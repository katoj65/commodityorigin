<script setup>
import { ref } from 'vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

import {
    TrendCharts, Reading, Van, Lock, Box,
    QuestionFilled, Document, Opportunity, Star,
    ChatDotRound, Promotion, ArrowRight, Share,
    Monitor, DataLine, Warning, Sunny, Cloudy,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

const activeSideLink = ref('Market Data');
const sideLinks = [
    { label: 'Terminal',      icon: Monitor },
    { label: 'Market Data',   icon: TrendCharts },
    { label: 'Global News',   icon: Reading },
    { label: 'Supply Chain',  icon: Van },
    { label: 'Risk Analytics',icon: Lock },
    { label: 'Archive',       icon: Box },
];
const sideFooterLinks = [
    { label: 'Support',       icon: QuestionFilled },
    { label: 'API Reference', icon: Document },
];

const tickerItems = [
    { text: 'Robusta prices rise 4.2%',                           tone: 'green' },
    { text: 'Heavy rain reported in Brazil',                       tone: 'red' },
    { text: 'UAE demand for Ugandan coffee increasing',            tone: 'amber' },
    { text: 'EU traceability requirements affecting exporters',    tone: 'red' },
];

const summaryCards = [
    { label: 'Price Direction', value: '+2.4%',   tone: 'green',  icon: TrendCharts },
    { label: 'Weather Risk',    value: 'Critical', tone: 'red',    icon: Warning },
    { label: 'Demand Trend',    value: 'Accel.',   tone: 'primary',icon: DataLine },
    { label: 'Supply Status',   value: 'Shortage', tone: 'amber',  icon: Box },
    { label: 'Quality Avg',     value: '92.4',     tone: 'primary',icon: Star },
    { label: 'Export Risk',     value: 'Medium',   tone: 'amber',  icon: Lock },
];

const feedCards = [
    {
        age: '14m ago · Brazil',
        sentiment: 'Positive',
        tone: 'positive',
        title: 'Frost warnings in Minas Gerais trigger Arabica price volatility',
        body: 'Met services confirm a cold front moving towards key regions, potentially impacting late harvest cherry development.',
        impactLabel: 'Critical Impact',
        why: 'Likely to drive short-term futures spikes by up to 8.5 cents/lb.',
        impact: '9.2 / 10',
    },
    {
        age: '2h ago · EU / Vietnam',
        sentiment: 'Negative',
        tone: 'negative',
        title: 'New EUDR compliance hurdles for Vietnamese Robusta exporters',
        body: 'Regulatory update requires geolocation data for all smallholder plots by Q4, risking export delays for unverified volumes.',
        impactLabel: 'Supply Risk',
        why: '30% of current supply chain lacks the necessary digital mapping data.',
        impact: '7.8 / 10',
    },
    {
        age: '5h ago · UAE',
        sentiment: 'Positive',
        tone: 'positive',
        title: 'DMCC reports 15% growth in specialty coffee trade corridors',
        body: 'Strong growth driven by specialty demand from MENA region and new logistics corridors for East African producers.',
        impactLabel: 'Market Opportunity',
        why: 'Signals a major trade opportunity for Ethiopian and Ugandan Arabicas.',
        impact: '6.5 / 10',
    },
];

const weatherItems = [
    { country: 'Uganda',  note: '24°C · 85% precip. expected', risk: 'Low Risk',  tone: 'green', icon: Cloudy },
    { country: 'Brazil',  note: '32°C · Extreme drought',       risk: 'High Risk', tone: 'red',   icon: Sunny },
    { country: 'Vietnam', note: '19°C · Unseasonal frost',      risk: 'Med Risk',  tone: 'amber', icon: Cloudy },
];

const analyticsBars = [24, 50, 40, 76, 60, 35, 92, 66, 100];
const activeAnalyticsTab = ref('Arabica');

const buyerRequirements = [
    { market: 'UAE',     coffee: 'Specialty Arabica',    score: '85+', moisture: '11.5%', cert: 'Halal / Organic',   demand: 'Surging', tone: 'green' },
    { market: 'Germany', coffee: 'Fine Robusta',          score: '82+', moisture: '12.0%', cert: 'Fairtrade / RF',    demand: 'Stable',  tone: 'neutral' },
    { market: 'Japan',   coffee: 'Micro-lot Arabica',     score: '88+', moisture: '11.0%', cert: 'JAS Organic',       demand: 'High',    tone: 'green' },
];

const deskAlerts = ref([
    { label: 'Price Volatility (>5%)',   enabled: true },
    { label: 'New Compliance Rules',     enabled: true },
    { label: 'Trade Matches',            enabled: false },
]);

const regionalDesks = [
    'East Africa: +256 (0) 41 455…',
    'South America: +55 (11) 3444…',
    'EMEA Central: +971 4 433…',
];

const chatInput = ref('');
const chatMessages = ref([
    { role: 'advisor', text: 'Brazil frost warnings have triggered a 4% spike in Arabica futures. Would you like to see how this impacts your current Ugandan Robusta export strategy?' },
    { role: 'user',    text: 'Yes, show me the UAE arbitrage opportunity.' },
]);
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMessages.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => {
        chatMessages.value.push({ role: 'advisor', text: 'Based on current Robusta supply tightness and UAE demand, I recommend listing export-ready lots now to maximise the 4.2% price premium.' });
    }, 700);
};
</script>

<template>
<outer-layout>
 <div class="mi-root">
            <div class="mi-layout">

                <!-- ── Sidebar ──────────────────────────────────────────── -->
                <aside class="mi-sidebar">
                    <div class="mi-sidebar__head">
                        <div class="mi-sidebar__brand">Institutional Exchange</div>
                        <div class="mi-sidebar__version">Terminal v4.2</div>
                    </div>

                    <nav class="mi-sidebar__nav">
                        <button
                            v-for="item in sideLinks"
                            :key="item.label"
                            class="mi-nav-link"
                            :class="{ 'mi-nav-link--active': activeSideLink === item.label }"
                            @click="activeSideLink = item.label"
                        >
                            <el-icon class="mi-nav-link__icon"><component :is="item.icon" /></el-icon>
                            <span>{{ item.label }}</span>
                        </button>
                    </nav>

                    <div class="mi-sidebar__footer">
                        <button class="mi-live-btn">
                            <el-icon><Promotion /></el-icon>
                            Live Intelligence
                        </button>
                        <div class="mi-sidebar__meta">
                            <button v-for="item in sideFooterLinks" :key="item.label" class="mi-meta-link">
                                <el-icon><component :is="item.icon" /></el-icon>
                                <span>{{ item.label }}</span>
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- ── Main content ─────────────────────────────────────── -->
                <main class="mi-main">

                    <!-- Hero insight strip -->
                    <div class="mi-insight-hero">
                        <div class="mi-insight-hero__left">
                            <div class="mi-insight-hero__eyebrow">
                                <el-icon><Opportunity /></el-icon>
                                AI Market Insight
                            </div>
                            <p class="mi-insight-hero__text">Robusta demand is rising in UAE while global supply remains tight.</p>
                        </div>
                        <div class="mi-insight-hero__badges">
                            <span class="mi-badge mi-badge--primary">Bullish</span>
                            <span class="mi-badge mi-badge--soft">High Demand</span>
                            <span class="mi-badge mi-badge--neutral">Impact: Med</span>
                        </div>
                    </div>

                    <!-- Scrolling ticker -->
                    <div class="mi-ticker" aria-label="Breaking market updates">
                        <div class="mi-ticker__track">
                            <div v-for="n in 2" :key="n" class="mi-ticker__group" :aria-hidden="n > 1">
                                <div v-for="item in tickerItems" :key="`${n}-${item.text}`" class="mi-ticker__item">
                                    <span class="mi-ticker__dot" :class="`mi-ticker__dot--${item.tone}`"></span>
                                    {{ item.text }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inner content -->
                    <div class="mi-inner">

                        <!-- KPI cards -->
                        <div class="mi-kpi-grid">
                            <div v-for="card in summaryCards" :key="card.label" class="mi-kpi-card">
                                <div class="mi-kpi-card__head">
                                    <span class="mi-kpi-card__label">{{ card.label }}</span>
                                    <span class="mi-kpi-icon" :class="`mi-kpi-icon--${card.tone}`">
                                        <el-icon><component :is="card.icon" /></el-icon>
                                    </span>
                                </div>
                                <div class="mi-kpi-card__value" :class="`mi-kpi-card__value--${card.tone}`">{{ card.value }}</div>
                            </div>
                        </div>

                        <!-- Feed header -->
                        <div class="mi-feed-head">
                            <div>
                                <h1 class="mi-section-title">Market Intelligence Feed</h1>
                                <p class="mi-section-sub">Live analysis · Sourced from global commodity data</p>
                            </div>
                            <div class="mi-btn-row">
                                <button class="mi-btn mi-btn--primary"><el-icon><Warning /></el-icon> Set Alerts</button>
                                <button class="mi-btn mi-btn--ghost"><el-icon><Share /></el-icon> Export</button>
                            </div>
                        </div>

                        <!-- Story cards -->
                        <div class="mi-story-grid">
                            <article v-for="card in feedCards" :key="card.title" class="mi-story-card">
                                <div class="mi-story-card__meta">
                                    <span class="mi-story-card__age">{{ card.age }}</span>
                                    <span class="mi-sentiment-dot" :class="`mi-sentiment-dot--${card.tone}`"></span>
                                    <span class="mi-story-card__sentiment">{{ card.sentiment }}</span>
                                </div>
                                <h2 class="mi-story-card__title">{{ card.title }}</h2>
                                <p class="mi-story-card__body">{{ card.body }}</p>
                                <div class="mi-story-card__impact">
                                    <span class="mi-story-card__impact-label">{{ card.impactLabel }}</span>
                                    <p>{{ card.why }}</p>
                                </div>
                                <div class="mi-story-card__footer">
                                    <div>
                                        <div class="mi-story-card__score-label">Impact Score</div>
                                        <strong class="mi-story-card__score">{{ card.impact }}</strong>
                                    </div>
                                    <button class="mi-link-btn">
                                        View Data <el-icon><ArrowRight /></el-icon>
                                    </button>
                                </div>
                            </article>
                        </div>

                        <!-- Analysis grid -->
                        <div class="mi-analysis-grid">

                            <!-- Weather & Crop Risk -->
                            <section class="mi-card">
                                <h2 class="mi-card-title">
                                    <span class="mi-card-icon"><el-icon><Cloudy /></el-icon></span>
                                    Weather &amp; Crop Risk
                                </h2>
                                <div class="mi-weather-list">
                                    <div v-for="item in weatherItems" :key="item.country" class="mi-weather-row">
                                        <span class="mi-weather-icon" :class="`mi-weather-icon--${item.tone}`">
                                            <el-icon><component :is="item.icon" /></el-icon>
                                        </span>
                                        <div class="mi-weather-body">
                                            <div class="mi-weather-country-row">
                                                <strong class="mi-weather-country">{{ item.country }}</strong>
                                                <span class="mi-risk-badge" :class="`mi-risk-badge--${item.tone}`">{{ item.risk }}</span>
                                            </div>
                                            <span class="mi-weather-note">{{ item.note }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- AI Recommendation -->
                                <div class="mi-recommendation">
                                    <div class="mi-recommendation__eyebrow">
                                        <el-icon><Opportunity /></el-icon>
                                        AI Recommendation
                                    </div>
                                    <h3>List export-ready Robusta for UAE immediately to capitalise on the supply gap.</h3>
                                    <button class="mi-recommendation__btn">Execute Strategy</button>
                                </div>
                            </section>

                            <!-- Market Analytics -->
                            <section class="mi-card mi-analytics-card">
                                <div class="mi-analytics-head">
                                    <div>
                                        <h2 class="mi-card-title">Market Analytics</h2>
                                        <p class="mi-analytics-sub">Institutional price &amp; volume tracking</p>
                                    </div>
                                    <div class="mi-tab-switch">
                                        <button
                                            v-for="tab in ['Arabica','Robusta','Uganda']"
                                            :key="tab"
                                            :class="{ 'mi-tab-switch__btn--active': activeAnalyticsTab === tab }"
                                            class="mi-tab-switch__btn"
                                            @click="activeAnalyticsTab = tab"
                                        >{{ tab }}</button>
                                    </div>
                                </div>

                                <div class="mi-chart">
                                    <div class="mi-chart-bars">
                                        <span v-for="(h, i) in analyticsBars" :key="i" :style="{ height: `${h}%` }" class="mi-chart-bar"></span>
                                    </div>
                                    <div class="mi-chart-spot">
                                        <small>Current Spot</small>
                                        <strong>$182.45</strong>
                                        <em>+1.2% Day</em>
                                    </div>
                                </div>

                                <div class="mi-chart-meta">
                                    <div class="mi-chart-meta-item">
                                        <span>Volume 24h</span>
                                        <strong>1.2M Bags</strong>
                                    </div>
                                    <div class="mi-chart-meta-item">
                                        <span>Open Interest</span>
                                        <strong>45.2K Contracts</strong>
                                    </div>
                                    <div class="mi-chart-meta-item">
                                        <span>Influence</span>
                                        <strong class="mi-bullish">Bullish</strong>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Buyer requirements table -->
                        <div class="mi-card mt-4">
                            <div class="mi-table-head">
                                <h2 class="mi-card-title">
                                    <span class="mi-card-icon"><el-icon><Van /></el-icon></span>
                                    Global Buyer Requirements
                                </h2>
                                <button class="mi-link-btn">
                                    View All Markets <el-icon><Share /></el-icon>
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
                                        <tr v-for="row in buyerRequirements" :key="row.market">
                                            <td>
                                                <div class="mi-market-cell">
                                                    <span class="mi-market-flag"></span>
                                                    <strong>{{ row.market }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ row.coffee }}</td>
                                            <td class="mi-td-strong">{{ row.score }}</td>
                                            <td>{{ row.moisture }}</td>
                                            <td class="mi-td-cert">{{ row.cert }}</td>
                                            <td>
                                                <span class="mi-demand-chip" :class="`mi-demand-chip--${row.tone}`">{{ row.demand }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Footer panels -->
                        <div class="mi-footer-panels">
                            <div class="mi-footer-brand">
                                <div class="mi-footer-brand__name">Bean Origin Intelligence</div>
                                <p>Institutional-grade data terminal for global coffee supply chains, logistics, and market analytics.</p>
                                <div class="mi-footer-links">
                                    <a href="#">Privacy Policy</a>
                                    <a href="#">Terms of Service</a>
                                    <a href="#">Methodology</a>
                                </div>
                            </div>

                            <div class="mi-card mi-footer-card">
                                <div class="mi-footer-card-label">Smart Alerts</div>
                                <div class="mi-toggle-list">
                                    <div v-for="item in deskAlerts" :key="item.label" class="mi-toggle-row">
                                        <span>{{ item.label }}</span>
                                        <button class="mi-toggle" :class="{ 'mi-toggle--on': item.enabled }" @click="item.enabled = !item.enabled">
                                            <i></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mi-card mi-footer-card">
                                <div class="mi-footer-card-label">Regional Desks</div>
                                <div class="mi-desk-list">
                                    <span v-for="desk in regionalDesks" :key="desk" class="mi-desk-item">{{ desk }}</span>
                                </div>
                            </div>
                        </div>

                    </div><!-- /mi-inner -->
                </main>
            </div>

            <!-- ── Floating Advisor ─────────────────────────────────────── -->
            <div class="mi-advisor">
                <div class="mi-advisor__head">
                    <div class="mi-advisor__identity">
                        <div class="mi-advisor__avatar">
                            <el-icon><ChatDotRound /></el-icon>
                        </div>
                        <div>
                            <strong class="mi-advisor__name">Bean Origin Advisor</strong>
                            <span class="mi-advisor__status"><i></i> Online &amp; Analysing</span>
                        </div>
                    </div>
                    <button class="mi-advisor__close">×</button>
                </div>
                <div class="mi-advisor__body">
                    <div
                        v-for="(msg, i) in chatMessages"
                        :key="i"
                        class="mi-chat-msg"
                        :class="`mi-chat-msg--${msg.role}`"
                    >{{ msg.text }}</div>
                </div>
                <div class="mi-advisor__footer">
                    <div class="mi-advisor__chips">
                        <button class="mi-chip" @click="chatInput = 'UAE Entry'; sendChat()">UAE Entry</button>
                        <button class="mi-chip" @click="chatInput = 'EUDR Compliance'; sendChat()">EUDR Compliance</button>
                    </div>
                    <div class="mi-advisor__input">
                        <input v-model="chatInput" placeholder="Ask advisor…" @keydown.enter="sendChat" />
                        <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                    </div>
                </div>
            </div>
        </div>

</outer-layout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.mi-root {
    --primary:        #004532;
    --primary-grad:   #065f46;
    --on-primary:     #ffffff;
    --primary-soft:   #e8f3ef;
    --primary-faint:  #f0f9f5;
    --amber:          #92400e;
    --amber-bg:       #fef3c7;
    --red:            #b91c1c;
    --red-bg:         #fee2e2;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-white:  #ffffff;
    --surface-low:    #f8fafc;
    --surface-mid:    #f1f5f9;
    --surface-high:   #eef2f0;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
    position: relative;
}

/* ── Layout ────────────────────────────────────────────────────────────────── */
.mi-layout {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    min-height: 100%;
}

/* ── Sidebar ───────────────────────────────────────────────────────────────── */
.mi-sidebar {
    display: flex;
    flex-direction: column;
    padding: 1.25rem;
    background: var(--surface-low);
    border-right: 1px solid var(--surface-high);
    min-height: calc(100vh - 56px);
}
.mi-sidebar__brand  { font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--on-surface-var); }
.mi-sidebar__version{ font-size: 0.625rem; color: #9ca3af; margin-top: 2px; }
.mi-sidebar__nav    { display: flex; flex-direction: column; gap: 4px; margin-top: 1.5rem; }

.mi-nav-link {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 10px; border-radius: 8px; border: none;
    background: transparent; font-family: 'Manrope', sans-serif;
    font-size: 0.875rem; font-weight: 500; color: var(--on-surface-var);
    cursor: pointer; text-align: left; transition: background 0.12s ease, color 0.12s ease;
}
.mi-nav-link:hover         { background: var(--surface-mid); color: var(--on-surface); }
.mi-nav-link--active       { background: var(--primary-soft); color: var(--primary); font-weight: 700; }
.mi-nav-link__icon         { font-size: 16px; flex-shrink: 0; }

.mi-sidebar__footer { margin-top: auto; }
.mi-live-btn {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    width: 100%; padding: 10px; border-radius: 8px; border: none;
    background: var(--primary); color: var(--on-primary);
    font-family: 'Manrope', sans-serif; font-size: 0.8125rem; font-weight: 700;
    cursor: pointer; transition: opacity 0.15s ease;
}
.mi-live-btn:hover { opacity: 0.88; }

.mi-sidebar__meta { display: flex; flex-direction: column; gap: 6px; margin-top: 1rem; padding-left: 4px; }
.mi-meta-link {
    display: flex; align-items: center; gap: 8px;
    border: none; background: none; padding: 4px 6px;
    font-family: 'Manrope', sans-serif; font-size: 0.75rem;
    color: var(--on-surface-var); cursor: pointer;
}
.mi-meta-link:hover { color: var(--on-surface); }

/* ── Main ──────────────────────────────────────────────────────────────────── */
.mi-main { display: flex; flex-direction: column; min-width: 0; }

/* ── Hero insight strip ────────────────────────────────────────────────────── */
.mi-insight-hero {
    display: flex; align-items: center; justify-content: space-between; gap: 1.25rem;
    padding: 14px 1.5rem; flex-wrap: wrap;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
}
.mi-insight-hero__eyebrow {
    display: flex; align-items: center; gap: 6px;
    font-size: 0.625rem; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; opacity: 0.8; margin-bottom: 5px;
}
.mi-insight-hero__text {
    margin: 0; font-size: 1rem; font-weight: 700; line-height: 1.4;
}
.mi-insight-hero__badges { display: flex; gap: 6px; flex-shrink: 0; flex-wrap: wrap; }

.mi-badge { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 4px 12px; white-space: nowrap; }
.mi-badge--primary { background: rgba(255,255,255,0.18); color: #fff; }
.mi-badge--soft    { background: #d1fae5; color: var(--primary); }
.mi-badge--neutral { background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.9); }

/* ── Ticker ────────────────────────────────────────────────────────────────── */
.mi-ticker { overflow: hidden; background: #111827; }
.mi-ticker__track { display: flex; width: max-content; animation: mi-scroll 32s linear infinite; }
.mi-ticker__group { display: flex; align-items: center; }
.mi-ticker__item {
    display: inline-flex; align-items: center; gap: 8px;
    min-height: 30px; padding: 0 24px; white-space: nowrap;
    font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.04em; color: #d1d5db;
}
.mi-ticker__dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.mi-ticker__dot--green { background: #4ade80; }
.mi-ticker__dot--red   { background: #f87171; }
.mi-ticker__dot--amber { background: #fbbf24; }

@keyframes mi-scroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* ── Inner ─────────────────────────────────────────────────────────────────── */
.mi-inner { padding: 1.5rem 1.5rem 4rem; background: linear-gradient(180deg, #fafafa 0%, #fff 10%); }
.mt-4 { margin-top: 1.25rem; }

/* ── KPI grid ──────────────────────────────────────────────────────────────── */
.mi-kpi-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 0.875rem; margin-bottom: 2rem; }
.mi-kpi-card {
    background: var(--surface-white); border: 1px solid var(--surface-high);
    border-radius: 6px; padding: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.mi-kpi-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.mi-kpi-card__label { font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--on-surface-var); }
.mi-kpi-icon { width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; }
.mi-kpi-icon--primary { background: var(--primary-soft); color: var(--primary); }
.mi-kpi-icon--green   { background: #dcfce7; color: #166534; }
.mi-kpi-icon--red     { background: var(--red-bg); color: var(--red); }
.mi-kpi-icon--amber   { background: var(--amber-bg); color: var(--amber); }
.mi-kpi-card__value { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); }
.mi-kpi-card__value--green   { color: #166534; }
.mi-kpi-card__value--red     { color: var(--red); }
.mi-kpi-card__value--amber   { color: var(--amber); }
.mi-kpi-card__value--primary { color: var(--primary); }

/* ── Feed head ─────────────────────────────────────────────────────────────── */
.mi-feed-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;
}
.mi-section-title { font-size: 1.0625rem; font-weight: 700; color: var(--on-surface); margin: 0 0 2px; }
.mi-section-sub   { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0; }

.mi-btn-row { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.mi-weather-country-row { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
.mi-advisor__identity { display: flex; align-items: center; gap: 0.75rem; }

.mi-btn {
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 6px; border: none; padding: 7px 14px;
    font-family: 'Manrope', sans-serif; font-size: 0.8125rem; font-weight: 700;
    cursor: pointer; transition: opacity 0.15s ease;
}
.mi-btn--primary { background: var(--primary); color: var(--on-primary); }
.mi-btn--primary:hover { opacity: 0.88; }
.mi-btn--ghost   { background: var(--surface-mid); color: var(--on-surface); }
.mi-btn--ghost:hover { background: var(--surface-high); }

/* ── Story cards ───────────────────────────────────────────────────────────── */
.mi-story-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
.mi-story-card {
    display: flex; flex-direction: column;
    background: var(--surface-white); border: 1px solid var(--surface-high);
    border-radius: 6px; padding: 1.125rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.mi-story-card__meta { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.mi-story-card__age {
    background: var(--surface-mid); border-radius: 6px;
    font-size: 0.6875rem; font-weight: 700; color: var(--on-surface-var);
    padding: 2px 10px;
}
.mi-sentiment-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.mi-sentiment-dot--positive { background: #22c55e; }
.mi-sentiment-dot--negative { background: #ef4444; }
.mi-story-card__sentiment { font-size: 0.75rem; font-weight: 600; color: var(--on-surface-var); }
.mi-story-card__title { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); line-height: 1.35; margin: 0 0 8px; }
.mi-story-card__body  { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.55; margin: 0 0 auto; }
.mi-story-card__impact {
    margin-top: 1rem; padding: 12px; border-radius: 6px;
    background: var(--surface-low); border: 1px solid var(--surface-high);
}
.mi-story-card__impact-label { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #3b82f6; display: block; margin-bottom: 4px; }
.mi-story-card__impact p { font-size: 0.8125rem; color: var(--on-surface); margin: 0; line-height: 1.5; }
.mi-story-card__footer {
    display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem;
    margin-top: 1rem; padding-top: 0.875rem; border-top: 1px solid var(--surface-high);
}
.mi-story-card__score-label { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--on-surface-var); }
.mi-story-card__score { font-size: 1rem; font-weight: 800; color: var(--on-surface); }

.mi-link-btn {
    display: inline-flex; align-items: center; gap: 5px;
    border: none; background: none; padding: 0;
    font-family: 'Manrope', sans-serif; font-size: 0.8125rem;
    font-weight: 700; color: var(--primary); cursor: pointer;
    white-space: nowrap;
}
.mi-link-btn:hover { opacity: 0.75; }

/* ── Analysis grid ─────────────────────────────────────────────────────────── */
.mi-analysis-grid { display: grid; grid-template-columns: 1fr 1.9fr; gap: 1rem; }

.mi-card {
    background: var(--surface-white); border: 1px solid var(--surface-high);
    border-radius: 6px; padding: 1.125rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.mi-card-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); margin: 0 0 1rem;
}
.mi-card-icon {
    width: 26px; height: 26px; border-radius: 6px;
    background: rgba(0,69,50,0.08); color: var(--primary);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
}

/* Weather */
.mi-weather-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 1rem; }
.mi-weather-row  { display: flex; align-items: center; gap: 10px; }
.mi-weather-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
}
.mi-weather-icon--green { background: #dcfce7; color: #166534; }
.mi-weather-icon--red   { background: var(--red-bg); color: var(--red); }
.mi-weather-icon--amber { background: var(--amber-bg); color: var(--amber); }
.mi-weather-body  { flex: 1; min-width: 0; }
.mi-weather-country { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.mi-weather-note    { font-size: 0.6875rem; color: var(--on-surface-var); display: block; margin-top: 2px; }
.mi-risk-badge { font-size: 0.625rem; font-weight: 700; border-radius: 999px; padding: 2px 8px; white-space: nowrap; }
.mi-risk-badge--green { background: #dcfce7; color: #166534; }
.mi-risk-badge--red   { background: var(--red-bg); color: var(--red); }
.mi-risk-badge--amber { background: var(--amber-bg); color: var(--amber); }

/* Recommendation */
.mi-recommendation {
    border-radius: 10px; padding: 14px;
    background: linear-gradient(160deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
}
.mi-recommendation__eyebrow {
    display: flex; align-items: center; gap: 6px;
    font-size: 0.625rem; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; opacity: 0.8; margin-bottom: 8px;
}
.mi-recommendation h3 { font-size: 0.9375rem; font-weight: 700; line-height: 1.4; margin: 0 0 12px; }
.mi-recommendation__btn {
    width: 100%; border: none; border-radius: 6px;
    background: rgba(255,255,255,0.92); color: var(--on-surface);
    font-family: 'Manrope', sans-serif; font-size: 0.8125rem; font-weight: 700;
    padding: 8px; cursor: pointer; transition: background 0.15s ease;
}
.mi-recommendation__btn:hover { background: #fff; }

/* Analytics card */
.mi-analytics-card { display: flex; flex-direction: column; }
.mi-analytics-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
.mi-analytics-sub  { font-size: 0.8125rem; color: var(--on-surface-var); margin: 3px 0 0; }
.mi-tab-switch { display: inline-flex; gap: 3px; padding: 3px; border-radius: 8px; background: var(--surface-mid); }
.mi-tab-switch__btn {
    min-height: 24px; padding: 0 10px; border: none; border-radius: 6px;
    background: transparent; font-family: 'Manrope', sans-serif; font-size: 0.75rem; font-weight: 700;
    color: var(--on-surface-var); cursor: pointer; transition: background 0.12s ease;
}
.mi-tab-switch__btn--active { background: var(--surface-white); color: var(--on-surface); box-shadow: 0 1px 2px rgba(0,0,0,0.08); }

/* Chart */
.mi-chart {
    position: relative; display: flex; align-items: flex-end;
    min-height: 220px; border-bottom: 1px solid var(--surface-high);
    margin-bottom: 1rem;
}
.mi-chart-bars { display: grid; grid-template-columns: repeat(9, minmax(0, 1fr)); align-items: end; gap: 8px; width: 100%; min-height: 180px; padding: 0 8px; }
.mi-chart-bar { border-radius: 6px 6px 0 0; background: #d1fae5; }
.mi-chart-bars span:nth-child(4)  { background: #6ee7b7; }
.mi-chart-bars span:nth-child(5)  { background: #34d399; }
.mi-chart-bars span:nth-child(7)  { background: #059669; }
.mi-chart-bars span:nth-child(8)  { background: #10b981; }
.mi-chart-bars span:nth-child(9)  { background: var(--primary); }

.mi-chart-spot {
    position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
    min-width: 110px; padding: 10px 14px; border-radius: 14px;
    background: rgba(255,255,255,0.95); text-align: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1); backdrop-filter: blur(8px);
}
.mi-chart-spot small { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--on-surface-var); display: block; }
.mi-chart-spot strong { font-size: 1.375rem; font-weight: 800; color: var(--on-surface); display: block; margin: 4px 0; }
.mi-chart-spot em {
    display: inline-flex; align-items: center; border-radius: 999px;
    background: #dcfce7; color: var(--primary); font-style: normal;
    font-size: 0.75rem; font-weight: 700; padding: 2px 10px;
}
.mi-chart-meta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
.mi-chart-meta-item span   { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; margin-bottom: 4px; }
.mi-chart-meta-item strong { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.mi-bullish { color: var(--primary) !important; }

/* Table */
.mi-table-head {
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    margin-bottom: 1rem; flex-wrap: wrap;
}
.mi-table-wrap { overflow-x: auto; }
.mi-table { width: 100%; border-collapse: collapse; }
.mi-table thead th { padding: 10px 14px; text-align: left; background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); white-space: nowrap; }
.mi-table tbody td { padding: 11px 14px; border-top: 1px solid var(--surface-high); font-size: 0.8125rem; color: var(--on-surface); white-space: nowrap; }
.mi-market-cell { display: flex; align-items: center; gap: 8px; }
.mi-market-flag { width: 22px; height: 14px; border-radius: 3px; background: var(--surface-high); flex-shrink: 0; }
.mi-td-strong { font-weight: 700; }
.mi-td-cert   { font-weight: 700; color: var(--primary); }
.mi-demand-chip { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.mi-demand-chip--green   { background: #dcfce7; color: #166534; }
.mi-demand-chip--neutral { background: var(--surface-mid); color: var(--on-surface-var); }

/* Footer panels */
.mi-footer-panels { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 2rem; margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid var(--surface-high); }
.mi-footer-brand__name { font-size: 0.9375rem; font-weight: 800; color: var(--primary); margin-bottom: 0.75rem; }
.mi-footer-brand p { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.65; margin: 0 0 1.25rem; max-width: 340px; }
.mi-footer-links { display: flex; gap: 1.25rem; flex-wrap: wrap; }
.mi-footer-links a { font-size: 0.75rem; font-weight: 700; color: var(--primary); text-decoration: none; }
.mi-footer-links a:hover { opacity: 0.75; }
.mi-footer-card { padding: 1rem; }
.mi-footer-card-label { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--on-surface-var); margin-bottom: 0.75rem; }

.mi-toggle-list { display: flex; flex-direction: column; gap: 10px; }
.mi-toggle-row  { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.mi-toggle-row span { font-size: 0.8125rem; color: var(--on-surface); }
.mi-toggle {
    width: 30px; height: 17px; border-radius: 999px; padding: 2px; border: none;
    background: var(--surface-high); cursor: pointer; flex-shrink: 0;
    transition: background 0.2s ease;
}
.mi-toggle i { display: block; width: 13px; height: 13px; border-radius: 50%; background: #fff; transition: transform 0.2s ease; }
.mi-toggle--on   { background: var(--primary); }
.mi-toggle--on i { transform: translateX(13px); }

.mi-desk-list { display: flex; flex-direction: column; gap: 8px; }
.mi-desk-item { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Advisor ───────────────────────────────────────────────────────────────── */
.mi-advisor {
    position: fixed; right: 1.5rem; bottom: 1.5rem; z-index: 200;
    width: 300px; border-radius: 16px; overflow: hidden;
    background: var(--surface-white); border: 1px solid var(--surface-high);
    box-shadow: 0 8px 32px rgba(0,0,0,0.14);
}
.mi-advisor__head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 12px 14px; background: var(--primary); color: var(--on-primary);
}
.mi-advisor__avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: rgba(255,255,255,0.14); display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.mi-advisor__name   { display: block; font-size: 0.8125rem; font-weight: 700; }
.mi-advisor__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; margin-top: 1px; }
.mi-advisor__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.mi-advisor__close  { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }

.mi-advisor__body { padding: 12px; background: var(--surface-low); display: flex; flex-direction: column; gap: 10px; max-height: 200px; overflow-y: auto; }
.mi-chat-msg { font-size: 0.8125rem; line-height: 1.5; padding: 10px 12px; border-radius: 10px; max-width: 90%; }
.mi-chat-msg--advisor { background: var(--surface-white); color: var(--on-surface); align-self: flex-start; border-radius: 10px 10px 10px 2px; }
.mi-chat-msg--user    { background: #dcfce7; color: #166534; align-self: flex-end; margin-left: auto; border-radius: 10px 10px 2px 10px; }

.mi-advisor__footer { padding: 10px 12px 12px; background: var(--surface-white); border-top: 1px solid var(--surface-high); }
.mi-advisor__chips { display: flex; gap: 6px; margin-bottom: 8px; overflow-x: auto; }
.mi-chip {
    border: 1px solid rgba(0,69,50,0.4); border-radius: 999px; background: #fff;
    font-family: 'Manrope', sans-serif; font-size: 0.625rem; font-weight: 700;
    color: var(--primary); padding: 3px 10px; cursor: pointer; white-space: nowrap;
}
.mi-chip:hover { background: var(--primary-faint); }
.mi-advisor__input { display: flex; gap: 6px; }
.mi-advisor__input input {
    flex: 1; border: 1px solid var(--surface-high); border-radius: 8px;
    padding: 7px 10px; font-family: 'Manrope', sans-serif; font-size: 0.8125rem;
    color: var(--on-surface); outline: none;
}
.mi-advisor__input input:focus { border-color: var(--primary); }
.mi-advisor__input button {
    border: none; background: var(--primary); color: var(--on-primary);
    border-radius: 8px; width: 34px; display: flex; align-items: center; justify-content: center;
    font-size: 14px; cursor: pointer;
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1200px) {
    .mi-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .mi-story-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .mi-analysis-grid { grid-template-columns: 1fr; }
    .mi-footer-panels { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 991px) {
    .mi-layout { grid-template-columns: 1fr; }
    .mi-sidebar { min-height: auto; flex-direction: row; flex-wrap: wrap; gap: 1rem; padding: 1rem; border-right: none; border-bottom: 1px solid var(--surface-high); }
    .mi-sidebar__nav { flex-direction: row; flex-wrap: wrap; margin-top: 0; flex: 1; }
    .mi-sidebar__footer { margin-top: 0; display: flex; gap: 0.75rem; align-items: center; }
}
@media (max-width: 767px) {
    .mi-story-grid { grid-template-columns: 1fr; }
    .mi-kpi-grid   { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .mi-footer-panels { grid-template-columns: 1fr; gap: 1rem; }
    .mi-advisor { right: 0.75rem; bottom: 0.75rem; width: calc(100vw - 1.5rem); max-width: 320px; }
}
@media (max-width: 479px) {
    .mi-kpi-grid { grid-template-columns: 1fr 1fr; }
    .mi-inner { padding: 1rem 1rem 3rem; }
}
</style>
