<script setup>
import { computed, ref } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { Goods, RefreshLeft, Search, Setting, Shop } from '@element-plus/icons-vue';

const props = defineProps({
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
    items: { type: Array, default: () => [] },
});

/* ── Category pill tone — hashed so the same category always renders the
   same tone, mirroring the mockup's alternating Light/Dark roast pills
   without hard-coding per-category colors. ─────────────────────────── */
const TONES = ['mkt-pill--a', 'mkt-pill--b', 'mkt-pill--c', 'mkt-pill--d'];

function pillTone(value) {
    if (!value) return TONES[0];
    let hash = 0;
    for (let i = 0; i < value.length; i++) hash = (hash * 31 + value.charCodeAt(i)) % TONES.length;
    return TONES[hash];
}

/* ── Filters ─────────────────────────────────────────────────────────── */
const searchQuery = ref('');
const categoryFilter = ref('all');
const sellerFilter = ref('all');
const sortBy = ref('newest');

const categories = computed(() => {
    const seen = new Set();
    props.items.forEach((i) => { if (i.category) seen.add(i.category); });
    return [...seen].sort();
});

const sellers = computed(() => {
    const seen = new Set();
    props.items.forEach((i) => { if (i.seller_name) seen.add(i.seller_name); });
    return [...seen].sort();
});

function resetFilters() {
    searchQuery.value = '';
    categoryFilter.value = 'all';
    sellerFilter.value = 'all';
    sortBy.value = 'newest';
}

const filteredItems = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();

    const filtered = props.items.filter((i) => {
        const matchesCategory = categoryFilter.value === 'all' || i.category === categoryFilter.value;
        const matchesSeller = sellerFilter.value === 'all' || i.seller_name === sellerFilter.value;
        const matchesSearch = !q || [i.name, i.category, i.seller_name].filter(Boolean).join(' ').toLowerCase().includes(q);
        return matchesCategory && matchesSeller && matchesSearch;
    });

    const sorted = [...filtered];
    if (sortBy.value === 'price_asc') sorted.sort((a, b) => a.price - b.price);
    else if (sortBy.value === 'price_desc') sorted.sort((a, b) => b.price - a.price);
    else sorted.sort((a, b) => (b.created_at ?? '').localeCompare(a.created_at ?? ''));

    return sorted;
});

function formatMoney(value, currency) {
    return `${currency} ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
</script>

<template>
    <StoreLayout title="Market" :store="store" :status-options="statusOptions" :import-result="importResult">
        <div class="mkt-body">
            <!-- ── Editorial intro ───────────────────────────────────────── -->
            <div class="mkt-hero">
                <span class="mkt-kicker">The Store · Bean Origin</span>
                <h1 class="dp-display-md">Market</h1>
                <p class="mkt-subtitle">Browse available lots listed by verified sellers across the platform — direct from cooperatives, traders, and roasters.</p>
            </div>

            <!-- ── Search + filters ──────────────────────────────────────── -->
            <div class="mkt-controls">
                <div class="mkt-search-row">
                    <div class="mkt-search">
                        <el-icon><Search /></el-icon>
                        <input v-model="searchQuery" type="text" placeholder="Search items or sellers…">
                    </div>
                    <el-tooltip content="Filter Options" placement="top">
                        <span class="mkt-filter-toggle"><el-icon :size="18"><Setting /></el-icon></span>
                    </el-tooltip>
                </div>

                <div class="mkt-filter-bar">
                    <el-select v-model="categoryFilter" class="mkt-filter-pill" placeholder="Category">
                        <el-option label="Category: All" value="all" />
                        <el-option v-for="c in categories" :key="c" :label="c" :value="c" />
                    </el-select>
                    <el-select v-model="sellerFilter" class="mkt-filter-pill" placeholder="Seller">
                        <el-option label="Seller: All" value="all" />
                        <el-option v-for="s in sellers" :key="s" :label="s" :value="s" />
                    </el-select>
                    <el-select v-model="sortBy" class="mkt-filter-pill" placeholder="Sort">
                        <el-option label="Newest First" value="newest" />
                        <el-option label="Price: Low to High" value="price_asc" />
                        <el-option label="Price: High to Low" value="price_desc" />
                    </el-select>
                    <button type="button" class="mkt-reset" @click="resetFilters">
                        <el-icon :size="14"><RefreshLeft /></el-icon> Reset Filters
                    </button>
                </div>
            </div>

            <!-- ── Product table ─────────────────────────────────────────── -->
            <div class="mkt-table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 mkt-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th class="text-end">Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in filteredItems" :key="item.id" class="mkt-row">
                                <td>
                                    <span class="mkt-thumb">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name">
                                        <el-icon v-else :size="16"><Goods /></el-icon>
                                    </span>
                                </td>
                                <td class="mkt-name">{{ item.name }}</td>
                                <td>
                                    <span v-if="item.category" class="mkt-pill" :class="pillTone(item.category)">{{ item.category }}</span>
                                    <span v-else class="mkt-muted">—</span>
                                </td>
                                <td>
                                    <span class="mkt-seller"><el-icon :size="12"><Shop /></el-icon> {{ item.seller_name || '—' }}</span>
                                </td>
                                <td>
                                    <span class="mkt-price">{{ formatMoney(item.price, item.currency_code) }}</span><small v-if="item.unit" class="mkt-unit">/{{ item.unit }}</small>
                                </td>
                                <td class="text-end mkt-muted">{{ item.quantity }} {{ item.unit || '' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!filteredItems.length" class="mkt-no-results">
                        <el-icon :size="18"><Search /></el-icon>
                        <span v-if="searchQuery || categoryFilter !== 'all' || sellerFilter !== 'all'">No items match your filters.</span>
                        <span v-else>No items are available across verified stores yet.</span>
                    </div>
                </div>

                <div v-if="filteredItems.length" class="mkt-cards">
                    <div v-for="item in filteredItems" :key="item.id" class="mkt-card">
                        <div class="mkt-card__top">
                            <span class="mkt-thumb">
                                <img v-if="item.image_url" :src="item.image_url" :alt="item.name">
                                <el-icon v-else :size="15"><Goods /></el-icon>
                            </span>
                            <div class="mkt-card__title">
                                <span class="mkt-name">{{ item.name }}</span>
                                <span v-if="item.category" class="mkt-pill" :class="pillTone(item.category)">{{ item.category }}</span>
                            </div>
                        </div>
                        <div class="mkt-card__body">
                            <span class="mkt-seller"><el-icon :size="12"><Shop /></el-icon> {{ item.seller_name || '—' }}</span>
                            <span class="mkt-price">{{ formatMoney(item.price, item.currency_code) }}<small v-if="item.unit" class="mkt-unit">/{{ item.unit }}</small></span>
                        </div>
                        <div class="mkt-card__footer mkt-muted">{{ item.quantity }} {{ item.unit || '' }} available</div>
                    </div>
                </div>
                <div v-else class="mkt-no-results mkt-cards">
                    <el-icon :size="18"><Search /></el-icon>
                    <span v-if="searchQuery || categoryFilter !== 'all' || sellerFilter !== 'all'">No items match your filters.</span>
                    <span v-else>No items are available across verified stores yet.</span>
                </div>
            </div>
        </div>
    </StoreLayout>
</template>

<style scoped>
.mkt-body {
    padding: 2.5rem 2rem 3rem;
    display: flex;
    flex-direction: column;
    gap: 24px;
    max-width: 1280px;
    margin: 0 auto;
    width: 100%;
}

/* ── Editorial intro ─────────────────────────────────────────────────── */
.mkt-hero { display: flex; flex-direction: column; gap: 10px; max-width: 640px; }
.mkt-hero h1 { color: var(--dp-primary); }
.mkt-kicker {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: var(--dp-primary);
}
.mkt-subtitle { font-size: 15px; line-height: 1.65; color: var(--dp-on-surface-variant); margin: 0; }

/* ── Search + filters ────────────────────────────────────────────────── */
.mkt-controls { display: flex; flex-direction: column; gap: 12px; }
.mkt-search-row { display: flex; align-items: center; gap: 12px; }
.mkt-search {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 18px;
    height: 46px;
    flex: 1;
    max-width: 360px;
    border-radius: 999px;
    background: var(--dp-surface-container-lowest);
    box-shadow: var(--dp-card-shadow);
    color: var(--dp-on-surface-variant);
}
.mkt-search :deep(.el-icon) { font-size: 16px; }
.mkt-search input { border: none; outline: none; background: transparent; font-size: 14px; color: var(--dp-on-surface); width: 100%; font-family: inherit; }

.mkt-filter-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--dp-surface-container-highest);
    color: var(--dp-on-surface);
    box-shadow: var(--dp-card-shadow);
    cursor: default;
}

.mkt-filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    background: var(--dp-surface-container-lowest);
    border-radius: 14px;
    box-shadow: var(--dp-card-shadow);
    padding: 14px 16px;
}
.mkt-filter-pill { width: 168px; }
.mkt-filter-pill :deep(.el-select__wrapper) {
    min-height: 40px !important;
    height: 40px !important;
    border-radius: 10px;
    background: var(--dp-surface-container-low);
    box-shadow: none !important;
    font-size: 13px;
    font-weight: 600;
    color: var(--dp-on-surface-variant);
    padding-left: 14px;
}
.mkt-filter-pill :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset !important; }
.mkt-filter-pill :deep(.el-select__placeholder) { color: var(--dp-on-surface-variant); }

.mkt-reset {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-left: auto;
    padding: 0;
    border: none;
    background: none;
    color: var(--dp-primary);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}
.mkt-reset:hover { text-decoration: underline; }
.mkt-reset:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 3px; }

/* ── Table card ──────────────────────────────────────────────────────── */
.mkt-table-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    overflow: hidden;
}

.mkt-table thead th {
    background: var(--dp-surface-container-low);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--dp-on-surface-variant);
    padding: 14px 16px;
    border-bottom-color: transparent;
    white-space: nowrap;
}
.mkt-table tbody td { padding: 12px 16px; font-size: 13.5px; border-color: color-mix(in srgb, var(--dp-outline-variant) 25%, transparent); vertical-align: middle; }
.mkt-row:last-child td { border-bottom: none; }
.mkt-row:hover { background: var(--dp-surface-container-low); }

.mkt-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
    flex-shrink: 0;
    overflow: hidden;
}
.mkt-thumb img { width: 100%; height: 100%; object-fit: cover; }
.mkt-name { font-size: 14px; font-weight: 700; color: var(--dp-on-surface); }
.mkt-muted { color: var(--dp-on-surface-variant); }
.mkt-price { font-size: 14px; font-weight: 700; color: var(--dp-primary); font-family: var(--dp-font-mono); }
.mkt-unit { font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); margin-left: 1px; }
.mkt-seller { display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; color: var(--dp-on-surface-variant); }
.mkt-seller :deep(.el-icon) { color: var(--dp-outline); flex-shrink: 0; }

/* Category pill — tone hashed per value, echoing the mockup's alternating
   Light/Dark roast-level badge treatment across varied real categories. */
.mkt-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}
.mkt-pill--a { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.mkt-pill--b { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.mkt-pill--c { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.mkt-pill--d { background: var(--dp-tertiary-fixed); color: var(--dp-on-tertiary-fixed); }

.mkt-no-results {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 2.5rem 1rem;
    font-size: 13px;
    color: var(--dp-on-surface-variant);
}
.mkt-no-results :deep(.el-icon) { color: var(--dp-outline); }

/* ── Mobile card list (replaces table below 640px) ──────────────────────── */
.mkt-cards { display: none; }
.mkt-card { padding: 14px 16px; border-bottom: 1px solid color-mix(in srgb, var(--dp-outline-variant) 25%, transparent); }
.mkt-card:last-child { border-bottom: none; }
.mkt-card__top { display: flex; align-items: center; gap: 10px; }
.mkt-card__title { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; align-items: flex-start; }
.mkt-card__body { display: flex; align-items: baseline; justify-content: space-between; margin: 10px 0 4px; padding-left: 58px; }
.mkt-card__footer { font-size: 12px; padding-left: 58px; }

@media (max-width: 900px) {
    .mkt-filter-pill { width: 150px; }
}

@media (max-width: 640px) {
    .mkt-body { padding: 1.5rem 1.25rem 2rem; }
    .mkt-search { max-width: none; }
    .mkt-filter-pill { width: 100%; }
    .mkt-filter-bar { flex-direction: column; align-items: stretch; }
    .mkt-reset { margin-left: 0; justify-content: center; padding: 8px 0 0; }
    .table-responsive { display: none; }
    .mkt-cards { display: block; }
}
</style>
