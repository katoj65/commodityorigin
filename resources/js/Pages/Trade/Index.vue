<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Box, CircleCheck, Coin, Goods, Location, PriceTag, Sell, ShoppingCart, Tickets, Trophy } from '@element-plus/icons-vue';

const props = defineProps({
    markets: { type: Array, default: () => [] },
    auctionCount: { type: Number, default: 0 },
    requestCount: { type: Number, default: 0 },
});

const fmt = (value, digits = 2) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
};

const actions = [
    {
        key: 'buy-now',
        label: 'Buy Now',
        description: 'Purchase available lots immediately and check out.',
        icon: ShoppingCart,
        href: route('checkout.index'),
    },
    {
        key: 'auction',
        label: 'Auction',
        description: 'Bid on coffee lots in live auctions.',
        icon: Trophy,
        href: route('auction.index'),
        count: props.auctionCount,
    },
    {
        key: 'offer',
        label: 'Make an Offer',
        description: 'Submit an offer on a listing and negotiate.',
        icon: Sell,
        href: route('trade.offer'),
    },
    {
        key: 'rfq',
        label: 'Request for Quote',
        description: 'Request a quote from sellers for your needs.',
        icon: Tickets,
        href: route('rfq.index'),
        count: props.requestCount,
    },
];
</script>

<template>
    <DesignPreviewLayout title="Trade">
        <Head title="Trade" />

        <div class="trade-page">
            <div class="trade-page__header">
                <h1 class="trade-page__title">Trade</h1>
                <p class="trade-page__subtitle">Buy, bid, offer, and request quotes across the exchange.</p>
            </div>

            <div class="trade-actions">
                <Link v-for="action in actions" :key="action.key" :href="action.href" class="trade-action">
                    <span class="trade-action__icon"><el-icon :size="18"><component :is="action.icon" /></el-icon></span>
                    <span class="trade-action__body">
                        <span class="trade-action__label">
                            {{ action.label }}
                            <span v-if="action.count !== undefined" class="trade-action__count">{{ action.count }}</span>
                        </span>
                        <span class="trade-action__hint">{{ action.description }}</span>
                    </span>
                </Link>
            </div>

            <section class="trade-products">
                <div class="trade-products__head">
                    <h2 class="trade-products__title"><el-icon><Goods /></el-icon> Products</h2>
                    <span class="trade-products__count">{{ markets.length }}</span>
                </div>

                <div v-if="markets.length" class="trade-table-wrap">
                    <table class="trade-table">
                        <thead>
                            <tr>
                                <th><span class="trade-table__th"><el-icon><Goods /></el-icon> Product</span></th>
                                <th><span class="trade-table__th"><el-icon><Location /></el-icon> Origin</span></th>
                                <th><span class="trade-table__th"><el-icon><Coin /></el-icon> Price</span></th>
                                <th><span class="trade-table__th"><el-icon><Box /></el-icon> Quantity</span></th>
                                <th><span class="trade-table__th"><el-icon><PriceTag /></el-icon> Pricing</span></th>
                                <th><span class="trade-table__th"><el-icon><CircleCheck /></el-icon> Status</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="market in markets" :key="market.id">
                                <td>
                                    <Link :href="route('market.show', market.id)" class="trade-table__name">{{ market.name || market.lot_code || `Listing #${market.id}` }}</Link>
                                    <span v-if="market.type" class="trade-table__sub">{{ market.type }}</span>
                                </td>
                                <td>{{ market.origin || '—' }}</td>
                                <td>{{ market.currency }} {{ fmt(market.price_per_kg) }}</td>
                                <td>{{ fmt(market.quantity) }} {{ market.unit }}</td>
                                <td>{{ market.pricing_type || '—' }}</td>
                                <td><span class="trade-status">{{ market.status }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="trade-empty">No products listed on the market yet.</p>
            </section>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.trade-page { display: flex; flex-direction: column; gap: 20px; }

.trade-page__title { font-size: 22px; font-weight: 700; color: var(--dp-on-surface); margin: 0; }
.trade-page__subtitle { font-size: 13.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; }

.trade-actions { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.trade-action {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: var(--dp-surface);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 8px;
    text-decoration: none;
    transition: border-color 0.15s ease, transform 0.15s ease;
}
.trade-action:hover { border-color: var(--dp-outline); transform: translateY(-1px); }
.trade-action__icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    flex-shrink: 0;
    background: var(--dp-surface-container-high);
    color: var(--dp-on-surface-variant);
    display: flex;
    align-items: center;
    justify-content: center;
}
.trade-action__body { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.trade-action__label { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dp-on-surface); }
.trade-action__count {
    font-size: 10px;
    font-weight: 700;
    color: var(--dp-on-surface-variant);
    background: var(--dp-surface-container-high);
    padding: 1px 7px;
    border-radius: 999px;
}
.trade-action__hint { font-size: 12px; color: var(--dp-on-surface-variant); line-height: 1.5; }

.trade-products {
    background: var(--dp-surface);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 8px;
    padding: 16px;
}
.trade-products__head { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.trade-products__title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-outline); margin: 0; }
.trade-products__title .el-icon { font-size: 15px; color: var(--dp-outline); }
.trade-products__count {
    font-size: 11px;
    font-weight: 700;
    color: var(--dp-on-surface-variant);
    background: var(--dp-surface-container-high);
    padding: 1px 8px;
    border-radius: 999px;
}

.trade-table-wrap { overflow-x: auto; }
.trade-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.trade-table th {
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--dp-on-surface-variant);
    padding: 8px 12px;
    border-bottom: 1px solid var(--dp-outline-variant);
    white-space: nowrap;
}
.trade-table__th {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.trade-table__th .el-icon { font-size: 13px; color: var(--dp-on-surface-variant); }
.trade-table td { padding: 11px 12px; border-bottom: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface); }
.trade-table tr:last-child td { border-bottom: none; }
.trade-table__name { display: block; font-weight: 600; color: var(--dp-on-surface); text-decoration: none; }
.trade-table__name:hover { color: var(--dp-primary); text-decoration: underline; }
.trade-table__sub { display: block; font-size: 11.5px; color: var(--dp-on-surface-variant); margin-top: 2px; }
.trade-status {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--dp-on-surface-variant);
    background: var(--dp-surface-container-high);
    padding: 2px 8px;
    border-radius: 999px;
}
.trade-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; }

@media (max-width: 900px) {
    .trade-actions { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 560px) {
    .trade-actions { grid-template-columns: 1fr; }
}
</style>

