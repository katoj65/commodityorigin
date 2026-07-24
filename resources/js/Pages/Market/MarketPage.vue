<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Search, MagicStick, TrendCharts, DataAnalysis,
    Filter, Download, Grid, UserFilled,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    searchQuery: { type: String, default: '' },
});

const emit = defineEmits(['update:searchQuery']);

/* ══════════════════════════════════════════════════════════════════════
   Top bar — AI search + quick actions
   ══════════════════════════════════════════════════════════════════════ */
const search = computed({
    get: () => props.searchQuery,
    set: (value) => emit('update:searchQuery', value),
});

const searchPrompts = [
    'Find buyers in UAE',
    'Predict Arabica prices',
    'Compare Uganda and Brazil',
    'Best export market this month',
];

const quickActions = [
    { label: 'Find Buyers', icon: UserFilled, href: route('buy.index') },
    { label: 'Analyze Market', icon: DataAnalysis, href: route('market.analysis') },
    { label: 'Compare Countries', icon: Grid, href: route('market.compare') },
    { label: 'Export Report', icon: Download },
    { label: 'View Forecast', icon: TrendCharts, href: route('forecast.index') },
];

const filtersOpen = ref(true);
</script>

<template>
    <AppLayout title="Coffee Market" full-width flush :show-banner="false">
        <Head title="Coffee Market" />

        <div class="mkt-page">

            <!-- ══════════════════════════════════════════════════════════
                 Sticky top bar — AI search + quick actions
                 ══════════════════════════════════════════════════════════ -->
            <div class="mkt-topbar pt-2 pb-2">
                <div class="container-fluid px-3 px-lg-4 py-2">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
                        <div class="mkt-search-wrap flex-grow-1">
                            <el-icon class="mkt-search-icon"><Search /></el-icon>
                            <input v-model="search" class="mkt-search-input" placeholder="Ask Coffee Pulse AI…">
                            <el-icon class="mkt-search-ai"><MagicStick /></el-icon>
                        </div>
                        <div class="mkt-quick-actions">
                            <template v-for="qa in quickActions" :key="qa.label">
                                <Link v-if="qa.href" :href="qa.href" class="mkt-qa-btn">
                                    <el-icon><component :is="qa.icon" /></el-icon> {{ qa.label }}
                                </Link>
                                <button v-else type="button" class="mkt-qa-btn">
                                    <el-icon><component :is="qa.icon" /></el-icon> {{ qa.label }}
                                </button>
                            </template>
                        </div>
                        <button type="button" class="mkt-filter-toggle d-lg-none" @click="filtersOpen = !filtersOpen">
                            <el-icon><Filter /></el-icon>
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
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Top bar ──────────────────────────────────────────────────────────── */
.mkt-topbar { position: sticky; top: 3.5rem; z-index: 20; background: #fff; border-bottom: 1px solid var(--border); }
.mkt-search-wrap { position: relative; display: flex; align-items: center; max-width: 480px; }
.mkt-search-icon { position: absolute; left: 12px; color: var(--on-surface-var); font-size: 14px; }
.mkt-search-ai { position: absolute; right: 12px; color: var(--gold); font-size: 14px; }
.mkt-search-input { width: 100%; height: 38px; border: 1px solid var(--border); border-radius: 10px; padding: 0 36px; font-size: .8125rem; outline: none; background: var(--surface-low); }
.mkt-search-input:focus { border-color: var(--green); background: #fff; }
.mkt-search-prompts { display: flex; flex-wrap: wrap; gap: 6px; padding: 6px 0 8px; }
.mkt-prompt-chip { font-size: .6875rem; padding: 3px 10px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface-var); cursor: pointer; white-space: nowrap; }
.mkt-prompt-chip:hover { background: #eef2f1; }

.mkt-quick-actions { display: flex; flex-wrap: wrap; gap: 6px; }
.mkt-qa-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: 7px 12px; border-radius: 8px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; text-decoration: none; }
.mkt-qa-btn:hover { background: #eef2f1; border-color: var(--green); }

.mkt-filter-toggle { width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border); background: #fff; color: var(--on-surface-var); display: inline-flex; align-items: center; justify-content: center; }
</style>
