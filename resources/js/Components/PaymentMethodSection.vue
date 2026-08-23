<script setup>
import { computed, reactive, ref } from 'vue';
import { CreditCard, Wallet as WalletIcon, Lock, CircleCheckFilled, WarningFilled } from '@element-plus/icons-vue';
import { luhnValid, expiryValid, detectCardBrand, formatCardNumber as formatCardNumberValue, formatExpiry as formatExpiryValue } from '@/utils/card';

const props = defineProps({
    modelValue: { type: String, default: 'wallet' },
    walletBalance: { type: Number, default: 0 },
    walletCoversTotal: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const paymentMethod = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(value || 0);

/* ── Card input — full number/CVV never leave this component ───────────── */
const cardNumber = ref('');
const cardHolder = ref('');
const cardExpiry = ref('');
const cardCvv = ref('');
const cardTouched = reactive({ number: false, holder: false, expiry: false, cvv: false });

const cardDigits = computed(() => cardNumber.value.replace(/\D/g, ''));

function formatCardNumber(event) {
    cardNumber.value = formatCardNumberValue(event.target.value);
}

function formatExpiry(event) {
    cardExpiry.value = formatExpiryValue(event.target.value);
}

const cardNumberValid = computed(() => luhnValid(cardDigits.value));
const cardExpiryValid = computed(() => expiryValid(cardExpiry.value));
const cardCvvValid = computed(() => /^\d{3,4}$/.test(cardCvv.value));
const cardHolderValid = computed(() => cardHolder.value.trim().length > 1);
const cardFormValid = computed(() => cardNumberValid.value && cardExpiryValid.value && cardCvvValid.value && cardHolderValid.value);
const cardBrand = computed(() => (cardDigits.value.length ? detectCardBrand(cardDigits.value) : null));

defineExpose({
    isCardValid: () => cardFormValid.value,
    touchCard: () => {
        cardTouched.number = cardTouched.holder = cardTouched.expiry = cardTouched.cvv = true;
    },
    buildCardPayload: () => ({
        holder: cardHolder.value.trim(),
        last4: cardDigits.value.slice(-4),
        brand: detectCardBrand(cardDigits.value),
        expiry: cardExpiry.value,
    }),
});
</script>

<template>
    <section class="pm-card">
        <div class="pm-card__head">
            <div>
                <h2 class="pm-card__title">Payment Method</h2>
                <p class="pm-card__subtitle">All transactions are secure and encrypted.</p>
            </div>
            <div class="pm-trust-badge"><el-icon><CircleCheckFilled /></el-icon> Secure Checkout</div>
        </div>

        <div class="pm-tabs">
            <label class="pm-tab" :class="{ 'pm-tab--active': paymentMethod === 'wallet' }">
                <input type="radio" name="payment_method" class="pm-tab__radio-input" :checked="paymentMethod === 'wallet'" @change="paymentMethod = 'wallet'">
                <span class="pm-tab__radio"><span /></span>
                <span class="pm-tab__icon"><el-icon><WalletIcon /></el-icon></span>
                <span class="pm-tab__body">
                    <span class="pm-tab__label">Wallet Balance</span>
                    <span class="pm-tab__meta">{{ formatCurrency(walletBalance) }} available</span>
                </span>
            </label>
            <label class="pm-tab" :class="{ 'pm-tab--active': paymentMethod === 'card' }">
                <input type="radio" name="payment_method" class="pm-tab__radio-input" :checked="paymentMethod === 'card'" @change="paymentMethod = 'card'">
                <span class="pm-tab__radio"><span /></span>
                <span class="pm-tab__icon"><el-icon><CreditCard /></el-icon></span>
                <span class="pm-tab__body">
                    <span class="pm-tab__label">Credit / Debit Card</span>
                    <span class="pm-tab__meta">Visa, Mastercard, Amex</span>
                </span>
            </label>
        </div>

        <p v-if="paymentMethod === 'wallet' && !walletCoversTotal" class="pm-wallet-warning">
            <el-icon><WarningFilled /></el-icon>
            Your wallet balance doesn't cover this order's total. Top up your wallet or pay by card instead.
        </p>

        <form v-if="paymentMethod === 'card'" class="pm-field-grid" @submit.prevent>
            <label class="pm-field pm-field--full">
                <span>Cardholder Name</span>
                <input v-model="cardHolder" type="text" placeholder="AS APPEARS ON CARD" @blur="cardTouched.holder = true">
                <small v-if="cardTouched.holder && !cardHolderValid" class="pm-error">Enter the cardholder's name.</small>
            </label>
            <label class="pm-field pm-field--full">
                <span>Card Number</span>
                <div class="pm-card-number">
                    <input
                        :value="cardNumber"
                        type="text"
                        inputmode="numeric"
                        placeholder="XXXX XXXX XXXX XXXX"
                        maxlength="23"
                        @input="formatCardNumber"
                        @blur="cardTouched.number = true"
                    >
                    <span class="pm-card-number__chips">
                        <span class="pm-chip pm-chip--visa" :class="{ 'pm-chip--dim': cardBrand && cardBrand !== 'Visa' }">VISA</span>
                        <span class="pm-chip pm-chip--mc" :class="{ 'pm-chip--dim': cardBrand && cardBrand !== 'Mastercard' }" />
                    </span>
                </div>
                <small v-if="cardTouched.number && !cardNumberValid" class="pm-error">Enter a valid card number.</small>
            </label>
            <label class="pm-field">
                <span>Expiry Date</span>
                <input :value="cardExpiry" type="text" inputmode="numeric" placeholder="MM/YY" maxlength="5" class="pm-field--center" @input="formatExpiry" @blur="cardTouched.expiry = true">
                <small v-if="cardTouched.expiry && !cardExpiryValid" class="pm-error">Enter a valid, unexpired date.</small>
            </label>
            <label class="pm-field">
                <span>CVV</span>
                <input v-model="cardCvv" type="text" inputmode="numeric" placeholder="XXX" maxlength="4" class="pm-field--center" @blur="cardTouched.cvv = true">
                <small v-if="cardTouched.cvv && !cardCvvValid" class="pm-error">3 or 4 digits.</small>
            </label>
            <p class="pm-card-note pm-field--full"><el-icon><Lock /></el-icon> Your card number and CVV are never sent to or stored on our servers.</p>
        </form>

        <div class="pm-divider" />
        <slot name="billing" />

        <slot name="footer" />
    </section>
</template>

<style scoped>
.pm-card {
    --green: #271310;
    --green-dark: #1a0d0b;
    --border: #d3c3c0;
    --on-surface: #1a1c1c;
    --on-surface-var: #504442;
    --surface-low: #f3f3f3;
    font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background: #fff;
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 1px 2px rgba(39, 19, 16, .06), 0 1px 1px rgba(39, 19, 16, .04);
}

.pm-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 2rem; }
.pm-card__title { font-size: 1.375rem; font-weight: 800; letter-spacing: -0.01em; color: var(--on-surface); margin: 0 0 6px; }
.pm-card__subtitle { font-size: .875rem; color: var(--on-surface-var); margin: 0; }

.pm-trust-badge {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    padding: 5px 12px; border-radius: 999px; background: #ecfdf5; color: #059669;
    font-size: .75rem; font-weight: 700; white-space: nowrap;
}
.pm-trust-badge :deep(.el-icon) { font-size: 14px; }

/* ── Payment tabs ────────────────────────────────────────────────────── */
.pm-tabs { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; margin-bottom: 2rem; }
.pm-tab {
    position: relative;
    display: flex; align-items: center; gap: 12px; text-align: left;
    border: 2px solid transparent; border-radius: 12px; padding: 15px 16px;
    background: var(--surface-low); cursor: pointer; min-width: 0;
    transition: border-color .15s ease, background .15s ease;
}
.pm-tab__radio-input { position: absolute; opacity: 0; pointer-events: none; }
.pm-tab__radio {
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--border);
}
.pm-tab__radio span { width: 10px; height: 10px; border-radius: 50%; background: var(--green); opacity: 0; transition: opacity .15s ease; }

.pm-tab__icon { display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--on-surface-var); }
.pm-tab__icon :deep(.el-icon) { font-size: 18px; }

.pm-tab__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1 1 auto; }
.pm-tab__label { font-size: .8125rem; font-weight: 700; color: var(--on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pm-tab__meta { font-size: .6875rem; color: var(--on-surface-var); font-variant-numeric: tabular-nums; overflow-wrap: break-word; line-height: 1.4; }

.pm-tab--active { border-color: var(--green); background: rgba(39, 19, 16, .04); }
.pm-tab--active .pm-tab__radio { border-color: var(--green); }
.pm-tab--active .pm-tab__radio span { opacity: 1; }
.pm-tab--active .pm-tab__icon { color: var(--green); }
.pm-tab--active .pm-tab__label { color: var(--green); }

.pm-wallet-warning { display: flex; align-items: center; gap: 6px; font-size: .75rem; color: #b45309; background: #fffbeb; border-radius: 8px; padding: 8px 12px; margin: -1rem 0 1.5rem; }
.pm-wallet-warning :deep(.el-icon) { flex-shrink: 0; }

/* ── Card fields ─────────────────────────────────────────────────────── */
.pm-field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
.pm-field { display: flex; flex-direction: column; gap: 8px; font-size: .75rem; font-weight: 700; color: var(--on-surface-var); text-transform: uppercase; letter-spacing: .04em; }
.pm-field--full { grid-column: 1 / -1; }
.pm-field input {
    border: none; border-bottom: 2px solid var(--surface-low); border-radius: 10px 10px 0 0; padding: 14px 14px;
    font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-size: 1rem; font-weight: 500; color: var(--on-surface); background: var(--surface-low);
    text-transform: none; letter-spacing: normal;
}
.pm-field input::placeholder { color: #c7bcb9; }
.pm-field input:focus { outline: none; border-bottom-color: var(--green); }
.pm-field--center { text-align: center; font-family: ui-monospace, monospace; }
.pm-error { color: #dc2626; font-weight: 600; text-transform: none; }

.pm-card-number { position: relative; display: flex; align-items: center; }
.pm-card-number input { width: 100%; padding-right: 96px; font-family: ui-monospace, monospace; letter-spacing: .06em; }
.pm-card-number__chips { position: absolute; right: 14px; display: flex; gap: 6px; }
.pm-chip { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 21px; border-radius: 4px; font-size: 7px; font-weight: 800; font-style: italic; color: #fff; transition: opacity .15s ease; }
.pm-chip--visa { background: #1434cb; }
.pm-chip--mc { background: linear-gradient(90deg, #eb001b 50%, #f79e1b 50%); border-radius: 50%; width: 21px; }
.pm-chip--dim { opacity: .35; }

.pm-card-note { display: flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 500; text-transform: none; color: var(--on-surface-var); margin: 0; }
.pm-card-note :deep(.el-icon) { color: var(--green); flex-shrink: 0; }

.pm-divider { border-top: 1px solid rgba(39, 19, 16, .12); margin: 1.75rem 0; }

@media (max-width: 640px) {
    .pm-card { padding: 1.5rem; }
    .pm-tabs { grid-template-columns: 1fr; }
    .pm-field-grid { grid-template-columns: 1fr; }
}
</style>
