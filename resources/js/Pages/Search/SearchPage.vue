<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Search, Close, Clock, Box, ArrowRight, MapLocation, Coffee, Star } from '@element-plus/icons-vue';

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
    Extreme: 'sch-badge--red',
    High: 'sch-badge--amber',
    Medium: 'sch-badge--blue',
};
</script>

<template>
    <DesignPreviewLayout title="Search">
        <Head title="Search" />

        <div class="sch-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="sch-hero">
                <div class="sch-hero__inner">
                    <span class="sch-kicker">Marketplace Search</span>
                    <h1 class="sch-title">Find coffee lots across the marketplace</h1>
                    <p class="sch-subtitle">Search live listings by name, lot code, origin, or type — then filter to narrow it down.</p>

                    <div class="sch-searchbar">
                        <el-icon :size="17" class="sch-searchbar__icon"><Search /></el-icon>
                        <input
                            v-model="queryInput"
                            type="text"
                            placeholder="Search by name, lot code, origin, type…"
                            class="sch-searchbar__input"
                            @keydown.enter="runSearch()"
                        >
                        <button v-if="queryInput" type="button" class="sch-searchbar__clear" @click="queryInput = ''; runSearch();">
                            <el-icon :size="14"><Close /></el-icon>
                        </button>
                        <button type="button" class="sch-searchbar__btn" @click="runSearch()">Search</button>
                    </div>

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
                    <Link v-for="item in results" :key="item.id" :href="route('market.show', item.id)" class="sch-card">
                        <div class="sch-card__banner">
                            <img v-if="item.image" :src="item.image" :alt="item.name">
                            <el-icon v-else :size="26"><Coffee /></el-icon>
                            <span v-if="item.demand" class="sch-badge sch-card__demand" :class="demandTone[item.demand] ?? 'sch-badge--muted'">{{ item.demand }}</span>
                        </div>
                        <div class="sch-card__body">
                            <span class="sch-card__name">{{ item.name }}</span>
                            <div class="sch-card__meta">
                                <span v-if="item.origin" class="sch-card__meta-item"><el-icon :size="11"><MapLocation /></el-icon>{{ item.origin }}</span>
                                <span v-if="item.type">{{ item.type }}</span>
                                <span v-if="item.lot_code">{{ item.lot_code }}</span>
                            </div>

                            <div v-if="item.quality_score" class="sch-card__quality">
                                <el-icon :size="11"><Star /></el-icon> {{ Number(item.quality_score).toFixed(1) }} quality score
                            </div>

                            <div class="sch-card__divider" />

                            <div class="sch-card__row">
                                <span class="sch-card__price">{{ formatMoney(item.price_per_kg) }}<small>/kg</small></span>
                                <span v-if="item.quantity" class="sch-card__qty">{{ Number(item.quantity).toLocaleString() }} kg avail.</span>
                            </div>

                            <div v-if="Array.isArray(item.badges) && item.badges.length" class="sch-card__badges">
                                <span v-for="b in item.badges" :key="b" class="sch-badge sch-badge--muted">{{ b }}</span>
                            </div>

                            <span class="sch-card__view">View full details <el-icon :size="11"><ArrowRight /></el-icon></span>
                        </div>
                    </Link>
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
}

/* ── Hero header ─────────────────────────────────────────────────────── */
.sch-hero {
    background: linear-gradient(180deg, var(--surface-container-lowest) 0%, var(--surface) 100%);
    border-bottom: 1px solid var(--card-border);
}

.sch-hero__inner { max-width: 920px; padding: 2rem 1.5rem 1.5rem; }

.sch-kicker { display: inline-block; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--brand-primary); margin-bottom: 6px; }
.sch-title { font-size: 1.625rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 0.375rem; color: var(--text-main); }
.sch-subtitle { font-size: 0.875rem; color: var(--text-muted); margin: 0 0 1.25rem; line-height: 1.6; max-width: 560px; }

.sch-searchbar {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid var(--card-border);
    border-radius: var(--radius-pill);
    padding: 5px 6px 5px 16px;
    background: var(--surface-container-lowest);
    box-shadow: var(--shadow-float);
    max-width: 720px;
}
.sch-searchbar__icon { color: var(--text-muted); flex-shrink: 0; }
.sch-searchbar__input { flex: 1; border: none; background: none; font-size: 0.875rem; padding: 9px 0; outline: none; color: var(--text-main); }
.sch-searchbar__clear { border: none; background: none; color: var(--text-muted); display: inline-flex; padding: 4px; cursor: pointer; border-radius: 6px; }
.sch-searchbar__clear:hover { background: var(--surface-container-low); }
.sch-searchbar__btn { border: none; border-radius: var(--radius-pill); background: var(--brand-primary); color: #fff; font-size: 0.8125rem; font-weight: 700; padding: 10px 22px; cursor: pointer; transition: opacity 0.15s; }
.sch-searchbar__btn:hover { opacity: 0.9; }

/* ── Filters ─────────────────────────────────────────────────────────── */
.sch-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 18px; margin-top: 1.25rem; }
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
.sch-body { max-width: 1280px; padding: 1.5rem 1.5rem 3rem; }

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

.sch-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(252px, 1fr)); gap: 14px; }

.sch-card {
    display: block;
    border: 1px solid var(--card-border);
    border-radius: var(--radius-card);
    overflow: hidden;
    background: var(--surface-container-lowest);
    text-decoration: none;
    color: inherit;
    transition: box-shadow 0.15s ease, transform 0.15s ease, border-color 0.15s ease;
}
.sch-card:hover { border-color: transparent; box-shadow: var(--shadow-float), 0 8px 24px rgba(15, 23, 42, 0.08); transform: translateY(-2px); }

.sch-card__banner {
    position: relative;
    height: 96px;
    background: linear-gradient(135deg, var(--el-color-primary-light-8), var(--el-color-primary-light-9));
    color: var(--brand-primary);
    display: flex;
    align-items: center;
    justify-content: center;
}
.sch-card__banner img { width: 100%; height: 100%; object-fit: cover; }
.sch-card__demand { position: absolute; top: 8px; right: 8px; }

.sch-card__body { padding: 14px; display: flex; flex-direction: column; gap: 5px; }
.sch-card__name { font-size: 0.9375rem; font-weight: 700; color: var(--text-main); line-height: 1.3; }

.sch-card__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; font-size: 0.75rem; color: var(--text-muted); }
.sch-card__meta-item { display: inline-flex; align-items: center; gap: 3px; }
.sch-card__meta > * + *::before { content: '·'; margin-right: 8px; color: var(--card-border); }

.sch-card__quality { display: inline-flex; align-items: center; gap: 4px; font-size: 0.75rem; font-weight: 600; color: var(--warning-amber); }

.sch-card__divider { height: 1px; background: var(--card-border); margin: 4px 0; }

.sch-card__row { display: flex; align-items: baseline; justify-content: space-between; }
.sch-card__price { font-size: 1.0625rem; font-weight: 800; color: var(--brand-primary); }
.sch-card__price small { font-size: 0.6875rem; font-weight: 600; color: var(--text-muted); }
.sch-card__qty { font-size: 0.75rem; color: var(--text-muted); }
.sch-card__badges { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 2px; }

.sch-card__view { display: inline-flex; align-items: center; gap: 4px; margin-top: 6px; padding-top: 8px; border-top: 1px solid var(--card-border); font-size: 0.75rem; font-weight: 700; color: var(--brand-primary); }

.sch-badge { display: inline-flex; border-radius: 999px; font-size: 0.625rem; font-weight: 700; padding: 3px 8px; white-space: nowrap; }
.sch-badge--red { background: #fee2e2; color: #991b1b; }
.sch-badge--amber { background: #fef3c7; color: #92400e; }
.sch-badge--blue { background: #dbeafe; color: #1e40af; }
.sch-badge--muted { background: var(--surface-container-low); color: var(--text-muted); }

@media (max-width: 575.98px) {
    .sch-hero__inner { padding: 1.25rem 1.25rem 1.25rem; }
    .sch-body { padding: 1.25rem 1.25rem 3rem; }
    .sch-filters { gap: 12px; }
}
</style>
