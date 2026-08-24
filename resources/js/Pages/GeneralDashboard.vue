<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
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
    CoffeeCup, PriceTag, Opportunity, TrendCharts,
    ArrowRight, Flag, MapLocation, Ship, Position,
    MagicStick, Notebook, Sunny, Cloudy, Pouring, UserFilled, Timer,
    Coffee, Star, Coin, OfficeBuilding,
    Calendar as CalendarIcon, List, FullScreen, Tickets, Compass,
} from '@element-plus/icons-vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const props = defineProps({
    exchangeRates: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
    orders: { type: Array, default: () => [] },
    markets: { type: Array, default: () => [] },
    hasProfile: { type: Boolean, default: true },
    businessTypeOptions: { type: Array, default: () => [] },
});

const scheduleTab = ref('calendar');
const pendingTaskCount = computed(() => props.tasks.filter((t) => t.status === 'pending').length);

const ordersTab = ref('requests');
const activeOrderCount = computed(() => props.orders.filter((o) => !['delivered', 'cancelled'].includes(o.status)).length);

/* Drives the Latest Updates / Weather Forecast pane layout directly via an
   inline style binding rather than a CSS media query — this exact card
   inexplicably kept computing flex-direction: column even with no matching
   rule anywhere in the page (confirmed via exhaustive devtools inspection),
   so the reactive-ref + inline-style approach sidesteps it entirely. */
const isNarrowViewport = ref(false);
function syncNarrowViewport() {
    isNarrowViewport.value = window.innerWidth < 992;
}

/* ── Profile completion dialog ───────────────────────────────────────────
   One screen, one tab bar: Personal vs Business. The active tab *is* the
   account type — switching tabs sets profileForm.profile_type directly,
   so there's no separate "confirm your type" step. Both tabs read/write
   the same profileForm, so nothing is ever asked for twice when you flip
   between them. */
const profileDialogOpen = ref(!props.hasProfile);

const profileForm = useForm({
    profile_type: 'personal',
    // Personal-only fields
    date_of_birth: '',
    gender: '',
    bio: '',
    // Business-only fields — mirrors the business_profiles table columns
    business_name: '',
    business_type: '',
    industry: 'Coffee',
    registration_number: '',
    tax_id: '',
    website: '',
    contact_email: '',
    contact_phone: '',
    employee_count: '',
    year_established: '',
    description: '',
    // Shared by both tabs — one address, not asked for twice
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
});

watch(() => profileForm.profile_type, (type) => {
    if (type === 'business') profileForm.gender = '';
});

function saveProfile() {
    profileForm.post(route('profile.store'), {
        preserveScroll: true,
        onSuccess: () => { profileDialogOpen.value = false; },
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
            borderColor: '#000000',
            backgroundColor: 'rgba(0,69,50,0.08)',
            tension: 0.35,
            fill: true,
            pointRadius: 2,
        },
        {
            label: 'Robusta ($/mt ÷1000)',
            data: [2.2, 2.18, 2.25, 2.28, 2.22, 2.35, 2.34],
            borderColor: '#D29922',
            backgroundColor: 'rgba(200,134,42,0.08)',
            tension: 0.35,
            fill: true,
            pointRadius: 2,
        },
    ],
};

const scoreTone = (score) => (score >= 90 ? 'cp-ring--high' : score >= 75 ? 'cp-ring--mid' : 'cp-ring--low');

const demandCls = (demand) => ({
    high: 'cp-badge--green', medium: 'cp-badge--amber', low: 'cp-badge--red',
}[(demand ?? '').toLowerCase()] ?? 'cp-badge--muted');

const fmtPrice = (n) => (n != null ? `$${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '—');

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
            borderColor: '#000000',
            backgroundColor: 'rgba(0,69,50,0.08)',
            tension: 0.3,
            fill: true,
            pointRadius: 2,
        },
        {
            label: 'Robusta ($/mt ÷1000)',
            data: [1.32, 1.75, 2.02, 2.51, 3.12, 4.02, 4.68],
            borderColor: '#D29922',
            backgroundColor: 'rgba(200,134,42,0.08)',
            tension: 0.3,
            fill: true,
            pointRadius: 2,
        },
    ],
};

const priceRange = ref('7d');
const priceRangeData = computed(() => (priceRange.value === '7d' ? marketChartData : historicalChartData));

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

onMounted(() => {
    syncNarrowViewport();
    window.addEventListener('resize', syncNarrowViewport);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncNarrowViewport);
});

</script>

<template>
    <DesignPreviewLayout title="Coffee Intelligence Center">
        <Head title="Coffee Intelligence Center" />
        
        <div class="cp-page">

            <div class="cp-hero">
                <div class="cp-hero__text">
                    <h1 class="cp-hero__title">Coffee Intelligence Center</h1>
                    <p class="cp-hero__subtitle">Prices, orders, and tasks from across the exchange, updated in real time and all in one place.</p>
                </div>
                <div class="cp-hero__actions">
                    <Link :href="route('calendar.index')" class="cp-hero__btn cp-hero__btn--outline">
                        <el-icon><CalendarIcon /></el-icon> View Calendar
                    </Link>
                    <Link :href="route('market.index')" class="cp-hero__btn cp-hero__btn--primary">
                        <el-icon><Compass /></el-icon> Browse Market
                    </Link>
                </div>
            </div>

            <div class="container-fluid px-0 py-0">
                <div class="row g-4">
                    <div v-for="kpi in marketKpis" :key="kpi.label" class="col-6 col-md-3">
                        <div class="cp-kpi h-100">
                            <div class="cp-kpi__icon"><el-icon><component :is="kpi.icon" /></el-icon></div>
                            <span class="cp-kpi__label">{{ kpi.label }}</span>
                            <div class="cp-kpi__value">{{ kpi.value }}<small v-if="kpi.unit">{{ ' ' + kpi.unit }}</small></div>
                            <div class="cp-kpi__change" :class="kpi.up ? 'cp-up' : 'cp-down'">{{ kpi.change }}</div>
                        </div>
                    </div>

                    <div class="col-12 cp-col-70">
                        <div class="cp-card">
                            <div class="cp-schedule__head mb-2">
                                <div class="cp-card-title mb-0"><el-icon class="cp-card-icon"><TrendCharts /></el-icon> Price Trend</div>
                                <div class="cp-schedule__tabs">
                                    <button
                                        type="button"
                                        class="cp-schedule__tab"
                                        :class="{ 'cp-schedule__tab--active': priceRange === '7d' }"
                                        @click="priceRange = '7d'"
                                    >7D</button>
                                    <button
                                        type="button"
                                        class="cp-schedule__tab"
                                        :class="{ 'cp-schedule__tab--active': priceRange === '7y' }"
                                        @click="priceRange = '7y'"
                                    >7Y</button>
                                </div>
                            </div>
                            <div style="height:220px;"><Line :data="priceRangeData" :options="chartOptions" /></div>
                        </div>
                    </div>
                    <div class="col-12 cp-col-30">
                        <div class="cp-card h-100 cp-schedule">
                            <div class="cp-schedule__head">
                                <div class="cp-card-title mb-0"><el-icon class="cp-card-icon"><Tickets /></el-icon> Orders</div>

                                <div class="cp-schedule__tabs">
                                    <button
                                        type="button"
                                        class="cp-schedule__tab"
                                        :class="{ 'cp-schedule__tab--active': ordersTab === 'orders' }"
                                        @click="ordersTab = 'orders'"
                                    >
                                        Orders
                                        <span v-if="activeOrderCount" class="cp-schedule__tab-count">{{ activeOrderCount }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="cp-schedule__tab"
                                        :class="{ 'cp-schedule__tab--active': ordersTab === 'requests' }"
                                        @click="ordersTab = 'requests'"
                                    >
                                        Requests
                                        <span v-if="buyerRequests.length" class="cp-schedule__tab-count">{{ buyerRequests.length }}</span>
                                    </button>
                                </div>

                                <Link
                                    :href="route('market.request')"
                                    class="cp-schedule__link"
                                    aria-label="Open all orders"
                                    title="Open all orders"
                                >
                                    <el-icon :size="14"><FullScreen /></el-icon>
                                </Link>
                            </div>

                            <div class="cp-schedule__body">
                                <Orders v-show="ordersTab === 'orders'" :orders="props.orders" :show-header="false" />

                                <div v-show="ordersTab === 'requests'" class="cp-requests-list">
                                    <div v-if="!buyerRequests.length" class="orders-widget__empty">
                                        <span class="orders-widget__empty-icon"><el-icon :size="18"><UserFilled /></el-icon></span>
                                        <p>No buyer requests yet.</p>
                                    </div>
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

                    <div class="col-12">
                        <div class="cp-card p-0 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between px-3 pt-3">
                                <div class="cp-card-title mb-0"><el-icon class="cp-card-icon"><Opportunity /></el-icon> Market Opportunities</div>
                                <Link :href="route('market.index')" class="cp-link">View all <el-icon><ArrowRight /></el-icon></Link>
                            </div>
                            <div class="table-responsive">
                                <table class="table cp-table mb-0">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th><el-icon class="cp-th-icon"><PriceTag /></el-icon>Lot</th>
                                            <th><el-icon class="cp-th-icon"><MapLocation /></el-icon>Origin</th>
                                            <th><el-icon class="cp-th-icon"><Coffee /></el-icon>Type</th>
                                            <th><el-icon class="cp-th-icon"><Star /></el-icon>Quality</th>
                                            <th><el-icon class="cp-th-icon"><Coin /></el-icon>Price / kg</th>
                                            <th><el-icon class="cp-th-icon"><TrendCharts /></el-icon>Demand</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="m in markets" :key="m.id">
                                            <td style="width:44px;">
                                                <div class="cp-thumb">
                                                    <img v-if="m.image" :src="`/storage/${m.image}`" :alt="m.name">
                                                    <svg v-else class="cp-thumb-icon" viewBox="0 0 24 24">
                                                        <ellipse cx="9" cy="12" rx="5" ry="7" transform="rotate(-25 9 12)" fill="#4b2e1d" />
                                                        <path d="M9 6 C7 9, 11 15, 9 18" stroke="#c9a27a" stroke-width="1.1" fill="none" transform="rotate(-25 9 12)" />
                                                        <ellipse cx="16.5" cy="14.5" rx="4" ry="5.5" transform="rotate(20 16.5 14.5)" fill="#6b4226" />
                                                        <path d="M16.5 10 C15 12.5, 18 16, 16.5 19" stroke="#d8b48f" stroke-width="1" fill="none" transform="rotate(20 16.5 14.5)" />
                                                    </svg>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="cp-item-name">{{ m.lot_code }}</div>
                                                <div class="text-muted" style="font-size:.7rem;">{{ m.name }}</div>
                                            </td>
                                            <td>{{ m.origin || '—' }}</td>
                                            <td class="text-capitalize">{{ m.type || '—' }}</td>
                                            <td>{{ m.quality_score ?? '—' }}</td>
                                            <td>{{ fmtPrice(m.price_per_kg) }}</td>
                                            <td><span class="cp-badge" :class="demandCls(m.demand)">{{ m.demand || '—' }}</span></td>
                                        </tr>
                                        <tr v-if="!markets.length"><td colspan="7" class="text-center text-muted py-4">No live listings yet.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 cp-col-60">
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
                    <div class="col-12 cp-col-40">
                        <div class="cp-card h-100 cp-schedule">
                            <div class="cp-schedule__head">
                                <div class="cp-card-title mb-0"><el-icon class="cp-card-icon"><CalendarIcon /></el-icon> Schedule</div>

                                <div class="cp-schedule__tabs">
                                    <button
                                        type="button"
                                        class="cp-schedule__tab"
                                        :class="{ 'cp-schedule__tab--active': scheduleTab === 'calendar' }"
                                        @click="scheduleTab = 'calendar'"
                                    >
                                        <el-icon :size="13"><CalendarIcon /></el-icon> Calendar
                                    </button>
                                    <button

                                    type="button"
                                        class="cp-schedule__tab"
                                        :class="{ 'cp-schedule__tab--active': scheduleTab === 'tasks' }"
                                        @click="scheduleTab = 'tasks'"
                                    >
                                        <el-icon :size="13"><List /></el-icon> Tasks
                                        <span v-if="pendingTaskCount" class="cp-schedule__tab-count">{{ pendingTaskCount }}</span>
                                    </button>
                                </div>

                                <Link
                                    :href="scheduleTab === 'calendar' ? route('calendar.index') : route('task.index')"
                                    class="cp-schedule__link"
                                    :aria-label="scheduleTab === 'calendar' ? 'Open full calendar' : 'Open all tasks'"
                                    :title="scheduleTab === 'calendar' ? 'Open full calendar' : 'Open all tasks'"
                                >
                                    <el-icon :size="14"><FullScreen /></el-icon>
                                </Link>
                            </div>

                            <div class="cp-schedule__body">
                                <Calendar v-show="scheduleTab === 'calendar'" :events="props.calendarEvents" :show-header="false" />
                                <Task v-show="scheduleTab === 'tasks'" :tasks="props.tasks" :show-header="false" />

                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="cp-card cp-tri-panel h-100" :style="{ flexDirection: isNarrowViewport ? 'column' : 'row' }">
                            <div class="cp-tri-panel__col">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><MapLocation /></el-icon> Producing Countries</div>
                                <div v-for="p in producingCountries" :key="p.country" class="cp-share-row">
                                    <span class="cp-share-label">{{ p.country }}</span>
                                    <div class="cp-bar-track"><div class="cp-bar-fill" :style="{ width: p.share + '%' }"></div></div>
                                    <span class="cp-share-pct">{{ p.volume }}</span>
                                </div>
                            </div>

                            <div class="cp-tri-panel__divider" :style="isNarrowViewport ? { width: '100%', height: '1px' } : { width: '1px', height: 'auto' }"></div>

                            <div class="cp-tri-panel__col">
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
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="cp-card h-100">
                            <ExchangeRates :rates="props.exchangeRates" title="Currency Exchange" />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="cp-card cp-tri-panel" :style="{ flexDirection: isNarrowViewport ? 'column' : 'row' }">
                            <div class="cp-tri-panel__col">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Ship /></el-icon> Shipping Routes</div>
                                <div v-for="r in shippingRoutes" :key="r.route" class="cp-hotspot-row">
                                    <div>
                                        <div class="cp-item-name">{{ r.route }}</div>
                                        <div class="cp-muted" style="font-size:.7rem;">Transit: {{ r.transit }}</div>
                                    </div>
                                    <span class="cp-badge" :class="r.status === 'On time' ? 'cp-badge--green' : 'cp-badge--amber'">{{ r.status }}</span>
                                </div>
                            </div>

                            <div class="cp-tri-panel__divider" :style="isNarrowViewport ? { width: '100%', height: '1px' } : { width: '1px', height: 'auto' }"></div>

                            <div class="cp-tri-panel__col">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Position /></el-icon> Port Congestion</div>
                                <div v-for="p in portCongestion" :key="p.port" class="cp-share-row">
                                    <span class="cp-share-label">{{ p.port }}</span>
                                    <div class="cp-bar-track"><div class="cp-bar-fill" :class="p.congestion > 55 ? 'cp-bar-fill--warn' : ''" :style="{ width: p.congestion + '%' }"></div></div>
                                    <span class="cp-share-pct">{{ p.congestion }}%</span>
                                </div>
                            </div>

                            <div class="cp-tri-panel__divider" :style="isNarrowViewport ? { width: '100%', height: '1px' } : { width: '1px', height: 'auto' }"></div>

                            <div class="cp-tri-panel__col">
                                <div class="cp-card-title mb-2"><el-icon class="cp-card-icon"><Timer /></el-icon> Transit Time</div>
                                <div v-for="s in transitTimeStats" :key="s.label" class="cp-hotspot-row">
                                    <span class="cp-item-name" style="font-weight:600;">{{ s.label }}</span>
                                    <span class="cp-item-name">{{ s.value }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

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
                    <div class="col-12 col-lg-9">
                        <div
                            class="cp-card h-100 cp-updates-weather"
                            :style="{ flexDirection: isNarrowViewport ? 'column' : 'row' }"
                        >
                            <div class="cp-updates-weather__col cp-updates-weather__col--news">
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

                            <div
                                class="cp-updates-weather__divider"
                                :style="isNarrowViewport ? { width: '100%', height: '1px' } : { width: '1px', height: 'auto' }"
                            ></div>

                            <div
                                class="cp-updates-weather__col cp-updates-weather__col--weather"
                                :style="isNarrowViewport ? { flex: '0 0 auto' } : { flex: '0 0 260px' }"
                            >
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
            </div>
        </div>

        <!-- ── Complete Your Profile modal ─────────────────────────────── -->
        <el-dialog
            v-model="profileDialogOpen"
            width="560px"
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
                        <div class="gd-modal__eyebrow">Welcome to Bean Origin</div>
                        <div class="gd-modal__title">A few details about you</div>
                    </div>
                </div>
            </template>

            <form class="gd-modal__body gd-modal__body--tabs" @submit.prevent="saveProfile">
                <el-tabs v-model="profileForm.profile_type" class="gd-tabs">
                    <!-- Personal ─────────────────────────────────────────── -->
                    <el-tab-pane name="personal">
                        <template #label>
                            <span class="gd-tab-label"><el-icon><UserFilled /></el-icon> Personal</span>
                        </template>

                        <div class="gd-tab-panel">
                            <div class="gd-field-row">
                                <div class="gd-field">
                                    <label class="gd-field__label">Date of Birth</label>
                                    <el-date-picker v-model="profileForm.date_of_birth" type="date" value-format="YYYY-MM-DD" placeholder="Pick a date" style="width:100%" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.date_of_birth }" />
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
                                <label class="gd-field__label">Street Address</label>
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
                                    <label class="gd-field__label">State / Region</label>
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
                                <el-input v-model="profileForm.bio" type="textarea" :rows="3" placeholder="A short note about you" class="gd-input" />
                                <span v-if="profileForm.errors.bio" class="gd-field__error">{{ profileForm.errors.bio }}</span>
                            </div>
                        </div>
                    </el-tab-pane>

                    <!-- Business ──────────────────────────────────────────── -->
                    <el-tab-pane name="business">
                        <template #label>
                            <span class="gd-tab-label"><el-icon><OfficeBuilding /></el-icon> Business</span>
                        </template>

                        <div class="gd-tab-panel">
                            <div class="gd-field-row">
                                <div class="gd-field">
                                    <label class="gd-field__label">Business Name</label>
                                    <el-input v-model="profileForm.business_name" placeholder="Your business name" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.business_name }" />
                                    <span v-if="profileForm.errors.business_name" class="gd-field__error">{{ profileForm.errors.business_name }}</span>
                                </div>
                                <div class="gd-field">
                                    <label class="gd-field__label">Business Type</label>
                                    <el-select v-model="profileForm.business_type" placeholder="Select" style="width:100%" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.business_type }">
                                        <el-option v-for="option in businessTypeOptions" :key="option" :label="option" :value="option" />
                                    </el-select>
                                    <span v-if="profileForm.errors.business_type" class="gd-field__error">{{ profileForm.errors.business_type }}</span>
                                </div>
                            </div>

                            <div class="gd-field-row">
                                <div class="gd-field">
                                    <label class="gd-field__label">Industry</label>
                                    <el-input v-model="profileForm.industry" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.industry }" />
                                    <span v-if="profileForm.errors.industry" class="gd-field__error">{{ profileForm.errors.industry }}</span>
                                </div>
                                <div class="gd-field">
                                    <label class="gd-field__label">Registration Number <span class="gd-field__optional">(optional)</span></label>
                                    <el-input v-model="profileForm.registration_number" class="gd-input" />
                                </div>
                            </div>

                            <div class="gd-field-row">
                                <div class="gd-field">
                                    <label class="gd-field__label">Tax ID <span class="gd-field__optional">(optional)</span></label>
                                    <el-input v-model="profileForm.tax_id" class="gd-input" />
                                </div>
                                <div class="gd-field">
                                    <label class="gd-field__label">Website <span class="gd-field__optional">(optional)</span></label>
                                    <el-input v-model="profileForm.website" placeholder="https://" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.website }" />
                                    <span v-if="profileForm.errors.website" class="gd-field__error">{{ profileForm.errors.website }}</span>
                                </div>
                            </div>

                            <div class="gd-field-row">
                                <div class="gd-field">
                                    <label class="gd-field__label">Contact Email <span class="gd-field__optional">(optional)</span></label>
                                    <el-input v-model="profileForm.contact_email" placeholder="hello@business.com" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.contact_email }" />
                                    <span v-if="profileForm.errors.contact_email" class="gd-field__error">{{ profileForm.errors.contact_email }}</span>
                                </div>
                                <div class="gd-field">
                                    <label class="gd-field__label">Contact Phone <span class="gd-field__optional">(optional)</span></label>
                                    <el-input v-model="profileForm.contact_phone" class="gd-input" />
                                </div>
                            </div>

                            <div class="gd-field-row">
                                <div class="gd-field">
                                    <label class="gd-field__label">Employees <span class="gd-field__optional">(optional)</span></label>
                                    <el-input v-model="profileForm.employee_count" type="number" min="0" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.employee_count }" />
                                    <span v-if="profileForm.errors.employee_count" class="gd-field__error">{{ profileForm.errors.employee_count }}</span>
                                </div>
                                <div class="gd-field">
                                    <label class="gd-field__label">Year Established <span class="gd-field__optional">(optional)</span></label>
                                    <el-input v-model="profileForm.year_established" type="number" min="1800" :max="new Date().getFullYear()" class="gd-input" :class="{ 'gd-input--error': profileForm.errors.year_established }" />
                                    <span v-if="profileForm.errors.year_established" class="gd-field__error">{{ profileForm.errors.year_established }}</span>
                                </div>
                            </div>

                            <div class="gd-field">
                                <label class="gd-field__label">Street Address</label>
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
                                    <label class="gd-field__label">State / Region</label>
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
                                <label class="gd-field__label">Description <span class="gd-field__optional">(optional)</span></label>
                                <el-input v-model="profileForm.description" type="textarea" :rows="3" placeholder="What does your business trade?" class="gd-input" />
                                <span v-if="profileForm.errors.description" class="gd-field__error">{{ profileForm.errors.description }}</span>
                            </div>
                        </div>
                    </el-tab-pane>
                </el-tabs>

                <div class="gd-modal__footer gd-modal__footer--tab">
                    <button type="submit" class="gd-btn-primary" :disabled="profileForm.processing">
                        <el-icon v-if="!profileForm.processing"><UserFilled /></el-icon>
                        {{ profileForm.processing ? 'Saving…' : 'Save & Start Trading' }}
                    </button>
                </div>
            </form>
        </el-dialog>
    </DesignPreviewLayout>
</template>

<style scoped>
.cp-page {
    /* UI.md theme (2026-08-24): app-wide default. See
       reference_ui_md_design_system memory for the full spec. */
    --green: #000000;
    --green-dark: #262626;
    --gold: #D29922;
    --border: #E5E7EB;
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --on-surface: #121516;
    --on-surface-var: #4B5457;
    --surface-low: #F5F6F7;
    --shadow-sm: none;
    --shadow-md: none;
    font-family: 'Inter', system-ui, sans-serif;
    background: var(--surface, #ffffff);
    color: var(--on-surface);
    min-height: 100%;
}
/* ── Hero — font, spacing, and margins copied from MarketListings.vue's
   .mktl-topbar, the app's default page-header pattern (2026-08-24). ──── */
.cp-hero { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 16px; padding-bottom: 16px; }
.cp-hero__text { display: flex; flex-direction: column; gap: 8px; max-width: 640px; }
.cp-hero__title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    color: #000000;
    margin: 0 0 6px;
}
.cp-hero__subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 400; color: #504442; margin: 0; max-width: 620px; }
.cp-hero__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

@media (max-width: 575.98px) {
    .cp-hero__title { font-size: 1.25rem; line-height: 1.6rem; }
}

.cp-hero__btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s ease, transform .15s ease, box-shadow .15s ease;
    white-space: nowrap;
}
.cp-hero__btn--primary { padding: 0 16px; background: #000000; color: #fff; }
.cp-hero__btn--primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); }
.cp-hero__btn--outline { padding: 0 16px; background: #e8e8e8; color: var(--on-surface); }
.cp-hero__btn--outline:hover { background: color-mix(in srgb, #E5E7EB 35%, transparent); }

.cp-muted { color: var(--on-surface-var); }
.cp-up   { color: #166534; font-weight: 700; }
.cp-down { color: #991b1b; font-weight: 700; }
.cp-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }
.cp-link { font-size: .8125rem; font-weight: 700; color: var(--green); text-decoration: none; display: inline-flex; align-items: center; gap: 3px; }
.cp-link:hover { color: var(--green-dark); }

/* ── Buttons ──────────────────────────────────────────────────────────── */
.cp-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 8px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; box-shadow: var(--shadow-sm); transition: background .15s ease, box-shadow .15s ease, transform .1s ease; }
.cp-btn-primary:hover { background: var(--green-dark); box-shadow: var(--shadow-md); }
.cp-btn-primary:active { transform: translateY(1px); }

/* ── KPI tiles ────────────────────────────────────────────────────────── */
.cp-kpi { background: #fff; border: 1px solid var(--card-border) !important; border-radius: var(--card-radius); padding: 1.25rem; box-shadow: var(--card-shadow); transition: box-shadow .15s ease, transform .15s ease; }
.cp-kpi:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.cp-kpi__icon { width: 28px; height: 28px; border-radius: 50%; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 13px; margin-bottom: 8px; }
.cp-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.cp-kpi__value { font-size: 1.5rem; font-weight: 700; color: var(--on-surface); line-height: 1.2; margin: 6px 0 2px; letter-spacing: -.01em; }
.cp-kpi__value small { font-size: .625rem; font-weight: 600; color: var(--on-surface-var); }
.cp-kpi__change { font-size: .6875rem; font-weight: 700; }

/* ── Card ─────────────────────────────────────────────────────────────── */
.cp-card { background: #fff; border: 1px solid var(--card-border) !important; border-radius: var(--card-radius); padding: 1rem; box-shadow: var(--card-shadow); transition: box-shadow .15s ease; }
.cp-card:hover { box-shadow: var(--shadow-md); }
.cp-card--flat { box-shadow: none; }
.cp-card--flat:hover { box-shadow: none; }
.cp-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: .875rem; font-weight: 700; color: var(--on-surface); line-height: 1.3; }
.cp-card-icon  { width: 26px; height: 26px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Schedule (combined Calendar + Tasks) card ───────────────────────────
   Single card, two views. A tab switcher replaces the two separate widget
   headers Calendar.vue/Task.vue normally render (suppressed here via their
   show-header prop) so only one title row exists for the whole card. */
.cp-schedule { display: flex; flex-direction: column; }
.cp-schedule__head { display: flex; align-items: center; gap: .75rem; margin-bottom: .875rem; flex-wrap: wrap; }
.cp-schedule__tabs { display: inline-flex; align-items: center; gap: 2px; padding: 3px; background: var(--surface-low); border-radius: 9px; margin-left: auto; }
.cp-schedule__tab {
    display: inline-flex; align-items: center; gap: 5px;
    border: none; background: none; border-radius: 7px;
    padding: 5px 11px; font-size: .75rem; font-weight: 600; color: var(--on-surface-var);
    cursor: pointer; transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
    white-space: nowrap;
}
.cp-schedule__tab:hover { color: var(--on-surface); }
.cp-schedule__tab--active { background: #fff; color: var(--green); box-shadow: 0 1px 2px rgba(15,23,42,.08); }
.cp-schedule__tab-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 16px; height: 16px; padding: 0 4px; border-radius: 999px;
    background: #fef3c7; color: #92400e; font-size: .625rem; font-weight: 800;
}
.cp-schedule__link {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 7px; flex-shrink: 0;
    color: var(--on-surface-var); text-decoration: none; transition: background-color .12s ease, color .12s ease;
}
.cp-schedule__link:hover { background: var(--surface-low); color: var(--green); }
.cp-schedule__body { flex: 1; min-height: 0; }

/* Exact-ratio row splits — Bootstrap's integer grid can't express these
   (closest to 70/30 is 8/4, i.e. 66.7/33.3), so these set explicit
   flex-basis at the lg breakpoint instead.
   70/30 → Price Trend + Orders. 60/40 → Farmgate vs Export + Schedule. */
@media (min-width: 992px) {
    .cp-col-70 { flex: 0 0 70%; max-width: 70%; }
    .cp-col-30 { flex: 0 0 30%; max-width: 30%; }
    .cp-col-60 { flex: 0 0 60%; max-width: 60%; }
    .cp-col-40 { flex: 0 0 40%; max-width: 40%; }
}

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

/* ── Combined Latest Updates + Weather Forecast card ──────────────────────
   Two independent panes side by side rather than tabs — unlike Schedule
   (Calendar/Tasks) or Orders (Orders/Requests), these aren't alternate
   views of the same thing, so both should stay visible at once.
   flex-direction/flex-basis for the narrow-viewport case are bound inline
   in the template (isNarrowViewport) rather than via @media here — this
   exact rule set kept computing flex-direction: column at all viewport
   widths for reasons that didn't trace back to any matching CSS rule. */
.cp-updates-weather { display: flex; gap: 1.25rem; align-items: stretch; }
.cp-updates-weather__col { min-width: 0; }
.cp-updates-weather__col--news { flex: 1 1 auto; }
.cp-updates-weather__col--weather { flex: 0 0 260px; }
.cp-updates-weather__divider { width: 1px; flex-shrink: 0; background: var(--border); }

/* ── Combined Shipping Routes + Port Congestion + Transit Time card ──────
   Three equal panes, same responsive inline-style-driven approach as
   .cp-updates-weather above. */
.cp-tri-panel { display: flex; gap: 1.25rem; align-items: stretch; }
.cp-tri-panel__col { flex: 1 1 0; min-width: 0; }
.cp-tri-panel__divider { flex-shrink: 0; background: var(--border); }


/* ── Table ────────────────────────────────────────────────────────────── */
.cp-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 9px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.cp-table tbody td { padding: 10px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.cp-table tbody tr:last-child td { border-bottom: none; }
.cp-table tbody tr { transition: background-color .12s ease; }
.cp-table tbody tr:hover td { background-color: var(--surface-low); }
.cp-th-icon { width: 14px; height: 14px; margin-right: 5px; color: var(--green); opacity: .8; vertical-align: -2px; }
.cp-thumb { width: 32px; height: 32px; border-radius: 9px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f1e6d8; border: 1px solid #e6d5bf; }
.cp-thumb img { width: 100%; height: 100%; object-fit: cover; }
.cp-thumb-icon { width: 22px; height: 22px; }

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
    padding: 20px 24px 16px;
    background: #fff;
}

.gd-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 69, 50, 0.08);
    color: #000000;
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
    color: #000000;
    margin-bottom: 1px;
}

.gd-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.gd-modal__body {
    padding: 22px 26px 8px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 68vh;
    overflow-y: auto;
}

.gd-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.gd-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
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
.gd-input--error :deep(.el-textarea__inner),
.gd-input--error :deep(.el-select__wrapper) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.gd-input :deep(.el-input__wrapper),
.gd-input :deep(.el-textarea__inner),
.gd-input :deep(.el-select__wrapper) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #eef2f0 inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.gd-input :deep(.el-input__wrapper:hover),
.gd-input :deep(.el-textarea__inner:hover),
.gd-input :deep(.el-select__wrapper:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.gd-input :deep(.el-input__wrapper.is-focus),
.gd-input :deep(.el-textarea__inner:focus),
.gd-input :deep(.el-select__wrapper.is-focused) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #000000 inset;
}

.gd-modal__footer {
    display: flex;
    justify-content: flex-end;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.gd-modal__footer--tab {
    padding: 14px 0 0;
    margin-top: 4px;
    background: transparent;
    border-top: 1px solid #f3f4f6;
}

.gd-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #000000, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 10px 22px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.gd-btn-primary:hover:not(:disabled) { opacity: 0.9; }
.gd-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Personal / Business tabs ─────────────────────────────────────────
   The active tab *is* profileForm.profile_type — each pane is a complete,
   self-contained form for that account type, both reading/writing the
   same profileForm so switching tabs never discards or re-asks for
   anything already entered. */
.gd-modal__body--tabs { gap: 10px; }

.gd-tabs :deep(.el-tabs__header) { margin: 0 0 14px; }
.gd-tabs :deep(.el-tabs__nav-wrap::after) { background: #f3f4f6; height: 1px; }
.gd-tabs :deep(.el-tabs__nav) { gap: 4px; }
.gd-tabs :deep(.el-tabs__item) {
    height: 38px;
    padding: 0 4px;
    margin-right: 24px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #9ca3af;
    transition: color 0.12s ease;
}
.gd-tabs :deep(.el-tabs__item.is-active) { color: #000000; font-weight: 700; }
.gd-tabs :deep(.el-tabs__item:hover) { color: #000000; }
.gd-tabs :deep(.el-tabs__active-bar) { background-color: #000000; height: 2.5px; border-radius: 999px; }

.gd-tab-label { display: inline-flex; align-items: center; gap: 6px; }
.gd-tab-label .el-icon { font-size: 16px; }

.gd-tab-panel {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 4px 2px 10px;
}

@media (max-width: 575.98px) {
    .gd-field-row { grid-template-columns: 1fr; }
    .cp-hero { flex-direction: column; align-items: stretch; }
    .cp-hero__actions { flex-direction: column; align-items: stretch; }
    .cp-hero__btn { justify-content: center; }
}

@media (max-width: 480px) {
    :deep(.el-dialog.gd-modal) { width: 92vw !important; }
}
</style>
