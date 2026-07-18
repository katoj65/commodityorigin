<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Radar, Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement,
    RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend,
} from 'chart.js';
import {
    Search, MagicStick, DataAnalysis, TrendCharts, Coin, Box, UserFilled,
    Filter, Download, Grid, PieChart, Trophy, Medal, CircleCheck, Clock,
    Close, View, Star, Picture,
    InfoFilled, Refresh, Aim,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Calendar from '@/Components/Calendar.vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const props = defineProps({
    overview: { type: Object, default: () => ({}) },
    calendarEvents: { type: Array, default: () => [] },
    featuredLots: { type: Array, default: () => [] },
    liveBids: { type: Array, default: () => [] },
    upcomingLots: { type: Array, default: () => [] },
    lotExplorer: { type: Array, default: () => [] },
    qualityProfiles: { type: Array, default: () => [] },
    analytics: { type: Object, default: () => ({ top_origins: [], bids_per_lot: [], status_volume: [] }) },
    originIntelligence: { type: Array, default: () => [] },
    aiIntelligence: { type: Object, default: () => ({ has_data: false }) },
    leaderboard: { type: Object, default: () => ({ top_buyers: [], top_sellers: [], most_active_origins: [] }) },
    lotDetails: { type: Array, default: () => [] },
});

/* ══════════════════════════════════════════════════════════════════════
   Top bar — AI search + quick actions
   ══════════════════════════════════════════════════════════════════════ */
const searchQuery = ref('');
const searchPrompts = [
    'Show auctions ending today',
    'Find Grade AA coffee',
    'Find lots above 86 cup score',
    'Which lot has the highest ROI',
];

function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function exportReport() {
    const rows = [
        ['Lot ID', 'Origin', 'Region', 'Grower', 'Variety', 'Grade', 'Cup Score', 'Quantity (kg)', 'Reserve Price', 'Current Bid', 'Status'],
        ...props.lotExplorer.map((r) => [r.lot_number, r.origin, r.region, r.grower, r.variety, r.grade, r.cup_score, r.net_weight_kg, r.reserve_price, r.current_bid ?? '', r.status]),
    ];
    const csv = rows.map((row) => row.map((cell) => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'auction-report.csv';
    a.click();
    URL.revokeObjectURL(url);
}

const quickActions = [
    { label: 'Browse Auctions', icon: Grid, action: () => scrollTo('auc-explorer') },
    { label: 'Compare Lots', icon: DataAnalysis, action: () => { compareMode.value = !compareMode.value; } },
    { label: 'Export Report', icon: Download, action: exportReport },
];

/* ══════════════════════════════════════════════════════════════════════
   Watch / compare state (session-only — not persisted to the backend)
   ══════════════════════════════════════════════════════════════════════ */
const watched = ref(new Set());
function toggleWatch(id) {
    const next = new Set(watched.value);
    next.has(id) ? next.delete(id) : next.add(id);
    watched.value = next;
}

const compareMode = ref(false);
const compareSet = ref(new Set());
function toggleCompare(id) {
    const next = new Set(compareSet.value);
    next.has(id) ? next.delete(id) : next.add(id);
    compareSet.value = next;
}
const compareLots = computed(() => props.lotExplorer.filter((r) => compareSet.value.has(r.id)));

/* ══════════════════════════════════════════════════════════════════════
   Right sidebar filters — applied client-side to the lot explorer
   ══════════════════════════════════════════════════════════════════════ */
const filters = reactive({ origin: '', variety: '', process: '', grade: '', status: '', minCupScore: '' });

const filterOptions = computed(() => ({
    origins: [...new Set(props.lotExplorer.map((r) => r.origin).filter(Boolean))],
    varieties: [...new Set(props.lotExplorer.map((r) => r.variety).filter(Boolean))],
    processes: [...new Set(props.lotExplorer.map((r) => r.process).filter(Boolean))],
    grades: [...new Set(props.lotExplorer.map((r) => r.grade).filter(Boolean))],
    statuses: [...new Set(props.lotExplorer.map((r) => r.status).filter(Boolean))],
}));

const explorerSearch = ref('');

const filteredExplorer = computed(() => props.lotExplorer.filter((r) => {
    if (filters.origin && r.origin !== filters.origin) return false;
    if (filters.variety && r.variety !== filters.variety) return false;
    if (filters.process && r.process !== filters.process) return false;
    if (filters.grade && r.grade !== filters.grade) return false;
    if (filters.status && r.status !== filters.status) return false;
    if (filters.minCupScore && r.cup_score < Number(filters.minCupScore)) return false;
    if (explorerSearch.value) {
        const term = explorerSearch.value.toLowerCase();
        const haystack = `${r.lot_number} ${r.origin} ${r.region} ${r.grower} ${r.variety}`.toLowerCase();
        if (!haystack.includes(term)) return false;
    }
    return true;
}));

function resetFilters() {
    filters.origin = ''; filters.variety = ''; filters.process = ''; filters.grade = ''; filters.status = ''; filters.minCupScore = '';
    explorerSearch.value = '';
}

/* ══════════════════════════════════════════════════════════════════════
   Quality intelligence — radar chart, switchable between live lots
   ══════════════════════════════════════════════════════════════════════ */
const selectedQualityLot = ref(props.qualityProfiles[0]?.lot_id ?? null);
const selectedQualityProfile = computed(() => props.qualityProfiles.find((p) => p.lot_id === selectedQualityLot.value) ?? props.qualityProfiles[0]);

const radarData = computed(() => {
    const profile = selectedQualityProfile.value;
    if (!profile) return { labels: [], datasets: [] };
    return {
        labels: profile.axes.map((a) => a.label),
        datasets: [{
            label: profile.lot_number,
            data: profile.axes.map((a) => a.value),
            backgroundColor: 'rgba(0,69,50,0.15)',
            borderColor: '#004532',
            pointBackgroundColor: '#004532',
        }],
    };
});
const radarOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: { r: { min: 0, max: 100, ticks: { stepSize: 20, font: { size: 9 } }, pointLabels: { font: { size: 11, weight: '600' } } } },
    plugins: { legend: { display: false } },
};

/* ══════════════════════════════════════════════════════════════════════
   Analytics charts
   ══════════════════════════════════════════════════════════════════════ */
const originChartData = computed(() => ({
    labels: props.analytics.top_origins.map((o) => o.label),
    datasets: [{ label: 'Average Price', data: props.analytics.top_origins.map((o) => o.average_price), backgroundColor: '#004532', borderRadius: 6, maxBarThickness: 32 }],
}));
const bidsChartData = computed(() => ({
    labels: props.analytics.bids_per_lot.map((b) => b.label),
    datasets: [{ label: 'Bids', data: props.analytics.bids_per_lot.map((b) => b.bids), backgroundColor: '#c8862a', borderRadius: 6, maxBarThickness: 32 }],
}));
const statusChartData = computed(() => ({
    labels: props.analytics.status_volume.map((s) => s.label),
    datasets: [{ data: props.analytics.status_volume.map((s) => s.count), backgroundColor: ['#004532', '#c8862a', '#2563eb', '#dc2626', '#7c3aed'] }],
}));
const barOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } } },
};
const doughnutOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 10 } } } } };

/* ══════════════════════════════════════════════════════════════════════
   Lot details modal
   ══════════════════════════════════════════════════════════════════════ */
const activeLotId = ref(null);
const activeLot = computed(() => props.lotDetails.find((l) => l.id === activeLotId.value));
function openLot(id) { activeLotId.value = id; }
function closeLot() { activeLotId.value = null; }

/* ══════════════════════════════════════════════════════════════════════
   Display helpers
   ══════════════════════════════════════════════════════════════════════ */
const fmtMoney = (n) => (n != null ? Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');
const cap = (s) => (s ? String(s).charAt(0).toUpperCase() + String(s).slice(1) : '—');
const statusCls = (s) => ({
    ready: 'auc-badge--green', listing_ready: 'auc-badge--green', tokenisation_ready: 'auc-badge--blue', draft: 'auc-badge--muted',
}[s] ?? 'auc-badge--muted');
</script>

<template>
    <AppLayout title="Coffee Auction Exchange" full-width flush :show-banner="false">
        <Head title="Coffee Auction Exchange" />

        <div class="auc-page">

            <!-- ══════════════════════════════════════════════════════════
                 Sticky top bar — AI search + quick actions
                 ══════════════════════════════════════════════════════════ -->
            <div class="auc-topbar pt-2 pb-2">
                <div class="container-fluid px-3 px-lg-4 py-2">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
                        <div class="auc-search-wrap flex-grow-1">
                            <el-icon class="auc-search-icon"><Search /></el-icon>
                            <input v-model="searchQuery" class="auc-search-input" placeholder="Ask Coffee Pulse AI…">
                            <el-icon class="auc-search-ai"><MagicStick /></el-icon>
                        </div>
                        <div class="auc-quick-actions">
                            <template v-for="qa in quickActions" :key="qa.label">
                                <Link v-if="qa.href" :href="qa.href" class="auc-qa-btn">
                                    <el-icon><component :is="qa.icon" /></el-icon> {{ qa.label }}
                                </Link>
                                <button v-else type="button" class="auc-qa-btn" :class="{ 'auc-qa-btn--active': qa.label === 'Compare Lots' && compareMode }" @click="qa.action">
                                    <el-icon><component :is="qa.icon" /></el-icon> {{ qa.label }}
                                </button>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">
                <div class="row g-3">

                    <!-- ══════════════════════════════════════════════════
                         Main column
                         ══════════════════════════════════════════════════ -->
                    <div class="col-12 col-xl-9 order-2 order-xl-1">

                        <!-- SECTION 1 — Overview hero -->
                        <section class="auc-section">

                            <div class="row g-2 pt-0">
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Grid /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.live_auctions) }}</div><div class="auc-kpi__label">Live Auctions</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Clock /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.upcoming_lots) }}</div><div class="auc-kpi__label">Upcoming Lots</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><CircleCheck /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.completed_auctions) }}</div><div class="auc-kpi__label">Completed</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><UserFilled /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.active_buyers) }}</div><div class="auc-kpi__label">Active Buyers</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Box /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.lots_available) }}</div><div class="auc-kpi__label">Lots Available</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Coin /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.total_auction_value) }}</div><div class="auc-kpi__label">Total Auction Value</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><TrendCharts /></el-icon><div class="auc-kpi__value">{{ overview.highest_bid_today != null ? fmtNum(overview.highest_bid_today) : '—' }}</div><div class="auc-kpi__label">Highest Bid Today</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Trophy /></el-icon><div class="auc-kpi__value">{{ overview.average_winning_price != null ? fmtNum(overview.average_winning_price) : '—' }}</div><div class="auc-kpi__label">Avg. Winning Price</div></div>
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 2 — Featured auctions -->
                        <section class="auc-section">
                            <div class="auc-section-head"><el-icon class="auc-section-icon"><Star /></el-icon><h2 class="auc-title">Featured Auctions</h2></div>

                            <div v-if="!featuredLots.length" class="auc-empty">No lots are currently open for bidding.</div>
                            <div v-else class="row g-3">
                                <div v-for="lot in featuredLots" :key="lot.id" class="col-12 col-md-6 col-xl-4">
                                    <div class="auc-lot-card h-100" @click="openLot(lot.id)">
                                        <div class="auc-lot-card__image">
                                            <img v-if="lot.image" :src="lot.image" :alt="lot.lot_name">
                                            <el-icon v-else><Picture /></el-icon>
                                            <span class="auc-lot-card__ai">AI {{ lot.ai_score }}</span>
                                        </div>
                                        <div class="auc-lot-card__body">
                                            <div class="d-flex align-items-start justify-content-between mb-1">
                                                <div>
                                                    <div class="auc-lot-card__lot">{{ lot.lot_number }}</div>
                                                    <div class="auc-lot-card__name">{{ lot.lot_name ?? 'Unnamed Lot' }}</div>
                                                </div>
                                                <span class="auc-badge" :class="statusCls(lot.status)">{{ cap(lot.status?.replace('_',' ')) }}</span>
                                            </div>
                                            <div class="auc-lot-card__meta">{{ lot.origin_country }}<span v-if="lot.region"> · {{ lot.region }}</span></div>
                                            <div class="auc-lot-card__meta">{{ lot.grower }}</div>

                                            <div class="auc-tags">
                                                <span v-if="lot.variety" class="auc-tag">{{ cap(lot.variety) }}</span>
                                                <span v-if="lot.grade" class="auc-tag">Grade {{ lot.grade }}</span>
                                                <span v-if="lot.process" class="auc-tag">{{ lot.process }}</span>
                                                <span v-if="lot.harvest_year" class="auc-tag">{{ lot.harvest_year }}</span>
                                            </div>

                                            <div class="auc-lot-card__price-row">
                                                <div>
                                                    <span class="auc-lot-card__price-label">{{ lot.current_bid != null ? 'Current Bid' : 'Starting Price' }}</span>
                                                    <div class="auc-lot-card__price">{{ fmtMoney(lot.current_bid ?? lot.starting_price) }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <span class="auc-lot-card__price-label">Min. Increment</span>
                                                    <div class="auc-lot-card__increment">+{{ fmtMoney(lot.min_increment) }}</div>
                                                </div>
                                            </div>

                                            <div class="auc-lot-card__stats">
                                                <span><el-icon><UserFilled /></el-icon> {{ lot.bidder_count }} bidder{{ lot.bidder_count === 1 ? '' : 's' }}</span>
                                                <span><el-icon><Clock /></el-icon> Listed {{ lot.listed_ago }}</span>
                                            </div>

                                            <div class="d-flex gap-2 mt-2" @click.stop>
                                                <button type="button" class="btn auc-btn-outline flex-fill" :class="{ 'auc-btn-outline--active': watched.has(lot.id) }" @click="toggleWatch(lot.id)">
                                                    <el-icon><View /></el-icon> {{ watched.has(lot.id) ? 'Watching' : 'Watch' }}
                                                </button>
                                                <Link :href="route('bid.place', lot.id)" class="btn auc-btn-primary flex-fill">
                                                    <el-icon><Coin /></el-icon> Place Bid
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 3 — Live auction feed -->
                        <section class="auc-section">
                            <div class="auc-section-head"><span class="auc-live-dot"></span><h2 class="auc-title">Live Auction Feed</h2></div>
                            <div class="auc-card">
                                <div v-if="!liveBids.length" class="auc-empty">No bids have been placed yet.</div>
                                <div v-else class="auc-feed">
                                    <div v-for="bid in liveBids" :key="bid.id" class="auc-feed-row">
                                        <div class="auc-feed-row__avatar"><el-icon><UserFilled /></el-icon></div>
                                        <div class="flex-grow-1">
                                            <div class="auc-feed-row__top">
                                                <strong>{{ bid.bidder }}</strong> bid <strong class="auc-feed-row__amount">{{ fmtMoney(bid.amount) }}</strong> on <strong>{{ bid.lot_number }}</strong>
                                            </div>
                                            <div class="auc-muted">{{ bid.placed_ago }} · {{ fmtNum(bid.quantity) }} kg</div>
                                        </div>
                                        <span class="auc-badge" :class="bid.status === 'pending' ? 'auc-badge--amber' : 'auc-badge--green'">{{ cap(bid.status) }}</span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 4 — Auction calendar / pipeline -->
                        <section class="auc-section">
                            <div class="auc-section-head"><el-icon class="auc-section-icon"><Clock /></el-icon><h2 class="auc-title">Auction Calendar</h2></div>
                            <div class="auc-card">
                                <p class="auc-muted mb-3" style="font-size:.8125rem;">Lots below are still in preparation and not yet open for bidding.</p>
                                <div v-if="!upcomingLots.length" class="auc-empty">No lots are currently in the pipeline — every listed lot is already open for bidding.</div>
                                <div v-else class="table-responsive">
                                    <table class="table auc-table mb-0">
                                        <thead><tr><th>Lot</th><th>Origin</th><th>Listed By</th><th>Added</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <tr v-for="lot in upcomingLots" :key="lot.id">
                                                <td class="auc-item-name">{{ lot.lot_number }}</td>
                                                <td class="auc-muted">{{ lot.origin ?? '—' }}</td>
                                                <td class="auc-muted">{{ lot.grower ?? '—' }}</td>
                                                <td class="auc-muted">{{ lot.listed_ago }}</td>
                                                <td><span class="auc-badge auc-badge--muted">Pending Listing</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 5 — Coffee lot explorer -->
                        <section id="auc-explorer" class="auc-section">
                            <div class="auc-section-head"><el-icon class="auc-section-icon"><Grid /></el-icon><h2 class="auc-title">Coffee Lot Explorer</h2><span class="auc-count">{{ filteredExplorer.length }} of {{ lotExplorer.length }} lots</span></div>

                            <div class="auc-card">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <div class="auc-search-wrap auc-search-wrap--table">
                                        <el-icon class="auc-search-icon"><Search /></el-icon>
                                        <input v-model="explorerSearch" class="auc-search-input" placeholder="Search lot, origin, grower…">
                                    </div>
                                    <button type="button" class="btn auc-btn-outline btn-sm" @click="resetFilters"><el-icon><Refresh /></el-icon> Reset Filters</button>
                                </div>

                                <div v-if="!filteredExplorer.length" class="auc-empty">No lots match your filters.</div>
                                <div v-else class="table-responsive">
                                    <table class="table auc-table mb-0">
                                        <thead>
                                            <tr>
                                                <th v-if="compareMode"></th>
                                                <th>Lot ID</th><th>Origin</th><th>Region</th><th>Grower</th><th>Variety</th><th>Grade</th>
                                                <th>Moisture</th><th>Screen</th><th class="text-end">Cup Score</th><th class="text-end">Quantity</th>
                                                <th class="text-end">Reserve</th><th class="text-end">Current Bid</th><th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="row in filteredExplorer" :key="row.id" class="auc-table-row" @click="openLot(row.id)">
                                                <td v-if="compareMode" @click.stop>
                                                    <input type="checkbox" :checked="compareSet.has(row.id)" @change="toggleCompare(row.id)">
                                                </td>
                                                <td class="auc-item-name">{{ row.lot_number }}</td>
                                                <td class="auc-muted">{{ cap(row.origin) }}</td>
                                                <td class="auc-muted">{{ row.region ?? '—' }}</td>
                                                <td class="auc-muted">{{ row.grower ?? '—' }}</td>
                                                <td class="auc-muted">{{ cap(row.variety) }}</td>
                                                <td><span class="auc-grade-pill">{{ row.grade ?? '—' }}</span></td>
                                                <td class="auc-muted">{{ row.moisture_content != null ? row.moisture_content + '%' : '—' }}</td>
                                                <td class="auc-muted">{{ row.screen_size ?? '—' }}</td>
                                                <td class="text-end"><strong>{{ row.cup_score }}</strong></td>
                                                <td class="text-end auc-muted">{{ fmtNum(row.net_weight_kg) }} kg</td>
                                                <td class="text-end auc-muted">{{ fmtMoney(row.reserve_price) }}</td>
                                                <td class="text-end"><strong v-if="row.current_bid">{{ fmtMoney(row.current_bid) }}</strong><span v-else class="auc-muted">—</span></td>
                                                <td><span class="auc-badge" :class="statusCls(row.status)">{{ cap(row.status?.replace('_',' ')) }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Compare bar -->
                            <div v-if="compareMode && compareLots.length" class="auc-card mt-3">
                                <div class="auc-card-title mb-2"><el-icon class="auc-card-icon"><DataAnalysis /></el-icon> Comparing {{ compareLots.length }} Lot{{ compareLots.length === 1 ? '' : 's' }}</div>
                                <div class="table-responsive">
                                    <table class="table auc-table mb-0">
                                        <thead><tr><th>Lot</th><th>Origin</th><th>Grade</th><th class="text-end">Cup Score</th><th class="text-end">Reserve</th><th class="text-end">Current Bid</th></tr></thead>
                                        <tbody>
                                            <tr v-for="row in compareLots" :key="row.id">
                                                <td class="auc-item-name">{{ row.lot_number }}</td>
                                                <td class="auc-muted">{{ cap(row.origin) }}</td>
                                                <td>{{ row.grade ?? '—' }}</td>
                                                <td class="text-end">{{ row.cup_score }}</td>
                                                <td class="text-end">{{ fmtMoney(row.reserve_price) }}</td>
                                                <td class="text-end">{{ row.current_bid ? fmtMoney(row.current_bid) : '—' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 6 — Quality intelligence -->
                        <section class="auc-section">
                            <div class="auc-section-head"><el-icon class="auc-section-icon"><Aim /></el-icon><h2 class="auc-title">Coffee Quality Intelligence</h2></div>
                            <div class="auc-card">
                                <div v-if="!qualityProfiles.length" class="auc-empty">No quality data available yet.</div>
                                <template v-else>
                                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                        <span class="auc-muted" style="font-size:.8125rem;">Viewing:</span>
                                        <select v-model="selectedQualityLot" class="auc-select">
                                            <option v-for="p in qualityProfiles" :key="p.lot_id" :value="p.lot_id">{{ p.lot_number }}</option>
                                        </select>
                                    </div>
                                    <div class="row g-3 align-items-center">
                                        <div class="col-12 col-md-7">
                                            <div class="auc-radar-wrap"><Radar :data="radarData" :options="radarOptions" /></div>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <div v-for="axis in selectedQualityProfile?.axes" :key="axis.label" class="auc-quality-row">
                                                <span>{{ axis.label }}</span>
                                                <div class="auc-bar-track"><div class="auc-bar-fill" :style="{ width: axis.value + '%' }"></div></div>
                                                <strong>{{ axis.value }}</strong>
                                            </div>
                                            <p v-if="selectedQualityProfile?.cupping_notes" class="auc-muted mt-2 mb-0" style="font-size:.75rem;">{{ selectedQualityProfile.cupping_notes }}</p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <!-- SECTION 7 — Auction analytics -->
                        <section class="auc-section">
                            <div class="auc-section-head"><el-icon class="auc-section-icon"><PieChart /></el-icon><h2 class="auc-title">Auction Analytics</h2></div>
                            <div class="row g-3">
                                <div class="col-12 col-lg-4">
                                    <div class="auc-card h-100">
                                        <div class="auc-card-title mb-2">Highest-Selling Origins</div>
                                        <div v-if="analytics.top_origins.length" class="auc-chart-wrap"><Bar :data="originChartData" :options="barOptions" /></div>
                                        <div v-else class="auc-empty">No data yet.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="auc-card h-100">
                                        <div class="auc-card-title mb-2">Bidding Activity by Lot</div>
                                        <div v-if="analytics.bids_per_lot.length" class="auc-chart-wrap"><Bar :data="bidsChartData" :options="barOptions" /></div>
                                        <div v-else class="auc-empty">No data yet.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="auc-card h-100">
                                        <div class="auc-card-title mb-2">Auction Volume by Status</div>
                                        <div v-if="analytics.status_volume.length" class="auc-chart-wrap"><Doughnut :data="statusChartData" :options="doughnutOptions" /></div>
                                        <div v-else class="auc-empty">No data yet.</div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 8 — Origin intelligence -->
                        <section class="auc-section">
                            <div class="auc-section-head"><el-icon class="auc-section-icon"><Grid /></el-icon><h2 class="auc-title">Origin Intelligence</h2></div>
                            <div v-if="!originIntelligence.length" class="auc-empty">No origin data available yet.</div>
                            <div v-else class="row g-3">
                                <div v-for="o in originIntelligence" :key="o.label" class="col-12 col-md-6 col-xl-4">
                                    <div class="auc-card h-100">
                                        <div class="auc-card-title mb-2">{{ cap(o.label) }}</div>
                                        <div class="auc-spec"><span>Lots Available</span><strong>{{ o.lots }}</strong></div>
                                        <div class="auc-spec"><span>Average Quality</span><strong>{{ o.average_quality }}</strong></div>
                                        <div class="auc-spec"><span>Average Price</span><strong>{{ fmtMoney(o.average_price) }}</strong></div>
                                        <div class="auc-spec"><span>Volume Available</span><strong>{{ fmtNum(o.total_volume_kg) }} kg</strong></div>
                                        <div class="auc-spec"><span>Bidding Activity</span><strong>{{ o.total_bids }} bids</strong></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 9 — AI auction intelligence -->
                        <section class="auc-section">
                            <div class="auc-section-head"><el-icon class="auc-section-icon"><MagicStick /></el-icon><h2 class="auc-title">AI Auction Intelligence</h2></div>
                            <div class="auc-ai-panel">
                                <div v-if="!aiIntelligence.has_data" class="auc-empty">{{ aiIntelligence.message }}</div>
                                <template v-else>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="auc-ai-card">
                                                <div class="auc-ai-card__label"><el-icon><TrendCharts /></el-icon> Most Undervalued Lot</div>
                                                <div class="auc-ai-card__value">{{ aiIntelligence.most_undervalued_lot.lot_number }}</div>
                                                <p class="auc-ai-card__reason">{{ aiIntelligence.most_undervalued_lot.reason }}</p>
                                                <div class="auc-muted" style="font-size:.75rem;">Quality {{ aiIntelligence.most_undervalued_lot.quality_score }} at {{ fmtMoney(aiIntelligence.most_undervalued_lot.price) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="auc-ai-card">
                                                <div class="auc-ai-card__label"><el-icon><UserFilled /></el-icon> Highest Demand Lot</div>
                                                <div class="auc-ai-card__value">{{ aiIntelligence.highest_demand_lot.lot_number }}</div>
                                                <p class="auc-ai-card__reason">{{ aiIntelligence.highest_demand_lot.reason }}</p>
                                                <div class="auc-muted" style="font-size:.75rem;">{{ aiIntelligence.highest_demand_lot.bidder_count }} bidders · {{ aiIntelligence.highest_demand_lot.bid_count }} bids</div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="auc-ai-stat"><span>Predicted Winning Bid</span><strong>{{ fmtMoney(aiIntelligence.predicted_winning_bid) }}</strong></div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="auc-ai-stat"><span>Suggested Max Bid</span><strong>{{ fmtMoney(aiIntelligence.suggested_maximum_bid) }}</strong></div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="auc-ai-stat"><span>Confidence</span><strong>{{ aiIntelligence.confidence }}%</strong></div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="auc-ai-stat"><span>Model</span><strong style="font-size:.75rem;">Heuristic</strong></div>
                                        </div>
                                    </div>
                                    <p class="auc-ai-note"><el-icon><InfoFilled /></el-icon> {{ aiIntelligence.note }}</p>
                                </template>
                            </div>
                        </section>

                        <!-- SECTION 10 — Leaderboard -->
                        <section class="auc-section auc-section--last">
                            <div class="auc-section-head"><el-icon class="auc-section-icon"><Trophy /></el-icon><h2 class="auc-title">Auction Leaderboard</h2></div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="auc-card h-100">
                                        <div class="auc-card-title mb-2"><el-icon class="auc-card-icon"><Medal /></el-icon> Top Buyers</div>
                                        <div v-if="!leaderboard.top_buyers.length" class="auc-empty">No bids placed yet.</div>
                                        <div v-for="(b, i) in leaderboard.top_buyers" :key="b.name + i" class="auc-leader-row">
                                            <span class="auc-leader-rank">{{ i + 1 }}</span>
                                            <div class="flex-grow-1">
                                                <div class="auc-item-name">{{ b.name }}</div>
                                                <div class="auc-muted" style="font-size:.6875rem;">{{ cap(b.role) }} · {{ b.bids_placed }} bids</div>
                                            </div>
                                            <strong>{{ fmtMoney(b.total_bid_value) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="auc-card h-100">
                                        <div class="auc-card-title mb-2"><el-icon class="auc-card-icon"><Trophy /></el-icon> Top Sellers</div>
                                        <div v-if="!leaderboard.top_sellers.length" class="auc-empty">No lots listed yet.</div>
                                        <div v-for="(s, i) in leaderboard.top_sellers" :key="s.name + i" class="auc-leader-row">
                                            <span class="auc-leader-rank">{{ i + 1 }}</span>
                                            <div class="flex-grow-1">
                                                <div class="auc-item-name">{{ s.name }}</div>
                                                <div class="auc-muted" style="font-size:.6875rem;">{{ cap(s.role) }} · {{ s.lots_listed }} lots</div>
                                            </div>
                                            <strong>{{ fmtMoney(s.total_listed_value) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="auc-card">
                                        <div class="auc-card-title mb-2"><el-icon class="auc-card-icon"><Coin /></el-icon> Largest Auction</div>
                                        <template v-if="leaderboard.largest_auction">
                                            <div class="auc-spec"><span>Lot</span><strong>{{ leaderboard.largest_auction.lot_number }}</strong></div>
                                            <div class="auc-spec"><span>Total Bid Value</span><strong>{{ fmtMoney(leaderboard.largest_auction.total_bid_value) }}</strong></div>
                                            <div class="auc-spec"><span>Bids</span><strong>{{ leaderboard.largest_auction.bid_count }}</strong></div>
                                        </template>
                                        <div v-else class="auc-empty">No bids placed yet.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="auc-card">
                                        <div class="auc-card-title mb-2"><el-icon class="auc-card-icon"><Grid /></el-icon> Most Active Origins</div>
                                        <div v-if="!leaderboard.most_active_origins.length" class="auc-empty">No data yet.</div>
                                        <div v-for="o in leaderboard.most_active_origins" :key="o.label" class="auc-spec"><span>{{ cap(o.label) }}</span><strong>{{ o.lots }} lots</strong></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>

                    <!-- ══════════════════════════════════════════════════
                         Right sidebar — filters
                         ══════════════════════════════════════════════════ -->
                    <div class="col-12 col-xl-3 order-1 order-xl-2">
                        <div class="auc-sidebar">
                            <div class="auc-card">
                                <div class="auc-card-title mb-3"><el-icon class="auc-card-icon"><Filter /></el-icon> Filters</div>

                                <div class="auc-field">
                                    <label>Origin</label>
                                    <select v-model="filters.origin" class="auc-select w-100">
                                        <option value="">All Origins</option>
                                        <option v-for="o in filterOptions.origins" :key="o" :value="o">{{ cap(o) }}</option>
                                    </select>
                                </div>
                                <div class="auc-field">
                                    <label>Variety</label>
                                    <select v-model="filters.variety" class="auc-select w-100">
                                        <option value="">All Varieties</option>
                                        <option v-for="v in filterOptions.varieties" :key="v" :value="v">{{ cap(v) }}</option>
                                    </select>
                                </div>
                                <div class="auc-field">
                                    <label>Process</label>
                                    <select v-model="filters.process" class="auc-select w-100">
                                        <option value="">All Processes</option>
                                        <option v-for="p in filterOptions.processes" :key="p" :value="p">{{ p }}</option>
                                    </select>
                                </div>
                                <div class="auc-field">
                                    <label>Grade</label>
                                    <select v-model="filters.grade" class="auc-select w-100">
                                        <option value="">All Grades</option>
                                        <option v-for="g in filterOptions.grades" :key="g" :value="g">{{ g }}</option>
                                    </select>
                                </div>
                                <div class="auc-field">
                                    <label>Auction Status</label>
                                    <select v-model="filters.status" class="auc-select w-100">
                                        <option value="">All Statuses</option>
                                        <option v-for="s in filterOptions.statuses" :key="s" :value="s">{{ cap(s.replace('_',' ')) }}</option>
                                    </select>
                                </div>
                                <div class="auc-field mb-0">
                                    <label>Minimum Cup Score</label>
                                    <input v-model="filters.minCupScore" type="number" class="auc-search-input" style="padding-left:10px;width:100%;" placeholder="e.g. 84">
                                </div>
                            </div>

                            <div class="auc-card mt-3">
                                <Calendar :events="calendarEvents" title="My Calendar" />
                            </div>

                            <div class="auc-card mt-3">
                                <div class="auc-card-title mb-2"><el-icon class="auc-card-icon"><View /></el-icon> Watchlist</div>
                                <p v-if="!watched.size" class="auc-empty mb-0">You're not watching any lots yet.</p>
                                <div v-for="lot in featuredLots.filter((l) => watched.has(l.id))" :key="lot.id" class="auc-spec">
                                    <span>{{ lot.lot_number }}</span><strong>{{ fmtMoney(lot.current_bid ?? lot.starting_price) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             Lot details modal
             ══════════════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <div v-if="activeLot" class="auc-modal-overlay" @click.self="closeLot">
                <div class="auc-modal">
                    <button type="button" class="auc-modal__close" @click="closeLot"><el-icon><Close /></el-icon></button>

                    <div class="auc-modal__gallery">
                        <img v-if="activeLot.image" :src="activeLot.image" :alt="activeLot.lot_name">
                        <el-icon v-else style="font-size:2.5rem;"><Picture /></el-icon>
                    </div>

                    <div class="auc-modal__body">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
                            <div>
                                <div class="auc-lot-card__lot">{{ activeLot.lot_number }}</div>
                                <h3 class="auc-modal__title mb-0">{{ activeLot.lot_name ?? 'Unnamed Lot' }}</h3>
                            </div>
                            <span class="auc-badge" :class="statusCls(activeLot.status)">{{ cap(activeLot.status?.replace('_',' ')) }}</span>
                        </div>

                        <p v-if="activeLot.description" class="auc-muted" style="font-size:.8438rem;">{{ activeLot.description }}</p>

                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3"><div class="auc-spec-block"><span>Origin</span><strong>{{ cap(activeLot.origin_country) }}</strong></div></div>
                            <div class="col-6 col-md-3"><div class="auc-spec-block"><span>Grower</span><strong>{{ activeLot.grower ?? '—' }}</strong></div></div>
                            <div class="col-6 col-md-3"><div class="auc-spec-block"><span>Process</span><strong>{{ activeLot.process ?? '—' }}</strong></div></div>
                            <div class="col-6 col-md-3"><div class="auc-spec-block"><span>Grade</span><strong>{{ activeLot.grade ?? '—' }}</strong></div></div>
                            <div class="col-6 col-md-3"><div class="auc-spec-block"><span>Moisture</span><strong>{{ activeLot.moisture_content != null ? activeLot.moisture_content + '%' : '—' }}</strong></div></div>
                            <div class="col-6 col-md-3"><div class="auc-spec-block"><span>Defect Count</span><strong>{{ activeLot.defect_count ?? '—' }}</strong></div></div>
                            <div class="col-6 col-md-3"><div class="auc-spec-block"><span>Drying Method</span><strong>{{ activeLot.drying_method ?? '—' }}</strong></div></div>
                            <div class="col-6 col-md-3"><div class="auc-spec-block"><span>Packaging</span><strong>{{ activeLot.packaging_type ?? '—' }}</strong></div></div>
                        </div>

                        <div v-if="activeLot.cupping_notes || activeLot.flavor_notes" class="auc-modal__notes mb-3">
                            <div v-if="activeLot.flavor_notes"><strong>Flavor:</strong> {{ activeLot.flavor_notes }}</div>
                            <div v-if="activeLot.cupping_notes"><strong>Cupping Notes:</strong> {{ activeLot.cupping_notes }}</div>
                        </div>

                        <div class="auc-modal__section-title">Traceability Timeline</div>
                        <div class="auc-timeline mb-3">
                            <div v-for="(step, i) in activeLot.timeline" :key="i" class="auc-timeline__step">
                                <span class="auc-timeline__dot"></span>
                                <div><strong>{{ step.label }}</strong><span class="auc-muted"> · {{ step.ago }}</span></div>
                            </div>
                        </div>

                        <div class="auc-modal__section-title">Bid History</div>
                        <div v-if="!activeLot.bid_history.length" class="auc-empty mb-3">No bids placed on this lot yet.</div>
                        <div v-else class="mb-3">
                            <div v-for="(b, i) in activeLot.bid_history" :key="i" class="auc-my-row">
                                <div><strong>{{ b.bidder }}</strong><span class="auc-muted"> · {{ b.placed_ago }}</span></div>
                                <strong>{{ fmtMoney(b.amount) }}</strong>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn auc-btn-outline flex-fill" :class="{ 'auc-btn-outline--active': watched.has(activeLot.id) }" @click="toggleWatch(activeLot.id)">
                                <el-icon><View /></el-icon> {{ watched.has(activeLot.id) ? 'Watching' : 'Watch' }}
                            </button>
                            <Link :href="route('bid.place', activeLot.id)" class="btn auc-btn-primary flex-fill">
                                <el-icon><Coin /></el-icon> Place Bid — {{ fmtMoney(activeLot.current_bid ?? activeLot.starting_price) }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.auc-page {
    --green: #004532;
    --green-dark: #002e20;
    --gold: #c8862a;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #fff;
    color: var(--on-surface);
    min-height: 100%;
}
.auc-muted { color: var(--on-surface-var); font-size: .8125rem; }
.auc-item-name { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.auc-empty { font-size: .8125rem; color: var(--on-surface-var); text-align: center; padding: 1.5rem 0; }

/* ── Top bar ─────────────────────────────────────────────────────────── */
.auc-topbar { position: sticky; top: 0; z-index: 40; background: #fff; border-bottom: 1px solid var(--border); }
.auc-search-wrap { position: relative; display: flex; align-items: center; }
.auc-search-icon { position: absolute; left: 12px; color: var(--on-surface-var); font-size: 14px; }
.auc-search-ai { position: absolute; right: 12px; color: var(--gold); font-size: 14px; }
.auc-search-input { width: 100%; height: 38px; border: 1px solid var(--border); border-radius: 10px; padding: 0 36px; font-size: .8125rem; outline: none; background: var(--surface-low); }
.auc-search-input:focus { border-color: var(--green); background: #fff; }
.auc-search-wrap--table { flex: 1; min-width: 220px; }
.auc-search-wrap--table .auc-search-input { height: 34px; background: #fff; }

.auc-search-prompts { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
.auc-prompt-chip { font-size: .6875rem; padding: 3px 10px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface-var); cursor: pointer; white-space: nowrap; }
.auc-prompt-chip:hover { border-color: var(--green); color: var(--green); }

.auc-quick-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.auc-qa-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: 7px 12px; border-radius: 8px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; text-decoration: none; }
.auc-qa-btn:hover { background: #eef2f1; border-color: var(--green); }
.auc-qa-btn--active { background: var(--green); border-color: var(--green); color: #fff; }

/* ── Sections ────────────────────────────────────────────────────────── */
.auc-section { padding: 1.25rem 0; border-bottom: 1px solid var(--border); }
.auc-section--last { border-bottom: none; }
.auc-section-head { display: flex; align-items: center; gap: 8px; margin-bottom: .875rem; }
.auc-section-icon { width: 28px; height: 28px; border-radius: 8px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.auc-title { font-size: 1rem; font-weight: 800; color: var(--on-surface); margin: 0; }
.auc-count { margin-left: auto; font-size: .75rem; font-weight: 600; color: var(--on-surface-var); }

.auc-live-dot { width: 10px; height: 10px; border-radius: 50%; background: #dc2626; box-shadow: 0 0 0 rgba(220,38,38,.5); animation: auc-pulse 1.6s infinite; }
@keyframes auc-pulse { 0% { box-shadow: 0 0 0 0 rgba(220,38,38,.5); } 70% { box-shadow: 0 0 0 8px rgba(220,38,38,0); } 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0); } }

/* ── Hero banner ─────────────────────────────────────────────────────── */
.auc-hero-banner { display: flex; align-items: flex-start; gap: 12px; background: linear-gradient(135deg, rgba(0,69,50,.06), rgba(200,134,42,.06)); border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; }
.auc-hero-banner__icon { width: 34px; height: 34px; border-radius: 9px; background: var(--green); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.auc-hero-banner__kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--green); margin-bottom: 3px; }
.auc-hero-banner__text { font-size: .8438rem; color: var(--on-surface); line-height: 1.5; }

/* ── KPI ─────────────────────────────────────────────────────────────── */
.auc-kpi { border: 1px solid var(--border); border-radius: 10px; padding: .875rem; background: #fff; text-align: center; }
.auc-kpi__icon { color: var(--green); font-size: 16px; margin-bottom: 4px; }
.auc-kpi__value { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); line-height: 1.1; }
.auc-kpi__label { font-size: .625rem; font-weight: 600; color: var(--on-surface-var); margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }

/* ── Cards ───────────────────────────────────────────────────────────── */
.auc-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem; }
.auc-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: .8438rem; font-weight: 700; color: var(--on-surface); }
.auc-card-icon { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

.auc-field { margin-bottom: 12px; }
.auc-field label { display: block; font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.auc-select { height: 34px; border: 1px solid var(--border); border-radius: 8px; padding: 0 10px; font-size: .8125rem; color: var(--on-surface); background: #fff; outline: none; cursor: pointer; }
.auc-select:focus { border-color: var(--green); }

.auc-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; }
.auc-btn-primary:hover { background: var(--green-dark); color: #fff; }
.auc-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
.auc-btn-outline:hover { background: var(--surface-low); }
.auc-btn-outline--active { background: var(--green); border-color: var(--green); color: #fff; }

.auc-sidebar { position: sticky; top: 90px; display: flex; flex-direction: column; gap: 0; }

/* ── Featured lot cards ──────────────────────────────────────────────── */
.auc-lot-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; overflow: hidden; cursor: pointer; transition: box-shadow .15s, transform .15s; display: flex; flex-direction: column; }
.auc-lot-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); transform: translateY(-1px); }
.auc-lot-card__image { position: relative; height: 140px; background: var(--surface-low); display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 2rem; }
.auc-lot-card__image img { width: 100%; height: 100%; object-fit: cover; }
.auc-lot-card__ai { position: absolute; top: 8px; right: 8px; background: rgba(0,69,50,.9); color: #fff; font-size: .625rem; font-weight: 700; padding: 3px 8px; border-radius: 999px; }
.auc-lot-card__body { padding: .875rem; flex: 1; display: flex; flex-direction: column; }
.auc-lot-card__lot { font-size: .625rem; font-weight: 700; color: var(--gold); text-transform: uppercase; letter-spacing: .04em; }
.auc-lot-card__name { font-size: .9375rem; font-weight: 800; color: var(--on-surface); }
.auc-lot-card__meta { font-size: .75rem; color: var(--on-surface-var); }

.auc-tags { display: flex; flex-wrap: wrap; gap: 5px; margin: 8px 0; }
.auc-tag { font-size: .625rem; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: var(--surface-low); color: var(--on-surface-var); border: 1px solid var(--border); }

.auc-lot-card__price-row { display: flex; align-items: flex-end; justify-content: space-between; background: var(--surface-low); border-radius: 10px; padding: 8px 10px; margin: 4px 0; }
.auc-lot-card__price-label { font-size: .625rem; color: var(--on-surface-var); font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
.auc-lot-card__price { font-size: 1.0625rem; font-weight: 800; color: var(--green); }
.auc-lot-card__increment { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }

.auc-lot-card__stats { display: flex; justify-content: space-between; font-size: .6875rem; color: var(--on-surface-var); margin-top: 4px; }
.auc-lot-card__stats span { display: inline-flex; align-items: center; gap: 4px; }

/* ── Feed ────────────────────────────────────────────────────────────── */
.auc-feed { display: flex; flex-direction: column; gap: 2px; }
.auc-feed-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--surface-low); }
.auc-feed-row:last-child { border-bottom: none; }
.auc-feed-row__avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; }
.auc-feed-row__top { font-size: .8125rem; color: var(--on-surface); }
.auc-feed-row__amount { color: var(--green); }

/* ── Table ───────────────────────────────────────────────────────────── */
.auc-table thead th { background: var(--surface-low); font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); padding: 8px 10px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.auc-table tbody td { padding: 8px 10px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; white-space: nowrap; }
.auc-table-row { cursor: pointer; }
.auc-table-row:hover { background: var(--surface-low); }
.auc-table tbody tr:last-child td { border-bottom: none; }

.auc-grade-pill { display: inline-flex; border-radius: 4px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

.auc-my-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: .8125rem; }
.auc-my-row:last-child { border-bottom: none; }

/* ── Quality radar ───────────────────────────────────────────────────── */
.auc-radar-wrap { height: 260px; }
.auc-quality-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: .8125rem; }
.auc-quality-row span { width: 90px; flex-shrink: 0; color: var(--on-surface-var); }
.auc-quality-row strong { width: 32px; text-align: right; flex-shrink: 0; }
.auc-bar-track { flex: 1; height: 6px; border-radius: 999px; background: var(--surface-low); overflow: hidden; }
.auc-bar-fill { height: 100%; background: var(--green); border-radius: 999px; }

/* ── Charts ──────────────────────────────────────────────────────────── */
.auc-chart-wrap { height: 220px; }

/* ── Spec rows ───────────────────────────────────────────────────────── */
.auc-spec { display: flex; align-items: center; justify-content: space-between; font-size: .75rem; color: var(--on-surface-var); padding: 5px 0; border-bottom: 1px solid var(--surface-low); }
.auc-spec:last-child { border-bottom: none; }
.auc-spec strong { color: var(--on-surface); font-weight: 700; }

/* ── AI panel ────────────────────────────────────────────────────────── */
.auc-ai-panel { background: linear-gradient(135deg, rgba(0,69,50,.04), rgba(200,134,42,.04)); border: 1px solid var(--border); border-radius: 14px; padding: 1.125rem; }
.auc-ai-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem; height: 100%; }
.auc-ai-card__label { display: inline-flex; align-items: center; gap: 6px; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--gold); }
.auc-ai-card__value { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); margin: 4px 0; }
.auc-ai-card__reason { font-size: .75rem; color: var(--on-surface-var); line-height: 1.5; margin-bottom: 4px; }
.auc-ai-stat { background: #fff; border: 1px solid var(--border); border-radius: 10px; padding: .75rem; text-align: center; }
.auc-ai-stat span { display: block; font-size: .625rem; font-weight: 600; color: var(--on-surface-var); text-transform: uppercase; letter-spacing: .03em; margin-bottom: 4px; }
.auc-ai-stat strong { font-size: 1rem; color: var(--on-surface); }
.auc-ai-note { display: flex; align-items: flex-start; gap: 6px; font-size: .75rem; color: var(--on-surface-var); margin: 12px 0 0; }

/* ── Leaderboard ─────────────────────────────────────────────────────── */
.auc-leader-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.auc-leader-row:last-child { border-bottom: none; }
.auc-leader-rank { width: 22px; height: 22px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); font-size: .6875rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Badges ──────────────────────────────────────────────────────────── */
.auc-badge { display: inline-flex; border-radius: 999px; font-size: .625rem; font-weight: 700; padding: 3px 9px; white-space: nowrap; }
.auc-badge--green { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.auc-badge--amber { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.auc-badge--blue { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.auc-badge--muted { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

/* ── Modal ───────────────────────────────────────────────────────────── */
.auc-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.55); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.auc-modal { background: #fff; border-radius: 16px; max-width: 640px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative; }
.auc-modal__close { position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,.9); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; z-index: 2; cursor: pointer; }
.auc-modal__gallery { height: 200px; background: var(--surface-low); display: flex; align-items: center; justify-content: center; color: #cbd5e1; border-radius: 16px 16px 0 0; overflow: hidden; }
.auc-modal__gallery img { width: 100%; height: 100%; object-fit: cover; }
.auc-modal__body { padding: 1.25rem; }
.auc-modal__title { font-size: 1.125rem; font-weight: 800; }
.auc-modal__section-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); margin: 14px 0 8px; }
.auc-modal__notes { background: var(--surface-low); border-radius: 10px; padding: .75rem; font-size: .8125rem; display: flex; flex-direction: column; gap: 4px; }

.auc-spec-block { background: var(--surface-low); border-radius: 8px; padding: 8px 10px; }
.auc-spec-block span { display: block; font-size: .625rem; color: var(--on-surface-var); font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
.auc-spec-block strong { font-size: .8125rem; color: var(--on-surface); }

.auc-timeline { position: relative; padding-left: 16px; border-left: 2px solid var(--surface-low); display: flex; flex-direction: column; gap: 12px; }
.auc-timeline__step { position: relative; font-size: .8125rem; }
.auc-timeline__dot { position: absolute; left: -21px; top: 4px; width: 8px; height: 8px; border-radius: 50%; background: var(--green); }

@media (max-width: 1199.98px) {
    .auc-sidebar { position: static; }
}
</style>
