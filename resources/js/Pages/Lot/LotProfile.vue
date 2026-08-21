<script setup>
import { computed } from 'vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Link } from '@inertiajs/vue3';
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
const aromaScore      = computed(() => Number(props.lot.aroma_score      || 8.75));
const acidityScore    = computed(() => Number(props.lot.acidity_score    || 9.0));
const bodyScore       = computed(() => Number(props.lot.body_score       || 8.25));
const balanceScore    = computed(() => Number(props.lot.balance_score    || 8.5));
const aftertasteScore = computed(() => Number(props.lot.aftertaste_score || 8.5));
const flavorScore     = computed(() => Number(props.lot.flavor_score     || 8.8));
const sweetnessScore  = computed(() => Number(props.lot.sweetness_score  || 10.0));
const uniformityScore = computed(() => Number(props.lot.uniformity_score || 10.0));
const cleanCupScore   = computed(() => Number(props.lot.clean_cup_score  || 10.0));
const overallScore    = computed(() => Number(props.lot.overall_score    || 8.75));

const cuppingAttributes = computed(() => [
    { label: 'Fragrance / Aroma', score: aromaScore.value },
    { label: 'Flavor',            score: flavorScore.value },
    { label: 'Aftertaste',        score: aftertasteScore.value },
    { label: 'Acidity',           score: acidityScore.value },
    { label: 'Body',              score: bodyScore.value },
    { label: 'Balance',           score: balanceScore.value },
    { label: 'Uniformity',        score: uniformityScore.value },
    { label: 'Clean Cup',         score: cleanCupScore.value },
    { label: 'Sweetness',         score: sweetnessScore.value },
    { label: 'Overall',           score: overallScore.value },
]);

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

const complianceDocs = computed(() => [
    { name: 'Certificate of Origin',     category: 'Trade',      date: '20 Feb 2026', available: true  },
    { name: 'Cupping / Quality Report',  category: 'Quality',    date: '18 Feb 2026', available: true  },
    { name: 'Packing List',              category: 'Logistics',  date: '20 Feb 2026', available: true  },
    { name: 'Warehouse Receipt',         category: 'Logistics',  date: '15 Feb 2026', available: Boolean(props.lot.warehouse) },
    { name: 'Organic Certificate',       category: 'Compliance', date: '01 Jan 2026', available: true  },
    { name: 'Fair Trade Certificate',    category: 'Compliance', date: '01 Jan 2026', available: true  },
    { name: 'Phytosanitary Certificate', category: 'Export',     date: null,          available: props.lot.status === 'active' },
    { name: 'Export Permit',             category: 'Export',     date: null,          available: false },
]);

const complianceScore = computed(() => {
    const done = complianceDocs.value.filter(d => d.available).length;
    return Math.round((done / complianceDocs.value.length) * 100);
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


</script>

<template>
    <DesignPreviewLayout title="Lot Profile">

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
                        <div class="lp-hero__actions mt-3">
                            <Link href="/checkout" class="lp-btn lp-btn--hero-outline" style="border: 1px solid #bec9c2;">
                                <el-icon><ShoppingCart /></el-icon> Buy
                            </Link>
                            <button class="lp-btn lp-btn--hero-ghost" style="border: 1px solid #bec9c2;">
                                <el-icon><GoodsFilled /></el-icon> Request Sample
                            </button>
                            <button class="lp-btn lp-btn--hero-ghost" style="border: 1px solid #bec9c2;">
                                <el-icon><Download /></el-icon> Report
                            </button>
                        </div>
                    </div>
                    <!-- Coffee photo + trace QR -->
                    <div class="lp-hero__media">
                    <div class="lp-hero__photo">
                        <img :src="lot.image || 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVNPRKcnvtgsayf1-HlE1xA92LWW1C56Io3VMreh4aujnZTgd7RVNEZOyEqFGcffC6O3JdFFEczJbLDdWYhY3SPZ_97Ep-mSdEA6EpSHOYxQ4YC-9rWllkkDGEgrkRhX8fdY9yD34FR8UBs42K4RgVHEi6OXDt4QvP-hJgG1uWAZlyFMQ7HCYg9NcS7oQW5HysDvCK3FiXBDRpfkupmdW5tIy7o5GV8ZL8feaXnYtU6ZpDEAJvS_XKRffdezzJJCSUQeF2AHlDDapn'" :alt="lotName" class="lp-hero__photo-img" />
                    </div>

                    <div class="lp-hero__right">
                        <div class="lp-qr-block">
                            <p class="lp-qr-label">Trace &amp; Verify</p>
                            <svg class="lp-qr-svg" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" fill="#004532">
                                <rect x="2" y="2" width="22" height="22" rx="2" fill="none" stroke="#004532" stroke-width="2.5"/>
                                <rect x="7" y="7" width="12" height="12" rx="1"/>
                                <rect x="56" y="2" width="22" height="22" rx="2" fill="none" stroke="#004532" stroke-width="2.5"/>
                                <rect x="61" y="7" width="12" height="12" rx="1"/>
                                <rect x="2" y="56" width="22" height="22" rx="2" fill="none" stroke="#004532" stroke-width="2.5"/>
                                <rect x="7" y="61" width="12" height="12" rx="1"/>
                                <rect x="28" y="2" width="4" height="4"/>
                                <rect x="34" y="2" width="4" height="4"/>
                                <rect x="46" y="2" width="4" height="4"/>
                                <rect x="28" y="8" width="4" height="4"/>
                                <rect x="40" y="8" width="4" height="4"/>
                                <rect x="52" y="8" width="4" height="4"/>
                                <rect x="34" y="14" width="4" height="4"/>
                                <rect x="46" y="14" width="4" height="4"/>
                                <rect x="28" y="20" width="4" height="4"/>
                                <rect x="40" y="20" width="4" height="4"/>
                                <rect x="28" y="30" width="4" height="4"/>
                                <rect x="34" y="30" width="4" height="4"/>
                                <rect x="46" y="30" width="4" height="4"/>
                                <rect x="52" y="30" width="4" height="4"/>
                                <rect x="28" y="36" width="4" height="4"/>
                                <rect x="40" y="36" width="4" height="4"/>
                                <rect x="52" y="36" width="4" height="4"/>
                                <rect x="34" y="42" width="4" height="4"/>
                                <rect x="40" y="42" width="4" height="4"/>
                                <rect x="46" y="42" width="4" height="4"/>
                                <rect x="28" y="48" width="4" height="4"/>
                                <rect x="52" y="48" width="4" height="4"/>
                                <rect x="34" y="56" width="4" height="4"/>
                                <rect x="46" y="56" width="4" height="4"/>
                                <rect x="52" y="56" width="4" height="4"/>
                                <rect x="28" y="62" width="4" height="4"/>
                                <rect x="40" y="62" width="4" height="4"/>
                                <rect x="34" y="68" width="4" height="4"/>
                                <rect x="46" y="68" width="4" height="4"/>
                                <rect x="52" y="68" width="4" height="4"/>
                                <rect x="28" y="74" width="4" height="4"/>
                                <rect x="40" y="74" width="4" height="4"/>
                            </svg>
                            <p class="lp-qr-id">{{ lotNumber }}</p>
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
                                <span class="lp-zone-kv__label"><el-icon><Trophy /></el-icon> Cupping Score</span>
                                <strong class="lp-accent">{{ qualityScore.toFixed(1) }} SCA</strong>
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
                                <div class="lp-card__head-row">
                                    <h2 class="lp-card__title"><el-icon><DataAnalysis /></el-icon> Quality Intelligence</h2>
                                    <div class="lp-qi-score">
                                        <span class="lp-qi-score__num">{{ qualityScore.toFixed(1) }}</span>
                                        <span class="lp-qi-score__label">SCA Cupping Score · Grade {{ grade }}</span>
                                    </div>
                                </div>
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
                                            <text x="20"  y="136" text-anchor="end"   class="lp-radar-lbl">Balance</text>
                                            <text x="20"  y="68"  text-anchor="end"    class="lp-radar-lbl">Flavor</text>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Cupping Profile -->
                            <div class="lp-card">
                                <div class="lp-card__head-row">
                                    <h2 class="lp-card__title"><el-icon><DataAnalysis /></el-icon> Cupping Profile</h2>
                                    <span class="lp-origin-tag">SCA Protocol</span>
                                </div>
                                <div class="lp-cupping-grid">
                                    <div class="lp-cupping-attrs">
                                        <div v-for="attr in cuppingAttributes" :key="attr.label" class="lp-cupping-attr-row">
                                            <div class="lp-cupping-attr-meta">
                                                <span>{{ attr.label }}</span>
                                                <strong>{{ attr.score.toFixed(2) }}</strong>
                                            </div>
                                            <div class="lp-bar-track">
                                                <div class="lp-bar-fill" :style="{ width: (attr.score / 10 * 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="lp-cupping-aside">
                                        <div class="lp-cupping-total">
                                            <span class="lp-cupping-total__num">{{ qualityScore.toFixed(2) }}</span>
                                            <span class="lp-cupping-total__label">SCA Total Score</span>
                                            <span class="lp-origin-tag" style="margin-top:8px;">Grade {{ grade }}</span>
                                        </div>
                                        <div class="lp-kv-stack">
                                            <div class="lp-kv-row"><span>Cupped by</span><strong>Q-Grader Certified</strong></div>
                                            <div class="lp-kv-row"><span>Cupping date</span><strong>18 Feb 2026</strong></div>
                                            <div class="lp-kv-row"><span>Cat. 1 Defects</span><strong>0</strong></div>
                                            <div class="lp-kv-row"><span>Cat. 2 Defects</span><strong>2</strong></div>
                                        </div>
                                        <div class="lp-cupping-flavor-block">
                                            <p class="lp-cupping-flavor-label">Flavor Notes</p>
                                            <div class="lp-zone-flavor-row">
                                                <span class="lp-flavor-chip">Blackcurrant</span>
                                                <span class="lp-flavor-chip">Citrus</span>
                                                <span class="lp-flavor-chip">Floral</span>
                                                <span class="lp-flavor-chip">Dark Chocolate</span>
                                                <span class="lp-flavor-chip">Stone Fruit</span>
                                            </div>
                                        </div>
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

                            <!-- Compliance Documents -->
                            <div class="lp-card">
                                <div class="lp-card__head-row">
                                    <h2 class="lp-card__title"><el-icon><Document /></el-icon> Compliance Documents</h2>
                                    <div class="lp-doc-summary">
                                        <div class="lp-ring-wrap">
                                            <svg viewBox="0 0 36 36" class="lp-ring">
                                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                    fill="none" stroke="#e0e3e5" stroke-width="3"/>
                                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                    fill="none" stroke="#004532" stroke-width="3"
                                                    :stroke-dasharray="`${complianceScore}, 100`" stroke-linecap="round"/>
                                            </svg>
                                            <span class="lp-ring-label">{{ complianceScore }}%</span>
                                        </div>
                                        <span class="lp-doc-summary__text">
                                            {{ complianceDocs.filter(d => d.available).length }}/{{ complianceDocs.length }} docs
                                        </span>
                                    </div>
                                </div>
                                <div class="lp-doc-grid">
                                    <div v-for="doc in complianceDocs" :key="doc.name"
                                        class="lp-doc-item"
                                        :class="doc.available ? 'lp-doc-item--ok' : 'lp-doc-item--missing'">
                                        <el-icon class="lp-doc-icon" :class="doc.available ? 'lp-doc-icon--ok' : 'lp-doc-icon--gap'">
                                            <Checked v-if="doc.available" />
                                            <WarningFilled v-else />
                                        </el-icon>
                                        <div class="lp-doc-item__info">
                                            <span class="lp-doc-item__label">{{ doc.name }}</span>
                                            <span class="lp-doc-item__meta">
                                                <span class="lp-doc-cat">{{ doc.category }}</span>
                                                <span v-if="doc.date">· {{ doc.date }}</span>
                                            </span>
                                        </div>
                                        <span class="lp-doc-badge" :class="doc.available ? 'lp-doc-badge--ok' : 'lp-doc-badge--gap'">
                                            {{ doc.available ? 'Available' : 'Missing' }}
                                        </span>
                                        <div class="lp-doc-item__actions">
                                            <button v-if="doc.available" class="lp-btn lp-btn--tertiary lp-doc-dl-btn" title="Download">
                                                <el-icon><Download /></el-icon>
                                            </button>
                                            <button v-else class="lp-btn lp-btn--tertiary lp-doc-dl-btn lp-doc-dl-btn--upload" title="Upload">
                                                <el-icon><Files /></el-icon>
                                            </button>
                                        </div>
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
                                        <Link href="/checkout" class="lp-btn lp-btn--primary lp-btn--full">Buy Now</Link>
                                    </div>
                                    <div class="lp-trade-card">
                                        <div class="lp-trade-card__eyebrow"><el-icon><Medal /></el-icon> PLACE BID</div>
                                        <div class="lp-trade-card__price">Shs. {{ fmt(Number(props.lot.price_per_kg || 0) * 0.94, 2) }}<small>/kg</small></div>
                                        <div class="lp-trade-card__meta">3 bids · Closes in 48h</div>
                                        <Link :href="route('bid.place', lot.id)" class="lp-btn lp-btn--secondary lp-btn--full">Place Bid</Link>
                                    </div>
                                    <div class="lp-trade-card">
                                        <div class="lp-trade-card__eyebrow"><el-icon><Trophy /></el-icon> AUCTION</div>
                                        <div class="lp-trade-card__price">Shs. {{ fmt(props.lot.price || 0, 2) }}<small>/kg</small></div>
                                        <div class="lp-trade-card__meta">Price</div>
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

        <!-- ── Sticky mobile buy bar ────────────────────────────────────── -->
        <div class="lp-mobile-cta">
            <div class="lp-mobile-cta__price">
                <span>Price / kg</span>
                <strong>Shs. {{ pricePerKg }}</strong>
            </div>
            <Link href="/checkout" class="lp-btn lp-btn--primary lp-mobile-cta__buy">
                <el-icon><ShoppingCart /></el-icon> Buy Now
            </Link>
        </div>

    </DesignPreviewLayout>
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
    --surface-high:       #eef2f0;
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
    background: #ffffff;
    border-bottom: 1px solid var(--surface-high);
    padding: 2.5rem 0 0;
}
.lp-hero__inner {
    padding: 0 var(--inner-pad) 2.25rem;
    display: flex;
    align-items: flex-start;
    gap: 2.5rem;
}
.lp-hero__left { flex: 1; min-width: 0; }
.lp-hero__media { flex-shrink: 0; display: flex; align-items: flex-start; gap: 1rem; }
.lp-hero__right { flex-shrink: 0; display: flex; flex-direction: column; gap: 1rem; align-items: center; }

/* Hero tags */
.lp-tag-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1.25rem; }
.lp-tag-row--mt { margin-top: 1rem; }

.lp-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--surface-low);
    color: var(--on-surface);
    border: 1px solid var(--surface-high);
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 4px 12px;
    text-transform: uppercase;
}
.lp-hero-tag--warm {
    background: var(--secondary-fixed);
    color: var(--on-secondary-fixed);
    border-color: transparent;
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
    color: var(--on-surface);
    margin: 0 0 0.75rem;
}
.lp-hero__sub {
    font-size: 0.9rem;
    color: var(--on-surface-var);
    margin: 0 0 1.75rem;
    line-height: 1.6;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}
.lp-sub-icon { font-size: 13px; color: var(--on-surface-var); opacity: 0.65; }
.lp-sub-sep  { color: var(--surface-highest); }
.lp-hero__actions { display: flex; flex-wrap: wrap; gap: 10px; }

/* Hero action buttons */
.lp-btn--hero-outline {
    background: transparent;
    color: var(--on-surface);
    border: 1px solid var(--outline-variant);
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
.lp-btn--hero-outline:hover { background: var(--surface-low); }
.lp-btn--hero-ghost {
    background: transparent;
    color: var(--on-surface-var);
    border: 1px solid var(--outline-variant);
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
.lp-btn--hero-ghost:hover { color: var(--on-surface); background: var(--surface-low); }

/* Score card */
.lp-score-display {
    background: var(--surface-low);
    border: 1px solid var(--surface-high);
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
    color: var(--primary);
    line-height: 1;
}
.lp-score-display__label {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: var(--on-surface-var);
    text-transform: uppercase;
}
.lp-score-display__grade {
    margin-top: 8px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--primary-fixed);
    color: var(--on-primary-fixed);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 12px;
}

/* Coffee photo */
.lp-hero__photo {
    width: 160px;
    height: 160px;
    border-radius: 0.75rem;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid var(--surface-high);
    background: var(--surface-low);
}
.lp-hero__photo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* QR block */
.lp-qr-block {
    background: var(--surface-low);
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    padding: 1rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    width: 160px;
}
.lp-qr-label {
    font-size: 0.6875rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: var(--on-surface-var);
    text-transform: uppercase;
    margin: 0;
}
.lp-qr-svg {
    width: 80px;
    height: 80px;
    display: block;
}
.lp-qr-id {
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--primary);
    font-family: 'IBM Plex Mono', monospace;
    margin: 0;
    letter-spacing: 0.04em;
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

.lp-qi-score { text-align: right; flex-shrink: 0; }
.lp-qi-score__num {
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 1.625rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--primary);
    line-height: 1;
}
.lp-qi-score__label {
    display: block;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--on-surface-var);
    margin-top: 3px;
}

/* ── Cupping profile ──────────────────────────────────────────────────────── */
.lp-cupping-grid {
    display: grid;
    grid-template-columns: 1fr 220px;
    gap: 2rem;
    align-items: start;
}
.lp-cupping-attrs { display: grid; gap: 10px; }
.lp-cupping-attr-row { display: grid; gap: 5px; }
.lp-cupping-attr-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.8125rem;
}
.lp-cupping-attr-meta span   { color: var(--on-surface-var); }
.lp-cupping-attr-meta strong { color: var(--primary); font-weight: 700; }

.lp-cupping-aside { display: flex; flex-direction: column; gap: 1.25rem; }
.lp-cupping-total {
    background: var(--surface-low);
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    padding: 1.25rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}
.lp-cupping-total__num {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 2.25rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--primary);
    line-height: 1;
}
.lp-cupping-total__label {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--on-surface-var);
    margin-bottom: 2px;
}
.lp-cupping-flavor-block { display: flex; flex-direction: column; gap: 8px; }
.lp-cupping-flavor-label {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--on-surface-var);
    margin: 0;
}

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
    font-family: 'IBM Plex Mono', monospace;
    font-size: 1.125rem; font-weight: 700; color: var(--on-surface); letter-spacing: -0.01em;
}
.lp-trade-card__price small { font-size: 0.75rem; font-weight: 500; color: var(--on-surface-var); }
.lp-trade-card__meta { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 4px; }

/* ── Documents ────────────────────────────────────────────────────────────── */
.lp-doc-grid { display: grid; gap: 8px; }
.lp-doc-item {
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: 6px;
    padding: 11px 14px;
    font-size: 0.8125rem;
    border: 1px solid transparent;
}
.lp-doc-item--ok      { background: var(--surface-low); }
.lp-doc-item--missing { background: #fffbf5; border-color: #fde8cc; }

.lp-doc-icon          { font-size: 15px; flex-shrink: 0; }
.lp-doc-icon--ok      { color: var(--primary); }
.lp-doc-icon--gap     { color: #d97706; }

.lp-doc-item__info    { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.lp-doc-item__label   { color: var(--on-surface); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lp-doc-item__meta    { font-size: 0.6875rem; color: var(--on-surface-var); display: flex; gap: 4px; }
.lp-doc-cat           { background: var(--surface-high); border-radius: 4px; padding: 1px 6px; font-weight: 700; font-size: 0.625rem; letter-spacing: 0.04em; text-transform: uppercase; color: var(--on-surface-var); }

.lp-doc-badge         { font-size: 0.625rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; border-radius: 4px; padding: 2px 8px; white-space: nowrap; flex-shrink: 0; }
.lp-doc-badge--ok     { background: var(--primary-fixed); color: var(--on-primary-fixed); }
.lp-doc-badge--gap    { background: #fde8cc; color: #92400e; }

.lp-doc-item__actions { display: flex; gap: 6px; flex-shrink: 0; }
.lp-doc-dl-btn        { padding: 5px 8px !important; font-size: 13px !important; color: var(--on-surface-var) !important; }
.lp-doc-dl-btn:hover  { background: var(--surface-high) !important; color: var(--primary) !important; }
.lp-doc-dl-btn--upload:hover { color: #d97706 !important; }

.lp-doc-summary       { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.lp-doc-summary__text { font-size: 0.75rem; font-weight: 700; color: var(--on-surface-var); }

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
.lp-market-hero strong { display: block; font-family: 'IBM Plex Mono', monospace; font-size: 1.5rem; font-weight: 700; letter-spacing: -0.01em; color: var(--on-surface); }
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

/* ── Sticky mobile buy bar (hidden above the mobile breakpoint) ──────────── */
.lp-mobile-cta { display: none; }

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
    .lp-hero__photo { width: 120px; height: 120px; }
    .lp-score-display { padding: 1.25rem 1.5rem; min-width: 130px; }
    .lp-score-display__num { font-size: 1.75rem; }
    .lp-qr-block { width: 130px; }
    .lp-qr-svg { width: 64px; height: 64px; }

    /* Layout */
    .lp-main-grid { grid-template-columns: 1fr; }
    .lp-two-col-cards { grid-template-columns: 1fr; }
    .lp-trade-grid { grid-template-columns: 1fr; }
    .lp-quality-layout { grid-template-columns: 1fr; }
    .lp-radar-area { width: 100%; }
    .lp-cupping-grid { grid-template-columns: 1fr; }

    /* Sections */
    .lp-section { padding: 2rem 0; }

    /* Trace */
    .lp-trace { overflow-x: auto; padding-bottom: 4px; }
}

/* Mobile */
@media (max-width: 640px) {
    .lp-root { --inner-pad: 1rem; }

    /* Hero — stack vertically; photo + QR shrink into a compact row instead of disappearing */
    .lp-hero { padding: 1.25rem 0 0; }
    .lp-hero__inner { flex-direction: column; gap: 1rem; padding-bottom: 1.25rem; }
    .lp-hero__media { width: 100%; }
    .lp-hero__photo { width: 84px; height: 84px; }
    .lp-hero__right { align-items: flex-start; }
    .lp-qr-block { flex-direction: row; width: auto; padding: 0.625rem 0.875rem; gap: 10px; }
    .lp-qr-svg { width: 44px; height: 44px; }
    .lp-qr-label { display: none; }
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

    /* Sticky mobile buy bar */
    .lp-root { padding-bottom: 4.5rem; }
    .lp-mobile-cta {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 250;
        background: var(--surface-white);
        border-top: 1px solid var(--surface-high);
        padding: 10px 16px calc(10px + env(safe-area-inset-bottom));
        box-shadow: 0 -4px 16px rgba(25, 28, 30, 0.08);
    }
    .lp-mobile-cta__price { display: flex; flex-direction: column; line-height: 1.25; }
    .lp-mobile-cta__price span { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: var(--on-surface-var); }
    .lp-mobile-cta__price strong { font-family: 'IBM Plex Mono', monospace; font-size: 1rem; font-weight: 700; color: var(--on-surface); }
    .lp-mobile-cta__buy { flex-shrink: 0; margin: 0; }
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
