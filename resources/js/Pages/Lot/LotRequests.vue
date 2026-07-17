<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Box, Check, DataLine, Filter, Refresh, Search, Tickets, Warning,
} from '@element-plus/icons-vue';

/* ── Props ──────────────────────────────────────────────────── */
const props = defineProps({
    requests: { type: Object, default: () => ({ data: [], meta: { total: 0, current_page: 1, last_page: 1, from: 0, to: 0 } }) },
    filters:  { type: Object, default: () => ({}) },
});

/* ── Filter state ───────────────────────────────────────────── */
const search   = ref(props.filters.search    ?? '');
const fStatus  = ref(props.filters.status    ?? '');
const fCrop    = ref(props.filters.crop_type ?? '');

function applyFilters() {
    router.get(route('lot.requests.index'), {
        search:    search.value   || undefined,
        status:    fStatus.value  || undefined,
        crop_type: fCrop.value    || undefined,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value  = '';
    fStatus.value = '';
    fCrop.value   = '';
    applyFilters();
}

/* ── Helpers ────────────────────────────────────────────────── */
const rows = computed(() => props.requests.data ?? []);
const meta = computed(() => props.requests.meta ?? { total: 0, current_page: 1, last_page: 1, from: 0, to: 0 });

const statusLabel = (s) => ({
    pending:   'Pending',
    approved:  'Approved',
    rejected:  'Rejected',
    fulfilled: 'Fulfilled',
}[s] ?? s ?? '—');

const statusCls = (s) => ({
    pending:   'lr-badge--yellow',
    approved:  'lr-badge--green',
    rejected:  'lr-badge--red',
    fulfilled: 'lr-badge--blue',
}[s] ?? 'lr-badge--muted');

const fmtDate  = (d) => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
const fmtQty   = (q) => q != null ? `${Number(q).toLocaleString()} kg` : '—';
const fmtAmt   = (a) => a != null ? `$${Number(a).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '—';
const cap      = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : '—';

const kpis = computed(() => {
    const all = rows.value;
    return [
        { label: 'Total',     value: meta.value.total,                                                               sub: 'All requests'    },
        { label: 'Pending',   value: all.filter(r => r.status === 'pending').length,   cls: 'lr-kpi__value--amber',  sub: 'Awaiting review' },
        { label: 'Approved',  value: all.filter(r => r.status === 'approved').length,  cls: 'lr-kpi__value--green',  sub: 'Ready to match'  },
        { label: 'Fulfilled', value: all.filter(r => r.status === 'fulfilled').length, cls: 'lr-kpi__value--blue',   sub: 'Completed'       },
    ];
});

const visiblePages = computed(() => {
    const cur  = meta.value.current_page ?? 1;
    const last = meta.value.last_page    ?? 1;
    const pages = [];
    for (let p = Math.max(1, cur - 1); p <= Math.min(last, cur + 1); p++) pages.push(p);
    return pages;
});
</script>

<template>
    <AppLayout title="Lot Requests" full-width flush :show-banner="false">
        <Head title="Lot Requests" />

        <div class="lr-page">

            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="lr-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="lr-kicker">Procurement</div>
                            <h1 class="lr-title mb-0">Lot Requests</h1>
                            <p class="lr-subtitle mb-0">Browse and manage buyer procurement requests for coffee lots.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <Link :href="route('dashboard')" class="btn lr-btn-outline btn-sm">
                                <el-icon><Tickets /></el-icon> Dashboard
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── KPI strip ────────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-sm-3">
                        <div class="lr-kpi h-100">
                            <span class="lr-kpi__label">{{ kpi.label }}</span>
                            <div class="lr-kpi__value" :class="kpi.cls ?? ''">{{ kpi.value }}</div>
                            <div class="lr-kpi__sub">{{ kpi.sub }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Filters ───────────────────────────────────────────── -->
                <div class="lr-filters mb-3">
                    <div class="lr-search-wrap">
                        <el-icon class="lr-search-icon"><Search /></el-icon>
                        <input v-model="search" class="lr-search-input" placeholder="Search crop, variety, grade…" @keydown.enter="applyFilters">
                    </div>
                    <select v-model="fStatus" class="lr-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="fulfilled">Fulfilled</option>
                    </select>
                    <select v-model="fCrop" class="lr-select">
                        <option value="">All Crop Types</option>
                        <option value="arabica">Arabica</option>
                        <option value="robusta">Robusta</option>
                    </select>
                    <div class="d-flex gap-2 ms-auto">
                        <button class="btn lr-btn-primary btn-sm" @click="applyFilters">
                            <el-icon><Filter /></el-icon> Apply
                        </button>
                        <button class="btn lr-btn-ghost btn-sm" @click="resetFilters">
                            <el-icon><Refresh /></el-icon> Reset
                        </button>
                    </div>
                </div>

                <!-- ── Table ─────────────────────────────────────────────── -->
                <div class="lr-section">

                    <!-- Count bar -->
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="background:#f8fafc;border-radius:8px 8px 0 0;">
                        <span class="lr-count-label">
                            <span class="lr-count-num">{{ meta.total }}</span> requests
                        </span>
                        <span class="lr-muted" style="font-size:.75rem;">
                            Page {{ meta.current_page }} of {{ meta.last_page }}
                        </span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="!rows.length" class="text-center py-5">
                        <el-icon style="font-size:2rem;color:#d1d5db;"><Box /></el-icon>
                        <p class="lr-muted mt-2 mb-0" style="font-size:.875rem;">No lot requests found.</p>
                    </div>

                    <!-- Table -->
                    <div v-else class="table-responsive">
                        <table class="table lr-table mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width:180px;">Crop / Variety</th>
                                    <th>Grade</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Budget</th>
                                    <th>Status</th>
                                    <th style="min-width:140px;">Requester</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="req in rows" :key="req.id" class="lr-table-row">
                                    <!-- Crop identity -->
                                    <td>
                                        <div class="lr-tbl-crop">
                                            <div class="lr-tbl-crop__avatar">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="13" height="13" style="opacity:.5"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/></svg>
                                            </div>
                                            <div>
                                                <div class="lr-item-name">{{ cap(req.crop_type) }}</div>
                                                <div class="lr-tbl-crop__sub">{{ req.variety ? cap(req.variety) : '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Grade -->
                                    <td>
                                        <span v-if="req.grade" class="lr-grade-pill">{{ cap(req.grade) }}</span>
                                        <span v-else class="lr-muted">—</span>
                                    </td>
                                    <!-- Quantity -->
                                    <td class="text-end">
                                        <span style="font-size:.875rem;font-weight:700;color:#111827;">{{ fmtQty(req.quantity) }}</span>
                                    </td>
                                    <!-- Budget -->
                                    <td class="text-end">
                                        <span v-if="req.amount" style="font-size:.875rem;font-weight:800;color:#004532;">{{ fmtAmt(req.amount) }}</span>
                                        <span v-else class="lr-muted">—</span>
                                    </td>
                                    <!-- Status -->
                                    <td>
                                        <span class="lr-badge" :class="statusCls(req.status)">{{ statusLabel(req.status) }}</span>
                                    </td>
                                    <!-- Requester -->
                                    <td>
                                        <div v-if="req.user" class="lr-requester">
                                            <div class="lr-requester__avatar">{{ (req.user.name ?? '?').charAt(0).toUpperCase() }}</div>
                                            <div>
                                                <div style="font-size:.8125rem;font-weight:600;color:#111827;">{{ req.user.name }}</div>
                                                <div style="font-size:.6875rem;color:#6b7280;">{{ req.user.email }}</div>
                                            </div>
                                        </div>
                                        <span v-else class="lr-muted">—</span>
                                    </td>
                                    <!-- Date -->
                                    <td>
                                        <span style="font-size:.8125rem;color:#6b7280;white-space:nowrap;">{{ fmtDate(req.created_at) }}</span>
                                    </td>
                                    <!-- Actions -->
                                    <td>
                                        <Link :href="route('lot.request.show', req.id)" class="btn lr-btn-outline lr-act-btn btn-sm">
                                            <el-icon><Tickets /></el-icon> View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="lr-pagination-bar">
                        <span class="lr-muted" style="font-size:.8125rem;">
                            Showing {{ rows.length }} of {{ meta.total }} requests
                        </span>
                        <div class="d-flex gap-1">
                            <Link
                                v-if="meta.current_page > 1"
                                :href="route('lot.requests.index', { page: meta.current_page - 1, search: filters.search, status: filters.status, crop_type: filters.crop_type })"
                                class="lr-page-btn"
                            >← Prev</Link>
                            <span v-else class="lr-page-btn lr-page-btn--disabled">← Prev</span>
                            <Link
                                v-for="p in visiblePages"
                                :key="p"
                                :href="route('lot.requests.index', { page: p, search: filters.search, status: filters.status, crop_type: filters.crop_type })"
                                class="lr-page-btn"
                                :class="{ 'lr-page-btn--active': p === meta.current_page }"
                            >{{ p }}</Link>
                            <Link
                                v-if="meta.current_page < meta.last_page"
                                :href="route('lot.requests.index', { page: meta.current_page + 1, search: filters.search, status: filters.status, crop_type: filters.crop_type })"
                                class="lr-page-btn"
                            >Next →</Link>
                            <span v-else class="lr-page-btn lr-page-btn--disabled">Next →</span>
                        </div>
                    </div>

                </div><!-- /lr-section -->

            </div><!-- /container -->
        </div><!-- /lr-page -->
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ───────────────────────────────────────────────────────────────── */
.lr-page {
    --green:          #004532;
    --border:         #e5e7eb;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-low:    #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}
.lr-muted { color: var(--on-surface-var); }
.lr-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

/* ── Header ───────────────────────────────────────────────────────────────── */
.lr-header   { background: #fff; border-bottom: 1px solid var(--border); }
.lr-kicker   { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.lr-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.lr-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

/* ── Buttons ──────────────────────────────────────────────────────────────── */
.lr-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.lr-btn-primary:hover { background: #065f46; color: #fff; }
.lr-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.lr-btn-outline:hover { background: var(--surface-low); }
.lr-btn-ghost { background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.lr-act-btn { font-size: .75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }

/* ── KPI ──────────────────────────────────────────────────────────────────── */
.lr-kpi { border: 1px solid var(--border); border-radius: 8px; padding: .875rem; background: #fff; }
.lr-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.lr-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.lr-kpi__value--green { color: #166534; }
.lr-kpi__value--amber { color: #92400e; }
.lr-kpi__value--blue  { color: #1d4ed8; }
.lr-kpi__sub   { font-size: .625rem; color: var(--on-surface-var); }

/* ── Filters ──────────────────────────────────────────────────────────────── */
.lr-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.lr-search-wrap  { position: relative; display: flex; align-items: center; }
.lr-search-icon  { position: absolute; left: 8px; font-size: 12px; color: var(--on-surface-var); pointer-events: none; }
.lr-search-input { height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 0 10px 0 28px; font-size: .8125rem; outline: none; width: 200px; color: var(--on-surface); }
.lr-search-input:focus { border-color: var(--green); }
.lr-select { height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 0 10px; font-size: .8125rem; color: var(--on-surface); background: #fff; outline: none; cursor: pointer; }
.lr-select:focus { border-color: var(--green); }

/* ── Section ──────────────────────────────────────────────────────────────── */
.lr-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }

/* ── Count label ──────────────────────────────────────────────────────────── */
.lr-count-label { font-size: .8125rem; color: var(--on-surface-var); }
.lr-count-num   { font-weight: 700; color: var(--on-surface); }

/* ── Table ────────────────────────────────────────────────────────────────── */
.lr-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.lr-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.lr-table-row:last-child td { border-bottom: none; }
.lr-table-row:hover { background: var(--surface-low); }

/* ── Crop cell ────────────────────────────────────────────────────────────── */
.lr-tbl-crop { display: flex; align-items: center; gap: 10px; }
.lr-tbl-crop__avatar { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface-low); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lr-tbl-crop__sub { font-size: .6875rem; color: var(--on-surface-var); margin-top: 1px; }

/* ── Grade pill ───────────────────────────────────────────────────────────── */
.lr-grade-pill { display: inline-flex; border-radius: 4px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

/* ── Requester ────────────────────────────────────────────────────────────── */
.lr-requester { display: flex; align-items: center; gap: 8px; }
.lr-requester__avatar { width: 28px; height: 28px; border-radius: 50%; background: #004532; color: #fff; font-size: .75rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Badges ───────────────────────────────────────────────────────────────── */
.lr-badge        { display: inline-flex; border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 8px; }
.lr-badge--green  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lr-badge--yellow { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.lr-badge--red    { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.lr-badge--blue   { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.lr-badge--muted  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

/* ── Pagination ───────────────────────────────────────────────────────────── */
.lr-pagination-bar { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; flex-wrap: wrap; gap: .5rem; border-top: 1px solid var(--border); }
.lr-page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 28px; border-radius: 5px; border: 1px solid var(--border); background: #fff; color: var(--on-surface); font-size: .8125rem; font-weight: 600; text-decoration: none; padding: 0 8px; }
.lr-page-btn:hover { background: var(--surface-low); }
.lr-page-btn--active   { background: var(--green); border-color: var(--green); color: #fff; }
.lr-page-btn--disabled { opacity: .4; pointer-events: none; }

/* ── Responsive ───────────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .lr-search-input { width: 150px; }
    .lr-filters { gap: 6px; }
}
</style>
