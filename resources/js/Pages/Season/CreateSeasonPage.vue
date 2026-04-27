<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Tickets } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    regionOptions: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    region: '',
    start_date: '',
    end_date: '',
    notes: '',
});

const submit = () => {
    form.post(route('season.store'));
};
</script>

<template>
    <AppLayout title="Create Season">
        <Head title="Create Season" />

        <div class="season-create-page">
            <div class="season-create-shell">
                <section class="season-create-hero">
                    <div>
                        <div class="season-create-eyebrow">Season Planning</div>
                        <h1 class="season-create-title">Create Season</h1>
                        <p class="season-create-copy">
                            Add a new seasonal window for harvest planning, sourcing alignment, and regional coffee cycle tracking.
                        </p>
                    </div>

                    <div class="season-create-hero-actions">
                        <Link :href="route('season.index')" class="season-create-back-link">Back to Seasons</Link>
                    </div>
                </section>

                <section class="season-create-card">
                    <div class="season-create-card-head">
                        <div class="season-create-card-title-wrap">
                            <span class="season-create-card-icon">
                                <el-icon><Plus /></el-icon>
                            </span>
                            <div>
                                <div class="season-create-card-title">Season Details</div>
                                <div class="season-create-card-copy">
                                    Complete the core season details using the structured form below.
                                </div>
                            </div>
                        </div>
                    </div>

                    <form class="season-create-form" @submit.prevent="submit">
                        <div class="season-create-grid">
                            <div class="season-create-field">
                                <label class="season-create-label">Season Name</label>
                                <el-input v-model="form.name" placeholder="e.g. Uganda Main Harvest 2026" :prefix-icon="Tickets" />
                                <InputError class="mt-2 text-sm" :message="form.errors.name" />
                            </div>

                            <div class="season-create-field">
                                <label class="season-create-label">Region</label>
                                <el-select v-model="form.region" placeholder="Select region">
                                    <el-option v-for="region in regionOptions" :key="region" :label="region" :value="region" />
                                </el-select>
                                <InputError class="mt-2 text-sm" :message="form.errors.region" />
                            </div>

                            <div class="season-create-field">
                                <label class="season-create-label">Start Date</label>
                                <el-date-picker
                                    v-model="form.start_date"
                                    type="date"
                                    value-format="YYYY-MM-DD"
                                    placeholder="Select start date"
                                />
                                <InputError class="mt-2 text-sm" :message="form.errors.start_date" />
                            </div>

                            <div class="season-create-field">
                                <label class="season-create-label">End Date</label>
                                <el-date-picker
                                    v-model="form.end_date"
                                    type="date"
                                    value-format="YYYY-MM-DD"
                                    placeholder="Select end date"
                                />
                                <InputError class="mt-2 text-sm" :message="form.errors.end_date" />
                            </div>

                            <div class="season-create-field season-create-field--full">
                                <label class="season-create-label">Notes</label>
                                <el-input
                                    v-model="form.notes"
                                    type="textarea"
                                    :rows="5"
                                    placeholder="Add season notes, trade windows, harvest expectations, or regional context."
                                />
                                <InputError class="mt-2 text-sm" :message="form.errors.notes" />
                            </div>
                        </div>

                        <div class="season-create-actions">
                            <SubmitButton class="season-create-submit" :loading="form.processing" :full-width="false">
                                Create Season
                            </SubmitButton>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.season-create-page {
    background: #fff;
    padding-top: 6px;
}

.season-create-shell {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin: 0 auto;
    max-width: 1180px;
}

.season-create-hero,
.season-create-card-head,
.season-create-actions {
    align-items: center;
    display: flex;
    gap: 16px;
    justify-content: space-between;
}

.season-create-eyebrow,
.season-create-card-title {
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.season-create-eyebrow {
    color: #8a9692;
    font-size: 11px;
    font-weight: 700;
}

.season-create-title {
    color: #16312b;
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.05em;
    margin: 10px 0 0;
}

.season-create-copy,
.season-create-card-copy {
    color: #60716c;
    font-size: 14px;
    line-height: 1.6;
}

.season-create-copy {
    margin: 10px 0 0;
    max-width: 720px;
}

.season-create-back-link {
    border: 1px solid #d7e2de;
    border-radius: 999px;
    color: #24443d;
    display: inline-flex;
    font-size: 13px;
    font-weight: 700;
    padding: 10px 16px;
    text-decoration: none;
    transition: background-color 0.18s ease, border-color 0.18s ease;
}

.season-create-back-link:hover {
    background: #f4f8f7;
    border-color: #bbcdc7;
}

.season-create-card {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid #dfe8e4;
    border-radius: 24px;
    box-shadow: 0 18px 44px rgba(9, 35, 30, 0.06);
    padding: 22px;
}

.season-create-card-title-wrap {
    align-items: center;
    display: flex;
    gap: 14px;
}

.season-create-card-title {
    color: #4c6860;
    font-size: 12px;
    font-weight: 700;
}

.season-create-card-copy {
    margin-top: 4px;
}

.season-create-card-icon {
    align-items: center;
    background: #e8f3ef;
    border-radius: 14px;
    color: #0f5c46;
    display: inline-flex;
    flex-shrink: 0;
    font-size: 18px;
    height: 42px;
    justify-content: center;
    width: 42px;
}

.season-create-form {
    display: flex;
    flex-direction: column;
    gap: 22px;
    margin-top: 20px;
}

.season-create-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.season-create-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.season-create-field--full {
    grid-column: 1 / -1;
}

.season-create-label {
    color: #1f3530;
    font-size: 13px;
    font-weight: 700;
}

:deep(.season-create-field .el-select),
:deep(.season-create-field .el-date-editor.el-input),
:deep(.season-create-field .el-date-editor.el-input__wrapper),
:deep(.season-create-field .el-input),
:deep(.season-create-field .el-textarea) {
    width: 100%;
}

:deep(.season-create-field .el-input__wrapper),
:deep(.season-create-field .el-textarea__inner),
:deep(.season-create-field .el-select__wrapper) {
    background: #fff;
    border-radius: 5px !important;
    box-shadow: 0 0 0 1px #d7e2de inset;
    min-height: 46px;
}

:deep(.season-create-field .el-textarea__inner) {
    padding-top: 12px;
}

.season-create-actions {
    border-top: 1px solid #e6efec;
    padding-top: 18px;
}

.season-create-submit {
    min-width: 168px;
}

@media (max-width: 900px) {
    .season-create-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 720px) {
    .season-create-card,
    .season-create-shell {
        gap: 14px;
    }

    .season-create-card {
        padding: 16px;
    }

    .season-create-hero,
    .season-create-card-head,
    .season-create-actions {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
