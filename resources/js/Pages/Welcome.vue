<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search } from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const props = defineProps({
    filterOptions: {
        type: Object,
        default: () => ({ species: [], processing: [], origins: [], grades: [], incoterms: [] }),
    },
});

/* ── Illustrative content — this is a public marketing landing page (no
   authenticated props exist for it), ported 1:1 from the approved
   institutional-exchange design file. Colors are reskinned onto the
   OuterLayout brand palette already used for the header/footer chrome
   (#121611 dark, #a3f69c accent, #0d631b link green) instead of the
   mockup's own emerald/obsidian palette, for site-wide consistency. ────── */
const heroMetrics = [
    { label: '24h Traded Volume', value: '142.8', unit: 'MT', note: '▲ +18.4% vs last week' },
    { label: 'Active Verified Lots', value: '312', unit: 'Lots', note: 'Export-ready inventory' },
    { label: 'Escrow Payout SLA', value: '< 24', unit: 'HRS', note: 'Stanbic Bank Tier-1 Custody' },
    { label: 'EUDR Compliance', value: '100%', unit: 'Polygon', note: 'Zero deforestation proof' },
];

const orderBookRows = [
    {
        dot: '#0d631b', title: 'Mt. Elgon Arabica (Washed)', sub: 'Bugisu High Altitude Coop',
        lot: '#LOT-UG-9412', grade: 'Screen 18+ AA', moisture: '11.2%', cup: '86.5 pts',
        vol: '19,200 kg', volNote: '(1 FCL)', incoterm: 'FOB Mombasa', price: '$5.15',
    },
    {
        dot: '#b45309', title: 'Mukono Fine Robusta (Natural)', sub: 'Kyagalanyi Dry Mill Lot',
        lot: '#LOT-UG-8830', grade: 'Screen 18 Extra', moisture: '11.8%', cup: '82.0 pts',
        vol: '38,400 kg', volNote: '(2 FCL)', incoterm: 'FOB Mombasa', price: '$3.95',
    },
    {
        dot: '#0d631b', title: 'Rwenzori Washed Arabica', sub: 'Kasese Snowpeaks Agronomy',
        lot: '#LOT-UG-7711', grade: 'Grade AB (Washed)', moisture: '11.4%', cup: '84.8 pts',
        vol: '9,600 kg', volNote: '(0.5 FCL)', incoterm: 'EXW Kampala Mill', price: '$4.70',
    },
    {
        dot: '#4338ca', title: 'Sidama Washed Specialty (Anaerobic)', sub: 'Bensa Origin Direct Export',
        lot: '#LOT-ET-3092', grade: 'Grade 1 Microlot', moisture: '10.9%', cup: '88.5 pts',
        vol: '4,800 kg', volNote: '(Specialty)', incoterm: 'FOB Djibouti', price: '$7.40',
    },
];

const dppMeta = [
    { label: 'GPS ORIGIN POLYGON', value: '1.042°N, 34.331°E', note: 'Mt. Elgon Basin (1,920m)' },
    { label: 'UCDA QUALITY CERT', value: '#UCDA-EXP-2025-44', note: 'Grade AA · Defect Score 0' },
    { label: 'MOISTURE & WATER ACT.', value: '11.2% / 0.54 aw', note: 'Dry Mill Inspected' },
    { label: 'HARVEST / MILL DATE', value: 'Main Crop 2024/25', note: 'Milled Nov 2024' },
    { label: 'WAREHOUSE LOCATION', value: 'Bolloré Jinja Dry Port', note: 'Customs Bonded WH #4' },
    { label: 'ESCROW RELEASE SLA', value: 'On Bill of Lading (BL)', note: 'Stanbic Bank Kampala' },
];

const stepperSteps = [
    { n: '01', title: 'Farm Polygon', note: 'Satellite GPS & EUDR zero-deforestation map' },
    { n: '02', title: 'Collection & Brix', note: 'Weight recording & cherry density log' },
    { n: '03', title: 'Dry Mill Batch', note: 'Hulling, screen sizing & gravimetric sorting' },
    { n: '04', title: 'UCDA Cupping', note: 'Official state sensory score & moisture cert' },
    { n: '05', title: 'Digital Title', note: 'Escrow allocation & exchange listing' },
    { n: '06', title: 'Port Release', note: 'Mombasa stuffing & Stanbic payment release' },
];

const corridors = [
    { n: '01', route: 'Mombasa → Jebel Ali (Dubai)', desc: 'Middle East Re-export Hub', transit: '9-11 Days Transit', rows: [['Carriers:', 'Maersk, CMA CGM'], ['Avg Freight:', '$1,450 / 20ft FCL'], ['Active Lots:', '84 Lots in transit']] },
    { n: '02', route: 'Mombasa → Hamburg / RTM', desc: 'Western Europe Roasting Hub', transit: '24-28 Days Transit', rows: [['Compliance:', 'EUDR Auto-Validated'], ['Avg Freight:', '$2,680 / 20ft FCL'], ['Active Lots:', '128 Lots contracted']] },
    { n: '03', route: 'Mombasa → Houston / NY', desc: 'North America Roasters', transit: '32-36 Days Transit', rows: [['Warehousing:', 'Continental, The Green'], ['Avg Freight:', '$3,400 / 20ft FCL'], ['Active Lots:', '62 Lots scheduled']] },
    { n: '04', route: 'Mombasa → Singapore / Busan', desc: 'Asia Pacific Fast-Growth Hub', transit: '18-21 Days Transit', rows: [['Demand Focus:', 'Fine Robusta & AA'], ['Avg Freight:', '$1,850 / 20ft FCL'], ['Active Lots:', '38 Lots in pipeline']] },
];

const trustBadges = ['UCDA Licensed', 'EUDR 100% Valid', 'Stanbic Escrow', 'SCAA Cupping'];

const aiLotMatches = [
    { id: '#LOT-UG-8830 (Mukono Basin)', price: '$3.95/kg FOB', note: 'Vol: 38,400 kg · Moisture: 11.8% · EUDR Polygons: 142 farms' },
    { id: '#LOT-UG-7104 (Mubende Forest)', price: '$4.05/kg FOB', note: 'Vol: 21,000 kg · Moisture: 11.4% · EUDR Polygons: 98 farms' },
];

/* Lot filter fields — options sourced from the real metadata tables
   (crop_variety_metadata, processing_metadata, origins_metadata,
   crop_grade_metadata, incoterm_metadata) via HomeController; no live
   search backend wired for this illustrative landing page yet. */
const speciesOptions = computed(() => ['All Coffee Types', ...props.filterOptions.species]);
const processingOptions = computed(() => ['All Processing', ...props.filterOptions.processing]);
const originOptions = computed(() => ['All Origins', ...props.filterOptions.origins]);
const gradeOptions = computed(() => ['Any Grade', ...props.filterOptions.grades]);
const incotermOptions = computed(() => ['Any Incoterm', ...props.filterOptions.incoterms]);

const filterKeyword = ref('');
const filterSpecies = ref(speciesOptions.value[0]);
const filterProcessing = ref(processingOptions.value[0]);
const filterOrigin = ref(originOptions.value[0]);
const filterGrade = ref(gradeOptions.value[0]);
const filterIncoterm = ref(incotermOptions.value[0]);
const aiChatDraft = ref('');
</script>

<template>
    <OuterLayout title="Bean Origin - The Digital Exchange for Physical Coffee">
        <div class="wp-exchange">
            <!-- HERO -->
            <section class="relative bg-[#121611] text-white pt-16 pb-24 md:pt-20 md:pb-28 overflow-hidden">
                <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[900px] h-[400px] bg-[#a3f69c]/10 rounded-full blur-3xl pointer-events-none" />
                <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-4xl mx-auto">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white mb-5 leading-[1.12]">
                            The Digital Exchange for <br class="hidden sm:inline" />
                            <span class="text-[#a3f69c]">Physical Coffee</span>
                        </h1>
                        <p class="text-base sm:text-lg text-[#bfcaba] max-w-2xl mx-auto mb-8 leading-relaxed">
                            Direct institutional trading, verified origin provenance, and escrow settlement connecting East African producers with global roasters and commodity desks.
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-3.5 mb-14">
                            <Link :href="route('register')" class="inline-flex items-center gap-2 bg-[#a3f69c] hover:bg-[#88d982] text-[#002204] font-bold px-6 py-3 rounded-lg text-sm no-underline transition-all">
                                <span>Explore Live Exchange</span>
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </Link>
                            <Link :href="route('login')" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/15 text-white border border-white/20 font-semibold px-6 py-3 rounded-lg text-sm no-underline transition-all">
                                <span class="material-symbols-outlined text-[16px] text-[#a3f69c]">terminal</span>
                                <span>Launch Trading Terminal</span>
                            </Link>
                            <a href="#custody" class="inline-flex items-center gap-2 text-[#bfcaba] hover:text-white px-4 py-3 text-sm font-medium no-underline">
                                <span class="material-symbols-outlined text-[16px] text-[#a3f69c]">verified_user</span>
                                <span>Escrow &amp; Compliance Guarantee</span>
                            </a>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-left max-w-4xl mx-auto bg-black/30 border border-white/10 rounded-xl p-4 sm:p-5">
                            <div v-for="(m, i) in heroMetrics" :key="m.label" class="pl-0 md:pl-2" :class="i < 3 ? 'border-r border-white/10 pr-3' : ''">
                                <div class="text-[11px] font-mono text-[#8a9384] uppercase">{{ m.label }}</div>
                                <div class="text-2xl font-bold font-mono text-white flex items-baseline gap-1.5 mt-0.5" :class="{ 'text-[#a3f69c]': i === 3 }">
                                    {{ m.value }} <span class="text-xs font-normal" :class="i === 3 ? 'text-[#bfcaba]' : 'text-[#a3f69c]'">{{ m.unit }}</span>
                                </div>
                                <div class="text-[11px] font-mono mt-0.5" :class="i === 1 ? 'text-[#8a9384]' : 'text-[#a3f69c]/90'">{{ m.note }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SEARCH & LOT MATCHER -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-9 relative z-20">
                <div class="bg-white rounded-xl border border-[#e2e8e0] shadow-xl shadow-black/5 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2 text-xs font-bold text-[#181d17] uppercase tracking-wide">
                            <span class="material-symbols-outlined text-[16px] text-[#0d631b]">filter_alt</span>
                            <span>Institutional Lot Filter &amp; Spot Matcher</span>
                        </div>
                        <div class="text-xs font-mono text-[#6b7568]">
                            Matched: <span class="font-bold text-[#0d631b]">312 export Lots</span> across 4 active African corridors
                        </div>
                    </div>
                    <div class="flex flex-nowrap items-center gap-2 overflow-x-auto wp-lot-filter">
                        <el-input v-model="filterKeyword" placeholder="Search keywords, exporter, Lot #..." class="wp-lot-filter__input">
                            <template #prefix>
                                <el-icon :size="14" class="text-slate-400"><Search /></el-icon>
                            </template>
                        </el-input>
                        <el-select v-model="filterSpecies" placeholder="Coffee Type" class="wp-lot-filter__select">
                            <el-option v-for="opt in speciesOptions" :key="opt" :label="opt" :value="opt" />
                        </el-select>
                        <el-select v-model="filterProcessing" placeholder="Processing" class="wp-lot-filter__select">
                            <el-option v-for="opt in processingOptions" :key="opt" :label="opt" :value="opt" />
                        </el-select>
                        <el-select v-model="filterOrigin" placeholder="Origin" class="wp-lot-filter__select">
                            <el-option v-for="opt in originOptions" :key="opt" :label="opt" :value="opt" />
                        </el-select>
                        <el-select v-model="filterGrade" placeholder="Grade" class="wp-lot-filter__select">
                            <el-option v-for="opt in gradeOptions" :key="opt" :label="opt" :value="opt" />
                        </el-select>
                        <el-select v-model="filterIncoterm" placeholder="Incoterm" class="wp-lot-filter__select">
                            <el-option v-for="opt in incotermOptions" :key="opt" :label="opt" :value="opt" />
                        </el-select>
                        <el-button class="wp-lot-filter__btn" :icon="Search">Filter</el-button>
                    </div>
                </div>
            </section>

            <!-- LIVE ORDER BOOK -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" id="orderbook">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-5">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#181d17]">Live Institutional Order Book</h2>
                        <p class="text-sm text-[#6b7568] mt-0.5">Physical coffee lots available for instantaneous forward contracting, counter-offers, or spot settlement.</p>
                    </div>
                    <div class="mt-3 md:mt-0 flex items-center gap-2">
                        <span class="text-xs font-mono text-slate-400">Market Spread: <strong>$0.04/kg</strong></span>
                        <button type="button" class="text-xs font-mono bg-white border border-slate-300 text-slate-700 px-3 py-1.5 rounded-md hover:bg-slate-50 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">refresh</span> Auto-refresh (3s)
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-[#e2e8e0] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-mono text-[11px] uppercase tracking-wider">
                                    <th class="py-3 px-4">Type / Origin</th>
                                    <th class="py-3 px-3">Lot ID</th>
                                    <th class="py-3 px-3">Grade &amp; Screen</th>
                                    <th class="py-3 px-3">Moisture</th>
                                    <th class="py-3 px-3">SCAA Cup</th>
                                    <th class="py-3 px-3">Available Vol</th>
                                    <th class="py-3 px-3">Incoterm</th>
                                    <th class="py-3 px-3 text-right">Spot Ask ($/kg)</th>
                                    <th class="py-3 px-3 text-center">Escrow Status</th>
                                    <th class="py-3 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="row in orderBookRows" :key="row.lot" class="hover:bg-[#f4f9f0] transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-[#181d17] flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ background: row.dot }" />
                                            {{ row.title }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">{{ row.sub }}</div>
                                    </td>
                                    <td class="py-3 px-3 font-mono text-slate-600 font-medium">{{ row.lot }}</td>
                                    <td class="py-3 px-3"><span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 font-mono text-[11px] text-slate-700">{{ row.grade }}</span></td>
                                    <td class="py-3 px-3 font-mono text-slate-600">{{ row.moisture }}</td>
                                    <td class="py-3 px-3 font-mono font-bold text-[#0d631b]">{{ row.cup }}</td>
                                    <td class="py-3 px-3 font-mono font-semibold text-slate-800">{{ row.vol }} <span class="text-[10px] text-slate-400 font-normal">{{ row.volNote }}</span></td>
                                    <td class="py-3 px-3 font-mono text-slate-600">{{ row.incoterm }}</td>
                                    <td class="py-3 px-3 font-mono font-bold text-[#181d17] text-right text-sm">{{ row.price }}</td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono bg-[#e9f6e3] text-[#0d631b] font-medium">
                                            <span class="material-symbols-outlined text-[12px]">verified</span> Stanbic Verified
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" class="bg-[#e9f6e3] text-[#0d631b] hover:bg-[#d9efce] font-bold px-2.5 py-1 rounded text-xs transition-colors">Bid</button>
                                            <button type="button" class="bg-[#121611] hover:bg-[#232b1f] text-white font-bold px-2.5 py-1 rounded text-xs transition-colors">Buy Spot</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs font-mono text-slate-500">
                        <div>Total spot liquidity represented: <strong class="text-slate-800">72,000 kg ($328,400 USD)</strong></div>
                        <Link :href="route('market.live')" class="text-[#0d631b] font-bold hover:underline flex items-center gap-1 no-underline">
                            Open Full 312 Lot Order Depth <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- PROVENANCE & CHAIN OF CUSTODY -->
            <section class="bg-white border-y border-[#e2e8e0] py-16" id="custody">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-14">
                        <h2 class="text-3xl font-extrabold text-[#181d17]">A Marketplace Built Around Real Physical Coffee</h2>
                        <p class="text-[#6b7568] mt-2 text-sm leading-relaxed">
                            Every commercial contract is cryptographically and legally tethered to the physical batch: GPS agronomy polygon, moisture sensor logs, official UCDA cupping certification, and bill of lading custody.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-16 bg-[#f7fbf0] rounded-2xl p-6 sm:p-8">
                        <div class="lg:col-span-5 relative">
                            <div class="rounded-xl overflow-hidden border border-[#e2e8e0] relative aspect-[4/3] bg-[#121611] flex items-center justify-center">
                                <span class="material-symbols-outlined text-[64px] text-[#a3f69c]/40">grain</span>
                                <div class="absolute top-3 left-3 bg-[#121611]/90 text-white font-mono text-[10px] font-bold px-2.5 py-1 rounded flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[12px] text-[#a3f69c]">verified</span>
                                    PHYSICAL BATCH VERIFIED
                                </div>
                                <div class="absolute bottom-3 right-3 bg-black/75 text-white font-mono text-[11px] px-2.5 py-1 rounded">
                                    Weight: 60kg Export Bag
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-7">
                            <div class="bg-white border border-[#e2e8e0] rounded-xl p-6">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                    <div>
                                        <span class="font-mono text-[10px] text-slate-400 uppercase">Digital Product Passport (DPP)</span>
                                        <h3 class="text-lg font-bold text-[#181d17]">Uganda Bugisu Grade AA · Lot #LOT-UG-9412</h3>
                                    </div>
                                    <span class="px-2.5 py-1 rounded bg-[#e9f6e3] text-[#0d631b] border border-[#0d631b]/20 font-mono text-xs font-bold whitespace-nowrap">
                                        ✓ 100% EUDR Compliant
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs mb-4">
                                    <div v-for="item in dppMeta" :key="item.label" class="bg-slate-50 p-2.5 rounded border border-slate-100">
                                        <span class="text-slate-400 text-[10px] font-mono block">{{ item.label }}</span>
                                        <span class="font-bold text-slate-800 font-mono">{{ item.value }}</span>
                                        <span class="text-[10px] text-slate-500 block">{{ item.note }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-100 text-xs">
                                    <span class="text-slate-500 font-mono">Audit Hash: <code class="text-slate-700">0x7f2a...8c1e</code></span>
                                    <a href="#" class="text-[#0d631b] font-bold hover:underline inline-flex items-center gap-1 no-underline">
                                        <span class="material-symbols-outlined text-[14px]">description</span> Download Complete Traceability Dossier
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div v-for="(step, i) in stepperSteps" :key="step.n" class="rounded-xl p-4 text-center transition-all" :class="i === 5 ? 'bg-[#121611] text-white' : 'bg-slate-50 border border-slate-200 hover:border-[#0d631b]/40'">
                            <div class="w-9 h-9 rounded-full font-mono font-bold text-sm flex items-center justify-center mx-auto mb-2" :class="i === 5 ? 'bg-[#a3f69c] text-[#002204]' : 'bg-white border border-[#0d631b] text-[#0d631b]'">{{ step.n }}</div>
                            <div class="font-bold text-xs" :class="i === 5 ? 'text-[#a3f69c]' : 'text-[#181d17]'">{{ step.title }}</div>
                            <div class="text-[11px] mt-1" :class="i === 5 ? 'text-white/70' : 'text-slate-500'">{{ step.note }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- DUAL TERMINALS -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16" id="dual-terminals">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#181d17]">Institutional Desks for Both Sides of Trade</h2>
                    <p class="text-sm text-[#6b7568] mt-1">Tailored interfaces and risk controls for roasters, commodity desks, and origin suppliers.</p>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white border border-[#e2e8e0] rounded-2xl p-7 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                            <div class="inline-flex items-center gap-2 text-xs font-mono font-bold text-[#0d631b] uppercase tracking-wide bg-[#e9f6e3] px-2.5 py-1 rounded">
                                <span class="material-symbols-outlined text-[14px]">shopping_cart_checkout</span> Buyer Sourcing Suite
                            </div>
                            <span class="text-xs font-mono text-slate-400">For Roasters &amp; Trade Desks</span>
                        </div>
                        <h3 class="text-xl font-bold text-[#181d17] mb-2">Algorithmic Coffee Procurement</h3>
                        <p class="text-sm text-[#6b7568] mb-5 leading-relaxed">
                            Access high-grade spot supplies and forward container commitments with direct digital contracts, moisture warranties, and automated letter of credit integration.
                        </p>
                        <div class="space-y-2.5 mb-6">
                            <div class="flex items-start gap-2 text-xs text-slate-700">
                                <span class="material-symbols-outlined text-[16px] text-[#0d631b] mt-0.5">check_circle</span>
                                <span>Broadcast a spec (say, 100 MT Bugisu Grade AA) as an RFQ or reverse auction straight to verified exporters.</span>
                            </div>
                            <div class="flex items-start gap-2 text-xs text-slate-700">
                                <span class="material-symbols-outlined text-[16px] text-[#0d631b] mt-0.5">check_circle</span>
                                <span>Your capital sits in Stanbic Bank escrow and only releases once the container passes SGS inspection at Mombasa.</span>
                            </div>
                            <div class="flex items-start gap-2 text-xs text-slate-700">
                                <span class="material-symbols-outlined text-[16px] text-[#0d631b] mt-0.5">check_circle</span>
                                <span>Pull a polygon export in one click, formatted for the EU TRACES database.</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-slate-100">
                            <Link :href="route('register')" class="bg-[#121611] hover:bg-[#232b1f] text-white text-xs font-bold px-4 py-2.5 rounded-lg no-underline">Launch Sourcing Desk</Link>
                            <a href="#rfq" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-lg no-underline">Publish Buyer RFQ</a>
                        </div>
                    </div>

                    <div class="bg-white border border-[#e2e8e0] rounded-2xl p-7 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                            <div class="inline-flex items-center gap-2 text-xs font-mono font-bold text-amber-800 uppercase tracking-wide bg-amber-50 px-2.5 py-1 rounded">
                                <span class="material-symbols-outlined text-[14px]">domain</span> Producer &amp; Exporter Suite
                            </div>
                            <span class="text-xs font-mono text-slate-400">For Mills, Coops &amp; Shippers</span>
                        </div>
                        <h3 class="text-xl font-bold text-[#181d17] mb-2">Direct International Liquidation</h3>
                        <p class="text-sm text-[#6b7568] mb-5 leading-relaxed">
                            Convert warehouse receipt inventory into global cash contracts. Bypass traditional opaque broker cascades and retain full commercial margin.
                        </p>
                        <div class="space-y-2.5 mb-6">
                            <div class="flex items-start gap-2 text-xs text-slate-700">
                                <span class="material-symbols-outlined text-[16px] text-amber-600 mt-0.5">check_circle</span>
                                <span>Upload harvest logs, milling moisture data, and UCDA certificates, and a lot is digitized in minutes.</span>
                            </div>
                            <div class="flex items-start gap-2 text-xs text-slate-700">
                                <span class="material-symbols-outlined text-[16px] text-amber-600 mt-0.5">check_circle</span>
                                <span>Funded escrow accounts guarantee the trade, with wire settlement triggered automatically on bill of lading.</span>
                            </div>
                            <div class="flex items-start gap-2 text-xs text-slate-700">
                                <span class="material-symbols-outlined text-[16px] text-amber-600 mt-0.5">check_circle</span>
                                <span>Lock a forward contract with international roasters before milling is even complete.</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-slate-100">
                            <Link :href="route('register')" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-4 py-2.5 rounded-lg no-underline">Onboard Coffee Inventory</Link>
                            <a href="#seller-guide" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-lg no-underline">Exporter Requirements</a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TRADE CORRIDORS -->
            <section class="bg-[#121611] text-white py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Trans-Continental Trade Corridors</h2>
                            <p class="text-sm text-[#8a9384] mt-1">Connecting East African dry mills via bonded rail and road to Port of Mombasa for scheduled global ocean departures.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded bg-white/5 border border-white/10 font-mono text-xs text-[#bfcaba] whitespace-nowrap">
                            <span class="material-symbols-outlined text-[14px] text-[#a3f69c]">anchor</span> Port of Exit: Mombasa (KE-MBA)
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div v-for="c in corridors" :key="c.n" class="bg-white/5 border border-white/10 rounded-xl p-5 hover:border-[#a3f69c]/50 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-mono font-bold text-[#a3f69c]">CORRIDOR {{ c.n }}</span>
                                <span class="text-[10px] font-mono bg-black/30 text-[#a3f69c] border border-[#a3f69c]/20 px-2 py-0.5 rounded whitespace-nowrap">{{ c.transit }}</span>
                            </div>
                            <div class="text-lg font-bold text-white mb-1">{{ c.route }}</div>
                            <div class="text-xs text-[#8a9384] mb-3">{{ c.desc }}</div>
                            <div class="border-t border-white/10 pt-3 text-[11px] font-mono text-[#bfcaba] space-y-1">
                                <div v-for="pair in c.rows" :key="pair[0]" class="flex justify-between gap-2">
                                    <span>{{ pair[0] }}</span> <span class="text-white text-right">{{ pair[1] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- AI AGENTIC COMMERCE -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16" id="ai-terminal">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-6">
                        <h2 class="text-3xl font-extrabold text-[#181d17]">Trade with Bean Origin AI</h2>
                        <p class="text-[#6b7568] mt-3 text-sm leading-relaxed">
                            Type plain-English trading requirements. The exchange agent parses real physical inventories, evaluates moisture and cupping dossiers, constructs formal counter-offers, and provisions escrow contracts.
                        </p>
                        <div class="mt-6 space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded bg-[#e9f6e3] text-[#0d631b] flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[15px]">format_quote</span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-[#181d17]">Natural Language Procurement Query</div>
                                    <div class="text-xs text-slate-500">"Match me 20 MT Screen 18 Robusta under $4.00 FOB Mombasa for Q1 shipment."</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded bg-[#e9f6e3] text-[#0d631b] flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[15px]">task_alt</span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-[#181d17]">Guaranteed Human-In-The-Loop Signoff</div>
                                    <div class="text-xs text-slate-500">AI drafts the formal escrow and purchase offer, requiring cryptographic confirmation before any financial movement.</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 pt-6 border-t border-slate-200">
                            <div class="text-[11px] font-mono text-slate-400 uppercase mb-3">Institutional Regulatory Credentials</div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center font-mono text-[11px]">
                                <div v-for="badge in trustBadges" :key="badge" class="bg-slate-100 p-2 rounded border border-slate-200 text-slate-700 font-semibold">{{ badge }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-6">
                        <div class="bg-white border border-[#e2e8e0] rounded-2xl shadow-xl overflow-hidden">
                            <div class="bg-[#121611] px-4 py-3 text-white flex items-center justify-between text-xs font-mono border-b border-white/10">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#a3f69c]" />
                                    <span class="font-bold">Bean Origin AI Copilot · Session #TR-881</span>
                                </div>
                                <span class="text-[#8a9384] text-[10px]">Connected to Exchange Engine</span>
                            </div>
                            <div class="p-5 space-y-4 text-xs bg-slate-50/50">
                                <div class="flex justify-end">
                                    <div class="bg-[#121611] text-white p-3 rounded-2xl rounded-tr-none max-w-[85%]">
                                        "Find me 20 tonnes of Ugandan Screen 18 Robusta under $4.10/kg FOB Mombasa with EUDR geolocation ready."
                                    </div>
                                </div>
                                <div class="flex justify-start">
                                    <div class="bg-white border border-slate-200 text-slate-800 p-3.5 rounded-2xl rounded-tl-none max-w-[90%] space-y-2">
                                        <div class="font-semibold text-[#0d631b] flex items-center gap-1.5 font-mono text-[11px]">
                                            <span class="material-symbols-outlined text-[14px]">bolt</span> Matched 2 Verified Exchange Lots:
                                        </div>
                                        <div v-for="lot in aiLotMatches" :key="lot.id" class="bg-slate-50 p-2.5 rounded border border-slate-200 font-mono text-[11px] space-y-1">
                                            <div class="flex justify-between font-bold text-slate-900 gap-2">
                                                <span>{{ lot.id }}</span>
                                                <span class="text-[#0d631b]">{{ lot.price }}</span>
                                            </div>
                                            <div class="text-slate-500 text-[10px]">{{ lot.note }}</div>
                                        </div>
                                        <div class="text-slate-600 text-xs">
                                            Would you like me to draft a binding purchase bid on #LOT-UG-8830 at $3.95/kg ($76,800 total) into Stanbic escrow?
                                        </div>
                                        <div class="flex items-center gap-2 pt-1 font-mono text-[10px]">
                                            <button type="button" class="bg-[#0d631b] text-white px-3 py-1.5 rounded font-bold hover:bg-[#0a4f15] transition-colors">Draft Escrow Offer</button>
                                            <button type="button" class="bg-slate-100 text-slate-700 px-3 py-1.5 rounded border border-slate-200 hover:bg-slate-200">Compare Cupping Scores</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 bg-white border-t border-slate-200 flex items-center gap-2 wp-filter-fields">
                                <el-input v-model="aiChatDraft" placeholder="Instruct AI: e.g. Prepare FOB Mombasa contract..." class="flex-1" />
                                <button type="button" class="w-8 h-8 rounded-lg bg-[#121611] text-white flex items-center justify-center hover:bg-[#232b1f] flex-shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- INSTITUTIONAL CTA -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
                <div class="bg-[#121611] rounded-2xl p-8 sm:p-12 text-white text-center relative overflow-hidden">
                    <div class="relative z-10 max-w-2xl mx-auto">
                        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Ready to Trade Physical Coffee on Digital Rails?</h2>
                        <p class="text-[#bfcaba] text-sm sm:text-base mt-3 mb-8">
                            Join verified roasters, commodity funds, and East African producer cooperatives settling commercial contracts transparently.
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <Link :href="route('register')" class="bg-[#a3f69c] hover:bg-[#88d982] text-[#002204] font-bold text-xs px-6 py-3 rounded-lg no-underline transition-all">Open Roaster / Buyer Account</Link>
                            <Link :href="route('register')" class="bg-white/10 hover:bg-white/20 text-white border border-white/30 font-semibold text-xs px-6 py-3 rounded-lg no-underline transition-all">Apply as Verified Exporter / Dry Mill</Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </OuterLayout>
</template>

<style scoped>
/* Element Plus field overrides — match the page's slate/green Tailwind
   look (small text, slate-50 fill, green focus) instead of ElementPlus's
   own default theme. */
.wp-filter-fields :deep(.el-input__wrapper),
.wp-filter-fields :deep(.el-select__wrapper) {
    background: #f8fafc;
    border-radius: 6px;
    box-shadow: 0 0 0 1px #e2e8f0 inset;
    padding: 1px 11px;
    height: 34px;
    box-sizing: border-box;
}
.wp-filter-fields :deep(.el-select) {
    height: 34px;
}
.wp-filter-fields :deep(.el-input__wrapper.is-focus),
.wp-filter-fields :deep(.el-select__wrapper.is-focused) {
    background: #ffffff;
    box-shadow: 0 0 0 1px #0d631b inset;
}
.wp-filter-fields :deep(.el-input__inner),
.wp-filter-fields :deep(.el-select__selected-item) {
    font-size: 12px;
    color: #0f172a;
}
.wp-filter-fields :deep(.el-input__inner::placeholder) {
    color: #94a3b8;
}

/* Institutional Lot Filter — single-line, compact row: every field and
   the button are pinned to the exact same 36px medium-sized box (height
   + line-height + paddings), so nothing looks taller or shorter. */
.wp-lot-filter {
    padding-bottom: 2px;
}
.wp-lot-filter__input {
    flex: 1 1 240px;
    min-width: 180px;
}
.wp-lot-filter__select {
    flex: 0 0 148px;
    width: 148px;
}
.wp-lot-filter :deep(.el-input),
.wp-lot-filter :deep(.el-select) {
    height: 36px !important;
}
.wp-lot-filter :deep(.el-input__wrapper),
.wp-lot-filter :deep(.el-select__wrapper) {
    background: #f8fafc;
    border-radius: 6px;
    box-shadow: 0 0 0 1px #e2e8f0 inset;
    height: 36px !important;
    min-height: 36px !important;
    line-height: 36px !important;
    padding: 0 12px !important;
    box-sizing: border-box;
}
.wp-lot-filter :deep(.el-select__selection) {
    height: 34px !important;
}
.wp-lot-filter :deep(.el-input__wrapper.is-focus),
.wp-lot-filter :deep(.el-select__wrapper.is-focused) {
    background: #ffffff;
    box-shadow: 0 0 0 1px #0d631b inset;
}
.wp-lot-filter :deep(.el-input__inner),
.wp-lot-filter :deep(.el-select__selected-item),
.wp-lot-filter :deep(.el-select__placeholder) {
    height: 34px !important;
    line-height: 34px !important;
    font-size: 13px !important;
    color: #0f172a;
}
.wp-lot-filter :deep(.el-input__prefix) {
    margin-right: 4px;
}
.wp-lot-filter :deep(.el-input__inner::placeholder) {
    color: #94a3b8;
    font-size: 13px !important;
}
.wp-lot-filter__btn.el-button {
    flex-shrink: 0;
    height: 36px !important;
    min-height: 36px !important;
    line-height: 36px !important;
    margin: 0;
    border: none;
    border-radius: 6px;
    background: #0d631b;
    color: #ffffff;
    font-size: 13px !important;
    font-weight: 700;
    padding: 0 18px !important;
    box-sizing: border-box;
}
.wp-lot-filter__btn.el-button:hover,
.wp-lot-filter__btn.el-button:focus {
    background: #0a4f15;
    color: #ffffff;
}
</style>
