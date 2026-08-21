<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    SuccessFilled, CircleCheck, Van, Wallet as WalletIcon, CreditCard, Calendar, ArrowRight, ShoppingBag,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

const props = defineProps({
    orders: { type: Array, default: () => [] },
});

function formatMoney(amount, currency = 'USD') {
    return `${currency} ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(dateTime) {
    if (!dateTime) return '—';
    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
    });
}

const orderCount = computed(() => props.orders.length);
const currency = computed(() => props.orders[0]?.currency ?? 'USD');
const grandTotal = computed(() => props.orders.reduce((sum, order) => sum + Number(order.total_amount), 0));

const paymentLabels = { wallet: 'Wallet Balance', card: 'Credit / Debit Card' };
const paymentIcons = { wallet: WalletIcon, card: CreditCard };

function paymentLabel(method) {
    return paymentLabels[method] ?? method;
}

const statusTone = {
    confirmed: 'ordc-badge--green',
    pending: 'ordc-badge--amber',
    cancelled: 'ordc-badge--red',
};
</script>

<template>
    <DesignPreviewLayout title="Order Confirmed">
        <div class="ordc-page">
            <div class="ordc-body">
                <!-- ── Success hero ─────────────────────────────────────── -->
                <div class="ordc-hero">
                    <div class="ordc-hero__icon"><el-icon :size="30"><SuccessFilled /></el-icon></div>
                    <div class="ordc-kicker">Checkout · Bean Origin</div>
                    <h1 class="ordc-title">Order Confirmed</h1>
                    <p class="ordc-subtitle">
                        <template v-if="orderCount > 1">
                            {{ orderCount }} orders totaling <strong>{{ formatMoney(grandTotal, currency) }}</strong> have been placed and confirmed.
                        </template>
                        <template v-else>
                            Your order totaling <strong>{{ formatMoney(grandTotal, currency) }}</strong> has been placed and confirmed.
                        </template>
                    </p>
                </div>

                <!-- ── Orders ────────────────────────────────────────────── -->
                <div class="ordc-list">
                    <article v-for="order in orders" :key="order.id" class="ordc-card">
                        <div class="ordc-card__head">
                            <div>
                                <div class="ordc-card__number">{{ order.order_number }}</div>
                                <div class="ordc-card__meta"><el-icon :size="11"><Calendar /></el-icon> {{ formatDate(order.created_at) }}</div>
                            </div>
                            <span class="ordc-badge" :class="statusTone[order.status] ?? 'ordc-badge--muted'">
                                <el-icon :size="11"><CircleCheck /></el-icon> {{ order.status }}
                            </span>
                        </div>

                        <div class="ordc-card__body">
                            <div class="ordc-card__product">
                                <span class="ordc-card__product-name">{{ order.variety || order.crop_type }}</span>
                                <span class="ordc-card__product-meta">
                                    {{ order.crop_type }}<template v-if="order.grade"> · {{ order.grade }}</template>
                                    <template v-if="order.seller_name"> · Sold by {{ order.seller_name }}</template>
                                </span>
                            </div>
                            <div class="ordc-card__qty">
                                <span>{{ order.quantity }}kg × {{ formatMoney(order.unit_price, order.currency) }}</span>
                                <strong>{{ formatMoney(order.total_amount, order.currency) }}</strong>
                            </div>
                        </div>

                        <div class="ordc-card__foot">
                            <span class="ordc-card__pay">
                                <el-icon :size="12"><component :is="paymentIcons[order.payment_method] ?? WalletIcon" /></el-icon>
                                Paid via {{ paymentLabel(order.payment_method) }}
                            </span>
                            <Link :href="route('orders.show', order.id)" class="ordc-card__track">
                                Track order <el-icon :size="11"><ArrowRight /></el-icon>
                            </Link>
                        </div>
                    </article>
                </div>

                <!-- ── Actions ───────────────────────────────────────────── -->
                <div class="ordc-actions">
                    <Link :href="route('orders.index')" class="ordc-btn ordc-btn--primary">
                        <el-icon :size="14"><CircleCheck /></el-icon> View My Orders
                    </Link>
                    <Link :href="route('market.index')" class="ordc-btn">
                        <el-icon :size="14"><ShoppingBag /></el-icon> Continue Shopping
                    </Link>
                </div>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.ordc-page {
    --green: #004532;
    --green-dark: #002e20;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

.ordc-body { max-width: 640px; margin: 0 auto; padding: 3rem 1.5rem 4rem; }

/* ── Hero ────────────────────────────────────────────────────────────── */
.ordc-hero { display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 2.5rem; }

.ordc-hero__icon {
    display: flex; align-items: center; justify-content: center;
    width: 64px; height: 64px; border-radius: 999px;
    background: rgba(0, 69, 50, 0.08); color: var(--green);
    margin-bottom: 1.25rem;
}

.ordc-kicker { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 6px; }
.ordc-title { font-size: 1.75rem; font-weight: 800; letter-spacing: -.025em; margin: 0 0 .625rem; }
.ordc-subtitle { font-size: .9375rem; color: var(--on-surface-var); line-height: 1.6; max-width: 440px; margin: 0; }
.ordc-subtitle strong { color: var(--on-surface); font-variant-numeric: tabular-nums; }

/* ── Order cards ─────────────────────────────────────────────────────── */
.ordc-list { display: flex; flex-direction: column; gap: 1rem; }

.ordc-card { border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }

.ordc-card__head {
    display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem;
    padding: 1rem 1.25rem; background: var(--surface-low); border-bottom: 1px solid var(--border);
}

.ordc-card__number { font-size: .875rem; font-weight: 800; font-family: 'IBM Plex Mono', monospace; letter-spacing: -.01em; }
.ordc-card__meta { display: flex; align-items: center; gap: 4px; font-size: .6875rem; color: var(--on-surface-var); margin-top: 2px; }

.ordc-badge {
    display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;
    border-radius: 999px; font-size: .6875rem; font-weight: 600; letter-spacing: .01em;
    padding: 4px 10px; text-transform: capitalize; white-space: nowrap;
}
.ordc-badge--green { background: #dcfce7; color: #166534; }
.ordc-badge--amber { background: #fef3c7; color: #92400e; }
.ordc-badge--red { background: #fee2e2; color: #991b1b; }
.ordc-badge--muted { background: #f3f4f6; color: #6b7280; }

.ordc-card__body { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; }

.ordc-card__product { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.ordc-card__product-name { font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.ordc-card__product-meta { font-size: .75rem; color: var(--on-surface-var); }

.ordc-card__qty { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; flex-shrink: 0; text-align: right; }
.ordc-card__qty span { font-size: .75rem; color: var(--on-surface-var); font-variant-numeric: tabular-nums; white-space: nowrap; }
.ordc-card__qty strong { font-size: .9375rem; font-weight: 800; color: var(--green); font-family: 'IBM Plex Mono', monospace; }

.ordc-card__foot {
    display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    padding: .75rem 1.25rem; border-top: 1px dashed var(--border);
}

.ordc-card__pay { display: flex; align-items: center; gap: 5px; font-size: .75rem; color: var(--on-surface-var); }
.ordc-card__pay .el-icon { color: var(--green); }

.ordc-card__track {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .75rem; font-weight: 700; color: var(--green); text-decoration: none;
}
.ordc-card__track:hover { text-decoration: underline; }

/* ── Actions ─────────────────────────────────────────────────────────── */
.ordc-actions { display: flex; align-items: center; justify-content: center; gap: .75rem; margin-top: 2.5rem; flex-wrap: wrap; }

.ordc-btn {
    display: inline-flex; align-items: center; gap: 7px;
    border-radius: 9px; font-size: .8125rem; font-weight: 600; letter-spacing: .01em;
    padding: 10px 20px; cursor: pointer; border: 1px solid var(--border);
    background: #fff; color: var(--on-surface); text-decoration: none;
    transition: opacity .15s ease, background .15s ease;
}
.ordc-btn:hover { background: var(--surface-low); }

.ordc-btn--primary { background: linear-gradient(135deg, #004532, #065f46); border-color: transparent; color: #fff; }
.ordc-btn--primary:hover { opacity: .9; background: linear-gradient(135deg, #004532, #065f46); }

@media (max-width: 640px) {
    .ordc-body { padding: 2rem 1.25rem 3rem; }
    .ordc-card__body { flex-direction: column; }
    .ordc-card__qty { align-items: flex-start; text-align: left; }
}
</style>
