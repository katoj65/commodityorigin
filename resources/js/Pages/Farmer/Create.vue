<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);

const form = useForm({
    first_name: '',
    last_name: '',
    telephone: '',
    email: '',
    district: '',
    sub_county: '',
    coffee_type: '',
    cooperative: '',
    farm_size: '',
    notes: '',
});

const submit = () => {
    form.post(route('farmer.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Register Farmer">
        <div class="space-y-4">
            <section class="rounded-2xl border border-[#E5E7EB] bg-white p-4 sm:p-5">
                <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="font-display text-[22px] font-bold tracking-tight text-[#111827]">
                            Farmer intake form
                        </h2>
                        <p class="mt-1 text-[13px] text-[#6B7280]">
                            Use clear onboarding data for traceability, lot creation, and future batch records.
                        </p>
                    </div>

                    <div class="rounded-full bg-[#FFF8F0] px-3 py-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#9A5A10]">
                        Marketplace onboarding
                    </div>
                </div>

                <div
                    v-if="flashSuccess"
                    class="mb-5 rounded-xl border border-[#D1FAE5] bg-[#ECFDF5] px-4 py-3 text-[13px] text-[#1B6E4B]"
                >
                    {{ flashSuccess }}
                </div>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="farmer-label">First name</label>
                            <el-input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                class="mt-2"
                                placeholder="Joshua"
                            />
                            <InputError class="mt-2" :message="form.errors.first_name" />
                        </div>

                        <div>
                            <label for="last_name" class="farmer-label">Last name</label>
                            <el-input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                class="mt-2"
                                placeholder="Kato"
                            />
                            <InputError class="mt-2" :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="telephone" class="farmer-label">Telephone</label>
                            <el-input
                                id="telephone"
                                v-model="form.telephone"
                                type="tel"
                                class="mt-2"
                                placeholder="+256752567534"
                            />
                            <InputError class="mt-2" :message="form.errors.telephone" />
                        </div>

                        <div>
                            <label for="email" class="farmer-label">Email</label>
                            <el-input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-2"
                                placeholder="farmer@example.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="district" class="farmer-label">District</label>
                            <el-input
                                id="district"
                                v-model="form.district"
                                type="text"
                                class="mt-2"
                                placeholder="Mbale"
                            />
                            <InputError class="mt-2" :message="form.errors.district" />
                        </div>

                        <div>
                            <label for="sub_county" class="farmer-label">Sub-county</label>
                            <el-input
                                id="sub_county"
                                v-model="form.sub_county"
                                type="text"
                                class="mt-2"
                                placeholder="Buginyanya"
                            />
                            <InputError class="mt-2" :message="form.errors.sub_county" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="coffee_type" class="farmer-label">Coffee type</label>
                            <el-select
                                id="coffee_type"
                                v-model="form.coffee_type"
                                class="mt-2 !w-full"
                                placeholder="Select type"
                            >
                                <el-option label="Arabica" value="arabica" />
                                <el-option label="Robusta" value="robusta" />
                                <el-option label="Mixed" value="mixed" />
                            </el-select>
                            <InputError class="mt-2" :message="form.errors.coffee_type" />
                        </div>

                        <div>
                            <label for="cooperative" class="farmer-label">Cooperative</label>
                            <el-input
                                id="cooperative"
                                v-model="form.cooperative"
                                type="text"
                                class="mt-2"
                                placeholder="Sipi Farmers Coop"
                            />
                            <InputError class="mt-2" :message="form.errors.cooperative" />
                        </div>

                        <div>
                            <label for="farm_size" class="farmer-label">Farm size</label>
                            <el-input
                                id="farm_size"
                                v-model="form.farm_size"
                                type="text"
                                class="mt-2"
                                placeholder="2.4 acres"
                            />
                            <InputError class="mt-2" :message="form.errors.farm_size" />
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="farmer-label">Notes</label>
                        <el-input
                            id="notes"
                            v-model="form.notes"
                            type="textarea"
                            :rows="5"
                            resize="vertical"
                            class="mt-2"
                            placeholder="Add any origin, traceability, or onboarding notes..."
                        />
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-[#E5E7EB] pt-4">
                        <div class="w-full sm:w-auto sm:min-w-[220px]">
                            <SubmitButton :loading="form.processing" :disabled="form.processing">
                                Save farmer intake
                            </SubmitButton>
                        </div>

                        <div class="font-mono text-[10px] uppercase tracking-[0.12em] text-[#9CA3AF]">
                            Traceability-ready farmer profile
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.farmer-label {
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #6b7280;
}
</style>
