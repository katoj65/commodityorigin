<script setup>
import { computed, watch } from 'vue';
import { ElNotification } from 'element-plus';
import { Close } from '@element-plus/icons-vue';
import { useForm } from '@inertiajs/vue3';
import SubmitButton from '@/Components/Button/SubmitButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    batch: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue', 'success']);

const processingMethods = ['Washed', 'Natural', 'Honey', 'Anaerobic', 'Semi-washed'];
const dryingMethods = ['Raised beds', 'Patio', 'Mechanical dryer', 'Greenhouse'];
const millingStatuses = ['Pending', 'In milling', 'Milled', 'Ready for grading'];

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

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const hydrateForm = () => {
    form.defaults({
        batch_number: props.batch.batch_number ?? '',
        variety: props.batch.variety ?? '',
        warehouse_location: props.batch.warehouse_location ?? '',
        quantity_bags: props.batch.quantity_bags ?? '',
        net_weight_kg: props.batch.net_weight_kg ?? '',
        price: props.batch.price ?? '',
        moisture_content: props.batch.moisture_content ?? '',
        processing_date: props.batch.processing_date ?? '',
        processing_method: props.batch.processing_method ?? '',
        drying_method: props.batch.drying_method ?? '',
        drying_duration: props.batch.drying_duration ?? '',
        milling_status: props.batch.milling_status ?? '',
        screen_size: props.batch.screen_size ?? '',
        defect_count: props.batch.defect_count ?? '',
        cup_score: props.batch.cup_score ?? '',
        notes: props.batch.notes ?? '',
    });

    form.reset();
    form.clearErrors();
};

watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            hydrateForm();
        }
    },
);

watch(
    () => props.batch,
    () => {
        if (props.modelValue) {
            hydrateForm();
        }
    },
);

const closeDialog = () => {
    dialogVisible.value = false;
};

const submit = () => {
    form.patch(route('batch.update', props.batch.id), {
        preserveScroll: 'errors',
        onSuccess: () => {
            ElNotification({
                title: 'Batch Updated',
                message: `Batch ${props.batch.batch_number || `#${props.batch.id}`} was updated successfully.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
            emit('success');
            closeDialog();
        },
        onError: () => {
            dialogVisible.value = true;
        },
    });
};
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(780px, calc(100vw - 2rem))"
        class="update-batch-dialog"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
    >
        <template #header>
            <div class="modal-header">
                <div class="pr-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-[#6B7280]">Batch profile</div>
                    <div class="mt-1 text-[20px] font-bold tracking-tight text-[#111827]">Edit Batch Data</div>
                    <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                        Update inventory, pricing, and processing data for this batch.
                    </p>
                </div>

                <button type="button" class="modal-close-button" aria-label="Close dialog" @click="closeDialog">
                    <el-icon :size="18"><Close /></el-icon>
                </button>
            </div>
        </template>

        <el-form label-position="top" class="update-batch-form grid gap-x-4 gap-y-2 px-5 pb-2 sm:grid-cols-2">
            <el-form-item label="Batch number">
                <el-input v-model="form.batch_number" placeholder="e.g. BATCH-2026-001" />
                <InputError :message="form.errors.batch_number" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Variety">
                <el-input v-model="form.variety" placeholder="e.g. Bourbon, Geisha, SL-28" />
                <InputError :message="form.errors.variety" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Warehouse location" class="sm:col-span-2">
                <el-input v-model="form.warehouse_location" placeholder="Warehouse or collection point" />
                <InputError :message="form.errors.warehouse_location" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Quantity (bags)">
                <el-input v-model="form.quantity_bags" type="number" min="1" placeholder="e.g. 12" />
                <InputError :message="form.errors.quantity_bags" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Net weight (kg)">
                <el-input v-model="form.net_weight_kg" type="number" min="1" step="0.01" placeholder="e.g. 720" />
                <InputError :message="form.errors.net_weight_kg" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Moisture content">
                <el-input v-model="form.moisture_content" type="number" min="0" max="100" step="0.01" placeholder="Optional moisture percentage" />
                <InputError :message="form.errors.moisture_content" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Price">
                <el-input v-model="form.price" type="number" min="0" step="0.01" placeholder="e.g. 2450" />
                <InputError :message="form.errors.price" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Processing date">
                <el-date-picker
                    v-model="form.processing_date"
                    type="date"
                    value-format="YYYY-MM-DD"
                    placeholder="Select date"
                    class="!w-full"
                />
                <InputError :message="form.errors.processing_date" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Processing method">
                <el-select v-model="form.processing_method" clearable filterable placeholder="Select method" class="!w-full">
                    <el-option
                        v-for="method in processingMethods"
                        :key="method"
                        :label="method"
                        :value="method"
                    />
                </el-select>
                <InputError :message="form.errors.processing_method" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Drying method">
                <el-select v-model="form.drying_method" clearable filterable placeholder="Select drying method" class="!w-full">
                    <el-option
                        v-for="method in dryingMethods"
                        :key="method"
                        :label="method"
                        :value="method"
                    />
                </el-select>
                <InputError :message="form.errors.drying_method" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Drying duration (days)">
                <el-input v-model="form.drying_duration" type="number" min="0" placeholder="e.g. 14" />
                <InputError :message="form.errors.drying_duration" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Milling status">
                <el-select v-model="form.milling_status" clearable filterable placeholder="Select status" class="!w-full">
                    <el-option
                        v-for="status in millingStatuses"
                        :key="status"
                        :label="status"
                        :value="status"
                    />
                </el-select>
                <InputError :message="form.errors.milling_status" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Screen size">
                <el-input v-model="form.screen_size" placeholder="e.g. 16/18" />
                <InputError :message="form.errors.screen_size" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Defect count">
                <el-input v-model="form.defect_count" type="number" min="0" placeholder="e.g. 8" />
                <InputError :message="form.errors.defect_count" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Cup score">
                <el-input v-model="form.cup_score" type="number" min="0" max="100" step="0.01" placeholder="e.g. 86.75" />
                <InputError :message="form.errors.cup_score" class="modal-input-error" />
            </el-form-item>

            <el-form-item label="Notes" class="sm:col-span-2">
                <el-input
                    v-model="form.notes"
                    type="textarea"
                    resize="vertical"
                    placeholder="Add warehouse, quality, or traceability notes"
                />
                <InputError :message="form.errors.notes" class="modal-input-error" />
            </el-form-item>
        </el-form>

        <template #footer>
            <div class="flex justify-end">
                <SubmitButton
                    native-type="button"
                    class="sm:!w-auto sm:min-w-[190px]"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="submit"
                >
                    Save Changes
                </SubmitButton>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
:deep(.update-batch-dialog),
:deep(.update-batch-dialog .el-dialog) {
    border-radius: 12px;
    overflow: hidden;
    --el-color-primary: #0e5b3f;
    --el-color-primary-light-3: #d7e8e0;
    --el-input-focus-border-color: #d7e8e0;
    --el-border-color-hover: #c7d9d1;
    --el-fill-color-blank: #ffffff;
}

:deep(.update-batch-dialog .el-dialog__header) {
    margin-right: 0;
    padding: 20px 24px 8px;
}

:deep(.update-batch-dialog .el-dialog__body) {
    max-height: min(68vh, 720px);
    overflow-y: auto;
    padding: 10px 24px 4px;
}

:deep(.update-batch-dialog .el-dialog__footer) {
    padding: 8px 24px 24px;
}

.update-batch-form :deep(.el-input),
.update-batch-form :deep(.el-textarea),
.update-batch-form :deep(.el-select),
.update-batch-form :deep(.el-date-editor) {
    width: 100%;
}

:deep(.update-batch-dialog .el-input__wrapper),
:deep(.update-batch-dialog .el-textarea__inner),
:deep(.update-batch-dialog .el-select__wrapper),
:deep(.update-batch-dialog .el-date-editor.el-input__wrapper) {
    border-radius: 8px;
    box-shadow: 0 0 0 1px #dbe1e6 inset !important;
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.modal-close-button {
    border: 0;
    background: #f3f5f4;
    color: #51615c;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
}

.modal-input-error {
    display: block;
    width: 100%;
    margin-top: 8px;
}
</style>
