<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Close, Delete, Plus, Tickets } from '@element-plus/icons-vue';

const props = defineProps({
    requests: { type: Array, default: () => [] },
    cropTypes: { type: Array, default: () => [] },
    grades: { type: Array, default: () => [] },
});

const fmt = (value, digits = 2) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
};

const statusTone = (status) => {
    switch (status) {
        case 'approved': return 'is-good';
        case 'fulfilled': return 'is-info';
        case 'rejected': return 'is-bad';
        default: return 'is-warn';
    }
};

const createOpen = ref(false);
const deleteOpen = ref(false);
const pendingDelete = ref(null);

const form = useForm({
    crop_type: '',
    variety: '',
    grade: '',
    amount: '',
    quantity: '',
    notes: '',
});

function openCreate() {
    form.reset();
    form.clearErrors();
    createOpen.value = true;
}

function submit() {
    form.post(route('rfq.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createOpen.value = false;
        },
    });
}

function requestDelete(item) {
    pendingDelete.value = item;
    deleteOpen.value = true;
}

function confirmDelete() {
    if (!pendingDelete.value) return;
    router.delete(route('rfq.destroy', pendingDelete.value.id), { preserveScroll: true });
    pendingDelete.value = null;
}
</script>

<template>
    <DesignPreviewLayout title="Request for Quote">
        <Head title="Request for Quote" />

        <div class="rfq-page">
            <div class="rfq-page__header">
                <div>
                    <h1 class="rfq-page__title">Request for Quote</h1>
                    <p class="rfq-page__subtitle">Submit and track requests for coffee quotes.</p>
                </div>
                <button type="button" class="rfq-add-btn" @click="openCreate">
                    <el-icon><Plus /></el-icon> New RFQ
                </button>
            </div>

            <section class="rfq-card">
                <div v-if="requests.length" class="rfq-table-wrap">
                    <table class="rfq-table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Grade</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in requests" :key="item.id">
                                <td>
                                    <span class="rfq-table__main">{{ item.crop_type }}</span>
                                    <span v-if="item.variety" class="rfq-table__sub">{{ item.variety }}</span>
                                </td>
                                <td>{{ item.grade || '—' }}</td>
                                <td>{{ fmt(item.quantity) }}</td>
                                <td>{{ item.amount ? fmt(item.amount) : '—' }}</td>
                                <td><span class="rfq-status" :class="statusTone(item.status)">{{ item.status }}</span></td>
                                <td>{{ item.user?.name || '—' }}</td>
                                <td class="rfq-table__actions">
                                    <button type="button" class="rfq-delete-btn" title="Delete" @click="requestDelete(item)">
                                        <el-icon><Delete /></el-icon>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="rfq-empty">No requests for quote yet.</p>
            </section>
        </div>
        <el-dialog
            v-model="createOpen"
            width="min(520px, calc(100vw - 2rem))"
            align-center
            :close-on-click-modal="false"
            :show-close="false"
            class="rfq-modal"
        >
            <template #header>
                <div class="rfq-modal__head">
                    <div class="rfq-modal__head-icon">
                        <el-icon :size="18"><Tickets /></el-icon>
                    </div>
                    <div class="rfq-modal__head-text">
                        <div class="rfq-modal__eyebrow">Trade</div>
                        <div class="rfq-modal__title">New Request for Quote</div>
                    </div>
                    <button type="button" class="rfq-modal__close" aria-label="Close" @click="createOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="rfq-modal__body">
                <div class="rfq-grid">
                    <div class="rfq-field rfq-field--span2">
                        <label class="rfq-field__label">Crop Type</label>
                        <el-select v-model="form.crop_type" placeholder="Select crop type" filterable class="rfq-input" :class="{ 'rfq-input--error': form.errors.crop_type }">
                            <el-option v-for="crop in cropTypes" :key="crop" :label="crop" :value="crop" />
                        </el-select>
                        <span v-if="form.errors.crop_type" class="rfq-field__error">{{ form.errors.crop_type }}</span>
                    </div>
                    <div class="rfq-field">
                        <label class="rfq-field__label">Variety <small>(optional)</small></label>
                        <el-input v-model="form.variety" placeholder="e.g. SL14" class="rfq-input" :class="{ 'rfq-input--error': form.errors.variety }" />
                        <span v-if="form.errors.variety" class="rfq-field__error">{{ form.errors.variety }}</span>
                    </div>
                    <div class="rfq-field">
                        <label class="rfq-field__label">Grade</label>
                        <el-select v-model="form.grade" placeholder="Select grade" filterable class="rfq-input" :class="{ 'rfq-input--error': form.errors.grade }">
                            <el-option v-for="grade in grades" :key="grade" :label="grade" :value="grade" />
                        </el-select>
                        <span v-if="form.errors.grade" class="rfq-field__error">{{ form.errors.grade }}</span>
                    </div>
                    <div class="rfq-field">
                        <label class="rfq-field__label">Quantity</label>
                        <el-input-number v-model="form.quantity" :min="0.01" :precision="2" class="rfq-input w-100" :class="{ 'rfq-input--error': form.errors.quantity }" />
                        <span v-if="form.errors.quantity" class="rfq-field__error">{{ form.errors.quantity }}</span>
                    </div>
                    <div class="rfq-field">
                        <label class="rfq-field__label">Amount <small>(optional)</small></label>
                        <el-input-number v-model="form.amount" :min="0" :precision="2" class="rfq-input w-100" :class="{ 'rfq-input--error': form.errors.amount }" />
                        <span v-if="form.errors.amount" class="rfq-field__error">{{ form.errors.amount }}</span>
                    </div>
                    <div class="rfq-field rfq-field--span2">
                        <label class="rfq-field__label">Notes <small>(optional)</small></label>
                        <el-input v-model="form.notes" type="textarea" :rows="3" class="rfq-input" :class="{ 'rfq-input--error': form.errors.notes }" />
                        <span v-if="form.errors.notes" class="rfq-field__error">{{ form.errors.notes }}</span>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="rfq-modal__footer">
                    <button type="button" class="rfq-btn-outline" :disabled="form.processing" @click="createOpen = false">Cancel</button>
                    <button type="button" class="rfq-btn-primary" :disabled="form.processing" @click="submit">
                        {{ form.processing ? 'Submitting…' : 'Submit' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="deleteOpen"
            eyebrow="Trade"
            title="Delete Request for Quote"
            :message="pendingDelete ? `Delete this ${pendingDelete.crop_type} request? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDelete"
        />
    </DesignPreviewLayout>
</template>

<style>
.el-dialog.rfq-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.rfq-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.rfq-modal .el-dialog__body { padding: 0; }
.el-dialog.rfq-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.rfq-page { display: flex; flex-direction: column; gap: 20px; }

.rfq-page__header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.rfq-page__title { font-size: 22px; font-weight: 700; color: var(--dp-on-surface); margin: 0; }
.rfq-page__subtitle { font-size: 13.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; }
.rfq-add-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 34px;
    padding: 0 14px;
    border-radius: 6px;
    background: var(--dp-primary);
    border: 1px solid transparent;
    color: var(--dp-on-primary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.rfq-add-btn:hover { opacity: 0.88; }

.rfq-card {
    background: var(--dp-surface);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 8px;
    padding: 16px;
}
.rfq-table-wrap { overflow-x: auto; }
.rfq-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rfq-table th {
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--dp-on-surface-variant);
    padding: 8px 12px;
    border-bottom: 1px solid var(--dp-outline-variant);
}
.rfq-table td { padding: 11px 12px; border-bottom: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface); }
.rfq-table tr:last-child td { border-bottom: none; }
.rfq-table__main { display: block; font-weight: 600; }
.rfq-table__sub { display: block; font-size: 11.5px; color: var(--dp-on-surface-variant); margin-top: 2px; }
.rfq-status {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 2px 8px;
    border-radius: 999px;
}
.rfq-status.is-good { color: #16A34A; background: #E9F9EE; }
.rfq-status.is-info { color: #1D4ED8; background: #EFF6FF; }
.rfq-status.is-bad { color: var(--dp-error); background: var(--dp-error-container); }
.rfq-status.is-warn { color: #92400E; background: #fef3c7; }
.rfq-table__actions { text-align: right; }
.rfq-delete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: var(--dp-on-surface-variant);
    cursor: pointer;
}
.rfq-delete-btn:hover { background: var(--dp-error-container); color: var(--dp-error); }
.rfq-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; }

.rfq-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.rfq-modal__head-icon {
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
.rfq-modal__head-text { flex: 1; min-width: 0; }
.rfq-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.rfq-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.rfq-modal__close {
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
}
.rfq-modal__close:hover { background: #E5E7EB; color: #121516; }

.rfq-modal__body { padding: 22px 24px 8px; }
.rfq-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.rfq-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.rfq-field--span2 { grid-column: span 2; }
.rfq-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.rfq-field__label small { font-weight: 500; color: #6F7677; }
.rfq-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.rfq-input { width: 100%; }
.rfq-input :deep(.el-input__wrapper),
.rfq-input :deep(.el-select__wrapper),
.rfq-input :deep(.el-textarea__inner) { border-radius: 6px; }
.rfq-input--error :deep(.el-input__wrapper),
.rfq-input--error :deep(.el-select__wrapper),
.rfq-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.rfq-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.rfq-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    background: #000000;
    border: 1px solid transparent;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.rfq-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.rfq-btn-primary:disabled { opacity: 0.5; cursor: default; }
.rfq-btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    background: #fff;
    border: 1px solid #E5E7EB;
    color: #121516;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.rfq-btn-outline:hover:not(:disabled) { background: #F5F6F7; }

@media (max-width: 640px) {
    .rfq-grid { grid-template-columns: 1fr; }
    .rfq-field--span2 { grid-column: span 1; }
}
</style>

