<script setup>
import { computed, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ChatDotRound, Check, CircleCheck, Collection,
    DataLine, Location, Message, OfficeBuilding,
    Phone, Promotion, Star, User,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const page         = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const submitted    = ref(false);

/* ── UI-only state ───────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');

/* ── Form — existing fields only (unchanged) ─────────────── */
const form = useForm({
    first_name:  '',
    last_name:   '',
    telephone:   '',
    email:       '',
    district:    '',
    sub_county:  '',
    coffee_type: '',
    cooperative: '',
    farm_size:   '',
    notes:       '',
});

/* ── Completion — preserved from original ────────────────── */
const completion = computed(() => {
    const required = [form.first_name, form.last_name, form.telephone, form.district, form.coffee_type];
    const optional = [form.email, form.sub_county, form.cooperative, form.farm_size, form.notes];
    const reqFilled = required.filter(v => v.trim()).length;
    const optFilled = optional.filter(v => v.trim()).length;
    return Math.round((reqFilled / required.length) * 70 + (optFilled / optional.length) * 30);
});

/* ── Static UI data ──────────────────────────────────────── */
const chatPrompts = ['Is this farmer eligible for export supply?', 'How can farmer improve quality?', 'What certifications are needed?'];
const fillPrompt  = p => { chatInput.value = p; };

/* ── Submit — preserved from original ───────────────────── */
const submit = () => {
    form.post(route('farmer.store'), {
        preserveScroll: true,
        onSuccess: () => { submitted.value = true; },
    });
};
</script>

<template>
    <AppLayout title="Register Farmer" full-width flush :show-banner="false">

        <div class="fr-page">

            <!-- Header ──────── -->
            <div class="fr-header">
                <div class="fr-header-left">
                    <div class="fr-kicker">Farmer Onboarding</div>
                    <h1 class="fr-title mt-2 mb-2">Register Farmer</h1>
                    <p class="fr-subtitle mb-2">Onboard a coffee farmer into the traceability system</p>
                    <div class="fr-badge-row">
                        <span class="fr-badge fr-badge--green">Verified Farmer</span>
                        <span class="fr-badge fr-badge--blue">Traceable Producer</span>
                        <span class="fr-badge fr-badge--amber">Market Ready</span>
                    </div>
                </div>
                <div class="fr-header-actions">
                    <Link :href="route('farmer.index')" class="fr-btn fr-btn--outline">
                        <el-icon><Collection /></el-icon> View Farmers
                    </Link>

                </div>
            </div>

            <!-- Flash -->
            <div v-if="flashSuccess && !submitted" class="fr-flash">{{ flashSuccess }}</div>

            <!-- ② Success state ──────────────────────────────────────── -->
            <div v-if="submitted || flashSuccess" class="fr-success-wrap">
                <div class="fr-success-card">
                    <div class="fr-success-circle">
                        <el-icon style="font-size:26px;"><Check /></el-icon>
                    </div>
                    <h2 class="fr-success-title">Farmer Registered!</h2>
                    <p class="fr-success-sub">
                        <strong>{{ form.first_name }} {{ form.last_name }}</strong> has been successfully registered and added to the traceability network.
                    </p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <a :href="route('farmer.index')" class="fr-btn fr-btn--primary">View Farmer Directory</a>
                        <a :href="route('farmer.create')" class="fr-btn fr-btn--outline">Register Another</a>
                    </div>
                </div>
            </div>

            <!-- ③ Registration form ──────────────────────────────────── -->
            <template v-else>

                <form class="fr-body" @submit.prevent="submit">

                    <!-- Left: form sections -->
                    <div class="fr-main">

                        <!-- Section A: Farmer Identity ────────────────── -->
                        <div class="fr-card">
                            <div class="fr-card-head">
                                <div class="fr-card-title"><el-icon><User /></el-icon> Farmer Identity</div>
                                <span class="fr-badge fr-badge--blue" style="font-size:9px;">Section A</span>
                            </div>
                            <div class="fr-card-body">
                                <div class="fr-grid">
                                    <div class="fr-field">
                                        <label>First Name <span class="fr-req">*</span></label>
                                        <el-input v-model="form.first_name" placeholder="e.g. Joshua" class="fr-input" />
                                        <InputError :message="form.errors.first_name" class="fr-error" />
                                    </div>
                                    <div class="fr-field">
                                        <label>Last Name <span class="fr-req">*</span></label>
                                        <el-input v-model="form.last_name" placeholder="e.g. Kato" class="fr-input" />
                                        <InputError :message="form.errors.last_name" class="fr-error" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section B: Location Information ───────────── -->
                        <div class="fr-card">
                            <div class="fr-card-head">
                                <div class="fr-card-title"><el-icon><Location /></el-icon> Location Information</div>
                                <span class="fr-badge fr-badge--green" style="font-size:9px;">Section B</span>
                            </div>
                            <div class="fr-card-body">
                                <div class="fr-grid">
                                    <div class="fr-field">
                                        <label>District <span class="fr-req">*</span></label>
                                        <el-input v-model="form.district" placeholder="e.g. Mbale" :prefix-icon="Location" class="fr-input" />
                                        <InputError :message="form.errors.district" class="fr-error" />
                                    </div>
                                    <div class="fr-field">
                                        <label>Sub-County</label>
                                        <el-input v-model="form.sub_county" placeholder="e.g. Buginyanya" class="fr-input" />
                                        <InputError :message="form.errors.sub_county" class="fr-error" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section C: Contact Information ────────────── -->
                        <div class="fr-card">
                            <div class="fr-card-head">
                                <div class="fr-card-title"><el-icon><ChatDotRound /></el-icon> Contact Information</div>
                                <span class="fr-badge fr-badge--amber" style="font-size:9px;">Section C</span>
                            </div>
                            <div class="fr-card-body">
                                <div class="fr-grid">
                                    <div class="fr-field">
                                        <label>Phone Number <span class="fr-req">*</span></label>
                                        <el-input v-model="form.telephone" type="tel" placeholder="+256 752 567 534" :prefix-icon="Phone" class="fr-input" />
                                        <InputError :message="form.errors.telephone" class="fr-error" />
                                    </div>
                                    <div class="fr-field">
                                        <label>Email Address</label>
                                        <el-input v-model="form.email" type="email" placeholder="farmer@example.com" :prefix-icon="Message" class="fr-input" />
                                        <InputError :message="form.errors.email" class="fr-error" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section D: Farm Information ───────────────── -->
                        <div class="fr-card">
                            <div class="fr-card-head">
                                <div class="fr-card-title"><el-icon><Star /></el-icon> Farm Information</div>
                                <span class="fr-badge fr-badge--blue" style="font-size:9px;">Section D</span>
                            </div>
                            <div class="fr-card-body">
                                <div class="fr-grid">
                                    <div class="fr-field">
                                        <label>Coffee Type <span class="fr-req">*</span></label>
                                        <el-select v-model="form.coffee_type" clearable placeholder="Select type" class="fr-input w-100">
                                            <el-option label="Arabica" value="arabica" />
                                            <el-option label="Robusta" value="robusta" />
                                            <el-option label="Mixed"   value="mixed"   />
                                        </el-select>
                                        <InputError :message="form.errors.coffee_type" class="fr-error" />
                                    </div>
                                    <div class="fr-field">
                                        <label>Farm Size</label>
                                        <el-input v-model="form.farm_size" placeholder="e.g. 2.4 acres" class="fr-input" />
                                        <InputError :message="form.errors.farm_size" class="fr-error" />
                                    </div>
                                    <div class="fr-field fr-field--full">
                                        <label>Cooperative</label>
                                        <el-input v-model="form.cooperative" placeholder="e.g. Sipi Farmers Cooperative" :prefix-icon="OfficeBuilding" class="fr-input" />
                                        <InputError :message="form.errors.cooperative" class="fr-error" />
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Actions ────────────────────────────────────── -->
                        <div class="fr-form-actions">
                            <SubmitButton native-type="submit" :loading="form.processing" :disabled="form.processing" style="width:170px;">
                                Register Farmer
                            </SubmitButton>


                        </div>

                    </div><!-- /fr-main -->

                    <!-- Right: sticky summary ────────────────────────── -->
                    <aside class="fr-rail">

                        <!-- Completion + live summary -->
                        <div class="fr-card fr-card--sticky">
                            <div class="fr-card-head">
                                <div class="fr-card-title"><el-icon><DataLine /></el-icon> Profile Summary</div>
                                <span class="fr-completion-pill">{{ completion }}%</span>
                            </div>
                            <div class="fr-card-body">
                                <div class="fr-progress-label">Profile completion</div>
                                <div class="fr-progress-track mb-3">
                                    <div
                                        class="fr-progress-fill"
                                        :class="completion >= 70 ? '' : 'fr-progress-fill--amber'"
                                        :style="{ width: completion + '%' }"
                                    />
                                </div>

                                <div class="fr-detail-list">
                                    <div class="fr-detail-row">
                                        <span class="fr-muted">Full Name</span>
                                        <strong>{{ [form.first_name, form.last_name].filter(Boolean).join(' ') || '—' }}</strong>
                                    </div>
                                    <div class="fr-detail-row">
                                        <span class="fr-muted">District</span>
                                        <strong>{{ form.district || '—' }}</strong>
                                    </div>
                                    <div class="fr-detail-row">
                                        <span class="fr-muted">Sub-County</span>
                                        <strong>{{ form.sub_county || '—' }}</strong>
                                    </div>
                                    <div class="fr-detail-row">
                                        <span class="fr-muted">Phone</span>
                                        <strong>{{ form.telephone || '—' }}</strong>
                                    </div>
                                    <div class="fr-detail-row">
                                        <span class="fr-muted">Coffee Type</span>
                                        <strong>{{ form.coffee_type ? form.coffee_type.charAt(0).toUpperCase() + form.coffee_type.slice(1) : '—' }}</strong>
                                    </div>
                                    <div class="fr-detail-row">
                                        <span class="fr-muted">Farm Size</span>
                                        <strong>{{ form.farm_size || '—' }}</strong>
                                    </div>
                                    <div class="fr-detail-row">
                                        <span class="fr-muted">Cooperative</span>
                                        <strong>{{ form.cooperative || '—' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Readiness checklist -->
                        <div class="fr-card">
                            <div class="fr-card-head">
                                <div class="fr-card-title"><el-icon><CircleCheck /></el-icon> Readiness</div>
                            </div>
                            <div class="fr-card-body">
                                <div class="fr-checklist">
                                    <div
                                        v-for="item in [
                                            { label: 'First Name Set',      done: Boolean(form.first_name) },
                                            { label: 'Last Name Set',       done: Boolean(form.last_name) },
                                            { label: 'Phone Number Added',  done: Boolean(form.telephone) },
                                            { label: 'District Set',        done: Boolean(form.district) },
                                            { label: 'Coffee Type Selected',done: Boolean(form.coffee_type) },
                                            { label: 'Email Added',         done: Boolean(form.email) },
                                            { label: 'Cooperative Linked',  done: Boolean(form.cooperative) },
                                        ]"
                                        :key="item.label"
                                        class="fr-check-item"
                                    >
                                        <span class="fr-check-dot" :class="{ 'fr-check-dot--done': item.done }">
                                            <el-icon v-if="item.done"><Check /></el-icon>
                                        </span>
                                        <span :class="item.done ? 'fr-bold' : 'fr-muted'" style="font-size:11px;">{{ item.label }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </aside>

                </form><!-- /fr-body -->


            </template>

        </div><!-- /fr-page -->

        <!-- ⑦ Floating Chatbot ────────────────────────────────────────── -->
        <div class="fr-float">
            <div v-if="chatOpen" class="fr-chat-panel">
                <div class="fr-chat-head">
                    <div>
                        <div class="fr-bold" style="font-size:12px;">Bean Origin Farmer Advisor</div>
                        <div class="fr-muted" style="font-size:10px;">Farmer onboarding assistant</div>
                    </div>
                    <button class="fr-chat-close" type="button" @click="chatOpen = false">×</button>
                </div>
                <div class="fr-chat-prompts">
                    <button v-for="p in chatPrompts" :key="p" class="fr-chat-prompt" type="button" @click="fillPrompt(p)">{{ p }}</button>
                </div>
                <div class="fr-chat-input-row">
                    <input v-model="chatInput" class="fr-chat-input" placeholder="Ask about farmer registration…" />
                    <button class="fr-chat-send" type="button"><el-icon><Promotion /></el-icon></button>
                </div>
            </div>
            <button class="fr-float-btn" type="button" @click="chatOpen = !chatOpen">
                <el-icon><ChatDotRound /></el-icon>
            </button>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ── Base ───────────────────────────────────────────────────────── */
.fr-page {
    min-height: calc(100vh - 48px);
    background: var(--surface, #f7f9fb);
    color: #1f2a2a;
    padding-bottom: 60px;
    font-family: 'Manrope', sans-serif;
}

/* ── Header ─────────────────────────────────────────────────────── */
.fr-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; padding: 10px 24px;
    border-bottom: 1px solid #e8ecec;
    background: #fff; flex-wrap: wrap;
}
.fr-kicker {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .16em; text-transform: uppercase;
    color: #94a1b2; margin-bottom: 4px;
}
.fr-title    { font-size: 18px; font-weight: 800; color: #003f2c; margin: 0 0 3px; line-height: 1.15; }
.fr-subtitle { font-size: 12px; color: #657386; margin: 0 0 8px; line-height: 1.5; }
.fr-badge-row { display: flex; gap: 5px; flex-wrap: wrap; }
.fr-header-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }

/* ── Flash ───────────────────────────────────────────────────────── */
.fr-flash {
    margin: 12px 24px 0; padding: 10px 14px;
    border: 1px solid #c3ddd2; border-radius: 7px;
    background: #eef5f1; font-size: 12px; color: #004532;
}

/* ── Success state ───────────────────────────────────────────────── */
.fr-success-wrap { display: flex; justify-content: center; padding: 48px 24px; }
.fr-success-card {
    max-width: 480px; width: 100%; text-align: center;
    border: 1px solid #e4e7e8; border-radius: 12px;
    background: #fff; padding: 40px 32px;
}
.fr-success-circle {
    width: 64px; height: 64px; border-radius: 50%;
    background: #003f2c; color: #fff;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.fr-success-title { font-size: 20px; font-weight: 800; color: #1f2a2a; margin-bottom: 8px; }
.fr-success-sub { font-size: 13px; color: #657386; line-height: 1.6; margin-bottom: 24px; }

/* ── Badges ──────────────────────────────────────────────────────── */
.fr-badge {
    display: inline-flex; align-items: center;
    padding: 3px 7px; border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 700;
    letter-spacing: .1em; text-transform: uppercase;
}
.fr-badge--green { background: #eef5f1; color: #004532; border: 1px solid #c3ddd2; }
.fr-badge--blue  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.fr-badge--amber { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.fr-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    padding: 0 12px; height: 30px; border-radius: 5px;
    font-size: 11px; font-weight: 700; letter-spacing: .03em;
    cursor: pointer; border: 1px solid transparent;
    text-decoration: none; white-space: nowrap;
    font-family: 'Manrope', sans-serif;
    transition: background .14s, color .14s, border-color .14s;
}
.fr-btn--primary { background: #003f2c; color: #fff; border-color: #003f2c; }
.fr-btn--primary:hover { background: #004532; }
.fr-btn--outline { background: #fff; color: #263232; border-color: #d4d8d8; }
.fr-btn--outline:hover { border-color: #003f2c; color: #003f2c; }
.fr-btn--ghost { background: transparent; color: #657386; border-color: transparent; }
.fr-btn--ghost:hover { background: #f3f4f4; }
.fr-btn--sm { height: 26px; padding: 0 9px; font-size: 10px; }

/* ── Body layout ─────────────────────────────────────────────────── */
.fr-body {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(0, 0.8fr);
    gap: 16px;
    padding: 16px 24px;
    align-items: start;
}
@media (max-width: 1100px) { .fr-body { grid-template-columns: 1fr; } }

.fr-main { display: flex; flex-direction: column; gap: 12px; }
.fr-rail { display: flex; flex-direction: column; gap: 12px; }
.fr-section { padding: 0 24px 12px; }

/* ── Cards ───────────────────────────────────────────────────────── */
.fr-card { border: 1px solid #e4e7e8; border-radius: 8px; background: #fff; overflow: hidden; }
.fr-card--sticky { position: sticky; top: 12px; }
.fr-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 8px 14px; border-bottom: 1px solid #e8ecec;
    background: #f8f9f9; border-radius: 7px 7px 0 0; flex-wrap: wrap;
}
.fr-card-title {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 800; color: #1f2a2a;
    letter-spacing: .04em; text-transform: uppercase;
    font-family: 'IBM Plex Mono', monospace;
}
.fr-card-body      { padding: 14px; }
.fr-card-body--pipe { padding: 16px 14px; }

/* ── Form fields ─────────────────────────────────────────────────── */
.fr-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
@media (max-width: 640px) { .fr-grid { grid-template-columns: 1fr; } }

.fr-field { display: flex; flex-direction: column; gap: 5px; }
.fr-field--full { grid-column: 1 / -1; }
.fr-field label { font-size: 11px; font-weight: 700; color: #4b5563; letter-spacing: .03em; }
.fr-req   { color: #ef4444; }
.fr-error { font-size: 11px; color: #c0392b; margin-top: 4px; }

/* ── Input overrides ─────────────────────────────────────────────── */
.fr-input { width: 100%; }
:deep(.fr-input .el-input__wrapper),
:deep(.fr-input .el-select__wrapper) {
    background: #fff; border-radius: 6px !important;
    box-shadow: 0 0 0 1px #e6e8ea inset;
    min-height: 34px;
    font-family: 'Manrope', sans-serif; font-size: 12px;
}
:deep(.fr-input .el-input__wrapper.is-focus),
:deep(.fr-input .el-select__wrapper.is-focused) {
    box-shadow: 0 0 0 1px #003f2c inset, 0 0 0 3px rgba(0,69,50,.07);
}
:deep(.fr-input .el-textarea__inner) {
    background: #fff; border-radius: 6px !important;
    box-shadow: 0 0 0 1px #e6e8ea inset;
    font-family: 'Manrope', sans-serif; font-size: 12px;
    padding: 8px 12px; resize: vertical;
}
:deep(.fr-input .el-textarea__inner:focus) {
    box-shadow: 0 0 0 1px #003f2c inset; outline: none;
}

/* ── Form actions ────────────────────────────────────────────────── */
.fr-form-actions {
    display: flex; align-items: center; gap: 8px;
    flex-wrap: wrap; padding: 14px 0 2px;
    border-top: 1px solid #e8ecec;
}

/* ── Summary rail ────────────────────────────────────────────────── */
.fr-completion-pill {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px; font-weight: 800;
    color: #004532; background: #eef5f1;
    border: 1px solid #c3ddd2; padding: 3px 8px; border-radius: 5px;
}
.fr-detail-list { display: flex; flex-direction: column; }
.fr-detail-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 8px; padding: 6px 0; border-bottom: 1px solid #f0f2f2; font-size: 11px;
}
.fr-detail-row:last-child { border-bottom: none; }
.fr-detail-row strong { font-weight: 700; color: #1f2a2a; font-size: 11px; }

.fr-progress-label { font-size: 10px; color: #94a1b2; margin-bottom: 4px; }
.fr-progress-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.fr-progress-fill  { height: 100%; background: #004532; border-radius: 3px; transition: width .4s; }
.fr-progress-fill--amber { background: #d97706; }

.fr-checklist { display: flex; flex-direction: column; gap: 8px; }
.fr-check-item { display: flex; align-items: center; gap: 8px; }
.fr-check-dot {
    width: 17px; height: 17px; border-radius: 50%;
    border: 2px solid #d4d8d8;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 10px; color: #fff; background: #fff;
}
.fr-check-dot--done { border-color: #004532; background: #004532; }

/* ── Farm table ──────────────────────────────────────────────────── */
.fr-table-wrap { overflow-x: auto; }
.fr-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.fr-table thead th {
    padding: 7px 10px; background: #f6f8f8;
    border-bottom: 1px solid #e4e7e8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .12em; text-transform: uppercase;
    color: #7b8796; white-space: nowrap;
}
.fr-table tbody td { padding: 7px 10px; border-bottom: 1px solid #f0f2f2; vertical-align: middle; }
.fr-table tbody tr:last-child td { border-bottom: none; }
.fr-pill {
    display: inline-flex; padding: 2px 7px; border-radius: 4px;
    background: #eef5f1; color: #004532; border: 1px solid #c3ddd2;
    font-family: 'IBM Plex Mono', monospace; font-size: 9px;
    font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
}
.fr-remove-btn {
    width: 22px; height: 22px; border-radius: 4px;
    border: 1px solid #e4e7e8; background: #fafbfb;
    color: #94a1b2; cursor: pointer; font-size: 11px;
    display: flex; align-items: center; justify-content: center;
    transition: background .12s, color .12s;
}
.fr-remove-btn:hover { background: #fff0f0; color: #c0392b; border-color: #fca5a5; }
.fr-table-foot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 14px; border-top: 1px solid #e8ecec;
    background: #fafbfb; flex-wrap: wrap; gap: 8px;
}

/* ── Pipeline ────────────────────────────────────────────────────── */
.fr-pipe { display: flex; align-items: stretch; overflow-x: auto; }
.fr-pipe-item { flex: 1; min-width: 70px; display: flex; flex-direction: column; align-items: center; }
.fr-pipe-item__top { text-align: center; padding-bottom: 8px; min-height: 24px; display: flex; align-items: flex-end; justify-content: center; }
.fr-pipe-item__sub { font-size: 9px; color: #94a1b2; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; }
.fr-pipe-item__mid { display: flex; align-items: center; width: 100%; }
.fr-pipe-item__seg { flex: 1; height: 2px; background: #e4e7e8; }
.fr-pipe-item__seg--done   { background: #004532; }
.fr-pipe-item__seg--hidden { background: transparent; }
.fr-pipe-item__dot {
    width: 28px; height: 28px; border-radius: 50%;
    border: 2px solid #d4d8d8; background: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; color: #fff; z-index: 1;
}
.fr-pipe-item__dot--done    { border-color: #004532; background: #004532; }
.fr-pipe-item__dot--current { border-color: #d97706; background: #d97706; outline: 4px solid rgba(217,119,6,.15); outline-offset: 1px; }
.fr-pipe-item__bot { text-align: center; padding-top: 8px; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.fr-pipe-item__label { font-size: 10px; font-weight: 800; color: #1f2a2a; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; }
.fr-pipe-item__label--current { color: #d97706; }
.fr-pipe-item__verify { font-size: 9px; color: #b8c0cc; font-family: 'IBM Plex Mono', monospace; padding: 2px 6px; border-radius: 3px; border: 1px solid #e8ecec; background: #fafbfb; white-space: nowrap; }
.fr-pipe-item__verify--done    { color: #004532; border-color: #c3ddd2; background: #eef5f1; }
.fr-pipe-item__verify--current { color: #92400e; border-color: #fde68a; background: #fffbeb; }

/* ── AI Insights ─────────────────────────────────────────────────── */
.fr-insights-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
@media (max-width: 768px) { .fr-insights-grid { grid-template-columns: 1fr; } }
.fr-insight-card { display: flex; align-items: flex-start; gap: 8px; padding: 12px; border-radius: 7px; border: 1px solid; }
.fr-insight-card--green { background: #eef5f1; border-color: #c3ddd2; }
.fr-insight-card--blue  { background: #eff6ff; border-color: #bfdbfe; }
.fr-insight-card--amber { background: #fffbeb; border-color: #fde68a; }
.fr-insight-icon { font-size: 13px; color: #004532; flex-shrink: 0; margin-top: 1px; }
.fr-insight-text { font-size: 12px; font-weight: 600; color: #1f2a2a; line-height: 1.55; margin: 0; }

/* ── Helpers ─────────────────────────────────────────────────────── */
.fr-muted { font-size: 11px; color: #94a1b2; }
.fr-bold  { font-weight: 700; color: #1f2a2a; }

/* ── Floating chat ───────────────────────────────────────────────── */
.fr-float { position: fixed; bottom: 24px; right: 24px; z-index: 200; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.fr-float-btn { width: 44px; height: 44px; border-radius: 50%; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: background .14s; }
.fr-float-btn:hover { background: #004532; }
.fr-chat-panel { width: 290px; border: 1px solid #e4e7e8; border-radius: 10px; background: #fff; overflow: hidden; }
.fr-chat-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 13px; border-bottom: 1px solid #e8ecec; background: #f8f9f9; }
.fr-chat-close { font-size: 18px; line-height: 1; background: none; border: none; color: #94a1b2; cursor: pointer; padding: 0; }
.fr-chat-prompts { padding: 8px 12px; display: flex; flex-direction: column; gap: 5px; border-bottom: 1px solid #f0f2f2; }
.fr-chat-prompt { text-align: left; background: #f8f9f9; border: 1px solid #e4e7e8; border-radius: 5px; padding: 6px 9px; font-size: 11px; color: #263232; cursor: pointer; font-family: 'Manrope', sans-serif; transition: background .14s; }
.fr-chat-prompt:hover { background: #eef5f1; border-color: #c3ddd2; color: #004532; }
.fr-chat-input-row { display: flex; align-items: center; padding: 8px 12px; gap: 6px; }
.fr-chat-input { flex: 1; height: 30px; padding: 0 9px; border: 1px solid #d4d8d8; border-radius: 5px; font-size: 11px; outline: none; font-family: 'Manrope', sans-serif; }
.fr-chat-input:focus { border-color: #003f2c; }
.fr-chat-send { width: 30px; height: 30px; border-radius: 5px; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 1100px) { .fr-rail { position: static !important; } }
@media (max-width: 640px) {
    .fr-header { padding: 10px 14px; }
    .fr-body, .fr-section { padding-left: 14px; padding-right: 14px; }
    .fr-insights-grid { grid-template-columns: 1fr; }
    .fr-success-card { padding: 28px 20px; }
}
</style>
