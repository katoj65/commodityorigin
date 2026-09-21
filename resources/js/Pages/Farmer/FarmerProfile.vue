<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Calendar, Check, CircleCheck, CirclePlus,
    CollectionTag, Delete, EditPen, Location, Medal, Message,
    OfficeBuilding, Phone, Postcard, UserFilled,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import AddFarmModal from '@/Components/Modals/AddFarmModal.vue';
import EditFarmerDialog from '@/Components/Modals/EditFarmerDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    farmer: { type: Object, required: true },
    cooperatives: { type: Array, default: () => [] },
    canCreateFarm: { type: Boolean, default: false },
});

const page         = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

/* ── Decorative per-index tone (no external photos — the reference
   mockup's hotlinked "aida" image URLs are ephemeral design-tool
   preview assets and 429 under real traffic, so texture is done in
   pure CSS instead). Cycled by id so each photo/farm reliably gets
   a consistent, distinct wash. ─────────────────────────────────────── */
const TONES = ['fpr-tone-a', 'fpr-tone-b', 'fpr-tone-c'];
const toneClass = (id) => TONES[id % TONES.length];

/* ── Real computed — everything here comes straight off FarmerResource,
   nothing fabricated. ───────────────────────────────────────────── */
const fullName = computed(() =>
    props.farmer.full_name || [props.farmer.first_name, props.farmer.last_name].filter(Boolean).join(' ') || 'Assigned Producer',
);
const locationLabel = computed(() =>
    [props.farmer.subcounty, props.farmer.district].filter(Boolean).join(', ') || 'Origin pending',
);
const memberSinceYear = computed(() => {
    if (!props.farmer.created_at) return '—';
    return new Intl.DateTimeFormat('en-US', { year: 'numeric' }).format(new Date(props.farmer.created_at));
});
const verificationLabel = computed(() => {
    const v = props.farmer.verification_status;
    return v ? v.charAt(0).toUpperCase() + v.slice(1) : 'Pending';
});
const statusLabel = computed(() => {
    const s = props.farmer.status;
    return s ? s.charAt(0).toUpperCase() + s.slice(1) : '—';
});

/* Same 7-row "Technical Specifications" list shape as the reference
   mockup, with its Coffee Type / Farm Size swapped for real farmer
   columns (neither exists on this model) and two extra real fields
   (National ID, Farmer Number) appended as additional rows. */
const specFields = computed(() => [
    { icon: Phone, label: 'Telephone', value: props.farmer.tel },
    { icon: Message, label: 'Email', value: props.farmer.email },
    { icon: Location, label: 'District', value: props.farmer.district },
    { icon: Location, label: 'Sub-County', value: props.farmer.subcounty },
    { icon: OfficeBuilding, label: 'Cooperative', value: props.farmer.cooperative?.name },
    { icon: Postcard, label: 'National ID', value: props.farmer.national_id },
    { icon: CollectionTag, label: 'Farmer Number', value: props.farmer.farmer_number },
]);

/* "About the Producer" in the mockup is a fabricated personal narrative
   for a fictional persona — inventing biographical prose about a real
   farmer would misrepresent them, so this is built entirely from real
   fields instead of prose flavor text. */
const aboutParagraph = computed(() => {
    const parts = [];
    parts.push(`${fullName.value} has been part of the Bean Origin network since ${memberSinceYear.value}.`);
    if (props.farmer.district) {
        parts.push(`Based in ${locationLabel.value}${props.farmer.country ? ', ' + props.farmer.country : ''}.`);
    }
    if (props.farmer.cooperative?.name) {
        parts.push(`Affiliated with ${props.farmer.cooperative.name}.`);
    }
    parts.push(`Verification status: ${verificationLabel.value.toLowerCase()}.`);
    return parts.join(' ');
});

/* ── Navigation ────────────────────────────────────────────────── */
const addFarmOpen    = ref(false);
const editFarmerOpen = ref(false);
const deleteConfirmOpen = ref(false);

function deleteFarmer() {
    router.delete(route('farmer.destroy', props.farmer.id));
}
</script>

<template>
    <MainLayout :title="fullName">
        <Head :title="fullName" />

        <div class="fpr-page">

            <!-- Flash -->
            <div v-if="flashSuccess" class="fpr-flash">
                <el-icon><Check /></el-icon> {{ flashSuccess }}
            </div>

            <div class="fpr-grid">

                <!-- ── Left column ──────────────────────────────────────── -->
                <div class="fpr-left">

                    <!-- Profile Summary -->
                    <section class="fpr-card fpr-summary">
                        <div class="fpr-photo-wrap">
                            <div class="fpr-photo" :class="toneClass(farmer.id)">
                                <el-icon class="fpr-photo-icon"><UserFilled /></el-icon>
                            </div>
                            <div class="fpr-photo-badge"><el-icon><Medal /></el-icon></div>
                        </div>

                        <span class="fpr-pill" :class="{ 'fpr-pill--green': farmer.verification_status === 'verified' }">
                            {{ verificationLabel }}
                        </span>

                        <h1 class="fpr-name">{{ fullName }}</h1>
                        <div class="fpr-pin"><el-icon><Location /></el-icon> {{ locationLabel }}</div>

                        <div class="fpr-action-row">
                            <a v-if="farmer.email" :href="`mailto:${farmer.email}`" class="fpr-action-btn">
                                <el-icon><Message /></el-icon>
                            </a>
                            <a v-if="farmer.tel" :href="`tel:${farmer.tel}`" class="fpr-action-btn">
                                <el-icon><Phone /></el-icon>
                            </a>
                            <button v-if="canCreateFarm" class="fpr-action-btn" type="button" title="Add Farm" @click="addFarmOpen = true">
                                <el-icon><CirclePlus /></el-icon>
                            </button>
                            <button class="fpr-action-btn" type="button" title="Edit Farmer" @click="editFarmerOpen = true">
                                <el-icon><EditPen /></el-icon>
                            </button>
                            <button class="fpr-action-btn fpr-action-btn--danger" type="button" title="Delete Farmer" @click="deleteConfirmOpen = true">
                                <el-icon><Delete /></el-icon>
                            </button>
                        </div>
                    </section>

                    <!-- Technical Specifications -->
                    <section class="fpr-card">
                        <h2 class="fpr-card-heading">Technical Specifications</h2>
                        <div class="fpr-spec-list">
                            <div v-for="f in specFields" :key="f.label" class="fpr-spec-row">
                                <div class="fpr-spec-icon"><el-icon><component :is="f.icon" /></el-icon></div>
                                <div class="fpr-spec-text">
                                    <span class="fpr-spec-label">{{ f.label }}</span>
                                    <span class="fpr-spec-value">{{ f.value || '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ── Right column ─────────────────────────────────────── -->
                <div class="fpr-right">

                    <!-- Metrics -->
                    <section class="fpr-metrics">
                        <div class="fpr-metric-card">
                            <div class="fpr-metric-icon fpr-metric-icon--green"><el-icon><CircleCheck /></el-icon></div>
                            <div>
                                <h3 class="fpr-metric-label">Status</h3>
                                <p class="fpr-metric-value">{{ statusLabel }}</p>
                            </div>
                        </div>
                        <div class="fpr-metric-card">
                            <div class="fpr-metric-icon"><el-icon><Calendar /></el-icon></div>
                            <div>
                                <h3 class="fpr-metric-label">Member Since</h3>
                                <p class="fpr-metric-value">{{ memberSinceYear }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- About -->
                    <section class="fpr-card">
                        <h2 class="fpr-card-heading">About the Producer</h2>
                        <p class="fpr-about-text">{{ aboutParagraph }}</p>
                    </section>

                </div>

            </div>
        </div>

        <AddFarmModal v-model="addFarmOpen" />
        <EditFarmerDialog v-model="editFarmerOpen" :farmer="farmer" :cooperatives="cooperatives" />
        <ConfirmDialog
            v-model="deleteConfirmOpen"
            title="Delete Farmer"
            :message="`${fullName} will be permanently removed. Farms are not affected.`"
            confirm-text="Delete Farmer"
            @confirm="deleteFarmer"
        />
    </MainLayout>
</template>

<style scoped>
/* ── Ported from the two-column "FarmerProfile" reference mockup,
   mapped onto the app's persistent --dp-* tokens (this mockup's own
   palette, per its DESIGN.md, already matches dp-* 1:1). System font
   stack in place of Playfair Display / Inter, matching Farmer/Create.
   vue's redesign convention. Cards use white + shadow rather than the
   mockup's light borders, and internal divider borders are dropped —
   both per this page's own settled conventions from earlier in this
   redesign pass. ───────────────────────────────────────────────────── */
.fpr-page {
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
    padding-bottom: 32px;
}

/* ── Flash ───────────────────────────────────────────────────────── */
.fpr-flash {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 16px; border-radius: 12px; margin-bottom: 12px;
    background: var(--dp-secondary-container); color: var(--dp-on-secondary-container);
    font-size: .875rem; font-weight: 600;
}

/* ── Grid ────────────────────────────────────────────────────────── */
.fpr-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 20px; align-items: stretch; }
.fpr-left  { grid-column: span 12; display: flex; flex-direction: column; gap: 20px; }
.fpr-right { grid-column: span 12; display: flex; flex-direction: column; gap: 20px; }
/* Equal-height columns: the last card in each column (Technical
   Specifications / Farm Portfolio) absorbs whatever height difference
   is left over, so both columns' bottoms line up. */
.fpr-left > :last-child, .fpr-right > :last-child { flex: 1; display: flex; flex-direction: column; }
@media (min-width: 1024px) {
    .fpr-left  { grid-column: span 4; }
    .fpr-right { grid-column: span 8; }
}

/* ── Cards ───────────────────────────────────────────────────────── */
.fpr-card {
    background: var(--dp-surface-container-lowest); box-shadow: var(--dp-card-shadow);
    border-radius: 6px; padding: 24px;
}
.fpr-card-heading { font-size: 1.125rem; font-weight: 700; color: var(--dp-primary); margin: 0 0 16px !important; }

/* ── Profile summary ─────────────────────────────────────────────── */
.fpr-summary { display: flex; flex-direction: column; align-items: center; text-align: center; }
.fpr-photo-wrap { position: relative; width: 128px; height: 128px; margin-bottom: 16px; }
.fpr-photo {
    width: 128px; height: 128px; border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,.15);
    display: flex; align-items: center; justify-content: center;
    background-image: var(--fpr-tone-gradient);
}
.fpr-photo-icon { font-size: 3rem; color: rgba(255,255,255,.85); }
.fpr-photo-badge {
    position: absolute; bottom: -6px; right: -6px;
    width: 28px; height: 28px; border-radius: 999px;
    background: var(--dp-secondary-container); color: var(--dp-on-secondary-container);
    display: flex; align-items: center; justify-content: center;
    font-size: .8125rem; box-shadow: 0 1px 3px rgba(0,0,0,.2);
}

.fpr-tone-a { --fpr-tone-gradient: linear-gradient(150deg, var(--dp-primary-container), var(--dp-primary)); }
.fpr-tone-b { --fpr-tone-gradient: linear-gradient(150deg, #3a5a3f, var(--dp-secondary)); }
.fpr-tone-c { --fpr-tone-gradient: linear-gradient(150deg, #8a6a3f, #4a3520); }

.fpr-pill {
    display: inline-flex; align-items: center; padding: 5px 14px; border-radius: 999px;
    background: var(--dp-surface-container-high); color: var(--dp-primary);
    font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
    margin-bottom: 12px;
}
.fpr-pill--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }

.fpr-name { font-size: 1.375rem; font-weight: 800; letter-spacing: -.01em; line-height: 1.25; color: var(--dp-on-surface); margin: 0 !important; }
.fpr-pin { display: flex; align-items: center; justify-content: center; gap: 4px; font-size: .8125rem; color: var(--dp-on-surface-variant); margin-top: 6px; margin-bottom: 20px; }
.fpr-pin :deep(.el-icon) { color: var(--dp-primary); font-size: .75rem; }

.fpr-action-row { display: flex; gap: 12px; }
.fpr-action-btn {
    width: 40px; height: 40px; border-radius: 6px; border: none;
    background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant);
    display: flex; align-items: center; justify-content: center; cursor: pointer;
    text-decoration: none; transition: background .15s ease, color .15s ease;
}
.fpr-action-btn:hover { background: var(--dp-surface-container-high); color: var(--dp-primary); }
.fpr-action-btn--danger:hover { background: var(--dp-error-container); color: var(--dp-error); }

/* ── Technical specifications ────────────────────────────────────── */
.fpr-spec-list { display: flex; flex-direction: column; gap: 4px; }
.fpr-spec-row {
    display: flex; align-items: center; gap: 12px;
    padding: 8px; margin: 0 -8px; border-radius: 10px;
    transition: background .15s ease;
}
.fpr-spec-row:hover { background: var(--dp-surface-container-low); }
.fpr-spec-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant);
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
}
.fpr-spec-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.fpr-spec-label { font-size: .8125rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.fpr-spec-value { font-size: .9375rem; font-weight: 600; color: var(--dp-on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Metrics ─────────────────────────────────────────────────────── */
.fpr-metrics { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
@media (max-width: 640px) { .fpr-metrics { grid-template-columns: 1fr; } }
.fpr-metric-card {
    background: var(--dp-surface-container-lowest); box-shadow: var(--dp-card-shadow);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 6px; padding: 18px; display: flex; align-items: center; gap: 14px;
    transition: box-shadow .15s ease, transform .15s ease; min-width: 0;
}
.fpr-metric-card:hover { box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 14px 28px -14px rgba(0,0,0,.18); transform: translateY(-1px); }
.fpr-metric-icon {
    width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
    background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant);
    display: flex; align-items: center; justify-content: center; font-size: 1.125rem;
}
.fpr-metric-icon--green { background: rgba(27,109,36,.12); color: var(--dp-secondary); }
.fpr-metric-label { font-size: .8125rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); margin: 0 0 2px !important; }
.fpr-metric-value { font-size: 1.25rem; font-weight: 800; color: var(--dp-primary); margin: 0 !important; line-height: 1; }

/* ── About ───────────────────────────────────────────────────────── */
.fpr-about-text { font-size: .9375rem; color: var(--dp-on-surface-variant); line-height: 1.7; margin: 0 !important; }
</style>
