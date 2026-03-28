<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';

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
            <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:p-6">
                <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
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

                <form class="space-y-5" @submit.prevent="submit">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="farmer-label">First name</label>
                            <input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                class="farmer-input mt-2"
                                placeholder="Joshua"
                            />
                            <InputError class="mt-2" :message="form.errors.first_name" />
                        </div>

                        <div>
                            <label for="last_name" class="farmer-label">Last name</label>
                            <input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                class="farmer-input mt-2"
                                placeholder="Kato"
                            />
                            <InputError class="mt-2" :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="telephone" class="farmer-label">Telephone</label>
                            <input
                                id="telephone"
                                v-model="form.telephone"
                                type="tel"
                                class="farmer-input mt-2"
                                placeholder="+256752567534"
                            />
                            <InputError class="mt-2" :message="form.errors.telephone" />
                        </div>

                        <div>
                            <label for="email" class="farmer-label">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="farmer-input mt-2"
                                placeholder="farmer@example.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="district" class="farmer-label">District</label>
                            <input
                                id="district"
                                v-model="form.district"
                                type="text"
                                class="farmer-input mt-2"
                                placeholder="Mbale"
                            />
                            <InputError class="mt-2" :message="form.errors.district" />
                        </div>

                        <div>
                            <label for="sub_county" class="farmer-label">Sub-county</label>
                            <input
                                id="sub_county"
                                v-model="form.sub_county"
                                type="text"
                                class="farmer-input mt-2"
                                placeholder="Buginyanya"
                            />
                            <InputError class="mt-2" :message="form.errors.sub_county" />
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="coffee_type" class="farmer-label">Coffee type</label>
                            <select
                                id="coffee_type"
                                v-model="form.coffee_type"
                                class="farmer-input mt-2"
                            >
                                <option value="">Select type</option>
                                <option value="arabica">Arabica</option>
                                <option value="robusta">Robusta</option>
                                <option value="mixed">Mixed</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.coffee_type" />
                        </div>

                        <div>
                            <label for="cooperative" class="farmer-label">Cooperative</label>
                            <input
                                id="cooperative"
                                v-model="form.cooperative"
                                type="text"
                                class="farmer-input mt-2"
                                placeholder="Sipi Farmers Coop"
                            />
                            <InputError class="mt-2" :message="form.errors.cooperative" />
                        </div>

                        <div>
                            <label for="farm_size" class="farmer-label">Farm size</label>
                            <input
                                id="farm_size"
                                v-model="form.farm_size"
                                type="text"
                                class="farmer-input mt-2"
                                placeholder="2.4 acres"
                            />
                            <InputError class="mt-2" :message="form.errors.farm_size" />
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="farmer-label">Notes</label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            class="farmer-input farmer-textarea mt-2"
                            placeholder="Add any origin, traceability, or onboarding notes..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-[#E5E7EB] pt-5">
                        <button
                            type="submit"
                            class="farmer-submit"
                            :class="{ 'cursor-not-allowed opacity-60': form.processing }"
                            :disabled="form.processing"
                        >
                            Save farmer intake
                        </button>

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

.farmer-input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 0.875rem;
    background: #ffffff;
    padding: 0.8rem 0.9rem;
    font-size: 0.95rem;
    color: #111827;
    outline: 0;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.farmer-input:focus {
    border-color: rgba(200, 134, 42, 0.45);
    box-shadow: 0 0 0 3px rgba(200, 134, 42, 0.12);
}

.farmer-textarea {
    min-height: 124px;
    resize: vertical;
}

.farmer-submit {
    border: 0;
    border-radius: 9999px;
    background: linear-gradient(135deg, #c8862a, #e09b3a);
    padding: 0.85rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #ffffff;
    box-shadow: 0 16px 36px rgba(200, 134, 42, 0.24);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.farmer-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 18px 40px rgba(200, 134, 42, 0.28);
}
</style>
