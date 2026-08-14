<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, Calendar, ChatDotRound, Check,
    Clock, CollectionTag, DataLine, Download,
    Location, Opportunity, Promotion,
    ShoppingCart, Star, Tickets, TrendCharts, Van,
    Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    seasons: { type: Object, required: true },
});

/* ── Real computed ─────────────────────────────────────────────── */
const visiblePages = computed(() => {
    const cur   = props.seasons.meta.current_page || 1;
    const last  = props.seasons.meta.last_page    || 1;
    const start = Math.max(1, cur - 1);
    const end   = Math.min(last, cur + 1);
    const pages = [];
    for (let p = start; p <= end; p++) pages.push(p);
    return pages;
});

const rangeSummary   = computed(() => {
    const { from = 0, to = 0, total = 0 } = props.seasons.meta;
    return `Showing ${from}–${to} of ${total}`;
});
const totalSeasons   = computed(() => props.seasons.meta.total || 0);
const activeCount    = computed(() => props.seasons.data.filter(s => String(s.status).toLowerCase() === 'active').length);
const completedCount = computed(() => props.seasons.data.filter(s => String(s.status).toLowerCase() === 'completed').length);

const formatDate = (value) => {
    if (!value) return 'Pending';
    return new Intl.DateTimeFormat('en-UG', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(value));
};

const statusTone = (status) => {
    switch (String(status).toLowerCase()) {
        case 'active':    return 'success';
        case 'completed': return 'secondary';
        case 'upcoming':  return 'primary';
        case 'archived':  return 'warning';
        default:          return 'secondary';
    }
};

/* ── Static mock data ──────────────────────────────────────────── */
const timelineStages = [
    { label: 'Planting',      date: 'Oct – Nov', done: true  },
    { label: 'Flowering',     date: 'Dec – Jan', done: true  },
    { label: 'Preparation',   date: 'Feb – Mar', done: true  },
    { label: 'Main Harvest',  date: 'Mar – Apr', done: false },
    { label: 'Processing',    date: 'Apr – May', done: false },
    { label: 'Export Window', date: 'May – Jun', done: false },
];

const comparison = [
    { season: '2024/25 Main', production: '12,700 kg', quality: 87.4, export: '8,400 kg', price: '$4.90/kg', demand: 'High',   best: true  },
    { season: '2023/24 Main', production: '10,200 kg', quality: 86.1, export: '6,800 kg', price: '$4.60/kg', demand: 'High',   best: false },
    { season: '2023 Fly Crop',production:  '3,400 kg', quality: 84.8, export: '1,800 kg', price: '$4.20/kg', demand: 'Medium', best: false },
];

const harvestRows = [
    { id: 'HV-001', farm: 'Sipi Plot A',   date: 'Mar 20', qty: '2,400 kg', score: 88.5, status: 'Processed',  tone: 'success' },
    { id: 'HV-002', farm: 'Elgon Block B', date: 'Mar 15', qty: '1,800 kg', score: 87.2, status: 'In Process', tone: 'warning' },
    { id: 'HV-003', farm: 'Rwenzori Farm', date: 'Feb 28', qty: '2,100 kg', score: 86.9, status: 'Processed',  tone: 'success' },
    { id: 'HV-004', farm: 'Sipi Plot A',   date: 'Feb 10', qty: '1,400 kg', score: 87.8, status: 'Sold',       tone: 'primary' },
];

const prodBars  = [0, 0, 0, 42, 68, 84, 100, 88, 62, 28, 0, 0];
const qualBars  = [0, 0, 0, 80, 84, 88,  92, 90, 87, 84, 0, 0];
const months    = ['J','F','M','A','M','J','J','A','S','O','N','D'];

const regions = [
    { name: 'Mount Elgon',    prod: '4,200 kg', score: 88.5, harvests: 12, export: '92%' },
    { name: 'Rwenzori',       prod: '3,800 kg', score: 87.1, harvests: 9,  export: '88%' },
    { name: 'Central Uganda', prod: '2,900 kg', score: 84.6, harvests: 8,  export: '74%' },
    { name: 'SW Uganda',      prod: '1,800 kg', score: 86.3, harvests: 6,  export: '80%' },
];

const traceFlow = [
    { label: 'Season',  icon: Calendar,      qty: `${totalSeasons.value} seasons`, done: true  },
    { label: 'Harvest', icon: Box,           qty: '28 harvests',                   done: true  },
    { label: 'Batch',   icon: CollectionTag, qty: '14 batches',                    done: false },
    { label: 'Lot',     icon: Star,          qty: '9 lots',                        done: false },
    { label: 'Market',  icon: ShoppingCart,  qty: '3 active',                      done: false },
];

const markets = [
    { region: 'UAE',          demand: 'Extreme', price: '$1.80–2.10', pct: 96 },
    { region: 'Germany / EU', demand: 'High',    price: '$5.00–5.40', pct: 85 },
    { region: 'USA',          demand: 'High',    price: '$4.80–5.20', pct: 78 },
    { region: 'Japan',        demand: 'Medium',  price: '$5.20–5.80', pct: 62 },
];

const sustainability = [
    { label: 'Organic Compliance',       value: '59%', pct: 59 },
    { label: 'Climate-Smart Practices',  value: '72%', pct: 72 },
    { label: 'Water Usage Efficiency',   value: '85%', pct: 85 },
    { label: 'Shade-Grown Coverage',     value: '92%', pct: 92 },
];

const activityLog = [
    { icon: Calendar,      text: `Season 2024/25 created and activated`,         date: 'Oct 2024',  tone: 'success' },
    { icon: Star,          text: '18 farmers assigned to season',                date: 'Nov 2024',  tone: 'primary' },
    { icon: Box,           text: 'First harvest recorded — Sipi Plot A',         date: 'Mar 2025',  tone: 'success' },
    { icon: TrendCharts,   text: 'Peak harvest period reached — 8,400 kg',       date: 'Apr 2025',  tone: 'warning' },
    { icon: CollectionTag, text: '14 batches in processing stage',               date: 'Apr 2025',  tone: 'info'   },
    { icon: Van,           text: '3 export-ready lots cleared for shipping',     date: 'May 2025',  tone: 'primary' },
];

const insights = [
    { text: 'This season shows 24% higher yield than the previous main crop — strong specialty potential.',  tone: 'success' },
    { text: 'Quality consistency is improving across farms — average SCA score up 1.3 points YoY.',         tone: 'primary' },
    { text: 'Export demand is increasing in EU markets — prioritise specialty lot processing.',              tone: 'warning' },
];

const alerts = reactive({ newHarvest: true, seasonEnd: true, qualityDrop: false, exportReady: true, demandSpike: false });

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Bean Origin Season Advisor. I can help you track production cycles, analyse seasonal performance, and plan for export readiness. What would you like to know?" },
]);
const prompts = ['Which season performed best?', 'Is this season export ready?', 'What harvests are linked?', 'How can yield improve?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: '2024/25 Main Harvest is performing best with 12,700 kg total production and an average cupping score of 87.4. Export readiness is at 88% — 3 lots are already cleared for shipping.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <AppLayout title="Seasons" full-width flush :show-banner="false">
        <Head title="Seasons" />

        <div class="sx-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="sx-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="sx-kicker">Production Management</div>
                            <h1 class="sx-title mb-0">Seasons</h1>
                            <p class="sx-subtitle mb-0">Manage coffee production cycles, harvest timelines, and seasonal performance.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button class="btn sx-btn-outline btn-sm"><el-icon><Download /></el-icon> Export Data</button>
                            <button class="btn sx-btn-ghost btn-sm" @click="chatOpen = true"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                            <Link :href="route('season.create')" class="btn sx-btn-primary btn-sm"><el-icon><Calendar /></el-icon> Create Season</Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. KPI Row ──────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="sx-kpi h-100">
                            <span class="sx-kpi__label">Total Seasons</span>
                            <div class="sx-kpi__value">{{ totalSeasons }}</div>
                            <div class="sx-kpi__sub">All cycles</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="sx-kpi h-100">
                            <span class="sx-kpi__label">Active</span>
                            <div class="sx-kpi__value sx-green">{{ activeCount }}</div>
                            <div class="sx-kpi__sub">Running now</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="sx-kpi h-100">
                            <span class="sx-kpi__label">Completed</span>
                            <div class="sx-kpi__value">{{ completedCount }}</div>
                            <div class="sx-kpi__sub">Finished</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="sx-kpi h-100">
                            <span class="sx-kpi__label">Total Harvests</span>
                            <div class="sx-kpi__value">28</div>
                            <div class="sx-kpi__sub">This cycle</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="sx-kpi h-100">
                            <span class="sx-kpi__label">Production</span>
                            <div class="sx-kpi__value sx-green">12,700</div>
                            <div class="sx-kpi__sub">kg this season</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="sx-kpi h-100">
                            <span class="sx-kpi__label">Avg Quality</span>
                            <div class="sx-kpi__value sx-green">87.4</div>
                            <div class="sx-kpi__sub">SCA score</div>
                        </div>
                    </div>
                </div>

                <!-- ── 3. Seasons Grid ─────────────────────────────────── -->
                <div class="sx-section mb-3">
                    <div class="sx-section-head">
                        <el-icon class="sx-section-icon"><Tickets /></el-icon>
                        All Seasons
                        <span class="sx-section-count">{{ rangeSummary }}</span>
                        <Link :href="route('season.create')" class="btn sx-btn-primary btn-sm ms-auto"><el-icon><Calendar /></el-icon> New Season</Link>
                    </div>

                    <!-- Empty state -->
                    <div v-if="!seasons.data.length" class="sx-empty">
                        <el-icon style="font-size:2rem; color:#d1d5db;"><Tickets /></el-icon>
                        <strong>No seasons yet</strong>
                        <span>Create your first season to start tracking production cycles.</span>
                        <Link :href="route('season.create')" class="btn sx-btn-primary btn-sm mt-2"><el-icon><Calendar /></el-icon> Create Season</Link>
                    </div>

                    <!-- Season table -->
                    <div v-else>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 sx-table">
                                <thead>
                                    <tr>
                                        <th>Season</th>
                                        <th>Region</th>
                                        <th>Coffee Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in seasons.data" :key="row.id" class="sx-table-row">
                                        <td>
                                            <Link :href="route('season.show', row.id)" class="sx-season-name">{{ row.name }}</Link>
                                        </td>
                                        <td class="sx-muted">{{ row.region || '—' }}</td>
                                        <td class="sx-muted">{{ row.coffee_type || '—' }}</td>
                                        <td class="sx-muted">{{ formatDate(row.start_date) }}</td>
                                        <td class="sx-muted">{{ formatDate(row.end_date) }}</td>
                                        <td>
                                            <span class="badge rounded-pill sx-status-badge"
                                                :class="`sx-status-badge--${statusTone(row.status)}`">
                                                {{ row.status || 'Unknown' }}
                                            </span>
                                        </td>
                                        <td class="sx-muted" style="max-width:200px; white-space:normal;">{{ row.notes || '—' }}</td>
                                        <td class="text-end">
                                            <Link :href="route('season.show', row.id)" class="btn btn-sm sx-btn-outline sx-act-btn">
                                                <el-icon><Star /></el-icon> View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="sx-pagination-bar">
                            <span class="sx-muted" style="font-size:.75rem;">{{ rangeSummary }}</span>
                            <div class="d-flex gap-1">
                                <Link :href="route('season.index', { page: Math.max(1, seasons.meta.current_page - 1) })"
                                    class="sx-page-btn" :class="{ 'sx-page-btn--disabled': seasons.meta.current_page <= 1 }">
                                    ← Prev
                                </Link>
                                <Link v-for="page in visiblePages" :key="page"
                                    :href="route('season.index', { page })"
                                    class="sx-page-btn" :class="{ 'sx-page-btn--active': page === seasons.meta.current_page }">
                                    {{ page }}
                                </Link>
                                <Link :href="route('season.index', { page: Math.min(seasons.meta.last_page, seasons.meta.current_page + 1) })"
                                    class="sx-page-btn" :class="{ 'sx-page-btn--disabled': seasons.meta.current_page >= seasons.meta.last_page }">
                                    Next →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 4. Season Timeline ──────────────────────────────── -->
                <div class="sx-section mb-3">
                    <div class="sx-section-head">
                        <el-icon class="sx-section-icon"><Clock /></el-icon>
                        Season Timeline — 2024/25 Main Harvest
                    </div>
                    <div class="sx-section-body">
                        <div class="sx-htl">
                            <!-- Row 1: timestamps -->
                            <div class="sx-htl__ts-row">
                                <div v-for="stage in timelineStages" :key="stage.label + '-ts'" class="sx-htl__ts">
                                    {{ stage.date }}
                                </div>
                            </div>
                            <!-- Row 2: dots + connectors -->
                            <div class="sx-htl__dot-row">
                                <template v-for="(stage, i) in timelineStages" :key="stage.label + '-dot'">
                                    <div class="sx-htl__dot" :class="stage.done ? 'sx-htl__dot--done' : 'sx-htl__dot--todo'">
                                        <el-icon v-if="stage.done"><Check /></el-icon>
                                    </div>
                                    <div v-if="i < timelineStages.length - 1" class="sx-htl__seg" :class="stage.done ? 'sx-htl__seg--done' : ''"></div>
                                </template>
                            </div>
                            <!-- Row 3: labels + badges -->
                            <div class="sx-htl__lbl-row">
                                <div v-for="stage in timelineStages" :key="stage.label + '-lbl'" class="sx-htl__lbl-cell">
                                    <div class="sx-htl__label" :class="stage.done ? 'sx-htl__label--done' : ''">{{ stage.label }}</div>
                                    <div class="sx-htl__badge" :class="stage.done ? 'sx-htl__badge--done' : 'sx-htl__badge--todo'">
                                        {{ stage.done ? 'Completed' : 'Upcoming' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 5. Season Comparison -->
                            <div class="sx-section p-0 overflow-hidden">
                                <div class="sx-section-head px-3">
                                    <el-icon class="sx-section-icon"><TrendCharts /></el-icon>
                                    Season vs Season Comparison
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 sx-table">
                                        <thead>
                                            <tr>
                                                <th>Season</th>
                                                <th>Production</th>
                                                <th>Avg Quality</th>
                                                <th>Export Volume</th>
                                                <th>Price</th>
                                                <th>Demand</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="c in comparison" :key="c.season" class="sx-table-row" :class="{ 'sx-table-row--best': c.best }">
                                                <td>
                                                    <div class="sx-item-name d-flex align-items-center gap-2">
                                                        {{ c.season }}
                                                        <span v-if="c.best" class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.6rem;">Best</span>
                                                    </div>
                                                </td>
                                                <td class="fw-semibold">{{ c.production }}</td>
                                                <td>
                                                    <span class="sx-score-pill" :class="c.quality >= 87 ? 'sx-score-pill--high' : 'sx-score-pill--mid'">{{ c.quality }}</span>
                                                </td>
                                                <td class="sx-muted">{{ c.export }}</td>
                                                <td class="fw-semibold">{{ c.price }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="c.demand === 'High' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                        {{ c.demand }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 6. Linked Harvests -->
                            <div class="sx-section p-0 overflow-hidden">
                                <div class="sx-section-head px-3">
                                    <el-icon class="sx-section-icon"><Box /></el-icon>
                                    Linked Harvests
                                    <span class="sx-section-count">{{ harvestRows.length }} records</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 sx-table">
                                        <thead>
                                            <tr>
                                                <th>Harvest ID</th>
                                                <th>Farm</th>
                                                <th>Date</th>
                                                <th>Quantity</th>
                                                <th>Quality Score</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="h in harvestRows" :key="h.id" class="sx-table-row">
                                                <td class="sx-item-name">{{ h.id }}</td>
                                                <td class="sx-muted">{{ h.farm }}</td>
                                                <td class="sx-muted">{{ h.date }}</td>
                                                <td class="fw-semibold">{{ h.qty }}</td>
                                                <td>
                                                    <span class="sx-score-pill" :class="h.score >= 88 ? 'sx-score-pill--high' : 'sx-score-pill--mid'">{{ h.score }}</span>
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
                                                    <button class="btn btn-sm sx-btn-outline sx-act-btn"><el-icon><Star /></el-icon> View</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 7. Production Analytics -->
                            <div class="sx-section">
                                <div class="sx-section-head">
                                    <el-icon class="sx-section-icon"><DataLine /></el-icon>
                                    Production Performance Analytics
                                </div>
                                <div class="sx-section-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="sx-muted mb-1" style="font-size:.75rem;">Production Per Month (kg)</div>
                                            <div class="sx-chart">
                                                <div v-for="(v, i) in prodBars" :key="i" class="sx-chart-bar sx-chart-bar--prod" :style="{ height: `${Math.max(v, 4)}%` }">
                                                    <span class="sx-chart-lbl">{{ months[i] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="sx-muted mb-1" style="font-size:.75rem;">Quality Trend (SCA Score)</div>
                                            <div class="sx-chart">
                                                <div v-for="(v, i) in qualBars" :key="i" class="sx-chart-bar sx-chart-bar--qual" :style="{ height: `${Math.max(v, 4)}%` }">
                                                    <span class="sx-chart-lbl">{{ months[i] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-6 col-md-3"><div class="sx-info-cell"><span>Peak Month</span><strong>July</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="sx-info-cell"><span>Arabica Share</span><strong class="sx-green">72%</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="sx-info-cell"><span>Export Vol.</span><strong>8,400 kg</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="sx-info-cell"><span>Growth YoY</span><strong class="sx-green">+24%</strong></div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Season-to-Market Flow -->
                            <div class="sx-section">
                                <div class="sx-section-head">
                                    <el-icon class="sx-section-icon"><Van /></el-icon>
                                    Season-to-Market Traceability
                                </div>
                                <div class="sx-section-body">
                                    <div class="sx-flow mb-3">
                                        <div v-for="(stage, i) in traceFlow" :key="stage.label" class="sx-flow-stage">
                                            <div class="sx-flow-dot" :class="stage.done ? 'sx-flow-dot--done' : 'sx-flow-dot--todo'">
                                                <el-icon><component :is="stage.icon" /></el-icon>
                                            </div>
                                            <div class="sx-flow-label">{{ stage.label }}</div>
                                            <div class="sx-flow-qty sx-muted">{{ stage.qty }}</div>
                                            <div v-if="i < traceFlow.length - 1" class="sx-flow-line" :class="stage.done ? 'sx-flow-line--done' : ''"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Export & Market Insights -->
                            <div class="sx-section">
                                <div class="sx-section-head">
                                    <el-icon class="sx-section-icon"><Van /></el-icon>
                                    Export &amp; Market Insights
                                </div>
                                <div class="sx-section-body">
                                    <div class="d-flex flex-column gap-3">
                                        <div v-for="m in markets" :key="m.region">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="sx-item-name">{{ m.region }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="sx-muted" style="font-size:.75rem;">{{ m.price }}</span>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="m.demand === 'Extreme' ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                              : m.demand === 'High' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                        {{ m.demand }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="sx-bar-track">
                                                <div class="sx-bar-fill" :style="{ width: `${m.pct}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Activity Log -->
                            <div class="sx-section">
                                <div class="sx-section-head">
                                    <el-icon class="sx-section-icon"><Clock /></el-icon>
                                    Season Activity Log
                                </div>
                                <div class="sx-section-body">
                                    <div class="sx-timeline">
                                        <div v-for="(act, i) in activityLog" :key="i" class="sx-timeline-item">
                                            <div class="sx-timeline-dot" :class="`sx-timeline-dot--${act.tone}`">
                                                <el-icon><component :is="act.icon" /></el-icon>
                                            </div>
                                            <div class="sx-timeline-body">
                                                <div class="sx-item-name">{{ act.text }}</div>
                                                <div class="sx-muted" style="font-size:.6875rem; margin-top:2px;">{{ act.date }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="sx-rail">

                            <!-- 8. Regional Breakdown -->
                            <div class="sx-section">
                                <div class="sx-section-head">
                                    <el-icon class="sx-section-icon"><Location /></el-icon>
                                    Region-Based Breakdown
                                </div>
                                <div class="sx-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="reg in regions" :key="reg.name" class="sx-region-card">
                                            <div class="sx-item-name mb-1">{{ reg.name }}</div>
                                            <div class="row g-1">
                                                <div class="col-6"><div class="sx-info-cell"><span>Production</span><strong>{{ reg.prod }}</strong></div></div>
                                                <div class="col-6"><div class="sx-info-cell"><span>Quality</span><strong class="sx-green">{{ reg.score }}</strong></div></div>
                                                <div class="col-6"><div class="sx-info-cell"><span>Harvests</span><strong>{{ reg.harvests }}</strong></div></div>
                                                <div class="col-6"><div class="sx-info-cell"><span>Export Ready</span><strong class="sx-green">{{ reg.export }}</strong></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Sustainability -->
                            <div class="sx-section">
                                <div class="sx-section-head">
                                    <el-icon class="sx-section-icon"><Check /></el-icon>
                                    Sustainability Performance
                                </div>
                                <div class="sx-section-body">
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        <span class="sx-sus-badge">Sustainable</span>
                                        <span class="sx-sus-badge sx-sus-badge--amber">Organic</span>
                                        <span class="sx-sus-badge sx-sus-badge--blue">Verified</span>
                                    </div>
                                    <div class="d-flex flex-column gap-3">
                                        <div v-for="s in sustainability" :key="s.label">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span style="font-size:.8125rem;">{{ s.label }}</span>
                                                <strong class="sx-green" style="font-size:.8125rem;">{{ s.value }}</strong>
                                            </div>
                                            <div class="sx-bar-track">
                                                <div class="sx-bar-fill" :style="{ width: `${s.pct}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. AI Insights -->
                            <div>
                                <div class="sx-section-head sx-section-head--plain mb-2">
                                    <el-icon class="sx-section-icon"><Opportunity /></el-icon>
                                    AI Season Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="sx-insight-card" :class="`sx-insight-card--${ins.tone}`">
                                        <el-icon class="sx-insight-icon"><Opportunity /></el-icon>
                                        <p class="sx-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. Smart Alerts -->
                            <div class="sx-section">
                                <div class="sx-section-head">
                                    <el-icon class="sx-section-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="sx-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="(val, key) in alerts" :key="key" class="sx-alert-row">
                                            <span style="font-size:.8125rem;">{{ { newHarvest: 'New Harvest Added', seasonEnd: 'Season Nearing Completion', qualityDrop: 'Quality Drop Detected', exportReady: 'Export Readiness Achieved', demandSpike: 'Buyer Demand Spike' }[key] }}</span>
                                            <button class="sx-toggle" :class="{ 'sx-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
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
            <div class="sx-fab-wrap">
                <Transition name="sx-chat">
                    <div v-if="chatOpen" class="sx-chatbot">
                        <div class="sx-chatbot__head">
                            <div class="sx-chatbot__identity">
                                <div class="sx-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="sx-chatbot__name">Season Advisor</div>
                                    <div class="sx-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="sx-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="sx-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="sx-chat-msg" :class="`sx-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="sx-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="sx-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="sx-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="sx-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.sx-page {
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
.sx-green { color: #166534; font-weight: 700; }
.sx-muted { color: var(--on-surface-var); }
.sx-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Header ────────────────────────────────────────────────────────────────── */
.sx-header   { background: #fff; border-bottom: 1px solid var(--border); }
.sx-kicker   { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.sx-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.sx-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.sx-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.sx-btn-primary:hover { background: #065f46; color: #fff; }
.sx-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.sx-btn-outline:hover { background: var(--surface-low); }
.sx-btn-ghost   { background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.sx-act-btn { font-size: .75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }

/* ── KPI ───────────────────────────────────────────────────────────────────── */
.sx-kpi { border: 1px solid var(--border); border-radius: 8px; padding: .875rem; background: #fff; }
.sx-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.sx-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.sx-kpi__sub   { font-size: .625rem; color: var(--on-surface-var); }

/* ── Sections ──────────────────────────────────────────────────────────────── */
.sx-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; }
.sx-section-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--surface-low);
    border-bottom: 1px solid var(--border);
    border-radius: 7px 7px 0 0;
    font-size: .8125rem;
    font-weight: 700;
    color: var(--on-surface);
}
.sx-section-head--plain { display: flex; align-items: center; gap: 8px; font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.sx-section-icon { width: 20px; height: 20px; border-radius: 4px; background: rgba(0,69,50,.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.sx-section-count { font-size: .6875rem; font-weight: 700; background: var(--border); color: var(--on-surface-var); border-radius: 999px; padding: 2px 8px; }
.sx-section-body { padding: 1rem; }

/* Empty state */
.sx-empty { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 3rem 1rem; text-align: center; }

/* ── Season cards ──────────────────────────────────────────────────────────── */
.sx-season-card { border: 1px solid var(--border); border-radius: 8px; padding: 1rem; display: flex; flex-direction: column; transition: border-color .12s; }
.sx-season-card:hover { border-color: var(--border-m); }
.sx-season-name { font-size: .9375rem; font-weight: 700; color: var(--green); text-decoration: none; line-height: 1.3; }
.sx-season-name:hover { text-decoration: underline; }
.sx-status-badge { font-size: .65rem !important; font-weight: 700; padding: 2px 8px !important; }
.sx-status-badge--success   { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.sx-status-badge--secondary { background: var(--surface-low); color: var(--on-surface-var); border: 1px solid var(--border); }
.sx-status-badge--primary   { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.sx-status-badge--warning   { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.sx-season-meta { display: flex; flex-direction: column; gap: 3px; }
.sx-meta-row { display: flex; align-items: center; gap: 6px; font-size: .8125rem; color: var(--on-surface-var); }
.sx-meta-row .el-icon { font-size: 11px; flex-shrink: 0; }
.sx-season-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; }
.sx-metric-cell { border: 1px solid var(--border); border-radius: 5px; padding: 4px 6px; text-align: center; }
.sx-metric-cell span   { font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); display: block; }
.sx-metric-cell strong { font-size: .8125rem; font-weight: 700; color: var(--on-surface); display: block; }
.sx-season-notes { font-size: .75rem; color: var(--on-surface-var); line-height: 1.5; border-left: 2px solid var(--border); padding-left: 8px; }

/* ── Horizontal timeline ────────────────────────────────────────────────────── */
/* Row 1 — timestamps */
.sx-htl__ts-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
.sx-htl__ts     { flex: 1; text-align: center; font-size: .6875rem; font-weight: 600; color: var(--on-surface-var); }
/* Row 2 — dots + connectors (all siblings in one horizontal flex) */
.sx-htl__dot-row { display: flex; align-items: center; }
.sx-htl__dot { width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid var(--border); background: #fff; color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.sx-htl__dot--done { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
.sx-htl__dot--todo { border-color: #d1d5db; }
.sx-htl__seg { flex: 1; height: 1px; background: var(--border); }
.sx-htl__seg--done { background: #16a34a; }
/* Row 3 — labels + badges */
.sx-htl__lbl-row  { display: flex; justify-content: space-between; margin-top: 8px; }
.sx-htl__lbl-cell { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.sx-htl__label { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); text-align: center; }
.sx-htl__label--done { color: var(--on-surface); }
.sx-htl__badge { display: inline-flex; border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 7px; }
.sx-htl__badge--done { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.sx-htl__badge--todo { background: var(--surface-low); color: var(--on-surface-var); border: 1px solid var(--border); }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.sx-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.sx-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.sx-table-row:last-child td { border-bottom: none; }
.sx-table-row:hover { background: var(--surface-low); }
.sx-table-row--best { background: #f0fdf4; }
.sx-score-pill { display: inline-flex; border-radius: 999px; font-size: .6875rem; font-weight: 800; padding: 2px 8px; }
.sx-score-pill--high { background: #dcfce7; color: #166534; }
.sx-score-pill--mid  { background: #fef3c7; color: #92400e; }

/* ── Charts ────────────────────────────────────────────────────────────────── */
.sx-chart { height: 80px; display: flex; align-items: flex-end; gap: 2px; border: 1px solid var(--border); border-radius: 6px; padding: 5px; background: var(--surface-low); }
.sx-chart-bar { flex: 1; border-radius: 2px 2px 0 0; position: relative; }
.sx-chart-bar--prod { background: var(--green); opacity: .8; }
.sx-chart-bar--qual { background: #059669; opacity: .8; }
.sx-chart-lbl { position: absolute; bottom: -14px; left: 50%; transform: translateX(-50%); font-size: .4375rem; color: var(--on-surface-var); font-weight: 600; white-space: nowrap; }

/* ── Traceability flow ─────────────────────────────────────────────────────── */
.sx-flow { display: flex; justify-content: space-between; align-items: flex-start; position: relative; }
.sx-flow-stage { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; position: relative; z-index: 1; }
.sx-flow-dot { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 13px; background: #fff; color: var(--on-surface-var); }
.sx-flow-dot--done { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
.sx-flow-label { font-size: .6875rem; font-weight: 700; color: var(--on-surface); text-align: center; }
.sx-flow-qty   { font-size: .625rem; color: var(--on-surface-var); text-align: center; }
.sx-flow-line  { position: absolute; top: 17px; left: 50%; width: 100%; height: 1px; background: var(--border); z-index: 0; }
.sx-flow-line--done { background: #16a34a; }

/* ── Progress bars ─────────────────────────────────────────────────────────── */
.sx-bar-track { height: 5px; background: var(--border); border-radius: 999px; overflow: hidden; }
.sx-bar-fill  { height: 100%; background: var(--green); border-radius: 999px; transition: width .5s ease; }

/* ── Info cell ─────────────────────────────────────────────────────────────── */
.sx-info-cell { border: 1px solid var(--border); border-radius: 6px; padding: 5px 8px; }
.sx-info-cell span   { font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; margin-bottom: 1px; }
.sx-info-cell strong { font-size: .8125rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Timeline (activity) ───────────────────────────────────────────────────── */
.sx-timeline { display: flex; flex-direction: column; }
.sx-timeline-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--border); }
.sx-timeline-item:last-child { border-bottom: none; }
.sx-timeline-dot { width: 26px; height: 26px; border-radius: 6px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.sx-timeline-dot--success { border-color: #86efac; background: #f0fdf4; color: #16a34a; }
.sx-timeline-dot--primary { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }
.sx-timeline-dot--warning { border-color: #fcd34d; background: #fffbeb; color: #92400e; }
.sx-timeline-dot--info    { border-color: #7dd3fc; background: #f0f9ff; color: #0369a1; }
.sx-timeline-body { flex: 1; min-width: 0; }

/* ── Region cards ──────────────────────────────────────────────────────────── */
.sx-region-card { border: 1px solid var(--border); border-radius: 6px; padding: .75rem; }

/* ── Sustainability ────────────────────────────────────────────────────────── */
.sx-sus-badge { display: inline-flex; background: rgba(0,69,50,.07); color: var(--green); border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; }
.sx-sus-badge--amber { background: #fef3c7; color: #92400e; }
.sx-sus-badge--blue  { background: #dbeafe; color: #1e40af; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.sx-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: .875rem; border-radius: 8px; border: 1px solid; }
.sx-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.sx-insight-card--primary { background: #eff6ff; border-color: #bfdbfe; }
.sx-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.sx-insight-icon { font-size: 13px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.sx-insight-text { font-size: .8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.sx-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--border); }
.sx-alert-row:last-child { border-bottom: none; }
.sx-toggle { width: 32px; height: 18px; border-radius: 999px; border: 1px solid var(--border); padding: 2px; background: #fff; cursor: pointer; transition: background .2s; flex-shrink: 0; }
.sx-toggle i { display: block; width: 12px; height: 12px; border-radius: 50%; background: var(--border); transition: transform .2s; }
.sx-toggle--on { background: var(--green); border-color: var(--green); }
.sx-toggle--on i { background: #fff; transform: translateX(14px); }

/* ── Pagination ────────────────────────────────────────────────────────────── */
.sx-pagination-bar { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: .5rem; }
.sx-page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 30px; border-radius: 5px; border: 1px solid var(--border); background: #fff; color: var(--on-surface); font-size: .8125rem; font-weight: 600; text-decoration: none; padding: 0 9px; }
.sx-page-btn:hover { background: var(--surface-low); }
.sx-page-btn--active  { background: var(--green); border-color: var(--green); color: #fff; }
.sx-page-btn--disabled{ opacity: .4; pointer-events: none; }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.sx-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.sx-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: .75rem; }
.sx-fab { width: 46px; height: 46px; border-radius: 50%; border: 1.5px solid var(--green); background: var(--green); color: #fff; font-size: 18px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.sx-fab:hover { background: #065f46; }
.sx-chatbot { width: 310px; border-radius: 10px; overflow: hidden; background: #fff; border: 1px solid var(--border); display: flex; flex-direction: column; }
.sx-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.sx-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.sx-chatbot__avatar { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 13px; }
.sx-chatbot__name { font-size: .875rem; font-weight: 700; }
.sx-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: .625rem; opacity: .8; }
.sx-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.sx-chatbot__close { border: none; background: none; color: rgba(255,255,255,.8); font-size: 20px; line-height: 1; cursor: pointer; }
.sx-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 190px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.sx-chat-msg { font-size: .8125rem; padding: 8px 10px; border-radius: 8px; line-height: 1.5; max-width: 90%; }
.sx-chat-msg--bot  { background: #fff; color: var(--on-surface); border: 1px solid var(--border); border-radius: 8px 8px 8px 2px; }
.sx-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 8px 8px 2px 8px; }
.sx-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 4px; padding: 7px 10px; border-top: 1px solid var(--border); }
.sx-prompt-chip { font-size: .6rem; padding: 3px 8px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.sx-prompt-chip:hover { background: #dcfce7; }
.sx-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--border); }
.sx-chatbot__input input { flex: 1; border: 1px solid var(--border); border-radius: 6px; padding: 6px 9px; font-size: .8125rem; outline: none; }
.sx-chatbot__input input:focus { border-color: var(--green); }
.sx-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 6px; width: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px; }
.sx-chat-enter-active, .sx-chat-leave-active { transition: opacity .2s ease, transform .2s ease; }
.sx-chat-enter-from, .sx-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .sx-rail { position: static; } }
@media (max-width: 767.98px) {
    .sx-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
    .sx-tl-label, .sx-tl-date { font-size: .5625rem; }
}
</style>
