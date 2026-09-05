<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import OfferPreviewModal from '@/Components/Offers/OfferPreviewModal.vue';
import {
    Plus, Close, Box, ShoppingCart, Coin, Checked, FolderOpened, User, List, Delete, ChatDotRound,
} from '@element-plus/icons-vue';

const props = defineProps({
    offers: { type: Array, default: () => [] },
    myOfferResponses: { type: Array, default: () => [] },
    myResponses: { type: Array, default: () => [] },
    authUserId: { type: Number, default: null },
});

/* ── Search + KPI helpers ────────────────────────────────────────────── */
function matchesFilter(key, offer) {
    switch (key) {
        case 'open': return offer.status === 'open';
        case 'pending': return offer.status === 'pending';
        case 'delivered': return offer.status === 'delivered';
        case 'cancelled': return offer.status === 'cancelled';
        default: return true;
    }
}

const showMyOffers = ref(false);

const filteredOffers = computed(() => {
    if (!showMyOffers.value) return props.offers;
    return props.offers.filter((offer) => offer.seller_id === props.authUserId);
});

function tabCount(key) {
    return props.offers.filter((o) => matchesFilter(key, o)).length;
}

/* ── KPIs ────────────────────────────────────────────────────────────── */
const kpis = computed(() => ({
    total: props.offers.length,
    open: tabCount('open'),
    pending: tabCount('pending'),
    delivered: tabCount('delivered'),
}));

/* ── Display helpers ─────────────────────────────────────────────────── */
function statusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function statusTone(status) {
    return {
        open: 'ofr-badge--muted',
        pending: 'ofr-badge--amber',
        confirmed: 'ofr-badge--blue',
        processing: 'ofr-badge--blue',
        shipped: 'ofr-badge--blue',
        delivered: 'ofr-badge--green',
        cancelled: 'ofr-badge--red',
        withdrawn: 'ofr-badge--muted',
        bought: 'ofr-badge--green',
    }[status] ?? 'ofr-badge--muted';
}

function responseTone(status) {
    return {
        pending: 'ofr-badge--amber',
        accepted: 'ofr-badge--green',
        declined: 'ofr-badge--red',
        paid: 'ofr-badge--green',
    }[status] ?? 'ofr-badge--muted';
}

function formatMoney(amount, currency) {
    return `${currency} ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

/* ── Preview modal ────────────────────────────────────────────────────── */
const previewOpen = ref(false);
const selectedOffer = ref(null);
const confirmDeleteOpen = ref(false);
const pendingDeleteOffer = ref(null);

function openPreview(offer) {
    selectedOffer.value = offer;
    previewOpen.value = true;
}

/* ── Response preview ────────────────────────────────────────────────── */
const responsePreviewOpen = ref(false);
const selectedResponse = ref(null);

const allResponses = computed(() => [...props.myOfferResponses, ...props.myResponses]);

function isBuyerResponse(response) {
    return response.user_id === props.authUserId;
}

function responseKind(response) {
    return isBuyerResponse(response) ? 'Sent' : 'Received';
}

function responseParty(response) {
    return isBuyerResponse(response)
        ? (response.owner_name || '—')
        : (response.user_name || '—');
}

function openResponsePreview(response) {
    selectedResponse.value = response;
    responsePreviewOpen.value = true;
}

function respondToResponse(status) {
    if (!selectedResponse.value) return;
    router.patch(route('trade.offer.response.update', selectedResponse.value.id), { status }, {
        preserveScroll: true,
        onSuccess: () => {
            responsePreviewOpen.value = false;
            selectedResponse.value = null;
        },
    });
}

function formatDate(dateTime) {
    if (!dateTime) return '—';
    const date = new Date(dateTime.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleString(undefined, { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function editOffer(offer) {
    previewOpen.value = false;
    editingOfferId.value = offer.id;
    form.clearErrors();
    form.crop_type = offer.crop_type;
    form.variety = offer.variety ?? '';
    form.grade = offer.grade ?? '';
    form.quantity = offer.quantity;
    form.unit_price = offer.unit_price;
    form.notes = offer.notes ?? '';
    if (cropSelectOptions.includes(offer.crop_type)) {
        cropSelectValue.value = offer.crop_type;
        otherCropMode.value = false;
    } else {
        cropSelectValue.value = '__other__';
        otherCropMode.value = true;
    }
    createOpen.value = true;
}

function removeOffer(offer) {
    pendingDeleteOffer.value = offer;
    previewOpen.value = false;
    confirmDeleteOpen.value = true;
}

function confirmDeleteOffer() {
    if (!pendingDeleteOffer.value) return;
    router.delete(route('trade.offer.destroy', pendingDeleteOffer.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmDeleteOpen.value = false;
            previewOpen.value = false;
            pendingDeleteOffer.value = null;
        },
    });
}

/* ── Identity — deterministic initials + palette per seller name ─────── */
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

/* ── Create / edit offer dialog ──────────────────────────────────────── */
const createOpen = ref(false);
const editingOfferId = ref(null);
const form = useForm({
    crop_type: '',
    variety: '',
    grade: '',
    quantity: null,
    unit_price: null,
    notes: '',
});

const cropSelectOptions = ['Arabica', 'Robusta'];
const otherCropMode = ref(false);
const cropSelectValue = ref('');

function handleCropSelect(value) {
    if (value === '__other__') {
        otherCropMode.value = true;
        form.crop_type = '';
    } else {
        otherCropMode.value = false;
        form.crop_type = value;
    }
}

const estimatedTotal = computed(() => (Number(form.quantity) || 0) * (Number(form.unit_price) || 0));

function openCreateDialog() {
    form.reset();
    form.clearErrors();
    editingOfferId.value = null;
    otherCropMode.value = false;
    cropSelectValue.value = '';
    createOpen.value = true;
}

function saveOffer() {
    form.clearErrors();
    if (!form.crop_type.trim()) form.setError('crop_type', 'Crop type is required.');
    if (!form.quantity) form.setError('quantity', 'Quantity is required.');
    if (!form.unit_price) form.setError('unit_price', 'Unit price is required.');
    if (Object.keys(form.errors).length) return;

    const options = {
        preserveScroll: true,
        onSuccess: () => { createOpen.value = false; },
    };

    if (editingOfferId.value) {
        form.patch(route('trade.offer.update', editingOfferId.value), options);
    } else {
        form.post(route('trade.offer.store'), options);
    }
}
</script>

<template>
    <DesignPreviewLayout title="Offers">
        <Head title="Offers" />

        <div class="ofr-page">
            <!-- ── Page Header ───────────────────────────────────────────── -->
            <div class="ofr-page-header">
                <div class="ofr-page-header__left">
                    <h1 class="ofr-title">Offers</h1>
                    <p class="ofr-subtitle">Browse and respond to sell-side offers posted by growers and exporters across the marketplace.</p>
                </div>
                <div class="ofr-page-header__actions">
                    <button type="button" class="ofr-btn ofr-btn--primary" @click="openCreateDialog">
                        <el-icon><Plus /></el-icon> Post Offer
                    </button>
                </div>
            </div>

            <!-- ── Overview tiles ─────────────────────────────────────────── -->
            <div class="ofr-kpi-grid">
                <div class="ofr-kpi">
                    <div class="ofr-kpi__icon"><el-icon :size="16"><List /></el-icon></div>
                    <div class="ofr-kpi__body">
                        <strong class="ofr-kpi__val">{{ kpis.total }}</strong>
                        <span class="ofr-kpi__label">Total Offers</span>
                    </div>
                </div>
                <div class="ofr-kpi">
                    <div class="ofr-kpi__icon"><el-icon :size="16"><ShoppingCart /></el-icon></div>
                    <div class="ofr-kpi__body">
                        <strong class="ofr-kpi__val">{{ kpis.open }}</strong>
                        <span class="ofr-kpi__label">Open</span>
                    </div>
                </div>
                <div class="ofr-kpi">
                    <div class="ofr-kpi__icon"><el-icon :size="16"><Box /></el-icon></div>
                    <div class="ofr-kpi__body">
                        <strong class="ofr-kpi__val">{{ kpis.pending }}</strong>
                        <span class="ofr-kpi__label">Pending</span>
                    </div>
                </div>
                <div class="ofr-kpi">
                    <div class="ofr-kpi__icon ofr-kpi__icon--green"><el-icon :size="16"><Checked /></el-icon></div>
                    <div class="ofr-kpi__body">
                        <strong class="ofr-kpi__val ofr-text-green">{{ kpis.delivered }}</strong>
                        <span class="ofr-kpi__label">Delivered</span>
                    </div>
                </div>
            </div>

            <div class="ofr-body">
                <div class="ofr-columns">
                    <div class="ofr-columns__main">
                        <div class="ofr-section">
                            <div class="ofr-toolbar">
                                <h2 class="ofr-toolbar-title">{{ showMyOffers ? 'My Offers' : 'All Offers' }}</h2>

                                <button
                                    type="button"
                                    class="ofr-btn ofr-btn--outline"
                                    :class="{ 'ofr-btn--active': showMyOffers }"
                                    @click="showMyOffers = !showMyOffers"
                                >
                                    <el-icon :size="14"><User /></el-icon>
                                    {{ showMyOffers ? 'Show All Offers' : 'My Offers' }}
                                </button>
                            </div>

                    <div class="ofr-card">
                        <el-table
                            :data="filteredOffers"
                            class="ofr-table"
                            @row-click="openPreview"
                        >
                            <el-table-column>
                                <template #header><span class="ofr-th"><el-icon><List /></el-icon> Offer</span></template>
                                <template #default="{ row }">
                                    <div class="ofr-cell-order">
                                        <span class="ofr-cell-order__num">{{ row.offer_number }}</span>
                                        <span class="ofr-cell-order__sub">{{ row.crop_type }}<template v-if="row.variety"> · {{ row.variety }}</template></span>
                                    </div>
                                </template>
                            </el-table-column>

                            <el-table-column>
                                <template #header><span class="ofr-th"><el-icon><User /></el-icon> Seller</span></template>
                                <template #default="{ row }">
                                    <div class="ofr-cell-party">
                                        <span v-if="row.seller_name" class="ofr-avatar" :style="avatarStyle(row.seller_name)">{{ initials(row.seller_name) }}</span>
                                        <span class="ofr-cell-party__name">{{ row.seller_name || '—' }}</span>
                                    </div>
                                </template>
                            </el-table-column>

                            <el-table-column align="right">
                                <template #header><span class="ofr-th ofr-th--right"><el-icon><Coin /></el-icon> Amount</span></template>
                                <template #default="{ row }">
                                    <div class="ofr-cell-amount">
                                        <strong class="ofr-num ofr-amount">{{ formatMoney(row.total_amount, row.currency) }}</strong>
                                        <span class="ofr-cell-amount__qty">{{ Number(row.quantity).toLocaleString() }} kg</span>
                                    </div>
                                </template>
                            </el-table-column>

                            <el-table-column align="right" width="110">
                                <template #header><span class="ofr-th ofr-th--right"><el-icon><Checked /></el-icon> Status</span></template>
                                <template #default="{ row }">
                                    <span class="ofr-badge" :class="statusTone(row.status)">{{ statusLabel(row.status) }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column v-if="offers.some((o) => o.seller_id === authUserId)" align="right" width="70">
                                <template #header><span class="ofr-th ofr-th--right">Action</span></template>
                                <template #default="{ row }">
                                    <button
                                        v-if="row.seller_id === authUserId"
                                        type="button"
                                        class="ofr-row-action"
                                        title="Delete offer"
                                        @click.stop="removeOffer(row)"
                                    >
                                        <el-icon :size="15"><Delete /></el-icon>
                                    </button>
                                </template>
                            </el-table-column>

                            <template #empty>
                                <div class="ofr-empty">
                                    <div class="ofr-empty__icon"><el-icon :size="22"><FolderOpened /></el-icon></div>
                                    <template v-if="showMyOffers">
                                        <div class="ofr-empty__title">No offers posted yet</div>
                                        <p class="ofr-empty__text">You haven't posted any offers. Post one to start selling.</p>
                                    </template>
                                    <template v-else>
                                        <div class="ofr-empty__title">No offers yet</div>
                                        <p class="ofr-empty__text">Post an offer to start selling on Bean Origin.</p>
                                    </template>
                                </div>
                            </template>
                        </el-table>
                    </div>
                </div>
                    </div>

                    <!-- ── Responses ─────────────────────────────────────────── -->
                    <div class="ofr-columns__side">
                        <div class="ofr-section">
                            <div class="ofr-toolbar">
                                <h2 class="ofr-toolbar-title">
                                    <el-icon class="ofr-toolbar-title__icon"><ChatDotRound /></el-icon>
                                    Responses
                                </h2>
                                <span class="ofr-toolbar-count">{{ allResponses.length }}</span>
                            </div>

                            <div class="ofr-card">
                                <el-table :data="allResponses" class="ofr-table ofr-table--responses" @row-click="openResponsePreview">
                                    <el-table-column>
                                        <template #header><span class="ofr-th"><el-icon><List /></el-icon> Response</span></template>
                                        <template #default="{ row }">
                                            <div class="ofr-cell-response">
                                                <div class="ofr-cell-response__top">
                                                    <span class="ofr-cell-response__offer">{{ row.offer_number || '—' }}</span>
                                                    <span class="ofr-badge ofr-badge--kind" :class="isBuyerResponse(row) ? 'ofr-badge--blue' : 'ofr-badge--muted'">{{ responseKind(row) }}</span>
                                                </div>
                                                <div class="ofr-cell-response__party">
                                                    <span class="ofr-avatar ofr-avatar--sm" :style="avatarStyle(responseParty(row))">{{ initials(responseParty(row)) }}</span>
                                                    <span class="ofr-cell-response__name">{{ responseParty(row) }}</span>
                                                    <span class="ofr-cell-response__role">{{ isBuyerResponse(row) ? 'Seller' : 'Buyer' }}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column width="96" align="right">
                                        <template #header><span class="ofr-th ofr-th--right">Status</span></template>
                                        <template #default="{ row }">
                                            <Link
                                                v-if="isBuyerResponse(row) && row.status === 'accepted'"
                                                :href="route('trade.offer.payment', row.offer_id)"
                                                class="ofr-btn ofr-btn--success ofr-btn--sm"
                                                @click.stop
                                            >
                                                Pay
                                            </Link>
                                            <span v-else class="ofr-badge" :class="responseTone(row.status)">{{ statusLabel(row.status) }}</span>
                                        </template>
                                    </el-table-column>

                                    <template #empty>
                                        <div class="ofr-empty">
                                            <div class="ofr-empty__icon"><el-icon :size="22"><FolderOpened /></el-icon></div>
                                            <div class="ofr-empty__title">Nothing here</div>
                                            <p class="ofr-empty__text">Responses to your offers and your responses will appear here.</p>
                                        </div>
                                    </template>
                                </el-table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ── Post Offer modal ──────────────────────────────────────────── -->
        <el-dialog
            v-model="createOpen"
            width="min(480px, calc(100vw - 2rem))"
            destroy-on-close
            align-center
            :show-close="false"
            class="ofr-modal"
        >
            <template #header>
                <div class="ofr-modal__head">
                    <div class="ofr-modal__head-icon">
                        <el-icon :size="18"><Box /></el-icon>
                    </div>
                    <div class="ofr-modal__head-text">
                        <div class="ofr-modal__eyebrow">Sell</div>
                        <div class="ofr-modal__title">{{ editingOfferId ? 'Edit Offer' : 'New Offer' }}</div>
                    </div>
                    <button type="button" class="ofr-modal__close" aria-label="Close" @click="createOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="ofr-modal__body">
                <div class="ofr-field">
                    <label class="ofr-field__label">Crop Type</label>
                    <el-select
                        v-model="cropSelectValue"
                        placeholder="Select crop type…"
                        style="width:100%"
                        class="ofr-input"
                        :class="{ 'ofr-input--error': form.errors.crop_type }"
                        @change="handleCropSelect"
                    >
                        <el-option v-for="opt in cropSelectOptions" :key="opt" :label="opt" :value="opt" />
                        <el-option label="Other" value="__other__" />
                    </el-select>
                    <el-input
                        v-if="otherCropMode"
                        v-model="form.crop_type"
                        placeholder="e.g. Liberica, Excelsa"
                        class="ofr-input mt-2"
                        :class="{ 'ofr-input--error': form.errors.crop_type }"
                    />
                    <span v-if="form.errors.crop_type" class="ofr-field__error">{{ form.errors.crop_type }}</span>
                </div>

                <div class="ofr-grid-2">
                    <div class="ofr-field">
                        <label class="ofr-field__label">Variety</label>
                        <el-input v-model="form.variety" placeholder="e.g. Bugisu" class="ofr-input" />
                    </div>
                    <div class="ofr-field">
                        <label class="ofr-field__label">Grade</label>
                        <el-input v-model="form.grade" placeholder="e.g. AA" class="ofr-input" />
                    </div>
                </div>

                <div class="ofr-grid-2">
                    <div class="ofr-field">
                        <label class="ofr-field__label">Quantity (kg)</label>
                        <el-input v-model="form.quantity" type="number" min="0" placeholder="0" class="ofr-input" :class="{ 'ofr-input--error': form.errors.quantity }" />
                        <span v-if="form.errors.quantity" class="ofr-field__error">{{ form.errors.quantity }}</span>
                    </div>
                    <div class="ofr-field">
                        <label class="ofr-field__label">Unit Price</label>
                        <el-input v-model="form.unit_price" type="number" min="0" step="0.01" placeholder="0.00" class="ofr-input" :class="{ 'ofr-input--error': form.errors.unit_price }" />
                        <span v-if="form.errors.unit_price" class="ofr-field__error">{{ form.errors.unit_price }}</span>
                    </div>
                </div>

                <div class="ofr-field">
                    <label class="ofr-field__label">Notes</label>
                    <el-input v-model="form.notes" type="textarea" :rows="3" placeholder="Optional details about the coffee…" class="ofr-input" />
                </div>

                <div class="ofr-modal__total">
                    <span>Estimated total</span>
                    <strong>{{ formatMoney(estimatedTotal, 'USD') }}</strong>
                </div>
            </div>

            <template #footer>
                <div class="ofr-modal__footer">
                    <button v-if="!editingOfferId" type="button" class="ofr-btn ofr-btn--outline" @click="createOpen = false">Cancel</button>
                    <button type="button" class="ofr-btn ofr-btn--primary" :disabled="form.processing" @click="saveOffer">
                        {{ form.processing ? (editingOfferId ? 'Saving…' : 'Posting…') : (editingOfferId ? 'Save Changes' : 'Post Offer') }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Offer preview modal ────────────────────────────────────────── -->
        <OfferPreviewModal
            v-model="previewOpen"
            :offer="selectedOffer"
            :auth-user-id="authUserId"
            @edit="editOffer"
            @delete="removeOffer"
        />

        <!-- ── Delete offer confirmation ─────────────────────────────────── -->
        <ConfirmDialog
            v-model="confirmDeleteOpen"
            eyebrow="Trade"
            title="Delete Offer"
            :message="pendingDeleteOffer ? `Are you sure you want to delete ${pendingDeleteOffer.offer_number}? This action cannot be undone.` : ''"
            confirm-text="Delete Offer"
            @confirm="confirmDeleteOffer"
        />

        <!-- ── Response detail modal ─────────────────────────────────────── -->
        <el-dialog
            v-model="responsePreviewOpen"
            width="min(520px, calc(100vw - 2rem))"
            destroy-on-close
            align-center
            :show-close="false"
            class="ofr-modal"
        >
            <template #header>
                <div class="ofr-modal__head">
                    <div class="ofr-modal__head-icon">
                        <el-icon :size="18"><User /></el-icon>
                    </div>
                    <div class="ofr-modal__head-text">
                        <div class="ofr-modal__eyebrow">Response</div>
                        <div class="ofr-modal__title">{{ selectedResponse?.user_name || '—' }}</div>
                    </div>
                    <button type="button" class="ofr-modal__close" aria-label="Close" @click="responsePreviewOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div v-if="selectedResponse" class="ofr-modal__body">
                <div class="ofr-resp__top">
                    <span class="ofr-badge" :class="responseTone(selectedResponse.status)">{{ statusLabel(selectedResponse.status) }}</span>
                    <span class="ofr-resp__date">{{ formatDate(selectedResponse.created_at) }}</span>
                </div>

                <div class="ofr-resp__offer">
                    <span class="ofr-resp__offer-label">Offer</span>
                    <strong class="ofr-resp__offer-value">{{ selectedResponse.offer_number || '—' }} · {{ selectedResponse.crop_type || '—' }}</strong>
                </div>

                <div class="ofr-resp__grid">
                    <div class="ofr-resp__item"><span>Variety</span><strong>{{ selectedResponse.variety || '—' }}</strong></div>
                    <div class="ofr-resp__item"><span>Grade</span><strong>{{ selectedResponse.grade || '—' }}</strong></div>
                    <div class="ofr-resp__item"><span>Quantity</span><strong>{{ Number(selectedResponse.quantity || 0).toLocaleString() }} kg</strong></div>
                    <div class="ofr-resp__item"><span>Total</span><strong>{{ formatMoney(selectedResponse.total_amount, selectedResponse.currency) }}</strong></div>
                </div>

                <div v-if="selectedResponse.message" class="ofr-resp__message">
                    <span class="ofr-resp__message-label">Message</span>
                    <p class="ofr-resp__message-text">{{ selectedResponse.message }}</p>
                </div>
            </div>

            <template #footer>
                <div v-if="selectedResponse && selectedResponse.status !== 'paid'" class="ofr-modal__footer">
                    <div class="ofr-modal__footer-actions">
                        <template v-if="!isBuyerResponse(selectedResponse)">
                            <button
                                v-if="selectedResponse.status !== 'declined'"
                                type="button"
                                class="ofr-btn ofr-btn--danger"
                                @click="respondToResponse('declined')"
                            >
                                Decline
                            </button>
                            <button
                                v-if="selectedResponse.status !== 'accepted'"
                                type="button"
                                class="ofr-btn ofr-btn--success"
                                @click="respondToResponse('accepted')"
                            >
                                Accept
                            </button>
                        </template>
                        <template v-else-if="selectedResponse.status === 'accepted'">
                            <Link :href="route('trade.offer.payment', selectedResponse.offer_id)" class="ofr-btn ofr-btn--success">Pay</Link>
                        </template>
                    </div>
                </div>
            </template>
        </el-dialog>
    </DesignPreviewLayout>
</template>

<style scoped>
.ofr-page {
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
    --success: #15803D;
    --error: var(--dp-error, #F85149);
    font-family: var(--dp-font-sans, 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
    color: var(--text);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.ofr-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.ofr-page-header__left { max-width: 640px; }
.ofr-page-header__actions { display: flex; gap: 8px; flex-wrap: wrap; }

.ofr-title { font-size: 1.5rem; line-height: 1.9rem; letter-spacing: -0.015em; font-weight: 800; margin: 0 0 6px; }
.ofr-subtitle { font-size: 0.9375rem; line-height: 1.5rem; color: var(--text-muted); margin: 0; max-width: 64ch; text-wrap: pretty; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.ofr-btn {
    height: 36px;
    padding: 0 16px;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 120ms ease, background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ofr-btn--primary { background: var(--primary); border: 1px solid transparent; color: var(--on-primary); }
.ofr-btn--primary:hover:not(:disabled) { opacity: 0.88; }
.ofr-btn--primary:disabled { opacity: 0.5; cursor: default; }
.ofr-btn--outline { background: var(--surface); border: 1px solid var(--border); color: var(--text); }
.ofr-btn--outline:hover { background: var(--surface-muted); }
.ofr-btn--danger { background: #B91C1C; border: 1px solid transparent; color: #FFFFFF; }
.ofr-btn--danger:hover:not(:disabled) { opacity: 0.88; }
.ofr-btn--success { background: #16A34A; border: 1px solid transparent; color: #FFFFFF; }
.ofr-btn--success:hover:not(:disabled) { opacity: 0.88; }

.ofr-btn-primary {
    height: 36px;
    padding: 0 16px;
    background: var(--primary);
    border: 1px solid transparent;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    color: var(--on-primary);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 120ms ease;
}
.ofr-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.ofr-btn-primary:disabled { opacity: 0.5; cursor: default; }

.ofr-btn-outline {
    height: 36px;
    padding: 0 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ofr-btn-outline:hover { background: var(--surface-muted); }

/* ── Overview / KPI strip ────────────────────────────────────────────── */
.ofr-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
.ofr-kpi {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow, none);
    padding: 16px 18px;
    transition: box-shadow 0.15s ease, transform 0.15s ease, border-color 0.15s ease;
}
.ofr-kpi:hover { box-shadow: 0 12px 28px -18px rgba(15, 23, 42, 0.18); transform: translateY(-1px); border-color: var(--primary); }
.ofr-kpi__icon { width: 38px; height: 38px; border-radius: 10px; background: var(--surface-muted); color: var(--text-2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ofr-kpi__icon--green { background: var(--dp-secondary-container, #E5FAE7); color: var(--dp-on-secondary-container, #2F6B35); }
.ofr-kpi__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.ofr-kpi__label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); white-space: nowrap; }
.ofr-kpi__val { font-size: 1.5rem; line-height: 2rem; font-weight: 800; color: var(--text); letter-spacing: -0.01em; font-variant-numeric: tabular-nums; }
.ofr-text-green { color: var(--success); }

/* ── Body / section card ─────────────────────────────────────────────── */
.ofr-body { display: flex; flex-direction: column; }
.ofr-columns { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 16px; align-items: stretch; }
.ofr-columns__main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.ofr-columns__side { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.ofr-toolbar-count { font-size: 11px; font-weight: 700; color: var(--text-muted); background: var(--surface-elevated); padding: 2px 10px; border-radius: 999px; }
.ofr-section {
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow, none);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    flex: 1;
    height: 100%;
}

/* ── Toolbar: title + search ─────────────────────────────────────────── */
.ofr-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}
.ofr-toolbar-title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -0.005em; color: var(--text); margin: 0; display: inline-flex; align-items: center; gap: 8px; }
.ofr-toolbar-title__icon { color: var(--text-muted); font-size: 16px; }

/* ── Response tabs (removed — single merged list) ─────────────────────── */

.ofr-btn--sm { height: 30px; padding: 0 12px; font-size: 12px; }

.ofr-btn--active { background: var(--surface-muted); border-color: var(--text); color: var(--text); }

/* ── Card / table ────────────────────────────────────────────────────── */
.ofr-card { overflow: hidden; }
.ofr-table {
    --el-table-border-color: var(--border);
    --el-table-bg-color: transparent;
    --el-table-tr-bg-color: transparent;
    --el-table-header-bg-color: var(--surface-muted);
    --el-table-header-text-color: var(--text-muted);
    --el-table-text-color: var(--text-2);
    --el-table-row-hover-bg-color: var(--surface-muted);
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
}
.ofr-table :deep(.el-table__header) th { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }
.ofr-table :deep(.el-table__row) { cursor: pointer; }
.ofr-table :deep(.el-table__inner-wrapper::before) { display: none; }
.ofr-table :deep(.el-table__header-wrapper th:first-child .cell),
.ofr-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
.ofr-table :deep(.el-table__header-wrapper th:last-child .cell),
.ofr-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }

.ofr-th { display: inline-flex; align-items: center; gap: 6px; }
.ofr-th :deep(.el-icon) { font-size: 13px; color: var(--text-muted); }
.ofr-th--right { justify-content: flex-end; }

.ofr-cell-order { display: flex; flex-direction: column; gap: 3px; align-items: flex-start; min-width: 0; }
.ofr-cell-order__num { font-size: 13px; font-weight: 600; color: var(--text); font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
.ofr-cell-order__sub { font-size: 11px; font-weight: 500; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
.ofr-cell-amount { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; min-width: 0; }
.ofr-cell-amount__qty { font-size: 11px; color: var(--text-muted); white-space: nowrap; }

.ofr-cell-party { display: flex; align-items: center; gap: 8px; min-width: 0; }
.ofr-avatar { width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.625rem; font-weight: 800; }
.ofr-avatar--sm { width: 20px; height: 20px; font-size: 0.5625rem; }
.ofr-cell-party__name { font-size: 13px; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.ofr-cell-response { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.ofr-cell-response__top { display: flex; align-items: center; gap: 6px; min-width: 0; }
.ofr-cell-response__offer { font-size: 12px; font-weight: 600; color: var(--text); font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.ofr-cell-response__party { display: flex; align-items: center; gap: 6px; min-width: 0; }
.ofr-cell-response__name { font-size: 12.5px; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ofr-cell-response__role { font-size: 10.5px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; flex-shrink: 0; }
.ofr-badge--kind { font-size: 10px; padding: 2px 7px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; }

.ofr-row-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: 1px solid transparent;
    border-radius: 6px;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ofr-row-action:hover { background: #FBE9E9; color: #B91C1C; border-color: #F5C6C6; }

.ofr-num { font-variant-numeric: tabular-nums; }
.ofr-amount { font-weight: 600; color: var(--text); }

.ofr-badge { display: inline-flex; border-radius: 999px; font-size: 11px; font-weight: 600; padding: 4px 10px; flex-shrink: 0; white-space: nowrap; }
.ofr-badge--green { background: var(--dp-secondary-container, #E5FAE7); color: var(--dp-on-secondary-container, #2F6B35); }
.ofr-badge--amber { background: #fef3c7; color: #92400e; }
.ofr-badge--red { background: var(--dp-error-container, #FEEDED); color: #991b1b; }
.ofr-badge--blue { background: #dbeafe; color: #1e40af; }
.ofr-badge--muted { background: var(--dp-surface-container-high, #E5E7EB); color: var(--dp-on-surface-variant, #4B5457); }

/* ── Empty state ───────────────────────────────────────────────────────── */
.ofr-empty { text-align: center; padding: 3rem 1rem; }
.ofr-empty__icon { width: 52px; height: 52px; border-radius: 12px; background: var(--surface-muted); color: var(--text-muted); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.ofr-empty__title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.ofr-empty__text { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; max-width: 340px; margin-left: auto; margin-right: auto; line-height: 1.5; }
/* ── New Offer modal — <el-dialog> teleports to <body>, so literal hex
   values are used instead of the page's --dp-* tokens (same as the
   orders page). ──────────────────────────────────────────────────────── */
:deep(.el-dialog.ofr-modal) {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
}
:deep(.el-dialog.ofr-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.ofr-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.ofr-modal .el-dialog__footer) { padding: 0; }

.ofr-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.ofr-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; border: 1px solid #E5E7EB; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ofr-modal__head-text { flex: 1; min-width: 0; }
.ofr-modal__eyebrow { font-size: 11px; line-height: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6F7677; margin-bottom: 2px; }
.ofr-modal__title { font-size: 15px; line-height: 20px; font-weight: 700; color: #121516; }
.ofr-modal__close { width: 32px; height: 32px; border-radius: 6px; border: none; background: transparent; color: #6F7677; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 120ms, color 120ms; }
.ofr-modal__close:hover { background: #F1F2F3; color: #121516; }

.ofr-modal__body { padding: 22px 24px 6px; display: flex; flex-direction: column; gap: 16px; max-height: 65vh; overflow-y: auto; }
.ofr-field { display: flex; flex-direction: column; gap: 6px; }
.ofr-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.ofr-field__label { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: #121516; }
.ofr-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.ofr-input--error :deep(.el-input__wrapper),
.ofr-input--error :deep(.el-textarea__inner),
.ofr-input--error :deep(.el-select__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.ofr-input :deep(.el-input__wrapper),
.ofr-input :deep(.el-textarea__inner) { border-radius: 6px; box-shadow: 0 0 0 1px #E5E7EB inset; background: #F5F6F7; transition: box-shadow 120ms, background 120ms; }
.ofr-input :deep(.el-input__wrapper:hover),
.ofr-input :deep(.el-textarea__inner:hover) { background: #fff; box-shadow: 0 0 0 1px #E5E7EB inset; }
.ofr-input :deep(.el-input__wrapper.is-focus),
.ofr-input :deep(.el-textarea__inner:focus) { background: #fff; box-shadow: 0 0 0 1.5px #000000 inset; }

.ofr-modal__total { display: flex; align-items: center; justify-content: space-between; background: #F5F6F7; border: 1px solid #E5E7EB; border-radius: 6px; padding: 10px 12px; font-size: 12px; color: #6F7677; }
.ofr-modal__total strong { font-size: 14px; color: #121516; font-weight: 800; }

/* ── Response detail modal content ─────────────────────────────────────── */
.ofr-resp__top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.ofr-resp__date { font-size: 12.5px; color: #6F7677; }
.ofr-resp__offer { display: flex; flex-direction: column; gap: 3px; background: #F5F6F7; border: 1px solid #E5E7EB; border-radius: 6px; padding: 10px 12px; }
.ofr-resp__offer-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6F7677; }
.ofr-resp__offer-value { font-size: 13.5px; font-weight: 700; color: #121516; }
.ofr-resp__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ofr-resp__item { display: flex; flex-direction: column; gap: 3px; background: #F5F6F7; border: 1px solid #E5E7EB; border-radius: 6px; padding: 10px 12px; min-width: 0; }
.ofr-resp__item span { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6F7677; }
.ofr-resp__item strong { font-size: 13.5px; font-weight: 700; color: #121516; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ofr-resp__message { display: flex; flex-direction: column; gap: 4px; }
.ofr-resp__message-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6F7677; }
.ofr-resp__message-text { font-size: 13px; line-height: 1.6; color: #4B5457; background: #F5F6F7; border-radius: 6px; padding: 10px 12px; margin: 0; white-space: pre-wrap; }

.ofr-modal__footer {
    --primary: #000000;
    --on-primary: #ffffff;
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --border: #E5E7EB;
    --text: #121516;
    --text-muted: #6F7677;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.ofr-modal__footer-actions { display: flex; align-items: center; gap: 10px; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ofr-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .ofr-columns { grid-template-columns: 1fr; }
}
@media (max-width: 767.98px) {
    .ofr-page-header { flex-direction: column; align-items: stretch; }
    .ofr-grid-2 { grid-template-columns: 1fr; }
    .ofr-page { gap: 14px; }
    .ofr-title { font-size: 1.25rem; line-height: 1.6rem; }
    .ofr-page-header__actions .ofr-btn { width: 100%; justify-content: center; }
    .ofr-kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .ofr-kpi { padding: 12px 14px; gap: 10px; }
    .ofr-kpi__icon { width: 32px; height: 32px; }
    .ofr-kpi__val { font-size: 1.25rem; line-height: 1.6rem; }
    .ofr-toolbar { flex-direction: column; align-items: stretch; }
}
@media (max-width: 479.98px) {
    .ofr-kpi-grid { grid-template-columns: 1fr 1fr; }
    .ofr-kpi__label { font-size: 0.625rem; }
    .ofr-modal__head { padding: 16px 18px; }
    .ofr-modal__body { padding: 18px 18px 6px; }
    .ofr-modal__footer { padding: 14px 18px; flex-wrap: wrap; }
    .ofr-resp__grid { grid-template-columns: 1fr; }
}
</style>