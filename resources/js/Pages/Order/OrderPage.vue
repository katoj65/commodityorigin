<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Plus, Close, Files, Box, ShoppingCart,
} from '@element-plus/icons-vue';

const props = defineProps({
    orders: { type: Array, default: () => [] },
    openOrders: { type: Array, default: () => [] },
    authUserId: { type: Number, default: null },
});

/* ── Perspective helpers ─────────────────────────────────────────────── */
function isBuyer(order) {
    return order.buyer_id === props.authUserId;
}

function isSeller(order) {
    return order.seller_id === props.authUserId;
}

function isParty(order) {
    return isBuyer(order) || isSeller(order);
}

// The party who originally posted the order: the buyer on a "request",
// the seller on an "offer".
function isCreator(order) {
    return order.type === 'offer' ? isSeller(order) : isBuyer(order);
}

function counterpartName(order) {
    return isBuyer(order) ? order.seller_name : order.buyer_name;
}

function perspectiveLabel(order) {
    if (order.status === 'open') {
        if (isCreator(order)) return 'Placed';
        return order.type === 'offer' ? 'Offer' : 'Open';
    }
    if (!isParty(order)) return order.type === 'offer' ? 'Offer' : 'Request';
    return isCreator(order) ? 'Placed' : 'Received';
}

function partyLabel(order) {
    if (order.status === 'open') {
        if (isCreator(order)) return order.type === 'offer' ? 'Awaiting a buyer' : 'Awaiting a seller';
        return order.type === 'offer' ? `From ${order.seller_name}` : `From ${order.buyer_name}`;
    }
    if (!isParty(order)) return `${order.buyer_name} ↔ ${order.seller_name}`;
    return isBuyer(order) ? `To ${order.seller_name}` : `From ${order.buyer_name}`;
}

/* ── Filters ─────────────────────────────────────────────────────────── */
const activeFilter = ref('all');
const filters = [
    { key: 'all', label: 'All' },
    { key: 'placed', label: 'Placed by Me' },
    { key: 'received', label: 'Received' },
    { key: 'marketplace', label: 'Marketplace' },
    { key: 'pending', label: 'Pending' },
    { key: 'delivered', label: 'Delivered' },
    { key: 'cancelled', label: 'Cancelled' },
    { key: 'withdrawn', label: 'Withdrawn' },
];

// The full public order book: every order the viewer placed or is
// fulfilling, plus every other request and offer on the marketplace —
// merged so every tab (including "All") can see the whole platform.
const allOrders = computed(() => {
    const seen = new Set();
    const merged = [];

    for (const order of [...props.orders, ...props.openOrders]) {
        if (seen.has(order.id)) continue;
        seen.add(order.id);
        merged.push(order);
    }

    return merged.sort((a, b) => b.created_at.localeCompare(a.created_at));
});

function matchesFilter(key, order) {
    switch (key) {
        case 'placed': return isCreator(order);
        case 'received': return isParty(order) && !isCreator(order);
        case 'marketplace': return !isParty(order);
        case 'pending': return order.status === 'pending';
        case 'delivered': return order.status === 'delivered';
        case 'cancelled': return order.status === 'cancelled';
        case 'withdrawn': return order.status === 'withdrawn';
        default: return true;
    }
}

const filteredOrders = computed(() => allOrders.value.filter((o) => matchesFilter(activeFilter.value, o)));

function tabCount(key) {
    return allOrders.value.filter((o) => matchesFilter(key, o)).length;
}

/* ── KPIs ────────────────────────────────────────────────────────────── */
const kpis = computed(() => ({
    total: allOrders.value.length,
    placed: tabCount('placed'),
    received: tabCount('received'),
    marketplace: tabCount('marketplace'),
    delivered: tabCount('delivered'),
}));

function typeLabel(type) {
    return type === 'offer' ? 'Offer' : 'Request';
}

function typeTone(type) {
    return type === 'offer' ? 'ord-badge--blue' : 'ord-badge--muted';
}

/* ── Display helpers ─────────────────────────────────────────────────── */
function statusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function statusTone(status) {
    return {
        open: 'ord-badge--muted',
        pending: 'ord-badge--amber',
        confirmed: 'ord-badge--blue',
        processing: 'ord-badge--blue',
        shipped: 'ord-badge--blue',
        delivered: 'ord-badge--green',
        cancelled: 'ord-badge--red',
        withdrawn: 'ord-badge--muted',
    }[status] ?? 'ord-badge--muted';
}

function formatMoney(amount, currency) {
    return `${currency} ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

/* ── Create-order dialog ─────────────────────────────────────────────── */
const createOpen = ref(false);
const form = useForm({
    type: 'request',
    crop_type: '',
    variety: '',
    grade: '',
    quantity: null,
    unit_price: null,
    notes: '',
});

const isOfferForm = computed(() => form.type === 'offer');

const cropSelectOptions = ['Arabica', 'Robusta'];
const otherCropMode = ref(false);
const cropSelectValue = ref('');

function handleCropSelect(value) {
    if (value === '__other__') {
        otherCropMode.value = true;
        form.crop_type = '';
    } else {
        otherCropMode.value = false;
        form.crop_type = value;
    }
}

const estimatedTotal = computed(() => {
    const qty = Number(form.quantity) || 0;
    const price = Number(form.unit_price) || 0;
    return qty * price;
});

function openCreateDialog(type = 'request') {
    form.reset();
    form.clearErrors();
    form.type = type;
    otherCropMode.value = false;
    cropSelectValue.value = '';
    createOpen.value = true;
}

function saveOrder() {
    form.clearErrors();

    if (!form.crop_type.trim()) form.setError('crop_type', 'Crop type is required.');
    if (!form.quantity) form.setError('quantity', 'Quantity is required.');
    if (!form.unit_price) form.setError('unit_price', 'Unit price is required.');
    if (Object.keys(form.errors).length) return;

    form.post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: () => { createOpen.value = false; },
    });
}

/* ── Row navigation ──────────────────────────────────────────────────── */
function openOrder(order) {
    router.visit(route('orders.show', order.id));
}
</script>

<template>
    <AppLayout title="Orders" full-width flush :show-banner="false">
        <Head title="Orders" />

        <div class="ord-page">

            <!-- ── Page Header ───────────────────────────────────────────── -->
            <div class="ord-page-header">
                <div class="ord-page-header__left">
                    <div class="ord-kicker">Trade · Bean Origin</div>
                    <h1 class="ord-title">Orders</h1>
                    <p class="ord-subtitle">Track every coffee order you've placed or received, from confirmation through delivery.</p>
                </div>
                <div class="ord-page-header__actions">
                    <el-button-group class="ord-create-group">
                        <el-button class="ord-create-group__btn" @click="openCreateDialog('request')">
                            <el-icon><ShoppingCart /></el-icon> Request
                        </el-button>
                        <el-button class="ord-create-group__btn ord-create-group__btn--offer" @click="openCreateDialog('offer')">
                            <el-icon><Box /></el-icon> Offer
                        </el-button>
                    </el-button-group>
                </div>
            </div>

            <!-- ── Overview strip ────────────────────────────────────────── -->
            <div class="ord-kpi-strip">
                <div class="ord-kpi">
                    <span class="ord-kpi__label">Total Orders</span>
                    <strong class="ord-kpi__val">{{ kpis.total }}</strong>
                </div>
                <div class="ord-kpi">
                    <span class="ord-kpi__label">Placed by Me</span>
                    <strong class="ord-kpi__val">{{ kpis.placed }}</strong>
                </div>
                <div class="ord-kpi">
                    <span class="ord-kpi__label">Received</span>
                    <strong class="ord-kpi__val">{{ kpis.received }}</strong>
                </div>
                <div class="ord-kpi">
                    <span class="ord-kpi__label">Marketplace</span>
                    <strong class="ord-kpi__val" :class="kpis.marketplace ? 'ord-text-amber' : ''">{{ kpis.marketplace }}</strong>
                </div>
                <div class="ord-kpi">
                    <span class="ord-kpi__label">Delivered</span>
                    <strong class="ord-kpi__val ord-text-green">{{ kpis.delivered }}</strong>
                </div>
            </div>

            <div class="ord-body">
                <div class="ord-section">
                    <el-tabs v-model="activeFilter" class="ord-tabs">
                        <el-tab-pane v-for="f in filters" :key="f.key" :name="f.key">
                            <template #label>
                                <span class="ord-tab-label">
                                    {{ f.label }}
                                    <span v-if="tabCount(f.key)" class="ord-tab-count">{{ tabCount(f.key) }}</span>
                                </span>
                            </template>
                        </el-tab-pane>
                    </el-tabs>

                    <el-table
                        :data="filteredOrders"
                        class="ord-table"
                        empty-text="No orders in this view."
                        @row-click="openOrder"
                    >
                        <el-table-column label="Order" width="170">
                            <template #default="{ row }">
                                <div class="ord-cell-order">
                                    <span class="ord-cell-order__num">{{ row.order_number }}</span>
                                    <span class="ord-row__perspective">{{ perspectiveLabel(row) }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="Type" width="100">
                            <template #default="{ row }">
                                <span class="ord-badge" :class="typeTone(row.type)">{{ typeLabel(row.type) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Party" min-width="180">
                            <template #default="{ row }">
                                <span :class="row.status === 'open' && isCreator(row) ? 'ord-muted-cell' : ''">{{ partyLabel(row) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Coffee" min-width="160">
                            <template #default="{ row }">
                                {{ row.crop_type }}<template v-if="row.variety"> — {{ row.variety }}</template>
                            </template>
                        </el-table-column>
                        <el-table-column label="Quantity" width="110">
                            <template #default="{ row }">{{ row.quantity.toLocaleString() }} kg</template>
                        </el-table-column>
                        <el-table-column label="Amount" width="140">
                            <template #default="{ row }"><span class="ord-amount">{{ formatMoney(row.total_amount, row.currency) }}</span></template>
                        </el-table-column>
                        <el-table-column label="Status" width="110" align="right">
                            <template #default="{ row }">
                                <span class="ord-badge" :class="statusTone(row.status)">{{ statusLabel(row.status) }}</span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </div>

        <!-- ── Create Order modal ────────────────────────────────────────── -->
        <el-dialog
            v-model="createOpen"
            width="480px"
            destroy-on-close
            align-center
            :show-close="false"
            class="ord-modal"
        >
            <template #header>
                <div class="ord-modal__head">
                    <div class="ord-modal__head-icon">
                        <el-icon :size="18"><component :is="isOfferForm ? Box : ShoppingCart" /></el-icon>
                    </div>
                    <div class="ord-modal__head-text">
                        <div class="ord-modal__eyebrow">{{ isOfferForm ? 'Sell' : 'Buy' }}</div>
                        <div class="ord-modal__title">{{ isOfferForm ? 'New Offer' : 'New Request' }}</div>
                    </div>
                    <button type="button" class="ord-modal__close" aria-label="Close" @click="createOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="ord-modal__body">
                <div class="ord-field">
                    <label class="ord-field__label">Crop Type</label>
                    <el-select
                        v-model="cropSelectValue"
                        placeholder="Select crop type…"
                        style="width:100%"
                        class="ord-input"
                        :class="{ 'ord-input--error': form.errors.crop_type }"
                        @change="handleCropSelect"
                    >
                        <el-option v-for="opt in cropSelectOptions" :key="opt" :label="opt" :value="opt" />
                        <el-option label="Other" value="__other__" />
                    </el-select>
                    <el-input
                        v-if="otherCropMode"
                        v-model="form.crop_type"
                        placeholder="e.g. Liberica, Excelsa"
                        class="ord-input mt-2"
                        :class="{ 'ord-input--error': form.errors.crop_type }"
                        maxlength="255"
                    />
                    <span v-if="form.errors.crop_type" class="ord-field__error">{{ form.errors.crop_type }}</span>
                </div>

                <div class="ord-field-row">
                    <div class="ord-field">
                        <label class="ord-field__label">Variety</label>
                        <el-input v-model="form.variety" placeholder="Optional" class="ord-input" />
                    </div>
                    <div class="ord-field">
                        <label class="ord-field__label">Grade</label>
                        <el-input v-model="form.grade" placeholder="Optional" class="ord-input" />
                    </div>
                </div>

                <div class="ord-field-row">
                    <div class="ord-field">
                        <label class="ord-field__label">Quantity (kg)</label>
                        <el-input v-model="form.quantity" type="number" min="0" step="0.01" placeholder="e.g. 500" class="ord-input ord-input--number" :class="{ 'ord-input--error': form.errors.quantity }" />
                        <span v-if="form.errors.quantity" class="ord-field__error">{{ form.errors.quantity }}</span>
                    </div>
                    <div class="ord-field">
                        <label class="ord-field__label">Unit Price ($/kg)</label>
                        <el-input v-model="form.unit_price" type="number" min="0" step="0.01" placeholder="e.g. 4.50" class="ord-input ord-input--number" :class="{ 'ord-input--error': form.errors.unit_price }" />
                        <span v-if="form.errors.unit_price" class="ord-field__error">{{ form.errors.unit_price }}</span>
                    </div>
                </div>

                <div v-if="estimatedTotal > 0" class="ord-estimate">
                    <span>Estimated Total</span>
                    <strong>${{ estimatedTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</strong>
                </div>

                <div class="ord-field">
                    <label class="ord-field__label"><el-icon :size="12"><Files /></el-icon> Notes</label>
                    <el-input v-model="form.notes" type="textarea" :rows="2" :placeholder="isOfferForm ? 'Optional notes for the buyer' : 'Optional notes for the seller'" class="ord-input" />
                </div>
            </div>

            <template #footer>
                <div class="ord-modal__footer">
                    <button type="button" class="ord-btn-outline" @click="createOpen = false">Cancel</button>
                    <button type="button" class="ord-btn-primary" :disabled="form.processing" @click="saveOrder">
                        <el-icon v-if="!form.processing"><Plus /></el-icon>
                        {{ form.processing ? 'Posting…' : (isOfferForm ? 'Post Offer' : 'Create Request') }}
                    </button>
                </div>
            </template>
        </el-dialog>
    </AppLayout>
</template>

<style scoped>
.ord-page {
    --green: #004532;
    --green-dark: #002e20;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.ord-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.75rem 1.5rem 0;
}

.ord-page-header__left {
    max-width: 560px;
}

.ord-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.ord-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.ord-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

.ord-page-header__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 4px;
}

/* ── Overview strip ──────────────────────────────────────────────────── */
.ord-kpi-strip {
    display: flex;
    gap: 0;
    overflow-x: auto;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-top: 1.5rem;
    scrollbar-width: none;
}

.ord-kpi-strip::-webkit-scrollbar {
    display: none;
}

.ord-kpi {
    flex: 1;
    min-width: 130px;
    padding: 1rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
    border-right: 1px solid var(--border);
}

.ord-kpi:last-child {
    border-right: none;
}

.ord-kpi__label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
}

.ord-kpi__val {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--on-surface);
    letter-spacing: -0.01em;
}

.ord-text-green { color: #166534; }
.ord-text-amber { color: #92400e; }
.ord-text-red { color: #b91c1c; }

/* ── Body ────────────────────────────────────────────────────────────── */
.ord-body {
    padding: 1.5rem 0 3rem;
}

/* ── Section ─────────────────────────────────────────────────────────── */
.ord-section {
    background: transparent;
}

/* ── Create button group (page header) ────────────────────────────────── */
.ord-create-group {
    display: inline-flex;
    flex-shrink: 0;
}

.ord-create-group__btn {
    font-weight: 700 !important;
    font-size: 0.8125rem !important;
    border-color: var(--green) !important;
    color: var(--green) !important;
}

.ord-create-group__btn :deep(.el-icon) {
    margin-right: 5px;
}

.ord-create-group__btn:hover {
    background: rgba(0, 69, 50, 0.08) !important;
    color: var(--green) !important;
}

.ord-create-group__btn--offer {
    background: var(--green) !important;
    color: #fff !important;
}

.ord-create-group__btn--offer:hover {
    opacity: 0.9;
    color: #fff !important;
}

/* ── Category tabs ───────────────────────────────────────────────────── */
.ord-tabs {
    padding: 0 1.5rem;
}

.ord-tabs :deep(.el-tabs__header) {
    margin: 0;
}

.ord-tabs :deep(.el-tabs__nav-wrap::after) {
    background-color: var(--border);
}

.ord-tabs :deep(.el-tabs__item) {
    font-weight: 700;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    height: 44px;
}

.ord-tabs :deep(.el-tabs__item.is-active) {
    color: var(--green);
}

.ord-tabs :deep(.el-tabs__active-bar) {
    background-color: var(--green);
}

.ord-tab-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.ord-tab-count {
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

/* ── Order table ─────────────────────────────────────────────────────── */
.ord-table {
    --el-table-border-color: var(--border);
    --el-table-header-bg-color: var(--surface-low);
    --el-table-header-text-color: var(--on-surface-var);
    font-family: 'Manrope', system-ui, sans-serif;
}

.ord-table :deep(.el-table__header) th {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.ord-table :deep(.el-table__row) {
    cursor: pointer;
}

.ord-table :deep(.el-table__inner-wrapper::before) {
    display: none;
}

.ord-table :deep(.el-table__header-wrapper th:first-child .cell),
.ord-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.5rem; }
.ord-table :deep(.el-table__header-wrapper th:last-child .cell),
.ord-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.5rem; }

.ord-cell-order {
    display: flex;
    flex-direction: column;
    gap: 3px;
    align-items: flex-start;
}

.ord-cell-order__num {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
    font-family: 'IBM Plex Mono', monospace;
}

.ord-muted-cell {
    color: var(--on-surface-var);
    font-style: italic;
}

.ord-amount {
    font-weight: 600;
    color: var(--on-surface);
}

.ord-row__perspective {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--green);
    background: rgba(0, 69, 50, 0.08);
    border-radius: 999px;
    padding: 2px 8px;
    align-self: flex-start;
}

.ord-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 4px 10px;
    flex-shrink: 0;
    white-space: nowrap;
}

.ord-badge--green { background: #dcfce7; color: #166534; }
.ord-badge--amber { background: #fef3c7; color: #92400e; }
.ord-badge--red { background: #fee2e2; color: #991b1b; }
.ord-badge--blue { background: #dbeafe; color: #1e40af; }
.ord-badge--muted { background: #f3f4f6; color: #6b7280; }

.ord-muted { color: var(--on-surface-var); font-size: 0.8125rem; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.ord-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.ord-btn-primary:hover { opacity: 0.9; }
.ord-btn-primary:disabled { opacity: 0.6; cursor: default; }

.ord-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.ord-btn-outline:hover { background: #f8fafc; }

/* ── New/Edit modal ────────────────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .ord-page's
   DOM subtree, so CSS custom properties defined on .ord-page do NOT
   cascade in. All colors below are literal hex values on purpose. */
:deep(.el-dialog.ord-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.ord-modal .el-dialog__header) {
    padding: 0;
    margin: 0;
}

:deep(.el-dialog.ord-modal .el-dialog__body) {
    padding: 0;
}

:deep(.el-dialog.ord-modal .el-dialog__footer) {
    padding: 0;
}

.ord-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.ord-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ord-modal__head-text {
    flex: 1;
    min-width: 0;
}

.ord-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.ord-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
    font-family: 'IBM Plex Mono', monospace;
}

.ord-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}

.ord-modal__close:hover { background: #e5e7eb; color: #111827; }

.ord-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.ord-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ord-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.ord-field__label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #374151;
}

.ord-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.ord-input--error :deep(.el-input__wrapper),
.ord-input--error :deep(.el-textarea__inner),
.ord-input--error :deep(.el-select__wrapper) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.ord-desc {
    font-size: 0.8125rem;
    color: #374151;
    line-height: 1.5;
    margin: 0;
}

.ord-input :deep(.el-input__wrapper),
.ord-input :deep(.el-textarea__inner) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.ord-input :deep(.el-input__wrapper:hover),
.ord-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.ord-input :deep(.el-input__wrapper.is-focus),
.ord-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.ord-input--number :deep(input[type='number']::-webkit-outer-spin-button),
.ord-input--number :deep(input[type='number']::-webkit-inner-spin-button) {
    -webkit-appearance: none;
    margin: 0;
}

.ord-input--number :deep(input[type='number']) {
    -moz-appearance: textfield;
}

.ord-estimate {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
    border: 1px solid #f3f4f6;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.8125rem;
    color: #6b7280;
}

.ord-estimate strong {
    font-size: 0.9375rem;
    color: #111827;
    font-weight: 800;
}

.ord-modal__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .ord-page-header { padding: 1.25rem 1.25rem 0; }
    .ord-body { padding: 1.25rem 0 3rem; }
    .ord-tabs { padding: 0 1.25rem; }
    .ord-table :deep(.el-table__header-wrapper th:first-child .cell),
    .ord-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
    .ord-table :deep(.el-table__header-wrapper th:last-child .cell),
    .ord-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }
    .ord-field-row { grid-template-columns: 1fr; }
}
</style>
