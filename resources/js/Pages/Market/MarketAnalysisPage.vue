<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale,
    ArcElement, PointElement, LineElement, Filler,
} from 'chart.js';
import {
    DataAnalysis, Tickets, CircleCheck, Coin, Box, Sunny, InfoFilled,
    MagicStick, GoodsFilled, Location, Cloudy, Ship, Money, Opportunity,
    WarningFilled, UserFilled, TrendCharts, Odometer, Connection, Trophy,
    Medal, Reading, Promotion, ArrowUp, ArrowDown, Grid,
} from '@element-plus/icons-vue';
import MarketPage from './MarketPage.vue';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement, PointElement, LineElement, Filler);

const props = defineProps({
    analysis: {
        type: Object,
        default: () => ({
            total_listings: 0,
            total_volume_kg: 0,
            average_price_per_kg: null,
            min_price_per_kg: null,
            max_price_per_kg: null,
            average_quality_score: null,
            types: [],
            origins: [],
            demand: [],
            insights: [],
        }),
    },
    intel: {
        type: Object,
        default: () => ({
            opportunities: [],
            competitors: [],
            tradeFlows: [],
            forecast: {
                price: [], demand: [], weather: [], export: [], risk: [],
            },
            exchangeRates: [],
            topBuyers: [],
            topSellers: [],
        }),
    },
});

const searchQuery = ref('');

const hasData = computed(() => props.analysis.total_listings > 0);

const fmtNum = (n) => (n != null ? Number(n).toLocaleString(undefined, { maximumFractionDigits: 0 }) : '—');
const fmtPrice = (n) => (n != null ? Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtMoney = (n) => (n != null ? `$${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '—');

const demandTone = (label) => ({ High: 'man-tone--green', Medium: 'man-tone--amber', Low: 'man-tone--red' }[label] ?? 'man-tone--muted');
const directionTone = (direction) => ({ up: 'man-tone--green', down: 'man-tone--red' }[direction] ?? 'man-tone--muted');

/* ── Sections nav ─────────────────────────────────────────────────────── */
const sections = [
    { id: 'overview', label: 'Market Overview' },
    { id: 'ai-summary', label: 'AI Market Summary' },
    { id: 'price-analysis', label: 'Price Analysis' },
    { id: 'supply-demand', label: 'Supply & Demand' },
    { id: 'origin-analysis', label: 'Origin Analysis' },
    { id: 'weather-climate', label: 'Weather & Climate' },
    { id: 'trade-export', label: 'Trade & Export' },
    { id: 'currency-economics', label: 'Currency & Economics' },
    { id: 'opportunities', label: 'Market Opportunities' },
    { id: 'risks', label: 'Market Risks' },
    { id: 'competitors', label: 'Competitor Analysis' },
    { id: 'demand-forecast', label: 'Demand Forecast' },
    { id: 'price-forecast', label: 'Price Forecast' },
    { id: 'trade-flows', label: 'Trade Flows' },
    { id: 'top-buyers', label: 'Top Buyers' },
    { id: 'top-sellers', label: 'Top Sellers' },
    { id: 'coffee-news', label: 'Coffee News' },
    { id: 'ai-recommendations', label: 'AI Recommendations' },
];

const activeSection = ref('overview');
let sectionObserver = null;

onMounted(() => {
    const targets = sections.map((s) => document.getElementById(s.id)).filter(Boolean);
    if (!targets.length) return;

    sectionObserver = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((e) => e.isIntersecting)
                .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
            if (visible[0]) activeSection.value = visible[0].target.id;
        },
        { rootMargin: '-100px 0px -70% 0px', threshold: 0 },
    );
    targets.forEach((el) => sectionObserver.observe(el));
});

onBeforeUnmount(() => sectionObserver?.disconnect());

/* ── Derived, real-data views ─────────────────────────────────────────── */
const destinationBreakdown = computed(() => {
    const totals = new Map();
    for (const flow of props.intel.tradeFlows) {
        totals.set(flow.destination, (totals.get(flow.destination) ?? 0) + flow.volume_kg);
    }
    const grandTotal = [...totals.values()].reduce((a, b) => a + b, 0) || 1;
    return [...totals.entries()]
        .map(([destination, volume]) => ({ destination, volume, share: Math.round((volume / grandTotal) * 1000) / 10 }))
        .sort((a, b) => b.volume - a.volume);
});

const maxTradeFlowVolume = computed(() => Math.max(1, ...props.intel.tradeFlows.map((f) => f.volume_kg)));

const priceForecastByType = computed(() => {
    const groups = {};
    for (const f of props.intel.forecast.price) {
        groups[f.crop_type] ??= { crop_type: f.crop_type, horizons: {} };
        groups[f.crop_type].horizons[f.horizon] = f;
    }
    return Object.values(groups);
});

const originConcentration = computed(() => {
    const origins = props.analysis.origins;
    if (!origins.length || !props.analysis.total_volume_kg) return null;
    const top = origins[0];
    const share = Math.round((top.total_volume_kg / props.analysis.total_volume_kg) * 1000) / 10;
    return share >= 35 ? { label: top.label, share } : null;
});

const aiSummary = computed(() => props.analysis.insights.join(' '));

const recommendations = computed(() => {
    const items = [];

    for (const o of props.intel.opportunities.slice(0, 2)) {
        items.push(`Consider listing more ${o.type} — buyer demand share (${o.high_demand_share}%) is outpacing its supply share (${o.share}%) by ${o.gap_score} points.`);
    }
    for (const r of props.intel.forecast.risk.slice(0, 2)) {
        items.push(`${r.headline}: ${r.detail}`);
    }
    if (props.intel.topBuyers[0]) {
        items.push(`Prioritize ${props.intel.topBuyers[0].name}, the highest-value buyer with ${fmtMoney(props.intel.topBuyers[0].total_value)} in orders.`);
    }
    if (originConcentration.value) {
        items.push(`${originConcentration.value.label} accounts for ${originConcentration.value.share}% of listed volume — diversifying origins could reduce supply concentration risk.`);
    }

    return items.slice(0, 6);
});

/* ── Coffee news — illustrative examples, not a live feed ────────────── */
const coffeeNews = [
    { title: 'ICO reports global coffee exports steady amid tightening supply', tag: 'Supply' },
    { title: 'Specialty roasters increase direct-trade sourcing from East Africa', tag: 'Trade' },
    { title: 'Arabica futures hold near multi-week highs on weather concerns', tag: 'Price' },
    { title: 'Vietnam robusta stocks rebuild after last season\'s shortfall', tag: 'Supply' },
];

/* ══════════════════════════════════════════════════════════════════════
   Charts — shared options + per-section datasets, all built from real,
   already-computed page data.
   ══════════════════════════════════════════════════════════════════════ */
const palette = ['#004532', '#059669', '#d97706', '#0ea5e9', '#dc2626', '#7c3aed', '#78716c', '#0891b2'];

const tooltipBase = { backgroundColor: '#111827', padding: 10, cornerRadius: 8, titleFont: { size: 12 }, bodyFont: { size: 12 } };

const barOptionsHorizontal = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: tooltipBase },
    scales: {
        x: { grid: { color: '#f1f5f4' }, ticks: { font: { size: 11 }, color: '#6b7280' } },
        y: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: '#111827' } },
    },
};

const barOptionsVertical = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: tooltipBase },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: '#111827' } },
        y: { grid: { color: '#f1f5f4' }, ticks: { font: { size: 11 }, color: '#6b7280' } },
    },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '65%',
    plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', font: { size: 11 }, padding: 10, color: '#374151' } },
        tooltip: tooltipBase,
    },
};

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', align: 'end', labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', font: { size: 11 }, color: '#374151' } },
        tooltip: tooltipBase,
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#6b7280' } },
        y: { grid: { color: '#f1f5f4' }, ticks: { font: { size: 11 }, color: '#6b7280', callback: (v) => `${v}%` } },
    },
};

const typesChartData = computed(() => ({
    labels: props.analysis.types.map((t) => t.label),
    datasets: [{ label: 'Share of listings (%)', data: props.analysis.types.map((t) => t.share), backgroundColor: '#059669', borderRadius: 6, barThickness: 20 }],
}));

const demandChartData = computed(() => ({
    labels: props.analysis.demand.map((d) => d.label),
    datasets: [{
        data: props.analysis.demand.map((d) => d.count),
        backgroundColor: props.analysis.demand.map((d) => ({ High: '#059669', Medium: '#d97706', Low: '#dc2626' }[d.label] ?? '#78716c')),
        borderWidth: 0,
    }],
}));

const originsChartData = computed(() => ({
    labels: props.analysis.origins.map((o) => o.label),
    datasets: [{ label: 'Volume (kg)', data: props.analysis.origins.map((o) => o.total_volume_kg), backgroundColor: '#004532', borderRadius: 6, barThickness: 18 }],
}));

const priceChartData = computed(() => ({
    labels: ['Lowest', 'Average', 'Highest'],
    datasets: [{
        label: 'Price / kg',
        data: [props.analysis.min_price_per_kg, props.analysis.average_price_per_kg, props.analysis.max_price_per_kg],
        backgroundColor: ['#a7f3d0', '#059669', '#004532'],
        borderRadius: 6,
        barThickness: 40,
    }],
}));

const destinationChartData = computed(() => ({
    labels: destinationBreakdown.value.map((d) => d.destination),
    datasets: [{ data: destinationBreakdown.value.map((d) => d.volume), backgroundColor: palette, borderWidth: 0 }],
}));

const priceForecastChartData = computed(() => {
    const parsePct = (headline) => parseFloat((headline || '0').replace('%', '').replace('+', '')) || 0;
    const colors = { Arabica: '#004532', Robusta: '#d97706' };
    return {
        labels: ['7-Day', '30-Day', '90-Day'],
        datasets: priceForecastByType.value.map((g) => {
            const color = colors[g.crop_type] ?? '#6b7280';
            return {
                label: g.crop_type,
                data: ['7-Day', '30-Day', '90-Day'].map((h) => parsePct(g.horizons[h]?.headline)),
                borderColor: color,
                backgroundColor: `${color}22`,
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: color,
            };
        }),
    };
});

const opportunitiesChartData = computed(() => ({
    labels: props.intel.opportunities.map((o) => o.type),
    datasets: [{ label: 'Demand/supply gap (pts)', data: props.intel.opportunities.map((o) => o.gap_score), backgroundColor: '#059669', borderRadius: 6, barThickness: 20 }],
}));

const competitorsChartData = computed(() => ({
    labels: props.intel.competitors.map((c) => c.name),
    datasets: [{ label: 'Volume (kg)', data: props.intel.competitors.map((c) => c.volume_kg), backgroundColor: '#004532', borderRadius: 6, barThickness: 18 }],
}));

const topBuyersChartData = computed(() => ({
    labels: props.intel.topBuyers.map((b) => b.name),
    datasets: [{ label: 'Total value ($)', data: props.intel.topBuyers.map((b) => b.total_value), backgroundColor: '#d97706', borderRadius: 6, barThickness: 20 }],
}));

const topSellersChartData = computed(() => ({
    labels: props.intel.topSellers.map((s) => s.name),
    datasets: [{ label: 'Total value ($)', data: props.intel.topSellers.map((s) => s.total_value), backgroundColor: '#059669', borderRadius: 6, barThickness: 20 }],
}));

const currencyChartData = computed(() => ({
    labels: props.intel.exchangeRates.map((r) => r.pair),
    datasets: [{
        label: 'Daily change (%)',
        data: props.intel.exchangeRates.map((r) => r.daily_change_percent ?? 0),
        backgroundColor: props.intel.exchangeRates.map((r) => (r.up ? '#059669' : '#dc2626')),
        borderRadius: 6,
        barThickness: 18,
    }],
}));
</script>

<template>
    <MarketPage v-model:search-query="searchQuery">
        <div class="mkt-body">


       

            <div class="man-content">
                <!-- ── Empty state ─────────────────────────────────────── -->
                <div v-if="!hasData" class="man-panel man-empty-card">
                    <el-icon style="font-size:2.25rem;color:#d1d5db;"><Box /></el-icon>
                    <p class="man-muted mt-2 mb-0">{{ analysis.insights[0] }}</p>
                </div>

                <template v-else>
                    <!-- ══ 1. Market Overview ═══════════════════════════ -->
                    <section id="overview" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><DataAnalysis /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Market Overview</h2>
                                <p class="man-section__desc">The headline numbers behind every live listing right now.</p>
                            </div>
                        </div>
                        <div class="man-kpi-row">
                            <div class="man-kpi-col">
                                <div class="man-kpi-col__label"><el-icon><Tickets /></el-icon> Live Listings</div>
                                <div class="man-kpi-col__value">{{ fmtNum(analysis.total_listings) }}</div>
                            </div>
                            <div class="man-kpi-col">
                                <div class="man-kpi-col__label"><el-icon><Box /></el-icon> Total Volume</div>
                                <div class="man-kpi-col__value">{{ fmtNum(analysis.total_volume_kg) }} kg</div>
                            </div>
                            <div class="man-kpi-col">
                                <div class="man-kpi-col__label"><el-icon><Coin /></el-icon> Average Price / kg</div>
                                <div class="man-kpi-col__value">{{ fmtPrice(analysis.average_price_per_kg) }}</div>
                            </div>
                            <div class="man-kpi-col">
                                <div class="man-kpi-col__label"><el-icon><Sunny /></el-icon> Average Quality</div>
                                <div class="man-kpi-col__value">{{ analysis.average_quality_score ?? '—' }}</div>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 2. AI Market Summary ═════════════════════════ -->
                    <section id="ai-summary" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon man-section__icon--ai"><el-icon><MagicStick /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">AI Market Summary</h2>
                                <p class="man-section__desc">A plain-language read of the live market, generated from real listing data.</p>
                            </div>
                        </div>
                        <div class="man-panel man-ai-card">
                            <p class="man-ai-text">{{ aiSummary }}</p>
                        </div>
                    </section>

                    <!-- ══ 3. Price Analysis ════════════════════════════ -->
                    <section id="price-analysis" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Coin /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Price Analysis</h2>
                                <p class="man-section__desc">Where prices sit today, from lowest to highest asking price per kg.</p>
                            </div>
                        </div>
                        <div v-if="analysis.min_price_per_kg !== null" class="man-analytics-grid">
                            <div class="man-panel">
                                <div class="man-chart-wrap man-chart-wrap--sm">
                                    <Bar :data="priceChartData" :options="barOptionsVertical" />
                                </div>
                            </div>
                            <div class="man-panel man-aside-card">
                                <div class="man-kpi-line">
                                    <span class="man-range__label">Average</span>
                                    <span class="man-range__value">{{ fmtPrice(analysis.average_price_per_kg) }}</span>
                                </div>
                                <div class="man-kpi-line">
                                    <span class="man-range__label">Lowest</span>
                                    <span class="man-range__value">{{ fmtPrice(analysis.min_price_per_kg) }}</span>
                                </div>
                                <div class="man-kpi-line">
                                    <span class="man-range__label">Highest</span>
                                    <span class="man-range__value">{{ fmtPrice(analysis.max_price_per_kg) }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 4. Supply & Demand ═══════════════════════════ -->
                    <section id="supply-demand" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><GoodsFilled /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Supply & Demand</h2>
                                <p class="man-section__desc">How available coffee types break down, and how buyer demand is spread across them.</p>
                            </div>
                        </div>
                        <div class="man-analytics-grid">
                            <div class="man-panel">
                                <div class="man-card-title mb-3">Coffee Types on the Market</div>
                                <div class="man-chart-wrap">
                                    <Bar :data="typesChartData" :options="barOptionsHorizontal" />
                                </div>
                            </div>
                            <div class="man-panel">
                                <div class="man-card-title mb-3">Buyer Demand Levels</div>
                                <div v-if="analysis.demand.length" class="man-chart-wrap">
                                    <Doughnut :data="demandChartData" :options="doughnutOptions" />
                                </div>
                                <div v-else class="man-muted">No demand data yet.</div>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 5. Origin Analysis ═══════════════════════════ -->
                    <section id="origin-analysis" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Location /></el-icon></div>
                            <div class="flex-grow-1">
                                <h2 class="man-section__title">Origin Analysis</h2>
                                <p class="man-section__desc">The growing regions supplying the most volume to the market today.</p>
                            </div>
                            <Link :href="route('market.compare')" class="mkt-btn-group__item">
                                <el-icon><Grid /></el-icon> Compare Countries
                            </Link>
                        </div>
                        <div class="man-analytics-grid">
                            <div class="man-panel">
                                <div class="man-chart-wrap">
                                    <Bar :data="originsChartData" :options="barOptionsHorizontal" />
                                </div>
                            </div>
                            <div class="man-panel man-aside-card">
                                <div v-for="(o, i) in analysis.origins" :key="o.label" class="man-origin-row">
                                    <span class="man-origin-rank">{{ i + 1 }}</span>
                                    <span class="man-breakdown-row__label flex-grow-1">{{ o.label }}</span>
                                    <span class="man-muted">{{ fmtNum(o.total_volume_kg) }} kg</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 6. Weather & Climate ═════════════════════════ -->
                    <section id="weather-climate" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Cloudy /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Weather & Climate</h2>
                                <p class="man-section__desc">Growing-region conditions that could affect near-term supply.</p>
                            </div>
                        </div>
                        <div v-if="intel.forecast.weather.length" class="man-signal-grid">
                            <div v-for="f in intel.forecast.weather" :key="f.id" class="man-card man-signal-card">
                                <div class="man-signal-card__top">
                                    <span class="man-tag">{{ f.crop_type }}</span>
                                    <span class="man-tone" :class="directionTone(f.direction)">{{ f.headline }}</span>
                                </div>
                                <p class="man-muted mb-0">{{ f.detail }}</p>
                                <div class="man-bar-track man-confidence-bar"><div class="man-bar-fill" :style="{ width: f.confidence + '%' }"></div></div>
                                <div class="man-signal-card__confidence">{{ f.confidence }}% confidence</div>
                            </div>
                        </div>
                        <div v-else class="man-panel man-muted">No weather signals available right now.</div>
                    </section>

                    <!-- ══ 7. Trade & Export ════════════════════════════ -->
                    <section id="trade-export" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Ship /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Trade & Export</h2>
                                <p class="man-section__desc">Where listed volume is headed, and export-side signals worth watching.</p>
                            </div>
                        </div>
                        <div class="man-analytics-grid mb-3">
                            <div class="man-panel">
                                <div class="man-card-title mb-3">Volume by Destination</div>
                                <div class="man-chart-wrap">
                                    <Doughnut :data="destinationChartData" :options="doughnutOptions" />
                                </div>
                            </div>
                            <div class="man-panel man-aside-card">
                                <div v-for="d in destinationBreakdown" :key="d.destination" class="man-origin-row">
                                    <span class="man-breakdown-row__label flex-grow-1">{{ d.destination }}</span>
                                    <span class="man-muted">{{ fmtNum(d.volume) }} kg · {{ d.share }}%</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="intel.forecast.export.length" class="man-signal-grid">
                            <div v-for="f in intel.forecast.export" :key="f.id" class="man-card man-signal-card">
                                <div class="man-signal-card__top">
                                    <span class="man-tag">{{ f.crop_type }}</span>
                                    <span class="man-tone" :class="directionTone(f.direction)">{{ f.headline }}</span>
                                </div>
                                <p class="man-muted mb-0">{{ f.detail }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 8. Currency & Economics ══════════════════════ -->
                    <section id="currency-economics" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Money /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Currency & Economics</h2>
                                <p class="man-section__desc">Live exchange rates relevant to cross-border coffee trade.</p>
                            </div>
                        </div>
                        <div class="man-panel mb-3">
                            <div class="man-card-title mb-3">Daily Movement</div>
                            <div class="man-chart-wrap">
                                <Bar :data="currencyChartData" :options="barOptionsHorizontal" />
                            </div>
                        </div>
                        <div class="man-rate-grid">
                            <div v-for="r in intel.exchangeRates" :key="r.id" class="man-card man-rate-card">
                                <div class="man-rate-card__pair">{{ r.pair }}</div>
                                <div class="man-rate-card__value">{{ r.rate }}</div>
                                <div class="man-tone man-tone--icon" :class="r.up ? 'man-tone--green' : 'man-tone--red'">
                                    <el-icon><component :is="r.up ? ArrowUp : ArrowDown" /></el-icon>
                                    {{ r.daily_change_percent != null ? Math.abs(r.daily_change_percent) + '%' : '—' }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 9. Market Opportunities ══════════════════════ -->
                    <section id="opportunities" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Opportunity /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Market Opportunities</h2>
                                <p class="man-section__desc">Coffee types where buyer demand is outpacing available supply.</p>
                            </div>
                        </div>
                        <template v-if="intel.opportunities.length">
                            <div class="man-analytics-grid mb-3">
                                <div class="man-panel">
                                    <div class="man-chart-wrap">
                                        <Bar :data="opportunitiesChartData" :options="barOptionsHorizontal" />
                                    </div>
                                </div>
                                <div class="man-panel man-aside-card">
                                    <div v-for="o in intel.opportunities" :key="o.type" class="man-origin-row">
                                        <span class="man-breakdown-row__label flex-grow-1">{{ o.type }}</span>
                                        <span class="man-muted">demand {{ o.high_demand_share }}% vs. supply {{ o.share }}%</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div v-else class="man-panel man-muted">No standout supply gaps right now — demand and supply are broadly in balance.</div>
                    </section>

                    <!-- ══ 10. Market Risks ═════════════════════════════ -->
                    <section id="risks" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon man-section__icon--warn"><el-icon><WarningFilled /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Market Risks</h2>
                                <p class="man-section__desc">Signals that could work against sellers or buyers in the near term.</p>
                            </div>
                        </div>
                        <div class="man-signal-grid">
                            <div v-if="originConcentration" class="man-card man-signal-card">
                                <div class="man-signal-card__top">
                                    <span class="man-tag">Concentration</span>
                                    <span class="man-tone man-tone--amber">{{ originConcentration.share }}%</span>
                                </div>
                                <p class="man-muted mb-0">{{ originConcentration.label }} accounts for a large share of listed volume — a supply shock there would have an outsized market impact.</p>
                            </div>
                            <div v-for="f in intel.forecast.risk" :key="f.id" class="man-card man-signal-card">
                                <div class="man-signal-card__top">
                                    <span class="man-tag">{{ f.crop_type }} · {{ f.category }}</span>
                                    <span class="man-tone man-tone--red">{{ f.headline }}</span>
                                </div>
                                <p class="man-muted mb-0">{{ f.detail }}</p>
                                <div class="man-bar-track man-confidence-bar man-confidence-bar--red"><div class="man-bar-fill" :style="{ width: f.confidence + '%' }"></div></div>
                                <div class="man-signal-card__confidence">{{ f.confidence }}% confidence</div>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 11. Competitor Analysis ══════════════════════ -->
                    <section id="competitors" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><UserFilled /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Competitor Analysis</h2>
                                <p class="man-section__desc">The sellers currently competing for buyer attention on the live market.</p>
                            </div>
                        </div>
                        <div class="man-analytics-grid">
                            <div class="man-panel">
                                <div class="man-chart-wrap">
                                    <Bar :data="competitorsChartData" :options="barOptionsHorizontal" />
                                </div>
                            </div>
                            <div class="man-panel man-aside-card">
                                <div v-for="(c, i) in intel.competitors" :key="c.name + i" class="man-origin-row">
                                    <span class="man-origin-rank">{{ i + 1 }}</span>
                                    <span class="man-breakdown-row__label flex-grow-1">{{ c.name }}</span>
                                    <span class="man-muted">Q{{ c.average_quality }} · {{ fmtPrice(c.average_price) }}/kg</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 12. Demand Forecast ══════════════════════════ -->
                    <section id="demand-forecast" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><TrendCharts /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Demand Forecast</h2>
                                <p class="man-section__desc">Where buyer demand looks to be heading, by coffee type.</p>
                            </div>
                        </div>
                        <div class="man-signal-grid">
                            <div v-for="f in intel.forecast.demand" :key="f.id" class="man-card man-signal-card">
                                <div class="man-signal-card__top">
                                    <span class="man-tag">{{ f.crop_type }}</span>
                                    <span class="man-tone" :class="directionTone(f.direction)">{{ f.headline }}</span>
                                </div>
                                <p class="man-muted mb-0">{{ f.detail }}</p>
                                <div class="man-bar-track man-confidence-bar"><div class="man-bar-fill" :style="{ width: f.confidence + '%' }"></div></div>
                                <div class="man-signal-card__confidence">{{ f.confidence }}% confidence</div>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 13. Price Forecast ═══════════════════════════ -->
                    <section id="price-forecast" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Odometer /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Price Forecast</h2>
                                <p class="man-section__desc">Projected price movement over the next 7, 30, and 90 days, by coffee type.</p>
                            </div>
                        </div>
                        <div class="man-panel">
                            <div class="man-chart-wrap">
                                <Line :data="priceForecastChartData" :options="lineOptions" />
                            </div>
                        </div>
                    </section>

                    <!-- ══ 14. Trade Flows ══════════════════════════════ -->
                    <section id="trade-flows" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Connection /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Trade Flows</h2>
                                <p class="man-section__desc">Origin-to-destination volume, derived from live listings.</p>
                            </div>
                        </div>
                        <el-table :data="intel.tradeFlows" class="mkt-el-table" stripe empty-text="No trade flows yet.">
                            <el-table-column width="56">
                                <template #default>
                                    <div class="mkt-thumb"><el-icon><Ship /></el-icon></div>
                                </template>
                            </el-table-column>
                            <el-table-column label="Origin" min-width="140">
                                <template #header><el-icon class="mkt-th-icon"><Location /></el-icon>Origin</template>
                                <template #default="{ row }"><span class="mkt-item-name">{{ row.origin }}</span></template>
                            </el-table-column>
                            <el-table-column label="Destination" min-width="140">
                                <template #header><el-icon class="mkt-th-icon"><Ship /></el-icon>Destination</template>
                                <template #default="{ row }">{{ row.destination }}</template>
                            </el-table-column>
                            <el-table-column label="Listings" min-width="100" align="right" header-align="left">
                                <template #header><el-icon class="mkt-th-icon"><Tickets /></el-icon>Listings</template>
                                <template #default="{ row }"><span class="mkt-num">{{ row.listings }}</span></template>
                            </el-table-column>
                            <el-table-column label="Volume" min-width="160" align="right" header-align="left">
                                <template #header><el-icon class="mkt-th-icon"><Box /></el-icon>Volume</template>
                                <template #default="{ row }">
                                    <div class="man-table-bar-wrap">
                                        <span class="mkt-num">{{ fmtNum(row.volume_kg) }} kg</span>
                                        <div class="man-bar-track man-table-bar"><div class="man-bar-fill" :style="{ width: (row.volume_kg / maxTradeFlowVolume * 100) + '%' }"></div></div>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column label="Avg Price" min-width="110" align="right" header-align="left">
                                <template #header><el-icon class="mkt-th-icon"><Coin /></el-icon>Avg Price</template>
                                <template #default="{ row }"><span class="mkt-num">{{ fmtPrice(row.average_price) }}/kg</span></template>
                            </el-table-column>
                        </el-table>
                    </section>

                    <!-- ══ 15. Top Buyers ═══════════════════════════════ -->
                    <section id="top-buyers" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon man-section__icon--gold"><el-icon><Trophy /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Top Buyers</h2>
                                <p class="man-section__desc">Ranked by total value across every order placed.</p>
                            </div>
                        </div>
                        <div v-if="intel.topBuyers.length" class="man-analytics-grid">
                            <div class="man-panel">
                                <div class="man-chart-wrap">
                                    <Bar :data="topBuyersChartData" :options="barOptionsHorizontal" />
                                </div>
                            </div>
                            <div class="man-panel man-aside-card">
                                <div v-for="(b, i) in intel.topBuyers" :key="b.name + i" class="man-origin-row">
                                    <span class="man-origin-rank">{{ i + 1 }}</span>
                                    <span class="man-breakdown-row__label flex-grow-1">{{ b.name }}</span>
                                    <span class="man-muted">{{ b.orders }} orders · {{ fmtMoney(b.total_value) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="man-panel man-muted">No buyer orders yet.</div>
                    </section>

                    <!-- ══ 16. Top Sellers ══════════════════════════════ -->
                    <section id="top-sellers" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon man-section__icon--gold"><el-icon><Medal /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Top Sellers</h2>
                                <p class="man-section__desc">Ranked by total value across every order fulfilled.</p>
                            </div>
                        </div>
                        <div v-if="intel.topSellers.length" class="man-analytics-grid">
                            <div class="man-panel">
                                <div class="man-chart-wrap">
                                    <Bar :data="topSellersChartData" :options="barOptionsHorizontal" />
                                </div>
                            </div>
                            <div class="man-panel man-aside-card">
                                <div v-for="(s, i) in intel.topSellers" :key="s.name + i" class="man-origin-row">
                                    <span class="man-origin-rank">{{ i + 1 }}</span>
                                    <span class="man-breakdown-row__label flex-grow-1">{{ s.name }}</span>
                                    <span class="man-muted">{{ s.orders }} orders · {{ fmtMoney(s.total_value) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="man-panel man-muted">No seller orders yet.</div>
                    </section>

                    <!-- ══ 17. Coffee News ══════════════════════════════ -->
                    <section id="coffee-news" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon"><el-icon><Reading /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">Coffee News</h2>
                                <p class="man-section__desc">Illustrative industry headlines — connect a live feed for real-time updates.</p>
                            </div>
                        </div>
                        <div class="man-panel">
                            <div v-for="(n, i) in coffeeNews" :key="i" class="man-news-row">
                                <span class="man-tag">{{ n.tag }}</span>
                                <span class="man-breakdown-row__label">{{ n.title }}</span>
                            </div>
                        </div>
                    </section>

                    <!-- ══ 18. AI Recommendations ═══════════════════════ -->
                    <section id="ai-recommendations" class="man-section">
                        <div class="man-section__head">
                            <div class="man-section__icon man-section__icon--ai"><el-icon><Promotion /></el-icon></div>
                            <div>
                                <h2 class="man-section__title">AI Recommendations</h2>
                                <p class="man-section__desc">Actionable next steps, derived from the data above.</p>
                            </div>
                        </div>
                        <div class="man-panel man-ai-card">
                            <ul class="man-insight-list">
                                <li v-for="(r, i) in recommendations" :key="i">
                                    <el-icon class="man-insight-check"><CircleCheck /></el-icon>
                                    <span>{{ r }}</span>
                                </li>
                            </ul>
                        </div>
                    </section>
                </template>
            </div>
        </div>
    </MarketPage>
</template>

<style scoped>
.man-muted { color: var(--on-surface-var); font-size: .8125rem; }

/* ── Body ─────────────────────────────────────────────────────────────── */
.mkt-body { padding: 1.5rem 0 3rem; }
.mkt-section__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; padding: 0 1.5rem; }
.mkt-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }

/* ── Hero header ─────────────────────────────────────────────────────── */
.man-hero { padding-bottom: 1.25rem; }
.man-hero-title { font-family: 'IBM Plex Mono', monospace; }
.man-kicker-live { display: inline-flex; align-items: center; }
.man-live-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #10b981; margin-right: 6px; box-shadow: 0 0 0 0 rgba(16,185,129,.5); animation: man-pulse 2s infinite; }
@keyframes man-pulse {
    0% { box-shadow: 0 0 0 0 rgba(16,185,129,.45); }
    70% { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
}
.mkt-title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }

.mkt-btn-group__item { display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; font-size: .75rem; font-weight: 700; letter-spacing: .01em; text-decoration: none; color: var(--green); background: #fff; border: 1px solid var(--green); border-radius: 8px; cursor: pointer; white-space: nowrap; transition: background .15s ease, color .15s ease; }
.mkt-btn-group__item:hover { background: #f0f5f3; }

/* ── In-page nav — a slim scrollable tab strip, not a wall of pills ───── */
.man-nav { display: flex; flex-wrap: nowrap; align-items: center; gap: 1.5rem; padding: 0 1.5rem 0; margin-bottom: 1.75rem; border-bottom: 1px solid var(--border); overflow-x: auto; scrollbar-width: none; }
.man-nav::-webkit-scrollbar { display: none; }
.man-nav__chip { flex-shrink: 0; font-size: .75rem; font-weight: 600; padding: 10px 1px; background: none; border: none; border-bottom: 2px solid transparent; border-radius: 0; color: var(--on-surface-var); text-decoration: none; white-space: nowrap; transition: color .12s ease, border-color .12s ease; }
.man-nav__chip:hover { color: var(--on-surface); }
.man-nav__chip.is-active { color: var(--green); border-bottom-color: var(--green); }

.man-content { padding: 0 1.5rem; scroll-behavior: smooth; }

/* ── Sections ────────────────────────────────────────────────────────── */
.man-section { padding-top: 2.25rem; margin-top: 2.25rem; border-top: 1px solid var(--border); scroll-margin-top: 4.5rem; }
.man-section:first-child { padding-top: 0; margin-top: 0; border-top: none; }
.man-section__head { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 1.125rem; }
.man-section__icon { color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; margin-top: 2px; }
.man-section__icon--ai { color: var(--gold); }
.man-section__icon--warn { color: #dc2626; }
.man-section__icon--gold { color: var(--gold); }
.man-section__title { font-size: 1rem; font-weight: 800; letter-spacing: -.01em; margin: 0 0 2px; color: var(--on-surface); }
.man-section__desc { font-size: .8125rem; color: var(--on-surface-var); margin: 0; max-width: 640px; }

/* ── Overview KPI strip — divided row, spans edge to edge ─────────────── */
.man-kpi-row { display: flex; flex-wrap: wrap; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.man-kpi-col { flex: 1 1 0; min-width: 160px; padding: .9375rem 1.25rem; border-left: 1px solid var(--border); }
.man-kpi-col:first-child { border-left: none; }
.man-kpi-col__label { display: flex; align-items: center; gap: 6px; font-size: .6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin-bottom: 8px; white-space: nowrap; }
.man-kpi-col__label :deep(.el-icon) { font-size: 13px; color: var(--green); opacity: .85; }
.man-kpi-col__value { font-family: 'IBM Plex Mono', monospace; font-size: 1.1875rem; font-weight: 700; color: var(--on-surface); letter-spacing: -.01em; }
@media (max-width: 767.98px) {
    .man-kpi-row { flex-direction: column; }
    .man-kpi-col { border-left: none; border-top: 1px solid var(--border); }
    .man-kpi-col:first-child { border-top: none; }
}

/* ── Cards (grid items — forecast signals, currency rates) ────────────── */
.man-card { background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 1rem; }
.man-card-title { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }

/* ── Panels (borderless — chart wraps, ranked lists, text blocks) ─────── */
.man-panel { padding: 0; }
.man-empty-card { display: flex; flex-direction: column; align-items: center; padding: 3rem 1rem; text-align: center; }

/* ── Analytics grid (chart + companion panel) ───────────────────────── */
.man-analytics-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.5rem; align-items: stretch; }
.man-chart-wrap { position: relative; height: 240px; }
.man-chart-wrap--sm { height: 190px; }
.man-aside-card { display: flex; flex-direction: column; justify-content: center; max-height: 260px; overflow-y: auto; border-left: 1px solid var(--border); padding-left: 1.25rem; }
.man-kpi-line { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--surface-low); }
.man-kpi-line:last-child { border-bottom: none; }

/* ── AI cards ────────────────────────────────────────────────────────── */
.man-ai-card { background: linear-gradient(135deg, #f0fdf4, #ffffff); border: 1px solid #bbf7d0; border-radius: 6px; padding: 1.125rem; }
.man-ai-text { font-size: .875rem; line-height: 1.6; color: var(--on-surface); margin: 0; }

/* ── Insights ────────────────────────────────────────────────────────── */
.man-insight-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.man-insight-list li { display: flex; align-items: flex-start; gap: 8px; font-size: .8438rem; color: var(--on-surface); line-height: 1.5; }
.man-insight-check { color: #16a34a; font-size: 15px; margin-top: 2px; flex-shrink: 0; }

/* ── Price range labels ──────────────────────────────────────────────── */
.man-range__label { font-size: .625rem; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); font-weight: 700; }
.man-range__value { font-family: 'IBM Plex Mono', monospace; font-size: .9375rem; font-weight: 700; color: var(--on-surface); }

/* ── Breakdown rows ──────────────────────────────────────────────────── */
.man-breakdown-row__label { font-size: .875rem; font-weight: 700; color: var(--on-surface); }

.man-bar-track { height: 6px; border-radius: 999px; background: var(--surface-low); overflow: hidden; }
.man-bar-fill { height: 100%; border-radius: 999px; background: var(--green); }
.man-confidence-bar { margin-top: 10px; }
.man-confidence-bar--red .man-bar-fill { background: #dc2626; }

/* ── Origins / ranked rows ───────────────────────────────────────────── */
.man-origin-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.man-origin-row:last-child { border-bottom: none; }
.man-origin-rank { width: 22px; height: 22px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); font-size: .6875rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Tones / tags ────────────────────────────────────────────────────── */
.man-tone { display: inline-flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 600; padding: 3px 10px 3px 8px; border-radius: 999px; line-height: 1.5; white-space: nowrap; }
.man-tone::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.man-tone--icon::before { display: none; }
.man-tone--icon svg { width: 11px; height: 11px; }
.man-tone--green { background: #ecfdf5; color: #059669; }
.man-tone--amber { background: #fffbeb; color: #d97706; }
.man-tone--red { background: #fef2f2; color: #dc2626; }
.man-tone--muted { background: #f5f5f4; color: #78716c; }
.man-tag { font-size: .625rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); background: var(--surface-low); border-radius: 6px; padding: 3px 8px; }

/* ── Signal / opportunity / risk cards ──────────────────────────────── */
.man-signal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
.man-signal-card__top { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
.man-signal-card__confidence { font-size: .6875rem; color: var(--on-surface-var); margin-top: 6px; }

/* ── Currency rate cards ─────────────────────────────────────────────── */
.man-rate-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
.man-rate-card { text-align: center; }
.man-rate-card__pair { font-size: .75rem; font-weight: 700; color: var(--on-surface-var); margin-bottom: 6px; }
.man-rate-card__value { font-family: 'IBM Plex Mono', monospace; font-size: 1.25rem; font-weight: 700; color: var(--on-surface); margin-bottom: 8px; }

/* ── Element Plus table, reskinned to match the market design system ──── */
.mkt-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }
.mkt-num { font-variant-numeric: tabular-nums; }
.mkt-el-table { --el-table-border-color: var(--border); --el-table-header-bg-color: var(--surface-low); --el-table-header-text-color: var(--on-surface-var); --el-table-row-hover-bg-color: #f3f6f5; --el-table-text-color: var(--on-surface); font-family: inherit; border-top: 1px solid var(--border); }
.mkt-el-table :deep(.el-table__cell) { padding: 11px 0; }
.mkt-el-table :deep(.cell) { padding: 0 12px; font-size: .8125rem; line-height: 1.45; }
.mkt-el-table :deep(th.el-table__cell) { font-size: .6875rem; font-weight: 600; letter-spacing: .04em; }
.mkt-el-table :deep(th.el-table__cell .cell) { display: flex; align-items: center; white-space: nowrap; }
.mkt-el-table :deep(.el-table__cell:first-child .cell) { padding-left: 1.5rem; }
.mkt-el-table :deep(.el-table__cell:last-child .cell) { padding-right: 1.5rem; }
.mkt-el-table :deep(.el-table__inner-wrapper::before) { display: none; }
.mkt-el-table :deep(.el-table__body td.el-table__cell) { transition: background-color .12s ease; }
.mkt-el-table :deep(.el-table__body tr.el-table__row--striped td.el-table__cell) { background: #fafaf9; }
.mkt-el-table :deep(.el-table__body tr:hover > td.el-table__cell) { background: var(--el-table-row-hover-bg-color); }
.mkt-el-table :deep(.el-table__empty-block) { min-height: 120px; }
.mkt-el-table :deep(.el-table__empty-text) { color: var(--on-surface-var); font-size: .8125rem; }
.mkt-th-icon { width: 14px; height: 14px; margin-right: 5px; color: var(--green); opacity: .8; }

.mkt-thumb { width: 32px; height: 32px; border-radius: 9px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(0,69,50,0.08); color: var(--green); font-size: 14px; }

.man-table-bar-wrap { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; min-width: 110px; }
.man-table-bar { width: 100%; }

/* ── News rows ───────────────────────────────────────────────────────── */
.man-news-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--surface-low); }
.man-news-row:last-child { border-bottom: none; }

@media (max-width: 991.98px) {
    .man-analytics-grid { grid-template-columns: 1fr; }
    .man-aside-card { max-height: none; border-left: none; padding-left: 0; border-top: 1px solid var(--border); padding-top: 1.25rem; }
}

@media (max-width: 767.98px) {
    .mkt-section__head { padding: 0 1.25rem; }
    .man-nav { padding: 0 1.25rem; }
    .man-content { padding: 0 1.25rem; }
}
</style>
