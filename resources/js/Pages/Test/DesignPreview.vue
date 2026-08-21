<script setup>
import { computed, ref } from 'vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    Bottom, Calendar, Clock, Cloudy, Coin, CoffeeCup, Document, EditPen, MapLocation,
    Notebook, Opportunity, Position, Pouring, PriceTag, Ship, Sunny, Tickets, Timer, Top,
    TrendCharts, Van, WarningFilled,
} from '@element-plus/icons-vue';

/* Content ported from GeneralDashboard.vue (the real, live dashboard page)
   into this design-system preview — same sections/data, restyled with the
   dp-* components already established on this page. The profile-completion
   modal and the unused "Decision Center" data (defined in the source but
   never rendered there) are left out as out of scope for a static preview. */

// 1. Market Pulse
const marketKpis = [
    { label: 'Arabica (KC)', value: '$5.10', unit: '/lb', change: '+2.4%', up: true, icon: CoffeeCup },
    { label: 'Robusta (RM)', value: '$2,340', unit: '/mt', change: '+1.1%', up: true, icon: CoffeeCup },
    { label: 'Coffee C Price', value: '186.40', unit: '¢/lb', change: '-0.6%', up: false, icon: PriceTag },
    { label: 'Market Sentiment', value: 'Bullish', unit: '', change: '72/100', up: true, icon: Opportunity },
];

// 2. Price Trend + Orders/Requests
const priceRange = ref('7D');

const ordersTab = ref('orders');
const orders = [
    { code: 'ORD-2291', item: 'Washed Yirgacheffe, 2t', status: 'Processing', statusType: 'warning' },
    { code: 'ORD-2288', item: 'Sul de Minas NY 2/3, 1 container', status: 'Shipped', statusType: 'info' },
    { code: 'ORD-2276', item: 'Colombia Supremo, 5t', status: 'Delivered', statusType: 'success' },
];
const buyerRequests = [
    { buyer: 'Nordic Roasters', request: 'Grade AA Arabica, 5t', budget: '$26,500' },
    { buyer: 'Berlin Kaffee', request: 'Organic Robusta, 8t', budget: '$19,200' },
    { buyer: 'Dubai Specialty Co', request: 'Washed Arabica, 3t', budget: '$15,800' },
];

// Market Opportunities
const markets = [
    { lot: 'LOT-4471', name: 'Ethiopia Yirgacheffe', origin: 'Ethiopia', type: 'Washed', quality: 92, price: '$6.40', demand: 'High', demandType: 'success' },
    { lot: 'LOT-4468', name: 'Colombia Supremo', origin: 'Colombia', type: 'Washed', quality: 88, price: '$5.35', demand: 'Medium', demandType: 'warning' },
    { lot: 'LOT-4460', name: 'Brazil Cerrado', origin: 'Brazil', type: 'Natural', quality: 84, price: '$4.10', demand: 'Medium', demandType: 'warning' },
    { lot: 'LOT-4452', name: 'Vietnam Robusta', origin: 'Vietnam', type: 'Natural', quality: 79, price: '$2.71', demand: 'Low', demandType: 'info' },
];

// 3. Coffee Prices — Farmgate vs Export
const priceComparison = [
    { country: 'Uganda', farmgate: '$4.10/kg', exportPrice: '$5.35/kg', delta: '+30.5%' },
    { country: 'Ethiopia', farmgate: '$4.62/kg', exportPrice: '$5.88/kg', delta: '+27.3%' },
    { country: 'Brazil', farmgate: '$5.02/kg', exportPrice: '$6.10/kg', delta: '+21.5%' },
    { country: 'Vietnam', farmgate: '$2.18/kg', exportPrice: '$2.71/kg', delta: '+24.3%' },
];

// Schedule — Calendar/Tasks
const scheduleTab = ref('calendar');
const events = [
    { date: 'Oct 15 - 20', title: 'Harvest Commencement', place: 'Ethiopia, Sidama Region', active: true },
    { date: 'Nov 02', title: 'Coffee Expo 2024', place: 'Tokyo, Japan - Trade Delegation', active: false },
    { date: 'Nov 18', title: 'Q4 Market Analysis', place: 'Internal Webinar', active: false },
];
const tasks = [
    { icon: WarningFilled, tint: '#ffdad6', tintColor: '#93000a', title: 'Approve Sample #402', tag: 'Urgent', tagType: 'danger', due: 'Due in 4 hours' },
    { icon: EditPen, tint: '#ffdad4', tintColor: '#2b1613', title: 'Finalize Brazil Contract', tag: 'High', tagType: 'info', due: 'Due Tomorrow' },
    { icon: Van, tint: '#e6e1e1', tintColor: '#1d1b1b', title: 'Review Shipping Documents', tag: 'Medium', tagType: '', due: 'Due in 3 days' },
];

// 4. Supply & Demand
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

const exchangeRates = [
    { pair: 'USD / KES', rate: '129.40' },
    { pair: 'USD / ETB', rate: '118.75' },
    { pair: 'USD / BRL', rate: '5.42' },
    { pair: 'USD / VND', rate: '25,180' },
];

// 5. Shipping & Ports
const shippingRoutes = [
    { route: 'Mombasa → Rotterdam', transit: '24 days', status: 'On time', statusType: 'success' },
    { route: 'Santos → Hamburg', transit: '19 days', status: 'Delayed 2d', statusType: 'warning' },
    { route: 'Ho Chi Minh → Dubai', transit: '11 days', status: 'On time', statusType: 'success' },
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

// 6. Intelligence & News
const newsCategories = ['All', 'Coffee News', 'Government Policies', 'Trade Agreements', 'Climate News'];
const activeCategory = ref('All');
const newsItems = [
    { category: 'Coffee News', title: "Arabica futures climb on tightening Q3 outlook", importance: 'High', importanceType: 'danger', region: 'Global', time: '2h ago' },
    { category: 'Government Policies', title: 'Uganda announces new export licensing framework', importance: 'Medium', importanceType: 'warning', region: 'Uganda', time: '5h ago' },
    { category: 'Trade Agreements', title: 'EU–Vietnam trade pact lowers coffee tariffs', importance: 'Medium', importanceType: 'warning', region: 'EU / Vietnam', time: '1d ago' },
    { category: 'Climate News', title: 'Rainfall delays harvest across Minas Gerais', importance: 'High', importanceType: 'danger', region: 'Brazil', time: '4h ago' },
];
const showAllNews = ref(false);
const categoryNews = computed(() => (activeCategory.value === 'All'
    ? newsItems
    : newsItems.filter((n) => n.category === activeCategory.value)));
const filteredNews = computed(() => (showAllNews.value ? categoryNews.value : categoryNews.value.slice(0, 3)));
function selectCategory(c) {
    activeCategory.value = c;
    showAllNews.value = false;
}

const weather = [
    { place: 'Minas Gerais, Brazil', icon: Pouring, condition: 'Rain, 19°C', note: 'Harvest delay risk' },
    { place: 'Central Highlands, Vietnam', icon: Sunny, condition: 'Sunny, 27°C', note: 'Favorable for drying' },
    { place: 'Sidamo, Ethiopia', icon: Cloudy, condition: 'Cloudy, 21°C', note: 'Stable conditions' },
];
</script>

<template>
<DesignPreviewLayout title="Coffee Intelligence Center">

    <div class="dp-kpi-grid">
        <el-card
            v-for="kpi in marketKpis"
            :key="kpi.label"
            class="dp-stat-card"
            shadow="hover"
            :body-style="{ padding: '20px' }"
        >
            <div class="dp-stat-card__head">
                <p class="dp-label-md">{{ kpi.label }}</p>
                <el-icon :size="18" class="dp-stat-card__icon"><component :is="kpi.icon" /></el-icon>
            </div>
            <p class="dp-headline-md dp-mono dp-stat-card__value">
                {{ kpi.value }}<span v-if="kpi.unit" class="dp-caption dp-stat-card__unit">{{ kpi.unit }}</span>
            </p>
            <p class="dp-caption dp-stat-card__delta" :class="kpi.up ? 'dp-stat-card__delta--up' : 'dp-stat-card__delta--down'">
                <el-icon :size="12"><component :is="kpi.up ? Top : Bottom" /></el-icon>
                {{ kpi.change }}
            </p>
        </el-card>
    </div>

    <div class="dp-split dp-split--70-30">
        <el-card class="dp-card" shadow="never" :body-style="{ padding: '32px' }">
            <div class="dp-card__head">
                <h2 class="dp-headline-md dp-card-title"><el-icon :size="18"><TrendCharts /></el-icon>Price Trend</h2>
                <el-radio-group v-model="priceRange" class="dp-range-toggle">
                    <el-radio-button label="7D" value="7D" />
                    <el-radio-button label="7Y" value="7Y" />
                </el-radio-group>
            </div>
            <div class="dp-chart">
                <div class="dp-chart__axis">
                    <span class="dp-caption dp-mono">$5.20</span>
                    <span class="dp-caption dp-mono">$4.90</span>
                    <span class="dp-caption dp-mono">$4.60</span>
                    <span class="dp-caption dp-mono">$4.30</span>
                </div>
                <div class="dp-chart__plot">
                    <svg viewBox="0 0 500 200" preserveAspectRatio="none">
                        <polyline
                            fill="none"
                            stroke="var(--dp-primary)"
                            stroke-width="2.5"
                            points="0,150 60,160 120,140 180,110 240,125 300,70 360,90 420,45 480,60 500,20"
                        />
                    </svg>
                    <span class="dp-chart__point" />
                </div>
            </div>
            <div class="dp-chart__labels">
                <span class="dp-caption dp-mono">Mon</span>
                <span class="dp-caption dp-mono">Wed</span>
                <span class="dp-caption dp-mono">Fri</span>
                <span class="dp-caption dp-mono">Sun</span>
            </div>
        </el-card>

        <el-card class="dp-card" shadow="never" :body-style="{ padding: '28px' }">
            <div class="dp-card__head">
                <h2 class="dp-headline-md dp-card-title"><el-icon :size="18"><Tickets /></el-icon>Orders</h2>
                <el-radio-group v-model="ordersTab" class="dp-range-toggle">
                    <el-radio-button label="orders" value="orders">Orders <span class="dp-badge-count">{{ orders.length }}</span></el-radio-button>
                    <el-radio-button label="requests" value="requests">Requests <span class="dp-badge-count">{{ buyerRequests.length }}</span></el-radio-button>
                </el-radio-group>
            </div>
            <el-table v-if="ordersTab === 'orders'" :data="orders" :show-header="false" class="dp-table dp-table--plain">
                <el-table-column label="Order">
                    <template #default="{ row }">
                        <p class="dp-body-md dp-table__primary">{{ row.code }}</p>
                        <p class="dp-caption dp-table__muted">{{ row.item }}</p>
                    </template>
                </el-table-column>
                <el-table-column label="Status" width="130" class-name="dp-nowrap">
                    <template #default="{ row }">
                        <el-tag :type="row.statusType" size="small" round>{{ row.status }}</el-tag>
                    </template>
                </el-table-column>
            </el-table>
            <el-table v-else :data="buyerRequests" :show-header="false" class="dp-table dp-table--plain">
                <el-table-column label="Buyer">
                    <template #default="{ row }">
                        <p class="dp-body-md dp-table__primary">{{ row.buyer }}</p>
                        <p class="dp-caption dp-table__muted">{{ row.request }}</p>
                    </template>
                </el-table-column>
                <el-table-column label="Budget" class-name="dp-mono dp-nowrap" width="100">
                    <template #default="{ row }">
                        <span class="dp-trend dp-trend--up">{{ row.budget }}</span>
                    </template>
                </el-table-column>
            </el-table>
        </el-card>
    </div>

    <div>
        <el-card class="dp-table-card" shadow="never" :body-style="{ padding: 0 }">
            <div class="dp-table-scroll">
                <el-table :data="markets" class="dp-table dp-table--wide">
                    <el-table-column label="Lot">
                        <template #default="{ row }">
                            <p class="dp-body-md dp-table__primary">{{ row.lot }}</p>
                            <p class="dp-caption dp-table__muted">{{ row.name }}</p>
                        </template>
                    </el-table-column>
                    <el-table-column prop="origin" label="Origin" />
                    <el-table-column prop="type" label="Type" />
                    <el-table-column prop="quality" label="Quality" class-name="dp-mono" />
                    <el-table-column prop="price" label="Price / kg" class-name="dp-mono" />
                    <el-table-column label="Demand">
                        <template #default="{ row }">
                            <el-tag :type="row.demandType" size="small" round>{{ row.demand }}</el-tag>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </el-card>
    </div>

    <div class="dp-split dp-split--60-40">
        <div>
            <el-card class="dp-table-card" shadow="never" :body-style="{ padding: 0 }">
                <div class="dp-table-scroll">
                    <el-table :data="priceComparison" class="dp-table dp-table--wide">
                        <el-table-column prop="country" label="Country" />
                        <el-table-column prop="farmgate" label="Farmgate" class-name="dp-mono" />
                        <el-table-column prop="exportPrice" label="Export" class-name="dp-mono" />
                        <el-table-column label="Margin" class-name="dp-mono">
                            <template #default="{ row }">
                                <span class="dp-trend dp-trend--up">{{ row.delta }}</span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </el-card>
        </div>

        <el-card class="dp-card dp-events" shadow="never" :body-style="{ padding: '28px' }">
            <div class="dp-card__head">
                <h2 class="dp-headline-md dp-card-title"><el-icon :size="18"><Calendar /></el-icon>Schedule</h2>
                <el-radio-group v-model="scheduleTab" class="dp-range-toggle">
                    <el-radio-button label="calendar" value="calendar">Calendar</el-radio-button>
                    <el-radio-button label="tasks" value="tasks">Tasks <span class="dp-badge-count">{{ tasks.length }}</span></el-radio-button>
                </el-radio-group>
            </div>
            <el-timeline v-if="scheduleTab === 'calendar'" class="dp-timeline">
                <el-timeline-item v-for="ev in events" :key="ev.title" :hollow="!ev.active">
                    <p class="dp-caption" :class="ev.active ? 'dp-timeline__date--active' : 'dp-timeline__date'">{{ ev.date }}</p>
                    <p class="dp-body-md dp-timeline__title">{{ ev.title }}</p>
                    <p class="dp-caption dp-timeline__place">{{ ev.place }}</p>
                </el-timeline-item>
            </el-timeline>
            <div v-else class="dp-tasks">
                <el-card
                    v-for="task in tasks"
                    :key="task.title"
                    class="dp-task"
                    shadow="hover"
                    :body-style="{ padding: '16px' }"
                >
                    <div class="dp-task__icon" :style="{ background: task.tint, color: task.tintColor }">
                        <el-icon :size="20"><component :is="task.icon" /></el-icon>
                    </div>
                    <div class="dp-task__body">
                        <div class="dp-task__top">
                            <h3 class="dp-headline-sm dp-truncate">{{ task.title }}</h3>
                            <el-tag :type="task.tagType || undefined" class="dp-task__tag" size="small" round>{{ task.tag }}</el-tag>
                        </div>
                        <div class="dp-caption dp-task__meta">
                            <span><el-icon :size="13"><Clock /></el-icon>{{ task.due }}</span>
                        </div>
                    </div>
                </el-card>
            </div>
        </el-card>
    </div>

    <div class="dp-split">
        <el-card class="dp-card" shadow="never" :body-style="{ padding: '28px' }">
            <div class="dp-tri">
                <div class="dp-tri__col">
                    <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><MapLocation /></el-icon>Producing Countries</h3>
                    <el-table :data="producingCountries" :show-header="false" class="dp-table">
                        <el-table-column prop="country" label="Country" class-name="dp-nowrap" width="92" />
                        <el-table-column label="Share">
                            <template #default="{ row }">
                                <el-progress :percentage="row.share" :stroke-width="6" :show-text="false" color="var(--dp-primary)" />
                            </template>
                        </el-table-column>
                        <el-table-column prop="volume" label="Volume" class-name="dp-mono dp-nowrap" width="76" align="right" />
                    </el-table>
                </div>
                <div class="dp-tri__col">
                    <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><Opportunity /></el-icon>Demand Hotspots</h3>
                    <el-table :data="demandHotspots" :show-header="false" class="dp-table">
                        <el-table-column label="Region">
                            <template #default="{ row }">
                                <p class="dp-body-md dp-table__primary">{{ row.region }}</p>
                                <p class="dp-caption dp-table__muted">{{ row.note }}</p>
                            </template>
                        </el-table-column>
                        <el-table-column label="Trend" class-name="dp-mono dp-nowrap" width="76">
                            <template #default="{ row }">
                                <span class="dp-trend dp-trend--up">{{ row.trend }}</span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </el-card>

        <el-card class="dp-card" shadow="never" :body-style="{ padding: '28px' }">
            <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><Coin /></el-icon>Currency Exchange</h3>
            <el-table :data="exchangeRates" :show-header="false" class="dp-table">
                <el-table-column prop="pair" label="Pair" />
                <el-table-column prop="rate" label="Rate" class-name="dp-mono" align="right" />
            </el-table>
        </el-card>
    </div>

    <el-card class="dp-card" shadow="never" :body-style="{ padding: '28px' }">
        <div class="dp-tri">
            <div class="dp-tri__col">
                <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><Ship /></el-icon>Shipping Routes</h3>
                <el-table :data="shippingRoutes" :show-header="false" class="dp-table">
                    <el-table-column label="Route">
                        <template #default="{ row }">
                            <p class="dp-body-md dp-table__primary">{{ row.route }}</p>
                            <p class="dp-caption dp-table__muted">Transit: {{ row.transit }}</p>
                        </template>
                    </el-table-column>
                    <el-table-column label="Status" width="120" class-name="dp-nowrap">
                        <template #default="{ row }">
                            <el-tag :type="row.statusType" size="small" round>{{ row.status }}</el-tag>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="dp-tri__col">
                <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><Position /></el-icon>Port Congestion</h3>
                <el-table :data="portCongestion" :show-header="false" class="dp-table">
                    <el-table-column prop="port" label="Port" class-name="dp-nowrap" width="96" />
                    <el-table-column label="Congestion">
                        <template #default="{ row }">
                            <el-progress
                                :percentage="row.congestion"
                                :stroke-width="6"
                                :show-text="false"
                                :color="row.congestion > 55 ? 'var(--dp-error)' : 'var(--dp-primary)'"
                            />
                        </template>
                    </el-table-column>
                    <el-table-column label="%" class-name="dp-mono dp-nowrap" width="60" align="right">
                        <template #default="{ row }">{{ row.congestion }}%</template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="dp-tri__col">
                <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><Timer /></el-icon>Transit Time</h3>
                <el-table :data="transitTimeStats" :show-header="false" class="dp-table">
                    <el-table-column prop="label" label="Label" />
                    <el-table-column prop="value" label="Value" class-name="dp-mono" align="right" />
                </el-table>
            </div>
        </div>
    </el-card>

    <div class="dp-split dp-split--25-75">
        <el-card class="dp-card" shadow="never" :body-style="{ padding: '24px' }">
            <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><Notebook /></el-icon>Categories</h3>
            <el-menu :default-active="activeCategory" class="dp-cat-menu" @select="selectCategory">
                <el-menu-item v-for="c in newsCategories" :key="c" :index="c" class="dp-cat-item">
                    <span>{{ c }}</span>
                    <span class="dp-cat-count">{{ c === 'All' ? newsItems.length : newsItems.filter((n) => n.category === c).length }}</span>
                </el-menu-item>
            </el-menu>
        </el-card>

        <el-card class="dp-card" shadow="never" :body-style="{ padding: '28px' }">
            <div class="dp-tri">
                <div class="dp-tri__col">
                    <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><Document /></el-icon>Latest Updates</h3>
                    <ul class="dp-news">
                        <li v-for="n in filteredNews" :key="n.title">
                            <div class="dp-news__top">
                                <el-tag :type="n.importanceType" size="small" round>{{ n.importance }}</el-tag>
                                <span class="dp-caption dp-news__cat">{{ n.category }}</span>
                                <span class="dp-caption dp-news__meta">{{ n.time }}</span>
                            </div>
                            <p class="dp-body-md dp-news__title">{{ n.title }}</p>
                            <span class="dp-caption dp-news__region"><el-icon :size="12"><MapLocation /></el-icon>{{ n.region }}</span>
                        </li>
                    </ul>
                    <el-button
                        v-if="categoryNews.length > 3"
                        text
                        class="dp-news__more"
                        @click="showAllNews = !showAllNews"
                    >{{ showAllNews ? 'View Less' : 'View More' }}</el-button>
                </div>
                <div class="dp-tri__col">
                    <h3 class="dp-headline-sm dp-panel-title"><el-icon :size="18"><Sunny /></el-icon>Weather Forecast</h3>
                    <div class="dp-weather-list">
                        <div v-for="w in weather" :key="w.place" class="dp-weather-row">
                            <div>
                                <p class="dp-body-md dp-table__primary">{{ w.place }}</p>
                                <p class="dp-caption dp-weather-row__now"><el-icon :size="14"><component :is="w.icon" /></el-icon>{{ w.condition }}</p>
                            </div>
                            <p class="dp-caption dp-table__muted" style="max-width:96px;text-align:right;">{{ w.note }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </el-card>
    </div>
</DesignPreviewLayout>
</template>

<style scoped>
/* Tokens/typography classes live on .dp-shell in DesignPreviewLayout.vue
   and cascade down through the slot into this page's markup. */

.dp-hero-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 32px; flex-wrap: wrap; }
.dp-eyebrow { color: var(--dp-primary); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 8px !important; }

.dp-live-pill { display: flex; align-items: center; gap: 8px; background: var(--dp-surface-container); color: var(--dp-on-surface-variant); font-size: 13px; font-weight: 500; padding: 8px 16px; border-radius: 999px; }
.dp-live-dot { position: relative; display: inline-flex; width: 12px; height: 12px; border-radius: 50%; background: var(--dp-secondary); }
.dp-live-dot__ping { position: absolute; inset: 0; border-radius: 50%; background: var(--dp-secondary); opacity: 0.75; animation: dp-ping 1.6s cubic-bezier(0, 0, 0.2, 1) infinite; }
@keyframes dp-ping { 75%, 100% { transform: scale(2.4); opacity: 0; } }

.dp-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: -24px; }
.dp-kpi-grid .dp-stat-card__head { margin-bottom: 20px; }

.dp-stat-card { border-radius: 12px; border: 1px solid var(--dp-border-subtle); overflow: hidden; position: relative; height: 100%; }
.dp-stat-card :deep(.el-card__body) { position: relative; z-index: 1; }
.dp-stat-card__head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 48px; position: relative; }
.dp-stat-card__icon { color: var(--dp-outline-variant); }
.dp-stat-card__value { color: var(--dp-primary); position: relative; font-size: 21px; line-height: 26px; letter-spacing: -0.015em; font-weight: 800; }
.dp-stat-card__unit { font-family: var(--dp-font-sans); font-size: 12px; font-weight: 600; letter-spacing: 0; color: var(--dp-on-surface-variant); margin-left: 4px; }
.dp-stat-card__delta { display: flex; align-items: center; gap: 4px; margin-top: 9px; position: relative; }
.dp-stat-card__delta--up { color: var(--dp-secondary); }
.dp-stat-card__delta--down { color: var(--dp-error); }
.dp-stat-card__delta--neutral { color: var(--dp-on-surface-variant); }

.dp-card { border-radius: 14px; border: 1px solid var(--dp-border-subtle); height: 100%; }
.dp-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 12px; }
.dp-card-title { display: flex; align-items: center; gap: 8px; }
.dp-card-title .el-icon { color: var(--dp-primary); }

.dp-range-toggle :deep(.el-radio-button__inner) { border-radius: 8px !important; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); font-size: 14px; font-weight: 600; box-shadow: none !important; padding: 8px 16px; margin-left: 8px; display: inline-flex; align-items: center; }
.dp-range-toggle :deep(.el-radio-button.is-active .el-radio-button__inner) { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.dp-badge-count { display: inline-flex; align-items: center; justify-content: center; min-width: 16px; height: 16px; padding: 0 4px; margin-left: 6px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 10px; font-weight: 800; }

.dp-chart { height: 256px; position: relative; display: flex; padding: 0 8px 24px; border-bottom: 2px solid var(--dp-surface-container-highest); }
.dp-chart__axis { position: absolute; left: 0; top: 0; bottom: 0; display: flex; flex-direction: column; justify-content: space-between; color: var(--dp-outline-variant); padding: 24px 0; }
.dp-chart__plot { width: 100%; height: 100%; margin-left: 48px; position: relative; display: flex; align-items: flex-end; }
.dp-chart__plot svg { width: 100%; height: 100%; }
.dp-chart__point { position: absolute; width: 8px; height: 8px; border-radius: 50%; background: var(--dp-primary); right: 10%; top: 20%; box-shadow: 0 0 0 4px var(--dp-surface-container-lowest); }
.dp-chart__labels { display: flex; justify-content: space-between; margin-left: 48px; margin-top: 16px; color: var(--dp-outline-variant); }

.dp-split { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: stretch; }
.dp-split > * { min-width: 0; }
.dp-split--70-30 { grid-template-columns: 7fr 3fr; }
.dp-split--60-40 { grid-template-columns: 3fr 2fr; }
.dp-split--25-75 { grid-template-columns: 1fr 3fr; }

.dp-section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; gap: 12px; }
.dp-link-btn.el-button { color: var(--dp-primary); font-size: 14px; font-weight: 600; letter-spacing: 0.05em; padding: 0; height: auto; display: inline-flex; align-items: center; gap: 4px; }
.dp-link-btn.el-button:hover { color: var(--dp-primary-container); }

.dp-tasks { display: flex; flex-direction: column; gap: 12px; }
.dp-task { border-radius: 10px; border: none; cursor: pointer; }
.dp-task :deep(.el-card__body) { display: flex; align-items: flex-start; gap: 16px; }
.dp-task__icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.dp-task__body { flex: 1; min-width: 0; }
.dp-task__top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px; }
.dp-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-task__tag { flex-shrink: 0; letter-spacing: 0.05em; text-transform: uppercase; border: none; }
.dp-task__meta { display: flex; align-items: center; gap: 16px; color: var(--dp-outline); }
.dp-task__meta span { display: flex; align-items: center; gap: 4px; }

.dp-table-card { border-radius: 14px; border: 1px solid var(--dp-border-subtle); overflow: hidden; height: 100%; }
.dp-table-scroll { overflow-x: auto; }
.dp-table.el-table { --el-table-border-color: var(--dp-surface-container-highest); font-family: var(--dp-font-sans); }
.dp-table--wide.el-table { min-width: 480px; }
.dp-table--plain :deep(td.el-table__cell) { border-bottom: none; padding: 15px 0; }
.dp-table--plain :deep(.el-table__inner-wrapper::before) { display: none; }
.dp-table--plain :deep(tr) { background: transparent; }
.dp-table :deep(th.el-table__cell) { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
.dp-table :deep(td.el-table__cell) { padding: 14px 0; color: var(--dp-on-surface); }
.dp-table :deep(.cell) { padding: 0 16px; }
.dp-table :deep(.dp-nowrap .cell) { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dp-table :deep(.el-progress-bar__outer) { background: var(--dp-surface-container-high); }
.dp-table__primary { font-weight: 500; margin: 0; }
.dp-table__muted { color: var(--dp-outline-variant); margin: 2px 0 0; }

.dp-trend { display: inline-flex; align-items: center; gap: 4px; font-weight: 500; }
.dp-trend--up { color: var(--dp-secondary); }
.dp-trend--down { color: var(--dp-error); }

.dp-panel-title { display: flex; align-items: center; gap: 8px; margin-bottom: 16px !important; color: var(--dp-on-surface); }
.dp-panel-title .el-icon { color: var(--dp-primary); }

/* Tri-panel — auto-adapts to 2 or 3 columns (Producing/Demand;
   Shipping/Port/Transit) with a divider between each. */
.dp-tri { display: flex; gap: 0; align-items: stretch; }
.dp-tri__col { flex: 1 1 0; min-width: 0; padding: 0 28px; }
.dp-tri__col:first-child { padding-left: 0; }
.dp-tri__col:last-child { padding-right: 0; }
.dp-tri__col:not(:first-child) { border-left: 1px solid var(--dp-border-subtle); }

/* Categories list — News (el-menu). */
.dp-cat-menu.el-menu { border-right: none; background: transparent; }
.dp-cat-item.el-menu-item {
    height: auto;
    line-height: normal;
    padding: 9px 10px !important;
    margin-bottom: 2px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 600;
    color: var(--dp-on-surface-variant);
}
.dp-cat-item.el-menu-item:hover { background: var(--dp-surface-container); color: var(--dp-on-surface-variant); }
.dp-cat-item.el-menu-item.is-active { background: var(--dp-primary-container); color: var(--dp-on-primary-container); border: none; }
.dp-cat-count { font-size: 11px; font-weight: 700; color: var(--dp-outline); background: var(--dp-surface-container); border-radius: 999px; padding: 1px 8px; }
.dp-cat-item.el-menu-item.is-active .dp-cat-count { background: var(--dp-surface-container-lowest); color: var(--dp-primary); }

.dp-events { position: relative; background: var(--dp-surface-container) !important; }
.dp-timeline { margin-bottom: 0; }
.dp-timeline :deep(.el-timeline-item__tail) { border-color: var(--dp-outline-variant); opacity: 0.5; }
.dp-timeline :deep(.el-timeline-item__node) { background: var(--dp-surface-container-lowest); }
.dp-timeline :deep(.el-timeline-item__wrapper) { padding-left: 20px; }
.dp-timeline__date { color: var(--dp-outline); margin-bottom: 4px !important; }
.dp-timeline__date--active { color: var(--dp-primary); margin-bottom: 4px !important; }
.dp-timeline__title { font-weight: 600; color: var(--dp-on-surface); margin: 0 !important; }
.dp-timeline__place { color: var(--dp-on-surface-variant); margin-top: 4px !important; }

.dp-news { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 14px; }
.dp-news li { padding-bottom: 14px; border-bottom: 1px solid var(--dp-surface-container); }
.dp-news li:last-child { border-bottom: none; padding-bottom: 0; }
.dp-news__top { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.dp-news__cat { color: var(--dp-primary); font-weight: 700; }
.dp-news__meta { color: var(--dp-outline); margin-left: auto; }
.dp-news__title { color: var(--dp-on-surface); font-weight: 600; margin-bottom: 4px !important; }
.dp-news__region { display: inline-flex; align-items: center; gap: 4px; color: var(--dp-outline); }
.dp-news__more { padding: 0; height: auto; margin-top: 4px; color: var(--dp-primary); font-size: 13px; font-weight: 600; }
.dp-news__more:hover { color: var(--dp-primary-container); }

.dp-weather-list { display: flex; flex-direction: column; gap: 12px; }
.dp-weather-row { padding: 14px; background: var(--dp-surface-container); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.dp-weather-row p { margin: 0; }
.dp-weather-row__now { display: flex; align-items: center; gap: 8px; color: var(--dp-on-surface-variant); margin-top: 4px !important; }

@media (max-width: 1279.98px) {
    .dp-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .dp-split { grid-template-columns: 1fr; }
    .dp-tri { flex-direction: column; gap: 20px; }
    .dp-tri__col { padding: 0; }
    .dp-tri__col:not(:first-child) { border-left: none; border-top: 1px solid var(--dp-border-subtle); padding-top: 20px; }
}

@media (max-width: 767.98px) {
    .dp-hero-head { margin-bottom: 24px; }
    .dp-kpi-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
    .dp-stat-card :deep(.el-card__body) { padding: 16px !important; }
    .dp-card :deep(.el-card__body) { padding: 20px !important; }
    .dp-card__head { margin-bottom: 20px; }
    .dp-chart { height: 200px; padding: 0 4px 16px; }
    .dp-split { gap: 16px; }
    .dp-section-head { margin-bottom: 20px; }
    .dp-task :deep(.el-card__body) { padding: 14px !important; gap: 12px; }
    .dp-task__icon { width: 36px; height: 36px; }
}
</style>
