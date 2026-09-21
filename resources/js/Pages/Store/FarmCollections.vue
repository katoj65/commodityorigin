<script setup>
import { router } from '@inertiajs/vue3';
import StoreInventoryLayout from '@/Layouts/StoreInventoryLayout.vue';
import { CircleCheck, FolderOpened, OfficeBuilding } from '@element-plus/icons-vue';

const props = defineProps({
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
    stageProgress: { type: Array, default: () => [] },
    movementLedger: { type: Array, default: () => [] },
    chainLineage: { type: Array, default: null },
    inventoryHealth: { type: Object, default: () => ({}) },
    farmCollections: { type: Array, default: () => [] },
    batches: { type: Array, default: () => [] },
    lots: { type: Array, default: () => [] },
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
});

function goToCollection(row) {
    router.visit(route('farm-collection.show', row.id));
}

function formatDate(value) {
    if (!value) return '';
    return new Date(value.replace(' ', 'T')).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatMoney(amount, currency) {
    const value = Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${value}` : `$${value}`;
}

const STATUS_LABELS = { pending: 'Pending', batched: 'Batched' };
const STATUS_TONES = { pending: 'neutral', batched: 'positive' };
function statusLabel(status) {
    return STATUS_LABELS[status] || (status || '—');
}
function statusTone(status) {
    return STATUS_TONES[status] || 'neutral';
}

/* ── Sort comparator for el-table's built-in client-side sorting on the
   Date column — the only sortable column. collection_date is an ISO
   "YYYY-MM-DD" string, so a plain lexicographic compare already sorts
   it chronologically; this just keeps null/empty values from breaking
   localeCompare. ────────────────────────────────────────────────────── */
function stringSort(key) {
    return (a, b) => String(a[key] || '').localeCompare(String(b[key] || ''));
}
</script>

<template>
    <StoreInventoryLayout
        active-tab="collections"
        :store="store"
        :status-options="statusOptions"
        :import-result="importResult"
        :stage-progress="stageProgress"
        :movement-ledger="movementLedger"
        :chain-lineage="chainLineage"
        :inventory-health="inventoryHealth"
        :farm-collections="farmCollections"
        :batches="batches"
        :lots="lots"
        :process-options="processOptions"
        :drying-method-options="dryingMethodOptions"
        :milling-options="millingOptions"
        :coffee-type-options="coffeeTypeOptions"
        :harvest-season-options="harvestSeasonOptions"
        :coffee-grade-options="coffeeGradeOptions"
        :packaging-type-options="packagingTypeOptions"
        :origin-options="originOptions"
        :currency-options="currencyOptions"
        :currency-countries="currencyCountries"
        :flavor-options="flavorOptions"
        :body-options="bodyOptions"
        :acidity-options="acidityOptions"
        :aftertaste-options="aftertasteOptions"
        :aroma-options="aromaOptions"
    >
        <div class="st-table-card">
            <el-table
                :data="farmCollections"
                class="st-el-table"
                stripe
                :default-sort="{ prop: 'collection_date', order: 'descending' }"
                @row-click="goToCollection"
            >
                <el-table-column min-width="180">
                    <template #header><span class="st-el-table__head"><el-icon><OfficeBuilding /></el-icon>Farm</span></template>
                    <template #default="{ row }">
                        <div class="st-el-table__farm">
                            <div class="st-list-row__icon"><el-icon><OfficeBuilding /></el-icon></div>
                            <div class="st-el-table__farm-text">
                                <div class="st-list-row__title">{{ row.farm?.name || `Farm #${row.farm_id}` }}</div>
                                <div class="st-list-row__sub">
                                    <span class="st-code">{{ row.collection_code || '—' }}</span>
                                    {{ row.coffee_type || '—' }}<span v-if="row.variety"> · {{ row.variety }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column width="95" align="right">
                    <template #header>Quantity</template>
                    <template #default="{ row }">{{ Number(row.quantity || 0).toLocaleString() }} {{ row.unit || '' }}</template>
                </el-table-column>
                <el-table-column width="80" align="right">
                    <template #header>Grade</template>
                    <template #default="{ row }"><span class="st-pill st-pill--a">{{ row.initial_grade || '—' }}</span></template>
                </el-table-column>
                <el-table-column width="85" align="right">
                    <template #header>Quality</template>
                    <template #default="{ row }">{{ row.initial_quality_score != null ? Number(row.initial_quality_score).toFixed(1) : '—' }}</template>
                </el-table-column>
                <el-table-column width="90" align="right">
                    <template #header>Moisture</template>
                    <template #default="{ row }">{{ row.initial_moisture != null ? Number(row.initial_moisture).toFixed(1) + '%' : '—' }}</template>
                </el-table-column>
                <el-table-column width="140" align="right">
                    <template #header>Price</template>
                    <template #default="{ row }"><span class="st-pill st-pill--b">{{ formatMoney(row.collection_price, row.currency) }}</span></template>
                </el-table-column>
                <el-table-column width="100">
                    <template #header><span class="st-el-table__head"><el-icon><CircleCheck /></el-icon>Status</span></template>
                    <template #default="{ row }"><span class="st-tone" :class="`st-tone--${statusTone(row.status)}`">{{ statusLabel(row.status) }}</span></template>
                </el-table-column>
                <el-table-column width="105" align="right" prop="collection_date" sortable :sort-method="stringSort('collection_date')">
                    <template #header>Date</template>
                    <template #default="{ row }">{{ formatDate(row.collection_date) }}</template>
                </el-table-column>
                <template #empty>
                    <div class="st-empty-cell">
                        <div class="st-empty-cell__icon"><el-icon :size="20"><FolderOpened /></el-icon></div>
                        No farm collections recorded yet.
                    </div>
                </template>
            </el-table>

            <div class="st-pagination-foot">
                <span class="st-pagination-foot__text">{{ farmCollections.length }} farm collection{{ farmCollections.length === 1 ? '' : 's' }}</span>
                <span class="st-pagination-foot__hint">Click Date to sort</span>
            </div>
        </div>
    </StoreInventoryLayout>
</template>

<style scoped>
/* Shared list/table styling — identical across all four inventory tab
   pages, so it's duplicated per-page rather than pulled into the layout
   (each page's body content is its own independent list markup). */

.st-table-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    overflow: hidden;
}

/* ── Element Plus table — restyled with this page's own tokens instead
   of Element Plus's defaults (same --el-table-* override approach as
   Batch/BatchesPage.vue's .bt-el-table). ─────────────────────────────── */
.st-el-table { width: 100%; font-family: var(--sans); font-size: 13px; --el-table-border-color: var(--card-border); --el-table-header-bg-color: var(--surface-container-lowest); --el-table-header-text-color: var(--on-surface-variant); --el-table-row-hover-bg-color: var(--surface-container-low); --el-table-text-color: var(--on-surface); }
.st-el-table :deep(.el-table__header th.el-table__cell) { padding: 10px 0; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
.st-el-table :deep(.el-table__body td.el-table__cell) { padding: 10px 0; vertical-align: middle; }
.st-el-table :deep(.el-table__inner-wrapper::before) { display: none; }
.st-el-table :deep(.el-table__row) { cursor: pointer; }
.st-el-table :deep(.el-table__column-filter-trigger),
.st-el-table :deep(.caret-wrapper) { cursor: pointer; }
.st-el-table :deep(.sort-caret.ascending),
.st-el-table :deep(.sort-caret.descending) { border-bottom-color: var(--outline-variant); border-top-color: var(--outline-variant); }
.st-el-table :deep(th.el-table__cell.ascending .sort-caret.ascending),
.st-el-table :deep(th.el-table__cell.descending .sort-caret.descending) { border-bottom-color: var(--primary); border-top-color: var(--primary); }

.st-el-table__head { display: inline-flex; align-items: center; gap: 4px; flex-wrap: nowrap; white-space: nowrap; }
.st-el-table__head .el-icon { font-size: 12px; color: var(--on-surface-variant); flex-shrink: 0; }

.st-el-table__farm { display: flex; align-items: center; gap: 14px; }
.st-el-table__farm-text { min-width: 0; display: flex; flex-direction: column; gap: 3px; }

.st-list-row__icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--surface-container-low);
    color: var(--on-surface-variant);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 16px;
}
.st-list-row__title { font-size: 14px; font-weight: 700; color: var(--on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.st-list-row__sub { font-size: 12.5px; color: var(--on-surface-variant); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.st-empty-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 2.5rem 1rem;
    color: var(--on-surface-variant);
    font-size: 13px;
}
.st-empty-cell__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 999px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
}

.st-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}
.st-pill--a { background: var(--surface-container); color: var(--on-surface-variant); border: 1px solid color-mix(in srgb, var(--outline-variant) 50%, transparent); }
.st-pill--b { background: color-mix(in srgb, var(--secondary-container) 35%, transparent); color: var(--on-secondary-container); border: 1px solid color-mix(in srgb, var(--secondary-container) 60%, transparent); }

.st-code { font-family: monospace; font-size: 10.5px; font-weight: 700; color: var(--on-surface-variant); background: var(--surface-container-low); padding: 2px 6px; border-radius: 5px; margin-right: 6px; }

.st-tone { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.st-tone--neutral { background: var(--surface-container); color: var(--on-surface-variant); }
.st-tone--positive { background: var(--secondary-container); color: var(--on-secondary-container); }

.st-pagination-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 24px;
    border-top: 1px solid color-mix(in srgb, var(--outline-variant) 15%, transparent);
    background: color-mix(in srgb, var(--surface-container-low) 25%, transparent);
}
.st-pagination-foot__text { font-size: 12px; color: var(--on-surface-variant); }
.st-pagination-foot__hint { font-size: 11px; color: var(--on-surface-variant); opacity: .75; }

@media (max-width: 640px) {
    .st-table-card { overflow-x: auto; }
}
</style>
