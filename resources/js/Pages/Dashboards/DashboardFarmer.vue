<script setup>
import { reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Clock,
    CollectionTag, DataLine, Download,
    Location, Medal, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning,
} from '@element-plus/icons-vue';

/* ── KPIs ──────────────────────────────────────────────────────── */
const kpis = [
    { label: 'Total Farms',       value: '3',        sub: 'Active farm plots',       tone: 'primary', icon: Location      },
    { label: 'Active Season',     value: '2024/25',  sub: 'Current harvest season',  tone: 'success', icon: Star          },
    { label: 'Total Production',  value: '8,400 kg', sub: 'This season',             tone: 'info',    icon: Box           },
    { label: 'Harvests Recorded', value: '12',       sub: 'Across all farms',        tone: 'warning', icon: CollectionTag },
    { label: 'Lots in Market',    value: '4',        sub: 'Published for sale',      tone: 'success', icon: ShoppingCart  },
    { label: 'Avg Quality Score', value: '87.4',     sub: 'Cupping score average',   tone: 'primary', icon: Medal         },
];

/* ── Production Chart ──────────────────────────────────────────── */
const chartRange = ref('6M');
const chartBars = [
    { label: 'Nov', value: 55, target: 70 },
    { label: 'Dec', value: 78, target: 70 },
    { label: 'Jan', value: 92, target: 80 },
    { label: 'Feb', value: 84, target: 80 },
    { label: 'Mar', value: 100, target: 85 },
    { label: 'Apr', value: 88, target: 85 },
];

/* ── Farms ─────────────────────────────────────────────────────── */
const farms = [
    { name: 'Sipi Plot A',    location: 'Kapchorwa, Uganda', area: '2.4 ha', variety: 'Arabica (SL28)',  status: 'Active', trees: '1,800', health: 92 },
    { name: 'Elgon Block B',  location: 'Mbale, Uganda',     area: '3.1 ha', variety: 'Arabica (Ruiru)', status: 'Active', trees: '2,400', health: 78 },
    { name: 'Rwenzori Upper', location: 'Kasese, Uganda',    area: '1.8 ha', variety: 'Arabica (K7)',    status: 'Fallow', trees: '1,200', health: 55 },
];

/* ── Active Season ─────────────────────────────────────────────── */
const season = {
    name: 'Main Crop 2024/25',
    start: 'Oct 2024',
    end: 'Apr 2025',
    targetKg: 10000,
    harvestedKg: 8400,
    processingKg: 1600,
    revenue: '$32,500',
};
const seasonProgress = Math.round((season.harvestedKg / season.targetKg) * 100);

/* ── Harvest Records ───────────────────────────────────────────── */
const harvests = ref([
    { id: 'HV-001', farm: 'Sipi Plot A',   date: 'Mar 20', qty: '2,400 kg', method: 'Selective', status: 'Processed',  tone: 'success' },
    { id: 'HV-002', farm: 'Elgon Block B', date: 'Mar 15', qty: '1,800 kg', method: 'Strip',     status: 'In Process', tone: 'warning' },
    { id: 'HV-003', farm: 'Sipi Plot A',   date: 'Feb 28', qty: '2,100 kg', method: 'Selective', status: 'Processed',  tone: 'success' },
    { id: 'HV-004', farm: 'Elgon Block B', date: 'Feb 10', qty: '1,400 kg', method: 'Selective', status: 'Sold',       tone: 'primary' },
    { id: 'HV-005', farm: 'Sipi Plot A',   date: 'Jan 22', qty: '700 kg',   method: 'Strip',     status: 'Sold',       tone: 'primary' },
]);

/* ── Batch & Lot Progress ──────────────────────────────────────── */
const batches = [
    { id: 'BCH-001', name: 'Sipi Natural Lot 1',  kg: '2,400', stage: 'Market',  pct: 100, tone: 'success' },
    { id: 'BCH-002', name: 'Elgon Washed Lot 2',  kg: '1,800', stage: 'Drying',  pct: 65,  tone: 'warning' },
    { id: 'BCH-003', name: 'Sipi Honey Process',  kg: '2,100', stage: 'Milling', pct: 80,  tone: 'info'    },
    { id: 'BCH-004', name: 'Elgon Mixed Process', kg: '1,400', stage: 'Sold',    pct: 100, tone: 'primary' },
];

/* ── Quality Overview ──────────────────────────────────────────── */
const quality = [
    { attribute: 'Aroma',       score: 88, display: '8.8/10' },
    { attribute: 'Flavor',      score: 86, display: '8.6/10' },
    { attribute: 'Acidity',     score: 90, display: '9.0/10' },
    { attribute: 'Body',        score: 84, display: '8.4/10' },
    { attribute: 'Aftertaste',  score: 87, display: '8.7/10' },
    { attribute: 'Balance',     score: 85, display: '8.5/10' },
];

/* ── Market & Selling ──────────────────────────────────────────── */
const lots = ref([
    { id: 'LT-001', name: 'Sipi Natural Lot 1', qty: '1,200 kg', price: '$5.20/kg', buyer: 'Nordic Roasters',    status: 'Bid Open', tone: 'success' },
    { id: 'LT-002', name: 'Sipi Honey Process', qty: '900 kg',   price: '$4.80/kg', buyer: '—',                  status: 'Listed',   tone: 'info'    },
    { id: 'LT-003', name: 'Elgon Washed Lot 2', qty: '800 kg',   price: '$3.60/kg', buyer: 'Berlin Kaffee',      status: 'Reserved', tone: 'warning' },
    { id: 'LT-004', name: 'Elgon Mixed Process', qty: '600 kg',  price: '$4.10/kg', buyer: 'Dubai Specialty Co', status: 'Sold',     tone: 'primary' },
]);

/* ── Earnings ──────────────────────────────────────────────────── */
const earnings = [
    { period: 'Nov 2024', amount: '$4,200', qty: '800 kg',  raw: 4200 },
    { period: 'Dec 2024', amount: '$7,800', qty: '1,500 kg',raw: 7800 },
    { period: 'Jan 2025', amount: '$9,200', qty: '1,800 kg',raw: 9200 },
    { period: 'Feb 2025', amount: '$6,400', qty: '1,200 kg',raw: 6400 },
    { period: 'Mar 2025', amount: '$4,900', qty: '900 kg',  raw: 4900 },
];

/* ── Documents ─────────────────────────────────────────────────── */
const documents = [
    { name: 'Organic Certification',     expires: 'Dec 2025', status: 'Valid',    tone: 'success' },
    { name: 'Export Permit',             expires: 'Jun 2025', status: 'Expiring', tone: 'warning' },
    { name: 'Cupping Report Q1 2025',    expires: '—',        status: 'Ready',    tone: 'success' },
    { name: 'Phytosanitary Certificate', expires: 'May 2025', status: 'Expiring', tone: 'warning' },
];

/* ── Activity ──────────────────────────────────────────────────── */
const activities = [
    { icon: CollectionTag, text: 'Harvest HV-001 processed and moved to milling.',           time: 'Today, 10:12 AM',    tone: 'success' },
    { icon: ShoppingCart,  text: 'LT-003 reserved by Berlin Kaffee — $3.60/kg confirmed.',   time: 'Yesterday, 4:30 PM', tone: 'primary' },
    { icon: Star,          text: 'Cupping report uploaded for Sipi Natural Lot 1.',           time: 'May 1, 2:00 PM',    tone: 'warning' },
    { icon: Warning,       text: 'Export permit expiring in 52 days — renewal required.',     time: 'Apr 30, 9:00 AM',   tone: 'danger'  },
    { icon: Box,           text: 'BCH-002 entered drying stage — 65% complete.',              time: 'Apr 29, 3:15 PM',   tone: 'info'    },
];

/* ── AI Insights ───────────────────────────────────────────────── */
const insights = [
    { text: 'Sipi Plot A consistently scores 88+ on cupping — prioritise specialty market listing.',   tone: 'success' },
    { text: 'Export permit renewal due in 52 days. Renew early to avoid shipment delays.',             tone: 'warning' },
    { text: 'Robusta demand is rising — consider adding Elgon Washed Lot to premium buyer pipeline.', tone: 'primary' },
];

/* ── Alerts ────────────────────────────────────────────────────── */
const alerts = reactive({ harvestDue: true, lotExpiry: true, priceDrop: false, docRenewal: true });

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Bean Origin Farm Advisor. I can help you manage harvests, track lots, and find the best buyers for your coffee. What do you need help with?" },
]);
const prompts = ['How is my farm performing?', 'When should I harvest?', 'Who is buying my lots?', 'How can I improve quality?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'Your Sipi Plot A is performing excellently this season with an average quality score of 87.4. Consider listing more lots in specialty markets to maximise your earnings.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <DesignPreviewLayout title="Farmer Dashboard">
        <Head title="Farmer Dashboard" />

        <div class="fr-page">

            <!-- ── Header ────────────────────────────────────────────── -->
            <div class="fr-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <h1 class="fr-title mb-0">Farmer Dashboard</h1>
                            <p class="fr-subtitle mb-0">Manage your farms, harvests, and coffee lots</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn fr-btn-primary btn-sm"><el-icon><Box /></el-icon> New Harvest</button>
                            <button class="btn fr-btn-outline btn-sm"><el-icon><CollectionTag /></el-icon> Create Lot</button>
                            <button class="btn fr-btn-outline btn-sm"><el-icon><Download /></el-icon> Reports</button>
                            <button class="btn fr-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 pb-2">
                        <span class="fr-hbadge"><el-icon><Star /></el-icon> Specialty Grade</span>
                        <span class="fr-hbadge fr-hbadge--soft"><el-icon><Check /></el-icon> Organic Certified</span>
                        <span class="fr-hbadge fr-hbadge--outline"><el-icon><Van /></el-icon> Export Ready</span>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Insight Strip ────────────────────────────── -->
                <div class="fr-insight mb-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="fr-insight__left">
                            <el-icon><Opportunity /></el-icon>
                            <span>Main crop season 84% complete — 4 lots ready for market listing.</span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap ms-auto">
                            <span class="fr-insight-badge fr-insight-badge--green">Good Season</span>
                            <span class="fr-insight-badge fr-insight-badge--soft">Buyers Active</span>
                            <span class="fr-insight-badge fr-insight-badge--muted">Quality: AA</span>
                        </div>
                    </div>
                </div>

                <!-- ── 3. KPI Cards ────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-sm-4 col-lg-2">
                        <div class="fr-kpi h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fr-kpi__label">{{ kpi.label }}</span>
                                <span class="fr-kpi__icon" :class="`fr-kpi__icon--${kpi.tone}`">
                                    <el-icon><component :is="kpi.icon" /></el-icon>
                                </span>
                            </div>
                            <div class="fr-kpi__value" :class="`fr-kpi__value--${kpi.tone}`">{{ kpi.value }}</div>
                            <div class="fr-kpi__sub">{{ kpi.sub }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 4. Production Chart -->
                            <div class="fr-card">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div class="fr-card-title">
                                        <el-icon class="fr-card-icon"><TrendCharts /></el-icon>
                                        Production Overview
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button v-for="r in ['3M','6M','1Y']" :key="r"
                                            class="btn" :class="chartRange === r ? 'fr-btn-primary' : 'fr-btn-outline'"
                                            @click="chartRange = r">{{ r }}</button>
                                    </div>
                                </div>
                                <div class="fr-chart">
                                    <div class="fr-chart-bars">
                                        <div v-for="bar in chartBars" :key="bar.label" class="fr-chart-col">
                                            <div class="fr-chart-pair">
                                                <div class="fr-chart-bar fr-chart-bar--actual" :style="{ height: `${bar.value}%` }"></div>
                                                <div class="fr-chart-bar fr-chart-bar--target" :style="{ height: `${bar.target}%` }"></div>
                                            </div>
                                            <span class="fr-chart-label">{{ bar.label }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-2">
                                    <div class="d-flex gap-3">
                                        <span class="fr-legend"><span class="fr-legend-dot fr-legend-dot--actual"></span>Actual (kg)</span>
                                        <span class="fr-legend"><span class="fr-legend-dot fr-legend-dot--target"></span>Target (kg)</span>
                                    </div>
                                    <div class="fr-chart-note">
                                        <el-icon><Opportunity /></el-icon>
                                        March peak at 100% — strong season performance.
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Farms Overview -->
                            <div class="fr-card">
                                <div class="fr-card-title mb-3">
                                    <el-icon class="fr-card-icon"><Location /></el-icon>
                                    My Farms
                                </div>
                                <div class="row g-2">
                                    <div v-for="farm in farms" :key="farm.name" class="col-12 col-md-4">
                                        <div class="fr-farm-card h-100">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div>
                                                    <div class="fr-farm-name">{{ farm.name }}</div>
                                                    <div class="fr-farm-loc">{{ farm.location }}</div>
                                                </div>
                                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                                    :class="farm.status === 'Active'
                                                        ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                        : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                                    {{ farm.status }}
                                                </span>
                                            </div>
                                            <div class="row g-1 mb-2">
                                                <div class="col-6"><div class="fr-detail-cell"><span>Area</span><strong>{{ farm.area }}</strong></div></div>
                                                <div class="col-6"><div class="fr-detail-cell"><span>Trees</span><strong>{{ farm.trees }}</strong></div></div>
                                                <div class="col-12"><div class="fr-detail-cell"><span>Variety</span><strong>{{ farm.variety }}</strong></div></div>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span style="font-size:.75rem; color:var(--on-surface-var);">Tree Health</span>
                                                <strong style="font-size:.75rem;"
                                                    :class="farm.health >= 80 ? 'fr-up' : farm.health >= 60 ? 'fr-warn' : 'fr-down'">
                                                    {{ farm.health }}%
                                                </strong>
                                            </div>
                                            <div class="fr-bar-track">
                                                <div class="fr-bar-fill"
                                                    :style="{ width: `${farm.health}%`,
                                                              background: farm.health >= 80 ? '#004532' : farm.health >= 60 ? '#f59e0b' : '#ef4444' }">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Active Season Summary -->
                            <div class="fr-card">
                                <div class="fr-card-title mb-3">
                                    <el-icon class="fr-card-icon"><Star /></el-icon>
                                    Active Season — {{ season.name }}
                                </div>
                                <div class="row g-3 align-items-center">
                                    <div class="col-12 col-md-7">
                                        <div class="row g-2">
                                            <div class="col-6"><div class="fr-season-cell"><span>Season Start</span><strong>{{ season.start }}</strong></div></div>
                                            <div class="col-6"><div class="fr-season-cell"><span>Expected End</span><strong>{{ season.end }}</strong></div></div>
                                            <div class="col-6"><div class="fr-season-cell"><span>Target</span><strong>{{ season.targetKg.toLocaleString() }} kg</strong></div></div>
                                            <div class="col-6"><div class="fr-season-cell"><span>Harvested</span><strong class="fr-up">{{ season.harvestedKg.toLocaleString() }} kg</strong></div></div>
                                            <div class="col-6"><div class="fr-season-cell"><span>In Processing</span><strong>{{ season.processingKg.toLocaleString() }} kg</strong></div></div>
                                            <div class="col-6"><div class="fr-season-cell"><span>Revenue to Date</span><strong class="fr-up">{{ season.revenue }}</strong></div></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <div class="fr-season-ring-wrap">
                                            <div class="fr-season-ring">
                                                <svg viewBox="0 0 80 80" width="110" height="110">
                                                    <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                                    <circle cx="40" cy="40" r="34" fill="none" stroke="#004532" stroke-width="8"
                                                        stroke-dasharray="213.6"
                                                        :stroke-dashoffset="213.6 - (213.6 * seasonProgress / 100)"
                                                        stroke-linecap="round"
                                                        transform="rotate(-90 40 40)"/>
                                                </svg>
                                                <div class="fr-season-ring-inner">
                                                    <div class="fr-season-ring-pct">{{ seasonProgress }}%</div>
                                                    <div class="fr-season-ring-lbl">Season</div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-2 ms-3">
                                                <div class="fr-sleg"><span class="fr-sleg-dot" style="background:#004532;"></span>Harvested</div>
                                                <div class="fr-sleg"><span class="fr-sleg-dot" style="background:#059669;"></span>Sold</div>
                                                <div class="fr-sleg"><span class="fr-sleg-dot" style="background:#e5e7eb;"></span>Remaining</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Harvest Records -->
                            <div class="fr-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="fr-card-title">
                                        <el-icon class="fr-card-icon"><CollectionTag /></el-icon>
                                        Harvest Records
                                    </div>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ harvests.length }} records</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fr-table">
                                        <thead>
                                            <tr>
                                                <th>Harvest ID</th>
                                                <th>Farm</th>
                                                <th>Date</th>
                                                <th>Quantity</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="h in harvests" :key="h.id" class="fr-table-row">
                                                <td class="fr-item-name">{{ h.id }}</td>
                                                <td class="fr-td-muted">{{ h.farm }}</td>
                                                <td class="fr-td-muted">{{ h.date }}</td>
                                                <td class="fw-semibold">{{ h.qty }}</td>
                                                <td><span class="fr-method-badge">{{ h.method }}</span></td>
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
                                                        <button class="btn btn-sm fr-btn-primary px-2"><el-icon><View /></el-icon></button>
                                                        <button class="btn btn-sm fr-btn-outline px-2"><el-icon><Download /></el-icon></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 8. Batch & Lot Progress Flow -->
                            <div class="fr-card">
                                <div class="fr-card-title mb-3">
                                    <el-icon class="fr-card-icon"><Box /></el-icon>
                                    Batch &amp; Lot Progress
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="batch in batches" :key="batch.id">
                                        <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-1">
                                            <div>
                                                <span class="fr-item-name">{{ batch.name }}</span>
                                                <span class="fr-td-muted ms-2">({{ batch.kg }} kg)</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge rounded-pill" style="font-size:.6rem;"
                                                    :class="batch.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                          : batch.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                          : batch.tone === 'primary' ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'
                                                          : 'bg-info-subtle text-info-emphasis border border-info-subtle'">
                                                    {{ batch.stage }}
                                                </span>
                                                <span class="fr-batch-pct">{{ batch.pct }}%</span>
                                            </div>
                                        </div>
                                        <div class="fr-bar-track">
                                            <div class="fr-bar-fill"
                                                :style="{ width: `${batch.pct}%`,
                                                          background: batch.tone === 'success' ? '#004532'
                                                                    : batch.tone === 'warning' ? '#f59e0b'
                                                                    : batch.tone === 'primary' ? '#3b82f6'
                                                                    : '#06b6d4' }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Market & Selling -->
                            <div class="fr-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="fr-card-title">
                                        <el-icon class="fr-card-icon"><ShoppingCart /></el-icon>
                                        Market &amp; Selling
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ lots.length }} lots</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fr-table">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Buyer</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in lots" :key="lot.id" class="fr-table-row">
                                                <td>
                                                    <div class="fr-item-name">{{ lot.name }}</div>
                                                    <div class="fr-td-muted" style="font-size:.7rem;">{{ lot.id }}</div>
                                                </td>
                                                <td class="fr-td-muted">{{ lot.qty }}</td>
                                                <td class="fw-semibold">{{ lot.price }}</td>
                                                <td class="fr-td-muted">{{ lot.buyer }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="lot.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : lot.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : lot.tone === 'primary' ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'
                                                              : 'bg-info-subtle text-info-emphasis border border-info-subtle'">
                                                        {{ lot.status }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm fr-btn-primary px-2"><el-icon><View /></el-icon></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 11. Earnings Summary -->
                            <div class="fr-card p-0 overflow-hidden">
                                <div class="px-3 py-2 border-bottom">
                                    <div class="fr-card-title">
                                        <el-icon class="fr-card-icon"><DataLine /></el-icon>
                                        Earnings Summary
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fr-table">
                                        <thead>
                                            <tr>
                                                <th>Period</th>
                                                <th>Quantity</th>
                                                <th>Amount</th>
                                                <th>Trend</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(e, i) in earnings" :key="e.period">
                                                <td class="fr-item-name">{{ e.period }}</td>
                                                <td class="fr-td-muted">{{ e.qty }}</td>
                                                <td class="fw-semibold fr-up">{{ e.amount }}</td>
                                                <td>
                                                    <span v-if="i > 0"
                                                        :class="e.raw > earnings[i-1].raw ? 'fr-up' : 'fr-down'"
                                                        style="font-size:.875rem; font-weight:700;">
                                                        {{ e.raw > earnings[i-1].raw ? '↑' : '↓' }}
                                                    </span>
                                                    <span v-else class="fr-td-muted">—</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Bottom row: Docs + Activity + AI Insights -->
                            <div class="row g-3">

                                <!-- 12. Documents & Compliance -->
                                <div class="col-12 col-md-6">
                                    <div class="fr-card h-100">
                                        <div class="fr-card-title mb-3">
                                            <el-icon class="fr-card-icon"><Download /></el-icon>
                                            Documents &amp; Compliance
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div v-for="doc in documents" :key="doc.name" class="fr-doc-row">
                                                <div class="flex-fill min-w-0">
                                                    <div class="fr-doc-name">{{ doc.name }}</div>
                                                    <div class="fr-td-muted" style="font-size:.7rem;">Expires: {{ doc.expires }}</div>
                                                </div>
                                                <span class="badge rounded-pill flex-shrink-0" style="font-size:.65rem;"
                                                    :class="doc.tone === 'success'
                                                        ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                        : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                    {{ doc.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 13. Activity Timeline -->
                                <div class="col-12 col-md-6">
                                    <div class="fr-card h-100">
                                        <div class="fr-card-title mb-3">
                                            <el-icon class="fr-card-icon"><Clock /></el-icon>
                                            Activity Timeline
                                        </div>
                                        <div class="fr-timeline">
                                            <div v-for="(act, i) in activities" :key="i" class="fr-timeline-item">
                                                <div class="fr-timeline-dot" :class="`fr-timeline-dot--${act.tone}`">
                                                    <el-icon><component :is="act.icon" /></el-icon>
                                                </div>
                                                <div class="fr-timeline-body">
                                                    <div class="fr-timeline-text">{{ act.text }}</div>
                                                    <div class="fr-timeline-time">{{ act.time }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 14. AI Farmer Insights -->
                                <div class="col-12">
                                    <div class="fr-card-title mb-2">
                                        <el-icon class="fr-card-icon"><Opportunity /></el-icon>
                                        AI Farmer Insights
                                    </div>
                                    <div class="row g-2">
                                        <div v-for="ins in insights" :key="ins.text" class="col-12 col-md-4">
                                            <div class="fr-insight-card" :class="`fr-insight-card--${ins.tone}`">
                                                <el-icon class="fr-insight-icon"><Opportunity /></el-icon>
                                                <p class="fr-insight-text">{{ ins.text }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="fr-rail">

                            <!-- 9. Quality Overview -->
                            <div class="fr-card">
                                <div class="fr-card-title mb-3">
                                    <el-icon class="fr-card-icon"><Medal /></el-icon>
                                    Quality Overview
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="fr-quality-score">87.4</div>
                                    <div>
                                        <div style="font-size:.75rem; font-weight:700; color:var(--on-surface);">Overall Cupping Score</div>
                                        <div class="fr-td-muted" style="font-size:.7rem;">SCA Specialty Grade (85+)</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="q in quality" :key="q.attribute">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fr-quality-label">{{ q.attribute }}</span>
                                            <span class="fr-quality-val">{{ q.display }}</span>
                                        </div>
                                        <div class="fr-bar-track">
                                            <div class="fr-bar-fill" :style="{ width: `${q.score}%`, background: '#004532' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Revenue mini chart -->
                            <div class="fr-card">
                                <div class="fr-card-title mb-3">
                                    <el-icon class="fr-card-icon"><DataLine /></el-icon>
                                    Monthly Revenue
                                </div>
                                <div class="fr-mini-chart mb-3">
                                    <div v-for="e in earnings" :key="e.period"
                                        class="fr-mini-bar"
                                        :style="{ height: `${(e.raw / 9200) * 100}%` }">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div style="font-size:.625rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--on-surface-var);">Season Total</div>
                                        <div style="font-size:1.0625rem; font-weight:800; color:var(--green);">$32,500</div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">+18% vs last season</span>
                                </div>
                            </div>

                            <!-- 15. Smart Alerts -->
                            <div class="fr-card">
                                <div class="fr-card-title mb-3">
                                    <el-icon class="fr-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="fr-alert-row">
                                        <span>{{ { harvestDue: 'Harvest Reminders', lotExpiry: 'Lot Expiry Alerts', priceDrop: 'Price Drops', docRenewal: 'Document Renewals' }[key] }}</span>
                                        <button class="fr-toggle" :class="{ 'fr-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Pending Actions -->
                            <div class="fr-card">
                                <div class="fr-card-title mb-3">
                                    <el-icon class="fr-card-icon"><Warning /></el-icon>
                                    Pending Actions
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div class="fr-action-item fr-action-item--warning">
                                        <el-icon style="flex-shrink:0;"><Warning /></el-icon>
                                        <div class="flex-fill">
                                            <div class="fr-action-title">Export permit expiring</div>
                                            <div class="fr-action-sub">Renew before Jun 2025</div>
                                        </div>
                                        <button class="btn btn-sm fr-btn-outline ms-auto flex-shrink-0">Renew</button>
                                    </div>
                                    <div class="fr-action-item fr-action-item--info">
                                        <el-icon style="flex-shrink:0;"><Box /></el-icon>
                                        <div class="flex-fill">
                                            <div class="fr-action-title">BCH-002 drying in progress</div>
                                            <div class="fr-action-sub">65% complete — check moisture</div>
                                        </div>
                                    </div>
                                    <div class="fr-action-item fr-action-item--success">
                                        <el-icon style="flex-shrink:0;"><ShoppingCart /></el-icon>
                                        <div class="flex-fill">
                                            <div class="fr-action-title">LT-002 ready to list</div>
                                            <div class="fr-action-sub">Sipi Honey Process — 900 kg</div>
                                        </div>
                                        <button class="btn btn-sm fr-btn-primary ms-auto flex-shrink-0">List</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── Floating Chatbot ──────────────────────────────────── -->
            <div class="fr-fab-wrap">
                <Transition name="fr-chat">
                    <div v-if="chatOpen" class="fr-chatbot">
                        <div class="fr-chatbot__head">
                            <div class="fr-chatbot__identity">
                                <div class="fr-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="fr-chatbot__name">Farm Advisor</div>
                                    <div class="fr-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="fr-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="fr-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="fr-chat-msg" :class="`fr-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="fr-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="fr-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="fr-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="fr-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.fr-page {
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
.fr-header   { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.fr-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; }
.fr-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); }
.fr-hbadge   { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fr-hbadge--soft    { background: #dcfce7; color: #166534; }
.fr-hbadge--outline { background: transparent; border: 1px solid var(--surface-high); color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.fr-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fr-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.fr-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fr-btn-outline:hover { background: var(--surface-low); }
.fr-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fr-btn-ghost:hover { background: var(--surface-high); }

/* ── Insight strip ─────────────────────────────────────────────────────────── */
.fr-insight { background: linear-gradient(135deg, var(--green), var(--green-grad)); color: #fff; border-radius: 10px; padding: 12px 16px; }
.fr-insight__left { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; font-weight: 600; }
.fr-insight-badge { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fr-insight-badge--green { background: rgba(255,255,255,0.18); color: #fff; }
.fr-insight-badge--soft  { background: rgba(166,242,209,0.2);  color: #a6f2d1; }
.fr-insight-badge--muted { background: rgba(255,255,255,0.1);  color: rgba(255,255,255,0.8); }

/* ── KPI Cards ─────────────────────────────────────────────────────────────── */
.fr-kpi { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.fr-kpi__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.fr-kpi__icon  { width: 22px; height: 22px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
.fr-kpi__icon--primary { background: #e0f2fe; color: #0369a1; }
.fr-kpi__icon--success { background: #dcfce7; color: #166534; }
.fr-kpi__icon--warning { background: #fef3c7; color: #92400e; }
.fr-kpi__icon--info    { background: #dbeafe; color: #1d4ed8; }
.fr-kpi__value { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.fr-kpi__value--primary { color: #0369a1; }
.fr-kpi__value--success { color: var(--green); }
.fr-kpi__value--warning { color: #92400e; }
.fr-kpi__value--info    { color: #1d4ed8; }
.fr-kpi__sub   { font-size: 0.625rem; color: var(--on-surface-var); }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.fr-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.fr-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.fr-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Chart ─────────────────────────────────────────────────────────────────── */
.fr-chart { height: 130px; display: flex; align-items: flex-end; background: var(--surface-low); border-radius: 8px; padding: 8px; }
.fr-chart-bars { display: flex; align-items: flex-end; gap: 4px; width: 100%; height: 100%; }
.fr-chart-col  { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; height: 100%; justify-content: flex-end; }
.fr-chart-pair { display: flex; gap: 2px; align-items: flex-end; height: 85%; }
.fr-chart-bar  { width: 10px; border-radius: 3px 3px 0 0; }
.fr-chart-bar--actual { background: var(--green); }
.fr-chart-bar--target { background: #a7f3d0; }
.fr-chart-label { font-size: 0.625rem; color: var(--on-surface-var); font-weight: 600; }
.fr-legend { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; color: var(--on-surface-var); }
.fr-legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.fr-legend-dot--actual { background: var(--green); }
.fr-legend-dot--target { background: #a7f3d0; }
.fr-chart-note { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--green); font-weight: 600; }

/* ── Farms ─────────────────────────────────────────────────────────────────── */
.fr-farm-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.fr-farm-name { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.fr-farm-loc  { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }
.fr-detail-cell { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 6px; padding: 5px 8px; }
.fr-detail-cell span   { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.fr-detail-cell strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Season ────────────────────────────────────────────────────────────────── */
.fr-season-cell { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 8px; padding: 8px 10px; }
.fr-season-cell span   { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 2px; }
.fr-season-cell strong { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); display: block; }
.fr-season-ring-wrap { display: flex; align-items: center; justify-content: center; }
.fr-season-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; }
.fr-season-ring-inner { position: absolute; text-align: center; }
.fr-season-ring-pct { font-size: 1.25rem; font-weight: 800; color: var(--green); line-height: 1; }
.fr-season-ring-lbl { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.fr-sleg { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--on-surface-var); }
.fr-sleg-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

/* ── Bar / Progress ────────────────────────────────────────────────────────── */
.fr-bar-track { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.fr-bar-fill  { height: 100%; border-radius: 999px; transition: width 0.6s ease; }
.fr-batch-pct { font-size: 0.75rem; font-weight: 700; color: var(--on-surface-var); }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.fr-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.fr-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.fr-table-row { transition: background 0.1s; }
.fr-table-row:hover { background: var(--surface-low); }
.fr-item-name   { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.fr-td-muted    { color: var(--on-surface-var); font-size: 0.8125rem; }
.fr-method-badge { display: inline-flex; background: #f0fdf4; color: #166534; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.fr-up   { color: #166534; font-weight: 700; }
.fr-warn { color: #92400e; font-weight: 700; }
.fr-down { color: #b91c1c; font-weight: 700; }

/* ── Quality ───────────────────────────────────────────────────────────────── */
.fr-quality-score { width: 56px; height: 56px; border-radius: 50%; background: var(--green); color: #fff; font-size: 1.0625rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fr-quality-label { font-size: 0.8125rem; color: var(--on-surface); font-weight: 600; }
.fr-quality-val   { font-size: 0.75rem; font-weight: 700; color: var(--green); }

/* ── Documents ─────────────────────────────────────────────────────────────── */
.fr-doc-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.fr-doc-row:last-child { border-bottom: none; }
.fr-doc-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Timeline ──────────────────────────────────────────────────────────────── */
.fr-timeline { display: flex; flex-direction: column; gap: 0; }
.fr-timeline-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.fr-timeline-item:last-child { border-bottom: none; }
.fr-timeline-dot { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.fr-timeline-dot--success { background: #dcfce7; color: #166534; }
.fr-timeline-dot--primary { background: #dbeafe; color: #1d4ed8; }
.fr-timeline-dot--warning { background: #fef3c7; color: #92400e; }
.fr-timeline-dot--danger  { background: #fee2e2; color: #b91c1c; }
.fr-timeline-dot--info    { background: #e0f2fe; color: #0369a1; }
.fr-timeline-body { flex: 1; min-width: 0; }
.fr-timeline-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.4; }
.fr-timeline-time { font-size: 0.6875rem; color: var(--on-surface-var); margin-top: 2px; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.fr-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.fr-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.fr-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.fr-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.fr-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.fr-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Mini chart ────────────────────────────────────────────────────────────── */
.fr-mini-chart { display: flex; align-items: flex-end; gap: 3px; height: 60px; background: var(--surface-low); border-radius: 8px; padding: 6px; }
.fr-mini-bar   { flex: 1; background: var(--green); border-radius: 2px 2px 0 0; opacity: 0.75; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.fr-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.fr-alert-row:last-child { border-bottom: none; }
.fr-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.fr-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.fr-toggle--on { background: var(--green); }
.fr-toggle--on i { transform: translateX(14px); }

/* ── Pending Actions ───────────────────────────────────────────────────────── */
.fr-action-item { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; border: 1px solid; }
.fr-action-item--warning { background: #fffbeb; border-color: #fde68a; }
.fr-action-item--info    { background: #f0f9ff; border-color: #bae6fd; }
.fr-action-item--success { background: #f0fdf4; border-color: #bbf7d0; }
.fr-action-title { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.fr-action-sub   { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 1px; }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.fr-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.fr-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.fr-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.fr-fab:hover { background: var(--green-grad); }
.fr-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.fr-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.fr-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.fr-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.fr-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.fr-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.fr-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.fr-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.fr-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.fr-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.fr-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.fr-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.fr-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fr-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.fr-prompt-chip:hover { background: var(--surface-mid); }
.fr-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fr-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.fr-chatbot__input input:focus { border-color: var(--green); }
.fr-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.fr-chat-enter-active, .fr-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fr-chat-enter-from, .fr-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .fr-rail { position: static; } }
@media (max-width: 767.98px)  { .fr-chatbot { width: calc(100vw - 3rem); max-width: 310px; } }
</style>
