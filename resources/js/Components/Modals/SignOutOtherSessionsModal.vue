<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Monitor } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'success']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({ password: '' });

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({ title: 'Signed Out', message: 'All other browser sessions have been signed out.', type: 'success', duration: 3200, offset: 84 });
            emit('success');
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(420px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="sos-modal"
    >
        <template #header>
            <div class="sos-modal__head">
                <div class="sos-modal__head-icon">
                    <el-icon :size="18"><Monitor /></el-icon>
                </div>
                <div class="sos-modal__head-text">
                    <div class="sos-modal__eyebrow">Security</div>
                    <div class="sos-modal__title">Sign Out Other Devices</div>
                </div>
                <button type="button" class="sos-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="sos-modal__body">
            <p class="sos-modal__message">This signs your account out on every other browser and device, keeping this one signed in. Enter your password to confirm.</p>
            <div class="sos-field">
                <label class="sos-field__label">Password</label>
                <el-input v-model="form.password" type="password" show-password autocomplete="current-password" class="sos-input" :class="{ 'sos-input--error': form.errors.password }" @keyup.enter="submit" />
                <span v-if="form.errors.password" class="sos-field__error">{{ form.errors.password }}</span>
            </div>
        </div>

        <template #footer>
            <div class="sos-modal__footer">
                <button type="button" class="sos-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Signing Out…' : 'Sign Out Other Devices' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.sos-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.sos-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.sos-modal .el-dialog__body { padding: 0; }
.el-dialog.sos-modal .el-dialog__footer { padding: 0; }

.dialog-fade-fast-enter-active { animation: modal-fade-in .12s; }
.dialog-fade-fast-enter-active .el-overlay-dialog { animation: dialog-fade-in .12s; }
.dialog-fade-fast-leave-active { animation: modal-fade-out .12s; }
.dialog-fade-fast-leave-active .el-overlay-dialog { animation: dialog-fade-out .12s; }
</style>

<style scoped>
.sos-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.sos-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #FEEDED; color: #C6413A; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sos-modal__head-text { flex: 1; min-width: 0; }
.sos-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6F7677; margin-bottom: 1px; }
.sos-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.sos-modal__close { width: 28px; height: 28px; border-radius: 6px; border: none; background: #F1F2F3; color: #4B5457; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.12s; }
.sos-modal__close:hover { background: #E5E7EB; color: #121516; }

.sos-modal__body { padding: 22px 24px; display: flex; flex-direction: column; gap: 16px; }
.sos-modal__message { margin: 0; font-size: 13px; line-height: 1.55; color: #4B5457; }
.sos-field { display: flex; flex-direction: column; gap: 6px; }
.sos-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.sos-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.sos-input :deep(.el-input__wrapper) { border-radius: 6px; }
.sos-input--error :deep(.el-input__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.sos-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #F5F6F7; border-top: 1px solid #E5E7EB; }
.sos-btn-primary {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 16px; background: #C6413A; border: 1px solid transparent;
    color: #fff; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s ease;
}
.sos-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.sos-btn-primary:disabled { opacity: 0.5; cursor: default; }
</style>
