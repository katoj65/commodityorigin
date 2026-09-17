<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Key } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({ title: 'Password Updated', message: 'Your password was changed successfully.', type: 'success', duration: 3200, offset: 84 });
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(440px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="cpw-modal"
    >
        <template #header>
            <div class="cpw-modal__head">
                <div class="cpw-modal__head-icon">
                    <el-icon :size="18"><Key /></el-icon>
                </div>
                <div class="cpw-modal__head-text">
                    <div class="cpw-modal__eyebrow">Security</div>
                    <div class="cpw-modal__title">Change Password</div>
                </div>
                <button type="button" class="cpw-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="cpw-modal__body">
            <div class="cpw-field">
                <label class="cpw-field__label">Current Password</label>
                <el-input v-model="form.current_password" type="password" show-password autocomplete="current-password" class="cpw-input" :class="{ 'cpw-input--error': form.errors.current_password }" />
                <span v-if="form.errors.current_password" class="cpw-field__error">{{ form.errors.current_password }}</span>
            </div>
            <div class="cpw-field">
                <label class="cpw-field__label">New Password</label>
                <el-input v-model="form.password" type="password" show-password autocomplete="new-password" class="cpw-input" :class="{ 'cpw-input--error': form.errors.password }" />
                <span v-if="form.errors.password" class="cpw-field__error">{{ form.errors.password }}</span>
            </div>
            <div class="cpw-field">
                <label class="cpw-field__label">Confirm New Password</label>
                <el-input v-model="form.password_confirmation" type="password" show-password autocomplete="new-password" class="cpw-input" :class="{ 'cpw-input--error': form.errors.password_confirmation }" />
                <span v-if="form.errors.password_confirmation" class="cpw-field__error">{{ form.errors.password_confirmation }}</span>
            </div>
        </div>

        <template #footer>
            <div class="cpw-modal__footer">
                <button type="button" class="cpw-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Password' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.cpw-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.cpw-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.cpw-modal .el-dialog__body { padding: 0; }
.el-dialog.cpw-modal .el-dialog__footer { padding: 0; }

.dialog-fade-fast-enter-active { animation: modal-fade-in .12s; }
.dialog-fade-fast-enter-active .el-overlay-dialog { animation: dialog-fade-in .12s; }
.dialog-fade-fast-leave-active { animation: modal-fade-out .12s; }
.dialog-fade-fast-leave-active .el-overlay-dialog { animation: dialog-fade-out .12s; }
</style>

<style scoped>
.cpw-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.cpw-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cpw-modal__head-text { flex: 1; min-width: 0; }
.cpw-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6F7677; margin-bottom: 1px; }
.cpw-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.cpw-modal__close { width: 28px; height: 28px; border-radius: 6px; border: none; background: #F1F2F3; color: #4B5457; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.12s; }
.cpw-modal__close:hover { background: #E5E7EB; color: #121516; }

.cpw-modal__body { padding: 22px 24px; display: flex; flex-direction: column; gap: 16px; }
.cpw-field { display: flex; flex-direction: column; gap: 6px; }
.cpw-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.cpw-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.cpw-input :deep(.el-input__wrapper) { border-radius: 6px; }
.cpw-input--error :deep(.el-input__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.cpw-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #F5F6F7; border-top: 1px solid #E5E7EB; }
.cpw-btn-primary {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 16px; background: #000000; border: 1px solid transparent;
    color: #fff; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s ease;
}
.cpw-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.cpw-btn-primary:disabled { opacity: 0.5; cursor: default; }
</style>
