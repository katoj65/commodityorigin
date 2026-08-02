<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Box, Location, Calendar, WindPower,
    Sunny, PartlyCloudy, Cloudy, Umbrella, Lightning,
} from '@element-plus/icons-vue';

const props = defineProps({
    forecasts: { type: Array, default: () => [] },
    regionOptions: { type: Array, default: () => [] },
});

const avatarPalette = [
    { bg: '#eef2ff', color: '#4338ca' },
    { bg: '#ecfdf5', color: '#047857' },
    { bg: '#fff7ed', color: '#c2410c' },
    { bg: '#fdf4ff', color: '#a21caf' },
    { bg: '#eff6ff', color: '#1d4ed8' },
    { bg: '#f0fdfa', color: '#0f766e' },
];

function initials(name) {
    const parts = (name || '').trim().split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || 'W';
}

function avatarStyle(row) {
    const index = props.regionOptions.indexOf(row.region);
    const swatch = avatarPalette[(index >= 0 ? index : row.id) % avatarPalette.length];
    return { background: swatch.bg, color: swatch.color };
}

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(`${value}T00:00:00`);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
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

const conditionMeta = {
    'Sunny': { icon: Sunny, type: 'warning' },
    'Partly Cloudy': { icon: PartlyCloudy, type: 'info' },
    'Cloudy': { icon: Cloudy, type: 'info' },
    'Rainy': { icon: Umbrella, type: 'primary' },
    'Thunderstorms': { icon: Lightning, type: 'danger' },
};

function conditionIcon(row) {
    return conditionMeta[row.condition]?.icon || Cloudy;
}

function conditionType(row) {
    return conditionMeta[row.condition]?.type || 'info';
}
</script>

<template>
    <AppLayout title="Weather Forecast" full-width flush :show-banner="false">
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
                </div>
            </div>

            <!-- ── Toolbar ───────────────────────────────────────────────── -->
            <div class="wf-toolbar">
                <span class="wf-toolbar__title">All Regions</span>
                <span class="wf-toolbar__count">{{ regionOptions.length }} regions · {{ forecasts.length }} forecasts</span>
            </div>

            <!-- ── Table (edge-to-edge) ─────────────────────────────────── -->
            <el-table
                :data="forecasts"
                class="wf-table"
                row-key="id"
            >
                <el-table-column min-width="190">
                    <template #header><span class="wf-th"><el-icon><Location /></el-icon> Region</span></template>
                    <template #default="{ row }">
                        <div class="wf-cell-region">
                            <div class="wf-avatar" :style="avatarStyle(row)">{{ initials(row.region) }}</div>
                            <span class="wf-cell-name">{{ row.region }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column min-width="150">
                    <template #header><span class="wf-th"><el-icon><Calendar /></el-icon> Date</span></template>
                    <template #default="{ row }">
                        <div class="wf-cell-specs">
                            <span>{{ formatDate(row.forecast_date) }}</span>
                            <span v-if="relativeDayLabel(row.forecast_date)" class="wf-cell-sub">{{ relativeDayLabel(row.forecast_date) }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column min-width="160">
                    <template #header><span class="wf-th"><el-icon><Sunny /></el-icon> Condition</span></template>
                    <template #default="{ row }">
                        <el-tag :type="conditionType(row)" effect="light" size="small" round class="wf-condition-tag">
                            <el-icon><component :is="conditionIcon(row)" /></el-icon>
                            {{ row.condition }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column min-width="120" align="center">
                    <template #header><span class="wf-th wf-th--center"><el-icon><Box /></el-icon> Temp</span></template>
                    <template #default="{ row }">
                        <span class="wf-cell-strong">{{ row.temperature_min }}° – {{ row.temperature_max }}°</span>
                    </template>
                </el-table-column>
                <el-table-column min-width="130">
                    <template #header><span class="wf-th"><el-icon><Umbrella /></el-icon> Rainfall</span></template>
                    <template #default="{ row }">
                        <div class="wf-cell-specs">
                            <span>{{ row.rainfall_mm !== null ? `${row.rainfall_mm} mm` : '—' }}</span>
                            <span class="wf-cell-sub">{{ row.humidity_percentage !== null ? `${row.humidity_percentage}% humidity` : 'Humidity not set' }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column min-width="110" align="center">
                    <template #header><span class="wf-th wf-th--center"><el-icon><WindPower /></el-icon> Wind</span></template>
                    <template #default="{ row }">
                        <span>{{ row.wind_speed_kmh !== null ? `${row.wind_speed_kmh} km/h` : '—' }}</span>
                    </template>
                </el-table-column>
                <el-table-column min-width="260">
                    <template #header><span class="wf-th">Farming Advisory</span></template>
                    <template #default="{ row }">
                        <span class="wf-advisory">{{ row.advisory || '—' }}</span>
                    </template>
                </el-table-column>

                <template #empty>
                    <div class="wf-empty">
                        <div class="wf-empty__icon"><el-icon :size="24"><Sunny /></el-icon></div>
                        <div class="wf-empty__title">No forecasts available</div>
                        <p class="wf-empty__text">Check back soon for the latest regional weather outlook.</p>
                    </div>
                </template>
            </el-table>

        </div>
    </AppLayout>
</template>

<style scoped>
.wf-page {
    --green: #004532;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-white: #ffffff;
    --surface-low: #f8fafc;
    --surface-high: #e5e7eb;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
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

/* ── Toolbar ───────────────────────────────────────────────────────────── */
.wf-toolbar { display: flex; align-items: center; justify-content: space-between; padding: .875rem clamp(1rem, 3vw, 2rem); }
.wf-toolbar__title { font-size: .875rem; font-weight: 800; color: var(--on-surface); }
.wf-toolbar__count { font-size: .75rem; font-weight: 600; color: var(--on-surface-var); }

/* ── Table (edge-to-edge) ──────────────────────────────────────────────── */
.wf-table { width: 100%; border-top: 1px solid var(--surface-high); }
.wf-table :deep(.el-table__inner-wrapper::before) { display: none; }
.wf-table :deep(.el-table__header th.el-table__cell) {
    background: #fafbfc;
    border-bottom: 1px solid var(--surface-high);
    padding: 10px 0;
}
.wf-table :deep(.el-table__header th.el-table__cell > .cell) {
    font-size: .8125rem;
    font-weight: 600;
    color: #374151;
    line-height: 1.3;
}
.wf-table :deep(td.el-table__cell) { font-size: .8125rem; color: var(--on-surface); padding: 8px 0; }
.wf-table :deep(.el-table__cell:first-child .cell) { padding-left: clamp(1rem, 3vw, 2rem); }
.wf-table :deep(.el-table__cell:last-child .cell) { padding-right: clamp(1rem, 3vw, 2rem); }
.wf-table :deep(.el-table__row:hover .el-table__cell) { background: var(--surface-low); }

/* ── Table header icons ───────────────────────────────────────────────── */
.wf-th { display: inline-flex; align-items: center; gap: 6px; }
.wf-th :deep(.el-icon) { font-size: 14px; color: #9ca3af; }
.wf-th--center { justify-content: center; }

/* ── Table cells ───────────────────────────────────────────────────────── */
.wf-cell-region { display: flex; align-items: center; gap: 8px; }
.wf-avatar { width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .625rem; font-weight: 800; flex-shrink: 0; }
.wf-cell-name { font-weight: 700; }
.wf-cell-sub { font-size: .6875rem; color: var(--on-surface-var); line-height: 1.3; }
.wf-cell-specs { display: flex; flex-direction: column; gap: 0; }
.wf-cell-strong { font-weight: 700; }
.wf-condition-tag { display: inline-flex; align-items: center; gap: 4px; }
.wf-condition-tag :deep(.el-icon) { font-size: 13px; }
.wf-advisory { color: #374151; line-height: 1.4; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.wf-empty { text-align: center; padding: 3rem 1rem; }
.wf-empty__icon { width: 52px; height: 52px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.wf-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.wf-empty__text { font-size: .8125rem; color: var(--on-surface-var); margin-bottom: 16px; max-width: 360px; margin-left: auto; margin-right: auto; line-height: 1.5; }
</style>
