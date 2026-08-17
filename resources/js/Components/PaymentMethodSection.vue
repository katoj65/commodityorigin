<script setup>
import { computed, reactive, ref } from 'vue';
import { CreditCard, Wallet as WalletIcon, Lock, WarningFilled, CircleCheck } from '@element-plus/icons-vue';
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
        <h2 class="pm-card__title mb-3"><el-icon><CreditCard /></el-icon> Payment Method</h2>

        <div class="pm-tabs">
            <button
                type="button"
                class="pm-tab"
                :class="{ 'pm-tab--active': paymentMethod === 'wallet' }"
                @click="paymentMethod = 'wallet'"
            >
                <span class="pm-tab__icon"><el-icon><WalletIcon /></el-icon></span>
                <span class="pm-tab__body">
                    <span class="pm-tab__label">Wallet Balance</span>
                    <span class="pm-tab__meta">{{ formatCurrency(walletBalance) }} available</span>
                </span>
                <span class="pm-tab__check"><el-icon><CircleCheck /></el-icon></span>
            </button>
            <button
                type="button"
                class="pm-tab"
                :class="{ 'pm-tab--active': paymentMethod === 'card' }"
                @click="paymentMethod = 'card'"
            >
                <span class="pm-tab__icon"><el-icon><CreditCard /></el-icon></span>
                <span class="pm-tab__body">
                    <span class="pm-tab__label">Credit / Debit Card</span>
                    <span class="pm-tab__meta">Visa, Mastercard, Amex</span>
                </span>
                <span class="pm-tab__check"><el-icon><CircleCheck /></el-icon></span>
            </button>
        </div>

        <p v-if="paymentMethod === 'wallet' && !walletCoversTotal" class="pm-wallet-warning">
            <el-icon><WarningFilled /></el-icon>
            Your wallet balance doesn't cover this order's total. Top up your wallet or pay by card instead.
        </p>

        <div v-if="paymentMethod === 'card'" class="pm-field-grid">
            <label class="pm-field pm-field--full">
                <span>Card number</span>
                <input
                    :value="cardNumber"
                    type="text"
                    inputmode="numeric"
                    placeholder="1234 5678 9012 3456"
                    maxlength="23"
                    @input="formatCardNumber"
                    @blur="cardTouched.number = true"
                >
                <small v-if="cardTouched.number && !cardNumberValid" class="pm-error">Enter a valid card number.</small>
            </label>
            <label class="pm-field pm-field--full">
                <span>Cardholder name</span>
                <input v-model="cardHolder" type="text" placeholder="Name as shown on card" @blur="cardTouched.holder = true">
                <small v-if="cardTouched.holder && !cardHolderValid" class="pm-error">Enter the cardholder's name.</small>
            </label>
            <label class="pm-field">
                <span>Expiry (MM/YY)</span>
                <input :value="cardExpiry" type="text" inputmode="numeric" placeholder="MM/YY" maxlength="5" @input="formatExpiry" @blur="cardTouched.expiry = true">
                <small v-if="cardTouched.expiry && !cardExpiryValid" class="pm-error">Enter a valid, unexpired date.</small>
            </label>
            <label class="pm-field">
                <span>CVV</span>
                <input v-model="cardCvv" type="text" inputmode="numeric" placeholder="123" maxlength="4" @blur="cardTouched.cvv = true">
                <small v-if="cardTouched.cvv && !cardCvvValid" class="pm-error">3 or 4 digits.</small>
            </label>
            <p class="pm-card-note"><el-icon><Lock /></el-icon> Your card number and CVV are never sent to or stored on our servers.</p>
        </div>
    </section>
</template>

<style scoped>
.pm-card {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.5rem;
}

.pm-card__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .9375rem;
    font-weight: 800;
    color: var(--on-surface);
    margin: 0 0 1.25rem;
}

.pm-card__title :deep(.el-icon) { color: var(--green); font-size: 16px; }

/* ── Payment tabs ────────────────────────────────────────────────────── */
.pm-tabs { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 12px; }
.pm-tab {
    display: flex; align-items: center; gap: 12px; text-align: left;
    border: 1.5px solid var(--border); border-radius: 12px; padding: 13px 14px;
    background: #fff; cursor: pointer; min-width: 0;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
}
.pm-tab:hover { border-color: #c7d1cb; }

.pm-tab__icon {
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    width: 36px; height: 36px; border-radius: 10px;
    background: var(--surface-low); color: var(--on-surface-var);
    transition: background .15s ease, color .15s ease;
}
.pm-tab__icon :deep(.el-icon) { font-size: 16px; }

.pm-tab__body { display: flex; flex-direction: column; gap: 3px; min-width: 0; flex: 1 1 auto; }
.pm-tab__label { font-size: .8125rem; font-weight: 700; color: var(--on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pm-tab__meta { font-size: .6875rem; color: var(--on-surface-var); font-variant-numeric: tabular-nums; overflow-wrap: break-word; line-height: 1.4; }

.pm-tab__check { flex-shrink: 0; display: flex; color: var(--green); font-size: 18px; opacity: 0; transform: scale(.6); transition: opacity .15s ease, transform .15s ease; }
.pm-tab__check :deep(.el-icon) { font-size: 18px; }

.pm-tab--active { border-color: var(--green); background: #f0f5f3; box-shadow: 0 0 0 3px rgba(0, 69, 50, .08); }
.pm-tab--active .pm-tab__icon { background: var(--green); color: #fff; }
.pm-tab--active .pm-tab__check { opacity: 1; transform: scale(1); }

.pm-wallet-warning { display: flex; align-items: center; gap: 6px; font-size: .75rem; color: #b45309; background: #fffbeb; border-radius: 8px; padding: 8px 12px; margin: 12px 0 0; }
.pm-wallet-warning :deep(.el-icon) { flex-shrink: 0; }

/* ── Card fields ─────────────────────────────────────────────────────── */
.pm-field-grid {
    display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;
    margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px dashed var(--border);
}
.pm-field { display: flex; flex-direction: column; gap: 5px; font-size: .75rem; font-weight: 700; color: var(--on-surface-var); }
.pm-field--full { grid-column: 1 / -1; }
.pm-field input {
    border: 1px solid var(--border); border-radius: 8px; padding: 9px 11px;
    font-family: 'Manrope', system-ui, sans-serif; font-size: .8125rem; font-weight: 500; color: var(--on-surface);
    background: #fff;
}
.pm-field input:focus { outline: none; border-color: var(--green); }
.pm-error { color: #dc2626; font-weight: 600; }

.pm-card-note { grid-column: 1 / -1; display: flex; align-items: center; gap: 5px; font-size: .6875rem; color: var(--on-surface-var); margin: 0; }
.pm-card-note :deep(.el-icon) { color: var(--green); flex-shrink: 0; }

@media (max-width: 640px) {
    .pm-tabs { grid-template-columns: 1fr; }
    .pm-field-grid { grid-template-columns: 1fr; }
}
</style>
