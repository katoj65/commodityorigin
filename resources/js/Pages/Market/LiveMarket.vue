<script setup>
import { Link } from '@inertiajs/vue3';
import {
    Bell,
    ChatLineRound,
    Checked,
    Connection,
    DataAnalysis,
    Document,
    Grid,
    InfoFilled,
    List,
    Money,
    Opportunity,
    Search,
    Setting,
    ShoppingCartFull,
    ShoppingTrolley,
    TrendCharts,
    UserFilled,
    WarnTriangleFilled,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const sideLinks = [
    { label: 'Terminal', icon: Grid, active: true, href: '/live-market' },
    { label: 'Portfolio', icon: Document, active: false, href: '/origins' },
    { label: 'Alerts', icon: Bell, active: false, href: '/news' },
];

const tickerItems = [
    { name: 'Brazil Arabica', price: '$224.15', change: '▼ 0.8%', tone: 'red' },
    { name: 'Vietnam Robusta', price: '$168.90', change: '▲ 1.2%', tone: 'green' },
    { name: 'Ethiopia Sidamo', price: '$294.00', change: '▲ 4.1%', tone: 'green' },
    { name: 'Colombia Excelso', price: '$245.50', change: '▼ 1.5%', tone: 'red' },
    { name: 'Uganda Robusta', price: '$184.20', change: '▲ 3.2%', tone: 'green' },
];

const topStats = [
    { label: 'Market Status', value: 'Bullish', tone: 'green', helper: '↗' },
    { label: 'Avg Price (MT)', value: '$2,412.00', tone: 'default' },
    { label: '24H Volume', value: '14.2k Lots', tone: 'default' },
    { label: 'Volatility', value: 'Low (2.4%)', tone: 'red' },
    { label: 'Top Active Origin', value: 'Robusta G1', tone: 'default', badge: 'Uganda' },
    { label: 'Highest Demand', value: 'UAE (Dubai)', tone: 'default' },
];

const watchlist = [
    { code: 'UG-ROB-G1', name: 'Uganda Robusta', price: '$1,840', change: '+3.2%', tone: 'green' },
    { code: 'BR-ARA-NY', name: 'Brazil Arabica', price: '$2,210', change: '-0.4%', tone: 'red' },
    { code: 'VN-ROB-13', name: 'Vietnam Screen 13', price: '$1,695', change: '+1.8%', tone: 'green' },
];

const gainers = [
    { rank: '01', name: 'Ethiopia Yirgacheffe', gain: '+8.4%', width: '78%' },
    { rank: '02', name: 'Kenya AA Plus', gain: '+6.1%', width: '58%' },
];

const alertCards = [
    {
        title: 'Weather Alert: Brazil',
        body: 'Frost predicted in Minas Gerais region.',
        tone: 'red',
        icon: WarnTriangleFilled,
    },
    {
        title: 'Policy Update: EUDR',
        body: 'New deforestation compliance timeline.',
        tone: 'amber',
        icon: InfoFilled,
    },
];

const chartBars = [
    { height: '36%', tone: 'soft' },
    { height: '51%', tone: 'soft' },
    { height: '41%', tone: 'red' },
    { height: '56%', tone: 'soft' },
    { height: '72%', tone: 'deep' },
    { height: '61%', tone: 'deep' },
    { height: '82%', tone: 'dark' },
];

const lotsFeed = [
    { code: 'UG', name: 'Rwenzori Natural', score: '86.5', price: '$2,840', change: '+1.2%', tone: 'green' },
    { code: 'BR', name: 'Santos Screen 17', score: '82.0', price: '$2,115', change: '-0.5%', tone: 'red' },
    { code: 'VN', name: 'Lam Dong Robusta', score: '78.5', price: '$1,640', change: '+0.8%', tone: 'green' },
];

const corridorRows = [
    { route: 'UG → UAE', state: 'Opportunity', tone: 'green' },
    { route: 'BR → DE', state: 'Stable', tone: 'slate' },
];

const executionRows = [
    { side: 'BUY', contract: 'UG-ROB-G1', volume: '14.5 MT', total: '$26,680.00', time: '12:04:15', tone: 'green' },
    { side: 'SELL', contract: 'BR-ARA-NY', volume: '5.0 MT', total: '$11,050.00', time: '12:03:50', tone: 'red' },
    { side: 'BUY', contract: 'VN-ROB-13', volume: '20.0 MT', total: '$33,800.00', time: '12:02:11', tone: 'green' },
];
</script>

<template>
    <OuterLayout title="Live Market">
        <section class="live-page">
            <div class="live-shell">

                <div class="live-layout">
                    <aside class="live-sidebar">
                        <div class="live-profile">

                            <div>
                                <strong>Trader Terminal</strong>
                                <span>Live Market Active</span>
                            </div>
                        </div>

                        <nav class="live-side-nav">
                            <Link
                                v-for="item in sideLinks"
                                :key="item.label"
                                :href="item.href"
                                class="live-side-link"
                                :class="{ 'is-active': item.active }"
                            >
                                <component :is="item.icon" />
                                <span>{{ item.label }}</span>
                            </Link>
                        </nav>

                        <button type="button" class="live-deposit-btn">Quick Deposit</button>

                       
                    </aside>

                    <main class="live-main">
                        <section class="live-titlebar">
                            <div>
                                <h1>Live Coffee Market</h1>
                                <p>Real-time coffee prices, demand, supply, and trade signals</p>
                            </div>

                            <div class="live-title-actions">
                                <div class="live-segmented">
                                    <button type="button" class="is-active">Price</button>
                                    <button type="button">Volume</button>
                                    <button type="button">Demand</button>
                                </div>
                                <button type="button" class="live-action-btn is-buy">
                                    <ShoppingCartFull />
                                    <span>Buy Coffee</span>
                                </button>
                                <button type="button" class="live-action-btn">
                                    <Money />
                                    <span>Sell Coffee</span>
                                </button>
                                <button type="button" class="live-action-btn is-advisor">
                                    <ChatLineRound />
                                    <span>Ask Advisor</span>
                                </button>
                            </div>
                        </section>



                        <section class="live-ticker-bar">
                            <div v-for="item in tickerItems" :key="item.name" class="live-ticker-item">
                                <span>{{ item.name }}</span>
                                <strong>{{ item.price }}</strong>
                                <em :class="`is-${item.tone}`">{{ item.change }}</em>
                            </div>
                        </section>

                        <section class="live-stats-row">
                            <article v-for="item in topStats" :key="item.label" class="live-stat-card">
                                <span>{{ item.label }}</span>
                                <div class="live-stat-value" :class="`is-${item.tone}`">
                                    <small v-if="item.badge" class="live-origin-badge">{{ item.badge }}</small>
                                    <strong>{{ item.value }}</strong>
                                    <em v-if="item.helper">{{ item.helper }}</em>
                                </div>
                            </article>
                        </section>

                        <section class="live-main-grid">
                            <div class="live-left-stack">
                                <article class="live-card live-watchlist-card">
                                    <div class="live-card-head">
                                        <h2>Market Watchlist</h2>
                                        <span>⋮</span>
                                    </div>
                                    <div class="live-watchlist">
                                        <div v-for="item in watchlist" :key="item.code" class="live-watch-row">
                                            <div class="live-watch-meta">
                                                <i :class="`is-${item.tone}`"></i>
                                                <div>
                                                    <strong>{{ item.code }}</strong>
                                                    <span>{{ item.name }}</span>
                                                </div>
                                            </div>
                                            <div class="live-watch-price">
                                                <strong>{{ item.price }}</strong>
                                                <span :class="`is-${item.tone}`">{{ item.change }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="live-link-btn">View All Assets</button>
                                </article>

                                <article class="live-card live-gainers-card">
                                    <h2>Top Gainers</h2>
                                    <div class="live-gainers-list">
                                        <div v-for="item in gainers" :key="item.rank" class="live-gainer-row">
                                            <div class="live-gainer-name">
                                                <span>{{ item.rank }}</span>
                                                <strong>{{ item.name }}</strong>
                                            </div>
                                            <div class="live-gainer-side">
                                                <em>{{ item.gain }}</em>
                                                <div class="live-mini-track">
                                                    <div :style="{ width: item.width }"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>

                                <article
                                    v-for="card in alertCards"
                                    :key="card.title"
                                    class="live-alert-card"
                                    :class="`is-${card.tone}`"
                                >
                                    <component :is="card.icon" />
                                    <div>
                                        <strong>{{ card.title }}</strong>
                                        <p>{{ card.body }}</p>
                                    </div>
                                </article>
                            </div>

                            <div class="live-center-stack">
                                <article class="live-card live-chart-card">
                                    <div class="live-card-head live-chart-head">
                                        <div>
                                            <h2>Uganda Robusta G1</h2>
                                            <div class="live-chart-price-row">
                                                <strong>$1,840.00</strong>
                                                <span class="live-up-pill">+3.24%</span>
                                                <DataAnalysis />
                                                <TrendCharts />
                                                <Connection />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="live-chart-surface">
                                        <div class="live-chart-legend">
                                            <span><i class="is-dark"></i>Live Price Move</span>
                                            <span><i class="is-light"></i>Market Forecast</span>
                                        </div>
                                        <div class="live-chart-bars">
                                            <div
                                                v-for="(bar, index) in chartBars"
                                                :key="index"
                                                class="live-chart-bar"
                                                :class="`is-${bar.tone}`"
                                                :style="{ height: bar.height }"
                                            ></div>
                                        </div>
                                    </div>
                                </article>

                                <article class="live-card live-feed-card">
                                    <div class="live-card-head">
                                        <h2>Live Lots Feed</h2>
                                        <div class="live-feed-tags">
                                            <span>SCAA 85+</span>
                                            <span>Direct</span>
                                        </div>
                                    </div>

                                    <div class="live-feed-table">
                                        <div class="live-feed-head">
                                            <span>Origin / Grade</span>
                                            <span>SCAA Score</span>
                                            <span>Price/MT</span>
                                            <span>24H Chg</span>
                                        </div>
                                        <div v-for="row in lotsFeed" :key="row.name" class="live-feed-row">
                                            <div class="live-feed-origin">
                                                <b>{{ row.code }}</b>
                                                <div>
                                                    <strong>{{ row.name }}</strong>
                                                </div>
                                            </div>
                                            <span class="live-score-pill">{{ row.score }}</span>
                                            <strong>{{ row.price }}</strong>
                                            <em :class="`is-${row.tone}`">{{ row.change }}</em>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <aside class="live-right-stack">
                                <article class="live-signal-card">
                                    <div class="live-signal-head">
                                        <div>
                                            <span>✦</span>
                                            <strong>AI Trade Signal</strong>
                                        </div>
                                    </div>
                                    <div class="live-signal-body">
                                        <small>Recommended</small>
                                        <h2>Long Position: Uganda Robusta</h2>
                                        <div class="live-confidence">88% <span>Confidence</span></div>
                                        <div class="live-signal-stats">
                                            <div>
                                                <span>Risk Level</span>
                                                <strong>Low-Mid</strong>
                                            </div>
                                            <div>
                                                <span>Target Profit</span>
                                                <strong>+4.5%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </article>

                                <article class="live-chat-card">
                                    <div class="live-chat-head">
                                        <div class="live-chat-title">
                                            <div class="live-chat-avatar">
                                                <ChatLineRound />
                                            </div>
                                            <div>
                                                <strong>Bean Origin Advisor</strong>
                                                <span>Online • Market Analysis</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="live-chat-body">
                                        <div class="live-chat-bubble">
                                            Good morning! I see you're looking at Uganda Robusta. Prices have surged 3%
                                            following the logistics report from Mombasa port. Would you like a detailed
                                            forecast?
                                        </div>
                                        <div class="live-chat-bubble is-user">
                                            Show me the export corridor risk for the next 48 hours.
                                        </div>
                                    </div>

                                    <div class="live-chat-input">
                                        <input type="text" placeholder="Ask market advice..." />
                                        <button type="button">
                                            <Promotion />
                                        </button>
                                    </div>
                                </article>

                                <button type="button" class="live-buyorder-btn">Place Buy Order</button>
                            </aside>
                        </section>

                        <section class="live-bottom-grid">
                            <article class="live-card live-heatmap-card">
                                <h2>Demand Heatmap</h2>
                                <div class="live-heatmap-surface">
                                    <div class="live-heatmap-point"></div>
                                    <div class="live-heatmap-callout">
                                        <strong>UAE Demand Spiking</strong>
                                        <p>UAE market demand for specialty Robusta increased 14% this week.</p>
                                    </div>
                                </div>
                            </article>

                            <article class="live-card live-corridors-card">
                                <h2>Trade Corridors</h2>
                                <div class="live-corridor-list">
                                    <div v-for="item in corridorRows" :key="item.route" class="live-corridor-row">
                                        <strong>{{ item.route }}</strong>
                                        <span :class="`is-${item.tone}`">{{ item.state }}</span>
                                    </div>
                                </div>
                            </article>

                            <article class="live-card live-recent-card">
                                <h2>Recent Executions</h2>
                                <div class="live-recent-table">
                                    <div v-for="item in executionRows" :key="`${item.side}-${item.time}`" class="live-recent-row">
                                        <span :class="`is-${item.tone}`">{{ item.side }}</span>
                                        <strong>{{ item.contract }}</strong>
                                        <em>{{ item.volume }}</em>
                                        <b>{{ item.total }}</b>
                                        <small>{{ item.time }}</small>
                                    </div>
                                </div>
                            </article>
                        </section>
                    </main>
                </div>

                <button type="button" class="live-floating-fab">
                    <ShoppingTrolley />
                </button>
            </div>
        </section>
    </OuterLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');

.live-page {
    background: #eef3f7;
    color: #122132;
    font-family: 'Manrope', sans-serif;
    min-height: 100vh;
}

.live-shell {
    width: 100%;
}

.live-header {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #e6edf3;
    display: flex;
    justify-content: space-between;
    min-height: 64px;
    padding: 0 1.5rem;
}

.live-brand-row {
    align-items: center;
    display: flex;
    gap: 2rem;
}

.live-brand {
    color: #0d5a41;
    font-size: 1rem;
    font-weight: 700;
}

.live-top-nav {
    display: flex;
    gap: 1.45rem;
}

.live-top-link {
    border-bottom: 2px solid transparent;
    color: #55677c;
    font-size: 0.94rem;
    padding: 1.2rem 0;
    text-decoration: none;
}

.live-top-link.is-active {
    border-bottom-color: #0d5a41;
    color: #111827;
    font-weight: 600;
}

.live-header-tools {
    align-items: center;
    display: flex;
    gap: 0.85rem;
}

.live-search {
    align-items: center;
    background: #f5f7fa;
    border-radius: 0.5rem;
    color: #98a2b3;
    display: inline-flex;
    gap: 0.6rem;
    min-width: 305px;
    padding: 0.85rem 0.95rem;
}

.live-search :deep(svg),
.live-side-link :deep(svg),
.live-icon-btn :deep(svg),
.live-chat-avatar :deep(svg),
.live-action-btn :deep(svg),
.live-buyorder-btn :deep(svg),
.live-floating-fab :deep(svg),
.live-alert-card :deep(svg) {
    height: 1rem;
    width: 1rem;
}

.live-execute-btn {
    background: #0d5a41;
    border: 0;
    border-radius: 0.45rem;
    color: #fff;
    font-size: 0.92rem;
    font-weight: 700;
    min-height: 46px;
    padding: 0 1.35rem;
}

.live-icon-btn {
    align-items: center;
    background: transparent;
    border: 0;
    color: #42556b;
    display: inline-flex;
    height: 2.25rem;
    justify-content: center;
    width: 2.25rem;
}

.live-layout {
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr);
}

.live-sidebar {
    background: linear-gradient(180deg, #fbfcfd 0%, #f5f8fb 100%);
    border-right: 1px solid #e6edf3;
    min-height: calc(100vh - 64px);
    padding: 1rem 1rem 1.2rem;
}

.live-profile {
    align-items: center;
    display: flex;
    gap: 0.9rem;
    margin-bottom: 1.6rem;
    padding: 0 0.2rem;
}

.live-avatar {
    align-items: center;
    background: linear-gradient(180deg, #d8dde3, #b8c2cd);
    border-radius: 999px;
    color: #0f172a;
    display: inline-flex;
    flex: 0 0 auto;
    height: 2.75rem;
    justify-content: center;
    width: 2.75rem;
}

.live-profile strong {
    color: #152436;
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
}

.live-profile span {
    color: #728397;
    display: block;
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    margin-top: 0.18rem;
    text-transform: uppercase;
}

.live-side-nav {
    display: grid;
    gap: 0.55rem;
}

.live-side-link {
    align-items: center;
    background: transparent;
    border-radius: 0.75rem;
    color: #52657a;
    display: flex;
    gap: 0.85rem;
    min-height: 50px;
    padding: 0.95rem 1rem;
    text-decoration: none;
}

.live-side-link.is-active {
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    color: #0d5a41;
    font-weight: 600;
}

.live-deposit-btn {
    background: #ffd4a8;
    border: 0;
    border-radius: 0.75rem;
    color: #1f2937;
    font-size: 0.98rem;
    font-weight: 700;
    margin-top: 18rem;
    min-height: 48px;
    width: 100%;
}

.live-sidebar-footer {
    display: grid;
    gap: 1rem;
    margin-top: 2rem;
    padding: 0 0.2rem;
}

.live-footer-link {
    align-items: center;
    color: #55677c;
    display: flex;
    gap: 0.8rem;
    text-decoration: none;
}

.live-main {
    min-width: 0;
}

.live-titlebar {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #e6edf3;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.5rem;
}

.live-titlebar h1 {
    color: #0d5a41;
    font-size: 1.2rem;
    font-weight: 800;
    margin: 0 0 0.2rem;
}

.live-titlebar p {
    color: #59697d;
    font-size: 0.9rem;
    margin: 0;
}

.live-title-actions {
    align-items: center;
    display: flex;
    gap: 0.7rem;
}

.live-segmented {
    background: #fff;
    border: 1px solid #edf1f4;
    border-radius: 0.7rem;
    display: flex;
    gap: 0.15rem;
    padding: 0.2rem;
}

.live-segmented button {
    background: transparent;
    border: 0;
    border-radius: 0.45rem;
    color: #1f2937;
    font-size: 0.88rem;
    min-height: 34px;
    padding: 0 0.95rem;
}

.live-segmented .is-active {
    background: #f6f8fb;
    box-shadow: inset 0 0 0 1px #eef2f5;
    font-weight: 700;
}

.live-action-btn {
    align-items: center;
    background: #fff;
    border: 1px solid #0d5a41;
    border-radius: 0.55rem;
    color: #0d5a41;
    display: inline-flex;
    font-size: 0.9rem;
    font-weight: 700;
    gap: 0.55rem;
    min-height: 42px;
    padding: 0 1rem;
}

.live-action-btn.is-buy {
    background: #0d5a41;
    color: #fff;
}

.live-action-btn.is-advisor {
    background: #ffd9b5;
    border-color: #ffd9b5;
    color: #1f2937;
}

.live-filter-row {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #e6edf3;
    display: flex;
    gap: 1rem;
    padding: 0.8rem 1.5rem;
}

.live-filter-pill {
    align-items: center;
    display: inline-flex;
    gap: 0.45rem;
}

.live-filter-pill span {
    color: #364152;
    font-size: 0.76rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.live-filter-pill strong {
    background: #f7f9fb;
    border: 1px solid #eef2f5;
    border-radius: 0.45rem;
    color: #111827;
    font-size: 0.86rem;
    font-weight: 600;
    padding: 0.5rem 0.75rem;
}

.live-ticker-bar {
    align-items: center;
    background: #0d5a41;
    display: flex;
    gap: 2rem;
    overflow: hidden;
    padding: 0.7rem 1.5rem;
}

.live-ticker-item {
    display: flex;
    gap: 0.45rem;
    white-space: nowrap;
}

.live-ticker-item span,
.live-ticker-item strong,
.live-ticker-item em {
    color: #fff;
    font-size: 0.88rem;
    font-style: normal;
}

.live-ticker-item em.is-red {
    color: #ffd1d1;
}

.live-ticker-item em.is-green {
    color: #cff7e1;
}

.live-stats-row {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
}

.live-stat-card {
    background: #fff;
    border-bottom: 1px solid #e6edf3;
    border-right: 1px solid #e6edf3;
    min-height: 94px;
    padding: 1rem 1rem 0.95rem;
}

.live-stat-card:last-child {
    border-right: 0;
}

.live-stat-card span {
    color: #364152;
    display: block;
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    margin-bottom: 0.55rem;
    text-transform: uppercase;
}

.live-stat-value {
    align-items: center;
    display: flex;
    gap: 0.45rem;
}

.live-stat-value strong {
    color: #111827;
    font-size: 0.95rem;
    font-weight: 800;
}

.live-stat-value.is-green strong {
    color: #0d5a41;
}

.live-stat-value.is-red strong {
    color: #d92d20;
}

.live-stat-value em {
    color: #0d5a41;
    font-style: normal;
    font-weight: 700;
}

.live-origin-badge {
    background: #b8f2d0;
    border-radius: 0.25rem;
    color: #0d5a41;
    font-size: 0.65rem;
    font-style: normal;
    font-weight: 700;
    padding: 0.22rem 0.38rem;
    text-transform: uppercase;
}

.live-main-grid {
    display: grid;
    gap: 1.3rem;
    grid-template-columns: 230px minmax(0, 1fr) 310px;
    padding: 1.5rem;
}

.live-left-stack,
.live-center-stack,
.live-right-stack {
    display: grid;
    gap: 1rem;
    align-content: start;
}

.live-card,
.live-signal-card,
.live-chat-card {
    background: #fff;
    border: 1px solid #e6edf3;
    border-radius: 0.95rem;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.035);
}

.live-card {
    padding: 1rem;
}

.live-card-head {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.live-card h2,
.live-signal-card h2 {
    color: #122132;
    font-size: 0.92rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    margin: 0;
    text-transform: uppercase;
}

.live-watchlist {
    display: grid;
}

.live-watch-row {
    align-items: center;
    border-top: 1px solid #eef2f5;
    display: flex;
    justify-content: space-between;
    padding: 0.95rem 0;
}

.live-watch-row:first-child {
    border-top: 0;
    padding-top: 0;
}

.live-watch-meta {
    align-items: center;
    display: flex;
    gap: 0.65rem;
}

.live-watch-meta i {
    border-radius: 999px;
    display: inline-block;
    height: 8px;
    width: 8px;
}

.live-watch-meta i.is-green {
    background: #0d5a41;
}

.live-watch-meta i.is-red {
    background: #d92d20;
}

.live-watch-meta strong {
    color: #111827;
    display: block;
    font-size: 0.88rem;
}

.live-watch-meta span,
.live-watch-price span {
    color: #667085;
    display: block;
    font-size: 0.76rem;
}

.live-watch-price {
    text-align: right;
}

.live-watch-price strong {
    color: #111827;
    display: block;
    font-size: 0.88rem;
}

.live-watch-price .is-green {
    color: #0d5a41;
}

.live-watch-price .is-red {
    color: #d92d20;
}

.live-link-btn {
    background: transparent;
    border: 0;
    color: #111827;
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    margin: 0.35rem auto 0;
    text-transform: uppercase;
}

.live-gainers-list {
    display: grid;
    gap: 1rem;
}

.live-gainer-row {
    display: grid;
    gap: 0.8rem;
}

.live-gainer-name {
    align-items: center;
    display: flex;
    gap: 0.6rem;
}

.live-gainer-name span {
    background: #b8f2d0;
    border-radius: 0.2rem;
    color: #0d5a41;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.18rem 0.35rem;
}

.live-gainer-name strong {
    color: #111827;
    font-size: 0.86rem;
}

.live-gainer-side {
    align-items: center;
    display: flex;
    gap: 0.65rem;
    justify-content: space-between;
}

.live-gainer-side em {
    color: #0d5a41;
    font-size: 0.84rem;
    font-style: normal;
    font-weight: 700;
}

.live-mini-track {
    background: #edf1f4;
    border-radius: 999px;
    flex: 1;
    height: 5px;
    overflow: hidden;
}

.live-mini-track div {
    background: #0d5a41;
    height: 100%;
}

.live-alert-card {
    align-items: start;
    border-radius: 0.9rem;
    display: flex;
    gap: 0.7rem;
    padding: 0.95rem 1rem;
}

.live-alert-card.is-red {
    background: #ffe3df;
    border: 1px solid #ffd0ca;
    color: #8d2318;
}

.live-alert-card.is-amber {
    background: #ffdcb9;
    border: 1px solid #ffcc94;
    color: #7a4b16;
}

.live-alert-card strong {
    display: block;
    font-size: 0.84rem;
    margin-bottom: 0.2rem;
}

.live-alert-card p {
    font-size: 0.8rem;
    line-height: 1.45;
    margin: 0;
}

.live-chart-card {
    padding: 0;
}

.live-chart-head {
    padding: 1rem 1rem 0;
}

.live-chart-price-row {
    align-items: center;
    display: flex;
    gap: 0.65rem;
    margin-top: 0.35rem;
}

.live-chart-price-row strong {
    color: #111827;
    font-size: 0.96rem;
    font-weight: 800;
}

.live-chart-price-row :deep(svg) {
    color: #233242;
    height: 1rem;
    width: 1rem;
}

.live-up-pill {
    background: #b8f2d0;
    border-radius: 0.25rem;
    color: #0d5a41;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.22rem 0.45rem;
}

.live-chart-surface {
    background:
        radial-gradient(circle at center, rgba(10, 10, 10, 0.03) 1px, transparent 1px),
        linear-gradient(180deg, #f9fbfd 0%, #f3f7fb 100%);
    background-size: 16px 16px, 100% 100%;
    border-radius: 0 0 0.95rem 0.95rem;
    min-height: 342px;
    padding: 1rem 1rem 1.15rem;
    position: relative;
}

.live-chart-surface::before {
    background:
        linear-gradient(180deg, transparent 0, transparent calc(100% - 1px), rgba(15, 23, 42, 0.06) calc(100% - 1px)),
        linear-gradient(90deg, transparent 0, transparent calc(100% - 1px), rgba(15, 23, 42, 0.04) calc(100% - 1px));
    background-size: 100% 25%, 14.28% 100%;
    content: '';
    inset: 0;
    position: absolute;
}

.live-chart-legend {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #e6edf3;
    border-radius: 0.45rem;
    display: grid;
    gap: 0.35rem;
    padding: 0.6rem 0.7rem;
    position: absolute;
    right: 1rem;
    top: 1rem;
    z-index: 2;
}

.live-chart-legend span {
    align-items: center;
    color: #5b6472;
    display: flex;
    font-size: 0.74rem;
    gap: 0.4rem;
}

.live-chart-legend i {
    border-radius: 999px;
    display: inline-block;
    height: 8px;
    width: 8px;
}

.live-chart-legend .is-dark {
    background: #0d5a41;
}

.live-chart-legend .is-light {
    background: #baa47c;
}

.live-chart-bars {
    align-items: end;
    display: grid;
    gap: 0.2rem;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    height: 290px;
    padding-top: 42px;
    position: relative;
    z-index: 1;
}

.live-chart-bar {
    border-radius: 0.2rem 0.2rem 0 0;
}

.live-chart-bar.is-soft {
    background: rgba(146, 176, 170, 0.65);
}

.live-chart-bar.is-deep {
    background: rgba(98, 136, 124, 0.82);
}

.live-chart-bar.is-dark {
    background: #0d5a41;
}

.live-chart-bar.is-red {
    background: rgba(234, 175, 176, 0.9);
}

.live-feed-card {
    padding: 0;
}

.live-feed-card .live-card-head {
    padding: 1rem 1rem 0;
}

.live-feed-tags {
    display: flex;
    gap: 0.5rem;
}

.live-feed-tags span {
    background: #f5f7fa;
    border: 1px solid #edf1f4;
    border-radius: 0.35rem;
    color: #4b5563;
    font-size: 0.72rem;
    padding: 0.28rem 0.5rem;
}

.live-feed-table {
    padding: 0 1rem 1rem;
}

.live-feed-head,
.live-feed-row {
    display: grid;
    gap: 0.8rem;
    grid-template-columns: 1.35fr 0.7fr 0.7fr 0.6fr;
}

.live-feed-head {
    border-top: 1px solid #eef2f5;
    color: #4b5563;
    font-size: 0.74rem;
    margin-top: 0.3rem;
    padding: 0.9rem 0;
    text-transform: uppercase;
}

.live-feed-row {
    align-items: center;
    border-top: 1px solid #f0f4f7;
    padding: 1rem 0;
}

.live-feed-origin {
    align-items: center;
    display: flex;
    gap: 0.65rem;
}

.live-feed-origin b {
    align-items: center;
    background: #b8f2d0;
    border-radius: 0.2rem;
    color: #0d5a41;
    display: inline-flex;
    font-size: 0.72rem;
    height: 1.7rem;
    justify-content: center;
    width: 1.7rem;
}

.live-feed-origin strong,
.live-feed-row strong {
    color: #111827;
    font-size: 0.88rem;
}

.live-score-pill {
    background: #edf1f4;
    border-radius: 999px;
    color: #111827;
    display: inline-block;
    font-size: 0.76rem;
    justify-self: start;
    padding: 0.26rem 0.55rem;
}

.live-feed-row em {
    font-size: 0.84rem;
    font-style: normal;
    font-weight: 700;
}

.live-feed-row em.is-green {
    color: #0d5a41;
}

.live-feed-row em.is-red {
    color: #d92d20;
}

.live-signal-card {
    background: linear-gradient(180deg, #0d5a41 0%, #0b4b38 100%);
    border-radius: 0.95rem;
    color: #fff;
    padding: 1rem;
}

.live-signal-head strong {
    font-size: 0.92rem;
    text-transform: uppercase;
}

.live-signal-head span {
    margin-right: 0.4rem;
}

.live-signal-body small {
    color: rgba(255, 255, 255, 0.72);
    display: block;
    font-size: 0.74rem;
    letter-spacing: 0.1em;
    margin: 1rem 0 0.45rem;
    text-transform: uppercase;
}

.live-signal-body h2 {
    color: #fff;
    font-size: 0.98rem;
    line-height: 1.35;
    margin: 0 0 0.9rem;
    text-transform: none;
}

.live-confidence {
    color: #d8f9e7;
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 1rem;
}

.live-confidence span {
    color: rgba(255, 255, 255, 0.72);
    display: block;
    font-size: 0.68rem;
    letter-spacing: 0.1em;
    margin-top: 0.2rem;
    text-transform: uppercase;
}

.live-signal-stats {
    display: grid;
    gap: 0.7rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.live-signal-stats div {
    background: rgba(255, 255, 255, 0.08);
    border-radius: 0.45rem;
    padding: 0.75rem;
}

.live-signal-stats span {
    color: rgba(255, 255, 255, 0.72);
    display: block;
    font-size: 0.68rem;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
}

.live-signal-stats strong {
    font-size: 0.9rem;
}

.live-chat-card {
    overflow: hidden;
    position: relative;
}

.live-chat-head {
    background: #0d5a41;
    color: #fff;
    padding: 0.9rem 1rem;
}

.live-chat-title {
    align-items: center;
    display: flex;
    gap: 0.7rem;
}

.live-chat-avatar {
    align-items: center;
    background: rgba(255, 255, 255, 0.14);
    border-radius: 999px;
    display: inline-flex;
    height: 2rem;
    justify-content: center;
    width: 2rem;
}

.live-chat-title strong {
    display: block;
    font-size: 0.92rem;
}

.live-chat-title span {
    color: rgba(255, 255, 255, 0.72);
    display: block;
    font-size: 0.72rem;
    letter-spacing: 0.08em;
    margin-top: 0.15rem;
    text-transform: uppercase;
}

.live-chat-body {
    display: grid;
    gap: 1rem;
    padding: 1rem;
}

.live-chat-bubble {
    background: #fff;
    border: 1px solid #e8edf2;
    border-radius: 0.8rem;
    color: #344054;
    font-size: 0.85rem;
    line-height: 1.55;
    padding: 0.95rem 1rem;
}

.live-chat-bubble.is-user {
    background: #e8f3ef;
    border-color: #dcebe5;
    justify-self: end;
    max-width: 88%;
    text-align: right;
}

.live-chat-input {
    align-items: center;
    border-top: 1px solid #edf1f4;
    display: flex;
    gap: 0.6rem;
    padding: 0.9rem 1rem 1rem;
}

.live-chat-input input {
    background: #f6f8fb;
    border: 1px solid #eef2f5;
    border-radius: 0.45rem;
    flex: 1;
    min-height: 42px;
    outline: none;
    padding: 0 0.9rem;
}

.live-chat-input button,
.live-buyorder-btn {
    align-items: center;
    background: #0d5a41;
    border: 0;
    border-radius: 0.45rem;
    color: #fff;
    display: inline-flex;
    justify-content: center;
}

.live-chat-input button {
    height: 42px;
    width: 42px;
}

.live-buyorder-btn {
    font-size: 0.98rem;
    font-weight: 700;
    min-height: 54px;
    width: 100%;
}

.live-orderbook-card h2 {
    margin-bottom: 1rem;
}

.live-book-block {
    border-radius: 0.5rem;
    overflow: hidden;
    padding: 0.2rem 0;
}

.live-book-block.is-sell {
    background: #fff4f2;
}

.live-book-block.is-buy {
    background: #eef9f4;
}

.live-book-head,
.live-spread-row,
.live-book-row {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.live-book-head,
.live-spread-row {
    color: #364152;
    font-size: 0.78rem;
    margin-bottom: 0.8rem;
    text-transform: uppercase;
}

.live-book-row {
    font-size: 0.88rem;
    padding: 0.5rem 0.75rem;
}

.live-book-row strong {
    color: #d92d20;
}

.live-book-block.is-buy .live-book-row strong {
    color: #0d5a41;
}

.live-book-row span {
    color: #111827;
}

.live-spread-row {
    margin: 0.9rem 0;
}

.live-spread-row strong {
    color: #111827;
    font-size: 0.9rem;
}

.live-bottom-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: 1.05fr 1fr 2fr;
    padding: 0 1.5rem 1.5rem;
}

.live-heatmap-surface {
    background:
        radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.2), transparent 16%),
        linear-gradient(180deg, #d8dde2, #c6ccd3);
    border-radius: 0.8rem;
    min-height: 162px;
    padding: 1.1rem;
    position: relative;
}

.live-heatmap-point {
    background: #0d5a41;
    border-radius: 999px;
    box-shadow: 0 0 0 6px rgba(13, 90, 65, 0.12);
    height: 12px;
    left: 2.9rem;
    position: absolute;
    top: 4.7rem;
    width: 12px;
}

.live-heatmap-callout {
    bottom: 1rem;
    left: 1rem;
    position: absolute;
}

.live-heatmap-callout strong {
    color: #111827;
    display: block;
    font-size: 0.84rem;
    margin-bottom: 0.35rem;
}

.live-heatmap-callout p {
    color: #5d6674;
    font-size: 0.76rem;
    line-height: 1.45;
    margin: 0;
    max-width: 175px;
}

.live-corridor-list {
    display: grid;
    gap: 0.75rem;
}

.live-corridor-row {
    align-items: center;
    background: #f7f9fb;
    border-radius: 0.55rem;
    display: flex;
    justify-content: space-between;
    padding: 0.85rem 0.9rem;
}

.live-corridor-row strong {
    color: #111827;
    font-size: 0.9rem;
}

.live-corridor-row span {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
}

.live-corridor-row .is-green {
    color: #0d5a41;
}

.live-corridor-row .is-slate {
    color: #667085;
}

.live-recent-table {
    display: grid;
}

.live-recent-row {
    align-items: center;
    border-top: 1px solid #edf1f4;
    display: grid;
    gap: 0.8rem;
    grid-template-columns: 0.5fr 1fr 0.65fr 0.9fr 0.55fr;
    padding: 0.9rem 0;
}

.live-recent-row:first-child {
    border-top: 0;
    padding-top: 0;
}

.live-recent-row span,
.live-recent-row strong,
.live-recent-row em,
.live-recent-row b,
.live-recent-row small {
    font-size: 0.82rem;
}

.live-recent-row span {
    font-weight: 700;
}

.live-recent-row span.is-green {
    color: #0d5a41;
}

.live-recent-row span.is-red {
    color: #d92d20;
}

.live-recent-row strong,
.live-recent-row b {
    color: #111827;
}

.live-recent-row em,
.live-recent-row small {
    color: #5e6b78;
    font-style: normal;
}

.live-floating-fab {
    align-items: center;
    background: #0d5a41;
    border: 0;
    border-radius: 1rem;
    bottom: 1.35rem;
    box-shadow: 0 14px 30px rgba(13, 90, 65, 0.22);
    color: #fff;
    display: inline-flex;
    height: 58px;
    justify-content: center;
    position: fixed;
    right: 1.35rem;
    width: 58px;
    z-index: 30;
}

@media (max-width: 1279.98px) {
    .live-layout,
    .live-main-grid,
    .live-bottom-grid {
        grid-template-columns: 1fr;
    }

    .live-sidebar {
        min-height: auto;
    }

    .live-deposit-btn {
        margin-top: 2rem;
    }

    .live-stats-row {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .live-header,
    .live-titlebar {
        align-items: start;
        flex-direction: column;
    }

    .live-brand-row,
    .live-header-tools,
    .live-title-actions {
        flex-wrap: wrap;
    }

    .live-search {
        min-width: 100%;
    }

    .live-stats-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .live-main-grid {
        padding: 1rem;
    }
}

@media (max-width: 767.98px) {
    .live-header,
    .live-titlebar,
    .live-filter-row,
    .live-ticker-bar,
    .live-bottom-grid {
        padding-inline: 1rem;
    }

    .live-stats-row {
        grid-template-columns: 1fr;
    }

    .live-recent-row {
        grid-template-columns: 1fr;
    }
}
</style>
