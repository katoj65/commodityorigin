<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AddLotModal from '@/Components/Modals/AddLotModal.vue';

/* ── Structural/visual port of the uploaded "My Lots" mockup (code.html),
   restyled with this app's own --dp-* theme tokens rather than the
   mockup's own Tailwind palette. Everything below is illustrative dummy
   data, not real lots — but "Create Lot" opens the same real AddLotModal
   already used on the Inventory page (reused, not rebuilt), wired to real
   option lists already provided by StoreController::inventoryContext(). ── */
const props = defineProps({
    processOptions: { type: Array, default: () => [] },
    coffeeGradeOptions: { type: Array, default: () => [] },
    packagingTypeOptions: { type: Array, default: () => [] },
    coffeeTypeOptions: { type: Array, default: () => [] },
    originOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    currencyCountries: { type: Object, default: () => ({}) },
    flavorOptions: { type: Array, default: () => [] },
    bodyOptions: { type: Array, default: () => [] },
    acidityOptions: { type: Array, default: () => [] },
    aftertasteOptions: { type: Array, default: () => [] },
    aromaOptions: { type: Array, default: () => [] },
});

const addLotOpen = ref(false);

const kpiCards = [
    { icon: 'inventory_2', label: 'Active Lots', value: '24', trailing: '+2 this mo', trailingIcon: 'arrow_upward', tone: 'primary', note: '92% audit compliance' },
    { icon: 'scale', label: 'Coffee Available', value: '428.0', unit: 'MT', tone: 'text', note: 'Unreserved trade stock · Across 4 regional hubs' },
    { icon: 'storefront', label: 'Listed on Exchange', value: '16', trailing: '(320 MT)', tone: 'secondary', note: '6 active incoming bids' },
    { icon: 'lock', label: 'Reserved', value: '82.0', unit: 'MT', tone: 'tertiary', note: 'Pending escrow & RFQs · 3 contracts binding' },
    { icon: 'history', label: 'In Processing', value: '96.0', unit: 'MT', note: 'Upstream batch preparation · 5 batches nearing ready' },
];

const alerts = [
    { tone: 'error', title: 'Offer Requires Response', note: 'LOT-UG-001: Counter-offer $4.08/kg by Dubai Coffee Trading', action: 'Review Offer', actionTone: 'primary' },
    { tone: 'primary', title: 'Batch Outturn Available', note: 'BAT-UG-2047 has 24.0 MT graded ready for commercialization', action: 'Create Lot', actionTone: 'muted' },
    { tone: 'secondary', title: 'Listing Not Published', note: 'LOT-UG-002 (35 MT FAQ Masaka) verified and awaiting push', action: 'Publish', actionTone: 'secondary' },
];

const lots = [
    {
        id: 'LOT-UG-001', coffee: 'Uganda Robusta Screen 18', origin: 'Central Mukono · Wet Processed', batch: 'BAT-UG-2021',
        quality: '84.5 CQI', qualityTone: 'primary', total: '20 MT Total', sub: '15 Avail / 5 Res',
        segments: [{ pct: 75, tone: 'primary' }, { pct: 25, tone: 'secondary' }],
        price: '$4.15', exchangeLabel: 'Listed', exchangeTone: 'primary', status: 'Active', statusTone: 'neutral', active: true,
    },
    {
        id: 'LOT-UG-002', coffee: 'Uganda Robusta FAQ', origin: 'Masaka Basin · Sun Dried', batch: 'BAT-UG-2038',
        quality: '80.5 CQI', qualityTone: 'neutral', total: '35 MT Total', sub: '35 Avail / 0 Res',
        segments: [{ pct: 100, tone: 'primary' }],
        price: '$3.98', exchangeLabel: 'Unlisted', exchangeTone: 'neutral', status: 'Ready', statusTone: 'secondary',
    },
    {
        id: 'LOT-UG-003', coffee: 'Uganda Bugisu Arabica AA Washed', origin: 'Mt. Elgon High Slopes (1900m)', batch: 'BAT-UG-2045',
        quality: '86.8 SCAA', qualityTone: 'primary', total: '12 MT Total', sub: '0 Avail / 12 Res',
        segments: [{ pct: 100, tone: 'secondary' }],
        price: '$5.40', exchangeLabel: 'Listed', exchangeTone: 'primary', status: 'Reserved', statusTone: 'secondary',
    },
    {
        id: 'LOT-UG-004', coffee: 'Rwenzori Natural Drugar Arabica', origin: 'Kasese District · Natural Sun', batch: 'BAT-UG-2042',
        quality: '85.0 CQI', qualityTone: 'primary', total: '15 MT Total', sub: '10 Avail / 5 Sold',
        segments: [{ pct: 66.6, tone: 'primary' }, { pct: 33.4, tone: 'tertiary' }],
        price: '$4.90', exchangeLabel: 'Listed', exchangeTone: 'primary', status: 'Part. Sold', statusTone: 'neutral',
    },
    {
        id: 'LOT-UG-005', coffee: 'West Nile FAQ Robusta', origin: 'Nebbi District · Dry Milled', batch: 'BAT-UG-2038',
        quality: '79.8 CQI', qualityTone: 'neutral', total: '25 MT Total', sub: '25 Avail / 0 Res',
        segments: [{ pct: 100, tone: 'primary' }],
        price: '$3.85', exchangeLabel: 'Draft', exchangeTone: 'neutral', status: 'QC Pending', statusTone: 'neutral',
    },
];

const qualitySpecs = [
    { label: 'Screen Size', value: 'Screen 18+ Washed' },
    { label: 'Moisture Content', value: '11.2% (Target < 12.5%)' },
    { label: 'CQI Cup Score', value: '84.50 / 100 (Fine Robusta)', tone: 'text' },
    { label: 'Defect Analysis', value: 'Grade 1 Clean (2 / 350g)' },
];

const activity = [
    { icon: 'currency_exchange', tone: 'error', title: 'Counter-Offer Received', time: '11:05 AM', note: 'Dubai Coffee Trading bid $4.08/kg for 20 MT FOB' },
    { icon: 'lock', tone: 'secondary', title: '5,000 kg Escrow Reserved', time: 'Yesterday', note: 'Secured for pending negotiation window (OFF-1048)' },
    { icon: 'publish', tone: 'primary', title: 'Published to Exchange', time: '18 Sep', note: 'Live listing published with verified cupping certificate' },
];
</script>

<template>
    <MainLayout title="Lots">
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="ltc-page">
            <!-- ── Top context bar ──────────────────────────────────────── -->
            <div class="ltc-hero">
                <div class="ltc-hero__text">
                    <nav class="ltc-breadcrumb">
                        <span>My Coffee</span>
                        <span class="material-symbols-outlined">chevron_right</span>
                        <span class="ltc-breadcrumb__current">Lots</span>
                    </nav>
                    <h1 class="ltc-title">My Lots</h1>
                    <p class="ltc-subtitle">Manage export-ready coffee consignments prepared for commercial trading, exchange publishing, and institutional bilateral execution.</p>
                </div>
                <div class="ltc-hero__actions">
                    <button type="button" class="ltc-btn ltc-btn--primary" @click="addLotOpen = true">
                        <span class="material-symbols-outlined">add_circle</span> Create Lot
                    </button>
                    <Link :href="route('inventory.batches')" class="ltc-btn ltc-btn--muted">
                        <span class="material-symbols-outlined">grain</span> View Batches
                    </Link>
                    <button type="button" class="ltc-btn ltc-btn--secondary">
                        <span class="material-symbols-outlined">auto_awesome</span> Ask Bean Origin AI
                    </button>
                </div>
            </div>

            <!-- ── Coffee lifecycle pipeline ribbon ─────────────────────── -->
            <div class="ltc-stepper">
                <div class="ltc-stepper__track">
                    <div class="ltc-stepper__node"><span>Farm</span></div>
                    <span class="material-symbols-outlined ltc-stepper__arrow">trending_flat</span>
                    <div class="ltc-stepper__node"><span>Farm Collection</span></div>
                    <span class="material-symbols-outlined ltc-stepper__arrow">trending_flat</span>
                    <div class="ltc-stepper__node"><span>Batch</span></div>
                    <span class="material-symbols-outlined ltc-stepper__arrow ltc-stepper__arrow--active">trending_flat</span>
                    <div class="ltc-stepper__badge">
                        <span class="ltc-stepper__badge-dot"></span>
                        <span>Lot: Commercial Active</span>
                    </div>
                    <span class="material-symbols-outlined ltc-stepper__arrow">trending_flat</span>
                    <div class="ltc-stepper__node"><span>Exchange</span></div>
                    <span class="material-symbols-outlined ltc-stepper__arrow">trending_flat</span>
                    <div class="ltc-stepper__node"><span>Trade</span></div>
                </div>
                <el-text size="small">
                   Available, Reserved, and Sold quantities reflect non-overlapping states of the same physical consignment.
                </el-text>
            </div>

            <!-- ── KPI summary row ───────────────────────────────────────── -->
            <div class="ltc-kpi-grid">
                <div v-for="kpi in kpiCards" :key="kpi.label" class="ltc-kpi">
                    <div class="ltc-kpi__top">
                        <span class="ltc-kpi__label">{{ kpi.label }}</span>
                        <span v-if="kpi.trailing && !kpi.trailingIcon" class="ltc-kpi__pill">{{ kpi.trailing }}</span>
                        <span v-else class="material-symbols-outlined" :class="`ltc-tone-${kpi.tone || 'muted'}`">{{ kpi.icon }}</span>
                    </div>
                    <div class="ltc-kpi__value">
                        <span class="ltc-mono" :class="kpi.tone === 'text' ? 'ltc-tone-text' : ''">{{ kpi.value }}</span>
                        <span v-if="kpi.unit" class="ltc-kpi__unit ltc-mono">{{ kpi.unit }}</span>
                        <span v-if="kpi.trailing && kpi.trailingIcon" class="ltc-kpi__trailing"><span class="material-symbols-outlined">{{ kpi.trailingIcon }}</span>{{ kpi.trailing }}</span>
                    </div>
                    <span class="ltc-kpi__note">{{ kpi.note }}</span>
                </div>
            </div>

            <!-- ── Action required alert strip ───────────────────────────── -->
            <div class="ltc-alerts">
                <div class="ltc-alerts__head">
                    <span class="material-symbols-outlined">notification_important</span>
                    <span>Action Required · Operational Prioritization ({{ alerts.length }})</span>
                </div>
                <div class="ltc-alerts__grid">
                    <div v-for="alert in alerts" :key="alert.title" class="ltc-alert">
                        <div class="ltc-alert__text">
                            <div class="ltc-alert__title"><span class="ltc-alert__dot" :class="`ltc-alert__dot--${alert.tone}`"></span>{{ alert.title }}</div>
                            <p class="ltc-alert__note">{{ alert.note }}</p>
                        </div>
                        <button type="button" class="ltc-alert__btn" :class="`ltc-alert__btn--${alert.actionTone}`">{{ alert.action }}</button>
                    </div>
                </div>
            </div>

            <!-- ── Filter & search toolbar ───────────────────────────────── -->
            <div class="ltc-filters">
                <div class="ltc-tabs">
                    <button type="button" class="ltc-tab ltc-tab--active">All (24)</button>
                    <button type="button" class="ltc-tab">Draft (2)</button>
                    <button type="button" class="ltc-tab">Ready (4)</button>
                    <button type="button" class="ltc-tab">Listed (16)</button>
                    <button type="button" class="ltc-tab">Partially Sold (5)</button>
                    <button type="button" class="ltc-tab">Reserved (3)</button>
                    <button type="button" class="ltc-tab">Sold (12)</button>
                    <button type="button" class="ltc-tab">Completed</button>
                </div>
                <div class="ltc-filters__row">
                    <div class="ltc-search">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" placeholder="Search Lots by Lot ID, coffee, origin, grade, mill, or source batch..." readonly />
                    </div>
                    <select class="ltc-select"><option>Origin: All Regions</option><option>Central Mukono</option><option>Masaka Basin</option><option>Mt. Elgon</option></select>
                    <select class="ltc-select"><option>Coffee: All Types</option><option>Robusta Screen 18</option><option>Arabica AA Washed</option></select>
                    <select class="ltc-select"><option>Trading: All States</option><option>Listed on Exchange</option><option>Unlisted / Private</option></select>
                    <button type="button" class="ltc-btn ltc-btn--muted">
                        <span class="material-symbols-outlined">bookmark_add</span> Save View
                    </button>
                </div>
            </div>

            <!-- ── Two-column workspace ─────────────────────────────────── -->
            <div class="ltc-grid">
                <!-- ── Left column: Lots ledger ──────────────────────────── -->
                <div class="ltc-col-main">
                    <div class="ltc-table-card">
                        <div class="ltc-table-card__head">
                            <div class="ltc-table-card__title">
                                <span>Coffee Lots Ledger</span>
                                <span class="ltc-chip">Showing 5 of 24 Lots</span>
                            </div>
                            <div class="ltc-table-card__actions">
                                <button type="button" class="ltc-icon-btn"><span class="material-symbols-outlined">download</span></button>
                                <button type="button" class="ltc-icon-btn"><span class="material-symbols-outlined">tune</span></button>
                            </div>
                        </div>
                        <div class="ltc-table-wrap">
                            <table class="ltc-table">
                                <colgroup>
                                    <col style="width: 10%" />
                                    <col style="width: 20%" />
                                    <col style="width: 9%" />
                                    <col style="width: 19%" />
                                    <col style="width: 10%" />
                                    <col style="width: 9%" />
                                    <col style="width: 9%" />
                                    <col style="width: 14%" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Lot ID</th>
                                        <th>Coffee &amp; Origin</th>
                                        <th>Batch</th>
                                        <th>Breakdown (MT)</th>
                                        <th>Ask Price</th>
                                        <th>Exchange</th>
                                        <th>Status</th>
                                        <th>Quality</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in lots" :key="row.id" class="ltc-table__row" :class="{ 'ltc-table__row--active': row.active }">
                                        <td class="ltc-mono ltc-strong ltc-tone-text">
                                            <span class="ltc-table__dot" v-if="row.active"></span>{{ row.id }}
                                        </td>
                                        <td>
                                            <div class="ltc-strong">{{ row.coffee }}</div>
                                            <div class="ltc-muted ltc-small">{{ row.origin }}</div>
                                        </td>
                                        <td class="ltc-mono ltc-muted ltc-small">{{ row.batch }}</td>
                                        <td>
                                            <div class="ltc-breakdown__row">
                                                <span class="ltc-strong ltc-small">{{ row.total }}</span>
                                                <span class="ltc-muted ltc-small">{{ row.sub }}</span>
                                            </div>
                                            <div class="ltc-bar">
                                                <div v-for="(seg, i) in row.segments" :key="i" class="ltc-bar__seg" :class="`ltc-bar__seg--${seg.tone}`" :style="{ width: seg.pct + '%' }"></div>
                                            </div>
                                        </td>
                                        <td class="ltc-mono ltc-strong">{{ row.price }}<span class="ltc-muted ltc-small ltc-mono">/kg</span></td>
                                        <td>
                                            <span class="ltc-status" :class="`ltc-status--${row.exchangeTone}`">
                                                <span v-if="row.exchangeTone === 'primary'" class="ltc-status__dot"></span>{{ row.exchangeLabel }}
                                            </span>
                                        </td>
                                        <td><span class="ltc-status ltc-status--plain">{{ row.status }}</span></td>
                                        <td><span class="ltc-chip" :class="row.qualityTone === 'primary' ? 'ltc-chip--fixed' : ''">{{ row.quality }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="ltc-table-card__foot">
                            <span class="ltc-muted ltc-small">Showing Page 1 of 5</span>
                            <div class="ltc-pagination">
                                <button type="button" class="ltc-page-btn" disabled>Previous</button>
                                <button type="button" class="ltc-page-btn ltc-page-btn--active">1</button>
                                <button type="button" class="ltc-page-btn">2</button>
                                <button type="button" class="ltc-page-btn">3</button>
                                <button type="button" class="ltc-page-btn">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Right column: Lot dossier (LOT-UG-001) ────────────── -->
                <div class="ltc-col-side">
                    <div class="ltc-card">
                        <div class="ltc-dossier__head">
                            <div>
                                <div class="ltc-dossier__title-row">
                                    <span class="ltc-mono ltc-tone-text ltc-title-lg">LOT-UG-001</span>
                                    <span class="ltc-status ltc-status--primary">Listed &amp; Active</span>
                                </div>
                                <p class="ltc-field-value">Uganda Robusta Screen 18 · Central Mukono</p>
                            </div>
                            <div class="ltc-dossier__head-actions">
                                <button type="button" class="ltc-btn ltc-btn--muted ltc-btn--sm">Edit</button>
                                <button type="button" class="ltc-btn ltc-btn--primary ltc-btn--sm">Listing</button>
                            </div>
                        </div>

                        <div class="ltc-dossier__section">
                            <div class="ltc-dossier__section-head">
                                <span class="ltc-card__title-plain">Physical Lot Ledger</span>
                                <span class="ltc-mono ltc-strong ltc-tone-text">20,000 kg (20 MT)</span>
                            </div>
                            <div class="ltc-panel">
                                <div class="ltc-bar ltc-bar--lg">
                                    <div class="ltc-bar__seg ltc-bar__seg--primary" style="width: 75%"></div>
                                    <div class="ltc-bar__seg ltc-bar__seg--secondary" style="width: 25%"></div>
                                </div>
                                <div class="ltc-ledger-grid">
                                    <div><span class="ltc-eyebrow-sm">Available</span><span class="ltc-strong ltc-tone-text ltc-mono">15,000 kg</span></div>
                                    <div><span class="ltc-eyebrow-sm">Reserved</span><span class="ltc-strong ltc-tone-secondary ltc-mono">5,000 kg</span></div>
                                    <div><span class="ltc-eyebrow-sm">Sold</span><span class="ltc-strong ltc-mono">0 kg</span></div>
                                </div>
                                <p class="ltc-ledger-note">* Active counter-offers reserve 5,000 kg in escrow until settlement or expiry.</p>
                            </div>
                        </div>

                        <div class="ltc-dossier__section">
                            <span class="ltc-card__title-plain">Traceability Lineage</span>
                            <div class="ltc-panel ltc-lineage">
                                <div class="ltc-lineage__row"><span class="material-symbols-outlined ltc-tone-text">park</span><span class="ltc-muted">Farm Origin:</span><span class="ltc-strong">Kawempe Estate (FARM-1048)</span></div>
                                <div class="ltc-lineage__row"><span class="material-symbols-outlined ltc-tone-text">hub</span><span class="ltc-muted">Collection:</span><span class="ltc-strong">Mukono Central Wet Mill (4 Picks)</span></div>
                                <div class="ltc-lineage__row"><span class="material-symbols-outlined ltc-tone-text">grain</span><span class="ltc-muted">Source Batch:</span><span class="ltc-mono ltc-strong ltc-tone-text">BAT-UG-2021</span></div>
                                <div class="ltc-lineage__row"><span class="material-symbols-outlined ltc-tone-secondary">warehouse</span><span class="ltc-muted">Storage:</span><span class="ltc-strong">Stanbic Bonded Silo B-14</span></div>
                            </div>
                        </div>

                        <div class="ltc-dossier__section">
                            <span class="ltc-card__title-plain">Quality &amp; Cupping Specifications</span>
                            <div class="ltc-panel--grid2">
                                <div v-for="spec in qualitySpecs" :key="spec.label" class="ltc-field-box">
                                    <span class="ltc-eyebrow-sm">{{ spec.label }}</span>
                                    <span class="ltc-strong ltc-small" :class="spec.tone === 'text' ? 'ltc-tone-text' : ''">{{ spec.value }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="ltc-dossier__section">
                            <span class="ltc-card__title-plain">Commercial &amp; Exchange Configuration</span>
                            <div class="ltc-panel ltc-commercial">
                                <div class="ltc-commercial__row"><span class="ltc-muted">Asking Price:</span><span class="ltc-mono ltc-strong ltc-tone-text">$4.15 / kg ($4,150/MT)</span></div>
                                <div class="ltc-commercial__row"><span class="ltc-muted">Incoterm Delivery:</span><span class="ltc-strong">FOB Port of Mombasa</span></div>
                                <div class="ltc-commercial__row"><span class="ltc-muted">Settlement Escrow:</span><span class="ltc-strong">Stanbic Bank Uganda PLC</span></div>
                                <div class="ltc-commercial__row"><span class="ltc-muted">Active Inquiries:</span><span class="ltc-strong ltc-tone-secondary">1 Counter-offer · 2 RFQ Fits</span></div>
                            </div>
                        </div>

                        <div class="ltc-ai-box">
                            <div class="ltc-ai-box__head">
                                <span class="material-symbols-outlined ltc-tone-secondary">auto_awesome</span>
                                <span class="ltc-strong ltc-small">Commercial AI Copilot</span>
                            </div>
                            <p class="ltc-ai-box__text">Asking price is <strong>+2.8% above</strong> Mombasa 30-day baseline. High European demand for Screen 18+ washed Robusta creates favorable counter-negotiation leverage.</p>
                            <div class="ltc-ai-box__actions">
                                <button type="button" class="ltc-ai-chip">Benchmark Price</button>
                                <button type="button" class="ltc-ai-chip ltc-ai-chip--strong">Match Active RFQs</button>
                            </div>
                        </div>

                        <div class="ltc-dossier__section">
                            <span class="ltc-card__title-plain">Recent Trading Activity</span>
                            <div class="ltc-activity">
                                <div v-for="(entry, i) in activity" :key="i" class="ltc-activity__item">
                                    <span class="material-symbols-outlined" :class="`ltc-tone-${entry.tone}`">{{ entry.icon }}</span>
                                    <div class="ltc-activity__body">
                                        <div class="ltc-activity__top"><span class="ltc-strong ltc-small">{{ entry.title }}</span><span class="ltc-muted ltc-small">{{ entry.time }}</span></div>
                                        <p class="ltc-muted ltc-small">{{ entry.note }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ltc-related">
                            <span class="ltc-muted ltc-small">Related:</span>
                            <div class="ltc-related__chips">
                                <span class="ltc-chip">BAT-UG-2021</span>
                                <span class="ltc-chip">PRD-UG-001</span>
                                <span class="ltc-chip">OFF-1048</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── "Create Lot" — the same real modal reused from the Inventory
             page, not a rebuilt dummy multi-step wizard. ───────────────── -->
        <AddLotModal
            v-model="addLotOpen"
            :process-options="processOptions"
            :coffee-grade-options="coffeeGradeOptions"
            :packaging-type-options="packagingTypeOptions"
            :variety-options="coffeeTypeOptions"
            :origin-options="originOptions"
            :currency-options="currencyOptions"
            :currency-countries="currencyCountries"
            :flavor-options="flavorOptions"
            :body-options="bodyOptions"
            :acidity-options="acidityOptions"
            :aftertaste-options="aftertasteOptions"
            :aroma-options="aromaOptions"
        />
    </MainLayout>
</template>

<style scoped>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

.ltc-page { font-family: var(--dp-font-sans); display: flex; flex-direction: column; gap: 20px; color: var(--dp-on-surface); }
.ltc-mono { font-family: var(--dp-font-mono); }
.ltc-muted { color: var(--dp-on-surface-variant); }
.ltc-strong { font-weight: 700; color: var(--dp-on-surface); }
.ltc-small { font-size: 11px; }
.ltc-tone-text { color: var(--dp-primary); }
.ltc-tone-primary { color: var(--dp-primary); }
.ltc-tone-secondary { color: var(--dp-on-secondary-container); }
.ltc-tone-tertiary { color: #4B5578; }
.ltc-tone-error { color: var(--dp-error); }
.ltc-tone-muted { color: var(--dp-on-surface-variant); }

/* Hero */
.ltc-hero { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.ltc-breadcrumb { display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); margin-bottom: 4px; }
.ltc-breadcrumb .material-symbols-outlined { font-size: 14px; }
.ltc-breadcrumb__current { color: var(--dp-primary); font-weight: 700; }
.ltc-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.015em; color: var(--dp-on-surface); margin: 0; }
.ltc-subtitle { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; line-height: 1.5; max-width: 62ch; }
.ltc-hero__actions { display: flex; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }

.ltc-btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border-radius: 6px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); text-decoration: none; transition: opacity .12s ease, background .12s ease; }
.ltc-btn .material-symbols-outlined { font-size: 16px; }
.ltc-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ltc-btn--primary:hover { opacity: .9; }
.ltc-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.ltc-btn--muted:hover { background: var(--dp-surface-container-highest); }
.ltc-btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ltc-btn--secondary:hover { opacity: .9; }
.ltc-btn--sm { height: 28px; padding: 0 10px; font-size: 11.5px; }

/* Stepper */
.ltc-stepper { background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px; }
.ltc-stepper__track { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ltc-stepper__node { padding: 4px 9px; border-radius: 5px; background: var(--dp-surface-container-lowest); font-size: 11.5px; font-weight: 700; color: var(--dp-on-surface); }
.ltc-stepper__arrow { font-size: 15px; color: var(--dp-outline); }
.ltc-stepper__arrow--active { color: var(--dp-primary); }
.ltc-stepper__badge { display: inline-flex; align-items: center; gap: 8px; background: var(--dp-primary); color: var(--dp-on-primary); padding: 5px 11px; border-radius: 5px; font-size: 11.5px; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.ltc-stepper__badge-dot { width: 7px; height: 7px; border-radius: 999px; background: var(--dp-on-primary); animation: ltc-pulse 1.8s ease-in-out infinite; flex-shrink: 0; }
@keyframes ltc-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
.ltc-stepper__rule { display: flex; align-items: center; gap: 8px; font-size: 11px; color: var(--dp-on-surface-variant); line-height: 1.5; background: var(--dp-surface-container-lowest); padding: 8px 12px; border-radius: 8px; max-width: 72ch; }
.ltc-stepper__rule .material-symbols-outlined { font-size: 16px; color: var(--dp-primary); flex-shrink: 0; }
.ltc-stepper__rule strong { color: var(--dp-on-surface); font-weight: 700; }

/* Element Plus's .el-text sets align-self: center by default, which
   centers it as a flex item inside .ltc-stepper's column layout — that's
   what reads as "centered" here, not a text-align issue. */
.ltc-stepper :deep(.el-text) { align-self: stretch; text-align: left; }

/* KPI row */
.ltc-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
.ltc-kpi { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 14px; display: flex; flex-direction: column; gap: 6px; }
.ltc-kpi__top { display: flex; align-items: center; justify-content: space-between; }
.ltc-kpi__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.ltc-kpi__top .material-symbols-outlined { font-size: 17px; }
.ltc-kpi__pill { font-size: 10px; font-weight: 700; color: var(--dp-primary); padding: 2px 7px; border-radius: 999px; background: var(--dp-primary-fixed); }
.ltc-kpi__value { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
.ltc-kpi__value > .ltc-mono:first-child { font-size: 1.375rem; font-weight: 800; color: var(--dp-on-surface); }
.ltc-kpi__unit { font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant); }
.ltc-kpi__trailing { font-size: 10.5px; font-weight: 700; color: var(--dp-primary); display: inline-flex; align-items: center; gap: 2px; }
.ltc-kpi__trailing .material-symbols-outlined { font-size: 13px; }
.ltc-kpi__note { font-size: 10px; color: var(--dp-on-surface-variant); margin-top: 2px; }

/* Alerts */
.ltc-alerts { background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px; }
.ltc-alerts__head { display: flex; align-items: center; gap: 8px; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface); }
.ltc-alerts__head .material-symbols-outlined { font-size: 17px; color: var(--dp-primary); }
.ltc-alerts__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; }
.ltc-alert { display: flex; align-items: center; justify-content: space-between; gap: 8px; background: var(--dp-surface-container-lowest); border-radius: 8px; padding: 10px 12px; }
.ltc-alert__text { min-width: 0; }
.ltc-alert__title { display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; color: var(--dp-on-surface); }
.ltc-alert__dot { width: 7px; height: 7px; border-radius: 999px; flex-shrink: 0; }
.ltc-alert__dot--error { background: var(--dp-error); }
.ltc-alert__dot--primary { background: var(--dp-primary); }
.ltc-alert__dot--secondary { background: var(--dp-secondary); }
.ltc-alert__note { font-size: 10.5px; color: var(--dp-on-surface-variant); margin: 3px 0 0; }
.ltc-alert__btn { flex-shrink: 0; padding: 6px 10px; border-radius: 6px; border: none; font-size: 10.5px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); white-space: nowrap; }
.ltc-alert__btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ltc-alert__btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.ltc-alert__btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }

/* Filters */
.ltc-filters { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.ltc-tabs { display: flex; align-items: center; gap: 4px; overflow-x: auto; }
.ltc-tab { padding: 7px 12px; border-radius: 6px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 11.5px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); }
.ltc-tab:hover { background: var(--dp-surface-container-low); }
.ltc-tab--active { background: var(--dp-primary); color: var(--dp-on-primary); }
.ltc-filters__row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ltc-search { position: relative; display: flex; align-items: center; flex: 1; min-width: 220px; }
.ltc-search .material-symbols-outlined { position: absolute; left: 10px; font-size: 17px; color: var(--dp-on-surface-variant); }
.ltc-search input { width: 100%; padding: 8px 12px 8px 34px; background: var(--dp-surface-container-low); border: none; border-radius: 6px; font-size: 12px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); outline: none; }
.ltc-select { padding: 7px 10px; background: var(--dp-surface-container-low); color: var(--dp-on-surface); font-size: 11.5px; font-weight: 600; border: none; border-radius: 6px; font-family: var(--dp-font-sans); cursor: pointer; }

/* Two-column grid */
.ltc-grid { display: grid; grid-template-columns: minmax(0, 8fr) minmax(320px, 4fr); gap: 18px; align-items: start; }
.ltc-col-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.ltc-col-side { display: flex; flex-direction: column; gap: 16px; }
@media (max-width: 1180px) { .ltc-grid { grid-template-columns: 1fr; } }

/* Table */
.ltc-table-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); overflow: hidden; }
.ltc-table-card__head { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: var(--dp-surface-container-low); gap: 10px; flex-wrap: wrap; }
.ltc-table-card__title { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: .8125rem; color: var(--dp-on-surface); }
.ltc-table-card__actions { display: flex; gap: 4px; }
.ltc-table-wrap { overflow-x: hidden; }
.ltc-table { width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: 11.5px; }
.ltc-table thead tr { background: var(--dp-surface-container-low); }
.ltc-table th { padding: 9px 8px; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); overflow-wrap: break-word; }
.ltc-table td { padding: 10px 8px; border-top: 1px solid var(--dp-outline-variant); vertical-align: middle; overflow-wrap: break-word; }
.ltc-table__row { transition: background .12s ease; }
.ltc-table__row:hover { background: var(--dp-surface-container-low); }
.ltc-table__row--active { background: color-mix(in srgb, var(--dp-primary) 6%, transparent); }
.ltc-table__dot { display: inline-block; width: 5px; height: 5px; border-radius: 999px; background: var(--dp-primary); margin-right: 5px; }
.ltc-table-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; background: var(--dp-surface-container-low); flex-wrap: wrap; }

.ltc-breakdown__row { display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 3px; }
.ltc-bar { width: 100%; height: 6px; border-radius: 999px; background: var(--dp-surface-container-high); overflow: hidden; display: flex; }
.ltc-bar--lg { height: 8px; }
.ltc-bar__seg--primary { background: var(--dp-primary); }
.ltc-bar__seg--secondary { background: var(--dp-secondary); }
.ltc-bar__seg--tertiary { background: #7B87B8; }

.ltc-pagination { display: flex; align-items: center; gap: 3px; }
.ltc-page-btn { min-width: 24px; height: 24px; padding: 0 8px; border-radius: 5px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 11px; font-family: var(--dp-font-sans); font-weight: 700; cursor: pointer; }
.ltc-page-btn:hover:not(:disabled) { background: var(--dp-surface-container-high); }
.ltc-page-btn:disabled { opacity: .4; cursor: default; }
.ltc-page-btn--active { background: var(--dp-primary); color: var(--dp-on-primary); }

/* Icon buttons */
.ltc-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-on-surface-variant); cursor: pointer; transition: background .12s ease, color .12s ease; }
.ltc-icon-btn:hover { background: var(--dp-surface-container-highest); color: var(--dp-on-surface); }
.ltc-icon-btn .material-symbols-outlined { font-size: 16px; }

/* Chips / status */
.ltc-chip { display: inline-flex; align-items: center; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-family: var(--dp-font-mono); font-weight: 700; background: var(--dp-surface-container); color: var(--dp-on-surface-variant); white-space: nowrap; }
.ltc-chip--fixed { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.ltc-status { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 999px; font-size: 9.5px; font-weight: 700; white-space: nowrap; }
.ltc-status--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.ltc-status--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ltc-status--neutral { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.ltc-status--plain { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.ltc-status__dot { width: 5px; height: 5px; border-radius: 999px; background: var(--dp-primary); }

/* Dossier card */
.ltc-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 18px; display: flex; flex-direction: column; gap: 16px; }
.ltc-card__title-plain { font-size: .8125rem; font-weight: 800; color: var(--dp-on-surface); }
.ltc-eyebrow-sm { display: block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dp-on-surface-variant); margin-bottom: 2px; }
.ltc-field-value { font-size: 12.5px; font-weight: 600; color: var(--dp-on-surface); margin: 2px 0 0; }

.ltc-dossier__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.ltc-dossier__title-row { display: flex; align-items: center; gap: 8px; }
.ltc-title-lg { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.01em; }
.ltc-dossier__head-actions { display: flex; gap: 4px; flex-shrink: 0; }
.ltc-dossier__section { display: flex; flex-direction: column; gap: 8px; }
.ltc-dossier__section-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

.ltc-panel { background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; gap: 10px; }
.ltc-ledger-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.ltc-ledger-grid > div { display: flex; flex-direction: column; gap: 2px; }
.ltc-ledger-note { font-size: 10px; font-style: italic; color: var(--dp-on-surface-variant); margin: 0; }

.ltc-lineage { gap: 8px; }
.ltc-lineage__row { display: flex; align-items: center; gap: 8px; font-size: 11.5px; }
.ltc-lineage__row .material-symbols-outlined { font-size: 16px; }

.ltc-panel--grid2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.ltc-field-box { background: var(--dp-surface-container-low); border-radius: 6px; padding: 8px; display: flex; flex-direction: column; gap: 2px; }

.ltc-commercial__row { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 11.5px; }

.ltc-ai-box { background: color-mix(in srgb, var(--dp-secondary-fixed) 50%, var(--dp-surface-container-lowest)); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.ltc-ai-box__head { display: flex; align-items: center; gap: 6px; }
.ltc-ai-box__text { font-size: 12px; color: var(--dp-on-surface); line-height: 1.55; margin: 0; }
.ltc-ai-box__actions { display: flex; flex-wrap: wrap; gap: 6px; }
.ltc-ai-chip { padding: 6px 10px; border-radius: 6px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); font-size: 10px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.ltc-ai-chip:hover { background: var(--dp-surface-container-highest); }
.ltc-ai-chip--strong { background: var(--dp-secondary); color: var(--dp-on-secondary); }

.ltc-activity { display: flex; flex-direction: column; gap: 6px; }
.ltc-activity__item { display: flex; align-items: flex-start; gap: 10px; padding: 8px; background: var(--dp-surface-container-low); border-radius: 8px; }
.ltc-activity__item .material-symbols-outlined { font-size: 16px; margin-top: 1px; flex-shrink: 0; }
.ltc-activity__body { min-width: 0; flex: 1; }
.ltc-activity__top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.ltc-activity__body p { margin: 2px 0 0; }

.ltc-related { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 2px; }
.ltc-related__chips { display: flex; gap: 6px; }

@media (max-width: 640px) {
    .ltc-hero__actions { width: 100%; }
    .ltc-hero__actions .ltc-btn { flex: 1; justify-content: center; }
    .ltc-panel--grid2 { grid-template-columns: 1fr; }
    .ltc-ledger-grid { grid-template-columns: 1fr; }
    .ltc-alerts__grid { grid-template-columns: 1fr; }
}
</style>
