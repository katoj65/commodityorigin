<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend,
} from 'chart.js';
import { Search, Tickets, TrendCharts, Coin, Grid } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({
    producers: { type: Array, default: () => [] },
});

/* ── Selection ───────────────────────────────────────────────────────── */
const search = ref('');
const selected = ref(new Set(props.producers.slice(0, 6).map((p) => p.iso2)));

const filteredProducers = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.producers;
    return props.producers.filter((p) => p.name.toLowerCase().includes(term) || p.iso2.toLowerCase().includes(term) || (p.region ?? '').toLowerCase().includes(term));
});

function toggle(iso2) {
    const next = new Set(selected.value);
    if (next.has(iso2)) next.delete(iso2); else next.add(iso2);
    selected.value = next;
}

const selectedProducers = computed(() => props.producers
    .filter((p) => selected.value.has(p.iso2))
    .sort((a, b) => b.coffee_production_bags - a.coffee_production_bags));

/* ── Stats ───────────────────────────────────────────────────────────── */
const totalGlobalBags = computed(() => props.producers.reduce((sum, p) => sum + (p.coffee_production_bags ?? 0), 0));
const selectedTotalBags = computed(() => selectedProducers.value.reduce((sum, p) => sum + (p.coffee_production_bags ?? 0), 0));

const fmtBags = (n) => (n != null ? `${(n / 1_000_000).toLocaleString(undefined, { maximumFractionDigits: 1 })}M bags` : '—');
const marketShare = (bags) => (totalGlobalBags.value ? ((bags / totalGlobalBags.value) * 100).toFixed(1) : '0.0');

/* ── Chart ───────────────────────────────────────────────────────────── */
const chartData = computed(() => ({
    labels: selectedProducers.value.map((p) => `${p.flag_emoji} ${p.name}`),
    datasets: [{
        label: 'Annual Production (bags)',
        data: selectedProducers.value.map((p) => p.coffee_production_bags ?? 0),
        backgroundColor: '#004532',
        borderRadius: 6,
        maxBarThickness: 36,
    }],
}));

const chartOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => `${Number(ctx.raw).toLocaleString()} bags`,
            },
        },
    },
    scales: {
        x: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, callback: (v) => `${(v / 1_000_000).toFixed(0)}M` } },
        y: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
    },
};
</script>

<template>
    <AppLayout title="Compare Countries" full-width flush :show-banner="false">
        <Head title="Compare Countries" />

        <div class="cmp-page">
            <!-- ══════════════════════════════════════════════════════════
                 Header
                 ══════════════════════════════════════════════════════════ -->
            <div class="cmp-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="cmp-kicker"><el-icon><Grid /></el-icon> Origin Intelligence</div>
                            <h1 class="cmp-title mb-0">Compare Countries</h1>
                            <p class="cmp-subtitle mb-0">Compare annual coffee production across coffee-growing origins.</p>
                        </div>
                        <Link :href="route('market.index')" class="btn cmp-btn-outline btn-sm">
                            <el-icon><Tickets /></el-icon> Back to Market
                        </Link>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-4">

                <!-- ── Stats strip ─────────────────────────────────────── -->
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="cmp-kpi h-100">
                            <span class="cmp-kpi__label">Producing Countries</span>
                            <div class="cmp-kpi__value">{{ producers.length }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="cmp-kpi h-100">
                            <span class="cmp-kpi__label">Global Output</span>
                            <div class="cmp-kpi__value">{{ fmtBags(totalGlobalBags) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="cmp-kpi h-100">
                            <span class="cmp-kpi__label">Selected</span>
                            <div class="cmp-kpi__value">{{ selectedProducers.length }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="cmp-kpi h-100">
                            <span class="cmp-kpi__label">Selected Share</span>
                            <div class="cmp-kpi__value">{{ marketShare(selectedTotalBags) }}%</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">

                    <!-- ══════════════════════════════════════════════════
                         Left — country picker
                         ══════════════════════════════════════════════════ -->
                    <div class="col-12 col-lg-4">
                        <div class="cmp-card">
                            <div class="cmp-card-title mb-3"><el-icon class="cmp-card-icon"><Tickets /></el-icon> Select Countries</div>

                            <div class="cmp-search-wrap mb-3">
                                <el-icon class="cmp-search-icon"><Search /></el-icon>
                                <input v-model="search" class="cmp-search-input" placeholder="Search origin or region…">
                            </div>

                            <div class="cmp-picker-list">
                                <label v-for="p in filteredProducers" :key="p.iso2" class="cmp-picker-row">
                                    <input type="checkbox" :checked="selected.has(p.iso2)" @change="toggle(p.iso2)">
                                    <span class="cmp-picker-flag">{{ p.flag_emoji }}</span>
                                    <span class="cmp-picker-name">{{ p.name }}</span>
                                    <span class="cmp-picker-bags">{{ fmtBags(p.coffee_production_bags) }}</span>
                                </label>
                                <div v-if="!filteredProducers.length" class="cmp-empty">No matching countries.</div>
                            </div>
                        </div>
                    </div>

                    <!-- ══════════════════════════════════════════════════
                         Right — chart + comparison table
                         ══════════════════════════════════════════════════ -->
                    <div class="col-12 col-lg-8">
                        <div class="cmp-card mb-3">
                            <div class="cmp-card-title mb-3"><el-icon class="cmp-card-icon"><TrendCharts /></el-icon> Production Comparison</div>

                            <div v-if="!selectedProducers.length" class="cmp-empty py-5 text-center">Select at least one country to compare.</div>
                            <div v-else class="cmp-chart-wrap">
                                <Bar :data="chartData" :options="chartOptions" />
                            </div>
                        </div>

                        <div class="cmp-card">
                            <div class="cmp-card-title mb-3"><el-icon class="cmp-card-icon"><Coin /></el-icon> Ranking</div>

                            <div v-if="!selectedProducers.length" class="cmp-empty">No countries selected.</div>
                            <div v-else class="table-responsive">
                                <table class="table cmp-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th style="min-width:180px;">Country</th>
                                            <th>Region</th>
                                            <th class="text-end">Annual Production</th>
                                            <th class="text-end">Global Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(p, i) in selectedProducers" :key="p.iso2" class="cmp-table-row">
                                            <td class="cmp-muted">{{ i + 1 }}</td>
                                            <td>
                                                <div class="cmp-tbl-country">
                                                    <span class="cmp-picker-flag">{{ p.flag_emoji }}</span>
                                                    <span class="cmp-item-name">{{ p.name }}</span>
                                                </div>
                                            </td>
                                            <td class="cmp-muted">{{ p.region }}</td>
                                            <td class="text-end"><strong>{{ fmtBags(p.coffee_production_bags) }}</strong></td>
                                            <td class="text-end">
                                                <span class="cmp-share-pill">{{ marketShare(p.coffee_production_bags) }}%</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.cmp-page {
    --green: #004532;
    --gold: #c8862a;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-low);
    color: var(--on-surface);
    min-height: 100%;
}
.cmp-muted { color: var(--on-surface-var); font-size: .8125rem; }
.cmp-item-name { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }

/* ── Header ──────────────────────────────────────────────────────────── */
.cmp-header { background: #fff; border-bottom: 1px solid var(--border); }
.cmp-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.cmp-title { font-size: 1.25rem; font-weight: 800; letter-spacing: -.02em; }
.cmp-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

.cmp-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.cmp-btn-outline:hover { background: var(--surface-low); }

/* ── KPI ─────────────────────────────────────────────────────────────── */
.cmp-kpi { border: 1px solid var(--border); border-radius: 10px; padding: .875rem; background: #fff; }
.cmp-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.cmp-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin-top: 4px; }

/* ── Cards ───────────────────────────────────────────────────────────── */
.cmp-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1.125rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.cmp-card-title { display: inline-flex; align-items: center; gap: 8px; font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.cmp-card-icon { width: 26px; height: 26px; border-radius: 7px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Picker ──────────────────────────────────────────────────────────── */
.cmp-search-wrap { position: relative; display: flex; align-items: center; }
.cmp-search-icon { position: absolute; left: 10px; font-size: 12px; color: var(--on-surface-var); pointer-events: none; }
.cmp-search-input { width: 100%; height: 34px; border: 1px solid var(--border); border-radius: 8px; padding: 0 10px 0 30px; font-size: .8125rem; outline: none; }
.cmp-search-input:focus { border-color: var(--green); }

.cmp-picker-list { max-height: 480px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px; }
.cmp-picker-row { display: flex; align-items: center; gap: 8px; padding: 7px 6px; border-radius: 8px; cursor: pointer; font-size: .8125rem; }
.cmp-picker-row:hover { background: var(--surface-low); }
.cmp-picker-row input { flex-shrink: 0; accent-color: var(--green); width: 15px; height: 15px; }
.cmp-picker-flag { font-size: 1.0625rem; line-height: 1; flex-shrink: 0; }
.cmp-picker-name { flex: 1; font-weight: 600; color: var(--on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cmp-picker-bags { font-size: .6875rem; color: var(--on-surface-var); flex-shrink: 0; white-space: nowrap; }

.cmp-empty { font-size: .8125rem; color: var(--on-surface-var); text-align: center; padding: .75rem 0; }

/* ── Chart ───────────────────────────────────────────────────────────── */
.cmp-chart-wrap { height: 340px; }

/* ── Table ───────────────────────────────────────────────────────────── */
.cmp-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.cmp-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.cmp-table-row:last-child td { border-bottom: none; }
.cmp-table-row:hover { background: var(--surface-low); }
.cmp-tbl-country { display: flex; align-items: center; gap: 8px; }

.cmp-share-pill { display: inline-flex; border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 2px 10px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
</style>
