<script setup>
import { Head } from '@inertiajs/vue3';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const topTabs = ['Region', 'Coffee Type', 'Category'];

const sideLinks = [
    { label: 'Terminal', icon: 'dashboard', active: false },
    { label: 'Market Data', icon: 'monitoring', active: true },
    { label: 'Global News', icon: 'newspaper', active: false },
    { label: 'Supply Chain', icon: 'local_shipping', active: false },
    { label: 'Risk Analytics', icon: 'security_update_good', active: false },
    { label: 'Archive', icon: 'inventory_2', active: false },
];

const sideFooterLinks = [
    { label: 'Support', icon: 'help_outline' },
    { label: 'API Reference', icon: 'code' },
];

const tickerItems = [
    { text: 'Robusta prices rise 4.2%', tone: 'green' },
    { text: 'Heavy rain reported in Brazil', tone: 'red' },
    { text: 'UAE demand for Ugandan coffee increasing', tone: 'amber' },
    { text: 'EU traceability requirements affecting exporters', tone: 'red' },
];

const summaryCards = [
    { label: 'Price Direction', value: '+2.4%', tone: 'green', icon: 'bars' },
    { label: 'Weather Risk', value: 'Critical', tone: 'red', icon: 'warning' },
    { label: 'Demand Trend', value: 'Accel.', tone: 'dark', icon: 'trending_up' },
    { label: 'Supply Status', value: 'Shortage', tone: 'amber', icon: 'inventory' },
    { label: 'Quality Avg', value: '92.4', tone: 'dark', icon: 'star' },
    { label: 'Export Risk', value: 'Medium', tone: 'amber', icon: 'gavel' },
];

const feedCards = [
    {
        age: '14M AGO • BRAZIL',
        sentiment: 'Positive Sentiment',
        tone: 'positive',
        title: 'Frost warnings in Minas Gerais trigger Arabica price volatility',
        body: 'Met services confirm a cold front moving towards key regions, potentially impacting late harvest cherry development.',
        impactLabel: 'Critical Impact',
        why: 'Likely to drive short-term futures spikes by up to 8.5 cents/lb.',
        impact: '9.2 / 10',
    },
    {
        age: '2H AGO • EU/VIETNAM',
        sentiment: 'Negative Sentiment',
        tone: 'negative',
        title: 'New EUDR compliance hurdles for Vietnamese Robusta exporters',
        body: 'Regulatory update requires geolocation data for all smallholder plots by Q4, risking export delays for unverified volumes.',
        impactLabel: 'Supply Risk',
        why: '30% of current supply chain lacks necessary digital mapping data.',
        impact: '7.8 / 10',
    },
    {
        age: '5H AGO • UAE',
        sentiment: 'Positive Sentiment',
        tone: 'positive',
        title: 'DMCC reports 15% growth in specialty coffee trade corridors',
        body: 'Strong growth driven by specialty demand from MENA region and new logistics corridors for East African producers.',
        impactLabel: 'Market Opportunity',
        why: 'Signals massive trade opportunity for Ethiopian and Ugandan Arabicas.',
        impact: '6.5 / 10',
    },
];

const weatherItems = [
    { country: 'Uganda', note: '24°C | 85% PRECIP EXPECTED', risk: 'Low Risk', tone: 'green', icon: 'rainy' },
    { country: 'Brazil', note: '32°C | EXTREME DROUGHT', risk: 'High Risk', tone: 'red', icon: 'wb_sunny' },
    { country: 'Vietnam', note: '19°C | UNSEASONAL FROST', risk: 'Med Risk', tone: 'amber', icon: 'cloudy_snowing' },
];

const analyticsBars = [24, 50, 40, 76, 60, 35, 92, 66, 100];

const buyerRequirements = [
    { market: 'UAE', coffee: 'Specialty Arabica', score: '85+', moisture: '11.5%', cert: 'Halal/Organic', demand: 'Surging', tone: 'green' },
    { market: 'Germany', coffee: 'Fine Robusta', score: '82+', moisture: '12.0%', cert: 'Fairtrade/RF', demand: 'Stable', tone: 'gray' },
    { market: 'Japan', coffee: 'Micro-lot Arabica', score: '88+', moisture: '11.0%', cert: 'JAS Organic', demand: 'High', tone: 'green' },
];

const deskAlerts = [
    { label: 'Price Volatility (>5%)', enabled: true },
    { label: 'New Compliance Rules', enabled: true },
    { label: 'Trade Matches', enabled: false },
];

const regionalDesks = [
    'East Africa: +256 (0) 41 455...',
    'South America: +55 (11) 3444...',
    'EMEA Central: +971 4 433...',
];
</script>

<template>
    <OuterLayout title="Market Intelligence">
        <Head>
            <link
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
                rel="stylesheet"
            />
        </Head>

        <section class="mi-page">
            <div class="mi-shell">
             
                <div class="mi-layout">
                    <aside class="mi-sidebar">
                        <div class="mi-sidebar__head">
                            <h2>Institutional Exchange</h2>
                            <span>Terminal v4.2</span>
                        </div>

                        <nav class="mi-sidebar__nav">
                            <button
                                v-for="item in sideLinks"
                                :key="item.label"
                                type="button"
                                class="mi-sidebar__link"
                                :class="{ 'is-active': item.active }"
                            >
                                <span class="material-symbols-outlined">{{ item.icon }}</span>
                                <span>{{ item.label }}</span>
                            </button>
                        </nav>

                        <div class="mi-sidebar__bottom">
                            <button type="button" class="mi-live-btn">
                                <span class="material-symbols-outlined">bolt</span>
                                <span>Live Intelligence</span>
                            </button>

                            <div class="mi-sidebar__meta">
                                <button
                                    v-for="item in sideFooterLinks"
                                    :key="item.label"
                                    type="button"
                                    class="mi-sidebar__meta-link"
                                >
                                    <span class="material-symbols-outlined">{{ item.icon }}</span>
                                    <span>{{ item.label }}</span>
                                </button>
                            </div>
                        </div>
                    </aside>

                    <main class="mi-content">
                        <section class="mi-summary-strip">
                            <div class="mi-summary-strip__text">
                                <span class="material-symbols-outlined">auto_awesome</span>
                                <p>Robusta demand is rising in UAE while global supply remains tight.</p>
                            </div>

                            <div class="mi-summary-strip__badges">
                                <span class="is-primary">Bullish</span>
                                <span class="is-soft">High Demand</span>
                                <span class="is-neutral">Impact: Med</span>
                            </div>
                        </section>

                        <section class="mi-ticker" aria-label="Breaking market updates">
                            <div class="mi-ticker__track">
                                <div class="mi-ticker__group">
                                    <div
                                        v-for="item in tickerItems"
                                        :key="`first-${item.text}`"
                                        class="mi-ticker__item"
                                    >
                                        <i :class="`is-${item.tone}`"></i>
                                        <span>{{ item.text }}</span>
                                    </div>
                                </div>
                                <div class="mi-ticker__group" aria-hidden="true">
                                    <div
                                        v-for="item in tickerItems"
                                        :key="`second-${item.text}`"
                                        class="mi-ticker__item"
                                    >
                                        <i :class="`is-${item.tone}`"></i>
                                        <span>{{ item.text }}</span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="mi-content__inner">
                            <section class="mi-kpi-grid">
                                <article v-for="item in summaryCards" :key="item.label" class="mi-kpi-card">
                                    <p>{{ item.label }}</p>
                                    <div class="mi-kpi-card__value">
                                        <strong :class="`is-${item.tone}`">{{ item.value }}</strong>

                                        <div v-if="item.icon === 'bars'" class="mi-spark-bars" aria-hidden="true">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                        <span v-else class="material-symbols-outlined" :class="`is-${item.tone}`">
                                            {{ item.icon }}
                                        </span>
                                    </div>
                                </article>
                            </section>

                            <section class="mi-feed">
                                <div class="mi-feed__head">
                                    <h1>Market Intelligence Feed</h1>
                                    <div class="mi-feed__actions">
                                        <button type="button" class="is-primary">Set Alerts</button>
                                        <button type="button">Export</button>
                                    </div>
                                </div>

                                <div class="mi-story-grid">
                                    <article v-for="item in feedCards" :key="item.title" class="mi-story-card">
                                        <div class="mi-story-card__top">
                                            <span class="mi-story-card__age">{{ item.age }}</span>
                                            <span class="mi-story-card__sentiment">
                                                <i :class="`is-${item.tone}`"></i>
                                                {{ item.sentiment }}
                                            </span>
                                        </div>

                                        <h2>{{ item.title }}</h2>
                                        <p class="mi-story-card__body">{{ item.body }}</p>

                                        <div class="mi-story-card__impact">
                                            <span>{{ item.impactLabel }}</span>
                                            <p>{{ item.why }}</p>
                                        </div>

                                        <div class="mi-story-card__footer">
                                            <div>
                                                <small>Impact Score</small>
                                                <strong>{{ item.impact }}</strong>
                                            </div>
                                            <button type="button">
                                                View Data
                                                <span class="material-symbols-outlined">arrow_right_alt</span>
                                            </button>
                                        </div>
                                    </article>
                                </div>

                                <div class="mi-analysis-grid">
                                    <section class="mi-weather">
                                        <h2>Weather &amp; Crop Risk</h2>

                                        <div class="mi-weather__list">
                                            <article v-for="item in weatherItems" :key="item.country" class="mi-weather-card">
                                                <div class="mi-weather-card__icon" :class="`is-${item.tone}`">
                                                    <span class="material-symbols-outlined">{{ item.icon }}</span>
                                                </div>

                                                <div class="mi-weather-card__body">
                                                    <div class="mi-weather-card__head">
                                                        <strong>{{ item.country }}</strong>
                                                        <em :class="`is-${item.tone}`">{{ item.risk }}</em>
                                                    </div>
                                                    <span>{{ item.note }}</span>
                                                </div>
                                            </article>
                                        </div>

                                        <article class="mi-recommendation">
                                            <span>AI Recommendation</span>
                                            <h3>List export-ready Robusta for UAE immediately to capitalize on supply gap.</h3>
                                            <button type="button">Execute Strategy</button>
                                        </article>
                                    </section>

                                    <section class="mi-analytics-card">
                                        <div class="mi-analytics-card__head">
                                            <div>
                                                <h2>Market Analytics</h2>
                                                <p>Institutional Price &amp; Volume Tracking</p>
                                            </div>

                                            <div class="mi-analytics-card__tabs">
                                                <button type="button" class="is-active">Arabica</button>
                                                <button type="button">Robusta</button>
                                                <button type="button">Uganda</button>
                                            </div>
                                        </div>

                                        <div class="mi-chart">
                                            <div class="mi-chart__bars">
                                                <span
                                                    v-for="(item, index) in analyticsBars"
                                                    :key="index"
                                                    :style="{ height: `${item}%` }"
                                                ></span>
                                            </div>

                                            <div class="mi-chart__spot">
                                                <small>Current Spot</small>
                                                <strong>$182.45</strong>
                                                <em>+1.2% Day</em>
                                            </div>
                                        </div>

                                        <div class="mi-analytics-card__meta">
                                            <div>
                                                <span>Volume 24h</span>
                                                <strong>1.2M Bags</strong>
                                            </div>
                                            <div>
                                                <span>Open Interest</span>
                                                <strong>45.2K Contracts</strong>
                                            </div>
                                            <div>
                                                <span>Influence</span>
                                                <strong class="is-green">Bullish</strong>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </section>

                            <section class="mi-table-card">
                                <div class="mi-table-card__head">
                                    <h2>Global Buyer Requirements</h2>
                                    <button type="button">
                                        View All Markets
                                        <span class="material-symbols-outlined">open_in_new</span>
                                    </button>
                                </div>

                                <div class="mi-table-wrap">
                                    <table class="mi-table">
                                        <thead>
                                            <tr>
                                                <th>Market</th>
                                                <th>Preferred Coffee</th>
                                                <th>Min Score</th>
                                                <th>Moisture %</th>
                                                <th>Certification</th>
                                                <th>Demand Level</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in buyerRequirements" :key="item.market">
                                                <td>
                                                    <div class="mi-market-cell">
                                                        <span class="mi-market-cell__flag"></span>
                                                        <strong>{{ item.market }}</strong>
                                                    </div>
                                                </td>
                                                <td>{{ item.coffee }}</td>
                                                <td class="is-strong">{{ item.score }}</td>
                                                <td>{{ item.moisture }}</td>
                                                <td class="is-cert">{{ item.cert }}</td>
                                                <td>
                                                    <span class="mi-demand-chip" :class="`is-${item.tone}`">{{ item.demand }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <section class="mi-footer-panels">
                                <article class="mi-footer-panels__brand">
                                    <h2>Bean Origin Intelligence</h2>
                                    <p>Institutional-grade data terminal for global coffee supply chains, logistics, and market analytics.</p>
                                    <div class="mi-footer-panels__links">
                                        <a href="#">Privacy Policy</a>
                                        <a href="#">Terms of Service</a>
                                        <a href="#">Methodology</a>
                                    </div>
                                </article>

                                <article class="mi-footer-panels__card">
                                    <h3>Smart Alerts</h3>
                                    <div class="mi-toggle-list">
                                        <div v-for="item in deskAlerts" :key="item.label" class="mi-toggle-row">
                                            <span>{{ item.label }}</span>
                                            <button type="button" class="mi-toggle" :class="{ 'is-on': item.enabled }" aria-hidden="true">
                                                <i></i>
                                            </button>
                                        </div>
                                    </div>
                                </article>

                                <article class="mi-footer-panels__card">
                                    <h3>Regional Desks</h3>
                                    <div class="mi-desk-list">
                                        <span v-for="item in regionalDesks" :key="item">{{ item }}</span>
                                    </div>
                                </article>
                            </section>
                        </div>
                    </main>
                </div>

                <aside class="mi-advisor">
                    <div class="mi-advisor__head">
                        <div class="mi-advisor__identity">
                            <div class="mi-advisor__avatar">
                                <span class="material-symbols-outlined">smart_toy</span>
                            </div>
                            <div>
                                <strong>Bean Origin Advisor</strong>
                                <span><i></i> Online &amp; Analyzing</span>
                            </div>
                        </div>

                        <button type="button" aria-label="Close advisor">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="mi-advisor__body">
                        <div class="mi-advisor__message">
                            Good morning. Brazil frost warnings have triggered a 4% spike in Arabica futures. Would you like to see how this impacts your current Ugandan Robusta export strategy?
                        </div>

                        <div class="mi-advisor__reply">
                            Yes, show me the UAE arbitrage opportunity.
                        </div>
                    </div>

                    <div class="mi-advisor__footer">
                        <div class="mi-advisor__chips">
                            <button type="button">UAE Entry</button>
                            <button type="button">EUDR Compliance</button>
                        </div>

                        <label class="mi-advisor__input">
                            <input type="text" placeholder="Ask advisor..." />
                            <button type="button" aria-label="Send">
                                <span class="material-symbols-outlined">send</span>
                            </button>
                        </label>
                    </div>
                </aside>
            </div>
        </section>
    </OuterLayout>
</template>

<style scoped>
.mi-page {
    --mi-primary: #065f46;
    --mi-primary-soft: #e8f3ef;
    --mi-primary-faint: #dff3ea;
    --mi-amber: #e6a41d;
    --mi-red: #d63c34;
    --mi-text: #191c1e;
    --mi-muted: #68727d;
    --mi-soft-text: #8c96a1;
    --mi-line: rgba(190, 201, 194, 0.38);
    --mi-panel: #ffffff;
    --mi-page: #f8f9fa;
    --mi-surface: #f2f4f6;
    min-height: 100vh;
    background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
    color: var(--mi-text);
    font-family: 'Manrope', sans-serif;
}

.mi-shell {
    position: relative;
    width: 100%;
    background: #ffffff;
}

.mi-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    min-height: 72px;
    padding: 0 28px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 1px 0 rgba(224, 227, 229, 0.9);
    backdrop-filter: blur(14px);
}

.mi-topbar__left,
.mi-topbar__right,
.mi-brand,
.mi-tabs,
.mi-topbar__actions {
    display: flex;
    align-items: center;
}

.mi-topbar__left {
    gap: 28px;
}

.mi-brand {
    gap: 10px;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #0f2f27;
}

.mi-brand__icon {
    font-size: 20px;
    color: #0c67ff;
}

.mi-tabs {
    gap: 28px;
}

.mi-tabs__button {
    min-height: 28px;
    padding: 0 0 4px;
    border: 0;
    border-bottom: 2px solid transparent;
    background: transparent;
    font-size: 14px;
    color: #54606b;
    cursor: pointer;
}

.mi-tabs__button.is-active {
    color: #11191f;
    border-bottom-color: #0f2a20;
}

.mi-topbar__right {
    gap: 16px;
}

.mi-search {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-width: 220px;
    min-height: 40px;
    padding: 0 16px;
    border-radius: 999px;
    background: #f3f4f5;
    color: #838d98;
}

.mi-search input,
.mi-advisor__input input {
    width: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    font: inherit;
    color: var(--mi-text);
}

.mi-search input::placeholder,
.mi-advisor__input input::placeholder {
    color: #8d97a1;
}

.mi-topbar__actions {
    gap: 4px;
}

.mi-topbar__actions button,
.mi-sidebar__link,
.mi-sidebar__meta-link,
.mi-tabs__button,
.mi-live-btn,
.mi-feed__actions button,
.mi-story-card__footer button,
.mi-analytics-card__tabs button,
.mi-table-card__head button,
.mi-toggle,
.mi-advisor__head button,
.mi-advisor__chips button,
.mi-advisor__input button,
.mi-sidebar__meta-link {
    font-family: inherit;
}

.mi-topbar__actions button {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border: 0;
    background: transparent;
    color: #163128;
    cursor: pointer;
}

.mi-layout {
    display: grid;
    grid-template-columns: 218px minmax(0, 1fr);
}

.mi-sidebar {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 72px);
    padding: 18px 18px 22px 20px;
    background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
    box-shadow: inset -1px 0 0 rgba(224, 227, 229, 0.82);
}

.mi-sidebar__head h2,
.mi-footer-panels__brand h2 {
    margin: 0;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.mi-sidebar__head h2 {
    color: #5f6a73;
}

.mi-sidebar__head span {
    display: block;
    margin-top: 3px;
    font-size: 10px;
    color: #8b959e;
}

.mi-sidebar__nav {
    display: grid;
    gap: 6px;
    margin-top: 34px;
}

.mi-sidebar__link {
    display: flex;
    align-items: center;
    gap: 14px;
    min-height: 48px;
    padding: 0 10px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    font-size: 15px;
    font-weight: 500;
    color: #55606c;
    cursor: pointer;
    text-align: left;
}

.mi-sidebar__link.is-active {
    background: #e2f3eb;
    color: #12372d;
}

.mi-sidebar__bottom {
    margin-top: auto;
}

.mi-live-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 48px;
    width: 100%;
    border: 0;
    border-radius: 14px;
    background: var(--mi-primary);
    box-shadow: 0 12px 22px rgba(6, 95, 70, 0.2);
    font-size: 14px;
    font-weight: 800;
    color: #ffffff;
    cursor: pointer;
}

.mi-sidebar__meta {
    display: grid;
    gap: 8px;
    margin-top: 18px;
    padding-left: 8px;
}

.mi-sidebar__meta-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 0;
    background: transparent;
    padding: 0;
    font-size: 11px;
    color: #7e8892;
    cursor: pointer;
    text-align: left;
}

.mi-content {
    min-width: 0;
    background: #ffffff;
}

.mi-summary-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    min-height: 30px;
    padding: 0 20px;
    background: #eaf5ef;
    box-shadow: inset 0 -1px 0 rgba(219, 236, 228, 0.9);
}

.mi-summary-strip__text,
.mi-summary-strip__badges,
.mi-story-card__sentiment,
.mi-weather-card__head,
.mi-table-card__head button,
.mi-advisor__identity,
.mi-advisor__input {
    display: flex;
    align-items: center;
}

.mi-summary-strip__text {
    gap: 10px;
    min-width: 0;
}

.mi-summary-strip__text span {
    font-size: 14px;
    color: #0d63ff;
}

.mi-summary-strip__text p {
    margin: 0;
    font-size: 12px;
    font-weight: 700;
    color: #0f63ff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mi-summary-strip__badges {
    gap: 6px;
}

.mi-summary-strip__badges span,
.mi-weather-card__head em,
.mi-demand-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 18px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: 10px;
    line-height: 1;
}

.mi-summary-strip__badges .is-primary {
    background: var(--mi-primary);
    color: #ffffff;
}

.mi-summary-strip__badges .is-soft {
    background: #d9f1df;
    color: #1f5d42;
}

.mi-summary-strip__badges .is-neutral {
    background: #ecebea;
    color: #3e4348;
}

.mi-ticker {
    overflow: hidden;
    background: #1f2124;
    color: #ffffff;
}

.mi-ticker__track {
    display: flex;
    width: max-content;
    animation: mi-ticker 34s linear infinite;
}

.mi-ticker__group {
    display: flex;
    align-items: center;
}

.mi-ticker__item {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-width: max-content;
    min-height: 32px;
    padding: 0 26px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: -0.01em;
    text-transform: uppercase;
}

.mi-ticker__item i {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #8be0bb;
}

.mi-ticker__item i.is-red {
    background: #e85b53;
}

.mi-ticker__item i.is-amber {
    background: #e6aa38;
}

.mi-content__inner {
    padding: 22px 20px 64px;
    background: linear-gradient(180deg, #fafafa 0%, #ffffff 22%);
}

.mi-kpi-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 14px;
}

.mi-kpi-card,
.mi-story-card,
.mi-weather-card,
.mi-analytics-card,
.mi-table-card {
    background: #ffffff;
    box-shadow:
        0 1px 0 rgba(233, 236, 238, 1),
        0 12px 24px rgba(25, 28, 30, 0.04);
}

.mi-kpi-card {
    padding: 14px 16px;
    border-radius: 16px;
}

.mi-kpi-card p,
.mi-story-card__impact span,
.mi-story-card__footer small,
.mi-analytics-card__meta span,
.mi-table thead th,
.mi-footer-panels__card h3 {
    margin: 0;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #8b949d;
}

.mi-kpi-card__value {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 10px;
}

.mi-kpi-card__value strong {
    font-size: 17px;
    font-weight: 800;
    color: #12191f;
}

.mi-kpi-card__value strong.is-green,
.mi-kpi-card__value .material-symbols-outlined.is-green,
.mi-analytics-card__meta strong.is-green {
    color: var(--mi-primary);
}

.mi-kpi-card__value strong.is-red,
.mi-kpi-card__value .material-symbols-outlined.is-red {
    color: var(--mi-red);
}

.mi-kpi-card__value strong.is-amber,
.mi-kpi-card__value .material-symbols-outlined.is-amber {
    color: var(--mi-amber);
}

.mi-kpi-card__value .material-symbols-outlined {
    font-size: 22px;
    color: #707b86;
}

.mi-spark-bars {
    display: flex;
    align-items: flex-end;
    gap: 3px;
    width: 18px;
    height: 20px;
}

.mi-spark-bars span {
    width: 4px;
    border-radius: 999px;
    background: #61c29b;
}

.mi-spark-bars span:nth-child(1) {
    height: 8px;
    background: #d1ece0;
}

.mi-spark-bars span:nth-child(2) {
    height: 12px;
    background: #a8ddc4;
}

.mi-spark-bars span:nth-child(3) {
    height: 18px;
    background: #14a36f;
}

.mi-feed {
    margin-top: 34px;
}

.mi-feed__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
    padding: 0 4px;
}

.mi-feed__head h1,
.mi-weather h2,
.mi-analytics-card__head h2,
.mi-table-card__head h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #171d22;
}

.mi-feed__actions {
    display: flex;
    gap: 10px;
}

.mi-feed__actions button,
.mi-table-card__head button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 28px;
    padding: 0 16px;
    border-radius: 10px;
    border: 1px solid rgba(111, 121, 115, 0.45);
    background: #ffffff;
    font-size: 12px;
    font-weight: 700;
    color: #5a6169;
    cursor: pointer;
}

.mi-feed__actions .is-primary {
    border-color: transparent;
    background: var(--mi-primary);
    color: #ffffff;
}

.mi-story-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}

.mi-story-card {
    display: flex;
    flex-direction: column;
    min-height: 324px;
    padding: 18px 20px;
    border-radius: 18px;
}

.mi-story-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.mi-story-card__age {
    display: inline-flex;
    align-items: center;
    min-height: 20px;
    padding: 0 8px;
    border-radius: 999px;
    background: #f4f5f6;
    font-size: 9px;
    font-weight: 800;
    color: #7e8791;
}

.mi-story-card__sentiment {
    gap: 6px;
    font-size: 10px;
    font-weight: 700;
    color: #2b3137;
}

.mi-story-card__sentiment i {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #12a66f;
}

.mi-story-card__sentiment i.is-negative {
    background: #e14841;
}

.mi-story-card h2 {
    margin: 16px 0 12px;
    font-size: 17px;
    line-height: 1.32;
    font-weight: 700;
    color: #132027;
}

.mi-story-card__body,
.mi-story-card__impact p,
.mi-footer-panels__brand p,
.mi-desk-list span,
.mi-toggle-row span {
    margin: 0;
    font-size: 13px;
    line-height: 1.45;
    color: #6a747f;
}

.mi-story-card__impact {
    margin-top: auto;
    padding: 14px;
    border-radius: 12px;
    background: #f7f8f8;
}

.mi-story-card__impact span {
    color: #2f73ff;
}

.mi-story-card__impact p {
    margin-top: 4px;
    color: #41474d;
}

.mi-story-card__footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-top: 18px;
    padding-top: 16px;
    box-shadow: inset 0 1px 0 rgba(236, 238, 240, 0.9);
}

.mi-story-card__footer strong {
    display: block;
    margin-top: 6px;
    font-size: 16px;
    font-weight: 700;
    color: #1a1f24;
}

.mi-story-card__footer button {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: 0;
    background: transparent;
    padding: 0;
    font-size: 13px;
    font-weight: 700;
    color: var(--mi-primary);
    cursor: pointer;
}

.mi-story-card__footer button span {
    font-size: 18px;
}

.mi-analysis-grid {
    display: grid;
    grid-template-columns: 0.92fr 1.88fr;
    gap: 20px;
    margin-top: 20px;
}

.mi-weather__list {
    display: grid;
    gap: 14px;
    margin-top: 14px;
}

.mi-weather-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border-radius: 16px;
}

.mi-weather-card__icon {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: #f4f6f7;
    color: var(--mi-primary);
    flex-shrink: 0;
}

.mi-weather-card__icon.is-red {
    background: #ffe8e6;
    color: var(--mi-red);
}

.mi-weather-card__icon.is-amber {
    background: #f4f5f6;
    color: #7c8289;
}

.mi-weather-card__body {
    flex: 1;
}

.mi-weather-card__head {
    justify-content: space-between;
    gap: 12px;
}

.mi-weather-card__head strong {
    font-size: 15px;
    font-weight: 700;
    color: #161d22;
}

.mi-weather-card__body span {
    display: block;
    margin-top: 3px;
    font-size: 10px;
    font-weight: 700;
    color: #707a84;
}

.mi-weather-card__head em {
    font-style: normal;
}

.mi-weather-card__head em.is-green {
    background: #dff4e7;
    color: #266b49;
}

.mi-weather-card__head em.is-red {
    background: #ef3d3d;
    color: #ffffff;
}

.mi-weather-card__head em.is-amber {
    background: #f8e9ba;
    color: #866016;
}

.mi-recommendation {
    position: relative;
    margin-top: 14px;
    padding: 18px 18px 20px;
    border-radius: 16px;
    background: linear-gradient(180deg, #0b5a40 0%, #084a35 100%);
    box-shadow: 0 18px 28px rgba(6, 95, 70, 0.22);
    color: #ffffff;
    overflow: hidden;
}

.mi-recommendation::after {
    content: 'auto_awesome';
    position: absolute;
    right: -14px;
    top: -10px;
    font-family: 'Material Symbols Outlined', sans-serif;
    font-size: 118px;
    opacity: 0.08;
    line-height: 1;
}

.mi-recommendation > span {
    position: relative;
    z-index: 1;
    display: block;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    opacity: 0.82;
}

.mi-recommendation h3 {
    position: relative;
    z-index: 1;
    margin: 12px 0 18px;
    font-size: 17px;
    line-height: 1.28;
    font-weight: 800;
}

.mi-recommendation button {
    position: relative;
    z-index: 1;
    width: 100%;
    min-height: 34px;
    border: 0;
    border-radius: 10px;
    background: #ffffff;
    font-size: 13px;
    font-weight: 700;
    color: #2a2a2a;
    cursor: pointer;
}

.mi-analytics-card {
    padding: 18px 20px 20px;
    border-radius: 18px;
}

.mi-analytics-card__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.mi-analytics-card__head p {
    margin: 4px 0 0;
    font-size: 13px;
    color: #76808a;
}

.mi-analytics-card__tabs {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    border-radius: 12px;
    background: #f3f5f6;
}

.mi-analytics-card__tabs button {
    min-height: 24px;
    padding: 0 10px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    font-size: 10px;
    font-weight: 800;
    color: #4d5760;
    cursor: pointer;
}

.mi-analytics-card__tabs button.is-active {
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(25, 28, 30, 0.08);
}

.mi-chart {
    position: relative;
    display: flex;
    align-items: flex-end;
    min-height: 300px;
    margin-top: 18px;
    padding: 20px 22px 0;
    box-shadow: inset 0 -1px 0 rgba(232, 235, 237, 1);
}

.mi-chart__bars {
    display: grid;
    grid-template-columns: repeat(9, minmax(0, 1fr));
    align-items: end;
    gap: 12px;
    width: 100%;
    min-height: 228px;
}

.mi-chart__bars span {
    border-radius: 8px 8px 0 0;
    background: #ebf7f2;
}

.mi-chart__bars span:nth-child(2) {
    background: #d8f1e3;
}

.mi-chart__bars span:nth-child(3) {
    background: #d1efe0;
}

.mi-chart__bars span:nth-child(4) {
    background: #99e7c1;
}

.mi-chart__bars span:nth-child(5) {
    background: #35ca8b;
}

.mi-chart__bars span:nth-child(6) {
    background: #dcefe4;
}

.mi-chart__bars span:nth-child(7) {
    background: #0f9467;
}

.mi-chart__bars span:nth-child(8) {
    background: #2dbb82;
}

.mi-chart__bars span:nth-child(9) {
    background: #0d5a41;
}

.mi-chart__spot {
    position: absolute;
    left: 53%;
    top: 50%;
    transform: translate(-50%, -50%);
    min-width: 126px;
    padding: 10px 14px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.92);
    box-shadow:
        0 1px 0 rgba(233, 236, 238, 1),
        0 20px 40px rgba(25, 28, 30, 0.08);
    text-align: center;
    backdrop-filter: blur(10px);
}

.mi-chart__spot small {
    display: block;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #8b949d;
}

.mi-chart__spot strong {
    display: block;
    margin-top: 4px;
    font-size: 25px;
    font-weight: 800;
    color: #113025;
}

.mi-chart__spot em {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 8px;
    min-height: 22px;
    padding: 0 10px;
    border-radius: 999px;
    background: #daf3e4;
    font-style: normal;
    font-size: 12px;
    color: var(--mi-primary);
}

.mi-analytics-card__meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-top: 18px;
}

.mi-analytics-card__meta strong {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    font-weight: 700;
    color: #20262b;
}

.mi-table-card {
    margin-top: 22px;
    border-radius: 18px;
    overflow: hidden;
}

.mi-table-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    box-shadow: inset 0 -1px 0 rgba(233, 236, 238, 1);
}

.mi-table-card__head button {
    border: 0;
    background: transparent;
    padding: 0;
    color: #0f63ff;
}

.mi-table-wrap {
    overflow-x: auto;
}

.mi-table {
    width: 100%;
    border-collapse: collapse;
}

.mi-table thead th {
    padding: 14px 20px;
    text-align: left;
    background: #f7f8f8;
    white-space: nowrap;
}

.mi-table tbody td {
    padding: 13px 20px;
    box-shadow: inset 0 -1px 0 rgba(236, 238, 240, 0.95);
    font-size: 14px;
    color: #20262b;
    white-space: nowrap;
}

.mi-market-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mi-market-cell__flag {
    width: 22px;
    height: 12px;
    border-radius: 3px;
    background: #f0f2f3;
}

.mi-table .is-strong {
    font-weight: 700;
}

.mi-table .is-cert {
    font-weight: 700;
    color: var(--mi-primary);
}

.mi-demand-chip.is-green {
    background: #d8f3df;
    color: #1b6a45;
}

.mi-demand-chip.is-gray {
    background: #ececed;
    color: #30353a;
}

.mi-footer-panels {
    display: grid;
    grid-template-columns: 1.35fr 0.9fr 0.9fr;
    gap: 36px;
    margin-top: 56px;
    padding: 0 10px 12px;
}

.mi-footer-panels__brand {
    max-width: 360px;
}

.mi-footer-panels__brand h2 {
    color: #0b4f3a;
}

.mi-footer-panels__brand p {
    margin-top: 20px;
    line-height: 1.65;
}

.mi-footer-panels__links {
    display: flex;
    gap: 24px;
    margin-top: 32px;
}

.mi-footer-panels__links a {
    font-size: 12px;
    font-weight: 700;
    color: var(--mi-primary);
    text-decoration: none;
}

.mi-footer-panels__card h3 {
    margin-bottom: 18px;
    color: #41484f;
}

.mi-toggle-list,
.mi-desk-list {
    display: grid;
    gap: 12px;
}

.mi-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.mi-toggle-row span {
    color: #5d6771;
}

.mi-toggle {
    display: inline-flex;
    align-items: center;
    width: 30px;
    height: 16px;
    padding: 2px;
    border: 0;
    border-radius: 999px;
    background: #d5d9de;
    cursor: default;
}

.mi-toggle i {
    width: 12px;
    height: 12px;
    border-radius: 999px;
    background: #ffffff;
    transition: transform 0.2s ease;
}

.mi-toggle.is-on {
    background: #2d6de9;
}

.mi-toggle.is-on i {
    transform: translateX(14px);
}

.mi-advisor {
    position: fixed;
    right: 28px;
    bottom: 28px;
    z-index: 50;
    width: 300px;
    overflow: hidden;
    border-radius: 18px;
    background: #ffffff;
    box-shadow:
        0 1px 0 rgba(233, 236, 238, 1),
        0 24px 48px rgba(25, 28, 30, 0.14);
}

.mi-advisor__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    background: #0a5a40;
    color: #ffffff;
}

.mi-advisor__identity {
    gap: 12px;
}

.mi-advisor__avatar {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
}

.mi-advisor__head strong {
    display: block;
    font-size: 13px;
    font-weight: 800;
}

.mi-advisor__head span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 3px;
    font-size: 9px;
    color: rgba(255, 255, 255, 0.8);
}

.mi-advisor__head span i {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #40d388;
}

.mi-advisor__head button {
    display: grid;
    place-items: center;
    width: 28px;
    height: 28px;
    border: 0;
    background: transparent;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
}

.mi-advisor__body {
    display: grid;
    gap: 16px;
    padding: 14px;
    background: #f4f5f6;
}

.mi-advisor__message,
.mi-advisor__reply {
    font-size: 13px;
    line-height: 1.55;
    box-shadow: 0 8px 16px rgba(25, 28, 30, 0.05);
}

.mi-advisor__message {
    padding: 14px;
    border-radius: 16px 16px 16px 4px;
    background: #ffffff;
    color: #23292f;
}

.mi-advisor__reply {
    width: 78%;
    margin-left: auto;
    padding: 14px;
    border-radius: 16px 16px 4px 16px;
    background: #e7f7ec;
    color: #184932;
}

.mi-advisor__footer {
    padding: 12px 14px 14px;
    background: #ffffff;
    box-shadow: inset 0 1px 0 rgba(233, 236, 238, 1);
}

.mi-advisor__chips {
    display: flex;
    gap: 6px;
    margin-bottom: 10px;
    overflow-x: auto;
}

.mi-advisor__chips button {
    min-height: 22px;
    padding: 0 10px;
    border: 1px solid rgba(6, 95, 70, 0.8);
    border-radius: 999px;
    background: #ffffff;
    font-size: 9px;
    font-weight: 800;
    color: var(--mi-primary);
    white-space: nowrap;
    cursor: pointer;
}

.mi-advisor__input {
    gap: 8px;
    min-height: 40px;
    padding: 0 12px;
    border-radius: 999px;
    background: #f4f5f6;
}

.mi-advisor__input button {
    display: grid;
    place-items: center;
    width: 28px;
    height: 28px;
    border: 0;
    background: transparent;
    color: #1d67ff;
    cursor: pointer;
}

@keyframes mi-ticker {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-50%);
    }
}

@media (max-width: 1199px) {
    .mi-kpi-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .mi-story-grid,
    .mi-analysis-grid,
    .mi-footer-panels {
        grid-template-columns: 1fr;
    }

    .mi-advisor {
        right: 20px;
        bottom: 20px;
    }
}

@media (max-width: 991px) {
    .mi-layout {
        grid-template-columns: 1fr;
    }

    .mi-sidebar {
        min-height: auto;
        padding-bottom: 18px;
        box-shadow: inset 0 -1px 0 rgba(224, 227, 229, 0.82);
    }

    .mi-sidebar__nav {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mi-sidebar__bottom {
        margin-top: 24px;
    }

    .mi-story-grid {
        grid-template-columns: 1fr;
    }

    .mi-advisor {
        position: static;
        width: 100%;
        margin-top: 24px;
    }
}

@media (max-width: 767px) {
    .mi-topbar {
        flex-direction: column;
        align-items: stretch;
        padding: 18px 16px;
    }

    .mi-topbar__left,
    .mi-topbar__right {
        flex-direction: column;
        align-items: stretch;
    }

    .mi-topbar__left {
        gap: 16px;
    }

    .mi-tabs {
        gap: 18px;
        overflow-x: auto;
    }

    .mi-search {
        min-width: 0;
        width: 100%;
    }

    .mi-topbar__actions {
        justify-content: flex-end;
    }

    .mi-summary-strip,
    .mi-feed__head,
    .mi-table-card__head {
        flex-direction: column;
        align-items: flex-start;
    }

    .mi-summary-strip {
        padding: 10px 16px;
    }

    .mi-summary-strip__text p {
        white-space: normal;
    }

    .mi-content__inner {
        padding: 18px 16px 40px;
    }

    .mi-kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mi-sidebar__nav {
        grid-template-columns: 1fr;
    }

    .mi-analytics-card__head {
        flex-direction: column;
    }

    .mi-chart {
        padding-left: 8px;
        padding-right: 8px;
    }

    .mi-chart__bars {
        gap: 8px;
    }

    .mi-chart__spot {
        left: 50%;
        min-width: 110px;
    }

    .mi-analytics-card__meta,
    .mi-footer-panels {
        grid-template-columns: 1fr;
    }

    .mi-footer-panels__links {
        flex-wrap: wrap;
        gap: 14px;
    }

    .mi-table thead th,
    .mi-table tbody td {
        padding-left: 14px;
        padding-right: 14px;
    }
}

@media (max-width: 479px) {
    .mi-kpi-grid {
        grid-template-columns: 1fr;
    }

    .mi-feed__actions {
        width: 100%;
    }

    .mi-feed__actions button {
        flex: 1;
    }
}
</style>
