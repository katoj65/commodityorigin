<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { View, Lock, Unlock } from '@element-plus/icons-vue';

const props = defineProps({
    escrows: { type: Array, default: () => [] },
    authUserId: { type: Number, default: null },
    isAdmin: { type: Boolean, default: false },
});

/* ── Perspective helpers ─────────────────────────────────────────────── */
function amBuyer(escrow) {
    return escrow.buyer_id === props.authUserId;
}

function amSeller(escrow) {
    return escrow.seller_id === props.authUserId;
}

/* ── Filters ─────────────────────────────────────────────────────────── */
const activeFilter = ref('all');
const filters = [
    { key: 'all', label: 'All' },
    { key: 'buyer', label: 'As Buyer' },
    { key: 'seller', label: 'As Seller' },
    { key: 'released', label: 'Released' },
];

const sortedEscrows = computed(() => [...props.escrows].sort((a, b) => b.created_at.localeCompare(a.created_at)));

function matchesFilter(key, escrow) {
    switch (key) {
        case 'buyer': return amBuyer(escrow);
        case 'seller': return amSeller(escrow);
        case 'released': return escrow.status === 'released';
        default: return true;
    }
}

const filteredEscrows = computed(() => sortedEscrows.value.filter((e) => matchesFilter(activeFilter.value, e)));

function tabCount(key) {
    return sortedEscrows.value.filter((e) => matchesFilter(key, e)).length;
}

/* ── KPIs ────────────────────────────────────────────────────────────── */
function formatMoney(amount, currency) {
    return `${currency} ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const kpis = computed(() => {
    const released = sortedEscrows.value.filter((e) => e.status === 'released');
    const totalReleased = released.reduce((sum, e) => sum + Number(e.amount), 0);
    const currency = sortedEscrows.value[0]?.currency ?? 'USD';

    return {
        total: sortedEscrows.value.length,
        buyer: tabCount('buyer'),
        seller: tabCount('seller'),
        releasedValue: formatMoney(totalReleased, currency),
    };
});

/* ── Display helpers ─────────────────────────────────────────────────── */
function statusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function statusTone(status) {
    return {
        held: 'esc-badge--amber',
        released: 'esc-badge--green',
        refunded: 'esc-badge--red',
    }[status] ?? 'esc-badge--muted';
}

function formatDate(dateTime) {
    if (!dateTime) return '—';

    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
    });
}

/* ── Actions ─────────────────────────────────────────────────────────── */
function openOrder(escrow) {
    router.visit(route('orders.show', escrow.order_id));
}
</script>

<template>
    <DesignPreviewLayout title="Escrow">
        <Head title="Escrow" />

        <div class="esc-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="esc-page-header">
                <div class="esc-page-header__left">
                    <div class="esc-kicker">Payments · Bean Origin</div>
                    <h1 class="esc-title">Escrow</h1>
                    <p class="esc-subtitle">Every order's funds, held against the buyer's wallet and released into the seller's once shipping is activated.</p>
                </div>
            </div>

            <!-- ── Overview strip ────────────────────────────────────────── -->
            <div class="esc-kpi-strip">
                <div class="esc-kpi">
                    <span class="esc-kpi__label">Total Accounts</span>
                    <strong class="esc-kpi__val">{{ kpis.total }}</strong>
                </div>
                <div class="esc-kpi">
                    <span class="esc-kpi__label">As Buyer</span>
                    <strong class="esc-kpi__val">{{ kpis.buyer }}</strong>
                </div>
                <div class="esc-kpi">
                    <span class="esc-kpi__label">As Seller</span>
                    <strong class="esc-kpi__val">{{ kpis.seller }}</strong>
                </div>
                <div class="esc-kpi">
                    <span class="esc-kpi__label">Total Released</span>
                    <strong class="esc-kpi__val esc-text-green">{{ kpis.releasedValue }}</strong>
                </div>
            </div>

            <div class="esc-body">
                <div class="esc-section">
                    <el-tabs v-model="activeFilter" class="esc-tabs">
                        <el-tab-pane v-for="f in filters" :key="f.key" :name="f.key">
                            <template #label>
                                <span class="esc-tab-label">
                                    {{ f.label }}
                                    <span v-if="tabCount(f.key)" class="esc-tab-count">{{ tabCount(f.key) }}</span>
                                </span>
                            </template>
                        </el-tab-pane>
                    </el-tabs>

                    <el-table
                        :data="filteredEscrows"
                        class="esc-table"
                        empty-text="No escrow accounts in this view."
                        @row-click="openOrder"
                    >
                        <el-table-column label="Order" width="170">
                            <template #default="{ row }">
                                <div class="esc-cell-order">
                                    <span class="esc-cell-order__num">{{ row.order_number }}</span>
                                    <span class="esc-cell-order__crop">{{ row.crop_type }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="Buyer" min-width="140">
                            <template #default="{ row }">{{ row.buyer_name }}</template>
                        </el-table-column>
                        <el-table-column label="Seller" min-width="140">
                            <template #default="{ row }">{{ row.seller_name }}</template>
                        </el-table-column>
                        <el-table-column label="Amount" min-width="130">
                            <template #default="{ row }">
                                <span class="esc-amount">{{ formatMoney(row.amount, row.currency) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Status" width="110">
                            <template #default="{ row }">
                                <span class="esc-badge" :class="statusTone(row.status)">
                                    <el-icon :size="10"><component :is="row.status === 'held' ? Lock : Unlock" /></el-icon>
                                    {{ statusLabel(row.status) }}
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Held" width="110">
                            <template #default="{ row }">{{ formatDate(row.held_at) }}</template>
                        </el-table-column>
                        <el-table-column label="Released" width="110">
                            <template #default="{ row }">{{ formatDate(row.released_at) }}</template>
                        </el-table-column>
                        <el-table-column label="" width="60" align="right">
                            <template #default="{ row }">
                                <button
                                    type="button"
                                    class="esc-icon-btn"
                                    aria-label="View order"
                                    title="View order"
                                    @click.stop="openOrder(row)"
                                >
                                    <el-icon :size="15"><View /></el-icon>
                                </button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.esc-page {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.esc-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.75rem 1.5rem 0;
}

.esc-page-header__left {
    max-width: 560px;
}

.esc-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.esc-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.esc-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

/* ── Overview strip ──────────────────────────────────────────────────── */
.esc-kpi-strip {
    display: flex;
    overflow-x: auto;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-top: 1.5rem;
    scrollbar-width: none;
}

.esc-kpi-strip::-webkit-scrollbar { display: none; }

.esc-kpi {
    flex: 1;
    min-width: 130px;
    padding: 1rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
    border-right: 1px solid var(--border);
}

.esc-kpi:last-child { border-right: none; }

.esc-kpi__label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
}

.esc-kpi__val {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--on-surface);
    letter-spacing: -0.01em;
}

.esc-text-green { color: #166534; }

/* ── Body ────────────────────────────────────────────────────────────── */
.esc-body {
    padding: 1.5rem 0 3rem;
}

.esc-section {
    background: transparent;
}

/* ── Category tabs ───────────────────────────────────────────────────── */
.esc-tabs { padding: 0 1.5rem; }
.esc-tabs :deep(.el-tabs__header) { margin: 0; }
.esc-tabs :deep(.el-tabs__nav-wrap::after) { background-color: var(--border); }
.esc-tabs :deep(.el-tabs__item) {
    font-weight: 700;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    height: 44px;
}
.esc-tabs :deep(.el-tabs__item.is-active) { color: var(--green); }
.esc-tabs :deep(.el-tabs__active-bar) { background-color: var(--green); }

.esc-tab-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.esc-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    font-size: 0.625rem;
    font-weight: 800;
}

/* ── Table ───────────────────────────────────────────────────────────── */
.esc-table {
    --el-table-border-color: var(--border);
    --el-table-header-bg-color: var(--surface-low);
    --el-table-header-text-color: var(--on-surface-var);
    font-family: 'Manrope', system-ui, sans-serif;
}

.esc-table :deep(.el-table__header) th {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.esc-table :deep(.el-table__row) { cursor: pointer; }
.esc-table :deep(.el-table__inner-wrapper::before) { display: none; }
.esc-table :deep(.el-table__header-wrapper th:first-child .cell),
.esc-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.5rem; }
.esc-table :deep(.el-table__header-wrapper th:last-child .cell),
.esc-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.5rem; }

.esc-cell-order {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.esc-cell-order__num {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
    font-family: 'IBM Plex Mono', monospace;
}

.esc-cell-order__crop {
    font-size: 0.75rem;
    color: var(--on-surface-var);
}

.esc-amount {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
    font-family: 'IBM Plex Mono', monospace;
}

.esc-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 4px 10px;
    white-space: nowrap;
}

.esc-badge--green { background: #dcfce7; color: #166534; }
.esc-badge--amber { background: #fef3c7; color: #92400e; }
.esc-badge--red { background: #fee2e2; color: #991b1b; }
.esc-badge--muted { background: #f3f4f6; color: #6b7280; }

.esc-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    background: none;
    color: #6b7280;
    cursor: pointer;
    transition: background 0.12s, color 0.12s;
}

.esc-icon-btn:hover { background: var(--surface-low); color: var(--on-surface); }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .esc-page-header { padding: 1.25rem 1.25rem 0; }
    .esc-body { padding: 1.25rem 0 3rem; }
    .esc-tabs { padding: 0 1.25rem; }
    .esc-table :deep(.el-table__header-wrapper th:first-child .cell),
    .esc-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
    .esc-table :deep(.el-table__header-wrapper th:last-child .cell),
    .esc-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }
}
</style>
