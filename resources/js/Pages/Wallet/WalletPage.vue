<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import DepositModal from '@/Components/DepositModal.vue';
import WithdrawModal from '@/Components/WithdrawModal.vue';
import EscrowTransferModal from '@/Components/EscrowTransferModal.vue';
import {
    Top, Bottom, Promotion, Lock, FolderOpened, CirclePlus, OfficeBuilding,
} from '@element-plus/icons-vue';

const props = defineProps({
    wallet: { type: Object, required: true },
    escrowWallet: { type: Object, required: true },
    transactions: { type: Array, default: () => [] },
});

/* ── Formatting ──────────────────────────────────────────────────────── */
function formatMoney(amount, currency = props.wallet.currency) {
    return `${currency} ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(dateTime) {
    if (!dateTime) return '—';
    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
    });
}

const heroBalance = computed(() => {
    const [intPart, decPart] = Number(props.wallet.available_balance)
        .toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        .split('.');
    return { intPart, decPart };
});

/* ── KPIs ────────────────────────────────────────────────────────────── */
const kpis = computed(() => {
    const received = props.transactions.filter((t) => t.is_credit).reduce((sum, t) => sum + Number(t.amount), 0);
    const spent = props.transactions.filter((t) => !t.is_credit).reduce((sum, t) => sum + Number(t.amount), 0);

    return {
        received: formatMoney(received),
        spent: formatMoney(spent),
        count: props.transactions.length,
    };
});

/* ── Cash flow: real monthly totals from the transaction ledger ────────── */
const cashFlowView = ref('inflow');

const cashFlowMonths = computed(() => {
    const now = new Date();
    const months = [];
    for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        months.push({ key: `${d.getFullYear()}-${d.getMonth()}`, label: d.toLocaleDateString('en-US', { month: 'short' }), inflow: 0, outflow: 0 });
    }
    const byKey = Object.fromEntries(months.map((m) => [m.key, m]));
    for (const t of props.transactions) {
        if (!t.created_at) continue;
        const d = new Date(t.created_at.replace(' ', 'T'));
        const bucket = byKey[`${d.getFullYear()}-${d.getMonth()}`];
        if (!bucket) continue;
        if (t.is_credit) bucket.inflow += Number(t.amount);
        else bucket.outflow += Number(t.amount);
    }
    return months;
});

const cashFlowMax = computed(() => Math.max(1, ...cashFlowMonths.value.map((m) => m[cashFlowView.value])));

/* ── Filters ─────────────────────────────────────────────────────────── */
const activeFilter = ref('all');
const filters = [
    { key: 'all', label: 'All' },
    { key: 'in', label: 'Money In' },
    { key: 'out', label: 'Money Out' },
];

function matchesFilter(key, t) {
    switch (key) {
        case 'in': return t.is_credit;
        case 'out': return !t.is_credit;
        default: return true;
    }
}

const filteredTransactions = computed(() => props.transactions.filter((t) => matchesFilter(activeFilter.value, t)));

function tabCount(key) {
    return props.transactions.filter((t) => matchesFilter(key, t)).length;
}

/* ── Display helpers ────────────────────────────────────────────────── */
const typeLabels = {
    deposit: 'Deposit',
    withdrawal: 'Withdrawal',
    escrow_fund: 'Transfer to Escrow',
    escrow_hold: 'Escrow Hold',
    escrow_release: 'Escrow Payment',
};

function typeLabel(type) {
    return typeLabels[type] ?? type;
}

const statusTone = {
    completed: 'wal-badge--green',
    pending: 'wal-badge--amber',
    failed: 'wal-badge--red',
};

/* ── Deposit ─────────────────────────────────────────────────────────── */
const depositOpen = ref(false);

function openDeposit() {
    depositOpen.value = true;
}

/* ── Transfer to escrow ──────────────────────────────────────────────── */
const transferOpen = ref(false);

function openTransfer() {
    transferOpen.value = true;
}

/* ── Withdraw ────────────────────────────────────────────────────────── */
const withdrawOpen = ref(false);

function openWithdraw() {
    withdrawOpen.value = true;
}
</script>

<template>
    <DesignPreviewLayout title="Wallet">
        <Head title="Wallet" />

        <div class="wal-page">

            <div class="wal-header">
                <div>
                    <h1 class="wal-title">Wallet &amp; Finance</h1>
                    <p class="wal-subtitle">Track your balance, escrow holdings, and every transaction across the exchange.</p>
                </div>
                <button type="button" class="wal-btn wal-btn--primary" @click="openDeposit">
                    <el-icon :size="15"><CirclePlus /></el-icon> Deposit Funds
                </button>
            </div>

            <!-- ── Top: balance + cash flow ─────────────────────────────── -->
            <div class="wal-top-grid">
                <div class="wal-card wal-balance-card">
                    <div class="wal-balance-head">
                        <span class="wal-status" :class="wallet.status === 'active' ? 'wal-status--on' : 'wal-status--off'">{{ wallet.status }}</span>
                        <h2 class="wal-card-title">Total Balance</h2>
                        <p class="wal-card-sub">Available for trading</p>
                    </div>

                    <div class="wal-balance-amount">
                        <span class="wal-balance-amount__int">{{ wallet.currency }} {{ heroBalance.intPart }}</span>
                        <span class="wal-balance-amount__dec">.{{ heroBalance.decPart }}</span>
                    </div>

                    <div class="wal-balance-actions">
                        <button type="button" class="wal-btn wal-btn--sm" @click="openTransfer">
                            <el-icon :size="13"><Promotion /></el-icon> Transfer
                        </button>
                        <button type="button" class="wal-btn wal-btn--sm" @click="openWithdraw">
                            <el-icon :size="13"><Bottom /></el-icon> Withdraw
                        </button>
                    </div>

                    <div class="wal-balance-footer">
                        <div class="wal-balance-stat">
                            <span class="wal-balance-stat__label">Total Balance</span>
                            <strong class="wal-balance-stat__val">{{ formatMoney(wallet.balance) }}</strong>
                        </div>
                        <div class="wal-balance-stat">
                            <span class="wal-balance-stat__label">Locked</span>
                            <strong class="wal-balance-stat__val">{{ formatMoney(wallet.locked_balance) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="wal-card wal-cashflow-card">
                    <div class="wal-cashflow-head">
                        <div>
                            <h2 class="wal-card-title">Cash Flow</h2>
                            <p class="wal-card-sub">Last 6 months</p>
                        </div>
                        <div class="wal-segmented">
                            <button
                                type="button"
                                class="wal-segmented__option"
                                :class="{ 'wal-segmented__option--active': cashFlowView === 'inflow' }"
                                @click="cashFlowView = 'inflow'"
                            >Inflow</button>
                            <button
                                type="button"
                                class="wal-segmented__option"
                                :class="{ 'wal-segmented__option--active': cashFlowView === 'outflow' }"
                                @click="cashFlowView = 'outflow'"
                            >Outflow</button>
                        </div>
                    </div>

                    <div class="wal-chart">
                        <div v-for="m in cashFlowMonths" :key="m.key" class="wal-chart__col">
                            <div class="wal-chart__bar-track">
                                <div
                                    class="wal-chart__bar"
                                    :class="cashFlowView === 'inflow' ? 'wal-chart__bar--in' : 'wal-chart__bar--out'"
                                    :style="{ height: `${Math.max(4, (m[cashFlowView] / cashFlowMax) * 100)}%` }"
                                    :title="formatMoney(m[cashFlowView])"
                                ></div>
                            </div>
                            <span class="wal-chart__label">{{ m.label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Lower: wallets + recent activity ─────────────────────── -->
            <div class="wal-lower-grid">
                <div class="wal-col-wallets">
                    <div class="wal-lower-head">
                        <h3 class="wal-lower-title">Your Wallets</h3>
                    </div>

                    <div class="wal-account-tile">
                        <div class="wal-account-tile__top">
                            <span class="wal-account-tile__icon"><el-icon :size="18"><OfficeBuilding /></el-icon></span>
                            <span class="wal-account-tile__badge">Primary</span>
                        </div>
                        <h4 class="wal-account-tile__name">Main Wallet</h4>
                        <p class="wal-account-tile__val">{{ formatMoney(wallet.available_balance) }}</p>
                    </div>

                    <Link :href="route('escrow.index')" class="wal-account-tile wal-account-tile--dark">
                        <div class="wal-account-tile__top">
                            <span class="wal-account-tile__icon wal-account-tile__icon--dark"><el-icon :size="18"><Lock /></el-icon></span>
                            <span class="wal-account-tile__badge wal-account-tile__badge--dark">Escrow</span>
                        </div>
                        <h4 class="wal-account-tile__name">Escrow Wallet</h4>
                        <p class="wal-account-tile__val">{{ formatMoney(escrowWallet.balance, escrowWallet.currency) }}</p>
                        <p class="wal-account-tile__note">Funds reserved for active trades</p>
                    </Link>
                </div>

                <div class="wal-col-main">
                    <div class="wal-card wal-activity-card">
                        <div class="wal-section-head">
                            <h3 class="wal-lower-title">Recent Activity</h3>
                            <div class="wal-segmented">
                                <button
                                    v-for="filter in filters"
                                    :key="filter.key"
                                    type="button"
                                    class="wal-segmented__option"
                                    :class="{ 'wal-segmented__option--active': activeFilter === filter.key }"
                                    @click="activeFilter = filter.key"
                                >
                                    {{ filter.label }} <span class="wal-segmented__count">{{ tabCount(filter.key) }}</span>
                                </button>
                            </div>
                        </div>

                        <el-table :data="filteredTransactions" class="wal-table">
                            <template #empty>
                                <div class="wal-empty">
                                    <el-icon :size="24"><FolderOpened /></el-icon>
                                    <p>No transactions yet — your ledger will appear here once money moves in or out.</p>
                                </div>
                            </template>
                            <el-table-column width="56" align="center">
                                <template #default="{ row }">
                                    <span class="wal-dir" :class="row.is_credit ? 'wal-dir--in' : 'wal-dir--out'">
                                        <el-icon :size="13"><component :is="row.is_credit ? Top : Bottom" /></el-icon>
                                    </span>
                                </template>
                            </el-table-column>
                            <el-table-column label="Description" min-width="220">
                                <template #default="{ row }">
                                    <div class="wal-cell-desc">
                                        <span class="wal-cell-desc__text">{{ row.description || typeLabel(row.type) }}</span>
                                        <span class="wal-cell-desc__meta">
                                            {{ typeLabel(row.type) }}<template v-if="row.counterparty_name"> · {{ row.counterparty_name }}</template>
                                        </span>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column label="Status" width="130">
                                <template #default="{ row }">
                                    <span class="wal-badge" :class="statusTone[row.status] ?? 'wal-badge--muted'">
                                        <i></i> {{ row.status }}
                                    </span>
                                </template>
                            </el-table-column>
                            <el-table-column label="Date" width="160">
                                <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                            </el-table-column>
                            <el-table-column label="Amount" width="170" align="right">
                                <template #default="{ row }">
                                    <span class="wal-amount" :class="row.is_credit ? 'wal-text-green' : 'wal-text-red'">
                                        {{ row.is_credit ? '+' : '−' }}{{ formatMoney(row.amount, row.currency) }}
                                    </span>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Deposit modal ────────────────────────────────────────────── -->
        <DepositModal v-model="depositOpen" :currency="wallet.currency" />

        <!-- ── Transfer to escrow modal ─────────────────────────────────── -->
        <EscrowTransferModal v-model="transferOpen" :currency="wallet.currency" :available-balance="wallet.available_balance" />

        <!-- ── Withdraw modal ───────────────────────────────────────────── -->
        <WithdrawModal v-model="withdrawOpen" :currency="wallet.currency" :available-balance="wallet.available_balance" />
    </DesignPreviewLayout>
</template>

<style scoped>
.wal-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
}

.wal-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
}

/* ── Header ──────────────────────────────────────────────────────────── */
.wal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.wal-title {
    font-size: clamp(1.375rem, 1.05rem + 1.2vw, 1.75rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--dp-on-surface);
    margin: 0 0 4px;
}

.wal-subtitle {
    font-size: 0.9375rem;
    color: var(--dp-on-surface-variant);
    margin: 0;
    max-width: 560px;
    line-height: 1.5;
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.wal-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border-radius: 10px;
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    padding: 10px 16px;
    cursor: pointer;
    border: 1px solid var(--dp-outline-variant);
    background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface);
    transition: background 0.15s ease, opacity 0.15s ease;
    white-space: nowrap;
}

.wal-btn:hover:not(:disabled) { background: var(--dp-surface-container-low); }
.wal-btn:disabled { cursor: not-allowed; opacity: 0.5; }
.wal-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.wal-btn--primary {
    background: var(--dp-primary);
    border-color: transparent;
    color: var(--dp-on-primary);
    border-radius: 999px;
    padding: 10px 20px;
}

.wal-btn--primary:hover:not(:disabled) { opacity: 0.88; background: var(--dp-primary); }

.wal-btn--sm { flex: 1; justify-content: center; padding: 8px 12px; font-size: 0.75rem; }

.wal-status {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: capitalize;
    letter-spacing: 0.01em;
}

.wal-status--on { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.wal-status--off { background: var(--dp-error-container); color: var(--dp-error); }

.wal-text-green { color: var(--dp-secondary); }
.wal-text-red { color: var(--dp-error); }

.wal-card-title { font-size: 1.0625rem; font-weight: 700; color: var(--dp-on-surface); margin: 6px 0 2px; }
.wal-card-sub { font-size: 0.8125rem; color: var(--dp-on-surface-variant); margin: 0; }

/* ── Top grid: balance + cash flow ──────────────────────────────────── */
.wal-top-grid {
    display: grid;
    grid-template-columns: 5fr 7fr;
    gap: 1.25rem;
    align-items: stretch;
}

.wal-top-grid > *,
.wal-lower-grid > * {
    min-width: 0;
}

.wal-balance-card {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
}

.wal-balance-head { display: flex; flex-direction: column; align-items: flex-start; gap: 2px; }

.wal-balance-amount {
    margin: 1.25rem 0;
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
}

.wal-balance-amount__int {
    font-size: clamp(1.75rem, 1.2rem + 2.2vw, 2.5rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--dp-primary);
    font-variant-numeric: tabular-nums;
}

.wal-balance-amount__dec {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--dp-on-surface-variant);
    font-variant-numeric: tabular-nums;
}

.wal-balance-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 1.25rem;
}

.wal-balance-footer {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--dp-outline-variant);
    margin-top: auto;
}

.wal-balance-stat { display: flex; flex-direction: column; gap: 3px; }
.wal-balance-stat__label { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-on-surface-variant); }
.wal-balance-stat__val { font-size: 0.9375rem; font-weight: 700; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }

/* ── Cash flow chart ─────────────────────────────────────────────────── */
.wal-cashflow-card { padding: 1.5rem; display: flex; flex-direction: column; }

.wal-cashflow-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.wal-chart {
    flex: 1;
    display: flex;
    align-items: flex-end;
    gap: clamp(8px, 2vw, 24px);
    min-height: 160px;
}

.wal-chart__col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 10px; height: 100%; }
.wal-chart__bar-track { flex: 1; width: 100%; display: flex; align-items: flex-end; }
.wal-chart__bar { width: 100%; border-radius: 4px 4px 0 0; transition: height 0.3s ease; min-height: 4px; }
.wal-chart__bar--in { background: var(--dp-secondary); }
.wal-chart__bar--out { background: var(--dp-error); opacity: 0.85; }
.wal-chart__label { font-size: 0.75rem; color: var(--dp-on-surface-variant); font-weight: 600; }

/* ── Segmented toggle (shared: cash flow + activity filters) ───────────── */
.wal-segmented {
    display: flex;
    gap: 2px;
    padding: 3px;
    background: var(--dp-surface-container-low);
    border-radius: 10px;
    flex-wrap: wrap;
}

.wal-segmented__option {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: var(--dp-on-surface-variant);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 7px 12px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
}

.wal-segmented__option:hover { color: var(--dp-on-surface); }
.wal-segmented__option:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.wal-segmented__option--active {
    background: var(--dp-surface-container-lowest);
    color: var(--dp-secondary);
    box-shadow: 0 1px 3px rgba(39, 19, 16, 0.1);
}

.wal-segmented__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    border-radius: 999px;
    background: var(--dp-secondary-container);
    color: var(--dp-on-secondary-container);
    font-size: 0.625rem;
    font-weight: 800;
}

/* ── Lower grid: wallets + activity ─────────────────────────────────── */
.wal-lower-grid {
    display: grid;
    grid-template-columns: 4fr 8fr;
    gap: 1.25rem;
    align-items: start;
}

.wal-col-wallets { display: flex; flex-direction: column; gap: 1rem; }
.wal-lower-head { display: flex; align-items: center; justify-content: space-between; }
.wal-lower-title { font-size: 1.0625rem; font-weight: 700; color: var(--dp-on-surface); margin: 0; }

.wal-account-tile {
    background: var(--dp-surface-container-lowest);
    border-radius: 18px;
    box-shadow: var(--dp-card-shadow);
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 4px;
    text-decoration: none;
    color: inherit;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.wal-account-tile:hover { transform: translateY(-2px); box-shadow: 0 2px 4px rgba(39,19,16,.05), 0 12px 24px -14px rgba(39,19,16,.18); }
.wal-account-tile:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }
.wal-account-tile--dark:focus-visible { outline-color: var(--dp-on-primary); }

.wal-account-tile--dark { background: var(--dp-primary); color: var(--dp-on-primary); }

.wal-account-tile__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }

.wal-account-tile__icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: var(--dp-secondary-container);
    color: var(--dp-on-secondary-container);
    display: flex;
    align-items: center;
    justify-content: center;
}

.wal-account-tile__icon--dark { background: rgba(255,255,255,.12); color: var(--dp-on-primary); }

.wal-account-tile__badge {
    font-size: 0.6875rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
}

.wal-account-tile__badge--dark { background: rgba(255,255,255,.14); color: var(--dp-on-primary); }

.wal-account-tile__name { font-size: 0.9375rem; font-weight: 700; margin: 0; }
.wal-account-tile__val { font-size: 1.125rem; font-weight: 700; margin: 0; font-variant-numeric: tabular-nums; }
.wal-account-tile__note { font-size: 0.75rem; opacity: 0.75; margin: 2px 0 0; }

/* ── Recent activity ─────────────────────────────────────────────────── */
.wal-col-main { min-width: 0; }

.wal-activity-card { padding: 1.5rem; overflow-x: auto; }

.wal-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}

.wal-table {
    --el-table-header-bg-color: var(--dp-surface-container-low);
    --el-table-header-text-color: var(--dp-on-surface-variant);
    --el-table-tr-bg-color: transparent;
    --el-table-row-hover-bg-color: var(--dp-surface-container-low);
    font-family: var(--dp-font-sans);
}

/* Borderless: Element's default table draws a hairline under every header
   and body cell — dropped in favor of generous row padding + hover shading
   for separation, matching this app's card-based (no internal dividers)
   convention. */
.wal-table :deep(.el-table__cell) { border-bottom: none !important; }
.wal-table :deep(.el-table__inner-wrapper::before),
.wal-table :deep(.el-table__inner-wrapper::after),
.wal-table :deep(.el-table::before) { display: none !important; }

.wal-table :deep(.el-table__header) th {
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 12px 0;
}

.wal-table :deep(.cell) { padding: 0 14px; line-height: 1.5; }
.wal-table :deep(td.el-table__cell) { padding: 16px 0; }
.wal-table :deep(.el-table__row) { transition: background 0.15s ease; }

.wal-dir {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 999px;
}

.wal-dir--in { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.wal-dir--out { background: var(--dp-error-container); color: var(--dp-error); }

.wal-cell-desc { display: flex; flex-direction: column; gap: 4px; }
.wal-cell-desc__text { font-size: 0.8125rem; font-weight: 600; color: var(--dp-on-surface); letter-spacing: -0.005em; }
.wal-cell-desc__meta { font-size: 0.75rem; font-weight: 500; color: var(--dp-on-surface-variant); letter-spacing: 0.01em; }

.wal-amount {
    font-size: 0.8125rem;
    font-weight: 700;
    font-family: var(--dp-font-mono);
    font-variant-numeric: tabular-nums;
}

.wal-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    padding: 5px 10px 5px 8px;
    text-transform: capitalize;
    white-space: nowrap;
}

.wal-badge i { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

.wal-badge--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.wal-badge--amber { background: #fef3c7; color: #92400e; }
.wal-badge--red { background: var(--dp-error-container); color: var(--dp-error); }
.wal-badge--muted { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); }

.wal-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 2.5rem 1rem;
    color: var(--dp-on-surface-variant);
}

.wal-empty .el-icon { color: var(--dp-outline-variant); }

.wal-empty p {
    max-width: 320px;
    margin: 0;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.6;
    text-align: center;
}

/* ── Reduced motion ──────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .wal-account-tile,
    .wal-chart__bar {
        transition: none;
    }
    .wal-account-tile:hover { transform: none; }
}

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .wal-top-grid { grid-template-columns: 1fr; }
    .wal-lower-grid { grid-template-columns: 1fr; }
    .wal-col-wallets { flex-direction: row; }
    .wal-account-tile { flex: 1; }
}

@media (max-width: 640px) {
    .wal-col-wallets { flex-direction: column; }
}
</style>
