<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AddLotModal from '@/Components/Modals/AddLotModal.vue';

/* ── Structural/visual port of the uploaded "My Lots" mockup (code.html),
   restyled with this app's own --dp-* theme tokens rather than the
   mockup's own Tailwind palette. The KPI strip and lots table are real
   (StoreController::lotKpis() / inventoryContext()'s `lots` prop) — but
   "Create Lot" opens the same real AddLotModal already used on the
   Inventory page (reused, not rebuilt), wired to real option lists
   already provided by StoreController::inventoryContext(). ─────────── */
const props = defineProps({
    lots: { type: Array, default: () => [] },
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
    lotKpis: {
        type: Object,
        default: () => ({
            active_lots: 0,
            new_this_month: 0,
            ready_lots: 0,
            available_kg: 0,
            region_count: 0,
            listed_count: 0,
            listed_volume_kg: 0,
            pending_bid_count: 0,
            reserved_kg: 0,
            reserved_listing_count: 0,
            processing_kg: 0,
            processing_count: 0,
        }),
    },
    actionAlerts: { type: Array, default: () => [] },
});

const addLotOpen = ref(false);

const mt = (kg) => (Number(kg) / 1000).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

const kpiCards = computed(() => {
    const k = props.lotKpis;
    return [
        {
            icon: 'inventory_2', label: 'Active Lots', value: String(k.active_lots), tone: 'primary',
            ...(k.new_this_month > 0 ? { trailing: `+${k.new_this_month} this mo`, trailingIcon: 'arrow_upward' } : {}),
            note: `${k.ready_lots} ready for market`,
        },
        {
            icon: 'scale', label: 'Coffee Available', value: mt(k.available_kg), unit: 'MT', tone: 'text',
            note: `Unreserved trade stock · Across ${k.region_count} region${k.region_count === 1 ? '' : 's'}`,
        },
        {
            icon: 'storefront', label: 'Listed on Exchange', value: String(k.listed_count), trailing: `(${mt(k.listed_volume_kg)} MT)`, tone: 'secondary',
            note: `${k.pending_bid_count} active incoming bid${k.pending_bid_count === 1 ? '' : 's'}`,
        },
        {
            icon: 'lock', label: 'Reserved', value: mt(k.reserved_kg), unit: 'MT', tone: 'tertiary',
            note: `Pending escrow & RFQs · ${k.reserved_listing_count} listing${k.reserved_listing_count === 1 ? '' : 's'} with reserved stock`,
        },
        {
            icon: 'history', label: 'In Processing', value: mt(k.processing_kg), unit: 'MT',
            note: `Upstream lot preparation · ${k.processing_count} lot${k.processing_count === 1 ? '' : 's'} pending grading`,
        },
    ];
});


/* ── Real lot rows, mapped from the `lots` prop (LotResource, eager
   loaded with lotBatches.batch/market/blockchain — see
   StoreController::inventoryContext()) into this table's display
   shape. `searchText` is the flattened, lowercased haystack the search
   bar matches against. ──────────────────────────────────────────────── */
const STATUS_LABELS = { draft: 'Draft', ready: 'Ready', listing_ready: 'Listing Ready', tokenisation_ready: 'Tokenisation Ready' };
const STATUS_TONES = { draft: 'neutral', ready: 'secondary', listing_ready: 'secondary', tokenisation_ready: 'secondary' };

const lotRows = computed(() => props.lots.map((lot) => {
    const market = lot.market ?? null;
    const batchNumbers = (lot.lot_batches ?? []).map((lb) => lb.batch_number).filter(Boolean);
    const totalKg = market ? Number(market.quantity) : Number(lot.net_weight_kg || 0);
    const availKg = market ? Number(market.available_quantity ?? 0) : Number(lot.net_weight_kg || 0);
    const reservedKg = market ? Number(market.reserved_quantity ?? 0) : 0;

    let statusBucket = 'ready';
    if (lot.status === 'draft') {
        statusBucket = 'draft';
    } else if (market?.status === 'sold') {
        statusBucket = 'sold';
    } else if (market?.status === 'live') {
        if (reservedKg > 0 && availKg <= 0) {
            statusBucket = 'reserved';
        } else if (availKg > 0 && availKg < totalKg) {
            statusBucket = 'partially_sold';
        } else {
            statusBucket = 'listed';
        }
    }

    return {
        key: lot.id,
        statusBucket,
        id: lot.lot_number,
        coffee: lot.variety || lot.lot_name || 'Coffee Lot',
        origin: [lot.origin, lot.process].filter(Boolean).join(' · ') || '—',
        quality: lot.quality_score ? `${Number(lot.quality_score).toFixed(1)} pts` : (lot.grade || '—'),
        qualityTone: lot.quality_score ? 'primary' : 'neutral',
        price: lot.price !== null && lot.price !== undefined ? `$${Number(lot.price).toFixed(2)}` : '—',
        status: STATUS_LABELS[lot.status] || lot.status || '—',
        statusTone: STATUS_TONES[lot.status] || 'neutral',
        active: market?.status === 'live',
        searchText: [lot.lot_number, lot.lot_name, lot.variety, lot.origin, lot.region, lot.grade, lot.process, ...batchNumbers]
            .filter(Boolean).join(' ').toLowerCase(),
    };
}));

const searchQuery = ref('');
const originFilter = ref('');
const coffeeFilter = ref('');
const tradingFilter = ref('');
const statusFilter = ref('all');

/* Tab buckets are derived only from real Lot/Market fields (Lot.status,
   Market.status/available_quantity/reserved_quantity) — there is no
   "Completed" state anywhere in the schema for a lot's listing, so that
   tab from the original mockup was dropped rather than faked. */
const statusTabs = computed(() => {
    const rows = lotRows.value;
    const count = (bucket) => rows.filter((row) => row.statusBucket === bucket).length;
    return [
        { value: 'all', label: 'All', count: rows.length },
        { value: 'draft', label: 'Draft', count: count('draft') },
        { value: 'ready', label: 'Ready', count: count('ready') },
        { value: 'listed', label: 'Listed', count: count('listed') },
        { value: 'partially_sold', label: 'Partially Sold', count: count('partially_sold') },
        { value: 'reserved', label: 'Reserved', count: count('reserved') },
        { value: 'sold', label: 'Sold', count: count('sold') },
    ];
});

const filteredLots = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    return lotRows.value.filter((row) => {
        if (statusFilter.value !== 'all' && row.statusBucket !== statusFilter.value) return false;
        if (q && !row.searchText.includes(q)) return false;
        return true;
    });
});

/* Every lot for this user (StoreController::inventoryContext()'s `lots`
   prop, ordered `->latest()` i.e. by created_at desc) is passed down and
   filtered client-side above — pagination below just slices that already-
   ordered, already-filtered list, 10 rows per page. */
const LOTS_PAGE_SIZE = 10;
const currentPage = ref(1);
const totalPages = computed(() => Math.max(1, Math.ceil(filteredLots.value.length / LOTS_PAGE_SIZE)));
const pageNumbers = computed(() => Array.from({ length: totalPages.value }, (_, i) => i + 1));
const pagedLots = computed(() => {
    const start = (currentPage.value - 1) * LOTS_PAGE_SIZE;
    return filteredLots.value.slice(start, start + LOTS_PAGE_SIZE);
});
const pageRangeStart = computed(() => (filteredLots.value.length ? (currentPage.value - 1) * LOTS_PAGE_SIZE + 1 : 0));
const pageRangeEnd = computed(() => Math.min(currentPage.value * LOTS_PAGE_SIZE, filteredLots.value.length));

function goToPage(page) {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
}

watch([searchQuery, statusFilter], () => { currentPage.value = 1; });

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

            <!-- ── Action required alert strip — this user's oldest still-draft
                 lots (StoreController::draftLotAlerts()), capped at 3 ────── -->
            <div v-if="actionAlerts.length" class="ltc-alerts">
                <div class="ltc-alerts__head">
                    <span class="material-symbols-outlined">notification_important</span>
                    <span>Action Required · Operational Prioritization ({{ actionAlerts.length }})</span>
                </div>
                <div class="ltc-alerts__grid">
                    <div v-for="alert in actionAlerts" :key="alert.id" class="ltc-alert">
                        <div class="ltc-alert__text">
                            <div class="ltc-alert__title"><span class="ltc-alert__dot" :class="`ltc-alert__dot--${alert.tone}`"></span>{{ alert.title }}</div>
                            <p class="ltc-alert__note">{{ alert.note }}</p>
                        </div>
                        <Link :href="route('lot.show', alert.id)" class="ltc-alert__btn" :class="`ltc-alert__btn--${alert.actionTone}`">{{ alert.action }}</Link>
                    </div>
                </div>
            </div>

            <!-- ── Filter & search toolbar ───────────────────────────────── -->
            <div class="ltc-filters">
                <el-radio-group v-model="statusFilter" size="small" class="ltc-tabs">
                    <el-radio-button v-for="tab in statusTabs" :key="tab.value" :value="tab.value">
                        {{ tab.label }} ({{ tab.count }})
                    </el-radio-button>
                </el-radio-group>
                <div class="ltc-filters__row">
                    <el-input v-model="searchQuery" size="small" class="ltc-search" placeholder="Search Lots by Lot ID, coffee, origin, grade, mill, or source batch...">
                        <template #prefix><span class="material-symbols-outlined">search</span></template>
                    </el-input>
                    <el-select v-model="originFilter" size="small" class="ltc-select" placeholder="Origin: All Regions">
                        <el-option label="Origin: All Regions" value="" />
                        <el-option label="Central Mukono" value="Central Mukono" />
                        <el-option label="Masaka Basin" value="Masaka Basin" />
                        <el-option label="Mt. Elgon" value="Mt. Elgon" />
                    </el-select>
                    <el-select v-model="coffeeFilter" size="small" class="ltc-select" placeholder="Coffee: All Types">
                        <el-option label="Coffee: All Types" value="" />
                        <el-option label="Robusta Screen 18" value="Robusta Screen 18" />
                        <el-option label="Arabica AA Washed" value="Arabica AA Washed" />
                    </el-select>
                    <el-select v-model="tradingFilter" size="small" class="ltc-select" placeholder="Trading: All States">
                        <el-option label="Trading: All States" value="" />
                        <el-option label="Listed on Exchange" value="Listed on Exchange" />
                        <el-option label="Unlisted / Private" value="Unlisted / Private" />
                    </el-select>
                    <el-button size="small" class="ltc-save-btn">
                        <span class="material-symbols-outlined">bookmark_add</span> Save View
                    </el-button>
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
                                <span class="ltc-chip">Showing {{ filteredLots.length }} of {{ lotRows.length }} Lots</span>
                            </div>
                            <div class="ltc-table-card__actions">
                                <button type="button" class="ltc-icon-btn"><span class="material-symbols-outlined">download</span></button>
                                <button type="button" class="ltc-icon-btn"><span class="material-symbols-outlined">tune</span></button>
                            </div>
                        </div>
                        <div class="ltc-table-wrap">
                            <table class="ltc-table">
                                <colgroup>
                                    <col style="width: 22%" />
                                    <col style="width: 26%" />
                                    <col style="width: 19%" />
                                    <col style="width: 15%" />
                                    <col style="width: 18%" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Lot ID</th>
                                        <th>Coffee &amp; Origin</th>
                                        <th>Quality</th>
                                        <th>Ask Price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in pagedLots" :key="row.key" class="ltc-table__row" :class="{ 'ltc-table__row--active': row.active }">
                                        <td class="ltc-mono ltc-strong ltc-tone-text">
                                            <span class="ltc-table__dot" v-if="row.active"></span>{{ row.id }}
                                        </td>
                                        <td>
                                            <div class="ltc-strong">{{ row.coffee }}</div>
                                            <div class="ltc-muted ltc-small">{{ row.origin }}</div>
                                        </td>
                                        <td><span class="ltc-chip" :class="row.qualityTone === 'primary' ? 'ltc-chip--fixed' : ''">{{ row.quality }}</span></td>
                                        <td class="ltc-mono ltc-strong">{{ row.price }}<span class="ltc-muted ltc-small ltc-mono">/kg</span></td>
                                        <td><span class="ltc-status ltc-status--plain">{{ row.status }}</span></td>
                                    </tr>
                                    <tr v-if="!pagedLots.length">
                                        <td colspan="5" class="ltc-muted ltc-small" style="text-align: center; padding: 24px;">
                                            {{ lotRows.length ? 'No lots match your search.' : "You haven't created any lots yet." }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="ltc-table-card__foot">
                            <span class="ltc-muted ltc-small">Showing {{ pageRangeStart }}–{{ pageRangeEnd }} of {{ filteredLots.length }} lots</span>
                            <div v-if="totalPages > 1" class="ltc-pagination">
                                <button type="button" class="ltc-page-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">Previous</button>
                                <button v-for="p in pageNumbers" :key="p" type="button" class="ltc-page-btn" :class="{ 'ltc-page-btn--active': p === currentPage }" @click="goToPage(p)">{{ p }}</button>
                                <button type="button" class="ltc-page-btn" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Right column: Recent Trading Activity ──────────────── -->
                <div class="ltc-col-side">
                    <div class="ltc-card">
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
.ltc-alert__btn { flex-shrink: 0; display: inline-block; padding: 6px 10px; border-radius: 6px; border: none; font-size: 10.5px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); white-space: nowrap; text-decoration: none; }
.ltc-alert__btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ltc-alert__btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.ltc-alert__btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ltc-alert__btn--dark { background: #000; color: #fff; }

/* Filters */
.ltc-filters { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.ltc-tabs { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; }
.ltc-tabs :deep(.el-radio-button) { margin: 0; }
.ltc-tabs :deep(.el-radio-button__inner) { padding: 7px 12px; border-radius: 6px !important; border: none; box-shadow: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 11.5px; font-weight: 700; white-space: nowrap; font-family: var(--dp-font-sans); }
.ltc-tabs :deep(.el-radio-button__inner:hover) { background: var(--dp-surface-container-low); }
.ltc-tabs :deep(.el-radio-button.is-active .el-radio-button__inner) { background: var(--dp-primary); color: var(--dp-on-primary); box-shadow: none; }
.ltc-filters__row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
/* Search/filter fields are real Element Plus <el-input>/<el-select>
   (size="small", overriding Element Plus's own default size) rather
   than native inputs. The app's global .el-input__wrapper/.el-select__wrapper
   rule (resources/css/element-overrides.css) forces min-height:48px and
   font-size:14px app-wide with !important, which silences size="small"
   everywhere unless a page-scoped override matches it back with !important
   of its own — same fix already used in Rfq/Index.vue's toolbar. */
.ltc-filters__row .ltc-search { flex: 1; min-width: 220px; }
.ltc-search .material-symbols-outlined { font-size: 17px; color: var(--dp-on-surface-variant); }
.ltc-filters__row .ltc-search :deep(.el-input__wrapper) { background: var(--dp-surface-container-low); box-shadow: none !important; border-radius: 6px; min-height: 30px !important; padding-top: 0 !important; padding-bottom: 0 !important; }
.ltc-filters__row .ltc-search :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset !important; }
.ltc-filters__row .ltc-search :deep(.el-input__inner) { font-size: 12px !important; color: var(--dp-on-surface); font-family: var(--dp-font-sans); }
.ltc-filters__row .ltc-select { width: 170px; flex-shrink: 0; }
.ltc-filters__row .ltc-select :deep(.el-select__wrapper) { background: var(--dp-surface-container-low); box-shadow: none !important; border-radius: 6px; font-weight: 600; font-family: var(--dp-font-sans); color: var(--dp-on-surface); min-height: 30px !important; padding-top: 0 !important; padding-bottom: 0 !important; }
.ltc-filters__row .ltc-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset !important; }
.ltc-filters__row .ltc-select :deep(.el-select__selected-item),
.ltc-filters__row .ltc-select :deep(.el-select__placeholder) { font-size: 11.5px !important; }
.ltc-filters__row .ltc-save-btn.el-button { height: 30px; padding: 0 12px; margin: 0; border: none; border-radius: 6px !important; background: var(--dp-surface-container-high); color: var(--dp-on-surface); font-size: 11.5px; font-weight: 700; font-family: var(--dp-font-sans); gap: 6px; }
.ltc-filters__row .ltc-save-btn.el-button:hover { background: var(--dp-surface-container-highest); color: var(--dp-on-surface); }
.ltc-save-btn .material-symbols-outlined { font-size: 16px; }

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
.ltc-table { width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: 13px; }
.ltc-table thead tr { background: var(--dp-surface-container-low); }
.ltc-table th { padding: 10px 8px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); overflow-wrap: break-word; }
.ltc-table td { padding: 11px 8px; border-top: 1px solid var(--dp-outline-variant); vertical-align: middle; overflow-wrap: break-word; }
.ltc-table .ltc-small { font-size: 12px; }
.ltc-table .ltc-chip { font-size: 11px; }
.ltc-table .ltc-status { font-size: 11px; }
.ltc-table__row { transition: background .12s ease; }
.ltc-table__row:hover { background: var(--dp-surface-container-low); }
.ltc-table__row--active { background: color-mix(in srgb, var(--dp-primary) 6%, transparent); }
.ltc-table__dot { display: inline-block; width: 5px; height: 5px; border-radius: 999px; background: var(--dp-primary); margin-right: 5px; }
.ltc-table-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; background: var(--dp-surface-container-low); flex-wrap: wrap; }


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

.ltc-dossier__section { display: flex; flex-direction: column; gap: 8px; }

.ltc-activity { display: flex; flex-direction: column; gap: 6px; }
.ltc-activity__item { display: flex; align-items: flex-start; gap: 10px; padding: 8px; background: var(--dp-surface-container-low); border-radius: 8px; }
.ltc-activity__item .material-symbols-outlined { font-size: 16px; margin-top: 1px; flex-shrink: 0; }
.ltc-activity__body { min-width: 0; flex: 1; }
.ltc-activity__top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.ltc-activity__body p { margin: 2px 0 0; }

@media (max-width: 640px) {
    .ltc-hero__actions { width: 100%; }
    .ltc-hero__actions .ltc-btn { flex: 1; justify-content: center; }
    .ltc-alerts__grid { grid-template-columns: 1fr; }
}
</style>
