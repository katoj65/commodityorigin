<script setup>
import { computed } from 'vue';
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

const form = useForm({
    compliance_type: '',
    status: 'pending',
    certificate_number: '',
    issued_by: '',
    issued_at: '',
    expires_at: '',
    notes: '',
});

const statusOptions = [
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Rejected', value: 'rejected' },
    { label: 'Expired', value: 'expired' },
];

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const hydrateForm = () => {
    form.defaults({
        compliance_type: '',
        status: 'pending',
        certificate_number: '',
        issued_by: '',
        issued_at: '',
        expires_at: '',
        notes: '',
    });

    form.reset();
    form.clearErrors();
};

const closeDialog = () => {
    dialogVisible.value = false;
};

const submit = () => {
    form.post(route('batch.compliance.store', props.batch.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            closeDialog();
            hydrateForm();
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
        width="min(680px, calc(100vw - 2rem))"
        class="batch-compliance-dialog"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        @closed="hydrateForm"
    >
        <template #header>
            <div class="modal-header">
                <div class="pr-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-[#6B7280]">Batch compliance</div>
                    <div class="mt-1 text-[20px] font-bold tracking-tight text-[#111827]">Add Compliance</div>
                    <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                        Save certification or review details for this batch profile.
                    </p>
                </div>

                <button type="button" class="modal-close-button" aria-label="Close dialog" @click="closeDialog">
                    <el-icon :size="18"><Close /></el-icon>
                </button>
            </div>
        </template>

        <el-form label-position="top" class="grid gap-x-4 gap-y-2 px-5 pb-2 sm:grid-cols-2">
            <el-form-item label="Compliance type">
                <el-input v-model="form.compliance_type" placeholder="e.g. Organic certificate" />
                <InputError :message="form.errors.compliance_type" class="modal-input-error mt-1" />
            </el-form-item>

            <el-form-item label="Status">
                <el-select v-model="form.status" placeholder="Select status" class="!w-full">
                    <el-option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :label="option.label"
                        :value="option.value"
                    />
                </el-select>
                <InputError :message="form.errors.status" class="modal-input-error mt-1" />
            </el-form-item>

            <el-form-item label="Certificate number">
                <el-input v-model="form.certificate_number" placeholder="Optional certificate number" />
                <InputError :message="form.errors.certificate_number" class="modal-input-error mt-1" />
            </el-form-item>

            <el-form-item label="Issued by">
                <el-input v-model="form.issued_by" placeholder="Issuing body" />
                <InputError :message="form.errors.issued_by" class="modal-input-error mt-1" />
            </el-form-item>

            <el-form-item label="Issued date">
                <el-date-picker
                    v-model="form.issued_at"
                    type="date"
                    value-format="YYYY-MM-DD"
                    placeholder="Select date"
                    class="!w-full"
                />
                <InputError :message="form.errors.issued_at" class="modal-input-error mt-1" />
            </el-form-item>

            <el-form-item label="Expiry date">
                <el-date-picker
                    v-model="form.expires_at"
                    type="date"
                    value-format="YYYY-MM-DD"
                    placeholder="Select date"
                    class="!w-full"
                />
                <InputError :message="form.errors.expires_at" class="modal-input-error mt-1" />
            </el-form-item>

            <el-form-item label="Notes" class="sm:col-span-2">
                <el-input
                    v-model="form.notes"
                    type="textarea"
                    resize="vertical"
                    placeholder="Add compliance notes"
                />
                <InputError :message="form.errors.notes" class="modal-input-error mt-1" />
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
                    Save Compliance
                </SubmitButton>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
:deep(.batch-compliance-dialog),
:deep(.batch-compliance-dialog .el-dialog) {
    border-radius: 12px;
    overflow: hidden;
    --el-color-primary: #0e5b3f;
    --el-color-primary-light-3: #d7e8e0;
    --el-input-focus-border-color: #d7e8e0;
    --el-border-color-hover: #c7d9d1;
    --el-fill-color-blank: #ffffff;
}

:deep(.batch-compliance-dialog .el-dialog__header) {
    margin-right: 0;
    padding: 20px 24px 8px;
}

:deep(.batch-compliance-dialog .el-dialog__body) {
    padding: 10px 24px 4px;
}

:deep(.batch-compliance-dialog .el-dialog__footer) {
    padding: 8px 24px 24px;
}

:deep(.batch-compliance-dialog .el-input__wrapper),
:deep(.batch-compliance-dialog .el-textarea__inner),
:deep(.batch-compliance-dialog .el-select__wrapper),
:deep(.batch-compliance-dialog .el-date-editor.el-input__wrapper) {
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
}
</style>
