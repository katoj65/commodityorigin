<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Clock,
    CollectionTag, DataLine, Download,
    Location, Opportunity, Promotion,
    ShoppingCart, Star, Tickets, TrendCharts, Van,
    Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    harvests:      { type: Object, required: true },
    filters:       { type: Object, default: () => ({ search: '' }) },
    estateOptions: { type: Array,  default: () => [] },
});
/* ── Real computed & search ────────────────────────────────────── */
const visiblePages  = computed(() => {
    const cur  = props.harvests.meta.current_page || 1;
    const last = props.harvests.meta.last_page    || 1;
    const pages = [];
    for (let p = Math.max(1, cur - 1); p <= Math.min(last, cur + 1); p++) pages.push(p);
    return pages;
});
const hasHarvests   = computed(() => props.harvests.data.length > 0);
const totalHarvests = computed(() => props.harvests.meta.total || 0);
const rangeSummary  = computed(() => {
    const { from = 0, to = 0, total = 0 } = props.harvests.meta;
    return `Showing ${from}–${to} of ${total}`;
});
const searchQuery = ref(props.filters.search ?? '');

let debounceId = null;
const visitHarvestIndex = (page = 1) => {
    router.get(
        route('harvest.index'),
        { page, search: searchQuery.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
watch(searchQuery, () => {
    if (debounceId) window.clearTimeout(debounceId);
    debounceId = window.setTimeout(() => visitHarvestIndex(1), 250);
});
onBeforeUnmount(() => { if (debounceId) window.clearTimeout(debounceId); });

const formatDate   = (v) => v ? new Intl.DateTimeFormat('en-UG', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(v)) : 'Pending';
const formatWeight = (v) => `${Number(v || 0).toLocaleString()} kg`;
const formatPrice  = (v) => (v === null || v === undefined || v === '') ? 'Pending' : `Shs. ${Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

/* ── Static mock data ──────────────────────────────────────────── */
const prodBars  = [0, 0, 0, 42, 68, 86, 100, 90, 64, 30, 0, 0];
const months    = ['J','F','M','A','M','J','J','A','S','O','N','D'];

const pipelineStages = [
    { label: 'Harvested', icon: Box,           count: totalHarvests.value || 28, done: true  },
    { label: 'Sorted',    icon: Star,           count: 24,                        done: true  },
    { label: 'Processed', icon: CollectionTag,  count: 18,                        done: false },
    { label: 'Batched',   icon: TrendCharts,    count: 14,                        done: false },
    { label: 'In Lot',    icon: ShoppingCart,   count: 9,                         done: false },
];

const farmSources = [
    { name: 'Sipi Plot A',   harvests: 8,  production: '8,400 kg', quality: 88.5, badges: ['Specialty Grade','Verified Farm'] },
    { name: 'Elgon Block B', harvests: 6,  production: '6,200 kg', quality: 87.2, badges: ['High Yield','Verified Farm']     },
    { name: 'Rwenzori Upper',harvests: 4,  production: '3,800 kg', quality: 86.4, badges: ['Verified Farm']                  },
];

const qualityMetrics = [
    { label: 'Avg Cupping Score', value: '87.4', pct: 87 },
    { label: 'Moisture (Optimal)', value: '11.8%', pct: 82 },
    { label: 'Defect Rate',       value: '0.8%',  pct: 92 },
    { label: 'Ready for Batch',   value: '64%',   pct: 64 },
];

const activityLog = [
    { icon: Box,          text: 'Harvest HV-028 created — Sipi Plot A, 2,400 kg',  date: 'May 7',    tone: 'success' },
    { icon: Star,         text: 'Quality test completed — score 88.5',              date: 'May 6',    tone: 'primary' },
    { icon: Check,        text: 'Moisture check passed — 11.8%',                   date: 'May 5',    tone: 'success' },
    { icon: CollectionTag,text: 'HV-026 approved for batching',                    date: 'May 4',    tone: 'warning' },
    { icon: Van,          text: 'HV-024 moved to batch BCH-012',                   date: 'May 3',    tone: 'primary' },
];

const insights = [
    { text: 'Rwenzori harvests show higher specialty scores this season — average 88.5 SCA.',  tone: 'success' },
    { text: 'Moisture levels are within optimal export range at 11.8% across all farms.',      tone: 'primary' },
    { text: 'This season\'s harvest volume is 24% above last year — strong production cycle.', tone: 'warning' },
];

const alerts = reactive({ newHarvest: true, qualityDrop: false, batchReady: true, peakAlert: true, exportUpdate: false });

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Bean Origin Harvest Advisor. I can help you track quality, find batch-ready harvests, and analyze seasonal performance. What would you like to know?" },
]);
const prompts = ['Which harvests are highest quality?', 'Which are ready for batching?', 'What season is performing best?', 'Which farms produce best harvests?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'Sipi Plot A consistently produces the highest quality harvests with an average SCA score of 88.5. Currently 14 harvests are batch-ready and 9 have been moved to active lots.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <AppLayout title="Harvests" full-width flush :show-banner="false">
        <Head title="Harvests" />

        <div class="hv-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="hv-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="hv-kicker">Traceability Management</div>
                            <h1 class="hv-title mb-0">Harvests</h1>
                            <p class="hv-subtitle mb-0">Track coffee harvests from farms and seasons with full traceability into batches and lots.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button class="btn hv-btn-outline btn-sm" style="border-color:#c8c8c8;"><el-icon><Download /></el-icon> Export Data</button>
                            <Link :href="route('season.index')" class="btn hv-btn-primary btn-sm"><el-icon><Box /></el-icon> Add Harvest</Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. KPI Row ──────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="hv-kpi h-100">
                            <span class="hv-kpi__label">Total Harvests</span>
                            <div class="hv-kpi__value">{{ totalHarvests }}</div>
                            <div class="hv-kpi__sub">All records</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="hv-kpi h-100">
                            <span class="hv-kpi__label">Active Season</span>
                            <div class="hv-kpi__value hv-green">{{ harvests.data.length }}</div>
                            <div class="hv-kpi__sub">This page</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="hv-kpi h-100">
                            <span class="hv-kpi__label">Completed</span>
                            <div class="hv-kpi__value">18</div>
                            <div class="hv-kpi__sub">Processed</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="hv-kpi h-100">
                            <span class="hv-kpi__label">Total Quantity</span>
                            <div class="hv-kpi__value hv-green">18,400</div>
                            <div class="hv-kpi__sub">kg this season</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="hv-kpi h-100">
                            <span class="hv-kpi__label">Avg Quality</span>
                            <div class="hv-kpi__value hv-green">87.4</div>
                            <div class="hv-kpi__sub">SCA score</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="hv-kpi h-100">
                            <span class="hv-kpi__label">Ready to Batch</span>
                            <div class="hv-kpi__value">14</div>
                            <div class="hv-kpi__sub">Awaiting batch</div>
                        </div>
                    </div>
                </div>

                <!-- ── 3. Filters Bar ──────────────────────────────────── -->
                <div class="hv-filters mb-3">
                    <div class="hv-search-wrap">
                        <el-icon class="hv-search-icon"><Star /></el-icon>
                        <input v-model="searchQuery" class="hv-search-input" placeholder="Search harvest ID, farm…">
                    </div>
                    <select class="hv-select"><option>All Seasons</option></select>
                    <select class="hv-select"><option>All Farms</option></select>
                    <select class="hv-select"><option>All Statuses</option><option>Verified</option><option>Processing</option><option>Ready</option><option>Pending</option></select>
                    <select class="hv-select"><option>All Coffee Types</option><option>Arabica</option><option>Robusta</option></select>
                    <button class="btn hv-btn-ghost btn-sm" @click="searchQuery = ''">Reset</button>
                </div>

                <!-- ── 4. Main 2-column grid ───────────────────────────── -->
                <div class="row g-3">

                    <!-- Left -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 4. Harvests Table -->
                            <div class="hv-section p-0 overflow-hidden">
                                <div class="hv-section-head px-3">
                                    <el-icon class="hv-section-icon"><Tickets /></el-icon>
                                    All Harvest Records
                                    <span class="hv-section-count">{{ rangeSummary }}</span>
                                </div>

                                <!-- Empty state -->
                                <div v-if="!hasHarvests" class="hv-empty">
                                    <el-icon style="font-size:2rem; color:#d1d5db;"><Tickets /></el-icon>
                                    <strong>No harvests recorded yet</strong>
                                    <span>Harvests will appear here once added to a season.</span>
                                </div>

                                <div v-else>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0 hv-table">
                                            <thead>
                                                <tr>
                                                    <th>Harvest ID</th>
                                                    <th>Farm</th>
                                                    <th>Season</th>
                                                    <th>Harvest Date</th>
                                                    <th>Quantity</th>
                                                    <th>Pick Method</th>
                                                    <th>Price</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="harvest in harvests.data" :key="harvest.id" class="hv-table-row">
                                                    <td class="hv-item-name">H-{{ harvest.id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                                            <span class="hv-item-name">{{ harvest.farm_name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span v-if="harvest.harvest_season" class="hv-tag hv-tag--green">{{ harvest.harvest_season }}</span>
                                                        <span v-else class="hv-tag">—</span>
                                                    </td>
                                                    <td class="hv-muted">{{ formatDate(harvest.harvest_date) }}</td>
                                                    <td class="fw-semibold">{{ formatWeight(harvest.weight) }}</td>
                                                    <td>
                                                        <span v-if="harvest.pick_method" class="hv-tag">{{ harvest.pick_method }}</span>
                                                        <span v-else class="hv-tag hv-tag--muted">Pending</span>
                                                    </td>
                                                    <td class="hv-muted">{{ formatPrice(harvest.price) }}</td>
                                                    <td class="text-end">
                                                        <Link :href="harvest.show_url" class="btn btn-sm hv-btn-outline hv-act-btn">
                                                            <el-icon><Star /></el-icon> View
                                                        </Link>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="hv-pagination-bar">
                                        <span class="hv-muted" style="font-size:.75rem;">{{ rangeSummary }}</span>
                                        <div class="d-flex gap-1">
                                            <Link :href="route('harvest.index', { page: Math.max(1, harvests.meta.current_page - 1), search: filters.search || undefined })"
                                                class="hv-page-btn" :class="{ 'hv-page-btn--disabled': harvests.meta.current_page <= 1 }">← Prev</Link>
                                            <Link v-for="page in visiblePages" :key="page"
                                                :href="route('harvest.index', { page, search: filters.search || undefined })"
                                                class="hv-page-btn" :class="{ 'hv-page-btn--active': page === harvests.meta.current_page }">{{ page }}</Link>
                                            <Link :href="route('harvest.index', { page: Math.min(harvests.meta.last_page, harvests.meta.current_page + 1), search: filters.search || undefined })"
                                                class="hv-page-btn" :class="{ 'hv-page-btn--disabled': harvests.meta.current_page >= harvests.meta.last_page }">Next →</Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Harvest-to-Season Mapping -->
                            <div class="hv-section">
                                <div class="hv-section-head">
                                    <el-icon class="hv-section-icon"><Van /></el-icon>
                                    Harvest Traceability Pipeline
                                </div>
                                <div class="hv-section-body">
                                    <div class="hv-flow">
                                        <div v-for="(stage, i) in pipelineStages" :key="stage.label" class="hv-flow-stage">
                                            <div class="hv-flow-dot" :class="stage.done ? 'hv-flow-dot--done' : 'hv-flow-dot--todo'">
                                                <el-icon><component :is="stage.icon" /></el-icon>
                                            </div>
                                            <div class="hv-flow-count">{{ stage.count }}</div>
                                            <div class="hv-flow-label">{{ stage.label }}</div>
                                            <div v-if="i < pipelineStages.length - 1" class="hv-flow-line" :class="stage.done ? 'hv-flow-line--done' : ''"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Farm Source Breakdown -->
                            <div class="hv-section">
                                <div class="hv-section-head">
                                    <el-icon class="hv-section-icon"><Location /></el-icon>
                                    Farm Source Breakdown
                                </div>
                                <div class="hv-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="farm in farmSources" :key="farm.name" class="hv-farm-row">
                                            <div class="hv-farm-avatar">{{ farm.name[0] }}</div>
                                            <div class="flex-fill min-w-0">
                                                <div class="hv-item-name">{{ farm.name }}</div>
                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                    <span v-for="b in farm.badges" :key="b" class="hv-mini-badge">{{ b }}</span>
                                                </div>
                                            </div>
                                            <div class="row g-1" style="min-width:220px;">
                                                <div class="col-4"><div class="hv-info-cell"><span>Harvests</span><strong>{{ farm.harvests }}</strong></div></div>
                                                <div class="col-4"><div class="hv-info-cell"><span>Production</span><strong>{{ farm.production }}</strong></div></div>
                                                <div class="col-4"><div class="hv-info-cell"><span>Quality</span><strong class="hv-green">{{ farm.quality }}</strong></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Seasonal Harvest Overview -->
                            <div class="hv-section">
                                <div class="hv-section-head">
                                    <el-icon class="hv-section-icon"><TrendCharts /></el-icon>
                                    Seasonal Harvest Overview
                                </div>
                                <div class="hv-section-body">
                                    <div class="hv-muted mb-1" style="font-size:.75rem;">Harvest Volume by Month</div>
                                    <div class="hv-chart">
                                        <div v-for="(v, i) in prodBars" :key="i" class="hv-chart-bar" :style="{ height: `${Math.max(v, 3)}%` }">
                                            <span class="hv-chart-lbl">{{ months[i] }}</span>
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-6 col-md-3"><div class="hv-info-cell"><span>Peak Month</span><strong>July</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="hv-info-cell"><span>Total Volume</span><strong>18,400 kg</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="hv-info-cell"><span>Farms Active</span><strong>3</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="hv-info-cell"><span>YoY Growth</span><strong class="hv-green">+24%</strong></div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Activity Timeline -->
                            <div class="hv-section">
                                <div class="hv-section-head">
                                    <el-icon class="hv-section-icon"><Clock /></el-icon>
                                    Activity Timeline
                                </div>
                                <div class="hv-section-body">
                                    <div class="hv-timeline">
                                        <div v-for="(act, i) in activityLog" :key="i" class="hv-timeline-item">
                                            <div class="hv-timeline-dot" :class="`hv-timeline-dot--${act.tone}`">
                                                <el-icon><component :is="act.icon" /></el-icon>
                                            </div>
                                            <div class="hv-timeline-body">
                                                <div class="hv-item-name">{{ act.text }}</div>
                                                <div class="hv-muted" style="font-size:.6875rem; margin-top:2px;">{{ act.date }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="hv-rail">

                            <!-- 7. Quality Panel -->
                            <div class="hv-section">
                                <div class="hv-section-head">
                                    <el-icon class="hv-section-icon"><Star /></el-icon>
                                    Harvest Quality Panel
                                </div>
                                <div class="hv-section-body">
                                    <div class="d-flex flex-column gap-3">
                                        <div v-for="q in qualityMetrics" :key="q.label">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span style="font-size:.8125rem;">{{ q.label }}</span>
                                                <strong class="hv-green" style="font-size:.8125rem;">{{ q.value }}</strong>
                                            </div>
                                            <div class="hv-bar-track">
                                                <div class="hv-bar-fill" :style="{ width: `${q.pct}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Processing Readiness -->
                            <div class="hv-section">
                                <div class="hv-section-head">
                                    <el-icon class="hv-section-icon"><Check /></el-icon>
                                    Processing Readiness
                                </div>
                                <div class="hv-section-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span style="font-size:.875rem; font-weight:700;">Overall Readiness</span>
                                        <strong class="hv-green" style="font-size:1.125rem; font-weight:800;">64%</strong>
                                    </div>
                                    <div class="hv-bar-track mb-3">
                                        <div class="hv-bar-fill" style="width:64%;"></div>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="hv-ready-row hv-ready-row--done"><el-icon><Check /></el-icon> Ready for batch — 14 harvests</div>
                                        <div class="hv-ready-row hv-ready-row--done"><el-icon><Check /></el-icon> Moisture acceptable — 22 harvests</div>
                                        <div class="hv-ready-row hv-ready-row--done"><el-icon><Check /></el-icon> Quality validated — 20 harvests</div>
                                        <div class="hv-ready-row"><el-icon><Warning /></el-icon> Pending review — 6 harvests</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. AI Insights -->
                            <div>
                                <div class="hv-section-head hv-section-head--plain mb-2">
                                    <el-icon class="hv-section-icon"><Opportunity /></el-icon>
                                    AI Harvest Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="hv-insight-card" :class="`hv-insight-card--${ins.tone}`">
                                        <el-icon class="hv-insight-icon"><Opportunity /></el-icon>
                                        <p class="hv-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. Smart Alerts -->
                            <div class="hv-section">
                                <div class="hv-section-head">
                                    <el-icon class="hv-section-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="hv-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="(val, key) in alerts" :key="key" class="hv-alert-row">
                                            <span style="font-size:.8125rem;">{{ { newHarvest: 'New Harvest Added', qualityDrop: 'Quality Drop Detected', batchReady: 'Ready for Batching', peakAlert: 'Season Peak Harvest Alert', exportUpdate: 'Export Readiness Update' }[key] }}</span>
                                            <button class="hv-toggle" :class="{ 'hv-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /row -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 15. Floating Chatbot ───────────────────────────────── -->
            <div class="hv-fab-wrap">
                <Transition name="hv-chat">
                    <div v-if="chatOpen" class="hv-chatbot">
                        <div class="hv-chatbot__head">
                            <div class="hv-chatbot__identity">
                                <div class="hv-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="hv-chatbot__name">Harvest Advisor</div>
                                    <div class="hv-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="hv-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="hv-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="hv-chat-msg" :class="`hv-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="hv-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="hv-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="hv-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="hv-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.hv-page {
    --green:   #004532;
    --border:  #eef2f0;
    --border-m:#d1d5db;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-low:    #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}
.hv-green  { color: #166534; font-weight: 700; }
.hv-muted  { color: var(--on-surface-var); }
.hv-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Header ────────────────────────────────────────────────────────────────── */
.hv-header   { background: #fff; border-bottom: 1px solid var(--border); }
.hv-kicker   { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.hv-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.hv-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.hv-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.hv-btn-primary:hover { background: #065f46; color: #fff; }
.hv-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.hv-btn-outline:hover { background: var(--surface-low); }
.hv-btn-ghost   { background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.hv-act-btn { font-size: .75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }

/* ── KPI ───────────────────────────────────────────────────────────────────── */
.hv-kpi { border: 1px solid var(--border); border-radius: 8px; padding: .875rem; background: #fff; }
.hv-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.hv-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.hv-kpi__sub   { font-size: .625rem; color: var(--on-surface-var); }

/* ── Filters ───────────────────────────────────────────────────────────────── */
.hv-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.hv-search-wrap  { position: relative; display: flex; align-items: center; }
.hv-search-icon  { position: absolute; left: 8px; font-size: 12px; color: var(--on-surface-var); pointer-events: none; }
.hv-search-input { height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 0 10px 0 26px; font-size: .8125rem; outline: none; width: 180px; color: var(--on-surface); }
.hv-search-input:focus { border-color: var(--green); }
.hv-select { height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 0 10px; font-size: .8125rem; color: var(--on-surface); background: #fff; outline: none; cursor: pointer; }
.hv-select:focus { border-color: var(--green); }

/* ── Sections ──────────────────────────────────────────────────────────────── */
.hv-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; }
.hv-section-head {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px;
    background: var(--surface-low);
    border-bottom: 1px solid var(--border);
    border-radius: 7px 7px 0 0;
    font-size: .8125rem; font-weight: 700; color: var(--on-surface);
}
.hv-section-head--plain { display: flex; align-items: center; gap: 8px; font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.hv-section-icon { width: 20px; height: 20px; border-radius: 4px; background: rgba(0,69,50,.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.hv-section-count { font-size: .6875rem; font-weight: 700; background: var(--border); color: var(--on-surface-var); border-radius: 999px; padding: 2px 8px; }
.hv-section-body { padding: 1rem; }

/* Empty state */
.hv-empty { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 3rem 1rem; text-align: center; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.hv-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.hv-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.hv-table-row:last-child td { border-bottom: none; }
.hv-table-row:hover { background: var(--surface-low); }

/* Tags */
.hv-tag { display: inline-flex; border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; background: rgba(0,69,50,.07); color: var(--green); }
.hv-tag--green { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.hv-tag--muted { background: #fef3c7; color: #92400e; }

/* Pagination */
.hv-pagination-bar { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: .5rem; }
.hv-page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 28px; border-radius: 5px; border: 1px solid var(--border); background: #fff; color: var(--on-surface); font-size: .8125rem; font-weight: 600; text-decoration: none; padding: 0 8px; }
.hv-page-btn:hover { background: var(--surface-low); }
.hv-page-btn--active  { background: var(--green); border-color: var(--green); color: #fff; }
.hv-page-btn--disabled{ opacity: .4; pointer-events: none; }

/* ── Pipeline flow ─────────────────────────────────────────────────────────── */
.hv-flow { display: flex; justify-content: space-between; align-items: flex-start; position: relative; }
.hv-flow-stage { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; position: relative; z-index: 1; }
.hv-flow-dot { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid var(--border); background: #fff; color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; font-size: 13px; }
.hv-flow-dot--done { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
.hv-flow-count { font-size: .875rem; font-weight: 800; color: var(--on-surface); }
.hv-flow-label { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); text-align: center; }
.hv-flow-line  { position: absolute; top: 17px; left: 50%; width: 100%; height: 1px; background: var(--border); z-index: 0; }
.hv-flow-line--done { background: #16a34a; }

/* ── Farm rows ─────────────────────────────────────────────────────────────── */
.hv-farm-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }
.hv-farm-row:last-child { border-bottom: none; }
.hv-farm-avatar { width: 32px; height: 32px; border-radius: 7px; border: 1px solid var(--border); background: var(--surface-low); color: var(--on-surface-var); font-weight: 800; font-size: .875rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.hv-mini-badge { display: inline-flex; background: rgba(0,69,50,.07); color: var(--green); border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 1px 6px; }

/* ── Chart ─────────────────────────────────────────────────────────────────── */
.hv-chart { height: 80px; display: flex; align-items: flex-end; gap: 2px; border: 1px solid var(--border); border-radius: 6px; padding: 5px; background: var(--surface-low); }
.hv-chart-bar { flex: 1; background: var(--green); opacity: .8; border-radius: 2px 2px 0 0; position: relative; }
.hv-chart-lbl { position: absolute; bottom: -14px; left: 50%; transform: translateX(-50%); font-size: .4375rem; color: var(--on-surface-var); font-weight: 600; white-space: nowrap; }

/* ── Info cell ─────────────────────────────────────────────────────────────── */
.hv-info-cell { border: 1px solid var(--border); border-radius: 6px; padding: 5px 8px; }
.hv-info-cell span   { font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; margin-bottom: 1px; }
.hv-info-cell strong { font-size: .8125rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Bars ──────────────────────────────────────────────────────────────────── */
.hv-bar-track { height: 5px; background: var(--border); border-radius: 999px; overflow: hidden; }
.hv-bar-fill  { height: 100%; background: var(--green); border-radius: 999px; transition: width .5s ease; }

/* ── Readiness rows ────────────────────────────────────────────────────────── */
.hv-ready-row { display: flex; align-items: center; gap: 8px; font-size: .8125rem; color: var(--on-surface-var); padding: 5px 0; border-bottom: 1px solid var(--border); }
.hv-ready-row:last-child { border-bottom: none; }
.hv-ready-row--done { color: #166534; }
.hv-ready-row .el-icon { font-size: 13px; flex-shrink: 0; }

/* ── Timeline ──────────────────────────────────────────────────────────────── */
.hv-timeline { display: flex; flex-direction: column; }
.hv-timeline-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--border); }
.hv-timeline-item:last-child { border-bottom: none; }
.hv-timeline-dot { width: 26px; height: 26px; border-radius: 6px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.hv-timeline-dot--success { border-color: #86efac; background: #f0fdf4; color: #16a34a; }
.hv-timeline-dot--primary { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }
.hv-timeline-dot--warning { border-color: #fcd34d; background: #fffbeb; color: #92400e; }
.hv-timeline-body { flex: 1; min-width: 0; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.hv-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: .875rem; border-radius: 8px; border: 1px solid; }
.hv-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.hv-insight-card--primary { background: #eff6ff; border-color: #bfdbfe; }
.hv-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.hv-insight-icon { font-size: 13px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.hv-insight-text { font-size: .8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.hv-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--border); }
.hv-alert-row:last-child { border-bottom: none; }
.hv-toggle { width: 32px; height: 18px; border-radius: 999px; border: 1px solid var(--border); padding: 2px; background: #fff; cursor: pointer; transition: background .2s; flex-shrink: 0; }
.hv-toggle i { display: block; width: 12px; height: 12px; border-radius: 50%; background: var(--border); transition: transform .2s; }
.hv-toggle--on { background: var(--green); border-color: var(--green); }
.hv-toggle--on i { background: #fff; transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.hv-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.hv-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: .75rem; }
.hv-fab { width: 46px; height: 46px; border-radius: 50%; border: 1.5px solid var(--green); background: var(--green); color: #fff; font-size: 18px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.hv-fab:hover { background: #065f46; }
.hv-chatbot { width: 310px; border-radius: 10px; overflow: hidden; background: #fff; border: 1px solid var(--border); display: flex; flex-direction: column; }
.hv-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.hv-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.hv-chatbot__avatar { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 13px; }
.hv-chatbot__name { font-size: .875rem; font-weight: 700; }
.hv-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: .625rem; opacity: .8; }
.hv-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.hv-chatbot__close { border: none; background: none; color: rgba(255,255,255,.8); font-size: 20px; line-height: 1; cursor: pointer; }
.hv-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 190px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.hv-chat-msg { font-size: .8125rem; padding: 8px 10px; border-radius: 8px; line-height: 1.5; max-width: 90%; }
.hv-chat-msg--bot  { background: #fff; color: var(--on-surface); border: 1px solid var(--border); border-radius: 8px 8px 8px 2px; }
.hv-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 8px 8px 2px 8px; }
.hv-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 4px; padding: 7px 10px; border-top: 1px solid var(--border); }
.hv-prompt-chip { font-size: .6rem; padding: 3px 8px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.hv-prompt-chip:hover { background: #dcfce7; }
.hv-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--border); }
.hv-chatbot__input input { flex: 1; border: 1px solid var(--border); border-radius: 6px; padding: 6px 9px; font-size: .8125rem; outline: none; }
.hv-chatbot__input input:focus { border-color: var(--green); }
.hv-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 6px; width: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px; }
.hv-chat-enter-active, .hv-chat-leave-active { transition: opacity .2s ease, transform .2s ease; }
.hv-chat-enter-from, .hv-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .hv-rail { position: static; } }
@media (max-width: 767.98px) {
    .hv-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
    .hv-search-input { width: 140px; }
}
</style>
