<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ProfileTypeModal from '@/Components/Modals/ProfileTypeModal.vue';

/* ── Structural/visual port of the uploaded "Good morning, Joshua" mockup
   (code.html), restyled with this app's own --dp-* theme tokens rather
   than the mockup's own Tailwind palette. Everything below is
   illustrative dummy data — this is the app's home page, so real
   navigational shortcuts (Find Coffee, Sell Coffee, Create RFQ, Open
   Exchange, View Market Intelligence, and the KPI "View/Review/Match"
   links) are wired to their real index pages; anything tied to a fake
   dummy record (offer/lot/trade refs) stays inert, matching the pattern
   used on Batches/Lots/Tokenised. ProfileTypeModal is kept wired — the
   profile-completion gate (EnsureProfileIsComplete) depends on this page
   to let a new user submit their profile. ──────────────────────────── */
const props = defineProps({
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

const profileModalOpen = ref(!props.hasProfile);

const page = usePage();
const displayName = computed(() => {
    const user = page.props.auth?.user;
    const fullName = [user?.first_name, user?.last_name].filter(Boolean).join(' ');
    return fullName || user?.name || 'there';
});

const shortcuts = [
    { icon: 'shopping_cart_checkout', tag: 'SPOT / FORWARD', title: 'Buy Coffee', desc: 'Find available certified coffee lots and compare screen specs.', action: 'Find Coffee', href: route('market.index'), tone: 'primary' },
    { icon: 'storefront', tag: 'INVENTORY', title: 'Sell Coffee', desc: 'Register graded lots and list inventory directly to institutional roasters.', action: 'Sell Coffee', href: route('inventory.index'), tone: 'secondary' },
    { icon: 'manage_search', tag: 'ORIGIN TENDER', title: 'Source Coffee', desc: 'Create targeted RFQs with cupping minimums and FOB Mombasa constraints.', action: 'Create RFQ', href: route('rfq.index'), tone: 'primary' },
    { icon: 'balance', tag: 'DESK ESCROW', title: 'Trade Exchange', desc: 'Manage live counter-offers, auction bidding rounds, and settlement escrow.', action: 'Open Exchange', href: route('exchange.index'), tone: 'secondary' },
];

const kpiCards = [
    { label: 'Coffee Available', value: '1,284', unit: 'MT', trend: '+84 MT this week', trendIcon: 'trending_up', action: 'View', href: route('inventory.index') },
    { label: 'Active Lots', value: '146', unit: 'Lots', note: 'Across 9 port origins', action: 'View', href: route('inventory.lots') },
    { label: 'Open Offers', value: '8', badge: 'Action Needed', note: '2 expiring within 24h', action: 'Review', href: route('exchange.offers') },
    { label: 'Active RFQs', value: '18', unit: 'Sourcing', note: '3 matching your harvest specs', tone: 'primary', action: 'Match', href: route('rfq.index') },
];

const alerts = [
    { tone: 'secondary', title: 'Offer received: Buyer has made an offer on Uganda Robusta Screen 18', meta: ['20 MT @ $4.08/kg', 'FOB Mombasa'], urgent: 'Expires in 18 hrs', action: 'Review Offer', actionTone: 'primary' },
    { tone: 'primary', title: 'RFQ response received: Three registered origin exporters have responded to Bugisu AA tender', meta: ['Qty: 40 MT target', 'Avg Quote: $5.28/kg', '2 new cupping certs attached'], action: 'Compare Responses', actionTone: 'muted' },
    { tone: 'error', title: 'Payment pending: Settlement escrow funding required for Trade TRD-1048', meta: ['$81,600.00 USD', 'Beneficiary: Kyagalanyi Escrow Hub'], urgent: 'Required by 16:00 EAT', action: 'View Order', actionTone: 'muted' },
];

const marketSnapshot = [
    { label: 'London Robusta', value: '$4.15', change: '+1.8%', note: 'USD / kg • Active Sep' },
    { label: 'NY Arabica (C)', value: '$5.62', change: '+2.9%', note: 'USD / kg • Front Month' },
    { label: 'Uganda Robusta Desk', value: '$4.15', change: '+1.9%', note: 'FOB Mombasa basis' },
    { label: 'Global Sourcing Demand', value: '18 RFQs', note: 'High interest • MENA/EU', tone: 'secondary' },
];

const opportunities = [
    { coffee: 'Uganda Robusta Screen 18', ref: 'LOT-UG-001 • Natural', origin: 'Uganda', station: 'Greater Masaka', volume: '20 MT', price: '$4.08 / kg', tag: 'Competitive offer', tagTone: 'primary', actions: ['View Lot', 'Make Offer'] },
    { coffee: 'Brazil Arabica Santos', ref: 'LOT-BR-409 • NY 2/3 SS', origin: 'Brazil', station: 'Sul de Minas', volume: '15 MT', price: '$5.30 / kg', tag: 'New listing', tagTone: 'secondary', actions: ['View Lot'] },
    { coffee: 'Uganda Robusta FAQ', ref: 'LOT-UG-018 • Screen 15+', origin: 'Uganda', station: 'Masaka Node', volume: '10 MT', price: '$3.95 / kg', tag: 'RFQ match', tagTone: 'neutral', actions: ['View'] },
    { coffee: 'Rwenzori Natural AA', ref: 'LOT-RW-002 • Micro-lot', origin: 'Uganda', station: 'Kasese High Elevation', volume: '12 MT', price: '$5.15 / kg', tag: 'Top Cupping (86.5)', tagTone: 'primary', actions: ['View Lot'] },
];

const lifecycleSteps = [
    { step: '01', label: 'Farm Collection', note: 'Aggregated', active: false },
    { step: '02', label: 'Batch Milling', note: '35 MT Active', active: true },
    { step: '03', label: 'Graded Lot', note: '20 MT Ready', active: true },
    { step: '04', label: 'Product Packaging', note: 'GrainPro 60kg', active: false },
    { step: '05', label: 'Exchange Listed', note: 'Available', active: false, highlight: true },
];

const myCoffeeRows = [
    { coffee: 'Uganda Robusta Screen 18', note: 'Grade 1 • Cupping 83.25 • Moisture 11.8%', stage: 'Stage: Lot', stageTone: 'high', weight: '20 MT', avail: '15 MT Available', reserved: '5 MT Reserved', pct: 75 },
    { coffee: 'Uganda Robusta FAQ Nganda', note: 'Mityana Dry Mill • Natural Prep', stage: 'Stage: Batch', stageTone: 'secondary', weight: '35 MT', status: 'Processing (Sun Drying Yard 3)', statusNote: 'Estimated completion: 02 Oct', statusTone: 'secondary' },
];

const activityRows = [
    { activity: 'Offer received', ref: 'Ref: OFF-9482', coffee: 'Uganda Robusta', volume: '20 MT', value: '$81,600.00', status: 'Negotiating', statusTone: 'secondary', action: 'Review' },
    { activity: 'Trade confirmed', ref: 'Ref: TRD-1048', coffee: 'Brazil Arabica', volume: '15 MT', value: '$79,500.00', status: 'Confirmed (Escrow Funded)', statusTone: 'primary', action: 'View Trade' },
    { activity: 'RFQ response', ref: 'Ref: RFQ-1045', coffee: 'Uganda Robusta FAQ', volume: '10 MT', value: '$40,000.00', status: 'Under Review', statusTone: 'neutral', action: 'View' },
];

const aiPrompts = ['Find Coffee', 'Compare Lots', 'Find Buyers', 'Analyze Offer', 'Create RFQ', 'Explain Market'];

const deadlines = [
    { tone: 'error', title: 'Offer expires: Uganda counter', note: 'Tomorrow, 14:00 EAT', action: 'Review' },
    { tone: 'secondary', title: 'RFQ tender closes in 2 days', note: 'Bugisu Washed Arabica 40MT', action: 'Check' },
    { tone: 'primary', title: 'Inspection batch audit', note: 'Stanbic Silo B-14 batch verification (28 Sep)', scheduled: true },
];

const recentActivity = [
    { title: 'Offer received from Dubai Coffee Trading', time: '10:42 AM • Lot LOT-UG-001', tone: 'primary' },
    { title: 'Lot LOT-UG-001 listed on Exchange', time: 'Yesterday, 17:15 • 20 MT Screen 18' },
    { title: 'RFQ RFQ-1048 published to verified network', time: '20 Sep • 40 MT Bugisu Arabica' },
    { title: 'Trade TRD-1045 completed & settled', time: '19 Sep • Escrow released to seller' },
];

const news = [
    { tag: 'Uganda Market', time: 'Today', title: 'UCDA records 14% export volume surge for Screen 18 Robusta', body: 'Stronger farmgate realisations reported across Central Region as drying infrastructure improves quality scores.', tone: 'primary' },
    { tag: 'Global Robusta', time: 'Yesterday', title: 'London ICE futures firm on Southeast Asian supply tightening', body: 'Dry spells across early flowering zones prompt commercial roasters to increase spot allocations in East Africa.', tone: 'secondary' },
    { tag: 'EUDR Compliance', time: '21 Sep', title: 'New geolocation polygon requirements active for Q4 shipments', body: 'Bean Origin verifies all listed Ugandan smallholder farm coordinates automatically upon lot submission.', tone: 'muted' },
];
</script>

<template>
    <MainLayout title="Coffee Intelligence Center">
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="gd-page">
            <!-- ── Welcome banner ────────────────────────────────────────── -->
            <div class="gd-hero">
                <div class="gd-hero__text">
                    <h1 class="gd-title">Market Intelligence</h1>
                    <p class="gd-subtitle">Here is what is moving across your physical positions and trade corridors today.</p>
                </div>
                <div class="gd-hero__actions">
                    <div class="gd-pill">
                        <span class="material-symbols-outlined gd-tone-text">verified</span>
                        <span class="gd-strong">Verified Member</span>
                        <span class="gd-tag gd-tag--fixed">Tier 1</span>
                    </div>
                    <div class="gd-pill">
                        <span class="material-symbols-outlined gd-muted">hub</span>
                        <span class="gd-muted">Active Node:</span>
                        <span class="gd-strong">Kampala / Mombasa Desk</span>
                    </div>
                    <button type="button" class="gd-btn gd-btn--primary">
                        <span class="material-symbols-outlined">add_circle</span> Quick Post Lot
                    </button>
                </div>
            </div>

            <!-- ── Trading desk shortcuts ────────────────────────────────── -->
            <div class="gd-section">
                <div class="gd-section__head">
                    <span class="gd-eyebrow">Trading Desk Shortcuts</span>
                    <span class="gd-muted gd-small">Instant Execution Directives</span>
                </div>
                <div class="gd-shortcuts">
                    <div v-for="s in shortcuts" :key="s.title" class="gd-shortcut">
                        <div class="gd-shortcut__top">
                            <span class="gd-shortcut__icon" :class="`gd-shortcut__icon--${s.tone}`"><span class="material-symbols-outlined">{{ s.icon }}</span></span>
                            <span class="gd-shortcut__tag">{{ s.tag }}</span>
                        </div>
                        <div>
                            <h3 class="gd-shortcut__title">{{ s.title }}</h3>
                            <p class="gd-shortcut__desc">{{ s.desc }}</p>
                        </div>
                        <Link :href="s.href" class="gd-shortcut__btn">
                            <span>{{ s.action }}</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ── KPI summary row ───────────────────────────────────────── -->
            <div class="gd-kpi-grid">
                <div v-for="kpi in kpiCards" :key="kpi.label" class="gd-kpi">
                    <div>
                        <span class="gd-kpi__label">{{ kpi.label }}</span>
                        <div class="gd-kpi__value">
                            <span :class="kpi.tone === 'primary' ? 'gd-tone-text' : ''">{{ kpi.value }}</span>
                            <span v-if="kpi.unit" class="gd-kpi__unit">{{ kpi.unit }}</span>
                            <span v-if="kpi.badge" class="gd-tag gd-tag--secondary">{{ kpi.badge }}</span>
                        </div>
                        <span v-if="kpi.trend" class="gd-kpi__trend"><span class="material-symbols-outlined">{{ kpi.trendIcon }}</span>{{ kpi.trend }}</span>
                        <span v-else-if="kpi.note" class="gd-kpi__note" :class="kpi.tone === 'primary' ? 'gd-tone-text' : ''">{{ kpi.note }}</span>
                    </div>
                    <Link :href="kpi.href" class="gd-kpi__btn">{{ kpi.action }}</Link>
                </div>
            </div>

            <!-- ── Two-column workspace ─────────────────────────────────── -->
            <div class="gd-grid">
                <!-- ── Left column ──────────────────────────────────────── -->
                <div class="gd-col-main">
                    <!-- Action Required -->
                    <section class="gd-panel">
                        <div class="gd-panel__head">
                            <div class="gd-panel__head-left">
                                <span class="material-symbols-outlined gd-tone-secondary">notification_important</span>
                                <h2 class="gd-panel__title">Action Required</h2>
                            </div>
                            <span class="gd-muted gd-small">{{ alerts.length }} priority items pending settlement</span>
                        </div>
                        <div class="gd-alerts">
                            <div v-for="alert in alerts" :key="alert.title" class="gd-alert">
                                <div class="gd-alert__left">
                                    <span class="gd-alert__dot" :class="`gd-alert__dot--${alert.tone}`"></span>
                                    <div>
                                        <p class="gd-alert__title">{{ alert.title }}</p>
                                        <div class="gd-alert__meta">
                                            <template v-for="(m, i) in alert.meta" :key="i">
                                                <span v-if="i > 0">•</span>
                                                <span :class="i === 0 ? 'gd-mono gd-strong' : ''">{{ m }}</span>
                                            </template>
                                            <template v-if="alert.urgent">
                                                <span>•</span>
                                                <span class="gd-tone-error gd-strong">{{ alert.urgent }}</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="gd-alert__btn" :class="`gd-alert__btn--${alert.actionTone}`">
                                    <span>{{ alert.action }}</span>
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- Coffee Market Snapshot -->
                    <section class="gd-section-sm">
                        <div class="gd-section__head">
                            <div class="gd-panel__head-left">
                                <span class="material-symbols-outlined gd-tone-text">query_stats</span>
                                <h2 class="gd-panel__title">Coffee Market Snapshot</h2>
                            </div>
                            <Link :href="route('market-intelligence.index')" class="gd-link">
                                <span>View Market Intelligence</span>
                                <span class="material-symbols-outlined">trending_flat</span>
                            </Link>
                        </div>
                        <div class="gd-market-grid">
                            <div v-for="m in marketSnapshot" :key="m.label" class="gd-market-card">
                                <span class="gd-eyebrow-xs">{{ m.label }}</span>
                                <div class="gd-market-card__value">
                                    <span class="gd-mono gd-strong">{{ m.value }}</span>
                                    <span v-if="m.change" class="gd-tone-text gd-small gd-strong">{{ m.change }}</span>
                                </div>
                                <span class="gd-muted gd-small" :class="m.tone === 'secondary' ? 'gd-tone-secondary gd-strong' : ''">{{ m.note }}</span>
                            </div>
                        </div>
                    </section>

                    <!-- Live Market Opportunities -->
                    <section class="gd-section-sm">
                        <div class="gd-section__head">
                            <div class="gd-panel__head-left">
                                <span class="material-symbols-outlined gd-tone-text">local_offer</span>
                                <h2 class="gd-panel__title">Live Market Opportunities</h2>
                            </div>
                            <span class="gd-muted gd-small">Filtered by your trade profile &amp; cupping appetite</span>
                        </div>
                        <div class="gd-table-card">
                            <div class="gd-table-wrap">
                                <table class="gd-table">
                                    <colgroup>
                                        <col style="width: 24%" />
                                        <col style="width: 17%" />
                                        <col style="width: 10%" />
                                        <col style="width: 13%" />
                                        <col style="width: 17%" />
                                        <col style="width: 19%" />
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>Coffee &amp; Lot</th>
                                            <th>Origin / Station</th>
                                            <th>Volume</th>
                                            <th>Offer Price</th>
                                            <th>Opportunity Context</th>
                                            <th class="gd-table__end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in opportunities" :key="row.ref">
                                            <td>
                                                <div class="gd-strong">{{ row.coffee }}</div>
                                                <div class="gd-mono gd-muted gd-small">{{ row.ref }}</div>
                                            </td>
                                            <td>
                                                <div class="gd-strong gd-small">{{ row.origin }}</div>
                                                <div class="gd-muted gd-small">{{ row.station }}</div>
                                            </td>
                                            <td class="gd-mono gd-strong gd-small">{{ row.volume }}</td>
                                            <td class="gd-mono gd-strong gd-small">{{ row.price }}</td>
                                            <td><span class="gd-tag" :class="`gd-tag--${row.tagTone}`">{{ row.tag }}</span></td>
                                            <td class="gd-table__end">
                                                <div class="gd-row-actions">
                                                    <button v-for="a in row.actions" :key="a" type="button" class="gd-mini-btn" :class="{ 'gd-mini-btn--primary': a === 'Make Offer' }">{{ a }}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <!-- My Coffee • Physical Batches -->
                    <section class="gd-section-sm">
                        <div class="gd-section__head">
                            <div class="gd-panel__head-left">
                                <span class="material-symbols-outlined gd-tone-text">inventory_2</span>
                                <h2 class="gd-panel__title">My Coffee • Physical Batches</h2>
                            </div>
                            <button type="button" class="gd-btn gd-btn--primary gd-btn--sm">
                                <span class="material-symbols-outlined">add</span> Add Coffee
                            </button>
                        </div>

                        <div class="gd-lifecycle">
                            <span class="gd-eyebrow-xs">Origin Value Chain Pipeline</span>
                            <div class="gd-lifecycle__track">
                                <div v-for="step in lifecycleSteps" :key="step.step" class="gd-lifecycle__step" :class="{ 'gd-lifecycle__step--active': step.active }">
                                    <span class="gd-mono gd-small">STEP {{ step.step }}</span>
                                    <span class="gd-strong gd-small">{{ step.label }}</span>
                                    <span class="gd-small" :class="step.highlight ? 'gd-tone-text gd-strong' : ''">{{ step.note }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="gd-table-card">
                            <div class="gd-table-wrap">
                                <table class="gd-table">
                                    <colgroup>
                                        <col style="width: 26%" />
                                        <col style="width: 15%" />
                                        <col style="width: 13%" />
                                        <col style="width: 34%" />
                                        <col style="width: 12%" />
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>Coffee Asset</th>
                                            <th>Stage</th>
                                            <th>Total Weight</th>
                                            <th>Allocation &amp; Warehouse Status</th>
                                            <th class="gd-table__end">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in myCoffeeRows" :key="row.coffee">
                                            <td>
                                                <div class="gd-strong">{{ row.coffee }}</div>
                                                <div class="gd-muted gd-small">{{ row.note }}</div>
                                            </td>
                                            <td><span class="gd-tag gd-tag--mono" :class="row.stageTone === 'secondary' ? 'gd-tag--secondary' : 'gd-tag--neutral'">{{ row.stage }}</span></td>
                                            <td class="gd-mono gd-strong gd-small">{{ row.weight }}</td>
                                            <td>
                                                <template v-if="row.pct">
                                                    <div class="gd-alloc">
                                                        <span class="gd-tone-text gd-strong gd-small">{{ row.avail }}</span>
                                                        <span class="gd-muted gd-small">/ {{ row.reserved }}</span>
                                                    </div>
                                                    <div class="gd-bar"><div class="gd-bar__fill" :style="{ width: row.pct + '%' }"></div></div>
                                                </template>
                                                <template v-else>
                                                    <div class="gd-alloc"><span class="gd-dot" :class="`gd-dot--${row.statusTone}`"></span><span class="gd-strong gd-small">{{ row.status }}</span></div>
                                                    <span class="gd-muted gd-small">{{ row.statusNote }}</span>
                                                </template>
                                            </td>
                                            <td class="gd-table__end"><button type="button" class="gd-mini-btn">View</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <!-- Trading Activity -->
                    <section class="gd-section-sm">
                        <div class="gd-section__head">
                            <div class="gd-panel__head-left">
                                <span class="material-symbols-outlined gd-tone-text">swap_horiz</span>
                                <h2 class="gd-panel__title">Trading Activity • Live Ledger</h2>
                            </div>
                            <span class="gd-muted gd-small">FOB and Ex-Warehouse settlement states</span>
                        </div>
                        <div class="gd-table-card">
                            <div class="gd-table-wrap">
                                <table class="gd-table">
                                    <colgroup>
                                        <col style="width: 20%" />
                                        <col style="width: 17%" />
                                        <col style="width: 12%" />
                                        <col style="width: 16%" />
                                        <col style="width: 20%" />
                                        <col style="width: 15%" />
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>Activity</th>
                                            <th>Coffee Spec</th>
                                            <th>Volume</th>
                                            <th>Contract Value</th>
                                            <th>Escrow Status</th>
                                            <th class="gd-table__end">Manage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in activityRows" :key="row.ref">
                                            <td>
                                                <div class="gd-strong">{{ row.activity }}</div>
                                                <div class="gd-mono gd-muted gd-small">{{ row.ref }}</div>
                                            </td>
                                            <td class="gd-strong gd-small">{{ row.coffee }}</td>
                                            <td class="gd-mono gd-small">{{ row.volume }}</td>
                                            <td class="gd-mono gd-strong gd-small">{{ row.value }}</td>
                                            <td><span class="gd-tag" :class="`gd-tag--${row.statusTone}`">{{ row.status }}</span></td>
                                            <td class="gd-table__end"><button type="button" class="gd-mini-btn">{{ row.action }}</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ── Right column ─────────────────────────────────────── -->
                <div class="gd-col-side">
                    <!-- Bean Origin AI -->
                    <section class="gd-card">
                        <div class="gd-card__head">
                            <div class="gd-card__head-left">
                                <span class="gd-card__icon"><span class="material-symbols-outlined">smart_toy</span></span>
                                <h2 class="gd-card__title">Bean Origin AI</h2>
                            </div>
                            <span class="gd-mono gd-tone-text gd-strong gd-tiny">Llama-3 Fine-tuned</span>
                        </div>
                        <div class="gd-ai-prompt">
                            <p>"Find me 10 tonnes of Ugandan Robusta under $4/kg with delivery to Dubai."</p>
                            <div class="gd-ai-prompt__foot">
                                <span>Market matching confidence</span>
                                <span class="gd-tone-text gd-strong">94.8% Match</span>
                            </div>
                        </div>
                        <div class="gd-ai-templates">
                            <span class="gd-eyebrow-xs">Prompt Templates</span>
                            <div class="gd-chip-row">
                                <button v-for="p in aiPrompts" :key="p" type="button" class="gd-chip">{{ p }}</button>
                            </div>
                        </div>
                        <Link :href="route('apps.index')" class="gd-btn gd-btn--primary gd-btn--block">
                            <span>Open AI Commerce Copilot</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </Link>
                    </section>

                    <!-- Logistics Snapshot -->
                    <section class="gd-card">
                        <div class="gd-card__head">
                            <div class="gd-card__head-left">
                                <span class="material-symbols-outlined gd-tone-text">anchor</span>
                                <h2 class="gd-card__title">Logistics Snapshot</h2>
                            </div>
                            <span class="gd-link gd-link--sm">Full Map</span>
                        </div>
                        <div class="gd-logi-row">
                            <div><span class="gd-eyebrow-xs">Port Congestion</span><p class="gd-strong gd-small">Dubai (Jebel Ali)</p></div>
                            <div class="gd-logi-row__right">
                                <span class="gd-tag gd-tag--secondary">Moderate</span>
                                <span class="gd-muted gd-tiny">2.1 days wait</span>
                            </div>
                        </div>
                        <div class="gd-logi-row">
                            <div><span class="gd-eyebrow-xs">Corridor Transit Time</span><p class="gd-strong gd-small">Mombasa → Dubai</p></div>
                            <div class="gd-logi-row__right">
                                <span class="gd-mono gd-strong gd-small">18–22 days</span>
                                <span class="gd-tone-text gd-tiny">Normal routing</span>
                            </div>
                        </div>
                        <div class="gd-logi-block">
                            <div class="gd-logi-block__head">
                                <span class="gd-eyebrow-xs">Upcoming Delivery</span>
                                <span class="gd-tag gd-tag--fixed">30 Sep</span>
                            </div>
                            <p class="gd-strong gd-small">20 MT Uganda Robusta Screen 18</p>
                            <p class="gd-muted gd-small gd-mono"><span class="material-symbols-outlined">directions_boat</span> Vessel: MSC Geneva (Voyage 804W)</p>
                        </div>
                        <button type="button" class="gd-btn gd-btn--muted gd-btn--block">
                            <span>View Logistics Intelligence</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </section>

                    <!-- Upcoming & Deadlines -->
                    <section class="gd-card">
                        <div class="gd-card__head">
                            <div class="gd-card__head-left">
                                <span class="material-symbols-outlined gd-tone-secondary">event_upcoming</span>
                                <h2 class="gd-card__title">Upcoming &amp; Deadlines</h2>
                            </div>
                            <span class="gd-muted gd-tiny">EAT (UTC+3)</span>
                        </div>
                        <div class="gd-deadline" v-for="d in deadlines" :key="d.title">
                            <div>
                                <div class="gd-deadline__title"><span class="gd-dot" :class="`gd-dot--${d.tone}`"></span><span class="gd-strong gd-small">{{ d.title }}</span></div>
                                <span class="gd-muted gd-tiny">{{ d.note }}</span>
                            </div>
                            <button v-if="d.action" type="button" class="gd-mini-btn">{{ d.action }}</button>
                            <span v-else class="gd-mono gd-muted gd-tiny">Scheduled</span>
                        </div>
                    </section>

                    <!-- Recent Activity -->
                    <section class="gd-card">
                        <div class="gd-card__head">
                            <div class="gd-card__head-left">
                                <span class="material-symbols-outlined gd-tone-text">history</span>
                                <h2 class="gd-card__title">Recent Activity</h2>
                            </div>
                            <span class="gd-muted gd-tiny">Audit Stream</span>
                        </div>
                        <div class="gd-timeline">
                            <div v-for="(item, i) in recentActivity" :key="i" class="gd-timeline__item" :class="{ 'gd-timeline__item--active': i === 0 }">
                                <span class="gd-timeline__dot"></span>
                                <p class="gd-strong gd-small">{{ item.title }}</p>
                                <p class="gd-muted gd-tiny">{{ item.time }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Desk Dispatch News -->
                    <section class="gd-card">
                        <div class="gd-card__head">
                            <div class="gd-card__head-left">
                                <span class="material-symbols-outlined gd-tone-text">newspaper</span>
                                <h2 class="gd-card__title">Desk Dispatch • News</h2>
                            </div>
                            <span class="gd-tone-text gd-strong gd-tiny">Live Wire</span>
                        </div>
                        <div class="gd-news">
                            <div v-for="n in news" :key="n.title" class="gd-news__item">
                                <div class="gd-news__head">
                                    <span class="gd-tiny gd-strong" :class="n.tone === 'secondary' ? 'gd-tone-secondary' : (n.tone === 'muted' ? 'gd-muted' : 'gd-tone-text')">{{ n.tag }}</span>
                                    <span class="gd-muted gd-tiny">{{ n.time }}</span>
                                </div>
                                <p class="gd-news__title">{{ n.title }}</p>
                                <p class="gd-muted gd-tiny gd-news__body">{{ n.body }}</p>
                            </div>
                        </div>
                        <button type="button" class="gd-btn gd-btn--muted gd-btn--block">
                            <span>View Coffee News Wire</span>
                            <span class="material-symbols-outlined">open_in_new</span>
                        </button>
                    </section>
                </div>
            </div>
        </div>

        <ProfileTypeModal v-model="profileModalOpen" />
    </MainLayout>
</template>

<style scoped>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

.gd-page { font-family: var(--dp-font-sans); display: flex; flex-direction: column; gap: 24px; color: var(--dp-on-surface); }
.gd-mono { font-family: var(--dp-font-mono); }
.gd-muted { color: var(--dp-on-surface-variant); }
.gd-strong { font-weight: 700; color: var(--dp-on-surface); }
.gd-small { font-size: 11px; }
.gd-tiny { font-size: 10px; }
.gd-tone-text { color: var(--dp-primary); }
.gd-tone-secondary { color: var(--dp-on-secondary-container); }
.gd-tone-error { color: var(--dp-error); }
.gd-eyebrow { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface-variant); }
.gd-eyebrow-xs { display: block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); margin-bottom: 4px; }

/* Hero */
.gd-hero { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 4px 0; }
.gd-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; color: var(--dp-on-surface); margin: 0; }
.gd-subtitle { font-size: 13px; color: var(--dp-on-surface-variant); margin: 4px 0 0; }
.gd-hero__actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.gd-pill { display: inline-flex; align-items: center; gap: 6px; background: var(--dp-surface-container-low); padding: 7px 12px; border-radius: 8px; font-size: 12px; }
.gd-pill .material-symbols-outlined { font-size: 16px; }
.gd-tag { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; white-space: nowrap; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.gd-tag--fixed { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.gd-tag--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.gd-tag--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.gd-tag--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.gd-tag--mono { font-family: var(--dp-font-mono); }

.gd-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 16px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); text-decoration: none; transition: opacity .12s ease, background .12s ease; }
.gd-btn .material-symbols-outlined { font-size: 16px; }
.gd-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.gd-btn--primary:hover { opacity: .9; }
.gd-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.gd-btn--muted:hover { background: var(--dp-surface-container-highest); }
.gd-btn--sm { height: 30px; padding: 0 12px; font-size: 11px; }
.gd-btn--block { width: 100%; }

/* Sections */
.gd-section { display: flex; flex-direction: column; gap: 12px; }
.gd-section-sm { display: flex; flex-direction: column; gap: 10px; }
.gd-section__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.gd-panel__head-left { display: flex; align-items: center; gap: 8px; }
.gd-panel__title { font-size: .8125rem; font-weight: 800; text-transform: uppercase; letter-spacing: .02em; color: var(--dp-on-surface); margin: 0; }
.gd-link { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; color: var(--dp-primary); text-decoration: none; }
.gd-link .material-symbols-outlined { font-size: 14px; }
.gd-link--sm { font-size: 11px; cursor: pointer; }

/* Shortcuts */
.gd-shortcuts { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
.gd-shortcut { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 10px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; }
.gd-shortcut__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.gd-shortcut__icon { width: 36px; height: 36px; border-radius: 8px; background: var(--dp-surface-container); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.gd-shortcut__icon--primary { color: var(--dp-primary); }
.gd-shortcut__icon--secondary { color: var(--dp-on-secondary-container); }
.gd-shortcut__tag { font-size: 9.5px; font-weight: 800; font-family: var(--dp-font-mono); color: var(--dp-on-surface-variant); background: var(--dp-surface-container-high); padding: 2px 7px; border-radius: 4px; white-space: nowrap; }
.gd-shortcut__title { font-size: .875rem; font-weight: 800; color: var(--dp-on-surface); margin: 0 0 4px; }
.gd-shortcut__desc { font-size: 11.5px; color: var(--dp-on-surface-variant); line-height: 1.5; margin: 0; }
.gd-shortcut__btn { display: flex; align-items: center; justify-content: center; gap: 6px; background: var(--dp-surface-container); color: var(--dp-on-surface); font-size: 11.5px; font-weight: 700; padding: 8px 12px; border-radius: 6px; text-decoration: none; transition: background .12s ease, color .12s ease; }
.gd-shortcut__btn:hover { background: var(--dp-primary); color: var(--dp-on-primary); }
.gd-shortcut__btn .material-symbols-outlined { font-size: 14px; }

/* KPI row */
.gd-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
.gd-kpi { background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant); border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.gd-kpi__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); }
.gd-kpi__value { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; margin-top: 4px; }
.gd-kpi__value > span:first-child { font-size: 1.375rem; font-weight: 800; color: var(--dp-on-surface); }
.gd-kpi__unit { font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant); }
.gd-kpi__trend { display: flex; align-items: center; gap: 2px; font-size: 11px; font-weight: 600; color: var(--dp-primary); margin-top: 2px; }
.gd-kpi__trend .material-symbols-outlined { font-size: 14px; }
.gd-kpi__note { display: block; font-size: 11px; color: var(--dp-on-surface-variant); margin-top: 2px; }
.gd-kpi__btn { flex-shrink: 0; background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); font-size: 11px; font-weight: 700; padding: 7px 11px; border-radius: 6px; text-decoration: none; }
.gd-kpi__btn:hover { background: var(--dp-surface-container-high); }

/* Two-column grid */
.gd-grid { display: grid; grid-template-columns: minmax(0, 8fr) minmax(300px, 4fr); gap: 24px; align-items: start; }
.gd-col-main { display: flex; flex-direction: column; gap: 24px; min-width: 0; }
.gd-col-side { display: flex; flex-direction: column; gap: 20px; }
@media (max-width: 1180px) { .gd-grid { grid-template-columns: 1fr; } }

/* Panel (Action Required) */
.gd-panel { background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; gap: 14px; }
.gd-panel__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.gd-alerts { display: flex; flex-direction: column; gap: 8px; }
.gd-alert { background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.gd-alert__left { display: flex; align-items: flex-start; gap: 10px; }
.gd-alert__dot { width: 8px; height: 8px; border-radius: 999px; margin-top: 5px; flex-shrink: 0; }
.gd-alert__dot--secondary { background: var(--dp-secondary); }
.gd-alert__dot--primary { background: var(--dp-primary); }
.gd-alert__dot--error { background: var(--dp-error); }
.gd-alert__title { font-size: 12px; font-weight: 600; color: var(--dp-on-surface); margin: 0; }
.gd-alert__meta { display: flex; align-items: center; gap: 6px; font-size: 10.5px; color: var(--dp-on-surface-variant); margin-top: 3px; flex-wrap: wrap; }
.gd-alert__btn { flex-shrink: 0; display: inline-flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 700; padding: 7px 12px; border-radius: 6px; border: none; cursor: pointer; font-family: var(--dp-font-sans); }
.gd-alert__btn .material-symbols-outlined { font-size: 14px; }
.gd-alert__btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.gd-alert__btn--muted { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.gd-alert__btn--muted:hover { background: var(--dp-surface-container-high); }

/* Market snapshot */
.gd-market-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
.gd-market-card { background: var(--dp-surface-container-low); border-radius: 8px; padding: 10px 12px; }
.gd-market-card__value { display: flex; align-items: baseline; gap: 6px; margin: 3px 0; }
.gd-market-card__value .gd-mono { font-size: 1.0625rem; }

/* Tables */
.gd-table-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 10px; overflow: hidden; }
.gd-table-wrap { overflow-x: hidden; }
.gd-table { width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: 11.5px; }
.gd-table thead tr { background: var(--dp-surface-container-low); }
.gd-table th { padding: 10px 14px; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); overflow-wrap: break-word; }
.gd-table td { padding: 11px 14px; overflow-wrap: break-word; vertical-align: middle; }
.gd-table tbody tr:hover { background: color-mix(in srgb, var(--dp-surface-container-low) 60%, transparent); }
.gd-table__end { text-align: right; }
.gd-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 6px; flex-wrap: wrap; }

.gd-mini-btn { padding: 6px 10px; border-radius: 6px; border: none; background: var(--dp-surface-container); color: var(--dp-on-surface); font-size: 10.5px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); white-space: nowrap; }
.gd-mini-btn:hover { background: var(--dp-surface-container-high); }
.gd-mini-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.gd-mini-btn--primary:hover { opacity: .9; }

/* Lifecycle strip */
.gd-lifecycle { background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant); border-radius: 10px; padding: 14px; }
.gd-lifecycle__track { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; text-align: center; }
.gd-lifecycle__step { background: var(--dp-surface-container-lowest); border-radius: 6px; padding: 8px 6px; display: flex; flex-direction: column; gap: 2px; }
.gd-lifecycle__step--active { background: var(--dp-primary); color: var(--dp-on-primary); }
.gd-lifecycle__step--active span { color: var(--dp-on-primary); }
@media (max-width: 720px) { .gd-lifecycle__track { grid-template-columns: repeat(2, 1fr); } }

.gd-alloc { display: flex; align-items: center; gap: 6px; margin-bottom: 3px; flex-wrap: wrap; }
.gd-bar { width: 100%; max-width: 160px; height: 5px; border-radius: 999px; background: var(--dp-surface-container-highest); overflow: hidden; }
.gd-bar__fill { height: 100%; background: var(--dp-primary); }
.gd-dot { width: 7px; height: 7px; border-radius: 999px; flex-shrink: 0; }
.gd-dot--secondary { background: var(--dp-secondary); }
.gd-dot--primary { background: var(--dp-primary); }
.gd-dot--error { background: var(--dp-error); }

/* Sidebar cards */
.gd-card { background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.gd-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.gd-card__head-left { display: flex; align-items: center; gap: 8px; }
.gd-card__title { font-size: .8125rem; font-weight: 800; text-transform: uppercase; letter-spacing: .02em; color: var(--dp-on-surface); margin: 0; }
.gd-card__icon { width: 24px; height: 24px; border-radius: 6px; background: var(--dp-primary); color: var(--dp-on-primary); display: flex; align-items: center; justify-content: center; }
.gd-card__icon .material-symbols-outlined { font-size: 14px; }

.gd-ai-prompt { background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 12px; }
.gd-ai-prompt p { font-size: 11px; color: var(--dp-on-surface); line-height: 1.5; margin: 0; font-style: italic; }
.gd-ai-prompt__foot { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; padding-top: 8px; font-size: 10px; color: var(--dp-on-surface-variant); }
.gd-ai-templates { display: flex; flex-direction: column; gap: 6px; }
.gd-chip-row { display: flex; flex-wrap: wrap; gap: 6px; }
.gd-chip { padding: 5px 10px; border-radius: 6px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); font-size: 11px; font-weight: 600; cursor: pointer; font-family: var(--dp-font-sans); }
.gd-chip:hover { background: var(--dp-surface-container-high); }

.gd-logi-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 10px; }
.gd-logi-row p { margin: 2px 0 0; }
.gd-logi-row__right { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; }
.gd-logi-block { background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 10px; display: flex; flex-direction: column; gap: 4px; }
.gd-logi-block__head { display: flex; align-items: center; justify-content: space-between; }
.gd-logi-block p { margin: 0; }
.gd-logi-block .material-symbols-outlined { font-size: 13px; vertical-align: -2px; margin-right: 2px; }

.gd-deadline { display: flex; align-items: center; justify-content: space-between; gap: 8px; background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 10px; }
.gd-deadline + .gd-deadline { margin-top: 8px; }
.gd-deadline__title { display: flex; align-items: center; gap: 6px; }

.gd-timeline { position: relative; display: flex; flex-direction: column; gap: 12px; padding-left: 14px; border-left: 2px solid var(--dp-surface-container-highest); }
.gd-timeline__item { position: relative; }
.gd-timeline__item p { margin: 0; }
.gd-timeline__dot { position: absolute; left: -19px; top: 3px; width: 8px; height: 8px; border-radius: 999px; background: var(--dp-on-surface-variant); }
.gd-timeline__item--active .gd-timeline__dot { background: var(--dp-primary); }

.gd-news__item { background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 10px; }
.gd-news__item + .gd-news__item { margin-top: 8px; }
.gd-news__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px; }
.gd-news__title { font-size: 12px; font-weight: 800; color: var(--dp-on-surface); margin: 0 0 3px; line-height: 1.4; }
.gd-news__body { margin: 0; line-height: 1.5; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }

@media (max-width: 640px) {
    .gd-hero__actions { width: 100%; }
    .gd-hero__actions .gd-btn { flex: 1; justify-content: center; }
}
</style>
