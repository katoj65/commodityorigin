<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, Download,
    Location, Medal, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, UserFilled, Van, View,
    Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    farmer: { type: Object, required: true },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

/* ── Real computed ─────────────────────────────────────────────── */
const fullName = computed(() =>
    [props.farmer.first_name, props.farmer.last_name].filter(Boolean).join(' ') || 'Assigned Producer',
);
const locationLabel = computed(() =>
    [props.farmer.sub_county, props.farmer.district, 'Uganda'].filter(Boolean).join(', ') || 'Origin pending',
);
const memberSince = computed(() => {
    if (!props.farmer.created_at) return 'Oct 2018';
    return new Intl.DateTimeFormat('en-US', { month: 'short', year: 'numeric' }).format(new Date(props.farmer.created_at));
});
const initials = computed(() =>
    [props.farmer.first_name?.[0], props.farmer.last_name?.[0]].filter(Boolean).join('').toUpperCase() || '??',
);
const isRobusta = computed(() => props.farmer.coffee_type?.toLowerCase().includes('robusta'));
const avgScore   = computed(() => isRobusta.value ? 84.6 : 87.2);

const estates = computed(() =>
    (props.farmer.farms || []).map((farm, i) => ({
        id: farm.id,
        name: farm.name || `Farm ${farm.id}`,
        identifier: `FARM-${String(farm.id).padStart(3, '0')}`,
        location: farm.location || [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', ') || 'Uganda',
        size: farm.size || '—',
        altitude: farm.altitude || '—',
        variety: farm.variety || props.farmer.coffee_type || 'Arabica',
        status: farm.status || 'Active',
        exportReady: i % 2 === 0,
        thumb: (farm.name?.[0] || 'F').toUpperCase(),
    })),
);

/* ── Static / mock data ────────────────────────────────────────── */
const productionBars = [48, 60, 66, 76, 72, 80, 84, 80, 88, 84, 90, 86];
const qualityBars    = [70, 76, 74, 80, 78, 84, 82, 86, 84, 88, 87, 90];

const harvestRows = computed(() => {
    const base = `HV-${String(props.farmer.id).padStart(3, '0')}`;
    return [
        { id: `${base}-01`, season: '2024/25', farm: estates.value[0]?.name || 'Farm A', qty: '2,400 kg', moisture: '12.4%', score: avgScore.value,     status: 'Processed',  tone: 'success' },
        { id: `${base}-02`, season: '2024/25', farm: estates.value[0]?.name || 'Farm A', qty: '1,800 kg', moisture: '12.8%', score: avgScore.value - 0.4, status: 'In Process', tone: 'warning' },
        { id: `${base}-03`, season: '2024/25', farm: estates.value[1]?.name || 'Farm B', qty: '1,200 kg', moisture: '11.9%', score: avgScore.value - 0.7, status: 'Sold',        tone: 'primary' },
    ];
});

const batchStages = [
    { label: 'Harvest', icon: Box,           total: 3,  done: 2, tone: 'success' },
    { label: 'Batch',   icon: CollectionTag, total: 2,  done: 1, tone: 'warning' },
    { label: 'Lot',     icon: Star,          total: 2,  done: 1, tone: 'primary' },
    { label: 'Market',  icon: ShoppingCart,  total: 1,  done: 1, tone: 'success' },
];

const qualityProfile = [
    { attribute: 'Aroma',      score: 88, display: '8.8/10' },
    { attribute: 'Flavor',     score: 86, display: '8.6/10' },
    { attribute: 'Acidity',    score: 90, display: '9.0/10' },
    { attribute: 'Body',       score: 84, display: '8.4/10' },
    { attribute: 'Aftertaste', score: 87, display: '8.7/10' },
];

const sustainability = [
    { label: 'Shade-Grown Farming',      value: '94%',        icon: Star    },
    { label: 'Water Management',         value: 'Low Impact',  icon: Check   },
    { label: 'Organic Fertilizer Use',   value: 'Active',      icon: Checked },
    { label: 'Climate-Smart Practices',  value: 'Certified',   icon: Medal   },
    { label: 'Carbon Footprint',         value: '<0.62kg/kg',  icon: Check   },
];

const buyerReviews = [
    { buyer: 'Nordic Roasters',    country: 'Sweden',  rating: 4.9, review: 'Consistently delivers premium specialty-grade lots on time with excellent traceability.' },
    { buyer: 'Berlin Kaffee',      country: 'Germany', rating: 4.7, review: 'Reliable sourcing partner. Harvest quality has improved each season.' },
];

const documents = [
    { name: 'Farmer Registration',     status: 'Verified', tone: 'success' },
    { name: 'Cupping Report Q1 2025',  status: 'Verified', tone: 'success' },
    { name: 'Organic Certification',   status: 'Pending',  tone: 'warning' },
    { name: 'Traceability Record',     status: 'Verified', tone: 'success' },
    { name: 'Export Compliance',       status: 'Missing',  tone: 'danger'  },
];

const timeline = [
    { icon: Star,          text: 'Farmer registered on Bean Origin',        date: memberSince.value, tone: 'success' },
    { icon: Location,      text: `${estates.value.length || 1} farm(s) added to profile`, date: '2024',           tone: 'primary' },
    { icon: Box,           text: 'First harvest recorded — 2,400 kg',       date: 'Mar 2025',        tone: 'success' },
    { icon: CollectionTag, text: 'Batch created — processing stage',        date: 'Apr 2025',        tone: 'warning' },
    { icon: ShoppingCart,  text: 'Lot listed on Bean Origin market',        date: 'Apr 2025',        tone: 'info'   },
    { icon: Van,           text: 'Coffee exported to Nordic Roasters',      date: 'May 2025',        tone: 'primary' },
];

const insights = [
    { text: 'Farmer shows improving quality consistency — cupping scores up 2.1 points vs last season.',   tone: 'success' },
    { text: 'Current season production is above average at 5,400 kg total across all farms.',              tone: 'primary' },
    { text: 'Export-ready potential is strong — 2 lots cleared for international markets.',                tone: 'warning' },
];

const alerts = reactive({ harvestUpdates: true, qualityAlerts: true, buyerInterest: false, priceAlerts: true });

/* ── Navigation ────────────────────────────────────────────────── */
/* eslint-disable-next-line no-undef */
const goToAddFarm   = () => router.visit(route('farm.create', props.farmer.id));
/* eslint-disable-next-line no-undef */
const goToFarm      = (id) => router.visit(route('farm.show', id));

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: `Hi! I'm your Bean Origin Farmer Advisor. I can help you evaluate ${fullName.value}'s production quality, reliability, and export potential. What would you like to know?` },
]);
const prompts = ['Is this farmer reliable?', 'What quality does this farmer produce?', 'Which lots are available?', 'Is this farmer export ready?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: `${fullName.value} is a reliable specialty-grade producer with a cupping score of ${avgScore.value}. They have ${estates.value.length || 1} active farm(s) and strong export-readiness from their most recent harvest.` }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <AppLayout :title="fullName" full-width flush :show-banner="false">
        <Head :title="fullName" />
        <div class="fpr-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="fpr-header">
                <div class="container-fluid px-3 px-lg-4">
                    <!-- Flash -->
                    <div v-if="flashSuccess" class="fpr-flash">
                        <el-icon><Check /></el-icon> {{ flashSuccess }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="fpr-kicker">Farmer Profile</div>
                            <h1 class="fpr-title mb-0">{{ fullName }}</h1>
                            <p class="fpr-subtitle mb-0">Verified coffee farmer with traceable production history</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn fpr-btn-outline btn-sm" @click="goToAddFarm"><el-icon><Location /></el-icon> View Farms</button>
                            <button class="btn fpr-btn-outline btn-sm"><el-icon><Box /></el-icon> Add Harvest</button>
                            <button class="btn fpr-btn-outline btn-sm"><el-icon><ShoppingCart /></el-icon> Sell Coffee</button>
                            <button class="btn fpr-btn-primary btn-sm"><el-icon><ChatDotRound /></el-icon> Contact Farmer</button>
                            <button class="btn fpr-btn-ghost btn-sm" @click="chatOpen = true"><el-icon><Opportunity /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 pb-2">
                        <span class="fpr-hbadge"><el-icon><Checked /></el-icon> Verified Farmer</span>
                        <span class="fpr-hbadge fpr-hbadge--soft"><el-icon><Check /></el-icon> Traceable Production</span>
                        <span class="fpr-hbadge fpr-hbadge--blue"><el-icon><Van /></el-icon> Export Ready</span>
                        <span class="fpr-hbadge fpr-hbadge--amber"><el-icon><Star /></el-icon> Sustainable Farming</span>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Hero Overview Card ──────────────────────────── -->
                <div class="fpr-hero-card mb-3">
                    <div class="row g-0 align-items-stretch">
                        <!-- Left: Identity -->
                        <div class="col-12 col-md-4 fpr-hero-identity">
                            <div class="fpr-avatar-xl">
                                <el-icon class="fpr-avatar-icon"><UserFilled /></el-icon>
                                <span class="fpr-avatar-initials">{{ initials }}</span>
                            </div>
                            <div class="fpr-hero-name">{{ fullName }}</div>
                            <div class="fpr-hero-sub">ID: FMR-{{ String(props.farmer.id).padStart(4, '0') }}</div>
                            <div class="d-flex align-items-center gap-1 mb-2">
                                <el-icon style="font-size:12px; color:var(--on-surface-var);"><Location /></el-icon>
                                <span class="fpr-td-muted" style="font-size:.8125rem;">{{ locationLabel }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">Verified</span>
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle" style="font-size:.65rem;">Active</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">{{ props.farmer.coffee_type || 'Arabica' }}</span>
                            </div>
                            <div class="fpr-contact-row" v-if="props.farmer.telephone">
                                <el-icon style="font-size:12px; flex-shrink:0;"><ChatDotRound /></el-icon>
                                <span>{{ props.farmer.telephone }}</span>
                            </div>
                            <div class="fpr-contact-row" v-if="props.farmer.email">
                                <el-icon style="font-size:12px; flex-shrink:0;"><Promotion /></el-icon>
                                <span>{{ props.farmer.email }}</span>
                            </div>
                            <div class="fpr-contact-row" v-if="props.farmer.cooperative">
                                <el-icon style="font-size:12px; flex-shrink:0;"><Check /></el-icon>
                                <span>{{ props.farmer.cooperative }}</span>
                            </div>
                            <div class="fpr-contact-row">
                                <el-icon style="font-size:12px; flex-shrink:0;"><Clock /></el-icon>
                                <span>Member since {{ memberSince }}</span>
                            </div>
                        </div>

                        <!-- Center: Production Summary -->
                        <div class="col-12 col-md-4 fpr-hero-center">
                            <div class="fpr-section-label mb-2">Production Summary</div>
                            <div class="row g-2">
                                <div class="col-6"><div class="fpr-spec-cell"><span>Total Farms</span><strong>{{ estates.length || '—' }}</strong></div></div>
                                <div class="col-6"><div class="fpr-spec-cell"><span>Coffee Type</span><strong>{{ props.farmer.coffee_type || 'Arabica' }}</strong></div></div>
                                <div class="col-6"><div class="fpr-spec-cell"><span>Active Season</span><strong>2024/25</strong></div></div>
                                <div class="col-6"><div class="fpr-spec-cell"><span>Est. Production</span><strong>5,400 kg</strong></div></div>
                                <div class="col-6"><div class="fpr-spec-cell"><span>Farm Size</span><strong>{{ props.farmer.farm_size || '—' }}</strong></div></div>
                                <div class="col-6"><div class="fpr-spec-cell"><span>Avg Yield / ha</span><strong>2.4 t</strong></div></div>
                                <div class="col-6"><div class="fpr-spec-cell"><span>District</span><strong>{{ props.farmer.district || '—' }}</strong></div></div>
                                <div class="col-6"><div class="fpr-spec-cell"><span>Sub-County</span><strong>{{ props.farmer.sub_county || '—' }}</strong></div></div>
                            </div>
                        </div>

                        <!-- Right: Performance & Trust -->
                        <div class="col-12 col-md-4 fpr-hero-perf">
                            <div class="fpr-section-label mb-2">Performance &amp; Trust</div>
                            <div class="fpr-quality-wrap mb-3">
                                <div class="fpr-quality-ring">
                                    <svg viewBox="0 0 80 80" width="96" height="96">
                                        <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                        <circle cx="40" cy="40" r="34" fill="none" stroke="#004532" stroke-width="8"
                                            stroke-dasharray="213.6"
                                            :stroke-dashoffset="213.6 - (213.6 * ((avgScore - 80) / 20))"
                                            stroke-linecap="round"
                                            transform="rotate(-90 40 40)"/>
                                    </svg>
                                    <div class="fpr-ring-inner">
                                        <div class="fpr-ring-score">{{ avgScore }}</div>
                                        <div class="fpr-ring-label">SCA</div>
                                    </div>
                                </div>
                                <div class="ms-3 d-flex flex-column gap-1">
                                    <span class="badge bg-success text-white" style="font-size:.65rem;">Premium Quality</span>
                                    <span class="badge bg-primary text-white" style="font-size:.65rem;">Reliable Supplier</span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">Organic Practices</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <div class="fpr-perf-row"><span>Export Readiness</span><strong class="fpr-up">92%</strong></div>
                                <div class="fpr-perf-row"><span>Sustainability Score</span><strong class="fpr-up">94%</strong></div>
                                <div class="fpr-perf-row"><span>Buyer Rating</span><strong class="fpr-up">4.8 ★</strong></div>
                                <div class="fpr-perf-row"><span>Defect Rate</span><strong>0.8%</strong></div>
                                <div class="fpr-perf-row"><span>Verification</span><strong class="fpr-up">Verified ✓</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes / brief if present -->
                <div v-if="props.farmer.notes" class="fpr-brief mb-3">
                    <el-icon><Opportunity /></el-icon>
                    <span>{{ props.farmer.notes }}</span>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 4. Linked Farms -->
                            <div class="fpr-card">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div class="fpr-card-title">
                                        <el-icon class="fpr-card-icon"><Location /></el-icon>
                                        Linked Farms &amp; Estates
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ estates.length }} farm(s)</span>
                                        <button class="btn fpr-btn-primary btn-sm" @click="goToAddFarm"><el-icon><Star /></el-icon> Add Farm</button>
                                    </div>
                                </div>

                                <div v-if="!estates.length" class="fpr-empty">
                                    <el-icon><Warning /></el-icon>
                                    <div>No farms registered yet. <button class="fpr-link" @click="goToAddFarm">Add the first farm.</button></div>
                                </div>

                                <div v-else class="row g-3">
                                    <div v-for="farm in estates" :key="farm.id" class="col-12 col-md-6">
                                        <div class="fpr-farm-card h-100">
                                            <div class="d-flex align-items-start gap-3 mb-2">
                                                <div class="fpr-farm-thumb">{{ farm.thumb }}</div>
                                                <div class="flex-fill min-w-0">
                                                    <div class="fpr-item-name">{{ farm.name }}</div>
                                                    <div class="fpr-td-muted" style="font-size:.7rem;">{{ farm.identifier }}</div>
                                                    <div class="d-flex align-items-center gap-1 mt-1">
                                                        <el-icon style="font-size:10px; color:var(--on-surface-var);"><Location /></el-icon>
                                                        <span class="fpr-td-muted" style="font-size:.75rem;">{{ farm.location }}</span>
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill" style="font-size:.6rem;"
                                                    :class="farm.status === 'Active' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                                    {{ farm.status }}
                                                </span>
                                            </div>
                                            <div class="row g-1 mb-3">
                                                <div class="col-6"><div class="fpr-spec-cell"><span>Size</span><strong>{{ farm.size }}</strong></div></div>
                                                <div class="col-6"><div class="fpr-spec-cell"><span>Altitude</span><strong>{{ farm.altitude }}</strong></div></div>
                                                <div class="col-6"><div class="fpr-spec-cell"><span>Variety</span><strong>{{ farm.variety }}</strong></div></div>
                                                <div class="col-6"><div class="fpr-spec-cell"><span>Export</span>
                                                    <strong :class="farm.exportReady ? 'fpr-up' : 'fpr-td-muted'">{{ farm.exportReady ? 'Ready' : 'Not Ready' }}</strong>
                                                </div></div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn fpr-btn-primary btn-sm flex-fill" @click="goToFarm(farm.id)">
                                                    <el-icon><View /></el-icon> View Farm
                                                </button>
                                                <button class="btn fpr-btn-outline btn-sm flex-fill">
                                                    <el-icon><Box /></el-icon> Harvests
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Production Overview -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><TrendCharts /></el-icon>
                                    Production Overview
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="fpr-td-muted mb-1" style="font-size:.75rem;">Production Trend (Monthly)</div>
                                        <div class="fpr-chart">
                                            <div class="fpr-chart-bars">
                                                <div v-for="(v, i) in productionBars" :key="i" class="fpr-chart-col">
                                                    <div class="fpr-chart-bar fpr-chart-bar--prod" :style="{ height: `${v}%` }"></div>
                                                    <span class="fpr-chart-lbl">{{ ['J','F','M','A','M','J','J','A','S','O','N','D'][i] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="fpr-td-muted mb-1" style="font-size:.75rem;">Quality Trend (Monthly)</div>
                                        <div class="fpr-chart">
                                            <div class="fpr-chart-bars">
                                                <div v-for="(v, i) in qualityBars" :key="i" class="fpr-chart-col">
                                                    <div class="fpr-chart-bar fpr-chart-bar--qual" :style="{ height: `${v}%` }"></div>
                                                    <span class="fpr-chart-lbl">{{ ['J','F','M','A','M','J','J','A','S','O','N','D'][i] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-6 col-md-3"><div class="fpr-spec-cell"><span>Total Production</span><strong>5,400 kg</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fpr-spec-cell"><span>Avg Yield/ha</span><strong>2.4 t</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fpr-spec-cell"><span>Active Season</span><strong>2024/25</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fpr-spec-cell"><span>Coffee Type</span><strong>{{ props.farmer.coffee_type || 'Arabica' }}</strong></div></div>
                                </div>
                            </div>

                            <!-- 6. Harvest Records -->
                            <div class="fpr-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="fpr-card-title">
                                        <el-icon class="fpr-card-icon"><Box /></el-icon>
                                        Harvest Records
                                    </div>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ harvestRows.length }} harvests</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fpr-table">
                                        <thead>
                                            <tr>
                                                <th>Harvest ID</th>
                                                <th>Season</th>
                                                <th>Farm</th>
                                                <th>Quantity</th>
                                                <th>Moisture</th>
                                                <th>Quality Score</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="h in harvestRows" :key="h.id" class="fpr-table-row">
                                                <td class="fpr-item-name">{{ h.id }}</td>
                                                <td class="fpr-td-muted">{{ h.season }}</td>
                                                <td class="fpr-td-muted">{{ h.farm }}</td>
                                                <td class="fw-semibold">{{ h.qty }}</td>
                                                <td class="fpr-td-muted">{{ h.moisture }}</td>
                                                <td>
                                                    <span class="fpr-score-pill" :class="h.score >= 87 ? 'fpr-score-pill--high' : 'fpr-score-pill--mid'">{{ h.score }}</span>
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
                                                        <button class="btn btn-sm fpr-btn-outline fpr-act-btn"><el-icon><View /></el-icon> View</button>
                                                        <button class="btn btn-sm fpr-btn-ghost fpr-act-btn"><el-icon><CollectionTag /></el-icon> Batch</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 7. Batch & Lot Progress -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><Van /></el-icon>
                                    Batch &amp; Lot Progress
                                </div>
                                <div class="fpr-flow mb-3">
                                    <div v-for="(stage, i) in batchStages" :key="stage.label" class="fpr-flow-stage">
                                        <div class="fpr-flow-dot" :class="`fpr-flow-dot--${stage.tone}`">
                                            <el-icon><component :is="stage.icon" /></el-icon>
                                        </div>
                                        <div class="fpr-flow-label">{{ stage.label }}</div>
                                        <div class="fpr-td-muted" style="font-size:.625rem;">{{ stage.done }}/{{ stage.total }}</div>
                                        <div v-if="i < batchStages.length - 1" class="fpr-flow-line"></div>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-4"><div class="fpr-spec-cell"><span>Total Batches</span><strong>2</strong></div></div>
                                    <div class="col-4"><div class="fpr-spec-cell"><span>Export-Ready Lots</span><strong class="fpr-up">1</strong></div></div>
                                    <div class="col-4"><div class="fpr-spec-cell"><span>Tokenised Lots</span><strong>0</strong></div></div>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn fpr-btn-primary btn-sm flex-fill"><el-icon><View /></el-icon> View Lots</button>
                                    <button class="btn fpr-btn-outline btn-sm flex-fill"><el-icon><ShoppingCart /></el-icon> Sell Coffee</button>
                                </div>
                            </div>

                            <!-- 10. Market & Sales -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><ShoppingCart /></el-icon>
                                    Market &amp; Sales Overview
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3"><div class="fpr-spec-cell"><span>Active Listings</span><strong class="fpr-up">2</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fpr-spec-cell"><span>Avg Sell Price</span><strong>$4.80–5.20</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fpr-spec-cell"><span>Buyer Interest</span><strong class="fpr-up">High</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fpr-spec-cell"><span>Export Demand</span><strong class="fpr-up">Strong</strong></div></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn fpr-btn-primary btn-sm flex-fill"><el-icon><TrendCharts /></el-icon> View Market</button>
                                    <button class="btn fpr-btn-outline btn-sm flex-fill"><el-icon><ShoppingCart /></el-icon> Sell Coffee</button>
                                </div>
                            </div>

                            <!-- 11. Buyer Feedback -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><Star /></el-icon>
                                    Buyer Feedback &amp; Ratings
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6 col-md-4"><div class="fpr-spec-cell"><span>Reliability Score</span><strong class="fpr-up">4.8 / 5.0</strong></div></div>
                                    <div class="col-6 col-md-4"><div class="fpr-spec-cell"><span>Delivery Consistency</span><strong class="fpr-up">96%</strong></div></div>
                                    <div class="col-6 col-md-4"><div class="fpr-spec-cell"><span>Total Reviews</span><strong>{{ buyerReviews.length }}</strong></div></div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="rev in buyerReviews" :key="rev.buyer" class="fpr-review-card">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <div class="fpr-review-avatar">
                                                <el-icon class="fpr-avatar-icon"><UserFilled /></el-icon>
                                                <span class="fpr-avatar-initials">{{ rev.buyer[0] }}</span>
                                            </div>
                                            <div>
                                                <div class="fpr-item-name">{{ rev.buyer }}</div>
                                                <div class="fpr-td-muted" style="font-size:.7rem;">{{ rev.country }}</div>
                                            </div>
                                            <div class="ms-auto fpr-up" style="font-size:.875rem; font-weight:800;">{{ rev.rating }} ★</div>
                                        </div>
                                        <p class="fpr-review-text">{{ rev.review }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. Activity Timeline -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><Clock /></el-icon>
                                    Activity Timeline
                                </div>
                                <div class="fpr-timeline">
                                    <div v-for="(act, i) in timeline" :key="i" class="fpr-timeline-item">
                                        <div class="fpr-timeline-dot" :class="`fpr-timeline-dot--${act.tone}`">
                                            <el-icon><component :is="act.icon" /></el-icon>
                                        </div>
                                        <div class="fpr-timeline-body">
                                            <div class="fpr-timeline-text">{{ act.text }}</div>
                                            <div class="fpr-timeline-time">{{ act.date }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="fpr-rail">

                            <!-- 8. Quality Profile -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><Medal /></el-icon>
                                    Quality Profile
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="fpr-quality-bubble">{{ avgScore }}</div>
                                    <div>
                                        <div style="font-size:.75rem; font-weight:700; color:var(--on-surface);">Avg Cupping Score</div>
                                        <div class="fpr-td-muted" style="font-size:.7rem;">Specialty Grade (85+)</div>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.6rem;">Specialty Potential</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="q in qualityProfile" :key="q.attribute">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fpr-cert-label">{{ q.attribute }}</span>
                                            <span class="fpr-up" style="font-size:.8125rem; font-weight:700;">{{ q.display }}</span>
                                        </div>
                                        <div class="fpr-bar-track">
                                            <div class="fpr-bar-fill" :style="{ width: `${q.score}%`, background: '#004532' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Sustainability -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><Star /></el-icon>
                                    Sustainability &amp; Practices
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="fpr-sus-badge">Sustainable</span>
                                    <span class="fpr-sus-badge fpr-sus-badge--amber">Organic Practices</span>
                                    <span class="fpr-sus-badge fpr-sus-badge--blue">Ethical Farming</span>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="s in sustainability" :key="s.label" class="fpr-sus-row">
                                        <el-icon style="font-size:13px; color:var(--green); flex-shrink:0;"><component :is="s.icon" /></el-icon>
                                        <span class="fpr-cert-label flex-fill">{{ s.label }}</span>
                                        <strong class="fpr-up" style="font-size:.8125rem;">{{ s.value }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Documents & Compliance -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><Download /></el-icon>
                                    Documents &amp; Compliance
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="doc in documents" :key="doc.name" class="fpr-doc-row">
                                        <span class="fpr-cert-label flex-fill">{{ doc.name }}</span>
                                        <span class="badge rounded-pill" style="font-size:.65rem; flex-shrink:0;"
                                            :class="doc.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                  : doc.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                  : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                            {{ doc.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. AI Insights -->
                            <div>
                                <div class="fpr-card-title mb-2">
                                    <el-icon class="fpr-card-icon"><Opportunity /></el-icon>
                                    AI Farmer Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="fpr-insight-card" :class="`fpr-insight-card--${ins.tone}`">
                                        <el-icon class="fpr-insight-icon"><Opportunity /></el-icon>
                                        <p class="fpr-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 15. Smart Alerts -->
                            <div class="fpr-card">
                                <div class="fpr-card-title mb-3">
                                    <el-icon class="fpr-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="fpr-alert-row">
                                        <span>{{ { harvestUpdates: 'Harvest Updates', qualityAlerts: 'Quality Alerts', buyerInterest: 'Buyer Interest', priceAlerts: 'Price Notifications' }[key] }}</span>
                                        <button class="fpr-toggle" :class="{ 'fpr-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 16. Floating Chatbot ───────────────────────────────── -->
            <div class="fpr-fab-wrap">
                <Transition name="fpr-chat">
                    <div v-if="chatOpen" class="fpr-chatbot">
                        <div class="fpr-chatbot__head">
                            <div class="fpr-chatbot__identity">
                                <div class="fpr-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="fpr-chatbot__name">Farmer Advisor</div>
                                    <div class="fpr-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="fpr-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="fpr-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="fpr-chat-msg" :class="`fpr-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="fpr-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="fpr-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="fpr-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="fpr-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.fpr-page {
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
.fpr-header  { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.fpr-kicker  { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--green); margin-bottom: 2px; }
.fpr-title   { font-size: 1.0625rem; font-weight: 800; letter-spacing: -0.02em; }
.fpr-subtitle{ font-size: 0.8125rem; color: var(--on-surface-var); }
.fpr-flash   { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 8px 12px; color: #166534; font-size: 0.8125rem; font-weight: 600; margin: 8px 0 0; }
.fpr-hbadge  { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fpr-hbadge--soft { background: #dcfce7; color: #166534; }
.fpr-hbadge--blue { background: #dbeafe; color: #1e40af; }
.fpr-hbadge--amber{ background: #fef3c7; color: #92400e; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.fpr-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fpr-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.fpr-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fpr-btn-outline:hover { background: var(--surface-low); }
.fpr-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }

/* ── Hero card ─────────────────────────────────────────────────────────────── */
.fpr-hero-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.fpr-hero-identity { padding: 1.25rem; border-right: 1px solid var(--surface-high); background: linear-gradient(180deg, #f0fdf4, #ffffff); display: flex; flex-direction: column; }
.fpr-hero-center, .fpr-hero-perf { padding: 1.25rem; }
.fpr-hero-center { border-right: 1px solid var(--surface-high); }
.fpr-avatar-xl { width: 60px; height: 60px; border-radius: 14px; background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; font-weight: 800; font-size: 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 12px; }
.fpr-avatar-icon     { font-size: 16px; line-height: 1; opacity: 0.7; }
.fpr-avatar-initials { font-size: 0.5rem; font-weight: 800; line-height: 1; letter-spacing: 0.03em; margin-top: 2px; }
.fpr-hero-name { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); margin-bottom: 2px; }
.fpr-hero-sub  { font-size: 0.75rem; color: var(--on-surface-var); margin-bottom: 6px; }
.fpr-contact-row { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--on-surface-var); padding: 3px 0; }
.fpr-section-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }

/* ── Quality ring ──────────────────────────────────────────────────────────── */
.fpr-quality-wrap { display: flex; align-items: center; }
.fpr-quality-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fpr-ring-inner { position: absolute; text-align: center; }
.fpr-ring-score { font-size: 1rem; font-weight: 800; color: var(--green); line-height: 1; }
.fpr-ring-label { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.fpr-perf-row { display: flex; align-items: center; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; color: var(--on-surface-var); }
.fpr-perf-row:last-child { border-bottom: none; }
.fpr-perf-row strong { color: var(--on-surface); font-weight: 700; }

/* ── Brief bar ─────────────────────────────────────────────────────────────── */
.fpr-brief { display: flex; align-items: flex-start; gap: 8px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 10px 14px; font-size: 0.8125rem; color: var(--on-surface); }

/* ── Spec cell ─────────────────────────────────────────────────────────────── */
.fpr-spec-cell { background: var(--surface-low); border-radius: 6px; padding: 6px 8px; }
.fpr-spec-cell span   { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 2px; }
.fpr-spec-cell strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.fpr-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.fpr-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.fpr-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Empty state ───────────────────────────────────────────────────────────── */
.fpr-empty { display: flex; align-items: center; gap: 8px; padding: 20px; color: var(--on-surface-var); font-size: 0.875rem; background: var(--surface-low); border-radius: 10px; border: 1px dashed var(--surface-high); }
.fpr-link  { background: none; border: none; color: var(--green); font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0; font-size: inherit; }

/* ── Farm card ─────────────────────────────────────────────────────────────── */
.fpr-farm-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; display: flex; flex-direction: column; transition: box-shadow 0.15s; }
.fpr-farm-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.fpr-farm-thumb { width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(145deg, #1f2937, #0f172a); color: #d1fae5; font-weight: 800; font-size: 0.875rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Production chart ──────────────────────────────────────────────────────── */
.fpr-chart { height: 90px; display: flex; align-items: flex-end; background: var(--surface-low); border-radius: 8px; padding: 5px; }
.fpr-chart-bars { display: flex; align-items: flex-end; gap: 2px; width: 100%; height: 100%; }
.fpr-chart-col  { display: flex; flex-direction: column; align-items: center; gap: 2px; flex: 1; height: 100%; justify-content: flex-end; }
.fpr-chart-bar  { width: 100%; border-radius: 2px 2px 0 0; }
.fpr-chart-bar--prod { background: var(--green); }
.fpr-chart-bar--qual { background: #059669; }
.fpr-chart-lbl  { font-size: 0.5rem; color: var(--on-surface-var); font-weight: 600; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.fpr-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.fpr-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.fpr-table-row { transition: background 0.1s; }
.fpr-table-row:hover { background: var(--surface-low); }
.fpr-item-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.fpr-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; }
.fpr-act-btn   { font-size: 0.75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.fpr-score-pill { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 800; padding: 2px 8px; }
.fpr-score-pill--high { background: #dcfce7; color: #166534; }
.fpr-score-pill--mid  { background: #fef3c7; color: #92400e; }
.fpr-up   { color: #166534; font-weight: 700; }
.fpr-warn { color: #92400e; font-weight: 700; }

/* ── Batch flow ────────────────────────────────────────────────────────────── */
.fpr-flow { display: flex; justify-content: space-between; align-items: flex-start; position: relative; }
.fpr-flow-stage { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; position: relative; z-index: 1; }
.fpr-flow-dot { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.fpr-flow-dot--success { background: #dcfce7; color: #166534; }
.fpr-flow-dot--warning { background: #fef3c7; color: #92400e; }
.fpr-flow-dot--primary { background: #dbeafe; color: #1d4ed8; }
.fpr-flow-label { font-size: 0.6875rem; font-weight: 700; color: var(--on-surface); text-align: center; }
.fpr-flow-line  { position: absolute; top: 18px; left: 50%; width: 100%; height: 2px; background: var(--surface-high); z-index: 0; }

/* ── Quality / bars ────────────────────────────────────────────────────────── */
.fpr-quality-bubble { width: 52px; height: 52px; border-radius: 50%; background: var(--green); color: #fff; font-size: 1rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fpr-cert-label { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.fpr-bar-track  { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.fpr-bar-fill   { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* ── Sustainability ────────────────────────────────────────────────────────── */
.fpr-sus-badge { display: inline-flex; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fpr-sus-badge--amber { background: #fef3c7; color: #92400e; }
.fpr-sus-badge--blue  { background: #dbeafe; color: #1e40af; }
.fpr-sus-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid var(--surface-low); }
.fpr-sus-row:last-child { border-bottom: none; }

/* ── Reviews ───────────────────────────────────────────────────────────────── */
.fpr-review-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.fpr-review-avatar { width: 32px; height: 32px; border-radius: 8px; background: var(--green); color: #fff; font-weight: 800; font-size: 0.875rem; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; }
.fpr-review-avatar .fpr-avatar-icon     { font-size: 11px; opacity: 0.8; }
.fpr-review-avatar .fpr-avatar-initials { font-size: 0.45rem; margin-top: 1px; }
.fpr-review-text { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.5; margin: 0; }

/* ── Documents ─────────────────────────────────────────────────────────────── */
.fpr-doc-row { display: flex; align-items: center; gap: 8px; padding: 7px 0; border-bottom: 1px solid var(--surface-low); }
.fpr-doc-row:last-child { border-bottom: none; }

/* ── Timeline ──────────────────────────────────────────────────────────────── */
.fpr-timeline { display: flex; flex-direction: column; }
.fpr-timeline-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.fpr-timeline-item:last-child { border-bottom: none; }
.fpr-timeline-dot { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.fpr-timeline-dot--success { background: #dcfce7; color: #166534; }
.fpr-timeline-dot--primary { background: #dbeafe; color: #1d4ed8; }
.fpr-timeline-dot--warning { background: #fef3c7; color: #92400e; }
.fpr-timeline-dot--info    { background: #e0f2fe; color: #0369a1; }
.fpr-timeline-body { flex: 1; min-width: 0; }
.fpr-timeline-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.4; }
.fpr-timeline-time { font-size: 0.6875rem; color: var(--on-surface-var); margin-top: 2px; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.fpr-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.fpr-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.fpr-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.fpr-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.fpr-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.fpr-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.fpr-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.fpr-alert-row:last-child { border-bottom: none; }
.fpr-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.fpr-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.fpr-toggle--on { background: var(--green); }
.fpr-toggle--on i { transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.fpr-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.fpr-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.fpr-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.fpr-fab:hover { background: var(--green-grad); }
.fpr-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.fpr-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.fpr-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.fpr-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.fpr-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.fpr-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.fpr-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.fpr-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.fpr-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.fpr-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.fpr-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.fpr-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.fpr-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fpr-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.fpr-prompt-chip:hover { background: var(--surface-mid); }
.fpr-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fpr-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.fpr-chatbot__input input:focus { border-color: var(--green); }
.fpr-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.fpr-chat-enter-active, .fpr-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fpr-chat-enter-from, .fpr-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .fpr-rail { position: static; } }
@media (max-width: 991.98px)  { .fpr-hero-identity, .fpr-hero-center { border-right: none; border-bottom: 1px solid var(--surface-high); } }
@media (max-width: 767.98px)  { .fpr-chatbot { width: calc(100vw - 3rem); max-width: 310px; } }
</style>
