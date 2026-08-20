<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Clock, Close } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    item: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({ status: '', notes: '' });

watch(() => props.modelValue, (open) => {
    if (!open || !props.item) return;
    form.defaults({ status: props.item.status, notes: '' });
    form.reset();
    form.clearErrors();
});

const logs = computed(() => props.item?.status_logs ?? []);

function statusLabel(status) {
    if (!status) return '—';
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function timelineType(status) {
    if (status === 'sold' || status === 'delivered') return 'success';
    if (status === 'archived') return 'info';
    if (status === 'reserved' || status === 'shipped') return 'warning';
    return 'primary';
}

function formatDate(value) {
    if (!value) return '';
    return new Date(value.replace(' ', 'T')).toLocaleString(undefined, {
        month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
    });
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (!props.item || form.status === props.item.status) return;

    form.patch(route('store.items.status', props.item.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({
                title: 'Status Updated',
                message: `Item moved to "${statusLabel(form.status)}".`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(560px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="sis-modal"
    >
        <template #header>
            <div class="sis-modal__head">
                <div class="sis-modal__head-icon">
                    <el-icon :size="18"><Clock /></el-icon>
                </div>
                <div class="sis-modal__head-text">
                    <div class="sis-modal__eyebrow">Traceability</div>
                    <div class="sis-modal__title">{{ item?.name }}</div>
                </div>
                <button type="button" class="sis-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="sis-modal__body">
            <div class="sis-change">
                <label class="sis-field__label">Move to status</label>
                <div class="sis-change__row">
                    <el-select v-model="form.status" placeholder="Select status" class="sis-input">
                        <el-option v-for="opt in statusOptions" :key="opt" :label="statusLabel(opt)" :value="opt" />
                    </el-select>
                    <button type="button" class="sis-btn-primary" :disabled="form.processing || form.status === item?.status" @click="submit">
                        {{ form.processing ? 'Saving…' : 'Update' }}
                    </button>
                </div>
                <el-input
                    v-model="form.notes"
                    type="textarea"
                    :rows="2"
                    placeholder="Optional note about this change (e.g. courier, buyer, reason)"
                    class="sis-input"
                    style="margin-top: 10px;"
                />
                <span v-if="form.errors.status" class="sis-field__error">{{ form.errors.status }}</span>
            </div>

            <div class="sis-divider">
                <span>History</span>
            </div>

            <el-timeline v-if="logs.length" class="sis-timeline">
                <el-timeline-item
                    v-for="log in logs"
                    :key="log.id"
                    :type="timelineType(log.to_status)"
                    :timestamp="formatDate(log.created_at)"
                    placement="top"
                >
                    <div class="sis-log">
                        <div class="sis-log__title">
                            <span v-if="log.from_status">{{ statusLabel(log.from_status) }} → </span>{{ statusLabel(log.to_status) }}
                        </div>
                        <div v-if="log.changed_by" class="sis-log__by">by {{ log.changed_by }}</div>
                        <div v-if="log.notes" class="sis-log__notes">{{ log.notes }}</div>
                    </div>
                </el-timeline-item>
            </el-timeline>
            <p v-else class="sis-empty">No status history yet.</p>
        </div>

        <template #footer>
            <div class="sis-modal__footer">
                <button type="button" class="sis-btn-outline" @click="closeDialog">Close</button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.sis-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}
.el-dialog.sis-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.sis-modal .el-dialog__body { padding: 0; }
.el-dialog.sis-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.sis-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}
.sis-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(20, 92, 66, 0.08);
    color: #145c42;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sis-modal__head-text { flex: 1; min-width: 0; }
.sis-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #145c42;
    margin-bottom: 1px;
}
.sis-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.sis-modal__close {
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
.sis-modal__close:hover { background: #e5e7eb; color: #111827; }

.sis-modal__body { padding: 20px 24px 6px; max-height: 65vh; overflow-y: auto; }

.sis-field__label { font-size: 0.75rem; font-weight: 700; color: #111827; display: block; margin-bottom: 6px; }
.sis-field__error { font-size: 0.75rem; font-weight: 600; color: #dc2626; line-height: 1.4; display: block; margin-top: 6px; }

.sis-change__row { display: flex; gap: 8px; }
.sis-input { width: 100%; }

.sis-btn-primary {
    background: linear-gradient(135deg, #145c42, #0d3d2c);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 0 16px;
    cursor: pointer;
    transition: opacity 0.15s ease;
    flex-shrink: 0;
}
.sis-btn-primary:hover { opacity: 0.9; }
.sis-btn-primary:disabled { opacity: 0.5; cursor: default; }

.sis-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 20px 0 12px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
}
.sis-divider::before,
.sis-divider::after { content: ''; flex: 1; height: 1px; background: #f3f4f6; }

.sis-timeline { padding-left: 4px; }
.sis-log__title { font-size: 0.8125rem; font-weight: 700; color: #111827; }
.sis-log__by { font-size: 0.75rem; color: #6b7280; margin-top: 1px; }
.sis-log__notes { font-size: 0.75rem; color: #6b7280; margin-top: 4px; line-height: 1.5; }

.sis-empty { font-size: 0.8125rem; color: #9ca3af; text-align: center; padding: 1.5rem 0; }

.sis-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}
.sis-btn-outline {
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
.sis-btn-outline:hover { background: #f8fafc; }
</style>
