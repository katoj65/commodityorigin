<script setup>
import { computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    ArrowLeft,
    Box,
    CircleCheck,
    Clock,
    Coin,
    Document,
    Files,
    Goods,
    Location,
    Medal,
    Stamp,
    Timer,
    Trophy,
    User,
} from '@element-plus/icons-vue';

const props = defineProps({
    lot: { type: Object, default: () => ({}) },
    canBid: { type: Boolean, default: false },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');

const status = computed(() => {
    const s = props.lot.status;
    if (s === 'draft') return { label: 'Upcoming', tone: 'neutral' };
    if (['ready', 'listing_ready', 'tokenisation_ready'].includes(s)) return { label: 'Live', tone: 'live' };
    if (s === 'ended') return { label: 'Ended', tone: 'neutral' };
    if (s === 'awarded') return { label: 'Awarded', tone: 'live' };
    return { label: (s || '—').replace(/_/g, ' '), tone: 'neutral' };
});

const currentPrice = computed(() => props.lot.current_bid ?? props.lot.starting_price ?? 0);
const minNextBid = computed(() => {
    const base = props.lot.current_bid != null ? props.lot.current_bid : (props.lot.starting_price ?? 0);
    const inc = Number(props.lot.min_increment ?? 1);
    return Number((base + inc).toFixed(2));
});

const trust = computed(() => [
    { label: 'Verified', active: props.lot.status !== 'draft', icon: CircleCheck },
    { label: 'Traceable', active: true, icon: Stamp },
    { label: 'Quality scored', active: (props.lot.quality_score ?? 0) > 0, icon: Medal },
    { label: 'Certified grade', active: !!props.lot.grade, icon: Trophy },
]);

const journey = ['Farm', 'Collection', 'Batch', 'Processing', 'Lot', 'Blockchain'];

const form = useForm({
    lot_id: props.lot.id ?? '',
    bid_amount: '',
    quantity: props.lot.net_weight_kg ?? '',
    notes: '',
});

onMounted(() => {
    if (!form.bid_amount) form.bid_amount = minNextBid.value.toFixed(2);
});

const submitBid = () => {
    const amt = Number(form.bid_amount);
    const qty = Number(form.quantity);
    if (!amt || amt < minNextBid.value) {
        ElMessage.error(`Minimum next bid is ${fmtMoney(minNextBid.value)}/kg.`);
        return;
    }
    if (!qty || qty <= 0) {
        ElMessage.error('Enter a valid quantity.');
        return;
    }
    if (qty > Number(props.lot.net_weight_kg || 0)) {
        ElMessage.error(`Maximum available quantity is ${fmtNum(props.lot.net_weight_kg)} kg.`);
        return;
    }
    form.post(route('bid.store'));
};
</script>

<template>
    <DesignPreviewLayout title="Auction">
        <Head :title="lot.lot_name || 'Auction'" />

        <div class="as">
            <Link :href="route('auction.index')" class="as-back">
                <el-icon><ArrowLeft /></el-icon> Back to Auctions
            </Link>

            <!-- Header -->
            <header class="as-hero">
                <div class="as-hero__main">
                    <div class="as-hero__badges">
                        <span class="as-status" :class="`as-status--${status.tone}`">{{ status.label }}</span>
                        <span v-for="t in trust.filter((x) => x.active)" :key="t.label" class="as-trust">
                            <el-icon><component :is="t.icon" /></el-icon> {{ t.label }}
                        </span>
                    </div>
                    <h1 class="as-hero__name">{{ lot.lot_name || 'Coffee Lot' }}</h1>
                    <p class="as-hero__lot"><el-icon><Files /></el-icon> {{ lot.lot_number || '—' }}</p>
                    <p class="as-hero__meta">
                        <el-icon><Location /></el-icon> {{ lot.origin_country || '—' }}<template v-if="lot.region"> · {{ lot.region }}</template>
                        <span class="as-hero__sep">·</span> {{ lot.variety || '—' }}
                        <template v-if="lot.grade"><span class="as-hero__sep">·</span> Grade {{ lot.grade }}</template>
                    </p>
                </div>
                <div class="as-hero__stats">
                    <div class="as-hstat">
                        <span class="as-hstat__label">Quantity</span>
                        <strong class="as-hstat__value"><el-icon><Box /></el-icon> {{ fmtNum(lot.net_weight_kg) }} kg</strong>
                    </div>
                    <div class="as-hstat">
                        <span class="as-hstat__label">{{ lot.current_bid != null ? 'Current Bid' : 'Starting Bid' }}</span>
                        <strong class="as-hstat__value as-hstat__value--price"><el-icon><Coin /></el-icon> {{ fmtMoney(currentPrice) }}</strong>
                    </div>
                    <div class="as-hstat">
                        <span class="as-hstat__label">Bids</span>
                        <strong class="as-hstat__value">{{ lot.bid_count ?? 0 }}</strong>
                    </div>
                </div>
            </header>

            <div class="as-grid">
                <!-- Left column -->
                <div class="as-main">
                    <!-- Lot information -->
                    <section class="as-card">
                        <h2 class="as-card__title"><el-icon><Goods /></el-icon> Coffee Profile</h2>
                        <div class="as-spec">
                            <div class="as-spec__row"><span>Type / Variety</span><strong>{{ lot.variety || '—' }}</strong></div>
                            <div class="as-spec__row"><span>Origin</span><strong>{{ lot.origin_country || '—' }}</strong></div>
                            <div class="as-spec__row"><span>Processing</span><strong>{{ lot.process || lot.processing_method || '—' }}</strong></div>
                            <div class="as-spec__row"><span>Grade</span><strong>{{ lot.grade || '—' }}</strong></div>
                            <div class="as-spec__row"><span>Altitude</span><strong>{{ lot.altitude ? fmtNum(lot.altitude) + ' m' : '—' }}</strong></div>
                            <div class="as-spec__row"><span>Harvest Year</span><strong>{{ lot.harvest_year || '—' }}</strong></div>
                            <div class="as-spec__row"><span>Quality Score</span><strong>{{ lot.quality_score || '—' }}</strong></div>
                            <div class="as-spec__row"><span>Quantity</span><strong>{{ fmtNum(lot.net_weight_kg) }} kg</strong></div>
                        </div>
                        <Link :href="route('lot.show', lot.id)" class="as-link">View Full Lot <el-icon><Goods /></el-icon></Link>
                    </section>

                    <!-- Origin & traceability -->
                    <section class="as-card">
                        <h2 class="as-card__title"><el-icon><Stamp /></el-icon> Origin &amp; Traceability</h2>
                        <div class="as-journey">
                            <template v-for="(step, i) in journey" :key="step">
                                <div class="as-journey__step">
                                    <span class="as-journey__dot"></span>
                                    <span class="as-journey__label">{{ step }}</span>
                                </div>
                                <span v-if="i < journey.length - 1" class="as-journey__line"></span>
                            </template>
                        </div>
                        <div v-if="lot.timeline && lot.timeline.length" class="as-timeline">
                            <div v-for="(t, i) in lot.timeline" :key="i" class="as-timeline__row">
                                <span class="as-timeline__label">{{ t.label }}</span>
                                <span class="as-timeline__time">{{ t.ago || '—' }}</span>
                            </div>
                        </div>
                        <div class="as-trust-list">
                            <span v-for="t in trust" :key="t.label" class="as-trust-pill" :class="{ 'as-trust-pill--on': t.active }">
                                <el-icon><component :is="t.icon" /></el-icon> {{ t.label }}
                            </span>
                        </div>
                    </section>

                    <!-- Auction terms -->
                    <section class="as-card">
                        <h2 class="as-card__title"><el-icon><Document /></el-icon> Auction Terms</h2>
                        <div class="as-spec">
                            <div class="as-spec__row"><span>Starting Price</span><strong>{{ fmtMoney(lot.starting_price) }}</strong></div>
                            <div class="as-spec__row"><span>Current Price</span><strong>{{ fmtMoney(currentPrice) }}</strong></div>
                            <div class="as-spec__row"><span>Minimum Increment</span><strong>{{ fmtMoney(lot.min_increment) }}</strong></div>
                            <div class="as-spec__row"><span>Quantity</span><strong>{{ fmtNum(lot.net_weight_kg) }} kg</strong></div>
                            <div class="as-spec__row"><span>Packaging</span><strong>{{ lot.packaging_type || '—' }}</strong></div>
                            <div class="as-spec__row"><span>Listed</span><strong>{{ lot.listed_ago || '—' }}</strong></div>
                        </div>
                    </section>

                    <!-- Description -->
                    <section v-if="lot.description" class="as-card">
                        <h2 class="as-card__title"><el-icon><Document /></el-icon> Description</h2>
                        <p class="as-desc">{{ lot.description }}</p>
                    </section>
                </div>
                <!-- Right column -->
                <aside class="as-side">
                    <!-- Bidding panel -->
                    <section class="as-card as-card--bid">
                        <h2 class="as-card__title"><el-icon><Trophy /></el-icon> Place a Bid</h2>

                        <div class="as-bid-current">
                            <span class="as-bid-current__label">Current Bid</span>
                            <strong class="as-bid-current__value">{{ fmtMoney(currentPrice) }} <small>/ kg</small></strong>
                        </div>
                        <div class="as-bid-min">
                            <span class="as-bid-min__label">Minimum Next Bid</span>
                            <strong class="as-bid-min__value">{{ fmtMoney(minNextBid) }} <small>/ kg</small></strong>
                        </div>

                        <form v-if="canBid" class="as-bid-form" @submit.prevent="submitBid">
                            <label class="as-field">
                                <span class="as-field__label">Your Bid (per kg)</span>
                                <el-input v-model="form.bid_amount" type="number" min="0" step="0.01" />
                            </label>
                            <label class="as-field">
                                <span class="as-field__label">Quantity (kg)</span>
                                <el-input v-model="form.quantity" type="number" min="0" step="1" />
                            </label>
                            <p class="as-bid-note">You are about to place a bid of <strong>{{ fmtMoney(form.bid_amount) }}/kg</strong> for <strong>{{ fmtNum(form.quantity) }} kg</strong> of {{ lot.lot_name || 'this lot' }}.</p>
                            <button type="submit" class="as-bid-btn" :disabled="form.processing">
                                {{ form.processing ? 'Placing bid…' : 'Place Bid' }}
                            </button>
                        </form>
                        <p v-else class="as-bid-restricted">
                            Bidding is available to registered buyers. <Link :href="route('auction.index')" class="as-link">Browse other auctions</Link>.
                        </p>
                    </section>

                    <!-- Countdown / status -->
                    <section class="as-card">
                        <h2 class="as-card__title"><el-icon><Clock /></el-icon> Auction Status</h2>
                        <div class="as-status-block">
                            <span class="as-status" :class="`as-status--${status.tone}`">{{ status.label }}</span>
                            <p class="as-status-block__meta">Listed {{ lot.listed_ago || '—' }} · {{ lot.bidder_count ?? 0 }} bidders</p>
                        </div>
                    </section>

                    <!-- Seller -->
                    <section class="as-card">
                        <h2 class="as-card__title"><el-icon><User /></el-icon> Seller</h2>
                        <div class="as-seller">
                            <span class="as-seller__avatar">{{ (lot.grower || '?').charAt(0).toUpperCase() }}</span>
                            <div class="as-seller__body">
                                <strong class="as-seller__name">{{ lot.grower || 'Unknown seller' }}</strong>
                                <span class="as-seller__meta"><el-icon><Location /></el-icon> {{ lot.region || lot.origin_country || '—' }}</span>
                            </div>
                        </div>
                    </section>

                    <!-- Bid history -->
                    <section class="as-card">
                        <h2 class="as-card__title"><el-icon><Timer /></el-icon> Bid History</h2>
                        <div v-if="lot.bid_history && lot.bid_history.length" class="as-bids">
                            <div v-for="(b, i) in lot.bid_history" :key="i" class="as-bid-row" :class="{ 'as-bid-row--top': i === 0 }">
                                <span class="as-bid-row__bidder">{{ b.bidder }}</span>
                                <span class="as-bid-row__right">
                                    <strong class="as-bid-row__amount">{{ fmtMoney(b.amount) }}/kg</strong>
                                    <span class="as-bid-row__time">{{ b.placed_ago || '—' }}</span>
                                </span>
                            </div>
                        </div>
                        <p v-else class="as-empty">No bids yet — be the first to bid.</p>
                    </section>
                </aside>
            </div>
        </div>
    </DesignPreviewLayout>
</template>
<style scoped>
.as { display: flex; flex-direction: column; gap: 16px; }

.as-back { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--dp-on-surface-variant); text-decoration: none; width: fit-content; }
.as-back:hover { color: var(--dp-on-surface); }

/* Hero */
.as-hero { display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: flex-end; }
.as-hero__main { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.as-hero__badges { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
.as-hero__name { font-size: 1.6rem; line-height: 1.15; font-weight: 800; letter-spacing: -0.015em; color: var(--dp-on-surface); margin: 0; }
.as-hero__lot { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0; }
.as-hero__meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; }
.as-hero__sep { opacity: .5; }

.as-status { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; padding: 3px 10px; border-radius: 999px; }
.as-status--live { color: #16A34A; background: #E9F9EE; }
.as-status--soon { color: #92400E; background: #fef3c7; }
.as-status--neutral { color: var(--dp-on-surface-variant); background: var(--dp-surface-container-high); }
.as-trust { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); }
.as-trust .el-icon { color: var(--dp-outline); }

.as-hero__stats { display: flex; gap: 12px; }
.as-hstat { display: flex; flex-direction: column; gap: 4px; padding: 12px 16px; background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius, 6px); min-width: 130px; }
.as-hstat__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.as-hstat__value { display: inline-flex; align-items: center; gap: 6px; font-size: 16px; font-weight: 800; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }
.as-hstat__value--price { color: var(--dp-primary); }

/* Grid */
.as-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 16px; align-items: start; }
.as-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.as-side { display: flex; flex-direction: column; gap: 16px; }

/* Cards */
.as-card { background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius, 6px); padding: 18px; }
.as-card__title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-outline); margin: 0 0 14px; }

/* Spec rows */
.as-spec { display: flex; flex-direction: column; }
.as-spec__row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 9px 0; border-top: 1px solid var(--dp-outline-variant); font-size: 13px; }
.as-spec__row:first-child { border-top: none; }
.as-spec__row span { color: var(--dp-on-surface-variant); }
.as-spec__row strong { color: var(--dp-on-surface); text-align: right; font-variant-numeric: tabular-nums; }

.as-link { display: inline-flex; align-items: center; gap: 6px; margin-top: 14px; font-size: 12.5px; font-weight: 700; color: var(--dp-primary); text-decoration: none; }
.as-link:hover { text-decoration: underline; }
.as-desc { font-size: 13.5px; line-height: 1.6; color: var(--dp-on-surface-variant); margin: 0; }
/* Journey */
.as-journey { display: flex; align-items: center; flex-wrap: wrap; margin-bottom: 14px; }
.as-journey__step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.as-journey__dot { width: 8px; height: 8px; border-radius: 50%; background: var(--dp-primary); }
.as-journey__label { font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); }
.as-journey__line { width: 26px; height: 1px; background: var(--dp-outline-variant); margin: 0 4px 18px; }

.as-timeline { display: flex; flex-direction: column; margin-bottom: 14px; }
.as-timeline__row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 0; border-top: 1px solid var(--dp-outline-variant); font-size: 12.5px; }
.as-timeline__row:first-child { border-top: none; }
.as-timeline__label { color: var(--dp-on-surface); font-weight: 600; }
.as-timeline__time { color: var(--dp-on-surface-variant); }

.as-trust-list { display: flex; flex-wrap: wrap; gap: 6px; }
.as-trust-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 999px; color: var(--dp-on-surface-variant); background: var(--dp-surface-container-low); }
.as-trust-pill--on { color: #2F6B35; background: #E5FAE7; }

/* Bidding */
.as-card--bid { border-color: var(--dp-outline); }
.as-bid-current { display: flex; flex-direction: column; gap: 2px; margin-bottom: 12px; }
.as-bid-current__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.as-bid-current__value { font-size: 24px; font-weight: 800; color: var(--dp-primary); font-variant-numeric: tabular-nums; }
.as-bid-current__value small { font-size: 13px; font-weight: 600; color: var(--dp-on-surface-variant); }
.as-bid-min { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; background: var(--dp-surface-container-low); border-radius: 6px; margin-bottom: 14px; }
.as-bid-min__label { font-size: 12px; color: var(--dp-on-surface-variant); }
.as-bid-min__value { font-size: 14px; font-weight: 800; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }
.as-bid-min__value small { font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); }

.as-bid-form { display: flex; flex-direction: column; gap: 12px; }
.as-field { display: flex; flex-direction: column; gap: 5px; }
.as-field__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.as-field :deep(.el-input__wrapper) { border-radius: 6px; }
.as-bid-note { font-size: 12px; line-height: 1.5; color: var(--dp-on-surface-variant); margin: 0; }
.as-bid-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 40px;
    background: var(--dp-primary); color: var(--dp-on-primary); border: none; border-radius: 6px;
    font-size: 13.5px; font-weight: 700; cursor: pointer; font-family: inherit;
}
.as-bid-btn:hover { opacity: .9; }
.as-bid-btn:disabled { opacity: .55; cursor: not-allowed; }
.as-bid-restricted { font-size: 13px; line-height: 1.6; color: var(--dp-on-surface-variant); margin: 0; }

/* Status block */
.as-status-block { display: flex; flex-direction: column; gap: 8px; align-items: flex-start; }
.as-status-block__meta { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0; }

/* Seller */
.as-seller { display: flex; align-items: center; gap: 12px; }
.as-seller__avatar {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--dp-primary); color: var(--dp-on-primary); font-weight: 700; font-size: 15px;
}
.as-seller__body { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.as-seller__name { font-size: 13.5px; font-weight: 700; color: var(--dp-on-surface); }
.as-seller__meta { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: var(--dp-on-surface-variant); }

/* Bid history */
.as-bids { display: flex; flex-direction: column; }
.as-bid-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 0; border-top: 1px solid var(--dp-outline-variant); font-size: 12.5px; }
.as-bid-row:first-child { border-top: none; }
.as-bid-row--top { color: var(--dp-primary); }
.as-bid-row__bidder { color: var(--dp-on-surface); font-weight: 600; }
.as-bid-row__right { display: flex; align-items: center; gap: 10px; }
.as-bid-row__amount { font-weight: 800; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }
.as-bid-row--top .as-bid-row__amount { color: #16A34A; }
.as-bid-row__time { color: var(--dp-on-surface-variant); font-size: 11px; }

.as-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; }

@media (max-width: 1000px) {
    .as-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .as-hero__stats { width: 100%; }
    .as-hstat { flex: 1; min-width: 0; }
}
</style>