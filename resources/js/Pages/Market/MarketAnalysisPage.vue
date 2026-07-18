<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    DataAnalysis, Tickets, CircleCheck, Coin, Box, Sunny, InfoFilled,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    analysis: {
        type: Object,
        default: () => ({
            total_listings: 0,
            total_volume_kg: 0,
            average_price_per_kg: null,
            min_price_per_kg: null,
            max_price_per_kg: null,
            average_quality_score: null,
            types: [],
            origins: [],
            demand: [],
            insights: [],
        }),
    },
});

const hasData = computed(() => props.analysis.total_listings > 0);

const fmtNum = (n) => (n != null ? Number(n).toLocaleString(undefined, { maximumFractionDigits: 0 }) : '—');
const fmtPrice = (n) => (n != null ? Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');

const demandTone = (label) => ({ High: 'man-tone--green', Medium: 'man-tone--amber', Low: 'man-tone--red' }[label] ?? 'man-tone--muted');
</script>

<template>
    <AppLayout title="Market Analysis" full-width flush :show-banner="false">
        <Head title="Market Analysis" />

        <div class="man-page">
            <!-- ══════════════════════════════════════════════════════════
                 Header
                 ══════════════════════════════════════════════════════════ -->
            <div class="man-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="man-kicker"><el-icon><DataAnalysis /></el-icon> Market Analysis</div>
                            <h1 class="man-title mb-0">What's happening in the market</h1>
                            <p class="man-subtitle mb-0">A simple summary of live coffee listings — no jargon, just the numbers that matter.</p>
                        </div>
                        <Link :href="route('market.index')" class="btn man-btn-outline btn-sm">
                            <el-icon><Tickets /></el-icon> Back to Market
                        </Link>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-4">

                <!-- ── Empty state ─────────────────────────────────────── -->
                <div v-if="!hasData" class="man-card man-empty-card">
                    <el-icon style="font-size:2.25rem;color:#d1d5db;"><Box /></el-icon>
                    <p class="man-muted mt-2 mb-0">{{ analysis.insights[0] }}</p>
                </div>

                <template v-else>
                    <!-- ── At a glance ──────────────────────────────────── -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="man-stat h-100">
                                <div class="man-stat__icon"><el-icon><Tickets /></el-icon></div>
                                <div class="man-stat__value">{{ fmtNum(analysis.total_listings) }}</div>
                                <div class="man-stat__label">Live Listings</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="man-stat h-100">
                                <div class="man-stat__icon"><el-icon><Box /></el-icon></div>
                                <div class="man-stat__value">{{ fmtNum(analysis.total_volume_kg) }} kg</div>
                                <div class="man-stat__label">Total Volume</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="man-stat h-100">
                                <div class="man-stat__icon"><el-icon><Coin /></el-icon></div>
                                <div class="man-stat__value">{{ fmtPrice(analysis.average_price_per_kg) }}</div>
                                <div class="man-stat__label">Average Price / kg</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="man-stat h-100">
                                <div class="man-stat__icon"><el-icon><Sunny /></el-icon></div>
                                <div class="man-stat__value">{{ analysis.average_quality_score ?? '—' }}</div>
                                <div class="man-stat__label">Average Quality</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">

                        <!-- ── Key takeaways ────────────────────────────── -->
                        <div class="col-12 col-lg-5">
                            <div class="man-card">
                                <div class="man-card-title mb-3"><el-icon class="man-card-icon"><InfoFilled /></el-icon> Key Takeaways</div>
                                <ul class="man-insight-list">
                                    <li v-for="(insight, i) in analysis.insights" :key="i">
                                        <el-icon class="man-insight-check"><CircleCheck /></el-icon>
                                        <span>{{ insight }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="analysis.min_price_per_kg !== null" class="man-card mt-3">
                                <div class="man-card-title mb-2"><el-icon class="man-card-icon"><Coin /></el-icon> Price Range</div>
                                <p class="man-muted mb-2" style="font-size:.8125rem;">Listings currently range from the lowest to the highest asking price per kg.</p>
                                <div class="man-range">
                                    <div class="man-range__end">
                                        <span class="man-range__label">Lowest</span>
                                        <span class="man-range__value">{{ fmtPrice(analysis.min_price_per_kg) }}</span>
                                    </div>
                                    <div class="man-range__bar"><div class="man-range__fill"></div></div>
                                    <div class="man-range__end text-end">
                                        <span class="man-range__label">Highest</span>
                                        <span class="man-range__value">{{ fmtPrice(analysis.max_price_per_kg) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Breakdown ─────────────────────────────────── -->
                        <div class="col-12 col-lg-7">
                            <div class="man-card mb-3">
                                <div class="man-card-title mb-3"><el-icon class="man-card-icon"><DataAnalysis /></el-icon> Coffee Types on the Market</div>
                                <div v-for="t in analysis.types" :key="t.label" class="man-breakdown-row">
                                    <div class="man-breakdown-row__top">
                                        <span class="man-breakdown-row__label">{{ t.label }}</span>
                                        <span class="man-muted">{{ t.count }} listing{{ t.count === 1 ? '' : 's' }} · {{ fmtPrice(t.average_price) }}/kg avg</span>
                                    </div>
                                    <div class="man-bar-track"><div class="man-bar-fill" :style="{ width: t.share + '%' }"></div></div>
                                    <div class="man-breakdown-row__share">{{ t.share }}% of listings</div>
                                </div>
                            </div>

                            <div class="man-card mb-3">
                                <div class="man-card-title mb-3"><el-icon class="man-card-icon"><Box /></el-icon> Top Origins by Volume</div>
                                <div v-for="(o, i) in analysis.origins" :key="o.label" class="man-origin-row">
                                    <span class="man-origin-rank">{{ i + 1 }}</span>
                                    <span class="man-breakdown-row__label flex-grow-1">{{ o.label }}</span>
                                    <span class="man-muted">{{ fmtNum(o.total_volume_kg) }} kg</span>
                                </div>
                            </div>

                            <div v-if="analysis.demand.length" class="man-card">
                                <div class="man-card-title mb-3"><el-icon class="man-card-icon"><Sunny /></el-icon> Buyer Demand Levels</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span v-for="d in analysis.demand" :key="d.label" class="man-tone" :class="demandTone(d.label)">
                                        {{ d.label }} demand — {{ d.count }} listing{{ d.count === 1 ? '' : 's' }} ({{ d.share }}%)
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.man-page {
    --green: #004532;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-low);
    color: var(--on-surface);
    min-height: 100%;
}
.man-muted { color: var(--on-surface-var); font-size: .8125rem; }

/* ── Header ──────────────────────────────────────────────────────────── */
.man-header { background: #fff; border-bottom: 1px solid var(--border); }
.man-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.man-title { font-size: 1.25rem; font-weight: 800; letter-spacing: -.02em; }
.man-subtitle { font-size: .8125rem; color: var(--on-surface-var); max-width: 560px; }

.man-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.man-btn-outline:hover { background: var(--surface-low); }

/* ── Stat cards ──────────────────────────────────────────────────────── */
.man-stat { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1.125rem; text-align: center; }
.man-stat__icon { width: 38px; height: 38px; border-radius: 10px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 17px; margin: 0 auto 10px; }
.man-stat__value { font-size: 1.5rem; font-weight: 800; color: var(--on-surface); line-height: 1.1; }
.man-stat__label { font-size: .75rem; color: var(--on-surface-var); font-weight: 600; margin-top: 4px; }

/* ── Cards ───────────────────────────────────────────────────────────── */
.man-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1.125rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.man-card-title { display: inline-flex; align-items: center; gap: 8px; font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.man-card-icon { width: 26px; height: 26px; border-radius: 7px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.man-empty-card { display: flex; flex-direction: column; align-items: center; padding: 3rem 1rem; text-align: center; }

/* ── Insights ────────────────────────────────────────────────────────── */
.man-insight-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.man-insight-list li { display: flex; align-items: flex-start; gap: 8px; font-size: .8438rem; color: var(--on-surface); line-height: 1.5; }
.man-insight-check { color: #16a34a; font-size: 15px; margin-top: 2px; flex-shrink: 0; }

/* ── Price range ─────────────────────────────────────────────────────── */
.man-range__end { display: flex; flex-direction: column; }
.man-range__label { font-size: .625rem; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); font-weight: 700; }
.man-range__value { font-size: .9375rem; font-weight: 800; color: var(--on-surface); }
.man-range { display: flex; align-items: center; gap: 10px; }
.man-range__bar { flex: 1; height: 6px; border-radius: 999px; background: linear-gradient(90deg, #dcfce7, var(--green)); }

/* ── Breakdown rows ──────────────────────────────────────────────────── */
.man-breakdown-row { margin-bottom: 16px; }
.man-breakdown-row:last-child { margin-bottom: 0; }
.man-breakdown-row__top { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 6px; gap: 8px; }
.man-breakdown-row__label { font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.man-breakdown-row__share { font-size: .6875rem; color: var(--on-surface-var); margin-top: 4px; }

.man-bar-track { height: 8px; border-radius: 999px; background: var(--surface-low); overflow: hidden; }
.man-bar-fill { height: 100%; border-radius: 999px; background: var(--green); }

/* ── Origins ─────────────────────────────────────────────────────────── */
.man-origin-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.man-origin-row:last-child { border-bottom: none; }
.man-origin-rank { width: 22px; height: 22px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); font-size: .6875rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Demand tones ────────────────────────────────────────────────────── */
.man-tone { font-size: .75rem; font-weight: 700; padding: 6px 12px; border-radius: 999px; }
.man-tone--green { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.man-tone--amber { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.man-tone--red { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.man-tone--muted { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }
</style>
