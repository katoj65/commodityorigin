<script setup>
import { computed } from 'vue';
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
   icon + a tone name drawn from the app's own semantic palette (good /
   info / warn / bad), used to tint the hero condition badge, the
   day-strip icons, and the advisory callout consistently. */
const conditionMeta = {
    'Sunny':          { icon: Sunny,        tone: 'warn' },
    'Partly Cloudy':  { icon: PartlyCloudy, tone: 'info' },
    'Cloudy':         { icon: Cloudy,       tone: 'neutral' },
    'Rainy':          { icon: Umbrella,     tone: 'info' },
    'Thunderstorms':  { icon: Lightning,    tone: 'bad' },
};
const fallbackMeta = { icon: Cloudy, tone: 'neutral' };

function meta(forecast) {
    return conditionMeta[forecast?.condition] || fallbackMeta;
}

function conditionIcon(forecast) {
    return meta(forecast).icon;
}

function conditionTone(forecast) {
    return meta(forecast).tone;
}

/* Advisory callouts borrow the same severity tone as the condition —
   thunderstorms read as a hard warning, rain/partly-cloudy as caution,
   everything else as a neutral farming tip. */
function advisoryTone(forecast) {
    const tone = conditionTone(forecast);
    if (tone === 'bad') return 'wf-advisory--bad';
    if (tone === 'info') return 'wf-advisory--info';
    return 'wf-advisory--neutral';
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
        <div class="wf-page">

            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="wf-header">
                <div class="wf-header__text">
                    <h1 class="wf-title">Weather Forecast</h1>
                    <p class="wf-subtitle">Outlook for Uganda's major coffee-growing regions.</p>
                </div>
                <div class="wf-header__meta">
                    <span class="wf-header__badge"><el-icon><Location /></el-icon> {{ regionGroups.length }} regions</span>
                    <span class="wf-header__badge"><el-icon><Calendar /></el-icon> {{ forecasts.length }} forecasts</span>
                </div>
            </div>

            <!-- ── Region outlook cards ─────────────────────────────────── -->
            <div v-if="regionGroups.length" class="wf-grid">
                <article v-for="group in regionGroups" :key="group.region" class="wf-card">

                    <header class="wf-card__head">
                        <div class="wf-card__region"><el-icon class="wf-card__pin"><Location /></el-icon> {{ group.region }}</div>
                        <span class="wf-card__count">{{ group.upcoming.length + 1 }}-day outlook</span>
                    </header>

                    <!-- Hero tile: soonest forecast for this region -->
                    <div class="wf-hero">
                        <div class="wf-hero__icon" :class="`wf-hero__icon--${conditionTone(group.primary)}`">
                            <el-icon :size="26"><component :is="conditionIcon(group.primary)" /></el-icon>
                        </div>
                        <div class="wf-hero__info">
                            <div class="wf-hero__day">{{ dayShortLabel(group.primary.forecast_date) }} · {{ formatDate(group.primary.forecast_date) }}</div>
                            <div class="wf-hero__temp">{{ group.primary.temperature_min }}°–{{ group.primary.temperature_max }}°</div>
                            <span class="wf-badge" :class="`wf-badge--${conditionTone(group.primary)}`">{{ group.primary.condition }}</span>
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
                            <el-icon class="wf-chip__icon" :class="`wf-chip__icon--${conditionTone(day)}`"><component :is="conditionIcon(day)" /></el-icon>
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
    </DesignPreviewLayout>
</template>

<style scoped>
.wf-page {
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --surface-elevated: #F1F2F3;
    --border: #E5E7EB;
    --primary: #000000;
    --text: #121516;
    --text-2: #4B5457;
    --text-muted: #6F7677;
    --success: #15803D;
    --success-soft: #F0FDF4;
    --warning: #B45309;
    --warning-soft: #FEF3E2;
    --error: #B91C1C;
    --error-soft: #FEF2F2;
    --info: #1D4ED8;
    --info-soft: #EFF6FF;
    --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-family: var(--font-sans);
    background: var(--surface);
    color: var(--text);
    min-height: 100%;
}

/* ── Header ────────────────────────────────────────────────────────────── */
.wf-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 24px; flex-wrap: wrap; }
.wf-header__text { min-width: 0; }
.wf-title { font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0 0 6px; }
.wf-subtitle { font-size: 13.5px; line-height: 20px; color: var(--text-2); margin: 0; max-width: 60ch; }
.wf-header__meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }
.wf-header__badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600; color: var(--text-2);
    background: var(--surface-muted); border: 1px solid var(--border);
    border-radius: 999px; padding: 5px 12px;
}
.wf-header__badge :deep(.el-icon) { font-size: 13px; color: var(--text-muted); }

/* ── Region card grid ──────────────────────────────────────────────────── */
.wf-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }

/* ── Region card ───────────────────────────────────────────────────────── */
.wf-card { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; overflow: hidden; transition: border-color 120ms ease; }
.wf-card:hover { border-color: var(--text-muted); }

.wf-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 12px 16px; border-bottom: 1px solid var(--border); }
.wf-card__region { display: flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 700; color: var(--text); }
.wf-card__pin { font-size: 14px; color: var(--text-muted); }
.wf-card__count { font-size: 11px; font-weight: 600; color: var(--text-2); background: var(--surface-muted); border-radius: 6px; padding: 3px 9px; white-space: nowrap; }

/* ── Hero tile ─────────────────────────────────────────────────────────── */
.wf-hero { display: flex; align-items: center; gap: 14px; padding: 16px; }
.wf-hero__icon { width: 52px; height: 52px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--surface-elevated); color: var(--text-2); }
.wf-hero__icon--warn { background: var(--warning-soft); color: var(--warning); }
.wf-hero__icon--info { background: var(--info-soft); color: var(--info); }
.wf-hero__icon--bad { background: var(--error-soft); color: var(--error); }
.wf-hero__icon--neutral { background: var(--surface-elevated); color: var(--text-2); }
.wf-hero__info { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.wf-hero__day { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); }
.wf-hero__temp { font-size: 26px; font-weight: 700; letter-spacing: -0.02em; line-height: 1; font-variant-numeric: tabular-nums; color: var(--text); }
.wf-hero__stats { display: flex; flex-direction: column; gap: 6px; margin-left: auto; flex-shrink: 0; }
.wf-stat { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--text-2); font-variant-numeric: tabular-nums; white-space: nowrap; }
.wf-stat :deep(.el-icon) { font-size: 13px; color: var(--text-muted); }

/* ── Condition badge ───────────────────────────────────────────────────── */
.wf-badge { display: inline-flex; align-items: center; align-self: flex-start; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
.wf-badge--warn { background: var(--warning-soft); color: var(--warning); }
.wf-badge--info { background: var(--info-soft); color: var(--info); }
.wf-badge--bad { background: var(--error-soft); color: var(--error); }
.wf-badge--neutral { background: var(--surface-elevated); color: var(--text-2); }

/* ── Advisory callout ──────────────────────────────────────────────────── */
.wf-advisory { display: flex; align-items: flex-start; gap: 8px; margin: 0 16px 14px; padding: 10px 12px; border-radius: 6px; font-size: 12.5px; line-height: 1.45; border-left: 3px solid; }
.wf-advisory :deep(.el-icon) { font-size: 14px; margin-top: 1px; flex-shrink: 0; }
.wf-advisory--neutral { background: var(--success-soft); border-color: #86EFAC; color: var(--success); }
.wf-advisory--info { background: var(--info-soft); border-color: #93C5FD; color: var(--info); }
.wf-advisory--bad { background: var(--error-soft); border-color: #FCA5A5; color: var(--error); }

/* ── Upcoming days strip ───────────────────────────────────────────────── */
.wf-strip { display: flex; gap: 2px; overflow-x: auto; padding: 10px 10px 12px; border-top: 1px solid var(--border); background: var(--surface-muted); scrollbar-width: none; }
.wf-strip::-webkit-scrollbar { display: none; }
.wf-chip { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1 1 0; min-width: 58px; padding: 8px 4px; border-radius: 6px; transition: background 120ms ease; }
.wf-chip:hover { background: var(--surface); }
.wf-chip__day { font-size: 11px; font-weight: 700; color: var(--text-2); }
.wf-chip__icon { font-size: 17px; color: var(--text-2); }
.wf-chip__icon--warn { color: var(--warning); }
.wf-chip__icon--info { color: var(--info); }
.wf-chip__icon--bad { color: var(--error); }
.wf-chip__temp { font-size: 12px; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; white-space: nowrap; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.wf-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center; padding: 48px 20px; background: var(--surface-muted); border: 1px solid var(--border); border-radius: 6px; }
.wf-empty__icon { color: var(--text-muted); }
.wf-empty__title { font-size: 14px; font-weight: 700; color: var(--text); }
.wf-empty__text { font-size: 13px; color: var(--text-muted); margin: 0; max-width: 360px; }

@media (max-width: 640px) {
    .wf-header { flex-direction: column; align-items: stretch; }
}

@media (max-width: 480px) {
    .wf-hero { flex-wrap: wrap; }
    .wf-hero__stats { margin-left: 0; flex-direction: row; flex-wrap: wrap; gap: 10px; }
}
</style>
