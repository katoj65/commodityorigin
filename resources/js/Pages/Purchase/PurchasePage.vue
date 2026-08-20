<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Box, Coin, CircleCheck, CircleClose, Close,
    Tickets, Calendar, Wallet as WalletIcon, CreditCard, FolderOpened,
} from '@element-plus/icons-vue';

const props = defineProps({
    purchases: { type: Array, default: () => [] },
});

const sortedPurchases = computed(() => [...props.purchases].sort((a, b) => b.created_at.localeCompare(a.created_at)));
const activeCount = computed(() => props.purchases.filter((p) => p.status === 'completed').length);

function formatMoney(value, currency) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: currency || 'USD' }).format(value || 0);
}

function formatDate(dateTime) {
    if (!dateTime) return '—';
    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function paymentLabel(method) {
    return method === 'wallet' ? 'Wallet Balance' : 'Credit / Debit Card';
}

function paymentIcon(method) {
    return method === 'wallet' ? WalletIcon : CreditCard;
}

function openPurchase(row) {
    router.visit(route('purchases.show', row.id));
}

/* ── Cancel ──────────────────────────────────────────────────────────── */
const cancelOpen = ref(false);
const pendingCancel = ref(null);

function requestCancel(order) {
    pendingCancel.value = order;
    cancelOpen.value = true;
}

function confirmCancel() {
    if (!pendingCancel.value) return;
    router.patch(route('purchases.cancel', pendingCancel.value.id), {}, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Purchases" full-width flush :show-banner="false">
        <Head title="Purchases" />

        <div class="pur-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <section class="pur-header">
                <div class="pur-header__inner">
                    <h1 class="pur-title">Purchases</h1>
                    <p class="pur-subtitle">{{ activeCount }} of {{ purchases.length }} receipts active. A simple record of what you've bought at checkout.</p>
                </div>
            </section>

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div class="pur-body">
                <div class="pur-card">
                    <el-table :data="sortedPurchases" class="pur-table" row-key="id" @row-click="openPurchase">
                        <el-table-column min-width="170">
                            <template #header><span class="pur-th"><el-icon><Tickets /></el-icon> Order</span></template>
                            <template #default="{ row }">
                                <div class="pur-cell-order">
                                    <span class="pur-cell-order__num">{{ row.order_number }}</span>
                                    <span class="pur-cell-order__date"><el-icon :size="11"><Calendar /></el-icon> {{ formatDate(row.created_at) }}</span>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column min-width="240">
                            <template #header><span class="pur-th"><el-icon><Box /></el-icon> Items</span></template>
                            <template #default="{ row }">
                                <div class="pur-cell-items">
                                    <div v-for="(item, idx) in row.items" :key="idx" class="pur-cell-item">
                                        <span class="pur-cell-item__name">{{ item.name }}</span>
                                        <span class="pur-cell-item__meta">{{ item.quantity.toLocaleString() }} kg × {{ formatMoney(item.unit_price, row.currency) }}</span>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column width="180">
                            <template #header><span class="pur-th"><el-icon><WalletIcon /></el-icon> Payment</span></template>
                            <template #default="{ row }">
                                <span class="pur-payment"><el-icon :size="13"><component :is="paymentIcon(row.payment_method)" /></el-icon> {{ paymentLabel(row.payment_method) }}</span>
                            </template>
                        </el-table-column>

                        <el-table-column width="130" align="right">
                            <template #header><span class="pur-th pur-th--right"><el-icon><Coin /></el-icon> Amount</span></template>
                            <template #default="{ row }"><span class="pur-amount">{{ formatMoney(row.total_amount, row.currency) }}</span></template>
                        </el-table-column>

                        <el-table-column width="150" align="right">
                            <template #header><span class="pur-th pur-th--right"><el-icon><CircleCheck /></el-icon> Status</span></template>
                            <template #default="{ row }">
                                <div class="pur-cell-status">
                                    <span class="pur-status" :class="row.status === 'completed' ? 'is-completed' : 'is-cancelled'">
                                        <el-icon :size="12"><component :is="row.status === 'completed' ? CircleCheck : CircleClose" /></el-icon>
                                        {{ row.status === 'completed' ? 'Completed' : 'Cancelled' }}
                                    </span>
                                    <span v-if="row.status === 'cancelled'" class="pur-cancelled-note">{{ formatDate(row.cancelled_at) }}</span>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column width="90" align="right">
                            <template #header><span class="pur-th pur-th--right">Actions</span></template>
                            <template #default="{ row }">
                                <button
                                    v-if="row.status === 'completed'"
                                    type="button"
                                    class="pur-cancel-btn"
                                    aria-label="Cancel order"
                                    title="Cancel order"
                                    @click.stop="requestCancel(row)"
                                >
                                    <el-icon :size="14"><Close /></el-icon>
                                </button>
                            </template>
                        </el-table-column>

                        <template #empty>
                            <div class="pur-empty">
                                <div class="pur-empty__icon"><el-icon :size="22"><FolderOpened /></el-icon></div>
                                <div class="pur-empty__title">No purchases yet</div>
                                <p class="pur-empty__text">Orders you complete at checkout will show up here as a simple receipt.</p>
                            </div>
                        </template>
                    </el-table>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model="cancelOpen"
            title="Cancel Order"
            :message="`${pendingCancel?.order_number ?? 'This order'} will be marked as cancelled. This can't be undone.`"
            confirm-text="Cancel Order"
            @confirm="confirmCancel"
        />
    </AppLayout>
</template>

<style scoped>
.pur-page {
    --green: #004532;
    --red: #991b1b;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Header — edge-to-edge, white, flush top/left/right ─────────────── */
.pur-header {
    background: #fff;
    border-bottom: 1px solid var(--border);
}

.pur-header__inner {
    padding: 1.75rem 1.5rem;
}


.pur-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.pur-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

/* ── Body ────────────────────────────────────────────────────────────── */
.pur-body {
    width: 100%;
    padding: 1.5rem 1.5rem 3rem;
}

.pur-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

/* ── Element Plus table, reskinned to match the app's design system ────── */
.pur-table {
    --el-table-border-color: var(--border);
    --el-table-header-bg-color: var(--surface-low);
    --el-table-header-text-color: var(--on-surface-var);
    font-family: 'Manrope', system-ui, sans-serif;
}

.pur-table :deep(.el-table__header) th {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.pur-table :deep(.el-table__row) { cursor: pointer; }
.pur-table :deep(.el-table__cell) { padding: 12px 0; }
.pur-table :deep(.el-table__inner-wrapper::before) { display: none; }
.pur-table :deep(.el-table__header-wrapper th:first-child .cell),
.pur-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
.pur-table :deep(.el-table__header-wrapper th:last-child .cell),
.pur-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }

/* ── Table header icons ──────────────────────────────────────────────── */
.pur-th { display: inline-flex; align-items: center; gap: 6px; }
.pur-th :deep(.el-icon) { font-size: 13px; color: #9ca3af; }
.pur-th--right { justify-content: flex-end; }

/* ── Cells ───────────────────────────────────────────────────────────── */
.pur-cell-order {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.pur-cell-order__num {
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 0.8125rem;
    font-weight: 800;
    color: var(--on-surface);
}

.pur-cell-order__date {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.6875rem;
    color: var(--on-surface-var);
}

.pur-cell-items {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.pur-cell-item { display: flex; flex-direction: column; }
.pur-cell-item__name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.pur-cell-item__meta { font-size: 0.6875rem; color: var(--on-surface-var); }

.pur-payment {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
}

.pur-amount {
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 0.875rem;
    font-weight: 800;
    color: var(--on-surface);
}

.pur-cell-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.pur-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 700;
    white-space: nowrap;
}

.pur-status.is-completed { background: #dcfce7; color: #166534; }
.pur-status.is-cancelled { background: #fee2e2; color: #991b1b; }

.pur-cancelled-note {
    font-size: 0.6875rem;
    color: var(--on-surface-var);
}

.pur-cancel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #fff;
    color: var(--red);
    cursor: pointer;
    transition: background 0.12s ease, border-color 0.12s ease;
}

.pur-cancel-btn:hover { background: #fef2f2; border-color: #fecaca; }

/* ── Empty state ─────────────────────────────────────────────────────── */
.pur-empty { text-align: center; padding: 4rem 1rem; }

.pur-empty__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid var(--border);
    color: var(--on-surface-var);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
}

.pur-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.pur-empty__text { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0 auto; max-width: 320px; line-height: 1.5; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .pur-header__inner { padding: 1.25rem; }
    .pur-body { padding: 1.25rem 1.25rem 2.5rem; }
}
</style>
