<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Calendar, Plus, Tickets } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    regionOptions: { type: Array, default: () => [] },
});

const form = useForm({
    name:       '',
    region:     '',
    start_date: '',
    end_date:   '',
    notes:      '',
});

const submit = () => form.post(route('season.store'));
</script>

<template>
    <AppLayout title="Create Season" full-width flush :show-banner="false">
        <Head title="Create Season" />

        <div class="cs-root">

            <!-- ── Header ──────────────────────────────────────────────── -->
            <section class="cs-hero">
                <div class="cs-hero__inner">
                    <div>
                        <h1 class="cs-hero__title">Create Season</h1>
                        <p class="cs-hero__sub">Add a new seasonal window for harvest planning and regional cycle tracking.</p>
                    </div>
                    <Link :href="route('season.index')" class="cs-btn cs-btn--ghost">
                        Back to Seasons
                    </Link>
                </div>
            </section>

            <!-- ── Form ────────────────────────────────────────────────── -->
            <div class="cs-body">
                <form class="cs-form" @submit.prevent="submit">

                    <!-- Card: Core Details -->
                    <div class="cs-card">
                        <div class="cs-card__head">
                            <span class="cs-card__icon"><el-icon><Tickets /></el-icon></span>
                            <div>
                                <div class="cs-card__title">Season Details</div>
                                <div class="cs-card__sub">Set the name, region, and date window for this season.</div>
                            </div>
                        </div>

                        <div class="cs-grid">
                            <div class="cs-field cs-field--full">
                                <label class="cs-label">Season Name <span class="cs-required">*</span></label>
                                <el-input
                                    v-model="form.name"
                                    placeholder="e.g. Uganda Main Harvest 2026"
                                    :prefix-icon="Tickets"
                                    class="cs-input"
                                />
                                <InputError class="cs-error" :message="form.errors.name" />
                            </div>

                            <div class="cs-field">
                                <label class="cs-label">Region <span class="cs-required">*</span></label>
                                <el-select
                                    v-model="form.region"
                                    placeholder="Select region"
                                    class="cs-input"
                                >
                                    <el-option
                                        v-for="region in regionOptions"
                                        :key="region"
                                        :label="region"
                                        :value="region"
                                    />
                                </el-select>
                                <InputError class="cs-error" :message="form.errors.region" />
                            </div>

                            <div class="cs-field">
                                <label class="cs-label">Start Date <span class="cs-required">*</span></label>
                                <el-date-picker
                                    v-model="form.start_date"
                                    type="date"
                                    value-format="YYYY-MM-DD"
                                    placeholder="Select start date"
                                    :prefix-icon="Calendar"
                                    class="cs-input"
                                />
                                <InputError class="cs-error cs-error--date mt-3" :message="form.errors.start_date" />
                            </div>

                            <div class="cs-field cs-field--full">
                                <label class="cs-label">End Date <span class="cs-required">*</span></label>
                                <el-date-picker
                                    v-model="form.end_date"
                                    type="date"
                                    value-format="YYYY-MM-DD"
                                    placeholder="Select end date"
                                    :prefix-icon="Calendar"
                                    class="cs-input"
                                />
                                <InputError class="cs-error cs-error--date mt-3" :message="form.errors.end_date" />
                            </div>

                            <div class="cs-field cs-field--full">
                                <label class="cs-label">Notes</label>
                                <el-input
                                    v-model="form.notes"
                                    type="textarea"
                                    :rows="4"
                                    placeholder="Add harvest expectations, trade windows, or regional context."
                                    class="cs-input"
                                />
                                <InputError class="cs-error" :message="form.errors.notes" />
                            </div>
                        </div>

                        <!-- Actions inside card -->
                        <div class="cs-actions">
                            <SubmitButton :loading="form.processing" style="width:160px;">
                                <el-icon class="mr-2"><Plus /></el-icon> Create Season
                            </SubmitButton>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.cs-root {
    --primary:        #004532;
    --primary-grad:   #065f46;
    --on-primary:     #ffffff;
    --on-surface:     #191c1e;
    --on-surface-var: #74777a;
    --surface-white:  #ffffff;
    --surface-low:    #f2f4f6;
    --surface-high:   #e6e8ea;
    --required:       #c0392b;
    --inner-pad:      2rem;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
    color: var(--on-surface);
    min-height: 100%;
    margin: 0;
    padding: 0;
}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.cs-hero {
    background: var(--surface-white);
    border-bottom: 1px solid var(--surface-high);
    padding: 1.5rem 0;
}
.cs-hero__inner {
    padding: 0 var(--inner-pad);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.cs-hero__title {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    margin: 0 0 0.25rem;
    line-height: 1.2;
}
.cs-hero__sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.5;
}

/* ── Body ──────────────────────────────────────────────────────────────────── */
.cs-body {
    padding: 2rem var(--inner-pad);
    padding-right: 10%;
}

/* ── Form ──────────────────────────────────────────────────────────────────── */
.cs-form { display: flex; flex-direction: column; gap: 1.5rem; }

/* ── Card ──────────────────────────────────────────────────────────────────── */
.cs-card {
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    padding: 1.5rem;
}
.cs-card__head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--surface-low);
}
.cs-card__icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(0,69,50,0.08);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.cs-card__title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--on-surface);
    margin-bottom: 2px;
}
.cs-card__sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    line-height: 1.5;
}

/* ── Grid ──────────────────────────────────────────────────────────────────── */
.cs-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem;
}
.cs-field { display: flex; flex-direction: column; gap: 6px; }
.cs-field--full { grid-column: 1 / -1; }

/* ── Labels ────────────────────────────────────────────────────────────────── */
.cs-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
}
.cs-required { color: var(--required); margin-left: 2px; }
.cs-error { font-size: 0.75rem; color: var(--required); margin-top: 4px; }
.cs-error--date { margin-top: 10px; display: block; }

/* ── Element Plus input overrides ──────────────────────────────────────────── */
.cs-input { width: 100%; }

:deep(.cs-input .el-input__wrapper),
:deep(.cs-input .el-select__wrapper),
:deep(.cs-input.el-date-editor .el-input__wrapper) {
    background: var(--surface-white);
    border-radius: 6px !important;
    box-shadow: 0 0 0 1px var(--surface-high) inset;
    min-height: 42px;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem;
}
:deep(.cs-input .el-input__wrapper.is-focus),
:deep(.cs-input .el-select__wrapper.is-focused),
:deep(.cs-input.el-date-editor .el-input__wrapper.is-focus) {
    box-shadow: 0 0 0 1px var(--primary) inset,
                0 0 0 3px rgba(0,69,50,0.08);
}
:deep(.cs-input .el-textarea__inner) {
    background: var(--surface-white);
    border-radius: 6px !important;
    box-shadow: 0 0 0 1px var(--surface-high) inset;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem;
    padding: 10px 14px;
    resize: vertical;
}
:deep(.cs-input .el-textarea__inner:focus) {
    box-shadow: 0 0 0 1px var(--primary) inset,
                0 0 0 3px rgba(0,69,50,0.08);
    outline: none;
}
:deep(.cs-input.el-date-editor) { width: 100%; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.cs-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem;
    font-weight: 700;
    border-radius: 0.375rem;
    padding: 9px 18px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.15s ease, background 0.15s ease;
}
.cs-btn--primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
    width: auto;
}
.cs-btn--primary:hover { opacity: 0.88; }
.cs-btn--ghost {
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    color: var(--on-surface);
}
.cs-btn--ghost:hover { background: var(--surface-low); }

/* ── Actions (inside card) ─────────────────────────────────────────────────── */
.cs-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--surface-low);
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .cs-root { --inner-pad: 1rem; }
    .cs-body { padding-top: 1.25rem; }
    .cs-grid { grid-template-columns: 1fr; }
    .cs-hero__inner { flex-direction: column; align-items: flex-start; gap: 1rem; }
    .cs-actions { justify-content: flex-end; }
}
</style>
