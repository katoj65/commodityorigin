<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Plus, Close, Calendar as CalendarIcon, Files, CircleCheck, Loading,
    Star, PriceTag, Sunny, TrendCharts, MoreFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    event: { type: Object, default: null },
    defaultDate: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const isEditing = computed(() => Boolean(props.event));

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

const form = useForm({
    title: '',
    description: '',
    event_date: todayStr(),
    type: '',
    status: 'pending',
    make_task: false,
});

const knownTypeValues = ['', 'task', 'deadline', 'harvest', 'market'];
const otherTypeMode = ref(false);

/* ── Populate the form whenever the dialog opens ────────────────────── */
watch(() => props.modelValue, (open) => {
    if (!open) return;

    form.clearErrors();

    if (props.event) {
        form.title = props.event.title;
        form.description = props.event.description ?? '';
        form.event_date = props.event.event_date;
        form.type = props.event.type ?? '';
        form.status = props.event.status;
        form.make_task = false;
        otherTypeMode.value = form.type !== '' && !knownTypeValues.includes(form.type);
    } else {
        form.reset();
        form.event_date = props.defaultDate || todayStr();
        otherTypeMode.value = false;
    }
});

function selectType(value) {
    otherTypeMode.value = false;
    form.type = value;
}

function selectOtherType() {
    otherTypeMode.value = true;
    if (knownTypeValues.includes(form.type)) {
        form.type = '';
    }
}

function saveEvent() {
    form.clearErrors();

    if (!form.title.trim()) form.setError('title', 'Title is required.');
    if (!form.event_date) form.setError('event_date', 'Date is required.');
    if (form.errors.title || form.errors.event_date) return;

    const onSuccess = () => { dialogVisible.value = false; };

    if (isEditing.value) {
        form.patch(route('calendar.update', props.event.id), { preserveScroll: true, onSuccess });
    } else {
        form.post(route('calendar.store'), { preserveScroll: true, onSuccess });
    }
}

const typeOptions = [
    { value: '', label: 'General', icon: Star, tone: 'muted' },
    { value: 'task', label: 'Task', icon: CircleCheck, tone: 'green' },
    { value: 'deadline', label: 'Deadline', icon: PriceTag, tone: 'red' },
    { value: 'harvest', label: 'Harvest', icon: Sunny, tone: 'amber' },
    { value: 'market', label: 'Market', icon: TrendCharts, tone: 'blue' },
];
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="480px"
        destroy-on-close
        align-center
        :show-close="false"
        class="evt-modal"
    >
        <template #header>
            <div class="evt-modal__head">
                <div class="evt-modal__head-icon">
                    <el-icon :size="18"><CalendarIcon /></el-icon>
                </div>
                <div class="evt-modal__head-text">
                    <div class="evt-modal__eyebrow">{{ isEditing ? 'Edit' : 'Create' }}</div>
                    <div class="evt-modal__title">{{ isEditing ? 'Edit Event' : 'New Event' }}</div>
                </div>
                <button type="button" class="evt-modal__close" aria-label="Close" @click="dialogVisible = false">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="evt-modal__body">
            <div class="evt-field">
                <label class="evt-field__label">Title</label>
                <el-input v-model="form.title" placeholder="e.g. Export deadline for Lot #42" class="evt-input" :class="{ 'evt-input--error': form.errors.title }" />
                <span v-if="form.errors.title" class="evt-field__error">{{ form.errors.title }}</span>
            </div>

            <div class="evt-field">
                <label class="evt-field__label"><el-icon :size="12"><Files /></el-icon> Description</label>
                <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Optional notes" class="evt-input" />
            </div>

            <div class="evt-field">
                <label class="evt-field__label">Date</label>
                <el-date-picker v-model="form.event_date" type="date" value-format="YYYY-MM-DD" style="width:100%" class="evt-input" :class="{ 'evt-input--error': form.errors.event_date }" />
                <span v-if="form.errors.event_date" class="evt-field__error">{{ form.errors.event_date }}</span>
            </div>

            <div class="evt-field mt-3">
                <label class="evt-field__label">Type</label>
                <div class="evt-type-grid">
                    <button
                        v-for="opt in typeOptions"
                        :key="opt.value || 'general'"
                        type="button"
                        class="evt-type-pill"
                        :class="[`evt-type-pill--${opt.tone}`, { 'evt-type-pill--active': !otherTypeMode && form.type === opt.value }]"
                        @click="selectType(opt.value)"
                    >
                        <el-icon :size="15"><component :is="opt.icon" /></el-icon>
                        <span>{{ opt.label }}</span>
                    </button>
                    <button
                        type="button"
                        class="evt-type-pill evt-type-pill--muted"
                        :class="{ 'evt-type-pill--active': otherTypeMode }"
                        @click="selectOtherType"
                    >
                        <el-icon :size="15"><MoreFilled /></el-icon>
                        <span>Other</span>
                    </button>
                </div>

                <el-input
                    v-if="otherTypeMode"
                    v-model="form.type"
                    placeholder="e.g. Compliance, Inspection, Shipping"
                    class="evt-input mt-2"
                    maxlength="255"
                />
            </div>

            <div v-if="isEditing" class="evt-field">
                <label class="evt-field__label">Status</label>
                <div class="evt-status-toggle">
                    <button
                        type="button"
                        class="evt-status-toggle__btn"
                        :class="{ 'evt-status-toggle__btn--active': form.status === 'pending' }"
                        @click="form.status = 'pending'"
                    >
                        <el-icon :size="13"><Loading /></el-icon> Pending
                    </button>
                    <button
                        type="button"
                        class="evt-status-toggle__btn evt-status-toggle__btn--green"
                        :class="{ 'evt-status-toggle__btn--active': form.status === 'completed' }"
                        @click="form.status = 'completed'"
                    >
                        <el-icon :size="13"><CircleCheck /></el-icon> Completed
                    </button>
                </div>
            </div>

            <div v-if="!isEditing" class="evt-field evt-field--task">
                <div class="evt-switch-row">
                    <div class="evt-switch-row__text">
                        <label class="evt-field__label"><el-icon :size="12"><CircleCheck /></el-icon> Task Reminder</label>
                        <span class="evt-field__hint">Also add this to your tasks. You'll get a decision-support notification when it's due.</span>
                    </div>
                    <el-switch v-model="form.make_task" class="evt-switch" />
                </div>
            </div>
        </div>

        <template #footer>
            <div class="evt-modal__footer">
                <button type="button" class="evt-btn-outline" @click="dialogVisible = false">Cancel</button>
                <button type="button" class="evt-btn-primary" :disabled="form.processing" @click="saveEvent">
                    <el-icon v-if="!form.processing"><Plus /></el-icon>
                    {{ form.processing ? 'Saving…' : isEditing ? 'Save Changes' : 'Create Event' }}
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
.el-dialog.evt-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

.el-dialog.evt-modal .el-dialog__header {
    padding: 0;
    margin: 0;
}

.el-dialog.evt-modal .el-dialog__body {
    padding: 0;
}

.el-dialog.evt-modal .el-dialog__footer {
    padding: 0;
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.evt-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.evt-modal__head-icon {
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

.evt-modal__head-text {
    flex: 1;
    min-width: 0;
}

.evt-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.evt-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.evt-modal__close {
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

.evt-modal__close:hover {
    background: #e5e7eb;
    color: #111827;
}

.evt-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.evt-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.evt-field__label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9375rem;
    font-weight: 400;
    color: #374151;
}

.evt-field--task {
    margin-top: 4px;
    padding-top: 14px;
    border-top: 1px solid #f3f4f6;
}

.evt-field__hint {
    font-size: 0.75rem;
    font-weight: 400;
    color: #6b7280;
    line-height: 1.4;
}

.evt-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.evt-input--error :deep(.el-input__wrapper),
.evt-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

/* ── Task-reminder switch ────────────────────────────────────────────── */
.evt-switch-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.evt-switch-row__text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.evt-switch-row .evt-field__label {
    font-weight: 600;
    color: #111827;
}

.evt-switch :deep(.el-switch__core) {
    background: #d1d5db;
    border-color: #d1d5db;
}

.evt-switch.is-checked :deep(.el-switch__core) {
    background: #004532;
    border-color: #004532;
}

.evt-input :deep(.el-input__wrapper),
.evt-input :deep(.el-textarea__inner) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.evt-input :deep(.el-input__wrapper:hover),
.evt-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.evt-input :deep(.el-input__wrapper.is-focus),
.evt-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.evt-type-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.evt-type-pill {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 8px;
    border-radius: 10px;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s;
}

.evt-type-pill:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.evt-type-pill--active.evt-type-pill--muted { background: #f3f4f6; border-color: #9ca3af; color: #374151; }
.evt-type-pill--active.evt-type-pill--green { background: #dcfce7; border-color: #16a34a; color: #166534; }
.evt-type-pill--active.evt-type-pill--red { background: #fee2e2; border-color: #dc2626; color: #991b1b; }
.evt-type-pill--active.evt-type-pill--amber { background: #fef3c7; border-color: #d97706; color: #92400e; }
.evt-type-pill--active.evt-type-pill--blue { background: #dbeafe; border-color: #2563eb; color: #1e40af; }

.evt-status-toggle {
    display: flex;
    gap: 8px;
}

.evt-status-toggle__btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 12px;
    border-radius: 10px;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s;
}

.evt-status-toggle__btn:hover {
    background: #f9fafb;
}

.evt-status-toggle__btn--active {
    background: #fef3c7;
    border-color: #d97706;
    color: #92400e;
}

.evt-status-toggle__btn--green.evt-status-toggle__btn--active {
    background: #dcfce7;
    border-color: #16a34a;
    color: #166534;
}

.evt-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.evt-btn-primary {
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

.evt-btn-primary:hover { opacity: 0.9; }
.evt-btn-primary:disabled { opacity: 0.6; cursor: default; }

.evt-btn-outline {
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

.evt-btn-outline:hover { background: #f8fafc; }
</style>
