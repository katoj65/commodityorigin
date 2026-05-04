<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Box, Check, Checked, Collection, CollectionTag, Connection,
    Download, Filter, Location, Medal, Message, Opportunity,
    Plus, ShoppingCart, Star, Tickets, TrendCharts, Van, View,
    WarningFilled, User, ChatDotRound, Grid, List,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    markets: { type: Array, default: () => [] },
});

// ── Demand helper ────────────────────────────────────────────────────────────
const resolveDemandTone = (demand) => {
    const d = (demand ?? '').toLowerCase();
    if (d === 'very high') return 'primary';
    if (d === 'high')      return 'success';
    if (d === 'stable')    return 'warning';
    if (d === 'low')       return 'danger';
    return 'info';
};

// ── Normalised lots ──────────────────────────────────────────────────────────
const lots = computed(() =>
    props.markets.map((m) => ({
        id:             m.id,
        lot_code:       m.lot_code,
        name:           m.name || m.lot_code,
        origin:         m.origin || '—',
        type:           m.type,
        process:        m.process,
        qualityScore:   Number(m.quality_score || 0),
        quantity:       Number(m.quantity || 0),
        pricePerKg:     Number(m.price_per_kg || 0),
        demand:         m.demand || 'Active',
        demandTone:     resolveDemandTone(m.demand),
        badges:         m.badges || [],
        targetMarket:   m.target_market,
        image:          m.image,
        availableLabel: `${Number(m.quantity || 0).toLocaleString()} kg`,
    })),
);

// ── Filters ──────────────────────────────────────────────────────────────────
const filters = reactive({ origin: '', type: '', minQuality: '', maxPrice: '', exportReady: false, tokenised: false });

const uniqueOrigins  = computed(() => [...new Set(lots.value.map((l) => l.origin).filter(Boolean))]);
const uniqueTypes    = computed(() => [...new Set(lots.value.map((l) => l.type).filter(Boolean))]);

const filteredLots = computed(() => lots.value.filter((lot) => {
    if (filters.origin     && lot.origin !== filters.origin) return false;
    if (filters.type       && lot.type   !== filters.type)   return false;
    if (filters.minQuality && lot.qualityScore < Number(filters.minQuality)) return false;
    if (filters.maxPrice   && lot.pricePerKg   > Number(filters.maxPrice))   return false;
    if (filters.exportReady && !lot.badges.includes('Export Ready')) return false;
    if (filters.tokenised   && !lot.badges.includes('Tokenised'))    return false;
    return true;
}));

const hasActiveFilters = computed(() =>
    filters.origin || filters.type || filters.minQuality || filters.maxPrice || filters.exportReady || filters.tokenised,
);

const clearFilters = () => Object.assign(filters, { origin: '', type: '', minQuality: '', maxPrice: '', exportReady: false, tokenised: false });

// ── View / selection ─────────────────────────────────────────────────────────
const viewMode     = ref('grid');
const selectedLots = ref([]);
const toggleSelect = (id) => {
    const i = selectedLots.value.indexOf(id);
    i === -1 ? selectedLots.value.push(id) : selectedLots.value.splice(i, 1);
};
const isSelected = (id) => selectedLots.value.includes(id);
const clearSelection = () => { selectedLots.value = []; };

// ── Summary cards ────────────────────────────────────────────────────────────
const summaryCards = computed(() => {
    const d = lots.value;
    const avgPrice = d.length ? d.reduce((s, l) => s + l.pricePerKg, 0) / d.length : 0;
    return [
        { label: 'Total Lots',     value: d.length.toString().padStart(2, '0'), sub: 'Live on market',       icon: Box,          tone: '' },
        { label: 'Export-Ready',   value: d.filter((l) => l.badges.includes('Export Ready')).length,         sub: 'Verified for export',    icon: Van,          tone: 'success' },
        { label: 'Tokenised',      value: d.filter((l) => l.badges.includes('Tokenised')).length,            sub: 'On-chain assets',        icon: Connection,   tone: 'info' },
        { label: 'Avg. Price',     value: d.length ? `$${avgPrice.toFixed(2)}` : '—',                        sub: 'Per kg across lots',     icon: CollectionTag,tone: '' },
        { label: 'High Demand',    value: d.filter((l) => ['High', 'Very High'].includes(l.demand)).length,  sub: 'Active buyer interest',  icon: TrendCharts,  tone: 'warning' },
    ];
});

// ── Featured lots ────────────────────────────────────────────────────────────
const featuredLots = computed(() => {
    const d = lots.value;
    if (!d.length) return [];
    const demandOrder = { 'Very High': 0, 'High': 1, 'Active': 2, 'Stable': 3, 'Low': 4 };
    const bestValue  = [...d].sort((a, b) => a.pricePerKg   - b.pricePerKg)[0];
    const highDemand = [...d].sort((a, b) => (demandOrder[a.demand] ?? 5) - (demandOrder[b.demand] ?? 5))[0];
    const premium    = [...d].sort((a, b) => b.qualityScore - a.qualityScore)[0];
    return [
        { title: 'Best Value',      sub: 'Lowest price per kg',  lot: bestValue,  tone: 'mint' },
        { title: 'High Demand',     sub: 'Most active buyers',   lot: highDemand, tone: 'green' },
        { title: 'Premium Quality', sub: 'Top cup score',        lot: premium,    tone: 'amber' },
    ].filter((f) => f.lot);
});

// ── Trend chart ──────────────────────────────────────────────────────────────
const timeRange = ref('7D');
const trendBars = [
    { label: 'Mon', arabica: 68, robusta: 52 },
    { label: 'Tue', arabica: 72, robusta: 56 },
    { label: 'Wed', arabica: 76, robusta: 60 },
    { label: 'Thu', arabica: 71, robusta: 66 },
    { label: 'Fri', arabica: 82, robusta: 70 },
    { label: 'Sat', arabica: 79, robusta: 73 },
];

// ── Readiness ────────────────────────────────────────────────────────────────
const readiness = computed(() => {
    const d = lots.value;
    return [
        { label: 'Export Ready',          count: d.filter((l) => l.badges.includes('Export Ready')).length,  tone: 'success', icon: Check },
        { label: 'Tokenised',             count: d.filter((l) => l.badges.includes('Tokenised')).length,     tone: 'info',    icon: Checked },
        { label: 'Quality Verified',      count: d.filter((l) => l.qualityScore >= 85).length,               tone: 'success', icon: Star },
        { label: 'Pending Verification',  count: d.filter((l) => !l.badges.includes('Verified')).length,     tone: 'warning', icon: WarningFilled },
    ];
});

// ── Alerts ───────────────────────────────────────────────────────────────────
const alerts = reactive({ newLots: false, priceDrops: false, demandSpikes: false, exportReady: false });

// ── Chatbot ──────────────────────────────────────────────────────────────────
const chatOpen    = ref(false);
const chatInput   = ref('');
const chatMessages = ref([
    { role: 'assistant', text: 'Hi! I\'m the Bean Origin Lots Advisor. How can I help you find the right coffee lots?' },
]);
const suggestedPrompts = [
    'Which lots are best to buy?',
    'Which lots are export ready?',
    'Which lots have high demand?',
    'What quality should I look for?',
];
const sendChat = () => {
    const text = chatInput.value.trim();
    if (!text) return;
    chatMessages.value.push({ role: 'user', text });
    chatInput.value = '';
    setTimeout(() => {
        chatMessages.value.push({
            role: 'assistant',
            text: 'Based on current market data, I recommend export-ready Arabica lots with a quality score above 87 for the best trade opportunities.',
        });
    }, 800);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };

// ── Badge class ──────────────────────────────────────────────────────────────
const badgeClass = (badge) => {
    if (badge === 'Verified')     return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (badge === 'Export Ready') return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (badge === 'Tokenised')    return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    if (badge === 'Premium')      return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    return 'bg-light text-body-secondary border';
};
</script>

<template>
    <AppLayout title="Coffee Lots" full-width flush :show-banner="false">
        <Head title="Coffee Lots" />

        <div class="lots-page">

            <!-- ── 1. Header ─────────────────────────────────────────────── -->
            <div class="lots-header">
                <div class="container-fluid px-3 px-lg-4">

                    <!-- Title row -->
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 py-3 border-bottom">
                        <div>
                            <h1 class="lots-title mb-0">Coffee Lots</h1>
                            <p class="lots-subtitle mb-0">Browse verified, traceable, and export-ready coffee lots</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn lots-btn-primary btn-sm">
                                <el-icon><Plus /></el-icon> Create Lot
                            </button>
                            <button class="btn lots-btn-outline btn-sm">
                                <el-icon><Tickets /></el-icon> My Lots
                            </button>
                            <button class="btn lots-btn-outline btn-sm">
                                <el-icon><Download /></el-icon> Export Report
                            </button>
                            <button class="btn lots-btn-ghost btn-sm">
                                <el-icon><ChatDotRound /></el-icon> Ask Advisor
                            </button>
                        </div>
                    </div>

                    <!-- Filters row -->
                    <div class="d-flex flex-wrap align-items-center gap-2 py-2">
                        <el-icon class="text-muted"><Filter /></el-icon>
                        <select v-model="filters.origin" class="form-select form-select-sm lots-filter-select">
                            <option value="">All Origins</option>
                            <option v-for="o in uniqueOrigins" :key="o" :value="o">{{ o }}</option>
                        </select>
                        <select v-model="filters.type" class="form-select form-select-sm lots-filter-select">
                            <option value="">All Types</option>
                            <option v-for="t in uniqueTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                        <select v-model="filters.minQuality" class="form-select form-select-sm lots-filter-select">
                            <option value="">Quality Score</option>
                            <option value="80">80+</option>
                            <option value="84">84+</option>
                            <option value="87">87+</option>
                            <option value="90">90+</option>
                        </select>
                        <select v-model="filters.maxPrice" class="form-select form-select-sm lots-filter-select">
                            <option value="">Any Price</option>
                            <option value="3">Under $3/kg</option>
                            <option value="4">Under $4/kg</option>
                            <option value="5">Under $5/kg</option>
                        </select>
                        <div class="form-check form-check-inline mb-0">
                            <input v-model="filters.exportReady" id="f-export" class="form-check-input" type="checkbox">
                            <label for="f-export" class="form-check-label lots-filter-label">Export Ready</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input v-model="filters.tokenised" id="f-token" class="form-check-input" type="checkbox">
                            <label for="f-token" class="form-check-label lots-filter-label">Tokenised</label>
                        </div>
                        <button v-if="hasActiveFilters" class="btn btn-link btn-sm text-danger p-0" @click="clearFilters">
                            Clear filters
                        </button>
                        <span class="ms-auto text-muted" style="font-size:0.75rem;">
                            {{ filteredLots.length }} lot{{ filteredLots.length !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 pb-5">

                <!-- ── 2. Insight Strip ──────────────────────────────────── -->
                <div class="lots-insight-bar d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 my-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="lots-insight-icon">
                            <el-icon><Opportunity /></el-icon>
                        </div>
                        <div>
                            <div class="lots-insight-kicker">AI Market Insight</div>
                            <div class="lots-insight-text">Export-ready Robusta lots from Uganda are in high demand in UAE markets.</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                        <span class="badge lots-insight-badge">High Demand</span>
                        <span class="badge lots-insight-badge">Export Ready</span>
                        <span class="badge lots-insight-badge">Trade Opportunity</span>
                    </div>
                </div>

                <!-- ── 3. Summary Cards ──────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="card in summaryCards" :key="card.label" class="col-6 col-sm-4 col-lg">
                        <div class="lots-kpi-card h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="lots-kpi-label">{{ card.label }}</span>
                                <span class="lots-kpi-icon" :class="`lots-kpi-icon--${card.tone || 'default'}`">
                                    <el-icon><component :is="card.icon" /></el-icon>
                                </span>
                            </div>
                            <div class="lots-kpi-value">{{ card.value }}</div>
                            <div class="lots-kpi-sub">{{ card.sub }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── 4. View Toggle ────────────────────────────────────── -->
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="lots-section-label">
                            <el-icon><Tickets /></el-icon> All Lots
                        </span>
                        <span class="badge bg-light text-secondary border">{{ filteredLots.length }}</span>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button
                            class="btn"
                            :class="viewMode === 'grid' ? 'lots-btn-primary' : 'lots-btn-outline'"
                            @click="viewMode = 'grid'"
                        >
                            <el-icon><Grid /></el-icon> Grid
                        </button>
                        <button
                            class="btn"
                            :class="viewMode === 'table' ? 'lots-btn-primary' : 'lots-btn-outline'"
                            @click="viewMode = 'table'"
                        >
                            <el-icon><List /></el-icon> Table
                        </button>
                    </div>
                </div>

                <!-- ── 5. Grid View ──────────────────────────────────────── -->
                <div v-if="viewMode === 'grid'">
                    <div v-if="filteredLots.length" class="row g-3 mb-4">
                        <div v-for="lot in filteredLots" :key="lot.id" class="col-12 col-sm-6 col-xl-4">
                            <div
                                class="lots-grid-card h-100"
                                :class="{ 'lots-grid-card--selected': isSelected(lot.id) }"
                            >
                                <!-- Card header -->
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div>
                                        <div class="lots-grid-code">{{ lot.lot_code }}</div>
                                        <div class="lots-grid-name">{{ lot.name }}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <input
                                            type="checkbox"
                                            class="form-check-input mt-0"
                                            :checked="isSelected(lot.id)"
                                            @change="toggleSelect(lot.id)"
                                        >
                                        <button class="lots-watchlist-btn"><el-icon><Star /></el-icon></button>
                                    </div>
                                </div>

                                <!-- Badges -->
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span
                                        v-for="badge in lot.badges"
                                        :key="badge"
                                        class="badge rounded-pill lots-badge"
                                        :class="badgeClass(badge)"
                                    >{{ badge }}</span>
                                    <span class="badge rounded-pill" :class="`bg-${lot.demandTone}-subtle text-${lot.demandTone}-emphasis border border-${lot.demandTone}-subtle lots-badge`">
                                        {{ lot.demand }}
                                    </span>
                                </div>

                                <!-- Meta grid -->
                                <div class="row g-1 mb-2">
                                    <div class="col-6">
                                        <div class="lots-meta-cell">
                                            <span><el-icon><Location /></el-icon> Origin</span>
                                            <strong>{{ lot.origin }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="lots-meta-cell">
                                            <span><el-icon><Collection /></el-icon> Type</span>
                                            <strong>{{ lot.type }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="lots-meta-cell">
                                            <span><el-icon><Box /></el-icon> Quantity</span>
                                            <strong>{{ lot.availableLabel }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="lots-meta-cell">
                                            <span><el-icon><CollectionTag /></el-icon> Process</span>
                                            <strong>{{ lot.process }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quality + Price row -->
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <div class="lots-score-label">Quality Score</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lots-score-bar">
                                                <div class="lots-score-bar__fill" :style="{ width: `${Math.min(100, (lot.qualityScore / 100) * 100)}%` }"></div>
                                            </div>
                                            <span class="lots-score-val">{{ lot.qualityScore.toFixed(1) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="lots-score-label">Price / kg</div>
                                        <div class="lots-price-val">${{ lot.pricePerKg.toFixed(2) }}</div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm lots-btn-primary flex-fill">
                                        <el-icon><ShoppingCart /></el-icon> Buy
                                    </button>
                                    <button class="btn btn-sm lots-btn-outline flex-fill">
                                        <el-icon><View /></el-icon> View
                                    </button>
                                    <button class="btn btn-sm lots-btn-ghost">
                                        <el-icon><Medal /></el-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grid empty state -->
                    <div v-else class="lots-empty mb-4">
                        <el-icon class="lots-empty__icon"><Box /></el-icon>
                        <strong>No lots match your filters</strong>
                        <span>Try adjusting the filters above or <button class="btn btn-link p-0" @click="clearFilters">clear all</button></span>
                    </div>
                </div>

                <!-- ── 6. Table View ─────────────────────────────────────── -->
                <div v-else class="lots-table-card mb-4">
                    <table class="table align-middle mb-0 lots-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="form-check-input"></th>
                                <th>Lot</th>
                                <th>Origin</th>
                                <th>Type</th>
                                <th>Process</th>
                                <th>Quality</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Demand</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody v-if="filteredLots.length">
                            <tr v-for="lot in filteredLots" :key="lot.id" :class="{ 'lots-table__row--selected': isSelected(lot.id) }">
                                <td><input type="checkbox" class="form-check-input" :checked="isSelected(lot.id)" @change="toggleSelect(lot.id)"></td>
                                <td>
                                    <div class="fw-semibold" style="font-size:0.8125rem;">{{ lot.lot_code }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ lot.name }}</div>
                                </td>
                                <td style="font-size:0.8125rem;">{{ lot.origin }}</td>
                                <td><span class="badge bg-light text-dark border" style="font-size:0.7rem;">{{ lot.type }}</span></td>
                                <td style="font-size:0.8125rem;">{{ lot.process }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:0.7rem;">
                                        {{ lot.qualityScore.toFixed(1) }}
                                    </span>
                                </td>
                                <td style="font-size:0.8125rem;">{{ lot.availableLabel }}</td>
                                <td class="fw-semibold" style="font-size:0.8125rem;">${{ lot.pricePerKg.toFixed(2) }}/kg</td>
                                <td>
                                    <span class="badge rounded-pill" :class="`bg-${lot.demandTone}-subtle text-${lot.demandTone}-emphasis border border-${lot.demandTone}-subtle`" style="font-size:0.7rem;">
                                        {{ lot.demand }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span v-for="badge in lot.badges" :key="badge" class="badge rounded-pill lots-badge" :class="badgeClass(badge)">{{ badge }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="btn btn-sm lots-btn-primary px-2"><el-icon><View /></el-icon></button>
                                        <button class="btn btn-sm lots-btn-outline px-2"><el-icon><ShoppingCart /></el-icon></button>
                                        <button class="btn btn-sm lots-btn-ghost px-2"><el-icon><Medal /></el-icon></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr><td colspan="11" class="text-center py-5 text-muted" style="font-size:0.875rem;">No lots match your current filters.</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- ── 7. Featured Lots ──────────────────────────────────── -->
                <div v-if="featuredLots.length" class="mb-4">
                    <div class="lots-section-heading mb-3">
                        <el-icon><Star /></el-icon>
                        <span>Featured Lots</span>
                    </div>
                    <div class="row g-3">
                        <div v-for="f in featuredLots" :key="f.title" class="col-12 col-md-4">
                            <div class="lots-featured-card" :class="`lots-featured-card--${f.tone}`">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div>
                                        <div class="lots-featured-label">{{ f.title }}</div>
                                        <div class="lots-featured-name">{{ f.lot.name }}</div>
                                    </div>
                                    <span class="lots-featured-score">{{ f.lot.qualityScore.toFixed(1) }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-3" style="font-size:0.8125rem;color:#6b7280;">
                                    <el-icon><Location /></el-icon>
                                    <span>{{ f.lot.origin }}</span>
                                    <span>·</span>
                                    <span>${{ f.lot.pricePerKg.toFixed(2) }}/kg</span>
                                    <span>·</span>
                                    <span>{{ f.lot.process }}</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm lots-btn-primary flex-fill">View Lot</button>
                                    <button class="btn btn-sm lots-btn-outline flex-fill"><el-icon><ShoppingCart /></el-icon> Buy</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 8–11. Bottom row ──────────────────────────────────── -->
                <div class="row g-3 mb-4">

                    <!-- 8. Price & Demand Trends -->
                    <div class="col-12 col-lg-5">
                        <div class="lots-card h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="lots-section-heading">
                                    <el-icon><TrendCharts /></el-icon>
                                    <span>Price &amp; Demand Trends</span>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button v-for="r in ['7D','30D','90D']" :key="r" class="btn" :class="timeRange === r ? 'lots-btn-primary' : 'lots-btn-outline'" @click="timeRange = r">{{ r }}</button>
                                </div>
                            </div>
                            <div class="lots-chart">
                                <div v-for="bar in trendBars" :key="bar.label" class="lots-chart-col">
                                    <div class="lots-chart-bars">
                                        <div class="lots-chart-bar lots-chart-bar--arabica" :style="{ height: `${bar.arabica}%` }"></div>
                                        <div class="lots-chart-bar lots-chart-bar--robusta" :style="{ height: `${bar.robusta}%` }"></div>
                                    </div>
                                    <span class="lots-chart-label">{{ bar.label }}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-3 mt-2">
                                <span class="lots-legend"><span class="lots-legend-dot lots-legend-dot--arabica"></span>Arabica</span>
                                <span class="lots-legend"><span class="lots-legend-dot lots-legend-dot--robusta"></span>Robusta</span>
                            </div>
                        </div>
                    </div>

                    <!-- 9. Lot Readiness Indicator -->
                    <div class="col-12 col-lg-4">
                        <div class="lots-card h-100">
                            <div class="lots-section-heading mb-3">
                                <el-icon><Check /></el-icon>
                                <span>Lot Readiness</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div v-for="item in readiness" :key="item.label" class="lots-readiness-row">
                                    <div class="d-flex align-items-center gap-2">
                                        <el-icon :class="`text-${item.tone}`"><component :is="item.icon" /></el-icon>
                                        <span>{{ item.label }}</span>
                                    </div>
                                    <span class="badge rounded-pill" :class="`bg-${item.tone}-subtle text-${item.tone}-emphasis border border-${item.tone}-subtle`">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 10 & 12. Seller Preview + Alerts -->
                    <div class="col-12 col-lg-3 d-flex flex-column gap-3">

                        <!-- Seller Preview -->
                        <div class="lots-card">
                            <div class="lots-section-heading mb-3">
                                <el-icon><User /></el-icon>
                                <span>Seller Preview</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="lots-seller-avatar"><el-icon><User /></el-icon></div>
                                <div>
                                    <div class="lots-seller-name">Bean Origin Co-op</div>
                                    <div class="lots-seller-meta">Uganda · <span class="text-success">Verified</span></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm lots-btn-outline flex-fill"><el-icon><Message /></el-icon> Contact</button>
                                <button class="btn btn-sm lots-btn-ghost flex-fill"><el-icon><View /></el-icon> Profile</button>
                            </div>
                        </div>

                        <!-- Smart Alerts -->
                        <div class="lots-card flex-fill">
                            <div class="lots-section-heading mb-3">
                                <el-icon><TrendCharts /></el-icon>
                                <span>Smart Alerts</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div v-for="(key, label) in { 'New Lots': 'newLots', 'Price Drops': 'priceDrops', 'Demand Spikes': 'demandSpikes', 'Export Ready': 'exportReady' }" :key="key" class="lots-alert-row">
                                    <span>{{ key }}</span>
                                    <el-switch v-model="alerts[label]" size="small" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div><!-- /container -->

            <!-- ── 11. Bulk Actions Bar ──────────────────────────────────── -->
            <Transition name="lots-bulk-slide">
                <div v-if="selectedLots.length" class="lots-bulk-bar">
                    <span class="lots-bulk-count">{{ selectedLots.length }} lot{{ selectedLots.length > 1 ? 's' : '' }} selected</span>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm lots-btn-primary"><el-icon><ShoppingCart /></el-icon> Bulk Buy</button>
                        <button class="btn btn-sm lots-btn-outline"><el-icon><View /></el-icon> Compare</button>
                        <button class="btn btn-sm lots-btn-outline"><el-icon><Download /></el-icon> Export</button>
                        <button class="btn btn-sm lots-btn-ghost" @click="clearSelection">Clear</button>
                    </div>
                </div>
            </Transition>

            <!-- ── 13. Floating Chatbot ──────────────────────────────────── -->
            <div class="lots-fab-wrap">
                <Transition name="lots-chat-slide">
                    <div v-if="chatOpen" class="lots-chatbot">
                        <div class="lots-chatbot__header">
                            <div>
                                <div class="lots-chatbot__title">Lots Advisor</div>
                                <div class="lots-chatbot__sub">Bean Origin AI</div>
                            </div>
                            <button class="lots-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="lots-chatbot__body">
                            <div v-for="(msg, i) in chatMessages" :key="i" class="lots-chat-msg" :class="`lots-chat-msg--${msg.role}`">
                                {{ msg.text }}
                            </div>
                        </div>
                        <div class="lots-chatbot__prompts">
                            <button v-for="p in suggestedPrompts" :key="p" class="lots-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="lots-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask about lots…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Message /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="lots-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Base ───────────────────────────────────────────────────────────────────── */
.lots-page {
    background: #ffffff;
    min-height: 100%;
    font-family: 'Manrope', system-ui, sans-serif;
    color: #111827;
}

/* ── Header ─────────────────────────────────────────────────────────────────── */
.lots-header {
    background: #ffffff;
    border-bottom: 1px solid #e9eef3;
}
.lots-title {
    font-size: 1.125rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #111827;
}
.lots-subtitle { font-size: 0.8125rem; color: #6b7280; }
.lots-filter-select {
    width: auto;
    min-width: 120px;
    border-radius: 6px;
    border-color: #e5e7eb;
    font-size: 0.8125rem;
    height: 32px;
    padding-top: 0;
    padding-bottom: 0;
}
.lots-filter-label { font-size: 0.8125rem; color: #374151; }

/* ── Buttons ────────────────────────────────────────────────────────────────── */
.lots-btn-primary {
    background: #004532;
    border-color: #004532;
    color: #fff;
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.lots-btn-primary:hover { background: #065f46; border-color: #065f46; color: #fff; }
.lots-btn-outline {
    background: #fff;
    border-color: #d1d5db;
    color: #374151;
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.lots-btn-outline:hover { background: #f9fafb; }
.lots-btn-ghost {
    background: #f3f4f6;
    border-color: transparent;
    color: #374151;
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.lots-btn-ghost:hover { background: #e5e7eb; }

/* ── Insight Strip ──────────────────────────────────────────────────────────── */
.lots-insight-bar {
    padding: 0.875rem 1.1rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #004532 0%, #065f46 100%);
    color: #fff;
}
.lots-insight-icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: rgba(255,255,255,0.14);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.lots-insight-kicker { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.1em; opacity: 0.7; text-transform: uppercase; }
.lots-insight-text   { font-size: 0.875rem; font-weight: 600; line-height: 1.4; }
.lots-insight-badge  { background: rgba(255,255,255,0.14); color: #fff; font-size: 0.6875rem; padding: 5px 10px; border-radius: 999px; }

/* ── KPI Cards ──────────────────────────────────────────────────────────────── */
.lots-kpi-card {
    background: #fff;
    border: 1px solid #e9eef3;
    border-radius: 10px;
    padding: 0.875rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.lots-kpi-label { font-size: 0.6875rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em; }
.lots-kpi-value { font-size: 1.375rem; font-weight: 800; letter-spacing: -0.03em; color: #111827; line-height: 1; margin: 4px 0 2px; }
.lots-kpi-sub   { font-size: 0.6875rem; color: #9ca3af; }
.lots-kpi-icon  {
    width: 30px; height: 30px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
}
.lots-kpi-icon--default { background: #f3f4f6; color: #374151; }
.lots-kpi-icon--success { background: #dcfce7; color: #15803d; }
.lots-kpi-icon--info    { background: #dbeafe; color: #1d4ed8; }
.lots-kpi-icon--warning { background: #fef9c3; color: #a16207; }

/* ── Section headings ───────────────────────────────────────────────────────── */
.lots-section-heading {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 0.9375rem; font-weight: 700; color: #111827;
}
.lots-section-label {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.875rem; font-weight: 700; color: #111827;
}

/* ── Shared card ────────────────────────────────────────────────────────────── */
.lots-card {
    background: #fff;
    border: 1px solid #e9eef3;
    border-radius: 12px;
    padding: 1.1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}

/* ── Grid cards ─────────────────────────────────────────────────────────────── */
.lots-grid-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
}
.lots-grid-card:hover         { box-shadow: 0 4px 12px rgba(0,69,50,0.08); border-color: #a7f3d0; }
.lots-grid-card--selected     { border-color: #004532; background: #f0fdf4; }

.lots-grid-code  { font-size: 0.6875rem; font-weight: 700; color: #9ca3af; }
.lots-grid-name  { font-size: 0.9375rem; font-weight: 700; color: #111827; line-height: 1.3; }

.lots-badge { font-size: 0.6rem; padding: 2px 8px; }

.lots-meta-cell {
    background: #f9fafb; border: 1px solid #f3f4f6; border-radius: 7px;
    padding: 6px 8px; display: flex; flex-direction: column; gap: 1px;
}
.lots-meta-cell span { font-size: 0.625rem; color: #9ca3af; display: flex; align-items: center; gap: 4px; }
.lots-meta-cell strong { font-size: 0.8125rem; color: #111827; font-weight: 600; }

.lots-score-label { font-size: 0.625rem; color: #9ca3af; font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
.lots-score-bar   { width: 80px; height: 5px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
.lots-score-bar__fill { height: 100%; background: #004532; border-radius: 999px; }
.lots-score-val   { font-size: 0.8125rem; font-weight: 700; color: #004532; }
.lots-price-val   { font-size: 1rem; font-weight: 800; color: #111827; }

.lots-watchlist-btn {
    border: none; background: none; padding: 2px; color: #d1d5db; font-size: 14px; cursor: pointer;
    line-height: 1;
}
.lots-watchlist-btn:hover { color: #f59e0b; }

/* ── Table ──────────────────────────────────────────────────────────────────── */
.lots-table-card {
    border: 1px solid #e9eef3;
    border-radius: 12px;
    overflow: hidden;
    overflow-x: auto;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.lots-table thead th {
    background: #f8fafc;
    border-bottom-color: #e9eef3;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #9ca3af;
    padding: 10px 12px;
    white-space: nowrap;
}
.lots-table tbody td { padding: 10px 12px; border-color: #f3f4f6; vertical-align: middle; }
.lots-table tbody tr { transition: background 0.1s; }
.lots-table tbody tr:hover { background: #f9fafb; }
.lots-table__row--selected { background: #f0fdf4 !important; }

/* ── Featured Lots ──────────────────────────────────────────────────────────── */
.lots-featured-card {
    border-radius: 12px;
    padding: 1.1rem;
    border: 1px solid #e5e7eb;
}
.lots-featured-card--mint  { background: linear-gradient(160deg, #f0fdf4, #ffffff); }
.lots-featured-card--green { background: linear-gradient(160deg, #f0fdf9, #ffffff); }
.lots-featured-card--amber { background: linear-gradient(160deg, #fffbeb, #ffffff); }

.lots-featured-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; }
.lots-featured-name  { font-size: 1rem; font-weight: 700; color: #111827; }
.lots-featured-score {
    min-width: 42px; height: 42px; border-radius: 10px;
    background: rgba(255,255,255,0.9); border: 1px solid #e5e7eb;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.9375rem; font-weight: 800; color: #111827; flex-shrink: 0;
}

/* ── Trend Chart ────────────────────────────────────────────────────────────── */
.lots-chart {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 6px;
    align-items: end;
    height: 110px;
    background: #f8fafc;
    border: 1px solid #e9eef3;
    border-radius: 8px;
    padding: 8px;
}
.lots-chart-col    { display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end; }
.lots-chart-bars   { display: flex; gap: 2px; align-items: flex-end; height: 80px; }
.lots-chart-bar    { width: 8px; border-radius: 3px 3px 0 0; }
.lots-chart-bar--arabica { background: #004532; }
.lots-chart-bar--robusta { background: #a7f3d0; }
.lots-chart-label  { font-size: 0.625rem; color: #9ca3af; font-weight: 600; }
.lots-legend       { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; color: #6b7280; }
.lots-legend-dot   { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.lots-legend-dot--arabica { background: #004532; }
.lots-legend-dot--robusta { background: #a7f3d0; }

/* ── Readiness ──────────────────────────────────────────────────────────────── */
.lots-readiness-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 10px; background: #f9fafb; border-radius: 8px;
    font-size: 0.8125rem; color: #374151;
}

/* ── Seller ─────────────────────────────────────────────────────────────────── */
.lots-seller-avatar {
    width: 40px; height: 40px; border-radius: 999px;
    background: linear-gradient(135deg, #004532, #065f46);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.lots-seller-name { font-size: 0.9375rem; font-weight: 700; color: #111827; }
.lots-seller-meta { font-size: 0.75rem; color: #6b7280; }

/* ── Smart Alerts ───────────────────────────────────────────────────────────── */
.lots-alert-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 7px 0; border-bottom: 1px solid #f3f4f6;
    font-size: 0.8125rem; color: #374151;
}
.lots-alert-row:last-child { border-bottom: none; }

/* ── Bulk Actions Bar ───────────────────────────────────────────────────────── */
.lots-bulk-bar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 200;
    background: #111827; color: #fff;
    display: flex; align-items: center; justify-content: center;
    gap: 1rem; padding: 0.75rem 1.5rem;
    flex-wrap: wrap;
}
.lots-bulk-count { font-size: 0.875rem; font-weight: 600; }
.lots-bulk-slide-enter-active,
.lots-bulk-slide-leave-active  { transition: transform 0.25s ease; }
.lots-bulk-slide-enter-from,
.lots-bulk-slide-leave-to      { transform: translateY(100%); }

/* ── Chatbot ────────────────────────────────────────────────────────────────── */
.lots-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.lots-fab {
    width: 50px; height: 50px; border-radius: 999px; border: none;
    background: #004532; color: #fff; font-size: 20px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer;
    transition: background 0.15s ease;
}
.lots-fab:hover { background: #065f46; }

.lots-chatbot {
    width: 320px; border-radius: 16px; overflow: hidden;
    background: #fff; border: 1px solid #e5e7eb;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    display: flex; flex-direction: column;
}
.lots-chatbot__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px; background: #004532; color: #fff;
}
.lots-chatbot__title { font-size: 0.9375rem; font-weight: 700; }
.lots-chatbot__sub   { font-size: 0.6875rem; opacity: 0.7; }
.lots-chatbot__close { border: none; background: none; color: #fff; font-size: 20px; line-height: 1; cursor: pointer; }

.lots-chatbot__body {
    padding: 12px; max-height: 220px; overflow-y: auto;
    display: flex; flex-direction: column; gap: 8px;
}
.lots-chat-msg { font-size: 0.8125rem; padding: 8px 12px; border-radius: 10px; line-height: 1.5; max-width: 85%; }
.lots-chat-msg--assistant { background: #f3f4f6; color: #111827; align-self: flex-start; }
.lots-chat-msg--user      { background: #004532; color: #fff; align-self: flex-end; }

.lots-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 12px; border-top: 1px solid #f3f4f6; }
.lots-prompt-chip {
    font-size: 0.6875rem; padding: 4px 10px; border-radius: 999px;
    background: #f3f4f6; border: 1px solid #e5e7eb; color: #374151;
    cursor: pointer; white-space: nowrap;
}
.lots-prompt-chip:hover { background: #e5e7eb; }

.lots-chatbot__input {
    display: flex; gap: 6px; padding: 10px 12px; border-top: 1px solid #f3f4f6;
}
.lots-chatbot__input input {
    flex: 1; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 7px 10px; font-size: 0.8125rem; outline: none;
}
.lots-chatbot__input input:focus { border-color: #004532; }
.lots-chatbot__input button {
    border: none; background: #004532; color: #fff; border-radius: 8px;
    width: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.lots-chat-slide-enter-active,
.lots-chat-slide-leave-active  { transition: opacity 0.2s ease, transform 0.2s ease; }
.lots-chat-slide-enter-from,
.lots-chat-slide-leave-to      { opacity: 0; transform: translateY(10px); }

/* ── Empty State ────────────────────────────────────────────────────────────── */
.lots-empty {
    padding: 3rem 2rem; text-align: center;
    border: 1px dashed #e5e7eb; border-radius: 12px; background: #fafafa;
}
.lots-empty__icon { font-size: 2.5rem; color: #d1d5db; display: block; margin: 0 auto 0.75rem; }
.lots-empty strong { display: block; font-size: 0.9375rem; color: #374151; margin-bottom: 6px; }
.lots-empty span   { font-size: 0.8125rem; color: #9ca3af; }

/* ── Element Plus overrides ─────────────────────────────────────────────────── */
:deep(.el-switch) { --el-switch-on-color: #004532; }
</style>
