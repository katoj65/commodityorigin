<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft, ArrowRight, Calendar, Check, CircleCheck, CirclePlus,
    CollectionTag, Delete, EditPen, Location, MapLocation, Medal, Message,
    OfficeBuilding, Phone, Postcard, UserFilled, View,
    Warning,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import AddFarmDialog from '@/Components/Modals/AddFarmDialog.vue';
import EditFarmerDialog from '@/Components/Modals/EditFarmerDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    farmer: { type: Object, required: true },
    cooperatives: { type: Array, default: () => [] },
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
const farms = computed(() => props.farmer.farms || []);

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
    parts.push(`Verification status: ${verificationLabel.value.toLowerCase()}, with ${farms.value.length} farm${farms.value.length === 1 ? '' : 's'} currently registered.`);
    return parts.join(' ');
});

/* ── Farm portfolio paging (3 per page, matching the mockup's grid) ── */
const PAGE_SIZE = 3;
const farmPage = ref(0);
const farmPageCount = computed(() => Math.max(1, Math.ceil(farms.value.length / PAGE_SIZE)));
const pagedFarms = computed(() => {
    const start = farmPage.value * PAGE_SIZE;
    return farms.value.slice(start, start + PAGE_SIZE);
});
const prevFarmPage = () => { if (farmPage.value > 0) farmPage.value--; };
const nextFarmPage = () => { if (farmPage.value < farmPageCount.value - 1) farmPage.value++; };

/* ── Navigation ────────────────────────────────────────────────── */
const addFarmOpen    = ref(false);
const editFarmerOpen = ref(false);
const deleteConfirmOpen = ref(false);
const goToFarm = (id) => router.visit(route('farm.show', id));

function deleteFarmer() {
    router.delete(route('farmer.destroy', props.farmer.id));
}
</script>

<template>
    <DesignPreviewLayout :title="fullName">
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
                            <button class="fpr-action-btn" type="button" title="Add Farm" @click="addFarmOpen = true">
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
                            <div class="fpr-metric-icon"><el-icon><MapLocation /></el-icon></div>
                            <div>
                                <h3 class="fpr-metric-label">Total Farms</h3>
                                <p class="fpr-metric-value">{{ farms.length }}</p>
                            </div>
                        </div>
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

                    <!-- Farm Portfolio -->
                    <section class="fpr-card fpr-portfolio">
                        <div class="fpr-portfolio-head">
                            <h2 class="fpr-portfolio-title">Farm Portfolio</h2>
                            <div class="fpr-page-nav" v-if="farms.length > PAGE_SIZE">
                                <button class="fpr-page-btn" type="button" :disabled="farmPage === 0" @click="prevFarmPage">
                                    <el-icon><ArrowLeft /></el-icon>
                                </button>
                                <button class="fpr-page-btn" type="button" :disabled="farmPage >= farmPageCount - 1" @click="nextFarmPage">
                                    <el-icon><ArrowRight /></el-icon>
                                </button>
                            </div>
                        </div>

                        <div v-if="!farms.length" class="fpr-empty">
                            <div class="fpr-empty-icon"><el-icon><Warning /></el-icon></div>
                            <div class="fpr-empty-title">No farms registered yet</div>
                            <p class="fpr-empty-text">Link this farmer's first farm to start tracking harvests, quality, and traceability.</p>
                            <button class="fpr-btn fpr-btn--primary" type="button" @click="addFarmOpen = true">
                                <el-icon><CirclePlus /></el-icon> Add First Farm
                            </button>
                        </div>

                        <template v-else>
                            <div class="fpr-farm-grid">
                                <article
                                    v-for="farm in pagedFarms"
                                    :key="farm.id"
                                    class="fpr-farm-card"
                                    @click="goToFarm(farm.id)"
                                >
                                    <div class="fpr-farm-thumb" :class="toneClass(farm.id)">
                                        <el-icon><MapLocation /></el-icon>
                                    </div>
                                    <div class="fpr-farm-info">
                                        <h3 class="fpr-farm-name">{{ farm.name || `Farm ${farm.id}` }}</h3>
                                        <p class="fpr-farm-desc">{{ [farm.altitude || farm.variety, farm.location].filter(Boolean).join(' · ') || 'Location pending' }}</p>
                                    </div>
                                    <button class="fpr-farm-view" type="button" @click.stop="goToFarm(farm.id)">
                                        <el-icon><View /></el-icon>
                                    </button>
                                </article>
                            </div>
                        </template>
                    </section>

                </div>

            </div>
        </div>

        <AddFarmDialog v-model="addFarmOpen" :farmer-id="farmer.id" />
        <EditFarmerDialog v-model="editFarmerOpen" :farmer="farmer" :cooperatives="cooperatives" />
        <ConfirmDialog
            v-model="deleteConfirmOpen"
            title="Delete Farmer"
            :message="`${fullName} will be permanently removed, along with any linked farms.`"
            confirm-text="Delete Farmer"
            @confirm="deleteFarmer"
        />
    </DesignPreviewLayout>
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
    border-radius: 16px; padding: 24px;
}
.fpr-card-heading { font-size: 1.125rem; font-weight: 700; color: var(--dp-primary); margin: 0 0 16px !important; }

/* ── Profile summary ─────────────────────────────────────────────── */
.fpr-summary { display: flex; flex-direction: column; align-items: center; text-align: center; }
.fpr-photo-wrap { position: relative; width: 128px; height: 128px; margin-bottom: 16px; }
.fpr-photo {
    width: 128px; height: 128px; border-radius: 12px;
    box-shadow: 0 4px 10px rgba(39,19,16,.15);
    display: flex; align-items: center; justify-content: center;
    background-image: var(--fpr-tone-gradient);
}
.fpr-photo-icon { font-size: 3rem; color: rgba(255,255,255,.85); }
.fpr-photo-badge {
    position: absolute; bottom: -6px; right: -6px;
    width: 28px; height: 28px; border-radius: 999px;
    background: var(--dp-secondary-container); color: var(--dp-on-secondary-container);
    display: flex; align-items: center; justify-content: center;
    font-size: .8125rem; box-shadow: 0 1px 3px rgba(39,19,16,.2);
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
    width: 40px; height: 40px; border-radius: 999px; border: none;
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
.fpr-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
@media (max-width: 640px) { .fpr-metrics { grid-template-columns: 1fr; } }
.fpr-metric-card {
    background: var(--dp-surface-container-lowest); box-shadow: var(--dp-card-shadow);
    border-radius: 12px; padding: 18px; display: flex; align-items: center; gap: 14px;
    transition: box-shadow .15s ease, transform .15s ease; min-width: 0;
}
.fpr-metric-card:hover { box-shadow: 0 1px 2px rgba(39,19,16,.04), 0 14px 28px -14px rgba(39,19,16,.18); transform: translateY(-1px); }
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

/* ── Farm portfolio ──────────────────────────────────────────────── */
.fpr-portfolio { display: flex; flex-direction: column; gap: 12px; }
.fpr-portfolio-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; }
.fpr-portfolio-title { font-size: 1.25rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-primary); margin: 0 !important; }

.fpr-page-nav { display: flex; gap: 8px; }
.fpr-page-btn {
    width: 32px; height: 32px; border-radius: 999px;
    border: 1px solid var(--dp-outline-variant); background: transparent; color: var(--dp-on-surface-variant);
    display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: .875rem;
    transition: color .15s ease, border-color .15s ease, background .15s ease;
}
.fpr-page-btn:hover:not(:disabled) { color: var(--dp-primary); border-color: var(--dp-primary); background: var(--dp-surface-container-low); }
.fpr-page-btn:disabled { opacity: .4; cursor: not-allowed; }

.fpr-farm-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 768px) { .fpr-farm-grid { grid-template-columns: repeat(3, 1fr); } }
.fpr-farm-card {
    display: flex; flex-direction: column; align-items: center; gap: 14px; padding: 12px; cursor: pointer;
    background: var(--dp-surface-container-lowest); box-shadow: var(--dp-card-shadow);
    border-radius: 12px; transition: box-shadow .15s ease, transform .15s ease; min-width: 0;
}
@media (min-width: 768px) { .fpr-farm-card { flex-direction: row; } }
.fpr-farm-card:hover { box-shadow: 0 1px 2px rgba(39,19,16,.04), 0 14px 28px -14px rgba(39,19,16,.18); transform: translateY(-1px); }
.fpr-farm-thumb {
    width: 100%; height: 96px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background-image: var(--fpr-tone-gradient); color: rgba(255,255,255,.5); font-size: 1.5rem;
}
@media (min-width: 768px) { .fpr-farm-thumb { width: 64px; height: 64px; } }
.fpr-farm-info { flex: 1; min-width: 0; width: 100%; text-align: center; }
@media (min-width: 768px) { .fpr-farm-info { text-align: left; } }
.fpr-farm-name { font-size: .875rem; font-weight: 700; color: var(--dp-primary); margin: 0 0 2px !important; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fpr-farm-desc { font-size: .75rem; color: var(--dp-on-surface-variant); margin: 0 !important; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fpr-farm-view {
    flex-shrink: 0; width: 32px; height: 32px; border-radius: 999px; border: none;
    background: var(--dp-surface-container-low); color: var(--dp-primary);
    display: none; align-items: center; justify-content: center; cursor: pointer;
}
@media (min-width: 768px) { .fpr-farm-view { display: flex; } }

.fpr-btn {
    display: inline-flex; align-items: center; gap: 8px; width: fit-content;
    border-radius: 999px; padding: 10px 20px; font-size: .8125rem; font-weight: 700;
    border: 2px solid transparent; cursor: pointer; font-family: var(--dp-font-sans);
    transition: background .15s ease, transform .15s ease;
}
.fpr-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.fpr-btn--primary:hover { transform: translateY(-1px); }

/* ── Empty state ─────────────────────────────────────────────────── */
.fpr-empty {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 4px;
    padding: 48px 24px; background: var(--dp-surface-container-low); border-radius: 12px; flex: 1; justify-content: center;
}
.fpr-empty-icon {
    width: 44px; height: 44px; border-radius: 50%; background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface-variant); display: flex; align-items: center; justify-content: center;
    font-size: 18px; margin-bottom: 8px;
}
.fpr-empty-title { font-size: 1rem; font-weight: 700; color: var(--dp-on-surface); }
.fpr-empty-text { font-size: .8125rem; color: var(--dp-on-surface-variant); max-width: 340px; margin: 4px 0 16px !important; line-height: 1.5; }
</style>
