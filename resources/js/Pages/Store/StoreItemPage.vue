<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import AddEditStoreItemDialog from '@/Components/Modals/AddEditStoreItemDialog.vue';
import StoreItemStatusDialog from '@/Components/Modals/StoreItemStatusDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Box, Calendar, Clock, Coin, Collection, Delete, Document, EditPen, Goods, Tickets,
} from '@element-plus/icons-vue';

const props = defineProps({
    item: { type: Object, required: true },
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
});

/* ── Shared with StoreLayout's header buttons via v-model ─────────────── */
const storeDialogOpen = ref(false);
const statusFilter = ref('all');
const importResultVisible = ref(Boolean(props.importResult));

function formatMoney(value, currency) {
    const amount = Number(value ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${amount}` : amount;
}

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
    if (!value) return '—';
    return new Date(value.replace(' ', 'T')).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDateTime(value) {
    if (!value) return '';
    return new Date(value.replace(' ', 'T')).toLocaleString(undefined, {
        month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
    });
}

const detailRows = computed(() => [
    { icon: Collection, label: 'Category', value: props.item.category || '—' },
    { icon: Tickets, label: 'SKU', value: props.item.sku || '—' },
    { icon: Coin, label: 'Price', value: formatMoney(props.item.price, props.item.currency_code) },
    { icon: Box, label: 'Quantity', value: `${props.item.quantity} ${props.item.unit || ''}`.trim() },
    { icon: Calendar, label: 'Added', value: formatDate(props.item.created_at) },
    { icon: Clock, label: 'Last Updated', value: formatDate(props.item.updated_at) },
]);

/* ── Edit / status / remove ───────────────────────────────────────────── */
const editDialogOpen = ref(false);
const statusDialogOpen = ref(false);

const removeOpen = ref(false);
const removing = ref(false);

function confirmRemove() {
    removing.value = true;
    router.delete(route('store.items.destroy', props.item.id), {
        onFinish: () => { removing.value = false; },
        onSuccess: () => {
            ElNotification({ title: 'Item Removed', message: `${props.item.name} was removed from your store.`, type: 'success', duration: 3200, offset: 84 });
        },
    });
}
</script>

<template>
    <StoreLayout
        :title="item.name"
        :store="store"
        :status-options="statusOptions"
        :import-result="importResult"
        v-model:store-dialog-open="storeDialogOpen"
        v-model:status-filter="statusFilter"
        v-model:import-result-visible="importResultVisible"
    >
        <template #icon>
            <el-icon :size="16"><Goods /></el-icon>
        </template>

        <div class="sip-page">
            <div class="sip-body">
                <div class="sip-layout">
                    <!-- ── Main column — every item detail lives in one card ── -->
                    <div class="sip-main">
                        <div class="sip-card">
                            <div class="sip-hero">
                                <span class="sip-thumb">
                                    <img v-if="item.image_url" :src="item.image_url" :alt="item.name">
                                    <el-icon v-else :size="26"><Goods /></el-icon>
                                </span>
                                <div class="sip-hero__info">
                                    <div class="sip-hero__top">
                                        <span v-if="item.category" class="sip-category">{{ item.category }}</span>
                                        <button type="button" class="sip-status" :class="`sip-status--${item.status}`" @click="statusDialogOpen = true">
                                            {{ statusLabel(item.status) }}
                                        </button>
                                    </div>
                                    <h1 class="sip-hero__name">{{ item.name }}</h1>
                                    <div class="sip-hero__price-row">
                                        <span class="sip-hero__price">{{ formatMoney(item.price, item.currency_code) }}</span>
                                        <span class="sip-hero__qty">{{ item.quantity }} {{ item.unit || '' }} available</span>
                                    </div>
                                </div>
                            </div>

                            <div class="sip-section">
                                <h3 class="sip-section-title"><el-icon :size="15"><Document /></el-icon> Item Details</h3>
                                <div class="sip-detail-grid">
                                    <div v-for="row in detailRows" :key="row.label" class="sip-detail-tile">
                                        <span class="sip-detail-tile__icon"><el-icon :size="15"><component :is="row.icon" /></el-icon></span>
                                        <div>
                                            <div class="sip-detail-tile__label">{{ row.label }}</div>
                                            <div class="sip-detail-tile__value">{{ row.value }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="sip-section">
                                <h3 class="sip-section-title"><el-icon :size="15"><Document /></el-icon> Description</h3>
                                <p v-if="item.description" class="sip-text">{{ item.description }}</p>
                                <p v-else class="sip-text sip-text--empty">No description has been provided for this item.</p>
                            </div>

                            <div v-if="item.notes" class="sip-section">
                                <h3 class="sip-section-title"><el-icon :size="15"><EditPen /></el-icon> Notes</h3>
                                <p class="sip-text">{{ item.notes }}</p>
                            </div>

                            <div class="sip-section">
                                <h3 class="sip-section-title"><el-icon :size="15"><Clock /></el-icon> Status &amp; Traceability</h3>

                                <el-timeline v-if="item.status_logs?.length" class="sip-timeline">
                                    <el-timeline-item
                                        v-for="log in item.status_logs"
                                        :key="log.id"
                                        :type="timelineType(log.to_status)"
                                        :timestamp="formatDateTime(log.created_at)"
                                        placement="top"
                                    >
                                        <div class="sip-log">
                                            <div class="sip-log__title">
                                                <span v-if="log.from_status">{{ statusLabel(log.from_status) }} → </span>{{ statusLabel(log.to_status) }}
                                            </div>
                                            <div v-if="log.changed_by" class="sip-log__by">by {{ log.changed_by }}</div>
                                            <div v-if="log.notes" class="sip-log__notes">{{ log.notes }}</div>
                                        </div>
                                    </el-timeline-item>
                                </el-timeline>
                                <p v-else class="sip-text sip-text--empty">No status history yet.</p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Sidebar ──────────────────────────────────────── -->
                    <div class="sip-sidebar">
                        <div class="sip-card">
                            <h3 class="sip-section-title">Actions</h3>
                            <div class="sip-actions">
                                <button type="button" class="sip-btn" @click="editDialogOpen = true">
                                    <el-icon :size="15"><EditPen /></el-icon> Edit Item
                                </button>
                                <button type="button" class="sip-btn" @click="statusDialogOpen = true">
                                    <el-icon :size="15"><Clock /></el-icon> Change Status
                                </button>
                                <button type="button" class="sip-btn sip-btn--danger" :disabled="removing" @click="removeOpen = true">
                                    <el-icon :size="15"><Delete /></el-icon> Remove Item
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AddEditStoreItemDialog v-model="editDialogOpen" :item="item" />
        <StoreItemStatusDialog v-model="statusDialogOpen" :item="item" :status-options="statusOptions" />
        <ConfirmDialog
            v-model="removeOpen"
            title="Remove Item"
            :message="`Remove ${item.name} from your store? This can't be undone.`"
            confirm-text="Remove"
            @confirm="confirmRemove"
        />
    </StoreLayout>
</template>

<style scoped>
.sip-page {
    --green: #145c42;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

.sip-body { padding: 1.5rem; max-width: 1080px; margin: 0 auto; }

.sip-layout { display: grid; grid-template-columns: minmax(0, 1fr) 260px; gap: 20px; align-items: start; }
.sip-main { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.sip-sidebar { display: flex; flex-direction: column; gap: 20px; }

.sip-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 26px 28px;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}
.sip-sidebar .sip-card { padding: 22px 24px; }

/* ── Sections — one card, hairline-separated, like a proper dossier ──── */
.sip-section { padding-top: 1.75rem; margin-top: 1.75rem; border-top: 1px solid var(--surface-low); }

/* ── Hero ─────────────────────────────────────────────────────────────── */
.sip-hero { display: flex; gap: 20px; align-items: flex-start; }
.sip-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 16px;
    background: rgba(20, 92, 66, 0.08);
    color: var(--green);
    flex-shrink: 0;
    overflow: hidden;
}
.sip-thumb img { width: 100%; height: 100%; object-fit: cover; }
.sip-hero__info { flex: 1; min-width: 0; }
.sip-hero__top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
.sip-category { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.sip-hero__name { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.01em; margin: 0 0 10px !important; color: var(--on-surface); }
.sip-hero__price-row { display: flex; align-items: baseline; gap: 12px; }
.sip-hero__price { font-family: 'IBM Plex Mono', monospace; font-size: 1.5rem; font-weight: 800; color: var(--on-surface); }
.sip-hero__qty { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Detail grid — icon-tile facts, matches the spec-tile pattern used
   across other detail pages in the app. ────────────────────────────── */
.sip-detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
.sip-detail-tile { display: flex; align-items: center; gap: 12px; background: var(--surface-low); border-radius: 10px; padding: 12px 14px; }
.sip-detail-tile__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: #fff;
    color: var(--green);
    flex-shrink: 0;
}
.sip-detail-tile__label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-var); }
.sip-detail-tile__value { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Status badge (button, opens status dialog) ──────────────────────── */
.sip-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: capitalize;
    padding: 4px 12px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    flex-shrink: 0;
}
.sip-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.sip-status--available { background: #dcfce7; color: #166534; }
.sip-status--reserved { background: #fef3c7; color: #92400e; }
.sip-status--sold { background: #dbeafe; color: #1e40af; }
.sip-status--shipped { background: #ede9fe; color: #6d28d9; }
.sip-status--delivered { background: #dcfce7; color: #166534; }
.sip-status--archived { background: #f1f5f9; color: #64748b; }

/* ── Sections ─────────────────────────────────────────────────────────── */
.sip-section-title { display: flex; align-items: center; gap: 6px; font-size: 0.9375rem; font-weight: 800; letter-spacing: -0.01em; color: var(--on-surface); margin: 0 0 1rem !important; }
.sip-section-title :deep(.el-icon) { color: var(--green); }
.sip-text { font-size: 0.875rem; color: var(--on-surface); line-height: 1.65; margin: 0; white-space: pre-line; }
.sip-text--empty { color: var(--on-surface-var); font-style: italic; }

/* ── Timeline ─────────────────────────────────────────────────────────── */
.sip-timeline { padding-left: 4px; }
.sip-log__title { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.sip-log__by { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 1px; }
.sip-log__notes { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 4px; line-height: 1.5; }

/* ── Sidebar actions ──────────────────────────────────────────────────── */
.sip-actions { display: flex; flex-direction: column; gap: 8px; }
.sip-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    height: 38px;
    padding: 0 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #fff;
    color: var(--on-surface);
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease;
}
.sip-btn:hover { background: var(--surface-low); }
.sip-btn:disabled { opacity: 0.6; cursor: default; }
.sip-btn--danger { color: #b91c1c; border-color: #fecaca; }
.sip-btn--danger:hover { background: #fef2f2; }

@media (max-width: 900px) {
    .sip-layout { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .sip-body { padding: 1.25rem; }
    .sip-hero { flex-direction: column; }
}
</style>
