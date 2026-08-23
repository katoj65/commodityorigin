<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import { ArrowRight, Delete, Lock, Minus, Plus, ShoppingCart, ShoppingTrolley, WarningFilled } from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(value || 0);

const itemCount = computed(() => props.items.length);
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
    <DesignPreviewLayout title="Cart">
        <div class="cart-page">
            <div class="cart-header">
                <div>
                    <div class="cart-kicker"><el-icon><ShoppingCart /></el-icon> Checkout</div>
                    <h1 class="cart-title">Your Cart</h1>
                    <p class="cart-subtitle" v-if="itemCount">
                        {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }} ready for checkout.
                    </p>
                </div>
                <Link :href="route('market.index')" class="cart-btn cart-btn--outline">Continue Shopping</Link>
            </div>

            <!-- ── Empty state ──────────────────────────────────────────── -->
            <div v-if="!itemCount" class="cart-empty">
                <div class="cart-empty__icon"><el-icon><ShoppingTrolley /></el-icon></div>
                <h2 class="cart-empty__title">Your cart is empty</h2>
                <p class="cart-empty__text">Browse the marketplace or the input store and add items you're interested in — they'll show up here.</p>
                <Link :href="route('market.index')" class="cart-btn cart-btn--solid">Browse Listings</Link>
            </div>

            <!-- ── Cart ───────────────────────────────────────────────────── -->
            <div v-else class="cart-grid">
                <section class="cart-items">
                    <article
                        v-for="item in items"
                        :key="item.id"
                        class="cart-item"
                        :class="{ 'cart-item--pending': pendingId === item.id, 'cart-item--unavailable': item.available_quantity <= 0 }"
                    >
                        <div class="cart-item__media">
                            <img v-if="item.image_url" :src="item.image_url" :alt="item.name">
                            <svg v-else class="cart-item__media-icon" viewBox="0 0 24 24">
                                <ellipse cx="9" cy="12" rx="5" ry="7" transform="rotate(-25 9 12)" fill="#4b2e1d" />
                                <ellipse cx="16.5" cy="14.5" rx="4" ry="5.5" transform="rotate(20 16.5 14.5)" fill="#6b4226" />
                            </svg>
                        </div>

                        <div class="cart-item__body">
                            <div class="cart-item__top">
                                <div>
                                    <Link v-if="item.detail_route" :href="route(item.detail_route.name, item.detail_route.params)" class="cart-item__title">{{ item.name || item.lot_code }}</Link>
                                    <span v-else class="cart-item__title cart-item__title--static">{{ item.name || item.lot_code }}</span>
                                    <p class="cart-item__subtitle">{{ item.subtitle }}</p>
                                    <span v-if="item.available_quantity <= 0" class="cart-badge cart-badge--out"><el-icon><WarningFilled /></el-icon> Out of Stock</span>
                                    <span v-else-if="item.quality_score !== null" class="cart-badge" :class="`cart-badge--${qualityTone(item.quality_score)}`">
                                        {{ item.quality_score.toFixed(1) }}
                                    </span>
                                </div>

                                <button type="button" class="cart-item__remove" :disabled="pendingId === item.id" title="Remove from cart" @click="requestRemove(item)">
                                    <el-icon><Delete /></el-icon>
                                </button>
                            </div>

                            <p v-if="item.current_price !== null && item.current_price !== item.unit_price" class="cart-item__price-note">
                                <el-icon><WarningFilled /></el-icon>
                                Price is now {{ formatCurrency(item.current_price) }}/{{ item.unit }} — you locked in {{ formatCurrency(item.unit_price) }}/{{ item.unit }} when added.
                            </p>

                            <div v-if="item.available_quantity > 0" class="cart-item__footer">
                                <div class="cart-qty" :class="{ 'cart-qty--disabled': pendingId === item.id }">
                                    <button type="button" class="cart-qty__btn" :disabled="pendingId === item.id || item.quantity <= 1" @click="updateQuantity(item, item.quantity - 1)">
                                        <el-icon><Minus /></el-icon>
                                    </button>
                                    <span class="cart-qty__value">{{ item.quantity }} {{ item.unit }}</span>
                                    <button type="button" class="cart-qty__btn" :disabled="pendingId === item.id || item.quantity >= item.available_quantity" @click="updateQuantity(item, item.quantity + 1)">
                                        <el-icon><Plus /></el-icon>
                                    </button>
                                </div>

                                <div class="cart-item__price">
                                    <span class="cart-item__price-unit">{{ formatCurrency(item.unit_price) }} / {{ item.unit }}</span>
                                    <span class="cart-item__price-total">{{ formatCurrency(item.line_total) }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>

                <aside class="cart-summary">
                    <div class="cart-summary__card">
                        <h2 class="cart-summary__title">Summary</h2>

                        <div class="cart-summary__row">
                            <span>Subtotal ({{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }})</span>
                            <span>{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div class="cart-summary__row">
                            <span>Shipping</span>
                            <span>Calculated at checkout</span>
                        </div>

                        <div class="cart-summary__divider" />

                        <div class="cart-summary__total">
                            <span>Total</span>
                            <span>{{ formatCurrency(subtotal) }}</span>
                        </div>

                        <Link :href="route('checkout.confirmation')" class="cart-btn cart-btn--solid cart-summary__checkout">
                            Proceed to Checkout <el-icon><ArrowRight /></el-icon>
                        </Link>

                        <p class="cart-summary__note"><el-icon><Lock /></el-icon> Secure Checkout</p>
                    </div>
                </aside>
            </div>
        </div>

        <ConfirmDialog
            v-model="removeOpen"
            title="Remove Item"
            :message="`${pendingRemove?.name || pendingRemove?.lot_code || 'This item'} will be removed from your cart.`"
            confirm-text="Remove"
            @confirm="confirmRemove"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
.cart-page { font-family: var(--dp-font-sans); color: var(--dp-on-surface); }

/* ── Header ──────────────────────────────────────────────────────────── */
.cart-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 32px; }
.cart-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--dp-secondary); margin-bottom: 8px; }
.cart-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.015em; color: var(--dp-on-surface); margin: 0; }
.cart-subtitle { font-size: .9375rem; color: var(--dp-on-surface-variant); margin: 6px 0 0; }

.cart-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 11px 22px; font-size: .8125rem; font-weight: 700; letter-spacing: .01em; text-decoration: none; border-radius: 8px; cursor: pointer; white-space: nowrap; transition: background .15s ease, color .15s ease, transform .12s ease; }
.cart-btn--outline { color: var(--dp-primary); background: var(--dp-surface-container-lowest); box-shadow: var(--dp-card-shadow); }
.cart-btn--outline:hover { background: var(--dp-surface-container-low); }
.cart-btn--solid { color: var(--dp-on-primary); background: var(--dp-primary); }
.cart-btn--solid:hover { background: var(--dp-primary); opacity: .92; transform: translateY(-1px); }

/* ── Empty state ─────────────────────────────────────────────────────── */
.cart-empty {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    padding: 4.5rem 1.5rem; background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius); box-shadow: var(--dp-card-shadow);
}
.cart-empty__icon { width: 52px; height: 52px; border-radius: 50%; background: var(--dp-surface-container-low); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--dp-on-surface-variant); margin-bottom: 1rem; }
.cart-empty__title { font-size: .9375rem; font-weight: 800; margin: 0 0 6px; }
.cart-empty__text { font-size: .8125rem; color: var(--dp-on-surface-variant); margin: 0 0 1.5rem; max-width: 340px; }

/* ── Layout ──────────────────────────────────────────────────────────── */
.cart-grid { display: grid; grid-template-columns: 1.7fr 1fr; gap: 24px; align-items: start; }

/* ── Cart items — cards, not bordered rows: separation comes from
   background/shadow, matching the rest of the app's dp-* card system. */
.cart-items { display: flex; flex-direction: column; gap: 10px; }
.cart-item {
    position: relative; display: grid; grid-template-columns: 80px minmax(0, 1fr); gap: 14px;
    border-radius: 14px; padding: 14px; background: var(--dp-surface-container-lowest);
    box-shadow: var(--dp-card-shadow); transition: box-shadow .15s ease, transform .15s ease, opacity .15s ease;
}
.cart-item:hover { transform: translateY(-1px); box-shadow: 0 1px 2px rgba(39, 19, 16, .04), 0 14px 28px -14px rgba(39, 19, 16, .18); }
.cart-item--pending { opacity: .6; }
.cart-item--unavailable { background: var(--dp-surface-container-low); }
.cart-item--unavailable .cart-item__media { opacity: .6; }

.cart-item__media { width: 80px; height: 80px; border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--dp-surface-container-high); flex-shrink: 0; }
.cart-item__media img { width: 100%; height: 100%; object-fit: cover; }
.cart-item__media-icon { width: 30px; height: 30px; }

.cart-item__body { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.cart-item__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.cart-item__title { font-size: .8125rem; font-weight: 800; color: var(--dp-on-surface); text-decoration: none; letter-spacing: -.01em; }
.cart-item__title:hover { color: var(--dp-primary); }
.cart-item__title--static { display: inline-block; cursor: default; }
.cart-item__title--static:hover { color: var(--dp-on-surface); }
.cart-item__subtitle { font-size: .6875rem; text-transform: uppercase; letter-spacing: .04em; font-weight: 700; color: var(--dp-outline); margin: 3px 0 0; }
.cart-item__subtitle span { margin: 0 4px; }

.cart-badge { margin-top: 6px; flex-shrink: 0; display: inline-flex; align-items: center; gap: 4px; border-radius: 999px; font-size: .625rem; font-weight: 700; padding: 3px 8px; width: fit-content; }
.cart-badge--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.cart-badge--amber { background: #fffbeb; color: #d97706; }
.cart-badge--red { background: var(--dp-error-container); color: var(--dp-on-error-container); }
.cart-badge--muted { background: var(--dp-surface-container); color: var(--dp-outline); }
.cart-badge--out { background: var(--dp-error-container); color: var(--dp-on-error-container); }

.cart-item__price-note { display: flex; align-items: center; gap: 5px; font-size: .6875rem; color: #b45309; background: #fffbeb; border-radius: 6px; padding: 6px 9px; margin: 6px 0 0; width: fit-content; }
.cart-item__price-note :deep(.el-icon) { font-size: 12px; flex-shrink: 0; }

.cart-item__footer { margin-top: auto; padding-top: 10px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }

.cart-qty { display: inline-flex; align-items: center; gap: 8px; background: var(--dp-surface-container-low); border-radius: 7px; padding: 3px 5px; }
.cart-qty--disabled { opacity: .7; }
.cart-qty__btn { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 5px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); cursor: pointer; transition: background .12s ease; }
.cart-qty__btn:hover:not(:disabled) { background: var(--dp-surface-container-high); }
.cart-qty__btn:disabled { opacity: .4; cursor: default; }
.cart-qty__value { min-width: 48px; text-align: center; font-family: var(--dp-font-mono); font-weight: 700; font-size: .75rem; }

.cart-item__price { text-align: right; display: flex; flex-direction: column; }
.cart-item__price-unit { font-size: .625rem; color: var(--dp-on-surface-variant); }
.cart-item__price-total { font-family: var(--dp-font-mono); font-size: .9375rem; font-weight: 800; color: var(--dp-primary); }

.cart-item__remove { align-self: flex-start; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 7px; border: none; background: transparent; color: var(--dp-on-surface-variant); cursor: pointer; transition: background .12s ease, color .12s ease; }
.cart-item__remove:hover:not(:disabled) { background: var(--dp-error-container); color: var(--dp-error); }
.cart-item__remove:disabled { opacity: .4; cursor: default; }

/* ── Summary ─────────────────────────────────────────────────────────── */
.cart-summary__card { border-radius: var(--dp-card-radius); padding: 24px; position: sticky; top: 96px; background: var(--dp-surface-container-lowest); box-shadow: var(--dp-card-shadow); }
.cart-summary__title { font-size: 1.0625rem; font-weight: 800; color: var(--dp-on-surface); margin: 0 0 16px; }
.cart-summary__row { display: flex; align-items: center; justify-content: space-between; font-size: .875rem; color: var(--dp-on-surface-variant); padding: 6px 0; font-variant-numeric: tabular-nums; }
.cart-summary__row span:last-child { color: var(--dp-on-surface); font-weight: 600; }
.cart-summary__divider { border-top: 1px solid var(--dp-surface-container); margin: 14px 0; }
.cart-summary__total { display: flex; align-items: center; justify-content: space-between; font-size: .9375rem; font-weight: 700; color: var(--dp-on-surface); }
.cart-summary__total span:last-child { font-family: var(--dp-font-mono); color: var(--dp-primary); font-size: 1.375rem; font-weight: 800; }
.cart-summary__checkout { width: 100%; margin-top: 20px; box-shadow: 0 6px 16px -8px rgba(39, 19, 16, .45); }
.cart-summary__note { display: flex; align-items: center; justify-content: center; gap: 5px; font-size: .75rem; color: var(--dp-on-surface-variant); text-align: center; margin: 14px 0 0; }

@media (max-width: 991.98px) {
    .cart-grid { grid-template-columns: 1fr; }
    .cart-summary__card { position: static; }
}

@media (max-width: 640px) {
    .cart-item { grid-template-columns: 64px minmax(0, 1fr); padding: 12px; gap: 10px; }
    .cart-item__media { width: 64px; height: 64px; }
    .cart-item__remove { position: absolute; top: 10px; right: 10px; }
}
</style>
