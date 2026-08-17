<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    TrendCharts, PieChart, ArrowUp, ArrowDown, Minus, Coin, Sunny,
    Box, Ship, WarningFilled, Odometer, Tickets,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    horizons: { type: Array, default: () => [] },
    signals: { type: Array, default: () => [] },
});

/* ── Crop filter ──────────────────────────────────────────────────────── */
const selectedCrop = ref('All');
const cropOptions = computed(() => {
    const crops = new Set([...props.horizons, ...props.signals].map((f) => f.crop_type));
    return ['All', ...crops];
});

const filteredHorizons = computed(() => (selectedCrop.value === 'All' ? props.horizons : props.horizons.filter((f) => f.crop_type === selectedCrop.value)));
const filteredSignals = computed(() => (selectedCrop.value === 'All' ? props.signals : props.signals.filter((f) => f.crop_type === selectedCrop.value)));

const horizonsByCrop = computed(() => {
    const groups = {};
    filteredHorizons.value.forEach((f) => {
        (groups[f.crop_type] ??= []).push(f);
    });
    return groups;
});

/* ── Display helpers ──────────────────────────────────────────────────── */
const categoryIcon = (c) => ({
    Price: Coin,
    Harvest: Sunny,
    Supply: Box,
    Demand: TrendCharts,
    Export: Ship,
    Weather: WarningFilled,
    'Trade Risk': WarningFilled,
}[c] ?? PieChart);

const directionIcon = (d) => ({ up: ArrowUp, down: ArrowDown, steady: Minus }[d] ?? Minus);
const directionCls = (d) => ({ up: 'fcp-dir--up', down: 'fcp-dir--down', steady: 'fcp-dir--steady' }[d] ?? 'fcp-dir--steady');
const confidenceCls = (c) => (c >= 80 ? 'fcp-conf--high' : c >= 65 ? 'fcp-conf--mid' : 'fcp-conf--low');
</script>

<template>
    <AppLayout title="Market Forecast" full-width flush :show-banner="false">
        <Head title="Market Forecast" />

        <div class="fcp-page">
            <!-- ══════════════════════════════════════════════════════════
                 Header
                 ══════════════════════════════════════════════════════════ -->
            <div class="fcp-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="fcp-kicker"><el-icon><Odometer /></el-icon> Market Intelligence</div>
                            <h1 class="fcp-title mb-0">Coffee Market Forecast</h1>
                            <p class="fcp-subtitle mb-0">AI-assisted price and signal forecasts to help coffee stakeholders plan ahead.</p>
                        </div>
                        <Link :href="route('market.index')" class="btn fcp-btn-outline btn-sm">
                            <el-icon><Tickets /></el-icon> Back to Market
                        </Link>
                    </div>

                    <div class="fcp-crop-tabs pb-3">
                        <button
                            v-for="c in cropOptions"
                            :key="c"
                            type="button"
                            class="fcp-crop-tab"
                            :class="{ 'fcp-crop-tab--active': selectedCrop === c }"
                            @click="selectedCrop = c"
                        >{{ c }}</button>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-4">

                <!-- ══════════════════════════════════════════════════════
                     Price horizon forecasts
                     ══════════════════════════════════════════════════════ -->
                <div v-for="(group, crop) in horizonsByCrop" :key="crop" class="fcp-section mb-4">
                    <div class="fcp-section-head mb-3">
                        <el-icon class="fcp-section-icon"><TrendCharts /></el-icon>
                        <h2 class="fcp-section-title">{{ crop }} Price Outlook</h2>
                    </div>

                    <div class="row g-3">
                        <div v-for="f in group" :key="f.id" class="col-12 col-md-4">
                            <div class="fcp-horizon-card h-100">
                                <div class="fcp-horizon-card__top">
                                    <span class="fcp-horizon-badge">{{ f.horizon }}</span>
                                    <span class="fcp-dir" :class="directionCls(f.direction)">
                                        <el-icon><component :is="directionIcon(f.direction)" /></el-icon>
                                    </span>
                                </div>
                                <div class="fcp-horizon-metric" :class="directionCls(f.direction)">{{ f.headline }}</div>
                                <p class="fcp-horizon-detail">{{ f.detail }}</p>
                                <div class="fcp-conf-track"><div class="fcp-conf-fill" :class="confidenceCls(f.confidence)" :style="{ width: (f.confidence ?? 0) + '%' }"></div></div>
                                <div class="fcp-conf-label">{{ f.confidence }}% confidence</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════
                     Signal forecasts
                     ══════════════════════════════════════════════════════ -->
                <div class="fcp-section">
                    <div class="fcp-section-head mb-3">
                        <el-icon class="fcp-section-icon"><PieChart /></el-icon>
                        <h2 class="fcp-section-title">Signal Forecasts</h2>
                        <span class="fcp-count">{{ filteredSignals.length }} signals</span>
                    </div>

                    <div v-if="!filteredSignals.length" class="fcp-empty">No signal forecasts for this crop yet.</div>

                    <div v-else class="row g-3">
                        <div v-for="s in filteredSignals" :key="s.id" class="col-12 col-md-6 col-xl-4">
                            <div class="fcp-signal-card h-100">
                                <div class="fcp-signal-card__top">
                                    <span class="fcp-signal-icon"><el-icon><component :is="categoryIcon(s.category)" /></el-icon></span>
                                    <div class="flex-grow-1">
                                        <div class="fcp-signal-category">{{ s.category }} Forecast</div>
                                        <div class="fcp-signal-crop">{{ s.crop_type }}</div>
                                    </div>
                                    <span class="fcp-dir" :class="directionCls(s.direction)">
                                        <el-icon><component :is="directionIcon(s.direction)" /></el-icon>
                                    </span>
                                </div>

                                <div class="fcp-signal-headline">{{ s.headline }}</div>
                                <p v-if="s.detail" class="fcp-signal-detail">{{ s.detail }}</p>

                                <div class="fcp-signal-card__footer">
                                    <div class="fcp-conf-track"><div class="fcp-conf-fill" :class="confidenceCls(s.confidence)" :style="{ width: (s.confidence ?? 0) + '%' }"></div></div>
                                    <span class="fcp-conf-label">{{ s.confidence }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fcp-page {
    --green: #004532;
    --green-dark: #002e20;
    --gold: #c8862a;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-low);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Header ──────────────────────────────────────────────────────────── */
.fcp-header { background: #fff; border-bottom: 1px solid var(--border); }
.fcp-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.fcp-title { font-size: 1.25rem; font-weight: 800; letter-spacing: -.02em; }
.fcp-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

.fcp-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.fcp-btn-outline:hover { background: var(--surface-low); }

.fcp-crop-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.fcp-crop-tab { border: 1px solid var(--border); background: #fff; color: var(--on-surface-var); font-size: .75rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; cursor: pointer; transition: all .15s; }
.fcp-crop-tab:hover { border-color: var(--green); color: var(--green); }
.fcp-crop-tab--active { background: var(--green); border-color: var(--green); color: #fff; }

/* ── Section ─────────────────────────────────────────────────────────── */
.fcp-section-head { display: flex; align-items: center; gap: 8px; }
.fcp-section-icon { width: 28px; height: 28px; border-radius: 8px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.fcp-section-title { font-size: 1rem; font-weight: 800; color: var(--on-surface); margin: 0; }
.fcp-count { margin-left: auto; font-size: .75rem; font-weight: 600; color: var(--on-surface-var); }
.fcp-empty { font-size: .8125rem; color: var(--on-surface-var); text-align: center; padding: 2rem 0; background: #fff; border: 1px solid var(--border); border-radius: 12px; }

/* ── Direction ───────────────────────────────────────────────────────── */
.fcp-dir { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.fcp-dir--up { background: #dcfce7; color: #166534; }
.fcp-dir--down { background: #fee2e2; color: #991b1b; }
.fcp-dir--steady { background: #f3f4f6; color: #6b7280; }

/* ── Confidence bar ──────────────────────────────────────────────────── */
.fcp-conf-track { height: 5px; border-radius: 999px; background: var(--surface-low); overflow: hidden; flex: 1; }
.fcp-conf-fill { height: 100%; border-radius: 999px; }
.fcp-conf--high { background: #16a34a; }
.fcp-conf--mid { background: var(--gold); }
.fcp-conf--low { background: #dc2626; }
.fcp-conf-label { font-size: .6875rem; color: var(--on-surface-var); font-weight: 600; white-space: nowrap; }

/* ── Horizon cards ───────────────────────────────────────────────────── */
.fcp-horizon-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1.125rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); display: flex; flex-direction: column; }
.fcp-horizon-card__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.fcp-horizon-badge { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); background: var(--surface-low); border-radius: 999px; padding: 3px 10px; }
.fcp-horizon-metric { font-size: 1.375rem; font-weight: 800; margin-bottom: 6px; }
.fcp-horizon-metric.fcp-dir--up { color: #166534; background: none; }
.fcp-horizon-metric.fcp-dir--down { color: #991b1b; background: none; }
.fcp-horizon-metric.fcp-dir--steady { color: var(--on-surface); background: none; }
.fcp-horizon-detail { font-size: .75rem; color: var(--on-surface-var); line-height: 1.5; flex: 1; margin-bottom: 12px; }
.fcp-conf-label { margin-top: 6px; display: block; }

/* ── Signal cards ────────────────────────────────────────────────────── */
.fcp-signal-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1.125rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); display: flex; flex-direction: column; transition: box-shadow .15s, transform .15s; }
.fcp-signal-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); transform: translateY(-1px); }
.fcp-signal-card__top { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
.fcp-signal-icon { width: 32px; height: 32px; border-radius: 9px; background: rgba(200,134,42,0.12); color: var(--gold); display: inline-flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.fcp-signal-category { font-size: .75rem; font-weight: 700; color: var(--on-surface); line-height: 1.2; }
.fcp-signal-crop { font-size: .6875rem; color: var(--on-surface-var); margin-top: 1px; }
.fcp-signal-headline { font-size: .9375rem; font-weight: 800; color: var(--on-surface); margin-bottom: 6px; }
.fcp-signal-detail { font-size: .75rem; color: var(--on-surface-var); line-height: 1.5; flex: 1; margin-bottom: 12px; }
.fcp-signal-card__footer { display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid var(--surface-low); }
.fcp-signal-card__footer .fcp-conf-label { margin-top: 0; }
</style>
