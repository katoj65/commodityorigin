<script setup>
import { computed, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    batch: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success ?? '');

const numericWeight = computed(() => Number(props.batch.net_weight_kg ?? 0));
const numericBags = computed(() => Number(props.batch.quantity_bags ?? 0));
const numericMoisture = computed(() => Number(props.batch.moisture_content));

const batchCode = computed(() => props.batch.batch_number || `BATCH-${props.batch.id}`);
const warehouseLabel = computed(() => props.batch.warehouse_location || 'Origin location pending');
const varietyLabel = computed(() => {
    if ((props.batch.notes || '').toLowerCase().includes('robusta')) {
        return 'Robusta';
    }

    return 'Heirloom';
});
const processLabel = computed(() => (
    Number.isFinite(numericMoisture.value) && numericMoisture.value > 0
        ? 'Washed / Wet Process'
        : 'Institutional Intake'
));
const scoreLabel = computed(() => {
    if (!Number.isFinite(numericMoisture.value)) {
        return '91.5';
    }

    const score = Math.max(85.4, Math.min(91.8, 91.8 - Math.abs(11.2 - numericMoisture.value) * 1.15));
    return score.toFixed(1);
});
const scoreTrend = computed(() => (
    Number(scoreLabel.value) >= 90 ? '+1.2% since morning' : '+0.6% since morning'
));
const heroCopy = computed(() => (
    props.batch.notes ||
    `Institutional coffee asset verified for transparency and quality through the Commodity Origin operating workflow.`
));
const volumeLabel = computed(() => {
    const bags = Number.isFinite(numericBags.value) && numericBags.value > 0 ? numericBags.value : 0;
    const kg = Number.isFinite(numericWeight.value) && numericWeight.value > 0 ? numericWeight.value : 0;
    const kgDisplay = Math.round(kg) === kg ? kg : Number(kg.toFixed(1));

    return `${bags} Bags (${kgDisplay}kg)`;
});
const marketRate = computed(() => {
    const rate = Number(scoreLabel.value) * 0.053 + 0.01;
    return `$${rate.toFixed(2)}`;
});
const priceTrend = computed(() => (
    Number(scoreLabel.value) >= 90 ? '+1.2% since morning' : '+0.8% since morning'
));
const lotNumber = computed(() => `BT-${String(props.batch.id).padStart(4, '0')}`);
const shippingEstimate = computed(() => {
    if (!Number.isFinite(numericWeight.value) || numericWeight.value <= 0) {
        return '10-14 Days';
    }

    return numericWeight.value >= 500 ? '14-21 Days' : '7-12 Days';
});
const availableStock = computed(() => `${Math.max(1, Math.round((numericBags.value || 6) * 0.75))}/${Math.max(numericBags.value || 6, 1)} Bags`);
const journeySteps = computed(() => [
    { label: 'Farm', detail: 'Harvest Complete', active: true },
    { label: 'Process', detail: 'Wet Milled', active: true },
    { label: 'Quality Check', detail: `Grading ${scoreLabel.value}`, active: true },
    { label: 'Storage', detail: 'In Warehouse', active: true },
    { label: 'Tokenization', detail: 'Pending', active: false },
]);
const activeJourneyStep = computed(() => Math.max(journeySteps.value.filter((step) => step.active).length - 1, 0));
const originDetails = computed(() => [
    { label: 'Farmer', value: page.props.auth?.user?.name || 'Assigned Operator' },
    { label: 'Altitude', value: numericWeight.value >= 600 ? '1,950 - 2,100m' : '1,780 - 1,950m' },
    { label: 'Lot Size', value: `${numericBags.value || 0} Bags` },
]);
const processDetails = computed(() => [
    { label: 'Method', value: processLabel.value },
    { label: 'Fermentation', value: Number.isFinite(numericMoisture.value) ? `${Math.max(18, Math.round(numericMoisture.value * 3.2))} Hours` : '36 Hours' },
    { label: 'Drying', value: numericWeight.value >= 500 ? 'Raised Beds' : 'Mechanical Finish' },
]);
const sustainabilityStats = computed(() => [
    { label: 'Carbon Footprint', value: (Math.max(0.62, (numericBags.value || 8) * 0.041)).toFixed(2), unit: 'kg CO2e' },
    { label: 'Water Intensity', value: Math.max(9.8, (numericWeight.value || 700) / 48).toFixed(1), unit: 'L/kg' },
    { label: 'Certifications', value: 'RFA', unit: 'Verified' },
    { label: 'Protocol', value: 'Organic', unit: 'Compliant' },
]);
const registryRows = computed(() => [
    {
        label: 'Registry Info',
        values: [
            'Protocol: Commodity Origin v2.4',
            `Hash: ${lotNumber.value}-${props.batch.id}`,
            `Issued: ${props.batch.created_at ? new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(props.batch.created_at)) : 'Sep 12, 2023'}`,
        ],
    },
    {
        label: 'Laboratory',
        values: [
            'Tester: Internal QA Desk',
            `Moisture: ${Number.isFinite(numericMoisture.value) ? `${numericMoisture.value}%` : 'Pending'}`,
            `Water Activity: ${Number.isFinite(numericMoisture.value) ? (numericMoisture.value / 20).toFixed(2) : '0.58'}`,
        ],
    },
    {
        label: 'Logistics',
        values: [
            'Port: Mombasa',
            'Incoterms: FOB',
            'Vessel: Trace Voyager',
        ],
    },
    {
        label: 'System Status',
        values: ['Blockchain Sync Active'],
    },
]);

let lastShownSuccess = '';

watch(
    flashSuccess,
    (message) => {
        if (!message || message === lastShownSuccess) {
            return;
        }

        lastShownSuccess = message;

        ElNotification({
            title: 'Batch Created',
            message,
            type: 'success',
            duration: 3200,
            offset: 84,
        });
    },
    { immediate: true },
);
</script>

<template>
    <AppLayout :title="batchCode">
        <Head :title="batchCode" />

        <div class="batch-dashboard-page">
            <div class="batch-dashboard-shell">
                <main class="batch-dashboard-main">
                    <section class="batch-dashboard-head">
                        <div>
                            <div class="batch-dashboard-head__badge">Premium Grade</div>
                            <h1 class="batch-dashboard-head__title">{{ batchCode }}</h1>
                            <p class="batch-dashboard-head__copy">{{ heroCopy }}</p>
                        </div>

                        <div class="batch-dashboard-head__stats">
                            <div class="batch-dashboard-head__stat">
                                <div class="batch-dashboard-head__label">SCAA Score</div>
                                <div class="batch-dashboard-head__value">{{ scoreLabel }}</div>
                            </div>
                            <div class="batch-dashboard-head__stat">
                                <div class="batch-dashboard-head__label">Variety</div>
                                <div class="batch-dashboard-head__value">{{ varietyLabel }}</div>
                            </div>
                        </div>
                    </section>

                    <section class="batch-tracker-card">
                        <div class="batch-card-kicker">Farm To Cup Journey Tracker</div>

                        <el-steps class="batch-tracker-steps" :active="activeJourneyStep" align-center finish-status="success">
                            <el-step
                                v-for="step in journeySteps"
                                :key="step.label"
                                :title="step.label"
                                :description="step.detail"
                                :status="step.active ? undefined : 'wait'"
                            />
                        </el-steps>
                    </section>

                    <section class="batch-info-grid">
                        <article class="batch-info-card">
                            <div class="batch-card-kicker">Originating Farm</div>

                            <div class="batch-origin-card">
                                <div class="batch-origin-card__thumb"></div>
                                <div>
                                    <div class="batch-origin-card__title">{{ warehouseLabel }}</div>
                                    <div class="batch-origin-card__meta">Gedeb Zone, Ethiopia</div>
                                </div>
                            </div>

                            <div class="batch-detail-list">
                                <div v-for="item in originDetails" :key="item.label" class="batch-detail-list__row">
                                    <span>{{ item.label }}</span>
                                    <strong>{{ item.value }}</strong>
                                </div>
                            </div>
                        </article>

                        <article class="batch-info-card">
                            <div class="batch-card-kicker">Technical Process</div>

                            <div class="batch-process-card__hero">
                                <div class="batch-process-card__label">Method</div>
                                <div class="batch-process-card__value">{{ processLabel }}</div>
                            </div>

                            <div class="batch-process-grid">
                                <div v-for="item in processDetails.slice(1)" :key="item.label" class="batch-process-grid__item">
                                    <div class="batch-process-grid__label">{{ item.label }}</div>
                                    <div class="batch-process-grid__value">{{ item.value }}</div>
                                </div>
                            </div>
                        </article>
                    </section>

                    <section class="batch-ledger-card">
                        <div class="batch-card-kicker batch-card-kicker--light">Sustainability Ledger</div>

                        <div class="batch-ledger-grid">
                            <div v-for="item in sustainabilityStats" :key="item.label" class="batch-ledger-grid__item">
                                <div class="batch-ledger-grid__label">{{ item.label }}</div>
                                <div class="batch-ledger-grid__value">{{ item.value }}</div>
                                <div class="batch-ledger-grid__unit">{{ item.unit }}</div>
                            </div>
                        </div>
                    </section>

                    <section class="batch-registry-grid">
                        <div v-for="group in registryRows" :key="group.label" class="batch-registry-grid__column">
                            <div class="batch-registry-grid__title">{{ group.label }}</div>
                            <div
                                v-for="value in group.values"
                                :key="value"
                                class="batch-registry-grid__row"
                                :class="{ 'batch-registry-grid__row--status': group.label === 'System Status' }"
                            >
                                {{ value }}
                            </div>
                        </div>
                    </section>
                </main>

                <aside class="batch-dashboard-side">
                    <section class="batch-terminal-card">
                        <div class="batch-terminal-card__title">Execution Terminal</div>

                        <div class="batch-terminal-card__price-label">Current Market Price</div>
                        <div class="batch-terminal-card__price">{{ marketRate }}<span>/lb</span></div>
                        <div class="batch-terminal-card__trend">{{ priceTrend }}</div>

                        <div class="batch-terminal-card__volume">
                            <div class="batch-terminal-card__volume-label">Bidding Volume (bags)</div>
                            <div class="batch-terminal-card__volume-value">{{ numericBags || 10 }}</div>
                        </div>

                        <button type="button" class="batch-terminal-card__button batch-terminal-card__button--primary">Place Bid</button>
                        <button type="button" class="batch-terminal-card__button batch-terminal-card__button--secondary">Request Sample</button>

                        <div class="batch-terminal-card__meta">
                            <div class="batch-terminal-card__meta-row">
                                <span>Lot #</span>
                                <strong>{{ lotNumber }}</strong>
                            </div>
                            <div class="batch-terminal-card__meta-row">
                                <span>Shipping Estimate</span>
                                <strong>{{ shippingEstimate }}</strong>
                            </div>
                            <div class="batch-terminal-card__meta-row">
                                <span>Available Stock</span>
                                <strong>{{ availableStock }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="batch-origin-map">
                        <div class="batch-origin-map__eyebrow">Geographical Origin</div>
                        <div class="batch-origin-map__title">{{ warehouseLabel }}</div>
                    </section>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.batch-dashboard-page {
    background: #ffffff;
}

.batch-dashboard-shell {
    max-width: 1260px;
    margin: 0 auto;
    padding: 16px 18px 24px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 260px;
    gap: 1.1rem;
    align-items: start;
}

.batch-dashboard-main,
.batch-dashboard-side {
    min-width: 0;
}

.batch-terminal-card__title {
    color: #17352b;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.batch-dashboard-main {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.batch-dashboard-head {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 1rem;
    align-items: end;
    padding: 8px 4px 2px;
}

.batch-dashboard-head__badge,
.batch-card-kicker,
.batch-registry-grid__title,
.batch-terminal-card__price-label,
.batch-terminal-card__volume-label,
.batch-origin-map__eyebrow {
    font-family: 'IBM Plex Mono', monospace;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.batch-dashboard-head__badge {
    display: inline-flex;
    padding: 4px 8px;
    border-radius: 999px;
    background: #dff4e8;
    color: #0b7a52;
    font-size: 10px;
    font-weight: 700;
}

.batch-dashboard-head__title {
    margin: 8px 0 0;
    color: #0d2c22;
    font-size: clamp(1.7rem, 2.8vw, 2.35rem);
    line-height: 1;
    font-weight: 800;
    letter-spacing: -0.03em;
}

.batch-dashboard-head__copy {
    max-width: 520px;
    margin: 10px 0 0;
    color: #6e7b78;
    font-size: 14px;
    line-height: 1.6;
}

.batch-dashboard-head__stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    min-width: 220px;
}

.batch-dashboard-head__label {
    color: #a3afac;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.batch-dashboard-head__value {
    margin-top: 6px;
    color: #0d2c22;
    font-size: 17px;
    font-weight: 800;
}

.batch-tracker-card,
.batch-info-card,
.batch-terminal-card,
.batch-origin-map {
    border: 1px solid #e6ece9;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
}

.batch-tracker-card {
    padding: 18px 18px 16px;
    background: #f7f9fa;
}

.batch-card-kicker {
    color: #4b5d59;
    font-size: 11px;
    font-weight: 700;
}

.batch-tracker-steps {
    margin-top: 18px;
}

:deep(.batch-tracker-steps .el-step__head.is-success),
:deep(.batch-tracker-steps .el-step__head.is-process) {
    color: #0b5b3f;
    border-color: #0b5b3f;
}

:deep(.batch-tracker-steps .el-step__head.is-wait) {
    color: #c7d1cd;
    border-color: #c7d1cd;
}

:deep(.batch-tracker-steps .el-step__line) {
    background-color: #d5ddda;
}

:deep(.batch-tracker-steps .el-step__icon) {
    width: 32px;
    height: 32px;
    border-width: 1px;
    background: #ffffff;
}

:deep(.batch-tracker-steps .el-step__icon-inner) {
    font-size: 13px;
    font-weight: 700;
}

:deep(.batch-tracker-steps .el-step__title) {
    margin-top: 4px;
    color: #16342a;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    line-height: 1.35;
}

:deep(.batch-tracker-steps .el-step__description) {
    margin-top: 4px;
    color: #95a19d;
    font-size: 10px;
    line-height: 1.45;
}

.batch-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.batch-info-card {
    padding: 16px;
}

.batch-origin-card {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr);
    gap: 12px;
    align-items: center;
    margin-top: 14px;
}

.batch-origin-card__thumb {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    background:
        linear-gradient(180deg, rgba(15, 91, 63, 0.18), rgba(15, 91, 63, 0.05)),
        linear-gradient(135deg, #9ac37d 0%, #d7e6b5 38%, #8eab73 100%);
}

.batch-origin-card__title {
    color: #17352b;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.1;
}

.batch-origin-card__meta {
    margin-top: 6px;
    color: #83918d;
    font-size: 13px;
    line-height: 1.45;
}

.batch-detail-list {
    margin-top: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.batch-detail-list__row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    color: #94a3af;
    font-size: 12px;
}

.batch-detail-list__row strong {
    color: #17352b;
    font-size: 13px;
}

.batch-process-card__hero {
    margin-top: 14px;
    border-radius: 10px;
    background: #f7f9fa;
    padding: 14px;
}

.batch-process-card__label,
.batch-process-grid__label {
    color: #a0ada9;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.batch-process-card__value {
    margin-top: 8px;
    color: #17352b;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.15;
}

.batch-process-grid {
    margin-top: 12px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.batch-process-grid__item {
    border-radius: 10px;
    background: #f7f9fa;
    padding: 14px;
}

.batch-process-grid__value {
    margin-top: 8px;
    color: #17352b;
    font-size: 18px;
    font-weight: 800;
}

.batch-ledger-card {
    padding: 18px 20px;
    border-radius: 12px;
    background: #0d4b35;
    color: #e7f6ee;
}

.batch-card-kicker--light {
    color: #bfd8ca;
}

.batch-ledger-grid {
    margin-top: 18px;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.batch-ledger-grid__label {
    color: #8dc0a6;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.batch-ledger-grid__value {
    margin-top: 8px;
    font-size: 28px;
    line-height: 1;
    font-weight: 800;
}

.batch-ledger-grid__unit {
    margin-top: 6px;
    color: #c6e7d6;
    font-size: 12px;
    font-weight: 600;
}

.batch-registry-grid {
    padding: 18px 0 6px;
    border-top: 1px solid #e8eeeb;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
}

.batch-registry-grid__title {
    color: #9aa7a3;
    font-size: 10px;
    font-weight: 700;
    margin-bottom: 10px;
}

.batch-registry-grid__row {
    color: #5f6c69;
    font-size: 12px;
    line-height: 1.8;
}

.batch-registry-grid__row--status {
    color: #0b8f5d;
    font-weight: 700;
}

.batch-dashboard-side {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.batch-terminal-card {
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.batch-terminal-card__price-label {
    margin-top: 4px;
    color: #9ea9a6;
    font-size: 10px;
    font-weight: 700;
}

.batch-terminal-card__price {
    margin-top: -2px;
    color: #0d2c22;
    font-size: 32px;
    line-height: 1;
    font-weight: 800;
}

.batch-terminal-card__price span {
    font-size: 14px;
    margin-left: 2px;
}

.batch-terminal-card__trend {
    margin-top: -4px;
    color: #0b8f5d;
    font-size: 12px;
    font-weight: 700;
}

.batch-terminal-card__volume {
    margin-top: 4px;
    border-radius: 10px;
    background: #f7f9fa;
    padding: 14px;
}

.batch-terminal-card__volume-label {
    color: #9ea9a6;
    font-size: 10px;
    font-weight: 700;
}

.batch-terminal-card__volume-value {
    margin-top: 8px;
    color: #17352b;
    font-size: 20px;
    font-weight: 800;
}

.batch-terminal-card__button {
    width: 100%;
    margin-top: 2px;
    border-radius: 8px;
    padding: 13px 14px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    cursor: pointer;
}

.batch-terminal-card__button--primary {
    border: 0;
    background: #0b5b3f;
    color: #ffffff;
}

.batch-terminal-card__button--secondary {
    border: 0;
    background: #ffe0cb;
    color: #8b5a35;
}

.batch-terminal-card__meta {
    margin-top: 6px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.batch-terminal-card__meta-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    color: #9aa7a3;
    font-size: 12px;
}

.batch-terminal-card__meta-row strong {
    color: #17352b;
}

.batch-origin-map {
    min-height: 140px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: end;
    background:
        linear-gradient(180deg, rgba(16, 45, 39, 0.08), rgba(13, 75, 53, 0.58)),
        linear-gradient(135deg, #e2ebe7 0%, #c7d6cf 45%, #8ea59b 100%);
}

.batch-origin-map__eyebrow {
    color: rgba(255, 255, 255, 0.82);
    font-size: 10px;
    font-weight: 700;
}

.batch-origin-map__title {
    margin-top: 6px;
    color: #ffffff;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.15;
}

@media (max-width: 1180px) {
    .batch-dashboard-shell {
        grid-template-columns: minmax(0, 1fr) 250px;
    }
}

@media (max-width: 900px) {
    .batch-dashboard-shell {
        grid-template-columns: 1fr;
        padding: 12px 8px 20px;
    }

    .batch-dashboard-head,
    .batch-info-grid,
    .batch-ledger-grid,
    .batch-registry-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .batch-dashboard-head,
    .batch-info-grid,
    .batch-ledger-grid,
    .batch-registry-grid,
    .batch-tracker {
        grid-template-columns: 1fr;
    }

    .batch-dashboard-head__stats {
        min-width: 0;
    }
}
</style>
