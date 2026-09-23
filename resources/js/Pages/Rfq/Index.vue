<script setup>
/* ── Structural/visual port of the uploaded "RFQs & Sourcing Desk"
   mockup (code.html), restyled with a page-scoped token block (--rq-*)
   mapped 1:1 from the mockup's own "Scientific Atelier" palette (Deep
   Emerald / Roasted Umber) rather than the app's own --dp-* theme — same
   convention already used on the Documentation page port. This page now
   sits directly on MainLayout (no shared Trade-hub chrome), so the
   mockup's own header/hero, action buttons, and filter-tab row are
   ported here as page content rather than assumed to exist elsewhere.
   Per explicit instruction this pass is pure dummy content — nothing
   here posts to the backend; every action stays either a local-only
   visual interaction (tabs, modal open/close, search filter, AI demo)
   or fully inert rather than faking a result. ── */
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';
import { Coffee, LocationFilled, Box, PriceTag, DocumentChecked, Ship } from '@element-plus/icons-vue';

const props = defineProps({
    requests: { type: Array, default: () => [] },
    cropTypes: { type: Array, default: () => [] },
    grades: { type: Array, default: () => [] },
    origins: { type: Array, default: () => [] },
    incoterms: { type: Array, default: () => [] },
    marketCount: { type: Number, default: 0 },
    auctionCount: { type: Number, default: 0 },
    requestCount: { type: Number, default: 0 },
    authUserId: { type: Number, default: null },
});

/* ── Header tabs (All RFQs / My RFQs / Responses / …) ────────────────── */
const filterTabs = [
    { key: 'all', label: 'All RFQs', count: 24 },
    { key: 'mine', label: 'My RFQs', count: 8 },
    { key: 'responses', label: 'Responses', count: 18 },
    { key: 'open', label: 'Open', count: 4 },
    { key: 'negotiating', label: 'Negotiating', count: 3 },
    { key: 'awarded', label: 'Awarded', count: 7 },
    { key: 'closed', label: 'Closed', count: 12 },
];
const activeFilterTab = ref('mine');


/* ── Lifecycle stepper ───────────────────────────────────────────────── */
const lifecycleSteps = [
    { code: '01 / DRAFT', title: 'Specification', state: 'Completed', done: true },
    { code: '02 / PUBLISHED', title: 'Desk Distribution', state: 'Dispatched', done: true },
    { code: '03 / OPEN', title: 'Seller Discovery', state: 'Active', done: true },
    { code: '04 / RESPONSES', title: '5 Quotes Recv.', state: 'Evaluating', active: true },
    { code: '05 / NEGOTIATE', title: 'Counter Terms', state: 'Next Phase' },
    { code: '06 / AWARDED', title: 'Seller Selection', state: 'Pending' },
    { code: '07 / TRADE MINT', title: 'Escrow Lock', state: 'Automated' },
    { code: '08 / CLOSED', title: 'Bill of Lading', state: 'Fulfillment' },
];

/* ── Sourcing requests table — real data from `requests` (every
   LotRequest, system-wide), same source the page received before this
   visual pass. No fabricated origin/port/quotations/closing-date
   columns since LotRequest doesn't track those yet — only real fields
   are shown. ─────────────────────────────────────────────────────── */
const searchQuery = ref('');

const fmt = (value, digits = 2) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
};
const fmtDate = (value) => value
    ? new Date(value).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' })
    : '—';
const rfqCode = (item) => `RFQ-${new Date(item.created_at).getFullYear()}-${String(item.id).padStart(5, '0')}`;

const STATUS_LABELS = { pending: 'Open', approved: 'Approved', fulfilled: 'Fulfilled', rejected: 'Rejected' };
const statusLabel = (status) => STATUS_LABELS[status] || status;
const statusTone = (status) => {
    switch (status) {
        case 'approved': return 'primary';
        case 'fulfilled': return 'fixed';
        case 'rejected': return 'error';
        default: return 'secondary';
    }
};
const isMine = (item) => item.user_id === props.authUserId;

/* ── KPI strip — computed live from `requests` (LotRequest rows owned by
   the current user), same source the table reads from. Every figure below
   is a real aggregate over real statuses; nothing here is invented, so
   "Responses"/"Negotiation" style KPIs from the original mockup were
   dropped since LotRequest has no quotes/negotiation concept to back
   them — only pending/approved/rejected/fulfilled. ────────────────────── */
const myRequests = computed(() => props.requests.filter((r) => r.user_id === props.authUserId));
const kpis = computed(() => {
    const mine = myRequests.value;
    const byStatus = (status) => mine.filter((r) => r.status === status);
    const sumQty = (rows) => rows.reduce((total, r) => total + Number(r.quantity || 0), 0);
    const sumAmt = (rows) => rows.reduce((total, r) => total + Number(r.amount || 0), 0);

    const open = byStatus('pending');
    const approved = byStatus('approved');
    const fulfilled = byStatus('fulfilled');
    const rejected = byStatus('rejected');
    const pipeline = [...open, ...approved];
    const decided = approved.length + fulfilled.length + rejected.length;
    const fulfillmentRate = decided ? Math.round((fulfilled.length / decided) * 100) : null;

    return [
        { label: 'Open RFQs', icon: 'feed', value: String(open.length), hint: `${fmt(sumQty(open), 0)} kg targeted`, foot: 'Awaiting seller response' },
        { label: 'Approved', icon: 'handshake', value: String(approved.length), hint: `${fmt(sumQty(approved), 0)} kg queued`, foot: 'Awaiting fulfillment' },
        { label: 'Fulfilled', icon: 'verified', value: String(fulfilled.length), hint: `${fmt(sumQty(fulfilled), 0)} kg contracted`, foot: fulfillmentRate !== null ? `${fulfillmentRate}% fulfillment rate` : 'No decisions yet', footIcon: fulfillmentRate !== null ? 'trending_up' : undefined },
        { label: 'Rejected', icon: 'cancel', value: String(rejected.length), hint: `${fmt(sumQty(rejected), 0)} kg declined`, foot: 'Review notes for reissue' },
        { label: 'Pipeline Value', icon: 'payments', value: `$${fmt(sumAmt(pipeline), 0)}`, hint: `${pipeline.length} active request${pipeline.length === 1 ? '' : 's'}`, foot: 'Target procurement value' },
    ];
});

const ALL_TYPES = 'All Types';
const ALL_GRADES = 'All Grades';
const filterType = ref(ALL_TYPES);
const filterGrade = ref(ALL_GRADES);
const filterTypeOptions = computed(() => [ALL_TYPES, ...props.cropTypes]);
const filterGradeOptions = computed(() => [ALL_GRADES, ...props.grades]);

const sortBy = ref('Newest First');
const sortOptions = ['Newest First', 'Oldest First', 'Largest Quantity'];

const filteredRows = computed(() => {
    let rows = props.requests;

    const needle = searchQuery.value.trim().toLowerCase();
    if (needle) {
        rows = rows.filter((r) => [rfqCode(r), r.crop_type, r.variety, r.grade, r.user?.name]
            .filter(Boolean)
            .some((f) => f.toLowerCase().includes(needle)));
    }
    if (filterType.value !== ALL_TYPES) rows = rows.filter((r) => r.crop_type === filterType.value);
    if (filterGrade.value !== ALL_GRADES) rows = rows.filter((r) => r.grade === filterGrade.value);

    if (sortBy.value === 'Oldest First') return [...rows].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    if (sortBy.value === 'Largest Quantity') return [...rows].sort((a, b) => b.quantity - a.quantity);
    return [...rows].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const PAGE_SIZE = 10;
const currentPage = ref(1);
watch([searchQuery, filterType, filterGrade, sortBy], () => { currentPage.value = 1; });
const pagedRows = computed(() => {
    const start = (currentPage.value - 1) * PAGE_SIZE;
    return filteredRows.value.slice(start, start + PAGE_SIZE);
});

function respond(item, status) {
    router.post(route('buy.respond', item.id), { status }, { preserveScroll: true });
}

const deleteOpen = ref(false);
const pendingDelete = ref(null);
function requestDelete(item) {
    pendingDelete.value = item;
    deleteOpen.value = true;
}
function confirmDelete() {
    if (!pendingDelete.value) return;
    router.delete(route('lot.request.destroy', pendingDelete.value.id), { preserveScroll: true });
    pendingDelete.value = null;
}

/* ── Focus workspace: RFQ-1048 spec header ───────────────────────────── */
const specPills = [
    { label: 'Target Price', value: '$4.10 / kg CIF', tone: 'primary' },
    { label: 'Destination', value: 'Dubai (Jebel Ali)' },
    { label: 'Delivery ETA', value: '30 Oct 2026' },
    { label: 'Payment Terms', value: '30% Adv, 70% CAD' },
    { label: 'Traceability', value: 'Farm Polygon Req.', tone: 'primary' },
    { label: 'Certifications', value: 'UCDA & EUDR Comp.' },
];

/* ── Seller quotations & decision matrix ─────────────────────────────── */
const quotations = [
    {
        seller: 'Uganda Coffee Exporters Ltd', tier: 'Tier 1 Verified Exporter', lot: 'LOT-UG-001',
        note: 'Greater Masaka (1,900m) • Certified Canephora • Full Polygon Coordinates',
        price: '$4.08 / kg', value: 'Trade Val: $81,600 (20 MT)', best: true,
        metrics: [
            { label: 'Delivery', value: 'CIF Dubai (30 Oct)' },
            { label: 'Commercials', value: '30% Adv / 70% CAD' },
            { label: 'EUDR Deforestation', value: '0.00% Zero-Deforest', tone: 'primary' },
            { label: 'Cupping / Grade', value: '82.5 Screen 18+' },
        ],
        foot: 'Pre-cleared by Bean Origin Quality Lab', footIcon: 'verified', footTone: 'primary', showAward: true,
    },
    {
        seller: 'East Africa Coffee Traders', tier: 'Verified Exporter', lot: 'LOT-UG-042',
        note: 'Mubende Fine Robusta • Dry Processed • Polygon Mapping Complete',
        price: '$4.02 / kg', value: 'Trade Val: $100,500 (25 MT)',
        metrics: [
            { label: 'Delivery', value: 'CIF Dubai (25 Oct)' },
            { label: 'Commercials', value: '20% Adv / 80% CAD' },
            { label: 'EUDR Deforestation', value: '0.02% Low Risk', tone: 'primary' },
            { label: 'Cupping / Grade', value: '81.0 Screen 18' },
        ],
        foot: 'Min order batch: 25 MT (exceeds requirement by 5 MT)',
    },
    {
        seller: 'Ankole Coffee Millers Union', tier: 'Verified Cooperative', lot: 'LOT-UG-089',
        note: 'Sheema / Bushenyi District • Sun-dried FAQ Screen 18 • Partial Polygon',
        price: '$4.15 / kg', value: 'Trade Val: $83,000 (20 MT)', dim: true,
        metrics: [
            { label: 'Delivery', value: 'CIF Dubai (02 Nov)' },
            { label: 'Commercials', value: '50% Advance' },
            { label: 'EUDR Deforestation', value: 'Polygon In-Progress', tone: 'secondary' },
            { label: 'Cupping / Grade', value: '80.5 Screen 18' },
        ],
        foot: 'Payment terms high (50% adv), delivery misses 30 Oct SLA', footTone: 'error',
    },
];

const matrixRows = [
    { metric: 'Offered Price', values: ['$4.08/kg (Target: $4.10)', '$4.02/kg (Lowest)', '$4.15/kg'], toneA: 'primary' },
    { metric: 'Landed Cost Est.', values: ['$4.19/kg (Dubai Warehouse)', '$4.15/kg (Dubai Warehouse)', '$4.27/kg (Dubai Warehouse)'] },
    { metric: 'Delivery Speed', values: ['On Time (30 Oct)', '5 Days Early (25 Oct)', '3 Days Late (02 Nov)'], toneA: 'primary', toneB: 'primary', toneC: 'error' },
    { metric: 'Cupping & Defects', values: ['82.5 (Clean, Heavy Body)', '81.0 (Commercial Grade)', '80.5 (Standard FAQ)'] },
    { metric: 'EUDR Deforestation', values: ['0.00% Fully Verified', '0.02% Negligible Risk', 'Verification in Review'], toneA: 'primary', toneB: 'primary', toneC: 'secondary' },
];

/* ── Bilateral negotiation trail ─────────────────────────────────────── */
const negotiationSteps = [
    { num: 1, label: 'Buyer Target RFQ:', value: '$4.10 / kg (20 MT)', time: '18 Sep, 09:30' },
    { num: 2, label: 'Seller Initial Quote:', value: '$4.08 / kg', time: '19 Sep, 14:15' },
    { num: 3, label: 'Buyer Counter-Offer:', value: '$4.05 / kg (Quick payment terms offered)', time: '20 Sep, 11:00' },
];
const finalCounter = { num: 4, label: 'Seller Final Counter:', value: '$4.07 / kg ($81,400 Total Value)', time: 'Today, 08:45' };

const provenanceChain = [
    { label: 'Farm: Kato Smallholder (Masaka)' },
    { label: 'Collection: COL-124' },
    { label: 'Batch: BTH-048' },
    { label: 'Export Lot: LOT-UG-001', highlight: true },
    { label: 'RFQ-1048 Match', primary: true },
];

/* ── AI Sourcing Copilot ─────────────────────────────────────────────── */
const copilotWorkflows = [
    { icon: 'inventory', label: 'Find matching lots in inventory' },
    { icon: 'calculate', label: 'Compare quotes by landed cost' },
    { icon: 'verified_user', label: 'Analyze seller fulfillment reliability' },
    { icon: 'edit_note', label: 'Draft counter-offer message', scrollToNegotiation: true },
];
function runCopilotWorkflow(workflow) {
    if (workflow.scrollToNegotiation) {
        document.getElementById('negotiation-trail')?.scrollIntoView({ behavior: 'smooth' });
        return;
    }
    aiCopilotOpen.value = true;
}

/* ── Activity audit trail ────────────────────────────────────────────── */
const activityTrail = [
    { title: 'Counter Received from Seller', note: 'Uganda Coffee Exporters revised price to $4.07/kg', time: 'Today, 08:45 AM', tone: 'primary' },
    { title: 'Buyer Counter Submitted', note: 'Propose $4.05/kg with rapid letter of credit', time: '20 Sep, 11:00 AM' },
    { title: 'Quotation Submitted', note: 'East Africa Coffee Traders submitted bid $4.02/kg (25 MT)', time: '19 Sep, 16:30 PM' },
    { title: 'RFQ-1048 Published', note: 'Requisition dispatched to 14 verified institutional sellers', time: '18 Sep, 09:30 AM' },
];

/* ── Create RFQ modal — Step 1 "Physical Coffee Specifications". The
   select options come from the app's metadata tables (CropVariety /
   CropGrade / CommodityOrigin / Incoterm) passed in by RfqController.
   Submitting posts to rfq.store and creates a LotRequest row. ─────── */
const createRfqOpen = ref(false);
const rfqSpeciesOptions = computed(() => props.cropTypes);
const rfqGradeOptions = computed(() => props.grades);
const rfqOriginOptions = computed(() => props.origins);
const rfqIncotermOptions = computed(() => props.incoterms);

const rfqForm = useForm({
    crop_type: props.cropTypes[0] ?? '',
    grade: props.grades[0] ?? '',
    origin: props.origins[0] ?? '',
    incoterm: props.incoterms[0] ?? '',
    port: 'Dubai, Jebel Ali (UAE)',
    volume: 20,
    price: 4.10,
});

function openCreateRfq() {
    rfqForm.clearErrors();
    rfqForm.reset();
    createRfqOpen.value = true;
}

function publishRfq() {
    rfqForm.clearErrors();

    const volume = Number(rfqForm.volume);
    const price = Number(rfqForm.price);

    if (!rfqForm.crop_type) rfqForm.setError('crop_type', 'Select a coffee species.');
    if (!rfqForm.grade) rfqForm.setError('grade', 'Select a coffee grade.');
    if (!volume || volume <= 0) rfqForm.setError('volume', 'Enter a target volume.');
    if (!price || price <= 0) rfqForm.setError('price', 'Enter a target price.');
    if (!rfqForm.incoterm) rfqForm.setError('incoterm', 'Select an Incoterm.');

    if (Object.keys(rfqForm.errors).length) return;

    rfqForm
        .transform((data) => {
            // Volume is entered in metric tons, but lot_requests.quantity
            // is stored in kilograms; amount is the total ceiling budget
            // (price/kg × quantity).
            const quantityKg = Math.round(volume * 1000);
            return {
                crop_type: data.crop_type,
                grade: data.grade,
                origin: data.origin || null,
                incoterm: data.incoterm,
                port: data.port || null,
                quantity: quantityKg,
                amount: Math.round(price * quantityKg * 100) / 100,
            };
        })
        .post(route('rfq.store'), {
            preserveScroll: true,
            onSuccess: () => {
                createRfqOpen.value = false;
                rfqForm.reset();
            },
        });
}

/* ── Award modal (visual only) ───────────────────────────────────────── */
const awardOpen = ref(false);

/* ── Invite sellers modal (visual only) ──────────────────────────────── */
const inviteOpen = ref(false);
const inviteSellers = ref([
    { name: 'Uganda Coffee Exporters Ltd', meta: 'Tier 1 • Greater Masaka', checked: true },
    { name: 'East Africa Coffee Traders', meta: 'Verified Exporter • Mubende', checked: true },
    { name: 'Ankole Coffee Millers Union', meta: 'Verified Union • Bushenyi', checked: false },
    { name: 'Great Lakes Specialty Origin', meta: 'Specialty Arabica & Robusta', checked: false },
]);

/* ── AI Copilot modal (client-side demo, mirrors the Documentation
   page's canned-response pattern — illustrative only) ───────────────── */
const aiCopilotOpen = ref(false);
const aiCopilotQuery = ref('');
const aiCopilotAsked = ref(false);
const aiCopilotResponse = ref('');
function askCopilot() {
    const q = aiCopilotQuery.value.trim();
    if (!q) return;
    aiCopilotAsked.value = true;
    aiCopilotResponse.value = 'Based on current landed-cost modelling, a 15-day delayed shipment shifts the Dubai warehouse benchmark from $4.19/kg to roughly $4.14/kg by absorbing one additional freight consolidation cycle — worth citing if you counter below $4.05/kg.';
}
</script>

<template>
    <MainLayout title="RFQs">
        <Head title="RFQs">
            <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="rq-page">
            <!-- ── Header / hero ──────────────────────────────────────────── -->
            <div class="rq-hero">
                <div class="rq-hero__top">
                    <div class="rq-hero__copy">
                        <h1 class="rq-title">RFQs &amp; Sourcing Desk</h1>
                        <p class="rq-subtitle">Source physical coffee lots by issuing structured specifications to certified origin exporters, cooperatives, and millers with automated EUDR compliance verification.</p>
                    </div>
                    <div class="rq-hero__actions">
                        <Link :href="route('exchange.index')" class="rq-btn rq-btn--muted"><span class="material-symbols-outlined">travel_explore</span> View Exchange</Link>
                        <button type="button" class="rq-btn rq-btn--primary" @click="openCreateRfq"><span class="material-symbols-outlined">add_circle</span> + Create RFQ</button>
                    </div>
                </div>

            
            </div>

            <!-- ── KPI strip ──────────────────────────────────────────────── -->
            <div class="rq-kpis">
                <div v-for="kpi in kpis" :key="kpi.label" class="rq-card rq-kpi">
                    <div class="rq-kpi__head">
                        <span class="rq-kpi__label">{{ kpi.label }}</span>
                        <span class="material-symbols-outlined rq-tone-primary">{{ kpi.icon }}</span>
                    </div>
                    <div class="rq-kpi__value-row">
                        <span class="rq-kpi__value">{{ kpi.value }}</span>
                        <span v-if="kpi.hint" class="rq-kpi__hint">{{ kpi.hint }}</span>
                    </div>
                    <div class="rq-kpi__foot" :class="{ 'rq-tone-primary': kpi.footIcon }">
                        <span v-if="kpi.footIcon" class="material-symbols-outlined">{{ kpi.footIcon }}</span>
                        {{ kpi.foot }}
                    </div>
                </div>
            </div>

            <!-- ── Lifecycle stepper ─────────────────────────────────────── -->
            <div class="rq-card">
                <div class="rq-section-head">
                    <span class="rq-eyebrow">Standard Requisition Progression</span>
                    <span class="rq-mono-note rq-tone-primary"><span class="rq-dot"></span> Current Node: RESPONSES</span>
                </div>
                <div class="rq-stepper">
                    <div
                        v-for="step in lifecycleSteps"
                        :key="step.code"
                        class="rq-stepper__step"
                        :class="{ 'rq-stepper__step--active': step.active, 'rq-stepper__step--dim': !step.done && !step.active }"
                    >
                        <span class="rq-stepper__code">{{ step.code }}</span>
                        <span class="rq-stepper__title">{{ step.title }}</span>
                        <span class="rq-stepper__state"><span v-if="step.done" class="material-symbols-outlined">check</span><span v-else-if="step.active" class="material-symbols-outlined">hourglass_top</span> {{ step.state }}</span>
                    </div>
                </div>
            </div>

            <!-- ── Sourcing requests table ───────────────────────────────── -->
            <div class="rq-card">
                <div class="rq-section-head">
                    <div>
                        <h2 class="rq-h2">My Sourcing Requests</h2>
                        <p class="rq-muted-text">Active procurement positions issued by your trading account</p>
                    </div>
                    <div class="rq-head-actions">
                        <button type="button" class="rq-btn rq-btn--muted"><span class="material-symbols-outlined">file_download</span> Export CSV</button>
                        <button type="button" class="rq-btn rq-btn--muted"><span class="material-symbols-outlined">filter_alt</span> Filter</button>
                    </div>
                </div>

                <div class="rq-toolbar">
                    <el-input v-model="searchQuery" placeholder="Filter by RFQ ID, crop, grade..." class="rq-el-input" size="small" clearable />
                    <el-select v-model="filterType" class="rq-el-select" size="small">
                        <el-option v-for="o in filterTypeOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                    <el-select v-model="filterGrade" class="rq-el-select" size="small">
                        <el-option v-for="o in filterGradeOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                    <el-select v-model="sortBy" class="rq-el-select" size="small">
                        <el-option v-for="o in sortOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>

                <p class="rq-results-count">
                    {{ filteredRows.length }} request{{ filteredRows.length === 1 ? '' : 's' }} found
                    <span v-if="filteredRows.length">· showing {{ (currentPage - 1) * PAGE_SIZE + 1 }}–{{ Math.min(currentPage * PAGE_SIZE, filteredRows.length) }}</span>
                </p>

                <el-table :data="pagedRows" border class="rq-el-table" table-layout="fixed">
                    <el-table-column label="RFQ Identifier &amp; Product">
                        <template #default="{ row }">
                            <div class="rq-row-id">
                                <span class="rq-dot"></span>
                                <div>
                                    <span class="rq-mono rq-tone-primary" style="display:block; font-weight:800;">{{ rfqCode(row) }}</span>
                                    <span class="rq-strong">{{ [row.crop_type, row.variety, row.grade].filter(Boolean).join(' · ') }}</span>
                                    <span v-if="row.notes" class="rq-row-notes">{{ row.notes }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="Quantity &amp; Amount" width="150" align="right">
                        <template #default="{ row }">
                            <span class="rq-mono rq-strong" style="display:block;">{{ fmt(row.quantity) }} KG</span>
                            <span class="rq-mono rq-tone-primary" style="font-weight:800;">{{ row.amount ? `$${fmt(row.amount)}` : '—' }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="Requested" width="120">
                        <template #default="{ row }"><span class="rq-mono rq-muted-text">{{ fmtDate(row.created_at) }}</span></template>
                    </el-table-column>
                    <el-table-column label="Task" width="120">
                        <template #default="{ row }"><span class="rq-status" :class="`rq-status--${statusTone(row.status)}`">{{ statusLabel(row.status) }}</span></template>
                    </el-table-column>
                    <el-table-column width="230" align="right">
                        <template #header>Action</template>
                        <template #default="{ row }">
                            <div class="rq-row-actions">
                                <Link :href="route('lot.request.show', row.id)" class="rq-btn rq-btn--muted rq-btn--sm">View</Link>
                                <template v-if="!isMine(row) && row.status === 'pending'">
                                    <button type="button" class="rq-btn rq-btn--primary rq-btn--sm" @click="respond(row, 'approved')">Approve</button>
                                    <button type="button" class="rq-btn rq-btn--secondary rq-btn--sm" @click="respond(row, 'rejected')">Reject</button>
                                </template>
                                <button v-else-if="!isMine(row) && row.status === 'approved'" type="button" class="rq-btn rq-btn--primary rq-btn--sm" @click="respond(row, 'fulfilled')">Mark Fulfilled</button>
                                <button v-if="isMine(row)" type="button" class="rq-icon-btn" title="Delete" @click="requestDelete(row)">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                        </template>
                    </el-table-column>
                    <template #empty>
                        <span class="rq-muted-text">No requests for quote match your filters.</span>
                    </template>
                </el-table>

                <el-pagination
                    v-if="filteredRows.length > PAGE_SIZE"
                    v-model:current-page="currentPage"
                    :page-size="PAGE_SIZE"
                    :total="filteredRows.length"
                    layout="prev, pager, next"
                    class="rq-pagination"
                />
            </div>

            <ConfirmDialog
                v-model="deleteOpen"
                eyebrow="Trade"
                title="Delete Request for Quote"
                :message="pendingDelete ? `Delete this ${pendingDelete.crop_type} request? This can't be undone.` : ''"
                confirm-text="Delete"
                @confirm="confirmDelete"
            />

            <!-- ── Focus workspace ────────────────────────────────────────── -->
            <div id="focus-workspace" class="rq-focus">
                <div class="rq-card">
                    <div class="rq-focus-head">
                        <div class="rq-focus-head__meta">
                            <span class="rq-badge rq-badge--solid">RFQ-1048</span>
                            <span class="rq-eyebrow">Active Sourcing Requisition</span>
                            <span class="rq-badge rq-badge--fixed">Responses Received</span>
                        </div>
                        <div class="rq-head-actions">
                            <button type="button" class="rq-btn rq-btn--muted rq-btn--sm"><span class="material-symbols-outlined">edit</span> Edit Specs</button>
                            <button type="button" class="rq-btn rq-btn--muted rq-btn--sm" @click="inviteOpen = true"><span class="material-symbols-outlined">person_add</span> Invite Sellers</button>
                            <button type="button" class="rq-btn rq-btn--muted rq-btn--sm rq-tone-error"><span class="material-symbols-outlined">cancel</span> Close RFQ</button>
                            <button type="button" class="rq-btn rq-btn--primary rq-btn--sm" @click="aiCopilotOpen = true"><span class="material-symbols-outlined">psychology</span> AI Analysis</button>
                        </div>
                    </div>
                    <h2 class="rq-h2" style="margin-top:12px;">Uganda Robusta Screen 18 — 20 Metric Tons Target</h2>
                    <p class="rq-muted-text">Published 18 Sep 2026 • Closes 30 Sep 2026 • Verified Institutional Exporters Only</p>
                    <div class="rq-spec-grid">
                        <div v-for="pill in specPills" :key="pill.label" class="rq-spec-pill">
                            <span class="rq-spec-pill__label">{{ pill.label }}</span>
                            <span class="rq-spec-pill__value" :class="{ 'rq-tone-primary': pill.tone === 'primary' }">{{ pill.value }}</span>
                        </div>
                    </div>
                </div>

                <div class="rq-focus-columns">
                    <!-- Left: quotations & matrix -->
                    <div class="rq-focus-main">
                        <div class="rq-card">
                            <div class="rq-section-head">
                                <div>
                                    <h3 class="rq-h3">Seller Quotations &amp; Decision Matrix</h3>
                                    <p class="rq-muted-text">3 verified bids submitted for RFQ-1048. Select to run comparison analysis.</p>
                                </div>
                                <button type="button" class="rq-link-btn">Export Matrix</button>
                            </div>

                            <div v-for="q in quotations" :key="q.seller" class="rq-quote" :class="{ 'rq-quote--dim': q.dim }">
                                <div class="rq-quote__top">
                                    <div class="rq-quote__id">
                                        <input type="checkbox" :checked="q.best || !q.dim" disabled />
                                        <div>
                                            <div class="rq-quote__id-row">
                                                <span class="rq-strong">{{ q.seller }}</span>
                                                <span class="rq-badge" :class="q.best ? 'rq-badge--solid' : 'rq-badge--muted'">{{ q.tier }}</span>
                                                <span class="rq-badge rq-badge--muted rq-mono">{{ q.lot }}</span>
                                            </div>
                                            <p class="rq-muted-text" style="margin-top:2px;">{{ q.note }}</p>
                                        </div>
                                    </div>
                                    <div class="rq-quote__price">
                                        <span class="rq-mono" :class="q.best ? 'rq-tone-primary' : 'rq-strong'" style="font-size:16px;font-weight:800;">{{ q.price }}</span>
                                        <span class="rq-mono rq-muted-text" style="display:block;font-size:11px;">{{ q.value }}</span>
                                    </div>
                                </div>
                                <div class="rq-quote__metrics">
                                    <div v-for="m in q.metrics" :key="m.label" class="rq-quote__metric">
                                        <span>{{ m.label }}</span>
                                        <strong :class="{ 'rq-tone-primary': m.tone === 'primary', 'rq-tone-secondary': m.tone === 'secondary' }">{{ m.value }}</strong>
                                    </div>
                                </div>
                                <div class="rq-quote__foot">
                                    <span class="rq-mono-note" :class="{ 'rq-tone-primary': q.footTone === 'primary', 'rq-tone-error': q.footTone === 'error' }">
                                        <span v-if="q.footIcon" class="material-symbols-outlined">{{ q.footIcon }}</span> {{ q.foot }}
                                    </span>
                                    <div class="rq-head-actions">
                                        <button type="button" class="rq-btn rq-btn--muted rq-btn--sm">Review Quote</button>
                                        <button type="button" class="rq-btn rq-btn--secondary rq-btn--sm" @click="document.getElementById('negotiation-trail')?.scrollIntoView({ behavior: 'smooth' })" v-if="q.best">Counter</button>
                                        <button type="button" class="rq-btn rq-btn--primary rq-btn--sm" v-if="q.showAward" @click="awardOpen = true">Award &amp; Create Trade</button>
                                    </div>
                                </div>
                            </div>

                            <div class="rq-matrix">
                                <span class="rq-eyebrow">Side-by-Side Spec Decision Matrix</span>
                                <div class="rq-table-wrap">
                                    <table class="rq-table rq-table--matrix">
                                        <thead>
                                            <tr>
                                                <th>Decision Metric</th>
                                                <th class="rq-tone-primary">Uganda Coffee Exporters</th>
                                                <th>East Africa Traders</th>
                                                <th class="rq-muted-text">Ankole Millers</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="row in matrixRows" :key="row.metric">
                                                <td class="rq-strong">{{ row.metric }}</td>
                                                <td class="rq-mono" :class="{ 'rq-tone-primary': row.toneA === 'primary', 'rq-tone-error': row.toneA === 'error' }" style="font-weight:700;">{{ row.values[0] }}</td>
                                                <td class="rq-mono" :class="{ 'rq-tone-primary': row.toneB === 'primary', 'rq-tone-error': row.toneB === 'error' }">{{ row.values[1] }}</td>
                                                <td class="rq-mono" :class="{ 'rq-tone-secondary': row.toneC === 'secondary', 'rq-tone-error': row.toneC === 'error' }">{{ row.values[2] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Negotiation trail -->
                        <div id="negotiation-trail" class="rq-card">
                            <div class="rq-section-head">
                                <div>
                                    <h3 class="rq-h3">Bilateral Negotiation Trail — Uganda Coffee Exporters</h3>
                                    <p class="rq-muted-text">Live price counter sequence for LOT-UG-001 (20 MT)</p>
                                </div>
                                <span class="rq-badge rq-badge--solid">Active Stage 04</span>
                            </div>
                            <div class="rq-negotiation">
                                <div v-for="step in negotiationSteps" :key="step.num" class="rq-negotiation__step">
                                    <div class="rq-negotiation__left">
                                        <span class="rq-negotiation__num">{{ step.num }}</span>
                                        <span class="rq-muted-text">{{ step.label }}</span>
                                        <span class="rq-mono rq-strong">{{ step.value }}</span>
                                    </div>
                                    <span class="rq-mono-note">{{ step.time }}</span>
                                </div>
                                <div class="rq-negotiation__step rq-negotiation__step--final">
                                    <div class="rq-negotiation__left">
                                        <span class="rq-negotiation__num rq-negotiation__num--final">{{ finalCounter.num }}</span>
                                        <span class="rq-tone-primary rq-strong">{{ finalCounter.label }}</span>
                                        <span class="rq-mono rq-tone-primary" style="font-weight:800;font-size:14px;">{{ finalCounter.value }}</span>
                                    </div>
                                    <span class="rq-mono-note">{{ finalCounter.time }}</span>
                                </div>
                                <div class="rq-negotiation__foot">
                                    <span class="rq-muted-text">Seller note: "Can accept $4.07 CIF Dubai with 30% advance on confirmation."</span>
                                    <div class="rq-head-actions">
                                        <button type="button" class="rq-btn rq-btn--muted rq-btn--sm">Draft New Counter</button>
                                        <button type="button" class="rq-btn rq-btn--primary rq-btn--sm" @click="awardOpen = true"><span class="material-symbols-outlined">check_circle</span> Accept $4.07 &amp; Execute Trade</button>
                                    </div>
                                </div>
                            </div>

                            <div class="rq-provenance">
                                <div class="rq-section-head">
                                    <span class="rq-eyebrow">Verified Provenance &amp; Lot Origin Chain</span>
                                    <span class="rq-mono-note rq-tone-primary">Traceability Pass: 100%</span>
                                </div>
                                <div class="rq-provenance__chain">
                                    <template v-for="(node, idx) in provenanceChain" :key="node.label">
                                        <span class="rq-provenance__node" :class="{ 'rq-provenance__node--highlight': node.highlight, 'rq-provenance__node--primary': node.primary }">{{ node.label }}</span>
                                        <span v-if="idx < provenanceChain.length - 1" class="rq-provenance__arrow">→</span>
                                    </template>
                                </div>
                                <div class="rq-section-head" style="margin-top:8px;">
                                    <span class="rq-mono-note">32 individual smallholder polygon coordinates linked to this lot</span>
                                    <button type="button" class="rq-link-btn">View Lot Master Record <span class="material-symbols-outlined">open_in_new</span></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: AI copilot + market context + activity -->
                    <div class="rq-focus-aside">
                        <div class="rq-card">
                            <div class="rq-section-head">
                                <div class="rq-ai-header">
                                    <span class="rq-icon-box rq-icon-box--primary"><span class="material-symbols-outlined">smart_toy</span></span>
                                    <div>
                                        <h3 class="rq-h3" style="font-size:14px;">AI Sourcing Copilot</h3>
                                        <span class="rq-mono-note rq-tone-primary"><span class="rq-dot"></span> Model v4.2 Active</span>
                                    </div>
                                </div>
                                <span class="rq-badge rq-badge--fixed">AI ASSIST</span>
                            </div>
                            <div class="rq-insight">
                                <div class="rq-insight__tag"><span class="rq-badge rq-badge--solid">Calculation</span> <span class="rq-strong">Landed Cost Optimization</span></div>
                                <p class="rq-muted-text">Uganda Coffee Exporters' counter of <strong class="rq-on-surface">$4.08/kg</strong> offers superior value over East Africa Traders despite the $0.06/kg spread, due to <strong class="rq-on-surface">0.00% EUDR polygon risk</strong> and pre-docked vessel allocation for Oct 22.</p>
                                <div class="rq-insight__tags">
                                    <span class="rq-mono-note rq-chip">Market Spread: -$0.07/kg</span>
                                    <span class="rq-mono-note rq-chip">Fulfillment Score: 98.4%</span>
                                </div>
                            </div>
                            <span class="rq-eyebrow" style="display:block;margin-bottom:6px;">Copilot Workflows</span>
                            <div class="rq-workflow-list">
                                <button v-for="w in copilotWorkflows" :key="w.label" type="button" class="rq-workflow-btn" @click="runCopilotWorkflow(w)">
                                    <span><span class="material-symbols-outlined rq-tone-primary">{{ w.icon }}</span> {{ w.label }}</span>
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </button>
                            </div>
                        </div>

                        <div class="rq-card">
                            <div class="rq-section-head">
                                <span class="rq-eyebrow">Physical Market Benchmark</span>
                                <span class="rq-mono-note rq-tone-primary">RC FRONT MONTH</span>
                            </div>
                            <div class="rq-benchmark">
                                <div>
                                    <span class="rq-muted-text" style="display:block;font-size:11px;">Robusta Reference</span>
                                    <span class="rq-mono rq-tone-primary" style="font-size:20px;font-weight:800;">$4.15 / kg</span>
                                </div>
                                <div style="text-align:right;">
                                    <span class="rq-tone-primary rq-strong" style="display:block;font-size:11px;">+1.4% (Today)</span>
                                    <span class="rq-mono-note">NYBOT / ICE RC</span>
                                </div>
                            </div>
                            <div class="rq-benchmark-grid">
                                <div class="rq-spec-pill">
                                    <span class="rq-spec-pill__label">Active Supply</span>
                                    <span class="rq-mono rq-strong">1,284 MT</span>
                                    <span class="rq-mono-note">Uganda Verified</span>
                                </div>
                                <div class="rq-spec-pill">
                                    <span class="rq-spec-pill__label">Active Demand</span>
                                    <span class="rq-mono rq-strong">18 RFQs</span>
                                    <span class="rq-mono-note">Middle East / EU</span>
                                </div>
                            </div>
                        </div>

                        <div class="rq-card">
                            <div class="rq-section-head">
                                <h3 class="rq-h3" style="font-size:13px;">RFQ Activity Audit Trail</h3>
                                <span class="material-symbols-outlined rq-muted-text">history</span>
                            </div>
                            <div class="rq-timeline">
                                <div v-for="item in activityTrail" :key="item.title" class="rq-timeline__item">
                                    <span class="rq-timeline__dot" :class="{ 'rq-timeline__dot--primary': item.tone === 'primary' }"></span>
                                    <div>
                                        <span class="rq-strong" style="display:block;">{{ item.title }}</span>
                                        <span class="rq-muted-text" style="display:block;">{{ item.note }}</span>
                                        <span class="rq-mono-note">{{ item.time }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Create RFQ modal (visual-only Step 1 preview) ───────────── -->
        <Teleport to="body">
            <div v-if="createRfqOpen" class="rq-modal-overlay" @click.self="createRfqOpen = false">
                <div class="rq-modal">
                    <div class="rq-modal__head">
                        <div>
                            <span class="rq-eyebrow rq-tone-primary">New Sourcing Requisition</span>
                            <h2 class="rq-h2" style="margin-top:4px;">Publish Request for Quotation (RFQ)</h2>
                        </div>
                        <button type="button" class="rq-modal__close" @click="createRfqOpen = false"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <div class="rq-wizard-steps">
                        <span class="rq-wizard-steps__active">1 Specs</span>
                        <span>›</span><span>2 Terms</span><span>›</span><span>3 Quality/EUDR</span><span>›</span><span>4 Reqmts</span><span>›</span><span>5 Visibility</span><span>›</span><span>6 Review</span>
                    </div>
                    <h3 class="rq-h3" style="margin:16px 0 2px;">Step 1: Physical Coffee Specifications</h3>
                    <p class="rq-modal__hint">These details are dispatched to every certified seller matching your species, origin, and volume criteria.</p>
                    <div class="rq-form-grid">
                        <div class="rq-form-field" :class="{ 'rq-form-field--error': rfqForm.errors.crop_type }">
                            <label>Coffee Species
                                <el-tooltip content="The coffee species sellers must match — Robusta or Arabica." placement="top">
                                    <span class="material-symbols-outlined rq-field-help">info</span>
                                </el-tooltip>
                            </label>
                            <el-select v-model="rfqForm.crop_type" class="rq-el-select">
                                <template #prefix><el-icon><Coffee /></el-icon></template>
                                <el-option v-for="o in rfqSpeciesOptions" :key="o" :label="o" :value="o" />
                            </el-select>
                            <span v-if="rfqForm.errors.crop_type" class="rq-form-field__error">{{ rfqForm.errors.crop_type }}</span>
                        </div>
                        <div class="rq-form-field" :class="{ 'rq-form-field--error': rfqForm.errors.grade }">
                            <label>Coffee Grade
                                <el-tooltip content="The region grade and screen size sellers must match to qualify." placement="top">
                                    <span class="material-symbols-outlined rq-field-help">info</span>
                                </el-tooltip>
                            </label>
                            <el-select v-model="rfqForm.grade" class="rq-el-select">
                                <template #prefix><el-icon><Box /></el-icon></template>
                                <el-option v-for="o in rfqGradeOptions" :key="o" :label="o" :value="o" />
                            </el-select>
                            <span v-if="rfqForm.errors.grade" class="rq-form-field__error">{{ rfqForm.errors.grade }}</span>
                        </div>
                        <div class="rq-form-field" :class="{ 'rq-form-field--error': rfqForm.errors.origin }">
                            <label>Origin Region / Terroir
                                <el-tooltip content="Narrows the requisition to sellers sourcing from this growing region." placement="top">
                                    <span class="material-symbols-outlined rq-field-help">info</span>
                                </el-tooltip>
                            </label>
                            <el-select v-model="rfqForm.origin" class="rq-el-select">
                                <template #prefix><el-icon><LocationFilled /></el-icon></template>
                                <el-option v-for="o in rfqOriginOptions" :key="o" :label="o" :value="o" />
                            </el-select>
                            <span v-if="rfqForm.errors.origin" class="rq-form-field__error">{{ rfqForm.errors.origin }}</span>
                        </div>
                        <div class="rq-form-field" :class="{ 'rq-form-field--error': rfqForm.errors.volume }">
                            <label>Target Volume (Metric Tons)
                                <el-tooltip content="Total physical volume you need — sellers can quote partial fills against this." placement="top">
                                    <span class="material-symbols-outlined rq-field-help">info</span>
                                </el-tooltip>
                            </label>
                            <el-input-number v-model="rfqForm.volume" class="rq-el-input-number" :min="1" :max="500" controls-position="right" />
                            <span v-if="rfqForm.errors.volume" class="rq-form-field__error">{{ rfqForm.errors.volume }}</span>
                        </div>
                        <div class="rq-form-field" :class="{ 'rq-form-field--error': rfqForm.errors.price }">
                            <label>Target Price (USD / kg)
                                <el-tooltip content="Your ceiling price — quotes above this are flagged as over-budget." placement="top">
                                    <span class="material-symbols-outlined rq-field-help">info</span>
                                </el-tooltip>
                            </label>
                            <el-input v-model="rfqForm.price" class="rq-el-input" type="number" :step="0.01">
                                <template #prefix><el-icon><PriceTag /></el-icon></template>
                                <template #suffix><span class="rq-input-suffix">/ kg</span></template>
                            </el-input>
                            <span class="rq-form-field__note">Current market benchmark: $4.05 / kg</span>
                            <span v-if="rfqForm.errors.price" class="rq-form-field__error">{{ rfqForm.errors.price }}</span>
                        </div>
                        <div class="rq-form-field" :class="{ 'rq-form-field--error': rfqForm.errors.incoterm }">
                            <label>Incoterms 2020
                                <el-tooltip content="Who bears freight, insurance, and customs risk once the trade is confirmed." placement="top">
                                    <span class="material-symbols-outlined rq-field-help">info</span>
                                </el-tooltip>
                            </label>
                            <el-select v-model="rfqForm.incoterm" class="rq-el-select">
                                <template #prefix><el-icon><DocumentChecked /></el-icon></template>
                                <el-option v-for="o in rfqIncotermOptions" :key="o" :label="o" :value="o" />
                            </el-select>
                            <span v-if="rfqForm.errors.incoterm" class="rq-form-field__error">{{ rfqForm.errors.incoterm }}</span>
                        </div>
                        <div class="rq-form-field rq-form-field--span2" :class="{ 'rq-form-field--error': rfqForm.errors.port }">
                            <label>Destination Port
                                <el-tooltip content="Final discharge port — used to estimate freight cost and transit time." placement="top">
                                    <span class="material-symbols-outlined rq-field-help">info</span>
                                </el-tooltip>
                            </label>
                            <el-input v-model="rfqForm.port" class="rq-el-input" placeholder="e.g. Dubai, Jebel Ali (UAE)">
                                <template #prefix><el-icon><Ship /></el-icon></template>
                            </el-input>
                            <span v-if="rfqForm.errors.port" class="rq-form-field__error">{{ rfqForm.errors.port }}</span>
                        </div>
                    </div>
                    <div class="rq-modal__footer">
                        <SubmitButton native-type="button" :loading="rfqForm.processing" :full-width="false" class="rq-submit min-w-[220px]" @click="publishRfq">
                            {{ rfqForm.processing ? 'Publishing…' : 'Publish' }}
                        </SubmitButton>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Award & Trade Execution modal (visual only) ─────────────── -->
        <Teleport to="body">
            <div v-if="awardOpen" class="rq-modal-overlay" @click.self="awardOpen = false">
                <div class="rq-modal">
                    <div class="rq-modal__head">
                        <div>
                            <span class="rq-badge rq-badge--solid">CONFIRMATION ACTION</span>
                            <h2 class="rq-h2" style="margin-top:6px;">Award Quotation &amp; Mint Trade TRD-1052</h2>
                        </div>
                        <button type="button" class="rq-modal__close" @click="awardOpen = false"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <div class="rq-modal__summary">
                        <div class="rq-modal__row"><span>Sourcing RFQ:</span><span class="rq-mono rq-strong">RFQ-1048</span></div>
                        <div class="rq-modal__row"><span>Awarded Exporter:</span><span class="rq-strong">Uganda Coffee Exporters Ltd</span></div>
                        <div class="rq-modal__row"><span>Linked Verified Lot:</span><span class="rq-mono rq-tone-primary" style="font-weight:700;">LOT-UG-001 (Masaka)</span></div>
                        <div class="rq-modal__row"><span>Agreed Price:</span><span class="rq-mono rq-tone-primary" style="font-weight:800;">$4.07 / kg CIF Dubai</span></div>
                        <div class="rq-modal__row"><span>Contracted Volume:</span><span class="rq-mono rq-strong">20,000 kg (20 MT)</span></div>
                        <div class="rq-modal__row rq-modal__row--total"><span>Total Trade Consideration:</span><span class="rq-mono rq-tone-primary">$81,400.00 USD</span></div>
                    </div>
                    <div class="rq-modal__notice">
                        <div class="rq-strong" style="display:flex;align-items:center;gap:6px;"><span class="material-symbols-outlined">lock</span> Institutional Escrow Lock</div>
                        <p>Executing this award transitions RFQ-1048 into legally binding trade <strong>TRD-1052</strong>. Escrow requirement: 30% advance ($24,420 USD) due within 48 hours.</p>
                    </div>
                    <div class="rq-modal__footer">
                        <button type="button" class="rq-btn rq-btn--muted" @click="awardOpen = false">Cancel</button>
                        <button type="button" class="rq-btn rq-btn--primary"><span class="material-symbols-outlined">verified</span> Confirm &amp; Execute Trade TRD-1052</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Invite Sellers modal (visual only) ───────────────────────── -->
        <Teleport to="body">
            <div v-if="inviteOpen" class="rq-modal-overlay" @click.self="inviteOpen = false">
                <div class="rq-modal rq-modal--sm">
                    <div class="rq-modal__head">
                        <div>
                            <h2 class="rq-h3">Invite Verified Sellers</h2>
                            <p class="rq-muted-text">Dispatch direct RFQ notifications to certified coffee exporters</p>
                        </div>
                        <button type="button" class="rq-modal__close" @click="inviteOpen = false"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <div class="rq-invite-list">
                        <label v-for="seller in inviteSellers" :key="seller.name" class="rq-invite-row">
                            <div>
                                <span class="rq-strong" style="display:block;">{{ seller.name }}</span>
                                <span class="rq-mono-note">{{ seller.meta }}</span>
                            </div>
                            <input v-model="seller.checked" type="checkbox" />
                        </label>
                    </div>
                    <div class="rq-modal__footer">
                        <button type="button" class="rq-btn rq-btn--muted" @click="inviteOpen = false">Cancel</button>
                        <button type="button" class="rq-btn rq-btn--primary" @click="inviteOpen = false">Dispatch Invites</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── AI Sourcing Copilot modal ─────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="aiCopilotOpen" class="rq-modal-overlay" @click.self="aiCopilotOpen = false">
                <div class="rq-modal rq-modal--sm">
                    <div class="rq-modal__head">
                        <div class="rq-ai-header">
                            <span class="rq-icon-box rq-icon-box--primary"><span class="material-symbols-outlined">psychology</span></span>
                            <div>
                                <h2 class="rq-h3">Bean Origin AI Sourcing Desk Copilot</h2>
                                <p class="rq-muted-text">Deep-learning market analysis &amp; multi-variable quotation solver</p>
                            </div>
                        </div>
                        <button type="button" class="rq-modal__close" @click="aiCopilotOpen = false"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <div class="rq-insight">
                        <div class="rq-insight__tag"><span class="rq-badge rq-badge--solid">Market Insight</span> <span class="rq-strong">Masaka Robusta Supply Tightness</span></div>
                        <p class="rq-muted-text">Screen 18 Robusta inventory in Greater Masaka is currently trading at +$0.12/kg premium over standard FAQ due to heightened EU forward booking for Q4 2026. The quote from <strong class="rq-on-surface">Uganda Coffee Exporters ($4.08/kg)</strong> is in the 28th percentile of current transaction bands.</p>
                    </div>
                    <span class="rq-eyebrow" style="display:block;margin:12px 0 6px;">Ask Copilot a Query</span>
                    <div class="rq-ai-input">
                        <input v-model="aiCopilotQuery" type="text" placeholder="e.g. Generate counter-offer rationale with 15-day delayed shipment..." @keydown.enter="askCopilot" />
                        <button type="button" class="rq-btn rq-btn--primary rq-btn--sm" @click="askCopilot">Ask AI</button>
                    </div>
                    <div v-if="aiCopilotAsked" class="rq-ai-response">{{ aiCopilotResponse }}</div>
                    <div class="rq-modal__footer" style="padding-top:12px;">
                        <button type="button" class="rq-btn rq-btn--muted" @click="aiCopilotOpen = false">Close</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </MainLayout>
</template>

<style scoped>
/* ── Page-scoped token block, mapped 1:1 from the mockup's own
   "Scientific Atelier" palette — kept separate from the app's --dp-*
   theme so this page renders exactly as designed (same convention as
   the Documentation page port). ── */
.rq-page {
    --rq-primary: #004532;
    --rq-primary-container: #065f46;
    --rq-on-primary: #ffffff;
    --rq-primary-fixed: #a6f2d1;
    --rq-on-primary-fixed: #002116;
    --rq-secondary: #725a42;
    --rq-secondary-fixed: #fedcbe;
    --rq-on-secondary-fixed: #291806;
    --rq-error: #ba1a1a;
    --rq-error-container: #ffdad6;
    --rq-surface: #f7f9fb;
    --rq-surface-container-lowest: #ffffff;
    --rq-surface-container-low: #f2f4f6;
    --rq-surface-container: #eceef0;
    --rq-surface-container-high: #e6e8ea;
    --rq-on-surface: #191c1e;
    --rq-on-surface-variant: #3f4944;
    --rq-outline-variant: #bec9c2;

    font-family: var(--dp-font-sans);
    color: var(--rq-on-surface);
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.rq-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; font-size: 18px; }
.rq-tone-primary { color: var(--rq-primary) !important; }
.rq-tone-secondary { color: var(--rq-secondary) !important; }
.rq-tone-error { color: var(--rq-error) !important; }
.rq-on-surface { color: var(--rq-on-surface); }
.rq-strong { font-weight: 700; color: var(--rq-on-surface); font-size: var(--dp-content-font-size); }
.rq-muted-text { font-size: var(--dp-content-font-size); color: var(--rq-on-surface-variant); line-height: 1.6; margin: 0; }
.rq-mono { font-family: var(--dp-font-mono); }
.rq-mono-note { font-size: 10.5px; font-family: var(--dp-font-mono); color: var(--rq-on-surface-variant); display: inline-flex; align-items: center; gap: 3px; }
.rq-eyebrow { font-size: 10.5px; text-transform: uppercase; letter-spacing: .06em; font-weight: 800; color: var(--rq-on-surface-variant); }
.rq-h2 { font-size: 17px; font-weight: 800; margin: 0; color: var(--rq-on-surface); }
.rq-h3 { font-size: 14px; font-weight: 800; margin: 0; color: var(--rq-on-surface); }
.rq-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--rq-primary); display: inline-block; }
.rq-dot--muted { background: var(--rq-outline-variant); }

.rq-card { background: var(--rq-surface-container-lowest); border-radius: 10px; padding: 20px; border: 1px solid var(--dp-outline-variant); }
.rq-section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
.rq-head-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.rq-icon-box { width: 26px; height: 26px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rq-icon-box--primary { background: var(--rq-primary); color: var(--rq-on-primary); }
.rq-icon-box .material-symbols-outlined { font-size: 15px; }
.rq-ai-header { display: flex; align-items: center; gap: 8px; }

.rq-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; }
.rq-badge--fixed { background: var(--rq-primary-fixed); color: var(--rq-on-primary-fixed); font-weight: 800; }
.rq-badge--solid { background: var(--rq-primary); color: var(--rq-on-primary); font-family: var(--dp-font-mono); }
.rq-badge--muted { background: var(--rq-surface-container); color: var(--rq-on-surface); }

.rq-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; border: none; cursor: pointer; font-size: var(--dp-content-font-size); font-weight: 700; text-decoration: none; white-space: nowrap; }
.rq-btn .material-symbols-outlined { font-size: 15px; }
.rq-btn--sm { padding: 5px 10px; font-size: 11px; }
.rq-btn--muted { background: var(--rq-surface-container-low); color: var(--rq-on-surface); }
.rq-btn--muted:hover { background: var(--rq-surface-container); }
.rq-btn--primary { background: var(--rq-primary); color: var(--rq-on-primary); }
.rq-btn--primary:hover { background: var(--rq-primary-container); }
.rq-btn--primary:disabled { opacity: .5; cursor: default; }

/* ── Submit button — shared SubmitButton restyled black to match the
   app's other RFQ submit button (TradeLayout's rfq-btn--primary). ── */
.rq-modal__footer :deep(.rq-submit.el-button) {
    background: #000000;
    border-color: #000000;
    color: #ffffff;
}
.rq-modal__footer :deep(.rq-submit.el-button:hover),
.rq-modal__footer :deep(.rq-submit.el-button:focus-visible) {
    background: #1c1c1c;
    border-color: #1c1c1c;
    color: #ffffff;
}
.rq-modal__footer :deep(.rq-submit.el-button .el-icon.is-loading) {
    color: #ffffff;
}
.rq-btn--secondary { background: var(--rq-secondary-fixed); color: var(--rq-on-secondary-fixed); }
.rq-link-btn { border: none; background: none; cursor: pointer; font-size: 11px; font-weight: 700; color: var(--rq-primary); display: inline-flex; align-items: center; gap: 4px; }
.rq-link-btn .material-symbols-outlined { font-size: 13px; }

/* Hero */
.rq-hero { background: var(--rq-surface-container-lowest); border-radius: 0; padding: 0 0 20px; display: flex; flex-direction: column; gap: 16px; border-bottom: 1px solid var(--dp-outline-variant); }
.rq-hero__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.rq-hero__eyebrow { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--rq-primary); }
.rq-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.01em; margin: 6px 0 0; color: var(--rq-on-surface); }
.rq-subtitle { font-size: var(--dp-content-font-size); color: var(--rq-on-surface-variant); margin: 8px 0 0; max-width: 640px; line-height: 1.6; }
.rq-hero__actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.rq-filter-tabs { display: flex; flex-wrap: wrap; gap: 4px; }
.rq-filter-tab { border: none; cursor: pointer; padding: 7px 12px; border-radius: 7px; font-size: 11.5px; font-weight: 600; color: var(--rq-on-surface-variant); background: transparent; display: flex; align-items: center; gap: 6px; }
.rq-filter-tab span { font-size: 9.5px; font-family: var(--dp-font-mono); padding: 1px 5px; border-radius: 999px; background: var(--rq-surface-container); color: var(--rq-on-surface-variant); }
.rq-filter-tab--active { background: var(--rq-primary); color: var(--rq-on-primary); font-weight: 800; }
.rq-filter-tab--active span { background: var(--rq-primary-container); color: var(--rq-on-primary); }

/* KPI strip */
.rq-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
.rq-kpi { padding: 14px; }
.rq-kpi__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.rq-kpi__label { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--rq-on-surface-variant); }
.rq-kpi__head .material-symbols-outlined { font-size: 19px; }
.rq-kpi__value-row { display: flex; align-items: baseline; gap: 6px; }
.rq-kpi__value { font-size: 27px; font-weight: 800; color: var(--rq-on-surface); }
.rq-kpi__hint { font-size: 13px; color: var(--rq-on-surface-variant); }
.rq-kpi__foot { margin-top: 6px; font-size: 12px; font-weight: 700; color: var(--rq-on-surface-variant); display: flex; align-items: center; gap: 3px; }
.rq-kpi__foot .material-symbols-outlined { font-size: 14px; }

/* Lifecycle stepper */
.rq-stepper { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.rq-stepper__step { padding: 10px; border-radius: 8px; background: var(--rq-surface-container-low); display: flex; flex-direction: column; gap: 2px; opacity: .55; }
.rq-stepper__step--active { background: var(--rq-primary-fixed); color: var(--rq-on-primary-fixed); opacity: 1; }
.rq-stepper__step--dim { opacity: .75; }
.rq-stepper__step:not(.rq-stepper__step--active):not(.rq-stepper__step--dim) { opacity: 1; }
.rq-stepper__code { font-size: 9.5px; font-family: var(--dp-font-mono); color: var(--rq-on-surface-variant); }
.rq-stepper__step--active .rq-stepper__code { color: var(--rq-on-primary-fixed); font-weight: 800; }
.rq-stepper__title { font-size: 11.5px; font-weight: 700; color: var(--rq-on-surface); }
.rq-stepper__step--active .rq-stepper__title { color: var(--rq-on-primary-fixed); }
.rq-stepper__state { font-size: 10px; font-weight: 700; color: var(--rq-primary); display: flex; align-items: center; gap: 2px; }
.rq-stepper__state .material-symbols-outlined { font-size: 12px; }
.rq-stepper__step--active .rq-stepper__state { text-transform: uppercase; letter-spacing: .03em; }
.rq-stepper__step--dim .rq-stepper__state { color: var(--rq-on-surface-variant); }

/* Toolbar */
.rq-toolbar { display: grid; grid-template-columns: 2fr repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
/* The app's global .el-input__wrapper/.el-select__wrapper rule
   (resources/css/element-overrides.css) forces min-height:48px and
   font-size:14px with !important app-wide, which silences Element
   Plus's own size="small" classes. These toolbar fields need to
   actually render small, so they override it back with a selector
   specific enough (3 classes) to win over the global 1-class rule. */
.rq-toolbar .rq-el-input :deep(.el-input__wrapper) { background: var(--rq-surface-container-low); box-shadow: 0 0 0 1px var(--dp-outline-variant) inset !important; border-radius: 6px; min-height: 30px !important; padding-top: 0 !important; padding-bottom: 0 !important; }
.rq-toolbar .rq-el-input :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1.5px var(--rq-primary) inset !important; }
.rq-toolbar .rq-el-input :deep(.el-input__inner) { color: var(--rq-on-surface); font-family: var(--dp-font-sans); font-size: 12px !important; }
.rq-el-select { width: 100%; }
.rq-toolbar .rq-el-select :deep(.el-select__wrapper) { background: var(--rq-surface-container-low); box-shadow: 0 0 0 1px var(--dp-outline-variant) inset !important; border-radius: 6px; font-family: var(--dp-font-sans); min-height: 30px !important; padding-top: 0 !important; padding-bottom: 0 !important; }
.rq-toolbar .rq-el-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--rq-primary) inset !important; }
.rq-toolbar .rq-el-select :deep(.el-select__selected-item),
.rq-toolbar .rq-el-select :deep(.el-select__placeholder) { color: var(--rq-on-surface); font-size: 12px !important; }

.rq-results-count { margin: 0 0 8px; font-size: 11px; font-weight: 700; color: var(--rq-on-surface-variant); text-transform: uppercase; letter-spacing: .04em; }
.rq-row-notes { display: block; margin-top: 2px; font-size: 11px; color: var(--rq-on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 320px; }
.rq-pagination { display: flex; justify-content: flex-end; margin-top: 14px; }
.rq-pagination :deep(.btn-prev),
.rq-pagination :deep(.btn-next),
.rq-pagination :deep(.el-pager li) { background: var(--rq-surface-container-low); font-family: var(--dp-font-sans); }
.rq-pagination :deep(.el-pager li.is-active) { color: var(--rq-primary); }

/* Table */
.rq-table-wrap { overflow-x: auto; }
.rq-table { width: 100%; border-collapse: collapse; text-align: left; font-size: var(--dp-content-font-size); }
.rq-table thead tr { background: var(--rq-surface-container-low); }
.rq-table th { padding: 10px 12px; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--rq-on-surface-variant); white-space: nowrap; }
.rq-table td { padding: 12px; vertical-align: middle; color: var(--rq-on-surface-variant); }
.rq-table tbody tr { border-bottom: 1px solid var(--rq-surface-container-low); }
.rq-table tbody tr:last-child { border-bottom: none; }
.rq-row-id { display: flex; align-items: center; gap: 8px; }
.rq-status { padding: 2px 8px; border-radius: 999px; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; }
.rq-status--primary { background: var(--rq-primary); color: var(--rq-on-primary); }
.rq-status--secondary { background: var(--rq-secondary-fixed); color: var(--rq-on-secondary-fixed); }
.rq-status--fixed { background: var(--rq-primary-fixed); color: var(--rq-on-primary-fixed); }
.rq-status--muted { background: var(--rq-surface-container); color: var(--rq-on-surface); }
.rq-status--error { background: var(--rq-error-container); color: var(--rq-error); }
.rq-table--matrix th { font-size: 10.5px; text-transform: none; font-weight: 700; }
.rq-table--matrix td { padding: 8px; }
.rq-table--matrix tbody tr:nth-child(odd) { background: rgba(255, 255, 255, .5); }

/* Element Plus table (real My Sourcing Requests data) */
.rq-el-table { width: 100%; font-size: var(--dp-content-font-size); --el-table-border-color: var(--rq-surface-container-low); --el-table-header-bg-color: var(--rq-surface-container-low); --el-table-header-text-color: var(--rq-on-surface-variant); --el-table-row-hover-bg-color: var(--rq-surface-container-low); --el-table-text-color: var(--rq-on-surface); }
.rq-el-table :deep(.el-table__header th.el-table__cell) { padding: 10px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.rq-el-table :deep(.el-table__body td.el-table__cell) { padding: 10px 12px; }
.rq-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 6px; flex-wrap: wrap; }
.rq-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: none; border-radius: 8px; background: transparent; color: var(--rq-on-surface-variant); cursor: pointer; }
.rq-icon-btn .material-symbols-outlined { font-size: 16px; }
.rq-icon-btn:hover { background: var(--rq-error-container); color: var(--rq-error); }

/* Focus workspace */
.rq-focus { display: flex; flex-direction: column; gap: 20px; }
.rq-focus-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.rq-focus-head__meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.rq-spec-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-top: 14px; }
.rq-spec-pill { background: var(--rq-surface-container-low); border-radius: 8px; padding: 10px; display: flex; flex-direction: column; gap: 3px; }
.rq-spec-pill__label { font-size: 9.5px; font-weight: 800; text-transform: uppercase; color: var(--rq-on-surface-variant); }
.rq-spec-pill__value { font-size: var(--dp-content-font-size); font-weight: 700; color: var(--rq-on-surface); }

.rq-focus-columns { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 20px; align-items: start; }
.rq-focus-main { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.rq-focus-aside { display: flex; flex-direction: column; gap: 20px; }

/* Quotation cards */
.rq-quote { background: var(--rq-surface-container-low); border-radius: 10px; padding: 14px; margin-top: 12px; display: flex; flex-direction: column; gap: 10px; }
.rq-quote--dim { opacity: .9; }
.rq-quote__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.rq-quote__id { display: flex; align-items: flex-start; gap: 10px; }
.rq-quote__id input { margin-top: 3px; }
.rq-quote__id-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.rq-quote__price { text-align: right; }
.rq-quote__metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px; }
.rq-quote__metric { background: var(--rq-surface-container-lowest); border-radius: 6px; padding: 8px; font-size: 10.5px; display: flex; flex-direction: column; gap: 2px; }
.rq-quote__metric span { color: var(--rq-on-surface-variant); text-transform: uppercase; font-size: 9px; font-weight: 700; }
.rq-quote__metric strong { color: var(--rq-on-surface); font-size: 11.5px; }
.rq-quote__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding-top: 4px; }

.rq-matrix { margin-top: 16px; padding: 14px; border-radius: 10px; background: rgba(230, 232, 234, .4); }

/* Negotiation trail */
.rq-negotiation { background: var(--rq-surface-container-low); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.rq-negotiation__step { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; font-size: 11.5px; }
.rq-negotiation__left { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.rq-negotiation__num { width: 22px; height: 22px; border-radius: 50%; background: var(--rq-surface-container-high); display: inline-flex; align-items: center; justify-content: center; font-family: var(--dp-font-mono); font-weight: 800; font-size: 10px; }
.rq-negotiation__step--final { background: var(--rq-surface-container-lowest); border-radius: 8px; padding: 8px 10px; }
.rq-negotiation__num--final { background: var(--rq-primary); color: var(--rq-on-primary); }
.rq-negotiation__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding-top: 4px; }

.rq-provenance { margin-top: 16px; padding: 14px; border-radius: 10px; background: var(--rq-surface-container-low); }
.rq-provenance__chain { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 11px; font-family: var(--dp-font-mono); }
.rq-provenance__node { padding: 5px 9px; border-radius: 6px; background: var(--rq-surface-container-lowest); font-weight: 700; color: var(--rq-on-surface); }
.rq-provenance__node--highlight { background: var(--rq-primary-fixed); color: var(--rq-on-primary-fixed); }
.rq-provenance__node--primary { background: var(--rq-primary); color: var(--rq-on-primary); }
.rq-provenance__arrow { color: var(--rq-outline-variant); }

/* AI copilot / benchmark / timeline */
.rq-insight { background: var(--rq-surface-container-low); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.rq-insight__tag { display: flex; align-items: center; gap: 6px; font-size: 11px; }
.rq-insight__tags { display: flex; flex-wrap: wrap; gap: 6px; }
.rq-chip { background: var(--rq-surface-container); padding: 2px 7px; border-radius: 5px; }
.rq-workflow-list { display: flex; flex-direction: column; gap: 6px; margin-top: 8px; }
.rq-workflow-btn { display: flex; align-items: center; justify-content: space-between; border: none; cursor: pointer; background: var(--rq-surface-container-low); padding: 8px; border-radius: 7px; font-size: 11.5px; color: var(--rq-on-surface); }
.rq-workflow-btn:hover { background: var(--rq-surface-container); }
.rq-workflow-btn span:first-child { display: flex; align-items: center; gap: 8px; }
.rq-workflow-btn .material-symbols-outlined { font-size: 15px; }

.rq-benchmark { background: var(--rq-surface-container-low); border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; justify-content: space-between; }
.rq-benchmark-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; }
.rq-benchmark-grid .rq-spec-pill__label { font-size: 9px; }

.rq-timeline { display: flex; flex-direction: column; gap: 12px; padding-left: 4px; border-left: 2px solid var(--rq-surface-container-high); }
.rq-timeline__item { display: flex; align-items: flex-start; gap: 10px; margin-left: -6px; }
.rq-timeline__dot { width: 9px; height: 9px; border-radius: 50%; background: var(--rq-surface-container-high); margin-top: 3px; flex-shrink: 0; }
.rq-timeline__dot--primary { background: var(--rq-primary); }
.rq-timeline__item .rq-strong { font-size: 11.5px; }
.rq-timeline__item .rq-muted-text { font-size: 10.5px; }

/* Modals */
.rq-modal-overlay { position: fixed; inset: 0; background: rgba(45, 49, 51, .4); backdrop-filter: blur(4px); z-index: 1200; display: flex; align-items: center; justify-content: center; padding: 16px; }
.rq-modal { background: #ffffff; border-radius: 14px; max-width: 560px; width: 100%; padding: 24px; box-shadow: 0 20px 50px rgba(0, 0, 0, .2); font-family: var(--dp-font-sans); color: #191c1e; max-height: 90vh; overflow-y: auto; }
.rq-modal--sm { max-width: 460px; }
.rq-modal__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.rq-modal__close { border: none; background: #f2f4f6; color: #3f4944; border-radius: 7px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
.rq-modal__summary { background: #f2f4f6; border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 8px; font-size: var(--dp-content-font-size); }
.rq-modal__row { display: flex; align-items: center; justify-content: space-between; color: #3f4944; }
.rq-modal__row--total { padding-top: 8px; font-weight: 800; color: #191c1e; font-size: var(--dp-content-font-size); }
.rq-modal__notice { background: #a6f2d1; color: #002116; border-radius: 8px; padding: 10px 12px; margin-top: 14px; font-size: 11px; }
.rq-modal__notice p { margin: 4px 0 0; line-height: 1.5; }
.rq-modal__footer { display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-top: 18px; }
.rq-invite-list { display: flex; flex-direction: column; gap: 8px; }
.rq-invite-row { display: flex; align-items: center; justify-content: space-between; background: #f2f4f6; border-radius: 8px; padding: 10px 12px; cursor: pointer; }
.rq-ai-input { position: relative; display: flex; gap: 8px; align-items: center; }
.rq-ai-input input { flex: 1; background: #f2f4f6; border: none; border-radius: 8px; padding: 10px 12px; font-size: var(--dp-content-font-size); outline: none; }
.rq-ai-response { margin-top: 10px; background: #f2f4f6; border-radius: 8px; padding: 12px; font-size: 11.5px; line-height: 1.6; color: #3f4944; }

.rq-wizard-steps { display: flex; align-items: center; gap: 8px; font-size: 11px; color: #3f4944; overflow-x: auto; padding-bottom: 4px; }
.rq-wizard-steps__active { font-weight: 800; color: var(--rq-primary, #004532); }
.rq-modal__hint { margin: 0 0 12px; font-size: 12px; color: #3f4944; line-height: 1.5; }
.rq-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.rq-form-field { display: flex; flex-direction: column; gap: 5px; font-size: 11px; font-weight: 700; color: #191c1e; }
.rq-form-field label { display: flex; align-items: center; gap: 4px; }
.rq-form-field--span2 { grid-column: span 2; }
.rq-field-help { font-size: 14px; color: #94a1b2; cursor: help; }
.rq-form-field__note { font-size: 10.5px; font-weight: 500; color: #94a1b2; }
.rq-form-field__error { font-size: 10.5px; font-weight: 600; color: #c0392b; line-height: 1.4; }
.rq-form-field--error :deep(.el-select__wrapper),
.rq-form-field--error :deep(.el-input__wrapper) { box-shadow: 0 0 0 1.5px #c0392b inset !important; }
.rq-input-suffix { font-size: 11px; font-weight: 700; color: #94a1b2; }
.rq-el-input-number { width: 100%; }
.rq-el-input-number :deep(.el-input-number__decrease),
.rq-el-input-number :deep(.el-input-number__increase) { background: #f2f4f6; border-color: transparent; }
.rq-el-input-number :deep(.el-input__wrapper) { background: #f2f4f6; box-shadow: none; border-radius: 6px; }
.rq-el-input-number :deep(.el-input__inner) { font-size: var(--dp-content-font-size); font-weight: 500; color: #191c1e; text-align: left; }
.rq-modal .rq-el-input :deep(.el-input__wrapper) { background: #f2f4f6; box-shadow: none; border-radius: 6px; padding: 4px 10px; }
.rq-modal .rq-el-input :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1.5px var(--rq-primary, #004532) inset; }
.rq-modal .rq-el-input :deep(.el-input__inner) { font-size: var(--dp-content-font-size); font-weight: 500; color: #191c1e; }
.rq-modal .rq-el-select :deep(.el-select__wrapper) { background: #f2f4f6; box-shadow: none; border-radius: 6px; min-height: 36px; }
.rq-modal .rq-el-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--rq-primary, #004532) inset; }
.rq-modal .rq-el-select :deep(.el-select__selected-item) { font-size: var(--dp-content-font-size); font-weight: 500; color: #191c1e; }

@media (max-width: 1200px) {
    .rq-focus-columns { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
    .rq-stepper { grid-template-columns: repeat(2, 1fr); }
    .rq-toolbar { grid-template-columns: 1fr; }
    .rq-form-grid { grid-template-columns: 1fr; }
    .rq-form-field--span2 { grid-column: span 1; }
}
</style>
