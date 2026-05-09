<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    Calendar, ChatDotRound, Check, CircleCheck,
    Collection, CollectionTag, DataLine,
    Plus, Promotion, Tickets,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    regionOptions: { type: Array, default: () => [] },
});

/* ── UI-only state (no new form fields) ──────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');

/* ── Form — existing fields only (unchanged) ─────────────── */
const form = useForm({
    name:       '',
    region:     '',
    start_date: '',
    end_date:   '',
    notes:      '',
});

/* ── Computed ────────────────────────────────────────────── */
const formCompletion = computed(() => {
    const fields = [form.name, form.region, form.start_date, form.end_date];
    return Math.round(fields.filter(Boolean).length / fields.length * 100);
});

const exportReadiness = computed(() => {
    let s = 20;
    if (form.name)                          s += 25;
    if (form.region)                        s += 25;
    if (form.start_date && form.end_date)   s += 30;
    return Math.min(s, 100);
});

const chatPrompts = ['Is this season export ready?', 'What quality should I expect?', 'Which market should I target?'];
const fillPrompt  = p => { chatInput.value = p; };

/* ── Submit (unchanged) ──────────────────────────────────── */
const submit = () => form.post(route('season.store'));
</script>

<template>
    <AppLayout title="Create Season" full-width flush :show-banner="false">

        <div class="sc-page">

            <!-- Header ────────────────────────────────────────────── -->
            <div class="sc-header">
                <div class="sc-header-left">
                    <div class="sc-kicker">Season Management</div>
                    <h1 class="sc-title mt-2 mb-2">Create Season</h1>
                    <p class="sc-subtitle">Manage coffee production seasons and harvest planning</p>
                    <div class="sc-badge-row mt-2">
                        <span class="sc-badge sc-badge--green">Traceable</span>
                        <span class="sc-badge sc-badge--blue">Production Cycle</span>
                        <span class="sc-badge sc-badge--amber">Export Ready</span>
                    </div>
                </div>
                <div class="sc-header-actions">
                    <Link :href="route('season.index')" class="sc-btn sc-btn--outline">
                        <el-icon><Collection /></el-icon> View Seasons
                    </Link>
                    <button type="button" class="sc-btn sc-btn--outline">
                        <el-icon><CollectionTag /></el-icon> Add Harvest
                    </button>

                </div>
            </div>

            <!-- Form + Summary ────────────────────────────────────── -->
            <form class="sc-body" @submit.prevent="submit">

                <!-- Left: form sections -->
                <div class="sc-main">

                    <!-- Section A: Basic Information ──────────────────── -->
                    <div class="sc-card">
                        <div class="sc-card-head">
                            <div class="sc-card-title"><el-icon><Tickets /></el-icon> Basic Information</div>
                            <span class="sc-badge sc-badge--blue" style="font-size:9px;">Section A</span>
                        </div>
                        <div class="sc-card-body">
                            <div class="sc-grid">
                                <div class="sc-field sc-field--full">
                                    <label>Season Name <span class="sc-req">*</span></label>
                                    <el-input
                                        v-model="form.name"
                                        placeholder="e.g. Uganda Main Harvest 2026"
                                        class="sc-input"
                                    />
                                    <InputError :message="form.errors.name" class="sc-error" />
                                </div>
                                <div class="sc-field sc-field--full">
                                    <label>Region / Origin <span class="sc-req">*</span></label>
                                    <el-select
                                        v-model="form.region"
                                        clearable
                                        filterable
                                        placeholder="Select region"
                                        class="sc-input !w-full"
                                    >
                                        <el-option
                                            v-for="r in regionOptions"
                                            :key="r"
                                            :label="r"
                                            :value="r"
                                        />
                                    </el-select>
                                    <InputError :message="form.errors.region" class="sc-error" />
                                </div>
                                <div class="sc-field sc-field--full">
                                    <label>Description / Notes</label>
                                    <el-input
                                        v-model="form.notes"
                                        type="textarea"
                                        :rows="3"
                                        placeholder="Add harvest expectations, trade windows, or regional context."
                                        class="sc-input"
                                    />
                                    <InputError :message="form.errors.notes" class="sc-error" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section B: Season Timeline ────────────────────── -->
                    <div class="sc-card">
                        <div class="sc-card-head">
                            <div class="sc-card-title"><el-icon><Calendar /></el-icon> Season Timeline</div>
                            <span class="sc-badge sc-badge--green" style="font-size:9px;">Section B</span>
                        </div>
                        <div class="sc-card-body ">
                            <div class="sc-grid pb-3">
                                <div class="sc-field">
                                    <label>Start Date <span class="sc-req">*</span></label>
                                    <el-date-picker
                                        v-model="form.start_date"
                                        type="date"
                                        value-format="YYYY-MM-DD"
                                        placeholder="Select start date"
                                        class="sc-input !w-full mb-2"
                                    />
                                    <InputError :message="form.errors.start_date" class="sc-error sc-error--date" />
                                </div>
                                <div class="sc-field">
                                    <label>End Date <span class="sc-req">*</span></label>
                                    <el-date-picker
                                        v-model="form.end_date"
                                        type="date"
                                        value-format="YYYY-MM-DD"
                                        placeholder="Select end date"
                                        class="sc-input !w-full mb-2"
                                    />
                                    <InputError :message="form.errors.end_date" class="sc-error sc-error--date" />
                                </div>
                            </div>


                        </div>
                    </div>

                    <!-- Actions ────────────────────────────────────────── -->
                    <div class="sc-form-actions">
                        <SubmitButton
                            native-type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                            style="width:150px;"
                        >
                            <el-icon class="me-1"><Plus /></el-icon> Create Season
                        </SubmitButton>

                    </div>

                </div><!-- /sc-main -->

                <!-- Right: sticky summary ────────────────────────────── -->
                <aside class="sc-rail">

                    <!-- Live summary card -->
                    <div class="sc-card sc-card--sticky">
                        <div class="sc-card-head">
                            <div class="sc-card-title"><el-icon><DataLine /></el-icon> Season Summary</div>
                            <span class="sc-completion-pill">{{ formCompletion }}% Complete</span>
                        </div>
                        <div class="sc-card-body">
                            <div class="sc-progress-label">Form completion</div>
                            <div class="sc-progress-track mb-3">
                                <div class="sc-progress-fill" :style="{ width: formCompletion + '%' }" />
                            </div>

                            <div class="sc-detail-list">
                                <div class="sc-detail-row">
                                    <span class="sc-muted">Season Name</span>
                                    <strong>{{ form.name || '—' }}</strong>
                                </div>
                                <div class="sc-detail-row">
                                    <span class="sc-muted">Region</span>
                                    <strong>{{ form.region || '—' }}</strong>
                                </div>
                                <div class="sc-detail-row">
                                    <span class="sc-muted">Start Date</span>
                                    <strong>{{ form.start_date || '—' }}</strong>
                                </div>
                                <div class="sc-detail-row">
                                    <span class="sc-muted">End Date</span>
                                    <strong>{{ form.end_date || '—' }}</strong>
                                </div>
                                <div class="sc-detail-row">
                                    <span class="sc-muted">Notes</span>
                                    <strong>{{ form.notes ? 'Added' : '—' }}</strong>
                                </div>
                            </div>

                            <div style="margin-top:12px;">
                                <div class="sc-progress-label">Export Readiness — {{ exportReadiness }}%</div>
                                <div class="sc-progress-track">
                                    <div class="sc-progress-fill sc-progress-fill--amber" :style="{ width: exportReadiness + '%' }" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Readiness checklist -->
                    <div class="sc-card">
                        <div class="sc-card-head">
                            <div class="sc-card-title"><el-icon><CircleCheck /></el-icon> Readiness</div>
                        </div>
                        <div class="sc-card-body">
                            <div class="sc-checklist">
                                <div
                                    v-for="item in [
                                        { label: 'Season Name Set',   done: Boolean(form.name) },
                                        { label: 'Region Selected',   done: Boolean(form.region) },
                                        { label: 'Start Date Set',    done: Boolean(form.start_date) },
                                        { label: 'End Date Set',      done: Boolean(form.end_date) },
                                        { label: 'Notes Added',       done: Boolean(form.notes) },
                                    ]"
                                    :key="item.label"
                                    class="sc-check-item"
                                >
                                    <span class="sc-check-dot" :class="{ 'sc-check-dot--done': item.done }">
                                        <el-icon v-if="item.done"><Check /></el-icon>
                                    </span>
                                    <span :class="item.done ? 'sc-bold' : 'sc-muted'" style="font-size:11px;">
                                        {{ item.label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>


                </aside>

            </form><!-- /sc-body -->

        </div><!-- /sc-page -->

        <!-- ⑥ Floating Chatbot ────────────────────────────────────────── -->
        <div class="sc-float">
            <div v-if="chatOpen" class="sc-chat-panel">
                <div class="sc-chat-head">
                    <div>
                        <div class="sc-bold" style="font-size:12px;">Bean Origin Season Advisor</div>
                        <div class="sc-muted" style="font-size:10px;">Season planning assistant</div>
                    </div>
                    <button class="sc-chat-close" type="button" @click="chatOpen = false">×</button>
                </div>
                <div class="sc-chat-prompts">
                    <button v-for="p in chatPrompts" :key="p" class="sc-chat-prompt" type="button" @click="fillPrompt(p)">{{ p }}</button>
                </div>
                <div class="sc-chat-input-row">
                    <input v-model="chatInput" class="sc-chat-input" placeholder="Ask about this season…" />
                    <button class="sc-chat-send" type="button"><el-icon><Promotion /></el-icon></button>
                </div>
            </div>
            <button class="sc-float-btn" type="button" @click="chatOpen = !chatOpen">
                <el-icon><ChatDotRound /></el-icon>
            </button>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ── Base ───────────────────────────────────────────────────────── */
.sc-page {
    min-height: calc(100vh - 48px);
    background: #fff;
    color: #1f2a2a;
    padding-bottom: 60px;
    font-family: 'Manrope', sans-serif;
}

/* ── Header ─────────────────────────────────────────────────────── */
.sc-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; padding: 10px 24px;
    border-bottom: 1px solid #e8ecec;
    background: #fff; flex-wrap: wrap;
}
.sc-kicker {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .16em; text-transform: uppercase;
    color: #94a1b2; margin-bottom: 4px;
}
.sc-title   { font-size: 18px; font-weight: 800; color: #003f2c; margin: 0 0 3px; line-height: 1.15; }
.sc-subtitle { font-size: 12px; color: #657386; margin: 0 0 8px; line-height: 1.5; }
.sc-badge-row { display: flex; gap: 5px; flex-wrap: wrap; }
.sc-header-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }

/* ── Badges ──────────────────────────────────────────────────────── */
.sc-badge {
    display: inline-flex; align-items: center;
    padding: 3px 7px; border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 700;
    letter-spacing: .1em; text-transform: uppercase;
}
.sc-badge--green  { background: #eef5f1; color: #004532; border: 1px solid #c3ddd2; }
.sc-badge--blue   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.sc-badge--amber  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.sc-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    padding: 0 12px; height: 30px; border-radius: 5px;
    font-size: 11px; font-weight: 700; letter-spacing: .03em;
    cursor: pointer; border: 1px solid transparent;
    text-decoration: none; white-space: nowrap;
    font-family: 'Manrope', sans-serif;
    transition: background .14s, color .14s, border-color .14s;
}
.sc-btn--primary { background: #003f2c; color: #fff; border-color: #003f2c; }
.sc-btn--primary:hover { background: #004532; }
.sc-btn--outline { background: #fff; color: #263232; border-color: #d4d8d8; }
.sc-btn--outline:hover { border-color: #003f2c; color: #003f2c; }
.sc-btn--ghost { background: transparent; color: #657386; border-color: transparent; }
.sc-btn--ghost:hover { background: #f3f4f4; }
.sc-btn--sm { height: 26px; padding: 0 9px; font-size: 10px; }

/* ── Body layout ─────────────────────────────────────────────────── */
.sc-body {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(0, 0.8fr);
    gap: 16px;
    padding: 16px 24px;
    align-items: start;
}
@media (max-width: 1100px) { .sc-body { grid-template-columns: 1fr; } }

.sc-main  { display: flex; flex-direction: column; gap: 12px; }
.sc-rail  { display: flex; flex-direction: column; gap: 12px; }
.sc-section { padding: 0 24px 12px; }

/* ── Cards ───────────────────────────────────────────────────────── */
.sc-card { border: 1px solid #e4e7e8; border-radius: 8px; background: #fff; overflow: hidden; }
.sc-card--sticky { position: sticky; top: 12px; }
.sc-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 8px 14px; border-bottom: 1px solid #e8ecec;
    background: #f8f9f9; border-radius: 7px 7px 0 0; flex-wrap: wrap;
}
.sc-card-title {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 800; color: #1f2a2a;
    letter-spacing: .04em; text-transform: uppercase;
    font-family: 'IBM Plex Mono', monospace;
}
.sc-card-body      { padding: 14px; }
.sc-card-body--pipe { padding: 16px 14px; }

/* ── Form fields ─────────────────────────────────────────────────── */
.sc-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
@media (max-width: 640px) { .sc-grid { grid-template-columns: 1fr; } }

.sc-field { display: flex; flex-direction: column; gap: 5px; }
.sc-field--full { grid-column: 1 / -1; }
.sc-field label { font-size: 11px; font-weight: 700; color: #4b5563; letter-spacing: .03em; }
.sc-req { color: #ef4444; }
.sc-error { font-size: 11px; color: #c0392b; margin-top: 4px; }
.sc-error--date { margin-top: 10px; display: block; }

/* ── Input overrides ─────────────────────────────────────────────── */
.sc-input { width: 100%; }
:deep(.sc-input .el-input__wrapper),
:deep(.sc-input .el-select__wrapper),
:deep(.sc-input.el-date-editor .el-input__wrapper) {
    background: #fff; border-radius: 6px !important;
    box-shadow: 0 0 0 1px #e6e8ea inset;
    min-height: 34px;
    font-family: 'Manrope', sans-serif; font-size: 12px;
}
:deep(.sc-input .el-input__wrapper.is-focus),
:deep(.sc-input .el-select__wrapper.is-focused),
:deep(.sc-input.el-date-editor .el-input__wrapper.is-focus) {
    box-shadow: 0 0 0 1px #003f2c inset, 0 0 0 3px rgba(0,69,50,.07);
}
:deep(.sc-input .el-textarea__inner) {
    background: #fff; border-radius: 6px !important;
    box-shadow: 0 0 0 1px #e6e8ea inset;
    font-family: 'Manrope', sans-serif; font-size: 12px;
    padding: 8px 12px; resize: vertical;
}
:deep(.sc-input .el-textarea__inner:focus) {
    box-shadow: 0 0 0 1px #003f2c inset; outline: none;
}
:deep(.sc-input.el-date-editor) { width: 100%; }

/* ── Timeline visualization ──────────────────────────────────────── */
.sc-timeline {
    display: flex; align-items: center;
    margin-top: 16px; padding: 12px 16px;
    border: 1px solid #e4e7e8; border-radius: 7px;
    background: #fafbfb; gap: 0;
}
.sc-tl-item {
    display: flex; flex-direction: column; align-items: center; gap: 5px;
    flex-shrink: 0; min-width: 90px;
}
.sc-tl-seg {
    flex: 1; height: 2px; background: #e4e7e8;
    transition: background .3s;
}
.sc-tl-seg--on { background: #004532; }
.sc-tl-dot {
    width: 22px; height: 22px; border-radius: 50%;
    border: 2px solid #d4d8d8; background: #fff;
    transition: background .2s, border-color .2s;
}
.sc-tl-dot--on  { background: #004532; border-color: #004532; }
.sc-tl-dot--mid { background: #f0f2f2; border-color: #c3ddd2; }
.sc-tl-name { font-size: 10px; font-weight: 700; color: #1f2a2a; font-family: 'IBM Plex Mono', monospace; letter-spacing: .04em; text-align: center; }
.sc-tl-date { font-size: 9px; color: #94a1b2; text-align: center; }

/* ── Form actions ────────────────────────────────────────────────── */
.sc-form-actions {
    display: flex; align-items: center; gap: 8px;
    flex-wrap: wrap; padding: 14px 0 2px;
    border-top: 1px solid #e8ecec;
}

/* ── Summary rail ────────────────────────────────────────────────── */
.sc-completion-pill {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px; font-weight: 800;
    color: #004532; background: #eef5f1;
    border: 1px solid #c3ddd2; padding: 3px 8px; border-radius: 5px;
}
.sc-detail-list { display: flex; flex-direction: column; }
.sc-detail-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 8px; padding: 6px 0; border-bottom: 1px solid #f0f2f2;
    font-size: 11px;
}
.sc-detail-row:last-child { border-bottom: none; }
.sc-detail-row strong { font-weight: 700; color: #1f2a2a; font-size: 11px; }

.sc-progress-label { font-size: 10px; color: #94a1b2; margin-bottom: 4px; }
.sc-progress-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.sc-progress-fill { height: 100%; background: #004532; border-radius: 3px; transition: width .4s; }
.sc-progress-fill--amber { background: #d97706; }

.sc-checklist { display: flex; flex-direction: column; gap: 8px; }
.sc-check-item { display: flex; align-items: center; gap: 8px; }
.sc-check-dot {
    width: 17px; height: 17px; border-radius: 50%;
    border: 2px solid #d4d8d8;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 10px; color: #fff; background: #fff;
}
.sc-check-dot--done { border-color: #004532; background: #004532; }

.sc-insight-mini {
    display: flex; align-items: flex-start; gap: 7px;
    padding: 9px; border-radius: 6px; border: 1px solid;
}
.sc-insight-mini--green  { background: #eef5f1; border-color: #c3ddd2; color: #004532; }
.sc-insight-mini--blue   { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.sc-insight-mini--amber  { background: #fffbeb; border-color: #fde68a; color: #92400e; }

/* ── Harvest table ───────────────────────────────────────────────── */
.sc-table-wrap { overflow-x: auto; }
.sc-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.sc-table thead th {
    padding: 7px 10px; background: #f6f8f8;
    border-bottom: 1px solid #e4e7e8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .12em; text-transform: uppercase;
    color: #7b8796; white-space: nowrap;
}
.sc-table tbody td { padding: 7px 10px; border-bottom: 1px solid #f0f2f2; vertical-align: middle; }
.sc-table tbody tr:last-child td { border-bottom: none; }
.sc-pill {
    display: inline-flex; padding: 2px 7px; border-radius: 4px;
    background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;
    font-family: 'IBM Plex Mono', monospace; font-size: 9px;
    font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
}
.sc-remove-btn {
    width: 22px; height: 22px; border-radius: 4px;
    border: 1px solid #e4e7e8; background: #fafbfb;
    color: #94a1b2; cursor: pointer; font-size: 11px;
    display: flex; align-items: center; justify-content: center;
    transition: background .12s, color .12s;
}
.sc-remove-btn:hover { background: #fff0f0; color: #c0392b; border-color: #fca5a5; }
.sc-table-foot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 14px; border-top: 1px solid #e8ecec;
    background: #fafbfb; flex-wrap: wrap; gap: 8px;
}

/* ── Pipeline ────────────────────────────────────────────────────── */
.sc-pipe { display: flex; align-items: stretch; overflow-x: auto; }
.sc-pipe-item { flex: 1; min-width: 70px; display: flex; flex-direction: column; align-items: center; }
.sc-pipe-item__top { text-align: center; padding-bottom: 8px; min-height: 24px; display: flex; align-items: flex-end; justify-content: center; }
.sc-pipe-item__sub { font-size: 9px; color: #94a1b2; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; }
.sc-pipe-item__mid { display: flex; align-items: center; width: 100%; }
.sc-pipe-item__seg { flex: 1; height: 2px; background: #e4e7e8; }
.sc-pipe-item__seg--done   { background: #004532; }
.sc-pipe-item__seg--hidden { background: transparent; }
.sc-pipe-item__dot {
    width: 28px; height: 28px; border-radius: 50%;
    border: 2px solid #d4d8d8; background: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; color: #fff; z-index: 1;
}
.sc-pipe-item__dot--done    { border-color: #004532; background: #004532; }
.sc-pipe-item__dot--current { border-color: #d97706; background: #d97706; outline: 4px solid rgba(217,119,6,.15); outline-offset: 1px; }
.sc-pipe-item__bot { text-align: center; padding-top: 8px; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.sc-pipe-item__label { font-size: 10px; font-weight: 800; color: #1f2a2a; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; }
.sc-pipe-item__label--current { color: #d97706; }
.sc-pipe-item__verify { font-size: 9px; color: #b8c0cc; font-family: 'IBM Plex Mono', monospace; padding: 2px 6px; border-radius: 3px; border: 1px solid #e8ecec; background: #fafbfb; white-space: nowrap; }
.sc-pipe-item__verify--done    { color: #004532; border-color: #c3ddd2; background: #eef5f1; }
.sc-pipe-item__verify--current { color: #92400e; border-color: #fde68a; background: #fffbeb; }

/* ── AI Insights ─────────────────────────────────────────────────── */
.sc-insights-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
@media (max-width: 768px) { .sc-insights-grid { grid-template-columns: 1fr; } }
.sc-insight-card { display: flex; align-items: flex-start; gap: 8px; padding: 12px; border-radius: 7px; border: 1px solid; }
.sc-insight-card--green { background: #eef5f1; border-color: #c3ddd2; }
.sc-insight-card--blue  { background: #eff6ff; border-color: #bfdbfe; }
.sc-insight-card--amber { background: #fffbeb; border-color: #fde68a; }
.sc-insight-icon { font-size: 13px; color: #004532; flex-shrink: 0; margin-top: 1px; }
.sc-insight-text { font-size: 12px; font-weight: 600; color: #1f2a2a; line-height: 1.55; margin: 0; }

/* ── Helpers ─────────────────────────────────────────────────────── */
.sc-muted { font-size: 11px; color: #94a1b2; }
.sc-bold  { font-weight: 700; color: #1f2a2a; }

/* ── Floating chat ───────────────────────────────────────────────── */
.sc-float { position: fixed; bottom: 24px; right: 24px; z-index: 200; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.sc-float-btn { width: 44px; height: 44px; border-radius: 50%; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: background .14s; }
.sc-float-btn:hover { background: #004532; }
.sc-chat-panel { width: 290px; border: 1px solid #e4e7e8; border-radius: 10px; background: #fff; overflow: hidden; }
.sc-chat-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 13px; border-bottom: 1px solid #e8ecec; background: #f8f9f9; }
.sc-chat-close { font-size: 18px; line-height: 1; background: none; border: none; color: #94a1b2; cursor: pointer; padding: 0; }
.sc-chat-prompts { padding: 8px 12px; display: flex; flex-direction: column; gap: 5px; border-bottom: 1px solid #f0f2f2; }
.sc-chat-prompt { text-align: left; background: #f8f9f9; border: 1px solid #e4e7e8; border-radius: 5px; padding: 6px 9px; font-size: 11px; color: #263232; cursor: pointer; font-family: 'Manrope', sans-serif; transition: background .14s; }
.sc-chat-prompt:hover { background: #eef5f1; border-color: #c3ddd2; color: #004532; }
.sc-chat-input-row { display: flex; align-items: center; padding: 8px 12px; gap: 6px; }
.sc-chat-input { flex: 1; height: 30px; padding: 0 9px; border: 1px solid #d4d8d8; border-radius: 5px; font-size: 11px; outline: none; font-family: 'Manrope', sans-serif; }
.sc-chat-input:focus { border-color: #003f2c; }
.sc-chat-send { width: 30px; height: 30px; border-radius: 5px; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 1100px) { .sc-rail { position: static !important; } }
@media (max-width: 640px) {
    .sc-header { padding: 10px 14px; }
    .sc-body, .sc-section { padding-left: 14px; padding-right: 14px; }
    .sc-insights-grid { grid-template-columns: 1fr; }
}
</style>
