<script setup>
/* Auctions hub — structural/visual port of the uploaded "Live Coffee
   Auctions" mockup (code.html / DESIGN.md) into the Trade section,
   restyled with this app's own --dp-* tokens (same convention as
   TradeLayout/Trade/Index.vue/Rfq/Index.vue/OrderPage.vue).

   Real data only: this app's auction system is built on Lot + Bid (no
   countdown/end-time is tracked on a lot — AuctionService::endingSoon()
   is explicitly "the oldest live lots" as an approximation), so the
   mockup's live countdown clocks, "reserve met" percentages, and
   "match rate"/"new floor lots" deltas have no real backing and were
   dropped. */
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import TradeLayout from '@/Layouts/TradeLayout.vue';
import AuctionLotTable from '@/Components/Auction/AuctionLotTable.vue';
import AuctionBidsTable from '@/Components/Auction/AuctionBidsTable.vue';

const props = defineProps({
    overview: { type: Object, default: () => ({}) },
    featuredLots: { type: Array, default: () => [] },
    endingSoon: { type: Array, default: () => [] },
    upcoming: { type: Array, default: () => [] },
    myBids: { type: Array, default: () => [] },
    myAuctions: { type: Array, default: () => [] },
    marketCount: { type: Number, default: 0 },
    auctionCount: { type: Number, default: 0 },
    requestCount: { type: Number, default: 0 },
    cropTypeOptions: { type: Array, default: () => [] },
    gradeOptions: { type: Array, default: () => [] },
});

/* ── KPIs — real counts/sums from AuctionService::overview(). ─────────── */
const kpis = computed(() => {
    const o = props.overview;
    const volumeKg = o.total_volume_kg ?? 0;

    return [
        { label: 'Live Auctions', value: (o.live_auctions ?? 0).toLocaleString(), icon: 'gavel', hint: 'Real-time competitive bid rooms' },
        { label: 'Total Volume', value: `${(volumeKg / 1000).toLocaleString(undefined, { maximumFractionDigits: 1 })} MT`, icon: 'package_2', hint: `${volumeKg.toLocaleString()} kg across live lots` },
        { label: 'Bids Today', value: (o.bids_today ?? 0).toLocaleString(), icon: 'bolt', hint: `${(o.total_bids ?? 0).toLocaleString()} bids placed in total` },
        { label: 'Active Buyers', value: (o.active_buyers ?? 0).toLocaleString(), icon: 'group', hint: 'Distinct bidders across all lots' },
    ];
});

/* ── Merge every lot the viewer can see into one deduplicated list, so
   "All" and the filter dropdowns see the full picture. ───────────────── */
const allLots = computed(() => {
    const seen = new Set();
    const merged = [];

    for (const lot of [...props.featuredLots, ...props.upcoming, ...props.myAuctions]) {
        if (seen.has(lot.id)) continue;
        seen.add(lot.id);
        merged.push(lot);
    }

    return merged;
});

const endingSoonIds = computed(() => new Set(props.endingSoon.map((l) => l.id)));
const myAuctionIds = computed(() => new Set(props.myAuctions.map((l) => l.id)));

function matchesTab(key, lot) {
    switch (key) {
        case 'live': return lot.status !== 'draft';
        case 'upcoming': return lot.status === 'draft';
        case 'ending_soon': return endingSoonIds.value.has(lot.id);
        case 'my_auctions': return myAuctionIds.value.has(lot.id);
        default: return true;
    }
}

const activeTab = ref('all');
const isBidsTab = computed(() => activeTab.value === 'my_bids');

const tabs = computed(() => [
    { key: 'all', label: 'All', count: allLots.value.length },
    { key: 'live', label: 'Live', count: allLots.value.filter((l) => matchesTab('live', l)).length },
    { key: 'upcoming', label: 'Upcoming', count: allLots.value.filter((l) => matchesTab('upcoming', l)).length },
    { key: 'ending_soon', label: 'Ending Soon', count: props.endingSoon.length },
    { key: 'my_auctions', label: 'My Auctions', count: props.myAuctions.length },
    { key: 'my_bids', label: 'My Bids', count: props.myBids.length },
]);

/* ── Search, filters (options derived from the real lot data — never a
   catalog that might not match what's actually loaded) and sort. ─────── */
const search = ref('');

const ALL_TYPES = 'All Types';
const ALL_ORIGINS = 'All Origins';
const ALL_GRADES = 'All Grades';
const filterVariety = ref(ALL_TYPES);
const filterOrigin = ref(ALL_ORIGINS);
const filterGrade = ref(ALL_GRADES);

const varietyOptions = computed(() => [ALL_TYPES, ...new Set(allLots.value.map((l) => l.variety).filter(Boolean))]);
const originOptions = computed(() => [ALL_ORIGINS, ...new Set(allLots.value.map((l) => l.origin_country).filter(Boolean))]);
const gradeFilterOptions = computed(() => [ALL_GRADES, ...new Set(allLots.value.map((l) => l.grade).filter(Boolean))]);

const sortBy = ref('Recommended');
const sortOptions = ['Recommended', 'Newest', 'Highest Current Bid', 'Lowest Starting Price'];

const filteredLots = computed(() => {
    let rows = allLots.value.filter((l) => matchesTab(activeTab.value, l));

    if (filterVariety.value !== ALL_TYPES) rows = rows.filter((l) => l.variety === filterVariety.value);
    if (filterOrigin.value !== ALL_ORIGINS) rows = rows.filter((l) => l.origin_country === filterOrigin.value);
    if (filterGrade.value !== ALL_GRADES) rows = rows.filter((l) => l.grade === filterGrade.value);

    const q = search.value.trim().toLowerCase();
    if (q) {
        rows = rows.filter((l) => [l.lot_name, l.lot_number, l.origin_country, l.grower]
            .filter(Boolean).join(' ').toLowerCase().includes(q));
    }

    const sorted = [...rows];
    // No created_at is exposed on a shaped lot — id order (auto-increment)
    // is a real, honest proxy for "newest" rather than a fabricated date.
    if (sortBy.value === 'Newest') return sorted.sort((a, b) => b.id - a.id);
    if (sortBy.value === 'Highest Current Bid') return sorted.sort((a, b) => (b.current_bid ?? b.starting_price) - (a.current_bid ?? a.starting_price));
    if (sortBy.value === 'Lowest Starting Price') return sorted.sort((a, b) => a.starting_price - b.starting_price);
    return sorted;
});

/* ── Export — a real CSV of whatever the current tab/filters/search/sort
   are showing. ────────────────────────────────────────────────────────── */
function exportCsv() {
    let header;
    let rows;

    if (isBidsTab.value) {
        header = ['Lot Number', 'Bid Amount', 'Quantity (kg)', 'Status', 'Placed'];
        rows = props.myBids.map((b) => [b.lot_number, b.amount, b.quantity, b.status, b.placed_ago]);
    } else {
        header = ['Lot Number', 'Lot Name', 'Origin', 'Variety', 'Grade', 'Quantity (kg)', 'Starting Price', 'Current Bid', 'Bids', 'Status'];
        rows = filteredLots.value.map((l) => [l.lot_number, l.lot_name, l.origin_country, l.variety, l.grade, l.net_weight_kg, l.starting_price, l.current_bid, l.bid_count, l.status]);
    }

    const csv = [header, ...rows]
        .map((row) => row.map((value) => `"${String(value ?? '').replace(/"/g, '""')}"`).join(','))
        .join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `bean-origin-auctions-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <TradeLayout
        title="Auctions"
        badge="Execution &amp; Bidding Desk"
        subtitle="Discover and participate in competitive coffee auctions from verified sellers."
        :market-count="marketCount"
        :auction-count="auctionCount"
        :request-count="requestCount"
        :crop-type-options="cropTypeOptions"
        :grade-options="gradeOptions"
    >
        <div class="auc-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="auc-page-header">
                <div class="auc-page-header__actions">
                    <button type="button" class="auc-btn auc-btn--outline" @click="exportCsv">
                        <span class="material-symbols-outlined">download</span> Export Bid Activity (CSV)
                    </button>
                    <Link :href="route('lot.create')" class="auc-btn auc-btn--primary">
                        <span class="material-symbols-outlined">add_circle</span> Create Auction
                    </Link>
                </div>
            </div>

            <!-- ── KPI cards ─────────────────────────────────────────────── -->
            <div class="auc-kpis">
                <div v-for="kpi in kpis" :key="kpi.label" class="auc-kpi">
                    <div class="auc-kpi__head">
                        <span class="auc-kpi__label">{{ kpi.label }}</span>
                        <span class="material-symbols-outlined">{{ kpi.icon }}</span>
                    </div>
                    <div class="auc-kpi__val">{{ kpi.value }}</div>
                    <p class="auc-kpi__hint">{{ kpi.hint }}</p>
                </div>
            </div>

            <!-- ── Tabs + sort ───────────────────────────────────────────── -->
            <div class="auc-toolbar">
                <div class="auc-tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="auc-tab"
                        :class="{ 'auc-tab--active': activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }} ({{ tab.count }})
                    </button>
                </div>
                <el-select v-if="!isBidsTab" v-model="sortBy" class="auc-el-select">
                    <el-option v-for="o in sortOptions" :key="o" :label="`Sort: ${o}`" :value="o" />
                </el-select>
            </div>

            <!-- ── Search + filters (lot tabs only) ─────────────────────── -->
            <div v-if="!isBidsTab" class="auc-filters">
                <div class="auc-search">
                    <span class="material-symbols-outlined">search</span>
                    <input v-model="search" type="text" placeholder="Search coffee, lot number, origin, grower…" />
                </div>
                <el-select v-model="filterVariety" class="auc-el-select">
                    <el-option v-for="o in varietyOptions" :key="o" :label="o" :value="o" />
                </el-select>
                <el-select v-model="filterOrigin" class="auc-el-select">
                    <el-option v-for="o in originOptions" :key="o" :label="o" :value="o" />
                </el-select>
                <el-select v-model="filterGrade" class="auc-el-select">
                    <el-option v-for="o in gradeFilterOptions" :key="o" :label="o" :value="o" />
                </el-select>
            </div>

            <!-- ── Main table ────────────────────────────────────────────── -->
            <div class="auc-table-card">
                <AuctionBidsTable v-if="isBidsTab" :bids="myBids" empty-text="You haven't placed any bids yet." />
                <AuctionLotTable
                    v-else
                    :lots="filteredLots"
                    :mode="activeTab === 'ending_soon' ? 'soon' : (activeTab === 'my_auctions' ? 'mine' : 'live')"
                    empty-text="No auctions match your filters."
                />
            </div>

            <!-- ── My Active Bids / My Managed Auctions summary ─────────── -->
            <div class="auc-summary-grid">
                <div class="auc-summary-card">
                    <div class="auc-summary-card__head">
                        <h3><span class="material-symbols-outlined">how_to_reg</span> My Active Bids</h3>
                        <span class="auc-summary-card__count">{{ myBids.length }} positions</span>
                    </div>
                    <AuctionBidsTable :bids="myBids.slice(0, 5)" empty-text="You haven't placed any bids yet." />
                </div>
                <div class="auc-summary-card">
                    <div class="auc-summary-card__head">
                        <h3><span class="material-symbols-outlined">work</span> My Managed Auctions</h3>
                        <span class="auc-summary-card__count">{{ myAuctions.length }} listed</span>
                    </div>
                    <AuctionLotTable :lots="myAuctions.slice(0, 5)" mode="mine" empty-text="You haven't listed any auctions." />
                </div>
            </div>

            <!-- ── Lifecycle footnote ────────────────────────────────────── -->
            <div class="auc-lifecycle">
                <div class="auc-lifecycle__steps">
                    <span class="auc-lifecycle__label">Bean Origin Auction Engine:</span>
                    <span class="auc-lifecycle__step">Draft</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                    <span class="auc-lifecycle__step auc-lifecycle__step--active">Live</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                    <span class="auc-lifecycle__step">Ended</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                    <span class="auc-lifecycle__step">Awarded</span>
                </div>
                <div class="auc-lifecycle__contract">
                    <strong>Lot (Source of Truth) → Live Auction → Bid</strong>
                </div>
            </div>
        </div>
    </TradeLayout>
</template>

<style scoped>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }
.auc-page { display: flex; flex-direction: column; gap: 18px; }

/* ── Page header ──────────────────────────────────────────────────────── */
.auc-page-header { display: flex; align-items: center; justify-content: flex-end; }
.auc-page-header__actions { display: flex; gap: 8px; flex-wrap: wrap; }
.auc-btn {
    display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 8px;
    font-family: inherit; font-size: 12.5px; font-weight: 700; cursor: pointer; border: none; white-space: nowrap;
    text-decoration: none; transition: background .15s ease, opacity .15s ease;
}
.auc-btn .material-symbols-outlined { font-size: 16px; }
.auc-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.auc-btn--primary:hover { opacity: .88; }
.auc-btn--outline { background: var(--dp-surface); color: var(--dp-on-surface); border: 1px solid var(--dp-outline-variant); }
.auc-btn--outline:hover { background: var(--dp-surface-container-low); }

/* ── KPI cards ────────────────────────────────────────────────────────── */
.auc-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.auc-kpi { padding: 16px; border-radius: var(--dp-card-radius); border: 1px solid var(--dp-outline-variant); background: var(--dp-surface); }
.auc-kpi__head { display: flex; align-items: center; justify-content: space-between; }
.auc-kpi__head .material-symbols-outlined { font-size: 18px; color: var(--dp-on-surface-variant); }
.auc-kpi__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.auc-kpi__val { font-family: var(--dp-font-mono); font-size: 1.5rem; font-weight: 800; color: var(--dp-on-surface); margin-top: 6px; }
.auc-kpi__hint { font-size: 11.5px; color: var(--dp-on-surface-variant); margin: 2px 0 0; }

/* ── Tabs + sort ──────────────────────────────────────────────────────── */
.auc-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--dp-outline-variant); padding-bottom: 10px; }
.auc-tabs { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.auc-tab {
    padding: 8px 14px; border-radius: 8px; border: none; background: transparent; cursor: pointer;
    font-family: inherit; font-size: 12.5px; font-weight: 700; color: var(--dp-on-surface-variant); white-space: nowrap;
    transition: background .15s ease, color .15s ease;
}
.auc-tab:hover:not(.auc-tab--active) { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.auc-tab--active { background: var(--dp-primary); color: var(--dp-on-primary); }

.auc-el-select { width: 190px; }
.auc-el-select :deep(.el-select__wrapper) {
    height: 36px; min-height: 36px !important; border-radius: 6px; box-shadow: 0 0 0 1px var(--dp-outline-variant) inset !important;
    background: var(--dp-surface); font-size: 12.5px; font-family: inherit;
}
.auc-el-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset !important; }

/* ── Search + filters ─────────────────────────────────────────────────── */
.auc-filters { display: flex; flex-wrap: wrap; gap: 10px; padding: 12px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant); }
.auc-search { display: flex; align-items: center; gap: 8px; height: 36px; padding: 0 12px; border-radius: 6px; background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); flex: 2 1 260px; }
.auc-search .material-symbols-outlined { font-size: 17px; color: var(--dp-on-surface-variant); }
.auc-search input { flex: 1; border: none; outline: none; background: transparent; font-family: inherit; font-size: 12.5px; color: var(--dp-on-surface); min-width: 0; }
.auc-search input::placeholder { color: var(--dp-on-surface-variant); }
.auc-filters .auc-el-select { flex: 1 1 160px; width: auto; }

/* ── Table card ───────────────────────────────────────────────────────── */
.auc-table-card { border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); background: var(--dp-surface); padding: 16px; overflow: hidden; }

/* ── Summary grid ─────────────────────────────────────────────────────── */
.auc-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; }
.auc-summary-card { border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); background: var(--dp-surface); padding: 16px; }
.auc-summary-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.auc-summary-card__head h3 { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: var(--dp-on-surface); margin: 0; }
.auc-summary-card__head .material-symbols-outlined { font-size: 18px; color: var(--dp-primary); }
.auc-summary-card__count { font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant); background: var(--dp-surface-container-high); padding: 2px 9px; border-radius: 999px; }

/* ── Lifecycle footnote ───────────────────────────────────────────────── */
.auc-lifecycle { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 16px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant); }
.auc-lifecycle__steps { display: flex; align-items: center; gap: 8px; font-family: var(--dp-font-mono); font-size: 11.5px; flex-wrap: wrap; }
.auc-lifecycle__label { font-weight: 700; color: var(--dp-on-surface); margin-right: 2px; }
.auc-lifecycle__step { padding: 3px 9px; border-radius: 6px; background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface-variant); font-weight: 600; }
.auc-lifecycle__step--active { background: var(--dp-primary); border-color: var(--dp-primary); color: var(--dp-on-primary); }
.auc-lifecycle__steps .material-symbols-outlined { font-size: 14px; color: var(--dp-on-surface-variant); }
.auc-lifecycle__contract { font-size: 11.5px; color: var(--dp-on-surface-variant); }

@media (max-width: 1100px) {
    .auc-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .auc-kpis { grid-template-columns: 1fr; }
}
</style>
