<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    ArrowLeft, Tickets, Box, Coin, Calendar, CircleCheck, CircleClose,
    Close, Wallet as WalletIcon, CreditCard, Van, Lock,
} from '@element-plus/icons-vue';

const props = defineProps({
    purchase: { type: Object, required: true },
});

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

const paymentIcon = computed(() => (props.purchase.payment_method === 'wallet' ? WalletIcon : CreditCard));
const isCompleted = computed(() => props.purchase.status === 'completed');
const totalItems = computed(() => props.purchase.items.reduce((sum, item) => sum + Number(item.quantity || 0), 0));

/* ── Cancel ──────────────────────────────────────────────────────────── */
const cancelOpen = ref(false);

function confirmCancel() {
    router.patch(route('purchases.cancel', props.purchase.id), {}, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :title="`Purchase ${purchase.order_number}`" full-width flush :show-banner="false">
        <Head :title="`Purchase ${purchase.order_number}`" />

        <div class="prf-page">
            <!-- ── Header — edge-to-edge, white, flush top/left/right ────── -->
            <section class="prf-header">
                <div class="prf-header__inner">

                    <div class="prf-header__row">
                        <div>
                            <div class="prf-kicker">Purchase Receipt · Bean Origin</div>
                            <h1 class="prf-title">{{ purchase.order_number }}</h1>
                        </div>
                        <div class="prf-header__right">
                            <span class="prf-status" :class="isCompleted ? 'is-completed' : 'is-cancelled'">
                                <el-icon :size="13"><component :is="isCompleted ? CircleCheck : CircleClose" /></el-icon>
                                {{ isCompleted ? 'Completed' : 'Cancelled' }}
                            </span>
                            <span class="prf-header__total">{{ formatMoney(purchase.total_amount, purchase.currency) }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div class="prf-body">
                <div class="prf-receipt">
                    <!-- ── Meta strip ────────────────────────────────────── -->
                    <div class="prf-meta">
                        <div class="prf-meta__item">
                            <span class="prf-meta__label"><el-icon :size="13"><Calendar /></el-icon> Placed</span>
                            <span class="prf-meta__value">{{ formatDate(purchase.created_at) }}</span>
                        </div>
                        <div class="prf-meta__item">
                            <span class="prf-meta__label"><el-icon :size="13"><component :is="paymentIcon" /></el-icon> Payment</span>
                            <span class="prf-meta__value">{{ paymentLabel(purchase.payment_method) }}</span>
                        </div>
                        <div class="prf-meta__item">
                            <span class="prf-meta__label"><el-icon :size="13"><Box /></el-icon> Total Quantity</span>
                            <span class="prf-meta__value">{{ totalItems.toLocaleString() }} kg</span>
                        </div>
                        <div v-if="!isCompleted" class="prf-meta__item">
                            <span class="prf-meta__label"><el-icon :size="13"><Close /></el-icon> Cancelled</span>
                            <span class="prf-meta__value">{{ formatDate(purchase.cancelled_at) }}</span>
                        </div>
                    </div>

                    <!-- ── Items ─────────────────────────────────────────── -->
                    <div class="prf-section">
                        <h2 class="prf-section__title"><el-icon :size="16"><Tickets /></el-icon> Items</h2>

                        <div class="prf-items mt-3">
                            <div v-for="(item, idx) in purchase.items" :key="idx" class="prf-item">
                                <span class="prf-item__icon"><el-icon :size="14"><Box /></el-icon></span>
                                <div class="prf-item__body">
                                    <span class="prf-item__name">{{ item.name }}</span>
                                    <span v-if="item.lot_code" class="prf-item__lot">{{ item.lot_code }}</span>
                                </div>
                                <span class="prf-item__meta">{{ item.quantity.toLocaleString() }} kg × {{ formatMoney(item.unit_price, purchase.currency) }}</span>
                                <span class="prf-item__total">{{ formatMoney(item.line_total, purchase.currency) }}</span>
                            </div>
                        </div>

                        <div class="prf-summary">
                            <div class="prf-summary__row">
                                <span>Subtotal</span>
                                <span>{{ formatMoney(purchase.total_amount, purchase.currency) }}</span>
                            </div>
                            <div class="prf-summary__row prf-summary__row--total">
                                <span>Total</span>
                                <span>{{ formatMoney(purchase.total_amount, purchase.currency) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ── Actions ───────────────────────────────────────── -->
                    <div class="prf-actions">
                        <p class="prf-trust"><el-icon :size="13"><Lock /></el-icon> This is a read-only record of your checkout — it does not affect your live order fulfillment.</p>
                        <button
                            v-if="isCompleted"
                            type="button"
                            class="prf-cancel-btn"
                            @click="cancelOpen = true"
                        >
                            <el-icon :size="14"><Close /></el-icon> Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model="cancelOpen"
            title="Cancel Order"
            :message="`${purchase.order_number} will be marked as cancelled. This can't be undone.`"
            confirm-text="Cancel Order"
            @confirm="confirmCancel"
        />
    </AppLayout>
</template>

<style scoped>
.prf-page {
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
.prf-header {
    background: #fff;
    border-bottom: 1px solid var(--border);
}

.prf-header__inner {
    padding: 1.5rem 1.5rem 1.75rem;
}

.prf-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--on-surface-var);
    text-decoration: none;
    margin-bottom: 20px;
    transition: color 0.15s ease;
}

.prf-back:hover { color: var(--green); }

.prf-header__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.prf-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 8px;
}

.prf-title {
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 1.625rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    line-height: 1.2;
    margin: 0;
}

.prf-header__right {
    display: flex;
    align-items: center;
    gap: 14px;
}

.prf-header__total {
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--on-surface);
}

.prf-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.prf-status.is-completed { background: #dcfce7; color: #166534; }
.prf-status.is-cancelled { background: #fee2e2; color: #991b1b; }

/* ── Body ────────────────────────────────────────────────────────────── */
.prf-body {
    max-width: 720px;
    margin: 0 auto;
    padding: 1.75rem 1.5rem 3rem;
}

.prf-receipt {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

/* ── Meta strip ──────────────────────────────────────────────────────── */
.prf-meta {
    display: flex;
    flex-wrap: wrap;
    border-bottom: 1px solid var(--border);
}

.prf-meta__item {
    flex: 1 1 160px;
    display: flex;
    flex-direction: column;
    gap: 7px;
    padding: 20px 22px;
    border-right: 1px solid var(--border);
}

.prf-meta__item:last-child { border-right: none; }

.prf-meta__label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--on-surface-var);
}

.prf-meta__label :deep(.el-icon) { color: var(--green); }

.prf-meta__value {
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--on-surface);
    letter-spacing: -0.01em;
}

/* ── Sections ────────────────────────────────────────────────────────── */
.prf-section { padding: 24px; }

.prf-section__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    margin: 0 0 18px;
}

.prf-section__title :deep(.el-icon) { color: var(--green); }

.prf-items {
    display: flex;
    flex-direction: column;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
}

.prf-item {
    display: grid;
    grid-template-columns: 36px minmax(0, 1fr) auto auto;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    background: #fff;
    transition: background 0.12s ease;
}

.prf-item:hover { background: var(--surface-low); }

.prf-item + .prf-item { border-top: 1px solid var(--surface-low); }

.prf-item__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    flex-shrink: 0;
}

.prf-item__body { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.prf-item__name { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); letter-spacing: -0.005em; }
.prf-item__lot { font-family: 'IBM Plex Mono', ui-monospace, monospace; font-size: 0.6875rem; color: var(--on-surface-var); }
.prf-item__meta { font-size: 0.75rem; color: var(--on-surface-var); white-space: nowrap; }
.prf-item__total { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); font-variant-numeric: tabular-nums; white-space: nowrap; }

.prf-summary {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px dashed var(--border);
}

.prf-summary__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    padding: 4px 0;
}

.prf-summary__row--total {
    margin-top: 4px;
    padding-top: 10px;
    border-top: 1px solid var(--surface-low);
    font-size: 1.0625rem;
    font-weight: 800;
    color: var(--on-surface);
}

/* ── Actions ─────────────────────────────────────────────────────────── */
.prf-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 18px 24px;
    background: var(--surface-low);
    border-top: 1px solid var(--border);
}

.prf-trust {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: var(--on-surface-var);
    margin: 0;
}

.prf-trust :deep(.el-icon) { color: var(--green); flex-shrink: 0; }

.prf-cancel-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #fff;
    color: var(--red);
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s ease, border-color 0.12s ease;
}

.prf-cancel-btn:hover { background: #fef2f2; border-color: #fecaca; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .prf-header__inner { padding: 1.25rem; }
    .prf-body { padding: 1.25rem; }
    .prf-item { grid-template-columns: 24px minmax(0, 1fr); grid-template-rows: auto auto; }
    .prf-item__meta, .prf-item__total { grid-column: 2; justify-self: start; }
}
</style>
