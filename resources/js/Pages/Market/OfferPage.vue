<script setup>
import { computed, ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    Plus, View, CollectionTag, Box, Coin, Money, Flag, User, ShoppingCart, Close, Files, Tickets,
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

watch(filteredOrders, () => { currentPage.value = 1; });
</script>

<template>
    <MarketPage v-model:search-query="searchQuery">
        <div class="mkt-body">


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
.mkt-muted { color: var(--on-surface-var); }
.mkt-item-name { font-size: .8125rem; font-weight: 600; color: var(--on-surface); }

.mkt-body { padding: 1.25rem 0 3rem; }
.mkt-section__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: .875rem; padding: 0 1.5rem; }
.mkt-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.mkt-title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
.mkt-count { font-size: .75rem; color: var(--on-surface-var); }
.mkt-section__actions { display: flex; align-items: center; gap: 14px; }

.mkt-btn-group__item { display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; font-size: .75rem; font-weight: 700; letter-spacing: .01em; text-decoration: none; color: var(--green); background: #fff; border: 1px solid var(--green); border-radius: 8px; cursor: pointer; white-space: nowrap; transition: background .15s ease, color .15s ease; }
.mkt-btn-group__item:hover { background: #f0f5f3; }
.mkt-btn-group__item--solid { background: var(--green); color: #fff; }
.mkt-btn-group__item--solid:hover { background: var(--green-dark); }
.mkt-btn-group__item:disabled { opacity: .6; cursor: not-allowed; }
.mkt-new-btn :deep(svg) { width: 14px; height: 14px; }

/* ── Table card — boxed, elevated container ─────────────────────────────── */
.mkt-card {
    margin: 0 1.5rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

/* ── Element Plus table, reskinned to match the market design system ──── */
.mkt-el-table { --el-table-border-color: var(--border); --el-table-header-bg-color: var(--surface-low); --el-table-header-text-color: var(--on-surface-var); --el-table-row-hover-bg-color: #f3f6f5; --el-table-text-color: var(--on-surface); font-family: inherit; }
.mkt-el-table :deep(.el-table__cell) { padding: 11px 0; }
.mkt-el-table :deep(.cell) { padding: 0 12px; font-size: .8125rem; line-height: 1.45; }
.mkt-el-table :deep(th.el-table__cell) { font-size: .6875rem; font-weight: 600; letter-spacing: .04em; }
.mkt-el-table :deep(th.el-table__cell .cell) { display: flex; align-items: center; white-space: nowrap; }
.mkt-el-table :deep(.el-table__cell:first-child .cell) { padding-left: 1.25rem; }
.mkt-el-table :deep(.el-table__cell:last-child .cell) { padding-right: 1.25rem; }
.mkt-el-table :deep(.el-table__inner-wrapper::before) { display: none; }
.mkt-el-table :deep(.el-table__body td.el-table__cell) { transition: background-color .12s ease; }
.mkt-el-table :deep(.el-table__body tr.el-table__row--striped td.el-table__cell) { background: #fafaf9; }
.mkt-el-table :deep(.el-table__body tr:hover > td.el-table__cell) { background: var(--el-table-row-hover-bg-color); }
.mkt-el-table :deep(.el-table__empty-block) { min-height: 160px; }
.mkt-el-table :deep(.el-table__empty-text) { color: var(--on-surface-var); font-size: .8125rem; }
.mkt-th-icon { width: 14px; height: 14px; margin-right: 5px; color: var(--green); opacity: .8; }
.mkt-num { font-variant-numeric: tabular-nums; }

.mkt-thumb { width: 32px; height: 32px; border-radius: 9px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f1e6d8; border: 1px solid #e6d5bf; }
.mkt-thumb-icon { width: 22px; height: 22px; }

.mkt-icon-link { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 7px; border: 1px solid var(--border); background: #fff; color: var(--on-surface-var); cursor: pointer; text-decoration: none; transition: all .15s ease; }
.mkt-icon-link:hover { background: var(--surface-low); color: var(--green); border-color: var(--green); box-shadow: 0 1px 3px rgba(0, 69, 50, .12); }

.mkt-pagination { padding: 1rem 1.25rem 1.25rem; border-top: 1px solid var(--border); }
.mkt-pagination :deep(.el-pagination) { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; width: 100%; font-family: inherit; }
.mkt-pagination :deep(.el-pagination__total) { margin-right: auto; font-size: .8125rem; font-weight: 600; color: var(--on-surface-var); }
.mkt-pagination :deep(.el-pagination__sizes) { margin-right: 4px; }
.mkt-pagination :deep(.el-select__wrapper) { border-radius: 8px; box-shadow: 0 0 0 1px var(--border) inset; min-height: 32px; font-size: .75rem; }
.mkt-pagination :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1px var(--green) inset; }
.mkt-pagination :deep(.btn-prev),
.mkt-pagination :deep(.btn-next) { width: 32px; height: 32px; border-radius: 9px; background: #fff; border: 1px solid var(--border); color: var(--on-surface-var); transition: all .15s ease; }
.mkt-pagination :deep(.btn-prev:hover:not(:disabled)),
.mkt-pagination :deep(.btn-next:hover:not(:disabled)) { border-color: var(--green); color: var(--green); background: #f0f5f3; }
.mkt-pagination :deep(.btn-prev:disabled),
.mkt-pagination :deep(.btn-next:disabled) { opacity: .4; background: #fff; border-color: var(--border); color: var(--on-surface-var); }
.mkt-pagination :deep(.el-pager) { display: flex; align-items: center; gap: 4px; }
.mkt-pagination :deep(.el-pager li) { min-width: 32px; height: 32px; border-radius: 9px; background: #fff; border: 1px solid var(--border); color: var(--on-surface); font-size: .8125rem; font-weight: 600; transition: all .15s ease; }
.mkt-pagination :deep(.el-pager li:hover) { border-color: var(--green); color: var(--green); background: #f0f5f3; }
.mkt-pagination :deep(.el-pager li.is-active) { background: var(--green); border-color: var(--green); color: #fff; box-shadow: 0 2px 6px rgba(0, 69, 50, .25); }

.mkt-badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; font-size: .6875rem; font-weight: 600; padding: 3px 10px 3px 8px; line-height: 1.5; white-space: nowrap; }
.mkt-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.mkt-badge--green { background: #ecfdf5; color: #059669; }
.mkt-badge--amber { background: #fffbeb; color: #d97706; }
.mkt-badge--red { background: #fef2f2; color: #dc2626; }
.mkt-badge--muted { background: #f5f5f4; color: #78716c; }

/* ── New Offer modal ─────────────────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .mkt-page's
   DOM subtree, so CSS custom properties defined on .mkt-page do NOT
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
