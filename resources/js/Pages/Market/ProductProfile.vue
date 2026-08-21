<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Minus, Plus, ShoppingBag, LocationFilled, ArrowLeft,
    Grape, Pouring, Connection, UserFilled,
    Star, TrendCharts, Setting, Collection, MapLocation, ChatLineSquare, House, Calendar,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

defineProps({
    item: { type: Object, default: () => ({}) },
    cartQuantity: { type: Number, default: 0 },
});

/* ── Hard-coded showcase content — ported 1:1 from the "Sumatra
   Mandheling" reference mockup (code.html) per explicit request to
   redesign this page's content area to match it exactly. This app has
   no backing data model yet for a multi-image gallery, trader notes,
   farm-profile copy, or sustainability programs, so all of it below is
   placeholder content, not derived from the real `item` prop. The
   quantity stepper is local-only and "Add to Cart" is presentational
   until a real listing is wired up to this layout. ─────────────────── */
const product = {
    origin: 'Indonesia',
    name: 'Sumatra Mandheling',
    description: 'Full-bodied, low-acidity profile with intense earthy and herbaceous notes.',
    price: 24.00,
    unit: '250g bag',
};

const stats = [
    { label: 'SCA Score', value: '88.0', accent: true, icon: Star },
    { label: 'Altitude', value: '1,400 - 1,600m', icon: TrendCharts },
    { label: 'Process', value: 'Giling Basah', icon: Setting },
    { label: 'Varietals', value: 'Typica, Catimor', icon: Collection },
];

const sensoryChips = [
    { label: 'Earthy', dot: 'var(--dp-surface-tint)' },
    { label: 'Spicy', dot: 'var(--dp-error-container)' },
    { label: 'Dark Chocolate', dot: '#191818' },
];

const traderNote = 'High demand lot. Consistent cup quality across previous 3 harvests. Recommended for specialty espresso programs.';

const origin = {
    description: 'Grown on the slopes of Mount Leuser, this Mandheling lot benefits from rich volcanic andisols and a unique microclimate.',
    stats: [
        { label: 'Farm Group', value: 'Lintong Nihuta', icon: House },
        { label: 'Harvest Season', value: 'Sept - Dec', icon: Calendar },
    ],
};

const farmerProfile = {
    text: 'The Lintong Nihuta cooperative represents 150 smallholders preserving traditional Giling Basah methods for over three generations.',
    impact: 'Community Impact: 100% Fair Trade',
};

const mapLocationLabel = 'Mount Leuser, Sumatra';
// Real public coordinates for Mount Leuser National Park, Sumatra (~3.70°N, 97.15°E) —
// a live OpenStreetMap embed (no API key needed) instead of a static photo standing in for a map.
const mapEmbedSrc = 'https://www.openstreetmap.org/export/embed.html?bbox=96.95%2C3.50%2C97.35%2C3.90&layer=mapnik&marker=3.70%2C97.15';

const sustainabilityPillars = [
    {
        icon: Grape,
        title: 'Regenerative Practices',
        text: 'Intercropping with shade trees and organic composting to maintain soil health and biodiversity in the Leuser ecosystem.',
    },
    {
        icon: Pouring,
        title: 'Water Conservation',
        text: 'Closed-loop wet milling systems reduce water consumption by 40% compared to traditional regional processing standards.',
    },
];

const supplyChainSteps = [
    { label: 'Harvest', date: 'Oct 12' },
    { label: 'Processing', date: 'Oct 15' },
    { label: 'Export', date: 'Nov 02' },
];

const PLACEHOLDER_IMAGE = '/images/coffee_image.jpg';

const quantity = ref(1);
function decrementQuantity() { if (quantity.value > 1) quantity.value -= 1; }
function incrementQuantity() { quantity.value += 1; }
</script>

<template>
    <DesignPreviewLayout :title="product.name">
        <Head :title="product.name" />

        <div class="mp-page">

            <!-- ── Hero — image gallery / specs / purchase widget ─────────── -->
            <section class="mp-hero">
                <div class="mp-hero__gallery">
                    <div class="mp-hero__main-img">
                        <img :src="PLACEHOLDER_IMAGE" :alt="product.name">
                        <span class="mp-hero__badge">Single Origin</span>
                    </div>
                    <div class="mp-hero__thumbs">
                        <div class="mp-hero__thumb"><img :src="PLACEHOLDER_IMAGE" :alt="product.name"></div>
                        <div class="mp-hero__thumb"><img :src="PLACEHOLDER_IMAGE" :alt="product.name"></div>
                        <div class="mp-hero__thumb"><img :src="PLACEHOLDER_IMAGE" :alt="product.name"></div>
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
                        <div class="mp-hero__chips">
                            <span v-for="chip in sensoryChips" :key="chip.label" class="mp-hero__chip">
                                <span class="mp-hero__chip-dot" :style="{ background: chip.dot }" />{{ chip.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mp-hero__buy">
                    <div class="mp-buy-card">
                        <div class="mp-buy-card__price-row">
                            <span class="mp-buy-card__price">${{ product.price.toFixed(2) }}</span>
                            <span class="mp-buy-card__unit">/ {{ product.unit }}</span>
                        </div>

                        <div class="mp-qty">
                            <button type="button" class="mp-qty__btn" :disabled="quantity <= 1" @click="decrementQuantity">
                                <el-icon><Minus /></el-icon>
                            </button>
                            <span class="mp-qty__value">{{ quantity }}</span>
                            <button type="button" class="mp-qty__btn" @click="incrementQuantity">
                                <el-icon><Plus /></el-icon>
                            </button>
                        </div>

                        <button type="button" class="mp-cart-btn">
                            <el-icon><ShoppingBag /></el-icon> Add to Cart
                        </button>
                    </div>

                    <div class="mp-trader-note">
                        <span class="mp-trader-note__label"><el-icon :size="12"><ChatLineSquare /></el-icon>Trader Note</span>
                        <p class="mp-trader-note__text">{{ traderNote }}</p>
                    </div>
                </div>
            </section>

            <!-- ── The Origin ───────────────────────────────────────────── -->
            <section class="mp-origin-story">
                <div class="mp-origin-story__grid">
                    <div class="mp-origin-story__left">
                        <div class="mp-origin-story__text">
                            <h2 class="mp-section-title"><el-icon :size="19"><MapLocation /></el-icon>The Origin</h2>
                            <p class="mp-desc">{{ origin.description }}</p>
                            <div class="mp-origin-story__stats">
                                <div v-for="stat in origin.stats" :key="stat.label">
                                    <p><el-icon :size="11"><component :is="stat.icon" /></el-icon>{{ stat.label }}</p>
                                    <strong>{{ stat.value }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mp-farmer-card">
                            <h3 class="mp-farmer-card__title"><el-icon :size="14"><UserFilled /></el-icon>Farmer Profile</h3>
                            <p class="mp-farmer-card__text">{{ farmerProfile.text }}</p>
                            <div class="mp-farmer-card__impact">
                                <span class="mp-farmer-card__impact-icon"><el-icon :size="13"><UserFilled /></el-icon></span>
                                {{ farmerProfile.impact }}
                            </div>
                        </div>
                    </div>

                    <div class="mp-map">
                        <iframe
                            class="mp-map__frame"
                            title="Mount Leuser, Sumatra"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            :src="mapEmbedSrc"
                        />
                        <span class="mp-map__label"><el-icon :size="13"><LocationFilled /></el-icon>{{ mapLocationLabel }}</span>
                    </div>
                </div>
            </section>

            <!-- ── Sustainability & Traceability ────────────────────────── -->
            <section class="mp-sustainability">
                <h2 class="mp-section-title"><el-icon :size="19"><Connection /></el-icon>Sustainability &amp; Traceability</h2>
                <div class="mp-sustainability__grid">
                    <div v-for="pillar in sustainabilityPillars" :key="pillar.title" class="mp-sustainability__col">
                        <h3 class="mp-sustainability__head"><el-icon :size="16"><component :is="pillar.icon" /></el-icon> {{ pillar.title }}</h3>
                        <p>{{ pillar.text }}</p>
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
.mp-page { font-family: var(--dp-font-sans); color: var(--dp-on-surface); margin-top: -24px; }

.mp-back {
    display: inline-flex; align-items: center; gap: 6px; margin-bottom: 16px;
    font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface-variant);
    text-decoration: none; transition: color .15s ease;
}
.mp-back:hover { color: var(--dp-primary); }

.mp-section-title { display: flex; align-items: center; gap: 9px; font-size: 1.5rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); margin: 0 0 12px !important; }
.mp-section-title :deep(.el-icon) { color: var(--dp-primary); }
.mp-desc { font-size: 1rem; line-height: 1.7; color: var(--dp-on-surface-variant); margin: 0 !important; }

/* ── Hero ────────────────────────────────────────────────────────────── */
.mp-hero { display: grid; grid-template-columns: 4fr 5fr 3fr; gap: 24px; align-items: start; }

.mp-hero__gallery { display: flex; flex-direction: column; gap: 20px; }
.mp-hero__main-img { position: relative; aspect-ratio: 1 / 1; border-radius: var(--dp-card-radius); overflow: hidden; background: var(--dp-surface-container-high); box-shadow: var(--dp-card-shadow); }
.mp-hero__main-img img { width: 100%; height: 100%; object-fit: cover; }
.mp-hero__badge {
    position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 5px;
    background: rgba(163, 246, 156, .92); color: var(--dp-on-secondary-fixed);
    font-size: .6875rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em;
    box-shadow: 0 2px 6px rgba(39, 19, 16, .15);
}
.mp-hero__thumbs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.mp-hero__thumb { aspect-ratio: 1 / 1; border-radius: 9px; overflow: hidden; background: var(--dp-surface-container); box-shadow: var(--dp-card-shadow); }
.mp-hero__thumb img { width: 100%; height: 100%; object-fit: cover; }

.mp-hero__specs { display: flex; flex-direction: column; gap: 22px; }
.mp-hero__eyebrow {
    display: flex; align-items: center; gap: 6px;
    font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .14em;
    color: var(--dp-outline); margin: 0 0 6px !important;
}
.mp-hero__title { font-family: var(--dp-font-sans); font-size: 2.125rem; font-weight: 800; letter-spacing: -.015em; line-height: 1.2; color: var(--dp-on-surface); margin: 0 0 10px !important; }
.mp-hero__desc { font-size: 1rem; line-height: 1.6; color: var(--dp-on-surface-variant); margin: 0 !important; }

.mp-hero__stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.mp-hero__stat { display: flex; flex-direction: column; gap: 5px; background: var(--dp-surface-container); border-radius: 12px; padding: 14px 16px; transition: box-shadow .15s ease, transform .15s ease; }
.mp-hero__stat:hover { box-shadow: var(--dp-card-shadow); transform: translateY(-1px); }
.mp-hero__stat-label { display: flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--dp-outline); }
.mp-hero__stat-value { font-size: .9375rem; font-weight: 700; color: var(--dp-on-surface); }
.mp-hero__stat-value--accent { font-size: 1.5rem; font-weight: 800; color: var(--dp-primary); }

.mp-hero__sensory-label { display: block; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--dp-outline); margin-bottom: 10px; }
.mp-hero__chips { display: flex; flex-wrap: wrap; gap: 6px; }
.mp-hero__chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 11px; border-radius: 7px; background: var(--dp-primary-container); color: var(--dp-on-primary-container); font-size: .75rem; font-weight: 600; }
.mp-hero__chip-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

.mp-hero__buy { display: flex; flex-direction: column; gap: 20px; }
.mp-buy-card { padding: 22px; background: var(--dp-surface-container-lowest); border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow); transition: box-shadow .2s ease; }
.mp-buy-card:hover { box-shadow: 0 1px 2px rgba(39, 19, 16, .04), 0 14px 28px -14px rgba(39, 19, 16, .18); }
.mp-buy-card__price-row { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 18px; }
.mp-buy-card__price { font-family: var(--dp-font-sans); font-size: 1.75rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); }
.mp-buy-card__unit { font-size: .75rem; color: var(--dp-on-surface-variant); }

.mp-qty { display: flex; align-items: center; justify-content: space-between; background: var(--dp-surface-container-low); border-radius: 8px; padding: 4px; margin-bottom: 10px; }
.mp-qty__btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border: none; border-radius: 6px; background: transparent; color: var(--dp-on-surface); cursor: pointer; transition: background .12s ease; }
.mp-qty__btn:hover:not(:disabled) { background: var(--dp-surface-container-high); }
.mp-qty__btn:disabled { opacity: .4; cursor: default; }
.mp-qty__value { font-size: .9375rem; font-weight: 700; }

.mp-cart-btn {
    width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 14px; border: none; border-radius: 8px; background: var(--dp-primary); color: var(--dp-on-primary);
    font-size: .9375rem; font-weight: 700; cursor: pointer;
    box-shadow: 0 6px 16px -8px rgba(39, 19, 16, .45);
    transition: opacity .15s ease, transform .15s ease;
}
.mp-cart-btn:hover { opacity: .92; transform: translateY(-1px); }

.mp-trader-note { padding: 20px; border-radius: var(--dp-card-radius); background: rgba(62, 39, 35, .06); box-shadow: var(--dp-card-shadow); }
.mp-trader-note__label { display: flex; align-items: center; gap: 6px; font-size: .6875rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--dp-primary); margin-bottom: 6px; }
.mp-trader-note__text { font-size: .8125rem; line-height: 1.65; color: var(--dp-on-surface); margin: 0 !important; }

/* ── The Origin ──────────────────────────────────────────────────────── */
.mp-origin-story { margin-top: 48px; padding: 32px; background: var(--dp-surface-container-lowest); border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow); }
.mp-origin-story__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
.mp-origin-story__left { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.mp-origin-story__stats { display: flex; flex-direction: column; gap: 12px; margin-top: 20px; }
.mp-origin-story__stats p { display: flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--dp-outline); margin: 0 !important; }
.mp-origin-story__stats strong { font-size: .9375rem; font-weight: 700; color: var(--dp-on-surface); }

.mp-farmer-card { padding: 20px; background: var(--dp-surface-container); border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow); height: fit-content; transition: box-shadow .15s ease, transform .15s ease; }
.mp-farmer-card:hover { transform: translateY(-1px); box-shadow: 0 1px 2px rgba(39, 19, 16, .04), 0 14px 28px -14px rgba(39, 19, 16, .18); }
.mp-farmer-card__title { display: flex; align-items: center; gap: 6px; font-size: .875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-primary); margin: 0 0 10px !important; }
.mp-farmer-card__text { font-size: .8125rem; line-height: 1.65; color: var(--dp-on-surface-variant); margin: 0 0 14px !important; }
.mp-farmer-card__impact { display: flex; align-items: center; gap: 8px; font-size: .6875rem; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; color: var(--dp-on-surface); }
.mp-farmer-card__impact-icon { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; flex-shrink: 0; border-radius: 50%; background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }

.mp-map { position: relative; width: 100%; aspect-ratio: 16 / 9; border-radius: var(--dp-card-radius); overflow: hidden; box-shadow: var(--dp-card-shadow); background: var(--dp-surface-container-high); }
.mp-map__frame { width: 100%; height: 100%; border: 0; display: block; }
.mp-map__label {
    position: absolute; left: 10px; bottom: 10px; display: inline-flex; align-items: center; gap: 6px;
    font-size: .6875rem; font-weight: 700; color: var(--dp-on-surface);
    background: rgba(249, 249, 249, .9); backdrop-filter: blur(4px); padding: 5px 10px; border-radius: 7px;
    box-shadow: 0 2px 6px rgba(39, 19, 16, .12);
}

/* ── Sustainability & Traceability ───────────────────────────────────── */
.mp-sustainability { margin-top: 32px; padding: 32px; background: var(--dp-surface-container-lowest); border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow); }
.mp-sustainability__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; margin-top: 24px; }
.mp-sustainability__head { display: flex; align-items: center; gap: 8px; font-size: .9375rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface); margin: 0 0 10px !important; }
.mp-sustainability__head :deep(.el-icon) { color: var(--dp-primary); }
.mp-sustainability__col p { font-size: .8125rem; line-height: 1.65; color: var(--dp-on-surface-variant); margin: 0 !important; }

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
}
</style>
