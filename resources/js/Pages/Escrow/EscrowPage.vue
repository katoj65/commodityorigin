<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { View, Lock, Unlock, CircleCheck, Clock, Warning, ShoppingBag } from '@element-plus/icons-vue';

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
function formatMoney(amount, currency = 'USD') {
    return `${currency} ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const kpis = computed(() => {
    const currency = sortedEscrows.value[0]?.currency ?? 'USD';
    const held = sortedEscrows.value.filter((e) => e.status === 'held');
    const heldTotal = held.reduce((sum, e) => sum + Number(e.amount), 0);

    const now = new Date();
    const releasedThisMonth = sortedEscrows.value.filter((e) => {
        if (e.status !== 'released' || !e.released_at) return false;
        const releasedAt = new Date(e.released_at.replace(' ', 'T'));
        return releasedAt.getFullYear() === now.getFullYear() && releasedAt.getMonth() === now.getMonth();
    });
    const releasedTotal = releasedThisMonth.reduce((sum, e) => sum + Number(e.amount), 0);

    return {
        heldTotal: formatMoney(heldTotal, currency),
        heldCount: held.length,
        totalCount: sortedEscrows.value.length,
        releasedTotal: formatMoney(releasedTotal, currency),
        releasedCount: releasedThisMonth.length,
    };
});

/* ── Selection (drives the detail panel) ────────────────────────────── */
const selectedId = ref(null);

watch(filteredEscrows, (list) => {
    if (!list.length) {
        selectedId.value = null;
        return;
    }
    if (!list.some((e) => e.id === selectedId.value)) {
        selectedId.value = list[0].id;
    }
}, { immediate: true });

const selectedEscrow = computed(() => filteredEscrows.value.find((e) => e.id === selectedId.value) ?? null);

function selectEscrow(escrow) {
    selectedId.value = escrow.id;
}

function rowClassName({ row }) {
    return row.id === selectedId.value ? 'esc-row--selected' : '';
}

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
            <div class="esc-header">
                <div class="esc-header__text">
                    <h1 class="dp-display-md">Escrow</h1>
                    <p class="esc-subtitle">Every order's funds, held against the buyer's wallet and released into the seller's the moment shipping is activated.</p>
                </div>
                <button type="button" class="esc-btn esc-btn--outline" @click="router.visit(route('orders.index'))">
                    <el-icon :size="16"><ShoppingBag /></el-icon>
                    View Orders
                </button>
            </div>

            <!-- ── KPI cards ─────────────────────────────────────────────── -->
            <div class="esc-kpi-grid">
                <div class="esc-kpi-card">
                    <div class="esc-kpi-card__decor esc-kpi-card__decor--primary" />
                    <div class="esc-kpi-card__top">
                        <span class="esc-kpi-card__label">Held In Escrow</span>
                        <span class="esc-kpi-card__icon"><el-icon :size="15"><Lock /></el-icon></span>
                    </div>
                    <div class="esc-kpi-card__val">{{ kpis.heldTotal }}</div>
                    <p class="esc-kpi-card__note">{{ kpis.heldCount }} account{{ kpis.heldCount === 1 ? '' : 's' }} awaiting release</p>
                </div>

                <div class="esc-kpi-card">
                    <div class="esc-kpi-card__decor esc-kpi-card__decor--secondary" />
                    <div class="esc-kpi-card__top">
                        <span class="esc-kpi-card__label">Total Accounts</span>
                        <span class="esc-kpi-card__icon esc-kpi-card__icon--secondary"><el-icon :size="15"><Unlock /></el-icon></span>
                    </div>
                    <div class="esc-kpi-card__val">{{ kpis.totalCount }}</div>
                    <p class="esc-kpi-card__note">Across every order you're party to</p>
                </div>

                <div class="esc-kpi-card esc-kpi-card--dark">
                    <div class="esc-kpi-card__top">
                        <span class="esc-kpi-card__label">Released This Month</span>
                        <span class="esc-kpi-card__icon esc-kpi-card__icon--dark"><el-icon :size="15"><CircleCheck /></el-icon></span>
                    </div>
                    <div class="esc-kpi-card__val">{{ kpis.releasedTotal }}</div>
                    <p class="esc-kpi-card__note">Across {{ kpis.releasedCount }} completed trade{{ kpis.releasedCount === 1 ? '' : 's' }}</p>
                </div>
            </div>

            <!-- ── Main grid ─────────────────────────────────────────────── -->
            <div class="esc-grid">
                <!-- Left: list -->
                <div class="esc-col-main">
                    <div class="esc-card esc-list-card">
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
                            :row-class-name="rowClassName"
                            @row-click="selectEscrow"
                        >
                            <el-table-column label="Order" min-width="170">
                                <template #default="{ row }">
                                    <div class="esc-cell-order">
                                        <span class="esc-cell-order__num">{{ row.order_number }}</span>
                                        <span class="esc-cell-order__crop">{{ row.crop_type }}</span>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column label="Buyer" min-width="130" class-name="esc-col-hide-sm">
                                <template #default="{ row }">{{ row.buyer_name }}</template>
                            </el-table-column>
                            <el-table-column label="Seller" min-width="130" class-name="esc-col-hide-sm">
                                <template #default="{ row }">{{ row.seller_name }}</template>
                            </el-table-column>
                            <el-table-column label="Amount" min-width="120">
                                <template #default="{ row }">
                                    <span class="esc-amount">{{ formatMoney(row.amount, row.currency) }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column label="Status" width="120">
                                <template #default="{ row }">
                                    <span class="esc-badge" :class="statusTone(row.status)">
                                        <el-icon :size="10"><component :is="row.status === 'held' ? Lock : Unlock" /></el-icon>
                                        {{ statusLabel(row.status) }}
                                    </span>
                                </template>
                            </el-table-column>
                            <el-table-column label="" width="52" align="right">
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

                <!-- Right: detail + info -->
                <div class="esc-col-side">
                    <div class="esc-card esc-detail-card">
                        <template v-if="selectedEscrow">
                            <div class="esc-detail__head">
                                <div>
                                    <p class="esc-detail__order">{{ selectedEscrow.order_number }}</p>
                                    <p class="esc-detail__crop">{{ selectedEscrow.crop_type }}</p>
                                </div>
                                <span class="esc-badge" :class="statusTone(selectedEscrow.status)">
                                    {{ statusLabel(selectedEscrow.status) }}
                                </span>
                            </div>

                            <dl class="esc-detail__specs">
                                <div class="esc-detail__spec">
                                    <dt>Buyer</dt>
                                    <dd>{{ selectedEscrow.buyer_name }}</dd>
                                </div>
                                <div class="esc-detail__spec">
                                    <dt>Seller</dt>
                                    <dd>{{ selectedEscrow.seller_name }}</dd>
                                </div>
                                <div class="esc-detail__spec">
                                    <dt>Amount</dt>
                                    <dd class="esc-mono">{{ formatMoney(selectedEscrow.amount, selectedEscrow.currency) }}</dd>
                                </div>
                            </dl>

                            <h3 class="esc-detail__timeline-title">Timeline</h3>
                            <div class="esc-timeline">
                                <div class="esc-timeline__step esc-timeline__step--done">
                                    <span class="esc-timeline__icon esc-timeline__icon--done"><el-icon :size="14"><CircleCheck /></el-icon></span>
                                    <div>
                                        <p class="esc-timeline__label">Escrow Funded</p>
                                        <p class="esc-timeline__meta">{{ formatDate(selectedEscrow.held_at) }}</p>
                                    </div>
                                </div>

                                <div
                                    class="esc-timeline__step"
                                    :class="selectedEscrow.status === 'held' ? 'esc-timeline__step--pending' : selectedEscrow.status === 'refunded' ? 'esc-timeline__step--refunded' : 'esc-timeline__step--done'"
                                >
                                    <span
                                        class="esc-timeline__icon"
                                        :class="selectedEscrow.status === 'held' ? 'esc-timeline__icon--pending' : selectedEscrow.status === 'refunded' ? 'esc-timeline__icon--refunded' : 'esc-timeline__icon--done'"
                                    >
                                        <el-icon :size="14">
                                            <component :is="selectedEscrow.status === 'held' ? Clock : selectedEscrow.status === 'refunded' ? Warning : CircleCheck" />
                                        </el-icon>
                                    </span>
                                    <div>
                                        <template v-if="selectedEscrow.status === 'held'">
                                            <p class="esc-timeline__label">Awaiting Release</p>
                                            <p class="esc-timeline__meta">Released once shipping is activated</p>
                                        </template>
                                        <template v-else-if="selectedEscrow.status === 'refunded'">
                                            <p class="esc-timeline__label">Funds Refunded</p>
                                            <p class="esc-timeline__meta">{{ formatDate(selectedEscrow.released_at) }}</p>
                                        </template>
                                        <template v-else>
                                            <p class="esc-timeline__label">Funds Released</p>
                                            <p class="esc-timeline__meta">
                                                {{ formatDate(selectedEscrow.released_at) }}<template v-if="selectedEscrow.released_by_name"> · {{ selectedEscrow.released_by_name }}</template>
                                            </p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="esc-btn esc-btn--block" @click="openOrder(selectedEscrow)">
                                View Full Order
                            </button>
                        </template>

                        <div v-else class="esc-detail__empty">
                            <el-icon :size="22"><Lock /></el-icon>
                            <p>No escrow account selected</p>
                        </div>
                    </div>

                    <div class="esc-card esc-protection-card">
                        <div class="esc-protection-card__head">
                            <span class="esc-protection-card__icon"><el-icon :size="18"><Lock /></el-icon></span>
                            <h4 class="esc-protection-card__title">Platform Protection</h4>
                        </div>
                        <p class="esc-protection-card__body">
                            When an order ships, the buyer's wallet is debited and the seller's wallet is credited in a single, atomic transaction — funds are never released without a confirmed shipment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.esc-page {
    display: flex;
    flex-direction: column;
    gap: 28px;
    font-family: var(--dp-font-sans);
}

/* ── Header ──────────────────────────────────────────────────────────── */
.esc-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.esc-header__text { max-width: 620px; }
.esc-header__text h1 { color: var(--dp-primary); }
.esc-subtitle {
    font-size: 14px;
    line-height: 1.6;
    color: var(--dp-on-surface-variant);
    margin: 8px 0 0;
}

.esc-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.01em;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}
.esc-btn--outline {
    border: 1px solid var(--dp-outline-variant);
    background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface);
}
.esc-btn--outline:hover { background: var(--dp-surface-container-low); }
.esc-btn--block {
    width: 100%;
    justify-content: center;
    margin-top: 18px;
    border: none;
    background: var(--dp-primary);
    color: var(--dp-on-primary);
}
.esc-btn--block:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); }
.esc-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

/* ── KPI cards ───────────────────────────────────────────────────────── */
.esc-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.esc-kpi-card {
    position: relative;
    overflow: hidden;
    padding: 22px;
    border-radius: var(--dp-card-radius);
    background: var(--dp-surface-container-lowest);
    box-shadow: var(--dp-card-shadow);
}

.esc-kpi-card__decor {
    position: absolute;
    top: -16px;
    right: -16px;
    width: 96px;
    height: 96px;
    border-radius: 50%;
    filter: blur(20px);
    pointer-events: none;
}
.esc-kpi-card__decor--primary { background: color-mix(in srgb, var(--dp-primary-container) 40%, transparent); }
.esc-kpi-card__decor--secondary { background: color-mix(in srgb, var(--dp-secondary-container) 45%, transparent); }

.esc-kpi-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    position: relative;
}
.esc-kpi-card__label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--dp-on-surface-variant);
}
.esc-kpi-card--dark .esc-kpi-card__label { color: rgba(255, 255, 255, 0.7); }

.esc-kpi-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
}
.esc-kpi-card__icon--secondary { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.esc-kpi-card__icon--dark { background: rgba(255, 255, 255, 0.14); color: var(--dp-on-primary); }

.esc-kpi-card__val {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: var(--dp-primary);
    font-variant-numeric: tabular-nums;
    position: relative;
}
.esc-kpi-card--dark .esc-kpi-card__val { color: var(--dp-on-primary); }

.esc-kpi-card__note {
    font-size: 12.5px;
    color: var(--dp-on-surface-variant);
    margin: 4px 0 0;
    position: relative;
}
.esc-kpi-card--dark .esc-kpi-card__note { color: rgba(255, 255, 255, 0.7); }

.esc-kpi-card--dark {
    background: var(--dp-primary);
    color: var(--dp-on-primary);
}

/* ── Grid ────────────────────────────────────────────────────────────── */
.esc-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 20px;
    align-items: start;
}
.esc-col-main { min-width: 0; }
.esc-col-side { display: flex; flex-direction: column; gap: 20px; min-width: 0; }

.esc-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
}

/* ── List card / tabs ────────────────────────────────────────────────── */
.esc-list-card { overflow: hidden; }
.esc-tabs { padding: 4px 20px 0; }
.esc-tabs :deep(.el-tabs__header) { margin: 0; }
.esc-tabs :deep(.el-tabs__nav-wrap::after) { background-color: var(--dp-outline-variant); opacity: 0.35; }
.esc-tabs :deep(.el-tabs__item) {
    font-weight: 700;
    font-size: 13px;
    color: var(--dp-on-surface-variant);
    height: 46px;
}
.esc-tabs :deep(.el-tabs__item.is-active) { color: var(--dp-primary); }
.esc-tabs :deep(.el-tabs__active-bar) { background-color: var(--dp-primary); }

.esc-tab-label { display: inline-flex; align-items: center; gap: 6px; }
.esc-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
    font-size: 10px;
    font-weight: 800;
}

/* ── Table ───────────────────────────────────────────────────────────── */
.esc-table {
    --el-table-header-bg-color: var(--dp-surface-container-lowest);
    --el-table-header-text-color: var(--dp-on-surface-variant);
    --el-table-tr-bg-color: transparent;
    --el-table-row-hover-bg-color: var(--dp-surface-container-low);
    font-family: var(--dp-font-sans);
}
.esc-table :deep(.el-table__header) th {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 14px 0;
}
.esc-table :deep(.el-table__cell) { border-bottom: 1px solid color-mix(in srgb, var(--dp-outline-variant) 25%, transparent); }
.esc-table :deep(.el-table__row) { cursor: pointer; }
.esc-table :deep(.el-table__inner-wrapper::before) { display: none; }
.esc-table :deep(.el-table__header-wrapper th:first-child .cell),
.esc-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 20px; }
.esc-table :deep(.el-table__header-wrapper th:last-child .cell),
.esc-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 20px; }
.esc-table :deep(tr.esc-row--selected td) { background: var(--dp-surface-container-low); }

.esc-cell-order { display: flex; flex-direction: column; gap: 3px; }
.esc-cell-order__num { font-size: 13px; font-weight: 700; color: var(--dp-primary); font-family: var(--dp-font-mono); }
.esc-cell-order__crop { font-size: 12px; color: var(--dp-on-surface-variant); }

.esc-amount { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); font-family: var(--dp-font-mono); }

.esc-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    white-space: nowrap;
    text-transform: capitalize;
}
.esc-badge--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.esc-badge--amber { background: #fef3c7; color: #92400e; }
.esc-badge--red { background: var(--dp-error-container); color: var(--dp-on-error-container); }
.esc-badge--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.esc-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: none;
    background: none;
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}
.esc-icon-btn:hover { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.esc-icon-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

/* ── Detail card ─────────────────────────────────────────────────────── */
.esc-detail-card { padding: 22px; }
.esc-detail__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}
.esc-detail__order { font-size: 15px; font-weight: 800; color: var(--dp-primary); margin: 0; font-family: var(--dp-font-mono); }
.esc-detail__crop { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 2px 0 0; }

.esc-detail__specs {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 0 0 20px;
    padding-bottom: 18px;
    border-bottom: 1px solid color-mix(in srgb, var(--dp-outline-variant) 30%, transparent);
}
.esc-detail__spec { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.esc-detail__spec dt { font-size: 12.5px; color: var(--dp-on-surface-variant); }
.esc-detail__spec dd { margin: 0; font-size: 13px; font-weight: 700; color: var(--dp-on-surface); text-align: right; }
.esc-mono { font-family: var(--dp-font-mono); }

.esc-detail__timeline-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--dp-on-surface-variant);
    margin: 0 0 14px;
}

.esc-timeline { display: flex; flex-direction: column; }
.esc-timeline__step {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding-bottom: 20px;
}
.esc-timeline__step::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 26px;
    bottom: 0;
    width: 1px;
    background: color-mix(in srgb, var(--dp-outline-variant) 45%, transparent);
}
.esc-timeline__step:last-child { padding-bottom: 0; }
.esc-timeline__step:last-child::before { display: none; }

.esc-timeline__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    flex-shrink: 0;
    border-radius: 50%;
    position: relative;
    z-index: 1;
}
.esc-timeline__icon--done { background: var(--dp-primary); color: var(--dp-on-primary); }
.esc-timeline__icon--pending { background: var(--dp-primary-fixed); color: var(--dp-primary); }
.esc-timeline__icon--refunded { background: var(--dp-error-container); color: var(--dp-on-error-container); }

.esc-timeline__label { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); margin: 0 0 2px; }
.esc-timeline__meta { font-size: 12px; color: var(--dp-on-surface-variant); margin: 0; }

.esc-detail__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 40px 0;
    color: var(--dp-outline);
    text-align: center;
    font-size: 13px;
}

/* ── Protection card ─────────────────────────────────────────────────── */
.esc-protection-card { padding: 22px; }
.esc-protection-card__head { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.esc-protection-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
    flex-shrink: 0;
}
.esc-protection-card__title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--dp-primary);
    margin: 0;
}
.esc-protection-card__body { font-size: 13px; line-height: 1.65; color: var(--dp-on-surface-variant); margin: 0; }

/* ── Reduced motion ──────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .esc-btn { transition: none; }
    .esc-icon-btn { transition: none; }
}

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .esc-grid { grid-template-columns: 1fr; }
    .esc-col-side { flex-direction: row; }
    .esc-col-side .esc-card { flex: 1; min-width: 0; }
}

@media (max-width: 767.98px) {
    .esc-kpi-grid { grid-template-columns: 1fr; }
    .esc-col-side { flex-direction: column; }
    .esc-table :deep(.esc-col-hide-sm) { display: none; }
    .esc-header { align-items: flex-start; }
}
</style>
