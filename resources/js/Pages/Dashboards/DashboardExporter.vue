<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, DataLine, Download,
    Location, Medal, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning,
} from '@element-plus/icons-vue';

/* ── KPIs ──────────────────────────────────────────────────────── */
const kpis = [
    { label: 'Available Lots',      value: '18',     sub: 'Sourced & ready',        tone: 'primary', icon: Box           },
    { label: 'Export-Ready Lots',   value: '11',     sub: 'Cleared for shipping',   tone: 'success', icon: Checked       },
    { label: 'Active Orders',       value: '6',      sub: 'Buyer orders confirmed', tone: 'info',    icon: CollectionTag },
    { label: 'Shipments in Transit',value: '3',      sub: 'Currently en route',     tone: 'warning', icon: Van           },
    { label: 'Avg Selling Price',   value: '$4.85',  sub: 'Per kg this quarter',    tone: 'success', icon: DataLine      },
    { label: 'Buyer Markets',       value: '7',      sub: 'Countries active',       tone: 'primary', icon: Location      },
];

/* ── Sourcing Lots ─────────────────────────────────────────────── */
const lots = ref([
    { id: 'UG-001', name: 'Mount Elgon AA',      origin: 'Uganda',   type: 'Arabica', score: 89.2, price: '$5.20', qty: '4,200 kg',  ready: 'Yes', status: 'Available', badges: ['Verified','Export Ready'],        tone: 'success', readyTone: 'success' },
    { id: 'UG-002', name: 'Kyoga Robusta R1',    origin: 'Uganda',   type: 'Robusta', score: 84.6, price: '$1.88', qty: '12,500 kg', ready: 'Yes', status: 'Reserved',  badges: ['Verified'],                       tone: 'warning', readyTone: 'success' },
    { id: 'ET-003', name: 'Yirgacheffe G1',      origin: 'Ethiopia', type: 'Arabica', score: 90.5, price: '$6.40', qty: '2,800 kg',  ready: 'No',  status: 'Available', badges: ['Verified','Premium'],             tone: 'success', readyTone: 'danger'  },
    { id: 'RW-004', name: 'Kivu Crest Bourbon',  origin: 'Rwanda',   type: 'Arabica', score: 88.1, price: '$5.05', qty: '3,600 kg',  ready: 'Yes', status: 'In Order',  badges: ['Verified','Tokenised'],           tone: 'primary', readyTone: 'success' },
    { id: 'UG-005', name: 'Rwenzori Select',     origin: 'Uganda',   type: 'Arabica', score: 87.8, price: '$4.60', qty: '3,800 kg',  ready: 'Partial', status: 'Available', badges: ['Verified','Export Ready'],   tone: 'success', readyTone: 'warning' },
]);

/* ── Readiness checklist ───────────────────────────────────────── */
const checklist = reactive([
    { item: 'Quality Cupping Report',        done: true  },
    { item: 'Certificate of Origin',         done: true  },
    { item: 'Phytosanitary Certificate',     done: false },
    { item: 'Packing List',                  done: true  },
    { item: 'Export Permit',                 done: false },
    { item: 'Warehouse Confirmation',        done: true  },
]);
const readinessScore = Math.round((checklist.filter(c => c.done).length / checklist.length) * 100);

/* ── Active Export Orders ──────────────────────────────────────── */
const orders = ref([
    { id: 'EXP-001', buyer: 'Nordic Roasters',     dest: 'Sweden',  qty: '4,200 kg',  value: '$21,840', status: 'Shipped',   shipStatus: 'In Transit', tone: 'info'    },
    { id: 'EXP-002', buyer: 'Dubai Specialty Co',  dest: 'UAE',     qty: '12,000 kg', value: '$22,560', status: 'Ready',     shipStatus: 'Preparing',  tone: 'success' },
    { id: 'EXP-003', buyer: 'Berlin Kaffee',        dest: 'Germany', qty: '2,800 kg',  value: '$17,920', status: 'Preparing', shipStatus: 'Pending',    tone: 'warning' },
    { id: 'EXP-004', buyer: 'Tokyo Bean Co',       dest: 'Japan',   qty: '1,800 kg',  value: '$9,090',  status: 'Delivered', shipStatus: 'Delivered',  tone: 'primary' },
    { id: 'EXP-005', buyer: 'NYC Roast Guild',     dest: 'USA',     qty: '3,600 kg',  value: '$18,180', status: 'Shipped',   shipStatus: 'In Transit', tone: 'info'    },
]);

/* ── Shipment Tracking ─────────────────────────────────────────── */
const shipments = [
    { id: 'SHP-001', lot: 'Mount Elgon AA',    from: 'Kampala', to: 'Stockholm', location: 'Dubai Port', eta: 'May 14', stage: 2 },
    { id: 'SHP-002', lot: 'Kyoga Robusta R1',  from: 'Kampala', to: 'Dubai',     location: 'Mombasa',   eta: 'May 10', stage: 1 },
    { id: 'SHP-003', lot: 'Kivu Crest Bourbon',from: 'Kigali',  to: 'New York',  location: 'Atlantic Ocean', eta: 'May 22', stage: 2 },
];
const shipStages = ['Packed', 'Dispatched', 'In Transit', 'Delivered'];

/* ── Market Intelligence ───────────────────────────────────────── */
const marketBars = [42, 58, 50, 72, 64, 80, 88, 70, 92, 78, 96, 84];
const markets = [
    { country: 'UAE',     demand: 'Extreme', price: '$1.88–2.40', tone: 'danger'  },
    { country: 'Germany', demand: 'High',    price: '$5.20–6.10', tone: 'success' },
    { country: 'USA',     demand: 'High',    price: '$4.80–5.50', tone: 'success' },
    { country: 'Japan',   demand: 'Medium',  price: '$5.60–6.40', tone: 'warning' },
];

/* ── Buyers ────────────────────────────────────────────────────── */
const buyers = [
    { name: 'Nordic Roasters',    country: 'Sweden',  history: '3 orders, $58K', rating: '4.9', certs: ['Fairtrade','Organic'] },
    { name: 'Dubai Specialty Co', country: 'UAE',     history: '5 orders, $94K', rating: '4.8', certs: ['Halal Certified']     },
    { name: 'Berlin Kaffee',      country: 'Germany', history: '2 orders, $36K', rating: '4.7', certs: ['Rainforest Alliance'] },
    { name: 'NYC Roast Guild',    country: 'USA',     history: '4 orders, $71K', rating: '4.6', certs: ['Organic']             },
];

/* ── Pricing & Margins ─────────────────────────────────────────── */
const margins = [
    { lot: 'Mount Elgon AA',   buy: '$3.80', sell: '$5.20', logistics: '$0.42', margin: '$0.98', pct: '18.8%', pctVal: 18.8 },
    { lot: 'Kyoga Robusta R1', buy: '$1.20', sell: '$1.88', logistics: '$0.28', margin: '$0.40', pct: '21.3%', pctVal: 21.3 },
    { lot: 'Rwenzori Select',  buy: '$3.20', sell: '$4.60', logistics: '$0.38', margin: '$1.02', pct: '22.2%', pctVal: 22.2 },
];
const marginBars = [62, 74, 68, 82, 76, 88, 80, 84, 78, 86, 92, 84];

/* ── Documents ─────────────────────────────────────────────────── */
const docs = [
    { name: 'Export Certificate — EXP-001',   type: 'Certificate', date: 'May 02', status: 'Ready',   tone: 'success' },
    { name: 'Purchase Contract — Nordic',      type: 'Contract',    date: 'Apr 28', status: 'Signed',  tone: 'success' },
    { name: 'Cupping Report Q1 2025',          type: 'Quality',     date: 'Apr 20', status: 'Ready',   tone: 'success' },
    { name: 'Shipping BL — SHP-001',           type: 'Shipping',    date: 'May 01', status: 'Pending', tone: 'warning' },
    { name: 'Phytosanitary — EXP-002',         type: 'Certificate', date: '—',      status: 'Missing', tone: 'danger'  },
];

/* ── Trade Opportunities ───────────────────────────────────────── */
const opps = [
    { route: 'Uganda Robusta → UAE',     demand: 'Extreme', price: '$1.88–2.40', score: 96, scoreTone: 'danger'  },
    { route: 'Rwenzori Arabica → Germany',demand: 'High',   price: '$4.40–4.80', score: 84, scoreTone: 'success' },
    { route: 'Mount Elgon AA → USA',     demand: 'High',    price: '$4.90–5.30', score: 78, scoreTone: 'success' },
];

/* ── Alerts ────────────────────────────────────────────────────── */
const alerts = reactive({ newLot: true, buyerDemand: true, priceIncrease: false, shipmentUpdate: true });

/* ── Sourcing filter ───────────────────────────────────────────── */
const lotSearch   = ref('');
const lotFilter   = ref('All');
const filteredLots = computed(() => {
    let result = lots.value;
    if (lotFilter.value !== 'All') {
        if (lotFilter.value === 'Export Ready') result = result.filter(l => l.ready === 'Yes');
        else result = result.filter(l => l.type === lotFilter.value);
    }
    if (lotSearch.value.trim()) {
        const q = lotSearch.value.toLowerCase();
        result = result.filter(l => l.name.toLowerCase().includes(q) || l.origin.toLowerCase().includes(q) || l.id.toLowerCase().includes(q));
    }
    return result;
});

/* ── AI Insights ───────────────────────────────────────────────── */
const insights = [
    { text: 'Your lots meet all major export requirements — only phytosanitary certificate pending.',     tone: 'success' },
    { text: 'Demand in UAE is increasing rapidly — Kyoga Robusta is priced competitively at $1.88/kg.',  tone: 'primary' },
    { text: 'Consider shipping premium Arabica lots to EU specialty roasters for highest margins.',       tone: 'warning' },
];

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Bean Origin Export Advisor. I can help you find buyers, prepare documents, and optimise your export strategy. What do you need help with?" },
]);
const prompts = ['Which market should I export to?', 'Are my lots export ready?', 'What documents do I need?', 'What price should I sell at?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'UAE is your best market right now — Kyoga Robusta demand is extreme, and your lots are priced competitively. Complete the phytosanitary certificate to unlock full export readiness.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };

const badgeClass = (b) => {
    if (b === 'Verified')      return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (b === 'Export Ready')  return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (b === 'Tokenised')     return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    if (b === 'Premium')       return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    return 'bg-light text-secondary border';
};
</script>

<template>
    <DesignPreviewLayout title="Exporter Dashboard">
        <Head title="Exporter Dashboard" />

        <div class="ex-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="ex-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <h1 class="ex-title mb-0">Exporter Dashboard</h1>
                            <p class="ex-subtitle mb-0">Manage sourcing, export readiness, and global trade</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn ex-btn-primary btn-sm"><el-icon><ShoppingCart /></el-icon> Source Coffee</button>
                            <button class="btn ex-btn-outline btn-sm"><el-icon><CollectionTag /></el-icon> Create Export Order</button>
                            <button class="btn ex-btn-outline btn-sm"><el-icon><Download /></el-icon> Documents</button>
                            <button class="btn ex-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 pb-2">
                        <span class="ex-hbadge"><el-icon><Checked /></el-icon> Verified Exporter</span>
                        <span class="ex-hbadge ex-hbadge--soft"><el-icon><Check /></el-icon> Export Ready</span>
                        <span class="ex-hbadge ex-hbadge--outline"><el-icon><Van /></el-icon> Global Trade</span>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Insight Strip ────────────────────────────────── -->
                <div class="ex-insight mb-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="ex-insight__left">
                            <el-icon><Opportunity /></el-icon>
                            <span>High demand for Ugandan Robusta in UAE — export-ready lots can be shipped quickly.</span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap ms-auto">
                            <span class="ex-insight-badge ex-insight-badge--green">High Demand</span>
                            <span class="ex-insight-badge ex-insight-badge--soft">Good Export Window</span>
                            <span class="ex-insight-badge ex-insight-badge--muted">Ready to Ship</span>
                        </div>
                    </div>
                </div>

                <!-- ── 3. KPI Cards ────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-sm-4 col-lg-2">
                        <div class="ex-kpi h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="ex-kpi__label">{{ kpi.label }}</span>
                                <span class="ex-kpi__icon" :class="`ex-kpi__icon--${kpi.tone}`">
                                    <el-icon><component :is="kpi.icon" /></el-icon>
                                </span>
                            </div>
                            <div class="ex-kpi__value" :class="`ex-kpi__value--${kpi.tone}`">{{ kpi.value }}</div>
                            <div class="ex-kpi__sub">{{ kpi.sub }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 4. Sourcing — Available Lots -->
                            <div class="ex-card p-0 overflow-hidden">
                                <!-- Card header -->
                                <div class="px-3 pt-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                                        <div class="ex-card-title">
                                            <el-icon class="ex-card-icon"><Box /></el-icon>
                                            Available Lots — Sourcing
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ filteredLots.length }} of {{ lots.length }} lots</span>
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ lots.filter(l => l.ready === 'Yes').length }} export ready</span>
                                        </div>
                                    </div>
                                    <!-- Filters + Search -->
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="ex-filter-group">
                                            <button v-for="f in ['All','Arabica','Robusta','Export Ready']" :key="f"
                                                class="ex-filter-btn"
                                                :class="{ 'ex-filter-btn--active': lotFilter === f }"
                                                @click="lotFilter = f">{{ f }}</button>
                                        </div>
                                        <div class="ex-search-wrap ms-auto">
                                            <el-icon class="ex-search-icon"><View /></el-icon>
                                            <input v-model="lotSearch" class="ex-search-input" placeholder="Search lots…">
                                        </div>
                                    </div>
                                </div>

                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ex-table">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Origin &amp; Type</th>
                                                <th>Quality</th>
                                                <th>Quantity</th>
                                                <th>Price / kg</th>
                                                <th>Readiness</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!filteredLots.length">
                                                <td colspan="8" class="text-center py-4 ex-td-muted" style="font-size:.875rem;">No lots match your filter.</td>
                                            </tr>
                                            <tr v-for="lot in filteredLots" :key="lot.id" class="ex-table-row">
                                                <!-- Lot name + ID + badges -->
                                                <td style="min-width:180px;">
                                                    <div class="d-flex align-items-start gap-2">
                                                        <div class="ex-lot-avatar" :class="lot.type === 'Arabica' ? 'ex-lot-avatar--amber' : 'ex-lot-avatar--blue'">
                                                            {{ lot.name.charAt(0) }}
                                                        </div>
                                                        <div>
                                                            <div class="ex-item-name">{{ lot.name }}</div>
                                                            <div class="ex-lot-id">{{ lot.id }}</div>
                                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                                <span v-for="b in lot.badges" :key="b"
                                                                    class="badge rounded-pill"
                                                                    :class="badgeClass(b)"
                                                                    style="font-size:.58rem; padding:2px 6px;">{{ b }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <!-- Origin + Type -->
                                                <td style="min-width:140px;">
                                                    <div class="d-flex align-items-center gap-1 mb-1">
                                                        <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                                        <span class="ex-td-muted">{{ lot.origin }}</span>
                                                    </div>
                                                    <span class="ex-type-badge" :class="lot.type === 'Arabica' ? 'ex-type-badge--amber' : 'ex-type-badge--blue'">{{ lot.type }}</span>
                                                </td>
                                                <!-- Quality score with bar -->
                                                <td style="min-width:110px;">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="ex-score-bubble" :class="lot.score >= 88 ? 'ex-score-bubble--high' : 'ex-score-bubble--mid'">{{ lot.score }}</div>
                                                        <div style="flex:1; min-width:36px;">
                                                            <div class="ex-bar-track">
                                                                <div class="ex-bar-fill" :style="{ width: `${((lot.score - 80) / 15) * 100}%`, background: lot.score >= 88 ? '#004532' : '#f59e0b' }"></div>
                                                            </div>
                                                            <div style="font-size:.625rem; color:var(--on-surface-var); margin-top:2px;">{{ lot.score >= 88 ? 'Specialty' : 'Grade A' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <!-- Quantity -->
                                                <td>
                                                    <div class="fw-semibold" style="font-size:.875rem;">{{ lot.qty }}</div>
                                                    <div class="ex-td-muted" style="font-size:.7rem;">Available</div>
                                                </td>
                                                <!-- Price -->
                                                <td>
                                                    <div class="ex-price-val">{{ lot.price }}</div>
                                                    <div class="ex-td-muted" style="font-size:.7rem;">per kg</div>
                                                </td>
                                                <!-- Export Readiness -->
                                                <td style="min-width:110px;">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <div class="ex-ready-dot"
                                                            :class="lot.readyTone === 'success' ? 'ex-ready-dot--yes'
                                                                  : lot.readyTone === 'warning' ? 'ex-ready-dot--partial'
                                                                  : 'ex-ready-dot--no'">
                                                        </div>
                                                        <span style="font-size:.8125rem; font-weight:600;"
                                                            :class="lot.readyTone === 'success' ? 'ex-up'
                                                                  : lot.readyTone === 'warning' ? 'ex-warn'
                                                                  : 'ex-down'">
                                                            {{ lot.ready === 'Yes' ? 'Ready' : lot.ready === 'Partial' ? 'Partial' : 'Not Ready' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <!-- Status -->
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="lot.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : lot.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'">
                                                        {{ lot.status }}
                                                    </span>
                                                </td>
                                                <!-- Actions -->
                                                <td class="text-end" style="min-width:160px;">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm ex-btn-outline ex-action-btn"><el-icon><View /></el-icon> View</button>
                                                        <button class="btn btn-sm ex-btn-primary ex-action-btn"><el-icon><ShoppingCart /></el-icon> Add to Export</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Footer summary -->
                                <div class="ex-table-footer">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <span class="ex-footer-stat"><span class="ex-footer-dot ex-footer-dot--green"></span>{{ lots.filter(l => l.ready === 'Yes').length }} Export Ready</span>
                                        <span class="ex-footer-stat"><span class="ex-footer-dot ex-footer-dot--yellow"></span>{{ lots.filter(l => l.ready === 'Partial').length }} Partial</span>
                                        <span class="ex-footer-stat"><span class="ex-footer-dot ex-footer-dot--red"></span>{{ lots.filter(l => l.ready === 'No').length }} Not Ready</span>
                                    </div>
                                    <button class="btn ex-btn-outline btn-sm ms-auto">View All Lots</button>
                                </div>
                            </div>

                            <!-- 6. Active Export Orders -->
                            <div class="ex-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="ex-card-title">
                                        <el-icon class="ex-card-icon"><CollectionTag /></el-icon>
                                        Active Export Orders
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ orders.length }} orders</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ex-table">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Buyer</th>
                                                <th>Destination</th>
                                                <th>Quantity</th>
                                                <th>Value</th>
                                                <th>Status</th>
                                                <th>Shipment</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="order in orders" :key="order.id" class="ex-table-row">
                                                <td class="ex-item-name">{{ order.id }}</td>
                                                <td class="fw-semibold">{{ order.buyer }}</td>
                                                <td class="ex-td-muted">{{ order.dest }}</td>
                                                <td class="ex-td-muted">{{ order.qty }}</td>
                                                <td class="fw-semibold">{{ order.value }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="order.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : order.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : order.tone === 'info'    ? 'bg-info-subtle text-info-emphasis border border-info-subtle'
                                                              : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'">
                                                        {{ order.status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="ex-ship-status" :class="`ex-ship-status--${order.tone}`">{{ order.shipStatus }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm ex-btn-outline px-2"><el-icon><View /></el-icon></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 7. Shipment Tracking -->
                            <div class="ex-card">
                                <div class="ex-card-title mb-3">
                                    <el-icon class="ex-card-icon"><Van /></el-icon>
                                    Shipment Tracking
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="shp in shipments" :key="shp.id" class="ex-ship-card">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                            <div>
                                                <div class="ex-item-name">{{ shp.id }} — {{ shp.lot }}</div>
                                                <div class="ex-td-muted" style="font-size:.75rem;">
                                                    {{ shp.from }} <span style="color:var(--on-surface-var);">→</span> {{ shp.to }}
                                                    &nbsp;·&nbsp; Now: <strong>{{ shp.location }}</strong>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="ex-eta-badge">ETA: {{ shp.eta }}</div>
                                            </div>
                                        </div>
                                        <!-- Stage progress -->
                                        <div class="ex-stages">
                                            <div v-for="(stage, i) in shipStages" :key="stage" class="ex-stage" :class="{ 'ex-stage--done': i <= shp.stage, 'ex-stage--active': i === shp.stage }">
                                                <div class="ex-stage-dot"></div>
                                                <div class="ex-stage-label">{{ stage }}</div>
                                            </div>
                                            <div class="ex-stage-track"><div class="ex-stage-fill" :style="{ width: `${(shp.stage / (shipStages.length - 1)) * 100}%` }"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Pricing & Margin Analysis -->
                            <div class="ex-card">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div class="ex-card-title">
                                        <el-icon class="ex-card-icon"><DataLine /></el-icon>
                                        Pricing &amp; Margin Analysis
                                    </div>
                                    <div class="ex-mini-chart-wrap">
                                        <div class="ex-mini-chart">
                                            <div v-for="(v, i) in marginBars" :key="i" class="ex-mini-bar" :style="{ height: `${v}%` }"></div>
                                        </div>
                                        <div style="font-size:.6875rem; color:var(--green); font-weight:600; margin-top:3px;">Margin trend ↑</div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ex-table">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Buy Price</th>
                                                <th>Sell Price</th>
                                                <th>Logistics</th>
                                                <th>Net Margin</th>
                                                <th>Margin %</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="m in margins" :key="m.lot">
                                                <td class="ex-item-name">{{ m.lot }}</td>
                                                <td class="ex-td-muted">{{ m.buy }}</td>
                                                <td class="fw-semibold">{{ m.sell }}</td>
                                                <td class="ex-td-muted">{{ m.logistics }}</td>
                                                <td class="ex-up fw-semibold">{{ m.margin }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="ex-bar-track" style="width:60px;">
                                                            <div class="ex-bar-fill" :style="{ width: `${m.pctVal * 4}%`, background: '#004532' }"></div>
                                                        </div>
                                                        <span class="ex-up" style="font-size:.8125rem; font-weight:700;">{{ m.pct }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 11. Documents -->
                            <div class="ex-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="ex-card-title">
                                        <el-icon class="ex-card-icon"><Download /></el-icon>
                                        Documents Management
                                    </div>
                                    <button class="btn ex-btn-primary btn-sm px-2"><el-icon><Download /></el-icon> Upload</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ex-table">
                                        <thead>
                                            <tr>
                                                <th>Document</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="doc in docs" :key="doc.name" class="ex-table-row">
                                                <td class="ex-item-name">{{ doc.name }}</td>
                                                <td><span class="ex-type-badge ex-type-badge--green">{{ doc.type }}</span></td>
                                                <td class="ex-td-muted">{{ doc.date }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="doc.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : doc.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                        {{ doc.status }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm ex-btn-outline px-2"><el-icon><View /></el-icon></button>
                                                        <button class="btn btn-sm ex-btn-ghost px-2"><el-icon><Download /></el-icon></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 12. Trade Opportunities + AI Insights -->
                            <div class="row g-3">

                                <!-- Trade Opportunities -->
                                <div class="col-12 col-md-6">
                                    <div class="ex-card h-100">
                                        <div class="ex-card-title mb-3">
                                            <el-icon class="ex-card-icon"><Medal /></el-icon>
                                            Trade Opportunities
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div v-for="opp in opps" :key="opp.route" class="ex-opp-row">
                                                <div class="flex-fill">
                                                    <div class="ex-opp-route">{{ opp.route }}</div>
                                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                        <span class="badge rounded-pill" style="font-size:.625rem;"
                                                            :class="opp.scoreTone === 'danger' ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-success-subtle text-success-emphasis border border-success-subtle'">
                                                            {{ opp.demand }}
                                                        </span>
                                                        <span class="ex-td-muted" style="font-size:.75rem;">{{ opp.price }}</span>
                                                        <span class="ex-opp-score">Score: {{ opp.score }}</span>
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm ex-btn-primary flex-shrink-0">Export Now</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- AI Insights -->
                                <div class="col-12 col-md-6">
                                    <div class="ex-card-title mb-2">
                                        <el-icon class="ex-card-icon"><Opportunity /></el-icon>
                                        AI Export Insights
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="ins in insights" :key="ins.text" class="ex-insight-card" :class="`ex-insight-card--${ins.tone}`">
                                            <el-icon class="ex-insight-icon"><Opportunity /></el-icon>
                                            <p class="ex-insight-text">{{ ins.text }}</p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="ex-rail">

                            <!-- 5. Export Readiness Panel -->
                            <div class="ex-card">
                                <div class="ex-card-title mb-3">
                                    <el-icon class="ex-card-icon"><Checked /></el-icon>
                                    Export Readiness
                                </div>
                                <!-- Readiness score -->
                                <div class="ex-readiness-score-wrap mb-3">
                                    <div class="ex-readiness-ring">
                                        <svg viewBox="0 0 80 80" width="90" height="90">
                                            <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                            <circle cx="40" cy="40" r="34" fill="none"
                                                :stroke="readinessScore >= 80 ? '#004532' : readinessScore >= 50 ? '#f59e0b' : '#ef4444'"
                                                stroke-width="8"
                                                stroke-dasharray="213.6"
                                                :stroke-dashoffset="213.6 - (213.6 * readinessScore / 100)"
                                                stroke-linecap="round"
                                                transform="rotate(-90 40 40)"/>
                                        </svg>
                                        <div class="ex-readiness-inner">
                                            <div class="ex-readiness-pct">{{ readinessScore }}%</div>
                                            <div class="ex-readiness-lbl">Ready</div>
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-fill">
                                        <div class="ex-readiness-msg">
                                            {{ readinessScore >= 80 ? 'Almost export ready!' : 'Action required' }}
                                        </div>
                                        <div class="ex-td-muted" style="font-size:.75rem;">
                                            {{ checklist.filter(c => c.done).length }}/{{ checklist.length }} items complete
                                        </div>
                                    </div>
                                </div>
                                <!-- Checklist -->
                                <div class="d-flex flex-column gap-1 mb-3">
                                    <div v-for="item in checklist" :key="item.item" class="ex-check-row">
                                        <div class="ex-check-icon" :class="item.done ? 'ex-check-icon--done' : 'ex-check-icon--miss'">
                                            <el-icon><component :is="item.done ? Check : Warning" /></el-icon>
                                        </div>
                                        <span class="ex-check-label" :class="{ 'ex-check-label--miss': !item.done }">{{ item.item }}</span>
                                    </div>
                                </div>
                                <button class="btn ex-btn-primary btn-sm w-100">Complete Documents</button>
                            </div>

                            <!-- 8. Market Intelligence -->
                            <div class="ex-card">
                                <div class="ex-card-title mb-3">
                                    <el-icon class="ex-card-icon"><TrendCharts /></el-icon>
                                    Market Intelligence
                                </div>
                                <div class="ex-mini-chart mb-3">
                                    <div v-for="(v, i) in marketBars" :key="i" class="ex-mini-bar" :style="{ height: `${v}%` }"></div>
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <div v-for="m in markets" :key="m.country" class="ex-market-row">
                                        <div>
                                            <div class="ex-market-name">{{ m.country }}</div>
                                            <div class="ex-td-muted" style="font-size:.75rem;">{{ m.price }}</div>
                                        </div>
                                        <span class="badge rounded-pill" style="font-size:.65rem;"
                                            :class="m.tone === 'danger'  ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                  : m.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                  : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                            {{ m.demand }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Buyer Management -->
                            <div class="ex-card">
                                <div class="ex-card-title mb-3">
                                    <el-icon class="ex-card-icon"><Star /></el-icon>
                                    Buyers
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="buyer in buyers" :key="buyer.name" class="ex-buyer-row">
                                        <div class="ex-buyer-avatar">{{ buyer.name[0] }}</div>
                                        <div class="flex-fill min-w-0">
                                            <div class="ex-buyer-name">{{ buyer.name }}</div>
                                            <div class="ex-td-muted" style="font-size:.7rem;">{{ buyer.country }} · {{ buyer.history }}</div>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                <span v-for="c in buyer.certs" :key="c" class="ex-cert-chip">{{ c }}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end gap-1">
                                            <div class="ex-buyer-rating">⭐ {{ buyer.rating }}</div>
                                            <button class="btn btn-sm ex-btn-outline px-2" style="font-size:.6875rem;">Contact</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. Smart Alerts -->
                            <div class="ex-card">
                                <div class="ex-card-title mb-3">
                                    <el-icon class="ex-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="ex-alert-row">
                                        <span>{{ { newLot: 'New Export-Ready Lot', buyerDemand: 'Buyer Demand', priceIncrease: 'Price Increase', shipmentUpdate: 'Shipment Updates' }[key] }}</span>
                                        <button class="ex-toggle" :class="{ 'ex-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 15. Floating Chatbot ────────────────────────────────── -->
            <div class="ex-fab-wrap">
                <Transition name="ex-chat">
                    <div v-if="chatOpen" class="ex-chatbot">
                        <div class="ex-chatbot__head">
                            <div class="ex-chatbot__identity">
                                <div class="ex-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="ex-chatbot__name">Export Advisor</div>
                                    <div class="ex-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="ex-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="ex-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="ex-chat-msg" :class="`ex-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="ex-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="ex-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="ex-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="ex-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.ex-page {
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
.ex-header   { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.ex-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; }
.ex-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); }
.ex-hbadge   { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.ex-hbadge--soft    { background: #dcfce7; color: #166534; }
.ex-hbadge--outline { background: transparent; border: 1px solid var(--surface-high); color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.ex-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.ex-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.ex-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.ex-btn-outline:hover { background: var(--surface-low); }
.ex-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.ex-btn-ghost:hover { background: var(--surface-high); }

/* ── Insight strip ─────────────────────────────────────────────────────────── */
.ex-insight { background: linear-gradient(135deg, var(--green), var(--green-grad)); color: #fff; border-radius: 10px; padding: 12px 16px; }
.ex-insight__left { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; font-weight: 600; }
.ex-insight-badge { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.ex-insight-badge--green { background: rgba(255,255,255,0.18); color: #fff; }
.ex-insight-badge--soft  { background: rgba(166,242,209,0.2);  color: #a6f2d1; }
.ex-insight-badge--muted { background: rgba(255,255,255,0.1);  color: rgba(255,255,255,0.8); }

/* ── KPI Cards ─────────────────────────────────────────────────────────────── */
.ex-kpi { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.ex-kpi__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.ex-kpi__icon  { width: 22px; height: 22px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
.ex-kpi__icon--primary { background: #e0f2fe; color: #0369a1; }
.ex-kpi__icon--success { background: #dcfce7; color: #166534; }
.ex-kpi__icon--warning { background: #fef3c7; color: #92400e; }
.ex-kpi__icon--info    { background: #dbeafe; color: #1d4ed8; }
.ex-kpi__value { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.ex-kpi__value--primary { color: #0369a1; }
.ex-kpi__value--success { color: var(--green); }
.ex-kpi__value--warning { color: #92400e; }
.ex-kpi__value--info    { color: #1d4ed8; }
.ex-kpi__sub   { font-size: 0.625rem; color: var(--on-surface-var); }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.ex-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 6px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.ex-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.ex-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.ex-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.ex-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.ex-table-row { transition: background 0.1s; }
.ex-table-row:hover { background: var(--surface-low); }
.ex-item-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.ex-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; }
.ex-type-badge { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.ex-type-badge--amber { background: #fef3c7; color: #92400e; }
.ex-type-badge--blue  { background: #dbeafe; color: #1e40af; }
.ex-type-badge--green { background: #dcfce7; color: #166534; }
.ex-ship-status { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; background: var(--surface-mid); color: var(--on-surface-var); }
.ex-ship-status--success { background: #dcfce7; color: #166534; }
.ex-ship-status--info    { background: #dbeafe; color: #1d4ed8; }
.ex-ship-status--warning { background: #fef3c7; color: #92400e; }
.ex-ship-status--primary { background: #ede9fe; color: #6d28d9; }
.ex-up   { color: #166534; font-weight: 700; }
.ex-warn { color: #92400e; font-weight: 700; }
.ex-down { color: #b91c1c; font-weight: 700; }

/* ── Sourcing table enhancements ───────────────────────────────────────────── */
.ex-filter-group { display: flex; gap: 3px; background: var(--surface-low); border-radius: 8px; padding: 3px; }
.ex-filter-btn { font-size: 0.6875rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; border: none; background: transparent; color: var(--on-surface-var); cursor: pointer; white-space: nowrap; transition: all 0.12s; }
.ex-filter-btn:hover { background: var(--surface-mid); color: var(--on-surface); }
.ex-filter-btn--active { background: var(--surface-white); color: var(--green); box-shadow: 0 1px 3px rgba(0,0,0,.08); font-weight: 700; }
.ex-search-wrap { position: relative; display: flex; align-items: center; }
.ex-search-icon { position: absolute; left: 8px; font-size: 13px; color: var(--on-surface-var); pointer-events: none; }
.ex-search-input { height: 30px; border: 1px solid var(--surface-high); border-radius: 7px; padding: 0 10px 0 28px; font-size: 0.8125rem; outline: none; color: var(--on-surface); background: var(--surface-white); width: 160px; }
.ex-search-input:focus { border-color: var(--green); }
.ex-lot-avatar { width: 34px; height: 34px; border-radius: 8px; font-weight: 800; font-size: 0.9375rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ex-lot-avatar--amber { background: #fef3c7; color: #92400e; }
.ex-lot-avatar--blue  { background: #dbeafe; color: #1e40af; }
.ex-lot-id { font-size: 0.6875rem; color: var(--on-surface-var); margin-top: 1px; }
.ex-score-bubble { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.6875rem; font-weight: 800; flex-shrink: 0; }
.ex-score-bubble--high { background: #dcfce7; color: #166534; }
.ex-score-bubble--mid  { background: #fef3c7; color: #92400e; }
.ex-price-val { font-size: 1rem; font-weight: 800; color: var(--on-surface); }
.ex-ready-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ex-ready-dot--yes     { background: #16a34a; }
.ex-ready-dot--partial { background: #f59e0b; }
.ex-ready-dot--no      { background: #dc2626; }
.ex-action-btn { font-size: 0.75rem !important; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.ex-table-footer { display: flex; align-items: center; gap: 1rem; padding: 10px 16px; border-top: 1px solid var(--surface-low); background: var(--surface-low); flex-wrap: wrap; }
.ex-footer-stat { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 600; color: var(--on-surface-var); }
.ex-footer-dot { width: 8px; height: 8px; border-radius: 50%; }
.ex-footer-dot--green  { background: #16a34a; }
.ex-footer-dot--yellow { background: #f59e0b; }
.ex-footer-dot--red    { background: #dc2626; }

/* ── Shipment tracking ─────────────────────────────────────────────────────── */
.ex-ship-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 6px; padding: 0.875rem; }
.ex-eta-badge { display: inline-flex; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.ex-stages { position: relative; display: flex; justify-content: space-between; align-items: flex-start; margin-top: 12px; gap: 0; }
.ex-stage { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; position: relative; z-index: 1; }
.ex-stage-dot { width: 12px; height: 12px; border-radius: 50%; background: var(--surface-high); border: 2px solid var(--surface-high); }
.ex-stage--done .ex-stage-dot  { background: var(--green); border-color: var(--green); }
.ex-stage--active .ex-stage-dot { background: var(--green); border-color: var(--green); box-shadow: 0 0 0 3px rgba(0,69,50,0.15); }
.ex-stage-label { font-size: 0.625rem; font-weight: 600; color: var(--on-surface-var); text-align: center; }
.ex-stage--done .ex-stage-label { color: var(--green); }
.ex-stage-track { position: absolute; top: 5px; left: 6%; right: 6%; height: 2px; background: var(--surface-high); border-radius: 999px; z-index: 0; }
.ex-stage-fill  { height: 100%; background: var(--green); border-radius: 999px; transition: width 0.6s ease; }

/* ── Bars ──────────────────────────────────────────────────────────────────── */
.ex-bar-track { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.ex-bar-fill  { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* ── Mini chart ────────────────────────────────────────────────────────────── */
.ex-mini-chart-wrap { display: flex; flex-direction: column; align-items: flex-end; }
.ex-mini-chart { display: flex; align-items: flex-end; gap: 3px; height: 40px; }
.ex-mini-bar   { width: 6px; background: var(--green); border-radius: 2px 2px 0 0; opacity: 0.75; }

/* ── Readiness ─────────────────────────────────────────────────────────────── */
.ex-readiness-score-wrap { display: flex; align-items: center; }
.ex-readiness-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ex-readiness-inner { position: absolute; text-align: center; }
.ex-readiness-pct { font-size: 1.125rem; font-weight: 800; color: var(--green); line-height: 1; }
.ex-readiness-lbl { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.ex-readiness-msg { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.ex-check-row { display: flex; align-items: center; gap: 8px; padding: 5px 0; border-bottom: 1px solid var(--surface-low); }
.ex-check-row:last-child { border-bottom: none; }
.ex-check-icon { width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.ex-check-icon--done { background: #dcfce7; color: #166534; }
.ex-check-icon--miss { background: #fee2e2; color: #b91c1c; }
.ex-check-label { font-size: 0.8125rem; color: var(--on-surface); }
.ex-check-label--miss { color: #b91c1c; }

/* ── Market rows ───────────────────────────────────────────────────────────── */
.ex-market-row { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); }
.ex-market-row:last-child { border-bottom: none; }
.ex-market-name { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }

/* ── Buyers ────────────────────────────────────────────────────────────────── */
.ex-buyer-row { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.ex-buyer-row:last-child { border-bottom: none; }
.ex-buyer-avatar { width: 34px; height: 34px; border-radius: 8px; background: var(--green); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9375rem; flex-shrink: 0; }
.ex-buyer-name   { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.ex-buyer-rating { font-size: 0.75rem; font-weight: 700; color: var(--on-surface); white-space: nowrap; }
.ex-cert-chip { display: inline-flex; background: rgba(0,69,50,0.07); color: var(--green); border-radius: 999px; font-size: 0.6rem; font-weight: 700; padding: 1px 7px; }

/* ── Opportunities ─────────────────────────────────────────────────────────── */
.ex-opp-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--surface-low); }
.ex-opp-row:last-child { border-bottom: none; }
.ex-opp-route { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.ex-opp-score { font-size: 0.75rem; font-weight: 700; color: var(--green); }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.ex-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 6px; border: 1px solid; }
.ex-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.ex-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.ex-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.ex-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.ex-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.ex-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.ex-alert-row:last-child { border-bottom: none; }
.ex-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.ex-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.ex-toggle--on { background: var(--green); }
.ex-toggle--on i { transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.ex-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.ex-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.ex-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.ex-fab:hover { background: var(--green-grad); }
.ex-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.ex-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.ex-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.ex-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.ex-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.ex-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.ex-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.ex-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.ex-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.ex-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.ex-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.ex-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.ex-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.ex-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.ex-prompt-chip:hover { background: var(--surface-mid); }
.ex-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.ex-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.ex-chatbot__input input:focus { border-color: var(--green); }
.ex-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.ex-chat-enter-active, .ex-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.ex-chat-enter-from, .ex-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .ex-rail { position: static; } }
@media (max-width: 767.98px)  { .ex-chatbot { width: calc(100vw - 3rem); max-width: 310px; } }
</style>
