<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    bid:     { type: Object, default: () => ({}) },
    lot:     { type: Object, default: () => ({}) },
    similar: { type: Array,  default: () => []   },
});

/* ── Countdown ──────────────────────────────────────────────────────────── */
const secondsLeft = ref(47 * 3600 + 32 * 60 + 15);
let timer = null;
onMounted(() => { timer = setInterval(() => { if (secondsLeft.value > 0) secondsLeft.value--; }, 1000); });
onUnmounted(() => clearInterval(timer));
const countdown = computed(() => {
    const s = secondsLeft.value;
    return {
        days:    String(Math.floor(s / 86400)).padStart(2, '0'),
        hours:   String(Math.floor((s % 86400) / 3600)).padStart(2, '0'),
        minutes: String(Math.floor((s % 3600) / 60)).padStart(2, '0'),
        seconds: String(s % 60).padStart(2, '0'),
    };
});

/* ── Bid data ───────────────────────────────────────────────────────────── */
const bidRef      = computed(() => props.bid?.reference   || 'BID-2026-00847');
const auctionId   = computed(() => props.bid?.auction_id  || 'AUC-2026-014');
const bidPrice    = computed(() => Number(props.bid?.bid_amount  || 12.40));
const quantity    = computed(() => Number(props.bid?.quantity    || 300));
const totalVal    = computed(() => (bidPrice.value * quantity.value).toLocaleString('en-US', { minimumFractionDigits: 2 }));
const submittedAt = computed(() => props.bid?.submitted_at || new Date().toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' }));

/* ── Lot data ───────────────────────────────────────────────────────────── */
const lotName      = computed(() => props.lot?.lot_name      || 'Bugisu Premium Export Lot');
const lotCode      = computed(() => props.lot?.lot_code      || 'LOT-2026-001');
const origin       = computed(() => props.lot?.origin        || 'Mbale, Uganda');
const coffeeType   = computed(() => props.lot?.variety       || props.lot?.type || 'Arabica – Bourbon');
const qualityScore = computed(() => Number(props.lot?.quality_score || 87.5).toFixed(1));
const netWeight    = computed(() => Number(props.lot?.net_weight_kg  || 1200).toLocaleString());
const lotImage     = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVNPRKcnvtgsayf1-HlE1xA92LWW1C56Io3VMreh4aujnZTgd7RVNEZOyEqFGcffC6O3JdFFEczJbLDdWYhY3SPZ_97Ep-mSdEA6EpSHOYxQ4YC-9rWllkkDGEgrkRhX8fdY9yD34FR8UBs42K4RgVHEi6OXDt4QvP-hJgG1uWAZlyFMQ7HCYg9NcS7oQW5HysDvCK3FiXBDRpfkupmdW5tIy7o5GV8ZL8feaXnYtU6ZpDEAJvS_XKRffdezzJJCSUQeF2AHlDDapn';

/* ── Static data ────────────────────────────────────────────────────────── */
const timeline = [
    { label: 'Bid Submitted',       sub: 'Your bid is live on the auction.',   done: true,  active: false },
    { label: 'Bid Under Review',    sub: 'System validates your submission.',  done: false, active: true  },
    { label: 'Auction Continues',   sub: 'Competing bids may arrive.',         done: false, active: false },
    { label: 'Auction Closes',      sub: 'Final bids are locked in.',          done: false, active: false },
    { label: 'Winner Notification', sub: 'Result sent to all participants.',   done: false, active: false },
];

const notifications = [
    { label: 'Outbid by another buyer',  tone: 'warn'    },
    { label: 'Auction closing soon',     tone: 'warn'    },
    { label: 'Your bid wins',            tone: 'success' },
    { label: 'Bid expires',              tone: 'danger'  },
];

const insights = [
    { text: 'Your bid is competitive at current market levels.',    variant: 'success' },
    { text: 'Demand for Arabica specialty lots remains strong.',    variant: 'info'    },
    { text: 'Similar lots recently sold above your current bid.',   variant: 'warning' },
];

const similarLots = computed(() => props.similar.length ? props.similar : [
    { id: 2, code: 'LOT-2026-002', name: 'Sipi Falls Washed AA',  origin: 'Kapchorwa, Uganda', price: '11.80', score: '86.2' },
    { id: 3, code: 'LOT-2026-003', name: 'Mt. Elgon Natural',     origin: 'Mbale, Uganda',     price: '12.20', score: '85.5' },
    { id: 4, code: 'LOT-2026-004', name: 'Rwenzori Highland',     origin: 'Kasese, Uganda',    price: '13.00', score: '88.0' },
]);

const fabOpen = ref(false);
</script>

<template>
    <AppLayout title="Bid Submitted" full-width flush :show-banner="false">
        <div class="bs-root">

            <!-- ── Success Hero ────────────────────────────────────────────── -->
            <section class="bs-hero">
                <div class="bs-hero-inner text-center">
                    <div class="bs-success-ring mx-auto mb-4">
                        <div class="bs-success-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                    </div>
                    <h1 class="bs-hero-title">Bid Successfully Submitted</h1>
                    <p class="bs-hero-sub">Your bid has been recorded and is now participating in the auction.</p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <span class="bs-badge bs-badge--green">Live Auction</span>
                        <span class="bs-badge">Ref: {{ bidRef }}</span>
                        <span class="bs-badge">{{ auctionId }}</span>
                    </div>
                </div>
            </section>

            <!-- ── Body ───────────────────────────────────────────────────── -->
            <div class="bs-body">
                <div class="row g-4">

                    <!-- Left: Bid Summary + Lot Snapshot -->
                    <div class="col-lg-4 col-12">

                        <!-- Bid Summary -->
                        <div class="bs-card mb-4">
                            <div class="bs-card-body">
                                <h6 class="bs-card-title">Bid Summary</h6>
                                <table class="bs-table w-100">
                                    <tbody>
                                        <tr><td>Reference</td><td><strong>{{ bidRef }}</strong></td></tr>
                                        <tr><td>Auction ID</td><td><strong>{{ auctionId }}</strong></td></tr>
                                        <tr><td>Lot</td><td><strong>{{ lotCode }}</strong></td></tr>
                                        <tr><td>Bid Price</td><td><strong class="bs-accent">Shs. {{ bidPrice.toFixed(2) }}/kg</strong></td></tr>
                                        <tr><td>Quantity</td><td><strong>{{ quantity }} kg</strong></td></tr>
                                        <tr><td>Total Value</td><td><strong class="bs-accent">Shs. {{ totalVal }}</strong></td></tr>
                                        <tr><td>Submitted</td><td><strong>{{ submittedAt }}</strong></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Lot Snapshot -->
                        <div class="bs-card">
                            <img :src="lot.image || lotImage" :alt="lotName" class="bs-lot-hero-img" />
                            <div class="bs-card-body">
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="bs-badge bs-badge--green">Verified</span>
                                    <span class="bs-badge">Export Ready</span>
                                    <span class="bs-badge bs-badge--warm">Tokenised</span>
                                </div>
                                <h6 class="bs-lot-name">{{ lotName }}</h6>
                                <p class="bs-lot-meta">{{ origin }} &middot; {{ coffeeType }}</p>
                                <div class="bs-lot-kv-grid">
                                    <div class="bs-lot-kv">
                                        <span>Quality Score</span>
                                        <strong class="bs-accent">{{ qualityScore }} SCA</strong>
                                    </div>
                                    <div class="bs-lot-kv">
                                        <span>Available</span>
                                        <strong>{{ netWeight }} kg</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Auction Status + Timeline + Notifications/Intelligence -->
                    <div class="col-lg-8">

                        <!-- Auction Status KPIs + Countdown -->
                        <div class="bs-card mb-4">
                            <div class="bs-card-body">
                                <h6 class="bs-card-title mb-3">Auction Status</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-6 col-sm-3">
                                        <div class="bs-kpi">
                                            <span class="bs-kpi-label">Your Ranking</span>
                                            <strong class="bs-kpi-val bs-accent">#2</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bs-kpi">
                                            <span class="bs-kpi-label">Highest Bid</span>
                                            <strong class="bs-kpi-val">Shs. 12.80/kg</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bs-kpi">
                                            <span class="bs-kpi-label">Your Bid</span>
                                            <strong class="bs-kpi-val">Shs. {{ bidPrice.toFixed(2) }}/kg</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="bs-kpi">
                                            <span class="bs-kpi-label">Gap to Top</span>
                                            <strong class="bs-kpi-val bs-warn">−Shs. 0.40/kg</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Countdown -->
                                <p class="bs-countdown-label">Auction closes in</p>
                                <div class="bs-countdown">
                                    <div class="bs-cd-unit">
                                        <span class="bs-cd-num">{{ countdown.days }}</span>
                                        <span class="bs-cd-lbl">Days</span>
                                    </div>
                                    <span class="bs-cd-sep">:</span>
                                    <div class="bs-cd-unit">
                                        <span class="bs-cd-num">{{ countdown.hours }}</span>
                                        <span class="bs-cd-lbl">Hours</span>
                                    </div>
                                    <span class="bs-cd-sep">:</span>
                                    <div class="bs-cd-unit">
                                        <span class="bs-cd-num">{{ countdown.minutes }}</span>
                                        <span class="bs-cd-lbl">Mins</span>
                                    </div>
                                    <span class="bs-cd-sep">:</span>
                                    <div class="bs-cd-unit">
                                        <span class="bs-cd-num">{{ countdown.seconds }}</span>
                                        <span class="bs-cd-lbl">Secs</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- What's Next Timeline -->
                        <div class="bs-card mb-4">
                            <div class="bs-card-body">
                                <h6 class="bs-card-title mb-4">What Happens Next</h6>
                                <div class="bs-timeline">
                                    <div v-for="(step, i) in timeline" :key="i"
                                        class="bs-tl-step"
                                        :class="{ 'bs-tl-step--done': step.done, 'bs-tl-step--active': step.active }">
                                        <div class="bs-tl-node">
                                            <svg v-if="step.done" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span v-else>{{ i + 1 }}</span>
                                        </div>
                                        <div v-if="i < timeline.length - 1" class="bs-tl-line"></div>
                                        <div class="bs-tl-body">
                                            <strong>{{ step.label }}</strong>
                                            <span>{{ step.sub }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications + Intelligence -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="bs-card h-100">
                                    <div class="bs-card-body">
                                        <h6 class="bs-card-title mb-1">Auction Notifications</h6>
                                        <p class="bs-card-sub mb-3">You will be notified when:</p>
                                        <div class="bs-notif-list">
                                            <div v-for="n in notifications" :key="n.label" class="bs-notif-row">
                                                <span class="bs-notif-dot" :class="`bs-notif-dot--${n.tone}`"></span>
                                                <span>{{ n.label }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bs-card h-100">
                                    <div class="bs-card-body">
                                        <h6 class="bs-card-title mb-3">Bid Intelligence</h6>
                                        <div class="bs-intel-list">
                                            <div v-for="ins in insights" :key="ins.text"
                                                class="alert mb-2 py-2 px-3"
                                                :class="`alert-${ins.variant}`"
                                                style="font-size:.8125rem;border-radius:8px;">
                                                {{ ins.text }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Recommended Actions -->
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <h6 class="bs-section-title mb-3">Recommended Actions</h6>
                    </div>
                    <div class="col-md-4">
                        <div class="bs-card h-100 bs-action-card">
                            <div class="bs-card-body">
                                <div class="bs-action-icon bs-action-icon--green mb-3">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </div>
                                <h6 class="bs-action-title">Monitor Auction</h6>
                                <p class="bs-action-sub">Track your bid performance and ranking in real time.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bs-card h-100 bs-action-card">
                            <div class="bs-card-body">
                                <div class="bs-action-icon bs-action-icon--amber mb-3">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="17 11 12 6 7 11"/><polyline points="17 18 12 13 7 18"/></svg>
                                </div>
                                <h6 class="bs-action-title">Increase Bid</h6>
                                <p class="bs-action-sub">Improve your ranking if auction activity increases.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bs-card h-100 bs-action-card">
                            <div class="bs-card-body">
                                <div class="bs-action-icon bs-action-icon--blue mb-3">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </div>
                                <h6 class="bs-action-title">Explore Similar Lots</h6>
                                <p class="bs-action-sub">Find alternative opportunities in the market.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main CTA Buttons -->
                <div class="bs-cta-row mt-4">
                    <Link :href="route('bid.index')" class="bs-btn bs-btn--primary">View Auction Status</Link>
                    <Link :href="route('bid.index')" class="bs-btn bs-btn--outline">My Active Bids</Link>
                    <Link :href="route('market.index')" class="bs-btn bs-btn--outline">Explore More Lots</Link>
                    <Link :href="route('market.live')" class="bs-btn bs-btn--ghost">Return to Market</Link>
                </div>

                <!-- Similar Opportunities -->
                <div class="mt-5 pb-5">
                    <h6 class="bs-section-title mb-3">Similar Opportunities</h6>
                    <div class="bs-similar-row">
                        <div v-for="sl in similarLots" :key="sl.code" class="bs-card bs-similar-card">
                            <img :src="sl.image || lotImage" :alt="sl.name" class="bs-similar-img rounded-top-4" />
                            <div class="bs-card-body bs-card-body--sm">
                                <p class="bs-similar-code">{{ sl.code }}</p>
                                <h6 class="bs-similar-name">{{ sl.name }}</h6>
                                <p class="bs-similar-meta">{{ sl.origin }}</p>
                                <div class="bs-similar-stats">
                                    <div>
                                        <span class="bs-kpi-label">Bid from</span>
                                        <strong class="bs-accent d-block">Shs. {{ sl.price }}/kg</strong>
                                    </div>
                                    <div>
                                        <span class="bs-kpi-label">Score</span>
                                        <strong class="d-block">{{ sl.score }} SCA</strong>
                                    </div>
                                </div>
                                <Link :href="route('bid.place', sl.id)" class="bs-btn bs-btn--primary bs-btn--sm w-100 mt-2">Place Bid</Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── FAB ──────────────────────────────────────────────────────────── -->
        <div class="bs-fab-wrap">
            <transition name="bs-fab-fade">
                <div v-if="fabOpen" class="bs-fab-menu">
                    <button class="bs-fab-item">Contact Seller</button>
                    <button class="bs-fab-item">Ask Market Advisor</button>
                </div>
            </transition>
            <button class="bs-fab" @click="fabOpen = !fabOpen" aria-label="Quick actions">
                <svg v-if="!fabOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

    </AppLayout>
</template>

<style scoped>
.bs-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #191c1e;
    --on-surface-var:   #74777a;
    --surface-low:      #f2f4f6;
    --surface-high:     #eef2f0;
    --primary-fixed:    #a6f2d1;
    --on-primary-fixed: #002116;
    --secondary-fixed:  #fedcbe;
    --on-secondary-fixed:#291806;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Hero ─────────────────────────────────────────────────────────────────── */
.bs-hero {
    background: #ffffff;
    border-bottom: 1px solid var(--surface-high);
}
.bs-hero-inner {
    padding: 3rem 2rem 2.5rem;
}
.bs-success-ring {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: rgba(0,69,50,0.07);
    border: 2px solid rgba(0,69,50,0.18);
    display: flex; align-items: center; justify-content: center;
}
.bs-success-icon {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    display: flex; align-items: center; justify-content: center;
}
.bs-hero-title {
    font-size: 1.625rem; font-weight: 800;
    letter-spacing: -0.02em; color: var(--on-surface); margin: 0 0 0.5rem;
}
.bs-hero-sub {
    font-size: 0.9375rem; color: var(--on-surface-var); margin: 0;
}

/* ── Badges ───────────────────────────────────────────────────────────────── */
.bs-badge {
    display: inline-flex; align-items: center;
    background: var(--surface-low); color: var(--on-surface-var);
    border: 1px solid var(--surface-high);
    border-radius: 999px; font-size: 0.6875rem; font-weight: 700;
    letter-spacing: 0.05em; padding: 4px 12px; text-transform: uppercase;
}
.bs-badge--green { background: var(--primary-fixed); color: var(--on-primary-fixed); border-color: transparent; }
.bs-badge--warm  { background: var(--secondary-fixed); color: var(--on-secondary-fixed); border-color: transparent; }

/* ── Body ─────────────────────────────────────────────────────────────────── */
.bs-body { padding: 2rem 2rem 4rem; }

/* ── Cards ────────────────────────────────────────────────────────────────── */
.bs-card {
    background: #ffffff;
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
}
.bs-card-body { padding: 1.5rem; }
.bs-card-body--sm { padding: 1rem; }

/* ── Card titles ──────────────────────────────────────────────────────────── */
.bs-card-title {
    font-size: 0.875rem; font-weight: 800;
    color: var(--on-surface); margin: 0 0 1.25rem; letter-spacing: -0.01em;
}
.bs-card-sub { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0; }
.bs-section-title {
    font-size: 0.875rem; font-weight: 800;
    color: var(--on-surface); letter-spacing: -0.01em; margin: 0;
}

/* ── Summary table ────────────────────────────────────────────────────────── */
.bs-table { border-collapse: collapse; }
.bs-table td {
    font-size: 0.8125rem; padding: 8px 0;
    border-bottom: 1px solid var(--surface-high);
    vertical-align: middle;
}
.bs-table tr:last-child td { border-bottom: none; }
.bs-table td:first-child { color: var(--on-surface-var); width: 45%; }
.bs-table td:last-child  { text-align: right; }
.bs-table strong { font-weight: 600; color: var(--on-surface); }

/* ── Lot snapshot ─────────────────────────────────────────────────────────── */
.bs-lot-hero-img { width: 100%; height: 180px; object-fit: cover; display: block; }
.bs-lot-name { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); margin: 0 0 4px; letter-spacing: -0.01em; }
.bs-lot-meta { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0 0 1rem; }
.bs-lot-kv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border-top: 1px solid var(--surface-high); }
.bs-lot-kv {
    padding: 10px 0; display: flex; flex-direction: column; gap: 3px;
    font-size: 0.8125rem;
}
.bs-lot-kv:first-child { padding-right: 12px; border-right: 1px solid var(--surface-high); }
.bs-lot-kv:last-child  { padding-left: 12px; }
.bs-lot-kv span   { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-var); }
.bs-lot-kv strong { font-weight: 700; color: var(--on-surface); }

/* ── KPI cards ────────────────────────────────────────────────────────────── */
.bs-kpi {
    background: var(--surface-low); border-radius: 0.5rem;
    padding: 14px; display: flex; flex-direction: column; gap: 4px;
}
.bs-kpi-label {
    font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--on-surface-var);
}
.bs-kpi-val { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); }

/* ── Countdown ────────────────────────────────────────────────────────────── */
.bs-countdown-label {
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.08em; color: var(--on-surface-var); margin-bottom: 10px;
}
.bs-countdown { display: flex; align-items: center; gap: 6px; }
.bs-cd-unit {
    background: var(--surface-low); border-radius: 0.5rem;
    padding: 12px 18px; text-align: center; min-width: 64px;
}
.bs-cd-num {
    display: block; font-size: 1.625rem; font-weight: 900;
    letter-spacing: -0.03em; color: var(--primary); line-height: 1;
    font-family: 'IBM Plex Mono', monospace;
}
.bs-cd-lbl {
    display: block; font-size: 0.625rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--on-surface-var); margin-top: 4px;
}
.bs-cd-sep { font-size: 1.25rem; font-weight: 700; color: var(--on-surface-var); padding-bottom: 16px; }

/* ── Timeline ─────────────────────────────────────────────────────────────── */
.bs-timeline { display: flex; align-items: flex-start; gap: 0; overflow-x: auto; }
.bs-tl-step {
    display: flex; flex-direction: column; align-items: center;
    flex: 1; position: relative; min-width: 80px;
}
.bs-tl-node {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; z-index: 1;
    background: var(--surface-high); color: var(--on-surface-var);
    font-size: 12px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--surface-high);
}
.bs-tl-step--done .bs-tl-node {
    background: var(--primary); color: #ffffff; border-color: var(--primary);
}
.bs-tl-step--active .bs-tl-node {
    background: #ffffff; color: var(--primary);
    border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,69,50,0.15);
}
.bs-tl-line {
    position: absolute; top: 15px; left: 50%; right: -50%;
    height: 2px; background: var(--surface-high); z-index: 0;
}
.bs-tl-step--done .bs-tl-line { background: var(--primary); }
.bs-tl-body {
    margin-top: 10px; text-align: center;
    display: flex; flex-direction: column; gap: 3px; padding: 0 4px;
}
.bs-tl-body strong { font-size: 0.75rem; font-weight: 700; color: var(--on-surface); }
.bs-tl-body span   { font-size: 0.6875rem; color: var(--on-surface-var); line-height: 1.4; }

/* ── Notifications ────────────────────────────────────────────────────────── */
.bs-notif-list { display: flex; flex-direction: column; gap: 10px; }
.bs-notif-row  { display: flex; align-items: center; gap: 10px; font-size: 0.8125rem; color: var(--on-surface); }
.bs-notif-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.bs-notif-dot--success { background: #16a34a; }
.bs-notif-dot--warn    { background: #d97706; }
.bs-notif-dot--danger  { background: #dc2626; }

/* ── Action cards ─────────────────────────────────────────────────────────── */
.bs-action-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.bs-action-icon--green { background: #f0fdf4; color: var(--primary); }
.bs-action-icon--amber { background: #fffbeb; color: #b45309; }
.bs-action-icon--blue  { background: #eff6ff; color: #1d4ed8; }
.bs-action-title { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); margin: 0 0 4px; }
.bs-action-sub   { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0; line-height: 1.5; }

/* ── CTA row ──────────────────────────────────────────────────────────────── */
.bs-cta-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }

/* ── Buttons ──────────────────────────────────────────────────────────────── */
.bs-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem; font-weight: 700;
    border-radius: 0.5rem; padding: 10px 22px;
    cursor: pointer; border: none; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    transition: opacity 0.15s ease, background 0.15s ease;
}
.bs-btn--primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
}
.bs-btn--primary:hover { opacity: 0.9; }
.bs-btn--outline {
    background: transparent; color: var(--on-surface);
    border: 1px solid #bec9c2;
}
.bs-btn--outline:hover { background: var(--surface-low); }
.bs-btn--ghost { background: transparent; color: var(--on-surface-var); border: 1px solid var(--surface-high); }
.bs-btn--ghost:hover  { background: var(--surface-low); color: var(--on-surface); }
.bs-btn--sm { font-size: 0.8125rem; padding: 7px 14px; }

/* ── Similar lots ─────────────────────────────────────────────────────────── */
.bs-similar-row {
    display: flex; gap: 1rem; overflow-x: auto;
    padding-bottom: 8px; scrollbar-width: none;
}
.bs-similar-row::-webkit-scrollbar { display: none; }
.bs-similar-card { min-width: 220px; max-width: 220px; flex-shrink: 0; }
.bs-similar-img  { width: 100%; height: 120px; object-fit: cover; display: block; }
.bs-similar-code { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--primary); margin: 0 0 3px; }
.bs-similar-name { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); margin: 0 0 3px; }
.bs-similar-meta { font-size: 0.75rem; color: var(--on-surface-var); margin: 0 0 10px; }
.bs-similar-stats { display: flex; justify-content: space-between; font-size: 0.8125rem; }
.bs-similar-stats span { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-var); }

/* ── Helpers ──────────────────────────────────────────────────────────────── */
.bs-accent { color: var(--primary) !important; }
.bs-warn   { color: #d97706 !important; }
.bs-intel-list .alert { margin-bottom: 0.5rem !important; }

/* ── FAB ──────────────────────────────────────────────────────────────────── */
.bs-fab-wrap { position: fixed; bottom: 28px; right: 28px; z-index: 400; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.bs-fab {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(0,69,50,0.3);
    transition: opacity 0.15s ease;
}
.bs-fab:hover { opacity: 0.9; }
.bs-fab-menu { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
.bs-fab-item {
    background: #ffffff; color: var(--on-surface);
    border: 1px solid var(--surface-high); border-radius: 0.5rem;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem; font-weight: 700;
    padding: 9px 18px; cursor: pointer; white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: background 0.12s ease;
}
.bs-fab-item:hover { background: var(--surface-low); }
.bs-fab-fade-enter-active,
.bs-fab-fade-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.bs-fab-fade-enter-from,
.bs-fab-fade-leave-to { opacity: 0; transform: translateY(6px); }
</style>
