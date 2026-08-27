<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    ArrowLeft, ShoppingCart, Box, Delete, Close, CircleCheck, CircleClose,
    Loading, Van, Check, Files, Message, Phone, Odometer, Coffee, User,
    Coin, Calendar, Timer, Flag, ChatLineSquare, RefreshRight, Search,
    CopyDocument, WarningFilled,
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
const typeTone = computed(() => (props.order.type === 'offer' ? 'blue' : 'muted'));

function statusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

/* Returns a tone KEY (not a class name) — used to build the badge class
   (`osh-badge--{tone}`). */
function statusTone(status) {
    return {
        open: 'muted',
        pending: 'amber',
        confirmed: 'blue',
        inspection: 'amber',
        processing: 'blue',
        shipped: 'blue',
        delivered: 'green',
        cancelled: 'red',
        withdrawn: 'muted',
    }[status] ?? 'muted';
}

function formatMoney(amount, currency) {
    return `${currency} ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(dateTime) {
    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
    });
}

/* ── Identity — deterministic initials + palette colour per name, so
   buyer/seller/interested-party rows read as distinct people rather than
   identical grey circles. ─────────────────────────────────────────────── */
const avatarPalette = [
    { bg: '#eef2ff', color: '#4338ca' },
    { bg: '#ecfdf5', color: '#047857' },
    { bg: '#fff7ed', color: '#c2410c' },
    { bg: '#fdf4ff', color: '#a21caf' },
    { bg: '#eff6ff', color: '#1d4ed8' },
    { bg: '#f0fdfa', color: '#0f766e' },
];

function initials(name) {
    const parts = (name || '').trim().split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || '?';
}

function avatarStyle(name) {
    const str = name || '';
    let hash = 0;
    for (let i = 0; i < str.length; i += 1) hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
    const swatch = avatarPalette[hash % avatarPalette.length];
    return { background: swatch.bg, color: swatch.color };
}

/* ── Copy order number ────────────────────────────────────────────────── */
const orderNumberCopied = ref(false);
let copyResetTimer = null;

function copyOrderNumber() {
    if (!navigator.clipboard) return;

    navigator.clipboard.writeText(props.order.order_number).then(() => {
        orderNumberCopied.value = true;
        clearTimeout(copyResetTimer);
        copyResetTimer = setTimeout(() => { orderNumberCopied.value = false; }, 1500);
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
    <DesignPreviewLayout :title="`Order ${order.order_number}`">
        <Head :title="`Order ${order.order_number}`" />

        <div class="osh-page">
            <div class="osh-container">

        
                <div class="osh-ticket">
                    <div class="osh-ticket__top">
                        <div class="osh-ticket__identity">
                            <div class="osh-badges">
                                <span class="osh-badge" :class="`osh-badge--${typeTone}`">{{ typeLabel }}</span>
                                <span class="osh-badge" :class="`osh-badge--${statusTone(order.status)}`">{{ statusLabel(order.status) }}</span>
                            </div>
                            <div class="osh-ticket__number">
                                {{ order.order_number }}
                                <button
                                    type="button"
                                    class="osh-copy-btn"
                                    :class="{ 'osh-copy-btn--done': orderNumberCopied }"
                                    :title="orderNumberCopied ? 'Copied!' : 'Copy order number'"
                                    @click="copyOrderNumber"
                                >
                                    <el-icon :size="13"><component :is="orderNumberCopied ? Check : CopyDocument" /></el-icon>
                                </button>
                            </div>
                            <p class="osh-ticket__subtitle">
                                {{ order.crop_type }}<template v-if="order.variety"> · {{ order.variety }}</template>
                                · Placed {{ formatDate(order.created_at) }}
                            </p>
                        </div>

                        <div v-if="primaryAction || secondaryAction || noActionLockBadge || noActionsMessage" class="osh-ticket__actions">
                            <button v-if="primaryAction" type="button" class="osh-btn osh-btn--primary" @click="primaryAction.handler">
                                <el-icon :size="14"><component :is="primaryAction.icon" /></el-icon> {{ primaryAction.label }}
                            </button>
                            <button v-if="secondaryAction" type="button" class="osh-btn osh-btn--outline osh-btn--danger" @click="secondaryAction.handler">
                                <el-icon :size="14"><component :is="secondaryAction.icon" /></el-icon> {{ secondaryAction.label }}
                            </button>
                            <span v-if="noActionLockBadge" class="osh-lock-badge">
                                <el-icon :size="13"><component :is="noActionLockBadge.icon" /></el-icon> {{ noActionLockBadge.label }}
                            </span>
                            <span v-if="noActionsMessage" class="osh-muted">{{ noActionsMessage }}</span>
                        </div>
                    </div>

                    <div class="osh-ticket__terms">
                        <div class="osh-ticket__term">
                            <span class="osh-ticket__term-label">Quantity</span>
                            <span class="osh-ticket__term-value">{{ order.quantity.toLocaleString() }} kg</span>
                        </div>
                        <span class="osh-ticket__op">×</span>
                        <div class="osh-ticket__term">
                            <span class="osh-ticket__term-label">Unit Price</span>
                            <span class="osh-ticket__term-value">{{ formatMoney(order.unit_price, order.currency) }}</span>
                        </div>
                        <span class="osh-ticket__op">=</span>
                        <div class="osh-ticket__term osh-ticket__term--total">
                            <span class="osh-ticket__term-label">Total Amount</span>
                            <span class="osh-ticket__term-value">{{ formatMoney(order.total_amount, order.currency) }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── Order progress ────────────────────────────────────────── -->
                <section class="osh-card">
                    <div class="osh-card__head">
                        <h2 class="osh-card__title"><el-icon :size="15"><Odometer /></el-icon> Order Progress</h2>
                        <p class="osh-card__subtitle">Follow this order from posting through delivery.</p>
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
                                    <el-icon v-if="stepState(idx) === 'done'" :size="13"><Check /></el-icon>
                                    <el-icon v-else :size="13"><component :is="step.icon" /></el-icon>
                                </div>
                            </div>
                            <div class="osh-step__body">
                                <span class="osh-step__label">{{ step.label }}</span>
                                <span class="osh-step__desc">{{ stepCaption(step, idx) }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ── Needs your review — promoted above generic detail cards
                     because it's the one thing that actually needs a decision
                     from whoever posted this order. ────────────────────────── -->
                <section v-if="amCreator && intents.length" class="osh-card osh-card--review">
                    <div class="osh-review-flag"><el-icon :size="13"><WarningFilled /></el-icon> Needs Your Review</div>
                    <div class="osh-card__head">
                        <h2 class="osh-card__title"><el-icon :size="15"><User /></el-icon> Interested {{ counterpartyLabel }}s</h2>
                        <p class="osh-card__subtitle">Review each party's profile and trade history, then confirm who fulfills this order.</p>
                    </div>

                    <div class="osh-intent-list">
                        <div v-for="intent in intents" :key="intent.id" class="osh-intent-row">
                            <div class="osh-intent-row__identity">
                                <span class="osh-avatar" :style="avatarStyle(intent.user_name)">{{ initials(intent.user_name) }}</span>
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

                <!-- ── Coffee details + parties (side by side) ──────────────── -->
                <section class="osh-card">
                    <div class="osh-row">
                        <div class="osh-col">
                            <div class="osh-card__head">
                                <h2 class="osh-card__title"><el-icon :size="15"><Coffee /></el-icon> Coffee Details</h2>
                                <p class="osh-card__subtitle">Crop specification for this order. Commercial terms are shown above.</p>
                            </div>

                            <div class="osh-specs">
                                <div class="osh-spec">
                                    <span class="osh-spec__label">Crop</span>
                                    <span class="osh-spec__value">{{ order.crop_type }}</span>
                                </div>
                                <div v-if="order.variety" class="osh-spec">
                                    <span class="osh-spec__label">Variety</span>
                                    <span class="osh-spec__value">{{ order.variety }}</span>
                                </div>
                                <div v-if="order.grade" class="osh-spec">
                                    <span class="osh-spec__label">Grade</span>
                                    <span class="osh-spec__value">{{ order.grade }}</span>
                                </div>
                                <div class="osh-spec">
                                    <span class="osh-spec__label">Currency</span>
                                    <span class="osh-spec__value">{{ order.currency }}</span>
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

                        <div class="osh-row__divider" />

                        <div class="osh-col">
                            <div class="osh-card__head">
                                <h2 class="osh-card__title"><el-icon :size="15"><User /></el-icon> Buyer &amp; Seller</h2>
                                <p class="osh-card__subtitle">Contact details for both parties in this trade.</p>
                            </div>

                            <div class="osh-parties">
                                <div class="osh-party-card">
                                    <span class="osh-party__role">Buyer</span>
                                    <div v-if="order.buyer_name" class="osh-party-card__head">
                                        <span class="osh-avatar" :style="avatarStyle(order.buyer_name)">{{ initials(order.buyer_name) }}</span>
                                        <span class="osh-party__name">
                                            {{ order.buyer_name }}
                                            <span v-if="buyerIsMe" class="osh-you-tag">You</span>
                                        </span>
                                    </div>
                                    <span v-else class="osh-party__name osh-muted">Awaiting a buyer</span>

                                    <div v-if="order.buyer_email" class="osh-contact">
                                        <span class="osh-contact__row"><el-icon :size="12"><Message /></el-icon> {{ order.buyer_email }}</span>
                                        <span v-if="order.buyer_phone" class="osh-contact__row"><el-icon :size="12"><Phone /></el-icon> {{ order.buyer_phone }}</span>
                                    </div>
                                </div>

                                <div class="osh-party-card">
                                    <span class="osh-party__role">Seller</span>
                                    <div v-if="order.seller_name" class="osh-party-card__head">
                                        <span class="osh-avatar" :style="avatarStyle(order.seller_name)">{{ initials(order.seller_name) }}</span>
                                        <span class="osh-party__name">
                                            {{ order.seller_name }}
                                            <span v-if="sellerIsMe" class="osh-you-tag">You</span>
                                        </span>
                                    </div>
                                    <span v-else class="osh-party__name osh-muted">Awaiting a seller</span>

                                    <div v-if="order.seller_email" class="osh-contact">
                                        <span class="osh-contact__row"><el-icon :size="12"><Message /></el-icon> {{ order.seller_email }}</span>
                                        <span v-if="order.seller_phone" class="osh-contact__row"><el-icon :size="12"><Phone /></el-icon> {{ order.seller_phone }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ── Notes ─────────────────────────────────────────────────── -->
                <section v-if="order.notes" class="osh-card">
                    <div class="osh-card__head">
                        <h2 class="osh-card__title"><el-icon :size="15"><Files /></el-icon> Notes</h2>
                        <p class="osh-card__subtitle">Additional instructions from the order's creator.</p>
                    </div>

                    <div class="osh-notes-card">
                        <el-icon class="osh-notes-card__icon" :size="34"><ChatLineSquare /></el-icon>
                        <p class="osh-notes-card__text">{{ order.notes }}</p>
                    </div>
                </section>

            </div>
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
                    <div class="osh-avatar osh-avatar--lg" :style="avatarStyle(selectedIntent?.user_name)">{{ initials(selectedIntent?.user_name) }}</div>
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
    </DesignPreviewLayout>
</template>

<style scoped>
/* Order profile — app-wide theme. Tokens come from the shared
   DesignPreviewLayout --dp-* palette (defined on .dp-shell); literal hex
   fallbacks are the same values so the page reads correctly on its own.
   Black is the page's single accent: the total figure, the primary
   button, and active stepper indicators — everything else is grayscale. */
.osh-page {
    --card-border: var(--dp-outline-variant, #E5E7EB);
    --surface: var(--dp-surface-container-lowest, #ffffff);
    --surface-muted: var(--dp-surface-container-low, #F5F6F7);
    --surface-elevated: var(--dp-surface-container, #F1F2F3);
    --border: var(--dp-outline-variant, #E5E7EB);
    --primary: var(--dp-primary, #000000);
    --on-primary: var(--dp-on-primary, #ffffff);
    --text: var(--dp-on-surface, #121516);
    --text-2: var(--dp-on-surface-variant, #4B5457);
    --text-muted: var(--dp-outline, #6F7677);
    --secondary-container: var(--dp-secondary-container, #E5FAE7);
    --on-secondary-container: var(--dp-on-secondary-container, #2F6B35);
    font-family: var(--dp-font-sans, 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
    color: var(--text);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.osh-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ══════════════════════════════════════════════════════════════════════
   ORDER TICKET — plain bordered document header, no gradients.
   ══════════════════════════════════════════════════════════════════════ */
.osh-ticket {
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    padding: 20px 24px;
    box-shadow: var(--dp-card-shadow, none);
}

.osh-ticket__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.25rem;
}

.osh-ticket__identity { min-width: 0; }

.osh-badges { display: flex; align-items: center; gap: 14px; margin-bottom: 10px; }

/* Quiet status indicators — a small semantic dot + gray text, not a
   filled color pill. Color is reserved for meaning (open/pending/etc),
   never used as a big flat surface. */
.osh-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-2);
    white-space: nowrap;
}

.osh-badge::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
    background: var(--dot, #9ca3af);
}

.osh-badge--green { --dot: #16a34a; color: var(--text); }
.osh-badge--amber { --dot: #d97706; color: var(--text); }
.osh-badge--red   { --dot: var(--error, #F85149); color: var(--text); }
.osh-badge--blue  { --dot: #2563eb; color: var(--text); }
.osh-badge--muted { --dot: var(--text-muted); }

.osh-ticket__number {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    line-height: 1.2;
    color: var(--text);
}

.osh-ticket__subtitle {
    font-size: 0.8125rem;
    color: var(--text-muted);
    margin: 5px 0 0;
    line-height: 1.6;
}

.osh-copy-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text-2);
    cursor: pointer;
    flex-shrink: 0;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
}

.osh-copy-btn:hover { background: var(--surface-muted); color: var(--text); }
.osh-copy-btn--done { background: var(--secondary-container); border-color: var(--secondary-container); color: var(--on-secondary-container); }

.osh-ticket__actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }

.osh-lock-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--text-2);
    background: var(--surface-muted);
    border-radius: 6px;
    padding: 8px 14px;
}

/* ── Commercial terms strip: quantity × unit price = total ────────────── */
.osh-ticket__terms {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid var(--surface-muted);
}

.osh-ticket__term { display: flex; flex-direction: column; gap: 2px; }
.osh-ticket__term-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.osh-ticket__term-value { font-size: 0.9375rem; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; }
.osh-ticket__op { font-size: 0.875rem; font-weight: 700; color: var(--text-muted); }

.osh-ticket__term--total .osh-ticket__term-value { font-size: 1.125rem; font-weight: 800; color: var(--primary); }

/* ══════════════════════════════════════════════════════════════════════
   CARDS — every section below the ticket header is a distinct, elevated
   module instead of a hairline-divided strip of the page.
   ══════════════════════════════════════════════════════════════════════ */
.osh-card {
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    padding: 22px 24px;
    box-shadow: var(--dp-card-shadow, none);
    transition: box-shadow .15s ease;
}

.osh-card__head { max-width: 640px; margin-bottom: 1.25rem; }

.osh-card__title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 1.0625rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: var(--text);
    margin: 0 0 4px;
}

.osh-card__title :deep(.el-icon) { color: var(--text-muted); }

.osh-card__subtitle {
    font-size: 0.8125rem;
    color: var(--text-muted);
    line-height: 1.55;
    margin: 0;
}

.osh-muted { font-size: 0.8125rem; color: var(--text-muted); }

/* ── Buttons (in-page, not hero) ───────────────────────────────────────── */
.osh-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 0 16px;
    height: 36px;
    cursor: pointer;
    border: 1px solid transparent;
    font-family: inherit;
    transition: opacity 120ms ease, background 120ms ease, border-color 120ms ease, color 120ms ease;
}

.osh-btn--outline { background: var(--surface); border-color: var(--border); color: var(--text); }
.osh-btn--outline:hover { background: var(--surface-muted); }

.osh-btn--primary { background: var(--primary); color: var(--on-primary); }
.osh-btn--primary:hover { opacity: 0.88; }

.osh-btn--danger.osh-btn--outline:hover { background: var(--dp-error-container, #FEEDED); border-color: var(--dp-error-container, #FEEDED); color: var(--dp-on-error-container, #C6413A); }

/* ── Alert (terminal state) ─────────────────────────────────────────── */
.osh-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-radius: 6px;
    font-size: 0.8437rem;
    font-weight: 600;
}

.osh-alert--red { background: var(--dp-error-container, #FEEDED); color: var(--dp-on-error-container, #C6413A); }
.osh-alert--muted { background: var(--surface-muted); color: var(--text-2); }

/* ── Stepper ─────────────────────────────────────────────────────────── */
.osh-stepper { display: flex; align-items: flex-start; }

.osh-step {
    position: relative;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    padding-right: 1.75rem;
}

.osh-step:last-child { flex: 0 1 auto; padding-right: 0; }

.osh-step__indicator { position: relative; width: 100%; margin-bottom: 12px; }

.osh-step:not(:last-child) .osh-step__indicator::after {
    content: '';
    position: absolute;
    top: 14px;
    left: 30px;
    right: 0;
    height: 2px;
    background: var(--border);
    z-index: 0;
}

.osh-step--done:not(:last-child) .osh-step__indicator::after { background: var(--primary); }

.osh-step__dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6875rem;
    font-weight: 800;
    background: var(--surface);
    border: 2px solid var(--border);
    color: var(--text-2);
    position: relative;
    z-index: 1;
    flex-shrink: 0;
}

.osh-step--done .osh-step__dot { background: var(--primary); border-color: var(--primary); color: var(--on-primary); }

.osh-step--current .osh-step__dot {
    border-color: var(--primary);
    color: var(--primary);
    box-shadow: 0 0 0 4px var(--dp-surface-container, #F1F2F3);
}

.osh-step__body { display: flex; flex-direction: column; gap: 4px; max-width: 200px; }
.osh-step__label { font-size: 0.8125rem; font-weight: 800; color: var(--text-2); letter-spacing: -0.005em; }
.osh-step--done .osh-step__label,
.osh-step--current .osh-step__label { color: var(--text); }
.osh-step__desc { font-size: 0.75rem; line-height: 1.45; color: var(--text-muted); }
.osh-step--done .osh-step__desc { color: var(--on-secondary-container); font-weight: 700; }
.osh-step--current .osh-step__desc { color: var(--text-muted); }

/* ── Needs-your-review card ────────────────────────────────────────────── */
.osh-card--review { border-left: 3px solid #d97706; }

.osh-review-flag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #92400e;
    margin-bottom: 14px;
}

/* ── Spec tiles — replaces the old bordered label/value row list with a
   modern chip grid, matching the tile language used on the market listing
   page instead of reading like a static invoice line-item table. ──────── */
.osh-specs { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; }
.osh-spec { display: flex; flex-direction: column; gap: 3px; background: var(--surface-muted); border-radius: 6px; padding: 10px 12px; }
.osh-spec__label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.osh-spec__value { font-size: 0.875rem; font-weight: 700; color: var(--text); }

.osh-notes-card {
    position: relative;
    width: 100%;
    background: var(--surface-muted);
    border-left: 3px solid var(--primary);
    border-radius: 6px;
    padding: 1.1rem 3rem 1.1rem 1.25rem;
    overflow: hidden;
}

.osh-notes-card__icon { position: absolute; top: 10px; right: 12px; color: var(--surface-elevated); }
.osh-notes-card__text { position: relative; font-size: 0.875rem; color: var(--text); line-height: 1.65; margin: 0; white-space: pre-line; }

/* ── Interested parties ────────────────────────────────────────────────── */
.osh-intent-list { display: flex; flex-direction: column; gap: 10px; }

.osh-intent-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: 6px;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.osh-intent-row:hover { border-color: var(--dp-outline, #6F7677); box-shadow: 0 4px 12px -6px rgba(15, 23, 42, .12); }

.osh-intent-row__identity { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
.osh-intent-row__name { font-size: 0.875rem; font-weight: 700; color: var(--text); }
.osh-intent-row__role { font-size: 0.75rem; color: var(--text-muted); }
.osh-intent-row__stats { display: flex; gap: 12px; font-size: 0.75rem; color: var(--text-muted); flex-shrink: 0; font-variant-numeric: tabular-nums; }

/* ── Coffee details / buyer & seller row ─────────────────────────────── */
.osh-row { display: grid; grid-template-columns: minmax(0, 1fr) 1px minmax(0, 1fr); gap: 2.5rem; }
.osh-row__divider { background: var(--border); align-self: stretch; }
.osh-col { min-width: 0; }

/* ── Buyer & seller ──────────────────────────────────────────────────── */
.osh-parties { display: flex; flex-direction: column; gap: 10px; }

.osh-party-card {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    background: var(--surface-muted);
    border-radius: 6px;
    padding: 12px 14px;
}

.osh-party-card__head { display: flex; align-items: center; gap: 8px; }
.osh-party__role { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); }
.osh-party__name { display: inline-flex; align-items: center; gap: 6px; font-size: 0.9375rem; font-weight: 700; color: var(--text); }

/* ── Shared avatar (parties, intents, review modal) ───────────────────── */
.osh-avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.625rem;
    font-weight: 800;
}

.osh-avatar--lg { width: 38px; height: 38px; border-radius: 6px; font-size: 0.875rem; }

.osh-you-tag {
    font-size: 0.5625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--on-secondary-container);
    background: var(--secondary-container);
    border-radius: 999px;
    padding: 1px 6px;
}

.osh-contact { display: flex; flex-direction: column; gap: 4px; margin-top: 6px; }
.osh-contact__row { display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-muted); }

.osh-meta { display: flex; flex-wrap: wrap; gap: 2.5rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--surface-muted); }
.osh-meta__item { display: flex; flex-direction: column; gap: 3px; }
.osh-meta__label { display: inline-flex; align-items: center; gap: 4px; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); }
.osh-meta__value { font-size: 0.8125rem; font-weight: 700; color: var(--text); }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .osh-row { grid-template-columns: 1fr; gap: 2rem; }
    .osh-row__divider { display: none; }

    .osh-stepper { flex-direction: column; gap: 1.1rem; }
    .osh-step { flex-direction: row; align-items: flex-start; gap: 12px; padding-right: 0; }
    .osh-step__indicator { width: auto; margin-bottom: 0; }
    .osh-step:not(:last-child) .osh-step__indicator::after {
        content: '';
        position: absolute;
        top: 30px;
        left: 15px;
        right: auto;
        width: 2px;
        height: calc(100% + 1.1rem - 30px);
        background: var(--border);
    }
    .osh-step--done:not(:last-child) .osh-step__indicator::after { background: var(--primary); }
    .osh-step__body { max-width: none; padding-top: 3px; }
}

@media (max-width: 640px) {
    .osh-ticket { padding: 16px 18px; }
    .osh-ticket__number { font-size: 1.25rem; }
    .osh-ticket__terms { flex-wrap: wrap; row-gap: 12px; }
    .osh-card { padding: 18px; }
}

/* ── Interested-party review modal ─────────────────────────────────────────
   <el-dialog> teleports to <body>, outside .osh-page's DOM subtree, so the
   page's --dp-* tokens don't cascade in — app-standard literal hex from the
   same token set is used instead (same approach as every other app modal). */
:deep(.el-dialog.osh-review-modal) {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    border-radius: var(--el-border-radius-base, 6px);
    padding: 0;
    overflow: hidden;
    box-shadow: var(--el-box-shadow-dark, 0 8px 28px rgba(0, 0, 0, 0.08));
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
}

:deep(.el-dialog.osh-review-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.osh-review-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.osh-review-modal .el-dialog__footer) { padding: 0; }

.osh-review-head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.osh-review-eyebrow { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6F7677; margin-bottom: 2px; }
.osh-review-title { font-size: 15px; font-weight: 700; color: #121516; }

.osh-review-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 20px; max-height: 60vh; overflow-y: auto; }
.osh-review-section__title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6F7677; margin: 0 0 10px; }

.osh-review-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; font-size: 13px; border-bottom: 1px solid #F1F2F3; }
.osh-review-row:last-child { border-bottom: none; }
.osh-review-row__label { color: #6F7677; font-weight: 600; }
.osh-review-row__value { color: #121516; font-weight: 600; }

.osh-review-verified { display: inline-flex; border-radius: 999px; padding: 2px 8px; font-size: 11px; font-weight: 700; background: var(--dp-surface-container-high, #E5E7EB); color: var(--dp-on-surface-variant, #4B5457); }
.osh-review-verified--yes { background: var(--dp-secondary-container, #E5FAE7); color: var(--dp-on-secondary-container, #2F6B35); }

.osh-review-bio { font-size: 13px; color: #4B5457; line-height: 1.6; margin: 0 0 10px; }

.osh-review-stats { display: flex; gap: 10px; }
.osh-review-stat { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 12px 8px; border-radius: 6px; background: #F5F6F7; }
.osh-review-stat strong { font-size: 1.125rem; font-weight: 800; color: #121516; font-variant-numeric: tabular-nums; }
.osh-review-stat span { font-size: 11px; font-weight: 600; color: #6F7677; text-transform: uppercase; letter-spacing: 0.04em; }

.osh-review-footer {
    /* Button tokens are defined here (not on .osh-page) because the
       <el-dialog> teleports to <body> — the page's custom properties don't
       cascade into it, so the primary confirm button would otherwise render
       with no background. */
    --primary: #000000;
    --on-primary: #ffffff;
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --border: #E5E7EB;
    --text: #121516;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.osh-review-btn { display: inline-flex; align-items: center; gap: 6px; border-radius: 6px; font-size: 13px; font-weight: 600; padding: 0 16px; height: 36px; cursor: pointer; border: 1px solid transparent; font-family: inherit; transition: opacity 120ms ease, background 120ms ease; }
.osh-review-btn--outline { background: var(--surface); border-color: var(--border); color: var(--text); }
.osh-review-btn--outline:hover { background: var(--surface-muted); }
.osh-review-btn--primary { background: var(--primary); color: var(--on-primary); }
.osh-review-btn--primary:hover { opacity: 0.88; }
</style>
