<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { MapLocation, InfoFilled } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    farmer: { type: Object, default: null },
    varietyOptions: { type: Array, default: () => [] },
});

const hasLockedFarmer = computed(() => !!props.farmer);

const farmerName = computed(() =>
    props.farmer ? [props.farmer.first_name, props.farmer.last_name].filter(Boolean).join(' ') || 'Selected Farmer' : '',
);

const form = useForm({
    farmer_id: props.farmer?.id ?? '',
    is_self_farmer: true,
    farmer: {
        first_name: '',
        last_name: '',
        telephone: '',
        email: '',
        district: '',
        sub_county: '',
        coffee_type: '',
        cooperative: '',
    },
    name:     '',
    location: props.farmer ? [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', ') : '',
    size:     '',
    altitude: '',
    variety:  '',
    notes:    '',
});

const showFarmerRegistration = computed(() => !hasLockedFarmer.value && !form.is_self_farmer);

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
                        <p class="af-header__sub">Register farm information for traceable coffee production.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <Link :href="route('farm.index')" class="af-action-btn">View Farms</Link>
                    </div>
                </div>
            </div>

            <!-- ── Body ────────────────────────────────────────────────── -->
            <div class="af-body">
                <div class="af-layout">
                <div class="af-main">
                    <form class="af-form" @submit.prevent="submit">

                        <!-- Farm Identity -->
                        <div class="af-group">
                            <div class="af-group__title">Farm Identity</div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="af-label">Farm Name <span class="af-req">*</span></label>
                                    <el-input v-model="form.name" placeholder="e.g. Elgon Heights Farm" class="af-input" :class="{ 'af-input--error': form.errors.name }" />
                                    <InputError class="af-err" :message="form.errors.name" />
                                </div>
                            </div>
                        </div>

                        <!-- Location & Geography -->
                        <div class="af-group">
                            <div class="af-group__title">Location &amp; Geography</div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Location <span class="af-req">*</span></label>
                                    <el-input v-model="form.location" placeholder="Village, parish, district" :prefix-icon="MapLocation" class="af-input" :class="{ 'af-input--error': form.errors.location }" />
                                    <InputError class="af-err" :message="form.errors.location" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Altitude</label>
                                    <el-input v-model="form.altitude" placeholder="e.g. 1,850 masl" class="af-input" />
                                    <InputError class="af-err" :message="form.errors.altitude" />
                                </div>
                            </div>
                        </div>

                        <!-- Production Details -->
                        <div class="af-group">
                            <div class="af-group__title">Production Details</div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Farm Size <span class="af-req">*</span></label>
                                    <el-input v-model="form.size" placeholder="e.g. 12 hectares" class="af-input" :class="{ 'af-input--error': form.errors.size }" />
                                    <InputError class="af-err" :message="form.errors.size" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Variety <span class="af-req">*</span></label>
                                    <el-select v-model="form.variety" placeholder="Select crop variety" clearable class="af-input w-100" :class="{ 'af-input--error': form.errors.variety }">
                                        <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                                    </el-select>
                                    <InputError class="af-err" :message="form.errors.variety" />
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="af-group">
                            <div class="af-group__title">Notes &amp; Context <span class="af-group__opt">Optional</span></div>
                            <el-input
                                v-model="form.notes"
                                type="textarea"
                                :rows="4"
                                placeholder="Add farm notes, access details, or agronomy context."
                                class="af-input"
                            />
                            <InputError class="af-err" :message="form.errors.notes" />
                        </div>

                        <!-- Locked farmer banner -->
                        <div v-if="hasLockedFarmer" class="af-group af-group--last">
                            <div class="af-locked-banner">
                                Registering a farm for <strong>{{ farmerName }}</strong>.
                            </div>
                        </div>

                        <!-- Are you the farmer? (last section) -->
                        <div v-else class="af-group" :class="{ 'af-group--last': !showFarmerRegistration }">
                            <div class="af-switch-row">
                                <div>
                                    <div class="af-switch-label">Are you the farmer for this farm?</div>
                                    <div class="af-switch-hint">
                                        {{ form.is_self_farmer ? 'We’ll use your account details as the farmer.' : 'Register the farmer’s details below.' }}
                                    </div>
                                </div>
                                <el-switch v-model="form.is_self_farmer" />
                            </div>
                        </div>

                        <!-- Farmer Registration (only when registering on someone else's behalf) -->
                        <div v-if="showFarmerRegistration" class="af-group af-group--last">
                            <div class="af-group__title">Farmer Registration</div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">First Name <span class="af-req">*</span></label>
                                    <el-input v-model="form.farmer.first_name" placeholder="e.g. Joshua" class="af-input" :class="{ 'af-input--error': form.errors['farmer.first_name'] }" />
                                    <InputError class="af-err" :message="form.errors['farmer.first_name']" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Last Name <span class="af-req">*</span></label>
                                    <el-input v-model="form.farmer.last_name" placeholder="e.g. Kato" class="af-input" :class="{ 'af-input--error': form.errors['farmer.last_name'] }" />
                                    <InputError class="af-err" :message="form.errors['farmer.last_name']" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Telephone <span class="af-req">*</span></label>
                                    <el-input v-model="form.farmer.telephone" placeholder="+256 752 567 534" class="af-input" :class="{ 'af-input--error': form.errors['farmer.telephone'] }" />
                                    <InputError class="af-err" :message="form.errors['farmer.telephone']" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Email</label>
                                    <el-input v-model="form.farmer.email" type="email" placeholder="farmer@example.com" class="af-input" />
                                    <InputError class="af-err" :message="form.errors['farmer.email']" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">District <span class="af-req">*</span></label>
                                    <el-input v-model="form.farmer.district" placeholder="e.g. Mbale" class="af-input" :class="{ 'af-input--error': form.errors['farmer.district'] }" />
                                    <InputError class="af-err" :message="form.errors['farmer.district']" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Sub-county</label>
                                    <el-input v-model="form.farmer.sub_county" placeholder="e.g. Buginyanya" class="af-input" />
                                    <InputError class="af-err" :message="form.errors['farmer.sub_county']" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Coffee Type <span class="af-req">*</span></label>
                                    <el-input v-model="form.farmer.coffee_type" placeholder="e.g. Arabica" class="af-input" :class="{ 'af-input--error': form.errors['farmer.coffee_type'] }" />
                                    <InputError class="af-err" :message="form.errors['farmer.coffee_type']" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="af-label">Cooperative</label>
                                    <el-input v-model="form.farmer.cooperative" placeholder="e.g. Sipi Farmers Cooperative" class="af-input" />
                                    <InputError class="af-err" :message="form.errors['farmer.cooperative']" />
                                </div>
                            </div>
                        </div>

                        <!-- Action bar -->
                        <div class="af-action-bar">
                            <SubmitButton :loading="form.processing" :full-width="false">
                                Save Farm
                            </SubmitButton>
                        </div>

                    </form>
                </div>

                <!-- ── Sidebar: instructions ────────────────────────────── -->
                <aside class="af-sidebar">
                    <div class="af-guide">
                        <div class="af-guide__title">
                            <el-icon style="font-size:13px; color:#004532;"><InfoFilled /></el-icon>
                            Farm Registration Guide
                        </div>
                        <ul class="af-guide__list">
                            <li>Use the exact farm name recognised by the farmer or cooperative.</li>
                            <li>Select the registered crop variety to keep farm records standardised.</li>
                            <li>Include altitude and notes to help with future lot registration.</li>
                            <li>If you're not the farmer, switch off "Are you the farmer?" and fill in their details so the farm can be linked correctly.</li>
                            <li>If you are the farmer, we'll use your account details automatically — no need to re-enter them.</li>
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
    grid-template-columns: minmax(0, 720px) 280px;
    gap: 1.5rem;
    align-items: start;
    max-width: 1040px;
}
.af-main { min-width: 0; }
.af-form { border: 1px solid var(--border-light); border-radius: 10px; padding: 1.5rem; }

/* ── Sidebar: instructions ────────────────────────────────────────────────── */
.af-sidebar { position: sticky; top: 60px; }
.af-guide { border: 1px solid var(--border-light); border-radius: 8px; overflow: hidden; }
.af-guide__title {
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
.af-guide__list { margin: 0; padding: 1rem 1rem 1rem 1.6rem; display: flex; flex-direction: column; gap: 8px; }
.af-guide__list li { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.55; }

/* ── Locked farmer banner ─────────────────────────────────────────────────── */
.af-locked-banner {
    background: var(--surface-low);
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
}
.af-locked-banner strong { color: var(--on-surface); }

/* ── Are-you-the-farmer switch ────────────────────────────────────────────── */
.af-switch-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.af-switch-label { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.af-switch-hint { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }

/* ── Field groups ──────────────────────────────────────────────────────────
   Flat, plain form layout on purpose — no bordered/boxed "cards" per group,
   just a small caption and a divider between groups. */
.af-group { padding: 1.25rem 0; border-bottom: 1px solid var(--border-light); }
.af-group:first-child { padding-top: 0; }
.af-group--last { border-bottom: none; padding-bottom: 0; }
.af-group__title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); margin-bottom: 1rem; }
.af-group__opt   { font-weight: 400; text-transform: none; letter-spacing: normal; color: var(--on-surface-var); margin-left: 6px; }

/* ── Labels & inputs ───────────────────────────────────────────────────────── */
.af-label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--on-surface); margin-bottom: 6px; }
.af-req   { color: var(--danger); }
.af-err   { font-size: 0.75rem; color: var(--danger); margin-top: 4px; display: block; }
.af-input { width: 100%; }

/* Field borders intentionally inherit the system-wide look from
   resources/css/element-overrides.css (inset box-shadow, no local
   border/radius/color overrides) so they stay uniform with every other
   page instead of drifting into a page-specific style. */
:deep(.af-input--readonly .el-input__inner) {
    color: var(--on-surface-var);
    cursor: default;
}

/* Same red used by the system-wide .el-form-item.is-error rule in
   element-overrides.css, applied here since this form doesn't use
   <el-form-item> wrappers. */
:deep(.af-input--error .el-input__wrapper),
:deep(.af-input--error .el-select__wrapper),
:deep(.af-input--error .el-textarea__inner) {
    box-shadow: 0 0 0 1px var(--el-color-danger) inset !important;
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
