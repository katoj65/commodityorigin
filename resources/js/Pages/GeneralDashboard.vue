<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ExchangeRates from '@/Components/ExchangeRates.vue';
import Calendar from '@/Components/Calendar.vue';
import Task from '@/Components/Task.vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement,
    LineElement, Filler, Tooltip, Legend,
} from 'chart.js';
import {
    CoffeeCup, PriceTag, Opportunity, TrendCharts, Refresh,
    ArrowRight, Flag, MapLocation, Ship, Position, Tickets, Histogram,
    MagicStick, Notebook, Sunny, Cloudy, Pouring, UserFilled, Timer,
} from '@element-plus/icons-vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const props = defineProps({
    exchangeRates: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
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
   1. MARKET PULSE — global trends + currency exchange
   ══════════════════════════════════════════════════════════════════════ */
const marketKpis = [
    { label: 'Arabica (KC)', value: '$5.10', unit: '/lb', change: '+2.4%', up: true, icon: CoffeeCup },
    { label: 'Robusta (RM)', value: '$2,340', unit: '/mt', change: '+1.1%', up: true, icon: CoffeeCup },
    { label: 'Coffee C Price', value: '186.40', unit: '¢/lb', change: '-0.6%', up: false, icon: PriceTag },
    { label: 'Market Sentiment', value: 'Bullish', unit: '', change: '72/100', up: true, icon: Opportunity },
];

const marketChartData = {
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [
        {
            label: 'Arabica ($/lb)',
            data: [4.92, 4.88, 4.95, 5.01, 4.97, 5.05, 5.10],
            borderColor: '#004532',
            backgroundColor: 'rgba(0,69,50,0.08)',
            tension: 0.35,
            fill: true,
            pointRadius: 2,
        },
        {
            label: 'Robusta ($/mt ÷1000)',
            data: [2.2, 2.18, 2.25, 2.28, 2.22, 2.35, 2.34],
            borderColor: '#c8862a',
            backgroundColor: 'rgba(200,134,42,0.08)',
            tension: 0.35,
            fill: true,
            pointRadius: 2,
        },
    ],
};

/* ══════════════════════════════════════════════════════════════════════
   2. MARKET OPPORTUNITIES
   ══════════════════════════════════════════════════════════════════════ */
const opportunities = [
    { title: 'Buyer seeking Grade AA Arabica', score: 94, country: 'Germany', revenue: '$62,000', action: 'Send sample offer' },
    { title: 'Organic coffee demand increasing', score: 88, country: 'Netherlands', revenue: '$41,500', action: 'List organic lots' },
    { title: 'Coffee shortage expected — Robusta', score: 81, country: 'Vietnam Corridor', revenue: '$118,000', action: 'Secure forward contract' },
];

const scoreTone = (score) => (score >= 90 ? 'cp-ring--high' : score >= 75 ? 'cp-ring--mid' : 'cp-ring--low');

/* ══════════════════════════════════════════════════════════════════════
   7. DECISION CENTER — AI-recommended actions
   ══════════════════════════════════════════════════════════════════════ */
const decisions = [
    { title: 'Lock in Arabica forward pricing', rationale: 'Prices are up 2.4% overnight with bullish sentiment — lock in now before further upside.', confidence: 91, impact: 'High', action: 'Lock Price' },
    { title: 'Send sample offer to German roaster', rationale: 'Matches your available Grade AA Arabica inventory with a $62,000 revenue opportunity.', confidence: 88, impact: 'High', action: 'Send Offer' },
    { title: 'Re-route Santos-bound shipment', rationale: 'Port congestion at 68% risks a multi-day delay on an active order.', confidence: 76, impact: 'Medium', action: 'Review Routing' },
];

const impactCls = (impact) => (impact === 'High' ? 'cp-badge--red' : impact === 'Medium' ? 'cp-badge--amber' : 'cp-badge--muted');

/* ══════════════════════════════════════════════════════════════════════
   6. INTELLIGENCE & NEWS
   ══════════════════════════════════════════════════════════════════════ */
const newsCategories = [
    'All', 'Coffee News', 'Government Policies', 'Trade Agreements', 'Export Restrictions',
    'Sustainability Updates', 'Climate News', 'Consumer Trends', 'New Technologies',
    'Coffee Research', 'Investment News',
];
const activeCategory = ref('All');

const newsItems = [
    { category: 'Coffee News', title: 'Arabica futures climb on tightening Q3 outlook', summary: 'Brazilian yield concerns drive sustained upward pressure.', importance: 'High', region: 'Global', time: '2h ago' },
    { category: 'Government Policies', title: 'Uganda announces new export licensing framework', summary: 'New rules improve traceability for smallholder exporters.', importance: 'Medium', region: 'Uganda', time: '5h ago' },
    { category: 'Trade Agreements', title: 'EU–Vietnam trade pact lowers coffee tariffs', summary: 'Reduced tariffs expected to boost Robusta shipments into the EU.', importance: 'Medium', region: 'EU / Vietnam', time: '1d ago' },
    { category: 'Export Restrictions', title: 'Brazil tightens export docs ahead of EUDR deadline', summary: 'Stricter geolocation checks now required before clearance.', importance: 'High', region: 'Brazil', time: '8h ago' },
    { category: 'Sustainability Updates', title: 'Major roasters commit to 100% traceable sourcing', summary: 'Coalition pledges full farm-to-cup traceability by 2027.', importance: 'Medium', region: 'Global', time: '1d ago' },
    { category: 'Climate News', title: 'Rainfall delays harvest across Minas Gerais', summary: 'Weather models point to a two-week delay to peak harvest.', importance: 'High', region: 'Brazil', time: '4h ago' },
    { category: 'Consumer Trends', title: 'Cold brew drives specialty growth in Asia', summary: 'Ready-to-drink formats now outpacing hot coffee sales.', importance: 'Low', region: 'Asia', time: '2d ago' },
    { category: 'New Technologies', title: 'Satellite yield forecasting gains adoption', summary: 'Cooperatives report sharper harvest planning accuracy.', importance: 'Medium', region: 'Global', time: '1d ago' },
    { category: 'Coffee Research', title: 'New rust-resistant Arabica variety trialed', summary: 'Strong resilience gains with no loss in cup quality.', importance: 'Medium', region: 'Colombia', time: '2d ago' },
    { category: 'Investment News', title: 'Specialty coffee fund raises $40M', summary: 'Capital earmarked for pre-harvest financing in East Africa.', importance: 'Low', region: 'East Africa', time: '1d ago' },
];

const showAllNews = ref(false);

const categoryNews = computed(() => activeCategory.value === 'All'
    ? newsItems
    : newsItems.filter((n) => n.category === activeCategory.value));

const filteredNews = computed(() => showAllNews.value ? categoryNews.value : categoryNews.value.slice(0, 3));

function selectCategory(c) {
    activeCategory.value = c;
    showAllNews.value = false;
}

const importanceCls = (level) => ({
    High: 'cp-badge--red',
    Medium: 'cp-badge--amber',
    Low: 'cp-badge--muted',
}[level] ?? 'cp-badge--muted');

const weatherForecast = [
    { region: 'Minas Gerais, Brazil', condition: 'Rain', temp: '19°C', note: 'Harvest delay risk', icon: Pouring },
    { region: 'Central Highlands, Vietnam', condition: 'Sunny', temp: '27°C', note: 'Favorable for drying', icon: Sunny },
    { region: 'Sidamo, Ethiopia', condition: 'Cloudy', temp: '21°C', note: 'Stable conditions', icon: Cloudy },
];

/* ══════════════════════════════════════════════════════════════════════
   3. COFFEE PRICES — historical trend + farmgate vs export
   ══════════════════════════════════════════════════════════════════════ */
const historicalChartData = {
    labels: ['2020', '2021', '2022', '2023', '2024', '2025', '2026'],
    datasets: [
        {
            label: 'Arabica ($/lb)',
            data: [1.15, 1.78, 2.24, 1.82, 2.41, 4.28, 5.10],
            borderColor: '#004532',
            backgroundColor: 'rgba(0,69,50,0.08)',
            tension: 0.3,
            fill: true,
            pointRadius: 2,
        },
        {
            label: 'Robusta ($/mt ÷1000)',
            data: [1.32, 1.75, 2.02, 2.51, 3.12, 4.02, 4.68],
            borderColor: '#c8862a',
            backgroundColor: 'rgba(200,134,42,0.08)',
            tension: 0.3,
            fill: true,
            pointRadius: 2,
        },
    ],
};

const priceComparison = [
    { country: 'Uganda', farmgate: '$4.10/kg', export: '$5.35/kg', delta: '+30.5%' },
    { country: 'Ethiopia', farmgate: '$4.62/kg', export: '$5.88/kg', delta: '+27.3%' },
    { country: 'Brazil', farmgate: '$5.02/kg', export: '$6.10/kg', delta: '+21.5%' },
    { country: 'Vietnam', farmgate: '$2.18/kg', export: '$2.71/kg', delta: '+24.3%' },
];

/* ══════════════════════════════════════════════════════════════════════
   4. SUPPLY & DEMAND — producing countries + demand hotspots
   ══════════════════════════════════════════════════════════════════════ */
const producingCountries = [
    { country: 'Brazil', volume: '3.2M t', share: 38 },
    { country: 'Vietnam', volume: '1.5M t', share: 17 },
    { country: 'Colombia', volume: '0.7M t', share: 8 },
    { country: 'Uganda', volume: '0.32M t', share: 4 },
];

const demandHotspots = [
    { region: 'United Arab Emirates', trend: '+14%', note: 'Specialty imports rising sharply' },
    { region: 'China', trend: '+21%', note: 'Fastest-growing consumption market' },
    { region: 'Germany', trend: '+5%', note: 'Steady organic & Fairtrade demand' },
];

const buyerRequests = [
    { buyer: 'Nordic Roasters', request: 'Grade AA Arabica, 5t', budget: '$26,500' },
    { buyer: 'Berlin Kaffee', request: 'Organic Robusta, 8t', budget: '$19,200' },
    { buyer: 'Dubai Specialty Co', request: 'Washed Arabica, 3t', budget: '$15,800' },
];

/* ══════════════════════════════════════════════════════════════════════
   5. SHIPPING & PORTS — shipping routes + port congestion
   ══════════════════════════════════════════════════════════════════════ */
const shippingRoutes = [
    { route: 'Mombasa → Rotterdam', transit: '24 days', status: 'On time' },
    { route: 'Santos → Hamburg', transit: '19 days', status: 'Delayed 2d' },
    { route: 'Ho Chi Minh → Dubai', transit: '11 days', status: 'On time' },
];

const portCongestion = [
    { port: 'Mombasa', congestion: 42 },
    { port: 'Santos', congestion: 68 },
    { port: 'Rotterdam', congestion: 35 },
    { port: 'Jebel Ali', congestion: 28 },
];

const transitTimeStats = [
    { label: 'Average Shipping Days', value: '18.3 days' },
    { label: 'Fastest Route', value: 'Ho Chi Minh → Dubai (11d)' },
    { label: 'Estimated Arrival', value: 'Jul 29, 2026' },
    { label: 'On-Time Performance', value: '82%' },
];

/* ══════════════════════════════════════════════════════════════════════
   1b. MARKET PULSE — orders & tasks
   ══════════════════════════════════════════════════════════════════════ */
const recentOrders = [
    { id: 'ORD-2291', buyer: 'Nordic Roasters', amount: '$12,400', status: 'Processing' },
    { id: 'ORD-2288', buyer: 'Berlin Kaffee', amount: '$8,150', status: 'Shipped' },
    { id: 'ORD-2285', buyer: 'Dubai Specialty Co', amount: '$21,900', status: 'Completed' },
];

const orderStatusCls = (s) => ({
    Processing: 'cp-badge--amber',
    Shipped: 'cp-badge--blue',
    Completed: 'cp-badge--green',
}[s] ?? 'cp-badge--muted');


</script>

<template>
    <AppLayout title="Coffee Intelligence Center" full-width flush :show-banner="false">
        <Head title="Coffee Intelligence Center" />

        <div class="cp-page">

            <!-- ══════════════════════════════════════════════════════════
                 1. MARKET PULSE
                 ══════════════════════════════════════════════════════════ -->
            <section class="cp-section cp-section--tight">
                <div class="container-fluid px-3 px-lg-4 pt-3">
                    <div class="cp-section__head">
                        <div>
                            <div class="cp-kicker">Live · Updated 2 min ago</div>
                            <h1 class="cp-title mb-0">Market Pulse</h1>
                        </div>
                        <button class="btn cp-btn-ghost btn-sm"><el-icon><Refresh /></el-icon> Refresh</button>
                    </div>

                    <div class="row g-2 mb-3">
                        <div v-for="kpi in marketKpis" :key="kpi.label" class="col-6 col-md-3">
                            <div class="cp-kpi h-100">
                                <div class="cp-kpi__icon"><el-icon><component :is="kpi.icon" /></el-icon></div>
                                <span class="cp-kpi__label">{{ kpi.label }}</span>
                                <div class="cp-kpi__value">{{ kpi.value }}<small v-if="kpi.unit">{{ ' ' + kpi.unit }}</small></div>
                                <div class="cp-kpi__change" :class="kpi.up ? 'cp-up' : 'cp-down'">{{ kpi.change }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-4">
                            <div class="cp-card p-0 overflow-hidden h-100">
                                <div class="cp-card-title px-3 pt-3"><el-icon class="cp-card-icon"><Tickets /></el-icon> Recent Orders</div>
                                <div class="table-responsive">
                                    <table class="table cp-table mb-0">
                                        <thead><tr><th>Order</th><th>Buyer</th><th>Amount</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <tr v-for="o in recentOrders" :key="o.id">
                                                <td class="cp-item-name">{{ o.id }}</td>
                                                <td>{{ o.buyer }}</td>
                                                <td class="fw-semibold">{{ o.amount }}</td>
                                                <td><span class="cp-badge" :class="orderStatusCls(o.status)">{{ o.status }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <Task :tasks="props.tasks" title="Tasks" />
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <Calendar :events="props.calendarEvents" title="My Calendar" />
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-7">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><TrendCharts /></el-icon> 7-Day Price Trend</div>
                                <div style="height:200px;"><Line :data="marketChartData" :options="chartOptions" /></div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <div class="cp-card h-100">
                                <ExchangeRates :rates="props.exchangeRates" title="Currency Exchange" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 2. MARKET OPPORTUNITIES
                 ══════════════════════════════════════════════════════════ -->
            <section class="cp-section">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="cp-section__head">
                        <div>
                            <div class="cp-kicker">AI-Generated</div>
                            <h2 class="cp-title mb-0">Market Opportunities</h2>
                        </div>
                        <a href="#" class="cp-link" @click.prevent>View all <el-icon><ArrowRight /></el-icon></a>
                    </div>

                    <div class="row g-3">
                        <div v-for="opp in opportunities" :key="opp.title" class="col-12 col-md-4">
                            <div class="cp-card h-100 cp-opp-card">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div class="cp-ring" :class="scoreTone(opp.score)">{{ opp.score }}</div>
                                    <el-icon class="cp-opp-icon"><Opportunity /></el-icon>
                                </div>
                                <div class="cp-opp-title">{{ opp.title }}</div>
                                <div class="cp-opp-meta"><el-icon><Flag /></el-icon> {{ opp.country }}</div>
                                <div class="cp-opp-revenue">{{ opp.revenue }} <span>est. revenue</span></div>
                                <button class="btn cp-btn-primary btn-sm w-100 mt-2">{{ opp.action }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 3. COFFEE PRICES
                 ══════════════════════════════════════════════════════════ -->
            <section class="cp-section">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="cp-section__head">
                        <div>
                            <div class="cp-kicker">Analytics</div>
                            <h2 class="cp-title mb-0">Coffee Prices</h2>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Histogram /></el-icon> Historical Prices (7-Year)</div>
                                <div style="height:220px;"><Line :data="historicalChartData" :options="chartOptions" /></div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="cp-card p-0 overflow-hidden h-100">
                                <div class="cp-card-title px-3 pt-3"><el-icon class="cp-card-icon"><PriceTag /></el-icon> Farmgate vs Export</div>
                                <div class="table-responsive">
                                    <table class="table cp-table mb-0">
                                        <thead>
                                            <tr><th>Country</th><th>Farmgate</th><th>Export</th><th>Margin</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="c in priceComparison" :key="c.country">
                                                <td class="cp-item-name">{{ c.country }}</td>
                                                <td>{{ c.farmgate }}</td>
                                                <td>{{ c.export }}</td>
                                                <td class="cp-up">{{ c.delta }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 4. SUPPLY & DEMAND
                 ══════════════════════════════════════════════════════════ -->
            <section class="cp-section">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="cp-section__head">
                        <div>
                            <div class="cp-kicker">Global Intelligence</div>
                            <h2 class="cp-title mb-0">Supply &amp; Demand</h2>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><MapLocation /></el-icon> Producing Countries</div>
                                <div v-for="p in producingCountries" :key="p.country" class="cp-share-row">
                                    <span class="cp-share-label">{{ p.country }}</span>
                                    <div class="cp-bar-track"><div class="cp-bar-fill" :style="{ width: p.share + '%' }"></div></div>
                                    <span class="cp-share-pct">{{ p.volume }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Opportunity /></el-icon> Demand Hotspots</div>
                                <div v-for="d in demandHotspots" :key="d.region" class="cp-hotspot-row">
                                    <div>
                                        <div class="cp-item-name">{{ d.region }}</div>
                                        <div class="cp-muted" style="font-size:.7rem;">{{ d.note }}</div>
                                    </div>
                                    <span class="cp-up cp-hotspot-trend">{{ d.trend }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><UserFilled /></el-icon> Buyer Requests</div>
                                <div v-for="b in buyerRequests" :key="b.buyer" class="cp-hotspot-row">
                                    <div>
                                        <div class="cp-item-name">{{ b.buyer }}</div>
                                        <div class="cp-muted" style="font-size:.7rem;">{{ b.request }}</div>
                                    </div>
                                    <span class="cp-up cp-hotspot-trend">{{ b.budget }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 5. SHIPPING & PORTS
                 ══════════════════════════════════════════════════════════ -->
            <section class="cp-section">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="cp-section__head">
                        <div>
                            <div class="cp-kicker">Global Logistics</div>
                            <h2 class="cp-title mb-0">Shipping &amp; Ports</h2>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Ship /></el-icon> Shipping Routes</div>
                                <div v-for="r in shippingRoutes" :key="r.route" class="cp-hotspot-row">
                                    <div>
                                        <div class="cp-item-name">{{ r.route }}</div>
                                        <div class="cp-muted" style="font-size:.7rem;">Transit: {{ r.transit }}</div>
                                    </div>
                                    <span class="cp-badge" :class="r.status === 'On time' ? 'cp-badge--green' : 'cp-badge--amber'">{{ r.status }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Position /></el-icon> Port Congestion</div>
                                <div v-for="p in portCongestion" :key="p.port" class="cp-share-row">
                                    <span class="cp-share-label">{{ p.port }}</span>
                                    <div class="cp-bar-track"><div class="cp-bar-fill" :class="p.congestion > 55 ? 'cp-bar-fill--warn' : ''" :style="{ width: p.congestion + '%' }"></div></div>
                                    <span class="cp-share-pct">{{ p.congestion }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Timer /></el-icon> Transit Time</div>
                                <div v-for="s in transitTimeStats" :key="s.label" class="cp-hotspot-row">
                                    <span class="cp-item-name" style="font-weight:600;">{{ s.label }}</span>
                                    <span class="cp-item-name">{{ s.value }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 6. INTELLIGENCE & NEWS
                 ══════════════════════════════════════════════════════════ -->
            <section class="cp-section">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="cp-section__head">
                        <div>
                            <div class="cp-kicker">AI-Curated</div>
                            <h2 class="cp-title mb-0">Intelligence &amp; News</h2>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-3">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Notebook /></el-icon> Categories</div>
                                <div class="cp-cat-list">
                                    <button
                                        v-for="c in newsCategories"
                                        :key="c"
                                        class="cp-cat-row"
                                        :class="{ 'cp-cat-row--active': activeCategory === c }"
                                        @click="selectCategory(c)"
                                    >
                                        <span>{{ c }}</span>
                                        <span class="cp-cat-count">{{ c === 'All' ? newsItems.length : newsItems.filter((n) => n.category === c).length }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><MagicStick /></el-icon> Latest Updates</div>
                                <div v-for="n in filteredNews" :key="n.title" class="cp-news-row">
                                    <div class="cp-news-row__top">
                                        <span class="cp-badge" :class="importanceCls(n.importance)">{{ n.importance }}</span>
                                        <span class="cp-news-category">{{ n.category }}</span>
                                        <span class="cp-news-time ms-auto">{{ n.time }}</span>
                                    </div>
                                    <div class="cp-news-title">{{ n.title }}</div>
                                    <p class="cp-news-summary">{{ n.summary }}</p>
                                    <span class="cp-news-region"><el-icon><Flag /></el-icon> {{ n.region }}</span>
                                </div>
                                <button
                                    v-if="categoryNews.length > 3"
                                    type="button"
                                    class="cp-view-more"
                                    @click="showAllNews = !showAllNews"
                                >{{ showAllNews ? 'View Less' : 'View More' }}</button>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cp-card h-100">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Sunny /></el-icon> Weather Forecast</div>
                                <div v-for="w in weatherForecast" :key="w.region" class="cp-weather-row">
                                    <div class="cp-weather-icon"><el-icon><component :is="w.icon" /></el-icon></div>
                                    <div class="flex-fill">
                                        <div class="cp-item-name">{{ w.region }}</div>
                                        <div class="cp-muted" style="font-size:.7rem;">{{ w.condition }} · {{ w.note }}</div>
                                    </div>
                                    <span class="cp-weather-temp">{{ w.temp }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 7. DECISION CENTER
                 ══════════════════════════════════════════════════════════ -->
            <section class="cp-section cp-section--last">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="cp-section__head">
                        <div>
                            <div class="cp-kicker">Powered by AI</div>
                            <h2 class="cp-title mb-0">Decision Center</h2>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div v-for="d in decisions" :key="d.title" class="col-12 col-md-4">
                            <div class="cp-card h-100 cp-opp-card">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div class="cp-ring" :class="scoreTone(d.confidence)">{{ d.confidence }}</div>
                                    <span class="cp-badge" :class="impactCls(d.impact)">{{ d.impact }} Impact</span>
                                </div>
                                <div class="cp-opp-title">{{ d.title }}</div>
                                <p class="cp-news-summary">{{ d.rationale }}</p>
                                <button class="btn cp-btn-primary btn-sm w-100 mt-2">{{ d.action }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </AppLayout>
</template>

<style scoped>
.cp-page {
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
.cp-muted { color: var(--on-surface-var); }
.cp-up   { color: #166534; font-weight: 700; }
.cp-down { color: #991b1b; font-weight: 700; }
.cp-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Sections ─────────────────────────────────────────────────────────── */
.cp-section { padding: 1.5rem 0; border-bottom: 1px solid var(--border); }
.cp-section--tight { padding-top: 0; }
.cp-section--last { border-bottom: none; padding-bottom: 3rem; }
.cp-section__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: .875rem; }
.cp-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.cp-title  { font-size: 1.1875rem; font-weight: 800; letter-spacing: -.02em; }
.cp-link   { font-size: .8125rem; font-weight: 700; color: var(--green); text-decoration: none; display: inline-flex; align-items: center; gap: 3px; }
.cp-link:hover { color: var(--green-dark); }

/* ── Buttons ──────────────────────────────────────────────────────────── */
.cp-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.cp-btn-primary:hover { background: var(--green-dark); }
.cp-btn-ghost { background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }

/* ── KPI tiles ────────────────────────────────────────────────────────── */
.cp-kpi { background: #fff; border: 1px solid var(--border); border-radius: 10px; padding: .875rem; }
.cp-kpi__icon { width: 26px; height: 26px; border-radius: 7px; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 13px; margin-bottom: 6px; }
.cp-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.cp-kpi__value { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); line-height: 1.2; margin: 4px 0 2px; }
.cp-kpi__value small { font-size: .625rem; font-weight: 600; color: var(--on-surface-var); }
.cp-kpi__change { font-size: .6875rem; font-weight: 700; }

/* ── Card ─────────────────────────────────────────────────────────────── */
.cp-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.cp-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.cp-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Share rows ───────────────────────────────────────────────────────── */
.cp-share-row { display: flex; align-items: center; gap: 8px; padding: 5px 0; }
.cp-share-label { font-size: .75rem; font-weight: 600; width: 78px; flex-shrink: 0; color: var(--on-surface); }
.cp-share-pct   { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); width: 52px; text-align: right; flex-shrink: 0; }
.cp-bar-track { flex: 1; height: 6px; background: var(--surface-low); border-radius: 999px; overflow: hidden; }
.cp-bar-fill  { height: 100%; background: var(--green); border-radius: 999px; }
.cp-bar-fill--warn { background: #d97706; }

/* ── Opportunity cards ────────────────────────────────────────────────── */
.cp-opp-card { display: flex; flex-direction: column; }
.cp-opp-icon { color: var(--gold); font-size: 16px; }
.cp-ring { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8125rem; font-weight: 800; flex-shrink: 0; }
.cp-ring--high { background: #dcfce7; color: #166534; border: 2px solid #86efac; }
.cp-ring--mid  { background: #fef3c7; color: #92400e; border: 2px solid #fcd34d; }
.cp-ring--low  { background: #f3f4f6; color: #6b7280; border: 2px solid #d1d5db; }
.cp-opp-title { font-size: .875rem; font-weight: 700; color: var(--on-surface); margin-bottom: 6px; line-height: 1.3; }
.cp-opp-meta { display: flex; align-items: center; gap: 5px; font-size: .75rem; color: var(--on-surface-var); margin-bottom: 2px; }
.cp-opp-revenue { font-size: .9375rem; font-weight: 800; color: var(--green); margin-top: 8px; }
.cp-opp-revenue span { font-size: .625rem; font-weight: 600; color: var(--on-surface-var); text-transform: uppercase; letter-spacing: .04em; margin-left: 4px; }

/* ── Badges ───────────────────────────────────────────────────────────── */
.cp-badge { display: inline-flex; border-radius: 999px; font-size: .625rem; font-weight: 700; padding: 3px 9px; }
.cp-badge--green  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.cp-badge--amber  { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.cp-badge--blue   { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.cp-badge--red    { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.cp-badge--muted  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

/* ── Categories list ──────────────────────────────────────────────────── */
.cp-cat-list { display: flex; flex-direction: column; gap: 2px; max-height: 320px; overflow-y: auto; }
.cp-cat-row { display: flex; align-items: center; justify-content: space-between; width: 100%; text-align: left; background: none; border: none; border-radius: 6px; padding: 8px 10px; font-size: .8125rem; font-weight: 600; color: var(--on-surface-var); cursor: pointer; }
.cp-cat-row:hover { background: var(--surface-low); }
.cp-cat-row--active { background: rgba(0,69,50,0.08); color: var(--green); }
.cp-cat-count { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); border-radius: 999px; padding: 1px 8px; }
.cp-cat-row--active .cp-cat-count { background: #fff; color: var(--green); }

/* ── News feed ────────────────────────────────────────────────────────── */
.cp-news-row { padding: 10px 0; border-bottom: 1px solid var(--surface-low); }
.cp-news-row:last-child { border-bottom: none; }
.cp-news-row__top { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.cp-news-time { font-size: .6875rem; color: var(--on-surface-var); }
.cp-news-title { font-size: .875rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; line-height: 1.35; }
.cp-news-summary { font-size: .8125rem; color: var(--on-surface-var); line-height: 1.5; margin-bottom: 6px; }
.cp-news-region { display: inline-flex; align-items: center; gap: 4px; font-size: .75rem; color: var(--on-surface-var); }
.cp-news-category { font-size: .6875rem; font-weight: 600; color: var(--green); }
.cp-view-more { display: block; width: 100%; text-align: center; background: none; border: none; padding-top: 10px; font-size: .75rem; font-weight: 700; color: var(--green); cursor: pointer; }
.cp-view-more:hover { color: var(--green-dark); }

/* ── Weather ──────────────────────────────────────────────────────────── */
.cp-weather-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.cp-weather-row:last-child { border-bottom: none; }
.cp-weather-icon { width: 30px; height: 30px; border-radius: 8px; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.cp-weather-temp { font-size: .875rem; font-weight: 800; color: var(--on-surface); flex-shrink: 0; }

/* ── Table ────────────────────────────────────────────────────────────── */
.cp-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.cp-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.cp-table tbody tr:last-child td { border-bottom: none; }

/* ── Hotspot / list rows ──────────────────────────────────────────────── */
.cp-hotspot-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.cp-hotspot-row:last-child { border-bottom: none; }
.cp-hotspot-trend { font-size: .8125rem; font-weight: 800; white-space: nowrap; }
</style>
