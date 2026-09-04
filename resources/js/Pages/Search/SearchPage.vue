<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Search, Close, Clock, Box, ArrowRight, Star, ShoppingCart } from '@element-plus/icons-vue';

const props = defineProps({
    query: { type: String, default: '' },
    filters: { type: Object, default: () => ({}) },
    results: { type: Array, default: () => [] },
    recentSearches: { type: Array, default: () => [] },
});

/* ── Search form ─────────────────────────────────────────────────────── */
const queryInput = ref(props.query);
const typeFilter = ref(props.filters.type ?? '');
const originFilter = ref(props.filters.origin ?? '');
const demandFilter = ref(props.filters.demand ?? '');
const minPrice = ref(props.filters.min_price ?? '');
const maxPrice = ref(props.filters.max_price ?? '');

const typeOptions = ['Arabica', 'Robusta', 'Specialty'];
const demandOptions = ['Medium', 'High', 'Extreme'];

function runSearch(overrideQuery = null) {
    router.get(route('search.index'), {
        q: overrideQuery ?? queryInput.value,
        type: typeFilter.value || undefined,
        origin: originFilter.value || undefined,
        demand: demandFilter.value || undefined,
        min_price: minPrice.value || undefined,
        max_price: maxPrice.value || undefined,
    }, { preserveState: true, replace: true });
}

function toggleType(value) {
    typeFilter.value = typeFilter.value === value ? '' : value;
    runSearch();
}

function toggleDemand(value) {
    demandFilter.value = demandFilter.value === value ? '' : value;
    runSearch();
}

const hasActiveFilters = () =>
    !!(typeFilter.value || originFilter.value || demandFilter.value || minPrice.value || maxPrice.value);

function clearFilters() {
    typeFilter.value = '';
    originFilter.value = '';
    demandFilter.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    runSearch();
}

/* ── Recent searches ─────────────────────────────────────────────────── */
function useRecentSearch(entry) {
    queryInput.value = entry.query;
    runSearch(entry.query);
}

function removeRecentSearch(entry) {
    router.delete(route('search.history.destroy', entry.id), { preserveState: true, preserveScroll: true });
}

function clearHistory() {
    router.delete(route('search.history.clear'), { preserveState: true, preserveScroll: true });
}

/* ── Display helpers ─────────────────────────────────────────────────── */
function formatMoney(amount) {
    return `$${Number(amount ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const demandTone = {
    Extreme: 'sch-card__badge--red',
    High: 'sch-card__badge--amber',
    Medium: 'sch-card__badge--blue',
};

/* ── Card actions ─────────────────────────────────────────────────────── */
const addingId = ref(null);

function goToItem(item) {
    router.visit(route('market.show', item.id));
}

function addToCart(item) {
    addingId.value = item.id;
    router.post(route('checkout.items.store'), {
        cartable_type: 'market',
        cartable_id: item.id,
        quantity: 1,
    }, {
        preserveScroll: true,
        onFinish: () => { addingId.value = null; },
    });
}
</script>

<template>
    <DesignPreviewLayout title="Search">
        <Head title="Search" />

        <div class="sch-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="sch-hero">
                <div class="sch-hero__inner">
                    <h1 class="sch-title">Search Results</h1>

                    <div v-if="query" class="sch-filters">
                        <div class="sch-filter-group">
                            <span class="sch-filter-label">Type</span>
                            <div class="sch-pills">
                                <button
                                    v-for="t in typeOptions"
                                    :key="t"
                                    type="button"
                                    class="sch-pill"
                                    :class="{ 'sch-pill--active': typeFilter === t }"
                                    @click="toggleType(t)"
                                >{{ t }}</button>
                            </div>
                        </div>

                        <div class="sch-filter-group">
                            <span class="sch-filter-label">Demand</span>
                            <div class="sch-pills">
                                <button
                                    v-for="d in demandOptions"
                                    :key="d"
                                    type="button"
                                    class="sch-pill"
                                    :class="{ 'sch-pill--active': demandFilter === d }"
                                    @click="toggleDemand(d)"
                                >{{ d }}</button>
                            </div>
                        </div>

                        <div class="sch-filter-group">
                            <span class="sch-filter-label">Origin</span>
                            <input v-model="originFilter" type="text" placeholder="e.g. Ethiopia" class="sch-input" @change="runSearch()">
                        </div>

                        <div class="sch-filter-group">
                            <span class="sch-filter-label">Price / kg</span>
                            <div class="sch-price-range">
                                <input v-model="minPrice" type="number" min="0" step="0.01" placeholder="Min" class="sch-input sch-input--num" @change="runSearch()">
                                <span class="sch-price-range__sep">–</span>
                                <input v-model="maxPrice" type="number" min="0" step="0.01" placeholder="Max" class="sch-input sch-input--num" @change="runSearch()">
                            </div>
                        </div>

                        <button v-if="hasActiveFilters()" type="button" class="sch-clear-filters" @click="clearFilters">
                            <el-icon :size="12"><Close /></el-icon> Clear filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="sch-body">
                <!-- ── Recent searches ─────────────────────────────────── -->
                <div v-if="query && recentSearches.length" class="sch-recent">
                    <div class="sch-recent__head">
                        <span class="sch-recent__title"><el-icon :size="13"><Clock /></el-icon> Recent Searches</span>
                        <button type="button" class="sch-recent__clear" @click="clearHistory">Clear all</button>
                    </div>
                    <div class="sch-recent__chips">
                        <span v-for="entry in recentSearches" :key="entry.id" class="sch-chip">
                            <button type="button" class="sch-chip__text" @click="useRecentSearch(entry)">{{ entry.query }}</button>
                            <button type="button" class="sch-chip__remove" aria-label="Remove" @click="removeRecentSearch(entry)">
                                <el-icon :size="10"><Close /></el-icon>
                            </button>
                        </span>
                    </div>
                </div>

                <!-- ── Results ──────────────────────────────────────────── -->
                <div v-if="query" class="sch-results-head">
                    <span>{{ results.length }} result{{ results.length === 1 ? '' : 's' }} for "{{ query }}"</span>
                </div>

                <div v-if="!query" class="sch-empty">
                    <div class="sch-empty__icon"><el-icon :size="28"><Search /></el-icon></div>
                    <p class="sch-empty__title">Search the marketplace</p>
                    <p class="sch-empty__text">Type a name, lot code, origin, or type above to find live listings.</p>
                </div>

                <div v-else-if="!results.length" class="sch-empty">
                    <div class="sch-empty__icon"><el-icon :size="28"><Box /></el-icon></div>
                    <p class="sch-empty__title">No market items match your search</p>
                    <p class="sch-empty__text">Try a different keyword or clear your filters.</p>
                    <Link :href="route('market.index')" class="sch-empty__cta">Browse all coffee <el-icon :size="12"><ArrowRight /></el-icon></Link>
                </div>

                <div v-else class="sch-grid">
                    <article
                        v-for="item in results"
                        :key="item.id"
                        class="sch-card"
                        @click="goToItem(item)"
                    >
                        <div class="sch-card__media">
                            <img :src="item.image ? `/storage/${item.image}` : '/images/coffee_image.jpg'" :alt="item.name">
                            <span v-if="item.demand" class="sch-card__badge" :class="demandTone[item.demand] ?? 'sch-card__badge--muted'">{{ item.demand }}</span>
                        </div>

                        <div class="sch-card__body">
                            <div class="sch-card__meta">
                                <span class="sch-card__origin">{{ item.origin || '—' }}</span>
                                <span class="sch-card__score"><el-icon :size="12"><Star /></el-icon>{{ Number(item.quality_score || 0).toFixed(1) }}</span>
                            </div>

                            <h3 class="sch-card__title">{{ item.name }}</h3>

                            <div class="sch-card__specs">
                                <div class="sch-card__spec">
                                    <span>Process</span>
                                    <strong>{{ item.process || '—' }}</strong>
                                </div>
                                <div class="sch-card__spec">
                                    <span>Variety</span>
                                    <strong>{{ item.type || '—' }}</strong>
                                </div>
                            </div>

                            <div class="sch-card__footer">
                                <div class="sch-card__price-block">
                                    <span class="sch-card__price">{{ formatMoney(item.price_per_kg) }}</span>
                                    <span class="sch-card__price-unit">per kg</span>
                                </div>
                                <button
                                    type="button"
                                    class="sch-card__cart"
                                    :disabled="addingId === item.id"
                                    title="Add to cart"
                                    @click.stop="addToCart(item)"
                                >
                                    <el-icon :size="18"><ShoppingCart /></el-icon>
                                </button>
                            </div>

                            <div class="sch-card__stock"><el-icon :size="12"><Box /></el-icon>{{ Number(item.quantity || 0).toLocaleString() }} kg avail.</div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.sch-page {
    background: var(--surface, #f7f9fb);
    color: var(--text-main);
    min-height: 100%;
    margin-top: -24px;
}

/* ── Hero header ─────────────────────────────────────────────────────── */
.sch-hero {
    background: linear-gradient(180deg, var(--surface-container-lowest) 0%, var(--surface) 100%);
    border-bottom: 1px solid var(--card-border);
}

.sch-hero__inner { max-width: 920px; padding: 0.5rem 0 0.75rem; }

.sch-title { font-size: 1.5rem; line-height: 1.9rem; letter-spacing: -0.015em; font-weight: 800; margin: 0 0 0.75rem; color: var(--text-main); }

/* ── Filters ─────────────────────────────────────────────────────────── */
.sch-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 18px; }
.sch-filter-group { display: flex; flex-direction: column; gap: 6px; }
.sch-filter-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); }

.sch-pills { display: inline-flex; gap: 4px; padding: 3px; background: var(--surface-container-low); border-radius: var(--radius-pill); }
.sch-pill { border: none; background: none; border-radius: var(--radius-pill); padding: 6px 12px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); cursor: pointer; transition: background 0.12s, color 0.12s; white-space: nowrap; }
.sch-pill:hover { color: var(--text-main); }
.sch-pill--active { background: var(--surface-container-lowest); color: var(--brand-primary); box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08); }

.sch-input {
    border: 1px solid var(--card-border);
    border-radius: var(--radius-control);
    background: var(--surface-container-lowest);
    font-size: 0.8125rem;
    padding: 8px 10px;
    color: var(--text-main);
    min-width: 130px;
}
.sch-input:focus { outline: none; border-color: var(--brand-primary); }
.sch-input--num { min-width: 74px; width: 74px; }

.sch-price-range { display: flex; align-items: center; gap: 6px; }
.sch-price-range__sep { color: var(--text-muted); font-size: 0.75rem; }

.sch-clear-filters { display: inline-flex; align-items: center; gap: 5px; border: none; background: none; color: var(--text-muted); font-size: 0.75rem; font-weight: 700; padding: 8px 4px; cursor: pointer; }
.sch-clear-filters:hover { color: var(--danger-rose); }

/* ── Body ────────────────────────────────────────────────────────────── */
.sch-body { max-width: 1280px; padding: 0.75rem 0 2rem; }

/* ── Recent searches ─────────────────────────────────────────────────── */
.sch-recent { margin-bottom: 1.5rem; }
.sch-recent__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.sch-recent__title { display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); }
.sch-recent__clear { border: none; background: none; color: var(--brand-primary); font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.sch-recent__chips { display: flex; flex-wrap: wrap; gap: 8px; }
.sch-chip { display: inline-flex; align-items: center; gap: 4px; border: 1px solid var(--card-border); border-radius: 999px; padding: 4px 6px 4px 12px; background: var(--surface-container-lowest); }
.sch-chip__text { border: none; background: none; font-size: 0.75rem; font-weight: 600; color: var(--text-main); cursor: pointer; }
.sch-chip__remove { border: none; background: none; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; cursor: pointer; }
.sch-chip__remove:hover { background: var(--surface-container-low); color: var(--text-main); }

/* ── Results ─────────────────────────────────────────────────────────── */
.sch-results-head { font-size: 0.8125rem; font-weight: 600; color: var(--text-muted); margin-bottom: 1rem; }

.sch-empty { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 4rem 1rem; }
.sch-empty__icon { width: 56px; height: 56px; border-radius: 50%; background: var(--surface-container-low); color: var(--text-muted); display: flex; align-items: center; justify-content: center; margin-bottom: 6px; }
.sch-empty__title { font-size: 0.9375rem; font-weight: 700; color: var(--text-main); margin: 0; }
.sch-empty__text { font-size: 0.8125rem; color: var(--text-muted); margin: 0 0 6px; }
.sch-empty__cta { display: inline-flex; align-items: center; gap: 5px; font-size: 0.8125rem; font-weight: 700; color: var(--brand-primary); text-decoration: none; }
.sch-empty__cta:hover { text-decoration: underline; }

.sch-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-top: 8px; }

@media (max-width: 1180px) {
    .sch-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .sch-grid { grid-template-columns: 1fr; }
}

.sch-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--radius-card);
    padding: 16px;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: box-shadow .15s ease, transform .12s ease;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.sch-card:hover { box-shadow: 0 6px 18px rgba(15, 23, 42, 0.1); transform: translateY(-2px); }

.sch-card__media {
    position: relative; width: 100%; aspect-ratio: 4 / 3; background: var(--surface-container-low);
    border-radius: var(--radius-card); margin-bottom: 14px; overflow: hidden;
}
.sch-card__media img { width: 100%; height: 100%; object-fit: cover; mix-blend-mode: multiply; transition: transform .5s ease; }
.sch-card:hover .sch-card__media img { transform: scale(1.05); }

.sch-card__badge { position: absolute; top: 12px; right: 12px; font-size: .6875rem; font-weight: 700; padding: 4px 11px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
.sch-card__badge--red { background: #ffdad6; color: #93000a; }
.sch-card__badge--amber { background: #fef3c7; color: #92400e; }
.sch-card__badge--blue { background: #dbeafe; color: #1e40af; }
.sch-card__badge--muted { background: #E5E7EB; color: var(--text-muted); }

.sch-card__body { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.sch-card__meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.sch-card__origin { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sch-card__score {
    display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;
    background: var(--surface-container-low); color: var(--text-main);
    font-size: .75rem; font-weight: 700; padding: 2px 8px; border-radius: 6px;
}
.sch-card__score .el-icon { color: var(--warning-amber); }

.sch-card__title {
    font-size: 1rem; font-weight: 700; color: var(--text-main);
    letter-spacing: -0.005em; line-height: 1.375rem; margin: 0;
    display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    min-height: 2.75rem;
}

.sch-card__specs { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin: 2px 0 4px; }
.sch-card__spec { background: var(--surface-container-low); border-radius: 6px; padding: 7px 10px; min-width: 0; }
.sch-card__spec span { display: block; font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-bottom: 2px; }
.sch-card__spec strong { display: block; font-size: .8125rem; font-weight: 600; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.sch-card__footer {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    margin-top: auto; padding-top: 12px; border-top: 1px solid var(--surface-container-low);
}
.sch-card__price-block { display: flex; flex-direction: column; line-height: 1.15; }
.sch-card__price { font-size: 1.375rem; font-weight: 800; color: var(--brand-primary); letter-spacing: -0.01em; }
.sch-card__price-unit { font-size: .6875rem; color: var(--text-muted); }

.sch-card__cart {
    display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    width: 44px; height: 44px; border-radius: 6px; border: none;
    background: var(--surface-container-low); color: var(--text-main); cursor: pointer;
    transition: background .15s ease, color .15s ease;
}
.sch-card:hover .sch-card__cart { background: var(--brand-primary); color: #fff; }
.sch-card__cart:disabled { opacity: .6; cursor: default; }

.sch-card__stock { display: inline-flex; align-items: center; gap: 4px; font-size: .6875rem; color: var(--text-muted); white-space: nowrap; margin-top: 8px; }

@media (max-width: 575.98px) {
    .sch-hero__inner { padding: 0.5rem 0 0.75rem; }
    .sch-body { padding: 0.75rem 0 2rem; }
    .sch-filters { gap: 12px; }
}
</style>
