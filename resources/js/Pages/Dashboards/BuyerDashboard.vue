<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, Connection, Download, Location,
    Medal, Message, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning, WarningFilled,
} from '@element-plus/icons-vue';

/* ── KPIs ──────────────────────────────────────────────────────── */
const kpis = [
    { label: 'Available Lots',      value: '24',    sub: 'Live on market',      tone: 'primary', icon: Box          },
    { label: 'Watchlist Items',     value: '7',     sub: 'Lots you are tracking', tone: 'warning', icon: Star        },
    { label: 'Orders in Progress',  value: '3',     sub: 'Pending & processing', tone: 'info',    icon: Clock       },
    { label: 'Avg Purchase Price',  value: '$4.20', sub: 'Per kg last 30 days',  tone: 'success', icon: CollectionTag },
    { label: 'High Match Lots',     value: '9',     sub: 'AI-matched to profile',tone: 'success', icon: TrendCharts },
];

/* ── Recommended lots ──────────────────────────────────────────── */
const recommended = [
    { name: 'Mount Elgon AA',    origin: 'Uganda',   type: 'Arabica', score: 89.2, price: '$5.20', qty: '4,200 kg', notes: ['Floral','Citrus','Bright'],   badges: ['Recommended','High Match','Export Ready'] },
    { name: 'Rwenzori Select',   origin: 'Uganda',   type: 'Arabica', score: 87.8, price: '$4.60', qty: '3,800 kg', notes: ['Chocolate','Fruity','Winey'],  badges: ['High Match','Export Ready'] },
    { name: 'Sipi Falls Natural',origin: 'Uganda',   type: 'Arabica', score: 91.0, price: '$6.10', qty: '1,200 kg', notes: ['Peach','Berry','Sweet'],       badges: ['Recommended','Premium'] },
    { name: 'Yirgacheffe G1',    origin: 'Ethiopia', type: 'Arabica', score: 90.5, price: '$6.40', qty: '2,800 kg', notes: ['Jasmine','Lemon','Clean'],     badges: ['High Match'] },
    { name: 'Kivu Crest Bourbon',origin: 'Rwanda',   type: 'Arabica', score: 88.1, price: '$5.05', qty: '3,600 kg', notes: ['Caramel','Nutty','Smooth'],   badges: ['Export Ready','Tokenised'] },
];

/* ── Chart ─────────────────────────────────────────────────────── */
const chartRange = ref('7D');
const chartBars = [
    { label: 'Mon', arabica: 68, robusta: 48 },
    { label: 'Tue', arabica: 72, robusta: 52 },
    { label: 'Wed', arabica: 76, robusta: 56 },
    { label: 'Thu', arabica: 71, robusta: 60 },
    { label: 'Fri', arabica: 82, robusta: 65 },
    { label: 'Sat', arabica: 79, robusta: 68 },
];

/* ── Market table ──────────────────────────────────────────────── */
const marketLots = ref([
    { id: 'UG-001', name: 'Mount Elgon AA',      origin: 'Uganda',   type: 'Arabica', score: 89.2, price: '$5.20', qty: '4,200 kg',  demand: 'High',   badges: ['Verified','Export Ready'], demandTone: 'success' },
    { id: 'UG-002', name: 'Kyoga Robusta R1',     origin: 'Uganda',   type: 'Robusta', score: 84.6, price: '$1.88', qty: '12,500 kg', demand: 'V.High', badges: ['Verified'],               demandTone: 'danger'  },
    { id: 'ET-003', name: 'Yirgacheffe G1',       origin: 'Ethiopia', type: 'Arabica', score: 90.5, price: '$6.40', qty: '2,800 kg',  demand: 'High',   badges: ['Verified','Premium'],      demandTone: 'success' },
    { id: 'RW-004', name: 'Kivu Crest Bourbon',   origin: 'Rwanda',   type: 'Arabica', score: 88.1, price: '$5.05', qty: '3,600 kg',  demand: 'Medium', badges: ['Verified','Tokenised'],    demandTone: 'warning' },
    { id: 'BR-005', name: 'Minas Gerais Fazenda', origin: 'Brazil',   type: 'Arabica', score: 85.0, price: '$3.20', qty: '20,000 kg', demand: 'Stable', badges: ['Verified'],               demandTone: 'secondary'},
]);

const selectedLot = ref(marketLots.value[0]);

/* ── Quick buy ─────────────────────────────────────────────────── */
const qtyInput   = ref(500);
const orderType  = ref('Market Buy');
const subtotal   = computed(() => (qtyInput.value * parseFloat(selectedLot.value.price.replace('$',''))).toLocaleString('en-US', { minimumFractionDigits: 2 }));
const fee        = computed(() => (qtyInput.value * parseFloat(selectedLot.value.price.replace('$','')) * 0.005).toLocaleString('en-US', { minimumFractionDigits: 2 }));
const totalPay   = computed(() => (qtyInput.value * parseFloat(selectedLot.value.price.replace('$','')) * 1.005).toLocaleString('en-US', { minimumFractionDigits: 2 }));

/* ── Flavor preferences ────────────────────────────────────────── */
const flavorPrefs   = ref(['Fruity', 'Chocolate', 'Citrus']);
const allFlavors    = ['Fruity', 'Chocolate', 'Citrus', 'Nutty', 'Floral', 'Earthy', 'Spicy', 'Berry'];
const toggleFlavor  = (f) => {
    const i = flavorPrefs.value.indexOf(f);
    i === -1 ? flavorPrefs.value.push(f) : flavorPrefs.value.splice(i, 1);
};

/* ── Orders ────────────────────────────────────────────────────── */
const orders = [
    { lot: 'Mount Elgon AA',      qty: '500 kg',    price: '$5.20/kg', status: 'Processing', date: 'May 04', tone: 'warning' },
    { lot: 'Rwenzori Select',     qty: '1,000 kg',  price: '$4.60/kg', status: 'Shipped',    date: 'May 01', tone: 'info'    },
    { lot: 'Sipi Falls Natural',  qty: '300 kg',    price: '$6.10/kg', status: 'Pending',    date: 'May 06', tone: 'secondary'},
    { lot: 'Kyoga Robusta R1',    qty: '2,500 kg',  price: '$1.88/kg', status: 'Completed',  date: 'Apr 28', tone: 'success' },
];

/* ── Watchlist ─────────────────────────────────────────────────── */
const watchlist = ref([
    { name: 'Sipi Falls Natural', price: '$6.10', score: 91.0, demand: 'High'   },
    { name: 'Bugisu AA Reserve',  price: '$5.80', score: 88.5, demand: 'Medium' },
    { name: 'Rwenzori Select',    price: '$4.60', score: 87.8, demand: 'High'   },
]);
const removeWatch = (name) => { watchlist.value = watchlist.value.filter((w) => w.name !== name); };

/* ── Suppliers ─────────────────────────────────────────────────── */
const suppliers = [
    { name: 'Bugisu Cooperative Union', location: 'Mt. Elgon, Uganda', rating: '4.9', cert: ['Organic','Fairtrade'] },
    { name: 'Rwenzori Farmers Ltd',     location: 'Rwenzori, Uganda',  rating: '4.7', cert: ['Rainforest Alliance'] },
    { name: 'Sipi Coffee Growers',      location: 'Kapchorwa, Uganda', rating: '4.8', cert: ['UTZ','Organic']       },
];

/* ── Opportunities ─────────────────────────────────────────────── */
const opps = [
    { route: 'Uganda Robusta',   market: 'UAE',     demand: 'Extreme', price: '$2.70–3.10', score: 96, tone: 'danger'  },
    { route: 'Rwenzori Arabica', market: 'Germany', demand: 'High',    price: '$4.40–4.80', score: 84, tone: 'warning' },
    { route: 'Mount Elgon AA',   market: 'USA',     demand: 'Medium',  price: '$4.90–5.30', score: 72, tone: 'info'    },
];

/* ── Alerts ────────────────────────────────────────────────────── */
const alerts = reactive({ priceDrop: true, newMatch: true, demandSpike: false, orderUpdate: true });

/* ── AI insights ───────────────────────────────────────────────── */
const insights = [
    { text: 'Mount Elgon AA matches your citrus & floral flavor profile perfectly.',     tone: 'success' },
    { text: 'Current price of $5.20/kg is within your preferred $4.50–6.00 range.',     tone: 'primary' },
    { text: 'High demand expected for Arabica lots — consider purchasing soon.',         tone: 'warning' },
];

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: 'Hi! I\'m your Bean Origin Buyer Advisor. I can help you find the best lots for your roastery. What are you looking for?' },
]);
const prompts = ['Which coffee should I buy?', 'Is this a good price?', 'Which origin matches my taste?', 'Should I buy now?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'Based on your flavor preferences and budget, Mount Elgon AA and Sipi Falls Natural are your best matches right now — both are export-ready and within your price range.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };

const badgeClass = (b) => {
    if (b === 'Verified')       return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (b === 'Export Ready')   return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (b === 'Tokenised')      return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    if (b === 'Premium')        return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    if (b === 'Recommended')    return 'bg-success text-white';
    if (b === 'High Match')     return 'bg-primary text-white';
    return 'bg-light text-secondary border';
};
</script>

<template>
    <AppLayout title="Buyer Dashboard" full-width flush :show-banner="false">


        <div class="db-page">

            <!-- ── Header ────────────────────────────────────────────── -->
            <div class="db-header">
                <div class="container-fluid px-3 px-lg-4 border-0">
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <h1 class="db-title mb-0">Buyer Dashboard</h1>
                            <p class="db-subtitle mb-0">Source, evaluate, and purchase verified coffee</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn db-btn-primary btn-sm"><el-icon><ShoppingCart /></el-icon> Buy Coffee</button>
                            <button class="btn db-btn-outline btn-sm"><el-icon><Clock /></el-icon> My Orders</button>
                            <button class="btn db-btn-outline btn-sm"><el-icon><Star /></el-icon> Watchlist</button>
                            <button class="btn db-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                </div>
            </div>





            <div class="container-fluid px-3 px-lg-4 py-3 border-0">

                <!-- ── 2. Insight Strip ──────────────────────────────── -->
                <div class="db-insight mb-3 border-0">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="db-insight__left">
                            <el-icon><Opportunity /></el-icon>
                            <span>High-quality Ugandan Arabica lots available — strong match for specialty roasters.</span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap ms-auto">
                            <span class="db-insight-badge db-insight-badge--green">High Match</span>
                            <span class="db-insight-badge db-insight-badge--soft">Good Buying Window</span>
                            <span class="db-insight-badge db-insight-badge--muted">Premium Quality</span>
                        </div>
                    </div>
                </div>

                <!-- ── 3. KPI Cards ──────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-sm-4 col-lg">
                        <div class="db-kpi h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="db-kpi__label">{{ kpi.label }}</span>
                                <span class="db-kpi__icon" :class="`db-kpi__icon--${kpi.tone}`">
                                    <el-icon><component :is="kpi.icon" /></el-icon>
                                </span>
                            </div>
                            <div class="db-kpi__value" :class="`db-kpi__value--${kpi.tone}`">{{ kpi.value }}</div>
                            <div class="db-kpi__sub">{{ kpi.sub }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 4. Recommended Lots -->
                            <div class="db-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="db-card-title">
                                        <el-icon class="db-card-icon"><Star /></el-icon>
                                        AI-Recommended Lots
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ recommended.length }} matches</span>
                                </div>
                                <div class="db-rec-scroll">
                                    <div v-for="lot in recommended" :key="lot.name" class="db-rec-card">
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                            <div>
                                                <div class="db-rec-name">{{ lot.name }}</div>
                                                <div class="db-rec-origin">{{ lot.origin }} · {{ lot.type }}</div>
                                            </div>
                                            <div class="db-rec-score">{{ lot.score }}</div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <span v-for="b in lot.badges" :key="b" class="badge rounded-pill db-rec-badge" :class="badgeClass(b)" style="font-size:.6rem;">{{ b }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mb-3">
                                            <span v-for="note in lot.notes" :key="note" class="db-flavor-chip">{{ note }}</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="db-rec-price">{{ lot.price }}<small>/kg</small></span>
                                            <span class="db-rec-qty">{{ lot.qty }}</span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn db-btn-primary btn-sm flex-fill">Buy</button>
                                            <button class="btn db-btn-outline btn-sm flex-fill">View</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Market Overview Chart -->
                            <div class="db-card">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div class="db-card-title">
                                        <el-icon class="db-card-icon"><TrendCharts /></el-icon>
                                        Market Overview
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button v-for="r in ['7D','30D']" :key="r"
                                            class="btn" :class="chartRange === r ? 'db-btn-primary' : 'db-btn-outline'"
                                            @click="chartRange = r">{{ r }}</button>
                                    </div>
                                </div>
                                <div class="db-chart">
                                    <div class="db-chart-bars">
                                        <div v-for="bar in chartBars" :key="bar.label" class="db-chart-col">
                                            <div class="db-chart-pair">
                                                <div class="db-chart-bar db-chart-bar--arabica" :style="{ height: `${bar.arabica}%` }"></div>
                                                <div class="db-chart-bar db-chart-bar--robusta" :style="{ height: `${bar.robusta}%` }"></div>
                                            </div>
                                            <span class="db-chart-label">{{ bar.label }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-2">
                                    <div class="d-flex gap-3">
                                        <span class="db-legend"><span class="db-legend-dot db-legend-dot--arabica"></span>Arabica</span>
                                        <span class="db-legend"><span class="db-legend-dot db-legend-dot--robusta"></span>Robusta</span>
                                    </div>
                                    <div class="db-chart-insight">
                                        <el-icon><Opportunity /></el-icon>
                                        Prices slightly rising due to demand increase.
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Live Market Table -->
                            <div class="db-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="db-card-title">
                                        <el-icon class="db-card-icon"><CollectionTag /></el-icon>
                                        Live Market
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ marketLots.length }} active</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 db-table">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Origin</th>
                                                <th>Type</th>
                                                <th>Quality</th>
                                                <th>Price/kg</th>
                                                <th>Quantity</th>
                                                <th>Demand</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in marketLots" :key="lot.id"
                                                class="db-table-row"
                                                :class="{ 'db-table-row--selected': selectedLot.id === lot.id }"
                                                @click="selectedLot = lot">
                                                <td>
                                                    <div class="db-lot-name">{{ lot.name }}</div>
                                                    <div class="db-lot-id">{{ lot.id }}</div>
                                                </td>
                                                <td class="db-td-muted">{{ lot.origin }}</td>
                                                <td><span class="db-type-badge" :class="lot.type === 'Arabica' ? 'db-type-badge--amber' : 'db-type-badge--blue'">{{ lot.type }}</span></td>
                                                <td><span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.7rem;">{{ lot.score }}</span></td>
                                                <td class="fw-700">{{ lot.price }}</td>
                                                <td class="db-td-muted">{{ lot.qty }}</td>
                                                <td>
                                                    <span class="badge rounded-pill"
                                                        :class="`bg-${lot.demandTone}-subtle text-${lot.demandTone}-emphasis border border-${lot.demandTone}-subtle`"
                                                        style="font-size:.65rem;">{{ lot.demand }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <span v-for="b in lot.badges" :key="b" class="badge rounded-pill" :class="badgeClass(b)" style="font-size:.6rem;">{{ b }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm db-btn-primary px-2" @click.stop="selectedLot = lot"><el-icon><ShoppingCart /></el-icon></button>
                                                        <button class="btn btn-sm db-btn-outline px-2"><el-icon><View /></el-icon></button>
                                                        <button class="btn btn-sm db-btn-ghost px-2"><el-icon><Star /></el-icon></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 9. My Orders -->
                            <div class="db-card p-0 overflow-hidden">
                                <div class="px-3 py-2 border-bottom">
                                    <div class="db-card-title">
                                        <el-icon class="db-card-icon"><Clock /></el-icon>
                                        My Orders
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 db-table">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="order in orders" :key="order.lot + order.date">
                                                <td class="db-lot-name">{{ order.lot }}</td>
                                                <td class="db-td-muted">{{ order.qty }}</td>
                                                <td class="fw-700">{{ order.price }}</td>
                                                <td>
                                                    <span class="badge rounded-pill"
                                                        :class="`bg-${order.tone}-subtle text-${order.tone}-emphasis border border-${order.tone}-subtle`"
                                                        style="font-size:.65rem;">{{ order.status }}</span>
                                                </td>
                                                <td class="db-td-muted">{{ order.date }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Bottom row: Suppliers + Opportunities + Alerts + AI Insights -->
                            <div class="row g-3">

                                <!-- 11. Suppliers -->
                                <div class="col-12 col-md-6">
                                    <div class="db-card h-100">
                                        <div class="db-card-title mb-3">
                                            <el-icon class="db-card-icon"><Check /></el-icon>
                                            Trusted Suppliers
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div v-for="s in suppliers" :key="s.name" class="db-supplier-row">
                                                <div class="db-supplier-avatar">{{ s.name[0] }}</div>
                                                <div class="flex-fill min-width-0">
                                                    <div class="db-supplier-name">{{ s.name }}</div>
                                                    <div class="db-supplier-loc">{{ s.location }}</div>
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <span v-for="c in s.cert" :key="c" class="db-cert-chip">{{ c }}</span>
                                                    </div>
                                                </div>
                                                <div class="db-supplier-rating">⭐ {{ s.rating }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 12. Opportunities -->
                                <div class="col-12 col-md-6">
                                    <div class="db-card h-100">
                                        <div class="db-card-title mb-3">
                                            <el-icon class="db-card-icon"><Medal /></el-icon>
                                            Market Opportunities
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div v-for="opp in opps" :key="opp.route" class="db-opp-row">
                                                <div class="flex-fill">
                                                    <div class="db-opp-route">{{ opp.route }} <span class="db-opp-arrow">→</span> {{ opp.market }}</div>
                                                    <div class="d-flex align-items-center gap-2 mt-1">
                                                        <span class="db-opp-demand" :class="`db-opp-demand--${opp.tone}`">{{ opp.demand }}</span>
                                                        <span class="db-opp-price">{{ opp.price }}</span>
                                                    </div>
                                                </div>
                                                <button class="btn db-btn-outline btn-sm">View Lots</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 14. AI Insights -->
                                <div class="col-12">
                                    <div class="db-card-title mb-2">
                                        <el-icon class="db-card-icon"><Opportunity /></el-icon>
                                        AI Buyer Insights
                                    </div>
                                    <div class="row g-2">
                                        <div v-for="ins in insights" :key="ins.text" class="col-12 col-md-4">
                                            <div class="db-insight-card" :class="`db-insight-card--${ins.tone}`">
                                                <el-icon class="db-insight-card__icon"><Opportunity /></el-icon>
                                                <p class="db-insight-card__text">{{ ins.text }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /bottom row -->
                        </div>
                    </div>

                    <!-- Right column -->
                    <div class="col-12 col-xxl-4">
                        <div class="db-rail">

                            <!-- 7. Quick Buy -->
                            <div class="db-card">
                                <div class="db-card-title mb-3">
                                    <el-icon class="db-card-icon"><ShoppingCart /></el-icon>
                                    Quick Buy
                                </div>
                                <div class="db-selected-lot mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <strong class="db-selected-lot__name">{{ selectedLot.name }}</strong>
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">Selected</span>
                                    </div>
                                    <div class="db-selected-lot__meta">{{ selectedLot.origin }} · {{ selectedLot.type }}</div>
                                    <div class="row g-1 mt-2">
                                        <div class="col-6"><div class="db-detail-cell"><span>Quality</span><strong>{{ selectedLot.score }}</strong></div></div>
                                        <div class="col-6"><div class="db-detail-cell"><span>Price/kg</span><strong>{{ selectedLot.price }}</strong></div></div>
                                        <div class="col-12"><div class="db-detail-cell"><span>Available</span><strong>{{ selectedLot.qty }}</strong></div></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="db-field-label">Quantity (kg)</label>
                                    <input v-model="qtyInput" type="number" class="form-control form-control-sm db-input" placeholder="e.g. 500">
                                </div>
                                <div class="mb-3">
                                    <label class="db-field-label">Order Type</label>
                                    <select v-model="orderType" class="form-select form-select-sm db-input">
                                        <option>Market Buy</option>
                                        <option>Limit Order</option>
                                    </select>
                                </div>
                                <div class="db-totals mb-3">
                                    <div class="db-total-row"><span>Subtotal</span><strong>${{ subtotal }}</strong></div>
                                    <div class="db-total-row"><span>Platform fee (0.5%)</span><strong>${{ fee }}</strong></div>
                                    <div class="db-total-row db-total-row--final"><span>Total Payable</span><strong>${{ totalPay }}</strong></div>
                                </div>
                                <div class="d-grid gap-2 mb-3">
                                    <button class="btn db-btn-primary btn-sm">Buy Now</button>
                                    <button class="btn db-btn-outline btn-sm">Place Limit Order</button>
                                    <button class="btn db-btn-ghost btn-sm"><el-icon><Download /></el-icon> Request Sample</button>
                                </div>
                                <div class="row g-1">
                                    <div class="col-4"><div class="db-trust"><el-icon><Checked /></el-icon> Verified</div></div>
                                    <div class="col-4"><div class="db-trust"><el-icon><Van /></el-icon> Export</div></div>
                                    <div class="col-4"><div class="db-trust"><el-icon><Check /></el-icon> Secure</div></div>
                                </div>
                            </div>

                            <!-- 8. Flavor Preferences -->
                            <div class="db-card">
                                <div class="db-card-title mb-3">
                                    <el-icon class="db-card-icon"><Star /></el-icon>
                                    Flavor Preferences
                                </div>
                                <div class="mb-2">
                                    <label class="db-field-label mb-1">Coffee Type</label>
                                    <select class="form-select form-select-sm db-input">
                                        <option>Arabica (Specialty)</option>
                                        <option>Robusta</option>
                                        <option>Both</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="db-field-label mb-1">Flavor Profile</label>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button v-for="f in allFlavors" :key="f"
                                            class="db-flavor-toggle"
                                            :class="{ 'db-flavor-toggle--active': flavorPrefs.includes(f) }"
                                            @click="toggleFlavor(f)">{{ f }}</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="db-field-label mb-1">Origin Preference</label>
                                    <select class="form-select form-select-sm db-input">
                                        <option>Uganda</option>
                                        <option>Ethiopia</option>
                                        <option>All Origins</option>
                                    </select>
                                </div>
                                <button class="btn db-btn-primary btn-sm w-100">Update Preferences</button>
                            </div>

                            <!-- 10. Watchlist -->
                            <div class="db-card">
                                <div class="db-card-title mb-3">
                                    <el-icon class="db-card-icon"><Star /></el-icon>
                                    Watchlist <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size:.65rem;">{{ watchlist.length }}</span>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="w in watchlist" :key="w.name" class="db-watch-row">
                                        <div class="flex-fill">
                                            <div class="db-watch-name">{{ w.name }}</div>
                                            <div class="d-flex gap-2 mt-1">
                                                <span class="db-watch-price">{{ w.price }}/kg</span>
                                                <span class="db-watch-score">Score: {{ w.score }}</span>
                                                <span class="db-watch-demand">{{ w.demand }}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm db-btn-primary px-2"><el-icon><ShoppingCart /></el-icon></button>
                                            <button class="btn btn-sm db-btn-ghost px-2" @click="removeWatch(w.name)">×</button>
                                        </div>
                                    </div>
                                    <div v-if="!watchlist.length" class="text-center py-3 text-muted" style="font-size:.8125rem;">Your watchlist is empty.</div>
                                </div>
                            </div>

                            <!-- 13. Smart Alerts -->
                            <div class="db-card">
                                <div class="db-card-title mb-3">
                                    <el-icon class="db-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="db-alert-row">
                                        <span>{{ { priceDrop: 'Price Drop', newMatch: 'New Matching Lot', demandSpike: 'Demand Spike', orderUpdate: 'Order Update' }[key] }}</span>
                                        <button class="db-toggle" :class="{ 'db-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── Floating Chatbot ──────────────────────────────────── -->
            <div class="db-fab-wrap">
                <Transition name="db-chat">
                    <div v-if="chatOpen" class="db-chatbot">
                        <div class="db-chatbot__head">
                            <div class="db-chatbot__identity">
                                <div class="db-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="db-chatbot__name">Buyer Advisor</div>
                                    <div class="db-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="db-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="db-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="db-chat-msg" :class="`db-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="db-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="db-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="db-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="db-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.db-page {
    --green:          #004532;
    --green-grad:     #065f46;
    --on-green:       #ffffff;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-white:  #ffffff;
    --surface-low:    #f8fafc;
    --surface-mid:    #f1f5f9;
    --surface-high:   #eef2f0;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.db-header { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.db-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; }
.db-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.db-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.db-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.db-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.db-btn-outline:hover { background: var(--surface-low); }
.db-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.db-btn-ghost:hover { background: var(--surface-high); }

/* ── Insight strip ─────────────────────────────────────────────────────────── */
.db-insight {
    background: linear-gradient(135deg, var(--green), var(--green-grad));
    color: #fff; border-radius: 10px; padding: 12px 16px;
}
.db-insight__left { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; font-weight: 600; }
.db-insight-badge { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.db-insight-badge--green { background: rgba(255,255,255,0.18); color: #fff; }
.db-insight-badge--soft  { background: rgba(166,242,209,0.2);  color: #a6f2d1; }
.db-insight-badge--muted { background: rgba(255,255,255,0.1);  color: rgba(255,255,255,0.8); }

/* ── KPI Cards ─────────────────────────────────────────────────────────────── */
.db-kpi { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.db-kpi__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.db-kpi__icon  { width: 22px; height: 22px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
.db-kpi__icon--primary { background: #e0f2fe; color: #0369a1; }
.db-kpi__icon--success { background: #dcfce7; color: #166534; }
.db-kpi__icon--warning { background: #fef3c7; color: #92400e; }
.db-kpi__icon--info    { background: #dbeafe; color: #1d4ed8; }
.db-kpi__value { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.db-kpi__value--primary { color: #0369a1; }
.db-kpi__value--success { color: var(--green); }
.db-kpi__value--warning { color: #92400e; }
.db-kpi__value--info    { color: #1d4ed8; }
.db-kpi__sub   { font-size: 0.625rem; color: var(--on-surface-var); }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.db-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.db-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.db-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Recommended scroll ────────────────────────────────────────────────────── */
.db-rec-scroll { display: flex; gap: 0.75rem; overflow-x: auto; padding-bottom: 4px; }
.db-rec-scroll::-webkit-scrollbar { height: 4px; }
.db-rec-scroll::-webkit-scrollbar-track { background: var(--surface-low); border-radius: 999px; }
.db-rec-scroll::-webkit-scrollbar-thumb { background: var(--surface-high); border-radius: 999px; }
.db-rec-card   { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; min-width: 200px; flex-shrink: 0; display: flex; flex-direction: column; }
.db-rec-name   { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.db-rec-origin { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }
.db-rec-score  { width: 38px; height: 38px; border-radius: 50%; background: var(--green); color: #fff; font-size: 0.6875rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.db-rec-badge  { font-size: 0.6rem; padding: 2px 7px; }
.db-flavor-chip { display: inline-flex; align-items: center; background: rgba(0,69,50,0.07); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.db-rec-price  { font-size: 1rem; font-weight: 800; color: var(--on-surface); }
.db-rec-price small { font-size: 0.625rem; color: var(--on-surface-var); font-weight: 400; }
.db-rec-qty    { font-size: 0.75rem; color: var(--on-surface-var); }

/* ── Chart ─────────────────────────────────────────────────────────────────── */
.db-chart { height: 120px; display: flex; align-items: flex-end; background: var(--surface-low); border-radius: 8px; padding: 8px; gap: 6px; }
.db-chart-bars { display: flex; align-items: flex-end; gap: 6px; width: 100%; height: 100%; }
.db-chart-col  { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; height: 100%; justify-content: flex-end; }
.db-chart-pair { display: flex; gap: 2px; align-items: flex-end; height: 80%; }
.db-chart-bar  { width: 10px; border-radius: 3px 3px 0 0; }
.db-chart-bar--arabica { background: var(--green); }
.db-chart-bar--robusta { background: #a7f3d0; }
.db-chart-label { font-size: 0.625rem; color: var(--on-surface-var); font-weight: 600; }
.db-legend { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; color: var(--on-surface-var); }
.db-legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.db-legend-dot--arabica { background: var(--green); }
.db-legend-dot--robusta { background: #a7f3d0; }
.db-chart-insight { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--green); font-weight: 600; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.db-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.db-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.db-table-row { cursor: pointer; transition: background 0.1s; }
.db-table-row:hover { background: var(--surface-low); }
.db-table-row--selected { background: #f0fdf4 !important; }
.db-lot-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.db-lot-id   { font-size: 0.6875rem; color: var(--on-surface-var); }
.db-td-muted { color: var(--on-surface-var); font-size: 0.8125rem; }
.fw-700 { font-weight: 700; }
.db-type-badge { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.db-type-badge--amber { background: #fef3c7; color: #92400e; }
.db-type-badge--blue  { background: #dbeafe; color: #1e40af; }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.db-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* Quick buy */
.db-selected-lot { background: var(--surface-low); border-radius: 8px; padding: 10px; border: 1px solid var(--surface-high); }
.db-selected-lot__name { font-size: 0.875rem; }
.db-selected-lot__meta { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }
.db-detail-cell { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 6px; padding: 5px 8px; }
.db-detail-cell span   { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.db-detail-cell strong { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.db-field-label { font-size: 0.75rem; font-weight: 600; color: var(--on-surface-var); display: block; margin-bottom: 4px; }
.db-input { font-size: 0.8125rem; border-color: var(--surface-high); border-radius: 6px; }
.db-input:focus { border-color: var(--green); box-shadow: 0 0 0 2px rgba(0,69,50,0.1); }
.db-totals { background: var(--surface-low); border-radius: 8px; padding: 10px; }
.db-total-row { display: flex; align-items: center; justify-content: space-between; padding: 3px 0; font-size: 0.8125rem; }
.db-total-row span { color: var(--on-surface-var); }
.db-total-row strong { font-weight: 700; }
.db-total-row--final { border-top: 1px solid var(--surface-high); margin-top: 4px; padding-top: 7px; }
.db-total-row--final span, .db-total-row--final strong { font-size: 0.875rem; color: var(--on-surface); }
.db-trust { display: flex; align-items: center; gap: 4px; font-size: 0.625rem; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); border-radius: 6px; padding: 5px 6px; white-space: nowrap; }

/* Flavor toggles */
.db-flavor-toggle { border: 1px solid var(--surface-high); border-radius: 999px; background: var(--surface-white); color: var(--on-surface-var); font-size: 0.6875rem; font-weight: 600; padding: 3px 10px; cursor: pointer; transition: all 0.12s; }
.db-flavor-toggle--active { background: var(--green); border-color: var(--green); color: #fff; }

/* Watchlist */
.db-watch-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.db-watch-row:last-child { border-bottom: none; }
.db-watch-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.db-watch-price { font-size: 0.75rem; font-weight: 700; color: var(--green); }
.db-watch-score, .db-watch-demand { font-size: 0.75rem; color: var(--on-surface-var); }

/* Alerts */
.db-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.db-alert-row:last-child { border-bottom: none; }
.db-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.db-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.db-toggle--on { background: var(--green); }
.db-toggle--on i { transform: translateX(14px); }

/* Suppliers */
.db-supplier-row { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.db-supplier-row:last-child { border-bottom: none; }
.db-supplier-avatar { width: 34px; height: 34px; border-radius: 8px; background: var(--green); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9375rem; flex-shrink: 0; }
.db-supplier-name { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.db-supplier-loc  { font-size: 0.75rem; color: var(--on-surface-var); }
.db-supplier-rating { font-size: 0.75rem; font-weight: 700; color: var(--on-surface); flex-shrink: 0; }
.db-cert-chip { display: inline-flex; background: rgba(0,69,50,0.07); color: var(--green); border-radius: 999px; font-size: 0.6rem; font-weight: 700; padding: 1px 7px; }

/* Opportunities */
.db-opp-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.db-opp-row:last-child { border-bottom: none; }
.db-opp-route { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.db-opp-arrow { color: var(--on-surface-var); }
.db-opp-demand { display: inline-flex; border-radius: 999px; font-size: 0.625rem; font-weight: 700; padding: 2px 8px; }
.db-opp-demand--danger  { background: #fee2e2; color: #b91c1c; }
.db-opp-demand--warning { background: #fef3c7; color: #92400e; }
.db-opp-demand--info    { background: #dbeafe; color: #1e40af; }
.db-opp-price { font-size: 0.75rem; color: var(--on-surface-var); }

/* AI Insights */
.db-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.db-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.db-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.db-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.db-insight-card__icon { font-size: 15px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.db-insight-card__text { font-size: 0.875rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.db-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.db-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.db-fab:hover { background: var(--green-grad); }
.db-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.db-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.db-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.db-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.db-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.db-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.db-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.db-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.db-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.db-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.db-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.db-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.db-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.db-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.db-prompt-chip:hover { background: var(--surface-mid); }
.db-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.db-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.db-chatbot__input input:focus { border-color: var(--green); }
.db-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.db-chat-enter-active, .db-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.db-chat-enter-from, .db-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .db-rail { position: static; } }
@media (max-width: 767.98px)  { .db-chatbot { width: calc(100vw - 3rem); max-width: 310px; } }
</style>
