<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import {
    CirclePlus, DocumentAdd, Shop, Connection, DocumentChecked, Trophy, Refresh,
    CircleCheckFilled, Tickets, Box, Wallet,
    CircleCheck, Cherry, Document, Lock, Timer, TrendCharts, Notification, Plus,
    Check, Promotion, Coin, Medal, MapLocation, ArrowRight,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    lots: {
        type: Object,
        default: () => ({ data: [], meta: { current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 } }),
    },
    openOffersCount: { type: Number, default: 0 },
});

const visibleLotPages = computed(() => {
    const cur = props.lots.meta.current_page || 1;
    const last = props.lots.meta.last_page || 1;
    const pages = [];
    for (let p = Math.max(1, cur - 1); p <= Math.min(last, cur + 1); p++) pages.push(p);
    return pages;
});
const lotRangeSummary = computed(() => {
    const { from = 0, to = 0, total = 0 } = props.lots.meta;
    return total > 0 ? `Showing ${from}-${to} of ${total} live physical lots` : 'No live listings yet.';
});

function formatQty(quantity, unit) {
    const value = Number(quantity) || 0;
    return `${value.toLocaleString(undefined, { maximumFractionDigits: 2 })} ${unit || 'kg'}`;
}

function formatMoney(amount, currency) {
    const value = Number(amount) || 0;
    return `${currency || 'USD'} ${value.toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
}

function pricingLabel(pricingType) {
    return pricingType === 'auction' ? 'Live Auction' : pricingType === 'negotiable' ? 'Negotiable' : 'Fixed Price';
}

function pricingTone(pricingType) {
    return pricingType === 'auction' ? 'secondary' : pricingType === 'negotiable' ? 'neutral' : 'primary';
}

/* ── Dummy trading-floor content — illustrative only ────────────────── */
const headerActions = [
    { icon: CirclePlus, label: 'Sell Coffee', tone: 'muted', accent: 'secondary' },
    { icon: DocumentAdd, label: 'Create RFQ', tone: 'muted', accent: 'primary' },
];

const exchangeTabs = computed(() => [
    { key: 'market', icon: Shop, label: 'Market', count: '86 Lots', routeName: 'exchange.index' },
    { key: 'offers', icon: Connection, label: 'Offers', count: `${props.openOffersCount} Open`, routeName: 'exchange.offers' },
    { key: 'rfqs', icon: DocumentChecked, label: 'RFQs', count: '18 Requests', routeName: 'rfq.index' },
    { key: 'auctions', icon: Trophy, label: 'Auctions', count: '4 Live', routeName: 'auction.index' },
    { key: 'trades', icon: Refresh, label: 'My Trades', count: '3 In-Flight', routeName: 'orders.index' },
]);

function goToTab(tab) {
    if (route().current(tab.routeName)) {
        return;
    }
    router.visit(route(tab.routeName));
}

const kpiStats = [
    { icon: Box, label: 'Available Volume', value: '1,284', unit: 'MT', note: 'Physical inventory verified in warehouse' },
    { icon: Tickets, label: 'Active Lots', value: '86', unit: 'Lots', note: 'Ready for immediate contract allocation' },
    { icon: Coin, label: 'Open Spot Offers', value: '24', unit: 'Tranches', note: 'Avg pricing: $4.15/kg FOB Mombasa' },
    { icon: DocumentChecked, label: 'Active Buyer RFQs', value: '18', unit: 'Demand Orders', note: 'Aggregated bid volume: 540 MT', strong: true },
];

const provenanceChain = [
    { n: '1', title: 'Origin Farm Clusters', value: 'Mukono Co-op', note: '342 smallholder plots', pill: 'GPS Poly Validated', tone: 'primary' },
    { n: '2', title: 'Collection Center', value: 'COL-0124 (Mukono)', note: 'Cherry intake: 18.2% Brix', pill: 'Batch Sealed Oct 12', tone: 'neutral' },
    { n: '3', title: 'Dry Milling Facility', value: 'BAT-0082 Hulling', note: 'Screen 18 graded', pill: 'Gravity Separated', tone: 'neutral' },
    { n: '4', title: 'Export Lot Bond', value: 'LOT-UG-8821', note: '120 MT containerized', pill: 'UCDA Stamp #UG-998', tone: 'primary' },
];

const cuppingStats = [
    { label: 'Moisture', value: '11.2%', note: 'Standard <12.5%' },
    { label: 'Screen 18', value: '92.4%', note: 'High retention' },
    { label: 'Defects', value: '0 Primary', note: '3 secondary/350g' },
];

const sensoryBars = [
    { label: 'Fragrance / Aroma (Cocoa, Cedar)', value: '8.50', pct: 85, tone: 'primary' },
    { label: 'Clean Cup / Uniformity', value: '9.00', pct: 90, tone: 'primary' },
    { label: 'Body & Mouthfeel', value: '8.75', pct: 87.5, tone: 'secondary' },
];

const landedCostRows = [
    { label: 'Base FOB Price (Mombasa)', value: '$4.200 / kg', strong: true },
    { label: 'Inland Transit (Kampala to Mombasa)', value: '$0.080 / kg' },
    { label: 'Ocean Freight (Mombasa to Jebel Ali)', value: '$0.140 / kg' },
    { label: 'Cargo Marine Insurance (0.45%)', value: '$0.020 / kg' },
    { label: 'Destination Terminal & Handling', value: '$0.040 / kg' },
];

const negotiationCards = [
    { icon: Refresh, tone: 'primary', title: 'Pending Offers (6)', tag: '2 Needing Action', body: 'Counter-offer from Hamburg Roasters on LOT-UG-8821 ($4.12/kg for 60 MT).', actions: [{ label: 'Accept $4.12', tone: 'primary' }, { label: 'Counter', tone: 'muted' }] },
    { icon: Tickets, tone: 'secondary', title: 'Active RFQs (18)', tag: 'High Match', body: 'Dubai institutional buyer seeking 180 MT Screen 18 Fine Robusta for Q1 2025 delivery.', actions: [{ label: 'Submit Tender Quote', tone: 'secondary' }] },
    { icon: Timer, tone: 'error', title: 'Live Auction #42', tag: '42m Remaining', body: 'Bugisu AA Washed (45 MT). Leading bid: $5.25/kg by Nordic Coffee Importers.', actions: [{ label: 'Enter Auction Room', tone: 'muted' }] },
];

const benchmarks = [
    { label: 'Robusta Benchmark (FOB MBA)', change: '+1.8%', value: '$4,150', unit: '/MT', note: '$4.15/kg' },
    { label: 'Arabica Benchmark (FOB MBA)', change: '+2.9%', value: '$5,620', unit: '/MT', note: '$5.62/kg' },
];

const depthStats = [
    { label: 'Buyer Demand', value: '540 MT', note: '18 Active RFQs', tone: 'primary' },
    { label: 'Floor Supply', value: '1,284 MT', note: '86 Physical Lots', tone: 'secondary' },
];

const activityFeed = [
    { icon: Plus, tone: 'primary', title: 'New Uganda Robusta lot listed (120 MT)', meta: 'Mukono Union', time: '3m ago' },
    { icon: Check, tone: 'neutral', title: 'Offer accepted on LOT-ET-902 ($6.70/kg)', meta: 'Rotterdam Buyer', time: '12m ago' },
    { icon: Promotion, tone: 'neutral', title: 'New RFQ: Dubai Roaster 40 MT Arabica', meta: 'FOB Mombasa', time: '24m ago' },
    { icon: Trophy, tone: 'secondary', title: 'Auction ending: Bugisu AA (Bid: $5.25/kg)', meta: 'Mt Elgon', time: '42m remaining' },
];

const trustItems = [
    { icon: CircleCheckFilled, label: '100% KYC & AML Screened Traders' },
    { icon: Coin, label: 'Immutable Polygon Chain Custody' },
    { icon: Medal, label: 'SCAA & Q-Robusta Graded Lots' },
    { icon: MapLocation, label: 'EUDR Polygon Geolocation Included' },
    { icon: Wallet, label: 'Stanbic Tier-1 Bank Escrow Custody' },
];

function placeholderAction(label) {
    ElMessage.info(`${label} (dummy preview).`);
}
</script>

<template>
    <MainLayout title="Coffee Exchange">
        <!-- 1. STATUS STRIP -->
        <section class="ex-status-line">
            <div class="ex-flex-icon">
                <span class="ex-strong ex-icon--primary">Trading Floor</span>
                <span class="ex-sep">/</span>
                <span class="ex-strong">Physical Spot &amp; Forward Exchange</span>
                <span class="ex-sep">/</span>
                <span class="ex-tag-mini ex-tag-mini--primary">Market Open</span>
            </div>
            <div class="ex-flex-icon">
                <span class="ex-flex-icon"><span class="ex-dot ex-dot--pulse"></span> <span class="dp-caption dp-mono ex-strong">UTC 11:42:08</span></span>
                <span class="dp-caption dp-mono ex-muted ex-hide-sm">Escrow Protocol v4.2 Active</span>
                <span class="dp-caption dp-mono ex-icon--primary ex-strong ex-hide-sm">100% EUDR Cleared Lots Available</span>
            </div>
        </section>

        <!-- 2. HEADER & QUICK ACTIONS -->
        <section class="ex-card ex-hero">
            <div class="ex-hero__top">
                <div>
                    <div class="ex-title-row">
                        <h1 class="dp-display-md">Coffee Exchange</h1>
                        <span class="ex-tag-mini">Physical Spot &amp; Forward</span>
                    </div>
                    <p class="dp-body-md ex-muted">Discover authenticated coffee lots, connect directly with licensed origin aggregators, and turn institutional market interest into legally binding escrow trades.</p>
                </div>
                <div class="ex-hero__actions">
                    <button v-for="a in headerActions" :key="a.label" type="button" class="ex-btn" :class="`ex-btn--${a.tone}`" @click="placeholderAction(a.label)">
                        <el-icon :size="16"><component :is="a.icon" /></el-icon>
                        <span>{{ a.label }}</span>
                    </button>
                </div>
            </div>

            <div class="ex-tabs">
                <button v-for="tab in exchangeTabs" :key="tab.key" type="button" class="ex-tab" :class="{ 'ex-tab--active': route().current(tab.routeName) }" @click="goToTab(tab)">
                    <el-icon :size="16"><component :is="tab.icon" /></el-icon>
                    <span>{{ tab.label }}</span>
                    <span class="ex-tab__count">{{ tab.count }}</span>
                </button>
            </div>
        </section>

        <!-- 3. KEY METRICS -->
        <section class="ex-kpi-grid">
            <div v-for="kpi in kpiStats" :key="kpi.label" class="ex-kpi">
                <div class="ex-kpi__head">
                    <span class="dp-label-md ex-strong">{{ kpi.label }}</span>
                    <el-icon :size="18" class="ex-muted"><component :is="kpi.icon" /></el-icon>
                </div>
                <div class="ex-kpi__value">
                    <span class="ex-kpi__num">{{ kpi.value }}</span>
                    <span class="dp-body-md ex-muted">{{ kpi.unit }}</span>
                </div>
                <p class="dp-caption" :class="kpi.strong ? 'ex-strong' : 'ex-muted'">{{ kpi.note }}</p>
            </div>
        </section>

        <!-- 4. MAIN TWO-COLUMN LAYOUT -->
        <section class="ex-grid-12">
            <div class="ex-col-main">
                <!-- Lots table -->
                <div class="ex-card">
                    <div class="ex-card__head">
                        <div>
                            <h2 class="dp-headline-md">Available Physical Coffee Lots</h2>
                            <p class="dp-caption ex-muted">Real-time verified origin inventory backed by phytosanitary &amp; cupping documentation.</p>
                        </div>
                    </div>
                    <div class="ex-table-wrap">
                        <table class="ex-table">
                            <thead>
                                <tr><th>Coffee &amp; Lot</th><th>Origin &amp; Quality</th><th>Volume &amp; Price</th><th>Seller &amp; Corridor</th><th class="ex-right">Actions</th></tr>
                            </thead>
                            <tbody>
                                <tr v-if="!lots.data.length">
                                    <td colspan="5" class="ex-center dp-caption ex-muted">No live listings yet.</td>
                                </tr>
                                <tr v-for="lot in lots.data" :key="lot.id">
                                    <td>
                                        <div class="ex-strong">{{ lot.name }}</div>
                                        <div class="ex-flex-icon"><span class="dp-mono ex-icon--primary ex-caption-sm">{{ lot.lot_code || `#${lot.id}` }}</span><span class="ex-tag-mini" :class="`ex-tag-mini--${pricingTone(lot.pricing_type)}`">{{ pricingLabel(lot.pricing_type) }}</span></div>
                                    </td>
                                    <td>
                                        <div class="ex-strong">{{ lot.origin || 'Origin unverified' }}</div>
                                        <div class="dp-caption ex-muted">{{ [lot.region, lot.process].filter(Boolean).join(' · ') || '—' }}</div>
                                        <div v-if="lot.quality_score || lot.grade" class="dp-caption ex-icon--primary dp-mono">
                                            <template v-if="lot.quality_score">{{ lot.quality_score }} CQI</template>
                                            <template v-if="lot.quality_score && lot.grade"> &middot; </template>
                                            <template v-if="lot.grade">{{ lot.grade }}</template>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ex-strong dp-mono">{{ formatQty(lot.quantity, lot.unit) }}</div>
                                        <div v-if="lot.quantity_bags" class="dp-caption ex-muted dp-mono">{{ lot.quantity_bags }} bags</div>
                                        <div class="dp-caption ex-strong dp-mono">{{ formatMoney(lot.price_per_kg, lot.currency) }}/kg <span class="ex-muted">&middot; {{ formatMoney(lot.total_price, lot.currency) }}</span></div>
                                    </td>
                                    <td>
                                        <div class="ex-flex-icon"><el-icon :size="13" class="ex-icon--primary"><component :is="lot.is_traceable ? CircleCheck : Cherry" /></el-icon><span class="ex-strong">{{ lot.seller_name || 'Verified Seller' }}</span></div>
                                        <div v-if="lot.highest_bid" class="dp-caption ex-icon--primary">Highest bid {{ formatMoney(lot.highest_bid, lot.currency) }}</div>
                                        <span class="ex-tag-mini ex-tag-mini--corridor">{{ lot.delivery_location || 'Corridor TBC' }}</span>
                                    </td>
                                    <td class="ex-right">
                                        <div class="ex-actions-inline ex-actions-inline--end">
                                            <button type="button" class="ex-btn ex-btn--muted ex-btn--sm" @click="router.visit(route('market.show', lot.id))">Inspect</button>
                                            <button type="button" class="ex-btn ex-btn--sm" :class="lot.pricing_type === 'auction' ? 'ex-btn--secondary' : lot.pricing_type === 'negotiable' ? 'ex-btn--muted' : 'ex-btn--primary'" @click="router.visit(route('market.show', lot.id))">{{ lot.pricing_type === 'auction' ? 'Place Bid' : 'Buy Now' }}</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="ex-footline">
                        <span class="dp-caption ex-muted">{{ lotRangeSummary }}</span>
                        <div v-if="lots.meta.last_page > 1" class="ex-actions-inline">
                            <Link
                                :href="route('exchange.index', { page: Math.max(1, lots.meta.current_page - 1) })"
                                class="ex-btn ex-btn--muted ex-btn--sm"
                                :class="{ 'ex-btn--disabled': lots.meta.current_page <= 1 }"
                            >Prev</Link>
                            <Link
                                v-for="page in visibleLotPages"
                                :key="page"
                                :href="route('exchange.index', { page })"
                                class="ex-btn ex-btn--sm"
                                :class="page === lots.meta.current_page ? 'ex-btn--primary' : 'ex-btn--muted'"
                            >{{ page }}</Link>
                            <Link
                                :href="route('exchange.index', { page: Math.min(lots.meta.last_page, lots.meta.current_page + 1) })"
                                class="ex-btn ex-btn--muted ex-btn--sm"
                                :class="{ 'ex-btn--disabled': lots.meta.current_page >= lots.meta.last_page }"
                            >Next</Link>
                        </div>
                    </div>
                </div>

                <!-- Selected lot quick view -->
                <div class="ex-card">
                    <div class="ex-card__head">
                        <div>
                            <div class="ex-flex-icon"><span class="ex-tag-mini ex-tag-mini--primary">Selected Inspection</span><h3 class="dp-headline-sm">Uganda Fine Robusta Screen 18 (LOT-UG-8821)</h3></div>
                            <p class="dp-caption ex-muted">Origin custody verification &amp; live landed cost simulator</p>
                        </div>
                        <button type="button" class="ex-btn ex-btn--muted" @click="placeholderAction('Download CQI Lab PDF')">
                            <el-icon :size="15"><Document /></el-icon> Download CQI Lab PDF
                        </button>
                    </div>

                    <div class="ex-provenance">
                        <div class="ex-flex-icon dp-label-md ex-muted ex-mb-sm"><el-icon :size="16" class="ex-icon--primary"><Connection /></el-icon> Immutable Custody Provenance Pipeline</div>
                        <div class="ex-provenance-grid">
                            <div v-for="p in provenanceChain" :key="p.n" class="ex-provenance-item">
                                <span class="dp-caption ex-muted">{{ p.n }}. {{ p.title }}</span>
                                <span class="ex-strong" :class="p.tone === 'primary' ? 'ex-icon--primary' : ''">{{ p.value }}</span>
                                <span class="dp-caption ex-muted">{{ p.note }}</span>
                                <span class="ex-tag-mini" :class="p.tone === 'primary' ? 'ex-tag-mini--primary' : ''">{{ p.pill }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="ex-specs-grid">
                        <div class="ex-specs-col">
                            <div class="ex-flex-icon-between">
                                <span class="dp-label-md ex-strong">Physical Cupping &amp; Lab Parameters</span>
                                <span class="dp-caption ex-icon--primary dp-mono">Lab Verified: Kampala Central</span>
                            </div>
                            <div class="ex-mini-stat-grid">
                                <div v-for="s in cuppingStats" :key="s.label" class="ex-mini-stat">
                                    <span class="dp-caption ex-muted">{{ s.label }}</span>
                                    <span class="dp-body-lg ex-strong dp-mono">{{ s.value }}</span>
                                    <span class="dp-caption ex-icon--primary">{{ s.note }}</span>
                                </div>
                            </div>
                            <div class="ex-sensory">
                                <span class="dp-label-md ex-strong">Sensory Attribute Breakdown (SCAA Protocol)</span>
                                <div v-for="b in sensoryBars" :key="b.label" class="ex-sensory-row">
                                    <div class="ex-flex-icon-between"><span class="dp-caption ex-muted">{{ b.label }}</span><span class="dp-caption dp-mono ex-strong">{{ b.value }}</span></div>
                                    <div class="ex-bar"><div class="ex-bar__fill" :class="`ex-bar__fill--${b.tone}`" :style="{ width: b.pct + '%' }"></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="ex-specs-col">
                            <div class="ex-cost-box">
                                <div class="ex-flex-icon-between">
                                    <span class="dp-label-md ex-strong">Live Landed Cost Simulator</span>
                                    <span class="ex-tag-mini">Destination: Jebel Ali (UAE)</span>
                                </div>
                                <div class="ex-cost-rows">
                                    <div v-for="r in landedCostRows" :key="r.label" class="ex-cost-row">
                                        <span class="dp-caption ex-muted">{{ r.label }}</span>
                                        <span class="dp-caption dp-mono" :class="r.strong ? 'ex-strong' : 'ex-on'">{{ r.value }}</span>
                                    </div>
                                </div>
                                <div class="ex-cost-total">
                                    <div><span class="dp-caption ex-muted">Estimated Landed Cost CIF</span><div class="dp-headline-sm ex-icon--primary">$4.480 <span class="dp-caption ex-muted">/kg</span></div></div>
                                    <div class="ex-right"><span class="dp-caption ex-muted">Total Consignment</span><div class="dp-body-lg ex-strong">$537,600 USD</div></div>
                                </div>
                            </div>
                            <div class="ex-actions-inline">
                                <button type="button" class="ex-btn ex-btn--primary ex-btn--grow" @click="placeholderAction('Proceed to Purchase Review')">Proceed to Purchase Review</button>
                                <button type="button" class="ex-btn ex-btn--muted" @click="placeholderAction('Make Bilateral Counter-Offer')">Make Bilateral Counter-Offer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Escrow guarantee banner -->
                <div class="ex-card ex-escrow-banner">
                    <div class="ex-flex-icon">
                        <el-icon :size="24" class="ex-icon--primary"><Lock /></el-icon>
                        <div>
                            <div class="dp-label-md ex-strong">Institutional Escrow Settlement Guarantee</div>
                            <p class="dp-caption ex-muted">All transactions execute via Stanbic Bank custody escrow. Funds stay secured until an independent SGS port inspection confirms volume, moisture, and grading at the Mombasa terminal.</p>
                        </div>
                    </div>
                    <div class="ex-actions-inline">
                        <span class="dp-caption dp-mono ex-icon--primary ex-strong ex-hide-sm">2-Step Dual Authorization</span>
                        <button type="button" class="ex-btn ex-btn--primary" @click="placeholderAction('View Legal Terms')">View Legal Terms</button>
                    </div>
                </div>

                <!-- Negotiation hub -->
                <div class="ex-card">
                    <div class="ex-card__head">
                        <div>
                            <h3 class="dp-headline-sm">Bilateral Negotiation &amp; Mechanism Hub</h3>
                            <p class="dp-caption ex-muted">Toggle active commercial workflows across RFQs, live bidding rooms, and offer counters.</p>
                        </div>
                        <span class="ex-tag-mini">3 Workspaces Active</span>
                    </div>
                    <div class="ex-negotiation-grid">
                        <div v-for="card in negotiationCards" :key="card.title" class="ex-negotiation-card">
                            <div>
                                <div class="ex-flex-icon-between">
                                    <span class="ex-flex-icon ex-strong"><el-icon :size="16" :class="`ex-icon--${card.tone}`"><component :is="card.icon" /></el-icon> {{ card.title }}</span>
                                    <span class="dp-caption dp-mono ex-strong" :class="`ex-icon--${card.tone}`">{{ card.tag }}</span>
                                </div>
                                <p class="dp-caption ex-muted">{{ card.body }}</p>
                            </div>
                            <div class="ex-actions-inline">
                                <button v-for="a in card.actions" :key="a.label" type="button" class="ex-btn ex-btn--sm" :class="`ex-btn--${a.tone}`" @click="placeholderAction(a.label)">{{ a.label }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="ex-col-side">
                <div class="ex-card">
                    <div class="ex-flex-icon-between">
                        <span class="ex-flex-icon dp-label-md ex-strong"><el-icon :size="16" class="ex-icon--primary"><TrendCharts /></el-icon> Live Physical Benchmarks</span>
                        <span class="dp-caption dp-mono ex-icon--primary ex-strong">Live Feed</span>
                    </div>
                    <div class="ex-benchmark-list">
                        <div v-for="b in benchmarks" :key="b.label" class="ex-benchmark">
                            <div class="ex-flex-icon-between"><span class="dp-caption ex-strong">{{ b.label }}</span><span class="dp-caption ex-icon--primary ex-strong">{{ b.change }}</span></div>
                            <div class="ex-flex-icon-between"><span class="dp-headline-sm dp-mono">{{ b.value }}<span class="dp-caption ex-muted">{{ b.unit }}</span></span><span class="dp-caption ex-muted dp-mono">{{ b.note }}</span></div>
                        </div>
                    </div>
                    <div class="ex-depth-grid">
                        <div v-for="d in depthStats" :key="d.label" class="ex-depth-stat">
                            <span class="dp-caption ex-muted">{{ d.label }}</span>
                            <span class="dp-body-lg ex-strong dp-mono">{{ d.value }}</span>
                            <span class="dp-caption" :class="`ex-icon--${d.tone}`">{{ d.note }}</span>
                        </div>
                    </div>
                    <button type="button" class="ex-link" @click="router.visit(route('market.active'))">
                        <span>Open Coffee Market Terminal</span>
                        <el-icon :size="15"><ArrowRight /></el-icon>
                    </button>
                </div>

                <div class="ex-card">
                    <div class="ex-flex-icon-between">
                        <span class="ex-flex-icon dp-label-md ex-strong"><el-icon :size="16" class="ex-icon--primary"><Notification /></el-icon> Exchange Activity Stream</span>
                        <span class="ex-dot ex-dot--pulse"></span>
                    </div>
                    <div class="ex-activity-list">
                        <div v-for="a in activityFeed" :key="a.title" class="ex-activity-item">
                            <span class="ex-activity-icon" :class="`ex-activity-icon--${a.tone}`"><el-icon :size="13"><component :is="a.icon" /></el-icon></span>
                            <div>
                                <div class="dp-caption ex-strong">{{ a.title }}</div>
                                <div class="ex-flex-icon dp-caption ex-muted dp-mono"><span>{{ a.meta }}</span><span>&middot;</span><span>{{ a.time }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ex-card ex-sell-banner">
                    <div class="ex-flex-icon"><el-icon :size="18"><Shop /></el-icon><span class="dp-label-md">Producer &amp; Miller Gateway</span></div>
                    <h4 class="dp-headline-sm">Have Physical Coffee to Sell?</h4>
                    <p class="dp-caption">Bring your lots directly to the exchange floor. Connect with verified importers, access escrow settlement, and skip the intermediary discounts.</p>
                    <button type="button" class="ex-btn ex-btn--onprimary ex-btn--full" @click="placeholderAction('List Coffee on Exchange')">List Coffee on Exchange</button>
                </div>

                <div class="ex-card">
                    <span class="dp-label-md ex-strong">Institutional Trust Guarantees</span>
                    <div class="ex-trust-list">
                        <div v-for="t in trustItems" :key="t.label" class="ex-flex-icon">
                            <el-icon :size="16" class="ex-icon--primary"><component :is="t.icon" /></el-icon>
                            <span class="dp-caption ex-strong">{{ t.label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
.ex-card {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--dp-outline-variant);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 16px;
}
.ex-muted { color: var(--dp-on-surface-variant); }
.ex-strong { color: var(--dp-on-surface); font-weight: 700; }
.ex-on { color: var(--dp-on-surface); }
.ex-mb-sm { margin-bottom: 8px; }
.ex-caption-sm { font-size: 11px; }
.ex-hide-sm { display: none; }
@media (min-width: 640px) { .ex-hide-sm { display: inline; } }
.ex-flex-icon { display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ex-flex-icon-between { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
.ex-sep { color: var(--dp-outline-variant); }

.ex-icon--primary { color: var(--dp-primary); }
.ex-icon--secondary { color: var(--dp-secondary); }
.ex-icon--error { color: var(--dp-error); }
.ex-icon--neutral { color: var(--dp-on-surface-variant); }

.ex-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--dp-primary); flex-shrink: 0; }
.ex-dot--pulse { animation: ex-pulse 1.6s ease-in-out infinite; }
@keyframes ex-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }

.ex-status-line { display: flex; flex-direction: column; gap: 8px; padding: 10px 20px; background: var(--dp-surface-container-low); border-radius: 8px; font-size: 12px; margin-top: -24px; margin-bottom: 4px; }
@media (min-width: 1024px) { .ex-status-line { flex-direction: row; align-items: center; justify-content: space-between; } }

.ex-tag-mini { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 3px 9px; border-radius: 6px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); white-space: nowrap; }
.ex-tag-mini--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.ex-tag-mini--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ex-tag-mini--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.ex-hero {
    border: none;
    border-bottom: 1px solid var(--dp-outline-variant);
    border-radius: 0;
    margin-top: -16px;
    padding-bottom: 24px;
    box-shadow: none;
}
.ex-hero__top { display: flex; flex-direction: column; gap: 16px; }
@media (min-width: 1024px) { .ex-hero__top { flex-direction: row; align-items: center; justify-content: space-between; } }
.ex-title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.ex-hero__actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }

.ex-tabs { display: flex; align-items: center; gap: 8px; overflow-x: auto; padding-top: 4px; }
.ex-tab { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 10px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); font-size: 12px; font-weight: 700; cursor: pointer; flex-shrink: 0; font-family: var(--dp-font-sans); }
.ex-tab--active { background: var(--dp-primary); color: var(--dp-on-primary); }
.ex-tab__count { padding: 2px 6px; border-radius: 6px; background: var(--dp-surface-container-high); color: var(--dp-on-surface); font-size: 10px; font-family: var(--dp-font-mono); }
.ex-tab--active .ex-tab__count { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }

.ex-kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
@media (min-width: 1024px) { .ex-kpi-grid { grid-template-columns: repeat(4, 1fr); } }
.ex-kpi { background: var(--dp-surface-container-low); border-radius: var(--dp-card-radius); padding: 18px 20px; display: flex; flex-direction: column; gap: 10px; }
.ex-kpi__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.ex-kpi__value { display: flex; align-items: baseline; gap: 6px; }
.ex-kpi__num { font-size: 26px; font-weight: 700; color: var(--dp-on-surface); font-family: var(--dp-font-sans); line-height: 1; }

.ex-grid-12 { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 1200px) { .ex-grid-12 { grid-template-columns: minmax(0, 1fr) 320px; align-items: start; } }
.ex-col-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.ex-col-side { display: flex; flex-direction: column; gap: 16px; }


.ex-card__head { display: flex; flex-direction: column; gap: 10px; }
@media (min-width: 640px) { .ex-card__head { flex-direction: row; align-items: flex-start; justify-content: space-between; } }

.ex-table-wrap { overflow-x: auto; }
.ex-table { width: 100%; border-collapse: collapse; text-align: left; font-size: var(--dp-content-font-size); }
.ex-table thead tr { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; font-weight: 700; }
.ex-table th { padding: 10px 12px; white-space: nowrap; }
.ex-table th:first-child { border-radius: 6px 0 0 6px; }
.ex-table th:last-child { border-radius: 0 6px 6px 0; }
.ex-table tbody tr { border-bottom: 1px solid var(--dp-outline-variant); transition: background 0.15s ease; }
.ex-table tbody tr:last-child { border-bottom: none; }
.ex-table tbody tr:hover { background: var(--dp-surface-container-low); }
.ex-table td { padding: 12px; vertical-align: middle; }
.ex-tag-mini--corridor { margin-top: 6px; }
.ex-row--selected { background: var(--dp-primary-fixed); }
.ex-row--selected:hover { background: var(--dp-primary-fixed); }
.ex-center { text-align: center; }
.ex-right { text-align: right; }

.ex-footline { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); }
.ex-actions-inline { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ex-actions-inline--end { justify-content: flex-end; }

.ex-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 14px; border-radius: 8px; border: none;
    font-size: var(--dp-content-font-size); font-weight: 700; cursor: pointer; transition: background 0.15s ease, color 0.15s ease; font-family: var(--dp-font-sans); white-space: nowrap;
    text-decoration: none;
}
.ex-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ex-btn--primary:hover { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.ex-btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ex-btn--secondary:hover { background: var(--dp-secondary); color: var(--dp-on-secondary-container); }
.ex-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.ex-btn--muted:hover { background: var(--dp-surface-dim); }
.ex-btn--onprimary { background: var(--dp-on-primary); color: var(--dp-primary); }
.ex-btn--onprimary:hover { background: var(--dp-surface-container-lowest); }
.ex-btn--sm { padding: 6px 10px; }
.ex-btn--full { width: 100%; }
.ex-btn--grow { flex: 1; }
.ex-btn--disabled { opacity: 0.45; pointer-events: none; }

.ex-link { display: inline-flex; align-items: center; justify-content: space-between; gap: 6px; font-weight: 700; color: var(--dp-primary); text-decoration: none; background: none; border: none; cursor: pointer; font-size: 12px; font-family: var(--dp-font-sans); padding: 4px 0; }
.ex-link:hover { text-decoration: underline; }

.ex-provenance { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; }
.ex-provenance-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
@media (min-width: 640px) { .ex-provenance-grid { grid-template-columns: repeat(4, 1fr); } }
.ex-provenance-item { background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 10px; display: flex; flex-direction: column; gap: 4px; }

.ex-specs-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 900px) { .ex-specs-grid { grid-template-columns: 1fr 1fr; } }
.ex-specs-col { display: flex; flex-direction: column; gap: 12px; }
.ex-mini-stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.ex-mini-stat { background: var(--dp-surface-container-low); border-radius: 8px; padding: 10px; display: flex; flex-direction: column; align-items: center; gap: 2px; text-align: center; }
.ex-sensory { background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.ex-sensory-row { display: flex; flex-direction: column; gap: 4px; }
.ex-bar { width: 100%; height: 6px; border-radius: 999px; background: var(--dp-surface-container-high); overflow: hidden; }
.ex-bar__fill { height: 100%; border-radius: 999px; }
.ex-bar__fill--primary { background: var(--dp-primary); }
.ex-bar__fill--secondary { background: var(--dp-secondary); }

.ex-cost-box { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.ex-cost-rows { display: flex; flex-direction: column; gap: 6px; }
.ex-cost-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.ex-cost-total { display: flex; align-items: center; justify-content: space-between; gap: 8px; background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 10px 12px; }

.ex-escrow-banner { flex-direction: column; }
@media (min-width: 900px) { .ex-escrow-banner { flex-direction: row; align-items: center; } }
.ex-escrow-banner .ex-flex-icon { align-items: flex-start; }

.ex-negotiation-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 900px) { .ex-negotiation-grid { grid-template-columns: repeat(3, 1fr); } }
.ex-negotiation-card { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; }

.ex-benchmark-list { display: flex; flex-direction: column; gap: 10px; }
.ex-benchmark { background: var(--dp-surface-container-low); border-radius: 8px; padding: 10px; display: flex; flex-direction: column; gap: 4px; }
.ex-depth-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.ex-depth-stat { background: var(--dp-surface-container-low); border-radius: 8px; padding: 10px; display: flex; flex-direction: column; align-items: center; gap: 2px; text-align: center; }

.ex-activity-list { display: flex; flex-direction: column; gap: 12px; }
.ex-activity-item { display: flex; align-items: flex-start; gap: 10px; }
.ex-activity-icon { width: 24px; height: 24px; border-radius: 999px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; color: var(--dp-on-primary-fixed); }
.ex-activity-icon--primary { background: var(--dp-primary-fixed); }
.ex-activity-icon--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ex-activity-icon--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }

.ex-sell-banner { background: linear-gradient(135deg, var(--dp-primary), var(--dp-primary-container)); color: var(--dp-on-primary); }
.ex-sell-banner p { color: var(--dp-on-primary); opacity: 0.85; }

.ex-trust-list { display: flex; flex-direction: column; gap: 10px; }
</style>
