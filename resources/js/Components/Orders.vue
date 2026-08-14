<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Tickets, FullScreen, ShoppingCart } from '@element-plus/icons-vue';

const props = defineProps({
    orders: { type: Array, default: () => [] },
    title: { type: String, default: 'Orders' },
    showHeader: { type: Boolean, default: true },
});

const page = usePage();
const authUserId = computed(() => page.props.auth?.user?.id ?? null);

function isBuyer(order) {
    return order.buyer_id === authUserId.value;
}

function isSeller(order) {
    return order.seller_id === authUserId.value;
}

function isCreator(order) {
    return order.type === 'offer' ? isSeller(order) : isBuyer(order);
}

function partyLabel(order) {
    if (order.status === 'open') {
        if (isCreator(order)) return order.type === 'offer' ? 'Awaiting a buyer' : 'Awaiting a seller';
        return order.type === 'offer' ? `From ${order.seller_name}` : `From ${order.buyer_name}`;
    }
    return isBuyer(order) ? `To ${order.seller_name}` : `From ${order.buyer_name}`;
}

const sortedOrders = computed(() => [...props.orders].sort((a, b) => b.created_at.localeCompare(a.created_at)));

const activeCount = computed(() => props.orders.filter((o) => !['delivered', 'cancelled'].includes(o.status)).length);

function statusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function toneFor(status) {
    return {
        open: 'orders-badge--muted',
        pending: 'orders-badge--amber',
        confirmed: 'orders-badge--blue',
        processing: 'orders-badge--blue',
        shipped: 'orders-badge--blue',
        delivered: 'orders-badge--green',
        cancelled: 'orders-badge--red',
    }[status] ?? 'orders-badge--muted';
}

function formatAmount(order) {
    return `${order.currency} ${Number(order.total_amount).toLocaleString('en-US', { maximumFractionDigits: 0 })}`;
}
</script>

<template>
    <div class="orders-widget">
        <div v-if="showHeader" class="orders-widget__head">
            <Link :href="route('market.request')" class="orders-widget__title-group">
                <span class="orders-widget__icon"><el-icon :size="14"><Tickets /></el-icon></span>
                <div class="orders-widget__title">{{ title }}</div>
                <span v-if="activeCount" class="orders-widget__count">{{ activeCount }}</span>
            </Link>
            <Link :href="route('market.request')" class="orders-widget__link" aria-label="Open all orders" title="Open all orders">
                <el-icon :size="13"><FullScreen /></el-icon>
            </Link>
        </div>

        <div v-if="!sortedOrders.length" class="orders-widget__empty">
            <span class="orders-widget__empty-icon"><el-icon :size="18"><ShoppingCart /></el-icon></span>
            <p>No orders yet — place one to start trading.</p>
        </div>

        <div v-else class="orders-widget__list">
            <Link v-for="order in sortedOrders" :key="order.id" :href="route('orders.show', order.id)" class="orders-row">
                <div class="orders-row__body">
                    <div class="orders-row__title">{{ order.order_number }}</div>
                    <div class="orders-row__meta">{{ partyLabel(order) }}</div>
                </div>
                <div class="orders-row__amount">{{ formatAmount(order) }}</div>
                <span class="orders-badge" :class="toneFor(order.status)">{{ statusLabel(order.status) }}</span>
            </Link>
        </div>
    </div>
</template>

<style scoped>
.orders-widget {
    font-family: 'Manrope', system-ui, sans-serif;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.orders-widget__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.orders-widget__title-group {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
    color: inherit;
    border-radius: 6px;
    transition: opacity 0.12s;
}

.orders-widget__title-group:hover {
    opacity: 0.75;
}

.orders-widget__icon {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.orders-widget__title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #111827;
}

.orders-widget__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #fef3c7;
    color: #92400e;
    font-size: 0.625rem;
    font-weight: 800;
}

.orders-widget__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    color: #6b7280;
    text-decoration: none;
    transition: background 0.12s, color 0.12s;
}

.orders-widget__link:hover {
    background: #f8fafc;
    color: #004532;
}

.orders-widget__empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-align: center;
    padding: 1.5rem 0.5rem;
}

.orders-widget__empty-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #f8fafc;
    color: #9ca3af;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.orders-widget__empty p {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

.orders-widget__list {
    display: flex;
    flex-direction: column;
}

.orders-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 6px;
    margin: 0 -6px;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
    transition: background 0.12s;
}

.orders-row:hover {
    background: #f8fafc;
}

.orders-row__body {
    flex: 1;
    min-width: 0;
}

.orders-row__title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: #111827;
    font-family: 'IBM Plex Mono', monospace;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.orders-row__meta {
    font-size: 0.6875rem;
    color: #6b7280;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.orders-row__amount {
    font-size: 0.75rem;
    font-weight: 700;
    color: #111827;
    white-space: nowrap;
    flex-shrink: 0;
}

.orders-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.625rem;
    font-weight: 700;
    padding: 3px 9px;
    flex-shrink: 0;
    white-space: nowrap;
}

.orders-badge--green { background: #dcfce7; color: #166534; }
.orders-badge--amber { background: #fef3c7; color: #92400e; }
.orders-badge--red { background: #fee2e2; color: #991b1b; }
.orders-badge--blue { background: #dbeafe; color: #1e40af; }
.orders-badge--muted { background: #f3f4f6; color: #6b7280; }
</style>
