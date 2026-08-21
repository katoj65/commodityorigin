<script setup>
import { computed, nextTick, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Search, List, ShoppingCart, Sell, DataAnalysis, TrendCharts, Trophy, Filter,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import MarketFilterDialog from '@/Components/Market/MarketFilterDialog.vue';

const props = defineProps({
    searchQuery: { type: String, default: '' },
});

const emit = defineEmits(['update:searchQuery']);

const search = computed({
    get: () => props.searchQuery,
    set: (value) => emit('update:searchQuery', value),
});

const filterDialogOpen = ref(false);

/* ══════════════════════════════════════════════════════════════════════
   Top bar — section tabs + a single search icon that expands on click
   ══════════════════════════════════════════════════════════════════════ */
const marketTabs = [
    { label: 'Listings', icon: List, name: 'market.index' },
    { label: 'Requests', icon: ShoppingCart, name: 'market.request' },
    { label: 'Offers', icon: Sell, name: 'market.offer' },
    { label: 'Auctions', icon: Trophy, name: 'auction.index' },
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
    <DesignPreviewLayout title="Coffee Market">
        <Head title="Coffee Market">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
        </Head>

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

                    <button
                        type="button"
                        class="mkt-filter-toggle"
                        title="Filter coffee"
                        @click="filterDialogOpen = true"
                    >
                        <el-icon><Filter /></el-icon> <span>Filter</span>
                    </button>
                </div>
            </div>

            <slot />

        </div>

        <MarketFilterDialog v-model="filterDialogOpen" />
    </DesignPreviewLayout>
</template>

<style scoped>
.mkt-page {
    /* Bean Origin design system — literal hex from the Stitch export, not
       tailwind.config.js: this app's shared config already owns an old
       dark-theme palette under similarly-named tokens (see
       feedback_stitch_mockup_porting memory). Variable NAMES kept as
       --green/--border/etc. on purpose — every .mktl-* rule in
       MarketListings.vue already references them, and CSS custom
       properties cascade across scoped SFC boundaries, so re-pointing
       the values here recolors the whole page without touching every
       rule individually. */
    --green: #271310;          /* primary — Deep Roast */
    --green-dark: #1a0d0b;
    --border: #d3c3c0;         /* outline-variant */
    --on-surface: #1a1c1c;
    --on-surface-var: #504442; /* on-surface-variant */
    --surface-low: #f3f3f3;    /* surface-container-low */
    font-family: 'Inter', system-ui, sans-serif;
    background: #f9f9f9;
    color: var(--on-surface);
    min-height: 100%;
    /* DesignPreviewLayout's .dp-main carries its own 48px top padding
       (shared by every page it wraps) — pulled back up here so the
       sticky topbar sits flush under the header instead of leaving a
       dead gap, same fix already applied to GeneralDashboard's .cp-page. */
    margin-top: -48px;
}

/* ── Top bar ──────────────────────────────────────────────────────────── */
.mkt-topbar { position: sticky; top: 80px; z-index: 20; background: #fff; border-bottom: 1px solid var(--border); }
.mkt-topbar__inner { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .5rem 0; }

/* ── Section tabs ─────────────────────────────────────────────────────── */
.mkt-tabs { display: flex; align-items: center; gap: 2px; overflow-x: auto; scrollbar-width: none; flex: 1 1 auto; min-width: 0; }
.mkt-tabs::-webkit-scrollbar { display: none; }

.mkt-tab {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    padding: 10px 16px; border-radius: 999px;
    font-size: .875rem; font-weight: 600; color: var(--on-surface-var);
    text-decoration: none; white-space: nowrap;
    transition: background .15s ease, color .15s ease;
}
.mkt-tab :deep(.el-icon) { font-size: 14px; }
.mkt-tab:hover { background: var(--surface-low); color: var(--on-surface); }
.mkt-tab--active { background: rgba(39, 19, 16, .08); color: var(--green); font-weight: 700; }

.mkt-search-wrap { position: relative; display: flex; align-items: center; flex-shrink: 0; }

.mkt-search-toggle {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    border: 1px solid var(--border); background: #fff; color: var(--on-surface-var);
    cursor: pointer; transition: all .15s ease;
}
.mkt-search-toggle:hover { border-color: var(--green); color: var(--green); background: var(--surface-low); }
.mkt-search-wrap--open .mkt-search-toggle { border-color: var(--green); color: var(--green); background: var(--surface-low); }

.mkt-search-input {
    width: 220px; height: 36px; margin-right: 8px; border: 1px solid var(--border); border-radius: 10px;
    padding: 0 12px; font-size: .875rem; font-family: inherit; outline: none;
    background: var(--surface-low); color: var(--on-surface);
    transition: border-color .15s ease, background .15s ease;
}
.mkt-search-input:focus { border-color: var(--green); background: #fff; }

@media (max-width: 480px) {
    .mkt-search-input { width: 160px; }
}

/* ── Filter toggle button — the mockup's "All Filters" primary pill ────── */
.mkt-filter-toggle {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    height: 36px; padding: 0 16px; border-radius: 999px;
    border: none; background: var(--green); color: #fff;
    font-size: .875rem; font-weight: 600;
    cursor: pointer; transition: all .15s ease;
}
.mkt-filter-toggle :deep(.el-icon) { font-size: 14px; }
.mkt-filter-toggle:hover { background: var(--green-dark); }

@media (max-width: 640px) {
    .mkt-filter-toggle span { display: none; }
}
</style>
