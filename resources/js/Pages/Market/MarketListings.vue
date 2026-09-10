<script setup>
/* Design-fidelity pass: most of this content area still uses placeholder
   data to match the uploaded "Coffee Market" mockup's exact features
   (KPIs, filters, Quick Buy, opportunities, seller spotlight, price/
   demand chart) — real MarketService data will be wired back in once the
   feature set here is signed off. The Lot grid and "All Lots" table are
   already real, backed by MarketService::featuredListing() and
   marketPageListing() respectively. */
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import QuickBuy from '@/Components/Market/QuickBuy.vue';

const props = defineProps({
    featuredLots: { type: Array, default: () => [] },
    markets: { type: Array, default: () => [] },
    priceIndexes: { type: Array, default: () => [] },
    exchangeRates: { type: Array, default: () => [] },
});

/* KPI row — real, derived from the same `markets` data backing the
   "All Lots" table (every live listing). Type is matched
   case-insensitively since it's free-typed metadata with inconsistent
   casing across older listings (see MarketService::listingsByType()).
   "Price Trend" is the real average `percentage_fluctuation` across the
   coffee entries in price_indexes (PriceIndexService) — an actual
   reduction/increase signal, not a fabricated one. */
const kpis = computed(() => {
    const markets = props.markets;
    const arabicaCount = markets.filter((m) => (m.type || '').toLowerCase() === 'arabica').length;
    const robustaCount = markets.filter((m) => (m.type || '').toLowerCase() === 'robusta').length;

    const fluctuations = props.priceIndexes.map((p) => Number(p.percentage_fluctuation)).filter((f) => !Number.isNaN(f));
    const avgFluctuation = fluctuations.length ? fluctuations.reduce((a, b) => a + b, 0) / fluctuations.length : null;
    const trendValue = avgFluctuation != null ? `${avgFluctuation >= 0 ? '+' : ''}${avgFluctuation.toFixed(2)}%` : '—';

    return [
        { label: 'Arabica Lots', value: arabicaCount.toLocaleString() },
        { label: 'Robusta Lots', value: robustaCount.toLocaleString() },
        { label: 'Price Trend', value: trendValue, tone: avgFluctuation == null ? null : (avgFluctuation >= 0 ? 'up' : 'down') },
        { label: 'Lots Available', value: markets.length.toLocaleString() },
    ];
});

/* ── Filter bar — real, filters the "All Lots" table below against
   metadata pulled straight from the live `markets` data (the same
   source MarketService::filterOptions() derives its dropdowns from).
   Price/quality use fixed buckets rather than free-typed ranges to fit
   the compact chip UI; the SCA quality bands match the industry-
   standard Specialty Coffee Association cupping scale. The Lot grid
   above (featured lots) is deliberately left unfiltered — it's curated
   content, not a listing search. */
const filterType = ref('');
const filterOrigin = ref('');
const filterPriceBucket = ref('');
const filterQualityBucket = ref('');

/* Type is free-typed metadata with inconsistent casing across older
   listings (e.g. "Arabica" vs "arabica") — dedupe case-insensitively so
   the dropdown doesn't show near-duplicate options, same fix as the
   Arabica/Robusta KPI counts above. */
const filterTypeOptions = computed(() => {
    const seen = new Map();
    for (const m of props.markets) {
        if (!m.type) continue;
        const key = m.type.toLowerCase();
        if (!seen.has(key)) seen.set(key, key.charAt(0).toUpperCase() + key.slice(1));
    }
    return [...seen.values()].sort();
});
const filterOriginOptions = computed(() => [...new Set(props.markets.map((m) => m.origin).filter(Boolean))].sort());

const priceBuckets = [
    { value: 'under-3', label: 'Under $3/kg', min: 0, max: 3 },
    { value: '3-5', label: '$3 – $5/kg', min: 3, max: 5 },
    { value: '5-8', label: '$5 – $8/kg', min: 5, max: 8 },
    { value: '8-plus', label: '$8/kg & up', min: 8, max: Infinity },
];

const qualityBuckets = [
    { value: '90-plus', label: '90+ Outstanding', min: 90, max: Infinity },
    { value: '85-89', label: '85–89 Excellent', min: 85, max: 90 },
    { value: '80-84', label: '80–84 Very Good', min: 80, max: 85 },
    { value: 'below-80', label: 'Below 80', min: 0, max: 80 },
];

const activeFilterCount = computed(() => [
    filterType.value, filterOrigin.value, filterPriceBucket.value, filterQualityBucket.value,
].filter(Boolean).length);

function resetFilters() {
    filterType.value = '';
    filterOrigin.value = '';
    filterPriceBucket.value = '';
    filterQualityBucket.value = '';
}

const filteredMarkets = computed(() => {
    const price = priceBuckets.find((b) => b.value === filterPriceBucket.value);
    const quality = qualityBuckets.find((b) => b.value === filterQualityBucket.value);

    return props.markets.filter((m) => {
        if (filterType.value && (m.type || '').toLowerCase() !== filterType.value.toLowerCase()) return false;
        if (filterOrigin.value && m.origin !== filterOrigin.value) return false;
        if (price) {
            const p = Number(m.price_per_kg || 0);
            if (p < price.min || p >= price.max) return false;
        }
        if (quality) {
            const q = Number(m.quality_score || 0);
            if (q < quality.min || q >= quality.max) return false;
        }
        return true;
    });
});

/* Lot grid — real, backed by MarketService::featuredListing() (live
   listings where is_featured = true, newest first, limit 3). The badge
   tones alternate green/dark same as the "All Lots" table's cert tags. */
const lots = computed(() => props.featuredLots.map((m) => {
    const quantity = Number(m.quantity || 0);
    const available = Number(m.available_quantity ?? m.quantity ?? 0);
    const unit = m.unit || 'kg';

    return {
        id: m.id,
        name: m.name || m.lot_code,
        code: m.lot_code,
        badges: (m.badges || []).slice(0, 2).map((label, i) => ({ label, tone: i === 0 ? 'primary' : 'secondary' })),
        score: Number(m.quality_score || 0),
        origin: m.origin || '—',
        process: m.process || '—',
        quantity: `${quantity.toLocaleString()} ${unit}`,
        price: `$${Number(m.price_per_kg || 0).toFixed(2)}/kg`,
        priceValue: Number(m.price_per_kg || 0),
        availLabel: `${available.toLocaleString()} ${unit} avail.`,
        image: m.image ? `/storage/${m.image}` : '/images/coffee_image.jpg',
    };
}));

/* ── "Market Listings" section hosts the full lots table (see below)
   instead of the old opportunity callout cards. Real, backed by
   MarketService::marketPageListing() — already every live listing,
   newest-first (`liveMarkets()` runs `orderByDesc('created_at')`), so no
   client-side sort is needed here — just filter (see above) and
   paginate at 15 rows/page. */
const allLots = computed(() => filteredMarkets.value.map((m) => {
    const quantity = Number(m.quantity || 0);
    const unit = m.unit || 'kg';
    const cert = (m.badges || [])[0] || null;

    return {
        id: m.id,
        name: m.name || m.lot_code,
        code: m.lot_code,
        origin: m.origin || '—',
        quantity: `${quantity.toLocaleString()} ${unit}`,
        price: `$${Number(m.price_per_kg || 0).toFixed(2)}/kg`,
        quality: Number(m.quality_score || 0),
        cert,
        certTone: cert ? 'primary-fixed' : null,
    };
}));

const tablePage = ref(1);
const tablePageSize = 15;
const pagedLots = computed(() => {
    const start = (tablePage.value - 1) * tablePageSize;
    return allLots.value.slice(start, start + tablePageSize);
});

watch([filterType, filterOrigin, filterPriceBucket, filterQualityBucket], () => { tablePage.value = 1; });

function goToLotDetails(row) {
    router.visit(route('market.show', row.id));
}

const addingId = ref(null);

function addLotToCart(row) {
    addingId.value = row.id;
    router.post(route('checkout.items.store'), {
        cartable_type: 'market',
        cartable_id: row.id,
        quantity: 1,
    }, {
        preserveScroll: true,
        onFinish: () => { addingId.value = null; },
    });
}

const seller = {
    name: 'Misty Mountains Coop',
    location: 'Sidama Region, Ethiopia',
    rating: 4.9,
    reviews: 124,
    certs: ['RFA Certified', 'Organic Cert'],
};

const priceChartBars = [
    { height: 40, tone: 'low' },
    { height: 55, tone: 'low' },
    { height: 50, tone: 'low' },
    { height: 70, tone: 'primary' },
    { height: 85, tone: 'primary' },
    { height: 75, tone: 'primary' },
    { height: 95, tone: 'secondary' },
];
const priceChartLabels = ['AUG', 'SEP', 'OCT', 'NOV (PROJ)'];

/* ── Quick Buy — selection now lives in the independent <QuickBuy>
   component (resources/js/Components/Market/QuickBuy.vue), v-model'd
   here so the Lot grid's "Select" buttons can still drive it directly.
   Starts unselected: QuickBuy shows a lot-number lookup field until
   either that resolves a match or a featured card is picked. */
const selectedLot = ref(null);

function selectLot(lot) {
    selectedLot.value = lot;
}

const alertsEnabled = ref(true);
const priceDropAlerts = ref(true);
const newSpecialtyLots = ref(true);
</script>

<template>
    <DesignPreviewLayout title="Coffee Market">
        <Head title="Coffee Market">
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
                rel="stylesheet"
            />
        </Head>

        <div class="cm-page">
            <!-- ── Title + actions ─────────────────────────────────────── -->
            <section class="cm-header">
                <div>
                    <h1 class="cm-header__title">Coffee Market</h1>
                    <p class="cm-header__subtitle">Buy verified, traceable, and export-ready coffee lots directly from certified regional cooperatives.</p>
                </div>
                <div class="cm-header__actions">
                    <Link :href="route('orders.index')" class="cm-btn cm-btn--tonal">
                        <span class="material-symbols-outlined">receipt_long</span> My Orders
                    </Link>
                    <button type="button" class="cm-btn cm-btn--secondary" title="Coming soon">
                        <span class="material-symbols-outlined">smart_toy</span> Ask Advisor
                    </button>
                </div>
            </section>

            <!-- ── KPI grid ─────────────────────────────────────────────── -->
            <div class="cm-kpis">
                <div v-for="kpi in kpis" :key="kpi.label" class="cm-kpi">
                    <p class="cm-kpi__label">{{ kpi.label }}</p>
                    <div class="cm-kpi__row">
                        <h3 class="cm-kpi__value" :class="{ 'cm-kpi__value--up': kpi.tone === 'up', 'cm-kpi__value--down': kpi.tone === 'down' }">
                            <span v-if="kpi.tone" class="material-symbols-outlined">{{ kpi.tone === 'up' ? 'arrow_upward' : 'arrow_downward' }}</span>{{ kpi.value }}
                        </h3>
                        <div class="cm-kpi__spark" />
                    </div>
                </div>
            </div>

            <!-- ── Filter bar — real, filters the Market Listings table ─── -->
            <div class="cm-filters">
                <el-select v-model="filterType" placeholder="Coffee Type" clearable class="cm-el-select">
                    <el-option v-for="t in filterTypeOptions" :key="t" :label="t" :value="t" />
                </el-select>
                <el-select v-model="filterOrigin" placeholder="Origin" clearable class="cm-el-select">
                    <el-option v-for="o in filterOriginOptions" :key="o" :label="o" :value="o" />
                </el-select>
                <el-select v-model="filterPriceBucket" placeholder="Price Range" clearable class="cm-el-select">
                    <el-option v-for="b in priceBuckets" :key="b.value" :label="b.label" :value="b.value" />
                </el-select>
                <el-select v-model="filterQualityBucket" placeholder="Quality (SCA)" clearable class="cm-el-select">
                    <el-option v-for="b in qualityBuckets" :key="b.value" :label="b.label" :value="b.value" />
                </el-select>
                <button v-if="activeFilterCount" type="button" class="cm-filters__clear" @click="resetFilters">
                    <span class="material-symbols-outlined">close</span> Clear filters
                </button>
                <div class="cm-filters__alerts">
                    <span>Alerts:</span>
                    <el-switch v-model="alertsEnabled" />
                </div>
            </div>

            <!-- ── Main layout ──────────────────────────────────────────── -->
            <div class="cm-layout">
                <div class="cm-layout__main">
                    <!-- Lot grid — real featured listings -->
                    <div v-if="lots.length" class="cm-lots">
                        <article v-for="lot in lots" :key="lot.id" class="cm-lot">
                            <div class="cm-lot__media">
                                <img :src="lot.image" :alt="lot.name">
                                <div class="cm-lot__badges">
                                    <span v-for="b in lot.badges" :key="b.label" class="cm-badge" :class="`cm-badge--${b.tone}`">{{ b.label }}</span>
                                </div>
                                <div class="cm-lot__score">{{ lot.score }}</div>
                            </div>
                            <div class="cm-lot__body">
                                <div class="cm-lot__head">
                                    <h4 class="cm-lot__name">{{ lot.name }}<br><span>Lot: {{ lot.code }}</span></h4>
                                    <span class="material-symbols-outlined cm-lot__bookmark">bookmark</span>
                                </div>
                                <div class="cm-lot__specs">
                                    <div><p>Origin</p><strong>{{ lot.origin }}</strong></div>
                                    <div><p>Process</p><strong>{{ lot.process }}</strong></div>
                                    <div><p>Quantity</p><strong>{{ lot.quantity }}</strong></div>
                                    <div><p>Price</p><strong class="cm-lot__price">{{ lot.price }}</strong></div>
                                </div>
                                <div class="cm-lot__cta">
                                    <button type="button" class="cm-btn cm-btn--primary cm-btn--block" @click="selectLot(lot)">Select</button>
                                    <button type="button" class="cm-btn cm-btn--tonal cm-btn--block" @click="goToLotDetails(lot)">Details</button>
                                </div>
                            </div>
                        </article>
                    </div>
                    <p v-else class="cm-lots-empty">No featured lots right now — check back soon.</p>

                    <!-- Market Listings -->
                    <section class="cm-section">
                        <h3 class="cm-section__title"><span class="material-symbols-outlined">trending_up</span> Market Listings</h3>

                        <div class="cm-compare">
                            <div class="cm-compare__head">
                                <h3>All Lots</h3>
                                <span class="cm-compare__count">{{ allLots.length }} lots</span>
                            </div>
                            <div class="cm-compare__wrap">
                                <table>
                                    <colgroup>
                                        <col style="width: 26%;"><col style="width: 15%;"><col style="width: 19%;">
                                        <col style="width: 9%;"><col style="width: 16%;"><col style="width: 15%;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>Lot</th><th>Origin</th><th>Quantity &amp; Price</th><th>Score</th><th>Certification</th><th class="cm-compare__action"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in pagedLots" :key="row.id" class="cm-compare__row" @click="goToLotDetails(row)">
                                            <td>
                                                <div class="cm-compare__lot">
                                                    <span class="cm-compare__strong">{{ row.name }}</span>
                                                    <span class="cm-compare__muted">{{ row.code }}</span>
                                                </div>
                                            </td>
                                            <td>{{ row.origin }}</td>
                                            <td>
                                                <div class="cm-compare__lot">
                                                    <span class="cm-compare__muted">{{ row.quantity }}</span>
                                                    <span class="cm-compare__strong">{{ row.price }}</span>
                                                </div>
                                            </td>
                                            <td>{{ row.quality }}</td>
                                            <td>
                                                <span v-if="row.cert" class="cm-tag" :class="`cm-tag--${row.certTone}`">{{ row.cert }}</span>
                                                <span v-else class="cm-compare__muted">—</span>
                                            </td>
                                            <td class="cm-compare__action">
                                                <button
                                                    type="button"
                                                    class="cm-btn cm-btn--tonal cm-btn--sm"
                                                    :disabled="addingId === row.id"
                                                    title="Add to Cart"
                                                    @click.stop="addLotToCart(row)"
                                                >
                                                    <span class="material-symbols-outlined">shopping_cart</span> Add
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="cm-compare__pagination">
                                <el-pagination
                                    v-model:current-page="tablePage"
                                    :page-size="tablePageSize"
                                    :total="allLots.length"
                                    layout="total, prev, pager, next"
                                    background
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Seller + chart -->
                    <div class="cm-bottom">
                        <div class="cm-card">
                            <h3 class="cm-card__title">Top Rated Seller</h3>
                            <div class="cm-seller">
                                <div class="cm-seller__icon"><span class="material-symbols-outlined">agriculture</span></div>
                                <div>
                                    <h4>{{ seller.name }}</h4>
                                    <p>{{ seller.location }}</p>
                                    <div class="cm-seller__rating"><span class="material-symbols-outlined">star</span>{{ seller.rating }} ({{ seller.reviews }} reviews)</div>
                                </div>
                            </div>
                            <div class="cm-seller__certs">
                                <span v-for="c in seller.certs" :key="c">{{ c }}</span>
                            </div>
                        </div>

                        <div class="cm-card">
                            <h3 class="cm-card__title">Price & Demand Chart</h3>
                            <div class="cm-chart">
                                <div v-for="(bar, i) in priceChartBars" :key="i" class="cm-chart__bar" :class="`cm-chart__bar--${bar.tone}`" :style="{ height: bar.height + '%' }" />
                            </div>
                            <div class="cm-chart__labels">
                                <span v-for="l in priceChartLabels" :key="l">{{ l }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Quick Buy sidebar ────────────────────────────────── -->
                <aside class="cm-sidebar">
                    <QuickBuy v-model="selectedLot" :markets="markets" :exchange-rates="exchangeRates" />

                    <div class="cm-alerts">
                        <h4>Market Alerts</h4>
                        <div class="cm-alerts__row">
                            <span>Price Drop Alerts</span>
                            <el-switch v-model="priceDropAlerts" size="small" />
                        </div>
                        <div class="cm-alerts__row">
                            <span>New Specialty Lots</span>
                            <el-switch v-model="newSpecialtyLots" size="small" />
                        </div>
                    </div>
                </aside>
            </div>

            <!-- ── Floating advisor chatbot ─────────────────────────────── -->
            <div class="cm-fab">
                <button type="button" class="cm-fab__btn" title="Bean Origin Market Advisor">
                    <span class="material-symbols-outlined">smart_toy</span>
                </button>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── App theme tokens — the same flat, hairline-border convention used
   across LiveMarket.vue / MarketIntelligence / HowItWorks (see
   feedback_claude_console_theme + reference_ui_md_design_system
   memories), swapped in for the mockup's own DESIGN.md palette now that
   the content-area feature set is signed off. ───────────────────────── */
.cm-page {
    --green: #000000;
    --green-dark: #262626;
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --on-surface: #121516;
    --on-surface-var: #4B5457;
    --surface-low: #F5F6F7;

    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    color: var(--on-surface);
    background: #fff;
    min-height: 100%;
    margin-top: -48px;
    padding: 28px 0 32px;
    position: relative;
}
.cm-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 18px; line-height: 1; }

/* ── Header ───────────────────────────────────────────────────────────── */
.cm-header { display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px; }
.cm-header__title { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.015em; line-height: 1.3; color: var(--green); margin: 0 0 6px; }
.cm-header__subtitle { font-size: 0.9375rem; font-weight: 400; color: var(--on-surface-var); max-width: 640px; margin: 0; }
.cm-header__actions { display: flex; flex-wrap: wrap; gap: 10px; }
@media (min-width: 900px) {
    .cm-header { flex-direction: row; align-items: flex-end; justify-content: space-between; }
}

.cm-btn { display: inline-flex; align-items: center; gap: 6px; border: none; border-radius: var(--card-radius); font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; padding: 9px 16px; white-space: nowrap; text-decoration: none; transition: background .15s ease, transform .15s ease; }
.cm-btn--tonal { background: var(--surface-low); color: var(--on-surface); }
.cm-btn--tonal:hover { background: #ece4e2; }
.cm-btn--secondary { background: var(--green-dark); color: #fff; }
.cm-btn--secondary:hover { background: var(--green); }
.cm-btn--primary { background: var(--green); color: #fff; }
.cm-btn--primary:hover { background: var(--green-dark); }
.cm-btn--outline { background: #fff; color: var(--on-surface); border: 1px solid var(--card-border); }
.cm-btn--outline:hover { background: var(--surface-low); }
.cm-btn--block { flex: 1; justify-content: center; padding: 8px 10px; font-size: 12px; }
.cm-btn--full { width: 100%; justify-content: center; }
.cm-btn--sm { padding: 6px 12px; font-size: 11.5px; }
.cm-btn--sm .material-symbols-outlined { font-size: 15px; }

.cm-tag { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
.cm-tag--primary-fixed { background: #DCFCE7; color: #166534; }
.cm-tag--secondary-fixed { background: #FEF3C7; color: #92400E; }

/* ── KPI grid ─────────────────────────────────────────────────────────── */
.cm-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 24px; }
.cm-kpi { background: #fff; padding: 14px 16px; border: 1px solid var(--card-border); border-radius: var(--card-radius); }
.cm-kpi__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); margin: 0 0 6px; }
.cm-kpi__row { display: flex; align-items: flex-end; justify-content: space-between; gap: 10px; }
.cm-kpi__value { display: inline-flex; align-items: center; gap: 2px; font-size: 1.25rem; font-weight: 800; color: var(--on-surface); font-variant-numeric: tabular-nums; margin: 0; }
.cm-kpi__value span { font-size: 12px; font-weight: 600; color: var(--on-surface-var); }
.cm-kpi__value--up { color: #16A34A; }
.cm-kpi__value--down { color: #DC2626; }
.cm-kpi__value--up .material-symbols-outlined,
.cm-kpi__value--down .material-symbols-outlined { font-size: 18px; }
.cm-kpi__spark { height: 28px; width: 88px; flex-shrink: 0; border-radius: 3px; background: linear-gradient(to right, rgba(0,0,0,.05), rgba(0,0,0,.18)); }

/* ── Filter bar ───────────────────────────────────────────────────────── */
.cm-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding-bottom: 14px; margin-bottom: 24px; border-bottom: 1px solid var(--card-border); }
.cm-filters__alerts { margin-left: auto; display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); }
.cm-filters__clear { display: inline-flex; align-items: center; gap: 4px; border: none; background: none; font-family: inherit; font-size: 12px; font-weight: 700; color: var(--on-surface-var); cursor: pointer; padding: 8px 4px; }
.cm-filters__clear:hover { color: var(--on-surface); }
.cm-filters__clear .material-symbols-outlined { font-size: 15px; }

/* ── Element Plus field overrides — flat, hairline-border theme matching
   the rest of this page instead of Element Plus's default blue/shadow
   look (see feedback_claude_console_theme memory). ────────────────────── */
.cm-el-select { width: 168px; }
.cm-el-select :deep(.el-select__wrapper) { border-radius: var(--card-radius); box-shadow: 0 0 0 1px var(--card-border) inset; background: #fff; min-height: 34px; font-size: 13px; }
.cm-el-select :deep(.el-select__wrapper.is-focused),
.cm-el-select :deep(.el-select__wrapper.is-hovering) { box-shadow: 0 0 0 1px var(--card-border) inset; }
.cm-el-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 2px var(--green-dark) inset; }
.cm-el-select :deep(.el-select__placeholder) { font-weight: 600; color: var(--on-surface); }

.cm-page :deep(.el-switch.is-checked .el-switch__core) { background: var(--green) !important; border-color: var(--green) !important; }

/* ── Layout ───────────────────────────────────────────────────────────── */
.cm-layout { display: grid; grid-template-columns: 1fr; gap: 24px; align-items: start; }
.cm-layout__main { display: flex; flex-direction: column; gap: 32px; min-width: 0; }
@media (min-width: 1100px) {
    .cm-layout { grid-template-columns: 2fr 1fr; }
}

/* ── Lot grid ─────────────────────────────────────────────────────────── */
.cm-lots { display: grid; grid-template-columns: 1fr; gap: 20px; }
@media (min-width: 700px) { .cm-lots { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 1100px) and (max-width: 1399px) { .cm-lots { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 1400px) { .cm-lots { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
.cm-lots-empty { font-size: 13px; color: var(--on-surface-var); background: var(--surface-low); border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 24px; text-align: center; margin: 0; }

.cm-lot { background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); overflow: hidden; display: flex; flex-direction: column; transition: transform .12s ease; }
.cm-lot:hover { transform: translateY(-2px); }
.cm-lot__media { position: relative; height: 128px; background: var(--surface-low); }
.cm-lot__media img { width: 100%; height: 100%; object-fit: cover; mix-blend-mode: multiply; }
.cm-lot__badges { position: absolute; top: 12px; left: 12px; display: flex; flex-wrap: wrap; gap: 4px; }
.cm-lot__score { position: absolute; bottom: 12px; right: 12px; background: rgba(255,255,255,.92); padding: 4px 8px; border-radius: 6px; font-size: 13px; font-weight: 700; color: var(--on-surface); box-shadow: 0 1px 3px rgba(0,0,0,.12); }

.cm-badge { padding: 4px 8px; font-size: 9px; font-weight: 700; text-transform: uppercase; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
.cm-badge--primary { background: #a0f399; color: #217128; }
.cm-badge--secondary { background: #2e2c2c; color: #d4d4d4; }
.cm-badge--primary-container { background: #a0f399; color: #217128; }
.cm-badge--tertiary-container { background: #2e2c2c; color: #d4d4d4; }
.cm-badge--secondary-fixed { background: #2e2c2c; color: #d4d4d4; }

.cm-lot__body { padding: 16px; display: flex; flex-direction: column; flex: 1; }
.cm-lot__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
.cm-lot__name { font-size: 15px; font-weight: 700; color: var(--on-surface); letter-spacing: -0.005em; line-height: 1.3; margin: 0; }
.cm-lot__name span { font-size: 11px; font-weight: 500; color: var(--on-surface-var); }
.cm-lot__bookmark { color: var(--on-surface-var); cursor: pointer; font-size: 18px !important; flex-shrink: 0; }
.cm-lot__bookmark:hover { color: var(--green); }

.cm-lot__specs { display: grid; grid-template-columns: repeat(2, 1fr); row-gap: 10px; column-gap: 8px; margin: 14px 0; }
.cm-lot__specs p { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); margin: 0 0 2px; }
.cm-lot__specs strong { font-size: 13px; font-weight: 600; color: var(--on-surface); }
.cm-lot__price { font-weight: 800 !important; color: var(--on-surface) !important; }

.cm-lot__cta { margin-top: auto; padding-top: 14px; border-top: 1px solid var(--surface-low); display: flex; gap: 8px; }

/* ── Sections ─────────────────────────────────────────────────────────── */
.cm-section__title { display: flex; align-items: center; gap: 8px; font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); margin: 0 0 18px; }
.cm-section__title .material-symbols-outlined { color: var(--green); }

/* ── Lots table (replaces the old opportunity cards + standalone Lot
   Comparison section — one real, paginated table under "Featured
   Opportunities") ───────────────────────────────────────────────────── */
.cm-compare { background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); overflow: hidden; }
.cm-compare__head { padding: 16px 18px; border-bottom: 1px solid var(--card-border); display: flex; align-items: center; justify-content: space-between; }
.cm-compare__head h3 { font-size: 14px; font-weight: 800; color: var(--on-surface); margin: 0; }
.cm-compare__count { font-size: 11px; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); padding: 3px 10px; border-radius: 999px; }
/* table-layout: fixed + the <colgroup> widths in the template keep every
   column within the card's own width instead of growing to fit its
   widest cell — the fix for the table needing horizontal scroll. */
.cm-compare__wrap { overflow-x: hidden; }
.cm-compare table { width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: 12.5px; }
.cm-compare thead { background: var(--surface-low); }
.cm-compare th { padding: 10px 10px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); white-space: nowrap; }
.cm-compare td { padding: 10px 10px; border-top: 1px solid var(--card-border); color: var(--on-surface); white-space: normal; overflow-wrap: break-word; vertical-align: middle; }
.cm-compare__row { cursor: pointer; }
.cm-compare tbody tr:hover { background: var(--surface-low); }
.cm-compare__strong { font-weight: 700; color: var(--on-surface); }
.cm-compare__muted { color: var(--on-surface-var); font-size: 11.5px; }
.cm-compare__lot { display: flex; flex-direction: column; gap: 2px; }
.cm-compare__action { text-align: right; }
.cm-compare__action .cm-btn--sm { padding: 6px 10px; }
.cm-compare__action .cm-btn--sm .material-symbols-outlined { font-size: 14px; }
.cm-compare .cm-tag { white-space: normal; text-align: center; }

.cm-compare__pagination { padding: 12px 18px; border-top: 1px solid var(--card-border); }
.cm-compare__pagination :deep(.el-pagination) { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; width: 100%; font-family: inherit; justify-content: flex-end; }
.cm-compare__pagination :deep(.el-pagination__total) { margin-right: auto; font-size: 12.5px; font-weight: 600; color: var(--on-surface-var); }
.cm-compare__pagination :deep(.btn-prev),
.cm-compare__pagination :deep(.btn-next) { width: 30px; height: 30px; border-radius: 8px; background: var(--surface-low); border: none; color: var(--on-surface-var); transition: all .15s ease; }
.cm-compare__pagination :deep(.btn-prev:hover:not(:disabled)),
.cm-compare__pagination :deep(.btn-next:hover:not(:disabled)) { color: var(--on-surface); background: #ece4e2; }
.cm-compare__pagination :deep(.el-pager) { display: flex; align-items: center; gap: 4px; }
.cm-compare__pagination :deep(.el-pager li) { min-width: 30px; height: 30px; border-radius: 8px; background: var(--surface-low); border: none; color: var(--on-surface); font-size: 12.5px; font-weight: 600; transition: all .15s ease; }
.cm-compare__pagination :deep(.el-pager li.is-active) { background: var(--green); color: #fff; }

/* ── Bottom: seller + chart ───────────────────────────────────────────── */
.cm-bottom { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 700px) { .cm-bottom { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.cm-card { background: #fff; padding: 18px; border: 1px solid var(--card-border); border-radius: var(--card-radius); }
.cm-card__title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); margin: 0 0 14px; }

.cm-seller { display: flex; gap: 14px; }
.cm-seller__icon { width: 56px; height: 56px; border-radius: var(--card-radius); background: var(--surface-low); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cm-seller__icon .material-symbols-outlined { font-size: 26px; color: var(--green); }
.cm-seller h4 { font-size: 15px; font-weight: 700; color: var(--on-surface); margin: 0; }
.cm-seller p { font-size: 12px; font-weight: 500; color: var(--on-surface-var); margin: 2px 0 0; }
.cm-seller__rating { display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; color: var(--on-surface); margin-top: 8px; }
.cm-seller__rating .material-symbols-outlined { font-size: 15px; color: #eab308; font-variation-settings: 'FILL' 1; }
.cm-seller__certs { display: flex; gap: 8px; margin-top: 14px; }
.cm-seller__certs span { font-size: 10px; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); padding: 4px 8px; border-radius: 6px; }

.cm-chart { height: 88px; width: 100%; display: flex; align-items: flex-end; gap: 4px; padding: 0 6px; }
.cm-chart__bar { flex: 1; border-radius: 3px 3px 0 0; }
.cm-chart__bar--low { background: var(--surface-low); }
.cm-chart__bar--primary { background: var(--green); }
.cm-chart__bar--secondary { background: #D29922; }
.cm-chart__labels { display: flex; justify-content: space-between; margin-top: 8px; padding: 0 6px; font-size: 10px; font-weight: 700; color: var(--on-surface-var); }

/* ── Sidebar (Quick Buy is its own component now — see
   Components/Market/QuickBuy.vue) ────────────────────────────────────── */
.cm-sidebar { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 16px; }

.cm-alerts { background: var(--surface-low); border: 1px solid var(--card-border); padding: 18px; border-radius: var(--card-radius); }
.cm-alerts h4 { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface); margin: 0 0 14px; }
.cm-alerts__row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; }
.cm-alerts__row span { font-size: 13px; font-weight: 600; color: var(--on-surface); }

/* ── Floating advisor chatbot ─────────────────────────────────────────── */
.cm-fab { position: fixed; bottom: 24px; right: 24px; z-index: 40; }
.cm-fab__btn { width: 56px; height: 56px; border-radius: 9999px; background: var(--green); color: #fff; border: none; box-shadow: 0 6px 16px -6px rgba(0,0,0,.5); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: transform .15s ease, background .15s ease; }
.cm-fab__btn:hover { transform: scale(1.06); background: var(--green-dark); }
.cm-fab__btn .material-symbols-outlined { font-size: 26px; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .cm-page { padding: 10px 0 24px; }
    .cm-header__title { font-size: 1.3125rem; }
    .cm-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
</style>
