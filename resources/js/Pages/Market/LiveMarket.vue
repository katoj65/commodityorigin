<script setup>
import { computed, ref } from 'vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, Connection, DataLine, Document,
    Download, Filter, Location, Medal, Message,
    Opportunity, Promotion, Reading, Share, ShoppingCart,
    Star, TrendCharts, Van, View, Warning, WarnTriangleFilled,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

/* ── Filters ───────────────────────────────────────────────────── */
const filters = ref({ type: '', origin: '', market: '', quality: '', exportReady: false, tokenised: false, timeframe: '24H' });
const timeframe = ref('24H');
const chartTab  = ref('Price');
const tradeMode = ref('Buy');

/* ── Ticker ────────────────────────────────────────────────────── */
const ticker = [
    { name: 'Uganda Robusta',    price: '$2.85/kg', change: '+4.2%', up: true },
    { name: 'Rwenzori Arabica',  price: '$4.60/kg', change: '+5.0%', up: true },
    { name: 'Brazil Arabica',    price: '$4.10/kg', change: '-1.1%', up: false },
    { name: 'Vietnam Robusta',   price: '$2.70/kg', change: '+2.8%', up: true },
    { name: 'Kenya AA',          price: '$6.20/kg', change: '+0.5%', up: true },
    { name: 'Colombia Huila',    price: '$5.45/kg', change: '-0.8%', up: false },
];

/* ── KPIs ──────────────────────────────────────────────────────── */
const kpis = [
    { label: 'Market Status',  value: 'Open',   sub: 'Active trading',   tone: 'success', icon: Check },
    { label: 'Avg. Price',     value: '$3.82',   sub: 'Per kg avg',       tone: 'primary', icon: CollectionTag },
    { label: '24h Volume',     value: '1.2M kg', sub: 'Across all lots',  tone: 'info',    icon: Box },
    { label: 'Price Change',   value: '+2.4%',   sub: 'Since yesterday',  tone: 'success', icon: TrendCharts },
    { label: 'Top Demand',     value: 'UAE',     sub: 'Highest activity', tone: 'warning', icon: Location },
    { label: 'Volatility',     value: 'Medium',  sub: 'Market stability', tone: 'warning', icon: Warning },
];

/* ── Chart data ────────────────────────────────────────────────── */
const candles = [
    { h: 42, dir: 'up'   }, { h: 68, dir: 'down' }, { h: 55, dir: 'up'   },
    { h: 82, dir: 'down' }, { h: 47, dir: 'up'   }, { h: 71, dir: 'down' },
    { h: 53, dir: 'up'   }, { h: 88, dir: 'down' }, { h: 36, dir: 'up'   },
    { h: 64, dir: 'down' }, { h: 78, dir: 'up'   }, { h: 91, dir: 'down' },
    { h: 58, dir: 'up'   }, { h: 44, dir: 'down' }, { h: 76, dir: 'up'   },
];
const volumes = [18, 30, 22, 46, 28, 50, 32, 56, 24, 38, 44, 62];

/* ── Lots table ────────────────────────────────────────────────── */
const lots = ref([
    { id: 'UG-001', name: 'Mount Elgon AA',       origin: 'Uganda',   type: 'Arabica', price: 5.20, change: '+1.8%', up: true,  qty: '4,200 kg', demand: 'High',    status: ['Verified','Export Ready'], statusTone: 'live'    },
    { id: 'UG-002', name: 'Kyoga Robusta R1',      origin: 'Uganda',   type: 'Robusta', price: 1.88, change: '+4.2%', up: true,  qty: '12,500 kg', demand: 'V.High',  status: ['Verified'],               statusTone: 'live'    },
    { id: 'ET-003', name: 'Yirgacheffe G1',        origin: 'Ethiopia', type: 'Arabica', price: 6.40, change: '-0.5%', up: false, qty: '2,800 kg',  demand: 'High',    status: ['Verified','Premium'],      statusTone: 'live'    },
    { id: 'RW-004', name: 'Kivu Crest Bourbon',    origin: 'Rwanda',   type: 'Arabica', price: 5.05, change: '+2.1%', up: true,  qty: '3,600 kg',  demand: 'Medium',  status: ['Verified','Tokenised'],    statusTone: 'pending' },
    { id: 'BR-005', name: 'Minas Gerais Fazenda',  origin: 'Brazil',   type: 'Arabica', price: 3.20, change: '-1.1%', up: false, qty: '20,000 kg', demand: 'Stable',  status: ['Verified'],               statusTone: 'pending' },
]);

const selectedLot = ref(lots.value[0]);
const selectLot = (lot) => { selectedLot.value = lot; };

/* ── Recent trades ─────────────────────────────────────────────── */
const recentTrades = [
    { lot: 'Mount Elgon AA',      side: 'Buy',  qty: '500 kg',  price: '$5.20/kg', time: '2m ago',  tone: 'success' },
    { lot: 'Kyoga Robusta R1',    side: 'Sell', qty: '2,000 kg',price: '$1.88/kg', time: '5m ago',  tone: 'danger'  },
    { lot: 'Yirgacheffe G1',      side: 'Buy',  qty: '800 kg',  price: '$6.40/kg', time: '9m ago',  tone: 'success' },
    { lot: 'Minas Gerais Fazenda',side: 'Buy',  qty: '5,000 kg',price: '$3.20/kg', time: '14m ago', tone: 'success' },
    { lot: 'Kivu Crest Bourbon',  side: 'Sell', qty: '1,200 kg',price: '$5.05/kg', time: '21m ago', tone: 'danger'  },
];

/* ── Quick buy ─────────────────────────────────────────────────── */
const qtyInput  = ref(1000);
const priceInput = ref(selectedLot.value.price);
const subtotal   = computed(() => (qtyInput.value * priceInput.value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
const fee        = computed(() => (qtyInput.value * priceInput.value * 0.005).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
const totalPay   = computed(() => (qtyInput.value * priceInput.value * 1.005).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

/* ── Smart alerts ──────────────────────────────────────────────── */
const alerts = ref([
    { label: 'Price Target Hit',  on: true  },
    { label: 'Demand Spike',      on: true  },
    { label: 'New Lot Listed',    on: false },
    { label: 'Supply Change',     on: false },
]);

/* ── Demand heatmap ────────────────────────────────────────────── */
const heatmap = [
    { market: 'UAE',          demand: 'Extreme', coffee: 'Robusta',        score: 96, tone: 'danger'  },
    { market: 'Germany',      demand: 'High',    coffee: 'Arabica',        score: 82, tone: 'warning' },
    { market: 'USA',          demand: 'Medium',  coffee: 'Micro-lot',      score: 71, tone: 'info'    },
    { market: 'Saudi Arabia', demand: 'High',    coffee: 'Specialty',      score: 78, tone: 'warning' },
];

/* ── Opportunities ─────────────────────────────────────────────── */
const opportunities = [
    { route: 'Uganda Robusta',    market: 'UAE',     priceRange: '$2.70–3.10', demand: 'Extreme', risk: 'Low'    },
    { route: 'Rwenzori Arabica',  market: 'Germany', priceRange: '$4.40–4.80', demand: 'High',    risk: 'Medium' },
    { route: 'Mount Elgon AA',    market: 'USA',     priceRange: '$4.90–5.30', demand: 'Medium',  risk: 'Low'    },
];

/* ── Market news ───────────────────────────────────────────────── */
const news = [
    { title: 'Frost warnings in Minas Gerais',    impact: 'High',   action: 'Secure Arabica lots now',        tone: 'danger'  },
    { title: 'EUDR compliance deadline Q4',        impact: 'Medium', action: 'Verify traceability documents',  tone: 'warning' },
    { title: 'UAE import quota expanded 15%',      impact: 'High',   action: 'List Robusta for UAE market',    tone: 'success' },
    { title: 'UGX weakens against USD',            impact: 'Low',    action: 'Monitor exchange rate impact',   tone: 'info'    },
];

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen   = ref(false);
const chatInput  = ref('');
const chatMsgs   = ref([
    { role: 'bot',  text: 'Hi! I\'m your Bean Origin Market Advisor. Robusta lots are trending upward — want to see the best opportunities right now?' },
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
    if (b === 'Tokenised')    return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    if (b === 'Premium')      return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    return 'bg-light text-secondary border';
};
</script>

<template>
    <OuterLayout title="Live Coffee Market">

        <div class="lm-page">

            <!-- ── Sticky Header ─────────────────────────────────────── -->
            <div class="lm-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 border-bottom">
                        <div>
                            <h1 class="lm-title mb-0">Live Coffee Market</h1>
                            <p class="lm-subtitle mb-0">Real-time prices, demand signals, and trade activity</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn lm-btn-green btn-sm"><el-icon><ShoppingCart /></el-icon> Buy Coffee</button>
                            <button class="btn lm-btn-outline btn-sm"><el-icon><Share /></el-icon> Sell Coffee</button>
                            <button class="btn lm-btn-ghost btn-sm"><el-icon><Bell /></el-icon> Set Alert</button>
                            <button class="btn lm-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                    <!-- Filters -->
                    <div class="d-flex flex-wrap align-items-center gap-2 py-2">
                        <el-icon class="text-muted" style="font-size:14px;"><Filter /></el-icon>
                        <select v-model="filters.type" class="form-select form-select-sm lm-select">
                            <option value="">All Types</option>
                            <option>Arabica</option>
                            <option>Robusta</option>
                            <option>Specialty</option>
                        </select>
                        <select v-model="filters.origin" class="form-select form-select-sm lm-select">
                            <option value="">All Origins</option>
                            <option>Uganda</option>
                            <option>Ethiopia</option>
                            <option>Brazil</option>
                        </select>
                        <select v-model="filters.market" class="form-select form-select-sm lm-select">
                            <option value="">All Markets</option>
                            <option>UAE</option>
                            <option>Germany</option>
                            <option>USA</option>
                        </select>
                        <select v-model="filters.quality" class="form-select form-select-sm lm-select">
                            <option value="">Quality Score</option>
                            <option value="80">80+</option>
                            <option value="85">85+</option>
                            <option value="88">88+</option>
                        </select>
                        <div class="form-check form-check-inline mb-0">
                            <input v-model="filters.exportReady" class="form-check-input" type="checkbox" id="fExport">
                            <label class="form-check-label lm-check-label" for="fExport">Export Ready</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input v-model="filters.tokenised" class="form-check-input" type="checkbox" id="fToken">
                            <label class="form-check-label lm-check-label" for="fToken">Tokenised</label>
                        </div>
                        <div class="btn-group btn-group-sm ms-auto">
                            <button v-for="tf in ['1H','24H','7D','30D']" :key="tf"
                                class="btn" :class="timeframe === tf ? 'lm-btn-green' : 'lm-btn-outline'"
                                @click="timeframe = tf">{{ tf }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── Market Pulse Strip ─────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-sm-4 col-lg-2">
                        <div class="lm-kpi-card h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="lm-kpi-label">{{ kpi.label }}</span>
                                <span class="lm-kpi-icon" :class="`lm-kpi-icon--${kpi.tone}`">
                                    <el-icon><component :is="kpi.icon" /></el-icon>
                                </span>
                            </div>
                            <div class="lm-kpi-value" :class="`lm-kpi-value--${kpi.tone}`">{{ kpi.value }}</div>
                            <div class="lm-kpi-sub">{{ kpi.sub }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Live Ticker ────────────────────────────────────── -->
                <div class="lm-ticker mb-3">
                    <div class="lm-ticker__track">
                        <div v-for="n in 2" :key="n" class="lm-ticker__group">
                            <span v-for="item in ticker" :key="`${n}-${item.name}`" class="lm-ticker__item">
                                <span class="lm-ticker__dot" :class="item.up ? 'lm-ticker__dot--up' : 'lm-ticker__dot--down'"></span>
                                <strong>{{ item.name }}</strong>
                                <span>{{ item.price }}</span>
                                <span :class="item.up ? 'lm-up' : 'lm-down'">{{ item.change }}</span>
                                <span class="lm-ticker__sep">·</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ─────────────────────────────── -->
                <div class="row g-3">

                    <!-- ── Left column ────────────────────────────────── -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- A. Price Chart -->
                            <div class="lm-card">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                                    <div>
                                        <div class="lm-card-title">
                                            <el-icon class="lm-card-icon"><TrendCharts /></el-icon>
                                            Price Chart
                                        </div>
                                        <p class="lm-card-sub mb-0">Live candlestick &amp; volume tracking</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="btn-group btn-group-sm">
                                            <button v-for="tab in ['Price','Volume','Demand','Supply']" :key="tab"
                                                class="btn" :class="chartTab === tab ? 'lm-btn-green' : 'lm-btn-outline'"
                                                @click="chartTab = tab">{{ tab }}</button>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button v-for="tf in ['1H','24H','7D','30D']" :key="tf"
                                                class="btn btn-sm" :class="timeframe === tf ? 'lm-btn-green' : 'lm-btn-ghost'"
                                                @click="timeframe = tf">{{ tf }}</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Candlestick chart -->
                                <div class="lm-chart">
                                    <div class="lm-chart__candles">
                                        <div v-for="(c, i) in candles" :key="i"
                                            class="lm-candle" :class="`lm-candle--${c.dir}`"
                                            :style="{ height: `${c.h}%` }">
                                        </div>
                                    </div>
                                    <div class="lm-chart__overlay">
                                        <svg viewBox="0 0 300 80" preserveAspectRatio="none" class="lm-chart__line">
                                            <polyline points="0,70 20,60 40,55 60,42 80,48 100,35 120,40 140,28 160,32 180,22 200,30 220,18 240,25 260,15 280,20 300,12" fill="none" stroke="rgba(0,69,50,0.5)" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    <div class="lm-chart__spot">
                                        <small>Spot Price</small>
                                        <strong>$3.82</strong>
                                        <span class="lm-up">+2.4%</span>
                                    </div>
                                </div>

                                <!-- Volume bars -->
                                <div class="lm-vol-bars">
                                    <div v-for="(v, i) in volumes" :key="i" class="lm-vol-bar" :style="{ height: `${v}%` }"></div>
                                </div>

                                <!-- AI note -->
                                <div class="lm-ai-note mt-2">
                                    <el-icon><Opportunity /></el-icon>
                                    <span>Robusta prices trending upward due to increased UAE demand.</span>
                                </div>
                            </div>

                            <!-- B. Live Coffee Table -->
                            <div class="lm-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="lm-card-title">
                                        <el-icon class="lm-card-icon"><CollectionTag /></el-icon>
                                        Live Coffee Lots
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ lots.length }} Active</span>
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
                                            <tr v-for="lot in lots" :key="lot.id"
                                                class="lm-table-row"
                                                :class="{ 'lm-table-row--selected': selectedLot.id === lot.id }"
                                                @click="selectLot(lot)">
                                                <td>
                                                    <div class="lm-lot-name">{{ lot.name }}</div>
                                                    <div class="lm-lot-id">{{ lot.id }}</div>
                                                </td>
                                                <td class="lm-td-muted">{{ lot.origin }}</td>
                                                <td><span class="lm-type-badge" :class="lot.type === 'Arabica' ? 'lm-type-badge--amber' : 'lm-type-badge--blue'">{{ lot.type }}</span></td>
                                                <td class="lm-td-price">${{ lot.price.toFixed(2) }}</td>
                                                <td><span :class="lot.up ? 'lm-up' : 'lm-down'" style="font-size:.75rem;font-weight:700;">{{ lot.change }}</span></td>
                                                <td class="lm-td-muted">{{ lot.qty }}</td>
                                                <td>
                                                    <span class="badge rounded-pill"
                                                        :class="lot.demand === 'V.High' ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' :
                                                                lot.demand === 'High'   ? 'bg-success-subtle text-success-emphasis border border-success-subtle' :
                                                                'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'"
                                                        style="font-size:.65rem;">{{ lot.demand }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <span v-for="s in lot.status" :key="s" class="badge rounded-pill lm-status-badge" :class="badgeClass(s)">{{ s }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm lm-btn-green px-2" @click.stop="selectLot(lot)"><el-icon><Check /></el-icon></button>
                                                        <button class="btn btn-sm lm-btn-outline px-2"><el-icon><View /></el-icon></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- C. Recent Trades -->
                            <div class="lm-card">
                                <div class="lm-card-title mb-3">
                                    <el-icon class="lm-card-icon"><Clock /></el-icon>
                                    Recent Trades
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="t in recentTrades" :key="t.lot + t.time" class="lm-trade-row">
                                        <span class="lm-trade-badge" :class="`lm-trade-badge--${t.tone}`">{{ t.side }}</span>
                                        <span class="lm-trade-name">{{ t.lot }}</span>
                                        <span class="lm-trade-qty">{{ t.qty }}</span>
                                        <span class="lm-trade-price">{{ t.price }}</span>
                                        <span class="lm-trade-time">{{ t.time }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ── Right column ───────────────────────────────── -->
                    <div class="col-12 col-xxl-4">
                        <div class="lm-rail">

                            <!-- A. Quick Buy -->
                            <div class="lm-card lm-quick-buy">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="lm-card-title">
                                        <el-icon class="lm-card-icon"><ShoppingCart /></el-icon>
                                        Quick {{ tradeMode }}
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button v-for="m in ['Buy','Sell']" :key="m"
                                            class="btn" :class="tradeMode === m ? 'lm-btn-green' : 'lm-btn-outline'"
                                            @click="tradeMode = m">{{ m }}</button>
                                    </div>
                                </div>

                                <!-- Selected lot -->
                                <div class="lm-selected-lot mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <strong class="lm-selected-lot__name">{{ selectedLot.name }}</strong>
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">Selected</span>
                                    </div>
                                    <div class="lm-selected-lot__meta">{{ selectedLot.origin }} · {{ selectedLot.type }}</div>
                                    <div class="row g-1 mt-2">
                                        <div class="col-6"><div class="lm-detail-cell"><span>Price/kg</span><strong>${{ selectedLot.price.toFixed(2) }}</strong></div></div>
                                        <div class="col-6"><div class="lm-detail-cell"><span>Available</span><strong>{{ selectedLot.qty }}</strong></div></div>
                                        <div class="col-6"><div class="lm-detail-cell"><span>24h Change</span><strong :class="selectedLot.up ? 'lm-up' : 'lm-down'">{{ selectedLot.change }}</strong></div></div>
                                        <div class="col-6"><div class="lm-detail-cell"><span>Demand</span><strong>{{ selectedLot.demand }}</strong></div></div>
                                    </div>
                                </div>

                                <!-- Inputs -->
                                <div class="mb-2">
                                    <label class="lm-field-label">Quantity (kg)</label>
                                    <input v-model="qtyInput" type="number" class="form-control form-control-sm lm-input" placeholder="e.g. 1000">
                                </div>
                                <div class="mb-2">
                                    <label class="lm-field-label">Price per kg ($)</label>
                                    <input v-model="priceInput" type="number" step="0.01" class="form-control form-control-sm lm-input">
                                </div>
                                <div class="mb-3">
                                    <label class="lm-field-label">Order Type</label>
                                    <select class="form-select form-select-sm lm-input">
                                        <option>Market Buy</option>
                                        <option>Limit Order</option>
                                    </select>
                                </div>

                                <!-- Totals -->
                                <div class="lm-totals mb-3">
                                    <div class="lm-total-row"><span>Subtotal</span><strong>${{ subtotal }}</strong></div>
                                    <div class="lm-total-row"><span>Platform fee (0.5%)</span><strong>${{ fee }}</strong></div>
                                    <div class="lm-total-row lm-total-row--final"><span>Total Payable</span><strong>${{ totalPay }}</strong></div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button class="btn lm-btn-green btn-sm">{{ tradeMode }} Now</button>
                                    <button class="btn lm-btn-outline btn-sm">Place Limit Order</button>
                                    <button class="btn lm-btn-ghost btn-sm"><el-icon><Document /></el-icon> Request Sample</button>
                                </div>

                                <!-- Trust badges -->
                                <div class="row g-1 mt-2">
                                    <div class="col-6"><div class="lm-trust"><el-icon><Checked /></el-icon> Verified Lot</div></div>
                                    <div class="col-6"><div class="lm-trust"><el-icon><Van /></el-icon> Export Ready</div></div>
                                    <div class="col-6"><div class="lm-trust"><el-icon><Check /></el-icon> Secure Payment</div></div>
                                    <div class="col-6"><div class="lm-trust"><el-icon><Connection /></el-icon> Blockchain</div></div>
                                </div>
                            </div>

                            <!-- B. AI Trade Signal -->
                            <div class="lm-card lm-signal-card">
                                <div class="lm-card-title mb-3">
                                    <el-icon class="lm-card-icon"><Opportunity /></el-icon>
                                    AI Trade Signal
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="lm-signal-badge">BUY</div>
                                    <div>
                                        <div class="lm-signal-conf">92% Confidence</div>
                                        <div class="lm-signal-meta">Suggested: $3.85/kg · Risk: Low</div>
                                    </div>
                                </div>
                                <p class="lm-signal-note">UAE Robusta demand at record levels. Supply tight. Price likely to rise 6–8% in 48h.</p>
                            </div>

                            <!-- C. Smart Alerts -->
                            <div class="lm-card">
                                <div class="lm-card-title mb-3">
                                    <el-icon class="lm-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="alert in alerts" :key="alert.label" class="lm-alert-row">
                                        <span>{{ alert.label }}</span>
                                        <button class="lm-toggle" :class="{ 'lm-toggle--on': alert.on }" @click="alert.on = !alert.on"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ── Demand Heatmap ──────────────────────────────────── -->
                <div class="lm-card mt-3">
                    <div class="lm-card-title mb-3">
                        <el-icon class="lm-card-icon"><Location /></el-icon>
                        Demand Heatmap
                    </div>
                    <div class="row g-2">
                        <div v-for="h in heatmap" :key="h.market" class="col-6 col-md-3">
                            <div class="lm-heat-card" :class="`lm-heat-card--${h.tone}`">
                                <div class="lm-heat-market">{{ h.market }}</div>
                                <div class="lm-heat-demand">{{ h.demand }}</div>
                                <div class="lm-heat-coffee">{{ h.coffee }}</div>
                                <div class="lm-heat-bar-wrap">
                                    <div class="lm-heat-bar" :style="{ width: `${h.score}%` }"></div>
                                </div>
                                <div class="lm-heat-score">Score: {{ h.score }}/100</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Market Opportunities ───────────────────────────── -->
                <div class="mt-3">
                    <div class="lm-card-title mb-2">
                        <el-icon class="lm-card-icon"><Medal /></el-icon>
                        Market Opportunities
                    </div>
                    <div class="row g-2">
                        <div v-for="opp in opportunities" :key="opp.route" class="col-12 col-md-4">
                            <div class="lm-opp-card h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <div class="lm-opp-route">{{ opp.route }}</div>
                                        <div class="lm-opp-market">→ {{ opp.market }}</div>
                                    </div>
                                    <span class="badge" :class="opp.risk === 'Low' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'" style="font-size:.65rem;">{{ opp.risk }} Risk</span>
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-6"><div class="lm-detail-cell"><span>Price Range</span><strong>{{ opp.priceRange }}</strong></div></div>
                                    <div class="col-6"><div class="lm-detail-cell"><span>Demand</span><strong>{{ opp.demand }}</strong></div></div>
                                </div>
                                <button class="btn lm-btn-green btn-sm w-100">Trade Now</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Market News & Alerts ───────────────────────────── -->
                <div class="mt-3">
                    <div class="lm-card-title mb-2">
                        <el-icon class="lm-card-icon"><Reading /></el-icon>
                        Market News &amp; Alerts
                    </div>
                    <div class="row g-2">
                        <div v-for="item in news" :key="item.title" class="col-12 col-sm-6 col-xl-3">
                            <div class="lm-news-card h-100">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <el-icon class="lm-news-icon" :class="`lm-news-icon--${item.tone}`">
                                        <WarnTriangleFilled v-if="item.tone === 'danger' || item.tone === 'warning'" />
                                        <Check v-else-if="item.tone === 'success'" />
                                        <Message v-else />
                                    </el-icon>
                                    <div>
                                        <div class="lm-news-title">{{ item.title }}</div>
                                        <span class="badge rounded-pill" style="font-size:.6rem;"
                                            :class="item.tone === 'danger'  ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' :
                                                    item.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' :
                                                    item.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' :
                                                    'bg-info-subtle text-info-emphasis border border-info-subtle'">{{ item.impact }} Impact</span>
                                    </div>
                                </div>
                                <div class="lm-news-action">{{ item.action }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pb-5"></div>

            </div><!-- /container -->

            <!-- ── Floating Chatbot ───────────────────────────────────── -->
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

        </div><!-- /lm-page -->
    </OuterLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.lm-page {
    --green:          #004532;
    --green-grad:     #065f46;
    --on-green:       #ffffff;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-white:  #ffffff;
    --surface-low:    #f8fafc;
    --surface-mid:    #f1f5f9;
    --surface-high:   #e5e7eb;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
    color: var(--on-surface);
    min-height: 100vh;
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.lm-header {
    background: var(--surface-white);
    border-bottom: 1px solid var(--surface-high);
}
.lm-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; }
.lm-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); }
.lm-select   { width: auto; min-width: 110px; font-size: 0.8125rem; border-color: var(--surface-high); border-radius: 6px; height: 32px; padding-block: 0; }
.lm-check-label { font-size: 0.8125rem; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.lm-btn-green {
    background: var(--green); border-color: var(--green); color: var(--on-green);
    border-radius: 6px; font-size: 0.8125rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.lm-btn-green:hover, .lm-btn-green:focus { background: var(--green-grad); border-color: var(--green-grad); color: var(--on-green); }
.lm-btn-outline {
    background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface);
    border-radius: 6px; font-size: 0.8125rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.lm-btn-outline:hover { background: var(--surface-low); }
.lm-btn-ghost {
    background: var(--surface-mid); border-color: transparent; color: var(--on-surface);
    border-radius: 6px; font-size: 0.8125rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.lm-btn-ghost:hover { background: var(--surface-high); }

/* ── KPI Cards ─────────────────────────────────────────────────────────────── */
.lm-kpi-card {
    background: var(--surface-white); border: 1px solid var(--surface-high);
    border-radius: 10px; padding: 0.75rem;
}
.lm-kpi-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.lm-kpi-icon  { width: 22px; height: 22px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
.lm-kpi-icon--success { background: #dcfce7; color: #166534; }
.lm-kpi-icon--primary { background: #e0f2fe; color: #0369a1; }
.lm-kpi-icon--info    { background: #dbeafe; color: #1d4ed8; }
.lm-kpi-icon--warning { background: #fef3c7; color: #92400e; }
.lm-kpi-value { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.lm-kpi-value--success { color: #166534; }
.lm-kpi-value--primary { color: var(--green); }
.lm-kpi-sub   { font-size: 0.625rem; color: var(--on-surface-var); }

/* ── Ticker ────────────────────────────────────────────────────────────────── */
.lm-ticker { background: #111827; border-radius: 8px; overflow: hidden; }
.lm-ticker__track { display: flex; width: max-content; animation: lm-scroll 30s linear infinite; }
.lm-ticker__group { display: flex; align-items: center; }
.lm-ticker__item  { display: inline-flex; align-items: center; gap: 8px; padding: 0 18px; min-height: 32px; font-size: 0.75rem; color: #d1d5db; white-space: nowrap; }
.lm-ticker__dot   { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.lm-ticker__dot--up   { background: #4ade80; }
.lm-ticker__dot--down { background: #f87171; }
.lm-ticker__item strong { color: #ffffff; font-weight: 700; }
.lm-ticker__sep   { opacity: 0.3; }
.lm-up   { color: #22c55e; font-weight: 700; }
.lm-down { color: #ef4444; font-weight: 700; }
@keyframes lm-scroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.lm-card {
    background: var(--surface-white); border: 1px solid var(--surface-high);
    border-radius: 12px; padding: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.lm-card-title {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 0.9375rem; font-weight: 700; color: var(--on-surface);
}
.lm-card-icon {
    width: 24px; height: 24px; border-radius: 6px;
    background: rgba(0,69,50,0.08); color: var(--green);
    display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
}
.lm-card-sub { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Chart ─────────────────────────────────────────────────────────────────── */
.lm-chart {
    position: relative; height: 180px; background: var(--surface-low);
    border-radius: 8px; overflow: hidden; margin-bottom: 0.5rem;
}
.lm-chart__candles {
    position: absolute; inset: 12px; display: flex; align-items: flex-end; gap: 6px;
}
.lm-candle {
    flex: 1; border-radius: 3px 3px 0 0; min-height: 8px;
    position: relative;
}
.lm-candle--up   { background: rgba(34,197,94,0.6); }
.lm-candle--down { background: rgba(239,68,68,0.6); }
.lm-chart__overlay {
    position: absolute; inset: 0;
}
.lm-chart__line { width: 100%; height: 100%; }
.lm-chart__spot {
    position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,0.96); border-radius: 10px; padding: 8px 12px;
    text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.lm-chart__spot small { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.lm-chart__spot strong { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); display: block; }
.lm-chart__spot span { font-size: 0.75rem; font-weight: 700; }

.lm-vol-bars { display: flex; align-items: flex-end; gap: 4px; height: 36px; }
.lm-vol-bar  { flex: 1; background: rgba(0,69,50,0.2); border-radius: 2px 2px 0 0; }

.lm-ai-note {
    display: flex; align-items: center; gap: 7px;
    padding: 7px 10px; border-radius: 7px; background: rgba(0,69,50,0.06);
    font-size: 0.8125rem; color: var(--green); font-weight: 600;
}

/* ── Table ─────────────────────────────────────────────────────────────────── */
.lm-table thead th {
    background: var(--surface-low); font-size: 0.6875rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var);
    padding: 8px 12px; white-space: nowrap; border-bottom-color: var(--surface-high);
}
.lm-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.lm-table-row { cursor: pointer; transition: background 0.1s; }
.lm-table-row:hover { background: var(--surface-low); }
.lm-table-row--selected { background: #f0fdf4 !important; }
.lm-lot-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.lm-lot-id   { font-size: 0.6875rem; color: var(--on-surface-var); }
.lm-td-muted { color: var(--on-surface-var); }
.lm-td-price { font-weight: 700; }
.lm-type-badge { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.lm-type-badge--amber { background: #fef3c7; color: #92400e; }
.lm-type-badge--blue  { background: #dbeafe; color: #1e40af; }
.lm-status-badge { font-size: 0.6rem; padding: 2px 7px; }

/* ── Recent Trades ─────────────────────────────────────────────────────────── */
.lm-trade-row {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 0; border-bottom: 1px solid var(--surface-low);
    font-size: 0.8125rem;
}
.lm-trade-row:last-child { border-bottom: none; }
.lm-trade-badge { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; flex-shrink: 0; }
.lm-trade-badge--success { background: #dcfce7; color: #166534; }
.lm-trade-badge--danger  { background: #fee2e2; color: #b91c1c; }
.lm-trade-name  { font-weight: 600; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lm-trade-qty   { font-size: 0.75rem; color: var(--on-surface-var); white-space: nowrap; }
.lm-trade-price { font-weight: 700; white-space: nowrap; }
.lm-trade-time  { font-size: 0.6875rem; color: var(--on-surface-var); white-space: nowrap; }

/* ── Right Rail ────────────────────────────────────────────────────────────── */
.lm-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 130px; }

/* ── Quick Buy ─────────────────────────────────────────────────────────────── */
.lm-selected-lot {
    background: var(--surface-low); border-radius: 8px; padding: 10px;
    border: 1px solid var(--surface-high);
}
.lm-selected-lot__name { font-size: 0.9375rem; }
.lm-selected-lot__meta { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }
.lm-detail-cell { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 6px; padding: 6px 8px; }
.lm-detail-cell span   { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 2px; }
.lm-detail-cell strong { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.lm-field-label { font-size: 0.75rem; font-weight: 600; color: var(--on-surface-var); display: block; margin-bottom: 4px; }
.lm-input { font-size: 0.8125rem; border-color: var(--surface-high); border-radius: 6px; }
.lm-input:focus { border-color: var(--green); box-shadow: 0 0 0 2px rgba(0,69,50,0.1); }
.lm-totals { background: var(--surface-low); border-radius: 8px; padding: 10px; }
.lm-total-row { display: flex; align-items: center; justify-content: space-between; padding: 4px 0; font-size: 0.8125rem; }
.lm-total-row span   { color: var(--on-surface-var); }
.lm-total-row strong { font-weight: 700; }
.lm-total-row--final { border-top: 1px solid var(--surface-high); margin-top: 4px; padding-top: 8px; }
.lm-total-row--final span, .lm-total-row--final strong { font-size: 0.9375rem; color: var(--on-surface); }
.lm-trust {
    display: flex; align-items: center; gap: 5px;
    font-size: 0.6875rem; font-weight: 600; color: var(--on-surface-var);
    background: var(--surface-low); border-radius: 6px; padding: 5px 7px;
}

/* ── AI Signal ─────────────────────────────────────────────────────────────── */
.lm-signal-card { background: linear-gradient(160deg, var(--green), var(--green-grad)); color: var(--on-green); }
.lm-signal-card .lm-card-title { color: rgba(255,255,255,0.8); }
.lm-signal-card .lm-card-icon  { background: rgba(255,255,255,0.14); color: #fff; }
.lm-signal-badge {
    width: 56px; height: 56px; border-radius: 12px; flex-shrink: 0;
    background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 800; letter-spacing: 0.04em;
}
.lm-signal-conf { font-size: 1rem; font-weight: 800; }
.lm-signal-meta { font-size: 0.75rem; opacity: 0.8; margin-top: 2px; }
.lm-signal-note { font-size: 0.8125rem; line-height: 1.55; margin: 0; opacity: 0.9; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.lm-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.lm-alert-row:last-child { border-bottom: none; }
.lm-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.lm-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.lm-toggle--on { background: var(--green); }
.lm-toggle--on i { transform: translateX(14px); }

/* ── Demand Heatmap ────────────────────────────────────────────────────────── */
.lm-heat-card { border-radius: 10px; padding: 12px; border: 1px solid var(--surface-high); background: var(--surface-low); }
.lm-heat-market { font-size: 1rem; font-weight: 800; color: var(--on-surface); }
.lm-heat-demand { font-size: 0.75rem; font-weight: 700; margin: 2px 0; }
.lm-heat-coffee { font-size: 0.6875rem; color: var(--on-surface-var); margin-bottom: 8px; }
.lm-heat-bar-wrap { height: 5px; background: var(--surface-high); border-radius: 999px; overflow: hidden; margin-bottom: 4px; }
.lm-heat-bar { height: 100%; border-radius: 999px; background: var(--green); transition: width 0.6s ease; }
.lm-heat-score { font-size: 0.625rem; color: var(--on-surface-var); font-weight: 600; }
.lm-heat-card--danger  .lm-heat-demand { color: #b91c1c; }
.lm-heat-card--warning .lm-heat-demand { color: #92400e; }
.lm-heat-card--info    .lm-heat-demand { color: #1e40af; }
.lm-heat-card--danger  .lm-heat-bar { background: #ef4444; }
.lm-heat-card--warning .lm-heat-bar { background: #f59e0b; }
.lm-heat-card--info    .lm-heat-bar { background: #3b82f6; }

/* ── Opportunities ─────────────────────────────────────────────────────────── */
.lm-opp-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 1rem; }
.lm-opp-route  { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.lm-opp-market { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── News ──────────────────────────────────────────────────────────────────── */
.lm-news-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.lm-news-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
.lm-news-icon--danger  { color: #b91c1c; }
.lm-news-icon--warning { color: #92400e; }
.lm-news-icon--success { color: #166534; }
.lm-news-icon--info    { color: #1e40af; }
.lm-news-title  { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); margin-bottom: 3px; line-height: 1.35; }
.lm-news-action { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 6px; line-height: 1.5; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.lm-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.lm-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; transition: background 0.15s; }
.lm-fab:hover { background: var(--green-grad); }
.lm-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.lm-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.lm-chatbot__avatar { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 15px; }
.lm-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.lm-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.lm-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.lm-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.lm-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.lm-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 88%; }
.lm-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.lm-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.lm-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.lm-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.lm-prompt-chip:hover { background: var(--surface-mid); }
.lm-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.lm-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.lm-chatbot__input input:focus { border-color: var(--green); }
.lm-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.lm-chat-enter-active, .lm-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.lm-chat-enter-from, .lm-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) {
    .lm-rail { position: static; }
}
@media (max-width: 767.98px) {
    .lm-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
}
</style>
