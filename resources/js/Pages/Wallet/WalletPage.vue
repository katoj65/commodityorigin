<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DepositModal from '@/Components/DepositModal.vue';
import WithdrawModal from '@/Components/WithdrawModal.vue';
import EscrowTransferModal from '@/Components/EscrowTransferModal.vue';
import {
    Top, Bottom, Promotion, Wallet as WalletIcon, Lock, List,
    CircleCheck, Clock, CircleClose, FolderOpened, InfoFilled,
    Sort, Document, Calendar, Money,
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

const statusIcon = {
    completed: CircleCheck,
    pending: Clock,
    failed: CircleClose,
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
    <AppLayout title="Wallet" full-width flush :show-banner="false">

        <div class="wal-page">
            <!-- ── Two-column layout: 70% transactions / 30% balance card ── -->
            <div class="wal-columns">
                <!-- 70% -->
                <div class="wal-col-main">
                    <div class="wal-section-head">
                        <div>
                            <h1 class="wal-section-title pt-3">Wallet</h1>
                            <p class="wal-section-sub mt-2">Transaction details</p>
                        </div>
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
                        <el-table-column width="50" align="center">
                            <template #header>
                                <el-icon :size="13" class="wal-th__solo"><Sort /></el-icon>
                            </template>
                            <template #default="{ row }">
                                <span class="wal-dir" :class="row.is_credit ? 'wal-dir--in' : 'wal-dir--out'">
                                    <el-icon :size="12"><component :is="row.is_credit ? Top : Bottom" /></el-icon>
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column min-width="220">
                            <template #header>
                                <span class="wal-th"><el-icon :size="12"><Document /></el-icon> Description</span>
                            </template>
                            <template #default="{ row }">
                                <div class="wal-cell-desc">
                                    <span class="wal-cell-desc__text">{{ row.description || typeLabel(row.type) }}</span>
                                    <span class="wal-cell-desc__meta">
                                        {{ typeLabel(row.type) }}<template v-if="row.counterparty_name"> · {{ row.counterparty_name }}</template>
                                    </span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column width="130">
                            <template #header>
                                <span class="wal-th"><el-icon :size="12"><CircleCheck /></el-icon> Status</span>
                            </template>
                            <template #default="{ row }">
                                <span class="wal-badge" :class="statusTone[row.status] ?? 'wal-badge--muted'">
                                    <el-icon :size="11"><component :is="statusIcon[row.status] ?? InfoFilled" /></el-icon> {{ row.status }}
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column width="160">
                            <template #header>
                                <span class="wal-th"><el-icon :size="12"><Calendar /></el-icon> Date</span>
                            </template>
                            <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                        </el-table-column>
                        <el-table-column width="170" align="right">
                            <template #header>
                                <span class="wal-th"><el-icon :size="12"><Money /></el-icon> Amount</span>
                            </template>
                            <template #default="{ row }">
                                <span class="wal-amount" :class="row.is_credit ? 'wal-text-green' : 'wal-text-red'">
                                    {{ row.is_credit ? '+' : '−' }}{{ formatMoney(row.amount, row.currency) }}
                                </span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>

                <!-- 30% -->
                <div class="wal-col-side pt-4">
                    <div class="wal-side-card">
                        <div class="wal-side-balance">
                            <span class="wal-side-balance__label">
                                <el-icon :size="13"><WalletIcon /></el-icon> Available Balance
                                <span class="wal-status" :class="wallet.status === 'active' ? 'wal-status--on' : 'wal-status--off'">{{ wallet.status }}</span>
                            </span>
                            <strong class="wal-side-balance__val">{{ formatMoney(wallet.available_balance) }}</strong>
                        </div>

                        <div class="wal-side-actions">
                            <button type="button" class="wal-btn wal-btn--primary wal-btn--block" @click="openDeposit">
                                <el-icon :size="14"><Top /></el-icon> Deposit
                            </button>
                            <button type="button" class="wal-btn wal-btn--block" @click="openTransfer">
                                <el-icon :size="14"><Promotion /></el-icon> Transfer
                            </button>
                            <button type="button" class="wal-btn wal-btn--block" @click="openWithdraw">
                                <el-icon :size="14"><Bottom /></el-icon> Withdraw
                            </button>
                        </div>

                        <div class="wal-side-divider" />

                        <div class="wal-side-metrics">
                            <Link :href="route('escrow.index')" class="wal-side-metric wal-side-metric--link">
                                <span class="wal-side-metric__icon wal-side-metric__icon--amber"><el-icon :size="14"><Lock /></el-icon></span>
                                <span class="wal-side-metric__body">
                                    <span class="wal-side-metric__label">In Escrow</span>
                                    <strong class="wal-side-metric__val">{{ formatMoney(escrowWallet.balance, escrowWallet.currency) }}</strong>
                                </span>
                            </Link>
                            <div class="wal-side-metric">
                                <span class="wal-side-metric__icon wal-side-metric__icon--green"><el-icon :size="14"><Top /></el-icon></span>
                                <span class="wal-side-metric__body">
                                    <span class="wal-side-metric__label">Total Received</span>
                                    <strong class="wal-side-metric__val wal-text-green">{{ kpis.received }}</strong>
                                </span>
                            </div>
                            <div class="wal-side-metric">
                                <span class="wal-side-metric__icon wal-side-metric__icon--red"><el-icon :size="14"><Bottom /></el-icon></span>
                                <span class="wal-side-metric__body">
                                    <span class="wal-side-metric__label">Total Spent</span>
                                    <strong class="wal-side-metric__val wal-text-red">{{ kpis.spent }}</strong>
                                </span>
                            </div>
                            <div class="wal-side-metric">
                                <span class="wal-side-metric__icon"><el-icon :size="14"><List /></el-icon></span>
                                <span class="wal-side-metric__body">
                                    <span class="wal-side-metric__label">Total Transactions</span>
                                    <strong class="wal-side-metric__val">{{ kpis.count }}</strong>
                                </span>
                            </div>
                        </div>
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
    </AppLayout>
</template>

<style scoped>
.wal-page {
    --green: #004532;
    --red: #991b1b;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}


/* ── Header buttons ──────────────────────────────────────────────────── */
.wal-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border-radius: 9px;
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    padding: 9px 16px;
    cursor: pointer;
    border: 1px solid var(--border);
    background: #fff;
    color: var(--on-surface);
    transition: opacity 0.15s ease, background 0.15s ease;
}

.wal-btn:hover:not(:disabled) { background: var(--surface-low); }
.wal-btn:disabled { cursor: not-allowed; opacity: 0.5; }

.wal-btn--primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border-color: transparent;
    color: #fff;
}

.wal-btn--primary:hover:not(:disabled) { opacity: 0.9; background: linear-gradient(135deg, #004532, #065f46); }

.wal-btn--block { width: 100%; justify-content: center; }

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

.wal-status--on { background: #dcfce7; color: #166534; }
.wal-status--off { background: #fee2e2; color: #991b1b; }

.wal-text-green { color: #166534; }
.wal-text-red { color: #991b1b; }

/* ── Two-column layout ───────────────────────────────────────────────── */
.wal-columns {
    display: grid;
    grid-template-columns: 7fr 3fr;
    align-items: start;
}

.wal-col-main {
    padding: 0 0 3rem;
    border-right: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    min-width: 0;
}

.wal-col-side {
    padding: 0 1.5rem 3rem;
    min-width: 0;
}

/* ── Side card: balance, actions, metrics ─────────────────────────────── */
.wal-side-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    position: sticky;
    top: 88px;
}

.wal-side-balance {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 1.25rem;
}

.wal-side-balance__label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--on-surface-var);
}

.wal-side-balance__val {
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    font-variant-numeric: tabular-nums;
}

.wal-side-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 1.5rem;
}

.wal-side-divider {
    height: 1px;
    background: var(--border);
    margin-bottom: 1.25rem;
}

.wal-side-metrics {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.wal-side-metric {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    margin: 0 -8px;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s ease;
}

.wal-side-metric--link:hover { background: var(--surface-low); }

.wal-side-metric__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 9px;
    flex-shrink: 0;
    background: var(--surface-low);
    color: var(--on-surface-var);
}

.wal-side-metric__icon--green { background: #dcfce7; color: #166534; }
.wal-side-metric__icon--red { background: #fee2e2; color: #991b1b; }
.wal-side-metric__icon--amber { background: #fef3c7; color: #92400e; }

.wal-side-metric__body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.wal-side-metric__label {
    font-size: 0.6875rem;
    font-weight: 600;
    color: var(--on-surface-var);
}

.wal-side-metric__val {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface);
    letter-spacing: -0.01em;
    font-variant-numeric: tabular-nums;
}

/* ── Section head + filter ─────────────────────────────────────────────── */
.wal-section-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin: 0 0 0.75rem;
    padding: 0 1.5rem;
}

.wal-section-title {
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    line-height: 1.2;
    color: var(--on-surface);
    margin: 0;
}

.wal-section-sub {
    font-size: 0.75rem;
    color: var(--on-surface-var);
    line-height: 1.3;
    margin: 0;
}

.wal-segmented {
    display: flex;
    gap: 2px;
    padding: 3px;
    background: var(--surface-low);
    border-radius: 10px;
}

.wal-segmented__option {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: var(--on-surface-var);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 7px 12px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
}

.wal-segmented__option:hover { color: var(--on-surface); }

.wal-segmented__option--active {
    background: #fff;
    color: var(--green);
    box-shadow: 0 1px 3px rgba(17, 24, 39, 0.1);
}

.wal-segmented__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    border-radius: 999px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    font-size: 0.625rem;
    font-weight: 800;
}

.wal-segmented__option--active .wal-segmented__count { background: rgba(0, 69, 50, 0.14); }

/* ── Table ───────────────────────────────────────────────────────────── */
.wal-table {
    --el-table-border-color: var(--border);
    --el-table-header-bg-color: var(--surface-low);
    --el-table-header-text-color: var(--on-surface-var);
    font-family: 'Manrope', system-ui, sans-serif;
}

.wal-table :deep(.el-table__header) th {
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 11px 0;
}

.wal-table :deep(.cell) { padding: 0 12px; line-height: 1.5; }
.wal-table :deep(.el-table__inner-wrapper::before) { display: none; }
.wal-table :deep(td.el-table__cell) { padding: 12px 0; }
.wal-table :deep(.el-table__row:hover .el-table__cell) { background: var(--surface-low); }

.wal-th {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.wal-th .el-icon,
.wal-th__solo { color: var(--on-surface-var); opacity: 0.85; }

.wal-dir {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 999px;
}

.wal-dir--in { background: #dcfce7; color: #166534; }
.wal-dir--out { background: #fee2e2; color: #991b1b; }

.wal-cell-desc {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.wal-cell-desc__text {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
    letter-spacing: -0.005em;
}

.wal-cell-desc__meta {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--on-surface-var);
    letter-spacing: 0.01em;
}

.wal-amount {
    font-size: 0.8125rem;
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
    font-variant-numeric: tabular-nums;
}

.wal-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    padding: 5px 10px;
    text-transform: capitalize;
    white-space: nowrap;
}

.wal-badge--green { background: #dcfce7; color: #166534; }
.wal-badge--amber { background: #fef3c7; color: #92400e; }
.wal-badge--red { background: #fee2e2; color: #991b1b; }
.wal-badge--muted { background: #f3f4f6; color: #6b7280; }

.wal-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 2.5rem 1rem;
    color: var(--on-surface-var);
}

.wal-empty .el-icon { color: #d1d5db; }

.wal-empty p {
    max-width: 320px;
    margin: 0;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.6;
    text-align: center;
}

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1023.98px) {
    .wal-columns { grid-template-columns: 1fr; }
    .wal-col-main { border-right: none; }
    .wal-col-side {
        order: -1;
        border-bottom: 1px solid var(--border);
        padding-bottom: 1.75rem;
    }
    .wal-side-card { position: static; }
}

@media (max-width: 767.98px) {
    .wal-col-main { padding: 0 1.25rem 3rem; }
    .wal-col-side { padding: 1.25rem 1.25rem 1.75rem; }
}

</style>
