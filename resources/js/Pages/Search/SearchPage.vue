<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Search, Close, Clock, Box } from '@element-plus/icons-vue';

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
    'Extreme': 'sch-badge--red',
    'High': 'sch-badge--amber',
    'Medium': 'sch-badge--blue',
};
</script>

<template>
    <AppLayout title="Search" full-width flush :show-banner="false">
        <Head title="Search" />

        <div class="sch-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="sch-page-header">
               
                <div class="sch-searchbar">
                    <el-icon :size="16" class="sch-searchbar__icon"><Search /></el-icon>
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

                <div class="sch-filters">
                    <select v-model="typeFilter" class="sch-select" @change="runSearch()">
                        <option value="">All Types</option>
                        <option value="Arabica">Arabica</option>
                        <option value="Robusta">Robusta</option>
                        <option value="Specialty">Specialty</option>
                    </select>
                    <input v-model="originFilter" type="text" placeholder="Origin" class="sch-select" @change="runSearch()">
                    <select v-model="demandFilter" class="sch-select" @change="runSearch()">
                        <option value="">Any Demand</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Extreme">Extreme</option>
                    </select>
                    <input v-model="minPrice" type="number" min="0" step="0.01" placeholder="Min $/kg" class="sch-select sch-select--num" @change="runSearch()">
                    <input v-model="maxPrice" type="number" min="0" step="0.01" placeholder="Max $/kg" class="sch-select sch-select--num" @change="runSearch()">
                    <button type="button" class="sch-clear-filters" @click="clearFilters">Clear</button>
                </div>
            </div>

            <div class="sch-body">
                <!-- ── Recent searches ─────────────────────────────────── -->
                <div v-if="recentSearches.length" class="sch-recent">
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
                <div class="sch-results-head">
                    <span v-if="query">{{ results.length }} result{{ results.length === 1 ? '' : 's' }} for "{{ query }}"</span>
                    <span v-else>{{ results.length }} live market item{{ results.length === 1 ? '' : 's' }}</span>
                </div>

                <div v-if="!results.length" class="sch-empty">
                    <el-icon :size="32"><Box /></el-icon>
                    <p>No market items match your search.</p>
                </div>

                <div v-else class="sch-grid">
                    <div v-for="item in results" :key="item.id" class="sch-card">
                        <div class="sch-card__img">
                            <img v-if="item.image" :src="item.image" :alt="item.name">
                            <el-icon v-else :size="24"><Box /></el-icon>
                        </div>
                        <div class="sch-card__body">
                            <div class="sch-card__top">
                                <span class="sch-card__name">{{ item.name }}</span>
                                <span v-if="item.demand" class="sch-badge" :class="demandTone[item.demand] ?? 'sch-badge--muted'">{{ item.demand }}</span>
                            </div>
                            <div class="sch-card__meta">{{ item.origin }}<template v-if="item.type"> · {{ item.type }}</template><template v-if="item.lot_code"> · {{ item.lot_code }}</template></div>
                            <div class="sch-card__row">
                                <span class="sch-card__price">{{ formatMoney(item.price_per_kg) }}<small>/kg</small></span>
                                <span v-if="item.quantity" class="sch-card__qty">{{ Number(item.quantity).toLocaleString() }} kg</span>
                            </div>
                            <div v-if="Array.isArray(item.badges) && item.badges.length" class="sch-card__badges">
                                <span v-for="b in item.badges" :key="b" class="sch-badge sch-badge--muted">{{ b }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.sch-page {
    --green: #004532;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.sch-page-header { padding: 1.75rem 1.5rem 1.25rem; }
.sch-kicker { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--green); margin-bottom: 4px; }
.sch-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 0.25rem; }
.sch-subtitle { font-size: 0.875rem; color: var(--on-surface-var); margin: 0 0 1.25rem; line-height: 1.6; max-width: 560px; }

.sch-searchbar {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 4px 4px 4px 14px;
    background: var(--surface-low);
    max-width: 720px;
}
.sch-searchbar__icon { color: var(--on-surface-var); flex-shrink: 0; }
.sch-searchbar__input { flex: 1; border: none; background: none; font-size: 0.875rem; padding: 8px 0; outline: none; color: var(--on-surface); }
.sch-searchbar__clear { border: none; background: none; color: var(--on-surface-var); display: inline-flex; padding: 4px; cursor: pointer; border-radius: 6px; }
.sch-searchbar__clear:hover { background: #fff; }
.sch-searchbar__btn { border: none; border-radius: 8px; background: linear-gradient(135deg, #004532, #065f46); color: #fff; font-size: 0.8125rem; font-weight: 700; padding: 8px 18px; cursor: pointer; transition: opacity 0.15s; }
.sch-searchbar__btn:hover { opacity: 0.9; }

.sch-filters { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.sch-select {
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #fff;
    font-size: 0.8125rem;
    padding: 7px 10px;
    color: var(--on-surface);
    min-width: 120px;
}
.sch-select--num { min-width: 100px; }
.sch-select:focus { outline: none; border-color: var(--green); }
.sch-clear-filters { border: 1px solid var(--border); border-radius: 8px; background: #fff; color: var(--on-surface-var); font-size: 0.75rem; font-weight: 700; padding: 7px 12px; cursor: pointer; }
.sch-clear-filters:hover { background: var(--surface-low); }

/* ── Body ────────────────────────────────────────────────────────────── */
.sch-body { padding: 0 1.5rem 3rem; border-top: 1px solid var(--border); padding-top: 1.5rem; }

/* ── Recent searches ─────────────────────────────────────────────────── */
.sch-recent { margin-bottom: 1.5rem; }
.sch-recent__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.sch-recent__title { display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.sch-recent__clear { border: none; background: none; color: var(--green); font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.sch-recent__chips { display: flex; flex-wrap: wrap; gap: 8px; }
.sch-chip { display: inline-flex; align-items: center; gap: 4px; border: 1px solid var(--border); border-radius: 999px; padding: 4px 6px 4px 12px; background: var(--surface-low); }
.sch-chip__text { border: none; background: none; font-size: 0.75rem; font-weight: 600; color: var(--on-surface); cursor: pointer; }
.sch-chip__remove { border: none; background: none; color: var(--on-surface-var); display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; cursor: pointer; }
.sch-chip__remove:hover { background: #e5e7eb; color: var(--on-surface); }

/* ── Results ─────────────────────────────────────────────────────────── */
.sch-results-head { font-size: 0.8125rem; color: var(--on-surface-var); margin-bottom: 1rem; }

.sch-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 3rem 1rem; color: #d1d5db; }
.sch-empty p { font-size: 0.875rem; color: var(--on-surface-var); margin: 0; }

.sch-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }

.sch-card { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; background: #fff; transition: border-color 0.12s; }
.sch-card:hover { border-color: #d1d5db; }

.sch-card__img { height: 120px; background: var(--surface-low); display: flex; align-items: center; justify-content: center; color: #d1d5db; }
.sch-card__img img { width: 100%; height: 100%; object-fit: cover; }

.sch-card__body { padding: 12px; display: flex; flex-direction: column; gap: 6px; }
.sch-card__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.sch-card__name { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.sch-card__meta { font-size: 0.75rem; color: var(--on-surface-var); }
.sch-card__row { display: flex; align-items: baseline; justify-content: space-between; margin-top: 2px; }
.sch-card__price { font-size: 1rem; font-weight: 800; color: var(--green); }
.sch-card__price small { font-size: 0.6875rem; font-weight: 600; color: var(--on-surface-var); }
.sch-card__qty { font-size: 0.75rem; color: var(--on-surface-var); }
.sch-card__badges { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }

.sch-badge { display: inline-flex; border-radius: 999px; font-size: 0.625rem; font-weight: 700; padding: 3px 8px; white-space: nowrap; }
.sch-badge--red { background: #fee2e2; color: #991b1b; }
.sch-badge--amber { background: #fef3c7; color: #92400e; }
.sch-badge--blue { background: #dbeafe; color: #1e40af; }
.sch-badge--muted { background: #f3f4f6; color: #6b7280; }

@media (max-width: 575.98px) {
    .sch-page-header { padding: 1.25rem 1.25rem 1rem; }
    .sch-body { padding: 1.25rem 1.25rem 3rem; border-top: 1px solid var(--border); }
}
</style>
