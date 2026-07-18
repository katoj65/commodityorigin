<script setup>
import { Coin } from '@element-plus/icons-vue';

const props = defineProps({
    rates: { type: Array, default: () => [] },
    title: { type: String, default: 'Exchange Rates' },
    showLive: { type: Boolean, default: true },
});

function formatRate(rate) {
    if (rate === null || rate === undefined) return '—';
    return Number(rate).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
}

function formatChange(change) {
    if (change === null || change === undefined) return '—';
    const n = Number(change);
    return `${n >= 0 ? '+' : ''}${n.toFixed(2)}%`;
}
</script>

<template>
    <div class="xr-widget">
        <div class="xr-widget__head">
            <div class="xr-widget__title"><el-icon class="xr-widget__icon"><Coin /></el-icon> {{ title }}</div>
            <span v-if="showLive" class="xr-widget__live"><i></i> Live</span>
        </div>

        <div v-if="rates.length" class="xr-list">
            <div v-for="r in rates" :key="r.pair ?? `${r.base_currency}/${r.quote_currency}`" class="xr-row">
                <span class="xr-row__pair">{{ r.pair ?? `${r.base_currency} / ${r.quote_currency}` }}</span>
                <span class="xr-row__value">{{ formatRate(r.rate) }}</span>
                <span :class="r.up ? 'xr-up' : 'xr-down'">{{ formatChange(r.daily_change_percent) }}</span>
            </div>
        </div>
        <p v-else class="xr-empty">No exchange rate data available.</p>
    </div>
</template>

<style scoped>
.xr-widget {
    font-family: 'Manrope', system-ui, sans-serif;
}

.xr-widget__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.xr-widget__title {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.875rem;
    font-weight: 700;
    color: #111827;
}

.xr-widget__icon {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.xr-widget__live {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #166534;
}

.xr-widget__live i {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #22c55e;
    display: inline-block;
    animation: xr-pulse 1.8s infinite;
}

.xr-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.xr-row {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 10px;
    align-items: center;
    padding: 7px 0;
    border-bottom: 1px solid #f8fafc;
    font-size: 0.8125rem;
    font-variant-numeric: tabular-nums;
}

.xr-row:last-child {
    border-bottom: none;
}

.xr-row__pair {
    color: #6b7280;
    font-weight: 600;
}

.xr-row__value {
    font-weight: 700;
    font-family: 'IBM Plex Mono', 'Manrope', monospace;
}

.xr-up { color: #166534; font-weight: 700; }
.xr-down { color: #991b1b; font-weight: 700; }

.xr-empty {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0;
    padding: 0.5rem 0;
}

@keyframes xr-pulse {
    0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, .5); }
    70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
    100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}
</style>
