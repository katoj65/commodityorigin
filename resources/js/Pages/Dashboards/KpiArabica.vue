<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Box, CoffeeCup, Goods, Location, PriceTag } from '@element-plus/icons-vue';

const props = defineProps({
    listings: { type: Array, default: () => [] },
    referencePrice: { type: Object, default: null },
    stats: { type: Object, default: () => ({}) },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');
</script>

<template>
    <DesignPreviewLayout title="Arabica (KC)">
        <Head title="Arabica (KC)" />

        <div class="kpi-page">
            <header class="kpi-hero">
                <div class="kpi-hero__text">
                    <h1 class="kpi-hero__title"><el-icon><CoffeeCup /></el-icon> Arabica (KC)</h1>
                    <p class="kpi-hero__subtitle">Every live Arabica listing on the exchange right now, and the reference price behind the headline number.</p>
                </div>
            </header>

            <div class="kpi-stats">
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Live Listings</span>
                    <strong class="kpi-stat__value">{{ stats.count ?? 0 }}</strong>
                </div>
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Average Price</span>
                    <strong class="kpi-stat__value">{{ fmtMoney(stats.average_price_per_kg) }}<small>/kg</small></strong>
                </div>
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Total Volume</span>
                    <strong class="kpi-stat__value">{{ fmtNum(stats.total_quantity_kg) }}<small>kg</small></strong>
                </div>
                <div class="kpi-stat" v-if="referencePrice">
                    <span class="kpi-stat__label">{{ referencePrice.item }} Index</span>
                    <strong class="kpi-stat__value">
                        {{ fmtMoney(referencePrice.current_price) }}
                        <small :class="referencePrice.percentage_fluctuation >= 0 ? 'kpi-up' : 'kpi-down'">
                            {{ referencePrice.percentage_fluctuation >= 0 ? '+' : '' }}{{ referencePrice.percentage_fluctuation }}%
                        </small>
                    </strong>
                </div>
            </div>

            <section class="kpi-section">
                <div class="kpi-section__head">
                    <h2 class="kpi-section__title"><el-icon><Goods /></el-icon> Live Arabica Listings</h2>
                    <span class="kpi-section__count">{{ listings.length }}</span>
                </div>

                <div v-if="listings.length" class="kpi-table-wrap">
                    <table class="kpi-table">
                        <thead>
                            <tr>
                                <th><span class="kpi-th"><el-icon><Goods /></el-icon> Listing</span></th>
                                <th><span class="kpi-th"><el-icon><Location /></el-icon> Origin</span></th>
                                <th><span class="kpi-th"><el-icon><PriceTag /></el-icon> Price</span></th>
                                <th><span class="kpi-th"><el-icon><Box /></el-icon> Quantity</span></th>
                                <th>Demand</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="listing in listings" :key="listing.id">
                                <td><Link :href="route('market.show', listing.id)" class="kpi-table-link">{{ listing.name || listing.lot_code || `Listing #${listing.id}` }}</Link></td>
                                <td>{{ listing.origin || '—' }}</td>
                                <td class="kpi-mono">{{ listing.currency }} {{ fmtMoney(listing.price_per_kg) }}</td>
                                <td class="kpi-mono">{{ fmtNum(listing.quantity) }} {{ listing.unit }}</td>
                                <td><span class="kpi-badge" v-if="listing.demand">{{ listing.demand }}</span><span v-else>—</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="kpi-empty">No live Arabica listings right now.</p>
            </section>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.kpi-page {
    --green: #000000;
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --on-surface: #121516;
    --on-surface-var: #4B5457;
    --surface-low: #F5F6F7;
    font-family: 'Inter', system-ui, sans-serif;
    color: var(--on-surface);
    min-height: 100%;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.kpi-hero__title { display: flex; align-items: center; gap: 8px; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.015em; color: var(--on-surface); margin: 0; }
.kpi-hero__subtitle { font-size: 13.5px; line-height: 1.5; color: var(--on-surface-var); margin: 6px 0 0; max-width: 640px; }

.kpi-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.kpi-stat { display: flex; flex-direction: column; gap: 4px; background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 14px 16px; }
.kpi-stat__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); }
.kpi-stat__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); font-variant-numeric: tabular-nums; }
.kpi-stat__value small { font-size: .6875rem; font-weight: 600; color: var(--on-surface-var); margin-left: 4px; }
.kpi-up { color: #16A34A !important; }
.kpi-down { color: #DC2626 !important; }

.kpi-section { background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 18px; }
.kpi-section__head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.kpi-section__title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin: 0; }
.kpi-section__count { font-size: 11px; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); padding: 1px 8px; border-radius: 999px; }

.kpi-table-wrap { overflow-x: auto; }
.kpi-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.kpi-table th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--card-border); white-space: nowrap; }
.kpi-th { display: inline-flex; align-items: center; gap: 5px; }
.kpi-th .el-icon { font-size: 13px; }
.kpi-table td { padding: 11px 12px; border-bottom: 1px solid var(--card-border); color: var(--on-surface); }
.kpi-table tr:last-child td { border-bottom: none; }
.kpi-mono { font-variant-numeric: tabular-nums; }
.kpi-table-link { color: var(--on-surface); font-weight: 600; text-decoration: none; }
.kpi-table-link:hover { color: var(--green); text-decoration: underline; }
.kpi-badge { display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); background: var(--surface-low); padding: 2px 8px; border-radius: 999px; }
.kpi-empty { font-size: 13px; color: var(--on-surface-var); margin: 0; padding: 8px 0; }

@media (max-width: 900px) {
    .kpi-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
</style>
