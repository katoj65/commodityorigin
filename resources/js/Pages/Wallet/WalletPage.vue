<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Top, Bottom, Promotion } from '@element-plus/icons-vue';

const props = defineProps({
    wallet: { type: Object, required: true },
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
    transfer_in: 'Transfer In',
    transfer_out: 'Transfer Out',
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

/* ── Transfer ────────────────────────────────────────────────────────── */
const transferOpen = ref(false);

const form = useForm({
    recipient_email: '',
    amount: '',
    description: '',
});

function openTransfer() {
    form.reset();
    form.clearErrors();
    transferOpen.value = true;
}

function submitTransfer() {
    form.post(route('wallet.transfer'), {
        preserveScroll: true,
        onSuccess: () => {
            transferOpen.value = false;
            form.reset();
        },
    });
}

/* ── Withdraw ────────────────────────────────────────────────────────── */
const withdrawOpen = ref(false);

const withdrawForm = useForm({
    amount: '',
    description: '',
});

function openWithdraw() {
    withdrawForm.reset();
    withdrawForm.clearErrors();
    withdrawOpen.value = true;
}

function submitWithdraw() {
    withdrawForm.post(route('wallet.withdraw'), {
        preserveScroll: true,
        onSuccess: () => {
            withdrawOpen.value = false;
            withdrawForm.reset();
        },
    });
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
                    <p class="wal-subtitle">Your balance, and the ledger of every deposit, transfer, and order payment.</p>
                </div>
                <div class="wal-page-header__actions">
                    <button type="button" class="wal-btn wal-btn--primary" @click="openTransfer">
                        <el-icon :size="14"><Promotion /></el-icon> Transfer
                    </button>
                    <button type="button" class="wal-btn" disabled title="Coming soon — no payment gateway connected yet">
                        <el-icon :size="14"><Top /></el-icon> Deposit
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
                        Available Balance
                        <span class="wal-status" :class="wallet.status === 'active' ? 'wal-status--on' : 'wal-status--off'">{{ wallet.status }}</span>
                    </span>
                    <strong class="wal-kpi__val wal-kpi__val--lg">{{ formatMoney(wallet.available_balance) }}</strong>
                </div>
                <div class="wal-kpi">
                    <span class="wal-kpi__label">Locked</span>
                    <strong class="wal-kpi__val">{{ formatMoney(wallet.locked_balance) }}</strong>
                </div>
                <div class="wal-kpi">
                    <span class="wal-kpi__label">Total Received</span>
                    <strong class="wal-kpi__val wal-text-green">{{ kpis.received }}</strong>
                </div>
                <div class="wal-kpi">
                    <span class="wal-kpi__label">Total Spent</span>
                    <strong class="wal-kpi__val wal-text-red">{{ kpis.spent }}</strong>
                </div>
                <div class="wal-kpi">
                    <span class="wal-kpi__label">Transactions</span>
                    <strong class="wal-kpi__val">{{ kpis.count }}</strong>
                </div>
            </div>

            <div class="wal-body">
                <div class="wal-section">
                    <el-tabs v-model="activeFilter" class="wal-tabs">
                        <el-tab-pane v-for="f in filters" :key="f.key" :name="f.key">
                            <template #label>
                                <span class="wal-tab-label">
                                    {{ f.label }}
                                    <span v-if="tabCount(f.key)" class="wal-tab-count">{{ tabCount(f.key) }}</span>
                                </span>
                            </template>
                        </el-tab-pane>
                    </el-tabs>

                    <el-table
                        :data="filteredTransactions"
                        class="wal-table"
                        empty-text="No transactions yet."
                    >
                        <el-table-column label="" width="46">
                            <template #default="{ row }">
                                <span class="wal-dir" :class="row.is_credit ? 'wal-dir--in' : 'wal-dir--out'">
                                    <el-icon :size="12"><component :is="row.is_credit ? Top : Bottom" /></el-icon>
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
                        <el-table-column label="Status" width="110">
                            <template #default="{ row }">
                                <span class="wal-badge" :class="statusTone[row.status] ?? 'wal-badge--muted'">{{ row.status }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Date" width="150">
                            <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                        </el-table-column>
                        <el-table-column label="Amount" width="150" align="right">
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

        <!-- ── Transfer modal ───────────────────────────────────────────── -->
        <el-dialog v-model="transferOpen" width="420px" destroy-on-close align-center :show-close="true" class="wal-modal">
            <template #header>
                <div class="wal-modal-head">
                    <div class="wal-modal-head__icon"><el-icon :size="18"><Promotion /></el-icon></div>
                    <div>
                        <div class="wal-modal-eyebrow">Send Money</div>
                        <div class="wal-modal-title">Transfer Funds</div>
                    </div>
                </div>
            </template>

            <div class="wal-modal-body">
                <form id="wal-transfer-form" class="wal-form" @submit.prevent="submitTransfer">
                    <div class="wal-form__field">
                        <label class="wal-form__label">Recipient Email</label>
                        <input v-model="form.recipient_email" type="email" required placeholder="name@example.com" class="wal-form__input">
                        <p v-if="form.errors.recipient_email" class="wal-form__error">{{ form.errors.recipient_email }}</p>
                    </div>

                    <div class="wal-form__field">
                        <label class="wal-form__label">Amount ({{ wallet.currency }})</label>
                        <input v-model="form.amount" type="number" step="0.01" min="0.01" required placeholder="0.00" class="wal-form__input">
                        <p v-if="form.errors.amount" class="wal-form__error">{{ form.errors.amount }}</p>
                        <p v-else class="wal-form__hint">Available: {{ formatMoney(wallet.available_balance) }}</p>
                    </div>

                    <div class="wal-form__field">
                        <label class="wal-form__label">Note (optional)</label>
                        <input v-model="form.description" type="text" maxlength="255" placeholder="What's this for?" class="wal-form__input">
                    </div>

                    <p v-if="form.errors.wallet" class="wal-form__error">{{ form.errors.wallet }}</p>
                </form>
            </div>

            <template #footer>
                <div class="wal-modal-footer">
                    <button type="button" class="wal-modal-btn wal-modal-btn--outline" @click="transferOpen = false">Cancel</button>
                    <button type="submit" form="wal-transfer-form" :disabled="form.processing" class="wal-modal-btn wal-modal-btn--primary">
                        <el-icon :size="14"><Promotion /></el-icon> {{ form.processing ? 'Sending…' : 'Send Transfer' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Withdraw modal ───────────────────────────────────────────── -->
        <el-dialog v-model="withdrawOpen" width="420px" destroy-on-close align-center :show-close="true" class="wal-modal">
            <template #header>
                <div class="wal-modal-head">
                    <div class="wal-modal-head__icon"><el-icon :size="18"><Bottom /></el-icon></div>
                    <div>
                        <div class="wal-modal-eyebrow">Cash Out</div>
                        <div class="wal-modal-title">Withdraw Funds</div>
                    </div>
                </div>
            </template>

            <div class="wal-modal-body">
                <form id="wal-withdraw-form" class="wal-form" @submit.prevent="submitWithdraw">
                    <div class="wal-form__field">
                        <label class="wal-form__label">Amount ({{ wallet.currency }})</label>
                        <input v-model="withdrawForm.amount" type="number" step="0.01" min="0.01" required placeholder="0.00" class="wal-form__input">
                        <p v-if="withdrawForm.errors.amount" class="wal-form__error">{{ withdrawForm.errors.amount }}</p>
                        <p v-else class="wal-form__hint">Available: {{ formatMoney(wallet.available_balance) }}</p>
                    </div>

                    <div class="wal-form__field">
                        <label class="wal-form__label">Note (optional)</label>
                        <input v-model="withdrawForm.description" type="text" maxlength="255" placeholder="What's this for?" class="wal-form__input">
                    </div>

                    <p v-if="withdrawForm.errors.wallet" class="wal-form__error">{{ withdrawForm.errors.wallet }}</p>
                </form>
            </div>

            <template #footer>
                <div class="wal-modal-footer">
                    <button type="button" class="wal-modal-btn wal-modal-btn--outline" @click="withdrawOpen = false">Cancel</button>
                    <button type="submit" form="wal-withdraw-form" :disabled="withdrawForm.processing" class="wal-modal-btn wal-modal-btn--primary">
                        <el-icon :size="14"><Bottom /></el-icon> {{ withdrawForm.processing ? 'Withdrawing…' : 'Withdraw' }}
                    </button>
                </div>
            </template>
        </el-dialog>
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
    background: #ffffff;
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
    padding: 1.75rem 1.5rem 0;
}

.wal-page-header__left {
    max-width: 560px;
}

.wal-page-header__actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.wal-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.wal-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.wal-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

/* ── Header buttons ──────────────────────────────────────────────────── */
.wal-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 14px;
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
    margin-top: 1.5rem;
    scrollbar-width: none;
}

.wal-kpi-strip::-webkit-scrollbar { display: none; }

.wal-kpi {
    flex: 1;
    min-width: 130px;
    padding: 1rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
    border-right: 1px solid var(--border);
}

.wal-kpi--primary { min-width: 220px; flex: 1.4; }

.wal-kpi:last-child { border-right: none; }

.wal-kpi__label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
}

.wal-kpi__val {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--on-surface);
    letter-spacing: -0.01em;
}

.wal-kpi__val--lg { font-size: 1.75rem; }

.wal-status {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 1px 7px;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: capitalize;
    letter-spacing: normal;
}

.wal-status--on { background: #dcfce7; color: #166534; }
.wal-status--off { background: #fee2e2; color: #991b1b; }

.wal-text-green { color: #166534; }
.wal-text-red { color: #991b1b; }

/* ── Body ────────────────────────────────────────────────────────────── */
.wal-body {
    padding: 1.5rem 0 3rem;
}

.wal-section {
    background: transparent;
}

/* ── Category tabs ───────────────────────────────────────────────────── */
.wal-tabs { padding: 0 1.5rem; }
.wal-tabs :deep(.el-tabs__header) { margin: 0; }
.wal-tabs :deep(.el-tabs__nav-wrap::after) { background-color: var(--border); }
.wal-tabs :deep(.el-tabs__item) {
    font-weight: 700;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    height: 44px;
}
.wal-tabs :deep(.el-tabs__item.is-active) { color: var(--green); }
.wal-tabs :deep(.el-tabs__active-bar) { background-color: var(--green); }

.wal-tab-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.wal-tab-count {
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
.wal-table {
    --el-table-border-color: var(--border);
    --el-table-header-bg-color: var(--surface-low);
    --el-table-header-text-color: var(--on-surface-var);
    font-family: 'Manrope', system-ui, sans-serif;
}

.wal-table :deep(.el-table__header) th {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.wal-table :deep(.el-table__inner-wrapper::before) { display: none; }
.wal-table :deep(.el-table__header-wrapper th:first-child .cell),
.wal-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.5rem; }
.wal-table :deep(.el-table__header-wrapper th:last-child .cell),
.wal-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.5rem; }

.wal-dir {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 999px;
}

.wal-dir--in { background: #dcfce7; color: #166534; }
.wal-dir--out { background: #fee2e2; color: #991b1b; }

.wal-cell-desc {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.wal-cell-desc__text {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
}

.wal-cell-desc__meta {
    font-size: 0.75rem;
    color: var(--on-surface-var);
}

.wal-amount {
    font-size: 0.8125rem;
    font-weight: 600;
    font-family: 'IBM Plex Mono', monospace;
}

.wal-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 4px 10px;
    text-transform: capitalize;
    white-space: nowrap;
}

.wal-badge--green { background: #dcfce7; color: #166534; }
.wal-badge--amber { background: #fef3c7; color: #92400e; }
.wal-badge--red { background: #fee2e2; color: #991b1b; }
.wal-badge--muted { background: #f3f4f6; color: #6b7280; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .wal-page-header { padding: 1.25rem 1.25rem 0; }
    .wal-body { padding: 1.25rem 0 3rem; }
    .wal-tabs { padding: 0 1.25rem; }
    .wal-table :deep(.el-table__header-wrapper th:first-child .cell),
    .wal-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
    .wal-table :deep(.el-table__header-wrapper th:last-child .cell),
    .wal-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }
}

/* ── Modals ──────────────────────────────────────────────────────────────
   el-dialog teleports to <body>, but :deep() still pierces the internal
   Element Plus DOM from here — same pattern as OrderShowPage's review modal. */
:deep(.el-dialog.wal-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.wal-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.wal-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.wal-modal .el-dialog__footer) { padding: 0; }

.wal-modal-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.wal-modal-head__icon {
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

.wal-modal-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.wal-modal-title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.wal-modal-body {
    padding: 20px 24px;
}

.wal-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.wal-form__field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.wal-form__label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
}

.wal-form__input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 0.8125rem;
    font-family: 'Manrope', system-ui, sans-serif;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.wal-form__input:focus {
    outline: none;
    border-color: #004532;
    box-shadow: 0 0 0 3px rgba(0, 69, 50, 0.08);
}

.wal-form__error { font-size: 0.6875rem; color: #991b1b; margin: 0; }
.wal-form__hint { font-size: 0.6875rem; color: #9ca3af; margin: 0; font-family: 'IBM Plex Mono', monospace; }

.wal-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.wal-modal-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s ease, background 0.15s ease;
}

.wal-modal-btn--outline {
    background: #fff;
    border-color: #e5e7eb;
    color: #111827;
}

.wal-modal-btn--outline:hover { background: #f9fafb; }

.wal-modal-btn--primary {
    background: linear-gradient(135deg, #004532, #065f46);
    color: #fff;
}

.wal-modal-btn--primary:hover:not(:disabled) { opacity: 0.9; }
.wal-modal-btn--primary:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
