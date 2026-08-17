<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Tickets, Star, ShoppingCart, Check, Minus, Plus, Lock, CircleCheck, Medal,
    Document, Shop, MapLocation, CaretTop, CaretBottom, Coin, List, ChatLineSquare,
} from '@element-plus/icons-vue';
import MarketPage from './MarketPage.vue';

const props = defineProps({
    item: { type: Object, required: true },
    cartQuantity: { type: Number, default: 0 },
});

const searchQuery = ref('');

/* ── Identity — same deterministic initials + palette used on the order
   page, so a seller avatar reads consistently across the app. ─────────── */
const avatarPalette = [
    { bg: '#eef2ff', color: '#4338ca' },
    { bg: '#ecfdf5', color: '#047857' },
    { bg: '#fff7ed', color: '#c2410c' },
    { bg: '#fdf4ff', color: '#a21caf' },
    { bg: '#eff6ff', color: '#1d4ed8' },
    { bg: '#f0fdfa', color: '#0f766e' },
];

function initials(name) {
    const parts = (name || '').trim().split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || '?';
}

function avatarStyle(name) {
    const str = name || '';
    let hash = 0;
    for (let i = 0; i < str.length; i += 1) hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
    const swatch = avatarPalette[hash % avatarPalette.length];
    return { background: swatch.bg, color: swatch.color };
}

const qualityTone = (score) => {
    if (score >= 85) return 'green';
    if (score >= 70) return 'amber';
    return 'red';
};

const qualityLabel = (score) => {
    if (score >= 85) return 'Excellent';
    if (score >= 70) return 'Good';
    return 'Needs Review';
};

/* ── Quality ring — SVG stroke-dasharray gauge ────────────────────────── */
const RING_RADIUS = 46;
const RING_CIRCUMFERENCE = 2 * Math.PI * RING_RADIUS;

const qualityRingOffset = computed(() => {
    const pct = Math.min(100, Math.max(0, props.item.quality_score)) / 100;
    return RING_CIRCUMFERENCE * (1 - pct);
});

function formatDate(dateTime) {
    if (!dateTime) return '—';
    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDateOnly(date) {
    if (!date) return '—';
    return new Date(`${date}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function titleCase(value) {
    return String(value).replace(/(^|[\s_-])(\w)/g, (m, sep, ch) => sep + ch.toUpperCase());
}

/* ── Stock status — 100kg is roughly one standard 60kg export bag with
   room to spare, a real trade-scale threshold rather than an arbitrary one. */
const stockStatus = computed(() => {
    if (props.item.quantity <= 0) return { tone: 'red', label: 'Sold Out' };
    if (props.item.quantity < 100) return { tone: 'amber', label: 'Low Stock' };
    return { tone: 'green', label: 'In Stock' };
});

/* ── Market comparison — real delta vs. same-type live listings ────────── */
const priceDelta = computed(() => {
    const pct = props.item.price_delta_pct;
    if (pct === null || pct === undefined) return null;
    const below = pct <= 0;
    const abs = Math.abs(pct);
    // Beyond ~2x the market average, a precise percentage reads like a bug
    // rather than a signal — fall back to a qualitative label instead.
    const label = abs > 200
        ? (below ? 'Well below market avg' : 'Well above market avg')
        : `${abs.toFixed(1)}% ${below ? 'below' : 'above'} market avg`;
    return { below, label };
});

/* ── Specification sheet — split like a lot dossier ──────────────────── */
const technicalSpecDefs = [
    { key: 'grade', label: 'Grade' },
    { key: 'variety', label: 'Variety', format: titleCase },
    { key: 'altitude', label: 'Altitude', suffix: ' masl' },
    { key: 'screen_size', label: 'Screen Size' },
    { key: 'packaging_type', label: 'Packaging' },
    { key: 'net_weight_kg', label: 'Net Weight', suffix: ' kg' },
    { key: 'quantity_bags', label: 'Bags' },
    { key: 'bag_weight_kg', label: 'Bag Weight', suffix: ' kg' },
    { key: 'warehouse', label: 'Warehouse' },
];

const qualityMetricDefs = [
    { key: 'moisture_content', label: 'Moisture Content', suffix: '%' },
    { key: 'defect_count', label: 'Defect Count' },
    { key: 'drying_method', label: 'Drying Method' },
    { key: 'processing_date', label: 'Processing Date', format: formatDateOnly },
];


function buildRows(defs) {
    const rows = [];
    for (const def of defs) {
        const raw = props.item.specs?.[def.key];
        if (raw === undefined || raw === null || raw === '') continue;
        const value = def.format ? def.format(raw) : raw;
        rows.push({ label: def.label, value: `${value}${def.suffix ?? ''}` });
    }
    return rows;
}

const technicalSpecRows = computed(() => {
    const rows = [];
    if (props.item.origin) rows.push({ label: 'Origin', value: props.item.origin });
    if (props.item.type) rows.push({ label: 'Type', value: props.item.type });
    if (props.item.process) rows.push({ label: 'Process', value: props.item.process });
    if (props.item.target_market) rows.push({ label: 'Target Market', value: props.item.target_market });
    return rows.concat(buildRows(technicalSpecDefs));
});

const qualityMetricRows = computed(() => buildRows(qualityMetricDefs));

/* ── Cupping profile ─────────────────────────────────────────────────── */
const cuppingDefs = [
    { key: 'aroma_score', label: 'Aroma' },
    { key: 'acidity_score', label: 'Acidity' },
    { key: 'body_score', label: 'Body' },
    { key: 'aftertaste_score', label: 'Aftertaste' },
];

const cuppingRows = computed(() => {
    const cupping = props.item.cupping;
    if (!cupping) return [];
    return cuppingDefs
        .filter((def) => cupping[def.key] !== undefined)
        .map((def) => ({ label: def.label, value: cupping[def.key], width: `${Math.min(100, (cupping[def.key] / 10) * 100)}%` }));
});

const cuppingNotes = computed(() => {
    const cupping = props.item.cupping;
    if (!cupping) return [];
    return [
        cupping.fragrance_notes ? { label: 'Fragrance', text: cupping.fragrance_notes } : null,
        cupping.flavor_notes ? { label: 'Flavor', text: cupping.flavor_notes } : null,
        cupping.cupping_notes ? { label: "Cupper's Notes", text: cupping.cupping_notes } : null,
    ].filter(Boolean);
});

/* ── Add to cart ─────────────────────────────────────────────────────── */
const quantity = ref(1);
const maxQuantity = computed(() => Math.max(1, Math.floor(props.item.quantity || 1)));

function decrementQuantity() {
    if (quantity.value > 1) quantity.value -= 1;
}

function incrementQuantity() {
    if (quantity.value < maxQuantity.value) quantity.value += 1;
}

function clampQuantity() {
    if (!Number.isFinite(quantity.value) || quantity.value < 1) {
        quantity.value = 1;
    } else if (quantity.value > maxQuantity.value) {
        quantity.value = maxQuantity.value;
    } else {
        quantity.value = Math.floor(quantity.value);
    }
}

/* ── Live order summary ──────────────────────────────────────────────── */
const subtotal = computed(() => (props.item.price_per_kg || 0) * (quantity.value || 0));

function formatCurrency(value) {
    return `$${(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

// Re-keying the total on change restarts its CSS pulse animation, even
// across rapid successive quantity changes.
const totalPulseKey = ref(0);
watch(subtotal, () => { totalPulseKey.value += 1; });

const cartForm = useForm({
    market_id: props.item.id,
    quantity: 1,
});

function addToCart() {
    clampQuantity();
    cartForm.quantity = quantity.value;
    cartForm.post(route('checkout.items.store'), {
        preserveScroll: true,
        onSuccess: () => { quantity.value = 1; },
    });
}

// Bulk lots aren't a fixed-unit purchase — jumping straight to a blind
// "add 1kg" from the hero would misrepresent what the buyer is ordering.
// Send them to the quantity field instead of guessing a quantity for them.
function focusQuantity() {
    const el = document.getElementById('pp-qty');
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    window.requestAnimationFrame(() => el.focus());
}
</script>

<template>
    <MarketPage v-model:search-query="searchQuery">
        <Head :title="item.name" />

        <div class="pp-page">
            <div class="mkt-body">
                <div class="man-content">
                    <el-row :gutter="24">
                        <!-- ── Product details — one single card, 70% width ── -->
                        <el-col :xs="24" :sm="24" :md="17">
                            <el-card shadow="never" class="pp-card pp-details-card" :body-style="{ padding: 0 }">
                                <div class="pp-hero">
                                    <div class="pp-hero__media">
                                        <div class="pp-media">
                                            <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.name">
                                            <svg v-else class="pp-media__icon" viewBox="0 0 24 24">
                                                <ellipse cx="9" cy="12" rx="5" ry="7" transform="rotate(-25 9 12)" fill="#4b2e1d" />
                                                <path d="M9 6 C7 9, 11 15, 9 18" stroke="#c9a27a" stroke-width="1.1" fill="none" transform="rotate(-25 9 12)" />
                                                <ellipse cx="16.5" cy="14.5" rx="4" ry="5.5" transform="rotate(20 16.5 14.5)" fill="#6b4226" />
                                                <path d="M16.5 10 C15 12.5, 18 16, 16.5 19" stroke="#d8b48f" stroke-width="1" fill="none" transform="rotate(20 16.5 14.5)" />
                                            </svg>
                                            <span v-if="item.quality_score >= 85" class="pp-media__ribbon"><el-icon><Medal /></el-icon> Premium Lot</span>
                                        </div>
                                    </div>

                                    <div class="pp-hero__info">
                                        <div class="pp-tags">
                                            <span v-if="item.seller_name" class="mkt-badge mkt-badge--green">Verified Seller</span>
                                            <span v-if="item.is_traceable" class="mkt-badge mkt-badge--muted">Traceable Lot</span>
                                            <span v-for="b in item.badges" :key="b" class="mkt-badge mkt-badge--green">{{ b }}</span>
                                        </div>

                                        <div class="mkt-kicker"><el-icon><Tickets /></el-icon> {{ item.lot_code }}</div>
                                        <h1 class="pp-hero__title">{{ item.name }}</h1>

                                        <div class="pp-hero__meta">
                                            <span class="pp-hero__meta-item" :class="`pp-hero__meta-item--${qualityTone(item.quality_score)}`">
                                                <el-icon><Star /></el-icon> {{ item.quality_score.toFixed(1) }} quality score
                                            </span>
                                            <span class="pp-hero__meta-dot" />
                                            <span class="pp-hero__meta-item"><el-icon><MapLocation /></el-icon> {{ item.origin || '—' }}</span>
                                            <span class="pp-hero__meta-dot" />
                                            <span class="pp-stock-pill" :class="`pp-stock-pill--${stockStatus.tone}`">{{ stockStatus.label }}</span>
                                        </div>

                                        <div class="pp-hero__price-block">
                                            <div class="pp-hero__price-row">
                                                <span class="pp-hero__price">${{ item.price_per_kg.toFixed(2) }}</span>
                                                <span class="pp-hero__price-unit">/ kg</span>
                                            </div>
                                            <div class="pp-hero__price-tags">
                                                <span v-if="priceDelta" class="pp-delta" :class="priceDelta.below ? 'pp-delta--down' : 'pp-delta--up'">
                                                    <el-icon><component :is="priceDelta.below ? CaretBottom : CaretTop" /></el-icon> {{ priceDelta.label }}
                                                </span>
                                                <span class="pp-hero__spot">Spot Price</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ── Overview, Quality & Traceability — stacked in one card ── -->
                                <div class="pp-sections">
                                    <section class="pp-section">
                                        <h3 class="pp-section-title"><el-icon :size="17"><Document /></el-icon> Overview &amp; Specs</h3>

                                        <h4 class="pp-spec-col__head"><el-icon :size="13"><Document /></el-icon> Description</h4>
                                        <div class="pp-notes-card">
                                            <el-icon class="pp-notes-card__icon" :size="34"><Document /></el-icon>
                                            <p v-if="item.notes" class="pp-notes-card__text">{{ item.notes }}</p>
                                            <p v-else class="pp-notes-card__text pp-notes-card__text--empty">No description has been provided for this lot yet.</p>
                                        </div>

                                        <el-row :gutter="32" class="pp-spec-columns-row">
                                            <el-col :xs="24" :sm="24" :md="12">
                                                <h4 class="pp-spec-col__head"><el-icon :size="13"><List /></el-icon> Technical Specifications</h4>
                                                <dl class="pp-spec-list">
                                                    <div v-for="row in technicalSpecRows" :key="row.label" class="pp-spec-row">
                                                        <dt>{{ row.label }}</dt>
                                                        <dd>{{ row.value }}</dd>
                                                    </div>
                                                </dl>
                                            </el-col>
                                            <el-col :xs="24" :sm="24" :md="12">
                                                <h4 class="pp-spec-col__head"><el-icon :size="13"><Star /></el-icon> Quality Metrics</h4>
                                                <dl class="pp-spec-list">
                                                    <div class="pp-spec-row">
                                                        <dt>Quality Score</dt>
                                                        <dd><span class="pp-spec-highlight">{{ item.quality_score.toFixed(1) }}</span></dd>
                                                    </div>
                                                    <div v-for="row in qualityMetricRows" :key="row.label" class="pp-spec-row">
                                                        <dt>{{ row.label }}</dt>
                                                        <dd>{{ row.value }}</dd>
                                                    </div>
                                                </dl>
                                            </el-col>
                                        </el-row>
                                    </section>

                                    <section class="pp-section">
                                        <h3 class="pp-section-title"><el-icon :size="17"><Star /></el-icon> Quality &amp; Sensory</h3>

                                        <div class="pp-quality-hero">
                                            <div class="pp-quality-ring">
                                                <svg viewBox="0 0 100 100">
                                                    <circle class="pp-quality-ring__track" cx="50" cy="50" r="46" />
                                                    <circle
                                                        class="pp-quality-ring__fill"
                                                        :class="`pp-quality-ring__fill--${qualityTone(item.quality_score)}`"
                                                        cx="50" cy="50" r="46"
                                                        :style="{ strokeDasharray: RING_CIRCUMFERENCE, strokeDashoffset: qualityRingOffset }"
                                                    />
                                                </svg>
                                                <div class="pp-quality-ring__value" :class="`pp-quality-ring__value--${qualityTone(item.quality_score)}`">
                                                    {{ item.quality_score.toFixed(1) }}
                                                    <small>/ 100</small>
                                                </div>
                                            </div>

                                            <div class="pp-quality-hero__info">
                                                <span class="pp-quality-hero__badge" :class="`pp-quality-hero__badge--${qualityTone(item.quality_score)}`">
                                                    <el-icon :size="12"><Star /></el-icon> {{ qualityLabel(item.quality_score) }}
                                                </span>
                                                <p class="pp-quality-hero__text">
                                                    Graded before the lot was listed. The score reflects overall cup quality, defect rate, and consistency against the seller's records.
                                                </p>
                                            </div>
                                        </div>

                                        <div v-if="cuppingRows.length" class="pp-cupping-grid">
                                            <div v-for="row in cuppingRows" :key="row.label" class="pp-cupping-tile">
                                                <span class="pp-cupping-tile__label">{{ row.label }}</span>
                                                <span class="pp-cupping-tile__value">{{ row.value.toFixed(1) }}</span>
                                                <div class="pp-cupping-tile__track"><div class="pp-cupping-tile__fill" :style="{ width: row.width }" /></div>
                                            </div>
                                        </div>

                                        <div v-if="cuppingNotes.length" class="pp-cupping-notes">
                                            <div v-for="note in cuppingNotes" :key="note.label" class="pp-cupping-note">
                                                <span class="pp-cupping-note__label"><el-icon :size="12"><ChatLineSquare /></el-icon> {{ note.label }}</span>
                                                <p>{{ note.text }}</p>
                                            </div>
                                        </div>

                                        <div v-if="!cuppingRows.length && !cuppingNotes.length" class="pp-cupping-empty">
                                            <el-icon :size="18"><ChatLineSquare /></el-icon>
                                            <p>A full cupping profile hasn't been recorded for this lot — the quality score above reflects the seller's overall grading.</p>
                                        </div>
                                    </section>

                                    <section class="pp-section">
                                        <h3 class="pp-section-title"><el-icon :size="17"><Shop /></el-icon> Traceability</h3>

                                        <div class="pp-seller">
                                            <div class="pp-seller__avatar" :style="avatarStyle(item.seller_name)">{{ initials(item.seller_name) }}</div>
                                            <div class="pp-seller__info">
                                                <div class="pp-seller__name">{{ item.seller_name || 'Unknown seller' }}<el-icon class="pp-seller__verified"><CircleCheck /></el-icon></div>
                                                <div class="pp-seller__meta">
                                                    Listed {{ formatDate(item.created_at) }}
                                                    <template v-if="item.seller_active_listings">
                                                        · {{ item.seller_active_listings }} active listing{{ item.seller_active_listings === 1 ? '' : 's' }}
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pp-trust-grid">
                                            <el-row :gutter="14">
                                                <el-col :xs="24" :sm="8" class="pp-trust-item">
                                                    <el-icon><Lock /></el-icon>
                                                    <div>
                                                        <div class="pp-trust-item__title">Secure Escrow Payment</div>
                                                        <div class="pp-trust-item__text">Funds are held safely until delivery is confirmed.</div>
                                                    </div>
                                                </el-col>
                                                <el-col :xs="24" :sm="8" class="pp-trust-item">
                                                    <el-icon><CircleCheck /></el-icon>
                                                    <div>
                                                        <div class="pp-trust-item__title">Verified Listing</div>
                                                        <div class="pp-trust-item__text">Lot details are reviewed against seller records.</div>
                                                    </div>
                                                </el-col>
                                                <el-col :xs="24" :sm="8" class="pp-trust-item">
                                                    <el-icon><Medal /></el-icon>
                                                    <div>
                                                        <div class="pp-trust-item__title">Quality Assured</div>
                                                        <div class="pp-trust-item__text">Graded and scored before it reaches the marketplace.</div>
                                                    </div>
                                                </el-col>
                                            </el-row>
                                        </div>

                                        <el-row :gutter="20" class="pp-spec-columns-row">
                                            <el-col :xs="24" :sm="24" :md="12">
                                                <div class="pp-origin-card">
                                                    <div class="pp-origin-card__head">
                                                        <span class="pp-origin-card__icon"><el-icon :size="16"><MapLocation /></el-icon></span>
                                                        <span class="pp-origin-card__label">Origin</span>
                                                    </div>
                                                    <div class="pp-origin-card__value">{{ item.origin || '—' }}</div>
                                                    <div v-if="item.target_market" class="pp-origin-card__sub">Ships to {{ item.target_market }}</div>

                                                    <div class="pp-origin-card__trace" :class="{ 'pp-origin-card__trace--yes': item.is_traceable }">
                                                        <el-icon :size="13"><CircleCheck /></el-icon>
                                                        {{ item.is_traceable ? 'Traceable to lot records' : 'Not yet linked to a tracked lot' }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :xs="24" :sm="24" :md="12">
                                                <div class="pp-cert-card">
                                                    <div class="pp-cert-card__head">
                                                        <span class="pp-origin-card__icon"><el-icon :size="16"><Medal /></el-icon></span>
                                                        <span class="pp-origin-card__label">Certifications</span>
                                                    </div>
                                                    <div v-if="item.badges.length" class="pp-cert-chips">
                                                        <span v-for="b in item.badges" :key="b" class="pp-cert-chip"><el-icon :size="12"><CircleCheck /></el-icon>{{ b }}</span>
                                                    </div>
                                                    <p v-else class="pp-cert-empty">No certifications recorded for this lot.</p>
                                                </div>
                                            </el-col>
                                        </el-row>
                                    </section>
                                </div>
                            </el-card>
                        </el-col>

                        <!-- ── Add to cart area — 30% width ────────────── -->
                        <el-col :xs="24" :sm="24" :md="7">
                            <div class="pp-sidebar">
                                <el-card shadow="never" class="pp-card pp-buy">
                                    <template #header>
                                        <h3 class="pp-card__title"><el-icon :size="15"><Coin /></el-icon> Trade Setup</h3>
                                    </template>

                                    <div class="pp-buy__volume">
                                        <div>
                                            <span class="pp-buy__volume-label">Available Volume</span>
                                            <span class="pp-buy__volume-value">
                                                {{ item.quantity.toLocaleString() }} kg
                                                <span class="pp-stock-pill" :class="`pp-stock-pill--${stockStatus.tone}`">{{ stockStatus.label }}</span>
                                            </span>
                                        </div>
                                        <div class="pp-buy__volume-end">
                                            <span class="pp-buy__volume-label">Quality Score</span>
                                            <span class="pp-buy__volume-value">{{ item.quality_score.toFixed(1) }}</span>
                                        </div>
                                    </div>

                                    <form v-if="item.quantity > 0" class="pp-buy__form" @submit.prevent="addToCart">
                                        <label class="pp-buy__qty-label" for="pp-qty">Quantity (kg)</label>
                                        <div class="pp-buy__qty">
                                            <button type="button" class="pp-qty-btn" :disabled="quantity <= 1" @click="decrementQuantity">
                                                <el-icon><Minus /></el-icon>
                                            </button>
                                            <input
                                                id="pp-qty"
                                                v-model.number="quantity"
                                                type="number"
                                                class="pp-qty-input"
                                                min="1"
                                                :max="maxQuantity"
                                                step="1"
                                                @change="clampQuantity"
                                            >
                                            <button type="button" class="pp-qty-btn" :disabled="quantity >= maxQuantity" @click="incrementQuantity">
                                                <el-icon><Plus /></el-icon>
                                            </button>
                                        </div>
                                        <div class="pp-buy__qty-hint">Max {{ maxQuantity.toLocaleString() }} kg available</div>

                                        <div class="pp-summary">
                                            <div class="pp-summary__row">
                                                <span>Unit price</span>
                                                <span>{{ formatCurrency(item.price_per_kg) }} / kg</span>
                                            </div>
                                            <div class="pp-summary__row">
                                                <span>Quantity</span>
                                                <span>× {{ quantity.toLocaleString() }} kg</span>
                                            </div>
                                            <div class="pp-summary__divider" />
                                            <div class="pp-summary__row pp-summary__row--total">
                                                <span>Total Estimate</span>
                                                <span :key="totalPulseKey" class="pp-summary__total">{{ formatCurrency(subtotal) }}</span>
                                            </div>
                                        </div>

                                        <button type="submit" class="mkt-btn-group__item mkt-btn-group__item--solid pp-buy__btn" :disabled="cartForm.processing">
                                            <el-icon><ShoppingCart /></el-icon>
                                            {{ cartForm.processing ? 'Adding…' : `Add to Cart · ${formatCurrency(subtotal)}` }}
                                        </button>

                                        <div v-if="cartQuantity > 0" class="pp-buy__in-cart">
                                            <el-icon><Check /></el-icon> {{ cartQuantity }} kg already in your cart
                                        </div>

                                        <p class="pp-buy__trust"><el-icon><Lock /></el-icon> Secure escrow settlement</p>
                                    </form>
                                    <p v-else class="man-muted pp-buy__note">
                                        This lot is sold out.
                                    </p>
                                </el-card>

                                <el-card shadow="never" class="pp-card pp-seller-card">
                                    <div class="pp-seller">
                                        <div class="pp-seller__avatar" :style="avatarStyle(item.seller_name)">{{ initials(item.seller_name) }}</div>
                                        <div class="pp-seller__info">
                                            <div class="pp-seller__name">{{ item.seller_name || 'Unknown seller' }}<el-icon class="pp-seller__verified"><CircleCheck /></el-icon></div>
                                            <div class="pp-seller__meta">
                                                Listed {{ formatDate(item.created_at) }}
                                                <template v-if="item.seller_active_listings">
                                                    · {{ item.seller_active_listings }} active listing{{ item.seller_active_listings === 1 ? '' : 's' }}
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </el-card>
                            </div>
                        </el-col>
                    </el-row>
                </div>
            </div>

            <!-- ── Mobile sticky purchase bar — the sidebar buy panel scrolls
                 out of view on small screens, so keep the essentials pinned. -->
            <div v-if="item.quantity > 0" class="pp-sticky-bar">
                <div class="pp-sticky-bar__price">
                    <span class="pp-sticky-bar__price-value">${{ item.price_per_kg.toFixed(2) }}</span>
                    <span class="pp-sticky-bar__price-unit">/ kg</span>
                </div>
                <button type="button" class="mkt-btn-group__item mkt-btn-group__item--solid" @click="focusQuantity">
                    <el-icon><ShoppingCart /></el-icon> Order Now
                </button>
            </div>
        </div>
    </MarketPage>
</template>

<style scoped>
.pp-page {
    /* Tokens copied 1:1 from OrderShowPage.vue's .osh-page — same accent,
       grayscale, font, and page background, so the two pages read as the
       same product rather than two different design systems. */
    --green: #004532;
    --green-dark: #002e20;
    --accent-soft: rgba(0, 69, 50, .08);
    --gold: #c8862a;
    --border: #eef2f0;
    --on-surface: #16181d;
    --on-surface-var: #71717a;
    --surface-low: #f6f6f7;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #fafafa;
    color: var(--on-surface);
    min-height: 100%;
}

.man-muted { color: var(--on-surface-var); font-size: .8125rem; }

/* ── Body ─────────────────────────────────────────────────────────────── */
.mkt-body { padding: 1.25rem 0 3rem; }

.mkt-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 6px; }

/* ── Buttons — copied 1:1 from .osh-btn / .osh-btn--outline / .osh-btn--primary. */
.mkt-btn-group__item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 8px;
    font-size: .8125rem;
    font-weight: 700;
    padding: 8px 16px;
    cursor: pointer;
    border: 1px solid var(--border);
    background: #fff;
    color: var(--on-surface);
    text-decoration: none;
    white-space: nowrap;
    transition: opacity .15s ease, background .15s ease, border-color .15s ease, color .15s ease;
}
.mkt-btn-group__item:hover { background: var(--surface-low); }
.mkt-btn-group__item--solid { background: var(--green); color: #fff; border-color: transparent; }
.mkt-btn-group__item--solid:hover { background: var(--green-dark); color: #fff; }
.mkt-btn-group__item--solid:disabled { opacity: .6; cursor: default; background: var(--green); }

.man-content { max-width: 1160px; margin: 0 auto; padding: 0 1.5rem; }

/* ── Layout ──────────────────────────────────────────────────────────── */
.pp-sidebar { display: flex; flex-direction: column; gap: 14px; position: sticky; top: 5.5rem; }

/* ── Shared card — copied 1:1 from .osh-card / .osh-card__title. el-card
   root gets these classes directly. ─────────────────────────────────── */
.pp-card {
    border: 1px solid var(--border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03), 0 12px 28px -18px rgba(15, 23, 42, .14);
    overflow: hidden;
}
.pp-card :deep(.el-card__body) { padding: 22px 24px; }
.pp-card :deep(.el-card__header) { padding: 22px 24px; border-bottom: 1px solid var(--border); }
.pp-card__title { display: flex; align-items: center; gap: 7px; font-size: 1.0625rem; font-weight: 800; letter-spacing: -.01em; margin: 0; color: var(--on-surface); }

/* ── Hero — media + info side by side ───────────────────────────────────── */
.pp-hero { display: flex; gap: 2rem; align-items: center; padding: 1.75rem 1.5rem; border-bottom: 1px solid var(--border); }
.pp-hero__media { flex: 0 0 calc(50% - 1rem); min-width: 0; }
.pp-hero__info { flex: 0 0 calc(50% - 1rem); min-width: 0; display: flex; flex-direction: column; }

.pp-media { position: relative; width: 100%; aspect-ratio: 4 / 3; border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f1e6d8; border: 1px solid #e6d5bf; }
.pp-media img { width: 100%; height: 100%; object-fit: cover; }
.pp-media__icon { width: 72px; height: 72px; }
.pp-media__ribbon { position: absolute; top: 12px; right: 12px; display: inline-flex; align-items: center; gap: 5px; background: rgba(17, 24, 39, .82); color: #fbbf24; font-size: .6875rem; font-weight: 700; padding: 5px 11px; border-radius: 999px; backdrop-filter: blur(4px); }

.pp-tags { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-bottom: .75rem; }

/* ── Badges — copied 1:1 from .osh-badge: a small semantic dot + quiet
   gray text, never a filled color pill. Color is reserved for meaning. */
.mkt-badge { display: inline-flex; align-items: center; gap: 6px; font-size: .75rem; font-weight: 600; color: var(--on-surface-var); white-space: nowrap; }
.mkt-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; background: var(--dot, #9ca3af); }
.mkt-badge--green { --dot: #16a34a; color: var(--on-surface); }
.mkt-badge--muted { --dot: #9ca3af; }

/* !important overrides the app-wide "\:where(...) heading/p margin-block:
   0 !important" reset (see resources/css/*.css) for this component's own
   deliberate spacing. */
.pp-hero__title { font-size: 1.625rem; font-weight: 700; letter-spacing: -.02em; margin: 0 0 .75rem !important; color: var(--on-surface); line-height: 1.25; }

.pp-hero__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; font-size: .8125rem; color: var(--on-surface-var); margin-bottom: 1.25rem; }
.pp-hero__meta-item { display: inline-flex; align-items: center; gap: 5px; }
.pp-hero__meta-item :deep(.el-icon) { font-size: 14px; }
.pp-hero__meta-item--green { color: #16a34a; }
.pp-hero__meta-item--amber { color: #f59e0b; }
.pp-hero__meta-item--red { color: #ef4444; }
.pp-hero__meta-dot { width: 3px; height: 3px; border-radius: 50%; background: #d4d4d8; }

.pp-hero__price-block { display: flex; flex-direction: column; gap: 8px; }
.pp-hero__price-row { display: flex; align-items: baseline; gap: 6px; }
.pp-hero__price { font-family: 'IBM Plex Mono', monospace; font-size: 1.875rem; font-weight: 800; letter-spacing: -.01em; color: var(--on-surface); }
.pp-hero__price-unit { font-size: .875rem; color: var(--on-surface-var); }
.pp-hero__price-tags { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
.pp-hero__spot { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; }

/* Quiet indicator, no filled surface — matches osh's "color for meaning
   only" rule; the up/down icon already carries the direction. */
.pp-delta { display: inline-flex; align-items: center; gap: 3px; font-size: .75rem; font-weight: 700; font-family: 'IBM Plex Mono', monospace; }
.pp-delta--down { color: #16a34a; }
.pp-delta--up { color: #ef4444; }

/* ── Lot content — Overview, Quality & Traceability stacked in one card,
   each a plain section separated by a hairline instead of tabs. ───────── */
.pp-sections { padding: 2rem; }
.pp-section + .pp-section { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border); }

.pp-section-title { display: flex; align-items: center; gap: 7px; font-size: 1.0625rem; font-weight: 800; letter-spacing: -.01em; color: var(--on-surface); margin: 0 0 1.5rem !important; }

/* ── Notes card — copied 1:1 from .osh-notes-card: quiet surface, accent
   left border, and a large low-opacity icon watermark. */
.pp-notes-card { position: relative; width: 100%; background: var(--surface-low); border-left: 3px solid var(--green); border-radius: 10px; padding: 1.25rem 3rem 1.25rem 1.375rem; overflow: hidden; margin-bottom: 2.5rem; }
.pp-notes-card__icon { position: absolute; top: 10px; right: 12px; color: rgba(0, 69, 50, .1); }
.pp-notes-card__text { position: relative; font-size: .875rem; color: var(--on-surface); line-height: 1.7; margin: 0; white-space: pre-line; }
.pp-notes-card__text--empty { color: var(--on-surface-var); font-style: italic; }

/* ── Specifications — copied 1:1 from .osh-specs / .osh-spec: a tile
   grid instead of a bordered row list. ───────────────────────────────── */
.pp-spec-col__head { display: flex; align-items: center; gap: 6px; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin: 0 0 1rem !important; padding-bottom: .625rem; border-bottom: 1px solid var(--border); }
.pp-spec-col__head :deep(.el-icon) { color: var(--green); flex-shrink: 0; }
.pp-spec-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 14px; margin: 0; }
.pp-spec-row { display: flex; flex-direction: column; gap: 5px; background: var(--surface-low); border-radius: 10px; padding: 12px 14px; }
.pp-spec-row dt { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin: 0; }
.pp-spec-row dd { font-size: .875rem; font-weight: 700; color: var(--on-surface); margin: 0; }
.pp-spec-highlight { display: inline-block; background: var(--accent-soft); color: var(--green); font-weight: 800; padding: 2px 8px; border-radius: 6px; }

/* Give the two spec columns breathing room from each other and from the
   next section when they stack on narrow widths. */
.pp-spec-columns-row { row-gap: 2rem; }

/* ── Quality & cupping ───────────────────────────────────────────────── */
.pp-quality-hero { display: flex; align-items: center; gap: 1.75rem; margin-bottom: 1.75rem; }

.pp-quality-ring { position: relative; width: 104px; height: 104px; flex-shrink: 0; }
.pp-quality-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.pp-quality-ring__track { fill: none; stroke: var(--surface-low); stroke-width: 8; }
.pp-quality-ring__fill { fill: none; stroke-width: 8; stroke-linecap: round; transition: stroke-dashoffset .4s ease; }
.pp-quality-ring__fill--green { stroke: #16a34a; }
.pp-quality-ring__fill--amber { stroke: #f59e0b; }
.pp-quality-ring__fill--red { stroke: #ef4444; }
.pp-quality-ring__value { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'IBM Plex Mono', monospace; font-size: 1.375rem; font-weight: 800; color: var(--on-surface); line-height: 1; }
.pp-quality-ring__value small { font-family: var(--font-sans, inherit); font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); margin-top: 3px; }
.pp-quality-ring__value--green { color: #16a34a; }
.pp-quality-ring__value--amber { color: #f59e0b; }
.pp-quality-ring__value--red { color: #ef4444; }

.pp-quality-hero__info { display: flex; flex-direction: column; gap: 8px; }
.pp-quality-hero__badge { display: inline-flex; align-items: center; gap: 5px; align-self: flex-start; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: 4px 10px; border-radius: 999px; }
.pp-quality-hero__badge--green { background: rgba(22, 163, 74, .12); color: #15803d; }
.pp-quality-hero__badge--amber { background: rgba(245, 158, 11, .14); color: #b45309; }
.pp-quality-hero__badge--red { background: rgba(239, 68, 68, .12); color: #b91c1c; }
.pp-quality-hero__text { font-size: .8125rem; color: var(--on-surface-var); line-height: 1.6; margin: 0 !important; max-width: 42ch; }

.pp-cupping-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 14px; margin-top: 1.75rem; padding-top: 1.75rem; border-top: 1px solid var(--surface-low); }
.pp-cupping-tile { display: flex; flex-direction: column; gap: 8px; background: var(--surface-low); border-radius: 10px; padding: 14px 16px; }
.pp-cupping-tile__label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); }
.pp-cupping-tile__value { font-family: 'IBM Plex Mono', monospace; font-size: 1.25rem; font-weight: 800; color: var(--on-surface); }
.pp-cupping-tile__track { height: 5px; border-radius: 999px; background: var(--border); overflow: hidden; }
.pp-cupping-tile__fill { height: 100%; border-radius: 999px; background: var(--gold); }

.pp-cupping-notes { display: flex; flex-direction: column; gap: 16px; margin-top: 1.75rem; padding-top: 1.75rem; border-top: 1px solid var(--surface-low); }
.pp-cupping-note { padding-left: 1rem; border-left: 3px solid var(--surface-low); }
.pp-cupping-note__label { display: inline-flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); }
.pp-cupping-note p { font-size: .8125rem; color: var(--on-surface); margin: 5px 0 0 !important; line-height: 1.6; font-style: italic; }

.pp-cupping-empty { display: flex; align-items: flex-start; gap: 10px; margin-top: 1.75rem; padding-top: 1.75rem; border-top: 1px solid var(--surface-low); color: var(--on-surface-var); }
.pp-cupping-empty :deep(.el-icon) { flex-shrink: 0; margin-top: 1px; color: #9ca3af; }
.pp-cupping-empty p { font-size: .8125rem; line-height: 1.6; margin: 0 !important; max-width: 52ch; }

/* ── Traceability ─────────────────────────────────────────────────────── */
.pp-seller { display: flex; align-items: center; gap: 14px; }
/* Sizing copied from .osh-avatar--lg; background/color come from the same
   deterministic avatarStyle() palette used on the order page. */
.pp-seller__avatar { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .875rem; flex-shrink: 0; }
.pp-seller__name { display: inline-flex; align-items: center; gap: 5px; font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.pp-seller__verified { color: #16a34a; font-size: 14px; }
.pp-seller__meta { font-size: .75rem; color: var(--on-surface-var); margin-top: 3px; }

.pp-trust-grid { margin: 1.75rem 0; padding: 1.75rem 0; border-top: 1px solid var(--surface-low); border-bottom: 1px solid var(--surface-low); }
.pp-trust-item { display: flex; align-items: flex-start; gap: 12px; }
.pp-trust-item :deep(.el-icon) { flex-shrink: 0; font-size: 17px; color: var(--green); margin-top: 1px; }
.pp-trust-item__title { font-size: .75rem; font-weight: 700; color: var(--on-surface); }
.pp-trust-item__text { font-size: .6875rem; color: var(--on-surface-var); margin-top: 4px; line-height: 1.55; }

/* ── Origin & Certifications — matched pair of cards instead of a single
   generic gray box, so provenance and compliance read as distinct,
   deliberately designed facts rather than two spec-list columns. ──────── */
.pp-origin-card,
.pp-cert-card {
    height: 100%;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
}

.pp-origin-card__head,
.pp-cert-card__head { display: flex; align-items: center; gap: 8px; margin-bottom: 1rem; }
.pp-origin-card__icon { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 8px; background: var(--accent-soft); color: var(--green); flex-shrink: 0; }
.pp-origin-card__label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); }

.pp-origin-card__value { font-size: 1.375rem; font-weight: 700; letter-spacing: -.01em; color: var(--on-surface); line-height: 1.25; }
.pp-origin-card__sub { font-size: .8125rem; color: var(--on-surface-var); margin-top: 4px; }

.pp-origin-card__trace { display: inline-flex; align-items: center; gap: 6px; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--surface-low); font-size: .75rem; font-weight: 600; color: var(--on-surface-var); }
.pp-origin-card__trace :deep(.el-icon) { color: #9ca3af; }
.pp-origin-card__trace--yes { color: var(--on-surface); }
.pp-origin-card__trace--yes :deep(.el-icon) { color: #16a34a; }

.pp-cert-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.pp-cert-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface-low);
    font-size: .75rem;
    font-weight: 700;
    color: var(--on-surface);
}
.pp-cert-chip :deep(.el-icon) { color: #16a34a; flex-shrink: 0; }
.pp-cert-empty { font-size: .8125rem; color: var(--on-surface-var); margin: 0 !important; }

/* ── Purchase panel ──────────────────────────────────────────────────── */
.pp-buy__volume { display: flex; align-items: flex-start; justify-content: space-between; background: var(--surface-low); border: 1px solid var(--border); border-radius: 10px; padding: .75rem 1rem; margin-bottom: 1.25rem; }
.pp-buy__volume > div { display: flex; flex-direction: column; gap: 3px; }
.pp-buy__volume-end { align-items: flex-end; text-align: right; }
.pp-buy__volume-label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); }
.pp-buy__volume-value { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; font-family: 'IBM Plex Mono', monospace; font-size: .875rem; font-weight: 700; color: var(--on-surface); white-space: nowrap; }
.pp-buy__volume-end .pp-buy__volume-value { align-items: flex-end; }

/* Same quiet dot-badge as .mkt-badge — copied 1:1 from .osh-badge's exact
   per-state dot colors instead of a filled pill. */
.pp-stock-pill { display: inline-flex; align-items: center; gap: 6px; font-family: var(--font-sans, inherit); font-size: .6875rem; font-weight: 600; color: var(--on-surface-var); white-space: nowrap; }
.pp-stock-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; background: var(--dot, #9ca3af); }
.pp-stock-pill--green { --dot: #16a34a; color: var(--on-surface); }
.pp-stock-pill--amber { --dot: #f59e0b; color: var(--on-surface); }
.pp-stock-pill--red { --dot: #ef4444; color: var(--on-surface); }

.pp-buy__form { display: flex; flex-direction: column; }
.pp-buy__qty-label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin-bottom: 6px; }

.pp-buy__qty { display: flex; align-items: center; gap: 10px; border: 1px solid var(--border); border-radius: 8px; padding: 6px; }
.pp-qty-btn { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; flex-shrink: 0; border-radius: 6px; border: none; background: var(--surface-low); color: var(--on-surface); cursor: pointer; transition: background .12s ease; }
.pp-qty-btn:hover:not(:disabled) { background: #eef2f1; }
.pp-qty-btn:disabled { opacity: .4; cursor: default; }

.pp-qty-input { width: 100%; min-width: 0; border: none; background: transparent; text-align: center; font-family: 'IBM Plex Mono', ui-monospace, monospace; font-weight: 700; font-size: .9375rem; color: var(--on-surface); }
.pp-qty-input:focus { outline: none; }
.pp-qty-input::-webkit-inner-spin-button,
.pp-qty-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.pp-qty-input[type='number'] { appearance: textfield; -moz-appearance: textfield; }

.pp-buy__qty-hint { font-size: .6875rem; color: var(--on-surface-var); margin: 6px 0 0; }

/* ── Live order summary ─────────────────────────────────────────────── */
.pp-summary { border-top: 1px solid var(--border); padding-top: 1rem; margin: 1.25rem 0; }
.pp-summary__row { display: flex; align-items: center; justify-content: space-between; font-size: .875rem; color: var(--on-surface-var); padding: 4px 0; font-variant-numeric: tabular-nums; }
.pp-summary__divider { display: none; }
.pp-summary__row--total { padding-top: 8px; }
.pp-summary__row--total span:first-child { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.pp-summary__total { font-family: 'IBM Plex Mono', ui-monospace, monospace; font-size: 1.1875rem; font-weight: 800; color: var(--green); letter-spacing: -.01em; animation: pp-total-pulse .4s ease; }

@keyframes pp-total-pulse {
    0% { transform: scale(1.12); color: var(--gold); }
    100% { transform: scale(1); color: var(--green); }
}

.pp-buy__btn { width: 100%; justify-content: center; box-shadow: 0 6px 16px -6px rgba(0, 69, 50, .45); transition: background .15s ease, transform .12s ease, box-shadow .15s ease; }
.pp-buy__btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 10px 20px -6px rgba(0, 69, 50, .5); }
.pp-buy__btn:active:not(:disabled) { transform: translateY(0); }

.pp-buy__in-cart { display: flex; align-items: center; gap: 6px; margin-top: 12px; font-size: .75rem; font-weight: 600; color: #16a34a; }
.pp-buy__note { margin: 0; }

.pp-buy__trust { display: flex; align-items: center; justify-content: center; gap: 5px; margin: 14px 0 0 !important; font-size: .75rem; color: var(--on-surface-var); }
.pp-buy__trust :deep(.el-icon) { font-size: 13px; color: var(--green); }

/* ── Mobile sticky purchase bar ──────────────────────────────────────── */
.pp-sticky-bar { display: none; }

@media (max-width: 991.98px) {
    .pp-sidebar { position: static; margin-top: 1.5rem; }
    .pp-hero { flex-direction: column; }
    .pp-hero__media { flex: 0 0 auto; width: 100%; max-width: 360px; }

    .mkt-body { padding-bottom: 6rem; }

    .pp-sticky-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 40;
        background: #fff;
        border-top: 1px solid var(--border);
        padding: .75rem 5.5rem .75rem 1.25rem;
        padding-bottom: calc(.75rem + env(safe-area-inset-bottom));
        box-shadow: 0 -4px 16px rgba(15, 23, 42, .08);
    }
    .pp-sticky-bar__price { display: flex; align-items: baseline; gap: 4px; }
    .pp-sticky-bar__price-value { font-family: 'IBM Plex Mono', monospace; font-size: 1.25rem; font-weight: 800; color: var(--on-surface); }
    .pp-sticky-bar__price-unit { font-size: .75rem; color: var(--on-surface-var); }
    .pp-sticky-bar .mkt-btn-group__item { padding: 10px 20px; }
}

@media (max-width: 767.98px) {




    .man-content { padding: 0 1.25rem; }
}
</style>
