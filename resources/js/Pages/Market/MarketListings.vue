<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Location, Coffee, Star, Box, CircleCheck, Search,
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
const resolveDemandTone = (demand) => {
    const d = (demand ?? '').toLowerCase();
    if (d === 'very high') return 'primary';
    if (d === 'high') return 'success';
    if (d === 'stable') return 'warning';
    if (d === 'low') return 'danger';
    return 'info';
};

const demandBadgeTone = {
    primary: 'green-solid',
    success: 'green',
    warning: 'amber',
    danger: 'red',
    info: 'muted',
};

const demandBadgeClass = (demandTone) => `mkt-badge--${demandBadgeTone[demandTone] ?? 'muted'}`;

const listings = computed(() => props.markets.map((m) => ({
    id: m.id,
    lot_code: m.lot_code,
    name: m.name || m.lot_code,
    origin: m.origin || '—',
    type: m.type,
    process: m.process,
    qualityScore: Number(m.quality_score || 0),
    quantity: Number(m.quantity || 0),
    pricePerKg: Number(m.price_per_kg || 0),
    demand: m.demand || 'Active',
    demandTone: resolveDemandTone(m.demand),
    badges: m.badges || [],
    image: m.image || null,
})));

const filteredListings = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return listings.value;
    return listings.value.filter((l) => `${l.name} ${l.lot_code} ${l.origin} ${l.type}`.toLowerCase().includes(q));
});

/* ── Pagination ───────────────────────────────────────────────────────── */
const currentPage = ref(1);
const pageSize = ref(24);

const pagedListings = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return filteredListings.value.slice(start, start + pageSize.value);
});

watch(filteredListings, () => { currentPage.value = 1; });
</script>

<template>
    <DesignPreviewLayout title="Coffee Market">
        <Head title="Coffee Market" />

        <div class="mkt-page">
            <div class="mktl-body">
                <div class="mktl-hero">
                    <div class="mktl-hero__text">
                        <p class="mktl-hero__eyebrow">Coffee Trading Platform</p>
                        <h1 class="mktl-hero__title">Marketplace</h1>
                        <p class="mktl-hero__subtitle">Discover exceptional micro-lots, direct trade staples, and rare varietals sourced from the world's most dedicated producers.</p>
                    </div>
                    <label class="mktl-hero__search">
                        <el-icon :size="16"><Search /></el-icon>
                        <input v-model="searchQuery" type="text" placeholder="Search origin, lot, or variety…">
                    </label>
                </div>

                <div v-if="pagedListings.length" class="mktl-grid">
                    <article
                        v-for="row in pagedListings"
                        :key="row.id"
                        class="mktl-card"
                        @click="goToListing(row)"
                    >
                        <div class="mktl-card__media">
                            <img :src="row.image ? `/storage/${row.image}` : '/images/coffee_image.jpg'" :alt="row.name">
                            <span v-if="row.qualityScore >= 85" class="mktl-card__ribbon">Premium Lot</span>
                            <span class="mktl-card__score"><el-icon :size="14"><Star /></el-icon>{{ row.qualityScore.toFixed(1) }}</span>
                            <span class="mktl-card__demand mkt-badge" :class="demandBadgeClass(row.demandTone)">{{ row.demand }}</span>
                        </div>

                        <div class="mktl-card__body">
                            <div class="mktl-card__top">
                                <h3 class="mktl-card__title">{{ row.name }}</h3>
                                <div class="mktl-card__price-block">
                                    <span class="mktl-card__price">${{ row.pricePerKg.toFixed(2) }}</span>
                                    <span class="mktl-card__price-unit">/ kg</span>
                                </div>
                            </div>

                            <div class="mktl-card__meta">
                                <span class="mktl-card__origin"><el-icon :size="12"><Location /></el-icon>{{ row.origin }}</span>
                                <span v-if="row.type" class="mktl-card__dot" />
                                <span v-if="row.type">{{ row.type }}</span>
                                <span v-if="row.process" class="mktl-card__dot" />
                                <span v-if="row.process">{{ row.process }}</span>
                            </div>

                            <div class="mktl-card__stock"><el-icon :size="12"><Box /></el-icon>{{ row.quantity.toLocaleString() }} kg avail.</div>

                            <Link :href="route('market.show', row.id)" class="mktl-card__cta" @click.stop>
                                <el-icon :size="13"><CircleCheck /></el-icon> View Listing
                            </Link>
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

.mktl-body { padding: 2.5rem 0 3rem; }

/* ── Hero ─────────────────────────────────────────────────────────────── */
.mktl-hero {
    display: flex; flex-direction: column; gap: 20px;
    margin-bottom: 32px; padding-bottom: 28px; border-bottom: 1px solid rgba(39, 19, 16, .08);
}
.mktl-hero__text { min-width: 0; }
.mktl-hero__eyebrow { font-size: .6875rem; font-weight: 700; color: #1b6d24; text-transform: uppercase; letter-spacing: .1em; margin: 0 0 6px; }
.mktl-hero__title { font-size: 1.875rem; line-height: 2.25rem; letter-spacing: -0.015em; font-weight: 800; color: var(--green); margin: 0 0 8px; }
.mktl-hero__subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 500; color: var(--on-surface-var); max-width: 560px; margin: 0; }

.mktl-hero__search {
    display: flex; align-items: center; gap: 10px; flex-shrink: 0;
    width: 100%; height: 46px; padding: 0 16px; border-radius: 12px;
    background: var(--surface-low); color: var(--on-surface-var);
    transition: box-shadow .15s ease;
}
.mktl-hero__search:focus-within { box-shadow: 0 0 0 2px var(--green-dark) inset; color: var(--on-surface); }
.mktl-hero__search input {
    flex: 1; min-width: 0; height: 100%; border: none; background: none;
    font: inherit; font-size: .875rem; color: var(--on-surface);
}
.mktl-hero__search input:focus,
.mktl-hero__search input:focus-visible {
    outline: none !important;
    box-shadow: none !important;
}
.mktl-hero__search input::placeholder { color: var(--on-surface-var); }

@media (min-width: 768px) {
    .mktl-hero { flex-direction: row; align-items: flex-end; justify-content: space-between; gap: 32px; }
    .mktl-hero__search { width: 280px; }
}

.mktl-clear { display: inline-flex; align-items: center; gap: 4px; border: none; background: none; color: var(--green); font-size: .6875rem; font-weight: 700; cursor: pointer; padding: 0; }
.mktl-clear:hover { text-decoration: underline; }
.mktl-clear--pill { border: none; background: var(--surface-low); border-radius: 8px; padding: 7px 14px; margin-top: 4px; }

/* ── Product grid ─────────────────────────────────────────────────────── */
.mktl-grid { display: grid; grid-template-columns: 1fr; gap: 32px; margin-top: 8px; }

@media (min-width: 640px) {
    .mktl-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .mktl-grid { grid-template-columns: repeat(3, 1fr); }
}

.mktl-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .15s ease, transform .12s ease;
    display: flex;
    flex-direction: column;
}
.mktl-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

.mktl-card__media {
    position: relative; width: 100%; aspect-ratio: 4 / 3; background: #f1e6d8;
    display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.mktl-card__media img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
.mktl-card:hover .mktl-card__media img { transform: scale(1.05); }
.mktl-card__ribbon { position: absolute; top: 12px; left: 12px; background: rgba(26, 15, 12, .82); color: #fbbf24; font-size: .625rem; font-weight: 700; padding: 3px 9px; border-radius: 999px; backdrop-filter: blur(4px); }
.mktl-card__score {
    position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 4px;
    background: rgba(255, 255, 255, .92); backdrop-filter: blur(4px); color: var(--on-surface);
    font-size: .75rem; font-weight: 700; padding: 5px 10px; border-radius: 999px;
}
.mktl-card__score .el-icon { color: var(--green); }
.mktl-card__ribbon ~ .mktl-card__score { top: 44px; }
.mktl-card__demand { position: absolute; top: 12px; right: 12px; }

.mktl-card__body { padding: 16px 18px 18px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
.mktl-card__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.mktl-card__title {
    font-size: 1rem; font-weight: 700; color: var(--on-surface);
    letter-spacing: -0.005em; line-height: 1.375rem; margin: 0 !important;
    display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    min-height: 2.75rem;
}
.mktl-card__price-block { display: flex; align-items: baseline; gap: 3px; flex-shrink: 0; }
.mktl-card__price { font-family: 'IBM Plex Mono', ui-monospace, monospace; font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); white-space: nowrap; }
.mktl-card__price-unit { font-size: .6875rem; color: var(--on-surface-var); }

.mktl-card__meta { display: flex; align-items: center; gap: 6px; font-size: .6875rem; font-weight: 600; color: var(--on-surface-var); text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; overflow: hidden; }
.mktl-card__meta > span:not(.mktl-card__dot) { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.mktl-card__origin { display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0; text-transform: none; }
.mktl-card__dot { width: 3px; height: 3px; border-radius: 50%; background: var(--border); flex-shrink: 0; }

.mktl-card__stock { display: inline-flex; align-items: center; gap: 3px; font-size: .75rem; color: var(--on-surface-var); white-space: nowrap; }

.mktl-card__cta {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    margin-top: auto; height: 40px; border-radius: 10px;
    background: var(--green); color: #fff; text-decoration: none;
    font-size: .8125rem; font-weight: 700;
    transition: background .15s ease, transform .15s ease;
}
.mktl-card__cta:hover { background: var(--green-dark); transform: translateY(-1px); }

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

/* ── Badges (shared demand-badge classes from the market design system) ── */
.mkt-badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; font-size: .625rem; font-weight: 700; padding: 3px 9px; line-height: 1.4; white-space: nowrap; }
.mkt-badge--green { background: #ecfdf5; color: #059669; }
.mkt-badge--green-solid { background: var(--green); color: #fff; }
.mkt-badge--amber { background: #fffbeb; color: #d97706; }
.mkt-badge--red { background: #fef2f2; color: #dc2626; }
.mkt-badge--muted { background: #f5f5f4; color: #78716c; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .mktl-body { padding: 1.5rem 0 2.5rem; }
    .mktl-hero__title { font-size: 1.5rem; line-height: 1.9rem; }
    .mktl-grid { gap: 20px; }
}
</style>
