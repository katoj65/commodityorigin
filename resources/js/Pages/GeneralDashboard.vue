<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import {
    ShoppingBag, Shop, Position, TrendCharts, ArrowRight, AddLocation,
    Ship, Odometer, Notebook, WarningFilled, InfoFilled, MagicStick,
    Search, Promotion, Lock, Document, Tools, Coin, Calendar as CalendarIcon,
    Download, Sunny, Right, CircleCheckFilled, Refresh, Box,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

defineProps({
    hasProfile: { type: Boolean, default: true },
    businessTypeOptions: { type: Array, default: () => [] },
    cropGrades: { type: Array, default: () => [] },
    lotRequests: { type: Array, default: () => [] },
    exchangeRates: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
    orders: { type: Array, default: () => [] },
    markets: { type: Array, default: () => [] },
});

/* ── Dummy dashboard content — illustrative only ────────────────────── */
const quickActions = [
    { icon: ShoppingBag, tone: 'primary-fixed', tag: 'Direct Escrow', title: 'Buy Coffee', body: 'Browse verified export-ready lots with automated bank-grade custody & escrow.', cta: 'Find Coffee Lots', ctaIcon: ArrowRight, route: 'market.index' },
    { icon: Shop, tone: 'secondary-fixed', tag: 'On-Chain RWA', title: 'Sell Coffee', body: 'List warehouse batches, mint digital ownership tokens, and connect with global roasters.', cta: 'List New Batch', ctaIcon: AddLocation, route: 'store.show' },
    { icon: Position, tone: 'tertiary-fixed', tag: 'RFQ Network', title: 'Source Coffee', body: 'Broadcast institutional sourcing RFQs directly to certified dry mills and co-ops.', cta: 'Create RFQ', ctaIcon: Promotion, route: 'trade.index' },
    { icon: TrendCharts, tone: 'high', tag: 'Order Book', title: 'Live Exchange', body: 'Access real-time bid ladders, depth charts, and physical spot desks at Kampala & Mombasa.', cta: 'Open Exchange Desk', ctaIcon: ArrowRight, route: 'market.active' },
];

const kpis = [
    { label: 'Coffee Available', value: '1,284', unit: 'MT', tag: '+8.4%', tagTone: 'primary', note: 'Physical corridor liquidity' },
    { label: 'Active Lots', value: '86', unit: 'Lots', tag: '12 newly listed', tagTone: 'secondary', note: 'UCDA & CQI laboratory certified' },
    { label: 'Open Offers', value: '24', unit: 'Offers', tag: '7 action req.', tagTone: 'error', note: 'Valued at $1.84M gross settlement' },
    { label: 'Active RFQs', value: '18', unit: 'RFQs', tag: '5 matched', tagTone: 'neutral', note: 'Active institutional demand' },
];

const arbitrageRows = [
    { grade: 'Uganda Robusta Screen 18', origin: 'Central / Masaka Basin', farmgate: '$2.80 /kg', farmgateNote: '10,400 UGX', fob: '$4.15 /kg', fobNote: 'FOB Mombasa', spread: '+$1.35 /kg', spreadPct: '(+48.2%)', marginTag: 'High Margin', marginTone: 'primary', trend: 'up' },
    { grade: 'Uganda Bugisu Arabica AA', origin: 'Mount Elgon Washed', farmgate: '$3.60 /kg', farmgateNote: '13,380 UGX', fob: '$5.10 /kg', fobNote: 'FOB Mombasa', spread: '+$1.50 /kg', spreadPct: '(+41.7%)', marginTag: 'Premium Spread', marginTone: 'secondary', trend: 'up' },
    { grade: 'Vietnam Robusta Gr. 2', origin: 'Wet Polished 5% Black', farmgate: '$2.40 /kg', farmgateNote: 'Central Highlands', fob: '$3.85 /kg', fobNote: 'FOB Ho Chi Minh', spread: '+$1.45 /kg', spreadPct: '(+60.4%)', marginTag: 'Wide Window', marginTone: 'primary', trend: 'flat' },
    { grade: 'Brazil Santos NY 2/3', origin: 'Strictly Soft Fine Cup', farmgate: '$3.10 /kg', farmgateNote: 'Minas Gerais Farmgate', fob: '$4.60 /kg', fobNote: 'FOB Santos', spread: '+$1.50 /kg', spreadPct: '(+48.4%)', marginTag: 'Benchmark Par', marginTone: 'neutral', trend: 'up' },
];

const portStatuses = [
    { name: 'Port of Mombasa (KE)', status: '2.4d dwell · Normal', width: '35%', tone: 'primary', meta: ['Berth turnaround: 28 hrs', 'Reefer availability: 94%'] },
    { name: 'Port of Dar es Salaam (TZ)', status: '1.1d dwell · Optimal', width: '18%', tone: 'primary', meta: ['SGR Rail connection fluid', 'Congestion index: Low'] },
    { name: 'Jebel Ali Gateway (AE)', status: '2.0d dwell · Stable', width: '40%', tone: 'secondary', meta: ['Middle East Transit desk', 'Direct feeder route'] },
    { name: 'Rotterdam Hub (NL)', status: '4.8d dwell · Moderate', width: '72%', tone: 'secondary', meta: ['EUDR Customs checks', 'Warehousing at 88%'] },
];

const seaLanes = [
    { route: 'Mombasa → Jebel Ali', days: '14 – 18 Days' },
    { route: 'Mombasa → Hamburg', days: '26 – 32 Days' },
];

const exportLots = [
    {
        id: 'LOT-UG-8821', grade: 'Uganda Fine Robusta Screen 18', producer: 'Mukono Farmers Co-op', location: 'Central Region, 1,180m ASL',
        volume: '120 MT', volumeNote: '(6 × 20ft FCL)', price: '$4.20', priceNote: '$504,000 Total',
        tags: [{ label: 'UCDA Verified', tone: 'primary' }, { label: 'EUDR Deforestation-Free', tone: 'secondary' }], spec: 'Moisture 11.8% · Max 1% Defect',
        term: 'FOB Mombasa', termNote: 'Dispatched in 7 days', dot: 'primary',
    },
    {
        id: 'LOT-UG-9042', grade: 'Bugisu Arabica AA Washed', producer: 'Mt. Elgon Organic Estate', location: 'Mbale District, 1,850m ASL',
        volume: '45 MT', volumeNote: '(2.2 × 20ft FCL)', price: '$5.10', priceNote: '$229,500 Total',
        tags: [{ label: 'CQI Score 86.5', tone: 'primary' }, { label: 'Organic Certified', tone: 'tertiary' }], spec: 'Jasmine, Bergamot, Black Tea',
        term: 'FOB Mombasa', termNote: 'Immediate loading', dot: 'primary',
    },
    {
        id: 'LOT-RW-3021', grade: 'Rwenzori Natural Drugar', producer: 'Kasese Highlands Union', location: 'Mountains of the Moon, 1,600m',
        volume: '60 MT', volumeNote: '(3 × 20ft FCL)', price: '$4.65', priceNote: '$279,000 Total',
        tags: [{ label: 'Sun-dried African Beds', tone: 'neutral' }, { label: 'EUDR GPS Tagged', tone: 'primary' }], spec: 'Wild berry, dark cocoa finish',
        term: 'FOB Mombasa', termNote: 'Dry mill inspected', dot: 'secondary',
    },
];

const lifecycleSteps = [
    { n: '01', label: 'Farm Collection', volume: '85 MT', note: '24 primary buying centres · Central Uganda', icon: CircleCheckFilled, tone: 'primary' },
    { n: '02', label: 'Processing Batch', volume: '80 MT', note: '12 active dry mill batches · Namanve Industrial', icon: Refresh, tone: 'primary' },
    { n: '03', label: 'Master Lots', volume: '72 MT', note: '8 export-graded certified warehouse lots', icon: CircleCheckFilled, tone: 'primary' },
    { n: '04', label: 'Tokenised RWA', volume: '25 MT', note: '3 lots on exchange order books (Stanbic Custody)', icon: Coin, tone: 'secondary' },
];

const activeTrades = [
    { id: 'TRD-2026-089', custody: 'Stanbic Escrow', grade: 'Uganda Robusta 18', side: 'BUY', sideTone: 'primary', volume: '20 MT', value: '$84,000', milestoneIcon: Lock, milestone: 'Funds Deposited', milestoneTone: 'primary', milestoneNote: 'Buyer awaiting release' },
    { id: 'TRD-2026-084', custody: 'HSBC Export L/C', grade: 'Bugisu Arabica AA', side: 'SELL', sideTone: 'secondary', volume: '15 MT', value: '$76,500', milestoneIcon: Document, milestone: 'Customs Clearance', milestoneTone: 'secondary', milestoneNote: 'Malaba border post transit' },
    { id: 'TRD-2026-078', custody: 'Smart Contract Escrow', grade: 'Rwenzori Natural', side: 'BUY', sideTone: 'primary', volume: '25 MT', value: '$116,250', milestoneIcon: Tools, milestone: 'Vessel Stuffing', milestoneTone: 'neutral', milestoneNote: 'Mombasa Berth 5' },
];

const actionItems = [
    { level: 'High', levelTone: 'error', title: 'Confirm escrow release for Order #TRD-2026-089', body: 'Buyer (Zurich Coffee GmbH) deposited $84,000 USD into Stanbic Custody. Warehouse receipt verified.', cta: 'Review & Confirm', primary: true },
    { level: 'Medium', levelTone: 'secondary', title: 'Review 3 new seller quotations for RFQ #RFQ-2026-0042', body: 'Dubai Roaster Tender: 40 MT Bugisu AA. Quotations start at $5.05/kg FOB Mombasa.', cta: 'Open RFQ', primary: false },
    { level: 'Normal', levelTone: 'neutral', title: 'Upload EUDR Satellite Geolocation Polygons', body: 'Mukono Lot #LOT-000124 missing GPS boundary shapefile for 8 contributing smallholder plots.', cta: 'Upload Polygon', primary: false },
];

const tradingCalendarEvents = [
    { month: 'SEP', day: '18', tone: 'primary', title: 'UCDA Spot Floor Coffee Auction', note: 'Kampala Coffee House Desk · 42 lots cataloged for bidding' },
    { month: 'SEP', day: '20', tone: 'secondary', title: 'RFQ Submission Deadline (Middle East)', note: 'Jebel Ali Specialty Coffee consortium 100 MT Arabica tender' },
    { month: 'SEP', day: '23', tone: 'tertiary', title: 'Maersk Vessel Cut-Off (Mombasa → Jebel Ali)', note: 'Final container gate-in at Kilindini Harbour for MV Safmarine' },
];

const aiPrompts = [
    { icon: Search, tone: 'primary', label: 'Find 40 MT Arabica AA', query: 'Find 40 MT Arabica AA washed ready for FOB Mombasa under $5.10/kg' },
    { icon: Coin, tone: 'secondary', label: 'Simulate Freight to Jebel Ali', query: 'Simulate ocean freight & insurance from Mombasa to Jebel Ali for 2x20ft containers' },
    { icon: Odometer, tone: 'primary', label: 'Check Farmgate Arbitrage', query: 'Check current farmgate arbitrage spread for Masaka Robusta Screen 18 vs FOB' },
    { icon: WarningFilled, tone: 'tertiary', label: 'EUDR Compliance Scan', query: 'Run EUDR compliance pre-validation scan on Mukono Farmers Co-op polygon shapefiles' },
];

const aiCommand = ref('');
const aiResponseVisible = ref(false);
const aiResponseQuery = ref('');

function runAiQuery(query) {
    aiCommand.value = query;
    aiResponseQuery.value = query;
    aiResponseVisible.value = true;
}

function executeAiCommand() {
    if (!aiCommand.value.trim()) return;
    runAiQuery(aiCommand.value.trim());
}

function placeholderAction(label) {
    ElMessage.info(`${label} (dummy preview).`);
}
</script>

<template>
    <DesignPreviewLayout title="Coffee Intelligence Center">
        <!-- 1. HERO HEADER & UNIVERSAL ACTION BAR -->
        <section class="gd-card gd-hero">
            <div class="gd-hero__top">
                <div>
                    <h1 class="dp-display-md">Coffee Exchange</h1>
                    <p class="dp-body-md gd-muted">Here is what is happening across your physical coffee corridors, inventory, and trades today.</p>
                </div>
                <div class="gd-hero__stats">
                    <div class="gd-mini-stat">
                        <el-icon :size="18" class="gd-icon--primary"><CircleCheckFilled /></el-icon>
                        <div>
                            <div class="dp-caption gd-muted">Escrow Facility</div>
                            <div class="dp-body-md gd-strong">Stanbic UGX 3.2B Secured</div>
                        </div>
                    </div>
                    <div class="gd-mini-stat">
                        <el-icon :size="18" class="gd-icon--secondary"><Notebook /></el-icon>
                        <div>
                            <div class="dp-caption gd-muted">UCDA Desk</div>
                            <div class="dp-body-md gd-strong">Floor Active (09:00 EAT)</div>
                        </div>
                    </div>
                </div>
            </div>


        </section>

        <!-- 2. QUICK ACTION DISPATCH HUB -->
        <section class="gd-actions-grid">
            <div v-for="action in quickActions" :key="action.title" class="gd-card gd-action-card">
                <div>
                    <div class="gd-action-card__head">
                        <div class="gd-action-icon" :class="`gd-action-icon--${action.tone}`"><el-icon :size="20"><component :is="action.icon" /></el-icon></div>
                        <span class="gd-tag-mini">{{ action.tag }}</span>
                    </div>
                    <h3 class="dp-headline-sm">{{ action.title }}</h3>
                    <p class="dp-caption gd-muted gd-clamp2">{{ action.body }}</p>
                </div>
                <button type="button" class="gd-action-cta" @click="router.visit(route(action.route))">
                    <span>{{ action.cta }}</span>
                    <el-icon :size="15"><component :is="action.ctaIcon" /></el-icon>
                </button>
            </div>
        </section>

        <!-- 3. CORE BUSINESS KPIS -->
        <section class="gd-card">
            <div class="gd-kpi-row">
                <div v-for="kpi in kpis" :key="kpi.label" class="gd-kpi">
                    <div class="gd-kpi__head">
                        <span class="dp-label-md gd-muted">{{ kpi.label }}</span>
                        <span class="gd-tag-mini" :class="`gd-tag-mini--${kpi.tagTone}`">{{ kpi.tag }}</span>
                    </div>
                    <div class="dp-display-md gd-kpi__value">{{ kpi.value }} <span class="dp-body-md gd-muted">{{ kpi.unit }}</span></div>
                    <p class="dp-caption gd-muted">{{ kpi.note }}</p>
                </div>
            </div>
        </section>

        <!-- 4. ARBITRAGE + PORT LOGISTICS -->
        <section class="gd-grid-12">
            <div class="gd-card gd-col-8">
                <div class="gd-card__head">
                    <div>
                        <div class="gd-title-row"><h2 class="dp-headline-md">Farmgate vs. Export Price Arbitrage</h2><span class="gd-tag-mini">Live Spot Spreads</span></div>
                        <p class="dp-caption gd-muted">Tracking dry mill procurement arbitrage through Mombasa & Santos corridors</p>
                    </div>
                    <div class="gd-toggle-group">
                        <button type="button" class="gd-toggle gd-toggle--active">East Africa</button>
                        <button type="button" class="gd-toggle" @click="placeholderAction('Global Benchmarks view')">Global Benchmarks</button>
                    </div>
                </div>
                <div class="gd-table-wrap">
                    <table class="gd-table">
                        <thead>
                            <tr><th>Origin Grade</th><th>Farmgate / In-Country</th><th>FOB Port Terminal</th><th>Gross Spread</th><th>Margin Grade</th><th class="gd-right">Trend</th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in arbitrageRows" :key="row.grade">
                                <td><div class="gd-strong">{{ row.grade }}</div><div class="dp-caption gd-muted">{{ row.origin }}</div></td>
                                <td><div class="gd-strong">{{ row.farmgate }}</div><div class="dp-caption gd-muted">{{ row.farmgateNote }}</div></td>
                                <td><div class="gd-strong">{{ row.fob }}</div><div class="dp-caption gd-muted">{{ row.fobNote }}</div></td>
                                <td class="gd-icon--primary gd-strong">{{ row.spread }} <span class="dp-caption gd-muted">{{ row.spreadPct }}</span></td>
                                <td><span class="gd-tag-mini" :class="`gd-tag-mini--${row.marginTone}`">{{ row.marginTag }}</span></td>
                                <td class="gd-right"><el-icon :size="18" :class="row.trend === 'up' ? 'gd-icon--primary' : 'gd-icon--secondary'"><component :is="row.trend === 'up' ? TrendCharts : Right" /></el-icon></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="gd-note-bar">
                    <span><el-icon :size="15" class="gd-icon--primary"><InfoFilled /></el-icon> Export spreads account for inland trucking (Kampala → Mombasa: $85/MT) and port terminal handling.</span>
                    <a href="#" class="gd-link" @click.prevent="placeholderAction('Full Commodity Model')">Full Commodity Model →</a>
                </div>
            </div>

            <div class="gd-card gd-col-4">
                <div>
                    <div class="gd-card__head">
                        <div class="gd-title-row"><el-icon :size="18" class="gd-icon--primary"><Ship /></el-icon><h2 class="dp-headline-md">Port Logistics &amp; Dwell</h2></div>
                        <span class="gd-tag-mini gd-tag-mini--primary"><span class="gd-dot gd-dot--sm"></span> Telemetry Active</span>
                    </div>
                    <p class="dp-caption gd-muted gd-mb">Export gateway congestion and corridor transit times for containerized bagged green coffee.</p>
                    <div class="gd-port-list">
                        <div v-for="port in portStatuses" :key="port.name" class="gd-port">
                            <div class="gd-port__head"><span class="gd-strong dp-body-md">{{ port.name }}</span><span class="gd-strong" :class="`gd-icon--${port.tone}`">{{ port.status }}</span></div>
                            <div class="gd-bar"><div class="gd-bar__fill" :class="`gd-bar__fill--${port.tone}`" :style="{ width: port.width }"></div></div>
                            <div class="gd-port__meta"><span>{{ port.meta[0] }}</span><span>{{ port.meta[1] }}</span></div>
                        </div>
                    </div>
                    <div class="gd-lanes">
                        <div class="dp-label-md gd-muted gd-mb-sm">Active Sea-Lanes</div>
                        <div v-for="lane in seaLanes" :key="lane.route" class="gd-lane"><span><el-icon :size="14"><Ship /></el-icon> {{ lane.route }}</span><span class="gd-strong">{{ lane.days }}</span></div>
                    </div>
                </div>
                <button type="button" class="gd-btn gd-btn--muted gd-btn--full" @click="placeholderAction('Freight Corridor Tracker')">Freight Corridor Tracker</button>
            </div>
        </section>

        <!-- 5. EXPORT-READY LOTS -->
        <section class="gd-card">
            <div class="gd-card__head">
                <div>
                    <div class="gd-title-row"><h2 class="dp-headline-md">Export-Ready Lots Discovered</h2><span class="gd-tag-mini gd-tag-mini--primary">Auto-Matched to Sourcing Profile</span></div>
                    <p class="dp-caption gd-muted">Direct physical inventory verified in licenced dry mills with lab certificates and polygon traceability</p>
                </div>
                <div class="gd-actions-inline">
                    <button type="button" class="gd-btn gd-btn--muted" @click="placeholderAction('Filter by origin & grade')"><el-icon :size="15"><Tools /></el-icon> Filter Origin &amp; Grade</button>
                    <button type="button" class="gd-btn gd-btn--primary" @click="router.visit(route('market.index'))">View All 86 Lots</button>
                </div>
            </div>
            <div class="gd-table-wrap">
                <table class="gd-table">
                    <thead>
                        <tr><th>Lot ID &amp; Grade</th><th>Origin / Producer</th><th>Volume</th><th>Contract Price</th><th>Certifications &amp; Quality</th><th>Delivery Term</th><th class="gd-right">Action</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="lot in exportLots" :key="lot.id">
                            <td>
                                <div class="gd-strong gd-flex-icon"><span class="gd-dot" :class="`gd-dot--${lot.dot}`"></span><span class="dp-mono">{{ lot.id }}</span></div>
                                <div class="dp-caption gd-muted">{{ lot.grade }}</div>
                            </td>
                            <td><div class="gd-strong">{{ lot.producer }}</div><div class="dp-caption gd-muted">{{ lot.location }}</div></td>
                            <td><div class="gd-strong">{{ lot.volume }}</div><div class="dp-caption gd-muted">{{ lot.volumeNote }}</div></td>
                            <td><div class="gd-strong gd-icon--primary">{{ lot.price }} <span class="dp-caption gd-on">/kg</span></div><div class="dp-caption gd-muted dp-mono">{{ lot.priceNote }}</div></td>
                            <td>
                                <div class="gd-tag-row"><span v-for="tag in lot.tags" :key="tag.label" class="gd-tag-mini" :class="`gd-tag-mini--${tag.tone}`">{{ tag.label }}</span></div>
                                <div class="dp-caption gd-muted gd-mb-none">{{ lot.spec }}</div>
                            </td>
                            <td><div class="gd-strong">{{ lot.term }}</div><div class="dp-caption gd-muted">{{ lot.termNote }}</div></td>
                            <td class="gd-right">
                                <div class="gd-actions-inline gd-actions-inline--end">
                                    <button type="button" class="gd-btn gd-btn--muted gd-btn--sm" @click="placeholderAction(`View ${lot.id}`)">View Lot</button>
                                    <button type="button" class="gd-btn gd-btn--primary gd-btn--sm" @click="placeholderAction(`Bid/Buy on ${lot.id}`)">Bid / Buy</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 6. LIFECYCLE + ACTIVE TRADES -->
        <section class="gd-grid-12">
            <div class="gd-card gd-col-5">
                <div>
                    <div class="gd-card__head">
                        <div><h2 class="dp-headline-md">My Coffee Lifecycle</h2><p class="dp-caption gd-muted">Real-time inventory transformation across your supply chain</p></div>
                        <span class="gd-tag-mini gd-tag-mini--primary">262 MT Managed</span>
                    </div>
                    <div class="gd-lifecycle">
                        <div v-for="step in lifecycleSteps" :key="step.n" class="gd-lifecycle__step">
                            <div class="gd-lifecycle__badge" :class="`gd-lifecycle__badge--${step.tone}`">{{ step.n }}</div>
                            <div class="gd-lifecycle__body">
                                <div class="gd-lifecycle__row"><span class="gd-strong dp-body-md">{{ step.label }}</span><span class="gd-strong" :class="`gd-icon--${step.tone}`">{{ step.volume }}</span></div>
                                <div class="dp-caption gd-muted">{{ step.note }}</div>
                            </div>
                            <el-icon :size="18" :class="`gd-icon--${step.tone}`"><component :is="step.icon" /></el-icon>
                        </div>
                    </div>
                </div>
                <button type="button" class="gd-btn gd-btn--muted gd-btn--full" @click="router.visit(route('store.show'))">
                    <el-icon :size="15"><Box /></el-icon> Manage My Coffee Operations
                </button>
            </div>

            <div class="gd-card gd-col-7">
                <div>
                    <div class="gd-card__head">
                        <div><h2 class="dp-headline-md">Active Commercial Trades</h2><p class="dp-caption gd-muted">Settlement escrow and customs status for current contract commitments</p></div>
                        <span class="gd-tag-mini">3 Active Executions</span>
                    </div>
                    <div class="gd-table-wrap">
                        <table class="gd-table">
                            <thead><tr><th>Trade ID</th><th>Grade &amp; Position</th><th>Volume</th><th>Gross Value</th><th>Escrow / Milestone</th><th class="gd-right">Status</th></tr></thead>
                            <tbody>
                                <tr v-for="trade in activeTrades" :key="trade.id">
                                    <td><div class="gd-strong dp-mono">{{ trade.id }}</div><div class="dp-caption gd-muted">{{ trade.custody }}</div></td>
                                    <td><div class="gd-strong">{{ trade.grade }}</div><span class="gd-tag-mini" :class="`gd-tag-mini--${trade.sideTone}`">{{ trade.side }}</span></td>
                                    <td class="gd-strong">{{ trade.volume }}</td>
                                    <td class="gd-strong">{{ trade.value }}</td>
                                    <td>
                                        <span class="gd-flex-icon gd-strong" :class="`gd-icon--${trade.milestoneTone}`"><el-icon :size="14"><component :is="trade.milestoneIcon" /></el-icon> {{ trade.milestone }}</span>
                                        <div class="dp-caption gd-muted">{{ trade.milestoneNote }}</div>
                                    </td>
                                    <td class="gd-right"><button type="button" class="gd-btn gd-btn--muted gd-btn--sm" @click="placeholderAction(`View ${trade.id}`)">View Trade</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="gd-footline">
                    <span class="dp-caption gd-muted">Total active physical trades volume: <strong class="gd-on">60 MT ($276,750 USD)</strong></span>
                    <a href="#" class="gd-link" @click.prevent="router.visit(route('orders.index'))">Complete Trade Ledger →</a>
                </div>
            </div>
        </section>

        <!-- 7. ACTION REQUIRED + CALENDAR -->
        <section class="gd-grid-12">
            <div class="gd-card gd-col-7">
                <div class="gd-card__head">
                    <div class="gd-title-row"><el-icon :size="18" class="gd-icon--error"><WarningFilled /></el-icon><h2 class="dp-headline-md">Action Required</h2></div>
                    <span class="gd-tag-mini gd-tag-mini--error">3 Pending Compliance &amp; Approvals</span>
                </div>
                <p class="dp-caption gd-muted gd-mb">Immediate operational tasks to clear corridor cargo holds and escrow releases</p>
                <div class="gd-action-list">
                    <div v-for="item in actionItems" :key="item.title" class="gd-action-item">
                        <div class="gd-action-item__body">
                            <span class="gd-level" :class="`gd-level--${item.levelTone}`">{{ item.level }}</span>
                            <div>
                                <div class="gd-strong dp-body-md">{{ item.title }}</div>
                                <div class="dp-caption gd-muted">{{ item.body }}</div>
                            </div>
                        </div>
                        <button type="button" class="gd-btn gd-btn--sm" :class="item.primary ? 'gd-btn--primary' : 'gd-btn--muted'" @click="placeholderAction(item.cta)">{{ item.cta }}</button>
                    </div>
                </div>
            </div>

            <div class="gd-card gd-col-5">
                <div>
                    <div class="gd-card__head">
                        <div class="gd-title-row"><el-icon :size="18" class="gd-icon--secondary"><CalendarIcon /></el-icon><h2 class="dp-headline-md">Trading Calendar</h2></div>
                        <a href="#" class="gd-link" @click.prevent="router.visit(route('calendar.index'))">Full Calendar</a>
                    </div>
                    <p class="dp-caption gd-muted gd-mb">Official floor auctions, shipping schedules, and tender deadlines</p>
                    <div class="gd-cal-list">
                        <div v-for="event in tradingCalendarEvents" :key="event.title" class="gd-cal-item">
                            <div class="gd-cal-date"><div class="dp-caption" :class="`gd-icon--${event.tone}`">{{ event.month }}</div><div class="dp-headline-sm">{{ event.day }}</div></div>
                            <div><div class="gd-strong dp-body-md">{{ event.title }}</div><div class="dp-caption gd-muted">{{ event.note }}</div></div>
                        </div>
                    </div>
                </div>
                <div class="gd-footline">
                    <span class="dp-caption gd-muted">Sync to Outlook / Google Calendar</span>
                    <button type="button" class="gd-link gd-flex-icon" @click="placeholderAction('Subscribe to calendar feed')"><span>Subscribe Feed (.ics)</span><el-icon :size="13"><Download /></el-icon></button>
                </div>
            </div>
        </section>

        <!-- 8. AI COMMERCE ASSISTANT -->
        <section class="gd-card">
            <div class="gd-ai-head">
                <div class="gd-flex-icon">
                    <div class="gd-ai-icon"><el-icon :size="22"><MagicStick /></el-icon></div>
                    <div>
                        <div class="gd-title-row"><h2 class="dp-headline-md">Bean Origin AI Commerce Advisory</h2><span class="gd-tag-mini gd-tag-mini--primary">Llama-3 Commodity Model</span></div>
                        <p class="dp-caption gd-muted">Synthesizing real-time farmgate pricing, freight rates, customs regulations, and tokenised custody</p>
                    </div>
                </div>
                <span class="gd-flex-icon dp-caption gd-muted"><span class="gd-dot"></span> Human-in-the-loop validation enforced for contract minting</span>
            </div>

            <div class="gd-prompt-row">
                <span class="dp-label-md gd-muted">Suggested Prompts:</span>
                <button v-for="prompt in aiPrompts" :key="prompt.label" type="button" class="gd-chip" @click="runAiQuery(prompt.query)">
                    <el-icon :size="14" :class="`gd-icon--${prompt.tone}`"><component :is="prompt.icon" /></el-icon>
                    <span>{{ prompt.label }}</span>
                </button>
            </div>

            <div class="gd-searchbar">
                <div class="gd-searchbar__input">
                    <el-icon :size="18"><Promotion /></el-icon>
                    <input v-model="aiCommand" type="text" placeholder="Ask anything: 'What is the current FOB export parity for Bugisu AA?' or 'Draft an RFQ for 60 MT Robusta'..." @keydown.enter="executeAiCommand" />
                </div>
                <el-button class="gd-btn gd-btn--primary" @click="executeAiCommand">Execute Query <el-icon :size="14"><Promotion /></el-icon></el-button>
            </div>

            <div v-if="aiResponseVisible" class="gd-ai-response">
                <div class="gd-ai-response__head">
                    <span class="gd-flex-icon gd-icon--primary gd-strong"><el-icon :size="16"><MagicStick /></el-icon> Bean Origin AI Response</span>
                    <button type="button" class="gd-icon-close" @click="aiResponseVisible = false"><el-icon :size="16"><Refresh /></el-icon></button>
                </div>
                <p class="dp-body-md gd-on">
                    <strong>Synthesizing query:</strong> "{{ aiResponseQuery }}"<br /><br />
                    • <strong>Optimal Corridor:</strong> Kampala → Malaba Inland Transit → Mombasa Terminal 2.<br />
                    • <strong>Estimated Clearance:</strong> 48 hours via Stanbic Digital Bill of Lading.<br />
                    • <strong>Calculated Gross Margin:</strong> +$1.42/kg (+44.8% over current farmgate procurement).
                </p>
            </div>
        </section>
    </DesignPreviewLayout>
</template>

<style scoped>
.gd-card {
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
.gd-muted { color: var(--dp-on-surface-variant); }
.gd-strong { color: var(--dp-on-surface); font-weight: 700; }
.gd-on { color: var(--dp-on-surface); }
.gd-mb { margin-bottom: 16px; }
.gd-mb-sm { margin-bottom: 8px; }
.gd-mb-none { margin-bottom: 0; }
.gd-clamp2 { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.gd-icon--primary { color: var(--dp-primary); }
.gd-icon--secondary { color: var(--dp-secondary); }
.gd-icon--tertiary { color: #923357; }
.gd-icon--error { color: var(--dp-error); }
.gd-icon--neutral { color: var(--dp-on-surface-variant); }

.gd-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--dp-primary); flex-shrink: 0; }
.gd-dot--sm { width: 5px; height: 5px; }
.gd-dot--secondary { background: var(--dp-secondary); }

.gd-hero {
    border: none;
    border-bottom: 1px solid var(--dp-outline-variant);
    /* DesignPreviewLayout's .dp-main carries its own 48px top padding
       (shared by every page it wraps) — partially pulled back up here so
       the hero card keeps some breathing room under the header instead of
       sitting fully flush, same fix already applied on MarketPage.vue /
       MarketListings.vue. */
    margin-top: -24px;
}
.gd-hero__top { display: flex; flex-direction: column; gap: 16px; padding-bottom: 4px; }
@media (min-width: 1024px) { .gd-hero__top { flex-direction: row; align-items: center; justify-content: space-between; } }
.gd-hero__stats { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.gd-mini-stat { display: flex; align-items: center; gap: 8px; background: var(--dp-surface-container-low); padding: 8px 12px; border-radius: 8px; }

.gd-searchbar { background: var(--dp-surface-container-low); padding: 8px; border-radius: 8px; display: flex; flex-direction: column; gap: 8px; }
@media (min-width: 768px) { .gd-searchbar { flex-direction: row; align-items: center; } }
.gd-searchbar--actions-only { justify-content: flex-end; }
.gd-searchbar--actions-only .gd-searchbar__actions { width: 100%; }
@media (min-width: 768px) { .gd-searchbar--actions-only .gd-searchbar__actions { width: auto; margin-left: auto; } }
.gd-searchbar__input { position: relative; flex: 1; display: flex; align-items: center; gap: 10px; padding: 0 14px; background: var(--dp-surface-container-lowest); border-radius: 8px; height: 44px; color: var(--dp-on-surface-variant); }
.gd-searchbar__input input { flex: 1; border: none; outline: none; background: transparent; font-size: 13px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); }
.gd-searchbar__actions { display: flex; gap: 8px; flex-shrink: 0; }

.gd-actions-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 640px) { .gd-actions-grid { grid-template-columns: 1fr 1fr; } }
@media (min-width: 1024px) { .gd-actions-grid { grid-template-columns: 1fr 1fr 1fr 1fr; } }
.gd-action-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.gd-action-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.gd-action-icon--primary-fixed { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.gd-action-icon--secondary-fixed { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.gd-action-icon--tertiary-fixed { background: var(--dp-tertiary-fixed); color: var(--dp-on-tertiary-fixed); }
.gd-action-icon--high { background: var(--dp-surface-container-high); color: var(--dp-primary); }
.gd-action-cta {
    width: 100%; padding: 8px 12px; border-radius: 8px; border: none; background: var(--dp-surface-container-low);
    color: var(--dp-on-surface); font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: space-between;
    cursor: pointer; transition: background 0.15s ease, color 0.15s ease; font-family: var(--dp-font-sans);
}
.gd-action-cta:hover { background: var(--dp-primary); color: var(--dp-on-primary); }

.gd-tag-mini { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 2px 8px; border-radius: 6px; background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); white-space: nowrap; }
.gd-tag-mini--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.gd-tag-mini--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.gd-tag-mini--tertiary { background: var(--dp-tertiary-fixed); color: var(--dp-on-tertiary-fixed); }
.gd-tag-mini--error { background: var(--dp-error-container); color: var(--dp-on-error-container); }
.gd-tag-mini--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.gd-kpi-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; }
@media (min-width: 1024px) { .gd-kpi-row { grid-template-columns: repeat(4, 1fr); } }
.gd-kpi { display: flex; flex-direction: column; gap: 4px; padding-top: 4px; border-top: 2px solid transparent; }
@media (min-width: 1024px) { .gd-kpi { border-top: none; border-left: 1px solid var(--dp-outline-variant); padding-left: 16px; padding-top: 0; } .gd-kpi:first-child { border-left: none; padding-left: 0; } }
.gd-kpi__head { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.gd-kpi__value { display: flex; align-items: baseline; gap: 6px; }

.gd-grid-12 { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 1200px) { .gd-grid-12 { grid-template-columns: repeat(12, 1fr); } .gd-col-8 { grid-column: span 8; } .gd-col-4 { grid-column: span 4; } .gd-col-5 { grid-column: span 5; } .gd-col-7 { grid-column: span 7; } }

.gd-card__head { display: flex; flex-direction: column; gap: 10px; }
@media (min-width: 640px) { .gd-card__head { flex-direction: row; align-items: flex-start; justify-content: space-between; } }
.gd-title-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.gd-toggle-group { display: flex; align-items: center; gap: 2px; background: var(--dp-surface-container-low); padding: 4px; border-radius: 8px; font-size: 12px; flex-shrink: 0; }
.gd-toggle { padding: 5px 10px; border-radius: 6px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.gd-toggle--active { background: var(--dp-surface-container-lowest); color: var(--dp-primary); }

.gd-table-wrap { overflow-x: auto; }
.gd-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; min-width: 560px; }
.gd-table thead tr { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; font-weight: 700; }
.gd-table th { padding: 10px 12px; }
.gd-table th:first-child { border-radius: 6px 0 0 6px; }
.gd-table th:last-child { border-radius: 0 6px 6px 0; }
.gd-table tbody tr { border-bottom: 1px solid var(--dp-outline-variant); transition: background 0.15s ease; }
.gd-table tbody tr:last-child { border-bottom: none; }
.gd-table tbody tr:hover { background: var(--dp-surface-container-low); }
.gd-table td { padding: 12px; vertical-align: middle; }
.gd-right { text-align: right; }
.gd-flex-icon { display: inline-flex; align-items: center; gap: 5px; }
.gd-tag-row { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 4px; }

.gd-note-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; background: var(--dp-surface-container-low); padding: 10px 12px; border-radius: 8px; font-size: 11px; color: var(--dp-on-surface-variant); }
.gd-note-bar span { display: flex; align-items: center; gap: 6px; }
.gd-link { font-weight: 700; color: var(--dp-primary); text-decoration: none; background: none; border: none; cursor: pointer; font-size: 12px; font-family: var(--dp-font-sans); }
.gd-link:hover { text-decoration: underline; }

.gd-port-list { display: flex; flex-direction: column; gap: 12px; }
.gd-port { background: var(--dp-surface-container-low); padding: 10px; border-radius: 8px; }
.gd-port__head { display: flex; align-items: center; justify-content: space-between; font-size: 12px; margin-bottom: 4px; gap: 6px; }
.gd-bar { width: 100%; height: 6px; border-radius: 999px; background: var(--dp-surface-container-highest); overflow: hidden; }
.gd-bar__fill { height: 100%; border-radius: 999px; }
.gd-bar__fill--primary { background: var(--dp-primary); }
.gd-bar__fill--secondary { background: var(--dp-secondary); }
.gd-port__meta { display: flex; justify-content: space-between; font-size: 10px; color: var(--dp-on-surface-variant); margin-top: 4px; }
.gd-lanes { margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); font-size: 11px; }
.gd-lane { display: flex; align-items: center; justify-content: space-between; padding: 4px 0; color: var(--dp-on-surface-variant); }

.gd-actions-inline { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.gd-actions-inline--end { justify-content: flex-end; }

.gd-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 14px; border-radius: 8px; border: none;
    font-size: 12px; font-weight: 700; cursor: pointer; transition: background 0.15s ease, color 0.15s ease; font-family: var(--dp-font-sans); white-space: nowrap;
}
.gd-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.gd-btn--primary:hover { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.gd-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.gd-btn--muted:hover { background: var(--dp-surface-dim); }
.gd-btn--sm { padding: 6px 10px; }
.gd-btn--full { width: 100%; }

.gd-lifecycle { display: flex; flex-direction: column; gap: 10px; margin: 14px 0; }
.gd-lifecycle__step { display: flex; align-items: center; gap: 10px; background: var(--dp-surface-container-low); padding: 10px; border-radius: 8px; }
.gd-lifecycle__badge { width: 30px; height: 30px; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; color: var(--dp-on-primary); }
.gd-lifecycle__badge--primary { background: var(--dp-primary); }
.gd-lifecycle__badge--secondary { background: var(--dp-secondary); color: var(--dp-on-secondary-container); }
.gd-lifecycle__body { flex: 1; min-width: 0; }
.gd-lifecycle__row { display: flex; align-items: center; justify-content: space-between; }

.gd-footline { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); font-size: 12px; }

.gd-level { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 2px 6px; border-radius: 4px; flex-shrink: 0; margin-top: 2px; }
.gd-level--error { background: var(--dp-error); color: var(--dp-on-error); }
.gd-level--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.gd-level--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.gd-action-list { display: flex; flex-direction: column; gap: 10px; }
.gd-action-item { display: flex; flex-direction: column; gap: 10px; background: var(--dp-surface-container-low); padding: 12px; border-radius: 8px; }
@media (min-width: 640px) { .gd-action-item { flex-direction: row; align-items: center; justify-content: space-between; } }
.gd-action-item__body { display: flex; align-items: flex-start; gap: 10px; }

.gd-cal-list { display: flex; flex-direction: column; gap: 10px; }
.gd-cal-item { display: flex; align-items: flex-start; gap: 10px; background: var(--dp-surface-container-low); padding: 10px; border-radius: 8px; }
.gd-cal-date { background: var(--dp-surface-container-lowest); border-radius: 6px; padding: 6px 10px; text-align: center; flex-shrink: 0; min-width: 46px; }

.gd-ai-head { display: flex; flex-direction: column; gap: 10px; }
@media (min-width: 768px) { .gd-ai-head { flex-direction: row; align-items: center; justify-content: space-between; } }
.gd-ai-icon { width: 40px; height: 40px; border-radius: 8px; background: var(--dp-primary); color: var(--dp-on-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.gd-prompt-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.gd-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: var(--dp-surface-container-low); border: none; color: var(--dp-on-surface); font-size: 12px; cursor: pointer; transition: background 0.15s ease; font-family: var(--dp-font-sans); }
.gd-chip:hover { background: var(--dp-surface-container-high); }
.gd-ai-response { margin-top: 14px; padding: 14px; border-radius: 8px; background: var(--dp-secondary-container); font-size: 12px; }
.gd-ai-response__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.gd-icon-close { background: none; border: none; color: var(--dp-on-surface-variant); cursor: pointer; display: flex; padding: 2px; transform: rotate(45deg); }
</style>
