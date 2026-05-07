<script setup>
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const page         = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const submitted    = ref(false);

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

const completion = computed(() => {
    const required = [form.first_name, form.last_name, form.telephone, form.district, form.coffee_type];
    const optional = [form.email, form.sub_county, form.cooperative, form.farm_size, form.notes];
    const reqFilled = required.filter(v => v.trim()).length;
    const optFilled = optional.filter(v => v.trim()).length;
    return Math.round((reqFilled / required.length) * 70 + (optFilled / optional.length) * 30);
});

const submit = () => {
    form.post(route('farmer.store'), {
        preserveScroll: true,
        onSuccess: () => { submitted.value = true; },
    });
};
</script>

<template>
    <AppLayout title="Register Farmer" full-width flush :show-banner="false">

        <div class="fc-page">

            <!-- ── Page top accent ────────────────────────────────────── -->
            <div class="fc-top-bar">
                <div class="fc-top-bar__inner">
                    <div>
                        <div class="fc-top-bar__kicker">Bean Origin · Farmer Onboarding</div>
                        <div class="fc-top-bar__title">Register a new farmer profile</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="fc-badge">✓ Verified Producer</span>
                        <span class="fc-badge">✓ Traceable Farming</span>
                        <span class="fc-badge">✓ Export Ready</span>
                    </div>
                </div>
            </div>

            <!-- ── Centered form area ─────────────────────────────────── -->
            <div class="fc-body">

                <!-- Success state -->
                <div v-if="submitted || flashSuccess" class="fc-card fc-success-card">
                    <div class="fc-success-circle">✓</div>
                    <h2 class="fc-success-title">Farmer Registered!</h2>
                    <p class="fc-success-sub">
                        <strong>{{ form.first_name }} {{ form.last_name }}</strong> has been added to the Bean Origin traceability network.
                    </p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <a :href="route('farmer.index')" class="btn fc-btn-primary">View Farmer Directory</a>
                        <a :href="route('farmer.index')" class="btn fc-btn-outline">Register Another</a>
                    </div>
                </div>

                <template v-else>
                <div class="fc-card">

                    <!-- Card header -->
                    <div class="fc-card-head">
                        <div>
                            <h1 class="fc-card-title">Register Farmer</h1>
                            <p class="fc-card-sub">Create a verified farmer profile for traceable coffee production and market access.</p>
                        </div>
                    </div>

                    <!-- Flash message -->
                    <div v-if="flashSuccess" class="fc-flash">
                        <span class="fc-flash__dot"></span>{{ flashSuccess }}
                    </div>

                    <!-- Completion bar -->
                    <div class="fc-completion">
                        <div class="fc-completion__label">
                            <span>Profile Completion</span>
                            <strong :class="completion >= 70 ? 'fc-green' : 'fc-amber'">{{ completion }}%</strong>
                        </div>
                        <div class="fc-completion__track">
                            <div class="fc-completion__fill"
                                :style="{ width: completion + '%', background: completion >= 70 ? '#004532' : '#f59e0b' }">
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="fc-form">

                        <!-- ── Section 1: Personal Information ────────── -->
                        <div class="fc-section">
                            <div class="fc-section__head">
                                <span class="fc-section__title">Personal Information</span>
                                <span class="fc-section__req">* Required</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label for="first_name" class="fc-label">First Name <span class="fc-req">*</span></label>
                                    <el-input id="first_name" v-model="form.first_name" type="text" placeholder="e.g. Joshua" class="fc-input" />
                                    <InputError class="fc-err" :message="form.errors.first_name" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="last_name" class="fc-label">Last Name <span class="fc-req">*</span></label>
                                    <el-input id="last_name" v-model="form.last_name" type="text" placeholder="e.g. Kato" class="fc-input" />
                                    <InputError class="fc-err" :message="form.errors.last_name" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="telephone" class="fc-label">Telephone <span class="fc-req">*</span></label>
                                    <el-input id="telephone" v-model="form.telephone" type="tel" placeholder="+256 752 567 534" class="fc-input" />
                                    <InputError class="fc-err" :message="form.errors.telephone" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="email" class="fc-label">Email <span class="fc-opt">(optional)</span></label>
                                    <el-input id="email" v-model="form.email" type="email" placeholder="farmer@example.com" class="fc-input" />
                                    <InputError class="fc-err" :message="form.errors.email" />
                                </div>
                            </div>
                        </div>

                        <!-- ── Section 2: Farm Location ───────────────── -->
                        <div class="fc-section">
                            <div class="fc-section__head">
                                <span class="fc-section__title">Farm Location</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label for="district" class="fc-label">District <span class="fc-req">*</span></label>
                                    <el-input id="district" v-model="form.district" type="text" placeholder="e.g. Mbale" class="fc-input" />
                                    <InputError class="fc-err" :message="form.errors.district" />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="sub_county" class="fc-label">Sub-County <span class="fc-opt">(optional)</span></label>
                                    <el-input id="sub_county" v-model="form.sub_county" type="text" placeholder="e.g. Buginyanya" class="fc-input" />
                                    <InputError class="fc-err" :message="form.errors.sub_county" />
                                </div>
                            </div>
                        </div>

                        <!-- ── Section 3: Production Information ──────── -->
                        <div class="fc-section">
                            <div class="fc-section__head">
                                <span class="fc-section__title">Production Information</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-4">
                                    <label for="coffee_type" class="fc-label">Coffee Type <span class="fc-req">*</span></label>
                                    <el-select id="coffee_type" v-model="form.coffee_type" placeholder="Select type" class="fc-input w-100">
                                        <el-option label="Arabica" value="arabica" />
                                        <el-option label="Robusta" value="robusta" />
                                        <el-option label="Mixed"   value="mixed" />
                                    </el-select>
                                    <InputError class="fc-err" :message="form.errors.coffee_type" />
                                </div>
                                <div class="col-12 col-sm-4">
                                    <label for="cooperative" class="fc-label">Cooperative <span class="fc-opt">(optional)</span></label>
                                    <el-input id="cooperative" v-model="form.cooperative" type="text" placeholder="e.g. Sipi Farmers Coop" class="fc-input" />
                                    <InputError class="fc-err" :message="form.errors.cooperative" />
                                </div>
                                <div class="col-12 col-sm-4">
                                    <label for="farm_size" class="fc-label">Farm Size <span class="fc-opt">(optional)</span></label>
                                    <el-input id="farm_size" v-model="form.farm_size" type="text" placeholder="e.g. 2.4 acres" class="fc-input" />
                                    <InputError class="fc-err" :message="form.errors.farm_size" />
                                </div>
                            </div>
                        </div>

                        <!-- ── Section 4: Notes ───────────────────────── -->
                        <div class="fc-section fc-section--last">
                            <div class="fc-section__head">
                                <span class="fc-section__title">Notes &amp; Context</span>
                                <span class="fc-opt ms-2">(optional)</span>
                            </div>
                            <el-input
                                id="notes"
                                v-model="form.notes"
                                type="textarea"
                                :rows="4"
                                resize="vertical"
                                placeholder="Add any origin story, traceability context, farming practices, or onboarding notes..."
                                class="fc-input"
                            />
                            <InputError class="fc-err" :message="form.errors.notes" />
                        </div>

                        <!-- ── Action bar ─────────────────────────────── -->
                        <div class="fc-action-bar">
                            <a :href="route('farmer.index')" class="btn fc-btn-ghost">Cancel</a>
                            <div class="ms-auto d-flex align-items-center gap-3 flex-wrap">
                                <span class="fc-action-note">Fields marked <span class="fc-req">*</span> are required</span>
                                <SubmitButton :loading="form.processing" :disabled="form.processing">
                                    Save Farmer Profile
                                </SubmitButton>
                            </div>
                        </div>

                    </form>
                </div>
                </template>

            </div>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.fc-page {
    --green:          #004532;
    --green-grad:     #065f46;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-white:  #ffffff;
    --surface-low:    #f8fafc;
    --surface-high:   #e5e7eb;
    --danger:         #dc2626;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Top accent bar ────────────────────────────────────────────────────────── */
.fc-top-bar { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); padding: 14px 0; }
.fc-top-bar__inner {
    padding: 0 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.fc-top-bar__kicker { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--green); margin-bottom: 2px; }
.fc-top-bar__title  { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.fc-badge { display: inline-flex; align-items: center; background: rgba(0,69,50,0.07); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; white-space: nowrap; }

/* ── Body ──────────────────────────────────────────────────────────────────── */
.fc-body { padding: 2rem 1.25rem 4rem; display: flex; flex-direction: column; align-items: center; }

/* ── Card ──────────────────────────────────────────────────────────────────── */
.fc-card {
    width: 100%;
    max-width: 860px;
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 16px;
    box-shadow: none;
    overflow: hidden;
}

/* ── Card header ───────────────────────────────────────────────────────────── */
.fc-card-head { display: flex; align-items: flex-start; gap: 16px; padding: 1.75rem 1.75rem 1.25rem; border-bottom: 1px solid var(--surface-high); background: linear-gradient(135deg, #f0fdf4, var(--surface-white)); }
.fc-card-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -0.025em; color: var(--on-surface); margin: 0 0 4px; }
.fc-card-sub   { font-size: 0.875rem; color: var(--on-surface-var); line-height: 1.5; margin: 0; max-width: 480px; }

/* ── Flash ─────────────────────────────────────────────────────────────────── */
.fc-flash { display: flex; align-items: center; gap: 10px; background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 12px 1.75rem; font-size: 0.875rem; font-weight: 600; color: #166534; }
.fc-flash__dot { width: 8px; height: 8px; border-radius: 50%; background: #16a34a; flex-shrink: 0; }

/* ── Completion bar ────────────────────────────────────────────────────────── */
.fc-completion { padding: 1rem 1.75rem; border-bottom: 1px solid var(--surface-high); background: var(--surface-low); }
.fc-completion__label { display: flex; align-items: center; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 6px; color: var(--on-surface-var); }
.fc-completion__label strong { font-size: 0.875rem; }
.fc-completion__track { height: 5px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.fc-completion__fill  { height: 100%; border-radius: 999px; transition: width 0.5s ease; }
.fc-green { color: var(--green); }
.fc-amber { color: #92400e; }

/* ── Form ──────────────────────────────────────────────────────────────────── */
.fc-form { padding: 0; }

/* ── Sections ──────────────────────────────────────────────────────────────── */
.fc-section { padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--surface-high); }
.fc-section--last { border-bottom: none; }
.fc-section__head { display: flex; align-items: center; gap: 8px; margin-bottom: 1.125rem; }
.fc-section__title { font-size: 0.875rem; font-weight: 800; color: var(--on-surface); text-transform: uppercase; letter-spacing: 0.04em; }
.fc-section__req   { font-size: 0.6875rem; color: var(--on-surface-var); margin-left: auto; }

/* ── Labels ────────────────────────────────────────────────────────────────── */
.fc-label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--on-surface); margin-bottom: 6px; }
.fc-req   { color: var(--danger); }
.fc-opt   { color: var(--on-surface-var); font-weight: 400; font-size: 0.6875rem; }
.fc-err   { font-size: 0.75rem; color: var(--danger); margin-top: 4px; display: block; }

/* ── Element Plus overrides ────────────────────────────────────────────────── */
:deep(.fc-input .el-input__wrapper),
:deep(.fc-input.el-select .el-select__wrapper),
:deep(.fc-input .el-textarea__inner) {
    border-radius: 8px !important;
    border: 1.5px solid #d1d5db !important;
    box-shadow: none !important;
    font-family: 'Manrope', system-ui, sans-serif !important;
    font-size: 0.9375rem !important;
    transition: border-color 0.15s, box-shadow 0.15s;
}
:deep(.fc-input .el-input__wrapper:focus-within),
:deep(.fc-input.el-select .el-select__wrapper:focus-within),
:deep(.fc-input .el-textarea__inner:focus) {
    border-color: var(--green) !important;
    box-shadow: 0 0 0 3px rgba(0,69,50,0.1) !important;
}
:deep(.fc-input .el-input__placeholder),
:deep(.fc-input .el-textarea__inner::placeholder) {
    color: #c0c8d0 !important;
}

/* ── Action bar ────────────────────────────────────────────────────────────── */
.fc-action-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1.125rem 1.75rem;
    border-top: 1px solid var(--surface-high);
    background: var(--surface-low);
}
.fc-action-note { font-size: 0.75rem; color: var(--on-surface-var); white-space: nowrap; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.fc-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 8px; font-size: 0.9375rem; font-weight: 700; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
.fc-btn-primary:hover { background: var(--green-grad, #065f46); color: #fff; }
.fc-btn-outline { background: #fff; border: 1px solid var(--surface-high); color: var(--on-surface); border-radius: 8px; font-size: 0.9375rem; font-weight: 600; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
.fc-btn-outline:hover { background: var(--surface-low); }
.fc-btn-ghost   { background: transparent; border: 1px solid var(--surface-high); color: var(--on-surface-var); border-radius: 8px; font-size: 0.9375rem; font-weight: 600; padding: 9px 22px; display: inline-flex; align-items: center; text-decoration: none; }
.fc-btn-ghost:hover { background: var(--surface-high); color: var(--on-surface); }

/* ── Success card ──────────────────────────────────────────────────────────── */
.fc-success-card { text-align: center; padding: 3.5rem 2rem; }
.fc-success-circle { width: 72px; height: 72px; border-radius: 50%; background: var(--green); color: #fff; font-size: 2rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; }
.fc-success-title { font-size: 1.5rem; font-weight: 800; color: var(--on-surface); margin-bottom: 10px; }
.fc-success-sub   { font-size: 0.9375rem; color: var(--on-surface-var); line-height: 1.6; margin-bottom: 1.75rem; max-width: 400px; margin-left: auto; margin-right: auto; }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .fc-body { padding: 1rem 0.75rem 3rem; }
    .fc-card-head, .fc-section, .fc-action-bar, .fc-completion { padding-left: 1.125rem; padding-right: 1.125rem; }
    .fc-top-bar__inner { flex-direction: column; align-items: flex-start; gap: 8px; }
    .fc-action-bar { flex-direction: column; align-items: stretch; }
    .fc-action-bar .ms-auto { margin-left: 0 !important; }
}
</style>
