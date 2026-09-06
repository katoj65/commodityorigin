<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    LocationFilled, ArrowLeft,
    Grape, Pouring, Connection, House,
    Star, Setting, Collection, MapLocation, ChatLineSquare,
    Grid, Sunny, MostlyCloudy, Lightning, Refresh, RefreshRight, MagicStick, Trophy,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ImageViewer from '@/Components/ImageViewer.vue';
import AddToCartForm from '@/Components/AddToCartForm.vue';

const props = defineProps({
    item: { type: Object, default: () => ({}) },
});

/* ── Fallback showcase content — ported 1:1 from the original "Sumatra
   Mandheling" reference mockup (code.html). Every section below now binds
   to the real `item` payload first; a field only falls back to one of
   these values when the payload genuinely has nothing for it (no
   sustainability-programs or trader-note data model exists yet, so those
   two stay fully hard-coded). The quantity stepper is local-only and
   "Add to Cart" is presentational until a real listing is wired up. ───── */
const FALLBACK = {
    origin: 'Indonesia',
    name: 'Sumatra Mandheling',
    description: 'Full-bodied, low-acidity profile with intense earthy and herbaceous notes.',
    price: 24.00,
    unit: '250g bag',
    badge: 'Single Origin',
    scaScore: '88.0',
    screen: '16/18',
    process: 'Giling Basah',
    varietals: 'Typica, Catimor',
    originStats: {
        origin: 'Indonesia',
        region: 'North Sumatra',
        altitude: '1,400 - 1,600m',
        moisture: '11.5%',
        packagingType: 'Grain Pro Bags',
        harvestYear: '2024',
        defectsPercentage: '2.0%',
    },
    sensory: {
        acidity: 'Low',
        body: 'Full',
        flavor: 'Dark Chocolate',
        aroma: 'Earthy',
        balance: '7.5 / 10',
        aftertaste: 'Herbaceous',
    },
    traderNote: 'High demand lot. Consistent cup quality across previous 3 harvests. Recommended for specialty espresso programs.',
    sourceFarms: [
        { name: 'Lintong Nihuta Cooperative', size: '12.4 ha', location: 'Lintong Nihuta, Sumatra, Indonesia' },
    ],
    mapLabel: 'Mount Leuser, Sumatra',
    // Real public coordinates for Mount Leuser National Park, Sumatra (~3.70°N, 97.15°E).
    mapEmbedSrc: 'https://www.openstreetmap.org/export/embed.html?bbox=96.95%2C3.50%2C97.35%2C3.90&layer=mapnik&marker=3.70%2C97.15',
    supplyChain: [
        { label: 'Harvest', date: 'Oct 12' },
        { label: 'Processing', date: 'Oct 15' },
        { label: 'Export', date: 'Nov 02' },
    ],
};

const PLACEHOLDER_IMAGE = '/images/coffee_image.jpg';

const product = computed(() => ({
    origin: props.item.origin || FALLBACK.origin,
    name: props.item.name || FALLBACK.name,
    description: props.item.notes || FALLBACK.description,
    price: props.item.price_per_kg ?? FALLBACK.price,
    unit: props.item.unit || FALLBACK.unit,
}));

const heroBadge = computed(() => (props.item.badges && props.item.badges[0]) || FALLBACK.badge);

const stats = computed(() => [
    {
        label: 'SCA Score',
        value: props.item.quality_score != null ? Number(props.item.quality_score).toFixed(1) : FALLBACK.scaScore,
        accent: true,
        icon: Star,
    },
    {
        label: 'Screen',
        value: props.item.specs?.screen || FALLBACK.screen,
        icon: Grid,
    },
    { label: 'Process', value: props.item.process || FALLBACK.process, icon: Setting },
    { label: 'Variety', value: props.item.specs?.variety || FALLBACK.varietals, icon: Collection },
]);

const SENSORY_FIELDS = [
    { key: 'acidity', label: 'Acidity' },
    { key: 'body', label: 'Body' },
    { key: 'flavor', label: 'Flavour' },
    { key: 'aroma', label: 'Aroma' },
    { key: 'balance', label: 'Balance' },
    { key: 'aftertaste', label: 'Aftertaste' },
];

const sensoryAttributes = computed(() => {
    const cupping = props.item.cupping || {};
    return SENSORY_FIELDS.map(({ key, label }) => {
        const raw = cupping[key];
        let value = FALLBACK.sensory[key];
        if (raw != null && raw !== '') {
            value = key === 'balance' ? `${Number(raw).toFixed(1)} / 10` : raw;
        }
        return { label, value };
    });
});

const traderNote = computed(() => {
    if (!props.item.demand) return FALLBACK.traderNote;
    const listings = props.item.seller_active_listings;
    const listingsClause = listings
        ? ` The seller currently has ${listings} other active listing${listings === 1 ? '' : 's'}.`
        : '';
    return `This lot is in ${props.item.demand} demand.${listingsClause}`;
});

const origin = computed(() => {
    const specs = props.item.specs || {};
    const stats = FALLBACK.originStats;

    return {
        stats: [
            { label: 'Origin', value: specs.origin || stats.origin },
            { label: 'Region', value: specs.region || stats.region },
            { label: 'Altitude', value: specs.altitude != null && specs.altitude !== '' ? `${specs.altitude}m` : stats.altitude },
            { label: 'Moisture', value: specs.moisture != null ? `${Number(specs.moisture).toFixed(1)}%` : stats.moisture },
            { label: 'Package Type', value: specs.packaging_type || stats.packagingType },
            { label: 'Harvest Year', value: specs.year_of_harvest || stats.harvestYear },
            { label: 'Defect %', value: specs.defects_percentage != null ? `${Number(specs.defects_percentage).toFixed(1)}%` : stats.defectsPercentage },
        ],
    };
});

const sourceFarms = computed(() => {
    const farms = props.item.contributing_farms;
    if (farms?.length) {
        return farms.map((farm) => ({
            name: farm.name,
            size: farm.size_ha != null ? `${Number(farm.size_ha).toFixed(1)} ha` : null,
            location: farm.location || null,
        }));
    }
    return FALLBACK.sourceFarms;
});

const mapLocationLabel = computed(() => {
    const farm = props.item.farm;
    const label = farm ? [farm.district, farm.region, farm.country].filter(Boolean).join(', ') : '';
    return label || FALLBACK.mapLabel;
});

const mapEmbedSrc = computed(() => {
    const farm = props.item.farm;
    if (!farm?.latitude || !farm?.longitude) return FALLBACK.mapEmbedSrc;
    const lat = Number(farm.latitude);
    const lng = Number(farm.longitude);
    const d = 0.2;
    return `https://www.openstreetmap.org/export/embed.html?bbox=${(lng - d).toFixed(2)}%2C${(lat - d).toFixed(2)}%2C${(lng + d).toFixed(2)}%2C${(lat + d).toFixed(2)}&layer=mapnik&marker=${lat.toFixed(2)}%2C${lng.toFixed(2)}`;
});

/* ── Farm sustainability practices — the first column of the Sustainability
   & Traceability grid. Each slug resolves to a matching icon, falling back
   to a generic plant icon for any slug without a dedicated icon (e.g. a
   newly added practice). ──────────────────────────────────────────────── */
const PRACTICE_ICONS = {
    intercropping: Grid,
    organic_composting: RefreshRight,
    shade_grown: MostlyCloudy,
    water_efficient_irrigation: Pouring,
    agroforestry: Sunny,
    soil_conservation: Collection,
    integrated_pest_management: MagicStick,
    renewable_energy: Lightning,
    waste_reduction_recycling: Refresh,
    biodiversity_conservation: Trophy,
};

const sustainabilityPractices = computed(() =>
    (props.item.sustainability_practices || []).map((practice) => ({
        ...practice,
        icon: PRACTICE_ICONS[practice.slug] || Grape,
    })),
);

const supplyChainSteps = computed(() => (props.item.supply_chain?.length ? props.item.supply_chain : FALLBACK.supplyChain));

/* ── Images: the main photo and the 3 thumbnails come from the lot's own
   photos (its cover image, then its uploaded gallery) — falling back to
   the listing's own image/gallery, then to the static placeholder only
   when a slot has no real photo at all. ─────────────────────────────── */
const hasRealMainImage = computed(() => Boolean(props.item.lot_image || props.item.image));
const mainImageUrl = computed(() => props.item.lot_image || (props.item.image ? `/storage/${props.item.image}` : null) || PLACEHOLDER_IMAGE);

const galleryImageUrls = computed(() => {
    if (props.item.lot_images?.length) return props.item.lot_images.map((img) => img.image_url);
    if (props.item.images?.length) return props.item.images.map((img) => img.image_url);
    return [];
});
const galleryThumbs = computed(() => {
    const thumbs = [...galleryImageUrls.value];
    while (thumbs.length < 3) thumbs.push(null);
    return thumbs.slice(0, 3);
});

/* ── Image viewer — main photo + gallery form one browsable sequence of
   real photos only (placeholder tiles aren't worth zooming into). ────── */
const viewerOpen = ref(false);
const viewerIndex = ref(0);
const viewerImages = computed(() => {
    const list = [];
    if (hasRealMainImage.value) list.push({ url: mainImageUrl.value, alt: product.value.name });
    for (const url of galleryImageUrls.value) list.push({ url, alt: product.value.name });
    return list;
});

function openViewer(index) {
    if (!viewerImages.value.length) return;
    viewerIndex.value = Math.min(index, viewerImages.value.length - 1);
    viewerOpen.value = true;
}

function openGalleryThumb(index) {
    if (!galleryThumbs.value[index]) return;
    openViewer((hasRealMainImage.value ? 1 : 0) + index);
}

const cartMaxQuantity = computed(() => {
    const available = props.item.available_quantity;
    return available != null && available > 0 ? Math.floor(available) : null;
});
</script>

<template>
    <DesignPreviewLayout :title="product.name">
        <Head :title="product.name" />

        <div class="mp-page">

            <!-- ── Hero — image gallery / specs / purchase widget ─────────── -->
            <section class="mp-hero">
                <div class="mp-hero__gallery">
                    <div class="mp-hero__main-img" :class="{ 'mp-hero__main-img--clickable': hasRealMainImage }" @click="hasRealMainImage && openViewer(0)">
                        <img :src="mainImageUrl" :alt="product.name">
                        <span class="mp-hero__badge">{{ heroBadge }}</span>
                    </div>
                    <div class="mp-hero__thumbs">
                        <div
                            v-for="(thumb, i) in galleryThumbs"
                            :key="i"
                            class="mp-hero__thumb"
                            :class="{ 'mp-hero__thumb--clickable': thumb }"
                            @click="openGalleryThumb(i)"
                        >
                            <img :src="thumb || PLACEHOLDER_IMAGE" :alt="product.name">
                        </div>
                    </div>
                </div>

                <div class="mp-hero__specs">
                    <div>
                        <p class="mp-hero__eyebrow"><el-icon :size="11"><MapLocation /></el-icon>{{ product.origin }}</p>
                        <h1 class="mp-hero__title">{{ product.name }}</h1>
                        <p class="mp-hero__desc">{{ product.description }}</p>
                    </div>

                    <div class="mp-hero__stat-grid">
                        <div v-for="stat in stats" :key="stat.label" class="mp-hero__stat">
                            <span class="mp-hero__stat-label"><el-icon :size="12"><component :is="stat.icon" /></el-icon>{{ stat.label }}</span>
                            <span class="mp-hero__stat-value" :class="{ 'mp-hero__stat-value--accent': stat.accent }">{{ stat.value }}</span>
                        </div>
                    </div>

                    <div class="mp-hero__sensory">
                        <span class="mp-hero__sensory-label">Sensory Profile</span>
                        <div class="mp-hero__sensory-grid">
                            <div v-for="attr in sensoryAttributes" :key="attr.label" class="mp-hero__sensory-item">
                                <span class="mp-hero__sensory-item-label">{{ attr.label }}</span>
                                <span class="mp-hero__sensory-item-value">{{ attr.value }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mp-hero__buy">
                    <div class="mp-buy-card">
                        <div class="mp-buy-card__price-row">
                            <span class="mp-buy-card__price">${{ product.price.toFixed(2) }}</span>
                            <span class="mp-buy-card__unit">/ {{ product.unit }}</span>
                        </div>

                        <AddToCartForm
                            cartable-type="market"
                            :cartable-id="item.id"
                            :item-name="product.name"
                            :unit="product.unit"
                            :max="cartMaxQuantity"
                        />
                    </div>

                    <div class="mp-trader-note">
                        <span class="mp-trader-note__label"><el-icon :size="12"><ChatLineSquare /></el-icon>Trader Note</span>
                        <p class="mp-trader-note__text">{{ traderNote }}</p>
                    </div>
                </div>
            </section>

            <!-- ── Separator between the hero and the origin story ────────── -->
            <hr class="mp-separator" />

            <!-- ── The Origin ───────────────────────────────────────────── -->
            <section class="mp-origin-story">
                <div class="mp-origin-story__grid">
                    <div class="mp-origin-story__left">
                        <div class="mp-origin-story__text">
                            <h2 class="mp-section-title"><el-icon :size="19"><MapLocation /></el-icon>The Origin</h2>
                            <div class="mp-origin-story__stats">
                                <div v-for="stat in origin.stats" :key="stat.label">
                                    <p>{{ stat.label }}</p>
                                    <strong>{{ stat.value }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mp-farmer-card">
                            <h3 class="mp-farmer-card__title"><el-icon :size="14"><House /></el-icon>Source Farms</h3>
                            <ul class="mp-farmer-card__list">
                                <li v-for="farm in sourceFarms" :key="farm.name" class="mp-farmer-card__farm">
                                    <span class="mp-farmer-card__farm-name">{{ farm.name }}</span>
                                    <span class="mp-farmer-card__farm-meta">
                                        <span v-if="farm.size">{{ farm.size }}</span>
                                        <span v-if="farm.size && farm.location"> · </span>
                                        <span v-if="farm.location">{{ farm.location }}</span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mp-map">
                        <iframe
                            class="mp-map__frame"
                            title="Mount Leuser, Sumatra"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            :src="mapEmbedSrc"/>
                        <span class="mp-map__label"><el-icon :size="13"><LocationFilled /></el-icon>{{ mapLocationLabel }}</span>
                    </div>
                </div>
            </section>

            <!-- ── Sustainability & Traceability ────────────────────────── -->
            <section class="mp-sustainability">
                <h2 class="mp-section-title"><el-icon :size="19"><Connection /></el-icon>Sustainability &amp; Traceability</h2>
                <div class="mp-sustainability__grid">
                    <div class="mp-sustainability__col">
                        <h3 class="mp-sustainability__head"><el-icon :size="16"><Grape /></el-icon> Sustainability Practices</h3>
                        <ul v-if="sustainabilityPractices.length" class="mp-practices">
                            <li v-for="practice in sustainabilityPractices" :key="practice.id" class="mp-practice">
                                <el-icon class="mp-practice__icon" :size="16"><component :is="practice.icon" /></el-icon>
                                <div class="mp-practice__body">
                                    <span class="mp-practice__name">{{ practice.name }}</span>
                                    <span v-if="practice.description" class="mp-practice__desc">{{ practice.description }}</span>
                                </div>
                            </li>
                        </ul>
                        <p v-else class="mp-empty">No sustainability practices recorded for this farm yet.</p>
                    </div>
                    <div class="mp-sustainability__col">
                        <h3 class="mp-sustainability__head"><el-icon :size="16"><Connection /></el-icon> Traceability &amp; Certifications</h3>
                        <p>Traceable from farm through processing to lot, with the farm's sustainability certifications.</p>
                    </div>
                    <div class="mp-sustainability__col">
                        <h3 class="mp-sustainability__head"><el-icon :size="16"><Connection /></el-icon> Supply Chain Timeline</h3>
                        <ul class="mp-supply-timeline">
                            <li v-for="step in supplyChainSteps" :key="step.label"><span class="mp-supply-timeline__dot" />{{ step.label }}: {{ step.date }}</li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>

        <ImageViewer v-model="viewerOpen" :images="viewerImages" :index="viewerIndex" />
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Ported 1:1 from the "Sumatra Mandheling" reference mockup, mapped
   onto the app's persistent --dp-* tokens (this mockup's own palette,
   per DESIGN.md, already matches dp-* 1:1) so this page stays in sync
   with the rest of the app's theme. Manrope (the app's established
   headline font — see MarketListings.vue) is used in place of
   DESIGN.md's Playfair Display, since the "app theme" already committed
   to a single sans-serif system everywhere else. ───────────────────────── */
.mp-page {
    --card-border: var(--dp-outline-variant);
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
}

.mp-back {
    display: inline-flex; align-items: center; gap: 6px; margin-bottom: 16px;
    font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface-variant);
    text-decoration: none; transition: color .15s ease;
}
.mp-back:hover { color: var(--dp-primary); }

.mp-section-title { display: flex; align-items: center; gap: 9px; font-size: 1.5rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); margin: 0 0 12px !important; }
.mp-section-title :deep(.el-icon) { color: var(--dp-primary); }

/* ── Hero ────────────────────────────────────────────────────────────── */
.mp-hero { display: grid; grid-template-columns: 4fr 5fr 3fr; gap: 24px; align-items: start; }

.mp-hero__gallery { display: flex; flex-direction: column; gap: 20px; }
.mp-hero__main-img { position: relative; aspect-ratio: 1 / 1; border-radius: var(--dp-card-radius); overflow: hidden; background: var(--dp-surface-container-high); box-shadow: var(--dp-card-shadow); }
.mp-hero__main-img img { width: 100%; height: 100%; object-fit: cover; }
.mp-hero__main-img--clickable { cursor: pointer; }
.mp-hero__main-img--clickable:hover img { transform: scale(1.03); }
.mp-hero__main-img img { transition: transform .25s ease; }
.mp-hero__badge {
    position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 5px;
    background: rgba(163, 246, 156, .92); color: var(--dp-on-secondary-fixed);
    font-size: .6875rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em;
    box-shadow: 0 2px 6px rgba(39, 19, 16, .15);
}
.mp-hero__thumbs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.mp-hero__thumb { aspect-ratio: 1 / 1; border-radius: var(--dp-card-radius); overflow: hidden; background: var(--dp-surface-container); box-shadow: var(--dp-card-shadow); }
.mp-hero__thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .2s ease; }
.mp-hero__thumb--clickable { cursor: pointer; }
.mp-hero__thumb--clickable:hover img { transform: scale(1.06); }

.mp-hero__specs { display: flex; flex-direction: column; gap: 22px; }
.mp-hero__eyebrow {
    display: flex; align-items: center; gap: 6px;
    font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .14em;
    color: var(--dp-outline); margin: 0 0 6px !important;
}
.mp-hero__title { font-family: var(--dp-font-sans); font-size: 2.125rem; font-weight: 800; letter-spacing: -.015em; line-height: 1.2; color: var(--dp-on-surface); margin: 0 0 10px !important; }
.mp-hero__desc { font-size: 1rem; line-height: 1.6; color: var(--dp-on-surface-variant); margin: 0 !important; }

.mp-hero__stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.mp-hero__stat { display: flex; flex-direction: column; gap: 5px; background: var(--dp-surface-container); border: 1px solid var(--card-border); border-radius: 12px; padding: 14px 16px; transition: box-shadow .15s ease, transform .15s ease; }
.mp-hero__stat:hover { box-shadow: var(--dp-card-shadow); transform: translateY(-1px); }
.mp-hero__stat-label { display: flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--dp-outline); }
.mp-hero__stat-value { font-size: .9375rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-hero__stat-value--accent { font-size: 1.5rem; font-weight: 800; color: var(--dp-primary); }

.mp-hero__sensory-label { display: block; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--dp-outline); margin-bottom: 10px; }
.mp-hero__sensory-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.mp-hero__sensory-item { display: flex; flex-direction: column; gap: 3px; padding: 8px 10px; border-radius: 8px; background: var(--dp-primary-container); }
.mp-hero__sensory-item-label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-primary-container); opacity: .7; }
.mp-hero__sensory-item-value { font-size: .8125rem; font-weight: 600; color: var(--dp-on-primary-container); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.mp-hero__buy { display: flex; flex-direction: column; gap: 20px; }
.mp-buy-card { padding: 22px; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow); transition: box-shadow .2s ease; }
.mp-buy-card:hover { box-shadow: 0 1px 2px rgba(39, 19, 16, .04), 0 14px 28px -14px rgba(39, 19, 16, .18); }

.mp-buy-card__price-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 18px; }
.mp-buy-card__price { font-family: var(--dp-font-sans); font-size: 1.75rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); }
.mp-buy-card__unit { font-size: .75rem; color: var(--dp-on-surface-variant); }

.mp-trader-note { padding: 20px; border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); background: rgba(62, 39, 35, .06); box-shadow: var(--dp-card-shadow); }
.mp-trader-note__label { display: flex; align-items: center; gap: 6px; font-size: .6875rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--dp-primary); margin-bottom: 6px; }
.mp-trader-note__text { font-size: .8125rem; line-height: 1.65; color: var(--dp-on-surface); margin: 0 !important; }

/* ── Section separator ───────────────────────────────────────────────── */
.mp-separator { border: none; border-top: 1px solid var(--card-border); margin: 24px 0; opacity: 1; }

/* ── The Origin ──────────────────────────────────────────────────────── */
.mp-origin-story { margin-top: 0; padding: 32px; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow); }
.mp-origin-story__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
.mp-origin-story__left { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.mp-origin-story__stats { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 20px; margin-top: 20px; }
.mp-origin-story__stats > div { display: flex; flex-direction: column; gap: 4px; }
.mp-origin-story__stats > div:last-child:nth-child(odd) { grid-column: 1 / -1; }
.mp-origin-story__stats p { font-size: .6875rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: var(--dp-outline); margin: 0 !important; }
.mp-origin-story__stats strong { font-size: .9375rem; font-weight: 700; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }

.mp-farmer-card { padding: 20px; background: var(--dp-surface-container); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow); height: 100%; transition: box-shadow .15s ease, transform .15s ease; }
.mp-farmer-card:hover { transform: translateY(-1px); box-shadow: 0 1px 2px rgba(39, 19, 16, .04), 0 14px 28px -14px rgba(39, 19, 16, .18); }
.mp-farmer-card__title { display: flex; align-items: center; gap: 6px; font-size: .875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-primary); margin: 0 0 10px !important; }
.mp-farmer-card__list { display: flex; flex-direction: column; gap: 10px; list-style: none; margin: 0 !important; padding: 0 !important; }
.mp-farmer-card__farm { display: flex; flex-direction: column; gap: 2px; padding-bottom: 10px; border-bottom: 1px solid var(--card-border); }
.mp-farmer-card__farm:last-child { padding-bottom: 0; border-bottom: none; }
.mp-farmer-card__farm-name { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-farmer-card__farm-meta { font-size: .75rem; color: var(--dp-on-surface-variant); }

.mp-map { position: relative; width: 100%; aspect-ratio: 16 / 9; border-radius: var(--dp-card-radius); overflow: hidden; box-shadow: var(--dp-card-shadow); }
.mp-map__frame { width: 100%; height: 100%; border: 0; display: block; }
.mp-map__label {
    position: absolute; left: 10px; bottom: 10px; display: inline-flex; align-items: center; gap: 6px;
    font-size: .6875rem; font-weight: 700; color: var(--dp-on-surface);
    background: rgba(249, 249, 249, .9); backdrop-filter: blur(4px); padding: 5px 10px; border-radius: 7px;
    box-shadow: 0 2px 6px rgba(39, 19, 16, .12);
}

/* ── Sustainability & Traceability ───────────────────────────────────── */
.mp-sustainability { margin-top: 32px; padding: 32px; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow); }
.mp-sustainability__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; margin-top: 24px; }
.mp-sustainability__head { display: flex; align-items: center; gap: 8px; font-size: .9375rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface); margin: 0 0 10px !important; }
.mp-sustainability__head :deep(.el-icon) { color: var(--dp-primary); }
.mp-sustainability__col p { font-size: .8125rem; line-height: 1.65; color: var(--dp-on-surface-variant); margin: 0 !important; }

/* ── Sustainability practices list (first column) ─────────────────────── */
.mp-practices { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.mp-practice { display: flex; align-items: flex-start; gap: 10px; }
.mp-practice__icon {
    width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--dp-secondary-container); color: var(--dp-on-secondary-container);
}
.mp-practice__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.mp-practice__name { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); line-height: 1.35; }
.mp-practice__desc { font-size: .75rem; line-height: 1.5; color: var(--dp-on-surface-variant); }
.mp-empty { font-size: .8125rem; color: var(--dp-on-surface-variant); margin: 0; }

.mp-supply-timeline { list-style: none; margin: 6px 0 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
.mp-supply-timeline li { display: flex; align-items: center; gap: 8px; font-size: .75rem; color: var(--dp-on-surface-variant); }
.mp-supply-timeline__dot { width: 6px; height: 6px; border-radius: 50%; background: var(--dp-secondary); flex-shrink: 0; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .mp-hero { grid-template-columns: 1fr; }
    .mp-hero__specs { padding: 0; }
    .mp-origin-story__grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .mp-origin-story__left { grid-template-columns: 1fr; }
    .mp-sustainability__grid { grid-template-columns: 1fr; }
    .mp-hero__stat-grid { grid-template-columns: 1fr 1fr; }
    .mp-hero__sensory-grid { grid-template-columns: 1fr 1fr; }
}
</style>
