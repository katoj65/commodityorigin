<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Location, User, OfficeBuilding, Crop, MapLocation, Plus, InfoFilled } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    farmer: {
        type: Object,
        required: true,
    },
    varietyOptions: {
        type: Array,
        default: () => [],
    },
});

const farmerName = computed(() =>
    [props.farmer.first_name, props.farmer.last_name].filter(Boolean).join(' ') || 'Selected Farmer',
);

const farmerLocation = computed(() =>
    [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', ') || 'Location pending',
);

const farmerMeta = computed(() => [
    { label: 'Coffee Type',  value: props.farmer.coffee_type || 'Not set',                icon: 'crop' },
    { label: 'Cooperative',  value: props.farmer.cooperative || 'Independent producer',   icon: 'office' },
    { label: 'Origin',       value: farmerLocation.value,                                  icon: 'map' },
]);

const form = useForm({
    farmer_id: props.farmer.id,
    name:      '',
    location:  [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', '),
    size:      '',
    altitude:  '',
    variety:   '',
    notes:     '',
});

const submit = () => form.post(route('farm.store'));
</script>

<template>
    <AppLayout title="Add Farm" full-width flush :show-banner="false">
        <Head title="Add Farm" />

        <div class="af-root">

            <!-- ── Hero ──────────────────────────────────────────────────── -->
            <section class="af-hero">
                <div class="af-hero__inner">
                    <div class="af-hero__left">
                        <h1 class="af-hero__title">Add Farm</h1>
                        <p class="af-hero__sub">
                            Register a new farm location for <strong class="af-hero__farmer">{{ farmerName }}</strong> and capture its traceability details.
                        </p>
                    </div>
                    <Link :href="route('farmer.show', farmer.id)" class="af-back-btn">
                        Back to {{ farmerName }}
                    </Link>
                </div>
            </section>

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div class="af-body">
                <div class="af-grid">

                    <!-- ── Form card ──────────────────────────────────────── -->
                    <div class="af-card">
                        <div class="af-card__head">
                           
                            <div>
                                <div class="af-card__title">Farm Details</div>
                                <div class="af-card__sub">Enter the farm's location, size, variety, and agronomic context.</div>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="af-form-grid">

                                <!-- Farmer (readonly) -->
                                <div class="af-field af-field--full">
                                    <label class="af-label">Farmer</label>
                                    <el-input :model-value="farmerName" readonly class="af-input" />
                                </div>

                                <!-- Farm name -->
                                <div class="af-field">
                                    <label class="af-label">Farm Name <span class="af-required">*</span></label>
                                    <el-input v-model="form.name" placeholder="e.g. Elgon Heights Farm" class="af-input" />
                                    <InputError class="af-error" :message="form.errors.name" />
                                </div>

                                <!-- Location -->
                                <div class="af-field">
                                    <label class="af-label">Location</label>
                                    <el-input v-model="form.location" placeholder="Village, parish, district" :prefix-icon="MapLocation" class="af-input" />
                                    <InputError class="af-error" :message="form.errors.location" />
                                </div>

                                <!-- Farm size -->
                                <div class="af-field">
                                    <label class="af-label">Farm Size</label>
                                    <el-input v-model="form.size" placeholder="e.g. 12 hectares" class="af-input" />
                                    <InputError class="af-error" :message="form.errors.size" />
                                </div>

                                <!-- Altitude -->
                                <div class="af-field">
                                    <label class="af-label">Altitude</label>
                                    <el-input v-model="form.altitude" placeholder="e.g. 1,850 masl" class="af-input" />
                                    <InputError class="af-error" :message="form.errors.altitude" />
                                </div>

                                <!-- Variety -->
                                <div class="af-field af-field--full">
                                    <label class="af-label">Variety</label>
                                    <el-select v-model="form.variety" placeholder="Select crop variety" clearable class="af-input">
                                        <el-option
                                            v-for="option in varietyOptions"
                                            :key="option"
                                            :label="option"
                                            :value="option"
                                        />
                                    </el-select>
                                    <InputError class="af-error" :message="form.errors.variety" />
                                </div>

                                <!-- Notes -->
                                <div class="af-field af-field--full">
                                    <label class="af-label">Notes</label>
                                    <el-input
                                        v-model="form.notes"
                                        type="textarea"
                                        :rows="4"
                                        placeholder="Add farm notes, access details, or agronomy context."
                                        class="af-input"
                                    />
                                    <InputError class="af-error" :message="form.errors.notes" />
                                </div>
                            </div>

                            <div class="af-actions">
                                <SubmitButton :loading="form.processing" style="width: auto; min-width: 160px;">
                                    Save Farm
                                </SubmitButton>
                            </div>
                        </form>
                    </div>

                    <!-- ── Sidebar ─────────────────────────────────────────── -->
                    <aside class="af-sidebar">

                        <!-- Farmer summary -->
                        <div class="af-farmer-card">
                            <div class="af-farmer-card__top">
                                <div>
                                    <p class="af-farmer-card__eyebrow">Linked Farmer</p>
                                    <h2 class="af-farmer-card__name">{{ farmerName }}</h2>
                                    <div class="af-farmer-card__location">
                                        <el-icon class="af-farmer-card__loc-icon"><Location /></el-icon>
                                        <span>{{ farmerLocation }}</span>
                                    </div>
                                </div>
                                <div class="af-farmer-avatar">
                                    <el-icon :size="18"><User /></el-icon>
                                </div>
                            </div>

                            <div class="af-meta-list">
                                <div v-for="item in farmerMeta" :key="item.label" class="af-meta-item">
                                    <span class="af-meta-item__icon">
                                        <el-icon>
                                            <Crop v-if="item.icon === 'crop'" />
                                            <OfficeBuilding v-else-if="item.icon === 'office'" />
                                            <MapLocation v-else />
                                        </el-icon>
                                    </span>
                                    <div class="af-meta-item__body">
                                        <span class="af-meta-item__label">{{ item.label }}</span>
                                        <span class="af-meta-item__value">{{ item.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Intake guide -->
                        <div class="af-guide">
                            <div class="af-guide__head">
                                <el-icon class="af-guide__icon"><InfoFilled /></el-icon>
                                <span class="af-guide__title">Farm Intake Guide</span>
                            </div>
                            <ul class="af-guide__list">
                                <li>Use the exact farm name recognized by the farmer or cooperative.</li>
                                <li>Select the registered crop variety so farm records stay standardized.</li>
                                <li>Include altitude and notes that help future lot registration.</li>
                            </ul>
                        </div>

                    </aside>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.af-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #191c1e;
    --on-surface-var:   #74777a;
    --surface-white:    #ffffff;
    --surface-low:      #f2f4f6;
    --surface-high:     #e6e8ea;
    --primary-fixed:    #a6f2d1;
    --on-primary-fixed: #002116;
    --required:         #c0392b;
    --inner-pad:        2rem;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.af-hero {
    background: var(--surface-white);
    border-bottom: 1px solid var(--surface-high);
    padding: 1.5rem 0;
}
.af-hero__inner {
    padding: 0 var(--inner-pad);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.af-hero__title {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    margin: 0 0 0.25rem;
    line-height: 1.2;
}
.af-hero__sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.5;
}
.af-hero__farmer { color: var(--on-surface); font-weight: 700; }

.af-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface);
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 6px;
    border: 1px solid var(--surface-high);
    background: var(--surface-white);
    white-space: nowrap;
    transition: background 0.12s ease;
}
.af-back-btn:hover { background: var(--surface-low); }

/* ── Body ──────────────────────────────────────────────────────────────────── */
.af-body { padding: 2rem var(--inner-pad) 3rem; }

.af-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 300px;
    gap: 1.5rem;
    align-items: start;
}

/* ── Form card ─────────────────────────────────────────────────────────────── */
.af-card {
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    padding: 1.5rem;
}

.af-card__head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--surface-low);
}
.af-card__icon {
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
.af-card__title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--on-surface);
    margin-bottom: 2px;
}
.af-card__sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    line-height: 1.5;
}

/* ── Form grid ─────────────────────────────────────────────────────────────── */
.af-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem;
}
.af-field         { display: flex; flex-direction: column; gap: 6px; }
.af-field--full   { grid-column: 1 / -1; }

.af-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
}
.af-required { color: var(--required); margin-left: 2px; }
.af-error    { font-size: 0.75rem; color: var(--required); margin-top: 4px; }

/* Input overrides */
.af-input { width: 100%; }

:deep(.af-input .el-input__wrapper),
:deep(.af-input .el-select__wrapper),
:deep(.af-input.el-date-editor .el-input__wrapper) {
    background: var(--surface-white);
    border-radius: 6px !important;
    box-shadow: 0 0 0 1px var(--surface-high) inset;
    min-height: 42px;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem;
}
:deep(.af-input .el-input__wrapper.is-focus),
:deep(.af-input .el-select__wrapper.is-focused) {
    box-shadow: 0 0 0 1px var(--primary) inset,
                0 0 0 3px rgba(0,69,50,0.08);
}
:deep(.af-input .el-textarea__inner) {
    background: var(--surface-white);
    border-radius: 6px !important;
    box-shadow: 0 0 0 1px var(--surface-high) inset;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem;
    padding: 10px 14px;
    resize: vertical;
}
:deep(.af-input .el-textarea__inner:focus) {
    box-shadow: 0 0 0 1px var(--primary) inset,
                0 0 0 3px rgba(0,69,50,0.08);
    outline: none;
}
:deep(.af-input .el-input__inner[readonly]) {
    color: var(--on-surface-var);
    cursor: default;
}

/* Actions */
.af-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 1.25rem;
    margin-top: 1.5rem;
    border-top: 1px solid var(--surface-low);
}

/* ── Sidebar ───────────────────────────────────────────────────────────────── */
.af-sidebar { display: flex; flex-direction: column; gap: 1rem; }

/* Farmer card */
.af-farmer-card {
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
}
.af-farmer-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem;
    background: var(--surface-low);
    border-bottom: 1px solid var(--surface-high);
}
.af-farmer-card__eyebrow {
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--on-surface-var);
    letter-spacing: 0.04em;
    margin: 0 0 4px;
}
.af-farmer-card__name {
    font-size: 1rem;
    font-weight: 800;
    color: var(--on-surface);
    margin: 0 0 6px;
    line-height: 1.2;
}
.af-farmer-card__location {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
}
.af-farmer-card__loc-icon { font-size: 13px; color: var(--primary); flex-shrink: 0; }

.af-farmer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Meta list */
.af-meta-list {
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}
.af-meta-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.af-meta-item__icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: rgba(0,69,50,0.07);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    margin-top: 1px;
}
.af-meta-item__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.af-meta-item__label {
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--on-surface-var);
}
.af-meta-item__value {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--on-surface);
    line-height: 1.4;
    overflow-wrap: anywhere;
}

/* Intake guide */
.af-guide {
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    padding: 1.25rem;
}
.af-guide__head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 0.875rem;
}
.af-guide__icon { font-size: 15px; color: var(--primary); flex-shrink: 0; }
.af-guide__title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--on-surface);
}
.af-guide__list {
    margin: 0;
    padding-left: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.af-guide__list li {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    line-height: 1.6;
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 960px) {
    .af-grid { grid-template-columns: 1fr; }
    .af-root { --inner-pad: 1.25rem; }
    .af-hero__inner { flex-direction: column; align-items: flex-start; gap: 1rem; }
}
@media (max-width: 640px) {
    .af-root { --inner-pad: 1rem; }
    .af-form-grid { grid-template-columns: 1fr; }
    .af-actions { justify-content: stretch; }
}
</style>
