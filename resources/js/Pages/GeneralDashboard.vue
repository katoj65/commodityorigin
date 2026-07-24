<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ExchangeRates from '@/Components/ExchangeRates.vue';
import Calendar from '@/Components/Calendar.vue';
import Task from '@/Components/Task.vue';
import Orders from '@/Components/Orders.vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement,
    LineElement, Filler, Tooltip, Legend,
} from 'chart.js';
import {
    CoffeeCup, PriceTag, Opportunity, TrendCharts, Refresh,
    ArrowRight, Flag, MapLocation, Ship, Position, Histogram,
    MagicStick, Notebook, Sunny, Cloudy, Pouring, UserFilled, Timer,
} from '@element-plus/icons-vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const props = defineProps({
    exchangeRates: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
    orders: { type: Array, default: () => [] },
    hasProfile: { type: Boolean, default: true },
    hasRole: { type: Boolean, default: true },
    roles: { type: Array, default: () => [] },
});

const page = usePage();
const firstName = computed(() => page.props.auth?.user?.first_name ?? '');

/* ── Profile completion dialog ───────────────────────────────────────── */
const profileDialogOpen = ref(!props.hasProfile);

const profileForm = useForm({
    date_of_birth: '',
    gender: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
    bio: '',
    photo: null,
});

const photoPreview = ref(null);

function onPhotoChange(event) {
    const file = event.target.files[0] ?? null;
    profileForm.photo = file;
    photoPreview.value = file ? URL.createObjectURL(file) : null;
}

function saveProfile() {
    profileForm.post(route('profile.store'), {
        preserveScroll: true,
        onSuccess: () => {
            profileDialogOpen.value = false;
            if (!props.hasRole) roleDialogOpen.value = true;
        },
    });
}

/* ── Role selection dialog ─────────────────────────────────────────────
   Opens once a profile exists but no role has been chosen yet — either
   right away (profile was already complete) or right after the profile
   dialog above closes. */
const roleDialogOpen = ref(props.hasProfile && !props.hasRole);
const selectingRole = ref(null);

function roleInitial(name) {
    return name?.charAt(0)?.toUpperCase() ?? '?';
}

function selectRole(slug) {
    if (selectingRole.value) return;

    selectingRole.value = slug;
    router.post(route('profile.role'), { role: slug }, {
        preserveScroll: true,
        onSuccess: () => { roleDialogOpen.value = false; },
        onFinish: () => { selectingRole.value = null; },
    });
}

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

</script>

<template>
    <AppLayout title="Coffee Intelligence Center" full-width flush :show-banner="false">
        <Head title="Coffee Intelligence Center" />

        <div class="cp-page">

            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="cp-page-header">
                <h1 class="cp-title mb-0">Welcome{{ firstName ? `, ${firstName}` : '' }}</h1>
                <button class="btn cp-btn-ghost btn-sm"><el-icon><Refresh /></el-icon> Refresh</button>
            </div>

            <!-- ══════════════════════════════════════════════════════════
                 1. MARKET PULSE
                 ══════════════════════════════════════════════════════════ -->
            <section class="cp-section cp-section--tight">
                <div class="container-fluid px-3 px-lg-4 pt-3">
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
                            <div class="cp-card cp-card--flat h-100">
                                <Orders :orders="props.orders" title="Orders" />
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cp-card cp-card--flat h-100">
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

        <!-- ── Complete Your Profile modal ─────────────────────────────── -->
        <el-dialog
            v-model="profileDialogOpen"
            width="520px"
            align-center
            :show-close="false"
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            class="gd-modal"
        >
            <template #header>
                <div class="gd-modal__head">
                    <div class="gd-modal__head-icon">
                        <el-icon :size="18"><UserFilled /></el-icon>
                    </div>
                    <div class="gd-modal__head-text">
                        <div class="gd-modal__eyebrow">One Last Step</div>
                        <div class="gd-modal__title">Complete Your Profile</div>
                    </div>
                </div>
            </template>

            <div class="gd-modal__body">
                <p class="gd-modal__intro">We need a few details before you can start trading on Bean Origin.</p>

                <div class="gd-photo-row">
                    <div class="gd-photo-preview">
                        <img v-if="photoPreview" :src="photoPreview" alt="Profile photo preview">
                        <el-icon v-else :size="22"><UserFilled /></el-icon>
                    </div>
                    <div class="gd-photo-field">
                        <label class="gd-field__label">Profile Photo <span class="gd-field__optional">(optional)</span></label>
                        <label class="gd-photo-upload">
                            <input type="file" accept="image/*" class="gd-photo-upload__input" @change="onPhotoChange">
                            {{ profileForm.photo ? 'Change Photo' : 'Upload Photo' }}
                        </label>
                        <span v-if="profileForm.errors.photo" class="gd-field__error">{{ profileForm.errors.photo }}</span>
                    </div>
                </div>

                <div class="gd-field-row">
                    <div class="gd-field">
                        <label class="gd-field__label">Date of Birth</label>
                        <el-date-picker v-model="profileForm.date_of_birth" type="date" value-format="YYYY-MM-DD" style="width:100%" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.date_of_birth }" />
                        <span v-if="profileForm.errors.date_of_birth" class="gd-field__error mt-3">{{ profileForm.errors.date_of_birth }}</span>
                    </div>
                    <div class="gd-field">
                        <label class="gd-field__label">Gender</label>
                        <el-select v-model="profileForm.gender" placeholder="Select" style="width:100%" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.gender }">
                            <el-option label="Male" value="male" />
                            <el-option label="Female" value="female" />
                            <el-option label="Prefer not to say" value="prefer_not_to_say" />
                        </el-select>
                        <span v-if="profileForm.errors.gender" class="gd-field__error">{{ profileForm.errors.gender }}</span>
                    </div>
                </div>

                <div class="gd-field">
                    <label class="gd-field__label">Address Line 1</label>
                    <el-input v-model="profileForm.address_line_1" placeholder="Street address" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.address_line_1 }" />
                    <span v-if="profileForm.errors.address_line_1" class="gd-field__error">{{ profileForm.errors.address_line_1 }}</span>
                </div>

                <div class="gd-field">
                    <label class="gd-field__label">Address Line 2 <span class="gd-field__optional">(optional)</span></label>
                    <el-input v-model="profileForm.address_line_2" placeholder="Apartment, suite, etc." class="gd-input" />
                </div>

                <div class="gd-field-row">
                    <div class="gd-field">
                        <label class="gd-field__label">City</label>
                        <el-input v-model="profileForm.city" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.city }" />
                        <span v-if="profileForm.errors.city" class="gd-field__error">{{ profileForm.errors.city }}</span>
                    </div>
                    <div class="gd-field">
                        <label class="gd-field__label">State</label>
                        <el-input v-model="profileForm.state" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.state }" />
                        <span v-if="profileForm.errors.state" class="gd-field__error">{{ profileForm.errors.state }}</span>
                    </div>
                </div>

                <div class="gd-field-row">
                    <div class="gd-field">
                        <label class="gd-field__label">Country</label>
                        <el-input v-model="profileForm.country" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.country }" />
                        <span v-if="profileForm.errors.country" class="gd-field__error">{{ profileForm.errors.country }}</span>
                    </div>
                    <div class="gd-field">
                        <label class="gd-field__label">Postal Code <span class="gd-field__optional">(optional)</span></label>
                        <el-input v-model="profileForm.postal_code" class="gd-input" />
                    </div>
                </div>

                <div class="gd-field">
                    <label class="gd-field__label">Bio <span class="gd-field__optional">(optional)</span></label>
                    <el-input v-model="profileForm.bio" type="textarea" :rows="3" placeholder="A short note about you or your business" class="gd-input" />
                </div>
            </div>

            <template #footer>
                <div class="gd-modal__footer">
                    <button type="button" class="gd-btn-primary" :disabled="profileForm.processing" @click="saveProfile">
                        <el-icon v-if="!profileForm.processing"><UserFilled /></el-icon>
                        {{ profileForm.processing ? 'Saving…' : 'Save Profile' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Select Your Role modal ──────────────────────────────────── -->
        <el-dialog
            v-model="roleDialogOpen"
            width="640px"
            align-center
            :show-close="false"
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            class="gd-modal"
        >
            <template #header>
                <div class="gd-modal__head">
                    <div class="gd-modal__head-icon">
                        <el-icon :size="18"><UserFilled /></el-icon>
                    </div>
                    <div class="gd-modal__head-text">
                        <div class="gd-modal__eyebrow">One Last Step</div>
                        <div class="gd-modal__title">How do you want to interact with the platform?</div>
                    </div>
                </div>
            </template>

            <div class="gd-modal__body">
                <p class="gd-modal__intro">Pick the role that fits you best. You can change this later from your profile.</p>

                <div class="gd-role-grid">
                    <button
                        v-for="role in roles"
                        :key="role.slug"
                        type="button"
                        class="gd-role-tile"
                        :disabled="!!selectingRole"
                        :class="{ 'gd-role-tile--busy': selectingRole === role.slug }"
                        @click="selectRole(role.slug)"
                    >
                        <span class="gd-role-tile__icon">{{ roleInitial(role.name) }}</span>
                        <span class="gd-role-tile__name">{{ role.name }}</span>
                        <span class="gd-role-tile__desc">{{ role.description }}</span>
                    </button>
                </div>
            </div>
        </el-dialog>
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
    --shadow-sm: 0 1px 2px rgba(15, 23, 42, .05);
    --shadow-md: 0 6px 16px rgba(15, 23, 42, .08);
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

.cp-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
}
.cp-page-header .cp-title { font-size: 1.5rem; }
.cp-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.cp-title  { font-size: 1.1875rem; font-weight: 800; letter-spacing: -.02em; }
.cp-link   { font-size: .8125rem; font-weight: 700; color: var(--green); text-decoration: none; display: inline-flex; align-items: center; gap: 3px; }
.cp-link:hover { color: var(--green-dark); }

/* ── Buttons ──────────────────────────────────────────────────────────── */
.cp-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 8px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; box-shadow: var(--shadow-sm); transition: background .15s ease, box-shadow .15s ease, transform .1s ease; }
.cp-btn-primary:hover { background: var(--green-dark); box-shadow: var(--shadow-md); }
.cp-btn-primary:active { transform: translateY(1px); }
.cp-btn-ghost { background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); border-radius: 8px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; transition: background .15s ease, border-color .15s ease; }
.cp-btn-ghost:hover { background: #fff; border-color: #d1d5db; }

/* ── KPI tiles ────────────────────────────────────────────────────────── */
.cp-kpi { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: .875rem; box-shadow: var(--shadow-sm); transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease; }
.cp-kpi:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); border-color: #d1d5db; }
.cp-kpi__icon { width: 28px; height: 28px; border-radius: 50%; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 13px; margin-bottom: 8px; }
.cp-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.cp-kpi__value { font-size: 1.1875rem; font-weight: 800; color: var(--on-surface); line-height: 1.2; margin: 4px 0 2px; letter-spacing: -.01em; }
.cp-kpi__value small { font-size: .625rem; font-weight: 600; color: var(--on-surface-var); }
.cp-kpi__change { font-size: .6875rem; font-weight: 700; }

/* ── Card ─────────────────────────────────────────────────────────────── */
.cp-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1rem; box-shadow: var(--shadow-sm); transition: box-shadow .15s ease; }
.cp-card--flat { box-shadow: none; }
.cp-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.cp-card-icon  { width: 26px; height: 26px; border-radius: 8px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

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
.cp-cat-row { display: flex; align-items: center; justify-content: space-between; width: 100%; text-align: left; background: none; border: none; border-radius: 8px; padding: 8px 10px; font-size: .8125rem; font-weight: 600; color: var(--on-surface-var); cursor: pointer; transition: background-color .12s ease, color .12s ease; }
.cp-cat-row:hover { background: var(--surface-low); }
.cp-cat-row--active { background: rgba(0,69,50,0.08); color: var(--green); }
.cp-cat-count { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); border-radius: 999px; padding: 1px 8px; }
.cp-cat-row--active .cp-cat-count { background: #fff; color: var(--green); }

/* ── News feed ────────────────────────────────────────────────────────── */
.cp-news-row { padding: 10px 6px; margin: 0 -6px; border-radius: 8px; border-bottom: 1px solid var(--surface-low); transition: background-color .12s ease; }
.cp-news-row:hover { background-color: var(--surface-low); }
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
.cp-weather-row { display: flex; align-items: center; gap: 10px; padding: 8px 6px; margin: 0 -6px; border-radius: 8px; border-bottom: 1px solid var(--surface-low); transition: background-color .12s ease; }
.cp-weather-row:hover { background-color: var(--surface-low); }
.cp-weather-row:last-child { border-bottom: none; }
.cp-weather-icon { width: 30px; height: 30px; border-radius: 8px; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.cp-weather-temp { font-size: .875rem; font-weight: 800; color: var(--on-surface); flex-shrink: 0; }

/* ── Table ────────────────────────────────────────────────────────────── */
.cp-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 9px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.cp-table tbody td { padding: 10px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.cp-table tbody tr:last-child td { border-bottom: none; }
.cp-table tbody tr { transition: background-color .12s ease; }
.cp-table tbody tr:hover td { background-color: var(--surface-low); }

/* ── Hotspot / list rows ──────────────────────────────────────────────── */
.cp-hotspot-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 6px; margin: 0 -6px; border-radius: 8px; border-bottom: 1px solid var(--surface-low); transition: background-color .12s ease; }
.cp-hotspot-row:hover { background-color: var(--surface-low); }
.cp-hotspot-row:last-child { border-bottom: none; }
.cp-hotspot-trend { font-size: .8125rem; font-weight: 800; white-space: nowrap; }

/* ── Complete Your Profile modal ──────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .cp-page's DOM
   subtree, so CSS custom properties (var(--green) etc.) defined on .cp-page
   do NOT cascade in. All colors below are literal hex values on purpose. */
:deep(.el-dialog.gd-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.gd-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.gd-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.gd-modal .el-dialog__footer) { padding: 0; }

.gd-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.gd-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.gd-modal__head-text { flex: 1; min-width: 0; }

.gd-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.gd-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.gd-modal__body {
    padding: 20px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    max-height: 65vh;
    overflow-y: auto;
}

.gd-modal__intro {
    font-size: 0.8125rem;
    color: #6b7280;
    line-height: 1.5;
    margin: 0 0 2px;
}

.gd-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.gd-photo-row {
    display: flex;
    align-items: center;
    gap: 14px;
}

.gd-photo-preview {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #f3f4f6;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.gd-photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gd-photo-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.gd-photo-upload {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    color: #374151;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 7px 14px;
    cursor: pointer;
    transition: background 0.12s, border-color 0.12s;
}

.gd-photo-upload:hover { background: #fff; border-color: #d1d5db; }

.gd-photo-upload__input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.gd-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.gd-field__label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #374151;
}

.gd-field__optional {
    font-weight: 400;
    color: #9ca3af;
}

.gd-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    margin-top: 4px;
    display: block;
}

.gd-input--error :deep(.el-input__wrapper),
.gd-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.gd-input :deep(.el-input__wrapper),
.gd-input :deep(.el-textarea__inner) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.gd-input :deep(.el-input__wrapper:hover),
.gd-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.gd-input :deep(.el-input__wrapper.is-focus),
.gd-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.gd-modal__footer {
    display: flex;
    justify-content: flex-end;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.gd-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.gd-btn-primary:hover:not(:disabled) { opacity: 0.9; }
.gd-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Role tiles ────────────────────────────────────────────────────────── */
.gd-role-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.gd-role-tile {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
    padding: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    text-align: left;
    transition: border-color 0.12s ease, background 0.12s ease, transform 0.08s ease, opacity 0.12s ease;
}

.gd-role-tile:hover:not(:disabled) {
    border-color: #004532;
    background: #f8fafc;
}

.gd-role-tile:active:not(:disabled) { transform: scale(0.98); }
.gd-role-tile:disabled { opacity: 0.5; cursor: not-allowed; }

.gd-role-tile--busy {
    opacity: 1;
    border-color: #004532;
    background: rgba(0, 69, 50, 0.05);
}

.gd-role-tile__icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 800;
}

.gd-role-tile__name {
    font-size: 0.8125rem;
    font-weight: 700;
    color: #111827;
}

.gd-role-tile__desc {
    font-size: 0.6875rem;
    color: #6b7280;
    line-height: 1.4;
}

@media (max-width: 575.98px) {
    .gd-field-row { grid-template-columns: 1fr; }
    .gd-role-grid { grid-template-columns: 1fr 1fr; }
}
</style>
