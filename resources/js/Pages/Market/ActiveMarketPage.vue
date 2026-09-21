<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import {
    ShoppingCart, Sell, DocumentAdd, Files, Trophy, Connection, Refresh,
    Setting, Position, Coin, Odometer, DataAnalysis, Box, CircleCheck,
    TrendCharts, Right, Lightning, Warning, Ship, Sunny, Cloudy,
    Document, Calendar as CalendarIcon, MagicStick, Promotion, Search,
    WarningFilled, ArrowRight,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    lots: { type: Array, default: () => [] },
});

/* ── Dummy market content — illustrative only ───────────────────────── */
const quickTrades = [
    { icon: ShoppingCart, label: 'Buy Coffee', tone: 'primary' },
    { icon: Sell, label: 'Sell Coffee', tone: 'secondary' },
    { icon: DocumentAdd, label: 'Create RFQ', tone: 'muted' },
    { icon: Files, label: 'Make Offer', tone: 'muted' },
    { icon: Trophy, label: 'Start Auction', tone: 'muted' },
    { icon: Connection, label: 'View My Trades', tone: 'muted' },
];

const kpis = [
    { icon: Position, tone: 'primary', label: 'Global Index', value: '$4.82', unit: '/ kg', tag: '+2.4% today', tagTone: 'primary', note: '24h Vol: 18.4k MT' },
    { icon: Coin, tone: 'secondary', label: 'Robusta Benchmark', value: '$4.15', unit: '/ kg', tag: '+1.8% (+$0.08)', tagTone: 'primary', note: 'Screen 18/G2' },
    { icon: Odometer, tone: 'primary', label: 'Arabica Benchmark', value: '$5.62', unit: '/ kg', tag: '+2.9% (+$0.16)', tagTone: 'primary', note: 'Washed AA / NY2' },
    { icon: DataAnalysis, tone: 'tertiary', label: 'Exchange Volume', value: '18,420', unit: 'MT', tag: '+6.2% vs avg', tagTone: 'primary', note: '307 Contracts' },
    { icon: Box, tone: 'primary', label: 'Active Listed Lots', value: '1,284', unit: 'Lots', tag: '+94 newly listed', tagTone: 'primary', note: '98.2% EUDR OK' },
];

const filterOptions = {
    types: ['All Types', 'Robusta', 'Arabica', 'Liberica'],
    origins: ['All Origins', 'Uganda (East Africa)', 'Brazil (Cerrado/Santos)', 'Vietnam (Dak Lak)', 'Ethiopia (Sidama/Yirgacheffe)', 'Colombia (Huila/Antioquia)'],
    grades: ['All Grades', 'Screen 18 Fine Robusta', 'Bugisu AA Washed', 'Vietnam Gr. 2 (5% Def)', 'Rwenzori Drugar Natural'],
    terms: ['All Delivery Terms', 'FOB Mombasa', 'CIF Jebel Ali / Dubai', 'CIF Hamburg', 'FOB Ho Chi Minh', 'FOB Santos'],
    tiers: ['EUDR Verified & Tier-1', 'EUDR Deforestation-Free', 'Rainforest Alliance', 'Fair Trade Certified', 'Organic USDA / EU'],
};
const filterType = ref(filterOptions.types[1]);
const filterOrigin = ref(filterOptions.origins[1]);
const filterGrade = ref(filterOptions.grades[0]);
const filterTerm = ref(filterOptions.terms[0]);
const filterTier = ref(filterOptions.tiers[0]);

const priceBoardTabs = ['Global', 'Arabica', 'Robusta', 'Uganda', 'Africa'];
const priceBoardTab = ref('Global');

const spotPrices = [
    { name: 'Uganda Robusta', origin: 'Lake Victoria Basin · Washed/Dry', spec: 'Screen 18', price: '$4.15', change: '+$0.08 (+1.9%)', changeTone: 'primary', vol: '420 MT', cta: 'Trade', ctaTone: 'primary' },
    { name: 'Bugisu Arabica AA', origin: 'Mt. Elgon · High Grown (1,900m)', spec: 'AA Fully Washed', price: '$5.10', change: '+$0.12 (+2.4%)', changeTone: 'primary', vol: '180 MT', cta: 'Trade', ctaTone: 'primary' },
    { name: 'Vietnam Robusta Gr. 2', origin: 'Dak Lak Highlands · 5% Black/Broken', spec: 'Wet Polished', price: '$3.85', change: '-$0.02 (-0.5%)', changeTone: 'error', vol: '950 MT', cta: 'View', ctaTone: 'muted' },
    { name: 'Brazil Santos NY 2/3', origin: 'Minas Gerais · Scr 17/18', spec: 'Strictly Soft', price: '$4.60', change: '+$0.05 (+1.1%)', changeTone: 'primary', vol: '1,400 MT', cta: 'Trade', ctaTone: 'primary' },
    { name: 'Ethiopia Yirgacheffe G1', origin: 'Gedeo Zone · Heirloom Floral', spec: 'G1 Washed Special', price: '$6.20', change: '+$0.18 (+3.0%)', changeTone: 'primary', vol: '90 MT', cta: 'Trade', ctaTone: 'primary' },
    { name: 'Rwenzori Drugar', origin: 'Kasese Foothills · Dry Natural', spec: 'Natural Scr 15+', price: '$4.65', change: '+$0.07 (+1.5%)', changeTone: 'primary', vol: '150 MT', cta: 'Trade', ctaTone: 'primary' },
];

const chartRanges = ['1D', '1W', '1M', '3M', '1Y'];
const chartRange = ref('1M');
const chartSeries = [
    { label: 'Uganda Robusta', tone: 'primary' },
    { label: 'Bugisu AA', tone: 'secondary' },
    { label: 'Vietnam Gr. 2', tone: 'neutral' },
];

const opportunityCards = [
    { icon: Lightning, tone: 'primary', tag: 'Price Arbitrage', tagTone: 'primary', stat: '+4.2% Delta', statTone: 'primary', title: 'Uganda Robusta Scr 18 Rally', body: 'Export pricing on FOB Mombasa is pacing +4.2% week-on-week while inland Mukono farmgate warehouse stock remains $0.15/kg below benchmark.', meta: 'Est. Net Margin: $18,000 / Lot', cta: 'Explore Lots', ctaTone: 'primary' },
    { icon: Warning, tone: 'secondary', tag: 'Supply Alert', tagTone: 'secondary', stat: 'Tightening 14%', statTone: 'error', title: 'Vietnam Central Highlands Drought', body: 'Dak Lak reservoir stress is limiting prompt G2 loadings. European roasters are shifting Q3 forward cover to African high-density Screen 18.', meta: 'Substitute demand: +28%', cta: 'View Market', ctaTone: 'muted' },
    { icon: Trophy, tone: 'tertiary', tag: 'Buyer RFQ Tender', tagTone: 'tertiary', stat: 'Expires in 6h', statTone: 'primary', title: 'Dubai Specialty Roaster: 40 MT Arabica', body: 'Tier-1 roaster in Al Quoz, UAE seeking 2× 20ft FCL Bugisu AA Washed (Score 86+) at $5.15/kg CIF Jebel Ali. Fast-track Letter of Credit ready.', meta: 'Buyer Rating: 4.9 ★ (Verified)', cta: 'Submit Quote', ctaTone: 'primary' },
];

const floorLots = [
    { id: 'LOT-UG-8821', grade: 'Fine Robusta Scr 18', cooperative: 'Mukono Smallholders Union', origin: 'Central Uganda · 2024 Crop', volume: '120 MT', volumeNote: '(6 FCL)', cqi: 'CQI 84.5', defects: 'Defects: 3 / 300g', compliance: 'EUDR Polygon', complianceTone: 'primary', price: '$4.20', term: 'FOB Mombasa', cta: 'Buy Now', ctaTone: 'primary' },
    { id: 'LOT-UG-9042', grade: 'Bugisu Arabica AA Washed', cooperative: 'Sipi Falls Cooperative', origin: 'Mt. Elgon, Mbale · High Grown', volume: '45 MT', volumeNote: '(2.25 FCL)', cqi: 'CQI 86.5', defects: 'Defects: 1 / 300g (Specialty)', compliance: 'Rainforest Cert', complianceTone: 'primary', price: '$5.10', term: 'FOB Mombasa', cta: 'Buy Now', ctaTone: 'primary' },
    { id: 'LOT-RW-3021', grade: 'Rwenzori Natural Drugar', cooperative: 'Kasese Mountain Farmers Union', origin: 'Western Highlands · Sun Dried', volume: '60 MT', volumeNote: '(3 FCL)', cqi: 'CQI 85.0', defects: 'Heavy Berry Sweetness', compliance: 'Organic EU / FairTrade', complianceTone: 'secondary', price: '$4.65', term: 'FOB Mombasa', cta: 'Make Offer', ctaTone: 'muted' },
    { id: 'LOT-VN-1104', grade: 'Dak Lak Robusta Gr. 1', cooperative: 'Saigon Central Processing Mill', origin: 'Buon Ma Thuot · Polished', volume: '200 MT', volumeNote: '(10 FCL)', cqi: 'CQI 82.0', defects: 'Screen 16/18 · 2% Def', compliance: 'UCDA/4C Approved', complianceTone: 'primary', price: '$3.85', term: 'FOB Ho Chi Minh', cta: 'Buy Now', ctaTone: 'primary' },
];

const spreadRows = [
    { grade: 'Uganda Robusta (Screen 18)', margin: '+48.2% Gross Margin', farmgate: 60, transit: 14, spread: 26, farmgateLabel: 'Farmgate: UGX 10,400 ($2.80)', transitLabel: 'Freight/Milling: $0.40', fobLabel: 'FOB Mombasa: $4.15' },
    { grade: 'Bugisu Arabica AA (Mt. Elgon)', margin: '+41.7% Gross Margin', farmgate: 62, transit: 12, spread: 26, farmgateLabel: 'Farmgate: UGX 13,300 ($3.60)', transitLabel: 'Processing: $0.45', fobLabel: 'FOB Mombasa: $5.10' },
    { grade: 'Rwenzori Natural Drugar', margin: '+37.5% Gross Margin', farmgate: 66, transit: 10, spread: 24, farmgateLabel: 'Farmgate: UGX 12,500 ($3.38)', transitLabel: 'Logistics: $0.32', fobLabel: 'FOB Mombasa: $4.65' },
];

const originMatrix = [
    { flag: '🇺🇬', country: 'Uganda', phase: 'Peak Fly Crop', phaseTone: 'primary', price: '$4.15', priceTone: 'primary', vol: '+14.2%', volTone: 'primary' },
    { flag: '🇧🇷', country: 'Brazil', phase: 'Post-Harvest Export', phaseTone: 'neutral', price: '$4.60', priceTone: 'on', vol: '-3.1%', volTone: 'error' },
    { flag: '🇻🇳', country: 'Vietnam', phase: 'Off-Season / Storage', phaseTone: 'secondary', price: '$3.85', priceTone: 'on', vol: '-8.4%', volTone: 'error' },
    { flag: '🇪🇹', country: 'Ethiopia', phase: 'Main Crop Active', phaseTone: 'primary', price: '$6.20', priceTone: 'primary', vol: '+9.8%', volTone: 'primary' },
    { flag: '🇨🇴', country: 'Colombia', phase: 'Mitaca Harvest', phaseTone: 'neutral', price: '$5.40', priceTone: 'on', vol: '+2.1%', volTone: 'primary' },
];

const ports = [
    { name: 'Port of Mombasa (KE)', status: 'Optimal', statusTone: 'primary', note: 'East African Primary Gate (UG Lots)', stats: [['Container Dwell', '2.4 days'], ['Vessel Queue', '4 vessels']], foot: 'Avg Uganda CFS transit: 4.5 days' },
    { name: 'Dar es Salaam (TZ)', status: 'Optimal', statusTone: 'primary', note: 'Secondary South Corridor', stats: [['Container Dwell', '1.1 days'], ['Vessel Queue', '2 vessels']], foot: 'South Corridor transit: 6.2 days' },
    { name: 'Jebel Ali / Dubai (UAE)', status: 'Normal', statusTone: 'neutral', note: 'Gulf Re-export & Roaster Hub', stats: [['Container Dwell', '2.0 days'], ['Sea Route from MBA', '18 – 24 days']], foot: 'Freezone storage capacity: 88%' },
    { name: 'Hamburg / Rotterdam (EU)', status: 'Congested', statusTone: 'error', note: 'Northern Europe Discharging', stats: [['Container Dwell', '4.8 days'], ['Sea Route from MBA', '28 – 35 days']], foot: 'EUDR Green Lane inspection: 100% Active' },
];

const weather = [
    { icon: Sunny, tone: 'primary', name: 'Uganda: Mukono / Mbale', temp: '24°C', sub: '12mm precip', rows: [['Drying Patio Risk', 'Low (Optimal)'], ['Solar Radiation', 'High (6.8 kWh/m²)']], note: '✓ Ideal sun-drying conditions for naturals', noteTone: 'primary' },
    { icon: Cloudy, tone: 'secondary', name: 'Brazil: Cerrado Mineiro', temp: '28°C', sub: '0mm precip (Dry)', rows: [['Soil Moisture', 'Low (28%)'], ['Drought Index', 'Moderate Stress']], note: '! Flowering trigger watching in progress', noteTone: 'secondary' },
    { icon: Warning, tone: 'error', name: 'Vietnam: Dak Lak / Gia Lai', temp: '31°C', sub: 'Heatwave Alert', rows: [['Irrigation Reservoir', 'Critical 42%'], ['Defoliation Risk', 'Elevated']], note: '! Bean sizing reduction expected for 24/25', noteTone: 'error' },
    { icon: Cloudy, tone: 'primary', name: 'Ethiopia: Sidama / Yirgacheffe', temp: '21°C', sub: 'Mild showers (8mm)', rows: [['Washing Station Water', 'Abundant'], ['Cherries Maturity', 'Even ripening']], note: '✓ Grade 1 preparation fully on schedule', noteTone: 'primary' },
];

const tradeNews = [
    { tag: 'Regulation', tagTone: 'primary', time: '32 mins ago', title: 'European Commission publishes streamlined API for EUDR geolocation validation at Hamburg port' },
    { tag: 'Logistics', tagTone: 'secondary', time: '2h ago', title: 'Kenya Ports Authority introduces express rail shuttle from Kampala CFS directly to Mombasa Berth 21' },
    { tag: 'Pricing', tagTone: 'tertiary', time: '4h ago', title: 'Uganda Coffee Development Authority (UCDA) raises indicative minimum farmgate floor to UGX 10,200' },
];

const scheduleItems = [
    { when: 'TODAY', time: '14:00', tone: 'primary', title: 'UCDA Spot Desk Floor Auction', note: '320 MT Fine Robusta & Arabica lots' },
    { when: 'TOMORROW', time: '09:30', tone: 'secondary', title: 'Dubai Roaster RFQ Deadline', note: 'Tender for 6× 20ft Containers CIF Jebel Ali' },
    { when: 'OCT 28', time: '18:00', tone: 'tertiary', title: 'CMA CGM Mombasa Feeder Cutoff', note: 'Direct service to Tangier / Rotterdam hub' },
];

const deskStats = [
    { label: 'Active Listings', value: '3 Lots', note: '180 MT Live', tone: 'primary' },
    { label: 'Open Bids / Offers', value: '4 Offers', note: '2 Under review', tone: 'secondary' },
    { label: 'RFQs Submitted', value: '2 RFQs', note: '1 Counter received', tone: 'neutral' },
    { label: 'Executed (MTD)', value: '$482,000', note: '100% Fulfilled', tone: 'primary' },
];

const aiPrompts = [
    { icon: Search, tone: 'primary', label: 'Compare UG vs VN Prices', query: 'Compare Uganda vs Vietnam prices for Q3 delivery' },
    { icon: WarningFilled, tone: 'secondary', label: 'Find EUDR Lots', query: 'Find verified EUDR lots under $4.20/kg FOB Mombasa' },
    { icon: Coin, tone: 'primary', label: 'Estimate Freight Rate', query: 'Estimate freight rate Mombasa to Jebel Ali for 5x 20ft containers' },
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
    <MainLayout title="Active Market">
        <Head title="Active Market" />

        <!-- 1. QUICK TRADE ACTION RIBBON -->
        <section class="lm-card lm-hero">
            <div class="lm-ribbon">
                <div class="lm-ribbon__actions">
                    <button v-for="action in quickTrades" :key="action.label" type="button" class="lm-btn" :class="`lm-btn--${action.tone}`" @click="placeholderAction(action.label)">
                        <el-icon :size="15"><component :is="action.icon" /></el-icon>
                        <span>{{ action.label }}</span>
                    </button>
                </div>
            </div>

            <div class="lm-status-line">
                <div class="lm-status-line__left">
                    <span class="lm-flex-icon lm-strong lm-icon--primary"><span class="lm-dot"></span> Market Live</span>
                    <span class="lm-status-line__sep">/</span>
                    <span class="lm-flex-icon lm-muted"><el-icon :size="14"><CalendarIcon /></el-icon> Updated: 2 mins ago</span>
                    <span class="lm-status-line__sep">/</span>
                    <span class="lm-muted">Market Coverage: <strong class="lm-on">East Africa · LatAm · SE Asia Corridors</strong></span>
                    <span class="lm-status-line__sep">/</span>
                    <span class="lm-muted">Standard Quotation: <strong class="lm-on dp-mono">USD / metric kg</strong> (Incoterms 2020)</span>
                </div>
                <div class="lm-status-line__right">
                    <span class="dp-mono lm-muted">ICE Coffee 'C': 228.45c/lb (+1.4%)</span>
                    <button type="button" class="lm-link" @click="placeholderAction('Market Settings')"><el-icon :size="14"><Setting /></el-icon> Market Settings</button>
                </div>
            </div>
        </section>

        <!-- 2. MARKET KPI ROW -->
        <section class="lm-card">
            <div class="lm-kpi-row">
                <div v-for="kpi in kpis" :key="kpi.label" class="lm-kpi">
                    <div class="lm-kpi__head">
                        <span class="dp-label-md lm-muted">{{ kpi.label }}</span>
                        <el-icon :size="16" :class="`lm-icon--${kpi.tone}`"><component :is="kpi.icon" /></el-icon>
                    </div>
                    <div class="dp-display-md lm-kpi__value">{{ kpi.value }} <span class="dp-body-md lm-muted">{{ kpi.unit }}</span></div>
                    <div class="lm-kpi__foot">
                        <span class="lm-tag-mini" :class="`lm-tag-mini--${kpi.tagTone}`">{{ kpi.tag }}</span>
                        <span class="dp-caption lm-muted dp-mono">{{ kpi.note }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. PERSISTENT MARKET FILTER BAR -->
        <section class="lm-card">
            <div class="lm-filter-row">
                <div class="lm-filter-field">
                    <label class="lm-filter-label">Coffee Type</label>
                    <el-select v-model="filterType" class="lm-select">
                        <el-option v-for="opt in filterOptions.types" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                </div>
                <div class="lm-filter-field">
                    <label class="lm-filter-label">Origin Country</label>
                    <el-select v-model="filterOrigin" class="lm-select">
                        <el-option v-for="opt in filterOptions.origins" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                </div>
                <div class="lm-filter-field">
                    <label class="lm-filter-label">Grade / Quality</label>
                    <el-select v-model="filterGrade" class="lm-select">
                        <el-option v-for="opt in filterOptions.grades" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                </div>
                <div class="lm-filter-field">
                    <label class="lm-filter-label">Delivery / Term</label>
                    <el-select v-model="filterTerm" class="lm-select">
                        <el-option v-for="opt in filterOptions.terms" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                </div>
                <div class="lm-filter-field">
                    <label class="lm-filter-label">Compliance &amp; Tier</label>
                    <el-select v-model="filterTier" class="lm-select">
                        <el-option v-for="opt in filterOptions.tiers" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                </div>
                <div class="lm-filter-field lm-filter-field--actions">
                    <label class="lm-filter-label">&nbsp;</label>
                    <div class="lm-filter-actions">
                        <button type="button" class="lm-btn lm-btn--primary lm-btn--grow" @click="placeholderAction('Apply Filters')">Apply Filters</button>
                        <button type="button" class="lm-icon-btn" title="Reset Filters" @click="placeholderAction('Reset Filters')"><el-icon :size="15"><Refresh /></el-icon></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. SPOT PRICE BOARD + TREND CHART -->
        <section class="lm-grid-12">
            <div class="lm-card lm-col-7">
                <div class="lm-card__head">
                    <div>
                        <h2 class="dp-headline-md">Institutional Spot Price Board</h2>
                        <p class="dp-caption lm-muted">Validated FOB origin prices based on bilateral settles &amp; terminal bids</p>
                    </div>
                    <div class="lm-toggle-group">
                        <button v-for="tab in priceBoardTabs" :key="tab" type="button" class="lm-toggle" :class="{ 'lm-toggle--active': priceBoardTab === tab }" @click="priceBoardTab = tab">{{ tab }}</button>
                    </div>
                </div>
                <div class="lm-table-wrap">
                    <table class="lm-table">
                        <thead>
                            <tr><th>Coffee / Origin</th><th>Grade Spec</th><th class="lm-right">Spot (USD/kg)</th><th class="lm-right">Net Change</th><th class="lm-right">24h Vol</th><th class="lm-right">Action</th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in spotPrices" :key="row.name">
                                <td><div class="lm-strong">{{ row.name }}</div><div class="dp-caption lm-muted">{{ row.origin }}</div></td>
                                <td><span class="lm-tag-mini">{{ row.spec }}</span></td>
                                <td class="lm-right lm-strong dp-mono">{{ row.price }}</td>
                                <td class="lm-right dp-mono lm-strong" :class="`lm-icon--${row.changeTone}`">{{ row.change }}</td>
                                <td class="lm-right dp-mono lm-muted">{{ row.vol }}</td>
                                <td class="lm-right"><button type="button" class="lm-btn lm-btn--sm" :class="`lm-btn--${row.ctaTone}`" @click="placeholderAction(`${row.cta} ${row.name}`)">{{ row.cta }}</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lm-card lm-col-5">
                <div>
                    <div class="lm-card__head">
                        <div>
                            <h2 class="dp-headline-md">Price Trend &amp; Spread Arbitrage</h2>
                            <span class="dp-caption lm-muted dp-mono">30-Day Normalized Spot Corridors (USD/kg)</span>
                        </div>
                        <div class="lm-toggle-group lm-toggle-group--sm">
                            <button v-for="r in chartRanges" :key="r" type="button" class="lm-toggle" :class="{ 'lm-toggle--active': chartRange === r }" @click="chartRange = r">{{ r }}</button>
                        </div>
                    </div>
                    <div class="lm-series-row">
                        <span v-for="s in chartSeries" :key="s.label" class="lm-tag-mini" :class="`lm-tag-mini--${s.tone}`"><span class="lm-dot lm-dot--xs" :class="`lm-dot--${s.tone}`"></span> {{ s.label }}</span>
                    </div>
                    <div class="lm-chart">
                        <svg viewBox="0 0 460 170" class="lm-chart__svg">
                            <line x1="0" x2="460" y1="20" y2="20" stroke="var(--dp-outline-variant)" stroke-dasharray="3,3" stroke-opacity="0.4" />
                            <line x1="0" x2="460" y1="60" y2="60" stroke="var(--dp-outline-variant)" stroke-dasharray="3,3" stroke-opacity="0.4" />
                            <line x1="0" x2="460" y1="100" y2="100" stroke="var(--dp-outline-variant)" stroke-dasharray="3,3" stroke-opacity="0.4" />
                            <path d="M 40 45 Q 100 50, 160 38 T 260 30 T 360 25 T 450 20" fill="none" stroke="var(--dp-secondary)" stroke-width="2.2" stroke-linecap="round" />
                            <path d="M 40 95 Q 110 92, 170 85 T 270 70 T 370 62 T 450 56" fill="none" stroke="var(--dp-primary)" stroke-width="2.5" stroke-linecap="round" />
                            <path d="M 40 105 Q 120 102, 190 104 T 290 109 T 390 112 T 450 114" fill="none" stroke="var(--dp-outline)" stroke-width="1.8" stroke-dasharray="4,2" />
                            <circle cx="450" cy="56" r="4" fill="var(--dp-primary)" />
                            <circle cx="450" cy="56" r="7" fill="var(--dp-primary)" fill-opacity="0.2" />
                            <rect v-for="(bar, i) in [15,20,28,35,24,32,38,42,36,45,50]" :key="i" :x="50 + i * 39" :y="160 - bar" width="12" :height="bar" rx="1" fill="var(--dp-outline-variant)" fill-opacity="0.5" />
                        </svg>
                    </div>
                </div>
                <div class="lm-footline">
                    <span class="dp-caption lm-muted">Robusta/Arabica Spread: <strong class="lm-on dp-mono">-$0.95/kg</strong> (-18.6%)</span>
                    <span class="dp-caption lm-icon--primary lm-strong">Spread Tightening +3.4% this week</span>
                </div>
            </div>
        </section>

        <!-- 5. ARBITRAGE & OPPORTUNITIES -->
        <section class="lm-card">
            <div class="lm-title-row lm-mb">
                <el-icon :size="18" class="lm-icon--primary"><Lightning /></el-icon>
                <h2 class="dp-headline-md">Automated Trading Arbitrage &amp; Opportunities</h2>
                <span class="lm-tag-mini lm-tag-mini--primary">AI Scan Active</span>
            </div>
            <div class="lm-opps-grid">
                <div v-for="opp in opportunityCards" :key="opp.title" class="lm-opp-card">
                    <div>
                        <div class="lm-opp-card__head">
                            <span class="lm-tag-mini" :class="`lm-tag-mini--${opp.tagTone}`">{{ opp.tag }}</span>
                            <span class="dp-mono lm-strong" :class="`lm-icon--${opp.statTone}`">{{ opp.stat }}</span>
                        </div>
                        <h3 class="dp-body-lg lm-strong">{{ opp.title }}</h3>
                        <p class="dp-caption lm-muted lm-clamp3">{{ opp.body }}</p>
                    </div>
                    <div class="lm-opp-card__foot">
                        <span class="dp-caption lm-muted dp-mono">{{ opp.meta }}</span>
                        <button type="button" class="lm-btn lm-btn--sm" :class="`lm-btn--${opp.ctaTone === 'primary' ? 'primary' : 'muted'}`" @click="placeholderAction(opp.cta)">{{ opp.cta }}</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. AVAILABLE LOTS ON TRADING FLOOR -->
        <section class="lm-card">
            <div class="lm-card__head">
                <div>
                    <h2 class="dp-headline-md">Available Lots on Trading Floor</h2>
                    <p class="dp-caption lm-muted">Direct commercial access to physical, bonded, and ready-to-load coffee inventory</p>
                </div>
                <div class="lm-actions-inline">
                    <span class="dp-caption lm-muted dp-mono">1,284 Verified Lots</span>
                    <button type="button" class="lm-btn lm-btn--muted" @click="placeholderAction('Export Manifest CSV')">Export Manifest CSV</button>
                </div>
            </div>
            <div class="lm-table-wrap">
                <table class="lm-table">
                    <thead>
                        <tr><th>Lot Reference</th><th>Origin &amp; Cooperative</th><th>Volume</th><th>Quality (CQI/Defects)</th><th>Compliance</th><th class="lm-right">Spot Price</th><th class="lm-right">Incoterm</th><th class="lm-right">Direct Trading</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="lot in floorLots" :key="lot.id">
                            <td><div class="lm-strong dp-mono lm-icon--primary">{{ lot.id }}</div><div class="dp-caption lm-muted">{{ lot.grade }}</div></td>
                            <td><div class="lm-strong">{{ lot.cooperative }}</div><div class="dp-caption lm-muted">{{ lot.origin }}</div></td>
                            <td><div class="lm-strong dp-mono">{{ lot.volume }} <span class="dp-caption lm-muted">{{ lot.volumeNote }}</span></div></td>
                            <td><div class="lm-flex-icon lm-strong lm-icon--primary"><el-icon :size="13"><CircleCheck /></el-icon> {{ lot.cqi }}</div><div class="dp-caption lm-muted">{{ lot.defects }}</div></td>
                            <td><span class="lm-tag-mini" :class="`lm-tag-mini--${lot.complianceTone}`"><el-icon :size="11"><CircleCheck /></el-icon> {{ lot.compliance }}</span></td>
                            <td class="lm-right"><span class="lm-strong dp-mono">{{ lot.price }}</span><span class="dp-caption lm-muted">/kg</span></td>
                            <td class="lm-right dp-mono lm-muted">{{ lot.term }}</td>
                            <td class="lm-right">
                                <div class="lm-actions-inline lm-actions-inline--end">
                                    <button type="button" class="lm-btn lm-btn--muted lm-btn--sm" @click="placeholderAction(`Inspect ${lot.id}`)">Inspect</button>
                                    <button type="button" class="lm-btn lm-btn--sm" :class="lot.ctaTone === 'primary' ? 'lm-btn--primary' : 'lm-btn--secondary'" @click="placeholderAction(`${lot.cta} ${lot.id}`)">{{ lot.cta }}</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 7. FARMGATE SPREAD + ORIGIN MATRIX -->
        <section class="lm-grid-12">
            <div class="lm-card lm-col-6">
                <div>
                    <div class="lm-card__head">
                        <div>
                            <h2 class="dp-headline-md">Farmgate vs. Export FOB Spread</h2>
                            <p class="dp-caption lm-muted">Inland collection point parity to port FOB gross margin</p>
                        </div>
                        <button type="button" class="lm-link" @click="placeholderAction('Full Analysis')">Full Analysis</button>
                    </div>
                    <div class="lm-spread-list">
                        <div v-for="row in spreadRows" :key="row.grade" class="lm-spread-row">
                            <div class="lm-spread-row__head"><span class="lm-strong">{{ row.grade }}</span><span class="dp-mono lm-strong lm-icon--primary">{{ row.margin }}</span></div>
                            <div class="lm-spread-bar">
                                <div class="lm-spread-bar__seg lm-spread-bar__seg--secondary" :style="{ width: row.farmgate + '%' }"></div>
                                <div class="lm-spread-bar__seg lm-spread-bar__seg--muted" :style="{ width: row.transit + '%' }"></div>
                                <div class="lm-spread-bar__seg lm-spread-bar__seg--primary" :style="{ width: row.spread + '%' }"></div>
                            </div>
                            <div class="lm-spread-row__foot dp-mono">
                                <span>{{ row.farmgateLabel }}</span><span>{{ row.transitLabel }}</span><span class="lm-strong">{{ row.fobLabel }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lm-legend">
                    <span class="lm-flex-icon"><span class="lm-legend__swatch lm-legend__swatch--secondary"></span> Farmgate Equiv</span>
                    <span class="lm-flex-icon"><span class="lm-legend__swatch lm-legend__swatch--muted"></span> Processing/Transit</span>
                    <span class="lm-flex-icon"><span class="lm-legend__swatch lm-legend__swatch--primary"></span> Export Spread</span>
                </div>
            </div>

            <div class="lm-card lm-col-6">
                <div class="lm-card__head">
                    <div>
                        <h2 class="dp-headline-md">Origin Harvest &amp; Movement Matrix</h2>
                        <p class="dp-caption lm-muted">Active macro harvest cycles and export velocity</p>
                    </div>
                    <span class="lm-tag-mini dp-mono">5 Tracked Origins</span>
                </div>
                <div class="lm-table-wrap">
                    <table class="lm-table">
                        <thead>
                            <tr><th>Country</th><th>Harvest Phase</th><th class="lm-right">FOB Price</th><th class="lm-right">MoM Vol</th><th class="lm-right">Action</th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in originMatrix" :key="row.country">
                                <td class="lm-strong lm-flex-icon"><span>{{ row.flag }}</span> {{ row.country }}</td>
                                <td><span class="lm-tag-mini" :class="`lm-tag-mini--${row.phaseTone}`">{{ row.phase }}</span></td>
                                <td class="lm-right dp-mono lm-strong" :class="row.priceTone === 'primary' ? 'lm-icon--primary' : 'lm-on'">{{ row.price }}</td>
                                <td class="lm-right dp-mono lm-strong" :class="`lm-icon--${row.volTone}`">{{ row.vol }}</td>
                                <td class="lm-right"><button type="button" class="lm-link" @click="placeholderAction(`Explore ${row.country}`)">Explore</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- 8. PORT LOGISTICS -->
        <section class="lm-card">
            <div class="lm-card__head">
                <div>
                    <h2 class="dp-headline-md">Port Logistics &amp; Shipping Corridor Intelligence</h2>
                    <p class="dp-caption lm-muted">Live dwell times, vessel congestion queues, and typical sea freight durations</p>
                </div>
                <span class="lm-flex-icon dp-mono lm-icon--primary lm-strong"><el-icon :size="15"><Ship /></el-icon> Direct Mombasa Corridors</span>
            </div>
            <div class="lm-ports-grid">
                <div v-for="port in ports" :key="port.name" class="lm-port-card">
                    <div>
                        <div class="lm-port-card__head"><span class="lm-strong dp-body-md">{{ port.name }}</span><span class="lm-tag-mini" :class="`lm-tag-mini--${port.statusTone}`">{{ port.status }}</span></div>
                        <div class="dp-caption lm-muted lm-mb-sm">{{ port.note }}</div>
                        <div class="lm-port-card__stats dp-mono">
                            <div v-for="stat in port.stats" :key="stat[0]" class="lm-port-card__stat"><span class="lm-muted">{{ stat[0] }}:</span><span class="lm-strong">{{ stat[1] }}</span></div>
                        </div>
                    </div>
                    <div class="lm-port-card__foot dp-mono lm-muted">{{ port.foot }}</div>
                </div>
            </div>
        </section>

        <!-- 9. AGRO-WEATHER FOCUS -->
        <section class="lm-card">
            <div class="lm-card__head">
                <div>
                    <h2 class="dp-headline-md">Agro-Weather &amp; Drying Index Focus</h2>
                    <p class="dp-caption lm-muted">Microclimate satellite readings affecting drying patios and green moisture levels</p>
                </div>
                <span class="dp-caption lm-muted dp-mono">Copernicus / GFS Feeds</span>
            </div>
            <div class="lm-weather-grid">
                <div v-for="w in weather" :key="w.name" class="lm-weather-card">
                    <div class="lm-weather-card__head"><span class="lm-strong dp-body-md">{{ w.name }}</span><el-icon :size="18" :class="`lm-icon--${w.tone}`"><component :is="w.icon" /></el-icon></div>
                    <div class="lm-weather-card__temp"><span class="dp-headline-sm dp-mono">{{ w.temp }}</span><span class="dp-caption lm-muted dp-mono">{{ w.sub }}</span></div>
                    <div class="lm-weather-card__rows">
                        <div v-for="r in w.rows" :key="r[0]" class="dp-caption lm-muted">{{ r[0] }}: <strong :class="`lm-icon--${w.tone}`">{{ r[1] }}</strong></div>
                    </div>
                    <div class="lm-weather-card__note dp-mono" :class="`lm-icon--${w.noteTone}`">{{ w.note }}</div>
                </div>
            </div>
        </section>

        <!-- 10. NEWS / SCHEDULE / DESK ACTIVITY -->
        <section class="lm-grid-12">
            <div class="lm-card lm-col-4">
                <div>
                    <div class="lm-card__head"><h2 class="dp-headline-md">Verified Trade News</h2><span class="dp-caption lm-icon--primary lm-strong">Wire Feeds</span></div>
                    <div class="lm-news-list">
                        <div v-for="n in tradeNews" :key="n.title" class="lm-news-item">
                            <div class="lm-flex-icon"><span class="lm-tag-mini" :class="`lm-tag-mini--${n.tagTone}`">{{ n.tag }}</span><span class="dp-caption lm-muted">{{ n.time }}</span></div>
                            <a href="#" class="lm-news-item__link" @click.prevent="placeholderAction('Read article')">{{ n.title }}</a>
                        </div>
                    </div>
                </div>
                <button type="button" class="lm-btn lm-btn--muted lm-btn--full" @click="placeholderAction('View All Commodity Intelligence')">View All Commodity Intelligence</button>
            </div>

            <div class="lm-card lm-col-4">
                <div>
                    <div class="lm-card__head"><h2 class="dp-headline-md">Market Schedule &amp; Auctions</h2><el-icon :size="18" class="lm-icon--primary"><CalendarIcon /></el-icon></div>
                    <div class="lm-schedule-list">
                        <div v-for="ev in scheduleItems" :key="ev.title" class="lm-schedule-item">
                            <div class="lm-schedule-item__date"><div class="dp-caption" :class="`lm-icon--${ev.tone}`">{{ ev.when }}</div><div class="dp-body-lg lm-strong">{{ ev.time }}</div></div>
                            <div><div class="lm-strong dp-body-md">{{ ev.title }}</div><div class="dp-caption lm-muted">{{ ev.note }}</div></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="lm-btn lm-btn--muted lm-btn--full" @click="placeholderAction('Sync with My Calendar')">Sync with My Calendar</button>
            </div>

            <div class="lm-card lm-col-4">
                <div>
                    <div class="lm-card__head"><h2 class="dp-headline-md">My Desk Activity</h2><span class="lm-tag-mini lm-tag-mini--primary">Moses Kato</span></div>
                    <div class="lm-desk-grid">
                        <div v-for="d in deskStats" :key="d.label" class="lm-desk-stat">
                            <div class="dp-caption lm-muted">{{ d.label }}</div>
                            <div class="dp-headline-sm dp-mono" :class="`lm-icon--${d.tone}`">{{ d.value }}</div>
                            <div class="dp-caption lm-muted">{{ d.note }}</div>
                        </div>
                    </div>
                    <div class="lm-desk-note">
                        <span class="dp-caption">Recent: Counter-offer on LOT-UG-8821 ($4.18/kg)</span>
                        <button type="button" class="lm-link" @click="placeholderAction('Review counter-offer')">Review</button>
                    </div>
                </div>
                <button type="button" class="lm-btn lm-btn--primary lm-btn--full" @click="router.visit(route('store.show'))">Manage My Portfolio</button>
            </div>
        </section>

        <!-- 11. AI MARKET ASSISTANT -->
        <section class="lm-card">
            <div class="lm-ai-head">
                <div class="lm-flex-icon">
                    <div class="lm-ai-icon"><el-icon :size="18"><MagicStick /></el-icon></div>
                    <h2 class="dp-headline-md">Bean Origin Commodity AI Copilot</h2>
                </div>
                <span class="lm-tag-mini dp-mono">Trained on ICE, UCDA, CQI &amp; Global Customs Datasets</span>
            </div>

            <div class="lm-searchbar">
                <div class="lm-searchbar__input">
                    <el-icon :size="18"><Promotion /></el-icon>
                    <input v-model="aiCommand" type="text" placeholder="Ask anything: e.g. 'Compare Uganda Screen 18 vs Vietnam Robusta FOB spread over the last 90 days...'" @keydown.enter="executeAiCommand" />
                </div>
                <el-button class="lm-btn lm-btn--primary" @click="executeAiCommand">Analyze <el-icon :size="14"><ArrowRight /></el-icon></el-button>
            </div>

            <div class="lm-prompt-row">
                <span class="dp-label-md lm-muted">Quick Inquiries:</span>
                <button v-for="prompt in aiPrompts" :key="prompt.label" type="button" class="lm-chip" @click="runAiQuery(prompt.query)">
                    <el-icon :size="14" :class="`lm-icon--${prompt.tone}`"><component :is="prompt.icon" /></el-icon>
                    <span>{{ prompt.label }}</span>
                </button>
            </div>

            <div v-if="aiResponseVisible" class="lm-ai-response">
                <div class="lm-ai-response__head">
                    <span class="lm-flex-icon lm-icon--primary lm-strong"><el-icon :size="16"><MagicStick /></el-icon> Bean Origin AI Response</span>
                    <button type="button" class="lm-icon-close" @click="aiResponseVisible = false"><el-icon :size="16"><Refresh /></el-icon></button>
                </div>
                <p class="dp-body-md lm-on">
                    <strong>AI Analysis for:</strong> "{{ aiResponseQuery }}"<br /><br />
                    Robusta Screen 18 currently holds a +$0.30/kg premium over Vietnam Gr 2 on prompt European delivery due to lower port dwell at Mombasa (2.4d) and immediate EUDR polygon compliance certification. Recommend securing available lots before expected Thursday auction price adjustments.
                </p>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
.lm-card {
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
.lm-muted { color: var(--dp-on-surface-variant); }
.lm-strong { color: var(--dp-on-surface); font-weight: 700; }
.lm-on { color: var(--dp-on-surface); }
.lm-mb { margin-bottom: 4px; }
.lm-mb-sm { margin-bottom: 8px; }
.lm-clamp3 { display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.lm-flex-icon { display: inline-flex; align-items: center; gap: 5px; }

.lm-icon--primary { color: var(--dp-primary); }
.lm-icon--secondary { color: var(--dp-secondary); }
.lm-icon--tertiary { color: #923357; }
.lm-icon--error { color: var(--dp-error); }
.lm-icon--neutral { color: var(--dp-on-surface-variant); }

.lm-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--dp-primary); flex-shrink: 0; }
.lm-dot--xs { width: 5px; height: 5px; }
.lm-dot--secondary { background: var(--dp-secondary); }
.lm-dot--neutral { background: var(--dp-on-surface-variant); }

.lm-hero {
    border: none;
    border-bottom: 1px solid var(--dp-outline-variant);
    margin-top: -48px;
}

.lm-ribbon { display: flex; flex-direction: column; gap: 12px; }
@media (min-width: 1024px) { .lm-ribbon { flex-direction: row; align-items: center; justify-content: space-between; } }
.lm-ribbon__actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.lm-ribbon__status { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.lm-icon-btn { width: 30px; height: 30px; border-radius: 8px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s ease; flex-shrink: 0; }
.lm-icon-btn:hover { background: var(--dp-surface-container-high); }

.lm-status-line { display: flex; flex-direction: column; gap: 8px; padding: 10px 14px; background: var(--dp-surface-container-low); border-radius: 8px; font-size: 12px; }
@media (min-width: 1024px) { .lm-status-line { flex-direction: row; align-items: center; justify-content: space-between; } }
.lm-status-line__left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.lm-status-line__sep { color: var(--dp-outline-variant); }
.lm-status-line__right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

.lm-tag-mini { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 2px 8px; border-radius: 6px; background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); white-space: nowrap; }
.lm-tag-mini--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.lm-tag-mini--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.lm-tag-mini--tertiary { background: var(--dp-tertiary-fixed); color: var(--dp-on-tertiary-fixed); }
.lm-tag-mini--error { background: var(--dp-error-container); color: var(--dp-on-error-container); }
.lm-tag-mini--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.lm-kpi-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; }
@media (min-width: 1024px) { .lm-kpi-row { grid-template-columns: repeat(5, 1fr); } }
.lm-kpi { display: flex; flex-direction: column; gap: 6px; padding-top: 4px; border-top: 2px solid transparent; }
@media (min-width: 1024px) { .lm-kpi { border-top: none; border-left: 1px solid var(--dp-outline-variant); padding-left: 16px; padding-top: 0; } .lm-kpi:first-child { border-left: none; padding-left: 0; } }
.lm-kpi__head { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.lm-kpi__value { display: flex; align-items: baseline; gap: 6px; }
.lm-kpi__foot { display: flex; align-items: center; justify-content: space-between; gap: 6px; }

.lm-filter-row { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 640px) { .lm-filter-row { grid-template-columns: 1fr 1fr; } }
@media (min-width: 1024px) { .lm-filter-row { grid-template-columns: repeat(6, 1fr); } }
.lm-filter-field { display: flex; flex-direction: column; gap: 6px; }
.lm-filter-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--dp-on-surface-variant); }
.lm-filter-actions { display: flex; gap: 6px; }
.lm-filter-field--actions { justify-content: flex-end; }
.lm-select { width: 100%; }
.lm-select :deep(.el-select__wrapper) { height: 32px !important; min-height: 32px !important; line-height: 32px !important; }
.lm-select :deep(.el-select__selected-item), .lm-select :deep(.el-select__placeholder) { font-size: 12px !important; }

.lm-grid-12 { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 1200px) {
    .lm-grid-12 { grid-template-columns: repeat(12, 1fr); }
    .lm-col-4 { grid-column: span 4; }
    .lm-col-5 { grid-column: span 5; }
    .lm-col-6 { grid-column: span 6; }
    .lm-col-7 { grid-column: span 7; }
}

.lm-card__head { display: flex; flex-direction: column; gap: 10px; }
@media (min-width: 640px) { .lm-card__head { flex-direction: row; align-items: flex-start; justify-content: space-between; } }
.lm-title-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.lm-toggle-group { display: flex; align-items: center; gap: 2px; background: var(--dp-surface-container-low); padding: 4px; border-radius: 8px; font-size: 12px; flex-shrink: 0; flex-wrap: wrap; }
.lm-toggle-group--sm { font-size: 10px; font-family: var(--dp-font-mono); font-weight: 700; }
.lm-toggle { padding: 5px 10px; border-radius: 6px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.lm-toggle--active { background: var(--dp-surface-container-lowest); color: var(--dp-primary); }

.lm-table-wrap { overflow-x: auto; }
.lm-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; min-width: 560px; }
.lm-table thead tr { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; font-weight: 700; }
.lm-table th { padding: 10px 12px; }
.lm-table th:first-child { border-radius: 6px 0 0 6px; }
.lm-table th:last-child { border-radius: 0 6px 6px 0; }
.lm-table tbody tr { border-bottom: 1px solid var(--dp-outline-variant); transition: background 0.15s ease; }
.lm-table tbody tr:last-child { border-bottom: none; }
.lm-table tbody tr:hover { background: var(--dp-surface-container-low); }
.lm-table td { padding: 12px; vertical-align: middle; }
.lm-right { text-align: right; }

.lm-series-row { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.lm-chart { width: 100%; height: 190px; }
.lm-chart__svg { width: 100%; height: 100%; overflow: visible; }

.lm-footline { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); font-size: 12px; }

.lm-opps-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 768px) { .lm-opps-grid { grid-template-columns: repeat(3, 1fr); } }
.lm-opp-card { background: var(--dp-surface-container-low); border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; }
.lm-opp-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
.lm-opp-card__foot { display: flex; flex-direction: column; gap: 8px; padding-top: 10px; border-top: 1px solid var(--dp-outline-variant); }
@media (min-width: 480px) { .lm-opp-card__foot { flex-direction: row; align-items: center; justify-content: space-between; } }

.lm-actions-inline { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.lm-actions-inline--end { justify-content: flex-end; }

.lm-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 14px; border-radius: 8px; border: none;
    font-size: 12px; font-weight: 700; cursor: pointer; transition: background 0.15s ease, color 0.15s ease; font-family: var(--dp-font-sans); white-space: nowrap;
}
.lm-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.lm-btn--primary:hover { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.lm-btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.lm-btn--secondary:hover { background: var(--dp-secondary); color: var(--dp-on-secondary-container); }
.lm-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.lm-btn--muted:hover { background: var(--dp-surface-dim); }
.lm-btn--sm { padding: 6px 10px; }
.lm-btn--full { width: 100%; }
.lm-btn--grow { flex: 1; }

.lm-link { font-weight: 700; color: var(--dp-primary); text-decoration: none; background: none; border: none; cursor: pointer; font-size: 12px; font-family: var(--dp-font-sans); display: inline-flex; align-items: center; gap: 4px; }
.lm-link:hover { text-decoration: underline; }

.lm-spread-list { display: flex; flex-direction: column; gap: 16px; margin: 8px 0; }
.lm-spread-row__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; font-size: 12px; }
.lm-spread-bar { width: 100%; height: 10px; border-radius: 999px; background: var(--dp-surface-container-low); overflow: hidden; display: flex; }
.lm-spread-bar__seg--secondary { background: var(--dp-secondary); }
.lm-spread-bar__seg--muted { background: var(--dp-outline-variant); }
.lm-spread-bar__seg--primary { background: var(--dp-primary); }
.lm-spread-row__foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; margin-top: 6px; font-size: 10px; color: var(--dp-on-surface-variant); }
.lm-legend { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); font-size: 11px; color: var(--dp-on-surface-variant); }
.lm-legend__swatch { width: 10px; height: 10px; border-radius: 3px; }
.lm-legend__swatch--secondary { background: var(--dp-secondary); }
.lm-legend__swatch--muted { background: var(--dp-outline-variant); }
.lm-legend__swatch--primary { background: var(--dp-primary); }

.lm-ports-grid { display: grid; grid-template-columns: 1fr; gap: 14px; }
@media (min-width: 640px) { .lm-ports-grid { grid-template-columns: 1fr 1fr; } }
@media (min-width: 1024px) { .lm-ports-grid { grid-template-columns: repeat(4, 1fr); } }
.lm-port-card { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 10px; }
.lm-port-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.lm-port-card__stats { display: flex; flex-direction: column; gap: 4px; font-size: 12px; }
.lm-port-card__stat { display: flex; justify-content: space-between; }
.lm-port-card__foot { padding-top: 8px; border-top: 1px solid var(--dp-outline-variant); font-size: 11px; }

.lm-weather-grid { display: grid; grid-template-columns: 1fr; gap: 14px; }
@media (min-width: 640px) { .lm-weather-grid { grid-template-columns: 1fr 1fr; } }
@media (min-width: 1024px) { .lm-weather-grid { grid-template-columns: repeat(4, 1fr); } }
.lm-weather-card { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; display: flex; flex-direction: column; gap: 8px; }
.lm-weather-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.lm-weather-card__temp { display: flex; align-items: baseline; gap: 8px; }
.lm-weather-card__rows { display: flex; flex-direction: column; gap: 3px; }
.lm-weather-card__note { font-size: 10px; font-weight: 700; padding-top: 6px; border-top: 1px solid var(--dp-outline-variant); }

.lm-news-list { display: flex; flex-direction: column; gap: 12px; margin-top: 4px; }
.lm-news-item { display: flex; flex-direction: column; gap: 4px; }
.lm-news-item__link { font-size: 12px; font-weight: 700; color: var(--dp-on-surface); text-decoration: none; line-height: 1.4; }
.lm-news-item__link:hover { color: var(--dp-primary); }

.lm-schedule-list { display: flex; flex-direction: column; gap: 10px; margin-top: 4px; }
.lm-schedule-item { display: flex; align-items: flex-start; gap: 10px; background: var(--dp-surface-container-low); padding: 10px; border-radius: 8px; }
.lm-schedule-item__date { background: var(--dp-surface-container-lowest); border-radius: 6px; padding: 6px 10px; text-align: center; flex-shrink: 0; min-width: 46px; }

.lm-desk-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 4px 0 10px; }
.lm-desk-stat { background: var(--dp-surface-container-low); border-radius: 8px; padding: 10px; display: flex; flex-direction: column; gap: 2px; }
.lm-desk-note { display: flex; align-items: center; justify-content: space-between; gap: 8px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); padding: 8px 10px; border-radius: 8px; font-size: 11px; }
.lm-desk-note .lm-link { color: var(--dp-on-primary-fixed); text-decoration: underline; }

.lm-ai-head { display: flex; flex-direction: column; gap: 10px; }
@media (min-width: 768px) { .lm-ai-head { flex-direction: row; align-items: center; justify-content: space-between; } }
.lm-ai-icon { width: 32px; height: 32px; border-radius: 8px; background: var(--dp-primary); color: var(--dp-on-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lm-searchbar { background: var(--dp-surface-container-low); padding: 8px; border-radius: 8px; display: flex; flex-direction: column; gap: 8px; }
@media (min-width: 768px) { .lm-searchbar { flex-direction: row; align-items: center; } }
.lm-searchbar__input { position: relative; flex: 1; display: flex; align-items: center; gap: 10px; padding: 0 14px; background: var(--dp-surface-container-lowest); border-radius: 8px; height: 44px; color: var(--dp-on-surface-variant); }
.lm-searchbar__input input { flex: 1; border: none; outline: none; background: transparent; font-size: 13px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); }
.lm-prompt-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.lm-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: var(--dp-surface-container-low); border: none; color: var(--dp-on-surface); font-size: 12px; cursor: pointer; transition: background 0.15s ease; font-family: var(--dp-font-sans); }
.lm-chip:hover { background: var(--dp-surface-container-high); }
.lm-ai-response { padding: 14px; border-radius: 8px; background: var(--dp-secondary-container); font-size: 12px; }
.lm-ai-response__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.lm-icon-close { background: none; border: none; color: var(--dp-on-surface-variant); cursor: pointer; display: flex; padding: 2px; transform: rotate(45deg); }
</style>
