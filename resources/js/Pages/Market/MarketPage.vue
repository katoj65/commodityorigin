<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Box,
    Check,
    Checked,
    Collection,
    CollectionTag,
    Connection,
    Document,
    Location,
    Message,
    Opportunity,
    Pointer,
    Reading,
    Search,
    ShoppingTrolley,
    Star,
    Tickets,
    TrendCharts,
    Van,
    View,
    WarnTriangleFilled,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const lots = [
    {
        id: 'UG-9902',
        name: 'Misty Mountains Robusta',
        origin: 'Uganda',
        type: 'Robusta',
        process: 'Natural',
        qualityScore: 87.5,
        quantity: 18.5,
        pricePerKg: 4.1,
        demand: 'High',
        demandTone: 'success',
        badges: ['Verified', 'Export Ready', 'Tokenised'],
        seller: 'Misty Mountains Cooperative',
        availableLabel: '18.5 MT',
    },
    {
        id: 'ET-2481',
        name: 'Sidamo G2 Natural',
        origin: 'Ethiopia',
        type: 'Arabica',
        process: 'Washed',
        qualityScore: 89.2,
        quantity: 5,
        pricePerKg: 3.85,
        demand: 'Very High',
        demandTone: 'primary',
        badges: ['Verified', 'Export Ready', 'Premium'],
        seller: 'Sidamo Highlands Union',
        availableLabel: '5.0 MT',
    },
    {
        id: 'BR-1120',
        name: 'Cerrado Gold',
        origin: 'Brazil',
        type: 'Arabica',
        process: 'Pulped Natural',
        qualityScore: 84,
        quantity: 120,
        pricePerKg: 3.2,
        demand: 'Stable',
        demandTone: 'warning',
        badges: ['Verified'],
        seller: 'Cerrado Export Group',
        availableLabel: '120.0 MT',
    },
    {
        id: 'RW-4417',
        name: 'Kivu Crest Bourbon',
        origin: 'Rwanda',
        type: 'Arabica',
        process: 'Honey',
        qualityScore: 88.1,
        quantity: 12.5,
        pricePerKg: 5.05,
        demand: 'High',
        demandTone: 'success',
        badges: ['Verified', 'Export Ready'],
        seller: 'Lake Kivu Growers',
        availableLabel: '12.5 MT',
    },
    {
        id: 'VN-6604',
        name: 'Dak Lak Select',
        origin: 'Vietnam',
        type: 'Robusta',
        process: 'Washed',
        qualityScore: 85.4,
        quantity: 62,
        pricePerKg: 2.95,
        demand: 'Active',
        demandTone: 'info',
        badges: ['Verified', 'Tokenised'],
        seller: 'Dak Lak Commodity House',
        availableLabel: '62.0 MT',
    },
];

const featuredLots = [
    {
        title: 'Best Value Lot',
        lotId: 'ET-2481',
        lotName: 'Sidamo G2 Natural',
        detail: 'Ethiopia • 89.2 score • $3.85/kg',
        tone: 'mint',
        meta: 'Lowest landed cost',
        stat: '89.2',
        cta: 'Best entry',
    },
    {
        title: 'High Demand Lot',
        lotId: 'UG-9902',
        lotName: 'Misty Mountains Robusta',
        detail: 'Uganda • Strong UAE demand • $4.10/kg',
        tone: 'green',
        meta: 'UAE buyers active',
        stat: 'High',
        cta: 'Fast-moving',
    },
    {
        title: 'Premium Specialty Lot',
        lotId: 'RW-4417',
        lotName: 'Kivu Crest Bourbon',
        detail: 'Rwanda • Honey process • $5.05/kg',
        tone: 'amber',
        meta: 'Specialty premium',
        stat: '88.1',
        cta: 'Top cup score',
    },
];

const trendBars = [
    { label: 'Mon', arabica: 68, robusta: 52 },
    { label: 'Tue', arabica: 72, robusta: 56 },
    { label: 'Wed', arabica: 76, robusta: 60 },
    { label: 'Thu', arabica: 71, robusta: 66 },
    { label: 'Fri', arabica: 82, robusta: 70 },
    { label: 'Sat', arabica: 79, robusta: 73 },
];

const selectedLotId = ref('ET-2481');
const quantityToBuy = ref(1);
const currency = ref('USD ($)');
const paymentMethod = ref('Escrow Transfer');
const deliveryMarket = ref('UAE');
const orderType = ref('Market Buy');

const selectedLot = computed(() => lots.find((lot) => lot.id === selectedLotId.value) ?? lots[0]);

const summaryCards = computed(() => {
    const averagePrice = lots.reduce((sum, lot) => sum + lot.pricePerKg, 0) / lots.length;
    const highestDemand = lots.find((lot) => lot.demand === 'Very High')?.origin ?? 'Uganda';
    const exportReadyLots = lots.filter((lot) => lot.badges.includes('Export Ready')).length;

    return [
        {
            label: 'Available Lots',
            value: lots.length.toString().padStart(2, '0'),
            subtext: 'Live and trade-ready',
            icon: Box,
        },
        {
            label: 'Average Price',
            value: `$${averagePrice.toFixed(2)}/kg`,
            subtext: 'Across listed coffee lots',
            icon: CollectionTag,
        },
        {
            label: 'Highest Demand Origin',
            value: highestDemand,
            subtext: 'Buyer interest leading today',
            icon: Location,
        },
        {
            label: 'Export-Ready Lots',
            value: exportReadyLots.toString(),
            subtext: 'Verified for shipment',
            icon: Van,
        },
    ];
});

const subtotal = computed(() => selectedLot.value.pricePerKg * quantityToBuy.value * 1000);
const platformFee = computed(() => subtotal.value * 0.005);
const estimatedLogistics = computed(() => quantityToBuy.value * 165);
const totalPayable = computed(() => subtotal.value + platformFee.value + estimatedLogistics.value);

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(value);

const badgeClass = (badge) => {
    if (badge === 'Verified') return 'text-bg-success-subtle text-success-emphasis border border-success-subtle';
    if (badge === 'Export Ready') return 'text-bg-primary-subtle text-primary-emphasis border border-primary-subtle';
    if (badge === 'Tokenised') return 'text-bg-info-subtle text-info-emphasis border border-info-subtle';
    if (badge === 'Premium') return 'text-bg-warning-subtle text-warning-emphasis border border-warning-subtle';

    return 'text-bg-light text-body-secondary border';
};

const selectLot = (lotId) => {
    selectedLotId.value = lotId;
    quantityToBuy.value = 1;
};
</script>

<template>


    <AppLayout title="Coffee Market" full-width flush :show-banner="false">
        <section class="market-page bg-white">
            <div class="container-fluid px-3 px-lg-4 py-3 py-lg-4">
                <div class="row g-4 align-items-start">
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-4">
                            <div class="market-card p-3 p-lg-4">
                                <div class="d-flex flex-column gap-3 flex-lg-row justify-content-between align-items-lg-end">
                                    <div>
                                        <h1 class="market-title mb-1">Coffee Market</h1>
                                        <p class="market-subtitle mb-0">
                                            Buy verified, traceable, and export-ready coffee lots
                                        </p>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-light market-toolbar-btn">
                                            <el-icon><Tickets /></el-icon>
                                            <span>My Orders</span>
                                        </button>
                                        <button type="button" class="btn btn-light market-toolbar-btn">
                                            <el-icon><Star /></el-icon>
                                            <span>Watchlist</span>
                                        </button>
                                        <button type="button" class="btn btn-light market-toolbar-btn">
                                            <el-icon><Message /></el-icon>
                                            <span>Set Alert</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div
                                    v-for="card in summaryCards"
                                    :key="card.label"
                                    class="col-12 col-sm-6 col-xl-3"
                                >
                                    <div class="market-card market-summary-card h-100 p-3">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div>
                                                <div class="market-summary-label">{{ card.label }}</div>
                                                <div class="market-summary-value">{{ card.value }}</div>
                                                <div class="market-summary-subtext">{{ card.subtext }}</div>
                                            </div>
                                            <div class="market-icon-badge">
                                                <el-icon><component :is="card.icon" /></el-icon>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="market-insight-bar">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="market-insight-icon">
                                            <el-icon><Opportunity /></el-icon>
                                        </div>
                                        <div>
                                            <div class="market-insight-label">AI Market Insight</div>
                                            <div class="market-insight-text">
                                                Ugandan Robusta is showing strong buyer demand in UAE markets today.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge market-pill">High Demand</span>
                                        <span class="badge market-pill">Export Ready</span>
                                        <span class="badge market-pill">Buyer Momentum</span>
                                    </div>
                                </div>
                            </div>

                            <div class="market-card p-0 overflow-hidden">
                                <div class="border-bottom px-3 px-lg-4 py-3">
                                    <div class="d-flex flex-column gap-2 flex-lg-row justify-content-between align-items-lg-center">
                                        <div>
                                            <div class="market-section-heading mb-1">
                                                <span class="market-section-icon"><el-icon><Tickets /></el-icon></span>
                                                <h2 class="market-section-title mb-0">Coffee Lots</h2>
                                            </div>
                                            <p class="market-section-subtitle mb-0">Compact trading view for fast buyer decisions</p>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge rounded-pill text-bg-light border market-filter-badge">
                                                <el-icon><Location /></el-icon>
                                                <span>All Origins</span>
                                            </span>
                                            <span class="badge rounded-pill text-bg-light border market-filter-badge">
                                                <el-icon><Collection /></el-icon>
                                                <span>Arabica &amp; Robusta</span>
                                            </span>
                                            <span class="badge rounded-pill text-bg-light border market-filter-badge">
                                                <el-icon><Star /></el-icon>
                                                <span>Score 84+</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 market-table">
                                        <thead>
                                            <tr>
                                                <th>Lot ID</th>
                                                <th>Coffee Name</th>
                                                <th>Origin</th>
                                                <th>Profile</th>
                                                <th>Quantity</th>
                                                <th>Price / kg</th>
                                                <th>Market Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="lot in lots"
                                                :key="lot.id"
                                                :class="{ 'is-selected': selectedLot.id === lot.id }"
                                                @click="selectLot(lot.id)"
                                            >
                                                <td class="fw-semibold">#{{ lot.id }}</td>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ lot.name }}</div>
                                                    <div class="market-row-subtext">{{ lot.seller }}</div>
                                                </td>
                                                <td>{{ lot.origin }}</td>
                                                <td>
                                                    <div class="market-meta-cluster">
                                                        <span class="badge rounded-pill text-bg-light border market-profile-badge">
                                                            {{ lot.type }}
                                                        </span>
                                                        <span class="badge rounded-pill text-bg-light border market-profile-badge">
                                                            {{ lot.process }}
                                                        </span>
                                                        <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis border border-success-subtle market-profile-score">
                                                            {{ lot.qualityScore }}
                                                        </span>
                                                        <span class="market-inline-meta">Cup score</span>
                                                    </div>
                                                </td>
                                                <td>{{ lot.availableLabel }}</td>
                                                <td class="fw-semibold">{{ formatCurrency(lot.pricePerKg) }}</td>
                                                <td>
                                                    <div class="market-meta-cluster market-meta-cluster--status">
                                                        <span class="badge rounded-pill" :class="`text-bg-${lot.demandTone}-subtle text-${lot.demandTone}-emphasis border border-${lot.demandTone}-subtle`">
                                                            {{ lot.demand }}
                                                        </span>
                                                        <span
                                                            v-for="badge in lot.badges"
                                                            :key="badge"
                                                            class="badge rounded-pill market-status-badge"
                                                            :class="badgeClass(badge)"
                                                        >
                                                            {{ badge }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                                        <button type="button" class="btn btn-sm btn-success market-action-btn" @click.stop="selectLot(lot.id)">
                                                            <el-icon><Check /></el-icon>
                                                            <span>Select</span>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-light market-action-btn">
                                                            <el-icon><View /></el-icon>
                                                            <span>View</span>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-light market-action-btn">
                                                            <el-icon><Star /></el-icon>
                                                            <span>Watchlist</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                         
                        </div>
                    </div>

                    <div class="col-12 col-xxl-4">
                        <div class="market-sidebar-sticky">
                            <div class="market-card market-quick-buy p-3 p-lg-4">
                                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                    <div>
                                        <div class="market-sidebar-kicker">Quick Buy</div>
                                        <div class="market-section-heading">
                                            <span class="market-section-icon"><el-icon><ShoppingTrolley /></el-icon></span>
                                            <h2 class="market-sidebar-title mb-0">Checkout Panel</h2>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis border border-success-subtle">
                                        Selected
                                    </span>
                                </div>

                                <div class="market-selected-lot mb-3">
                                    <div class="fw-semibold text-dark">{{ selectedLot.name }}</div>
                                    <div class="market-row-subtext">Lot #{{ selectedLot.id }} • {{ selectedLot.seller }}</div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="market-detail-card">
                                            <div class="market-detail-label">Origin</div>
                                            <div class="market-detail-value">{{ selectedLot.origin }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="market-detail-card">
                                            <div class="market-detail-label">Coffee Type</div>
                                            <div class="market-detail-value">{{ selectedLot.type }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="market-detail-card">
                                            <div class="market-detail-label">Quality Score</div>
                                            <div class="market-detail-value">{{ selectedLot.qualityScore }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="market-detail-card">
                                            <div class="market-detail-label">Available Quantity</div>
                                            <div class="market-detail-value">{{ selectedLot.availableLabel }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="market-detail-card">
                                            <div class="market-detail-label">Price per kg</div>
                                            <div class="market-detail-value">{{ formatCurrency(selectedLot.pricePerKg) }}</div>
                                        </div>
                                    </div>
                                </div>

                                <el-form label-position="top" class="market-buy-form mt-1">
                                    <el-form-item label="Quantity to buy (MT)" class="market-buy-form__item">
                                        <el-input
                                            v-model="quantityToBuy"
                                            type="text"
                                            inputmode="decimal"
                                            placeholder="Enter quantity"
                                            class="market-control"
                                        />
                                    </el-form-item>

                                    <div class="row g-2">
                                        <div class="col-12 col-sm-6 col-xxl-12 col-xl-6">
                                            <el-form-item label="Currency" class="market-buy-form__item mb-0">
                                                <el-select v-model="currency" class="market-control" placeholder="Select currency">
                                                    <el-option label="USD ($)" value="USD ($)" />
                                                    <el-option label="EUR (€)" value="EUR (€)" />
                                                    <el-option label="AED (د.إ)" value="AED (د.إ)" />
                                                </el-select>
                                            </el-form-item>
                                        </div>
                                        <div class="col-12 col-sm-6 col-xxl-12 col-xl-6">
                                            <el-form-item label="Payment method" class="market-buy-form__item mb-0">
                                                <el-select v-model="paymentMethod" class="market-control" placeholder="Select payment method">
                                                    <el-option label="Escrow Transfer" value="Escrow Transfer" />
                                                    <el-option label="Bank Wire" value="Bank Wire" />
                                                    <el-option label="Trade Credit" value="Trade Credit" />
                                                </el-select>
                                            </el-form-item>
                                        </div>
                                        <div class="col-12 col-sm-6 col-xxl-12 col-xl-6">
                                            <el-form-item label="Delivery market" class="market-buy-form__item mb-0">
                                                <el-select v-model="deliveryMarket" class="market-control" placeholder="Select delivery market">
                                                    <el-option label="UAE" value="UAE" />
                                                    <el-option label="Germany" value="Germany" />
                                                    <el-option label="USA" value="USA" />
                                                </el-select>
                                            </el-form-item>
                                        </div>
                                        <div class="col-12 col-sm-6 col-xxl-12 col-xl-6">
                                            <el-form-item label="Order type" class="market-buy-form__item mb-0">
                                                <el-select v-model="orderType" class="market-control" placeholder="Select order type">
                                                    <el-option label="Market Buy" value="Market Buy" />
                                                    <el-option label="Limit Order" value="Limit Order" />
                                                </el-select>
                                            </el-form-item>
                                        </div>
                                    </div>
                                </el-form>

                                <div class="market-total-card mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="market-total-label"><el-icon><Document /></el-icon><span>Subtotal</span></span>
                                        <strong>{{ formatCurrency(subtotal) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="market-total-label"><el-icon><CollectionTag /></el-icon><span>Platform fee</span></span>
                                        <strong>{{ formatCurrency(platformFee) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="market-total-label"><el-icon><Van /></el-icon><span>Estimated logistics</span></span>
                                        <strong>{{ formatCurrency(estimatedLogistics) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                                        <span class="fw-semibold market-total-label"><el-icon><ShoppingTrolley /></el-icon><span>Total payable</span></span>
                                        <strong class="market-total-value">{{ formatCurrency(totalPayable) }}</strong>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-success market-cta-btn">
                                        <el-icon><ShoppingTrolley /></el-icon>
                                        <span>Buy Now</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-success market-cta-btn">
                                        <el-icon><Collection /></el-icon>
                                        <span>Place Limit Order</span>
                                    </button>
                                    <button type="button" class="btn btn-light market-cta-btn">
                                        <el-icon><Reading /></el-icon>
                                        <span>Request Sample</span>
                                    </button>
                                </div>

                                <div class="row g-2 mt-3">
                                    <div class="col-6">
                                        <div class="trust-badge">
                                            <el-icon><Check /></el-icon>
                                            <span>Secure Payment</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="trust-badge">
                                            <el-icon><Checked /></el-icon>
                                            <span>Verified Lot</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="trust-badge">
                                            <el-icon><Van /></el-icon>
                                            <span>Export Ready</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="trust-badge">
                                            <el-icon><Connection /></el-icon>
                                            <span>Blockchain Verified</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.market-page {
    font-family: 'Source Sans 3', sans-serif;
    color: #111827;
    line-height: 1.45;
}

.market-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.05;
    color: #111827;
}

.market-subtitle,
.market-section-subtitle,
.market-row-subtext,
.market-summary-subtext,
.market-detail-label,
.market-total-card span {
    color: #6b7280;
}

.market-subtitle,
.market-section-subtitle,
.market-row-subtext,
.market-summary-subtext,
.featured-lot-detail,
.featured-lot-meta,
.market-chart-note,
.market-total-card,
.trust-badge,
.alert-row {
    font-size: 0.8125rem;
    line-height: 1.5;
}

.market-card {
    background: #fff;
    border: 1px solid #edf1f5;
    border-radius: 20px;
    box-shadow: none;
}

.market-toolbar-btn,
.market-action-btn,
.market-cta-btn,
.market-control {
    border-radius: 12px;
}

.market-toolbar-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 0.9rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-color: #e9eef3;
    background: #fff;
}

.market-toolbar-btn .el-icon,
.market-action-btn .el-icon,
.market-cta-btn .el-icon {
    font-size: 1rem;
}

.market-summary-card {
    min-height: 128px;
}

.market-summary-label,
.market-sidebar-kicker,
.featured-lot-label,
.market-form-label,
.market-detail-label,
.market-insight-label,
.market-table thead th,
.market-alert-badge,
.featured-lot-chip,
.market-buy-form :deep(.el-form-item__label) {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.625rem;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #94a3b8;
}

.market-summary-value {
    margin-top: 0.35rem;
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.03em;
    color: #111827;
}

.market-icon-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 14px;
    background: #f3f8f5;
    color: #2d6a4f;
}

.market-icon-badge .el-icon,
.market-insight-icon .el-icon {
    font-size: 1.15rem;
}

.market-section-heading {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
}

.market-section-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.9rem;
    height: 1.9rem;
    border-radius: 999px;
    background: #f8fafc;
    color: #2d6a4f;
}

.market-section-icon .el-icon {
    font-size: 1rem;
}

.market-insight-bar {
    padding: 1rem 1.15rem;
    border-radius: 20px;
    background: linear-gradient(135deg, #2d6a4f 0%, #24553f 100%);
    box-shadow: none;
    color: #fff;
    border: 1px solid rgba(45, 106, 79, 0.12);
}

.market-insight-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.14);
}

.market-insight-label {
    opacity: 0.8;
}

.market-insight-text {
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1.45;
}

.market-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.7rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    color: #fff;
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.04em;
}

.market-section-title,
.market-sidebar-title {
    font-size: 1.125rem;
    font-weight: 700;
    line-height: 1.15;
    letter-spacing: -0.02em;
    color: #111827;
}

.market-table thead th {
    padding: 0.9rem 1rem;
    background: #f8fafc;
    border-bottom-color: #edf1f5;
    white-space: nowrap;
}

.market-table tbody td {
    padding: 0.95rem 1rem;
    vertical-align: middle;
    border-color: #edf1f5;
    font-size: 0.875rem;
}

.market-filter-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.7rem;
    color: #475569;
}

.market-filter-badge .el-icon {
    font-size: 0.95rem;
}

.market-table tbody tr {
    cursor: pointer;
    transition: background-color 0.2s ease, box-shadow 0.2s ease;
}

.market-table tbody tr:hover {
    background: #fbfcfd;
}

.market-table tbody tr.is-selected {
    background: #f5fbf8;
    box-shadow: inset 3px 0 0 #198754;
}

.market-status-badge {
    font-weight: 600;
}

.market-meta-cluster {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
    line-height: 1.35;
}

.market-meta-cluster--status {
    max-width: 18rem;
}

.market-profile-badge {
    color: #475569;
    font-size: 0.72rem;
    font-weight: 600;
}

.market-profile-score {
    font-weight: 700;
}

.market-inline-meta {
    color: #6b7280;
    font-size: 0.72rem;
    white-space: nowrap;
}

.market-action-btn,
.market-cta-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
}

.featured-lot {
    padding: 1rem;
    border: 1px solid #edf1f5;
    border-radius: 18px;
    min-height: 100%;
}

.featured-lot.is-mint {
    background: linear-gradient(180deg, #f6fbf8 0%, #ffffff 100%);
}

.featured-lot.is-green {
    background: linear-gradient(180deg, #f3f8f5 0%, #ffffff 100%);
}

.featured-lot.is-amber {
    background: linear-gradient(180deg, #fffaf1 0%, #ffffff 100%);
}

.featured-lot-name,
.market-detail-value,
.market-total-value {
    font-weight: 800;
    color: #111827;
}

.featured-lot-detail {
    margin-top: 0.25rem;
    color: #6b7280;
}

.featured-lot-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    align-items: center;
    margin-top: 0.7rem;
    color: #64748b;
    font-weight: 500;
}

.featured-lot-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.28rem 0.55rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #e4eaef;
    color: #30543f;
    letter-spacing: 0.08em;
}

.featured-lot-stat {
    flex-shrink: 0;
    min-width: 3.5rem;
    padding: 0.6rem 0.75rem;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #e4eaef;
    text-align: center;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #111827;
}

.market-mini-chart {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.55rem;
    align-items: end;
    min-height: 158px;
    padding: 0.75rem;
    border-radius: 16px;
    background: #f8fafc;
    border: 1px solid #edf1f5;
}

.market-mini-chart-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.market-bars {
    display: flex;
    align-items: end;
    justify-content: center;
    gap: 0.3rem;
    height: 108px;
}

.market-bar {
    display: inline-block;
    width: 0.6rem;
    border-radius: 999px 999px 0 0;
}

.market-bar.is-arabica {
    background: #198754;
}

.market-bar.is-robusta {
    background: #0dcaf0;
}

.market-legend-dot {
    width: 0.6rem;
    height: 0.6rem;
    border-radius: 50%;
    display: inline-block;
}

.market-chart-note {
    padding: 0.75rem 0.85rem;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid #edf1f5;
    color: #64748b;
    line-height: 1.45;
}

.market-inline-icon {
    margin-top: 0.1rem;
    color: #2d6a4f;
}

.market-inline-icon .el-icon {
    font-size: 1rem;
}

.alert-row,
.market-selected-lot,
.market-detail-card,
.trust-badge {
    padding: 0.9rem;
    border: 1px solid #edf1f5;
    border-radius: 16px;
    background: #fbfcfd;
}

.market-detail-card {
    height: 100%;
}

.alert-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.85rem;
}

.market-alert-badge {
    flex-shrink: 0;
    margin-top: 0.1rem;
    letter-spacing: 0.08em;
}

.market-control {
    min-height: 44px;
    border-color: #e8edf3;
    box-shadow: none;
    font-size: 0.875rem;
}

.market-control:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.12);
}

.market-buy-form :deep(.el-form-item) {
    margin-bottom: 0.9rem;
}

.market-buy-form :deep(.el-form-item__label) {
    padding-bottom: 0.45rem;
    line-height: 1.2;
}

.market-buy-form :deep(.el-input),
.market-buy-form :deep(.el-select) {
    width: 100%;
}

.market-buy-form :deep(.el-input .el-input__wrapper),
.market-buy-form :deep(.el-select .el-select__wrapper) {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 0 0 1px #e8edf3 inset;
    min-height: 44px;
    padding-left: 0.85rem;
    padding-right: 0.85rem;
}

.market-buy-form :deep(.el-input .el-input__wrapper.is-focus),
.market-buy-form :deep(.el-select .el-select__wrapper.is-focused),
.market-buy-form :deep(.el-select .el-select__wrapper:focus-within) {
    box-shadow: 0 0 0 1px #198754 inset, 0 0 0 0.2rem rgba(25, 135, 84, 0.12);
}

.market-total-card {
    padding: 1rem;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #edf1f5;
    font-size: 0.8125rem;
}

.market-total-label {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.market-total-label .el-icon {
    font-size: 0.95rem;
    color: #64748b;
}

.trust-badge {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    height: 100%;
    font-weight: 600;
    color: #304153;
}

.trust-badge .el-icon {
    color: #198754;
}

.market-sidebar-sticky {
    position: sticky;
    top: 88px;
}

:deep(.fab-shell) {
    display: none;
}

@media (max-width: 1399.98px) {
    .market-sidebar-sticky {
        position: static;
    }
}

@media (max-width: 991.98px) {
    .market-title {
        font-size: 1.35rem;
    }

    .market-card,
    .market-insight-bar {
        border-radius: 18px;
    }

    .market-table thead th,
    .market-table tbody td {
        padding-left: 0.8rem;
        padding-right: 0.8rem;
    }

    .featured-lot-stat {
        min-width: 3.1rem;
        padding-inline: 0.65rem;
    }
}

@media (max-width: 575.98px) {
    .alert-row {
        flex-direction: column;
        align-items: stretch;
    }

    .market-alert-badge {
        align-self: flex-start;
    }
}
</style>
