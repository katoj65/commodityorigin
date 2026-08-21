<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    Location, Calendar, WindPower, WarningFilled,
    Sunny, PartlyCloudy, Cloudy, Umbrella, Lightning, Odometer,
} from '@element-plus/icons-vue';

const props = defineProps({
    forecasts: { type: Array, default: () => [] },
    regionOptions: { type: Array, default: () => [] },
});

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(`${value}T00:00:00`);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, { weekday: 'long', month: 'short', day: 'numeric' });
}

function relativeDayLabel(value) {
    if (!value) return '';
    const target = new Date(`${value}T00:00:00`);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const diffDays = Math.round((target.getTime() - today.getTime()) / 86400000);
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    return '';
}

function dayShortLabel(value) {
    const relative = relativeDayLabel(value);
    if (relative) return relative;
    if (!value) return '—';
    const date = new Date(`${value}T00:00:00`);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, { weekday: 'short' });
}

/* ── Condition presentation ───────────────────────────────────────────────
   icon, tag type (for the pill under the hero temp), and an accent
   bg/color pair used to tint the hero condition badge + day-strip icons. */
const conditionMeta = {
    'Sunny':          { icon: Sunny,        tagType: 'warning', bg: '#fff7ed', color: '#c2410c' },
    'Partly Cloudy':  { icon: PartlyCloudy, tagType: 'info',    bg: '#eff6ff', color: '#1d4ed8' },
    'Cloudy':         { icon: Cloudy,       tagType: 'info',    bg: '#f1f5f9', color: '#475569' },
    'Rainy':          { icon: Umbrella,     tagType: 'primary', bg: '#eff6ff', color: '#0369a1' },
    'Thunderstorms':  { icon: Lightning,    tagType: 'danger',  bg: '#fef2f2', color: '#b91c1c' },
};
const fallbackMeta = { icon: Cloudy, tagType: 'info', bg: '#f1f5f9', color: '#6b7280' };

function meta(forecast) {
    return conditionMeta[forecast?.condition] || fallbackMeta;
}

function conditionIcon(forecast) {
    return meta(forecast).icon;
}

function conditionAccent(forecast) {
    const m = meta(forecast);
    return { background: m.bg, color: m.color };
}

/* Advisory callouts borrow the same severity tone as the condition —
   thunderstorms read as a hard warning, rain as a caution, everything
   else as a neutral farming tip. */
function advisoryTone(forecast) {
    const type = meta(forecast).tagType;
    if (type === 'danger') return 'wf-advisory--danger';
    if (type === 'primary') return 'wf-advisory--caution';
    return 'wf-advisory--info';
}

/* ── Group forecasts by region, ordered per regionOptions ────────────────
   Each group surfaces a "primary" (soonest) forecast for the hero tile,
   plus the remaining days as a horizontal outlook strip. */
const regionGroups = computed(() => props.regionOptions
    .map((region) => {
        const sorted = props.forecasts
            .filter((forecast) => forecast.region === region)
            .sort((a, b) => new Date(`${a.forecast_date}T00:00:00`) - new Date(`${b.forecast_date}T00:00:00`));
        return { region, primary: sorted[0] ?? null, upcoming: sorted.slice(1) };
    })
    .filter((group) => group.primary));
</script>

<template>
    <DesignPreviewLayout title="Weather Forecast">
        <Head title="Weather Forecast" />

        <div class="wf-page">

            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="wf-header">
                <div class="wf-header__inner">
                    <div>
                        <div class="wf-kicker">Farm Workspace</div>
                        <h1 class="wf-title mb-0">Weather Forecast</h1>
                        <p class="wf-subtitle mb-0">Outlook for Uganda's major coffee-growing regions.</p>
                    </div>
                    <div class="wf-header__meta">
                        <span class="wf-header__badge"><el-icon><Location /></el-icon> {{ regionGroups.length }} regions</span>
                        <span class="wf-header__badge"><el-icon><Calendar /></el-icon> {{ forecasts.length }} forecasts</span>
                    </div>
                </div>
            </div>

            <!-- ── Region outlook cards ─────────────────────────────────── -->
            <div class="wf-body">
                <div v-if="regionGroups.length" class="wf-grid">
                    <article v-for="group in regionGroups" :key="group.region" class="wf-card">

                        <header class="wf-card__head">
                            <div class="wf-card__region"><el-icon class="wf-card__pin"><Location /></el-icon> {{ group.region }}</div>
                            <span class="wf-card__count">{{ group.upcoming.length + 1 }}-day outlook</span>
                        </header>

                        <!-- Hero tile: soonest forecast for this region -->
                        <div class="wf-hero">
                            <div class="wf-hero__icon" :style="conditionAccent(group.primary)">
                                <el-icon :size="28"><component :is="conditionIcon(group.primary)" /></el-icon>
                            </div>
                            <div class="wf-hero__info">
                                <div class="wf-hero__day">{{ dayShortLabel(group.primary.forecast_date) }} · {{ formatDate(group.primary.forecast_date) }}</div>
                                <div class="wf-hero__temp">{{ group.primary.temperature_min }}°–{{ group.primary.temperature_max }}°</div>
                                <el-tag :type="meta(group.primary).tagType" effect="light" size="small" round>{{ group.primary.condition }}</el-tag>
                            </div>
                            <div class="wf-hero__stats">
                                <span class="wf-stat"><el-icon><Umbrella /></el-icon> {{ group.primary.rainfall_mm !== null ? `${group.primary.rainfall_mm} mm` : '—' }}</span>
                                <span class="wf-stat"><el-icon><Odometer /></el-icon> {{ group.primary.humidity_percentage !== null ? `${group.primary.humidity_percentage}% humidity` : '—' }}</span>
                                <span class="wf-stat"><el-icon><WindPower /></el-icon> {{ group.primary.wind_speed_kmh !== null ? `${group.primary.wind_speed_kmh} km/h` : '—' }}</span>
                            </div>
                        </div>

                        <!-- Farming advisory callout -->
                        <div v-if="group.primary.advisory" class="wf-advisory" :class="advisoryTone(group.primary)">
                            <el-icon><WarningFilled /></el-icon>
                            <span>{{ group.primary.advisory }}</span>
                        </div>

                        <!-- Upcoming days strip -->
                        <div v-if="group.upcoming.length" class="wf-strip">
                            <div v-for="day in group.upcoming" :key="day.id" class="wf-chip">
                                <span class="wf-chip__day">{{ dayShortLabel(day.forecast_date) }}</span>
                                <el-icon class="wf-chip__icon" :style="{ color: meta(day).color }"><component :is="conditionIcon(day)" /></el-icon>
                                <span class="wf-chip__temp">{{ day.temperature_min }}°–{{ day.temperature_max }}°</span>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="wf-empty">
                    <div class="wf-empty__icon"><el-icon :size="24"><Sunny /></el-icon></div>
                    <div class="wf-empty__title">No forecasts available</div>
                    <p class="wf-empty__text">Check back soon for the latest regional weather outlook.</p>
                </div>
            </div>

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.wf-page {
    --green: #004532;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-white: #ffffff;
    --surface-low: #f8fafc;
    --surface-high: #eef2f0;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-low);
    color: var(--on-surface);
    min-height: 100%;
    line-height: 1.5;
}

/* ── Header ────────────────────────────────────────────────────────────── */
.wf-header { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.wf-header__inner { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: 1rem clamp(1rem, 3vw, 2rem); }
.wf-kicker { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 4px; line-height: 1.4; }
.wf-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.25; }
.wf-subtitle { font-size: .8125rem; color: var(--on-surface-var); margin-top: 2px; line-height: 1.5; }
.wf-header__meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.wf-header__badge { display: inline-flex; align-items: center; gap: 6px; font-size: .75rem; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 999px; padding: 5px 12px; }
.wf-header__badge :deep(.el-icon) { font-size: 13px; color: #9ca3af; }

/* ── Body / grid ───────────────────────────────────────────────────────── */
.wf-body { padding: 1.5rem clamp(1rem, 3vw, 2rem) 3rem; }
.wf-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }

/* ── Region card ───────────────────────────────────────────────────────── */
.wf-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); transition: box-shadow .15s ease, transform .15s ease; }
.wf-card:hover { box-shadow: 0 12px 28px -16px rgba(15, 23, 42, .18); transform: translateY(-1px); }

.wf-card__head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: 12px 16px; border-bottom: 1px solid var(--surface-high); }
.wf-card__region { display: flex; align-items: center; gap: 6px; font-size: .875rem; font-weight: 800; color: var(--on-surface); }
.wf-card__pin { font-size: 14px; color: var(--green); }
.wf-card__count { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); border-radius: 999px; padding: 3px 9px; white-space: nowrap; }

/* ── Hero tile ─────────────────────────────────────────────────────────── */
.wf-hero { display: flex; align-items: center; gap: 14px; padding: 16px; }
.wf-hero__icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.wf-hero__info { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.wf-hero__day { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); }
.wf-hero__temp { font-size: 1.75rem; font-weight: 800; letter-spacing: -.02em; line-height: 1; font-variant-numeric: tabular-nums; }
.wf-hero__stats { display: flex; flex-direction: column; gap: 6px; margin-left: auto; flex-shrink: 0; }
.wf-stat { display: inline-flex; align-items: center; gap: 6px; font-size: .75rem; font-weight: 600; color: var(--on-surface-var); font-variant-numeric: tabular-nums; white-space: nowrap; }
.wf-stat :deep(.el-icon) { font-size: 13px; color: #9ca3af; }

/* ── Advisory callout ──────────────────────────────────────────────────── */
.wf-advisory { display: flex; align-items: flex-start; gap: 8px; margin: 0 16px 14px; padding: 10px 12px; border-radius: 10px; font-size: .78125rem; line-height: 1.45; border-left: 3px solid; }
.wf-advisory :deep(.el-icon) { font-size: 14px; margin-top: 1px; flex-shrink: 0; }
.wf-advisory--info { background: #f0fdf4; border-color: #86efac; color: #166534; }
.wf-advisory--caution { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.wf-advisory--danger { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

/* ── Upcoming days strip ───────────────────────────────────────────────── */
.wf-strip { display: flex; gap: 2px; overflow-x: auto; padding: 10px 10px 12px; border-top: 1px solid var(--surface-high); background: var(--surface-low); scrollbar-width: none; }
.wf-strip::-webkit-scrollbar { display: none; }
.wf-chip { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1 1 0; min-width: 58px; padding: 8px 4px; border-radius: 10px; transition: background .15s ease; }
.wf-chip:hover { background: var(--surface-white); }
.wf-chip__day { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); }
.wf-chip__icon { font-size: 17px; }
.wf-chip__temp { font-size: .75rem; font-weight: 700; color: var(--on-surface); font-variant-numeric: tabular-nums; white-space: nowrap; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.wf-empty { text-align: center; padding: 3rem 1rem; background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 14px; }
.wf-empty__icon { width: 52px; height: 52px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.wf-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.wf-empty__text { font-size: .8125rem; color: var(--on-surface-var); margin-bottom: 0; max-width: 360px; margin-left: auto; margin-right: auto; line-height: 1.5; }

@media (max-width: 480px) {
    .wf-hero { flex-wrap: wrap; }
    .wf-hero__stats { margin-left: 0; flex-direction: row; flex-wrap: wrap; gap: 10px; }
}
</style>
