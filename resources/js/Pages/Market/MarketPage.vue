<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement,
    LineElement, Filler, Tooltip, Legend,
} from 'chart.js';
import {
    Search, MagicStick, Opportunity, TrendCharts, Coin,
    WarningFilled, Flag, MapLocation, Ship, Position, Histogram, PieChart,
    DataAnalysis, ArrowRight, CircleCheck, Box, Van, UserFilled,
    Filter, Download,
    Compass, Grid, Notebook, Timer, View,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Calendar from '@/Components/Calendar.vue';
import ExchangeRates from '@/Components/ExchangeRates.vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const props = defineProps({
    markets: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    exchangeRates: { type: Array, default: () => [] },
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 10 } } } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
    },
};

/* ══════════════════════════════════════════════════════════════════════
   Top bar — AI search + quick actions
   ══════════════════════════════════════════════════════════════════════ */
const searchQuery = ref('');
const searchPrompts = [
    'Find buyers in UAE',
    'Predict Arabica prices',
    'Compare Uganda and Brazil',
    'Best export market this month',
];

const quickActions = [
    { label: 'Find Buyers', icon: UserFilled, href: route('buy.index') },
    { label: 'Analyze Market', icon: DataAnalysis, href: route('market.analysis') },
    { label: 'Compare Countries', icon: Grid, href: route('country.compare') },
    { label: 'Export Report', icon: Download },
    { label: 'View Forecast', icon: TrendCharts, href: route('forecast.index') },
];

/* ══════════════════════════════════════════════════════════════════════
   Hero — market overview
   ══════════════════════════════════════════════════════════════════════ */
const heroKpis = [
    { label: 'Market Status', value: 'Open', sub: 'Live trading', icon: CircleCheck },
    { label: 'Coffee Pulse Index', value: '104.8', sub: '+0.9% today', icon: DataAnalysis },
    { label: 'Market Sentiment', value: 'Bullish', sub: '72 / 100', icon: Opportunity },
    { label: 'Market Trend', value: 'Upward', sub: '5-day streak', icon: TrendCharts },
];

/* ══════════════════════════════════════════════════════════════════════
   Coffee prices
   ══════════════════════════════════════════════════════════════════════ */
const priceCards = [
    { name: 'Arabica', unit: '$/lb', price: '5.10', daily: '+2.4%', weekly: '+6.1%', monthly: '+18.2%', high: '5.24', low: '4.61', up: true },
    { name: 'Robusta', unit: '$/mt', price: '2,340', daily: '+1.1%', weekly: '+3.4%', monthly: '+11.7%', high: '2,398', low: '2,102', up: true },
    { name: 'Coffee C', unit: '¢/lb', price: '186.40', daily: '-0.6%', weekly: '+2.0%', monthly: '+9.4%', high: '191.20', low: '174.80', up: false },
    { name: 'Specialty', unit: '$/lb', price: '7.85', daily: '+1.8%', weekly: '+4.2%', monthly: '+14.6%', high: '8.02', low: '7.10', up: true },
    { name: 'Organic', unit: '$/lb', price: '6.40', daily: '+0.9%', weekly: '+2.7%', monthly: '+10.3%', high: '6.58', low: '5.92', up: true },
];

const trendRange = ref('7D');

const weeklyTrend = {
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [
        { label: 'Arabica ($/lb)', data: [4.92, 4.88, 4.95, 5.01, 4.97, 5.05, 5.10], borderColor: '#004532', backgroundColor: 'rgba(0,69,50,0.08)', tension: 0.35, fill: true, pointRadius: 2 },
        { label: 'Robusta ($/mt ÷1000)', data: [2.2, 2.18, 2.25, 2.28, 2.22, 2.35, 2.34], borderColor: '#c8862a', backgroundColor: 'rgba(200,134,42,0.08)', tension: 0.35, fill: true, pointRadius: 2 },
    ],
};

const monthlyTrend = {
    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
    datasets: [
        { label: 'Arabica ($/lb)', data: [4.61, 4.74, 4.92, 5.10], borderColor: '#004532', backgroundColor: 'rgba(0,69,50,0.08)', tension: 0.35, fill: true, pointRadius: 2 },
        { label: 'Robusta ($/mt ÷1000)', data: [2.08, 2.16, 2.24, 2.34], borderColor: '#c8862a', backgroundColor: 'rgba(200,134,42,0.08)', tension: 0.35, fill: true, pointRadius: 2 },
    ],
};

const priceTrendData = computed(() => (trendRange.value === '7D' ? weeklyTrend : monthlyTrend));

/* ══════════════════════════════════════════════════════════════════════
   Live market listings — real data
   ══════════════════════════════════════════════════════════════════════ */
const resolveDemandTone = (demand) => {
    const d = (demand ?? '').toLowerCase();
    if (d === 'very high') return 'primary';
    if (d === 'high') return 'success';
    if (d === 'stable') return 'warning';
    if (d === 'low') return 'danger';
    return 'info';
};

const listings = computed(() => props.markets.map((m) => ({
    id: m.id,
    lot_code: m.lot_code,
    name: m.name || m.lot_code,
    origin: m.origin || '—',
    type: m.type,
    process: m.process,
    qualityScore: Number(m.quality_score || 0),
    quantity: Number(m.quantity || 0),
    pricePerKg: Number(m.price_per_kg || 0),
    demand: m.demand || 'Active',
    demandTone: resolveDemandTone(m.demand),
    badges: m.badges || [],
})));

const uniqueOrigins = computed(() => [...new Set(listings.value.map((l) => l.origin).filter(Boolean))]);
const uniqueTypes = computed(() => [...new Set(listings.value.map((l) => l.type).filter(Boolean))]);

const filters = reactive({ origin: '', type: '', grade: '', certification: '', market: '', maxPrice: '', availability: '', buyerRegion: '', exportRegion: '' });
const filtersOpen = ref(true);

const filteredListings = computed(() => listings.value.filter((l) => {
    if (filters.origin && l.origin !== filters.origin) return false;
    if (filters.type && l.type !== filters.type) return false;
    if (filters.maxPrice && l.pricePerKg > Number(filters.maxPrice)) return false;
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        const hay = `${l.name} ${l.origin} ${l.type}`.toLowerCase();
        if (!hay.includes(q)) return false;
    }
    return true;
}));

const clearFilters = () => Object.assign(filters, { origin: '', type: '', grade: '', certification: '', market: '', maxPrice: '', availability: '', buyerRegion: '', exportRegion: '' });

/* ══════════════════════════════════════════════════════════════════════
   Market opportunities
   ══════════════════════════════════════════════════════════════════════ */
const opportunityFilter = ref('All');
const opportunities = [
    { country: 'Germany', type: 'Grade AA Arabica', score: 94, demand: '$62,000', margin: '+18%', competition: 'Low', recommendation: 'Send a sample offer this week — buyer is actively sourcing.' },
    { country: 'Netherlands', type: 'Organic Arabica', score: 88, demand: '$41,500', margin: '+22%', competition: 'Medium', recommendation: 'List certified organic lots; demand is rising fast.' },
    { country: 'Vietnam Corridor', type: 'Robusta', score: 81, demand: '$118,000', margin: '+9%', competition: 'High', recommendation: 'Secure forward contracts before the shortage tightens pricing.' },
    { country: 'UAE', type: 'Specialty Blend', score: 76, demand: '$29,800', margin: '+15%', competition: 'Low', recommendation: 'Introduce your catalog — new distributor market opening.' },
];
const opportunityTypes = ['All', ...new Set(opportunities.map((o) => o.type))];
const filteredOpportunities = computed(() => opportunityFilter.value === 'All' ? opportunities : opportunities.filter((o) => o.type === opportunityFilter.value));
const scoreTone = (score) => (score >= 90 ? 'mkt-ring--high' : score >= 75 ? 'mkt-ring--mid' : 'mkt-ring--low');

/* ══════════════════════════════════════════════════════════════════════
   Supply & demand intelligence
   ══════════════════════════════════════════════════════════════════════ */
const supplyDemandKpis = [
    { label: 'Global Supply', value: '9.2M', unit: 'bags', icon: Box },
    { label: 'Global Demand', value: '9.6M', unit: 'bags', icon: TrendCharts },
    { label: 'Inventory', value: '7.8M', unit: 'bags', icon: Notebook },
    { label: 'Export Capacity', value: '82%', unit: 'utilised', icon: Ship },
    { label: 'Production', value: '+3.1%', unit: 'YoY', icon: MapLocation },
    { label: 'Consumption', value: '+2.3%', unit: 'YoY', icon: CircleCheck },
    { label: 'Supply Gap', value: '-400K', unit: 'bags', icon: WarningFilled },
    { label: 'Demand Growth', value: '+4.6%', unit: 'YoY', icon: Opportunity },
];

/* ══════════════════════════════════════════════════════════════════════
   Buyer demand
   ══════════════════════════════════════════════════════════════════════ */
const buyerRequests = [
    { country: 'Nordic Roasters (Sweden)', type: 'Grade AA Arabica', grade: 'AA', quantity: '5t', certification: 'Organic', targetPrice: '$5.30/kg', deadline: 'Jul 28', urgency: 'High' },
    { country: 'Berlin Kaffee (Germany)', type: 'Organic Robusta', grade: 'A', quantity: '8t', certification: 'Fairtrade', targetPrice: '$2.60/kg', deadline: 'Aug 05', urgency: 'Medium' },
    { country: 'Dubai Specialty Co (UAE)', type: 'Washed Arabica', grade: 'AA', quantity: '3t', certification: 'Rainforest Alliance', targetPrice: '$5.80/kg', deadline: 'Jul 24', urgency: 'High' },
];
const urgencyCls = (u) => ({ High: 'mkt-badge--red', Medium: 'mkt-badge--amber', Low: 'mkt-badge--muted' }[u] ?? 'mkt-badge--muted');

/* ══════════════════════════════════════════════════════════════════════
   Origin intelligence
   ══════════════════════════════════════════════════════════════════════ */
const originCountries = [
    { name: 'Brazil', production: '3.2M bags', harvest: 'May – Sep', weather: 'Rain risk (Minas Gerais)', grades: 'NY2, FC', price: '$5.02/kg', exportCapacity: '86%', demand: 'High', inventory: '2.1M bags', buyers: 'US, EU, Japan' },
    { name: 'Vietnam', production: '1.5M bags', harvest: 'Oct – Jan', weather: 'Favorable', grades: 'Robusta G1', price: '$2.71/kg', exportCapacity: '91%', demand: 'Very High', inventory: '0.9M bags', buyers: 'EU, Middle East' },
    { name: 'Colombia', production: '0.7M bags', harvest: 'Apr – Jun, Oct – Dec', weather: 'Stable', grades: 'Supremo, Excelso', price: '$5.95/kg', exportCapacity: '78%', demand: 'High', inventory: '0.4M bags', buyers: 'US, EU' },
    { name: 'Ethiopia', production: '0.45M bags', harvest: 'Oct – Jan', weather: 'Stable', grades: 'Yirgacheffe G1', price: '$5.88/kg', exportCapacity: '73%', demand: 'High', inventory: '0.3M bags', buyers: 'EU, Asia' },
    { name: 'Uganda', production: '0.32M bags', harvest: 'Apr – Jun', weather: 'Favorable for drying', grades: 'Bugisu AA', price: '$5.35/kg', exportCapacity: '69%', demand: 'Medium', inventory: '0.2M bags', buyers: 'EU, UAE' },
];
const selectedOrigin = ref(originCountries[0]);

/* ══════════════════════════════════════════════════════════════════════
   Trade intelligence
   ══════════════════════════════════════════════════════════════════════ */
const topExporters = [
    { country: 'Brazil', volume: '38%', trend: '+3.1%' },
    { country: 'Vietnam', volume: '17%', trend: '+5.4%' },
    { country: 'Colombia', volume: '8%', trend: '+1.2%' },
];
const topImporters = [
    { country: 'United States', volume: '22%', trend: '+2.0%' },
    { country: 'Germany', volume: '14%', trend: '+1.6%' },
    { country: 'Japan', volume: '9%', trend: '+0.8%' },
];
const fastestGrowingMarkets = ['China (+21%)', 'UAE (+14%)', 'South Korea (+9%)'];
const tradeSignals = [
    { label: 'EU–Vietnam Tariff Cut', note: 'Lower Robusta import tariffs boost EU-bound shipments.' },
    { label: 'USD Strength', note: 'A stronger dollar is pressuring origin-currency farmgate prices.' },
    { label: 'EUDR Deadline', note: 'Traceability requirements tightening for EU-bound exporters.' },
];

/* ══════════════════════════════════════════════════════════════════════
   Logistics intelligence
   ══════════════════════════════════════════════════════════════════════ */
const logisticsKpis = [
    { label: 'Freight Cost (40ft)', value: '$3,140', change: '-2.1%', up: false, icon: Ship },
    { label: 'Avg Transit Time', value: '21.4 days', change: '+0.6d', up: false, icon: Timer },
    { label: 'Container Availability', value: '74%', change: '+3.0%', up: true, icon: Box },
];
const shippingRoutes = [
    { route: 'Mombasa → Rotterdam', transit: '24 days', status: 'On time' },
    { route: 'Santos → Hamburg', transit: '19 days', status: 'Delayed 2d' },
    { route: 'Ho Chi Minh → Dubai', transit: '11 days', status: 'On time' },
];
const portCongestion = [
    { port: 'Mombasa', congestion: 42 },
    { port: 'Santos', congestion: 68 },
    { port: 'Rotterdam', congestion: 35 },
];
const logisticsAlert = 'Santos port congestion above 65% — expect 2–3 day delays on new bookings.';

/* ══════════════════════════════════════════════════════════════════════
   AI market intelligence
   ══════════════════════════════════════════════════════════════════════ */
const aiInsights = [
    { category: 'Market Summary', text: 'Arabica prices extended gains for a fifth session on tightening Brazilian supply and steady EU demand.', confidence: 91 },
    { category: 'Price Analysis', text: 'Coffee C is consolidating near 186¢/lb; a break above 191¢ would confirm the uptrend.', confidence: 84 },
    { category: 'Supply Analysis', text: 'Global supply is trailing demand by roughly 400K bags this season, the widest gap in three years.', confidence: 88 },
    { category: 'Weather Impact', text: 'Sustained rainfall in Minas Gerais risks delaying peak harvest by up to two weeks.', confidence: 79 },
    { category: 'Trade Risk', text: 'EUDR compliance deadlines may disrupt shipments from exporters without completed geolocation data.', confidence: 86 },
    { category: 'Investment Opportunity', text: 'Specialty-grade Arabica from Uganda is undervalued relative to comparable East African origins.', confidence: 82 },
];
const aiConfidenceTone = (c) => (c >= 88 ? 'mkt-badge--green' : c >= 78 ? 'mkt-badge--amber' : 'mkt-badge--muted');

/* ══════════════════════════════════════════════════════════════════════
   Market forecast
   ══════════════════════════════════════════════════════════════════════ */
const forecastHorizons = [
    { horizon: '7-Day', metric: 'Arabica +1.8%', confidence: 87 },
    { horizon: '30-Day', metric: 'Arabica +4.5%', confidence: 74 },
    { horizon: '90-Day', metric: 'Arabica +9.2%', confidence: 61 },
];
const forecastSignals = [
    { label: 'Harvest Forecast', value: 'Delayed 2 weeks (Brazil)' },
    { label: 'Supply Forecast', value: 'Tightening through Q3' },
    { label: 'Demand Forecast', value: 'Steady growth, +4.6% YoY' },
    { label: 'Export Forecast', value: 'Vietnam capacity easing' },
    { label: 'Weather Forecast', value: 'Elevated rainfall risk' },
];
</script>

<template>
    <AppLayout title="Coffee Market" full-width flush :show-banner="false">
        <Head title="Coffee Market" />

        <div class="mkt-page">

            <!-- ══════════════════════════════════════════════════════════
                 Sticky top bar — AI search + quick actions
                 ══════════════════════════════════════════════════════════ -->
            <div class="mkt-topbar pt-2 pb-2">
                <div class="container-fluid px-3 px-lg-4 py-2">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
                        <div class="mkt-search-wrap flex-grow-1">
                            <el-icon class="mkt-search-icon"><Search /></el-icon>
                            <input v-model="searchQuery" class="mkt-search-input" placeholder="Ask Coffee Pulse AI…">
                            <el-icon class="mkt-search-ai"><MagicStick /></el-icon>
                        </div>
                        <div class="mkt-quick-actions">
                            <template v-for="qa in quickActions" :key="qa.label">
                                <Link v-if="qa.href" :href="qa.href" class="mkt-qa-btn">
                                    <el-icon><component :is="qa.icon" /></el-icon> {{ qa.label }}
                                </Link>
                                <button v-else type="button" class="mkt-qa-btn">
                                    <el-icon><component :is="qa.icon" /></el-icon> {{ qa.label }}
                                </button>
                            </template>
                        </div>
                        <button type="button" class="mkt-filter-toggle d-lg-none" @click="filtersOpen = !filtersOpen">
                            <el-icon><Filter /></el-icon>
                        </button>
                    </div>
                    <!-- <div class="mkt-search-prompts">
                        <button v-for="p in searchPrompts" :key="p" type="button" class="mkt-prompt-chip" @click="searchQuery = p">{{ p }}</button>
                    </div> -->
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">
                <div class="row g-3">

                    <!-- ── Main column ─────────────────────────────────────── -->
                    <div class="col-12 col-xl-9">


                        <!-- 2. Coffee prices -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Live Pricing</div>
                                    <h2 class="mkt-title">Coffee Prices</h2>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div v-for="p in priceCards" :key="p.name" class="col-6 col-md-4 col-xl">
                                    <div class="mkt-price-card h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="mkt-price-card__name">{{ p.name }}</span>
                                            <span class="mkt-badge" :class="p.up ? 'mkt-badge--green' : 'mkt-badge--red'">{{ p.daily }}</span>
                                        </div>
                                        <div class="mkt-price-card__price">{{ p.price }} <small>{{ p.unit }}</small></div>
                                        <div class="mkt-price-card__row"><span>7d</span><span :class="p.up ? 'mkt-up' : 'mkt-down'">{{ p.weekly }}</span></div>
                                        <div class="mkt-price-card__row"><span>30d</span><span :class="p.up ? 'mkt-up' : 'mkt-down'">{{ p.monthly }}</span></div>
                                        <div class="mkt-price-card__row"><span>High / Low</span><span>{{ p.high }} / {{ p.low }}</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mkt-card">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="mkt-card-title"><el-icon class="mkt-card-icon"><Histogram /></el-icon> {{ trendRange === '7D' ? '7-Day' : '30-Day' }} Price Trend</div>
                                    <div class="mkt-toggle-group">
                                        <button type="button" class="mkt-toggle-btn" :class="{ 'mkt-toggle-btn--active': trendRange === '7D' }" @click="trendRange = '7D'">Weekly</button>
                                        <button type="button" class="mkt-toggle-btn" :class="{ 'mkt-toggle-btn--active': trendRange === '30D' }" @click="trendRange = '30D'">Monthly</button>
                                    </div>
                                </div>
                                <div style="height:220px;"><Line :data="priceTrendData" :options="chartOptions" /></div>
                            </div>
                        </section>

                        <!-- 3. Live market listings (real data) -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Live Data</div>
                                    <h2 class="mkt-title">Live Market Listings</h2>
                                </div>
                                <span class="mkt-count">{{ filteredListings.length }} lot{{ filteredListings.length !== 1 ? 's' : '' }}</span>
                            </div>

                            <div class="mkt-card p-0 overflow-hidden">
                                <div class="table-responsive">
                                    <table class="table mkt-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Lot</th><th>Origin</th><th>Type</th><th>Process</th>
                                                <th>Quality</th><th>Quantity</th><th>Price</th><th>Demand</th><th></th>
                                            </tr>
                                        </thead>
                                        <tbody v-if="filteredListings.length">
                                            <tr v-for="lot in filteredListings" :key="lot.id">
                                                <td>
                                                    <div class="mkt-item-name">{{ lot.lot_code }}</div>
                                                    <div class="mkt-muted" style="font-size:.7rem;">{{ lot.name }}</div>
                                                </td>
                                                <td style="font-size:.8125rem;">{{ lot.origin }}</td>
                                                <td><span class="mkt-badge mkt-badge--muted">{{ lot.type }}</span></td>
                                                <td style="font-size:.8125rem;">{{ lot.process }}</td>
                                                <td><span class="mkt-badge mkt-badge--green">{{ lot.qualityScore.toFixed(1) }}</span></td>
                                                <td style="font-size:.8125rem;">{{ lot.quantity.toLocaleString() }} kg</td>
                                                <td class="fw-semibold" style="font-size:.8125rem;">${{ lot.pricePerKg.toFixed(2) }}/kg</td>
                                                <td><span class="mkt-badge" :class="`mkt-badge--${lot.demandTone === 'success' ? 'green' : lot.demandTone === 'danger' ? 'red' : lot.demandTone === 'warning' ? 'amber' : 'muted'}`">{{ lot.demand }}</span></td>
                                                <td class="text-end">
                                                    <Link :href="route('lot.show', lot.id)" class="mkt-icon-link" title="View lot"><el-icon><View /></el-icon></Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tbody v-else>
                                            <tr><td colspan="9" class="text-center py-4 mkt-muted">No lots match your filters.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>

                        <!-- 4. Market opportunities -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">AI-Generated</div>
                                    <h2 class="mkt-title">Market Opportunities</h2>
                                </div>
                                <select v-model="opportunityFilter" class="mkt-select">
                                    <option v-for="t in opportunityTypes" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>

                            <div class="row g-3">
                                <div v-for="opp in filteredOpportunities" :key="opp.country + opp.type" class="col-12 col-md-6 col-xl-3">
                                    <div class="mkt-card h-100 mkt-opp-card">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div class="mkt-ring" :class="scoreTone(opp.score)">{{ opp.score }}</div>
                                            <span class="mkt-badge" :class="opp.competition === 'Low' ? 'mkt-badge--green' : opp.competition === 'Medium' ? 'mkt-badge--amber' : 'mkt-badge--red'">{{ opp.competition }} Comp.</span>
                                        </div>
                                        <div class="mkt-opp-title">{{ opp.type }}</div>
                                        <div class="mkt-opp-meta"><el-icon><Flag /></el-icon> {{ opp.country }}</div>
                                        <div class="mkt-opp-row"><span>Est. Demand</span><strong>{{ opp.demand }}</strong></div>
                                        <div class="mkt-opp-row"><span>Expected Margin</span><strong class="mkt-up">{{ opp.margin }}</strong></div>
                                        <p class="mkt-opp-reco"><el-icon><MagicStick /></el-icon> {{ opp.recommendation }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 5. Supply & demand intelligence -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Global Intelligence</div>
                                    <h2 class="mkt-title">Supply &amp; Demand Intelligence</h2>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div v-for="k in supplyDemandKpis" :key="k.label" class="col-6 col-md-3">
                                    <div class="mkt-kpi h-100">
                                        <div class="mkt-kpi__icon"><el-icon><component :is="k.icon" /></el-icon></div>
                                        <span class="mkt-kpi__label">{{ k.label }}</span>
                                        <div class="mkt-kpi__value">{{ k.value }}</div>
                                        <div class="mkt-kpi__sub">{{ k.unit }}</div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 6. Buyer demand -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Live Requests</div>
                                    <h2 class="mkt-title">Buyer Demand</h2>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div v-for="b in buyerRequests" :key="b.country" class="col-12 col-md-6 col-xl-4">
                                    <div class="mkt-card h-100">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="mkt-badge" :class="urgencyCls(b.urgency)">{{ b.urgency }} Urgency</span>
                                            <span class="mkt-muted" style="font-size:.6875rem;">Due {{ b.deadline }}</span>
                                        </div>
                                        <div class="mkt-item-name" style="font-size:.9375rem;">{{ b.country }}</div>
                                        <div class="mkt-muted mb-2" style="font-size:.75rem;">{{ b.type }} · Grade {{ b.grade }}</div>
                                        <div class="mkt-opp-row"><span>Quantity</span><strong>{{ b.quantity }}</strong></div>
                                        <div class="mkt-opp-row"><span>Certification</span><strong>{{ b.certification }}</strong></div>
                                        <div class="mkt-opp-row"><span>Target Price</span><strong class="mkt-up">{{ b.targetPrice }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 7. Origin intelligence -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Producing Countries</div>
                                    <h2 class="mkt-title">Coffee Origin Intelligence</h2>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="mkt-card h-100 p-2">
                                        <button
                                            v-for="c in originCountries"
                                            :key="c.name"
                                            type="button"
                                            class="mkt-origin-row"
                                            :class="{ 'mkt-origin-row--active': selectedOrigin.name === c.name }"
                                            @click="selectedOrigin = c"
                                        >
                                            <el-icon><Compass /></el-icon>
                                            <span>{{ c.name }}</span>
                                            <el-icon class="ms-auto"><ArrowRight /></el-icon>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12 col-md-8">
                                    <div class="mkt-card h-100">
                                        <div class="mkt-card-title mb-3"><el-icon class="mkt-card-icon"><MapLocation /></el-icon> {{ selectedOrigin.name }}</div>
                                        <div class="row g-2">
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Production</span><strong>{{ selectedOrigin.production }}</strong></div></div>
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Harvest Window</span><strong>{{ selectedOrigin.harvest }}</strong></div></div>
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Weather</span><strong>{{ selectedOrigin.weather }}</strong></div></div>
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Grades</span><strong>{{ selectedOrigin.grades }}</strong></div></div>
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Current Price</span><strong>{{ selectedOrigin.price }}</strong></div></div>
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Export Capacity</span><strong>{{ selectedOrigin.exportCapacity }}</strong></div></div>
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Demand</span><strong>{{ selectedOrigin.demand }}</strong></div></div>
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Inventory</span><strong>{{ selectedOrigin.inventory }}</strong></div></div>
                                            <div class="col-6 col-lg-4"><div class="mkt-spec"><span>Major Buyers</span><strong>{{ selectedOrigin.buyers }}</strong></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 8. Trade intelligence -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Global Trade</div>
                                    <h2 class="mkt-title">Trade Intelligence</h2>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="mkt-card h-100">
                                        <div class="mkt-card-title mb-2"><el-icon class="mkt-card-icon"><Van /></el-icon> Top Exporters</div>
                                        <div v-for="e in topExporters" :key="e.country" class="mkt-opp-row"><span>{{ e.country }}</span><strong class="mkt-up">{{ e.volume }}</strong></div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="mkt-card h-100">
                                        <div class="mkt-card-title mb-2"><el-icon class="mkt-card-icon"><Ship /></el-icon> Top Importers</div>
                                        <div v-for="i in topImporters" :key="i.country" class="mkt-opp-row"><span>{{ i.country }}</span><strong class="mkt-up">{{ i.volume }}</strong></div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="mkt-card h-100">
                                        <div class="mkt-card-title mb-2"><el-icon class="mkt-card-icon"><TrendCharts /></el-icon> Fastest-Growing</div>
                                        <div v-for="m in fastestGrowingMarkets" :key="m" class="mkt-opp-row"><span>{{ m }}</span></div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="mkt-card h-100">
                                        <div class="mkt-card-title mb-2"><el-icon class="mkt-card-icon"><Coin /></el-icon> Trade Signals</div>
                                        <div v-for="s in tradeSignals" :key="s.label" class="mb-2">
                                            <div class="mkt-item-name" style="font-size:.75rem;">{{ s.label }}</div>
                                            <div class="mkt-muted" style="font-size:.6875rem;">{{ s.note }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 9. Logistics intelligence -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Global Logistics</div>
                                    <h2 class="mkt-title">Logistics Intelligence</h2>
                                </div>
                            </div>

                            <div class="mkt-alert mb-3"><el-icon><WarningFilled /></el-icon> {{ logisticsAlert }}</div>

                            <div class="row g-2 mb-3">
                                <div v-for="k in logisticsKpis" :key="k.label" class="col-6 col-md-4">
                                    <div class="mkt-kpi h-100">
                                        <div class="mkt-kpi__icon"><el-icon><component :is="k.icon" /></el-icon></div>
                                        <span class="mkt-kpi__label">{{ k.label }}</span>
                                        <div class="mkt-kpi__value">{{ k.value }}</div>
                                        <div class="mkt-kpi__sub" :class="k.up ? 'mkt-up' : 'mkt-down'">{{ k.change }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <div class="mkt-card h-100">
                                        <div class="mkt-card-title mb-2"><el-icon class="mkt-card-icon"><Ship /></el-icon> Shipping Routes</div>
                                        <div v-for="r in shippingRoutes" :key="r.route" class="mkt-hotspot-row">
                                            <div>
                                                <div class="mkt-item-name">{{ r.route }}</div>
                                                <div class="mkt-muted" style="font-size:.7rem;">Transit: {{ r.transit }}</div>
                                            </div>
                                            <span class="mkt-badge" :class="r.status === 'On time' ? 'mkt-badge--green' : 'mkt-badge--amber'">{{ r.status }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="mkt-card h-100">
                                        <div class="mkt-card-title mb-2"><el-icon class="mkt-card-icon"><Position /></el-icon> Port Congestion</div>
                                        <div v-for="p in portCongestion" :key="p.port" class="mkt-share-row">
                                            <span class="mkt-share-label">{{ p.port }}</span>
                                            <div class="mkt-bar-track"><div class="mkt-bar-fill" :class="p.congestion > 55 ? 'mkt-bar-fill--warn' : ''" :style="{ width: p.congestion + '%' }"></div></div>
                                            <span class="mkt-share-pct">{{ p.congestion }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 10. AI market intelligence -->
                        <section class="mkt-section">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Powered by AI</div>
                                    <h2 class="mkt-title">AI Market Intelligence</h2>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div v-for="ins in aiInsights" :key="ins.category" class="col-12 col-md-6 col-xl-4">
                                    <div class="mkt-card h-100">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="mkt-item-name" style="font-size:.75rem;">{{ ins.category }}</span>
                                            <span class="mkt-badge" :class="aiConfidenceTone(ins.confidence)">{{ ins.confidence }}% confidence</span>
                                        </div>
                                        <p class="mkt-ai-text"><el-icon><MagicStick /></el-icon> {{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 11. Market forecast -->
                        <section class="mkt-section mkt-section--last">
                            <div class="mkt-section__head">
                                <div>
                                    <div class="mkt-kicker">Predictive</div>
                                    <h2 class="mkt-title">Market Forecast</h2>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div v-for="f in forecastHorizons" :key="f.horizon" class="col-12 col-md-4">
                                    <div class="mkt-card h-100 text-center">
                                        <div class="mkt-kicker mb-1">{{ f.horizon }} Forecast</div>
                                        <div class="mkt-forecast-metric">{{ f.metric }}</div>
                                        <div class="mkt-bar-track mt-2"><div class="mkt-bar-fill" :style="{ width: f.confidence + '%' }"></div></div>
                                        <div class="mkt-muted mt-1" style="font-size:.6875rem;">{{ f.confidence }}% confidence</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mkt-card">
                                <div class="mkt-card-title mb-2"><el-icon class="mkt-card-icon"><PieChart /></el-icon> Signal Forecast</div>
                                <div class="row g-2">
                                    <div v-for="s in forecastSignals" :key="s.label" class="col-12 col-md-6 col-lg-4">
                                        <div class="mkt-spec"><span>{{ s.label }}</span><strong>{{ s.value }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div><!-- /main column -->

                    <!-- ── Right sidebar — filters ─────────────────────────── -->
                    <div class="col-12 col-xl-3" :class="{ 'd-none d-xl-block': !filtersOpen }">
                        <div class="mkt-sidebar">
                            <div class="mkt-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="mkt-card-title"><el-icon class="mkt-card-icon"><Filter /></el-icon> Filters</div>
                                    <button v-if="Object.values(filters).some(Boolean)" type="button" class="mkt-clear-link" @click="clearFilters">Clear</button>
                                </div>

                                <div class="mkt-filter-group">
                                    <label>Country / Origin</label>
                                    <select v-model="filters.origin" class="mkt-select w-100">
                                        <option value="">All Origins</option>
                                        <option v-for="o in uniqueOrigins" :key="o" :value="o">{{ o }}</option>
                                    </select>
                                </div>
                                <div class="mkt-filter-group">
                                    <label>Coffee Type</label>
                                    <select v-model="filters.type" class="mkt-select w-100">
                                        <option value="">All Types</option>
                                        <option v-for="t in uniqueTypes" :key="t" :value="t">{{ t }}</option>
                                    </select>
                                </div>
                                <div class="mkt-filter-group">
                                    <label>Grade</label>
                                    <select v-model="filters.grade" class="mkt-select w-100">
                                        <option value="">Any Grade</option>
                                        <option value="AA">AA</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                    </select>
                                </div>
                                <div class="mkt-filter-group">
                                    <label>Certification</label>
                                    <select v-model="filters.certification" class="mkt-select w-100">
                                        <option value="">Any Certification</option>
                                        <option value="Organic">Organic</option>
                                        <option value="Fairtrade">Fairtrade</option>
                                        <option value="Rainforest Alliance">Rainforest Alliance</option>
                                    </select>
                                </div>
                                <div class="mkt-filter-group">
                                    <label>Market</label>
                                    <select v-model="filters.market" class="mkt-select w-100">
                                        <option value="">All Markets</option>
                                        <option value="Spot">Spot</option>
                                        <option value="Futures">Futures</option>
                                    </select>
                                </div>
                                <div class="mkt-filter-group">
                                    <label>Price Range (max $/kg)</label>
                                    <select v-model="filters.maxPrice" class="mkt-select w-100">
                                        <option value="">Any Price</option>
                                        <option value="3">Under $3</option>
                                        <option value="4">Under $4</option>
                                        <option value="5">Under $5</option>
                                        <option value="6">Under $6</option>
                                    </select>
                                </div>
                                <div class="mkt-filter-group">
                                    <label>Availability</label>
                                    <select v-model="filters.availability" class="mkt-select w-100">
                                        <option value="">Any</option>
                                        <option value="in-stock">In Stock</option>
                                        <option value="pre-order">Pre-Order</option>
                                    </select>
                                </div>
                                <div class="mkt-filter-group">
                                    <label>Buyer Region</label>
                                    <select v-model="filters.buyerRegion" class="mkt-select w-100">
                                        <option value="">Any Region</option>
                                        <option value="EU">EU</option>
                                        <option value="Middle East">Middle East</option>
                                        <option value="Asia">Asia</option>
                                        <option value="North America">North America</option>
                                    </select>
                                </div>
                                <div class="mkt-filter-group">
                                    <label>Export Region</label>
                                    <select v-model="filters.exportRegion" class="mkt-select w-100">
                                        <option value="">Any Region</option>
                                        <option value="East Africa">East Africa</option>
                                        <option value="South America">South America</option>
                                        <option value="Southeast Asia">Southeast Asia</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mkt-card">
                                <ExchangeRates :rates="exchangeRates" />
                            </div>

                            <div class="mkt-card">
                                <Calendar :events="calendarEvents" title="My Calendar" />
                            </div>
                        </div>
                    </div>

                </div><!-- /row -->
            </div><!-- /container -->

        </div>
    </AppLayout>
</template>

<style scoped>
.mkt-page {
    --green: #004532;
    --green-dark: #002e20;
    --gold: #c8862a;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}
.mkt-muted { color: var(--on-surface-var); }
.mkt-up { color: #166534; font-weight: 700; }
.mkt-down { color: #991b1b; font-weight: 700; }
.mkt-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Top bar ──────────────────────────────────────────────────────────── */
.mkt-topbar { position: sticky; top: 0; z-index: 40; background: #fff; border-bottom: 1px solid var(--border); }
.mkt-search-wrap { position: relative; display: flex; align-items: center; max-width: 480px; }
.mkt-search-icon { position: absolute; left: 12px; color: var(--on-surface-var); font-size: 14px; }
.mkt-search-ai { position: absolute; right: 12px; color: var(--gold); font-size: 14px; }
.mkt-search-input { width: 100%; height: 38px; border: 1px solid var(--border); border-radius: 10px; padding: 0 36px; font-size: .8125rem; outline: none; background: var(--surface-low); }
.mkt-search-input:focus { border-color: var(--green); background: #fff; }
.mkt-search-prompts { display: flex; flex-wrap: wrap; gap: 6px; padding: 6px 0 8px; }
.mkt-prompt-chip { font-size: .6875rem; padding: 3px 10px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface-var); cursor: pointer; white-space: nowrap; }
.mkt-prompt-chip:hover { background: #eef2f1; }

.mkt-quick-actions { display: flex; flex-wrap: wrap; gap: 6px; }
.mkt-qa-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: 7px 12px; border-radius: 8px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; text-decoration: none; }
.mkt-qa-btn:hover { background: #eef2f1; border-color: var(--green); }

.mkt-filter-toggle { width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border); background: #fff; color: var(--on-surface-var); display: inline-flex; align-items: center; justify-content: center; }

/* ── Sections ─────────────────────────────────────────────────────────── */
.mkt-section { padding: 1.25rem 0; border-bottom: 1px solid var(--border); }
.mkt-section--tight { padding-top: 0.25rem; }
.mkt-section--last { border-bottom: none; padding-bottom: 2rem; }
.mkt-section__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: .875rem; }
.mkt-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.mkt-title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
.mkt-count { font-size: .75rem; color: var(--on-surface-var); }

/* ── Hero ─────────────────────────────────────────────────────────────── */
.mkt-hero { background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 16px; padding: 1.5rem; color: #fff; }
.mkt-hero__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem; }
.mkt-hero__badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,.14); border-radius: 999px; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; padding: 5px 12px; }
.mkt-hero__updated { font-size: .6875rem; opacity: .7; }
.mkt-hero__text { font-size: .8125rem; opacity: .88; line-height: 1.6; max-width: 820px; margin-bottom: 1rem; }
.mkt-hero__highlights { display: flex; flex-wrap: wrap; gap: 10px; }
.mkt-hero__highlight { flex: 1; min-width: 240px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); border-radius: 10px; padding: 10px 12px; font-size: .75rem; }
.mkt-hero__highlight-label { display: flex; align-items: center; gap: 5px; font-weight: 700; color: #ffd88a; margin-bottom: 3px; }

/* ── KPI ──────────────────────────────────────────────────────────────── */
.mkt-kpi { background: #fff; border: 1px solid var(--border); border-radius: 10px; padding: .875rem; }
.mkt-kpi__icon { width: 26px; height: 26px; border-radius: 7px; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 13px; margin-bottom: 6px; }
.mkt-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.mkt-kpi__value { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); line-height: 1.2; margin: 4px 0 2px; }
.mkt-kpi__sub { font-size: .6875rem; color: var(--on-surface-var); font-weight: 700; }

/* ── Price cards ──────────────────────────────────────────────────────── */
.mkt-price-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: .875rem; }
.mkt-price-card__name { font-size: .8125rem; font-weight: 700; }
.mkt-price-card__price { font-size: 1.25rem; font-weight: 800; margin: 6px 0 8px; }
.mkt-price-card__price small { font-size: .625rem; font-weight: 600; color: var(--on-surface-var); }
.mkt-price-card__row { display: flex; align-items: center; justify-content: space-between; font-size: .6875rem; color: var(--on-surface-var); padding: 2px 0; }

/* ── Card ─────────────────────────────────────────────────────────────── */
.mkt-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.mkt-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.mkt-card-icon { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Table ────────────────────────────────────────────────────────────── */
.mkt-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.mkt-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.mkt-table tbody tr:last-child td { border-bottom: none; }
.mkt-table tbody tr:hover { background: var(--surface-low); }
.mkt-icon-link { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 7px; border: 1px solid var(--border); color: var(--on-surface-var); text-decoration: none; }
.mkt-icon-link:hover { background: var(--surface-low); color: var(--green); }

/* ── Badges ───────────────────────────────────────────────────────────── */
.mkt-badge { display: inline-flex; border-radius: 999px; font-size: .625rem; font-weight: 700; padding: 3px 9px; }
.mkt-badge--green { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.mkt-badge--amber { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.mkt-badge--red { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.mkt-badge--muted { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

/* ── Opportunity cards ────────────────────────────────────────────────── */
.mkt-opp-card { display: flex; flex-direction: column; }
.mkt-ring { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 800; flex-shrink: 0; }
.mkt-ring--high { background: #dcfce7; color: #166534; border: 2px solid #86efac; }
.mkt-ring--mid { background: #fef3c7; color: #92400e; border: 2px solid #fcd34d; }
.mkt-ring--low { background: #f3f4f6; color: #6b7280; border: 2px solid #d1d5db; }
.mkt-opp-title { font-size: .8125rem; font-weight: 700; margin-bottom: 4px; line-height: 1.3; }
.mkt-opp-meta { display: flex; align-items: center; gap: 5px; font-size: .75rem; color: var(--on-surface-var); margin-bottom: 8px; }
.mkt-opp-row { display: flex; align-items: center; justify-content: space-between; font-size: .75rem; color: var(--on-surface-var); padding: 3px 0; }
.mkt-opp-reco { font-size: .75rem; color: var(--on-surface-var); line-height: 1.5; display: flex; gap: 5px; margin: 8px 0 0; }
.mkt-opp-reco .el-icon { color: var(--gold); flex-shrink: 0; margin-top: 2px; }

/* ── Spec cell ────────────────────────────────────────────────────────── */
.mkt-spec { background: var(--surface-low); border-radius: 8px; padding: 8px 10px; }
.mkt-spec span { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); display: block; margin-bottom: 2px; }
.mkt-spec strong { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }

/* ── Origin selector ──────────────────────────────────────────────────── */
.mkt-origin-row { display: flex; align-items: center; gap: 8px; width: 100%; text-align: left; background: none; border: none; padding: 9px 10px; font-size: .8125rem; font-weight: 600; color: var(--on-surface-var); cursor: pointer; border-radius: 8px; }
.mkt-origin-row:hover { background: var(--surface-low); }
.mkt-origin-row--active { background: rgba(0,69,50,0.08); color: var(--green); }

/* ── Hotspot / share rows ─────────────────────────────────────────────── */
.mkt-hotspot-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.mkt-hotspot-row:last-child { border-bottom: none; }
.mkt-share-row { display: flex; align-items: center; gap: 8px; padding: 5px 0; }
.mkt-share-label { font-size: .75rem; font-weight: 600; width: 78px; flex-shrink: 0; }
.mkt-share-pct { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); width: 40px; text-align: right; flex-shrink: 0; }
.mkt-bar-track { flex: 1; height: 6px; background: var(--surface-low); border-radius: 999px; overflow: hidden; }
.mkt-bar-fill { height: 100%; background: var(--green); border-radius: 999px; }
.mkt-bar-fill--warn { background: #d97706; }

/* ── Alert ────────────────────────────────────────────────────────────── */
.mkt-alert { display: flex; align-items: center; gap: 8px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 10px; padding: 10px 14px; font-size: .8125rem; font-weight: 600; }

/* ── AI insight text ──────────────────────────────────────────────────── */
.mkt-ai-text { font-size: .8125rem; color: var(--on-surface-var); line-height: 1.55; display: flex; gap: 6px; margin: 0; }
.mkt-ai-text .el-icon { color: var(--gold); flex-shrink: 0; margin-top: 2px; }

/* ── Forecast ─────────────────────────────────────────────────────────── */
.mkt-forecast-metric { font-size: 1.0625rem; font-weight: 800; color: var(--green); }

/* ── Toggle group ─────────────────────────────────────────────────────── */
.mkt-toggle-group { display: flex; gap: 3px; background: var(--surface-low); border-radius: 8px; padding: 3px; flex-shrink: 0; }
.mkt-toggle-btn { font-size: .75rem; font-weight: 600; padding: 4px 12px; border-radius: 6px; border: none; background: transparent; color: var(--on-surface-var); cursor: pointer; transition: all .12s; }
.mkt-toggle-btn:hover { background: #eef2f1; }
.mkt-toggle-btn--active { background: #fff; color: var(--green); box-shadow: 0 1px 3px rgba(0,0,0,.08); font-weight: 700; }

/* ── Select / filters ─────────────────────────────────────────────────── */
.mkt-select { height: 32px; border: 1px solid var(--border); border-radius: 7px; padding: 0 10px; font-size: .8125rem; color: var(--on-surface); background: #fff; outline: none; cursor: pointer; }
.mkt-select:focus { border-color: var(--green); }
.mkt-filter-group { margin-bottom: 12px; }
.mkt-filter-group label { display: block; font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); margin-bottom: 4px; }
.mkt-clear-link { font-size: .75rem; font-weight: 700; color: #dc2626; background: none; border: none; cursor: pointer; }

/* ── Sidebar ──────────────────────────────────────────────────────────── */
.mkt-sidebar { position: sticky; top: 5rem; display: flex; flex-direction: column; gap: 1rem; }


/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .mkt-sidebar { position: static; }
}
</style>
