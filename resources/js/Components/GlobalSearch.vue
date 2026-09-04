<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { ArrowDown, Box, Search } from '@element-plus/icons-vue';

// ── Header search — ported from AppLayout.vue's global search exactly
// (same debounced /search/suggest endpoint, same keyboard nav, same
// submit-to-results-page behavior), skinned in dp-* classes.
const page = usePage();

// Keep the search bar in sync with the current URL's `q` query param, so
// the search word stays visible when the user lands on /search.
function currentQuery() {
    try {
        const url = new URL(page.url, window.location.origin);
        return url.searchParams.get('q') ?? '';
    } catch {
        return '';
    }
}

const searchQuery = ref(currentQuery());
const suggestions = ref([]);
const suggestOpen = ref(false);
const suggestLoading = ref(false);
const activeSuggestIndex = ref(-1);
const searchFilter = ref('all');
let suggestTimer = null;
let suggestRequestId = 0;

const filterOptions = [
    { command: 'all', label: 'All' },
    { command: 'products', label: 'Products' },
    { command: 'lots', label: 'Lots' },
    { command: 'subscribers', label: 'Subscribers' },
];
const searchFilterLabel = computed(() => filterOptions.find((o) => o.command === searchFilter.value)?.label ?? 'All');

function handleSearchFilter(command) {
    searchFilter.value = command;
}

function closeSuggestions() {
    suggestOpen.value = false;
    activeSuggestIndex.value = -1;
}

function fetchSuggestions(q) {
    const requestId = ++suggestRequestId;
    suggestLoading.value = true;

    window.axios.get(route('search.suggest'), { params: { q } })
        .then(({ data }) => {
            if (requestId !== suggestRequestId) return;
            suggestions.value = data.results ?? [];
            suggestOpen.value = true;
        })
        .catch(() => {
            if (requestId !== suggestRequestId) return;
            suggestions.value = [];
        })
        .finally(() => {
            if (requestId === suggestRequestId) suggestLoading.value = false;
        });
}

watch(searchQuery, (value) => {
    clearTimeout(suggestTimer);
    activeSuggestIndex.value = -1;
    const q = value.trim();
    if (!q) {
        suggestions.value = [];
        suggestOpen.value = false;
        return;
    }
    suggestTimer = setTimeout(() => fetchSuggestions(q), 200);
});

// Reflect the active search word whenever the URL changes (e.g. landing
// on the search page, or applying a filter that re-navigates).
watch(() => page.url, () => {
    searchQuery.value = currentQuery();
});

function goToSuggestion(item) {
    closeSuggestions();
    searchQuery.value = '';
    router.visit(route('market.show', item.id));
}

function submitSearch() {
    closeSuggestions();
    const q = searchQuery.value.trim();
    router.visit(route('search.index'), q ? { data: { q }, method: 'get' } : undefined);
}

function onSearchFocus() {
    if (searchQuery.value.trim() && suggestions.value.length) {
        suggestOpen.value = true;
    }
}

function onSearchBlur() {
    setTimeout(closeSuggestions, 120);
}

function onSearchKeydown(e) {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!suggestions.value.length) return;
        activeSuggestIndex.value = (activeSuggestIndex.value + 1) % suggestions.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!suggestions.value.length) return;
        activeSuggestIndex.value = (activeSuggestIndex.value - 1 + suggestions.value.length) % suggestions.value.length;
    } else if (e.key === 'Enter') {
        if (activeSuggestIndex.value >= 0 && suggestions.value[activeSuggestIndex.value]) {
            goToSuggestion(suggestions.value[activeSuggestIndex.value]);
        } else {
            submitSearch();
        }
    } else if (e.key === 'Escape') {
        closeSuggestions();
    }
}
</script>

<template>
    <div class="dp-header__search">
        <div class="dp-searchbar">
            <el-dropdown trigger="click" @command="handleSearchFilter" class="dp-search__filter">
                <button type="button" class="dp-search__filter-btn">
                    <span>{{ searchFilterLabel }}</span>
                    <el-icon :size="14"><ArrowDown /></el-icon>
                </button>
                <template #dropdown>
                    <el-dropdown-menu>
                        <el-dropdown-item command="all">All results</el-dropdown-item>
                        <el-dropdown-item command="products">Products</el-dropdown-item>
                        <el-dropdown-item command="lots">Lots</el-dropdown-item>
                        <el-dropdown-item command="subscribers">Subscribers</el-dropdown-item>
                    </el-dropdown-menu>
                </template>
            </el-dropdown>

            <div class="dp-search">
                <el-input
                    v-model="searchQuery"
                    placeholder="Search subscribers, products..."
                    :prefix-icon="Search"
                    class="dp-search-input"
                    @focus="onSearchFocus"
                    @blur="onSearchBlur"
                    @keydown="onSearchKeydown"
                />

                <transition name="dp-search-fade">
                    <div v-if="suggestOpen" class="dp-search__panel">
                        <div v-if="suggestLoading" class="dp-search__loading">Searching…</div>
                        <template v-else>
                            <button
                                v-for="(item, index) in suggestions"
                                :key="item.id"
                                type="button"
                                class="dp-search__item"
                                :class="{ 'dp-search__item--active': index === activeSuggestIndex }"
                                @mousedown.prevent="goToSuggestion(item)"
                                @mouseenter="activeSuggestIndex = index"
                            >
                                <span class="dp-search__item-icon"><el-icon :size="15"><Box /></el-icon></span>
                                <span class="dp-search__item-body">
                                    <span class="dp-search__item-name">{{ item.name }}</span>
                                    <span class="dp-search__item-meta">{{ [item.origin, item.type, item.lot_code].filter(Boolean).join(' · ') }}</span>
                                </span>
                                <span class="dp-search__item-price">${{ Number(item.price_per_kg).toFixed(2) }}<small>/kg</small></span>
                            </button>

                            <div v-if="!suggestions.length" class="dp-search__empty">No market items match "{{ searchQuery }}"</div>

                            <button type="button" class="dp-search__footer" @mousedown.prevent="submitSearch">
                                See all results for "{{ searchQuery }}"
                            </button>
                        </template>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<style>
.dp-header__search { flex: 1; display: flex; justify-content: center; min-width: 0; }
.dp-searchbar {
    display: flex;
    align-items: stretch;
    max-width: 620px;
    width: 100%;
    background: var(--dp-surface-container-low);
    border: 1px solid var(--dp-border-subtle);
    border-radius: 10px;
}
.dp-search { position: relative; flex: 1; min-width: 0; }
.dp-search__filter { flex-shrink: 0; display: flex; }
.dp-search__filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 44px;
    padding: 0 14px;
    border: none;
    border-right: 1px solid var(--dp-border-subtle);
    border-radius: 9px 0 0 9px;
    background: transparent;
    color: var(--dp-on-surface-variant);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background .15s ease, color .15s ease;
}
.dp-search__filter-btn:hover { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.dp-search__filter-btn:focus,
.dp-search__filter-btn:focus-visible {
    outline: none;
    box-shadow: none;
}
.dp-search-input { width: 100%; }
.dp-search-input .el-input__wrapper {
    background: transparent;
    border-radius: 0;
    box-shadow: none !important;
    height: 44px;
    padding-left: 14px;
}
.dp-search-input :deep(.el-input__inner) { font-size: 14px; }

/* ── Search suggestions dropdown — functionally identical to
   AppLayout.vue's global search (same /search/suggest endpoint, same
   keyboard nav and submit behavior), skinned with dp-* tokens. ─────── */
.dp-search__panel {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: var(--dp-surface-container-lowest);
    border-radius: 6px;
    box-shadow: 0 16px 40px rgba(39, 19, 16, 0.16);
    border: 1px solid var(--dp-border-subtle);
    max-height: 400px;
    overflow-y: auto;
    z-index: 40;
    padding: 6px;
}
.dp-search__loading,
.dp-search__empty {
    padding: 16px 12px;
    text-align: center;
    font-size: 13px;
    color: var(--dp-on-surface-variant);
}
.dp-search__item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 8px 10px;
    border: none;
    background: none;
    border-radius: 9px;
    cursor: pointer;
    text-align: left;
    transition: background 0.12s ease;
}
.dp-search__item:hover,
.dp-search__item--active {
    background: var(--dp-surface-container-high);
}
.dp-search__item-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border-radius: 8px;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
}
.dp-search__item-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.dp-search__item-name { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-search__item-meta { font-size: 11.5px; color: var(--dp-on-surface-variant); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-search__item-price { flex-shrink: 0; font-family: var(--dp-font-mono); font-size: 13px; font-weight: 700; color: var(--dp-primary); }
.dp-search__item-price small { font-family: var(--dp-font-sans); font-size: 10px; font-weight: 600; color: var(--dp-on-surface-variant); margin-left: 2px; }
.dp-search__footer {
    display: block;
    width: 100%;
    padding: 10px;
    margin-top: 4px;
    border: none;
    border-top: 1px solid var(--dp-border-subtle);
    background: none;
    border-radius: 0 0 9px 9px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--dp-primary);
    cursor: pointer;
    text-align: center;
    transition: background 0.12s ease;
}
.dp-search__footer:hover { background: var(--dp-surface-container-low); }

.dp-search-fade-enter-active,
.dp-search-fade-leave-active { transition: opacity 0.12s ease, transform 0.12s ease; }
.dp-search-fade-enter-from,
.dp-search-fade-leave-to { opacity: 0; transform: translateY(-4px); }

@media (max-width: 767.98px) {
    .dp-header__search { justify-content: flex-start; }
    .dp-search-input .el-input__wrapper { height: 40px; padding-left: 12px; }
    .dp-search__filter-btn { height: 40px; padding: 0 12px; }
}
@media (max-width: 479.98px) {
    .dp-header__search { display: none; }
}
</style>