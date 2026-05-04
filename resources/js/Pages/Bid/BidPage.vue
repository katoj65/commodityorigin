<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Tickets, TrendCharts, Clock, Medal, Trophy,
    CollectionTag, Star, Filter, DataLine, User,
    ArrowUp, Warning,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const activeFilter = ref('Arabica');
const filters = ['Arabica', 'Robusta', 'Micro-lots', 'Ending Soon'];

const featuredLot = {
    id: 'UG-SIPI-2024-08',
    title: 'Mount Elgon Sipi Falls',
    origin: 'Kapchorwa District, Uganda',
    process: 'Washed Arabica',
    score: '87.5',
    currentBid: '$4.85/lb',
    reserve: '$4.70/lb',
    bidders: '14 desks',
    closes: '02h 45m',
};

const tableMetrics = [
    { label: 'Live lots',     value: '5',      tone: 'default', icon: Tickets },
    { label: 'Fastest close', value: '42m 10s', tone: 'danger',  icon: Clock },
    { label: 'Top score',     value: '89.2',    tone: 'success', icon: Star },
];

const auctionLots = [
    {
        id: 'UG-SIPI-2024-08',
        estate: 'Mount Elgon Sipi Falls',
        location: 'Kapchorwa District, Uganda',
        type: 'Washed Arabica',
        typeTone: 'amber',
        score: '87.5',
        bid: '$4.85/lb',
        movement: '+0.15',
        movementUp: true,
        reserve: 'Reserve met',
        reserveTone: 'success',
        timeLeft: '02h 45m',
        timeUrgent: true,
    },
    {
        id: 'ET-YRG-2024-12',
        estate: 'Yirgacheffe Chelelektu',
        location: 'Gedeo Zone, Ethiopia',
        type: 'Natural Process',
        typeTone: 'green',
        score: '89.2',
        bid: '$6.12/lb',
        movement: 'No recent bids',
        movementUp: false,
        reserve: 'Premium lot',
        reserveTone: 'info',
        timeLeft: '14h 12m',
        timeUrgent: false,
    },
    {
        id: 'CO-HUL-2024-03',
        estate: 'Huila Finca La Reserva',
        location: 'Pitalito, Colombia',
        type: 'Honey Process',
        typeTone: 'amber',
        score: '88.0',
        bid: '$5.20/lb',
        movement: '+0.05',
        movementUp: true,
        reserve: 'Reserve met',
        reserveTone: 'success',
        timeLeft: '01d 04h',
        timeUrgent: false,
    },
    {
        id: 'CR-TAR-2024-21',
        estate: 'Tarrazu Don Mayo',
        location: 'San Marcos, Costa Rica',
        type: 'Washed Arabica',
        typeTone: 'amber',
        score: '86.8',
        bid: '$4.40/lb',
        movement: 'Stable',
        movementUp: false,
        reserve: 'Closing fast',
        reserveTone: 'danger',
        timeLeft: '42m 10s',
        timeUrgent: true,
    },
    {
        id: 'BR-MIN-2024-55',
        estate: 'Minas Gerais Fazenda',
        location: 'Sul de Minas, Brazil',
        type: 'Specialty Robusta',
        typeTone: 'blue',
        score: '84.2',
        bid: '$2.95/lb',
        movement: '+0.02',
        movementUp: true,
        reserve: 'Open bid',
        reserveTone: 'neutral',
        timeLeft: '03d 19h',
        timeUrgent: false,
    },
];

const bidLadder = [
    { time: '14:02', bidder: 'Export House A',     amount: '$4.85/lb', state: 'Leading',       leading: true },
    { time: '13:58', bidder: 'Roaster Union',       amount: '$4.81/lb', state: 'Outbid',        leading: false },
    { time: '13:47', bidder: 'Kampala Trader Co.',  amount: '$4.76/lb', state: 'Reserve met',   leading: false },
    { time: '13:39', bidder: 'Origin Buyer Desk',   amount: '$4.70/lb', state: 'Opening mark',  leading: false },
];

const watchlist = [
    { lot: 'Rwenzori Select',    note: 'Aggressive bidding in the last 20 minutes',      tone: 'success' },
    { lot: 'Bugisu AA Reserve',  note: 'High score lot with reserve already cleared',    tone: 'amber' },
    { lot: 'Kisoro Honey Lot',   note: 'Thin ladder, likely to move on next bid',        tone: 'info' },
];

const footerStats = [
    { label: 'Market liquidity',  value: '$12.4M',  icon: DataLine },
    { label: 'Avg cup score',     value: '86.4',    icon: Star },
    { label: 'Bidders online',    value: '1,402',   icon: User },
    { label: 'Avg close cycle',   value: '4.2 Days', icon: Clock },
];
</script>

<template>
    <AppLayout title="Bid Board" full-width flush :show-banner="false">
        <Head title="Bid Board" />

        <div class="bp-root">

            <!-- ── Hero ─────────────────────────────────────────────────── -->
            <section class="bp-hero">
                <div class="bp-hero__inner">
                    <div class="bp-hero__left">
                        <h1 class="bp-hero__title">Bid Board</h1>
                        <p class="bp-hero__sub">Review active lots, bid velocity, and reserve status before committing capital.</p>
                    </div>
                    <div class="bp-filter-row">
                        <el-icon class="bp-filter-icon"><Filter /></el-icon>
                        <button
                            v-for="f in filters"
                            :key="f"
                            class="bp-filter-chip"
                            :class="{ 'bp-filter-chip--active': activeFilter === f }"
                            @click="activeFilter = f"
                        >{{ f }}</button>
                        <button class="bp-filter-chip bp-filter-chip--ghost">
                            <el-icon><Filter /></el-icon> More
                        </button>
                    </div>
                </div>
            </section>

            <!-- ── Body ─────────────────────────────────────────────────── -->
            <div class="bp-body">
                <div class="bp-grid">

                    <!-- ── Main column ──────────────────────────────────── -->
                    <div class="bp-main">

                        <!-- Table card -->
                        <div class="bp-card bp-table-card">

                            <!-- Card header -->
                            <div class="bp-card-head">
                                <div class="bp-card-head__left">
                                    <div>
                                        <div class="bp-eyebrow">Trading Table</div>
                                        <h2 class="bp-card-title">Live Auction Windows</h2>
                                        <p class="bp-card-sub">Monitor lead bids, reserve thresholds, and close pressure in one desk-ready board.</p>
                                    </div>
                                </div>
                                <div class="bp-metrics-row">
                                    <div v-for="m in tableMetrics" :key="m.label" class="bp-metric">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                            <span class="bp-metric__label">{{ m.label }}</span>
                                            <el-icon class="bp-metric__icon" :class="`bp-metric__icon--${m.tone}`"><component :is="m.icon" /></el-icon>
                                        </div>
                                        <div class="bp-metric__value" :class="`bp-metric__value--${m.tone}`">{{ m.value }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Featured lot strip -->
                            <div class="bp-featured">
                                <div class="bp-featured__left">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="bp-lead-badge">
                                            <el-icon><Trophy /></el-icon> Lead Window
                                        </span>
                                        <span class="bp-lot-id">{{ featuredLot.id }}</span>
                                    </div>
                                    <div class="bp-featured__title">{{ featuredLot.title }}</div>
                                    <div class="bp-featured__sub">{{ featuredLot.origin }} · {{ featuredLot.process }}</div>
                                </div>
                                <div class="bp-featured__stats">
                                    <div class="bp-fstat">
                                        <span class="bp-fstat__label">Current Bid</span>
                                        <strong class="bp-fstat__val">{{ featuredLot.currentBid }}</strong>
                                    </div>
                                    <div class="bp-fstat">
                                        <span class="bp-fstat__label">Reserve</span>
                                        <strong class="bp-fstat__val">{{ featuredLot.reserve }}</strong>
                                    </div>
                                    <div class="bp-fstat">
                                        <span class="bp-fstat__label">Cup Score</span>
                                        <strong class="bp-fstat__val bp-fstat__val--green">{{ featuredLot.score }}</strong>
                                    </div>
                                    <div class="bp-fstat">
                                        <span class="bp-fstat__label">Closes In</span>
                                        <strong class="bp-fstat__val bp-fstat__val--red">
                                            <el-icon><Clock /></el-icon> {{ featuredLot.closes }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Auction table -->
                            <div class="bp-table-wrap">
                                <table class="bp-table">
                                    <thead>
                                        <tr>
                                            <th>Lot</th>
                                            <th>Profile</th>
                                            <th class="text-center">Score</th>
                                            <th class="text-end">Current Bid</th>
                                            <th class="text-center">Reserve</th>
                                            <th class="text-center">Time Left</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="lot in auctionLots" :key="lot.id" class="bp-row">
                                            <td class="bp-td">
                                                <div class="d-flex align-items-start gap-2">
                                                    <span class="bp-status-dot" :class="lot.timeUrgent ? 'bp-status-dot--urgent' : 'bp-status-dot--ok'"></span>
                                                    <div>
                                                        <div class="bp-lot-id">{{ lot.id }}</div>
                                                        <div class="bp-estate">{{ lot.estate }}</div>
                                                        <div class="bp-location">{{ lot.location }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="bp-td">
                                                <span class="bp-type-badge" :class="`bp-type-badge--${lot.typeTone}`">{{ lot.type }}</span>
                                            </td>
                                            <td class="bp-td text-center">
                                                <div class="bp-score">{{ lot.score }}</div>
                                                <div class="bp-score-label">cup score</div>
                                            </td>
                                            <td class="bp-td text-end">
                                                <div class="bp-bid">{{ lot.bid }}</div>
                                                <span class="bp-movement" :class="lot.movementUp ? 'bp-movement--up' : 'bp-movement--flat'">
                                                    <el-icon v-if="lot.movementUp"><ArrowUp /></el-icon>
                                                    {{ lot.movement }}
                                                </span>
                                            </td>
                                            <td class="bp-td text-center">
                                                <span class="bp-reserve-badge" :class="`bp-reserve-badge--${lot.reserveTone}`">{{ lot.reserve }}</span>
                                            </td>
                                            <td class="bp-td text-center">
                                                <span class="bp-time" :class="lot.timeUrgent ? 'bp-time--urgent' : ''">
                                                    <el-icon><Clock /></el-icon> {{ lot.timeLeft }}
                                                </span>
                                            </td>
                                            <td class="bp-td text-end">
                                                <button class="bp-bid-btn">
                                                    <el-icon><Medal /></el-icon> Bid
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Footer stats -->
                        <div class="bp-stats-bar">
                            <div v-for="stat in footerStats" :key="stat.label" class="bp-stat">
                                <span class="bp-stat__icon"><el-icon><component :is="stat.icon" /></el-icon></span>
                                <div>
                                    <div class="bp-stat__label">{{ stat.label }}</div>
                                    <div class="bp-stat__value">{{ stat.value }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Sidebar ───────────────────────────────────────── -->
                    <aside class="bp-sidebar">

                        <!-- Portfolio card -->
                        <div class="bp-card">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="bp-eyebrow">Desk Summary</div>
                                    <h3 class="bp-card-title mt-1">Quick Portfolio</h3>
                                </div>
                                <span class="bp-sidebar-icon"><el-icon><TrendCharts /></el-icon></span>
                            </div>

                            <div class="bp-portfolio-grid">
                                <div class="bp-portfolio-stat">
                                    <div class="bp-portfolio-stat__label">Active Bids</div>
                                    <div class="bp-portfolio-stat__value">12 <span>lots</span></div>
                                </div>
                                <div class="bp-portfolio-stat bp-portfolio-stat--win">
                                    <div class="bp-portfolio-stat__label">Winning</div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="bp-portfolio-stat__value bp-portfolio-stat__value--green">08</div>
                                        <span class="bp-top-badge">Top bid</span>
                                    </div>
                                </div>
                                <div class="bp-portfolio-stat">
                                    <div class="bp-portfolio-stat__label">Total Exposure</div>
                                    <div class="bp-portfolio-stat__value">$248,500</div>
                                </div>
                            </div>

                            <button class="bp-manage-btn mt-3">Manage All Bids</button>
                        </div>

                        <!-- Bid ladder -->
                        <div class="bp-card">
                            <div class="bp-eyebrow">Lead Ladder</div>
                            <h3 class="bp-card-title mt-1 mb-3">Bid Movement</h3>

                            <div class="bp-ladder">
                                <div
                                    v-for="entry in bidLadder"
                                    :key="`${entry.time}-${entry.bidder}`"
                                    class="bp-ladder-entry"
                                    :class="{ 'bp-ladder-entry--leading': entry.leading }"
                                >
                                    <div class="bp-ladder-entry__dot" :class="entry.leading ? 'bp-ladder-entry__dot--green' : ''"></div>
                                    <div class="bp-ladder-entry__body">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <span class="bp-ladder-bidder">{{ entry.bidder }}</span>
                                            <span class="bp-ladder-amount">{{ entry.amount }}</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-2 mt-1">
                                            <span class="bp-ladder-time">{{ entry.time }}</span>
                                            <span class="bp-ladder-state" :class="entry.leading ? 'bp-ladder-state--green' : ''">{{ entry.state }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Watchlist -->
                        <div class="bp-card bp-watchlist-card">
                            <div class="bp-eyebrow bp-eyebrow--amber">Watchlist Pulse</div>
                            <h3 class="bp-card-title mt-1 mb-3">What Needs Attention</h3>

                            <div class="bp-watch-list">
                                <div
                                    v-for="item in watchlist"
                                    :key="item.lot"
                                    class="bp-watch-item"
                                >
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <span class="bp-watch-name">{{ item.lot }}</span>
                                        <span class="bp-watch-badge" :class="`bp-watch-badge--${item.tone}`">
                                            <el-icon><Warning /></el-icon> Watch
                                        </span>
                                    </div>
                                    <p class="bp-watch-note">{{ item.note }}</p>
                                </div>
                            </div>
                        </div>

                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.bp-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #111827;
    --on-surface-var:   #6b7280;
    --surface-white:    #ffffff;
    --surface-low:      #f8fafc;
    --surface-mid:      #f1f5f9;
    --surface-high:     #e5e7eb;
    --success:          #166534;
    --success-bg:       #dcfce7;
    --danger:           #b91c1c;
    --danger-bg:        #fee2e2;
    --amber:            #92400e;
    --amber-bg:         #fef3c7;
    --info:             #1e40af;
    --info-bg:          #dbeafe;
    --inner-pad:        2rem;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface-white);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.bp-hero {
    border-bottom: 1px solid var(--surface-high);
    padding: 1.25rem 0;
}
.bp-hero__inner {
    padding: 0 var(--inner-pad);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.bp-hero__title {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    margin: 0 0 0.2rem;
    line-height: 1.2;
}
.bp-hero__sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.5;
    max-width: 500px;
}

/* Filter chips */
.bp-filter-row    { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.bp-filter-icon   { color: var(--on-surface-var); font-size: 14px; }
.bp-filter-chip {
    display: inline-flex; align-items: center; gap: 5px;
    border: 1px solid var(--surface-high);
    border-radius: 999px;
    background: var(--surface-white);
    padding: 5px 14px;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--on-surface-var);
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease, border-color 0.12s ease;
}
.bp-filter-chip:hover             { background: var(--surface-low); }
.bp-filter-chip--active           { background: var(--primary); border-color: var(--primary); color: var(--on-primary); }
.bp-filter-chip--ghost            { border-style: dashed; }

/* ── Body ──────────────────────────────────────────────────────────────────── */
.bp-body { padding: 1.5rem var(--inner-pad) 3rem; }

.bp-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 300px;
    gap: 1.25rem;
    align-items: start;
}

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.bp-card {
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 0.875rem;
    padding: 1.25rem;
}
.bp-table-card { padding: 0; overflow: hidden; }

/* ── Card header ───────────────────────────────────────────────────────────── */
.bp-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.25rem;
    padding: 1.25rem;
    background: var(--surface-low);
    border-bottom: 1px solid var(--surface-high);
    flex-wrap: wrap;
}
.bp-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--on-surface-var);
    margin-bottom: 4px;
}
.bp-eyebrow--amber { color: var(--amber); }
.bp-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--on-surface);
    margin: 0;
    line-height: 1.3;
}
.bp-card-sub {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    margin: 4px 0 0;
    line-height: 1.5;
    max-width: 500px;
}

/* Metrics row */
.bp-metrics-row { display: flex; gap: 0.75rem; flex-shrink: 0; }
.bp-metric {
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 0.625rem;
    padding: 10px 14px;
    min-width: 110px;
}
.bp-metric__label { font-size: 0.6875rem; font-weight: 600; color: var(--on-surface-var); text-transform: uppercase; letter-spacing: 0.06em; }
.bp-metric__icon  { font-size: 13px; }
.bp-metric__icon--default { color: var(--on-surface-var); }
.bp-metric__icon--success { color: var(--success); }
.bp-metric__icon--danger  { color: var(--danger); }
.bp-metric__value { font-size: 1rem; font-weight: 800; color: var(--on-surface); margin-top: 6px; line-height: 1; }
.bp-metric__value--success { color: var(--success); }
.bp-metric__value--danger  { color: var(--danger); }

/* ── Featured lot strip ────────────────────────────────────────────────────── */
.bp-featured {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.25rem;
    padding: 1.25rem;
    border-bottom: 1px solid var(--surface-high);
    flex-wrap: wrap;
}
.bp-lead-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--primary);
    color: var(--on-primary);
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 700;
    padding: 4px 12px;
}
.bp-lot-id {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: var(--on-surface-var);
    text-transform: uppercase;
}
.bp-featured__title { font-size: 1.0625rem; font-weight: 700; color: var(--on-surface); margin: 6px 0 2px; }
.bp-featured__sub   { font-size: 0.8125rem; color: var(--on-surface-var); }
.bp-featured__stats { display: flex; gap: 0.75rem; flex-shrink: 0; flex-wrap: wrap; }
.bp-fstat {
    background: var(--surface-low);
    border: 1px solid var(--surface-high);
    border-radius: 0.625rem;
    padding: 10px 16px;
    min-width: 100px;
}
.bp-fstat__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--on-surface-var); display: block; margin-bottom: 6px; }
.bp-fstat__val {
    font-size: 1.0625rem;
    font-weight: 800;
    color: var(--on-surface);
    display: flex;
    align-items: center;
    gap: 5px;
}
.bp-fstat__val--green { color: var(--success); }
.bp-fstat__val--red   { color: var(--danger); }

/* ── Auction Table ─────────────────────────────────────────────────────────── */
.bp-table-wrap { overflow-x: auto; }
.bp-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}
.bp-table thead tr { background: var(--surface-low); }
.bp-table th {
    padding: 10px 14px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
    border-bottom: 1px solid var(--surface-high);
    white-space: nowrap;
}
.bp-row {
    border-bottom: 1px solid var(--surface-low);
    transition: background 0.1s ease;
}
.bp-row:last-child { border-bottom: none; }
.bp-row:hover { background: #fafbfc; }
.bp-td { padding: 13px 14px; vertical-align: middle; }

.bp-status-dot {
    width: 8px; height: 8px;
    border-radius: 50%; flex-shrink: 0; margin-top: 5px;
}
.bp-status-dot--urgent { background: #fca5a5; }
.bp-status-dot--ok     { background: #86efac; }

.bp-estate   { font-size: 0.875rem; font-weight: 600; color: var(--on-surface); margin: 4px 0 2px; }
.bp-location { font-size: 0.75rem; color: var(--on-surface-var); }

.bp-type-badge {
    display: inline-flex; align-items: center;
    border-radius: 999px; font-size: 0.6875rem; font-weight: 700;
    padding: 3px 10px; white-space: nowrap;
}
.bp-type-badge--amber  { background: var(--amber-bg); color: var(--amber); }
.bp-type-badge--green  { background: #d1fae5; color: #065f46; }
.bp-type-badge--blue   { background: var(--info-bg); color: var(--info); }

.bp-score       { font-size: 1rem; font-weight: 800; color: var(--success); line-height: 1; }
.bp-score-label { font-size: 0.625rem; color: var(--on-surface-var); margin-top: 3px; }

.bp-bid { font-size: 1rem; font-weight: 800; color: var(--on-surface); line-height: 1; }
.bp-movement {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 0.6875rem; font-weight: 600;
    border-radius: 999px; padding: 2px 8px; margin-top: 4px;
}
.bp-movement--up   { background: #d1fae5; color: var(--success); }
.bp-movement--flat { background: var(--surface-mid); color: var(--on-surface-var); }

.bp-reserve-badge {
    display: inline-flex; align-items: center;
    border-radius: 999px; font-size: 0.6875rem; font-weight: 700;
    padding: 3px 10px; white-space: nowrap;
}
.bp-reserve-badge--success { background: var(--success-bg); color: var(--success); }
.bp-reserve-badge--danger  { background: var(--danger-bg); color: var(--danger); }
.bp-reserve-badge--info    { background: var(--info-bg); color: var(--info); }
.bp-reserve-badge--neutral { background: var(--surface-mid); color: var(--on-surface-var); }

.bp-time {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.8125rem; font-weight: 600; color: var(--on-surface-var);
}
.bp-time--urgent { color: var(--danger); }

.bp-bid-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--primary); color: var(--on-primary);
    border: none; border-radius: 6px;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.75rem; font-weight: 700;
    padding: 6px 12px; cursor: pointer;
    transition: opacity 0.15s ease;
    white-space: nowrap;
}
.bp-bid-btn:hover { opacity: 0.88; }

/* ── Footer stats bar ──────────────────────────────────────────────────────── */
.bp-stats-bar {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0;
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 0.875rem;
    overflow: hidden;
    margin-top: 1.25rem;
}
.bp-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.25rem;
    border-right: 1px solid var(--surface-high);
}
.bp-stat:last-child { border-right: none; }
.bp-stat__icon {
    width: 36px; height: 36px; border-radius: 8px;
    background: rgba(0,69,50,0.07); color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.bp-stat__label { font-size: 0.6875rem; font-weight: 600; color: var(--on-surface-var); margin-bottom: 2px; }
.bp-stat__value { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); line-height: 1; }

/* ── Sidebar ───────────────────────────────────────────────────────────────── */
.bp-sidebar { display: flex; flex-direction: column; gap: 1.25rem; }

.bp-sidebar-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: rgba(0,69,50,0.07); color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}

/* Portfolio */
.bp-portfolio-grid { display: flex; flex-direction: column; gap: 0.625rem; }
.bp-portfolio-stat {
    background: var(--surface-low);
    border-radius: 0.625rem;
    padding: 12px 14px;
}
.bp-portfolio-stat--win { background: #f0fdf4; }
.bp-portfolio-stat__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--on-surface-var); margin-bottom: 6px; }
.bp-portfolio-stat__value { font-size: 1.625rem; font-weight: 800; color: var(--on-surface); line-height: 1; }
.bp-portfolio-stat__value span { font-size: 0.875rem; font-weight: 400; color: var(--on-surface-var); }
.bp-portfolio-stat__value--green { color: var(--success); }
.bp-top-badge {
    background: var(--success-bg); color: var(--success);
    border-radius: 999px; font-size: 0.6875rem; font-weight: 700;
    padding: 3px 10px;
}
.bp-manage-btn {
    width: 100%; background: var(--primary); color: var(--on-primary);
    border: none; border-radius: 6px;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem; font-weight: 700;
    padding: 10px 0; cursor: pointer;
    transition: opacity 0.15s ease;
}
.bp-manage-btn:hover { opacity: 0.88; }

/* Bid ladder */
.bp-ladder { display: flex; flex-direction: column; gap: 0; }
.bp-ladder-entry {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 0;
    border-bottom: 1px solid var(--surface-low);
}
.bp-ladder-entry:last-child { border-bottom: none; padding-bottom: 0; }
.bp-ladder-entry--leading { }
.bp-ladder-entry__dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px;
    background: var(--surface-high);
}
.bp-ladder-entry__dot--green { background: #4ade80; }
.bp-ladder-entry__body { flex: 1; min-width: 0; }
.bp-ladder-bidder { font-size: 0.875rem; font-weight: 600; color: var(--on-surface); }
.bp-ladder-amount { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); }
.bp-ladder-time   { font-size: 0.6875rem; color: var(--on-surface-var); }
.bp-ladder-state  { font-size: 0.6875rem; font-weight: 600; color: var(--on-surface-var); }
.bp-ladder-state--green { color: var(--success); }

/* Watchlist */
.bp-watchlist-card { background: linear-gradient(160deg, #fffbeb, #fff); }
.bp-watch-list { display: flex; flex-direction: column; gap: 0.75rem; }
.bp-watch-item {
    background: var(--surface-white);
    border: 1px solid #f1e7d3;
    border-radius: 0.625rem;
    padding: 12px;
}
.bp-watch-name  { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.bp-watch-note  { font-size: 0.75rem; color: var(--on-surface-var); line-height: 1.5; margin: 0; }
.bp-watch-badge {
    display: inline-flex; align-items: center; gap: 4px;
    border-radius: 999px; font-size: 0.625rem; font-weight: 700;
    padding: 3px 10px; white-space: nowrap;
}
.bp-watch-badge--success { background: var(--success-bg); color: var(--success); }
.bp-watch-badge--amber   { background: var(--amber-bg);   color: var(--amber); }
.bp-watch-badge--info    { background: var(--info-bg);    color: var(--info); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .bp-grid { grid-template-columns: 1fr; }
    .bp-sidebar { flex-direction: row; flex-wrap: wrap; }
    .bp-sidebar > .bp-card { flex: 1; min-width: 260px; }
    .bp-stats-bar { grid-template-columns: repeat(2, 1fr); }
    .bp-stat:nth-child(2) { border-right: none; }
    .bp-stat:nth-child(3) { border-top: 1px solid var(--surface-high); }
    .bp-stat:nth-child(4) { border-top: 1px solid var(--surface-high); border-right: none; }
}
@media (max-width: 900px) {
    .bp-root { --inner-pad: 1.25rem; }
    .bp-card-head { flex-direction: column; }
    .bp-metrics-row { flex-direction: row; }
    .bp-featured { flex-direction: column; }
    .bp-featured__stats { flex-direction: row; flex-wrap: wrap; }
    .bp-hero__inner { flex-direction: column; align-items: flex-start; gap: 1rem; }
}
@media (max-width: 640px) {
    .bp-root { --inner-pad: 1rem; }
    .bp-stats-bar { grid-template-columns: 1fr 1fr; }
    .bp-stat { border-right: none; border-bottom: 1px solid var(--surface-high); }
    .bp-stat:nth-child(3), .bp-stat:nth-child(4) { border-top: none; }
    .bp-metrics-row { flex-wrap: wrap; }
}
</style>
