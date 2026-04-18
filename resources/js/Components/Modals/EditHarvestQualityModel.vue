<script setup>
import { computed, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { Close } from '@element-plus/icons-vue';
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
    pickMethodOptions: {
        type: Array,
        default: () => [],
    },
    harvestSeasonOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'success']);

const form = useForm({
    harvest_date: '',
    harvest_season: '',
    pick_method: '',
    price: '',
    weight: '',
    ripeness_percentage: '',
    foreign_matter_present: false,
    pest_damage: false,
    disease_signs: false,
    visible_defects: false,
});

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const disableFutureDates = (date) => {
    const today = new Date();
    today.setHours(23, 59, 59, 999);

    return date.getTime() > today.getTime();
};

const hydrateForm = () => {
    form.defaults({
        harvest_date: props.harvest?.harvest_date ?? '',
        harvest_season: props.harvest?.harvest_season ?? '',
        pick_method: props.harvest?.pick_method ?? '',
        price: props.harvest?.price ?? '',
        weight: props.harvest?.weight ?? '',
        ripeness_percentage: props.harvest?.ripeness_percentage ?? '',
        foreign_matter_present: Boolean(props.harvest?.foreign_matter_present),
        pest_damage: Boolean(props.harvest?.pest_damage),
        disease_signs: Boolean(props.harvest?.disease_signs),
        visible_defects: Boolean(props.harvest?.visible_defects),
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
    () => props.harvest,
    () => {
        if (props.modelValue) {
            hydrateForm();
        }
    },
    { deep: true },
);

const closeDialog = () => {
    dialogVisible.value = false;
};

const submit = () => {
    form.patch(route('harvest.update', props.harvest.id), {
        preserveScroll: true,
        onSuccess: () => {
            ElMessage.success('Harvest profile updated successfully.');
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
        width="min(760px, calc(100vw - 2rem))"
        class="harvest-quality-dialog"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
    >
        <template #header>
            <div class="modal-header">
                <div class="pr-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#6B7280]">Edit harvest profile</div>
                    <div class="mt-1 text-[22px] font-bold tracking-tight text-[#111827]">Edit Harvest Profile</div>
                    <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                        Update the quality and intake details used for evaluation, pricing, and downstream traceability.
                    </p>
                </div>
                <button type="button" class="modal-close-button" @click="closeDialog" aria-label="Close dialog">
                    <el-icon :size="18"><Close /></el-icon>
                </button>
            </div>
        </template>

        <el-form label-position="top" class="grid gap-4 p-5 sm:grid-cols-2">
            <el-form-item label="Harvest date">
                <el-date-picker
                    v-model="form.harvest_date"
                    type="date"
                    value-format="YYYY-MM-DD"
                    placeholder="Select harvest date"
                    class="!w-full"
                    :disabled-date="disableFutureDates"
                />
                <InputError :message="form.errors.harvest_date" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Harvest season">
                <el-select v-model="form.harvest_season" placeholder="Select harvest season" class="!w-full">
                    <el-option
                        v-for="option in props.harvestSeasonOptions"
                        :key="option"
                        :label="option"
                        :value="option"
                    />
                </el-select>
                <InputError :message="form.errors.harvest_season" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Pick method">
                <el-select v-model="form.pick_method" placeholder="Select pick method" class="!w-full">
                    <el-option
                        v-for="option in props.pickMethodOptions"
                        :key="option"
                        :label="option"
                        :value="option"
                    />
                </el-select>
                <InputError :message="form.errors.pick_method" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Ripeness percentage">
                <el-input
                    v-model="form.ripeness_percentage"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    placeholder="e.g. 92.50"
                >
                    <template #suffix>%</template>
                </el-input>
                <InputError :message="form.errors.ripeness_percentage" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Weight (kg)">
                <el-input
                    v-model="form.weight"
                    type="number"
                    min="0.01"
                    step="0.01"
                    placeholder="e.g. 1250"
                />
                <InputError :message="form.errors.weight" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Price">
                <el-input
                    v-model="form.price"
                    type="number"
                    min="0.01"
                    step="0.01"
                    placeholder="e.g. 4.85"
                >
                    <template #prefix>Shs.</template>
                </el-input>
                <InputError :message="form.errors.price" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Quality indicators" class="sm:col-span-2">
                <div class="grid w-full gap-3 sm:grid-cols-2">
                    <label class="modal-flag-row">
                        <span>Foreign matter present</span>
                        <el-switch v-model="form.foreign_matter_present" />
                    </label>

                    <label class="modal-flag-row">
                        <span>Pest damage</span>
                        <el-switch v-model="form.pest_damage" />
                    </label>

                    <label class="modal-flag-row">
                        <span>Disease signs</span>
                        <el-switch v-model="form.disease_signs" />
                    </label>

                    <label class="modal-flag-row">
                        <span>Visible defects</span>
                        <el-switch v-model="form.visible_defects" />
                    </label>
                </div>

                <div class="mt-2 grid w-full gap-1 sm:grid-cols-2">
                    <InputError :message="form.errors.foreign_matter_present" class="modal-input-error" />
                    <InputError :message="form.errors.pest_damage" class="modal-input-error" />
                    <InputError :message="form.errors.disease_signs" class="modal-input-error" />
                    <InputError :message="form.errors.visible_defects" class="modal-input-error" />
                </div>
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
                    Save Harvest Profile
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
    padding: 12px 24px 8px;
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

:deep(.harvest-quality-dialog .el-date-editor.el-input) {
    border: none !important;
}

.modal-input-error :deep(p) {
    margin: 0;
}

.modal-input-error {
    min-height: 1rem;
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

.modal-flag-row {
    border: 1px solid #dbe1e6;
    border-radius: 12px;
    min-height: 48px;
    padding: 0.8rem 0.95rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    color: #111827;
    font-size: 13px;
    font-weight: 500;
}
</style>
