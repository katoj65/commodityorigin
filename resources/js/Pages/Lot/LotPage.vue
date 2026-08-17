<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Box, Check, CollectionTag, DataLine, Download,
    Location, Opportunity, Promotion, ShoppingCart,
    Star, Tickets, TrendCharts, Van, Warning, ChatDotRound,
} from '@element-plus/icons-vue';

/* ── Props ──────────────────────────────────────────────────── */
const props = defineProps({
    lots:    { type: Object, default: () => ({ data: [], meta: { total: 0, current_page: 1, last_page: 1, from: 0, to: 0 } }) },
    filters: { type: Object, default: () => ({}) },
});

/* ── View toggle ────────────────────────────────────────────── */
const viewMode = ref('table');

/* ── Search / filter state ──────────────────────────────────── */
const search       = ref('');
const filterType   = ref('');
const filterOrigin = ref('');
const filterSeason = ref('');
const filterStatus = ref('');

/* ── Pagination helpers ─────────────────────────────────────── */
const totalLots = computed(() => props.lots.meta?.total ?? mockLots.length);
const visiblePages = computed(() => {
    const cur  = props.lots.meta?.current_page ?? 1;
    const last = props.lots.meta?.last_page    ?? 1;
    const pages = [];
    for (let p = Math.max(1, cur - 1); p <= Math.min(last, cur + 1); p++) pages.push(p);
    return pages;
});

/* ── Mock data ──────────────────────────────────────────────── */
const mockLots = [
    {
        id: 1, lot_number: 'LOT-SIP-001', lot_name: 'Sipi Falls Natural',
        type: 'Arabica', origin: 'Mt. Elgon, Uganda', season: '2024 / A',
        quality_score: 89.4, moisture: 11.2, quantity: 2400,
        price: 4.80, demand: 'High', buyer_interest: 18,
        sustainability: 88,
        verified: true, traceable: true, export_ready: true, tokenised: false,
        status: 'available',
        image: null,
    },
    {
        id: 2, lot_number: 'LOT-ELG-007', lot_name: 'Elgon Highland Washed',
        type: 'Arabica', origin: 'Kapchorwa, Uganda', season: '2024 / A',
        quality_score: 87.2, moisture: 12.0, quantity: 1800,
        price: 4.30, demand: 'Medium', buyer_interest: 11,
        sustainability: 82,
        verified: true, traceable: true, export_ready: false, tokenised: true,
        status: 'available',
        image: null,
    },
    {
        id: 3, lot_number: 'LOT-RWZ-003', lot_name: 'Rwenzori Honey Select',
        type: 'Arabica', origin: 'Kasese, Uganda', season: '2023 / B',
        quality_score: 86.8, moisture: 11.6, quantity: 3200,
        price: 4.10, demand: 'High', buyer_interest: 22,
        sustainability: 91,
        verified: true, traceable: false, export_ready: true, tokenised: false,
        status: 'in_auction',
        image: null,
    },
    {
        id: 4, lot_number: 'LOT-WNL-012', lot_name: 'West Nile Natural Grade A',
        type: 'Robusta', origin: 'Arua, Uganda', season: '2024 / A',
        quality_score: 82.5, moisture: 13.1, quantity: 4800,
        price: 2.90, demand: 'Medium', buyer_interest: 7,
        sustainability: 74,
        verified: false, traceable: true, export_ready: false, tokenised: false,
        status: 'draft',
        image: null,
    },
    {
        id: 5, lot_number: 'LOT-BUG-019', lot_name: 'Bugisu AA Washed',
        type: 'Arabica', origin: 'Mbale, Uganda', season: '2024 / A',
        quality_score: 88.1, moisture: 11.8, quantity: 1600,
        price: 4.60, demand: 'High', buyer_interest: 15,
        sustainability: 85,
        verified: true, traceable: true, export_ready: true, tokenised: true,
        status: 'available',
        image: null,
    },
    {
        id: 6, lot_number: 'LOT-SIP-022', lot_name: 'Sipi Bourbon Natural',
        type: 'Arabica', origin: 'Mt. Elgon, Uganda', season: '2023 / B',
        quality_score: 90.2, moisture: 10.9, quantity: 960,
        price: 5.20, demand: 'High', buyer_interest: 31,
        sustainability: 94,
        verified: true, traceable: true, export_ready: true, tokenised: false,
        status: 'available',
        image: null,
    },
];

const displayLots = computed(() =>
    props.lots.data.length ? props.lots.data : mockLots,
);

const featuredLots = computed(() =>
    displayLots.value.filter(l => l.quality_score >= 88).slice(0, 5),
);

const recentLots = computed(() =>
    [...displayLots.value].slice(0, 4),
);

/* ── Helpers ────────────────────────────────────────────────── */
const statusLabel = (s) => ({
    available:  'Available',
    in_auction: 'In Auction',
    sold:       'Sold',
    draft:      'Draft',
}[s] ?? s);

const statusCls = (s) => ({
    available:  'lt-badge--green',
    in_auction: 'lt-badge--blue',
    sold:       'lt-badge--muted',
    draft:      'lt-badge--yellow',
}[s] ?? 'lt-badge--muted');

const demandCls = (d) => ({
    High:   'lt-badge--green',
    Medium: 'lt-badge--yellow',
    Low:    'lt-badge--muted',
}[d] ?? 'lt-badge--muted');

const qualityColour = (q) => q >= 88 ? '#166534' : q >= 85 ? '#1d4ed8' : '#92400e';

/* ── Quick Buy ───────────────────────────────────────────────── */
const quickBuyLotId  = ref(1);
const quickBuyQty    = ref('');
const quickBuyBidPx  = ref('');
const quickBuyLot    = computed(() => displayLots.value.find(l => l.id === quickBuyLotId.value) ?? displayLots.value[0]);

/* ── AI Insights ─────────────────────────────────────────────── */
const insights = [
    { text: 'Sipi Bourbon Natural scores 90.2 — premium specialty market candidate.', tone: 'success' },
    { text: 'Export-ready lots up 18% this season — strong buyer demand ahead of Q3.', tone: 'primary' },
    { text: 'Moisture on LOT-WNL-012 is slightly elevated. Consider pre-export drying.', tone: 'warning' },
];

/* ── Market intelligence ─────────────────────────────────────── */
const marketDemand = [
    { label: 'Arabica Demand', pct: 82, colour: '#166534' },
    { label: 'Robusta Demand', pct: 54, colour: '#1d4ed8' },
];
const priceTrends = [
    { label: 'Rising',  count: 3, icon: '↑', cls: 'lt-badge--green' },
    { label: 'Stable',  count: 2, icon: '→', cls: 'lt-badge--blue' },
    { label: 'Falling', count: 1, icon: '↓', cls: 'lt-badge--muted' },
];
const buyerActivity = [
    { label: 'High',   pct: 67 },
    { label: 'Medium', pct: 25 },
    { label: 'Low',    pct: 8 },
];

/* ── Chatbot ────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Lot Advisor. I can help you evaluate quality, find buyers, and manage your coffee lots. What would you like to know?" },
]);
const chatPrompts = [
    'Which lots have the highest quality?',
    'Which lots are export-ready?',
    'Which origins show strongest demand?',
    'What price should I list for?',
];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'LOT-SIP-022 (Sipi Bourbon Natural) leads with 90.2 SCA — ideal for specialty roasters. You currently have 4 export-ready lots and 2 active auctions with strong buyer signals.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <AppLayout title="Lots" full-width flush :show-banner="false">
        <Head title="Lots" />

        <div class="lt-page">

            <!-- ── 1. Header ─────────────────────────────────────────────── -->
            <div class="lt-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="lt-kicker">Lot Management</div>
                            <h1 class="lt-title mb-0">Lots</h1>
                            <p class="lt-subtitle mb-0">Manage, monitor, and trade coffee lots across the marketplace.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button class="btn lt-btn-outline btn-sm">
                                <el-icon><Download /></el-icon> Export Lots
                            </button>
                            <Link :href="route('seller.sell-coffee')" class="btn lt-btn-outline btn-sm">
                                <el-icon><Van /></el-icon> Sell Coffee
                            </Link>
                            <Link :href="route('batch.index')" class="btn lt-btn-primary btn-sm">
                                <el-icon><Box /></el-icon> Create Lot
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. KPI Strip ──────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="lt-kpi h-100">
                            <span class="lt-kpi__label">Total Lots</span>
                            <div class="lt-kpi__value">{{ totalLots || mockLots.length }}</div>
                            <div class="lt-kpi__sub">All records</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="lt-kpi h-100">
                            <span class="lt-kpi__label">Available</span>
                            <div class="lt-kpi__value lt-green">{{ displayLots.filter(l => l.status === 'available').length }}</div>
                            <div class="lt-kpi__sub">Ready for sale</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="lt-kpi h-100">
                            <span class="lt-kpi__label">Active Auctions</span>
                            <div class="lt-kpi__value lt-blue">{{ displayLots.filter(l => l.status === 'in_auction').length }}</div>
                            <div class="lt-kpi__sub">Live bidding</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="lt-kpi h-100">
                            <span class="lt-kpi__label">Total Quantity</span>
                            <div class="lt-kpi__value lt-green">{{ displayLots.reduce((a, l) => a + (l.quantity || 0), 0).toLocaleString() }}</div>
                            <div class="lt-kpi__sub">kg across all lots</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="lt-kpi h-100">
                            <span class="lt-kpi__label">Avg Quality Score</span>
                            <div class="lt-kpi__value lt-green">
                                {{ displayLots.length ? (displayLots.reduce((a, l) => a + (l.quality_score || 0), 0) / displayLots.length).toFixed(1) : '—' }}
                            </div>
                            <div class="lt-kpi__sub">SCA average</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-lg-2">
                        <div class="lt-kpi h-100">
                            <span class="lt-kpi__label">Export Ready</span>
                            <div class="lt-kpi__value">{{ displayLots.filter(l => l.export_ready).length }}</div>
                            <div class="lt-kpi__sub">Cleared for export</div>
                        </div>
                    </div>
                </div>

                <!-- ── 3. Filters ────────────────────────────────────────── -->
                <div class="lt-filters mb-3">
                    <div class="lt-search-wrap">
                        <el-icon class="lt-search-icon"><Star /></el-icon>
                        <input v-model="search" class="lt-search-input" placeholder="Search lot ID, name…">
                    </div>
                    <select v-model="filterType" class="lt-select">
                        <option value="">All Coffee Types</option>
                        <option>Arabica</option>
                        <option>Robusta</option>
                    </select>
                    <select v-model="filterOrigin" class="lt-select">
                        <option value="">All Origins</option>
                        <option>Mt. Elgon, Uganda</option>
                        <option>Kapchorwa, Uganda</option>
                        <option>Kasese, Uganda</option>
                        <option>Mbale, Uganda</option>
                        <option>Arua, Uganda</option>
                    </select>
                    <select v-model="filterSeason" class="lt-select">
                        <option value="">All Seasons</option>
                        <option>2024 / A</option>
                        <option>2023 / B</option>
                    </select>
                    <select v-model="filterStatus" class="lt-select">
                        <option value="">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="in_auction">In Auction</option>
                        <option value="sold">Sold</option>
                        <option value="draft">Draft</option>
                    </select>
                    <select class="lt-select">
                        <option value="">Export Ready</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                    <select class="lt-select">
                        <option value="">Tokenised</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                    <div class="d-flex gap-2 ms-auto">
                        <button class="btn lt-btn-primary btn-sm">Apply Filters</button>
                        <button class="btn lt-btn-ghost btn-sm" @click="search=''; filterType=''; filterOrigin=''; filterSeason=''; filterStatus=''">Reset</button>
                    </div>
                </div>

                <!-- ── 4. Main grid + sidebar ────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left: lot list -->
                    <div class="col-12 col-xxl-8">

                        <!-- Count bar -->
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="lt-count-label">
                                <span class="lt-count-num">{{ displayLots.length }}</span> lots found
                            </div>
                        </div>

                        <!-- Lots table -->
                        <div class="lt-section mb-4">
                            <div class="table-responsive">
                                <table class="table lt-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="min-width:200px;">Lot</th>
                                            <th>Type</th>
                                            <th style="min-width:160px;">Origin</th>
                                            <th>Season</th>
                                            <th class="text-center">Quality</th>
                                            <th class="text-center">Moisture</th>
                                            <th class="text-end">Quantity</th>
                                            <th class="text-end">Price / kg</th>
                                            <th>Demand</th>
                                            <th>Attributes</th>
                                            <th>Status</th>
                                            <th style="min-width:140px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="lot in displayLots" :key="lot.id" class="lt-table-row">
                                            <!-- Lot identity -->
                                            <td>
                                                <div class="lt-tbl-lot">
                                                    <div class="lt-tbl-lot__avatar">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="14" height="14" style="opacity:.5"><path d="M17 8h1a4 4 0 010 8h-1"/><path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="lt-item-name">{{ lot.lot_name || lot.lot_number }}</div>
                                                        <div class="lt-tbl-lot__id">{{ lot.lot_number }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Type -->
                                            <td>
                                                <span class="lt-type-pill" :class="lot.type === 'Arabica' ? 'lt-type-pill--ara' : 'lt-type-pill--rob'">
                                                    {{ lot.type }}
                                                </span>
                                            </td>
                                            <!-- Origin -->
                                            <td>
                                                <div style="font-size:.8125rem;color:#374151;">{{ lot.origin }}</div>
                                            </td>
                                            <!-- Season -->
                                            <td>
                                                <span style="font-size:.75rem;color:#6b7280;white-space:nowrap;">{{ lot.season }}</span>
                                            </td>
                                            <!-- Quality score with mini bar -->
                                            <td class="text-center">
                                                <div class="lt-quality-cell">
                                                    <span class="lt-quality-num" :style="{ color: qualityColour(lot.quality_score) }">{{ lot.quality_score }}</span>
                                                    <div class="lt-quality-bar">
                                                        <div class="lt-quality-fill" :style="{ width: lot.quality_score + '%', background: qualityColour(lot.quality_score) }"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Moisture -->
                                            <td class="text-center">
                                                <span style="font-size:.8125rem;font-weight:600;" :style="{ color: lot.moisture > 13 ? '#b45309' : '#374151' }">
                                                    {{ lot.moisture }}%
                                                </span>
                                            </td>
                                            <!-- Quantity -->
                                            <td class="text-end">
                                                <span style="font-size:.8125rem;font-weight:700;color:#111827;">{{ lot.quantity?.toLocaleString() }}</span>
                                                <span style="font-size:.6875rem;color:#6b7280;"> kg</span>
                                            </td>
                                            <!-- Price -->
                                            <td class="text-end">
                                                <span style="font-size:.875rem;font-weight:800;color:#004532;">${{ lot.price?.toFixed(2) }}</span>
                                            </td>
                                            <!-- Demand -->
                                            <td>
                                                <span class="lt-badge" :class="demandCls(lot.demand)">{{ lot.demand }}</span>
                                                <div style="font-size:.6rem;color:#6b7280;margin-top:2px;">{{ lot.buyer_interest }} buyers</div>
                                            </td>
                                            <!-- Attribute badges -->
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap" style="max-width:120px;">
                                                    <span v-if="lot.verified"     class="lt-attr-dot lt-attr-dot--green" title="Verified">V</span>
                                                    <span v-if="lot.traceable"    class="lt-attr-dot lt-attr-dot--blue"  title="Traceable">T</span>
                                                    <span v-if="lot.export_ready" class="lt-attr-dot lt-attr-dot--green" title="Export Ready">E</span>
                                                    <span v-if="lot.tokenised"    class="lt-attr-dot lt-attr-dot--purple" title="Tokenised">K</span>
                                                    <span v-if="!lot.verified && !lot.traceable && !lot.export_ready && !lot.tokenised" style="font-size:.6875rem;color:#9ca3af;">—</span>
                                                </div>
                                            </td>
                                            <!-- Status -->
                                            <td>
                                                <span class="lt-badge" :class="statusCls(lot.status)">{{ statusLabel(lot.status) }}</span>
                                            </td>
                                            <!-- Actions -->
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <Link :href="route('lot.show', lot.id)" class="btn lt-btn-outline lt-act-btn btn-sm">
                                                        <el-icon><Tickets /></el-icon> View
                                                    </Link>
                                                    <Link :href="route('bid.place', lot.id)" class="btn lt-btn-primary lt-act-btn btn-sm">
                                                        <el-icon><Opportunity /></el-icon> Bid
                                                    </Link>
                                                    <Link :href="route('seller.sell-coffee')" class="btn lt-btn-outline lt-act-btn btn-sm">
                                                        <el-icon><Van /></el-icon>
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="lt-pagination-bar lt-section">
                            <span class="lt-muted" style="font-size:.8125rem;">
                                Showing {{ displayLots.length }} of {{ totalLots || displayLots.length }} lots
                            </span>
                            <div class="d-flex gap-1">
                                <a href="#" class="lt-page-btn lt-page-btn--disabled">← Prev</a>
                                <a
                                    v-for="p in visiblePages"
                                    :key="p"
                                    href="#"
                                    class="lt-page-btn"
                                    :class="{ 'lt-page-btn--active': p === (props.lots.meta?.current_page ?? 1) }"
                                >{{ p }}</a>
                                <a href="#" class="lt-page-btn">Next →</a>
                            </div>
                        </div>

                        <!-- ── Featured Lots ────────────────────────────── -->
                        <div class="lt-section mt-4">
                            <div class="lt-section-head">
                                <span class="lt-section-icon"><el-icon><Star /></el-icon></span>
                                Featured Lots
                                <span class="lt-section-count">{{ featuredLots.length }}</span>
                            </div>
                            <div class="lt-section-body p-0">
                                <div class="lt-featured-scroll">
                                    <div v-for="lot in featuredLots" :key="'f'+lot.id" class="lt-featured-card">
                                        <div class="lt-featured-img">
                                            <div class="lt-featured-img-ph">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="24" height="24" style="opacity:.3"><path d="M17 8h1a4 4 0 010 8h-1"/><path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/></svg>
                                            </div>
                                            <span class="lt-featured-tag">Featured</span>
                                        </div>
                                        <div class="lt-featured-body">
                                            <div class="lt-featured-name">{{ lot.lot_name }}</div>
                                            <div class="lt-featured-meta">
                                                <span :style="{ color: qualityColour(lot.quality_score), fontWeight: 700, fontSize: '.75rem' }">{{ lot.quality_score }} SCA</span>
                                                <span class="lt-sep">·</span>
                                                <span style="font-size:.75rem;color:#6b7280;">{{ lot.origin }}</span>
                                            </div>
                                            <div class="lt-featured-price">${{ lot.price?.toFixed(2) }}/kg</div>
                                            <Link :href="route('lot.show', lot.id)" class="btn lt-btn-primary lt-act-btn btn-sm w-100 mt-2">View Lot</Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Recently Added Lots ─────────────────────── -->
                        <div class="lt-section mt-3">
                            <div class="lt-section-head">
                                <span class="lt-section-icon"><el-icon><CollectionTag /></el-icon></span>
                                Recently Added Lots
                            </div>
                            <div class="lt-section-body p-0">
                                <div
                                    v-for="lot in recentLots"
                                    :key="'r'+lot.id"
                                    class="lt-recent-row"
                                >
                                    <div class="lt-recent-avatar">{{ lot.lot_number.slice(-2) }}</div>
                                    <div class="flex-fill min-width-0">
                                        <div class="lt-item-name">{{ lot.lot_name || lot.lot_number }}</div>
                                        <div style="font-size:.6875rem;color:#6b7280;">{{ lot.season }} · {{ lot.quantity?.toLocaleString() }} kg</div>
                                    </div>
                                    <span class="lt-badge" :class="statusCls(lot.status)">{{ statusLabel(lot.status) }}</span>
                                </div>
                            </div>
                        </div>

                    </div><!-- /col-xxl-8 -->

                    <!-- Right sidebar -->
                    <div class="col-12 col-xxl-4">
                        <div class="lt-rail">

                            <!-- Quick Buy -->
                            <div class="lt-section">
                                <div class="lt-section-head">
                                    <span class="lt-section-icon"><el-icon><ShoppingCart /></el-icon></span>
                                    Quick Buy
                                </div>
                                <div class="lt-section-body d-flex flex-column gap-3">

                                    <!-- Lot selector -->
                                    <div>
                                        <label class="lt-field-label">Select Lot</label>
                                        <el-select v-model="quickBuyLotId" placeholder="Choose a lot" size="small" style="width:100%;">
                                            <el-option
                                                v-for="lot in displayLots.filter(l => l.status === 'available' || l.status === 'in_auction')"
                                                :key="lot.id"
                                                :value="lot.id"
                                                :label="`${lot.lot_number} — ${lot.lot_name}`"
                                            />
                                        </el-select>
                                    </div>

                                    <!-- Selected lot snapshot -->
                                    <div v-if="quickBuyLot" class="lt-qb-snapshot">
                                        <div class="lt-qb-snapshot__row">
                                            <span class="lt-qb-snapshot__label">Origin</span>
                                            <span class="lt-qb-snapshot__value">{{ quickBuyLot.origin }}</span>
                                        </div>
                                        <div class="lt-qb-snapshot__row">
                                            <span class="lt-qb-snapshot__label">Type</span>
                                            <span class="lt-qb-snapshot__value">{{ quickBuyLot.type }}</span>
                                        </div>
                                        <div class="lt-qb-snapshot__row">
                                            <span class="lt-qb-snapshot__label">Quality</span>
                                            <span class="lt-qb-snapshot__value" :style="{ color: qualityColour(quickBuyLot.quality_score), fontWeight: 700 }">
                                                {{ quickBuyLot.quality_score }} SCA
                                            </span>
                                        </div>
                                        <div class="lt-qb-snapshot__row">
                                            <span class="lt-qb-snapshot__label">Available</span>
                                            <span class="lt-qb-snapshot__value">{{ quickBuyLot.quantity?.toLocaleString() }} kg</span>
                                        </div>
                                        <div class="lt-qb-snapshot__row">
                                            <span class="lt-qb-snapshot__label">Price</span>
                                            <span class="lt-qb-snapshot__value lt-green">${{ quickBuyLot.price?.toFixed(2) }}/kg</span>
                                        </div>
                                        <div class="d-flex gap-1 mt-1 flex-wrap">
                                            <span v-if="quickBuyLot.verified"     class="lt-badge lt-badge--green">Verified</span>
                                            <span v-if="quickBuyLot.export_ready" class="lt-badge lt-badge--green">Export Ready</span>
                                            <span v-if="quickBuyLot.tokenised"    class="lt-badge lt-badge--purple">Tokenised</span>
                                        </div>
                                    </div>

                                    <!-- Quantity & total -->
                                    <div>
                                        <label class="lt-field-label">Quantity (kg)</label>
                                        <el-input-number
                                            v-model="quickBuyQty"
                                            :min="1"
                                            :max="quickBuyLot?.quantity ?? 99999"
                                            :step="50"
                                            placeholder="e.g. 500"
                                            size="small"
                                            style="width:100%;"
                                            controls-position="right"
                                        />
                                        <div v-if="quickBuyQty && quickBuyLot" class="lt-qb-total">
                                            Total: <strong>${{ (Number(quickBuyQty) * (quickBuyLot.price ?? 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</strong>
                                        </div>
                                    </div>

                                    <!-- Buy now CTA -->
                                    <Link :href="route('checkout.index')" class="btn lt-btn-primary w-100 lt-act-btn">
                                        <el-icon><ShoppingCart /></el-icon> Buy Now
                                    </Link>

                                    <!-- Divider -->
                                    <div class="lt-qb-divider">or place a bid</div>

                                    <!-- Bid price -->
                                    <div>
                                        <label class="lt-field-label">Your Bid ($/kg)</label>
                                        <el-input
                                            v-model="quickBuyBidPx"
                                            type="number"
                                            :min="0"
                                            :step="0.01"
                                            :placeholder="`Min. $${quickBuyLot?.price?.toFixed(2) ?? '0.00'}`"
                                            size="small"
                                        >
                                            <template #prepend>$</template>
                                            <template #append>/kg</template>
                                        </el-input>
                                    </div>

                                    <Link :href="quickBuyLot ? route('bid.place', quickBuyLot.id) : '#'" class="btn lt-btn-outline w-100 lt-act-btn">
                                        <el-icon><Opportunity /></el-icon> Place Bid
                                    </Link>

                                    <!-- Top picks -->
                                    <div class="lt-qb-picks">
                                        <div class="lt-intel-heading mb-2">Top Picks</div>
                                        <div
                                            v-for="lot in displayLots.filter(l => l.quality_score >= 88).slice(0, 3)"
                                            :key="'qb'+lot.id"
                                            class="lt-qb-pick"
                                            :class="{ 'lt-qb-pick--active': quickBuyLotId === lot.id }"
                                            @click="quickBuyLotId = lot.id"
                                        >
                                            <div class="flex-fill min-width-0">
                                                <div style="font-size:.8125rem;font-weight:700;color:#111827;">{{ lot.lot_name }}</div>
                                                <div style="font-size:.6875rem;color:#6b7280;">{{ lot.origin }}</div>
                                            </div>
                                            <div class="text-end flex-shrink-0">
                                                <div style="font-size:.8125rem;font-weight:800;color:#004532;">${{ lot.price?.toFixed(2) }}</div>
                                                <div style="font-size:.6rem;" :style="{ color: qualityColour(lot.quality_score) }">{{ lot.quality_score }} SCA</div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Market Intelligence -->
                            <div class="lt-section">
                                <div class="lt-section-head">
                                    <span class="lt-section-icon"><el-icon><TrendCharts /></el-icon></span>
                                    Market Intelligence
                                </div>
                                <div class="lt-section-body d-flex flex-column gap-3">
                                    <div>
                                        <div class="lt-intel-heading mb-2">Market Demand</div>
                                        <div v-for="d in marketDemand" :key="d.label" class="mb-2">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span style="font-size:.8125rem;color:#374151;">{{ d.label }}</span>
                                                <span style="font-size:.75rem;font-weight:700;" :style="{ color: d.colour }">{{ d.pct }}%</span>
                                            </div>
                                            <div class="lt-bar-track">
                                                <div class="lt-bar-fill" :style="{ width: d.pct + '%', background: d.colour }"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="lt-intel-heading mb-2">Price Trends</div>
                                        <div class="d-flex gap-2">
                                            <div v-for="t in priceTrends" :key="t.label" class="lt-trend-chip">
                                                <span class="lt-trend-icon">{{ t.icon }}</span>
                                                <span>{{ t.label }}</span>
                                                <span class="lt-badge" :class="t.cls">{{ t.count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="lt-intel-heading mb-2">Buyer Activity</div>
                                        <div v-for="b in buyerActivity" :key="b.label" class="d-flex align-items-center gap-2 mb-1">
                                            <span style="font-size:.8125rem;color:#374151;width:56px;">{{ b.label }}</span>
                                            <div class="lt-bar-track flex-fill">
                                                <div class="lt-bar-fill" :style="{ width: b.pct + '%' }"></div>
                                            </div>
                                            <span style="font-size:.75rem;font-weight:700;color:#374151;width:28px;text-align:right;">{{ b.pct }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- AI Insights -->
                            <div class="lt-section">
                                <div class="lt-section-head">
                                    <span class="lt-section-icon"><el-icon><Star /></el-icon></span>
                                    AI Insights
                                </div>
                                <div class="lt-section-body d-flex flex-column gap-2">
                                    <div
                                        v-for="ins in insights"
                                        :key="ins.text"
                                        class="lt-insight-card"
                                        :class="`lt-insight-card--${ins.tone}`"
                                    >
                                        <el-icon class="lt-insight-icon"><Check v-if="ins.tone==='success'" /><TrendCharts v-else-if="ins.tone==='primary'" /><Warning v-else /></el-icon>
                                        <p class="lt-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /lt-rail -->
                    </div><!-- /col -->

                </div><!-- /row -->
            </div><!-- /container -->

            <!-- ── Floating actions ───────────────────────────────────────── -->
            <div class="lt-fab-wrap">
                <Transition name="lt-chat">
                    <div v-if="chatOpen" class="lt-chatbot">
                        <div class="lt-chatbot__head">
                            <div class="lt-chatbot__identity">
                                <div class="lt-chatbot__avatar"><el-icon><Star /></el-icon></div>
                                <div>
                                    <div class="lt-chatbot__name">Lot Advisor</div>
                                    <div class="lt-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="lt-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="lt-chatbot__body">
                            <div
                                v-for="(msg, i) in chatMsgs"
                                :key="i"
                                class="lt-chat-msg"
                                :class="msg.role === 'bot' ? 'lt-chat-msg--bot' : 'lt-chat-msg--user'"
                            >{{ msg.text }}</div>
                        </div>
                        <div class="lt-chatbot__prompts">
                            <button v-for="p in chatPrompts" :key="p" class="lt-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="lt-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <Link :href="route('batch.index')" class="lt-fab lt-fab--outline" title="Create Lot">
                    <el-icon><Box /></el-icon>
                </Link>
                <button class="lt-fab" title="Ask Advisor" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div><!-- /lt-page -->
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.lt-page {
    --green:          #004532;
    --blue:           #1d4ed8;
    --border:         #eef2f0;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-low:    #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}
.lt-green { color: #166534; font-weight: 700; }
.lt-blue  { color: var(--blue); font-weight: 700; }
.lt-muted { color: var(--on-surface-var); }
.lt-sep   { margin: 0 4px; color: var(--on-surface-var); }
.lt-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Header ────────────────────────────────────────────────────────────────── */
.lt-header   { background: #fff; border-bottom: 1px solid var(--border); }
.lt-kicker   { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.lt-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.lt-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.lt-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.lt-btn-primary:hover { background: #065f46; color: #fff; }
.lt-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.lt-btn-outline:hover { background: var(--surface-low); }
.lt-btn-ghost { background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.lt-act-btn { font-size: .75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }

/* ── KPI ───────────────────────────────────────────────────────────────────── */
.lt-kpi { border: 1px solid var(--border); border-radius: 8px; padding: .875rem; background: #fff; }
.lt-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.lt-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.lt-kpi__sub   { font-size: .625rem; color: var(--on-surface-var); }

/* ── Filters ───────────────────────────────────────────────────────────────── */
.lt-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.lt-search-wrap  { position: relative; display: flex; align-items: center; }
.lt-search-icon  { position: absolute; left: 8px; font-size: 12px; color: var(--on-surface-var); pointer-events: none; }
.lt-search-input { height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 0 10px 0 26px; font-size: .8125rem; outline: none; width: 180px; color: var(--on-surface); }
.lt-search-input:focus { border-color: var(--green); }
.lt-select { height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 0 10px; font-size: .8125rem; color: var(--on-surface); background: #fff; outline: none; cursor: pointer; }
.lt-select:focus { border-color: var(--green); }

/* ── Sections ──────────────────────────────────────────────────────────────── */
.lt-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; }
.lt-section-head { display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: var(--surface-low); border-bottom: 1px solid var(--border); border-radius: 7px 7px 0 0; font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.lt-section-icon { width: 20px; height: 20px; border-radius: 4px; background: rgba(0,69,50,.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.lt-section-count { font-size: .6875rem; font-weight: 700; background: var(--border); color: var(--on-surface-var); border-radius: 999px; padding: 2px 8px; }
.lt-section-body { padding: 1rem; }

/* ── Count label ───────────────────────────────────────────────────────────── */
.lt-count-label { font-size: .8125rem; color: var(--on-surface-var); }
.lt-count-num   { font-weight: 700; color: var(--on-surface); }

/* ── Table lot cell ────────────────────────────────────────────────────────── */
.lt-tbl-lot { display: flex; align-items: center; gap: 10px; }
.lt-tbl-lot__avatar { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface-low); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lt-tbl-lot__id { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); margin-top: 1px; }

/* ── Type pill ─────────────────────────────────────────────────────────────── */
.lt-type-pill { display: inline-flex; border-radius: 4px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; }
.lt-type-pill--ara { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.lt-type-pill--rob { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

/* ── Quality cell ──────────────────────────────────────────────────────────── */
.lt-quality-cell { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.lt-quality-num  { font-size: .875rem; font-weight: 800; line-height: 1; }
.lt-quality-bar  { width: 48px; height: 3px; background: var(--border); border-radius: 999px; overflow: hidden; }
.lt-quality-fill { height: 100%; border-radius: 999px; }

/* ── Attribute dots ────────────────────────────────────────────────────────── */
.lt-attr-dot { width: 18px; height: 18px; border-radius: 4px; font-size: .5rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; cursor: default; }
.lt-attr-dot--green  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lt-attr-dot--blue   { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.lt-attr-dot--purple { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }

/* ── Badges ────────────────────────────────────────────────────────────────── */
.lt-badge          { display: inline-flex; border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 7px; }
.lt-badge--green   { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lt-badge--blue    { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.lt-badge--yellow  { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.lt-badge--purple  { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }
.lt-badge--muted   { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

/* ── Bar ───────────────────────────────────────────────────────────────────── */
.lt-bar-track { height: 5px; background: var(--border); border-radius: 999px; overflow: hidden; }
.lt-bar-fill  { height: 100%; background: var(--green); border-radius: 999px; transition: width .4s ease; }

/* ── Quick Buy ─────────────────────────────────────────────────────────────── */
.lt-field-label { display: block; font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .05em; }
.lt-qb-snapshot { border: 1px solid var(--border); border-radius: 6px; padding: 10px 12px; background: var(--surface-low); display: flex; flex-direction: column; gap: 5px; }
.lt-qb-snapshot__row { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; }
.lt-qb-snapshot__label { font-size: .6875rem; color: var(--on-surface-var); white-space: nowrap; }
.lt-qb-snapshot__value { font-size: .8125rem; font-weight: 600; color: var(--on-surface); text-align: right; }
.lt-qb-total { font-size: .8125rem; color: var(--on-surface-var); margin-top: 4px; }
.lt-qb-total strong { color: var(--green); }
.lt-qb-divider { text-align: center; font-size: .6875rem; color: var(--on-surface-var); position: relative; }
.lt-qb-divider::before,
.lt-qb-divider::after { content: ''; position: absolute; top: 50%; width: 38%; height: 1px; background: var(--border); }
.lt-qb-divider::before { left: 0; }
.lt-qb-divider::after  { right: 0; }
.lt-qb-picks { display: flex; flex-direction: column; gap: 2px; }
.lt-qb-pick { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; transition: background .12s; }
.lt-qb-pick:hover { background: #f0fdf4; border-color: #bbf7d0; }
.lt-qb-pick--active { background: #f0fdf4; border-color: #004532; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.lt-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.lt-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.lt-table-row:last-child td { border-bottom: none; }
.lt-table-row:hover { background: var(--surface-low); }

/* ── Pagination ────────────────────────────────────────────────────────────── */
.lt-pagination-bar { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; flex-wrap: wrap; gap: .5rem; }
.lt-page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 28px; border-radius: 5px; border: 1px solid var(--border); background: #fff; color: var(--on-surface); font-size: .8125rem; font-weight: 600; text-decoration: none; padding: 0 8px; }
.lt-page-btn:hover { background: var(--surface-low); }
.lt-page-btn--active   { background: var(--green); border-color: var(--green); color: #fff; }
.lt-page-btn--disabled { opacity: .4; pointer-events: none; }

/* ── Featured lots ─────────────────────────────────────────────────────────── */
.lt-featured-scroll { display: flex; gap: 12px; overflow-x: auto; padding: 12px 16px; scrollbar-width: none; }
.lt-featured-scroll::-webkit-scrollbar { display: none; }
.lt-featured-card { flex: 0 0 160px; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
.lt-featured-img  { height: 90px; background: #f1f5f9; position: relative; }
.lt-featured-img-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
.lt-featured-tag  { position: absolute; top: 6px; left: 6px; background: #c8862a; color: #fff; font-size: .5rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; border-radius: 999px; padding: 2px 7px; }
.lt-featured-body { padding: 8px 10px; }
.lt-featured-name { font-size: .75rem; font-weight: 700; color: var(--on-surface); line-height: 1.3; margin-bottom: 2px; }
.lt-featured-meta { display: flex; align-items: center; gap: 4px; margin-bottom: 4px; }
.lt-featured-price { font-size: .875rem; font-weight: 800; color: var(--green); }

/* ── Recently added rows ───────────────────────────────────────────────────── */
.lt-recent-row    { display: flex; align-items: center; gap: 10px; padding: 9px 16px; border-bottom: 1px solid var(--border); }
.lt-recent-row:last-child { border-bottom: none; }
.lt-recent-avatar { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface-low); font-size: .75rem; font-weight: 800; color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Sidebar ───────────────────────────────────────────────────────────────── */
.lt-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }
.lt-intel-heading { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); }
.lt-trend-chip { display: flex; align-items: center; gap: 4px; background: var(--surface-low); border: 1px solid var(--border); border-radius: 6px; padding: 4px 8px; font-size: .75rem; font-weight: 600; color: var(--on-surface); }
.lt-trend-icon { font-size: .875rem; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.lt-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: .875rem; border-radius: 8px; border: 1px solid; }
.lt-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.lt-insight-card--primary { background: #eff6ff; border-color: #bfdbfe; }
.lt-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.lt-insight-icon { font-size: 13px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.lt-insight-text { font-size: .8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Floating actions ──────────────────────────────────────────────────────── */
.lt-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: .75rem; }
.lt-fab { width: 46px; height: 46px; border-radius: 50%; border: 1.5px solid var(--green); background: var(--green); color: #fff; font-size: 18px; display: flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; }
.lt-fab:hover { background: #065f46; }
.lt-fab--outline { background: #fff; color: var(--green); }
.lt-fab--outline:hover { background: var(--surface-low); color: var(--green); }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.lt-chatbot { width: 310px; border-radius: 10px; overflow: hidden; background: #fff; border: 1px solid var(--border); display: flex; flex-direction: column; }
.lt-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.lt-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.lt-chatbot__avatar { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 13px; }
.lt-chatbot__name { font-size: .875rem; font-weight: 700; }
.lt-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: .625rem; opacity: .8; }
.lt-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.lt-chatbot__close { border: none; background: none; color: rgba(255,255,255,.8); font-size: 20px; line-height: 1; cursor: pointer; }
.lt-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 190px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.lt-chat-msg { font-size: .8125rem; padding: 8px 10px; border-radius: 8px; line-height: 1.5; max-width: 90%; }
.lt-chat-msg--bot  { background: #fff; color: var(--on-surface); border: 1px solid var(--border); border-radius: 8px 8px 8px 2px; }
.lt-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 8px 8px 2px 8px; }
.lt-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 4px; padding: 7px 10px; border-top: 1px solid var(--border); }
.lt-prompt-chip { font-size: .6rem; padding: 3px 8px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.lt-prompt-chip:hover { background: #dcfce7; }
.lt-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--border); }
.lt-chatbot__input input { flex: 1; border: 1px solid var(--border); border-radius: 6px; padding: 6px 9px; font-size: .8125rem; outline: none; }
.lt-chatbot__input input:focus { border-color: var(--green); }
.lt-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 6px; width: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px; }
.lt-chat-enter-active, .lt-chat-leave-active { transition: opacity .2s ease, transform .2s ease; }
.lt-chat-enter-from, .lt-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .lt-rail { position: static; } }
@media (max-width: 767.98px) {
    .lt-chatbot  { width: calc(100vw - 3rem); max-width: 310px; }
    .lt-search-input { width: 140px; }
    .lt-filters { gap: 6px; }
}
</style>
