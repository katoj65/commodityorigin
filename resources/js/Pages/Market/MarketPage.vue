<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Operation } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const region = ref('global');
const grade = ref('specialty');

const marketLots = [
    {
        id: '#ETH-23094',
        flag: '🇪🇹',
        origin: 'Yirgacheffe G1',
        estate: 'Chelchele, Gedio',
        tags: ['Heirloom', 'Washed'],
        score: '89.5',
        bid: '$5.85',
        bidMeta: '14 bids · 2h 14m left',
        region: 'global',
        grade: 'specialty',
    },
    {
        id: '#COL-45122',
        flag: '🇨🇴',
        origin: 'Huila Pink Bourbon',
        estate: 'Finca El Diviso',
        tags: ['Bourbon', 'Anaerobic'],
        score: '91.0',
        bid: '$12.40',
        bidMeta: '32 bids · 45m left',
        region: 'global',
        grade: 'specialty',
    },
    {
        id: '#KEN-88102',
        flag: '🇰🇪',
        origin: 'Nyeri SL-28',
        estate: 'Gatuyaini Factory',
        tags: ['SL28/34', 'Washed'],
        score: '88.2',
        bid: '$7.15',
        bidMeta: '0 bids · 1d 4h left',
        region: 'africa',
        grade: 'specialty',
    },
    {
        id: '#BRA-10022',
        flag: '🇧🇷',
        origin: 'Cerrado Mineiro',
        estate: 'Fazenda Rainha',
        tags: ['Catuai', 'Natural'],
        score: '84.5',
        bid: '$2.95',
        bidMeta: '5 bids · 3h 10m left',
        region: 'global',
        grade: 'premium',
    },
];

const filteredLots = computed(() =>
    marketLots.filter((lot) => {
        const matchesRegion = region.value === 'global' || lot.region === region.value;
        const matchesGrade =
            grade.value === 'all' ||
            (grade.value === 'specialty' && lot.grade === 'specialty') ||
            (grade.value === 'premium' && lot.grade === 'premium');

        return matchesRegion && matchesGrade;
    }),
);
</script>

<template>
    <AppLayout title="Market Terminal" full-width>
        <Head title="Market Terminal" />

        <div class="market-terminal-page">
            <div class="market-terminal-shell">
                <section class="market-terminal-topnav">
                    <nav class="market-terminal-tabs">
                        <a href="#" class="market-terminal-tab is-active">Markets</a>
                        <a href="#" class="market-terminal-tab">Analytics</a>
                        <a href="#" class="market-terminal-tab">Portfolio</a>
                    </nav>
                </section>

                <section class="market-heading-card">
                    <div>
                        <h1 class="market-heading-title">Market Terminal</h1>
                        <p class="market-heading-copy">
                            Real-time auction of institutional-grade specialty lots.
                        </p>
                    </div>

                    <div class="market-heading-filters">
                        <el-select v-model="region" placeholder="Region">
                            <el-option label="Region: Global" value="global" />
                            <el-option label="Africa" value="africa" />
                        </el-select>

                        <el-select v-model="grade" placeholder="Grade">
                            <el-option label="Grade: Specialty (86+)" value="specialty" />
                            <el-option label="Grade: Premium" value="premium" />
                            <el-option label="Grade: All" value="all" />
                        </el-select>

                        <button type="button" class="market-filter-icon" aria-label="Advanced filters">
                            <el-icon><Operation /></el-icon>
                        </button>
                    </div>
                </section>

                <section class="market-board-card">
                    <div class="market-table-wrap">
                        <table class="market-board-table">
                            <thead>
                                <tr>
                                    <th>Lot ID</th>
                                    <th>Origin</th>
                                    <th>Variety / Process</th>
                                    <th>SCAA Score</th>
                                    <th>Current Bid</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody v-if="filteredLots.length">
                                <tr v-for="lot in filteredLots" :key="lot.id">
                                    <td>
                                        <div class="market-lot-id">{{ lot.id }}</div>
                                    </td>
                                    <td>
                                        <div class="market-origin-cell">
                                            <span class="market-origin-flag">{{ lot.flag }}</span>
                                            <div>
                                                <div class="market-origin-name">{{ lot.origin }}</div>
                                                <div class="market-origin-estate">{{ lot.estate }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="market-tag-row">
                                            <span
                                                v-for="tag in lot.tags"
                                                :key="`${lot.id}-${tag}`"
                                                class="market-tag-chip"
                                                :class="{ 'market-tag-chip--amber': tag === 'Anaerobic' || tag === 'Natural' }"
                                            >
                                                {{ tag }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="market-score-value">{{ lot.score }}</div>
                                    </td>
                                    <td>
                                        <div class="market-bid-value">{{ lot.bid }} <span>/ lb</span></div>
                                        <div class="market-bid-meta">{{ lot.bidMeta }}</div>
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="market-bid-button">Bid</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="!filteredLots.length" class="market-empty-state">
                        <div class="market-empty-state__title">No lots in this market view</div>
                        <p class="market-empty-state__copy">
                            Try a different region or grade filter to load more auction inventory.
                        </p>
                    </div>
                </section>

                <footer class="market-terminal-footer">
                    <div class="market-terminal-footer__left">
                        <span>© 2024 Scientific Atelier Trading AG</span>
                        <span>Terms of Settlement</span>
                        <span>Quality Assurance Protocol</span>
                    </div>

                    <div class="market-terminal-footer__right">
                        <span class="market-terminal-footer__dot"></span>
                        <span>Direct Node Connected · Latency 14ms</span>
                    </div>
                </footer>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.market-terminal-page {
    background: #fff;
}

.market-terminal-shell {
    margin: 0 auto;
    max-width: 1320px;
    padding: 10px 10px 30px;
}

.market-terminal-topnav {
    margin-bottom: 16px;
}

.market-terminal-tabs {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
}

.market-terminal-tab {
    border-bottom: 2px solid transparent;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    padding: 4px 0 8px;
    text-decoration: none;
}

.market-terminal-tab.is-active {
    border-color: #14532d;
    color: #14532d;
}

.market-heading-card {
    align-items: end;
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr);
    margin-bottom: 16px;
}

.market-heading-title {
    color: #14532d;
    font-size: clamp(1.65rem, 2.15vw, 2.5rem);
    font-weight: 800;
    letter-spacing: -0.05em;
    line-height: 1;
    margin: 0 0 8px;
}

.market-heading-copy {
    color: #4b5563;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.market-heading-filters {
    display: grid;
    gap: 10px;
    grid-template-columns: minmax(0, 1fr);
}

.market-filter-icon {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    color: #475569;
    display: inline-flex;
    font-size: 15px;
    height: 42px;
    justify-content: center;
    width: 100%;
}

.market-board-card {
    background: #fff;
    border: 1px solid #e9eef3;
    border-radius: 18px;
    overflow: hidden;
}

.market-table-wrap {
    overflow-x: auto;
    padding: 0 14px;
}

.market-board-table {
    border-collapse: collapse;
    min-width: 920px;
    width: 100%;
}

.market-board-table th {
    color: #64748b;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 14px 0 12px;
    text-align: left;
    text-transform: uppercase;
}

.market-board-table td {
    border-top: 1px solid #eef2f7;
    color: #111827;
    font-size: 14px;
    padding: 16px 0;
    vertical-align: middle;
}

.market-lot-id {
    color: #14532d;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 700;
}

.market-origin-cell {
    align-items: center;
    display: flex;
    gap: 10px;
}

.market-origin-flag {
    font-size: 18px;
    line-height: 1;
}

.market-origin-name {
    color: #1f2937;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 3px;
}

.market-origin-estate {
    color: #94a3b8;
    font-size: 11px;
}

.market-tag-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.market-tag-chip {
    background: #dff8ea;
    border-radius: 6px;
    color: #157347;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 4px 7px;
    text-transform: uppercase;
}

.market-tag-chip--amber {
    background: #fff1db;
    color: #b66b0f;
}

.market-score-value {
    color: #14532d;
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
}

.market-bid-value {
    color: #1f2937;
    font-size: 1.05rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 4px;
}

.market-bid-value span,
.market-bid-meta {
    color: #94a3b8;
    font-size: 11px;
}

.market-bid-button {
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
    min-width: 68px;
    padding: 0 14px;
    text-transform: uppercase;
}

.market-empty-state {
    padding: 26px 18px;
    text-align: center;
}

.market-empty-state__title {
    color: #111827;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
}

.market-empty-state__copy {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.6;
    margin: 0;
}

.market-terminal-footer {
    align-items: center;
    color: #94a3b8;
    display: flex;
    flex-wrap: wrap;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    gap: 14px;
    justify-content: space-between;
    letter-spacing: 0.08em;
    margin-top: 14px;
    text-transform: uppercase;
}

.market-terminal-footer__left,
.market-terminal-footer__right {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
}

.market-terminal-footer__dot {
    background: #86b19a;
    border-radius: 999px;
    display: inline-block;
    height: 7px;
    width: 7px;
}

@media (min-width: 900px) {
    .market-heading-card {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .market-heading-filters {
        grid-template-columns: repeat(2, minmax(170px, 1fr)) 44px;
    }

    .market-filter-icon {
        width: 44px;
    }
}

@media (max-width: 767px) {
    .market-terminal-shell {
        padding: 10px 8px 24px;
    }

    .market-heading-title {
        font-size: 1.95rem;
    }
}
</style>
