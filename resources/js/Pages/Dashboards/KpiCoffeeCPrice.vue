<script setup>
import { Head } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { CircleCheck, Files, PriceTag, TrendCharts } from '@element-plus/icons-vue';

const props = defineProps({
    indexes: { type: Array, default: () => [] },
    averagePrice: { type: Number, default: null },
    activeCount: { type: Number, default: 0 },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
</script>

<template>
    <DesignPreviewLayout title="Coffee C Price">
        <Head title="Coffee C Price" />

        <div class="kpi-page">
            <header class="kpi-hero">
                <div class="kpi-hero__text">
                    <h1 class="kpi-hero__title"><el-icon><PriceTag /></el-icon> Coffee C Price</h1>
                    <p class="kpi-hero__subtitle">The admin-curated reference price index — every tracked commodity, its current price, and how it's moved.</p>
                </div>
            </header>

            <div class="kpi-stats">
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Tracked Indexes</span>
                    <strong class="kpi-stat__value">{{ indexes.length }}</strong>
                </div>
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Active</span>
                    <strong class="kpi-stat__value">{{ activeCount }}</strong>
                </div>
                <div class="kpi-stat">
                    <span class="kpi-stat__label">Average Price</span>
                    <strong class="kpi-stat__value">{{ fmtMoney(averagePrice) }}</strong>
                </div>
            </div>

            <section class="kpi-section">
                <div class="kpi-section__head">
                    <h2 class="kpi-section__title"><el-icon><Files /></el-icon> Price Index</h2>
                    <span class="kpi-section__count">{{ indexes.length }}</span>
                </div>

                <div v-if="indexes.length" class="kpi-table-wrap">
                    <table class="kpi-table">
                        <thead>
                            <tr>
                                <th><span class="kpi-th"><el-icon><Files /></el-icon> Item</span></th>
                                <th><span class="kpi-th"><el-icon><PriceTag /></el-icon> Current Price</span></th>
                                <th><span class="kpi-th"><el-icon><TrendCharts /></el-icon> Fluctuation</span></th>
                                <th><span class="kpi-th"><el-icon><CircleCheck /></el-icon> Status</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="index in indexes" :key="index.id">
                                <td><strong>{{ index.item }}</strong></td>
                                <td class="kpi-mono">{{ fmtMoney(index.current_price) }}</td>
                                <td class="kpi-mono" :class="index.percentage_fluctuation >= 0 ? 'kpi-up' : 'kpi-down'">
                                    {{ index.percentage_fluctuation != null ? (index.percentage_fluctuation >= 0 ? '+' : '') + index.percentage_fluctuation + '%' : '—' }}
                                </td>
                                <td><span class="kpi-badge">{{ index.status }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="kpi-empty">No price index entries have been recorded yet.</p>
            </section>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.kpi-page {
    --green: #000000;
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --on-surface: #121516;
    --on-surface-var: #4B5457;
    --surface-low: #F5F6F7;
    font-family: 'Inter', system-ui, sans-serif;
    color: var(--on-surface);
    min-height: 100%;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.kpi-hero__title { display: flex; align-items: center; gap: 8px; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.015em; color: var(--on-surface); margin: 0; }
.kpi-hero__subtitle { font-size: 13.5px; line-height: 1.5; color: var(--on-surface-var); margin: 6px 0 0; max-width: 640px; }

.kpi-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.kpi-stat { display: flex; flex-direction: column; gap: 4px; background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 14px 16px; }
.kpi-stat__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); }
.kpi-stat__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); font-variant-numeric: tabular-nums; }
.kpi-up { color: #16A34A !important; }
.kpi-down { color: #DC2626 !important; }

.kpi-section { background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 18px; }
.kpi-section__head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.kpi-section__title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin: 0; }
.kpi-section__count { font-size: 11px; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); padding: 1px 8px; border-radius: 999px; }

.kpi-table-wrap { overflow-x: auto; }
.kpi-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.kpi-table th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--card-border); white-space: nowrap; }
.kpi-th { display: inline-flex; align-items: center; gap: 5px; }
.kpi-th .el-icon { font-size: 13px; }
.kpi-table td { padding: 11px 12px; border-bottom: 1px solid var(--card-border); color: var(--on-surface); }
.kpi-table tr:last-child td { border-bottom: none; }
.kpi-mono { font-variant-numeric: tabular-nums; }
.kpi-badge { display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); background: var(--surface-low); padding: 2px 8px; border-radius: 999px; }
.kpi-empty { font-size: 13px; color: var(--on-surface-var); margin: 0; padding: 8px 0; }

@media (max-width: 900px) {
    .kpi-stats { grid-template-columns: 1fr 1fr; }
}
</style>
