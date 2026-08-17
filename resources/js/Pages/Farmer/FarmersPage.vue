<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked,
    CollectionTag, DataLine, Download,
    Location, Medal, Money, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, UserFilled, Van, View,
    Warning,
} from '@element-plus/icons-vue';

const props = defineProps({
    farmers: { type: Array, default: () => [] },
});

/* ── Filters ───────────────────────────────────────────────────── */
const search      = ref('');
const districtF   = ref('All');
const coffeeTypeF = ref('All');
const exportF     = ref('All');
const viewMode    = ref('table');

const uniqueRegions   = computed(() => ['All', ...new Set(props.farmers.map(f => f.district).filter(Boolean))].sort());
const uniqueVarieties = computed(() => ['All', ...new Set(props.farmers.map(f => f.coffee_type).filter(Boolean))].sort());

/* ── Derived rows ──────────────────────────────────────────────── */
const directoryRows = computed(() =>
    props.farmers.map((farmer, i) => {
        const score = farmer.coffee_type?.toLowerCase() === 'robusta'
            ? 84 + (i % 3)
            : 87 + ((i + 2) % 4);
        const displayScore = `${score}.${i % 2 === 0 ? '5' : '0'}`;
        return {
            ...farmer,
            fullName: [farmer.first_name, farmer.last_name].filter(Boolean).join(' ') || 'Unnamed Producer',
            initials: [farmer.first_name?.[0], farmer.last_name?.[0]].filter(Boolean).join('').toUpperCase().slice(0, 2) || 'FP',
            location: [farmer.sub_county, farmer.district].filter(Boolean).join(', ') || 'Origin pending',
            estates: farmer.farms_count || 0,
            score: displayScore,
            scoreNum: parseFloat(displayScore),
            scoreNote: score >= 90 ? 'Exceptional' : score >= 88 ? 'Excellent' : score >= 86 ? 'Very Good' : 'Good',
            exportReady: i % 3 !== 2,
            traceability: 80 + (i % 18),
            buyerRating: (4.5 + ((i * 7) % 5) / 10).toFixed(1),
            sus: ['Organic', 'Shade Grown', 'Climate Smart', 'Ethical Farming'].slice(0, 2 + (i % 3)),
            lotsAvailable: 1 + (i % 4),
            priceRange: farmer.coffee_type?.toLowerCase() === 'robusta' ? '$1.75–2.10' : '$4.50–5.20',
        };
    }),
);

const filteredRows = computed(() => {
    const term = search.value.trim().toLowerCase();
    return directoryRows.value.filter(f => {
        const hay = [f.fullName, f.district, f.sub_county, f.coffee_type].filter(Boolean).join(' ').toLowerCase();
        const mS = !term || hay.includes(term);
        const mD = districtF.value === 'All' || f.district === districtF.value;
        const mC = coffeeTypeF.value === 'All' || f.coffee_type === coffeeTypeF.value;
        const mE = exportF.value === 'All' || (exportF.value === 'Yes' ? f.exportReady : !f.exportReady);
        return mS && mD && mC && mE;
    });
});

/* ── KPIs ──────────────────────────────────────────────────────── */
const totalVerified  = computed(() => Math.round(directoryRows.value.length * 0.84));
const totalExport    = computed(() => directoryRows.value.filter(f => f.exportReady).length);
const avgScore       = computed(() => {
    if (!directoryRows.value.length) return '—';
    const s = directoryRows.value.reduce((a, f) => a + f.scoreNum, 0) / directoryRows.value.length;
    return s.toFixed(1);
});
const specialtyCount = computed(() => directoryRows.value.filter(f => f.scoreNum >= 87).length);

/* ── Featured ──────────────────────────────────────────────────── */
const featured = computed(() => {
    const rows = [...directoryRows.value];
    const byScore   = [...rows].sort((a, b) => b.scoreNum - a.scoreNum)[0];
    const byExport  = rows.filter(r => r.exportReady)[0] || rows[0];
    const bySus     = [...rows].sort((a, b) => b.traceability - a.traceability)[0];
    const byRising  = rows[rows.length - 1] || rows[0];
    return [
        { label: 'Highest Quality',      farmer: byScore,  badge: 'Top Score',       tone: 'success' },
        { label: 'Top Export Farmer',    farmer: byExport, badge: 'Export Leader',   tone: 'primary' },
        { label: 'Sustainable Producer', farmer: bySus,    badge: 'Eco Certified',   tone: 'warning' },
        { label: 'Rising Specialty',     farmer: byRising, badge: 'Specialty Rising', tone: 'info'   },
    ].filter(f => f.farmer);
});

/* ── Static mock data ──────────────────────────────────────────── */
const prodBars  = [52, 64, 68, 78, 72, 82, 86, 82, 90, 86, 92, 88];
const qualBars  = [70, 76, 72, 82, 78, 84, 82, 88, 84, 90, 89, 92];

const regions = [
    { name: 'Rwenzori',    farmers: 84,  type: 'Arabica', quality: 'High',    volume: '22K t' },
    { name: 'Mt. Elgon',   farmers: 62,  type: 'Arabica', quality: 'Premium', volume: '18K t' },
    { name: 'Central UG',  farmers: 48,  type: 'Robusta', quality: 'Good',    volume: '32K t' },
    { name: 'SW Uganda',   farmers: 56,  type: 'Mixed',   quality: 'High',    volume: '14K t' },
];

const certifications = [
    { label: 'Organic Certification',   pct: 59, done: 168 },
    { label: 'Fairtrade',               pct: 44, done: 124 },
    { label: 'Rainforest Alliance',     pct: 27, done: 76  },
    { label: 'Traceability Verified',   pct: 84, done: 238 },
];

const buyerMatches = [
    { buyer: 'Nordic Roasters',    match: 94, quality: 'High', supply: 'Consistent', price: 'Competitive' },
    { buyer: 'Berlin Kaffee',      match: 88, quality: 'High', supply: 'Strong',     price: 'Fair'        },
    { buyer: 'Dubai Specialty Co', match: 82, quality: 'Good', supply: 'Reliable',   price: 'Favorable'   },
];

const alerts = reactive({ newFarmer: true, highQuality: true, priceChange: false, exportReady: true, specialtyLots: false });

const insights = [
    { text: 'Specialty production increasing in high-altitude Arabica regions — quality scores up 2.8% YoY.',  tone: 'success' },
    { text: 'Export-quality Robusta supply improving — UAE demand at record highs.',                           tone: 'primary' },
    { text: 'Certified farmers receiving 34% higher buyer interest than uncertified counterparts.',            tone: 'warning' },
];

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Bean Origin Farmer Advisor. I can help you discover the best coffee farmers, match your sourcing preferences, and identify export-ready producers. What are you looking for?" },
]);
const prompts = ['Which farmers produce specialty coffee?', 'Who has export-ready lots?', 'Which region has highest quality?', 'Which farmers match my sourcing needs?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: `There are ${specialtyCount.value} specialty-grade farmers (87+ SCA) in the directory, with ${totalExport.value} export-ready producers. High-altitude farms in Rwenzori and Mt. Elgon are showing the strongest cupping scores this season.` }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };

const badgeClass = (b) => {
    if (b === 'Verified')     return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (b === 'Export Ready') return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (b === 'Organic')      return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    return 'bg-info-subtle text-info-emphasis border border-info-subtle';
};
</script>

<template>
    <AppLayout title="Farmers Directory" full-width flush :show-banner="false">
        <Head title="Farmers Directory" />

        <div class="fd-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="fd-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="fd-kicker">Farmer Workspace</div>
                            <h1 class="fd-title mb-0">Farmers Directory</h1>
                            <p class="fd-subtitle mb-0">Discover verified coffee farmers producing traceable, export-ready coffee</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <Link :href="route('farmer.create')" class="btn fd-btn-primary btn-sm"><el-icon><Star /></el-icon> Add Farmer</Link>
                            <button class="btn fd-btn-outline btn-sm"><el-icon><Download /></el-icon> Export Directory</button>
                            <button class="btn fd-btn-ghost btn-sm" @click="chatOpen = true"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 pb-2">
                        <span class="fd-hbadge"><el-icon><Checked /></el-icon> Verified Producers</span>
                        <span class="fd-hbadge fd-hbadge--soft"><el-icon><Check /></el-icon> Traceable Coffee</span>
                        <span class="fd-hbadge fd-hbadge--amber"><el-icon><Star /></el-icon> Sustainable Farming</span>
                        <span class="fd-hbadge fd-hbadge--blue"><el-icon><Van /></el-icon> Export Ready</span>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. AI Intelligence Banner ──────────────────────── -->
                <div class="fd-ai-banner mb-3">
                    <div class="fd-ai-banner__left">
                        <div class="fd-ai-banner__icon"><el-icon><Opportunity /></el-icon></div>
                        <div>
                            <div class="fd-ai-banner__text">High-altitude farmers in Western Uganda are producing strong specialty-grade Arabica this season.</div>
                        </div>
                    </div>
                    <div class="fd-ai-stats">
                        <div class="fd-ai-stat"><span>Opportunity</span><strong>High</strong></div>
                        <div class="fd-ai-stat fd-ai-stat--div"></div>
                        <div class="fd-ai-stat"><span>Demand</span><strong>Rising ↑</strong></div>
                        <div class="fd-ai-stat fd-ai-stat--div"></div>
                        <div class="fd-ai-stat"><span>Price Trend</span><strong>+4.2%</strong></div>
                        <div class="fd-ai-stat fd-ai-stat--div"></div>
                        <div class="fd-ai-stat"><span>Export Ready</span><strong>{{ totalExport }}</strong></div>
                    </div>
                </div>

                <!-- ── 3. Smart Filters ────────────────────────────────── -->
                <div class="fd-filters mb-3">
                    <div class="fd-search-wrap">
                        <el-icon class="fd-search-icon"><View /></el-icon>
                        <input v-model="search" class="fd-search-input" placeholder="Search farmer name, region…">
                    </div>
                    <select v-model="districtF" class="fd-select">
                        <option v-for="r in uniqueRegions" :key="r" :value="r">{{ r === 'All' ? 'All Districts' : r }}</option>
                    </select>
                    <select v-model="coffeeTypeF" class="fd-select">
                        <option v-for="c in uniqueVarieties" :key="c" :value="c">{{ c === 'All' ? 'All Coffee Types' : c }}</option>
                    </select>
                    <select v-model="exportF" class="fd-select">
                        <option value="All">Export Status: All</option>
                        <option value="Yes">Export Ready</option>
                        <option value="No">Not Ready</option>
                    </select>
                    <button class="btn fd-btn-ghost btn-sm" @click="search = ''; districtF = 'All'; coffeeTypeF = 'All'; exportF = 'All'">Reset</button>
                </div>

                <!-- ── 4. KPI Cards ────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="fd-kpi h-100">
                            <span class="fd-kpi__label">Total Farmers</span>
                            <div class="fd-kpi__value">{{ props.farmers.length }}</div>
                            <div class="fd-kpi__sub fd-up">↑ +6% this month</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="fd-kpi h-100">
                            <span class="fd-kpi__label">Verified</span>
                            <div class="fd-kpi__value fd-kpi__value--success">{{ totalVerified }}</div>
                            <div class="fd-kpi__sub">Platform verified</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="fd-kpi h-100">
                            <span class="fd-kpi__label">Export Ready</span>
                            <div class="fd-kpi__value fd-kpi__value--primary">{{ totalExport }}</div>
                            <div class="fd-kpi__sub">Cleared for export</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="fd-kpi h-100">
                            <span class="fd-kpi__label">Active Harvests</span>
                            <div class="fd-kpi__value fd-kpi__value--warning">{{ Math.max(1, Math.round(props.farmers.length * 0.6)) }}</div>
                            <div class="fd-kpi__sub">This season</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="fd-kpi h-100">
                            <span class="fd-kpi__label">Avg Quality</span>
                            <div class="fd-kpi__value fd-kpi__value--success">{{ avgScore }}</div>
                            <div class="fd-kpi__sub">SCA avg score</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="fd-kpi h-100">
                            <span class="fd-kpi__label">Specialty</span>
                            <div class="fd-kpi__value">{{ specialtyCount }}</div>
                            <div class="fd-kpi__sub">Score 87+</div>
                        </div>
                    </div>
                </div>

                <!-- ── View toggle ─────────────────────────────────────── -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div style="font-size:.875rem; font-weight:600; color:var(--on-surface);">
                        Showing <strong>{{ filteredRows.length }}</strong> of <strong>{{ directoryRows.length }}</strong> farmers
                    </div>
                    <div class="fd-view-toggle">
                        <button class="fd-view-btn" :class="{ 'fd-view-btn--active': viewMode === 'table' }" @click="viewMode = 'table'">
                            <el-icon><DataLine /></el-icon> Table
                        </button>
                        <button class="fd-view-btn" :class="{ 'fd-view-btn--active': viewMode === 'grid' }" @click="viewMode = 'grid'">
                            <el-icon><CollectionTag /></el-icon> Grid
                        </button>
                    </div>
                </div>

                <!-- ── 6. Table View ───────────────────────────────────── -->
                <div v-if="viewMode === 'table'" class="fd-card p-0 overflow-hidden mb-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fd-table">
                            <thead>
                                <tr>
                                    <th><span class="fd-th"><el-icon><UserFilled /></el-icon> Farmer</span></th>
                                    <th><span class="fd-th"><el-icon><Location /></el-icon> Region</span></th>
                                    <th><span class="fd-th"><el-icon><CollectionTag /></el-icon> Coffee Type</span></th>
                                    <th><span class="fd-th"><el-icon><Box /></el-icon> Farms</span></th>
                                    <th><span class="fd-th"><el-icon><TrendCharts /></el-icon> Quality</span></th>
                                    <th><span class="fd-th"><el-icon><Checked /></el-icon> Sustainability</span></th>
                                    <th><span class="fd-th"><el-icon><Van /></el-icon> Export</span></th>
                                    <th><span class="fd-th"><el-icon><DataLine /></el-icon> Lots</span></th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!filteredRows.length">
                                    <td colspan="9" class="text-center fd-td-muted" style="padding: 2.5rem 1rem;">
                                        <div class="fd-empty-state__icon" style="margin: 0 auto 10px;"><el-icon :size="20"><Warning /></el-icon></div>
                                        <div class="fd-empty-state__title" style="margin-bottom: 2px;">No farmers match your filters</div>
                                        <span style="font-size:.8125rem;">Try adjusting your search or filters.</span>
                                    </td>
                                </tr>
                                <tr v-for="farmer in filteredRows" :key="farmer.id" class="fd-table-row">
                                    <td style="min-width:200px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="fd-avatar-sm">
                                                <el-icon class="fd-avatar-icon"><UserFilled /></el-icon>
                                                <span class="fd-avatar-initials">{{ farmer.initials }}</span>
                                            </div>
                                            <div>
                                                <div class="fd-item-name">{{ farmer.fullName }}</div>
                                                <div class="fd-td-muted" style="font-size:.7rem;">FMR-{{ String(farmer.id).padStart(4,'0') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                            <span class="fd-td-muted">{{ farmer.location }}</span>
                                        </div>
                                    </td>
                                    <td><span class="fd-coffee-chip">{{ farmer.coffee_type || 'Arabica' }}</span></td>
                                    <td class="fd-td-muted">{{ farmer.estates || 0 }} farm(s)</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fd-score-pill" :class="farmer.scoreNum >= 88 ? 'fd-score-pill--high' : 'fd-score-pill--mid'">{{ farmer.score }}</span>
                                            <span class="fd-td-muted" style="font-size:.7rem;">{{ farmer.scoreNote }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="fd-bar-track" style="width:48px;">
                                                <div class="fd-bar-fill" :style="{ width: `${farmer.traceability}%`, background: '#004532' }"></div>
                                            </div>
                                            <span style="font-size:.75rem; font-weight:700;" class="fd-up">{{ farmer.traceability }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" style="font-size:.65rem;"
                                            :class="farmer.exportReady ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                            {{ farmer.exportReady ? 'Ready' : 'Not Ready' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fd-lots-badge">{{ farmer.lotsAvailable }}</span>
                                    </td>
                                    <td class="text-end">
                                        <Link :href="route('farmer.show', farmer.id)" class="btn btn-sm fd-btn-primary fd-act-btn">
                                            <el-icon><View /></el-icon> View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="fd-table-footer">
                        <span class="fd-td-muted" style="font-size:.75rem;">Showing {{ filteredRows.length }} of {{ directoryRows.length }} farmers</span>
                        <button class="btn fd-btn-outline btn-sm ms-auto">Load More</button>
                    </div>
                </div>

                <!-- ── 5. Grid View ────────────────────────────────────── -->
                <div v-if="viewMode === 'grid'" class="mb-3">
                    <div v-if="!filteredRows.length" class="fd-empty-state">
                        <div class="fd-empty-state__icon"><el-icon :size="22"><Warning /></el-icon></div>
                        <div class="fd-empty-state__title">No farmers match your filters</div>
                        <p class="fd-empty-state__text">Try adjusting your search term or filter selections to find the farmers you're looking for.</p>
                        <button class="btn fd-btn-outline btn-sm" @click="search = ''; districtF = 'All'; coffeeTypeF = 'All'; exportF = 'All'">Reset Filters</button>
                    </div>
                    <div v-else class="row g-3">
                        <div v-for="farmer in filteredRows" :key="farmer.id" class="col-12 col-sm-6 col-lg-4">
                            <div class="fd-farmer-card h-100">
                                <!-- Top: Identity -->
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="fd-avatar-lg">
                                        <el-icon class="fd-avatar-icon"><UserFilled /></el-icon>
                                        <span class="fd-avatar-initials">{{ farmer.initials }}</span>
                                    </div>
                                    <div class="flex-fill min-w-0">
                                        <div class="fd-farmer-name">{{ farmer.fullName }}</div>
                                        <div class="d-flex align-items-center gap-1">
                                            <el-icon style="font-size:11px; color:var(--on-surface-var);"><Location /></el-icon>
                                            <span class="fd-td-muted" style="font-size:.75rem;">{{ farmer.location }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.58rem; padding:2px 6px;">Verified</span>
                                            <span v-if="farmer.exportReady" class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle" style="font-size:.58rem; padding:2px 6px;">Export Ready</span>
                                        </div>
                                    </div>
                                    <div class="fd-score-ring" :class="farmer.scoreNum >= 88 ? 'fd-score-ring--high' : 'fd-score-ring--mid'">{{ farmer.score }}</div>
                                </div>

                                <!-- Middle: Specs -->
                                <div class="row g-1 mb-2">
                                    <div class="col-6"><div class="fd-spec-cell"><span>Coffee Type</span><strong>{{ farmer.coffee_type || 'Arabica' }}</strong></div></div>
                                    <div class="col-6"><div class="fd-spec-cell"><span>Farms</span><strong>{{ farmer.estates }} farm(s)</strong></div></div>
                                    <div class="col-6"><div class="fd-spec-cell"><span>Buyer Rating</span><strong class="fd-up">{{ farmer.buyerRating }} ★</strong></div></div>
                                    <div class="col-6"><div class="fd-spec-cell"><span>Lots Available</span><strong>{{ farmer.lotsAvailable }}</strong></div></div>
                                </div>

                                <!-- Quality mini bars -->
                                <div class="fd-quality-bars mb-2">
                                    <div v-for="(attr, j) in ['Aroma','Flavor','Consistency']" :key="attr" class="fd-quality-bar-row">
                                        <span class="fd-quality-attr">{{ attr }}</span>
                                        <div class="fd-bar-track fd-bar-track--thin">
                                            <div class="fd-bar-fill" :style="{ width: `${80 + j * 4 + (farmer.id % 8)}%`, background: '#004532' }"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sustainability tags -->
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span v-for="s in farmer.sus" :key="s" class="fd-sus-chip">{{ s }}</span>
                                </div>

                                <!-- Trust indicators -->
                                <div class="fd-trust-row mb-3">
                                    <div class="fd-trust-item">
                                        <span>Traceability</span>
                                        <strong class="fd-up">{{ farmer.traceability }}%</strong>
                                    </div>
                                    <div class="fd-trust-item">
                                        <span>Buyer Rating</span>
                                        <strong class="fd-up">{{ farmer.buyerRating }} ★</strong>
                                    </div>
                                    <div class="fd-trust-item">
                                        <span>Export</span>
                                        <strong :class="farmer.exportReady ? 'fd-up' : 'fd-td-muted'">{{ farmer.exportReady ? 'Ready' : 'No' }}</strong>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex gap-1 mt-auto flex-wrap">
                                    <Link :href="route('farmer.show', farmer.id)" class="btn fd-btn-primary btn-sm flex-fill fd-act-btn">
                                        <el-icon><View /></el-icon> Profile
                                    </Link>
                                    <button class="btn fd-btn-outline btn-sm flex-fill fd-act-btn"><el-icon><Box /></el-icon> Lots</button>
                                    <button class="btn fd-btn-ghost btn-sm fd-act-btn px-2"><el-icon><ShoppingCart /></el-icon></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 8. Featured Farmers ─────────────────────────────── -->
                <div class="fd-card mb-3" v-if="featured.length">
                    <div class="fd-card-title mb-3">
                        <el-icon class="fd-card-icon"><Medal /></el-icon>
                        Featured Farmers
                    </div>
                    <div class="row g-3">
                        <div v-for="feat in featured" :key="feat.label" class="col-12 col-sm-6 col-lg-3">
                            <div class="fd-featured-card h-100">
                                <div class="fd-featured-label">{{ feat.label }}</div>
                                <div class="fd-featured-avatar">
                                    <el-icon class="fd-avatar-icon"><UserFilled /></el-icon>
                                    <span class="fd-avatar-initials">{{ feat.farmer.initials }}</span>
                                </div>
                                <div class="fd-featured-name">{{ feat.farmer.fullName }}</div>
                                <div class="fd-td-muted mb-2" style="font-size:.75rem;">{{ feat.farmer.location }}</div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="fd-score-pill fd-score-pill--high">{{ feat.farmer.score }}</span>
                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                        :class="feat.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                              : feat.tone === 'primary' ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'
                                              : feat.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                              : 'bg-info-subtle text-info-emphasis border border-info-subtle'">
                                        {{ feat.badge }}
                                    </span>
                                </div>
                                <Link :href="route('farmer.show', feat.farmer.id)" class="btn fd-btn-primary btn-sm w-100 fd-act-btn">
                                    <el-icon><View /></el-icon> View Farmer
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 7. Regional Map -->
                            <div class="fd-card">
                                <div class="fd-card-title mb-3">
                                    <el-icon class="fd-card-icon"><Location /></el-icon>
                                    Regional Production Map — Uganda
                                </div>
                                <div class="fd-region-grid">
                                    <div v-for="reg in regions" :key="reg.name" class="fd-region-card">
                                        <div class="fd-region-dot"></div>
                                        <div class="fd-region-name">{{ reg.name }}</div>
                                        <div class="fd-region-farmers">{{ reg.farmers }} farmers</div>
                                        <div class="row g-1 mt-2">
                                            <div class="col-6"><div class="fd-spec-cell"><span>Coffee</span><strong>{{ reg.type }}</strong></div></div>
                                            <div class="col-6"><div class="fd-spec-cell"><span>Quality</span><strong>{{ reg.quality }}</strong></div></div>
                                            <div class="col-12"><div class="fd-spec-cell"><span>Volume</span><strong>{{ reg.volume }}</strong></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Production & Quality Intelligence -->
                            <div class="fd-card">
                                <div class="fd-card-title mb-3">
                                    <el-icon class="fd-card-icon"><TrendCharts /></el-icon>
                                    Production &amp; Quality Intelligence
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="fd-td-muted mb-1" style="font-size:.75rem;">Production Trend</div>
                                        <div class="fd-chart">
                                            <div v-for="(v, i) in prodBars" :key="i" class="fd-chart-bar fd-chart-bar--prod" :style="{ height: `${v}%` }">
                                                <span v-if="i % 3 === 0" class="fd-chart-lbl">{{ ['J','','','A','','','J','','','O','',''][i] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="fd-td-muted mb-1" style="font-size:.75rem;">Quality Trend</div>
                                        <div class="fd-chart">
                                            <div v-for="(v, i) in qualBars" :key="i" class="fd-chart-bar fd-chart-bar--qual" :style="{ height: `${v}%` }">
                                                <span v-if="i % 3 === 0" class="fd-chart-lbl">{{ ['J','','','A','','','J','','','O','',''][i] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-6 col-md-3"><div class="fd-spec-cell"><span>Total Farmers</span><strong>{{ props.farmers.length }}</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fd-spec-cell"><span>Specialty 87+</span><strong class="fd-up">{{ specialtyCount }}</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fd-spec-cell"><span>Arabica Share</span><strong>68%</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fd-spec-cell"><span>Export Ready</span><strong class="fd-up">{{ totalExport }}</strong></div></div>
                                </div>
                            </div>

                            <!-- 10. Available Coffee from Farmers -->
                            <div class="fd-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="fd-card-title">
                                        <el-icon class="fd-card-icon"><ShoppingCart /></el-icon>
                                        Available Coffee from Farmers
                                    </div>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ filteredRows.slice(0,4).reduce((s,f) => s + f.lotsAvailable, 0) }} lots</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fd-table">
                                        <thead>
                                            <tr>
                                                <th><span class="fd-th"><el-icon><UserFilled /></el-icon> Farmer</span></th>
                                                <th><span class="fd-th"><el-icon><DataLine /></el-icon> Lots</span></th>
                                                <th><span class="fd-th"><el-icon><CollectionTag /></el-icon> Coffee Type</span></th>
                                                <th><span class="fd-th"><el-icon><Money /></el-icon> Price Range</span></th>
                                                <th><span class="fd-th"><el-icon><TrendCharts /></el-icon> Quality</span></th>
                                                <th><span class="fd-th"><el-icon><Opportunity /></el-icon> Demand</span></th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!filteredRows.length">
                                                <td colspan="7" class="text-center py-3 fd-td-muted">No farmers available.</td>
                                            </tr>
                                            <tr v-for="farmer in filteredRows.slice(0, 5)" :key="`lot-${farmer.id}`" class="fd-table-row">
                                                <td class="fd-item-name">{{ farmer.fullName }}</td>
                                                <td>
                                                    <span class="fd-lots-badge">{{ farmer.lotsAvailable }}</span>
                                                </td>
                                                <td><span class="fd-coffee-chip">{{ farmer.coffee_type || 'Arabica' }}</span></td>
                                                <td class="fw-semibold">{{ farmer.priceRange }}</td>
                                                <td>
                                                    <span class="fd-score-pill" :class="farmer.scoreNum >= 88 ? 'fd-score-pill--high' : 'fd-score-pill--mid'">{{ farmer.score }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="farmer.exportReady ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                        {{ farmer.exportReady ? 'High' : 'Medium' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm fd-btn-outline" style="font-size:.75rem;">View Lot</button>
                                                        <button class="btn btn-sm fd-btn-primary" style="font-size:.75rem;"><el-icon><ShoppingCart /></el-icon> Buy</button>
                                                    </div>
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
                        <div class="fd-rail">

                            <!-- 11. Sustainability & Compliance -->
                            <div class="fd-card">
                                <div class="fd-card-title mb-3">
                                    <el-icon class="fd-card-icon"><Checked /></el-icon>
                                    Sustainability &amp; Compliance
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="cert in certifications" :key="cert.label">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fd-cert-label">{{ cert.label }}</span>
                                            <span class="fd-up" style="font-size:.8125rem; font-weight:700;">{{ cert.pct }}%</span>
                                        </div>
                                        <div class="fd-bar-track">
                                            <div class="fd-bar-fill" :style="{ width: `${cert.pct}%`, background: cert.pct >= 70 ? '#004532' : '#f59e0b' }"></div>
                                        </div>
                                        <div class="fd-td-muted mt-1" style="font-size:.7rem;">{{ cert.done }} of {{ props.farmers.length || cert.done }} farmers</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Buyer Match Recommendations -->
                            <div class="fd-card">
                                <div class="fd-card-title mb-1">
                                    <el-icon class="fd-card-icon"><Star /></el-icon>
                                    Buyer Match
                                </div>
                                <div class="fd-td-muted mb-3" style="font-size:.75rem;">These buyers closely match available farmer profiles.</div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="bm in buyerMatches" :key="bm.buyer" class="fd-match-card">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="fd-item-name">{{ bm.buyer }}</span>
                                            <span class="fd-match-score fd-up">{{ bm.match }}% match</span>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-6"><div class="fd-spec-cell"><span>Quality</span><strong>{{ bm.quality }}</strong></div></div>
                                            <div class="col-6"><div class="fd-spec-cell"><span>Supply</span><strong>{{ bm.supply }}</strong></div></div>
                                            <div class="col-12"><div class="fd-spec-cell"><span>Price</span><strong>{{ bm.price }}</strong></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- AI Insights -->
                            <div>
                                <div class="fd-card-title mb-2">
                                    <el-icon class="fd-card-icon"><Opportunity /></el-icon>
                                    AI Intelligence
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="fd-insight-card" :class="`fd-insight-card--${ins.tone}`">
                                        <el-icon class="fd-insight-icon"><Opportunity /></el-icon>
                                        <p class="fd-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. Smart Alerts -->
                            <div class="fd-card">
                                <div class="fd-card-title mb-3">
                                    <el-icon class="fd-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="fd-alert-row">
                                        <span>{{ { newFarmer: 'New Farmer Added', highQuality: 'High Quality Harvest', priceChange: 'Price Changes', exportReady: 'Export-Ready Coffee', specialtyLots: 'New Specialty Lots' }[key] }}</span>
                                        <button class="fd-toggle" :class="{ 'fd-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom network stats -->
                            <div class="fd-card fd-card--green">
                                <div style="font-size:.875rem; font-weight:800; color:#fff; margin-bottom:6px;">Sourcing Network</div>
                                <p style="font-size:.8125rem; color:rgba(255,255,255,.8); margin-bottom:16px; line-height:1.5;">Access detailed farmer records for every verified coffee producer in the exchange.</p>
                                <div class="row g-2">
                                    <div class="col-6"><div class="fd-green-stat"><div class="fd-green-stat__val">{{ uniqueRegions.length - 1 || 1 }}</div><div class="fd-green-stat__lbl">Districts</div></div></div>
                                    <div class="col-6"><div class="fd-green-stat"><div class="fd-green-stat__val">{{ props.farmers.length }}</div><div class="fd-green-stat__lbl">Producers</div></div></div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 14. Floating Chatbot ───────────────────────────────── -->
            <div class="fd-fab-wrap">
                <Transition name="fd-chat">
                    <div v-if="chatOpen" class="fd-chatbot">
                        <div class="fd-chatbot__head">
                            <div class="fd-chatbot__identity">
                                <div class="fd-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="fd-chatbot__name">Farmer Advisor</div>
                                    <div class="fd-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="fd-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="fd-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="fd-chat-msg" :class="`fd-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="fd-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="fd-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="fd-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="fd-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.fd-page {
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
.fd-header   { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.fd-kicker   { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--green); margin-bottom: 4px; line-height: 1.4; }
.fd-title    { font-size: 1.375rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.25; }
.fd-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); margin-top: 2px; }
.fd-hbadge   { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fd-hbadge--soft { background: #dcfce7; color: #166534; }
.fd-hbadge--amber{ background: #fef3c7; color: #92400e; }
.fd-hbadge--blue { background: #dbeafe; color: #1e40af; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.fd-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.fd-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.fd-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fd-btn-outline:hover { background: var(--surface-low); }
.fd-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }

/* ── AI Banner ─────────────────────────────────────────────────────────────── */
.fd-ai-banner { background: linear-gradient(135deg, var(--green), var(--green-grad)); color: #fff; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.fd-ai-banner__left { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px; }
.fd-ai-banner__icon { width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.fd-ai-banner__text { font-size: 0.875rem; font-weight: 600; }
.fd-ai-stats { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.fd-ai-stat { display: flex; flex-direction: column; align-items: center; }
.fd-ai-stat span { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.75; }
.fd-ai-stat strong { font-size: 0.875rem; font-weight: 800; }
.fd-ai-stat--div { width: 1px; height: 24px; background: rgba(255,255,255,0.25); }

/* ── Filters ───────────────────────────────────────────────────────────────── */
.fd-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.fd-search-wrap  { position: relative; display: flex; align-items: center; }
.fd-search-icon  { position: absolute; left: 8px; font-size: 13px; color: var(--on-surface-var); pointer-events: none; }
.fd-search-input { height: 32px; border: 1px solid var(--surface-high); border-radius: 7px; padding: 0 10px 0 28px; font-size: 0.8125rem; outline: none; color: var(--on-surface); background: var(--surface-white); width: 180px; }
.fd-search-input:focus { border-color: var(--green); }
.fd-select { height: 32px; border: 1px solid var(--surface-high); border-radius: 7px; padding: 0 10px; font-size: 0.8125rem; color: var(--on-surface); background: var(--surface-white); outline: none; cursor: pointer; }
.fd-select:focus { border-color: var(--green); }

/* ── KPI ───────────────────────────────────────────────────────────────────── */
.fd-kpi { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.fd-kpi__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.fd-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.fd-kpi__value--success { color: var(--green); }
.fd-kpi__value--primary { color: #0369a1; }
.fd-kpi__value--warning { color: #92400e; }
.fd-kpi__sub { font-size: 0.625rem; color: var(--on-surface-var); }

/* ── View toggle ───────────────────────────────────────────────────────────── */
.fd-view-toggle { display: flex; gap: 3px; background: var(--surface-low); border-radius: 8px; padding: 3px; }
.fd-view-btn { font-size: 0.75rem; font-weight: 600; padding: 4px 12px; border-radius: 6px; border: none; background: transparent; color: var(--on-surface-var); cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.12s; }
.fd-view-btn:hover { background: var(--surface-mid); }
.fd-view-btn--active { background: var(--surface-white); color: var(--green); box-shadow: 0 1px 3px rgba(0,0,0,.08); font-weight: 700; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.fd-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.fd-card--green { background: linear-gradient(135deg, var(--green), var(--green-grad)); border-color: var(--green); }
.fd-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.fd-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.fd-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.fd-th { display: inline-flex; align-items: center; gap: 5px; }
.fd-th :deep(.el-icon) { font-size: 12px; color: #9ca3af; }
.fd-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.fd-table-row { transition: background 0.1s; }
.fd-table-row:hover { background: var(--surface-low); }
.fd-table-footer { display: flex; align-items: center; padding: 10px 16px; border-top: 1px solid var(--surface-low); background: var(--surface-low); }
.fd-item-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.fd-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; }
.fd-act-btn   { font-size: 0.75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.fd-up { color: #166534; font-weight: 700; }
.fd-warn { color: #92400e; font-weight: 700; }

/* ── Avatars ───────────────────────────────────────────────────────────────── */
.fd-avatar-sm { width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; font-weight: 800; font-size: 0.8125rem; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; gap: 0; }
.fd-avatar-lg { width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; font-weight: 800; font-size: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; gap: 0; }
.fd-avatar-icon     { font-size: 12px; line-height: 1; opacity: 0.7; }
.fd-avatar-sm .fd-avatar-icon { font-size: 11px; }
.fd-avatar-lg .fd-avatar-icon { font-size: 14px; }
.fd-avatar-initials { font-size: 0.5rem; font-weight: 800; line-height: 1; letter-spacing: 0.03em; margin-top: 1px; }

/* ── Score pills / rings ───────────────────────────────────────────────────── */
.fd-score-pill { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 800; padding: 2px 8px; }
.fd-score-pill--high { background: #dcfce7; color: #166534; }
.fd-score-pill--mid  { background: #fef3c7; color: #92400e; }
.fd-score-ring { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.6875rem; font-weight: 800; flex-shrink: 0; }
.fd-score-ring--high { background: #dcfce7; color: #166534; border: 2px solid #86efac; }
.fd-score-ring--mid  { background: #fef3c7; color: #92400e; border: 2px solid #fcd34d; }

/* ── Chips / badges ────────────────────────────────────────────────────────── */
.fd-coffee-chip { display: inline-flex; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.fd-sus-chip    { display: inline-flex; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 999px; font-size: 0.6rem; font-weight: 700; padding: 2px 7px; }
.fd-lots-badge  { display: inline-flex; width: 22px; height: 22px; border-radius: 50%; background: var(--green); color: #fff; font-size: 0.6875rem; font-weight: 800; align-items: center; justify-content: center; }
.fd-cert-label  { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Spec cell ─────────────────────────────────────────────────────────────── */
.fd-spec-cell { background: var(--surface-low); border-radius: 6px; padding: 5px 8px; }
.fd-spec-cell span   { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 1px; }
.fd-spec-cell strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Farmer card (grid) ────────────────────────────────────────────────────── */
.fd-farmer-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); display: flex; flex-direction: column; transition: box-shadow 0.15s, transform 0.15s; }
.fd-farmer-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.10); transform: translateY(-2px); border-color: rgba(0,69,50,0.2); }
.fd-farmer-name { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); line-height: 1.3; }

/* ── Quality bars (grid card) ──────────────────────────────────────────────── */
.fd-quality-bars { display: flex; flex-direction: column; gap: 3px; }
.fd-quality-bar-row { display: flex; align-items: center; gap: 6px; }
.fd-quality-attr { font-size: 0.6rem; font-weight: 700; color: var(--on-surface-var); width: 60px; flex-shrink: 0; }

/* ── Trust row ─────────────────────────────────────────────────────────────── */
.fd-trust-row { display: flex; gap: 0; border: 1px solid var(--surface-high); border-radius: 8px; overflow: hidden; }
.fd-trust-item { flex: 1; padding: 6px 8px; text-align: center; border-right: 1px solid var(--surface-high); }
.fd-trust-item:last-child { border-right: none; }
.fd-trust-item span { font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 1px; }
.fd-trust-item strong { font-size: 0.75rem; font-weight: 700; display: block; }

/* ── Featured ──────────────────────────────────────────────────────────────── */
.fd-featured-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; display: flex; flex-direction: column; }
.fd-featured-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--green); margin-bottom: 8px; }
.fd-featured-avatar { width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; font-weight: 800; font-size: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 8px; }
.fd-featured-avatar .fd-avatar-icon     { font-size: 14px; }
.fd-featured-avatar .fd-avatar-initials { font-size: 0.5rem; }
.fd-featured-name { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); margin-bottom: 2px; }

/* ── Regional cards ────────────────────────────────────────────────────────── */
.fd-region-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.fd-region-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; position: relative; }
.fd-region-dot  { width: 10px; height: 10px; border-radius: 50%; background: var(--green); margin-bottom: 6px; }
.fd-region-name { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); margin-bottom: 2px; }
.fd-region-farmers { font-size: 0.75rem; color: var(--on-surface-var); }

/* ── Charts ────────────────────────────────────────────────────────────────── */
.fd-chart { height: 80px; display: flex; align-items: flex-end; gap: 2px; background: var(--surface-low); border-radius: 8px; padding: 5px; }
.fd-chart-bar { flex: 1; border-radius: 2px 2px 0 0; position: relative; }
.fd-chart-bar--prod { background: var(--green); opacity: 0.8; }
.fd-chart-bar--qual { background: #059669; opacity: 0.8; }
.fd-chart-lbl { position: absolute; bottom: -14px; left: 50%; transform: translateX(-50%); font-size: 0.5rem; color: var(--on-surface-var); font-weight: 600; white-space: nowrap; }

/* ── Progress bars ─────────────────────────────────────────────────────────── */
.fd-bar-track { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.fd-bar-track--thin { height: 4px; }
.fd-bar-fill  { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* ── Match cards ───────────────────────────────────────────────────────────── */
.fd-match-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 8px; padding: 0.75rem; }
.fd-match-score { font-size: 0.8125rem; font-weight: 800; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.fd-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.fd-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.fd-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.fd-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.fd-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.fd-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.fd-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.fd-alert-row:last-child { border-bottom: none; }
.fd-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.fd-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.fd-toggle--on { background: var(--green); }
.fd-toggle--on i { transform: translateX(14px); }

/* ── Green stat card ───────────────────────────────────────────────────────── */
.fd-green-stat { background: rgba(255,255,255,0.12); border-radius: 8px; padding: 10px; text-align: center; }
.fd-green-stat__val { font-size: 1.25rem; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 2px; }
.fd-green-stat__lbl { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,.7); }

/* ── Empty state ───────────────────────────────────────────────────────────── */
.fd-empty-state { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 40px 20px; color: var(--on-surface-var); font-size: 0.875rem; background: var(--surface-low); border-radius: 12px; border: 1px solid var(--surface-high); }
.fd-empty-state__icon  { width: 48px; height: 48px; border-radius: 50%; background: var(--surface-white); border: 1px solid var(--surface-high); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; margin-bottom: 6px; }
.fd-empty-state__title { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.fd-empty-state__text  { font-size: 0.8125rem; color: var(--on-surface-var); max-width: 320px; margin: 0 auto 6px; line-height: 1.5; }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.fd-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.fd-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.fd-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.fd-fab:hover { background: var(--green-grad); }
.fd-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.fd-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.fd-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.fd-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.fd-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.fd-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.fd-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.fd-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.fd-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.fd-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.fd-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.fd-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.fd-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fd-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.fd-prompt-chip:hover { background: var(--surface-mid); }
.fd-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.fd-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.fd-chatbot__input input:focus { border-color: var(--green); }
.fd-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.fd-chat-enter-active, .fd-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fd-chat-enter-from, .fd-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .fd-rail { position: static; } }
@media (max-width: 767.98px)  {
    .fd-chatbot { width: calc(100vw - 3rem); max-width: 310px; }
    .fd-search-input { width: 140px; }
    .fd-region-grid { grid-template-columns: 1fr; }
}
</style>
