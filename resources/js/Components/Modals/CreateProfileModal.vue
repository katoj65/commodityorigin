<script setup>
import { computed } from 'vue';
import { ElMessage } from 'element-plus';
import { useForm, usePage } from '@inertiajs/vue3';
import SubmitButton from '@/Components/Button/SubmitButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);
const page = usePage();
const existingProfile = computed(() => page.props.auth?.user?.profile ?? null);

const form = useForm({
    bio: '',
    date_of_birth: '',
    gender: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    country: 'Uganda',
    postal_code: '',
});

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const hydrateForm = () => {
    form.defaults({
        bio: existingProfile.value?.bio ?? '',
        date_of_birth: existingProfile.value?.date_of_birth ?? '',
        gender: existingProfile.value?.gender ?? '',
        address_line_1: existingProfile.value?.address_line_1 ?? '',
        address_line_2: existingProfile.value?.address_line_2 ?? '',
        city: existingProfile.value?.city ?? '',
        state: existingProfile.value?.state ?? '',
        country: existingProfile.value?.country ?? 'Uganda',
        postal_code: existingProfile.value?.postal_code ?? '',
    });

    form.reset();
    form.clearErrors();
};

const closeDialog = () => {
    dialogVisible.value = false;
};

const submitProfile = () => {
    form.post(route('profile.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            ElMessage.success('Profile saved successfully.');
            closeDialog();
            hydrateForm();
        },
        onError: () => {
            dialogVisible.value = true;
        },
    });
};
hydrateForm();
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(720px, calc(100vw - 2rem))"
        class="create-profile-dialog"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
    >
        <template #header>
            <div class="pr-8">
                <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#6B7280]">Create profile</div>
                <div class="mt-1 text-[22px] font-bold tracking-tight text-[#111827]">User Profile Setup</div>
                <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                    Add the core profile details used for identity, address, and trading desk onboarding.
                </p>
            </div>
        </template>

        <el-form label-position="top" class="grid gap-4 sm:grid-cols-2 p-5">
            <el-form-item label="Date of birth">
                <el-date-picker
                    v-model="form.date_of_birth"
                    type="date"
                    placeholder="Select date"
                    class="!w-full mb-1"
                    value-format="YYYY-MM-DD"

                />
                <InputError :message="form.errors.date_of_birth" class="modal-input-error mt-5" />
            </el-form-item>

            <el-form-item label="Gender">
                <el-select v-model="form.gender" placeholder="Select gender" class="!w-full">
                    <el-option label="Male" value="male" />
                    <el-option label="Female" value="female" />
                    <el-option label="Prefer not to say" value="prefer_not_to_say" />
                </el-select>
                <InputError :message="form.errors.gender" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Address line 1" class="sm:col-span-2">
                <el-input v-model="form.address_line_1" placeholder="Street, village, or plot" />
                <InputError :message="form.errors.address_line_1" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Address line 2" class="sm:col-span-2">
                <el-input v-model="form.address_line_2" placeholder="Apartment, landmark, or extra details" />
                <InputError :message="form.errors.address_line_2" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="City">
                <el-input v-model="form.city" placeholder="City or town" />
                <InputError :message="form.errors.city" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="State / District">
                <el-input v-model="form.state" placeholder="District or region" />
                <InputError :message="form.errors.state" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Country">
                <el-input v-model="form.country" placeholder="Country" />
                <InputError :message="form.errors.country" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Postal code">
                <el-input v-model="form.postal_code" placeholder="Postal code" />
                <InputError :message="form.errors.postal_code" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Short bio" class="sm:col-span-2">
                <el-input
                    v-model="form.bio"
                    type="textarea"
                    :rows="4"
                    resize="none"
                    placeholder="Tell us about the trader, desk, or business profile"
                />
                <InputError :message="form.errors.bio" class="modal-input-error mt-2" />
            </el-form-item>
        </el-form>

        <template #footer>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <!-- <el-button @click="closeDialog">Cancel</el-button> -->
                <SubmitButton
                    native-type="button"
                    class="sm:!w-auto sm:min-w-[220px]"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="submitProfile"
                >
                    Create Profile
                </SubmitButton>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
:deep(.create-profile-dialog),
:deep(.create-profile-dialog .el-dialog) {
    border-radius: 20px;
    overflow: hidden;
    --el-color-primary: #6b7280;
    --el-color-primary-light-3: #dbe1e6;
    --el-color-primary-light-5: #dbe1e6;
    --el-input-focus-color: #dbe1e6;
    --el-input-focus-border-color: #dbe1e6;
    --el-border-color-hover: #cdd5dc;
    --el-select-input-focus-border-color: #dbe1e6;
    --el-fill-color-blank: #ffffff;
}

:deep(.create-profile-dialog .el-dialog__header) {
    margin-right: 0;
    padding: 20px 24px 8px;
}

:deep(.create-profile-dialog .el-dialog__body) {
    padding: 12px 24px 8px;
}

:deep(.create-profile-dialog .el-dialog__footer) {
    padding: 8px 24px 24px;
}

:deep(.create-profile-dialog .el-input__wrapper),
:deep(.create-profile-dialog .el-textarea__inner),
:deep(.create-profile-dialog .el-select__wrapper) {
    border-radius: 12px;
    box-shadow: 0 0 0 1px #dbe1e6 inset !important;
    min-height: 48px;
}

:deep(.create-profile-dialog .el-input__wrapper),
:deep(.create-profile-dialog .el-select__wrapper),
:deep(.create-profile-dialog .el-date-editor.el-input__wrapper) {
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
}

:deep(.create-profile-dialog .el-date-editor.el-input) {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    padding: 0 !important;
}

:deep(.create-profile-dialog .el-date-editor.el-input),
:deep(.create-profile-dialog .el-select),
:deep(.create-profile-dialog .el-input) {
    width: 100%;
}

:deep(.create-profile-dialog .el-input__inner),
:deep(.create-profile-dialog .el-select__selected-item),
:deep(.create-profile-dialog .el-textarea__inner) {
    font-size: 14px;
    color: #111827;
}

:deep(.create-profile-dialog .el-form-item__label) {
    padding-bottom: 0.35rem;
    color: #374151;
    font-size: 12px;
    font-weight: 600;
}

.modal-input-error :deep(p) {
    font-size: 0.875rem !important;
    line-height: 1.4;
    font-weight: 600;
}

.modal-input-error {
    width: 100%;
    flex-basis: 100%;
}

:deep(.create-profile-dialog .el-form-item__content) {
    align-items: flex-start;
}

:deep(.create-profile-dialog .el-textarea__inner) {
    min-height: 120px !important;
    padding: 0.85rem 0.95rem;
    line-height: 1.55;
}

:deep(.create-profile-dialog .el-input__wrapper.is-focus),
:deep(.create-profile-dialog .el-input.is-focus .el-input__wrapper),
:deep(.create-profile-dialog .el-input.is-active .el-input__wrapper),
:deep(.create-profile-dialog .el-select__wrapper.is-focused),
:deep(.create-profile-dialog .el-textarea__inner:focus),
:deep(.create-profile-dialog .el-date-editor.is-focus .el-input__wrapper),
:deep(.create-profile-dialog .el-date-editor.is-active .el-input__wrapper),
:deep(.create-profile-dialog .el-date-editor:focus-within .el-input__wrapper) {
    border-color: #dbe1e6 !important;
    box-shadow: 0 0 0 1px #dbe1e6 inset !important;
    outline: none !important;
}

:deep(.create-profile-dialog .el-date-editor .el-input__wrapper),
:deep(.create-profile-dialog .el-date-editor.el-input.is-active .el-input__wrapper),
:deep(.create-profile-dialog .el-date-editor.el-input.is-focus .el-input__wrapper),
:deep(.create-profile-dialog .el-date-editor.is-focus .el-input__wrapper),
:deep(.create-profile-dialog .el-date-editor.is-active .el-input__wrapper) {
    border-color: #dbe1e6 !important;
    box-shadow: 0 0 0 1px #dbe1e6 inset !important;
    outline: none !important;
}

:deep(.create-profile-dialog .el-input__wrapper:focus-within),
:deep(.create-profile-dialog .el-select__wrapper:focus-within),
:deep(.create-profile-dialog .el-date-editor:focus-within),
:deep(.create-profile-dialog .el-date-editor.el-input__wrapper:focus-within) {
    border-color: #dbe1e6 !important;
    box-shadow: 0 0 0 1px #dbe1e6 inset !important;
    outline: none !important;
}

:deep(.create-profile-dialog .el-select__wrapper.is-focused .el-select__caret),
:deep(.create-profile-dialog .el-date-editor.is-focus .el-input__prefix-inner),
:deep(.create-profile-dialog .el-date-editor.is-active .el-input__prefix-inner) {
    color: #9ca3af !important;
}

:deep(.create-profile-dialog .el-input__wrapper:hover),
:deep(.create-profile-dialog .el-select__wrapper:hover),
:deep(.create-profile-dialog .el-textarea__inner:hover) {
    box-shadow: 0 0 0 1px #cdd5dc inset !important;
}
</style>
