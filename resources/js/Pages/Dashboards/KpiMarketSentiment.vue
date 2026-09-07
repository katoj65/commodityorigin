<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Goods, Opportunity, Trophy, User } from '@element-plus/icons-vue';

const props = defineProps({
    demand: { type: Object, default: () => ({}) },
    auctionOverview: { type: Object, default: () => ({}) },
});

const TIER_LABELS = { high: 'High Demand', medium: 'Medium Demand', low: 'Low Demand' };
const tiers = computed(() => (props.demand.tiers || []).map((t) => ({ ...t, label: TIER_LABELS[t.tier] || t.tier })));

const sentimentLabel = computed(() => {
    const score = props.demand.sentiment_score;
    if (score == null) return 'No data yet';
    if (score >= 75) return 'Bullish';
    if (score >= 45) return 'Neutral';
    return 'Bearish';
});
</script>

<template>
    <DesignPreviewLayout title="Market Sentiment">
        <Head title="Market Sentiment" />

        <div class="kpi-page">
            <header class="kpi-hero">
                <div class="kpi-hero__text">
                    <h1 class="kpi-hero__title"><el-icon><Opportunity /></el-icon> Market Sentiment</h1>
                    <p class="kpi-hero__subtitle">A sentiment score computed from real demand tags across every live listing, plus current auction activity.</p>
                </div>
            </header>

            <div class="kpi-stats">
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Sentiment</span>
                    <strong class="kpi-stat__value">{{ sentimentLabel }}<small v-if="demand.sentiment_score != null">{{ demand.sentiment_score }}/100</small></strong>
                </div>
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Live Listings</span>
                    <strong class="kpi-stat__value">{{ demand.total_listings ?? 0 }}</strong>
                </div>
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Live Auctions</span>
                    <strong class="kpi-stat__value">{{ auctionOverview.live_auctions ?? 0 }}</strong>
                </div>
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Active Buyers</span>
                    <strong class="kpi-stat__value">{{ auctionOverview.active_buyers ?? 0 }}</strong>
                </div>
            </div>

            <section class="kpi-section">
                <div class="kpi-section__head">
                    <h2 class="kpi-section__title"><el-icon><Goods /></el-icon> Demand Breakdown</h2>
                </div>

                <div v-if="demand.total_listings" class="kpi-tiers">
                    <div v-for="tier in tiers" :key="tier.tier" class="kpi-tier">
                        <div class="kpi-tier__head">
                            <span class="kpi-tier__label">{{ tier.label }}</span>
                            <span class="kpi-tier__count">{{ tier.count }} listing{{ tier.count === 1 ? '' : 's' }} · {{ tier.percentage }}%</span>
                        </div>
                        <div class="kpi-tier__bar"><div class="kpi-tier__fill" :class="`kpi-tier__fill--${tier.tier}`" :style="{ width: tier.percentage + '%' }" /></div>
                    </div>
                    <p v-if="demand.unspecified" class="kpi-tier-note">{{ demand.unspecified }} listing{{ demand.unspecified === 1 ? '' : 's' }} with no demand tag set.</p>
                </div>
                <p v-else class="kpi-empty">No live listings to compute sentiment from yet.</p>
            </section>

            <section class="kpi-section" v-for="tier in tiers.filter((t) => t.listings.length)" :key="`list-${tier.tier}`">
                <div class="kpi-section__head">
                    <h2 class="kpi-section__title"><el-icon><Trophy /></el-icon> {{ tier.label }} Listings</h2>
                    <span class="kpi-section__count">{{ tier.listings.length }}</span>
                </div>
                <div class="kpi-table-wrap">
                    <table class="kpi-table">
                        <thead>
                            <tr>
                                <th>Listing</th>
                                <th>Origin</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="listing in tier.listings" :key="listing.id">
                                <td><Link :href="route('market.show', listing.id)" class="kpi-table-link">{{ listing.name || listing.lot_code || `Listing #${listing.id}` }}</Link></td>
                                <td>{{ listing.origin || '—' }}</td>
                                <td class="kpi-mono">{{ listing.currency }} {{ Number(listing.price_per_kg).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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

.kpi-section { background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 18px; }
.kpi-section__head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.kpi-section__title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin: 0; }
.kpi-section__count { font-size: 11px; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); padding: 1px 8px; border-radius: 999px; }

.kpi-tiers { display: flex; flex-direction: column; gap: 16px; }
.kpi-tier { display: flex; flex-direction: column; gap: 6px; }
.kpi-tier__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 13px; }
.kpi-tier__label { font-weight: 700; color: var(--on-surface); }
.kpi-tier__count { color: var(--on-surface-var); font-variant-numeric: tabular-nums; }
.kpi-tier__bar { height: 8px; border-radius: 999px; background: var(--surface-low); overflow: hidden; }
.kpi-tier__fill { height: 100%; border-radius: 999px; }
.kpi-tier__fill--high { background: #16A34A; }
.kpi-tier__fill--medium { background: #D29922; }
.kpi-tier__fill--low { background: #DC2626; }
.kpi-tier-note { font-size: 12px; color: var(--on-surface-var); margin: 4px 0 0; }

.kpi-table-wrap { overflow-x: auto; }
.kpi-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.kpi-table th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--card-border); white-space: nowrap; }
.kpi-table td { padding: 11px 12px; border-bottom: 1px solid var(--card-border); color: var(--on-surface); }
.kpi-table tr:last-child td { border-bottom: none; }
.kpi-mono { font-variant-numeric: tabular-nums; }
.kpi-table-link { color: var(--on-surface); font-weight: 600; text-decoration: none; }
.kpi-table-link:hover { color: var(--green); text-decoration: underline; }
.kpi-empty { font-size: 13px; color: var(--on-surface-var); margin: 0; padding: 8px 0; }

@media (max-width: 900px) {
    .kpi-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
</style>
