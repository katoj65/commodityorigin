<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    PriceTag, Location, Coffee, SetUp, Star, Box, Coin, TrendCharts, View, ShoppingCart, Sell,
} from '@element-plus/icons-vue';
import MarketPage from './MarketPage.vue';

const props = defineProps({
    markets: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    exchangeRates: { type: Array, default: () => [] },
});

function goToListing(row) {
    router.visit(route('market.show', row.id));
}

const searchQuery = ref('');

/* ══════════════════════════════════════════════════════════════════════
   Live market listings — real data
   ══════════════════════════════════════════════════════════════════════ */
const resolveDemandTone = (demand) => {
    const d = (demand ?? '').toLowerCase();
    if (d === 'very high') return 'primary';
    if (d === 'high') return 'success';
    if (d === 'stable') return 'warning';
    if (d === 'low') return 'danger';
    return 'info';
};

const qualityTone = (score) => {
    if (score >= 85) return 'green';
    if (score >= 70) return 'amber';
    return 'red';
};

const demandBadgeTone = {
    primary: 'green-solid',
    success: 'green',
    warning: 'amber',
    danger: 'red',
    info: 'muted',
};

const demandBadgeClass = (demandTone) => `mkt-badge--${demandBadgeTone[demandTone] ?? 'muted'}`;

const listings = computed(() => props.markets.map((m) => ({
    id: m.id,
    lot_code: m.lot_code,
    name: m.name || m.lot_code,
    origin: m.origin || '—',
    type: m.type,
    process: m.process,
    qualityScore: Number(m.quality_score || 0),
    quantity: Number(m.quantity || 0),
    pricePerKg: Number(m.price_per_kg || 0),
    demand: m.demand || 'Active',
    demandTone: resolveDemandTone(m.demand),
    badges: m.badges || [],
    image: m.image || null,
})));

const uniqueOrigins = computed(() => [...new Set(listings.value.map((l) => l.origin).filter(Boolean))]);
const uniqueTypes = computed(() => [...new Set(listings.value.map((l) => l.type).filter(Boolean))]);

const filters = reactive({ origin: '', type: '', grade: '', certification: '', market: '', maxPrice: '', availability: '', buyerRegion: '', exportRegion: '' });

const filteredListings = computed(() => listings.value.filter((l) => {
    if (filters.origin && l.origin !== filters.origin) return false;
    if (filters.type && l.type !== filters.type) return false;
    if (filters.maxPrice && l.pricePerKg > Number(filters.maxPrice)) return false;
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        const hay = `${l.name} ${l.origin} ${l.type}`.toLowerCase();
        if (!hay.includes(q)) return false;
    }
    return true;
}));

const clearFilters = () => Object.assign(filters, { origin: '', type: '', grade: '', certification: '', market: '', maxPrice: '', availability: '', buyerRegion: '', exportRegion: '' });

/* ── Pagination ───────────────────────────────────────────────────────── */
const currentPage = ref(1);
const pageSize = ref(25);

const pagedListings = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return filteredListings.value.slice(start, start + pageSize.value);
});

watch(filteredListings, () => { currentPage.value = 1; });
</script>

<template>
    <MarketPage v-model:search-query="searchQuery">
        <div class="mkt-body">
            <div class="mkt-section__head">
                <div>
                    <div class="mkt-kicker">Live Data</div>
                    <h2 class="mkt-title">Live Market Listings</h2>
                </div>
                <div class="mkt-section__actions">
                    <span class="mkt-count">{{ filteredListings.length }} lot{{ filteredListings.length !== 1 ? 's' : '' }}</span>
                    <div class="mkt-btn-group">
                        <Link :href="route('market.request')" class="mkt-btn-group__item"><el-icon><ShoppingCart /></el-icon> Request</Link>
                        <Link :href="route('market.offer')" class="mkt-btn-group__item mkt-btn-group__item--solid"><el-icon><Sell /></el-icon> Offer</Link>
                    </div>
                </div>
            </div>

            <div class="mkt-card">
                <el-table :data="pagedListings" class="mkt-el-table" stripe row-key="id" empty-text="No lots match your search." @row-click="goToListing">
                    <el-table-column width="72">
                        <template #default="{ row }">
                            <div class="mkt-thumb">
                                <img v-if="row.image" :src="`/storage/${row.image}`" :alt="row.name">
                                <svg v-else class="mkt-thumb-icon" viewBox="0 0 24 24">
                                    <ellipse cx="9" cy="12" rx="5" ry="7" transform="rotate(-25 9 12)" fill="#4b2e1d" />
                                    <path d="M9 6 C7 9, 11 15, 9 18" stroke="#c9a27a" stroke-width="1.1" fill="none" transform="rotate(-25 9 12)" />
                                    <ellipse cx="16.5" cy="14.5" rx="4" ry="5.5" transform="rotate(20 16.5 14.5)" fill="#6b4226" />
                                    <path d="M16.5 10 C15 12.5, 18 16, 16.5 19" stroke="#d8b48f" stroke-width="1" fill="none" transform="rotate(20 16.5 14.5)" />
                                </svg>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="Lot" min-width="160">
                        <template #header><el-icon class="mkt-th-icon"><PriceTag /></el-icon>Lot</template>
                        <template #default="{ row }">
                            <div class="mkt-item-name">{{ row.lot_code }}</div>
                            <div class="mkt-muted" style="font-size:.7rem;">{{ row.name }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="Origin" min-width="120">
                        <template #header><el-icon class="mkt-th-icon"><Location /></el-icon>Origin</template>
                        <template #default="{ row }">{{ row.origin }}</template>
                    </el-table-column>
                    <el-table-column label="Type" min-width="100" align="center">
                        <template #header><el-icon class="mkt-th-icon"><Coffee /></el-icon>Type</template>
                        <template #default="{ row }"><span class="mkt-badge mkt-badge--muted">{{ row.type }}</span></template>
                    </el-table-column>
                    <el-table-column label="Process" min-width="110">
                        <template #header><el-icon class="mkt-th-icon"><SetUp /></el-icon>Process</template>
                        <template #default="{ row }">{{ row.process }}</template>
                    </el-table-column>
                    <el-table-column label="Quality" min-width="100" align="center">
                        <template #header><el-icon class="mkt-th-icon"><Star /></el-icon>Quality</template>
                        <template #default="{ row }"><span class="mkt-badge" :class="`mkt-badge--${qualityTone(row.qualityScore)}`">{{ row.qualityScore.toFixed(1) }}</span></template>
                    </el-table-column>
                    <el-table-column label="Quantity" min-width="110" align="right" header-align="left">
                        <template #header><el-icon class="mkt-th-icon"><Box /></el-icon>Quantity</template>
                        <template #default="{ row }"><span class="mkt-num">{{ row.quantity.toLocaleString() }} kg</span></template>
                    </el-table-column>
                    <el-table-column label="Price" min-width="110" align="right" header-align="left">
                        <template #header><el-icon class="mkt-th-icon"><Coin /></el-icon>Price</template>
                        <template #default="{ row }"><span class="mkt-num fw-semibold">${{ row.pricePerKg.toFixed(2) }}/kg</span></template>
                    </el-table-column>
                    <el-table-column label="Demand" min-width="110" align="center">
                        <template #header><el-icon class="mkt-th-icon"><TrendCharts /></el-icon>Demand</template>
                        <template #default="{ row }">
                            <span class="mkt-badge" :class="demandBadgeClass(row.demandTone)">{{ row.demand }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column width="70" align="right">
                        <template #default="{ row }">
                            <Link :href="route('market.show', row.id)" class="mkt-icon-link" title="View listing" @click.stop><el-icon><View /></el-icon></Link>
                        </template>
                    </el-table-column>
                </el-table>

                <div v-if="filteredListings.length" class="mkt-pagination">
                    <el-pagination
                        v-model:current-page="currentPage"
                        v-model:page-size="pageSize"
                        :total="filteredListings.length"
                        :page-sizes="[25, 50, 100]"
                        layout="total, sizes, prev, pager, next"
                        background
                    />
                </div>
            </div>
        </div>
    </MarketPage>
</template>

<style scoped>
.mkt-muted { color: var(--on-surface-var); }
.mkt-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Body ─────────────────────────────────────────────────────────────── */
.mkt-body { padding: 1.5rem 0 3rem; }

.mkt-section__head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: .875rem; padding: 0 1.5rem; }
.mkt-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.mkt-title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
.mkt-count { font-size: .75rem; color: var(--on-surface-var); }
.mkt-section__actions { display: flex; align-items: center; gap: 14px; }

.mkt-btn-group { display: inline-flex; border-radius: 8px; overflow: hidden; border: 1px solid var(--green); }
.mkt-btn-group__item { display: inline-flex; align-items: center; gap: 5px; padding: 7px 18px; font-size: .75rem; font-weight: 700; letter-spacing: .01em; text-decoration: none; color: var(--green); background: #fff; transition: background .15s ease, color .15s ease; white-space: nowrap; }
.mkt-btn-group__item :deep(.el-icon) { font-size: 13px; }
.mkt-btn-group__item + .mkt-btn-group__item { border-left: 1px solid var(--green); }
.mkt-btn-group__item:hover { background: #f0f5f3; }
.mkt-btn-group__item--solid { background: var(--green); color: #fff; }
.mkt-btn-group__item--solid:hover { background: var(--green-dark); }

/* ── Table card — boxed, elevated container ─────────────────────────────── */
.mkt-card {
    margin: 0 1.5rem;
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

/* ── Element Plus table, reskinned to match the market design system ──── */
.mkt-el-table { --el-table-border-color: var(--border); --el-table-header-bg-color: var(--surface-low); --el-table-header-text-color: var(--on-surface-var); --el-table-row-hover-bg-color: #f3f6f5; --el-table-text-color: var(--on-surface); font-family: inherit; }
.mkt-el-table :deep(.el-table__row) { cursor: pointer; }
.mkt-el-table :deep(.el-table__cell) { padding: 11px 0; }
.mkt-el-table :deep(.cell) { padding: 0 12px; font-size: .8125rem; line-height: 1.45; }
.mkt-el-table :deep(th.el-table__cell) { font-size: .6875rem; font-weight: 600; letter-spacing: .04em; }
.mkt-el-table :deep(th.el-table__cell .cell) { display: flex; align-items: center; white-space: nowrap; }
.mkt-el-table :deep(.el-table__cell:first-child .cell) { padding-left: 1.25rem; }
.mkt-el-table :deep(.el-table__cell:last-child .cell) { padding-right: 1.25rem; }
.mkt-el-table :deep(.el-table__inner-wrapper::before) { display: none; }
.mkt-el-table :deep(.el-table__body td.el-table__cell) { transition: background-color .12s ease; }
.mkt-el-table :deep(.el-table__body tr.el-table__row--striped td.el-table__cell) { background: #fafaf9; }
.mkt-el-table :deep(.el-table__body tr:hover > td.el-table__cell) { background: var(--el-table-row-hover-bg-color); }
.mkt-el-table :deep(.el-table__empty-block) { min-height: 160px; }
.mkt-el-table :deep(.el-table__empty-text) { color: var(--on-surface-var); font-size: .8125rem; }
.mkt-th-icon { width: 16px; height: 16px; margin-right: 5px; color: var(--green); opacity: .8; }
.mkt-num { font-variant-numeric: tabular-nums; }
.mkt-icon-link { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 7px; border: 1px solid var(--border); color: var(--on-surface-var); text-decoration: none; transition: all .15s ease; }
.mkt-icon-link:hover { background: var(--surface-low); color: var(--green); border-color: var(--green); box-shadow: 0 1px 3px rgba(0, 69, 50, .12); }

.mkt-thumb { width: 40px; height: 40px; border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f1e6d8; border: 1px solid #e6d5bf; }
.mkt-thumb img { width: 100%; height: 100%; object-fit: cover; }
.mkt-thumb-icon { width: 28px; height: 28px; }

.mkt-pagination { padding: 1rem 1.25rem 1.25rem; border-top: 1px solid var(--border); }
.mkt-pagination :deep(.el-pagination) { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; width: 100%; font-family: inherit; }
.mkt-pagination :deep(.el-pagination__total) { margin-right: auto; font-size: .8125rem; font-weight: 600; color: var(--on-surface-var); }
.mkt-pagination :deep(.el-pagination__sizes) { margin-right: 4px; }
.mkt-pagination :deep(.el-select__wrapper) { border-radius: 8px; box-shadow: 0 0 0 1px var(--border) inset; min-height: 32px; font-size: .75rem; }
.mkt-pagination :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1px var(--green) inset; }
.mkt-pagination :deep(.btn-prev),
.mkt-pagination :deep(.btn-next) { width: 32px; height: 32px; border-radius: 9px; background: #fff; border: 1px solid var(--border); color: var(--on-surface-var); transition: all .15s ease; }
.mkt-pagination :deep(.btn-prev:hover:not(:disabled)),
.mkt-pagination :deep(.btn-next:hover:not(:disabled)) { border-color: var(--green); color: var(--green); background: #f0f5f3; }
.mkt-pagination :deep(.btn-prev:disabled),
.mkt-pagination :deep(.btn-next:disabled) { opacity: .4; background: #fff; border-color: var(--border); color: var(--on-surface-var); }
.mkt-pagination :deep(.el-pager) { display: flex; align-items: center; gap: 4px; }
.mkt-pagination :deep(.el-pager li) { min-width: 32px; height: 32px; border-radius: 9px; background: #fff; border: 1px solid var(--border); color: var(--on-surface); font-size: .8125rem; font-weight: 600; transition: all .15s ease; }
.mkt-pagination :deep(.el-pager li:hover) { border-color: var(--green); color: var(--green); background: #f0f5f3; }
.mkt-pagination :deep(.el-pager li.is-active) { background: var(--green); border-color: var(--green); color: #fff; box-shadow: 0 2px 6px rgba(0, 69, 50, .25); }

/* ── Badges ───────────────────────────────────────────────────────────── */
.mkt-badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; font-size: .6875rem; font-weight: 600; padding: 3px 10px 3px 8px; line-height: 1.5; white-space: nowrap; }
.mkt-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.mkt-badge--green { background: #ecfdf5; color: #059669; }
.mkt-badge--green-solid { background: var(--green); color: #fff; }
.mkt-badge--amber { background: #fffbeb; color: #d97706; }
.mkt-badge--red { background: #fef2f2; color: #dc2626; }
.mkt-badge--muted { background: #f5f5f4; color: #78716c; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .mkt-section__head { padding: 0 1.25rem; }
    .mkt-card { margin: 0 1.25rem; border-radius: 12px; }
    .mkt-el-table :deep(.el-table__cell:first-child .cell) { padding-left: 1rem; }
    .mkt-el-table :deep(.el-table__cell:last-child .cell) { padding-right: 1rem; }
    .mkt-pagination { padding: 1rem 1rem 1.25rem; }
    .mkt-pagination :deep(.el-pagination) { justify-content: center; }
    .mkt-pagination :deep(.el-pagination__total) { margin-right: 0; width: 100%; text-align: center; order: -1; }
}
</style>
