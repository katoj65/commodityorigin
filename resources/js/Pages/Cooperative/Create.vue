<script setup>
import { computed, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ChatDotRound, Check, CircleCheck, Collection,
    DataLine, InfoFilled, Location, Message, OfficeBuilding,
    Phone, Promotion, User,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const page         = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

/* ── UI-only state ───────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');

/* ── Form — existing fields only (unchanged) ─────────────── */
const form = useForm({
    name:                '',
    code:                '',
    registration_number: '',
    contact_person:      '',
    telephone:           '',
    email:               '',
    district:            '',
    sub_county:          '',
    address:             '',
    notes:               '',
});

/* ── Computed ────────────────────────────────────────────── */
const formCompletion = computed(() => {
    const fields = [form.name, form.code, form.contact_person, form.telephone, form.district];
    return Math.round(fields.filter(Boolean).length / fields.length * 100);
});

const registrationReadiness = computed(() => {
    let s = 10;
    if (form.name)                s += 20;
    if (form.code)                s += 15;
    if (form.registration_number) s += 15;
    if (form.contact_person)      s += 15;
    if (form.district)            s += 15;
    if (form.email)               s += 10;
    return Math.min(s, 100);
});

const chatPrompts = ['Is this cooperative export ready?', 'What certifications are needed?', 'How can production improve?'];
const fillPrompt  = p => { chatInput.value = p; };

/* ── Submit (unchanged) ──────────────────────────────────── */
const submit = () => {
    form.post(route('cooperative.store'), { preserveScroll: true });
};
</script>

<template>
    <DesignPreviewLayout title="Register Cooperative">

        <div class="cr-page">

            <!-- Header ────────────────────────────────────────────── -->
            <div class="cr-header">
                <div class="cr-header-left">
                    <div class="cr-kicker">Cooperative Registry</div>
                    <h1 class="cr-title mb-2">Register Cooperative</h1>
                    <p class="cr-subtitle mb-2">Register and onboard a coffee cooperative.</p>
                    <div class="cr-badge-row">
                        <span class="cr-badge cr-badge--green">Verified Cooperative</span>
                        <span class="cr-badge cr-badge--blue">Traceable Network</span>
                        <span class="cr-badge cr-badge--amber">Export Ready</span>
                    </div>
                </div>
                <div class="cr-header-actions">
                    <Link :href="route('cooperative.index')" class="cr-btn cr-btn--outline">
                        <el-icon><Collection /></el-icon> View Cooperatives
                    </Link>
                </div>
            </div>

            <!-- Flash success -->
            <div v-if="flashSuccess" class="cr-flash">
                {{ flashSuccess }}
            </div>

            <!-- ② Form + Summary ────────────────────────────────────── -->
            <form class="cr-body" @submit.prevent="submit">

                <!-- Left: form sections -->
                <div class="cr-main">

                    <!-- Section A: Cooperative Information ────────────── -->
                    <div class="cr-card">
                        <div class="cr-card-head">
                            <div class="cr-card-title"><el-icon><OfficeBuilding /></el-icon> Cooperative Information</div>
                            <span class="cr-badge cr-badge--blue">Section A</span>
                        </div>
                        <div class="cr-card-body">
                            <div class="cr-grid">
                                <div class="cr-field cr-field--full">
                                    <label>Cooperative Name <span class="cr-req">*</span></label>
                                    <el-input v-model="form.name" placeholder="e.g. Mount Elgon Producers Union" class="cr-input" />
                                    <InputError :message="form.errors.name" class="cr-error" />
                                </div>
                                <div class="cr-field">
                                    <label>Cooperative Code <span class="cr-req">*</span></label>
                                    <el-input v-model="form.code" placeholder="e.g. MEPU" class="cr-input" />
                                    <InputError :message="form.errors.code" class="cr-error" />
                                </div>
                                <div class="cr-field">
                                    <label>Registration Number</label>
                                    <el-input v-model="form.registration_number" placeholder="e.g. REG-2026-001" class="cr-input" />
                                    <InputError :message="form.errors.registration_number" class="cr-error" />
                                </div>
                                <div class="cr-field cr-field--full">
                                    <label>Description / Notes</label>
                                    <el-input v-model="form.notes" type="textarea" :rows="3" placeholder="Add sourcing notes, membership details, or compliance context…" class="cr-input" />
                                    <InputError :message="form.errors.notes" class="cr-error" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section B: Location Information ───────────────── -->
                    <div class="cr-card">
                        <div class="cr-card-head">
                            <div class="cr-card-title"><el-icon><Location /></el-icon> Location Information</div>
                            <span class="cr-badge cr-badge--green">Section B</span>
                        </div>
                        <div class="cr-card-body">
                            <div class="cr-grid">
                                <div class="cr-field">
                                    <label>District <span class="cr-req">*</span></label>
                                    <el-input v-model="form.district" placeholder="e.g. Mbale" :prefix-icon="Location" class="cr-input" />
                                    <InputError :message="form.errors.district" class="cr-error" />
                                </div>
                                <div class="cr-field">
                                    <label>Sub-county</label>
                                    <el-input v-model="form.sub_county" placeholder="e.g. Buginyanya" class="cr-input" />
                                    <InputError :message="form.errors.sub_county" class="cr-error" />
                                </div>
                                <div class="cr-field cr-field--full">
                                    <label>Address</label>
                                    <el-input v-model="form.address" placeholder="Trading centre, road, or office address" class="cr-input" />
                                    <InputError :message="form.errors.address" class="cr-error" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section C: Contact Information ────────────────── -->
                    <div class="cr-card">
                        <div class="cr-card-head">
                            <div class="cr-card-title"><el-icon><User /></el-icon> Contact Information</div>
                            <span class="cr-badge cr-badge--amber">Section C</span>
                        </div>
                        <div class="cr-card-body">
                            <div class="cr-grid">
                                <div class="cr-field cr-field--full">
                                    <label>Contact Person <span class="cr-req">*</span></label>
                                    <el-input v-model="form.contact_person" placeholder="e.g. Jane Auma" :prefix-icon="User" class="cr-input" />
                                    <InputError :message="form.errors.contact_person" class="cr-error" />
                                </div>
                                <div class="cr-field">
                                    <label>Phone Number</label>
                                    <el-input v-model="form.telephone" type="tel" placeholder="+256 700 000 000" :prefix-icon="Phone" class="cr-input" />
                                    <InputError :message="form.errors.telephone" class="cr-error" />
                                </div>
                                <div class="cr-field">
                                    <label>Email Address</label>
                                    <el-input v-model="form.email" type="email" placeholder="info@cooperative.ug" :prefix-icon="Message" class="cr-input" />
                                    <InputError :message="form.errors.email" class="cr-error" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions ────────────────────────────────────────── -->
                    <div class="cr-form-actions">
                        <SubmitButton native-type="submit" :loading="form.processing" :disabled="form.processing" :full-width="false" style="min-width:180px;">
                            Register
                        </SubmitButton>
                    </div>

                </div><!-- /cr-main -->

                <!-- Right: sticky summary ────────────────────────────── -->
                <aside class="cr-rail">

                    <!-- Live summary -->
                    <div class="cr-card cr-card--sticky">
                        <div class="cr-card-head">
                            <div class="cr-card-title"><el-icon><DataLine /></el-icon> Summary</div>
                            <span class="cr-completion-pill">{{ formCompletion }}% Complete</span>
                        </div>
                        <div class="cr-card-body">
                            <div class="cr-progress-label">Form completion</div>
                            <div class="cr-progress-track mb-3">
                                <div class="cr-progress-fill" :style="{ width: formCompletion + '%' }" />
                            </div>

                            <div class="cr-detail-list">
                                <div class="cr-detail-row">
                                    <span class="cr-muted">Cooperative Name</span>
                                    <strong>{{ form.name || '—' }}</strong>
                                </div>
                                <div class="cr-detail-row">
                                    <span class="cr-muted">Code</span>
                                    <strong>{{ form.code || '—' }}</strong>
                                </div>
                                <div class="cr-detail-row">
                                    <span class="cr-muted">Reg. Number</span>
                                    <strong>{{ form.registration_number || '—' }}</strong>
                                </div>
                                <div class="cr-detail-row">
                                    <span class="cr-muted">District</span>
                                    <strong>{{ form.district || '—' }}</strong>
                                </div>
                                <div class="cr-detail-row">
                                    <span class="cr-muted">Sub-county</span>
                                    <strong>{{ form.sub_county || '—' }}</strong>
                                </div>
                                <div class="cr-detail-row">
                                    <span class="cr-muted">Contact</span>
                                    <strong>{{ form.contact_person || '—' }}</strong>
                                </div>
                            </div>

                            <div style="margin-top:12px;">
                                <div class="cr-progress-label">Registration Readiness — {{ registrationReadiness }}%</div>
                                <div class="cr-progress-track">
                                    <div class="cr-progress-fill cr-progress-fill--amber" :style="{ width: registrationReadiness + '%' }" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Readiness checklist -->
                    <div class="cr-card">
                        <div class="cr-card-head">
                            <div class="cr-card-title"><el-icon><CircleCheck /></el-icon> Readiness</div>
                        </div>
                        <div class="cr-card-body">
                            <div class="cr-checklist">
                                <div
                                    v-for="item in [
                                        { label: 'Cooperative Name Set',    done: Boolean(form.name) },
                                        { label: 'Code Assigned',           done: Boolean(form.code) },
                                        { label: 'Registration Number',     done: Boolean(form.registration_number) },
                                        { label: 'Contact Person Named',    done: Boolean(form.contact_person) },
                                        { label: 'Phone Number Added',      done: Boolean(form.telephone) },
                                        { label: 'Email Added',             done: Boolean(form.email) },
                                        { label: 'District Set',            done: Boolean(form.district) },
                                    ]"
                                    :key="item.label"
                                    class="cr-check-item"
                                >
                                    <span class="cr-check-dot" :class="{ 'cr-check-dot--done': item.done }">
                                        <el-icon v-if="item.done"><Check /></el-icon>
                                    </span>
                                    <span :class="item.done ? 'cr-bold' : 'cr-muted'" style="font-size:11px;">{{ item.label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration guide -->
                    <div class="cr-card">
                        <div class="cr-card-head">
                            <div class="cr-card-title"><el-icon><InfoFilled /></el-icon> Registration Guide</div>
                        </div>
                        <div class="cr-card-body">
                            <ul class="cr-guide-list">
                                <li>Use the cooperative's registered legal or common name for accurate matching.</li>
                                <li>Assign a short, memorable code — it's used across lot batches and reporting.</li>
                                <li>A registration number and email speed up export-readiness verification.</li>
                                <li>Name a single contact person so buyers and inspectors know who to reach.</li>
                            </ul>
                        </div>
                    </div>

                </aside>

            </form><!-- /cr-body -->

        </div><!-- /cr-page -->

        <!-- ⑥ Floating Chatbot ────────────────────────────────────────── -->
        <div class="cr-float">
            <div v-if="chatOpen" class="cr-chat-panel">
                <div class="cr-chat-head">
                    <div>
                        <div class="cr-bold" style="font-size:12px;">Bean Origin Cooperative Advisor</div>
                        <div class="cr-muted" style="font-size:10px;">Registration assistant</div>
                    </div>
                    <button class="cr-chat-close" type="button" @click="chatOpen = false">×</button>
                </div>
                <div class="cr-chat-prompts">
                    <button v-for="p in chatPrompts" :key="p" class="cr-chat-prompt" type="button" @click="fillPrompt(p)">{{ p }}</button>
                </div>
                <div class="cr-chat-input-row">
                    <input v-model="chatInput" class="cr-chat-input" placeholder="Ask about cooperative registration…" />
                    <button class="cr-chat-send" type="button"><el-icon><Promotion /></el-icon></button>
                </div>
            </div>
            <button class="cr-float-btn" type="button" @click="chatOpen = !chatOpen">
                <el-icon><ChatDotRound /></el-icon>
            </button>
        </div>

    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Base ───────────────────────────────────────────────────────── */
.cr-page {
    min-height: calc(100vh - 48px);
    background: var(--surface, #f7f9fb);
    color: #1f2a2a;
    padding-bottom: 60px;
    font-family: 'Manrope', sans-serif;
}

/* ── Header ─────────────────────────────────────────────────────── */
.cr-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; padding: 10px 24px;
    border-bottom: 1px solid #e8ecec;
    background: #fff; flex-wrap: wrap;
}
.cr-kicker {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .16em; text-transform: uppercase;
    color: #94a1b2; margin-bottom: 4px;
}
.cr-title   { font-size: 18px; font-weight: 800; color: #003f2c; margin: 0 0 3px; line-height: 1.15; }
.cr-subtitle { font-size: 12px; color: #657386; margin: 0 0 8px; line-height: 1.5; }
.cr-badge-row { display: flex; gap: 5px; flex-wrap: wrap; }
.cr-header-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }

/* ── Flash ───────────────────────────────────────────────────────── */
.cr-flash {
    margin: 12px 24px 0;
    padding: 10px 14px;
    border: 1px solid #c3ddd2;
    border-radius: 7px;
    background: #eef5f1;
    font-size: 12px;
    color: #004532;
}

/* ── Badges ──────────────────────────────────────────────────────── */
.cr-badge {
    display: inline-flex; align-items: center;
    padding: 3px 7px; border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 700;
    letter-spacing: .1em; text-transform: uppercase;
}
.cr-badge--green  { background: #eef5f1; color: #004532; border: 1px solid #c3ddd2; }
.cr-badge--blue   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.cr-badge--amber  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.cr-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    padding: 0 12px; height: 30px; border-radius: 5px;
    font-size: 11px; font-weight: 700; letter-spacing: .03em;
    cursor: pointer; border: 1px solid transparent;
    text-decoration: none; white-space: nowrap;
    font-family: 'Manrope', sans-serif;
    transition: background .14s, color .14s, border-color .14s;
}
.cr-btn--primary { background: #003f2c; color: #fff; border-color: #003f2c; }
.cr-btn--primary:hover { background: #004532; }
.cr-btn--outline { background: #fff; color: #263232; border-color: #d4d8d8; }
.cr-btn--outline:hover { border-color: #003f2c; color: #003f2c; }
.cr-btn--ghost { background: transparent; color: #657386; border-color: transparent; }
.cr-btn--ghost:hover { background: #f3f4f4; }
.cr-btn--sm { height: 26px; padding: 0 9px; font-size: 10px; }

/* ── Body layout ─────────────────────────────────────────────────── */
.cr-body {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(0, 0.8fr);
    gap: 16px;
    padding: 16px 24px;
    align-items: start;
}
@media (max-width: 1100px) { .cr-body { grid-template-columns: 1fr; } }

.cr-main    { display: flex; flex-direction: column; gap: 12px; }
.cr-rail    { display: flex; flex-direction: column; gap: 12px; }
.cr-section { padding: 0 24px 12px; }

/* ── Cards ───────────────────────────────────────────────────────── */
.cr-card { border: 1px solid #eef2f0; border-radius: 8px; background: #fff; overflow: hidden; }
.cr-card--sticky { position: sticky; top: 12px; }
.cr-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 8px 14px; border-bottom: 1px solid #e8ecec;
    background: #f8f9f9; border-radius: 7px 7px 0 0; flex-wrap: wrap;
}
.cr-card-title {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 800; color: #1f2a2a;
    letter-spacing: .04em; text-transform: uppercase;
    font-family: 'IBM Plex Mono', monospace;
}
.cr-card-body      { padding: 14px; }
.cr-card-body--pipe { padding: 16px 14px; }

/* ── Form fields ─────────────────────────────────────────────────── */
.cr-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
@media (max-width: 640px) { .cr-grid { grid-template-columns: 1fr; } }

.cr-field { display: flex; flex-direction: column; gap: 5px; }
.cr-field--full { grid-column: 1 / -1; }
.cr-field label { font-size: 11px; font-weight: 700; color: #4b5563; letter-spacing: .03em; }
.cr-req   { color: #ef4444; }
.cr-error { font-size: 11px; color: #c0392b; margin-top: 4px; }

/* ── Input overrides ─────────────────────────────────────────────── */
.cr-input { width: 100%; }
:deep(.cr-input .el-input__wrapper),
:deep(.cr-input .el-select__wrapper) {
    background: #fff; border-radius: 6px !important;
    box-shadow: 0 0 0 1px #e6e8ea inset;
    min-height: 34px;
    font-family: 'Manrope', sans-serif; font-size: 12px;
}
:deep(.cr-input .el-input__wrapper.is-focus),
:deep(.cr-input .el-select__wrapper.is-focused) {
    box-shadow: 0 0 0 1px #003f2c inset, 0 0 0 3px rgba(0,69,50,.07);
}
:deep(.cr-input .el-textarea__inner) {
    background: #fff; border-radius: 6px !important;
    box-shadow: 0 0 0 1px #e6e8ea inset;
    font-family: 'Manrope', sans-serif; font-size: 12px;
    padding: 8px 12px; resize: vertical;
}
:deep(.cr-input .el-textarea__inner:focus) {
    box-shadow: 0 0 0 1px #003f2c inset; outline: none;
}

/* ── Form actions ────────────────────────────────────────────────── */
.cr-form-actions {
    display: flex; align-items: center; gap: 8px;
    flex-wrap: wrap; padding: 14px 0 2px;
    border-top: 1px solid #e8ecec;
}

/* ── Summary rail ────────────────────────────────────────────────── */
.cr-completion-pill {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px; font-weight: 800;
    color: #004532; background: #eef5f1;
    border: 1px solid #c3ddd2; padding: 3px 8px; border-radius: 5px;
}
.cr-detail-list { display: flex; flex-direction: column; }
.cr-detail-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 8px; padding: 6px 0; border-bottom: 1px solid #f0f2f2; font-size: 11px;
}
.cr-detail-row:last-child { border-bottom: none; }
.cr-detail-row strong { font-weight: 700; color: #1f2a2a; font-size: 11px; }

.cr-progress-label { font-size: 10px; color: #94a1b2; margin-bottom: 4px; }
.cr-progress-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #eef2f0; }
.cr-progress-fill  { height: 100%; background: #004532; border-radius: 3px; transition: width .4s; }
.cr-progress-fill--amber { background: #d97706; }

.cr-checklist { display: flex; flex-direction: column; gap: 8px; }
.cr-check-item { display: flex; align-items: center; gap: 8px; }
.cr-check-dot {
    width: 17px; height: 17px; border-radius: 50%;
    border: 2px solid #d4d8d8;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 10px; color: #fff; background: #fff;
}
.cr-check-dot--done { border-color: #004532; background: #004532; }

.cr-guide-list { margin: 0; padding: 0 0 0 1rem; display: flex; flex-direction: column; gap: 8px; }
.cr-guide-list li { font-size: 11px; color: #657386; line-height: 1.55; }

/* ── Members table ───────────────────────────────────────────────── */
.cr-table-wrap { overflow-x: auto; }
.cr-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.cr-table thead th {
    padding: 7px 10px; background: #f6f8f8;
    border-bottom: 1px solid #eef2f0;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .12em; text-transform: uppercase;
    color: #7b8796; white-space: nowrap;
}
.cr-table tbody td { padding: 7px 10px; border-bottom: 1px solid #f0f2f2; vertical-align: middle; }
.cr-table tbody tr:last-child td { border-bottom: none; }
.cr-pill {
    display: inline-flex; padding: 2px 7px; border-radius: 4px;
    background: #eef5f1; color: #004532; border: 1px solid #c3ddd2;
    font-family: 'IBM Plex Mono', monospace; font-size: 9px;
    font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
}
.cr-remove-btn {
    width: 22px; height: 22px; border-radius: 4px;
    border: 1px solid #eef2f0; background: #fafbfb;
    color: #94a1b2; cursor: pointer; font-size: 11px;
    display: flex; align-items: center; justify-content: center;
    transition: background .12s, color .12s;
}
.cr-remove-btn:hover { background: #fff0f0; color: #c0392b; border-color: #fca5a5; }
.cr-table-foot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 14px; border-top: 1px solid #e8ecec;
    background: #fafbfb; flex-wrap: wrap; gap: 8px;
}

/* ── Pipeline ────────────────────────────────────────────────────── */
.cr-pipe { display: flex; align-items: stretch; overflow-x: auto; }
.cr-pipe-item { flex: 1; min-width: 72px; display: flex; flex-direction: column; align-items: center; }
.cr-pipe-item__top { text-align: center; padding-bottom: 8px; min-height: 24px; display: flex; align-items: flex-end; justify-content: center; }
.cr-pipe-item__sub { font-size: 9px; color: #94a1b2; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; }
.cr-pipe-item__mid { display: flex; align-items: center; width: 100%; }
.cr-pipe-item__seg { flex: 1; height: 2px; background: #eef2f0; }
.cr-pipe-item__seg--done   { background: #004532; }
.cr-pipe-item__seg--hidden { background: transparent; }
.cr-pipe-item__dot {
    width: 28px; height: 28px; border-radius: 50%;
    border: 2px solid #d4d8d8; background: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; color: #fff; z-index: 1;
}
.cr-pipe-item__dot--done    { border-color: #004532; background: #004532; }
.cr-pipe-item__dot--current { border-color: #d97706; background: #d97706; outline: 4px solid rgba(217,119,6,.15); outline-offset: 1px; }
.cr-pipe-item__bot { text-align: center; padding-top: 8px; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.cr-pipe-item__label { font-size: 10px; font-weight: 800; color: #1f2a2a; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; }
.cr-pipe-item__label--current { color: #d97706; }
.cr-pipe-item__verify { font-size: 9px; color: #b8c0cc; font-family: 'IBM Plex Mono', monospace; padding: 2px 6px; border-radius: 3px; border: 1px solid #e8ecec; background: #fafbfb; white-space: nowrap; }
.cr-pipe-item__verify--done    { color: #004532; border-color: #c3ddd2; background: #eef5f1; }
.cr-pipe-item__verify--current { color: #92400e; border-color: #fde68a; background: #fffbeb; }

/* ── AI Insights ─────────────────────────────────────────────────── */
.cr-insights-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
@media (max-width: 768px) { .cr-insights-grid { grid-template-columns: 1fr; } }
.cr-insight-card { display: flex; align-items: flex-start; gap: 8px; padding: 12px; border-radius: 7px; border: 1px solid; }
.cr-insight-card--green { background: #eef5f1; border-color: #c3ddd2; }
.cr-insight-card--blue  { background: #eff6ff; border-color: #bfdbfe; }
.cr-insight-card--amber { background: #fffbeb; border-color: #fde68a; }
.cr-insight-icon { font-size: 13px; color: #004532; flex-shrink: 0; margin-top: 1px; }
.cr-insight-text { font-size: 12px; font-weight: 600; color: #1f2a2a; line-height: 1.55; margin: 0; }

/* ── Helpers ─────────────────────────────────────────────────────── */
.cr-muted { font-size: 11px; color: #94a1b2; }
.cr-bold  { font-weight: 700; color: #1f2a2a; }

/* ── Floating chat ───────────────────────────────────────────────── */
.cr-float { position: fixed; bottom: 24px; right: 24px; z-index: 200; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.cr-float-btn { width: 44px; height: 44px; border-radius: 50%; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: background .14s; }
.cr-float-btn:hover { background: #004532; }
.cr-chat-panel { width: 290px; border: 1px solid #eef2f0; border-radius: 10px; background: #fff; overflow: hidden; }
.cr-chat-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 13px; border-bottom: 1px solid #e8ecec; background: #f8f9f9; }
.cr-chat-close { font-size: 18px; line-height: 1; background: none; border: none; color: #94a1b2; cursor: pointer; padding: 0; }
.cr-chat-prompts { padding: 8px 12px; display: flex; flex-direction: column; gap: 5px; border-bottom: 1px solid #f0f2f2; }
.cr-chat-prompt { text-align: left; background: #f8f9f9; border: 1px solid #eef2f0; border-radius: 5px; padding: 6px 9px; font-size: 11px; color: #263232; cursor: pointer; font-family: 'Manrope', sans-serif; transition: background .14s; }
.cr-chat-prompt:hover { background: #eef5f1; border-color: #c3ddd2; color: #004532; }
.cr-chat-input-row { display: flex; align-items: center; padding: 8px 12px; gap: 6px; }
.cr-chat-input { flex: 1; height: 30px; padding: 0 9px; border: 1px solid #d4d8d8; border-radius: 5px; font-size: 11px; outline: none; font-family: 'Manrope', sans-serif; }
.cr-chat-input:focus { border-color: #003f2c; }
.cr-chat-send { width: 30px; height: 30px; border-radius: 5px; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 1100px) { .cr-rail { position: static !important; } }
@media (max-width: 640px) {
    .cr-header { padding: 10px 14px; }
    .cr-body, .cr-section { padding-left: 14px; padding-right: 14px; }
    .cr-insights-grid { grid-template-columns: 1fr; }
}
</style>
