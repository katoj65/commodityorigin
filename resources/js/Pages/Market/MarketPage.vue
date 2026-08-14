<script setup>
import { computed, nextTick, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Search, List, ShoppingCart, Sell, DataAnalysis, TrendCharts,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    searchQuery: { type: String, default: '' },
});

const emit = defineEmits(['update:searchQuery']);

const search = computed({
    get: () => props.searchQuery,
    set: (value) => emit('update:searchQuery', value),
});

/* ══════════════════════════════════════════════════════════════════════
   Top bar — section tabs + a single search icon that expands on click
   ══════════════════════════════════════════════════════════════════════ */
const marketTabs = [
    { label: 'Listings', icon: List, name: 'market.index' },
    { label: 'Requests', icon: ShoppingCart, name: 'market.request' },
    { label: 'Offers', icon: Sell, name: 'market.offer' },
    { label: 'Analysis', icon: DataAnalysis, name: 'market.analysis' },
    { label: 'Compare', icon: TrendCharts, name: 'market.compare' },
];

const searchOpen = ref(false);
const searchInputRef = ref(null);

function toggleSearch() {
    searchOpen.value = !searchOpen.value;

    if (searchOpen.value) {
        nextTick(() => searchInputRef.value?.focus());
    }
}

function closeSearch() {
    searchOpen.value = false;
}
</script>

<template>
    <AppLayout title="Coffee Market" full-width flush :show-banner="false">
        <Head title="Coffee Market" />

        <div class="mkt-page">

            <!-- ══════════════════════════════════════════════════════════
                 Sticky top bar — section tabs + a search icon, expands on click
                 ══════════════════════════════════════════════════════════ -->
            <div class="mkt-topbar">
                <div class="mkt-topbar__inner">
                    <nav class="mkt-tabs">
                        <Link
                            v-for="tab in marketTabs"
                            :key="tab.name"
                            :href="route(tab.name)"
                            class="mkt-tab"
                            :class="{ 'mkt-tab--active': route().current(tab.name) }"
                        >
                            <el-icon><component :is="tab.icon" /></el-icon> {{ tab.label }}
                        </Link>
                    </nav>

                    <div class="mkt-search-wrap" :class="{ 'mkt-search-wrap--open': searchOpen }">
                        <input
                            v-if="searchOpen"
                            ref="searchInputRef"
                            v-model="search"
                            type="text"
                            class="mkt-search-input"
                            placeholder="Search…"
                            @keyup.esc="closeSearch"
                        >
                        <button
                            type="button"
                            class="mkt-search-toggle"
                            :title="searchOpen ? 'Close search' : 'Search'"
                            @click="toggleSearch"
                        >
                            <el-icon><Search /></el-icon>
                        </button>
                    </div>
                </div>
            </div>

            <slot />

        </div>
    </AppLayout>
</template>

<style scoped>
.mkt-page {
    --green: #004532;
    --green-dark: #002e20;
    --gold: #c8862a;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Top bar ──────────────────────────────────────────────────────────── */
.mkt-topbar { position: sticky; top: 3.5rem; z-index: 20; background: #fff; border-bottom: 1px solid var(--border); }
.mkt-topbar__inner { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .5rem 1.5rem; }

/* ── Section tabs ─────────────────────────────────────────────────────── */
.mkt-tabs { display: flex; align-items: center; gap: 2px; overflow-x: auto; scrollbar-width: none; flex: 1 1 auto; min-width: 0; }
.mkt-tabs::-webkit-scrollbar { display: none; }

.mkt-tab {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    padding: 8px 14px; border-radius: 8px;
    font-size: .8125rem; font-weight: 600; color: var(--on-surface-var);
    text-decoration: none; white-space: nowrap;
    transition: background .15s ease, color .15s ease;
}
.mkt-tab :deep(.el-icon) { font-size: 14px; }
.mkt-tab:hover { background: var(--surface-low); color: var(--on-surface); }
.mkt-tab--active { background: rgba(0, 69, 50, .08); color: var(--green); }

.mkt-search-wrap { position: relative; display: flex; align-items: center; flex-shrink: 0; }

.mkt-search-toggle {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0;
    border: 1px solid var(--border); background: #fff; color: var(--on-surface-var);
    cursor: pointer; transition: all .15s ease;
}
.mkt-search-toggle:hover { border-color: var(--green); color: var(--green); background: #f0f5f3; }
.mkt-search-wrap--open .mkt-search-toggle { border-color: var(--green); color: var(--green); background: #f0f5f3; }

.mkt-search-input {
    width: 220px; height: 36px; margin-right: 8px; border: 1px solid var(--border); border-radius: 9px;
    padding: 0 12px; font-size: .8125rem; font-family: inherit; outline: none;
    background: var(--surface-low); color: var(--on-surface);
    transition: border-color .15s ease, background .15s ease;
}
.mkt-search-input:focus { border-color: var(--green); background: #fff; }

@media (max-width: 480px) {
    .mkt-search-input { width: 160px; }
}
</style>
