<script setup>
import { computed, watch } from 'vue';
import { Close } from '@element-plus/icons-vue';
import { useForm } from '@inertiajs/vue3';
import SubmitButton from '@/Components/Button/SubmitButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    season: {
        type: Object,
        required: true,
    },
    regionOptions: {
        type: Array,
        default: () => [],
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'success']);

const form = useForm({
    name: '',
    region: '',
    start_date: '',
    end_date: '',
    notes: '',
});

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const hydrateForm = () => {
    form.defaults({
        name: props.season?.name ?? '',
        region: props.season?.region ?? '',
        start_date: props.season?.start_date ?? '',
        end_date: props.season?.end_date ?? '',
        notes: props.season?.notes ?? '',
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
    () => props.season,
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
    form.patch(route('season.update', props.season.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('success', 'Season updated successfully.');
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
        width="min(700px, calc(100vw - 2rem))"
        class="season-edit-dialog"
        destroy-on-close
        append-to-body
        align-center
        :close-on-click-modal="false"
        :show-close="false"
    >
        <template #header>
            <div class="modal-header">
                <div class="pr-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#6B7280]">Edit season</div>
                    <div class="mt-1 text-[22px] font-bold tracking-tight text-[#111827]">Update Season</div>
                    <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                        Update the core season details used for seasonal coordination and reporting.
                    </p>
                </div>
                <button type="button" class="modal-close-button" @click="closeDialog" aria-label="Close dialog">
                    <el-icon :size="18"><Close /></el-icon>
                </button>
            </div>
        </template>

        <el-form label-position="top" class="grid gap-4 p-5 pb-2 pt-2 sm:grid-cols-2">
            <el-form-item label="Season name" class="sm:col-span-2">
                <el-input v-model="form.name" placeholder="e.g. Main Crop 2026" />
                <InputError :message="form.errors.name" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Region" class="sm:col-span-2">
                <el-select v-model="form.region" placeholder="Select region" class="!w-full">
                    <el-option
                        v-for="option in props.regionOptions"
                        :key="option"
                        :label="option"
                        :value="option"
                    />
                </el-select>
                <InputError :message="form.errors.region" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Start date">
                <el-date-picker
                    v-model="form.start_date"
                    type="date"
                    value-format="YYYY-MM-DD"
                    placeholder="Select start date"
                    class="!w-full"
                />
                <InputError :message="form.errors.start_date" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="End date">
                <el-date-picker
                    v-model="form.end_date"
                    type="date"
                    value-format="YYYY-MM-DD"
                    placeholder="Select end date"
                    class="!w-full"
                />
                <InputError :message="form.errors.end_date" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Notes" class="sm:col-span-2">
                <el-input
                    v-model="form.notes"
                    type="textarea"
                    :rows="5"
                    placeholder="Add season notes, yield context, or coordination remarks."
                />
                <InputError :message="form.errors.notes" class="modal-input-error mt-2" />
            </el-form-item>
        </el-form>

        <template #footer>
            <div class="flex flex-col-reverse gap-3 px-5 pb-2 sm:flex-row sm:justify-end">

                <SubmitButton :loading="form.processing" :full-width="false" class="min-w-[180px]" @click="submit">
                    Save Season
                </SubmitButton>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
:deep(.season-edit-dialog .el-dialog) {
    border-radius: 18px;
    overflow: hidden;
}

.modal-header {
    align-items: flex-start;
    display: flex;
    justify-content: space-between;
}

.modal-close-button {
    align-items: center;
    background: #f3f4f6;
    border: 0;
    border-radius: 999px;
    color: #4b5563;
    display: inline-flex;
    height: 34px;
    justify-content: center;
    width: 34px;
}

.modal-input-error {
    color: #dc2626;
    font-size: 12px;
}

:deep(.season-edit-dialog .el-form-item__label) {
    color: #111827;
    font-size: 12px;
    font-weight: 700;
    padding-bottom: 8px;
}
</style>
