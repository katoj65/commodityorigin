<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);

const form = useForm({
    name: '',
    code: '',
    registration_number: '',
    contact_person: '',
    telephone: '',
    email: '',
    district: '',
    sub_county: '',
    address: '',
    notes: '',
});

const submit = () => {
    form.post(route('cooperative.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Register Cooperative">
        <div class="space-y-4">
            <section class="rounded-2xl border border-[#E5E7EB] bg-white p-4 sm:p-5">
                <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="font-display text-[22px] font-bold tracking-tight text-[#111827]">
                            Cooperative registration
                        </h2>
                        <p class="mt-1 text-[13px] text-[#6B7280]">
                            Register a cooperative for sourcing, grower onboarding, and exchange-side traceability.
                        </p>
                    </div>

                    <div class="rounded-full bg-[#F3FAF6] px-3 py-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#166534]">
                        Producer organization
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
                            <label for="name" class="cooperative-label">Cooperative name</label>
                            <el-input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-2"
                                placeholder="Mount Elgon Producers Union"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <label for="code" class="cooperative-label">Code</label>
                            <el-input
                                id="code"
                                v-model="form.code"
                                type="text"
                                class="mt-2"
                                placeholder="MEPU"
                            />
                            <InputError class="mt-2" :message="form.errors.code" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="registration_number" class="cooperative-label">Registration number</label>
                            <el-input
                                id="registration_number"
                                v-model="form.registration_number"
                                type="text"
                                class="mt-2"
                                placeholder="REG-2026-001"
                            />
                            <InputError class="mt-2" :message="form.errors.registration_number" />
                        </div>

                        <div>
                            <label for="contact_person" class="cooperative-label">Contact person</label>
                            <el-input
                                id="contact_person"
                                v-model="form.contact_person"
                                type="text"
                                class="mt-2"
                                placeholder="Jane Auma"
                            />
                            <InputError class="mt-2" :message="form.errors.contact_person" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="telephone" class="cooperative-label">Telephone</label>
                            <el-input
                                id="telephone"
                                v-model="form.telephone"
                                type="tel"
                                class="mt-2"
                                placeholder="+256700000000"
                            />
                            <InputError class="mt-2" :message="form.errors.telephone" />
                        </div>

                        <div>
                            <label for="email" class="cooperative-label">Email</label>
                            <el-input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-2"
                                placeholder="info@cooperative.ug"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="district" class="cooperative-label">District</label>
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
                            <label for="sub_county" class="cooperative-label">Sub-county</label>
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

                    <div>
                        <label for="address" class="cooperative-label">Address</label>
                        <el-input
                            id="address"
                            v-model="form.address"
                            type="text"
                            class="mt-2"
                            placeholder="Trading center, road, or office address"
                        />
                        <InputError class="mt-2" :message="form.errors.address" />
                    </div>

                    <div>
                        <label for="notes" class="cooperative-label">Notes</label>
                        <el-input
                            id="notes"
                            v-model="form.notes"
                            type="textarea"
                            :rows="5"
                            resize="vertical"
                            class="mt-2"
                            placeholder="Add sourcing notes, membership details, or compliance context..."
                        />
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-[#E5E7EB] pt-4">
                        <div class="w-full sm:w-auto sm:min-w-[220px]">
                            <SubmitButton :loading="form.processing" :disabled="form.processing">
                                Save cooperative
                            </SubmitButton>
                        </div>

                        <div class="font-mono text-[10px] uppercase tracking-[0.12em] text-[#9CA3AF]">
                            Cooperative registry entry
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.cooperative-label {
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #6b7280;
}
</style>
