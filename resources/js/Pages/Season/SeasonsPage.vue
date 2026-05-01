<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Calendar,
    Location,
    Plus,
    Reading,
    Tickets,
    TrendCharts,
    Box,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    seasons: {
        type: Object,
        required: true,
    },
});

const visiblePages = computed(() => {
    const currentPage = props.seasons.meta.current_page || 1;
    const lastPage    = props.seasons.meta.last_page    || 1;
    const start = Math.max(1, currentPage - 1);
    const end   = Math.min(lastPage, currentPage + 1);
    const pages = [];
    for (let p = start; p <= end; p++) pages.push(p);
    return pages;
});

const rangeSummary = computed(() => {
    const { from = 0, to = 0, total = 0 } = props.seasons.meta;
    return `Showing ${from}–${to} of ${total}`;
});

const totalSeasons  = computed(() => props.seasons.meta.total || 0);
const activeCount   = computed(() => props.seasons.data.filter(s => String(s.status).toLowerCase() === 'active').length);
const completedCount= computed(() => props.seasons.data.filter(s => String(s.status).toLowerCase() === 'completed').length);

const formatDate = (value) => {
    if (!value) return 'Pending';
    return new Intl.DateTimeFormat('en-UG', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(value));
};

const statusClass = (status) => {
    switch (String(status).toLowerCase()) {
        case 'active':    return 'sp-status--active';
        case 'completed': return 'sp-status--completed';
        case 'archived':  return 'sp-status--archived';
        default:          return 'sp-status--default';
    }
};
</script>

<template>
    <AppLayout title="Seasons Directory" full-width flush :show-banner="false">
        <Head title="Seasons Directory" />

        <div class="sp-root">

            <!-- ── Header ──────────────────────────────────────────────────── -->
            <section class="sp-hero">
                <div class="sp-hero__inner">
                    <div class="sp-hero__left">
                        <h1 class="sp-hero__title">Seasons Directory</h1>
                        <p class="sp-hero__sub">Track production windows, regional cycles, and harvest planning.</p>
                    </div>
                    <div class="sp-hero__right">
                        <div class="sp-hero__stats">
                            <div class="sp-stat">
                                <span class="sp-stat__val">{{ totalSeasons }}</span>
                                <span class="sp-stat__label">Total</span>
                            </div>
                            <div class="sp-stat-divider"></div>
                            <div class="sp-stat">
                                <span class="sp-stat__val sp-stat__val--green">{{ activeCount }}</span>
                                <span class="sp-stat__label">Active</span>
                            </div>
                            <div class="sp-stat-divider"></div>
                            <div class="sp-stat">
                                <span class="sp-stat__val">{{ completedCount }}</span>
                                <span class="sp-stat__label">Completed</span>
                            </div>
                        </div>
                        <Link :href="route('season.create')" class="sp-btn sp-btn--primary">
                            <el-icon><Plus /></el-icon> Add Season
                        </Link>
                    </div>
                </div>
            </section>

            <!-- ── Table ───────────────────────────────────────────────────── -->
            <section class="sp-section">
                <div class="sp-section__inner">

                    <div class="sp-table-wrap">
                        <!-- Table head -->
                        <div class="sp-table-head">
                            <div class="sp-th sp-col--name">Season</div>
                            <div class="sp-th sp-col--region">Region</div>
                            <div class="sp-th sp-col--type">Coffee Type</div>
                            <div class="sp-th sp-col--date">Start Date</div>
                            <div class="sp-th sp-col--date">End Date</div>
                            <div class="sp-th sp-col--status">Status</div>
                            <div class="sp-th sp-col--notes">Notes</div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!seasons.data.length" class="sp-empty">
                            <el-icon class="sp-empty__icon"><Tickets /></el-icon>
                            <strong>No seasons yet</strong>
                            <span>Create your first season to start tracking production cycles.</span>
                            <Link :href="route('season.create')" class="sp-btn sp-btn--primary" style="margin-top:1rem;">
                                <el-icon><Plus /></el-icon> Add Season
                            </Link>
                        </div>

                        <!-- Rows -->
                        <div
                            v-for="row in seasons.data"
                            :key="row.id"
                            class="sp-table-row"
                        >
                            <div class="sp-td sp-col--name">
                                <Link :href="route('season.show', row.id)" class="sp-season-link">
                                    <el-icon><Tickets /></el-icon>
                                    <span>{{ row.name }}</span>
                                </Link>
                            </div>
                            <div class="sp-td sp-col--region">
                                <el-icon><Location /></el-icon>
                                <span>{{ row.region || '—' }}</span>
                            </div>
                            <div class="sp-td sp-col--type">
                                <el-icon><Reading /></el-icon>
                                <span>{{ row.coffee_type || '—' }}</span>
                            </div>
                            <div class="sp-td sp-col--date">
                                <el-icon><Calendar /></el-icon>
                                <span>{{ formatDate(row.start_date) }}</span>
                            </div>
                            <div class="sp-td sp-col--date">
                                <el-icon><Calendar /></el-icon>
                                <span>{{ formatDate(row.end_date) }}</span>
                            </div>
                            <div class="sp-td sp-col--status">
                                <span class="sp-status" :class="statusClass(row.status)">{{ row.status }}</span>
                            </div>
                            <div class="sp-td sp-col--notes sp-td--muted">
                                {{ row.notes || 'No notes added.' }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer / pagination -->
                    <div class="sp-footer">
                        <span class="sp-footer__summary">{{ rangeSummary }}</span>
                        <div class="sp-pagination">
                            <Link
                                :href="route('season.index', { page: Math.max(1, seasons.meta.current_page - 1) })"
                                class="sp-page-btn"
                                :class="{ 'sp-page-btn--disabled': seasons.meta.current_page <= 1 }"
                            >Previous</Link>
                            <Link
                                v-for="page in visiblePages"
                                :key="page"
                                :href="route('season.index', { page })"
                                class="sp-page-btn"
                                :class="{ 'sp-page-btn--active': page === seasons.meta.current_page }"
                            >{{ page }}</Link>
                            <Link
                                :href="route('season.index', { page: Math.min(seasons.meta.last_page, seasons.meta.current_page + 1) })"
                                class="sp-page-btn"
                                :class="{ 'sp-page-btn--disabled': seasons.meta.current_page >= seasons.meta.last_page }"
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
.sp-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #191c1e;
    --on-surface-var:   #74777a;
    --surface:          #f7f9fb;
    --surface-low:      #f2f4f6;
    --surface-high:     #e6e8ea;
    --surface-white:    #ffffff;
    --primary-fixed:    #a6f2d1;
    --on-primary-fixed: #002116;
    --secondary-fixed:  #fedcbe;
    --on-secondary-fixed: #291806;
    --inner-pad: 2rem;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
    color: var(--on-surface);
    min-height: 100%;
    margin: 0;
    padding: 0;
}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.sp-hero {
    background: var(--surface-white);
    border-bottom: 1px solid var(--surface-high);
    padding: 1.5rem 0;
}
.sp-hero__inner {
    padding: 0 var(--inner-pad);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.sp-hero__left { min-width: 0; }
.sp-hero__right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-shrink: 0;
    flex-wrap: wrap;
}
.sp-hero__title {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    margin: 0 0 0.25rem;
    line-height: 1.2;
}
.sp-hero__sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.5;
}

/* Inline stats */
.sp-hero__stats {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.sp-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
    text-align: center;
}
.sp-stat__val {
    font-size: 1.125rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    line-height: 1;
}
.sp-stat__val--green { color: var(--primary); }
.sp-stat__label {
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--on-surface-var);
}
.sp-stat-divider {
    width: 1px;
    height: 28px;
    background: var(--surface-high);
}

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.sp-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem;
    font-weight: 700;
    border-radius: 0.375rem;
    padding: 9px 18px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.15s ease;
}
.sp-btn--primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
}
.sp-btn--primary:hover { opacity: 0.88; }

/* ── Section ───────────────────────────────────────────────────────────────── */
.sp-section { background: var(--surface-white); padding: 2rem 0; }
.sp-section__inner { padding: 0 var(--inner-pad); }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.sp-table-wrap {
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
}

/* Column widths */
.sp-col--name   { flex: 1.8; min-width: 160px; }
.sp-col--region { flex: 1.4; min-width: 130px; }
.sp-col--type   { flex: 1.2; min-width: 120px; }
.sp-col--date   { flex: 1.2; min-width: 120px; }
.sp-col--status { flex: 0.8; min-width: 90px; }
.sp-col--notes  { flex: 2; min-width: 160px; }

.sp-table-head {
    display: flex;
    align-items: center;
    gap: 0;
    background: var(--surface-low);
    border-bottom: 1px solid var(--surface-high);
    padding: 0 1.25rem;
}
.sp-th {
    padding: 0.75rem 0.75rem 0.75rem 0;
    font-size: 0.6875rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--on-surface-var);
}

.sp-table-row {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 0 1.25rem;
    border-bottom: 1px solid var(--surface-low);
    transition: background 0.12s ease;
}
.sp-table-row:last-child { border-bottom: none; }
.sp-table-row:hover { background: #fafbfc; }

.sp-td {
    padding: 1rem 0.75rem 1rem 0;
    font-size: 0.8125rem;
    color: var(--on-surface);
    display: flex;
    align-items: center;
    gap: 7px;
}
.sp-td .el-icon { color: var(--on-surface-var); font-size: 14px; flex-shrink: 0; }
.sp-td--muted { color: var(--on-surface-var); font-size: 0.75rem; }

.sp-season-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--primary);
    font-weight: 700;
    text-decoration: none;
}
.sp-season-link:hover { text-decoration: underline; }
.sp-season-link .el-icon { color: var(--primary); }

/* Status badges */
.sp-status {
    display: inline-block;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    border-radius: 999px;
    padding: 3px 10px;
}
.sp-status--active    { background: var(--primary-fixed); color: var(--on-primary-fixed); }
.sp-status--completed { background: var(--surface-high);  color: var(--on-surface-var); }
.sp-status--archived  { background: var(--secondary-fixed); color: var(--on-secondary-fixed); }
.sp-status--default   { background: var(--surface-high);  color: var(--on-surface-var); }

/* Empty state */
.sp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    gap: 8px;
}
.sp-empty__icon { font-size: 2.5rem; color: var(--surface-high); margin-bottom: 0.5rem; }
.sp-empty strong { font-size: 1rem; font-weight: 700; color: var(--on-surface); }
.sp-empty span   { font-size: 0.875rem; color: var(--on-surface-var); }

/* ── Footer / Pagination ───────────────────────────────────────────────────── */
.sp-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1.25rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.sp-footer__summary {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
}
.sp-pagination { display: flex; gap: 6px; flex-wrap: wrap; }
.sp-page-btn {
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
    transition: background 0.12s ease, border-color 0.12s ease;
}
.sp-page-btn:hover { background: var(--surface-low); }
.sp-page-btn--active {
    background: var(--primary);
    border-color: var(--primary);
    color: var(--on-primary);
}
.sp-page-btn--disabled { opacity: 0.4; pointer-events: none; }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .sp-root { --inner-pad: 1.25rem; }
    .sp-hero__inner { flex-wrap: wrap; gap: 1rem; }
    .sp-col--notes { display: none; }
}
@media (max-width: 640px) {
    .sp-root { --inner-pad: 1rem; }
    .sp-hero__right { gap: 1rem; }
    .sp-hero__stats { gap: 0.75rem; }
    .sp-col--type, .sp-col--region { display: none; }
    .sp-table-head, .sp-table-row { padding: 0 0.875rem; }
    .sp-pagination { gap: 4px; }
}
</style>
