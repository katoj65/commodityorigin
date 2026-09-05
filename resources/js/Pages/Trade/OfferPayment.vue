<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Box, Coffee, Coin, CreditCard, Document, Goods, Lock, Medal, ShoppingCart, Tickets, User, Wallet, WarningFilled } from '@element-plus/icons-vue';

const props = defineProps({
    offer: { type: Object, default: () => ({}) },
    response: { type: Object, default: null },
    walletBalance: { type: Number, default: 0 },
});

const form = useForm({
    payment_method: '',
    notes: '',
});

const paymentMethods = ['Wallet', 'Bank Transfer', 'Mobile Money', 'Card', 'Escrow'];

function submitPayment() {
    if (!form.payment_method) {
        form.setError('payment_method', 'Select a payment method.');
        return;
    }
    form.post(route('trade.offer.payment.store', props.offer.id));
}

function formatMoney(amount, currency) {
    return `${currency} ${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
</script>

<template>
    <DesignPreviewLayout title="Offer Payment">
        <Head title="Offer Payment" />

        <div class="ofp-page">
            <div class="ofp-header">
                <h1 class="ofp-title">Offer Payment</h1>
                <p class="ofp-subtitle">Complete payment to secure this offer from the seller.</p>
            </div>

            <div class="ofp-grid">
                <!-- Offer summary -->
                <section class="ofp-card">
                    <h2 class="ofp-card__title"><el-icon><ShoppingCart /></el-icon> Offer Summary</h2>
                    <div class="ofp-spec">
                        <div class="ofp-spec__row"><span class="ofp-spec__label"><el-icon><Tickets /></el-icon> Offer</span><strong>{{ offer.offer_number || '—' }}</strong></div>
                        <div class="ofp-spec__row"><span class="ofp-spec__label"><el-icon><Coffee /></el-icon> Crop Type</span><strong>{{ offer.crop_type }}</strong></div>
                        <div class="ofp-spec__row"><span class="ofp-spec__label"><el-icon><Goods /></el-icon> Variety</span><strong>{{ offer.variety || '—' }}</strong></div>
                        <div class="ofp-spec__row"><span class="ofp-spec__label"><el-icon><Medal /></el-icon> Grade</span><strong>{{ offer.grade || '—' }}</strong></div>
                        <div class="ofp-spec__row"><span class="ofp-spec__label"><el-icon><Box /></el-icon> Quantity</span><strong>{{ Number(offer.quantity || 0).toLocaleString() }} kg</strong></div>
                        <div class="ofp-spec__row"><span class="ofp-spec__label"><el-icon><Coin /></el-icon> Unit Price</span><strong>{{ formatMoney(offer.unit_price, offer.currency) }}</strong></div>
                        <div class="ofp-spec__row"><span class="ofp-spec__label"><el-icon><User /></el-icon> Seller</span><strong>{{ offer.seller_name || '—' }}</strong></div>
                    </div>

                    <div v-if="response?.message" class="ofp-note">
                        <span class="ofp-note__label">Your response</span>
                        <p class="ofp-note__text">{{ response.message }}</p>
                    </div>
                </section>

                <!-- Payment -->
                <section class="ofp-card">
                    <h2 class="ofp-card__title"><el-icon><Wallet /></el-icon> Payment</h2>

                    <div class="ofp-total">
                        <span class="ofp-total__label">Total Amount</span>
                        <strong class="ofp-total__value"><el-icon><Coin /></el-icon> {{ formatMoney(offer.total_amount, offer.currency) }}</strong>
                    </div>

                    <div v-if="form.payment_method === 'Wallet'" class="ofp-wallet">
                        <span class="ofp-wallet__label"><el-icon><Wallet /></el-icon> Wallet Balance</span>
                        <strong class="ofp-wallet__value">{{ formatMoney(walletBalance, offer.currency) }}</strong>
                    </div>

                    <div v-if="form.errors.wallet" class="ofp-error">
                        <el-icon><WarningFilled /></el-icon> {{ form.errors.wallet }}
                    </div>

                    <form class="ofp-form" @submit.prevent="submitPayment">
                        <label class="ofp-field">
                            <span class="ofp-field__label"><el-icon><CreditCard /></el-icon> Payment Method</span>
                            <el-select v-model="form.payment_method" placeholder="Select payment method…" style="width: 100%" class="ofp-select">
                                <el-option v-for="m in paymentMethods" :key="m" :label="m" :value="m" />
                            </el-select>
                            <span v-if="form.errors.payment_method" class="ofp-field__error">{{ form.errors.payment_method }}</span>
                        </label>

                        <label class="ofp-field">
                            <span class="ofp-field__label"><el-icon><Document /></el-icon> Notes</span>
                            <el-input v-model="form.notes" type="textarea" :rows="3" placeholder="Optional payment notes…" class="ofp-input" />
                        </label>

                        <button type="submit" class="ofp-pay" :disabled="form.processing">
                            <el-icon :size="16"><Lock /></el-icon> {{ form.processing ? 'Processing…' : 'Pay Now' }}
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.ofp-page {
    --card-border: var(--dp-outline-variant, #E5E7EB);
    --surface: var(--dp-surface-container-lowest, #ffffff);
    --surface-muted: var(--dp-surface-container-low, #F5F6F7);
    --border: var(--dp-outline-variant, #E5E7EB);
    --primary: var(--dp-primary, #000000);
    --on-primary: var(--dp-on-primary, #ffffff);
    --text: var(--dp-on-surface, #121516);
    --text-2: var(--dp-on-surface-variant, #4B5457);
    --text-muted: var(--dp-outline, #6F7677);
    font-family: var(--dp-font-sans, 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
    color: var(--text);
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ofp-header { display: flex; flex-direction: column; gap: 4px; }
.ofp-title { font-size: 1.5rem; line-height: 1.9rem; letter-spacing: -0.015em; font-weight: 800; margin: 0; }
.ofp-subtitle { font-size: 0.9375rem; color: var(--text-muted); margin: 0; }

.ofp-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 16px; align-items: start; }

.ofp-card {
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow, none);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.ofp-card__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin: 0;
}

.ofp-spec { display: flex; flex-direction: column; }
.ofp-spec__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 0;
    border-top: 1px solid var(--border);
    font-size: 13px;
}
.ofp-spec__row:first-child { border-top: none; }
.ofp-spec__row span { color: var(--text-muted); }
.ofp-spec__label { display: inline-flex; align-items: center; gap: 6px; }
.ofp-spec__label .el-icon { font-size: 13px; color: var(--text-muted); }
.ofp-spec__row strong { color: var(--text); text-align: right; font-variant-numeric: tabular-nums; }

.ofp-note {
    background: var(--surface-muted);
    border-radius: 6px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.ofp-note__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.ofp-note__text { font-size: 13px; color: var(--text-2); line-height: 1.5; margin: 0; white-space: pre-wrap; }

.ofp-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: var(--surface-muted);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 14px 16px;
}
.ofp-total__label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.ofp-total__value { display: inline-flex; align-items: center; gap: 6px; font-size: 1.5rem; font-weight: 800; color: var(--text); letter-spacing: -0.01em; font-variant-numeric: tabular-nums; }
.ofp-total__value .el-icon { font-size: 18px; color: var(--text-muted); }

.ofp-wallet {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--surface);
}
.ofp-wallet__label { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--text-muted); }
.ofp-wallet__value { font-size: 14px; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; }

.ofp-error {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 6px;
    background: var(--dp-error-container, #FEEDED);
    color: var(--dp-on-error-container, #C6413A);
    font-size: 13px;
    font-weight: 600;
}
.ofp-error .el-icon { font-size: 15px; }

.ofp-form { display: flex; flex-direction: column; gap: 14px; }
.ofp-field { display: flex; flex-direction: column; gap: 6px; }
.ofp-field__label { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--text); }
.ofp-field__label .el-icon { font-size: 13px; color: var(--text-muted); }
.ofp-field__error { font-size: 12px; font-weight: 500; color: var(--dp-error, #F85149); }
.ofp-select :deep(.el-select__wrapper),
.ofp-input :deep(.el-input__wrapper),
.ofp-input :deep(.el-textarea__inner) { border-radius: 6px; }

.ofp-pay {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 44px;
    border: none;
    border-radius: 6px;
    background: var(--primary);
    color: var(--on-primary);
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 120ms ease;
}
.ofp-pay:hover:not(:disabled) { opacity: 0.88; }
.ofp-pay:disabled { opacity: 0.55; cursor: default; }

@media (max-width: 767.98px) {
    .ofp-grid { grid-template-columns: 1fr; }
    .ofp-title { font-size: 1.25rem; line-height: 1.6rem; }
}
</style>