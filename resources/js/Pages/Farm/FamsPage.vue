<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, DataLine, Download,
    Location, Medal, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    farms: { type: Array, default: () => [] },
});

/* ── Filters ───────────────────────────────────────────────────── */
const search      = ref('');
const regionF     = ref('All');
const coffeeTypeF = ref('All');
const farmTypeF   = ref('All');
const altitudeF   = ref('All');
const certF       = ref('All');
const exportF     = ref('All');
const viewMode    = ref('table');

const regionOptions = computed(() =>
    ['All', ...new Set(props.farms.map(f => f.farmer?.district).filter(Boolean))].sort(),
);

/* ── Derived rows from real data ───────────────────────────────── */
const directoryRows = computed(() =>
    props.farms.map((farm, i) => {
        const altitudeRaw = parseInt(String(farm.altitude || '').replace(/[^\d]/g, ''), 10);
        const altitudeText = farm.altitude || '1,650m ASL';
        const sizeText = farm.size || '—';
        const score = 84 + (i % 8);
        const sustainability = 80 + ((i * 7) % 16);
        const proprietor = [farm.farmer?.first_name, farm.farmer?.last_name].filter(Boolean).join(' ')
            || farm.farmer?.cooperative || 'Estate proprietor';
        const origin = farm.location
            || [farm.farmer?.district, 'Uganda'].filter(Boolean).join(', ')
            || 'Origin pending';
        return {
            ...farm,
            thumb: [farm.name?.[0], farm.variety?.[0]].filter(Boolean).join('').slice(0, 2).toUpperCase() || 'ES',
            proprietor,
            origin,
            altitudeText,
            altitudeRaw: isFinite(altitudeRaw) ? altitudeRaw : 1650,
            sizeText,
            score,
            sustainability,
            coffeeType: farm.variety || 'Arabica',
            farmType: i % 3 === 0 ? 'Estate' : i % 3 === 1 ? 'Smallholder' : 'Cooperative Farm',
            exportReady: i % 3 === 0,
            badges: [
                ...(i % 4 !== 3 ? ['Verified'] : []),
                ...(i % 3 === 0 ? ['Export Ready'] : []),
                ...(i % 5 === 0 ? ['Specialty Potential'] : []),
                ...(i % 6 === 0 ? ['Organic'] : []),
            ],
        };
    }),
);

const filteredRows = computed(() => {
    const term = search.value.trim().toLowerCase();
    return directoryRows.value.filter(farm => {
        const haystack = [farm.name, farm.origin, farm.coffeeType, farm.proprietor, farm.farmer?.district]
            .filter(Boolean).join(' ').toLowerCase();
        const mSearch  = !term || haystack.includes(term);
        const mRegion  = regionF.value === 'All' || farm.farmer?.district === regionF.value;
        const mExport  = exportF.value === 'All' || (exportF.value === 'Yes' ? farm.exportReady : !farm.exportReady);
        const mType    = farmTypeF.value === 'All' || farm.farmType === farmTypeF.value;
        const mAlt     = altitudeF.value === 'All'
            || (altitudeF.value === 'low' && farm.altitudeRaw < 1600)
            || (altitudeF.value === 'mid' && farm.altitudeRaw >= 1600 && farm.altitudeRaw < 1900)
            || (altitudeF.value === 'high' && farm.altitudeRaw >= 1900);
        return mSearch && mRegion && mExport && mType && mAlt;
    });
});

/* ── KPI computed ──────────────────────────────────────────────── */
const totalArea = computed(() => {
    const total = props.farms.reduce((s, f) => {
        const v = parseFloat(String(f.size || '').replace(/[^0-9.]/g, ''));
        return isFinite(v) ? s + v : s;
    }, 0);
    return total ? (total >= 1000 ? `${(total / 1000).toFixed(1)}K` : total.toFixed(1)) : '14.2K';
});

const averageAltitude = computed(() => {
    const vals = props.farms
        .map(f => parseInt(String(f.altitude || '').replace(/[^\d]/g, ''), 10))
        .filter(v => isFinite(v));
    return vals.length
        ? Math.round(vals.reduce((s, v) => s + v, 0) / vals.length).toLocaleString()
        : '1,640';
});

const totalVerified = computed(() => directoryRows.value.filter(f => f.badges.includes('Verified')).length);
const totalExport   = computed(() => directoryRows.value.filter(f => f.exportReady).length);

/* ── Static mock data ──────────────────────────────────────────── */
const featured = [
    { label: 'High Altitude Specialty', name: 'Sipi Falls Estate',     region: 'Kapchorwa, Uganda', altitude: '2,100m ASL', type: 'Arabica', score: 91.4, badgeTone: 'success' },
    { label: 'Large Scale Export',      name: 'Bugisu Export Estate',  region: 'Mbale, Uganda',    altitude: '1,850m ASL', type: 'Washed Arabica', score: 87.8, badgeTone: 'primary' },
    { label: 'Premium Arabica',         name: 'Rwenzori Summit Farm',  region: 'Kasese, Uganda',   altitude: '1,980m ASL', type: 'Arabica', score: 89.6, badgeTone: 'warning' },
];

const regions = [
    { name: 'Uganda',   farms: 284, volume: '62,000 t', altitude: '1,600–2,200m', specialty: 'High',   tone: 'success'   },
    { name: 'Ethiopia', farms: 196, volume: '48,000 t', altitude: '1,800–2,400m', specialty: 'Extreme',tone: 'danger'    },
    { name: 'Kenya',    farms: 108, volume: '32,000 t', altitude: '1,500–2,100m', specialty: 'High',   tone: 'success'   },
    { name: 'Colombia', farms: 152, volume: '55,000 t', altitude: '1,200–1,900m', specialty: 'Medium', tone: 'warning'   },
    { name: 'Brazil',   farms: 420, volume: '210,000 t',altitude: '800–1,400m',  specialty: 'Stable', tone: 'secondary' },
];

const perfBars  = [52, 64, 70, 78, 74, 84, 80, 88, 82, 86, 90, 88];
const qualBars  = [68, 74, 72, 80, 78, 84, 82, 88, 86, 90, 89, 92];

const traceStages = [
    { label: 'Farm',    icon: Location,      status: 'Verified',   complete: true  },
    { label: 'Harvest', icon: Box,           status: 'Recorded',   complete: true  },
    { label: 'Batch',   icon: CollectionTag, status: 'Processing', complete: false },
    { label: 'Lot',     icon: Star,          status: 'Pending',    complete: false },
    { label: 'Market',  icon: ShoppingCart,  status: 'Ready',      complete: false },
];

const availableLots = [
    { farm: 'Sipi Falls Estate',     lots: 3, type: 'Arabica',        price: '$4.80–5.40', score: 91.4, demand: 'High',    tone: 'success' },
    { farm: 'Bugisu Export Estate',  lots: 5, type: 'Washed Arabica', price: '$4.20–4.80', score: 87.8, demand: 'High',    tone: 'success' },
    { farm: 'Rwenzori Summit Farm',  lots: 2, type: 'Arabica',        price: '$4.60–5.10', score: 89.6, demand: 'Medium',  tone: 'warning' },
    { farm: 'Kyoga Robusta Estate',  lots: 7, type: 'Robusta',        price: '$1.75–2.10', score: 83.4, demand: 'Extreme', tone: 'danger'  },
];

const certifications = [
    { label: 'Organic Certification',   done: 168, total: 284, pct: 59 },
    { label: 'Rainforest Alliance',     done: 76,  total: 284, pct: 27 },
    { label: 'Fairtrade',               done: 124, total: 284, pct: 44 },
    { label: 'Local Compliance',        done: 272, total: 284, pct: 96 },
];

const sourcingRequests = ref([
    { buyer: 'Nordic Roasters',    type: 'Estate',       region: 'Uganda',   coffee: 'Arabica', qty: '4,000 kg',  status: 'Open',      tone: 'success'   },
    { buyer: 'Berlin Kaffee',      type: 'Smallholder',  region: 'Ethiopia', coffee: 'Arabica', qty: '2,800 kg',  status: 'Matched',   tone: 'primary'   },
    { buyer: 'Dubai Specialty Co', type: 'Estate',       region: 'Uganda',   coffee: 'Robusta', qty: '12,000 kg', status: 'Fulfilled', tone: 'secondary' },
    { buyer: 'Tokyo Bean Co',      type: 'Any',          region: 'Kenya',    coffee: 'AA',      qty: '1,500 kg',  status: 'Open',      tone: 'success'   },
]);

const insights = [
    { text: 'High-altitude farms in Uganda are producing premium Arabica profiles this season.',           tone: 'success' },
    { text: 'Estate farms show stronger export consistency than smallholder clusters across all regions.', tone: 'primary' },
    { text: 'Certified farms are receiving 34% higher buyer demand globally — certification drives ROI.',  tone: 'warning' },
];

const alerts = reactive({ newFarm: true, exportStatus: false, certUpdates: true, highDemand: true });

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Bean Origin Farm Advisor. I can help you find the best farms, evaluate quality, and identify sourcing opportunities. What are you looking for?" },
]);
const prompts = ['Which farm produces the best coffee?', 'Are these farms export ready?', 'Which region has highest quality?', 'Which estates are available to buy from?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'Sipi Falls Estate leads with a cupping score of 91.4 — specialty-grade Arabica at 2,100m ASL. It\'s fully export-ready with organic certification. Bugisu Export Estate offers the highest volume at competitive prices.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };

const badgeClass = (b) => {
    if (b === 'Verified')           return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (b === 'Export Ready')       return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (b === 'Specialty Potential')return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    if (b === 'Organic')            return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    return 'bg-light text-secondary border';
};
</script>

<template>
    <AppLayout title="Farm &amp; Estate Directory" full-width flush :show-banner="false">
        <Head title="Farm &amp; Estate Directory" />

        <div class="fm-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="fm-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <h1 class="fm-title mb-0">Farm &amp; Estate Directory</h1>
                            <p class="fm-subtitle mb-0">Explore verified coffee farms and estates with traceable production data</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <Link :href="route('farmer.index')" class="btn fm-btn-primary btn-sm"><el-icon><Star /></el-icon> Add Farm / Estate</Link>
                            <button class="btn fm-btn-outline btn-sm"><el-icon><Download /></el-icon> Export Directory</button>
                            <button class="btn fm-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                    <!-- Filters -->
                    <div class="fm-filters pb-3">
                        <div class="fm-search-wrap">
                            <el-icon class="fm-search-icon"><View /></el-icon>
                            <input v-model="search" class="fm-search-input" placeholder="Search farms…">
                        </div>
                        <select v-model="regionF" class="fm-select">
                            <option v-for="r in regionOptions" :key="r" :value="r">{{ r === 'All' ? 'All Regions' : r }}</option>
                        </select>
                        <select v-model="coffeeTypeF" class="fm-select">
                            <option value="All">All Coffee Types</option>
                            <option>Arabica</option>
                            <option>Robusta</option>
                            <option>Mixed</option>
                        </select>
                        <select v-model="farmTypeF" class="fm-select">
                            <option value="All">All Farm Types</option>
                            <option>Estate</option>
                            <option>Smallholder</option>
                            <option>Cooperative Farm</option>
                        </select>
                        <select v-model="altitudeF" class="fm-select">
                            <option value="All">All Altitudes</option>
                            <option value="low">Below 1,600m</option>
                            <option value="mid">1,600–1,899m</option>
                            <option value="high">1,900m+</option>
                        </select>
                        <select v-model="certF" class="fm-select">
                            <option value="All">All Certifications</option>
                            <option>Organic</option>
                            <option>Fairtrade</option>
                            <option>Rainforest Alliance</option>
                        </select>
                        <select v-model="exportF" class="fm-select">
                            <option value="All">Export Status: All</option>
                            <option value="Yes">Export Ready</option>
                            <option value="No">Not Ready</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Insight Strip ────────────────────────────────── -->
                <div class="fm-insight mb-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="fm-insight__left">
                            <el-icon><Opportunity /></el-icon>
                            <span>High-altitude farms in Uganda and Ethiopia show strong specialty coffee potential this season.</span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap ms-auto">
                            <span class="fm-insight-badge fm-insight-badge--green">High Specialty Potential</span>
                            <span class="fm-insight-badge fm-insight-badge--soft">Verified Farms</span>
                            <span class="fm-insight-badge fm-insight-badge--muted">Export Ready</span>
                        </div>
                    </div>
                </div>

                <!-- ── 3. KPI Cards ────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="fm-kpi h-100">
                            <span class="fm-kpi__label">Total Farms</span>
                            <div class="fm-kpi__value">{{ props.farms.length }}</div>
                            <div class="fm-kpi__sub fm-up">↑ +6% this month</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="fm-kpi h-100">
                            <span class="fm-kpi__label">Verified</span>
                            <div class="fm-kpi__value fm-kpi__value--success">{{ totalVerified }}</div>
                            <div class="fm-kpi__sub">Platform verified</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="fm-kpi h-100">
                            <span class="fm-kpi__label">Export Ready</span>
                            <div class="fm-kpi__value fm-kpi__value--primary">{{ totalExport }}</div>
                            <div class="fm-kpi__sub">Ready to ship</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="fm-kpi h-100">
                            <span class="fm-kpi__label">Avg Altitude</span>
                            <div class="fm-kpi__value fm-kpi__value--warning">{{ averageAltitude }}m</div>
                            <div class="fm-kpi__sub">ASL average</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="fm-kpi h-100">
                            <span class="fm-kpi__label">Total Area</span>
                            <div class="fm-kpi__value">{{ totalArea }} ha</div>
                            <div class="fm-kpi__sub">Production capacity</div>
                        </div>
                    </div>
                </div>

                <!-- ── View toggle ─────────────────────────────────────── -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div style="font-size:.875rem; font-weight:600; color:var(--on-surface);">
                        Showing <strong>{{ filteredRows.length }}</strong> of <strong>{{ directoryRows.length }}</strong> farms
                    </div>
                    <div class="fm-view-toggle">
                        <button class="fm-view-btn" :class="{ 'fm-view-btn--active': viewMode === 'table' }" @click="viewMode = 'table'">
                            <el-icon><DataLine /></el-icon> Table
                        </button>
                        <button class="fm-view-btn" :class="{ 'fm-view-btn--active': viewMode === 'grid' }" @click="viewMode = 'grid'">
                            <el-icon><CollectionTag /></el-icon> Grid
                        </button>
                    </div>
                </div>

                <!-- ── 5. Table View ───────────────────────────────────── -->
                <div v-if="viewMode === 'table'" class="fm-card p-0 overflow-hidden mb-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead>
                                <tr>
                                    <th>Farm / Estate</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Altitude</th>
                                    <th>Coffee</th>
                                    <th>Quality</th>
                                    <th>Sustainability</th>
                                    <th>Export</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!filteredRows.length">
                                    <td colspan="10" class="text-center py-4 fm-td-muted">No farms match your filters.</td>
                                </tr>
                                <tr v-for="farm in filteredRows" :key="farm.id" class="fm-table-row">
                                    <td style="min-width:200px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="fm-farm-avatar">{{ farm.thumb }}</div>
                                            <div>
                                                <div class="fm-item-name">{{ farm.name || `Estate ${farm.id}` }}</div>
                                                <div class="fm-td-muted" style="font-size:.7rem;">{{ farm.proprietor }}</div>
                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                    <span v-for="b in farm.badges.slice(0,2)" :key="b" class="badge rounded-pill" :class="badgeClass(b)" style="font-size:.58rem; padding:2px 6px;">{{ b }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                            <span class="fm-td-muted">{{ farm.origin }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fm-type-badge" :class="farm.farmType === 'Estate' ? 'fm-type-badge--green' : farm.farmType === 'Smallholder' ? 'fm-type-badge--amber' : 'fm-type-badge--blue'">
                                            {{ farm.farmType }}
                                        </span>
                                    </td>
                                    <td class="fm-td-muted">{{ farm.sizeText }}</td>
                                    <td>
                                        <div class="fm-altitude-badge">{{ farm.altitudeText }}</div>
                                    </td>
                                    <td>
                                        <span class="fm-coffee-chip">{{ farm.coffeeType }}</span>
                                    </td>
                                    <td style="min-width:100px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="fm-score-bubble" :class="farm.score >= 88 ? 'fm-score-bubble--high' : 'fm-score-bubble--mid'">{{ farm.score }}</div>
                                            <div class="fm-bar-track" style="width:36px;">
                                                <div class="fm-bar-fill" :style="{ width: `${((farm.score - 80) / 15) * 100}%`, background: farm.score >= 88 ? '#004532' : '#f59e0b' }"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="min-width:110px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="fm-bar-track" style="width:56px;">
                                                <div class="fm-bar-fill" :style="{ width: `${farm.sustainability}%`, background: '#004532' }"></div>
                                            </div>
                                            <span class="fm-up" style="font-size:.75rem; font-weight:700;">{{ farm.sustainability }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" style="font-size:.65rem;"
                                            :class="farm.exportReady ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                            {{ farm.exportReady ? 'Ready' : 'Not Ready' }}
                                        </span>
                                    </td>
                                    <td class="text-end" style="min-width:130px;">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <Link :href="route('farm.show', farm.id)" class="btn btn-sm fm-btn-primary fm-act-btn">
                                                <el-icon><View /></el-icon> View
                                            </Link>
                                            <button class="btn btn-sm fm-btn-outline fm-act-btn"><el-icon><ShoppingCart /></el-icon> Source</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="fm-table-footer">
                        <span class="fm-td-muted" style="font-size:.75rem;">Showing {{ filteredRows.length }} of {{ directoryRows.length }} farms</span>
                        <button class="btn fm-btn-outline btn-sm ms-auto">Load More</button>
                    </div>
                </div>

                <!-- ── 4. Grid View ────────────────────────────────────── -->
                <div v-if="viewMode === 'grid'" class="mb-3">
                    <div v-if="!filteredRows.length" class="fm-empty">
                        <el-icon><Warning /></el-icon>
                        <div>No farms match your filters.</div>
                    </div>
                    <div v-else class="row g-3">
                        <div v-for="farm in filteredRows" :key="farm.id" class="col-12 col-sm-6 col-lg-4">
                            <div class="fm-farm-card h-100">
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="fm-farm-avatar">{{ farm.thumb }}</div>
                                    <div class="flex-fill min-w-0">
                                        <div class="fm-farm-name">{{ farm.name || `Estate ${farm.id}` }}</div>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                            <span class="fm-farm-location">{{ farm.origin }}</span>
                                        </div>
                                    </div>
                                    <div class="fm-score-bubble" :class="farm.score >= 88 ? 'fm-score-bubble--high' : 'fm-score-bubble--mid'">{{ farm.score }}</div>
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-4"><div class="fm-stat-cell"><span>Type</span><strong>{{ farm.farmType }}</strong></div></div>
                                    <div class="col-4"><div class="fm-stat-cell"><span>Size</span><strong>{{ farm.sizeText }}</strong></div></div>
                                    <div class="col-4"><div class="fm-stat-cell"><span>Alt.</span><strong>{{ farm.altitudeText }}</strong></div></div>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="fm-coffee-chip">{{ farm.coffeeType }}</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span v-for="b in farm.badges" :key="b" class="badge rounded-pill" :class="badgeClass(b)" style="font-size:.6rem; padding:2px 7px;">{{ b }}</span>
                                </div>
                                <div class="d-flex gap-2 mt-auto">
                                    <Link :href="route('farm.show', farm.id)" class="btn fm-btn-primary btn-sm flex-fill">
                                        <el-icon><View /></el-icon> View Profile
                                    </Link>
                                    <button class="btn fm-btn-outline btn-sm flex-fill"><el-icon><ShoppingCart /></el-icon> Source</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 6. Featured Farms ───────────────────────────────── -->
                <div class="fm-card mb-3">
                    <div class="fm-card-title mb-3">
                        <el-icon class="fm-card-icon"><Medal /></el-icon>
                        Featured Farms &amp; Estates
                    </div>
                    <div class="row g-3">
                        <div v-for="feat in featured" :key="feat.label" class="col-12 col-md-4">
                            <div class="fm-featured-card h-100">
                                <div class="fm-featured-label">{{ feat.label }}</div>
                                <div class="fm-featured-name">{{ feat.name }}</div>
                                <div class="d-flex align-items-center gap-1 mb-2">
                                    <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                    <span class="fm-td-muted" style="font-size:.75rem;">{{ feat.region }}</span>
                                </div>
                                <div class="row g-1 mb-3">
                                    <div class="col-6"><div class="fm-stat-cell"><span>Altitude</span><strong>{{ feat.altitude }}</strong></div></div>
                                    <div class="col-6"><div class="fm-stat-cell"><span>Type</span><strong>{{ feat.type }}</strong></div></div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div style="font-size:.625rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--on-surface-var);">Cupping Score</div>
                                        <div class="fm-featured-score">{{ feat.score }}</div>
                                    </div>
                                    <button class="btn fm-btn-primary btn-sm">View Farm</button>
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

                            <!-- 7. Regional Breakdown -->
                            <div class="fm-card">
                                <div class="fm-card-title mb-3">
                                    <el-icon class="fm-card-icon"><Location /></el-icon>
                                    Regional Breakdown
                                </div>
                                <div class="row g-2">
                                    <div v-for="reg in regions" :key="reg.name" class="col-6 col-md-4 col-lg">
                                        <div class="fm-region-card h-100">
                                            <div class="fm-region-name">{{ reg.name }}</div>
                                            <div class="fm-region-farms">{{ reg.farms }} farms</div>
                                            <div class="row g-1 mt-2">
                                                <div class="col-12"><div class="fm-stat-cell"><span>Volume</span><strong>{{ reg.volume }}</strong></div></div>
                                                <div class="col-12"><div class="fm-stat-cell"><span>Altitude</span><strong>{{ reg.altitude }}</strong></div></div>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                                    :class="reg.tone === 'danger'    ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                          : reg.tone === 'success'   ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                          : reg.tone === 'warning'   ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                          : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                                    {{ reg.specialty }} Potential
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Farm-to-Market Traceability -->
                            <div class="fm-card">
                                <div class="fm-card-title mb-3">
                                    <el-icon class="fm-card-icon"><Van /></el-icon>
                                    Farm-to-Market Traceability
                                </div>
                                <div class="fm-trace-flow">
                                    <div v-for="(stage, i) in traceStages" :key="stage.label" class="fm-trace-stage">
                                        <div class="fm-trace-dot" :class="stage.complete ? 'fm-trace-dot--done' : 'fm-trace-dot--pending'">
                                            <el-icon><component :is="stage.icon" /></el-icon>
                                        </div>
                                        <div class="fm-trace-label">{{ stage.label }}</div>
                                        <div class="fm-trace-status" :class="stage.complete ? 'fm-up' : 'fm-td-muted'">{{ stage.status }}</div>
                                        <div v-if="i < traceStages.length - 1" class="fm-trace-connector" :class="stage.complete ? 'fm-trace-connector--done' : ''"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Available Coffee from Farms -->
                            <div class="fm-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="fm-card-title">
                                        <el-icon class="fm-card-icon"><ShoppingCart /></el-icon>
                                        Available Coffee from Farms
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ availableLots.length }} farms</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fm-table">
                                        <thead>
                                            <tr>
                                                <th>Farm</th>
                                                <th>Lots Available</th>
                                                <th>Coffee Type</th>
                                                <th>Price Range</th>
                                                <th>Quality</th>
                                                <th>Demand</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in availableLots" :key="lot.farm" class="fm-table-row">
                                                <td class="fm-item-name">{{ lot.farm }}</td>
                                                <td>
                                                    <span class="fm-lots-count">{{ lot.lots }}</span>
                                                    <span class="fm-td-muted ms-1" style="font-size:.75rem;">lots</span>
                                                </td>
                                                <td><span class="fm-coffee-chip">{{ lot.type }}</span></td>
                                                <td class="fw-semibold">{{ lot.price }}</td>
                                                <td>
                                                    <span class="fm-score-pill" :class="lot.score >= 88 ? 'fm-score-pill--high' : 'fm-score-pill--mid'">{{ lot.score }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="lot.tone === 'danger'  ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                              : lot.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                        {{ lot.demand }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm fm-btn-outline" style="font-size:.75rem;">View Lots</button>
                                                        <button class="btn btn-sm fm-btn-primary" style="font-size:.75rem;"><el-icon><ShoppingCart /></el-icon> Buy</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 12. Sourcing Requests -->
                            <div class="fm-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="fm-card-title">
                                        <el-icon class="fm-card-icon"><CollectionTag /></el-icon>
                                        Sourcing Requests
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">{{ sourcingRequests.filter(s => s.status === 'Open').length }} open</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fm-table">
                                        <thead>
                                            <tr>
                                                <th>Buyer</th>
                                                <th>Farm Type</th>
                                                <th>Region</th>
                                                <th>Coffee</th>
                                                <th>Quantity</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="sr in sourcingRequests" :key="sr.buyer + sr.region" class="fm-table-row">
                                                <td class="fm-item-name">{{ sr.buyer }}</td>
                                                <td><span class="fm-coffee-chip">{{ sr.type }}</span></td>
                                                <td class="fm-td-muted">{{ sr.region }}</td>
                                                <td class="fm-td-muted">{{ sr.coffee }}</td>
                                                <td class="fm-td-muted">{{ sr.qty }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="sr.tone === 'success'   ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : sr.tone === 'primary'   ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'
                                                              : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                                        {{ sr.status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="fm-rail">

                            <!-- 8. Production & Quality Insights -->
                            <div class="fm-card">
                                <div class="fm-card-title mb-3">
                                    <el-icon class="fm-card-icon"><TrendCharts /></el-icon>
                                    Production &amp; Quality Insights
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><div class="fm-metric-cell"><span>Avg Cupping Score</span><strong>87.4</strong></div></div>
                                    <div class="col-6"><div class="fm-metric-cell"><span>Export Ready</span><strong class="fm-up">{{ totalExport }}</strong></div></div>
                                    <div class="col-6"><div class="fm-metric-cell"><span>Yield / ha</span><strong>2.4 t</strong></div></div>
                                    <div class="col-6"><div class="fm-metric-cell"><span>Specialty Share</span><strong class="fm-up">64%</strong></div></div>
                                </div>
                                <div class="fm-td-muted mb-1" style="font-size:.7rem;">Production Trend</div>
                                <div class="fm-mini-chart mb-3">
                                    <div v-for="(v, i) in perfBars" :key="i" class="fm-mini-bar" :style="{ height: `${v}%` }"></div>
                                </div>
                                <div class="fm-td-muted mb-1" style="font-size:.7rem;">Quality Trend</div>
                                <div class="fm-mini-chart fm-mini-chart--quality">
                                    <div v-for="(v, i) in qualBars" :key="i" class="fm-mini-bar" :style="{ height: `${v}%` }"></div>
                                </div>
                            </div>

                            <!-- 11. Certification & Compliance -->
                            <div class="fm-card">
                                <div class="fm-card-title mb-3">
                                    <el-icon class="fm-card-icon"><Checked /></el-icon>
                                    Certification &amp; Compliance
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="cert in certifications" :key="cert.label">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fm-cert-label">{{ cert.label }}</span>
                                            <span class="fw-semibold" :class="cert.pct >= 80 ? 'fm-up' : cert.pct >= 40 ? 'fm-warn' : 'fm-down'" style="font-size:.8125rem;">{{ cert.pct }}%</span>
                                        </div>
                                        <div class="fm-bar-track">
                                            <div class="fm-bar-fill"
                                                :style="{ width: `${cert.pct}%`,
                                                          background: cert.pct >= 80 ? '#004532' : cert.pct >= 40 ? '#f59e0b' : '#ef4444' }">
                                            </div>
                                        </div>
                                        <div class="fm-td-muted mt-1" style="font-size:.7rem;">{{ cert.done }} / {{ cert.total }} farms</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. AI Farm Insights -->
                            <div>
                                <div class="fm-card-title mb-2">
                                    <el-icon class="fm-card-icon"><Opportunity /></el-icon>
                                    AI Farm Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="fm-insight-card" :class="`fm-insight-card--${ins.tone}`">
                                        <el-icon class="fm-insight-icon"><Opportunity /></el-icon>
                                        <p class="fm-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. Smart Alerts -->
                            <div class="fm-card">
                                <div class="fm-card-title mb-3">
                                    <el-icon class="fm-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="fm-alert-row">
                                        <span>{{ { newFarm: 'New Farm Added', exportStatus: 'Export-Ready Status', certUpdates: 'Certification Updates', highDemand: 'High-Demand Farms' }[key] }}</span>
                                        <button class="fm-toggle" :class="{ 'fm-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 15. Floating Chatbot ────────────────────────────────── -->
            <div class="fm-fab-wrap">
                <Transition name="fm-chat">
                    <div v-if="chatOpen" class="fm-chatbot">
                        <div class="fm-chatbot__head">
                            <div class="fm-chatbot__identity">
                                <div class="fm-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="fm-chatbot__name">Farm Advisor</div>
                                    <div class="fm-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="fm-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="fm-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="fm-chat-msg" :class="`fm-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="fm-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="fm-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="fm-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="fm-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.fm-page {
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
.fm-header   { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.fm-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; }
.fm-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Filters ───────────────────────────────────────────────────────────────── */
.fm-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.fm-search-wrap  { position: relative; display: flex; align-items: center; }
.fm-search-icon  { position: absolute; left: 8px; font-size: 13px; color: var(--on-surface-var); pointer-events: none; }
.fm-search-input { height: 32px; border: 1px solid var(--surface-high); border-radius: 7px; padding: 0 10px 0 28px; font-size: 0.8125rem; outline: none; color: var(--on-surface); background: var(--surface-white); width: 160px; }
.fm-search-input:focus { border-color: var(--green); }
.fm-select { height: 32px; border: 1px solid var(--surface-high); border-radius: 7px; padding: 0 10px; font-size: 0.8125rem; color: var(--on-surface); background: var(--surface-white); outline: none; cursor: pointer; }
.fm-select:focus { border-color: var(--green); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.fm-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.fm-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.fm-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fm-btn-outline:hover { background: var(--surface-low); }
.fm-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }

/* ── Insight strip ─────────────────────────────────────────────────────────── */
.fm-insight { background: linear-gradient(135deg, var(--green), var(--green-grad)); color: #fff; border-radius: 10px; padding: 12px 16px; }
.fm-insight__left { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; font-weight: 600; }
.fm-insight-badge { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fm-insight-badge--green { background: rgba(255,255,255,0.18); color: #fff; }
.fm-insight-badge--soft  { background: rgba(166,242,209,0.2);  color: #a6f2d1; }
.fm-insight-badge--muted { background: rgba(255,255,255,0.1);  color: rgba(255,255,255,0.8); }

/* ── KPI Cards ─────────────────────────────────────────────────────────────── */
.fm-kpi { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.fm-kpi__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.fm-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.fm-kpi__value--success { color: var(--green); }
.fm-kpi__value--primary { color: #0369a1; }
.fm-kpi__value--warning { color: #92400e; }
.fm-kpi__sub   { font-size: 0.625rem; color: var(--on-surface-var); }

/* ── View toggle ───────────────────────────────────────────────────────────── */
.fm-view-toggle { display: flex; gap: 3px; background: var(--surface-low); border-radius: 8px; padding: 3px; }
.fm-view-btn { font-size: 0.75rem; font-weight: 600; padding: 4px 12px; border-radius: 6px; border: none; background: transparent; color: var(--on-surface-var); cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.12s; }
.fm-view-btn:hover { background: var(--surface-mid); }
.fm-view-btn--active { background: var(--surface-white); color: var(--green); box-shadow: 0 1px 3px rgba(0,0,0,.08); font-weight: 700; }

/* ── Farm card (grid) ──────────────────────────────────────────────────────── */
.fm-farm-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); display: flex; flex-direction: column; transition: box-shadow 0.15s; }
.fm-farm-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); border-color: rgba(0,69,50,0.2); }
.fm-farm-avatar { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(145deg, #1f2937, #0f172a); color: #d1fae5; font-weight: 800; font-size: 0.875rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fm-farm-name    { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); line-height: 1.3; }
.fm-farm-location{ font-size: 0.75rem; color: var(--on-surface-var); }

/* ── Empty state ───────────────────────────────────────────────────────────── */
.fm-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 40px 20px; color: var(--on-surface-var); font-size: 0.875rem; background: var(--surface-low); border-radius: 12px; border: 1px solid var(--surface-high); }

/* ── Stat cell ─────────────────────────────────────────────────────────────── */
.fm-stat-cell { background: var(--surface-low); border-radius: 6px; padding: 5px 8px; }
.fm-stat-cell span   { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.fm-stat-cell strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Type / coffee chips ───────────────────────────────────────────────────── */
.fm-type-badge { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.fm-type-badge--green { background: #dcfce7; color: #166534; }
.fm-type-badge--amber { background: #fef3c7; color: #92400e; }
.fm-type-badge--blue  { background: #dbeafe; color: #1e40af; }
.fm-coffee-chip { display: inline-flex; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.fm-altitude-badge { display: inline-flex; background: #f0f9ff; color: #0369a1; border-radius: 6px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.fm-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.fm-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.fm-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Featured ──────────────────────────────────────────────────────────────── */
.fm-featured-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; display: flex; flex-direction: column; }
.fm-featured-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--green); margin-bottom: 4px; }
.fm-featured-name  { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.fm-featured-score { font-size: 1.25rem; font-weight: 800; color: var(--green); }

/* ── Regional ──────────────────────────────────────────────────────────────── */
.fm-region-card  { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.fm-region-name  { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); margin-bottom: 2px; }
.fm-region-farms { font-size: 0.75rem; color: var(--on-surface-var); margin-bottom: 4px; }

/* ── Traceability ──────────────────────────────────────────────────────────── */
.fm-trace-flow { display: flex; align-items: flex-start; gap: 0; justify-content: space-between; position: relative; }
.fm-trace-stage { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; position: relative; z-index: 1; }
.fm-trace-dot { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.fm-trace-dot--done    { background: #dcfce7; color: #166534; }
.fm-trace-dot--pending { background: var(--surface-high); color: var(--on-surface-var); }
.fm-trace-label  { font-size: 0.6875rem; font-weight: 700; color: var(--on-surface); text-align: center; }
.fm-trace-status { font-size: 0.625rem; font-weight: 600; text-align: center; }
.fm-trace-connector { position: absolute; top: 18px; left: 50%; width: 100%; height: 2px; background: var(--surface-high); z-index: 0; }
.fm-trace-connector--done { background: #16a34a; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.fm-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.fm-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.fm-table-row { transition: background 0.1s; }
.fm-table-row:hover { background: var(--surface-low); }
.fm-item-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.fm-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; }
.fm-table-footer { display: flex; align-items: center; padding: 10px 16px; border-top: 1px solid var(--surface-low); background: var(--surface-low); }
.fm-act-btn   { font-size: 0.75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.fm-score-bubble { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.6875rem; font-weight: 800; flex-shrink: 0; }
.fm-score-bubble--high { background: #dcfce7; color: #166534; }
.fm-score-bubble--mid  { background: #fef3c7; color: #92400e; }
.fm-score-pill { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 800; padding: 2px 8px; }
.fm-score-pill--high { background: #dcfce7; color: #166534; }
.fm-score-pill--mid  { background: #fef3c7; color: #92400e; }
.fm-lots-count { font-size: 1rem; font-weight: 800; color: var(--green); }
.fm-up   { color: #166534; font-weight: 700; }
.fm-warn { color: #92400e; font-weight: 700; }
.fm-down { color: #b91c1c; font-weight: 700; }

/* ── Bars ──────────────────────────────────────────────────────────────────── */
.fm-bar-track { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.fm-bar-fill  { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* ── Metrics / mini charts ─────────────────────────────────────────────────── */
.fm-metric-cell { background: var(--surface-low); border-radius: 8px; padding: 8px 10px; }
.fm-metric-cell span   { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 2px; }
.fm-metric-cell strong { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); }
.fm-mini-chart { display: flex; align-items: flex-end; gap: 3px; height: 44px; background: var(--surface-low); border-radius: 8px; padding: 5px; }
.fm-mini-chart--quality .fm-mini-bar { background: #059669; }
.fm-mini-bar   { flex: 1; background: var(--green); border-radius: 2px 2px 0 0; opacity: 0.75; }

/* ── Certification ─────────────────────────────────────────────────────────── */
.fm-cert-label { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.fm-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.fm-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.fm-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.fm-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.fm-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.fm-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.fm-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.fm-alert-row:last-child { border-bottom: none; }
.fm-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.fm-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.fm-toggle--on { background: var(--green); }
.fm-toggle--on i { transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.fm-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.fm-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.fm-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.fm-fab:hover { background: var(--green-grad); }
.fm-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.fm-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.fm-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.fm-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.fm-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.fm-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.fm-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.fm-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.fm-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.fm-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.fm-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.fm-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.fm-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fm-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.fm-prompt-chip:hover { background: var(--surface-mid); }
.fm-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fm-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.fm-chatbot__input input:focus { border-color: var(--green); }
.fm-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.fm-chat-enter-active, .fm-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fm-chat-enter-from, .fm-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .fm-rail { position: static; } }
@media (max-width: 767.98px)  {
    .fm-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
    .fm-search-input { width: 130px; }
}
</style>
