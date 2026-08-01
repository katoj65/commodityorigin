<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Location, User, OfficeBuilding, Crop, MapLocation, InfoFilled } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    farmer: { type: Object, default: null },
    farmerOptions: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
});

const hasLockedFarmer = computed(() => !!props.farmer);

const farmerName = computed(() =>
    props.farmer ? [props.farmer.first_name, props.farmer.last_name].filter(Boolean).join(' ') || 'Selected Farmer' : '',
);

const farmerLocation = computed(() =>
    props.farmer ? [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', ') || 'Location pending' : '',
);

const farmerMeta = computed(() => props.farmer ? [
    { label: 'Coffee Type', value: props.farmer.coffee_type || 'Not set',              icon: 'crop'   },
    { label: 'Cooperative', value: props.farmer.cooperative || 'Independent producer', icon: 'office' },
    { label: 'Origin',      value: farmerLocation.value,                               icon: 'map'    },
] : []);

const form = useForm({
    farmer_id: props.farmer?.id ?? '',
    name:      '',
    location:  props.farmer ? [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', ') : '',
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

        <div class="af-page">

            <!-- ── Page Header ──────────────────────────────────────────── -->
            <div class="af-header">
                <div class="af-header__inner">
                    <div>
                        <div class="af-header__kicker">Farm Registration</div>
                        <h1 class="af-header__title">Add Farm</h1>
                        <p class="af-header__sub">Register and manage farm information for traceable coffee production.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <Link :href="route('farm.index')" class="af-action-btn">View Farms</Link>
                        <Link v-if="hasLockedFarmer" :href="route('farmer.show', farmer.id)" class="af-action-btn af-action-btn--primary">
                            ← Back to {{ farmerName }}
                        </Link>
                        <Link v-else :href="route('farmer.index')" class="af-action-btn af-action-btn--primary">
                            ← Back to Farmers
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ── Body ────────────────────────────────────────────────── -->
            <div class="af-body">
                <div class="af-layout">

                    <!-- ── Main form ────────────────────────────────────── -->
                    <div class="af-main">
                        <form @submit.prevent="submit">

                            <!-- Section: Farm Identity -->
                            <div class="af-section">
                                <div class="af-section__head">
                                    <span class="af-section__title">Farm Identity</span>
                                </div>
                                <div class="af-section__body">
                                    <div class="row g-3">
                                        <div v-if="!hasLockedFarmer" class="col-12">
                                            <label class="af-label">Farmer <span class="af-req">*</span></label>
                                            <el-select v-model="form.farmer_id" placeholder="Select the farmer this farm belongs to" filterable class="af-input w-100">
                                                <el-option v-for="option in farmerOptions" :key="option.id" :label="option.name" :value="option.id" />
                                            </el-select>
                                            <InputError class="af-err" :message="form.errors.farmer_id" />
                                        </div>
                                        <div class="col-12">
                                            <label class="af-label">Farm Name <span class="af-req">*</span></label>
                                            <el-input v-model="form.name" placeholder="e.g. Elgon Heights Farm" class="af-input" />
                                            <InputError class="af-err" :message="form.errors.name" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Location & Geography -->
                            <div class="af-section">
                                <div class="af-section__head">
                                    <span class="af-section__title">Location &amp; Geography</span>
                                </div>
                                <div class="af-section__body">
                                    <div class="row g-3">
                                        <div class="col-12 col-sm-6">
                                            <label class="af-label">Location</label>
                                            <el-input v-model="form.location" placeholder="Village, parish, district" :prefix-icon="MapLocation" class="af-input" />
                                            <InputError class="af-err" :message="form.errors.location" />
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="af-label">Altitude</label>
                                            <el-input v-model="form.altitude" placeholder="e.g. 1,850 masl" class="af-input" />
                                            <InputError class="af-err" :message="form.errors.altitude" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Production Details -->
                            <div class="af-section">
                                <div class="af-section__head">
                                    <span class="af-section__title">Production Details</span>
                                </div>
                                <div class="af-section__body">
                                    <div class="row g-3">
                                        <div class="col-12 col-sm-6">
                                            <label class="af-label">Farm Size</label>
                                            <el-input v-model="form.size" placeholder="e.g. 12 hectares" class="af-input" />
                                            <InputError class="af-err" :message="form.errors.size" />
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="af-label">Variety <span class="af-req">*</span></label>
                                            <el-select v-model="form.variety" placeholder="Select crop variety" clearable class="af-input w-100">
                                                <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                                            </el-select>
                                            <InputError class="af-err" :message="form.errors.variety" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Notes -->
                            <div class="af-section af-section--last">
                                <div class="af-section__head">
                                    <span class="af-section__title">Notes &amp; Context</span>
                                    <span class="af-section__opt">Optional</span>
                                </div>
                                <div class="af-section__body">
                                    <el-input
                                        v-model="form.notes"
                                        type="textarea"
                                        :rows="4"
                                        placeholder="Add farm notes, access details, or agronomy context."
                                        class="af-input"
                                    />
                                    <InputError class="af-err" :message="form.errors.notes" />
                                </div>
                            </div>

                            <!-- Action bar -->
                            <div class="af-action-bar">
                                <Link :href="hasLockedFarmer ? route('farmer.show', farmer.id) : route('farm.index')" class="af-action-btn">Cancel</Link>
                                <SubmitButton :loading="form.processing" :full-width="false">
                                    Save Farm
                                </SubmitButton>
                            </div>

                        </form>
                    </div>

                    <!-- ── Sidebar ──────────────────────────────────────── -->
                    <aside class="af-sidebar">

                        <!-- Linked farmer -->
                        <div v-if="hasLockedFarmer" class="af-sidebar-block">
                            <div class="af-sidebar-block__head">Linked Farmer</div>
                            <div class="af-sidebar-block__body">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="af-farmer-avatar">
                                        <el-icon :size="16"><User /></el-icon>
                                    </div>
                                    <div>
                                        <div class="af-farmer-name">{{ farmerName }}</div>
                                        <div class="af-farmer-loc">
                                            <el-icon style="font-size:11px;"><Location /></el-icon>
                                            {{ farmerLocation }}
                                        </div>
                                    </div>
                                </div>
                                <div class="af-meta-list">
                                    <div v-for="item in farmerMeta" :key="item.label" class="af-meta-row">
                                        <span class="af-meta-label">{{ item.label }}</span>
                                        <span class="af-meta-value">{{ item.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Intake guide -->
                        <div class="af-sidebar-block">
                            <div class="af-sidebar-block__head">
                                <el-icon style="font-size:13px; color:#004532;"><InfoFilled /></el-icon>
                                Farm Intake Guide
                            </div>
                            <div class="af-sidebar-block__body">
                                <ul class="af-guide-list">
                                    <li>Use the exact farm name recognised by the farmer or cooperative.</li>
                                    <li>Select the registered crop variety to keep farm records standardised.</li>
                                    <li>Include altitude and notes to help future lot registration.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Required fields -->
                        <div class="af-sidebar-block">
                            <div class="af-sidebar-block__head">Required Fields</div>
                            <div class="af-sidebar-block__body">
                                <div class="af-req-list">
                                    <div v-if="!hasLockedFarmer" class="af-req-row" :class="form.farmer_id ? 'af-req-row--done' : ''">
                                        <span class="af-req-dot"></span> Farmer
                                    </div>
                                    <div class="af-req-row" :class="form.name ? 'af-req-row--done' : ''">
                                        <span class="af-req-dot"></span> Farm Name
                                    </div>
                                    <div class="af-req-row" :class="form.variety ? 'af-req-row--done' : ''">
                                        <span class="af-req-dot"></span> Crop Variety
                                    </div>
                                </div>
                            </div>
                        </div>

                    </aside>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.af-page {
    --green:          #004532;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --border:         #d1d5db;
    --border-light:   #e5e7eb;
    --surface-low:    #f8fafc;
    --danger:         #dc2626;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.af-header { background: #fff; border-bottom: 1px solid var(--border-light); padding: 14px 0; }
.af-header__inner {
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.af-header__kicker { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--green); margin-bottom: 2px; }
.af-header__title  { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; color: var(--on-surface); margin: 0 0 2px; line-height: 1.2; }
.af-header__sub    { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0; }

/* Header buttons */
.af-action-btn { display: inline-flex; align-items: center; gap: 6px; font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); text-decoration: none; padding: 7px 14px; border-radius: 6px; border: 1px solid var(--border); background: #fff; white-space: nowrap; }
.af-action-btn:hover { background: var(--surface-low); color: var(--on-surface); }
.af-action-btn--primary { background: var(--green); border-color: var(--green); color: #fff; }
.af-action-btn--primary:hover { background: #065f46; color: #fff; }

/* ── Body ──────────────────────────────────────────────────────────────────── */
.af-body { padding: 1.75rem 1.5rem 3rem; }
.af-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 280px;
    gap: 1.5rem;
    align-items: start;
    max-width: 1100px;
}

/* ── Sections ──────────────────────────────────────────────────────────────── */
.af-section { border: 1px solid var(--border-light); border-radius: 8px; margin-bottom: 1rem; overflow: hidden; }
.af-section--last { margin-bottom: 0; }
.af-section__head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--surface-low);
    border-bottom: 1px solid var(--border-light);
}
.af-section__title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface); }
.af-section__opt   { font-size: 0.6875rem; color: var(--on-surface-var); font-weight: 400; margin-left: auto; }
.af-section__body  { padding: 1.125rem 1rem; }

/* ── Labels & inputs ───────────────────────────────────────────────────────── */
.af-label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--on-surface); margin-bottom: 6px; }
.af-req   { color: var(--danger); }
.af-err   { font-size: 0.75rem; color: var(--danger); margin-top: 4px; display: block; }
.af-input { width: 100%; }

:deep(.af-input .el-input__wrapper),
:deep(.af-input.el-select .el-select__wrapper),
:deep(.af-input .el-textarea__inner) {
    border-radius: 6px !important;
    border: 1.5px solid var(--border) !important;
    box-shadow: none !important;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem;
    background: #fff;
    transition: border-color 0.15s;
}
:deep(.af-input .el-input__wrapper:focus-within),
:deep(.af-input.el-select .el-select__wrapper:focus-within),
:deep(.af-input .el-textarea__inner:focus) {
    border-color: var(--green) !important;
    box-shadow: none !important;
    outline: none;
}
:deep(.af-input--readonly .el-input__inner) {
    color: var(--on-surface-var);
    cursor: default;
}

/* ── Action bar ────────────────────────────────────────────────────────────── */
.af-action-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0 0;
    margin-top: 1rem;
    border-top: 1px solid var(--border-light);
}

/* ── Sidebar ───────────────────────────────────────────────────────────────── */
.af-sidebar { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

.af-sidebar-block { border: 1px solid var(--border-light); border-radius: 8px; overflow: hidden; }
.af-sidebar-block__head {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 9px 14px;
    background: var(--surface-low);
    border-bottom: 1px solid var(--border-light);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface);
}
.af-sidebar-block__body { padding: 1rem; }

/* Farmer card */
.af-farmer-avatar { width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--border-light); background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.af-farmer-name   { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); line-height: 1.2; margin-bottom: 2px; }
.af-farmer-loc    { display: flex; align-items: center; gap: 4px; font-size: 0.75rem; color: var(--on-surface-var); }

.af-meta-list { display: flex; flex-direction: column; gap: 0; border: 1px solid var(--border-light); border-radius: 6px; overflow: hidden; }
.af-meta-row  { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 7px 10px; border-bottom: 1px solid var(--border-light); font-size: 0.8125rem; }
.af-meta-row:last-child { border-bottom: none; }
.af-meta-label { color: var(--on-surface-var); font-size: 0.75rem; flex-shrink: 0; }
.af-meta-value { color: var(--on-surface); font-weight: 600; text-align: right; word-break: break-word; }

/* Guide */
.af-guide-list { margin: 0; padding-left: 1.1rem; display: flex; flex-direction: column; gap: 6px; }
.af-guide-list li { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.6; }

/* Required field checker */
.af-req-list { display: flex; flex-direction: column; gap: 6px; }
.af-req-row  { display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--on-surface-var); }
.af-req-row--done { color: #166534; font-weight: 600; }
.af-req-dot { width: 8px; height: 8px; border-radius: 50%; border: 1.5px solid var(--border); display: inline-block; flex-shrink: 0; }
.af-req-row--done .af-req-dot { background: #16a34a; border-color: #16a34a; }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 960px) {
    .af-layout { grid-template-columns: 1fr; }
    .af-sidebar { position: static; }
}
@media (max-width: 640px) {
    .af-body { padding: 1rem 1rem 3rem; }
    .af-header__inner { flex-direction: column; align-items: flex-start; gap: 10px; }
}
</style>
