<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { CircleCheckFilled, Close } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    farmId: { type: [Number, String], required: true },
    practiceOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
        practice: '',
        description: '',
    };
}

const form = useForm(emptyForm());

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.post(route('farm.sustainability-practices.store', props.farmId), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({ title: 'Practice Recorded', message: 'The sustainability practice was added to this farm.', type: 'success', duration: 3200, offset: 84 });
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(480px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="afsp-modal"
    >
        <template #header>
            <div class="afsp-modal__head">
                <div class="afsp-modal__head-icon">
                    <el-icon :size="18"><CircleCheckFilled /></el-icon>
                </div>
                <div class="afsp-modal__head-text">
                    <div class="afsp-modal__eyebrow">Farm Profile</div>
                    <div class="afsp-modal__title">Add Sustainability Practice</div>
                </div>
                <button type="button" class="afsp-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="afsp-modal__body">
            <div class="afsp-field">
                <label class="afsp-field__label">Practice</label>
                <el-select v-model="form.practice" placeholder="Select a practice" filterable class="afsp-input w-100" :class="{ 'afsp-input--error': form.errors.practice }">
                    <el-option v-for="option in practiceOptions" :key="option.slug" :label="option.name" :value="option.slug" />
                </el-select>
                <span v-if="form.errors.practice" class="afsp-field__error">{{ form.errors.practice }}</span>
            </div>

            <div class="afsp-field">
                <label class="afsp-field__label">Description <small>(optional)</small></label>
                <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Add any detail worth recording — how it's applied, since when, etc." class="afsp-input" :class="{ 'afsp-input--error': form.errors.description }" />
                <span v-if="form.errors.description" class="afsp-field__error">{{ form.errors.description }}</span>
            </div>
        </div>

        <template #footer>
            <div class="afsp-modal__footer">
                <button type="button" class="afsp-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="afsp-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Add Practice' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.afsp-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.afsp-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.afsp-modal .el-dialog__body { padding: 0; }
.el-dialog.afsp-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.afsp-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.afsp-modal__head-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    background: #F1F2F3;
    color: #121516;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.afsp-modal__head-text { flex: 1; min-width: 0; }
.afsp-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.afsp-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.afsp-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: #F1F2F3;
    color: #4B5457;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}
.afsp-modal__close:hover { background: #E5E7EB; color: #121516; }

.afsp-modal__body { padding: 22px 24px; display: flex; flex-direction: column; gap: 16px; }
.afsp-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }

.afsp-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.afsp-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.afsp-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.afsp-input { width: 100%; }
.afsp-input :deep(.el-input__wrapper),
.afsp-input :deep(.el-select__wrapper),
.afsp-input :deep(.el-textarea__inner) { border-radius: 6px; }
.afsp-input--error :deep(.el-input__wrapper),
.afsp-input--error :deep(.el-select__wrapper),
.afsp-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.afsp-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.afsp-btn-primary {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 16px;
    background: #000000;
    border: 1px solid transparent;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.afsp-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.afsp-btn-primary:disabled { opacity: 0.5; cursor: default; }
.afsp-btn-outline {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 16px;
    background: #fff;
    border: 1px solid #E5E7EB;
    color: #121516;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
}
.afsp-btn-outline:hover { background: #F5F6F7; }
</style>
