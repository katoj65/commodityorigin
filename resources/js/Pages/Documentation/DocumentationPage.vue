<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

/* ── Structural/visual port of the uploaded "Bean Origin Documentation"
   knowledge-hub mockup (code.html), restyled with a page-scoped token
   block (--dc-*) mapped 1:1 from the mockup's own "Scientific Atelier"
   palette (Deep Emerald / Roasted Umber) rather than the app's own
   --dp-* theme, which is a different (black/mint) palette — matches
   this project's established Stitch-mockup-porting convention. The real
   Documentation feature (shared, uploaded knowledge-base files) has no
   UI surface in this design, so every section here is illustrative
   dummy content; nothing posts to the backend and no button fakes a
   result. ── */
defineProps({
    documents: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    authUserId: { type: Number, default: null },
});

/* ── Search & quick filters ──────────────────────────────────────────── */
const searchInputEl = ref(null);
const searchQuery = ref('');
function applyFilter(term) {
    searchQuery.value = term;
    nextTick(() => searchInputEl.value?.focus());
}
const frequentChips = [
    { label: 'Getting Started' },
    { label: 'Lot vs Batch' },
    { label: 'Escrow Release' },
    { label: 'RFQs & Offers' },
    { label: 'EUDR Compliance', highlight: true },
    { label: 'AI Workforce' },
    { label: 'Spot Auctions' },
];

function onSearchKeydown(e) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        searchInputEl.value?.focus();
        searchInputEl.value?.select();
    }
}
onMounted(() => window.addEventListener('keydown', onSearchKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onSearchKeydown));

/* ── Jump-to quick nav ───────────────────────────────────────────────── */
const jumpLinks = [
    { href: '#welcome', label: 'Overview' },
    { href: '#start-here', label: 'Role Gateways' },
    { href: '#lifecycle', label: 'Lifecycle Pipeline' },
    { href: '#concepts', label: 'Lot & Primitives' },
    { href: '#workflows', label: 'Trading Flows' },
    { href: '#ai-commerce', label: 'AI Workforce' },
    { href: '#trust', label: 'Trust & Escrow' },
];

/* ── Ask Bean Origin AI (client-side demo, mirrors the mockup's own
   canned-response behaviour — illustrative only, no backend call) ──── */
const aiQuery = ref('');
const aiAsked = ref(false);
const aiLoading = ref(false);
const aiResponse = ref('');
const aiSuggestions = [
    'What is the difference between a Batch and a Lot?',
    'How does escrow release work upon shipping?',
    'What satellite check is needed for EUDR?',
];
function fillAiQuery(prompt) {
    aiQuery.value = prompt;
    askAi();
}
function askAi() {
    const q = aiQuery.value.trim();
    if (!q) return;
    aiAsked.value = true;
    aiLoading.value = true;
    const needle = q.toLowerCase();
    setTimeout(() => {
        if (needle.includes('batch') || needle.includes('lot')) {
            aiResponse.value = 'A Batch represents raw processed parchment from a single drying bed or fermentation tank. A Lot is the finalized, dry-milled export commercial unit (e.g. 300 GrainPro bags) listed on the exchange with formal Q-scores and contracts.';
        } else if (needle.includes('escrow')) {
            aiResponse.value = 'Escrow funds are deposited 100% by the buyer upon Trade confirmation. Funds are disbursed automatically in milestones: 20% upon pre-shipment sample approval, 70% upon clean Ocean Bill of Lading verification, and 10% upon final destination weight/moisture verification.';
        } else if (needle.includes('eudr')) {
            aiResponse.value = 'EUDR compliance requires validated GPS polygon coordinates for all farm plots over 4 hectares (or points for smaller plots). Bean Origin cross-references these automatically against 2020 Sentinel-2 canopy satellite baselines to generate your official Due Diligence Statement (DDS).';
        } else {
            aiResponse.value = 'Under the Bean Origin Institutional Standard, all trades are governed by bilateral FOB/CIF Incoterms 2026. Every transaction features cryptographic audit trails and human-in-the-loop sign-off before fund movements.';
        }
        aiLoading.value = false;
    }, 450);
}

const resources = [
    { icon: 'picture_as_pdf', title: 'Standard Contract Spec 2026', meta: 'PDF (1.8 MB)', tone: 'secondary' },
    { icon: 'description', title: 'EUDR Due Diligence Template', meta: 'XLS (420 KB)', tone: 'primary' },
    { icon: 'menu_book', title: 'Incoterms 2026 Coffee Handbook', meta: 'PDF (2.4 MB)', tone: 'muted' },
];

const feedbackMsg = ref('');
function giveFeedback(positive) {
    feedbackMsg.value = positive
        ? 'Thank you! Your feedback improves this documentation.'
        : 'Noted. Our editorial team will review this article.';
}

/* ── Overview lifecycle strip ────────────────────────────────────────── */
const lifecycleSteps = [
    { num: '01', label: 'Discover' },
    { num: '02', label: 'Evaluate' },
    { num: '03', label: 'Trade' },
    { num: '04', label: 'Order' },
    { num: '05', label: 'Payment' },
    { num: '06', label: 'Fulfil' },
    { num: '07', label: 'Delivery', active: true },
];

/* ── Role gateways ───────────────────────────────────────────────────── */
const roleCards = [
    { icon: 'shopping_cart_checkout', tone: 'primary', title: "I'm a Buyer / Roaster", desc: 'Source transparent origin coffees with lab cupping scores, arrange pre-shipment green samples, and negotiate container or pallet volumes with complete price visibility.', cta: 'Start Buying Guide', href: '#workflows' },
    { icon: 'apartment', tone: 'secondary', title: "I'm a Coffee Business", desc: 'Establish an export cooperative or washing station entity. Verify business registrations, link bank accounts for USD/EUR settlement, and invite team operators.', cta: 'Set Up Business', href: '#trust' },
    { icon: 'agriculture', tone: 'primary', title: 'I Want to Sell Coffee', desc: 'Ingest processing batches from smallholders or estates. Group verified parchment batches into export-ready Lots with moisture, screen size, and sensor metrics.', cta: 'Learn to Sell', href: '#concepts' },
    { icon: 'candlestick_chart', tone: 'secondary', title: 'I Want to Trade & Arbitrage', desc: 'Master market structures: Private bilateral Offers, public reverse-auction RFQs, timed spot auctions, differential spreads against the ICE C-Market index.', cta: 'Learn Trading', href: '#workflows' },
];

/* ── Lifecycle pipeline (click a node to inspect) ───────────────────── */
const pipelineNodes = [
    { id: 'farm', phase: 'Phase 1', icon: 'psychiatry', title: 'Farm / Origin', note: 'GPS polygon & grower identity' },
    { id: 'collection', phase: 'Phase 2', icon: 'factory', title: 'Farm Collection', note: 'Washing station & daily intake' },
    { id: 'batch', phase: 'Phase 3', icon: 'layers', title: 'Batch Processing', note: 'Wet milling & fermentation tank' },
    { id: 'lot', phase: 'Phase 4 (Current)', icon: 'inventory_2', title: 'Export Lot', note: 'Milled green coffee (300 bags)' },
];
const pipelineDetail = {
    farm: { title: 'Phase 1: Registered Farm Origin', badge: 'GEOSPATIAL SOURCE', body: 'Smallholder plot or private estate with defined polygon boundaries recorded via high-precision GPS. Includes soil elevation, shade tree biodiversity, and EUDR zero-deforestation certification.' },
    collection: { title: 'Phase 2: Washing Station / Collection', badge: 'INTAKE POINT', body: 'Central cherry reception center where raw red coffee is weighed, floated for density grading, and logged into digital ledger before depulping.' },
    batch: { title: 'Phase 3: Fermentation & Parchment Batch', badge: 'PROCESSING', body: 'Homogeneous parchment quantity that undergoes uniform anaerobic, washed, or natural drying. Moisture is monitored down to exactly 10.5%–11.5%.' },
    lot: {
        title: 'Phase 4: Export Lot Specification',
        badge: 'CONTRACTABLE UNIT',
        body: 'An Export Lot is the legal trade unit on Bean Origin. It compiles one or several homogeneous batches from an identifiable washing station. A Lot requires verified moisture analysis (10.5%–11.5%), water activity levels, physical defect count (Grade 1/2), and an official Q-Grader cupping score sheet before listing on the public exchange.',
        stats: [
            { label: 'Min Size', value: '10 Bags (600kg)' },
            { label: 'Max Size', value: '1 Full Container Load (FCL, 19.2 MT)' },
            { label: 'Telemetry', value: 'GrainPro Verified' },
        ],
    },
};
const selectedNode = ref('lot');
const activeDetail = computed(() => pipelineDetail[selectedNode.value]);

/* ── Core system concepts glossary ──────────────────────────────────── */
const concepts = [
    { term: 'Farm & Estate', tag: 'ENTITY', desc: 'A verified geographic parcel with registered latitude/longitude boundaries. Enables automated compliance with EUDR zero-deforestation satellite verifications.' },
    { term: 'Farm Collection', tag: 'AGGREGATOR', desc: 'A wet mill or central washing station that gathers red cherry receipts from member smallholders within an 8-mile micro-region.' },
    { term: 'Parchment Batch', tag: 'RAW STAGE', desc: 'Harvest processed together on a single drying bed or within one fermentation tank over a distinct date range (e.g., Nov 12–15).' },
    { term: 'Product Profile', tag: 'SPECIFICATION', desc: 'The institutional green coffee specification including varietal (SL28, Gesha, Bourbon), screen size (AA, AB, Supremo), processing method, and moisture profile.' },
    { term: 'The Offer', tag: 'BILATERAL', desc: 'A formal, time-bound commercial proposal submitted by a buyer directly on a published Lot, specifying target price per pound (FOB or CIF) and required shipping window.' },
    { term: 'Request for Quote (RFQ)', tag: 'REVERSE AUCTION', desc: 'A buyer-published procurement tender broadcast to certified exporters across specific origins (e.g., "Wanted: 2 FCL Organic Huila washed Arabica, 84+ SCAA, shipping March").' },
    { term: 'The Trade', tag: 'LEGAL AGREEMENT', desc: 'The legally binding agreement formed when an Offer or RFQ quote is accepted. Establishes the electronic sales contract, Incoterms, and payment escrow schedule.' },
    { term: 'The Order & Escrow', tag: 'FULFILMENT', desc: 'The execution phase where buyer funds enter multi-sig escrow, milling is completed, Bill of Lading documents are uploaded, and container logistics proceed.' },
];

/* ── Trading workflows ──────────────────────────────────────────────── */
const workflowTabs = [
    {
        id: 'offers', letter: 'A', title: 'Private Bilateral Offers', desc: 'Step-by-step negotiation protocol between a single roaster and exporter.',
        steps: [
            { icon: 'search', title: '1. Discover Lot', note: 'Inspect cupping report' },
            { icon: 'send', title: '2. Make Offer', note: 'Specify Price & Incoterm' },
            { icon: 'swap_horiz', title: '3. Counter / Revise', note: '48h response window' },
            { icon: 'handshake', title: '4. Trade Struck', note: 'Binding contract issued', final: true },
        ],
        rule: 'Rules of Engagement: Sellers have exactly 48 hours to accept, counter, or decline an Offer. If countered, the buyer has 48 hours to respond. Upon mutual agreement, the status automatically converts into an executable Trade #TRD-XXXX.',
    },
    {
        id: 'rfqs', letter: 'B', title: 'Reverse Procurement RFQs', desc: 'Broadcasting institutional purchasing criteria to hundreds of vetted origins.',
        steps: [
            { icon: 'post_add', title: '1. Create RFQ', note: 'Target Origin & Volume' },
            { icon: 'campaign', title: '2. Broadcast', note: 'Sent to vetted exporters' },
            { icon: 'balance', title: '3. Compare Quotes', note: 'Diff spread & cupping score' },
            { icon: 'check_circle', title: '4. Award & Trade', note: 'Locked allocation', final: true },
        ],
        rule: 'RFQs allow importers and commercial roasters to secure contract coverage months in advance. Sellers compete on physical quality, differential price, and verifiable harvest dates.',
    },
    {
        id: 'orders', letter: 'C', title: 'Orders, Telemetry & Escrow Release', desc: 'Guaranteed payment escrow stages matched with shipping documentation.',
        steps: [
            { icon: 'lock', title: '1. Escrow Funded', note: '100% funds secured' },
            { icon: 'precision_manufacturing', title: '2. Dry Milling', note: 'Green grading & bagging' },
            { icon: 'directions_boat', title: '3. Bill of Lading', note: 'Container onboard ship' },
            { icon: 'account_balance_wallet', title: '4. Settlement', note: 'Escrow released to seller', final: true },
        ],
        rule: 'Escrow protects both sides: Sellers never ship without guaranteed funds locked in tier-1 bank custody. Buyers never release funds until original clean on-board Bills of Lading and phytosanitary certificates are verified.',
    },
];
const activeWorkflowId = ref('offers');
const activeWorkflow = computed(() => workflowTabs.find((t) => t.id === activeWorkflowId.value));

/* ── AI commerce ─────────────────────────────────────────────────────── */
const aiAgents = [
    { title: 'Discovery & Sourcing Agent', desc: 'Matches taste profiles (e.g. "Jasmine, Bergamot, 87+") with uncommitted harvest Lots in Ethiopia & Colombia.' },
    { title: 'Negotiation Copilot', desc: 'Evaluates current differential ranges against historical contracts to suggest optimal counter-bid levels.' },
    { title: 'Logistics & Route Telemetry', desc: 'Monitors Red Sea / Panama transit delays and predicts arrival shifts for customs clearance planning.' },
    { title: 'EUDR Compliance Auditor', desc: 'Cross-references farm GPS coordinates with Sentinel-2 satellite canopy loss indices to generate Due Diligence Statements.' },
];

/* ── Trust & settlement ──────────────────────────────────────────────── */
const trustCards = [
    { icon: 'verified', tone: 'primary', title: 'KYB / AML Verified', desc: 'Every business passes strict Know-Your-Business, export license checks, and beneficiary audits before issuing lots or bidding.' },
    { icon: 'satellite_alt', tone: 'secondary', title: 'EUDR Deforestation-Free', desc: 'Automated geospatial polygon verification confirms zero post-2020 forest loss, generating turnkey EU customs packages.' },
    { icon: 'shield', tone: 'primary', title: 'Escrowed Settlement', desc: 'Capital is held in segregated regulatory trust accounts and only released against verifiable port-of-loading transport documents.' },
];

/* ── Sidebar knowledge tree ─────────────────────────────────────────── */
const navGroups = [
    { id: 'getting-started', icon: 'play_circle', tone: 'primary', label: 'Getting Started', items: [
        { label: 'Platform Introduction', href: '#welcome', active: true },
        { label: 'How Bean Origin Works', href: '#welcome' },
        { label: 'Buyer Onboarding Protocol', href: '#start-here' },
        { label: 'Business Account Setup', href: '#start-here' },
        { label: 'Identity Verification (KYB/AML)', href: '#trust' },
        { label: 'The Exchange Dashboard', href: '#welcome' },
    ] },
    { id: 'coffee', icon: 'grain', tone: 'secondary', label: 'Coffee Entities', items: [
        { label: 'Farms & Estates', href: '#concepts' },
        { label: 'Farm Collections (Washing Stations)', href: '#concepts' },
        { label: 'Batch Ingestion Protocols', href: '#concepts' },
        { label: 'Export Lot Formation', href: '#concepts', active: true },
        { label: 'Product Profiles (Green Coffee)', href: '#concepts' },
        { label: 'SCAA / CQI Sensory Scores', href: '#trust' },
        { label: 'EUDR Deforestation Data', href: '#trust' },
    ] },
    { id: 'trading', icon: 'sync_alt', tone: 'primary', label: 'Trading Engine', items: [
        { label: 'Order Routing & Matching', href: '#workflows' },
        { label: 'Making Private Offers', href: '#workflows' },
        { label: 'Issuing Global RFQs', href: '#workflows' },
        { label: 'Spot Auctions & Liquidity', href: '#workflows' },
        { label: 'Escrow Deposit Milestones', href: '#workflows' },
        { label: 'Bills of Lading & Fulfilment', href: '#workflows' },
    ] },
    { id: 'intel', icon: 'monitoring', tone: 'muted', label: 'Market Intelligence', items: [
        { label: 'C-Price & Differential Index', href: '#workflows' },
        { label: 'Farmgate vs Export Margins', href: '#workflows' },
        { label: 'Origin Weather & Harvest Cycles', href: '#workflows' },
        { label: 'Container Freight Telemetry', href: '#workflows' },
    ] },
    { id: 'ai', icon: 'smart_toy', tone: 'primary', label: 'AI Commerce', items: [
        { label: 'AI Workforce Philosophy', href: '#ai-commerce' },
        { label: 'Discovery & Sourcing Agent', href: '#ai-commerce' },
        { label: 'Negotiation Copilot', href: '#ai-commerce' },
        { label: 'Human-in-the-Loop Safeguards', href: '#ai-commerce' },
    ] },
    { id: 'trust-nav', icon: 'verified_user', tone: 'secondary', label: 'Trust & Settlement', items: [
        { label: 'Smart Escrow Mechanics', href: '#trust' },
        { label: 'Cryptographic Provenance', href: '#trust' },
        { label: 'Quality Dispute Resolution', href: '#trust' },
        { label: 'Sample Verification Protocol', href: '#trust' },
    ] },
];
const openGroups = ref(Object.fromEntries(navGroups.map((g) => [g.id, true])));
function toggleGroup(id) {
    openGroups.value[id] = !openGroups.value[id];
}

const footerLinks = [
    { label: 'Documentation Home', href: '#welcome' },
    { label: 'Trade Rulebook', href: '#workflows' },
    { label: 'Escrow Terms', href: '#trust' },
    { label: 'EUDR Compliance', href: '#trust' },
    { label: 'API & Webhooks', href: '#' },
    { label: 'System Status', href: '#' },
];
</script>

<template>
    <MainLayout title="Documentation">
        <Head title="Documentation">
            <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="dc-page">
            <!-- ── Hero / search zone ─────────────────────────────────── -->
            <section class="dc-hero">
                <div class="dc-crumb">
                    <span>Documentation</span>
                    <span>/</span>
                    <span class="dc-crumb__active">Knowledge Hub</span>
                    <span>/</span>
                    <span class="dc-crumb__badge">v2.4 INSTITUTIONAL GUIDE</span>
                </div>

                <div class="dc-hero__top">
                    <div class="dc-hero__copy">
                        <h1 class="dc-title">Bean Origin Documentation</h1>
                        <p class="dc-subtitle">The definitive trading, logistics, and verification handbook. Learn how raw harvest moves from high-altitude farm gate into traded export Lots, digital contracts, and global delivery.</p>
                    </div>
                    <div class="dc-hero__actions">
                        <button type="button" class="dc-btn dc-btn--muted">
                            <span class="material-symbols-outlined">file_download</span> Exporter Spec 2026
                        </button>
                        <button type="button" class="dc-btn dc-btn--primary">
                            <span class="material-symbols-outlined">support_agent</span> Trading Desk Support
                        </button>
                    </div>
                </div>

                <div class="dc-search">
                    <div class="dc-search__field">
                        <span class="material-symbols-outlined dc-search__icon">search</span>
                        <input
                            ref="searchInputEl"
                            v-model="searchQuery"
                            class="dc-search__input"
                            placeholder="Search coffee origins, Lot protocols, RFQs, escrow terms, EUDR compliance, AI workflows..."
                            type="text"
                        />
                        <kbd class="dc-search__kbd">⌘K</kbd>
                    </div>
                    <div class="dc-chips">
                        <span class="dc-chips__label">Frequent:</span>
                        <button
                            v-for="chip in frequentChips"
                            :key="chip.label"
                            type="button"
                            class="dc-chip"
                            :class="{ 'dc-chip--active': chip.highlight }"
                            @click="applyFilter(chip.label)"
                        >{{ chip.label }}</button>
                    </div>
                </div>
            </section>

            <!-- ── Workspace ──────────────────────────────────────────── -->
            <div class="dc-columns">
                <main class="dc-main">
                    <div class="dc-jumpnav">
                        <div class="dc-jumpnav__label"><span class="material-symbols-outlined">list_alt</span> Jump To:</div>
                        <div class="dc-jumpnav__links">
                            <a v-for="link in jumpLinks" :key="link.href" :href="link.href" class="dc-jumpnav__link">{{ link.label }}</a>
                        </div>
                    </div>

                    <div class="dc-quick-grid">
                        <div class="dc-card dc-ai-card">
                            <div class="dc-ai-card__head">
                                <div class="dc-ai-card__title"><span class="dc-icon-box dc-icon-box--primary"><span class="material-symbols-outlined">smart_toy</span></span> Ask Bean Origin AI</div>
                                <span class="dc-status-pill">ONLINE</span>
                            </div>
                            <p class="dc-muted-text">Instant answers from our 400-page trading rulebook and technical standards.</p>
                            <div class="dc-ai-input">
                                <input v-model="aiQuery" type="text" placeholder="Ask about Lot creation, escrow triggers, EUDR..." @keydown.enter="askAi" />
                                <button type="button" class="dc-ai-send" @click="askAi"><span class="material-symbols-outlined">arrow_upward</span></button>
                            </div>
                            <div v-if="aiAsked" class="dc-ai-response">
                                <span class="dc-ai-response__label">AI Assistant:</span>
                                <span v-if="aiLoading" class="dc-muted-text">Synthesizing exchange documentation...</span>
                                <span v-else>{{ aiResponse }}</span>
                            </div>
                            <div class="dc-ai-suggestions">
                                <button v-for="s in aiSuggestions" :key="s" type="button" class="dc-ai-suggestion" @click="fillAiQuery(s)">{{ s }}</button>
                            </div>
                        </div>

                        <div class="dc-card dc-resource-card">
                            <div class="dc-resource-card__head">
                                <span class="dc-ai-card__title" style="font-weight:800;">Commercial Resources &amp; Downloads</span>
                                <span class="dc-mono-tag">SPEC V2.4</span>
                            </div>
                            <p class="dc-muted-text">Official legal trade specs, compliance templates, and Incoterms documentation.</p>
                            <div class="dc-resource-list">
                                <button v-for="r in resources" :key="r.title" type="button" class="dc-resource-row">
                                    <span class="dc-resource-row__left">
                                        <span class="material-symbols-outlined" :class="`dc-tone-${r.tone}`">{{ r.icon }}</span>
                                        <span>{{ r.title }}</span>
                                    </span>
                                    <span class="dc-resource-row__meta">{{ r.meta }}</span>
                                </button>
                            </div>
                            <div class="dc-feedback">
                                <span>Was this helpful?</span>
                                <div class="dc-feedback__actions">
                                    <button type="button" class="dc-feedback__btn" @click="giveFeedback(true)"><span class="material-symbols-outlined">thumb_up</span> Yes</button>
                                    <button type="button" class="dc-feedback__btn" @click="giveFeedback(false)"><span class="material-symbols-outlined">thumb_down</span> No</button>
                                </div>
                                <span class="dc-feedback__msg">{{ feedbackMsg }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Overview -->
                    <section id="welcome" class="dc-card dc-section">
                        <div class="dc-eyebrow"><span class="material-symbols-outlined">hub</span> Architecture &amp; Principles</div>
                        <h2 class="dc-h2">A Direct Institutional Protocol for Specialty Green Coffee</h2>
                        <p class="dc-body-text">Bean Origin replaces fragmented broker networks, opaque FOB margins, and unverified paper certifications with a unified digital infrastructure. Coffee businesses and buyers connect directly via real-time sensory evaluation data, verifiable satellite deforestation checks, structured electronic negotiation, and programmatic escrow settlement.</p>
                        <div class="dc-lifecycle-head">
                            <span>The End-to-End Cycle</span>
                            <span class="dc-tone-primary-text">7 Seamless States</span>
                        </div>
                        <div class="dc-lifecycle-grid">
                            <div v-for="step in lifecycleSteps" :key="step.num" class="dc-lifecycle-step" :class="{ 'dc-lifecycle-step--active': step.active }">
                                <span class="dc-lifecycle-step__num">{{ step.num }}</span>
                                <span>{{ step.label }}</span>
                            </div>
                        </div>
                    </section>

                    <!-- Role gateways -->
                    <section id="start-here" class="dc-section">
                        <div class="dc-section-head">
                            <div>
                                <span class="dc-label">Role Gateways</span>
                                <h3 class="dc-h3">Start Here Based on Your Role</h3>
                            </div>
                            <span class="dc-mono-note">Select your workspace mode</span>
                        </div>
                        <div class="dc-role-grid">
                            <div v-for="role in roleCards" :key="role.title" class="dc-card dc-role-card">
                                <div class="dc-icon-box" :class="`dc-icon-box--${role.tone}`"><span class="material-symbols-outlined">{{ role.icon }}</span></div>
                                <h4 class="dc-role-card__title">{{ role.title }}</h4>
                                <p class="dc-muted-text">{{ role.desc }}</p>
                                <a :href="role.href" class="dc-role-card__cta" :class="`dc-tone-${role.tone}-text`">{{ role.cta }} <span class="material-symbols-outlined">arrow_forward</span></a>
                            </div>
                        </div>
                    </section>

                    <!-- Lifecycle pipeline -->
                    <section id="lifecycle" class="dc-section">
                        <div class="dc-section-head">
                            <div>
                                <span class="dc-label">Physical to Digital Transition</span>
                                <h3 class="dc-h3">The Coffee Lifecycle Pipeline</h3>
                            </div>
                            <span class="dc-mono-note">Click a node to inspect</span>
                        </div>
                        <div class="dc-card">
                            <p class="dc-muted-text">Every bag traded on Bean Origin has an auditable digital thread. Track how cherries transform into institutional contract lots:</p>
                            <div class="dc-pipeline-grid">
                                <button
                                    v-for="node in pipelineNodes"
                                    :key="node.id"
                                    type="button"
                                    class="dc-pipeline-node"
                                    :class="{ 'dc-pipeline-node--active': selectedNode === node.id }"
                                    @click="selectedNode = node.id"
                                >
                                    <div class="dc-pipeline-node__top">
                                        <span class="dc-pipeline-node__phase">{{ node.phase }}</span>
                                        <span class="material-symbols-outlined">{{ node.icon }}</span>
                                    </div>
                                    <div class="dc-pipeline-node__title">{{ node.title }}</div>
                                    <div class="dc-pipeline-node__note">{{ node.note }}</div>
                                </button>
                            </div>
                            <div class="dc-pipeline-detail">
                                <div class="dc-pipeline-detail__head">
                                    <div class="dc-pipeline-detail__title"><span class="dc-dot"></span> {{ activeDetail.title }}</div>
                                    <span class="dc-mono-tag dc-mono-tag--fixed">{{ activeDetail.badge }}</span>
                                </div>
                                <p class="dc-muted-text" v-html="activeDetail.body"></p>
                                <div v-if="activeDetail.stats" class="dc-pipeline-stats">
                                    <template v-for="(stat, idx) in activeDetail.stats" :key="stat.label">
                                        <span>{{ stat.label }}: <strong>{{ stat.value }}</strong></span>
                                        <span v-if="idx < activeDetail.stats.length - 1">•</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Concepts -->
                    <section id="concepts" class="dc-section">
                        <span class="dc-label">Glossary &amp; Primitives</span>
                        <h3 class="dc-h3">Core System Concepts</h3>
                        <p class="dc-mono-note dc-mono-note--block">Master the vocabulary used across the trading terminal, contract templates, and warehouse slips.</p>
                        <div class="dc-concept-grid">
                            <div v-for="c in concepts" :key="c.term" class="dc-card dc-concept-card">
                                <div class="dc-concept-card__head">
                                    <span class="dc-concept-card__term">{{ c.term }}</span>
                                    <span class="dc-mono-tag">{{ c.tag }}</span>
                                </div>
                                <p class="dc-muted-text">{{ c.desc }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Trading workflows -->
                    <section id="workflows" class="dc-section">
                        <div class="dc-section-head">
                            <div>
                                <span class="dc-label">Standard Operating Procedures</span>
                                <h3 class="dc-h3">Trading Workflows Explained</h3>
                            </div>
                            <div class="dc-tabs">
                                <button
                                    v-for="tab in workflowTabs"
                                    :key="tab.id"
                                    type="button"
                                    class="dc-tab"
                                    :class="{ 'dc-tab--active': activeWorkflowId === tab.id }"
                                    @click="activeWorkflowId = tab.id"
                                >{{ tab.title.split(' ')[0] }}{{ tab.id === 'orders' ? ' & Escrow' : ' Flow' }}</button>
                            </div>
                        </div>
                        <div class="dc-card">
                            <div class="dc-workflow-head">
                                <div class="dc-workflow-letter">{{ activeWorkflow.letter }}</div>
                                <div>
                                    <h4 class="dc-role-card__title">{{ activeWorkflow.title }}</h4>
                                    <p class="dc-mono-note">{{ activeWorkflow.desc }}</p>
                                </div>
                            </div>
                            <div class="dc-workflow-steps">
                                <template v-for="(step, idx) in activeWorkflow.steps" :key="step.title">
                                    <div class="dc-workflow-step">
                                        <div class="dc-workflow-step__icon" :class="{ 'dc-workflow-step__icon--final': step.final }"><span class="material-symbols-outlined">{{ step.icon }}</span></div>
                                        <span class="dc-workflow-step__title">{{ step.title }}</span>
                                        <span class="dc-workflow-step__note">{{ step.note }}</span>
                                    </div>
                                    <span v-if="idx < activeWorkflow.steps.length - 1" class="material-symbols-outlined dc-workflow-arrow">trending_flat</span>
                                </template>
                            </div>
                            <p class="dc-muted-text"><strong>{{ activeWorkflow.rule.split(':')[0] }}:</strong>{{ activeWorkflow.rule.split(':').slice(1).join(':') }}</p>
                        </div>
                    </section>

                    <!-- AI commerce -->
                    <section id="ai-commerce" class="dc-section">
                        <div class="dc-card dc-ai-commerce">
                            <div class="dc-ai-commerce__head">
                                <div class="dc-ai-card__title"><span class="dc-icon-box dc-icon-box--primary"><span class="material-symbols-outlined">smart_toy</span></span>
                                    <div>
                                        <div>Trade with Bean Origin AI</div>
                                        <span class="dc-mono-note dc-tone-primary-text">AUTONOMOUS WORKFORCE ENGINE</span>
                                    </div>
                                </div>
                                <span class="dc-mono-tag dc-mono-tag--fixed">HUMAN-IN-THE-LOOP</span>
                            </div>
                            <p class="dc-body-text">Bean Origin deploys specialized, domain-trained AI Agents to accelerate market discovery, automate contract drafting, and monitor shipping bottlenecks. Our foundational safety principle:</p>
                            <div class="dc-directive">
                                <span class="material-symbols-outlined">gavel</span>
                                <p><strong>Core Fiduciary Directive:</strong> "Bean Origin AI agents can scan telemetry, compare cupping variance, draft counter-proposals, and analyze freight routes. However, all legally binding financial commitments, payment releases, and contract executions require explicit human-in-the-loop authorization."</p>
                            </div>
                            <div class="dc-agent-grid">
                                <div v-for="agent in aiAgents" :key="agent.title" class="dc-agent-card">
                                    <div class="dc-agent-card__head"><span>{{ agent.title }}</span><span class="dc-tone-primary-text dc-mono-note">Active</span></div>
                                    <p class="dc-muted-text">{{ agent.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Trust -->
                    <section id="trust" class="dc-section">
                        <span class="dc-label">Integrity Infrastructure</span>
                        <h3 class="dc-h3">Trust at Every Stage</h3>
                        <p class="dc-mono-note dc-mono-note--block">Why institutional roasters and multi-national exporters trade millions of dollars via Bean Origin.</p>
                        <div class="dc-trust-grid">
                            <div v-for="t in trustCards" :key="t.title" class="dc-card">
                                <span class="material-symbols-outlined" :class="`dc-tone-${t.tone}`" style="font-size:24px;">{{ t.icon }}</span>
                                <h4 class="dc-role-card__title" style="margin-top:8px;">{{ t.title }}</h4>
                                <p class="dc-muted-text">{{ t.desc }}</p>
                            </div>
                        </div>
                    </section>
                </main>

                <aside class="dc-aside">
                    <div class="dc-card dc-nav-card">
                        <div class="dc-nav-card__head">
                            <span>Knowledge Architecture</span>
                            <span class="dc-mono-tag">42 TOPICS</span>
                        </div>
                        <nav class="dc-nav-tree">
                            <div v-for="group in navGroups" :key="group.id">
                                <div class="dc-nav-group__head" @click="toggleGroup(group.id)">
                                    <div class="dc-nav-group__label"><span class="material-symbols-outlined" :class="`dc-tone-${group.tone}`">{{ group.icon }}</span> {{ group.label }}</div>
                                    <span class="material-symbols-outlined dc-nav-chevron" :class="{ 'dc-nav-chevron--open': openGroups[group.id] }">expand_more</span>
                                </div>
                                <ul v-show="openGroups[group.id]" class="dc-nav-group__list">
                                    <li v-for="item in group.items" :key="item.label">
                                        <a :href="item.href" :class="{ 'dc-nav-item--active': item.active }">{{ item.label }}</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                    <div class="dc-card dc-promo-card">
                        <div class="dc-promo-card__title"><span class="material-symbols-outlined">verified</span> Accredited Exchange</div>
                        <p class="dc-muted-text">All trades executed on Bean Origin are backed by the Uniform Commercial Coffee Standards &amp; verified multi-party escrows.</p>
                        <a href="#trust" class="dc-promo-card__link">View Escrow Guarantees <span class="material-symbols-outlined">arrow_forward</span></a>
                    </div>
                </aside>
            </div>

            <!-- ── Footer strip ───────────────────────────────────────── -->
            <footer class="dc-footer">
                <div class="dc-footer__brand">
                    <div class="dc-icon-box dc-icon-box--primary"><span class="material-symbols-outlined">grain</span></div>
                    <div>
                        <span class="dc-footer__title">Bean Origin Exchange Documentation</span>
                        <span class="dc-mono-note">Institutional Coffee Standards &amp; Electronic Trading Rulebook</span>
                    </div>
                </div>
                <div class="dc-footer__links">
                    <a v-for="link in footerLinks" :key="link.label" :href="link.href">{{ link.label }}</a>
                </div>
                <div class="dc-mono-note">© 2026 Bean Origin Corp. All rights reserved.</div>
            </footer>
        </div>
    </MainLayout>
</template>

<style scoped>
/* ── Page-scoped token block, mapped 1:1 from the mockup's own
   "Scientific Atelier" palette (Deep Emerald / Roasted Umber) — kept
   separate from the app's --dp-* theme (a different black/mint
   palette) so this page renders exactly as designed. ── */
.dc-page {
    --dc-primary: #004532;
    --dc-primary-container: #065f46;
    --dc-on-primary: #ffffff;
    --dc-on-primary-container: #8bd6b7;
    --dc-primary-fixed: #a6f2d1;
    --dc-on-primary-fixed: #002116;
    --dc-secondary: #725a42;
    --dc-secondary-fixed: #fedcbe;
    --dc-on-secondary-fixed: #291806;
    --dc-surface: #f7f9fb;
    --dc-surface-container-lowest: #ffffff;
    --dc-surface-container-low: #f2f4f6;
    --dc-surface-container: #eceef0;
    --dc-surface-container-high: #e6e8ea;
    --dc-surface-container-highest: #e0e3e5;
    --dc-on-surface: #191c1e;
    --dc-on-surface-variant: #3f4944;
    --dc-outline-variant: #bec9c2;

    font-family: var(--dp-font-sans);
    color: var(--dc-on-surface);
    background: var(--dc-surface);
    margin: -48px -64px;
    padding: 0 0 40px;
    display: flex;
    flex-direction: column;
}
.dc-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; font-size: 20px; }
.dc-tone-primary { color: var(--dc-primary); }
.dc-tone-primary-text { color: var(--dc-primary); }
.dc-tone-secondary { color: var(--dc-secondary); }
.dc-tone-secondary-text { color: var(--dc-secondary); }
.dc-tone-muted { color: var(--dc-on-surface-variant); }

.dc-card { background: var(--dc-surface-container-lowest); border-radius: 12px; box-shadow: 0 1px 3px rgba(25, 28, 30, .05); padding: 24px; }
.dc-muted-text { font-size: var(--dp-content-font-size); color: var(--dc-on-surface-variant); line-height: 1.6; margin: 0; }
.dc-mono-note { font-size: 11px; font-family: var(--dp-font-mono); color: var(--dc-on-surface-variant); }
.dc-mono-note--block { display: block; margin: 4px 0 16px; }
.dc-mono-tag { font-size: 10px; font-weight: 700; font-family: var(--dp-font-mono); padding: 2px 8px; border-radius: 999px; background: var(--dc-surface-container); color: var(--dc-on-surface-variant); }
.dc-mono-tag--fixed { background: var(--dc-primary-fixed); color: var(--dc-on-primary-fixed); font-weight: 800; }
.dc-label { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: var(--dc-primary); font-weight: 700; }
.dc-h2 { font-size: 26px; font-weight: 800; letter-spacing: -.01em; margin: 8px 0; color: var(--dc-on-surface); }
.dc-h3 { font-size: 19px; font-weight: 800; margin: 4px 0 0; color: var(--dc-on-surface); }
.dc-body-text { font-size: 14px; color: var(--dc-on-surface-variant); line-height: 1.7; margin: 0; }

.dc-icon-box { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: var(--dc-surface-container-low); color: var(--dc-primary); flex-shrink: 0; }
.dc-icon-box .material-symbols-outlined { font-size: 16px; }
.dc-icon-box--primary { background: var(--dc-primary); color: var(--dc-on-primary); }
.dc-icon-box--secondary { background: var(--dc-secondary-fixed); color: var(--dc-on-secondary-fixed); }
.dc-icon-box--muted { background: var(--dc-surface-container-low); color: var(--dc-on-surface-variant); }
.dc-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: var(--dc-primary); margin-right: 4px; }
.dc-status-pill { font-size: 10px; font-family: var(--dp-font-mono); font-weight: 800; padding: 2px 8px; border-radius: 999px; background: var(--dc-primary-fixed); color: var(--dc-on-primary-fixed); }

/* Hero */
.dc-hero { background: var(--dc-surface-container-lowest); padding: 40px 64px 48px; box-shadow: 0 1px 8px rgba(0, 0, 0, .04); display: flex; flex-direction: column; gap: 12px; }
.dc-crumb { display: flex; align-items: center; gap: 8px; font-size: 11px; font-family: var(--dp-font-mono); color: var(--dc-on-surface-variant); }
.dc-crumb__active { color: var(--dc-primary); font-weight: 700; }
.dc-crumb__badge { padding: 2px 8px; border-radius: 999px; background: var(--dc-primary-fixed); color: var(--dc-on-primary-fixed); font-weight: 800; font-size: 10px; letter-spacing: .03em; }
.dc-hero__top { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; flex-wrap: wrap; padding-bottom: 8px; }
.dc-hero__copy { max-width: 720px; }
.dc-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.15; margin: 0; color: var(--dc-on-surface); }
.dc-subtitle { font-size: 15px; color: var(--dc-on-surface-variant); margin: 10px 0 0; line-height: 1.6; }
.dc-hero__actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.dc-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.dc-btn .material-symbols-outlined { font-size: 16px; }
.dc-btn--muted { background: var(--dc-surface-container-low); color: var(--dc-on-surface); }
.dc-btn--muted:hover { background: var(--dc-surface-container); }
.dc-btn--primary { background: var(--dc-primary); color: var(--dc-on-primary); box-shadow: 0 1px 3px rgba(0, 69, 50, .2); }
.dc-btn--primary:hover { background: var(--dc-primary-container); }

.dc-search { max-width: 900px; }
.dc-search__field { display: flex; align-items: center; background: var(--dc-surface-container-low); border-radius: 14px; padding: 6px; transition: box-shadow .2s, background .2s; }
.dc-search__field:focus-within { background: var(--dc-surface-container-lowest); box-shadow: 0 8px 30px rgba(0, 69, 50, .08); }
.dc-search__icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; color: var(--dc-primary); font-size: 22px; }
.dc-search__input { flex: 1; border: none; background: transparent; font: inherit; font-size: 15px; color: var(--dc-on-surface); padding: 8px 0; outline: none; }
.dc-search__input::placeholder { color: rgba(63, 73, 68, .55); }
.dc-search__kbd { font-family: var(--dp-font-mono); font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px; background: var(--dc-surface-container-highest); color: var(--dc-on-surface-variant); margin-right: 8px; }
.dc-chips { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 14px; }
.dc-chips__label { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; font-weight: 700; color: var(--dc-on-surface-variant); margin-right: 2px; }
.dc-chip { border: none; cursor: pointer; padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; background: var(--dc-surface-container); color: var(--dc-on-surface); }
.dc-chip:hover { background: var(--dc-primary); color: var(--dc-on-primary); }
.dc-chip--active { background: var(--dc-primary-fixed); color: var(--dc-on-primary-fixed); font-weight: 800; }

/* Workspace columns */
.dc-columns { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 32px; align-items: start; padding: 40px 64px 0; }
.dc-main { display: flex; flex-direction: column; gap: 48px; min-width: 0; }
.dc-aside { position: sticky; top: 24px; display: flex; flex-direction: column; gap: 24px; }

.dc-jumpnav { background: var(--dc-surface-container-lowest); border-radius: 12px; box-shadow: 0 1px 3px rgba(25, 28, 30, .05); padding: 16px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; }
.dc-jumpnav__label { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dc-on-surface-variant); }
.dc-jumpnav__label .material-symbols-outlined { color: var(--dc-primary); font-size: 18px; }
.dc-jumpnav__links { display: flex; flex-wrap: wrap; gap: 8px; }
.dc-jumpnav__link { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; background: var(--dc-surface-container-low); color: var(--dc-on-surface); text-decoration: none; }
.dc-jumpnav__link:hover { background: var(--dc-primary-fixed); color: var(--dc-on-primary-fixed); }

.dc-quick-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
.dc-ai-card, .dc-resource-card { display: flex; flex-direction: column; gap: 14px; }
.dc-ai-card__head { display: flex; align-items: center; justify-content: space-between; }
.dc-ai-card__title { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 14px; }
.dc-ai-input { position: relative; }
.dc-ai-input input { width: 100%; box-sizing: border-box; background: var(--dc-surface-container-low); border: none; border-radius: 10px; padding: 10px 36px 10px 12px; font-size: var(--dp-content-font-size); color: var(--dc-on-surface); outline: none; }
.dc-ai-input input:focus { box-shadow: 0 0 0 1px var(--dc-primary); }
.dc-ai-send { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--dc-primary); cursor: pointer; display: flex; }
.dc-ai-response { background: var(--dc-surface-container-low); border-radius: 10px; padding: 12px; font-size: 11px; line-height: 1.6; }
.dc-ai-response__label { font-weight: 800; color: var(--dc-primary); display: block; margin-bottom: 4px; }
.dc-ai-suggestions { display: flex; flex-wrap: wrap; gap: 8px; }
.dc-ai-suggestion { text-align: left; border: none; cursor: pointer; padding: 5px 10px; border-radius: 8px; background: var(--dc-surface-container-low); font-size: 11px; color: var(--dc-on-surface-variant); }
.dc-ai-suggestion:hover { background: var(--dc-surface-container); color: var(--dc-primary); }

.dc-resource-card__head { display: flex; align-items: center; justify-content: space-between; }
.dc-resource-list { display: flex; flex-direction: column; gap: 8px; }
.dc-resource-row { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px; border: none; border-radius: 10px; background: var(--dc-surface-container-low); cursor: pointer; font: inherit; }
.dc-resource-row:hover { background: var(--dc-surface-container); }
.dc-resource-row__left { display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 600; color: var(--dc-on-surface); }
.dc-resource-row__meta { font-size: 10px; font-family: var(--dp-font-mono); font-weight: 700; color: var(--dc-on-surface-variant); }
.dc-feedback { display: flex; align-items: center; gap: 10px; font-size: 12px; color: var(--dc-on-surface-variant); padding-top: 8px; flex-wrap: wrap; }
.dc-feedback__actions { display: flex; gap: 8px; }
.dc-feedback__btn { display: flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; border: none; cursor: pointer; background: var(--dc-surface-container-low); color: var(--dc-on-surface); font-size: 11px; font-weight: 600; }
.dc-feedback__btn:hover { background: var(--dc-surface-container); }
.dc-feedback__btn .material-symbols-outlined { font-size: 13px; }
.dc-feedback__msg { font-size: 10px; color: var(--dc-primary); font-weight: 600; }

.dc-section { display: flex; flex-direction: column; gap: 16px; }
.dc-section-head { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.dc-lifecycle-head { display: flex; align-items: center; justify-content: space-between; font-size: 11px; font-family: var(--dp-font-mono); font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dc-on-surface-variant); padding-top: 8px; }
.dc-lifecycle-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; text-align: center; font-size: 11px; font-weight: 700; }
.dc-lifecycle-step { background: var(--dc-surface-container-low); padding: 12px 6px; border-radius: 10px; color: var(--dc-on-surface); }
.dc-lifecycle-step__num { display: block; font-size: 10px; font-family: var(--dp-font-mono); color: rgba(63, 73, 68, .55); margin-bottom: 2px; }
.dc-lifecycle-step--active { background: var(--dc-primary); color: var(--dc-on-primary); }
.dc-lifecycle-step--active .dc-lifecycle-step__num { color: var(--dc-primary-fixed); }

.dc-role-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
.dc-role-card { display: flex; flex-direction: column; gap: 10px; transition: box-shadow .2s; }
.dc-role-card:hover { box-shadow: 0 4px 16px rgba(25, 28, 30, .07); }
.dc-role-card__title { font-size: 15px; font-weight: 800; margin: 0; color: var(--dc-on-surface); }
.dc-role-card__cta { margin-top: auto; padding-top: 8px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; text-decoration: none; }
.dc-role-card__cta .material-symbols-outlined { font-size: 15px; }

.dc-pipeline-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin: 16px 0; }
.dc-pipeline-node { border: none; cursor: pointer; text-align: left; background: var(--dc-surface-container-low); border-radius: 10px; padding: 12px; font: inherit; color: var(--dc-on-surface); }
.dc-pipeline-node:hover { background: var(--dc-surface-container); }
.dc-pipeline-node--active { background: var(--dc-primary); color: var(--dc-on-primary); }
.dc-pipeline-node__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.dc-pipeline-node__phase { font-size: 10px; font-family: var(--dp-font-mono); text-transform: uppercase; color: var(--dc-on-surface-variant); }
.dc-pipeline-node--active .dc-pipeline-node__phase { color: var(--dc-primary-fixed); font-weight: 800; }
.dc-pipeline-node__title { font-weight: 700; font-size: 12px; }
.dc-pipeline-node__note { font-size: 11px; opacity: .85; margin-top: 2px; }
.dc-pipeline-detail { background: var(--dc-surface-container-low); border-radius: 10px; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
.dc-pipeline-detail__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
.dc-pipeline-detail__title { display: flex; align-items: center; font-weight: 800; font-size: 13px; color: var(--dc-on-surface); }
.dc-pipeline-stats { display: flex; flex-wrap: wrap; gap: 10px; font-size: 11px; font-family: var(--dp-font-mono); color: var(--dc-on-surface-variant); }
.dc-pipeline-stats strong { color: var(--dc-on-surface); }

.dc-concept-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; }
.dc-concept-card { display: flex; flex-direction: column; gap: 6px; padding: 18px; }
.dc-concept-card__head { display: flex; align-items: center; justify-content: space-between; }
.dc-concept-card__term { font-weight: 800; font-size: 13.5px; color: var(--dc-on-surface); }

.dc-tabs { display: flex; gap: 4px; background: var(--dc-surface-container); padding: 4px; border-radius: 8px; }
.dc-tab { border: none; cursor: pointer; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; background: transparent; color: var(--dc-on-surface-variant); }
.dc-tab--active { background: var(--dc-surface-container-lowest); color: var(--dc-primary); font-weight: 800; box-shadow: 0 1px 2px rgba(0, 0, 0, .06); }
.dc-workflow-head { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
.dc-workflow-letter { width: 32px; height: 32px; border-radius: 8px; background: var(--dc-primary-fixed); color: var(--dc-on-primary-fixed); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; }
.dc-workflow-steps { display: flex; align-items: center; justify-content: space-between; gap: 6px; background: var(--dc-surface-container-low); padding: 16px; border-radius: 10px; margin-bottom: 16px; overflow-x: auto; }
.dc-workflow-step { display: flex; flex-direction: column; align-items: center; text-align: center; width: 110px; flex-shrink: 0; }
.dc-workflow-step__icon { width: 40px; height: 40px; border-radius: 50%; background: var(--dc-surface-container-lowest); box-shadow: 0 1px 3px rgba(25, 28, 30, .08); display: flex; align-items: center; justify-content: center; color: var(--dc-primary); margin-bottom: 8px; }
.dc-workflow-step__icon--final { background: var(--dc-primary); color: var(--dc-on-primary); }
.dc-workflow-step__title { font-size: 12px; font-weight: 700; color: var(--dc-on-surface); }
.dc-workflow-step__note { font-size: 10px; color: var(--dc-on-surface-variant); margin-top: 2px; }
.dc-workflow-arrow { color: var(--dc-outline-variant); flex-shrink: 0; }

.dc-ai-commerce { background: linear-gradient(135deg, var(--dc-surface-container-lowest), var(--dc-surface-container-low)); display: flex; flex-direction: column; gap: 16px; }
.dc-ai-commerce__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.dc-ai-commerce__head .dc-ai-card__title { font-size: 16px; }
.dc-directive { background: rgba(0, 69, 50, .05); padding: 14px; border-radius: 10px; display: flex; align-items: flex-start; gap: 10px; }
.dc-directive .material-symbols-outlined { color: var(--dc-primary); margin-top: 2px; }
.dc-directive p { margin: 0; font-size: 12px; color: var(--dc-on-surface); line-height: 1.6; }
.dc-agent-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
.dc-agent-card { background: var(--dc-surface-container-lowest); border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(25, 28, 30, .04); }
.dc-agent-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
.dc-agent-card__head span:first-child { font-weight: 700; font-size: 12px; color: var(--dc-on-surface); }

.dc-trust-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }

/* Aside */
.dc-nav-card { padding: 20px; }
.dc-nav-card__head { display: flex; align-items: center; justify-content: space-between; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; padding-bottom: 12px; margin-bottom: 12px; }
.dc-nav-tree { display: flex; flex-direction: column; gap: 12px; max-height: calc(100vh - 220px); overflow-y: auto; font-size: 12px; }
.dc-nav-group__head { display: flex; align-items: center; justify-content: space-between; cursor: pointer; padding: 6px 8px; border-radius: 8px; font-weight: 800; color: var(--dc-on-surface); }
.dc-nav-group__head:hover { background: var(--dc-surface-container-low); }
.dc-nav-group__label { display: flex; align-items: center; gap: 8px; }
.dc-nav-group__label .material-symbols-outlined { font-size: 17px; }
.dc-nav-chevron { font-size: 14px; color: var(--dc-on-surface-variant); transition: transform .15s; transform: rotate(-90deg); }
.dc-nav-chevron--open { transform: rotate(0deg); }
.dc-nav-group__list { list-style: none; margin: 6px 0 0; padding: 0 0 0 26px; display: flex; flex-direction: column; gap: 2px; }
.dc-nav-group__list a { display: block; padding: 4px 0; font-size: 12px; color: var(--dc-on-surface-variant); text-decoration: none; }
.dc-nav-group__list a:hover { color: var(--dc-primary); }
.dc-nav-item--active { color: var(--dc-primary) !important; font-weight: 700; }

.dc-promo-card { display: flex; flex-direction: column; gap: 8px; background: var(--dc-surface-container-low); box-shadow: none; }
.dc-promo-card__title { display: flex; align-items: center; gap: 6px; font-weight: 800; font-size: 13px; color: var(--dc-primary); }
.dc-promo-card__link { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; color: var(--dc-primary); text-decoration: none; }
.dc-promo-card__link:hover { text-decoration: underline; }
.dc-promo-card__link .material-symbols-outlined { font-size: 14px; }

/* Footer */
.dc-footer { margin-top: 24px; background: var(--dc-surface-container-lowest); box-shadow: 0 -1px 8px rgba(0, 0, 0, .02); padding: 32px 64px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
.dc-footer__brand { display: flex; align-items: center; gap: 12px; }
.dc-footer__title { display: block; font-weight: 800; font-size: 13px; color: var(--dc-on-surface); }
.dc-footer__links { display: flex; flex-wrap: wrap; gap: 20px; font-size: 12px; font-weight: 600; }
.dc-footer__links a { color: var(--dc-on-surface-variant); text-decoration: none; }
.dc-footer__links a:hover { color: var(--dc-primary); }

@media (max-width: 1200px) {
    .dc-columns { grid-template-columns: 1fr; }
    .dc-aside { position: static; }
}
@media (max-width: 900px) {
    .dc-hero { padding: 32px 24px; }
    .dc-columns { padding: 32px 24px 0; }
    .dc-footer { padding: 24px; }
    .dc-lifecycle-grid { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 640px) {
    .dc-hero__top { flex-direction: column; align-items: flex-start; }
    .dc-lifecycle-grid { grid-template-columns: repeat(2, 1fr); }
    .dc-workflow-steps { justify-content: flex-start; }
}
</style>
