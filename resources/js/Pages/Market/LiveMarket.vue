<script setup>
import {
    Bell,
    Checked,
    Connection,
    DataAnalysis,
    Document,
    InfoFilled,
    List,
    Money,
    Opportunity,
    Search,
    Setting,
    ShoppingCartFull,
    ShoppingTrolley,
    TrendCharts,
    WarnTriangleFilled,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const topActions = [
    { label: 'Buy Coffee', icon: ShoppingCartFull, tone: 'green' },
    { label: 'Sell Coffee', icon: Money, tone: 'peach' },
    { label: 'Set Alert', icon: Bell, tone: 'ghost' },
    { label: 'Ask Advisor', icon: Opportunity, tone: 'ghost' },
];

const filters = [
    'Coffee Type',
    'Origin',
    'Quality Score',
];

const filterPills = [
    { label: 'Tokenised', active: true },
    { label: 'Export Ready', active: false },
];

const tickerItems = [
    { name: 'Uganda Robusta', change: '↑ 4.2%', price: '$1.92' },
    { name: 'Rwenzori Arabica', change: '↑ 5.0%', price: '$4.15' },
    { name: 'Brazil Arabica', change: '↓ 1.1%', price: '$3.88' },
    { name: 'Vietnam Robusta', change: '↑ 2.8%', price: '$1.75' },
    { name: 'Kenya AA', change: '↑ 0.5%', price: '$6.20' },
];

const statCards = [
    { label: 'Market Status', value: 'Bullish', accent: 'trend' },
    { label: 'Avg. Price', value: '$2.85', muted: ' ' },
    { label: '24H Volume', value: '1.2M kg' },
    { label: 'Volatility', value: 'Medium', accent: 'soft' },
    { label: 'Peak Demand', value: 'UAE' },
    { label: 'Export Lots', value: '342' },
];

const watchlistItems = [
    { name: 'Uganda R1', type: 'Robusta', price: '$1.92', move: '+4.2%', tone: 'up' },
    { name: 'Rwenz. Arabica', type: 'Specialty', price: '$4.15', move: '+5.0%', tone: 'up' },
    { name: 'Brazil Santos', type: 'Arabica', price: '$3.88', move: '-1.1%', tone: 'down' },
];

const demandOrigins = [
    { code: 'UG', name: 'Uganda', level: '92%' },
    { code: 'ET', name: 'Ethiopia', level: '85%' },
];

const chartTabs = ['Price', 'Volume', 'Demand', 'Forecast'];

const chartCandles = [
    { x: 4, h: 42, dir: 'up' }, { x: 7, h: 68, dir: 'down' }, { x: 10, h: 55, dir: 'up' },
    { x: 13, h: 82, dir: 'down' }, { x: 16, h: 47, dir: 'up' }, { x: 19, h: 71, dir: 'down' },
    { x: 22, h: 53, dir: 'up' }, { x: 25, h: 88, dir: 'down' }, { x: 28, h: 36, dir: 'up' },
    { x: 31, h: 64, dir: 'down' }, { x: 34, h: 48, dir: 'up' }, { x: 37, h: 91, dir: 'down' },
    { x: 40, h: 58, dir: 'up' }, { x: 43, h: 44, dir: 'down' }, { x: 46, h: 76, dir: 'up' },
    { x: 49, h: 52, dir: 'down' }, { x: 52, h: 66, dir: 'up' }, { x: 55, h: 84, dir: 'down' },
    { x: 58, h: 57, dir: 'up' }, { x: 61, h: 72, dir: 'down' }, { x: 64, h: 49, dir: 'up' },
    { x: 67, h: 62, dir: 'down' }, { x: 70, h: 78, dir: 'up' }, { x: 73, h: 50, dir: 'down' },
    { x: 76, h: 69, dir: 'up' }, { x: 79, h: 60, dir: 'down' }, { x: 82, h: 74, dir: 'up' },
    { x: 85, h: 46, dir: 'down' },
];

const chartVolumes = [18, 30, 22, 46, 28, 50, 32, 56, 24, 38, 21, 62];

const liveLots = [
    {
        name: 'Mount Elgon AA',
        origin: 'Uganda',
        type: 'Arabica',
        price: '$5.20',
        volume: '4,200',
        status: 'Live',
        statusTone: 'live',
        imageTone: 'brown',
    },
    {
        name: 'Kyoga Robusta R1',
        origin: 'Uganda',
        type: 'Robusta',
        price: '$1.88',
        volume: '12,500',
        status: 'Pending',
        statusTone: 'pending',
        imageTone: 'olive',
    },
];

const orderBookAsks = [
    { price: '$2.88', qty: '400', total: '1,152', tone: 'ask', width: '68%' },
    { price: '$2.87', qty: '820', total: '2,353', tone: 'ask', width: '84%' },
];

const orderBookBids = [
    { price: '$2.84', qty: '1,250', total: '3,550', tone: 'bid', width: '93%' },
    { price: '$2.83', qty: '300', total: '849', tone: 'bid', width: '42%' },
];

const regionalTags = [
    { label: 'UAE', note: 'Extreme', tone: 'green' },
    { label: 'DE', note: 'High', tone: 'dark' },
    { label: 'USA', note: 'Moderate', tone: 'peach' },
];

const regionalRoutes = [
    { route: 'UG Robusta', market: 'UAE', roi: '+12% ROI' },
    { route: 'RW Arabica', market: 'GER', roi: '+8% ROI' },
];

const riskToggles = [
    { label: 'Price Spike Alert (> $3.00)', enabled: true },
    { label: 'Export Policy Changes', enabled: false },
    { label: 'Major Volume Demand (>10 kg)', enabled: true },
];
</script>

<template>
    <OuterLayout title="Live Coffee Market">
        <section class="terminal-page">
            <div class="terminal-shell">
                <section class="terminal-hero">
                    <div class="terminal-hero__head">
                        <div>
                            <h1>Live Coffee Market</h1>
                            <p>Real-time prices, demand, supply, and verified coffee lots</p>
                        </div>

                        <div class="terminal-actions">
                            <button
                                v-for="item in topActions"
                                :key="item.label"
                                type="button"
                                class="terminal-action"
                                :class="`is-${item.tone}`"
                            >
                                <el-icon><component :is="item.icon" /></el-icon>
                                <span>{{ item.label }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="terminal-filters">
                        <span class="terminal-filter-label">Filters:</span>

                        <button
                            v-for="item in filters"
                            :key="item"
                            type="button"
                            class="terminal-filter-chip"
                        >
                            {{ item }}<sup>*</sup>
                        </button>

                        <button
                            v-for="item in filterPills"
                            :key="item.label"
                            type="button"
                            class="terminal-filter-pill"
                            :class="{ 'is-active': item.active }"
                        >
                            <el-icon v-if="item.active"><Checked /></el-icon>
                            <span>{{ item.label }}</span>
                        </button>

                        <div class="terminal-range-switch">
                            <button type="button" class="is-active">1H</button>
                            <button type="button">24H</button>
                            <button type="button">7D</button>
                        </div>
                    </div>
                </section>

                <section class="terminal-ticker">
                    <div v-for="item in tickerItems" :key="item.name" class="terminal-ticker__item">
                        <span>{{ item.name }}</span>
                        <strong>{{ item.change }}</strong>
                        <em>{{ item.price }}</em>
                    </div>
                </section>

                <section class="terminal-stats">
                    <article v-for="item in statCards" :key="item.label" class="terminal-stat-card">
                        <span>{{ item.label }}</span>
                        <div class="terminal-stat-card__value" :class="{ 'is-soft': item.accent === 'soft' }">
                            <strong>{{ item.value }}</strong>
                            <i v-if="item.accent === 'trend'">↗</i>
                            <small v-if="item.muted"></small>
                        </div>
                    </article>
                </section>

                <section class="terminal-main-grid">
                    <div class="terminal-left-rail">
                        <article class="terminal-card terminal-watchlist">
                            <div class="terminal-card__head">
                                <h2>My Watchlist</h2>
                                <button type="button">⋮</button>
                            </div>

                            <div class="watchlist-list">
                                <div v-for="item in watchlistItems" :key="item.name" class="watchlist-row">
                                    <div>
                                        <strong>{{ item.name }}</strong>
                                        <span>{{ item.type }}</span>
                                    </div>
                                    <div class="watchlist-row__price">
                                        <strong>{{ item.price }}</strong>
                                        <em :class="`is-${item.tone}`">{{ item.move }}</em>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <article class="terminal-card terminal-demand-card">
                            <h2>High Demand Origins</h2>

                            <div class="demand-origin-list">
                                <div v-for="item in demandOrigins" :key="item.code" class="demand-origin-row">
                                    <span class="demand-origin-row__code">{{ item.code }}</span>
                                    <div class="demand-origin-row__body">
                                        <div class="demand-origin-row__head">
                                            <strong>{{ item.name }}</strong>
                                            <span>{{ item.level }}</span>
                                        </div>
                                        <div class="demand-origin-row__track">
                                            <span :style="{ width: item.level }"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <article class="terminal-card terminal-chart-card">
                        <div class="terminal-card__head terminal-card__head--chart">
                            <div class="chart-segments">
                                <button
                                    v-for="(item, index) in chartTabs"
                                    :key="item"
                                    type="button"
                                    :class="{ 'is-active': index === 0 }"
                                >
                                    {{ item }}
                                </button>
                            </div>

                            <div class="chart-head-tools">
                                <button type="button"><TrendCharts /></button>
                                <button type="button"><Setting /></button>
                            </div>
                        </div>

                        <div class="chart-surface">
                            <div class="chart-grid-lines">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>

                            <div class="chart-waves">
                                <span class="wave wave-a"></span>
                                <span class="wave wave-b"></span>
                                <span class="wave wave-c"></span>
                            </div>

                            <div class="chart-candles">
                                <div
                                    v-for="(item, index) in chartCandles"
                                    :key="index"
                                    class="chart-candle"
                                    :class="`is-${item.dir}`"
                                    :style="{ left: `${item.x}%`, height: `${item.h}%` }"
                                >
                                    <span></span>
                                </div>
                            </div>

                            <div class="chart-volumes">
                                <span
                                    v-for="(item, index) in chartVolumes"
                                    :key="index"
                                    :style="{ height: `${item}%` }"
                                ></span>
                            </div>

                            <div class="chart-watermark">
                                <strong>LIVE</strong>
                                <span>ANALYTICS</span>
                                <p>Real-time forecasting engine active</p>
                            </div>
                        </div>
                    </article>

                    <div class="terminal-right-rail">
                        <article class="trade-signal-card">
                            <div class="trade-signal-card__eyebrow">
                                <span>✦</span>
                                <strong>AI Trade Signal</strong>
                            </div>

                            <div class="trade-signal-card__body">
                                <h2>Strong<br>BUY</h2>
                                <div class="trade-signal-card__badge">
                                    <TrendCharts />
                                </div>
                            </div>

                            <span class="trade-signal-card__meta">92% Confidence • UAE Market</span>
                            <p>Demand from UAE roasting hubs expected to spike by 15% in next 48h. Secure lots now.</p>
                        </article>

                        <article class="terminal-card quick-trade-card">
                            <h2>Quick Trade Box</h2>

                            <div class="quick-trade-switch">
                                <button type="button" class="is-active">Buy</button>
                                <button type="button">Sell</button>
                            </div>

                            <label>Quantity (kg)</label>
                            <div class="quick-trade-input">1000</div>

                            <label>Limit Price ($/kg)</label>
                            <div class="quick-trade-input">2.85</div>

                            <div class="quick-trade-total">
                                <span>Total Est.</span>
                                <strong>$2,850.00</strong>
                            </div>

                            <button type="button" class="quick-trade-submit">Confirm Order</button>

                            <button type="button" class="quick-trade-fab">
                                <el-icon><ShoppingTrolley /></el-icon>
                            </button>
                        </article>
                    </div>
                </section>

                <section class="terminal-card lots-card">
                    <div class="terminal-card__head terminal-card__head--table">
                        <h2>Live Coffee Lots</h2>
                        <button type="button" class="table-link">View Full Inventory →</button>
                    </div>

                    <div class="lots-table">
                        <div class="lots-table__head">
                            <span>Lot Name</span>
                            <span>Origin</span>
                            <span>Type</span>
                            <span>Price</span>
                            <span>Vol (kg)</span>
                            <span>Status</span>
                            <span>Certifications</span>
                            <span>Action</span>
                        </div>

                        <div v-for="item in liveLots" :key="item.name" class="lots-table__row">
                            <div class="lots-table__lot">
                                <span class="lot-thumb" :class="`is-${item.imageTone}`"></span>
                                <strong>{{ item.name }}</strong>
                            </div>
                            <span>{{ item.origin }}</span>
                            <span class="type-pill" :class="`is-${item.type.toLowerCase()}`">{{ item.type }}</span>
                            <strong>{{ item.price }}</strong>
                            <span>{{ item.volume }}</span>
                            <span class="status-pill" :class="`is-${item.statusTone}`">{{ item.status }}</span>
                            <span class="cert-icons">
                                <Checked />
                                <Document />
                                <Connection />
                            </span>
                            <button type="button" class="trade-chip">TRADE</button>
                        </div>
                    </div>
                </section>

                <section class="terminal-bottom-grid">
                    <article class="terminal-card terminal-bottom-card">
                        <h2>Order Book</h2>

                        <div class="order-book-head">
                            <span>Price</span>
                            <span>Qty</span>
                            <span>Total</span>
                        </div>

                        <div v-for="item in orderBookAsks" :key="`${item.price}-${item.qty}`" class="order-book-row is-ask">
                            <span>{{ item.price }}</span>
                            <div class="order-book-row__bar"><i :style="{ width: item.width }"></i></div>
                            <span>{{ item.qty }}</span>
                            <span>{{ item.total }}</span>
                        </div>

                        <div class="order-book-spread">SPREAD: 0.01 (0.35%)</div>

                        <div v-for="item in orderBookBids" :key="`${item.price}-${item.qty}`" class="order-book-row is-bid">
                            <span>{{ item.price }}</span>
                            <div class="order-book-row__bar"><i :style="{ width: item.width }"></i></div>
                            <span>{{ item.qty }}</span>
                            <span>{{ item.total }}</span>
                        </div>
                    </article>

                    <article class="terminal-card terminal-bottom-card">
                        <h2>Regional Analytics</h2>

                        <span class="bottom-card-label">Demand Heatmap</span>
                        <div class="regional-tags">
                            <div v-for="item in regionalTags" :key="item.label" class="regional-tag" :class="`is-${item.tone}`">
                                <strong>{{ item.label }}</strong>
                                <span>{{ item.note }}</span>
                            </div>
                        </div>

                        <span class="bottom-card-label">Live Opportunities</span>
                        <div class="regional-opps">
                            <div v-for="item in regionalRoutes" :key="item.route" class="regional-opp-row">
                                <div>
                                    <strong>{{ item.route }}</strong>
                                    <span>→</span>
                                    <strong>{{ item.market }}</strong>
                                </div>
                                <em>{{ item.roi }}</em>
                            </div>
                        </div>
                    </article>

                    <article class="terminal-card terminal-bottom-card risk-card">
                        <div class="terminal-card__head">
                            <h2>Risks &amp; Smart Alerts</h2>
                            <button type="button">⌁</button>
                        </div>

                        <div class="risk-banner">
                            <el-icon><WarnTriangleFilled /></el-icon>
                            <div>
                                <strong>Weather Risk: High</strong>
                                <p>El Niño effects expected in East Africa. Supply may tighten.</p>
                            </div>
                        </div>

                        <div class="risk-toggles">
                            <div v-for="item in riskToggles" :key="item.label" class="risk-toggle-row">
                                <span>{{ item.label }}</span>
                                <button type="button" class="risk-toggle" :class="{ 'is-on': item.enabled }">
                                    <i></i>
                                </button>
                            </div>
                        </div>
                    </article>
                </section>
            </div>
        </section>
    </OuterLayout>
</template>

<style scoped>
.terminal-page {
    min-height: 100vh;
    background: #ffffff;
    color: #16202b;
    font-family: 'Manrope', sans-serif;
}

.terminal-shell {
    max-width: 1240px;
    margin: 0 auto;
    padding: 24px 28px 42px;
}

.terminal-hero__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
}

.terminal-hero h1 {
    margin: 0;
    font-size: 25px;
    line-height: 1;
    font-weight: 800;
    letter-spacing: -0.05em;
    color: #12181f;
}

.terminal-hero p {
    margin: 8px 0 0;
    font-size: 18px;
    color: #546171;
}

.terminal-actions {
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
}

.terminal-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 14px;
    border-radius: 10px;
    border: 1px solid transparent;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
}

.terminal-action.is-green {
    background: #0a5a3b;
    color: #ffffff;
}

.terminal-action.is-peach {
    background: #ffd8b5;
    color: #23180f;
}

.terminal-action.is-ghost {
    background: #f3f5f8;
    color: #1a2430;
}

.terminal-action :deep(svg) {
    width: 16px;
    height: 16px;
}

.terminal-filters {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 26px;
}

.terminal-filter-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    padding: 0 12px;
    border-radius: 999px;
    background: #f3f5f7;
    font-size: 14px;
    color: #9ba6b1;
}

.terminal-filter-chip {
    background: transparent;
    border: 0;
    padding: 0 10px;
    min-height: 30px;
    font-size: 15px;
    color: #141d28;
    cursor: pointer;
}

.terminal-filter-chip sup {
    font-size: 10px;
    top: -0.2em;
}

.terminal-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 36px;
    padding: 0 14px;
    border-radius: 999px;
    border: 0;
    background: #eff2f4;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    color: #1a2532;
    cursor: pointer;
}

.terminal-filter-pill.is-active {
    background: #aef0c9;
}

.terminal-range-switch {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 4px;
    border-radius: 999px;
    background: #f3f5f7;
}

.terminal-range-switch button {
    min-width: 44px;
    min-height: 30px;
    border: 0;
    border-radius: 999px;
    background: transparent;
    font-size: 12px;
    font-weight: 700;
    color: #687383;
    cursor: pointer;
}

.terminal-range-switch .is-active {
    background: #ffffff;
    color: #151d27;
    box-shadow: 0 2px 8px rgba(16, 24, 40, 0.08);
}

.terminal-ticker {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 18px;
    margin-top: 22px;
    padding: 8px 16px;
    border-radius: 0;
    background: #0a6644;
    color: #dff7ea;
}

.terminal-ticker__item {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    font-size: 14px;
    white-space: nowrap;
}

.terminal-ticker__item strong {
    font-weight: 700;
}

.terminal-ticker__item em {
    color: #effcf5;
    font-style: normal;
}

.terminal-stats {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 14px;
    margin-top: 30px;
}

.terminal-stat-card,
.terminal-card,
.trade-signal-card {
    background: #ffffff;
    border: 1px solid #eef2f4;
    border-radius: 18px;
    box-shadow: 0 14px 28px rgba(16, 24, 40, 0.05);
}

.terminal-stat-card {
    padding: 16px 18px;
}

.terminal-stat-card > span {
    display: block;
    font-size: 13px;
    color: #697584;
}

.terminal-stat-card__value {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
}

.terminal-stat-card__value strong {
    font-size: 21px;
    font-weight: 700;
    color: #111a23;
}

.terminal-stat-card__value i {
    font-style: normal;
    font-size: 18px;
    color: #0a7e51;
}

.terminal-stat-card__value.is-soft strong {
    color: #8e6c41;
}

.terminal-main-grid {
    display: grid;
    grid-template-columns: 198px minmax(0, 1fr) 280px;
    gap: 28px;
    margin-top: 28px;
}

.terminal-left-rail,
.terminal-right-rail {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.terminal-card {
    padding: 22px;
}

.terminal-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.terminal-card__head h2,
.terminal-demand-card h2,
.quick-trade-card h2,
.terminal-bottom-card h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #151d27;
}

.terminal-card__head button {
    border: 0;
    background: transparent;
    color: #7e8794;
    cursor: pointer;
}

.watchlist-list {
    display: grid;
    gap: 18px;
    margin-top: 18px;
}

.watchlist-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.watchlist-row strong,
.demand-origin-row__head strong,
.lots-table__lot strong,
.regional-opp-row strong {
    display: block;
    font-size: 15px;
    font-weight: 600;
    color: #131b24;
}

.watchlist-row span {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    color: #8b96a2;
}

.watchlist-row__price {
    text-align: right;
}

.watchlist-row__price em {
    display: block;
    margin-top: 2px;
    font-size: 13px;
    font-style: normal;
}

.watchlist-row__price .is-up {
    color: #0a8d5a;
}

.watchlist-row__price .is-down {
    color: #cf5648;
}

.terminal-demand-card {
    padding-top: 18px;
}

.demand-origin-list {
    display: grid;
    gap: 18px;
    margin-top: 18px;
}

.demand-origin-row {
    display: flex;
    gap: 12px;
}

.demand-origin-row__code {
    width: 30px;
    height: 30px;
    border-radius: 10px;
    background: #f3f5f7;
    display: grid;
    place-items: center;
    font-size: 14px;
    font-weight: 700;
    color: #222b36;
    flex-shrink: 0;
}

.demand-origin-row__body {
    flex: 1;
}

.demand-origin-row__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.demand-origin-row__head span {
    font-size: 12px;
    font-weight: 700;
    color: #16222d;
}

.demand-origin-row__track {
    height: 4px;
    margin-top: 8px;
    border-radius: 999px;
    background: #dce5df;
    overflow: hidden;
}

.demand-origin-row__track span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: #0a6945;
}

.terminal-chart-card {
    padding: 22px;
}

.terminal-card__head--chart {
    margin-bottom: 20px;
}

.chart-segments {
    display: inline-flex;
    gap: 4px;
    padding: 4px;
    border-radius: 10px;
    background: #f2f5f8;
}

.chart-segments button {
    min-height: 30px;
    padding: 0 14px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    font-size: 14px;
    color: #37414d;
    cursor: pointer;
}

.chart-segments .is-active {
    background: #ffffff;
    box-shadow: 0 0 0 1px #e7edf2 inset;
}

.chart-head-tools {
    display: inline-flex;
    gap: 10px;
}

.chart-head-tools button {
    width: 26px;
    height: 26px;
    border: 0;
    background: transparent;
    color: #687585;
    cursor: pointer;
}

.chart-surface {
    position: relative;
    min-height: 530px;
    border-radius: 10px;
    overflow: hidden;
    background:
        linear-gradient(180deg, rgba(72, 84, 90, 0.58) 0%, rgba(123, 136, 145, 0.34) 100%),
        linear-gradient(180deg, #8f979c 0%, #727f86 44%, #8b9399 100%);
}

.chart-grid-lines {
    position: absolute;
    inset: 0;
    display: grid;
    grid-template-rows: repeat(5, 1fr);
}

.chart-grid-lines span {
    border-top: 1px solid rgba(209, 236, 249, 0.18);
}

.chart-waves {
    position: absolute;
    inset: 0;
}

.wave {
    position: absolute;
    left: 0;
    right: 0;
    border-top: 2px solid;
    border-radius: 50%;
    opacity: 0.7;
}

.wave-a {
    top: 38%;
    height: 210px;
    border-color: rgba(234, 144, 156, 0.68);
    transform: skewY(-8deg);
}

.wave-b {
    top: 44%;
    height: 250px;
    border-color: rgba(134, 215, 229, 0.58);
    transform: skewY(-11deg);
}

.wave-c {
    top: 56%;
    height: 190px;
    border-color: rgba(221, 208, 134, 0.42);
    transform: skewY(-6deg);
}

.chart-candles {
    position: absolute;
    inset: 34px 18px 84px;
}

.chart-candle {
    position: absolute;
    bottom: 0;
    width: 10px;
}

.chart-candle::before {
    content: '';
    position: absolute;
    left: 4px;
    top: -26px;
    bottom: -18px;
    width: 1px;
    background: rgba(255, 255, 255, 0.36);
}

.chart-candle span {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 18px;
    border-radius: 4px;
}

.chart-candle.is-up span {
    height: 34px;
    background: rgba(131, 243, 240, 0.7);
}

.chart-candle.is-down span {
    height: 46px;
    background: rgba(249, 165, 175, 0.72);
}

.chart-volumes {
    position: absolute;
    left: 26px;
    right: 26px;
    bottom: 28px;
    height: 90px;
    display: flex;
    align-items: flex-end;
    gap: 8px;
}

.chart-volumes span {
    flex: 1;
    border-radius: 2px 2px 0 0;
    background: rgba(111, 227, 255, 0.38);
}

.chart-watermark {
    position: absolute;
    inset: 0;
    display: grid;
    place-content: center;
    text-align: center;
    color: rgba(26, 55, 64, 0.18);
}

.chart-watermark strong,
.chart-watermark span {
    display: block;
    font-size: 72px;
    line-height: 0.95;
    font-weight: 800;
    letter-spacing: -0.05em;
}

.chart-watermark p {
    margin: 18px 0 0;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.trade-signal-card {
    padding: 20px;
    background: #08553a;
    color: #ffffff;
    box-shadow: 0 18px 28px rgba(7, 45, 30, 0.16);
}

.trade-signal-card__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #d6fff1;
}

.trade-signal-card__body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 24px;
}

.trade-signal-card__body h2 {
    margin: 0;
    font-size: 57px;
    line-height: 0.96;
    font-weight: 800;
    letter-spacing: -0.05em;
}

.trade-signal-card__badge {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: rgba(23, 130, 95, 0.72);
    display: grid;
    place-items: center;
}

.trade-signal-card__meta {
    display: block;
    margin-top: 18px;
    font-size: 15px;
    color: #bfe8d7;
}

.trade-signal-card p {
    margin: 10px 0 0;
    font-size: 14px;
    line-height: 1.7;
    color: #d8f4e8;
}

.quick-trade-card {
    position: relative;
}

.quick-trade-switch {
    display: inline-flex;
    width: 100%;
    margin-top: 18px;
    padding: 4px;
    border-radius: 10px;
    background: #f1f4f6;
}

.quick-trade-switch button {
    flex: 1;
    min-height: 34px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    font-size: 14px;
    color: #202935;
    cursor: pointer;
}

.quick-trade-switch .is-active {
    background: #0a5a3b;
    color: #ffffff;
}

.quick-trade-card label {
    display: block;
    margin-top: 22px;
    font-size: 12px;
    text-transform: uppercase;
    color: #6f7a86;
}

.quick-trade-input {
    display: flex;
    align-items: center;
    min-height: 40px;
    margin-top: 8px;
    padding: 0 12px;
    border-radius: 8px;
    background: #f3f5f7;
    font-size: 28px;
    font-weight: 700;
    color: #131b24;
}

.quick-trade-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 22px;
    font-size: 15px;
}

.quick-trade-total strong {
    font-size: 25px;
    font-weight: 700;
}

.quick-trade-submit {
    width: 100%;
    min-height: 40px;
    margin-top: 16px;
    border: 0;
    border-radius: 10px;
    background: #0a5a3b;
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    cursor: pointer;
}

.quick-trade-fab {
    position: absolute;
    right: -18px;
    bottom: 94px;
    width: 50px;
    height: 50px;
    border: 0;
    border-radius: 14px;
    background: #09573b;
    color: #ffffff;
    display: grid;
    place-items: center;
    box-shadow: 0 10px 20px rgba(8, 44, 29, 0.18);
    cursor: pointer;
}

.lots-card {
    margin-top: 32px;
    padding: 0;
    overflow: hidden;
}

.terminal-card__head--table {
    padding: 18px 24px;
}

.table-link {
    border: 0;
    background: transparent;
    font-size: 14px;
    color: #17212c;
    cursor: pointer;
}

.lots-table__head,
.lots-table__row {
    display: grid;
    grid-template-columns: 1.8fr 0.9fr 0.9fr 0.8fr 0.8fr 0.9fr 1fr 0.7fr;
    gap: 14px;
    align-items: center;
    padding: 16px 24px;
}

.lots-table__head {
    border-top: 1px solid #edf2f5;
    background: #fafbfc;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #7a8594;
}

.lots-table__row {
    border-top: 1px solid #edf2f5;
    font-size: 14px;
    color: #18212d;
}

.lots-table__lot {
    display: flex;
    align-items: center;
    gap: 14px;
}

.lot-thumb {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    flex-shrink: 0;
}

.lot-thumb.is-brown {
    background:
        radial-gradient(circle at 30% 30%, #3b1c10 0 18%, transparent 19%),
        radial-gradient(circle at 65% 38%, #5c2c16 0 16%, transparent 17%),
        linear-gradient(135deg, #7a3e23 0%, #4f2412 100%);
}

.lot-thumb.is-olive {
    background:
        radial-gradient(circle at 38% 36%, #d8f0bd 0 18%, transparent 19%),
        radial-gradient(circle at 64% 44%, #a3d488 0 16%, transparent 17%),
        linear-gradient(135deg, #2e4821 0%, #5f7d42 100%);
}

.type-pill,
.status-pill,
.trade-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    min-height: 22px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.type-pill.is-arabica {
    background: #ffd8b5;
    color: #6b4729;
}

.type-pill.is-robusta {
    background: #eceef1;
    color: #25303d;
}

.status-pill.is-live {
    color: #0a7b4f;
}

.status-pill.is-live::before,
.status-pill.is-pending::before {
    content: '';
    width: 6px;
    height: 6px;
    margin-right: 6px;
    border-radius: 999px;
    background: currentColor;
}

.status-pill.is-pending {
    color: #8a673f;
}

.cert-icons {
    display: inline-flex;
    gap: 8px;
    color: #1d2c39;
}

.trade-chip {
    border: 0;
    background: #eef2f4;
    color: #1c2733;
    cursor: pointer;
}

.terminal-bottom-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 28px;
    margin-top: 30px;
}

.terminal-bottom-card {
    min-height: 282px;
}

.order-book-head {
    display: grid;
    grid-template-columns: 0.8fr 1.1fr 0.7fr 0.8fr;
    gap: 12px;
    margin-top: 22px;
    font-size: 12px;
    text-transform: uppercase;
    color: #7a8593;
}

.order-book-row {
    display: grid;
    grid-template-columns: 0.8fr 1.1fr 0.7fr 0.8fr;
    gap: 12px;
    align-items: center;
    margin-top: 12px;
    font-size: 14px;
}

.order-book-row.is-ask span:first-child {
    color: #d24e44;
}

.order-book-row.is-bid span:first-child {
    color: #0b6e47;
}

.order-book-row__bar {
    height: 22px;
    border-radius: 4px;
    background: #f3f5f7;
    overflow: hidden;
}

.order-book-row__bar i {
    display: block;
    height: 100%;
}

.order-book-row.is-ask .order-book-row__bar i {
    background: rgba(244, 208, 208, 0.55);
}

.order-book-row.is-bid .order-book-row__bar i {
    background: rgba(220, 233, 228, 0.8);
}

.order-book-spread {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    margin: 16px 0;
    border-radius: 8px;
    background: #eff2f4;
    font-size: 13px;
    font-weight: 700;
    color: #38414c;
}

.bottom-card-label {
    display: block;
    margin-top: 22px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: #778390;
}

.regional-tags {
    display: flex;
    gap: 10px;
    margin-top: 14px;
}

.regional-tag {
    min-width: 62px;
    padding: 12px 12px 10px;
    border-radius: 10px;
}

.regional-tag strong,
.regional-tag span {
    display: block;
}

.regional-tag strong {
    font-size: 16px;
    font-weight: 700;
}

.regional-tag span {
    margin-top: 6px;
    font-size: 12px;
}

.regional-tag.is-green {
    background: #0a5f3f;
    color: #ffffff;
}

.regional-tag.is-dark {
    background: #2b6c4f;
    color: #ffffff;
}

.regional-tag.is-peach {
    background: #ffd8b5;
    color: #65472a;
}

.regional-opps {
    display: grid;
    gap: 12px;
    margin-top: 14px;
}

.regional-opp-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 10px;
    background: #f3f5f7;
}

.regional-opp-row div {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.regional-opp-row em {
    font-style: normal;
    font-size: 13px;
    font-weight: 700;
    color: #0a6845;
}

.risk-card .terminal-card__head {
    margin-bottom: 22px;
}

.risk-banner {
    display: flex;
    gap: 12px;
    padding: 16px 16px 15px;
    border-radius: 16px;
    border: 1px solid #f3c8c4;
    background: #fff4f2;
    color: #cb3c32;
}

.risk-banner p {
    margin: 6px 0 0;
    font-size: 13px;
    line-height: 1.5;
    color: #d2847d;
}

.risk-toggles {
    display: grid;
    gap: 16px;
    margin-top: 22px;
}

.risk-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    font-size: 14px;
    color: #151d27;
}

.risk-toggle {
    width: 34px;
    height: 20px;
    padding: 2px;
    border: 0;
    border-radius: 999px;
    background: #e3e7eb;
    cursor: pointer;
}

.risk-toggle i {
    display: block;
    width: 16px;
    height: 16px;
    border-radius: 999px;
    background: #ffffff;
    transition: transform 0.2s ease;
}

.risk-toggle.is-on {
    background: #0a5c3c;
}

.risk-toggle.is-on i {
    transform: translateX(14px);
}

@media (max-width: 1200px) {
    .terminal-hero__head {
        flex-direction: column;
        align-items: flex-start;
    }

    .terminal-actions {
        flex-wrap: wrap;
    }

    .terminal-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .terminal-main-grid,
    .terminal-bottom-grid {
        grid-template-columns: 1fr;
    }

    .terminal-ticker {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .terminal-main-grid {
        gap: 22px;
    }
}

@media (max-width: 900px) {
    .terminal-shell {
        padding: 18px 16px 34px;
    }

    .terminal-hero__head,
    .terminal-filters,
    .terminal-actions,
    .terminal-card__head--table,
    .terminal-card__head {
        flex-direction: column;
        align-items: flex-start;
    }

    .terminal-actions {
        width: 100%;
    }

    .terminal-action {
        width: 100%;
        justify-content: center;
    }

    .terminal-stats {
        grid-template-columns: 1fr;
    }

    .terminal-ticker {
        grid-template-columns: 1fr;
    }

    .terminal-range-switch {
        margin-left: 0;
    }

    .lots-table__head {
        display: none;
    }

    .lots-table__row {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .trade-signal-card__body h2 {
        font-size: 40px;
    }

    .chart-surface {
        min-height: 420px;
    }
}

@media (max-width: 640px) {
    .terminal-shell {
        padding: 16px 12px 28px;
    }

    .terminal-filters {
        gap: 10px;
    }

    .terminal-filter-chip,
    .terminal-filter-pill,
    .terminal-filter-label {
        width: 100%;
        justify-content: center;
    }

    .terminal-range-switch {
        width: 100%;
        justify-content: center;
    }

    .terminal-card,
    .terminal-stat-card,
    .trade-signal-card {
        border-radius: 14px;
    }

    .terminal-card,
    .trade-signal-card,
    .terminal-chart-card {
        padding: 18px;
    }

    .trade-signal-card__body h2 {
        font-size: 34px;
    }
}
</style>
