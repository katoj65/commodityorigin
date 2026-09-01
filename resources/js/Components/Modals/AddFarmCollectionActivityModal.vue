<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Clock, Close } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    collectionId: { type: [Number, String], required: true },
    activityOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
        event: '',
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
    form.post(route('farm-collection.activities.store', props.collectionId), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({ title: 'Activity Recorded', message: 'The farm collection activity was added to the log.', type: 'success', duration: 3200, offset: 84 });
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
        class="afca-modal"
    >
        <template #header>
            <div class="afca-modal__head">
                <div class="afca-modal__head-icon">
                    <el-icon :size="18"><Clock /></el-icon>
                </div>
                <div class="afca-modal__head-text">
                    <div class="afca-modal__eyebrow">Farm Collection</div>
                    <div class="afca-modal__title">Add Activity</div>
                </div>
                <button type="button" class="afca-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="afca-modal__body">
            <div class="afca-field">
                <label class="afca-field__label">Event</label>
                <el-select v-model="form.event" placeholder="Select an event" filterable class="afca-input w-100" :class="{ 'afca-input--error': form.errors.event }">
                    <el-option v-for="option in activityOptions" :key="option.slug" :label="option.name" :value="option.slug" />
                </el-select>
                <span v-if="form.errors.event" class="afca-field__error">{{ form.errors.event }}</span>
            </div>

            <div class="afca-field">
                <label class="afca-field__label">Description <small>(optional)</small></label>
                <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Add any detail worth recording — moisture reading, warehouse, inspector, etc." class="afca-input" :class="{ 'afca-input--error': form.errors.description }" />
                <span v-if="form.errors.description" class="afca-field__error">{{ form.errors.description }}</span>
            </div>
        </div>

        <template #footer>
            <div class="afca-modal__footer">
                <button type="button" class="afca-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="afca-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Record Activity' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.afca-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.afca-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.afca-modal .el-dialog__body { padding: 0; }
.el-dialog.afca-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.afca-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.afca-modal__head-icon {
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
.afca-modal__head-text { flex: 1; min-width: 0; }
.afca-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.afca-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.afca-modal__close {
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
.afca-modal__close:hover { background: #E5E7EB; color: #121516; }

.afca-modal__body { padding: 22px 24px; display: flex; flex-direction: column; gap: 16px; }
.afca-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }

.afca-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.afca-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.afca-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.afca-input { width: 100%; }
.afca-input :deep(.el-input__wrapper),
.afca-input :deep(.el-select__wrapper),
.afca-input :deep(.el-textarea__inner) { border-radius: 6px; }
.afca-input--error :deep(.el-input__wrapper),
.afca-input--error :deep(.el-select__wrapper),
.afca-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.afca-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.afca-btn-primary {
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
.afca-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.afca-btn-primary:disabled { opacity: 0.5; cursor: default; }
.afca-btn-outline {
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
.afca-btn-outline:hover { background: #F5F6F7; }
</style>
