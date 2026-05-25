<script setup>
import { computed, ref, reactive } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Box, CollectionTag, TrendCharts, Location, Star, Medal,
    Upload, Picture, Delete, ChatDotRound, Opportunity,
    Check, Warning, Van, Connection, ShoppingCart, Money,
    Tickets, List, Clock, Edit, Plus,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// ── Form state ────────────────────────────────────────────────────────────────
const form = reactive({
    selectedType: 'lot',
    selectedId: '',
    saleType: 'fixed',
    quantity: '',
    unit: 'kg',
    price: '',
    minPrice: '',
    currency: 'USD',
    markets: [],
    buyerTypes: [],
    images: [],
    notes: '',
});

// ── Coffee inventory mock ─────────────────────────────────────────────────────
const coffeeOptions = {
    lot: [
        { id: 'LOT-2024-001', label: 'LOT-2024-001 — Arabica AA, Bugisu', origin: 'Bugisu, Uganda', type: 'Arabica', qty: 2400, quality: 91.5 },
        { id: 'LOT-2024-002', label: 'LOT-2024-002 — Robusta Screen 18, Masaka', origin: 'Masaka, Uganda', type: 'Robusta', qty: 1800, quality: 84.2 },
    ],
    batch: [
        { id: 'BAT-2024-015', label: 'BAT-2024-015 — Washed Arabica, Kasese', origin: 'Kasese, Uganda', type: 'Arabica', qty: 5000, quality: 88.0 },
    ],
    harvest: [
        { id: 'HRV-2024-007', label: 'HRV-2024-007 — Main Crop, Rwenzori', origin: 'Rwenzori, Uganda', type: 'Arabica', qty: 12000, quality: 87.5 },
    ],
};

const selectedItem = computed(() => {
    const list = coffeeOptions[form.selectedType] ?? [];
    return list.find((i) => i.id === form.selectedId) ?? null;
});

const badges = ['Verified', 'Export Ready', 'Traceable', 'Tokenised'];

const badgeClass = (badge) => {
    if (badge === 'Verified')     return 'bg-success-subtle text-success-emphasis border border-success-subtle';
    if (badge === 'Export Ready') return 'bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (badge === 'Traceable')    return 'bg-info-subtle text-info-emphasis border border-info-subtle';
    if (badge === 'Tokenised')    return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    return 'bg-light text-secondary border';
};

// ── Market prices ─────────────────────────────────────────────────────────────
const marketPrices = [
    { type: 'Robusta', price: '$2.34', change: '+1.2%', trend: 'up', weekly: '+3.4%' },
    { type: 'Arabica', price: '$3.87', change: '+0.8%', trend: 'up', weekly: '+2.1%' },
];

const demandRegions = [
    { region: 'UAE',  level: 92, tone: 'success' },
    { region: 'EU',   level: 78, tone: 'primary' },
    { region: 'USA',  level: 85, tone: 'info' },
    { region: 'Asia', level: 70, tone: 'warning' },
];

// ── Listing summary ───────────────────────────────────────────────────────────
const estimatedValue = computed(() => {
    const qty = Number(form.quantity || 0);
    const price = Number(form.price || 0);
    return qty && price ? (qty * price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—';
});

const saleTypeLabel = computed(() => ({
    fixed: 'Fixed Price',
    auction: 'Auction',
    rfo: 'Request for Offers',
}[form.saleType] ?? '—'));

// ── Recent listings ───────────────────────────────────────────────────────────
const recentListings = [
    { coffee: 'LOT-2023-088 Arabica AA', qty: '2,000 kg', price: '$3.90/kg', status: 'Active',  date: '2024-11-15' },
    { coffee: 'BAT-2023-031 Robusta S18', qty: '3,500 kg', price: '$2.40/kg', status: 'Sold',    date: '2024-10-28' },
    { coffee: 'HRV-2023-012 Main Crop',   qty: '5,000 kg', price: '$2.20/kg', status: 'Pending', date: '2024-10-10' },
    { coffee: 'LOT-2023-045 Arabica AB',  qty: '1,200 kg', price: '$3.50/kg', status: 'Auction', date: '2024-09-22' },
];

const statusClass = (s) => ({
    Active: 'bg-success-subtle text-success-emphasis',
    Sold: 'bg-secondary-subtle text-secondary-emphasis',
    Pending: 'bg-warning-subtle text-warning-emphasis',
    Auction: 'bg-primary-subtle text-primary-emphasis',
}[s] ?? 'bg-light text-secondary');

// ── Image mock ────────────────────────────────────────────────────────────────
const previewImages = ref([
    { id: 1, label: 'Primary', src: null },
    { id: 2, label: 'Additional 1', src: null },
    { id: 3, label: 'Additional 2', src: null },
]);

// ── Coffee overview (auto-populated) ─────────────────────────────────────────
const overviewSpecs = computed(() => {
    const item = selectedItem.value;
    return [
        { label: 'Coffee Type',       value: item?.type ?? '—' },
        { label: 'Origin',            value: item?.origin ?? '—' },
        { label: 'Processing Method', value: 'Washed' },
        { label: 'Harvest Season',    value: '2024 Main Crop' },
        { label: 'Moisture Content',  value: '11.2%' },
        { label: 'Quality Score',     value: item ? `${item.quality}` : '—' },
    ];
});

// ── Quality & sustainability ──────────────────────────────────────────────────
const qualityMetrics = [
    { label: 'Cupping Score', value: 88.5, max: 100 },
    { label: 'Bean Grade',    value: 95,   max: 100 },
    { label: 'Moisture',      value: 78,   max: 100 },
];

const sustainabilityMetrics = [
    { label: 'Sustainability Score', value: 82, max: 100, tone: 'success' },
    { label: 'Certification Status', badge: 'Fairtrade', tone: 'success' },
    { label: 'Traceability Status',  badge: 'Full Chain', tone: 'info' },
];

// ── Actions ───────────────────────────────────────────────────────────────────
const saving = ref(false);
const publishing = ref(false);

const publishListing = () => {
    publishing.value = true;
    setTimeout(() => { publishing.value = false; }, 1200);
};
const saveDraft = () => {
    saving.value = true;
    setTimeout(() => { saving.value = false; }, 800);
};
</script>

<template>
    <AppLayout title="Sell Coffee" full-width flush :show-banner="false">
        <Head title="Sell Coffee" />

        <div class="sc-page">

            <!-- ── Header ────────────────────────────────────────────────────── -->
            <div class="sc-header border-bottom">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 py-3">
                        <div>
                            <h1 class="sc-title mb-0">Sell Coffee</h1>
                            <p class="sc-subtitle mb-0">List your coffee on the marketplace and connect with buyers globally.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn sc-btn-outline btn-sm">
                                <el-icon><List /></el-icon> View My Listings
                            </button>
                            <button class="btn sc-btn-outline btn-sm">
                                <el-icon><TrendCharts /></el-icon> Market Prices
                            </button>
                            <button class="btn sc-btn-ghost btn-sm">
                                <el-icon><ChatDotRound /></el-icon> Ask Advisor
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Body ──────────────────────────────────────────────────────── -->
            <div class="container-fluid px-3 px-lg-4 py-4">
                <div class="row g-3 align-items-start">

                    <!-- ── Left Column (8) ───────────────────────────────────── -->
                    <div class="col-12 col-lg-8">

                        <!-- Coffee Selection Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--green">
                                        <el-icon><Box /></el-icon>
                                    </span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Select Coffee to Sell</h5>
                                        <p class="sc-card-sub mb-0">Choose a verified lot, batch, or harvest</p>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-sm-4">
                                        <label class="sc-label">Source Type</label>
                                        <select v-model="form.selectedType" class="form-select form-select-sm sc-select">
                                            <option value="lot">Lot</option>
                                            <option value="batch">Batch</option>
                                            <option value="harvest">Harvest</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-8">
                                        <label class="sc-label">Select Coffee</label>
                                        <select v-model="form.selectedId" class="form-select form-select-sm sc-select">
                                            <option value="">— Search and select —</option>
                                            <option v-for="opt in coffeeOptions[form.selectedType]" :key="opt.id" :value="opt.id">
                                                {{ opt.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Selected item details -->
                                <div v-if="selectedItem" class="sc-selected-detail rounded-3 p-3">
                                    <div class="row g-2 mb-2">
                                        <div class="col-6 col-sm-3">
                                            <div class="sc-meta-label">Name</div>
                                            <div class="sc-meta-value">{{ selectedItem.id }}</div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="sc-meta-label">Origin</div>
                                            <div class="sc-meta-value">{{ selectedItem.origin }}</div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="sc-meta-label">Type</div>
                                            <div class="sc-meta-value">{{ selectedItem.type }}</div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="sc-meta-label">Qty Available</div>
                                            <div class="sc-meta-value">{{ selectedItem.qty.toLocaleString() }} kg</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex flex-wrap gap-1">
                                            <span v-for="badge in badges" :key="badge" class="badge rounded-pill" :class="badgeClass(badge)" style="font-size:0.68rem;">
                                                {{ badge }}
                                            </span>
                                        </div>
                                        <div class="sc-quality-pill">
                                            <el-icon><Star /></el-icon> {{ selectedItem.quality }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="sc-empty-state rounded-3 text-center py-3">
                                    <el-icon class="text-muted" style="font-size:1.5rem;"><Box /></el-icon>
                                    <p class="text-muted mb-0 mt-1" style="font-size:0.8rem;">Select a coffee source above to continue</p>
                                </div>
                            </div>
                        </div>

                        <!-- Listing Details Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--amber">
                                        <el-icon><CollectionTag /></el-icon>
                                    </span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Listing Details</h5>
                                        <p class="sc-card-sub mb-0">Set your price, quantity, and sale terms</p>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="sc-label">Sale Type</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <label v-for="opt in [{v:'fixed',l:'Fixed Price'},{v:'auction',l:'Auction'},{v:'rfo',l:'Request for Offers'}]"
                                                :key="opt.v"
                                                class="sc-radio-pill"
                                                :class="{ 'sc-radio-pill--active': form.saleType === opt.v }">
                                                <input v-model="form.saleType" type="radio" :value="opt.v" class="d-none">
                                                {{ opt.l }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-4">
                                        <label class="sc-label">Quantity for Sale</label>
                                        <input v-model="form.quantity" type="number" class="form-control form-control-sm sc-input" placeholder="e.g. 500">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label class="sc-label">Unit of Sale</label>
                                        <select v-model="form.unit" class="form-select form-select-sm sc-select">
                                            <option value="kg">Kg</option>
                                            <option value="bag">Bag</option>
                                            <option value="container">Container</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label class="sc-label">Currency</label>
                                        <select v-model="form.currency" class="form-select form-select-sm sc-select">
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                            <option value="UGX">UGX</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-sm-6">
                                        <label class="sc-label">Asking Price (per {{ form.unit }})</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text sc-addon">{{ form.currency }}</span>
                                            <input v-model="form.price" type="number" class="form-control sc-input" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-6">
                                        <label class="sc-label">Minimum Acceptable Price</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text sc-addon">{{ form.currency }}</span>
                                            <input v-model="form.minPrice" type="number" class="form-control sc-input" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Marketplace Visibility Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--blue">
                                        <el-icon><Connection /></el-icon>
                                    </span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Marketplace Visibility</h5>
                                        <p class="sc-card-sub mb-0">Choose who can see your listing</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="sc-label mb-2">Market Access</label>
                                        <div class="d-flex flex-column gap-1">
                                            <label v-for="m in ['Local Market','Regional Market','Global Market']" :key="m" class="sc-check-row">
                                                <input v-model="form.markets" type="checkbox" :value="m" class="form-check-input me-2">
                                                {{ m }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="sc-label mb-2">Buyer Types</label>
                                        <div class="d-flex flex-column gap-1">
                                            <label v-for="b in ['Roasters','Exporters','Importers','Investors']" :key="b" class="sc-check-row">
                                                <input v-model="form.buyerTypes" type="checkbox" :value="b" class="form-check-input me-2">
                                                {{ b }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coffee Overview Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--teal">
                                        <el-icon><Tickets /></el-icon>
                                    </span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Coffee Overview</h5>
                                        <p class="sc-card-sub mb-0">Auto-populated from selected coffee</p>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div v-for="spec in overviewSpecs" :key="spec.label" class="col-6 col-sm-4">
                                        <div class="sc-spec-cell">
                                            <div class="sc-spec-label">{{ spec.label }}</div>
                                            <div class="sc-spec-value">{{ spec.value }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quality & Sustainability Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--green">
                                        <el-icon><Medal /></el-icon>
                                    </span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Quality & Sustainability</h5>
                                        <p class="sc-card-sub mb-0">Verified metrics and certifications</p>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <div class="sc-section-label mb-2">Quality</div>
                                        <div v-for="q in qualityMetrics" :key="q.label" class="mb-2">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="sc-metric-label">{{ q.label }}</span>
                                                <span class="sc-metric-value">{{ q.value }}</span>
                                            </div>
                                            <div class="progress sc-progress">
                                                <div class="progress-bar bg-success" :style="{ width: (q.value / q.max * 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="sc-section-label mb-2">Sustainability</div>
                                        <div v-for="s in sustainabilityMetrics" :key="s.label" class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="sc-metric-label">{{ s.label }}</span>
                                                <span v-if="s.badge" class="badge rounded-pill" :class="`bg-${s.tone}-subtle text-${s.tone}-emphasis`" style="font-size:0.65rem;">
                                                    {{ s.badge }}
                                                </span>
                                                <span v-else class="sc-metric-value">{{ s.value }}</span>
                                            </div>
                                            <div v-if="s.value" class="progress sc-progress">
                                                <div class="progress-bar" :class="`bg-${s.tone}`" :style="{ width: (s.value / s.max * 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Photo Gallery Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--amber">
                                        <el-icon><Picture /></el-icon>
                                    </span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Coffee Images</h5>
                                        <p class="sc-card-sub mb-0">Add photos to attract buyers</p>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div v-for="img in previewImages" :key="img.id" class="col-4">
                                        <div class="sc-image-slot rounded-3">
                                            <div v-if="!img.src" class="sc-image-slot__placeholder">
                                                <el-icon><Picture /></el-icon>
                                                <span>{{ img.label }}</span>
                                            </div>
                                            <img v-else :src="img.src" class="sc-image-slot__img" alt="Coffee image">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn sc-btn-outline btn-sm">
                                        <el-icon><Upload /></el-icon> Upload Images
                                    </button>
                                    <button type="button" class="btn sc-btn-ghost btn-sm text-danger">
                                        <el-icon><Delete /></el-icon> Remove Image
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button
                                        type="button"
                                        class="btn sc-btn-primary"
                                        :disabled="publishing"
                                        @click="publishListing"
                                    >
                                        <el-icon><Van /></el-icon>
                                        {{ publishing ? 'Publishing…' : 'Publish Listing' }}
                                    </button>
                                    <button type="button" class="btn sc-btn-outline btn-sm" :disabled="saving" @click="saveDraft">
                                        {{ saving ? 'Saving…' : 'Save Draft' }}
                                    </button>
                                    <button type="button" class="btn sc-btn-ghost btn-sm">Preview Listing</button>
                                    <button type="button" class="btn btn-link btn-sm text-danger ms-auto">Cancel</button>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Listings Section -->
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="sc-card-icon sc-card-icon--teal">
                                            <el-icon><Clock /></el-icon>
                                        </span>
                                        <h5 class="sc-card-title mb-0">Recent Listings</h5>
                                    </div>
                                    <button class="btn btn-link btn-sm p-0">View All</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm sc-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Coffee</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                                <th>Date Listed</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="listing in recentListings" :key="listing.coffee">
                                                <td class="sc-table-name">{{ listing.coffee }}</td>
                                                <td>{{ listing.qty }}</td>
                                                <td class="fw-medium">{{ listing.price }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" :class="statusClass(listing.status)" style="font-size:0.68rem;">
                                                        {{ listing.status }}
                                                    </span>
                                                </td>
                                                <td class="text-muted">{{ listing.date }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div><!-- /col-lg-8 -->

                    <!-- ── Right Sidebar (4) ──────────────────────────────────── -->
                    <div class="col-12 col-lg-4">

                        <!-- Listing Summary Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--green">
                                        <el-icon><ShoppingCart /></el-icon>
                                    </span>
                                    <h6 class="sc-card-title mb-0">Listing Summary</h6>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Coffee</span>
                                    <span class="sc-summary-value">{{ selectedItem?.id ?? '—' }}</span>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Quantity</span>
                                    <span class="sc-summary-value">{{ form.quantity ? form.quantity + ' ' + form.unit : '—' }}</span>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Asking Price</span>
                                    <span class="sc-summary-value fw-semibold">{{ form.price ? form.currency + ' ' + form.price : '—' }}</span>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Est. Value</span>
                                    <span class="sc-summary-value text-success fw-bold">{{ estimatedValue !== '—' ? form.currency + ' ' + estimatedValue : '—' }}</span>
                                </div>
                                <div class="sc-summary-row border-0">
                                    <span class="sc-summary-label">Sale Type</span>
                                    <span class="sc-summary-value">{{ saleTypeLabel }}</span>
                                </div>
                                <div v-if="form.markets.length" class="d-flex flex-wrap gap-1 mt-2">
                                    <span v-for="m in form.markets" :key="m" class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill" style="font-size:0.65rem;">{{ m }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Listing Preview Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3">
                                <h6 class="sc-card-title mb-3">Buyer Preview</h6>
                                <div class="sc-preview-card rounded-3 p-3">
                                    <div class="sc-preview-img rounded-2 mb-2">
                                        <el-icon class="text-muted" style="font-size:1.8rem;"><Picture /></el-icon>
                                    </div>
                                    <div class="sc-preview-name">{{ selectedItem?.id ?? 'Coffee Lot' }}</div>
                                    <div class="sc-preview-origin text-muted">{{ selectedItem?.origin ?? 'Origin, Country' }}</div>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <div class="sc-preview-price">{{ form.price ? form.currency + ' ' + form.price : '—' }}<span class="text-muted">/{{ form.unit }}</span></div>
                                        <div class="d-flex align-items-center gap-1 text-warning" style="font-size:0.75rem;">
                                            <el-icon><Star /></el-icon>
                                            <span>{{ selectedItem?.quality ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        <span v-for="badge in ['Verified','Traceable','Export Ready']" :key="badge"
                                            class="badge rounded-pill" :class="badgeClass(badge)" style="font-size:0.62rem;">
                                            {{ badge }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Market Price Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--amber">
                                        <el-icon><TrendCharts /></el-icon>
                                    </span>
                                    <h6 class="sc-card-title mb-0">Current Market Prices</h6>
                                </div>
                                <div v-for="mp in marketPrices" :key="mp.type" class="sc-price-row">
                                    <div class="sc-price-type">{{ mp.type }}</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="sc-price-value">{{ mp.price }}</div>
                                        <span class="badge rounded-pill" :class="mp.trend === 'up' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'" style="font-size:0.65rem;">
                                            {{ mp.change }}
                                        </span>
                                    </div>
                                    <div class="sc-price-weekly text-muted">Weekly: {{ mp.weekly }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Demand Insights Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="sc-card-icon sc-card-icon--blue">
                                        <el-icon><Location /></el-icon>
                                    </span>
                                    <h6 class="sc-card-title mb-0">Buyer Demand</h6>
                                </div>
                                <div v-for="d in demandRegions" :key="d.region" class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="sc-metric-label">{{ d.region }}</span>
                                        <span class="sc-metric-value">{{ d.level }}%</span>
                                    </div>
                                    <div class="progress sc-progress">
                                        <div class="progress-bar" :class="`bg-${d.tone}`" :style="{ width: d.level + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AI Pricing Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-3 sc-ai-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="sc-card-icon sc-card-icon--green">
                                        <el-icon><Opportunity /></el-icon>
                                    </span>
                                    <h6 class="sc-card-title mb-0">AI Pricing Recommendation</h6>
                                </div>
                                <div class="sc-ai-range mb-2">$3.60 – $3.95 <span class="text-muted">/kg</span></div>
                                <div class="d-flex gap-2 mb-3">
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill" style="font-size:0.65rem;">Competitive</span>
                                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill" style="font-size:0.65rem;">High Demand</span>
                                </div>
                                <p class="sc-ai-text mb-0">Current market conditions suggest pricing between <strong>$3.60</strong> and <strong>$3.95</strong> per kg for Arabica AA from this origin. Demand from UAE and EU buyers is elevated.</p>
                            </div>
                        </div>

                    </div><!-- /col-lg-4 -->

                </div><!-- /row -->
            </div><!-- /container-fluid -->

        </div><!-- /sc-page -->

        <!-- ── Floating Actions ───────────────────────────────────────────── -->
        <div class="sc-fab-group">
            <button class="btn sc-fab" title="Contact Support">
                <el-icon><ChatDotRound /></el-icon>
            </button>
            <button class="btn sc-fab sc-fab--primary" title="Ask Market Advisor">
                <el-icon><Opportunity /></el-icon>
            </button>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ── Page shell ──────────────────────────────────────────────────────────── */
.sc-page {
    background: #fff;
    min-height: 100vh;
    padding-bottom: 5rem;
}

/* ── Header ──────────────────────────────────────────────────────────────── */
.sc-header { background: #fff; }
.sc-title { font-size: 1.25rem; font-weight: 700; color: #1a1a2e; }
.sc-subtitle { font-size: 0.8rem; color: #6c757d; }

/* ── Buttons ─────────────────────────────────────────────────────────────── */
.sc-btn-primary {
    background: #2d6a4f;
    border-color: #2d6a4f;
    color: #fff;
    font-size: 0.82rem;
    font-weight: 600;
    border-radius: 8px;
    padding: 0.45rem 1.1rem;
}
.sc-btn-primary:hover { background: #1b4332; border-color: #1b4332; color: #fff; }
.sc-btn-primary:disabled { opacity: 0.6; }
.sc-btn-outline {
    background: transparent;
    border: 1px solid #dee2e6;
    color: #495057;
    font-size: 0.78rem;
    border-radius: 8px;
}
.sc-btn-outline:hover { background: #f8f9fa; border-color: #adb5bd; }
.sc-btn-ghost {
    background: transparent;
    border: none;
    color: #6c757d;
    font-size: 0.78rem;
}
.sc-btn-ghost:hover { background: #f8f9fa; }

/* ── Card icon badges ────────────────────────────────────────────────────── */
.sc-card-icon {
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    font-size: 0.95rem;
    flex-shrink: 0;
}
.sc-card-icon--green { background: #d8f3dc; color: #2d6a4f; }
.sc-card-icon--amber { background: #fff3cd; color: #856404; }
.sc-card-icon--blue  { background: #cfe2ff; color: #0a58ca; }
.sc-card-icon--teal  { background: #d1ecf1; color: #0c525d; }

/* ── Card typography ─────────────────────────────────────────────────────── */
.sc-card-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; }
.sc-card-sub   { font-size: 0.73rem; color: #6c757d; }
.sc-section-label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #6c757d; }

/* ── Form controls ───────────────────────────────────────────────────────── */
.sc-label {
    font-size: 0.73rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
    display: block;
}
.sc-input, .sc-select {
    font-size: 0.82rem;
    border-radius: 8px;
    border-color: #dee2e6;
    color: #212529;
}
.sc-input:focus, .sc-select:focus {
    border-color: #2d6a4f;
    box-shadow: 0 0 0 0.15rem rgba(45,106,79,.15);
}
.sc-addon {
    background: #f8f9fa;
    font-size: 0.78rem;
    border-color: #dee2e6;
    color: #6c757d;
}

/* ── Radio pills ─────────────────────────────────────────────────────────── */
.sc-radio-pill {
    cursor: pointer;
    padding: 0.3rem 0.8rem;
    font-size: 0.78rem;
    border-radius: 20px;
    border: 1px solid #dee2e6;
    color: #495057;
    background: #fff;
    user-select: none;
    transition: all .15s;
}
.sc-radio-pill--active {
    background: #2d6a4f;
    border-color: #2d6a4f;
    color: #fff;
}

/* ── Checkbox rows ───────────────────────────────────────────────────────── */
.sc-check-row {
    display: flex;
    align-items: center;
    font-size: 0.82rem;
    color: #495057;
    cursor: pointer;
}

/* ── Selected detail panel ───────────────────────────────────────────────── */
.sc-selected-detail { background: #f8fdf9; border: 1px solid #d8f3dc; }
.sc-empty-state { background: #f8f9fa; border: 1px dashed #dee2e6; }
.sc-meta-label { font-size: 0.68rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.sc-meta-value { font-size: 0.82rem; font-weight: 600; color: #1a1a2e; }
.sc-quality-pill {
    display: inline-flex; align-items: center; gap: 4px;
    background: #fff3cd; color: #856404;
    border-radius: 20px; padding: 2px 10px;
    font-size: 0.75rem; font-weight: 700;
}

/* ── Spec grid ───────────────────────────────────────────────────────────── */
.sc-spec-cell {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0.5rem 0.65rem;
}
.sc-spec-label { font-size: 0.67rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px; }
.sc-spec-value { font-size: 0.82rem; font-weight: 600; color: #1a1a2e; }

/* ── Progress bars ───────────────────────────────────────────────────────── */
.sc-progress { height: 6px; border-radius: 3px; background: #e9ecef; }

/* ── Metric labels ───────────────────────────────────────────────────────── */
.sc-metric-label { font-size: 0.78rem; color: #495057; }
.sc-metric-value { font-size: 0.78rem; font-weight: 600; color: #1a1a2e; }

/* ── Image slots ─────────────────────────────────────────────────────────── */
.sc-image-slot {
    aspect-ratio: 1 / 1;
    background: #f8f9fa;
    border: 1.5px dashed #dee2e6;
    overflow: hidden;
}
.sc-image-slot__placeholder {
    height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
    color: #adb5bd;
    font-size: 0.7rem;
}
.sc-image-slot__img { width: 100%; height: 100%; object-fit: cover; }

/* ── Sidebar: listing summary ────────────────────────────────────────────── */
.sc-summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.45rem 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.8rem;
}
.sc-summary-label { color: #6c757d; }
.sc-summary-value { color: #1a1a2e; font-weight: 500; }

/* ── Sidebar: preview card ───────────────────────────────────────────────── */
.sc-preview-card { background: #f8f9fa; }
.sc-preview-img {
    height: 80px;
    background: #e9ecef;
    display: flex; align-items: center; justify-content: center;
}
.sc-preview-name { font-size: 0.85rem; font-weight: 700; color: #1a1a2e; }
.sc-preview-origin { font-size: 0.73rem; }
.sc-preview-price { font-size: 0.9rem; font-weight: 700; color: #2d6a4f; }

/* ── Sidebar: market prices ──────────────────────────────────────────────── */
.sc-price-row { padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; }
.sc-price-row:last-child { border-bottom: none; }
.sc-price-type { font-size: 0.75rem; font-weight: 700; color: #495057; text-transform: uppercase; letter-spacing: 0.04em; }
.sc-price-value { font-size: 1rem; font-weight: 800; color: #1a1a2e; }
.sc-price-weekly { font-size: 0.68rem; margin-top: 2px; }

/* ── AI card ─────────────────────────────────────────────────────────────── */
.sc-ai-card { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); }
.sc-ai-range { font-size: 1.3rem; font-weight: 800; color: #1a1a2e; }
.sc-ai-text { font-size: 0.78rem; color: #495057; line-height: 1.5; }

/* ── Table ───────────────────────────────────────────────────────────────── */
.sc-table { font-size: 0.8rem; }
.sc-table th {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: #6c757d; border-bottom-width: 1px;
    padding: 0.4rem 0.5rem;
}
.sc-table td { padding: 0.45rem 0.5rem; vertical-align: middle; color: #495057; }
.sc-table-name { color: #1a1a2e; font-weight: 600; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── FAB ─────────────────────────────────────────────────────────────────── */
.sc-fab-group {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    z-index: 1000;
}
.sc-fab {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: #fff;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
    color: #495057;
    font-size: 1rem;
    transition: all .15s;
}
.sc-fab:hover { background: #f8f9fa; box-shadow: 0 4px 12px rgba(0,0,0,.15); }
.sc-fab--primary { background: #2d6a4f; border-color: #2d6a4f; color: #fff; }
.sc-fab--primary:hover { background: #1b4332; }
</style>
