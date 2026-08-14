<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Bottom, OfficeBuilding, Phone, Lock } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    currency: { type: String, default: 'USD' },
    availableBalance: { type: [Number, String], default: 0 },
});

const emit = defineEmits(['update:modelValue']);

const open = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const mobileProviders = ['MTN Mobile Money', 'Airtel Money'];

const form = useForm({
    amount: '',
    payment_method: 'bank',
    bank: null,
    mobile_money: null,
});

/* ── Bank account input — full account number never leaves this component ── */
const bankName = ref('');
const accountHolder = ref('');
const accountNumber = ref('');
const bankTouched = reactive({ bankName: false, holder: false, number: false });

function onAccountNumberInput(event) {
    accountNumber.value = event.target.value.replace(/\D/g, '').slice(0, 34);
}

const bankNameValid = computed(() => bankName.value.trim().length > 1);
const accountHolderValid = computed(() => accountHolder.value.trim().length > 1);
const accountNumberValid = computed(() => accountNumber.value.length >= 6);
const bankFormValid = computed(() => bankNameValid.value && accountHolderValid.value && accountNumberValid.value);

/* ── Mobile money input ──────────────────────────────────────────────── */
const mobileProvider = ref(mobileProviders[0]);
const mobilePhone = ref('');
const mobileTouched = reactive({ phone: false });
const mobilePhoneValid = computed(() => /^[0-9+][0-9\s]{7,17}$/.test(mobilePhone.value.trim()));
const mobileFormValid = computed(() => mobilePhoneValid.value);

const amountValid = computed(() => {
    const value = Number(form.amount);
    return value > 0 && value <= Number(props.availableBalance);
});
const canSubmit = computed(() => {
    if (!amountValid.value) return false;
    return form.payment_method === 'bank' ? bankFormValid.value : mobileFormValid.value;
});

function reset() {
    form.reset();
    form.clearErrors();
    form.payment_method = 'bank';
    bankName.value = '';
    accountHolder.value = '';
    accountNumber.value = '';
    bankTouched.bankName = bankTouched.holder = bankTouched.number = false;
    mobileProvider.value = mobileProviders[0];
    mobilePhone.value = '';
    mobileTouched.phone = false;
}

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) reset();
});

function submit() {
    if (form.payment_method === 'bank') {
        bankTouched.bankName = bankTouched.holder = bankTouched.number = true;
        if (!bankFormValid.value) return;

        form.bank = {
            bank_name: bankName.value.trim(),
            account_holder: accountHolder.value.trim(),
            last4: accountNumber.value.slice(-4),
        };
        form.mobile_money = null;
    } else {
        mobileTouched.phone = true;
        if (!mobileFormValid.value) return;

        form.mobile_money = {
            provider: mobileProvider.value,
            phone: mobilePhone.value.trim(),
        };
        form.bank = null;
    }

    form.post(route('wallet.withdraw'), {
        preserveScroll: true,
        onSuccess: () => { open.value = false; reset(); },
    });
}
</script>

<template>
    <el-dialog v-model="open" width="460px" destroy-on-close align-center :show-close="true" class="wdr-modal" style="padding: 0">
        <template #header>
            <div class="wdr-modal-head">
                <div class="wdr-modal-head__icon"><el-icon :size="18"><Bottom /></el-icon></div>
                <div>
                    <div class="wdr-modal-eyebrow">Cash Out</div>
                    <div class="wdr-modal-title">Withdraw Funds</div>
                </div>
            </div>
        </template>

        <div class="wdr-modal-body">
            <form id="wdr-form" class="wdr-form" @submit.prevent="submit">
                <div class="wdr-field">
                    <label class="wdr-field__label">Amount ({{ currency }})</label>
                    <input v-model="form.amount" type="number" step="0.01" min="0.01" required placeholder="0.00" class="wdr-field__input">
                    <p v-if="form.errors.amount" class="wdr-error">{{ form.errors.amount }}</p>
                    <p v-else-if="Number(form.amount) > Number(availableBalance)" class="wdr-error">Amount exceeds your available balance.</p>
                </div>

                <div class="wdr-field">
                    <label class="wdr-field__label">Withdraw to</label>
                    <div class="wdr-segmented">
                        <button
                            type="button"
                            class="wdr-segmented__option"
                            :class="{ 'wdr-segmented__option--active': form.payment_method === 'bank' }"
                            @click="form.payment_method = 'bank'"
                        >
                            <el-icon><OfficeBuilding /></el-icon> Bank Account
                        </button>
                        <button
                            type="button"
                            class="wdr-segmented__option"
                            :class="{ 'wdr-segmented__option--active': form.payment_method === 'mobile_money' }"
                            @click="form.payment_method = 'mobile_money'"
                        >
                            <el-icon><Phone /></el-icon> Mobile Money
                        </button>
                    </div>
                </div>

                <Transition name="wdr-fade" mode="out-in">
                    <div v-if="form.payment_method === 'bank'" key="bank" class="wdr-method-fields">
                        <div class="wdr-field">
                            <label class="wdr-field__label">Bank name</label>
                            <div class="wdr-input-icon">
                                <el-icon class="wdr-input-icon__glyph"><OfficeBuilding /></el-icon>
                                <input v-model="bankName" type="text" placeholder="e.g. Stanbic Bank" class="wdr-field__input wdr-field__input--icon" @blur="bankTouched.bankName = true">
                            </div>
                            <p v-if="bankTouched.bankName && !bankNameValid" class="wdr-error">Enter the bank name.</p>
                        </div>
                        <div class="wdr-field">
                            <label class="wdr-field__label">Account holder name</label>
                            <input v-model="accountHolder" type="text" placeholder="Name on the account" class="wdr-field__input" @blur="bankTouched.holder = true">
                            <p v-if="bankTouched.holder && !accountHolderValid" class="wdr-error">Enter the account holder's name.</p>
                        </div>
                        <div class="wdr-field">
                            <label class="wdr-field__label">Account number</label>
                            <input :value="accountNumber" type="text" inputmode="numeric" placeholder="0000000000" maxlength="34" class="wdr-field__input" @input="onAccountNumberInput" @blur="bankTouched.number = true">
                            <p v-if="bankTouched.number && !accountNumberValid" class="wdr-error">Enter a valid account number.</p>
                        </div>
                        <p class="wdr-note"><el-icon><Lock /></el-icon> Only the last 4 digits of your account number are stored.</p>
                    </div>

                    <div v-else key="mobile" class="wdr-method-fields">
                        <div class="wdr-field">
                            <label class="wdr-field__label">Provider</label>
                            <select v-model="mobileProvider" class="wdr-field__input">
                                <option v-for="provider in mobileProviders" :key="provider" :value="provider">{{ provider }}</option>
                            </select>
                        </div>
                        <div class="wdr-field">
                            <label class="wdr-field__label">Mobile money number</label>
                            <div class="wdr-input-icon">
                                <el-icon class="wdr-input-icon__glyph"><Phone /></el-icon>
                                <input v-model="mobilePhone" type="tel" placeholder="07XX XXX XXX" class="wdr-field__input wdr-field__input--icon" @blur="mobileTouched.phone = true">
                            </div>
                            <p v-if="mobileTouched.phone && !mobilePhoneValid" class="wdr-error">Enter a valid phone number.</p>
                        </div>
                        <p class="wdr-note"><el-icon><Lock /></el-icon> You'll receive a prompt on this number to confirm the withdrawal.</p>
                    </div>
                </Transition>

                <p v-if="form.errors.wallet" class="wdr-error">{{ form.errors.wallet }}</p>
            </form>
        </div>

        <template #footer>
            <div class="wdr-modal-footer">
                <button type="submit" form="wdr-form" :disabled="form.processing || !canSubmit" class="wdr-modal-btn wdr-modal-btn--primary">
                    <el-icon :size="14"><Bottom /></el-icon> {{ form.processing ? 'Withdrawing…' : 'Withdraw' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
/* Matches .wal-modal / .dep-modal chrome so every wallet dialog shares the
   same look. !important here overrides Element Plus's own dialog padding,
   which lives on the outer .el-dialog element itself. */
:deep(.el-dialog.wdr-modal) {
    border-radius: 18px !important;
    padding: 0 !important;
    overflow: hidden !important;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.wdr-modal .el-dialog__header) { padding: 0 !important; margin: 0; }
:deep(.el-dialog.wdr-modal .el-dialog__body) { padding: 0 !important; }
:deep(.el-dialog.wdr-modal .el-dialog__footer) { padding: 0 !important; }

.wdr-modal-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
    border-radius: 18px 18px 0 0;
}

.wdr-modal-head__icon {
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

.wdr-modal-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.wdr-modal-title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.wdr-modal-body { padding: 20px 24px; max-height: 70vh; overflow-y: auto; }

.wdr-form { display: flex; flex-direction: column; gap: 16px; }

.wdr-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.wdr-field__label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; }
.wdr-field__input {
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
.wdr-field__input:focus {
    outline: none;
    border-color: #004532;
    box-shadow: 0 0 0 3px rgba(0, 69, 50, 0.08);
}

.wdr-field__input--icon { padding-left: 34px; }

.wdr-error { font-size: 0.6875rem; color: #991b1b; margin: 0; font-weight: 600; }
.wdr-note { display: flex; align-items: flex-start; gap: 5px; font-size: 0.6875rem; color: #9ca3af; margin: 2px 0 0; line-height: 1.5; }
.wdr-note :deep(.el-icon) { flex-shrink: 0; margin-top: 2px; color: #004532; opacity: .7; }

.wdr-method-fields { display: flex; flex-direction: column; gap: 16px; }

/* ── Input icon adornment ────────────────────────────────────────────── */
.wdr-input-icon { position: relative; }
.wdr-input-icon__glyph {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; font-size: 14px; pointer-events: none;
}

/* ── Withdraw destination — segmented control ───────────────────────────── */
.wdr-segmented { display: flex; gap: 2px; padding: 3px; background: #f3f4f6; border-radius: 10px; }
.wdr-segmented__option {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
    border: none; border-radius: 8px; background: transparent;
    color: #6b7280; font-size: .78125rem; font-weight: 700;
    padding: 9px 10px; cursor: pointer; min-width: 0;
    transition: background .15s ease, color .15s ease, box-shadow .15s ease;
}
.wdr-segmented__option :deep(.el-icon) { font-size: 14px; flex-shrink: 0; }
.wdr-segmented__option:hover { color: #374151; }
.wdr-segmented__option--active { background: #fff; color: #004532; box-shadow: 0 1px 3px rgba(17, 24, 39, .1); }

/* ── Transition between withdraw methods ─────────────────────────────── */
.wdr-fade-enter-active, .wdr-fade-leave-active { transition: opacity .15s ease, transform .15s ease; }
.wdr-fade-enter-from { opacity: 0; transform: translateY(6px); }
.wdr-fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* ── Footer ──────────────────────────────────────────────────────────── */
.wdr-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
    border-radius: 0 0 18px 18px;
}

.wdr-modal-btn {
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

.wdr-modal-btn--primary {
    background: linear-gradient(135deg, #004532, #065f46);
    color: #fff;
}

.wdr-modal-btn--primary:hover:not(:disabled) { opacity: 0.9; }
.wdr-modal-btn--primary:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
