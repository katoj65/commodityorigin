<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import StoreInventoryLayout from '@/Layouts/StoreInventoryLayout.vue';
import { ArrowRight, FolderOpened, Ticket } from '@element-plus/icons-vue';

const props = defineProps({
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
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
});

/* ── A lot is "tokenised" once it has a real Blockchain commit record
   (Lot::blockchain, eager-loaded by StoreController::inventoryContext) —
   not the "tokenisation_ready" status, which only means it was submitted
   with intent to tokenise, not that it's actually been committed. ────── */
const tokenisedLots = computed(() => props.lots.filter((lot) => lot.blockchain));

function goToLot(row) {
    router.visit(route('lot.show', row.id));
}

function formatMoney(amount, currency) {
    const value = Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${value}` : `$${value}`;
}
</script>

<template>
    <StoreInventoryLayout
        active-tab="tokenised"
        :store="store"
        :status-options="statusOptions"
        :import-result="importResult"
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
    >
        <div class="st-table-card">
            <div class="st-list">
                <div
                    v-for="row in tokenisedLots"
                    :key="row.id"
                    class="st-list-row st-list-row--link"
                    tabindex="0"
                    role="button"
                    @click="goToLot(row)"
                    @keydown.enter="goToLot(row)"
                >
                    <div class="st-list-row__icon"><el-icon><Ticket /></el-icon></div>
                    <div class="st-list-row__main">
                        <div class="st-list-row__title">{{ row.lot_name || row.lot_number || `Lot #${row.id}` }}</div>
                        <div class="st-list-row__sub">
                            {{ row.process || '—' }}<span v-if="row.grade"> · {{ row.grade }}</span>
                            <span v-if="row.lot_name && row.lot_number"> · {{ row.lot_number }}</span>
                        </div>
                    </div>
                    <div class="st-list-row__stats">
                        <div class="st-list-stat">
                            <span class="st-list-stat__value">{{ row.net_weight_kg ? `${Number(row.net_weight_kg).toLocaleString()} kg` : '—' }}</span>
                            <span class="st-list-stat__label">Weight</span>
                        </div>
                        <span class="st-pill st-pill--b">{{ formatMoney(row.price, null) }}</span>
                        <span class="st-pill st-pill--a">{{ row.status || '—' }}</span>
                    </div>
                    <el-icon class="st-list-row__chevron"><ArrowRight /></el-icon>
                </div>
                <div v-if="!tokenisedLots.length" class="st-empty-cell">
                    <div class="st-empty-cell__icon"><el-icon :size="20"><FolderOpened /></el-icon></div>
                    No lots committed on the blockchain yet.
                </div>
            </div>

            <div class="st-pagination-foot">
                <span class="st-pagination-foot__text">Showing {{ tokenisedLots.length }} tokenised lot{{ tokenisedLots.length === 1 ? '' : 's' }}</span>
            </div>
        </div>
    </StoreInventoryLayout>
</template>

<style scoped>
.st-table-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    overflow: hidden;
}

.st-list { display: flex; flex-direction: column; padding: 4px 20px; }
.st-list-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 4px;
    border-bottom: 1px solid var(--card-border);
    transition: background .15s ease;
}
.st-list-row:last-child { border-bottom: none; }
.st-list-row:hover { background: color-mix(in srgb, var(--surface-container-low) 60%, transparent); margin: 0 -12px; padding: 14px 16px; border-radius: 10px; }
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
.st-list-row__main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.st-list-row__title { font-size: 14px; font-weight: 700; color: var(--on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.st-list-row__sub { font-size: 12.5px; color: var(--on-surface-variant); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.st-list-row__stats { display: flex; align-items: center; gap: 20px; flex-shrink: 0; }
.st-list-stat { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; min-width: 76px; }
.st-list-stat__value { font-size: 13.5px; font-weight: 700; color: var(--on-surface); font-variant-numeric: tabular-nums; white-space: nowrap; }
.st-list-stat__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-variant); white-space: nowrap; }

.st-list-row--link { cursor: pointer; }
.st-list-row--link:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; border-radius: 10px; }
.st-list-row__chevron { flex-shrink: 0; font-size: 14px; color: var(--on-surface-variant); opacity: 0; transform: translateX(-4px); transition: opacity .15s ease, transform .15s ease; }
.st-list-row--link:hover .st-list-row__chevron,
.st-list-row--link:focus-visible .st-list-row__chevron { opacity: 1; transform: translateX(0); }

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

@media (max-width: 640px) {
    .st-list { padding: 4px 12px; }
    .st-list-row { flex-wrap: wrap; }
    .st-list-row:hover { margin: 0; padding: 14px 4px; border-radius: 0; }
    .st-list-row__stats { width: 100%; justify-content: flex-start; padding-left: 54px; gap: 16px; }
}
</style>
