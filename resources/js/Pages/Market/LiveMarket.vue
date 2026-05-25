<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, Connection, Document,
    Filter, Location, Medal, Message,
    Opportunity, Promotion, Reading, Share, ShoppingCart,
    TrendCharts, Van, View, Warning, WarnTriangleFilled,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const props = defineProps({
    lots: { type: Array, default: () => [] },
});

/* ── Filters ───────────────────────────────────────────────────── */
const filters  = ref({ type: '', origin: '', market: '', quality: '', exportReady: false, tokenized: false, timeframe: '24H' });
const timeframe = ref('24H');
const chartTab  = ref('Price');
const tradeMode = ref('Buy');

/* ── Ticker ────────────────────────────────────────────────────── */
const ticker = [
    { name: 'Uganda Robusta',   price: '$2.85/kg', change: '+4.2%', up: true  },
    { name: 'Rwenzori Arabica', price: '$4.60/kg', change: '+5.0%', up: true  },
    { name: 'Brazil Arabica',   price: '$4.10/kg', change: '-1.1%', up: false },
    { name: 'Vietnam Robusta',  price: '$2.70/kg', change: '+2.8%', up: true  },
    { name: 'Kenya AA',         price: '$6.20/kg', change: '+0.5%', up: true  },
    { name: 'Colombia Huila',   price: '$5.45/kg', change: '-0.8%', up: false },
];

/* ── KPIs ──────────────────────────────────────────────────────── */
const kpis = [
    { label: 'Market Status', value: 'Open',    sub: 'Active trading',   tone: 'success', icon: Check      },
    { label: 'Avg. Price',    value: '$3.82',   sub: 'Per kg avg',       tone: 'primary', icon: CollectionTag },
    { label: '24h Volume',    value: '1.2M kg', sub: 'All lots',         tone: 'info',    icon: Box        },
    { label: 'Price Change',  value: '+2.4%',   sub: 'Since yesterday',  tone: 'success', icon: TrendCharts },
    { label: 'Top Demand',    value: 'UAE',     sub: 'Highest activity', tone: 'warning', icon: Location   },
    { label: 'Volatility',    value: 'Medium',  sub: 'Stability',        tone: 'warning', icon: Warning    },
];

/* ── Chart data ────────────────────────────────────────────────── */
const candles = [
    { h: 42, dir: 'up' }, { h: 68, dir: 'down' }, { h: 55, dir: 'up' },
    { h: 82, dir: 'down' }, { h: 47, dir: 'up' }, { h: 71, dir: 'down' },
    { h: 53, dir: 'up' }, { h: 88, dir: 'down' }, { h: 36, dir: 'up' },
    { h: 64, dir: 'down' }, { h: 78, dir: 'up' }, { h: 91, dir: 'down' },
    { h: 58, dir: 'up' }, { h: 44, dir: 'down' }, { h: 76, dir: 'up' },
];
const volumes = [18, 30, 22, 46, 28, 50, 32, 56, 24, 38, 44, 62];

/* ── Lots table ────────────────────────────────────────────────── */
const mockLots = [
    { id: 'UG-001', name: 'Mount Elgon AA',      origin: 'Uganda',   type: 'Arabica', price: 5.20, change: '+1.8%', up: true,  qty: '4,200 kg',  demand: 'High',   status: ['Verified','Export Ready'], statusTone: 'live'    },
    { id: 'UG-002', name: 'Kyoga Robusta R1',     origin: 'Uganda',   type: 'Robusta', price: 1.88, change: '+4.2%', up: true,  qty: '12,500 kg', demand: 'V.High', status: ['Verified'],               statusTone: 'live'    },
    { id: 'ET-003', name: 'Yirgacheffe G1',       origin: 'Ethiopia', type: 'Arabica', price: 6.40, change: '-0.5%', up: false, qty: '2,800 kg',  demand: 'High',   status: ['Verified','Premium'],      statusTone: 'live'    },
    { id: 'RW-004', name: 'Kivu Crest Bourbon',   origin: 'Rwanda',   type: 'Arabica', price: 5.05, change: '+2.1%', up: true,  qty: '3,600 kg',  demand: 'Medium', status: ['Verified','Tokenized'],    statusTone: 'pending' },
    { id: 'BR-005', name: 'Minas Gerais Fazenda', origin: 'Brazil',   type: 'Arabica', price: 3.20, change: '-1.1%', up: false, qty: '20,000 kg', demand: 'Stable', status: ['Verified'],               statusTone: 'pending' },
];

const displayLots = computed(() => {
    if (props.lots.length) {
        return props.lots.map(l => ({
            id:         l.lot_code || `LOT-${l.id}`,
            name:       l.name,
            origin:     l.origin,
            type:       l.type,
            price:      l.price_per_kg,
            change:     '—',
            up:         true,
            qty:        `${Number(l.quantity).toLocaleString()} kg`,
            demand:     l.demand || '—',
            status:     Array.isArray(l.badges) ? l.badges : [],
            statusTone: l.status,
        }));
    }
    return mockLots;
});

const selectedLot = ref(displayLots.value[0]);
const selectLot   = (lot) => { selectedLot.value = lot; };

/* ── Recent trades ─────────────────────────────────────────────── */
const recentTrades = [
    { lot: 'Mount Elgon AA',       side: 'Buy',  qty: '500 kg',   price: '$5.20/kg', time: '2m ago',  tone: 'success' },
    { lot: 'Kyoga Robusta R1',     side: 'Sell', qty: '2,000 kg', price: '$1.88/kg', time: '5m ago',  tone: 'danger'  },
    { lot: 'Yirgacheffe G1',       side: 'Buy',  qty: '800 kg',   price: '$6.40/kg', time: '9m ago',  tone: 'success' },
    { lot: 'Minas Gerais Fazenda', side: 'Buy',  qty: '5,000 kg', price: '$3.20/kg', time: '14m ago', tone: 'success' },
    { lot: 'Kivu Crest Bourbon',   side: 'Sell', qty: '1,200 kg', price: '$5.05/kg', time: '21m ago', tone: 'danger'  },
];

/* ── Quick trade ───────────────────────────────────────────────── */
const qtyInput   = ref(1000);
const priceInput = ref(selectedLot.value.price);
const subtotal   = computed(() => (qtyInput.value * priceInput.value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
const fee        = computed(() => (qtyInput.value * priceInput.value * 0.005).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
const totalPay   = computed(() => (qtyInput.value * priceInput.value * 1.005).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

/* ── Smart alerts ──────────────────────────────────────────────── */
const alerts = ref([
    { label: 'Price Target Hit', on: true  },
    { label: 'Demand Spike',     on: true  },
    { label: 'New Lot Listed',   on: false },
    { label: 'Supply Change',    on: false },
]);

/* ── Demand heatmap ────────────────────────────────────────────── */
const heatmap = [
    { market: 'UAE',          demand: 'Extreme', coffee: 'Robusta',   score: 96, tone: 'danger'  },
    { market: 'Germany',      demand: 'High',    coffee: 'Arabica',   score: 82, tone: 'warning' },
    { market: 'USA',          demand: 'Medium',  coffee: 'Micro-lot', score: 71, tone: 'info'    },
    { market: 'Saudi Arabia', demand: 'High',    coffee: 'Specialty', score: 78, tone: 'warning' },
];

/* ── Opportunities ─────────────────────────────────────────────── */
const opportunities = [
    { route: 'Uganda Robusta',   market: 'UAE',     priceRange: '$2.70–3.10', demand: 'Extreme', risk: 'Low'    },
    { route: 'Rwenzori Arabica', market: 'Germany', priceRange: '$4.40–4.80', demand: 'High',    risk: 'Medium' },
    { route: 'Mount Elgon AA',   market: 'USA',     priceRange: '$4.90–5.30', demand: 'Medium',  risk: 'Low'    },
];

/* ── Market news ───────────────────────────────────────────────── */
const news = [
    { title: 'Frost warnings in Minas Gerais',  impact: 'High',   action: 'Secure Arabica lots now',       tone: 'danger'  },
    { title: 'EUDR compliance deadline Q4',      impact: 'Medium', action: 'Verify traceability documents', tone: 'warning' },
    { title: 'UAE import quota expanded 15%',    impact: 'High',   action: 'List Robusta for UAE market',   tone: 'success' },
    { title: 'UGX weakens against USD',          impact: 'Low',    action: 'Monitor exchange rate impact',  tone: 'info'    },
];

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: 'Hi! I\'m your Bean Origin Market Advisor. Robusta lots are trending upward — want to see the best opportunities right now?' },
]);
const prompts = ['Which coffee is trending?', 'Should I buy now?', 'Best price today?', 'Which market has demand?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'Based on current market data, Uganda Robusta offers the best opportunity with UAE demand at 96% and prices trending +4.2% today.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };

const badgeClass = (b) => {
    if (b === 'Verified')     return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (b === 'Export Ready') return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (b === 'Tokenized')    return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    if (b === 'Premium')      return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    return 'bg-light text-secondary border';
};
</script>

<template>
    <OuterLayout title="Live Coffee Market">
        <div class="lm-page">

            <!-- ① Sticky Header ─────────────────────────────────────── -->
            <div class="lm-header">
                <div class="container-fluid px-3 px-lg-4">

                    <!-- Title row -->
                    <div class="d-flex align-items-center justify-content-between gap-3 pt-3 pb-2 flex-wrap">
                        <div>
                            <div class="lm-kicker">Real-Time Exchange · Bean Origin</div>
                            <h1 class="lm-title mb-0">Live Coffee Market</h1>
                            <p class="lm-subtitle mb-0">Real-time prices, demand signals, and trading activity</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button class="btn lm-btn-buy btn-sm">
                                <el-icon><ShoppingCart /></el-icon> Buy Coffee
                            </button>
                            <Link :href="route('seller.sell-coffee')" class="btn lm-btn-sell btn-sm">
                                <el-icon><Share /></el-icon> Sell Coffee
                            </Link>
                            <button class="btn lm-btn-ghost btn-sm">
                                <el-icon><Bell /></el-icon> Set Alert
                            </button>
                            <button class="btn lm-btn-ghost btn-sm">
                                <el-icon><ChatDotRound /></el-icon> Ask Advisor
                            </button>
                        </div>
                    </div>

                    <!-- Filter bar -->
                    <div class="d-flex flex-wrap align-items-center gap-2 pb-2 border-top pt-2">
                        <el-icon class="lm-filter-icon"><Filter /></el-icon>
                        <select v-model="filters.type"   class="form-select form-select-sm lm-select"><option value="">All Types</option><option>Arabica</option><option>Robusta</option><option>Specialty</option></select>
                        <select v-model="filters.origin" class="form-select form-select-sm lm-select"><option value="">All Origins</option><option>Uganda</option><option>Ethiopia</option><option>Brazil</option></select>
                        <select v-model="filters.market" class="form-select form-select-sm lm-select"><option value="">All Markets</option><option>UAE</option><option>Germany</option><option>USA</option></select>
                        <select v-model="filters.quality" class="form-select form-select-sm lm-select"><option value="">Quality Score</option><option value="80">80+</option><option value="85">85+</option><option value="88">88+</option></select>
                        <div class="form-check form-check-inline mb-0 ms-1">
                            <input v-model="filters.exportReady" class="form-check-input" type="checkbox" id="fExport">
                            <label class="form-check-label lm-check-label" for="fExport">Export Ready</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input v-model="filters.tokenized" class="form-check-input" type="checkbox" id="fToken">
                            <label class="form-check-label lm-check-label" for="fToken">Tokenized</label>
                        </div>
                        <div class="btn-group btn-group-sm ms-auto">
                            <button v-for="tf in ['1H','24H','7D','30D']" :key="tf"
                                class="btn" :class="timeframe === tf ? 'lm-btn-active-tf' : 'lm-btn-tf'"
                                @click="timeframe = tf">{{ tf }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ② Market Status Strip ──────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-sm-4 col-xl-2">
                        <div class="lm-kpi" :class="`lm-kpi--${kpi.tone}`">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="lm-kpi__label">{{ kpi.label }}</span>
                                <span class="lm-kpi__icon">
                                    <el-icon><component :is="kpi.icon" /></el-icon>
                                </span>
                            </div>
                            <div class="lm-kpi__value">{{ kpi.value }}</div>
                            <div class="lm-kpi__sub">{{ kpi.sub }}</div>
                        </div>
                    </div>
                </div>

                <!-- ③ Live Ticker ──────────────────────────────────── -->
                <div class="lm-ticker mb-3">
                    <div class="lm-ticker__inner">
                        <div class="lm-ticker__track">
                            <div v-for="n in 3" :key="n" class="lm-ticker__group">
                                <span v-for="item in ticker" :key="`${n}-${item.name}`" class="lm-ticker__item">
                                    <span class="lm-ticker__dot" :class="item.up ? 'up' : 'down'"></span>
                                    <strong>{{ item.name }}</strong>
                                    <span class="lm-ticker__price">{{ item.price }}</span>
                                    <span :class="item.up ? 'lm-up' : 'lm-down'">{{ item.change }}</span>
                                    <span class="lm-ticker__sep">|</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ④ Main 2-column grid ──────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column ─────────────────────────────────── -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- A. Price Chart -->
                            <div class="lm-card">
                                <div class="lm-card__head">
                                    <div>
                                        <div class="lm-card__title">
                                            <el-icon class="lm-card__icon"><TrendCharts /></el-icon>
                                            Price &amp; Trend Chart
                                        </div>
                                        <div class="lm-card__sub">Live candlestick · volume · demand overlay</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="btn-group btn-group-sm">
                                            <button v-for="tab in ['Price','Volume','Demand','Supply']" :key="tab"
                                                class="btn" :class="chartTab === tab ? 'lm-btn-active-tf' : 'lm-btn-tf'"
                                                @click="chartTab = tab">{{ tab }}</button>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button v-for="tf in ['1H','24H','7D','30D']" :key="tf"
                                                class="btn" :class="timeframe === tf ? 'lm-btn-active-tf' : 'lm-btn-ghost'"
                                                @click="timeframe = tf">{{ tf }}</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart area -->
                                <div class="lm-chart">
                                    <!-- SVG line -->
                                    <svg class="lm-chart__svg" viewBox="0 0 600 160" preserveAspectRatio="none">
                                        <defs>
                                            <linearGradient id="lineGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="rgba(0,69,50,0.18)"/>
                                                <stop offset="100%" stop-color="rgba(0,69,50,0)"/>
                                            </linearGradient>
                                        </defs>
                                        <polygon points="0,140 40,120 80,110 120,84 160,96 200,70 240,80 280,56 320,64 360,44 400,60 440,36 480,50 520,30 560,40 600,24 600,160 0,160"
                                            fill="url(#lineGrad)" />
                                        <polyline points="0,140 40,120 80,110 120,84 160,96 200,70 240,80 280,56 320,64 360,44 400,60 440,36 480,50 520,30 560,40 600,24"
                                            fill="none" stroke="#004532" stroke-width="2.5" stroke-linejoin="round"/>
                                        <!-- Grid lines -->
                                        <line x1="0" y1="40" x2="600" y2="40" stroke="#e5e7eb" stroke-width="1"/>
                                        <line x1="0" y1="80" x2="600" y2="80" stroke="#e5e7eb" stroke-width="1"/>
                                        <line x1="0" y1="120" x2="600" y2="120" stroke="#e5e7eb" stroke-width="1"/>
                                    </svg>
                                    <!-- Candles overlay -->
                                    <div class="lm-chart__candles">
                                        <div v-for="(c, i) in candles" :key="i"
                                            class="lm-candle" :class="`lm-candle--${c.dir}`"
                                            :style="{ height: `${c.h}%` }"></div>
                                    </div>
                                    <!-- Spot price badge -->
                                    <div class="lm-chart__spot">
                                        <div class="lm-chart__spot-label">Spot</div>
                                        <div class="lm-chart__spot-price">$3.82</div>
                                        <div class="lm-up" style="font-size:11px;font-weight:700;">+2.4%</div>
                                    </div>
                                </div>

                                <!-- Volume bars -->
                                <div class="lm-vol-wrap">
                                    <div v-for="(v, i) in volumes" :key="i" class="lm-vol-bar" :style="{ height: `${v}%` }"></div>
                                </div>

                                <!-- AI note -->
                                <div class="lm-ai-note">
                                    <el-icon><Opportunity /></el-icon>
                                    <span>Robusta prices trending upward due to increased UAE demand — opportunity window open.</span>
                                </div>
                            </div>

                            <!-- B. Live Coffee Lots Table -->
                            <div class="lm-card lm-card--flush">
                                <div class="lm-card__head lm-card__head--border">
                                    <div class="lm-card__title">
                                        <el-icon class="lm-card__icon"><CollectionTag /></el-icon>
                                        Live Coffee Lots
                                    </div>
                                    <span class="lm-live-badge">● {{ displayLots.length }} Active</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 lm-table">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Origin</th>
                                                <th>Type</th>
                                                <th>Price/kg</th>
                                                <th>24h</th>
                                                <th>Quantity</th>
                                                <th>Demand</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in displayLots" :key="lot.id"
                                                class="lm-tr"
                                                :class="{ 'lm-tr--selected': selectedLot.id === lot.id }"
                                                @click="selectLot(lot)">
                                                <td>
                                                    <div class="lm-lot-name">{{ lot.name }}</div>
                                                    <div class="lm-lot-id">{{ lot.id }}</div>
                                                </td>
                                                <td class="lm-td-muted">{{ lot.origin }}</td>
                                                <td>
                                                    <span class="lm-type-pill" :class="lot.type === 'Arabica' ? 'lm-type-pill--amber' : 'lm-type-pill--blue'">
                                                        {{ lot.type }}
                                                    </span>
                                                </td>
                                                <td class="lm-td-price">${{ lot.price.toFixed(2) }}</td>
                                                <td>
                                                    <span :class="lot.up ? 'lm-up' : 'lm-down'" style="font-size:.75rem;font-weight:700;">
                                                        {{ lot.up ? '↑' : '↓' }} {{ lot.change }}
                                                    </span>
                                                </td>
                                                <td class="lm-td-muted">{{ lot.qty }}</td>
                                                <td>
                                                    <span class="lm-demand-pill"
                                                        :class="lot.demand === 'V.High' ? 'lm-demand-pill--hot' :
                                                                lot.demand === 'High'   ? 'lm-demand-pill--high' :
                                                                'lm-demand-pill--med'">
                                                        {{ lot.demand }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <span v-for="s in lot.status" :key="s" class="lm-status-pill" :class="badgeClass(s)">{{ s }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm lm-btn-buy px-2" @click.stop="selectLot(lot)">Buy</button>
                                                        <button class="btn btn-sm lm-btn-ghost px-2"><el-icon><View /></el-icon></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- C. Recent Trades Feed -->
                            <div class="lm-card">
                                <div class="lm-card__title mb-3">
                                    <el-icon class="lm-card__icon"><Clock /></el-icon>
                                    Recent Trades
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <div v-for="t in recentTrades" :key="t.lot + t.time" class="lm-trade">
                                        <span class="lm-trade__side" :class="`lm-trade__side--${t.tone}`">{{ t.side }}</span>
                                        <span class="lm-trade__name">{{ t.lot }}</span>
                                        <span class="lm-trade__qty">{{ t.qty }}</span>
                                        <span class="lm-trade__price" :class="t.tone === 'success' ? 'lm-up' : 'lm-down'">{{ t.price }}</span>
                                        <span class="lm-trade__time">{{ t.time }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right column ────────────────────────────────── -->
                    <div class="col-12 col-xxl-4">
                        <div class="lm-rail">

                            <!-- A. Quick Trade -->
                            <div class="lm-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="lm-card__title">
                                        <el-icon class="lm-card__icon"><ShoppingCart /></el-icon>
                                        Quick {{ tradeMode }}
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button v-for="m in ['Buy','Sell']" :key="m"
                                            class="btn" :class="tradeMode === m ? (m === 'Buy' ? 'lm-btn-buy' : 'lm-btn-sell') : 'lm-btn-tf'"
                                            @click="tradeMode = m">{{ m }}</button>
                                    </div>
                                </div>

                                <!-- Selected lot -->
                                <div class="lm-selected">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <strong class="lm-selected__name">{{ selectedLot.name }}</strong>
                                        <span class="lm-live-badge" style="font-size:.6rem;">Selected</span>
                                    </div>
                                    <div class="lm-selected__meta">{{ selectedLot.origin }} · {{ selectedLot.type }}</div>
                                    <div class="row g-1 mt-2">
                                        <div class="col-6"><div class="lm-detail-cell"><span>Price/kg</span><strong>${{ selectedLot.price.toFixed(2) }}</strong></div></div>
                                        <div class="col-6"><div class="lm-detail-cell"><span>Available</span><strong>{{ selectedLot.qty }}</strong></div></div>
                                        <div class="col-6"><div class="lm-detail-cell"><span>24h</span><strong :class="selectedLot.up ? 'lm-up' : 'lm-down'">{{ selectedLot.change }}</strong></div></div>
                                        <div class="col-6"><div class="lm-detail-cell"><span>Demand</span><strong>{{ selectedLot.demand }}</strong></div></div>
                                    </div>
                                </div>

                                <div class="mt-3 d-flex flex-column gap-2">
                                    <div>
                                        <label class="lm-field-label">Quantity (kg)</label>
                                        <input v-model="qtyInput" type="number" class="form-control form-control-sm lm-input" placeholder="e.g. 1000">
                                    </div>
                                    <div>
                                        <label class="lm-field-label">Price per kg ($)</label>
                                        <input v-model="priceInput" type="number" step="0.01" class="form-control form-control-sm lm-input">
                                    </div>
                                    <div>
                                        <label class="lm-field-label">Order Type</label>
                                        <select class="form-select form-select-sm lm-input">
                                            <option>Market Buy</option>
                                            <option>Limit Order</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Totals -->
                                <div class="lm-totals mt-3">
                                    <div class="lm-totals__row"><span>Subtotal</span><strong>${{ subtotal }}</strong></div>
                                    <div class="lm-totals__row"><span>Platform fee (0.5%)</span><strong>${{ fee }}</strong></div>
                                    <div class="lm-totals__row lm-totals__row--final"><span>Total Payable</span><strong>${{ totalPay }}</strong></div>
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-sm" :class="tradeMode === 'Buy' ? 'lm-btn-buy' : 'lm-btn-sell'">
                                        {{ tradeMode }} Now
                                    </button>
                                    <button class="btn lm-btn-outline btn-sm">Place Limit Order</button>
                                    <button class="btn lm-btn-ghost btn-sm">
                                        <el-icon><Document /></el-icon> Request Sample
                                    </button>
                                </div>

                                <!-- Trust badges -->
                                <div class="lm-trust-row mt-3">
                                    <div class="lm-trust-item"><el-icon><Checked /></el-icon> Verified Lot</div>
                                    <div class="lm-trust-item"><el-icon><Van /></el-icon> Export Ready</div>
                                    <div class="lm-trust-item"><el-icon><Check /></el-icon> Secure Pay</div>
                                    <div class="lm-trust-item"><el-icon><Connection /></el-icon> Blockchain</div>
                                </div>
                            </div>

                            <!-- B. AI Trade Signal -->
                            <div class="lm-signal-card">
                                <div class="lm-signal-card__head">
                                    <div class="lm-signal-card__title">
                                        <el-icon><Opportunity /></el-icon> AI Trade Signal
                                    </div>
                                    <span class="lm-signal-card__live">● Live</span>
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="lm-signal-badge">BUY</div>
                                    <div>
                                        <div class="lm-signal-conf">92% Confidence</div>
                                        <div class="lm-signal-meta">Entry: $3.85/kg · Risk: Low</div>
                                    </div>
                                </div>
                                <p class="lm-signal-note">UAE Robusta demand at record levels. Supply tight. Price likely to rise 6–8% in 48h.</p>
                            </div>

                            <!-- C. Smart Alerts -->
                            <div class="lm-card">
                                <div class="lm-card__title mb-3">
                                    <el-icon class="lm-card__icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <div v-for="alert in alerts" :key="alert.label" class="lm-alert-row">
                                        <span>{{ alert.label }}</span>
                                        <button class="lm-toggle" :class="{ 'lm-toggle--on': alert.on }" @click="alert.on = !alert.on"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ⑤ Demand Heatmap ──────────────────────────────── -->
                <div class="lm-card mt-3">
                    <div class="lm-card__title mb-3">
                        <el-icon class="lm-card__icon"><Location /></el-icon>
                        Demand Heatmap
                    </div>
                    <div class="row g-2">
                        <div v-for="h in heatmap" :key="h.market" class="col-6 col-md-3">
                            <div class="lm-heat" :class="`lm-heat--${h.tone}`">
                                <div class="lm-heat__market">{{ h.market }}</div>
                                <div class="lm-heat__demand">{{ h.demand }}</div>
                                <div class="lm-heat__coffee">{{ h.coffee }}</div>
                                <div class="lm-heat__bar-wrap">
                                    <div class="lm-heat__bar" :style="{ width: `${h.score}%` }"></div>
                                </div>
                                <div class="lm-heat__score">Score {{ h.score }}/100</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ⑥ Market Opportunities ──────────────────────── -->
                <div class="mt-3">
                    <div class="lm-section-title mb-2">
                        <el-icon class="lm-card__icon"><Medal /></el-icon>
                        Market Opportunities
                    </div>
                    <div class="row g-2">
                        <div v-for="opp in opportunities" :key="opp.route" class="col-12 col-md-4">
                            <div class="lm-opp h-100">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div>
                                        <div class="lm-opp__route">{{ opp.route }}</div>
                                        <div class="lm-opp__market">→ {{ opp.market }}</div>
                                    </div>
                                    <span class="lm-risk-badge" :class="opp.risk === 'Low' ? 'lm-risk-badge--low' : 'lm-risk-badge--med'">
                                        {{ opp.risk }} Risk
                                    </span>
                                </div>
                                <div class="row g-1 mb-3">
                                    <div class="col-6"><div class="lm-detail-cell"><span>Price Range</span><strong>{{ opp.priceRange }}</strong></div></div>
                                    <div class="col-6"><div class="lm-detail-cell"><span>Demand</span><strong>{{ opp.demand }}</strong></div></div>
                                </div>
                                <button class="btn lm-btn-buy btn-sm w-100">Trade Now</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ⑦ Market News ──────────────────────────────────── -->
                <div class="mt-3">
                    <div class="lm-section-title mb-2">
                        <el-icon class="lm-card__icon"><Reading /></el-icon>
                        Market News &amp; Alerts
                    </div>
                    <div class="row g-2">
                        <div v-for="item in news" :key="item.title" class="col-12 col-sm-6 col-xl-3">
                            <div class="lm-news h-100" :class="`lm-news--${item.tone}`">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <el-icon class="lm-news__icon">
                                        <WarnTriangleFilled v-if="item.tone === 'danger' || item.tone === 'warning'" />
                                        <Check v-else-if="item.tone === 'success'" />
                                        <Message v-else />
                                    </el-icon>
                                    <div>
                                        <div class="lm-news__title">{{ item.title }}</div>
                                        <span class="lm-news__impact">{{ item.impact }} Impact</span>
                                    </div>
                                </div>
                                <div class="lm-news__action">{{ item.action }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pb-5"></div>
            </div>

            <!-- ⑧ Floating Chatbot ────────────────────────────────── -->
            <div class="lm-fab-wrap">
                <Transition name="lm-chat">
                    <div v-if="chatOpen" class="lm-chatbot">
                        <div class="lm-chatbot__head">
                            <div class="d-flex align-items-center gap-2">
                                <div class="lm-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="lm-chatbot__name">Market Advisor</div>
                                    <div class="lm-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="lm-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="lm-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="lm-chat-msg" :class="`lm-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="lm-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="lm-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="lm-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask about the market…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="lm-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </OuterLayout>
</template>

<style scoped>
/* ── Tokens ─────────────────────────────────────────────────────── */
.lm-page {
    --green:     #004532;
    --green-d:   #065f46;
    --on-green:  #ffffff;
    --surface:   #ffffff;
    --surface-l: #f8fafc;
    --surface-m: #f1f5f9;
    --border:    #e5e7eb;
    --border-m:  #d1d5db;
    --text:      #111827;
    --muted:     #6b7280;
    --buy:       #16a34a;
    --sell:      #dc2626;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface);
    color: var(--text);
    min-height: 100vh;
}

/* ── Header ─────────────────────────────────────────────────────── */
.lm-header {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 100;
}
.lm-kicker  { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .14em; color: var(--green); margin-bottom: 2px; }
.lm-title   { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.lm-subtitle { font-size: .8125rem; color: var(--muted); }
.lm-filter-icon { font-size: 14px; color: var(--muted); }
.lm-select { width: auto; min-width: 110px; font-size: .8125rem; border-color: var(--border); border-radius: 6px; height: 30px; padding-block: 0; }
.lm-check-label { font-size: .8125rem; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.lm-btn-buy {
    background: var(--buy); border-color: var(--buy); color: #fff;
    border-radius: 6px; font-size: .8125rem; font-weight: 700;
    display: inline-flex; align-items: center; gap: 5px;
}
.lm-btn-buy:hover, .lm-btn-buy:focus { background: #15803d; border-color: #15803d; color: #fff; }
.lm-btn-sell {
    background: var(--sell); border-color: var(--sell); color: #fff;
    border-radius: 6px; font-size: .8125rem; font-weight: 700;
    display: inline-flex; align-items: center; gap: 5px;
}
.lm-btn-sell:hover { background: #b91c1c; border-color: #b91c1c; color: #fff; }
.lm-btn-outline {
    background: var(--surface); border-color: var(--border); color: var(--text);
    border-radius: 6px; font-size: .8125rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.lm-btn-outline:hover { background: var(--surface-l); }
.lm-btn-ghost {
    background: var(--surface-m); border-color: transparent; color: var(--text);
    border-radius: 6px; font-size: .8125rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.lm-btn-ghost:hover { background: var(--border); }
.lm-btn-tf { background: var(--surface); border-color: var(--border); color: var(--muted); font-size: .8125rem; font-weight: 600; }
.lm-btn-tf:hover { background: var(--surface-l); color: var(--text); }
.lm-btn-active-tf { background: var(--green); border-color: var(--green); color: #fff; font-size: .8125rem; font-weight: 700; }

/* ── KPI Strip ───────────────────────────────────────────────────── */
.lm-kpi {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 10px; padding: .75rem;
    border-top-width: 3px;
}
.lm-kpi--success { border-top-color: #16a34a; }
.lm-kpi--primary { border-top-color: var(--green); }
.lm-kpi--info    { border-top-color: #2563eb; }
.lm-kpi--warning { border-top-color: #d97706; }
.lm-kpi__label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
.lm-kpi__icon  { width: 22px; height: 22px; border-radius: 5px; background: var(--surface-l); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; color: var(--muted); }
.lm-kpi__value { font-size: 1.0625rem; font-weight: 800; color: var(--text); line-height: 1; margin: 4px 0 2px; }
.lm-kpi--success .lm-kpi__value { color: #166534; }
.lm-kpi--primary .lm-kpi__value { color: var(--green); }
.lm-kpi__sub   { font-size: .6rem; color: var(--muted); }

/* ── Ticker ─────────────────────────────────────────────────────── */
.lm-ticker { background: #0f172a; border-radius: 8px; overflow: hidden; }
.lm-ticker__inner { overflow: hidden; }
.lm-ticker__track { display: flex; width: max-content; animation: lm-scroll 40s linear infinite; }
.lm-ticker__group { display: flex; align-items: center; }
.lm-ticker__item  { display: inline-flex; align-items: center; gap: 8px; padding: 0 20px; min-height: 34px; font-size: .75rem; color: #94a3b8; white-space: nowrap; }
.lm-ticker__item strong { color: #f1f5f9; font-weight: 700; }
.lm-ticker__price { color: #cbd5e1; }
.lm-ticker__dot   { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.lm-ticker__dot.up   { background: #4ade80; }
.lm-ticker__dot.down { background: #f87171; }
.lm-ticker__sep { opacity: .25; margin: 0 4px; }
.lm-up   { color: #22c55e; font-weight: 700; }
.lm-down { color: #ef4444; font-weight: 700; }
@keyframes lm-scroll { from { transform: translateX(0); } to { transform: translateX(-33.333%); } }

/* ── Cards ───────────────────────────────────────────────────────── */
.lm-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 12px; padding: 1rem;
}
.lm-card--flush { padding: 0; }
.lm-card__head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 12px; flex-wrap: wrap; margin-bottom: 1rem;
}
.lm-card__head--border {
    padding: .625rem 1rem; border-bottom: 1px solid var(--border);
    margin-bottom: 0; background: var(--surface-l); border-radius: 11px 11px 0 0;
}
.lm-card__title {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: .9375rem; font-weight: 700; color: var(--text);
}
.lm-card__icon {
    width: 24px; height: 24px; border-radius: 6px;
    background: rgba(0,69,50,.08); color: var(--green);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
}
.lm-card__sub { font-size: .75rem; color: var(--muted); margin-top: 1px; }
.lm-live-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .65rem; font-weight: 700; letter-spacing: .04em;
    background: #dcfce7; color: #166534; border: 1px solid #86efac;
    border-radius: 999px; padding: 2px 8px;
}
.lm-section-title {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: .9375rem; font-weight: 700; color: var(--text);
}

/* ── Chart ───────────────────────────────────────────────────────── */
.lm-chart {
    position: relative; height: 180px;
    background: var(--surface-l); border-radius: 8px;
    overflow: hidden; margin-bottom: .5rem;
}
.lm-chart__svg { position: absolute; inset: 0; width: 100%; height: 100%; }
.lm-chart__candles {
    position: absolute; inset: 12px;
    display: flex; align-items: flex-end; gap: 5px;
    opacity: .35;
}
.lm-candle { flex: 1; border-radius: 2px 2px 0 0; min-height: 6px; }
.lm-candle--up   { background: #22c55e; }
.lm-candle--down { background: #ef4444; }
.lm-chart__spot {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,.95); border-radius: 8px;
    padding: 8px 12px; text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,.1);
    border: 1px solid var(--border);
}
.lm-chart__spot-label { font-size: .55rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); }
.lm-chart__spot-price { font-size: 1.125rem; font-weight: 800; color: var(--text); line-height: 1.1; }
.lm-vol-wrap { display: flex; align-items: flex-end; gap: 4px; height: 32px; margin-bottom: .5rem; }
.lm-vol-bar  { flex: 1; background: rgba(0,69,50,.18); border-radius: 2px 2px 0 0; }
.lm-ai-note {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 10px; border-radius: 7px;
    background: rgba(0,69,50,.06); border: 1px solid rgba(0,69,50,.12);
    font-size: .8125rem; color: var(--green); font-weight: 600;
}

/* ── Table ───────────────────────────────────────────────────────── */
.lm-table thead th {
    background: var(--surface-l); font-size: .6rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em; color: var(--muted);
    padding: 8px 12px; white-space: nowrap;
    border-bottom-color: var(--border);
}
.lm-table tbody td { padding: 9px 12px; font-size: .8125rem; border-color: var(--surface-m); vertical-align: middle; }
.lm-tr { cursor: pointer; transition: background .1s; }
.lm-tr:hover td { background: var(--surface-l); }
.lm-tr--selected td { background: #f0fdf4 !important; }
.lm-lot-name { font-size: .8125rem; font-weight: 600; }
.lm-lot-id   { font-size: .65rem; color: var(--muted); font-family: monospace; }
.lm-td-muted { color: var(--muted); }
.lm-td-price { font-weight: 800; font-size: .875rem; }
.lm-type-pill { display: inline-flex; align-items: center; border-radius: 5px; font-size: .65rem; font-weight: 700; padding: 2px 7px; letter-spacing: .04em; }
.lm-type-pill--amber { background: #fef3c7; color: #92400e; }
.lm-type-pill--blue  { background: #dbeafe; color: #1e40af; }
.lm-demand-pill { display: inline-flex; align-items: center; border-radius: 999px; font-size: .65rem; font-weight: 700; padding: 2px 8px; }
.lm-demand-pill--hot  { background: #fee2e2; color: #b91c1c; }
.lm-demand-pill--high { background: #dcfce7; color: #166534; }
.lm-demand-pill--med  { background: var(--surface-m); color: var(--muted); }
.lm-status-pill { display: inline-flex; align-items: center; border-radius: 999px; font-size: .6rem; padding: 2px 7px; font-weight: 700; }

/* ── Trades ──────────────────────────────────────────────────────── */
.lm-trade {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 0; border-bottom: 1px solid var(--surface-m);
    font-size: .8125rem;
}
.lm-trade:last-child { border-bottom: none; }
.lm-trade__side { display: inline-flex; border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 8px; flex-shrink: 0; text-transform: uppercase; letter-spacing: .04em; }
.lm-trade__side--success { background: #dcfce7; color: #166534; }
.lm-trade__side--danger  { background: #fee2e2; color: #b91c1c; }
.lm-trade__name  { font-weight: 600; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lm-trade__qty   { font-size: .75rem; color: var(--muted); white-space: nowrap; }
.lm-trade__price { font-weight: 700; white-space: nowrap; }
.lm-trade__time  { font-size: .65rem; color: var(--muted); white-space: nowrap; }

/* ── Rail ────────────────────────────────────────────────────────── */
.lm-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 120px; }
@media (max-width: 1399.98px) { .lm-rail { position: static; } }

/* ── Quick Trade ─────────────────────────────────────────────────── */
.lm-selected {
    background: var(--surface-l); border-radius: 8px;
    padding: 10px; border: 1px solid var(--border);
}
.lm-selected__name { font-size: .9375rem; font-weight: 700; }
.lm-selected__meta { font-size: .75rem; color: var(--muted); margin-top: 2px; }
.lm-detail-cell { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: 6px 8px; }
.lm-detail-cell span   { font-size: .55rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); display: block; margin-bottom: 2px; }
.lm-detail-cell strong { font-size: .875rem; font-weight: 700; color: var(--text); }
.lm-field-label { font-size: .75rem; font-weight: 600; color: var(--muted); display: block; margin-bottom: 4px; }
.lm-input { font-size: .8125rem; border-color: var(--border); border-radius: 6px; }
.lm-input:focus { border-color: var(--green); box-shadow: 0 0 0 2px rgba(0,69,50,.1); outline: none; }
.lm-totals { background: var(--surface-l); border-radius: 8px; padding: 10px; border: 1px solid var(--border); }
.lm-totals__row { display: flex; align-items: center; justify-content: space-between; padding: 4px 0; font-size: .8125rem; }
.lm-totals__row span   { color: var(--muted); }
.lm-totals__row strong { font-weight: 700; }
.lm-totals__row--final { border-top: 1px solid var(--border); margin-top: 4px; padding-top: 8px; }
.lm-totals__row--final span, .lm-totals__row--final strong { font-size: .9375rem; color: var(--text); }
.lm-trust-row { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.lm-trust-item { display: flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 600; color: var(--muted); background: var(--surface-l); border-radius: 6px; padding: 5px 7px; }

/* ── AI Signal Card ──────────────────────────────────────────────── */
.lm-signal-card {
    background: linear-gradient(140deg, #003326, #004532);
    border-radius: 12px; padding: 1rem; border: none;
}
.lm-signal-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .875rem; }
.lm-signal-card__title { display: flex; align-items: center; gap: 6px; font-size: .875rem; font-weight: 700; color: rgba(255,255,255,.8); }
.lm-signal-card__live { font-size: .6rem; font-weight: 700; color: #4ade80; letter-spacing: .06em; text-transform: uppercase; }
.lm-signal-badge {
    width: 56px; height: 56px; border-radius: 10px; flex-shrink: 0;
    background: rgba(255,255,255,.12); display: flex; align-items: center;
    justify-content: center; font-size: 1rem; font-weight: 800;
    letter-spacing: .06em; color: #fff;
}
.lm-signal-conf { font-size: 1rem; font-weight: 800; color: #fff; }
.lm-signal-meta { font-size: .75rem; opacity: .75; color: #fff; margin-top: 2px; }
.lm-signal-note { font-size: .8125rem; line-height: 1.55; margin: 0; color: rgba(255,255,255,.8); }

/* ── Smart Alerts ────────────────────────────────────────────────── */
.lm-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-m); font-size: .8125rem; }
.lm-alert-row:last-child { border-bottom: none; }
.lm-toggle { width: 32px; height: 18px; border-radius: 999px; border: 1px solid var(--border); padding: 2px; background: var(--surface-m); cursor: pointer; transition: background .2s; flex-shrink: 0; }
.lm-toggle i { display: block; width: 12px; height: 12px; border-radius: 50%; background: #fff; transition: transform .2s; }
.lm-toggle--on { background: var(--green); border-color: var(--green); }
.lm-toggle--on i { transform: translateX(14px); }

/* ── Demand Heatmap ──────────────────────────────────────────────── */
.lm-heat {
    border-radius: 10px; padding: 12px;
    border: 1px solid var(--border); background: var(--surface-l);
    border-left-width: 3px;
}
.lm-heat--danger  { border-left-color: #ef4444; }
.lm-heat--warning { border-left-color: #f59e0b; }
.lm-heat--info    { border-left-color: #3b82f6; }
.lm-heat__market { font-size: 1rem; font-weight: 800; }
.lm-heat__demand { font-size: .75rem; font-weight: 700; margin: 2px 0; }
.lm-heat--danger  .lm-heat__demand { color: #b91c1c; }
.lm-heat--warning .lm-heat__demand { color: #92400e; }
.lm-heat--info    .lm-heat__demand { color: #1e40af; }
.lm-heat__coffee { font-size: .6875rem; color: var(--muted); margin-bottom: 8px; }
.lm-heat__bar-wrap { height: 5px; background: var(--border); border-radius: 999px; overflow: hidden; margin-bottom: 4px; }
.lm-heat__bar { height: 100%; border-radius: 999px; background: var(--green); transition: width .5s ease; }
.lm-heat--danger  .lm-heat__bar { background: #ef4444; }
.lm-heat--warning .lm-heat__bar { background: #f59e0b; }
.lm-heat--info    .lm-heat__bar { background: #3b82f6; }
.lm-heat__score { font-size: .6rem; color: var(--muted); font-weight: 600; }

/* ── Opportunities ───────────────────────────────────────────────── */
.lm-opp { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 1rem; }
.lm-opp__route  { font-size: .9375rem; font-weight: 700; }
.lm-opp__market { font-size: .8125rem; color: var(--muted); }
.lm-risk-badge { display: inline-flex; border-radius: 999px; font-size: .65rem; font-weight: 700; padding: 3px 9px; }
.lm-risk-badge--low { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lm-risk-badge--med { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

/* ── News ────────────────────────────────────────────────────────── */
.lm-news { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: .875rem; border-top-width: 3px; }
.lm-news--danger  { border-top-color: #ef4444; }
.lm-news--warning { border-top-color: #f59e0b; }
.lm-news--success { border-top-color: #16a34a; }
.lm-news--info    { border-top-color: #3b82f6; }
.lm-news__icon { font-size: 15px; flex-shrink: 0; margin-top: 2px; }
.lm-news--danger  .lm-news__icon { color: #b91c1c; }
.lm-news--warning .lm-news__icon { color: #92400e; }
.lm-news--success .lm-news__icon { color: #166534; }
.lm-news--info    .lm-news__icon { color: #1e40af; }
.lm-news__title  { font-size: .8125rem; font-weight: 700; line-height: 1.35; margin-bottom: 3px; }
.lm-news__impact { font-size: .6rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
.lm-news__action { font-size: .75rem; color: var(--muted); margin-top: 6px; line-height: 1.5; }

/* ── Chatbot ─────────────────────────────────────────────────────── */
.lm-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: .75rem; }
.lm-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,.35); cursor: pointer; transition: background .15s; }
.lm-fab:hover { background: var(--green-d); }
.lm-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--border); box-shadow: 0 8px 30px rgba(0,0,0,.14); display: flex; flex-direction: column; }
.lm-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.lm-chatbot__avatar { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 15px; }
.lm-chatbot__name { font-size: .875rem; font-weight: 700; }
.lm-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: .625rem; opacity: .8; }
.lm-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.lm-chatbot__close { border: none; background: none; color: rgba(255,255,255,.8); font-size: 20px; line-height: 1; cursor: pointer; }
.lm-chatbot__body { padding: 10px; background: var(--surface-l); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.lm-chat-msg { font-size: .8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 88%; }
.lm-chat-msg--bot  { background: #fff; color: var(--text); border-radius: 10px 10px 10px 2px; border: 1px solid var(--border); }
.lm-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.lm-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--border); }
.lm-prompt-chip { font-size: .6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-l); border: 1px solid var(--border); color: var(--text); cursor: pointer; white-space: nowrap; }
.lm-prompt-chip:hover { background: #dcfce7; color: #166534; }
.lm-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--border); }
.lm-chatbot__input input { flex: 1; border: 1px solid var(--border); border-radius: 7px; padding: 6px 9px; font-size: .8125rem; outline: none; }
.lm-chatbot__input input:focus { border-color: var(--green); }
.lm-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.lm-chat-enter-active, .lm-chat-leave-active { transition: opacity .2s ease, transform .2s ease; }
.lm-chat-enter-from, .lm-chat-leave-to { opacity: 0; transform: translateY(8px); }

@media (max-width: 767.98px) {
    .lm-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
}
</style>
