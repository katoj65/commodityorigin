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
    variety: '',
    warehouse_location: '',
    quantity_bags: '',
    net_weight_kg: '',
    price: '',
    moisture_content: '',
    processing_date: '',
    processing_method: '',
    drying_method: '',
    drying_duration: '',
    milling_status: '',
    screen_size: '',
    defect_count: '',
    cup_score: '',
    notes: '',
});

const successMessage = computed(() => page.props.flash?.success ?? '');
const hasErrors = computed(() => Object.keys(form.errors).length > 0);

const processingMethods = ['Washed', 'Natural', 'Honey', 'Anaerobic', 'Semi-washed'];
const dryingMethods = ['Raised beds', 'Patio', 'Mechanical dryer', 'Greenhouse'];
const millingStatuses = ['Pending', 'In milling', 'Milled', 'Ready for grading'];

const submit = () => {
    form.post(route('batch.store'), {
        preserveScroll: 'errors',
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
            <section class="rounded-xl border border-[#EEF2F0] bg-white px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-1.5">
                    <h1 class="font-display text-[18px] font-bold leading-tight text-[#111827]">Add Batch</h1>
                    <p class="max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                        Register a warehouse batch against an existing lot and capture the operational details needed for inventory and settlement.
                    </p>
                </div>

            </section>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_320px]">
                <div class="rounded-xl border border-[#EEF2F0] bg-white px-4 py-4 sm:px-5 sm:py-5">
                    <form class="batch-create-form space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Batch number</label>
                                <el-input v-model="form.batch_number" placeholder="e.g. BATCH-2026-001" />
                                <InputError class="batch-create-input-error" :message="form.errors.batch_number" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Variety <span class="text-red-500">*</span></label>
                                <el-input v-model="form.variety" placeholder="e.g. Bourbon, Geisha, SL-28" />
                                <InputError class="batch-create-input-error" :message="form.errors.variety" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Warehouse location</label>
                                <el-input v-model="form.warehouse_location" placeholder="Warehouse or collection point" />
                                <InputError class="batch-create-input-error" :message="form.errors.warehouse_location" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Quantity (bags)</label>
                                <el-input v-model="form.quantity_bags" type="number" min="1" placeholder="e.g. 12" />
                                <InputError class="batch-create-input-error" :message="form.errors.quantity_bags" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Net weight (kg)</label>
                                <el-input v-model="form.net_weight_kg" type="number" min="1" step="0.01" placeholder="e.g. 720" />
                                <InputError class="batch-create-input-error" :message="form.errors.net_weight_kg" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Moisture content</label>
                                <el-input v-model="form.moisture_content" type="number" min="0" max="100" step="0.01" placeholder="Optional moisture percentage" />
                                <InputError class="batch-create-input-error" :message="form.errors.moisture_content" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Price <span class="text-red-500">*</span></label>
                                <el-input v-model="form.price" type="number" min="0" step="0.01" placeholder="e.g. 2450" />
                                <InputError class="batch-create-input-error" :message="form.errors.price" />
                            </div>
                        </div>

                        <div class="border-t border-[#EEF2F0] pt-5">
                            <div class="mb-4">
                                <h2 class="text-[15px] font-semibold text-[#111827]">Processing details</h2>
                                <p class="mt-1 text-[13px] leading-5 text-[#6B7280]">
                                    Add the quality and processing notes available at intake.
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="batch-create-field">
                                    <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Processing date <span class="text-red-500">*</span></label>
                                    <div class="batch-create-control-row">
                                        <el-date-picker
                                            v-model="form.processing_date"
                                            type="date"
                                            value-format="YYYY-MM-DD"
                                            placeholder="Select date"
                                            class="batch-create-date-picker !w-full"
                                        />
                                    </div>
                                    <div v-if="form.errors.processing_date" class="batch-create-date-error-slot">
                                        <InputError :message="form.errors.processing_date" />
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Processing method <span class="text-red-500">*</span></label>
                                    <el-select v-model="form.processing_method" clearable filterable placeholder="Select method" class="!w-full">
                                        <el-option
                                            v-for="method in processingMethods"
                                            :key="method"
                                            :label="method"
                                            :value="method"
                                        />
                                    </el-select>
                                    <InputError class="batch-create-input-error" :message="form.errors.processing_method" />
                                </div>

                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Drying method <span class="text-red-500">*</span></label>
                                    <el-select v-model="form.drying_method" clearable filterable placeholder="Select drying method" class="!w-full">
                                        <el-option
                                            v-for="method in dryingMethods"
                                            :key="method"
                                            :label="method"
                                            :value="method"
                                        />
                                    </el-select>
                                    <InputError class="batch-create-input-error" :message="form.errors.drying_method" />
                                </div>

                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Drying duration (days)</label>
                                    <el-input v-model="form.drying_duration" type="number" min="0" placeholder="e.g. 14" />
                                    <InputError class="batch-create-input-error" :message="form.errors.drying_duration" />
                                </div>

                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Milling status</label>
                                    <el-select v-model="form.milling_status" clearable filterable placeholder="Select status" class="!w-full">
                                        <el-option
                                            v-for="status in millingStatuses"
                                            :key="status"
                                            :label="status"
                                            :value="status"
                                        />
                                    </el-select>
                                    <InputError class="batch-create-input-error" :message="form.errors.milling_status" />
                                </div>

                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Screen size</label>
                                    <el-input v-model="form.screen_size" placeholder="e.g. 16/18" />
                                    <InputError class="batch-create-input-error" :message="form.errors.screen_size" />
                                </div>

                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Defect count</label>
                                    <el-input v-model="form.defect_count" type="number" min="0" placeholder="e.g. 8" />
                                    <InputError class="batch-create-input-error" :message="form.errors.defect_count" />
                                </div>

                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Cup score</label>
                                    <el-input v-model="form.cup_score" type="number" min="0" max="100" step="0.01" placeholder="e.g. 86.75" />
                                    <InputError class="batch-create-input-error" :message="form.errors.cup_score" />
                                </div>
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
                            <InputError class="batch-create-input-error" :message="form.errors.notes" />
                        </div>

                        <div class="flex justify-end pt-1">
                            <SubmitButton :loading="form.processing" :full-width="false">
                                Save Batch
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
                                    { label: 'Variety', value: form.variety || 'Not set' },
                                    { label: 'Net Weight', value: form.net_weight_kg ? `${form.net_weight_kg} kg` : 'Not set' },
                                    { label: 'Price', value: form.price ? `$${form.price}` : 'Not set' },
                                    { label: 'Cup Score', value: form.cup_score || 'Pending test' },
                                ]"
                                :key="item.label"
                                class="rounded-lg border border-[#EEF2F0] px-3.5 py-3"
                            >
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]">
                                        <OfficeBuilding v-if="item.label === 'Warehouse'" />
                                        <Document v-else-if="item.label === 'Variety'" />
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

<style scoped>
.batch-create-field {
    display: flex;
    min-width: 0;
    flex-direction: column;
}

.batch-create-date-picker {
    width: 100%;
}

.batch-create-control-row {
    width: 100%;
}

.batch-create-form :deep(.el-input),
.batch-create-form :deep(.el-input-number),
.batch-create-form :deep(.el-textarea),
.batch-create-form :deep(.el-select),
.batch-create-form :deep(.el-date-editor) {
    width: 100%;
}

.batch-create-input-error {
    display: block;
    margin-top: 15px;
    line-height: 1.35;
}

.batch-create-date-error-slot {
    margin-top: 30px;
    line-height: 1.35;
}

.batch-create-date-error-slot :deep(p) {
    margin: 0;

}
</style>
