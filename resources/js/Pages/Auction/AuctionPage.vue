<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Box, CircleCheck, Clock, Coin, Files, Goods, Location, Medal, Search, Timer, Trophy, User } from '@element-plus/icons-vue';

const props = defineProps({
    overview: { type: Object, default: () => ({}) },
    featuredLots: { type: Array, default: () => [] },
    endingSoon: { type: Array, default: () => [] },
    upcoming: { type: Array, default: () => [] },
    myBids: { type: Array, default: () => [] },
    myAuctions: { type: Array, default: () => [] },
    liveBids: { type: Array, default: () => [] },
});

const search = ref('');
const sort = ref('featured');
const statusFilter = ref('all');

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');

const statusLabel = (s) => {
    const map = { live: 'Live', draft: 'Upcoming', ready: 'Live', listing_ready: 'Live', tokenisation_ready: 'Live', ended: 'Ended', awarded: 'Awarded' };
    return map[s] || (s || '—').replace(/_/g, ' ');
};

const matches = (lot) => {
    const q = search.value.trim().toLowerCase();
    if (!q) return true;
    return [lot.lot_name, lot.lot_number, lot.origin_country, lot.region, lot.variety, lot.grade, lot.process]
        .filter(Boolean).some((v) => String(v).toLowerCase().includes(q));
};

const sortLots = (list) => {
    const arr = [...list];
    switch (sort.value) {
        case 'price_desc':
            arr.sort((a, b) => (b.current_bid ?? b.starting_price ?? 0) - (a.current_bid ?? a.starting_price ?? 0));
            break;
        case 'price_asc':
            arr.sort((a, b) => (a.current_bid ?? a.starting_price ?? 0) - (b.current_bid ?? b.starting_price ?? 0));
            break;
        case 'bids':
            arr.sort((a, b) => (b.bid_count ?? 0) - (a.bid_count ?? 0));
            break;
        case 'quality':
            arr.sort((a, b) => (b.quality_score ?? 0) - (a.quality_score ?? 0));
            break;
        default:
            break;
    }
    return arr;
};

const visible = (list) => sortLots(list.filter(matches).filter((lot) => {
    if (statusFilter.value === 'all') return true;
    if (statusFilter.value === 'upcoming') return lot.status === 'draft';
    return lot.status !== 'draft';
}));

const liveLots = computed(() => visible(props.featuredLots));
const soonLots = computed(() => visible(props.endingSoon));
const upcomingLots = computed(() => visible(props.upcoming));
const myAuctionLots = computed(() => visible(props.myAuctions));
const myBidRows = computed(() => props.myBids.filter((b) => {
    const q = search.value.trim().toLowerCase();
    if (!q) return true;
    return [b.lot_number, b.status].filter(Boolean).some((v) => String(v).toLowerCase().includes(q));
}));

const overviewStats = computed(() => [
    { label: 'Live Auctions', value: props.overview.live_auctions ?? 0, icon: Trophy },
    { label: 'Upcoming', value: props.overview.upcoming_lots ?? 0, icon: Clock },
    { label: 'Active Buyers', value: props.overview.active_buyers ?? 0, icon: User },
    { label: 'Highest Bid Today', value: props.overview.highest_bid_today != null ? fmtMoney(props.overview.highest_bid_today) : '—', icon: Coin },
]);
</script>

<template>
    <DesignPreviewLayout title="Auctions">
        <Head title="Auctions" />

        <div class="auc">
            <!-- Page header -->
            <header class="auc-header">
                <div class="auc-header__main">
                    <h1 class="auc-header__title">Auctions</h1>
                    <p class="auc-header__subtitle">Discover and bid on verified coffee lots available through competitive price discovery.</p>
                </div>
              
            </header>

            <!-- Overview strip -->
            <div class="auc-stats">
                <div v-for="s in overviewStats" :key="s.label" class="auc-stat">
                    <el-icon class="auc-stat__icon"><component :is="s.icon" /></el-icon>
                    <div class="auc-stat__body">
                        <span class="auc-stat__label">{{ s.label }}</span>
                        <strong class="auc-stat__value">{{ s.value }}</strong>
                    </div>
                </div>
            </div>

            <!-- Live Auctions -->
            <section class="auc-section">
                <div class="auc-section__head">
                    <h2 class="auc-section__title"><el-icon><Trophy /></el-icon> Live Auctions</h2>
                    <span class="auc-section__count">{{ liveLots.length }}</span>
                </div>
                <div v-if="liveLots.length" class="auc-table-wrap">
                    <table class="auc-table">
                        <thead>
                            <tr>
                                <th><span class="auc-th"><el-icon><Files /></el-icon> Lot</span></th>
                                <th><span class="auc-th"><el-icon><Location /></el-icon> Origin</span></th>
                                <th><span class="auc-th"><el-icon><Goods /></el-icon> Variety</span></th>
                                <th><span class="auc-th"><el-icon><Medal /></el-icon> Grade</span></th>
                                <th><span class="auc-th"><el-icon><Box /></el-icon> Quantity</span></th>
                                <th><span class="auc-th"><el-icon><Coin /></el-icon> Starting Bid</span></th>
                                <th><span class="auc-th"><el-icon><Coin /></el-icon> Current Bid</span></th>
                                <th><span class="auc-th"><el-icon><Trophy /></el-icon> Bids</span></th>
                                <th><span class="auc-th"><el-icon><CircleCheck /></el-icon> Status</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lot in liveLots" :key="lot.id">
                                <td>
                                    <div class="auc-lot-cell">
                                        <strong class="auc-lot-cell__name">{{ lot.lot_name || lot.lot_number }}</strong>
                                        <span class="auc-lot-cell__num">{{ lot.lot_number || '—' }}</span>
                                    </div>
                                </td>
                                <td>{{ lot.origin_country || '—' }}</td>
                                <td>{{ lot.variety || '—' }}</td>
                                <td>{{ lot.grade || '—' }}</td>
                                <td class="auc-mono">{{ fmtNum(lot.net_weight_kg) }} kg</td>
                                <td class="auc-mono">{{ fmtMoney(lot.starting_price) }}</td>
                                <td class="auc-mono auc-mono--strong">{{ fmtMoney(lot.current_bid ?? lot.starting_price) }}</td>
                                <td>{{ lot.bid_count ?? 0 }}</td>
                                <td><span class="auc-badge auc-badge--live">{{ statusLabel(lot.status) }}</span></td>
                                <td><Link :href="route('auction.show', lot.id)" class="auc-view-link">View <el-icon><Goods /></el-icon></Link></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="auc-empty">No live auctions match your filters.</p>
            </section>

            <!-- Ending Soon -->
            <section class="auc-section">
                <div class="auc-section__head">
                    <h2 class="auc-section__title"><el-icon><Timer /></el-icon> Ending Soon</h2>
                    <span class="auc-section__count">{{ soonLots.length }}</span>
                </div>
                <div v-if="soonLots.length" class="auc-table-wrap">
                    <table class="auc-table">
                        <thead>
                            <tr>
                                <th><span class="auc-th"><el-icon><Files /></el-icon> Lot</span></th>
                                <th><span class="auc-th"><el-icon><Location /></el-icon> Origin</span></th>
                                <th><span class="auc-th"><el-icon><Goods /></el-icon> Variety</span></th>
                                <th><span class="auc-th"><el-icon><Medal /></el-icon> Grade</span></th>
                                <th><span class="auc-th"><el-icon><Box /></el-icon> Quantity</span></th>
                                <th><span class="auc-th"><el-icon><Coin /></el-icon> Starting Bid</span></th>
                                <th><span class="auc-th"><el-icon><Coin /></el-icon> Current Bid</span></th>
                                <th><span class="auc-th"><el-icon><Trophy /></el-icon> Bids</span></th>
                                <th><span class="auc-th"><el-icon><CircleCheck /></el-icon> Status</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lot in soonLots" :key="lot.id">
                                <td>
                                    <div class="auc-lot-cell">
                                        <strong class="auc-lot-cell__name">{{ lot.lot_name || lot.lot_number }}</strong>
                                        <span class="auc-lot-cell__num">{{ lot.lot_number || '—' }}</span>
                                    </div>
                                </td>
                                <td>{{ lot.origin_country || '—' }}</td>
                                <td>{{ lot.variety || '—' }}</td>
                                <td>{{ lot.grade || '—' }}</td>
                                <td class="auc-mono">{{ fmtNum(lot.net_weight_kg) }} kg</td>
                                <td class="auc-mono">{{ fmtMoney(lot.starting_price) }}</td>
                                <td class="auc-mono auc-mono--strong">{{ fmtMoney(lot.current_bid ?? lot.starting_price) }}</td>
                                <td>{{ lot.bid_count ?? 0 }}</td>
                                <td><span class="auc-badge auc-badge--soon">Ending Soon</span></td>
                                <td><Link :href="route('auction.show', lot.id)" class="auc-view-link">View <el-icon><Goods /></el-icon></Link></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="auc-empty">No lots are ending soon.</p>
            </section>

            <!-- Upcoming -->
            <section class="auc-section">
                <div class="auc-section__head">
                    <h2 class="auc-section__title"><el-icon><Clock /></el-icon> Upcoming</h2>
                    <span class="auc-section__count">{{ upcomingLots.length }}</span>
                </div>
                <div v-if="upcomingLots.length" class="auc-list">
                    <div v-for="lot in upcomingLots" :key="lot.id" class="auc-list__row">
                        <div class="auc-list__main">
                            <span class="auc-list__name">{{ lot.lot_name || lot.lot_number }}</span>
                            <span class="auc-list__meta">{{ lot.origin_country }} · {{ lot.variety || '—' }} · {{ lot.grade || '—' }}</span>
                        </div>
                        <strong class="auc-list__value">{{ fmtMoney(lot.starting_price) }}</strong>
                        <Link :href="route('auction.show', lot.id)" class="auc-view-link">
                            View Auction <el-icon><Goods /></el-icon>
                        </Link>
                    </div>
                </div>
                <p v-else class="auc-empty">No upcoming auctions.</p>
            </section>

            <!-- My Bids -->
            <section class="auc-section">
                <div class="auc-section__head">
                    <h2 class="auc-section__title"><el-icon><Trophy /></el-icon> My Bids</h2>
                    <span class="auc-section__count">{{ myBidRows.length }}</span>
                </div>
                <div v-if="myBidRows.length" class="auc-table-wrap">
                    <table class="auc-table">
                        <thead>
                            <tr>
                                <th><span class="auc-th"><el-icon><Files /></el-icon> Lot</span></th>
                                <th><span class="auc-th"><el-icon><Coin /></el-icon> Amount</span></th>
                                <th><span class="auc-th"><el-icon><Box /></el-icon> Quantity</span></th>
                                <th><span class="auc-th"><el-icon><CircleCheck /></el-icon> Status</span></th>
                                <th><span class="auc-th"><el-icon><Clock /></el-icon> Placed</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="bid in myBidRows" :key="bid.id">
                                <td><Link :href="route('auction.show', bid.lot_id)" class="auc-table-link">{{ bid.lot_number }}</Link></td>
                                <td class="auc-mono">{{ fmtMoney(bid.amount) }}</td>
                                <td>{{ fmtNum(bid.quantity) }} kg</td>
                                <td><span class="auc-badge" :class="bid.status === 'pending' ? 'auc-badge--pending' : ''">{{ statusLabel(bid.status) }}</span></td>
                                <td>{{ bid.placed_ago || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="auc-empty">You haven't placed any bids yet.</p>
            </section>

            <!-- My Auctions -->
            <section class="auc-section">
                <div class="auc-section__head">
                    <h2 class="auc-section__title"><el-icon><Trophy /></el-icon> My Auctions</h2>
                    <span class="auc-section__count">{{ myAuctionLots.length }}</span>
                </div>
                <div v-if="myAuctionLots.length" class="auc-table-wrap">
                    <table class="auc-table">
                        <thead>
                            <tr>
                                <th><span class="auc-th"><el-icon><Files /></el-icon> Lot</span></th>
                                <th><span class="auc-th"><el-icon><Location /></el-icon> Origin</span></th>
                                <th><span class="auc-th"><el-icon><Goods /></el-icon> Variety</span></th>
                                <th><span class="auc-th"><el-icon><Medal /></el-icon> Grade</span></th>
                                <th><span class="auc-th"><el-icon><Box /></el-icon> Quantity</span></th>
                                <th><span class="auc-th"><el-icon><Coin /></el-icon> Starting Bid</span></th>
                                <th><span class="auc-th"><el-icon><Coin /></el-icon> Current Bid</span></th>
                                <th><span class="auc-th"><el-icon><Trophy /></el-icon> Bids</span></th>
                                <th><span class="auc-th"><el-icon><CircleCheck /></el-icon> Status</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lot in myAuctionLots" :key="lot.id">
                                <td>
                                    <div class="auc-lot-cell">
                                        <strong class="auc-lot-cell__name">{{ lot.lot_name || lot.lot_number }}</strong>
                                        <span class="auc-lot-cell__num">{{ lot.lot_number || '—' }}</span>
                                    </div>
                                </td>
                                <td>{{ lot.origin_country || '—' }}</td>
                                <td>{{ lot.variety || '—' }}</td>
                                <td>{{ lot.grade || '—' }}</td>
                                <td class="auc-mono">{{ fmtNum(lot.net_weight_kg) }} kg</td>
                                <td class="auc-mono">{{ fmtMoney(lot.starting_price) }}</td>
                                <td class="auc-mono auc-mono--strong">{{ fmtMoney(lot.current_bid ?? lot.starting_price) }}</td>
                                <td>{{ lot.bid_count ?? 0 }}</td>
                                <td><span class="auc-badge" :class="lot.status === 'draft' ? '' : 'auc-badge--live'">{{ statusLabel(lot.status) }}</span></td>
                                <td><Link :href="route('auction.show', lot.id)" class="auc-view-link">View <el-icon><Goods /></el-icon></Link></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="auc-empty">You haven't listed any auctions.</p>
            </section>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.auc { display: flex; flex-direction: column; gap: 18px; }

/* Header */
.auc-header { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
.auc-header__title { font-size: 1.5rem; line-height: 1.2; font-weight: 800; letter-spacing: -0.015em; color: var(--dp-on-surface); margin: 0; }
.auc-header__subtitle { font-size: 13.5px; line-height: 1.5; color: var(--dp-on-surface-variant); margin: 6px 0 0; max-width: 620px; }
.auc-header__controls { display: flex; gap: 10px; flex-wrap: wrap; }
.auc-search { width: 240px; }
.auc-select { width: 170px; }
.auc-search :deep(.el-input__wrapper),
.auc-select :deep(.el-input__wrapper) { border-radius: 6px; }

/* Overview strip */
.auc-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.auc-stat {
    display: flex; align-items: center; gap: 12px;
    background: var(--dp-surface);
    border: 1px solid var(--dp-outline-variant);
    border-radius: var(--dp-card-radius, 6px);
    padding: 14px 16px;
}
.auc-stat__icon { font-size: 20px; color: var(--dp-outline); flex-shrink: 0; }
.auc-stat__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.auc-stat__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.auc-stat__value { font-size: 16px; font-weight: 800; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }

/* Live table cells */
.auc-lot-cell { display: flex; flex-direction: column; gap: 2px; }
.auc-lot-cell__name { font-size: 13.5px; font-weight: 700; color: var(--dp-on-surface); }
.auc-lot-cell__num { font-size: 11.5px; color: var(--dp-on-surface-variant); }
.auc-mono--strong { font-weight: 800; color: var(--dp-on-surface); }

/* Sections */
.auc-section {
    background: var(--dp-surface);
    border: 1px solid var(--dp-outline-variant);
    border-radius: var(--dp-card-radius, 6px);
    padding: 18px;
}
.auc-section__head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.auc-section__title {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    color: var(--dp-outline); margin: 0;
}
.auc-section__count {
    font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant);
    background: var(--dp-surface-container-high); padding: 1px 8px; border-radius: 999px;
}

/* List (upcoming) */
.auc-list { display: flex; flex-direction: column; }
.auc-list__row { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-top: 1px solid var(--dp-outline-variant); }
.auc-list__row:first-child { border-top: none; }
.auc-list__main { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
.auc-list__name { font-size: 14px; font-weight: 600; color: var(--dp-on-surface); }
.auc-list__meta { font-size: 12px; color: var(--dp-on-surface-variant); }
.auc-list__value { font-size: 14px; font-weight: 700; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }
.auc-view-link {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    font-size: 12.5px; font-weight: 700; color: var(--dp-primary); text-decoration: none;
}
.auc-view-link:hover { text-decoration: underline; }

/* Table */
.auc-table-wrap { overflow-x: auto; }
.auc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.auc-table th {
    text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    color: var(--dp-on-surface-variant); padding: 8px 12px; border-bottom: 1px solid var(--dp-outline-variant);
    white-space: nowrap;
}
.auc-th { display: inline-flex; align-items: center; gap: 5px; }
.auc-th .el-icon { font-size: 13px; }
.auc-table td { padding: 11px 12px; border-bottom: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface); }
.auc-table tr:last-child td { border-bottom: none; }
.auc-table-link { color: var(--dp-on-surface); font-weight: 600; text-decoration: none; }
.auc-table-link:hover { color: var(--dp-primary); text-decoration: underline; }
.auc-mono { font-variant-numeric: tabular-nums; }

/* Badges */
.auc-badge {
    display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    color: var(--dp-on-surface-variant); background: var(--dp-surface-container-high);
    padding: 2px 8px; border-radius: 999px; white-space: nowrap;
}
.auc-badge--pending { color: #92400E; background: #fef3c7; }
.auc-badge--live { color: #16A34A; background: #E9F9EE; }
.auc-badge--soon { color: #92400E; background: #fef3c7; }

.auc-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; padding: 8px 0; }

@media (max-width: 1100px) {
    .auc-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .auc-stats { grid-template-columns: 1fr 1fr; }
    .auc-header__controls { width: 100%; }
    .auc-search { flex: 1; width: auto; }
}
</style>
