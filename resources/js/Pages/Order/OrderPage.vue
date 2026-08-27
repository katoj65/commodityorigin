<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    Plus, Close, Files, Box, ShoppingCart,
    Tickets, User, Coffee, Coin, Checked, Search, FolderOpened, List,
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

const search = ref('');

const filteredOrders = computed(() => {
    const term = search.value.trim().toLowerCase();

    return allOrders.value.filter((order) => {
        if (!matchesFilter(activeFilter.value, order)) return false;
        if (!term) return true;

        const haystack = [
            order.order_number, order.crop_type, order.variety,
            order.buyer_name, order.seller_name,
        ].filter(Boolean).join(' ').toLowerCase();
        return haystack.includes(term);
    });
});

const isSearching = computed(() => !!search.value.trim());

function tabCount(key) {
    return allOrders.value.filter((o) => matchesFilter(key, o)).length;
}

function resetFilters() {
    search.value = '';
    activeFilter.value = 'all';
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

function formatDate(dateTime) {
    if (!dateTime) return '—';
    const date = new Date(dateTime.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

/* ── Identity — deterministic initials + palette colour per counterparty
   name, so the Party column reads as distinct people instead of plain
   text. ─────────────────────────────────────────────────────────────── */
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
    <DesignPreviewLayout title="Orders">
        <Head title="Orders" />

        <div class="ord-page">

            <!-- ── Page Header ───────────────────────────────────────────── -->
            <div class="ord-page-header">
                <div class="ord-page-header__left">
                    <h1 class="ord-title">Orders</h1>
                    <p class="ord-subtitle">Track every coffee order you've placed or received, from confirmation through delivery.</p>
                </div>
                <div class="ord-page-header__actions">
                    <button type="button" class="ord-btn ord-btn--outline" @click="openCreateDialog('offer')">
                        <el-icon><Box /></el-icon> Post Offer
                    </button>
                    <button type="button" class="ord-btn ord-btn--primary" @click="openCreateDialog('request')">
                        <el-icon><Plus /></el-icon> New Request
                    </button>
                </div>
            </div>

            <!-- ── Overview tiles ───────────────────────────────────────────
                 Individual elevated cards, not a flat bordered strip — the
                 same floating-card language as the market listing page. -->
            <div class="ord-kpi-grid">
                <div class="ord-kpi">
                    <div class="ord-kpi__icon"><el-icon :size="16"><List /></el-icon></div>
                    <div class="ord-kpi__body">
                        <strong class="ord-kpi__val">{{ kpis.total }}</strong>
                        <span class="ord-kpi__label">Total Orders</span>
                    </div>
                </div>
                <div class="ord-kpi">
                    <div class="ord-kpi__icon"><el-icon :size="16"><ShoppingCart /></el-icon></div>
                    <div class="ord-kpi__body">
                        <strong class="ord-kpi__val">{{ kpis.placed }}</strong>
                        <span class="ord-kpi__label">Placed by Me</span>
                    </div>
                </div>
                <div class="ord-kpi">
                    <div class="ord-kpi__icon"><el-icon :size="16"><Box /></el-icon></div>
                    <div class="ord-kpi__body">
                        <strong class="ord-kpi__val">{{ kpis.received }}</strong>
                        <span class="ord-kpi__label">Received</span>
                    </div>
                </div>
                <div class="ord-kpi">
                    <div class="ord-kpi__icon"><el-icon :size="16"><Tickets /></el-icon></div>
                    <div class="ord-kpi__body">
                        <strong class="ord-kpi__val">{{ kpis.marketplace }}</strong>
                        <span class="ord-kpi__label">Marketplace</span>
                    </div>
                </div>
                <div class="ord-kpi">
                    <div class="ord-kpi__icon ord-kpi__icon--green"><el-icon :size="16"><Checked /></el-icon></div>
                    <div class="ord-kpi__body">
                        <strong class="ord-kpi__val ord-text-green">{{ kpis.delivered }}</strong>
                        <span class="ord-kpi__label">Delivered</span>
                    </div>
                </div>
            </div>

            <div class="ord-body">
                <div class="ord-section">
                    <div class="ord-toolbar">
                        <div class="ord-filters">
                            <button
                                v-for="f in filters"
                                :key="f.key"
                                type="button"
                                class="ord-filter"
                                :class="{ 'ord-filter--active': activeFilter === f.key }"
                                @click="activeFilter = f.key"
                            >
                                {{ f.label }}
                                <span class="ord-filter__count">{{ tabCount(f.key) }}</span>
                            </button>
                        </div>

                        <el-input
                            v-model="search"
                            class="ord-search"
                            :prefix-icon="Search"
                            placeholder="Search order #, crop, counterparty…"
                            clearable
                        />
                    </div>

                    <div class="ord-card">
                    <el-table
                        :data="filteredOrders"
                        class="ord-table"
                        @row-click="openOrder"
                    >
                        <el-table-column width="170">
                            <template #header><span class="ord-th"><el-icon><Tickets /></el-icon> Order</span></template>
                            <template #default="{ row }">
                                <div class="ord-cell-order">
                                    <span class="ord-cell-order__num">{{ row.order_number }}</span>
                                    <span class="ord-row__perspective">{{ perspectiveLabel(row) }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column width="100">
                            <template #header><span class="ord-th">Type</span></template>
                            <template #default="{ row }">
                                <span class="ord-badge" :class="typeTone(row.type)">{{ typeLabel(row.type) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column min-width="190">
                            <template #header><span class="ord-th"><el-icon><User /></el-icon> Party</span></template>
                            <template #default="{ row }">
                                <div class="ord-cell-party">
                                    <span
                                        v-if="counterpartName(row) || (!isParty(row) && (row.buyer_name || row.seller_name))"
                                        class="ord-avatar"
                                        :style="avatarStyle(counterpartName(row) || row.seller_name || row.buyer_name)"
                                    >{{ initials(counterpartName(row) || row.seller_name || row.buyer_name) }}</span>
                                    <span :class="row.status === 'open' && isCreator(row) ? 'ord-muted-cell' : ''">{{ partyLabel(row) }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column min-width="160">
                            <template #header><span class="ord-th"><el-icon><Coffee /></el-icon> Coffee</span></template>
                            <template #default="{ row }">
                                {{ row.crop_type }}<template v-if="row.variety"> — {{ row.variety }}</template>
                            </template>
                        </el-table-column>
                        <el-table-column width="110" align="right">
                            <template #header><span class="ord-th ord-th--right"><el-icon><Box /></el-icon> Quantity</span></template>
                            <template #default="{ row }"><span class="ord-num">{{ row.quantity.toLocaleString() }} kg</span></template>
                        </el-table-column>
                        <el-table-column width="150" align="right">
                            <template #header><span class="ord-th ord-th--right"><el-icon><Coin /></el-icon> Amount</span></template>
                            <template #default="{ row }"><span class="ord-num ord-amount">{{ formatMoney(row.total_amount, row.currency) }}</span></template>
                        </el-table-column>
                        <el-table-column width="130" align="right">
                            <template #header><span class="ord-th ord-th--right"><el-icon><Checked /></el-icon> Status</span></template>
                            <template #default="{ row }">
                                <div class="ord-cell-status">
                                    <span class="ord-badge" :class="statusTone(row.status)">{{ statusLabel(row.status) }}</span>
                                    <span class="ord-status-date">{{ formatDate(row.updated_at) }}</span>
                                </div>
                            </template>
                        </el-table-column>

                        <template #empty>
                            <div class="ord-empty">
                                <div class="ord-empty__icon"><el-icon :size="22"><FolderOpened /></el-icon></div>
                                <template v-if="isSearching || activeFilter !== 'all'">
                                    <div class="ord-empty__title">No orders match this view</div>
                                    <p class="ord-empty__text">Try a different tab or search term.</p>
                                    <button type="button" class="ord-btn-outline" @click="resetFilters">Reset Filters</button>
                                </template>
                                <template v-else>
                                    <div class="ord-empty__title">No orders yet</div>
                                    <p class="ord-empty__text">Post a request or an offer to start trading on Bean Origin.</p>
                                </template>
                            </div>
                        </template>
                    </el-table>
                    </div>
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
    </DesignPreviewLayout>
</template>

<style scoped>
/* Orders — app-wide theme. Tokens come from the shared DesignPreviewLayout
   --dp-* palette (defined on .dp-shell); literal hex fallbacks are the same
   values so the page reads correctly on its own. Uses the same icon-tile
   KPI + section-card language as Calendar, Contacts, and Notifications. */
.ord-page {
    --card-border: var(--dp-outline-variant, #E5E7EB);
    --surface: var(--dp-surface-container-lowest, #ffffff);
    --surface-muted: var(--dp-surface-container-low, #F5F6F7);
    --surface-elevated: var(--dp-surface-container, #F1F2F3);
    --border: var(--dp-outline-variant, #E5E7EB);
    --primary: var(--dp-primary, #000000);
    --on-primary: var(--dp-on-primary, #ffffff);
    --text: var(--dp-on-surface, #121516);
    --text-2: var(--dp-on-surface-variant, #4B5457);
    --text-muted: var(--dp-outline, #6F7677);
    --success: #15803D;
    --error: var(--dp-error, #F85149);
    font-family: var(--dp-font-sans, 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
    color: var(--text);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.ord-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.ord-page-header__left { max-width: 640px; }
.ord-page-header__actions { display: flex; gap: 8px; flex-wrap: wrap; }

.ord-title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    margin: 0 0 6px;
}
.ord-subtitle {
    font-size: 0.9375rem;
    line-height: 1.5rem;
    color: var(--text-muted);
    margin: 0;
    max-width: 64ch;
    text-wrap: pretty;
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.ord-btn {
    height: 36px;
    padding: 0 16px;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 120ms ease, background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ord-btn--primary {
    background: var(--primary);
    border: 1px solid transparent;
    color: var(--on-primary);
}
.ord-btn--primary:hover:not(:disabled) { opacity: 0.88; }
.ord-btn--primary:disabled { opacity: 0.5; cursor: default; }
.ord-btn--outline {
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--text);
}
.ord-btn--outline:hover { background: var(--surface-muted); }

/* ── Overview / KPI strip ────────────────────────────────────────────── */
.ord-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
}
.ord-kpi {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow, none);
    padding: 16px 18px;
    transition: box-shadow 0.15s ease, transform 0.15s ease, border-color 0.15s ease;
}
.ord-kpi:hover {
    box-shadow: 0 12px 28px -18px rgba(15, 23, 42, 0.18);
    transform: translateY(-1px);
    border-color: var(--primary);
}
.ord-kpi__icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--surface-muted);
    color: var(--text-2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ord-kpi__icon--green { background: var(--dp-secondary-container, #E5FAE7); color: var(--dp-on-secondary-container, #2F6B35); }
.ord-kpi__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.ord-kpi__label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted);
    white-space: nowrap;
}
.ord-kpi__val {
    font-size: 1.5rem;
    line-height: 2rem;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.01em;
    font-variant-numeric: tabular-nums;
}
.ord-text-green { color: var(--success); }

/* ── Body / section card ─────────────────────────────────────────────── */
.ord-body { display: flex; flex-direction: column; }
.ord-section {
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow, none);
    overflow: hidden;
}

/* ── Toolbar: filters + search ───────────────────────────────────────── */
.ord-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}
.ord-filters { display: flex; flex-wrap: wrap; gap: 6px; }
.ord-filter {
    height: 32px;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 999px;
    color: var(--text-2);
    font-family: inherit;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ord-filter:hover { background: var(--surface-muted); color: var(--text); }
.ord-filter--active { background: var(--primary); border-color: var(--primary); color: var(--on-primary); }
.ord-filter__count {
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-size: 11px;
    line-height: 16px;
    color: var(--text-muted);
}
.ord-filter--active .ord-filter__count { color: var(--on-primary); opacity: 0.78; }

/* ── Toolbar search — compact on-theme input. The app's global 48px input
      height is deliberately overridden so the toolbar stays tight while
      the input otherwise inherits the standard on-theme look. */
.ord-search { width: 260px; max-width: 100%; }
.ord-search :deep(.el-input__wrapper) {
    height: 36px;
    min-height: 36px !important;
    background: var(--surface);
    border-radius: 6px;
    box-shadow: 0 0 0 1px var(--border) inset !important;
    transition: box-shadow 120ms ease;
}
.ord-search :deep(.el-input__inner) { font-size: 13px; color: var(--text); }
.ord-search :deep(.el-input__inner::placeholder) { color: var(--text-muted); }
.ord-search :deep(.el-input__prefix .el-icon) { color: var(--text-muted); }
.ord-search :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1px var(--primary) inset !important; }

/* ── Card — holds the table; corners clip through .ord-section. ───────── */
.ord-card { overflow: hidden; }

/* ── Order table ─────────────────────────────────────────────────────── */
.ord-table {
    --el-table-border-color: var(--border);
    --el-table-bg-color: transparent;
    --el-table-tr-bg-color: transparent;
    --el-table-header-bg-color: var(--surface-muted);
    --el-table-header-text-color: var(--text-muted);
    --el-table-text-color: var(--text-2);
    --el-table-row-hover-bg-color: var(--surface-muted);
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
}
.ord-table :deep(.el-table__header) th {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.ord-table :deep(.el-table__row) { cursor: pointer; }
.ord-table :deep(.el-table__inner-wrapper::before) { display: none; }
.ord-table :deep(.el-table__header-wrapper th:first-child .cell),
.ord-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
.ord-table :deep(.el-table__header-wrapper th:last-child .cell),
.ord-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }

/* ── Table header icons ───────────────────────────────────────────────── */
.ord-th { display: inline-flex; align-items: center; gap: 6px; }
.ord-th :deep(.el-icon) { font-size: 13px; color: var(--text-muted); }
.ord-th--right { justify-content: flex-end; }

.ord-cell-order {
    display: flex;
    flex-direction: column;
    gap: 3px;
    align-items: flex-start;
}
.ord-cell-order__num {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
}
.ord-muted-cell { color: var(--text-muted); font-style: italic; }

/* ── Party identity ────────────────────────────────────────────────────── */
.ord-cell-party { display: flex; align-items: center; gap: 8px; min-width: 0; }
.ord-avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.625rem;
    font-weight: 800;
}

/* ── Numeric columns — right-aligned, tabular figures ─────────────────── */
.ord-num { font-variant-numeric: tabular-nums; }
.ord-amount { font-weight: 600; color: var(--text); }

/* ── Status cell ───────────────────────────────────────────────────────── */
.ord-cell-status { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; }
.ord-status-date { font-size: 11px; color: var(--text-muted); }

/* ── Empty state ───────────────────────────────────────────────────────── */
.ord-empty { text-align: center; padding: 3rem 1rem; }
.ord-empty__icon { width: 52px; height: 52px; border-radius: 12px; background: var(--surface-muted); color: var(--text-muted); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.ord-empty__title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.ord-empty__text { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; max-width: 340px; margin-left: auto; margin-right: auto; line-height: 1.5; }

.ord-row__perspective {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--dp-on-secondary-container, #2F6B35);
    background: var(--dp-secondary-container, #E5FAE7);
    border-radius: 999px;
    padding: 2px 8px;
    align-self: flex-start;
}

.ord-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    flex-shrink: 0;
    white-space: nowrap;
}
.ord-badge--green { background: var(--dp-secondary-container, #E5FAE7); color: var(--dp-on-secondary-container, #2F6B35); }
.ord-badge--amber { background: #fef3c7; color: #92400e; }
.ord-badge--red { background: var(--dp-error-container, #FEEDED); color: #991b1b; }
.ord-badge--blue { background: #dbeafe; color: #1e40af; }
.ord-badge--muted { background: var(--dp-surface-container-high, #E5E7EB); color: var(--dp-on-surface-variant, #4B5457); }

.ord-muted { color: var(--text-muted); font-size: 13px; }

/* ── Buttons (modal footer + empty-state reset) ──────────────────────── */

.ord-btn-primary {
    height: 36px;
    padding: 0 16px;
    background: var(--primary);
    border: 1px solid transparent;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    color: var(--on-primary);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 120ms ease;
}
.ord-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.ord-btn-primary:disabled { opacity: 0.5; cursor: default; }

.ord-btn-outline {
    height: 36px;
    padding: 0 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ord-btn-outline:hover { background: var(--surface-muted); }

/* ── New/Edit modal ────────────────────────────────────────────────────
   <el-dialog> teleports to <body>, outside .ord-page's DOM subtree, so
   the page's --dp-* tokens don't cascade in — app-standard literal hex
   from the same token set is used instead (same approach as every other
   app modal). */
:deep(.el-dialog.ord-modal) {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    border-radius: var(--el-border-radius-base, 6px);
    padding: 0;
    overflow: hidden;
    box-shadow: var(--el-box-shadow-dark, 0 8px 28px rgba(0, 0, 0, 0.08));
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
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
    border-bottom: 1px solid #E5E7EB;
}

.ord-modal__head-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    background: #F1F2F3;
    border: 1px solid #E5E7EB;
    color: #121516;
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
    font-size: 11px;
    line-height: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6F7677;
    margin-bottom: 2px;
}

.ord-modal__title {
    font-size: 15px;
    line-height: 20px;
    font-weight: 700;
    color: #121516;
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
}

.ord-modal__close {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: #6F7677;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 120ms, color 120ms;
}

.ord-modal__close:hover { background: #F1F2F3; color: #121516; }

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
    font-size: 12px;
    font-weight: 600;
    color: #121516;
}

.ord-field__error {
    font-size: 12px;
    font-weight: 500;
    color: #F85149;
    line-height: 1.4;
}

.ord-input--error :deep(.el-input__wrapper),
.ord-input--error :deep(.el-textarea__inner),
.ord-input--error :deep(.el-select__wrapper) {
    box-shadow: 0 0 0 1.5px #F85149 inset !important;
}

.ord-desc {
    font-size: 12px;
    color: #4B5457;
    line-height: 1.5;
    margin: 0;
}

.ord-input :deep(.el-input__wrapper),
.ord-input :deep(.el-textarea__inner) {
    border-radius: 6px;
    box-shadow: 0 0 0 1px #E5E7EB inset;
    background: #F5F6F7;
    transition: box-shadow 120ms, background 120ms;
}

.ord-input :deep(.el-input__wrapper:hover),
.ord-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #E5E7EB inset;
}

.ord-input :deep(.el-input__wrapper.is-focus),
.ord-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #000000 inset;
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
    background: #F5F6F7;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    padding: 10px 12px;
    font-size: 12px;
    color: #6F7677;
}

.ord-estimate strong {
    font-size: 14px;
    color: #121516;
    font-weight: 800;
}

.ord-modal__footer {
    /* Button tokens are defined here (not on .ord-page) because the
       <el-dialog> teleports to <body> — the page's custom properties
       don't cascade into it, so the primary save button would otherwise
       render with no background. */
    --primary: #000000;
    --on-primary: #ffffff;
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --border: #E5E7EB;
    --text: #121516;
    --text-muted: #6F7677;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ord-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 767.98px) {
    .ord-page-header { flex-direction: column; align-items: stretch; }
    .ord-kpi-grid { grid-template-columns: 1fr; }
    .ord-toolbar { flex-direction: column; align-items: stretch; }
    .ord-search { width: 100%; }
    .ord-field-row { grid-template-columns: 1fr; }
}

/* ── Reduced motion ───────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .ord-kpi,
    .ord-btn,
    .ord-filter,
    .ord-modal__close {
        transition: none;
    }
}
</style>
