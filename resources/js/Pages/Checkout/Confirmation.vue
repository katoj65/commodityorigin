<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Van, Lock, CircleCheck } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
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

const canSubmit = computed(() => {
    if (!itemCount.value) return false;
    if (form.payment_method === 'wallet') return walletCoversTotal.value;
    return paymentMethodSection.value?.isCardValid() ?? false;
});

function placeOrder() {
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
    <AppLayout title="Checkout" full-width flush :show-banner="false">
        <div class="chk-page">
            <div class="chk-body">
                <div class="chk-header">
                    <div class="chk-kicker"><el-icon><Lock /></el-icon> Secure Checkout</div>
                    <h1 class="chk-title">Checkout</h1>
                </div>

                <form class="chk-grid" @submit.prevent="placeOrder">
                    <div class="chk-main">
                        <!-- ── Delivery details ─────────────────────────── -->
                        <section class="chk-card">
                            <h2 class="chk-card__title"><el-icon><Van /></el-icon> Delivery Details</h2>

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
                        </section>

                        <!-- ── Payment method ───────────────────────────── -->
                        <PaymentMethodSection
                            ref="paymentMethodSection"
                            v-model="form.payment_method"
                            :wallet-balance="walletBalance"
                            :wallet-covers-total="walletCoversTotal"
                        />
                    </div>

                    <!-- ── Order summary ─────────────────────────────────── -->
                    <aside class="chk-summary">
                        <div class="chk-summary__card">
                            <h2 class="chk-summary__title">Order Summary</h2>

                            <ul class="chk-summary__items">
                                <li v-for="item in items" :key="item.id" class="chk-summary__item">
                                    <span class="chk-summary__item-name">{{ item.name || item.lot_code }} <em>× {{ item.quantity }}kg</em></span>
                                    <span>{{ formatCurrency(item.line_total) }}</span>
                                </li>
                            </ul>

                            <div class="chk-summary__divider" />

                            <div class="chk-summary__total">
                                <span>Total</span>
                                <span>{{ formatCurrency(subtotal) }}</span>
                            </div>

                            <button type="submit" class="chk-btn chk-btn--solid chk-summary__submit" :disabled="form.processing || !canSubmit">
                                <el-icon v-if="!form.processing"><CircleCheck /></el-icon>
                                {{ form.processing ? 'Placing Order…' : `Place Order · ${formatCurrency(subtotal)}` }}
                            </button>

                            <Link :href="route('checkout.index')" class="chk-summary__back">← Back to Cart</Link>
                        </div>
                    </aside>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.chk-page {
    --green: #004532;
    --green-dark: #002e20;
    --gold: #c8862a;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

.chk-body { max-width: 1100px; margin: 0 auto; padding: 1.5rem 1.5rem 3rem; }

.chk-header { margin-bottom: 1.5rem; }
.chk-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 4px; }
.chk-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }

.chk-grid { display: grid; grid-template-columns: 1.7fr 1fr; gap: 1.5rem; align-items: start; }
.chk-main { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }

.chk-card { border: 1px solid var(--border); border-radius: 14px; padding: 1.5rem; }
.chk-card__title { display: flex; align-items: center; gap: 8px; font-size: .9375rem; font-weight: 800; margin: 0 0 1.25rem; }
.chk-card__title :deep(.el-icon) { color: var(--green); font-size: 16px; }

.chk-field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
.chk-field { display: flex; flex-direction: column; gap: 5px; font-size: .75rem; font-weight: 700; color: var(--on-surface-var); }
.chk-field span em { font-weight: 500; font-style: normal; text-transform: none; color: var(--on-surface-var); opacity: .8; }
.chk-field--full { grid-column: 1 / -1; }
.chk-field input,
.chk-field textarea {
    border: 1px solid var(--border); border-radius: 8px; padding: 9px 11px;
    font-family: 'Manrope', system-ui, sans-serif; font-size: .8125rem; font-weight: 500; color: var(--on-surface);
    background: #fff; resize: vertical;
}
.chk-field input:focus,
.chk-field textarea:focus { outline: none; border-color: var(--green); }
.chk-error { color: #dc2626; font-weight: 600; }

/* ── Summary ─────────────────────────────────────────────────────────── */
.chk-summary__card { border: 1px solid var(--border); border-radius: 14px; padding: 1.5rem; position: sticky; top: 5.5rem; background: #fff; box-shadow: 0 1px 2px rgba(17, 24, 39, .04), 0 12px 28px -16px rgba(17, 24, 39, .16); }
.chk-summary__title { font-size: .9375rem; font-weight: 800; margin: 0 0 1rem; }
.chk-summary__items { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; max-height: 220px; overflow-y: auto; }
.chk-summary__item { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; font-size: .8125rem; font-variant-numeric: tabular-nums; }
.chk-summary__item-name { min-width: 0; color: var(--on-surface); }
.chk-summary__item-name em { font-style: normal; color: var(--on-surface-var); }
.chk-summary__divider { border-top: 1px solid var(--border); margin: 12px 0; }
.chk-summary__total { display: flex; align-items: center; justify-content: space-between; font-size: 1.0625rem; font-weight: 800; }
.chk-summary__total span:last-child { font-family: 'IBM Plex Mono', ui-monospace, monospace; color: var(--green); font-size: 1.25rem; }
.chk-summary__submit { width: 100%; margin-top: 1.25rem; }
.chk-summary__back { display: block; text-align: center; margin-top: 10px; font-size: .75rem; color: var(--on-surface-var); text-decoration: none; }
.chk-summary__back:hover { color: var(--green); }

.chk-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 11px 20px; font-size: .8125rem; font-weight: 700; border-radius: 8px; cursor: pointer; border: none; transition: background .15s ease, transform .12s ease; }
.chk-btn--solid { color: #fff; background: var(--green); }
.chk-btn--solid:hover:not(:disabled) { background: var(--green-dark); transform: translateY(-1px); }
.chk-btn--solid:disabled { opacity: .5; cursor: default; }

@media (max-width: 991.98px) {
    .chk-grid { grid-template-columns: 1fr; }
    .chk-summary__card { position: static; }
}

@media (max-width: 640px) {
    .chk-body { padding: 1.25rem 1.25rem 2.5rem; }
    .chk-field-grid { grid-template-columns: 1fr; }
}
</style>
