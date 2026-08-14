<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    Coffee, CollectionTag, DataLine, Download, Location,
    Medal, Opportunity, PriceTag, Promotion, ShoppingCart,
    Star, TrendCharts, User, Van, View, Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    cooperative: { type: Object, required: true },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

/* ── Real computed ─────────────────────────────────────────────── */
const cooperativeName    = computed(() => props.cooperative.name || 'Coffee Farmers Cooperative');
const cooperativeCode    = computed(() => props.cooperative.code || 'COOP-001');
const registrationNumber = computed(() => props.cooperative.registration_number || 'REG-2018-COOP');
const locationLabel      = computed(() =>
    [props.cooperative.sub_county, props.cooperative.district, 'Uganda'].filter(Boolean).join(', ') || 'Origin pending',
);
const establishedYear = computed(() =>
    props.cooperative.created_at ? new Date(props.cooperative.created_at).getFullYear() : '2018',
);
const initials = computed(() =>
    cooperativeName.value.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase().slice(0, 2),
);

/* ── Static mock data ──────────────────────────────────────────── */
const prodBars  = [48, 60, 68, 76, 72, 82, 86, 80, 88, 84, 90, 86];
const qualBars  = [72, 78, 74, 82, 80, 86, 84, 88, 86, 90, 89, 92];

const memberFarmers = [
    { name: 'James Okwi',    region: 'Mbale',      type: 'Arabica', size: '2.4 ha', score: 88.5, prod: '2,400 kg' },
    { name: 'Grace Atim',    region: 'Kapchorwa',  type: 'Arabica', size: '1.8 ha', score: 87.2, prod: '1,800 kg' },
    { name: 'David Muwanga', region: 'Kasese',      type: 'Mixed',   size: '3.1 ha', score: 85.6, prod: '2,800 kg' },
    { name: 'Sarah Nakato',  region: 'Mbale',      type: 'Arabica', size: '1.5 ha', score: 89.0, prod: '1,500 kg' },
    { name: 'Peter Ochieng', region: 'Soroti',     type: 'Robusta', size: '4.2 ha', score: 84.4, prod: '4,200 kg' },
];

const traceStages = [
    { label: 'Farmers', icon: Star,          value: memberFarmers.length, status: 'Verified',   complete: true  },
    { label: 'Farms',   icon: Location,      value: '12',                 status: 'Registered', complete: true  },
    { label: 'Harvests',icon: Box,           value: '28',                 status: 'Recorded',   complete: true  },
    { label: 'Batches', icon: CollectionTag, value: '14',                 status: 'Processing', complete: false },
    { label: 'Lots',    icon: Star,          value: '9',                  status: 'Listed',     complete: false },
    { label: 'Export',  icon: Van,           value: '3',                  status: 'Ready',      complete: false },
];

const lots = [
    { id: `${cooperativeCode.value}-24-08A`, type: 'Arabica', origin: 'Mbale, Uganda',     score: 89.3, qty: '2,400 kg', price: '$5.20/kg', demand: 'High',   status: 'Export Ready', tone: 'success' },
    { id: `${cooperativeCode.value}-24-12C`, type: 'Arabica', origin: 'Kapchorwa, Uganda', score: 88.0, qty: '1,800 kg', price: '$4.90/kg', demand: 'High',   status: 'Verified',     tone: 'primary' },
    { id: `${cooperativeCode.value}-24-15B`, type: 'Mixed',   origin: 'Kasese, Uganda',    score: 85.5, qty: '2,800 kg', price: '$4.20/kg', demand: 'Medium', status: 'Listed',       tone: 'warning' },
];

const qualityProfile = [
    { attribute: 'Aroma',      score: 88, display: '8.8/10' },
    { attribute: 'Flavor',     score: 87, display: '8.7/10' },
    { attribute: 'Acidity',    score: 90, display: '9.0/10' },
    { attribute: 'Body',       score: 85, display: '8.5/10' },
    { attribute: 'Aftertaste', score: 86, display: '8.6/10' },
];

const sustainability = [
    { label: 'Shade-Grown Coverage',    value: '92%', icon: Star    },
    { label: 'Water Conservation',      value: '85%', icon: Check   },
    { label: 'Organic Fertilizer Use',  value: 'Active', icon: Checked },
    { label: 'Climate-Smart Practices', value: 'Certified', icon: Medal },
];

const markets = [
    { region: 'Germany / EU', demand: 'High',    price: '$5.00–5.40', pct: 85 },
    { region: 'UAE',          demand: 'Extreme', price: '$1.80–2.10', pct: 96 },
    { region: 'USA',          demand: 'High',    price: '$4.80–5.20', pct: 78 },
    { region: 'Japan',        demand: 'Medium',  price: '$5.20–5.80', pct: 62 },
];

const facilities = [
    { name: 'Washing Station',   count: '2 units',  status: 'Operational', tone: 'success' },
    { name: 'Drying Beds',       count: '18 beds',  status: 'Active',      tone: 'success' },
    { name: 'Warehouse Storage', count: '400 MT',   status: 'Available',   tone: 'success' },
    { name: 'Milling Partner',   count: '1 partner',status: 'Contracted',  tone: 'primary' },
];

const documents = [
    { name: 'Registration Certificate',  status: 'Verified', tone: 'success' },
    { name: 'Export License',            status: 'Verified', tone: 'success' },
    { name: 'Organic Certification',     status: 'Pending',  tone: 'warning' },
    { name: 'Quality Audit Report',      status: 'Verified', tone: 'success' },
    { name: 'Fairtrade Certificate',     status: 'Expired',  tone: 'danger'  },
];

const timeline = [
    { icon: Star,          text: 'Cooperative registered on Bean Origin',      date: `${establishedYear.value}`,  tone: 'success' },
    { icon: Location,      text: '12 farms registered across member network',  date: '2023',                      tone: 'primary' },
    { icon: Box,           text: '28 harvests recorded this season',           date: 'Mar 2025',                  tone: 'success' },
    { icon: CollectionTag, text: 'Batch processing — 14 batches in progress',  date: 'Apr 2025',                  tone: 'warning' },
    { icon: ShoppingCart,  text: '9 lots listed on Bean Origin market',        date: 'Apr 2025',                  tone: 'info'   },
    { icon: Van,           text: '3 lots export-cleared and ready to ship',    date: 'May 2025',                  tone: 'primary' },
];

const insights = [
    { text: 'Specialty production is increasing this season — average cupping scores up 1.8 points.', tone: 'success' },
    { text: 'Export demand from UAE is rising for Arabica — prioritise export-ready lot processing.',  tone: 'primary' },
    { text: 'Member farmer quality consistency improved by 12% compared to last season.',             tone: 'warning' },
];

const alerts = reactive({ newLots: true, exportReady: true, qualityAlerts: false, buyerInterest: true, certExpiry: true });

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: `Hi! I'm your Bean Origin Cooperative Advisor. I can help you understand ${cooperativeName.value}'s production, export readiness, and available lots. What would you like to know?` },
]);
const prompts = ['Which farmers belong to this cooperative?', 'Is this cooperative export ready?', 'What coffee is available?', 'What certifications are active?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: `${cooperativeName.value} has ${memberFarmers.length} active member farmers, ${lots.length} available lots, and strong export readiness across EU and UAE markets. Organic certification is currently pending renewal.` }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <AppLayout :title="cooperativeName" full-width flush :show-banner="false">
        <Head :title="cooperativeName" />

        <div class="co-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="co-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div v-if="flashSuccess" class="co-flash">
                        <el-icon><Check /></el-icon> {{ flashSuccess }}
                    </div>
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="co-kicker">Cooperative Profile</div>
                            <h1 class="co-title mb-0">{{ cooperativeName }}</h1>
                            <p class="co-subtitle mb-2">Verified coffee cooperative with traceable production and export capabilities.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="co-badge co-badge--green">Verified Cooperative</span>
                                <span class="co-badge co-badge--blue">Export Ready</span>
                                <span class="co-badge co-badge--amber">Sustainable</span>
                                <span class="co-badge co-badge--soft">Traceable Supply</span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn co-btn-outline btn-sm"><el-icon><ChatDotRound /></el-icon> Contact</button>
                            <button class="btn co-btn-outline btn-sm"><el-icon><ShoppingCart /></el-icon> View Lots</button>
                            <button class="btn co-btn-outline btn-sm"><el-icon><Download /></el-icon> Export Data</button>
                            <button class="btn co-btn-primary btn-sm" @click="chatOpen = true"><el-icon><Opportunity /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Hero Overview ────────────────────────────────── -->
                <div class="co-section mb-3">
                    <div class="row g-0">

                        <!-- Left: Identity -->
                        <div class="col-12 col-md-4 co-identity-col">
                            <div class="co-identity-avatar">{{ initials }}</div>
                            <div class="co-identity-name">{{ cooperativeName }}</div>
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                <span class="co-muted" style="font-size:.8125rem;">{{ locationLabel }}</span>
                            </div>
                            <div class="co-muted mb-2" style="font-size:.8125rem;">
                                Reg: <strong>{{ registrationNumber }}</strong> · Est. {{ establishedYear }}
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="co-tag">Arabica</span>
                                <span class="co-tag">Specialty</span>
                                <span class="co-tag co-tag--blue">Fairtrade</span>
                            </div>
                        </div>

                        <!-- Center: Production -->
                        <div class="col-12 col-md-4 co-prod-col">
                            <div class="co-col-label">Production Capacity</div>
                            <div class="co-kpi-list">
                                <div class="co-kpi-row"><span>Member Farmers</span><strong>{{ memberFarmers.length }}</strong></div>
                                <div class="co-kpi-row"><span>Total Farms</span><strong>12</strong></div>
                                <div class="co-kpi-row"><span>Annual Production</span><strong>12,700 kg</strong></div>
                                <div class="co-kpi-row"><span>Active Harvests</span><strong>28</strong></div>
                                <div class="co-kpi-row"><span>Active Lots</span><strong>{{ lots.length }}</strong></div>
                                <div class="co-kpi-row"><span>Warehouse Capacity</span><strong>400 MT</strong></div>
                            </div>
                        </div>

                        <!-- Right: Trust & Quality -->
                        <div class="col-12 col-md-4 co-trust-col">
                            <div class="co-col-label">Trust &amp; Quality</div>
                            <div class="co-kpi-list mb-2">
                                <div class="co-kpi-row"><span>Avg Cupping Score</span><strong class="co-green">87.4</strong></div>
                                <div class="co-kpi-row"><span>Export Readiness</span><strong class="co-green">88%</strong></div>
                                <div class="co-kpi-row"><span>Sustainability Score</span><strong class="co-green">92%</strong></div>
                                <div class="co-kpi-row"><span>Traceability Score</span><strong class="co-green">95%</strong></div>
                                <div class="co-kpi-row"><span>Buyer Rating</span><strong class="co-green">4.8 ★</strong></div>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="co-tag co-tag--amber">Organic</span>
                                <span class="co-tag co-tag--blue">Fairtrade</span>
                                <span class="co-tag">Rainforest Alliance</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 3. Location & Coverage -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><Location /></el-icon>
                                    Location &amp; Coverage
                                </div>
                                <div class="co-section-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-7">
                                            <div class="co-map-tile">
                                                <div class="co-map-pin"></div>
                                                <div class="co-map-info">
                                                    <div class="fw-semibold" style="font-size:.875rem;">{{ locationLabel }}</div>
                                                    <div class="co-muted" style="font-size:.75rem;">Uganda · East Africa Coffee Belt · 1,200–2,100m ASL</div>
                                                </div>
                                                <div class="co-map-tags">
                                                    <span class="co-map-tag">Highland Zone</span>
                                                    <span class="co-map-tag">Volcanic Soil</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <div class="co-info-grid">
                                                <div class="co-info-cell"><span>District</span><strong>{{ props.cooperative.district || 'Mbale' }}</strong></div>
                                                <div class="co-info-cell"><span>Sub-County</span><strong>{{ props.cooperative.sub_county || 'Buginyanya' }}</strong></div>
                                                <div class="co-info-cell"><span>Collection Centers</span><strong>4 centres</strong></div>
                                                <div class="co-info-cell"><span>Washing Stations</span><strong>2 stations</strong></div>
                                                <div class="co-info-cell"><span>Altitude Range</span><strong>1,400–1,950m</strong></div>
                                                <div class="co-info-cell"><span>Climate Zone</span><strong>Highland Equatorial</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Member Farmers -->
                            <div class="co-section p-0 overflow-hidden">
                                <div class="co-section-head px-3">
                                    <el-icon class="co-section-icon"><Star /></el-icon>
                                    Member Farmers
                                    <div class="ms-auto d-flex gap-2 align-items-center">
                                        <span class="co-section-stat">{{ memberFarmers.length }} active</span>
                                        <span class="co-section-stat co-section-stat--green">{{ memberFarmers.filter(f => f.score >= 87).length }} specialty</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 co-table">
                                        <thead>
                                            <tr>
                                                <th><span class="co-th"><el-icon><User /></el-icon> Farmer</span></th>
                                                <th><span class="co-th"><el-icon><Location /></el-icon> Region</span></th>
                                                <th><span class="co-th"><el-icon><Coffee /></el-icon> Coffee Type</span></th>
                                                <th><span class="co-th"><el-icon><Box /></el-icon> Farm Size</span></th>
                                                <th><span class="co-th"><el-icon><Medal /></el-icon> Quality Score</span></th>
                                                <th><span class="co-th"><el-icon><TrendCharts /></el-icon> Production</span></th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="farmer in memberFarmers" :key="farmer.name" class="co-table-row">
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="co-farmer-dot">{{ farmer.name[0] }}</div>
                                                        <span class="co-item-name">{{ farmer.name }}</span>
                                                    </div>
                                                </td>
                                                <td class="co-muted">{{ farmer.region }}</td>
                                                <td><span class="co-tag">{{ farmer.type }}</span></td>
                                                <td class="co-muted">{{ farmer.size }}</td>
                                                <td>
                                                    <span class="co-score-pill" :class="farmer.score >= 88 ? 'co-score-pill--high' : 'co-score-pill--mid'">{{ farmer.score }}</span>
                                                </td>
                                                <td class="co-muted">{{ farmer.prod }}</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm co-btn-outline co-act-btn"><el-icon><View /></el-icon> View</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 5. Production Overview -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><TrendCharts /></el-icon>
                                    Production Overview
                                </div>
                                <div class="co-section-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="co-muted mb-1" style="font-size:.75rem;">Seasonal Production Trend</div>
                                            <div class="co-chart">
                                                <div v-for="(v, i) in prodBars" :key="i" class="co-chart-bar co-chart-bar--prod" :style="{ height: `${v}%` }"></div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="co-muted mb-1" style="font-size:.75rem;">Quality Trend</div>
                                            <div class="co-chart">
                                                <div v-for="(v, i) in qualBars" :key="i" class="co-chart-bar co-chart-bar--qual" :style="{ height: `${v}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-6 col-md-3"><div class="co-info-cell"><span>Total Production</span><strong>12,700 kg</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="co-info-cell"><span>Arabica Share</span><strong class="co-green">72%</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="co-info-cell"><span>Avg Yield/ha</span><strong>2.4 t</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="co-info-cell"><span>Export Volume</span><strong>8,400 kg</strong></div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Traceability Pipeline -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><Van /></el-icon>
                                    Traceability Pipeline
                                </div>
                                <div class="co-section-body">
                                    <div class="co-trace-flow mb-3">
                                        <div v-for="(stage, i) in traceStages" :key="stage.label" class="co-trace-stage">
                                            <div class="co-trace-dot" :class="stage.complete ? 'co-trace-dot--done' : 'co-trace-dot--todo'">
                                                <el-icon><component :is="stage.icon" /></el-icon>
                                            </div>
                                            <div class="co-trace-val">{{ stage.value }}</div>
                                            <div class="co-trace-label">{{ stage.label }}</div>
                                            <div class="co-trace-status" :class="stage.complete ? 'co-green' : 'co-muted'">{{ stage.status }}</div>
                                            <div v-if="i < traceStages.length - 1" class="co-trace-line" :class="stage.complete ? 'co-trace-line--done' : ''"></div>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4"><div class="co-info-cell"><span>Total Harvests</span><strong>28</strong></div></div>
                                        <div class="col-4"><div class="co-info-cell"><span>Export-Ready Lots</span><strong class="co-green">{{ lots.filter(l => l.status === 'Export Ready').length }}</strong></div></div>
                                        <div class="col-4"><div class="co-info-cell"><span>Tokenised Lots</span><strong>0</strong></div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Available Coffee Lots -->
                            <div class="co-section p-0 overflow-hidden">
                                <div class="co-section-head px-3">
                                    <el-icon class="co-section-icon"><ShoppingCart /></el-icon>
                                    Available Coffee Lots
                                    <span class="ms-auto co-section-stat">{{ lots.length }} lots</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 co-table">
                                        <thead>
                                            <tr>
                                                <th><span class="co-th"><el-icon><CollectionTag /></el-icon> Lot ID</span></th>
                                                <th><span class="co-th"><el-icon><Coffee /></el-icon> Coffee Type</span></th>
                                                <th><span class="co-th"><el-icon><Location /></el-icon> Origin</span></th>
                                                <th><span class="co-th"><el-icon><Medal /></el-icon> Quality</span></th>
                                                <th><span class="co-th"><el-icon><Box /></el-icon> Quantity</span></th>
                                                <th><span class="co-th"><el-icon><PriceTag /></el-icon> Price</span></th>
                                                <th><span class="co-th"><el-icon><TrendCharts /></el-icon> Demand</span></th>
                                                <th><span class="co-th"><el-icon><Checked /></el-icon> Status</span></th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in lots" :key="lot.id" class="co-table-row">
                                                <td class="co-item-name">{{ lot.id }}</td>
                                                <td><span class="co-tag">{{ lot.type }}</span></td>
                                                <td class="co-muted">{{ lot.origin }}</td>
                                                <td>
                                                    <span class="co-score-pill" :class="lot.score >= 88 ? 'co-score-pill--high' : 'co-score-pill--mid'">{{ lot.score }}</span>
                                                </td>
                                                <td class="co-muted">{{ lot.qty }}</td>
                                                <td class="fw-semibold">{{ lot.price }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="lot.demand === 'High' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : lot.demand === 'Extreme' ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                              : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                        {{ lot.demand }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="lot.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : lot.tone === 'primary' ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'
                                                              : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                        {{ lot.status }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm co-btn-outline co-act-btn"><el-icon><View /></el-icon></button>
                                                        <button class="btn btn-sm co-btn-primary co-act-btn"><el-icon><ShoppingCart /></el-icon> Buy</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 9. Export & Market Readiness -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><DataLine /></el-icon>
                                    Export &amp; Market Readiness
                                </div>
                                <div class="co-section-body">
                                    <div class="d-flex flex-column gap-3">
                                        <div v-for="m in markets" :key="m.region">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="fw-semibold" style="font-size:.8125rem;">{{ m.region }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="co-muted" style="font-size:.75rem;">{{ m.price }}</span>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="m.demand === 'Extreme' ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                              : m.demand === 'High' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                        {{ m.demand }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="co-bar-track">
                                                <div class="co-bar-fill" :style="{ width: `${m.pct}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Activity Timeline -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><Clock /></el-icon>
                                    Activity Timeline
                                </div>
                                <div class="co-section-body">
                                    <div class="co-timeline">
                                        <div v-for="(act, i) in timeline" :key="i" class="co-timeline-item">
                                            <div class="co-timeline-dot" :class="`co-timeline-dot--${act.tone}`">
                                                <el-icon><component :is="act.icon" /></el-icon>
                                            </div>
                                            <div class="co-timeline-body">
                                                <div class="co-item-name">{{ act.text }}</div>
                                                <div class="co-muted" style="font-size:.6875rem; margin-top:2px;">{{ act.date }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="co-rail">

                            <!-- Contact Info -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><User /></el-icon>
                                    Institutional Contact
                                </div>
                                <div class="co-section-body">
                                    <div class="co-item-name mb-1">{{ props.cooperative.contact_person || 'Operations Lead' }}</div>
                                    <div class="co-muted mb-3" style="font-size:.75rem;">General Manager</div>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="co-contact-row"><el-icon><ChatDotRound /></el-icon><span>{{ props.cooperative.telephone || '+256 700 000000' }}</span></div>
                                        <div class="co-contact-row"><el-icon><Download /></el-icon><span>{{ props.cooperative.email || 'Email not provided' }}</span></div>
                                        <div class="co-contact-row"><el-icon><Location /></el-icon><span>{{ props.cooperative.address || 'Primary field office' }}</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Quality Profile -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><Medal /></el-icon>
                                    Quality Profile
                                </div>
                                <div class="co-section-body">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="co-score-circle">87.4</div>
                                        <div>
                                            <div style="font-size:.8125rem; font-weight:700;">Avg Cupping Score</div>
                                            <div class="co-muted" style="font-size:.7rem;">Specialty Grade (85+)</div>
                                            <div class="co-muted" style="font-size:.7rem;">Defect Rate: 0.6%</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="q in qualityProfile" :key="q.attribute">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span style="font-size:.8125rem; font-weight:600;">{{ q.attribute }}</span>
                                                <span class="co-green" style="font-size:.8125rem; font-weight:700;">{{ q.display }}</span>
                                            </div>
                                            <div class="co-bar-track">
                                                <div class="co-bar-fill" :style="{ width: `${q.score}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8b. Sustainability -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><Checked /></el-icon>
                                    Sustainability
                                </div>
                                <div class="co-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="s in sustainability" :key="s.label" class="co-sus-row">
                                            <el-icon style="font-size:13px; color:var(--green);"><component :is="s.icon" /></el-icon>
                                            <span class="flex-fill" style="font-size:.8125rem;">{{ s.label }}</span>
                                            <strong class="co-green" style="font-size:.8125rem;">{{ s.value }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Infrastructure -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><Box /></el-icon>
                                    Infrastructure &amp; Facilities
                                </div>
                                <div class="co-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="f in facilities" :key="f.name" class="co-facility-row">
                                            <div class="flex-fill">
                                                <div class="co-item-name">{{ f.name }}</div>
                                                <div class="co-muted" style="font-size:.75rem;">{{ f.count }}</div>
                                            </div>
                                            <span class="badge rounded-pill" style="font-size:.65rem;"
                                                :class="f.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'">
                                                {{ f.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Documents -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><Download /></el-icon>
                                    Documents &amp; Compliance
                                </div>
                                <div class="co-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="doc in documents" :key="doc.name" class="co-doc-row">
                                            <span class="flex-fill" style="font-size:.8125rem;">{{ doc.name }}</span>
                                            <span class="badge rounded-pill" style="font-size:.65rem; flex-shrink:0;"
                                                :class="doc.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                      : doc.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                      : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                {{ doc.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. AI Insights -->
                            <div>
                                <div class="co-section-head co-section-head--borderless mb-2">
                                    <el-icon class="co-section-icon"><Opportunity /></el-icon>
                                    AI Cooperative Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="co-insight-card" :class="`co-insight-card--${ins.tone}`">
                                        <el-icon class="co-insight-icon"><Opportunity /></el-icon>
                                        <p class="co-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. Smart Alerts -->
                            <div class="co-section">
                                <div class="co-section-head">
                                    <el-icon class="co-section-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="co-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="(val, key) in alerts" :key="key" class="co-alert-row">
                                            <span style="font-size:.8125rem;">{{ { newLots: 'New Lots Available', exportReady: 'Export Readiness Changes', qualityAlerts: 'Quality Alerts', buyerInterest: 'Buyer Interest', certExpiry: 'Certification Expiry' }[key] }}</span>
                                            <button class="co-toggle" :class="{ 'co-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 15. Floating Chatbot ───────────────────────────────── -->
            <div class="co-fab-wrap">
                <Transition name="co-chat">
                    <div v-if="chatOpen" class="co-chatbot">
                        <div class="co-chatbot__head">
                            <div class="co-chatbot__identity">
                                <div class="co-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="co-chatbot__name">Cooperative Advisor</div>
                                    <div class="co-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="co-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="co-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="co-chat-msg" :class="`co-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="co-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="co-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="co-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="co-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.co-page {
    --green:   #004532;
    --border:  #e5e7eb;
    --border-m:#d1d5db;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-low:    #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}
.co-green  { color: #166534; font-weight: 700; }
.co-muted  { color: var(--on-surface-var); }
.co-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Header ────────────────────────────────────────────────────────────────── */
.co-header   { background: #fff; border-bottom: 1px solid var(--border); }
.co-flash    { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 8px 12px; color: #166534; font-size: .8125rem; font-weight: 600; margin-top: 8px; }
.co-kicker   { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.co-title    { font-size: 1.1875rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.25; margin-top: 6px !important; margin-bottom: 6px !important; }
.co-subtitle { font-size: .8125rem; color: var(--on-surface-var); }
.co-badge    { display: inline-flex; align-items: center; border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 3px 10px; }
.co-badge--green { background: rgba(0,69,50,.08); color: var(--green); }
.co-badge--blue  { background: #dbeafe; color: #1e40af; }
.co-badge--amber { background: #fef3c7; color: #92400e; }
.co-badge--soft  { background: #dcfce7; color: #166534; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.co-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.co-btn-primary:hover { background: #065f46; color: #fff; }
.co-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.co-btn-outline:hover { background: var(--surface-low); }
.co-act-btn { font-size: .75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }

/* ── Hero section ──────────────────────────────────────────────────────────── */
.co-section { background: #fff; border: 1px solid var(--border); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 2px rgba(15,23,42,.03), 0 12px 28px -18px rgba(15,23,42,.14); }
.co-section-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--surface-low);
    border-bottom: 1px solid var(--border);
    border-radius: 13px 13px 0 0;
    font-size: .8125rem;
    font-weight: 700;
    color: var(--on-surface);
    letter-spacing: -.005em;
}
.co-section-head--borderless { display: flex; align-items: center; gap: 8px; font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.co-section-icon { width: 22px; height: 22px; border-radius: 6px; background: rgba(0,69,50,.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.co-section-stat { font-size: .6875rem; font-weight: 700; background: var(--border); color: var(--on-surface-var); border-radius: 999px; padding: 2px 8px; font-variant-numeric: tabular-nums; }
.co-section-stat--green { background: #dcfce7; color: #166534; }
.co-section-body { padding: 1rem; }

/* Hero identity / cols */
.co-identity-col { padding: 1.125rem; border-right: 1px solid var(--border); display: flex; flex-direction: column; }
.co-prod-col, .co-trust-col { padding: 1.125rem; }
.co-prod-col { border-right: 1px solid var(--border); }
.co-identity-avatar { width: 48px; height: 48px; border: 1.5px solid var(--border-m); border-radius: 10px; background: var(--surface-low); color: var(--on-surface-var); font-weight: 800; font-size: 1.125rem; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
.co-identity-name { font-size: 1rem; font-weight: 800; color: var(--on-surface); margin-bottom: 4px; }
.co-col-label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); margin-bottom: 10px; }
.co-kpi-list { display: flex; flex-direction: column; }
.co-kpi-row  { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-size: .8125rem; }
.co-kpi-row:last-child { border-bottom: none; }
.co-kpi-row span { color: var(--on-surface-var); }
.co-kpi-row strong { color: var(--on-surface); font-weight: 700; font-variant-numeric: tabular-nums; }
.co-tag { display: inline-flex; background: rgba(0,69,50,.07); color: var(--green); border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; }
.co-tag--blue { background: #dbeafe; color: #1e40af; }
.co-tag--amber{ background: #fef3c7; color: #92400e; }

/* ── Map ───────────────────────────────────────────────────────────────────── */
.co-map-tile { min-height: 130px; border: 1.5px dashed var(--border-m); border-radius: 8px; background: var(--surface-low); position: relative; display: flex; align-items: flex-end; padding: 12px; overflow: hidden; }
.co-map-pin  { position: absolute; top: 50%; left: 50%; width: 28px; height: 28px; border-radius: 50% 50% 50% 0; background: var(--green); transform: translate(-50%, -70%) rotate(-45deg); }
.co-map-pin::after { content: ''; position: absolute; width: 10px; height: 10px; border-radius: 50%; background: #fff; top: 9px; left: 9px; }
.co-map-info { position: relative; z-index: 1; }
.co-map-tags { position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 4px; }
.co-map-tag  { background: rgba(0,69,50,.08); color: var(--green); border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 7px; }

/* ── Info grid ─────────────────────────────────────────────────────────────── */
.co-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.co-info-cell { border: 1px solid var(--border); border-radius: 6px; padding: 6px 8px; }
.co-info-cell span   { font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; margin-bottom: 2px; }
.co-info-cell strong { font-size: .8125rem; font-weight: 700; color: var(--on-surface); display: block; font-variant-numeric: tabular-nums; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.co-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.co-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; font-variant-numeric: tabular-nums; }
.co-table-row:last-child td { border-bottom: none; }
.co-table-row:hover { background: var(--surface-low); }
.co-th { display: inline-flex; align-items: center; gap: 5px; }
.co-th :deep(.el-icon) { font-size: 13px; color: #9ca3af; }
.co-farmer-dot { width: 28px; height: 28px; border-radius: 7px; border: 1px solid var(--border); background: var(--surface-low); color: var(--on-surface-var); font-weight: 700; font-size: .75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.co-score-pill { display: inline-flex; border-radius: 999px; font-size: .6875rem; font-weight: 800; padding: 2px 8px; font-variant-numeric: tabular-nums; }
.co-score-pill--high { background: #dcfce7; color: #166534; }
.co-score-pill--mid  { background: #fef3c7; color: #92400e; }

/* ── Charts ────────────────────────────────────────────────────────────────── */
.co-chart { height: 80px; display: flex; align-items: flex-end; gap: 2px; border: 1px solid var(--border); border-radius: 6px; padding: 5px; background: var(--surface-low); }
.co-chart-bar { flex: 1; border-radius: 2px 2px 0 0; }
.co-chart-bar--prod { background: var(--green); opacity: .8; }
.co-chart-bar--qual { background: #059669; opacity: .8; }

/* ── Traceability ──────────────────────────────────────────────────────────── */
.co-trace-flow { display: flex; justify-content: space-between; align-items: flex-start; position: relative; }
.co-trace-stage { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; position: relative; z-index: 1; }
.co-trace-dot { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 13px; background: #fff; }
.co-trace-dot--done { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
.co-trace-dot--todo { color: var(--on-surface-var); }
.co-trace-val   { font-size: .875rem; font-weight: 800; color: var(--on-surface); font-variant-numeric: tabular-nums; }
.co-trace-label { font-size: .6875rem; font-weight: 700; color: var(--on-surface); }
.co-trace-status{ font-size: .625rem; }
.co-trace-line  { position: absolute; top: 17px; left: 50%; width: 100%; height: 1px; background: var(--border); z-index: 0; }
.co-trace-line--done { background: #16a34a; }

/* ── Progress bars ─────────────────────────────────────────────────────────── */
.co-bar-track { height: 5px; background: var(--border); border-radius: 999px; overflow: hidden; }
.co-bar-fill  { height: 100%; background: var(--green); border-radius: 999px; transition: width .5s ease; }

/* ── Score circle ──────────────────────────────────────────────────────────── */
.co-score-circle { width: 52px; height: 52px; border-radius: 50%; border: 2px solid #16a34a; background: #f0fdf4; color: #166534; font-size: 1rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-variant-numeric: tabular-nums; }

/* ── Contact ───────────────────────────────────────────────────────────────── */
.co-contact-row { display: flex; align-items: flex-start; gap: 8px; font-size: .8125rem; color: var(--on-surface-var); padding: 4px 0; }

/* ── Sustainability ────────────────────────────────────────────────────────── */
.co-sus-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid var(--border); font-size: .8125rem; }
.co-sus-row:last-child { border-bottom: none; }

/* ── Facilities ────────────────────────────────────────────────────────────── */
.co-facility-row { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px solid var(--border); }
.co-facility-row:last-child { border-bottom: none; }

/* ── Documents ─────────────────────────────────────────────────────────────── */
.co-doc-row { display: flex; align-items: center; gap: 8px; padding: 7px 0; border-bottom: 1px solid var(--border); }
.co-doc-row:last-child { border-bottom: none; }

/* ── Timeline ──────────────────────────────────────────────────────────────── */
.co-timeline { display: flex; flex-direction: column; }
.co-timeline-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--border); }
.co-timeline-item:last-child { border-bottom: none; }
.co-timeline-dot { width: 26px; height: 26px; border-radius: 6px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.co-timeline-dot--success { border-color: #86efac; background: #f0fdf4; color: #166534; }
.co-timeline-dot--primary { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }
.co-timeline-dot--warning { border-color: #fcd34d; background: #fffbeb; color: #92400e; }
.co-timeline-dot--info    { border-color: #7dd3fc; background: #f0f9ff; color: #0369a1; }
.co-timeline-body { flex: 1; min-width: 0; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.co-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: .875rem; border-radius: 8px; border: 1px solid; }
.co-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.co-insight-card--primary { background: #eff6ff; border-color: #bfdbfe; }
.co-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.co-insight-icon { font-size: 13px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.co-insight-text { font-size: .8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.co-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--border); }
.co-alert-row:last-child { border-bottom: none; }
.co-toggle { width: 32px; height: 18px; border-radius: 999px; border: 1px solid var(--border); padding: 2px; background: #fff; cursor: pointer; transition: background .2s; flex-shrink: 0; }
.co-toggle i { display: block; width: 12px; height: 12px; border-radius: 50%; background: var(--border); transition: transform .2s; }
.co-toggle--on { background: var(--green); border-color: var(--green); }
.co-toggle--on i { background: #fff; transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.co-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.co-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: .75rem; }
.co-fab { width: 46px; height: 46px; border-radius: 50%; border: 1.5px solid var(--green); background: var(--green); color: #fff; font-size: 18px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.co-fab:hover { background: #065f46; }
.co-chatbot { width: 310px; border-radius: 10px; overflow: hidden; background: #fff; border: 1px solid var(--border); display: flex; flex-direction: column; }
.co-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.co-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.co-chatbot__avatar { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 13px; }
.co-chatbot__name { font-size: .875rem; font-weight: 700; }
.co-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: .625rem; opacity: .8; }
.co-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.co-chatbot__close { border: none; background: none; color: rgba(255,255,255,.8); font-size: 20px; line-height: 1; cursor: pointer; }
.co-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 190px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.co-chat-msg { font-size: .8125rem; padding: 8px 10px; border-radius: 8px; line-height: 1.5; max-width: 90%; }
.co-chat-msg--bot  { background: #fff; color: var(--on-surface); border: 1px solid var(--border); border-radius: 8px 8px 8px 2px; }
.co-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 8px 8px 2px 8px; }
.co-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 4px; padding: 7px 10px; border-top: 1px solid var(--border); }
.co-prompt-chip { font-size: .6rem; padding: 3px 8px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.co-prompt-chip:hover { background: #dcfce7; }
.co-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--border); }
.co-chatbot__input input { flex: 1; border: 1px solid var(--border); border-radius: 6px; padding: 6px 9px; font-size: .8125rem; outline: none; }
.co-chatbot__input input:focus { border-color: var(--green); }
.co-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 6px; width: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px; }
.co-chat-enter-active, .co-chat-leave-active { transition: opacity .2s ease, transform .2s ease; }
.co-chat-enter-from, .co-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .co-rail { position: static; } }
@media (max-width: 991.98px)  { .co-identity-col, .co-prod-col { border-right: none; border-bottom: 1px solid var(--border); } }
@media (max-width: 767.98px)  {
    .co-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
    .co-info-grid { grid-template-columns: 1fr; }
}
</style>
