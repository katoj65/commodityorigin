<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import { Delete, Minus, Plus, ShoppingCart, ShoppingTrolley, WarningFilled } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(value || 0);

const itemCount = computed(() => props.items.length);
const totalKg = computed(() => props.items.reduce((sum, item) => sum + item.quantity, 0));
const subtotal = computed(() => props.items.reduce((sum, item) => sum + item.line_total, 0));

function qualityTone(score) {
    if (score === null || score === undefined) return 'muted';
    if (score >= 85) return 'green';
    if (score >= 70) return 'amber';
    return 'red';
}

/* ── Row actions — each row tracks its own in-flight state ─────────────── */
const pendingId = ref(null);

function updateQuantity(item, quantity) {
    const next = Math.max(1, Math.min(item.available_quantity || quantity, Math.floor(quantity)));
    if (next === item.quantity || pendingId.value) return;

    pendingId.value = item.id;
    router.patch(route('checkout.items.update', item.id), { quantity: next }, {
        preserveScroll: true,
        onFinish: () => { pendingId.value = null; },
        onError: () => ElMessage.error('Could not update quantity.'),
    });
}

/* ── Remove — requires confirmation before the item leaves the cart ───── */
const removeOpen = ref(false);
const pendingRemove = ref(null);

function requestRemove(item) {
    if (pendingId.value) return;

    pendingRemove.value = item;
    removeOpen.value = true;
}

function confirmRemove() {
    const item = pendingRemove.value;
    if (!item) return;

    pendingId.value = item.id;
    router.delete(route('checkout.items.destroy', item.id), {
        preserveScroll: true,
        onSuccess: () => ElMessage.success(`Removed ${item.name || item.lot_code} from your cart.`),
        onFinish: () => { pendingId.value = null; },
        onError: () => ElMessage.error('Could not remove this item.'),
    });
}
</script>

<template>
    <AppLayout title="Cart" full-width flush :show-banner="false">
        <div class="cart-page">
            <section class="cart-header">
                <div class="cart-header__inner">
                    <div>
                        <div class="cart-kicker"><el-icon><ShoppingCart /></el-icon> Checkout</div>
                        <h1 class="cart-title">Your Cart</h1>
                        <p class="cart-subtitle" v-if="itemCount">
                            {{ itemCount }} {{ itemCount === 1 ? 'lot' : 'lots' }} · {{ totalKg.toLocaleString() }} kg total
                        </p>
                    </div>
                    <Link :href="route('market.index')" class="cart-btn cart-btn--outline">Continue Shopping</Link>
                </div>
            </section>

            <div class="cart-body">

                <!-- ── Empty state ──────────────────────────────────────── -->
                <div v-if="!itemCount" class="cart-empty">
                    <div class="cart-empty__icon"><el-icon><ShoppingTrolley /></el-icon></div>
                    <h2 class="cart-empty__title">Your cart is empty</h2>
                    <p class="cart-empty__text">Browse the marketplace and add lots you're interested in — they'll show up here.</p>
                    <Link :href="route('market.index')" class="cart-btn cart-btn--solid">Browse Listings</Link>
                </div>

                <!-- ── Cart ─────────────────────────────────────────────── -->
                <div v-else class="cart-grid">
                    <section class="cart-items">
                        <article v-for="item in items" :key="item.id" class="cart-item" :class="{ 'cart-item--pending': pendingId === item.id }">
                            <div class="cart-item__media">
                                <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.name">
                                <svg v-else class="cart-item__media-icon" viewBox="0 0 24 24">
                                    <ellipse cx="9" cy="12" rx="5" ry="7" transform="rotate(-25 9 12)" fill="#4b2e1d" />
                                    <ellipse cx="16.5" cy="14.5" rx="4" ry="5.5" transform="rotate(20 16.5 14.5)" fill="#6b4226" />
                                </svg>
                            </div>

                            <div class="cart-item__body">
                                <div class="cart-item__top">
                                    <div>
                                        <Link :href="route('market.show', item.market_id)" class="cart-item__title">{{ item.name || item.lot_code }}</Link>
                                        <p class="cart-item__subtitle">{{ item.origin || 'Origin unknown' }} <span>·</span> {{ item.process || '—' }}</p>
                                    </div>
                                    <span v-if="item.quality_score !== null" class="cart-badge" :class="`cart-badge--${qualityTone(item.quality_score)}`">
                                        {{ item.quality_score.toFixed(1) }}
                                    </span>
                                </div>

                                <p v-if="item.current_price !== item.unit_price" class="cart-item__price-note">
                                    <el-icon><WarningFilled /></el-icon>
                                    Price is now {{ formatCurrency(item.current_price) }}/kg — you locked in {{ formatCurrency(item.unit_price) }}/kg when added.
                                </p>

                                <div class="cart-item__footer">
                                    <div class="cart-qty" :class="{ 'cart-qty--disabled': pendingId === item.id }">
                                        <button type="button" class="cart-qty__btn" :disabled="pendingId === item.id || item.quantity <= 1" @click="updateQuantity(item, item.quantity - 1)">
                                            <el-icon><Minus /></el-icon>
                                        </button>
                                        <span class="cart-qty__value">{{ item.quantity }} kg</span>
                                        <button type="button" class="cart-qty__btn" :disabled="pendingId === item.id || item.quantity >= item.available_quantity" @click="updateQuantity(item, item.quantity + 1)">
                                            <el-icon><Plus /></el-icon>
                                        </button>
                                    </div>

                                    <div class="cart-item__price">
                                        <span class="cart-item__price-unit">{{ formatCurrency(item.unit_price) }} / kg</span>
                                        <span class="cart-item__price-total">{{ formatCurrency(item.line_total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="cart-item__remove" :disabled="pendingId === item.id" title="Remove from cart" @click="requestRemove(item)">
                                <el-icon><Delete /></el-icon>
                            </button>
                        </article>
                    </section>

                    <aside class="cart-summary">
                        <div class="cart-summary__card">
                            <h2 class="cart-summary__title">Order Summary</h2>

                            <div class="cart-summary__row">
                                <span>Subtotal ({{ itemCount }} {{ itemCount === 1 ? 'lot' : 'lots' }})</span>
                                <span>{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div class="cart-summary__row">
                                <span>Total weight</span>
                                <span>{{ totalKg.toLocaleString() }} kg</span>
                            </div>

                            <div class="cart-summary__divider" />

                            <div class="cart-summary__total">
                                <span>Total</span>
                                <span>{{ formatCurrency(subtotal) }}</span>
                            </div>

                            <Link :href="route('checkout.confirmation')" class="cart-btn cart-btn--solid cart-summary__checkout">
                                Proceed to Checkout
                            </Link>

                            <p class="cart-summary__note">Shipping and any applicable fees are arranged after checkout.</p>
                        </div>
                    </aside>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model="removeOpen"
            title="Remove Item"
            :message="`${pendingRemove?.name || pendingRemove?.lot_code || 'This item'} will be removed from your cart.`"
            confirm-text="Remove"
            @confirm="confirmRemove"
        />
    </AppLayout>
</template>

<style scoped>
.cart-page {
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

.cart-body { max-width: 1200px; margin: 0 auto; padding: 1.5rem 1.5rem 3rem; }

/* ── Header — edge-to-edge: flush with the top, left, and right of the
   page, white background, content aligned to the same max-width as the
   body below via .cart-header__inner. ──────────────────────────────── */
.cart-header { background: #fff; border-bottom: 1px solid var(--border); }
.cart-header__inner { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
.cart-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 4px; }
.cart-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
.cart-subtitle { font-size: .8125rem; color: var(--on-surface-var); margin: 4px 0 0; }

.cart-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 20px; font-size: .8125rem; font-weight: 700; letter-spacing: .01em; text-decoration: none; border-radius: 8px; cursor: pointer; white-space: nowrap; transition: background .15s ease, color .15s ease, transform .12s ease; }
.cart-btn--outline { color: var(--green); background: #fff; border: 1px solid var(--green); }
.cart-btn--outline:hover { background: #f0f5f3; }
.cart-btn--solid { color: #fff; background: var(--green); border: 1px solid var(--green); }
.cart-btn--solid:hover { background: var(--green-dark); transform: translateY(-1px); }

/* ── Empty state ─────────────────────────────────────────────────────── */
.cart-empty { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 4.5rem 1.5rem; border: 1px dashed var(--border); border-radius: 14px; }
.cart-empty__icon { width: 52px; height: 52px; border-radius: 50%; background: var(--surface-low); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--on-surface-var); margin-bottom: 1rem; }
.cart-empty__title { font-size: 1.0625rem; font-weight: 800; margin: 0 0 6px; }
.cart-empty__text { font-size: .8125rem; color: var(--on-surface-var); margin: 0 0 1.5rem; max-width: 340px; }

/* ── Layout ──────────────────────────────────────────────────────────── */
.cart-grid { display: grid; grid-template-columns: 1.7fr 1fr; gap: 1.5rem; align-items: start; }

/* ── Cart items ──────────────────────────────────────────────────────── */
.cart-items { display: flex; flex-direction: column; gap: 12px; }
.cart-item { position: relative; display: grid; grid-template-columns: 96px minmax(0, 1fr) auto; gap: 14px; align-items: stretch; border: 1px solid var(--border); border-radius: 12px; padding: 14px; background: #fff; transition: opacity .15s ease; }
.cart-item--pending { opacity: .6; }

.cart-item__media { width: 96px; height: 96px; border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f1e6d8; border: 1px solid #e6d5bf; flex-shrink: 0; }
.cart-item__media img { width: 100%; height: 100%; object-fit: cover; }
.cart-item__media-icon { width: 40px; height: 40px; }

.cart-item__body { min-width: 0; display: flex; flex-direction: column; }
.cart-item__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.cart-item__title { font-size: .9375rem; font-weight: 800; color: var(--on-surface); text-decoration: none; letter-spacing: -.01em; }
.cart-item__title:hover { color: var(--green); }
.cart-item__subtitle { font-size: .75rem; color: var(--on-surface-var); margin: 4px 0 0; }
.cart-item__subtitle span { margin: 0 4px; }

.cart-badge { flex-shrink: 0; display: inline-flex; align-items: center; border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 3px 9px; }
.cart-badge--green { background: #ecfdf5; color: #059669; }
.cart-badge--amber { background: #fffbeb; color: #d97706; }
.cart-badge--red { background: #fef2f2; color: #dc2626; }
.cart-badge--muted { background: #f5f5f4; color: #78716c; }

.cart-item__price-note { display: flex; align-items: center; gap: 5px; font-size: .6875rem; color: #b45309; background: #fffbeb; border-radius: 6px; padding: 5px 8px; margin: 8px 0 0; width: fit-content; }
.cart-item__price-note :deep(.el-icon) { font-size: 12px; flex-shrink: 0; }

.cart-item__footer { margin-top: auto; padding-top: 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }

.cart-qty { display: inline-flex; align-items: center; gap: 10px; border: 1px solid var(--border); border-radius: 8px; padding: 4px 6px; }
.cart-qty--disabled { opacity: .7; }
.cart-qty__btn { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 6px; border: none; background: var(--surface-low); color: var(--on-surface); cursor: pointer; transition: background .12s ease; }
.cart-qty__btn:hover:not(:disabled) { background: #eef2f1; }
.cart-qty__btn:disabled { opacity: .4; cursor: default; }
.cart-qty__value { min-width: 52px; text-align: center; font-family: 'IBM Plex Mono', ui-monospace, monospace; font-weight: 700; font-size: .8125rem; }

.cart-item__price { text-align: right; display: flex; flex-direction: column; }
.cart-item__price-unit { font-size: .6875rem; color: var(--on-surface-var); }
.cart-item__price-total { font-family: 'IBM Plex Mono', ui-monospace, monospace; font-size: 1.0625rem; font-weight: 800; color: var(--green); }

.cart-item__remove { align-self: flex-start; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 8px; border: none; background: transparent; color: var(--on-surface-var); cursor: pointer; transition: background .12s ease, color .12s ease; }
.cart-item__remove:hover:not(:disabled) { background: #fef2f2; color: #dc2626; }
.cart-item__remove:disabled { opacity: .4; cursor: default; }

/* ── Summary ─────────────────────────────────────────────────────────── */
.cart-summary__card { border: 1px solid var(--border); border-radius: 14px; padding: 1.5rem; position: sticky; top: 5.5rem; background: #fff; box-shadow: 0 1px 2px rgba(17, 24, 39, .04), 0 12px 28px -16px rgba(17, 24, 39, .16); }
.cart-summary__title { font-size: .9375rem; font-weight: 800; margin: 0 0 1rem; }
.cart-summary__row { display: flex; align-items: center; justify-content: space-between; font-size: .8125rem; color: var(--on-surface-var); padding: 5px 0; font-variant-numeric: tabular-nums; }
.cart-summary__divider { border-top: 1px solid var(--border); margin: 12px 0; }
.cart-summary__total { display: flex; align-items: center; justify-content: space-between; font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); }
.cart-summary__total span:last-child { font-family: 'IBM Plex Mono', ui-monospace, monospace; color: var(--green); font-size: 1.25rem; }
.cart-summary__checkout { width: 100%; margin-top: 1.25rem; }
.cart-summary__note { font-size: .6875rem; color: var(--on-surface-var); text-align: center; margin: 10px 0 0; }

@media (max-width: 991.98px) {
    .cart-grid { grid-template-columns: 1fr; }
    .cart-summary__card { position: static; }
}

@media (max-width: 640px) {
    .cart-header__inner { padding: 1.25rem; }
    .cart-body { padding: 1.25rem 1.25rem 2.5rem; }
    .cart-item { grid-template-columns: 72px minmax(0, 1fr); }
    .cart-item__media { width: 72px; height: 72px; }
    .cart-item__remove { position: absolute; top: 10px; right: 10px; }
}
</style>
