<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import {
    ShoppingCart, Sell, DocumentAdd, Refresh,
    Shop, Files, Connection, Cloudy, Coin, DataAnalysis, Ship,
    Warning, CircleCheck, CircleCheckFilled, Clock, Position,
    Lightning,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

const props = defineProps({
    markets: { type: Array, default: () => [] },
    featuredLots: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    exchangeRates: { type: Array, default: () => [] },
    priceIndexes: { type: Array, default: () => [] },
    analysis: { type: Object, default: () => ({}) },
    demand: { type: Object, default: () => ({}) },
    opportunities: { type: Array, default: () => [] },
    topSellers: { type: Array, default: () => [] },
});

/* ── Dummy market content — illustrative only ───────────────────────── */
const heroActions = [
    { icon: ShoppingCart, label: 'Buy Coffee', tone: 'primary' },
    { icon: Sell, label: 'Sell Coffee', tone: 'muted' },
    { icon: DocumentAdd, label: 'Create RFQ', tone: 'muted' },
];

const kpis = [
    { icon: Files, label: 'Available Volume', value: '1,284', unit: 'MT', note: 'Physical inventory verified in warehouse' },
    { icon: Files, label: 'Active Lots', value: '86', unit: 'Lots', note: 'Ready for immediate contract allocation' },
    { icon: Coin, label: 'Open Spot Offers', value: '24', unit: 'Tranches', note: 'Avg pricing: $4.15/kg FOB Mombasa' },
    { icon: DataAnalysis, label: 'Active Buyer RFQs', value: '18', unit: 'Demand Orders', note: 'Aggregated bid volume: 540 MT', noteTone: 'primary' },
];

const decisionPanels = [
    { label: 'Price Parity', value: '$4.15', unit: '/kg Spot', sub: 'Benchmark: $4.08/kg', foot: '+1.7% Spread', footTone: 'primary', tag: 'Fair Value', tagTone: 'primary' },
    { label: 'Supply Availability', value: '1,284 MT', sub: '86 lots listed on exchange', foot: 'Tightening', footTone: 'secondary', tag: 'Moderate', tagTone: 'secondary' },
    { label: 'Active Demand', value: '18 RFQs', sub: 'Buyer target: Robusta Screen 18', foot: 'High Inflow', footTone: 'primary', tag: 'Strong', tagTone: 'primary' },
    { label: 'Corridor Transit', value: '18–24 Days', sub: 'Mombasa to Jebel Ali / Dubai', foot: 'Mombasa: 2.4d delay', footTone: 'neutral', tag: 'Normal', tagTone: 'neutral' },
    { label: 'Macro Risk Index', value: 'EUDR Compliance', sub: 'GPS Polygon verification required', foot: 'Strict SLA', footTone: 'error', tag: 'Critical', tagTone: 'error' },
];

const macroFactors = [
    { label: 'Weather', value: 'Favorable (+0.4)', tone: 'primary' },
    { label: 'Vietnam Output', value: 'Drought (-12%)', tone: 'error' },
    { label: 'FX UGX/USD', value: 'Stable (3,710)', tone: 'primary' },
    { label: 'Red Sea Shipping', value: 'Cape Reroute (+6d)', tone: 'secondary' },
];

const priceTabs = ['All Markets', 'Robusta', 'Arabica'];
const priceTab = ref('All Markets');

const dummyBenchmarkPrices = [
    { name: 'Uganda Robusta', dot: 'primary', port: 'Mombasa (FOB)', grade: 'Screen 18', gradeTone: 'neutral', price: '$4.18', change: '+1.9%', changeTone: 'primary', vol: '420 MT', cta: 'View' },
    { name: 'Uganda Robusta', dot: 'primary', port: 'Mombasa (FOB)', grade: 'Screen 15', gradeTone: 'neutral', price: '$4.02', change: '+0.8%', changeTone: 'primary', vol: '310 MT', cta: 'View' },
    { name: 'Bugisu Arabica', dot: 'secondary', port: 'Mombasa (FOB)', grade: 'Grade AA', gradeTone: 'secondary', price: '$5.40', change: '-0.4%', changeTone: 'error', vol: '180 MT', cta: 'View' },
    { name: 'Rwenzori Natural', dot: 'secondary', port: 'Mombasa (FOB)', grade: 'Drugar Clean', gradeTone: 'neutral', price: '$4.85', change: '+2.1%', changeTone: 'primary', vol: '95 MT', cta: 'View' },
    { name: 'Vietnam Robusta', dot: 'neutral', port: 'Ho Chi Minh (FOB)', grade: 'Grade 2, 5%', gradeTone: 'neutral', price: '$4.42', change: '+3.2%', changeTone: 'primary', vol: '620 MT', cta: 'View' },
];

/* Real markets-table rows shaped to match the benchmark row layout, one
   field at a time falling back to the matching dummy row wherever the
   market record doesn't carry that data (no historical 24h change/volume
   tracking exists yet, so those columns always fall back). */
const benchmarkPrices = computed(() => {
    if (!props.markets.length) return dummyBenchmarkPrices;

    return props.markets.map((market, i) => {
        const fallback = dummyBenchmarkPrices[i % dummyBenchmarkPrices.length];

        return {
            id: market.id,
            name: market.name || fallback.name,
            dot: fallback.dot,
            port: market.origin || fallback.port,
            grade: market.process || market.type || fallback.grade,
            gradeTone: fallback.gradeTone,
            price: market.price_per_kg ? `$${Number(market.price_per_kg).toFixed(2)}` : fallback.price,
            change: fallback.change,
            changeTone: fallback.changeTone,
            vol: market.quantity ? `${market.quantity} ${market.unit || 'MT'}` : fallback.vol,
            cta: 'View',
        };
    });
});

const filteredBenchmarkPrices = computed(() => {
    if (priceTab.value === 'All Markets') return benchmarkPrices.value;

    return benchmarkPrices.value.filter((row) => row.grade.toLowerCase().includes(priceTab.value.toLowerCase()) || row.name.toLowerCase().includes(priceTab.value.toLowerCase()));
});

/* ── Benchmark table pagination — 5 rows per page ───────────────────── */
const benchmarkPage = ref(1);
const benchmarkPageSize = 5;

watch(priceTab, () => { benchmarkPage.value = 1; });

const pagedBenchmarkPrices = computed(() => {
    const start = (benchmarkPage.value - 1) * benchmarkPageSize;
    return filteredBenchmarkPrices.value.slice(start, start + benchmarkPageSize);
});

function viewBenchmarkRow(row) {
    if (row.id) {
        router.visit(route('market.show', row.id));
        return;
    }
    placeholderAction(`${row.cta} ${row.name}`);
}

const trendRanges = ['1M', '3M', '1Y'];
const trendRange = ref('1M');

const farmgateRows = [
    { name: 'Uganda Robusta (Kiboko)', ugx: 'UGX 12,400', usd: '$3.34/kg', milling: '$0.32/kg', fob: '$4.18/kg', spread: '+$0.52/kg', margin: '12.4%' },
    { name: 'Bugisu Arabica AA (Parchment)', ugx: 'UGX 16,500', usd: '$4.44/kg', milling: '$0.38/kg', fob: '$5.40/kg', spread: '+$0.58/kg', margin: '10.7%' },
    { name: 'Rwenzori Drugar Clean', ugx: 'UGX 14,800', usd: '$3.98/kg', milling: '$0.35/kg', fob: '$4.85/kg', spread: '+$0.52/kg', margin: '10.7%' },
];

const originProfiles = [
    { country: 'Uganda', dot: 'primary', phase: 'Main Crop Harvest', price: '$4.18/kg FOB', flow: 'Export Flow: High', flowTone: 'primary' },
    { country: 'Vietnam', dot: 'secondary', phase: 'Off-Season Transition', price: '$4.42/kg FOB', flow: 'Export Flow: Constrained', flowTone: 'secondary' },
    { country: 'Brazil', dot: 'primary', phase: 'Conilon / Arabica Tail', price: '$4.65/kg FOB', flow: 'Export Flow: Peak', flowTone: 'primary' },
    { country: 'Ethiopia', dot: 'neutral', phase: 'Washed Prep / ECX', price: '$5.90/kg FOB', flow: 'Export Flow: Moderate', flowTone: 'neutral' },
];

const balanceMetrics = [
    { label: 'East Africa Supply Pressure', value: '68% (Adequate)', tone: 'primary', pct: 68 },
    { label: 'European / UAE Buyer Demand', value: '84% (High Appetite)', tone: 'secondary', pct: 84 },
    { label: 'EUDR Traceability Readiness', value: '91% (Compliant)', tone: 'primary', pct: 91 },
];

const opportunityCards = [
    { tag: 'Price Arbitrage', tagTone: 'primary', stat: '-$0.24/kg Spread', statTone: 'primary', title: 'Uganda Screen 18 Discount vs Vietnam', body: 'Vietnamese drought has elevated Ho Chi Minh FOB to $4.42/kg. Uganda Screen 18 at $4.18/kg FOB offers instant $240/MT cost reduction for identical soluble/espresso specs.', cta: 'Review Matching Lots (6 Available)' },
    { tag: 'Origin Alert', tagTone: 'secondary', stat: '120 MT Tranche', statTone: 'secondary', title: 'Verified Deforestation-Free Bugisu AA', body: 'Direct cooperative lot from Mbale with validated polygon coordinate boundaries on chain. Zero risk under EU Deforestation Regulation enforcement.', cta: 'Access Inspection Certs' },
    { tag: 'Freight Optimizer', tagTone: 'tertiary', stat: 'Jebel Ali Route', statTone: 'neutral', title: 'Consolidated UAE Direct Vessel Slot', body: 'Anchor buyer departing Mombasa on Maersk feeder line Nov 4. Booking open for 4x 20ft FCLs at discounted negotiated rate of $1,450/box.', cta: 'Join Shipping Slot' },
];

const spotLots = [
    { id: 'LOT-UG-8821', spec: 'Uganda Screen 18 Robusta Clean', region: 'Masaka / Central', alt: '1,200m ASL', grade: 'Fine Robusta 83.5', gradeTone: 'primary', vol: '60.0 MT', bags: '1,000 Bags (60kg)', price: '$4.18', total: '$250,800 Total', moisture: '11.8%', seller: 'Great Lakes Ltd', sellerNote: 'Tier-1 Exporter · Escrow Ready', checked: true },
    { id: 'LOT-UG-9042', spec: 'Bugisu Arabica Grade AA Washed', region: 'Mt. Elgon / Mbale', alt: '1,850m ASL', grade: 'Specialty 86.0', gradeTone: 'secondary', vol: '19.2 MT', bags: '320 Bags (60kg)', price: '$5.40', total: '$103,680 Total', moisture: '11.4%', seller: 'Mbale Coffee Union', sellerNote: 'EUDR Certified Polygon', sellerNoteTone: 'primary', checked: true },
    { id: 'LOT-RW-3021', spec: 'Rwenzori Natural Drugar Arabica', region: 'Kasese / Rwenzori', alt: '1,600m ASL', grade: 'Commercial 82.5', gradeTone: 'neutral', vol: '38.4 MT', bags: '640 Bags (60kg)', price: '$4.85', total: '$186,240 Total', moisture: '12.1%', seller: 'Rwenzori Apex Mill', sellerNote: 'Direct Origin Warehoused', checked: true },
    { id: 'LOT-UG-7714', spec: 'Uganda Screen 15 Robusta Standard', region: 'Luweero / Central', alt: '1,150m ASL', grade: 'Commercial 80.0', gradeTone: 'neutral', vol: '76.8 MT', bags: '1,280 Bags (60kg)', price: '$4.02', total: '$308,736 Total', moisture: '12.0%', seller: 'Nalukolongo Hub', sellerNote: 'UCDA Stamp Verified', checked: false },
    { id: 'LOT-TZ-4402', spec: 'Tanzania Kilimanjaro Plantation AA', region: 'Moshi / Northern', alt: '1,700m ASL', grade: 'Specialty 84.5', gradeTone: 'secondary', vol: '21.6 MT', bags: '360 Bags (60kg)', price: '$5.25', total: '$113,400 Total', moisture: '11.6%', seller: 'Kilimanjaro Traders', sellerNote: 'Dar es Salaam Port Ready', checked: false },
];

const comparisonLots = [
    { id: 'LOT-UG-8821', type: 'Robusta', typeTone: 'primary', price: '$4.18/kg FOB', spec: 'Fine Robusta Screen 18 · Masaka', rows: [['CQI Cup Score', '83.5 pts'], ['Altitude', '1,200m'], ['Moisture & Def', '11.8% · 2/300g'], ['EUDR Polygons', '100% Mapped', 'primary'], ['Landed Dubai Est', '$4.48/kg']] },
    { id: 'LOT-UG-9042', type: 'Arabica AA', typeTone: 'secondary', price: '$5.40/kg FOB', spec: 'Bugisu Washed · Mt. Elgon', rows: [['CQI Cup Score', '86.0 pts (Specialty)'], ['Altitude', '1,850m'], ['Moisture & Def', '11.4% · 0/300g'], ['EUDR Polygons', '100% Mapped', 'primary'], ['Landed Dubai Est', '$5.72/kg']] },
    { id: 'LOT-RW-3021', type: 'Natural Arabica', typeTone: 'neutral', price: '$4.85/kg FOB', spec: 'Rwenzori Drugar Clean · Kasese', rows: [['CQI Cup Score', '82.5 pts'], ['Altitude', '1,600m'], ['Moisture & Def', '12.1% · 4/300g'], ['EUDR Polygons', 'In Verification'], ['Landed Dubai Est', '$5.16/kg']] },
];

const landedCostRows = [
    { label: 'Base FOB Mombasa Price', value: '$4.18 / kg' },
    { label: 'Inland Rail / Trucking Transit (Kampala → Mombasa)', value: '$0.08 / kg' },
    { label: 'Ocean Freight (Per 20ft FCL 19.2 MT)', value: '$0.14 / kg' },
    { label: 'Marine Cargo Insurance (All-Risk 110%)', value: '$0.02 / kg' },
    { label: 'Port Handling & Pre-Shipment Inspection (SGS)', value: '$0.06 / kg' },
];
const portOptions = ['Jebel Ali (Dubai)', 'Rotterdam (Europe)', 'Houston (USA)'];
const portSelection = ref(portOptions[0]);

const logisticsRows = [
    { icon: Ship, name: 'Port of Mombasa (KE)', note: 'Anchor wait: 2.4 days · Berth turnaround: 36h', tag: 'Fluid', tagTone: 'primary' },
    { icon: Ship, name: 'Jebel Ali Terminal (UAE)', note: 'Transit corridor: 18–22 days direct feeder', tag: 'Optimal', tagTone: 'primary' },
    { icon: Warning, name: 'Rotterdam Terminal (NL)', note: 'Cape of Good Hope reroute: 32–36 days transit', tag: '+6d Delay', tagTone: 'secondary' },
];

const microclimates = [
    { name: 'Mukono / Masaka (UG)', status: 'Optimal', statusTone: 'primary', meta: '24°C · Rain: 45mm/wk', note: 'Drying conditions ideal on raised patios. Cherry uniform ripening confirmed.' },
    { name: 'Mbale / Bugisu (UG)', status: 'Normal', statusTone: 'primary', meta: '19°C · Rain: 60mm/wk', note: 'Mountain showers steady. Pulping washing stations running at 100% capacity.' },
    { name: 'Central Highlands (VN)', status: 'Drought Alert', statusTone: 'error', meta: '32°C · Rain: -35% norm', note: 'Dak Lak province groundwater depletion. Projected 10-15% crop reduction.' },
    { name: 'Cerrado Mineiro (BR)', status: 'Moderate', statusTone: 'secondary', meta: '28°C · Rain: Dry season', note: 'Flowering initiated under irrigated sections; awaiting broad seasonal rains.' },
];

const tradeWire = [
    { tag: 'European Commission · EUDR', tagTone: 'primary', time: '2 hours ago', title: 'EU Parliament Confirms Traceability Standards for East African Exporters', body: 'Due diligence statements with polygon geo-coordinates validated via national coffee registries are recognized as priority fast-track entries.' },
    { tag: 'Uganda Coffee Development Authority', tagTone: 'secondary', time: '5 hours ago', title: 'September Coffee Export Volume Reaches Record 785,000 Bags', body: 'High international Robusta prices spur farmer deliveries and rapid processing turnaround across Greater Masaka.' },
    { tag: 'Freight Rate Monitor', tagTone: 'neutral', time: 'Yesterday', title: 'Bunker Fuel Surcharges Stabilize on Middle East Shipping Lanes', body: 'Mombasa to Jebel Ali container rates flat at $1,400 to $1,550 for 20-foot standard dry boxes.' },
];

const calendarItems = [
    { when: 'Today', time: '14:00', tone: 'primary', title: 'UCDA Central Quality Floor', note: 'Daily price guide release & sample inspections' },
    { when: 'Tomorrow', time: '10:00', tone: 'neutral', title: 'Dubai Roaster Group Tender Deadline', note: 'RFQ matching closing for 300 MT Robusta' },
    { when: '28 Oct', time: '18:00', tone: 'neutral', title: 'CMA CGM Mombasa Terminal Cut-off', note: 'Voyage 409E gate-in deadline for Jebel Ali discharge' },
];

const checklistItems = [
    { icon: CircleCheckFilled, tone: 'primary', title: 'Price Competitiveness', note: 'Validated within 1.7% of official ICE/UCDA parity benchmarks.' },
    { icon: CircleCheckFilled, tone: 'primary', title: 'Moisture & Grade Verification', note: 'Under 12.0% moisture, CQI score verified by certified Q-grader.' },
    { icon: CircleCheckFilled, tone: 'primary', title: 'EUDR Polygon Geo-Mapping', note: 'Farm boundaries digitized and cross-verified with satellite deforestation maps.' },
    { icon: CircleCheckFilled, tone: 'primary', title: 'Escrow Account Clearance', note: 'Direct custody tier-1 bank account (Stanbic Uganda) verified active.' },
    { icon: CircleCheckFilled, tone: 'primary', title: 'Physical Warehouse Inspection', note: 'Coffee inspected in dry palletized bags with phytosanitary permit on file.' },
    { icon: Clock, tone: 'secondary', title: 'Vessel Space Guarantee', note: 'Container booking pending final carrier allocation (within 48h).' },
];

function placeholderAction(label) {
    ElMessage.info(`${label} (dummy preview).`);
}
</script>

<template>
    <DesignPreviewLayout title="Coffee Market">
        <Head title="Coffee Market" />

        <!-- 1. PAGE HERO HEADER -->
        <section class="mkt-card mkt-hero">
            <div class="mkt-hero__top">
                <div>
                    <div class="mkt-title-row">
                        <h1 class="dp-display-md">Coffee Market</h1>
                        <span class="mkt-live-badge"><span class="mkt-dot"></span> Live Floor</span>
                    </div>
                    <p class="dp-body-md mkt-muted">Institutional coffee market intelligence, benchmark parity, and real-time physical lot execution.</p>
                </div>
                <div class="mkt-hero__actions">
                    <button v-for="action in heroActions" :key="action.label" type="button" class="mkt-btn" :class="`mkt-btn--${action.tone}`" @click="placeholderAction(action.label)">
                        <el-icon :size="15"><component :is="action.icon" /></el-icon>
                        <span>{{ action.label }}</span>
                    </button>
                </div>
            </div>

            <div class="mkt-status-line">
                <div class="mkt-status-line__left">
                    <span class="mkt-flex-icon mkt-strong mkt-icon--primary"><el-icon :size="14"><Position /></el-icon> Trading Session Open</span>
                    <span class="mkt-muted">Last tick: 42s ago</span>
                    <span class="mkt-muted">Coverage: <strong class="mkt-on">East Africa · LatAm · SE Asia</strong></span>
                    <span class="mkt-muted">Currency: <strong class="mkt-on">USD/kg (Metric)</strong></span>
                    <span class="mkt-muted">Feeds: <strong class="mkt-on">ICO · ICE Futures · UCDA</strong></span>
                </div>
                <div class="mkt-status-line__right">
                    <span class="mkt-tag-mini">Validated Parity</span>
                    <button type="button" class="mkt-icon-btn" title="Force Refresh Data" @click="placeholderAction('Force Refresh Data')"><el-icon :size="15"><Refresh /></el-icon></button>
                </div>
            </div>

            <div class="mkt-kpi-row">
                <div v-for="kpi in kpis" :key="kpi.label" class="mkt-kpi">
                    <div class="mkt-kpi__head">
                        <span class="dp-label-md mkt-muted">{{ kpi.label }}</span>
                        <el-icon :size="16" class="mkt-icon--primary"><component :is="kpi.icon" /></el-icon>
                    </div>
                    <div class="dp-display-md mkt-kpi__value">{{ kpi.value }} <span class="dp-body-md mkt-muted">{{ kpi.unit }}</span></div>
                    <p class="dp-caption" :class="kpi.noteTone ? `mkt-icon--${kpi.noteTone} mkt-strong` : 'mkt-muted'">{{ kpi.note }}</p>
                </div>
            </div>
        </section>

        <!-- 3. MARKET DECISION CENTER -->
        <section class="mkt-card">
            <div class="mkt-card__head">
                <div>
                    <div class="mkt-title-row"><el-icon :size="18" class="mkt-icon--primary"><DataAnalysis /></el-icon><h2 class="dp-headline-md">Market Decision Center</h2></div>
                    <p class="dp-caption mkt-muted">Aggregated execution indicators calibrated against spot physical flows &amp; ICE benchmarks.</p>
                </div>
                <span class="mkt-tag-mini">Composite Sentiment: <strong class="mkt-icon--primary">BUY-ACCUMULATE</strong></span>
            </div>
            <div class="mkt-decision-grid">
                <div v-for="panel in decisionPanels" :key="panel.label" class="mkt-decision">
                    <div>
                        <div class="dp-label-md mkt-muted">{{ panel.label }}</div>
                        <div class="dp-headline-sm mkt-strong mkt-mt-xs">{{ panel.value }} <span class="dp-caption mkt-muted">{{ panel.unit }}</span></div>
                        <div class="dp-caption mkt-muted">{{ panel.sub }}</div>
                    </div>
                    <div class="mkt-decision__foot">
                        <span class="dp-caption mkt-strong" :class="`mkt-icon--${panel.footTone}`">{{ panel.foot }}</span>
                        <span class="mkt-tag-mini" :class="`mkt-tag-mini--${panel.tagTone}`">{{ panel.tag }}</span>
                    </div>
                </div>
            </div>
            <div class="mkt-factors-row">
                <span class="dp-caption mkt-muted">Core Macro Drivers:</span>
                <span v-for="f in macroFactors" :key="f.label" class="mkt-factor-chip">{{ f.label }}: <strong :class="`mkt-icon--${f.tone}`">{{ f.value }}</strong></span>
            </div>
        </section>

        <!-- 4. BENCHMARK PRICES + TREND CHART -->
        <section class="mkt-grid-12">
            <div class="mkt-card mkt-col-8">
                <div class="mkt-card__head">
                    <div>
                        <h2 class="dp-headline-md">Cash Market Benchmark Prices</h2>
                        <p class="dp-caption mkt-muted">Official exchange benchmark prices updated continuously</p>
                    </div>
                    <div class="mkt-toggle-group">
                        <button v-for="tab in priceTabs" :key="tab" type="button" class="mkt-toggle" :class="{ 'mkt-toggle--active': priceTab === tab }" @click="priceTab = tab">{{ tab }}</button>
                    </div>
                </div>
                <div class="mkt-table-wrap">
                    <table class="mkt-table">
                        <thead>
                            <tr><th>Commodity</th><th>Origin / Port</th><th>Grade</th><th>Spot ($/kg)</th><th>24h Chg</th><th>24h Volume</th><th class="mkt-right">Action</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="!filteredBenchmarkPrices.length">
                                <td colspan="7" class="mkt-center dp-caption mkt-muted">No listings match this filter.</td>
                            </tr>
                            <tr v-for="row in pagedBenchmarkPrices" :key="row.id ?? row.name">
                                <td><span class="mkt-flex-icon mkt-strong"><span class="mkt-dot" :class="`mkt-dot--${row.dot}`"></span> {{ row.name }}</span></td>
                                <td class="mkt-muted">{{ row.port }}</td>
                                <td><span class="mkt-tag-mini" :class="`mkt-tag-mini--${row.gradeTone}`">{{ row.grade }}</span></td>
                                <td class="mkt-strong dp-mono">{{ row.price }}</td>
                                <td class="mkt-strong" :class="`mkt-icon--${row.changeTone}`">{{ row.change }}</td>
                                <td class="mkt-muted dp-mono">{{ row.vol }}</td>
                                <td class="mkt-right"><button type="button" class="mkt-link" @click="viewBenchmarkRow(row)">{{ row.cta }}</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="filteredBenchmarkPrices.length" class="mkt-pagination">
                    <el-pagination
                        v-model:current-page="benchmarkPage"
                        :page-size="benchmarkPageSize"
                        :total="filteredBenchmarkPrices.length"
                        layout="total, prev, pager, next"
                        background
                    />
                </div>
                <div class="mkt-footline">
                    <span class="dp-caption mkt-muted">Benchmark basis: 60kg export bags, seaworthy jute or grainpro lined.</span>
                    <a href="#" class="mkt-link" @click.prevent="placeholderAction('Complete Pricing Sheet')">Complete Pricing Sheet →</a>
                </div>
            </div>

            <div class="mkt-card mkt-col-4">
                <div>
                    <div class="mkt-card__head">
                        <h2 class="dp-headline-md">Price Trajectory &amp; Spreads</h2>
                        <div class="mkt-toggle-group mkt-toggle-group--sm">
                            <button v-for="r in trendRanges" :key="r" type="button" class="mkt-toggle" :class="{ 'mkt-toggle--active': trendRange === r }" @click="trendRange = r">{{ r }}</button>
                        </div>
                    </div>
                    <p class="dp-caption mkt-muted mkt-mb-sm">Uganda Screen 18 vs ICE Robusta Futures</p>
                    <div class="mkt-chart-box">
                        <div class="mkt-series-row">
                            <span class="mkt-flex-icon dp-caption mkt-muted"><span class="mkt-dot mkt-dot--primary"></span> UG Robusta Scr 18 ($4.18)</span>
                            <span class="mkt-flex-icon dp-caption mkt-muted"><span class="mkt-dot mkt-dot--secondary"></span> ICE Robusta London ($4.05)</span>
                        </div>
                        <svg viewBox="0 0 360 140" class="mkt-chart__svg">
                            <line x1="0" x2="360" y1="20" y2="20" stroke="var(--dp-outline-variant)" stroke-dasharray="2,2" stroke-opacity="0.4" />
                            <line x1="0" x2="360" y1="60" y2="60" stroke="var(--dp-outline-variant)" stroke-dasharray="2,2" stroke-opacity="0.4" />
                            <line x1="0" x2="360" y1="100" y2="100" stroke="var(--dp-outline-variant)" stroke-dasharray="2,2" stroke-opacity="0.4" />
                            <polygon points="10,110 50,95 90,102 130,80 170,75 210,60 250,55 290,40 330,35 350,30 350,135 10,135" fill="var(--dp-primary)" fill-opacity="0.08" />
                            <polyline points="10,110 50,95 90,102 130,80 170,75 210,60 250,55 290,40 330,35 350,30" fill="none" stroke="var(--dp-primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            <polyline points="10,120 50,115 90,110 130,95 170,88 210,80 250,72 290,60 330,55 350,50" fill="none" stroke="var(--dp-secondary)" stroke-width="1.8" stroke-dasharray="3,3" stroke-linecap="round" />
                            <circle cx="350" cy="30" r="4" fill="var(--dp-primary)" />
                            <circle cx="350" cy="30" r="7" fill="var(--dp-primary)" fill-opacity="0.2" />
                        </svg>
                        <div class="mkt-chart-foot dp-mono">
                            <span>30 Days Ago</span><span>15 Days Ago</span><span class="mkt-icon--primary mkt-strong">Today</span>
                        </div>
                    </div>
                </div>
                <div class="mkt-note-box">
                    <div class="mkt-note-box__row"><span class="dp-caption mkt-muted">Mombasa Export Parity:</span><span class="dp-caption mkt-strong mkt-icon--primary">+13.4 c/lb over ICE</span></div>
                    <p class="dp-caption mkt-muted">East African screen 18 quality premium holds firm due to tight European port stocks.</p>
                </div>
            </div>
        </section>

        <!-- 5. FARMGATE VS FOB ARBITRAGE -->
        <section class="mkt-card">
            <div class="mkt-card__head">
                <div>
                    <div class="mkt-title-row"><el-icon :size="18" class="mkt-icon--primary"><Coin /></el-icon><h2 class="dp-headline-md">Farmgate Parity vs. FOB Mombasa Export Arbitrage</h2></div>
                    <p class="dp-caption mkt-muted">Live transparent breakdown of internal buying costs in Uganda Shillings (UGX) to FOB ocean export margin.</p>
                </div>
                <span class="dp-caption mkt-muted dp-mono">FX Parity Basis: 1 USD = 3,710 UGX</span>
            </div>
            <div class="mkt-table-wrap">
                <table class="mkt-table">
                    <thead>
                        <tr><th>Coffee Type / Origin</th><th>Farmgate (UGX/kg)</th><th>Farmgate USD Equiv.</th><th>Milling &amp; Trucking</th><th>FOB Mombasa Price</th><th>Gross Spread</th><th>Gross Margin %</th><th class="mkt-right">Origin Action</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in farmgateRows" :key="row.name">
                            <td class="mkt-strong">{{ row.name }}</td>
                            <td class="dp-mono">{{ row.ugx }}</td>
                            <td class="dp-mono mkt-muted">{{ row.usd }}</td>
                            <td class="dp-mono mkt-muted">{{ row.milling }}</td>
                            <td class="mkt-strong">{{ row.fob }}</td>
                            <td class="mkt-strong mkt-icon--primary">{{ row.spread }}</td>
                            <td><span class="mkt-tag-mini mkt-tag-mini--primary">{{ row.margin }}</span></td>
                            <td class="mkt-right"><button type="button" class="mkt-btn mkt-btn--muted mkt-btn--sm" @click="placeholderAction(`Audit Parity: ${row.name}`)">Audit Parity</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 6. ORIGIN PROFILES + MARKET BALANCE -->
        <section class="mkt-grid-12">
            <div class="mkt-card mkt-col-8">
                <div class="mkt-card__head">
                    <div>
                        <h2 class="dp-headline-md">Primary Origin Execution Profiles</h2>
                        <p class="dp-caption mkt-muted">Production calendar cycles and export availability</p>
                    </div>
                    <span class="dp-caption mkt-muted">8 Key Origins Tracked</span>
                </div>
                <div class="mkt-origin-grid">
                    <div v-for="o in originProfiles" :key="o.country" class="mkt-origin">
                        <div class="mkt-origin__head"><span class="mkt-strong dp-body-md">{{ o.country }}</span><span class="mkt-dot" :class="`mkt-dot--${o.dot}`"></span></div>
                        <div class="dp-caption mkt-muted">{{ o.phase }}</div>
                        <div class="dp-body-md mkt-strong mkt-mt-xs">{{ o.price }}</div>
                        <div class="dp-caption mkt-strong" :class="`mkt-icon--${o.flowTone}`">{{ o.flow }}</div>
                    </div>
                </div>
            </div>

            <div class="mkt-card mkt-col-4">
                <div>
                    <h2 class="dp-headline-md">Market Balance Metric</h2>
                    <p class="dp-caption mkt-muted mkt-mb-sm">Physical spot inventory vs recorded procurement orders</p>
                    <div class="mkt-balance-list">
                        <div v-for="b in balanceMetrics" :key="b.label">
                            <div class="mkt-balance__head"><span class="dp-caption mkt-strong">{{ b.label }}</span><span class="dp-caption mkt-strong" :class="`mkt-icon--${b.tone}`">{{ b.value }}</span></div>
                            <div class="mkt-bar"><div class="mkt-bar__fill" :class="`mkt-bar__fill--${b.tone}`" :style="{ width: b.pct + '%' }"></div></div>
                        </div>
                    </div>
                </div>
                <div class="mkt-note-box">
                    <div class="mkt-note-box__row"><span class="dp-caption mkt-muted">Physical Balance Outlook:</span><span class="dp-caption mkt-strong mkt-icon--primary">Bullish on Robusta Screener</span></div>
                </div>
            </div>
        </section>

        <!-- 7. ARBITRAGE & SOURCING OPPORTUNITIES -->
        <section class="mkt-card">
            <div class="mkt-title-row mkt-mb-sm">
                <el-icon :size="18" class="mkt-icon--primary"><Lightning /></el-icon>
                <h2 class="dp-headline-md">Data-Identified Arbitrage &amp; Sourcing Opportunities</h2>
                <span class="dp-caption mkt-muted">Updated real-time by AI Valuation Engine</span>
            </div>
            <div class="mkt-opps-grid">
                <div v-for="opp in opportunityCards" :key="opp.title" class="mkt-opp-card">
                    <div>
                        <div class="mkt-opp-card__head">
                            <span class="mkt-tag-mini" :class="`mkt-tag-mini--${opp.tagTone}`">{{ opp.tag }}</span>
                            <span class="dp-mono mkt-strong" :class="`mkt-icon--${opp.statTone}`">{{ opp.stat }}</span>
                        </div>
                        <h3 class="dp-body-lg mkt-strong">{{ opp.title }}</h3>
                        <p class="dp-caption mkt-muted">{{ opp.body }}</p>
                    </div>
                    <button type="button" class="mkt-btn mkt-btn--muted mkt-btn--full" @click="placeholderAction(opp.cta)">{{ opp.cta }}</button>
                </div>
            </div>
        </section>

        <!-- 8. BUY COFFEE — SPOT & FORWARD LOTS -->
        <section class="mkt-card">
            <div class="mkt-card__head">
                <div>
                    <div class="mkt-title-row"><el-icon :size="18" class="mkt-icon--primary"><Shop /></el-icon><h2 class="dp-headline-md">Buy Coffee — Spot &amp; Forward Lots</h2></div>
                    <p class="dp-caption mkt-muted">Inspected physical coffee available for immediate purchase, escrow allocation, or firm negotiation.</p>
                </div>
                <div class="mkt-actions-inline">
                    <button type="button" class="mkt-btn mkt-btn--muted" @click="placeholderAction('Compare Selected')"><el-icon :size="15"><Connection /></el-icon> Compare Selected (3)</button>
                    <span class="dp-caption mkt-muted">Showing <strong class="mkt-on">5 of 86 Lots</strong></span>
                </div>
            </div>
            <div class="mkt-table-wrap">
                <table class="mkt-table">
                    <thead>
                        <tr><th class="mkt-cb-col"></th><th>Lot Code / Coffee Spec</th><th>Origin / Region</th><th>Grade &amp; CQI</th><th>Available Vol.</th><th>Price (FOB Mombasa)</th><th>Moisture</th><th>Seller Verification</th><th class="mkt-right">Actions</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="lot in spotLots" :key="lot.id">
                            <td class="mkt-cb-col"><input type="checkbox" :checked="lot.checked" class="mkt-checkbox" @click.prevent /></td>
                            <td><div class="mkt-strong">{{ lot.id }}</div><div class="dp-caption mkt-muted">{{ lot.spec }}</div></td>
                            <td><div class="mkt-strong">{{ lot.region }}</div><div class="dp-caption mkt-muted dp-mono">{{ lot.alt }}</div></td>
                            <td><span class="mkt-tag-mini" :class="`mkt-tag-mini--${lot.gradeTone}`">{{ lot.grade }}</span></td>
                            <td><div class="mkt-strong dp-mono">{{ lot.vol }}</div><div class="dp-caption mkt-muted">{{ lot.bags }}</div></td>
                            <td><div class="mkt-strong dp-mono">{{ lot.price }} <span class="dp-caption mkt-muted">/kg</span></div><div class="dp-caption mkt-muted dp-mono">{{ lot.total }}</div></td>
                            <td class="dp-mono">{{ lot.moisture }}</td>
                            <td>
                                <div class="mkt-flex-icon mkt-strong"><el-icon :size="14" class="mkt-icon--primary"><CircleCheck /></el-icon> {{ lot.seller }}</div>
                                <div class="dp-caption" :class="lot.sellerNoteTone ? `mkt-icon--${lot.sellerNoteTone} mkt-strong` : 'mkt-muted'">{{ lot.sellerNote }}</div>
                            </td>
                            <td class="mkt-right">
                                <div class="mkt-actions-inline mkt-actions-inline--end">
                                    <button type="button" class="mkt-btn mkt-btn--primary mkt-btn--sm" @click="placeholderAction(`Buy ${lot.id}`)">Buy</button>
                                    <button type="button" class="mkt-btn mkt-btn--muted mkt-btn--sm" @click="placeholderAction(`Offer ${lot.id}`)">Offer</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 9. ACTIVE LOT COMPARISON MATRIX -->
        <section class="mkt-card">
            <div class="mkt-card__head">
                <div>
                    <div class="mkt-title-row"><el-icon :size="18" class="mkt-icon--primary"><Files /></el-icon><h2 class="dp-headline-md">Active Lot Comparison Matrix</h2></div>
                    <p class="dp-caption mkt-muted">Technical audit of the 3 selected candidate lots for purchase</p>
                </div>
                <button type="button" class="mkt-link" @click="placeholderAction('Change Selection')">Change Selection</button>
            </div>
            <div class="mkt-compare-grid">
                <div v-for="lot in comparisonLots" :key="lot.id" class="mkt-compare-card">
                    <div>
                        <div class="mkt-compare-card__head"><span class="dp-body-lg mkt-strong">{{ lot.id }}</span><span class="mkt-tag-mini" :class="`mkt-tag-mini--${lot.typeTone}`">{{ lot.type }}</span></div>
                        <div class="dp-headline-sm mkt-strong mkt-mt-xs">{{ lot.price }}</div>
                        <div class="dp-caption mkt-muted">{{ lot.spec }}</div>
                        <div class="mkt-compare-rows">
                            <div v-for="r in lot.rows" :key="r[0]" class="mkt-compare-row"><span class="dp-caption mkt-muted">{{ r[0] }}:</span> <strong class="dp-caption" :class="r[2] ? `mkt-icon--${r[2]}` : 'mkt-on'">{{ r[1] }}</strong></div>
                        </div>
                    </div>
                    <button type="button" class="mkt-btn mkt-btn--primary mkt-btn--full" @click="placeholderAction(`Select ${lot.id} for allocation`)">Select for Allocation</button>
                </div>
            </div>
        </section>

        <!-- 10. LANDED COST ENGINE + LOGISTICS -->
        <section class="mkt-grid-12">
            <div class="mkt-card mkt-col-6">
                <div>
                    <div class="mkt-card__head">
                        <div>
                            <h2 class="dp-headline-md">Total Landed Cost Engine</h2>
                            <p class="dp-caption mkt-muted">Dynamic freight, port fees, &amp; cargo insurance simulator</p>
                        </div>
                        <el-select v-model="portSelection" class="mkt-select">
                            <el-option v-for="p in portOptions" :key="p" :label="p" :value="p" />
                        </el-select>
                    </div>
                    <div class="mkt-cost-rows">
                        <div v-for="row in landedCostRows" :key="row.label" class="mkt-cost-row">
                            <span class="dp-caption mkt-muted">{{ row.label }}</span>
                            <span class="dp-caption mkt-strong dp-mono">{{ row.value }}</span>
                        </div>
                    </div>
                </div>
                <div class="mkt-total-banner">
                    <div><div class="dp-caption mkt-total-banner__label">Estimated CIF Landed Rate</div><div class="dp-caption mkt-total-banner__sub">Full transit guarantee included</div></div>
                    <div class="dp-headline-md">$4.48 <span class="dp-caption">/kg</span></div>
                </div>
            </div>

            <div class="mkt-card mkt-col-6">
                <div>
                    <div class="mkt-card__head">
                        <div>
                            <h2 class="dp-headline-md">Logistics Corridors &amp; Port Wait Times</h2>
                            <p class="dp-caption mkt-muted">Real-time terminal congestion and maritime voyage durations</p>
                        </div>
                        <span class="mkt-tag-mini">AIS Monitored</span>
                    </div>
                    <div class="mkt-logistics-list">
                        <div v-for="row in logisticsRows" :key="row.name" class="mkt-logistics-row">
                            <div class="mkt-flex-icon"><el-icon :size="18" class="mkt-icon--primary"><component :is="row.icon" /></el-icon>
                                <div><div class="dp-body-md mkt-strong">{{ row.name }}</div><div class="dp-caption mkt-muted">{{ row.note }}</div></div>
                            </div>
                            <span class="mkt-tag-mini" :class="`mkt-tag-mini--${row.tagTone}`">{{ row.tag }}</span>
                        </div>
                    </div>
                </div>
                <div class="mkt-footline">
                    <span class="dp-caption mkt-muted">Carriers operating: Maersk, CMA CGM, MSC, Hapag-Lloyd</span>
                    <span class="dp-caption mkt-strong mkt-icon--primary">Live Schedule Active</span>
                </div>
            </div>
        </section>

        <!-- 11. MICROCLIMATES + TRADE WIRE -->
        <section class="mkt-grid-12">
            <div class="mkt-card mkt-col-6">
                <div class="mkt-card__head">
                    <div>
                        <h2 class="dp-headline-md">Regional Microclimates &amp; Harvest Impact</h2>
                        <p class="dp-caption mkt-muted">Satellite soil moisture and precipitation anomalies</p>
                    </div>
                    <el-icon :size="18" class="mkt-icon--neutral"><Cloudy /></el-icon>
                </div>
                <div class="mkt-weather-grid">
                    <div v-for="w in microclimates" :key="w.name" class="mkt-weather-card">
                        <div class="mkt-weather-card__head"><span class="dp-body-md mkt-strong">{{ w.name }}</span><span class="dp-caption mkt-strong" :class="`mkt-icon--${w.statusTone}`">{{ w.status }}</span></div>
                        <div class="dp-caption mkt-muted">{{ w.meta }}</div>
                        <p class="dp-caption mkt-muted">{{ w.note }}</p>
                    </div>
                </div>
            </div>

            <div class="mkt-card mkt-col-6">
                <div>
                    <div class="mkt-card__head">
                        <div>
                            <h2 class="dp-headline-md">Regulatory &amp; Market Intelligence Wire</h2>
                            <p class="dp-caption mkt-muted">Verified trade notices directly relevant to buyer execution</p>
                        </div>
                        <button type="button" class="mkt-link" @click="placeholderAction('View All Wires')">View All Wires</button>
                    </div>
                    <div class="mkt-wire-list">
                        <div v-for="n in tradeWire" :key="n.title" class="mkt-wire-item">
                            <div class="mkt-wire-item__head"><span class="dp-caption mkt-strong" :class="`mkt-icon--${n.tagTone}`">{{ n.tag }}</span><span class="dp-caption mkt-muted">{{ n.time }}</span></div>
                            <div class="dp-body-md mkt-strong">{{ n.title }}</div>
                            <p class="dp-caption mkt-muted">{{ n.body }}</p>
                        </div>
                    </div>
                </div>
                <div class="mkt-footline">
                    <span class="dp-caption mkt-muted">Bean Origin Desk Intelligence Wire</span>
                    <span class="dp-caption mkt-strong mkt-icon--primary">Terminal Feed Connected</span>
                </div>
            </div>
        </section>

        <!-- 12. CALENDAR + DUE DILIGENCE CHECKLIST -->
        <section class="mkt-grid-12">
            <div class="mkt-card mkt-col-4">
                <div>
                    <div class="mkt-card__head"><h2 class="dp-headline-md">Trading &amp; Port Calendar</h2><el-icon :size="18" class="mkt-icon--primary"><Ship /></el-icon></div>
                    <div class="mkt-cal-list">
                        <div v-for="ev in calendarItems" :key="ev.title" class="mkt-cal-item">
                            <div class="mkt-cal-date"><div class="dp-caption mkt-muted">{{ ev.when }}</div><div class="dp-body-md mkt-strong" :class="`mkt-icon--${ev.tone}`">{{ ev.time }}</div></div>
                            <div><div class="dp-body-md mkt-strong">{{ ev.title }}</div><div class="dp-caption mkt-muted">{{ ev.note }}</div></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="mkt-btn mkt-btn--muted mkt-btn--full" @click="placeholderAction('Complete Operations Schedule')">Complete Operations Schedule</button>
            </div>

            <div class="mkt-card mkt-col-8">
                <div class="mkt-card__head">
                    <div>
                        <div class="mkt-title-row"><el-icon :size="18" class="mkt-icon--primary"><CircleCheck /></el-icon><h2 class="dp-headline-md">Pre-Execution Buyer Due Diligence Checklist</h2></div>
                        <p class="dp-caption mkt-muted">Systematic institutional verification before committing capital to escrow</p>
                    </div>
                    <span class="mkt-tag-mini mkt-tag-mini--primary">6/7 Passed · High Confidence</span>
                </div>
                <div class="mkt-checklist-grid">
                    <div v-for="item in checklistItems" :key="item.title" class="mkt-checklist-item">
                        <el-icon :size="18" :class="`mkt-icon--${item.tone}`"><component :is="item.icon" /></el-icon>
                        <div><div class="dp-body-md mkt-strong">{{ item.title }}</div><div class="dp-caption mkt-muted">{{ item.note }}</div></div>
                    </div>
                </div>
            </div>
        </section>

    </DesignPreviewLayout>
</template>

<style scoped>
.mkt-card {
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
.mkt-muted { color: var(--dp-on-surface-variant); }
.mkt-strong { color: var(--dp-on-surface); font-weight: 700; }
.mkt-on { color: var(--dp-on-surface); }
.mkt-mb-sm { margin-bottom: 8px; }
.mkt-mt-xs { margin-top: 4px; }
.mkt-flex-icon { display: inline-flex; align-items: center; gap: 8px; }

.mkt-icon--primary { color: var(--dp-primary); }
.mkt-icon--secondary { color: var(--dp-secondary); }
.mkt-icon--tertiary { color: #923357; }
.mkt-icon--error { color: var(--dp-error); }
.mkt-icon--neutral { color: var(--dp-on-surface-variant); }

.mkt-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--dp-primary); flex-shrink: 0; }
.mkt-dot--secondary { background: var(--dp-secondary); }
.mkt-dot--neutral { background: var(--dp-on-surface-variant); }

.mkt-hero { border: none; border-bottom: 1px solid var(--dp-outline-variant); margin-top: -24px; }
.mkt-hero__top { display: flex; flex-direction: column; gap: 16px; }
@media (min-width: 1024px) { .mkt-hero__top { flex-direction: row; align-items: center; justify-content: space-between; } }
.mkt-title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.mkt-live-badge { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); font-size: 11px; font-weight: 700; }
.mkt-hero__actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.mkt-status-line { display: flex; flex-direction: column; gap: 8px; padding: 10px 14px; background: var(--dp-surface-container-low); border-radius: 8px; font-size: 12px; }
@media (min-width: 1024px) { .mkt-status-line { flex-direction: row; align-items: center; justify-content: space-between; } }
.mkt-status-line__left { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.mkt-status-line__right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

.mkt-tag-mini { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 3px 9px; border-radius: 6px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); white-space: nowrap; }
.mkt-tag-mini--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.mkt-tag-mini--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.mkt-tag-mini--tertiary { background: var(--dp-tertiary-fixed); color: var(--dp-on-tertiary-fixed); }
.mkt-tag-mini--error { background: var(--dp-error-container); color: var(--dp-on-error-container); }
.mkt-tag-mini--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.mkt-icon-btn { width: 30px; height: 30px; border-radius: 8px; border: none; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s ease; flex-shrink: 0; }
.mkt-icon-btn:hover { background: var(--dp-surface-dim); }

.mkt-kpi-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; }
@media (min-width: 1024px) { .mkt-kpi-row { grid-template-columns: repeat(4, 1fr); } }
.mkt-kpi { display: flex; flex-direction: column; gap: 6px; padding: 14px; background: var(--dp-surface-container-low); border-radius: 8px; }
.mkt-kpi__head { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.mkt-kpi__value { display: flex; align-items: baseline; gap: 6px; }

.mkt-card__head { display: flex; flex-direction: column; gap: 10px; }
@media (min-width: 640px) { .mkt-card__head { flex-direction: row; align-items: flex-start; justify-content: space-between; } }

.mkt-decision-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media (min-width: 1024px) { .mkt-decision-grid { grid-template-columns: repeat(5, 1fr); } }
.mkt-decision { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; }
.mkt-decision__foot { display: flex; align-items: center; justify-content: space-between; gap: 6px; background: var(--dp-surface-container-lowest); padding: 6px 8px; border-radius: 6px; }

.mkt-factors-row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); }
.mkt-factor-chip { display: inline-flex; gap: 4px; padding: 4px 10px; border-radius: 6px; background: var(--dp-surface-container-low); font-size: 11px; color: var(--dp-on-surface); }

.mkt-grid-12 { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 1200px) {
    .mkt-grid-12 { grid-template-columns: repeat(12, 1fr); }
    .mkt-col-4 { grid-column: span 4; }
    .mkt-col-6 { grid-column: span 6; }
    .mkt-col-8 { grid-column: span 8; }
}

.mkt-toggle-group { display: flex; align-items: center; gap: 2px; background: var(--dp-surface-container-low); padding: 4px; border-radius: 8px; font-size: 12px; flex-shrink: 0; flex-wrap: wrap; }
.mkt-toggle-group--sm { font-size: 11px; }
.mkt-toggle { padding: 5px 10px; border-radius: 6px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.mkt-toggle--active { background: var(--dp-surface-container-lowest); color: var(--dp-primary); }

.mkt-table-wrap { overflow-x: auto; }
.mkt-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; min-width: 560px; }
.mkt-table thead tr { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; font-weight: 700; }
.mkt-table th { padding: 10px 12px; }
.mkt-table th:first-child { border-radius: 6px 0 0 6px; }
.mkt-table th:last-child { border-radius: 0 6px 6px 0; }
.mkt-table tbody tr { border-bottom: 1px solid var(--dp-outline-variant); transition: background 0.15s ease; }
.mkt-table tbody tr:last-child { border-bottom: none; }
.mkt-table tbody tr:hover { background: var(--dp-surface-container-low); }
.mkt-table td { padding: 12px; vertical-align: middle; }
.mkt-right { text-align: right; }
.mkt-center { text-align: center; padding: 24px 12px; }
.mkt-cb-col { width: 30px; }
.mkt-checkbox { accent-color: var(--dp-primary); width: 14px; height: 14px; }

.mkt-pagination { padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); }
.mkt-pagination :deep(.el-pagination) { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; width: 100%; font-family: var(--dp-font-sans); }
.mkt-pagination :deep(.el-pagination__total) { margin-right: auto; font-size: 12px; font-weight: 600; color: var(--dp-on-surface-variant); }
.mkt-pagination :deep(.btn-prev),
.mkt-pagination :deep(.btn-next) { width: 28px; height: 28px; border-radius: 6px; background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface-variant); }
.mkt-pagination :deep(.btn-prev:disabled),
.mkt-pagination :deep(.btn-next:disabled) { opacity: 0.4; }
.mkt-pagination :deep(.el-pager) { display: flex; align-items: center; gap: 4px; }
.mkt-pagination :deep(.el-pager li) { min-width: 28px; height: 28px; border-radius: 6px; background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface); font-size: 12px; font-weight: 700; }
.mkt-pagination :deep(.el-pager li.is-active) { background: var(--dp-primary); border-color: var(--dp-primary); color: var(--dp-on-primary); }

.mkt-footline { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); font-size: 12px; }
.mkt-link { font-weight: 700; color: var(--dp-primary); text-decoration: none; background: none; border: none; cursor: pointer; font-size: 12px; font-family: var(--dp-font-sans); }
.mkt-link:hover { text-decoration: underline; }

.mkt-chart-box { background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; }
.mkt-series-row { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
.mkt-chart__svg { width: 100%; height: 130px; overflow: visible; }
.mkt-chart-foot { display: flex; justify-content: space-between; font-size: 10px; color: var(--dp-on-surface-variant); margin-top: 6px; }
.mkt-note-box { background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; gap: 4px; }
.mkt-note-box__row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

.mkt-origin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media (min-width: 640px) { .mkt-origin-grid { grid-template-columns: repeat(4, 1fr); } }
.mkt-origin { background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; gap: 4px; }
.mkt-origin__head { display: flex; align-items: center; justify-content: space-between; }

.mkt-balance-list { display: flex; flex-direction: column; gap: 14px; margin-top: 8px; }
.mkt-balance__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.mkt-bar { width: 100%; height: 8px; border-radius: 999px; background: var(--dp-surface-container-low); overflow: hidden; }
.mkt-bar__fill { height: 100%; border-radius: 999px; }
.mkt-bar__fill--primary { background: var(--dp-primary); }
.mkt-bar__fill--secondary { background: var(--dp-secondary); }

.mkt-opps-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 768px) { .mkt-opps-grid { grid-template-columns: repeat(3, 1fr); } }
.mkt-opp-card { background: var(--dp-surface-container-low); border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; }
.mkt-opp-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; }

.mkt-actions-inline { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.mkt-actions-inline--end { justify-content: flex-end; }

.mkt-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 14px; border-radius: 8px; border: none;
    font-size: 12px; font-weight: 700; cursor: pointer; transition: background 0.15s ease, color 0.15s ease; font-family: var(--dp-font-sans); white-space: nowrap;
}
.mkt-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.mkt-btn--primary:hover { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.mkt-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.mkt-btn--muted:hover { background: var(--dp-surface-dim); }
.mkt-btn--sm { padding: 6px 10px; }
.mkt-btn--full { width: 100%; }

.mkt-compare-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 768px) { .mkt-compare-grid { grid-template-columns: repeat(3, 1fr); } }
.mkt-compare-card { background: var(--dp-surface-container-low); border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; }
.mkt-compare-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.mkt-compare-rows { display: flex; flex-direction: column; gap: 4px; margin-top: 12px; }
.mkt-compare-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; background: var(--dp-surface-container-lowest); padding: 6px 10px; border-radius: 6px; font-size: 12px; }

.mkt-select { width: 100%; max-width: 220px; }
.mkt-select :deep(.el-select__wrapper) { height: 30px !important; min-height: 30px !important; }
.mkt-select :deep(.el-select__selected-item), .mkt-select :deep(.el-select__placeholder) { font-size: 12px !important; }

.mkt-cost-rows { display: flex; flex-direction: column; gap: 8px; margin-top: 4px; }
.mkt-cost-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--dp-surface-container-low); padding: 10px 12px; border-radius: 8px; }
.mkt-total-banner { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); padding: 14px; border-radius: 8px; }
.mkt-total-banner__label { text-transform: uppercase; font-weight: 700; letter-spacing: 0.03em; }
.mkt-total-banner__sub { opacity: 0.8; }

.mkt-logistics-list { display: flex; flex-direction: column; gap: 10px; margin-top: 4px; }
.mkt-logistics-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--dp-surface-container-low); padding: 12px; border-radius: 8px; }

.mkt-weather-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 640px) { .mkt-weather-grid { grid-template-columns: 1fr 1fr; } }
.mkt-weather-card { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; display: flex; flex-direction: column; gap: 6px; }
.mkt-weather-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

.mkt-wire-list { display: flex; flex-direction: column; gap: 12px; margin-top: 4px; }
.mkt-wire-item { background: var(--dp-surface-container-low); padding: 12px; border-radius: 8px; display: flex; flex-direction: column; gap: 4px; }
.mkt-wire-item__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

.mkt-cal-list { display: flex; flex-direction: column; gap: 10px; margin-top: 4px; }
.mkt-cal-item { display: flex; align-items: flex-start; gap: 10px; background: var(--dp-surface-container-low); padding: 10px; border-radius: 8px; }
.mkt-cal-date { background: var(--dp-surface-container-lowest); border-radius: 6px; padding: 6px 10px; text-align: center; flex-shrink: 0; min-width: 56px; }

.mkt-checklist-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
@media (min-width: 640px) { .mkt-checklist-grid { grid-template-columns: 1fr 1fr; } }
.mkt-checklist-item { display: flex; align-items: flex-start; gap: 10px; background: var(--dp-surface-container-low); padding: 12px; border-radius: 8px; }
</style>
