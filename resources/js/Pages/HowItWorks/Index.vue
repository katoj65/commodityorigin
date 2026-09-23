<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    Compass, Connection, LocationFilled, Box, CollectionTag, Postcard, Shop, ShoppingCart, Sell,
    Operation, Document, Lock, Van, CircleCheckFilled, MapLocation, Medal, Link as LinkIcon, Cpu,
    ArrowRight, Right, TopRight, Check, WarningFilled, ChatDotRound, DataAnalysis,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

defineProps({
    steps: { type: Array, default: () => [] },
});

/* ── Sidebar navigation (dummy structure — mirrors the guide's own info architecture) ── */
const navGroups = [
    {
        category: 'Getting Started',
        links: [
            { id: 'overview', label: 'Overview', icon: Compass },
            { id: 'journey', label: 'The Bean Origin Journey', icon: Connection },
        ],
    },
    {
        category: 'Coffee Pipeline',
        links: [
            { id: 'step-1-add-coffee', label: 'Add Coffee', icon: LocationFilled },
            { id: 'step-2-batch', label: 'Farm Collection & Batch', icon: Box },
            { id: 'step-3-lot', label: 'Create a Lot', icon: CollectionTag },
            { id: 'step-4-product', label: 'Product Profile', icon: Postcard },
        ],
    },
    {
        category: 'Trading & Exchange',
        links: [
            { id: 'step-5-exchange', label: 'Enter Exchange', icon: Shop },
            { id: 'buying-journey', label: 'Buying Journey', icon: ShoppingCart },
            { id: 'selling-journey', label: 'Selling Journey', icon: Sell },
            { id: 'trading-methods', label: 'Trading Methods', icon: Operation },
        ],
    },
    {
        category: 'Post-Trade Execution',
        links: [
            { id: 'step-7-order', label: 'Order Creation', icon: Document },
            { id: 'step-8-payment', label: 'Escrow & Payment', icon: Lock },
            { id: 'step-9-fulfilment', label: 'Fulfilment & Logistics', icon: Van },
            { id: 'step-10-completed', label: 'Completed Trades', icon: CircleCheckFilled },
        ],
    },
    {
        category: 'Trust & Data Architecture',
        links: [
            { id: 'traceability', label: 'Traceability Model', icon: MapLocation },
            { id: 'verification', label: 'Verification & Compliance', icon: Medal },
            { id: 'trust-and-data', label: 'One Connected Record', icon: LinkIcon },
        ],
    },
    {
        category: 'AI Commerce',
        links: [{ id: 'ai-commerce', label: 'Trade With AI', icon: Cpu }],
    },
];
const sectionIds = navGroups.flatMap((g) => g.links.map((l) => l.id));

/* ── Journey map (10 custody milestones) ─────────────────────────────── */
const journeyNodes = [
    { id: 'step-1-add-coffee', label: '1. Farm', icon: LocationFilled },
    { id: 'step-1-add-coffee', label: '2. Collection', icon: Box },
    { id: 'step-2-batch', label: '3. Batch', icon: Box },
    { id: 'step-3-lot', label: '4. Lot', icon: CollectionTag },
    { id: 'step-4-product', label: '5. Product', icon: Postcard },
    { id: 'step-5-exchange', label: '6. Exchange', icon: Shop },
    { id: 'trading-methods', label: '7. Trade', icon: Operation },
    { id: 'step-8-payment', label: '8. Payment', icon: Lock },
    { id: 'step-9-fulfilment', label: '9. Fulfilment', icon: Van },
    { id: 'step-10-completed', label: '10. Delivery', icon: CircleCheckFilled, highlight: true },
];

/* ── Step 1 dummy content ───────────────────────────────────────────── */
const capturedParams = [
    'Farm Name & ID', 'Smallholder Producer', 'GPS Polygon Location', 'Collection Date',
    'Wet Weight (kg)', 'Brix Sugar Index', 'EUDR Deforestation Map', 'UCDA Collector License',
];

/* ── Step 2 dummy table ─────────────────────────────────────────────── */
const batchMetrics = [
    { metric: 'Source Collections', params: 'Links to all parent Farm Collection IDs', threshold: '100% Verified Geofence' },
    { metric: 'Processing Method', params: 'Fully Washed, Natural, Honey, Anaerobic Fermentation', threshold: 'Controlled Fermentation pH' },
    { metric: 'Drying & Moisture', params: 'African Raised Beds, Mechanical Dryers', threshold: '11.0% – 12.0% Target' },
    { metric: 'Grading & Storage', params: 'Gravity separator, color sorter, GrainPro jute bags', threshold: 'Bonded Dry Mill Jinja/Kampala' },
];

/* ── Step 6 trading methods ─────────────────────────────────────────── */
const tradingMethods = [
    { name: 'Direct Buy', tag: 'INSTANT', tone: 'green', body: 'Purchase export inventory immediately at the published spot FOB price under standard Incoterms.', cta: 'Explore Buy Spot' },
    { name: 'Make an Offer', tag: 'NEGOTIATE', tone: 'blue', body: 'Propose custom pricing, volume split, or container delivery timelines to the verified producer or exporter.', cta: 'Learn Counter-Offers' },
    { name: 'RFQ Sourcing', tag: 'REVERSE AUCTION', tone: 'amber', body: 'Post specific procurement needs (e.g. 100 MT Bugisu AA) and receive competitive proposals from accredited mills.', cta: 'Create Sourcing RFQ' },
    { name: 'Live Auction', tag: 'TIMED BIDDING', tone: 'red', body: 'Bid against institutional roasters for exclusive specialty microlots and cup-of-excellence award winners.', cta: 'Enter Auction Floor' },
];

/* ── Buying / selling journeys ──────────────────────────────────────── */
const buyingSteps = [
    { n: 1, title: 'Search & Filter', body: 'Browse real-time exchange inventories by origin, screen size, moisture, and cup score.' },
    { n: 2, title: 'Review Dossier', body: 'Inspect UCDA cupping reports, farm GPS polygons, moisture data, and seller ratings.' },
    { n: 3, title: 'Choose Trade Type', body: 'Buy immediately, submit a negotiated counter-offer, broadcast an RFQ, or enter an auction.' },
    { n: 4, title: 'Confirm Terms', body: 'Review Incoterms (FOB Mombasa, CIF Dubai, EXW Mill), container count, and delivery schedules.' },
    { n: 5, title: 'Secure Escrow Pay', body: 'Deposit purchase funds into protected Tier-1 Stanbic Bank custody accounts.' },
    { n: 6, title: 'Receive & Release', body: 'Track container rail and ocean shipping until port arrival and independent SGS signoff.' },
];
const sellingSteps = [
    { n: '1–2', title: 'Record & Batch', body: 'Register smallholder collections and aggregate into dry mill processing batches.' },
    { n: '3–4', title: 'Lot & Product', body: 'Grade screen sizes, verify moisture < 12.0%, and publish digital product passports.' },
    { n: '5–6', title: 'Publish & Trade', body: 'List to global buyers, accept binding escrow purchase orders or counter-offers.' },
    { n: '7', title: 'Stuff & Liquidate', body: 'Stuff containers in Jinja/Mombasa; receive automated payout upon Bill of Lading release.' },
];

/* ── Escrow lifecycle ───────────────────────────────────────────────── */
const escrowStages = [
    { n: 1, label: 'Pending', note: 'Awaiting buyer wire', tone: 'neutral' },
    { n: 2, label: 'In Escrow', note: 'Locked in Stanbic Custody', tone: 'amber' },
    { n: 3, label: 'Verified', note: 'SGS port inspection', tone: 'blue' },
    { n: 4, label: 'Paid Out', note: 'Released to producer', tone: 'green' },
];

/* ── Completed trade archive ────────────────────────────────────────── */
const archiveLeft = [
    { label: 'Signed Bill of Lading (B/L)', pill: 'PDF Archival' },
    { label: 'UCDA Export Certificate of Origin', pill: 'Verified' },
    { label: 'SGS Pre-Shipment Assay', pill: 'Assayed' },
];
const archiveRight = [
    { label: 'Stanbic Escrow Release Hash', pill: '0x89a...4b1' },
    { label: 'EUDR Polygon Geofence Dossier', pill: '100% Zero-Deforest' },
    { label: 'Participant Feedback & Rating', pill: '5.0 ★ Rated' },
];

/* ── Verification chips ─────────────────────────────────────────────── */
const verifyChips = ['Business Verified', 'Origin Verified', 'Quality Verified', 'Traceability Available', 'EUDR Deforestation Free'];

/* ── Scrollspy ───────────────────────────────────────────────────────── */
const activeSection = ref('overview');
let observer;

onMounted(() => {
    const targets = sectionIds
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activeSection.value = entry.target.id;
                }
            });
        },
        { rootMargin: '-96px 0px -70% 0px', threshold: 0 },
    );

    targets.forEach((el) => observer.observe(el));
});

onBeforeUnmount(() => {
    observer?.disconnect();
});
</script>

<template>
    <OuterLayout title="How Bean Origin Works">
        <div class="wp-guide bg-white min-h-screen">
            <div class="lg:flex">
                <!-- Sidebar -->
                <aside class="wp-guide-sidebar hidden lg:flex lg:flex-col sticky self-start left-0 w-[270px] flex-shrink-0 bg-white border-r border-[#e2e8e0] overflow-y-auto" style="top: 80px; max-height: calc(100vh - 80px);">
                    <div class="p-[20px] flex-1">
                        <nav>
                            <template v-for="group in navGroups" :key="group.category">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-[#94a3b8] mt-[20px] mb-1.5 px-2.5 first:mt-0">{{ group.category }}</div>
                                <a
                                    v-for="link in group.links" :key="link.id"
                                    :href="`#${link.id}`"
                                    class="wp-guide-nav-link"
                                    :class="{ 'wp-guide-nav-link--active': activeSection === link.id }"
                                >
                                    <el-icon :size="14"><component :is="link.icon" /></el-icon>
                                    {{ link.label }}
                                </a>
                            </template>
                        </nav>
                    </div>

                    <div class="p-[20px] pt-[12px] border-t border-[#e2e8e0]">
                        <Link :href="route('exchange-snapshot.index')" class="wp-guide-cta">
                            <el-icon :size="13"><TopRight /></el-icon> Open Live Exchange
                        </Link>
                    </div>
                </aside>

                <!-- Main content -->
                <main class="wp-guide-content w-full max-w-[1080px] px-4 md:px-12 py-8 md:py-10 pb-20">
                    <div class="flex items-center gap-2 mb-3 text-[#94a3b8] text-[13px]">
                        <span>Documentation</span><span>/</span><span class="text-[#181d17] font-medium">How Bean Origin Works</span>
                    </div>

                    <!-- OVERVIEW -->
                    <section id="overview" class="mb-12">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-[#e6f4ee] text-[#0d631b]">PRODUCT GUIDE</span>
                            <span class="text-[12px] text-[#94a3b8]">Updated for 2026 Harvest & EUDR Standards</span>
                        </div>
                        <h1 class="text-3xl font-bold text-[#181d17] mb-2">How Bean Origin Works</h1>
                        <p class="text-lg text-[#40493d] mb-4">A simple journey from physical coffee to global digital trade.</p>
                        <div class="bg-[#f7fbf0] rounded-xl p-4 text-sm leading-relaxed text-[#40493d]">
                            <strong class="text-[#181d17]">Bean Origin connects coffee supply with global demand.</strong> Coffee is recorded directly at its origin, aggregated and milled with scientific rigor, published as verified trading units on the digital Exchange, and escorted through automated escrow order, multi-tier payment, maritime fulfilment, and verified delivery.
                        </div>
                    </section>

                    <!-- JOURNEY MAP -->
                    <section id="journey" class="mb-12">
                        <div class="flex items-baseline justify-between mb-2 gap-2 flex-wrap">
                            <h4 class="text-lg font-bold text-[#181d17] m-0">The Bean Origin Journey</h4>
                            <span class="text-xs text-[#94a3b8]">Click any stage to jump to its specification</span>
                        </div>
                        <p class="text-sm text-[#6b7568] mb-3">Every bag of coffee flows sequentially through 10 verified custody milestones:</p>
                        <div class="wp-journey-scroll">
                            <template v-for="(node, i) in journeyNodes" :key="i">
                                <a :href="`#${node.id}`" class="wp-journey-node" :class="{ 'wp-journey-node--highlight': node.highlight }">
                                    <el-icon :size="18"><component :is="node.icon" /></el-icon>
                                    <span class="wp-journey-node__title">{{ node.label }}</span>
                                </a>
                                <el-icon v-if="i < journeyNodes.length - 1" :size="14" class="text-[#94a3b8] flex-shrink-0"><Right /></el-icon>
                            </template>
                        </div>
                    </section>

                    <!-- STEP 1 -->
                    <section id="step-1-add-coffee" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">1</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 1: Add Coffee & Farm Collection</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Record exactly where the coffee comes from. Producers and cooperatives record verified farm registries and intake cherry batches immediately upon harvest.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <div class="bg-[#f7fbf0] rounded-lg p-3">
                                <div class="text-xs font-semibold text-[#181d17] mb-2 flex items-center gap-1"><el-icon :size="13" class="text-[#0d631b]"><CircleCheckFilled /></el-icon> Captured Data Parameters</div>
                                <div class="flex flex-wrap gap-1.5">
                                    <span v-for="p in capturedParams" :key="p" class="wp-tag">{{ p }}</span>
                                </div>
                            </div>
                            <div class="bg-[#f7fbf0] rounded-lg p-3">
                                <div class="text-xs font-semibold text-[#181d17] mb-2">Purpose for Buyers & Roasters</div>
                                <p class="text-xs text-[#6b7568] m-0">Guarantees 100% smallholder provenance and zero-deforestation EUDR compliance before cherry milling begins. Every single bean is anchored to verified land parcels.</p>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <a href="#step-2-batch" class="wp-btn-guide-primary">Learn About Batches <el-icon :size="12"><ArrowRight /></el-icon></a>
                            <a href="#" class="wp-btn-guide-outline"><el-icon :size="12"><TopRight /></el-icon> Explore Farm Directory</a>
                        </div>
                    </section>

                    <!-- STEP 2 -->
                    <section id="step-2-batch" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">2</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 2: Build a Batch</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Combine and process collected cherries. Wet and dry mills aggregate multiple collections into a homogeneous operational batch, tracking milling transformations.</p>
                        <div class="border border-[#e2e8e0] rounded-lg overflow-x-auto mb-4">
                            <table class="w-full text-xs border-collapse min-w-[520px]">
                                <thead>
                                    <tr class="bg-[#f7fbf0]">
                                        <th class="text-left font-semibold text-[#181d17] px-3 py-2">Operational Metric</th>
                                        <th class="text-left font-semibold text-[#181d17] px-3 py-2">Recorded Parameters</th>
                                        <th class="text-left font-semibold text-[#181d17] px-3 py-2">Standard Threshold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in batchMetrics" :key="row.metric" class="border-b border-[#f1f5eb] last:border-b-0">
                                        <td class="px-3 py-2 font-semibold text-[#181d17]">{{ row.metric }}</td>
                                        <td class="px-3 py-2 text-[#6b7568]">{{ row.params }}</td>
                                        <td class="px-3 py-2 font-mono text-[#181d17]">{{ row.threshold }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <a href="#step-3-lot" class="wp-btn-guide-primary">Learn About Lots <el-icon :size="12"><ArrowRight /></el-icon></a>
                            <a href="#" class="wp-btn-guide-outline"><el-icon :size="12"><Document /></el-icon> View Sample Batch Record</a>
                        </div>
                    </section>

                    <!-- STEP 3 -->
                    <section id="step-3-lot" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">3</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 3: Create a Lot</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Turn processed coffee into a commercial trading unit. A Lot represents an export-grade container-ready volume with official cupping analysis and custody documentation.</p>
                        <div class="bg-[#f7fbf0] rounded-lg p-3 mb-4">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div><span class="block text-[11px] text-[#94a3b8]">Commercial Spec</span><span class="font-bold font-mono text-sm text-[#181d17]">Screen 18+ / AA</span></div>
                                <div><span class="block text-[11px] text-[#94a3b8]">Export Container</span><span class="font-bold font-mono text-sm text-[#181d17]">1 FCL (19,200 kg)</span></div>
                                <div><span class="block text-[11px] text-[#94a3b8]">SCAA Quality Score</span><span class="font-bold font-mono text-sm text-[#0d631b]">86.5 pts Specialty</span></div>
                                <div><span class="block text-[11px] text-[#94a3b8]">Custody Status</span><span class="font-bold font-mono text-sm text-[#1e40af]">Warehouse Receipt Ready</span></div>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <a href="#step-4-product" class="wp-btn-guide-primary">Explore Product Profiles <el-icon :size="12"><ArrowRight /></el-icon></a>
                            <a href="#" class="wp-btn-guide-outline"><el-icon :size="12"><CollectionTag /></el-icon> Lot Directory</a>
                        </div>
                    </section>

                    <!-- STEP 4 -->
                    <section id="step-4-product" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">4</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 4: Create a Product</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Present coffee cleanly to buyers. The Product Profile serves as the public-facing Digital Product Passport (DPP), summarizing commercial, sensory, and origin attributes without overwhelming technical jargon.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                            <div class="border border-[#e2e8e0] rounded-lg p-3 text-center">
                                <div class="text-sm font-semibold text-[#181d17]">Sensory Tasting Notes</div>
                                <div class="text-xs text-[#94a3b8] mt-1">Dark chocolate, blackcurrant, cane sugar</div>
                            </div>
                            <div class="border border-[#e2e8e0] rounded-lg p-3 text-center">
                                <div class="text-sm font-semibold text-[#181d17]">Spot & Forward Pricing</div>
                                <div class="text-xs text-[#94a3b8] mt-1">FOB Mombasa: $5.15/kg ($2.34/lb)</div>
                            </div>
                            <div class="border border-[#e2e8e0] rounded-lg p-3 text-center">
                                <div class="text-sm font-semibold text-[#181d17]">Verified Seller Trust</div>
                                <div class="text-xs text-[#94a3b8] mt-1">Uganda Coffee Traders · Stanbic Escrow</div>
                            </div>
                        </div>
                        <a href="#step-5-exchange" class="wp-btn-guide-primary">Enter The Exchange <el-icon :size="12"><ArrowRight /></el-icon></a>
                    </section>

                    <!-- STEP 5 -->
                    <section id="step-5-exchange" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">5</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 5: Enter the Exchange</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Bring coffee to the marketplace. Sellers publish verified lots into direct liquidity pools, where international roasters, traders, and importers discover and benchmark physical inventories.</p>
                        <div class="bg-[#f7fbf0] rounded-lg p-3 mb-4">
                            <div class="text-xs font-semibold text-[#181d17] mb-2">Search & Match Filters Available on Exchange</div>
                            <div class="flex flex-wrap gap-1.5 text-xs">
                                <span class="wp-tag wp-tag--white">Coffee Variety (Arabica / Robusta)</span>
                                <span class="wp-tag wp-tag--white">Origin Basin (Mt. Elgon, Rwenzori, West Nile)</span>
                                <span class="wp-tag wp-tag--white">Screen Size (15, 17, 18+)</span>
                                <span class="wp-tag wp-tag--white">Target FOB Price / kg</span>
                                <span class="wp-tag wp-tag--white">Verification (EUDR GPS, UCDA Cert)</span>
                            </div>
                        </div>
                        <a href="#trading-methods" class="wp-btn-guide-primary">View 4 Ways To Trade <el-icon :size="12"><ArrowRight /></el-icon></a>
                    </section>

                    <!-- STEP 6: TRADING METHODS -->
                    <section id="trading-methods" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">6</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 6: Choose How You Trade</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Bean Origin supports four institutional execution models tailored to commercial flexibility and pricing discovery:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div v-for="method in tradingMethods" :key="method.name" class="border border-[#e2e8e0] rounded-lg p-3 flex flex-col justify-between bg-white">
                                <div>
                                    <div class="flex items-center justify-between mb-2 gap-1">
                                        <span class="font-bold text-sm text-[#181d17]">{{ method.name }}</span>
                                        <span class="wp-method-tag" :class="`wp-method-tag--${method.tone}`">{{ method.tag }}</span>
                                    </div>
                                    <p class="text-xs text-[#6b7568] mb-3">{{ method.body }}</p>
                                </div>
                                <a href="#" class="wp-btn-guide-mini">{{ method.cta }}</a>
                            </div>
                        </div>
                    </section>

                    <!-- BUYING JOURNEY -->
                    <section id="buying-journey" class="wp-guide-card wp-guide-card--accent">
                        <h4 class="text-lg font-bold text-[#181d17] mb-1">How Buying Works: The Sourcing Journey</h4>
                        <p class="text-sm text-[#6b7568] mb-3">Six clear steps for roasters, commodity desks, and importers:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-3">
                            <div v-for="s in buyingSteps" :key="s.n" class="bg-white border border-[#e2e8e0] rounded-lg p-3">
                                <div class="text-xs font-bold text-[#181d17] mb-1 flex items-center gap-1.5"><span class="wp-mini-badge">{{ s.n }}</span> {{ s.title }}</div>
                                <p class="text-xs text-[#6b7568] m-0">{{ s.body }}</p>
                            </div>
                        </div>
                        <a href="#" class="wp-btn-guide-primary"><el-icon :size="12"><ShoppingCart /></el-icon> Start Sourcing on Exchange</a>
                    </section>

                    <!-- SELLING JOURNEY -->
                    <section id="selling-journey" class="wp-guide-card wp-guide-card--accent">
                        <h4 class="text-lg font-bold text-[#181d17] mb-1">How Selling Works: Producer & Exporter Flow</h4>
                        <p class="text-sm text-[#6b7568] mb-3">Direct international liquidation with zero payment default risks:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                            <div v-for="s in sellingSteps" :key="s.n" class="bg-white border border-[#e2e8e0] rounded-lg p-3">
                                <div class="text-xs font-bold text-[#181d17] mb-1 flex items-center gap-1.5"><span class="wp-mini-badge wp-mini-badge--dark">{{ s.n }}</span> {{ s.title }}</div>
                                <p class="text-xs text-[#6b7568] m-0">{{ s.body }}</p>
                            </div>
                        </div>
                        <a href="#" class="wp-btn-guide-dark"><el-icon :size="12"><ArrowRight /></el-icon> Register Coffee Inventory</a>
                    </section>

                    <!-- STEP 7 -->
                    <section id="step-7-order" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">7</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 7: Turn Trades Into Binding Orders</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Once an agreement is formed across any trading channel, Bean Origin automatically generates an immutable commercial contract and master trade order.</p>
                        <div class="rounded-lg p-3 bg-[#f7fbf0] mb-4">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 font-mono text-xs text-[#181d17]">
                                <div><strong>Contract ID:</strong> #ORD-2026-0941</div>
                                <div><strong>Buyer:</strong> Nordic Roasters ApS</div>
                                <div><strong>Seller:</strong> Bugisu High Altitude Coop</div>
                                <div><strong>Terms:</strong> FOB Mombasa Incoterms 2020</div>
                            </div>
                        </div>
                        <a href="#step-8-payment" class="wp-btn-guide-primary">Learn About Escrow Payment <el-icon :size="12"><ArrowRight /></el-icon></a>
                    </section>

                    <!-- STEP 8 -->
                    <section id="step-8-payment" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">8</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 8: Financial Settlement & Escrow</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Zero default risk for producers; zero non-delivery risk for international buyers. Capital is held in audited Tier-1 Stanbic Bank custody until delivery milestones are met.</p>
                        <div class="border border-[#e2e8e0] rounded-lg p-3 mb-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold text-[#181d17]">Settlement Lifecycle Status Indicator</span>
                                <span class="text-[11px] font-mono border border-[#e2e8e0] rounded px-2 py-0.5 text-[#6b7568]">Real-Time SLA: &lt; 24h Payout</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                                <div v-for="stage in escrowStages" :key="stage.n" class="wp-escrow-stage" :class="`wp-escrow-stage--${stage.tone}`">
                                    <div class="text-xs font-bold">{{ stage.n }}. {{ stage.label }}</div>
                                    <div class="text-[10px] opacity-80 mt-0.5">{{ stage.note }}</div>
                                </div>
                            </div>
                        </div>
                        <a href="#step-9-fulfilment" class="wp-btn-guide-primary">Explore Fulfilment <el-icon :size="12"><ArrowRight /></el-icon></a>
                    </section>

                    <!-- STEP 9 -->
                    <section id="step-9-fulfilment" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">9</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 9: Fulfilment & Trans-Continental Logistics</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Move coffee from East African dry mills via bonded rail corridors directly into ocean carriers at Port of Mombasa.</p>
                        <div class="rounded-lg p-3 bg-[#f7fbf0] mb-4">
                            <div class="text-xs font-semibold text-[#181d17] mb-2">Live Corridor Milestone Tracking</div>
                            <div class="h-1.5 rounded-full bg-[#e2e8e0] overflow-hidden mb-2">
                                <div class="h-full bg-[#0d631b] rounded-full" style="width: 75%;"></div>
                            </div>
                            <div class="flex flex-wrap justify-between gap-1 text-[11px] text-[#6b7568]">
                                <span>1. Mill Prep & Stuffing</span>
                                <span>2. Bonded Jinja Rail</span>
                                <span class="font-bold text-[#0d631b]">3. Mombasa Berth (Current)</span>
                                <span>4. Destination Delivery</span>
                            </div>
                        </div>
                        <a href="#step-10-completed" class="wp-btn-guide-primary">Completed Trade Records <el-icon :size="12"><ArrowRight /></el-icon></a>
                    </section>

                    <!-- STEP 10 -->
                    <section id="step-10-completed" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="wp-step-badge">10</span>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Step 10: Trade Completion & Permanent Master Ledger</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">When physical cargo discharge and financial releases conclude, the transaction is codified into the Bean Origin permanent archive with cryptographic proof.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <ul class="wp-archive-list">
                                <li v-for="item in archiveLeft" :key="item.label">
                                    <span><el-icon :size="13" class="text-[#0d631b]"><Check /></el-icon> {{ item.label }}</span>
                                    <span class="wp-code-pill">{{ item.pill }}</span>
                                </li>
                            </ul>
                            <ul class="wp-archive-list">
                                <li v-for="item in archiveRight" :key="item.label">
                                    <span><el-icon :size="13" class="text-[#0d631b]"><Check /></el-icon> {{ item.label }}</span>
                                    <span class="wp-code-pill">{{ item.pill }}</span>
                                </li>
                            </ul>
                        </div>
                        <a href="#" class="wp-btn-guide-outline"><el-icon :size="12"><Document /></el-icon> Access My Trades Ledger</a>
                    </section>

                    <!-- TRACEABILITY -->
                    <section id="traceability" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <el-icon :size="20" class="text-[#0d631b]"><MapLocation /></el-icon>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Follow the Coffee: How Traceability Works</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Bean Origin connects commercial coffee products directly back to the physical farms and trees behind them. Traceability is never retrofitted; it is established at the cherry scale.</p>
                        <div class="bg-[#f7fbf0] rounded-lg p-3 mb-3 flex items-center justify-center flex-wrap gap-2 font-mono text-xs font-semibold text-[#181d17]">
                            <span class="bg-white border border-[#e2e8e0] rounded px-2.5 py-1.5">Farm</span>
                            <el-icon :size="12" class="text-[#94a3b8]"><Right /></el-icon>
                            <span class="bg-white border border-[#e2e8e0] rounded px-2.5 py-1.5">Collection</span>
                            <el-icon :size="12" class="text-[#94a3b8]"><Right /></el-icon>
                            <span class="bg-white border border-[#e2e8e0] rounded px-2.5 py-1.5">Batch</span>
                            <el-icon :size="12" class="text-[#94a3b8]"><Right /></el-icon>
                            <span class="bg-white border border-[#e2e8e0] rounded px-2.5 py-1.5">Lot</span>
                            <el-icon :size="12" class="text-[#94a3b8]"><Right /></el-icon>
                            <span class="bg-white border border-[#e2e8e0] rounded px-2.5 py-1.5">Product</span>
                            <el-icon :size="12" class="text-[#94a3b8]"><Right /></el-icon>
                            <span class="bg-[#0d631b] text-white rounded px-2.5 py-1.5">Trade</span>
                        </div>
                        <p class="text-xs text-[#6b7568] m-0">Every buyer can scan the QR code on a coffee bag and immediately access the producer family interview, soil altitude, drying bed humidity logs, and satellite verification.</p>
                    </section>

                    <!-- VERIFICATION -->
                    <section id="verification" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <el-icon :size="20" class="text-[#1e40af]"><Medal /></el-icon>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Know Who You're Trading With: Verification Standards</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">To maintain institutional integrity, Bean Origin verifies credentials across participants, agricultural assets, and legal documents:</p>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span v-for="chip in verifyChips" :key="chip" class="wp-verify-chip"><el-icon :size="12"><CircleCheckFilled /></el-icon> {{ chip }}</span>
                        </div>
                        <p class="text-xs text-[#6b7568] m-0"><em>Note:</em> Bean Origin does not label any listing as verified unless underlying official certificates (e.g. UCDA license, phytosanitary certificates, GPS land deed) have been validated by compliance officers.</p>
                    </section>

                    <!-- TRUST & ONE CONNECTED RECORD -->
                    <section id="trust-and-data" class="wp-guide-card">
                        <h3 class="text-base font-bold text-[#181d17] mb-2">One Coffee Record, Connected Throughout the Journey</h3>
                        <p class="text-sm text-[#40493d] mb-3">Physical commodity integrity relies on a strict single-source-of-truth data model:</p>
                        <div class="rounded-lg p-3 bg-[#f7fbf0] mb-3">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                <div class="md:border-r border-[#e2e8e0] md:pr-3">
                                    <strong class="text-[#181d17]">Farm Collection:</strong>
                                    <div class="text-[#6b7568] mt-0.5">Creates initial ground-truth harvest inventory.</div>
                                </div>
                                <div class="md:border-r border-[#e2e8e0] md:pr-3">
                                    <strong class="text-[#181d17]">Batch & Lot:</strong>
                                    <div class="text-[#6b7568] mt-0.5">Records processing and reserves verified export volume.</div>
                                </div>
                                <div>
                                    <strong class="text-[#181d17]">Product & Trade:</strong>
                                    <div class="text-[#6b7568] mt-0.5">Liquidates exact lot volume, so double-selling is impossible.</div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-[#6b7568] m-0">Because every product profile strictly points to an unallocated physical Lot record, duplicate listings and phantom coffee sales are structurally impossible.</p>
                    </section>

                    <!-- AI COMMERCE -->
                    <section id="ai-commerce" class="wp-guide-card">
                        <div class="flex items-center gap-2 mb-3">
                            <el-icon :size="20" class="text-[#0d631b]"><Cpu /></el-icon>
                            <h3 class="text-base font-bold text-[#181d17] m-0">Trade With AI: A Conversational Sourcing Copilot</h3>
                        </div>
                        <p class="text-sm text-[#40493d] mb-3">Instead of manual navigation through nested filter tables, institutional buyers can execute complex natural-language sourcing directly through the Bean Origin AI Trading Copilot.</p>

                        <div class="rounded-lg p-3 bg-[#f7fbf0] mb-4">
                            <div class="mb-3 text-right">
                                <span class="block text-[11px] text-[#94a3b8] mb-1">Trader</span>
                                <div class="wp-ai-bubble wp-ai-bubble--user">"Find me 10 tonnes of Ugandan Robusta Screen 18+ under $4.10/kg FOB Mombasa with EUDR compliance."</div>
                            </div>
                            <div class="text-left">
                                <span class="block text-[11px] text-[#94a3b8] mb-1">Bean Origin AI</span>
                                <div class="wp-ai-bubble wp-ai-bubble--bot">
                                    <div class="flex items-center gap-1 font-semibold mb-1"><el-icon :size="13"><CircleCheckFilled /></el-icon> Found 2 verified export lots:</div>
                                    <ul class="text-xs my-1 pl-4 space-y-0.5">
                                        <li><strong>#LOT-000124 (Mukono Basin):</strong> 5,000 kg @ $3.95/kg · 82.0 pts</li>
                                        <li><strong>#LOT-000780 (Mubende Forest):</strong> 12,000 kg @ $3.85/kg · 80.5 pts</li>
                                    </ul>
                                    Would you like me to prepare an Escrow purchase contract for 10 MT of #LOT-000780?
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FINAL CTA -->
                    <section class="p-6 bg-[#f7fbf0] rounded-xl text-center my-10">
                        <h3 class="text-xl font-bold text-[#181d17] mb-2">Ready to Explore Bean Origin?</h3>
                        <p class="text-sm text-[#6b7568] max-w-lg mx-auto mb-4">Whether you are an international roaster seeking verified single origins or an East African producer preparing export lots, the digital exchange is open for trade.</p>
                        <div class="flex justify-center gap-2 flex-wrap">
                            <Link :href="route('exchange-snapshot.index')" class="wp-btn-guide-primary"><el-icon :size="12"><Shop /></el-icon> Explore Coffee Exchange</Link>
                            <Link :href="route('market.live')" class="wp-btn-guide-outline"><el-icon :size="12"><Operation /></el-icon> Start Trading</Link>
                            <a href="#ai-commerce" class="wp-btn-guide-ai"><el-icon :size="12"><Cpu /></el-icon> Try AI Commerce</a>
                        </div>
                    </section>

                    <footer class="pt-4 mt-6 border-t border-[#e2e8e0] flex justify-end items-center text-[#94a3b8] text-xs">
                        <a href="#overview" class="text-[#94a3b8] hover:text-[#0d631b] no-underline">Back to top ↑</a>
                    </footer>
                </main>
            </div>
        </div>
    </OuterLayout>
</template>

<style scoped>
.wp-guide-nav-link {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.45rem 0.65rem;
    font-size: 13px;
    font-weight: 500;
    color: #40493d;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.15s ease;
    line-height: 1.3;
}
.wp-guide-nav-link:hover { background: #f7fbf0; color: #0d631b; }
.wp-guide-nav-link--active { background: #e6f4ee; color: #0d631b; font-weight: 600; }

.wp-guide-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8e0;
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #181d17;
    text-decoration: none;
    transition: all 0.15s ease;
}
.wp-guide-cta:hover { background: #f7fbf0; border-color: #0d631b; color: #0d631b; }

.wp-guide-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.wp-guide-card--accent { background: #f7fbf0; }

.wp-step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #0d631b;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.wp-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    background: #ffffff;
    border: 1px solid #e2e8e0;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
    color: #40493d;
}
.wp-tag--white { background: #ffffff; }

.wp-btn-guide-primary,
.wp-btn-guide-outline,
.wp-btn-guide-dark,
.wp-btn-guide-ai {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 0.5rem 0.9rem;
    border-radius: 6px;
    text-decoration: none;
    border: 1px solid transparent;
    transition: all 0.15s ease;
}
.wp-btn-guide-primary { background: #0d631b; color: #ffffff; }
.wp-btn-guide-primary:hover { background: #0a4f15; color: #ffffff; }
.wp-btn-guide-outline { background: #ffffff; color: #40493d; border-color: #e2e8e0; }
.wp-btn-guide-outline:hover { background: #f7fbf0; border-color: #bfcaba; color: #181d17; }
.wp-btn-guide-dark { background: #181d17; color: #ffffff; }
.wp-btn-guide-dark:hover { background: #000000; color: #ffffff; }
.wp-btn-guide-ai { background: #ffffff; color: #0d631b; border-color: #a7d9b0; }
.wp-btn-guide-ai:hover { background: #e6f4ee; }

.wp-btn-guide-mini {
    display: block;
    text-align: center;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #181d17;
    border: 1px solid #e2e8e0;
    border-radius: 6px;
    padding: 0.4rem;
    text-decoration: none;
    transition: all 0.15s ease;
}
.wp-btn-guide-mini:hover { background: #f7fbf0; border-color: #bfcaba; }

.wp-method-tag {
    font-size: 10px;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    white-space: nowrap;
    flex-shrink: 0;
}
.wp-method-tag--green { background: #e6f4ee; color: #0d631b; }
.wp-method-tag--blue { background: #eff6ff; color: #1e40af; }
.wp-method-tag--amber { background: #fef3c7; color: #b45309; }
.wp-method-tag--red { background: #fee2e2; color: #b91c1c; }

.wp-mini-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 4px;
    background: #6b7568;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
}
.wp-mini-badge--dark { background: #181d17; }

.wp-escrow-stage {
    border-radius: 8px;
    padding: 0.6rem 0.4rem;
    background: #f7fbf0;
    border: 1px solid #e2e8e0;
    color: #40493d;
}
.wp-escrow-stage--amber { background: #fef3c7; border-color: #fde68a; color: #92400e; }
.wp-escrow-stage--blue { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
.wp-escrow-stage--green { background: #e6f4ee; border-color: #a7d9b0; color: #0d631b; }

.wp-archive-list {
    list-style: none;
    margin: 0;
    padding: 0;
    border: 1px solid #e2e8e0;
    border-radius: 8px;
    overflow: hidden;
}
.wp-archive-list li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.55rem 0.75rem;
    font-size: 0.8125rem;
    color: #181d17;
    border-bottom: 1px solid #f1f5eb;
}
.wp-archive-list li:last-child { border-bottom: none; }
.wp-archive-list li span:first-child { display: flex; align-items: center; gap: 0.4rem; }

.wp-code-pill {
    font-family: ui-monospace, monospace;
    font-size: 10.5px;
    background: #f1f5eb;
    color: #40493d;
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
    border: 1px solid #e2e8e0;
    flex-shrink: 0;
}

.wp-verify-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #e6f4ee;
    border: 1px solid #a7d9b0;
    color: #0d631b;
    font-size: 12px;
    font-weight: 600;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
}

.wp-ai-bubble {
    display: inline-block;
    max-width: 85%;
    padding: 0.7rem 0.95rem;
    font-size: 13px;
    line-height: 1.5;
}
.wp-ai-bubble--user {
    background: #eef0eb;
    border-radius: 12px 12px 2px 12px;
    color: #181d17;
    text-align: left;
}
.wp-ai-bubble--bot {
    background: #e6f4ee;
    border: 1px solid #a7d9b0;
    border-radius: 12px 12px 12px 2px;
    color: #0d631b;
}

.wp-journey-scroll {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    overflow-x: auto;
    padding: 1rem 0.75rem;
    background: #f7fbf0;
    border: 1px solid #e2e8e0;
    border-radius: 10px;
}
.wp-journey-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    background: #ffffff;
    border: 1px solid #e2e8e0;
    border-radius: 8px;
    padding: 0.6rem 0.85rem;
    min-width: 92px;
    text-align: center;
    text-decoration: none;
    color: #0d631b;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.wp-journey-node:hover { border-color: #0d631b; transform: translateY(-2px); }
.wp-journey-node--highlight { border-color: #0d631b; background: #e6f4ee; }
.wp-journey-node__title { font-size: 11px; font-weight: 600; white-space: nowrap; color: #181d17; }

@media (max-width: 1023.98px) {
    .wp-guide-content { padding-top: 1.5rem; }
}
</style>
