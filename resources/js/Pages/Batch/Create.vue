<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Box, Document, Location, OfficeBuilding } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const page = usePage();

const form = useForm({
    batch_number: '',
    warehouse_location: '',
    quantity_bags: '',
    net_weight_kg: '',
    moisture_content: '',
    notes: '',
});

const successMessage = computed(() => page.props.flash?.success ?? '');
const hasErrors = computed(() => Object.keys(form.errors).length > 0);

const submit = () => {
    form.post(route('batch.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout title="Add Batch">
        <Head title="Add Batch" />

        <div class="space-y-4">
            <section class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-1.5">
                    <h1 class="font-display text-[20px] font-bold leading-tight text-[#111827]">Add Batch</h1>
                    <p class="max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                        Register a warehouse batch against an existing lot and capture the operational details needed for inventory and settlement.
                    </p>
                </div>

            </section>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_320px]">
                <div class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-4 sm:px-5 sm:py-5">
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Batch number</label>
                                <el-input v-model="form.batch_number" placeholder="e.g. BATCH-2026-001" />
                                <InputError class="mt-2 text-sm" :message="form.errors.batch_number" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Warehouse location</label>
                                <el-input v-model="form.warehouse_location" placeholder="Warehouse or collection point" />
                                <InputError class="mt-2 text-sm" :message="form.errors.warehouse_location" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Quantity (bags)</label>
                                <el-input v-model="form.quantity_bags" type="number" min="1" placeholder="e.g. 12" />
                                <InputError class="mt-2 text-sm" :message="form.errors.quantity_bags" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Net weight (kg)</label>
                                <el-input v-model="form.net_weight_kg" type="number" min="1" step="0.01" placeholder="e.g. 720" />
                                <InputError class="mt-2 text-sm" :message="form.errors.net_weight_kg" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Moisture content</label>
                                <el-input v-model="form.moisture_content" type="number" min="0" max="100" step="0.01" placeholder="Optional moisture percentage" />
                                <InputError class="mt-2 text-sm" :message="form.errors.moisture_content" />
                            </div>

                        </div>

                        <div>
                            <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Notes</label>
                            <el-input
                                v-model="form.notes"
                                type="textarea"
                                resize="vertical"
                                placeholder="Add warehouse, quality, or traceability notes"
                            />
                            <InputError class="mt-2 text-sm" :message="form.errors.notes" />
                        </div>

                        <div class="flex justify-end pt-1">
                            <SubmitButton :loading="form.processing" :full-width="false">
                                Save Batch
                            </SubmitButton>
                        </div>
                    </form>
                </div>

                <aside class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-4 sm:px-5">
                    <div class="space-y-4">
                        <div class="rounded-xl border border-[#E8F0EB] bg-[#F8FBF9] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#6B7280]">
                                        Batch Intake
                                    </p>
                                    <h2 class="mt-1 text-[18px] font-semibold leading-tight text-[#111827]">
                                        Warehouse Batch Registration
                                    </h2>
                                </div>

                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#0F5D3B] text-white">
                                    <el-icon :size="18"><Box /></el-icon>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-2 text-[13px] text-[#4B5563]">
                                <el-icon class="text-[#0F5D3B]"><Location /></el-icon>
                                <span>{{ form.warehouse_location || 'Warehouse location pending' }}</span>
                            </div>

                            <div class="mt-2 flex items-center gap-2 text-[13px] text-[#4B5563]">
                                <el-icon class="text-[#0F5D3B]"><Document /></el-icon>
                                <span>{{ form.batch_number || 'Batch number pending' }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="item in [
                                    { label: 'Warehouse', value: form.warehouse_location || 'Not set' },
                                    { label: 'Net Weight', value: form.net_weight_kg ? `${form.net_weight_kg} kg` : 'Not set' },
                                    { label: 'Moisture', value: form.moisture_content ? `${form.moisture_content}%` : 'Pending test' },
                                ]"
                                :key="item.label"
                                class="rounded-lg border border-[#E5E7EB] px-3.5 py-3"
                            >
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]">
                                        <OfficeBuilding v-if="item.label === 'Warehouse'" />
                                        <Location v-else-if="item.label === 'Net Weight'" />
                                        <Document v-else />
                                    </el-icon>
                                    <span>{{ item.label }}</span>
                                </div>
                                <p class="mt-1.5 text-[14px] font-semibold leading-6 text-[#111827]">
                                    {{ item.value }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-dashed border-[#D1D5DB] bg-[#FAFAFA] px-3.5 py-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#6B7280]">
                                Batch Intake Guide
                            </p>
                            <ul class="mt-2.5 space-y-2 text-[13px] leading-5 text-[#4B5563]">
                                <li>Use a unique batch number for warehouse and logistics traceability.</li>
                                <li>Capture warehouse location clearly so storage and dispatch teams can find the batch fast.</li>
                                <li>Capture weight and moisture accurately before the batch enters active inventory.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </AppLayout>
</template>
