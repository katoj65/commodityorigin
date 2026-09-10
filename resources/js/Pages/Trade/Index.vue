<script setup>
/* Trade hub — structural/visual port of the uploaded "Institutional
   Commodity Exchange" Trade mockup (code.html / DESIGN.md), restyled
   with this app's own --dp-* design tokens instead of the mockup's own
   literal emerald/slate palette + Manrope font (see DesignPreviewLayout
   .vue's token-block comment for why literal hex/tailwind.config.js
   extension is avoided app-wide). The real dark sidebar/header shell is
   unchanged — only this page's content area is rebuilt.

   KPIs, filter options and the listings table are wired to real data —
   see TradeController::index() / MarketService::tradeListing(). Filter
   options come from the same metadata tables (CropVarietyMetadata,
   Country::coffeeProducers, ProcessingMetadata, CoffeeGrade,
   CertificationMetadata) the lot-creation forms use, so a selected
   filter value always matches real listing data. */
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import TradeLayout from '@/Layouts/TradeLayout.vue';

const props = defineProps({
    markets: { type: Array, default: () => [] },
    availableLots: { type: Number, default: 0 },
    availableVolumeKg: { type: Number, default: 0 },
    auctionCount: { type: Number, default: 0 },
    requestCount: { type: Number, default: 0 },
    filterOptions: {
        type: Object,
        default: () => ({ types: [], origins: [], processes: [], grades: [], certifications: [] }),
    },
    cropTypeOptions: { type: Array, default: () => [] },
    gradeOptions: { type: Array, default: () => [] },
});

const formatNumber = (value, opts = {}) => Number(value || 0).toLocaleString(undefined, opts);

const CURRENCY_SYMBOLS = { USD: '$', EUR: '€', GBP: '£' };
const currencySymbol = (code) => CURRENCY_SYMBOLS[code] || (code ? `${code} ` : '$');

const kpis = computed(() => [
    { label: 'Available Lots', value: formatNumber(props.availableLots), hint: 'Live listings' },
    { label: 'Available Volume', value: formatNumber(props.availableVolumeKg), hint: 'KG' },
    { label: 'Live Auctions', value: formatNumber(props.auctionCount), badge: props.auctionCount > 0 ? 'Active Now' : null, badgeTone: 'live' },
    { label: 'Open RFQs', value: formatNumber(props.requestCount), hint: 'Buyer requests' },
]);

const ALL_TYPES = 'All Types';
const ALL_ORIGINS = 'All Origins';
const ANY_PROCESS = 'Any Process';
const ANY_GRADE = 'Any Grade';
const ALL_CERTS = 'All Certs';

const filterType = ref(ALL_TYPES);
const filterOrigin = ref(ALL_ORIGINS);
const filterProcess = ref(ANY_PROCESS);
const filterGrade = ref(ANY_GRADE);
const filterCert = ref(ALL_CERTS);
const sortBy = ref('Recommended');

const filterTypeOptions = computed(() => [ALL_TYPES, ...props.filterOptions.types]);
const filterOriginOptions = computed(() => [ALL_ORIGINS, ...props.filterOptions.origins]);
const filterProcessOptions = computed(() => [ANY_PROCESS, ...props.filterOptions.processes]);
const filterGradeOptions = computed(() => [ANY_GRADE, ...props.filterOptions.grades]);
const filterCertOptions = computed(() => [ALL_CERTS, ...props.filterOptions.certifications]);
const sortOptions = ['Recommended', 'Newest Listings', 'Price: Low to High', 'Price: High to Low'];

// Certification isn't tracked per listing (only per farm), so that filter's
// options are real but not applied here — nothing to match rows against yet.
const filteredRows = computed(() => {
    const rows = props.markets.filter((row) => {
        if (filterType.value !== ALL_TYPES && row.type !== filterType.value) return false;
        if (filterOrigin.value !== ALL_ORIGINS && row.origin !== filterOrigin.value) return false;
        if (filterProcess.value !== ANY_PROCESS && row.process !== filterProcess.value) return false;
        if (filterGrade.value !== ANY_GRADE && row.grade !== filterGrade.value) return false;
        return true;
    });

    if (sortBy.value === 'Price: Low to High') return [...rows].sort((a, b) => a.price_per_kg - b.price_per_kg);
    if (sortBy.value === 'Price: High to Low') return [...rows].sort((a, b) => b.price_per_kg - a.price_per_kg);
    return rows;
});

const pageSize = 10;
const currentPage = ref(1);
watch(filteredRows, () => { currentPage.value = 1; });

const pagedRows = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredRows.value.slice(start, start + pageSize);
});

const rangeStart = computed(() => filteredRows.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize + 1);
const rangeEnd = computed(() => Math.min(currentPage.value * pageSize, filteredRows.value.length));

const pricingTag = (row) => {
    if (row.pricing_type === 'auction') return { label: 'Competitive Auction', tone: 'amber' };
    if (row.pricing_type === 'negotiable') return { label: 'Direct / Negotiable', tone: null };
    return { label: 'Direct Spot', tone: null };
};

const qualityLine = (row) => [row.grade, row.screen ? `Screen ${row.screen}` : null].filter(Boolean).join(' · ') || 'Ungraded';
const processLine = (row) => [row.process, row.quality_score ? `Cup Score ${row.quality_score}` : null].filter(Boolean).join(' · ');

const quantityLine = (row) => `${formatNumber(row.quantity)} ${(row.unit || 'kg').toUpperCase()}`;
const bagsLine = (row) => row.quantity_bags ? `${formatNumber(row.quantity_bags)} Bags` : null;

const priceLine = (row) => `${currencySymbol(row.currency)}${formatNumber(row.price_per_kg, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const priceSubLine = (row) => {
    if (row.pricing_type === 'auction') {
        return row.highest_bid
            ? `Highest Bid: ${currencySymbol(row.currency)}${formatNumber(row.highest_bid, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
            : 'No bids yet';
    }

    return `Total: ${currencySymbol(row.currency)}${formatNumber(row.total_price)}`;
};


</script>

<template>
    <TradeLayout
        :market-count="availableLots"
        :auction-count="auctionCount"
        :request-count="requestCount"
        :crop-type-options="cropTypeOptions"
        :grade-options="gradeOptions"
    >
            <!-- ── KPI cards ─────────────────────────────────────────────── -->
            <div class="trade-kpis">
                <div v-for="kpi in kpis" :key="kpi.label" class="trade-kpi">
                    <div class="trade-kpi__label">{{ kpi.label }}</div>
                    <div class="trade-kpi__row">
                        <span class="trade-kpi__value">{{ kpi.value }}</span>
                        <span v-if="kpi.badge" class="trade-pill" :class="kpi.badgeTone === 'live' ? 'trade-pill--live' : 'trade-pill--up'">{{ kpi.badge }}</span>
                        <span v-else-if="kpi.hint" class="trade-kpi__hint">{{ kpi.hint }}</span>
                    </div>
                </div>
            </div>

            <!-- ── Filter bar ────────────────────────────────────────────── -->
            <div class="trade-filters">
                <div class="trade-field">
                    <label>Coffee Type</label>
                    <el-select v-model="filterType" class="trade-el-select">
                        <el-option v-for="o in filterTypeOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
                <div class="trade-field">
                    <label>Origin</label>
                    <el-select v-model="filterOrigin" class="trade-el-select">
                        <el-option v-for="o in filterOriginOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
                <div class="trade-field">
                    <label>Processing</label>
                    <el-select v-model="filterProcess" class="trade-el-select">
                        <el-option v-for="o in filterProcessOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
                <div class="trade-field">
                    <label>Grade</label>
                    <el-select v-model="filterGrade" class="trade-el-select">
                        <el-option v-for="o in filterGradeOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
                <div class="trade-field">
                    <label>Certification</label>
                    <el-select v-model="filterCert" class="trade-el-select">
                        <el-option v-for="o in filterCertOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
                <div class="trade-field">
                    <label>Sort By</label>
                    <el-select v-model="sortBy" class="trade-el-select">
                        <el-option v-for="o in sortOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
            </div>

            <!-- ── Coffee listings table ────────────────────────────────── -->
            <div class="trade-table-card">
                <div class="trade-table-wrap">
                    <table class="trade-listings">
                        <thead>
                            <tr>
                                <th><span class="material-symbols-outlined">coffee</span> Coffee &amp; Origin</th>
                                <th><span class="material-symbols-outlined">award_star</span> Quality &amp; Process</th>
                                <th><span class="material-symbols-outlined">scale</span> Quantity &amp; Price</th>
                                <th class="trade-listings__action-col"><span class="material-symbols-outlined">bolt</span> Trading Actions</th>
                            </tr>
                        </thead>
                        <tbody v-if="pagedRows.length">
                            <tr v-for="row in pagedRows" :key="row.id" class="trade-row">
                                <td>
                                    <div class="trade-spec__top">
                                        <span class="trade-spec__name">{{ row.name }}</span>
                                        <span class="trade-tag" :class="{ 'trade-tag--amber': pricingTag(row).tone === 'amber' }">{{ pricingTag(row).label }}</span>
                                    </div>
                                    <div class="trade-spec__meta">Lot #{{ row.lot_code }} · {{ row.origin || 'Origin unspecified' }}<template v-if="row.region"> ({{ row.region }})</template></div>
                                </td>
                                <td>
                                    <div class="trade-quality__grade">{{ qualityLine(row) }}</div>
                                    <div v-if="processLine(row)" class="trade-quality__sub">{{ processLine(row) }}</div>
                                </td>
                                <td>
                                    <div class="trade-qty">{{ quantityLine(row) }}<span v-if="bagsLine(row)" class="trade-qty__inline"> · {{ bagsLine(row) }}</span></div>
                                    <div class="trade-price">{{ priceLine(row) }} <span>/ {{ (row.unit || 'kg').toUpperCase() }}</span> <span class="trade-price__sub" :class="{ 'trade-price__sub--brand': row.pricing_type !== 'auction' }">· {{ priceSubLine(row) }}</span></div>
                                </td>
                                <td class="trade-listings__action-col">
                                    <div class="trade-row-actions">
                                        <Link :href="route('market.show', row.id)" class="trade-btn trade-btn--primary trade-btn--sm">Buy</Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr class="trade-row trade-row--empty">
                                <td colspan="4" class="trade-empty">No coffee lots match your filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="trade-table-footer">
                    <div class="trade-table-footer__count">
                        Showing <strong>{{ rangeStart }}–{{ rangeEnd }}</strong> of <strong>{{ filteredRows.length }}</strong> verified coffee lots
                    </div>
                    <el-pagination
                        v-if="filteredRows.length > pageSize"
                        v-model:current-page="currentPage"
                        :page-size="pageSize"
                        :total="filteredRows.length"
                        layout="prev, pager, next"
                        background
                        class="trade-pagination"
                    />
                </div>
            </div>
    </TradeLayout>
</template>

<style scoped>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

/* ── Buttons ──────────────────────────────────────────────────────────── */
.trade-btn {
    display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border-radius: 8px;
    font-family: inherit; font-size: 12.5px; font-weight: 700; cursor: pointer; border: none; white-space: nowrap;
    text-decoration: none; transition: background .15s ease, opacity .15s ease;
}
.trade-btn .material-symbols-outlined { font-size: 15px; }
.trade-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.trade-btn--primary:hover { opacity: .88; }
.trade-btn--sm { height: 28px; padding: 0 10px; font-size: 11.5px; }

/* ── Pills ────────────────────────────────────────────────────────────── */
.trade-pill { display: inline-flex; align-items: center; font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 999px; white-space: nowrap; }
.trade-pill--up { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.trade-pill--live { background: var(--dp-error-container); color: var(--dp-on-error-container); text-transform: uppercase; letter-spacing: .04em; }


/* ── KPI cards ────────────────────────────────────────────────────────── */
.trade-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.trade-kpi { padding: 16px; border-radius: var(--dp-card-radius); border: 1px solid var(--dp-outline-variant); background: var(--dp-surface); }
.trade-kpi__label { font-size: 10.5px; text-transform: uppercase; font-weight: 700; letter-spacing: .05em; color: var(--dp-on-surface-variant); }
.trade-kpi__row { display: flex; align-items: baseline; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
.trade-kpi__value { font-family: var(--dp-font-mono); font-size: 1.5rem; font-weight: 800; color: var(--dp-on-surface); }
.trade-kpi__hint { font-size: 12px; color: var(--dp-on-surface-variant); font-weight: 600; }

/* ── Filter bar ───────────────────────────────────────────────────────── */
.trade-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); align-items: end; gap: 10px; padding: 10px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant); }
.trade-field label { display: block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); margin-bottom: 4px; }
.trade-el-select { width: 100%; }
.trade-el-select :deep(.el-select__wrapper) { border-radius: 6px; box-shadow: 0 0 0 1px var(--dp-outline-variant) inset; background: var(--dp-surface); min-height: 26px; padding: 0 8px; font-size: 11.5px; font-family: inherit; }
.trade-el-select :deep(.el-select__wrapper.is-hovering) { box-shadow: 0 0 0 1px var(--dp-outline-variant) inset; }
.trade-el-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 2px var(--dp-primary) inset; }
.trade-el-select :deep(.el-select__placeholder) { color: var(--dp-on-surface); font-weight: 600; }
.trade-el-select :deep(.el-select__caret) { font-size: 13px; }

/* ── Table ────────────────────────────────────────────────────────────── */
.trade-table-card { border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); overflow: hidden; background: var(--dp-surface); }
.trade-table-wrap { overflow-x: auto; }
.trade-listings { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
.trade-listings thead tr { background: var(--dp-surface-container-low); border-bottom: 1px solid var(--dp-outline-variant); }
.trade-listings th { padding: 9px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface-variant); white-space: nowrap; }
.trade-listings th .material-symbols-outlined { font-size: 14px; vertical-align: -2px; margin-right: 3px; }
.trade-listings__action-col { text-align: right; }
.trade-row { border-bottom: 1px solid var(--dp-outline-variant); transition: background .15s ease; }
.trade-row:last-child { border-bottom: none; }
.trade-row:hover { background: var(--dp-surface-container-low); }
.trade-listings td { padding: 8px 16px; vertical-align: middle; color: var(--dp-on-surface-variant); line-height: 1.35; }

.trade-spec__top { display: flex; align-items: center; gap: 8px; }
.trade-spec__name { font-weight: 700; color: var(--dp-on-surface); font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.trade-spec__meta { font-size: 11.5px; color: var(--dp-on-surface-variant); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 320px; }
.trade-tag { display: inline-block; flex-shrink: 0; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; padding: 1.5px 6px; border-radius: 4px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.trade-tag--amber { background: #fef3c7; color: #92400e; }

.trade-quality__grade { font-weight: 600; color: var(--dp-on-surface); font-size: 12.5px; }
.trade-quality__sub { font-size: 11.5px; color: var(--dp-on-surface-variant); margin-top: 2px; }

.trade-qty { font-family: var(--dp-font-mono); font-weight: 700; color: var(--dp-on-surface); font-size: 12.5px; }
.trade-qty__inline { font-family: inherit; font-weight: 500; color: var(--dp-on-surface-variant); }

.trade-price { font-family: var(--dp-font-mono); font-weight: 700; color: var(--dp-on-surface); font-size: 12.5px; margin-top: 2px; }
.trade-price span { font-family: inherit; font-weight: 500; color: var(--dp-on-surface-variant); font-size: 11.5px; }
.trade-price__sub--brand { color: var(--dp-on-secondary-container); font-weight: 700; }

.trade-empty { text-align: center; padding: 32px 16px; color: var(--dp-on-surface-variant); font-size: 13px; }

.trade-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 6px; flex-wrap: wrap; }

.trade-table-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 16px; border-top: 1px solid var(--dp-outline-variant); background: var(--dp-surface); }
.trade-table-footer__count { font-size: 12px; color: var(--dp-on-surface-variant); }
.trade-table-footer__count strong { color: var(--dp-on-surface); }
.trade-pagination :deep(.el-pager li) { background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); border-radius: 6px; margin: 0 2px; font-family: var(--dp-font-mono); color: var(--dp-on-surface); }
.trade-pagination :deep(.el-pager li.is-active) { background: var(--dp-primary); border-color: var(--dp-primary); color: var(--dp-on-primary); }
.trade-pagination :deep(.btn-prev), .trade-pagination :deep(.btn-next) { background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); border-radius: 6px; }

@media (max-width: 1100px) {
    .trade-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .trade-kpis { grid-template-columns: 1fr; }
}
</style>
