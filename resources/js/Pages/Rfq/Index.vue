<script setup>
/* RFQ hub — structural/visual port of the uploaded "Requests for
   Quotation" mockup (code.html / DESIGN.md) into this app's Trade
   section, restyled with the app's own --dp-* tokens (same convention
   as TradeLayout/Trade/Index.vue — see those files for why literal
   mockup hex isn't used).

   Every number here is real, derived from the full `requests` list this
   page already receives (all LotRequests, system-wide) — no fabricated
   deltas ("+4 new"), no per-RFQ "quotes received" count or closing date,
   since this app's LotRequest model doesn't track either yet. The
   mockup's 6-stage lifecycle and AI lot-matching panel were adapted or
   dropped for the same reason: this app's real RFQ flow is a single
   pending → approved/rejected → fulfilled status, not a multi-quote
   marketplace with per-lot matching. */
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import TradeLayout from '@/Layouts/TradeLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    requests: { type: Array, default: () => [] },
    cropTypes: { type: Array, default: () => [] },
    grades: { type: Array, default: () => [] },
    marketCount: { type: Number, default: 0 },
    auctionCount: { type: Number, default: 0 },
    requestCount: { type: Number, default: 0 },
    authUserId: { type: Number, default: null },
});

const fmt = (value, digits = 2) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
};

const fmtDate = (value) => value
    ? new Date(value).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' })
    : '—';

const rfqCode = (item) => `RFQ-${new Date(item.created_at).getFullYear()}-${String(item.id).padStart(5, '0')}`;

const STATUS_LABELS = { pending: 'Open', approved: 'Approved', fulfilled: 'Fulfilled', rejected: 'Rejected' };
const statusLabel = (status) => STATUS_LABELS[status] || status;
const statusTone = (status) => {
    switch (status) {
        case 'approved': return 'is-good';
        case 'fulfilled': return 'is-info';
        case 'rejected': return 'is-bad';
        default: return 'is-warn';
    }
};

const isMine = (item) => item.user_id === props.authUserId;

/* ── KPI cards — real counts over the full request list. ─────────────── */
const openCount = computed(() => props.requests.filter((r) => r.status === 'pending').length);
const myCount = computed(() => props.requests.filter((r) => isMine(r)).length);
const approvedCount = computed(() => props.requests.filter((r) => r.status === 'approved').length);
const fulfilledCount = computed(() => props.requests.filter((r) => r.status === 'fulfilled').length);

const kpis = computed(() => [
    { key: 'open', label: 'Open RFQs', value: openCount.value, icon: 'description', hint: 'Awaiting a seller response' },
    { key: 'mine', label: 'My RFQs', value: myCount.value, icon: 'person', hint: 'Created by you' },
    { key: 'approved', label: 'Approved', value: approvedCount.value, icon: 'check_circle', hint: 'Seller confirmed' },
    { key: 'fulfilled', label: 'Fulfilled', value: fulfilledCount.value, icon: 'inventory_2', hint: 'Trade completed' },
]);

/* ── Segmented tabs, sort, filters, search ────────────────────────────── */
const activeTab = ref('all');
const tabs = computed(() => [
    { key: 'all', label: 'All RFQs', count: props.requests.length },
    { key: 'mine', label: 'My RFQs', count: myCount.value },
    { key: 'open', label: 'Open for Quotes', count: props.requests.filter((r) => r.status === 'pending' && !isMine(r)).length },
    { key: 'fulfilled', label: 'Fulfilled', count: fulfilledCount.value },
]);

const sortBy = ref('Recommended');
const sortOptions = ['Recommended', 'Newest First', 'Largest Quantity'];

const ALL_TYPES = 'All Types';
const ALL_GRADES = 'All Grades';
const filterType = ref(ALL_TYPES);
const filterGrade = ref(ALL_GRADES);
const filterTypeOptions = computed(() => [ALL_TYPES, ...props.cropTypes]);
const filterGradeOptions = computed(() => [ALL_GRADES, ...props.grades]);

const filteredRequests = computed(() => {
    let rows = props.requests;

    if (activeTab.value === 'mine') rows = rows.filter((r) => isMine(r));
    else if (activeTab.value === 'open') rows = rows.filter((r) => r.status === 'pending' && !isMine(r));
    else if (activeTab.value === 'fulfilled') rows = rows.filter((r) => r.status === 'fulfilled');

    if (filterType.value !== ALL_TYPES) rows = rows.filter((r) => r.crop_type === filterType.value);
    if (filterGrade.value !== ALL_GRADES) rows = rows.filter((r) => r.grade === filterGrade.value);

    if (sortBy.value === 'Newest First') return [...rows].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    if (sortBy.value === 'Largest Quantity') return [...rows].sort((a, b) => b.quantity - a.quantity);
    return rows;
});

const pageSize = 10;
const currentPage = ref(1);
const pagedRequests = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredRequests.value.slice(start, start + pageSize);
});
const rangeStart = computed(() => filteredRequests.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize + 1);
const rangeEnd = computed(() => Math.min(currentPage.value * pageSize, filteredRequests.value.length));

function setTab(key) {
    activeTab.value = key;
    currentPage.value = 1;
}

/* ── Row actions ───────────────────────────────────────────────────────── */
function respond(item, status) {
    router.post(route('buy.respond', item.id), { status }, { preserveScroll: true });
}

const deleteOpen = ref(false);
const pendingDelete = ref(null);

function requestDelete(item) {
    pendingDelete.value = item;
    deleteOpen.value = true;
}

function confirmDelete() {
    if (!pendingDelete.value) return;
    router.delete(route('lot.request.destroy', pendingDelete.value.id), { preserveScroll: true });
    pendingDelete.value = null;
}
</script>

<template>
    <TradeLayout
        title="RFQs"
        badge="B2B Sourcing Desk"
        subtitle="Find coffee sourcing opportunities or request quotations from verified sellers with farm-to-cup traceability."
        :market-count="marketCount"
        :auction-count="auctionCount"
        :request-count="requestCount"
        :crop-type-options="cropTypes"
        :grade-options="grades"
    >
        <div class="rfq-page">
            <!-- ── KPI cards ─────────────────────────────────────────────── -->
            <div class="rfq-kpis">
                <div v-for="kpi in kpis" :key="kpi.key" class="rfq-kpi">
                    <div class="rfq-kpi__head">
                        <span class="rfq-kpi__label">{{ kpi.label }}</span>
                        <span class="material-symbols-outlined">{{ kpi.icon }}</span>
                    </div>
                    <div class="rfq-kpi__value">{{ kpi.value }}</div>
                    <p class="rfq-kpi__hint">{{ kpi.hint }}</p>
                </div>
            </div>

            <!-- ── Lifecycle strip ───────────────────────────────────────── -->
            <div class="rfq-lifecycle">
                <div class="rfq-lifecycle__steps">
                    <span class="rfq-lifecycle__label">RFQ Lifecycle:</span>
                    <span class="rfq-lifecycle__step">1. Submitted</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                    <span class="rfq-lifecycle__step">2. Seller Response</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                    <span class="rfq-lifecycle__step">3. Fulfilled</span>
                </div>
                <div class="rfq-lifecycle__contract">
                    Data contract: <strong>Buyer Request → Seller Response → Fulfilled Trade</strong>
                </div>
            </div>

            <!-- ── Segmented tabs + sort ─────────────────────────────────── -->
            <div class="rfq-toolbar">
                <div class="rfq-tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="rfq-tab"
                        :class="{ 'rfq-tab--active': activeTab === tab.key }"
                        @click="setTab(tab.key)"
                    >
                        {{ tab.label }} ({{ tab.count }})
                    </button>
                </div>
                <div class="rfq-sort">
                    <label>Sort by</label>
                    <el-select v-model="sortBy" class="rfq-el-select">
                        <el-option v-for="o in sortOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
            </div>

            <!-- ── Filter bar ────────────────────────────────────────────── -->
            <div class="rfq-filters">
                <div class="rfq-field">
                    <label>Coffee Type</label>
                    <el-select v-model="filterType" class="rfq-el-select">
                        <el-option v-for="o in filterTypeOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
                <div class="rfq-field">
                    <label>Grade</label>
                    <el-select v-model="filterGrade" class="rfq-el-select">
                        <el-option v-for="o in filterGradeOptions" :key="o" :label="o" :value="o" />
                    </el-select>
                </div>
            </div>

            <!-- ── RFQ table ─────────────────────────────────────────────── -->
            <div class="rfq-table-card">
                <div class="rfq-table-wrap">
                    <table class="rfq-table">
                        <thead>
                            <tr>
                                <th><span class="material-symbols-outlined">tag</span> RFQ</th>
                                <th><span class="material-symbols-outlined">storefront</span> Buyer</th>
                                <th><span class="material-symbols-outlined">coffee</span> Coffee Requirement</th>
                                <th><span class="material-symbols-outlined">scale</span> Quantity</th>
                                <th><span class="material-symbols-outlined">payments</span> Amount</th>
                                <th><span class="material-symbols-outlined">flag</span> Status</th>
                                <th><span class="material-symbols-outlined">event</span> Requested</th>
                                <th class="rfq-table__action-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody v-if="pagedRequests.length">
                            <tr v-for="item in pagedRequests" :key="item.id" class="rfq-row">
                                <td class="rfq-code">{{ rfqCode(item) }}</td>
                                <td>
                                    <div class="rfq-buyer__name">{{ item.user?.name || '—' }}</div>
                                    <span v-if="isMine(item)" class="rfq-tag">You</span>
                                </td>
                                <td>
                                    <div class="rfq-coffee__main">{{ item.crop_type }}</div>
                                    <div class="rfq-coffee__sub">{{ [item.variety, item.grade].filter(Boolean).join(' · ') || '—' }}</div>
                                </td>
                                <td class="rfq-mono">{{ fmt(item.quantity) }} KG</td>
                                <td class="rfq-mono">{{ item.amount ? `$${fmt(item.amount)}` : '—' }}</td>
                                <td><span class="rfq-status" :class="statusTone(item.status)">{{ statusLabel(item.status) }}</span></td>
                                <td class="rfq-mono">{{ fmtDate(item.created_at) }}</td>
                                <td class="rfq-table__action-col">
                                    <div class="rfq-row-actions">
                                        <Link :href="route('lot.request.show', item.id)" class="rfq-btn rfq-btn--outline">View</Link>
                                        <template v-if="!isMine(item) && item.status === 'pending'">
                                            <button type="button" class="rfq-btn rfq-btn--primary" @click="respond(item, 'approved')">Approve</button>
                                            <button type="button" class="rfq-btn rfq-btn--danger" @click="respond(item, 'rejected')">Reject</button>
                                        </template>
                                        <button v-else-if="!isMine(item) && item.status === 'approved'" type="button" class="rfq-btn rfq-btn--primary" @click="respond(item, 'fulfilled')">Mark Fulfilled</button>
                                        <button v-if="isMine(item)" type="button" class="rfq-icon-btn" title="Delete" @click="requestDelete(item)">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="8" class="rfq-empty">No requests for quote match your filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rfq-table-footer">
                    <div class="rfq-table-footer__count">
                        Showing <strong>{{ rangeStart }}–{{ rangeEnd }}</strong> of <strong>{{ filteredRequests.length }}</strong> requests for quote
                    </div>
                    <el-pagination
                        v-if="filteredRequests.length > pageSize"
                        v-model:current-page="currentPage"
                        :page-size="pageSize"
                        :total="filteredRequests.length"
                        layout="prev, pager, next"
                        background
                        class="rfq-pagination"
                    />
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model="deleteOpen"
            eyebrow="Trade"
            title="Delete Request for Quote"
            :message="pendingDelete ? `Delete this ${pendingDelete.crop_type} request? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDelete"
        />
    </TradeLayout>
</template>

<style scoped>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

.rfq-page { display: flex; flex-direction: column; gap: 18px; }

/* ── KPI cards ────────────────────────────────────────────────────────── */
.rfq-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.rfq-kpi { padding: 16px; border-radius: var(--dp-card-radius); border: 1px solid var(--dp-outline-variant); background: var(--dp-surface); }
.rfq-kpi__head { display: flex; align-items: center; justify-content: space-between; }
.rfq-kpi__label { font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant); }
.rfq-kpi__head .material-symbols-outlined { font-size: 18px; color: var(--dp-on-surface-variant); }
.rfq-kpi__value { font-family: var(--dp-font-mono); font-size: 1.6rem; font-weight: 800; color: var(--dp-on-surface); margin-top: 6px; }
.rfq-kpi__hint { font-size: 11.5px; color: var(--dp-on-surface-variant); margin: 2px 0 0; }

/* ── Lifecycle strip ──────────────────────────────────────────────────── */
.rfq-lifecycle {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
    padding: 12px 16px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant);
}
.rfq-lifecycle__steps { display: flex; align-items: center; gap: 8px; font-family: var(--dp-font-mono); font-size: 11.5px; flex-wrap: wrap; }
.rfq-lifecycle__label { font-weight: 700; color: var(--dp-on-surface); margin-right: 2px; }
.rfq-lifecycle__step { padding: 3px 9px; border-radius: 6px; background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface-variant); font-weight: 600; }
.rfq-lifecycle__steps .material-symbols-outlined { font-size: 14px; color: var(--dp-on-surface-variant); }
.rfq-lifecycle__contract { font-size: 11.5px; color: var(--dp-on-surface-variant); }
.rfq-lifecycle__contract strong { color: var(--dp-on-surface); }

/* ── Segmented tabs + sort ────────────────────────────────────────────── */
.rfq-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--dp-outline-variant); padding-bottom: 10px; }
.rfq-tabs { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.rfq-tab {
    padding: 8px 14px; border-radius: 8px; border: none; background: transparent; cursor: pointer;
    font-family: inherit; font-size: 12.5px; font-weight: 700; color: var(--dp-on-surface-variant); white-space: nowrap;
    transition: background .15s ease, color .15s ease;
}
.rfq-tab:hover:not(.rfq-tab--active) { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.rfq-tab--active { background: var(--dp-primary); color: var(--dp-on-primary); }
.rfq-sort { display: flex; align-items: center; gap: 8px; }
.rfq-sort label { font-size: 11.5px; font-weight: 600; color: var(--dp-on-surface-variant); white-space: nowrap; }
.rfq-el-select { width: 170px; }
.rfq-el-select :deep(.el-select__wrapper) { border-radius: 6px; box-shadow: 0 0 0 1px var(--dp-outline-variant) inset; background: var(--dp-surface); min-height: 30px; font-size: 12px; font-family: inherit; }
.rfq-el-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 2px var(--dp-primary) inset; }

/* ── Filter bar ───────────────────────────────────────────────────────── */
.rfq-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 220px)); gap: 12px; padding: 12px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant); }
.rfq-field label { display: block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); margin-bottom: 4px; }
.rfq-field .rfq-el-select { width: 100%; }

/* ── Table ────────────────────────────────────────────────────────────── */
.rfq-table-card { border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); overflow: hidden; background: var(--dp-surface); }
.rfq-table-wrap { overflow-x: auto; }
.rfq-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
.rfq-table thead tr { background: var(--dp-surface-container-low); border-bottom: 1px solid var(--dp-outline-variant); }
.rfq-table th { padding: 9px 14px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface-variant); white-space: nowrap; }
.rfq-table th .material-symbols-outlined { font-size: 14px; vertical-align: -2px; margin-right: 3px; }
.rfq-table__action-col { text-align: right; }
.rfq-row { border-bottom: 1px solid var(--dp-outline-variant); transition: background .15s ease; }
.rfq-row:last-child { border-bottom: none; }
.rfq-row:hover { background: var(--dp-surface-container-low); }
.rfq-table td { padding: 10px 14px; vertical-align: middle; color: var(--dp-on-surface-variant); }
.rfq-mono { font-family: var(--dp-font-mono); font-weight: 600; color: var(--dp-on-surface); }

.rfq-code { font-family: var(--dp-font-mono); font-weight: 700; color: var(--dp-primary); }
.rfq-buyer__name { font-weight: 700; color: var(--dp-on-surface); }
.rfq-tag { display: inline-block; margin-top: 2px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; padding: 1.5px 6px; border-radius: 4px; background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.rfq-coffee__main { font-weight: 700; color: var(--dp-on-surface); }
.rfq-coffee__sub { font-size: 11.5px; color: var(--dp-on-surface-variant); margin-top: 2px; }

.rfq-status { display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; padding: 2px 8px; border-radius: 999px; }
.rfq-status.is-good { color: #16A34A; background: #E9F9EE; }
.rfq-status.is-info { color: #1D4ED8; background: #EFF6FF; }
.rfq-status.is-bad { color: var(--dp-error); background: var(--dp-error-container); }
.rfq-status.is-warn { color: #92400E; background: #fef3c7; }

.rfq-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 6px; flex-wrap: wrap; }
.rfq-btn {
    display: inline-flex; align-items: center; height: 28px; padding: 0 10px; border-radius: 8px;
    font-family: inherit; font-size: 11.5px; font-weight: 700; cursor: pointer; border: none; white-space: nowrap;
    text-decoration: none; transition: background .15s ease, opacity .15s ease;
}
.rfq-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.rfq-btn--primary:hover { opacity: .88; }
.rfq-btn--outline { background: var(--dp-surface); color: var(--dp-on-surface); border: 1px solid var(--dp-outline-variant); }
.rfq-btn--outline:hover { background: var(--dp-surface-container-low); }
.rfq-btn--danger { background: var(--dp-error-container); color: var(--dp-error); }
.rfq-btn--danger:hover { opacity: .85; }
.rfq-icon-btn {
    display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: none; border-radius: 8px;
    background: transparent; color: var(--dp-on-surface-variant); cursor: pointer;
}
.rfq-icon-btn .material-symbols-outlined { font-size: 16px; }
.rfq-icon-btn:hover { background: var(--dp-error-container); color: var(--dp-error); }

.rfq-empty { text-align: center; padding: 32px 16px; color: var(--dp-on-surface-variant); font-size: 13px; }

.rfq-table-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-top: 1px solid var(--dp-outline-variant); background: var(--dp-surface); }
.rfq-table-footer__count { font-size: 12px; color: var(--dp-on-surface-variant); }
.rfq-table-footer__count strong { color: var(--dp-on-surface); }
.rfq-pagination :deep(.el-pager li) { background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); border-radius: 6px; margin: 0 2px; font-family: var(--dp-font-mono); color: var(--dp-on-surface); }
.rfq-pagination :deep(.el-pager li.is-active) { background: var(--dp-primary); border-color: var(--dp-primary); color: var(--dp-on-primary); }
.rfq-pagination :deep(.btn-prev), .rfq-pagination :deep(.btn-next) { background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); border-radius: 6px; }

@media (max-width: 1100px) {
    .rfq-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .rfq-kpis { grid-template-columns: 1fr; }
}
</style>
