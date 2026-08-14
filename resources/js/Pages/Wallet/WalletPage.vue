<script setup>
import { computed, ref } from 'vue';
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
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="wal-page-header">
                <div class="wal-page-header__left">
                    <div class="wal-kicker">Payments · Bean Origin</div>
                    <h1 class="wal-title">Wallet</h1>
                    <p class="wal-subtitle">Your balance, plus a complete ledger of every deposit, transfer, and order payment.</p>
                </div>
                <div class="wal-page-header__actions">
                    <button type="button" class="wal-btn wal-btn--primary" @click="openDeposit">
                        <el-icon :size="14"><Top /></el-icon> Deposit
                    </button>
                    <button type="button" class="wal-btn" @click="openTransfer">
                        <el-icon :size="14"><Promotion /></el-icon> Transfer
                    </button>
                    <button type="button" class="wal-btn" @click="openWithdraw">
                        <el-icon :size="14"><Bottom /></el-icon> Withdraw
                    </button>
                </div>
            </div>

            <!-- ── Balance + overview strip ─────────────────────────────── -->
            <div class="wal-kpi-strip">
                <div class="wal-kpi wal-kpi--primary">
                    <span class="wal-kpi__label">
                        <el-icon :size="12"><WalletIcon /></el-icon> Available Balance
                        <span class="wal-status" :class="wallet.status === 'active' ? 'wal-status--on' : 'wal-status--off'">{{ wallet.status }}</span>
                    </span>
                    <strong class="wal-kpi__val wal-kpi__val--lg">{{ formatMoney(wallet.available_balance) }}</strong>
                </div>
                <div class="wal-kpi">
                    <span class="wal-kpi__label"><el-icon :size="12"><Lock /></el-icon> In Escrow</span>
                    <strong class="wal-kpi__val">{{ formatMoney(escrowWallet.balance, escrowWallet.currency) }}</strong>
                </div>
                <div class="wal-kpi">
                    <span class="wal-kpi__label"><el-icon :size="12"><Top /></el-icon> Total Received</span>
                    <strong class="wal-kpi__val wal-text-green">{{ kpis.received }}</strong>
                </div>
                <div class="wal-kpi">
                    <span class="wal-kpi__label"><el-icon :size="12"><Bottom /></el-icon> Total Spent</span>
                    <strong class="wal-kpi__val wal-text-red">{{ kpis.spent }}</strong>
                </div>
                <div class="wal-kpi">
                    <span class="wal-kpi__label"><el-icon :size="12"><List /></el-icon> Total Transactions</span>
                    <strong class="wal-kpi__val">{{ kpis.count }}</strong>
                </div>
            </div>

            <div class="wal-body">
                <div class="wal-section">
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

/* ── Page header ─────────────────────────────────────────────────────── */
.wal-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 2rem 1.5rem 0;
}

.wal-page-header__left {
    max-width: 560px;
}

.wal-page-header__actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.wal-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--green);
    margin-bottom: 6px;
}

.wal-title {
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.025em;
    line-height: 1.2;
    margin: 0 0 0.375rem;
}

.wal-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.55;
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

/* ── Overview strip ──────────────────────────────────────────────────── */
.wal-kpi-strip {
    display: flex;
    overflow-x: auto;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-top: 1.75rem;
    scrollbar-width: none;
}

.wal-kpi-strip::-webkit-scrollbar { display: none; }

.wal-kpi {
    flex: 1;
    min-width: 130px;
    padding: 1rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 5px;
    border-right: 1px solid var(--border);
}

.wal-kpi--primary { min-width: 220px; flex: 1.4; }

.wal-kpi:last-child { border-right: none; }

.wal-kpi__label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--on-surface-var);
}

.wal-kpi__val {
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--on-surface);
    letter-spacing: -0.01em;
    font-variant-numeric: tabular-nums;
}

.wal-kpi__val--lg {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

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

/* ── Body ────────────────────────────────────────────────────────────── */
.wal-body {
    padding: 0 0 3rem;
}

.wal-section {
    background: transparent;
}

/* ── Section head + filter ─────────────────────────────────────────────── */
.wal-section-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 0 1.5rem;
    margin-bottom: 1rem;
}

.wal-section-title {
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: var(--on-surface);
    margin: 0 0 2px;
}

.wal-section-sub {
    font-size: 0.75rem;
    color: var(--on-surface-var);
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
.wal-table :deep(.el-table__header-wrapper th:first-child .cell),
.wal-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.5rem; }
.wal-table :deep(.el-table__header-wrapper th:last-child .cell),
.wal-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.5rem; }

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
@media (max-width: 767.98px) {
    .wal-page-header { padding: 1.5rem 1.25rem 0; }
    .wal-body { padding: 0 0 3rem; }
    .wal-section-head { padding: 0 1.25rem; }
    .wal-table :deep(.el-table__header-wrapper th:first-child .cell),
    .wal-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
    .wal-table :deep(.el-table__header-wrapper th:last-child .cell),
    .wal-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }
}

</style>
