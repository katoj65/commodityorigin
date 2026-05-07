<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, DataLine, Download,
    Location, Medal, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    farm: { type: Object, required: true },
});

/* ── Real computed from prop ───────────────────────────────────── */
const farmName    = computed(() => props.farm.name || 'Farm Profile');
const farmerName  = computed(() => [props.farm.farmer?.first_name, props.farm.farmer?.last_name].filter(Boolean).join(' ') || 'Assigned Producer');
const locationLabel = computed(() =>
    props.farm.location ||
    [props.farm.farmer?.sub_county, props.farm.farmer?.district, 'Uganda'].filter(Boolean).join(', ') ||
    'Origin pending',
);
const estateBrief = computed(() =>
    props.farm.notes ||
    `${farmName.value} is managed for traceable coffee production with structured harvesting, post-harvest discipline, and field-level quality monitoring.`,
);
const altitudeRange = computed(() => props.farm.altitude || '1,950m – 2,100m');
const farmSize      = computed(() => props.farm.size || '—');
const variety       = computed(() => props.farm.variety || props.farm.farmer?.coffee_type || 'Arabica');
const rainfall      = computed(() => props.farm.rainfall    || (props.farm.altitude ? '1,850 mm' : '1,620 mm'));
const temperature   = computed(() => props.farm.temperature || (props.farm.altitude ? '18.5°C – 24.2°C' : '20.1°C – 26.8°C'));
const humidityIndex = computed(() => props.farm.humidity    || (props.farm.status?.toLowerCase() === 'active' ? '62%' : '58%'));
const soilType      = computed(() => props.farm.soil_type     || '—');
const climaticZone  = computed(() => props.farm.climatic_zone || '—');
const bagsEstimate  = computed(() => {
    if (props.farm.total_bags_produced) return `${props.farm.total_bags_produced.toLocaleString()} Bags`;
    const d = String(props.farm.size || '').match(/\d+/);
    return d?.[0] ? `${d[0]} Bags` : '—';
});
const thumb = computed(() =>
    [props.farm.name?.[0], props.farm.variety?.[0]].filter(Boolean).join('').slice(0, 2).toUpperCase() || 'ES',
);
const isRobusta = computed(() => variety.value.toLowerCase().includes('robusta'));
const qualityScore = computed(() => isRobusta.value ? 84.3 : 88.5);

const harvestRows = computed(() => [
    { id: `HV-${String(props.farm.id).padStart(3,'0')}-01`, season: '2024/25', date: 'Mar 20', qty: '2,400 kg', score: isRobusta.value ? 84.3 : 88.5, status: 'Processed',  tone: 'success' },
    { id: `HV-${String(props.farm.id).padStart(3,'0')}-02`, season: '2024/25', date: 'Feb 15', qty: '1,800 kg', score: isRobusta.value ? 83.8 : 87.9, status: 'In Process', tone: 'warning' },
    { id: `HV-${String(props.farm.id).padStart(3,'0')}-03`, season: '2024/25', date: 'Jan 22', qty: '1,200 kg', score: isRobusta.value ? 84.0 : 87.8, status: 'Sold',        tone: 'primary' },
]);

/* ── Static / mock data for new sections ───────────────────────── */
const productionBars = [52, 64, 68, 78, 74, 82, 88, 84, 90, 86, 92, 88];
const qualityBars    = [72, 78, 76, 82, 80, 86, 84, 88, 86, 90, 89, 92];

const qualityProfile = [
    { attribute: 'Aroma',       score: 88, display: '8.8/10' },
    { attribute: 'Flavor',      score: 86, display: '8.6/10' },
    { attribute: 'Acidity',     score: 90, display: '9.0/10' },
    { attribute: 'Body',        score: 84, display: '8.4/10' },
    { attribute: 'Aftertaste',  score: 87, display: '8.7/10' },
    { attribute: 'Balance',     score: 85, display: '8.5/10' },
];

const traceStages = [
    { label: 'Farm',    icon: Location,      status: 'Verified',   date: 'Oct 2024', complete: true  },
    { label: 'Harvest', icon: Box,           status: 'Recorded',   date: 'Mar 2025', complete: true  },
    { label: 'Batch',   icon: CollectionTag, status: 'Processing', date: 'Apr 2025', complete: false },
    { label: 'Lot',     icon: Star,          status: 'Pending',    date: '—',        complete: false },
    { label: 'Market',  icon: ShoppingCart,  status: 'Ready',      date: '—',        complete: false },
];

const processing = [
    { method: 'Washed',        drying: 'Raised Beds',  storage: 'Grain Pro Bags', capacity: '6,000 kg', badge: 'Export Grade', tone: 'success' },
    { method: 'Natural',       drying: 'Sun Dried',    storage: 'Warehouse',      capacity: '2,400 kg', badge: 'Traditional',  tone: 'warning' },
];

const sustainability = [
    { label: 'Shade Farming',          value: '94%',       icon: Star   },
    { label: 'Water Usage',            value: 'Low Impact', icon: Check  },
    { label: 'Carbon Footprint',       value: '<0.62kg/kg', icon: Check  },
    { label: 'Soil Management',        value: 'Organic',    icon: Medal  },
    { label: 'Climate-Smart Practices',value: 'Active',     icon: Checked},
];

const lots = [
    { name: `${farmName.value} Natural Lot 1`, type: variety.value, score: qualityScore.value, price: '$4.80–5.20', qty: '1,200 kg', demand: 'High',   badges: ['Verified','Export Ready'], tone: 'success' },
    { name: `${farmName.value} Washed Lot 2`,  type: variety.value, score: qualityScore.value - 1, price: '$4.40–4.80', qty: '900 kg',   demand: 'Medium', badges: ['Verified'],               tone: 'warning' },
];

const buyerMarkets = [
    { region: 'Germany / EU', demand: 'High',   price: '$5.00–5.40', pct: 85, tone: 'success' },
    { region: 'UAE',          demand: 'Extreme', price: '$1.80–2.10', pct: 96, tone: 'danger'  },
    { region: 'USA',          demand: 'High',    price: '$4.80–5.20', pct: 78, tone: 'success' },
    { region: 'Japan',        demand: 'Medium',  price: '$5.20–5.80', pct: 62, tone: 'warning' },
];

const documents = [
    { name: 'Farm Registration Certificate', status: 'Complete', tone: 'success' },
    { name: 'Cupping Report Q1 2025',        status: 'Complete', tone: 'success' },
    { name: 'Organic Certification',         status: 'Complete', tone: 'success' },
    { name: 'Export Compliance File',        status: 'Pending',  tone: 'warning' },
    { name: 'Phytosanitary Certificate',     status: 'Pending',  tone: 'warning' },
];

const timeline = [
    { icon: Location,      text: 'Farm registered on Bean Origin',              date: 'Jan 2024',  tone: 'success' },
    { icon: Star,          text: 'Season 2024/25 created and activated',        date: 'Oct 2024',  tone: 'primary' },
    { icon: Box,           text: 'Harvest HV-001 recorded — 2,400 kg',          date: 'Mar 2025',  tone: 'success' },
    { icon: CollectionTag, text: 'Batch created — processing stage',            date: 'Apr 2025',  tone: 'warning' },
    { icon: ShoppingCart,  text: 'First lot submitted to market',               date: 'Apr 2025',  tone: 'info'   },
    { icon: Van,           text: 'Lot 1 sold — Nordic Roasters, Sweden',        date: 'May 2025',  tone: 'primary' },
];

const insights = [
    { text: 'This farm shows strong specialty Arabica potential with consistent 88+ cupping scores.', tone: 'success' },
    { text: 'Harvest consistency is improving season over season — up 12% vs 2023/24.',               tone: 'primary' },
    { text: 'Export demand is increasing for this farm\'s coffee profile in EU and UAE markets.',     tone: 'warning' },
];

const alerts = reactive({ harvestUpdates: true, qualityAlerts: true, buyerInterest: false, exportUpdates: true });

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: `Hi! I'm your Bean Origin Farm Advisor. I can help you understand ${farmName.value}'s production quality, export potential, and available lots. What would you like to know?` },
]);
const prompts = ['Is this farm high quality?', "What is this farm's export potential?", 'Which lots come from this farm?', 'How can quality be improved?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: `${farmName.value} is performing excellently with a quality score of ${qualityScore.value}. It is export-ready with ${lots.length} active lots and strong buyer interest from EU and UAE markets.` }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
const data=computed(()=>props.farm);



</script>

<template>
    <AppLayout :title="farmName" full-width flush :show-banner="false">
        <div class="fp-page">

            <!-- ── 1. Sticky Header ───────────────────────────────────── -->
            <div class="fp-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="fp-header-kicker">Farm Profile</div>
                            <h1 class="fp-title mb-0">{{ farmName }}</h1>
                            <p class="fp-subtitle mb-0">Verified coffee farm with traceable production data</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn fp-btn-outline btn-sm"><el-icon><Box /></el-icon> View Harvests</button>
                            <button class="btn fp-btn-outline btn-sm"><el-icon><CollectionTag /></el-icon> View Lots</button>
                            <button class="btn fp-btn-primary btn-sm"><el-icon><ShoppingCart /></el-icon> Contact Farm</button>
                            <button class="btn fp-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 pb-2">
                        <span class="fp-hbadge"><el-icon><Checked /></el-icon> Verified Farm</span>
                        <span class="fp-hbadge fp-hbadge--soft"><el-icon><Check /></el-icon> Traceable Production</span>
                        <span class="fp-hbadge fp-hbadge--blue"><el-icon><Van /></el-icon> Export Ready</span>
                        <span class="fp-hbadge fp-hbadge--amber"><el-icon><Star /></el-icon> Sustainable</span>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Hero Overview Card ──────────────────────────── -->
                <div class="fp-hero-card mb-3">
                    <div class="row g-0 align-items-stretch">
                        <!-- Left: Identity -->
                        <div class="col-12 col-md-4 fp-hero-identity">
                            <div class="fp-farm-avatar-lg">{{ thumb }}</div>
                            <div class="fp-hero-name">{{ farmName }}</div>

                            <div class="d-flex align-items-center gap-1 mb-2">
                                <el-icon style="font-size:12px; color:var(--on-surface-var);"><Location /></el-icon>
                                <span class="fp-td-muted" style="font-size:.8125rem;">{{ locationLabel }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">Verified</span>
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle" style="font-size:.65rem;">Export Ready</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">Organic</span>
                            </div>
                            <!-- <div class="fp-contact-row">
                                <span class="fp-td-muted" style="font-size:.75rem;">{{ props.farm.farmer?.email || 'producer@exchange.ug' }}</span>
                            </div> -->
                            <!-- <div class="fp-contact-row">
                                <span class="fp-td-muted" style="font-size:.75rem;">{{ props.farm.farmer?.telephone || '+256 700 000000' }}</span>
                            </div> -->
                        </div>

                        <!-- Center: Specs -->
                        <div class="col-12 col-md-4 fp-hero-specs">
                            <div class="fp-specs-title">Farm Specifications</div>
                            <div class="row g-2">
                                <div class="col-6"><div class="fp-spec-cell"><span>Farm Size</span><strong>{{ farmSize }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Altitude</span><strong>{{ altitudeRange }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Coffee Type</span><strong>{{ variety }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Total Bags</span><strong>{{ bagsEstimate }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Rainfall</span><strong>{{ rainfall }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Temperature</span><strong>{{ temperature }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Humidity</span><strong>{{ humidityIndex }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Status</span><strong class="fp-up">{{ props.farm.status || 'Active' }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Soil Type</span><strong>{{ soilType }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Climatic Zone</span><strong>{{ climaticZone }}</strong></div></div>
                            </div>
                        </div>

                        <!-- Right: Performance -->
                        <div class="col-12 col-md-4 fp-hero-perf">
                            <div class="fp-specs-title">Performance &amp; Trust</div>
                            <div class="fp-quality-ring-wrap mb-3">
                                <div class="fp-quality-ring">
                                    <svg viewBox="0 0 80 80" width="100" height="100">
                                        <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                        <circle cx="40" cy="40" r="34" fill="none" stroke="#004532" stroke-width="8"
                                            stroke-dasharray="213.6"
                                            :stroke-dashoffset="213.6 - (213.6 * ((qualityScore - 80) / 20))"
                                            stroke-linecap="round"
                                            transform="rotate(-90 40 40)"/>
                                    </svg>
                                    <div class="fp-quality-ring-inner">
                                        <div class="fp-quality-score">{{ qualityScore }}</div>
                                        <div class="fp-quality-label">SCA Score</div>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <span class="badge bg-success text-white mb-1 d-block" style="font-size:.65rem;">Specialty Grade</span>
                                    <span class="badge bg-primary text-white mb-1 d-block" style="font-size:.65rem;">Export Quality</span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle d-block" style="font-size:.65rem;">Premium</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="fp-perf-row"><span>Yield Efficiency</span><strong class="fp-up">88%</strong></div>
                                <div class="fp-perf-row"><span>Export Readiness</span><strong class="fp-up">92%</strong></div>
                                <div class="fp-perf-row"><span>Verification</span><strong class="fp-up">Verified ✓</strong></div>
                                <div class="fp-perf-row"><span>Defect Rate</span><strong>0.8%</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 3. Farm Location & Map -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Location /></el-icon>
                                    Farm Location &amp; Environment
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-7">
                                        <div class="fp-map-tile">
                                            <div class="fp-map-pin"></div>
                                            <div class="fp-map-coords">
                                                <div class="fw-semibold" style="font-size:.8125rem;">{{ props.farm.latitude || '0.3476' }}°N, {{ props.farm.longitude || '34.1162' }}°E</div>
                                                <div class="fp-td-muted" style="font-size:.7rem;">GPS coordinates — {{ locationLabel }}</div>
                                            </div>
                                            <div class="fp-map-badges">
                                                <span class="fp-map-badge">{{ altitudeRange }}</span>
                                                <span class="fp-map-badge fp-map-badge--blue">Highland Climate</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <div class="d-flex flex-column gap-2 h-100 justify-content-center">
                                            <div class="fp-env-row"><span>Annual Rainfall</span><strong>{{ rainfall }}</strong></div>
                                            <div class="fp-env-row"><span>Temperature</span><strong>{{ temperature }}</strong></div>
                                            <div class="fp-env-row"><span>Humidity Index</span><strong>{{ humidityIndex }}</strong></div>
                                            <div class="fp-env-row"><span>Soil Type</span><strong>{{ soilType }}</strong></div>
                                            <div class="fp-env-row"><span>Climate Zone</span><strong>{{ climaticZone }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Production Overview -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><TrendCharts /></el-icon>
                                    Production Overview
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="fp-td-muted mb-1" style="font-size:.75rem;">Annual Production Trend</div>
                                        <div class="fp-chart">
                                            <div class="fp-chart-bars">
                                                <div v-for="(v, i) in productionBars" :key="i" class="fp-chart-col">
                                                    <div class="fp-chart-bar fp-chart-bar--prod" :style="{ height: `${v}%` }"></div>
                                                    <span class="fp-chart-lbl">{{ ['J','F','M','A','M','J','J','A','S','O','N','D'][i] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="fp-td-muted mb-1" style="font-size:.75rem;">Quality Trend</div>
                                        <div class="fp-chart">
                                            <div class="fp-chart-bars">
                                                <div v-for="(v, i) in qualityBars" :key="i" class="fp-chart-col">
                                                    <div class="fp-chart-bar fp-chart-bar--qual" :style="{ height: `${v}%` }"></div>
                                                    <span class="fp-chart-lbl">{{ ['J','F','M','A','M','J','J','A','S','O','N','D'][i] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-6 col-md-3"><div class="fp-spec-cell"><span>Avg Production</span><strong>5,400 kg/yr</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fp-spec-cell"><span>Yield / ha</span><strong>2.4 t</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fp-spec-cell"><span>Active Season</span><strong>2024/25</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fp-spec-cell"><span>Coffee Type Split</span><strong>{{ variety }} 100%</strong></div></div>
                                </div>
                            </div>

                            <!-- 5. Harvest History -->
                            <div class="fp-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="fp-card-title">
                                        <el-icon class="fp-card-icon"><Box /></el-icon>
                                        Harvest History
                                    </div>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ harvestRows.length }} harvests</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fp-table">
                                        <thead>
                                            <tr>
                                                <th>Harvest ID</th>
                                                <th>Season</th>
                                                <th>Date</th>
                                                <th>Quantity</th>
                                                <th>Quality Score</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="h in harvestRows" :key="h.id" class="fp-table-row">
                                                <td class="fp-item-name">{{ h.id }}</td>
                                                <td class="fp-td-muted">{{ h.season }}</td>
                                                <td class="fp-td-muted">{{ h.date }}</td>
                                                <td class="fw-semibold">{{ h.qty }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="fp-score-pill" :class="h.score >= 88 ? 'fp-score-pill--high' : 'fp-score-pill--mid'">{{ h.score }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="h.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : h.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'">
                                                        {{ h.status }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm fp-btn-outline fp-act-btn"><el-icon><View /></el-icon> View</button>
                                                        <button class="btn btn-sm fp-btn-ghost fp-act-btn"><el-icon><CollectionTag /></el-icon> Batch</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 6. Farm-to-Market Traceability -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Van /></el-icon>
                                    Farm-to-Market Traceability
                                </div>
                                <div class="fp-trace-flow">
                                    <div v-for="(stage, i) in traceStages" :key="stage.label" class="fp-trace-stage">
                                        <div class="fp-trace-dot" :class="stage.complete ? 'fp-trace-dot--done' : 'fp-trace-dot--pending'">
                                            <el-icon><component :is="stage.icon" /></el-icon>
                                        </div>
                                        <div class="fp-trace-label">{{ stage.label }}</div>
                                        <div class="fp-trace-status" :class="stage.complete ? 'fp-up' : 'fp-td-muted'">{{ stage.status }}</div>
                                        <div class="fp-trace-date fp-td-muted">{{ stage.date }}</div>
                                        <div v-if="i < traceStages.length - 1" class="fp-trace-line" :class="stage.complete ? 'fp-trace-line--done' : ''"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Available Lots -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><ShoppingCart /></el-icon>
                                    Available Coffee Lots
                                </div>
                                <div class="row g-3">
                                    <div v-for="lot in lots" :key="lot.name" class="col-12 col-md-6">
                                        <div class="fp-lot-card h-100">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div class="fp-item-name">{{ lot.name }}</div>
                                                <div class="fp-score-pill" :class="lot.score >= 88 ? 'fp-score-pill--high' : 'fp-score-pill--mid'" style="flex-shrink:0;">{{ lot.score }}</div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                <span v-for="b in lot.badges" :key="b" class="badge rounded-pill" style="font-size:.6rem; padding:2px 7px;"
                                                    :class="b === 'Verified' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                          : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'">{{ b }}</span>
                                            </div>
                                            <div class="row g-1 mb-3">
                                                <div class="col-6"><div class="fp-spec-cell"><span>Type</span><strong>{{ lot.type }}</strong></div></div>
                                                <div class="col-6"><div class="fp-spec-cell"><span>Qty</span><strong>{{ lot.qty }}</strong></div></div>
                                                <div class="col-6"><div class="fp-spec-cell"><span>Price/kg</span><strong>{{ lot.price }}</strong></div></div>
                                                <div class="col-6"><div class="fp-spec-cell"><span>Demand</span>
                                                    <strong :class="lot.tone === 'success' ? 'fp-up' : 'fp-warn'">{{ lot.demand }}</strong>
                                                </div></div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn fp-btn-outline btn-sm flex-fill fp-act-btn"><el-icon><View /></el-icon> View Lot</button>
                                                <button class="btn fp-btn-primary btn-sm flex-fill fp-act-btn"><el-icon><ShoppingCart /></el-icon> Buy</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Buyer Interest & Demand -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><TrendCharts /></el-icon>
                                    Buyer Interest &amp; Demand
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="m in buyerMarkets" :key="m.region">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-semibold" style="font-size:.8125rem;">{{ m.region }}</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fp-td-muted" style="font-size:.75rem;">{{ m.price }}</span>
                                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                                    :class="m.tone === 'danger'  ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                          : m.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                          : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                    {{ m.demand }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="fp-bar-track">
                                            <div class="fp-bar-fill"
                                                :style="{ width: `${m.pct}%`,
                                                          background: m.tone === 'danger' ? '#ef4444' : m.tone === 'success' ? '#004532' : '#f59e0b' }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. Activity Timeline -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Clock /></el-icon>
                                    Activity Timeline
                                </div>
                                <div class="fp-timeline">
                                    <div v-for="(act, i) in timeline" :key="i" class="fp-timeline-item">
                                        <div class="fp-timeline-dot" :class="`fp-timeline-dot--${act.tone}`">
                                            <el-icon><component :is="act.icon" /></el-icon>
                                        </div>
                                        <div class="fp-timeline-body">
                                            <div class="fp-timeline-text">{{ act.text }}</div>
                                            <div class="fp-timeline-time">{{ act.date }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="fp-rail">

                            <!-- Farmer attached to farm -->
                            <div class="fp-card" v-if="props.farm.farmer">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Star /></el-icon>
                                    Farm Owner
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="fp-farmer-avatar">
                                        {{ (props.farm.farmer.first_name?.[0] || '') + (props.farm.farmer.last_name?.[0] || '') || '?' }}
                                    </div>
                                    <div>
                                        <div class="fp-item-name">{{ farmerName }}</div>
                                        <div class="fp-td-muted" style="font-size:.75rem;">Single origin producer</div>
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mt-1 d-inline-block" style="font-size:.6rem;">Verified</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-1 mb-3">
                                    <div class="fp-farmer-row" v-if="props.farm.farmer.district || props.farm.farmer.sub_county">
                                        <el-icon style="font-size:12px; color:var(--on-surface-var); flex-shrink:0;"><Location /></el-icon>
                                        <span>{{ [props.farm.farmer.sub_county, props.farm.farmer.district].filter(Boolean).join(', ') }}</span>
                                    </div>
                                    <div class="fp-farmer-row" v-if="props.farm.farmer.telephone">
                                        <el-icon style="font-size:12px; color:var(--on-surface-var); flex-shrink:0;"><ChatDotRound /></el-icon>
                                        <span>{{ props.farm.farmer.telephone }}</span>
                                    </div>
                                    <div class="fp-farmer-row" v-if="props.farm.farmer.email">
                                        <el-icon style="font-size:12px; color:var(--on-surface-var); flex-shrink:0;"><Promotion /></el-icon>
                                        <span>{{ props.farm.farmer.email }}</span>
                                    </div>
                                </div>
                                <div class="row g-1 mb-3">
                                    <div class="col-6" v-if="props.farm.farmer.coffee_type">
                                        <div class="fp-spec-cell"><span>Coffee Type</span><strong>{{ props.farm.farmer.coffee_type }}</strong></div>
                                    </div>
                                    <div class="col-6" v-if="props.farm.farmer.farm_size">
                                        <div class="fp-spec-cell"><span>Farm Size</span><strong>{{ props.farm.farmer.farm_size }}</strong></div>
                                    </div>
                                    <div class="col-12" v-if="props.farm.farmer.cooperative">
                                        <div class="fp-spec-cell"><span>Cooperative</span><strong>{{ props.farm.farmer.cooperative }}</strong></div>
                                    </div>
                                </div>
                                <button class="btn fp-btn-outline btn-sm w-100"><el-icon><ShoppingCart /></el-icon> Contact Farmer</button>
                            </div>

                            <!-- 7. Quality Profile -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Medal /></el-icon>
                                    Quality Profile
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="fp-quality-bubble">{{ qualityScore }}</div>
                                    <div>
                                        <div style="font-size:.75rem; font-weight:700; color:var(--on-surface);">Overall SCA Score</div>
                                        <div class="fp-td-muted" style="font-size:.7rem;">Specialty Grade (85+)</div>
                                        <div class="fp-td-muted" style="font-size:.7rem; margin-top:2px;">Defect Rate: 0.8%</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="q in qualityProfile" :key="q.attribute">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fp-cert-label">{{ q.attribute }}</span>
                                            <span class="fp-up" style="font-size:.8125rem; font-weight:700;">{{ q.display }}</span>
                                        </div>
                                        <div class="fp-bar-track">
                                            <div class="fp-bar-fill" :style="{ width: `${q.score}%`, background: '#004532' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Processing Facilities -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Box /></el-icon>
                                    Processing Facilities
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="p in processing" :key="p.method" class="fp-process-card">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="fp-item-name">{{ p.method }}</span>
                                            <span class="badge rounded-pill" style="font-size:.65rem;"
                                                :class="p.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                {{ p.badge }}
                                            </span>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-6"><div class="fp-spec-cell"><span>Drying</span><strong>{{ p.drying }}</strong></div></div>
                                            <div class="col-6"><div class="fp-spec-cell"><span>Capacity</span><strong>{{ p.capacity }}</strong></div></div>
                                            <div class="col-12"><div class="fp-spec-cell"><span>Storage</span><strong>{{ p.storage }}</strong></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Sustainability & Practices -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Star /></el-icon>
                                    Sustainability &amp; Practices
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="fp-sus-badge">Sustainable</span>
                                    <span class="fp-sus-badge fp-sus-badge--amber">Organic Practices</span>
                                    <span class="fp-sus-badge fp-sus-badge--blue">Eco Certified</span>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="s in sustainability" :key="s.label" class="fp-sus-row">
                                        <el-icon style="font-size:13px; color:var(--green);"><component :is="s.icon" /></el-icon>
                                        <span class="fp-cert-label flex-fill">{{ s.label }}</span>
                                        <strong class="fp-up" style="font-size:.8125rem;">{{ s.value }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Market Readiness -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Van /></el-icon>
                                    Market Readiness
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><div class="fp-spec-cell"><span>Export Lots</span><strong class="fp-up">{{ lots.length }}</strong></div></div>
                                    <div class="col-6"><div class="fp-spec-cell"><span>Avg Price</span><strong>$4.60–5.20</strong></div></div>
                                    <div class="col-6"><div class="fp-spec-cell"><span>Buyer Interest</span><strong class="fp-up">High</strong></div></div>
                                    <div class="col-6"><div class="fp-spec-cell"><span>Active Contracts</span><strong>1</strong></div></div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn fp-btn-primary btn-sm"><el-icon><ShoppingCart /></el-icon> View Lots</button>
                                    <button class="btn fp-btn-outline btn-sm"><el-icon><DataLine /></el-icon> Sell Coffee</button>
                                </div>
                            </div>

                            <!-- 13. Documents & Compliance -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Download /></el-icon>
                                    Documents &amp; Compliance
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="doc in documents" :key="doc.name" class="fp-doc-row">
                                        <div class="fp-cert-label flex-fill">{{ doc.name }}</div>
                                        <span class="badge rounded-pill" style="font-size:.65rem; flex-shrink:0;"
                                            :class="doc.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                            {{ doc.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- 15. AI Farm Insights -->
                            <div>
                                <div class="fp-card-title mb-2">
                                    <el-icon class="fp-card-icon"><Opportunity /></el-icon>
                                    AI Farm Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="fp-insight-card" :class="`fp-insight-card--${ins.tone}`">
                                        <el-icon class="fp-insight-icon"><Opportunity /></el-icon>
                                        <p class="fp-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 16. Smart Alerts -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="fp-alert-row">
                                        <span>{{ { harvestUpdates: 'Harvest Updates', qualityAlerts: 'Quality Alerts', buyerInterest: 'Buyer Interest Alerts', exportUpdates: 'Export Readiness Updates' }[key] }}</span>
                                        <button class="fp-toggle" :class="{ 'fp-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 17. Floating Chatbot ───────────────────────────────── -->
            <div class="fp-fab-wrap">
                <Transition name="fp-chat">
                    <div v-if="chatOpen" class="fp-chatbot">
                        <div class="fp-chatbot__head">
                            <div class="fp-chatbot__identity">
                                <div class="fp-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="fp-chatbot__name">Farm Advisor</div>
                                    <div class="fp-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="fp-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="fp-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="fp-chat-msg" :class="`fp-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="fp-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="fp-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="fp-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="fp-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.fp-page {
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
    min-height: 100%;
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.fp-header      { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.fp-header-kicker { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--green); margin-bottom: 2px; }
.fp-title       { font-size: 1.0625rem; font-weight: 800; letter-spacing: -0.02em; }
.fp-subtitle    { font-size: 0.8125rem; color: var(--on-surface-var); }
.fp-hbadge      { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fp-hbadge--soft { background: #dcfce7; color: #166534; }
.fp-hbadge--blue { background: #dbeafe; color: #1e40af; }
.fp-hbadge--amber{ background: #fef3c7; color: #92400e; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.fp-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.fp-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.fp-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fp-btn-outline:hover { background: var(--surface-low); }
.fp-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }

/* ── Hero card ─────────────────────────────────────────────────────────────── */
.fp-hero-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.fp-hero-identity { padding: 1.25rem; border-right: 1px solid var(--surface-high); background: linear-gradient(180deg, #f0fdf4, #ffffff); display: flex; flex-direction: column; }
.fp-hero-specs, .fp-hero-perf { padding: 1.25rem; }
.fp-hero-specs { border-right: 1px solid var(--surface-high); }
.fp-farm-avatar-lg { width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(145deg, #1f2937, #0f172a); color: #d1fae5; font-weight: 800; font-size: 1.125rem; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.fp-hero-name   { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); margin-bottom: 2px; }
.fp-hero-farmer { font-size: 0.8125rem; color: var(--on-surface-var); margin-bottom: 6px; }
.fp-contact-row { font-size: 0.75rem; color: var(--on-surface-var); margin-bottom: 3px; }
.fp-specs-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); margin-bottom: 10px; }

/* ── Quality ring ──────────────────────────────────────────────────────────── */
.fp-quality-ring-wrap { display: flex; align-items: center; }
.fp-quality-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-quality-ring-inner { position: absolute; text-align: center; }
.fp-quality-score { font-size: 1.125rem; font-weight: 800; color: var(--green); line-height: 1; }
.fp-quality-label { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.fp-perf-row { display: flex; align-items: center; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; color: var(--on-surface-var); }
.fp-perf-row:last-child { border-bottom: none; }
.fp-perf-row strong { color: var(--on-surface); font-weight: 700; }

/* ── Spec cell ─────────────────────────────────────────────────────────────── */
.fp-spec-cell { background: var(--surface-low); border-radius: 6px; padding: 6px 8px; }
.fp-spec-cell span   { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 2px; }
.fp-spec-cell strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.fp-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.fp-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.fp-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Map ───────────────────────────────────────────────────────────────────── */
.fp-map-tile { position: relative; min-height: 180px; border-radius: 10px; overflow: hidden; background: linear-gradient(135deg, rgba(0,0,0,.04) 25%, transparent 25%) -12px 0/24px 24px, linear-gradient(225deg, rgba(0,0,0,.04) 25%, transparent 25%) -12px 0/24px 24px, linear-gradient(315deg, rgba(0,0,0,.04) 25%, transparent 25%) 0 0/24px 24px, linear-gradient(45deg, rgba(0,0,0,.04) 25%, transparent 25%) 0 0/24px 24px, #f6f7f7; border: 1px solid var(--surface-high); }
.fp-map-pin { position: absolute; top: 50%; left: 50%; width: 60px; height: 60px; border-radius: 50% 50% 50% 0; transform: translate(-50%, -65%) rotate(-45deg); background: var(--green); }
.fp-map-pin::after { content: ''; position: absolute; top: 14px; left: 14px; width: 32px; height: 32px; border-radius: 50%; background: #fff; }
.fp-map-coords { position: absolute; bottom: 0.75rem; left: 0.75rem; right: 0.75rem; background: rgba(255,255,255,.92); border-radius: 8px; padding: 8px 10px; }
.fp-map-badges { position: absolute; top: 0.75rem; right: 0.75rem; display: flex; flex-direction: column; gap: 4px; align-items: flex-end; }
.fp-map-badge { background: rgba(0,69,50,0.85); color: #fff; border-radius: 999px; font-size: 0.625rem; font-weight: 700; padding: 3px 8px; }
.fp-map-badge--blue { background: rgba(30,64,175,0.8); }
.fp-env-row { display: flex; align-items: center; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; color: var(--on-surface-var); }
.fp-env-row:last-child { border-bottom: none; }
.fp-env-row strong { color: var(--on-surface); font-weight: 700; }

/* ── Production chart ──────────────────────────────────────────────────────── */
.fp-chart { height: 100px; display: flex; align-items: flex-end; background: var(--surface-low); border-radius: 8px; padding: 6px; }
.fp-chart-bars { display: flex; align-items: flex-end; gap: 2px; width: 100%; height: 100%; }
.fp-chart-col  { display: flex; flex-direction: column; align-items: center; gap: 2px; flex: 1; height: 100%; justify-content: flex-end; }
.fp-chart-bar  { width: 100%; border-radius: 2px 2px 0 0; }
.fp-chart-bar--prod { background: var(--green); }
.fp-chart-bar--qual { background: #059669; }
.fp-chart-lbl  { font-size: 0.5rem; color: var(--on-surface-var); font-weight: 600; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.fp-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.fp-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.fp-table-row { transition: background 0.1s; }
.fp-table-row:hover { background: var(--surface-low); }
.fp-item-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.fp-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; }
.fp-act-btn   { font-size: 0.75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.fp-score-pill { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 800; padding: 2px 8px; }
.fp-score-pill--high { background: #dcfce7; color: #166534; }
.fp-score-pill--mid  { background: #fef3c7; color: #92400e; }
.fp-up   { color: #166534; font-weight: 700; }
.fp-warn { color: #92400e; font-weight: 700; }

/* ── Traceability ──────────────────────────────────────────────────────────── */
.fp-trace-flow { display: flex; justify-content: space-between; align-items: flex-start; position: relative; }
.fp-trace-stage { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; position: relative; z-index: 1; }
.fp-trace-dot { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.fp-trace-dot--done    { background: #dcfce7; color: #166534; }
.fp-trace-dot--pending { background: var(--surface-high); color: var(--on-surface-var); }
.fp-trace-label  { font-size: 0.6875rem; font-weight: 700; color: var(--on-surface); text-align: center; }
.fp-trace-status { font-size: 0.625rem; font-weight: 600; text-align: center; }
.fp-trace-date   { font-size: 0.5625rem; color: var(--on-surface-var); text-align: center; }
.fp-trace-line   { position: absolute; top: 19px; left: 50%; width: 100%; height: 2px; background: var(--surface-high); z-index: 0; }
.fp-trace-line--done { background: #16a34a; }

/* ── Lot card ──────────────────────────────────────────────────────────────── */
.fp-lot-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; display: flex; flex-direction: column; }

/* ── Bars ──────────────────────────────────────────────────────────────────── */
.fp-bar-track { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.fp-bar-fill  { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* ── Quality bubble ────────────────────────────────────────────────────────── */
.fp-quality-bubble { width: 52px; height: 52px; border-radius: 50%; background: var(--green); color: #fff; font-size: 1rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-cert-label { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Farmer card ───────────────────────────────────────────────────────────── */
.fp-farmer-avatar { width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-farmer-row { display: flex; align-items: center; gap: 7px; font-size: 0.8125rem; color: var(--on-surface-var); padding: 3px 0; }

/* ── Processing ────────────────────────────────────────────────────────────── */
.fp-process-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 8px; padding: 0.75rem; }

/* ── Sustainability ────────────────────────────────────────────────────────── */
.fp-sus-badge { display: inline-flex; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fp-sus-badge--amber { background: #fef3c7; color: #92400e; }
.fp-sus-badge--blue  { background: #dbeafe; color: #1e40af; }
.fp-sus-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid var(--surface-low); }
.fp-sus-row:last-child { border-bottom: none; }

/* ── Timeline ──────────────────────────────────────────────────────────────── */
.fp-timeline { display: flex; flex-direction: column; }
.fp-timeline-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.fp-timeline-item:last-child { border-bottom: none; }
.fp-timeline-dot { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.fp-timeline-dot--success { background: #dcfce7; color: #166534; }
.fp-timeline-dot--primary { background: #dbeafe; color: #1d4ed8; }
.fp-timeline-dot--warning { background: #fef3c7; color: #92400e; }
.fp-timeline-dot--info    { background: #e0f2fe; color: #0369a1; }
.fp-timeline-dot--danger  { background: #fee2e2; color: #b91c1c; }
.fp-timeline-body { flex: 1; min-width: 0; }
.fp-timeline-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.4; }
.fp-timeline-time { font-size: 0.6875rem; color: var(--on-surface-var); margin-top: 2px; }

/* ── Documents ─────────────────────────────────────────────────────────────── */
.fp-doc-row { display: flex; align-items: center; gap: 8px; padding: 7px 0; border-bottom: 1px solid var(--surface-low); }
.fp-doc-row:last-child { border-bottom: none; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.fp-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.fp-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.fp-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.fp-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.fp-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.fp-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.fp-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.fp-alert-row:last-child { border-bottom: none; }
.fp-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.fp-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.fp-toggle--on { background: var(--green); }
.fp-toggle--on i { transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.fp-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 100px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.fp-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.fp-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.fp-fab:hover { background: var(--green-grad); }
.fp-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.fp-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.fp-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.fp-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.fp-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.fp-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.fp-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.fp-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.fp-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.fp-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.fp-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.fp-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.fp-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fp-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.fp-prompt-chip:hover { background: var(--surface-mid); }
.fp-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fp-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.fp-chatbot__input input:focus { border-color: var(--green); }
.fp-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.fp-chat-enter-active, .fp-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fp-chat-enter-from, .fp-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .fp-rail { position: static; } }
@media (max-width: 991.98px)  { .fp-hero-identity, .fp-hero-specs { border-right: none; border-bottom: 1px solid var(--surface-high); } }
@media (max-width: 767.98px)  { .fp-chatbot { width: calc(100vw - 3rem); max-width: 310px; } }
</style>
