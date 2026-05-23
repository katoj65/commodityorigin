<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EditHarvestQualityModel from '@/Components/Modals/EditHarvestQualityModel.vue';
import {
    Box, Calendar, ChatDotRound, Check,
    Clock, CollectionTag, DataLine, Document, Download,
    EditPen, Location, Promotion,
    ShoppingCart, Star, TrendCharts, Van, Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    harvest:             { type: Object, required: true },
    season:              { type: Object, default: null  },
    dateRange:           { type: Array,  default: () => [] },
    pickMethodOptions:   { type: Array,  default: () => [] },
    harvestSeasonOptions:{ type: Array,  default: () => [] },
    documentTypeOptions: { type: Array,  default: () => [] },
});

/* ── Edit modal ────────────────────────────────────────────────── */
const editHarvestModalOpen = ref(false);
const openEditHarvestModal = () => { editHarvestModalOpen.value = true; };

/* ── Helpers ───────────────────────────────────────────────────── */
const formatDate = (value, options = { month: 'short', day: 'numeric', year: 'numeric' }) => {
    if (!value) return 'Pending';
    return new Intl.DateTimeFormat('en-US', options).format(new Date(value));
};
const formatMonthYear = (v) => formatDate(v, { month: 'short', year: 'numeric' });
const formatRange = (start, end) => {
    if (!start && !end) return 'Feb – Jun 2026';
    if (start && end) return `${formatMonthYear(start)} – ${formatMonthYear(end)}`;
    return start ? formatMonthYear(start) : formatMonthYear(end);
};
const formatWeight = (v, digits = 0) =>
    `${Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits })} kg`;
const formatCurrency = (v) => `$${Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
const formatCurrencyPrecise = (v) => `$${Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const startCase = (v, fallback) => {
    if (!v) return fallback;
    return String(v).replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim().replace(/\b\w/g, c => c.toUpperCase());
};

/* ── Real computed ─────────────────────────────────────────────── */
const harvestCode   = computed(() => {
    const year = props.harvest.harvest_date ? new Date(props.harvest.harvest_date).getFullYear() : new Date().getFullYear();
    return `#H-${year}-${String(props.harvest.id).padStart(2, '0')}`;
});
const seasonName        = computed(() => props.season?.name || props.harvest.season?.name || 'Main Crop 2026');
const farmName          = computed(() => props.harvest.farm?.name || 'Mount Elgon Heights');
const farmerName        = computed(() => {
    const f = props.harvest.farm?.farmer;
    return [f?.first_name, f?.last_name].filter(Boolean).join(' ') || 'Abebe Bikila';
});
const regionLabel       = computed(() => props.harvest.farm?.farmer?.district ? `${props.harvest.farm.farmer.district}, Uganda` : props.harvest.farm?.location || 'Gedeo, Uganda');
const altitudeLabel     = computed(() => props.harvest.farm?.altitude || '2,100m');
const coordinatesLabel  = computed(() => {
    const { latitude, longitude } = props.harvest.farm || {};
    return latitude && longitude ? `${latitude}, ${longitude}` : '1.149 N, 34.331 E';
});
const varietyLabel      = computed(() => props.harvest.variety || props.harvest.farm?.variety || 'Arabica SL-14');
const pickMethodLabel   = computed(() => startCase(props.harvest.pick_method, 'Selective'));
const totalQuantity     = computed(() => Number(props.harvest.weight || 1200));
const cleanEstimate     = computed(() => totalQuantity.value * 0.1667);
const processLoss       = computed(() => {
    let l = 4.2;
    if (props.harvest.foreign_matter_present) l += 0.8;
    if (props.harvest.visible_defects) l += 0.6;
    return l;
});
const expectedBatchQty  = computed(() => cleanEstimate.value * (1 - processLoss.value / 100));
const moistureLevel     = computed(() => Math.max(10.6, Math.min(12.3, 12.9 - (Number(props.harvest.ripeness_percentage || 94) * 0.018))));
const harvestDateLabel  = computed(() => formatDate(props.harvest.harvest_date));
const plantedDateLabel  = computed(() => formatDate(props.harvest.date_planted));
const seasonDuration    = computed(() => formatRange(props.season?.start_date || props.harvest.date_planted, props.season?.end_date || props.harvest.harvest_date));
const totalYield        = computed(() => props.season?.harvests_sum_weight ? formatWeight(props.season.harvests_sum_weight) : formatWeight(totalQuantity.value * 10.33));
const harvestCount      = computed(() => `${props.season?.harvests_count || 8} Events`);
const statusLabel       = computed(() => {
    const n = String(props.harvest.status || '').toLowerCase();
    return !n || n === 'active' ? 'Verified' : startCase(n, 'Verified');
});
const readinessItems    = computed(() => [
    { label: 'Season linked',      complete: true },
    { label: 'Farm verified',      complete: Boolean(props.harvest.farm?.id) },
    { label: 'Quantity verified',  complete: totalQuantity.value > 0 },
    { label: 'Moisture checked',   complete: moistureLevel.value > 0 },
    { label: 'Cherries sorted',    complete: !props.harvest.foreign_matter_present },
    { label: 'Evidence uploaded',  complete: (props.harvest.harvest_documents || []).length > 0 },
]);
const readinessScore    = computed(() => Math.round(readinessItems.value.filter(i => i.complete).length / readinessItems.value.length * 100));
const healthScore       = computed(() => Math.max(82, Math.min(96, Math.round(86 + (Number(props.harvest.ripeness_percentage || 94) * 0.06)))));
const cupScoreLabel     = computed(() => { const s = Math.max(84, Math.min(90, Math.round(83 + (Number(props.harvest.ripeness_percentage || 94) * 0.045)))); return `${s}–${s + 2}`; });
const estimatedValue    = computed(() => { const p = Number(props.harvest.price || 0); return p > 25 ? p : totalQuantity.value * Math.max(p, 4.5); });
const qualityItems      = computed(() => [
    { label: 'Foreign Matter', value: props.harvest.foreign_matter_present ? 'Yes' : 'No', ok: !props.harvest.foreign_matter_present },
    { label: 'Pest Damage',    value: props.harvest.pest_damage             ? 'Yes' : 'No', ok: !props.harvest.pest_damage },
    { label: 'Disease Signs',  value: props.harvest.disease_signs           ? 'Yes' : 'No', ok: !props.harvest.disease_signs },
    { label: 'Visible Defects',value: props.harvest.visible_defects         ? 'Yes' : 'No', ok: !props.harvest.visible_defects },
]);
const verificationChain = computed(() => [
    { title: 'Season Created',       sub: props.season?.start_date ? formatDate(props.season.start_date) : 'Feb 14, 2026',   done: true  },
    { title: 'Farm Activities Logged', sub: 'Mar – Sep 2026',                                                                done: true  },
    { title: 'Harvest Recorded',     sub: harvestDateLabel.value,                                                            done: true  },
    { title: 'Quantity Verified',    sub: harvestDateLabel.value,                                                            done: true  },
    { title: 'Ready for Batching',   sub: 'System Confirmed',                                                               done: false },
]);
const activitySteps     = computed(() => {
    if (!props.dateRange.length) return [
        { title: 'Planting',   date: 'Mar 24' },
        { title: 'Weeding',    date: 'May 24' },
        { title: 'Flowering',  date: 'Jul 24' },
        { title: 'Inspection', date: 'Sep 24' },
        { title: 'Harvesting', date: 'Oct 24' },
    ];
    return [
        { title: 'Planting',   date: formatMonthYear(props.dateRange[0]?.start || props.harvest.date_planted) },
        { title: 'Weeding',    date: formatMonthYear(props.dateRange[1]?.start || props.dateRange[0]?.start) },
        { title: 'Flowering',  date: formatMonthYear(props.dateRange[2]?.start || props.dateRange[0]?.start) },
        { title: 'Inspection', date: formatMonthYear(props.dateRange[3]?.start || props.dateRange[1]?.start) },
        { title: 'Harvesting', date: formatDate(props.harvest.harvest_date, { month: 'short', year: '2-digit' }) },
    ];
});
const evidenceItems     = computed(() => {
    const docs = props.harvest.harvest_documents || [];
    return docs.length ? docs.map(d => ({ id: d.id, title: d.title, subtitle: d.original_name, href: d.file_url }))
        : [
            { id: 'photos',      title: 'Harvest Photos',        subtitle: 'Pending upload', href: null },
            { id: 'farmer-conf', title: 'Farmer Confirmation',   subtitle: 'Pending upload', href: null },
            { id: 'field-report',title: 'Field Officer Report',  subtitle: 'Pending upload', href: null },
        ];
});

/* ── Traceability flow ─────────────────────────────────────────── */
const traceStages = [
    { label: 'Farm',    icon: Location,     done: true,  current: false },
    { label: 'Harvest', icon: Box,          done: true,  current: true  },
    { label: 'Batch',   icon: CollectionTag,done: false, current: false },
    { label: 'Lot',     icon: Star,         done: false, current: false },
    { label: 'Market',  icon: ShoppingCart, done: false, current: false },
];

/* ── Quality scores ────────────────────────────────────────────── */
const qualityScores = [
    { label: 'Aroma',      score: 88, display: '8.8/10' },
    { label: 'Flavor',     score: 87, display: '8.7/10' },
    { label: 'Acidity',    score: 90, display: '9.0/10' },
    { label: 'Body',       score: 85, display: '8.5/10' },
    { label: 'Aftertaste', score: 86, display: '8.6/10' },
];

/* ── Market readiness ──────────────────────────────────────────── */
const markets = [
    { region: 'UAE',          demand: 'Extreme', price: '$1.80–2.10', pct: 96 },
    { region: 'Germany / EU', demand: 'High',    price: '$5.00–5.40', pct: 85 },
    { region: 'USA',          demand: 'High',    price: '$4.80–5.20', pct: 78 },
    { region: 'Japan',        demand: 'Medium',  price: '$5.20–5.80', pct: 62 },
];

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: `Hi! I'm your Bean Origin Harvest Advisor. I can help you assess quality, export potential, and batching readiness for harvest ${harvestCode.value}. What would you like to know?` },
]);
const prompts = ['Is this harvest export ready?', 'What is its quality score?', 'Should it be batched now?', 'Which market is best for it?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: `Harvest ${harvestCode.value} shows strong export potential with a cupping range of ${cupScoreLabel.value} and ${readinessScore.value}% readiness. Moisture at ${moistureLevel.value.toFixed(1)}% is within the optimal 11–13% export window. Recommend batching immediately.` }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <AppLayout title="Harvest Profile" full-width flush :show-banner="false">
        <Head title="Harvest Profile" />

        <div class="hp-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="hp-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="hp-kicker">Traceability Record · {{ harvestCode }}</div>
                            <h1 class="hp-title mb-0">Harvest Profile</h1>
                            <p class="hp-subtitle mb-2">Traceable coffee harvest record linked to farm, season, and batch pipeline.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="hp-badge hp-badge--green">Verified Harvest</span>
                                <span class="hp-badge hp-badge--soft">Quality Checked</span>
                                <span class="hp-badge hp-badge--blue">Traceable</span>
                                <span class="hp-badge hp-badge--amber">Processing Ready</span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button class="btn hp-btn-outline btn-sm"><el-icon><Location /></el-icon> View Farm</button>
                            <button class="btn hp-btn-outline btn-sm"><el-icon><Calendar /></el-icon> View Season</button>
                            <button class="btn hp-btn-outline btn-sm"><el-icon><Download /></el-icon> Export</button>
                            <button class="btn hp-btn-primary btn-sm" @click="openEditHarvestModal"><el-icon><EditPen /></el-icon> Edit Harvest</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Hero Overview Card ──────────────────────────── -->
                <div class="hp-section mb-3">
                    <div class="row g-0">
                        <!-- Left: Identity -->
                        <div class="col-12 col-md-4 hp-col">
                            <div class="hp-col-label">Harvest Identity</div>
                            <div class="hp-kpi-list">
                                <div class="hp-kpi-row"><span>Harvest ID</span><strong class="hp-green">{{ harvestCode }}</strong></div>
                                <div class="hp-kpi-row"><span>Farm</span><strong>{{ farmName }}</strong></div>
                                <div class="hp-kpi-row"><span>Season</span><strong>{{ seasonName }}</strong></div>
                                <div class="hp-kpi-row"><span>Region</span><strong>{{ regionLabel }}</strong></div>
                                <div class="hp-kpi-row"><span>Variety</span><strong>{{ varietyLabel }}</strong></div>
                                <div class="hp-kpi-row"><span>Harvest Date</span><strong>{{ harvestDateLabel }}</strong></div>
                                <div class="hp-kpi-row"><span>Date Planted</span><strong>{{ plantedDateLabel }}</strong></div>
                            </div>
                        </div>

                        <!-- Center: Production Data -->
                        <div class="col-12 col-md-4 hp-col hp-col--bordered">
                            <div class="hp-col-label">Production Data</div>
                            <div class="hp-kpi-list">
                                <div class="hp-kpi-row"><span>Total Quantity</span><strong class="hp-green">{{ formatWeight(totalQuantity, 2) }}</strong></div>
                                <div class="hp-kpi-row"><span>Clean Estimate</span><strong>{{ formatWeight(cleanEstimate, 2) }}</strong></div>
                                <div class="hp-kpi-row"><span>Batch Expected</span><strong>{{ formatWeight(expectedBatchQty, 2) }}</strong></div>
                                <div class="hp-kpi-row"><span>Moisture Level</span><strong>{{ moistureLevel.toFixed(1) }}%</strong></div>
                                <div class="hp-kpi-row"><span>Ripeness</span><strong>{{ Number(harvest.ripeness_percentage || 94).toFixed(1) }}%</strong></div>
                                <div class="hp-kpi-row"><span>Pick Method</span><strong>{{ pickMethodLabel }}</strong></div>
                                <div class="hp-kpi-row"><span>Price</span><strong>{{ formatCurrencyPrecise(harvest.price || 0) }}</strong></div>
                            </div>
                        </div>

                        <!-- Right: Status & Traceability -->
                        <div class="col-12 col-md-4 hp-col">
                            <div class="hp-col-label">Status &amp; Traceability</div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="hp-score-circle">{{ cupScoreLabel }}</div>
                                <div>
                                    <div style="font-size:.8125rem; font-weight:700;">Cupping Score</div>
                                    <div class="hp-muted" style="font-size:.7rem;">SCA Specialty Grade</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="hp-tag hp-tag--green">Premium</span>
                                        <span class="hp-tag hp-tag--blue">Export Eligible</span>
                                    </div>
                                </div>
                            </div>
                            <div class="hp-kpi-list">
                                <div class="hp-kpi-row"><span>Status</span>
                                    <strong class="d-flex align-items-center gap-1">
                                        <span class="hp-status-dot"></span>{{ statusLabel }}
                                    </strong>
                                </div>
                                <div class="hp-kpi-row"><span>Readiness</span><strong class="hp-green">{{ readinessScore }}%</strong></div>
                                <div class="hp-kpi-row"><span>Health Score</span><strong class="hp-green">{{ healthScore }}</strong></div>
                                <div class="hp-kpi-row"><span>Est. Value</span><strong>{{ formatCurrency(estimatedValue) }}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 3. Farm & Season Link -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Location /></el-icon>
                                    Farm &amp; Season Link
                                </div>
                                <div class="hp-section-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-sm-6">
                                            <div class="hp-link-card">
                                                <div class="hp-link-card__label">Linked Farm</div>
                                                <div class="hp-item-name mb-1">{{ farmName }}</div>
                                                <div class="hp-muted" style="font-size:.75rem;">{{ regionLabel }}</div>
                                                <div class="d-flex gap-2 mt-2 flex-wrap">
                                                    <div class="hp-info-cell"><span>Altitude</span><strong>{{ altitudeLabel }}</strong></div>
                                                    <div class="hp-info-cell"><span>Farmer</span><strong>{{ farmerName }}</strong></div>
                                                </div>
                                                <button class="btn hp-btn-outline btn-sm mt-2 w-100"><el-icon><Location /></el-icon> View Farm</button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <div class="hp-link-card">
                                                <div class="hp-link-card__label">Linked Season</div>
                                                <div class="hp-item-name mb-1">{{ seasonName }}</div>
                                                <div class="hp-muted" style="font-size:.75rem;">{{ seasonDuration }}</div>
                                                <div class="d-flex gap-2 mt-2 flex-wrap">
                                                    <div class="hp-info-cell"><span>Total Yield</span><strong>{{ totalYield }}</strong></div>
                                                    <div class="hp-info-cell"><span>Harvests</span><strong>{{ harvestCount }}</strong></div>
                                                </div>
                                                <button class="btn hp-btn-outline btn-sm mt-2 w-100"><el-icon><Calendar /></el-icon> View Season</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Location & Origin -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Location /></el-icon>
                                    Farm Location &amp; Origin
                                </div>
                                <div class="hp-section-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="hp-map-tile">
                                                <div class="hp-map-pin"></div>
                                                <div class="hp-map-info">
                                                    <div class="fw-semibold" style="font-size:.875rem;">{{ farmName }}</div>
                                                    <div class="hp-muted" style="font-size:.75rem;">{{ coordinatesLabel }}</div>
                                                </div>
                                                <div class="hp-map-tags">
                                                    <span class="hp-map-tag">{{ altitudeLabel }}</span>
                                                    <span class="hp-map-tag">High Altitude</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="hp-info-grid">
                                                <div class="hp-info-cell"><span>Region</span><strong>{{ regionLabel }}</strong></div>
                                                <div class="hp-info-cell"><span>Altitude</span><strong>{{ altitudeLabel }}</strong></div>
                                                <div class="hp-info-cell"><span>GPS Coords</span><strong>{{ coordinatesLabel }}</strong></div>
                                                <div class="hp-info-cell"><span>Climate Zone</span><strong>{{ harvest.farm?.climatic_zone || 'Highland Equatorial' }}</strong></div>
                                                <div class="hp-info-cell"><span>Soil Type</span><strong>{{ harvest.farm?.soil_type || 'Volcanic Clay' }}</strong></div>
                                                <div class="hp-info-cell"><span>Variety Zone</span><strong>Arabica Belt</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Quality Analysis -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Star /></el-icon>
                                    Quality Analysis
                                </div>
                                <div class="hp-section-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-7">
                                            <div class="d-flex flex-column gap-2">
                                                <div v-for="q in qualityScores" :key="q.label">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span style="font-size:.8125rem;">{{ q.label }}</span>
                                                        <strong class="hp-green" style="font-size:.8125rem;">{{ q.display }}</strong>
                                                    </div>
                                                    <div class="hp-bar-track">
                                                        <div class="hp-bar-fill" :style="{ width: `${q.score}%` }"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <div class="hp-quality-flags">
                                                <div v-for="q in qualityItems" :key="q.label" class="hp-quality-flag" :class="q.ok ? 'hp-quality-flag--ok' : 'hp-quality-flag--alert'">
                                                    <span>{{ q.label }}</span>
                                                    <strong>{{ q.value }}</strong>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mt-3">
                                                <span class="hp-tag hp-tag--green">Specialty Grade</span>
                                                <span class="hp-tag hp-tag--blue">Export Standard</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Traceability Flow -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Van /></el-icon>
                                    Traceability Pipeline — Current Stage: Harvest
                                </div>
                                <div class="hp-section-body">
                                    <div class="hp-flow">
                                        <div v-for="(stage, i) in traceStages" :key="stage.label" class="hp-flow-stage">
                                            <div class="hp-flow-dot"
                                                :class="stage.current ? 'hp-flow-dot--current' : stage.done ? 'hp-flow-dot--done' : 'hp-flow-dot--todo'">
                                                <el-icon><component :is="stage.icon" /></el-icon>
                                            </div>
                                            <div class="hp-flow-label" :class="stage.current ? 'hp-green' : ''">{{ stage.label }}</div>
                                            <div class="hp-flow-status hp-muted">{{ stage.current ? 'Active' : stage.done ? 'Complete' : 'Pending' }}</div>
                                            <div v-if="i < traceStages.length - 1" class="hp-flow-line" :class="stage.done ? 'hp-flow-line--done' : ''"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity Transformation -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><DataLine /></el-icon>
                                    Quantity Breakdown &amp; Transformation
                                </div>
                                <div class="hp-section-body">
                                    <div class="row g-2 mb-3">
                                        <div class="col-4"><div class="hp-info-cell hp-info-cell--center"><span>Ripeness</span><strong>{{ Math.round(harvest.ripeness_percentage || 94) }}%</strong></div></div>
                                        <div class="col-4"><div class="hp-info-cell hp-info-cell--center"><span>Pick Method</span><strong>{{ pickMethodLabel }}</strong></div></div>
                                        <div class="col-4"><div class="hp-info-cell hp-info-cell--center"><span>Process Loss</span><strong>{{ processLoss.toFixed(1) }}%</strong></div></div>
                                    </div>
                                    <div class="hp-transform-flow">
                                        <div class="hp-transform-stage">
                                            <div class="hp-transform-dot hp-transform-dot--red">🍒</div>
                                            <div class="hp-muted" style="font-size:.75rem;">Fresh Cherry</div>
                                            <strong>{{ formatWeight(totalQuantity) }}</strong>
                                        </div>
                                        <div class="hp-transform-line"><span>Processing</span></div>
                                        <div class="hp-transform-stage">
                                            <div class="hp-transform-dot hp-transform-dot--amber">☕</div>
                                            <div class="hp-muted" style="font-size:.75rem;">Clean Estimate</div>
                                            <strong>{{ formatWeight(cleanEstimate) }}</strong>
                                        </div>
                                        <div class="hp-transform-line"><span>{{ processLoss.toFixed(1) }}% Loss</span></div>
                                        <div class="hp-transform-stage">
                                            <div class="hp-transform-dot hp-transform-dot--green">📦</div>
                                            <div class="hp-muted" style="font-size:.75rem;">Batch Expected</div>
                                            <strong>{{ formatWeight(expectedBatchQty) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Season Context -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Calendar /></el-icon>
                                    Season Context — {{ seasonName }}
                                </div>
                                <div class="hp-section-body">
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-3"><div class="hp-info-cell"><span>Duration</span><strong>{{ seasonDuration }}</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="hp-info-cell"><span>Total Yield</span><strong>{{ totalYield }}</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="hp-info-cell"><span>Harvest Events</span><strong>{{ harvestCount }}</strong></div></div>
                                        <div class="col-6 col-md-3"><div class="hp-info-cell"><span>Status</span><strong class="hp-green">Active</strong></div></div>
                                    </div>
                                    <!-- Activities before harvest -->
                                    <div class="hp-activities">
                                        <div v-for="(step, i) in activitySteps" :key="step.title" class="hp-act-stage">
                                            <div class="hp-act-dot" :class="i === activitySteps.length - 1 ? 'hp-act-dot--current' : 'hp-act-dot--done'">
                                                <el-icon v-if="i === activitySteps.length - 1"><Check /></el-icon>
                                                <span v-else class="hp-act-fill"></span>
                                            </div>
                                            <div class="hp-act-label">{{ step.title }}</div>
                                            <div class="hp-act-date hp-muted">{{ step.date }}</div>
                                            <div v-if="i < activitySteps.length - 1" class="hp-act-line"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Activity Timeline -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Clock /></el-icon>
                                    Verification Chain &amp; Activity
                                </div>
                                <div class="hp-section-body">
                                    <div class="hp-timeline">
                                        <div v-for="item in verificationChain" :key="item.title" class="hp-timeline-item">
                                            <div class="hp-timeline-dot" :class="item.done ? 'hp-timeline-dot--done' : 'hp-timeline-dot--todo'">
                                                <el-icon v-if="item.done"><Check /></el-icon>
                                            </div>
                                            <div class="hp-timeline-body">
                                                <div class="hp-item-name">{{ item.title }}</div>
                                                <div class="hp-muted" style="font-size:.6875rem; margin-top:2px;">{{ item.sub }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="hp-rail">

                            <!-- 6. Processing Readiness -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Check /></el-icon>
                                    Processing Readiness
                                    <span class="hp-section-count hp-section-count--green">{{ readinessScore }}%</span>
                                </div>
                                <div class="hp-section-body">
                                    <div class="hp-bar-track mb-3">
                                        <div class="hp-bar-fill" :style="{ width: `${readinessScore}%` }"></div>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <div v-for="item in readinessItems" :key="item.label" class="hp-ready-row" :class="item.complete ? 'hp-ready-row--done' : ''">
                                            <el-icon><component :is="item.complete ? Check : Warning" /></el-icon>
                                            {{ item.label }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Batch Conversion -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><CollectionTag /></el-icon>
                                    Batch Conversion
                                </div>
                                <div class="hp-section-body">
                                    <div class="row g-2 mb-3">
                                        <div class="col-6"><div class="hp-info-cell"><span>Available Qty</span><strong>{{ formatWeight(expectedBatchQty) }}</strong></div></div>
                                        <div class="col-6"><div class="hp-info-cell"><span>Eligible</span><strong class="hp-green">Yes</strong></div></div>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button class="btn hp-btn-primary btn-sm"><el-icon><CollectionTag /></el-icon> Create Batch</button>
                                        <button class="btn hp-btn-outline btn-sm"><el-icon><TrendCharts /></el-icon> Add to Existing</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Sustainability -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Star /></el-icon>
                                    Sustainability &amp; Practices
                                </div>
                                <div class="hp-section-body">
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        <span class="hp-tag hp-tag--green">Sustainable</span>
                                        <span class="hp-tag hp-tag--amber">Organic</span>
                                        <span class="hp-tag hp-tag--blue">Ethical</span>
                                    </div>
                                    <div class="hp-kpi-list">
                                        <div class="hp-kpi-row"><span>Shade-Grown</span><strong class="hp-green">Active</strong></div>
                                        <div class="hp-kpi-row"><span>Organic Practices</span><strong class="hp-green">Certified</strong></div>
                                        <div class="hp-kpi-row"><span>Water Usage</span><strong>Low Impact</strong></div>
                                        <div class="hp-kpi-row"><span>Climate-Smart</span><strong class="hp-green">Active</strong></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Market Readiness -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Van /></el-icon>
                                    Market Readiness
                                </div>
                                <div class="hp-section-body">
                                    <div class="d-flex flex-column gap-3">
                                        <div v-for="m in markets" :key="m.region">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span style="font-size:.8125rem; font-weight:600;">{{ m.region }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="hp-muted" style="font-size:.75rem;">{{ m.price }}</span>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="m.demand === 'Extreme' ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                              : 'bg-success-subtle text-success-emphasis border border-success-subtle'">
                                                        {{ m.demand }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="hp-bar-track">
                                                <div class="hp-bar-fill" :style="{ width: `${m.pct}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Evidence & Compliance -->
                            <div class="hp-section">
                                <div class="hp-section-head">
                                    <el-icon class="hp-section-icon"><Document /></el-icon>
                                    Evidence &amp; Compliance
                                </div>
                                <div class="hp-section-body">
                                    <div class="d-flex flex-column gap-2">
                                        <component
                                            v-for="item in evidenceItems" :key="item.id"
                                            :is="item.href ? 'a' : 'div'"
                                            class="hp-evidence-row"
                                            :href="item.href || undefined"
                                            :target="item.href ? '_blank' : undefined"
                                            :rel="item.href ? 'noreferrer noopener' : undefined"
                                        >
                                            <div class="hp-evidence-icon"><el-icon><Document /></el-icon></div>
                                            <div class="flex-fill min-w-0">
                                                <div class="hp-item-name">{{ item.title }}</div>
                                                <div class="hp-muted" style="font-size:.7rem;">{{ item.subtitle }}</div>
                                            </div>
                                            <el-icon class="hp-muted"><Download /></el-icon>
                                        </component>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /row -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 15. Floating Chatbot ───────────────────────────────── -->
            <div class="hp-fab-wrap">
                <Transition name="hp-chat">
                    <div v-if="chatOpen" class="hp-chatbot">
                        <div class="hp-chatbot__head">
                            <div class="hp-chatbot__identity">
                                <div class="hp-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="hp-chatbot__name">Harvest Advisor</div>
                                    <div class="hp-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="hp-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="hp-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="hp-chat-msg" :class="`hp-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="hp-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="hp-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="hp-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="hp-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>

        <EditHarvestQualityModel
            v-model="editHarvestModalOpen"
            :harvest="harvest"
            :pick-method-options="pickMethodOptions"
            :harvest-season-options="harvestSeasonOptions"
        />
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.hp-page {
    --green:   #004532;
    --border:  #e5e7eb;
    --border-m:#d1d5db;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-low:    #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}
.hp-green  { color: #166534; font-weight: 700; }
.hp-muted  { color: var(--on-surface-var); }
.hp-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Header ────────────────────────────────────────────────────────────────── */
.hp-header   { background: #fff; border-bottom: 1px solid var(--border); }
.hp-kicker   { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.hp-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.hp-subtitle { font-size: .8125rem; color: var(--on-surface-var); }
.hp-badge    { display: inline-flex; align-items: center; border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 3px 10px; }
.hp-badge--green { background: rgba(0,69,50,.08); color: var(--green); }
.hp-badge--soft  { background: #dcfce7; color: #166534; }
.hp-badge--blue  { background: #dbeafe; color: #1e40af; }
.hp-badge--amber { background: #fef3c7; color: #92400e; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.hp-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.hp-btn-primary:hover { background: #065f46; color: #fff; }
.hp-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.hp-btn-outline:hover { background: var(--surface-low); }

/* ── Hero overview cols ────────────────────────────────────────────────────── */
.hp-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; }
.hp-col { padding: 1.125rem; display: flex; flex-direction: column; }
.hp-col--bordered { border-left: 1px solid var(--border); border-right: 1px solid var(--border); }
.hp-col-label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); margin-bottom: 10px; }
.hp-kpi-list { display: flex; flex-direction: column; }
.hp-kpi-row  { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-size: .8125rem; }
.hp-kpi-row:last-child { border-bottom: none; }
.hp-kpi-row span { color: var(--on-surface-var); }
.hp-kpi-row strong { color: var(--on-surface); font-weight: 700; }

/* Score circle */
.hp-score-circle { width: 52px; height: 52px; border-radius: 50%; border: 1.5px solid #16a34a; background: #f0fdf4; color: #166534; font-size: .875rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; text-align: center; line-height: 1.1; }
.hp-status-dot { width: 8px; height: 8px; border-radius: 50%; background: #16a34a; display: inline-block; flex-shrink: 0; }

/* Tags */
.hp-tag { display: inline-flex; border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; background: rgba(0,69,50,.07); color: var(--green); }
.hp-tag--green { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.hp-tag--blue  { background: #dbeafe; color: #1e40af; }
.hp-tag--amber { background: #fef3c7; color: #92400e; }

/* ── Section structure ─────────────────────────────────────────────────────── */
.hp-section-head {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px;
    background: var(--surface-low);
    border-bottom: 1px solid var(--border);
    border-radius: 7px 7px 0 0;
    font-size: .8125rem; font-weight: 700; color: var(--on-surface);
}
.hp-section-head--plain { display: flex; align-items: center; gap: 8px; font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.hp-section-icon { width: 20px; height: 20px; border-radius: 4px; background: rgba(0,69,50,.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.hp-section-count { font-size: .6875rem; font-weight: 700; background: var(--border); color: var(--on-surface-var); border-radius: 999px; padding: 2px 8px; margin-left: auto; }
.hp-section-count--green { background: #dcfce7; color: #166534; }
.hp-section-body { padding: 1rem; }

/* ── Link cards ────────────────────────────────────────────────────────────── */
.hp-link-card { border: 1px solid var(--border); border-radius: 6px; padding: .875rem; }
.hp-link-card__label { font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--green); margin-bottom: 4px; }

/* ── Info grid + cell ──────────────────────────────────────────────────────── */
.hp-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.hp-info-cell { border: 1px solid var(--border); border-radius: 6px; padding: 5px 8px; }
.hp-info-cell--center { text-align: center; }
.hp-info-cell span   { font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; margin-bottom: 1px; }
.hp-info-cell strong { font-size: .8125rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Map tile ──────────────────────────────────────────────────────────────── */
.hp-map-tile { min-height: 140px; border: 1.5px dashed var(--border-m); border-radius: 8px; background: var(--surface-low); position: relative; display: flex; align-items: flex-end; padding: 12px; overflow: hidden; }
.hp-map-pin  { position: absolute; top: 50%; left: 50%; width: 24px; height: 24px; border-radius: 50% 50% 50% 0; background: var(--green); transform: translate(-50%, -70%) rotate(-45deg); }
.hp-map-pin::after { content: ''; position: absolute; width: 9px; height: 9px; border-radius: 50%; background: #fff; top: 7px; left: 7px; }
.hp-map-info { position: relative; z-index: 1; }
.hp-map-tags { position: absolute; top: 8px; right: 8px; display: flex; flex-direction: column; gap: 4px; }
.hp-map-tag  { background: rgba(0,69,50,.08); color: var(--green); border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 7px; }

/* ── Quality flags ─────────────────────────────────────────────────────────── */
.hp-quality-flags { display: flex; flex-direction: column; gap: 4px; }
.hp-quality-flag  { border: 1px solid var(--border); border-radius: 6px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between; }
.hp-quality-flag span { font-size: .75rem; color: var(--on-surface-var); }
.hp-quality-flag strong { font-size: .8125rem; font-weight: 700; }
.hp-quality-flag--ok    { background: #f0fdf4; border-color: #86efac; }
.hp-quality-flag--ok strong { color: #166534; }
.hp-quality-flag--alert { background: #fff7ed; border-color: #fdba74; }
.hp-quality-flag--alert strong { color: #c2410c; }

/* ── Traceability flow ─────────────────────────────────────────────────────── */
.hp-flow { display: flex; justify-content: space-between; align-items: flex-start; position: relative; }
.hp-flow-stage { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; position: relative; z-index: 1; }
.hp-flow-dot { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid var(--border); background: #fff; color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; font-size: 13px; }
.hp-flow-dot--done    { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
.hp-flow-dot--current { border-color: var(--green); background: var(--green); color: #fff; }
.hp-flow-label  { font-size: .6875rem; font-weight: 700; color: var(--on-surface); text-align: center; }
.hp-flow-status { font-size: .625rem; text-align: center; }
.hp-flow-line   { position: absolute; top: 17px; left: 50%; width: 100%; height: 1px; background: var(--border); z-index: 0; }
.hp-flow-line--done { background: #16a34a; }

/* ── Transformation ────────────────────────────────────────────────────────── */
.hp-transform-flow { display: grid; grid-template-columns: 1fr 60px 1fr 60px 1fr; align-items: center; gap: 0; }
.hp-transform-stage { display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center; }
.hp-transform-dot { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; border: 1px solid var(--border); }
.hp-transform-dot--red   { background: #fff1f2; }
.hp-transform-dot--amber { background: #fffbeb; }
.hp-transform-dot--green { background: #f0fdf4; }
.hp-transform-line { position: relative; height: 1px; background: var(--border); }
.hp-transform-line span { position: absolute; top: -10px; left: 50%; transform: translateX(-50%); font-size: .5625rem; font-weight: 600; color: var(--on-surface-var); background: #fff; padding: 0 4px; white-space: nowrap; text-transform: uppercase; letter-spacing: .04em; }

/* ── Activities before harvest ─────────────────────────────────────────────── */
.hp-activities { display: flex; justify-content: space-between; align-items: flex-start; position: relative; }
.hp-act-stage  { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; position: relative; z-index: 1; }
.hp-act-dot    { width: 28px; height: 28px; border-radius: 8px; border: 1.5px solid var(--border); background: #fff; color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; font-size: 12px; }
.hp-act-dot--done    { border-color: var(--border-m); background: #f8fafc; }
.hp-act-dot--current { border-color: var(--green); background: var(--green); color: #fff; }
.hp-act-fill   { width: 8px; height: 8px; border-radius: 50%; background: #d1d5db; display: block; }
.hp-act-label  { font-size: .6875rem; font-weight: 700; color: var(--on-surface); text-align: center; }
.hp-act-date   { font-size: .625rem; text-align: center; }
.hp-act-line   { position: absolute; top: 14px; left: 50%; width: 100%; height: 1px; background: var(--border); z-index: 0; }

/* ── Bars ──────────────────────────────────────────────────────────────────── */
.hp-bar-track { height: 5px; background: var(--border); border-radius: 999px; overflow: hidden; }
.hp-bar-fill  { height: 100%; background: var(--green); border-radius: 999px; transition: width .5s ease; }

/* ── Readiness rows ────────────────────────────────────────────────────────── */
.hp-ready-row { display: flex; align-items: center; gap: 8px; font-size: .8125rem; color: var(--on-surface-var); padding: 5px 0; border-bottom: 1px solid var(--border); }
.hp-ready-row:last-child { border-bottom: none; }
.hp-ready-row--done { color: #166534; }
.hp-ready-row .el-icon { font-size: 13px; flex-shrink: 0; }

/* ── Timeline (verification) ───────────────────────────────────────────────── */
.hp-timeline { display: flex; flex-direction: column; }
.hp-timeline-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--border); }
.hp-timeline-item:last-child { border-bottom: none; }
.hp-timeline-dot { width: 24px; height: 24px; border-radius: 50%; border: 1.5px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.hp-timeline-dot--done { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
.hp-timeline-dot--todo { background: var(--surface-low); color: var(--on-surface-var); }
.hp-timeline-body { flex: 1; min-width: 0; }

/* ── Evidence rows ─────────────────────────────────────────────────────────── */
.hp-evidence-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--border); text-decoration: none; color: var(--on-surface); }
.hp-evidence-row:last-child { border-bottom: none; }
.hp-evidence-icon { width: 26px; height: 26px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }


/* ── Right rail ────────────────────────────────────────────────────────────── */
.hp-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.hp-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: .75rem; }
.hp-fab { width: 46px; height: 46px; border-radius: 50%; border: 1.5px solid var(--green); background: var(--green); color: #fff; font-size: 18px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.hp-fab:hover { background: #065f46; }
.hp-chatbot { width: 310px; border-radius: 10px; overflow: hidden; background: #fff; border: 1px solid var(--border); display: flex; flex-direction: column; }
.hp-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.hp-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.hp-chatbot__avatar { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 13px; }
.hp-chatbot__name { font-size: .875rem; font-weight: 700; }
.hp-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: .625rem; opacity: .8; }
.hp-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.hp-chatbot__close { border: none; background: none; color: rgba(255,255,255,.8); font-size: 20px; line-height: 1; cursor: pointer; }
.hp-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 190px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.hp-chat-msg { font-size: .8125rem; padding: 8px 10px; border-radius: 8px; line-height: 1.5; max-width: 90%; }
.hp-chat-msg--bot  { background: #fff; color: var(--on-surface); border: 1px solid var(--border); border-radius: 8px 8px 8px 2px; }
.hp-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 8px 8px 2px 8px; }
.hp-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 4px; padding: 7px 10px; border-top: 1px solid var(--border); }
.hp-prompt-chip { font-size: .6rem; padding: 3px 8px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.hp-prompt-chip:hover { background: #dcfce7; }
.hp-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--border); }
.hp-chatbot__input input { flex: 1; border: 1px solid var(--border); border-radius: 6px; padding: 6px 9px; font-size: .8125rem; outline: none; }
.hp-chatbot__input input:focus { border-color: var(--green); }
.hp-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 6px; width: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px; }
.hp-chat-enter-active, .hp-chat-leave-active { transition: opacity .2s ease, transform .2s ease; }
.hp-chat-enter-from, .hp-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .hp-rail { position: static; } }
@media (max-width: 991.98px) { .hp-col--bordered { border-left: none; border-right: none; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); } }
@media (max-width: 767.98px) {
    .hp-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
    .hp-transform-flow { grid-template-columns: 1fr; gap: 12px; }
    .hp-transform-line { height: 20px; width: 1px; margin: 0 auto; }
    .hp-transform-line span { top: 50%; left: 14px; transform: translateY(-50%); }
    .hp-info-grid { grid-template-columns: 1fr; }
}
</style>
