<script setup>
import { ref } from 'vue';
import {
    ChatDotRound, Check, Checked, CollectionTag,
    Connection, DataLine, Document, Location,
    MapLocation, Medal, Opportunity, Promotion,
    Reading, Share, ShoppingCart, Star, TrendCharts,
    Van, Warning,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

/* ── Regions ───────────────────────────────────────────────────── */
const regions = [
    {
        name: 'Central Uganda',     type: 'Robusta',
        altitude: '900–1,200 m',   notes: ['Bold', 'Earthy', 'Strong body'],
        score: '82–86',            demand: 'Very High', tone: 'danger',
        icon: '🌿',
    },
    {
        name: 'Rwenzori Mountains', type: 'Arabica',
        altitude: '1,500–2,200 m', notes: ['Fruity', 'Chocolate', 'Winey'],
        score: '86–90',            demand: 'High', tone: 'success',
        icon: '⛰️',
    },
    {
        name: 'Mount Elgon',        type: 'Arabica',
        altitude: '1,800–2,400 m', notes: ['Floral', 'Citrus', 'Bright acidity'],
        score: '87–92',            demand: 'High', tone: 'success',
        icon: '🏔️',
    },
    {
        name: 'Southwestern Uganda',type: 'Mixed',
        altitude: '1,200–1,800 m', notes: ['Balanced', 'Nutty', 'Smooth'],
        score: '83–88',            demand: 'Medium', tone: 'warning',
        icon: '🌄',
    },
];

/* ── Overview stats ────────────────────────────────────────────── */
const overview = [
    { label: 'Coffee Types',    value: 'Robusta + Arabica', icon: CollectionTag },
    { label: 'Key Regions',     value: '4 Major Zones',     icon: MapLocation   },
    { label: 'Avg Altitude',    value: '1,200–2,400 m',     icon: TrendCharts   },
    { label: 'Avg Cup Score',   value: '84–92',             icon: Star          },
    { label: 'Export Demand',   value: 'Very High',         icon: Van           },
    { label: 'Global Ranking',  value: '#2 in Africa',      icon: Medal         },
];

/* ── Flavor profile ────────────────────────────────────────────── */
const flavorBars = [
    { label: 'Aroma',      pct: 88, color: '#004532' },
    { label: 'Flavor',     pct: 84, color: '#065f46' },
    { label: 'Acidity',    pct: 76, color: '#059669' },
    { label: 'Body',       pct: 92, color: '#10b981' },
    { label: 'Aftertaste', pct: 80, color: '#34d399' },
];
const flavorTags = ['Chocolate', 'Citrus', 'Fruity', 'Nutty', 'Earthy', 'Winey'];

/* ── Comparison ────────────────────────────────────────────────── */
const comparison = [
    { type: 'Ugandan Robusta', region: 'Central Uganda', altitude: '900–1,200 m', flavor: 'Bold, earthy, strong', use: 'Espresso blends, instant', markets: 'UAE, Italy, Vietnam', price: '$1.80–2.90/kg' },
    { type: 'Ugandan Arabica', region: 'Rwenzori, Elgon', altitude: '1,500–2,400 m', flavor: 'Fruity, floral, bright', use: 'Specialty, filter', markets: 'Germany, USA, Japan', price: '$4.00–6.50/kg' },
];

/* ── Harvest calendar ──────────────────────────────────────────── */
const calendar = [
    { label: 'Main Crop',       months: 'Oct – Feb',  color: '#004532', width: '42%', left: '0%'   },
    { label: 'Fly Crop',        months: 'May – Jul',  color: '#059669', width: '25%', left: '42%'  },
    { label: 'Peak Harvest',    months: 'Nov – Jan',  color: '#f59e0b', width: '30%', left: '5%'   },
    { label: 'Processing',      months: 'Oct – Mar',  color: '#3b82f6', width: '50%', left: '0%'   },
    { label: 'Export Window',   months: 'Jan – Jun',  color: '#8b5cf6', width: '50%', left: '50%'  },
];

/* ── Processing methods ────────────────────────────────────────── */
const methods = [
    { name: 'Washed',    desc: 'Clean, bright, high clarity',     icon: '💧', tone: 'primary' },
    { name: 'Natural',   desc: 'Fruity, heavy, wine-like body',   icon: '☀️', tone: 'warning' },
    { name: 'Honey',     desc: 'Balanced, sweet, smooth finish',  icon: '🍯', tone: 'amber'   },
    { name: 'Sun-Dried', desc: 'Traditional, full body, rustic',  icon: '🌾', tone: 'success' },
];

/* ── Demand by market ──────────────────────────────────────────── */
const markets = [
    { market: 'UAE',          demand: 'Extreme', coffee: 'Robusta',      score: 96, tone: 'danger'  },
    { market: 'Germany',      demand: 'High',    coffee: 'Arabica',      score: 84, tone: 'warning' },
    { market: 'USA',          demand: 'Medium',  coffee: 'Premium Blends', score: 72, tone: 'info'  },
    { market: 'Saudi Arabia', demand: 'High',    coffee: 'Specialty',    score: 79, tone: 'warning' },
];

/* ── Available lots ────────────────────────────────────────────── */
const lots = [
    { name: 'Mount Elgon AA',     region: 'Mount Elgon',   type: 'Arabica', score: 89.2, price: '$5.20/kg', qty: '4,200 kg', badges: ['Verified','Export Ready'] },
    { name: 'Kyoga Robusta R1',   region: 'Central Uganda',type: 'Robusta', score: 84.6, price: '$1.88/kg', qty: '12,500 kg',badges: ['Verified','Tokenised']    },
    { name: 'Rwenzori Select',    region: 'Rwenzori',       type: 'Arabica', score: 87.8, price: '$4.60/kg', qty: '3,800 kg', badges: ['Verified','Export Ready','Premium'] },
    { name: 'Sipi Falls Natural', region: 'Mount Elgon',   type: 'Arabica', score: 91.0, price: '$6.10/kg', qty: '1,200 kg', badges: ['Verified','Blockchain Verified'] },
    { name: 'Bugisu AA Reserve',  region: 'Mount Elgon',   type: 'Arabica', score: 88.5, price: '$5.80/kg', qty: '2,600 kg', badges: ['Verified','Export Ready','Tokenised'] },
    { name: 'Kasese Robusta',     region: 'Central Uganda',type: 'Robusta', score: 83.2, price: '$1.72/kg', qty: '8,000 kg', badges: ['Verified'] },
];

/* ── Trade opportunities ───────────────────────────────────────── */
const tradeOpps = [
    { route: 'Uganda Robusta',   market: 'UAE',     priceRange: '$2.70–3.10', demand: 'Extreme', risk: 'Low'    },
    { route: 'Rwenzori Arabica', market: 'Germany', priceRange: '$4.40–4.80', demand: 'High',    risk: 'Medium' },
    { route: 'Mount Elgon AA',   market: 'USA',     priceRange: '$4.90–5.30', demand: 'Medium',  risk: 'Low'    },
];

/* ── AI insights ───────────────────────────────────────────────── */
const insights = [
    { text: 'Ugandan Robusta demand is rising globally, especially in UAE markets.',         tone: 'success' },
    { text: 'Rwenzori Arabica has strong specialty positioning with scores above 87.',      tone: 'primary' },
    { text: 'Traceable, blockchain-verified lots attract 18–25% premium from buyers.',      tone: 'info'    },
];

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: 'Hi! I\'m your Uganda Coffee Advisor. Ask me about regions, quality, or available lots.' },
]);
const prompts = ['Which Ugandan coffee is best?', 'What quality does Uganda produce?', 'Which region is premium?', 'Which lots are available?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'Mount Elgon produces some of Uganda\'s finest Arabica with cup scores above 89. Rwenzori also offers exceptional specialty-grade lots for export to Germany and Japan.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };

const badgeClass = (b) => {
    if (b === 'Verified')            return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (b === 'Export Ready')        return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (b === 'Tokenised')           return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    if (b === 'Premium')             return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    if (b === 'Blockchain Verified') return 'bg-dark text-white';
    return 'bg-light text-secondary border';
};
</script>

<template>
    <OuterLayout title="Coffee Origins – Uganda">

        <div class="og-page">

            <!-- ── 1. Hero ─────────────────────────────────────────────── -->
            <section class="og-hero">
                <div class="container-fluid px-3 px-lg-5">
                    <div class="row align-items-center g-4 py-5">
                        <div class="col-12 col-lg-7">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="og-hero-badge">Africa's Leading Robusta Producer</span>
                                <span class="og-hero-badge og-hero-badge--soft">Specialty Arabica Growing</span>
                                <span class="og-hero-badge og-hero-badge--outline">Export Ready</span>
                            </div>
                            <h1 class="og-hero-title">Ugandan Coffee Origins</h1>
                            <p class="og-hero-sub">Explore the quality, regions, and global potential of Uganda's exceptional coffee — from bold Robusta plains to high-altitude Arabica peaks.</p>
                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button class="btn og-btn-primary"><el-icon><ShoppingCart /></el-icon> View Available Lots</button>
                                <button class="btn og-btn-outline"><el-icon><TrendCharts /></el-icon> Explore Market</button>
                                <button class="btn og-btn-ghost"><el-icon><Share /></el-icon> Contact Cooperatives</button>
                                <button class="btn og-btn-ghost"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <div class="og-hero-visual">
                                <div class="og-hero-map">
                                    <div class="og-map-dot og-map-dot--1"><span>Rwenzori</span></div>
                                    <div class="og-map-dot og-map-dot--2"><span>Mt. Elgon</span></div>
                                    <div class="og-map-dot og-map-dot--3"><span>Central</span></div>
                                    <div class="og-map-dot og-map-dot--4"><span>Southwest</span></div>
                                    <div class="og-map-label">Uganda</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="container-fluid px-3 px-lg-4 pb-5">

                <!-- ── 2. Overview Stats ──────────────────────────────── -->
                <div class="row g-2 mb-4">
                    <div v-for="stat in overview" :key="stat.label" class="col-6 col-sm-4 col-lg-2">
                        <div class="og-stat-card h-100">
                            <span class="og-stat-icon"><el-icon><component :is="stat.icon" /></el-icon></span>
                            <div class="og-stat-label">{{ stat.label }}</div>
                            <div class="og-stat-value">{{ stat.value }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── 3. Key Regions ─────────────────────────────────── -->
                <div class="og-section-head mb-3">
                    <div class="og-section-title"><el-icon class="og-section-icon"><MapLocation /></el-icon> Key Growing Regions</div>
                    <p class="og-section-sub">Four distinct zones producing Uganda's diverse coffee profiles</p>
                </div>
                <div class="row g-3 mb-4">
                    <div v-for="region in regions" :key="region.name" class="col-12 col-sm-6 col-lg-3">
                        <div class="og-region-card h-100">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="og-region-emoji">{{ region.icon }}</div>
                                    <div class="og-region-name">{{ region.name }}</div>
                                </div>
                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                    :class="region.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' :
                                            region.tone === 'danger'  ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' :
                                            'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                    {{ region.type }}
                                </span>
                            </div>
                            <div class="og-region-meta"><el-icon><TrendCharts /></el-icon> {{ region.altitude }}</div>
                            <div class="d-flex flex-wrap gap-1 my-2">
                                <span v-for="note in region.notes" :key="note" class="og-flavor-chip">{{ note }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                                <span class="og-score-label">Score: <strong>{{ region.score }}</strong></span>
                                <span class="og-demand-pill" :class="`og-demand-pill--${region.tone}`">{{ region.demand }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 4. Quality & Flavor Profile ────────────────────── -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="og-card h-100">
                            <div class="og-card-title mb-3">
                                <el-icon class="og-card-icon"><Star /></el-icon>
                                Quality &amp; Flavor Profile
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <div v-for="bar in flavorBars" :key="bar.label" class="og-flavor-bar-row">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="og-bar-label">{{ bar.label }}</span>
                                        <span class="og-bar-pct">{{ bar.pct }}%</span>
                                    </div>
                                    <div class="og-bar-track">
                                        <div class="og-bar-fill" :style="{ width: `${bar.pct}%`, background: bar.color }"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.7rem;">Premium</span>
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle" style="font-size:.7rem;">Specialty Potential</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.7rem;">Export Grade</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="og-card h-100">
                            <div class="og-card-title mb-3">
                                <el-icon class="og-card-icon"><CollectionTag /></el-icon>
                                Flavor Tags
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span v-for="tag in flavorTags" :key="tag" class="og-tag">{{ tag }}</span>
                            </div>
                            <div class="og-cup-score-card">
                                <div class="og-cup-score-inner">
                                    <div class="og-cup-score-ring">
                                        <span class="og-cup-score-num">89</span>
                                        <span class="og-cup-score-max">/100</span>
                                    </div>
                                    <div>
                                        <div class="og-cup-score-label">Average SCA Score</div>
                                        <div class="og-cup-score-sub">Top-tier Arabica lots</div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-4"><div class="og-mini-stat"><span>Robusta</span><strong>84–87</strong></div></div>
                                    <div class="col-4"><div class="og-mini-stat"><span>Arabica</span><strong>87–92</strong></div></div>
                                    <div class="col-4"><div class="og-mini-stat og-mini-stat--green"><span>Premium</span><strong>90+</strong></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 5. Arabica vs Robusta ───────────────────────────── -->
                <div class="og-card mb-4">
                    <div class="og-card-title mb-3">
                        <el-icon class="og-card-icon"><DataLine /></el-icon>
                        Arabica vs Robusta
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 og-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Region</th>
                                    <th>Altitude</th>
                                    <th>Flavor</th>
                                    <th>Use</th>
                                    <th>Demand Markets</th>
                                    <th>Price Range</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in comparison" :key="row.type">
                                    <td><span class="og-type-pill" :class="row.type.includes('Arabica') ? 'og-type-pill--arabica' : 'og-type-pill--robusta'">{{ row.type }}</span></td>
                                    <td class="og-td-muted">{{ row.region }}</td>
                                    <td class="og-td-muted">{{ row.altitude }}</td>
                                    <td style="font-size:.8125rem;">{{ row.flavor }}</td>
                                    <td class="og-td-muted">{{ row.use }}</td>
                                    <td style="font-size:.8125rem;font-weight:600;">{{ row.markets }}</td>
                                    <td class="og-td-price">{{ row.price }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── 6. Harvest Calendar ────────────────────────────── -->
                <div class="og-card mb-4">
                    <div class="og-card-title mb-3">
                        <el-icon class="og-card-icon"><Reading /></el-icon>
                        Harvest Calendar
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div v-for="item in calendar" :key="item.label" class="og-cal-row">
                            <div class="og-cal-label">{{ item.label }}</div>
                            <div class="og-cal-track">
                                <div class="og-cal-bar" :style="{ width: item.width, marginLeft: item.left, background: item.color }"></div>
                            </div>
                            <div class="og-cal-months">{{ item.months }}</div>
                        </div>
                        <div class="og-cal-months-axis">
                            <span v-for="m in ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']" :key="m">{{ m }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── 7. Processing Methods ──────────────────────────── -->
                <div class="og-section-head mb-3">
                    <div class="og-section-title"><el-icon class="og-section-icon"><CollectionTag /></el-icon> Processing Methods</div>
                </div>
                <div class="row g-2 mb-4">
                    <div v-for="m in methods" :key="m.name" class="col-6 col-md-3">
                        <div class="og-method-card h-100">
                            <div class="og-method-icon">{{ m.icon }}</div>
                            <div class="og-method-name">{{ m.name }}</div>
                            <div class="og-method-desc">{{ m.desc }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── 8. Demand by Market ────────────────────────────── -->
                <div class="og-section-head mb-3">
                    <div class="og-section-title"><el-icon class="og-section-icon"><Location /></el-icon> Demand by Market</div>
                </div>
                <div class="row g-2 mb-4">
                    <div v-for="m in markets" :key="m.market" class="col-6 col-lg-3">
                        <div class="og-market-card h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="og-market-name">{{ m.market }}</div>
                                <span class="og-demand-pill" :class="`og-demand-pill--${m.tone}`">{{ m.demand }}</span>
                            </div>
                            <div class="og-market-coffee">{{ m.coffee }}</div>
                            <div class="og-bar-track mt-2 mb-1">
                                <div class="og-bar-fill"
                                    :style="{ width: `${m.score}%`,
                                              background: m.tone === 'danger' ? '#ef4444' : m.tone === 'warning' ? '#f59e0b' : m.tone === 'info' ? '#3b82f6' : '#004532' }">
                                </div>
                            </div>
                            <div class="og-market-score">Opportunity: {{ m.score }}/100</div>
                        </div>
                    </div>
                </div>

                <!-- ── 9. Available Lots ───────────────────────────────── -->
                <div class="og-section-head mb-3">
                    <div class="og-section-title"><el-icon class="og-section-icon"><Medal /></el-icon> Available Ugandan Lots</div>
                    <p class="og-section-sub">Verified, traceable, and export-ready coffee lots from Uganda</p>
                </div>
                <div class="row g-3 mb-4">
                    <div v-for="lot in lots" :key="lot.name" class="col-12 col-sm-6 col-xl-4">
                        <div class="og-lot-card h-100">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="og-lot-name">{{ lot.name }}</div>
                                    <div class="og-lot-region">{{ lot.region }}</div>
                                </div>
                                <span class="og-type-pill" :class="lot.type === 'Arabica' ? 'og-type-pill--arabica' : 'og-type-pill--robusta'">{{ lot.type }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span v-for="b in lot.badges" :key="b" class="badge rounded-pill og-lot-badge" :class="badgeClass(b)">{{ b }}</span>
                            </div>
                            <div class="row g-1 mb-3">
                                <div class="col-4"><div class="og-mini-stat"><span>Score</span><strong>{{ lot.score }}</strong></div></div>
                                <div class="col-4"><div class="og-mini-stat og-mini-stat--green"><span>Price</span><strong>{{ lot.price }}</strong></div></div>
                                <div class="col-4"><div class="og-mini-stat"><span>Quantity</span><strong>{{ lot.qty }}</strong></div></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn og-btn-primary btn-sm flex-fill">View Lot</button>
                                <button class="btn og-btn-outline btn-sm flex-fill"><el-icon><ShoppingCart /></el-icon> Buy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 10. Sustainability ─────────────────────────────── -->
                <div class="og-card mb-4">
                    <div class="og-card-title mb-3">
                        <el-icon class="og-card-icon"><Check /></el-icon>
                        Sustainability &amp; Traceability
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="og-sustain-item">
                                <div class="og-sustain-icon">🌱</div>
                                <div class="og-sustain-title">Farm-to-Lot Tracking</div>
                                <div class="og-sustain-desc">Full traceability from farm to export, verified on blockchain.</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="og-sustain-item">
                                <div class="og-sustain-icon">👨‍🌾</div>
                                <div class="og-sustain-title">Verified Farmers</div>
                                <div class="og-sustain-desc">All producers are registered, verified, and profile-reviewed.</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="og-sustain-item">
                                <div class="og-sustain-icon">♻️</div>
                                <div class="og-sustain-title">Climate-Smart Farming</div>
                                <div class="og-sustain-desc">Practices designed to adapt to and mitigate climate change.</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="og-sustain-item">
                                <div class="og-sustain-icon">🤝</div>
                                <div class="og-sustain-title">Ethical Sourcing</div>
                                <div class="og-sustain-desc">Fair pricing and direct partnerships with cooperatives.</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Traceable</span>
                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Sustainable</span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Ethical</span>
                    </div>
                </div>

                <!-- ── 11. Trade Opportunities ────────────────────────── -->
                <div class="og-section-head mb-3">
                    <div class="og-section-title"><el-icon class="og-section-icon"><Van /></el-icon> Trade Opportunities</div>
                </div>
                <div class="row g-3 mb-4">
                    <div v-for="opp in tradeOpps" :key="opp.route" class="col-12 col-md-4">
                        <div class="og-opp-card h-100">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="og-opp-route">{{ opp.route }}</div>
                                    <div class="og-opp-market">→ {{ opp.market }}</div>
                                </div>
                                <span class="badge" style="font-size:.65rem;"
                                    :class="opp.risk === 'Low' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                    {{ opp.risk }} Risk
                                </span>
                            </div>
                            <div class="row g-1 mb-3">
                                <div class="col-6"><div class="og-mini-stat"><span>Price Range</span><strong>{{ opp.priceRange }}</strong></div></div>
                                <div class="col-6"><div class="og-mini-stat og-mini-stat--green"><span>Demand</span><strong>{{ opp.demand }}</strong></div></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn og-btn-outline btn-sm flex-fill">View Lots</button>
                                <button class="btn og-btn-primary btn-sm flex-fill">Trade Now</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 12. AI Insights ────────────────────────────────── -->
                <div class="og-section-head mb-3">
                    <div class="og-section-title"><el-icon class="og-section-icon"><Opportunity /></el-icon> AI Insights</div>
                </div>
                <div class="row g-2 mb-4">
                    <div v-for="ins in insights" :key="ins.text" class="col-12 col-md-4">
                        <div class="og-insight-card" :class="`og-insight-card--${ins.tone}`">
                            <el-icon class="og-insight-icon"><Opportunity /></el-icon>
                            <p class="og-insight-text">{{ ins.text }}</p>
                        </div>
                    </div>
                </div>

                <div class="pb-4"></div>
            </div><!-- /container -->

            <!-- ── 13. Floating Chatbot ───────────────────────────────── -->
            <div class="og-fab-wrap">
                <Transition name="og-chat">
                    <div v-if="chatOpen" class="og-chatbot">
                        <div class="og-chatbot__head">
                            <div class="og-chatbot__identity">
                                <div class="og-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="og-chatbot__name">Origin Advisor</div>
                                    <div class="og-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="og-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="og-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="og-chat-msg" :class="`og-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="og-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="og-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="og-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask about Uganda coffee…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="og-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div><!-- /og-page -->
    </OuterLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.og-page {
    --green:          #004532;
    --green-grad:     #065f46;
    --on-green:       #ffffff;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-white:  #ffffff;
    --surface-low:    #f8fafc;
    --surface-mid:    #f1f5f9;
    --surface-high:   #eef2f0;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100vh;
}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.og-hero {
    background: linear-gradient(160deg, #001f12 0%, #004532 60%, #065f46 100%);
    color: #fff;
}
.og-hero-badge {
    display: inline-flex; align-items: center;
    background: rgba(255,255,255,0.12); color: #fff;
    border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 4px 12px;
}
.og-hero-badge--soft    { background: rgba(166,242,209,0.2); color: #a6f2d1; }
.og-hero-badge--outline { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: rgba(255,255,255,0.8); }
.og-hero-title { font-size: clamp(1.5rem,3vw,2.25rem); font-weight: 800; letter-spacing: -0.03em; margin: 0 0 0.75rem; }
.og-hero-sub   { font-size: 0.9375rem; color: rgba(255,255,255,0.8); line-height: 1.65; max-width: 520px; margin: 0; }

/* Hero map visual */
.og-hero-visual { display: flex; align-items: center; justify-content: center; }
.og-hero-map {
    position: relative; width: 280px; height: 280px;
    background: rgba(255,255,255,0.05); border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.1);
}
.og-map-label {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
    font-size: 0.875rem; font-weight: 800; color: rgba(255,255,255,0.5); letter-spacing: 0.08em;
}
.og-map-dot {
    position: absolute; width: 10px; height: 10px; border-radius: 50%;
    background: #a6f2d1; cursor: pointer;
}
.og-map-dot span {
    position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
    font-size: 0.625rem; font-weight: 700; color: #a6f2d1; white-space: nowrap;
}
.og-map-dot--1 { top: 35%; left: 15%; }
.og-map-dot--2 { top: 25%; right: 18%; }
.og-map-dot--3 { top: 55%; left: 40%; background: #fbbf24; }
.og-map-dot--4 { bottom: 28%; left: 22%; background: #60a5fa; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.og-btn-primary {
    background: var(--green); border-color: var(--green); color: var(--on-green);
    border-radius: 6px; font-size: 0.8125rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.og-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.og-btn-outline {
    background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface);
    border-radius: 6px; font-size: 0.8125rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.og-btn-outline:hover { background: var(--surface-low); }
.og-btn-ghost {
    background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff;
    border-radius: 6px; font-size: 0.8125rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.og-btn-ghost:hover { background: rgba(255,255,255,0.18); }

/* ── Stat Cards ────────────────────────────────────────────────────────────── */
.og-stat-card {
    background: var(--surface-white); border: 1px solid var(--surface-high);
    border-radius: 10px; padding: 0.875rem; text-align: center;
}
.og-stat-icon { display: block; font-size: 18px; color: var(--green); margin-bottom: 6px; }
.og-stat-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.og-stat-value { font-size: 0.875rem; font-weight: 800; color: var(--on-surface); margin-top: 4px; line-height: 1.3; }

/* ── Section headings ──────────────────────────────────────────────────────── */
.og-section-head { margin-top: 1.5rem; }
.og-section-title { display: inline-flex; align-items: center; gap: 8px; font-size: 1rem; font-weight: 700; color: var(--on-surface); }
.og-section-icon  { width: 26px; height: 26px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.og-section-sub   { font-size: 0.8125rem; color: var(--on-surface-var); margin: 4px 0 0; }

/* ── Region Cards ──────────────────────────────────────────────────────────── */
.og-region-card {
    background: var(--surface-white); border: 1px solid var(--surface-high);
    border-radius: 12px; padding: 1rem; display: flex; flex-direction: column;
}
.og-region-emoji { font-size: 1.5rem; line-height: 1; margin-bottom: 4px; }
.og-region-name  { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.og-region-meta  { display: flex; align-items: center; gap: 5px; font-size: 0.75rem; color: var(--on-surface-var); margin: 4px 0; }
.og-flavor-chip  { display: inline-flex; align-items: center; background: rgba(0,69,50,0.07); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 9px; }
.og-score-label  { font-size: 0.75rem; color: var(--on-surface-var); }
.og-score-label strong { color: var(--on-surface); font-weight: 700; }
.og-demand-pill  { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.625rem; font-weight: 700; padding: 2px 8px; }
.og-demand-pill--success { background: #dcfce7; color: #166534; }
.og-demand-pill--danger  { background: #fee2e2; color: #b91c1c; }
.og-demand-pill--warning { background: #fef3c7; color: #92400e; }
.og-demand-pill--info    { background: #dbeafe; color: #1e40af; }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.og-card {
    background: var(--surface-white); border: 1px solid var(--surface-high);
    border-radius: 12px; padding: 1.125rem;
}
.og-card-title { display: inline-flex; align-items: center; gap: 8px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.og-card-icon  { width: 26px; height: 26px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* Flavor bars */
.og-bar-label  { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.og-bar-pct    { font-size: 0.75rem; font-weight: 700; color: var(--green); }
.og-bar-track  { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.og-bar-fill   { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* Flavor tags */
.og-tag { display: inline-flex; align-items: center; background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 999px; font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); padding: 5px 14px; }

/* Cup score */
.og-cup-score-card { background: var(--surface-low); border-radius: 10px; padding: 1rem; }
.og-cup-score-inner { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem; }
.og-cup-score-ring  { width: 56px; height: 56px; border-radius: 50%; background: var(--green); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.og-cup-score-num   { font-size: 1.25rem; font-weight: 800; color: #fff; }
.og-cup-score-max   { font-size: 0.625rem; color: rgba(255,255,255,0.7); }
.og-cup-score-label { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.og-cup-score-sub   { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }
.og-mini-stat { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 8px; text-align: center; }
.og-mini-stat span   { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.og-mini-stat strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); display: block; }
.og-mini-stat--green strong { color: var(--green); }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.og-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 9px 14px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.og-table tbody td { padding: 10px 14px; font-size: 0.8125rem; border-color: var(--surface-low); }
.og-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; }
.og-td-price  { font-weight: 700; color: var(--green); white-space: nowrap; }
.og-type-pill { display: inline-flex; align-items: center; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 9px; }
.og-type-pill--arabica { background: #fef3c7; color: #92400e; }
.og-type-pill--robusta { background: #dbeafe; color: #1e40af; }

/* ── Harvest Calendar ──────────────────────────────────────────────────────── */
.og-cal-row { display: flex; align-items: center; gap: 10px; }
.og-cal-label  { font-size: 0.75rem; font-weight: 600; color: var(--on-surface); min-width: 110px; flex-shrink: 0; }
.og-cal-track  { flex: 1; height: 8px; background: var(--surface-mid); border-radius: 999px; overflow: visible; position: relative; }
.og-cal-bar    { height: 100%; border-radius: 999px; position: absolute; top: 0; }
.og-cal-months { font-size: 0.6875rem; color: var(--on-surface-var); min-width: 80px; flex-shrink: 0; text-align: right; }
.og-cal-months-axis { display: flex; justify-content: space-between; padding-left: 120px; padding-right: 90px; }
.og-cal-months-axis span { font-size: 0.5625rem; color: var(--on-surface-var); font-weight: 600; }

/* ── Processing ────────────────────────────────────────────────────────────── */
.og-method-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 1rem; text-align: center; transition: border-color 0.15s; }
.og-method-card:hover { border-color: var(--green); }
.og-method-icon { font-size: 1.75rem; margin-bottom: 8px; }
.og-method-name { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.og-method-desc { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.45; }

/* ── Market Cards ──────────────────────────────────────────────────────────── */
.og-market-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 1rem; }
.og-market-name   { font-size: 1rem; font-weight: 800; color: var(--on-surface); }
.og-market-coffee { font-size: 0.8125rem; color: var(--on-surface-var); }
.og-market-score  { font-size: 0.6875rem; color: var(--on-surface-var); font-weight: 600; }

/* ── Lot Cards ─────────────────────────────────────────────────────────────── */
.og-lot-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; }
.og-lot-name   { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.og-lot-region { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }
.og-lot-badge  { font-size: 0.6rem; padding: 2px 7px; }

/* ── Sustainability ────────────────────────────────────────────────────────── */
.og-sustain-item { text-align: center; }
.og-sustain-icon  { font-size: 2rem; margin-bottom: 8px; }
.og-sustain-title { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.og-sustain-desc  { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.5; }

/* ── Opportunities ─────────────────────────────────────────────────────────── */
.og-opp-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; }
.og-opp-route  { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.og-opp-market { font-size: 0.8125rem; color: var(--on-surface-var); margin-top: 2px; }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.og-insight-card { display: flex; align-items: flex-start; gap: 10px; padding: 1rem; border-radius: 10px; border: 1px solid; }
.og-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.og-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.og-insight-card--info    { background: #eff6ff; border-color: #bfdbfe; }
.og-insight-icon { font-size: 16px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.og-insight-text { font-size: 0.875rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.og-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.og-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; transition: background 0.15s; }
.og-fab:hover { background: var(--green-grad); }
.og-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.og-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.og-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.og-chatbot__avatar { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 15px; }
.og-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.og-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.og-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.og-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.og-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.og-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.og-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.og-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.og-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.og-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.og-prompt-chip:hover { background: var(--surface-mid); }
.og-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.og-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.og-chatbot__input input:focus { border-color: var(--green); }
.og-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.og-chat-enter-active, .og-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.og-chat-enter-from, .og-chat-leave-to { opacity: 0; transform: translateY(8px); }
</style>
