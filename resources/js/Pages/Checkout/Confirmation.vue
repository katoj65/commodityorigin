<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { HomeFilled, Lock, Headset, Box } from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import PaymentMethodSection from '@/Components/PaymentMethodSection.vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
    walletBalance: { type: Number, default: 0 },
    deliveryDefaults: { type: Object, default: () => ({}) },
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(value || 0);

const itemCount = computed(() => props.items.length);
const subtotal = computed(() => props.items.reduce((sum, item) => sum + item.line_total, 0));
const walletCoversTotal = computed(() => props.walletBalance >= subtotal.value);

const form = useForm({
    payment_method: 'wallet',
    delivery: {
        full_name: props.deliveryDefaults.full_name || '',
        phone: props.deliveryDefaults.phone || '',
        address_line1: props.deliveryDefaults.address_line1 || '',
        address_line2: props.deliveryDefaults.address_line2 || '',
        city: props.deliveryDefaults.city || '',
        state: props.deliveryDefaults.state || '',
        country: props.deliveryDefaults.country || '',
        postal_code: props.deliveryDefaults.postal_code || '',
        delivery_notes: '',
    },
    card: null,
});

const paymentMethodSection = ref(null);

/* ── Billing address — collapsed to a one-line summary once the required
   fields are filled (matching a compact "review" card), expandable back
   into the real, required delivery form. ─────────────────────────────── */
const hasDeliveryDefaults = computed(() => Boolean(
    form.delivery.address_line1 && form.delivery.city && form.delivery.country,
));
const editingBilling = ref(!hasDeliveryDefaults.value);

const billingSummary = computed(() => [
    form.delivery.address_line1,
    form.delivery.address_line2,
    form.delivery.city,
    form.delivery.state,
    form.delivery.country,
    form.delivery.postal_code,
].filter(Boolean).join(', '));

const billingComplete = computed(() => Boolean(
    form.delivery.full_name && form.delivery.phone
        && form.delivery.address_line1 && form.delivery.city
        && form.delivery.country && form.delivery.postal_code,
));

function doneEditingBilling() {
    if (billingComplete.value) editingBilling.value = false;
}

const canSubmit = computed(() => {
    if (!itemCount.value || !billingComplete.value) return false;
    if (form.payment_method === 'wallet') return walletCoversTotal.value;
    return paymentMethodSection.value?.isCardValid() ?? false;
});

function placeOrder() {
    if (!billingComplete.value) {
        editingBilling.value = true;
        return;
    }

    if (form.payment_method === 'card') {
        paymentMethodSection.value.touchCard();
        if (!paymentMethodSection.value.isCardValid()) return;

        form.card = paymentMethodSection.value.buildCardPayload();
    } else {
        form.card = null;
    }

    form.post(route('checkout.placeOrder'), { preserveScroll: true });
}
</script>

<template>
    <DesignPreviewLayout title="Checkout">
        <div class="chk-page">
            <section class="chk-header">
                <div class="chk-header__inner">
                    <h1 class="chk-title">Complete Checkout</h1>
                    <p class="chk-subtitle">Confirm your delivery details and payment method to place the order.</p>
                </div>
            </section>

            <div class="chk-body">
                <form class="chk-grid" @submit.prevent="placeOrder">
                    <div class="chk-main">
                        <!-- ── Payment method (+ Pay button in its footer) ── -->
                        <PaymentMethodSection
                            ref="paymentMethodSection"
                            v-model="form.payment_method"
                            :wallet-balance="walletBalance"
                            :wallet-covers-total="walletCoversTotal"
                        >
                            <template #billing>
                                <!-- ── Billing address — compact summary, expands
                                     into the real required delivery form. ──── -->
                                <section class="chk-billing">
                                    <div class="chk-billing__icon"><el-icon :size="20"><HomeFilled /></el-icon></div>

                                    <div v-if="!editingBilling" class="chk-billing__summary">
                                        <p class="chk-billing__label">Billing Address</p>
                                        <p class="chk-billing__text">{{ form.delivery.full_name }} — {{ billingSummary }}</p>
                                    </div>
                                    <div v-else class="chk-billing__form">
                                        <p class="chk-billing__label">Billing Address</p>
                                        <div class="chk-field-grid">
                                            <label class="chk-field">
                                                <span>Full name</span>
                                                <input v-model="form.delivery.full_name" type="text" required>
                                                <small v-if="form.errors['delivery.full_name']" class="chk-error">{{ form.errors['delivery.full_name'] }}</small>
                                            </label>
                                            <label class="chk-field">
                                                <span>Phone</span>
                                                <input v-model="form.delivery.phone" type="tel" required>
                                                <small v-if="form.errors['delivery.phone']" class="chk-error">{{ form.errors['delivery.phone'] }}</small>
                                            </label>
                                            <label class="chk-field chk-field--full">
                                                <span>Address line 1</span>
                                                <input v-model="form.delivery.address_line1" type="text" required>
                                                <small v-if="form.errors['delivery.address_line1']" class="chk-error">{{ form.errors['delivery.address_line1'] }}</small>
                                            </label>
                                            <label class="chk-field chk-field--full">
                                                <span>Address line 2 <em>(optional)</em></span>
                                                <input v-model="form.delivery.address_line2" type="text">
                                            </label>
                                            <label class="chk-field">
                                                <span>City</span>
                                                <input v-model="form.delivery.city" type="text" required>
                                                <small v-if="form.errors['delivery.city']" class="chk-error">{{ form.errors['delivery.city'] }}</small>
                                            </label>
                                            <label class="chk-field">
                                                <span>State / Region <em>(optional)</em></span>
                                                <input v-model="form.delivery.state" type="text">
                                            </label>
                                            <label class="chk-field">
                                                <span>Country</span>
                                                <input v-model="form.delivery.country" type="text" required>
                                                <small v-if="form.errors['delivery.country']" class="chk-error">{{ form.errors['delivery.country'] }}</small>
                                            </label>
                                            <label class="chk-field">
                                                <span>Postal code</span>
                                                <input v-model="form.delivery.postal_code" type="text" required>
                                                <small v-if="form.errors['delivery.postal_code']" class="chk-error">{{ form.errors['delivery.postal_code'] }}</small>
                                            </label>
                                            <label class="chk-field chk-field--full">
                                                <span>Delivery notes <em>(optional)</em></span>
                                                <textarea v-model="form.delivery.delivery_notes" rows="2" placeholder="Gate code, landmark, preferred delivery time…" />
                                            </label>
                                        </div>
                                    </div>

                                    <button type="button" class="chk-billing__edit" @click="editingBilling ? doneEditingBilling() : (editingBilling = true)">
                                        {{ editingBilling ? 'Done' : 'Edit' }}
                                    </button>
                                </section>
                            </template>

                            <template #footer>
                                <button type="submit" class="chk-pay-btn" :disabled="form.processing || !canSubmit">
                                    <el-icon v-if="!form.processing"><Lock /></el-icon>
                                    {{ form.processing ? 'Processing…' : `Pay ${formatCurrency(subtotal)}` }}
                                </button>
                            </template>
                        </PaymentMethodSection>
                    </div>

                    <!-- ── Order summary ─────────────────────────────────── -->
                    <aside class="chk-summary">
                        <div class="chk-summary__card">
                            <h2 class="chk-summary__title">Order Summary</h2>

                            <ul class="chk-summary__items">
                                <li v-for="item in items" :key="item.id" class="chk-summary__item">
                                    <div class="chk-summary__thumb">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name">
                                        <el-icon v-else :size="18"><Box /></el-icon>
                                    </div>
                                    <div class="chk-summary__item-body">
                                        <h4 class="chk-summary__item-name">{{ item.name || item.lot_code }}</h4>
                                        <p v-if="item.subtitle" class="chk-summary__item-sub">{{ item.subtitle }}</p>
                                        <div class="chk-summary__item-row">
                                            <span>Qty: {{ item.quantity }}{{ item.unit }}</span>
                                            <span>{{ formatCurrency(item.line_total) }}</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>

                            <div class="chk-summary__divider" />

                            <div class="chk-summary__totals">
                                <div class="chk-summary__row">
                                    <span>Subtotal</span>
                                    <span>{{ formatCurrency(subtotal) }}</span>
                                </div>
                            </div>

                            <div class="chk-summary__divider" />

                            <div class="chk-summary__total">
                                <div>
                                    <span class="chk-summary__total-label">Total</span>
                                </div>
                                <span class="chk-summary__total-amount">{{ formatCurrency(subtotal) }}</span>
                            </div>
                        </div>

                        <div class="chk-support">
                            <el-icon :size="15"><Headset /></el-icon>
                            <span>Need help? <Link :href="route('checkout.index')">Back to cart</Link></span>
                        </div>
                    </aside>
                </form>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.chk-page {
    /* UI.md theme (2026-08-24): app-wide default. See
       reference_ui_md_design_system memory for the full spec. */
    --primary: #000000;
    --on-primary: #ffffff;
    --border: #E5E7EB;
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --surface: #ffffff;
    --on-surface: #121516;
    --on-surface-var: #4B5457;
    --on-surface-muted: #6F7677;
    --surface-low: #F5F6F7;
    --surface-container: #F1F2F3;
    --error: #B91C1C;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    color: var(--on-surface);
    min-height: 100%;
}

/* No negative-margin/gutter override here — .dp-main's own 48px/64px
   padding is left in place so this page's edges line up with the Cart
   page's, which relies on the same padding without any override. */
.chk-body { max-width: 1400px; margin: 25px auto 0; }

/* ── Header ──────────────────────────────────────────────────────────── */
.chk-header__inner { max-width: 1400px; margin: 0 auto; }
.chk-title { font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--on-surface); margin: 0 0 6px; }
.chk-subtitle { font-size: 13.5px; color: var(--on-surface-var); margin: 0; }

.chk-grid { display: grid; grid-template-columns: 1.7fr 1fr; gap: 1.5rem; align-items: start; }
.chk-main { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }

/* ── Pay button (rendered into PaymentMethodSection's footer slot) ────── */
.chk-pay-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; margin-top: 1.5rem; height: 48px; border: none; border-radius: 6px;
    background: var(--primary); color: var(--on-primary); font-size: 14px; font-weight: 600;
    cursor: pointer; transition: opacity 120ms ease;
}
.chk-pay-btn:hover:not(:disabled) { opacity: 0.88; }
.chk-pay-btn:disabled { opacity: .5; cursor: default; }

/* ── Billing address (compact summary + expandable form) ──────────────── */
.chk-billing { display: flex; align-items: flex-start; gap: 16px; }
.chk-billing__icon {
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    width: 40px; height: 40px; border-radius: 8px; background: var(--surface-container); color: var(--on-surface-var);
}
.chk-billing__summary, .chk-billing__form { flex: 1; min-width: 0; }
.chk-billing__label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); margin: 0 0 4px; }
.chk-billing__text { font-size: .9375rem; font-weight: 500; color: var(--on-surface); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.chk-billing__edit { flex-shrink: 0; border: none; background: none; color: var(--primary); font-size: .8125rem; font-weight: 700; text-decoration: underline; cursor: pointer; padding: 0; }

.chk-field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-top: 1rem; }
.chk-field { display: flex; flex-direction: column; gap: 5px; font-size: .75rem; font-weight: 700; color: var(--on-surface-var); }
.chk-field span em { font-weight: 500; font-style: normal; text-transform: none; color: var(--on-surface-var); opacity: .8; }
.chk-field--full { grid-column: 1 / -1; }
.chk-field input,
.chk-field textarea {
    border: 1px solid var(--border); border-radius: 6px; padding: 9px 11px;
    font-family: inherit;
    font-size: .8125rem; font-weight: 500; color: var(--on-surface);
    background: var(--surface); resize: vertical;
}
.chk-field input:focus,
.chk-field textarea:focus { outline: none; border-color: var(--primary); }
.chk-error { color: var(--error); font-weight: 600; }

/* ── Summary ─────────────────────────────────────────────────────────── */
.chk-summary { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 5.5rem; }
.chk-summary__card { border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 20px; background: var(--surface); }
.chk-summary__title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface); margin: 0 0 16px; }
.chk-summary__items { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; max-height: 320px; overflow-y: auto; }
.chk-summary__item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--card-border); }
.chk-summary__item:last-child { border-bottom: none; padding-bottom: 0; }
.chk-summary__item:first-child { padding-top: 0; }
.chk-summary__thumb {
    width: 44px; height: 44px; border-radius: 6px; flex-shrink: 0; overflow: hidden;
    background: var(--surface-low); color: var(--on-surface-var);
    display: flex; align-items: center; justify-content: center;
}
.chk-summary__thumb img { width: 100%; height: 100%; object-fit: cover; }
.chk-summary__item-body { min-width: 0; flex: 1; }
.chk-summary__item-name { margin: 0; font-size: .8125rem; font-weight: 700; color: var(--on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.chk-summary__item-sub { margin: 3px 0 0; font-size: .6875rem; color: var(--on-surface-var); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.chk-summary__item-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 6px; font-size: .75rem; font-variant-numeric: tabular-nums; }
.chk-summary__item-row span:last-child { font-weight: 700; color: var(--on-surface); }

.chk-summary__divider { border-top: 1px solid var(--card-border); margin: 16px 0; }
.chk-summary__totals { display: flex; flex-direction: column; gap: 10px; }
.chk-summary__row { display: flex; align-items: center; justify-content: space-between; font-size: .875rem; color: var(--on-surface-var); }
.chk-summary__total { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; }
.chk-summary__total-label { display: block; font-size: 15px; font-weight: 700; color: var(--on-surface); }
.chk-summary__total-amount { font-family: ui-monospace, monospace; font-size: 26px; font-weight: 800; color: var(--primary); letter-spacing: -.01em; line-height: 1; }

.chk-support { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: .75rem; color: var(--on-surface-var); }
.chk-support :deep(a) { color: var(--primary); text-decoration: underline; }

@media (max-width: 991.98px) {
    .chk-grid { grid-template-columns: 1fr; }
    .chk-summary { position: static; }
}

@media (max-width: 640px) {
    .chk-title { font-size: 1.125rem; }
    .chk-field-grid { grid-template-columns: 1fr; }
    .chk-billing { flex-direction: column; }
}
</style>
