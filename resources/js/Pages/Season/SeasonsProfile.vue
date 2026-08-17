<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ElMessageBox, ElNotification } from 'element-plus';
import {
    ArrowDown, Box, ChatDotRound, Check, CircleCheckFilled,
    Clock, CollectionTag, Delete, Download,
    EditPen, Files, Location,
    Opportunity, Plus, Promotion, Star, Tickets, TrendCharts,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import EditSeasonModal from '@/Components/Modals/EditSeasonModal.vue';

const props = defineProps({
    season:               { type: Object, required: true },
    harvests:             { type: Array,  default: () => [] },
    regionOptions:        { type: Array,  default: () => [] },
    farmOptions:          { type: Array,  default: () => [] },
    pickMethodOptions:    { type: Array,  default: () => [] },
    harvestSeasonOptions: { type: Array,  default: () => [] },
    statusOptions:        { type: Array,  default: () => [] },
});

/* ── Modal / chat state ──────────────────────────────────────── */
const editModalOpen = ref(false);
const chatOpen      = ref(false);
const chatInput     = ref('');

/* ── Formatters ──────────────────────────────────────────────── */
const formatDate = (v) =>
    v ? new Intl.DateTimeFormat('en-UG', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(v)) : '—';

const formatShort = (v) =>
    v ? new Intl.DateTimeFormat('en-UG', { month: 'short', day: 'numeric' }).format(new Date(v)) : '—';

/* ── Computed (all preserved) ────────────────────────────────── */
const seasonName = computed(() => props.season.name || 'Main Crop 2026');
const seasonCode = computed(() => {
    const year = props.season.start_date ? new Date(props.season.start_date).getFullYear() : 2026;
    return `#SEA-UGA-${year}-${String(props.season.id).padStart(2, '0')}`;
});
const seasonTimeline = computed(() => {
    const start = props.season.start_date ? new Date(props.season.start_date) : new Date('2026-01-15');
    const end   = props.season.end_date   ? new Date(props.season.end_date)   : new Date('2026-06-20');
    return `${formatShort(start)} – ${formatShort(end)}`;
});
const totalHarvests      = computed(() => props.season.harvests_count    ?? props.harvests.length);
const totalQuantity      = computed(() => props.season.harvests_sum_weight ?? 0);
const totalHarvestValue  = computed(() => props.season.harvests_sum_price  ?? 0);

const seasonHarvests = computed(() => props.harvests.map((h) => ({
    rawId:    h.id,
    id:       `HV-${String(h.id).padStart(3, '0')}`,
    farm:     h.farm?.name  || 'Unassigned',
    region:   h.farm?.location || h.variety || '—',
    date:     formatDate(h.harvest_date),
    quantity: h.weight  ? `${Number(h.weight).toLocaleString()} kg` : '—',
    score:    h.cup_score    ? Number(h.cup_score).toFixed(1)      : '—',
    moisture: h.moisture_content ? `${Number(h.moisture_content).toFixed(1)}%` : '—',
    status:   (h.status || 'pending').toUpperCase(),
    showUrl:  route('harvest.show', h.id),
})));

/* ── Preserved actions ───────────────────────────────────────── */
const handleOptionsCommand = (command) => {
    if (command === 'edit') { editModalOpen.value = true; return; }
    if (command === 'add-harvest') { router.get(route('season.create-harvest', props.season.id)); return; }
    if (command === 'delete') {
        ElMessageBox.confirm(
            'This will permanently delete the current season. Continue?',
            'Delete season',
            { confirmButtonText: 'Delete', cancelButtonText: 'Cancel', type: 'warning' },
        ).then(() => {
            router.delete(route('season.destroy', props.season.id), { preserveScroll: true });
        }).catch(() => {});
    }
};

const notifySeasonUpdateSuccess = (message) => {
    ElNotification({ title: 'Season Saved', message, type: 'success', duration: 3200, offset: 84 });
};

const notifyHarvestDeleteSuccess = (message) => {
    ElNotification({ title: 'Harvest Deleted', message, type: 'success', duration: 3200, offset: 84 });
};

const openHarvestProfile = (row) => { if (row?.showUrl) router.get(row.showUrl); };

const deleteHarvest = (row) => {
    ElMessageBox.confirm(
        `This will permanently delete ${row.id}. Continue?`,
        'Delete harvest',
        { confirmButtonText: 'Delete', cancelButtonText: 'Cancel', type: 'warning' },
    ).then(() => {
        router.delete(route('season.harvest.destroy', [props.season.id, row.rawId]), {
            preserveScroll: true,
            onSuccess: (page) => {
                notifyHarvestDeleteSuccess(page.props.flash?.success || `${row.id} deleted successfully.`);
            },
        });
    }).catch(() => {});
};

/* ── Static mock data ────────────────────────────────────────── */
const timelineStages = [
    { label: 'Planting',     date: 'Jan 2025', done: true,  current: false, verify: 'Completed'  },
    { label: 'Flowering',    date: 'Mar 2025', done: true,  current: false, verify: 'Completed'  },
    { label: 'Harvest Start',date: 'May 2025', done: true,  current: false, verify: 'Verified'   },
    { label: 'Peak Harvest', date: 'Jun 2025', done: false, current: true,  verify: 'In Progress'},
    { label: 'Processing',   date: 'Jul 2025', done: false, current: false, verify: 'Pending'    },
    { label: 'Export Window',date: 'Aug 2025', done: false, current: false, verify: 'Pending'    },
];

const regionBreakdown = [
    { name: 'Mount Elgon',       vol: '12,400 kg', farms: 8,  score: 88.2, readiness: 92 },
    { name: 'Rwenzori',          vol: '9,800 kg',  farms: 6,  score: 87.4, readiness: 88 },
    { name: 'Central Uganda',    vol: '7,200 kg',  farms: 5,  score: 86.1, readiness: 82 },
    { name: 'Southwestern Uganda',vol: '5,600 kg', farms: 4,  score: 85.8, readiness: 76 },
];

const batchRows = [
    { id: 'BTH-0042', type: 'Arabica',  qty: '4,200 kg', moisture: '11.2%', score: '87.4', status: 'Processing' },
    { id: 'BTH-0041', type: 'Arabica',  qty: '3,800 kg', moisture: '10.8%', score: '88.1', status: 'Ready'      },
    { id: 'BTH-0039', type: 'Robusta',  qty: '2,600 kg', moisture: '12.1%', score: '85.2', status: 'Exported'   },
];

const lotRows = [
    { id: 'LOT-0018', batch: 'BTH-0041', qty: '2,000 kg', price: '$5.2/kg', score: '88.1', status: 'Export Ready' },
    { id: 'LOT-0017', batch: 'BTH-0039', qty: '1,800 kg', price: '$4.8/kg', score: '85.2', status: 'Tokenised'    },
];

const pipelineStages = [
    { label: 'Farm',    count: props.season.farms_count   || 23, sub: 'Sourced',  verify: 'UCDA Verified', done: true,  current: false },
    { label: 'Harvest', count: totalHarvests.value        || 0,  sub: 'Verified', verify: 'Quality Graded',done: true,  current: false },
    { label: 'Batch',   count: props.season.batches_count || 3,  sub: 'Active',   verify: 'Processing',   done: false, current: true  },
    { label: 'Lot',     count: props.season.lots_count    || 2,  sub: 'Created',  verify: 'Pending',      done: false, current: false },
    { label: 'Market',  count: 0,                                sub: 'Pending',  verify: 'Awaiting',     done: false, current: false },
];

const qualityBars = [
    { label: 'Avg Cupping Score',   value: 87, suffix: '/100' },
    { label: 'Aroma',               value: 89, suffix: '/100' },
    { label: 'Flavor',              value: 86, suffix: '/100' },
    { label: 'Acidity',             value: 84, suffix: '/100' },
    { label: 'Body',                value: 83, suffix: '/100' },
    { label: 'Defect-Free Rate',    value: 93, suffix: '%'    },
    { label: 'Moisture Consistency',value: 91, suffix: '%'    },
];

const targetMarkets = [
    { label: 'UAE', pct: 38 },
    { label: 'EU',  pct: 30 },
    { label: 'USA', pct: 20 },
    { label: 'Asia',pct: 12 },
];

const sustainabilityMetrics = [
    { label: 'Organic Compliance',     value: 84, suffix: '%', color: '#004532' },
    { label: 'Shade-Grown Coverage',   value: 72, suffix: '%', color: '#16a05d' },
    { label: 'Climate-Smart Farming',  value: 68, suffix: '%', color: '#2563eb' },
    { label: 'Water Efficiency Score', value: 91, suffix: '%', color: '#d97706' },
];

const activityLog = [
    { label: `Season ${seasonCode.value} created`,        time: '3 mo ago',  dot: 'success' },
    { label: `${pipelineStages[0].count} farms onboarded`,time: '2 mo ago',  dot: 'success' },
    { label: 'Harvest phase started',                     time: '6 wk ago',  dot: 'success' },
    { label: 'Peak production phase reached',             time: '2 wk ago',  dot: 'success' },
    { label: `${batchRows.length} batches created`,       time: '1 wk ago',  dot: 'info'    },
    { label: `${lotRows.length} lots ready for export`,   time: '3 d ago',   dot: 'success' },
    { label: 'Export window opens soon',                  time: '1 d ago',   dot: 'info'    },
];

const chatPrompts = [
    'How is this season performing?',
    'Is it export ready?',
    'Which region is best performing?',
    'What should be improved?',
];

const fillPrompt = (p) => { chatInput.value = p; };
</script>

<template>
    <AppLayout :title="seasonName" flush>
        <div class="sp-page">

            <!-- Page Header ─────────────────────────────────────── -->
            <div class="sp-header">
                <div class="sp-header-left">
                    <div class="sp-kicker">Season Profile</div>
                    <h1 class="sp-title mt-2 mb-2">{{ seasonName }}</h1>
                    <p class="sp-subtitle mb-2">{{ seasonCode }} · Comprehensive coffee production season overview with traceability from farms to export-ready lots.</p>
                    <div class="sp-badge-row">
                        <span class="sp-badge sp-badge--green">Active Season</span>
                        <span class="sp-badge sp-badge--blue">Traceable Production</span>
                        <span class="sp-badge sp-badge--amber">Export Pipeline</span>
                        <span class="sp-badge sp-badge--purple">Verified Data</span>
                    </div>
                </div>
                <div class="sp-header-actions">
                    <button class="sp-btn sp-btn--primary" @click="router.get(route('harvest.index'))">
                        <el-icon><Tickets /></el-icon> View Harvests
                    </button>
                    <button class="sp-btn sp-btn--outline" @click="router.get(route('batch.index'))">
                        <el-icon><Box /></el-icon> View Batches
                    </button>
                    <button class="sp-btn sp-btn--outline" @click="router.get(route('batch.create-season', season.id))">
                        <el-icon><Plus /></el-icon> Create Batch
                    </button>
                    <button class="sp-btn sp-btn--outline">
                        <el-icon><Download /></el-icon> Export Data
                    </button>
                    <el-dropdown trigger="click" @command="handleOptionsCommand">
                        <button type="button" class="sp-btn sp-btn--ghost">
                            Options <el-icon style="font-size:11px; margin-left:3px;"><ArrowDown /></el-icon>
                        </button>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item command="edit"><el-icon><EditPen /></el-icon> Edit Season</el-dropdown-item>
                                <el-dropdown-item command="add-harvest"><el-icon><Plus /></el-icon> Add Harvest</el-dropdown-item>
                                <el-dropdown-item command="delete" class="sp-dropdown-danger"><el-icon><Delete /></el-icon> Delete Season</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                    <button class="sp-btn sp-btn--ghost">
                        <el-icon><ChatDotRound /></el-icon> Ask Advisor
                    </button>
                </div>
            </div>

            <!-- ② Season Overview Card ────────────────────────────── -->
            <div class="sp-overview">

                <!-- Identity -->
                <div class="sp-ov-block">
                    <div class="sp-ov-label">Season Identity</div>
                    <div class="sp-ov-title">{{ seasonName }}</div>
                    <div class="sp-ov-sub">{{ seasonCode }}</div>
                    <div class="sp-ov-meta-grid">
                        <div class="sp-meta-pair">
                            <span class="sp-muted">Start Date</span>
                            <span class="sp-bold">{{ formatDate(season.start_date) }}</span>
                        </div>
                        <div class="sp-meta-pair">
                            <span class="sp-muted">End Date</span>
                            <span class="sp-bold">{{ formatDate(season.end_date) }}</span>
                        </div>
                        <div class="sp-meta-pair">
                            <span class="sp-muted">Status</span>
                            <span class="sp-status sp-status--received">{{ season.status || 'Active' }}</span>
                        </div>
                        <div class="sp-meta-pair">
                            <span class="sp-muted">Timeline</span>
                            <span class="sp-bold">{{ seasonTimeline }}</span>
                        </div>
                        <div class="sp-meta-pair sp-meta-pair--full">
                            <span class="sp-muted">Region(s)</span>
                            <span class="sp-bold">{{ season.region || 'Uganda — Multi-region' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Production Summary -->
                <div class="sp-ov-block">
                    <div class="sp-ov-label">Production Summary</div>
                    <div class="sp-ov-stat-grid">
                        <div class="sp-ov-stat">
                            <div class="sp-ov-stat-val">{{ season.farms_count || 23 }}</div>
                            <div class="sp-muted">Farms</div>
                        </div>
                        <div class="sp-ov-stat">
                            <div class="sp-ov-stat-val">{{ totalHarvests }}</div>
                            <div class="sp-muted">Harvests</div>
                        </div>
                        <div class="sp-ov-stat">
                            <div class="sp-ov-stat-val">{{ batchRows.length }}</div>
                            <div class="sp-muted">Batches</div>
                        </div>
                        <div class="sp-ov-stat">
                            <div class="sp-ov-stat-val">{{ lotRows.length }}</div>
                            <div class="sp-muted">Lots</div>
                        </div>
                    </div>
                    <div class="sp-ov-total">
                        <div class="sp-ov-total-val">{{ Number(totalQuantity).toLocaleString() }} kg</div>
                        <div class="sp-muted">Total Production</div>
                    </div>
                </div>

                <!-- Performance & Quality -->
                <div class="sp-ov-block">
                    <div class="sp-ov-label">Performance &amp; Quality</div>
                    <div class="sp-ov-score-row">
                        <div class="sp-ov-score-item">
                            <div class="sp-ov-score-val" style="color:#004532;">87.4</div>
                            <div class="sp-muted">Cupping Score</div>
                        </div>
                        <div class="sp-ov-score-item">
                            <div class="sp-ov-score-val" style="color:#2563eb;">92%</div>
                            <div class="sp-muted">Quality Consistency</div>
                        </div>
                        <div class="sp-ov-score-item">
                            <div class="sp-ov-score-val" style="color:#d97706;">88%</div>
                            <div class="sp-muted">Export Readiness</div>
                        </div>
                    </div>
                    <div class="sp-progress-wrap" style="margin-top:10px;">
                        <div class="sp-muted" style="font-size:10px; margin-bottom:4px;">Yield Efficiency — 84%</div>
                        <div class="sp-progress-track"><div class="sp-progress-fill" style="width:84%;" /></div>
                    </div>
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <span class="sp-badge sp-badge--green" style="font-size:9px;">Premium Season</span>
                        <span class="sp-badge sp-badge--blue" style="font-size:9px;">Specialty Output</span>
                        <span class="sp-badge sp-badge--amber" style="font-size:9px;">Export Grade</span>
                    </div>
                </div>

            </div>

            <!-- Main two-column body ─────────────────────────────── -->
            <div class="sp-body">

                <!-- Left: Main content ───────────────────────────── -->
                <div class="sp-main">

                    <!-- ③ Season Timeline ────────────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><Clock /></el-icon> Season Timeline</div>
                            <span class="sp-badge sp-badge--amber" style="font-size:9px;">Current: Peak Harvest</span>
                        </div>
                        <div class="sp-card-body sp-card-body--pipe">
                            <div class="sp-pipe">
                                <div v-for="(stage, idx) in timelineStages" :key="stage.label" class="sp-pipe-item">
                                    <div class="sp-pipe-item__top">
                                        <div class="sp-pipe-item__date" :class="{ 'sp-pipe-item__date--current': stage.current }">{{ stage.date }}</div>
                                    </div>
                                    <div class="sp-pipe-item__mid">
                                        <div class="sp-pipe-item__seg" :class="{ 'sp-pipe-item__seg--done': idx > 0 && timelineStages[idx - 1].done, 'sp-pipe-item__seg--hidden': idx === 0 }" />
                                        <div class="sp-pipe-item__dot" :class="{ 'sp-pipe-item__dot--done': stage.done, 'sp-pipe-item__dot--current': stage.current }">
                                            <el-icon v-if="stage.done" style="font-size:12px;"><Check /></el-icon>
                                        </div>
                                        <div class="sp-pipe-item__seg" :class="{ 'sp-pipe-item__seg--done': stage.done, 'sp-pipe-item__seg--hidden': idx === timelineStages.length - 1 }" />
                                    </div>
                                    <div class="sp-pipe-item__bot">
                                        <div class="sp-pipe-item__label" :class="{ 'sp-pipe-item__label--current': stage.current }">{{ stage.label }}</div>
                                        <div class="sp-pipe-item__verify" :class="{ 'sp-pipe-item__verify--done': stage.done, 'sp-pipe-item__verify--current': stage.current }">{{ stage.verify }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⑤ Harvests Table ─────────────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><Tickets /></el-icon> Harvest Overview</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="sp-muted" style="font-size:11px;">{{ totalHarvests }} total</span>
                                <button class="sp-btn sp-btn--primary sp-btn--sm" @click="router.get(route('season.create-harvest', season.id))">
                                    <el-icon><Plus /></el-icon> Add Harvest
                                </button>
                            </div>
                        </div>
                        <div class="sp-table-wrap">
                            <table class="sp-table">
                                <thead>
                                    <tr>
                                        <th>Harvest ID</th>
                                        <th>Farm</th>
                                        <th>Region</th>
                                        <th>Date</th>
                                        <th class="text-end">Qty (kg)</th>
                                        <th class="text-end">Score</th>
                                        <th class="text-end">Moisture</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="seasonHarvests.length === 0">
                                        <td colspan="9" class="sp-empty">No harvests attached to this season yet.</td>
                                    </tr>
                                    <tr
                                        v-for="row in seasonHarvests"
                                        :key="row.rawId"
                                        class="sp-table-row--clickable"
                                        @click="openHarvestProfile(row)"
                                    >
                                        <td><span class="sp-code">{{ row.id }}</span></td>
                                        <td><span class="sp-primary" style="font-size:12px;">{{ row.farm }}</span></td>
                                        <td><span class="sp-muted">{{ row.region }}</span></td>
                                        <td><span class="sp-muted">{{ row.date }}</span></td>
                                        <td class="text-end"><span class="sp-primary" style="font-size:12px;">{{ row.quantity }}</span></td>
                                        <td class="text-end"><span class="sp-score">{{ row.score }}</span></td>
                                        <td class="text-end"><span class="sp-muted">{{ row.moisture }}</span></td>
                                        <td>
                                            <span class="sp-status" :class="row.status === 'VERIFIED' ? 'sp-status--received' : 'sp-status--ready'">
                                                {{ row.status }}
                                            </span>
                                        </td>
                                        <td class="text-end" @click.stop>
                                            <button class="sp-del-btn" title="Delete harvest" @click="deleteHarvest(row)">
                                                <el-icon><Delete /></el-icon>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="sp-table-foot">
                            <span class="sp-muted">{{ totalHarvests }} harvests · {{ Number(totalQuantity).toLocaleString() }} kg total</span>
                            <span class="sp-badge sp-badge--green" style="font-size:9px;">Average quality trend: ↑</span>
                        </div>
                    </div>

                    <!-- ④ Regional Breakdown ─────────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><Location /></el-icon> Regional Production Breakdown</div>
                        </div>
                        <div class="sp-card-body">
                            <div class="sp-region-grid">
                                <div v-for="r in regionBreakdown" :key="r.name" class="sp-region-card">
                                    <div class="sp-region-name">{{ r.name }}</div>
                                    <div class="sp-region-vol">{{ r.vol }}</div>
                                    <div class="sp-region-meta">
                                        <span class="sp-muted">{{ r.farms }} farms</span>
                                        <span class="sp-muted">Score {{ r.score }}</span>
                                    </div>
                                    <div class="sp-progress-wrap" style="margin-top:8px;">
                                        <div class="sp-muted" style="font-size:9px; margin-bottom:3px;">Export readiness {{ r.readiness }}%</div>
                                        <div class="sp-progress-track"><div class="sp-progress-fill" :style="{ width: r.readiness + '%' }" /></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⑥ Batch Overview ─────────────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><CollectionTag /></el-icon> Batch Overview</div>
                            <button class="sp-btn sp-btn--outline sp-btn--sm" @click="router.get(route('batch.create-season', season.id))">
                                <el-icon><Plus /></el-icon> Create Batch
                            </button>
                        </div>
                        <div class="sp-table-wrap">
                            <table class="sp-table">
                                <thead>
                                    <tr>
                                        <th>Batch ID</th>
                                        <th>Coffee Type</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Moisture</th>
                                        <th class="text-end">Score</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in batchRows" :key="row.id">
                                        <td><span class="sp-code">{{ row.id }}</span></td>
                                        <td><span class="sp-muted">{{ row.type }}</span></td>
                                        <td class="text-end"><span class="sp-primary" style="font-size:12px;">{{ row.qty }}</span></td>
                                        <td class="text-end"><span class="sp-muted">{{ row.moisture }}</span></td>
                                        <td class="text-end"><span class="sp-score">{{ row.score }}</span></td>
                                        <td>
                                            <span class="sp-status" :class="row.status === 'Ready' ? 'sp-status--received' : row.status === 'Exported' ? 'sp-status--ready' : 'sp-status--pending'">
                                                {{ row.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ⑦ Lot Overview ───────────────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><Files /></el-icon> Lot Production Overview</div>
                        </div>
                        <div class="sp-table-wrap">
                            <table class="sp-table">
                                <thead>
                                    <tr>
                                        <th>Lot ID</th>
                                        <th>Batch Source</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Score</th>
                                        <th>Export Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="lotRows.length === 0">
                                        <td colspan="6" class="sp-empty">No lots created this season yet.</td>
                                    </tr>
                                    <tr v-for="row in lotRows" :key="row.id">
                                        <td><span class="sp-code">{{ row.id }}</span></td>
                                        <td><span class="sp-muted">{{ row.batch }}</span></td>
                                        <td class="text-end"><span class="sp-primary" style="font-size:12px;">{{ row.qty }}</span></td>
                                        <td class="text-end"><span class="sp-bold" style="font-size:12px; color:#004532;">{{ row.price }}</span></td>
                                        <td class="text-end"><span class="sp-score">{{ row.score }}</span></td>
                                        <td>
                                            <span class="sp-status" :class="row.status === 'Export Ready' ? 'sp-status--received' : 'sp-status--ready'">
                                                {{ row.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ⑧ Traceability Pipeline ──────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><Promotion /></el-icon> Season Traceability Pipeline</div>
                            <span class="sp-badge sp-badge--amber" style="font-size:9px;">Current: Batch</span>
                        </div>
                        <div class="sp-card-body sp-card-body--pipe">
                            <div class="sp-pipe">
                                <div v-for="(stage, idx) in pipelineStages" :key="stage.label" class="sp-pipe-item">
                                    <div class="sp-pipe-item__top">
                                        <div class="sp-pipe-item__date" :class="{ 'sp-pipe-item__date--current': stage.current }">{{ stage.count }}</div>
                                        <div class="sp-pipe-item__sub">{{ stage.sub }}</div>
                                    </div>
                                    <div class="sp-pipe-item__mid">
                                        <div class="sp-pipe-item__seg" :class="{ 'sp-pipe-item__seg--done': idx > 0 && pipelineStages[idx - 1].done, 'sp-pipe-item__seg--hidden': idx === 0 }" />
                                        <div class="sp-pipe-item__dot" :class="{ 'sp-pipe-item__dot--done': stage.done, 'sp-pipe-item__dot--current': stage.current }">
                                            <el-icon v-if="stage.done" style="font-size:12px;"><Check /></el-icon>
                                        </div>
                                        <div class="sp-pipe-item__seg" :class="{ 'sp-pipe-item__seg--done': stage.done, 'sp-pipe-item__seg--hidden': idx === pipelineStages.length - 1 }" />
                                    </div>
                                    <div class="sp-pipe-item__bot">
                                        <div class="sp-pipe-item__label" :class="{ 'sp-pipe-item__label--current': stage.current }">{{ stage.label }}</div>
                                        <div class="sp-pipe-item__verify" :class="{ 'sp-pipe-item__verify--done': stage.done, 'sp-pipe-item__verify--current': stage.current }">{{ stage.verify }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /sp-main -->

                <!-- Right rail ───────────────────────────────────── -->
                <div class="sp-rail">

                    <!-- ⑩ Quality Analysis ───────────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><Star /></el-icon> Quality Analysis</div>
                            <span class="sp-badge sp-badge--green" style="font-size:9px;">Specialty Grade</span>
                        </div>
                        <div class="sp-card-body">
                            <div class="sp-score-tile">
                                <div class="sp-muted" style="font-size:9px; letter-spacing:.1em; text-transform:uppercase; margin-bottom:4px;">Avg SCA Cupping Score</div>
                                <div class="sp-score-big">87.4</div>
                                <div class="sp-score-stars">★ ★ ★ ★ ★</div>
                            </div>
                            <div class="sp-quality-bars">
                                <div v-for="m in qualityBars" :key="m.label" class="sp-quality-row">
                                    <div class="sp-quality-label">
                                        <span class="sp-muted" style="font-size:11px;">{{ m.label }}</span>
                                        <span class="sp-bold" style="font-size:11px;">{{ m.value }}{{ m.suffix }}</span>
                                    </div>
                                    <div class="sp-bar-track"><div class="sp-bar-fill" :style="{ width: m.value + '%' }" /></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                <span class="sp-badge sp-badge--green" style="font-size:9px;">Export Quality</span>
                                <span class="sp-badge sp-badge--blue" style="font-size:9px;">Premium Season</span>
                            </div>
                        </div>
                    </div>

                    <!-- ⑪ Export & Market Performance ───────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><TrendCharts /></el-icon> Export &amp; Market</div>
                            <span class="sp-badge sp-badge--green" style="font-size:9px;">Export Ready</span>
                        </div>
                        <div class="sp-card-body">
                            <div class="sp-export-stats">
                                <div class="sp-export-stat">
                                    <div class="sp-bold" style="font-size:20px; color:#004532;">88%</div>
                                    <div class="sp-muted">Eligibility</div>
                                </div>
                                <div class="sp-export-stat">
                                    <div class="sp-bold" style="font-size:20px; color:#d97706;">High</div>
                                    <div class="sp-muted">Demand</div>
                                </div>
                                <div class="sp-export-stat">
                                    <div class="sp-bold" style="font-size:18px;">$4.8–5.6</div>
                                    <div class="sp-muted">Avg / kg</div>
                                </div>
                            </div>
                            <div class="sp-muted" style="font-size:9px; text-transform:uppercase; letter-spacing:.12em; margin:10px 0 7px;">Target Markets</div>
                            <div class="sp-markets">
                                <div v-for="m in targetMarkets" :key="m.label" class="sp-market-row">
                                    <span class="sp-bold" style="font-size:11px; min-width:32px;">{{ m.label }}</span>
                                    <div class="sp-bar-track" style="flex:1;"><div class="sp-bar-fill sp-bar-fill--amber" :style="{ width: m.pct + '%' }" /></div>
                                    <span class="sp-muted" style="font-size:10px; min-width:26px; text-align:right;">{{ m.pct }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⑫ Sustainability ─────────────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><Opportunity /></el-icon> Sustainability</div>
                            <span class="sp-badge sp-badge--green" style="font-size:9px;">Eco Certified</span>
                        </div>
                        <div class="sp-card-body">
                            <div class="sp-sustain-bars">
                                <div v-for="m in sustainabilityMetrics" :key="m.label" class="sp-quality-row">
                                    <div class="sp-quality-label">
                                        <span class="sp-muted" style="font-size:11px;">{{ m.label }}</span>
                                        <span class="sp-bold" style="font-size:11px;">{{ m.value }}{{ m.suffix }}</span>
                                    </div>
                                    <div class="sp-bar-track"><div class="sp-bar-fill" :style="{ width: m.value + '%', background: m.color }" /></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mt-3">
                                <span class="sp-badge sp-badge--green" style="font-size:9px;">Sustainable Season</span>
                                <span class="sp-badge sp-badge--blue" style="font-size:9px;">Ethical Production</span>
                            </div>
                        </div>
                    </div>

                    <!-- ⑬ Activity Timeline ──────────────────────── -->
                    <div class="sp-card">
                        <div class="sp-card-head">
                            <div class="sp-card-title"><el-icon><Clock /></el-icon> Activity Timeline</div>
                        </div>
                        <div class="sp-card-body">
                            <div class="sp-timeline">
                                <div v-for="entry in activityLog" :key="entry.label" class="sp-tl-item">
                                    <div class="sp-tl-dot" :class="`sp-tl-dot--${entry.dot}`" />
                                    <div>
                                        <div class="sp-primary" style="font-size:11px;">{{ entry.label }}</div>
                                        <div class="sp-muted">{{ entry.time }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /sp-rail -->

            </div><!-- /sp-body -->

        </div><!-- /sp-page -->

        <!-- ⑯ Floating AI Assistant ──────────────────────────────── -->
        <div class="sp-float">
            <div v-if="chatOpen" class="sp-chat-panel">
                <div class="sp-chat-head">
                    <div>
                        <div class="sp-bold" style="font-size:12px;">Bean Origin Season Advisor</div>
                        <div class="sp-muted">Season intelligence assistant</div>
                    </div>
                    <button class="sp-chat-close" @click="chatOpen = false">×</button>
                </div>
                <div class="sp-chat-prompts">
                    <button v-for="p in chatPrompts" :key="p" class="sp-chat-prompt" @click="fillPrompt(p)">{{ p }}</button>
                </div>
                <div class="sp-chat-input-row">
                    <input v-model="chatInput" class="sp-chat-input" placeholder="Ask about this season…" />
                    <button class="sp-chat-send"><el-icon><Promotion /></el-icon></button>
                </div>
            </div>
            <button class="sp-float-btn" @click="chatOpen = !chatOpen">
                <el-icon><ChatDotRound /></el-icon>
            </button>
        </div>

        <EditSeasonModal
            v-model="editModalOpen"
            :season="season"
            :region-options="regionOptions"
            :status-options="statusOptions"
            @success="notifySeasonUpdateSuccess"
        />

    </AppLayout>
</template>

<style scoped>
/* ── Base ───────────────────────────────────────────────────────── */
.sp-page {
    min-height: calc(100vh - 48px);
    background: var(--surface, #f7f9fb);
    color: #1f2a2a;
    padding: 0 0 48px;
    font-family: 'Manrope', sans-serif;
}

/* ── Header ─────────────────────────────────────────────────────── */
.sp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 24px;
    border-bottom: 1px solid #e8ecec;
    background: #fff;
    flex-wrap: wrap;
}
.sp-kicker {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .16em; text-transform: uppercase;
    color: #94a1b2; margin-bottom: 2px;
}
.sp-title   { font-size: 18px; font-weight: 800; color: #003f2c; margin: 0 0 2px; line-height: 1.15; }
.sp-subtitle { font-size: 12px; color: #657386; margin: 0 0 7px; line-height: 1.5; }
.sp-badge-row { display: flex; gap: 5px; flex-wrap: wrap; }
.sp-header-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }

:deep(.sp-dropdown-danger) { color: #dc2626 !important; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.sp-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    padding: 0 12px; height: 30px; border-radius: 5px;
    font-size: 11px; font-weight: 700; letter-spacing: .03em;
    cursor: pointer; border: 1px solid transparent;
    text-decoration: none; white-space: nowrap;
    transition: background .14s, color .14s, border-color .14s;
}
.sp-btn--primary { background: #003f2c; color: #fff; border-color: #003f2c; }
.sp-btn--primary:hover { background: #004532; }
.sp-btn--outline { background: #fff; color: #263232; border-color: #d4d8d8; }
.sp-btn--outline:hover { border-color: #003f2c; color: #003f2c; }
.sp-btn--ghost { background: transparent; color: #657386; border-color: transparent; }
.sp-btn--ghost:hover { background: #f3f4f4; }
.sp-btn--sm { height: 26px; padding: 0 9px; font-size: 10px; }

/* ── Badges ──────────────────────────────────────────────────────── */
.sp-badge {
    display: inline-flex; align-items: center;
    padding: 3px 7px; border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
}
.sp-badge--green  { background: #eef5f1; color: #004532; border: 1px solid #c3ddd2; }
.sp-badge--blue   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.sp-badge--amber  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.sp-badge--purple { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

/* ── Overview Card ───────────────────────────────────────────────── */
.sp-overview {
    display: grid; grid-template-columns: repeat(3, 1fr);
    border-bottom: 1px solid #e8ecec;
}
@media (max-width: 900px) { .sp-overview { grid-template-columns: 1fr; } }

.sp-ov-block { padding: 14px 20px; border-right: 1px solid #e8ecec; }
.sp-ov-block:last-child { border-right: none; }
@media (max-width: 900px) { .sp-ov-block { border-right: none; border-bottom: 1px solid #e8ecec; } }

.sp-ov-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800; letter-spacing: .14em;
    text-transform: uppercase; color: #94a1b2; margin-bottom: 5px;
}
.sp-ov-title { font-size: 15px; font-weight: 800; color: #003f2c; line-height: 1.15; }
.sp-ov-sub   { font-size: 11px; color: #657386; margin-top: 1px; margin-bottom: 8px; }

.sp-ov-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 16px; }
.sp-meta-pair { display: flex; flex-direction: column; gap: 1px; }
.sp-meta-pair--full { grid-column: 1 / -1; }

.sp-ov-stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 7px; margin-bottom: 10px; }
.sp-ov-stat { text-align: center; padding: 7px; border: 1px solid #e8ecec; border-radius: 5px; background: #fafbfb; }
.sp-ov-stat-val { font-size: 18px; font-weight: 800; color: #1f2a2a; line-height: 1.1; }
.sp-ov-total { text-align: center; padding: 8px; border: 1px solid #c3ddd2; border-radius: 5px; background: #eef5f1; }
.sp-ov-total-val { font-size: 20px; font-weight: 800; color: #004532; line-height: 1.1; }

.sp-ov-score-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; margin-bottom: 10px; }
.sp-ov-score-item { text-align: center; padding: 8px 4px; }
.sp-ov-score-val { font-size: 18px; font-weight: 800; color: #1f2a2a; line-height: 1.1; }

/* ── Shared text helpers ─────────────────────────────────────────── */
.sp-primary { font-size: 12px; font-weight: 600; color: #1f2a2a; }
.sp-muted   { font-size: 11px; color: #94a1b2; }
.sp-bold    { font-weight: 700; color: #1f2a2a; }

/* ── Status dots ─────────────────────────────────────────────────── */
.sp-status {
    display: inline-flex; align-items: center; gap: 5px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #657386;
}
.sp-status::before { content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #cbd5df; }
.sp-status--received::before { background: #16a05d; }
.sp-status--ready::before    { background: #2563eb; }
.sp-status--pending::before  { background: #d97706; }

/* ── Progress ────────────────────────────────────────────────────── */
.sp-progress-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #eef2f0; }
.sp-progress-fill  { height: 100%; background: #004532; border-radius: 3px; transition: width .4s; }

/* ── Body layout ─────────────────────────────────────────────────── */
.sp-body {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 0.8fr);
    gap: 16px;
    padding: 16px 24px 0;
}
@media (max-width: 1100px) { .sp-body { grid-template-columns: 1fr; } }
.sp-main { display: flex; flex-direction: column; gap: 12px; }
.sp-rail { display: flex; flex-direction: column; gap: 12px; }

/* ── Card ────────────────────────────────────────────────────────── */
.sp-card { border: 1px solid #eef2f0; border-radius: 8px; background: #fff; overflow: hidden; }
.sp-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 10px; padding: 8px 14px;
    border-bottom: 1px solid #e8ecec; background: #f8f9f9;
    border-radius: 7px 7px 0 0; flex-wrap: wrap;
}
.sp-card-title {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 800; color: #1f2a2a;
    letter-spacing: .04em; text-transform: uppercase;
    font-family: 'IBM Plex Mono', monospace;
}
.sp-card-body { padding: 12px 14px; }
.sp-card-body--pipe { padding: 16px 12px; }

/* ── Pipeline (shared for timeline + traceability) ───────────────── */
.sp-pipe { display: flex; align-items: stretch; overflow-x: auto; }

.sp-pipe-item {
    flex: 1; min-width: 70px;
    display: flex; flex-direction: column; align-items: center;
}
.sp-pipe-item__top {
    text-align: center; padding-bottom: 8px;
    min-height: 36px;
    display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
}
.sp-pipe-item__date {
    font-size: 13px; font-weight: 800; color: #1f2a2a; line-height: 1;
}
.sp-pipe-item__date--current { color: #d97706; }
.sp-pipe-item__sub {
    font-size: 9px; color: #94a1b2; margin-top: 2px;
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: .06em; text-transform: uppercase;
}
.sp-pipe-item__mid { display: flex; align-items: center; width: 100%; }
.sp-pipe-item__seg { flex: 1; height: 2px; background: #eef2f0; transition: background .2s; }
.sp-pipe-item__seg--done   { background: #004532; }
.sp-pipe-item__seg--hidden { background: transparent; }
.sp-pipe-item__dot {
    width: 30px; height: 30px; border-radius: 50%;
    border: 2px solid #d4d8d8; background: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; color: #fff; position: relative; z-index: 1;
    transition: border-color .2s, background .2s;
}
.sp-pipe-item__dot--done    { border-color: #004532; background: #004532; }
.sp-pipe-item__dot--current { border-color: #d97706; background: #d97706; outline: 4px solid rgba(217,119,6,.16); outline-offset: 1px; }
.sp-pipe-item__bot { text-align: center; padding-top: 8px; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.sp-pipe-item__label {
    font-size: 10px; font-weight: 800; color: #1f2a2a;
    font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase;
}
.sp-pipe-item__label--current { color: #d97706; }
.sp-pipe-item__verify {
    font-size: 9px; color: #b8c0cc;
    font-family: 'IBM Plex Mono', monospace; letter-spacing: .04em;
    padding: 2px 6px; border-radius: 3px;
    border: 1px solid #e8ecec; background: #fafbfb; white-space: nowrap;
}
.sp-pipe-item__verify--done    { color: #004532; border-color: #c3ddd2; background: #eef5f1; }
.sp-pipe-item__verify--current { color: #92400e; border-color: #fde68a; background: #fffbeb; }

/* ── Table ───────────────────────────────────────────────────────── */
.sp-table-wrap { overflow-x: auto; }
.sp-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.sp-table thead th {
    padding: 7px 12px; background: #f6f8f8;
    border-bottom: 1px solid #eef2f0;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800; letter-spacing: .12em;
    text-transform: uppercase; color: #7b8796; white-space: nowrap;
}
.sp-table tbody td {
    padding: 8px 12px; border-bottom: 1px solid #f0f2f2; vertical-align: middle;
}
.sp-table tbody tr:last-child td { border-bottom: none; }
.sp-table-row--clickable { cursor: pointer; }
.sp-table-row--clickable:hover td { background: #f8fbf9; }
.sp-table-foot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 14px; border-top: 1px solid #e8ecec; background: #fafbfb;
    flex-wrap: wrap; gap: 8px;
}
.sp-empty { text-align: center; color: #94a1b2; font-size: 12px; padding: 32px 0; }
.sp-code {
    display: inline-flex; align-items: center; padding: 2px 7px;
    border-radius: 4px; background: #eef5f1; color: #003f2c;
    font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 800;
}
.sp-score {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 38px; padding: 2px 7px; border-radius: 4px;
    border: 1px solid #c3ddd2; background: #eef5f1;
    color: #004532; font-size: 11px; font-weight: 800;
}
.sp-del-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 4px;
    background: transparent; border: 1px solid #fee2e2; color: #dc2626;
    cursor: pointer; font-size: 13px; transition: background .14s;
}
.sp-del-btn:hover { background: #fee2e2; }

/* ── Regional grid ───────────────────────────────────────────────── */
.sp-region-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
@media (max-width: 600px) { .sp-region-grid { grid-template-columns: 1fr; } }
.sp-region-card { border: 1px solid #eef2f0; border-radius: 6px; padding: 10px 12px; background: #fafbfb; }
.sp-region-name { font-size: 12px; font-weight: 700; color: #1f2a2a; margin-bottom: 2px; }
.sp-region-vol  { font-size: 16px; font-weight: 800; color: #004532; margin-bottom: 4px; }
.sp-region-meta { display: flex; justify-content: space-between; gap: 8px; }

/* ── Quality / bar panels ────────────────────────────────────────── */
.sp-score-tile { border: 1px solid #eef2f0; border-radius: 6px; padding: 10px 12px; background: #fafbfb; text-align: center; margin-bottom: 12px; }
.sp-score-big  { font-size: 32px; font-weight: 800; color: #004532; line-height: 1; }
.sp-score-stars { margin-top: 6px; font-size: 12px; letter-spacing: .14em; color: #d97706; }
.sp-quality-bars   { display: flex; flex-direction: column; gap: 7px; }
.sp-sustain-bars   { display: flex; flex-direction: column; gap: 7px; }
.sp-quality-row    { display: flex; flex-direction: column; gap: 3px; }
.sp-quality-label  { display: flex; justify-content: space-between; gap: 8px; }
.sp-bar-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #eef2f0; }
.sp-bar-fill  { height: 100%; background: #004532; border-radius: 3px; }
.sp-bar-fill--amber { background: #d97706; }

/* ── Export / markets ────────────────────────────────────────────── */
.sp-export-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; margin-bottom: 8px; text-align: center; }
.sp-export-stat  { padding: 8px 4px; }
.sp-markets      { display: flex; flex-direction: column; gap: 6px; }
.sp-market-row   { display: flex; align-items: center; gap: 7px; }

/* ── Timeline ────────────────────────────────────────────────────── */
.sp-timeline { display: flex; flex-direction: column; gap: 9px; }
.sp-tl-item  { display: flex; align-items: flex-start; gap: 9px; }
.sp-tl-dot   { width: 9px; height: 9px; border-radius: 50%; border: 2px solid #d4d8d8; background: #fff; flex-shrink: 0; margin-top: 2px; }
.sp-tl-dot--success { border-color: #004532; background: #004532; }
.sp-tl-dot--info    { border-color: #2563eb; background: #2563eb; }


/* ── Floating Chat ───────────────────────────────────────────────── */
.sp-float { position: fixed; bottom: 24px; right: 24px; z-index: 200; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.sp-float-btn { width: 44px; height: 44px; border-radius: 50%; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: background .14s; }
.sp-float-btn:hover { background: #004532; }
.sp-chat-panel { width: 290px; border: 1px solid #eef2f0; border-radius: 10px; background: #fff; overflow: hidden; }
.sp-chat-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 13px; border-bottom: 1px solid #e8ecec; background: #f8f9f9; }
.sp-chat-close { font-size: 18px; line-height: 1; background: none; border: none; color: #94a1b2; cursor: pointer; padding: 0; }
.sp-chat-prompts { padding: 8px 12px; display: flex; flex-direction: column; gap: 5px; border-bottom: 1px solid #f0f2f2; }
.sp-chat-prompt { text-align: left; background: #f8f9f9; border: 1px solid #eef2f0; border-radius: 5px; padding: 6px 9px; font-size: 11px; color: #263232; cursor: pointer; transition: background .14s; }
.sp-chat-prompt:hover { background: #eef5f1; border-color: #c3ddd2; color: #004532; }
.sp-chat-input-row { display: flex; align-items: center; padding: 8px 12px; gap: 6px; }
.sp-chat-input { flex: 1; height: 30px; padding: 0 9px; border: 1px solid #d4d8d8; border-radius: 5px; font-size: 11px; outline: none; }
.sp-chat-input:focus { border-color: #003f2c; }
.sp-chat-send { width: 30px; height: 30px; border-radius: 5px; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; }
</style>
