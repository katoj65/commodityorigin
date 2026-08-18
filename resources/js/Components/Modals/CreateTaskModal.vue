<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Plus, Close, List, Files, Calendar as CalendarLine } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    defaultDate: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

const form = useForm({
    title: '',
    description: '',
    task_date: todayStr(),
    add_to_calendar: false,
});

/* ── Reset the form whenever the dialog opens ───────────────────────── */
watch(() => props.modelValue, (open) => {
    if (!open) return;

    form.reset();
    form.clearErrors();
    form.task_date = props.defaultDate || todayStr();
});

function saveTask() {
    form.clearErrors();

    if (!form.title.trim()) form.setError('title', 'Title is required.');
    if (!form.task_date) form.setError('task_date', 'Date is required.');
    if (form.errors.title || form.errors.task_date) return;

    form.post(route('task.store'), {
        preserveScroll: true,
        onSuccess: () => { dialogVisible.value = false; },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="440px"
        destroy-on-close
        align-center
        :show-close="false"
        class="tsk-modal"
    >
        <template #header>
            <div class="tsk-modal__head">
                <div class="tsk-modal__head-icon">
                    <el-icon :size="18"><List /></el-icon>
                </div>
                <div class="tsk-modal__head-text">
                    <div class="tsk-modal__eyebrow">Create</div>
                    <div class="tsk-modal__title">New Task</div>
                </div>
                <button type="button" class="tsk-modal__close" aria-label="Close" @click="dialogVisible = false">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="tsk-modal__body">
            <div class="tsk-field">
                <label class="tsk-field__label">Title</label>
                <el-input v-model="form.title" placeholder="e.g. Review Q3 pricing" class="tsk-input" :class="{ 'tsk-input--error': form.errors.title }" />
                <span v-if="form.errors.title" class="tsk-field__error">{{ form.errors.title }}</span>
            </div>

            <div class="tsk-field">
                <label class="tsk-field__label"><el-icon :size="12"><Files /></el-icon> Description</label>
                <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Optional notes" class="tsk-input" />
            </div>

            <div class="tsk-field">
                <label class="tsk-field__label">Due Date</label>
                <el-date-picker v-model="form.task_date" type="date" value-format="YYYY-MM-DD" style="width:100%" class="tsk-input" :class="{ 'tsk-input--error': form.errors.task_date }" />
                <span v-if="form.errors.task_date" class="tsk-field__error">{{ form.errors.task_date }}</span>
            </div>

            <div class="tsk-field tsk-field--calendar">
                <div class="tsk-switch-row">
                    <div class="tsk-switch-row__text">
                        <label class="tsk-field__label"><el-icon :size="12"><CalendarLine /></el-icon> Add to Calendar</label>
                        <span class="tsk-field__hint">Also create a matching event on your calendar for this due date.</span>
                    </div>
                    <el-switch v-model="form.add_to_calendar" class="tsk-switch" />
                </div>
            </div>
        </div>

        <template #footer>
            <div class="tsk-modal__footer">
                <button type="button" class="tsk-btn-outline" @click="dialogVisible = false">Cancel</button>
                <button type="button" class="tsk-btn-primary" :disabled="form.processing" @click="saveTask">
                    <el-icon v-if="!form.processing"><Plus /></el-icon>
                    {{ form.processing ? 'Saving…' : 'Create Task' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so it never carries this SFC's
   scope attribute — a scoped (or :deep()) selector can never reach it.
   Class names are specific enough to avoid collisions. */
.el-dialog.tsk-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

.el-dialog.tsk-modal .el-dialog__header {
    padding: 0;
    margin: 0;
}

.el-dialog.tsk-modal .el-dialog__body {
    padding: 0;
}

.el-dialog.tsk-modal .el-dialog__footer {
    padding: 0;
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.tsk-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.tsk-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tsk-modal__head-text {
    flex: 1;
    min-width: 0;
}

.tsk-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.tsk-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.tsk-modal__close {
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

.tsk-modal__close:hover {
    background: #e5e7eb;
    color: #111827;
}

.tsk-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.tsk-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tsk-field__label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9375rem;
    font-weight: 400;
    color: #374151;
}

.tsk-field--calendar {
    margin-top: 4px;
    padding-top: 14px;
    border-top: 1px solid #f3f4f6;
}

.tsk-field__hint {
    font-size: 0.75rem;
    font-weight: 400;
    color: #6b7280;
    line-height: 1.4;
}

.tsk-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.tsk-input--error :deep(.el-input__wrapper),
.tsk-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.tsk-switch-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.tsk-switch-row__text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.tsk-switch-row .tsk-field__label {
    font-weight: 600;
    color: #111827;
}

.tsk-switch :deep(.el-switch__core) {
    background: #d1d5db;
    border-color: #d1d5db;
}

.tsk-switch.is-checked :deep(.el-switch__core) {
    background: #004532;
    border-color: #004532;
}

.tsk-input :deep(.el-input__wrapper),
.tsk-input :deep(.el-textarea__inner) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.tsk-input :deep(.el-input__wrapper:hover),
.tsk-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.tsk-input :deep(.el-input__wrapper.is-focus),
.tsk-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.tsk-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.tsk-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.tsk-btn-primary:hover { opacity: 0.9; }
.tsk-btn-primary:disabled { opacity: 0.6; cursor: default; }

.tsk-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.tsk-btn-outline:hover { background: #f8fafc; }
</style>
