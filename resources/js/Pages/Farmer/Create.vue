<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    CircleCheck, InfoFilled, Location, Message,
    OfficeBuilding, Phone, Postcard, User,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    cooperatives: { type: Array, default: () => [] },
});

const page         = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

/* ── Form — matches the farmers table exactly (farmer_number is
   generated server-side by FarmerService, so it isn't collected here). */
const form = useForm({
    first_name:           '',
    middle_name:          '',
    last_name:            '',
    gender:                '',
    date_of_birth:        '',
    national_id:          '',
    tel:                  '',
    email:                '',
    country:              'Uganda',
    region:                '',
    district:              '',
    county:                '',
    subcounty:             '',
    parish:                '',
    village:               '',
    cooperative_id:        '',
});

const submit = () => {
    form.post(route('farmer.store'), { preserveScroll: true });
};
</script>

<template>
    <DesignPreviewLayout title="Register Farmer">

        <form class="fr-page" @submit.prevent="submit">

            <!-- Flash -->
            <div v-if="flashSuccess" class="fr-flash">
                <el-icon><CircleCheck /></el-icon> {{ flashSuccess }}
            </div>

            <!-- Header ──────────────────────────────────────────────── -->
            <div class="fr-header">
                <div>
                    <h1 class="fr-title">Onboard New Farmer</h1>
                    <p class="fr-subtitle">Enter details for a new agricultural partner. Accuracy in location and cooperative association ensures proper traceability through the supply chain.</p>
                </div>
                <div class="fr-header-actions">
                    <Link :href="route('farmer.index')" class="fr-btn fr-btn--outline">Cancel</Link>
                    <button type="submit" class="fr-btn fr-btn--primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save Farmer Profile' }}
                    </button>
                </div>
            </div>

            <!-- Grid ────────────────────────────────────────────────── -->
            <div class="fr-grid12">

                <!-- Personal Information -->
                <section class="fr-card fr-span-8">
                    <h2 class="fr-card-title"><el-icon><User /></el-icon> Personal Information</h2>
                    <div class="fr-fields">
                        <div class="fr-field">
                            <label>First Name <span class="fr-req">*</span></label>
                            <el-input v-model="form.first_name" placeholder="e.g. Joshua" class="fr-input" />
                            <InputError :message="form.errors.first_name" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Middle Name</label>
                            <el-input v-model="form.middle_name" placeholder="e.g. Wasswa" class="fr-input" />
                            <InputError :message="form.errors.middle_name" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Last Name <span class="fr-req">*</span></label>
                            <el-input v-model="form.last_name" placeholder="e.g. Kato" class="fr-input" />
                            <InputError :message="form.errors.last_name" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Gender</label>
                            <el-select v-model="form.gender" clearable placeholder="Select gender" class="fr-input w-100">
                                <el-option label="Male" value="male" />
                                <el-option label="Female" value="female" />
                                <el-option label="Other" value="other" />
                            </el-select>
                            <InputError :message="form.errors.gender" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Date of Birth</label>
                            <el-date-picker v-model="form.date_of_birth" type="date" value-format="YYYY-MM-DD" placeholder="Select date" class="fr-input w-100" />
                            <InputError :message="form.errors.date_of_birth" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>National ID</label>
                            <el-input v-model="form.national_id" placeholder="e.g. CM12345678AB9C" :prefix-icon="Postcard" class="fr-input" />
                            <InputError :message="form.errors.national_id" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Telephone <span class="fr-req">*</span></label>
                            <el-input v-model="form.tel" type="tel" placeholder="+256 752 567 534" :prefix-icon="Phone" class="fr-input" />
                            <InputError :message="form.errors.tel" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Email Address</label>
                            <el-input v-model="form.email" type="email" placeholder="farmer@example.com" :prefix-icon="Message" class="fr-input" />
                            <InputError :message="form.errors.email" class="fr-error" />
                        </div>
                    </div>
                </section>

                <!-- Context panel -->
                <aside class="fr-card fr-span-4 fr-context">
                    <div class="fr-context__image" />
                    <div class="fr-context__body">
                        <p class="fr-context__quote">"Building relationships at origin is the foundation of our quality control. Ensure all contact details are verified before saving."</p>
                        <div class="fr-context__chip">
                            <el-icon><CircleCheck /></el-icon>
                            <span>Verification Required</span>
                        </div>
                    </div>
                </aside>

                <!-- Location & Association -->
                <section class="fr-card fr-span-6">
                    <h2 class="fr-card-title"><el-icon><Location /></el-icon> Location &amp; Association</h2>
                    <div class="fr-fields">
                        <div class="fr-field">
                            <label>Country</label>
                            <el-input v-model="form.country" placeholder="e.g. Uganda" class="fr-input" />
                            <InputError :message="form.errors.country" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Region</label>
                            <el-input v-model="form.region" placeholder="e.g. Eastern" class="fr-input" />
                            <InputError :message="form.errors.region" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>District <span class="fr-req">*</span></label>
                            <el-input v-model="form.district" placeholder="e.g. Mbale" class="fr-input" />
                            <InputError :message="form.errors.district" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>County</label>
                            <el-input v-model="form.county" placeholder="e.g. Bungokho" class="fr-input" />
                            <InputError :message="form.errors.county" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Sub-county</label>
                            <el-input v-model="form.subcounty" placeholder="e.g. Buginyanya" class="fr-input" />
                            <InputError :message="form.errors.subcounty" class="fr-error" />
                        </div>
                        <div class="fr-field">
                            <label>Parish</label>
                            <el-input v-model="form.parish" placeholder="e.g. Bumwoni" class="fr-input" />
                            <InputError :message="form.errors.parish" class="fr-error" />
                        </div>
                        <div class="fr-field fr-field--full">
                            <label>Village</label>
                            <el-input v-model="form.village" placeholder="e.g. Busamaga" class="fr-input" />
                            <InputError :message="form.errors.village" class="fr-error" />
                        </div>
                    </div>
                </section>

                <!-- Right column: cooperative + registration note -->
                <div class="fr-span-6 fr-stack">

                    <section class="fr-card">
                        <h2 class="fr-card-title"><el-icon><OfficeBuilding /></el-icon> Cooperative</h2>
                        <div class="fr-fields">
                            <div class="fr-field fr-field--full">
                                <label>Cooperative Association</label>
                                <el-select v-model="form.cooperative_id" clearable filterable placeholder="Assign cooperative (optional)" class="fr-input w-100">
                                    <el-option v-for="c in cooperatives" :key="c.id" :label="c.name" :value="c.id" />
                                </el-select>
                                <InputError :message="form.errors.cooperative_id" class="fr-error" />
                            </div>
                        </div>
                    </section>

                    <section class="fr-card fr-note">
                        <h2 class="fr-card-title"><el-icon><InfoFilled /></el-icon> Farmer ID</h2>
                        <p class="fr-note__text">A unique farmer number (e.g. <span class="fr-mono">FMR-2026-A1B2C3</span>) is generated automatically once this profile is saved — no need to enter one here.</p>
                    </section>

                </div>

            </div>

        </form>

    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Ported from the "Onboard New Farmer" reference mockup, mapped onto
   the app's persistent --dp-* tokens (this mockup's own palette, per its
   DESIGN.md, already matches dp-* 1:1). The system font stack is used in
   place of the mockup's Playfair Display / Inter pairing, since the app
   theme already committed to a single reliable sans-serif everywhere
   else (see ProductProfile.vue). ────────────────────────────────────── */
.fr-page {
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
    padding-bottom: 48px;
}

/* ── Flash ───────────────────────────────────────────────────────── */
.fr-flash {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 20px; padding: 12px 16px;
    border-radius: 12px; background: var(--dp-secondary-container);
    color: var(--dp-on-secondary-container);
    font-size: .875rem; font-weight: 600;
}

/* ── Header ──────────────────────────────────────────────────────── */
.fr-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 24px; margin-bottom: 32px; flex-wrap: wrap;
}
.fr-title {
    font-size: 2rem; font-weight: 800; letter-spacing: -.015em;
    line-height: 1.2; color: var(--dp-primary); margin: 0 0 8px !important;
}
.fr-subtitle {
    font-size: .9375rem; color: var(--dp-on-surface-variant);
    max-width: 640px; line-height: 1.6; margin: 0 !important;
}
.fr-header-actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.fr-btn {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; border-radius: 6px; font-size: 13px; font-weight: 600;
    letter-spacing: .01em; cursor: pointer; border: 1px solid transparent;
    text-decoration: none; white-space: nowrap; font-family: var(--dp-font-sans);
    transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
}
.fr-btn--primary {
    padding: 0 16px; background: var(--dp-primary); color: var(--dp-on-primary);
    box-shadow: 0 10px 24px -12px rgba(0,0,0,.45);
}
.fr-btn--primary:hover:not(:disabled) { box-shadow: 0 14px 28px -12px rgba(0,0,0,.5); transform: translateY(-1px); }
.fr-btn--primary:disabled { opacity: .65; cursor: not-allowed; }
.fr-btn--outline {
    padding: 0 16px; background: transparent; color: var(--dp-primary);
    border-color: var(--dp-outline-variant);
}
.fr-btn--outline:hover { background: var(--dp-surface-container-low); }

/* ── Grid ────────────────────────────────────────────────────────── */
.fr-grid12 { display: grid; grid-template-columns: repeat(12, 1fr); gap: 24px; align-items: stretch; }
.fr-span-8 { grid-column: span 8; }
.fr-span-6 { grid-column: span 6; }
.fr-span-4 { grid-column: span 4; }
@media (max-width: 1024px) {
    .fr-span-8, .fr-span-6, .fr-span-4 { grid-column: span 12; }
}
.fr-stack { display: flex; flex-direction: column; gap: 24px; }

/* ── Cards ───────────────────────────────────────────────────────── */
.fr-card {
    background: var(--dp-surface-container-lowest);
    border-radius: 6px;
    box-shadow: var(--dp-card-shadow);
    padding: 32px;
}
.fr-card-title {
    display: flex; align-items: center; gap: 12px;
    font-size: 1.25rem; font-weight: 700; letter-spacing: -.01em;
    color: var(--dp-on-surface); margin: 0 0 28px !important;
}
.fr-card-title :deep(.el-icon) { color: var(--dp-surface-tint); font-size: 1.125rem; }

/* ── Fields ──────────────────────────────────────────────────────── */
.fr-fields { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
@media (max-width: 640px) { .fr-fields { grid-template-columns: 1fr; } }
.fr-field { display: flex; flex-direction: column; gap: 8px; }
.fr-field--full { grid-column: 1 / -1; }
.fr-field label {
    font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface-variant);
}
.fr-req { color: var(--dp-error); }
.fr-error { font-size: .75rem; color: var(--dp-error); margin-top: 2px; }

/* ── Input overrides ─────────────────────────────────────────────── */
.fr-input { width: 100%; }
:deep(.fr-input .el-input__wrapper),
:deep(.fr-input .el-select__wrapper) {
    background: var(--dp-surface-container-low);
    border-radius: 12px !important;
    box-shadow: none !important;
    min-height: 46px;
    padding: 0 16px;
    font-family: var(--dp-font-sans); font-size: .9375rem;
}
:deep(.fr-input .el-input__wrapper.is-focus),
:deep(.fr-input .el-select__wrapper.is-focused) {
    box-shadow: 0 0 0 2px var(--dp-primary-fixed) inset !important;
}
:deep(.fr-input .el-input__inner::placeholder) { color: var(--dp-outline); }

/* ── Context panel ───────────────────────────────────────────────── */
.fr-context { padding: 0; overflow: hidden; display: flex; flex-direction: column; }
.fr-context__image {
    height: 192px; width: 100%; background-size: cover; background-position: center;
    background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBH_TXuZ93g81Wt9am2_qAX7B6p5K98GcxhFNLxZT1JpDV4Oxb4xowvaWR0kMNAIB9yEgoTo6MABUIGrKpedcMfaUC_wZzozfUK1AtQbkTT22pely-E5pbV12GuFxHxaI5BBNQj66EDjUOC_XBMvr96UOT4SDZqigTtg5F08yvbdeJ5DCKcxRNj6XHdEOWwLKbe9duX77UGpf5Uk01FLgxQcY-gBb_RqczkIRN2aimHOef0KORI7Hnq');
}
.fr-context__body { padding: 28px 32px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
.fr-context__quote { font-size: .9375rem; font-style: italic; color: var(--dp-on-surface-variant); line-height: 1.6; margin: 0 0 20px !important; }
.fr-context__chip {
    display: inline-flex; align-items: center; gap: 8px; width: fit-content;
    padding: 8px 16px; border-radius: 999px; background: var(--dp-surface-container-low);
    font-size: .6875rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    color: var(--dp-on-surface-variant);
}
.fr-context__chip :deep(.el-icon) { color: var(--dp-secondary); }

/* ── Farmer ID note ──────────────────────────────────────────────── */
.fr-note { flex: 1; display: flex; flex-direction: column; }
.fr-note__text { font-size: .875rem; color: var(--dp-on-surface-variant); line-height: 1.6; margin: 0 !important; }
.fr-mono { font-family: var(--dp-font-mono); color: var(--dp-primary); font-weight: 700; }
</style>
