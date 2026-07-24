<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend,
} from 'chart.js';
import {
    Search, Tickets, TrendCharts, Coin, Grid, DataAnalysis,
} from '@element-plus/icons-vue';
import MarketPage from './MarketPage.vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({
    producers: { type: Array, default: () => [] },
});

const searchQuery = ref('');

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
            backgroundColor: '#111827', padding: 10, cornerRadius: 8, titleFont: { size: 12 }, bodyFont: { size: 12 },
            callbacks: { label: (ctx) => `${Number(ctx.raw).toLocaleString()} bags` },
        },
    },
    scales: {
        x: { grid: { color: '#f1f5f4' }, ticks: { font: { size: 11 }, color: '#6b7280', callback: (v) => `${(v / 1_000_000).toFixed(0)}M` } },
        y: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: '#111827' } },
    },
};
</script>

<template>
    <MarketPage v-model:search-query="searchQuery">
        <div class="mkt-body">
            <div class="mkt-section__head">
                <div>
                    <div class="mkt-kicker"><el-icon><Grid /></el-icon> Origin Intelligence</div>
                    <h2 class="mkt-title">Compare Countries</h2>
                </div>
                <Link :href="route('market.analysis')" class="mkt-btn-group__item">
                    <el-icon><DataAnalysis /></el-icon> Back to Market Analysis
                </Link>
            </div>

            <div class="man-content">
                <!-- ── Stats strip ─────────────────────────────────────── -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="man-stat h-100">
                            <div class="man-stat__icon"><el-icon><Grid /></el-icon></div>
                            <div class="man-stat__value">{{ producers.length }}</div>
                            <div class="man-stat__label">Producing Countries</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="man-stat h-100">
                            <div class="man-stat__icon"><el-icon><Coin /></el-icon></div>
                            <div class="man-stat__value">{{ fmtBags(totalGlobalBags) }}</div>
                            <div class="man-stat__label">Global Output</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="man-stat h-100">
                            <div class="man-stat__icon"><el-icon><Tickets /></el-icon></div>
                            <div class="man-stat__value">{{ selectedProducers.length }}</div>
                            <div class="man-stat__label">Selected</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="man-stat h-100">
                            <div class="man-stat__icon"><el-icon><TrendCharts /></el-icon></div>
                            <div class="man-stat__value">{{ marketShare(selectedTotalBags) }}%</div>
                            <div class="man-stat__label">Selected Share</div>
                        </div>
                    </div>
                </div>

                <section class="man-section">
                    <div class="man-section__head">
                        <div class="man-section__icon"><el-icon><TrendCharts /></el-icon></div>
                        <div>
                            <h2 class="man-section__title">Production Comparison</h2>
                            <p class="man-section__desc">Compare annual coffee production across coffee-growing origins.</p>
                        </div>
                    </div>

                    <div class="man-analytics-grid">
                        <!-- ── Picker ───────────────────────────────────── -->
                        <div class="man-panel man-aside-card man-picker-card">
                            <div class="man-card-title mb-3">Select Countries</div>
                            <div class="cmp-search-wrap mb-2">
                                <el-icon class="cmp-search-icon"><Search /></el-icon>
                                <input v-model="search" class="cmp-search-input" placeholder="Search origin or region…">
                            </div>
                            <div class="cmp-picker-list">
                                <label v-for="p in filteredProducers" :key="p.iso2" class="cmp-picker-row">
                                    <input type="checkbox" :checked="selected.has(p.iso2)" @change="toggle(p.iso2)">
                                    <span class="cmp-picker-flag">{{ p.flag_emoji }}</span>
                                    <span class="cmp-picker-name">{{ p.name }}</span>
                                    <span class="man-muted">{{ fmtBags(p.coffee_production_bags) }}</span>
                                </label>
                                <div v-if="!filteredProducers.length" class="man-muted text-center py-3">No matching countries.</div>
                            </div>
                        </div>

                        <!-- ── Chart ────────────────────────────────────── -->
                        <div class="man-panel">
                            <div v-if="!selectedProducers.length" class="man-muted text-center py-5">Select at least one country to compare.</div>
                            <div v-else class="man-chart-wrap">
                                <Bar :data="chartData" :options="chartOptions" />
                            </div>
                        </div>
                    </div>
                </section>

                <section class="man-section">
                    <div class="man-section__head">
                        <div class="man-section__icon"><el-icon><Coin /></el-icon></div>
                        <div>
                            <h2 class="man-section__title">Ranking</h2>
                            <p class="man-section__desc">Selected origins ranked by annual production and global share.</p>
                        </div>
                    </div>

                    <div v-if="!selectedProducers.length" class="man-panel man-muted">No countries selected.</div>
                    <div v-else class="man-panel man-table-card">
                        <table class="man-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Country</th>
                                    <th>Region</th>
                                    <th>Annual Production</th>
                                    <th>Global Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(p, i) in selectedProducers" :key="p.iso2">
                                    <td class="man-muted">{{ i + 1 }}</td>
                                    <td>
                                        <span class="cmp-picker-flag">{{ p.flag_emoji }}</span>
                                        <span class="man-breakdown-row__label">{{ p.name }}</span>
                                    </td>
                                    <td class="man-muted">{{ p.region }}</td>
                                    <td>{{ fmtBags(p.coffee_production_bags) }}</td>
                                    <td>
                                        <span class="man-tone man-tone--green">{{ marketShare(p.coffee_production_bags) }}%</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </MarketPage>
</template>

<style scoped>
.man-muted { color: var(--on-surface-var); font-size: .8125rem; }

/* ── Body ─────────────────────────────────────────────────────────────── */
.mkt-body { padding: 1.25rem 0 3rem; }
.mkt-section__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; padding: 0 1.5rem; }
.mkt-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.mkt-title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }

.mkt-btn-group__item { display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; font-size: .75rem; font-weight: 700; letter-spacing: .01em; text-decoration: none; color: var(--green); background: #fff; border: 1px solid var(--green); border-radius: 8px; cursor: pointer; white-space: nowrap; transition: background .15s ease; }
.mkt-btn-group__item:hover { background: #f0f5f3; }

.man-content { padding: 0 1.5rem; }

/* ── Sections ────────────────────────────────────────────────────────── */
.man-section { padding-top: 1.75rem; margin-top: 1.75rem; border-top: 1px solid var(--border); }
.man-section:first-child { padding-top: 0; margin-top: 0; border-top: none; }
.man-section__head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem; }
.man-section__icon { width: 34px; height: 34px; border-radius: 10px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; box-shadow: 0 0 0 3px rgba(0,69,50,.05); }
.man-section__title { font-size: 1rem; font-weight: 800; letter-spacing: -.01em; margin: 0 0 2px; color: var(--on-surface); }
.man-section__desc { font-size: .8125rem; color: var(--on-surface-var); margin: 0; max-width: 640px; }

/* ── Stat cards ──────────────────────────────────────────────────────── */
.man-stat { border: 1px solid var(--border); border-radius: 10px; padding: 1rem; text-align: center; }
.man-stat__icon { width: 34px; height: 34px; border-radius: 9px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 16px; margin: 0 auto 10px; }
.man-stat__value { font-family: 'IBM Plex Mono', monospace; font-size: 1.375rem; font-weight: 700; color: var(--on-surface); line-height: 1.1; }
.man-stat__label { font-size: .75rem; color: var(--on-surface-var); font-weight: 600; margin-top: 4px; }

/* ── Panels / cards ──────────────────────────────────────────────────── */
.man-panel { padding: 0; }
.man-card-title { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.man-breakdown-row__label { font-size: .875rem; font-weight: 700; color: var(--on-surface); margin-left: 8px; }

/* ── Analytics grid ──────────────────────────────────────────────────── */
.man-analytics-grid { display: grid; grid-template-columns: 1fr 1.4fr; gap: 1.5rem; align-items: stretch; }
.man-chart-wrap { position: relative; height: 340px; }
.man-aside-card { display: flex; flex-direction: column; border-left: 1px solid var(--border); padding-left: 1.25rem; }

/* ── Country picker ──────────────────────────────────────────────────── */
.man-picker-card { max-height: 400px; }
.cmp-search-wrap { position: relative; display: flex; align-items: center; }
.cmp-search-icon { position: absolute; left: 10px; font-size: 12px; color: var(--on-surface-var); pointer-events: none; }
.cmp-search-input { width: 100%; height: 34px; border: 1px solid var(--border); border-radius: 8px; padding: 0 10px 0 30px; font-size: .8125rem; outline: none; background: var(--surface-low); }
.cmp-search-input:focus { border-color: var(--green); background: #fff; }

.cmp-picker-list { max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px; }
.cmp-picker-row { display: flex; align-items: center; gap: 8px; padding: 7px 6px; border-radius: 8px; cursor: pointer; font-size: .8125rem; }
.cmp-picker-row:hover { background: var(--surface-low); }
.cmp-picker-row input { flex-shrink: 0; accent-color: var(--green); width: 15px; height: 15px; }
.cmp-picker-flag { font-size: 1.0625rem; line-height: 1; flex-shrink: 0; }
.cmp-picker-name { flex: 1; font-weight: 600; color: var(--on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Tones ───────────────────────────────────────────────────────────── */
.man-tone { display: inline-flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 600; padding: 3px 10px 3px 8px; border-radius: 999px; line-height: 1.5; white-space: nowrap; }
.man-tone::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.man-tone--green { background: #ecfdf5; color: #059669; }

/* ── Table ───────────────────────────────────────────────────────────── */
.man-table-card { overflow: hidden; }
.man-table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
.man-table thead th { text-align: left; font-size: .6875rem; font-weight: 600; letter-spacing: .04em; color: var(--on-surface-var); background: var(--surface-low); padding: 10px 14px; }
.man-table tbody td { padding: 9px 14px; border-top: 1px solid var(--surface-low); color: var(--on-surface); font-variant-numeric: tabular-nums; }
.man-table tbody tr:hover td { background: var(--surface-low); }

@media (max-width: 991.98px) {
    .man-analytics-grid { grid-template-columns: 1fr; }
    .man-aside-card { border-left: none; padding-left: 0; border-top: 1px solid var(--border); padding-top: 1.25rem; }
    .man-picker-card { max-height: none; }
}

@media (max-width: 767.98px) {
    .mkt-section__head { padding: 0 1.25rem; }
    .man-content { padding: 0 1.25rem; }
}
</style>
