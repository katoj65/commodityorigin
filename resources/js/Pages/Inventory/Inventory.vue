<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AddFarmCollectionModal from '@/Components/Modals/AddFarmCollectionModal.vue';
import AddBatchModal from '@/Components/Modals/AddBatchModal.vue';
import AddLotModal from '@/Components/Modals/AddLotModal.vue';

/* ── Structural/visual port of the uploaded "Inventory" mockup
   (code.html), restyled with this app's own --dp-* theme tokens rather
   than the mockup's own Tailwind palette. The stats/tables/cards below
   are illustrative dummy data, not real inventory — but the three
   "New …" header buttons open the same real modal components already
   used from StoreInventoryLayout.vue's "Register New ▾" dropdown, wired
   to the same real option lists (see InventoryController::modalOptions,
   which mirrors StoreController::inventoryContext). ─────────────────── */
const props = defineProps({
    processOptions: { type: Array, default: () => [] },
    dryingMethodOptions: { type: Array, default: () => [] },
    millingOptions: { type: Array, default: () => [] },
    coffeeTypeOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    coffeeGradeOptions: { type: Array, default: () => [] },
    packagingTypeOptions: { type: Array, default: () => [] },
    originOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    currencyCountries: { type: Object, default: () => ({}) },
    flavorOptions: { type: Array, default: () => [] },
    bodyOptions: { type: Array, default: () => [] },
    acidityOptions: { type: Array, default: () => [] },
    aftertasteOptions: { type: Array, default: () => [] },
    aromaOptions: { type: Array, default: () => [] },
    farmCollections: { type: Array, default: () => [] },
    lifecycleStats: { type: Object, default: () => ({}) },
});

/* ── kg → "1,284 MT" style figure, matching the stage cards' existing
   format (integer-leaning, one decimal only when the value needs it). ── */
function formatStageMt(kg) {
    return `${(Number(kg || 0) / 1000).toLocaleString(undefined, { maximumFractionDigits: 1 })} MT`;
}

/* ── The dialogs open the instant these are toggled — no button-level
   delay. Each modal now shows its own loader internally (over the field
   grid, for a couple of frames) while it mounts, instead of the button
   staying disabled/spinning before the dialog even appears. ─────────── */
const addCollectionOpen = ref(false);
const addBatchOpen = ref(false);
const addLotOpen = ref(false);

/* ── Physical Inventory Lifecycle KPI cards — the headline `value` (the
   big MT figure) uses real per-user totals from InventoryController's
   `lifecycleStats` prop wherever that stage actually has records
   (`count > 0`); a stage with no real records yet keeps its original
   dummy figure below rather than showing a bare "0 MT". Tokenised is
   real lots with an actual `blockchains` row (see
   InventoryController::lifecycleStats), not a status flag. The
   description/meta-line text stays illustrative either way — this app
   has no real "24 Wet/Dry Mills" or "22 Commercial" style metric to
   report.

   Each card also links to that stage's inventory workspace page — the
   same Store/* tab pages the store's "Register New ▾" flow uses,
   relocated to the inventory.* routes. Those pages require a verified
   store (StoreController::renderInventoryTab redirects to store.show
   otherwise), same as before the move. ───────────────────────────── */
const dummyLifecycleStages = [
    {
        num: 1, label: 'Collection', value: '1,284 MT', icon: 'agriculture', statKey: 'collection',
        desc: 'Coffee collected from specific farm or producer smallholders.',
        metaLabel: 'Historical Inbound', metaValue: '100% Traceable', current: true,
        href: route('farm-collection.index'),
    },
    {
        num: 2, label: 'Batch', value: '842 MT', icon: 'precision_manufacturing', statKey: 'batch',
        desc: 'Aggregated coffee undergoing processing, milling, and grading.',
        metaLabel: 'Active Operations', metaValue: '24 Wet/Dry Mills',
        href: route('inventory.batches'),
    },
    {
        num: 3, label: 'Lot', value: '412 MT', icon: 'inventory_2', statKey: 'lot',
        desc: 'Defined commercial consignment prepared for trading & export.',
        metaLabel: 'Export-Grade', metaValue: '22 Commercial',
        href: route('inventory.lots'),
    },
    {
        num: 4, label: 'Tokenised', value: '180 MT', icon: 'token', statKey: 'tokenised',
        desc: 'Commercial Lot registered as a digital asset with on-chain provenance.',
        metaLabel: 'Digital Twin', metaValue: '8 Lots Collateral', tone: 'secondary',
        href: route('inventory.tokenised'),
    },
];

const lifecycleStages = computed(() => dummyLifecycleStages.map((stage) => {
    const stat = props.lifecycleStats?.[stage.statKey];
    if (!stat || !stat.count) return stage;
    return { ...stage, value: formatStageMt(stat.weight_kg) };
}));

const tabs = [
    { key: 'collections', label: 'Farm Collections', count: 48 },
    { key: 'batches', label: 'Batches', count: 24 },
    { key: 'lots', label: 'Lots', count: 22 },
    { key: 'tokenised', label: 'Tokenised Coffee', count: 8 },
];
const activeTab = ref('collections');

/* ── "Registered Smallholder Deliveries" — real farm collection data
   for the authenticated user (InventoryController::index), everything
   else on the page stays dummy. Columns are adapted to fields that
   actually exist on FarmCollectionResource/FarmResource rather than the
   mockup's invented ones (no EUDR/geofencing or "#SH-xxx" smallholder
   ID column exists in the schema, so those aren't ported). ──────────── */
function goToCollection(row) {
    router.visit(route('farm-collection.show', row.id));
}

function formatCollectionDate(value) {
    if (!value) return '—';
    return new Date(value.replace(' ', 'T')).toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatMoney(amount, currency) {
    const value = Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${value}` : `$${value}`;
}

function originLabel(farm) {
    if (!farm) return '—';
    return [farm.district, farm.region].filter(Boolean).join(', ') || farm.country || '—';
}

const STATUS_LABELS = { pending: 'Pending', batched: 'Batched' };
const STATUS_TONE = { pending: false, batched: true };
function statusLabel(status) {
    return STATUS_LABELS[status] || (status || '—');
}

const processingBatches = [
    { id: 'BAT-UG-1021', name: '(Robusta Wet Mill)', figure: '9.4 MT Final', meta1: 'Input: 10.0 MT · Loss: 6.0%', meta2: 'Drying Stage · Day 14', progress: 85, primary: true },
    { id: 'BAT-UG-1024', name: '(Arabica Washed)', figure: '14.2 MT Input', meta1: 'From 8 Collections · Mt. Elgon', meta2: 'Pulping / Ferment', progress: 35, primary: false },
];

const readyLots = [
    { id: 'LOT-UG-2048', name: '(Fine Robusta 18)', figure: '20 MT Total', meta1: 'Stanbic Escrow Collateral', meta2: '15 MT Free / 5 MT Res', tag: 'TOK-UG-2048 Linked', tagTone: 'secondary', qscore: 'Q-Score: 84.50', primary: true },
    { id: 'LOT-ET-3091', name: '(Yirgacheffe Gr. 1)', figure: '19.2 MT', meta1: 'Addis Ababa Dry Port', meta2: 'Fully Available', tag: 'Tokenisation Eligible', tagTone: 'neutral', qscore: 'Q-Score: 88.25', primary: false },
];


</script>

<template>
    <MainLayout title="Inventory">
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="inv-page">
            <!-- ── Top operational sub-bar ─────────────────────────────── -->
            <div class="inv-hero">
                <div class="inv-hero__top">
                    <div class="inv-hero__text">
                        <div class="inv-hero__eyebrow-row">
                            <span class="inv-hero__eyebrow">Supply Operations</span>
                            <span class="inv-hero__eyebrow-mono">Physical Custody System · ISO 22053</span>
                        </div>
                        <h1 class="inv-title">Inventory</h1>
                        <p class="inv-subtitle">Track coffee from collection through processing, commercial lots, and tokenisation.</p>
                    </div>
                    <div class="inv-hero__actions">
                        <button type="button" class="inv-btn inv-btn--ghost">
                            <span class="material-symbols-outlined">auto_awesome</span> Ask Bean Origin AI
                        </button>
                        <button type="button" class="inv-btn inv-btn--muted" @click="addBatchOpen = true">
                            <span class="material-symbols-outlined">layers</span> + Create Batch
                        </button>
                        <button type="button" class="inv-btn inv-btn--muted" @click="addLotOpen = true">
                            <span class="material-symbols-outlined">inventory_2</span> + Create Lot
                        </button>
                        <button type="button" class="inv-btn inv-btn--primary" @click="addCollectionOpen = true">
                            <span class="material-symbols-outlined">add_circle</span> + Add Farm Collection
                        </button>
                    </div>
                </div>

                <!-- ── 4-stage lifecycle stepper ───────────────────────── -->
                <div class="inv-stepper">
                    <div class="inv-stepper__head">
                        <div class="inv-stepper__head-label">
                            <span class="material-symbols-outlined">account_tree</span>
                            <span>Physical Inventory Lifecycle</span>
                        </div>
                        <div class="inv-stepper__total">
                            <span class="inv-stepper__total-label">Total Mass Monitored</span>
                            <span class="inv-stepper__total-value inv-mono">1,284.00 <span class="inv-stepper__total-unit">MT</span></span>
                        </div>
                    </div>
                    <div class="inv-stepper__grid">
                        <Link
                            v-for="stage in lifecycleStages"
                            :key="stage.num"
                            :href="stage.href"
                            class="inv-stage"
                            :class="{ 'inv-stage--current': stage.current }"
                        >
                            <div class="inv-stage__top">
                                <span class="inv-stage__label" :class="{ 'inv-stage__label--tone': stage.tone }">
                                    <span class="material-symbols-outlined inv-stage__icon" :class="{ 'inv-stage__icon--pulse': stage.current, 'inv-stage__icon--tone': stage.tone }">{{ stage.icon }}</span>
                                    {{ stage.num }}. {{ stage.label }}
                                </span>
                                <span class="inv-mono inv-strong">{{ stage.value }}</span>
                            </div>
                            <p class="inv-stage__desc">{{ stage.desc }}</p>
                            <div class="inv-stage__meta">
                                <span>{{ stage.metaLabel }}</span>
                                <span class="inv-strong" :class="{ 'inv-tone-text': stage.tone }">{{ stage.metaValue }}</span>
                            </div>
                        </Link>
                    </div>
                    <div class="inv-stepper__notice">
                        <span class="material-symbols-outlined">verified_user</span>
                        <span><strong class="inv-strong">Physical Conservation Principle:</strong> Coffee progresses through physical transformation; quantities are accounted for at each stage and never double-counted across batches or export contracts.</span>
                    </div>
                </div>
            </div>

            <!-- ── Primary workspace ────────────────────────────────────── -->
            <div class="inv-body">
                <!-- Two-column workspace -->
                <div class="inv-grid">
                    <!-- ── Left column (main tables) ───────────────────── -->
                    <div class="inv-col-main">
                        <!-- Tab bar -->
                        <div class="inv-tabbar">
                            <div class="inv-tabbar__tabs">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.key"
                                    type="button"
                                    class="inv-tab"
                                    :class="{ 'inv-tab--active': activeTab === tab.key }"
                                    @click="activeTab = tab.key"
                                >
                                    <span>{{ tab.label }}</span>
                                    <span class="inv-tab__count">{{ tab.count }}</span>
                                </button>
                            </div>
                            <div class="inv-tabbar__actions">
                                <button type="button" class="inv-icon-btn" title="Filter columns"><span class="material-symbols-outlined">view_column</span></button>
                                <button type="button" class="inv-icon-btn" title="Export CSV"><span class="material-symbols-outlined">download</span></button>
                            </div>
                        </div>

                        <!-- Farm collections table -->
                        <div class="inv-table-card">
                            <div class="inv-table-card__head">
                                <div class="inv-table-card__title">
                                    <span>Registered Smallholder Deliveries</span>
                                    <span class="inv-mono inv-muted">(Active Inbound Log)</span>
                                </div>
                                <span class="inv-mono inv-muted">{{ farmCollections.length }} collection{{ farmCollections.length === 1 ? '' : 's' }}</span>
                            </div>
                            <div class="inv-table-wrap">
                                <table class="inv-table">
                                    <thead>
                                        <tr>
                                            <th>Farm</th>
                                            <th>Origin</th>
                                            <th>Date</th>
                                            <th>Weight</th>
                                            <th>Grade</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="row in farmCollections"
                                            :key="row.id"
                                            class="inv-table__row--link"
                                            tabindex="0"
                                            role="button"
                                            @click="goToCollection(row)"
                                            @keydown.enter="goToCollection(row)"
                                        >
                                            <td>
                                                <div class="inv-strong">{{ row.farm?.name || `Farm #${row.farm_id}` }}</div>
                                                <div class="inv-muted inv-small">{{ row.coffee_type || '—' }}<span v-if="row.variety"> · {{ row.variety }}</span></div>
                                            </td>
                                            <td>{{ originLabel(row.farm) }}</td>
                                            <td class="inv-mono inv-muted inv-small">{{ formatCollectionDate(row.collection_date) }}</td>
                                            <td class="inv-mono inv-strong">
                                                {{ Number(row.quantity || 0).toLocaleString() }} {{ row.unit || '' }}
                                                <span v-if="row.collection_price" class="inv-block inv-small inv-muted">{{ formatMoney(row.collection_price, row.currency) }}</span>
                                            </td>
                                            <td><span class="inv-chip">{{ row.initial_grade || '—' }}</span></td>
                                            <td>
                                                <span class="inv-chip" :class="STATUS_TONE[row.status] ? 'inv-chip--tone' : 'inv-chip--fixed'">{{ statusLabel(row.status) }}</span>
                                            </td>
                                        </tr>
                                        <tr v-if="!farmCollections.length">
                                            <td colspan="6" class="inv-table__empty">No farm collections recorded yet.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="inv-table-card__foot">
                                <span class="inv-mono inv-muted">{{ farmCollections.length }} farm collection{{ farmCollections.length === 1 ? '' : 's' }} total</span>
                            </div>
                        </div>

                        <!-- Secondary previews -->
                        <div class="inv-preview-grid">
                            <div class="inv-card">
                                <div class="inv-card__head">
                                    <div class="inv-card__head-label"><span class="material-symbols-outlined">hub</span> Current Processing Batches</div>
                                    <span class="inv-mono inv-strong inv-tone-text">24 Batches</span>
                                </div>
                                <div class="inv-mini-list">
                                    <div v-for="b in processingBatches" :key="b.id" class="inv-mini-row">
                                        <div class="inv-mini-row__top">
                                            <span class="inv-mono inv-strong" :class="{ 'inv-tone-text': b.primary }">{{ b.id }} {{ b.name }}</span>
                                            <span class="inv-mono inv-strong">{{ b.figure }}</span>
                                        </div>
                                        <div class="inv-mini-row__meta">
                                            <span>{{ b.meta1 }}</span>
                                            <span :class="b.primary ? 'inv-tone-text inv-strong' : 'inv-strong'">{{ b.meta2 }}</span>
                                        </div>
                                        <div class="inv-bar"><div class="inv-bar__fill" :style="{ width: b.progress + '%' }" /></div>
                                    </div>
                                </div>
                            </div>

                            <div class="inv-card">
                                <div class="inv-card__head">
                                    <div class="inv-card__head-label inv-tone-text--secondary"><span class="material-symbols-outlined">verified</span> <span class="inv-on-surface">Ready Commercial Lots</span></div>
                                    <span class="inv-mono inv-strong">22 Lots</span>
                                </div>
                                <div class="inv-mini-list">
                                    <div v-for="l in readyLots" :key="l.id" class="inv-mini-row">
                                        <div class="inv-mini-row__top">
                                            <span class="inv-mono inv-strong" :class="{ 'inv-tone-text': l.primary }">{{ l.id }} {{ l.name }}</span>
                                            <span class="inv-mono inv-strong">{{ l.figure }}</span>
                                        </div>
                                        <div class="inv-mini-row__meta">
                                            <span>{{ l.meta1 }}</span>
                                            <span :class="l.primary ? 'inv-tone-text inv-strong' : 'inv-strong'">{{ l.meta2 }}</span>
                                        </div>
                                        <div class="inv-mini-row__tags">
                                            <span class="inv-chip" :class="l.tagTone === 'secondary' ? 'inv-chip--secondary' : 'inv-chip--a'">{{ l.tag }}</span>
                                            <span class="inv-mono inv-muted inv-small">{{ l.qscore }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Right column (inspection & intelligence) ────── -->
                    <div class="inv-col-side">
                        <!-- Inspection dossier -->
                        <div class="inv-card">
                            <div class="inv-card__head">
                                <div class="inv-card__head-label"><span class="inv-dot"></span> Inspection Dossier</div>
                                <span class="inv-chip inv-chip--fixed">Commercial Lot</span>
                            </div>

                            <div class="inv-dossier">
                                <div class="inv-dossier__top">
                                    <div>
                                        <span class="inv-eyebrow-sm">Designation</span>
                                        <h2 class="inv-dossier__title">LOT-UG-2048</h2>
                                        <p class="inv-muted inv-small">Uganda Fine Robusta Screen 18 Washed</p>
                                    </div>
                                    <span class="inv-chip">84.5 CQI</span>
                                </div>

                                <div class="inv-dossier__panel">
                                    <div class="inv-kv"><span class="inv-muted">Total Weight:</span><span class="inv-mono inv-strong">20.00 MT (333 bags)</span></div>
                                    <div class="inv-kv"><span class="inv-muted">Uncommitted Available:</span><span class="inv-mono inv-strong inv-tone-text">15.00 MT</span></div>
                                    <div class="inv-kv"><span class="inv-muted">Escrow Reserved:</span><span class="inv-mono inv-strong inv-tone-text--secondary">5.00 MT (OFF-1048)</span></div>
                                </div>

                                <div class="inv-dossier__section">
                                    <span class="inv-eyebrow-sm">Transformation Provenance</span>
                                    <div class="inv-dossier__panel inv-dossier__panel--tight">
                                        <div class="inv-kv inv-kv--sm"><span class="inv-muted">Parent Batch:</span><span class="inv-mono inv-strong">BAT-UG-1021</span></div>
                                        <div class="inv-kv inv-kv--sm"><span class="inv-muted">Batch Hulling Loss:</span><span class="inv-mono inv-muted">600 kg (6.0%)</span></div>
                                        <div class="inv-kv inv-kv--sm"><span class="inv-muted">Constituent Collections:</span><span class="inv-mono inv-strong inv-tone-text">COL-1048, 1051, 1054, 1059</span></div>
                                        <div class="inv-kv inv-kv--sm"><span class="inv-muted">Moisture Content:</span><span class="inv-mono">11.2% (ISO 6673)</span></div>
                                    </div>
                                </div>

                                <div class="inv-dossier__section">
                                    <span class="inv-eyebrow-sm">Market &amp; Liquidity</span>
                                    <div class="inv-dossier__panel inv-dossier__market">
                                        <div>
                                            <span class="inv-strong inv-small inv-block">Mombasa Floor Listing</span>
                                            <span class="inv-mono inv-muted inv-small">3 Active Bids · 2 RFQ Matches</span>
                                        </div>
                                        <span class="inv-mono inv-strong inv-tone-text">$4.15/kg</span>
                                    </div>
                                </div>

                                <div class="inv-dossier__panel inv-dossier__token">
                                    <div class="inv-kv">
                                        <span class="inv-eyebrow-sm">Digital Asset Twin</span>
                                        <span class="inv-chip inv-chip--secondary">ERC-3643 RWA</span>
                                    </div>
                                    <div class="inv-kv">
                                        <span class="inv-mono inv-strong">TOK-UG-2048</span>
                                        <span class="inv-mono inv-muted inv-small">Vault: Stanbic Custody</span>
                                    </div>
                                    <p class="inv-muted inv-small">Represents physical title held in bonded warehouse custody. Not algorithmic or speculative collateral.</p>
                                </div>

                                <div class="inv-dossier__actions">
                                    <button type="button" class="inv-btn inv-btn--primary inv-btn--block">Traceability Dossier</button>
                                    <button type="button" class="inv-btn inv-btn--muted inv-btn--block">Exchange Listing</button>
                                </div>
                            </div>
                        </div>

                        <!-- Mass-balance accounting -->
                        <div class="inv-card">
                            <div class="inv-card__head">
                                <span class="inv-card__title-plain">Mass-Balance Accounting</span>
                                <span class="material-symbols-outlined inv-tone-text">balance</span>
                            </div>
                            <div class="inv-dossier__panel">
                                <div class="inv-kv inv-kv--sm"><span class="inv-muted">Historical Collected</span><span class="inv-mono inv-strong">1,284 MT</span></div>
                                <div class="inv-kv inv-kv--sm"><span class="inv-muted">Processing Outturn Loss</span><span class="inv-mono inv-error">-442 MT (34.4%)</span></div>
                                <div class="inv-kv inv-kv--sm"><span class="inv-muted">Current Active Batches</span><span class="inv-mono inv-strong">842 MT</span></div>
                                <div class="inv-kv inv-kv--sm"><span class="inv-muted">Commercial Export Lots</span><span class="inv-mono inv-strong inv-tone-text">412 MT</span></div>
                                <div class="inv-kv inv-kv--divider"><span class="inv-strong inv-tone-text--secondary">Tokenised Ownership Claims</span><span class="inv-mono inv-tone-text--secondary">180 MT (of 412 MT)</span></div>
                            </div>
                            <p class="inv-muted inv-small">* Note: Tokenised representation acts as an electronic title deed over the physical 412 MT commercial lot pool and does not augment physical volume.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── "New …" header button modals — the same real modal
             components StoreInventoryLayout.vue's "Register New ▾"
             dropdown opens, reused here rather than rebuilt. ────────── -->
        <AddFarmCollectionModal
            v-model="addCollectionOpen"
            :coffee-type-options="coffeeTypeOptions"
            :harvest-season-options="harvestSeasonOptions"
            :currency-options="currencyOptions"
        />
        <AddBatchModal
            v-model="addBatchOpen"
            :process-options="processOptions"
            :variety-options="coffeeTypeOptions"
            :drying-method-options="dryingMethodOptions"
            :currency-options="currencyOptions"
            :milling-options="millingOptions"
        />
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
/* Base icon rule — mirrors StoreInventoryLayout.vue's own base rule for
   the same Material Symbols font, loaded via the <Head> block above. */
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

.inv-page { font-family: var(--dp-font-sans); display: flex; flex-direction: column; gap: 20px; color: var(--dp-on-surface); }

.inv-mono { font-family: var(--dp-font-mono); }
.inv-muted { color: var(--dp-on-surface-variant); }
.inv-strong { font-weight: 700; color: var(--dp-on-surface); }
.inv-small { font-size: 11px; }
.inv-block { display: block; }
.inv-on-surface { color: var(--dp-on-surface); }
.inv-tone-text { color: var(--dp-primary); }
.inv-tone-text--secondary { color: var(--dp-on-secondary-container); }
.inv-error { color: var(--dp-error); font-weight: 600; }

/* ── Hero / sub-bar ──────────────────────────────────────────────────── */
.inv-hero { background: var(--dp-surface-container-lowest); border-radius: var(--dp-card-radius); padding: 0; display: flex; flex-direction: column; gap: 20px; }
.inv-hero__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.inv-hero__text { min-width: 0; }
.inv-hero__eyebrow-row { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap; }
.inv-hero__eyebrow { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: 2px 8px; border-radius: 4px; background: var(--dp-surface-container); color: var(--dp-on-surface-variant); }
.inv-hero__eyebrow-mono { font-family: var(--dp-font-mono); font-size: 11px; font-weight: 500; color: var(--dp-on-surface-variant); }
.inv-title { font-size: 1.5rem; line-height: 2.1rem; font-weight: 800; letter-spacing: -.015em; color: var(--dp-on-surface); margin: 0; }
.inv-subtitle { font-size: 13.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; }
.inv-hero__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; flex-shrink: 0; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.inv-btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 6px; border: none; font-size: 12.5px; font-weight: 700; cursor: pointer; white-space: nowrap; transition: opacity .12s ease, background .12s ease; font-family: var(--dp-font-sans); }
.inv-btn .material-symbols-outlined { font-size: 16px; }
.inv-btn--ghost { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.inv-btn--ghost .material-symbols-outlined { color: var(--dp-primary); }
.inv-btn--ghost:hover { background: var(--dp-surface-container); }
.inv-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.inv-btn--muted:hover { background: var(--dp-surface-container-highest); }
.inv-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.inv-btn--primary:hover { opacity: .9; }
.inv-btn--block { width: 100%; justify-content: center; }

/* ── Lifecycle stepper ───────────────────────────────────────────────── */
.inv-stepper { background: var(--dp-surface-container-low); border-radius: 10px; padding: 16px; }
.inv-stepper__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 12px; flex-wrap: wrap; }
.inv-stepper__head-label { display: flex; align-items: center; gap: 8px; font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface); }
.inv-stepper__head-label .material-symbols-outlined { color: var(--dp-primary); font-size: 18px; }
.inv-stepper__total { display: flex; align-items: baseline; gap: 8px; }
.inv-stepper__total-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dp-on-surface-variant); }
.inv-stepper__total-value { font-size: 1rem; font-weight: 800; color: var(--dp-on-surface); letter-spacing: -.01em; }
.inv-stepper__total-unit { font-size: .75rem; font-weight: 700; color: var(--dp-on-surface-variant); }
.inv-stepper__grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.inv-stage { position: relative; display: block; background: var(--dp-surface-container-lowest); padding: 14px; border-radius: 8px; text-decoration: none; color: inherit; cursor: pointer; transition: box-shadow .15s ease; }
.inv-stage:hover { box-shadow: 0 3px 10px rgba(18, 21, 22, 0.1); }
.inv-stage--current { box-shadow: 0 1px 3px rgba(18, 21, 22, 0.08); }
.inv-stage__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; gap: 8px; }
.inv-stage__label { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dp-on-surface); }
.inv-stage--current .inv-stage__label { color: var(--dp-primary); }
.inv-stage__label--tone { color: var(--dp-on-secondary-container); }
.inv-stage__icon { font-size: 15px; color: var(--dp-on-surface-variant); flex-shrink: 0; }
.inv-stage__icon--pulse { color: var(--dp-primary); animation: inv-pulse 1.8s ease-in-out infinite; }
.inv-stage__icon--tone { color: var(--dp-on-secondary-container); }
.inv-stage__desc { font-size: 11px; color: var(--dp-on-surface-variant); line-height: 1.5; margin: 0 0 10px; min-height: 32px; }
.inv-stage__meta { display: flex; align-items: center; justify-content: space-between; font-size: 10px; font-family: var(--dp-font-mono); color: var(--dp-on-surface-variant); }
@keyframes inv-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

.inv-stepper__notice { display: flex; align-items: flex-start; gap: 10px; margin-top: 14px; padding: 10px 12px; background: var(--dp-surface-container-high); border-radius: 8px; font-size: 11px; color: var(--dp-on-surface-variant); }
.inv-stepper__notice .material-symbols-outlined { color: var(--dp-primary); font-size: 16px; flex-shrink: 0; }

@media (max-width: 1180px) {
    .inv-stepper__grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .inv-stepper__grid { grid-template-columns: 1fr; }
}

/* ── Body ────────────────────────────────────────────────────────────── */
.inv-body { display: flex; flex-direction: column; gap: 20px; }

/* Two-column grid */
.inv-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 20px; align-items: start; }
.inv-col-main { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.inv-col-side { display: flex; flex-direction: column; gap: 20px; }
@media (max-width: 1180px) {
    .inv-grid { grid-template-columns: 1fr; }
}

/* Tab bar */
.inv-tabbar { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 8px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.inv-tabbar__tabs { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
.inv-tab { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 6px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 12px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); transition: background .12s ease, color .12s ease; }
.inv-tab:hover { background: var(--dp-surface-container-low); }
.inv-tab--active { background: var(--dp-surface-container); color: var(--dp-primary); }
.inv-tab__count { font-family: var(--dp-font-mono); font-size: 10px; padding: 1px 6px; border-radius: 999px; background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.inv-tab--active .inv-tab__count { background: var(--dp-primary); color: var(--dp-on-primary); }
.inv-tabbar__actions { display: flex; align-items: center; gap: 6px; padding-right: 4px; }

/* Icon buttons */
.inv-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); cursor: pointer; transition: background .12s ease, color .12s ease; }
.inv-icon-btn:hover { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.inv-icon-btn .material-symbols-outlined { font-size: 17px; }

/* Table card */
.inv-table-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); overflow: hidden; }
.inv-table-card__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; background: var(--dp-surface-container-low); flex-wrap: wrap; }
.inv-table-card__title { display: flex; align-items: center; gap: 8px; font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface); }
.inv-table-wrap { overflow-x: auto; }
.inv-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
.inv-table thead tr { background: var(--dp-surface-container-low); }
.inv-table th { padding: 10px 14px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface-variant); white-space: nowrap; }
.inv-table td { padding: 12px 14px; border-top: 1px solid var(--dp-outline-variant); vertical-align: middle; }
.inv-table tbody tr:hover { background: var(--dp-surface-container-low); }
.inv-table__row--link { cursor: pointer; }
.inv-table__row--link:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: -2px; }
.inv-table__empty { text-align: center; padding: 32px 14px; color: var(--dp-on-surface-variant); font-size: 12px; }
.inv-table-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 18px; background: var(--dp-surface-container-low); flex-wrap: wrap; }

/* Chips / flags */
.inv-chip { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-family: var(--dp-font-mono); font-weight: 700; background: var(--dp-surface-container); color: var(--dp-on-surface); white-space: nowrap; }
.inv-chip--fixed { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.inv-chip--tone { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.inv-chip--secondary { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.inv-chip--a { background: var(--dp-surface-container); color: var(--dp-on-surface-variant); }

/* Secondary preview cards */
.inv-preview-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; }
.inv-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 18px; display: flex; flex-direction: column; gap: 12px; }
.inv-card__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.inv-card__head-label { display: flex; align-items: center; gap: 8px; font-size: .75rem; font-weight: 800; text-transform: uppercase; color: var(--dp-on-surface); }
.inv-card__head-label .material-symbols-outlined { font-size: 18px; }
.inv-card__title-plain { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface); }

.inv-mini-list { display: flex; flex-direction: column; gap: 10px; }
.inv-mini-row { padding: 12px; background: var(--dp-surface-container-low); border-radius: 8px; }
.inv-mini-row__top { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 12px; }
.inv-mini-row__meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 4px; font-size: 11px; color: var(--dp-on-surface-variant); }
.inv-mini-row__tags { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 8px; }
.inv-bar { width: 100%; height: 6px; border-radius: 999px; background: var(--dp-surface-container-high); overflow: hidden; margin-top: 8px; }
.inv-bar__fill { height: 100%; border-radius: 999px; background: var(--dp-primary); }

/* Inspection dossier */
.inv-dot { width: 10px; height: 10px; border-radius: 999px; background: var(--dp-primary); }
.inv-dossier { display: flex; flex-direction: column; gap: 14px; }
.inv-dossier__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 14px; background: var(--dp-surface-container-low); border-radius: 8px; }
.inv-eyebrow-sm { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.inv-dossier__title { font-size: 1rem; font-weight: 800; color: var(--dp-on-surface); margin: 2px 0 0; }
.inv-dossier__panel { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 8px; padding: 10px 12px; display: flex; flex-direction: column; gap: 8px; }
.inv-dossier__panel--tight { gap: 6px; padding: 10px; }
.inv-dossier__section { display: flex; flex-direction: column; gap: 8px; }
.inv-kv { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 12px; }
.inv-kv--sm { font-size: 11px; }
.inv-kv--divider { padding-top: 8px; border-top: 1px solid var(--dp-outline-variant); font-size: 12px; }
.inv-dossier__market { flex-direction: row; align-items: center; justify-content: space-between; }
.inv-dossier__token { background: var(--dp-surface-container-low); border: none; }
.inv-dossier__actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

@media (max-width: 640px) {
    .inv-hero { padding: 18px; }
    .inv-hero__actions { width: 100%; }
    .inv-hero__actions .inv-btn { flex: 1; justify-content: center; }
    .inv-dossier__actions { grid-template-columns: 1fr; }
}
</style>
