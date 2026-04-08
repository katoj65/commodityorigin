<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Clock,
    DataBoard,
    Download,
    Lightning,
    Plus,
    Trophy,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const tickerItems = [
    { label: 'Arabica (KC)', value: '242.15', delta: '+1.2%', tone: 'up' },
    { label: 'Robusta (RC)', value: '4,412.00', delta: '-0.4%', tone: 'down' },
    { label: 'Brazil NY 2/3', value: '198.40', delta: '+0.8%', tone: 'up' },
    { label: 'Colombia Excelso', value: '265.00', delta: '0.0%', tone: 'flat' },
];

const lots = [
    {
        id: '#ETH-2024-042',
        originCountry: 'Ethiopia',
        originRegion: 'Yirgacheffe',
        flag: 'ET',
        process: 'Natural Process',
        varietal: 'Heirloom',
        score: '89.5',
        bid: '$12.45',
        unit: '/lb',
        time: '04:12',
        timeTone: 'urgent',
    },
    {
        id: '#COL-2024-118',
        originCountry: 'Colombia',
        originRegion: 'Huila',
        flag: 'CO',
        process: 'Anaerobic Honey',
        varietal: 'Pink Bourbon',
        score: '91.0',
        bid: '$18.20',
        unit: '/lb',
        time: '12:45:02',
        timeTone: 'normal',
    },
    {
        id: '#PAN-2024-001',
        originCountry: 'Panama',
        originRegion: 'Boquete',
        flag: 'PA',
        process: 'Washed',
        varietal: 'Geisha',
        score: '94.5',
        bid: '$84.00',
        unit: '/lb',
        time: '01:15:30',
        timeTone: 'normal',
    },
    {
        id: '#BRA-2024-902',
        originCountry: 'Brazil',
        originRegion: 'Cerrado',
        flag: 'BR',
        process: 'Natural',
        varietal: 'Catuai',
        score: '84.0',
        bid: '$3.45',
        unit: '/lb',
        time: '00:55:12',
        timeTone: 'normal',
    },
];

const insightCards = [
    { label: 'Active bids', value: '04', icon: Lightning, tone: 'mint' },
    { label: 'Recent wins', value: '12', icon: Trophy, tone: 'sand' },
    { label: 'Total exposure', value: '$42,500.80', icon: DataBoard, tone: 'wide' },
];

const recentActivity = [
    {
        title: 'New high bid on Lot #PAN-001',
        detail: 'By institutional buyer #04 · 2m ago',
    },
    {
        title: 'Lot #ETH-042 entered final 5 mins',
        detail: 'Bidding frequency increasing · 4m ago',
    },
    {
        title: 'Auction finalized: Lot #COL-090',
        detail: 'Final price $24.50/lb · 1h ago',
    },
];

const featuredOrigin = computed(() => ({
    title: 'Colombia: Huila Harvest',
    copy: 'Ultra-premium anaerobic experiments from the Acevedo region are yielding exceptional cup scores above 90 this session.',
    score: '88.4',
    lots: '14',
}));

const flagEmoji = {
    ET: '🇪🇹',
    CO: '🇨🇴',
    PA: '🇵🇦',
    BR: '🇧🇷',
};
</script>

<template>
    <AppLayout title="Auction Terminal" full-width>
        <Head title="Auction Terminal" />

        <div class="auction-terminal-page">
            <div class="auction-terminal-shell">
                <section class="auction-ticker-strip">
                    <article v-for="item in tickerItems" :key="item.label" class="auction-ticker-item">
                        <span class="auction-ticker-label">{{ item.label }}</span>
                        <span class="auction-ticker-value">{{ item.value }}</span>
                        <span
                            class="auction-ticker-delta"
                            :class="{
                                'is-up': item.tone === 'up',
                                'is-down': item.tone === 'down',
                                'is-flat': item.tone === 'flat',
                            }"
                        >
                            {{ item.delta }}
                        </span>
                    </article>
                </section>

                <div class="auction-layout-grid">
                    <div class="auction-main-column">
                        <section class="auction-heading-card">
                            <div>
                                <h1 class="auction-heading-title">Live Auction Terminal</h1>
                                <p class="auction-heading-copy">
                                    Institutional-grade coffee lot bidding and real-time execution across verified origins.
                                </p>
                            </div>

                            <div class="auction-heading-buttons">
                                <button type="button" class="auction-secondary-button">
                                    <el-icon><Setting /></el-icon>
                                    <span>Filter Lots</span>
                                </button>
                                <button type="button" class="auction-secondary-button">
                                    <el-icon><Download /></el-icon>
                                    <span>Export Data</span>
                                </button>
                            </div>
                        </section>

                        <section class="auction-board-card">
                            <div class="auction-table-wrap">
                                <table class="auction-table">
                                    <thead>
                                        <tr>
                                            <th>Lot ID</th>
                                            <th>Origin</th>
                                            <th>Varietal Process</th>
                                            <th>Cup Score</th>
                                            <th>Current High Bid</th>
                                            <th>Time Left</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="lot in lots" :key="lot.id">
                                            <td>
                                                <div class="auction-lot-id">{{ lot.id }}</div>
                                            </td>
                                            <td>
                                                <div class="auction-origin-cell">
                                                    <span class="auction-flag">{{ flagEmoji[lot.flag] }}</span>
                                                    <div>
                                                        <div class="auction-origin-country">{{ lot.originCountry }}</div>
                                                        <div class="auction-origin-region">{{ lot.originRegion }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="auction-varietal-name">{{ lot.varietal }}</div>
                                                <div class="auction-varietal-process">{{ lot.process }}</div>
                                            </td>
                                            <td>
                                                <span class="auction-score-pill">{{ lot.score }}</span>
                                            </td>
                                            <td>
                                                <div class="auction-bid-value">{{ lot.bid }}</div>
                                                <div class="auction-bid-unit">{{ lot.unit }}</div>
                                            </td>
                                            <td>
                                                <div
                                                    class="auction-time-value"
                                                    :class="{ 'is-urgent': lot.timeTone === 'urgent' }"
                                                >
                                                    <el-icon><Clock /></el-icon>
                                                    <span>{{ lot.time }}</span>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <button type="button" class="auction-place-bid-button">Place Bid</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="auction-feature-card">
                            <div class="auction-feature-media">
                                <div class="auction-feature-badge">Featured Origin</div>
                                <div class="auction-feature-content">
                                    <div>
                                        <h2 class="auction-feature-title">{{ featuredOrigin.title }}</h2>
                                        <p class="auction-feature-copy">{{ featuredOrigin.copy }}</p>
                                    </div>

                                    <div class="auction-feature-metrics">
                                        <div class="auction-feature-metric">
                                            <span class="auction-feature-metric__label">Avg. score</span>
                                            <span class="auction-feature-metric__value">{{ featuredOrigin.score }}</span>
                                        </div>
                                        <div class="auction-feature-metric">
                                            <span class="auction-feature-metric__label">Lots available</span>
                                            <span class="auction-feature-metric__value">{{ featuredOrigin.lots }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="auction-side-column">
                        <section class="auction-insights-card">
                            <div class="auction-side-title">Bidding Insights</div>

                            <div class="auction-insights-grid">
                                <article
                                    v-for="card in insightCards"
                                    :key="card.label"
                                    class="auction-insight-item"
                                    :class="`is-${card.tone}`"
                                >
                                    <div class="auction-insight-item__head">
                                        <span>{{ card.label }}</span>
                                        <el-icon><component :is="card.icon" /></el-icon>
                                    </div>
                                    <div class="auction-insight-item__value">{{ card.value }}</div>
                                    <div v-if="card.tone === 'wide'" class="auction-insight-progress">
                                        <span class="auction-insight-progress__fill"></span>
                                    </div>
                                </article>
                            </div>

                            <button type="button" class="auction-report-button">Download Report</button>
                        </section>

                        <section class="auction-activity-card">
                            <div class="auction-side-title">Recent Terminal Activity</div>

                            <div class="auction-activity-list">
                                <article v-for="item in recentActivity" :key="item.title" class="auction-activity-item">
                                    <div class="auction-activity-dot"></div>
                                    <div>
                                        <div class="auction-activity-title">{{ item.title }}</div>
                                        <div class="auction-activity-detail">{{ item.detail }}</div>
                                    </div>
                                </article>
                            </div>

                            <button type="button" class="auction-plus-button" aria-label="Add terminal note">
                                <el-icon><Plus /></el-icon>
                            </button>
                        </section>

                        <section class="auction-alert-card">
                            <div class="auction-side-title auction-side-title--light">Terminal Alert</div>
                            <h3 class="auction-alert-title">Supply constraints in Brazil pushing C-price upwards.</h3>
                            <p class="auction-alert-copy">
                                Analysts suggest locking origin contracts before next week’s ICO report.
                            </p>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.auction-terminal-page {
    background: #fff;
}

.auction-terminal-shell {
    margin: 0 auto;
    max-width: 1320px;
    padding: 10px 10px 28px;
}

.auction-ticker-strip {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #ebeff3;
    border-radius: 14px;
    display: grid;
    gap: 10px 18px;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    margin-bottom: 14px;
    padding: 10px 14px;
}

.auction-ticker-item {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.auction-ticker-label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.auction-ticker-value {
    color: #191c1e;
    font-size: 13px;
    font-weight: 700;
}

.auction-ticker-delta {
    font-size: 11px;
    font-weight: 700;
}

.auction-ticker-delta.is-up {
    color: #0f9f5d;
}

.auction-ticker-delta.is-down {
    color: #dc2626;
}

.auction-ticker-delta.is-flat {
    color: #64748b;
}

.auction-layout-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr);
}

.auction-main-column,
.auction-side-column {
    min-width: 0;
}

.auction-heading-card,
.auction-board-card,
.auction-insights-card,
.auction-activity-card {
    background: #fff;
    border: 1px solid #ebeff3;
    border-radius: 16px;
}

.auction-heading-card {
    align-items: end;
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr);
    margin-bottom: 14px;
    padding: 18px;
}

.auction-heading-title {
    color: #191c1e;
    font-size: clamp(1.35rem, 1.75vw, 1.95rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin: 0 0 8px;
}

.auction-heading-copy {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
    max-width: 520px;
}

.auction-heading-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.auction-secondary-button {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    color: #475569;
    display: inline-flex;
    font-size: 12px;
    font-weight: 700;
    gap: 8px;
    height: 40px;
    justify-content: center;
    padding: 0 14px;
    text-transform: uppercase;
}

.auction-board-card {
    margin-bottom: 14px;
    overflow: hidden;
}

.auction-table-wrap {
    overflow-x: auto;
    padding: 0 16px;
}

.auction-table {
    border-collapse: collapse;
    min-width: 940px;
    width: 100%;
}

.auction-table th {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 14px 0 12px;
    text-align: left;
    text-transform: uppercase;
}

.auction-table td {
    border-top: 1px solid #eef2f7;
    color: #191c1e;
    font-size: 14px;
    padding: 16px 0;
    vertical-align: middle;
}

.auction-lot-id {
    color: #14532d;
    font-size: 14px;
    font-weight: 800;
    line-height: 1.35;
    max-width: 90px;
    word-break: break-word;
}

.auction-origin-cell {
    align-items: center;
    display: flex;
    gap: 10px;
}

.auction-flag {
    font-size: 18px;
    line-height: 1;
}

.auction-origin-country {
    font-size: 14px;
    font-weight: 700;
    line-height: 1.35;
}

.auction-origin-region,
.auction-varietal-process,
.auction-bid-unit {
    color: #94a3b8;
    font-size: 11px;
    line-height: 1.4;
    margin-top: 3px;
}

.auction-varietal-name {
    font-size: 14px;
    font-weight: 700;
    line-height: 1.35;
}

.auction-score-pill {
    align-items: center;
    background: #bdf3d4;
    border-radius: 8px;
    color: #0f6b4c;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    height: 28px;
    justify-content: center;
    min-width: 46px;
    padding: 0 10px;
}

.auction-bid-value {
    font-size: 15px;
    font-weight: 800;
}

.auction-time-value {
    align-items: center;
    color: #334155;
    display: inline-flex;
    font-size: 12px;
    font-weight: 700;
    gap: 6px;
}

.auction-time-value.is-urgent {
    color: #dc2626;
}

.auction-place-bid-button {
    align-items: center;
    background: #0f6b4c;
    border: 0;
    border-radius: 10px;
    color: #fff;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    height: 38px;
    justify-content: center;
    min-width: 92px;
    padding: 0 14px;
    text-transform: uppercase;
}

.auction-feature-card {
    overflow: hidden;
}

.auction-feature-media {
    background:
        linear-gradient(180deg, rgba(11, 17, 32, 0.16) 0%, rgba(11, 17, 32, 0.74) 100%),
        radial-gradient(circle at 75% 18%, rgba(195, 240, 158, 0.14), transparent 28%),
        linear-gradient(135deg, #1f4d2e 0%, #284227 42%, #16212e 100%);
    border-radius: 18px;
    min-height: 256px;
    padding: 18px;
    position: relative;
}

.auction-feature-media::before {
    background:
        radial-gradient(circle at 25% 25%, rgba(116, 196, 118, 0.22), transparent 18%),
        radial-gradient(circle at 72% 40%, rgba(255, 220, 137, 0.08), transparent 16%),
        repeating-linear-gradient(120deg, rgba(255, 255, 255, 0.025), rgba(255, 255, 255, 0.025) 7px, transparent 7px, transparent 16px);
    content: '';
    inset: 0;
    position: absolute;
}

.auction-feature-badge,
.auction-feature-content {
    position: relative;
    z-index: 1;
}

.auction-feature-badge {
    background: rgba(255, 237, 178, 0.16);
    border-radius: 999px;
    color: #ffe8b3;
    display: inline-flex;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    margin-bottom: 110px;
    padding: 7px 10px;
    text-transform: uppercase;
}

.auction-feature-content {
    align-items: end;
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr);
}

.auction-feature-title {
    color: #fff;
    font-size: clamp(1.6rem, 2.1vw, 2.3rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 0.95;
    margin: 0 0 10px;
    max-width: 420px;
}

.auction-feature-copy {
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    line-height: 1.6;
    margin: 0;
    max-width: 420px;
}

.auction-feature-metrics {
    display: flex;
    gap: 14px;
}

.auction-feature-metric {
    min-width: 96px;
}

.auction-feature-metric__label {
    color: rgba(255, 255, 255, 0.58);
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.1em;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.auction-feature-metric__value {
    color: #fff;
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
}

.auction-side-column {
    display: grid;
    gap: 14px;
}

.auction-insights-card,
.auction-activity-card {
    padding: 18px;
}

.auction-side-title {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 14px;
    text-transform: uppercase;
}

.auction-side-title--light {
    color: rgba(255, 255, 255, 0.66);
}

.auction-insights-grid {
    display: grid;
    gap: 10px;
}

.auction-insight-item {
    border-radius: 14px;
    padding: 14px;
}

.auction-insight-item.is-mint {
    background: #eefbf4;
}

.auction-insight-item.is-sand {
    background: #fff6ea;
}

.auction-insight-item.is-wide {
    background: #f8fafc;
}

.auction-insight-item__head {
    align-items: center;
    color: #94a3b8;
    display: flex;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    justify-content: space-between;
    letter-spacing: 0.08em;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.auction-insight-item__value {
    color: #191c1e;
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
}

.auction-insight-progress {
    background: #d9e7de;
    border-radius: 999px;
    height: 4px;
    margin-top: 12px;
    overflow: hidden;
}

.auction-insight-progress__fill {
    background: #0f6b4c;
    display: block;
    height: 100%;
    width: 85%;
}

.auction-report-button {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    color: #334155;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    height: 40px;
    justify-content: center;
    margin-top: 14px;
    padding: 0 14px;
    text-transform: uppercase;
    width: 100%;
}

.auction-activity-list {
    display: grid;
    gap: 14px;
}

.auction-activity-item {
    display: grid;
    gap: 10px;
    grid-template-columns: 8px minmax(0, 1fr);
}

.auction-activity-dot {
    background: #0f6b4c;
    border-radius: 999px;
    height: 8px;
    margin-top: 6px;
    width: 8px;
}

.auction-activity-title {
    color: #191c1e;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.5;
    margin-bottom: 4px;
}

.auction-activity-detail {
    color: #94a3b8;
    font-size: 11px;
    line-height: 1.5;
}

.auction-plus-button {
    align-items: center;
    background: #0f6b4c;
    border: 0;
    border-radius: 12px;
    color: #fff;
    display: inline-flex;
    font-size: 18px;
    height: 42px;
    justify-content: center;
    margin-top: 14px;
    width: 42px;
}

.auction-alert-card {
    background: linear-gradient(180deg, #0f6b4c 0%, #0b5139 100%);
    border-radius: 16px;
    color: #fff;
    padding: 18px;
}

.auction-alert-title {
    font-size: 1.2rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.2;
    margin: 0 0 10px;
}

.auction-alert-copy {
    color: rgba(255, 255, 255, 0.82);
    font-size: 13px;
    line-height: 1.65;
    margin: 0;
}

@media (min-width: 900px) {
    .auction-ticker-strip {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .auction-heading-card {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .auction-feature-content {
        grid-template-columns: minmax(0, 1fr) auto;
    }
}

@media (min-width: 1200px) {
    .auction-layout-grid {
        align-items: start;
        grid-template-columns: minmax(0, 1.75fr) minmax(300px, 0.78fr);
    }
}

@media (max-width: 767px) {
    .auction-terminal-shell {
        padding: 10px 8px 22px;
    }

    .auction-heading-title {
        font-size: 1.55rem;
    }

    .auction-heading-buttons {
        display: grid;
        grid-template-columns: 1fr;
    }

    .auction-feature-badge {
        margin-bottom: 72px;
    }
}
</style>
