<script setup>
import { computed, ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    Plus, View, CollectionTag, Box, Coin, Money, Flag, User, ShoppingCart, Close, Files, Tickets,
    ShoppingBag, CircleCheck, Clock,
} from '@element-plus/icons-vue';
import MarketPage from './MarketPage.vue';

const props = defineProps({
    orders: { type: Array, default: () => [] },
});

const searchQuery = ref('');
const showForm = ref(false);

/* ── New offer dialog ────────────────────────────────────────────────── */
const form = useForm({
    type: 'offer',
    crop_type: '',
    variety: '',
    grade: '',
    quantity: null,
    unit_price: null,
    notes: '',
});

const cropSelectOptions = ['Arabica', 'Robusta'];
const otherCropMode = ref(false);
const cropSelectValue = ref('');

const handleCropSelect = (value) => {
    if (value === '__other__') {
        otherCropMode.value = true;
        form.crop_type = '';
    } else {
        otherCropMode.value = false;
        form.crop_type = value;
    }
};

const estimatedTotal = computed(() => {
    const qty = Number(form.quantity) || 0;
    const price = Number(form.unit_price) || 0;
    return qty * price;
});

const openCreateDialog = () => {
    form.reset();
    form.clearErrors();
    otherCropMode.value = false;
    cropSelectValue.value = '';
    showForm.value = true;
};

const saveOrder = () => {
    form.clearErrors();

    if (!form.crop_type.trim()) form.setError('crop_type', 'Crop type is required.');
    if (!form.quantity) form.setError('quantity', 'Quantity is required.');
    if (!form.unit_price) form.setError('unit_price', 'Unit price is required.');
    if (Object.keys(form.errors).length) return;

    form.post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: () => { showForm.value = false; },
    });
};

const orderStatusLabel = (s) => ({
    open: 'Open', pending: 'Pending', confirmed: 'Confirmed', inspection: 'Inspection',
    processing: 'Processing', shipped: 'Shipped', delivered: 'Delivered', cancelled: 'Cancelled', withdrawn: 'Withdrawn',
}[s] ?? s ?? '—');

const orderStatusTone = (s) => ({
    open: 'green', pending: 'amber', confirmed: 'green', inspection: 'amber',
    processing: 'amber', shipped: 'green', delivered: 'green', cancelled: 'red', withdrawn: 'muted',
}[s] ?? 'muted');

const fmtQty = (q) => (q != null ? `${Number(q).toLocaleString()} kg` : '—');
const fmtAmt = (a) => (a != null ? `$${Number(a).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '—');
const varietyGrade = (r) => [r.variety, r.grade].filter(Boolean).join(' · ') || '—';

const filteredOrders = computed(() => {
    if (!searchQuery.value) return props.orders;
    const q = searchQuery.value.toLowerCase();
    return props.orders.filter((o) => `${o.order_number ?? ''} ${o.crop_type ?? ''} ${o.variety ?? ''} ${o.grade ?? ''} ${o.seller_name ?? ''}`.toLowerCase().includes(q));
});

/* ── Pagination ───────────────────────────────────────────────────────── */
const currentPage = ref(1);
const pageSize = ref(25);

const pagedOrders = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return filteredOrders.value.slice(start, start + pageSize.value);
});

/* ── KPI summary (open / pending / total / shipped) ─────────────────────── */
const kpis = computed(() => {
    const orders = props.orders;
    const byStatus = (s) => orders.filter((o) => (o.status || '').toLowerCase() === s).length;
    return {
        total: orders.length || 0,
        open: byStatus('open'),
        pending: byStatus('pending'),
        shipped: byStatus('shipped'),
    };
});

watch(filteredOrders, () => { currentPage.value = 1; });
</script>

<template>
    <MarketPage v-model:search-query="searchQuery">
        <div class="mkt-body">


            <!-- KPIs -->
            <div class="mkt-kpis">
                <div class="mkt-kpi">
                    <div class="mkt-kpi__icon"><el-icon><ShoppingBag /></el-icon></div>
                    <div class="mkt-kpi__value">{{ kpis.total }}</div>
                    <div class="mkt-kpi__label">Total Offers</div>
                </div>
                <div class="mkt-kpi">
                    <div class="mkt-kpi__icon mkt-kpi__icon--green"><el-icon><CircleCheck /></el-icon></div>
                    <div class="mkt-kpi__value">{{ kpis.open }}</div>
                    <div class="mkt-kpi__label">Open</div>
                </div>
                <div class="mkt-kpi">
                    <div class="mkt-kpi__icon mkt-kpi__icon--amber"><el-icon><Clock /></el-icon></div>
                    <div class="mkt-kpi__value">{{ kpis.pending }}</div>
                    <div class="mkt-kpi__label">Pending</div>
                </div>
                <div class="mkt-kpi">
                    <div class="mkt-kpi__icon mkt-kpi__icon--blue"><el-icon><Box /></el-icon></div>
                    <div class="mkt-kpi__value">{{ kpis.shipped }}</div>
                    <div class="mkt-kpi__label">Shipped</div>
                </div>
            </div>

            <div class="mkt-card">
                <el-table :data="pagedOrders" class="mkt-el-table" stripe empty-text="No offers match your search.">
                    <el-table-column width="56">
                        <template #default>
                            <div class="mkt-thumb">
                                <svg class="mkt-thumb-icon" viewBox="0 0 24 24">
                                    <ellipse cx="9" cy="12" rx="5" ry="7" transform="rotate(-25 9 12)" fill="#4b2e1d" />
                                    <path d="M9 6 C7 9, 11 15, 9 18" stroke="#c9a27a" stroke-width="1.1" fill="none" transform="rotate(-25 9 12)" />
                                    <ellipse cx="16.5" cy="14.5" rx="4" ry="5.5" transform="rotate(20 16.5 14.5)" fill="#6b4226" />
                                    <path d="M16.5 10 C15 12.5, 18 16, 16.5 19" stroke="#d8b48f" stroke-width="1" fill="none" transform="rotate(20 16.5 14.5)" />
                                </svg>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="Order #" min-width="150">
                        <template #header><el-icon class="mkt-th-icon"><Tickets /></el-icon>Order #</template>
                        <template #default="{ row }">
                            <div class="mkt-item-name">{{ row.order_number }}</div>
                            <div class="mkt-muted" style="font-size:.7rem;">{{ row.crop_type }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="Variety / Grade" min-width="140">
                        <template #header><el-icon class="mkt-th-icon"><CollectionTag /></el-icon>Variety / Grade</template>
                        <template #default="{ row }">{{ varietyGrade(row) }}</template>
                    </el-table-column>
                    <el-table-column label="Quantity" min-width="110" align="right" header-align="left">
                        <template #header><el-icon class="mkt-th-icon"><Box /></el-icon>Quantity</template>
                        <template #default="{ row }"><span class="mkt-num">{{ fmtQty(row.quantity) }}</span></template>
                    </el-table-column>
                    <el-table-column label="Unit Price" min-width="110" align="right" header-align="left">
                        <template #header><el-icon class="mkt-th-icon"><Coin /></el-icon>Unit Price</template>
                        <template #default="{ row }"><span class="mkt-num">{{ fmtAmt(row.unit_price) }}</span></template>
                    </el-table-column>
                    <el-table-column label="Total" min-width="110" align="right" header-align="left">
                        <template #header><el-icon class="mkt-th-icon"><Money /></el-icon>Total</template>
                        <template #default="{ row }"><span class="mkt-num fw-semibold">{{ fmtAmt(row.total_amount) }}</span></template>
                    </el-table-column>
                    <el-table-column label="Seller" min-width="130">
                        <template #header><el-icon class="mkt-th-icon"><User /></el-icon>Seller</template>
                        <template #default="{ row }">{{ row.seller_name || '—' }}</template>
                    </el-table-column>
                    <el-table-column label="Status" min-width="110" align="center">
                        <template #header><el-icon class="mkt-th-icon"><Flag /></el-icon>Status</template>
                        <template #default="{ row }">
                            <span class="mkt-badge" :class="`mkt-badge--${orderStatusTone(row.status)}`">{{ orderStatusLabel(row.status) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column width="70" align="right">
                        <template #default="{ row }">
                            <Link :href="route('orders.show', row.id)" class="mkt-icon-link" title="View order"><el-icon><View /></el-icon></Link>
                        </template>
                    </el-table-column>
                </el-table>

                <div v-if="filteredOrders.length" class="mkt-pagination">
                    <el-pagination
                        v-model:current-page="currentPage"
                        v-model:page-size="pageSize"
                        :total="filteredOrders.length"
                        :page-sizes="[25, 50, 100]"
                        layout="total, sizes, prev, pager, next"
                        background
                    />
                </div>
            </div>
        </div>

        <!-- ── New Offer modal ───────────────────────────────────────────── -->
        <el-dialog
            v-model="showForm"
            width="480px"
            destroy-on-close
            align-center
            :show-close="false"
            class="ord-modal"
        >
            <template #header>
                <div class="ord-modal__head">
                    <div class="ord-modal__head-icon">
                        <el-icon :size="18"><ShoppingCart /></el-icon>
                    </div>
                    <div class="ord-modal__head-text">
                        <div class="ord-modal__eyebrow">Sell</div>
                        <div class="ord-modal__title">New Offer</div>
                    </div>
                    <button type="button" class="ord-modal__close" aria-label="Close" @click="showForm = false">
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
                    <el-input v-model="form.notes" type="textarea" :rows="2" placeholder="Optional notes for the buyer" class="ord-input" />
                </div>
            </div>

            <template #footer>
                <div class="ord-modal__footer">
                    <button type="button" class="ord-btn-outline" @click="showForm = false">Cancel</button>
                    <button type="button" class="ord-btn-primary" :disabled="form.processing" @click="saveOrder">
                        <el-icon v-if="!form.processing"><Plus /></el-icon>
                        {{ form.processing ? 'Posting…' : 'Create Offer' }}
                    </button>
                </div>
            </template>
        </el-dialog>
    </MarketPage>
</template>

<style scoped>
/* The Order page's --dp-* palette is defined on the shared layout wrapper
   (.dp-shell in DesignPreviewLayout), so it cascades here. We redeclare a
   few app-local aliases on .mkt-body for readability. */
.mkt-body {
    --card-border: var(--dp-outline-variant, #E5E7EB);
    --surface: var(--dp-surface-container-lowest, #ffffff);
    --surface-muted: var(--dp-surface-container-low, #F5F6F7);
    --primary: var(--dp-primary, #000000);
    --on-primary: var(--dp-on-primary, #ffffff);
    --text: var(--dp-on-surface, #121516);
    --text-2: var(--dp-on-surface-variant, #4B5457);
    --text-muted: var(--dp-outline, #6F7677);
    --error: var(--dp-error, #F85149);
    padding: 20px 0;
}

.mkt-muted,
.mkt-count { color: var(--text-muted); }
.mkt-item-name { font-size: .8125rem; font-weight: 600; color: var(--text); }

.mkt-section__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: .875rem; padding: 0 1.5rem; }
.mkt-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); margin-bottom: 2px; }
.mkt-title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
.mkt-section__actions { display: flex; align-items: center; gap: 14px; }

.mkt-btn-group__item { display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; font-size: .75rem; font-weight: 700; letter-spacing: .01em; text-decoration: none; color: var(--text-muted); background: var(--surface); border: 1px solid var(--card-border); border-radius: 6px; cursor: pointer; white-space: nowrap; transition: background .15s ease, color .15s ease; }
.mkt-btn-group__item:hover { background: var(--surface-muted); }
.mkt-btn-group__item--solid { background: var(--primary); color: var(--on-primary); }
.mkt-btn-group__item--solid:hover { opacity: .88; }
.mkt-btn-group__item:disabled { opacity: .6; cursor: not-allowed; }

/* KPI summary strip */
.mkt-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin: 0 1.5rem 16px; }
.mkt-kpi { background: var(--surface); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius, 6px); padding: 16px; box-shadow: var(--dp-card-shadow, none); display: flex; align-items: center; gap: 12px; }
.mkt-kpi__icon { width: 36px; height: 36px; border-radius: 8px; background: var(--surface-muted); color: var(--text-2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mkt-kpi__icon--green { background: var(--dp-secondary-container, #E5FAE7); color: var(--dp-on-secondary-container, #2F6B35); }
.mkt-kpi__icon--amber { background: var(--dp-tertiary-container, #FEF3C7); color: #92400e; }
.mkt-kpi__icon--blue { background: var(--dp-primary-container, #DBEAFE); color: var(--dp-on-primary-container, #1D4ED8); }
.mkt-kpi__value { font-size: 1.625rem; font-weight: 800; font-variant-numeric: tabular-nums; color: var(--text); }
.mkt-kpi__label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); }

/* ── Table card — boxed, elevated container ─────────────────────────────── */
.mkt-card {
    margin: 0 1.5rem;
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    overflow: hidden;
    background: var(--surface);
    box-shadow: var(--dp-card-shadow, none);
}

/* ── Element Plus table, reskinned to match the app design system ──── */
.mkt-el-table {
    --el-table-border-color: var(--card-border);
    --el-table-header-bg-color: var(--surface-muted);
    --el-table-header-text-color: var(--text-muted);
    --el-table-row-hover-bg-color: var(--surface-muted);
    --el-table-text-color: var(--text);
    font-family: inherit;
}
.mkt-el-table :deep(.el-table__cell) { padding: 11px 0; }
.mkt-el-table :deep(.cell) { padding: 0 12px; font-size: .8125rem; line-height: 1.45; }
.mkt-el-table :deep(th.el-table__cell) { font-size: .6875rem; font-weight: 600; letter-spacing: .04em; }
.mkt-el-table :deep(th.el-table__cell .cell) { display: flex; align-items: center; white-space: nowrap; }
.mkt-el-table :deep(.el-table__cell:first-child .cell) { padding-left: 1.25rem; }
.mkt-el-table :deep(.el-table__cell:last-child .cell) { padding-right: 1.25rem; }
.mkt-el-table :deep(.el-table__inner-wrapper::before) { display: none; }
.mkt-el-table :deep(.el-table__body td.el-table__cell) { transition: background-color .12s ease; }
.mkt-el-table :deep(.el-table__body tr.el-table__row--striped td.el-table__cell) { background: var(--surface-muted); }
.mkt-el-table :deep(.el-table__body tr:hover > td.el-table__cell) { background: var(--el-table-row-hover-bg-color); }
.mkt-el-table :deep(.el-table__empty-block) { min-height: 160px; }
.mkt-el-table :deep(.el-table__empty-text) { color: var(--text-muted); font-size: .8125rem; }
.mkt-th-icon { width: 14px; height: 14px; margin-right: 5px; color: var(--text-2); opacity: .8; }
.mkt-num { font-variant-numeric: tabular-nums; }

.mkt-thumb { width: 32px; height: 32px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--surface-muted); border: 1px solid var(--card-border); }
.mkt-thumb-icon { width: 22px; height: 22px; }

.mkt-icon-link { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s ease; }
.mkt-icon-link:hover { background: var(--surface-muted); color: var(--primary); border-color: var(--primary); box-shadow: 0 1px 3px rgba(0, 0, 0, .12); }

.mkt-pagination { padding: 1rem 1.25rem 1.25rem; border-top: 1px solid var(--border); }
.mkt-pagination :deep(.el-pagination) { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; width: 100%; font-family: inherit; }
.mkt-pagination :deep(.el-pagination__total) { margin-right: auto; font-size: .8125rem; font-weight: 600; color: var(--text-muted); }
.mkt-pagination :deep(.el-pagination__sizes) { margin-right: 4px; }
.mkt-pagination :deep(.el-select__wrapper) { border-radius: 6px; box-shadow: 0 0 0 1px var(--border) inset; min-height: 32px; font-size: .75rem; }
.mkt-pagination :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--primary) inset; }
.mkt-pagination :deep(.btn-prev),
.mkt-pagination :deep(.btn-next) { width: 32px; height: 32px; border-radius: 8px; background: var(--surface); border: 1px solid var(--border); color: var(--text-2); transition: all .15s ease; }
.mkt-pagination :deep(.btn-prev:hover:not(:disabled)),
.mkt-pagination :deep(.btn-next:hover:not(:disabled)) { border-color: var(--primary); color: var(--primary); background: var(--surface-muted); }
.mkt-pagination :deep(.btn-prev:disabled),
.mkt-pagination :deep(.btn-next:disabled) { opacity: .4; background: var(--surface); border-color: var(--border); color: var(--text-2); }
.mkt-pagination :deep(.el-pager) { display: flex; align-items: center; gap: 4px; }
.mkt-pagination :deep(.el-pager li) { min-width: 32px; height: 32px; border-radius: 8px; background: var(--surface); border: 1px solid var(--border); color: var(--text); font-size: .8125rem; font-weight: 600; transition: all .15s ease; }
.mkt-pagination :deep(.el-pager li:hover) { border-color: var(--primary); color: var(--primary); background: var(--surface-muted); }
.mkt-pagination :deep(.el-pager li.is-active) { background: var(--primary); border-color: var(--primary); color: var(--on-primary); }

/* ── New Offer modal ─────────────────────────────────────────────────────
   <el-dialog> teleports its content to <body>, outside the layout's DOM
   subtree, so the shared --dp-* tokens don't cascade in. Literal app-
   standard hex values are used here (same approach as every other modal
   in the app — Contacts, Order profile, etc.). */
:deep(.el-dialog.ord-modal) {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    border-radius: var(--el-border-radius-base, 6px);
    padding: 0;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
}

:deep(.el-dialog.ord-modal .el-dialog__header) { padding: 0; margin: 0; }

:deep(.el-dialog.ord-modal .el-dialog__body) { padding: 0; }

:deep(.el-dialog.ord-modal .el-dialog__footer) { padding: 0; }

.ord-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #ffffff;
    border-bottom: 1px solid #E5E7EB;
}

.ord-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.04);
    color: #000000;
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
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6F7677;
    margin-bottom: 1px;
}

.ord-modal__title {
    font-size: 15px;
    font-weight: 700;
    color: #121516;
    letter-spacing: -0.005em;
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
}

.ord-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: #F5F6F7;
    color: #6F7677;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 120ms ease, color 120ms ease;
}

.ord-modal__close:hover { background: #E5E7EB; color: #121516; }

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
    font-size: 13px;
    font-weight: 600;
    color: #6F7677;
}

.ord-field__error {
    font-size: 12px;
    font-weight: 600;
    color: #F85149;
    line-height: 1.4;
}

.ord-input--error :deep(.el-input__wrapper),
.ord-input--error :deep(.el-textarea__inner),
.ord-input--error :deep(.el-select__wrapper) {
    box-shadow: 0 0 0 1.5px #F85149 inset !important;
}

.ord-input :deep(.el-input__wrapper),
.ord-input :deep(.el-textarea__inner) {
    border-radius: 6px;
    box-shadow: 0 0 0 1px #E5E7EB inset;
    background: #F5F6F7;
    transition: box-shadow 120ms ease, background 120ms ease;
}

.ord-input :deep(.el-input__wrapper:hover),
.ord-input :deep(.el-textarea__inner:hover) {
    background: #ffffff;
    box-shadow: 0 0 0 1px #E5E7EB inset;
}

.ord-input :deep(.el-input__wrapper.is-focus),
.ord-input :deep(.el-textarea__inner:focus) {
    background: #ffffff;
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
    font-size: 13px;
    color: #6F7677;
}

.ord-estimate strong {
    font-size: 0.9375rem;
    color: #000000;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}

.ord-modal__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}

.ord-btn-primary {
    background: #000000;
    border: 1px solid transparent;
    color: #ffffff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 0 16px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    font-family: inherit;
    transition: opacity 120ms ease;
}

.ord-btn-primary:hover { opacity: 0.88; }
.ord-btn-primary:disabled { opacity: 0.6; cursor: default; }

.ord-btn-outline {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    color: #121516;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 0 16px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    font-family: inherit;
    transition: background 120ms ease, border-color 120ms ease, color 120ms ease;
}

.ord-btn-outline:hover { background: #EFF0F2; }

@media (max-width: 767.98px) {
    .mkt-section__head { padding: 0 1.25rem; }
    .mkt-card { margin: 0 1.25rem; border-radius: 6px; }
    .mkt-el-table :deep(.el-table__cell:first-child .cell) { padding-left: 1rem; }
    .mkt-el-table :deep(.el-table__cell:last-child .cell) { padding-right: 1rem; }
    .mkt-pagination { padding: 1rem 1rem 1.25rem; }
    .mkt-pagination :deep(.el-pagination) { justify-content: center; }
    .mkt-pagination :deep(.el-pagination__total) { margin-right: 0; width: 100%; text-align: center; order: -1; }
    .ord-field-row { grid-template-columns: 1fr; }
}
</style>
