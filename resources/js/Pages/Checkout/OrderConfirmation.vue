<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    order:   { type: Object, default: () => ({}) },
    lot:     { type: Object, default: () => ({}) },
    similar: { type: Array,  default: () => []   },
});

/* ── Order data ─────────────────────────────────────────────────────────── */
const orderId       = computed(() => props.order?.order_number  || 'ORD-2026-00341');
const purchaseDate  = computed(() => props.order?.created_at    || new Date().toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' }));
const paymentStatus = computed(() => props.order?.payment_status || 'Paid');
const sellerId      = computed(() => props.order?.seller        || 'Bugisu Cooperative Union');
const quantity      = computed(() => Number(props.order?.quantity || 300));
const bags          = computed(() => Math.ceil(quantity.value / 60));
const unitPrice     = computed(() => Number(props.order?.unit_price || 12.40));
const totalAmount   = computed(() => (unitPrice.value * quantity.value).toLocaleString('en-US', { minimumFractionDigits: 2 }));
const paymentMethod = computed(() => props.order?.payment_method || 'Bank Transfer');

/* ── Lot data ───────────────────────────────────────────────────────────── */
const lotName      = computed(() => props.lot?.lot_name      || 'Bugisu Premium Export Lot');
const lotCode      = computed(() => props.lot?.lot_code      || 'LOT-2026-001');
const origin       = computed(() => props.lot?.origin        || 'Mbale, Uganda');
const coffeeType   = computed(() => props.lot?.variety       || 'Arabica – Bourbon');
const process      = computed(() => props.lot?.process       || 'Washed');
const qualityScore = computed(() => Number(props.lot?.quality_score || 87.5).toFixed(1));
const season       = computed(() => props.lot?.season        || '2025/26 Main Crop');
const lotImage     = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVNPRKcnvtgsayf1-HlE1xA92LWW1C56Io3VMreh4aujnZTgd7RVNEZOyEqFGcffC6O3JdFFEczJbLDdWYhY3SPZ_97Ep-mSdEA6EpSHOYxQ4YC-9rWllkkDGEgrkRhX8fdY9yD34FR8UBs42K4RgVHEi6OXDt4QvP-hJgG1uWAZlyFMQ7HCYg9NcS7oQW5HysDvCK3FiXBDRpfkupmdW5tIy7o5GV8ZL8feaXnYtU6ZpDEAJvS_XKRffdezzJJCSUQeF2AHlDDapn';

/* ── Shipping ───────────────────────────────────────────────────────────── */
const warehouse        = computed(() => props.order?.warehouse       || 'UCE Kampala Bonded Warehouse');
const shippingMethod   = computed(() => props.order?.shipping_method || 'Air Freight');
const destination      = computed(() => props.order?.destination     || 'Rotterdam, Netherlands');
const estimatedDispatch = computed(() => props.order?.dispatch_date  || '02 Jun 2026');
const estimatedDelivery = computed(() => props.order?.delivery_date  || '18 Jun 2026');

/* ── Payment breakdown ──────────────────────────────────────────────────── */
const productTotal = computed(() => (unitPrice.value * quantity.value));
const serviceFees  = computed(() => productTotal.value * 0.015);
const shippingCost = computed(() => 320);
const taxes        = computed(() => productTotal.value * 0.0);
const grandTotal   = computed(() => (productTotal.value + serviceFees.value + shippingCost.value + taxes.value)
    .toLocaleString('en-US', { minimumFractionDigits: 2 }));

/* ── Static data ────────────────────────────────────────────────────────── */
const progressSteps = [
    { label: 'Order Confirmed',     done: true  },
    { label: 'Payment Verified',    done: true  },
    { label: 'Processing',          done: false, active: true  },
    { label: 'Export Preparation',  done: false },
    { label: 'Shipment',            done: false },
    { label: 'Delivered',           done: false },
];

const nextSteps = [
    'Seller confirms lot availability and reserves stock.',
    'Export preparation begins at bonded warehouse.',
    'Phytosanitary and customs documentation issued.',
    'Shipment tracking number shared via email.',
    'Coffee arrives at destination and is released.',
];

const documents = [
    { name: 'Purchase Invoice',       icon: 'invoice' },
    { name: 'Quality Certificate',    icon: 'quality' },
    { name: 'Sustainability Report',  icon: 'sustain' },
    { name: 'Traceability Report',    icon: 'trace'   },
    { name: 'Export Documentation',   icon: 'export'  },
];

const similarLots = computed(() => props.similar.length ? props.similar : [
    { id: 2, code: 'LOT-2026-002', name: 'Sipi Falls Washed AA',  origin: 'Kapchorwa, Uganda', price: '11.80', score: '86.2' },
    { id: 3, code: 'LOT-2026-003', name: 'Mt. Elgon Natural',     origin: 'Mbale, Uganda',     price: '12.20', score: '85.5' },
    { id: 4, code: 'LOT-2026-004', name: 'Rwenzori Highland',     origin: 'Kasese, Uganda',    price: '13.00', score: '88.0' },
]);

const fabOpen = ref(false);
</script>

<template>
    <AppLayout title="Order Confirmed" full-width flush :show-banner="false">
        <div class="oc-root">

            <!-- ── Success Hero ──────────────────────────────────────────── -->
            <section class="oc-hero">
                <div class="oc-hero-inner text-center">
                    <div class="oc-success-ring mx-auto mb-4">
                        <div class="oc-success-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                    </div>
                    <h1 class="oc-hero-title">Order Successfully Placed</h1>
                    <p class="oc-hero-sub">Your coffee purchase has been confirmed and is now being processed.</p>
                    <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                        <span class="oc-badge oc-badge--green">Paid</span>
                        <span class="oc-badge oc-badge--green">Confirmed</span>
                        <span class="oc-badge oc-badge--amber">Processing</span>
                        <span class="oc-badge">Order: {{ orderId }}</span>
                        <span class="oc-badge">{{ purchaseDate }}</span>
                    </div>
                </div>
            </section>

            <!-- ── Body ─────────────────────────────────────────────────── -->
            <div class="oc-body">
                <div class="row g-4">

                    <!-- Left column -->
                    <div class="col-lg-4 col-12">

                        <!-- Order Summary -->
                        <div class="oc-card mb-4">
                            <div class="oc-card-head">
                                <h6 class="oc-card-title">Order Summary</h6>
                            </div>
                            <div class="oc-card-body">
                                <table class="oc-table w-100">
                                    <tbody>
                                        <tr><td>Order ID</td><td><strong>{{ orderId }}</strong></td></tr>
                                        <tr><td>Lot</td><td><strong>{{ lotCode }}</strong></td></tr>
                                        <tr><td>Seller</td><td><strong>{{ sellerId }}</strong></td></tr>
                                        <tr><td>Quantity</td><td><strong>{{ quantity.toLocaleString() }} kg</strong></td></tr>
                                        <tr><td>Bags</td><td><strong>{{ bags }} × 60 kg</strong></td></tr>
                                        <tr><td>Unit Price</td><td><strong>Shs. {{ unitPrice.toFixed(2) }}/kg</strong></td></tr>
                                        <tr><td>Total</td><td><strong class="oc-accent">Shs. {{ totalAmount }}</strong></td></tr>
                                        <tr><td>Payment</td><td><strong>{{ paymentMethod }}</strong></td></tr>
                                        <tr><td>Status</td>
                                            <td><span class="oc-badge oc-badge--green oc-badge--sm">{{ paymentStatus }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Purchased Lot -->
                        <div class="oc-card mb-4">
                            <img :src="lot.image || lotImage" :alt="lotName" class="oc-lot-img" />
                            <div class="oc-card-body">
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="oc-badge oc-badge--green oc-badge--sm">Verified</span>
                                    <span class="oc-badge oc-badge--sm">Traceable</span>
                                    <span class="oc-badge oc-badge--sm">Export Ready</span>
                                    <span class="oc-badge oc-badge--amber oc-badge--sm">Sustainable</span>
                                </div>
                                <h6 class="oc-lot-name">{{ lotName }}</h6>
                                <p class="oc-lot-meta">{{ origin }} · {{ coffeeType }}</p>
                                <div class="oc-lot-kv-grid">
                                    <div class="oc-lot-kv">
                                        <span>Process</span>
                                        <strong>{{ process }}</strong>
                                    </div>
                                    <div class="oc-lot-kv">
                                        <span>Score</span>
                                        <strong class="oc-accent">{{ qualityScore }} SCA</strong>
                                    </div>
                                    <div class="oc-lot-kv">
                                        <span>Season</span>
                                        <strong>{{ season }}</strong>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <Link :href="route('lot.show', lot.id || 1)" class="oc-btn oc-btn--outline oc-btn--sm flex-fill text-center">View Lot Profile</Link>
                                    <button class="oc-btn oc-btn--ghost oc-btn--sm flex-fill">Download Report</button>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Breakdown -->
                        <div class="oc-card">
                            <div class="oc-card-head">
                                <h6 class="oc-card-title">Payment Summary</h6>
                            </div>
                            <div class="oc-card-body">
                                <table class="oc-table w-100">
                                    <tbody>
                                        <tr>
                                            <td>Product Total</td>
                                            <td><strong>Shs. {{ productTotal.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Service Fees (1.5%)</td>
                                            <td><strong>Shs. {{ serviceFees.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Shipping Cost</td>
                                            <td><strong>Shs. {{ shippingCost.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="oc-payment-total">
                                    <span>Grand Total</span>
                                    <strong class="oc-accent">Shs. {{ grandTotal }}</strong>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right column -->
                    <div class="col-lg-8 col-12">

                        <!-- Order Progress Tracker -->
                        <div class="oc-card mb-4">
                            <div class="oc-card-head">
                                <h6 class="oc-card-title">Order Progress</h6>
                            </div>
                            <div class="oc-card-body">
                                <div class="oc-progress-track">
                                    <div v-for="(step, i) in progressSteps" :key="i"
                                        class="oc-pt-step"
                                        :class="{ 'oc-pt-step--done': step.done, 'oc-pt-step--active': step.active }">
                                        <div class="oc-pt-node">
                                            <svg v-if="step.done" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span v-else>{{ i + 1 }}</span>
                                        </div>
                                        <div v-if="i < progressSteps.length - 1" class="oc-pt-line"></div>
                                        <div class="oc-pt-body">
                                            <strong>{{ step.label }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping & Logistics -->
                        <div class="oc-card mb-4">
                            <div class="oc-card-head">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <h6 class="oc-card-title mb-0">Shipping &amp; Logistics</h6>
                                    <span class="oc-badge oc-badge--amber oc-badge--sm">In Preparation</span>
                                </div>
                            </div>
                            <div class="oc-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="oc-kv-block">
                                            <span class="oc-kv-label">Warehouse</span>
                                            <strong class="oc-kv-val">{{ warehouse }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="oc-kv-block">
                                            <span class="oc-kv-label">Shipping Method</span>
                                            <strong class="oc-kv-val">{{ shippingMethod }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="oc-kv-block">
                                            <span class="oc-kv-label">Destination</span>
                                            <strong class="oc-kv-val">{{ destination }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="oc-kv-block">
                                            <span class="oc-kv-label">Export Status</span>
                                            <strong class="oc-kv-val">Pending Clearance</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="oc-kv-block">
                                            <span class="oc-kv-label">Est. Dispatch</span>
                                            <strong class="oc-kv-val">{{ estimatedDispatch }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="oc-kv-block">
                                            <span class="oc-kv-label">Est. Delivery</span>
                                            <strong class="oc-kv-val">{{ estimatedDelivery }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR & Traceability -->
                        <div class="oc-card mb-4">
                            <div class="oc-card-head">
                                <h6 class="oc-card-title">QR &amp; Traceability</h6>
                            </div>
                            <div class="oc-card-body">
                                <div class="d-flex gap-4 align-items-start flex-wrap">
                                    <div class="oc-qr-block flex-shrink-0">
                                        <!-- QR SVG placeholder -->
                                        <svg width="120" height="120" viewBox="0 0 120 120" class="oc-qr-svg">
                                            <!-- finder top-left -->
                                            <rect x="8"  y="8"  width="34" height="34" rx="3" fill="none" stroke="#004532" stroke-width="4"/>
                                            <rect x="16" y="16" width="18" height="18" rx="1" fill="#004532"/>
                                            <!-- finder top-right -->
                                            <rect x="78" y="8"  width="34" height="34" rx="3" fill="none" stroke="#004532" stroke-width="4"/>
                                            <rect x="86" y="16" width="18" height="18" rx="1" fill="#004532"/>
                                            <!-- finder bottom-left -->
                                            <rect x="8"  y="78" width="34" height="34" rx="3" fill="none" stroke="#004532" stroke-width="4"/>
                                            <rect x="16" y="86" width="18" height="18" rx="1" fill="#004532"/>
                                            <!-- data dots -->
                                            <g fill="#004532">
                                                <rect x="52" y="8"  width="6" height="6"/>
                                                <rect x="62" y="8"  width="6" height="6"/>
                                                <rect x="52" y="18" width="6" height="6"/>
                                                <rect x="52" y="28" width="6" height="6"/>
                                                <rect x="62" y="28" width="6" height="6"/>
                                                <rect x="8"  y="52" width="6" height="6"/>
                                                <rect x="18" y="52" width="6" height="6"/>
                                                <rect x="28" y="52" width="6" height="6"/>
                                                <rect x="52" y="52" width="6" height="6"/>
                                                <rect x="62" y="52" width="6" height="6"/>
                                                <rect x="72" y="52" width="6" height="6"/>
                                                <rect x="82" y="52" width="6" height="6"/>
                                                <rect x="52" y="62" width="6" height="6"/>
                                                <rect x="72" y="62" width="6" height="6"/>
                                                <rect x="52" y="72" width="6" height="6"/>
                                                <rect x="62" y="72" width="6" height="6"/>
                                                <rect x="52" y="82" width="6" height="6"/>
                                                <rect x="62" y="92" width="6" height="6"/>
                                                <rect x="72" y="82" width="6" height="6"/>
                                                <rect x="82" y="82" width="6" height="6"/>
                                                <rect x="82" y="92" width="6" height="6"/>
                                                <rect x="92" y="72" width="6" height="6"/>
                                                <rect x="102" y="62" width="6" height="6"/>
                                                <rect x="92" y="52" width="6" height="6"/>
                                                <rect x="102" y="52" width="6" height="6"/>
                                                <rect x="28" y="62" width="6" height="6"/>
                                                <rect x="18" y="72" width="6" height="6"/>
                                                <rect x="8"  y="82" width="6" height="6"/>
                                                <rect x="28" y="82" width="6" height="6"/>
                                                <rect x="8"  y="92" width="6" height="6"/>
                                                <rect x="18" y="102" width="6" height="6"/>
                                                <rect x="38" y="102" width="6" height="6"/>
                                            </g>
                                        </svg>
                                        <p class="oc-qr-hint">Scan to verify authenticity</p>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="oc-trace-rows">
                                            <div class="oc-trace-row">
                                                <span class="oc-trace-label">Verification</span>
                                                <span class="oc-badge oc-badge--green oc-badge--sm">Verified</span>
                                            </div>
                                            <div class="oc-trace-row">
                                                <span class="oc-trace-label">Blockchain</span>
                                                <span class="oc-badge oc-badge--green oc-badge--sm">Recorded</span>
                                            </div>
                                            <div class="oc-trace-row">
                                                <span class="oc-trace-label">Traceability</span>
                                                <span class="oc-badge oc-badge--green oc-badge--sm">Full Chain</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-3">
                                            <button class="oc-btn oc-btn--outline oc-btn--sm">Download QR</button>
                                            <Link :href="route('lot.traceability', lot.id || 1)" class="oc-btn oc-btn--ghost oc-btn--sm">View Traceability</Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents & Certificates -->
                        <div class="oc-card mb-4">
                            <div class="oc-card-head">
                                <h6 class="oc-card-title">Documents &amp; Certificates</h6>
                            </div>
                            <div class="oc-card-body">
                                <div class="oc-docs-grid">
                                    <div v-for="doc in documents" :key="doc.name" class="oc-doc-row">
                                        <div class="oc-doc-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <polyline points="14 2 14 8 20 8"/>
                                            </svg>
                                        </div>
                                        <span class="oc-doc-name">{{ doc.name }}</span>
                                        <div class="oc-doc-actions">
                                            <button class="oc-doc-btn">View</button>
                                            <button class="oc-doc-btn oc-doc-btn--primary">Download</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- What Happens Next -->
                        <div class="oc-card mb-4">
                            <div class="oc-card-head">
                                <h6 class="oc-card-title">What Happens Next?</h6>
                            </div>
                            <div class="oc-card-body">
                                <div class="oc-steps">
                                    <div v-for="(step, i) in nextSteps" :key="i" class="oc-step">
                                        <div class="oc-step-num">{{ i + 1 }}</div>
                                        <p class="oc-step-text">{{ step }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Recommended Actions -->
                <div class="mt-2 mb-4">
                    <h6 class="oc-section-title mb-3">Recommended Actions</h6>
                    <div class="row g-3">
                        <div class="col-md-4 col-12">
                            <div class="oc-card oc-action-card">
                                <div class="oc-card-body">
                                    <div class="oc-action-icon oc-action-icon--green mb-3">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/></svg>
                                    </div>
                                    <h6 class="oc-action-title">Track Order</h6>
                                    <p class="oc-action-sub">Monitor shipment and processing status in real time.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="oc-card oc-action-card">
                                <div class="oc-card-body">
                                    <div class="oc-action-icon oc-action-icon--amber mb-3">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16h12V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    </div>
                                    <h6 class="oc-action-title">Download Documents</h6>
                                    <p class="oc-action-sub">Access certificates, quality reports and export docs.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="oc-card oc-action-card">
                                <div class="oc-card-body">
                                    <div class="oc-action-icon oc-action-icon--blue mb-3">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </div>
                                    <h6 class="oc-action-title">Buy More Coffee</h6>
                                    <p class="oc-action-sub">Explore available lots and opportunities on the market.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="oc-cta-row mb-5">
                    <button class="oc-btn oc-btn--primary">Track Order</button>
                    <Link :href="route('checkout.index')" class="oc-btn oc-btn--outline">View Orders</Link>
                    <Link :href="route('market.index')" class="oc-btn oc-btn--outline">Return to Market</Link>
                    <button class="oc-btn oc-btn--ghost">Download Invoice</button>
                </div>

                <!-- Related Lots -->
                <div class="mb-5">
                    <h6 class="oc-section-title mb-3">Related Lots</h6>
                    <div class="oc-similar-row">
                        <div v-for="sl in similarLots" :key="sl.code" class="oc-card oc-similar-card">
                            <img :src="sl.image || lotImage" :alt="sl.name" class="oc-similar-img" />
                            <div class="oc-card-body oc-card-body--sm">
                                <p class="oc-similar-code">{{ sl.code }}</p>
                                <h6 class="oc-similar-name">{{ sl.name }}</h6>
                                <p class="oc-similar-meta">{{ sl.origin }}</p>
                                <div class="oc-similar-stats">
                                    <div>
                                        <span class="oc-kv-label">From</span>
                                        <strong class="oc-accent d-block">Shs. {{ sl.price }}/kg</strong>
                                    </div>
                                    <div>
                                        <span class="oc-kv-label">Score</span>
                                        <strong class="d-block">{{ sl.score }} SCA</strong>
                                    </div>
                                </div>
                                <Link :href="route('lot.show', sl.id)" class="oc-btn oc-btn--primary oc-btn--sm w-100 d-block text-center mt-2">View Lot</Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── FAB ──────────────────────────────────────────────────────── -->
        <div class="oc-fab-wrap">
            <transition name="oc-fab-fade">
                <div v-if="fabOpen" class="oc-fab-menu">
                    <button class="oc-fab-item">Contact Seller</button>
                    <button class="oc-fab-item">Contact Support</button>
                    <button class="oc-fab-item">Ask Advisor</button>
                </div>
            </transition>
            <button class="oc-fab" @click="fabOpen = !fabOpen" aria-label="Quick actions">
                <svg v-if="!fabOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

    </AppLayout>
</template>

<style scoped>
.oc-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --on-primary:       #ffffff;
    --on-surface:       #191c1e;
    --on-surface-var:   #74777a;
    --surface-low:      #f2f4f6;
    --surface-high:     #e6e8ea;
    --primary-fixed:    #a6f2d1;
    --on-primary-fixed: #002116;
    --secondary-fixed:  #fedcbe;
    --on-secondary-fixed:#291806;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Hero ─────────────────────────────────────────────────────────────────── */
.oc-hero { background: #ffffff; border-bottom: 1px solid var(--surface-high); }
.oc-hero-inner { padding: 3rem 2rem 2.5rem; }
.oc-success-ring {
    width: 72px; height: 72px; border-radius: 50%;
    background: rgba(0,69,50,0.07); border: 2px solid rgba(0,69,50,0.18);
    display: flex; align-items: center; justify-content: center;
}
.oc-success-icon {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    display: flex; align-items: center; justify-content: center;
}
.oc-hero-title { font-size: 1.625rem; font-weight: 800; letter-spacing: -0.02em; color: var(--on-surface); margin: 0 0 0.5rem; }
.oc-hero-sub   { font-size: 0.9375rem; color: var(--on-surface-var); margin: 0; }

/* ── Body ─────────────────────────────────────────────────────────────────── */
.oc-body { padding: 2rem 2rem 4rem; }

/* ── Badges ───────────────────────────────────────────────────────────────── */
.oc-badge {
    display: inline-flex; align-items: center;
    background: var(--surface-low); color: var(--on-surface-var);
    border: 1px solid var(--surface-high);
    border-radius: 999px; font-size: 0.6875rem; font-weight: 700;
    letter-spacing: 0.05em; padding: 4px 12px; text-transform: uppercase;
}
.oc-badge--green { background: var(--primary-fixed); color: var(--on-primary-fixed); border-color: transparent; }
.oc-badge--amber { background: var(--secondary-fixed); color: var(--on-secondary-fixed); border-color: transparent; }
.oc-badge--sm    { font-size: 0.625rem; padding: 3px 9px; }

/* ── Cards ────────────────────────────────────────────────────────────────── */
.oc-card {
    background: #ffffff;
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
}
.oc-card-head {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--surface-high);
    display: flex; align-items: center;
}
.oc-card-body      { padding: 1.5rem; }
.oc-card-body--sm  { padding: 1rem; }
.oc-card-title {
    font-size: 0.875rem; font-weight: 800;
    color: var(--on-surface); margin: 0; letter-spacing: -0.01em;
}
.oc-section-title  { font-size: 0.875rem; font-weight: 800; color: var(--on-surface); letter-spacing: -0.01em; margin: 0; }

/* ── Order table ──────────────────────────────────────────────────────────── */
.oc-table { border-collapse: collapse; }
.oc-table td {
    font-size: 0.8125rem; padding: 9px 0;
    border-bottom: 1px solid var(--surface-high); vertical-align: middle;
}
.oc-table tr:last-child td { border-bottom: none; }
.oc-table td:first-child { color: var(--on-surface-var); width: 45%; }
.oc-table td:last-child  { text-align: right; }
.oc-table strong { font-weight: 600; color: var(--on-surface); }

/* ── Payment total ────────────────────────────────────────────────────────── */
.oc-payment-total {
    display: flex; justify-content: space-between; align-items: center;
    padding-top: 1rem; margin-top: 1rem;
    border-top: 2px solid var(--surface-high);
    font-size: 1rem; font-weight: 700;
}
.oc-payment-total strong { font-size: 1.125rem; font-weight: 800; }

/* ── Lot card ─────────────────────────────────────────────────────────────── */
.oc-lot-img { width: 100%; height: 180px; object-fit: cover; display: block; }
.oc-lot-name { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); margin: 0 0 4px; letter-spacing: -0.01em; }
.oc-lot-meta { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0 0 0.75rem; }
.oc-lot-kv-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 0;
    border-top: 1px solid var(--surface-high);
}
.oc-lot-kv {
    padding: 10px 0; display: flex; flex-direction: column; gap: 3px;
    font-size: 0.8125rem;
}
.oc-lot-kv:not(:last-child) { padding-right: 10px; border-right: 1px solid var(--surface-high); }
.oc-lot-kv + .oc-lot-kv { padding-left: 10px; }
.oc-lot-kv span   { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-var); }
.oc-lot-kv strong { font-weight: 700; color: var(--on-surface); }

/* ── KV block ─────────────────────────────────────────────────────────────── */
.oc-kv-block { display: flex; flex-direction: column; gap: 4px; }
.oc-kv-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-var); }
.oc-kv-val   { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }

/* ── Progress tracker ─────────────────────────────────────────────────────── */
.oc-progress-track { display: flex; align-items: flex-start; overflow-x: auto; }
.oc-pt-step {
    display: flex; flex-direction: column; align-items: center;
    flex: 1; position: relative; min-width: 80px;
}
.oc-pt-node {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; z-index: 1;
    background: var(--surface-high); color: var(--on-surface-var);
    font-size: 12px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--surface-high);
}
.oc-pt-step--done .oc-pt-node   { background: var(--primary); color: #fff; border-color: var(--primary); }
.oc-pt-step--active .oc-pt-node {
    background: #fff; color: var(--primary); border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(0,69,50,0.15);
}
.oc-pt-line {
    position: absolute; top: 15px; left: 50%; right: -50%;
    height: 2px; background: var(--surface-high); z-index: 0;
}
.oc-pt-step--done .oc-pt-line { background: var(--primary); }
.oc-pt-body {
    margin-top: 10px; text-align: center; padding: 0 4px;
}
.oc-pt-body strong { font-size: 0.6875rem; font-weight: 700; color: var(--on-surface); line-height: 1.4; display: block; }

/* ── QR ───────────────────────────────────────────────────────────────────── */
.oc-qr-block { text-align: center; }
.oc-qr-svg   { display: block; margin: 0 auto; }
.oc-qr-hint  { font-size: 0.6875rem; color: var(--on-surface-var); margin-top: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.oc-trace-rows { display: flex; flex-direction: column; gap: 10px; }
.oc-trace-row  { display: flex; align-items: center; justify-content: space-between; }
.oc-trace-label { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Documents ────────────────────────────────────────────────────────────── */
.oc-docs-grid { display: flex; flex-direction: column; gap: 0; }
.oc-doc-row {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 0; border-bottom: 1px solid var(--surface-high);
}
.oc-doc-row:last-child { border-bottom: none; padding-bottom: 0; }
.oc-doc-icon {
    width: 34px; height: 34px; border-radius: 8px;
    background: var(--surface-low); color: var(--primary);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.oc-doc-name    { flex: 1; font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.oc-doc-actions { display: flex; gap: 6px; }
.oc-doc-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.75rem; font-weight: 700;
    border-radius: 0.375rem; padding: 5px 12px;
    cursor: pointer; border: 1px solid var(--surface-high);
    background: transparent; color: var(--on-surface-var);
    transition: background 0.12s ease;
}
.oc-doc-btn:hover { background: var(--surface-low); color: var(--on-surface); }
.oc-doc-btn--primary {
    background: var(--surface-low); color: var(--primary);
    border-color: var(--surface-high);
}
.oc-doc-btn--primary:hover { background: var(--primary-fixed); }

/* ── Next steps ───────────────────────────────────────────────────────────── */
.oc-steps { display: flex; flex-direction: column; gap: 14px; }
.oc-step  { display: flex; align-items: flex-start; gap: 12px; }
.oc-step-num {
    width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
    background: var(--surface-low); color: var(--primary);
    font-size: 0.75rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--surface-high);
}
.oc-step-text { font-size: 0.8125rem; color: var(--on-surface); margin: 0; line-height: 1.6; padding-top: 3px; }

/* ── Action cards ─────────────────────────────────────────────────────────── */
.oc-action-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.oc-action-icon--green { background: #f0fdf4; color: var(--primary); }
.oc-action-icon--amber { background: #fffbeb; color: #b45309; }
.oc-action-icon--blue  { background: #eff6ff; color: #1d4ed8; }
.oc-action-title { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); margin: 0 0 4px; }
.oc-action-sub   { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0; line-height: 1.5; }

/* ── CTA row ──────────────────────────────────────────────────────────────── */
.oc-cta-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }

/* ── Buttons ──────────────────────────────────────────────────────────────── */
.oc-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.875rem; font-weight: 700;
    border-radius: 0.5rem; padding: 10px 22px;
    cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    transition: opacity 0.15s ease, background 0.15s ease;
    border: none;
}
.oc-btn--primary  { background: linear-gradient(135deg, var(--primary), var(--primary-grad)); color: var(--on-primary); }
.oc-btn--primary:hover  { opacity: 0.9; }
.oc-btn--outline  { background: transparent; color: var(--on-surface); border: 1px solid #bec9c2; }
.oc-btn--outline:hover  { background: var(--surface-low); }
.oc-btn--ghost    { background: transparent; color: var(--on-surface-var); border: 1px solid var(--surface-high); }
.oc-btn--ghost:hover    { background: var(--surface-low); color: var(--on-surface); }
.oc-btn--sm       { font-size: 0.8125rem; padding: 7px 14px; }

/* ── Similar lots ─────────────────────────────────────────────────────────── */
.oc-similar-row  { display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none; }
.oc-similar-row::-webkit-scrollbar { display: none; }
.oc-similar-card { min-width: 220px; max-width: 220px; flex-shrink: 0; }
.oc-similar-img  { width: 100%; height: 120px; object-fit: cover; display: block; }
.oc-similar-code { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--primary); margin: 0 0 3px; }
.oc-similar-name { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); margin: 0 0 3px; }
.oc-similar-meta { font-size: 0.75rem; color: var(--on-surface-var); margin: 0 0 10px; }
.oc-similar-stats { display: flex; justify-content: space-between; font-size: 0.8125rem; }

/* ── Helpers ──────────────────────────────────────────────────────────────── */
.oc-accent { color: var(--primary) !important; }

/* ── FAB ──────────────────────────────────────────────────────────────────── */
.oc-fab-wrap { position: fixed; bottom: 28px; right: 28px; z-index: 400; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.oc-fab {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(0,69,50,0.3);
    transition: opacity 0.15s ease;
}
.oc-fab:hover { opacity: 0.9; }
.oc-fab-menu  { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
.oc-fab-item {
    background: #ffffff; color: var(--on-surface);
    border: 1px solid var(--surface-high); border-radius: 0.5rem;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem; font-weight: 700;
    padding: 9px 18px; cursor: pointer; white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: background 0.12s ease;
}
.oc-fab-item:hover { background: var(--surface-low); }
.oc-fab-fade-enter-active,
.oc-fab-fade-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.oc-fab-fade-enter-from,
.oc-fab-fade-leave-to { opacity: 0; transform: translateY(6px); }

/* ── Responsive ───────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .oc-hero-inner { padding: 2rem 1.25rem 2rem; }
    .oc-body { padding: 1.25rem 1.25rem 3rem; }
    .oc-lot-kv-grid { grid-template-columns: 1fr 1fr; }
}
</style>
