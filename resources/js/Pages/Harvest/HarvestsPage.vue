<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Tickets, Files, ArrowRight, MapLocation, ScaleToOriginal, Coin } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    harvests: { type: Object, required: true },
    filters:  { type: Object, default: () => ({ search: '' }) },
    estateOptions: { type: Array, default: () => [] },
});

const visiblePages = computed(() => {
    const cur  = props.harvests.meta.current_page || 1;
    const last = props.harvests.meta.last_page    || 1;
    const pages = [];
    for (let p = Math.max(1, cur - 1); p <= Math.min(last, cur + 1); p++) pages.push(p);
    return pages;
});

const hasHarvests  = computed(() => props.harvests.data.length > 0);
const totalHarvests = computed(() => props.harvests.meta.total || 0);
const searchQuery  = ref(props.filters.search ?? '');
const rangeSummary = computed(() => {
    const { from = 0, to = 0, total = 0 } = props.harvests.meta;
    return `Showing ${from}–${to} of ${total}`;
});

let debounceId = null;

const formatDate = (value) => {
    if (!value) return 'Pending';
    return new Intl.DateTimeFormat('en-UG', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(value));
};

const formatWeight = (value) => `${Number(value || 0).toLocaleString()} kg`;

const formatPrice = (value) => {
    if (value === null || value === undefined || value === '') return 'Pending';
    return `Shs. ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const visitHarvestIndex = (page = 1) => {
    router.get(
        route('harvest.index'),
        { page, search: searchQuery.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(searchQuery, () => {
    if (debounceId) window.clearTimeout(debounceId);
    debounceId = window.setTimeout(() => visitHarvestIndex(1), 250);
});

onBeforeUnmount(() => { if (debounceId) window.clearTimeout(debounceId); });
</script>

<template>
    <AppLayout title="Harvests Directory" full-width flush :show-banner="false">
        <Head title="Harvests Directory" />

        <div class="hp-root">

            <!-- ── Hero ──────────────────────────────────────────────────── -->
            <section class="hp-hero">
                <div class="hp-hero__inner">
                    <div class="hp-hero__left">
                        <h1 class="hp-hero__title">Harvests Directory</h1>
                        <p class="hp-hero__sub">Centralized oversight for all harvest records, quality tracking, and seasonal processing.</p>
                    </div>
                    <div class="hp-hero__right">
                        <div class="hp-stats">
                            <div class="hp-stat">
                                <span class="hp-stat__val">{{ totalHarvests }}</span>
                                <span class="hp-stat__label">Total</span>
                            </div>
                            <div class="hp-stat-divider"></div>
                            <div class="hp-stat">
                                <span class="hp-stat__val hp-stat__val--green">{{ harvests.data.length }}</span>
                                <span class="hp-stat__label">This page</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Table section ─────────────────────────────────────────── -->
            <section class="hp-section">
                <div class="hp-section__inner">

                  

                    <!-- Table -->
                    <div class="hp-table-wrap">
                        <div class="hp-table-header">
                            <div class="hp-table-header__left">
                                <el-icon class="hp-table-header__icon"><Tickets /></el-icon>
                                <span class="hp-table-header__title">All Harvest Records</span>
                            </div>
                        </div>
                        <table class="hp-table">
                            <thead>
                                <tr>
                                    <th>Harvest ID</th>
                                    <th>Farm</th>
                                    <th>Date Planted</th>
                                    <th>Harvest Date</th>
                                    <th>Season</th>
                                    <th>Weight</th>
                                    <th>Price</th>
                                    <th>Picking Method</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="hasHarvests">
                                <tr v-for="harvest in harvests.data" :key="harvest.id" class="hp-row">
                                    <td class="hp-td hp-td--id">
                                        <el-icon class="hp-row-icon"><Tickets /></el-icon>
                                        H-{{ harvest.id }}
                                    </td>
                                    <td class="hp-td hp-td--farm">
                                        <div class="hp-farm-cell">
                                            <el-icon class="hp-farm-icon"><MapLocation /></el-icon>
                                            {{ harvest.farm_name }}
                                        </div>
                                    </td>
                                    <td class="hp-td hp-td--muted">{{ formatDate(harvest.date_planted) }}</td>
                                    <td class="hp-td hp-td--muted">{{ formatDate(harvest.harvest_date) }}</td>
                                    <td class="hp-td">
                                        <span v-if="harvest.harvest_season" class="hp-season-tag">{{ harvest.harvest_season }}</span>
                                        <span v-else class="hp-pending-tag">Pending</span>
                                    </td>
                                    <td class="hp-td">
                                        <div class="hp-weight-cell">
                                            <el-icon class="hp-weight-icon"><ScaleToOriginal /></el-icon>
                                            <span class="hp-td--weight">{{ formatWeight(harvest.weight) }}</span>
                                        </div>
                                    </td>
                                    <td class="hp-td hp-td--price">
                                        <span v-if="harvest.price !== null && harvest.price !== undefined && harvest.price !== ''" class="hp-price-val">
                                            <el-icon class="hp-price-icon"><Coin /></el-icon>
                                            {{ formatPrice(harvest.price) }}
                                        </span>
                                        <span v-else class="hp-pending-tag">Pending</span>
                                    </td>
                                    <td class="hp-td">
                                        <span v-if="harvest.pick_method" class="hp-method-tag">{{ harvest.pick_method }}</span>
                                        <span v-else class="hp-pending-tag">Pending</span>
                                    </td>
                                    <td class="hp-td hp-td--action">
                                        <Link :href="harvest.show_url" class="hp-open-link">
                                            View <el-icon class="hp-open-link__icon"><ArrowRight /></el-icon>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="9" class="hp-empty">
                                        <el-icon class="hp-empty__icon"><Files /></el-icon>
                                        <strong>No harvests recorded yet</strong>
                                        <span>Harvests will appear here once they are added to a season.</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="hp-footer">
                        <span class="hp-footer__summary">{{ rangeSummary }}</span>
                        <div class="hp-pagination">
                            <Link
                                :href="route('harvest.index', { page: Math.max(1, harvests.meta.current_page - 1), search: filters.search || undefined })"
                                class="hp-page-btn"
                                :class="{ 'hp-page-btn--disabled': harvests.meta.current_page <= 1 }"
                            >Previous</Link>
                            <Link
                                v-for="page in visiblePages"
                                :key="page"
                                :href="route('harvest.index', { page, search: filters.search || undefined })"
                                class="hp-page-btn"
                                :class="{ 'hp-page-btn--active': page === harvests.meta.current_page }"
                            >{{ page }}</Link>
                            <Link
                                :href="route('harvest.index', { page: Math.min(harvests.meta.last_page, harvests.meta.current_page + 1), search: filters.search || undefined })"
                                class="hp-page-btn"
                                :class="{ 'hp-page-btn--disabled': harvests.meta.current_page >= harvests.meta.last_page }"
                            >Next</Link>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.hp-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #191c1e;
    --on-surface-var:   #74777a;
    --surface-white:    #ffffff;
    --surface-low:      #f2f4f6;
    --surface-high:     #e6e8ea;
    --primary-fixed:    #a6f2d1;
    --on-primary-fixed: #002116;
    --inner-pad:        2rem;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.hp-hero {
    background: var(--surface-white);
    border-bottom: 1px solid var(--surface-high);
    padding: 1.5rem 0;
}
.hp-hero__inner {
    padding: 0 var(--inner-pad);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.hp-hero__title {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    margin: 0 0 0.25rem;
    line-height: 1.2;
}
.hp-hero__sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.5;
    max-width: 480px;
}

/* Stats */
.hp-stats { display: flex; align-items: center; gap: 1rem; flex-shrink: 0; }
.hp-stat  { display: flex; flex-direction: column; align-items: center; gap: 1px; text-align: center; }
.hp-stat__val {
    font-size: 1.125rem; font-weight: 800;
    letter-spacing: -0.02em; color: var(--on-surface); line-height: 1;
}
.hp-stat__val--green { color: var(--primary); }
.hp-stat__label {
    font-size: 0.625rem; font-weight: 700;
    letter-spacing: 0.08em; text-transform: uppercase; color: var(--on-surface-var);
}
.hp-stat-divider { width: 1px; height: 28px; background: var(--surface-high); }

/* ── Section ───────────────────────────────────────────────────────────────── */
.hp-section { padding: 1.5rem 0 2.5rem; }
.hp-section__inner { padding: 0 var(--inner-pad); }

/* ── Toolbar ───────────────────────────────────────────────────────────────── */
.hp-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.hp-search { flex: 1; min-width: 220px; }
.hp-result-count {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface-var);
    white-space: nowrap;
    flex-shrink: 0;
}

:deep(.hp-search .el-input__wrapper) {
    background: var(--surface-white);
    border-radius: 6px !important;
    box-shadow: 0 0 0 1px var(--surface-high) inset;
    min-height: 38px;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem;
}
:deep(.hp-search .el-input__wrapper.is-focus) {
    box-shadow: 0 0 0 1px var(--primary) inset;
}

/* ── Table ─────────────────────────────────────────────────────────────────── */
.hp-table-wrap {
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
    overflow-x: auto;
}

.hp-table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--surface-white);
    border-bottom: 1px solid var(--surface-high);
}
.hp-table-header__left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.hp-table-header__icon {
    color: var(--primary);
    font-size: 15px;
}
.hp-table-header__title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--on-surface);
}
.hp-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}
.hp-table thead tr {
    background: var(--surface-low);
}
.hp-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 0.6875rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--on-surface-var);
    white-space: nowrap;
}
.hp-table th:last-child { width: 60px; }

.hp-row {
    border-bottom: 1px solid var(--surface-low);
    transition: background 0.1s ease;
    cursor: default;
}
.hp-row:last-child { border-bottom: none; }
.hp-row:hover { background: #fafbfc; }

.hp-td {
    padding: 12px 14px;
    font-size: 0.8125rem;
    color: var(--on-surface);
    vertical-align: middle;
}
.hp-td--id {
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.hp-row-icon { color: var(--primary); font-size: 13px; }
.hp-td--farm  { font-weight: 600; }
.hp-td--muted { color: var(--on-surface-var); }
.hp-td--weight{ font-weight: 600; }
.hp-td--price { color: var(--on-surface); font-weight: 500; }
.hp-td--action{ text-align: right; }

/* Farm cell */
.hp-farm-cell {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}
.hp-farm-icon { color: var(--on-surface-var); font-size: 13px; flex-shrink: 0; }

/* Weight cell */
.hp-weight-cell {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.hp-weight-icon { color: var(--on-surface-var); font-size: 12px; flex-shrink: 0; }

/* Price cell */
.hp-price-val {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}
.hp-price-icon { color: var(--on-surface-var); font-size: 12px; flex-shrink: 0; }

/* Tags */
.hp-season-tag {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    background: var(--primary-fixed);
    color: var(--on-primary-fixed);
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    white-space: nowrap;
}
.hp-method-tag {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    background: var(--surface-low);
    color: var(--on-surface);
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    white-space: nowrap;
    text-transform: capitalize;
}
.hp-pending-tag {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    background: #f5f0e8;
    color: #7a6030;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    white-space: nowrap;
}

.hp-open-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--primary);
    font-size: 0.8125rem;
    font-weight: 700;
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(0,69,50,0.2);
    background: rgba(0,69,50,0.04);
    transition: background 0.12s ease;
}
.hp-open-link:hover { background: rgba(0,69,50,0.09); }
.hp-open-link__icon { font-size: 11px; }

/* Empty state */
.hp-empty {
    padding: 4rem 2rem;
    text-align: center;
}
.hp-empty { display: table-cell; }
.hp-empty__icon {
    font-size: 2.5rem;
    color: var(--surface-high);
    display: block;
    margin: 0 auto 0.75rem;
}
.hp-empty strong { display: block; font-size: 0.9375rem; color: var(--on-surface); margin-bottom: 6px; }
.hp-empty span   { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Footer ────────────────────────────────────────────────────────────────── */
.hp-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 1.25rem;
}
.hp-footer__summary { font-size: 0.8125rem; color: var(--on-surface-var); }
.hp-pagination { display: flex; gap: 6px; flex-wrap: wrap; }

.hp-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    border-radius: 6px;
    border: 1px solid var(--surface-high);
    background: var(--surface-white);
    color: var(--on-surface);
    font-size: 0.8125rem;
    font-weight: 600;
    text-decoration: none;
    padding: 0 10px;
    transition: background 0.12s ease;
}
.hp-page-btn:hover { background: var(--surface-low); }
.hp-page-btn--active { background: var(--primary); border-color: var(--primary); color: var(--on-primary); }
.hp-page-btn--disabled { opacity: 0.4; pointer-events: none; }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .hp-root { --inner-pad: 1.25rem; }
    .hp-toolbar { flex-direction: column; align-items: stretch; }
    .hp-filters { flex-wrap: wrap; }
    .hp-hero__inner { flex-direction: column; align-items: flex-start; gap: 1rem; }
}
@media (max-width: 640px) {
    .hp-root { --inner-pad: 1rem; }
    .hp-stats { gap: 0.75rem; }
    .hp-footer { flex-direction: column; align-items: flex-start; }
}
</style>
