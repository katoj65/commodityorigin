<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Search, ShoppingCart } from '@element-plus/icons-vue';

const props = defineProps({
    lots: { type: Array, default: () => [] },
});

/* ── Static market data ─────────────────────────────────────────────────── */
const priceChart = ref('7D');
const chartTabs  = ['24H', '7D', '30D', '90D'];

const kpis = [
    { label: 'Market Status',     value: 'Open',        accent: 'green',  sub: 'Trading active' },
    { label: 'Active Lots',       value: '184',         accent: null,     sub: '+12 today' },
    { label: 'Trading Volume',    value: '94,200 kg',   accent: null,     sub: 'Last 24 h' },
    { label: 'Avg. Price',        value: 'Shs. 11.80',  accent: null,     sub: 'Per kg' },
    { label: 'Active Buyers',     value: '237',         accent: null,     sub: '+8 online' },
    { label: 'Active Sellers',    value: '112',         accent: null,     sub: '+3 online' },
    { label: 'Sentiment',         value: 'Bullish',     accent: 'green',  sub: '↑ Demand rising' },
];

const priceIndex = [
    { type: 'Uganda Robusta',    origin: 'Central UG',   price: 'Shs. 9.20',  change24h: '+1.2%', changeW: '+3.4%', demand: 'High',   trend: 'up'   },
    { type: 'Uganda Arabica',    origin: 'Mt. Elgon',    price: 'Shs. 12.80', change24h: '+0.4%', changeW: '+1.8%', demand: 'High',   trend: 'up'   },
    { type: 'Brazil Arabica',    origin: 'Cerrado',      price: 'Shs. 14.10', change24h: '-0.3%', changeW: '-0.9%', demand: 'Medium', trend: 'down' },
    { type: 'Ethiopia Arabica',  origin: 'Yirgacheffe',  price: 'Shs. 15.60', change24h: '+0.8%', changeW: '+2.1%', demand: 'High',   trend: 'up'   },
    { type: 'Vietnam Robusta',   origin: 'Lam Dong',     price: 'Shs. 8.40',  change24h: '-0.6%', changeW: '-1.2%', demand: 'Medium', trend: 'down' },
];

const listings = computed(() => props.lots.length ? props.lots : [
    { id: 1, code: 'LOT-2026-001', name: 'Bugisu Premium AA',   origin: 'Mbale, UG',     type: 'Arabica',  qty: '1,200 kg', score: 87.5, price: 'Shs. 12.40', demand: 'High',   seller: 'Bugisu Coop',    status: 'Available' },
    { id: 2, code: 'LOT-2026-002', name: 'Sipi Falls Washed',   origin: 'Kapchorwa, UG', type: 'Arabica',  qty: '800 kg',   score: 86.2, price: 'Shs. 11.80', demand: 'High',   seller: 'Sipi Estates',   status: 'Auction'   },
    { id: 3, code: 'LOT-2026-003', name: 'Mt. Elgon Natural',   origin: 'Mbale, UG',     type: 'Arabica',  qty: '600 kg',   score: 85.5, price: 'Shs. 12.20', demand: 'Medium', seller: 'Elgon Farms',    status: 'Available' },
    { id: 4, code: 'LOT-2026-004', name: 'Rwenzori Highland',   origin: 'Kasese, UG',    type: 'Arabica',  qty: '1,500 kg', score: 88.0, price: 'Shs. 13.00', demand: 'High',   seller: 'Rwenzori Coop',  status: 'Export Ready' },
    { id: 5, code: 'LOT-2026-005', name: 'Central Robusta',     origin: 'Masaka, UG',    type: 'Robusta',  qty: '3,000 kg', score: 78.4, price: 'Shs. 9.10',  demand: 'Medium', seller: 'Uganda Coffee',  status: 'Available' },
    { id: 6, code: 'LOT-2026-006', name: 'Rwenzori Washed AA',  origin: 'Kasese, UG',    type: 'Arabica',  qty: '900 kg',   score: 86.8, price: 'Shs. 12.60', demand: 'High',   seller: 'Kasese Growers', status: 'Reserved'  },
]);

const activity = [
    { time: '2m ago',  type: 'purchase', label: 'Purchase completed', coffee: 'Bugisu AA',    qty: '300 kg',   price: 'Shs. 12.40' },
    { time: '5m ago',  type: 'bid',      label: 'Bid submitted',      coffee: 'Sipi Washed',  qty: '200 kg',   price: 'Shs. 11.60' },
    { time: '11m ago', type: 'list',     label: 'New lot listed',     coffee: 'Elgon Natural', qty: '600 kg',  price: 'Shs. 12.20' },
    { time: '18m ago', type: 'auction',  label: 'Auction closed',     coffee: 'Rwenzori AA',  qty: '1,200 kg', price: 'Shs. 13.00' },
    { time: '24m ago', type: 'purchase', label: 'Purchase completed', coffee: 'Central Robusta', qty: '500 kg', price: 'Shs. 9.20' },
];

const demandRegions = [
    { region: 'UAE',   index: 82, change: '+4%', bar: 82  },
    { region: 'EU',    index: 91, change: '+2%', bar: 91  },
    { region: 'USA',   index: 76, change: '+1%', bar: 76  },
    { region: 'Asia',  index: 68, change: '+6%', bar: 68  },
];

const topOrigins = [
    { rank: 1, name: 'Mount Elgon',     quality: '88.0', demand: 'High',   trend: 'up'   },
    { rank: 2, name: 'Rwenzori',        quality: '87.5', demand: 'High',   trend: 'up'   },
    { rank: 3, name: 'Central Uganda',  quality: '78.4', demand: 'Medium', trend: 'flat' },
    { rank: 4, name: 'Ethiopia Sidamo', quality: '90.2', demand: 'High',   trend: 'up'   },
    { rank: 5, name: 'Brazil Cerrado',  quality: '84.1', demand: 'Medium', trend: 'down' },
];

const weatherData = [
    { region: 'Mt. Elgon',  status: 'Favourable', risk: 'Low Risk',      tone: 'green', impact: 'Stable supply'      },
    { region: 'Rwenzori',   status: 'Dry Spell',  risk: 'Moderate Risk', tone: 'amber', impact: 'Minor yield concern' },
    { region: 'Yirgacheffe', status: 'Optimal',   risk: 'Low Risk',      tone: 'green', impact: 'Good crop forecast'  },
];

const opportunities = [
    { type: 'Uganda Arabica', origin: 'Mt. Elgon',   price: 'Shs. 12.80', demand: 'High',   score: 94 },
    { type: 'Ethiopia AA',    origin: 'Yirgacheffe',  price: 'Shs. 15.60', demand: 'High',   score: 91 },
    { type: 'Uganda Robusta', origin: 'Central UG',  price: 'Shs. 9.20',  demand: 'Medium', score: 77 },
];

const aiInsights = [
    { title: 'Demand Forecast',      tone: 'green', text: 'Robusta demand projected to increase over the next 30 days due to supply constraints in competing markets.' },
    { title: 'Price Forecast',       tone: 'green', text: 'Premium Arabica prices expected to hold steady. Specialty-grade lots above 86 SCA trading above market average.' },
    { title: 'Export Opportunities', tone: 'blue',  text: 'Export-ready lots are clearing 40% faster this quarter. Buyers in EU and UAE showing strong repeat purchasing.' },
    { title: 'Market Risks',         tone: 'amber', text: 'Dry conditions in Rwenzori may tighten supply by Q3. Consider securing forward contracts now.' },
];

const fabOpen = ref(false);
const alertForm = ref({ type: '', price: '' });
const quickBuy  = ref({ type: '', origin: '', qty: '', priceMax: '' });

const statusTone = (s) => {
    if (s === 'Available')    return 'green';
    if (s === 'Auction')      return 'amber';
    if (s === 'Export Ready') return 'blue';
    return 'surface';
};
</script>

<template>
    <AppLayout title="Live Market" full-width flush :show-banner="false">
        <div class="am-root">

            <!-- ── Page Header ───────────────────────────────────────────── -->
            <div class="am-page-header">
                <div class="am-page-header__left">
                    <h1 class="am-page-title">Live Market</h1>
                    <p class="am-page-sub">Monitor coffee prices, market activity, demand trends, and active trading opportunities in real time.</p>
                </div>
                <div class="am-page-header__actions">
                    <Link :href="route('checkout.index')" class="am-btn am-btn--primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        Buy Coffee
                    </Link>
                    <Link href="/sell-coffee" class="am-btn am-btn--outline">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Sell Coffee
                    </Link>
                    <Link :href="route('auction.index')" class="am-btn am-btn--outline">Auctions</Link>
                    <button class="am-btn am-btn--ghost">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        Set Alert
                    </button>
                </div>
            </div>

            <!-- ── Market Overview Strip ─────────────────────────────────── -->
            <div class="am-kpi-strip">
                <div v-for="kpi in kpis" :key="kpi.label" class="am-kpi">
                    <span class="am-kpi__label">{{ kpi.label }}</span>
                    <strong class="am-kpi__val" :class="kpi.accent === 'green' ? 'am-text-green' : ''">{{ kpi.value }}</strong>
                    <span class="am-kpi__sub">{{ kpi.sub }}</span>
                </div>
            </div>

            <!-- ── Main Body ─────────────────────────────────────────────── -->
            <div class="am-body">
                <div class="row g-4">

                    <!-- ── Left Column ─────────────────────────────────── -->
                    <div class="col-lg-8 col-12">

                        <!-- Price Performance -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Price Performance</h6>
                                <div class="am-tab-row">
                                    <button v-for="tab in chartTabs" :key="tab"
                                        class="am-tab" :class="{ 'am-tab--active': priceChart === tab }"
                                        @click="priceChart = tab">{{ tab }}</button>
                                </div>
                            </div>
                            <div class="am-card-body">
                                <!-- Chart area: SVG sparkline placeholder -->
                                <div class="am-chart-area">
                                    <svg viewBox="0 0 700 120" preserveAspectRatio="none" class="am-chart-svg">
                                        <defs>
                                            <linearGradient id="am-grad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%"   stop-color="#004532" stop-opacity="0.15"/>
                                                <stop offset="100%" stop-color="#004532" stop-opacity="0"/>
                                            </linearGradient>
                                        </defs>
                                        <path d="M0,90 C50,85 80,70 130,65 S200,55 250,50 S320,42 370,38 S440,30 490,35 S560,45 620,28 S680,20 700,18"
                                            fill="none" stroke="#004532" stroke-width="2.5"/>
                                        <path d="M0,90 C50,85 80,70 130,65 S200,55 250,50 S320,42 370,38 S440,30 490,35 S560,45 620,28 S680,20 700,18 L700,120 L0,120 Z"
                                            fill="url(#am-grad)"/>
                                    </svg>
                                </div>
                                <div class="am-chart-stats">
                                    <div class="am-cs">
                                        <span class="am-cs__label">Average</span>
                                        <strong class="am-cs__val">Shs. 11.80</strong>
                                    </div>
                                    <div class="am-cs">
                                        <span class="am-cs__label">Highest</span>
                                        <strong class="am-cs__val am-text-green">Shs. 15.60</strong>
                                    </div>
                                    <div class="am-cs">
                                        <span class="am-cs__label">Lowest</span>
                                        <strong class="am-cs__val am-text-red">Shs. 8.40</strong>
                                    </div>
                                    <div class="am-cs">
                                        <span class="am-cs__label">Volume Traded</span>
                                        <strong class="am-cs__val">94,200 kg</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coffee Price Index -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Coffee Price Index</h6>
                                <span class="am-badge am-badge--green am-badge--sm">Live</span>
                            </div>
                            <div class="am-card-body am-card-body--flush">
                                <div class="am-table-wrap">
                                    <table class="am-table">
                                        <thead>
                                            <tr>
                                                <th>Coffee Type</th>
                                                <th>Origin</th>
                                                <th>Price / kg</th>
                                                <th>24H</th>
                                                <th>7D</th>
                                                <th>Demand</th>
                                                <th>Trend</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="row in priceIndex" :key="row.type">
                                                <td><strong>{{ row.type }}</strong></td>
                                                <td class="am-td-muted">{{ row.origin }}</td>
                                                <td><strong>{{ row.price }}</strong></td>
                                                <td :class="row.trend === 'up' ? 'am-text-green' : 'am-text-red'">
                                                    <strong>{{ row.change24h }}</strong>
                                                </td>
                                                <td :class="row.trend === 'up' ? 'am-text-green' : 'am-text-red'">
                                                    <strong>{{ row.changeW }}</strong>
                                                </td>
                                                <td>
                                                    <span class="am-badge am-badge--sm" :class="row.demand === 'High' ? 'am-badge--green' : ''">{{ row.demand }}</span>
                                                </td>
                                                <td>
                                                    <span v-if="row.trend === 'up'" class="am-trend am-trend--up">↑</span>
                                                    <span v-else class="am-trend am-trend--down">↓</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Active Market Listings -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Active Market Listings</h6>
                                <span class="am-listing-count">{{ listings.length }} lots</span>
                            </div>
                            <div class="am-card-body am-card-body--flush">
                                <div class="am-table-wrap">
                                    <table class="am-table">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Origin</th>
                                                <th>Type</th>
                                                <th>Qty</th>
                                                <th>Score</th>
                                                <th>Price</th>
                                                <th>Demand</th>
                                                <th>Seller</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in listings" :key="lot.id || lot.code">
                                                <td>
                                                    <div class="am-lot-cell">
                                                        <span class="am-lot-code">{{ lot.code }}</span>
                                                        <span class="am-lot-name">{{ lot.name }}</span>
                                                    </div>
                                                </td>
                                                <td class="am-td-muted">{{ lot.origin }}</td>
                                                <td class="am-td-muted">{{ lot.type }}</td>
                                                <td>{{ lot.qty }}</td>
                                                <td><strong class="am-text-green">{{ lot.score }}</strong></td>
                                                <td><strong>{{ lot.price }}</strong></td>
                                                <td>
                                                    <span class="am-badge am-badge--sm" :class="lot.demand === 'High' ? 'am-badge--green' : ''">{{ lot.demand }}</span>
                                                </td>
                                                <td class="am-td-muted">{{ lot.seller }}</td>
                                                <td>
                                                    <span class="am-badge am-badge--sm" :class="`am-badge--${statusTone(lot.status)}`">{{ lot.status }}</span>
                                                </td>
                                                <td>
                                                    <div class="am-row-actions">
                                                        <button class="am-act-btn">View</button>
                                                        <button class="am-act-btn am-act-btn--green" @click="router.visit(route('checkout.index'))">Buy</button>
                                                        <button class="am-act-btn am-act-btn--amber" @click="router.visit(route('bid.place', lot.id || 1))">Bid</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Live Trading Activity -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Live Trading Activity</h6>
                                <span class="am-live-dot"></span>
                            </div>
                            <div class="am-card-body am-card-body--flush">
                                <table class="am-table">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Activity</th>
                                            <th>Coffee</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="a in activity" :key="a.time + a.label">
                                            <td class="am-td-muted am-mono">{{ a.time }}</td>
                                            <td>
                                                <span class="am-activity-dot" :class="`am-activity-dot--${a.type}`"></span>
                                                {{ a.label }}
                                            </td>
                                            <td><strong>{{ a.coffee }}</strong></td>
                                            <td>{{ a.qty }}</td>
                                            <td><strong class="am-text-green">{{ a.price }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Market Demand Dashboard -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Global Demand Dashboard</h6>
                            </div>
                            <div class="am-card-body">
                                <div class="row g-3">
                                    <div v-for="d in demandRegions" :key="d.region" class="col-6 col-md-3">
                                        <div class="am-demand-card">
                                            <div class="am-demand-region">{{ d.region }}</div>
                                            <div class="am-demand-index am-text-green">{{ d.index }}</div>
                                            <div class="am-demand-bar">
                                                <div class="am-demand-fill" :style="{ width: d.bar + '%' }"></div>
                                            </div>
                                            <div class="am-demand-change am-text-green">{{ d.change }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ── Right Column ─────────────────────────────────── -->
                    <div class="col-lg-4 col-12">

                        <!-- Quick Buy -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Quick Buy</h6>
                            </div>
                            <div class="am-card-body">
                                <div class="am-field">
                                    <label class="am-label">Coffee Type</label>
                                    <el-select v-model="quickBuy.type" placeholder="Select type…" size="default" style="width:100%">
                                        <el-option label="Uganda Arabica"  value="Uganda Arabica" />
                                        <el-option label="Uganda Robusta"  value="Uganda Robusta" />
                                        <el-option label="Ethiopia Arabica" value="Ethiopia Arabica" />
                                        <el-option label="Brazil Arabica"  value="Brazil Arabica" />
                                    </el-select>
                                </div>
                                <div class="am-field">
                                    <label class="am-label">Origin</label>
                                    <el-select v-model="quickBuy.origin" placeholder="Any origin…" size="default" style="width:100%">
                                        <el-option label="Mt. Elgon, Uganda"    value="Mt. Elgon, Uganda" />
                                        <el-option label="Rwenzori, Uganda"     value="Rwenzori, Uganda" />
                                        <el-option label="Yirgacheffe, Ethiopia" value="Yirgacheffe, Ethiopia" />
                                        <el-option label="Cerrado, Brazil"      value="Cerrado, Brazil" />
                                    </el-select>
                                </div>
                                <div class="am-field">
                                    <label class="am-label">Quantity (kg)</label>
                                    <el-input-number v-model="quickBuy.qty" :min="0" placeholder="Min. 60 kg" controls-position="right" style="width:100%" />
                                </div>
                                <div class="am-field">
                                    <label class="am-label">Max Price (Shs./kg)</label>
                                    <el-input-number v-model="quickBuy.priceMax" :min="0" :step="0.01" :precision="2" placeholder="e.g. 13.00" controls-position="right" style="width:100%" />
                                </div>
                                <div class="d-flex gap-2 mt-1">
                                    <el-button class="flex-fill" style="width:100%">
                                        <el-icon class="mr-2"><Search /></el-icon> Search Lots
                                    </el-button>
                                    <el-button tag="a" :href="route('checkout.index')" class="am-el-btn-green flex-fill" style="width:100%;text-decoration:none">
                                        <el-icon class="mr-2"><ShoppingCart /></el-icon> Buy Now
                                    </el-button>
                                </div>
                            </div>
                        </div>

                        <!-- Market Intelligence -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Market Intelligence</h6>
                            </div>
                            <div class="am-card-body">
                                <div class="am-intel-list">
                                    <div class="am-intel-row am-intel-row--green">
                                        <span class="am-intel-dot am-intel-dot--green"></span>
                                        Demand for Robusta increasing across Asia.
                                    </div>
                                    <div class="am-intel-row am-intel-row--blue">
                                        <span class="am-intel-dot am-intel-dot--blue"></span>
                                        Premium Arabica prices remain stable this week.
                                    </div>
                                    <div class="am-intel-row am-intel-row--amber">
                                        <span class="am-intel-dot am-intel-dot--amber"></span>
                                        Export-ready lots selling 40% faster than Q1.
                                    </div>
                                    <div class="am-intel-row am-intel-row--green">
                                        <span class="am-intel-dot am-intel-dot--green"></span>
                                        EU buyers seeking traceable specialty grades.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Performing Origins -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Top Performing Origins</h6>
                            </div>
                            <div class="am-card-body am-card-body--flush">
                                <div class="am-origins">
                                    <div v-for="o in topOrigins" :key="o.rank" class="am-origin-row">
                                        <span class="am-origin-rank">{{ o.rank }}</span>
                                        <div class="am-origin-info">
                                            <strong>{{ o.name }}</strong>
                                            <span>Score {{ o.quality }} · Demand {{ o.demand }}</span>
                                        </div>
                                        <span class="am-trend" :class="o.trend === 'up' ? 'am-trend--up' : o.trend === 'down' ? 'am-trend--down' : 'am-trend--flat'">
                                            {{ o.trend === 'up' ? '↑' : o.trend === 'down' ? '↓' : '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Weather Impact -->
                        <div class="am-card mb-4">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Weather &amp; Harvest Risk</h6>
                            </div>
                            <div class="am-card-body am-card-body--flush">
                                <div class="am-weather-list">
                                    <div v-for="w in weatherData" :key="w.region" class="am-weather-row">
                                        <div class="am-weather-info">
                                            <strong>{{ w.region }}</strong>
                                            <span>{{ w.status }} · {{ w.impact }}</span>
                                        </div>
                                        <span class="am-badge am-badge--sm" :class="`am-badge--${w.tone}`">{{ w.risk }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Alert -->
                        <div class="am-card">
                            <div class="am-card-head">
                                <h6 class="am-card-title">Set Price Alert</h6>
                            </div>
                            <div class="am-card-body">
                                <div class="am-field">
                                    <label class="am-label">Coffee Type</label>
                                    <select v-model="alertForm.type" class="am-input">
                                        <option value="">Select type…</option>
                                        <option>Uganda Arabica</option>
                                        <option>Uganda Robusta</option>
                                        <option>Ethiopia Arabica</option>
                                    </select>
                                </div>
                                <div class="am-field">
                                    <label class="am-label">Target Price (Shs./kg)</label>
                                    <input v-model="alertForm.price" type="number" step="0.01" class="am-input" placeholder="e.g. 11.50" />
                                </div>
                                <button class="am-btn am-btn--primary am-btn--sm w-100 mt-1">Create Alert</button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── Market Opportunities ───────────────────────────── -->
                <div class="mt-2 mb-4">
                    <h6 class="am-section-title mb-3">Market Opportunities</h6>
                    <div class="row g-3">
                        <div v-for="opp in opportunities" :key="opp.type" class="col-md-4 col-12">
                            <div class="am-card am-opp-card">
                                <div class="am-card-body">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div>
                                            <h6 class="am-opp-type">{{ opp.type }}</h6>
                                            <p class="am-opp-origin">{{ opp.origin }}</p>
                                        </div>
                                        <div class="am-opp-score" :class="opp.score >= 90 ? 'am-opp-score--high' : opp.score >= 80 ? 'am-opp-score--mid' : ''">
                                            {{ opp.score }}
                                        </div>
                                    </div>
                                    <div class="am-opp-stats">
                                        <div>
                                            <span class="am-kv-label">Price</span>
                                            <strong class="am-text-green d-block">{{ opp.price }}</strong>
                                        </div>
                                        <div>
                                            <span class="am-kv-label">Demand</span>
                                            <span class="am-badge am-badge--sm d-block mt-1" :class="opp.demand === 'High' ? 'am-badge--green' : ''">{{ opp.demand }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <Link :href="route('checkout.index')" class="am-btn am-btn--primary am-btn--sm flex-fill text-center">Buy</Link>
                                        <button class="am-btn am-btn--ghost am-btn--sm flex-fill">View Market</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── AI Market Insights ─────────────────────────────── -->
                <div class="mb-5">
                    <h6 class="am-section-title mb-3">AI Market Insights</h6>
                    <div class="row g-3">
                        <div v-for="ins in aiInsights" :key="ins.title" class="col-md-6 col-12">
                            <div class="am-card am-insight-card" :class="`am-insight-card--${ins.tone}`">
                                <div class="am-card-body">
                                    <div class="am-insight-icon-row mb-2">
                                        <span class="am-insight-tag" :class="`am-insight-tag--${ins.tone}`">{{ ins.title }}</span>
                                    </div>
                                    <p class="am-insight-text">{{ ins.text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── FAB ──────────────────────────────────────────────────────── -->
        <div class="am-fab-wrap">
            <transition name="am-fab-fade">
                <div v-if="fabOpen" class="am-fab-menu">
                    <button class="am-fab-item">Ask Market Advisor</button>
                    <button class="am-fab-item">Contact Seller</button>
                </div>
            </transition>
            <button class="am-fab" @click="fabOpen = !fabOpen" aria-label="Quick actions">
                <svg v-if="!fabOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

    </AppLayout>
</template>

<style scoped>
.am-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #191c1e;
    --on-surface-var:   #74777a;
    --surface-low:      #f2f4f6;
    --surface-high:     #eef2f0;
    --primary-fixed:    #a6f2d1;
    --on-primary-fixed: #002116;
    --secondary-fixed:  #fedcbe;
    --on-secondary-fixed:#291806;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ──────────────────────────────────────────────────────────── */
.am-page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    flex-wrap: wrap; gap: 1rem;
    padding: 1.75rem 2rem 0;
}
.am-page-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; color: var(--on-surface); margin: 0 0 0.25rem; }
.am-page-sub   { font-size: 0.875rem; color: var(--on-surface-var); margin: 0; max-width: 520px; line-height: 1.6; }
.am-page-header__actions { display: flex; gap: 8px; flex-wrap: wrap; padding-top: 4px; }

/* ── KPI strip ────────────────────────────────────────────────────────────── */
.am-kpi-strip {
    display: flex; gap: 0; overflow-x: auto;
    border-top: 1px solid var(--surface-high);
    border-bottom: 1px solid var(--surface-high);
    margin-top: 1.5rem;
    scrollbar-width: none;
}
.am-kpi-strip::-webkit-scrollbar { display: none; }
.am-kpi {
    flex: 1; min-width: 130px;
    padding: 1rem 1.25rem;
    display: flex; flex-direction: column; gap: 3px;
    border-right: 1px solid var(--surface-high);
}
.am-kpi:last-child { border-right: none; }
.am-kpi__label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.am-kpi__val   { font-size: 1rem; font-weight: 800; color: var(--on-surface); letter-spacing: -0.01em; }
.am-kpi__sub   { font-size: 0.6875rem; color: var(--on-surface-var); }

/* ── Body ─────────────────────────────────────────────────────────────────── */
.am-body { padding: 2rem 2rem 4rem; }

/* ── Cards ────────────────────────────────────────────────────────────────── */
.am-card {
    background: #ffffff;
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
}
.am-card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--surface-high);
    gap: 10px;
}
.am-card-body       { padding: 1.25rem; }
.am-card-body--flush { padding: 0; }
.am-card-title      { font-size: 0.875rem; font-weight: 800; color: var(--on-surface); margin: 0; letter-spacing: -0.01em; }
.am-section-title   { font-size: 0.875rem; font-weight: 800; color: var(--on-surface); letter-spacing: -0.01em; margin: 0; }
.am-listing-count   { font-size: 0.75rem; color: var(--on-surface-var); font-weight: 600; }

/* ── Tabs ─────────────────────────────────────────────────────────────────── */
.am-tab-row { display: flex; gap: 2px; }
.am-tab {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.75rem; font-weight: 700;
    padding: 5px 12px; border-radius: 0.375rem;
    border: 1px solid var(--surface-high);
    background: transparent; color: var(--on-surface-var);
    cursor: pointer; transition: background 0.12s ease;
}
.am-tab:hover       { background: var(--surface-low); }
.am-tab--active     { background: var(--primary); color: #fff; border-color: var(--primary); }

/* ── Chart ────────────────────────────────────────────────────────────────── */
.am-chart-area { height: 140px; background: var(--surface-low); border-radius: 0.5rem; overflow: hidden; margin-bottom: 1rem; }
.am-chart-svg  { width: 100%; height: 100%; }
.am-chart-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border-top: 1px solid var(--surface-high); margin: 0 -1.25rem -1.25rem; }
.am-cs {
    padding: 12px 14px; display: flex; flex-direction: column; gap: 3px;
    border-right: 1px solid var(--surface-high);
}
.am-cs:last-child { border-right: none; }
.am-cs__label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-var); }
.am-cs__val   { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); }

/* ── Tables ───────────────────────────────────────────────────────────────── */
.am-table-wrap { overflow-x: auto; }
.am-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.am-table th {
    padding: 9px 14px; text-align: left;
    font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--on-surface-var);
    background: var(--surface-low);
    border-bottom: 1px solid var(--surface-high);
    white-space: nowrap;
}
.am-table td {
    padding: 11px 14px; border-bottom: 1px solid var(--surface-high);
    color: var(--on-surface); vertical-align: middle; white-space: nowrap;
}
.am-table tbody tr:last-child td { border-bottom: none; }
.am-table tbody tr:hover td      { background: var(--surface-low); }
.am-td-muted { color: var(--on-surface-var) !important; }
.am-mono     { font-family: 'IBM Plex Mono', monospace; font-size: 0.75rem; }
.am-lot-cell { display: flex; flex-direction: column; gap: 2px; }
.am-lot-code { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary); }
.am-lot-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.am-row-actions { display: flex; gap: 4px; }
.am-act-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.6875rem; font-weight: 700;
    padding: 4px 10px; border-radius: 0.375rem;
    border: 1px solid var(--surface-high);
    background: transparent; color: var(--on-surface-var);
    cursor: pointer; transition: background 0.12s ease;
}
.am-act-btn:hover          { background: var(--surface-low); color: var(--on-surface); }
.am-act-btn--green         { border-color: var(--primary-fixed); color: var(--primary); }
.am-act-btn--green:hover   { background: var(--primary-fixed); }

.am-el-btn-green.el-button { background: linear-gradient(135deg, var(--primary), var(--primary-grad)); border-color: transparent; color: var(--on-primary); }
.am-el-btn-green.el-button:hover { opacity: 0.88; background: linear-gradient(135deg, var(--primary), var(--primary-grad)); border-color: transparent; color: var(--on-primary); }
.am-act-btn--amber         { border-color: var(--secondary-fixed); color: #92400e; }
.am-act-btn--amber:hover   { background: var(--secondary-fixed); }

/* ── Live dot ─────────────────────────────────────────────────────────────── */
.am-live-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,0.2);
    animation: am-pulse 1.8s ease-in-out infinite;
}
@keyframes am-pulse {
    0%, 100% { box-shadow: 0 0 0 3px rgba(22,163,74,0.2); }
    50%       { box-shadow: 0 0 0 6px rgba(22,163,74,0.08); }
}
.am-activity-dot {
    display: inline-block; width: 8px; height: 8px;
    border-radius: 50%; margin-right: 6px; vertical-align: middle;
}
.am-activity-dot--purchase { background: #16a34a; }
.am-activity-dot--bid      { background: #d97706; }
.am-activity-dot--list     { background: #2563eb; }
.am-activity-dot--auction  { background: #9333ea; }

/* ── Demand ───────────────────────────────────────────────────────────────── */
.am-demand-card { text-align: center; padding: 1rem; background: var(--surface-low); border-radius: 0.5rem; }
.am-demand-region { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); margin-bottom: 6px; }
.am-demand-index  { font-size: 1.75rem; font-weight: 900; letter-spacing: -0.03em; line-height: 1; margin-bottom: 8px; }
.am-demand-bar    { height: 5px; background: var(--surface-high); border-radius: 999px; overflow: hidden; margin-bottom: 6px; }
.am-demand-fill   { height: 100%; background: linear-gradient(90deg, var(--primary), var(--primary-grad)); border-radius: 999px; transition: width 0.8s ease; }
.am-demand-change { font-size: 0.75rem; font-weight: 700; }

/* ── Origins ──────────────────────────────────────────────────────────────── */
.am-origins { display: flex; flex-direction: column; gap: 0; }
.am-origin-row {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 1.25rem;
    border-bottom: 1px solid var(--surface-high);
}
.am-origin-row:last-child { border-bottom: none; }
.am-origin-rank { font-size: 0.75rem; font-weight: 800; color: var(--on-surface-var); width: 18px; flex-shrink: 0; }
.am-origin-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.am-origin-info strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.am-origin-info span   { font-size: 0.6875rem; color: var(--on-surface-var); }

/* ── Weather ──────────────────────────────────────────────────────────────── */
.am-weather-list { display: flex; flex-direction: column; }
.am-weather-row  {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 11px 1.25rem; border-bottom: 1px solid var(--surface-high);
}
.am-weather-row:last-child { border-bottom: none; }
.am-weather-info { display: flex; flex-direction: column; gap: 2px; }
.am-weather-info strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.am-weather-info span   { font-size: 0.6875rem; color: var(--on-surface-var); }

/* ── Intelligence ─────────────────────────────────────────────────────────── */
.am-intel-list { display: flex; flex-direction: column; gap: 10px; }
.am-intel-row  {
    display: flex; align-items: flex-start; gap: 8px;
    font-size: 0.8125rem; color: var(--on-surface);
    padding: 10px 12px; border-radius: 0.5rem;
    line-height: 1.5;
}
.am-intel-row--green { background: #f0fdf4; }
.am-intel-row--blue  { background: #eff6ff; }
.am-intel-row--amber { background: #fffbeb; }
.am-intel-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }
.am-intel-dot--green { background: #16a34a; }
.am-intel-dot--blue  { background: #2563eb; }
.am-intel-dot--amber { background: #d97706; }

/* ── Opportunity cards ────────────────────────────────────────────────────── */
.am-opp-type   { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); margin: 0 0 2px; letter-spacing: -0.01em; }
.am-opp-origin { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0; }
.am-opp-score  {
    font-size: 1.25rem; font-weight: 900; color: var(--on-surface-var);
    background: var(--surface-low); border-radius: 0.5rem;
    padding: 6px 12px; letter-spacing: -0.02em;
}
.am-opp-score--high { background: var(--primary-fixed); color: var(--on-primary-fixed); }
.am-opp-score--mid  { background: #f0fdf4; color: var(--primary); }
.am-opp-stats { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 0.75rem; }

/* ── AI Insights ──────────────────────────────────────────────────────────── */
.am-insight-card { border-left-width: 3px !important; }
.am-insight-card--green { border-left-color: #16a34a !important; }
.am-insight-card--blue  { border-left-color: #2563eb !important; }
.am-insight-card--amber { border-left-color: #d97706 !important; }
.am-insight-tag {
    display: inline-block;
    font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
    padding: 3px 10px; border-radius: 999px;
}
.am-insight-tag--green { background: #f0fdf4; color: #166534; }
.am-insight-tag--blue  { background: #eff6ff; color: #1e40af; }
.am-insight-tag--amber { background: #fffbeb; color: #92400e; }
.am-insight-text { font-size: 0.8125rem; color: var(--on-surface); line-height: 1.6; margin: 0; }

/* ── Badges ───────────────────────────────────────────────────────────────── */
.am-badge {
    display: inline-flex; align-items: center;
    background: var(--surface-low); color: var(--on-surface-var);
    border: 1px solid var(--surface-high);
    border-radius: 999px; font-size: 0.6875rem; font-weight: 700;
    letter-spacing: 0.05em; padding: 4px 12px; text-transform: uppercase;
}
.am-badge--sm      { font-size: 0.625rem; padding: 3px 9px; }
.am-badge--green   { background: var(--primary-fixed); color: var(--on-primary-fixed); border-color: transparent; }
.am-badge--amber   { background: var(--secondary-fixed); color: var(--on-secondary-fixed); border-color: transparent; }
.am-badge--blue    { background: #dbeafe; color: #1e40af; border-color: transparent; }
.am-badge--surface { background: var(--surface-low); color: var(--on-surface-var); }

/* ── Trend arrows ─────────────────────────────────────────────────────────── */
.am-trend        { font-size: 0.875rem; font-weight: 800; }
.am-trend--up    { color: #16a34a; }
.am-trend--down  { color: #dc2626; }
.am-trend--flat  { color: var(--on-surface-var); }

/* ── Form ─────────────────────────────────────────────────────────────────── */
.am-field  { display: grid; gap: 5px; margin-bottom: 0.875rem; }
.am-label  { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.am-input, select.am-input {
    width: 100%; background: var(--surface-low);
    border: 1px solid var(--surface-high); border-radius: 0.5rem;
    color: var(--on-surface); font-size: 0.875rem;
    font-family: 'Manrope', system-ui, sans-serif;
    padding: 9px 12px; outline: none; box-sizing: border-box;
    transition: border-color 0.15s ease;
    -webkit-appearance: none; appearance: none;
}
.am-input:focus { border-color: var(--primary); background: #fff; }

/* ── KV ───────────────────────────────────────────────────────────────────── */
.am-kv-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-var); }

/* ── Buttons ──────────────────────────────────────────────────────────────── */
.am-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem; font-weight: 700;
    border-radius: 0.5rem; padding: 9px 18px;
    cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    transition: opacity 0.15s ease, background 0.15s ease;
    border: none; white-space: nowrap;
}
.am-btn--primary  { background: linear-gradient(135deg, var(--primary), var(--primary-grad)); color: var(--on-primary); }
.am-btn--primary:hover  { opacity: 0.9; }
.am-btn--outline  { background: transparent; color: var(--on-surface); border: 1px solid #bec9c2; }
.am-btn--outline:hover  { background: var(--surface-low); }
.am-btn--ghost    { background: transparent; color: var(--on-surface-var); border: 1px solid var(--surface-high); }
.am-btn--ghost:hover    { background: var(--surface-low); color: var(--on-surface); }
.am-btn--sm       { font-size: 0.8125rem; padding: 7px 14px; }

/* ── Helpers ──────────────────────────────────────────────────────────────── */
.am-text-green { color: #16a34a !important; }
.am-text-red   { color: #dc2626 !important; }

/* ── FAB ──────────────────────────────────────────────────────────────────── */
.am-fab-wrap { position: fixed; bottom: 28px; right: 28px; z-index: 400; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.am-fab {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(0,69,50,0.3);
    transition: opacity 0.15s ease;
}
.am-fab:hover { opacity: 0.9; }
.am-fab-menu  { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
.am-fab-item {
    background: #ffffff; color: var(--on-surface);
    border: 1px solid var(--surface-high); border-radius: 0.5rem;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem; font-weight: 700;
    padding: 9px 18px; cursor: pointer; white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: background 0.12s ease;
}
.am-fab-item:hover { background: var(--surface-low); }
.am-fab-fade-enter-active,
.am-fab-fade-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.am-fab-fade-enter-from,
.am-fab-fade-leave-to { opacity: 0; transform: translateY(6px); }

/* ── Responsive ───────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .am-page-header { padding: 1.25rem 1.25rem 0; flex-direction: column; }
    .am-body        { padding: 1.25rem 1.25rem 3rem; }
    .am-chart-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>
