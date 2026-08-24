<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Shop } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    rejected: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({ email: '', password: '' });

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.post(route('store.save'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({
                title: 'Store Requested',
                message: 'An admin will review your store before you can open it.',
                type: 'success',
                duration: 4000,
                offset: 84,
            });
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
        class="rsd-modal"
    >
        <template #header>
            <div class="rsd-modal__head">
                <div class="rsd-modal__head-icon">
                    <el-icon :size="18"><Shop /></el-icon>
                </div>
                <div class="rsd-modal__head-text">
                    <div class="rsd-modal__eyebrow">Store</div>
                    <div class="rsd-modal__title">{{ rejected ? 'Resubmit for Verification' : 'Request Your Store' }}</div>
                </div>
                <button type="button" class="rsd-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="rsd-modal__body">
            <p class="rsd-intro">Confirm it's you by re-entering your account email and password.</p>

            <div class="rsd-field">
                <label class="rsd-field__label">Email</label>
                <el-input v-model="form.email" type="email" placeholder="you@example.com" class="rsd-input" :class="{ 'rsd-input--error': form.errors.email }" />
                <span v-if="form.errors.email" class="rsd-field__error">{{ form.errors.email }}</span>
            </div>

            <div class="rsd-field">
                <label class="rsd-field__label">Password</label>
                <el-input v-model="form.password" type="password" show-password placeholder="Your account password" class="rsd-input" :class="{ 'rsd-input--error': form.errors.password }" />
                <span v-if="form.errors.password" class="rsd-field__error">{{ form.errors.password }}</span>
            </div>
        </div>

        <template #footer>
            <div class="rsd-modal__footer">
                <button type="button" class="rsd-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="rsd-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Submitting…' : 'Confirm & Request' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.rsd-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}
.el-dialog.rsd-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.rsd-modal .el-dialog__body { padding: 0; }
.el-dialog.rsd-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.rsd-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}
.rsd-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(39, 19, 16, 0.08);
    color: #271310;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.rsd-modal__head-text { flex: 1; min-width: 0; }
.rsd-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #271310;
    margin-bottom: 1px;
}
.rsd-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -0.01em; }
.rsd-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}
.rsd-modal__close:hover { background: #e5e7eb; color: #111827; }

.rsd-modal__body { padding: 20px 24px 6px; }
.rsd-intro { font-size: 0.8125rem; color: #6b7280; line-height: 1.5; margin: 0 0 18px; }

.rsd-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.rsd-field__label { font-size: 0.75rem; font-weight: 700; color: #111827; }
.rsd-field__error { font-size: 0.75rem; font-weight: 600; color: #dc2626; line-height: 1.4; }

.rsd-input { width: 100%; }
.rsd-input--error :deep(.el-input__wrapper) { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

.rsd-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}
.rsd-btn-primary {
    background: #271310;
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.rsd-btn-primary:hover { opacity: 0.9; }
.rsd-btn-primary:disabled { opacity: 0.6; cursor: default; }
.rsd-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.rsd-btn-outline:hover { background: #f8fafc; }
</style>
