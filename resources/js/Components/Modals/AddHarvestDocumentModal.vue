<script setup>
import { computed, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { Close, Upload } from '@element-plus/icons-vue';
import { useForm } from '@inertiajs/vue3';
import SubmitButton from '@/Components/Button/SubmitButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    harvest: {
        type: Object,
        required: true,
    },
    documentTypeOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'success']);

const form = useForm({
    title: '',
    document_type: '',
    document: null,
});

const fileInput = ref(null);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const closeDialog = () => {
    dialogVisible.value = false;
};

const resetFileInput = () => {
    if (fileInput.value?.value) {
        fileInput.value.value = null;
    }
};

const hydrateForm = () => {
    form.defaults({
        title: '',
        document_type: '',
        document: null,
    });

    form.reset();
    form.clearErrors();
    resetFileInput();
};

watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            hydrateForm();
        }
    },
);

const chooseFile = () => {
    fileInput.value?.click();
};

const onFileChange = () => {
    form.document = fileInput.value?.files?.[0] ?? null;

    if (!form.title && form.document) {
        form.title = form.document.name.replace(/\.[^.]+$/, '');
    }
};

const submit = () => {
    form.post(route('harvest.documents.store', props.harvest.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            ElMessage.success('Harvest document uploaded successfully.');
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
        width="min(720px, calc(100vw - 2rem))"
        class="harvest-quality-dialog"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
    >
        <template #header>
            <div class="modal-header">
                <div class="pr-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#6B7280]">Harvest documents</div>
                    <div class="mt-1 text-[22px] font-bold tracking-tight text-[#111827]">Add Document</div>
                    <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                        Attach a supporting harvest file for traceability, compliance, or internal review.
                    </p>
                </div>
                <button type="button" class="modal-close-button" aria-label="Close dialog" @click="closeDialog">
                    <el-icon :size="18"><Close /></el-icon>
                </button>
            </div>
        </template>

        <el-form label-position="top" class="grid gap-3 p-5">
            <el-form-item label="Document title" class="sm:col-span-2">
                <el-input v-model="form.title" placeholder="e.g. Harvest intake sheet" />
                <InputError :message="form.errors.title" class="modal-input-error mt-1" />
            </el-form-item>

            <el-form-item label="Document type" class="sm:col-span-2">
                <el-select v-model="form.document_type" placeholder="Select document type" class="!w-full">
                    <el-option
                        v-for="option in props.documentTypeOptions"
                        :key="option"
                        :label="option"
                        :value="option"
                    />
                </el-select>
                <InputError :message="form.errors.document_type" class="modal-input-error mt-1" />
            </el-form-item>

            <el-form-item label="File" class="sm:col-span-2">
                <input
                    ref="fileInput"
                    type="file"
                    class="hidden"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                    @change="onFileChange"
                >

                <button type="button" class="document-picker" @click="chooseFile">
                    <span class="document-picker__icon">
                        <el-icon :size="18"><Upload /></el-icon>
                    </span>
                    <span class="document-picker__copy">
                        <span class="document-picker__title">{{ form.document?.name || 'Choose a file' }}</span>
                        <span class="document-picker__hint">
                            Upload a harvest support file for verification, audit, or internal review.
                        </span>
                        <span class="document-picker__meta">PDF, image, Word, or Excel up to 10 MB</span>
                    </span>
                </button>

                <InputError :message="form.errors.document" class="modal-input-error mt-1" />
            </el-form-item>
        </el-form>

        <template #footer>
            <div class="flex justify-end">
                <SubmitButton
                    native-type="button"
                    class="sm:!w-auto sm:min-w-[220px]"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="submit"
                >
                    Save Document
                </SubmitButton>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
:deep(.harvest-quality-dialog),
:deep(.harvest-quality-dialog .el-dialog) {
    border-radius: 20px;
    overflow: hidden;
    --el-color-primary: #0e5b3f;
    --el-color-primary-light-3: #d7e8e0;
    --el-color-primary-light-5: #e6f0ec;
    --el-input-focus-color: #d7e8e0;
    --el-input-focus-border-color: #d7e8e0;
    --el-border-color-hover: #c7d9d1;
    --el-select-input-focus-border-color: #d7e8e0;
    --el-fill-color-blank: #ffffff;
}

:deep(.harvest-quality-dialog .el-dialog__header) {
    margin-right: 0;
    padding: 20px 24px 8px;
}

:deep(.harvest-quality-dialog .el-dialog__body) {
    padding: 10px 24px 6px;
}

:deep(.harvest-quality-dialog .el-dialog__footer) {
    padding: 8px 24px 24px;
}

:deep(.harvest-quality-dialog .el-input__wrapper),
:deep(.harvest-quality-dialog .el-textarea__inner),
:deep(.harvest-quality-dialog .el-select__wrapper),
:deep(.harvest-quality-dialog .el-date-editor.el-input__wrapper) {
    border-radius: 12px;
    box-shadow: 0 0 0 1px #dbe1e6 inset !important;
    min-height: 48px;
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
    width: 36px;
    height: 36px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.modal-close-button:hover {
    background: #e7ecea;
    color: #18342d;
}

.modal-input-error :deep(p) {
    margin: 0;
}

.modal-input-error {
    min-height: 0.75rem;
}

.document-picker {
    width: 100%;
    min-height: 84px;
    border: 1px dashed #cfd8d4;
    border-radius: 12px;
    background: #f8fbf9;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-align: left;
    cursor: pointer;
}

.document-picker__icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #e5f3ec;
    color: #0e6a46;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.document-picker__copy {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.document-picker__title {
    color: #17322c;
    font-size: 13px;
    font-weight: 700;
}

.document-picker__hint {
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
}

.document-picker__meta {
    color: #8a9590;
    font-size: 11px;
    line-height: 1.4;
}
</style>
