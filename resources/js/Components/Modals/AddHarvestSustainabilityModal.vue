<script setup>
import { computed, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { Close } from '@element-plus/icons-vue';
import { useForm } from '@inertiajs/vue3';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    harvest: {
        type: Object,
        required: true,
    },
    sustainability: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:modelValue', 'save', 'success']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({
    organicCertified: true,
    climateSmart: true,
    shadeGrown: true,
    waterManagement: true,
    soilConservation: true,
    lowCarbon: true,
    fairWages: true,
    notes: '',
});

const hydrateForm = () => {
    form.defaults({
        organicCertified: props.sustainability.organicCertified ?? true,
        climateSmart: props.sustainability.climateSmart ?? true,
        shadeGrown: props.sustainability.shadeGrown ?? true,
        waterManagement: props.sustainability.waterManagement ?? true,
        soilConservation: props.sustainability.soilConservation ?? true,
        lowCarbon: props.sustainability.lowCarbon ?? true,
        fairWages: props.sustainability.fairWages ?? true,
        notes: props.sustainability.notes ?? '',
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

const closeDialog = () => {
    dialogVisible.value = false;
};

const save = () => {
    form.post(route('harvest.sustainability.store', props.harvest.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('save', { ...form.data() });
            emit('success');
            ElMessage.success('Sustainability details updated.');
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
        width="min(760px, calc(100vw - 2rem))"
        class="harvest-sustainability-dialog"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
    >
        <template #header>
            <div class="modal-header">
                <div class="pr-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#6B7280]">Harvest sustainability</div>
                    <div class="mt-1 text-[22px] font-bold tracking-tight text-[#111827]">Add Sustainability Details</div>
                    <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                        Capture farm sustainability indicators and certifications linked to this harvest.
                    </p>
                </div>
                <button type="button" class="modal-close-button" aria-label="Close dialog" @click="closeDialog">
                    <el-icon :size="18"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="p-5 pt-3">
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="modal-flag-row">
                    <span>Organic Certified</span>
                    <el-switch v-model="form.organicCertified" />
                </label>
                <label class="modal-flag-row">
                    <span>Climate Smart</span>
                    <el-switch v-model="form.climateSmart" />
                </label>
                <label class="modal-flag-row">
                    <span>Shade Grown</span>
                    <el-switch v-model="form.shadeGrown" />
                </label>
                <label class="modal-flag-row">
                    <span>Water Management</span>
                    <el-switch v-model="form.waterManagement" />
                </label>
                <label class="modal-flag-row">
                    <span>Soil Conservation</span>
                    <el-switch v-model="form.soilConservation" />
                </label>
                <label class="modal-flag-row">
                    <span>Low Carbon</span>
                    <el-switch v-model="form.lowCarbon" />
                </label>
                <label class="modal-flag-row sm:col-span-2">
                    <span>Fair Wages</span>
                    <el-switch v-model="form.fairWages" />
                </label>
            </div>

            <el-form label-position="top" class="mt-4">
                <el-form-item label="Notes">
                    <el-input
                        v-model="form.notes"
                        type="textarea"
                        :rows="4"
                        placeholder="Add any notes about certifications, field practices, or sustainability verification."
                    />
                </el-form-item>
            </el-form>
        </div>

        <template #footer>
            <div class="flex justify-end">
                <SubmitButton
                    native-type="button"
                    class="sm:!w-auto sm:min-w-[220px]"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="save"
                >
                    Save Sustainability Details
                </SubmitButton>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
:deep(.harvest-sustainability-dialog),
:deep(.harvest-sustainability-dialog .el-dialog) {
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

:deep(.harvest-sustainability-dialog .el-dialog__header) {
    margin-right: 0;
    padding: 20px 24px 8px;
}

:deep(.harvest-sustainability-dialog .el-dialog__body) {
    padding: 0 24px 6px;
}

:deep(.harvest-sustainability-dialog .el-dialog__footer) {
    padding: 8px 24px 24px;
}

:deep(.harvest-sustainability-dialog .el-input__wrapper),
:deep(.harvest-sustainability-dialog .el-textarea__inner),
:deep(.harvest-sustainability-dialog .el-select__wrapper) {
    border-radius: 12px;
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
    width: 36px;
    height: 36px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
}

.modal-flag-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border: 1px solid #e7ecea;
    border-radius: 12px;
    background: #f8fafc;
    padding: 0.9rem 1rem;
    color: #18342d;
    font-size: 0.92rem;
    font-weight: 600;
}
</style>
