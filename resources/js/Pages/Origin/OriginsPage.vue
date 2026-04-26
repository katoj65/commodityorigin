<script setup>
import {
    Bell,
    ChatLineRound,
    Checked,
    DataAnalysis,
    Grid,
    Histogram,
    MapLocation,
    Opportunity,
    Pointer,
    Reading,
    Setting,
    Star,
    UserFilled,
    WarnTriangleFilled,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const headerTabs = [
    { label: 'Analytics', active: false },
    { label: 'Sourcing', active: true },
    { label: 'Trade', active: false },
];

const sideLinks = [
    { label: 'Dashboard', icon: Grid, active: false },
    { label: 'Global Map', icon: MapLocation, active: false },
    { label: 'Harvest Calendar', icon: Reading, active: false },
    { label: 'Flavor Matrix', icon: Histogram, active: false },
    { label: 'Opportunities', icon: Opportunity, active: true },
];

const filters = [
    'Country: Uganda',
    'Region: All',
    'Type: Arabica',
    'Quality: 85+ SCAA',
    'Market: UAE',
];

const stats = [
    { icon: '🌍', text: '14 Regions' },
    { icon: '☕', text: '70% Arabica / 30% Robusta' },
    { icon: '⭐', text: '85.4 Avg Score' },
    { icon: '↗', text: 'Increasing Demand' },
    { icon: '💰', text: '$2.5-$7.0/kg' },
];

const originCards = [
    {
        title: 'Rwenzori Mts, Uganda',
        score: '88.5',
        type: 'Arabica',
        altitude: '1,400-2,100m',
        tags: ['Chocolate', 'Nutty', 'Full Body'],
        demand: 'High',
        demandTone: 'dark',
        scoreValue: '9.5/10',
        price: '$5.20-7.00/kg',
        risk: 'Low',
        why: 'Strong interest from EU micro-roasters for natural process.',
    },
    {
        title: 'Mount Elgon, Uganda',
        score: '87.2',
        type: 'Arabica',
        altitude: '1,600-2,200m',
        tags: ['Floral', 'Fruity', 'Bright Acidity'],
        demand: 'Medium',
        demandTone: 'dark',
        scoreValue: '9.2/10',
        price: '$4.50-6.00/kg',
        risk: 'Low',
        why: 'Surge in specialty demand for washed process lots.',
    },
];

const flavorScores = [
    { label: 'Aroma', value: '9.2', width: '92%' },
    { label: 'Body', value: '8.4', width: '84%' },
    { label: 'Acidity', value: '8.8', width: '88%' },
    { label: 'Aftertaste', value: '9.0', width: '90%' },
];

const marketDemand = [
    { market: 'UAE', status: 'High', tone: 'green' },
];

const tradeOpportunities = [
    { route: 'Rwenzori → EU', note: 'High Demand', tone: 'green' },
    { route: 'Robusta → UAE', note: 'Rising Trend', tone: 'amber' },
];

const lotTags = ['Organic', 'Fairtrade', 'Traceable'];

const lots = [
    {
        id: 'LOT-RW-24-001',
        origin: 'Rwenzori / Natural',
        score: '88.5',
        process: 'Natural / Sundried',
        quantity: '42 Bags (60kg)',
    },
    {
        id: 'LOT-EG-24-012',
        origin: 'Mt. Elgon / Washed',
        score: '87.2',
        process: 'Fully Washed',
        quantity: '110 Bags (60kg)',
    },
    {
        id: 'LOT-KAS-24-09',
        origin: 'Kasese / Honey',
        score: '86.8',
        process: 'Red Honey',
        quantity: '15 Bags (60kg)',
    },
];
</script>

<template>
    <OuterLayout title="Coffee Origins">
        <section class="origin-page">
            <div class="origin-shell">
           

                <div class="origin-layout">
                    <aside class="origin-sidebar">
                        <div class="origin-sidebar-top">
                            <h2>Core Intel</h2>
                            <span>Institutional Grade</span>
                        </div>

                        <nav class="origin-side-nav">
                            <a
                                v-for="item in sideLinks"
                                :key="item.label"
                                href="#"
                                class="origin-side-link"
                                :class="{ 'is-active': item.active }"
                            >
                                <component :is="item.icon" />
                                <span>{{ item.label }}</span>
                            </a>
                        </nav>

                        <div class="origin-sidebar-bottom">
                            <button type="button" class="origin-new-btn">
                                <span>＋</span>
                                <span>New Analysis</span>
                            </button>

                            <a href="#" class="origin-bottom-link">
                                <Checked />
                                <span>Support</span>
                            </a>
                            <a href="#" class="origin-bottom-link">
                                <DataAnalysis />
                                <span>Logs</span>
                            </a>
                        </div>
                    </aside>

                    <main class="origin-main">
                        <section class="origin-toolbar">
                            <div>
                                <h1>Coffee Origins</h1>
                                <p>Global origin insights and sourcing intelligence</p>
                            </div>

                            <div class="origin-toolbar-actions">
                                <button type="button" class="origin-action-btn is-light">View Lots</button>
                                <button type="button" class="origin-action-btn is-light">Export Insights</button>
                                <button type="button" class="origin-action-btn is-dark">Ask Advisor</button>
                            </div>
                        </section>

                        <section class="origin-filter-bar">
                            <button v-for="item in filters" :key="item" type="button" class="origin-filter-btn">
                                <span>{{ item }}</span>
                                <span>⌄</span>
                            </button>
                        </section>

                        <section class="origin-positive-banner">
                            <span class="origin-pill">Positive</span>
                            <p>Ugandan Robusta demand rising in UAE while Arabica demand remains strong in EU markets.</p>
                        </section>

                        <section class="origin-stats-strip">
                            <div v-for="item in stats" :key="item.text" class="origin-stat-item">
                                <span class="origin-stat-icon">{{ item.icon }}</span>
                                <strong>{{ item.text }}</strong>
                            </div>
                        </section>

                        <section class="origin-grid">
                            <div class="origin-core-column">
                                <div class="origin-card-grid">
                                    <article v-for="card in originCards" :key="card.title" class="origin-card origin-region-card">
                                        <div class="origin-region-head">
                                            <div>
                                                <h2>{{ card.title }}</h2>
                                                <div class="origin-region-meta">
                                                    <span class="origin-type-chip">{{ card.type }}</span>
                                                    <span>{{ card.altitude }}</span>
                                                </div>
                                            </div>
                                            <div class="origin-region-score">
                                                <strong>{{ card.score }}</strong>
                                                <span>SCAA</span>
                                            </div>
                                        </div>

                                        <div class="origin-tag-row">
                                            <span v-for="tag in card.tags" :key="tag" class="origin-taste-tag">{{ tag }}</span>
                                        </div>

                                        <div class="origin-score-box">
                                            <div>
                                                <span>Demand</span>
                                                <strong>{{ card.demand }}</strong>
                                            </div>
                                            <div>
                                                <span>Score</span>
                                                <strong>{{ card.scoreValue }}</strong>
                                            </div>
                                            <div>
                                                <span>Price</span>
                                                <strong>{{ card.price }}</strong>
                                            </div>
                                            <div>
                                                <span>Risk</span>
                                                <strong class="is-low">{{ card.risk }}</strong>
                                            </div>
                                        </div>

                                        <div class="origin-why-box">
                                            <span>Why It Matters</span>
                                            <p>{{ card.why }}</p>
                                        </div>
                                    </article>
                                </div>

                                <section class="origin-card origin-harvest-card">
                                    <h3>Harvest Calendar</h3>
                                    <div class="origin-harvest-track">
                                        <div class="origin-harvest-months">
                                            <span>Jan</span>
                                            <span>Feb</span>
                                            <span>Mar</span>
                                            <span>Apr</span>
                                            <span>May</span>
                                            <span>Jun</span>
                                            <span>Jul</span>
                                            <span>Aug</span>
                                            <span>Sep</span>
                                        </div>
                                        <div class="origin-harvest-bars">
                                            <div class="origin-harvest-bar is-fly">Apr Fly Crop</div>
                                            <div class="origin-harvest-bar is-main">Main Crop</div>
                                        </div>
                                    </div>
                                </section>

                                <section class="origin-card origin-lots-card">
                                    <div class="origin-lots-head">
                                        <h3>Active Available Lots</h3>
                                        <div class="origin-lot-tags">
                                            <span
                                                v-for="tag in lotTags"
                                                :key="tag"
                                                class="origin-lot-tag"
                                                :class="{ 'is-active': tag === 'Organic' }"
                                            >
                                                {{ tag }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="origin-table-wrap">
                                        <table class="origin-table">
                                            <thead>
                                                <tr>
                                                    <th>Lot ID</th>
                                                    <th>Origin</th>
                                                    <th>Quality Score</th>
                                                    <th>Process</th>
                                                    <th>Quantity</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="lot in lots" :key="lot.id">
                                                    <td>{{ lot.id }}</td>
                                                    <td>{{ lot.origin }}</td>
                                                    <td><span class="origin-score-tag">{{ lot.score }}</span></td>
                                                    <td>{{ lot.process }}</td>
                                                    <td>{{ lot.quantity }}</td>
                                                    <td><a href="#">View Samples</a></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>

                            <aside class="origin-rail">
                                <article class="origin-card origin-map-card">
                                    <div class="origin-map-label">Uganda Region Map</div>
                                    <div class="origin-map-surface"></div>
                                </article>

                                <article class="origin-card origin-flavor-card">
                                    <h3>Flavor Profile Index</h3>
                                    <div class="origin-flavor-list">
                                        <div v-for="item in flavorScores" :key="item.label" class="origin-flavor-row">
                                            <div class="origin-flavor-meta">
                                                <span>{{ item.label }}</span>
                                                <strong>{{ item.value }}</strong>
                                            </div>
                                            <div class="origin-flavor-track">
                                                <div class="origin-flavor-fill" :style="{ width: item.width }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </article>

                                <article class="origin-card origin-demand-card">
                                    <h3>Demand by Market</h3>
                                    <div class="origin-demand-box">
                                        <div v-for="item in marketDemand" :key="item.market" class="origin-demand-pill">
                                            <span>{{ item.market }}</span>
                                            <strong>{{ item.status }}</strong>
                                        </div>
                                    </div>
                                </article>

                                <article class="origin-card origin-trade-card">
                                    <h3>Trade Opportunities</h3>
                                    <div class="origin-trade-list">
                                        <div
                                            v-for="item in tradeOpportunities"
                                            :key="item.route"
                                            class="origin-trade-row"
                                            :class="`is-${item.tone}`"
                                        >
                                            <div>
                                                <strong>{{ item.route }}</strong>
                                                <span>{{ item.note }}</span>
                                            </div>
                                            <Pointer />
                                        </div>
                                    </div>
                                </article>
                            </aside>
                        </section>
                    </main>
                </div>

                <div class="origin-floating-chat">
                    <div class="origin-chat-bubble">Which region has best quality?</div>
                    <div class="origin-chat-bubble">Where should I source for UAE?</div>
                    <button type="button" class="origin-chat-fab">
                        <ChatLineRound />
                    </button>
                </div>
            </div>
        </section>
    </OuterLayout>
</template>

<style scoped>
.origin-page {
    background: #f6f8fb;
    color: #132033;
    font-family: 'Manrope', sans-serif;
    min-height: 100vh;
}

.origin-shell {
    margin: 0 auto;
    max-width: 1406px;
}

.origin-header {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #e7edf2;
    display: flex;
    justify-content: space-between;
    min-height: 62px;
    padding: 0 1.6rem;
}

.origin-brand-row {
    align-items: center;
    display: flex;
    gap: 2rem;
}

.origin-brand {
    color: #0d5a41;
    font-size: 1.02rem;
    font-weight: 700;
}

.origin-top-nav {
    display: flex;
    gap: 1.8rem;
}

.origin-top-link {
    border-bottom: 2px solid transparent;
    color: #506176;
    font-size: 0.95rem;
    padding: 1.15rem 0;
    text-decoration: none;
}

.origin-top-link.is-active {
    border-bottom-color: #0d5a41;
    color: #0d5a41;
    font-weight: 600;
}

.origin-header-tools {
    display: flex;
    gap: 0.75rem;
}

.origin-icon-btn,
.origin-side-link :deep(svg),
.origin-bottom-link :deep(svg),
.origin-trade-row :deep(svg),
.origin-chat-fab :deep(svg) {
    align-items: center;
    color: #475569;
    display: inline-flex;
    height: 2rem;
    justify-content: center;
    width: 2rem;
}

.origin-icon-btn {
    background: transparent;
    border: 0;
}

.origin-layout {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
}

.origin-sidebar {
    background: linear-gradient(180deg, #fbfcfd 0%, #f6f8fb 100%);
    border-right: 1px solid #e7edf2;
    min-height: calc(100vh - 62px);
    padding: 1.5rem 1rem;
}

.origin-sidebar-top {
    margin-bottom: 2rem;
    padding: 0 0.45rem;
}

.origin-sidebar-top h2 {
    color: #0d5a41;
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 0.35rem;
    text-transform: uppercase;
}

.origin-sidebar-top span {
    color: #748396;
    font-size: 0.82rem;
}

.origin-side-nav {
    display: grid;
    gap: 0.55rem;
}

.origin-side-link {
    align-items: center;
    background: transparent;
    border-radius: 0.7rem;
    color: #334155;
    display: flex;
    gap: 0.85rem;
    min-height: 58px;
    padding: 0.95rem 1rem;
    text-decoration: none;
}

.origin-side-link.is-active {
    background: #fff;
    box-shadow: 0 1px 0 rgba(15, 23, 42, 0.02);
    color: #0d5a41;
    font-weight: 600;
}

.origin-sidebar-bottom {
    display: grid;
    gap: 1rem;
    margin-top: 20rem;
    padding: 0 0.1rem;
}

.origin-new-btn {
    align-items: center;
    background: #0d5a41;
    border: 0;
    border-radius: 0.45rem;
    color: #fff;
    display: inline-flex;
    font-size: 0.98rem;
    font-weight: 700;
    gap: 0.7rem;
    justify-content: center;
    min-height: 40px;
}

.origin-bottom-link {
    align-items: center;
    color: #46586c;
    display: flex;
    gap: 0.8rem;
    text-decoration: none;
}

.origin-main {
    padding: 1.65rem 1.55rem 2rem;
}

.origin-toolbar {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.15rem;
}

.origin-toolbar h1 {
    color: #111827;
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 0.25rem;
}

.origin-toolbar p {
    color: #4b5563;
    font-size: 0.95rem;
    margin: 0;
}

.origin-toolbar-actions {
    display: flex;
    gap: 0.65rem;
}

.origin-action-btn,
.origin-filter-btn {
    align-items: center;
    background: #f1f5f9;
    border: 1px solid #e6ecf1;
    border-radius: 0.45rem;
    color: #172431;
    display: inline-flex;
    font-size: 0.9rem;
    gap: 0.5rem;
    min-height: 38px;
    padding: 0 1rem;
}

.origin-action-btn.is-dark {
    background: #0d5a41;
    border-color: #0d5a41;
    color: #fff;
}

.origin-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.9rem;
    margin-bottom: 1.15rem;
}

.origin-filter-btn {
    background: #fff;
    justify-content: space-between;
    min-width: 154px;
}

.origin-positive-banner,
.origin-stats-strip,
.origin-card {
    background: #fff;
    border: 1px solid #e7edf2;
    border-radius: 0.9rem;
}

.origin-positive-banner {
    align-items: center;
    background: #dff6ee;
    display: flex;
    gap: 0.9rem;
    margin-bottom: 1.4rem;
    padding: 0.8rem 1.05rem;
}

.origin-pill {
    background: #0d5a41;
    border-radius: 999px;
    color: #fff;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    padding: 0.38rem 0.7rem;
    text-transform: uppercase;
}

.origin-positive-banner p {
    color: #103928;
    font-size: 0.96rem;
    margin: 0;
}

.origin-stats-strip {
    align-items: center;
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-bottom: 1.55rem;
    padding: 1.15rem 1.25rem;
}

.origin-stat-item {
    align-items: center;
    display: flex;
    gap: 0.75rem;
}

.origin-stat-icon {
    font-size: 1.25rem;
}

.origin-stat-item strong {
    color: #111827;
    font-size: 0.95rem;
    font-weight: 700;
}

.origin-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: minmax(0, 1fr) 342px;
}

.origin-core-column {
    display: grid;
    gap: 1.2rem;
}

.origin-card-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.origin-card {
    padding: 1rem;
}

.origin-region-card {
    min-height: 426px;
}

.origin-region-head {
    align-items: start;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.8rem;
}

.origin-region-head h2 {
    color: #111827;
    font-size: 1rem;
    font-weight: 800;
    margin: 0 0 0.45rem;
}

.origin-region-meta {
    align-items: center;
    color: #5b6472;
    display: flex;
    gap: 0.5rem;
    font-size: 0.82rem;
}

.origin-type-chip {
    background: #f7dcc0;
    border-radius: 0.35rem;
    color: #70411c;
    font-size: 0.72rem;
    padding: 0.3rem 0.45rem;
    text-transform: uppercase;
}

.origin-region-score {
    text-align: right;
}

.origin-region-score strong {
    color: #0d5a41;
    display: block;
    font-size: 1rem;
    font-weight: 800;
}

.origin-region-score span {
    color: #4b5563;
    font-size: 0.8rem;
}

.origin-tag-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-bottom: 0.9rem;
}

.origin-taste-tag {
    background: #c8f5dc;
    border-radius: 999px;
    color: #0d5a41;
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
}

.origin-score-box {
    background: #f6f8fb;
    border-radius: 0.5rem;
    display: grid;
    gap: 1rem 1.4rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 0.9rem;
    padding: 0.9rem 0.95rem;
}

.origin-score-box span {
    color: #5b6472;
    display: block;
    font-size: 0.72rem;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
}

.origin-score-box strong {
    color: #111827;
    font-size: 0.92rem;
    font-weight: 700;
}

.origin-score-box strong.is-low {
    color: #0d5a41;
}

.origin-why-box {
    background: #fffaf4;
    border-left: 2px solid #9c6a24;
    padding: 0.75rem 0.8rem;
}

.origin-why-box span {
    color: #6a4b1f;
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    margin-bottom: 0.35rem;
    text-transform: uppercase;
}

.origin-why-box p {
    color: #374151;
    font-size: 0.88rem;
    line-height: 1.5;
    margin: 0;
}

.origin-rail {
    display: grid;
    gap: 1rem;
    align-content: start;
}

.origin-map-card {
    min-height: 190px;
    overflow: hidden;
    padding: 0;
    position: relative;
}

.origin-map-label {
    background: rgba(255, 255, 255, 0.92);
    border-radius: 0.28rem;
    left: 0.9rem;
    padding: 0.42rem 0.55rem;
    position: absolute;
    top: 0.85rem;
    z-index: 1;
}

.origin-map-surface {
    background:
        radial-gradient(circle at 22% 20%, rgba(255, 255, 255, 0.22), transparent 18%),
        repeating-radial-gradient(circle at 40% 30%, rgba(255, 255, 255, 0.08) 0, rgba(255, 255, 255, 0.08) 1px, transparent 1px, transparent 7px),
        linear-gradient(180deg, #9ca3a1, #7d8583);
    height: 100%;
    min-height: 190px;
}

.origin-flavor-card h3,
.origin-demand-card h3,
.origin-trade-card h3,
.origin-harvest-card h3,
.origin-lots-card h3 {
    color: #111827;
    font-size: 0.96rem;
    font-weight: 800;
    margin: 0 0 1rem;
    text-transform: uppercase;
}

.origin-flavor-list {
    display: grid;
    gap: 0.95rem;
}

.origin-flavor-meta {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.35rem;
}

.origin-flavor-meta span {
    color: #111827;
    font-size: 0.86rem;
}

.origin-flavor-meta strong {
    color: #172431;
    font-size: 0.86rem;
}

.origin-flavor-track {
    background: #edf1f4;
    border-radius: 999px;
    height: 6px;
    overflow: hidden;
}

.origin-flavor-fill {
    background: #0d5a41;
    height: 100%;
}

.origin-demand-box {
    display: grid;
    gap: 0.8rem;
}

.origin-demand-pill {
    align-items: center;
    background: #e6f8f0;
    display: flex;
    gap: 0.6rem;
    justify-content: space-between;
    padding: 0.75rem 0.8rem;
}

.origin-demand-pill span {
    color: #506176;
    font-size: 0.78rem;
}

.origin-demand-pill strong {
    color: #0d5a41;
    font-size: 0.98rem;
    font-weight: 700;
}

.origin-trade-list {
    display: grid;
    gap: 0.8rem;
}

.origin-trade-row {
    align-items: center;
    border: 1px solid #eef2f5;
    border-radius: 0.45rem;
    display: flex;
    justify-content: space-between;
    padding: 0.8rem 0.85rem;
}

.origin-trade-row.is-green {
    background: #eefcf5;
}

.origin-trade-row.is-amber {
    background: #fff7ef;
}

.origin-trade-row strong {
    color: #111827;
    display: block;
    font-size: 0.9rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
}

.origin-trade-row span {
    color: #64748b;
    font-size: 0.78rem;
}

.origin-harvest-track {
    background: #f6f8fb;
    border-radius: 0.45rem;
    min-height: 78px;
    padding: 1rem 1.15rem;
}

.origin-harvest-months {
    color: #5a6472;
    display: grid;
    font-size: 0.72rem;
    grid-template-columns: repeat(9, minmax(0, 1fr));
    margin-bottom: 0.7rem;
    text-align: center;
}

.origin-harvest-bars {
    height: 20px;
    position: relative;
}

.origin-harvest-bar {
    align-items: center;
    border-radius: 999px;
    color: #fff;
    display: inline-flex;
    font-size: 0.64rem;
    font-weight: 700;
    height: 14px;
    justify-content: center;
    position: absolute;
    text-transform: uppercase;
}

.origin-harvest-bar.is-fly {
    background: #9e8a72;
    left: 25%;
    top: 0;
    width: 17%;
}

.origin-harvest-bar.is-main {
    background: #0d5a41;
    left: 75%;
    top: 0;
    width: 25%;
}

.origin-lots-head {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

.origin-lot-tags {
    display: flex;
    gap: 0.6rem;
}

.origin-lot-tag {
    background: #eff2f5;
    border-radius: 999px;
    color: #344054;
    font-size: 0.76rem;
    padding: 0.4rem 0.8rem;
}

.origin-lot-tag.is-active {
    background: #c8f5dc;
    color: #0d5a41;
}

.origin-table {
    border-collapse: collapse;
    width: 100%;
}

.origin-table th {
    border-bottom: 1px solid #edf1f4;
    color: #5a6472;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 1rem 0.8rem;
    text-align: left;
    text-transform: uppercase;
}

.origin-table td {
    border-bottom: 1px solid #f0f4f7;
    color: #172431;
    font-size: 0.92rem;
    padding: 1rem 0.8rem;
}

.origin-table tbody tr:last-child td {
    border-bottom: 0;
}

.origin-score-tag {
    background: #e6f3ef;
    border-radius: 0.3rem;
    color: #0d5a41;
    display: inline-block;
    font-weight: 700;
    padding: 0.28rem 0.5rem;
}

.origin-table a {
    color: #0d5a41;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    text-transform: uppercase;
}

.origin-floating-chat {
    align-items: end;
    bottom: 1.35rem;
    display: grid;
    gap: 0.6rem;
    position: fixed;
    right: 1.35rem;
    z-index: 20;
}

.origin-chat-bubble {
    background: #fff;
    border: 1px solid #edf1f4;
    border-radius: 999px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    color: #111827;
    font-size: 0.8rem;
    padding: 0.8rem 1rem;
}

.origin-chat-fab {
    align-items: center;
    background: #0d5a41;
    border: 0;
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(13, 90, 65, 0.22);
    color: #fff;
    display: inline-flex;
    height: 58px;
    justify-content: center;
    justify-self: end;
    width: 58px;
}

@media (max-width: 1199.98px) {
    .origin-layout,
    .origin-grid {
        grid-template-columns: 1fr;
    }

    .origin-sidebar {
        min-height: auto;
    }

    .origin-sidebar-bottom {
        margin-top: 2rem;
    }

    .origin-stats-strip,
    .origin-card-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .origin-header,
    .origin-toolbar {
        align-items: start;
        flex-direction: column;
        gap: 1rem;
    }

    .origin-brand-row {
        align-items: start;
        flex-direction: column;
        gap: 0.8rem;
    }

    .origin-toolbar-actions,
    .origin-filter-bar,
    .origin-stats-strip,
    .origin-card-grid {
        grid-template-columns: 1fr;
        flex-wrap: wrap;
    }

    .origin-stats-strip {
        display: grid;
    }
}

@media (max-width: 767.98px) {
    .origin-header,
    .origin-main {
        padding-inline: 1rem;
    }

    .origin-sidebar {
        padding-inline: 0.8rem;
    }

    .origin-toolbar h1 {
        font-size: 1.6rem;
    }

    .origin-stats-strip,
    .origin-card-grid {
        grid-template-columns: 1fr;
    }

    .origin-lots-head {
        align-items: start;
        flex-direction: column;
    }

    .origin-floating-chat {
        right: 0.8rem;
    }
}
</style>
