<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    CircleCheck, Sunny, Pouring, Coin, Aim, Star, DataAnalysis, LocationFilled,
    TopRight, Document, Notebook, Setting, Stamp, Download, Lightning, Box,
    Lock, Van, Message, Right,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';

/* ── Real Market/Lot data, field by field, with dummy fallbacks ported
   from the "Uganda Robusta — Batch #BTH-2026-0048" reference mockup
   wherever the database has nothing to say. `item` is MarketService::show()
   for this listing (real market + lot + farm + blockchain + traceability
   data); `similar` is a handful of other live listings of the same type.
   The layout/markup below is unchanged — only the data feeding it. ─────── */
const props = defineProps({
    item: { type: Object, default: () => ({}) },
    similar: { type: Array, default: () => [] },
});
const item = props.item ?? {};
const specs = item.specs ?? {};
const cupping = item.cupping ?? null;
const primaryFarm = item.farm ?? item.contributing_farms?.[0] ?? null;

const has = (v) => v !== null && v !== undefined && v !== '';
const titleCase = (s) => s.replace(/[_-]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
const fmtNum = (n) => Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 });
const findStage = (label) => item.supply_chain?.find((s) => s.label === label) ?? null;

const batch = {
    status: has(item.status) ? titleCase(item.status) : 'Contract Ready',
    listingId: item.lot_code || (has(item.id) ? `MKT-${item.id}` : 'CP-UGA-88421'),
    title: item.name || 'Uganda Robusta — Batch #BTH-2026-0048',
    price: has(item.price_per_kg) ? Number(item.price_per_kg) : 4.20,
};

const badgeTags = [
    { label: item.origin || 'Uganda', style: 'neutral' },
    { label: item.type || 'Robusta (Fine Canephora)', style: 'umber' },
    ...(item.is_traceable ? [{ label: 'Verified Batch', style: 'primary', icon: CircleCheck }] : []),
    ...(item.status === 'live' ? [{ label: 'Available for Purchase', style: 'primary-container', icon: CircleCheck }] : []),
    { label: has(specs.screen) ? `Screen ${specs.screen}` : 'Screen 18', style: 'high' },
    { label: item.process ? `${item.process} Process` : 'Natural Process', style: 'secondary-container' },
    { label: has(specs.year_of_harvest) ? `${specs.year_of_harvest} Crop` : '2026 Crop', style: 'container' },
];

const specimen = {
    image: item.lot_image || (item.image ? `/storage/${item.image}` : null) || item.lot_images?.[0]?.image_url || item.images?.[0]?.image_url || null,
    overlayLabel: has(specs.screen) ? `Sourced Lot: Screen ${specs.screen} Green` : 'Sourced Lot: Screen 18 Green',
    sampleTag: item.lot_code ? `Lot ${item.lot_code}` : 'Batch Sample #0048',
    stats: [
        { label: 'Bean Size', value: has(specs.screen) ? `Screen ${specs.screen}` : '7.14 mm' },
        { label: 'Moisture', value: has(specs.moisture_content) && specs.moisture_content > 0 ? `${specs.moisture_content}%` : (has(specs.moisture) ? `${specs.moisture}%` : '11.2%'), accent: true },
        { label: 'Crop Year', value: has(specs.year_of_harvest) ? String(specs.year_of_harvest) : '2026' },
    ],
};

const qty = has(item.quantity) ? Number(item.quantity) : null;
const availableQty = has(item.available_quantity) ? Number(item.available_quantity) : null;
const spotlight = {
    priceNote: `${item.currency || 'USD'} / ${item.unit || 'kg'} ${item.delivery_terms ? `(${item.delivery_terms})` : '(FOB Mombasa)'}`,
    fillPct: qty ? Math.min(100, Math.round((availableQty ?? 0) / qty * 1000) / 10) : 62.5,
    fillLabel: qty ? `${Math.round((availableQty ?? 0) / qty * 1000) / 10}% Available` : '62.5% Available',
    specs: [
        { label: 'Available Volume', value: availableQty !== null ? `${fmtNum(availableQty)} kg` : '1,250 kg', sub: qty !== null ? `(of ${fmtNum(qty)} kg)` : '(of 2,000 kg)' },
        { label: 'Minimum Order (MOQ)', value: has(item.minimum_order_quantity) && item.minimum_order_quantity > 0 ? `${fmtNum(item.minimum_order_quantity)} kg` : '100 kg', sub: '(Samples avail)', accent: true },
        { label: 'Origin / Region', value: [item.origin, specs.region].filter(has).join(', ') || 'Uganda, Central Mukono Basin' },
        { label: 'Processing', value: item.process || 'Natural on Raised Beds', accent: true },
        { label: 'Harvest Window', value: item.harvest_season || (has(specs.year_of_harvest) ? `${specs.year_of_harvest} Crop Season` : 'Main Crop Jan–Feb 2026') },
        { label: 'Export Packaging', value: specs.packaging_type || 'GrainPro + Jute (60kg)' },
    ],
};

const aboutText = item.notes || 'A traceable Ugandan Robusta batch sourced from verified smallholders in the Lake Victoria basin, prepared specifically for roasters looking for consistent, premium East African coffee. Naturally dried on elevated African beds under careful shade regulation, this batch delivers exceptional heavy crema, zero astringency, and rich cacao-driven depth ideal for modern espresso blending or elevated single-origin Robusta offerings.';

const specColumns = [
    [
        { label: 'Coffee Type', value: item.type || 'Robusta (Fine Canephora)' },
        { label: 'Harvest Season', value: item.harvest_season || (has(specs.year_of_harvest) ? `${specs.year_of_harvest} Season` : '2026 Season') },
        { label: 'Genetic Variety', value: specs.variety || 'NARO-Kituza KR Clones' },
        { label: 'Processing Method', value: item.process || 'Natural Sun-Dried', accent: true },
    ],
    [
        { label: 'Origin Country', value: item.origin || 'Uganda' },
        { label: 'Official Grade', value: specs.grade || 'Uganda Fine Robusta Grade 1' },
        { label: 'Region & District', value: [specs.region, primaryFarm?.location || primaryFarm?.district].filter(has).join(', ') || 'Central Uganda, Mukono Basin' },
        { label: 'Screen Size', value: has(specs.screen) ? `Screen ${specs.screen}` : 'Screen 18 (7.14mm, 92.4%)', accent: true },
    ],
];

const cupScore = has(item.quality_score) ? Number(item.quality_score).toFixed(2) : '82.50';
const dummyFlavorTags = [
    { label: 'Dark Cocoa Nibs', style: 'plain' },
    { label: 'Molasses', style: 'plain' },
    { label: 'Toasted Hazelnut', style: 'secondary' },
    { label: 'Dried Black Cherry', style: 'plain' },
    { label: 'Brown Sugar Sweetness', style: 'primary' },
    { label: 'Cedar', style: 'plain' },
];
const flavorTags = cupping
    ? [cupping.flavor, cupping.aroma, cupping.body, cupping.acidity, cupping.aftertaste]
        .filter(has)
        .map((label, i) => ({ label, style: ['plain', 'secondary', 'plain', 'primary', 'plain'][i % 5] }))
    : dummyFlavorTags;

/* Numeric per-attribute cupping scores (0–10) have no real column — the
   real `cupping` values above are qualitative descriptors, not scores —
   so these bars stay illustrative. */
const cupAttributes = [
    { label: 'Aroma', score: 7.75 },
    { label: 'Flavor', score: 8.00 },
    { label: 'Body & Crema', score: 8.50 },
    { label: 'Acidity', score: 7.25 },
    { label: 'Finish / Aftertaste', score: 7.75 },
    { label: 'Sweetness', score: 8.00 },
];
const qualityMetrics4 = [
    {
        label: 'Moisture Content',
        value: has(specs.moisture_content) && specs.moisture_content > 0 ? `${specs.moisture_content}%` : (has(specs.moisture) ? `${specs.moisture}%` : '11.2%'),
        sub: 'Optimum 10–12%', accent: true,
    },
    { label: has(specs.screen) ? `Screen ${specs.screen} Retention` : 'Screen 18 Retention', value: '92.4%', sub: '7.14 mm mesh' },
    {
        label: 'Primary Defects',
        value: has(specs.defect_count) ? `${specs.defect_count} / 350g` : '0 / 350g',
        sub: has(specs.defects_percentage) ? `${specs.defects_percentage}% by weight` : 'Export Zero Grade',
        accent: true,
    },
    { label: 'Water Activity', value: '0.54 aw', sub: 'Safe < 0.65 aw' },
];

const originFacts = [
    { label: 'Producer', value: primaryFarm?.name || 'Kato Family Farm (John Kato)' },
    { label: 'Altitude', value: has(specs.altitude) ? `${specs.altitude}m ASL` : '1,220m ASL' },
    { label: 'Country & Region', value: [primaryFarm?.country || item.origin, primaryFarm?.region || specs.region].filter(has).join(', ') || 'Uganda, Central Basin' },
    { label: 'District', value: primaryFarm?.district || primaryFarm?.location || 'Mukono District' },
    { label: 'Soil Composition', value: 'Rich Volcanic Sandy Loam' },
    {
        label: 'GPS Coordinates',
        value: has(primaryFarm?.latitude) && has(primaryFarm?.longitude) ? `${primaryFarm.latitude}° N, ${primaryFarm.longitude}° E` : '0.3542° N, 32.7481° E',
        mono: true, accent: true,
    },
];
const originMap = {
    sector: primaryFarm?.district || primaryFarm?.location || 'Mukono Cadastral Sector 4',
    eudrId: 'UG-MK-94',
};

const harvestEvent = findStage('Harvest');
const processingEvent = findStage('Processing');
const listedEvent = findStage('Listed');
const traceability = [
    {
        title: 'Farm Origin',
        code: item.lot_code || 'FARM-UG-000421',
        detail: primaryFarm ? `Sourced from ${primaryFarm.name}${has(primaryFarm.location) ? ` — ${primaryFarm.location}` : ''}.` : 'Selective hand harvesting of mature red cherries at Kato Family Farm.',
        date: harvestEvent?.date || '04 Jan 2026',
    },
    {
        title: 'Collection & Curing Station',
        code: null,
        detail: specs.drying_method ? `Dried via ${specs.drying_method}${processingEvent ? `, processed ${processingEvent.date}` : ''}.` : 'Mukono Central Station: 2,200 kg intake weighed, brix tested (22.5°), and spread on raised beds.',
        date: processingEvent?.date || '08 Jan 2026',
    },
    {
        title: 'Export Batch Created',
        code: null,
        detail: item.blockchain ? `Committed to ${item.blockchain.network} (Block #${item.blockchain.block_number}).` : 'Hulled, dry-milled, optical color-sorted to Screen 18, and nitrogen-purged GrainPro packing completed.',
        extra: availableQty !== null && qty !== null ? `${fmtNum(availableQty)} ${item.unit || 'kg'} remaining of ${fmtNum(qty)} ${item.unit || 'kg'} total.` : '1,250 kg remaining of 2,000 kg cured intake.',
        date: listedEvent?.date || '28 Jan 2026',
        current: true,
    },
];

const dummySustainIcons = [Sunny, Pouring, Coin, Aim];
const dummySustainability = [
    { icon: Sunny, title: 'Agroforestry Shade Grown', text: 'Intercropped under native Cordia & Albizia canopy trees fostering biodiverse soil microbiology.' },
    { icon: Pouring, title: 'Zero-Water Dry Footprint', text: 'Processed 100% via natural solar drying on raised African beds without municipal or river washing.' },
    { icon: Coin, title: 'Fair Living Wage Premium', text: '+38% premium directly disbursed into farmer mobile wallet over local market baseline floor.' },
    { icon: Aim, title: 'EUDR Deforestation-Free Verified', text: 'Satellite polygon plot verification confirms zero deforestation post-December 2020.' },
];
const sustainability = item.sustainability_practices?.length
    ? item.sustainability_practices.slice(0, 4).map((p, i) => ({
        icon: dummySustainIcons[i % dummySustainIcons.length],
        title: p.name,
        text: p.description || `Recorded sustainability practice at ${primaryFarm?.name || 'the source farm'}.`,
    }))
    : dummySustainability;
const verifiedBadges = (() => {
    const real = [...(item.badges ?? [])];
    if (item.is_traceable) real.push('Batch Traceable');
    return real.length ? real : ['Producer Verified', 'Batch Traceable', 'UCDA Export Certified', 'Rainforest Alliance CoC'];
})();

const inventory = qty !== null && availableQty !== null
    ? { total: qty, available: availableQty, escrow: 0, sold: Math.max(0, qty - availableQty) }
    : { total: 2000, available: 1250, escrow: 250, sold: 500 };

const tradeTerms = [
    { label: 'Unit Spot Price', value: `$${batch.price.toFixed(2)} ${item.currency || 'USD'} / ${item.unit || 'kg'}`, accent: true },
    { label: 'Payment Terms', value: item.payment_terms || '30% Escrow / 70% at BoL' },
    { label: 'Settlement Currency', value: item.currency ? `${item.currency} (${item.currency === 'USD' ? '$' : item.currency})` : 'USD ($) SWIFT or Fedwire' },
    { label: 'Delivery Terms', value: item.delivery_terms || (item.delivery_location ? `Delivery to ${titleCase(item.delivery_location)}` : 'FOB Mombasa Port (CIF Available)') },
    { label: 'Minimum Order Quantity (MOQ)', value: has(item.minimum_order_quantity) && item.minimum_order_quantity > 0 ? `${fmtNum(item.minimum_order_quantity)} kg` : '100 kg' },
    { label: 'Current Physical Storage', value: specs.warehouse || 'Kampala Bonded Depot #4' },
];
const priceValidityNote = 'Price Validity: Fixed rate binding until 30 September 2026 for spot allocations.';

/* No certificate/document records exist in the schema for markets or lots
   — this stays fully illustrative. */
const documents = [
    { icon: Document, name: 'UCDA Certificate of Origin (Form O)', meta: 'Issued by Uganda Coffee Development Authority • PDF • 1.2 MB' },
    { icon: Notebook, name: 'Quality & Cupping Sensory Lab Report', meta: 'Certified Q-Robusta Grader Audit (82.50 pts) • PDF • 840 KB' },
    { icon: Setting, name: 'Processing & Milling Specifications Dossier', meta: 'Screen 18 Sieve Analysis & Defect Grade • PDF • 620 KB' },
    { icon: Stamp, name: 'EUDR Traceability & Deforestation Pass', meta: 'Satellite Polygon Geo-Validation • PDF • 2.4 MB' },
];

const dummyTimeline = [
    { date: '12 Jan 2026', title: 'Farm Registered' },
    { date: '18 Jan 2026', title: 'Coffee Collected' },
    { date: '28 Jan 2026', title: 'Batch Created' },
    { date: '02 Feb 2026', title: 'Lab Cupping 82.5' },
    { date: '05 Feb 2026', title: 'Batch Published' },
    { date: 'Today', title: 'Available Spot', current: true },
];
const timeline = item.supply_chain?.length
    ? [
        ...item.supply_chain.map((s) => ({ date: s.date, title: s.label })),
        { date: 'Today', title: batch.status, current: true },
    ]
    : dummyTimeline;

const seller = {
    initials: item.seller_name ? item.seller_name.split(' ').filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join('') : 'CPU',
    name: item.seller_name ? titleCase(item.seller_name) : 'Coffee Pulse Uganda',
    license: 'Licensed Exporter #EXP-UG-2026-244',
    trades: has(item.seller_active_listings) ? `${item.seller_active_listings} Active Listing${item.seller_active_listings === 1 ? '' : 's'} on Bean Origin` : '34 Completed Trades on Pulse',
    sla: '99.4% SLA',
};

const dummyRelated = [
    { type: 'Arabica', style: 'primary', cup: '86.5 pts', cupAccent: true, name: 'Sipi Falls AA Arabica — Mount Elgon', desc: 'Bugisu Region, Fully Washed, Red Currant & Bergamot Notes. 2,400 kg Available.', price: 5.10 },
    { type: 'Natural Arabica', style: 'secondary', cup: '85.0 pts', cupAccent: true, name: 'Rwenzori Natural Dry-Process Lot', desc: '1,650m ASL, Heavy dried mango, winey body, anaerobic 48h maceration. 1,800 kg Available.', price: 4.85 },
    { type: 'Robusta', style: 'neutral', cup: '80.5 pts', cupAccent: false, name: 'West Nile FAQ Robusta Screen 17', desc: 'Natural Sun-Dried, dense chocolate cream profile, standard blending base. 4,500 kg Available.', price: 3.60 },
];
const relatedStyles = ['primary', 'secondary', 'neutral'];
const related = props.similar?.length
    ? props.similar.slice(0, 3).map((m, i) => ({
        type: m.type || 'Coffee',
        style: relatedStyles[i % relatedStyles.length],
        cup: has(m.quality_score) && m.quality_score > 0 ? `${Number(m.quality_score).toFixed(1)} pts` : '—',
        cupAccent: has(m.quality_score) && m.quality_score > 0,
        name: m.name,
        desc: [m.origin, m.process].filter(has).join(', ') + (has(m.available_quantity) ? `. ${fmtNum(m.available_quantity)} kg Available.` : '.'),
        price: Number(m.price_per_kg ?? 0),
    }))
    : dummyRelated;

/* ── Interactive ordering console — genuinely reactive local state, just
   not tied to a real cart/order yet. ───────────────────────────────────── */
const orderQty = ref(100);
const bags = computed(() => (orderQty.value / 60).toFixed(1));
const orderTotal = computed(() => (orderQty.value * batch.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
function setQty(amount) { orderQty.value = amount; }
function stepQty(delta) {
    const next = orderQty.value + delta;
    if (next >= 100 && next <= 1250) orderQty.value = next;
}
function onQtyInput(e) {
    let val = parseInt(e.target.value, 10) || 100;
    if (val < 100) val = 100;
    if (val > 1250) val = 1250;
    orderQty.value = val;
}

const orderCardRef = ref(null);
function scrollToOrder() {
    orderCardRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<template>
    <MainLayout :title="batch.title">
        <Head :title="batch.title" />

        <div class="mp-page">
            <!-- ── Top context bar: breadcrumb, title, badges, actions ───────────── -->
            <div class="mp-topbar">
                <div class="mp-topbar__crumbs-row">
                    <div class="mp-topbar__meta">
                        <span class="mp-muted">Listing ID: {{ batch.listingId }}</span>
                    </div>
                </div>

                <div class="mp-topbar__title-row">
                    <div>
                        <div class="mp-topbar__title-line">
                            <h1 class="mp-topbar__title">{{ batch.title }}</h1>
                            <button type="button" class="mp-bookmark-btn"><el-icon><Star /></el-icon></button>
                        </div>
                        <div class="mp-tag-row">
                            <span v-for="t in badgeTags" :key="t.label" class="mp-tag" :class="`mp-tag--${t.style}`">
                                <el-icon v-if="t.icon"><component :is="t.icon" /></el-icon>{{ t.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Body: main flow (left) + sticky ordering console (right) ─────── -->
            <div class="mp-body">
                <div class="mp-main">
                    <!-- ── Spotlight: specimen + commercial highlights ───────────────── -->
                    <div class="mp-card mp-spotlight">
                        <div class="mp-spotlight__media">
                            <div class="mp-spotlight__image" :style="specimen.image ? { backgroundImage: `url(${specimen.image})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}">
                                <span class="mp-spotlight__image-tag">{{ specimen.overlayLabel }}</span>
                                <span class="mp-spotlight__image-sample">{{ specimen.sampleTag }}</span>
                            </div>
                            <div class="mp-spotlight__stats">
                                <div v-for="s in specimen.stats" :key="s.label" class="mp-mini-stat">
                                    <span>{{ s.label }}</span>
                                    <strong :class="{ 'mp-accent-text': s.accent }">{{ s.value }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="mp-spotlight__body">
                            <div>
                                <div class="mp-spotlight__price-row">
                                    <div>
                                        <span class="mp-eyebrow">Spot Physical Price</span>
                                        <div class="mp-spotlight__price">${{ batch.price.toFixed(2) }} <span>{{ spotlight.priceNote }}</span></div>
                                    </div>
                                    <div class="mp-spotlight__fill">
                                        <span>Batch Fill</span>
                                        <strong>{{ spotlight.fillLabel }}</strong>
                                    </div>
                                </div>
                                <div class="mp-meter"><div class="mp-meter__fill" :style="{ width: spotlight.fillPct + '%' }"></div></div>
                                <div class="mp-spotlight__specs">
                                    <div v-for="s in spotlight.specs" :key="s.label" class="mp-spotlight__spec">
                                        <span>{{ s.label }}</span>
                                        <strong :class="{ 'mp-accent-text': s.accent }">{{ s.value }} <em v-if="s.sub">{{ s.sub }}</em></strong>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="mp-btn mp-btn--primary mp-btn--block" @click="scrollToOrder">
                                Configure Order &amp; Purchase <el-icon><Right /></el-icon>
                            </button>
                        </div>
                    </div>

                    <!-- ── About this coffee & specs ─────────────────────────────────── -->
                    <div class="mp-card">
                        <div class="mp-card__head">
                            <div>
                                <span class="mp-eyebrow">Agronomic Ledger</span>
                                <h2 class="mp-card__title">About This Coffee &amp; Technical Specifications</h2>
                            </div>
                            <el-icon class="mp-card__head-icon"><DataAnalysis /></el-icon>
                        </div>
                        <p class="mp-body-text">{{ aboutText }}</p>
                        <div class="mp-kv-grid">
                            <div v-for="(col, ci) in specColumns" :key="ci" class="mp-kv-box">
                                <div v-for="row in col" :key="row.label" class="mp-kv-row">
                                    <span>{{ row.label }}</span>
                                    <strong :class="{ 'mp-accent-text': row.accent }">{{ row.value }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Cup & quality profile ─────────────────────────────────────── -->
                    <div class="mp-card">
                        <div class="mp-card__head">
                            <div>
                                <span class="mp-eyebrow">Sensory &amp; Analytical Assay</span>
                                <h2 class="mp-card__title">Cup &amp; Quality Profile</h2>
                            </div>
                            <div class="mp-score-badge">
                                <div>
                                    <span>Fine Robusta Score</span>
                                    <strong>{{ cupScore }}</strong>
                                </div>
                                <div class="mp-score-badge__divider"></div>
                                <span class="mp-score-badge__protocol">CQI / UCDA<br>Protocol</span>
                            </div>
                        </div>
                        <div>
                            <span class="mp-label-row">Key Cupping Notes &amp; Flavor Descriptors:</span>
                            <div class="mp-tag-row mp-tag-row--wrap">
                                <span v-for="f in flavorTags" :key="f.label" class="mp-flavor-tag" :class="`mp-flavor-tag--${f.style}`">{{ f.label }}</span>
                            </div>
                        </div>
                        <div class="mp-attr-grid">
                            <div v-for="a in cupAttributes" :key="a.label" class="mp-attr">
                                <div class="mp-attr__row">
                                    <span>{{ a.label }}</span>
                                    <strong>{{ a.score.toFixed(2) }} / 10</strong>
                                </div>
                                <div class="mp-meter mp-meter--sm"><div class="mp-meter__fill" :style="{ width: (a.score * 10) + '%' }"></div></div>
                            </div>
                        </div>
                        <div class="mp-metric4-grid">
                            <div v-for="m in qualityMetrics4" :key="m.label" class="mp-metric4">
                                <span>{{ m.label }}</span>
                                <strong :class="{ 'mp-accent-text': m.accent }">{{ m.value }}</strong>
                                <em>{{ m.sub }}</em>
                            </div>
                        </div>
                    </div>

                    <!-- ── Origin & farm ──────────────────────────────────────────────── -->
                    <div class="mp-card">
                        <div>
                            <span class="mp-eyebrow">Cadastral Origin</span>
                            <h2 class="mp-card__title">Where This Coffee Comes From</h2>
                        </div>
                        <div class="mp-origin-layout">
                            <div class="mp-origin-facts">
                                <div class="mp-origin-facts__grid">
                                    <div v-for="f in originFacts" :key="f.label" class="mp-origin-fact">
                                        <span>{{ f.label }}</span>
                                        <strong :class="[{ 'mp-accent-text': f.accent }, { 'mp-mono': f.mono }]">{{ f.value }}</strong>
                                    </div>
                                </div>
                                <a href="#" class="mp-inline-link">View Full Farm Profile &amp; Farmer Story <el-icon><TopRight /></el-icon></a>
                            </div>
                            <div class="mp-origin-photo">
                                <div class="mp-origin-photo__image"></div>
                                <div class="mp-origin-photo__foot">
                                    <span><el-icon><LocationFilled /></el-icon>{{ originMap.sector }}</span>
                                    <span class="mp-mono mp-muted">EUDR ID: {{ originMap.eudrId }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Traceability journey ───────────────────────────────────────── -->
                    <div class="mp-card">
                        <div class="mp-card__head">
                            <div>
                                <span class="mp-eyebrow">Provenance Chain</span>
                                <h2 class="mp-card__title">Traceability Journey</h2>
                            </div>
                            <a href="#" class="mp-inline-link">Full Graph <el-icon><Right /></el-icon></a>
                        </div>
                        <div class="mp-timeline">
                            <div v-for="(t, i) in traceability" :key="i" class="mp-timeline__row">
                                <span class="mp-timeline__dot" :class="{ 'mp-timeline__dot--current': t.current }"></span>
                                <div class="mp-timeline__card" :class="{ 'mp-timeline__card--current': t.current }">
                                    <div>
                                        <div class="mp-timeline__title-row">
                                            <span class="mp-timeline__title">{{ t.title }}</span>
                                            <span v-if="t.code" class="mp-timeline__code mp-mono">{{ t.code }}</span>
                                            <span v-if="t.current" class="mp-timeline__current-pill">Current Stage</span>
                                        </div>
                                        <p>{{ t.detail }}</p>
                                        <p v-if="t.extra" class="mp-accent-text mp-timeline__extra">{{ t.extra }}</p>
                                    </div>
                                    <span class="mp-timeline__date">{{ t.date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Sustainability & ESG ───────────────────────────────────────── -->
                    <div class="mp-card">
                        <div>
                            <span class="mp-eyebrow">ESG &amp; Impact Criteria</span>
                            <h2 class="mp-card__title">Sustainability &amp; Environmental Standards</h2>
                        </div>
                        <div class="mp-sustain-grid">
                            <div v-for="s in sustainability" :key="s.title" class="mp-sustain-tile">
                                <el-icon><component :is="s.icon" /></el-icon>
                                <div>
                                    <span>{{ s.title }}</span>
                                    <p>{{ s.text }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mp-tag-row mp-tag-row--wrap">
                            <span v-for="b in verifiedBadges" :key="b" class="mp-check-chip"><el-icon><CircleCheck /></el-icon>{{ b }}</span>
                        </div>
                    </div>

                    <!-- ── Batch availability & inventory ─────────────────────────────── -->
                    <div class="mp-card">
                        <div class="mp-card__head">
                            <div>
                                <span class="mp-eyebrow">Physical Warehouse Ledger</span>
                                <h2 class="mp-card__title">Batch Availability &amp; Inventory Allocation</h2>
                            </div>
                            <span class="mp-mono mp-strong">{{ inventory.total.toLocaleString() }} kg Batch Total</span>
                        </div>
                        <div class="mp-seg-bar">
                            <div class="mp-seg-bar__fill mp-seg-bar__fill--avail" :style="{ width: (inventory.available / inventory.total * 100) + '%' }"></div>
                            <div class="mp-seg-bar__fill mp-seg-bar__fill--escrow" :style="{ width: (inventory.escrow / inventory.total * 100) + '%' }"></div>
                            <div class="mp-seg-bar__fill mp-seg-bar__fill--sold" :style="{ width: (inventory.sold / inventory.total * 100) + '%' }"></div>
                        </div>
                        <div class="mp-legend3-grid">
                            <div class="mp-legend3"><span class="mp-legend-dot mp-legend-dot--avail"></span><div><span>Available Spot Stock</span><strong class="mp-accent-text">{{ inventory.available.toLocaleString() }} kg</strong></div></div>
                            <div class="mp-legend3"><span class="mp-legend-dot mp-legend-dot--escrow"></span><div><span>Reserved Under Escrow</span><strong>{{ inventory.escrow.toLocaleString() }} kg</strong></div></div>
                            <div class="mp-legend3"><span class="mp-legend-dot mp-legend-dot--sold"></span><div><span>Settled &amp; Shipped</span><strong>{{ inventory.sold.toLocaleString() }} kg</strong></div></div>
                        </div>
                    </div>

                    <!-- ── Buying information & trade terms ───────────────────────────── -->
                    <div class="mp-card">
                        <div>
                            <span class="mp-eyebrow">Institutional Conditions</span>
                            <h2 class="mp-card__title">Buying Information &amp; Trade Terms</h2>
                        </div>
                        <div class="mp-terms2-grid">
                            <div v-for="t in tradeTerms" :key="t.label" class="mp-terms2-row">
                                <span>{{ t.label }}</span>
                                <strong :class="{ 'mp-accent-text': t.accent }">{{ t.value }}</strong>
                            </div>
                        </div>
                        <div class="mp-info-strip"><el-icon><Lock /></el-icon><span>{{ priceValidityNote }}</span></div>
                    </div>

                    <!-- ── Official documentation ─────────────────────────────────────── -->
                    <div class="mp-card">
                        <div class="mp-card__head">
                            <div>
                                <span class="mp-eyebrow">Audited Certifications</span>
                                <h2 class="mp-card__title">Official Batch Documentation</h2>
                            </div>
                            <span class="mp-muted">{{ documents.length }} Certified Attachments</span>
                        </div>
                        <div class="mp-doc-list">
                            <div v-for="d in documents" :key="d.name" class="mp-doc-row">
                                <div class="mp-doc-row__icon"><el-icon><component :is="d.icon" /></el-icon></div>
                                <div class="mp-doc-row__text">
                                    <span class="mp-doc-row__name">{{ d.name }}</span>
                                    <span class="mp-muted">{{ d.meta }}</span>
                                </div>
                                <div class="mp-doc-row__actions">
                                    <button type="button" class="mp-btn mp-btn--high mp-btn--sm">View</button>
                                    <button type="button" class="mp-btn mp-btn--outline mp-btn--sm"><el-icon><Download /></el-icon> Download</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Chronological batch timeline ───────────────────────────────── -->
                    <div class="mp-card">
                        <div>
                            <span class="mp-eyebrow">Lifecycle Audit</span>
                            <h2 class="mp-card__title">Batch Timeline</h2>
                        </div>
                        <div class="mp-lifecycle-grid">
                            <div v-for="t in timeline" :key="t.title" class="mp-lifecycle-tile" :class="{ 'mp-lifecycle-tile--current': t.current }">
                                <span>{{ t.date }}</span>
                                <strong>{{ t.title }}</strong>
                                <el-icon><component :is="t.current ? Lightning : CircleCheck" /></el-icon>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Sticky sidebar: ordering console + seller profile ─────────────── -->
                <div class="mp-side">
                    <div ref="orderCardRef" class="mp-card mp-order-card">
                        <div class="mp-order-card__head">
                            <div>
                                <span class="mp-eyebrow">Purchase Allocation</span>
                                <h3 class="mp-card__title">Buy This Batch</h3>
                            </div>
                            <span class="mp-price-chip">${{ batch.price.toFixed(2) }} / kg</span>
                        </div>
                        <div class="mp-info-strip mp-info-strip--muted"><el-icon><Box /></el-icon><span>GrainPro Hermetic Liners + Jute (60kg standard)</span></div>

                        <div class="mp-qty-block">
                            <div class="mp-qty-block__row">
                                <span>Select Order Quantity (kg)</span>
                                <span class="mp-accent-text mp-strong">~{{ bags }} Bags</span>
                            </div>
                            <div class="mp-qty-stepper">
                                <button type="button" class="mp-qty-btn" @click="stepQty(-50)">−</button>
                                <input class="mp-qty-input" type="number" min="100" max="1250" step="50" :value="orderQty" @input="onQtyInput">
                                <button type="button" class="mp-qty-btn" @click="stepQty(50)">+</button>
                            </div>
                            <div class="mp-qty-presets">
                                <button type="button" class="mp-preset-btn" @click="setQty(100)">100 kg</button>
                                <button type="button" class="mp-preset-btn" @click="setQty(300)">300 kg</button>
                                <button type="button" class="mp-preset-btn" @click="setQty(600)">600 kg</button>
                                <button type="button" class="mp-preset-btn mp-preset-btn--accent" @click="setQty(1250)">Max</button>
                            </div>
                        </div>

                        <div class="mp-summary-box">
                            <div class="mp-summary-box__row"><span>Unit Spot Price:</span><span class="mp-mono">${{ batch.price.toFixed(2) }} USD / kg</span></div>
                            <div class="mp-summary-box__row"><span>Allocation Volume:</span><span class="mp-mono">{{ orderQty }} kg</span></div>
                            <div class="mp-summary-box__row"><span>Estimated Packaging:</span><span class="mp-mono">~{{ bags }} bags (60kg)</span></div>
                            <div class="mp-summary-box__total"><span>Total Order Value:</span><strong>${{ orderTotal }} USD</strong></div>
                        </div>

                        <div class="mp-order-card__ctas">
                            <button type="button" class="mp-btn mp-btn--primary mp-btn--block mp-btn--uppercase">
                                <el-icon><CircleCheck /></el-icon> Buy Now (Escrow Secured)
                            </button>
                            <div class="mp-order-card__ctas-row">
                                <button type="button" class="mp-btn mp-btn--high">Make Offer</button>
                                <button type="button" class="mp-btn mp-btn--secondary">Request CIF Quote</button>
                            </div>
                        </div>

                        <div class="mp-trust-row">
                            <span><el-icon><Lock /></el-icon> Escrow Protected</span>
                            <span><el-icon><Van /></el-icon> Global Freight Support</span>
                        </div>
                    </div>

                    <div class="mp-card mp-seller-card">
                        <div class="mp-seller-card__head">
                            <span class="mp-eyebrow">Verified Origin Exporter</span>
                            <span class="mp-sla-pill"><el-icon><CircleCheck /></el-icon>{{ seller.sla }}</span>
                        </div>
                        <div class="mp-seller-card__id">
                            <div class="mp-seller-avatar">{{ seller.initials }}</div>
                            <div>
                                <h4>{{ seller.name }}</h4>
                                <span class="mp-muted">{{ seller.license }}</span>
                                <span class="mp-muted mp-seller-card__trades">{{ seller.trades }}</span>
                            </div>
                        </div>
                        <div class="mp-seller-card__ctas">
                            <button type="button" class="mp-btn mp-btn--high">View Profile</button>
                            <button type="button" class="mp-btn mp-btn--high"><el-icon><Message /></el-icon> Contact Seller</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Related batches ────────────────────────────────────────────────── -->
            <div class="mp-card mp-related">
                <div class="mp-card__head">
                    <div>
                        <span class="mp-eyebrow">Related Offerings</span>
                        <h2 class="mp-card__title mp-related__title">You May Also Like: Available Batches</h2>
                    </div>
                    <a href="#" class="mp-inline-link">Explore All 42 Live Batches <el-icon><Right /></el-icon></a>
                </div>
                <div class="mp-related-grid">
                    <div v-for="r in related" :key="r.name" class="mp-related-tile">
                        <div>
                            <div class="mp-related-tile__top">
                                <span class="mp-tag" :class="`mp-tag--${r.style}`">{{ r.type }}</span>
                                <span class="mp-mono mp-strong" :class="{ 'mp-accent-text': r.cupAccent }">{{ r.cup }}</span>
                            </div>
                            <h3>{{ r.name }}</h3>
                            <p>{{ r.desc }}</p>
                        </div>
                        <div class="mp-related-tile__foot">
                            <span>${{ r.price.toFixed(2) }} <em>/ kg</em></span>
                            <button type="button" class="mp-btn mp-btn--outline mp-btn--sm">Inspect Batch</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
/* ── Ported from the "Uganda Robusta — Batch #BTH-2026-0048" reference
   mockup (code.html) + DESIGN.md, mapped onto the app's persistent --dp-*
   tokens. All content on this page is illustrative sample data — see the
   script's opening comment. The ordering console (quantity stepper,
   presets, running total) is genuinely reactive, just not wired to a
   real cart/order yet. ──────────────────────────────────────────────────── */
.mp-page {
    --card-border: var(--dp-outline-variant);
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
    display: flex;
    flex-direction: column;
    gap: 32px;
    margin-top: -24px;
}

.mp-mono { font-family: var(--dp-font-mono); }
.mp-muted { display: block; color: var(--dp-on-surface-variant); font-size: .75rem; }
.mp-accent-text { color: var(--dp-primary) !important; }
.mp-strong { font-weight: 700; color: var(--dp-on-surface); }
.mp-eyebrow { display: block; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dp-primary); margin-bottom: 3px; }
.mp-body-text { font-size: .875rem; line-height: 1.65; color: var(--dp-on-surface-variant); margin: 0 !important; }
.mp-label-row { display: block; font-size: .75rem; color: var(--dp-on-surface-variant); margin-bottom: 10px; }
.mp-inline-link { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 700; color: var(--dp-primary); text-decoration: none; flex-shrink: 0; }
.mp-inline-link:hover { text-decoration: underline; }
.mp-inline-link :deep(.el-icon) { font-size: 13px; }

/* ── Cards ───────────────────────────────────────────────────────────── */
.mp-card { padding: 28px; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); display: flex; flex-direction: column; gap: 20px; }
.mp-card__head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 14px; }
.mp-card__title { font-size: 1.125rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); margin: 0 !important; }
.mp-card__head-icon { color: var(--dp-outline); font-size: 22px; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.mp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 40px; padding: 0 16px; border-radius: 8px; font-size: .75rem; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; font-family: inherit; transition: opacity .15s ease, background .15s ease; white-space: nowrap; }
.mp-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.mp-btn--primary:hover { opacity: .9; }
.mp-btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.mp-btn--secondary:hover { opacity: .9; }
.mp-btn--high { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.mp-btn--high:hover { background: var(--dp-surface-container-highest); }
.mp-btn--outline { background: var(--dp-surface-container-lowest); border-color: var(--card-border); color: var(--dp-primary); }
.mp-btn--outline:hover { background: var(--dp-surface-container); }
.mp-btn--block { width: 100%; }
.mp-btn--sm { height: 32px; padding: 0 12px; font-size: .6875rem; }
.mp-btn--uppercase { text-transform: uppercase; letter-spacing: .04em; }

/* ── Top bar ─────────────────────────────────────────────────────────── */
.mp-topbar { padding: 26px 30px; background: var(--dp-surface-container-lowest); border: none; border-bottom: 1px solid var(--card-border); border-radius: 0; display: flex; flex-direction: column; gap: 18px; }
.mp-topbar__crumbs-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
.mp-topbar__meta { display: flex; align-items: center; gap: 12px; }
.mp-topbar__title-row { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 18px; }
.mp-topbar__title-line { display: flex; align-items: center; gap: 8px; }
.mp-topbar__title { font-size: 1.625rem; font-weight: 800; letter-spacing: -.015em; color: var(--dp-on-surface); margin: 0 !important; }
.mp-bookmark-btn { width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: var(--dp-on-surface-variant); cursor: pointer; display: flex; align-items: center; justify-content: center; }
.mp-bookmark-btn:hover { background: var(--dp-surface-container-low); color: var(--dp-primary); }

.mp-tag-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 12px; }
.mp-tag-row--wrap { margin-top: 0; }
.mp-tag { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 8px; font-size: .75rem; font-weight: 600; }
.mp-tag :deep(.el-icon) { font-size: 13px; }
.mp-tag--neutral { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.mp-tag--umber { background: var(--dp-surface-container-low); color: var(--dp-secondary); }
.mp-tag--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.mp-tag--primary-container { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.mp-tag--high { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.mp-tag--secondary-container { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.mp-tag--container { background: var(--dp-surface-container); color: var(--dp-on-surface-variant); }
.mp-tag--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }

/* ── Body layout ─────────────────────────────────────────────────────── */
.mp-body { display: flex; align-items: flex-start; gap: 28px; }
.mp-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 28px; }
.mp-side { width: 360px; flex-shrink: 0; display: flex; flex-direction: column; gap: 20px; }

/* ── Spotlight ───────────────────────────────────────────────────────── */
.mp-spotlight { flex-direction: row; gap: 24px; }
.mp-spotlight__media { width: 42%; flex-shrink: 0; display: flex; flex-direction: column; gap: 10px; }
.mp-spotlight__image { position: relative; width: 100%; height: 230px; border-radius: 12px; overflow: hidden; background:
    radial-gradient(circle at 20% 25%, color-mix(in srgb, var(--dp-primary) 45%, #d9c79a) 0 3px, transparent 4px) 0 0 / 22px 22px,
    linear-gradient(150deg, color-mix(in srgb, var(--dp-primary) 25%, #cdbb8c), color-mix(in srgb, var(--dp-primary) 55%, #b7a877)); }
.mp-spotlight__image-tag { position: absolute; top: 12px; left: 12px; padding: 5px 10px; border-radius: 7px; background: color-mix(in srgb, var(--dp-surface-container-lowest) 90%, transparent); backdrop-filter: blur(8px); font-size: .6875rem; font-weight: 700; color: var(--dp-primary); }
.mp-spotlight__image-sample { position: absolute; bottom: 12px; right: 12px; padding: 5px 9px; border-radius: 7px; background: color-mix(in srgb, #1c1f21 82%, transparent); color: #fff; font-size: .625rem; backdrop-filter: blur(8px); }
.mp-spotlight__stats { display: flex; gap: 8px; }
.mp-mini-stat { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 8px 4px; border-radius: 8px; background: var(--dp-surface-container-low); text-align: center; }
.mp-mini-stat span { font-size: .5625rem; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); }
.mp-mini-stat strong { font-size: .75rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-spotlight__body { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; gap: 20px; }
.mp-spotlight__price-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding-bottom: 14px; }
.mp-spotlight__price { display: flex; align-items: baseline; gap: 6px; font-size: 1.75rem; font-weight: 800; color: var(--dp-primary); margin-top: 4px; }
.mp-spotlight__price span { font-size: .6875rem; font-weight: 500; color: var(--dp-on-surface-variant); }
.mp-spotlight__fill { text-align: right; display: flex; flex-direction: column; gap: 2px; }
.mp-spotlight__fill span { font-size: .6875rem; color: var(--dp-on-surface-variant); }
.mp-spotlight__fill strong { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-meter { width: 100%; height: 6px; border-radius: 999px; background: var(--dp-surface-container-high); overflow: hidden; margin-bottom: 18px; }
.mp-meter--sm { margin-bottom: 0; height: 5px; }
.mp-meter__fill { height: 100%; background: var(--dp-primary); border-radius: 999px; }
.mp-spotlight__specs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
.mp-spotlight__spec { display: flex; flex-direction: column; gap: 3px; font-size: .75rem; }
.mp-spotlight__spec span { color: var(--dp-on-surface-variant); font-size: .6875rem; }
.mp-spotlight__spec strong { font-weight: 700; color: var(--dp-on-surface); }
.mp-spotlight__spec strong em { font-style: normal; font-weight: 400; font-size: .6875rem; color: var(--dp-on-surface-variant); }

/* ── About / key-value boxes ─────────────────────────────────────────── */
.mp-kv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.mp-kv-box { padding: 14px 16px; border-radius: 10px; background: var(--dp-surface-container-low); display: flex; flex-direction: column; gap: 11px; }
.mp-kv-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: .75rem; }
.mp-kv-row span { color: var(--dp-on-surface-variant); }
.mp-kv-row strong { font-weight: 700; color: var(--dp-on-surface); text-align: right; }

/* ── Cup score & flavor ──────────────────────────────────────────────── */
.mp-score-badge { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 12px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); flex-shrink: 0; }
.mp-score-badge > div:first-child { display: flex; flex-direction: column; }
.mp-score-badge > div:first-child span { font-size: .5625rem; text-transform: uppercase; letter-spacing: .04em; font-weight: 800; opacity: .75; }
.mp-score-badge strong { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
.mp-score-badge__divider { width: 1px; height: 32px; background: color-mix(in srgb, var(--dp-on-primary-fixed) 20%, transparent); }
.mp-score-badge__protocol { font-size: .625rem; line-height: 1.3; }
.mp-flavor-tag { padding: 6px 12px; border-radius: 8px; font-size: .75rem; font-weight: 600; }
.mp-flavor-tag--plain { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.mp-flavor-tag--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.mp-flavor-tag--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }

.mp-attr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 28px; }
.mp-attr__row { display: flex; align-items: baseline; justify-content: space-between; font-size: .75rem; margin-bottom: 6px; }
.mp-attr__row span { color: var(--dp-on-surface); font-weight: 500; }
.mp-attr__row strong { color: var(--dp-primary); font-weight: 700; }

.mp-metric4-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.mp-metric4 { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 14px 8px; border-radius: 10px; background: var(--dp-surface-container-low); text-align: center; }
.mp-metric4 span { font-size: .625rem; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); }
.mp-metric4 strong { font-size: 1rem; font-weight: 800; color: var(--dp-on-surface); }
.mp-metric4 em { font-style: normal; font-size: .625rem; color: var(--dp-on-surface-variant); }

/* ── Origin ──────────────────────────────────────────────────────────── */
.mp-origin-layout { display: grid; grid-template-columns: 7fr 5fr; gap: 24px; align-items: center; }
.mp-origin-facts { display: flex; flex-direction: column; gap: 14px; }
.mp-origin-facts__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mp-origin-fact { display: flex; flex-direction: column; gap: 3px; font-size: .75rem; }
.mp-origin-fact span { color: var(--dp-on-surface-variant); font-size: .6875rem; }
.mp-origin-fact strong { font-weight: 700; color: var(--dp-on-surface); }
.mp-origin-photo { border-radius: 12px; overflow: hidden; background: var(--dp-surface-container); }
.mp-origin-photo__image { width: 100%; height: 176px; background:
    linear-gradient(0deg, color-mix(in srgb, var(--dp-primary) 12%, transparent) 1px, transparent 1px) 0 0 / 100% 22px,
    linear-gradient(90deg, color-mix(in srgb, var(--dp-primary) 12%, transparent) 1px, transparent 1px) 0 0 / 22px 100%,
    var(--dp-surface-container); }
.mp-origin-photo__foot { padding: 12px; background: var(--dp-surface-container-lowest); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.mp-origin-photo__foot > span:first-child { display: inline-flex; align-items: center; gap: 6px; font-size: .75rem; font-weight: 600; color: var(--dp-on-surface); }
.mp-origin-photo__foot :deep(.el-icon) { color: var(--dp-primary); }

/* ── Traceability timeline ───────────────────────────────────────────── */
.mp-timeline { position: relative; display: flex; flex-direction: column; gap: 18px; padding-left: 22px; }
.mp-timeline::before { content: ''; position: absolute; left: 5px; top: 8px; bottom: 8px; width: 2px; background: var(--dp-surface-container-high); }
.mp-timeline__row { position: relative; }
.mp-timeline__dot { position: absolute; left: -22px; top: 14px; width: 11px; height: 11px; border-radius: 50%; background: var(--dp-outline-variant); border: 2px solid var(--dp-surface-container-lowest); }
.mp-timeline__dot--current { background: var(--dp-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--dp-primary) 25%, transparent); }
.mp-timeline__card { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px; border-radius: 10px; background: var(--dp-surface-container-low); flex-wrap: wrap; }
.mp-timeline__card--current { background: color-mix(in srgb, var(--dp-primary-fixed) 45%, var(--dp-surface-container-lowest)); }
.mp-timeline__title-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.mp-timeline__title { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-timeline__code { padding: 1px 6px; border-radius: 5px; background: var(--dp-surface-container-highest); color: var(--dp-on-surface-variant); font-size: .625rem; }
.mp-timeline__current-pill { padding: 2px 8px; border-radius: 999px; background: var(--dp-primary); color: var(--dp-on-primary); font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
.mp-timeline__card p { font-size: .75rem; color: var(--dp-on-surface-variant); margin: 3px 0 0 !important; }
.mp-timeline__extra { font-weight: 700; }
.mp-timeline__date { font-size: .6875rem; color: var(--dp-on-surface-variant); font-weight: 600; flex-shrink: 0; }

/* ── Sustainability ──────────────────────────────────────────────────── */
.mp-sustain-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.mp-sustain-tile { display: flex; align-items: flex-start; gap: 10px; padding: 14px; border-radius: 10px; background: var(--dp-surface-container-low); }
.mp-sustain-tile :deep(.el-icon) { color: var(--dp-primary); font-size: 20px; flex-shrink: 0; margin-top: 1px; }
.mp-sustain-tile span { display: block; font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-sustain-tile p { font-size: .75rem; color: var(--dp-on-surface-variant); margin: 3px 0 0 !important; line-height: 1.45; }
.mp-check-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; background: var(--dp-surface-container); color: var(--dp-on-surface); font-size: .75rem; font-weight: 600; }
.mp-check-chip :deep(.el-icon) { color: var(--dp-primary); }

/* ── Inventory ───────────────────────────────────────────────────────── */
.mp-seg-bar { display: flex; width: 100%; height: 14px; border-radius: 8px; background: var(--dp-surface-container-high); padding: 3px; gap: 3px; overflow: hidden; }
.mp-seg-bar__fill { height: 100%; border-radius: 4px; }
.mp-seg-bar__fill--avail { background: var(--dp-primary); }
.mp-seg-bar__fill--escrow { background: var(--dp-secondary); }
.mp-seg-bar__fill--sold { background: var(--dp-outline-variant); }
.mp-legend3-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.mp-legend3 { display: flex; align-items: center; gap: 10px; padding: 12px; border-radius: 10px; background: var(--dp-surface-container-low); }
.mp-legend-dot { width: 11px; height: 11px; border-radius: 50%; flex-shrink: 0; }
.mp-legend-dot--avail { background: var(--dp-primary); }
.mp-legend-dot--escrow { background: var(--dp-secondary); }
.mp-legend-dot--sold { background: var(--dp-outline-variant); }
.mp-legend3 span { display: block; font-size: .6875rem; color: var(--dp-on-surface-variant); }
.mp-legend3 strong { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }

/* ── Trade terms ─────────────────────────────────────────────────────── */
.mp-terms2-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.mp-terms2-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-radius: 10px; background: var(--dp-surface-container-low); font-size: .75rem; }
.mp-terms2-row span { color: var(--dp-on-surface-variant); }
.mp-terms2-row strong { font-weight: 700; color: var(--dp-on-surface); text-align: right; }
.mp-info-strip { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 10px; background: var(--dp-surface-container); font-size: .75rem; color: var(--dp-on-surface-variant); }
.mp-info-strip :deep(.el-icon) { color: var(--dp-outline); flex-shrink: 0; }
.mp-info-strip--muted { background: var(--dp-surface-container-low); }

/* ── Documents ───────────────────────────────────────────────────────── */
.mp-doc-list { display: flex; flex-direction: column; gap: 10px; }
.mp-doc-row { display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 10px; background: var(--dp-surface-container-low); }
.mp-doc-row__icon { width: 36px; height: 36px; border-radius: 8px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mp-doc-row__text { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.mp-doc-row__name { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-doc-row__actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

/* ── Lifecycle timeline ──────────────────────────────────────────────── */
.mp-lifecycle-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; }
.mp-lifecycle-tile { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 12px 8px; border-radius: 10px; background: var(--dp-surface-container-low); text-align: center; }
.mp-lifecycle-tile--current { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.mp-lifecycle-tile span { font-size: .625rem; color: var(--dp-on-surface-variant); }
.mp-lifecycle-tile--current span { color: var(--dp-on-primary-fixed); text-transform: uppercase; font-weight: 700; letter-spacing: .03em; }
.mp-lifecycle-tile strong { font-size: .75rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-lifecycle-tile--current strong { color: var(--dp-on-primary-fixed); }
.mp-lifecycle-tile :deep(.el-icon) { color: var(--dp-primary); font-size: 16px; }
.mp-lifecycle-tile--current :deep(.el-icon) { color: var(--dp-on-primary-fixed); }

/* ── Sidebar: ordering console ───────────────────────────────────────── */
.mp-order-card { gap: 18px; }
.mp-order-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.mp-price-chip { padding: 5px 10px; border-radius: 7px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); font-family: var(--dp-font-mono); font-size: .75rem; font-weight: 700; flex-shrink: 0; }
.mp-qty-block { display: flex; flex-direction: column; gap: 8px; }
.mp-qty-block__row { display: flex; align-items: center; justify-content: space-between; font-size: .75rem; color: var(--dp-on-surface-variant); }
.mp-qty-stepper { display: flex; align-items: center; gap: 8px; }
.mp-qty-btn { width: 38px; height: 38px; border-radius: 8px; border: none; background: var(--dp-surface-container-high); color: var(--dp-on-surface); font-size: 1.125rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.mp-qty-btn:hover { background: var(--dp-surface-container-highest); }
.mp-qty-input { flex: 1; height: 38px; text-align: center; font-family: var(--dp-font-sans); font-weight: 700; font-size: .8125rem; background: var(--dp-surface-container-low); border: none; border-radius: 8px; color: var(--dp-on-surface); }
.mp-qty-input:focus { outline: 2px solid var(--dp-primary); outline-offset: -2px; }
.mp-qty-presets { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
.mp-preset-btn { padding: 6px 4px; border-radius: 6px; border: none; background: var(--dp-surface-container); color: var(--dp-on-surface); font-size: .625rem; font-weight: 700; cursor: pointer; font-family: inherit; }
.mp-preset-btn:hover { background: var(--dp-surface-container-high); }
.mp-preset-btn--accent { color: var(--dp-primary); }
.mp-summary-box { padding: 14px; border-radius: 10px; background: var(--dp-surface-container); display: flex; flex-direction: column; gap: 8px; }
.mp-summary-box__row { display: flex; align-items: center; justify-content: space-between; font-size: .75rem; color: var(--dp-on-surface-variant); }
.mp-summary-box__row span:last-child { color: var(--dp-on-surface); }
.mp-summary-box__total { display: flex; align-items: baseline; justify-content: space-between; padding-top: 8px; border-top: 1px solid var(--card-border); }
.mp-summary-box__total span { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-summary-box__total strong { font-size: 1.25rem; font-weight: 800; color: var(--dp-primary); }
.mp-order-card__ctas { display: flex; flex-direction: column; gap: 8px; }
.mp-order-card__ctas-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.mp-trust-row { display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid var(--card-border); font-size: .6875rem; color: var(--dp-on-surface-variant); }
.mp-trust-row span { display: inline-flex; align-items: center; gap: 5px; }
.mp-trust-row :deep(.el-icon) { color: var(--dp-primary); font-size: 14px; }

/* ── Sidebar: seller card ────────────────────────────────────────────── */
.mp-seller-card { gap: 14px; }
.mp-seller-card__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.mp-sla-pill { display: inline-flex; align-items: center; gap: 4px; font-size: .6875rem; font-weight: 700; color: var(--dp-primary); }
.mp-seller-card__id { display: flex; align-items: center; gap: 12px; }
.mp-seller-avatar { width: 46px; height: 46px; border-radius: 10px; background: var(--dp-primary); color: var(--dp-on-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .8125rem; flex-shrink: 0; }
.mp-seller-card__id h4 { font-size: .875rem; font-weight: 800; color: var(--dp-on-surface); margin: 0 0 2px !important; }
.mp-seller-card__trades { margin-top: 1px; }
.mp-seller-card__ctas { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

/* ── Related ─────────────────────────────────────────────────────────── */
.mp-related { margin-top: 8px; }
.mp-related__title { font-size: 1.25rem; }
.mp-related-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.mp-related-tile { display: flex; flex-direction: column; justify-content: space-between; gap: 14px; padding: 18px; border-radius: 12px; background: var(--dp-surface-container-low); }
.mp-related-tile__top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
.mp-related-tile h3 { font-size: .875rem; font-weight: 700; color: var(--dp-on-surface); margin: 0 !important; }
.mp-related-tile p { font-size: .75rem; color: var(--dp-on-surface-variant); margin: 6px 0 0 !important; line-height: 1.5; }
.mp-related-tile__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.mp-related-tile__foot > span { font-size: .9375rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-related-tile__foot > span em { font-style: normal; font-size: .625rem; font-weight: 400; color: var(--dp-on-surface-variant); }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1200px) {
    .mp-side { width: 320px; }
}
@media (max-width: 1024px) {
    .mp-body { flex-direction: column; }
    .mp-side { width: 100%; }
    .mp-spotlight { flex-direction: column; }
    .mp-spotlight__media { width: 100%; }
    .mp-related-grid { grid-template-columns: 1fr 1fr; }
    .mp-lifecycle-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 720px) {
    .mp-topbar__title-row { flex-direction: column; align-items: stretch; }
    .mp-kv-grid, .mp-attr-grid, .mp-metric4-grid, .mp-origin-layout, .mp-origin-facts__grid, .mp-sustain-grid, .mp-legend3-grid, .mp-terms2-grid, .mp-related-grid { grid-template-columns: 1fr; }
    .mp-lifecycle-grid { grid-template-columns: 1fr 1fr; }
    .mp-spotlight__specs { grid-template-columns: 1fr; }
    .mp-order-card__ctas-row, .mp-seller-card__ctas { grid-template-columns: 1fr; }
    .mp-qty-presets { grid-template-columns: 1fr 1fr; }
}
</style>
