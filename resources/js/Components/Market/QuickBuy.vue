<script setup>
/* Independent "Quick Buy" panel for the Coffee Market page. Owns its own
   quantity/currency/payment state; the selected lot itself is v-model'd
   so the parent's Lot grid "Select" buttons can drive it directly.
   Before any lot is chosen, shows a lot-number lookup field (searched
   against the real `markets` list) instead of a blank "nothing
   selected" state — picking a featured lot OR finding one by number
   both resolve to the same selection and hide the lookup field. */
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';

const props = defineProps({
    modelValue: { type: Object, default: null },
    markets: { type: Array, default: () => [] },
    exchangeRates: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const quantity = ref(500);
const currency = ref('USD');
const paymentMethod = ref('smart_contract');

/* Currency choices — real, pulled from the exchange_rates table (the
   only source of truth for currencies this app actually trades in). */
const currencyOptions = computed(() => {
    const set = new Set(['USD']);
    for (const r of props.exchangeRates) {
        if (r.base_currency) set.add(r.base_currency);
        if (r.quote_currency) set.add(r.quote_currency);
    }
    return [...set].sort();
});

const lotNumberQuery = ref('');
const lookupError = ref('');

function mapMarket(m) {
    const unit = m.unit || 'kg';
    return {
        id: m.id,
        name: m.name || m.lot_code,
        code: m.lot_code,
        priceValue: Number(m.price_per_kg || 0),
        availLabel: `${Number(m.available_quantity ?? m.quantity ?? 0).toLocaleString()} ${unit} avail.`,
    };
}

function lookupLot() {
    const query = lotNumberQuery.value.trim().toLowerCase();
    if (!query) return;

    const match = props.markets.find((m) => (m.lot_code || '').toLowerCase() === query);
    if (!match) {
        lookupError.value = `No lot found with number "${lotNumberQuery.value.trim()}".`;
        return;
    }

    lookupError.value = '';
    lotNumberQuery.value = '';
    emit('update:modelValue', mapMarket(match));
}

function clearSelection() {
    emit('update:modelValue', null);
}

const subtotal = computed(() => quantity.value * (props.modelValue?.priceValue ?? 0));
const serviceFee = computed(() => subtotal.value * 0.015);
const logisticsQuote = 125;
const totalEstimate = computed(() => subtotal.value + serviceFee.value + logisticsQuote);
const fmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

/* ── Buy Now → confirm dialog → MarketController::buy (POST
   market/{market}/buy) → adds the listing to the real cart. ─────────── */
const confirmDialogVisible = ref(false);
const buying = ref(false);

const paymentMethodLabel = computed(() => (paymentMethod.value === 'smart_contract' ? 'Smart Contract' : 'Bank Transfer'));
const paymentMethodIcon = computed(() => (paymentMethod.value === 'smart_contract' ? 'bolt' : 'account_balance'));

function openConfirmDialog() {
    if (!props.modelValue) return;
    confirmDialogVisible.value = true;
}

function confirmBuy() {
    if (!props.modelValue || buying.value) return;
    buying.value = true;

    router.post(route('market.buy', props.modelValue.id), {
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            ElMessage.success(`Added ${quantity.value} kg of "${props.modelValue.name}" to your cart.`);
            confirmDialogVisible.value = false;
        },
        onError: (errors) => {
            ElMessage.error(errors.quantity || 'Could not add this lot to your cart. Please try again.');
        },
        onFinish: () => {
            buying.value = false;
        },
    });
}
</script>

<template>
    <div class="cm-quickbuy">
        <h3>Quick Buy</h3>

        <div v-if="!modelValue" class="cm-quickbuy__lookup">
            <label>Lot Number</label>
            <div class="cm-quickbuy__lookup-row">
                <el-input v-model="lotNumberQuery" placeholder="e.g. MKT-2026-0002" class="cm-el-input" @keyup.enter="lookupLot" />
                <button type="button" class="cm-btn cm-btn--primary" @click="lookupLot">Find</button>
            </div>
            <p v-if="lookupError" class="cm-quickbuy__error">{{ lookupError }}</p>
            <p class="cm-quickbuy__hint">Or select a featured lot below.</p>
        </div>

        <template v-else>
            <div class="cm-quickbuy__field">
                <label>Selected Lot</label>
                <div class="cm-quickbuy__lot">
                    <div>
                        <p class="cm-quickbuy__lot-name">{{ modelValue.name }} ({{ modelValue.code }})</p>
                        <p class="cm-quickbuy__lot-meta">{{ modelValue.priceValue.toFixed(2) }} {{ currency }}/kg · {{ modelValue.availLabel }}</p>
                    </div>
                    <span class="material-symbols-outlined" title="Change lot" @click="clearSelection">change_circle</span>
                </div>
            </div>

            <div class="cm-quickbuy__grid">
                <div class="cm-quickbuy__field">
                    <label>Quantity (kg)</label>
                    <el-input-number v-model="quantity" :min="1" class="cm-el-input-number" controls-position="right" />
                </div>
                <div class="cm-quickbuy__field">
                    <label>Currency</label>
                    <el-select v-model="currency" class="cm-el-select">
                        <el-option v-for="c in currencyOptions" :key="c" :label="c" :value="c" />
                    </el-select>
                </div>
            </div>

            <div class="cm-quickbuy__field">
                <label>Payment Method</label>
                <el-radio-group v-model="paymentMethod" class="cm-payment">
                    <el-radio-button value="smart_contract">Smart Contract</el-radio-button>
                    <el-radio-button value="bank_transfer">Bank Transfer</el-radio-button>
                </el-radio-group>
            </div>

            <div class="cm-costs">
                <div><span>Subtotal</span><strong>${{ fmt(subtotal) }}</strong></div>
                <div><span>Service Fee (1.5%)</span><strong>${{ fmt(serviceFee) }}</strong></div>
                <div><span>Logistics Quote</span><strong>${{ fmt(logisticsQuote) }}</strong></div>
                <div class="cm-costs__total"><span>Total Est.</span><strong>${{ fmt(totalEstimate) }}</strong></div>
            </div>

            <div class="cm-quickbuy__actions">
                <button type="button" class="cm-btn cm-btn--primary cm-btn--full" @click="openConfirmDialog">Buy Now</button>
                <button type="button" class="cm-btn cm-btn--outline cm-btn--full">Place Limit Order</button>
                <button type="button" class="cm-btn cm-btn--secondary cm-btn--full">Request Sample</button>
            </div>

            <div class="cm-quickbuy__trust">
                <span class="material-symbols-outlined">verified_user</span>
                <span class="material-symbols-outlined">security</span>
                <span class="material-symbols-outlined">account_balance_wallet</span>
            </div>

            <el-dialog
                v-model="confirmDialogVisible"
                width="min(440px, calc(100vw - 2rem))"
                align-center
                :close-on-click-modal="!buying"
                :show-close="!buying"
                class="cm-confirm-dialog"
            >
                <template #header>
                    <div class="cm-confirm__head">
                        <div class="cm-confirm__head-icon">
                            <span class="material-symbols-outlined">shopping_bag</span>
                        </div>
                        <div class="cm-confirm__head-text">
                            <p class="cm-confirm__eyebrow">Quick Buy</p>
                            <h3 class="cm-confirm__title">Confirm Purchase</h3>
                        </div>
                    </div>
                </template>

                <div v-if="modelValue" class="cm-confirm">
                    <div class="cm-confirm__item">
                        <div class="cm-confirm__item-icon">
                            <span class="material-symbols-outlined">coffee</span>
                        </div>
                        <div class="cm-confirm__item-text">
                            <p class="cm-confirm__item-name">{{ modelValue.name }}</p>
                            <p class="cm-confirm__item-meta">{{ modelValue.code }} · {{ modelValue.priceValue.toFixed(2) }} {{ currency }}/kg</p>
                        </div>
                        <div class="cm-confirm__item-qty">{{ quantity.toLocaleString() }} kg</div>
                    </div>

                    <div class="cm-confirm__row">
                        <span class="cm-confirm__row-label"><span class="material-symbols-outlined">{{ paymentMethodIcon }}</span>Payment Method</span>
                        <strong>{{ paymentMethodLabel }}</strong>
                    </div>

                    <div class="cm-confirm__breakdown">
                        <div class="cm-confirm__row"><span>Subtotal</span><strong>${{ fmt(subtotal) }}</strong></div>
                        <div class="cm-confirm__row"><span>Service Fee (1.5%)</span><strong>${{ fmt(serviceFee) }}</strong></div>
                        <div class="cm-confirm__row"><span>Logistics Quote</span><strong>${{ fmt(logisticsQuote) }}</strong></div>
                        <div class="cm-confirm__divider" />
                        <div class="cm-confirm__row cm-confirm__row--total"><span>Total Est.</span><strong>${{ fmt(totalEstimate) }}</strong></div>
                    </div>
                </div>

                <template #footer>
                    <button type="button" class="cm-btn cm-btn--primary cm-btn--full" :disabled="buying" @click="confirmBuy">
                        <span v-if="!buying" class="material-symbols-outlined">lock</span>
                        {{ buying ? 'Adding to Cart…' : 'Confirm & Add to Cart' }}
                    </button>
                </template>
            </el-dialog>
        </template>
    </div>
</template>

<style scoped>
/* Duplicated (not inherited) from Market/MarketListings.vue's `.cm-page`
   token block + `.cm-btn`/`.cm-el-*` rules — Vue's scoped CSS doesn't
   cross component boundaries, but the CSS custom properties themselves
   (--green, --card-border, etc.) still cascade in from the parent's
   `.cm-page` root at runtime since this component renders as its DOM
   descendant. */
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 18px; line-height: 1; }

.cm-btn { display: inline-flex; align-items: center; gap: 6px; border: none; border-radius: var(--card-radius); font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; padding: 9px 16px; white-space: nowrap; text-decoration: none; transition: background .15s ease, transform .15s ease; }
.cm-btn--tonal { background: var(--surface-low); color: var(--on-surface); }
.cm-btn--tonal:hover { background: #ece4e2; }
.cm-btn--secondary { background: var(--green-dark); color: #fff; }
.cm-btn--secondary:hover { background: var(--green); }
.cm-btn--primary { background: var(--green); color: #fff; }
.cm-btn--primary:hover { background: var(--green-dark); }
.cm-btn--outline { background: #fff; color: var(--on-surface); border: 1px solid var(--card-border); }
.cm-btn--outline:hover { background: var(--surface-low); }
.cm-btn--full { width: 100%; justify-content: center; }

.cm-quickbuy { background: #fff; border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 20px; display: flex; flex-direction: column; gap: 18px; }
.cm-quickbuy h3 { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); margin: 0; }
.cm-quickbuy__field label,
.cm-quickbuy__lookup label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); margin-bottom: 8px; }
.cm-quickbuy__lookup-row { display: flex; gap: 8px; }
.cm-el-input { flex: 1; }
.cm-el-input :deep(.el-input__wrapper) { box-shadow: 0 0 0 1px var(--card-border) inset; border-radius: var(--card-radius); }
.cm-el-input :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 2px var(--green-dark) inset; }
.cm-quickbuy__error { font-size: 12px; color: #DC2626; margin: 8px 0 0; }
.cm-quickbuy__hint { font-size: 12px; color: var(--on-surface-var); margin: 10px 0 0; }

.cm-quickbuy__lot { padding: 12px; background: var(--surface-low); border-radius: var(--card-radius); display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.cm-quickbuy__lot-name { font-size: 13px; font-weight: 700; color: var(--on-surface); margin: 0; }
.cm-quickbuy__lot-meta { font-size: 11.5px; color: var(--on-surface-var); margin: 2px 0 0; }
.cm-quickbuy__lot .material-symbols-outlined { color: var(--green); cursor: pointer; flex-shrink: 0; }
.cm-quickbuy__grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.cm-quickbuy__grid .cm-el-select { width: 100%; }

.cm-el-select :deep(.el-select__wrapper) { border-radius: var(--card-radius); box-shadow: 0 0 0 1px var(--card-border) inset; background: #fff; min-height: 34px; font-size: 13px; }
.cm-el-select :deep(.el-select__wrapper.is-focused),
.cm-el-select :deep(.el-select__wrapper.is-hovering) { box-shadow: 0 0 0 1px var(--card-border) inset; }
.cm-el-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 2px var(--green-dark) inset; }
.cm-el-select :deep(.el-select__placeholder) { font-weight: 600; color: var(--on-surface); }

.cm-el-input-number { width: 100%; }
.cm-el-input-number :deep(.el-input__wrapper) { box-shadow: 0 0 0 1px var(--card-border) inset; border-radius: var(--card-radius); }
.cm-el-input-number :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 2px var(--green-dark) inset; }
.cm-el-input-number :deep(.el-input-number__decrease),
.cm-el-input-number :deep(.el-input-number__increase) { background: var(--surface-low); border-color: var(--card-border); color: var(--on-surface-var); }

.cm-payment { display: flex; width: 100%; }
.cm-payment :deep(.el-radio-button) { flex: 1; }
.cm-payment :deep(.el-radio-button__inner) { width: 100%; border: none; box-shadow: none !important; background: var(--surface-low); color: var(--on-surface); font-family: inherit; font-size: 12px; font-weight: 700; padding: 11px; }
.cm-payment :deep(.el-radio-button:first-child .el-radio-button__inner) { border-radius: var(--card-radius) 0 0 var(--card-radius); }
.cm-payment :deep(.el-radio-button:last-child .el-radio-button__inner) { border-radius: 0 var(--card-radius) var(--card-radius) 0; }
.cm-payment :deep(.el-radio-button__original-radio:checked + .el-radio-button__inner) { background: var(--green); color: #fff; }

.cm-costs { display: flex; flex-direction: column; gap: 8px; padding-top: 14px; border-top: 1px solid var(--card-border); }
.cm-costs > div { display: flex; align-items: center; justify-content: space-between; font-size: 13px; }
.cm-costs > div span { color: var(--on-surface-var); }
.cm-costs__total { padding-top: 8px; font-size: 15px; }
.cm-costs__total span, .cm-costs__total strong { font-weight: 800; color: var(--on-surface); }

.cm-quickbuy__actions { display: flex; flex-direction: column; gap: 10px; }
.cm-quickbuy__trust { display: flex; justify-content: center; align-items: center; gap: 16px; padding-top: 12px; opacity: .4; }

/* ── Buy Now confirmation dialog ──────────────────────────────────────── */
/* el-dialog teleports its content to <body>, outside `.cm-page` — so the
   `--card-border`/`--on-surface`/etc. custom properties defined there
   don't reach it via normal DOM inheritance. Redefine the subset this
   dialog needs directly on its own (global, unscoped) root class. Header/
   footer chrome mirrors the app-wide ConfirmDialog.vue modal pattern
   (icon-box + eyebrow/title header, surface-low footer). */
:global(.cm-confirm-dialog) {
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --on-surface: #121516;
    --on-surface-var: #4B5457;
    --surface-low: #F5F6F7;
    --green: #000000;
    --green-dark: #262626;
}
:global(.cm-confirm-dialog .el-dialog__header) { margin: 0; padding: 20px 24px; border-bottom: 1px solid var(--card-border); }
:global(.cm-confirm-dialog .el-dialog__headerbtn) { top: 18px; right: 18px; width: 32px; height: 32px; border-radius: 6px; background: var(--surface-low); transition: background .12s ease; }
:global(.cm-confirm-dialog .el-dialog__headerbtn:hover) { background: var(--card-border); }
:global(.cm-confirm-dialog .el-dialog__headerbtn .el-dialog__close) { color: var(--on-surface-var); font-size: 16px; }
:global(.cm-confirm-dialog .el-dialog__body) { padding: 22px 24px; }
:global(.cm-confirm-dialog .el-dialog__footer) { padding: 16px 24px; background: var(--surface-low); border-top: 1px solid var(--card-border); }

.cm-confirm__head { display: flex; align-items: center; gap: 12px; text-align: left; }
.cm-confirm__head-icon { width: 36px; height: 36px; border-radius: 6px; background: var(--surface-low); color: var(--on-surface); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cm-confirm__head-icon .material-symbols-outlined { font-size: 20px; }
.cm-confirm__head-text { flex: 1; min-width: 0; }
.cm-confirm__eyebrow { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--on-surface-var); margin: 0 0 1px; }
.cm-confirm__title { font-size: 1.0625rem; font-weight: 700; color: var(--on-surface); letter-spacing: -.01em; margin: 0; }

.cm-confirm { display: flex; flex-direction: column; gap: 14px; }

.cm-confirm__item { display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--surface-low); border: 1px solid var(--card-border); border-radius: var(--card-radius); }
.cm-confirm__item-icon { width: 42px; height: 42px; border-radius: 6px; background: #fff; border: 1px solid var(--card-border); color: var(--on-surface); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cm-confirm__item-icon .material-symbols-outlined { font-size: 20px; }
.cm-confirm__item-text { flex: 1; min-width: 0; }
.cm-confirm__item-name { font-size: 13.5px; font-weight: 700; color: var(--on-surface); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cm-confirm__item-meta { font-size: 11.5px; color: var(--on-surface-var); margin: 2px 0 0; }
.cm-confirm__item-qty { font-size: 12.5px; font-weight: 700; color: var(--on-surface); background: #fff; border: 1px solid var(--card-border); border-radius: 999px; padding: 4px 10px; flex-shrink: 0; white-space: nowrap; }

.cm-confirm__row { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 13px; }
.cm-confirm__row span { color: var(--on-surface-var); }
.cm-confirm__row strong { color: var(--on-surface); font-weight: 700; text-align: right; }
.cm-confirm__row-label { display: inline-flex; align-items: center; gap: 6px; }
.cm-confirm__row-label .material-symbols-outlined { font-size: 15px; color: var(--on-surface-var); }

.cm-confirm__breakdown { display: flex; flex-direction: column; gap: 8px; padding-top: 12px; border-top: 1px solid var(--card-border); }
.cm-confirm__divider { height: 1px; background: var(--card-border); margin: 4px 0; }
.cm-confirm__row--total { padding-top: 6px; font-size: 15px; }
.cm-confirm__row--total span, .cm-confirm__row--total strong { font-weight: 800; color: var(--on-surface); }
</style>
