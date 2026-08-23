<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Coffee, Star, Box, Search, Sort, ShoppingCart, Plus,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

const props = defineProps({
    markets: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    exchangeRates: { type: Array, default: () => [] },
});

function goToListing(row) {
    router.visit(route('market.show', row.id));
}

const searchQuery = ref('');

/* ══════════════════════════════════════════════════════════════════════
   Live market listings — real data
   ══════════════════════════════════════════════════════════════════════ */

/* A listing is flagged "Low Stock" the same honest way "Premium Lot" is
   flagged by score — a plain threshold on a real numeric field, not a
   fabricated tag. Any other real tag (e.g. "Direct Trade", "Organic")
   comes straight from the listing's own `badges` array when present. */
const LOW_STOCK_THRESHOLD_KG = 20;

const listings = computed(() => props.markets.map((m) => {
    const quantity = Number(m.quantity || 0);
    const badges = m.badges || [];
    const primaryBadge = quantity > 0 && quantity < LOW_STOCK_THRESHOLD_KG
        ? { label: 'Low Stock', tone: 'red' }
        : (badges[0] ? { label: badges[0], tone: badges[0].toLowerCase() === 'organic' ? 'dark' : 'green' } : null);

    return {
        id: m.id,
        lot_code: m.lot_code,
        name: m.name || m.lot_code,
        origin: m.origin || '—',
        type: m.type,
        process: m.process,
        qualityScore: Number(m.quality_score || 0),
        quantity,
        pricePerKg: Number(m.price_per_kg || 0),
        badge: primaryBadge,
        image: m.image || null,
    };
}));

const filteredListings = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return listings.value;
    return listings.value.filter((l) => `${l.name} ${l.lot_code} ${l.origin} ${l.type}`.toLowerCase().includes(q));
});

/* ── Sort ─────────────────────────────────────────────────────────────── */
const sortDescending = ref(true);
const sortedListings = computed(() => [...filteredListings.value].sort((a, b) => (
    sortDescending.value ? b.qualityScore - a.qualityScore : a.qualityScore - b.qualityScore
)));

function toggleSort() {
    sortDescending.value = !sortDescending.value;
}

/* ── Pagination ───────────────────────────────────────────────────────── */
const currentPage = ref(1);
const pageSize = ref(24);

const pagedListings = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return sortedListings.value.slice(start, start + pageSize.value);
});

watch(filteredListings, () => { currentPage.value = 1; });

/* ── Add to cart ──────────────────────────────────────────────────────── */
const addingId = ref(null);

function addToCart(row) {
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
</script>

<template>
    <DesignPreviewLayout title="Coffee Market">
        <Head title="Coffee Market" />

        <div class="mkt-page">
            <div class="mktl-topbar mt-4">
                <div class="mktl-topbar__text">
                    <h1 class="mktl-topbar__title">Coffee Marketplace</h1>
                    <p class="mktl-topbar__subtitle">Discover {{ listings.length }} premium lot{{ listings.length === 1 ? '' : 's' }} from around the world.</p>
                </div>
                <div class="mktl-topbar__actions">
                    <label class="mktl-hero__search">
                        <el-icon :size="15"><Search /></el-icon>
                        <input v-model="searchQuery" type="text" placeholder="Search origin, lot, or variety…">
                    </label>
                    <button type="button" class="mktl-sort" @click="toggleSort">
                        <el-icon :size="16"><Sort /></el-icon> Sort: Score ({{ sortDescending ? 'High to Low' : 'Low to High' }})
                    </button>
                    <Link :href="route('market.offer')" class="mktl-create">
                        <el-icon :size="16"><Plus /></el-icon> Create Offer
                    </Link>
                </div>
            </div>

            <div class="mktl-body">
                <div v-if="pagedListings.length" class="mktl-grid">
                    <article
                        v-for="row in pagedListings"
                        :key="row.id"
                        class="mktl-card"
                        @click="goToListing(row)"
                    >
                        <div class="mktl-card__media">
                            <img :src="row.image ? `/storage/${row.image}` : '/images/coffee_image.jpg'" :alt="row.name">
                            <span v-if="row.badge" class="mktl-card__badge" :class="`mktl-card__badge--${row.badge.tone}`">{{ row.badge.label }}</span>
                        </div>

                        <div class="mktl-card__body">
                            <div class="mktl-card__meta">
                                <span class="mktl-card__origin">{{ row.origin }}</span>
                                <span class="mktl-card__score"><el-icon :size="12"><Star /></el-icon>{{ row.qualityScore.toFixed(1) }}</span>
                            </div>

                            <h3 class="mktl-card__title">{{ row.name }}</h3>

                            <div class="mktl-card__specs">
                                <div class="mktl-card__spec">
                                    <span>Process</span>
                                    <strong>{{ row.process || '—' }}</strong>
                                </div>
                                <div class="mktl-card__spec">
                                    <span>Variety</span>
                                    <strong>{{ row.type || '—' }}</strong>
                                </div>
                            </div>

                            <div class="mktl-card__footer">
                                <div class="mktl-card__price-block">
                                    <span class="mktl-card__price">${{ row.pricePerKg.toFixed(2) }}</span>
                                    <span class="mktl-card__price-unit">per kg</span>
                                </div>
                                <button
                                    type="button"
                                    class="mktl-card__cart"
                                    :disabled="addingId === row.id"
                                    title="Add to cart"
                                    @click.stop="addToCart(row)"
                                >
                                    <el-icon :size="18"><ShoppingCart /></el-icon>
                                </button>
                            </div>

                            <div class="mktl-card__stock"><el-icon :size="12"><Box /></el-icon>{{ row.quantity.toLocaleString() }} kg avail.</div>
                        </div>
                    </article>
                </div>

                <div v-else class="mktl-empty">
                    <el-icon :size="28"><Coffee /></el-icon>
                    <p>{{ searchQuery ? `No lots match "${searchQuery}".` : 'No lots available right now.' }}</p>
                    <button v-if="searchQuery" type="button" class="mktl-clear mktl-clear--pill" @click="searchQuery = ''">Clear search</button>
                </div>

                <div v-if="filteredListings.length > pageSize" class="mktl-pagination">
                    <el-pagination
                        v-model:current-page="currentPage"
                        v-model:page-size="pageSize"
                        :total="filteredListings.length"
                        :page-sizes="[24, 48, 96]"
                        layout="total, sizes, prev, pager, next"
                        background
                    />
                </div>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Bean Origin design system — literal hex from the Stitch export, not
   tailwind.config.js: this app's shared config already owns an old
   dark-theme palette under similarly-named tokens (see
   feedback_stitch_mockup_porting memory).
   Interactive "items" (cards, inputs, buttons, pagination) are
   borderless — separation comes from background fill and soft ambient
   shadow rather than hairline outlines. The hero's own bottom edge is
   the one deliberate hairline divider, kept subtle (low-opacity, not
   the solid --border token) since it's structural, not an "item". ──── */
.mkt-page {
    --green: #271310;          /* primary — Deep Roast */
    --green-dark: #1a0d0b;
    --border: #d3c3c0;         /* outline-variant — still used for hairline dividers */
    --on-surface: #1a1c1c;
    --on-surface-var: #504442; /* on-surface-variant */
    --surface-low: #f3f3f3;    /* surface-container-low */
    --shadow-sm: 0 1px 2px rgba(39, 19, 16, .06), 0 1px 1px rgba(39, 19, 16, .04);
    --shadow-md: 0 10px 24px -12px rgba(39, 19, 16, .18);
    font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    background: #f9f9f9;
    color: var(--on-surface);
    min-height: 100%;
    /* DesignPreviewLayout's .dp-main carries its own 48px top padding
       (shared by every page it wraps) — pulled back up here so the hero
       sits flush under the header instead of leaving a dead gap, same
       fix applied to GeneralDashboard's .cp-page. */
    margin-top: -48px;
}

.mktl-body { padding: .75rem 0 2rem; }

/* ── Topbar ───────────────────────────────────────────────────────────── */
.mktl-topbar {
    display: flex; flex-direction: column; gap: 14px;
    margin-bottom: 16px; padding-bottom: 16px;
}
.mktl-topbar__text { min-width: 0; }
.mktl-topbar__title { font-size: 1.5rem; line-height: 1.9rem; letter-spacing: -0.015em; font-weight: 800; color: var(--green); margin: 0 0 6px; }
.mktl-topbar__subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 500; color: var(--on-surface-var); margin: 0; }
.mktl-topbar__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }

.mktl-hero__search {
    display: flex; align-items: center; gap: 9px; flex-shrink: 0;
    width: 100%; height: 42px; padding: 0 15px; border-radius: 999px;
    background: var(--surface-low); color: var(--on-surface-var);
    transition: box-shadow .15s ease;
}
.mktl-hero__search:focus-within { box-shadow: 0 0 0 2px var(--green-dark) inset; color: var(--on-surface); }
.mktl-hero__search input {
    flex: 1; min-width: 0; height: 100%; border: none; background: none;
    font: inherit; font-size: .8125rem; color: var(--on-surface);
}
.mktl-hero__search input:focus,
.mktl-hero__search input:focus-visible {
    outline: none !important;
    box-shadow: none !important;
}
.mktl-hero__search input::placeholder { color: var(--on-surface-var); }

.mktl-sort, .mktl-create {
    display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0;
    height: 42px; padding: 0 18px; border-radius: 999px; border: none;
    font-size: .8125rem; font-weight: 700; white-space: nowrap; cursor: pointer;
    text-decoration: none; transition: background .15s ease, transform .15s ease, box-shadow .15s ease;
}
.mktl-sort { background: var(--surface-low); color: var(--on-surface); }
.mktl-sort:hover { background: #ece4e2; }
.mktl-create { background: var(--green); color: #fff; box-shadow: 0 6px 16px -8px rgba(39, 19, 16, .4); }
.mktl-create:hover { background: var(--green-dark); transform: translateY(-1px); }

@media (min-width: 768px) {
    .mktl-topbar { flex-direction: row; align-items: flex-end; justify-content: space-between; gap: 32px; }
    .mktl-hero__search { width: 220px; }
}

.mktl-clear { display: inline-flex; align-items: center; gap: 4px; border: none; background: none; color: var(--green); font-size: .6875rem; font-weight: 700; cursor: pointer; padding: 0; }
.mktl-clear:hover { text-decoration: underline; }
.mktl-clear--pill { border: none; background: var(--surface-low); border-radius: 8px; padding: 7px 14px; margin-top: 4px; }

/* ── Product grid ─────────────────────────────────────────────────────── */
.mktl-grid { display: grid; grid-template-columns: 1fr; gap: 24px; margin-top: 8px; }

@media (min-width: 640px) {
    .mktl-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .mktl-grid { grid-template-columns: repeat(4, 1fr); }
}

.mktl-card {
    background: #fff;
    border-radius: 16px;
    padding: 16px;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .15s ease, transform .12s ease;
    display: flex;
    flex-direction: column;
}
.mktl-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

.mktl-card__media {
    position: relative; width: 100%; aspect-ratio: 3 / 4; background: var(--surface-low);
    border-radius: 12px; margin-bottom: 14px; overflow: hidden;
}
.mktl-card__media img { width: 100%; height: 100%; object-fit: cover; mix-blend-mode: multiply; transition: transform .5s ease; }
.mktl-card:hover .mktl-card__media img { transform: scale(1.05); }

.mktl-card__badge { position: absolute; top: 12px; right: 12px; font-size: .6875rem; font-weight: 700; padding: 4px 11px; border-radius: 999px; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
.mktl-card__badge--green { background: #a0f399; color: #217128; }
.mktl-card__badge--dark { background: #2e2c2c; color: #979393; }
.mktl-card__badge--red { background: #ffdad6; color: #93000a; }

.mktl-card__body { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.mktl-card__meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.mktl-card__origin { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mktl-card__score {
    display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;
    background: var(--surface-low); color: var(--on-surface);
    font-size: .75rem; font-weight: 700; padding: 2px 8px; border-radius: 6px;
}
.mktl-card__score .el-icon { color: var(--green); }

.mktl-card__title {
    font-size: 1rem; font-weight: 700; color: var(--on-surface);
    letter-spacing: -0.005em; line-height: 1.375rem; margin: 0 !important;
    display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    min-height: 2.75rem;
}

.mktl-card__specs { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin: 2px 0 4px; }
.mktl-card__spec { background: var(--surface-low); border-radius: 9px; padding: 7px 10px; min-width: 0; }
.mktl-card__spec span { display: block; font-size: .5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin-bottom: 2px; }
.mktl-card__spec strong { display: block; font-size: .8125rem; font-weight: 600; color: var(--on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.mktl-card__footer {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    margin-top: auto; padding-top: 12px; border-top: 1px solid var(--surface-low);
}
.mktl-card__price-block { display: flex; flex-direction: column; line-height: 1.15; }
.mktl-card__price { font-size: 1.375rem; font-weight: 800; color: var(--green); letter-spacing: -0.01em; }
.mktl-card__price-unit { font-size: .6875rem; color: var(--on-surface-var); }

.mktl-card__cart {
    display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    width: 44px; height: 44px; border-radius: 999px; border: none;
    background: var(--surface-low); color: var(--on-surface); cursor: pointer;
    transition: background .15s ease, color .15s ease;
}
.mktl-card:hover .mktl-card__cart { background: var(--green); color: #fff; }
.mktl-card__cart:disabled { opacity: .6; cursor: default; }

.mktl-card__stock { display: inline-flex; align-items: center; gap: 4px; font-size: .6875rem; color: var(--on-surface-var); white-space: nowrap; margin-top: 8px; }

/* ── Empty state ──────────────────────────────────────────────────────── */
.mktl-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 4rem 1rem; color: var(--on-surface-var); background: #fff; border-radius: 14px; box-shadow: var(--shadow-sm); }
.mktl-empty p { margin: 0 !important; font-size: .875rem; }

.mktl-pagination { margin-top: 1.25rem; padding: 1rem 1.25rem; background: #fff; border-radius: 12px; box-shadow: var(--shadow-sm); }
.mktl-pagination :deep(.el-pagination) { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; width: 100%; font-family: inherit; }
.mktl-pagination :deep(.el-pagination__total) { margin-right: auto; font-size: .8125rem; font-weight: 600; color: var(--on-surface-var); }
.mktl-pagination :deep(.el-select__wrapper) { border-radius: 8px; box-shadow: none; background: var(--surface-low); min-height: 32px; font-size: .75rem; }
.mktl-pagination :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 2px var(--green-dark) inset; }
.mktl-pagination :deep(.btn-prev),
.mktl-pagination :deep(.btn-next) { width: 32px; height: 32px; border-radius: 9px; background: var(--surface-low); border: none; color: var(--on-surface-var); transition: all .15s ease; }
.mktl-pagination :deep(.btn-prev:hover:not(:disabled)),
.mktl-pagination :deep(.btn-next:hover:not(:disabled)) { color: var(--green); background: #ece4e2; }
.mktl-pagination :deep(.el-pager) { display: flex; align-items: center; gap: 4px; }
.mktl-pagination :deep(.el-pager li) { min-width: 32px; height: 32px; border-radius: 9px; background: var(--surface-low); border: none; color: var(--on-surface); font-size: .8125rem; font-weight: 600; transition: all .15s ease; }
.mktl-pagination :deep(.el-pager li.is-active) { background: var(--green); color: #fff; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .mktl-body { padding: .75rem 0 1.5rem; }
    .mktl-topbar__title { font-size: 1.25rem; line-height: 1.6rem; }
    .mktl-topbar__actions { flex-direction: column; align-items: stretch; }
    .mktl-hero__search { width: 100%; }
    .mktl-grid { gap: 20px; }
}
</style>
