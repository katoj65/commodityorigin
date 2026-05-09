<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, DataLine, Download, Location,
    Medal, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    cooperatives: { type: Array, default: () => [] },
});

/* ── Filters ───────────────────────────────────────────────────── */
const search      = ref('');
const regionF     = ref('All');
const coffeeTypeF = ref('All');
const certF       = ref('All');
const sizeF       = ref('All');
const exportF     = ref('All');
const viewMode    = ref('table');

const regionOptions = computed(() =>
    ['All', ...new Set(props.cooperatives.map(c => c.district).filter(Boolean))].sort(),
);

/* ── Derived cooperative rows ──────────────────────────────────── */
const directoryRows = computed(() =>
    props.cooperatives.map((c, i) => {
        const memberEst = c.code
            ? c.code.replace(/[^A-Za-z0-9]/g, '').length * 4000
            : 8000 + i * 2500;
        const score = 84 + (i % 7);
        return {
            ...c,
            initials: (c.name || 'Coop').split(' ').filter(Boolean).slice(0, 2).map(p => p[0]?.toUpperCase()).join('').slice(0, 2) || 'CP',
            territory: [c.district, c.sub_county].filter(Boolean).join(', ') || 'Origin pending',
            members: memberEst,
            membersLabel: memberEst.toLocaleString(),
            production: `${((memberEst / 100) * 0.8).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} kg`,
            score,
            coffeeTypes: c.district?.toLowerCase().includes('mbale') ? ['Arabica'] : ['Washed Arabica'],
            badges: [
                ...(c.status === 'active' ? ['Verified'] : []),
                ...(i % 3 === 0 ? ['Export Ready'] : []),
                ...(i % 4 === 0 ? ['Organic'] : []),
                ...(i % 5 === 0 ? ['Fairtrade'] : []),
            ],
            exportReady: i % 3 === 0,
            size: memberEst < 10000 ? 'Small' : memberEst < 30000 ? 'Medium' : 'Large',
        };
    }),
);

const filteredRows = computed(() => {
    const term = search.value.trim().toLowerCase();
    return directoryRows.value.filter(c => {
        const haystack = [c.name, c.code, c.district, c.sub_county].filter(Boolean).join(' ').toLowerCase();
        const mSearch = !term || haystack.includes(term);
        const mRegion = regionF.value === 'All' || c.district === regionF.value;
        const mExport = exportF.value === 'All' || (exportF.value === 'Yes' ? c.exportReady : !c.exportReady);
        const mSize   = sizeF.value === 'All' || c.size === sizeF.value;
        return mSearch && mRegion && mExport && mSize;
    });
});

/* ── Summary KPIs ──────────────────────────────────────────────── */
const totalVerified  = computed(() => directoryRows.value.filter(c => c.badges.includes('Verified')).length);
const totalExport    = computed(() => directoryRows.value.filter(c => c.exportReady).length);
const totalMembers   = computed(() => directoryRows.value.reduce((s, c) => s + c.members, 0).toLocaleString());

/* ── Static mock data ──────────────────────────────────────────── */
const featured = [
    { label: 'Best Quality',       name: 'Sipi Falls Cooperative',      region: 'Kapchorwa, Uganda', type: 'Arabica',        score: 91.2, badge: 'Specialty Grade', badgeTone: 'success' },
    { label: 'Highest Production', name: 'Bugisu Cooperative Union',    region: 'Mbale, Uganda',    type: 'Washed Arabica', score: 88.0, badge: 'Top Exporter',    badgeTone: 'primary' },
    { label: 'Most Export-Ready',  name: 'Rwenzori Farmers Ltd',        region: 'Kasese, Uganda',   type: 'Arabica',        score: 87.6, badge: 'Export Ready',    badgeTone: 'info'    },
];

const regions = [
    { name: 'Uganda',   coops: 142, volume: '84,000 tons', quality: '86–91', demand: 'High',   tone: 'success' },
    { name: 'Ethiopia', coops: 98,  volume: '62,000 tons', quality: '88–92', demand: 'Extreme',tone: 'danger'  },
    { name: 'Kenya',    coops: 54,  volume: '38,000 tons', quality: '85–90', demand: 'High',   tone: 'success' },
    { name: 'Colombia', coops: 76,  volume: '55,000 tons', quality: '84–89', demand: 'Medium', tone: 'warning' },
    { name: 'Brazil',   coops: 210, volume: '220,000 tons',quality: '80–86', demand: 'Stable', tone: 'secondary'},
];

const perfBars = [58, 68, 72, 80, 76, 88, 84, 92, 86, 90, 88, 94];
const qualBars = [72, 78, 74, 84, 80, 88, 84, 90, 86, 88, 91, 92];

const availableLots = [
    { coop: 'Sipi Falls Cooperative',   lots: 3, price: '$5.00–5.40', score: 91.2, demand: 'High',   tone: 'success' },
    { coop: 'Bugisu Cooperative Union', lots: 5, price: '$4.60–4.90', score: 88.0, demand: 'High',   tone: 'success' },
    { coop: 'Rwenzori Farmers Ltd',     lots: 2, price: '$4.20–4.60', score: 87.6, demand: 'Medium', tone: 'warning' },
    { coop: 'Kyoga Robusta Union',      lots: 7, price: '$1.80–2.10', score: 84.2, demand: 'Extreme',tone: 'danger'  },
];

const certifications = [
    { label: 'Organic Certification',  count: 84,  total: 142, pct: 59, tone: 'success' },
    { label: 'Fairtrade',              count: 62,  total: 142, pct: 44, tone: 'warning' },
    { label: 'Rainforest Alliance',    count: 38,  total: 142, pct: 27, tone: 'primary' },
    { label: 'Local Compliance',       count: 138, total: 142, pct: 97, tone: 'success' },
];

const sourcingRequests = ref([
    { buyer: 'Nordic Roasters',    region: 'Uganda',   type: 'Arabica', qty: '5,000 kg',  status: 'Open',      tone: 'success' },
    { buyer: 'Berlin Kaffee',      region: 'Ethiopia', type: 'Arabica', qty: '3,200 kg',  status: 'Matched',   tone: 'primary' },
    { buyer: 'Dubai Specialty Co', region: 'Uganda',   type: 'Robusta', qty: '12,000 kg', status: 'Fulfilled', tone: 'secondary'},
    { buyer: 'NYC Roast Guild',    region: 'Kenya',    type: 'AA',      qty: '2,800 kg',  status: 'Open',      tone: 'success' },
]);

const insights = [
    { text: 'Ugandan cooperatives are increasingly export-ready — 68% now hold valid export permits.',    tone: 'success' },
    { text: 'Rwenzori cooperatives show high specialty potential with cupping scores averaging 88+.',     tone: 'primary' },
    { text: 'Demand for certified cooperatives is rising in EU markets — Fairtrade sees 32% YoY growth.',tone: 'warning' },
];

const alerts = reactive({ newCoop: true, certUpdates: true, exportStatus: false, highDemand: true });

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Bean Origin Cooperative Advisor. I can help you find the best cooperatives, check certifications, and discover sourcing opportunities. What are you looking for?" },
]);
const prompts = ['Which cooperative should I source from?', 'Are cooperatives verified?', 'Which region has best quality?', 'Which cooperatives are export ready?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'Sipi Falls Cooperative offers the best quality (91.2 cupping score) with full export readiness and organic certification — ideal for specialty roasters. Bugisu Cooperative Union offers the highest volume at competitive prices.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };

const badgeClass = (b) => {
    if (b === 'Verified')     return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (b === 'Export Ready') return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (b === 'Organic')      return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    if (b === 'Fairtrade')    return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    return 'bg-light text-secondary border';
};
</script>

<template>
    <AppLayout title="Cooperative Directory" full-width flush :show-banner="false">
        <Head title="Cooperative Directory" />

        <div class="cp-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="cp-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <h1 class="cp-title mb-0">Cooperative Directory</h1>
                            <p class="cp-subtitle mb-0">Discover verified coffee cooperatives across producing regions</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <Link :href="route('cooperative.create')" class="btn cp-btn-primary btn-sm"><el-icon><Star /></el-icon> Add Cooperative</Link>
                            <button class="btn cp-btn-outline btn-sm"><el-icon><Download /></el-icon> Export Directory</button>
                            <button class="btn cp-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>

                    <!-- Filter bar -->
                    <div class="cp-filters pb-3">
                        <div class="cp-search-wrap">
                            <el-icon class="cp-search-icon"><View /></el-icon>
                            <input v-model="search" class="cp-search-input" placeholder="Search cooperatives…">
                        </div>
                        <select v-model="regionF" class="cp-select">
                            <option v-for="r in regionOptions" :key="r" :value="r">{{ r === 'All' ? 'All Regions' : r }}</option>
                        </select>
                        <select v-model="coffeeTypeF" class="cp-select">
                            <option value="All">All Coffee Types</option>
                            <option>Arabica</option>
                            <option>Robusta</option>
                            <option>Washed Arabica</option>
                        </select>
                        <select v-model="certF" class="cp-select">
                            <option value="All">All Certifications</option>
                            <option>Organic</option>
                            <option>Fairtrade</option>
                            <option>Rainforest Alliance</option>
                        </select>
                        <select v-model="sizeF" class="cp-select">
                            <option value="All">All Sizes</option>
                            <option>Small</option>
                            <option>Medium</option>
                            <option>Large</option>
                        </select>
                        <select v-model="exportF" class="cp-select">
                            <option value="All">Export Status: All</option>
                            <option value="Yes">Export Ready</option>
                            <option value="No">Not Ready</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Insight Strip ────────────────────────────────── -->
                <div class="cp-insight mb-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="cp-insight__left">
                            <el-icon><Opportunity /></el-icon>
                            <span>Ugandan cooperatives show strong export-ready volumes with increasing global demand.</span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap ms-auto">
                            <span class="cp-insight-badge cp-insight-badge--green">Verified Network</span>
                            <span class="cp-insight-badge cp-insight-badge--soft">High Demand</span>
                            <span class="cp-insight-badge cp-insight-badge--muted">Export Ready</span>
                        </div>
                    </div>
                </div>

                <!-- ── 3. KPI Summary ──────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="cp-kpi h-100">
                            <span class="cp-kpi__label">Total Cooperatives</span>
                            <div class="cp-kpi__value">{{ props.cooperatives.length }}</div>
                            <div class="cp-kpi__sub cp-up">↑ +4% this month</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="cp-kpi h-100">
                            <span class="cp-kpi__label">Verified</span>
                            <div class="cp-kpi__value cp-kpi__value--success">{{ totalVerified }}</div>
                            <div class="cp-kpi__sub">Platform verified</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="cp-kpi h-100">
                            <span class="cp-kpi__label">Export Ready</span>
                            <div class="cp-kpi__value cp-kpi__value--primary">{{ totalExport }}</div>
                            <div class="cp-kpi__sub">Ready to ship</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="cp-kpi h-100">
                            <span class="cp-kpi__label">Active Sourcing</span>
                            <div class="cp-kpi__value cp-kpi__value--warning">{{ sourcingRequests.filter(s => s.status === 'Open').length }}</div>
                            <div class="cp-kpi__sub">Open requests</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg">
                        <div class="cp-kpi h-100">
                            <span class="cp-kpi__label">Member Network</span>
                            <div class="cp-kpi__value" style="font-size:.9375rem;">{{ totalMembers }}</div>
                            <div class="cp-kpi__sub">Across all cooperatives</div>
                        </div>
                    </div>
                </div>

                <!-- ── View toggle ─────────────────────────────────────── -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div style="font-size:.875rem; font-weight:600; color:var(--on-surface);">
                        Showing <strong>{{ filteredRows.length }}</strong> of <strong>{{ directoryRows.length }}</strong> cooperatives
                    </div>
                    <div class="cp-view-toggle">
                        <button class="cp-view-btn" :class="{ 'cp-view-btn--active': viewMode === 'grid' }" @click="viewMode = 'grid'">
                            <el-icon><CollectionTag /></el-icon> Grid
                        </button>
                        <button class="cp-view-btn" :class="{ 'cp-view-btn--active': viewMode === 'table' }" @click="viewMode = 'table'">
                            <el-icon><DataLine /></el-icon> Table
                        </button>
                    </div>
                </div>

                <!-- ── 4. Grid View ────────────────────────────────────── -->
                <div v-if="viewMode === 'grid'" class="mb-3">
                    <div v-if="!filteredRows.length" class="cp-empty">
                        <el-icon><Warning /></el-icon>
                        <div>No cooperatives match your filters.</div>
                    </div>
                    <div v-else class="row g-3">
                        <div v-for="coop in filteredRows" :key="coop.id" class="col-12 col-sm-6 col-lg-4">
                            <div class="cp-coop-card h-100">
                                <!-- Card header -->
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="cp-coop-avatar">{{ coop.initials }}</div>
                                    <div class="flex-fill min-w-0">
                                        <div class="cp-coop-name">{{ coop.name }}</div>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                            <span class="cp-coop-location">{{ coop.territory }}</span>
                                        </div>
                                    </div>
                                    <div class="cp-score-badge" :class="coop.score >= 88 ? 'cp-score-badge--high' : 'cp-score-badge--mid'">
                                        {{ coop.score }}
                                    </div>
                                </div>
                                <!-- Stats row -->
                                <div class="row g-1 mb-2">
                                    <div class="col-4"><div class="cp-stat-cell"><span>Members</span><strong>{{ coop.membersLabel }}</strong></div></div>
                                    <div class="col-4"><div class="cp-stat-cell"><span>Production</span><strong>{{ coop.production }}</strong></div></div>
                                    <div class="col-4"><div class="cp-stat-cell"><span>Size</span><strong>{{ coop.size }}</strong></div></div>
                                </div>
                                <!-- Coffee types -->
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span v-for="t in coop.coffeeTypes" :key="t" class="cp-type-chip">{{ t }}</span>
                                </div>
                                <!-- Badges -->
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span v-for="b in coop.badges" :key="b" class="badge rounded-pill" :class="badgeClass(b)" style="font-size:.6rem; padding:2px 7px;">{{ b }}</span>
                                </div>
                                <!-- Actions -->
                                <div class="d-flex gap-2 mt-auto">
                                    <Link :href="route('cooperative.show', coop.id)" class="btn cp-btn-primary btn-sm flex-fill">
                                        <el-icon><View /></el-icon> View Profile
                                    </Link>
                                    <button class="btn cp-btn-outline btn-sm flex-fill"><el-icon><ShoppingCart /></el-icon> View Lots</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 5. Table View ───────────────────────────────────── -->
                <div v-if="viewMode === 'table'" class="cp-card p-0 overflow-hidden mb-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 cp-table">
                            <thead>
                                <tr>
                                    <th>Cooperative</th>
                                    <th>Location</th>
                                    <th>Members</th>
                                    <th>Production</th>
                                    <th>Quality</th>
                                    <th>Certifications</th>
                                    <th>Export</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!filteredRows.length">
                                    <td colspan="8" class="text-center py-4 cp-td-muted">No cooperatives match your filters.</td>
                                </tr>
                                <tr v-for="coop in filteredRows" :key="coop.id" class="cp-table-row">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="cp-coop-avatar cp-coop-avatar--sm">{{ coop.initials }}</div>
                                            <div>
                                                <div class="cp-item-name">{{ coop.name }}</div>
                                                <div class="cp-td-muted" style="font-size:.7rem;">{{ coop.code || '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cp-td-muted">{{ coop.territory }}</td>
                                    <td class="cp-td-muted">{{ coop.membersLabel }}</td>
                                    <td class="cp-td-muted">{{ coop.production }}</td>
                                    <td>
                                        <span class="cp-score-pill" :class="coop.score >= 88 ? 'cp-score-pill--high' : 'cp-score-pill--mid'">{{ coop.score }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span v-for="b in coop.badges" :key="b" class="badge rounded-pill" :class="badgeClass(b)" style="font-size:.58rem; padding:2px 6px;">{{ b }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" style="font-size:.65rem;"
                                            :class="coop.exportReady ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                            {{ coop.exportReady ? 'Ready' : 'Not Ready' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <Link :href="route('cooperative.show', coop.id)" class="btn btn-sm cp-btn-primary px-2"><el-icon><View /></el-icon></Link>
                                            <button class="btn btn-sm cp-btn-outline px-2"><el-icon><ShoppingCart /></el-icon></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="cp-table-footer">
                        <span class="cp-td-muted" style="font-size:.75rem;">Showing {{ filteredRows.length }} of {{ directoryRows.length }} cooperatives</span>
                        <button class="btn cp-btn-outline btn-sm ms-auto">Load More</button>
                    </div>
                </div>

                <!-- ── 6. Featured Cooperatives ────────────────────────── -->
                <div class="cp-card mb-3">
                    <div class="cp-card-title mb-3">
                        <el-icon class="cp-card-icon"><Medal /></el-icon>
                        Featured Cooperatives
                    </div>
                    <div class="row g-3">
                        <div v-for="feat in featured" :key="feat.label" class="col-12 col-md-4">
                            <div class="cp-featured-card h-100">
                                <div class="cp-featured-label">{{ feat.label }}</div>
                                <div class="cp-featured-name">{{ feat.name }}</div>
                                <div class="d-flex align-items-center gap-1 mb-2">
                                    <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                    <span class="cp-td-muted" style="font-size:.75rem;">{{ feat.region }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="cp-type-chip">{{ feat.type }}</span>
                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                        :class="feat.badgeTone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                              : feat.badgeTone === 'primary' ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'
                                              : 'bg-info-subtle text-info-emphasis border border-info-subtle'">
                                        {{ feat.badge }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <div style="font-size:.625rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--on-surface-var);">Cupping Score</div>
                                        <div class="cp-featured-score">{{ feat.score }}</div>
                                    </div>
                                    <button class="btn cp-btn-primary btn-sm">View Profile</button>
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
                            <div class="cp-card">
                                <div class="cp-card-title mb-3">
                                    <el-icon class="cp-card-icon"><Location /></el-icon>
                                    Regional Breakdown
                                </div>
                                <div class="row g-2">
                                    <div v-for="reg in regions" :key="reg.name" class="col-6 col-md-4 col-lg">
                                        <div class="cp-region-card h-100">
                                            <div class="cp-region-name">{{ reg.name }}</div>
                                            <div class="cp-region-coops">{{ reg.coops }} cooperatives</div>
                                            <div class="row g-1 mt-2">
                                                <div class="col-12"><div class="cp-stat-cell"><span>Volume</span><strong>{{ reg.volume }}</strong></div></div>
                                                <div class="col-12"><div class="cp-stat-cell"><span>Quality</span><strong>{{ reg.quality }}</strong></div></div>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                                    :class="reg.tone === 'danger'    ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                          : reg.tone === 'success'   ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                          : reg.tone === 'warning'   ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                          : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                                    {{ reg.demand }} Demand
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Available Coffee from Cooperatives -->
                            <div class="cp-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="cp-card-title">
                                        <el-icon class="cp-card-icon"><ShoppingCart /></el-icon>
                                        Available Coffee from Cooperatives
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ availableLots.length }} cooperatives</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 cp-table">
                                        <thead>
                                            <tr>
                                                <th>Cooperative</th>
                                                <th>Lots Available</th>
                                                <th>Price Range</th>
                                                <th>Quality Score</th>
                                                <th>Demand</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in availableLots" :key="lot.coop" class="cp-table-row">
                                                <td class="cp-item-name">{{ lot.coop }}</td>
                                                <td>
                                                    <span class="cp-lots-count">{{ lot.lots }}</span>
                                                    <span class="cp-td-muted ms-1" style="font-size:.75rem;">lots</span>
                                                </td>
                                                <td class="fw-semibold">{{ lot.price }}</td>
                                                <td>
                                                    <span class="cp-score-pill" :class="lot.score >= 88 ? 'cp-score-pill--high' : 'cp-score-pill--mid'">{{ lot.score }}</span>
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
                                                        <button class="btn btn-sm cp-btn-outline" style="font-size:.75rem;">View Lots</button>
                                                        <button class="btn btn-sm cp-btn-primary" style="font-size:.75rem;"><el-icon><ShoppingCart /></el-icon> Buy</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 11. Sourcing Requests -->
                            <div class="cp-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="cp-card-title">
                                        <el-icon class="cp-card-icon"><CollectionTag /></el-icon>
                                        Sourcing Requests
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">{{ sourcingRequests.filter(s => s.status === 'Open').length }} open</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 cp-table">
                                        <thead>
                                            <tr>
                                                <th>Buyer</th>
                                                <th>Region</th>
                                                <th>Coffee Type</th>
                                                <th>Quantity</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="sr in sourcingRequests" :key="sr.buyer + sr.region" class="cp-table-row">
                                                <td class="cp-item-name">{{ sr.buyer }}</td>
                                                <td class="cp-td-muted">{{ sr.region }}</td>
                                                <td><span class="cp-type-chip">{{ sr.type }}</span></td>
                                                <td class="cp-td-muted">{{ sr.qty }}</td>
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
                        <div class="cp-rail">

                            <!-- 8. Performance Metrics -->
                            <div class="cp-card">
                                <div class="cp-card-title mb-3">
                                    <el-icon class="cp-card-icon"><TrendCharts /></el-icon>
                                    Performance Metrics
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><div class="cp-metric-cell"><span>Avg Cupping Score</span><strong>87.4</strong></div></div>
                                    <div class="col-6"><div class="cp-metric-cell"><span>Export Readiness</span><strong class="cp-up">68%</strong></div></div>
                                    <div class="col-6"><div class="cp-metric-cell"><span>Yield / Farm</span><strong>2.4 t/ha</strong></div></div>
                                    <div class="col-6"><div class="cp-metric-cell"><span>Sustainability</span><strong class="cp-up">82%</strong></div></div>
                                </div>
                                <div class="cp-chart-label-row mb-1">
                                    <span class="cp-td-muted" style="font-size:.7rem;">Production Trend</span>
                                </div>
                                <div class="cp-mini-chart mb-3">
                                    <div v-for="(v, i) in perfBars" :key="i" class="cp-mini-bar" :style="{ height: `${v}%` }"></div>
                                </div>
                                <div class="cp-chart-label-row mb-1">
                                    <span class="cp-td-muted" style="font-size:.7rem;">Quality Trend</span>
                                </div>
                                <div class="cp-mini-chart cp-mini-chart--quality">
                                    <div v-for="(v, i) in qualBars" :key="i" class="cp-mini-bar" :style="{ height: `${v}%` }"></div>
                                </div>
                            </div>

                            <!-- 10. Certification & Compliance -->
                            <div class="cp-card">
                                <div class="cp-card-title mb-3">
                                    <el-icon class="cp-card-icon"><Checked /></el-icon>
                                    Certification &amp; Compliance
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="cert in certifications" :key="cert.label">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="cp-cert-label">{{ cert.label }}</span>
                                            <span class="fw-700" :class="cert.pct >= 80 ? 'cp-up' : cert.pct >= 40 ? 'cp-warn' : 'cp-down'" style="font-size:.8125rem;">{{ cert.pct }}%</span>
                                        </div>
                                        <div class="cp-bar-track">
                                            <div class="cp-bar-fill"
                                                :style="{ width: `${cert.pct}%`,
                                                          background: cert.pct >= 80 ? '#004532' : cert.pct >= 40 ? '#f59e0b' : '#ef4444' }">
                                            </div>
                                        </div>
                                        <div class="cp-td-muted mt-1" style="font-size:.7rem;">{{ cert.count }} / {{ cert.total }} cooperatives</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. AI Insights -->
                            <div>
                                <div class="cp-card-title mb-2">
                                    <el-icon class="cp-card-icon"><Opportunity /></el-icon>
                                    Cooperative Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="cp-insight-card" :class="`cp-insight-card--${ins.tone}`">
                                        <el-icon class="cp-insight-icon"><Opportunity /></el-icon>
                                        <p class="cp-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. Smart Alerts -->
                            <div class="cp-card">
                                <div class="cp-card-title mb-3">
                                    <el-icon class="cp-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="cp-alert-row">
                                        <span>{{ { newCoop: 'New Cooperative Added', certUpdates: 'Certification Updates', exportStatus: 'Export-Ready Status', highDemand: 'High Demand Region' }[key] }}</span>
                                        <button class="cp-toggle" :class="{ 'cp-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 14. Floating Chatbot ────────────────────────────────── -->
            <div class="cp-fab-wrap">
                <Transition name="cp-chat">
                    <div v-if="chatOpen" class="cp-chatbot">
                        <div class="cp-chatbot__head">
                            <div class="cp-chatbot__identity">
                                <div class="cp-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="cp-chatbot__name">Cooperative Advisor</div>
                                    <div class="cp-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="cp-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="cp-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="cp-chat-msg" :class="`cp-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="cp-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="cp-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="cp-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="cp-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.cp-page {
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
.cp-header   { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.cp-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; }
.cp-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Filter bar ────────────────────────────────────────────────────────────── */
.cp-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.cp-search-wrap  { position: relative; display: flex; align-items: center; }
.cp-search-icon  { position: absolute; left: 8px; font-size: 13px; color: var(--on-surface-var); pointer-events: none; }
.cp-search-input { height: 32px; border: 1px solid var(--surface-high); border-radius: 7px; padding: 0 10px 0 28px; font-size: 0.8125rem; outline: none; color: var(--on-surface); background: var(--surface-white); width: 180px; }
.cp-search-input:focus { border-color: var(--green); }
.cp-select { height: 32px; border: 1px solid var(--surface-high); border-radius: 7px; padding: 0 10px; font-size: 0.8125rem; color: var(--on-surface); background: var(--surface-white); outline: none; cursor: pointer; }
.cp-select:focus { border-color: var(--green); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.cp-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.cp-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.cp-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.cp-btn-outline:hover { background: var(--surface-low); }
.cp-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }

/* ── Insight strip ─────────────────────────────────────────────────────────── */
.cp-insight { background: linear-gradient(135deg, var(--green), var(--green-grad)); color: #fff; border-radius: 10px; padding: 12px 16px; }
.cp-insight__left { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; font-weight: 600; }
.cp-insight-badge { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.cp-insight-badge--green { background: rgba(255,255,255,0.18); color: #fff; }
.cp-insight-badge--soft  { background: rgba(166,242,209,0.2);  color: #a6f2d1; }
.cp-insight-badge--muted { background: rgba(255,255,255,0.1);  color: rgba(255,255,255,0.8); }

/* ── KPI Cards ─────────────────────────────────────────────────────────────── */
.cp-kpi { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.cp-kpi__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.cp-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.cp-kpi__value--success { color: var(--green); }
.cp-kpi__value--primary { color: #0369a1; }
.cp-kpi__value--warning { color: #92400e; }
.cp-kpi__sub   { font-size: 0.625rem; color: var(--on-surface-var); }

/* ── View toggle ───────────────────────────────────────────────────────────── */
.cp-view-toggle { display: flex; gap: 3px; background: var(--surface-low); border-radius: 8px; padding: 3px; }
.cp-view-btn { font-size: 0.75rem; font-weight: 600; padding: 4px 12px; border-radius: 6px; border: none; background: transparent; color: var(--on-surface-var); cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.12s; }
.cp-view-btn:hover { background: var(--surface-mid); }
.cp-view-btn--active { background: var(--surface-white); color: var(--green); box-shadow: 0 1px 3px rgba(0,0,0,.08); font-weight: 700; }

/* ── Empty state ───────────────────────────────────────────────────────────── */
.cp-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 40px 20px; color: var(--on-surface-var); font-size: 0.875rem; background: var(--surface-low); border-radius: 12px; border: 1px solid var(--surface-high); }

/* ── Cooperative card (grid) ───────────────────────────────────────────────── */
.cp-coop-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); display: flex; flex-direction: column; transition: box-shadow 0.15s; }
.cp-coop-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); border-color: rgba(0,69,50,0.2); }
.cp-coop-avatar { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; font-weight: 800; font-size: 0.9375rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cp-coop-avatar--sm { width: 32px; height: 32px; border-radius: 8px; font-size: 0.8125rem; }
.cp-coop-name { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); line-height: 1.3; }
.cp-coop-location { font-size: 0.75rem; color: var(--on-surface-var); }
.cp-score-badge { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.6875rem; font-weight: 800; flex-shrink: 0; }
.cp-score-badge--high { background: #dcfce7; color: #166534; }
.cp-score-badge--mid  { background: #fef3c7; color: #92400e; }
.cp-stat-cell { background: var(--surface-low); border-radius: 6px; padding: 5px 8px; }
.cp-stat-cell span   { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.cp-stat-cell strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); display: block; }
.cp-type-chip { display: inline-flex; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.cp-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.cp-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.cp-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Featured ──────────────────────────────────────────────────────────────── */
.cp-featured-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; display: flex; flex-direction: column; }
.cp-featured-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--green); margin-bottom: 4px; }
.cp-featured-name  { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.cp-featured-score { font-size: 1.25rem; font-weight: 800; color: var(--green); }

/* ── Regional cards ────────────────────────────────────────────────────────── */
.cp-region-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.cp-region-name  { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); margin-bottom: 2px; }
.cp-region-coops { font-size: 0.75rem; color: var(--on-surface-var); margin-bottom: 4px; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.cp-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.cp-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.cp-table-row { transition: background 0.1s; }
.cp-table-row:hover { background: var(--surface-low); }
.cp-item-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.cp-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; }
.cp-table-footer { display: flex; align-items: center; padding: 10px 16px; border-top: 1px solid var(--surface-low); background: var(--surface-low); }
.cp-score-pill { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 800; padding: 2px 8px; }
.cp-score-pill--high { background: #dcfce7; color: #166534; }
.cp-score-pill--mid  { background: #fef3c7; color: #92400e; }
.cp-lots-count { font-size: 1rem; font-weight: 800; color: var(--green); }
.cp-up   { color: #166534; font-weight: 700; }
.cp-warn { color: #92400e; font-weight: 700; }
.cp-down { color: #b91c1c; font-weight: 700; }
.fw-700 { font-weight: 700; }

/* ── Performance / mini charts ─────────────────────────────────────────────── */
.cp-metric-cell { background: var(--surface-low); border-radius: 8px; padding: 8px 10px; }
.cp-metric-cell span   { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 2px; }
.cp-metric-cell strong { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); }
.cp-mini-chart { display: flex; align-items: flex-end; gap: 3px; height: 44px; background: var(--surface-low); border-radius: 8px; padding: 5px; }
.cp-mini-chart--quality .cp-mini-bar { background: #059669; }
.cp-mini-bar   { flex: 1; background: var(--green); border-radius: 2px 2px 0 0; opacity: 0.75; }
.cp-chart-label-row { display: flex; }

/* ── Certification bars ────────────────────────────────────────────────────── */
.cp-cert-label { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.cp-bar-track  { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.cp-bar-fill   { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.cp-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.cp-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.cp-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.cp-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.cp-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.cp-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.cp-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.cp-alert-row:last-child { border-bottom: none; }
.cp-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.cp-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.cp-toggle--on { background: var(--green); }
.cp-toggle--on i { transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.cp-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.cp-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.cp-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.cp-fab:hover { background: var(--green-grad); }
.cp-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.cp-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.cp-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.cp-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.cp-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.cp-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.cp-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.cp-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.cp-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.cp-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.cp-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.cp-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.cp-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.cp-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.cp-prompt-chip:hover { background: var(--surface-mid); }
.cp-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.cp-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.cp-chatbot__input input:focus { border-color: var(--green); }
.cp-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.cp-chat-enter-active, .cp-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.cp-chat-enter-from, .cp-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .cp-rail { position: static; } }
@media (max-width: 767.98px)  {
    .cp-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
    .cp-filters { gap: 6px; }
    .cp-search-input { width: 140px; }
}
</style>
