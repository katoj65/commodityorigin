<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Bell, Box, ChatDotRound, Check, CircleCheck, Clock,
    CollectionTag, DataAnalysis, DataLine, Document,
    Download, Files, Location, Opportunity, Promotion,
    Star, TrendCharts, Van,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import UpdateBatchModal from '@/Components/Modals/UpdateBatchModal.vue';

const props = defineProps({
    batch:    { type: Object, required: true },
    season:   { type: Object, default: null },
    harvests: { type: Array,  default: () => [] },
});

/* ── Modal ───────────────────────────────────────────────────── */
const updateBatchModalOpen = ref(false);
const chatOpen  = ref(false);
const chatInput = ref('');

/* ── Formatters (preserved) ──────────────────────────────────── */
const formatNumber = (value, digits = 0) =>
    Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });

const formatDate = (v) =>
    v ? new Intl.DateTimeFormat('en-UG', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(v)) : 'Pending';

/* ── Computed (all preserved) ────────────────────────────────── */
const batchCode       = computed(() => props.batch.batch_number || `BTH-${String(props.batch.id).padStart(4, '0')}`);
const gradeLabel      = computed(() => props.batch.grade || 'G1');
const batchTitle      = computed(() => props.batch.variety || 'Arabica — Specialty');
const totalQuantity   = computed(() => Number(props.batch.net_weight_kg || props.season?.harvests_sum_weight || 4200));
const commercialValue = computed(() => Number(props.batch.price || 32540));
const cupScore        = computed(() => Number(props.batch.cup_score || 87.2));
const moistureScore   = computed(() => Number(props.batch.moisture_content || 11.2));
const densityValue    = computed(() => Math.max(720, Math.round(cupScore.value * 8.1)));
const waterActivity   = computed(() => 0.58);
const seasonTitle     = computed(() => props.season?.name || 'Main Harvest 2024/25');
const seasonRegion    = computed(() => props.season?.region || 'Central Highlands Region');
const seasonHealth    = computed(() => Number(props.batch.cup_score || 88.4));
const activeFarms     = computed(() => props.harvests.length || props.season?.harvests_count || 12);
const readinessScore  = computed(() => {
    let score = 88;
    if (props.season)             score += 1;
    if (props.harvests.length > 0) score += 1;
    if (cupScore.value >= 87)     score += 1;
    if (moistureScore.value <= 12) score += 1;
    return Math.min(score, 92);
});
const seasonHref    = computed(() => props.season?.id ? route('season.show', props.season.id) : route('batch.index'));
const canManageBatch = computed(() => Boolean(props.batch.can_manage));

/* ── Table rows (preserved) ──────────────────────────────────── */
const harvestRows = computed(() => {
    if (props.harvests.length > 0) {
        return props.harvests.map((h, idx) => ({
            id: h.id,
            code: h.harvest_number || `HV-${String(idx + 1).padStart(3, '0')}`,
            farm: h.farm?.name || `Source Estate ${idx + 1}`,
            date: formatDate(h.harvest_date),
            qty: formatNumber(h.weight || 0),
            score: h.cup_score ? Number(h.cup_score).toFixed(1) : '87.2',
            moisture: h.moisture_content ? `${Number(h.moisture_content).toFixed(1)}%` : '11.2%',
            status: h.status || 'Verified',
        }));
    }
    return [
        { id: 1, code: 'HV-24-001', farm: 'Sipi Plot A',    date: 'May 3, 2025', qty: '1,200', score: '88.4', moisture: '10.8%', status: 'Verified'  },
        { id: 2, code: 'HV-24-005', farm: 'Elgon Block B',  date: 'May 5, 2025', qty: '850',   score: '87.1', moisture: '11.4%', status: 'Verified'  },
        { id: 3, code: 'HV-24-012', farm: 'Rwenzori Upper', date: 'May 6, 2025', qty: '1,150', score: '86.9', moisture: '11.9%', status: 'Approved'  },
    ];
});

const intelligenceBars = computed(() => [
    { label: 'Moisture Content',  value: `${formatNumber(moistureScore.value, 1)}%`, pct: 81 },
    { label: 'Water Activity',    value: `${waterActivity.value.toFixed(2)} AW`,     pct: 73 },
    { label: 'Bean Density',      value: `${densityValue.value} G/L`,                pct: 89 },
]);

const processingItems = computed(() => [
    { title: props.batch.processing_method || 'Washed Process',                                           sub: 'Processing Method' },
    { title: props.batch.drying_duration   ? `${props.batch.drying_duration}h Fermentation` : '36h Fermentation', sub: 'Controlled Anaerobic' },
    { title: props.batch.warehouse_location || 'Silo B-12 (Temp Controlled)',                             sub: 'Current Location'  },
]);

const readinessItems = computed(() => [
    { label: 'Season Linkage Complete',   done: true  },
    { label: 'Moisture Stabilised',       done: true  },
    { label: 'Quality Grading Completed', done: true  },
    { label: 'Contamination Check Passed',done: false },
    { label: 'Ready for Lot Creation',    done: false },
]);
const completionPct = computed(() => {
    const done = readinessItems.value.filter(s => s.done).length;
    return Math.round((done / readinessItems.value.length) * 100);
});

/* ── Static mock data ────────────────────────────────────────── */
const farmContributions = [
    { farm: 'Sipi Plot A',    qty: '1,200 kg', pct: 42, score: 88.4 },
    { farm: 'Elgon Block B',  qty: '850 kg',   pct: 30, score: 87.1 },
    { farm: 'Rwenzori Upper', qty: '700 kg',   pct: 24, score: 86.9 },
    { farm: 'Other Sources',  qty: '100 kg',   pct: 4,  score: 85.0 },
];
const qualityScores = [
    { label: 'Avg Cupping Score',   value: 87, suffix: '/100' },
    { label: 'Aroma',               value: 88, suffix: '/100' },
    { label: 'Flavor',              value: 86, suffix: '/100' },
    { label: 'Acidity',             value: 84, suffix: '/100' },
    { label: 'Body',                value: 82, suffix: '/100' },
    { label: 'Defect-Free Rate',    value: 94, suffix: '%'    },
    { label: 'Moisture Consistency',value: 91, suffix: '%'    },
];
const pipelineStages = [
    { label: 'Farm',    count: activeFarms.value,        sub: 'Sourced',  verify: 'UCDA Verified',   done: true,  current: false },
    { label: 'Harvest', count: harvestRows.value.length, sub: 'Verified', verify: 'Quality Graded',  done: true,  current: false },
    { label: 'Batch',   count: 1,                        sub: 'Active',   verify: 'In Processing',   done: false, current: true  },
    { label: 'Lot',     count: 0,                        sub: 'Pending',  verify: 'Awaiting',        done: false, current: false },
    { label: 'Market',  count: 0,                        sub: 'Pending',  verify: 'Awaiting',        done: false, current: false },
];
const activityLog = [
    { label: `Batch ${batchCode.value} created`,          time: '2 h ago',   dot: 'success' },
    { label: '3 harvests aggregated into this batch',     time: '3 h ago',   dot: 'success' },
    { label: 'Quality grading passed — Score 87.2',       time: 'Yesterday', dot: 'success' },
    { label: 'Moisture stabilisation confirmed — 11.2%',  time: '2 d ago',   dot: 'success' },
    { label: 'Processing verification completed',         time: '3 d ago',   dot: 'info'    },
    { label: 'Ready for lot creation',                    time: '3 d ago',   dot: 'success' },
];
const aiInsights = [
    'This batch shows consistent specialty-grade performance across all sourced harvests.',
    'High export demand expected for this quality range — EU and UAE buyers are active.',
    'Moisture stability at 11.2% supports premium processing and extended shelf life.',
];
const alerts = [
    { label: `Batch ${batchCode.value} is ready for lot creation`, type: 'success', time: '2h ago'  },
    { label: 'Export readiness achieved — 92% eligibility',        type: 'success', time: '6h ago'  },
    { label: 'Buyer demand spike detected — UAE market',            type: 'info',    time: '1d ago'  },
];
const targetMarkets = [
    { label: 'UAE', pct: 38 },
    { label: 'EU',  pct: 30 },
    { label: 'USA', pct: 20 },
    { label: 'Asia',pct: 12 },
];
const chatPrompts = [
    'Is this batch ready for export?',
    'Should I create a lot now?',
    'Which farms contribute most?',
    'What is the quality score trend?',
];
const fillPrompt = (p) => { chatInput.value = p; };
</script>

<template>

    <AppLayout title="Batch Profile" flush :show-banner="false">
        <div class="bp-page">

            <!--  Page Header ─────────────────────────────────────── -->
            <div class="bp-header">
                <div class="bp-header-left">
                    <div class="bp-kicker">Batch Profile</div>
                    <h1 class="bp-title mt-2 mb-2">{{ batchCode }}</h1>
                    <p class="bp-subtitle mb-2">{{ batchTitle }} · aggregated from verified harvests, prepared for export lot creation.</p>
                    <div class="bp-badge-row">
                        <span class="bp-badge bp-badge--green">Verified Batch</span>
                        <span class="bp-badge bp-badge--blue">Quality Controlled</span>
                        <span class="bp-badge bp-badge--purple">Traceable</span>
                        <span class="bp-badge bp-badge--amber">Export Ready Pipeline</span>
                    </div>
                </div>
                <div class="bp-header-actions">
                    <Link :href="seasonHref" class="bp-btn bp-btn--outline">
                        <el-icon><CollectionTag /></el-icon> View Harvests
                    </Link>
                    <Link :href="route('batch.create-lot', batch.id)" class="bp-btn bp-btn--primary">
                        <el-icon><Files /></el-icon> Create Lot
                    </Link>
                    <button class="bp-btn bp-btn--outline">
                        <el-icon><Download /></el-icon> Export Data
                    </button>
                    <button
                        v-if="canManageBatch"
                        class="bp-btn bp-btn--ghost"
                        @click="updateBatchModalOpen = true"
                    >
                        Edit Batch
                    </button>
                    <button class="bp-btn bp-btn--ghost">
                        <el-icon><ChatDotRound /></el-icon> Ask Advisor
                    </button>
                </div>
            </div>

            <!-- ② Batch Overview Card ─────────────────────────────── -->
            <div class="bp-overview">

                <!-- Identity -->
                <div class="bp-ov-block">
                    <div class="bp-ov-label">Batch Identity</div>
                    <div class="bp-ov-title">{{ batchCode }}</div>
                    <div class="bp-ov-sub">{{ batchTitle }}</div>
                    <div class="bp-ov-meta-grid">
                        <div class="bp-meta-pair">
                            <span class="bp-muted">Grade</span>
                            <span class="bp-bold">{{ gradeLabel }}</span>
                        </div>
                        <div class="bp-meta-pair">
                            <span class="bp-muted">Season</span>
                            <span class="bp-bold">{{ seasonTitle }}</span>
                        </div>
                        <div class="bp-meta-pair">
                            <span class="bp-muted">Region</span>
                            <span class="bp-bold">{{ seasonRegion }}</span>
                        </div>
                        <div class="bp-meta-pair">
                            <span class="bp-muted">Created</span>
                            <span class="bp-bold">{{ formatDate(batch.created_at) }}</span>
                        </div>
                        <div class="bp-meta-pair">
                            <span class="bp-muted">Warehouse</span>
                            <span class="bp-bold">{{ batch.warehouse_location || '—' }}</span>
                        </div>
                        <div class="bp-meta-pair">
                            <span class="bp-muted">Processing</span>
                            <span class="bp-bold">{{ batch.processing_method || 'Washed' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Composition -->
                <div class="bp-ov-block">
                    <div class="bp-ov-label">Batch Composition</div>
                    <div class="bp-ov-stat-grid">
                        <div class="bp-ov-stat">
                            <div class="bp-ov-stat-val">{{ formatNumber(totalQuantity) }}</div>
                            <div class="bp-muted">Total kg</div>
                        </div>
                        <div class="bp-ov-stat">
                            <div class="bp-ov-stat-val">{{ harvestRows.length }}</div>
                            <div class="bp-muted">Harvests</div>
                        </div>
                        <div class="bp-ov-stat">
                            <div class="bp-ov-stat-val">{{ activeFarms }}</div>
                            <div class="bp-muted">Farms</div>
                        </div>
                        <div class="bp-ov-stat">
                            <div class="bp-ov-stat-val">{{ formatNumber(moistureScore, 1) }}%</div>
                            <div class="bp-muted">Avg Moisture</div>
                        </div>
                    </div>
                    <div class="bp-ov-insight">
                        <el-icon style="color:#004532;"><TrendCharts /></el-icon>
                        <span>Moisture within optimal export processing range.</span>
                    </div>
                </div>

                <!-- Status & Traceability -->
                <div class="bp-ov-block">
                    <div class="bp-ov-label">Status &amp; Traceability</div>
                    <div class="bp-ov-status-row">
                        <span class="bp-status bp-status--received">{{ batch.status || 'Processing' }}</span>
                        <div class="d-flex gap-2">
                            <span class="bp-badge bp-badge--green" style="font-size:9px;">Premium Batch</span>
                            <span class="bp-badge bp-badge--blue" style="font-size:9px;">Specialty Grade</span>
                        </div>
                    </div>
                    <div class="bp-ov-score-row">
                        <div class="bp-ov-score-item">
                            <div class="bp-ov-score-val" style="color:#004532;">{{ formatNumber(cupScore, 1) }}</div>
                            <div class="bp-muted">Cupping Score</div>
                        </div>
                        <div class="bp-ov-score-item">
                            <div class="bp-ov-score-val">{{ readinessScore }}%</div>
                            <div class="bp-muted">Export Readiness</div>
                        </div>
                        <div class="bp-ov-score-item">
                            <div class="bp-ov-score-val" style="color:#2563eb;">94%</div>
                            <div class="bp-muted">Traceability</div>
                        </div>
                    </div>
                    <div class="bp-ov-progress">
                        <div class="bp-muted" style="font-size:10px; margin-bottom:5px;">Processing readiness — {{ completionPct }}%</div>
                        <div class="bp-progress-track"><div class="bp-progress-fill" :style="{ width: completionPct + '%' }" /></div>
                    </div>
                    <div style="margin-top:10px;">
                        <span class="bp-badge bp-badge--green">Export Eligible</span>
                    </div>
                </div>

            </div>

            <!-- Main two-column body ─────────────────────────────── -->
            <div class="bp-body">

                <!-- Left: Main content ───────────────────────────── -->
                <div class="bp-main">

                    <!-- ③ Harvest Composition Table ─────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><CollectionTag /></el-icon> Harvest Sources</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="bp-muted" style="font-size:11px;">{{ harvestRows.length }} harvests included</span>
                                <span class="bp-badge bp-badge--green" style="font-size:9px;">Verified</span>
                            </div>
                        </div>
                        <div class="bp-table-wrap">
                            <table class="bp-table">
                                <thead>
                                    <tr>
                                        <th>Harvest ID</th>
                                        <th>Farm</th>
                                        <th>Date</th>
                                        <th class="text-end">Qty (kg)</th>
                                        <th class="text-end">Score</th>
                                        <th class="text-end">Moisture</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in harvestRows" :key="row.id">
                                        <td><span class="bp-code">{{ row.code }}</span></td>
                                        <td><span class="bp-primary" style="font-size:12px;">{{ row.farm }}</span></td>
                                        <td><span class="bp-muted">{{ row.date }}</span></td>
                                        <td class="text-end"><span class="bp-primary" style="font-size:12px;">{{ row.qty }}</span></td>
                                        <td class="text-end"><span class="bp-score">{{ row.score }}</span></td>
                                        <td class="text-end"><span class="bp-muted">{{ row.moisture }}</span></td>
                                        <td>
                                            <span class="bp-status" :class="row.status === 'Verified' ? 'bp-status--received' : 'bp-status--ready'">
                                                {{ row.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="bp-table-foot">
                            <span class="bp-muted">{{ harvestRows.length }} harvest sources · {{ formatNumber(totalQuantity) }} kg total</span>
                            <span class="bp-badge bp-badge--blue" style="font-size:9px;">Source Diversity: High</span>
                        </div>
                    </div>

                    <!-- ④ Farm Contribution Breakdown ────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Location /></el-icon> Farm Contribution</div>
                            <span class="bp-muted" style="font-size:11px;">{{ farmContributions.length }} contributing farms</span>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-farm-grid">
                                <div v-for="f in farmContributions" :key="f.farm" class="bp-farm-card">
                                    <div class="bp-farm-card-top">
                                        <span class="bp-primary" style="font-size:12px; font-weight:700;">{{ f.farm }}</span>
                                        <span class="bp-bold" style="font-size:11px; color:#004532;">{{ f.pct }}%</span>
                                    </div>
                                    <div class="bp-comp-bar-wrap" style="margin:6px 0;">
                                        <div class="bp-comp-bar" :style="{ width: f.pct + '%' }" />
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="bp-muted">{{ f.qty }}</span>
                                        <span class="bp-muted">Score {{ f.score }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⑤ Batch Quality Analysis ─────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Star /></el-icon> Quality Analysis</div>
                            <div class="d-flex gap-2">
                                <span class="bp-badge bp-badge--green" style="font-size:9px;">Specialty Grade</span>
                                <span class="bp-badge bp-badge--blue" style="font-size:9px;">Export Standard</span>
                            </div>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-quality-layout">
                                <!-- Score tile -->
                                <div class="bp-score-tile">
                                    <div class="bp-muted" style="font-size:9px; letter-spacing:.1em; text-transform:uppercase; margin-bottom:6px;">SCA Cupping Score</div>
                                    <div class="bp-score-big">{{ formatNumber(cupScore, 1) }}</div>
                                    <div class="bp-muted" style="font-size:10px; margin-top:4px;">Specialty threshold: 80.0</div>
                                    <div class="bp-score-stars">★ ★ ★ ★ ★</div>
                                    <div style="margin-top:10px;">
                                        <div class="bp-muted" style="font-size:10px; margin-bottom:4px;">Premium Batch</div>
                                        <span class="bp-badge bp-badge--green" style="font-size:9px;">Specialty Grade</span>
                                    </div>
                                </div>
                                <!-- Score bars -->
                                <div class="bp-quality-bars">
                                    <div v-for="m in qualityScores" :key="m.label" class="bp-quality-row">
                                        <div class="bp-quality-label">
                                            <span class="bp-muted" style="font-size:11px;">{{ m.label }}</span>
                                            <span class="bp-bold" style="font-size:11px;">{{ m.value }}{{ m.suffix }}</span>
                                        </div>
                                        <div class="bp-bar-track">
                                            <div class="bp-bar-fill" :style="{ width: m.value + '%' }" />
                                        </div>
                                    </div>
                                    <!-- Intelligence bars -->
                                    <div class="bp-intel-divider">Physical Analysis</div>
                                    <div v-for="b in intelligenceBars" :key="b.label" class="bp-quality-row">
                                        <div class="bp-quality-label">
                                            <span class="bp-muted" style="font-size:11px;">{{ b.label }}</span>
                                            <span class="bp-bold" style="font-size:11px;">{{ b.value }}</span>
                                        </div>
                                        <div class="bp-bar-track">
                                            <div class="bp-bar-fill bp-bar-fill--blue" :style="{ width: b.pct + '%' }" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⑥ Processing Status ──────────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><CircleCheck /></el-icon> Processing Status</div>
                            <div class="bp-completion-pill">{{ completionPct }}% Complete</div>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-processing-layout">
                                <div class="bp-steps">
                                    <div v-for="step in readinessItems" :key="step.label" class="bp-step">
                                        <div class="bp-step-dot" :class="step.done ? 'bp-step-dot--done' : 'bp-step-dot--todo'">
                                            <el-icon v-if="step.done"><Check /></el-icon>
                                        </div>
                                        <div>
                                            <div class="bp-step-label" :class="{ 'bp-step-label--done': step.done }">{{ step.label }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bp-processing-detail">
                                    <div v-for="item in processingItems" :key="item.title" class="bp-proc-item">
                                        <div class="bp-muted" style="font-size:10px; text-transform:uppercase; letter-spacing:.09em;">{{ item.sub }}</div>
                                        <div class="bp-bold" style="font-size:12px; margin-top:2px;">{{ item.title }}</div>
                                    </div>
                                    <div class="bp-progress-wrap" style="margin-top:14px;">
                                        <div class="bp-muted" style="font-size:10px; margin-bottom:5px;">Readiness: {{ completionPct }}%</div>
                                        <div class="bp-progress-track"><div class="bp-progress-fill" :style="{ width: completionPct + '%' }" /></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⑦ Traceability Pipeline ──────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Promotion /></el-icon> Traceability Pipeline</div>
                            <span class="bp-badge bp-badge--amber">Current: Batch</span>
                        </div>
                        <div class="bp-card-body bp-card-body--pipe">
                            <div class="bp-pipe">
                                <div v-for="(stage, idx) in pipelineStages" :key="stage.label" class="bp-pipe-item">
                                    <!-- count + sub above dot -->
                                    <div class="bp-pipe-item__top">
                                        <div class="bp-pipe-item__count" :class="{ 'bp-pipe-item__count--current': stage.current }">
                                            {{ stage.count }}
                                        </div>
                                        <div class="bp-pipe-item__sub">{{ stage.sub }}</div>
                                    </div>
                                    <!-- connector-left · dot · connector-right -->
                                    <div class="bp-pipe-item__mid">
                                        <div
                                            class="bp-pipe-item__seg"
                                            :class="{
                                                'bp-pipe-item__seg--done':    idx > 0 && pipelineStages[idx - 1].done,
                                                'bp-pipe-item__seg--hidden':  idx === 0,
                                            }"
                                        />
                                        <div
                                            class="bp-pipe-item__dot"
                                            :class="{
                                                'bp-pipe-item__dot--done':    stage.done,
                                                'bp-pipe-item__dot--current': stage.current,
                                            }"
                                        >
                                            <el-icon v-if="stage.done" style="font-size:13px;"><Check /></el-icon>
                                        </div>
                                        <div
                                            class="bp-pipe-item__seg"
                                            :class="{
                                                'bp-pipe-item__seg--done':   stage.done,
                                                'bp-pipe-item__seg--hidden': idx === pipelineStages.length - 1,
                                            }"
                                        />
                                    </div>
                                    <!-- label + verify badge below dot -->
                                    <div class="bp-pipe-item__bot">
                                        <div class="bp-pipe-item__label" :class="{ 'bp-pipe-item__label--current': stage.current }">
                                            {{ stage.label }}
                                        </div>
                                        <div class="bp-pipe-item__verify" :class="{ 'bp-pipe-item__verify--done': stage.done, 'bp-pipe-item__verify--current': stage.current }">
                                            {{ stage.verify }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /bp-main -->

                <!-- Right rail ───────────────────────────────────── -->
                <div class="bp-rail">

                    <!-- ⑧ Season Context ─────────────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Clock /></el-icon> Season Context</div>
                            <span class="bp-badge bp-badge--green" style="font-size:9px;">Active</span>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-bold" style="font-size:13px; color:#003f2c;">{{ seasonTitle }}</div>
                            <div class="bp-muted" style="margin-top:2px;">{{ seasonRegion }}</div>
                            <div class="bp-progress-wrap" style="margin-top:10px;">
                                <div class="bp-muted" style="font-size:10px; margin-bottom:4px;">Season progress — 68%</div>
                                <div class="bp-progress-track"><div class="bp-progress-fill" style="width:68%;" /></div>
                            </div>
                            <div class="bp-season-stats">
                                <div class="bp-season-stat">
                                    <div class="bp-bold" style="font-size:16px; color:#004532;">{{ formatNumber(seasonHealth, 1) }}</div>
                                    <div class="bp-muted">Health Score</div>
                                </div>
                                <div class="bp-season-stat">
                                    <div class="bp-bold" style="font-size:16px;">{{ activeFarms }}</div>
                                    <div class="bp-muted">Active Farms</div>
                                </div>
                            </div>
                            <Link :href="seasonHref" class="bp-link-btn">View Season Details</Link>
                        </div>
                    </div>

                    <!-- ⑨ Export Readiness ───────────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Van /></el-icon> Export Readiness</div>
                            <span class="bp-badge bp-badge--green" style="font-size:9px;">Export Ready</span>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-export-stats">
                                <div class="bp-export-stat">
                                    <div class="bp-bold" style="font-size:20px; color:#004532;">{{ readinessScore }}%</div>
                                    <div class="bp-muted">Eligibility</div>
                                </div>
                                <div class="bp-export-stat">
                                    <div class="bp-bold" style="font-size:20px; color:#d97706;">High</div>
                                    <div class="bp-muted">Demand</div>
                                </div>
                                <div class="bp-export-stat">
                                    <div class="bp-bold" style="font-size:18px;">$4.8–5.6</div>
                                    <div class="bp-muted">Est. / kg</div>
                                </div>
                            </div>
                            <div class="bp-muted" style="font-size:9px; text-transform:uppercase; letter-spacing:.12em; margin:10px 0 7px;">Target Markets</div>
                            <div class="bp-markets">
                                <div v-for="m in targetMarkets" :key="m.label" class="bp-market-row">
                                    <span class="bp-bold" style="font-size:11px; min-width:30px;">{{ m.label }}</span>
                                    <div class="bp-bar-track" style="flex:1;"><div class="bp-bar-fill bp-bar-fill--amber" :style="{ width: m.pct + '%' }" /></div>
                                    <span class="bp-muted" style="font-size:10px; min-width:26px; text-align:right;">{{ m.pct }}%</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-2 flex-wrap">
                                <span class="bp-badge bp-badge--green" style="font-size:9px;">Premium Batch</span>
                                <span class="bp-badge bp-badge--purple" style="font-size:9px;">Verified</span>
                            </div>
                        </div>
                    </div>

                    <!-- ⑩ Lot Creation Readiness ─────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Files /></el-icon> Lot Creation</div>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-lot-stats">
                                <div class="bp-meta-pair">
                                    <span class="bp-muted">Eligible Qty</span>
                                    <span class="bp-bold">{{ formatNumber(totalQuantity) }} kg</span>
                                </div>
                                <div class="bp-meta-pair">
                                    <span class="bp-muted">Suggested Lots</span>
                                    <span class="bp-bold">2 × 1,400 kg</span>
                                </div>
                                <div class="bp-meta-pair">
                                    <span class="bp-muted">Quality Status</span>
                                    <span class="bp-badge bp-badge--green" style="font-size:9px;">Eligible</span>
                                </div>
                            </div>
                            <div class="bp-lot-actions">
                                <Link :href="route('batch.create-lot', batch.id)" class="bp-btn bp-btn--primary bp-btn--full">
                                    <el-icon><Files /></el-icon> Create Lot
                                </Link>
                                <div class="d-flex gap-2">
                                    <button class="bp-btn bp-btn--outline" style="flex:1;">Split Batch</button>
                                    <button class="bp-btn bp-btn--outline" style="flex:1;">Merge Batch</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⑪ Activity Timeline ──────────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Clock /></el-icon> Activity Timeline</div>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-timeline">
                                <div v-for="entry in activityLog" :key="entry.label" class="bp-tl-item">
                                    <div class="bp-tl-dot" :class="`bp-tl-dot--${entry.dot}`" />
                                    <div>
                                        <div class="bp-primary" style="font-size:11px;">{{ entry.label }}</div>
                                        <div class="bp-muted">{{ entry.time }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⑫ AI Batch Insights ──────────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Opportunity /></el-icon> AI Batch Insights</div>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-insights">
                                <div v-for="(insight, idx) in aiInsights" :key="idx" class="bp-insight">
                                    <div class="bp-insight-dot" />
                                    <p class="bp-insight-text">{{ insight }}</p>
                                </div>
                            </div>
                            <div class="bp-insight-footer">
                                <span class="bp-muted" style="font-size:9px; text-transform:uppercase; letter-spacing:.1em;">Powered by Bean Origin AI</span>
                            </div>
                        </div>
                    </div>

                    <!-- ⑬ Smart Alerts ───────────────────────────── -->
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <div class="bp-card-title"><el-icon><Bell /></el-icon> Smart Alerts</div>
                            <span class="bp-badge bp-badge--amber" style="font-size:9px;">{{ alerts.length }} Active</span>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-alerts">
                                <div v-for="alert in alerts" :key="alert.label" class="bp-alert" :class="`bp-alert--${alert.type}`">
                                    <div class="bp-alert-dot" />
                                    <div>
                                        <div class="bp-primary" style="font-size:11px;">{{ alert.label }}</div>
                                        <div class="bp-muted">{{ alert.time }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /bp-rail -->

            </div><!-- /bp-body -->

        </div><!-- /bp-page -->

        <!-- ⑭ Floating AI Assistant ──────────────────────────────── -->
        <div class="bp-float">
            <div v-if="chatOpen" class="bp-chat-panel">
                <div class="bp-chat-head">
                    <div>
                        <div class="bp-bold" style="font-size:12px;">Bean Origin Batch Advisor</div>
                        <div class="bp-muted">Profile intelligence assistant</div>
                    </div>
                    <button class="bp-chat-close" @click="chatOpen = false">×</button>
                </div>
                <div class="bp-chat-prompts">
                    <button v-for="p in chatPrompts" :key="p" class="bp-chat-prompt" @click="fillPrompt(p)">{{ p }}</button>
                </div>
                <div class="bp-chat-input-row">
                    <input v-model="chatInput" class="bp-chat-input" placeholder="Ask about this batch…" />
                    <button class="bp-chat-send"><el-icon><Promotion /></el-icon></button>
                </div>
            </div>
            <button class="bp-float-btn" @click="chatOpen = !chatOpen">
                <el-icon><ChatDotRound /></el-icon>
            </button>
        </div>

        <UpdateBatchModal
            v-if="canManageBatch"
            v-model="updateBatchModalOpen"
            :batch="batch"
        />

    </AppLayout>
</template>

<style scoped>
/* ── Base ───────────────────────────────────────────────────────── */
.bp-page {
    min-height: calc(100vh - 48px);
    background: var(--surface, #f7f9fb);
    color: #1f2a2a;
    padding: 0 0 48px;
    font-family: 'Manrope', sans-serif;
}

/* ── Header ─────────────────────────────────────────────────────── */
.bp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 24px;
    border-bottom: 1px solid #e8ecec;
    background: #fff;
    flex-wrap: wrap;
}
.bp-kicker {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: #94a1b2;
    margin-bottom: 2px;
}
.bp-title {
    font-size: 18px;
    font-weight: 800;
    color: #003f2c;
    margin: 0 0 2px;
    line-height: 1.15;
}
.bp-subtitle {
    font-size: 12px;
    color: #657386;
    margin: 0 0 7px;
    line-height: 1.5;
}
.bp-badge-row { display: flex; gap: 5px; flex-wrap: wrap; }
.bp-header-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.bp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
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
.bp-btn--primary { background: #003f2c; color: #fff; border-color: #003f2c; }
.bp-btn--primary:hover { background: #004532; }
.bp-btn--outline { background: #fff; color: #263232; border-color: #d4d8d8; }
.bp-btn--outline:hover { border-color: #003f2c; color: #003f2c; }
.bp-btn--ghost { background: transparent; color: #657386; border-color: transparent; }
.bp-btn--ghost:hover { background: #f3f4f4; }
.bp-btn--full { width: 100%; }

/* ── Badges ──────────────────────────────────────────────────────── */
.bp-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 7px;
    border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.bp-badge--green  { background: #eef5f1; color: #004532; border: 1px solid #c3ddd2; }
.bp-badge--blue   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.bp-badge--amber  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.bp-badge--purple { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

/* ── Overview Card ───────────────────────────────────────────────── */
.bp-overview {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    border-bottom: 1px solid #e8ecec;
}
@media (max-width: 900px) { .bp-overview { grid-template-columns: 1fr; } }

.bp-ov-block {
    padding: 14px 20px;
    border-right: 1px solid #e8ecec;
}
.bp-ov-block:last-child { border-right: none; }
@media (max-width: 900px) { .bp-ov-block { border-right: none; border-bottom: 1px solid #e8ecec; } }

.bp-ov-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #94a1b2;
    margin-bottom: 5px;
}
.bp-ov-title {
    font-size: 15px;
    font-weight: 800;
    color: #003f2c;
    line-height: 1.15;
}
.bp-ov-sub {
    font-size: 11px;
    color: #657386;
    margin-top: 1px;
    margin-bottom: 8px;
}
.bp-ov-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 16px;
}
.bp-meta-pair { display: flex; flex-direction: column; gap: 1px; }
.bp-ov-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 10px;
}
.bp-ov-stat { text-align: center; padding: 8px; border: 1px solid #e8ecec; border-radius: 5px; background: #fafbfb; }
.bp-ov-stat-val { font-size: 18px; font-weight: 800; color: #1f2a2a; line-height: 1.1; }
.bp-ov-insight {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    background: #eef5f1;
    border: 1px solid #c3ddd2;
    border-radius: 5px;
    font-size: 11px;
    color: #004532;
}
.bp-ov-status-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.bp-ov-score-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; margin-bottom: 10px; }
.bp-ov-score-item { text-align: center; padding: 8px 4px; }
.bp-ov-score-val { font-size: 18px; font-weight: 800; color: #1f2a2a; line-height: 1.1; }
.bp-ov-progress { margin-bottom: 8px; }

/* ── Shared text helpers ─────────────────────────────────────────── */
.bp-primary { font-size: 12px; font-weight: 600; color: #1f2a2a; }
.bp-muted   { font-size: 11px; color: #94a1b2; }
.bp-bold    { font-weight: 700; color: #1f2a2a; }

/* ── Status dots ─────────────────────────────────────────────────── */
.bp-status {
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
.bp-status::before {
    content: '';
    display: inline-block;
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #cbd5df;
}
.bp-status--received::before { background: #16a05d; }
.bp-status--ready::before    { background: #2563eb; }

/* ── Progress ────────────────────────────────────────────────────── */
.bp-progress-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.bp-progress-fill  { height: 100%; background: #004532; border-radius: 3px; transition: width .4s; }

/* ── Body layout ─────────────────────────────────────────────────── */
.bp-body {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 0.8fr);
    gap: 16px;
    padding: 16px 24px 0;
}
@media (max-width: 1100px) { .bp-body { grid-template-columns: 1fr; } }

.bp-main { display: flex; flex-direction: column; gap: 12px; }
.bp-rail { display: flex; flex-direction: column; gap: 12px; }

/* ── Card ────────────────────────────────────────────────────────── */
.bp-card {
    border: 1px solid #e4e7e8;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
}
.bp-card-head {
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
.bp-card-title {
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
.bp-card-body { padding: 12px 14px; }

/* ── Table ───────────────────────────────────────────────────────── */
.bp-table-wrap { overflow-x: auto; }
.bp-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.bp-table thead th {
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
.bp-table tbody td {
    padding: 8px 12px;
    border-bottom: 1px solid #f0f2f2;
    vertical-align: middle;
}
.bp-table tbody tr:last-child td { border-bottom: none; }
.bp-table tbody tr:hover td { background: #f8fbf9; }
.bp-table-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    border-top: 1px solid #e8ecec;
    background: #fafbfb;
    flex-wrap: wrap;
    gap: 8px;
}
.bp-code {
    display: inline-flex;
    align-items: center;
    padding: 2px 7px;
    border-radius: 4px;
    background: #eef5f1;
    color: #003f2c;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 800;
}
.bp-score {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    padding: 2px 7px;
    border-radius: 4px;
    border: 1px solid #c3ddd2;
    background: #eef5f1;
    color: #004532;
    font-size: 11px;
    font-weight: 800;
}

/* ── Farm grid ───────────────────────────────────────────────────── */
.bp-farm-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
@media (max-width: 640px) { .bp-farm-grid { grid-template-columns: 1fr; } }
.bp-farm-card {
    border: 1px solid #e4e7e8;
    border-radius: 6px;
    padding: 10px 12px;
    background: #fafbfb;
}
.bp-farm-card-top { display: flex; justify-content: space-between; align-items: center; }
.bp-comp-bar-wrap { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.bp-comp-bar { height: 100%; background: #004532; border-radius: 3px; }

/* ── Quality layout ──────────────────────────────────────────────── */
.bp-quality-layout { display: grid; grid-template-columns: 140px 1fr; gap: 16px; }
@media (max-width: 640px) { .bp-quality-layout { grid-template-columns: 1fr; } }
.bp-score-tile {
    border: 1px solid #e4e7e8;
    border-radius: 6px;
    padding: 12px;
    background: #fafbfb;
    text-align: center;
}
.bp-score-big {
    font-size: 36px;
    font-weight: 800;
    color: #004532;
    line-height: 1;
    margin-bottom: 2px;
}
.bp-score-stars {
    margin-top: 8px;
    font-size: 12px;
    letter-spacing: .16em;
    color: #d97706;
}
.bp-quality-bars { display: flex; flex-direction: column; gap: 7px; }
.bp-quality-row { display: flex; flex-direction: column; gap: 3px; }
.bp-quality-label { display: flex; justify-content: space-between; gap: 8px; }
.bp-bar-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.bp-bar-fill { height: 100%; background: #004532; border-radius: 3px; }
.bp-bar-fill--blue  { background: #2563eb; }
.bp-bar-fill--amber { background: #d97706; }
.bp-intel-divider {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #94a1b2;
    padding-top: 4px;
    border-top: 1px solid #f0f2f2;
}

/* ── Processing layout ───────────────────────────────────────────── */
.bp-processing-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 640px) { .bp-processing-layout { grid-template-columns: 1fr; } }
.bp-steps { display: flex; flex-direction: column; gap: 7px; }
.bp-step { display: flex; align-items: center; gap: 8px; }
.bp-step-dot {
    width: 18px; height: 18px;
    border-radius: 50%;
    border: 2px solid #d4d8d8;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 10px;
    color: #fff;
    background: #fff;
}
.bp-step-dot--done { border-color: #004532; background: #004532; }
.bp-step-label { font-size: 12px; color: #657386; }
.bp-step-label--done { color: #1f2a2a; font-weight: 600; }
.bp-completion-pill {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 800;
    color: #004532;
    background: #eef5f1;
    border: 1px solid #c3ddd2;
    padding: 3px 8px;
    border-radius: 5px;
}
.bp-proc-item { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #f0f2f2; }
.bp-proc-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

/* ── Pipeline ────────────────────────────────────────────────────── */
.bp-card-body--pipe { padding: 16px 12px; }

.bp-pipe {
    display: flex;
    align-items: stretch;
    overflow-x: auto;
}

/* Each stage takes equal width */
.bp-pipe-item {
    flex: 1;
    min-width: 72px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* ── Top: count + sub ── */
.bp-pipe-item__top {
    text-align: center;
    padding-bottom: 8px;
    min-height: 38px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
}
.bp-pipe-item__count {
    font-size: 17px;
    font-weight: 800;
    color: #1f2a2a;
    line-height: 1;
}
.bp-pipe-item__count--current { color: #d97706; }
.bp-pipe-item__sub {
    font-size: 9px;
    color: #94a1b2;
    margin-top: 2px;
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: .06em;
    text-transform: uppercase;
}

/* ── Middle: left-seg · dot · right-seg ── */
.bp-pipe-item__mid {
    display: flex;
    align-items: center;
    width: 100%;
}
.bp-pipe-item__seg {
    flex: 1;
    height: 2px;
    background: #e4e7e8;
    transition: background .2s;
}
.bp-pipe-item__seg--done   { background: #004532; }
.bp-pipe-item__seg--hidden { background: transparent; }

.bp-pipe-item__dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid #d4d8d8;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    position: relative;
    z-index: 1;
    transition: border-color .2s, background .2s;
}
.bp-pipe-item__dot--done {
    border-color: #004532;
    background: #004532;
}
.bp-pipe-item__dot--current {
    border-color: #d97706;
    background: #d97706;
    outline: 4px solid rgba(217, 119, 6, 0.18);
    outline-offset: 1px;
}

/* ── Bottom: label + verify ── */
.bp-pipe-item__bot {
    text-align: center;
    padding-top: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.bp-pipe-item__label {
    font-size: 10px;
    font-weight: 800;
    color: #1f2a2a;
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.bp-pipe-item__label--current { color: #d97706; }
.bp-pipe-item__verify {
    font-size: 9px;
    color: #b8c0cc;
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: .04em;
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid #e8ecec;
    background: #fafbfb;
    white-space: nowrap;
}
.bp-pipe-item__verify--done    { color: #004532; border-color: #c3ddd2; background: #eef5f1; }
.bp-pipe-item__verify--current { color: #92400e; border-color: #fde68a; background: #fffbeb; }

/* ── Rail cards ──────────────────────────────────────────────────── */
.bp-season-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; margin: 10px 0; }
.bp-season-stat { text-align: center; padding: 8px 4px; }
.bp-link-btn {
    display: flex; align-items: center; justify-content: center;
    height: 30px;
    border: 1px solid #d4d8d8;
    border-radius: 5px;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
    color: #263232;
    margin-top: 10px;
    transition: border-color .14s, color .14s;
}
.bp-link-btn:hover { border-color: #003f2c; color: #003f2c; }

.bp-export-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; margin-bottom: 8px; text-align: center; }
.bp-export-stat { padding: 8px 4px; }
.bp-markets { display: flex; flex-direction: column; gap: 6px; }
.bp-market-row { display: flex; align-items: center; gap: 7px; }

.bp-lot-stats { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.bp-lot-actions { display: flex; flex-direction: column; gap: 6px; }

/* ── Timeline ────────────────────────────────────────────────────── */
.bp-timeline { display: flex; flex-direction: column; gap: 9px; }
.bp-tl-item  { display: flex; align-items: flex-start; gap: 9px; }
.bp-tl-dot {
    width: 9px; height: 9px;
    border-radius: 50%;
    border: 2px solid #d4d8d8;
    background: #fff;
    flex-shrink: 0; margin-top: 2px;
}
.bp-tl-dot--success { border-color: #004532; background: #004532; }
.bp-tl-dot--info    { border-color: #2563eb; background: #2563eb; }

/* ── AI Insights ─────────────────────────────────────────────────── */
.bp-insights { display: flex; flex-direction: column; gap: 9px; }
.bp-insight  { display: flex; align-items: flex-start; gap: 8px; }
.bp-insight-dot { width: 7px; height: 7px; border-radius: 50%; background: #004532; flex-shrink: 0; margin-top: 4px; }
.bp-insight-text { font-size: 12px; color: #1f2a2a; line-height: 1.55; margin: 0; }
.bp-insight-footer { margin-top: 10px; padding-top: 10px; border-top: 1px solid #f0f2f2; }

/* ── Alerts ──────────────────────────────────────────────────────── */
.bp-alerts { display: flex; flex-direction: column; gap: 6px; }
.bp-alert {
    display: flex; align-items: flex-start; gap: 8px;
    padding: 8px 10px; border-radius: 5px; border: 1px solid #e4e7e8;
}
.bp-alert--info    { background: #eff6ff; border-color: #bfdbfe; }
.bp-alert--warning { background: #fffbeb; border-color: #fde68a; }
.bp-alert--success { background: #eef5f1; border-color: #c3ddd2; }
.bp-alert-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; background: #94a1b2; }
.bp-alert--info    .bp-alert-dot { background: #2563eb; }
.bp-alert--warning .bp-alert-dot { background: #d97706; }
.bp-alert--success .bp-alert-dot { background: #004532; }

/* ── Floating Chat ───────────────────────────────────────────────── */
.bp-float {
    position: fixed; bottom: 24px; right: 24px; z-index: 200;
    display: flex; flex-direction: column; align-items: flex-end; gap: 10px;
}
.bp-float-btn {
    width: 44px; height: 44px; border-radius: 50%;
    background: #003f2c; color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    transition: background .14s;
}
.bp-float-btn:hover { background: #004532; }
.bp-chat-panel {
    width: 290px; border: 1px solid #e4e7e8; border-radius: 10px; background: #fff; overflow: hidden;
}
.bp-chat-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 13px; border-bottom: 1px solid #e8ecec; background: #f8f9f9;
}
.bp-chat-close { font-size: 18px; line-height: 1; background: none; border: none; color: #94a1b2; cursor: pointer; padding: 0; }
.bp-chat-prompts { padding: 8px 12px; display: flex; flex-direction: column; gap: 5px; border-bottom: 1px solid #f0f2f2; }
.bp-chat-prompt {
    text-align: left; background: #f8f9f9; border: 1px solid #e4e7e8;
    border-radius: 5px; padding: 6px 9px; font-size: 11px; color: #263232; cursor: pointer;
    transition: background .14s;
}
.bp-chat-prompt:hover { background: #eef5f1; border-color: #c3ddd2; color: #004532; }
.bp-chat-input-row { display: flex; align-items: center; padding: 8px 12px; gap: 6px; }
.bp-chat-input {
    flex: 1; height: 30px; padding: 0 9px; border: 1px solid #d4d8d8;
    border-radius: 5px; font-size: 11px; outline: none;
}
.bp-chat-input:focus { border-color: #003f2c; }
.bp-chat-send {
    width: 30px; height: 30px; border-radius: 5px; background: #003f2c;
    color: #fff; border: none; cursor: pointer; display: flex;
    align-items: center; justify-content: center; font-size: 13px;
}
</style>
