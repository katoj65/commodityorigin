<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    Box, Compass, DataAnalysis, Goods, InfoFilled, Location,
    PriceTag, TrendCharts, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    lots: { type: Array, default: () => [] },
    analysis: { type: Object, default: () => ({}) },
    demand: { type: Object, default: () => ({}) },
    opportunities: { type: Array, default: () => [] },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');

const sentimentClass = computed(() => {
    const score = props.demand.sentiment_score;
    if (score == null) return '';
    if (score >= 70) return 'lm-up';
    if (score >= 40) return 'lm-mid';
    return 'lm-down';
});

const tierLabels = { high: 'High Demand', medium: 'Medium Demand', low: 'Low Demand' };
</script>

<template>
    <DesignPreviewLayout title="Live Market">
        <Head title="Live Market" />

        <div class="lm-page">
            <header class="lm-hero">
                <div class="lm-hero__text">
                    <h1 class="lm-hero__title"><el-icon><TrendCharts /></el-icon> Live Market</h1>
                    <p class="lm-hero__subtitle">A real-time pulse across every live listing on the exchange — demand, pricing, and where supply is falling short.</p>
                </div>
                <div class="lm-hero__actions">
                    <Link :href="route('market.active')" class="lm-hero__btn lm-hero__btn--outline">
                        <el-icon><DataAnalysis /></el-icon> Manage Active Listings
                    </Link>
                    <Link :href="route('market.index')" class="lm-hero__btn lm-hero__btn--primary">
                        <el-icon><Compass /></el-icon> Browse Market
                    </Link>
                </div>
            </header>

            <div class="lm-stats">
                <div class="lm-stat">
                    <span class="lm-stat__label">Live Listings</span>
                    <strong class="lm-stat__value">{{ fmtNum(analysis.total_listings) }}</strong>
                </div>
                <div class="lm-stat">
                    <span class="lm-stat__label">Total Volume</span>
                    <strong class="lm-stat__value">{{ fmtNum(analysis.total_volume_kg) }}<small>kg</small></strong>
                </div>
                <div class="lm-stat">
                    <span class="lm-stat__label">Average Price</span>
                    <strong class="lm-stat__value">{{ fmtMoney(analysis.average_price_per_kg) }}<small>/kg</small></strong>
                </div>
                <div class="lm-stat">
                    <span class="lm-stat__label">Market Sentiment</span>
                    <strong class="lm-stat__value" :class="sentimentClass">
                        {{ demand.sentiment_score ?? '—' }}<small v-if="demand.sentiment_score != null">/100</small>
                    </strong>
                </div>
            </div>

            <div class="lm-grid">
                <section class="lm-card">
                    <div class="lm-card__head">
                        <h2 class="lm-card__title"><el-icon><InfoFilled /></el-icon> Market Insights</h2>
                    </div>
                    <ul v-if="analysis.insights?.length" class="lm-insights">
                        <li v-for="(insight, i) in analysis.insights" :key="i">{{ insight }}</li>
                    </ul>
                    <p v-else class="lm-empty">No insights yet — they'll appear once listings go live.</p>
                </section>

                <section class="lm-card">
                    <div class="lm-card__head">
                        <h2 class="lm-card__title"><el-icon><DataAnalysis /></el-icon> Demand Breakdown</h2>
                    </div>
                    <div v-if="demand.tiers?.length" class="lm-tiers">
                        <div v-for="tier in demand.tiers" :key="tier.tier" class="lm-tier">
                            <div class="lm-tier__head">
                                <span class="lm-tier__label">{{ tierLabels[tier.tier] || tier.tier }}</span>
                                <span class="lm-tier__count">{{ tier.count }} · {{ tier.percentage }}%</span>
                            </div>
                            <div class="lm-tier__bar">
                                <div class="lm-tier__fill" :class="`lm-tier__fill--${tier.tier}`" :style="{ width: tier.percentage + '%' }" />
                            </div>
                        </div>
                    </div>
                    <p v-else class="lm-empty">No demand data yet.</p>
                </section>
            </div>

            <section class="lm-card">
                <div class="lm-card__head">
                    <h2 class="lm-card__title"><el-icon><WarningFilled /></el-icon> Market Opportunities</h2>
                    <span class="lm-card__hint">Coffee types where buyer demand is outpacing available supply.</span>
                </div>
                <div v-if="opportunities.length" class="lm-table-wrap">
                    <table class="lm-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Listings</th>
                                <th>Share of Market</th>
                                <th>High Demand Share</th>
                                <th>Gap Score</th>
                                <th>Average Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in opportunities" :key="row.type">
                                <td class="lm-table-strong">{{ row.type }}</td>
                                <td>{{ row.listings }}</td>
                                <td>{{ row.share }}%</td>
                                <td>{{ row.high_demand_share }}%</td>
                                <td><span class="lm-badge lm-badge--gap">+{{ row.gap_score }}</span></td>
                                <td class="lm-mono">{{ fmtMoney(row.average_price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="lm-empty">No supply gaps right now — demand and listings are roughly in balance.</p>
            </section>

            <section class="lm-card">
                <div class="lm-card__head">
                    <h2 class="lm-card__title"><el-icon><Goods /></el-icon> Live Listings</h2>
                    <span class="lm-card__count">{{ lots.length }}</span>
                </div>
                <div v-if="lots.length" class="lm-table-wrap">
                    <table class="lm-table">
                        <thead>
                            <tr>
                                <th><span class="lm-th"><el-icon><Goods /></el-icon> Listing</span></th>
                                <th><span class="lm-th"><el-icon><Location /></el-icon> Origin</span></th>
                                <th>Type</th>
                                <th><span class="lm-th"><el-icon><PriceTag /></el-icon> Price</span></th>
                                <th><span class="lm-th"><el-icon><Box /></el-icon> Quantity</span></th>
                                <th>Demand</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lot in lots" :key="lot.id">
                                <td><Link :href="route('market.show', lot.id)" class="lm-table-link">{{ lot.name || lot.lot_code || `Listing #${lot.id}` }}</Link></td>
                                <td>{{ lot.origin || '—' }}</td>
                                <td class="lm-capitalize">{{ lot.type || '—' }}</td>
                                <td class="lm-mono">{{ fmtMoney(lot.price_per_kg) }}</td>
                                <td class="lm-mono">{{ fmtNum(lot.quantity) }} kg</td>
                                <td><span v-if="lot.demand" class="lm-badge">{{ lot.demand }}</span><span v-else>—</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="lm-empty">No live listings right now.</p>
            </section>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.lm-page {
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

.lm-hero { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.lm-hero__text { display: flex; flex-direction: column; gap: 6px; max-width: 640px; }
.lm-hero__title { display: flex; align-items: center; gap: 8px; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.015em; color: var(--on-surface); margin: 0; }
.lm-hero__subtitle { font-size: 13.5px; line-height: 1.5; color: var(--on-surface-var); margin: 0; max-width: 640px; }
.lm-hero__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

.lm-hero__btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    border: 1px solid transparent;
    border-radius: var(--card-radius);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    padding: 0 16px;
    white-space: nowrap;
    transition: background .15s ease, transform .15s ease, box-shadow .15s ease;
}
.lm-hero__btn--primary { background: #000000; color: #fff; }
.lm-hero__btn--primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); }
.lm-hero__btn--outline { background: #fff; border-color: var(--card-border); color: var(--on-surface); }
.lm-hero__btn--outline:hover { background: var(--surface-low); }

.lm-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.lm-stat { display: flex; flex-direction: column; gap: 4px; background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 14px 16px; }
.lm-stat__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); }
.lm-stat__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); font-variant-numeric: tabular-nums; }
.lm-stat__value small { font-size: .6875rem; font-weight: 600; color: var(--on-surface-var); margin-left: 4px; }
.lm-up  { color: #16A34A !important; }
.lm-mid { color: #D29922 !important; }
.lm-down { color: #DC2626 !important; }

.lm-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }

.lm-card { background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 18px; }
.lm-card__head { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.lm-card__title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin: 0; }
.lm-card__hint { font-size: 12px; color: var(--on-surface-var); }
.lm-card__count { font-size: 11px; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); padding: 1px 8px; border-radius: 999px; }

.lm-insights { margin: 0; padding-left: 18px; display: flex; flex-direction: column; gap: 8px; }
.lm-insights li { font-size: 13px; line-height: 1.5; color: var(--on-surface); }

.lm-tiers { display: flex; flex-direction: column; gap: 14px; }
.lm-tier__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.lm-tier__label { font-size: 12.5px; font-weight: 600; color: var(--on-surface); }
.lm-tier__count { font-size: 12px; color: var(--on-surface-var); font-variant-numeric: tabular-nums; }
.lm-tier__bar { height: 8px; border-radius: 999px; background: var(--surface-low); overflow: hidden; }
.lm-tier__fill { height: 100%; border-radius: 999px; }
.lm-tier__fill--high { background: #16A34A; }
.lm-tier__fill--medium { background: #D29922; }
.lm-tier__fill--low { background: #DC2626; }

.lm-table-wrap { overflow-x: auto; }
.lm-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.lm-table th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--card-border); white-space: nowrap; }
.lm-th { display: inline-flex; align-items: center; gap: 5px; }
.lm-th .el-icon { font-size: 13px; }
.lm-table td { padding: 11px 12px; border-bottom: 1px solid var(--card-border); color: var(--on-surface); }
.lm-table tr:last-child td { border-bottom: none; }
.lm-mono { font-variant-numeric: tabular-nums; }
.lm-capitalize { text-transform: capitalize; }
.lm-table-strong { font-weight: 700; color: var(--on-surface); }
.lm-table-link { color: var(--on-surface); font-weight: 600; text-decoration: none; }
.lm-table-link:hover { color: var(--green); text-decoration: underline; }
.lm-badge { display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); background: var(--surface-low); padding: 2px 8px; border-radius: 999px; }
.lm-badge--gap { color: #166534; background: #DCFCE7; }
.lm-empty { font-size: 13px; color: var(--on-surface-var); margin: 0; padding: 8px 0; }

@media (max-width: 900px) {
    .lm-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .lm-grid { grid-template-columns: 1fr; }
}
</style>
