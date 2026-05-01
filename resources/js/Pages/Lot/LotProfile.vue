<script setup>
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Box,
    Checked,
    Clock,
    Collection,
    Connection,
    DataAnalysis,
    Document,
    Download,
    Files,
    GoodsFilled,
    Histogram,
    Location,
    Medal,
    Money,
    Promotion,
    ShoppingCart,
    Star,
    TrendCharts,
    Trophy,
    User,
    WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    lot: { type: Object, default: () => ({}) },
    batch: { type: Object, default: () => ({}) },
    season: { type: Object, default: () => ({}) },
    harvests: { type: Array, default: () => [] },
    relatedLots: { type: Array, default: () => [] },
});

const chatOpen = ref(false);
const chatInput = ref('');
const chatMessages = ref([
    { role: 'assistant', text: 'Hi! I\'m the Bean Origin Lot Advisor. How can I help you evaluate this lot?' },
]);

const fmt = (v, d = 0) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });

const lotNumber    = computed(() => props.lot.lot_number   || 'LOT-2026-001');
const lotName      = computed(() => props.lot.lot_name     || 'Bugisu Premium Export Lot');
const lotStatus    = computed(() => props.lot.status       || 'Active');
const coffeeType   = computed(() => props.batch.variety    || 'Arabica – Bourbon');
const origin       = computed(() => props.batch.origin     || props.batch.region || 'Mbale, Uganda');
const cooperative  = computed(() => props.batch.cooperative || 'Bugisu Cooperative Union');
const process      = computed(() => props.lot.process      || 'Washed');
const grade        = computed(() => props.lot.grade        || 'AA');
const screenSize   = computed(() => props.lot.screen_size  || '17/18');
const altitude     = computed(() => props.lot.altitude     || '1,800 – 2,100 m');
const packaging    = computed(() => props.lot.packaging_type || 'GrainPro');
const warehouse    = computed(() => props.lot.warehouse    || 'Kampala Dry Mill');
const qtyBags      = computed(() => fmt(props.lot.quantity_bags));
const bagWeight    = computed(() => fmt(props.lot.bag_weight_kg, 1));
const netWeight    = computed(() => fmt(props.lot.net_weight_kg, 2));
const grossWeight  = computed(() => fmt(Number(props.lot.net_weight_kg || 0) * 1.02, 2));
const pricePerKg   = computed(() => fmt(props.lot.price_per_kg, 2));
const totalValue   = computed(() => fmt(Number(props.lot.price_per_kg || 0) * Number(props.lot.net_weight_kg || 0), 2));
const qualityScore = computed(() => Number(props.lot.quality_score || props.batch.cup_score || 87.5));
const aromaScore   = computed(() => Number(props.lot.aroma_score   || 8.75));
const acidityScore = computed(() => Number(props.lot.acidity_score || 9.0));
const bodyScore    = computed(() => Number(props.lot.body_score    || 8.25));
const balanceScore = computed(() => Number(props.lot.balance_score || 8.5));
const isTokenised  = computed(() => props.lot.tokenize ?? true);
const batchNumber  = computed(() => props.batch.batch_number || 'BCH-2025-042');
const seasonName   = computed(() => props.season?.name || 'Main Harvest 2025/26');
const moisture     = computed(() => fmt(props.batch.moisture_content || 11.2, 1));

const scoreBar = (score) => Math.min(100, (score / 10) * 100);

const exportChecklist = computed(() => [
    { label: 'Quality report available',  done: true },
    { label: 'Certificate of origin',     done: true },
    { label: 'Phytosanitary certificate', done: props.lot.status === 'active' },
    { label: 'Packing list',              done: true },
    { label: 'Export permit',             done: false },
    { label: 'Warehouse confirmation',    done: Boolean(props.lot.warehouse) },
]);

const exportScore = computed(() => {
    const done = exportChecklist.value.filter(i => i.done).length;
    return Math.round((done / exportChecklist.value.length) * 100);
});

const timeline = [
    { label: 'Batch created',      date: '12 Jan 2026', done: true },
    { label: 'Lot created',        date: '15 Feb 2026', done: true },
    { label: 'Quality tested',     date: '18 Feb 2026', done: true },
    { label: 'Documents uploaded', date: '20 Feb 2026', done: true },
    { label: 'Tokenised',          date: '22 Feb 2026', done: isTokenised.value },
    { label: 'Listed on market',   date: '01 Mar 2026', done: Boolean(props.lot.price_per_kg) },
    { label: 'Trade activity',     date: 'Pending',     done: false },
];

const suggestedPrompts = [
    'Is this lot worth buying?',
    'What price should I bid?',
    'Is this lot export ready?',
    'Which market is best?',
];

const sendChat = () => {
    const text = chatInput.value.trim();
    if (!text) return;
    chatMessages.value.push({ role: 'user', text });
    chatInput.value = '';
    setTimeout(() => {
        chatMessages.value.push({
            role: 'assistant',
            text: 'Based on the quality score and market data, this lot looks highly competitive for the UAE specialty market. Would you like a detailed breakdown?',
        });
    }, 800);
};

const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <AppLayout title="Lot Profile" full-width flush :show-banner="false">

        <div class="lp-root">

            <!-- ── Hero ─────────────────────────────────────────────────────── -->
            <section class="lp-hero">
                <div class="lp-hero__inner">
                    <div class="lp-hero__left">
                        <div class="lp-tag-row mb-3">
                            <span class="lp-hero-tag">
                                <el-icon><Checked /></el-icon> Verified Lot
                            </span>
                            <span class="lp-hero-tag">
                                <el-icon><Box /></el-icon> Export Ready
                            </span>
                            <span v-if="isTokenised" class="lp-hero-tag lp-hero-tag--warm">
                                <el-icon><Promotion /></el-icon> Tokenised
                            </span>
                            <span class="lp-hero-tag lp-hero-tag--warm">
                                <el-icon><Medal /></el-icon> Blockchain Verified
                            </span>
                        </div>
                        <h1 class="lp-hero__title mb-1 mt-0">{{ lotName }}</h1>
                        <p class="lp-hero__sub">
                            <el-icon class="lp-sub-icon"><Files /></el-icon>{{ lotNumber }}
                            <span class="lp-sub-sep">&middot;</span>
                            <el-icon class="lp-sub-icon"><Location /></el-icon>{{ origin }}
                            <span class="lp-sub-sep">&middot;</span>
                            {{ process }}
                        </p>
                        <div class="lp-hero__actions mt-1">
                            <button class="lp-btn lp-btn--primary">
                                <el-icon><ShoppingCart /></el-icon> Buy Now
                            </button>
                            <button class="lp-btn lp-btn--hero-outline">
                                <el-icon><Money /></el-icon> Place Bid
                            </button>
                            <button class="lp-btn lp-btn--hero-ghost">
                                <el-icon><Trophy /></el-icon> Auction
                            </button>
                            <button class="lp-btn lp-btn--hero-ghost">
                                <el-icon><Download /></el-icon> Report
                            </button>
                        </div>
                    </div>
                    <div class="lp-hero__right">
                        <div class="lp-score-display">
                            <span class="lp-score-display__num">{{ qualityScore.toFixed(1) }}</span>
                            <span class="lp-score-display__label">SCA Cupping Score</span>
                            <div class="lp-score-display__grade">
                                <el-icon><Medal /></el-icon> Grade {{ grade }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hero data zones -->
                <div class="lp-hero__zones">
                    <!-- Zone 1: Lot Identity -->
                    <div class="lp-hero-zone">
                        <div class="lp-zone-eyebrow">
                            <el-icon><Box /></el-icon> LOT IDENTITY
                        </div>
                        <div class="lp-zone-kv-list">
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Files /></el-icon> Lot ID</span>
                                <strong>{{ lotNumber }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Collection /></el-icon> Type</span>
                                <strong>{{ coffeeType }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><User /></el-icon> Cooperative</span>
                                <strong>{{ cooperative }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Checked /></el-icon> Status</span>
                                <span class="lp-status-pill">{{ lotStatus }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Zone 2: Quality -->
                    <div class="lp-hero-zone">
                        <div class="lp-zone-eyebrow">
                            <el-icon><Trophy /></el-icon> QUALITY
                        </div>
                        <div class="lp-zone-kv-list">
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Medal /></el-icon> Grade</span>
                                <strong>{{ grade }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><DataAnalysis /></el-icon> Moisture</span>
                                <strong>{{ moisture }}%</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Histogram /></el-icon> Screen</span>
                                <strong>{{ screenSize }}</strong>
                            </div>
                            <div class="lp-zone-kv lp-zone-kv--chips">
                                <span class="lp-zone-kv__label"><el-icon><Star /></el-icon> Flavor</span>
                                <div class="lp-zone-flavor-row">
                                    <span class="lp-flavor-chip">Blackcurrant</span>
                                    <span class="lp-flavor-chip">Citrus</span>
                                    <span class="lp-flavor-chip">Floral</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zone 3: Commercial -->
                    <div class="lp-hero-zone">
                        <div class="lp-zone-eyebrow">
                            <el-icon><TrendCharts /></el-icon> COMMERCIAL
                        </div>
                        <div class="lp-zone-kv-list">
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Money /></el-icon> Price / kg</span>
                                <strong>Shs. {{ pricePerKg }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><TrendCharts /></el-icon> Total Value</span>
                                <strong class="lp-accent">Shs. {{ totalValue }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Box /></el-icon> Available</span>
                                <strong>{{ netWeight }} kg</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Star /></el-icon> Demand</span>
                                <span class="lp-demand-chip">HIGH</span>
                            </div>
                        </div>
                    </div>

                    <!-- Zone 4: Tokenisation -->
                    <div class="lp-hero-zone">
                        <div class="lp-zone-eyebrow">
                            <el-icon><Promotion /></el-icon> TOKENISATION
                        </div>
                        <div class="lp-zone-kv-list">
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Promotion /></el-icon> Status</span>
                                <strong :class="isTokenised ? 'lp-accent' : ''">{{ isTokenised ? 'Tokenised' : 'Pending' }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Connection /></el-icon> Network</span>
                                <strong>Arbitrum L2</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Box /></el-icon> Units</span>
                                <strong>{{ netWeight }} kg</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Files /></el-icon> Ownership</span>
                                <strong>Fractionable</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Traceability Chain ────────────────────────────────────────── -->
            <section class="lp-section lp-section--white">
                <div class="lp-section__inner">
                    <p class="lp-eyebrow">TRACEABILITY CHAIN</p>
                    <div class="lp-trace">
                        <div class="lp-trace-step lp-trace-step--done">
                            <div class="lp-trace-node">1</div>
                            <div class="lp-trace-body">
                                <div class="lp-trace-tag">SEASON</div>
                                <strong>{{ seasonName }}</strong>
                                <span>Verified</span>
                            </div>
                        </div>
                        <div class="lp-trace-connector"></div>
                        <div class="lp-trace-step lp-trace-step--done">
                            <div class="lp-trace-node">2</div>
                            <div class="lp-trace-body">
                                <div class="lp-trace-tag">HARVESTS</div>
                                <strong>{{ harvests.length || 4 }} Harvests</strong>
                                <a href="#" class="lp-trace-link">View Harvests</a>
                            </div>
                        </div>
                        <div class="lp-trace-connector"></div>
                        <div class="lp-trace-step lp-trace-step--done">
                            <div class="lp-trace-node">3</div>
                            <div class="lp-trace-body">
                                <div class="lp-trace-tag">BATCH</div>
                                <strong>{{ batchNumber }}</strong>
                                <a href="#" class="lp-trace-link">View Batch</a>
                            </div>
                        </div>
                        <div class="lp-trace-connector"></div>
                        <div class="lp-trace-step lp-trace-step--current">
                            <div class="lp-trace-node lp-trace-node--active">4</div>
                            <div class="lp-trace-body">
                                <div class="lp-trace-tag">LOT</div>
                                <strong>{{ lotNumber }}</strong>
                                <span class="lp-origin-tag" style="font-size:9px;padding:2px 8px;">Current</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Main Grid ─────────────────────────────────────────────────── -->
            <section class="lp-section lp-section--base">
                <div class="lp-section__inner">
                    <div class="lp-main-grid">

                        <!-- LEFT COLUMN -->
                        <div class="lp-main-col">

                            <!-- Quality Intelligence -->
                            <div class="lp-card">
                                <h2 class="lp-card__title"><el-icon><DataAnalysis /></el-icon> Quality Intelligence</h2>
                                <div class="lp-quality-layout">
                                    <div class="lp-quality-bars">
                                        <div v-for="item in [
                                            { label: 'Aroma',      score: aromaScore },
                                            { label: 'Acidity',    score: acidityScore },
                                            { label: 'Body',       score: bodyScore },
                                            { label: 'Balance',    score: balanceScore },
                                            { label: 'Aftertaste', score: 8.5 },
                                            { label: 'Flavor',     score: 8.8 },
                                        ]" :key="item.label" class="lp-bar-row">
                                            <div class="lp-bar-row__meta">
                                                <span>{{ item.label }}</span>
                                                <strong>{{ item.score.toFixed(2) }}</strong>
                                            </div>
                                            <div class="lp-bar-track">
                                                <div class="lp-bar-fill" :style="{ width: scoreBar(item.score) + '%' }"></div>
                                            </div>
                                        </div>
                                        <div class="lp-defect-note">
                                            <span>Defect Count</span>
                                            <span class="lp-defect-val">0 Cat. 1 &nbsp;&middot;&nbsp; 2 Cat. 2</span>
                                        </div>
                                    </div>
                                    <div class="lp-radar-area">
                                        <svg viewBox="0 0 200 200" class="lp-radar">
                                            <polygon points="100,22 168,61 168,139 100,178 32,139 32,61" fill="none" stroke="#e0e3e5" stroke-width="1.5"/>
                                            <polygon points="100,46 146,74 146,126 100,154 54,126 54,74"  fill="none" stroke="#e0e3e5" stroke-width="1"/>
                                            <polygon points="100,70 124,84 124,116 100,130 76,116 76,84"  fill="none" stroke="#e0e3e5" stroke-width="1"/>
                                            <polygon points="100,30 162,68 162,132 100,170 38,132 38,68"
                                                fill="rgba(0,69,50,0.10)" stroke="#004532" stroke-width="1.8"/>
                                            <circle cx="100" cy="30"  r="3.5" fill="#004532"/>
                                            <circle cx="162" cy="68"  r="3.5" fill="#004532"/>
                                            <circle cx="162" cy="132" r="3.5" fill="#004532"/>
                                            <circle cx="100" cy="170" r="3.5" fill="#004532"/>
                                            <circle cx="38"  cy="132" r="3.5" fill="#004532"/>
                                            <circle cx="38"  cy="68"  r="3.5" fill="#004532"/>
                                            <text x="100" y="13"  text-anchor="middle" class="lp-radar-lbl">Aroma</text>
                                            <text x="180" y="68"  text-anchor="start"  class="lp-radar-lbl">Acidity</text>
                                            <text x="180" y="136" text-anchor="start"  class="lp-radar-lbl">Body</text>
                                            <text x="100" y="194" text-anchor="middle" class="lp-radar-lbl">Aftertaste</text>
                                            <text x="20"  cy="136" text-anchor="end"   class="lp-radar-lbl">Balance</text>
                                            <text x="20"  y="68"  text-anchor="end"    class="lp-radar-lbl">Flavor</text>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Specifications + Packaging -->
                            <div class="lp-two-col-cards">
                                <div class="lp-card">
                                    <h2 class="lp-card__title"><el-icon><Histogram /></el-icon> Specifications</h2>
                                    <div class="lp-kv-stack">
                                        <div class="lp-kv-row"><span>Variety</span><strong>{{ coffeeType }}</strong></div>
                                        <div class="lp-kv-row"><span>Screen Size</span><strong>{{ screenSize }}</strong></div>
                                        <div class="lp-kv-row"><span>Crop Year</span><strong>2025/26</strong></div>
                                        <div class="lp-kv-row"><span>Altitude</span><strong>{{ altitude }}</strong></div>
                                        <div class="lp-kv-row"><span>Processing</span><strong>{{ process }}</strong></div>
                                        <div class="lp-kv-row"><span>Drying</span><strong>Raised Bed</strong></div>
                                        <div class="lp-kv-row"><span>Packaging</span><strong>{{ packaging }}</strong></div>
                                    </div>
                                </div>
                                <div class="lp-card">
                                    <h2 class="lp-card__title"><el-icon><Box /></el-icon> Packaging &amp; Logistics</h2>
                                    <div class="lp-kv-stack">
                                        <div class="lp-kv-row"><span>No. of Bags</span><strong>{{ qtyBags }}</strong></div>
                                        <div class="lp-kv-row"><span>Bag Weight</span><strong>{{ bagWeight }} kg</strong></div>
                                        <div class="lp-kv-row"><span>Net Weight</span><strong>{{ netWeight }} kg</strong></div>
                                        <div class="lp-kv-row"><span>Gross Weight</span><strong>{{ grossWeight }} kg</strong></div>
                                        <div class="lp-kv-row"><span>Warehouse</span><strong>{{ warehouse }}</strong></div>
                                        <div class="lp-kv-row"><span>Storage</span><strong>Controlled Humidity</strong></div>
                                    </div>
                                    <div class="lp-tag-row lp-tag-row--mt">
                                        <span class="lp-origin-tag">Ready to Ship</span>
                                        <span class="lp-origin-tag">Properly Stored</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Export Readiness -->
                            <div class="lp-card">
                                <div class="lp-card__head-row">
                                    <h2 class="lp-card__title"><el-icon><Checked /></el-icon> Export Readiness</h2>
                                    <div class="lp-ring-wrap">
                                        <svg viewBox="0 0 36 36" class="lp-ring">
                                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                fill="none" stroke="#e0e3e5" stroke-width="3"/>
                                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                fill="none" stroke="#004532" stroke-width="3"
                                                :stroke-dasharray="`${exportScore}, 100`" stroke-linecap="round"/>
                                        </svg>
                                        <span class="lp-ring-label">{{ exportScore }}%</span>
                                    </div>
                                </div>
                                <div class="lp-checklist">
                                    <div v-for="item in exportChecklist" :key="item.label"
                                        class="lp-check-item"
                                        :class="item.done ? 'lp-check-item--done' : 'lp-check-item--gap'">
                                        <span class="lp-check-dot"></span>
                                        <span>{{ item.label }}</span>
                                        <span v-if="!item.done" class="lp-check-missing">Missing</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Trading Options -->
                            <div class="lp-card">
                                <h2 class="lp-card__title mb-3"><el-icon><Money /></el-icon> Trading Options</h2>
                                <div class="lp-trade-grid">
                                    <div class="lp-trade-card lp-trade-card--primary">
                                        <div class="lp-trade-card__eyebrow"><el-icon><ShoppingCart /></el-icon> BUY NOW</div>
                                        <div class="lp-trade-card__price">Shs. {{ pricePerKg }}<small>/kg</small></div>
                                        <div class="lp-trade-card__meta">Min. 60 kg · Fixed price</div>
                                        <button class="lp-btn lp-btn--primary lp-btn--full">Buy Now</button>
                                    </div>
                                    <div class="lp-trade-card">
                                        <div class="lp-trade-card__eyebrow"><el-icon><Medal /></el-icon> PLACE BID</div>
                                        <div class="lp-trade-card__price">Shs. {{ fmt(Number(props.lot.price_per_kg || 0) * 0.94, 2) }}<small>/kg</small></div>
                                        <div class="lp-trade-card__meta">3 bids · Closes in 48h</div>
                                        <button class="lp-btn lp-btn--secondary lp-btn--full">Place Bid</button>
                                    </div>
                                    <div class="lp-trade-card">
                                        <div class="lp-trade-card__eyebrow"><el-icon><Trophy /></el-icon> AUCTION</div>
                                        <div class="lp-trade-card__price">Shs. {{ fmt(props.lot.reserve_price || 0, 2) }}<small>/kg</small></div>
                                        <div class="lp-trade-card__meta">Reserve price</div>
                                        <button class="lp-btn lp-btn--tertiary lp-btn--full">Join Auction</button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="lp-side-col">

                            <!-- Market Intelligence -->
                            <div class="lp-card">
                                <h2 class="lp-card__title"><el-icon><TrendCharts /></el-icon> Market Intelligence</h2>
                                <div class="lp-market-hero">
                                    <span>Current Market Price</span>
                                    <strong>Shs. {{ pricePerKg }}/kg</strong>
                                </div>
                                <div class="lp-chart-wrap">
                                    <svg viewBox="0 0 200 56" style="width:100%;display:block;">
                                        <defs>
                                            <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#004532" stop-opacity="0.12"/>
                                                <stop offset="100%" stop-color="#004532" stop-opacity="0"/>
                                            </linearGradient>
                                        </defs>
                                        <polyline points="0,48 30,38 60,42 90,28 120,26 150,16 200,10"
                                            fill="none" stroke="#004532" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                                        <polygon points="0,48 30,38 60,42 90,28 120,26 150,16 200,10 200,56 0,56"
                                            fill="url(#chartGrad)"/>
                                    </svg>
                                    <div class="lp-chart-labels">
                                        <span>7 days ago</span><span>Today</span>
                                    </div>
                                </div>
                                <div class="lp-mkt-kv-grid">
                                    <div class="lp-mkt-kv"><span>7-day Trend</span><strong class="lp-accent">+5.6%</strong></div>
                                    <div class="lp-mkt-kv"><span>Demand</span><strong>High</strong></div>
                                    <div class="lp-mkt-kv"><span>Best Market</span><strong>UAE Specialty</strong></div>
                                    <div class="lp-mkt-kv"><span>Opportunity</span><strong class="lp-accent">92 / 100</strong></div>
                                </div>
                                <div class="lp-ai-callout">
                                    High demand in UAE market — strong selling opportunity right now.
                                </div>
                            </div>

                            <!-- AI Insights -->
                            <div class="lp-card lp-card--tinted">
                                <h2 class="lp-card__title"><el-icon><Star /></el-icon> AI Lot Insights</h2>
                                <ul class="lp-insight-list">
                                    <li class="lp-insight-item lp-insight-item--ok">Lot meets export quality standards.</li>
                                    <li class="lp-insight-item lp-insight-item--ok">Price is competitive for current demand.</li>
                                    <li class="lp-insight-item lp-insight-item--ok">Suitable for UAE specialty buyers.</li>
                                    <li class="lp-insight-item lp-insight-item--warn">Export permit not yet uploaded.</li>
                                </ul>
                            </div>

                            <!-- Seller Profile -->
                            <div class="lp-card">
                                <h2 class="lp-card__title">Seller / Cooperative</h2>
                                <div class="lp-seller">
                                    <div class="lp-seller-avatar">{{ cooperative.charAt(0) }}</div>
                                    <div>
                                        <strong class="lp-seller-name">{{ cooperative }}</strong>
                                        <span class="lp-seller-loc">{{ origin }}</span>
                                    </div>
                                </div>
                                <div class="lp-kv-stack lp-kv-stack--mt">
                                    <div class="lp-kv-row"><span>Rating</span><strong>★ 4.9 / 5.0</strong></div>
                                    <div class="lp-kv-row"><span>Status</span><span class="lp-origin-tag" style="font-size:10px;">Verified</span></div>
                                    <div class="lp-kv-row"><span>Certifications</span><strong>Organic · Fair Trade</strong></div>
                                </div>
                                <button class="lp-btn lp-btn--secondary lp-btn--full lp-btn--mt">Contact Seller</button>
                            </div>

                            <!-- Related Lots -->
                            <div class="lp-card">
                                <h2 class="lp-card__title">Related Lots</h2>
                                <div class="lp-related-stack">
                                    <div v-for="rl in (relatedLots.length ? relatedLots : [
                                        { lot_number: 'LOT-2026-002', origin: 'Sipi Falls, Uganda', price_per_kg: 12.5, quality_score: 86.2 },
                                        { lot_number: 'LOT-2026-003', origin: 'Mt. Elgon, Uganda',  price_per_kg: 11.8, quality_score: 85.5 },
                                        { lot_number: 'LOT-2026-004', origin: 'Rwenzori, Uganda',   price_per_kg: 13.2, quality_score: 88.0 },
                                    ])" :key="rl.lot_number" class="lp-related-row">
                                        <div class="lp-related-row__info">
                                            <strong>{{ rl.lot_number }}</strong>
                                            <span>{{ rl.origin }}</span>
                                        </div>
                                        <div class="lp-related-row__data">
                                            <strong class="lp-accent">Shs. {{ fmt(rl.price_per_kg, 2) }}/kg</strong>
                                            <span>{{ rl.quality_score }} SCA</span>
                                        </div>
                                        <a href="#" class="lp-btn lp-btn--tertiary" style="font-size:11px;padding:5px 12px;white-space:nowrap;">View</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- ── Floating Chatbot ──────────────────────────────────────────── -->
        <button class="lp-fab" @click="chatOpen = !chatOpen" aria-label="Open Lot Advisor">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </button>

        <div v-if="chatOpen" class="lp-chat-panel">
            <div class="lp-chat-panel__head">
                <span>Bean Origin Lot Advisor</span>
                <button class="lp-chat-panel__close" @click="chatOpen = false" aria-label="Close">&#x2715;</button>
            </div>
            <div class="lp-chat-panel__msgs">
                <div v-for="(msg, i) in chatMessages" :key="i"
                    class="lp-msg"
                    :class="msg.role === 'user' ? 'lp-msg--user' : 'lp-msg--bot'">
                    {{ msg.text }}
                </div>
            </div>
            <div class="lp-chat-panel__prompts">
                <button v-for="p in suggestedPrompts" :key="p" class="lp-prompt-btn" @click="usePrompt(p)">{{ p }}</button>
            </div>
            <div class="lp-chat-panel__input">
                <input v-model="chatInput" class="lp-chat-input" placeholder="Ask about this lot…" @keydown.enter="sendChat"/>
                <button class="lp-chat-send" @click="sendChat" aria-label="Send">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ── Design tokens ─────────────────────────────────────────────────────────── */
.lp-root {
    --primary:            #004532;
    --primary-grad:       #065f46;
    --secondary:          #725a42;
    --on-primary:         #ffffff;
    --on-surface:         #191c1e;
    --on-surface-var:     #74777a;
    --surface:            #f7f9fb;
    --surface-low:        #f2f4f6;
    --surface-high:       #e6e8ea;
    --surface-highest:    #e0e3e5;
    --surface-white:      #ffffff;
    --primary-fixed:      #a6f2d1;
    --on-primary-fixed:   #002116;
    --secondary-fixed:    #fedcbe;
    --on-secondary-fixed: #291806;
    --outline-variant:    #bec9c2;
    --shadow-ambient:     0px 20px 40px rgba(25,28,30,0.04);
    --inner-pad:          2rem;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Hero ─────────────────────────────────────────────────────────────────── */
.lp-hero {
    background: linear-gradient(135deg, #004532 0%, #001f17 100%);
    padding: 2.5rem 0 0;
}
.lp-hero__inner {
    padding: 0 var(--inner-pad) 2.25rem;
    display: flex;
    align-items: flex-start;
    gap: 2.5rem;
}
.lp-hero__left { flex: 1; min-width: 0; }
.lp-hero__right { flex-shrink: 0; }

/* Hero tags (on dark bg) */
.lp-tag-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1.25rem; }
.lp-tag-row--mt { margin-top: 1rem; }

.lp-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.9);
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 4px 12px;
    text-transform: uppercase;
}
.lp-hero-tag--warm {
    background: rgba(254,220,190,0.18);
    color: #fedcbe;
}
.lp-hero-tag .el-icon { font-size: 11px; }

/* Origin tags (on light bg, used in cards/trace) */
.lp-origin-tag {
    background: var(--primary-fixed);
    color: var(--on-primary-fixed);
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.05em;
    padding: 4px 12px;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.lp-origin-tag--warm    { background: var(--secondary-fixed); color: var(--on-secondary-fixed); }
.lp-origin-tag--pending { background: var(--surface-high); color: var(--on-surface-var); }

.lp-hero__title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.2;
    color: #ffffff;
    margin: 0 0 0.75rem;
}
.lp-hero__sub {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.6);
    margin: 0 0 1.75rem;
    line-height: 1.6;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}
.lp-sub-icon { font-size: 13px; color: rgba(255,255,255,0.4); }
.lp-sub-sep  { color: rgba(255,255,255,0.25); }
.lp-hero__actions { display: flex; flex-wrap: wrap; gap: 10px; }

/* Hero action buttons */
.lp-btn--hero-outline {
    background: transparent;
    color: #ffffff;
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s ease;
}
.lp-btn--hero-outline:hover { background: rgba(255,255,255,0.1); }
.lp-btn--hero-ghost {
    background: transparent;
    color: rgba(255,255,255,0.6);
    border: none;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color 0.15s ease, background 0.15s ease;
}
.lp-btn--hero-ghost:hover { color: #ffffff; background: rgba(255,255,255,0.08); }

/* Score card */
.lp-score-display {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 0.75rem;
    padding: 1.75rem 2.25rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    min-width: 160px;
}
.lp-score-display__num {
    font-size: 2rem;
    font-weight: 900;
    letter-spacing: -0.03em;
    color: var(--primary-fixed);
    line-height: 1;
}
.lp-score-display__label {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
}
.lp-score-display__grade {
    margin-top: 8px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(166,242,209,0.15);
    color: var(--primary-fixed);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 12px;
}

/* Hero zones strip */
.lp-hero__zones {
    padding: 0 var(--inner-pad);
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    background: var(--surface-white);
    border-top: 1px solid var(--surface-high);
}
.lp-hero-zone {
    padding: 1.5rem;
    background: var(--surface-white);
}
.lp-hero-zone + .lp-hero-zone {
    border-left: 1px solid var(--surface-high);
}
.lp-zone-eyebrow {
    font-size: 0.6875rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    color: var(--primary);
    text-transform: uppercase;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 5px;
}
.lp-zone-eyebrow .el-icon { font-size: 13px; }

.lp-zone-kv-list { display: grid; gap: 0; }

.lp-zone-kv {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    font-size: 0.8125rem;
    padding: 7px 0;
    border-bottom: 1px solid var(--surface-low);
}
.lp-zone-kv:last-child { border-bottom: none; padding-bottom: 0; }
.lp-zone-kv:first-child { padding-top: 0; }

.lp-zone-kv--chips { align-items: flex-start; }

.lp-zone-kv__label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--on-surface-var);
    white-space: nowrap;
    flex-shrink: 0;
}
.lp-zone-kv__label .el-icon { font-size: 12px; opacity: 0.65; }

.lp-zone-kv strong { color: var(--on-surface); font-weight: 600; text-align: right; }

.lp-zone-flavor-row { display: flex; flex-wrap: wrap; gap: 5px; justify-content: flex-end; }
.lp-flavor-chip {
    background: var(--surface-low);
    color: var(--on-surface);
    font-size: 10px;
    font-weight: 600;
    border-radius: 999px;
    padding: 3px 10px;
}

.lp-status-pill {
    background: var(--primary-fixed);
    color: var(--on-primary-fixed);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.05em;
    border-radius: 999px;
    padding: 2px 10px;
    text-transform: uppercase;
}
.lp-demand-chip {
    background: var(--secondary-fixed);
    color: var(--on-secondary-fixed);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.06em;
    border-radius: 4px;
    padding: 2px 8px;
}
.lp-accent { color: var(--primary) !important; }

/* ── Buttons ──────────────────────────────────────────────────────────────── */
.lp-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem;
    font-weight: 700;
    border-radius: 0.375rem;
    padding: 9px 18px;
    border: none;
    cursor: pointer;
    transition: background 0.15s ease, opacity 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
}
.lp-btn--primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-grad) 100%);
    color: var(--on-primary);
}
.lp-btn--primary:hover { opacity: 0.9; }
.lp-btn--secondary {
    background: var(--secondary-fixed);
    color: var(--on-secondary-fixed);
}
.lp-btn--secondary:hover { opacity: 0.88; }
.lp-btn--tertiary {
    background: transparent;
    color: var(--primary);
}
.lp-btn--tertiary:hover { background: var(--surface-high); }
.lp-btn--full { width: 100%; margin-top: 1rem; }
.lp-btn--mt   { margin-top: 1.25rem; }

/* ── Sections ─────────────────────────────────────────────────────────────── */
.lp-section { padding: 2.5rem 0; }
.lp-section--white { background: var(--surface-white); border-top: 1px solid var(--surface-high); }
.lp-section--base  { background: var(--surface-white); border-top: 1px solid var(--surface-high); }
.lp-section__inner { padding: 0 var(--inner-pad); }
.lp-eyebrow {
    font-size: 0.6875rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    color: var(--on-surface-var);
    text-transform: uppercase;
    margin: 0 0 1.75rem;
}

/* ── Traceability ─────────────────────────────────────────────────────────── */
.lp-trace {
    display: flex;
    align-items: flex-start;
    gap: 0;
    overflow-x: auto;
    padding-bottom: 4px;
}
.lp-trace-connector {
    flex: 1;
    height: 2px;
    background: var(--surface-high);
    align-self: center;
    min-width: 32px;
    margin: 0 -2px;
    margin-top: -1.25rem;
}
.lp-trace-step { display: flex; align-items: flex-start; gap: 12px; min-width: 140px; }
.lp-trace-node {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--surface-high);
    color: var(--on-surface-var);
    font-size: 12px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.lp-trace-node--active {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
}
.lp-trace-body { display: grid; gap: 3px; }
.lp-trace-tag {
    font-size: 0.6875rem; font-weight: 800;
    letter-spacing: 0.12em; color: var(--on-surface-var); text-transform: uppercase;
}
.lp-trace-body strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.lp-trace-body span   { font-size: 0.75rem; color: var(--on-surface-var); }
.lp-trace-link { font-size: 0.75rem; font-weight: 600; color: var(--primary); text-decoration: none; }
.lp-trace-link:hover { text-decoration: underline; }

/* ── Main grid ────────────────────────────────────────────────────────────── */
.lp-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.85fr);
    gap: 2rem;
    align-items: start;
}
.lp-main-col  { display: grid; gap: 2rem; }
.lp-side-col  { display: grid; gap: 2rem; }

/* ── Cards ────────────────────────────────────────────────────────────────── */
.lp-card {
    background: #ffffff;
    border: 1px solid var(--surface-high);
    border-radius: 0.5rem;
    padding: 1.75rem;
}
.lp-card--tinted { background: #f6fdf9; border-color: #c8e6d4; }
.lp-card__title {
    font-size: 0.8125rem;
    font-weight: 700;
    letter-spacing: 0;
    color: var(--on-surface);
    margin: 0 0 1.5rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.lp-card__title .el-icon { font-size: 15px; color: var(--primary); }
.lp-card__head-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.lp-card__head-row .lp-card__title { margin-bottom: 0; }

/* ── Quality bars ─────────────────────────────────────────────────────────── */
.lp-quality-layout { display: grid; grid-template-columns: 1fr auto; gap: 2rem; align-items: start; }
.lp-quality-bars { display: grid; gap: 14px; }
.lp-bar-row { display: grid; gap: 6px; }
.lp-bar-row__meta { display: flex; justify-content: space-between; font-size: 0.8125rem; }
.lp-bar-row__meta span   { color: var(--on-surface-var); }
.lp-bar-row__meta strong { color: var(--primary); font-weight: 700; }
.lp-bar-track {
    height: 5px; background: var(--surface-high);
    border-radius: 999px; overflow: hidden;
}
.lp-bar-fill {
    height: 100%; border-radius: 999px;
    background: linear-gradient(90deg, var(--primary), var(--primary-grad));
    transition: width 0.4s ease;
}
.lp-defect-note {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 6px; font-size: 0.8125rem;
}
.lp-defect-note span:first-child { color: var(--on-surface-var); }
.lp-defect-val {
    background: var(--surface-low);
    color: var(--on-surface);
    font-weight: 600;
    border-radius: 4px;
    padding: 2px 8px;
    font-size: 0.75rem;
}
.lp-radar-area { background: var(--surface-low); border-radius: 0.5rem; padding: 12px; width: 180px; }
.lp-radar { width: 100%; }
.lp-radar-lbl { fill: var(--on-surface-var); font-size: 9px; font-family: 'Manrope', sans-serif; }

/* ── KV stack ─────────────────────────────────────────────────────────────── */
.lp-kv-stack { display: grid; gap: 0; }
.lp-kv-stack--mt { margin-top: 1.25rem; }
.lp-kv-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    font-size: 0.8125rem;
    padding: 10px 0;
    background: linear-gradient(var(--surface-low) 0, var(--surface-low) 100%) no-repeat left bottom / 100% 1px;
}
.lp-kv-row:last-child { background: none; }
.lp-kv-row span   { color: var(--on-surface-var); }
.lp-kv-row strong { color: var(--on-surface); font-weight: 600; text-align: right; }

/* ── Two-col cards ────────────────────────────────────────────────────────── */
.lp-two-col-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; }

/* ── Ring ─────────────────────────────────────────────────────────────────── */
.lp-ring-wrap { position: relative; width: 48px; height: 48px; flex-shrink: 0; }
.lp-ring { transform: rotate(-90deg); width: 48px; height: 48px; }
.lp-ring-label {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 800; color: var(--primary);
}

/* ── Checklist ────────────────────────────────────────────────────────────── */
.lp-checklist { display: grid; gap: 10px; }
.lp-check-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.8125rem;
    padding: 10px 14px;
    border-radius: 6px;
    background: var(--surface-low);
    color: var(--on-surface);
}
.lp-check-item--gap { background: #fff9f5; }
.lp-check-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    background: var(--surface-highest);
}
.lp-check-item--done .lp-check-dot {
    background: var(--primary-fixed);
    box-shadow: inset 0 0 0 2.5px var(--on-primary-fixed);
}
.lp-check-missing {
    margin-left: auto;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: var(--secondary);
    background: var(--secondary-fixed);
    border-radius: 4px;
    padding: 2px 8px;
}

/* ── Trade grid ───────────────────────────────────────────────────────────── */
.lp-trade-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.lp-trade-card {
    background: var(--surface-low);
    border-radius: 0.5rem;
    padding: 1.25rem;
    text-align: center;
}
.lp-trade-card--primary { background: #f0faf5; }
.lp-trade-card__eyebrow {
    font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.12em;
    color: var(--on-surface-var); text-transform: uppercase; margin-bottom: 0.75rem;
}
.lp-trade-card__price {
    font-size: 1.125rem; font-weight: 800; color: var(--on-surface); letter-spacing: -0.01em;
}
.lp-trade-card__price small { font-size: 0.75rem; font-weight: 500; color: var(--on-surface-var); }
.lp-trade-card__meta { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 4px; }

/* ── Documents ────────────────────────────────────────────────────────────── */
.lp-doc-grid { display: grid; gap: 10px; }
.lp-doc-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--surface-low);
    border-radius: 6px;
    padding: 12px 14px;
    font-size: 0.8125rem;
}
.lp-doc-item__label { flex: 1; color: var(--on-surface); font-weight: 500; }
.lp-doc-item__actions { display: flex; gap: 6px; }

/* ── Timeline ─────────────────────────────────────────────────────────────── */
.lp-timeline { display: grid; gap: 0; position: relative; }
.lp-tl-item {
    display: flex; align-items: flex-start; gap: 16px;
    padding-bottom: 1.5rem; position: relative;
}
.lp-tl-item:last-child { padding-bottom: 0; }
.lp-tl-dot {
    width: 14px; height: 14px; border-radius: 50%;
    background: var(--surface-high);
    flex-shrink: 0; z-index: 1; margin-top: 2px;
}
.lp-tl-item--done .lp-tl-dot {
    background: var(--primary-fixed);
    box-shadow: inset 0 0 0 3px var(--surface-white);
    outline: 2px solid var(--primary);
}
.lp-tl-spine {
    position: absolute;
    left: 6px; top: 16px; bottom: 0;
    width: 2px;
    background: var(--surface-high);
}
.lp-tl-item--done .lp-tl-spine { background: var(--primary-fixed); }
.lp-tl-body strong { display: block; font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.lp-tl-body span   { font-size: 0.75rem; color: var(--on-surface-var); }
.lp-tl-item--pending .lp-tl-body strong { color: var(--on-surface-var); }

/* ── Market ───────────────────────────────────────────────────────────────── */
.lp-market-hero { margin-bottom: 1rem; }
.lp-market-hero span   { display: block; font-size: 0.75rem; color: var(--on-surface-var); margin-bottom: 4px; }
.lp-market-hero strong { display: block; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; color: var(--on-surface); }
.lp-chart-wrap { background: var(--surface-low); border-radius: 0.5rem; padding: 12px 8px 6px; margin-bottom: 1.25rem; }
.lp-chart-labels {
    display: flex; justify-content: space-between;
    font-size: 0.6875rem; color: var(--on-surface-var); padding: 0 4px;
}
.lp-mkt-kv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 1rem; }
.lp-mkt-kv {
    background: var(--surface-low); border-radius: 6px;
    padding: 10px 12px; display: flex; flex-direction: column; gap: 3px;
}
.lp-mkt-kv span   { font-size: 0.6875rem; color: var(--on-surface-var); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; }
.lp-mkt-kv strong { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); }
.lp-ai-callout {
    background: var(--primary-fixed);
    color: var(--on-primary-fixed);
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    line-height: 1.6;
    padding: 12px 14px;
}

/* ── Insights ─────────────────────────────────────────────────────────────── */
.lp-insight-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
.lp-insight-item {
    font-size: 0.8125rem;
    line-height: 1.6;
    padding: 10px 14px 10px 36px;
    border-radius: 6px;
    position: relative;
    color: var(--on-surface);
}
.lp-insight-item::before {
    content: '';
    position: absolute;
    left: 14px; top: 50%; transform: translateY(-50%);
    width: 10px; height: 10px; border-radius: 50%;
}
.lp-insight-item--ok   { background: var(--surface-low); }
.lp-insight-item--ok::before   { background: var(--primary-fixed); }
.lp-insight-item--warn { background: #fffbf0; }
.lp-insight-item--warn::before { background: var(--secondary-fixed); }

/* ── Seller ───────────────────────────────────────────────────────────────── */
.lp-seller { display: flex; align-items: center; gap: 14px; margin-bottom: 1rem; }
.lp-seller-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
    font-size: 18px; font-weight: 800;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.lp-seller-name { display: block; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.lp-seller-loc  { display: block; font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }

/* ── Related lots ─────────────────────────────────────────────────────────── */
.lp-related-stack { display: grid; gap: 10px; }
.lp-related-row {
    display: flex; align-items: center; gap: 10px;
    background: var(--surface-low); border-radius: 6px; padding: 12px 14px;
}
.lp-related-row__info { flex: 1; }
.lp-related-row__info strong { display: block; font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.lp-related-row__info span   { display: block; font-size: 0.75rem; color: var(--on-surface-var); }
.lp-related-row__data { text-align: right; }
.lp-related-row__data strong { display: block; font-size: 0.8125rem; font-weight: 700; }
.lp-related-row__data span   { display: block; font-size: 0.6875rem; color: var(--on-surface-var); }

/* ── Floating chatbot ─────────────────────────────────────────────────────── */
.lp-fab {
    position: fixed; bottom: 28px; right: 28px; z-index: 300;
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(0, 69, 50, 0.32);
    transition: opacity 0.15s ease;
}
.lp-fab:hover { opacity: 0.9; }

.lp-chat-panel {
    position: fixed; bottom: 92px; right: 28px; z-index: 300;
    width: 320px; border-radius: 0.75rem;
    background: rgba(247, 249, 251, 0.92);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    box-shadow: 0 20px 40px rgba(25, 28, 30, 0.12);
    display: flex; flex-direction: column; overflow: hidden;
    max-height: 480px;
}
.lp-chat-panel__head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
    font-size: 0.8125rem; font-weight: 700;
}
.lp-chat-panel__close {
    background: none; border: none; color: var(--on-primary);
    cursor: pointer; font-size: 14px; padding: 0; line-height: 1;
}
.lp-chat-panel__msgs {
    flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px;
}
.lp-msg {
    border-radius: 10px; font-size: 0.8125rem; line-height: 1.55;
    max-width: 88%; padding: 10px 14px;
}
.lp-msg--bot  { background: var(--surface-white); color: var(--on-surface); align-self: flex-start; }
.lp-msg--user { background: linear-gradient(135deg, var(--primary), var(--primary-grad)); color: var(--on-primary); align-self: flex-end; }
.lp-chat-panel__prompts {
    display: flex; flex-wrap: wrap; gap: 6px; padding: 10px 14px;
    background: var(--surface-low);
}
.lp-prompt-btn {
    background: var(--surface-white); color: var(--primary);
    border: none; border-radius: 999px;
    font-size: 0.6875rem; font-weight: 700; font-family: 'Manrope', sans-serif;
    padding: 5px 12px; cursor: pointer;
    transition: background 0.12s ease;
}
.lp-prompt-btn:hover { background: var(--primary-fixed); color: var(--on-primary-fixed); }
.lp-chat-panel__input {
    display: flex; gap: 8px; padding: 12px 14px; background: var(--surface-white);
}
.lp-chat-input {
    flex: 1; background: var(--surface-low); border: none; border-radius: 6px;
    color: var(--on-surface); font-size: 0.8125rem; font-family: 'Manrope', sans-serif;
    padding: 9px 12px; outline: none;
    transition: background 0.15s ease;
}
.lp-chat-input:focus { background: var(--surface-high); }
.lp-chat-send {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary); border: none; border-radius: 6px;
    width: 36px; height: 36px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* ── Responsive ───────────────────────────────────────────────────────────── */

/* Tablet landscape */
@media (max-width: 1200px) {
    .lp-hero__zones { grid-template-columns: repeat(2, 1fr); }
}

/* Tablet portrait */
@media (max-width: 900px) {
    .lp-root { --inner-pad: 1.25rem; }

    /* Hero */
    .lp-hero { padding: 1.75rem 0 0; }
    .lp-hero__inner { flex-direction: row; align-items: center; gap: 1.25rem; padding-bottom: 1.5rem; }
    .lp-hero__title { font-size: 1.25rem; }
    .lp-hero__sub { font-size: 0.8125rem; }
    .lp-hero__actions { gap: 8px; }
    .lp-score-display { padding: 1.25rem 1.5rem; min-width: 130px; }
    .lp-score-display__num { font-size: 1.75rem; }

    /* Layout */
    .lp-main-grid { grid-template-columns: 1fr; }
    .lp-two-col-cards { grid-template-columns: 1fr; }
    .lp-trade-grid { grid-template-columns: 1fr; }
    .lp-quality-layout { grid-template-columns: 1fr; }
    .lp-radar-area { width: 100%; }

    /* Sections */
    .lp-section { padding: 2rem 0; }

    /* Trace */
    .lp-trace { overflow-x: auto; padding-bottom: 4px; }
}

/* Mobile */
@media (max-width: 640px) {
    .lp-root { --inner-pad: 1rem; }

    /* Hero — stack vertically, hide score card */
    .lp-hero { padding: 1.25rem 0 0; }
    .lp-hero__inner { flex-direction: column; gap: 1rem; padding-bottom: 1.25rem; }
    .lp-hero__right { display: none; }
    .lp-hero__title { font-size: 1.5rem; margin-bottom: 0.5rem; }
    .lp-hero__sub { font-size: 0.8125rem; gap: 4px; }
    .lp-hero__actions {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .lp-hero__actions::-webkit-scrollbar { display: none; }
    .lp-btn { white-space: nowrap; flex-shrink: 0; }

    /* Hero zones — 1 col */
    .lp-hero__zones { grid-template-columns: 1fr; }
    .lp-hero-zone + .lp-hero-zone { border-left: none; border-top: 1px solid var(--surface-high); }
    .lp-hero-zone:nth-child(3),
    .lp-hero-zone:nth-child(4) { border-top: 1px solid var(--surface-high); }
    .lp-hero-zone { padding: 1rem; }

    /* Tag row — scroll */
    .lp-tag-row {
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
        padding-bottom: 2px;
    }
    .lp-tag-row::-webkit-scrollbar { display: none; }
    .lp-hero-tag { flex-shrink: 0; }

    /* Sections */
    .lp-section { padding: 1.5rem 0; }

    /* Cards */
    .lp-card { padding: 1.25rem; }
    .lp-card__title { margin-bottom: 1rem; }

    /* Traceability — vertical */
    .lp-trace { flex-direction: column; gap: 0; }
    .lp-trace-step { padding: 0.75rem 0; }
    .lp-trace-connector { width: 2px; height: 20px; margin: 0 0 0 15px; min-width: unset; align-self: flex-start; }

    /* Quality bars */
    .lp-radar-area { display: none; }

    /* Trade cards */
    .lp-trade-grid { grid-template-columns: 1fr; gap: 0.75rem; }
    .lp-trade-card { padding: 1rem; }
    .lp-btn--full { margin-top: 0.75rem; }

    /* KV rows — shrink text */
    .lp-kv-row { font-size: 0.8125rem; }
    .lp-kv-row strong { font-size: 0.8125rem; max-width: 55%; text-align: right; word-break: break-word; }

    /* Market grid — 2 col stays */
    .lp-mkt-kv-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
    .lp-mkt-kv { padding: 8px 10px; }

    /* Docs */
    .lp-doc-item { flex-wrap: wrap; gap: 8px; }
    .lp-doc-item__actions { width: 100%; }

    /* Timeline */
    .lp-tl-item { padding-bottom: 1.25rem; }

    /* Related lots */
    .lp-related-row { flex-wrap: wrap; gap: 6px; }
    .lp-related-row__data { text-align: left; }

    /* Chat */
    .lp-chat-panel { right: 0; left: 0; bottom: 0; width: 100%; border-radius: 1rem 1rem 0 0; max-height: 70vh; }
    .lp-fab { right: 16px; bottom: 20px; }
}

/* Small mobile */
@media (max-width: 420px) {
    .lp-root { --inner-pad: 0.875rem; }
    .lp-hero__title { font-size: 1.25rem; }
    .lp-hero-zone { padding: 0.875rem; }
    .lp-card { padding: 1rem; }
    .lp-zone-eyebrow { font-size: 0.625rem; }
    .lp-two-col-cards { gap: 1rem; }
    .lp-main-grid { gap: 1rem; }
    .lp-main-col { gap: 1rem; }
    .lp-side-col { gap: 1rem; }
}
</style>
