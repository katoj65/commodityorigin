<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import {
    ArrowRight, Right, MoreFilled, CircleCheck, EditPen, Close,
    Lock, InfoFilled, TopRight, MagicStick, ChatDotRound,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

const props = defineProps({
    offerId: { type: String, default: 'OFF-1048' },
    offer: { type: Object, default: () => ({}) },
});

/* ── Real offer fields, with dummy fallbacks wherever the offers/market
   tables don't carry that data (negotiation rounds, audit trail, quality
   & provenance data, and AI commentary have no backing columns at all,
   so those sections further below stay fully illustrative). ─────────── */
const commodityLabel = computed(() => props.offer.commodity || 'Uganda Robusta Screen 18');
const lotRef = computed(() => props.offer.lotCode || 'LOT-UG-001');
const statusLabel = computed(() => props.offer.status || 'Buyer Counter');
const buyerName = computed(() => props.offer.buyerName || 'Dubai Coffee Trading LLC');
const sellerName = computed(() => props.offer.sellerName || 'Uganda Coffee Exporter Ltd');
const counterpartyName = computed(() => props.offer.counterpartyName || buyerName.value);
const quantityKg = computed(() => props.offer.quantityKg ?? 20000);
const quantityMtLabel = computed(() => `${(quantityKg.value / 1000).toFixed(2)} MT`);
const quantityKgLabel = computed(() => `${quantityKg.value.toLocaleString()} kg (${(quantityKg.value / 1000).toFixed(2)} Metric Tons)`);
const unitPrice = computed(() => props.offer.unitPrice ?? 4.08);
const unitPriceLabel = computed(() => `$${unitPrice.value.toFixed(2)} /kg`);
const totalAmount = computed(() => props.offer.totalAmount ?? 81600);
const totalLabel = computed(() => `$${totalAmount.value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
const marketBenchmark = computed(() => props.offer.marketPricePerKg ?? 4.15);
const varianceDiscount = computed(() => {
    const diff = unitPrice.value - marketBenchmark.value;
    const pct = marketBenchmark.value ? (diff / marketBenchmark.value) * 100 : 0;
    return `${diff >= 0 ? '+' : '-'}$${Math.abs(diff).toFixed(2)} (${pct >= 0 ? '+' : ''}${pct.toFixed(1)}%)`;
});

const pipeline = [
    { num: 1, title: 'Submitted', sub: '19 Sep 09:15', state: 'done' },
    { num: 2, title: 'Received', sub: '19 Sep 10:20', state: 'done' },
    { num: 3, title: 'Countered', sub: '19 Sep 10:30', state: 'done' },
    { num: 4, title: 'Negotiating', sub: '19 Sep 11:05', state: 'active' },
    { num: 5, title: 'Accepted', sub: 'Pending', state: 'pending' },
    { num: 6, title: 'Trade Created', sub: 'Escrow Lock', state: 'pending' },
];

const termsBanner = computed(() => [
    { label: 'Target Lot', value: commodityLabel.value, sub: props.offer.origin ? `Origin: ${props.offer.origin}` : 'Screen 18 (2026 Crop)', mono: false },
    { label: 'Quantity Volume', value: quantityMtLabel.value, sub: '333 Bags (60kg Net)', mono: false },
    { label: 'Proposed Unit Price', value: unitPriceLabel.value, sub: 'CIF Jebel Ali (Dubai)', mono: true, accent: true },
    { label: 'Total Trade Value', value: totalLabel.value, sub: 'USD Escrow Lock', mono: true },
]);

const commercialSpecs = computed(() => [
    { label: 'Commodity Grade & Lot', value: commodityLabel.value, sub: `Lot Ref: ${lotRef.value} (Single Estate)` },
    { label: 'Consignment Contract Weight', value: quantityKgLabel.value, sub: '333 Export Jute Bags w/ GrainPro' },
    { label: 'Unit Value (CIF Jebel Ali)', value: `$${unitPrice.value.toFixed(2)} USD / Net Kilogram`, sub: `$${(unitPrice.value * 1000).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USD / Metric Ton`, accent: true },
    { label: 'Settlement Capital Requirement', value: `${totalLabel.value} Total Net Value`, sub: 'Stanbic Institutional Escrow Transit' },
    { label: 'Port & Discharge Destination', value: 'Dubai (Jebel Ali Freezone, UAE)', sub: 'Port of Origin: Mombasa (Kenya)' },
    { label: 'Stipulated Target Dispatch', value: '30 October 2026', sub: 'Max Lead Time: 40 Calendar Days' },
]);

const varianceMetrics = [
    { label: 'Contract Unit Price', old: '$4.12', value: '$4.08/kg', delta: '-$0.04/kg (-0.97%)', tone: 'primary' },
    { label: 'Gross Contract Value', old: '$82,400', value: '$81,600', delta: '-$800.00 USD total concession', tone: 'secondary' },
    { label: 'Execution Mechanism', value: 'Stanbic Escrow Guarantee', delta: 'LC draft ready for lock', tone: 'primary' },
];

const negotiationRounds = [
    {
        round: 'Round 3 · Latest', latest: true, actor: 'Buyer Counter-Offer (Dubai Coffee Trading LLC)', timestamp: '19 Sep 2026, 11:05 UTC',
        priceLine: '20 MT @ $4.08 / kg CIF Dubai = $81,600 USD',
        message: 'Split difference at $4.08/kg. Letter of credit draft ready for immediate lock in Stanbic Escrow. Vessel space reserved with Maersk for early November arrival if finalized today.',
        note: 'Verified Buyer Balance ($120k) · Incoterm: CIF Jebel Ali',
    },
    {
        round: 'Round 2', actor: 'Seller Counter-Offer (Uganda Coffee Exporter Ltd)', timestamp: '19 Sep 2026, 10:30 UTC',
        priceLine: '20 MT @ $4.12 / kg CIF Dubai = $82,400 USD',
        message: 'Mukono single-origin screen 18 certified with zero primary defects. Current parity is $4.15/kg FOB Mombasa. Conceding freight portion to $4.12 CIF as long-term trade goodwill.',
    },
    {
        round: 'Round 1 · Origin', actor: 'Buyer Initial Offer (Dubai Coffee Trading LLC)', timestamp: '19 Sep 2026, 09:15 UTC',
        priceLine: '20 MT @ $4.05 / kg CIF Dubai = $81,000 USD',
        message: 'Looking for prompt container dispatch via Mombasa port. Standard export packaging required.',
    },
];

const auditTrail = [
    { time: '2026-09-19 11:05:42', event: 'Proposal Submitted (Round 3)', actor: 'Buyer: Trader #092', hash: '0x7c49...a9e3', tone: 'primary' },
    { time: '2026-09-19 10:30:11', event: 'Counter-Offer Submitted', actor: 'Seller: Principal #014', hash: '0x18db...f102', tone: 'secondary' },
    { time: '2026-09-19 10:20:04', event: 'Offer Viewed & Decrypted', actor: 'Seller: Principal #014', hash: '0xe359...bc21', tone: 'muted' },
    { time: '2026-09-19 09:15:20', event: 'Initial Offer Published', actor: 'Buyer: Trader #092', hash: '0x99a1...100d', tone: 'strong' },
];

const provenanceMetrics = [
    { label: 'Screen 18 Ret.', value: '92.4%', accent: true },
    { label: 'Moisture Cont.', value: '11.2%' },
    { label: 'CQI Score', value: '84.5 / 100', accent: 'secondary' },
    { label: 'Altitude', value: '1,200m ASL' },
];

const custodyChain = [
    { label: 'Farm: Kampala Coffee Estate' },
    { label: 'Collection: Mill Center FC-1048' },
    { label: 'Batch: BAT-2091 (Dry Milled)' },
    { label: 'Active Lot: LOT-UG-001', strong: true },
];

const counterparties = computed(() => [
    { role: 'Proposing Counterparty', name: buyerName.value, sub: 'DMCC Free Zone, United Arab Emirates', rating: 'Escrow Rating: 4.9/5', trades: '14 Completed Trades', tone: 'primary' },
    { role: 'Export Supplier', name: sellerName.value, sub: 'Licence #UCDA-EXP-088 · Kampala', rating: 'UCDA Certified Tier-1', trades: '22 Completed Trades', tone: 'secondary' },
]);

const aiSuggestions = [
    'Analyze Ocean Freight Spread',
    'Calculate Landed Cost Jebel Ali',
];

/* ── Modals — inert dummy interactivity, nothing persisted ──────────── */
const acceptOpen = ref(false);
const counterOpen = ref(false);
const counterForm = reactive({ price: 4.10, qty: 20, incoterm: 'CIF Jebel Ali (Dubai)', validity: '24 Hours', message: 'We can meet in the exact middle at $4.10/kg CIF Jebel Ali. All export permits are already stamped.' });
const counterTotal = computed(() => (counterForm.price * counterForm.qty * 1000).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

function confirmAccept() {
    acceptOpen.value = false;
    ElMessage.success(`Trade generated from ${props.offerId} (dummy preview).`);
}

function submitCounter() {
    counterOpen.value = false;
    ElMessage.success('Counter-offer transmitted (dummy preview).');
}

function placeholderAction(label) {
    ElMessage.info(`${label} (dummy preview).`);
}
</script>

<template>
    <DesignPreviewLayout :title="`Offer ${offerId}`">
        <div class="ex-page">

            <!-- HEADER -->
            <section class="ex-card ex-profile-hero">
                <div class="ex-profile-hero__top">
                    <div>
                        <div class="ex-flex-icon">
                            <h1 class="dp-display-md ex-strong">Offer {{ offerId }}</h1>
                            <span class="ex-tag-mini ex-tag-mini--primary">{{ statusLabel }}</span>
                        </div>
                        <p class="dp-body-md ex-muted" style="max-width: 640px;">
                            Commercial negotiation for <strong class="ex-strong">{{ commodityLabel }}</strong> &mdash; Lot Ref:
                            <button type="button" class="ex-link" style="display: inline-flex; padding: 0;" @click="placeholderAction(`Inspect ${lotRef}`)">{{ lotRef }} <el-icon :size="13"><TopRight /></el-icon></button>
                        </p>
                    </div>
                    <div class="ex-hero__actions">
                        <button type="button" class="ex-btn ex-btn--primary" @click="acceptOpen = true"><el-icon :size="16"><CircleCheck /></el-icon><span>Accept Offer ({{ totalLabel }})</span></button>
                        <button type="button" class="ex-btn ex-btn--muted" @click="counterOpen = true"><el-icon :size="16"><EditPen /></el-icon><span>Counter Offer</span></button>
                        <button type="button" class="ex-btn ex-btn--muted" style="color: var(--dp-error);" @click="placeholderAction('Decline Offer')"><el-icon :size="16"><Close /></el-icon><span>Decline</span></button>
                        <button type="button" class="ex-icon-btn" title="More Options" @click="placeholderAction('More Options')"><el-icon :size="16"><MoreFilled /></el-icon></button>
                    </div>
                </div>
            </section>

            <!-- COMMERCIAL TERMS BANNER -->
            <section class="ex-card ex-terms-banner">
                <div v-for="term in termsBanner" :key="term.label">
                    <span class="ex-ft-label">{{ term.label }}</span>
                    <div class="ex-strong" :class="{ 'dp-mono': term.mono, 'ex-icon--primary': term.accent }" style="font-size: 15px; margin-top: 2px;">{{ term.value }}</div>
                    <div class="dp-caption ex-muted" :class="{ 'ex-icon--secondary': term.label === 'Proposed Unit Price' }">{{ term.sub }}</div>
                </div>
                <div>
                    <span class="ex-ft-label">Counterparties</span>
                    <div class="dp-caption ex-strong" style="margin-top: 2px;" :title="`Buyer: ${buyerName}`">B: {{ buyerName }}</div>
                    <div class="dp-caption ex-muted" :title="`Seller: ${sellerName}`">S: {{ sellerName }}</div>
                </div>
                <div class="ex-validity-box">
                    <span class="ex-eyebrow ex-flex-icon" style="color: #000;"><el-icon :size="13" style="color: #000;"><InfoFilled /></el-icon>Validity Window</span>
                    <div class="dp-mono" style="font-size: 12px; color: #000; font-weight: 700;">21 Sep 2026, 14:00</div>
                    <div class="dp-caption" style="color: #000; font-weight: 700;">3 hours remaining</div>
                </div>
            </section>

            <!-- NEGOTIATION LIFECYCLE RIBBON -->
            <section class="ex-card">
                <div class="ex-flex-icon-between">
                    <span class="ex-eyebrow ex-muted">Negotiation Protocol State</span>
                    <span class="dp-caption ex-icon--primary dp-mono ex-strong">Stage 4 of 6 (Active Turn: Seller Response)</span>
                </div>
                <div class="ex-ribbon">
                    <div v-for="step in pipeline" :key="step.num" class="ex-ribbon__step" :class="`ex-ribbon__step--${step.state}`">
                        <div class="ex-ribbon__circle">
                            <el-icon v-if="step.state === 'done'" :size="14"><CircleCheck /></el-icon>
                            <el-icon v-else-if="step.state === 'active'" :size="14"><ChatDotRound /></el-icon>
                            <span v-else style="font-size: 11px; font-weight: 700;">{{ step.num }}</span>
                        </div>
                        <span class="ex-ribbon__title" :class="{ 'ex-icon--primary': step.state === 'active' }">{{ step.title }}</span>
                        <span class="dp-caption ex-muted dp-mono">{{ step.sub }}</span>
                    </div>
                </div>
            </section>

            <!-- MAIN TWO-COLUMN LAYOUT -->
            <section class="ex-grid-12">
                <div class="ex-col-main">
                    <!-- Active Commercial Agreement Terms -->
                    <div class="ex-card">
                        <div class="ex-card__head">
                            <div>
                                <h2 class="dp-headline-md">Active Commercial Agreement Terms</h2>
                                <p class="dp-caption ex-muted">Binding clauses proposed in Round 3 by {{ buyerName }}</p>
                            </div>
                            <span class="ex-tag-mini ex-tag-mini--primary">Incoterms&reg; 2020: CIF</span>
                        </div>
                        <div class="ex-spec-grid">
                            <div v-for="spec in commercialSpecs" :key="spec.label" class="ex-panel ex-spec-box">
                                <span class="ex-ft-label">{{ spec.label }}</span>
                                <span class="ex-strong" :class="{ 'ex-icon--primary dp-mono': spec.accent }" style="font-size: 13px;">{{ spec.value }}</span>
                                <span class="dp-caption ex-muted" :class="{ 'dp-mono': spec.accent }">{{ spec.sub }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Proposal Variance -->
                    <div class="ex-card">
                        <div class="ex-flex-icon-between">
                            <div class="ex-flex-icon"><el-icon :size="18" class="ex-icon--secondary"><TopRight /></el-icon><h3 class="dp-headline-sm ex-strong">Proposal Variance (Delta: Round 2 &rarr; Round 3)</h3></div>
                            <span class="dp-caption ex-muted dp-mono">Round 3 Adjustments</span>
                        </div>
                        <div class="ex-variance-grid">
                            <div v-for="metric in varianceMetrics" :key="metric.label" class="ex-panel">
                                <span class="ex-ft-label">{{ metric.label }}</span>
                                <div class="ex-flex-icon" style="margin-top: 4px;">
                                    <span v-if="metric.old" class="dp-mono ex-muted" style="text-decoration: line-through;">{{ metric.old }}</span>
                                    <span class="dp-mono ex-strong" :class="`ex-icon--${metric.tone}`" style="font-size: 15px;">{{ metric.value }}</span>
                                </div>
                                <div class="dp-caption ex-strong" :class="`ex-icon--${metric.tone}`" style="margin-top: 6px;">{{ metric.delta }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Negotiation History Timeline -->
                    <div class="ex-card">
                        <div class="ex-flex-icon-between">
                            <div class="ex-flex-icon"><el-icon :size="18" class="ex-icon--primary"><ChatDotRound /></el-icon><h3 class="dp-headline-sm ex-strong">Interactive Negotiation History</h3></div>
                            <span class="dp-caption ex-muted">3 Iterations Completed</span>
                        </div>
                        <div class="ex-timeline">
                            <div v-for="round in negotiationRounds" :key="round.round" class="ex-timeline__item">
                                <div class="ex-timeline__dot" :class="{ 'ex-timeline__dot--active': round.latest }"></div>
                                <div class="ex-panel" :class="{ 'ex-timeline__card--active': round.latest }">
                                    <div class="ex-flex-icon-between" style="margin-bottom: 8px;">
                                        <div class="ex-flex-icon">
                                            <span class="ex-tag-mini" :class="round.latest ? 'ex-tag-mini--primary' : 'ex-tag-mini--neutral'">{{ round.round }}</span>
                                            <span class="dp-caption ex-strong">{{ round.actor }}</span>
                                        </div>
                                        <span class="dp-caption ex-muted dp-mono">{{ round.timestamp }}</span>
                                    </div>
                                    <div class="dp-mono ex-strong" :class="{ 'ex-icon--primary': round.latest }" style="font-size: 13px; margin-bottom: 8px;">{{ round.priceLine }}</div>
                                    <p class="dp-caption ex-muted" style="line-height: 1.6;">&ldquo;{{ round.message }}&rdquo;</p>
                                    <div v-if="round.note" class="dp-caption ex-icon--primary ex-flex-icon" style="margin-top: 8px;"><el-icon :size="13"><CircleCheck /></el-icon>{{ round.note }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Respond Action Card -->
                    <div class="ex-card">
                        <div class="ex-flex-icon-between">
                            <div>
                                <h3 class="dp-headline-sm ex-strong">Respond to Current Commercial Terms</h3>
                                <p class="dp-caption ex-muted">You have 3 hours to take action before this offer expires.</p>
                            </div>
                            <div style="text-align: right;">
                                <span class="ex-ft-label">Settlement Value</span>
                                <div class="dp-mono ex-icon--primary ex-strong" style="font-size: 18px;">{{ totalLabel }} USD</div>
                            </div>
                        </div>
                        <div class="ex-actions-inline">
                            <button type="button" class="ex-btn ex-btn--primary" style="flex: 1; min-width: 200px;" @click="acceptOpen = true"><el-icon :size="16"><CircleCheck /></el-icon><span>Accept Proposal &amp; Lock Escrow</span></button>
                            <button type="button" class="ex-btn ex-btn--muted" @click="counterOpen = true"><el-icon :size="16"><EditPen /></el-icon><span>Make Counter Offer</span></button>
                            <button type="button" class="ex-btn ex-btn--muted" style="color: var(--dp-error);" @click="placeholderAction('Decline Offer')"><el-icon :size="16"><Close /></el-icon><span>Decline Offer</span></button>
                        </div>
                    </div>

                    <!-- Cryptographic Audit Trail -->
                    <div class="ex-card">
                        <div class="ex-flex-icon-between">
                            <div class="ex-flex-icon"><el-icon :size="16" class="ex-muted"><Lock /></el-icon><h3 class="dp-headline-sm ex-strong">Cryptographic Audit Trail</h3></div>
                            <span class="dp-caption ex-muted dp-mono">SHA-256 Ledger Verified</span>
                        </div>
                        <div class="ex-table-wrap">
                            <table class="ex-table">
                                <thead>
                                    <tr><th>Timestamp (UTC)</th><th>Event Signature</th><th>Actor</th><th>Hash Anchor</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in auditTrail" :key="row.hash">
                                        <td class="dp-mono ex-muted">{{ row.time }}</td>
                                        <td class="dp-mono" :class="row.tone === 'muted' ? 'ex-muted' : `ex-icon--${row.tone === 'strong' ? 'primary' : row.tone}`">{{ row.event }}</td>
                                        <td>{{ row.actor }}</td>
                                        <td class="dp-mono ex-muted ex-caption-sm">{{ row.hash }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="ex-col-side">
                    <!-- Provenance Card -->
                    <div class="ex-card" style="padding: 0; overflow: hidden;">
                        <div class="ex-provenance-media">
                            <div class="ex-provenance-media__overlay">
                                <span class="ex-eyebrow ex-icon--primary" style="color: var(--dp-primary-fixed);">Lot Provenance Verified</span>
                                <h4 style="color: #fff; font-weight: 800; font-size: 15px; margin: 2px 0;">Finca Mukono Estate</h4>
                                <span class="dp-caption dp-mono" style="color: rgba(255,255,255,0.8);">{{ commodityLabel }}</span>
                            </div>
                        </div>
                        <div style="padding: 18px; display: flex; flex-direction: column; gap: 14px;">
                            <div class="ex-provenance-metrics">
                                <div v-for="metric in provenanceMetrics" :key="metric.label" class="ex-panel ex-center">
                                    <span class="ex-ft-label">{{ metric.label }}</span>
                                    <div class="dp-mono ex-strong" :class="metric.accent ? `ex-icon--${metric.accent === true ? 'primary' : metric.accent}` : 'ex-on'" style="font-size: 14px;">{{ metric.value }}</div>
                                </div>
                            </div>
                            <div>
                                <span class="ex-ft-label">Verified Chain of Custody</span>
                                <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 8px;">
                                    <div v-for="c in custodyChain" :key="c.label" class="dp-caption dp-mono ex-flex-icon" :class="c.strong ? 'ex-strong ex-icon--primary' : 'ex-muted'">
                                        <span class="ex-dot" :style="{ background: c.strong ? 'var(--dp-primary)' : 'var(--dp-outline-variant)' }"></span>{{ c.label }}
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="ex-btn ex-btn--muted" style="width: 100%;" @click="placeholderAction('View Full Lot Inspection Dossier')">View Full Lot Inspection Dossier</button>
                        </div>
                    </div>

                    <!-- Counterparties -->
                    <div class="ex-card">
                        <span class="ex-ft-label">Trading Counterparties</span>
                        <div v-for="cp in counterparties" :key="cp.name" class="ex-panel">
                            <div class="ex-flex-icon-between">
                                <div>
                                    <span class="ex-eyebrow" :class="`ex-icon--${cp.tone}`">{{ cp.role }}</span>
                                    <h5 class="ex-strong" style="font-size: 14px; margin: 2px 0;">{{ cp.name }}</h5>
                                    <span class="dp-caption ex-muted">{{ cp.sub }}</span>
                                </div>
                                <el-icon :size="18" class="ex-icon--primary"><CircleCheck /></el-icon>
                            </div>
                            <div class="ex-flex-icon-between" style="margin-top: 10px;">
                                <span class="dp-caption ex-muted dp-mono">{{ cp.rating }}</span>
                                <span class="dp-caption ex-strong">{{ cp.trades }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Market Parity -->
                    <div class="ex-card">
                        <div class="ex-flex-icon-between">
                            <span class="ex-ft-label">Live Mombasa Parity</span>
                            <span class="ex-dot ex-dot--pulse"></span>
                        </div>
                        <div class="ex-parity-row"><span class="dp-caption ex-muted">FOB Mombasa Benchmark</span><span class="dp-mono ex-strong">${{ marketBenchmark.toFixed(2) }} / kg</span></div>
                        <div class="ex-parity-row"><span class="dp-caption ex-muted">Current Offer (CIF Dubai)</span><span class="dp-mono ex-icon--primary ex-strong">${{ unitPrice.toFixed(2) }} / kg</span></div>
                        <div class="ex-parity-row"><span class="dp-caption ex-muted">Variance Discount</span><span class="dp-mono ex-icon--secondary ex-strong">{{ varianceDiscount }}</span></div>
                        <div class="ex-panel dp-caption ex-on" style="line-height: 1.6;">3 comparable Screen 18 Ugandan lots currently listed between $4.10 and $4.18/kg FOB.</div>
                        <button type="button" class="ex-link" style="justify-content: center;" @click="router.visit(route('exchange.index'))">Compare on Exchange Order Book <el-icon :size="14"><ArrowRight /></el-icon></button>
                    </div>

                    <!-- AI Commercial Copilot -->
                    <div class="ex-card" style="background: color-mix(in srgb, var(--dp-primary) 6%, var(--dp-surface-container-lowest));">
                        <div class="ex-flex-icon ex-icon--primary ex-eyebrow"><el-icon :size="16"><MagicStick /></el-icon>Bean Origin AI Copilot</div>
                        <p class="dp-caption ex-on" style="line-height: 1.6;">&ldquo;The offer of <strong>${{ unitPrice.toFixed(2) }}/kg</strong> sits {{ Math.abs(((unitPrice - marketBenchmark) / marketBenchmark) * 100).toFixed(1) }}% below FOB Mombasa spot. If accepted today, turnaround guarantees prompt container booking by Oct 5 with guaranteed Stanbic escrow lock.&rdquo;</p>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <button v-for="s in aiSuggestions" :key="s" type="button" class="ex-ai-suggestion" @click="placeholderAction(s)"><span>{{ s }}</span><el-icon :size="14" class="ex-muted"><Right /></el-icon></button>
                            <button type="button" class="ex-ai-suggestion ex-ai-suggestion--primary" @click="counterOpen = true"><span>Counter at Recommended $4.10/kg</span><el-icon :size="14"><EditPen /></el-icon></button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- MODAL: Accept Confirmation -->
        <el-dialog v-model="acceptOpen" width="min(520px, calc(100vw - 2rem))" align-center :show-close="false" class="ex-modal">
            <template #header>
                <div class="ex-modal-head">
                    <div class="ex-modal-head__icon"><el-icon :size="18"><CircleCheck /></el-icon></div>
                    <div>
                        <div class="dp-headline-sm ex-strong">Confirm Acceptance of Offer</div>
                        <div class="dp-caption ex-muted">{{ offerId }} &middot; {{ totalLabel }} USD</div>
                    </div>
                    <button type="button" class="ex-modal-close" @click="acceptOpen = false"><el-icon :size="14"><Close /></el-icon></button>
                </div>
            </template>
            <div class="ex-modal-body">
                <div class="ex-panel" style="display: flex; flex-direction: column; gap: 8px;">
                    <div class="ex-flex-icon-between"><span class="dp-caption ex-muted">Total Settlement:</span><span class="dp-mono ex-icon--primary ex-strong">{{ totalLabel }} USD</span></div>
                    <div class="ex-flex-icon-between"><span class="dp-caption ex-muted">Volume:</span><span class="dp-caption">{{ quantityMtLabel }} {{ commodityLabel }}</span></div>
                    <div class="ex-flex-icon-between"><span class="dp-caption ex-muted">Incoterm / Release:</span><span class="dp-caption">CIF Dubai &middot; Stanbic Escrow Lock</span></div>
                </div>
                <div class="ex-panel dp-caption ex-icon--primary" style="line-height: 1.6;">
                    <strong>Legal Execution Safeguard:</strong> Accepting this offer binds both parties to Bean Origin Rulebook standard arbitration and converts this negotiation into executable <strong>Trade TRD-1048</strong>.
                </div>
            </div>
            <template #footer>
                <div class="ex-actions-inline ex-actions-inline--end">
                    <button type="button" class="ex-btn ex-btn--muted" @click="acceptOpen = false">Cancel</button>
                    <button type="button" class="ex-btn ex-btn--primary" @click="confirmAccept">Confirm &amp; Execute Trade</button>
                </div>
            </template>
        </el-dialog>

        <!-- MODAL: Counter Offer -->
        <el-dialog v-model="counterOpen" width="min(560px, calc(100vw - 2rem))" align-center :show-close="false" class="ex-modal">
            <template #header>
                <div class="ex-modal-head">
                    <div class="ex-modal-head__icon"><el-icon :size="18"><EditPen /></el-icon></div>
                    <div>
                        <div class="dp-headline-sm ex-strong">Submit Counter-Offer (Round 4)</div>
                        <div class="dp-caption ex-muted">Counterparty: {{ counterpartyName }} &middot; Ref {{ offerId }}</div>
                    </div>
                    <button type="button" class="ex-modal-close" @click="counterOpen = false"><el-icon :size="14"><Close /></el-icon></button>
                </div>
            </template>
            <div class="ex-modal-body">
                <div class="ex-field-grid">
                    <div class="ex-field">
                        <label>Price per kg (USD)</label>
                        <input v-model.number="counterForm.price" type="number" step="0.01" />
                    </div>
                    <div class="ex-field">
                        <label>Metric Tons (MT)</label>
                        <input v-model.number="counterForm.qty" type="number" />
                    </div>
                </div>
                <div class="ex-field-grid">
                    <div class="ex-field">
                        <label>Incoterms Delivery</label>
                        <select v-model="counterForm.incoterm"><option>CIF Jebel Ali (Dubai)</option><option>FOB Mombasa Port</option><option>EXW Kampala Dry Port</option></select>
                    </div>
                    <div class="ex-field">
                        <label>Validity (Hours)</label>
                        <select v-model="counterForm.validity"><option>24 Hours</option><option>48 Hours</option><option>72 Hours</option></select>
                    </div>
                </div>
                <div class="ex-field">
                    <label>Addressed Commercial Message</label>
                    <textarea v-model="counterForm.message" rows="3" placeholder="Provide contractual rationale or shipping condition specifications..."></textarea>
                </div>
                <div class="ex-total-box">
                    <span class="dp-caption ex-muted">Recalculated Value:</span>
                    <span class="dp-mono ex-icon--primary ex-strong" style="font-size: 15px;">${{ counterTotal }} USD</span>
                </div>
            </div>
            <template #footer>
                <div class="ex-actions-inline ex-actions-inline--end">
                    <button type="button" class="ex-btn ex-btn--muted" @click="counterOpen = false">Cancel</button>
                    <button type="button" class="ex-btn ex-btn--primary" @click="submitCounter">Dispatch Counter Proposal</button>
                </div>
            </template>
        </el-dialog>
    </DesignPreviewLayout>
</template>

<style>
.el-dialog.ex-modal { border-radius: 12px; padding: 0; overflow: hidden; }
.el-dialog.ex-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.ex-modal .el-dialog__body { padding: 0; }
.el-dialog.ex-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.ex-page { display: flex; flex-direction: column; gap: 16px; }

.ex-card {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--dp-outline-variant);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.ex-panel { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; display: flex; flex-direction: column; gap: 2px; }

.ex-muted { color: var(--dp-on-surface-variant); }
.ex-strong { color: var(--dp-on-surface); font-weight: 700; }
.ex-on { color: var(--dp-on-surface); }
.ex-caption-sm { font-size: 11px; }
.ex-eyebrow { text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; font-size: 11px; }
.ex-flex-icon { display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ex-flex-icon-between { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }

.ex-icon--primary { color: var(--dp-primary); }
.ex-icon--secondary { color: var(--dp-secondary); }
.ex-icon--error { color: var(--dp-error); }

.ex-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--dp-primary); flex-shrink: 0; }
.ex-dot--pulse { animation: ex-pulse 1.6s ease-in-out infinite; }
@keyframes ex-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }

.ex-tag-mini { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 3px 9px; border-radius: 6px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); white-space: nowrap; }
.ex-tag-mini--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.ex-tag-mini--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ex-tag-mini--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.ex-center { text-align: center; }
.ex-right { text-align: right; }

.ex-ft-label { font-size: 10px; font-weight: 700; color: var(--dp-on-surface-variant); text-transform: uppercase; letter-spacing: 0.04em; }

.ex-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 14px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; transition: background 0.15s ease, color 0.15s ease; font-family: var(--dp-font-sans); white-space: nowrap; text-decoration: none; }
.ex-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ex-btn--primary:hover { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.ex-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.ex-btn--muted:hover { background: var(--dp-surface-dim); }
.ex-btn--sm { padding: 6px 10px; }
.ex-icon-btn { width: 38px; height: 38px; border-radius: 8px; border: none; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
.ex-icon-btn:hover { background: var(--dp-surface-dim); color: var(--dp-on-surface); }

.ex-link { display: inline-flex; align-items: center; justify-content: space-between; gap: 6px; font-weight: 700; color: var(--dp-primary); text-decoration: none; background: none; border: none; cursor: pointer; font-size: 12px; font-family: var(--dp-font-sans); padding: 6px 4px; }
.ex-link:hover { text-decoration: underline; }

.ex-actions-inline { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ex-actions-inline--end { justify-content: flex-end; }

.ex-table-wrap { overflow-x: auto; }
.ex-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
.ex-table thead tr { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; font-weight: 700; }
.ex-table th { padding: 10px 12px; white-space: nowrap; }
.ex-table th:first-child { border-radius: 6px 0 0 6px; }
.ex-table th:last-child { border-radius: 0 6px 6px 0; }
.ex-table tbody tr { border-bottom: 1px solid var(--dp-outline-variant); }
.ex-table tbody tr:last-child { border-bottom: none; }
.ex-table tbody tr:hover { background: var(--dp-surface-container-low); }
.ex-table td { padding: 12px; vertical-align: middle; }

/* ── Grid / two-column layout (matches Exchange/Index.vue) ──────────── */
.ex-grid-12 { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 1200px) { .ex-grid-12 { grid-template-columns: minmax(0, 1fr) 320px; align-items: start; } }
.ex-col-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.ex-col-side { display: flex; flex-direction: column; gap: 16px; }
.ex-card__head { display: flex; flex-direction: column; gap: 10px; }
@media (min-width: 640px) { .ex-card__head { flex-direction: row; align-items: flex-start; justify-content: space-between; } }

/* ── Breadcrumb + status band ────────────────────────────────────────── */
.ex-crumb-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
.ex-crumbs { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; }
.ex-crumbs__link { color: var(--dp-on-surface-variant); text-decoration: none; }
.ex-crumbs__link:hover { color: var(--dp-primary); }
.ex-crumb-row__right { display: flex; align-items: center; gap: 10px; }

/* ── Profile hero ────────────────────────────────────────────────────── */
.ex-profile-hero { border: none; border-bottom: 1px solid var(--dp-outline-variant); border-radius: 0; box-shadow: none; margin-top: -32px; padding-bottom: 24px; }
.ex-profile-hero__top { display: flex; flex-direction: column; gap: 16px; }
@media (min-width: 1024px) { .ex-profile-hero__top { flex-direction: row; align-items: center; justify-content: space-between; } }
.ex-hero__actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }

/* ── Commercial terms banner ─────────────────────────────────────────── */
.ex-terms-banner { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
@media (min-width: 768px) { .ex-terms-banner { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1200px) { .ex-terms-banner { grid-template-columns: repeat(6, 1fr); } }
.ex-validity-box { background: var(--dp-secondary-fixed); border-radius: 8px; padding: 10px 12px; display: flex; flex-direction: column; gap: 2px; justify-content: space-between; }

/* ── Negotiation lifecycle ribbon ────────────────────────────────────── */
.ex-ribbon { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
@media (min-width: 768px) { .ex-ribbon { grid-template-columns: repeat(6, 1fr); } }
.ex-ribbon__step { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 3px; opacity: 0.4; }
.ex-ribbon__step--done, .ex-ribbon__step--active { opacity: 1; }
.ex-ribbon__circle { width: 28px; height: 28px; border-radius: 999px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); display: flex; align-items: center; justify-content: center; }
.ex-ribbon__step--done .ex-ribbon__circle, .ex-ribbon__step--active .ex-ribbon__circle { background: var(--dp-primary); color: var(--dp-on-primary); }
.ex-ribbon__step--active .ex-ribbon__circle { box-shadow: 0 0 0 4px var(--dp-primary-fixed); }
.ex-ribbon__title { font-size: 11px; font-weight: 700; color: var(--dp-on-surface); }

/* ── Commercial spec grid ────────────────────────────────────────────── */
.ex-spec-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 640px) { .ex-spec-grid { grid-template-columns: repeat(2, 1fr); } }
.ex-spec-box { gap: 4px; }

/* ── Proposal variance ───────────────────────────────────────────────── */
.ex-variance-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 768px) { .ex-variance-grid { grid-template-columns: repeat(3, 1fr); } }

/* ── Negotiation timeline ────────────────────────────────────────────── */
.ex-timeline { position: relative; display: flex; flex-direction: column; gap: 20px; padding-left: 22px; }
.ex-timeline::before { content: ''; position: absolute; left: 5px; top: 6px; bottom: 6px; width: 2px; background: var(--dp-outline-variant); }
.ex-timeline__item { position: relative; }
.ex-timeline__dot { position: absolute; left: -22px; top: 6px; width: 12px; height: 12px; border-radius: 999px; background: var(--dp-surface-container-high); border: 3px solid var(--dp-surface-container-lowest); }
.ex-timeline__dot--active { background: var(--dp-primary); }
.ex-timeline__card--active { background: color-mix(in srgb, var(--dp-primary) 6%, var(--dp-surface-container-low)); }

/* ── Provenance sidebar card ─────────────────────────────────────────── */
.ex-provenance-media { position: relative; height: 160px; background: linear-gradient(135deg, var(--dp-primary) 0%, var(--dp-tertiary) 100%); }
.ex-provenance-media__overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.75), rgba(0,0,0,0.1) 60%, transparent); display: flex; flex-direction: column; justify-content: flex-end; padding: 14px; }
.ex-provenance-metrics { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }

/* ── Market parity rows ──────────────────────────────────────────────── */
.ex-parity-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 12px; padding-bottom: 6px; }

/* ── AI copilot suggestion buttons ───────────────────────────────────── */
.ex-ai-suggestion { display: flex; align-items: center; justify-content: space-between; gap: 8px; width: 100%; padding: 8px 10px; border-radius: 6px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); font-size: 11px; font-weight: 600; cursor: pointer; text-align: left; font-family: var(--dp-font-sans); }
.ex-ai-suggestion:hover { background: var(--dp-surface-container-high); }
.ex-ai-suggestion--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ex-ai-suggestion--primary:hover { background: var(--dp-primary-container); }

/* ── Modal internals (shared with OffersPage.vue) ───────────────────── */
.ex-modal-head { display: flex; align-items: flex-start; gap: 12px; padding: 20px 24px; border-bottom: 1px solid var(--dp-outline-variant); }
.ex-modal-head__icon { width: 36px; height: 36px; border-radius: 8px; background: var(--dp-surface-container-high); color: var(--dp-on-surface); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ex-modal-head > div:nth-child(2) { flex: 1; min-width: 0; }
.ex-modal-close { width: 28px; height: 28px; border-radius: 8px; border: none; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
.ex-modal-close:hover { background: var(--dp-surface-dim); color: var(--dp-on-surface); }

.ex-modal-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 60vh; overflow-y: auto; }
.ex-field-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
@media (max-width: 640px) { .ex-field-grid { grid-template-columns: 1fr; } }
.ex-field { display: flex; flex-direction: column; gap: 6px; }
.ex-field label { font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant); }
.ex-field input, .ex-field select, .ex-field textarea { background: var(--dp-surface-container-low); border: none; border-radius: 8px; padding: 9px 12px; font-size: 12px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); outline: none; }
.ex-field input:focus, .ex-field select:focus, .ex-field textarea:focus { background: var(--dp-surface-container-high); }
.ex-field textarea { resize: vertical; font-family: var(--dp-font-sans); }

.ex-total-box { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border-radius: 8px; background: color-mix(in srgb, var(--dp-primary) 10%, transparent); }
</style>
