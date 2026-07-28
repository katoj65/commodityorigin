<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Search, MagicStick, TrendCharts, Coin, Box, UserFilled,
    Grid, Trophy, Medal, CircleCheck, Clock,
    View, Star,
    InfoFilled,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Calendar from '@/Components/Calendar.vue';

const props = defineProps({
    overview: { type: Object, default: () => ({}) },
    calendarEvents: { type: Array, default: () => [] },
    featuredLots: { type: Array, default: () => [] },
    auctionItems: { type: Array, default: () => [] },
    liveBids: { type: Array, default: () => [] },
});

/* ══════════════════════════════════════════════════════════════════════
   Top bar — AI search + quick actions
   ══════════════════════════════════════════════════════════════════════ */
const searchQuery = ref('');
const searchPrompts = [
    'Show auctions ending today',
    'Find Grade AA coffee',
    'Find lots above 86 cup score',
    'Which lot has the highest ROI',
];

function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

const quickActions = [
    { label: 'Browse Auctions', icon: Grid, action: () => scrollTo('auc-items') },
];

/* ══════════════════════════════════════════════════════════════════════
   Watch state (session-only — not persisted to the backend)
   ══════════════════════════════════════════════════════════════════════ */
const watched = ref(new Set());
function toggleWatch(id) {
    const next = new Set(watched.value);
    next.has(id) ? next.delete(id) : next.add(id);
    watched.value = next;
}

/* ══════════════════════════════════════════════════════════════════════
   Hot lots — the most contested live lots, ranked by bidder count
   ══════════════════════════════════════════════════════════════════════ */
const hotLots = computed(() => [...props.featuredLots]
    .filter((l) => l.bidder_count > 0)
    .sort((a, b) => b.bidder_count - a.bidder_count || b.bid_count - a.bid_count)
    .slice(0, 4));

/* ══════════════════════════════════════════════════════════════════════
   Display helpers
   ══════════════════════════════════════════════════════════════════════ */
const fmtMoney = (n) => (n != null ? Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');
const cap = (s) => (s ? String(s).charAt(0).toUpperCase() + String(s).slice(1) : '—');

const avatarPalette = ['#004532', '#c8862a', '#2563eb', '#7c3aed', '#0891b2', '#b45309'];
function initials(name) {
    if (!name) return '?';
    const parts = String(name).trim().split(/\s+/);
    return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase() || '?';
}
function avatarColor(name) {
    const str = String(name ?? '');
    const hash = [...str].reduce((acc, ch) => acc + ch.charCodeAt(0), 0);
    return avatarPalette[hash % avatarPalette.length];
}

/* ══════════════════════════════════════════════════════════════════════
   Items under auction — element-plus table
   ══════════════════════════════════════════════════════════════════════ */
const auctionStatusCls = (s) => ({
    live: 'auc-badge--green', pending: 'auc-badge--amber', sold: 'auc-badge--muted',
}[s] ?? 'auc-badge--muted');

const auctionQualityCls = (score) => (score >= 85 ? 'auc-badge--green' : score >= 70 ? 'auc-badge--amber' : 'auc-badge--muted');
</script>

<template>
    <AppLayout title="Coffee Auction Exchange" full-width flush :show-banner="false">
        <Head title="Coffee Auction Exchange" />

        <div class="auc-page">

            <!-- ══════════════════════════════════════════════════════════
                 Sticky top bar — AI search + quick actions
                 ══════════════════════════════════════════════════════════ -->
            <div class="auc-topbar pt-2 pb-2">
                <div class="container-fluid px-3 px-lg-4 py-2">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
                        <div class="auc-search-wrap flex-grow-1">
                            <el-icon class="auc-search-icon"><Search /></el-icon>
                            <input v-model="searchQuery" class="auc-search-input" placeholder="Ask Coffee Pulse AI…">
                            <el-icon class="auc-search-ai"><MagicStick /></el-icon>
                        </div>
                        <div class="auc-quick-actions">
                            <template v-for="qa in quickActions" :key="qa.label">
                                <Link v-if="qa.href" :href="qa.href" class="auc-qa-btn">
                                    <el-icon><component :is="qa.icon" /></el-icon> {{ qa.label }}
                                </Link>
                                <button v-else type="button" class="auc-qa-btn" @click="qa.action">
                                    <el-icon><component :is="qa.icon" /></el-icon> {{ qa.label }}
                                </button>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">
                <div class="row g-3">

                    <!-- ══════════════════════════════════════════════════
                         Main column
                         ══════════════════════════════════════════════════ -->
                    <div class="col-12 col-xl-9 order-2 order-xl-1">

                        <!-- SECTION 1 — Overview hero -->
                        <section class="auc-section">

                            <div class="row g-2 pt-0">
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Grid /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.live_auctions) }}</div><div class="auc-kpi__label">Live Auctions</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Clock /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.upcoming_lots) }}</div><div class="auc-kpi__label">Upcoming Lots</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><CircleCheck /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.completed_auctions) }}</div><div class="auc-kpi__label">Completed</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><UserFilled /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.active_buyers) }}</div><div class="auc-kpi__label">Active Buyers</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Box /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.lots_available) }}</div><div class="auc-kpi__label">Lots Available</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Coin /></el-icon><div class="auc-kpi__value">{{ fmtNum(overview.total_auction_value) }}</div><div class="auc-kpi__label">Total Auction Value</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><TrendCharts /></el-icon><div class="auc-kpi__value">{{ overview.highest_bid_today != null ? fmtNum(overview.highest_bid_today) : '—' }}</div><div class="auc-kpi__label">Highest Bid Today</div></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="auc-kpi h-100"><el-icon class="auc-kpi__icon"><Trophy /></el-icon><div class="auc-kpi__value">{{ overview.average_winning_price != null ? fmtNum(overview.average_winning_price) : '—' }}</div><div class="auc-kpi__label">Avg. Winning Price</div></div>
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 2 — Items under auction -->
                        <section id="auc-items" class="auc-section auc-section--last">
                            <div class="auc-section-head">
                                <el-icon class="auc-section-icon"><Star /></el-icon>
                                <h2 class="auc-title">Items Under Auction</h2>
                                <span class="auc-count">{{ auctionItems.length }} items</span>
                            </div>

                            <div class="auc-table-card">
                            <el-table :data="auctionItems" class="auc-el-table" stripe empty-text="No items are currently under auction.">
                                <el-table-column width="56">
                                    <template #default="{ row }">
                                        <div class="auc-thumb">
                                            <img v-if="row.image" :src="`/storage/${row.image}`" :alt="row.name">
                                            <svg v-else class="auc-thumb-icon" viewBox="0 0 24 24">
                                                <ellipse cx="9" cy="12" rx="5" ry="7" transform="rotate(-25 9 12)" fill="#4b2e1d" />
                                                <path d="M9 6 C7 9, 11 15, 9 18" stroke="#c9a27a" stroke-width="1.1" fill="none" transform="rotate(-25 9 12)" />
                                                <ellipse cx="16.5" cy="14.5" rx="4" ry="5.5" transform="rotate(20 16.5 14.5)" fill="#6b4226" />
                                                <path d="M16.5 10 C15 12.5, 18 16, 16.5 19" stroke="#d8b48f" stroke-width="1" fill="none" transform="rotate(20 16.5 14.5)" />
                                            </svg>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Lot" min-width="160">
                                    <template #header><el-icon class="auc-th-icon"><Medal /></el-icon>Lot</template>
                                    <template #default="{ row }">
                                        <div class="auc-item-name">{{ row.lot_code }}</div>
                                        <div class="auc-muted" style="font-size:.7rem;">{{ row.name }}</div>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Origin" min-width="130">
                                    <template #header><el-icon class="auc-th-icon"><Grid /></el-icon>Origin</template>
                                    <template #default="{ row }">{{ row.origin ?? '—' }}</template>
                                </el-table-column>
                                <el-table-column label="Type" min-width="100" align="center">
                                    <template #header><el-icon class="auc-th-icon"><Star /></el-icon>Type</template>
                                    <template #default="{ row }">{{ cap(row.type) }}</template>
                                </el-table-column>
                                <el-table-column label="Quality" min-width="100" align="center">
                                    <template #header><el-icon class="auc-th-icon"><Trophy /></el-icon>Quality</template>
                                    <template #default="{ row }">
                                        <span class="auc-badge" :class="auctionQualityCls(row.quality_score)">{{ row.quality_score ?? '—' }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Price / kg" min-width="110" align="right" header-align="left">
                                    <template #header><el-icon class="auc-th-icon"><Coin /></el-icon>Price / kg</template>
                                    <template #default="{ row }"><span class="auc-num">{{ fmtMoney(row.price_per_kg) }}</span></template>
                                </el-table-column>
                                <el-table-column label="Demand" min-width="100" align="center">
                                    <template #header><el-icon class="auc-th-icon"><TrendCharts /></el-icon>Demand</template>
                                    <template #default="{ row }">{{ cap(row.demand) ?? '—' }}</template>
                                </el-table-column>
                                <el-table-column label="Status" min-width="100" align="center">
                                    <template #header><el-icon class="auc-th-icon"><InfoFilled /></el-icon>Status</template>
                                    <template #default="{ row }">
                                        <span class="auc-badge" :class="auctionStatusCls(row.status)">{{ cap(row.status) }}</span>
                                    </template>
                                </el-table-column>
                            </el-table>
                            </div>
                        </section>

                    </div>

                    <!-- ══════════════════════════════════════════════════
                         Right sidebar — filters
                         ══════════════════════════════════════════════════ -->
                    <div class="col-12 col-xl-3 order-1 order-xl-2">
                        <div class="auc-sidebar">
                            <div class="auc-feed-card">
                                <div class="auc-feed-head">
                                    <div class="auc-feed-head__title">
                                        <span class="auc-live-dot"></span>
                                        <div>
                                            <div class="auc-feed-head__label">Live Auction Feed</div>
                                            <div class="auc-feed-head__sub">Real-time bidding activity</div>
                                        </div>
                                    </div>
                                    <span class="auc-feed-count">{{ liveBids.length }}</span>
                                </div>

                                <div class="auc-feed-stats">
                                    <div class="auc-feed-stat">
                                        <div class="auc-feed-stat__icon"><el-icon><UserFilled /></el-icon></div>
                                        <div>
                                            <span>Active Buyers</span>
                                            <strong>{{ fmtNum(overview.active_buyers) }}</strong>
                                        </div>
                                    </div>
                                    <div class="auc-feed-stat">
                                        <div class="auc-feed-stat__icon auc-feed-stat__icon--gold"><el-icon><TrendCharts /></el-icon></div>
                                        <div>
                                            <span>Highest Bid Today</span>
                                            <strong>{{ overview.highest_bid_today != null ? fmtMoney(overview.highest_bid_today) : '—' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="!liveBids.length" class="auc-feed-empty">
                                    <div class="auc-feed-empty__icon"><el-icon><Coin /></el-icon></div>
                                    <p>No bids yet</p>
                                    <span>Activity will appear here the moment someone bids.</span>
                                </div>
                                <div v-else class="auc-feed">
                                    <div
                                        v-for="(bid, i) in liveBids"
                                        :key="bid.id"
                                        class="auc-feed-row"
                                        :class="{ 'auc-feed-row--new': i === 0 }"
                                    >
                                        <div class="auc-feed-row__avatar" :style="{ background: avatarColor(bid.bidder) }">{{ initials(bid.bidder) }}</div>
                                        <div class="auc-feed-row__body">
                                            <div class="auc-feed-row__top">
                                                <strong>{{ bid.bidder }}</strong> bid on <strong>{{ bid.lot_number }}</strong>
                                            </div>
                                            <div class="auc-muted">{{ bid.placed_ago }} · {{ fmtNum(bid.quantity) }} kg</div>
                                        </div>
                                        <div class="auc-feed-row__end">
                                            <strong class="auc-feed-row__amount">{{ fmtMoney(bid.amount) }}</strong>
                                            <span class="auc-badge" :class="bid.status === 'pending' ? 'auc-badge--amber' : 'auc-badge--green'">{{ cap(bid.status) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="auc-card mt-3">
                                <div class="auc-card-title mb-2"><el-icon class="auc-card-icon"><TrendCharts /></el-icon> Hot Lots</div>
                                <p v-if="!hotLots.length" class="auc-empty mb-0">No bidding activity yet.</p>
                                <div v-for="(lot, i) in hotLots" :key="lot.id" class="auc-hot-row">
                                    <span class="auc-hot-rank">{{ i + 1 }}</span>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="auc-item-name">{{ lot.lot_number }}</div>
                                        <div class="auc-muted" style="font-size:.6875rem;">{{ cap(lot.origin_country) }}</div>
                                    </div>
                                    <div class="auc-hot-row__end">
                                        <strong>{{ fmtMoney(lot.current_bid ?? lot.starting_price) }}</strong>
                                        <span>{{ lot.bidder_count }} bidder{{ lot.bidder_count === 1 ? '' : 's' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="auc-card mt-3">
                                <Calendar :events="calendarEvents" title="My Calendar" />
                            </div>

                            <div class="auc-card mt-3">
                                <div class="auc-card-title mb-2"><el-icon class="auc-card-icon"><View /></el-icon> Watchlist</div>
                                <p v-if="!watched.size" class="auc-empty mb-0">You're not watching any lots yet.</p>
                                <div v-for="lot in featuredLots.filter((l) => watched.has(l.id))" :key="lot.id" class="auc-spec">
                                    <span>{{ lot.lot_number }}</span><strong>{{ fmtMoney(lot.current_bid ?? lot.starting_price) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.auc-page {
    --green: #004532;
    --green-dark: #002e20;
    --gold: #c8862a;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #fff;
    color: var(--on-surface);
    min-height: 100%;
}
.auc-muted { color: var(--on-surface-var); font-size: .8125rem; }
.auc-item-name { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.auc-empty { font-size: .8125rem; color: var(--on-surface-var); text-align: center; padding: 1.5rem 0; }

/* ── Top bar ─────────────────────────────────────────────────────────── */
.auc-topbar { position: sticky; top: 0; z-index: 40; background: #fff; border-bottom: 1px solid var(--border); }
.auc-search-wrap { position: relative; display: flex; align-items: center; }
.auc-search-icon { position: absolute; left: 12px; color: var(--on-surface-var); font-size: 14px; }
.auc-search-ai { position: absolute; right: 12px; color: var(--gold); font-size: 14px; }
.auc-search-input { width: 100%; height: 38px; border: 1px solid var(--border); border-radius: 10px; padding: 0 36px; font-size: .8125rem; outline: none; background: var(--surface-low); }
.auc-search-input:focus { border-color: var(--green); background: #fff; }
.auc-search-wrap--table { flex: 1; min-width: 220px; }
.auc-search-wrap--table .auc-search-input { height: 34px; background: #fff; }

.auc-search-prompts { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
.auc-prompt-chip { font-size: .6875rem; padding: 3px 10px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface-var); cursor: pointer; white-space: nowrap; }
.auc-prompt-chip:hover { border-color: var(--green); color: var(--green); }

.auc-quick-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.auc-qa-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: 7px 12px; border-radius: 8px; background: var(--surface-low); border: 1px solid var(--border); color: var(--on-surface); cursor: pointer; white-space: nowrap; text-decoration: none; }
.auc-qa-btn:hover { background: #eef2f1; border-color: var(--green); }
.auc-qa-btn--active { background: var(--green); border-color: var(--green); color: #fff; }

/* ── Sections ────────────────────────────────────────────────────────── */
.auc-section { padding: 1.25rem 0; border-bottom: 1px solid var(--border); }
.auc-section--last { border-bottom: none; }
.auc-section-head { display: flex; align-items: center; gap: 8px; margin-bottom: .875rem; }
.auc-section-icon { width: 28px; height: 28px; border-radius: 8px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.auc-title { font-size: 1rem; font-weight: 800; color: var(--on-surface); margin: 0; }
.auc-count { margin-left: auto; font-size: .75rem; font-weight: 600; color: var(--on-surface-var); }

.auc-live-dot { width: 10px; height: 10px; border-radius: 50%; background: #dc2626; box-shadow: 0 0 0 rgba(220,38,38,.5); animation: auc-pulse 1.6s infinite; }
@keyframes auc-pulse { 0% { box-shadow: 0 0 0 0 rgba(220,38,38,.5); } 70% { box-shadow: 0 0 0 8px rgba(220,38,38,0); } 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0); } }

/* ── Hero banner ─────────────────────────────────────────────────────── */
.auc-hero-banner { display: flex; align-items: flex-start; gap: 12px; background: linear-gradient(135deg, rgba(0,69,50,.06), rgba(200,134,42,.06)); border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; }
.auc-hero-banner__icon { width: 34px; height: 34px; border-radius: 9px; background: var(--green); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.auc-hero-banner__kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--green); margin-bottom: 3px; }
.auc-hero-banner__text { font-size: .8438rem; color: var(--on-surface); line-height: 1.5; }

/* ── KPI ─────────────────────────────────────────────────────────────── */
.auc-kpi { border: 1px solid var(--border); border-radius: 10px; padding: .875rem; background: #fff; text-align: center; }
.auc-kpi__icon { color: var(--green); font-size: 16px; margin-bottom: 4px; }
.auc-kpi__value { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); line-height: 1.1; }
.auc-kpi__label { font-size: .625rem; font-weight: 600; color: var(--on-surface-var); margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }

/* ── Cards ───────────────────────────────────────────────────────────── */
.auc-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem; }
.auc-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: .8438rem; font-weight: 700; color: var(--on-surface); }
.auc-card-icon { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

.auc-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; }
.auc-btn-primary:hover { background: var(--green-dark); color: #fff; }
.auc-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
.auc-btn-outline:hover { background: var(--surface-low); }
.auc-btn-outline--active { background: var(--green); border-color: var(--green); color: #fff; }

.auc-sidebar { position: sticky; top: 90px; display: flex; flex-direction: column; gap: 0; }

/* ── Live auction feed — modern activity-stream card ──────────────────── */
.auc-feed-card {
    display: flex; flex-direction: column;
    background: #fff; border: 1px solid var(--border); border-radius: 12px;
    padding: 1rem 1rem 0.25rem;
    position: relative; overflow: hidden;
}

.auc-feed-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .875rem; }
.auc-feed-head__title { display: flex; align-items: center; gap: 9px; }
.auc-feed-head__label { font-size: .8438rem; font-weight: 800; color: var(--on-surface); letter-spacing: -.01em; }
.auc-feed-head__sub { font-size: .6875rem; color: var(--on-surface-var); margin-top: 1px; }
.auc-feed-count { font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); border: 1px solid var(--border); border-radius: 999px; padding: 2px 10px; flex-shrink: 0; }

.auc-feed-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 1rem; }
.auc-feed-stat { background: var(--surface-low); border-radius: 10px; padding: 9px 10px; display: flex; align-items: center; gap: 8px; }
.auc-feed-stat__icon { width: 28px; height: 28px; border-radius: 8px; background: rgba(0,69,50,0.1); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.auc-feed-stat__icon--gold { background: rgba(200,134,42,0.14); color: var(--gold); }
.auc-feed-stat span { display: block; font-size: .5938rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); }
.auc-feed-stat strong { display: block; font-family: 'IBM Plex Mono', monospace; font-size: .8125rem; font-weight: 700; color: var(--on-surface); margin-top: 1px; }

.auc-feed-empty { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 1.75rem .5rem 2rem; }
.auc-feed-empty__icon { width: 40px; height: 40px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; font-size: 17px; margin-bottom: 10px; }
.auc-feed-empty p { font-size: .8125rem; font-weight: 700; color: var(--on-surface); margin: 0; }
.auc-feed-empty span { font-size: .75rem; color: var(--on-surface-var); margin-top: 2px; }

.auc-feed { display: flex; flex-direction: column; max-height: 380px; overflow-y: auto; margin: 0 -1rem; padding: 0 1rem 0.5rem; scrollbar-width: thin; scrollbar-color: var(--border) transparent; }
.auc-feed::-webkit-scrollbar { width: 5px; }
.auc-feed::-webkit-scrollbar-thumb { background: var(--border); border-radius: 999px; }

.auc-feed-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--surface-low); transition: background-color .15s ease; }
.auc-feed-row:last-child { border-bottom: none; }
.auc-feed-row:hover { background: var(--surface-low); }
.auc-feed-row--new { animation: auc-feed-flash 1.8s ease-out; }
@keyframes auc-feed-flash {
    0% { background-color: rgba(0,69,50,.1); }
    100% { background-color: transparent; }
}

.auc-feed-row__avatar { width: 32px; height: 32px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .6875rem; font-weight: 700; letter-spacing: .02em; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
.auc-feed-row__body { flex: 1; min-width: 0; }
.auc-feed-row__top { font-size: .8125rem; color: var(--on-surface); line-height: 1.4; }
.auc-feed-row__top strong { font-weight: 700; }
.auc-feed-row__end { display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0; }
.auc-feed-row__amount { font-family: 'IBM Plex Mono', monospace; font-size: .8125rem; font-weight: 700; color: var(--green); white-space: nowrap; }

/* ── Element Plus table, reskinned to match the exchange design system ── */
.auc-num { font-variant-numeric: tabular-nums; }
.auc-table-card { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.auc-el-table { --el-table-border-color: var(--border); --el-table-header-bg-color: var(--surface-low); --el-table-header-text-color: var(--on-surface-var); --el-table-row-hover-bg-color: #f3f6f5; --el-table-text-color: var(--on-surface); font-family: inherit; }
.auc-el-table :deep(.el-table__cell) { padding: 11px 0; }
.auc-el-table :deep(.cell) { padding: 0 12px; font-size: .8125rem; line-height: 1.45; }
.auc-el-table :deep(th.el-table__cell) { font-size: .6875rem; font-weight: 600; letter-spacing: .04em; }
.auc-el-table :deep(th.el-table__cell .cell) { display: flex; align-items: center; white-space: nowrap; }
.auc-el-table :deep(.el-table__cell:first-child .cell) { padding-left: 1rem; }
.auc-el-table :deep(.el-table__cell:last-child .cell) { padding-right: 1rem; }
.auc-el-table :deep(.el-table__inner-wrapper::before) { display: none; }
.auc-el-table :deep(.el-table__body td.el-table__cell) { transition: background-color .12s ease; }
.auc-el-table :deep(.el-table__body tr.el-table__row--striped td.el-table__cell) { background: #fafaf9; }
.auc-el-table :deep(.el-table__body tr:hover > td.el-table__cell) { background: var(--el-table-row-hover-bg-color); }
.auc-el-table :deep(.el-table__empty-block) { min-height: 140px; }
.auc-el-table :deep(.el-table__empty-text) { color: var(--on-surface-var); font-size: .8125rem; }
.auc-th-icon { width: 14px; height: 14px; margin-right: 5px; color: var(--green); opacity: .8; }

.auc-thumb { width: 32px; height: 32px; border-radius: 9px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f1e6d8; border: 1px solid #e6d5bf; }
.auc-thumb img { width: 100%; height: 100%; object-fit: cover; }
.auc-thumb-icon { width: 22px; height: 22px; }

/* ── Spec rows ───────────────────────────────────────────────────────── */
.auc-spec { display: flex; align-items: center; justify-content: space-between; font-size: .75rem; color: var(--on-surface-var); padding: 5px 0; border-bottom: 1px solid var(--surface-low); }
.auc-spec:last-child { border-bottom: none; }
.auc-spec strong { color: var(--on-surface); font-weight: 700; }

/* ── Hot lots ────────────────────────────────────────────────────────── */
.auc-hot-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.auc-hot-row:last-child { border-bottom: none; }
.auc-hot-rank { width: 20px; height: 20px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); font-size: .625rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.auc-hot-row__end { text-align: right; flex-shrink: 0; }
.auc-hot-row__end strong { display: block; font-family: 'IBM Plex Mono', monospace; font-size: .8125rem; color: var(--green); }
.auc-hot-row__end span { display: block; font-size: .6875rem; color: var(--on-surface-var); }

/* ── Badges ──────────────────────────────────────────────────────────── */
.auc-badge { display: inline-flex; border-radius: 999px; font-size: .625rem; font-weight: 700; padding: 3px 9px; white-space: nowrap; }
.auc-badge--green { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.auc-badge--amber { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.auc-badge--blue { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.auc-badge--muted { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

@media (max-width: 1199.98px) {
    .auc-sidebar { position: static; }
}
</style>
