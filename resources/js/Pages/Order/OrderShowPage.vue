<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    ArrowLeft, ShoppingCart, Box, Delete, Close, CircleCheck, CircleClose,
    Loading, Van, Check, Files, Message, Phone, Odometer, Coffee, User,
    Coin, Calendar, Timer, Flag, ChatLineSquare, RefreshRight, Search,
} from '@element-plus/icons-vue';

const props = defineProps({
    order: { type: Object, required: true },
    authUserId: { type: Number, default: null },
    isAdmin: { type: Boolean, default: false },
});

/* ── Perspective ─────────────────────────────────────────────────────── */
const buyerIsMe = computed(() => props.order.buyer_id === props.authUserId);
const sellerIsMe = computed(() => props.order.seller_id === props.authUserId);
const amParty = computed(() => buyerIsMe.value || sellerIsMe.value);
// The party who originally posted the order: the buyer on a "request",
// the seller on an "offer".
const amCreator = computed(() => (props.order.type === 'offer' ? sellerIsMe.value : buyerIsMe.value));

/* ── Display helpers ─────────────────────────────────────────────────── */
const typeLabel = computed(() => (props.order.type === 'offer' ? 'Offer' : 'Request'));
const typeTone = computed(() => (props.order.type === 'offer' ? 'osh-badge--blue' : 'osh-badge--muted'));

function statusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function statusTone(status) {
    return {
        open: 'osh-badge--muted',
        pending: 'osh-badge--amber',
        confirmed: 'osh-badge--blue',
        inspection: 'osh-badge--amber',
        processing: 'osh-badge--blue',
        shipped: 'osh-badge--blue',
        delivered: 'osh-badge--green',
        cancelled: 'osh-badge--red',
        withdrawn: 'osh-badge--muted',
    }[status] ?? 'osh-badge--muted';
}

function formatMoney(amount, currency) {
    return `${currency} ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(dateTime) {
    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
    });
}

/* ── Status pipeline ─────────────────────────────────────────────────── */
const steps = [
    {
        key: 'open',
        label: 'Open',
        icon: Box,
        description: () => (props.order.type === 'offer'
            ? 'Waiting for buyers to express interest.'
            : 'Waiting for sellers to express interest.'),
    },
    {
        key: 'pending', label: 'Pending', icon: Timer, description: () => `Waiting on the creator to confirm a ${props.order.type === 'offer' ? 'buyer' : 'seller'}.`,
    },
    {
        key: 'confirmed', label: 'Confirmed', icon: Flag, description: () => 'Waiting on the seller to request an inspection.',
    },
    {
        key: 'inspection',
        label: 'Inspection',
        icon: Search,
        description: () => (props.order.inspection?.buyer_acknowledged_at
            ? "Buyer acknowledged — waiting on admin to confirm it's complete."
            : 'Waiting on the buyer to acknowledge the inspection.'),
    },
    {
        key: 'processing', label: 'Processing', icon: Loading, description: () => 'Coffee is being prepared for shipment.',
    },
    {
        key: 'shipped', label: 'Shipped', icon: Van, description: () => 'Order has shipped and is in transit.',
    },
    {
        key: 'delivered', label: 'Delivered', icon: CircleCheck, description: () => 'Order has been delivered to the buyer.',
    },
];

const isTerminalAlt = computed(() => ['cancelled', 'withdrawn'].includes(props.order.status));
const currentStepIndex = computed(() => steps.findIndex((s) => s.key === props.order.status));

function stepState(idx) {
    if (idx < currentStepIndex.value) return 'done';
    if (idx === currentStepIndex.value) return 'current';
    return 'upcoming';
}

function stepCaption(step, idx) {
    return stepState(idx) === 'done' ? 'Completed' : step.description();
}

/* ── Actions ─────────────────────────────────────────────────────────── */
const sellerFlow = {
    shipped: { label: 'Mark Delivered', next: 'delivered', icon: CircleCheck },
};

const sellerNextStep = computed(() => sellerFlow[props.order.status] ?? null);

/* ── Inspection ──────────────────────────────────────────────────────── */
const inspection = computed(() => props.order.inspection ?? null);
const buyerAcknowledgedInspection = computed(() => !!inspection.value?.buyer_acknowledged_at);

function requestInspection() {
    router.post(route('orders.inspections.store', props.order.id), {}, { preserveScroll: true });
}

function acknowledgeInspection() {
    if (!inspection.value) return;

    router.post(route('orders.inspections.acknowledge', [props.order.id, inspection.value.id]), {}, { preserveScroll: true });
}

function completeInspection() {
    if (!inspection.value) return;

    router.post(route('orders.inspections.complete', [props.order.id, inspection.value.id]), {}, { preserveScroll: true });
}

/* ── Interest / confirmation ──────────────────────────────────────────── */
const counterpartyLabel = computed(() => (props.order.type === 'offer' ? 'Buyer' : 'Seller'));
const intents = computed(() => props.order.intents ?? []);
const isAcceptingIntent = computed(() => ['open', 'pending'].includes(props.order.status));
const selectedIntent = ref(null);
const intentReviewOpen = ref(false);

function openIntentReview(intent) {
    selectedIntent.value = intent;
    intentReviewOpen.value = true;
}

function confirmSelectedIntent() {
    if (!selectedIntent.value) return;

    router.post(route('orders.intents.confirm', [props.order.id, selectedIntent.value.id]), {}, {
        preserveScroll: true,
        onSuccess: () => { intentReviewOpen.value = false; },
    });
}

function expressInterest() {
    router.post(route('orders.intents.store', props.order.id), {}, { preserveScroll: true });
}

const withdrawIntentOpen = ref(false);

function requestWithdrawIntent() {
    withdrawIntentOpen.value = true;
}

function confirmWithdrawIntent() {
    router.delete(route('orders.intents.destroy', [props.order.id, props.order.my_intent_id]), { preserveScroll: true });
}

function updateStatus(status) {
    router.patch(route('orders.update', props.order.id), { status }, { preserveScroll: true });
}

const confirmCancelOpen = ref(false);
const withdrawOpen = ref(false);

function requestCancel() {
    confirmCancelOpen.value = true;
}

function requestWithdraw() {
    withdrawOpen.value = true;
}

function confirmCancel() {
    updateStatus('cancelled');
}

function confirmWithdraw() {
    router.post(route('orders.withdraw', props.order.id), {}, { preserveScroll: true });
}

function repostOrder() {
    router.post(route('orders.repost', props.order.id), {}, { preserveScroll: true });
}

const primaryAction = computed(() => {
    const o = props.order;

    if (isAcceptingIntent.value) {
        if (amCreator.value || o.my_intent_id) return null;
        return { label: 'Express Interest', icon: ShoppingCart, handler: expressInterest };
    }

    if (o.status === 'cancelled' && amCreator.value) {
        return {
            label: `Repost as New ${typeLabel.value}`, icon: RefreshRight, handler: repostOrder,
        };
    }

    if (o.status === 'confirmed' && sellerIsMe.value) {
        return { label: 'Request Inspection', icon: Search, handler: requestInspection };
    }

    if (o.status === 'inspection') {
        if (buyerIsMe.value && !buyerAcknowledgedInspection.value) {
            return { label: 'Acknowledge Inspection', icon: CircleCheck, handler: acknowledgeInspection };
        }
        if (props.isAdmin && buyerAcknowledgedInspection.value) {
            return { label: 'Confirm Inspection Complete', icon: CircleCheck, handler: completeInspection };
        }
    }

    if (o.status === 'processing' && props.isAdmin) {
        return { label: 'Activate Shipping', icon: Van, handler: () => updateStatus('shipped') };
    }

    if (sellerIsMe.value && sellerNextStep.value) {
        return {
            label: sellerNextStep.value.label,
            icon: sellerNextStep.value.icon,
            handler: () => updateStatus(sellerNextStep.value.next),
        };
    }

    return null;
});

const secondaryAction = computed(() => {
    const o = props.order;

    if (isAcceptingIntent.value) {
        if (amCreator.value) {
            return { label: `Withdraw ${typeLabel.value}`, icon: Delete, handler: requestWithdraw };
        }
        if (o.my_intent_id) {
            return { label: 'Withdraw Interest', icon: Delete, handler: requestWithdrawIntent };
        }
        return null;
    }

    if (buyerIsMe.value && o.status === 'confirmed') {
        return { label: 'Cancel Order', icon: Close, handler: requestCancel };
    }

    return null;
});

const noActionLockBadge = computed(() => {
    if (primaryAction.value || secondaryAction.value) return null;

    if (props.order.status === 'inspection') return { label: 'Under Inspection', icon: Search };
    if (props.order.status === 'processing') return { label: 'Processing', icon: Van };

    return null;
});

const noActionsMessage = computed(() => {
    if (primaryAction.value || secondaryAction.value || noActionLockBadge.value) return null;

    return amParty.value
        ? 'No actions available for this order right now.'
        : "You're viewing this publicly. No actions available.";
});
</script>

<template>
    <AppLayout :title="`Order ${order.order_number}`" full-width flush :show-banner="false">
        <Head :title="`Order ${order.order_number}`" />

        <div class="osh-page">
            <div class="osh-topbar">
                <Link :href="route('orders.index')" class="osh-back">
                    <el-icon :size="14"><ArrowLeft /></el-icon> Orders
                </Link>
            </div>

            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="osh-header pb-3">
                <div class="osh-header__main">
                    <div class="osh-badges">
                        <span class="osh-badge" :class="typeTone">{{ typeLabel }}</span>
                        <span class="osh-badge" :class="statusTone(order.status)">{{ statusLabel(order.status) }}</span>
                    </div>
                    <h1 class="osh-order-number">{{ order.order_number }}</h1>
                    <p class="osh-subtitle">
                        {{ order.crop_type }}<template v-if="order.variety"> · {{ order.variety }}</template>
                        · Placed {{ formatDate(order.created_at) }}
                    </p>
                </div>

                <div class="osh-header__actions">
                    <button
                        v-if="secondaryAction"
                        type="button"
                        class="osh-btn osh-btn--outline osh-btn--danger"
                        @click="secondaryAction.handler"
                    >
                        <el-icon :size="14"><component :is="secondaryAction.icon" /></el-icon> {{ secondaryAction.label }}
                    </button>
                    <button
                        v-if="primaryAction"
                        type="button"
                        class="osh-btn osh-btn--primary"
                        @click="primaryAction.handler"
                    >
                        <el-icon :size="14"><component :is="primaryAction.icon" /></el-icon> {{ primaryAction.label }}
                    </button>
                    <button
                        v-if="noActionLockBadge"
                        type="button"
                        class="osh-btn osh-btn--outline"
                        disabled
                    >
                        <el-icon :size="14"><component :is="noActionLockBadge.icon" /></el-icon> {{ noActionLockBadge.label }}
                    </button>
                    <span v-if="noActionsMessage" class="osh-muted">{{ noActionsMessage }}</span>
                </div>
            </div>

            <div class="osh-divider" />

            <!-- ── Order progress ────────────────────────────────────────── -->
            <section class="osh-segment">
                <div class="osh-segment__head">
                    <h2 class="osh-segment__title"><el-icon :size="15"><Odometer /></el-icon> Order Progress</h2>
                    <p class="osh-segment__subtitle">Follow this order from posting through delivery.</p>
                </div>

                <div v-if="isTerminalAlt" class="osh-alert" :class="order.status === 'cancelled' ? 'osh-alert--red' : 'osh-alert--muted'">
                    <el-icon :size="16"><component :is="order.status === 'cancelled' ? CircleClose : Delete" /></el-icon>
                    <span>This order was {{ order.status }} on {{ formatDate(order.updated_at) }}.</span>
                </div>

                <div v-else class="osh-stepper">
                    <div
                        v-for="(step, idx) in steps"
                        :key="step.key"
                        class="osh-step"
                        :class="`osh-step--${stepState(idx)}`"
                    >
                        <div class="osh-step__indicator">
                            <div class="osh-step__dot">
                                <el-icon v-if="stepState(idx) === 'done'" :size="12"><Check /></el-icon>
                                <el-icon v-else :size="12"><component :is="step.icon" /></el-icon>
                            </div>
                        </div>
                        <div class="osh-step__body">
                            <span class="osh-step__label">{{ step.label }}</span>
                            <span class="osh-step__desc">{{ stepCaption(step, idx) }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <template v-if="amCreator && intents.length">
                <div class="osh-divider" />

                <!-- ── Interested parties ───────────────────────────────── -->
                <section class="osh-segment">
                    <div class="osh-segment__head">
                        <h2 class="osh-segment__title"><el-icon :size="15"><User /></el-icon> Interested {{ counterpartyLabel }}s</h2>
                        <p class="osh-segment__subtitle">Review each party's profile and trade history, then confirm who fulfills this order.</p>
                    </div>

                    <div class="osh-intent-list">
                        <div v-for="intent in intents" :key="intent.id" class="osh-intent-row">
                            <div class="osh-intent-row__identity">
                                <span class="osh-party__avatar"><el-icon :size="14"><User /></el-icon></span>
                                <div>
                                    <div class="osh-intent-row__name">{{ intent.user_name }}</div>
                                    <div class="osh-intent-row__role">{{ intent.profile.role || 'Trader' }}</div>
                                </div>
                            </div>
                            <div class="osh-intent-row__stats">
                                <span>{{ intent.history.completed }} completed</span>
                                <span v-if="intent.history.cancelled">{{ intent.history.cancelled }} cancelled</span>
                            </div>
                            <button type="button" class="osh-btn osh-btn--outline" @click="openIntentReview(intent)">
                                <el-icon :size="13"><CircleCheck /></el-icon> Review &amp; Confirm
                            </button>
                        </div>
                    </div>
                </section>
            </template>

            <div class="osh-divider" />

            <!-- ── Coffee details + parties (side by side) ──────────────────── -->
            <section class="osh-row">
                <div class="osh-col">
                    <div class="osh-segment__head">
                        <h2 class="osh-segment__title"><el-icon :size="15"><Coffee /></el-icon> Coffee Details</h2>
                        <p class="osh-segment__subtitle">Crop specification, quantity, and pricing breakdown.</p>
                    </div>

                    <dl class="osh-dl">
                        <div class="osh-dl__row">
                            <dt>Crop</dt>
                            <dd>{{ order.crop_type }}</dd>
                        </div>
                        <div v-if="order.variety" class="osh-dl__row">
                            <dt>Variety</dt>
                            <dd>{{ order.variety }}</dd>
                        </div>
                        <div v-if="order.grade" class="osh-dl__row">
                            <dt>Grade</dt>
                            <dd>{{ order.grade }}</dd>
                        </div>
                        <div class="osh-dl__row">
                            <dt>Quantity</dt>
                            <dd>{{ order.quantity.toLocaleString() }} kg</dd>
                        </div>
                        <div class="osh-dl__row">
                            <dt>Unit Price</dt>
                            <dd>{{ formatMoney(order.unit_price, order.currency) }} / kg</dd>
                        </div>
                        <div class="osh-dl__row osh-dl__row--total">
                            <dt><el-icon :size="13"><Coin /></el-icon> Total Amount</dt>
                            <dd>{{ formatMoney(order.total_amount, order.currency) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="osh-row__divider" />

                <div class="osh-col">
                    <div class="osh-segment__head">
                        <h2 class="osh-segment__title"><el-icon :size="15"><User /></el-icon> Buyer &amp; Seller</h2>
                        <p class="osh-segment__subtitle">Contact details for both parties in this trade.</p>
                    </div>

                    <div class="osh-parties">
                        <div class="osh-party">
                            <span class="osh-party__role">Buyer</span>
                            <span v-if="order.buyer_name" class="osh-party__name">
                                <span class="osh-party__avatar"><el-icon :size="12"><User /></el-icon></span>
                                {{ order.buyer_name }}
                                <span v-if="buyerIsMe" class="osh-you-tag">You</span>
                            </span>
                            <span v-else class="osh-party__name osh-muted">Awaiting a buyer</span>

                            <div v-if="order.buyer_email" class="osh-contact">
                                <span class="osh-contact__row"><el-icon :size="12"><Message /></el-icon> {{ order.buyer_email }}</span>
                                <span v-if="order.buyer_phone" class="osh-contact__row"><el-icon :size="12"><Phone /></el-icon> {{ order.buyer_phone }}</span>
                            </div>
                        </div>

                        <div class="osh-party">
                            <span class="osh-party__role">Seller</span>
                            <span v-if="order.seller_name" class="osh-party__name">
                                <span class="osh-party__avatar"><el-icon :size="12"><User /></el-icon></span>
                                {{ order.seller_name }}
                                <span v-if="sellerIsMe" class="osh-you-tag">You</span>
                            </span>
                            <span v-else class="osh-party__name osh-muted">Awaiting a seller</span>

                            <div v-if="order.seller_email" class="osh-contact">
                                <span class="osh-contact__row"><el-icon :size="12"><Message /></el-icon> {{ order.seller_email }}</span>
                                <span v-if="order.seller_phone" class="osh-contact__row"><el-icon :size="12"><Phone /></el-icon> {{ order.seller_phone }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="osh-meta">
                        <div class="osh-meta__item">
                            <span class="osh-meta__label"><el-icon :size="11"><Calendar /></el-icon> Created</span>
                            <span class="osh-meta__value">{{ formatDate(order.created_at) }}</span>
                        </div>
                        <div class="osh-meta__item">
                            <span class="osh-meta__label"><el-icon :size="11"><Timer /></el-icon> Last updated</span>
                            <span class="osh-meta__value">{{ formatDate(order.updated_at) }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <template v-if="order.notes">
                <div class="osh-divider" />

                <!-- ── Notes ─────────────────────────────────────────────── -->
                <section class="osh-segment">
                    <div class="osh-segment__head">
                        <h2 class="osh-segment__title"><el-icon :size="15"><Files /></el-icon> Notes</h2>
                        <p class="osh-segment__subtitle">Additional instructions from the order's creator.</p>
                    </div>

                    <div class="osh-notes-card">
                        <el-icon class="osh-notes-card__icon" :size="34"><ChatLineSquare /></el-icon>
                        <p class="osh-notes-card__text">{{ order.notes }}</p>
                    </div>
                </section>
            </template>
        </div>

        <ConfirmDialog
            v-model="confirmCancelOpen"
            title="Cancel Order"
            :message="`Cancel ${order.order_number}? This can't be undone.`"
            confirm-text="Cancel Order"
            :icon="'warning'"
            @confirm="confirmCancel"
        />

        <ConfirmDialog
            v-model="withdrawOpen"
            title="Withdraw Order"
            :message="`Withdraw ${order.order_number}? This can't be undone.`"
            confirm-text="Withdraw"
            @confirm="confirmWithdraw"
        />

        <ConfirmDialog
            v-model="withdrawIntentOpen"
            title="Withdraw Interest"
            :message="`Withdraw your interest in ${order.order_number}? You can express interest again later if it's still open.`"
            confirm-text="Withdraw"
            @confirm="confirmWithdrawIntent"
        />

        <!-- ── Interested-party review / confirm modal ──────────────────── -->
        <el-dialog
            v-model="intentReviewOpen"
            width="520px"
            destroy-on-close
            align-center
            :show-close="true"
            class="osh-review-modal"
        >
            <template #header>
                <div class="osh-review-head">
                    <div class="osh-review-head__icon"><el-icon :size="18"><User /></el-icon></div>
                    <div>
                        <div class="osh-review-eyebrow">Confirm This {{ counterpartyLabel }}</div>
                        <div class="osh-review-title">{{ selectedIntent?.user_name }}</div>
                    </div>
                </div>
            </template>

            <div v-if="selectedIntent" class="osh-review-body">
                <div class="osh-review-section">
                    <h4 class="osh-review-section__title">Profile</h4>
                    <div class="osh-review-row">
                        <span class="osh-review-row__label">Role</span>
                        <span class="osh-review-row__value">{{ selectedIntent.profile.role || '—' }}</span>
                    </div>
                    <div class="osh-review-row">
                        <span class="osh-review-row__label">Verified</span>
                        <span
                            class="osh-review-verified"
                            :class="{ 'osh-review-verified--yes': selectedIntent.profile.email_verified }"
                        >
                            {{ selectedIntent.profile.email_verified ? 'Verified' : 'Unverified' }}
                        </span>
                    </div>
                    <div class="osh-review-row">
                        <span class="osh-review-row__label">Member Since</span>
                        <span class="osh-review-row__value">{{ selectedIntent.profile.member_since || '—' }}</span>
                    </div>
                </div>

                <div class="osh-review-section">
                    <h4 class="osh-review-section__title">Company Information</h4>
                    <p class="osh-review-bio">{{ selectedIntent.profile.bio || 'No company information provided.' }}</p>
                    <div v-if="selectedIntent.profile.location" class="osh-review-row">
                        <span class="osh-review-row__label">Location</span>
                        <span class="osh-review-row__value">{{ selectedIntent.profile.location }}</span>
                    </div>
                </div>

                <div class="osh-review-section">
                    <h4 class="osh-review-section__title">Trade History</h4>
                    <div class="osh-review-stats">
                        <div class="osh-review-stat">
                            <strong>{{ selectedIntent.history.completed }}</strong>
                            <span>Completed</span>
                        </div>
                        <div class="osh-review-stat">
                            <strong>{{ selectedIntent.history.active }}</strong>
                            <span>Active</span>
                        </div>
                        <div class="osh-review-stat">
                            <strong>{{ selectedIntent.history.cancelled }}</strong>
                            <span>Cancelled</span>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="osh-review-footer">
                    <button type="button" class="osh-review-btn osh-review-btn--outline" @click="intentReviewOpen = false">Cancel</button>
                    <button type="button" class="osh-review-btn osh-review-btn--primary" @click="confirmSelectedIntent">
                        <el-icon :size="14"><CircleCheck /></el-icon> Confirm {{ counterpartyLabel }}
                    </button>
                </div>
            </template>
        </el-dialog>
    </AppLayout>
</template>

<style scoped>
.osh-page {
    --green: #004532;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
    padding-bottom: 4rem;
}

/* ── Topbar ──────────────────────────────────────────────────────────── */
.osh-topbar {
    padding: 1.5rem 1.5rem 0;
}

.osh-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface-var);
    text-decoration: none;
    transition: color 0.12s;
}

.osh-back:hover {
    color: var(--on-surface);
}

/* ── Header ──────────────────────────────────────────────────────────── */
.osh-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.25rem;
    padding: 1.5rem 1.5rem 0;
}

.osh-header__main {
    max-width: 640px;
}

.osh-badges {
    display: flex;
    gap: 6px;
    margin-bottom: 10px;
}

.osh-order-number {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    margin: 0 0 6px;
}

.osh-subtitle {
    font-size: 0.8437rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

.osh-header__actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    padding-top: 8px;
}

.osh-muted {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.osh-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s ease, background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.osh-btn:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.osh-btn--outline:disabled:hover { background: #fff; }

.osh-btn--primary {
    background: linear-gradient(135deg, #004532, #065f46);
    color: #fff;
}

.osh-btn--primary:hover { opacity: 0.9; }

.osh-btn--outline {
    background: #fff;
    border-color: var(--border);
    color: var(--on-surface);
}

.osh-btn--outline:hover { background: var(--surface-low); }

.osh-btn--danger:hover { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }

/* ── Badges ──────────────────────────────────────────────────────────── */
.osh-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 700;
    padding: 4px 10px;
    white-space: nowrap;
}

.osh-badge--green { background: #dcfce7; color: #166534; }
.osh-badge--amber { background: #fef3c7; color: #92400e; }
.osh-badge--red { background: #fee2e2; color: #991b1b; }
.osh-badge--blue { background: #dbeafe; color: #1e40af; }
.osh-badge--muted { background: #f3f4f6; color: #6b7280; }

/* ── Alert (terminal state) ─────────────────────────────────────────── */
.osh-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-radius: 10px;
    font-size: 0.8437rem;
    font-weight: 600;
}

.osh-alert--red { background: #fef2f2; color: #991b1b; }
.osh-alert--muted { background: var(--surface-low); color: var(--on-surface-var); }

/* ── Stepper ─────────────────────────────────────────────────────────── */
.osh-stepper {
    display: flex;
    align-items: flex-start;
}

.osh-step {
    position: relative;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    padding-right: 1.75rem;
}

.osh-step:last-child {
    flex: 0 1 auto;
    padding-right: 0;
}

.osh-step__indicator {
    position: relative;
    width: 100%;
    margin-bottom: 12px;
}

.osh-step:not(:last-child) .osh-step__indicator::after {
    content: '';
    position: absolute;
    top: 13px;
    left: 26px;
    right: 0;
    height: 2px;
    background: var(--border);
    z-index: 0;
}

.osh-step--done:not(:last-child) .osh-step__indicator::after {
    background: var(--green);
}

.osh-step__dot {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6875rem;
    font-weight: 800;
    background: #fff;
    border: 2px solid var(--border);
    color: var(--on-surface-var);
    position: relative;
    z-index: 1;
    flex-shrink: 0;
}

.osh-step--done .osh-step__dot {
    background: var(--green);
    border-color: var(--green);
    color: #fff;
}

.osh-step--current .osh-step__dot {
    border-color: var(--green);
    color: var(--green);
    box-shadow: 0 0 0 4px rgba(0, 69, 50, 0.12);
}

.osh-step__body {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 200px;
}

.osh-step__label {
    font-size: 0.8125rem;
    font-weight: 800;
    color: var(--on-surface-var);
    letter-spacing: -0.005em;
}

.osh-step--done .osh-step__label,
.osh-step--current .osh-step__label {
    color: var(--on-surface);
}

.osh-step__desc {
    font-size: 0.75rem;
    line-height: 1.45;
    color: var(--on-surface-var);
}

.osh-step--done .osh-step__desc {
    color: var(--green);
    font-weight: 700;
}

.osh-step--current .osh-step__desc {
    color: var(--on-surface-var);
}

/* ── Horizontal segments ─────────────────────────────────────────────── */
.osh-divider {
    height: 1px;
    width: 100%;
    background: var(--border);
}

.osh-segment {
    padding: 2.25rem 1.5rem;
}

.osh-segment__head {
    max-width: 640px;
    margin-bottom: 1.5rem;
}

.osh-segment__title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 1.0625rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: var(--on-surface);
    margin: 0 0 4px;
}

.osh-segment__subtitle {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    line-height: 1.55;
    margin: 0;
}

.osh-dl {
    margin: 0;
}

.osh-dl__row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    padding: 13px 0;
    border-bottom: 1px solid var(--surface-low);
    font-size: 0.875rem;
}

.osh-dl__row dt {
    color: var(--on-surface-var);
    font-weight: 600;
}

.osh-dl__row dd {
    margin: 0;
    font-weight: 700;
    color: var(--on-surface);
    text-align: right;
}

.osh-dl__row--total {
    border-bottom: none;
    padding-top: 18px;
}

.osh-dl__row--total dt {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.osh-dl__row--total dd {
    font-size: 1.0625rem;
    font-weight: 800;
    color: var(--green);
}

.osh-notes-card {
    position: relative;
    width: 100%;
    background: var(--surface-low);
    border-left: 3px solid var(--green);
    border-radius: 10px;
    padding: 1.1rem 3rem 1.1rem 1.25rem;
    overflow: hidden;
}

.osh-notes-card__icon {
    position: absolute;
    top: 10px;
    right: 12px;
    color: rgba(0, 69, 50, 0.1);
}

.osh-notes-card__text {
    position: relative;
    font-size: 0.875rem;
    color: var(--on-surface);
    line-height: 1.65;
    margin: 0;
    white-space: pre-line;
}

/* ── Interested parties ────────────────────────────────────────────────── */
.osh-intent-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.osh-intent-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 10px;
}

.osh-intent-row__identity {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.osh-intent-row__name {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--on-surface);
}

.osh-intent-row__role {
    font-size: 0.75rem;
    color: var(--on-surface-var);
}

.osh-intent-row__stats {
    display: flex;
    gap: 12px;
    font-size: 0.75rem;
    color: var(--on-surface-var);
    flex-shrink: 0;
}

/* ── Coffee details / buyer & seller row ─────────────────────────────── */
.osh-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 1px minmax(0, 1fr);
    gap: 2.5rem;
    padding: 2.25rem 1.5rem;
}

.osh-row__divider {
    background: var(--border);
    align-self: stretch;
}

.osh-col {
    min-width: 0;
}

/* ── Buyer & seller ──────────────────────────────────────────────────── */
.osh-parties {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.osh-party {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.osh-party + .osh-party {
    border-top: 1px solid var(--border);
    padding-top: 1.5rem;
}

.osh-party__role {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #9ca3af;
}

.osh-party__name {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--on-surface);
}

.osh-party__avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--surface-low);
    color: var(--on-surface-var);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.osh-you-tag {
    font-size: 0.5625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--green);
    background: rgba(0, 69, 50, 0.1);
    border-radius: 999px;
    padding: 1px 6px;
}

.osh-contact {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 6px;
}

.osh-contact__row {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: var(--on-surface-var);
}

.osh-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 2.5rem;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--surface-low);
}

.osh-meta__item {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.osh-meta__label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #9ca3af;
}

.osh-meta__value {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface);
}

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .osh-row {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .osh-row__divider {
        display: none;
    }

    .osh-stepper {
        flex-direction: column;
        gap: 1.1rem;
    }

    .osh-step {
        flex-direction: row;
        align-items: flex-start;
        gap: 12px;
        padding-right: 0;
    }

    .osh-step__indicator {
        width: auto;
        margin-bottom: 0;
    }

    .osh-step:not(:last-child) .osh-step__indicator::after {
        content: '';
        position: absolute;
        top: 26px;
        left: 13px;
        right: auto;
        width: 2px;
        height: calc(100% + 1.1rem - 26px);
        background: var(--border);
    }

    .osh-step--done:not(:last-child) .osh-step__indicator::after {
        background: var(--green);
    }

    .osh-step__body {
        max-width: none;
        padding-top: 3px;
    }
}

@media (max-width: 767.98px) {
    .osh-topbar { padding: 1rem 1.25rem 0; }
    .osh-header { padding: 0.75rem 1.25rem 0; }
    .osh-segment { padding: 2rem 1.25rem; }
}

/* ── Interested-party review modal ─────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .osh-page's
   DOM subtree, so CSS custom properties defined on .osh-page do NOT
   cascade in. All colors below are literal hex values on purpose. */
:deep(.el-dialog.osh-review-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.osh-review-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.osh-review-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.osh-review-modal .el-dialog__footer) { padding: 0; }

.osh-review-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.osh-review-head__icon {
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

.osh-review-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.osh-review-title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.osh-review-body {
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.osh-review-section__title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin: 0 0 10px;
}

.osh-review-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 0;
    font-size: 0.8125rem;
    border-bottom: 1px solid #f3f4f6;
}

.osh-review-row:last-child { border-bottom: none; }

.osh-review-row__label { color: #6b7280; font-weight: 600; }
.osh-review-row__value { color: #111827; font-weight: 600; }

.osh-review-verified {
    display: inline-flex;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 0.6875rem;
    font-weight: 700;
    background: #f3f4f6;
    color: #6b7280;
}

.osh-review-verified--yes { background: #dcfce7; color: #166534; }

.osh-review-bio {
    font-size: 0.8125rem;
    color: #374151;
    line-height: 1.6;
    margin: 0 0 10px;
}

.osh-review-stats {
    display: flex;
    gap: 10px;
}

.osh-review-stat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 12px 8px;
    border-radius: 10px;
    background: #f9fafb;
}

.osh-review-stat strong {
    font-size: 1.125rem;
    font-weight: 800;
    color: #111827;
}

.osh-review-stat span {
    font-size: 0.6875rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.osh-review-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.osh-review-btn {
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

.osh-review-btn--outline {
    background: #fff;
    border-color: #e5e7eb;
    color: #111827;
}

.osh-review-btn--outline:hover { background: #f9fafb; }

.osh-review-btn--primary {
    background: linear-gradient(135deg, #004532, #065f46);
    color: #fff;
}

.osh-review-btn--primary:hover { opacity: 0.9; }
</style>
