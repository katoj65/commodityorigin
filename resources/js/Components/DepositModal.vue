<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Top, CreditCard, Phone, Lock } from '@element-plus/icons-vue';
import { luhnValid, expiryValid, detectCardBrand, formatCardNumber, formatExpiry } from '@/utils/card';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    currency: { type: String, default: 'USD' },
});

const emit = defineEmits(['update:modelValue']);

const open = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const presets = [50, 100, 250, 500];
const mobileProviders = ['MTN Mobile Money', 'Airtel Money'];

const form = useForm({
    amount: '',
    payment_method: 'card',
    card: null,
    mobile_money: null,
});

/* ── Card input — full number/CVV never leave this component ───────────── */
const cardNumber = ref('');
const cardHolder = ref('');
const cardExpiry = ref('');
const cardCvv = ref('');
const cardTouched = reactive({ number: false, holder: false, expiry: false, cvv: false });
const cardDigits = computed(() => cardNumber.value.replace(/\D/g, ''));

function onCardNumberInput(event) {
    cardNumber.value = formatCardNumber(event.target.value);
}

function onCardExpiryInput(event) {
    cardExpiry.value = formatExpiry(event.target.value);
}

const cardNumberValid = computed(() => luhnValid(cardDigits.value));
const cardExpiryValid = computed(() => expiryValid(cardExpiry.value));
const cardCvvValid = computed(() => /^\d{3,4}$/.test(cardCvv.value));
const cardHolderValid = computed(() => cardHolder.value.trim().length > 1);
const cardFormValid = computed(() => cardNumberValid.value && cardExpiryValid.value && cardCvvValid.value && cardHolderValid.value);

/* ── Mobile money input ──────────────────────────────────────────────── */
const mobileProvider = ref(mobileProviders[0]);
const mobilePhone = ref('');
const mobileTouched = reactive({ phone: false });
const mobilePhoneValid = computed(() => /^[0-9+][0-9\s]{7,17}$/.test(mobilePhone.value.trim()));
const mobileFormValid = computed(() => mobilePhoneValid.value);

const amountValid = computed(() => Number(form.amount) > 0);
const canSubmit = computed(() => {
    if (!amountValid.value) return false;
    return form.payment_method === 'card' ? cardFormValid.value : mobileFormValid.value;
});

function reset() {
    form.reset();
    form.clearErrors();
    form.payment_method = 'card';
    cardNumber.value = '';
    cardHolder.value = '';
    cardExpiry.value = '';
    cardCvv.value = '';
    cardTouched.number = cardTouched.holder = cardTouched.expiry = cardTouched.cvv = false;
    mobileProvider.value = mobileProviders[0];
    mobilePhone.value = '';
    mobileTouched.phone = false;
}

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) reset();
});

function pickPreset(value) {
    form.amount = value;
}

function submit() {
    if (form.payment_method === 'card') {
        cardTouched.number = cardTouched.holder = cardTouched.expiry = cardTouched.cvv = true;
        if (!cardFormValid.value) return;

        form.card = {
            holder: cardHolder.value.trim(),
            last4: cardDigits.value.slice(-4),
            brand: detectCardBrand(cardDigits.value),
            expiry: cardExpiry.value,
        };
        form.mobile_money = null;
    } else {
        mobileTouched.phone = true;
        if (!mobileFormValid.value) return;

        form.mobile_money = {
            provider: mobileProvider.value,
            phone: mobilePhone.value.trim(),
        };
        form.card = null;
    }

    form.post(route('wallet.deposit'), {
        preserveScroll: true,
        onSuccess: () => { open.value = false; reset(); },
    });
}
</script>

<template>
    <el-dialog v-model="open" width="460px" destroy-on-close align-center :show-close="true" class="dep-modal" style="padding: 0">
        <template #header>
            <div class="dep-modal-head">
                <div class="dep-modal-head__icon"><el-icon :size="18"><Top /></el-icon></div>
                <div>
                    <div class="dep-modal-eyebrow">Add Money</div>
                    <div class="dep-modal-title">Deposit Funds</div>
                </div>
            </div>
        </template>

        <div class="dep-modal-body">
            <form id="dep-form" class="dep-form" @submit.prevent="submit">
                <div class="dep-field">
                    <label class="dep-field__label">Amount ({{ currency }})</label>
                    <input v-model="form.amount" type="number" step="0.01" min="0.01" required placeholder="0.00" class="dep-field__input">
                    <p v-if="form.errors.amount" class="dep-error">{{ form.errors.amount }}</p>
                    <div class="dep-preset-grid">
                        <button
                            v-for="preset in presets"
                            :key="preset"
                            type="button"
                            class="dep-preset-chip"
                            :class="{ 'dep-preset-chip--active': Number(form.amount) === preset }"
                            @click="pickPreset(preset)"
                        >
                            {{ currency }} {{ preset }}
                        </button>
                    </div>
                </div>

                <div class="dep-field">
                    <label class="dep-field__label">Payment method</label>
                    <div class="dep-segmented">
                        <button
                            type="button"
                            class="dep-segmented__option"
                            :class="{ 'dep-segmented__option--active': form.payment_method === 'card' }"
                            @click="form.payment_method = 'card'"
                        >
                            <el-icon><CreditCard /></el-icon> Card
                        </button>
                        <button
                            type="button"
                            class="dep-segmented__option"
                            :class="{ 'dep-segmented__option--active': form.payment_method === 'mobile_money' }"
                            @click="form.payment_method = 'mobile_money'"
                        >
                            <el-icon><Phone /></el-icon> Mobile Money
                        </button>
                    </div>
                </div>

                <Transition name="dep-fade" mode="out-in">
                    <div v-if="form.payment_method === 'card'" key="card" class="dep-method-fields">
                        <div class="dep-field">
                            <label class="dep-field__label">Card number</label>
                            <div class="dep-input-icon">
                                <el-icon class="dep-input-icon__glyph"><CreditCard /></el-icon>
                                <input
                                    :value="cardNumber"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="1234 5678 9012 3456"
                                    maxlength="23"
                                    class="dep-field__input dep-field__input--icon"
                                    @input="onCardNumberInput"
                                    @blur="cardTouched.number = true"
                                >
                            </div>
                            <p v-if="cardTouched.number && !cardNumberValid" class="dep-error">Enter a valid card number.</p>
                        </div>
                        <div class="dep-field">
                            <label class="dep-field__label">Cardholder name</label>
                            <input v-model="cardHolder" type="text" placeholder="Name as shown on card" class="dep-field__input" @blur="cardTouched.holder = true">
                            <p v-if="cardTouched.holder && !cardHolderValid" class="dep-error">Enter the cardholder's name.</p>
                        </div>
                        <div class="dep-field-grid">
                            <div class="dep-field">
                                <label class="dep-field__label">Expiry (MM/YY)</label>
                                <input :value="cardExpiry" type="text" inputmode="numeric" placeholder="MM/YY" maxlength="5" class="dep-field__input" @input="onCardExpiryInput" @blur="cardTouched.expiry = true">
                                <p v-if="cardTouched.expiry && !cardExpiryValid" class="dep-error">Invalid or expired.</p>
                            </div>
                            <div class="dep-field">
                                <label class="dep-field__label">CVV</label>
                                <input v-model="cardCvv" type="text" inputmode="numeric" placeholder="123" maxlength="4" class="dep-field__input" @blur="cardTouched.cvv = true">
                                <p v-if="cardTouched.cvv && !cardCvvValid" class="dep-error">3 or 4 digits.</p>
                            </div>
                        </div>
                        <p class="dep-note"><el-icon><Lock /></el-icon> Your card number and CVV are never sent to or stored on our servers.</p>
                    </div>

                    <div v-else key="mobile" class="dep-method-fields">
                        <div class="dep-field">
                            <label class="dep-field__label">Provider</label>
                            <select v-model="mobileProvider" class="dep-field__input">
                                <option v-for="provider in mobileProviders" :key="provider" :value="provider">{{ provider }}</option>
                            </select>
                        </div>
                        <div class="dep-field">
                            <label class="dep-field__label">Mobile money number</label>
                            <div class="dep-input-icon">
                                <el-icon class="dep-input-icon__glyph"><Phone /></el-icon>
                                <input v-model="mobilePhone" type="tel" placeholder="07XX XXX XXX" class="dep-field__input dep-field__input--icon" @blur="mobileTouched.phone = true">
                            </div>
                            <p v-if="mobileTouched.phone && !mobilePhoneValid" class="dep-error">Enter a valid phone number.</p>
                        </div>
                        <p class="dep-note"><el-icon><Lock /></el-icon> You'll receive a prompt on this number to approve the deposit.</p>
                    </div>
                </Transition>

                <p v-if="form.errors.wallet" class="dep-error">{{ form.errors.wallet }}</p>
            </form>
        </div>

        <template #footer>
            <div class="dep-modal-footer">
                <button type="submit" form="dep-form" :disabled="form.processing || !canSubmit" class="dep-modal-btn dep-modal-btn--primary">
                    <el-icon :size="14"><Top /></el-icon> {{ form.processing ? 'Depositing…' : 'Deposit' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
/* Matches .wal-modal (WalletPage.vue Transfer/Withdraw dialogs) so every
   wallet dialog in the app shares the same chrome. Element Plus puts the
   dialog's only horizontal inset on the OUTER .el-dialog itself
   (padding: var(--el-dialog-padding-primary), ~16px all sides) — header/
   body/footer have no horizontal padding of their own. !important here is
   deliberate: it's overriding third-party library internals whose
   specificity/cascade shouldn't be left to chance. */
:deep(.el-dialog.dep-modal) {
    border-radius: 18px !important;
    padding: 0 !important;
    overflow: hidden !important;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.dep-modal .el-dialog__header) { padding: 0 !important; margin: 0; }
:deep(.el-dialog.dep-modal .el-dialog__body) { padding: 0 !important; }
:deep(.el-dialog.dep-modal .el-dialog__footer) { padding: 0 !important; }

.dep-modal-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
    border-radius: 18px 18px 0 0;
}

.dep-modal-head__icon {
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

.dep-modal-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.dep-modal-title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.dep-modal-body { padding: 20px 24px; max-height: 70vh; overflow-y: auto; }

.dep-form { display: flex; flex-direction: column; gap: 16px; }

.dep-field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.dep-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.dep-field__label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; }
.dep-field__input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 0.8125rem;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #fff;
    color: #111827;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.dep-field__input:focus {
    outline: none;
    border-color: #004532;
    box-shadow: 0 0 0 3px rgba(0, 69, 50, 0.08);
}

.dep-field__input--icon { padding-left: 34px; }

.dep-error { font-size: 0.6875rem; color: #991b1b; margin: 0; font-weight: 600; }
.dep-note { display: flex; align-items: flex-start; gap: 5px; font-size: 0.6875rem; color: #9ca3af; margin: 2px 0 0; line-height: 1.5; }
.dep-note :deep(.el-icon) { flex-shrink: 0; margin-top: 2px; color: #004532; opacity: .7; }

.dep-method-fields { display: flex; flex-direction: column; gap: 16px; }

/* ── Input icon adornment ────────────────────────────────────────────── */
.dep-input-icon { position: relative; }
.dep-input-icon__glyph {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; font-size: 14px; pointer-events: none;
}

/* ── Amount presets ──────────────────────────────────────────────────── */
.dep-preset-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; margin-top: 2px; }
.dep-preset-chip {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    color: #374151;
    font-size: 0.75rem;
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
    padding: 8px 4px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease, transform .1s ease;
}
.dep-preset-chip:hover { border-color: #004532; }
.dep-preset-chip:active { transform: scale(.96); }
.dep-preset-chip--active { background: #004532; border-color: #004532; color: #fff; }

/* ── Payment method — segmented control ───────────────────────────────── */
.dep-segmented { display: flex; gap: 2px; padding: 3px; background: #f3f4f6; border-radius: 10px; }
.dep-segmented__option {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
    border: none; border-radius: 8px; background: transparent;
    color: #6b7280; font-size: .78125rem; font-weight: 700;
    padding: 9px 10px; cursor: pointer; min-width: 0;
    transition: background .15s ease, color .15s ease, box-shadow .15s ease;
}
.dep-segmented__option :deep(.el-icon) { font-size: 14px; flex-shrink: 0; }
.dep-segmented__option:hover { color: #374151; }
.dep-segmented__option--active { background: #fff; color: #004532; box-shadow: 0 1px 3px rgba(17, 24, 39, .1); }

/* ── Transition between payment methods ──────────────────────────────── */
.dep-fade-enter-active, .dep-fade-leave-active { transition: opacity .15s ease, transform .15s ease; }
.dep-fade-enter-from { opacity: 0; transform: translateY(6px); }
.dep-fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* ── Footer ──────────────────────────────────────────────────────────── */
.dep-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
    border-radius: 0 0 18px 18px;
}

.dep-modal-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s ease, background 0.15s ease;
}

.dep-modal-btn--primary {
    background: linear-gradient(135deg, #004532, #065f46);
    color: #fff;
}

.dep-modal-btn--primary:hover:not(:disabled) { opacity: 0.9; }
.dep-modal-btn--primary:disabled { opacity: 0.5; cursor: not-allowed; }

@media (max-width: 480px) {
    .dep-field-grid { grid-template-columns: 1fr; }
    .dep-preset-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
</style>
