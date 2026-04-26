<script setup>
import { ref } from 'vue';
import {
    Bell,
    ChatLineRound,
    Checked,
    Download,
    Grid,
    Histogram,
    InfoFilled,
    Promotion,
    RefreshRight,
    Search,
    ShoppingCartFull,
    TrendCharts,
    UserFilled,
    WarnTriangleFilled,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const topTabs = [
    { label: 'Intelligence', active: true },
    { label: 'Markets', active: false },
    { label: 'Supply', active: false },
    { label: 'Forecasts', active: false },
];

const sideLinks = [
    { label: 'Global News', icon: Grid, active: true },
    { label: 'Weather Risks', icon: WarnTriangleFilled, active: false },
    { label: 'Price Index', icon: TrendCharts, active: false },
    { label: 'Trade Desk', icon: Promotion, active: false },
    { label: 'Sentiment', icon: Histogram, active: false },
];

const summaryCards = [
    {
        label: 'Weather Index',
        value: '34.2',
        meta: 'Global Avg Index',
        status: 'High Risk',
        tone: 'red',
        icon: WarnTriangleFilled,
        accent: 'alert',
    },
    {
        label: 'Price Index',
        value: '$188.40',
        meta: 'Composite USD/lb',
        status: '+1.8%',
        tone: 'green',
        icon: TrendCharts,
        accent: 'price',
    },
    {
        label: 'Supply Chain',
        value: '78%',
        meta: 'Capacity Efficiency',
        status: 'Congested',
        tone: 'amber',
        icon: Promotion,
        accent: 'supply',
    },
    {
        label: 'Demand Forecast',
        value: '+4.1m',
        meta: 'Bags (60kg) est.',
        status: 'Growing',
        tone: 'green',
        icon: ShoppingCartFull,
        accent: 'demand',
    },
    {
        label: 'Quality Pulse',
        value: '84.2',
        meta: 'SCA Avg Score',
        status: 'Stable',
        tone: 'green',
        icon: Checked,
        accent: 'quality',
    },
];

const movingMarkets = [
    { label: 'UAE', detail: '↑ 12% Demand', tone: 'green' },
    { label: 'Vietnam', detail: '↓ 5% Harvest', tone: 'red' },
    { label: 'Germany', detail: '↑ Specialty', tone: 'green' },
    { label: 'Robusta', detail: '🔥 High Volatility', tone: 'red' },
    { label: 'Logistics', detail: '↔ Ocean Freight Stagnant', tone: 'neutral' },
];

const feedCards = [
    {
        category: 'Climate',
        sentiment: 'Negative',
        tone: 'red',
        title: 'La Nina Intensifies: Heavy Rains Threaten Vietnam Harvest Window',
        body: 'Continuous rainfall in the Central Highlands is slowing cherry drying and compressing export-grade availability.',
        footerTitle: 'Why It Matters',
        footerBody: 'Potential 15% reduction in exportable grade Robusta for Q1.',
        impact: 'Impact 9/10',
        time: '14m ago',
    },
    {
        category: 'Trade',
        sentiment: 'Positive',
        tone: 'green',
        title: 'New Port Agreement in Kenya to Reduce Export Lead Times by 20%',
        body: 'Public-private partnership at Mombasa Port aims to streamline handling for premium East African lots.',
        footerTitle: 'Why It Matters',
        footerBody: 'Faster speed-to-market for premium Kenyan AA lots.',
        impact: 'Impact 6/10',
        time: '2h ago',
    },
    {
        category: 'Price',
        sentiment: 'Neutral',
        tone: 'slate',
        title: "Global Stockpiles Stabilize as Roasters Move to 'Just-in-Time' Model",
        body: 'Warehousing reports in Hamburg and NYC show stable inventory behavior despite intra-month volatility.',
        footerTitle: 'Why It Matters',
        footerBody: 'Short-term price stabilization expected across Arabica futures.',
        impact: 'Impact 4/10',
        time: '4h ago',
    },
    {
        category: 'Tech',
        sentiment: 'Positive',
        tone: 'green',
        title: 'Satellite Data Confirms Successful Replanting in South Ethiopia',
        body: 'High-resolution imaging shows 85% survival rate for new disease-resistant planting clusters.',
        footerTitle: 'Why It Matters',
        footerBody: 'Ensures quality consistency for 2026-2027 harvest seasons.',
        impact: 'Impact 7/10',
        time: '6h ago',
    },
];

const matrixRows = [
    { market: 'UAE', change: '+14%', status: 'High Growth', tone: 'green' },
    { market: 'USA', change: '+1%', status: 'Saturated', tone: 'slate' },
    { market: 'DE', change: '+6%', status: 'Specialty Shift', tone: 'amber' },
];

const advisorPrompts = [
    'Summarize Brazil drought impact',
    'Show Robusta price trends',
    'Identify supply chain risks',
];

const smartAlerts = [
    { label: 'Price Volatility (>5%)', note: 'Instant push notification', active: true },
    { label: 'Climate Risk Alerts', note: 'Origin-specific monitoring', active: true },
    { label: 'Competitor Insights', note: 'Weekly aggregated report', active: false },
];

const weatherCards = [
    { country: 'Brazil', status: 'Extreme', title: 'Drought Risk', tone: 'red' },
    { country: 'Vietnam', status: 'Moderate', title: 'Flood Warning', tone: 'amber' },
    { country: 'Uganda', status: 'Low', title: 'Ideal Conditions', tone: 'green' },
    { country: 'Colombia', status: 'Low', title: 'Stable Patterns', tone: 'green' },
];

const parityBars = ['22%', '26%', '20%', '34%', '31%', '38%', '46%', '58%'];

const advisorInput = ref('');

function fillAdvisorPrompt(prompt) {
    advisorInput.value = prompt;
}

function sendAdvisorMessage() {
    advisorInput.value = '';
}
</script>

<template>
    <OuterLayout title="Market Intelligence">
        <section class="mi-page">
            <div class="mi-frame">


                <div class="mi-layout">
                    <aside class="mi-sidebar">
                        <div class="mi-sidebar-top">
                            <h2>Intelligence Hub</h2>
                            <span>Market Pulse</span>
                        </div>

                        <nav class="mi-side-nav">
                            <a
                                v-for="item in sideLinks"
                                :key="item.label"
                                href="#"
                                class="mi-side-link"
                                :class="{ 'is-active': item.active }"
                            >
                                <component :is="item.icon" />
                                <span>{{ item.label }}</span>
                            </a>
                        </nav>

                        <div class="mi-sidebar-bottom">
                            <button type="button" class="mi-ai-btn">
                                <ChatLineRound />
                                <span>Ask AI Advisor</span>
                            </button>
                        </div>
                    </aside>

                    <main class="mi-main">
                        <section class="mi-toolbar">
                            <div>
                                <h1 class="mi-title">Coffee News &amp; Market Intelligence</h1>
                            </div>

                            <div class="mi-toolbar-actions">
                                <button type="button" class="mi-select-btn">
                                    <span>Global Regions</span>
                                    <span>⌄</span>
                                </button>
                                <button type="button" class="mi-select-btn">
                                    <span>All Types</span>
                                    <span>⌄</span>
                                </button>
                                <span class="mi-toolbar-divider"></span>
                                <button type="button" class="mi-tool-btn">
                                    <Bell />
                                    <span>Set Alerts</span>
                                </button>
                                <button type="button" class="mi-tool-btn">
                                    <Download />
                                    <span>Export</span>
                                </button>
                            </div>
                        </section>

                        <section class="mi-banner-row">
                            <article class="mi-ai-summary">
                                <div class="mi-ai-summary-badge">
                                    <span>AI</span>
                                    <strong>Summary</strong>
                                </div>
                                <p>
                                    Market sentiment is <strong>Bullish</strong> for Arabica following crop reports from Minas Gerais.
                                    Robusta supply chains offsetting logistics delays in the Red Sea.
                                </p>
                                <div class="mi-ai-summary-status">
                                    <i></i>
                                    <span>Market</span>
                                    <strong>Neutral/Positive</strong>
                                </div>
                            </article>

                            <article class="mi-breaking">
                                <span class="mi-breaking-label">
                                    <i></i>
                                    Breaking:
                                </span>
                                <p>Brazil rainfall significantly below 5-year average in key producing regions; Robusta prices spike +4.2% in London.</p>
                            </article>
                        </section>

                        <section class="mi-metric-grid">
                            <article v-for="card in summaryCards" :key="card.label" class="mi-card mi-metric-card">
                                <div class="mi-metric-top">
                                    <div>
                                        <span class="mi-kicker">{{ card.label }}</span>
                                        <div class="mi-metric-status" :class="`is-${card.tone}`">{{ card.status }}</div>
                                    </div>
                                    <component :is="card.icon" class="mi-metric-icon" :class="`is-${card.tone}`" />
                                </div>
                                <div class="mi-metric-value">{{ card.value }}</div>
                                <div class="mi-metric-meta">{{ card.meta }}</div>
                                <div v-if="card.accent === 'alert'" class="mi-alert-bars">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </article>
                        </section>

                        <section class="mi-moving-row">
                            <span class="mi-moving-label">Moving Markets:</span>
                            <div class="mi-chip-row">
                                <span v-for="item in movingMarkets" :key="item.label" class="mi-chip" :class="`is-${item.tone}`">
                                    <strong>{{ item.label }}</strong>
                                    <span>{{ item.detail }}</span>
                                </span>
                            </div>
                        </section>

                        <section class="mi-feed-section">
                            <div class="mi-feed-header">
                                <h2>Main Intelligence Feed</h2>
                                <div class="mi-feed-actions">
                                    <button type="button" class="mi-view-btn is-active">
                                        <Grid />
                                    </button>
                                    <button type="button" class="mi-view-btn">
                                        <Histogram />
                                    </button>
                                </div>
                            </div>

                            <div class="mi-feed-grid">
                                <div class="mi-feed-main">
                                    <div class="mi-story-grid">
                                        <article
                                            v-for="card in feedCards"
                                            :key="card.title"
                                            class="mi-card mi-story-card"
                                        >
                                            <div class="mi-story-top">
                                                <span class="mi-story-tag">{{ card.category }}</span>
                                                <span class="mi-story-tone" :class="`is-${card.tone}`">
                                                    <i></i>
                                                    {{ card.sentiment }}
                                                </span>
                                            </div>

                                            <h3>{{ card.title }}</h3>
                                            <p class="mi-story-body">{{ card.body }}</p>

                                            <div class="mi-story-footer-box">
                                                <div>
                                                    <span class="mi-story-footer-kicker">{{ card.footerTitle }}</span>
                                                    <p>{{ card.footerBody }}</p>
                                                </div>
                                                <strong>{{ card.impact }}</strong>
                                            </div>

                                            <div class="mi-story-meta">
                                                <span>{{ card.time }}</span>
                                                <div class="mi-story-icons">
                                                    <Checked />
                                                    <Promotion />
                                                </div>
                                            </div>
                                        </article>

                                        <article class="mi-card mi-loading-card">
                                            <RefreshRight class="mi-spin-icon" />
                                            <span>Loading Market Updates...</span>
                                        </article>

                                        <article class="mi-card mi-loading-card">
                                            <RefreshRight class="mi-spin-icon" />
                                            <span>Scanning Trade Reports...</span>
                                        </article>
                                    </div>

                                    <div class="mi-lower-panels">
                                        <section class="mi-panel-block">
                                            <h3>Weather Intelligence</h3>
                                            <div class="mi-weather-grid">
                                                <article
                                                    v-for="item in weatherCards"
                                                    :key="item.country"
                                                    class="mi-card mi-weather-card"
                                                    :class="`is-${item.tone}`"
                                                >
                                                    <div class="mi-weather-top">
                                                        <span>{{ item.country }}</span>
                                                        <strong>{{ item.status }}</strong>
                                                    </div>
                                                    <div class="mi-weather-title">{{ item.title }}</div>
                                                </article>
                                            </div>
                                        </section>

                                        <section class="mi-panel-block">
                                            <h3>Arabica/Robusta Parity</h3>
                                            <article class="mi-card mi-parity-card">
                                                <div class="mi-parity-top">
                                                    <div>
                                                        <span>Spread Gap</span>
                                                        <strong>82.40 pts</strong>
                                                    </div>
                                                    <div class="mi-parity-trend">
                                                        <span>24H Trend</span>
                                                        <strong>Widening</strong>
                                                    </div>
                                                </div>

                                                <div class="mi-parity-chart">
                                                    <div
                                                        v-for="(bar, index) in parityBars"
                                                        :key="index"
                                                        class="mi-parity-bar"
                                                        :class="{ 'is-active': index === parityBars.length - 1 }"
                                                        :style="{ height: bar }"
                                                    ></div>
                                                </div>
                                            </article>
                                        </section>
                                    </div>
                                </div>

                                <aside class="mi-rail">
                                    <article class="mi-card mi-demand-card">
                                        <div class="mi-rail-heading">
                                            <h3>Demand Matrix</h3>
                                        </div>
                                        <div class="mi-demand-list">
                                            <div v-for="row in matrixRows" :key="row.market" class="mi-demand-row">
                                                <div>
                                                    <strong>{{ row.market }}</strong>
                                                    <span>{{ row.change }}</span>
                                                </div>
                                                <em :class="`is-${row.tone}`">{{ row.status }}</em>
                                            </div>
                                        </div>
                                    </article>

                                    <article class="mi-card mi-advisor-card">
                                        <div class="mi-advisor-card-head">
                                            <div class="mi-advisor-card-title">
                                                <div class="mi-advisor-avatar">
                                                    <ChatLineRound />
                                                </div>
                                                <div>
                                                    <strong>News Advisor</strong>
                                                    <span>Online</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mi-advisor-prompts">
                                            <button
                                                v-for="prompt in advisorPrompts"
                                                :key="prompt"
                                                type="button"
                                                class="mi-advisor-prompt"
                                                @click="fillAdvisorPrompt(prompt)"
                                            >
                                                "{{ prompt }}"
                                            </button>
                                        </div>

                                        <div class="mi-advisor-input">
                                            <input
                                                v-model="advisorInput"
                                                type="text"
                                                placeholder="Ask Bean Origin AI..."
                                                @keydown.enter.prevent="sendAdvisorMessage"
                                            />
                                            <button type="button" @click="sendAdvisorMessage">
                                                <Promotion />
                                            </button>
                                        </div>
                                    </article>

                                    <article class="mi-card mi-alerts-card">
                                        <div class="mi-rail-heading">
                                            <h3>Smart Alerts</h3>
                                            <InfoFilled />
                                        </div>

                                        <div class="mi-alerts-list">
                                            <div v-for="alert in smartAlerts" :key="alert.label" class="mi-alert-line">
                                                <div>
                                                    <strong>{{ alert.label }}</strong>
                                                    <span>{{ alert.note }}</span>
                                                </div>
                                                <button type="button" class="mi-toggle" :class="{ 'is-active': alert.active }">
                                                    <span></span>
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                </aside>
                            </div>
                        </section>

                    </main>
                </div>
            </div>

            <button type="button" class="mi-floating-chat">
                <ChatLineRound />
            </button>
        </section>
    </OuterLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.mi-page {
    background: #fff;
    color: #111827;
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
}

.mi-frame {
    margin: 0 auto;
    max-width: 1320px;
}

.mi-header {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #e8edf2;
    display: flex;
    justify-content: space-between;
    min-height: 72px;
    padding: 0 1.5rem;
}

.mi-brand-row {
    align-items: center;
    display: flex;
    gap: 2rem;
}

.mi-brand {
    color: #0d5a41;
    font-size: 1rem;
    font-weight: 700;
}

.mi-top-nav {
    display: flex;
    gap: 1.5rem;
}

.mi-top-link {
    border-bottom: 2px solid transparent;
    color: #526277;
    font-size: 0.95rem;
    padding: 1.55rem 0;
    text-decoration: none;
}

.mi-top-link.is-active {
    border-bottom-color: #0d5a41;
    color: #0d5a41;
    font-weight: 600;
}

.mi-header-tools {
    align-items: center;
    display: flex;
    gap: 0.7rem;
}

.mi-search {
    align-items: center;
    background: #f5f7fa;
    border: 1px solid #eef2f5;
    border-radius: 0.55rem;
    color: #9aa5b1;
    display: inline-flex;
    gap: 0.6rem;
    min-width: 300px;
    padding: 0.78rem 0.95rem;
}

.mi-search :deep(svg),
.mi-circle-btn :deep(svg),
.mi-side-link :deep(svg),
.mi-tool-btn :deep(svg),
.mi-floating-chat :deep(svg),
.mi-story-icons :deep(svg),
.mi-advisor-avatar :deep(svg),
.mi-metric-icon {
    height: 1rem;
    width: 1rem;
}

.mi-circle-btn {
    align-items: center;
    background: transparent;
    border: 0;
    color: #6b7280;
    display: inline-flex;
    height: 2.2rem;
    justify-content: center;
    width: 2.2rem;
}

.mi-layout {
    display: grid;
    grid-template-columns: 264px minmax(0, 1fr);
}

.mi-sidebar {
    background: #f7f9fc;
    border-right: 1px solid #e8edf2;
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 72px);
    padding: 0.85rem 1rem 1rem;
}

.mi-sidebar-top {
    margin-bottom: 1.6rem;
    padding: 0.45rem 0.4rem;
}

.mi-sidebar-top h2 {
    color: #102131;
    font-size: 0.98rem;
    font-weight: 600;
    margin: 0 0 0.2rem;
}

.mi-sidebar-top span {
    color: #9aa5b1;
    font-size: 0.72rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.mi-side-nav {
    display: grid;
    gap: 0.45rem;
}

.mi-side-link {
    align-items: center;
    border-radius: 0.65rem;
    color: #334155;
    display: flex;
    gap: 0.85rem;
    min-height: 42px;
    padding: 0.85rem 0.95rem;
    position: relative;
    text-decoration: none;
}

.mi-side-link.is-active {
    background: #eefaf4;
    color: #0d5a41;
    font-weight: 600;
}

.mi-side-link.is-active::after {
    background: #0d5a41;
    border-radius: 999px;
    content: '';
    height: 72%;
    position: absolute;
    right: 0;
    top: 14%;
    width: 3px;
}

.mi-sidebar-bottom {
    margin-top: auto;
    padding: 1rem 0.2rem 0;
}

.mi-ai-btn {
    align-items: center;
    background: #0d5a41;
    border: 0;
    border-radius: 0.75rem;
    color: #fff;
    display: inline-flex;
    font-size: 0.95rem;
    font-weight: 600;
    gap: 0.6rem;
    justify-content: center;
    padding: 1rem 1.1rem;
    width: 100%;
}

.mi-main {
    background: #fff;
    padding: 2rem 1.9rem 1.7rem;
}

.mi-toolbar {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.35rem;
}

.mi-title {
    color: #101828;
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0;
}

.mi-toolbar-actions {
    align-items: center;
    display: flex;
    gap: 0.7rem;
}

.mi-select-btn,
.mi-tool-btn {
    align-items: center;
    background: #fff;
    border: 1px solid #e6ebf0;
    border-radius: 0.5rem;
    color: #344054;
    display: inline-flex;
    font-size: 0.9rem;
    gap: 0.55rem;
    min-height: 40px;
    padding: 0 0.9rem;
}

.mi-select-btn {
    min-width: 140px;
    justify-content: space-between;
}

.mi-toolbar-divider {
    background: #e0e7ef;
    height: 26px;
    width: 1px;
}

.mi-banner-row {
    display: grid;
    gap: 0.8rem;
    margin-bottom: 1.7rem;
}

.mi-ai-summary,
.mi-breaking,
.mi-card {
    border: 1px solid #e8edf2;
    border-radius: 0.85rem;
    background: #fff;
}

.mi-ai-summary {
    align-items: center;
    background: #f8fbf8;
    display: grid;
    gap: 0.85rem;
    grid-template-columns: auto 1fr auto;
    padding: 0.7rem 0.9rem;
}

.mi-ai-summary-badge {
    background: #0d4a36;
    border-radius: 0.4rem;
    color: #fff;
    display: grid;
    min-width: 72px;
    padding: 0.45rem 0.55rem;
}

.mi-ai-summary-badge span,
.mi-ai-summary-badge strong {
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1.15;
    text-transform: uppercase;
}

.mi-ai-summary p {
    color: #334155;
    font-size: 0.9rem;
    line-height: 1.45;
    margin: 0;
}

.mi-ai-summary-status {
    align-items: center;
    color: #0f172a;
    display: flex;
    gap: 0.5rem;
    justify-self: end;
}

.mi-ai-summary-status i {
    background: #0d5a41;
    border-radius: 999px;
    display: inline-block;
    height: 8px;
    width: 8px;
}

.mi-ai-summary-status span,
.mi-ai-summary-status strong {
    font-size: 0.75rem;
    text-transform: uppercase;
}

.mi-ai-summary-status span {
    color: #5c6877;
}

.mi-ai-summary-status strong {
    font-weight: 700;
}

.mi-breaking {
    align-items: center;
    background: #fff1f1;
    border-color: #ffd5d5;
    color: #b42318;
    display: flex;
    gap: 0.9rem;
    padding: 0.82rem 1rem;
}

.mi-breaking-label {
    align-items: center;
    display: inline-flex;
    font-size: 0.78rem;
    font-weight: 700;
    gap: 0.5rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    white-space: nowrap;
}

.mi-breaking-label i {
    background: #b42318;
    border-radius: 999px;
    display: inline-block;
    height: 10px;
    width: 10px;
}

.mi-breaking p {
    font-size: 0.9rem;
    line-height: 1.45;
    margin: 0;
}

.mi-metric-grid {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-bottom: 1.35rem;
}

.mi-metric-card {
    min-height: 124px;
    padding: 1rem 1rem 0.95rem;
}

.mi-metric-top {
    align-items: start;
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.9rem;
}

.mi-kicker {
    color: #677689;
    display: block;
    font-size: 0.74rem;
    letter-spacing: 0.12em;
    margin-bottom: 0.15rem;
    text-transform: uppercase;
}

.mi-metric-status {
    font-size: 0.72rem;
    font-weight: 600;
}

.mi-metric-status.is-red {
    color: #c81e1e;
}

.mi-metric-status.is-green {
    color: #0d5a41;
}

.mi-metric-status.is-amber {
    color: #8b5e25;
}

.mi-metric-icon {
    color: #0d5a41;
    flex: 0 0 auto;
}

.mi-metric-icon.is-red {
    color: #c81e1e;
}

.mi-metric-icon.is-amber {
    color: #8b5e25;
}

.mi-metric-value {
    color: #0f172a;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.mi-metric-meta {
    color: #7b8794;
    font-size: 0.8rem;
}

.mi-alert-bars {
    align-items: end;
    display: flex;
    gap: 0.18rem;
    justify-content: end;
    margin-top: -1rem;
}

.mi-alert-bars span {
    background: #0d5a41;
    border-radius: 2px 2px 0 0;
    display: inline-block;
    width: 12px;
}

.mi-alert-bars span:nth-child(1) {
    background: #0d5a41;
    height: 14px;
}

.mi-alert-bars span:nth-child(2) {
    background: #345b4f;
    height: 18px;
}

.mi-alert-bars span:nth-child(3) {
    background: #8d6d28;
    height: 28px;
}

.mi-alert-bars span:nth-child(4) {
    background: #c81e1e;
    height: 28px;
}

.mi-moving-row {
    align-items: center;
    display: flex;
    gap: 0.95rem;
    margin-bottom: 1.8rem;
}

.mi-moving-label {
    color: #5e6b78;
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    white-space: nowrap;
}

.mi-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.mi-chip {
    align-items: center;
    background: #f4f6f8;
    border-radius: 999px;
    color: #4b5563;
    display: inline-flex;
    font-size: 0.76rem;
    gap: 0.35rem;
    padding: 0.5rem 0.8rem;
}

.mi-chip strong {
    color: #111827;
    font-weight: 600;
}

.mi-chip.is-green span {
    color: #0d5a41;
}

.mi-chip.is-red span {
    color: #c81e1e;
}

.mi-feed-header {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.mi-feed-header h2 {
    color: #111827;
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0;
}

.mi-feed-actions {
    display: flex;
    gap: 0.45rem;
}

.mi-view-btn {
    align-items: center;
    background: transparent;
    border: 0;
    color: #7b8794;
    display: inline-flex;
    height: 2rem;
    justify-content: center;
    width: 2rem;
}

.mi-view-btn.is-active {
    color: #111827;
}

.mi-feed-grid {
    display: grid;
    gap: 1.1rem;
    grid-template-columns: minmax(0, 1fr) 256px;
}

.mi-feed-main {
    min-width: 0;
}

.mi-story-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 1.4rem;
}

.mi-story-card,
.mi-loading-card,
.mi-demand-card,
.mi-advisor-card,
.mi-alerts-card,
.mi-parity-card,
.mi-weather-card {
    padding: 1rem;
}

.mi-story-card {
    min-height: 304px;
}

.mi-story-top {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.mi-story-tag {
    background: #f3f4f6;
    border-radius: 0.3rem;
    color: #4b5563;
    font-size: 0.72rem;
    padding: 0.35rem 0.6rem;
}

.mi-story-tone {
    align-items: center;
    display: inline-flex;
    font-size: 0.72rem;
    font-weight: 600;
    gap: 0.35rem;
    text-transform: uppercase;
}

.mi-story-tone i {
    border-radius: 999px;
    display: inline-block;
    height: 6px;
    width: 6px;
}

.mi-story-tone.is-red {
    color: #c81e1e;
}

.mi-story-tone.is-red i {
    background: #c81e1e;
}

.mi-story-tone.is-green {
    color: #0d5a41;
}

.mi-story-tone.is-green i {
    background: #0d5a41;
}

.mi-story-tone.is-slate {
    color: #6b7280;
}

.mi-story-tone.is-slate i {
    background: #6b7280;
}

.mi-story-card h3 {
    color: #111827;
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.35;
    margin: 0 0 0.75rem;
}

.mi-story-body {
    color: #667085;
    font-size: 0.85rem;
    line-height: 1.5;
    margin: 0 0 1rem;
}

.mi-story-footer-box {
    background: #f8fafb;
    border-radius: 0.6rem;
    display: grid;
    gap: 0.5rem;
    grid-template-columns: 1fr auto;
    margin-bottom: 0.95rem;
    padding: 0.75rem 0.8rem;
}

.mi-story-footer-kicker {
    color: #6b7280;
    display: block;
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    margin-bottom: 0.35rem;
    text-transform: uppercase;
}

.mi-story-footer-box p {
    color: #5b6572;
    font-size: 0.78rem;
    line-height: 1.45;
    margin: 0;
}

.mi-story-footer-box strong {
    color: #111827;
    font-size: 0.78rem;
    font-weight: 600;
}

.mi-story-meta {
    align-items: center;
    color: #8b95a1;
    display: flex;
    font-size: 0.76rem;
    justify-content: space-between;
    margin-top: auto;
}

.mi-story-icons {
    color: #0d5a41;
    display: flex;
    gap: 0.7rem;
}

.mi-loading-card {
    align-items: center;
    border-style: dashed;
    color: #8b95a1;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    justify-content: center;
    min-height: 304px;
}

.mi-spin-icon {
    color: #98a2b3;
    height: 1.4rem;
    width: 1.4rem;
}

.mi-lower-panels {
    display: grid;
    gap: 1.4rem;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
}

.mi-panel-block h3,
.mi-rail-heading h3 {
    color: #5e6b78;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin: 0 0 0.9rem;
    text-transform: uppercase;
}

.mi-weather-grid {
    display: grid;
    gap: 0.65rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.mi-weather-card {
    background: #f8fafb;
}

.mi-weather-top {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.9rem;
}

.mi-weather-top span {
    color: #111827;
    font-size: 0.86rem;
    font-weight: 500;
}

.mi-weather-top strong {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
}

.mi-weather-card.is-red .mi-weather-top strong {
    color: #c81e1e;
}

.mi-weather-card.is-amber .mi-weather-top strong {
    color: #8b5e25;
}

.mi-weather-card.is-green .mi-weather-top strong {
    color: #0d5a41;
}

.mi-weather-title {
    color: #111827;
    font-size: 0.8rem;
    font-weight: 600;
}

.mi-parity-card {
    min-height: 206px;
}

.mi-parity-top {
    align-items: start;
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.mi-parity-top span {
    color: #667085;
    display: block;
    font-size: 0.82rem;
}

.mi-parity-top strong {
    color: #0d5a41;
    display: block;
    font-size: 0.92rem;
    font-weight: 700;
    margin-top: 0.2rem;
}

.mi-parity-trend {
    text-align: right;
}

.mi-parity-trend strong {
    color: #c81e1e;
}

.mi-parity-chart {
    align-items: end;
    display: grid;
    gap: 0.35rem;
    grid-template-columns: repeat(8, minmax(0, 1fr));
    height: 116px;
}

.mi-parity-bar {
    background: #c9d8d3;
    border-radius: 0.2rem 0.2rem 0 0;
}

.mi-parity-bar.is-active {
    background: #0d5a41;
}

.mi-rail {
    display: grid;
    gap: 1rem;
    align-content: start;
}

.mi-rail-heading {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.mi-rail-heading :deep(svg) {
    color: #5f6c79;
    height: 0.95rem;
    width: 0.95rem;
}

.mi-demand-list {
    display: grid;
    gap: 0.7rem;
}

.mi-demand-row {
    align-items: center;
    background: #f8fafb;
    border-radius: 0.55rem;
    display: flex;
    justify-content: space-between;
    padding: 0.78rem 0.8rem;
}

.mi-demand-row strong {
    color: #111827;
    display: block;
    font-size: 0.86rem;
    font-weight: 600;
}

.mi-demand-row span {
    color: #667085;
    font-size: 0.78rem;
}

.mi-demand-row em {
    border-radius: 0.4rem;
    font-size: 0.74rem;
    font-style: normal;
    padding: 0.3rem 0.55rem;
}

.mi-demand-row em.is-green {
    background: #dff7ea;
    color: #0d5a41;
}

.mi-demand-row em.is-slate {
    background: #eef2f5;
    color: #526277;
}

.mi-demand-row em.is-amber {
    background: #fff1df;
    color: #8b5e25;
}

.mi-advisor-card-head {
    margin-bottom: 0.9rem;
}

.mi-advisor-card-title {
    align-items: center;
    display: flex;
    gap: 0.7rem;
}

.mi-advisor-avatar {
    align-items: center;
    background: #0d5a41;
    border-radius: 999px;
    color: #fff;
    display: inline-flex;
    height: 2rem;
    justify-content: center;
    width: 2rem;
}

.mi-advisor-card-title strong {
    color: #111827;
    display: block;
    font-size: 0.92rem;
    font-weight: 600;
}

.mi-advisor-card-title span {
    color: #0d5a41;
    font-size: 0.76rem;
}

.mi-advisor-prompts {
    display: grid;
    gap: 0.55rem;
    margin-bottom: 0.8rem;
}

.mi-advisor-prompt {
    background: #f3f5f7;
    border: 0;
    border-radius: 0.45rem;
    color: #344054;
    font-size: 0.8rem;
    padding: 0.75rem 0.8rem;
    text-align: left;
}

.mi-advisor-input {
    align-items: center;
    background: #fff;
    border: 1px solid #e5ebf0;
    border-radius: 0.6rem;
    display: flex;
    gap: 0.55rem;
    padding: 0.45rem 0.5rem 0.45rem 0.75rem;
}

.mi-advisor-input input {
    background: transparent;
    border: 0;
    color: #111827;
    flex: 1;
    font-size: 0.86rem;
    outline: none;
}

.mi-advisor-input button {
    align-items: center;
    background: #0d5a41;
    border: 0;
    border-radius: 0.45rem;
    color: #fff;
    display: inline-flex;
    height: 2rem;
    justify-content: center;
    width: 2rem;
}

.mi-alerts-list {
    display: grid;
    gap: 1rem;
}

.mi-alert-line {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

.mi-alert-line strong {
    color: #111827;
    display: block;
    font-size: 0.86rem;
    font-weight: 600;
    margin-bottom: 0.15rem;
}

.mi-alert-line span {
    color: #8b95a1;
    font-size: 0.76rem;
}

.mi-toggle {
    background: #d5dce3;
    border: 0;
    border-radius: 999px;
    height: 20px;
    position: relative;
    width: 38px;
}

.mi-toggle span {
    background: #fff;
    border-radius: 999px;
    display: block;
    height: 16px;
    left: 2px;
    position: absolute;
    top: 2px;
    width: 16px;
}

.mi-toggle.is-active {
    background: #0d5a41;
}

.mi-toggle.is-active span {
    left: 20px;
}

.mi-footer {
    align-items: center;
    color: #98a2b3;
    display: flex;
    font-size: 0.78rem;
    justify-content: space-between;
    margin-top: 1.6rem;
    padding-top: 1rem;
}

.mi-footer-links {
    display: flex;
    gap: 1.8rem;
}

.mi-footer a {
    color: #94a3b8;
    text-decoration: none;
    text-transform: uppercase;
}

.mi-floating-chat {
    align-items: center;
    background: #0d5a41;
    border: 0;
    border-radius: 0.85rem;
    bottom: 1.25rem;
    box-shadow: 0 14px 34px rgba(13, 90, 65, 0.24);
    color: #fff;
    display: inline-flex;
    height: 3.5rem;
    justify-content: center;
    position: fixed;
    right: 1.25rem;
    width: 3.5rem;
    z-index: 30;
}

@media (max-width: 1199.98px) {
    .mi-layout,
    .mi-feed-grid,
    .mi-lower-panels {
        grid-template-columns: 1fr;
    }

    .mi-sidebar {
        min-height: auto;
    }

    .mi-metric-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .mi-story-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .mi-header,
    .mi-toolbar,
    .mi-ai-summary,
    .mi-footer {
        align-items: start;
        grid-template-columns: 1fr;
        flex-direction: column;
    }

    .mi-header {
        gap: 1rem;
        padding-block: 1rem;
    }

    .mi-brand-row {
        align-items: start;
        flex-direction: column;
        gap: 1rem;
    }

    .mi-header-tools,
    .mi-toolbar-actions {
        flex-wrap: wrap;
    }

    .mi-search {
        min-width: 100%;
    }

    .mi-metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mi-story-grid,
    .mi-weather-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .mi-frame {
        width: 100%;
    }

    .mi-main,
    .mi-header {
        padding-inline: 1rem;
    }

    .mi-sidebar {
        padding-inline: 0.85rem;
    }

    .mi-metric-grid,
    .mi-lower-panels {
        grid-template-columns: 1fr;
    }

    .mi-moving-row {
        align-items: start;
        flex-direction: column;
    }

    .mi-footer-links {
        flex-wrap: wrap;
        gap: 0.9rem 1.2rem;
    }
}
</style>
