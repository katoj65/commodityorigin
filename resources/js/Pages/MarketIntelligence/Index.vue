<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { ElMessage } from 'element-plus';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement,
    LineElement, Filler, Tooltip, Legend,
} from 'chart.js';
import {
    Aim, StarFilled, TrendCharts, PieChart, LocationFilled, MapLocation,
    Opportunity, Reading, DataLine, Ticket, GoodsFilled, Medal, CircleCheckFilled,
    Van, Lock, ArrowRight, Right, Close, Promotion, Bell, Filter, Cpu,
    OfficeBuilding, WarningFilled, InfoFilled, TopRight, Ship, Box,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

defineProps({
    articles: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

/* ── Sidebar navigation ─────────────────────────────────────────────── */
const navGroups = [
    { category: 'Overview', links: [
        { id: 'overview', label: 'Market Overview', icon: Aim },
    ] },
    { category: 'Markets', links: [
        { id: 'prices', label: 'Coffee Prices', icon: TrendCharts },
        { id: 'supply-demand', label: 'Supply & Demand', icon: PieChart },
        { id: 'origins', label: 'Origins', icon: LocationFilled },
        { id: 'global-markets', label: 'Global Markets', icon: MapLocation },
    ] },
    { category: 'Intelligence', links: [
        { id: 'opportunities', label: 'Market Opportunities', icon: Opportunity },
        { id: 'news', label: 'Market News', icon: Reading },
        { id: 'signals', label: 'Market Signals', icon: DataLine },
    ] },
    { category: 'Business', links: [
        { id: 'buyer-demand', label: 'Buyer Demand (RFQs)', icon: Ticket },
        { id: 'seller-supply', label: 'Available Supply', icon: GoodsFilled },
        { id: 'my-position', label: 'My Market Position', icon: Medal },
    ] },
    { category: 'Research & Trade', links: [
        { id: 'sustainability', label: 'Sustainability & EUDR', icon: CircleCheckFilled },
        { id: 'logistics', label: 'Trade & Logistics', icon: Van },
    ] },
];
const sectionIds = navGroups.flatMap((g) => g.links.map((l) => l.id));

const activeSection = ref('overview');
let observer;

onMounted(() => {
    const targets = sectionIds.map((id) => document.getElementById(id)).filter(Boolean);
    observer = new IntersectionObserver(
        (entries) => entries.forEach((entry) => { if (entry.isIntersecting) activeSection.value = entry.target.id; }),
        { rootMargin: '-96px 0px -70% 0px', threshold: 0 },
    );
    targets.forEach((el) => observer.observe(el));
});
onBeforeUnmount(() => observer?.disconnect());

/* ── Price chart (7-day Uganda Robusta FOB Mombasa) ─────────────────── */
const currentSpot = 4.20;
const priceLabels = ['Feb 20', 'Feb 21', 'Feb 22', 'Feb 23', 'Feb 24', 'Feb 25', 'Today'];
const pricePoints = [4.10, 4.12, 4.09, 4.14, 4.16, 4.18, 4.20];

const chartData = {
    labels: priceLabels,
    datasets: [{
        label: 'FOB Mombasa ($/kg)',
        data: pricePoints,
        borderColor: '#0d631b',
        backgroundColor: 'rgba(13, 99, 27, 0.08)',
        borderWidth: 2.5,
        tension: 0.35,
        fill: true,
        pointBackgroundColor: '#0d631b',
        pointRadius: 4,
        pointHoverRadius: 6,
    }],
};
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            bodyFont: { family: 'ui-monospace, monospace', size: 12 },
            padding: 10,
            displayColors: false,
            callbacks: { label: (item) => `$${item.formattedValue}/kg FOB Mombasa` },
        },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#64748b' } },
        y: {
            min: Number((Math.min(...pricePoints) - 0.1).toFixed(2)),
            max: Number((Math.max(...pricePoints) + 0.1).toFixed(2)),
            grid: { color: '#f1f5f9' },
            ticks: { font: { size: 10 }, color: '#64748b', callback: (v) => `$${v.toFixed(2)}` },
        },
    },
};

/* ── Supply & demand ────────────────────────────────────────────────── */
const supplyDemand = [
    { label: 'AVAILABLE SUPPLY', value: '2,450', unit: 't', note: 'Ready for stuffing' },
    { label: 'BUYER DEMAND', value: '1,870', unit: 't', note: 'Active solicitations', accent: true },
    { label: 'ACTIVE RFQs', value: '24', note: '+6 this week' },
    { label: 'ACTIVE LOTS', value: '128', note: '100% verified' },
];

/* ── Market opportunities ───────────────────────────────────────────── */
const opportunities = [
    {
        tag: 'DEMAND IDENTIFIED', tone: 'amber', score: 94, title: 'UAE Robusta Export Demand',
        body: 'Gulf roasters in Dubai are actively soliciting Ugandan Robusta meeting Screen 18+ specification for bulk delivery ahead of Ramadan roastery buildup.',
        stats: [['Potential match:', '4 Lots (76 tonnes)'], ['Destination:', 'UAE (CIF Jebel Ali)'], ['Indicated Price:', '$4.15 – $4.30/kg']],
        context: 'Opportunity identified based on RFQ-00124 and Mubende mill stock. Not guaranteed commercial profit.',
        matchCount: 4,
    },
    {
        tag: 'PREMIUM GRADE', tone: 'green', score: 89, title: 'Screen 18 Quality Sourcing',
        body: 'Three verified European buyers have published forward requirements specifically mandating Screen 18 sorting with moisture ≤ 12.0% and EUDR polygon dossiers.',
        stats: [['Matching supply:', '3 Lots (57.6 tonnes)'], ['Destination:', 'Germany & Italy'], ['Spread Advantage:', '+8.5% over Screen 15']],
        context: 'Opportunity mapped from verified dry mill assay records at Jinja and RFQ-00128.',
        matchCount: 3,
    },
    {
        tag: 'SPOT ARBITRAGE', tone: 'blue', score: 82, title: 'Bugisu Arabica AA Escrow Match',
        body: 'An unallocated container of high-altitude washed Mt. Elgon Arabica (87.2 SCAA cup score) is listed at competitive spot price with prompt FOB stuffing.',
        stats: [['Available Volume:', '19.2 tonnes (1 FCL)'], ['Target Importers:', 'Nordic & UK Specialty'], ['Spot Price:', '$5.15/kg FOB']],
        context: 'High cupping score (87.2) matches 2 active specialty search alerts.',
        matchCount: 1,
    },
];

/* ── Buyer demand / seller supply tables ────────────────────────────── */
const buyerDemand = [
    { code: 'RFQ-00124', coffee: 'Uganda Robusta', spec: 'Screen 18 · Natural', qty: '20 t', dest: 'Dubai, UAE', status: 'Open' },
    { code: 'RFQ-00128', coffee: 'Bugisu Arabica', spec: 'Grade AA · Washed', qty: '10 t', dest: 'Hamburg, Germany', status: 'Open' },
    { code: 'RFQ-00135', coffee: 'Uganda Fine Robusta', spec: 'Screen 15/18 Blend', qty: '38 t (2 FCL)', dest: 'Jebel Ali, UAE', status: 'Open' },
    { code: 'RFQ-00142', coffee: 'Rwenzori Arabica', spec: 'Grade 1 · Honey Process', qty: '5 t', dest: 'London, UK', status: 'Reviewing' },
];
const sellerSupply = [
    { id: '#LOT-000124', coffee: 'Uganda Robusta', origin: 'Mukono Basin · Natural', grade: 'Screen 18', qty: '5,000 kg', price: '$4.20/kg', seller: 'Uganda Coffee Traders' },
    { id: '#LOT-000315', coffee: 'Mt. Elgon Arabica', origin: 'Bugisu · Washed', grade: 'Grade AA', qty: '19,200 kg', price: '$5.15/kg', seller: 'Bugisu High Altitude Coop' },
    { id: '#LOT-000412', coffee: 'Mukono Fine Robusta', origin: 'Central Basin · Natural', grade: 'Screen 18', qty: '38,400 kg', price: '$3.95/kg', seller: 'Kyagalanyi Central Mill' },
    { id: '#LOT-000780', coffee: 'Kibale Robusta', origin: 'Mubende · Natural', grade: 'Screen 15', qty: '12,000 kg', price: '$3.85/kg', seller: 'Mubende Origin Shippers' },
];

/* ── Market signals ─────────────────────────────────────────────────── */
const signals = [
    { tag: 'PRICE SIGNAL', tone: 'green', title: 'Robusta Screen 18 Price Upward Momentum', delta: '▲ +2.4%', body: 'Robusta spot execution prices have increased steadily across 14 transactions over 7 days. Exporters are pricing forward lots with a firm floor.', source: 'Bean Origin Exchange Executions', confidence: 'High (14 trades)' },
    { tag: 'DEMAND SIGNAL', tone: 'blue', title: 'Concentrated Inquiry for Screen 18+ Grades', delta: '+32% Volume', body: '74% of all buyer search queries and RFQ volume targeted Screen 18 or above, widening the price premium over Screen 15.', source: 'Platform Search & RFQ Logs', confidence: 'High' },
    { tag: 'SUPPLY SIGNAL', tone: 'amber', title: 'Mubende Regional Intake Surging', delta: '+180 Tonnes', body: 'Central Uganda dry mills reported peak mid-crop parchment arrivals. Moisture levels average 11.6%, well within export compliance limits.', source: 'UCDA Regional Dry Mill Logs', confidence: 'Period: Past 5 Days' },
];

/* ── My market position ─────────────────────────────────────────────── */
const myPosition = [
    { label: 'MY COFFEE', value: '850', unit: 'kg' },
    { label: 'ACTIVE BIDS', value: '2' },
    { label: 'ACTIVE TRADES', value: '5' },
];
const myActivity = [
    { text: 'Bid on #LOT-000624 Nyeri AA Microlot ($7.90/kg)', tag: 'Leading', tone: 'green' },
    { text: 'Contract #ESC-892 (Vessel Sailing to Jebel Ali)', tag: 'In Transit', tone: 'blue' },
];

/* ── Origins & global markets ───────────────────────────────────────── */
const origins = [
    { flag: '🇺🇬', name: 'Uganda (Primary Exchange Hub)', tag: 'Robusta & Arabica', price: '$4.20 / $5.15/kg', stats: [['Available', '2,450 t'], ['Active Lots', '128 lots'], ['EUDR Ready', '100%']], link: 'Explore Uganda Origin Terminal', primary: true },
    { flag: '🇧🇷', name: 'Brazil', tag: 'Arabica Natural', price: '$5.25/kg FOB Santos', stats: [['Available', 'Global Ref'], ['Active Lots', 'External'], ['Benchmark', 'KC Spot']], link: 'Compare Brazil Indices' },
    { flag: '🇻🇳', name: 'Vietnam', tag: 'Robusta G2', price: '$4.35/kg FOB HCM', stats: [['Available', 'Global Ref'], ['Active Lots', 'External'], ['Benchmark', 'London RC']], link: 'Compare Vietnam Indices' },
];
const corridors = [
    { icon: OfficeBuilding, name: 'Middle East & UAE (Jebel Ali)', tag: 'High Demand', tone: 'green', body: 'Transit: 9–11 days via Port of Mombasa. Ocean freight: ~$1,450/20ft FCL. Heavy procurement of Screen 18 Robusta for commercial roasters.', meta: 'Active Corridor Demand: 84 Lots (1,612 MT)' },
    { icon: CircleCheckFilled, name: 'Europe (Rotterdam & Hamburg)', tag: 'EUDR Focus', tone: 'blue', body: 'Transit: 24–28 days. Ocean freight: ~$2,680/20ft FCL. Strict polygon shapefile validation mandatory. High demand for Bugisu Washed Arabica.', meta: 'Active Corridor Demand: 128 Lots (2,457 MT)' },
    { icon: MapLocation, name: 'North America (Houston & New York)', tag: 'Specialty Microlots', tone: 'slate', body: 'Transit: 32–36 days. Ocean freight: ~$3,480/20ft FCL. Focus on high-altitude Rwenzori Honey and Sipi Falls Arabica (86.5+ SCAA).', meta: 'Active Corridor Demand: 62 Lots (1,190 MT)' },
];

/* ── News ────────────────────────────────────────────────────────────── */
const newsFilters = ['All', 'Uganda', 'UAE', 'Europe', 'Regulations', 'Logistics'];
const activeNewsFilter = ref('All');
const newsItems = [
    { icon: OfficeBuilding, source: 'UCDA Official Gazette', time: 'Today, 09:15 EAT', title: 'Uganda February Coffee Exports Reach Record 580,000 Bags Ahead of EUDR', body: 'The Uganda Coffee Development Authority reported that increased dry mill throughput in Jinja and Greater Masaka pushed monthly export earnings to $112M, driven by European buyers accelerating stock intake.', tag: 'Uganda · Regulation', categories: ['Uganda', 'Regulations'] },
    { icon: MapLocation, source: 'Dubai Multi Commodities Centre (DMCC)', time: 'Yesterday, 16:30 GST', title: 'Jebel Ali Coffee Centre Expands Bonded Cold Storage for African Green Beans', body: 'DMCC has opened an additional 15,000 MT climate-controlled storage hall dedicated to East African Robusta and Arabica shipments, facilitating 24-hour turnaround for regional re-exports to GCC roasters.', tag: 'UAE · Logistics', categories: ['UAE', 'Logistics'] },
    { icon: CircleCheckFilled, source: 'European Commission Trade Directorate', time: '2 Days ago', title: 'EU Deforestation Regulation: Digital Product Passports Now Accepted via API', body: 'EU customs authorities have published the final TRACES portal interoperability standard. Bean Origin’s satellite polygon and lot hashing architecture has been registered as an accredited data connector.', tag: 'Europe · EUDR', categories: ['Europe', 'Regulations'] },
    { icon: Ship, source: 'Mombasa Port Authority Log', time: '3 Days ago', title: 'Standard Gauge Railway (SGR) Freight Feeder Slashes Dry Mill Transit to 36 Hours', body: 'Direct bonded rail wagons from Malaba/Jinja container depots directly into Mombasa berth 21 have reduced trans-shipment friction, lowering average container demurrage risk for exporters.', tag: 'Logistics · Transit', categories: ['Logistics'] },
];
const filteredNews = computed(() => activeNewsFilter.value === 'All'
    ? newsItems
    : newsItems.filter((item) => item.categories.includes(activeNewsFilter.value)));

/* ── Sustainability & logistics ─────────────────────────────────────── */
const sustainability = [
    { icon: WarningFilled, title: 'EUDR Zero-Deforestation Geofencing', tag: 'Mandatory 2025', tone: 'red', body: 'All lots shipped into the European Union must have GPS perimeter polygons verifying smallholder plots were not deforested post-December 31, 2020.', market: 'European Union', effective: 'Dec 30, 2024' },
    { icon: CircleCheckFilled, title: 'UCDA National Coffee Register (G-Tax & ID)', tag: 'In Effect', tone: 'green', body: 'Every commercial container stuffed in Uganda requires an electronic Certificate of Origin verified against registered farm collections and dry mill assays.', market: 'Uganda Export', effective: 'Active 2024' },
    { icon: InfoFilled, title: 'GCC Phytosanitary & Moisture Thresholds', tag: 'Standard', tone: 'blue', body: 'Shipments to Jebel Ali and Saudi ports mandate maximum moisture ceiling of 12.5% with certified SGS pre-shipment fumigation seals.', market: 'Middle East / GCC', effective: 'Continuous' },
];
const logistics = [
    { icon: Ship, title: 'Port of Mombasa Berth Status', tag: 'Normal Transit', body: 'Average container dwell time at Mombasa terminal: 3.2 days. Vessel turnaround on Middle East & Mediterranean feeders is operating at normal SLA.', meta: 'Active Corridor Loads: 142.8 tonnes stuffed this week' },
    { icon: Van, title: 'Jinja-to-Mombasa Bonded Rail Railcars', tag: 'Active Shuttle', body: 'Weekly dedicated agricultural rail shuttle departs Jinja dry mills every Tuesday and Friday, avoiding highway truck congestion at Malaba border.', meta: 'Average Transit Time: 36.4 hours from dry mill to dock' },
    { icon: Box, title: 'GrainPro & Hermetic Liner Availability', tag: '100% Stocked', body: 'All Bean Origin partner mills maintain certified 5-layer hermetic liners ensuring specialty arabica and fine robusta maintain cupping score during marine voyage.', meta: 'Quality Retention SLA: <0.5 pt cupping drift over 45 days' },
];

/* ── AI analyst prompts ─────────────────────────────────────────────── */
const promptChips = [
    'Which markets are currently looking for Ugandan Robusta?',
    'What changed in Robusta prices this month?',
    'Which of my Lots match current buyer demand?',
    'What should I watch in the UAE market?',
];

/* ── Dialogs ─────────────────────────────────────────────────────────── */
const alertOpen = ref(false);
const matchingOpen = ref(false);
const matchingTitle = ref('');
const matchingLots = [
    { id: '#LOT-000124', coffee: 'Uganda Robusta', mill: 'Mukono Dry Mill', grade: 'Screen 18', qty: '5,000 kg', price: '$4.20/kg' },
    { id: '#LOT-000412', coffee: 'Mukono Fine Robusta', mill: 'Kyagalanyi Central Mill', grade: 'Screen 18', qty: '38,400 kg', price: '$3.95/kg' },
];

function openMatching(title) {
    matchingTitle.value = title;
    matchingOpen.value = true;
}

const aiOpen = ref(false);
const aiDraft = ref('');
const chatMessages = ref([
    { from: 'ai', text: 'Hello Kato. I am tracking physical coffee inventories across Mubende, Mukono, and Mt. Elgon dry mills, alongside live RFQs from European and GCC roasters. How can I assist your trading decisions today?' },
]);

function openAi(prompt) {
    if (prompt) aiDraft.value = prompt;
    aiOpen.value = true;
}

function sendChat() {
    const text = aiDraft.value.trim();
    if (!text) return;
    chatMessages.value.push({ from: 'user', text });
    aiDraft.value = '';
    setTimeout(() => {
        chatMessages.value.push({
            from: 'ai',
            text: `Regarding "${text}": platform telemetry tracks 2,450 tonnes of physical supply across 128 verified lots. RFQ-00124 (Dubai) and RFQ-00128 (Germany) currently represent the highest liquidity opportunities matching Screen 18 specifications at an average spread of $4.20/kg FOB.`,
        });
    }, 500);
}

function saveAlert() {
    alertOpen.value = false;
    ElMessage.success('Market alert created (dummy preview) — notifications route to your Activity Inbox and email.');
}
</script>

<template>
    <OuterLayout title="Market Intelligence">
        <div class="mi-shell bg-white min-h-screen lg:flex">
            <!-- Sidebar -->
            <aside class="mi-sidebar hidden lg:flex lg:flex-col lg:flex-shrink-0 sticky w-[250px] bg-white border-r border-slate-200 overflow-y-auto" style="top: 80px; max-height: calc(100vh - 80px);">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-950 flex items-center gap-1.5"><el-icon :size="15" class="text-[#0d631b]"><Aim /></el-icon> Market Intelligence</span>
                    <span class="text-[10px] font-mono text-slate-400 bg-slate-50 border border-slate-200 rounded px-1.5 py-0.5">v2.6</span>
                </div>
                <nav class="py-2 flex-1">
                    <template v-for="group in navGroups" :key="group.category">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-4 pt-4 pb-1.5">{{ group.category }}</div>
                        <a v-for="link in group.links" :key="link.id" :href="`#${link.id}`" class="mi-nav-link" :class="{ 'mi-nav-link--active': activeSection === link.id }">
                            <el-icon :size="14"><component :is="link.icon" /></el-icon> {{ link.label }}
                        </a>
                    </template>
                </nav>
                <div class="p-3 m-3 mt-0 bg-slate-50 rounded-lg border border-slate-200">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-900 mb-1"><el-icon :size="13" class="text-[#0d631b]"><Cpu /></el-icon> Coffee Trade Copilot</div>
                    <p class="text-[11px] text-slate-500 mb-2 leading-snug">Analyze physical inventories, match RFQ specs, or draft forward contracts.</p>
                    <button type="button" class="w-full py-1.5 text-xs font-semibold text-[#0d631b] border border-[#0d631b]/40 rounded-md hover:bg-[#e6f4ee] transition" @click="openAi()">Open Analyst Chat</button>
                </div>
            </aside>

            <!-- Main -->
            <main class="mi-main w-full min-w-0 px-4 md:px-8 py-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-3 pb-4 border-b border-slate-200 mb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-extrabold text-slate-950 tracking-tight">Market Intelligence</h1>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-[#e6f4ee] text-[#0d631b] border border-[#0d631b]/20">Physical Commodity Desk</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 m-0">Understand coffee markets, identify opportunities, and make informed trading decisions.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition" @click="alertOpen = true">
                            <el-icon :size="13"><Bell /></el-icon> Create Alert
                        </button>
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-[#0d631b] hover:bg-[#0a4f15] rounded-lg transition" @click="openAi()">
                            <el-icon :size="13"><StarFilled /></el-icon> Ask AI
                        </button>
                    </div>
                </div>

                <!-- Overview marker (metrics live in supply/demand + prices below) -->
                <div id="overview"></div>

                <!-- Prices & Supply/Demand -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-5">
                    <section id="prices" class="lg:col-span-8">
                        <div class="mi-card h-full flex flex-col">
                            <div class="mi-card__head">
                                <div>
                                    <div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><TrendCharts /></el-icon> Coffee Prices: Uganda Robusta (Screen 18)</h3><span class="mi-badge-data">Spot Market Feed</span></div>
                                    <div class="text-xs text-slate-500 mt-0.5">Physical trade benchmark · FOB Port of Mombasa</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
                                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                                    <span class="text-[10px] font-mono text-slate-400 block">CURRENT SPOT</span>
                                    <div class="text-lg font-bold font-mono text-slate-950 my-0.5">${{ currentSpot.toFixed(2) }}<span class="text-slate-400 text-xs font-normal">/kg</span></div>
                                    <span class="text-[10px] font-mono text-[#0d631b] bg-[#e6f4ee] px-1 rounded">▲ +2.4%</span>
                                </div>
                                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                                    <span class="text-[10px] font-mono text-slate-400 block">PREVIOUS PERIOD</span>
                                    <div class="text-lg font-bold font-mono text-slate-950 my-0.5">$4.10<span class="text-slate-400 text-xs font-normal">/kg</span></div>
                                    <span class="text-[10px] font-mono text-slate-400">7-day rolling baseline</span>
                                </div>
                                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                                    <span class="text-[10px] font-mono text-slate-400 block">PERIOD RANGE</span>
                                    <div class="text-base font-bold font-mono text-slate-950 my-0.5">$4.08 – $4.22</div>
                                    <span class="text-[10px] font-mono text-slate-400">Low / High spread</span>
                                </div>
                                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                                    <span class="text-[10px] font-mono text-slate-400 block">LONDON RC EQUIV</span>
                                    <div class="text-lg font-bold font-mono text-slate-950 my-0.5">$4,120<span class="text-slate-400 text-xs font-normal">/t</span></div>
                                    <span class="text-[10px] font-mono text-[#0d631b]">+$80/t differential</span>
                                </div>
                            </div>
                            <div style="height: 220px;"><Line :data="chartData" :options="chartOptions" /></div>
                            <div class="mi-transparency mt-3 flex justify-between items-center flex-wrap gap-1">
                                <span><strong>Data Source:</strong> Bean Origin Spot Orderbook + UCDA Daily Indicative Price Sheet.</span>
                                <span class="font-mono">Updated: Today, 11:45 EAT</span>
                            </div>
                        </div>
                    </section>

                    <section id="supply-demand" class="lg:col-span-4">
                        <div class="mi-card h-full flex flex-col justify-between">
                            <div>
                                <div class="mi-card__head">
                                    <div><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><PieChart /></el-icon> Supply & Demand</h3><div class="text-xs text-slate-500 mt-0.5">Physical liquidity equilibrium</div></div>
                                    <span class="text-[11px] font-mono font-semibold text-[#1e40af] bg-[#eff6ff] border border-[#1e40af]/20 px-2 py-0.5 rounded">BALANCED</span>
                                </div>
                                <div class="mb-3">
                                    <div class="flex justify-between text-[11px] font-mono text-slate-500 mb-1"><span>Supply (56.7%)</span><span>Demand (43.3%)</span></div>
                                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden flex">
                                        <div class="h-full bg-[#0d631b]" style="width: 56.7%"></div>
                                        <div class="h-full bg-[#1e40af]" style="width: 43.3%"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div v-for="item in supplyDemand" :key="item.label" class="p-2.5 border border-slate-200 rounded-lg text-center">
                                        <span class="text-[10px] font-mono text-slate-400 block">{{ item.label }}</span>
                                        <div class="text-lg font-bold font-mono" :class="item.accent ? 'text-[#1e40af]' : 'text-slate-950'">{{ item.value }} <span class="text-xs font-normal text-slate-400" v-if="item.unit">{{ item.unit }}</span></div>
                                        <span class="text-[10px] text-slate-400">{{ item.note }}</span>
                                    </div>
                                </div>
                                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200 mt-3 text-xs text-slate-600">
                                    <strong class="text-slate-900">Sourcing Ratio:</strong> 1.31 tonnes of listed supply exists per tonne of verified buyer demand. Spot buyers retain prompt pricing advantage.
                                </div>
                            </div>
                            <a href="#buyer-demand" class="block text-center mt-3 pt-2 border-t border-slate-100 text-xs font-semibold text-slate-700 border-slate-200 py-1.5 rounded-lg hover:bg-slate-50 transition">Explore Supply & Demand Data</a>
                        </div>
                    </section>
                </div>

                <!-- Market Opportunities -->
                <section id="opportunities" class="mb-5">
                    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                        <div>
                            <div class="flex items-center gap-2"><h2 class="text-base font-bold text-slate-950 m-0">Market Opportunities</h2><span class="mi-badge-ai"><el-icon :size="11"><StarFilled /></el-icon> AI Opportunity Matching</span></div>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Automated requirement-to-inventory matchmaking across current market solicitations.</p>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-50 border border-slate-200 rounded px-2 py-0.5">Opportunities updated hourly</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div v-for="opp in opportunities" :key="opp.title" class="mi-opportunity">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <span class="mi-mini-tag" :class="`mi-mini-tag--${opp.tone}`">{{ opp.tag }}</span>
                                    <span class="text-[11px] font-mono text-slate-400">Match Score: {{ opp.score }}%</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-950 mb-1">{{ opp.title }}</h4>
                                <p class="text-xs text-slate-500 mb-3 leading-relaxed">{{ opp.body }}</p>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-[11px] font-mono mb-3 space-y-1">
                                    <div v-for="stat in opp.stats" :key="stat[0]" class="flex justify-between"><span class="text-slate-500">{{ stat[0] }}</span><strong class="text-slate-900">{{ stat[1] }}</strong></div>
                                </div>
                                <div class="text-[11px] text-slate-400 italic mb-3"><el-icon :size="11"><InfoFilled /></el-icon> AI Context: {{ opp.context }}</div>
                            </div>
                            <div class="flex gap-2 pt-2 border-t border-slate-100">
                                <button type="button" class="flex-1 py-1.5 text-xs font-semibold text-white bg-[#0d631b] hover:bg-[#0a4f15] rounded-lg transition" @click="openMatching(opp.title)">View Matching Coffee ({{ opp.matchCount }})</button>
                                <button type="button" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition" @click="ElMessage.info('Opening detailed specification (dummy preview).')">Explore</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Buyer demand / seller supply -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
                    <section id="buyer-demand">
                        <div class="mi-card h-full">
                            <div class="mi-card__head">
                                <div><div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><Ticket /></el-icon> Buyer Demand (Active RFQs)</h3><span class="mi-badge-data">Exchange Solicitations</span></div><div class="text-xs text-slate-500 mt-0.5">Real-time procurement requirements from global roasters</div></div>
                                <button type="button" class="text-xs font-mono font-semibold text-slate-600 hover:text-slate-900" @click="ElMessage.info('Viewing all 24 RFQs (dummy preview).')">View All 24 RFQs</button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs border-collapse min-w-[520px]">
                                    <thead><tr class="text-left text-[10px] font-mono uppercase text-slate-400 border-b border-slate-200 bg-slate-50"><th class="py-2">Requirement</th><th class="py-2">Coffee</th><th class="py-2">Qty</th><th class="py-2">Destination</th><th class="py-2">Status</th><th class="py-2 text-right">Action</th></tr></thead>
                                    <tbody>
                                        <tr v-for="row in buyerDemand" :key="row.code" class="border-b border-slate-100 last:border-b-0">
                                            <td class="py-2 font-mono font-semibold text-[#1e40af]">{{ row.code }}</td>
                                            <td class="py-2"><div class="font-bold text-slate-900">{{ row.coffee }}</div><div class="text-[10px] text-slate-400">{{ row.spec }}</div></td>
                                            <td class="py-2 font-mono font-bold text-slate-900">{{ row.qty }}</td>
                                            <td class="py-2 text-slate-600">{{ row.dest }}</td>
                                            <td class="py-2"><span class="text-[10px] font-mono px-1.5 py-0.5 rounded" :class="row.status === 'Open' ? 'bg-[#e6f4ee] text-[#0d631b]' : 'bg-amber-50 text-amber-700'">{{ row.status }}</span></td>
                                            <td class="py-2 text-right"><button type="button" class="text-[11px] font-mono font-semibold text-slate-700 border border-slate-200 rounded px-2 py-0.5 hover:bg-slate-50" @click="openMatching(row.coffee)">Match</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mi-transparency mt-3 flex justify-between items-center flex-wrap gap-1">
                                <span><strong>Data:</strong> Validated buyer requests backed by Stanbic escrow deposit readiness.</span>
                                <button type="button" class="text-[#0d631b] font-semibold flex items-center gap-1" @click="ElMessage.info('Filter demand specifications (dummy preview).')"><el-icon :size="11"><Filter /></el-icon> Filter Demand</button>
                            </div>
                        </div>
                    </section>

                    <section id="seller-supply">
                        <div class="mi-card h-full">
                            <div class="mi-card__head">
                                <div><div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><GoodsFilled /></el-icon> Available Supply (Live Exchange)</h3><span class="mi-badge-data">Physical Lots</span></div><div class="text-xs text-slate-500 mt-0.5">Physical coffee lots ready for trade & container stuffing</div></div>
                                <button type="button" class="text-xs font-mono font-semibold text-slate-600 hover:text-slate-900" @click="ElMessage.info('Opening full Exchange catalog (dummy preview).')">View Exchange (128)</button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs border-collapse min-w-[540px]">
                                    <thead><tr class="text-left text-[10px] font-mono uppercase text-slate-400 border-b border-slate-200 bg-slate-50"><th class="py-2">Lot #</th><th class="py-2">Coffee & Origin</th><th class="py-2">Grade</th><th class="py-2">Available</th><th class="py-2">Price</th><th class="py-2 text-right">Action</th></tr></thead>
                                    <tbody>
                                        <tr v-for="row in sellerSupply" :key="row.id" class="border-b border-slate-100 last:border-b-0">
                                            <td class="py-2 font-mono font-semibold text-[#0d631b]">{{ row.id }}</td>
                                            <td class="py-2"><div class="font-bold text-slate-900">{{ row.coffee }}</div><div class="text-[10px] text-slate-400">{{ row.origin }}</div></td>
                                            <td class="py-2"><span class="text-[10px] font-mono bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5">{{ row.grade }}</span></td>
                                            <td class="py-2 font-mono font-bold text-slate-900">{{ row.qty }}</td>
                                            <td class="py-2 font-mono font-bold text-slate-950">{{ row.price }}</td>
                                            <td class="py-2 text-right"><button type="button" class="text-[11px] font-mono font-semibold text-[#0d631b] border border-[#0d631b]/30 rounded px-2 py-0.5 hover:bg-[#e6f4ee]" @click="openMatching(row.coffee)">Dossier</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mi-transparency mt-3 flex justify-between items-center flex-wrap gap-1">
                                <span><strong>Data:</strong> Physical lots verified by UCDA inspectors with moisture logs attached.</span>
                                <a href="#" class="text-[#0d631b] font-semibold flex items-center gap-1">Open Exchange Catalog <el-icon :size="11"><ArrowRight /></el-icon></a>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Market signals / My position -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
                    <section id="signals">
                        <div class="mi-card h-full">
                            <div class="mi-card__head">
                                <div><div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><DataLine /></el-icon> Market Signals</h3><span class="mi-badge-data">Derived Telemetry</span></div><div class="text-xs text-slate-500 mt-0.5">Quantitative signals from exchange transaction activity</div></div>
                                <span class="text-[11px] font-mono text-slate-400">Window: 7 Days</span>
                            </div>
                            <div class="flex flex-col gap-2.5">
                                <div v-for="signal in signals" :key="signal.title" class="mi-signal" :class="`mi-signal--${signal.tone}`">
                                    <div class="flex justify-between items-center mb-1 gap-2">
                                        <div class="flex items-center gap-1.5 flex-wrap"><span class="text-[10px] font-mono bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5">{{ signal.tag }}</span><strong class="text-xs text-slate-900">{{ signal.title }}</strong></div>
                                        <span class="text-xs font-mono font-bold flex-shrink-0" :class="`mi-signal-delta--${signal.tone}`">{{ signal.delta }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-1">{{ signal.body }}</p>
                                    <div class="flex justify-between text-[10px] font-mono text-slate-400 flex-wrap gap-1"><span>Source: {{ signal.source }}</span><span>Confidence: {{ signal.confidence }}</span></div>
                                </div>
                            </div>
                            <div class="mi-transparency mt-3"><el-icon :size="12" class="text-[#0d631b]"><CircleCheckFilled /></el-icon> <strong>Methodology Note:</strong> Signals are algorithmic aggregations of verified trades and intake receipts.</div>
                        </div>
                    </section>

                    <section id="my-position">
                        <div class="mi-card h-full flex flex-col justify-between">
                            <div>
                                <div class="mi-card__head">
                                    <div><div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><Medal /></el-icon> Your Market Position</h3><span class="text-[11px] font-mono font-semibold text-[#0d631b] bg-[#e6f4ee] px-1.5 py-0.5 rounded border border-[#0d631b]/20">KM Account</span></div><div class="text-xs text-slate-500 mt-0.5">Personalized standing based on your verified inventory & bids</div></div>
                                    <span class="text-[11px] font-mono text-slate-400 bg-slate-50 border border-slate-200 rounded px-2 py-0.5">Stanbic Escrow Live</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-3">
                                    <div v-for="item in myPosition" :key="item.label" class="p-2 border border-slate-200 rounded-lg bg-slate-50 text-center">
                                        <span class="text-[10px] font-mono text-slate-400 block">{{ item.label }}</span>
                                        <strong class="text-lg font-mono text-slate-950">{{ item.value }} <span class="text-xs font-normal text-slate-400" v-if="item.unit">{{ item.unit }}</span></strong>
                                    </div>
                                </div>
                                <div class="p-3 bg-[#f0fdf4] border border-[#0d631b]/20 rounded-lg mb-3">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-[#0d631b] mb-1"><el-icon :size="13"><CircleCheckFilled /></el-icon> Potential Match Identified</div>
                                    <p class="text-xs text-slate-800 mb-2 leading-relaxed">You have <strong>850 kg of Screen 18 Uganda Robusta</strong> (#LOT-000124 allocation) that matches <strong>two active buyer requirements</strong> (RFQ-00124 & RFQ-00135).</p>
                                    <div class="flex gap-2">
                                        <button type="button" class="px-2.5 py-1 text-[11px] font-semibold text-white bg-[#0d631b] hover:bg-[#0a4f15] rounded-md" @click="ElMessage.info('Navigating to Lot #LOT-000124 (dummy preview).')">View My Lot</button>
                                        <button type="button" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-md hover:bg-slate-50" @click="ElMessage.info('Viewing matching RFQs: RFQ-00124, RFQ-00135.')">View Matching RFQs (2)</button>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-mono font-bold uppercase text-slate-400">Your Active Positions:</span>
                                    <ul class="mt-1.5 space-y-1.5 list-none p-0 m-0">
                                        <li v-for="item in myActivity" :key="item.text" class="flex justify-between items-center border-b border-slate-100 pb-1.5 text-xs text-slate-600">
                                            <span>{{ item.text }}</span>
                                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded flex-shrink-0" :class="item.tone === 'green' ? 'bg-[#e6f4ee] text-[#0d631b]' : 'bg-[#eff6ff] text-[#1e40af]'">{{ item.tag }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mi-transparency mt-3 flex justify-between items-center flex-wrap gap-1">
                                <span><strong>Data:</strong> Synchronized with your Stanbic Bank custodial escrow balance.</span>
                                <a href="#" class="text-[#0d631b] font-semibold">Manage Holdings</a>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Origins / Global markets -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
                    <section id="origins">
                        <div class="mi-card h-full">
                            <div class="mi-card__head">
                                <div><div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><LocationFilled /></el-icon> Coffee Origins Comparison</h3><span class="mi-badge-data">Physical Sourcing</span></div><div class="text-xs text-slate-500 mt-0.5">Compare regional supply pools, listing counts, and benchmark spot prices</div></div>
                            </div>
                            <div class="flex flex-col gap-2.5">
                                <div v-for="origin in origins" :key="origin.name" class="mi-origin" :class="{ 'mi-origin--primary': origin.primary }">
                                    <div class="flex justify-between items-center mb-1 flex-wrap gap-1">
                                        <div class="flex items-center gap-2"><span>{{ origin.flag }}</span><strong class="text-sm text-slate-900">{{ origin.name }}</strong><span class="text-[10px] font-mono bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5">{{ origin.tag }}</span></div>
                                        <span class="font-mono font-bold text-sm" :class="origin.primary ? 'text-[#0d631b]' : 'text-slate-900'">{{ origin.price }}</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1 text-[11px] font-mono text-slate-500">
                                        <div v-for="stat in origin.stats" :key="stat[0]">{{ stat[0] }}: <strong class="text-slate-900">{{ stat[1] }}</strong></div>
                                    </div>
                                    <div class="mt-1.5 text-right"><a href="#" class="text-[11px] font-semibold" :class="origin.primary ? 'text-[#0d631b]' : 'text-slate-500'">{{ origin.link }} →</a></div>
                                </div>
                            </div>
                            <div class="mi-transparency mt-3"><strong>Sources:</strong> Bean Origin physical inventories + ICO global export reports.</div>
                        </div>
                    </section>

                    <section id="global-markets">
                        <div class="mi-card h-full">
                            <div class="mi-card__head">
                                <div><div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><MapLocation /></el-icon> Global Markets Corridors</h3><span class="mi-badge-data">Trade Flow Telemetry</span></div><div class="text-xs text-slate-500 mt-0.5">Corridor-specific demand, customs SLA, and freight telemetry</div></div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div v-for="corridor in corridors" :key="corridor.name" class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                                    <div class="flex justify-between items-center mb-1 gap-2">
                                        <strong class="text-xs text-slate-900 flex items-center gap-1.5"><el-icon :size="13"><component :is="corridor.icon" /></el-icon> {{ corridor.name }}</strong>
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded flex-shrink-0" :class="`mi-mini-tag--${corridor.tone}`">{{ corridor.tag }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mb-1">{{ corridor.body }}</p>
                                    <div class="text-[10px] font-mono text-slate-400">{{ corridor.meta }}</div>
                                </div>
                            </div>
                            <div class="mi-transparency mt-3"><strong>Data:</strong> Corridors reflect actual Mombasa port bill-of-lading bookings and ocean liner freight schedules.</div>
                        </div>
                    </section>
                </div>

                <!-- News -->
                <section id="news" class="mb-5">
                    <div class="mi-card">
                        <div class="mi-card__head flex-wrap gap-2">
                            <div><div class="flex items-center gap-2"><h2 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><Reading /></el-icon> Coffee Market News & Updates</h2><span class="mi-badge-data">Verified Feeds</span></div><div class="text-xs text-slate-500 mt-0.5">External industry intelligence, regulatory updates, and crop developments</div></div>
                            <div class="flex flex-wrap gap-1">
                                <button v-for="f in newsFilters" :key="f" type="button" class="px-2.5 py-1 text-[11px] font-mono font-semibold rounded transition" :class="activeNewsFilter === f ? 'bg-[#0d631b] text-white' : 'text-slate-600 border border-slate-200 hover:bg-slate-50'" @click="activeNewsFilter = f">{{ f }}</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div v-for="item in filteredNews" :key="item.title" class="p-3 border border-slate-200 rounded-lg bg-slate-50 flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-center mb-1 text-[11px] font-mono text-slate-400">
                                        <span class="flex items-center gap-1.5"><el-icon :size="12" class="text-[#0d631b]"><component :is="item.icon" /></el-icon> {{ item.source }}</span>
                                        <span>{{ item.time }}</span>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-950 mb-1 leading-snug">{{ item.title }}</h4>
                                    <p class="text-xs text-slate-500 leading-relaxed mb-2">{{ item.body }}</p>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                                    <span class="text-[10px] font-mono bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5">{{ item.tag }}</span>
                                    <button type="button" class="text-[11px] font-semibold text-[#0d631b] flex items-center gap-1" @click="ElMessage.info('Reading full dispatch (dummy preview).')">Read Full Dispatch <el-icon :size="10"><ArrowRight /></el-icon></button>
                                </div>
                            </div>
                        </div>
                        <div class="mi-transparency mt-3 flex justify-between items-center flex-wrap gap-1">
                            <span><strong>Sources:</strong> Verified external trade authorities, official gazettes, and ocean freight logistics feeds.</span>
                            <span class="font-mono">All sources independently linked</span>
                        </div>
                    </div>
                </section>

                <!-- Sustainability / Logistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
                    <section id="sustainability">
                        <div class="mi-card h-full">
                            <div class="mi-card__head">
                                <div><div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><CircleCheckFilled /></el-icon> Sustainability & Regulations</h3><span class="mi-badge-data">Compliance Mandates</span></div><div class="text-xs text-slate-500 mt-0.5">Regulatory standards, import rules, and zero-deforestation criteria</div></div>
                            </div>
                            <div class="flex flex-col gap-2.5">
                                <div v-for="item in sustainability" :key="item.title" class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                    <div class="flex justify-between items-start mb-1 gap-2">
                                        <strong class="text-xs text-slate-900 flex items-center gap-1.5"><el-icon :size="13" :class="`mi-signal-delta--${item.tone}`"><component :is="item.icon" /></el-icon> {{ item.title }}</strong>
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded flex-shrink-0" :class="`mi-mini-tag--${item.tone}`">{{ item.tag }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-2">{{ item.body }}</p>
                                    <div class="flex justify-between items-center text-[10px] font-mono text-slate-400 pt-1 border-t border-slate-100 flex-wrap gap-1">
                                        <span>Market: <strong class="text-slate-700">{{ item.market }}</strong></span>
                                        <span>Effective: <strong class="text-slate-700">{{ item.effective }}</strong></span>
                                        <button type="button" class="text-[#0d631b] font-semibold" @click="ElMessage.info('Opening compliance dossier (dummy preview).')">Dossier →</button>
                                    </div>
                                </div>
                            </div>
                            <div class="mi-transparency mt-3"><strong>Note:</strong> Regulatory information is aggregated for operational guidance and does not replace formal legal counsel.</div>
                        </div>
                    </section>

                    <section id="logistics">
                        <div class="mi-card h-full">
                            <div class="mi-card__head">
                                <div><div class="flex items-center gap-2"><h3 class="text-sm font-bold text-slate-950 m-0 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><Van /></el-icon> Trade & Logistics Intelligence</h3><span class="mi-badge-data">Corridor Telemetry</span></div><div class="text-xs text-slate-500 mt-0.5">Maritime bottlenecks, port dwell times, and freight indexes</div></div>
                            </div>
                            <div class="flex flex-col gap-2.5">
                                <div v-for="item in logistics" :key="item.title" class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                    <div class="flex justify-between items-center mb-1 gap-2">
                                        <strong class="text-xs text-slate-900 flex items-center gap-1.5"><el-icon :size="13" class="text-[#0d631b]"><component :is="item.icon" /></el-icon> {{ item.title }}</strong>
                                        <span class="text-[10px] font-mono bg-[#e6f4ee] text-[#0d631b] border border-[#0d631b]/20 rounded px-1.5 py-0.5 flex-shrink-0">{{ item.tag }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-1">{{ item.body }}</p>
                                    <div class="text-[10px] font-mono text-slate-400">{{ item.meta }}</div>
                                </div>
                            </div>
                            <div class="mi-transparency mt-3"><strong>Telemetry:</strong> Live updates from Kenya Ports Authority & Maersk East Africa container trackers.</div>
                        </div>
                    </section>
                </div>

                <!-- AI Analyst CTA -->
                <section class="mb-5">
                    <div class="mi-ai-cta">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                            <div class="lg:col-span-8">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[11px] font-mono font-semibold bg-white text-slate-900 px-2 py-0.5 rounded flex items-center gap-1"><el-icon :size="11" class="text-[#0d631b]"><StarFilled /></el-icon> Persistent Copilot</span>
                                    <h3 class="text-lg font-bold text-white m-0">Ask the Market Analyst</h3>
                                </div>
                                <p class="text-white/70 text-sm mb-3 max-w-xl">Ask about prices, demand, supply, export opportunities, or current market conditions. The analyst synthesizes Bean Origin's 128 active lots, current RFQs, and physical market indices.</p>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="prompt in promptChips" :key="prompt" type="button" class="text-[11px] font-mono px-2.5 py-1.5 rounded-md bg-white/95 text-slate-900 hover:bg-white transition" @click="openAi(prompt)">"{{ prompt }}"</button>
                                </div>
                            </div>
                            <div class="lg:col-span-4">
                                <div class="bg-white/10 border border-white/20 rounded-xl p-3">
                                    <div class="flex gap-2">
                                        <input v-model="aiDraft" type="text" placeholder="Ask Market Analyst..." class="mi-ai-input" @keydown.enter="openAi()" />
                                        <button type="button" class="w-9 h-9 flex-shrink-0 flex items-center justify-center rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-900" @click="openAi()"><el-icon :size="15"><Promotion /></el-icon></button>
                                    </div>
                                    <span class="block mt-1.5 text-[10px] font-mono text-white/50">Direct natural-language procurement queries</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Footer transparency -->
                <footer class="mi-card p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h5 class="text-sm font-bold text-slate-950 mb-2 flex items-center gap-1.5"><el-icon :size="14" class="text-[#0d631b]"><Right /></el-icon> Platform Architecture: The 3-Layer Principle</h5>
                            <p class="text-xs text-slate-500 mb-3 leading-relaxed">Bean Origin structurally separates three elements to ensure total market clarity and user sovereignty:</p>
                            <div class="space-y-2 text-xs">
                                <div class="p-2 border border-slate-200 rounded-lg bg-slate-50"><strong class="font-mono text-slate-900">1. Market Data:</strong> <span class="text-slate-500">The empirical, audited facts (e.g. 3 active RFQs request Screen 18 Robusta; 128 verified physical lots in storage).</span></div>
                                <div class="p-2 border border-slate-200 rounded-lg bg-slate-50"><strong class="font-mono text-slate-900">2. AI Analysis:</strong> <span class="text-slate-500">Interpretive algorithmic synthesis (e.g. Current solicitations match 2 unallocated lots on Bean Origin).</span></div>
                                <div class="p-2 border border-slate-200 rounded-lg bg-slate-50"><strong class="font-mono text-slate-900">3. Trading Action:</strong> <span class="text-slate-500">User-determined commercial decisions (e.g. Inspect lot, submit counter-offer, or initiate escrow deposit).</span></div>
                            </div>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-slate-950 mb-2 flex items-center gap-1.5"><el-icon :size="14" class="text-[#1e40af]"><CircleCheckFilled /></el-icon> Data Sources & Transparency Disclosures</h5>
                            <p class="text-xs text-slate-500 mb-2 leading-relaxed">Information displayed on this terminal is drawn from verified internal ledgers and accredited international sources:</p>
                            <ul class="space-y-1.5 text-[11px] font-mono text-slate-500 list-none p-0 m-0">
                                <li><el-icon :size="11" class="text-[#0d631b]"><CircleCheckFilled /></el-icon> <strong class="text-slate-700">Bean Origin Exchange:</strong> Real-time trades, physical lot reserves, and Stanbic Bank Tier-1 escrow custody records.</li>
                                <li><el-icon :size="11" class="text-[#0d631b]"><CircleCheckFilled /></el-icon> <strong class="text-slate-700">UCDA Official Data:</strong> Daily indicative export farmgate & FOB Mombasa inspection benchmarks.</li>
                                <li><el-icon :size="11" class="text-[#0d631b]"><CircleCheckFilled /></el-icon> <strong class="text-slate-700">Ocean Carrier Logistics:</strong> Maersk & CMA CGM Port of Mombasa container telemetry.</li>
                                <li><el-icon :size="11" class="text-[#0d631b]"><CircleCheckFilled /></el-icon> <strong class="text-slate-700">Satellite Agrotechnology:</strong> Sentinel-2 geospatial polygon verification for EUDR zero-deforestation.</li>
                            </ul>
                            <div class="pt-2 mt-2 border-t border-slate-100 text-[10px] text-slate-400">© 2026 Bean Origin Physical Commodity Exchange Ltd. Registered under UCDA Coffee Charter.</div>
                        </div>
                    </div>
                </footer>
            </main>
        </div>

        <!-- Create Alert dialog -->
        <el-dialog v-model="alertOpen" title="Create Market Alert" width="420px" class="mi-dialog">
            <p class="text-xs text-slate-500 mb-3">Set customized automated signals for price thresholds, buyer demand, or regulatory developments.</p>
            <div class="mi-fields space-y-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Alert Type</label>
                    <el-select style="width:100%;" model-value="price">
                        <el-option label="Price Alert (Notify when price touches target)" value="price" />
                        <el-option label="Demand Alert (Notify when a matching RFQ appears)" value="demand" />
                        <el-option label="Market Shift Alert (Significant supply/demand change)" value="market" />
                        <el-option label="Regulatory Alert (EUDR or customs compliance changes)" value="eudr" />
                    </el-select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Commodity Specification</label>
                    <el-select style="width:100%;" model-value="Uganda Robusta (Screen 18)">
                        <el-option label="Uganda Robusta (Screen 18)" value="Uganda Robusta (Screen 18)" />
                        <el-option label="Uganda Robusta (Screen 15)" value="Uganda Robusta (Screen 15)" />
                        <el-option label="Bugisu Arabica (Grade AA)" value="Bugisu Arabica (Grade AA)" />
                        <el-option label="Rwenzori Arabica (Grade 1)" value="Rwenzori Arabica (Grade 1)" />
                    </el-select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Target Condition / Threshold</label>
                    <el-input model-value="4.35" type="number"><template #prepend>Price ≥</template><template #append>USD/kg</template></el-input>
                    <div class="text-[11px] text-slate-400 mt-1">Current spot is $4.20/kg. You will receive an instant notification & email.</div>
                </div>
            </div>
            <template #footer>
                <el-button @click="alertOpen = false">Cancel</el-button>
                <el-button class="mi-btn-primary" @click="saveAlert">Create Alert</el-button>
            </template>
        </el-dialog>

        <!-- Matching coffee dialog -->
        <el-dialog v-model="matchingOpen" width="600px" class="mi-dialog">
            <template #header>
                <div>
                    <div class="text-sm font-bold text-slate-950">Matching Coffee Lots: {{ matchingTitle }}</div>
                    <div class="text-xs text-slate-500">Physical lots on Bean Origin exchange meeting verified buyer parameters</div>
                </div>
            </template>
            <table class="w-full text-xs border-collapse">
                <thead><tr class="text-left text-[10px] font-mono uppercase text-slate-400 border-b border-slate-200 bg-slate-50"><th class="py-2">Lot ID</th><th class="py-2">Origin & Mill</th><th class="py-2">Grade</th><th class="py-2">Qty</th><th class="py-2">Price</th><th class="py-2 text-right">Action</th></tr></thead>
                <tbody>
                    <tr v-for="lot in matchingLots" :key="lot.id" class="border-b border-slate-100 last:border-b-0">
                        <td class="py-2 font-mono font-bold text-[#0d631b]">{{ lot.id }}</td>
                        <td class="py-2"><div class="font-bold text-slate-900">{{ lot.coffee }}</div><div class="text-[10px] text-slate-400">{{ lot.mill }}</div></td>
                        <td class="py-2"><span class="text-[10px] font-mono bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5">{{ lot.grade }}</span></td>
                        <td class="py-2 font-mono">{{ lot.qty }}</td>
                        <td class="py-2 font-mono font-bold text-slate-900">{{ lot.price }}</td>
                        <td class="py-2 text-right"><button type="button" class="text-[11px] font-semibold text-white bg-[#0d631b] hover:bg-[#0a4f15] rounded px-2.5 py-1" @click="ElMessage.info('Opening lot inspection (dummy preview).')">Inspect Lot</button></td>
                    </tr>
                </tbody>
            </table>
            <template #footer>
                <span class="text-[11px] font-mono text-slate-400 mr-auto">24h Settlement guarantee under Stanbic Bank Custody</span>
                <el-button @click="matchingOpen = false">Close</el-button>
            </template>
        </el-dialog>

        <!-- AI analyst dialog -->
        <el-dialog v-model="aiOpen" width="480px" class="mi-dialog">
            <template #header>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-[#0d631b] text-white flex items-center justify-center flex-shrink-0"><el-icon :size="13"><Cpu /></el-icon></div>
                    <div>
                        <div class="text-sm font-bold text-slate-950">Bean Origin AI Market Copilot</div>
                        <div class="text-[10px] font-mono text-slate-400">Grounded on 128 Platform Lots, 24 Active RFQs & Mombasa Export Logs</div>
                    </div>
                </div>
            </template>
            <div class="mi-chat">
                <div v-for="(msg, i) in chatMessages" :key="i" class="flex mb-3" :class="msg.from === 'user' ? 'justify-end' : 'justify-start gap-2'">
                    <div v-if="msg.from === 'ai'" class="w-7 h-7 rounded-full bg-[#0d631b] text-white flex items-center justify-center flex-shrink-0"><el-icon :size="12"><Cpu /></el-icon></div>
                    <div class="p-2.5 rounded-xl text-xs leading-relaxed max-w-[85%]" :class="msg.from === 'user' ? 'bg-slate-100 text-slate-900' : 'bg-white border border-slate-200 text-slate-800'">{{ msg.text }}</div>
                </div>
            </div>
            <div class="flex gap-2 mt-3 mi-fields">
                <el-input v-model="aiDraft" placeholder="Ask about prices, demand, matching lots, or export regulations..." @keydown.enter="sendChat" />
                <el-button class="mi-btn-primary flex-shrink-0" @click="sendChat"><el-icon :size="14"><Promotion /></el-icon></el-button>
            </div>
        </el-dialog>
    </OuterLayout>
</template>

<style scoped>
.mi-nav-link {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.4rem 1rem; font-size: 12.5px; font-weight: 500;
    color: #64748b; text-decoration: none; border-left: 3px solid transparent;
    transition: all 0.15s ease;
}
.mi-nav-link:hover { background: #f8fafc; color: #0f172a; }
.mi-nav-link--active { background: #f0fdf4; color: #0d631b; font-weight: 700; border-left-color: #0d631b; }

.mi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; }
.mi-card__head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.9rem; padding-bottom: 0.7rem; border-bottom: 1px solid #f1f5f9; }

.mi-badge-ai { display: inline-flex; align-items: center; gap: 4px; background: #ecfdf5; color: #0d631b; border: 1px solid #a7f3d0; font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 5px; }
.mi-badge-data { display: inline-flex; align-items: center; gap: 4px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 5px; }

.mi-mini-tag { font-size: 10px; font-weight: 700; font-family: ui-monospace, monospace; padding: 2px 6px; border-radius: 4px; flex-shrink: 0; white-space: nowrap; }
.mi-mini-tag--green { background: #e6f4ee; color: #0d631b; }
.mi-mini-tag--blue { background: #eff6ff; color: #1e40af; }
.mi-mini-tag--amber { background: #fef3c7; color: #b45309; }
.mi-mini-tag--slate { background: #f1f5f9; color: #475569; }
.mi-mini-tag--red { background: #fee2e2; color: #b91c1c; }

.mi-opportunity {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem;
    display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;
}
.mi-opportunity:hover { border-color: #94a3b8; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05); }

.mi-signal { border-left: 3px solid #0d631b; background: #fff; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 0.7rem 0.85rem; border-radius: 0 6px 6px 0; }
.mi-signal--blue { border-left-color: #2563eb; }
.mi-signal--amber { border-left-color: #d97706; }
.mi-signal-delta--green { color: #0d631b; }
.mi-signal-delta--blue { color: #2563eb; }
.mi-signal-delta--amber { color: #b45309; }
.mi-signal-delta--red { color: #b91c1c; }

.mi-origin { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem; background: #fff; transition: border-color 0.15s; }
.mi-origin--primary { border-color: #a7d9b0; background: #f0fdf4; }

.mi-transparency { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.75rem 1rem; font-size: 11.5px; color: #64748b; border-radius: 0 0 8px 8px; margin-left: -1.25rem; margin-right: -1.25rem; margin-bottom: -1.25rem; }

.mi-ai-cta { background: linear-gradient(135deg, #0d631b 0%, #0a4f15 100%); border-radius: 14px; padding: 1.5rem; }
.mi-ai-input {
    flex: 1; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.25); border-radius: 8px;
    padding: 0 10px; font-size: 12px; color: #fff; font-family: ui-monospace, monospace; outline: none;
}
.mi-ai-input::placeholder { color: rgba(255,255,255,0.5); }

.mi-chat { max-height: 340px; overflow-y: auto; padding: 0.5rem; background: #f8fafc; border-radius: 10px; }

/* Element Plus field overrides */
.mi-fields :deep(.el-input__wrapper),
.mi-fields :deep(.el-select__wrapper) {
    background: #ffffff; border-radius: 8px; box-shadow: 0 0 0 1px #e2e8f0 inset; padding: 1px 11px;
}
.mi-fields :deep(.el-input__wrapper.is-focus),
.mi-fields :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1px #0d631b inset; }
.mi-fields :deep(.el-input__inner),
.mi-fields :deep(.el-select__selected-item) { font-size: 12px; color: #0f172a; }
.mi-fields :deep(.el-input-group__prepend),
.mi-fields :deep(.el-input-group__append) { background: #f1f5f9; color: #64748b; font-size: 11px; box-shadow: 0 0 0 1px #e2e8f0 inset; }

:deep(.mi-btn-primary.el-button) { background: #0d631b; border-color: #0d631b; color: #fff; font-weight: 600; }
:deep(.mi-btn-primary.el-button:hover) { background: #0a4f15; border-color: #0a4f15; color: #fff; }
:deep(.mi-dialog.el-dialog) { border-radius: 14px; }
</style>
