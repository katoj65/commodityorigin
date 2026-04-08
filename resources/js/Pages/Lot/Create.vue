<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Box, Document, Location, OfficeBuilding, User } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    farms: {
        type: Array,
        default: () => [],
    },
    processOptions: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    farm_id: props.farms[0]?.id ?? '',
    lot_number: '',
    process: '',
    grade: '',
    quantity_bags: '',
    bag_weight_kg: '',
    reserve_price: '',
    quality_score: '',
    notes: '',
});

const selectedFarm = computed(() => props.farms.find((farm) => farm.id === form.farm_id) ?? null);

const farmerName = computed(() => {
    const farmer = selectedFarm.value?.farmer;

    return [farmer?.first_name, farmer?.last_name].filter(Boolean).join(' ') || 'Farmer not assigned';
});

const farmMeta = computed(() => [
    {
        label: 'Origin',
        value: [selectedFarm.value?.farmer?.sub_county, selectedFarm.value?.farmer?.district]
            .filter(Boolean)
            .join(', ') || selectedFarm.value?.location || 'Location pending',
    },
    {
        label: 'Variety',
        value: selectedFarm.value?.variety || 'Not set',
    },
    {
        label: 'Farm Size',
        value: selectedFarm.value?.size || 'Not set',
    },
]);

const submit = () => {
    form.post(route('lot.store'));
};
</script>

<template>
    <AppLayout title="Add Lot">
        <Head title="Add Lot" />

        <div class="space-y-4">
            <section class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-1.5">
                    <h1 class="font-display text-[20px] font-bold leading-tight text-[#111827]">Add Lot</h1>
                    <p class="max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                        Register a traceable coffee lot against an existing farm and prepare it for bidding, inventory, and settlement workflows.
                    </p>
                </div>
            </section>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_320px]">
                <div class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-4 sm:px-5 sm:py-5">
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Farm</label>
                                <el-select v-model="form.farm_id" class="w-full" placeholder="Select farm">
                                    <el-option
                                        v-for="farm in props.farms"
                                        :key="farm.id"
                                        :label="`${farm.name} · ${[farm.farmer?.first_name, farm.farmer?.last_name].filter(Boolean).join(' ')}`"
                                        :value="farm.id"
                                    />
                                </el-select>
                                <InputError class="mt-2 text-sm" :message="form.errors.farm_id" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Lot number</label>
                                <el-input v-model="form.lot_number" placeholder="e.g. LOT-2026-001" />
                                <InputError class="mt-2 text-sm" :message="form.errors.lot_number" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Process</label>
                                <el-select v-model="form.process" class="w-full" placeholder="Select process">
                                    <el-option
                                        v-for="option in props.processOptions"
                                        :key="option"
                                        :label="option"
                                        :value="option"
                                    />
                                </el-select>
                                <InputError class="mt-2 text-sm" :message="form.errors.process" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Grade</label>
                                <el-input v-model="form.grade" placeholder="e.g. AA, Screen 18, Specialty" />
                                <InputError class="mt-2 text-sm" :message="form.errors.grade" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Quantity (bags)</label>
                                <el-input v-model="form.quantity_bags" type="number" min="1" placeholder="e.g. 18" />
                                <InputError class="mt-2 text-sm" :message="form.errors.quantity_bags" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Bag weight (kg)</label>
                                <el-input v-model="form.bag_weight_kg" type="number" min="1" step="0.01" placeholder="e.g. 60" />
                                <InputError class="mt-2 text-sm" :message="form.errors.bag_weight_kg" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Reserve price</label>
                                <el-input v-model="form.reserve_price" type="number" min="0" step="0.01" placeholder="Optional reserve price" />
                                <InputError class="mt-2 text-sm" :message="form.errors.reserve_price" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Quality score</label>
                                <el-input v-model="form.quality_score" type="number" min="0" max="100" step="0.01" placeholder="Optional SCAA score" />
                                <InputError class="mt-2 text-sm" :message="form.errors.quality_score" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Notes</label>
                            <el-input
                                v-model="form.notes"
                                type="textarea"
                                resize="vertical"
                                placeholder="Add cupping notes, warehouse notes, or auction context"
                            />
                            <InputError class="mt-2 text-sm" :message="form.errors.notes" />
                        </div>

                        <div class="flex justify-end pt-1">
                            <SubmitButton :loading="form.processing" :full-width="false">
                                Save Lot
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
                                        Selected Farm
                                    </p>
                                    <h2 class="mt-1 text-[18px] font-semibold leading-tight text-[#111827]">
                                        {{ selectedFarm?.name || 'Choose a farm' }}
                                    </h2>
                                </div>

                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#0F5D3B] text-white">
                                    <el-icon :size="18"><Box /></el-icon>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-2 text-[13px] text-[#4B5563]">
                                <el-icon class="text-[#0F5D3B]"><User /></el-icon>
                                <span>{{ farmerName }}</span>
                            </div>

                            <div class="mt-2 flex items-center gap-2 text-[13px] text-[#4B5563]">
                                <el-icon class="text-[#0F5D3B]"><Location /></el-icon>
                                <span>{{ selectedFarm?.location || 'Farm location pending' }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="item in farmMeta"
                                :key="item.label"
                                class="rounded-lg border border-[#E5E7EB] px-3.5 py-3"
                            >
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]">
                                        <Location v-if="item.label === 'Origin'" />
                                        <Document v-else-if="item.label === 'Variety'" />
                                        <OfficeBuilding v-else />
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
                                Lot Intake Guide
                            </p>
                            <ul class="mt-2.5 space-y-2 text-[13px] leading-5 text-[#4B5563]">
                                <li>Use a unique lot number that can be traced across auction, inventory, and settlement records.</li>
                                <li>Match the lot to the correct farm so origin reporting stays consistent.</li>
                                <li>Capture process, grade, and quantity clearly for downstream bidding and export workflows.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </AppLayout>
</template>
