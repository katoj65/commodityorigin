<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { View, CircleCheck, Check, Clock } from '@element-plus/icons-vue';

const props = defineProps({
    inspections: { type: Array, default: () => [] },
    authUserId: { type: Number, default: null },
    isAdmin: { type: Boolean, default: false },
});

/* ── Perspective helpers ─────────────────────────────────────────────── */
function amBuyer(inspection) {
    return inspection.buyer_id === props.authUserId;
}

function amSeller(inspection) {
    return inspection.seller_id === props.authUserId;
}

function needsMyAction(inspection) {
    if (inspection.status !== 'pending') return false;
    if (amBuyer(inspection) && !inspection.buyer_acknowledged_at) return true;
    if (props.isAdmin && inspection.buyer_acknowledged_at) return true;

    return false;
}

/* ── Filters ─────────────────────────────────────────────────────────── */
const activeFilter = ref('all');
const filters = [
    { key: 'all', label: 'All' },
    { key: 'requested', label: 'Requested by Me' },
    { key: 'awaiting', label: 'Awaiting My Action' },
    { key: 'completed', label: 'Completed' },
];

const sortedInspections = computed(() => [...props.inspections].sort((a, b) => b.created_at.localeCompare(a.created_at)));

function matchesFilter(key, inspection) {
    switch (key) {
        case 'requested': return amSeller(inspection);
        case 'awaiting': return needsMyAction(inspection);
        case 'completed': return inspection.status === 'completed';
        default: return true;
    }
}

const filteredInspections = computed(() => sortedInspections.value.filter((i) => matchesFilter(activeFilter.value, i)));

function tabCount(key) {
    return sortedInspections.value.filter((i) => matchesFilter(key, i)).length;
}

/* ── KPIs ────────────────────────────────────────────────────────────── */
const kpis = computed(() => ({
    total: sortedInspections.value.length,
    requested: tabCount('requested'),
    awaiting: tabCount('awaiting'),
    completed: tabCount('completed'),
}));

/* ── Display helpers ─────────────────────────────────────────────────── */
function statusLabel(inspection) {
    if (inspection.status === 'pending' && inspection.buyer_acknowledged_at) return 'Under Inspection';

    return inspection.status.charAt(0).toUpperCase() + inspection.status.slice(1);
}

function statusTone(status) {
    return {
        pending: 'insp-badge--amber',
        completed: 'insp-badge--green',
    }[status] ?? 'insp-badge--muted';
}

function formatDate(dateTime) {
    if (!dateTime) return '—';

    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
    });
}

/* ── Actions ─────────────────────────────────────────────────────────── */
function openOrder(inspection) {
    router.visit(route('orders.show', inspection.order_id));
}

function acknowledgeInspection(inspection) {
    router.post(route('orders.inspections.acknowledge', [inspection.order_id, inspection.id]), {}, { preserveScroll: true });
}

function completeInspection(inspection) {
    router.post(route('orders.inspections.complete', [inspection.order_id, inspection.id]), {}, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Inspections" full-width flush :show-banner="false">
        <Head title="Inspections" />

        <div class="insp-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="insp-page-header">
                <div class="insp-page-header__left">
                    <div class="insp-kicker">Quality · Bean Origin</div>
                    <h1 class="insp-title">Inspections</h1>
                    <p class="insp-subtitle">Track every coffee quality inspection, from the seller's request through the admin's completion sign-off.</p>
                </div>
            </div>

            <!-- ── Overview strip ────────────────────────────────────────── -->
            <div class="insp-kpi-strip">
                <div class="insp-kpi">
                    <span class="insp-kpi__label">Total Inspections</span>
                    <strong class="insp-kpi__val">{{ kpis.total }}</strong>
                </div>
                <div class="insp-kpi">
                    <span class="insp-kpi__label">Requested by Me</span>
                    <strong class="insp-kpi__val">{{ kpis.requested }}</strong>
                </div>
                <div class="insp-kpi">
                    <span class="insp-kpi__label">Awaiting My Action</span>
                    <strong class="insp-kpi__val" :class="kpis.awaiting ? 'insp-text-amber' : ''">{{ kpis.awaiting }}</strong>
                </div>
                <div class="insp-kpi">
                    <span class="insp-kpi__label">Completed</span>
                    <strong class="insp-kpi__val insp-text-green">{{ kpis.completed }}</strong>
                </div>
            </div>

            <div class="insp-body">
                <div class="insp-section">
                    <el-tabs v-model="activeFilter" class="insp-tabs">
                        <el-tab-pane v-for="f in filters" :key="f.key" :name="f.key">
                            <template #label>
                                <span class="insp-tab-label">
                                    {{ f.label }}
                                    <span v-if="tabCount(f.key)" class="insp-tab-count">{{ tabCount(f.key) }}</span>
                                </span>
                            </template>
                        </el-tab-pane>
                    </el-tabs>

                    <el-table
                        :data="filteredInspections"
                        class="insp-table"
                        empty-text="No inspections in this view."
                        @row-click="openOrder"
                    >
                        <el-table-column label="Order" width="170">
                            <template #default="{ row }">
                                <div class="insp-cell-order">
                                    <span class="insp-cell-order__num">{{ row.order_number }}</span>
                                    <span class="insp-cell-order__crop">{{ row.crop_type }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="Buyer" min-width="150">
                            <template #default="{ row }">
                                <span class="insp-ack">
                                    <el-icon :size="12" :class="row.buyer_acknowledged_at ? 'insp-ack__icon--done' : 'insp-ack__icon'">
                                        <component :is="row.buyer_acknowledged_at ? Check : Clock" />
                                    </el-icon>
                                    {{ row.buyer_name }}
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Seller" min-width="150">
                            <template #default="{ row }">{{ row.seller_name }}</template>
                        </el-table-column>
                        <el-table-column label="Status" width="110">
                            <template #default="{ row }">
                                <span class="insp-badge" :class="statusTone(row.status)">{{ statusLabel(row) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Requested" width="110">
                            <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                        </el-table-column>
                        <el-table-column label="Completed" width="110">
                            <template #default="{ row }">{{ formatDate(row.completed_at) }}</template>
                        </el-table-column>
                        <el-table-column label="" width="170" align="right">
                            <template #default="{ row }">
                                <div class="insp-row-actions">
                                    <button
                                        v-if="row.status === 'pending' && amBuyer(row) && !row.buyer_acknowledged_at"
                                        type="button"
                                        class="insp-btn-primary"
                                        @click.stop="acknowledgeInspection(row)"
                                    >
                                        <el-icon :size="13"><CircleCheck /></el-icon> Acknowledge
                                    </button>
                                    <button
                                        v-else-if="row.status === 'pending' && isAdmin && row.buyer_acknowledged_at"
                                        type="button"
                                        class="insp-btn-primary"
                                        @click.stop="completeInspection(row)"
                                    >
                                        <el-icon :size="13"><CircleCheck /></el-icon> Complete
                                    </button>
                                    <button
                                        type="button"
                                        class="insp-icon-btn"
                                        aria-label="View order"
                                        title="View order"
                                        @click.stop="openOrder(row)"
                                    >
                                        <el-icon :size="15"><View /></el-icon>
                                    </button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.insp-page {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.insp-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.75rem 1.5rem 0;
}

.insp-page-header__left {
    max-width: 560px;
}

.insp-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.insp-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.insp-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

/* ── Overview strip ──────────────────────────────────────────────────── */
.insp-kpi-strip {
    display: flex;
    overflow-x: auto;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-top: 1.5rem;
    scrollbar-width: none;
}

.insp-kpi-strip::-webkit-scrollbar { display: none; }

.insp-kpi {
    flex: 1;
    min-width: 130px;
    padding: 1rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
    border-right: 1px solid var(--border);
}

.insp-kpi:last-child { border-right: none; }

.insp-kpi__label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
}

.insp-kpi__val {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--on-surface);
    letter-spacing: -0.01em;
}

.insp-text-green { color: #166534; }
.insp-text-amber { color: #92400e; }

/* ── Body ────────────────────────────────────────────────────────────── */
.insp-body {
    padding: 1.5rem 0 3rem;
}

.insp-section {
    background: transparent;
}

/* ── Category tabs ───────────────────────────────────────────────────── */
.insp-tabs { padding: 0 1.5rem; }
.insp-tabs :deep(.el-tabs__header) { margin: 0; }
.insp-tabs :deep(.el-tabs__nav-wrap::after) { background-color: var(--border); }
.insp-tabs :deep(.el-tabs__item) {
    font-weight: 700;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    height: 44px;
}
.insp-tabs :deep(.el-tabs__item.is-active) { color: var(--green); }
.insp-tabs :deep(.el-tabs__active-bar) { background-color: var(--green); }

.insp-tab-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.insp-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    font-size: 0.625rem;
    font-weight: 800;
}

/* ── Table ───────────────────────────────────────────────────────────── */
.insp-table {
    --el-table-border-color: var(--border);
    --el-table-header-bg-color: var(--surface-low);
    --el-table-header-text-color: var(--on-surface-var);
    font-family: 'Manrope', system-ui, sans-serif;
}

.insp-table :deep(.el-table__header) th {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.insp-table :deep(.el-table__row) { cursor: pointer; }
.insp-table :deep(.el-table__inner-wrapper::before) { display: none; }
.insp-table :deep(.el-table__header-wrapper th:first-child .cell),
.insp-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.5rem; }
.insp-table :deep(.el-table__header-wrapper th:last-child .cell),
.insp-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.5rem; }

.insp-cell-order {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.insp-cell-order__num {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
    font-family: 'IBM Plex Mono', monospace;
}

.insp-cell-order__crop {
    font-size: 0.75rem;
    color: var(--on-surface-var);
}

.insp-ack {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    color: var(--on-surface);
}

.insp-ack__icon { color: #d1a94a; }
.insp-ack__icon--done { color: #166534; }

.insp-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 4px 10px;
    white-space: nowrap;
}

.insp-badge--green { background: #dcfce7; color: #166534; }
.insp-badge--amber { background: #fef3c7; color: #92400e; }
.insp-badge--muted { background: #f3f4f6; color: #6b7280; }

.insp-row-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.insp-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 6px 10px;
    cursor: pointer;
    transition: opacity 0.15s ease;
    white-space: nowrap;
}

.insp-btn-primary:hover { opacity: 0.9; }

.insp-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    background: none;
    color: #6b7280;
    cursor: pointer;
    transition: background 0.12s, color 0.12s;
}

.insp-icon-btn:hover { background: var(--surface-low); color: var(--on-surface); }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .insp-page-header { padding: 1.25rem 1.25rem 0; }
    .insp-body { padding: 1.25rem 0 3rem; }
    .insp-tabs { padding: 0 1.25rem; }
    .insp-table :deep(.el-table__header-wrapper th:first-child .cell),
    .insp-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
    .insp-table :deep(.el-table__header-wrapper th:last-child .cell),
    .insp-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }
}
</style>
