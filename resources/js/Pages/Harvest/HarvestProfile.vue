<script setup>
import { computed, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    harvest: {
        type: Object,
        required: true,
    },
    dateRange: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');

const scoreByGrade = {
    'Premium Red Cherry': 91.5,
    'Mostly Red Cherry': 88.4,
    'Mixed Ripeness': 84.2,
    'Under-ripe Screening': 79.6,
};

const harvestCode = computed(() => {
    const year = props.harvest.harvest_date
        ? new Date(props.harvest.harvest_date).getFullYear()
        : new Date().getFullYear();

    return `Batch #${year}-${String(props.harvest.id).padStart(2, '0')}X${String(props.harvest.farm_id).padStart(2, '0')}`;
});

const farmName = computed(() => props.harvest.farm?.name || `${props.harvest.id}`);
const farmLocation = computed(() => props.harvest.farm?.location || 'Origin pending');
const farmerName = computed(() => props.harvest.farm?.farmer_name || 'Assigned field operator');
const qualityScore = computed(() => scoreByGrade[props.harvest.ripeness_grade] ?? 82.8);
const varietyLabel = computed(() => props.harvest.variety || props.harvest.farm?.variety || 'Heirloom');
const volumeKg = computed(() => Number(props.harvest.weight || 0));
const bagCount = computed(() => Math.max(1, Math.round(volumeKg.value / 60)));
const activePickers = computed(() => {
    if (qualityScore.value >= 90) {
        return 24;
    }

    if (qualityScore.value >= 87) {
        return 18;
    }

    if (qualityScore.value >= 83) {
        return 14;
    }

    return 10;
});
const currentPrice = computed(() => {
    if (props.harvest.price) {
        return Number(props.harvest.price);
    }

    if (qualityScore.value >= 90) {
        return 4.85;
    }

    if (qualityScore.value >= 87) {
        return 4.45;
    }

    if (qualityScore.value >= 83) {
        return 4.12;
    }

    return 3.78;
});
const priceChange = computed(() => {
    if ((props.harvest.pick_method || '').toLowerCase().includes('selective')) {
        return '+1.2%';
    }

    return '+0.7%';
});
const plantedDateLabel = computed(() => {
    if (!props.harvest.date_planted) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        year: 'numeric',
    }).format(new Date(props.harvest.date_planted));
});
const harvestDateLabel = computed(() => {
    if (!props.harvest.harvest_date) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(props.harvest.harvest_date));
});
const createdDateLabel = computed(() => {
    if (!props.harvest.created_at) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(props.harvest.created_at));
});
const stationLabel = computed(() => {
    const location = farmLocation.value.split(',')[0]?.trim();

    return location || 'Field Station';
});
const formatRangeLabel = (start, end) => {
    const startDate = new Date(`${start}-01`);
    const endDate = new Date(`${end}-01`);

    if (start === end) {
        return new Intl.DateTimeFormat('en-UG', {
            month: 'short',
        }).format(startDate);
    }

    const startMonth = new Intl.DateTimeFormat('en-UG', { month: 'short' }).format(startDate);
    const endMonth = new Intl.DateTimeFormat('en-UG', { month: 'short' }).format(endDate);
    const startYear = startDate.getFullYear();
    const endYear = endDate.getFullYear();

    if (startYear === endYear) {
        return `${startMonth}-${endMonth} ${endYear}`;
    }

    return `${startMonth} ${startYear}-${endMonth} ${endYear}`;
};
const usesLongRangeBuckets = computed(() => props.dateRange.some((range) => range.start !== range.end));
const analyticsMonthLabels = computed(() => {
    if (props.dateRange.length > 0) {
        return props.dateRange.map((range) => formatRangeLabel(range.start, range.end));
    }

    if (props.harvest.harvest_date) {
        return [
            new Intl.DateTimeFormat('en-UG', {
                month: 'short',
                ...(usesLongRangeBuckets.value ? { year: 'numeric' } : {}),
            }).format(new Date(props.harvest.harvest_date)),
        ];
    }

    return ['Current'];
});
const analyticsBars = computed(() => {
    const base = Math.max(volumeKg.value, 140);
    const values = analyticsMonthLabels.value.map((_, index, months) => {
        const divisor = Math.max(months.length - 1, 1);
        const ratio = 0.3 + ((index / divisor) * 0.7);

        return Math.round(base * ratio);
    });
    const peak = Math.max(...values, 1);

    return analyticsMonthLabels.value.map((label, index) => ({
        day: label,
        value: values[index],
        height: `${Math.max(16, Math.round((values[index] / peak) * 100))}%`,
        active: index === values.length - 1,
    }));
});
const latestAnalyticsMonth = computed(() => analyticsMonthLabels.value[analyticsMonthLabels.value.length - 1] || 'Current');
const microClimateCards = computed(() => [
    {
        label: 'Temperature',
        value: qualityScore.value >= 90 ? '24.2°C' : '23.6°C',
        state: 'Optimal',
    },
    {
        label: 'Humidity',
        value: volumeKg.value >= 1000 ? '62%' : '58%',
        state: 'Optimal',
    },
    {
        label: 'Moisture',
        value: qualityScore.value >= 87 ? '18.5%' : '20.4%',
        state: qualityScore.value >= 87 ? 'Optimal' : 'Warning',
    },
]);
const ledgerRows = computed(() => [
    {
        id: `#HB-${String(props.harvest.id).padStart(4, '0')}`,
        time: '08:42 AM',
        picker: farmerName.value,
        weight: Number(props.harvest.weight || 0).toFixed(1),
        score: props.harvest.ripeness_grade,
        status: 'Processed',
    },
    {
        id: `#HB-${String(props.harvest.id).padStart(4, '0')}-A`,
        time: '09:18 AM',
        picker: 'Field QA',
        weight: Math.max(volumeKg.value * 0.94, 1).toFixed(1),
        score: `Score ${qualityScore.value.toFixed(1)}`,
        status: 'Review',
    },
    {
        id: `#HB-${String(props.harvest.id).padStart(4, '0')}-B`,
        time: '10:02 AM',
        picker: stationLabel.value,
        weight: Math.max(volumeKg.value * 0.88, 1).toFixed(1),
        score: props.harvest.pick_method,
        status: 'Queued',
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
            title: 'Harvest Recorded',
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
    <AppLayout :title="harvestCode">
        <Head :title="harvestCode" />
        <div class="harvest-profile-page">
            <div class="harvest-profile-shell">
                <div class="harvest-profile-layout">
                    <main class="harvest-profile-main">
                        <section class="harvest-hero-card">
                            <div class="harvest-hero-card__content">
                                <div class="harvest-hero-card__eyebrow">Farm Harvest Detail</div>
                                <h1 class="harvest-hero-card__title">H-{{ props.harvest.id }}</h1>
                                <p class="harvest-hero-card__description">
                                    Institutional harvest intake from {{ farmName }} verified for quality and field traceability through the Commodity Origin workflow.
                                </p>
                            </div>

                            <div class="harvest-hero-card__stats">
                                <div class="harvest-hero-card__stat">
                                    <span class="harvest-hero-card__stat-label">Date Harvested</span>
                                    <strong>{{ harvestDateLabel }}</strong>
                                </div>
                                <div class="harvest-hero-card__stat">
                                    <span class="harvest-hero-card__stat-label">Weight</span>
                                    <strong>{{ Number(props.harvest.weight || 0).toLocaleString() }} kg</strong>
                                </div>
                                <div class="harvest-hero-card__stat">
                                    <span class="harvest-hero-card__stat-label">Date Planted</span>
                                    <strong>{{ plantedDateLabel }}</strong>
                                </div>
                                <div class="harvest-hero-card__stat">
                                    <span class="harvest-hero-card__stat-label">Variety</span>
                                    <strong>{{ varietyLabel }}</strong>
                                </div>
                            </div>
                        </section>


                        <section class="harvest-content-grid">
                            <article class="harvest-panel harvest-panel--analytics">
                                <div class="harvest-panel__header">
                                    <div>
                                        <div class="harvest-panel__title">Harvest Analytics</div>
                                        <div class="harvest-panel__subtitle">
                                            {{ usesLongRangeBuckets ? 'Progression from planted date to harvest date (kg)' : 'Monthly progression from planted date to harvest date (kg)' }}
                                        </div>
                                    </div>
                                    <!-- <div class="harvest-panel__chip">

                                        <strong>{{ latestAnalyticsMonth }}</strong>
                                    </div> -->
                                </div>

                                <div class="harvest-chart">
                                    <div
                                        v-for="bar in analyticsBars"
                                        :key="bar.day"
                                        class="harvest-chart__item"
                                    >
                                        <div class="harvest-chart__bar-wrap">
                                            <div class="harvest-chart__bar" :class="{ active: bar.active }" :style="{ height: bar.height }"></div>
                                        </div>
                                        <div class="harvest-chart__day">{{ bar.day }}</div>
                                    </div>
                                </div>
                            </article>

                            <aside class="harvest-micro-climate-card">
                                <div class="harvest-panel__title">Micro-Climate Data</div>

                                <div class="harvest-micro-climate-card__rows">
                                    <div v-for="item in microClimateCards" :key="item.label" class="harvest-micro-climate-card__row">
                                        <div>
                                            <div class="harvest-micro-climate-card__label">{{ item.label }}</div>
                                            <div class="harvest-micro-climate-card__value">{{ item.value }}</div>
                                        </div>
                                        <span class="harvest-micro-climate-card__badge" :class="item.state.toLowerCase()">{{ item.state }}</span>
                                    </div>
                                </div>

                            </aside>
                        </section>

                        <section class="harvest-panel">
                            <div class="harvest-ledger-head">
                                <div>
                                    <div class="harvest-panel__title">Daily Batch Ledger</div>
                                    <div class="harvest-panel__subtitle">Detailed log of individual bin entries for today’s harvest intake.</div>
                                </div>
                                <div class="harvest-ledger-search">Search bin ID...</div>
                            </div>

                            <div class="harvest-ledger-table-wrap">
                                <table class="harvest-ledger-table">
                                    <thead>
                                        <tr>
                                            <th>Bin ID</th>
                                            <th>Entry Time</th>
                                            <th>Picker</th>
                                            <th>Weight (kg)</th>
                                            <th>Quality Score</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in ledgerRows" :key="row.id">
                                            <td>{{ row.id }}</td>
                                            <td>{{ row.time }}</td>
                                            <td>{{ row.picker }}</td>
                                            <td>{{ row.weight }}</td>
                                            <td>{{ row.score }}</td>
                                            <td>
                                                <span class="harvest-ledger-status" :class="row.status.toLowerCase()">{{ row.status }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>


                    </main>

                    <aside class="harvest-terminal-card">
                        <div class="harvest-terminal-card__title">Execution Terminal</div>

                        <div class="harvest-terminal-card__price-label">Current Market Price</div>
                        <div class="harvest-terminal-card__price">Shs. {{ currentPrice.toFixed(2) }}<span></span></div>
                        <div class="harvest-terminal-card__delta">{{ priceChange }} since morning</div>

                        <div class="harvest-terminal-card__volume-block">
                            <div class="harvest-terminal-card__volume-label">Bidding Volume (Bags)</div>
                            <div class="harvest-terminal-card__volume-value">{{ bagCount }}</div>
                        </div>

                        <button type="button" class="harvest-terminal-card__primary">Place Bid</button>
                        <button type="button" class="harvest-terminal-card__secondary">Request Sample</button>

                        <div class="harvest-terminal-card__meta">
                            <div class="harvest-terminal-card__meta-row">
                                <span>Lot #</span>
                                <strong>HT-{{ String(props.harvest.id).padStart(4, '0') }}</strong>
                            </div>
                            <div class="harvest-terminal-card__meta-row">
                                <span>Shipping Estimate</span>
                                <strong>14-21 Days</strong>
                            </div>
                            <div class="harvest-terminal-card__meta-row">
                                <span>Available Stock</span>
                                <strong>{{ bagCount }}/{{ bagCount + 6 }} Bags</strong>
                            </div>
                        </div>

                        <div class="harvest-terminal-card__origin">
                            <span class="harvest-terminal-card__origin-label">Geographical Origin</span>
                            <strong>{{ farmLocation }}</strong>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.harvest-profile-page {
    background: #fff;
    padding-top: 4px;
}

.harvest-profile-shell {
    max-width: 1320px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.harvest-profile-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.harvest-profile-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
}

.harvest-profile-tab,
.harvest-hero-card__eyebrow,
.harvest-panel__title,
.harvest-panel__subtitle,
.harvest-metric-card__label,
.harvest-ledger-table th,
.harvest-footer-card__title,
.harvest-hero-card__stat-label,
.harvest-terminal-card__title,
.harvest-terminal-card__price-label,
.harvest-terminal-card__origin-label,
.harvest-terminal-card__volume-label,
.harvest-micro-climate-card__label {
    font-family: 'IBM Plex Mono', monospace;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.harvest-profile-tab {
    color: #8a9692;
    font-size: 13px;
    font-weight: 600;
}

.harvest-profile-tab.active {
    color: #18342d;
}

.harvest-profile-report-button {
    border: 0;
    border-radius: 8px;
    background: #0e5b3f;
    color: #fff;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 700;
}

.harvest-profile-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 300px;
    gap: 20px;
    align-items: start;
}

.harvest-profile-main {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.harvest-hero-card,
.harvest-metric-card,
.harvest-panel,
.harvest-terminal-card,
.harvest-footer-card,
.harvest-micro-climate-card {
    border: 1px solid #e7ecea;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 26px rgba(15, 23, 42, 0.04);
}

.harvest-hero-card {
    padding: 22px 24px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 280px;
    gap: 18px;
    align-items: start;
}

.harvest-hero-card__eyebrow {
    color: #9aa6a2;
    font-size: 10px;
    font-weight: 700;
}

.harvest-hero-card__title {
    margin: 10px 0 0;
    color: #142f28;
    font-size: clamp(1.6rem, 3vw, 2.35rem);
    line-height: 1.02;
    font-weight: 800;
    letter-spacing: -0.05em;
}

.harvest-hero-card__description {
    margin: 12px 0 0;
    max-width: 560px;
    color: #70807a;
    font-size: 14px;
    line-height: 1.7;
}

.harvest-hero-card__stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.harvest-hero-card__stat {
    border-left: 1px solid #edf1ef;
    padding-left: 14px;
}

.harvest-hero-card__stat-label {
    color: #9aa6a2;
    font-size: 9px;
    font-weight: 700;
}

.harvest-hero-card__stat strong {
    display: block;
    margin-top: 6px;
    color: #18342d;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.4;
}

.harvest-metric-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.harvest-metric-card {
    position: relative;
    padding: 16px 18px 16px 20px;
}

.harvest-metric-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 16px;
    bottom: 16px;
    width: 4px;
    border-radius: 999px;
}

.harvest-metric-card.accent-green::before { background: #0e6a46; }
.harvest-metric-card.accent-gold::before { background: #a96d21; }
.harvest-metric-card.accent-teal::before { background: #5e897e; }

.harvest-metric-card__label {
    color: #98a39f;
    font-size: 10px;
    font-weight: 700;
}

.harvest-metric-card__value {
    margin-top: 12px;
    color: #16312b;
    font-size: 35px;
    font-weight: 800;
    line-height: 1;
}

.harvest-metric-card__value span {
    font-size: 13px;
    font-weight: 700;
}

.harvest-metric-card__note {
    margin-top: 10px;
    color: #7d8784;
    font-size: 12px;
}

.harvest-content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 260px;
    gap: 16px;
}

.harvest-panel,
.harvest-micro-climate-card {
    padding: 18px;
}

.harvest-panel__header,
.harvest-ledger-head {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 12px;
}

.harvest-panel__title,
.harvest-terminal-card__title {
    color: #253b35;
    font-size: 12px;
    font-weight: 800;
}

.harvest-panel__subtitle {
    margin-top: 6px;
    color: #a0aba7;
    font-size: 10px;
    font-weight: 600;
}

.harvest-panel__chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    background: #eef6f2;
    padding: 7px 10px;
    color: #6f7f79;
    font-size: 11px;
    font-weight: 700;
}

.harvest-panel__chip strong {
    color: #0e6a46;
}

.harvest-chart {
    margin-top: 18px;
    height: 230px;
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 12px;
    align-items: end;
}

.harvest-chart__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    height: 100%;
}

.harvest-chart__bar-wrap {
    flex: 1;
    width: 100%;
    display: flex;
    align-items: end;
}

.harvest-chart__bar {
    width: 100%;
    min-height: 16px;
    border-radius: 10px 10px 0 0;
    background: #cfe0da;
}

.harvest-chart__bar.active {
    background: #0c5d41;
}

.harvest-chart__day {
    color: #94a19d;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}

.harvest-micro-climate-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.harvest-micro-climate-card__rows {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.harvest-micro-climate-card__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #eef2f0;
}

.harvest-micro-climate-card__row:last-child {
    border-bottom: 0;
}

.harvest-micro-climate-card__label {
    color: #99a5a1;
    font-size: 9px;
    font-weight: 700;
}

.harvest-micro-climate-card__value {
    margin-top: 4px;
    color: #18342d;
    font-size: 18px;
    font-weight: 800;
}

.harvest-micro-climate-card__badge {
    display: inline-flex;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.harvest-micro-climate-card__badge.optimal {
    background: #def3e8;
    color: #0e6a46;
}

.harvest-micro-climate-card__badge.warning {
    background: #fff0dd;
    color: #b46d1c;
}

.harvest-ledger-search {
    min-width: 132px;
    border-radius: 10px;
    background: #f7f9f8;
    padding: 10px 12px;
    color: #9aa6a2;
    font-size: 12px;
}

.harvest-ledger-table-wrap {
    margin-top: 18px;
    overflow-x: auto;
}

.harvest-ledger-table {
    width: 100%;
    border-collapse: collapse;
}

.harvest-ledger-table th {
    padding-bottom: 12px;
    color: #9aa6a2;
    font-size: 10px;
    font-weight: 700;
    text-align: left;
}

.harvest-ledger-table td {
    padding: 14px 0;
    border-top: 1px solid #eef2f0;
    color: #243934;
    font-size: 13px;
}

.harvest-ledger-status {
    display: inline-flex;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.harvest-ledger-status.processed {
    background: #def3e8;
    color: #0e6a46;
}

.harvest-ledger-status.review {
    background: #fff0dd;
    color: #b46d1c;
}

.harvest-ledger-status.queued {
    background: #edf2f7;
    color: #637381;
}

.harvest-footer-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.harvest-footer-card {
    padding: 16px 18px;
}

.harvest-footer-card.accent {
    background: #f6fbf8;
    border-color: #dcebe3;
}

.harvest-footer-card__title {
    color: #95a29d;
    font-size: 10px;
    font-weight: 700;
}

.harvest-footer-card__line {
    margin: 9px 0 0;
    color: #223833;
    font-size: 13px;
    line-height: 1.6;
}

.harvest-footer-card.accent .harvest-footer-card__line:first-of-type {
    color: #0e6a46;
    font-weight: 700;
}

.harvest-terminal-card {
    position: sticky;
    top: 24px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.harvest-terminal-card__price-label {
    color: #99a5a1;
    font-size: 10px;
    font-weight: 700;
}

.harvest-terminal-card__price {
    color: #17322c;
    font-size: 28px;
    font-weight: 800;
    line-height: 1.1;
}

.harvest-terminal-card__price span {
    font-size: 14px;
}

.harvest-terminal-card__delta {
    color: #0e6a46;
    font-size: 12px;
    font-weight: 700;
}

.harvest-terminal-card__volume-block {
    border-radius: 14px;
    background: #f6f8f7;
    padding: 14px 16px;
}

.harvest-terminal-card__volume-label {
    color: #99a5a1;
    font-size: 9px;
    font-weight: 700;
}

.harvest-terminal-card__volume-value {
    margin-top: 8px;
    color: #17322c;
    font-size: 30px;
    font-weight: 800;
}

.harvest-terminal-card__primary,
.harvest-terminal-card__secondary {
    width: 100%;
    border: 0;
    border-radius: 10px;
    padding: 13px 14px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.harvest-terminal-card__primary {
    background: #0e5b3f;
    color: #fff;
}

.harvest-terminal-card__secondary {
    background: #fde3c8;
    color: #8a5c27;
}

.harvest-terminal-card__meta {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.harvest-terminal-card__meta-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    color: #7d8784;
    font-size: 12px;
}

.harvest-terminal-card__meta-row strong {
    color: #16312b;
}

.harvest-terminal-card__origin {
    min-height: 170px;
    border-radius: 14px;
    background:
        linear-gradient(180deg, rgba(8, 47, 36, 0.05), rgba(8, 47, 36, 0.28)),
        radial-gradient(circle at top left, rgba(255, 255, 255, 0.22) 0, rgba(255, 255, 255, 0) 46%),
        linear-gradient(140deg, #d6e3db 0%, #90a99c 42%, #617d74 100%);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: end;
    gap: 8px;
}

.harvest-terminal-card__origin-label {
    color: rgba(255, 255, 255, 0.75);
    font-size: 9px;
    font-weight: 700;
}

.harvest-terminal-card__origin strong {
    color: #f7fffb;
    font-size: 24px;
    font-weight: 800;
    line-height: 1.15;
}

@media (max-width: 1180px) {
    .harvest-profile-layout,
    .harvest-content-grid {
        grid-template-columns: 1fr;
    }

    .harvest-terminal-card {
        position: static;
    }
}

@media (max-width: 960px) {
    .harvest-hero-card,
    .harvest-metric-grid,
    .harvest-footer-grid {
        grid-template-columns: 1fr;
    }

    .harvest-hero-card__stats {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 720px) {
    .harvest-profile-topbar,
    .harvest-panel__header,
    .harvest-ledger-head {
        flex-direction: column;
        align-items: stretch;
    }

    .harvest-profile-shell {
        gap: 14px;
    }

    .harvest-hero-card,
    .harvest-panel,
    .harvest-micro-climate-card,
    .harvest-terminal-card,
    .harvest-footer-card {
        padding-left: 16px;
        padding-right: 16px;
    }
}
</style>
