<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Search, Filter, Refresh, Box } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    countries: { type: Array, default: () => [] },
    regions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

/* ── Filter state ────────────────────────────────────────────────────── */
const search = ref(props.filters.search ?? '');
const region = ref(props.filters.region ?? '');

function applyFilters() {
    router.get(route('country.index'), {
        search: search.value || undefined,
        region: region.value || undefined,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = '';
    region.value = '';
    applyFilters();
}

/* ── KPIs ────────────────────────────────────────────────────────────── */
const kpis = computed(() => {
    const byRegion = {};
    props.countries.forEach((c) => { byRegion[c.region] = (byRegion[c.region] ?? 0) + 1; });
    return [
        { label: 'Countries', value: props.countries.length, sub: 'Matching current filters' },
        ...props.regions.slice(0, 3).map((r) => ({ label: r, value: byRegion[r] ?? 0, sub: 'In this region' })),
    ];
});
</script>

<template>
    <AppLayout title="Countries" full-width flush :show-banner="false">
        <Head title="Countries" />

        <div class="cty-page">

            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="cty-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="py-3">
                        <div class="cty-kicker">Reference Data</div>
                        <h1 class="cty-title mb-0">Countries</h1>
                        <p class="cty-subtitle mb-0">Every country in the system — origin, currency, and dialing reference for coffee trade.</p>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── KPI strip ────────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-sm-3">
                        <div class="cty-kpi h-100">
                            <span class="cty-kpi__label">{{ kpi.label }}</span>
                            <div class="cty-kpi__value">{{ kpi.value }}</div>
                            <div class="cty-kpi__sub">{{ kpi.sub }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Filters ───────────────────────────────────────────── -->
                <div class="cty-filters mb-3">
                    <div class="cty-search-wrap">
                        <el-icon class="cty-search-icon"><Search /></el-icon>
                        <input v-model="search" class="cty-search-input" placeholder="Search name, ISO2, ISO3…" @keydown.enter="applyFilters">
                    </div>
                    <select v-model="region" class="cty-select">
                        <option value="">All Regions</option>
                        <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
                    </select>
                    <div class="d-flex gap-2 ms-auto">
                        <button class="btn cty-btn-primary btn-sm" @click="applyFilters">
                            <el-icon><Filter /></el-icon> Apply
                        </button>
                        <button class="btn cty-btn-ghost btn-sm" @click="resetFilters">
                            <el-icon><Refresh /></el-icon> Reset
                        </button>
                    </div>
                </div>

                <!-- ── Table ─────────────────────────────────────────────── -->
                <div class="cty-section">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="background:#f8fafc;border-radius:8px 8px 0 0;">
                        <span class="cty-count-label"><span class="cty-count-num">{{ countries.length }}</span> countries</span>
                    </div>

                    <div v-if="!countries.length" class="text-center py-5">
                        <el-icon style="font-size:2rem;color:#d1d5db;"><Box /></el-icon>
                        <p class="cty-muted mt-2 mb-0" style="font-size:.875rem;">No countries match your filters.</p>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table cty-table mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width:200px;">Country</th>
                                    <th>ISO2</th>
                                    <th>ISO3</th>
                                    <th>Region</th>
                                    <th>Subregion</th>
                                    <th>Currency</th>
                                    <th>Dial Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in countries" :key="c.id" class="cty-table-row">
                                    <td>
                                        <div class="cty-tbl-country">
                                            <span class="cty-flag">{{ c.flag_emoji }}</span>
                                            <span class="cty-item-name">{{ c.name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="cty-code-pill">{{ c.iso2 }}</span></td>
                                    <td><span class="cty-muted">{{ c.iso3 }}</span></td>
                                    <td><span class="cty-muted">{{ c.region }}</span></td>
                                    <td><span class="cty-muted">{{ c.subregion ?? '—' }}</span></td>
                                    <td>
                                        <span v-if="c.currency_code" style="font-size:.8125rem;font-weight:700;color:#111827;">{{ c.currency_code }}</span>
                                        <span v-if="c.currency_name" class="cty-tbl-country__sub d-block">{{ c.currency_name }}</span>
                                    </td>
                                    <td><span class="cty-muted" style="white-space:nowrap;">{{ c.phone_code ?? '—' }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.cty-page {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}
.cty-muted { color: var(--on-surface-var); font-size: .8125rem; }
.cty-item-name { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }

/* ── Header ──────────────────────────────────────────────────────────── */
.cty-header { background: #fff; border-bottom: 1px solid var(--border); }
.cty-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.cty-title { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.cty-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

/* ── KPI ─────────────────────────────────────────────────────────────── */
.cty-kpi { border: 1px solid var(--border); border-radius: 8px; padding: .875rem; background: #fff; }
.cty-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.cty-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.cty-kpi__sub { font-size: .625rem; color: var(--on-surface-var); }

/* ── Filters ─────────────────────────────────────────────────────────── */
.cty-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.cty-search-wrap { position: relative; display: flex; align-items: center; }
.cty-search-icon { position: absolute; left: 8px; font-size: 12px; color: var(--on-surface-var); pointer-events: none; }
.cty-search-input { height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 0 10px 0 28px; font-size: .8125rem; outline: none; width: 220px; color: var(--on-surface); }
.cty-search-input:focus { border-color: var(--green); }
.cty-select { height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 0 10px; font-size: .8125rem; color: var(--on-surface); background: #fff; outline: none; cursor: pointer; }
.cty-select:focus { border-color: var(--green); }

.cty-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.cty-btn-primary:hover { background: #065f46; color: #fff; }
.cty-btn-ghost { background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }

/* ── Section / table ─────────────────────────────────────────────────── */
.cty-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
.cty-count-label { font-size: .8125rem; color: var(--on-surface-var); }
.cty-count-num { font-weight: 700; color: var(--on-surface); }

.cty-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.cty-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.cty-table-row:last-child td { border-bottom: none; }
.cty-table-row:hover { background: var(--surface-low); }

.cty-tbl-country { display: flex; align-items: center; gap: 10px; }
.cty-flag { font-size: 1.25rem; line-height: 1; }
.cty-tbl-country__sub { font-size: .6875rem; color: var(--on-surface-var); margin-top: 1px; }

.cty-code-pill { display: inline-flex; border-radius: 4px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; letter-spacing: .04em; }

@media (max-width: 767.98px) {
    .cty-search-input { width: 160px; }
    .cty-filters { gap: 6px; }
}
</style>
