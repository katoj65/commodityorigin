<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Files } from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    processOptions: {
        type: Array,
        default: () => [],
    },
    coffeeGradeOptions: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    process: '',
    grade: '',
    quantity_bags: '',
    bag_weight_kg: '',
    price: '',
    quality_score: '',
    notes: '',
});

const submit = () => {
    form.post(route('lot.store'));
};
</script>

<template>
    <DesignPreviewLayout title="Add Lot">
        <Head title="Add Lot" />

        <div class="space-y-4">
            <section class="rounded-xl border border-[#EEF2F0] bg-white px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-1.5">
                    <h1 class="font-display text-[20px] font-bold leading-tight text-[#111827]">Add Lot</h1>
                    <p class="max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                        Register a traceable coffee lot against an existing batch and prepare it for bidding, inventory, and settlement workflows.
                    </p>
                </div>
            </section>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_320px]">
                <div class="rounded-xl border border-[#EEF2F0] bg-white px-4 py-4 sm:px-5 sm:py-5">
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 md:grid-cols-2">
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
                                <el-select v-model="form.grade" class="w-full" placeholder="Select grade">
                                    <el-option
                                        v-for="option in props.coffeeGradeOptions"
                                        :key="option"
                                        :label="option"
                                        :value="option"
                                    />
                                </el-select>
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
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Price</label>
                                <el-input v-model="form.price" type="number" min="0" step="0.01" placeholder="Optional price" />
                                <InputError class="mt-2 text-sm" :message="form.errors.price" />
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

                <aside class="rounded-xl border border-[#EEF2F0] bg-white px-4 py-4 sm:px-5">
                    <div class="space-y-4">
                        <div class="rounded-xl border border-[#E8F0EB] bg-[#F8FBF9] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#6B7280]">
                                        Lot Number
                                    </p>
                                    <h2 class="mt-1 text-[18px] font-semibold leading-tight text-[#111827]">
                                        Auto-generated on save
                                    </h2>
                                </div>

                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#0F5D3B] text-white">
                                    <el-icon :size="18"><Files /></el-icon>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-dashed border-[#D1D5DB] bg-[#FAFAFA] px-3.5 py-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#6B7280]">
                                Lot Intake Guide
                            </p>
                            <ul class="mt-2.5 space-y-2 text-[13px] leading-5 text-[#4B5563]">
                                <li>Capture process, grade, and quantity clearly for downstream bidding and export workflows.</li>
                                <li>Link this lot to the batch it was sourced from afterward, from the lot's own profile page.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </DesignPreviewLayout>
</template>
