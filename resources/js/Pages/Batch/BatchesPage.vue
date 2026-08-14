<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, CircleCheck, Clock,
    CollectionTag, DataLine, Download, Filter,
    Histogram, Location, Opportunity, Promotion,
    RefreshRight, Star, TrendCharts, Upload, Van,
} from '@element-plus/icons-vue';

const props = defineProps({
    batches: { type: Object, required: true },
    stats: {
        type: Object,
        default: () => ({ total_batches: 0, received_batches: 0, total_weight: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

/* ── View state ──────────────────────────────────────────────── */
const viewMode   = ref('table');
const chatOpen   = ref(false);
const chatInput  = ref('');

/* ── Search / debounce ───────────────────────────────────────── */
const searchQuery = ref(props.filters.search ?? '');
let debounceId = null;
const visitBatchIndex = (page = 1) => {
    router.get(
        route('batch.index'),
        { page, search: searchQuery.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
watch(searchQuery, () => {
    if (debounceId) window.clearTimeout(debounceId);
    debounceId = window.setTimeout(() => visitBatchIndex(1), 280);
});
onBeforeUnmount(() => { if (debounceId) window.clearTimeout(debounceId); });
const resetFilters = () => { searchQuery.value = ''; visitBatchIndex(1); };

/* ── Formatters (preserved) ──────────────────────────────────── */
const formatDate     = (v) => v ? new Intl.DateTimeFormat('en-UG', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(v)) : 'Pending';
const formatWeight   = (v) => Number(v || 0).toLocaleString();
const formatMoisture = (v) => v ? `${Number(v).toFixed(1)}%` : 'Pending';
const processLabel   = (b) => {
    const m = Number(b.moisture_content || 0);
    if (!m) return 'Pending';
    if (m <= 11.5) return 'Stable';
    if (m <= 13)   return 'Review';
    return 'Hold';
};
const scoreLabel = (b) => {
    const m = Number(b.moisture_content || 0);
    if (!m) return 'Pending';
    return Math.max(82, Math.min(94, 96 - m)).toFixed(1);
};
const statusClass = (s) => String(s || 'received').toLowerCase().replace(/\s+/g, '-');
const statusLabel = (s) => String(s || 'received').replace(/_/g, ' ');

/* ── Pagination ──────────────────────────────────────────────── */
const visiblePages = computed(() => {
    const cur  = props.batches.meta.current_page || 1;
    const last = props.batches.meta.last_page    || 1;
    const pages = [];
    for (let p = Math.max(1, cur - 1); p <= Math.min(last, cur + 1); p++) pages.push(p);
    return pages;
});
const rangeSummary = computed(() => {
    const { from = 0, to = 0, total = 0 } = props.batches.meta;
    return total > 0 ? `Showing ${from}–${to} of ${total} batches` : 'No batches recorded';
});

/* ── KPI computed ────────────────────────────────────────────── */
const totalBatches     = computed(() => props.stats.total_batches  || 0);
const activeBatches    = computed(() => props.stats.received_batches || 0);
const completedBatches = computed(() => Math.max(0, totalBatches.value - activeBatches.value));
const totalWeightFmt   = computed(() => `${Number(props.stats.total_weight || 0).toLocaleString()} kg`);
const avgQualityScore  = computed(() => {
    const data   = props.batches.data || [];
    const scores = data.map(b => parseFloat(scoreLabel(b))).filter(s => !isNaN(s));
    return scores.length ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1) : '—';
});
const exportReadyCount = computed(() =>
    (props.batches.data || []).filter(b => { const m = Number(b.moisture_content || 0); return m > 0 && m <= 11.5; }).length,
);

/* ── Static mock data ────────────────────────────────────────── */
const qualityBars = [
    { label: 'Avg Cupping Score',     value: 87, suffix: '/100' },
    { label: 'Moisture Consistency',  value: 91, suffix: '%'    },
    { label: 'Defect-Free Rate',      value: 84, suffix: '%'    },
    { label: 'Export Compliance',     value: 96, suffix: '%'    },
];
const processingSteps = [
    { label: 'Sorting completed',          done: true  },
    { label: 'Moisture stabilised',        done: true  },
    { label: 'Quality grading done',       done: true  },
    { label: 'Contamination check passed', done: false },
    { label: 'Ready for lot creation',     done: false },
];
const completionPct = computed(() => {
    const done = processingSteps.filter(s => s.done).length;
    return Math.round((done / processingSteps.length) * 100);
});
const pipelineStages = [
    { label: 'Harvest', count: 48, sub: 'Verified',   done: true,    current: false },
    { label: 'Batch',   count: totalBatches.value, sub: 'Active', done: false,   current: true  },
    { label: 'Lot',     count: 12, sub: 'Created',    done: false,   current: false },
    { label: 'Market',  count: 8,  sub: 'Listed',     done: false,   current: false },
];
const compositionRows = [
    { farm: 'Sipi Plot A',    harvests: 8, qty: '8,400 kg', pct: 42 },
    { farm: 'Elgon Block B',  harvests: 6, qty: '6,200 kg', pct: 31 },
    { farm: 'Rwenzori Upper', harvests: 4, qty: '3,800 kg', pct: 19 },
    { farm: 'Other Sources',  harvests: 2, qty: '1,600 kg', pct: 8  },
];
const activityLog = [
    { label: 'Batch #BTH-0042 created',                         time: '2 h ago',  dot: 'success' },
    { label: 'Harvests added to Batch #BTH-0041',               time: '5 h ago',  dot: 'success' },
    { label: 'Quality check passed — Batch #BTH-0039',          time: 'Yesterday',dot: 'success' },
    { label: 'Processing started — Batch #BTH-0038',            time: '2 d ago',  dot: 'info'    },
    { label: 'Batch #BTH-0037 ready for lot creation',          time: '3 d ago',  dot: 'success' },
];
const aiInsights = [
    'This batch shows consistent specialty-grade quality across all included harvests.',
    'Moisture levels are optimal — within export processing range for EU and UAE markets.',
    'High buyer demand expected from EU and UAE this season. Consider prioritising lot creation.',
];
const alerts = [
    { label: 'New harvest added to Batch #BTH-0042',                     type: 'info',    time: '2h ago' },
    { label: 'Quality issue detected — Batch #BTH-0040 (moisture 14.2%)', type: 'warning', time: '6h ago' },
    { label: 'Batch #BTH-0039 ready for lot creation',                   type: 'success', time: '1d ago' },
];
const targetMarkets = [
    { label: 'UAE', pct: 35 },
    { label: 'EU',  pct: 28 },
    { label: 'USA', pct: 22 },
    { label: 'Asia',pct: 15 },
];
const chatPrompts = [
    'Is this batch export ready?',
    'Which harvests are included?',
    'Should I create a lot now?',
    'What is the quality score?',
];
const fillPrompt = (p) => { chatInput.value = p; };
</script>

<template>
    <Head title="Batches" />
    <AppLayout title="Batches" flush>
        <div class="bt-page">

            <!-- ① Page Header ─────────────────────────────────────── -->
            <div class="bt-header">
                <div class="bt-header-left">
                    <div class="bt-kicker">Batch Management</div>
                    <h1 class="bt-title">Batches</h1>
                    <p class="bt-subtitle mt-2">
                        Manage aggregated coffee batches created from verified harvests for processing and export readiness.
                    </p>
                    <div class="bt-badge-row mt-3">
                        <span class="bt-badge bt-badge--green">Traceable Batches</span>
                        <span class="bt-badge bt-badge--blue">Quality Controlled</span>
                        <span class="bt-badge bt-badge--amber">Export Ready Pipeline</span>
                    </div>
                </div>
                <div class="bt-header-actions">
                    <Link :href="route('batch.create')" class="bt-btn bt-btn--primary">
                        <el-icon><CollectionTag /></el-icon> Create Batch
                    </Link>
                    <button class="bt-btn bt-btn--outline">
                        <el-icon><Upload /></el-icon> Import Harvests
                    </button>
                    <button class="bt-btn bt-btn--outline">
                        <el-icon><Download /></el-icon> Export Data
                    </button>
                    <button class="bt-btn bt-btn--ghost">
                        <el-icon><ChatDotRound /></el-icon> Ask Advisor
                    </button>
                </div>
            </div>

            <!-- ② KPI Summary Row ─────────────────────────────────── -->
            <div class="bt-kpi-row">
                <div class="bt-kpi">
                    <div class="bt-kpi-icon"><el-icon><Histogram /></el-icon></div>
                    <div class="bt-kpi-body">
                        <div class="bt-kpi-val">{{ totalBatches }}</div>
                        <div class="bt-kpi-lbl">Total Batches</div>
                    </div>
                </div>
                <div class="bt-kpi">
                    <div class="bt-kpi-icon bt-kpi-icon--green"><el-icon><Box /></el-icon></div>
                    <div class="bt-kpi-body">
                        <div class="bt-kpi-val">{{ activeBatches }}</div>
                        <div class="bt-kpi-lbl">Active Batches</div>
                    </div>
                </div>
                <div class="bt-kpi">
                    <div class="bt-kpi-icon bt-kpi-icon--blue"><el-icon><CircleCheck /></el-icon></div>
                    <div class="bt-kpi-body">
                        <div class="bt-kpi-val">{{ completedBatches }}</div>
                        <div class="bt-kpi-lbl">Completed</div>
                    </div>
                </div>
                <div class="bt-kpi">
                    <div class="bt-kpi-icon bt-kpi-icon--amber"><el-icon><DataLine /></el-icon></div>
                    <div class="bt-kpi-body">
                        <div class="bt-kpi-val">{{ totalWeightFmt }}</div>
                        <div class="bt-kpi-lbl">Total Quantity</div>
                    </div>
                </div>
                <div class="bt-kpi">
                    <div class="bt-kpi-icon bt-kpi-icon--purple"><el-icon><Star /></el-icon></div>
                    <div class="bt-kpi-body">
                        <div class="bt-kpi-val">{{ avgQualityScore }}</div>
                        <div class="bt-kpi-lbl">Avg Quality Score</div>
                    </div>
                </div>
                <div class="bt-kpi">
                    <div class="bt-kpi-icon bt-kpi-icon--teal"><el-icon><Promotion /></el-icon></div>
                    <div class="bt-kpi-body">
                        <div class="bt-kpi-val">{{ exportReadyCount }}</div>
                        <div class="bt-kpi-lbl">Export Ready</div>
                    </div>
                </div>
            </div>

            <!-- ③ Filters Bar ─────────────────────────────────────── -->
            <div class="bt-filters">
                <div class="bt-filters-left">
                    <div class="bt-filter-group">
                        <el-icon class="bt-filter-icon"><Filter /></el-icon>
                        <select class="bt-select"><option value="">All Seasons</option></select>
                    </div>
                    <div class="bt-filter-group">
                        <select class="bt-select"><option value="">All Farms</option></select>
                    </div>
                    <div class="bt-filter-group">
                        <select class="bt-select"><option value="">All Regions</option></select>
                    </div>
                    <div class="bt-filter-group">
                        <select class="bt-select"><option value="">Coffee Type</option></select>
                    </div>
                    <div class="bt-filter-group">
                        <select class="bt-select">
                            <option value="">All Status</option>
                            <option value="processing">Processing</option>
                            <option value="ready">Ready</option>
                            <option value="exported">Exported</option>
                        </select>
                    </div>
                </div>
                <div class="bt-filters-right">
                    <div class="bt-search-wrap">
                        <input
                            v-model="searchQuery"
                            class="bt-search"
                            type="text"
                            placeholder="Search batch ID or farm…"
                        />
                    </div>
                    <button class="bt-btn bt-btn--ghost bt-btn--sm" @click="resetFilters">
                        <el-icon><RefreshRight /></el-icon> Reset
                    </button>
                    <div class="bt-view-toggle">
                        <button class="bt-toggle-btn" :class="{ active: viewMode === 'table' }" @click="viewMode = 'table'" title="Table view">
                            <el-icon><Histogram /></el-icon>
                        </button>
                        <button class="bt-toggle-btn" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" title="Grid view">
                            <el-icon><TrendCharts /></el-icon>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ④ Batch List — Table View ─────────────────────────── -->
            <div v-if="viewMode === 'table'" class="bt-card">
                <div class="bt-card-head">
                    <div class="bt-card-title">
                        <el-icon><CollectionTag /></el-icon> All Batches
                    </div>
                    <div class="bt-muted">{{ rangeSummary }}</div>
                </div>
                <div class="bt-table-wrap">
                    <table class="bt-table">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Season / Warehouse</th>
                                <th>Coffee Type</th>
                                <th class="text-end">Bags</th>
                                <th class="text-end">Quantity (kg)</th>
                                <th class="text-end">Moisture</th>
                                <th class="text-end">Quality</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="batches.data.length === 0">
                                <td colspan="10" class="bt-empty">No batches recorded yet.</td>
                            </tr>
                            <tr v-for="batch in batches.data" :key="batch.id">
                                <td>
                                    <span class="bt-code">{{ batch.batch_number }}</span>
                                </td>
                                <td>
                                    <div class="bt-primary">{{ batch.warehouse_location || '—' }}</div>
                                    <div class="bt-muted">{{ batch.season_name || 'Unassigned' }}</div>
                                </td>
                                <td>
                                    <div class="bt-muted">{{ batch.coffee_type || 'Arabica' }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="bt-primary">{{ Number(batch.quantity_bags || 0).toLocaleString() }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="bt-primary">{{ formatWeight(batch.net_weight_kg) }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="bt-primary">{{ formatMoisture(batch.moisture_content) }}</div>
                                    <div class="bt-muted" :class="{ 'bt-ok': processLabel(batch) === 'Stable', 'bt-warn': processLabel(batch) === 'Review', 'bt-hold': processLabel(batch) === 'Hold' }">
                                        {{ processLabel(batch) }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="bt-score">{{ scoreLabel(batch) }}</div>
                                </td>
                                <td>
                                    <span class="bt-status" :class="`bt-status--${statusClass(batch.status)}`">
                                        {{ statusLabel(batch.status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="bt-muted">{{ formatDate(batch.created_at) }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <Link :href="batch.show_url" class="bt-action">View</Link>
                                        <Link :href="route('batch.create-lot', batch.id)" class="bt-action bt-action--lot">Create Lot</Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="bt-foot">
                    <div class="bt-summary">{{ rangeSummary }}</div>
                    <div class="bt-pager">
                        <Link
                            :href="route('batch.index', { page: Math.max(1, batches.meta.current_page - 1), search: filters.search || undefined })"
                            class="bt-page-btn"
                            :class="{ disabled: batches.meta.current_page <= 1 }"
                        >Previous</Link>
                        <Link
                            v-for="page in visiblePages"
                            :key="page"
                            :href="route('batch.index', { page, search: filters.search || undefined })"
                            class="bt-page-btn"
                            :class="{ active: page === batches.meta.current_page }"
                        >{{ page }}</Link>
                        <Link
                            :href="route('batch.index', { page: Math.min(batches.meta.last_page, batches.meta.current_page + 1), search: filters.search || undefined })"
                            class="bt-page-btn"
                            :class="{ disabled: batches.meta.current_page >= batches.meta.last_page }"
                        >Next</Link>
                    </div>
                </div>
            </div>

            <!-- ⑤ Batch Card View — Grid ──────────────────────────── -->
            <div v-if="viewMode === 'grid'" class="bt-grid-section">
                <div class="bt-card-head">
                    <div class="bt-card-title"><el-icon><TrendCharts /></el-icon> Grid View</div>
                    <div class="bt-muted">{{ rangeSummary }}</div>
                </div>
                <div v-if="batches.data.length === 0" class="bt-empty" style="padding:40px 0; text-align:center;">
                    No batches recorded yet.
                </div>
                <div class="bt-grid">
                    <div v-for="batch in batches.data" :key="batch.id" class="bt-grid-card">
                        <div class="bt-grid-card-top">
                            <span class="bt-code">{{ batch.batch_number }}</span>
                            <span class="bt-status" :class="`bt-status--${statusClass(batch.status)}`">{{ statusLabel(batch.status) }}</span>
                        </div>
                        <div class="bt-grid-card-mid">
                            <div class="bt-grid-row">
                                <span class="bt-muted">Season</span>
                                <span class="bt-primary">{{ batch.season_name || '—' }}</span>
                            </div>
                            <div class="bt-grid-row">
                                <span class="bt-muted">Coffee Type</span>
                                <span class="bt-primary">{{ batch.coffee_type || 'Arabica' }}</span>
                            </div>
                            <div class="bt-grid-row">
                                <span class="bt-muted">Warehouse</span>
                                <span class="bt-primary">{{ batch.warehouse_location || '—' }}</span>
                            </div>
                        </div>
                        <div class="bt-grid-metrics">
                            <div class="bt-metric">
                                <div class="bt-metric-val">{{ formatWeight(batch.net_weight_kg) }}<span>kg</span></div>
                                <div class="bt-muted">Quantity</div>
                            </div>
                            <div class="bt-metric">
                                <div class="bt-metric-val">{{ formatMoisture(batch.moisture_content) }}</div>
                                <div class="bt-muted">Moisture</div>
                            </div>
                            <div class="bt-metric">
                                <div class="bt-metric-val">{{ scoreLabel(batch) }}</div>
                                <div class="bt-muted">Score</div>
                            </div>
                        </div>
                        <div class="bt-grid-card-foot">
                            <Link :href="batch.show_url" class="bt-action" style="flex:1; text-align:center;">View Batch</Link>
                            <Link :href="route('batch.create-lot', batch.id)" class="bt-action bt-action--lot" style="flex:1; text-align:center;">Create Lot</Link>
                        </div>
                    </div>
                </div>
                <!-- Grid pagination -->
                <div class="bt-foot" style="margin-top:16px;">
                    <div class="bt-summary">{{ rangeSummary }}</div>
                    <div class="bt-pager">
                        <Link
                            :href="route('batch.index', { page: Math.max(1, batches.meta.current_page - 1) })"
                            class="bt-page-btn"
                            :class="{ disabled: batches.meta.current_page <= 1 }"
                        >Previous</Link>
                        <Link
                            v-for="page in visiblePages"
                            :key="page"
                            :href="route('batch.index', { page })"
                            class="bt-page-btn"
                            :class="{ active: page === batches.meta.current_page }"
                        >{{ page }}</Link>
                        <Link
                            :href="route('batch.index', { page: Math.min(batches.meta.last_page, batches.meta.current_page + 1) })"
                            class="bt-page-btn"
                            :class="{ disabled: batches.meta.current_page >= batches.meta.last_page }"
                        >Next</Link>
                    </div>
                </div>
            </div>

            <!-- Analytics row ─────────────────────────────────────── -->
            <div class="bt-analytics-row">

                <!-- ⑥ Batch Composition ─────────────────────────── -->
                <div class="bt-card bt-card--flex">
                    <div class="bt-card-head">
                        <div class="bt-card-title"><el-icon><Location /></el-icon> Batch Composition</div>
                    </div>
                    <div class="bt-card-body">
                        <div class="bt-muted" style="margin-bottom:12px; font-size:11px;">
                            Source distribution — farms &amp; harvests included
                        </div>
                        <div class="bt-comp-rows">
                            <div v-for="row in compositionRows" :key="row.farm" class="bt-comp-row">
                                <div class="bt-comp-label">
                                    <span class="bt-primary" style="font-size:12px;">{{ row.farm }}</span>
                                    <span class="bt-muted" style="font-size:11px;">{{ row.harvests }} harvests · {{ row.qty }}</span>
                                </div>
                                <div class="bt-comp-bar-wrap">
                                    <div class="bt-comp-bar" :style="{ width: row.pct + '%' }" />
                                </div>
                                <div class="bt-comp-pct">{{ row.pct }}%</div>
                            </div>
                        </div>
                        <div class="bt-comp-totals">
                            <div class="bt-comp-total-item">
                                <div class="bt-kpi-val" style="font-size:18px;">4</div>
                                <div class="bt-muted">Farms</div>
                            </div>
                            <div class="bt-comp-total-item">
                                <div class="bt-kpi-val" style="font-size:18px;">20</div>
                                <div class="bt-muted">Harvests</div>
                            </div>
                            <div class="bt-comp-total-item">
                                <div class="bt-kpi-val" style="font-size:18px;">20,000</div>
                                <div class="bt-muted">Total kg</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ⑦ Quality Analysis ───────────────────────────── -->
                <div class="bt-card bt-card--flex">
                    <div class="bt-card-head">
                        <div class="bt-card-title"><el-icon><Star /></el-icon> Quality Analysis</div>
                        <div class="d-flex gap-2">
                            <span class="bt-badge bt-badge--green" style="font-size:10px;">Specialty Grade</span>
                            <span class="bt-badge bt-badge--blue" style="font-size:10px;">Export Quality</span>
                        </div>
                    </div>
                    <div class="bt-card-body">
                        <div class="bt-quality-bars">
                            <div v-for="m in qualityBars" :key="m.label" class="bt-quality-row">
                                <div class="bt-quality-label">
                                    <span class="bt-primary" style="font-size:12px;">{{ m.label }}</span>
                                    <span class="bt-primary" style="font-size:12px; font-weight:700;">{{ m.value }}{{ m.suffix }}</span>
                                </div>
                                <div class="bt-quality-bar-wrap">
                                    <div class="bt-quality-bar" :style="{ width: m.value + '%' }" />
                                </div>
                            </div>
                        </div>
                        <div class="bt-quality-score-card">
                            <div style="font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:#6b7280;">Avg Cupping Score</div>
                            <div style="font-size:32px; font-weight:800; color:#004532; line-height:1.1;">{{ avgQualityScore }}</div>
                            <div style="font-size:11px; color:#94a1b2;">Specialty grade threshold: 80.0</div>
                        </div>
                    </div>
                </div>

                <!-- ⑧ Processing Status ──────────────────────────── -->
                <div class="bt-card bt-card--flex">
                    <div class="bt-card-head">
                        <div class="bt-card-title"><el-icon><CircleCheck /></el-icon> Processing Status</div>
                        <div class="bt-completion-ring">{{ completionPct }}%</div>
                    </div>
                    <div class="bt-card-body">
                        <div class="bt-muted" style="margin-bottom:12px; font-size:11px;">Processing readiness checklist</div>
                        <div class="bt-steps">
                            <div v-for="step in processingSteps" :key="step.label" class="bt-step">
                                <div class="bt-step-dot" :class="step.done ? 'bt-step-dot--done' : 'bt-step-dot--todo'">
                                    <el-icon v-if="step.done"><Check /></el-icon>
                                </div>
                                <span class="bt-step-label" :class="{ 'bt-step-label--done': step.done }">{{ step.label }}</span>
                            </div>
                        </div>
                        <div class="bt-progress-wrap">
                            <div class="bt-muted" style="font-size:11px; margin-bottom:6px;">Completion: {{ completionPct }}%</div>
                            <div class="bt-progress-track">
                                <div class="bt-progress-fill" :style="{ width: completionPct + '%' }" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ⑨ Batch-to-Lot Pipeline ───────────────────────────── -->
            <div class="bt-card">
                <div class="bt-card-head">
                    <div class="bt-card-title"><el-icon><Promotion /></el-icon> Batch-to-Lot Pipeline</div>
                    <span class="bt-badge bt-badge--green">Current: Batch</span>
                </div>
                <div class="bt-card-body">
                    <div class="bt-pipeline">
                        <template v-for="(stage, idx) in pipelineStages" :key="stage.label">
                            <div class="bt-pipe-stage" :class="{ 'bt-pipe-stage--current': stage.current, 'bt-pipe-stage--done': stage.done }">
                                <div class="bt-pipe-dot" :class="{ 'bt-pipe-dot--current': stage.current, 'bt-pipe-dot--done': stage.done }">
                                    <el-icon v-if="stage.done"><Check /></el-icon>
                                </div>
                                <div class="bt-pipe-label">{{ stage.label }}</div>
                                <div class="bt-pipe-count">{{ stage.count }}</div>
                                <div class="bt-pipe-sub">{{ stage.sub }}</div>
                            </div>
                            <div v-if="idx < pipelineStages.length - 1" class="bt-pipe-conn" :class="{ 'bt-pipe-conn--done': stage.done }" />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Row: Season Context + Export Readiness ─────────────── -->
            <div class="bt-two-col">

                <!-- ⑩ Season Context ─────────────────────────────── -->
                <div class="bt-card">
                    <div class="bt-card-head">
                        <div class="bt-card-title"><el-icon><Clock /></el-icon> Season Context</div>
                    </div>
                    <div class="bt-card-body">
                        <div class="bt-season-row">
                            <div>
                                <div class="bt-muted" style="font-size:10px; text-transform:uppercase; letter-spacing:.1em;">Linked Season</div>
                                <div class="bt-primary" style="font-weight:800; font-size:15px; margin-top:4px;">2024–25 Main Crop</div>
                            </div>
                            <span class="bt-badge bt-badge--green">Active</span>
                        </div>
                        <div class="bt-progress-wrap" style="margin-top:12px;">
                            <div class="bt-muted" style="font-size:11px; margin-bottom:6px;">Season Progress — 68% complete</div>
                            <div class="bt-progress-track"><div class="bt-progress-fill" style="width:68%;" /></div>
                        </div>
                        <div class="bt-season-stats">
                            <div class="bt-season-stat">
                                <div class="bt-kpi-val" style="font-size:16px;">{{ totalBatches }}</div>
                                <div class="bt-muted">Season Batches</div>
                            </div>
                            <div class="bt-season-stat">
                                <div class="bt-kpi-val" style="font-size:16px;">{{ totalWeightFmt }}</div>
                                <div class="bt-muted">Season Output</div>
                            </div>
                            <div class="bt-season-stat">
                                <div class="bt-kpi-val" style="font-size:16px;">87.4</div>
                                <div class="bt-muted">Avg Score</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ⑪ Export Readiness ───────────────────────────── -->
                <div class="bt-card">
                    <div class="bt-card-head">
                        <div class="bt-card-title"><el-icon><Van /></el-icon> Export Readiness</div>
                        <div class="d-flex gap-2">
                            <span class="bt-badge bt-badge--green">Export Ready</span>
                            <span class="bt-badge bt-badge--amber">Premium Batch</span>
                        </div>
                    </div>
                    <div class="bt-card-body">
                        <div class="bt-export-metrics">
                            <div class="bt-export-metric">
                                <div class="bt-kpi-val" style="font-size:22px; color:#004532;">{{ exportReadyCount > 0 ? Math.round((exportReadyCount / (batches.data.length || 1)) * 100) : 0 }}%</div>
                                <div class="bt-muted">Export Eligibility</div>
                            </div>
                            <div class="bt-export-metric">
                                <div class="bt-kpi-val" style="font-size:22px; color:#d97706;">High</div>
                                <div class="bt-muted">Buyer Demand</div>
                            </div>
                            <div class="bt-export-metric">
                                <div class="bt-kpi-val" style="font-size:22px;">$4.8–5.6</div>
                                <div class="bt-muted">Est. Price / kg</div>
                            </div>
                        </div>
                        <div class="bt-muted" style="font-size:10px; text-transform:uppercase; letter-spacing:.1em; margin-top:12px; margin-bottom:8px;">Target Markets</div>
                        <div class="bt-markets">
                            <div v-for="m in targetMarkets" :key="m.label" class="bt-market-row">
                                <span class="bt-market-label">{{ m.label }}</span>
                                <div class="bt-quality-bar-wrap" style="flex:1;">
                                    <div class="bt-quality-bar bt-quality-bar--amber" :style="{ width: m.pct + '%' }" />
                                </div>
                                <span class="bt-muted" style="font-size:11px; min-width:30px; text-align:right;">{{ m.pct }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Row: Activity + AI Insights + Alerts ───────────────── -->
            <div class="bt-three-col">

                <!-- ⑫ Activity Timeline ─────────────────────────── -->
                <div class="bt-card">
                    <div class="bt-card-head">
                        <div class="bt-card-title"><el-icon><Clock /></el-icon> Activity Timeline</div>
                    </div>
                    <div class="bt-card-body">
                        <div class="bt-timeline">
                            <div v-for="entry in activityLog" :key="entry.label" class="bt-tl-item">
                                <div class="bt-tl-dot" :class="`bt-tl-dot--${entry.dot}`" />
                                <div class="bt-tl-body">
                                    <div class="bt-primary" style="font-size:12px;">{{ entry.label }}</div>
                                    <div class="bt-muted">{{ entry.time }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ⑬ AI Batch Insights ──────────────────────────── -->
                <div class="bt-card">
                    <div class="bt-card-head">
                        <div class="bt-card-title"><el-icon><Opportunity /></el-icon> AI Batch Insights</div>
                    </div>
                    <div class="bt-card-body">
                        <div class="bt-insights">
                            <div v-for="(insight, idx) in aiInsights" :key="idx" class="bt-insight">
                                <div class="bt-insight-dot" />
                                <p class="bt-insight-text">{{ insight }}</p>
                            </div>
                        </div>
                        <div class="bt-insight-footer">
                            <div class="bt-muted" style="font-size:10px; text-transform:uppercase; letter-spacing:.1em;">Powered by Bean Origin AI</div>
                        </div>
                    </div>
                </div>

                <!-- ⑭ Smart Alerts ───────────────────────────────── -->
                <div class="bt-card">
                    <div class="bt-card-head">
                        <div class="bt-card-title"><el-icon><Bell /></el-icon> Smart Alerts</div>
                        <span class="bt-badge bt-badge--amber">{{ alerts.length }} Active</span>
                    </div>
                    <div class="bt-card-body">
                        <div class="bt-alerts">
                            <div v-for="alert in alerts" :key="alert.label" class="bt-alert" :class="`bt-alert--${alert.type}`">
                                <div class="bt-alert-dot" />
                                <div style="flex:1;">
                                    <div class="bt-primary" style="font-size:12px;">{{ alert.label }}</div>
                                    <div class="bt-muted">{{ alert.time }}</div>
                                </div>
                            </div>
                        </div>
                        <button class="bt-btn bt-btn--outline bt-btn--sm" style="margin-top:12px; width:100%;">
                            <el-icon><Bell /></el-icon> View All Alerts
                        </button>
                    </div>
                </div>

            </div>

        </div><!-- /bt-page -->

        <!-- ⑮ Floating AI Assistant ──────────────────────────────── -->
        <div class="bt-float">
            <div v-if="chatOpen" class="bt-chat-panel">
                <div class="bt-chat-head">
                    <div>
                        <div class="bt-primary" style="font-size:12px; font-weight:700;">Bean Origin Batch Advisor</div>
                        <div class="bt-muted">Batch intelligence assistant</div>
                    </div>
                    <button class="bt-chat-close" @click="chatOpen = false">×</button>
                </div>
                <div class="bt-chat-prompts">
                    <button v-for="p in chatPrompts" :key="p" class="bt-chat-prompt" @click="fillPrompt(p)">{{ p }}</button>
                </div>
                <div class="bt-chat-input-row">
                    <input v-model="chatInput" class="bt-chat-input" placeholder="Ask about this batch…" />
                    <button class="bt-chat-send">
                        <el-icon><Promotion /></el-icon>
                    </button>
                </div>
            </div>
            <button class="bt-float-btn" @click="chatOpen = !chatOpen">
                <el-icon><ChatDotRound /></el-icon>
            </button>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ── Base ───────────────────────────────────────────────────────── */
.bt-page {
    min-height: calc(100vh - 48px);
    background: var(--surface, #f7f9fb);
    color: #1f2a2a;
    padding: 0 0 48px;
    font-family: 'Manrope', sans-serif;
}

/* ── Header ─────────────────────────────────────────────────────── */
.bt-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 24px;
    border-bottom: 1px solid #e8ecec;
    background: #fff;
    flex-wrap: wrap;
}
.bt-kicker {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: #94a1b2;
    margin-bottom: 3px;
}
.bt-title {
    font-size: 18px;
    font-weight: 800;
    color: #003f2c;
    margin: 0 0 2px;
    line-height: 1.15;
}
.bt-subtitle {
    font-size: 12px;
    color: #657386;
    margin: 0 0 7px;
    max-width: 560px;
    line-height: 1.5;
}
.bt-badge-row { display: flex; gap: 5px; flex-wrap: wrap; }
.bt-header-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.bt-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0 12px;
    height: 30px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .03em;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    white-space: nowrap;
    transition: background .14s, color .14s, border-color .14s;
}
.bt-btn--primary { background: #003f2c; color: #fff; border-color: #003f2c; }
.bt-btn--primary:hover { background: #004532; }
.bt-btn--outline { background: #fff; color: #263232; border-color: #d4d8d8; }
.bt-btn--outline:hover { border-color: #003f2c; color: #003f2c; }
.bt-btn--ghost { background: transparent; color: #657386; border-color: transparent; }
.bt-btn--ghost:hover { background: #f3f4f4; }
.bt-btn--sm { height: 26px; padding: 0 9px; font-size: 10px; }

/* ── Badges ──────────────────────────────────────────────────────── */
.bt-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.bt-badge--green  { background: #eef5f1; color: #004532; border: 1px solid #c3ddd2; }
.bt-badge--blue   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.bt-badge--amber  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.bt-badge--purple { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

/* ── KPI Row ─────────────────────────────────────────────────────── */
.bt-kpi-row {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    border-bottom: 1px solid #e8ecec;
}
@media (max-width: 1200px) { .bt-kpi-row { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 640px)  { .bt-kpi-row { grid-template-columns: repeat(2, 1fr); } }

.bt-kpi {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 14px;
    border-right: 1px solid #e8ecec;
}
.bt-kpi:last-child { border-right: none; }
.bt-kpi-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 5px;
    border: 1px solid #d4d8d8;
    background: #f8f9f9;
    color: #657386;
    font-size: 13px;
    flex-shrink: 0;
}
.bt-kpi-icon--green  { border-color: #c3ddd2; background: #eef5f1; color: #004532; }
.bt-kpi-icon--blue   { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
.bt-kpi-icon--amber  { border-color: #fde68a; background: #fffbeb; color: #92400e; }
.bt-kpi-icon--purple { border-color: #ddd6fe; background: #f5f3ff; color: #6d28d9; }
.bt-kpi-icon--teal   { border-color: #99f6e4; background: #f0fdfa; color: #0f766e; }
.bt-kpi-val  { font-size: 16px; font-weight: 800; color: #1f2a2a; line-height: 1.1; }
.bt-kpi-lbl  { font-size: 10px; color: #94a1b2; margin-top: 1px; }

/* ── Filters Bar ─────────────────────────────────────────────────── */
.bt-filters {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 24px;
    border-bottom: 1px solid #e8ecec;
    background: #fafbfb;
    flex-wrap: wrap;
}
.bt-filters-left  { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.bt-filters-right { display: flex; gap: 8px; align-items: center; }
.bt-filter-group  { display: flex; align-items: center; gap: 5px; }
.bt-filter-icon   { font-size: 12px; color: #94a1b2; }
.bt-select {
    height: 30px;
    padding: 0 10px;
    border: 1px solid #d4d8d8;
    border-radius: 5px;
    background: #fff;
    font-size: 11px;
    color: #263232;
    outline: none;
    cursor: pointer;
}
.bt-select:focus { border-color: #003f2c; }
.bt-search-wrap { position: relative; }
.bt-search {
    height: 30px;
    padding: 0 10px;
    border: 1px solid #d4d8d8;
    border-radius: 5px;
    background: #fff;
    font-size: 11px;
    color: #263232;
    outline: none;
    width: 200px;
}
.bt-search:focus { border-color: #003f2c; }
.bt-view-toggle { display: flex; border: 1px solid #d4d8d8; border-radius: 5px; overflow: hidden; }
.bt-toggle-btn {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: none;
    color: #94a1b2;
    cursor: pointer;
    font-size: 13px;
    transition: background .14s, color .14s;
}
.bt-toggle-btn.active { background: #003f2c; color: #fff; }
.bt-toggle-btn + .bt-toggle-btn { border-left: 1px solid #d4d8d8; }

/* ── Card ────────────────────────────────────────────────────────── */
.bt-card {
    border: 1px solid #e4e7e8;
    border-radius: 8px;
    background: #fff;
    margin: 12px 24px 0;
    overflow: hidden;
}
.bt-card--flex { display: flex; flex-direction: column; }
.bt-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 14px;
    border-bottom: 1px solid #e8ecec;
    background: #f8f9f9;
    border-radius: 7px 7px 0 0;
    flex-wrap: wrap;
}
.bt-card-title {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 800;
    color: #1f2a2a;
    letter-spacing: .04em;
    text-transform: uppercase;
    font-family: 'IBM Plex Mono', monospace;
}
.bt-card-body { padding: 12px 14px; flex: 1; }

/* ── Table ───────────────────────────────────────────────────────── */
.bt-table-wrap { overflow-x: auto; }
.bt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.bt-table thead th {
    padding: 7px 12px;
    background: #f6f8f8;
    border-bottom: 1px solid #e4e7e8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #7b8796;
    white-space: nowrap;
}
.bt-table tbody td {
    padding: 8px 12px;
    border-bottom: 1px solid #f0f2f2;
    vertical-align: middle;
}
.bt-table tbody tr:last-child td { border-bottom: none; }
.bt-table tbody tr:hover td { background: #f8fbf9; }
.bt-primary { font-size: 13px; font-weight: 700; color: #1f2a2a; }
.bt-muted   { font-size: 11px; color: #94a1b2; }
.bt-ok   { color: #16a05d; }
.bt-warn { color: #d97706; }
.bt-hold { color: #dc2626; }
.bt-code {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 5px;
    background: #eef5f1;
    color: #003f2c;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 800;
}
.bt-score {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid #c3ddd2;
    background: #eef5f1;
    color: #004532;
    font-size: 12px;
    font-weight: 800;
}
.bt-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #657386;
}
.bt-status::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #cbd5df;
}
.bt-status--received::before    { background: #16a05d; }
.bt-status--processing::before  { background: #d97706; }
.bt-status--ready::before       { background: #2563eb; }
.bt-status--exported::before    { background: #6d28d9; }
.bt-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    background: #f0f7f3;
    color: #003f2c;
    border: 1px solid #c3ddd2;
    white-space: nowrap;
    transition: background .14s;
}
.bt-action:hover { background: #e0efe7; }
.bt-action--lot { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.bt-action--lot:hover { background: #dbeafe; }
.bt-empty { text-align: center; color: #94a1b2; font-size: 13px; padding: 40px 0; }
.bt-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 14px;
    border-top: 1px solid #e8ecec;
    flex-wrap: wrap;
}
.bt-summary {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    color: #94a1b2;
    letter-spacing: .08em;
}
.bt-pager { display: flex; gap: 6px; align-items: center; }
.bt-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    border: 1px solid #e3e7e8;
    border-radius: 5px;
    background: #fff;
    color: #263232;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition: background .14s, border-color .14s;
}
.bt-page-btn.active   { background: #003f2c; border-color: #003f2c; color: #fff; }
.bt-page-btn.disabled { pointer-events: none; opacity: .4; }
.bt-page-btn:not(.active):not(.disabled):hover { border-color: #003f2c; color: #003f2c; }

/* ── Grid View ───────────────────────────────────────────────────── */
.bt-grid-section {
    border: 1px solid #e4e7e8;
    border-radius: 8px;
    background: #fff;
    margin: 12px 24px 0;
    overflow: hidden;
}
.bt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
    padding: 16px;
}
.bt-grid-card {
    border: 1px solid #e4e7e8;
    border-radius: 7px;
    background: #fff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.bt-grid-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-bottom: 1px solid #f0f2f2;
    background: #f8f9f9;
}
.bt-grid-card-mid { padding: 10px 12px; flex: 1; }
.bt-grid-row { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 6px; font-size: 12px; }
.bt-grid-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    border-top: 1px solid #f0f2f2;
    border-bottom: 1px solid #f0f2f2;
    background: #f0f2f2;
}
.bt-metric { background: #fff; padding: 10px 8px; text-align: center; }
.bt-metric-val { font-size: 15px; font-weight: 800; color: #1f2a2a; line-height: 1.1; }
.bt-metric-val span { font-size: 10px; color: #94a1b2; }
.bt-grid-card-foot { display: flex; gap: 8px; padding: 10px 12px; }

/* ── Analytics Row ───────────────────────────────────────────────── */
.bt-analytics-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin: 12px 24px 0;
}
@media (max-width: 1024px) { .bt-analytics-row { grid-template-columns: 1fr; } }
.bt-analytics-row .bt-card { margin: 0; }

/* Composition */
.bt-comp-rows { display: flex; flex-direction: column; gap: 8px; }
.bt-comp-row { display: grid; grid-template-columns: 1fr 72px 32px; align-items: center; gap: 7px; }
.bt-comp-label { display: flex; flex-direction: column; }
.bt-comp-bar-wrap { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.bt-comp-bar { height: 100%; background: #004532; border-radius: 3px; }
.bt-comp-pct { font-size: 11px; font-weight: 700; color: #657386; text-align: right; }
.bt-comp-totals {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    margin-top: 12px;
    border-top: 1px solid #e8ecec;
    padding-top: 10px;
    text-align: center;
}
.bt-comp-total-item .bt-muted { font-size: 10px; margin-top: 1px; }

/* Quality */
.bt-quality-bars { display: flex; flex-direction: column; gap: 8px; }
.bt-quality-row { display: flex; flex-direction: column; gap: 3px; }
.bt-quality-label { display: flex; justify-content: space-between; }
.bt-quality-bar-wrap { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.bt-quality-bar { height: 100%; background: #004532; border-radius: 3px; }
.bt-quality-bar--amber { background: #d97706; }
.bt-quality-score-card {
    margin-top: 12px;
    padding: 10px 12px;
    border: 1px solid #e4e7e8;
    border-radius: 6px;
    background: #f8f9f9;
}

/* Processing steps */
.bt-steps { display: flex; flex-direction: column; gap: 6px; }
.bt-step { display: flex; align-items: center; gap: 8px; }
.bt-step-dot {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid #d4d8d8;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 10px;
    color: #fff;
    background: #fff;
}
.bt-step-dot--done { border-color: #004532; background: #004532; }
.bt-step-dot--todo { border-color: #d4d8d8; }
.bt-step-label { font-size: 12px; color: #657386; }
.bt-step-label--done { color: #1f2a2a; font-weight: 600; }
.bt-progress-wrap { margin-top: 10px; }
.bt-progress-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.bt-progress-fill { height: 100%; background: #004532; border-radius: 3px; transition: width .4s; }
.bt-completion-ring {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    font-weight: 800;
    color: #004532;
    padding: 3px 8px;
    border: 1px solid #c3ddd2;
    border-radius: 6px;
    background: #eef5f1;
}

/* ── Pipeline ────────────────────────────────────────────────────── */
.bt-pipeline {
    display: flex;
    align-items: center;
    gap: 0;
    overflow-x: auto;
    padding: 8px 0;
}
.bt-pipe-stage {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    min-width: 100px;
    text-align: center;
    padding: 8px 12px;
}
.bt-pipe-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid #d4d8d8;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    color: #fff;
}
.bt-pipe-dot--done    { border-color: #004532; background: #004532; }
.bt-pipe-dot--current { border-color: #d97706; background: #d97706; }
.bt-pipe-label { font-size: 11px; font-weight: 800; color: #1f2a2a; }
.bt-pipe-count { font-size: 18px; font-weight: 800; color: #1f2a2a; line-height: 1; }
.bt-pipe-sub   { font-size: 10px; color: #94a1b2; }
.bt-pipe-stage--current .bt-pipe-label { color: #d97706; }
.bt-pipe-conn {
    flex: 1;
    height: 2px;
    background: #e4e7e8;
    min-width: 24px;
    align-self: center;
    margin-bottom: 32px;
}
.bt-pipe-conn--done { background: #004532; }

/* ── Two-col row ─────────────────────────────────────────────────── */
.bt-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin: 12px 24px 0;
}
@media (max-width: 900px) { .bt-two-col { grid-template-columns: 1fr; } }
.bt-two-col .bt-card { margin: 0; }

/* Season */
.bt-season-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.bt-season-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    margin-top: 14px;
    border-top: 1px solid #e8ecec;
    padding-top: 12px;
    text-align: center;
}
.bt-season-stat .bt-muted { font-size: 10px; margin-top: 2px; }

/* Export */
.bt-export-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; }
.bt-export-metric { text-align: center; padding: 10px 8px; }
.bt-export-metric .bt-muted { font-size: 10px; margin-top: 2px; }
.bt-markets { display: flex; flex-direction: column; gap: 7px; }
.bt-market-row { display: flex; align-items: center; gap: 8px; }
.bt-market-label { font-size: 11px; font-weight: 700; color: #1f2a2a; min-width: 30px; }

/* ── Three-col row ───────────────────────────────────────────────── */
.bt-three-col {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin: 12px 24px 0;
}
@media (max-width: 1024px) { .bt-three-col { grid-template-columns: 1fr; } }
.bt-three-col .bt-card { margin: 0; }

/* Timeline */
.bt-timeline { display: flex; flex-direction: column; gap: 9px; }
.bt-tl-item { display: flex; align-items: flex-start; gap: 9px; }
.bt-tl-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #d4d8d8;
    background: #fff;
    flex-shrink: 0;
    margin-top: 3px;
}
.bt-tl-dot--success { border-color: #004532; background: #004532; }
.bt-tl-dot--info    { border-color: #2563eb; background: #2563eb; }
.bt-tl-body { flex: 1; }

/* AI Insights */
.bt-insights { display: flex; flex-direction: column; gap: 9px; }
.bt-insight { display: flex; align-items: flex-start; gap: 9px; }
.bt-insight-dot { width: 8px; height: 8px; border-radius: 50%; background: #004532; flex-shrink: 0; margin-top: 4px; }
.bt-insight-text { font-size: 12px; color: #1f2a2a; line-height: 1.55; margin: 0; }
.bt-insight-footer { margin-top: 14px; padding-top: 12px; border-top: 1px solid #f0f2f2; }

/* Alerts */
.bt-alerts { display: flex; flex-direction: column; gap: 6px; }
.bt-alert {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 8px 10px;
    border-radius: 5px;
    border: 1px solid #e4e7e8;
}
.bt-alert--info    { background: #eff6ff; border-color: #bfdbfe; }
.bt-alert--warning { background: #fffbeb; border-color: #fde68a; }
.bt-alert--success { background: #eef5f1; border-color: #c3ddd2; }
.bt-alert-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 4px;
    background: #94a1b2;
}
.bt-alert--info    .bt-alert-dot { background: #2563eb; }
.bt-alert--warning .bt-alert-dot { background: #d97706; }
.bt-alert--success .bt-alert-dot { background: #004532; }

/* ── Floating Chat ───────────────────────────────────────────────── */
.bt-float {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 200;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}
.bt-float-btn {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #003f2c;
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: background .14s;
}
.bt-float-btn:hover { background: #004532; }
.bt-chat-panel {
    width: 300px;
    border: 1px solid #e4e7e8;
    border-radius: 10px;
    background: #fff;
    overflow: hidden;
}
.bt-chat-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 14px;
    border-bottom: 1px solid #e8ecec;
    background: #f8f9f9;
}
.bt-chat-close {
    font-size: 18px;
    line-height: 1;
    background: none;
    border: none;
    color: #94a1b2;
    cursor: pointer;
    padding: 0;
}
.bt-chat-prompts {
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    border-bottom: 1px solid #f0f2f2;
}
.bt-chat-prompt {
    text-align: left;
    background: #f8f9f9;
    border: 1px solid #e4e7e8;
    border-radius: 5px;
    padding: 7px 10px;
    font-size: 11px;
    color: #263232;
    cursor: pointer;
    transition: background .14s;
}
.bt-chat-prompt:hover { background: #eef5f1; border-color: #c3ddd2; color: #004532; }
.bt-chat-input-row {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    gap: 6px;
}
.bt-chat-input {
    flex: 1;
    height: 32px;
    padding: 0 10px;
    border: 1px solid #d4d8d8;
    border-radius: 5px;
    font-size: 11px;
    outline: none;
}
.bt-chat-input:focus { border-color: #003f2c; }
.bt-chat-send {
    width: 32px;
    height: 32px;
    border-radius: 5px;
    background: #003f2c;
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
</style>
