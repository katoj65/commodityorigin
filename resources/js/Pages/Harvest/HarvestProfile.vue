<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, Check, Document, Download, Location } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import EditHarvestQualityModel from '@/Components/Modals/EditHarvestQualityModel.vue';

const props = defineProps({
    harvest: {
        type: Object,
        required: true,
    },
    season: {
        type: Object,
        default: null,
    },
    dateRange: {
        type: Array,
        default: () => [],
    },
    pickMethodOptions: {
        type: Array,
        default: () => [],
    },
    harvestSeasonOptions: {
        type: Array,
        default: () => [],
    },
    documentTypeOptions: {
        type: Array,
        default: () => [],
    },
});

const pageTitle = 'Harvest Profile';
const editHarvestModalOpen = ref(false);

const formatDate = (value, options = { month: 'short', day: 'numeric', year: 'numeric' }) => {
    if (!value) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-US', options).format(new Date(value));
};

const formatMonthYear = (value) => formatDate(value, { month: 'short', year: 'numeric' });

const formatRange = (start, end) => {
    if (!start && !end) {
        return 'Feb - Jun 2026';
    }

    if (start && end) {
        return `${formatMonthYear(start)} - ${formatMonthYear(end)}`;
    }

    return start ? formatMonthYear(start) : formatMonthYear(end);
};

const formatWeight = (value, digits = 0) => `${Number(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
})}kg`;

const formatCurrency = (value) => `$${Number(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
})}`;

const formatCurrencyPrecise = (value) => `$${Number(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
})}`;

const startCase = (value, fallback) => {
    if (!value) {
        return fallback;
    }

    return String(value)
        .replace(/[_-]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .replace(/\b\w/g, (character) => character.toUpperCase());
};

const harvestCode = computed(() => {
    const year = props.harvest.harvest_date ? new Date(props.harvest.harvest_date).getFullYear() : new Date().getFullYear();

    return `#H-${year}-${String(props.harvest.id).padStart(2, '0')}`;
});

const seasonName = computed(() => props.season?.name || props.harvest.season?.name || 'Main Crop 2026');
const farmName = computed(() => props.harvest.farm?.name || 'Mount Elgon Heights');
const farmerName = computed(() => {
    const farmer = props.harvest.farm?.farmer;
    const fullName = [farmer?.first_name, farmer?.last_name].filter(Boolean).join(' ');

    return fullName || 'Abebe Bikila';
});
const regionLabel = computed(() => {
    const district = props.harvest.farm?.farmer?.district;
    const location = props.harvest.farm?.location;

    if (district) {
        return `${district}, Uganda`;
    }

    return location || 'Gedeo, Uganda';
});
const altitudeLabel = computed(() => props.harvest.farm?.altitude || '2,100m');
const coordinatesLabel = computed(() => {
    const latitude = props.harvest.farm?.latitude;
    const longitude = props.harvest.farm?.longitude;

    if (latitude && longitude) {
        return `${latitude}, ${longitude}`;
    }

    return '1.149 N, 34.331 E';
});
const varietyLabel = computed(() => props.harvest.variety || props.harvest.farm?.variety || 'Arabica SL-14');
const pickMethodLabel = computed(() => startCase(props.harvest.pick_method, 'Selective'));
const totalQuantity = computed(() => Number(props.harvest.weight || 1200));
const cleanEstimate = computed(() => totalQuantity.value * 0.1667);
const processLoss = computed(() => {
    let loss = 4.2;

    if (props.harvest.foreign_matter_present) {
        loss += 0.8;
    }

    if (props.harvest.visible_defects) {
        loss += 0.6;
    }

    return loss;
});
const expectedBatchQuantity = computed(() => cleanEstimate.value * (1 - (processLoss.value / 100)));
const moistureLevel = computed(() => {
    const ripeness = Number(props.harvest.ripeness_percentage || 94);
    return Math.max(10.6, Math.min(12.3, 12.9 - (ripeness * 0.018)));
});
const harvestDateLabel = computed(() => formatDate(props.harvest.harvest_date, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
}));
const seasonDuration = computed(() => formatRange(
    props.season?.start_date || props.harvest.date_planted,
    props.season?.end_date || props.harvest.harvest_date,
));
const plantedDateLabel = computed(() => formatDate(props.harvest.date_planted, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
}));
const totalYield = computed(() => props.season?.harvests_sum_weight
    ? formatWeight(props.season.harvests_sum_weight)
    : formatWeight(totalQuantity.value * 10.33));
const harvestCount = computed(() => `${props.season?.harvests_count || 8} Events`);
const statusLabel = computed(() => {
    const normalized = String(props.harvest.status || '').toLowerCase();

    if (!normalized || normalized === 'active') {
        return 'Verified';
    }

    return startCase(normalized, 'Verified');
});
const readinessItems = computed(() => [
    { label: 'Season linked', complete: true },
    { label: 'Farm verified', complete: Boolean(props.harvest.farm?.id) },
    { label: 'Quantity verified', complete: totalQuantity.value > 0 },
    { label: 'Moisture checked', complete: moistureLevel.value > 0 },
    { label: 'Cherries sorted', complete: !props.harvest.foreign_matter_present },
    { label: 'Evidence uploaded', complete: (props.harvest.harvest_documents || []).length > 0 },
]);
const readinessScore = computed(() => {
    const items = readinessItems.value;
    const completed = items.filter((item) => item.complete).length;

    return Math.round((completed / items.length) * 100);
});
const healthScore = computed(() => Math.max(82, Math.min(96, Math.round(86 + (Number(props.harvest.ripeness_percentage || 94) * 0.06)))));
const cupScoreLabel = computed(() => {
    const score = Math.max(84, Math.min(90, Math.round(83 + (Number(props.harvest.ripeness_percentage || 94) * 0.045))));
    return `${score}-${score + 2}`;
});
const estimatedValue = computed(() => {
    const price = Number(props.harvest.price || 0);

    if (price > 25) {
        return price;
    }

    return totalQuantity.value * Math.max(price, 4.5);
});
const traceableLabel = computed(() => `TRACEABLE HARVEST LINKED TO ${seasonName.value.toUpperCase()}`);
const badgeItems = computed(() => [
    { label: 'LINKED TO SEASON', tone: 'green' },
    { label: 'VERIFIED FARM', tone: 'neutral' },
    { label: 'READY FOR BATCHING', tone: 'green' },
    { label: 'TRACEABLE', tone: 'neutral' },
]);
const evidenceItems = computed(() => {
    const documents = props.harvest.harvest_documents || [];

    if (documents.length > 0) {
        return documents.map((document) => ({
            id: document.id,
            title: document.title,
            subtitle: document.original_name,
            href: document.file_url,
        }));
    }

    return [
        { id: 'photos', title: 'Harvest Photos', subtitle: 'Pending upload', href: null },
        { id: 'farmer-confirmation', title: 'Farmer Confirmation', subtitle: 'Pending upload', href: null },
        { id: 'field-report', title: 'Field Officer Report', subtitle: 'Pending upload', href: null },
    ];
});
const harvestSeasonLabel = computed(() => startCase(props.harvest.harvest_season, 'Main Crop'));
const detailItems = computed(() => [
    { key: 'date-planted', label: 'Date Planted', value: plantedDateLabel.value },
    { key: 'harvest-date', label: 'Harvest Date', value: harvestDateLabel.value },
    { key: 'harvest-season', label: 'Harvest Season', value: harvestSeasonLabel.value },
    { key: 'variety', label: 'Variety', value: varietyLabel.value },
    { key: 'pick-method', label: 'Pick Method', value: pickMethodLabel.value },
    { key: 'weight', label: 'Weight', value: formatWeight(totalQuantity.value, 2) },
    { key: 'price', label: 'Price', value: formatCurrencyPrecise(props.harvest.price || 0) },
    { key: 'ripeness', label: 'Ripeness Percentage', value: `${Number(props.harvest.ripeness_percentage || 0).toFixed(2)}%` },
]);
const qualityItems = computed(() => [
    { label: 'Foreign Matter Present', value: props.harvest.foreign_matter_present ? 'Yes' : 'No', tone: props.harvest.foreign_matter_present ? 'alert' : 'good' },
    { label: 'Pest Damage', value: props.harvest.pest_damage ? 'Yes' : 'No', tone: props.harvest.pest_damage ? 'alert' : 'good' },
    { label: 'Disease Signs', value: props.harvest.disease_signs ? 'Yes' : 'No', tone: props.harvest.disease_signs ? 'alert' : 'good' },
    { label: 'Visible Defects', value: props.harvest.visible_defects ? 'Yes' : 'No', tone: props.harvest.visible_defects ? 'alert' : 'good' },
]);
const verificationChain = computed(() => [
    {
        title: 'Season Created',
        subtitle: props.season?.start_date ? formatDate(props.season.start_date, { month: 'short', day: 'numeric', year: 'numeric' }) : 'Feb 14, 2026',
        active: false,
    },
    {
        title: 'Farm Activities Logged',
        subtitle: props.dateRange.length > 0
            ? `${formatMonthYear(props.dateRange[0]?.start)} - ${formatMonthYear(props.dateRange[props.dateRange.length - 1]?.end)}`
            : 'Mar - Sep 2026',
        active: false,
    },
    {
        title: 'Harvest Recorded',
        subtitle: harvestDateLabel.value,
        active: false,
    },
    {
        title: 'Quantity Verified',
        subtitle: harvestDateLabel.value,
        active: false,
    },
    {
        title: 'Ready for Batching',
        subtitle: 'SYSTEM CONFIRMED',
        active: true,
    },
]);
const activitySteps = computed(() => {
    const fallback = [
        { title: 'Planting', date: 'Mar 24' },
        { title: 'Weeding', date: 'May 24' },
        { title: 'Flowering', date: 'Jul 24' },
        { title: 'Inspection', date: 'Sept 24' },
        { title: 'Harvesting', date: 'Oct 24' },
    ];

    if (props.dateRange.length === 0) {
        return fallback;
    }

    return [
        { title: 'Planting', date: formatMonthYear(props.dateRange[0]?.start || props.harvest.date_planted).replace(' ', ' ') },
        { title: 'Weeding', date: formatMonthYear(props.dateRange[1]?.start || props.dateRange[0]?.start || props.harvest.date_planted) },
        { title: 'Flowering', date: formatMonthYear(props.dateRange[2]?.start || props.dateRange[0]?.start || props.harvest.date_planted) },
        { title: 'Inspection', date: formatMonthYear(props.dateRange[3]?.start || props.dateRange[1]?.start || props.harvest.date_planted) },
        { title: 'Harvesting', date: formatDate(props.harvest.harvest_date, { month: 'short', year: '2-digit' }) },
    ];
});
const openEditHarvestModal = () => {
    editHarvestModalOpen.value = true;
};
</script>

<template>
    <AppLayout :title="pageTitle" full-width flush :show-banner="false">
        <Head :title="pageTitle" />

        <div class="harvest-profile-page">
            <div class="harvest-profile-shell">
                <section class="profile-hero">
                    <div class="profile-hero__copy">
                        <p class="profile-kicker">{{ traceableLabel }}</p>
                        <h1 class="mt-2 mb-2">Harvest Profile</h1>
                    </div>

                    <div class="profile-hero__actions">
                        <button type="button" class="hero-button hero-button--soft" @click="openEditHarvestModal">Edit</button>
                    </div>
                </section>

                <section class="badge-row">
                    <span
                        v-for="badge in badgeItems"
                        :key="badge.label"
                        class="profile-badge"
                        :class="badge.tone === 'green' ? 'is-green' : 'is-neutral'"
                    >
                        {{ badge.label }}
                    </span>
                </section>

                <section class="summary-strip">
                    <article class="summary-strip__item">
                        <span>Harvest ID</span>
                        <strong>{{ harvestCode }}</strong>
                    </article>
                    <article class="summary-strip__item">
                        <span>Season</span>
                        <strong>{{ seasonName }}</strong>
                    </article>
                    <article class="summary-strip__item">
                        <span>Farm</span>
                        <strong>{{ farmName }}</strong>
                    </article>
                    <article class="summary-strip__item">
                        <span>Coffee Type</span>
                        <strong>{{ varietyLabel }}</strong>
                    </article>
                    <article class="summary-strip__item">
                        <span>Status</span>
                        <strong class="summary-status">
                            <span class="status-dot"></span>
                            {{ statusLabel }}
                        </strong>
                    </article>
                    <article class="summary-strip__item summary-strip__item--score">
                        <span>Readiness Score</span>
                        <strong>{{ readinessScore }}%</strong>
                    </article>
                </section>

                <div class="content-grid">
                    <div class="content-main">
                        <section class="season-card">
                            <div class="season-card__content">
                                <div class="mb-3">
                                    <h2>{{ seasonName }}</h2>
                                    <p>Active Agricultural Lifecycle</p>
                                </div>

                                <div class="season-metrics">
                                    <article class="mini-metric-card">
                                        <span>Duration</span>
                                        <strong>{{ seasonDuration }}</strong>
                                    </article>
                                    <article class="mini-metric-card">
                                        <span>Total Yield</span>
                                        <strong>{{ totalYield }}</strong>
                                    </article>
                                    <article class="mini-metric-card">
                                        <span>Harvests</span>
                                        <strong>{{ harvestCount }}</strong>
                                    </article>
                                    <article class="mini-metric-card">
                                        <span>Status</span>
                                        <strong class="metric-active">Active</strong>
                                    </article>
                                </div>
                            </div>

                            <div class="health-score-card">
                                <strong>{{ healthScore }}</strong>
                                <span>HEALTH SCORE</span>
                            </div>
                        </section>

                        <!-- <section class="harvest-stats">
                            <article class="metric-card">
                                <span>Harvest Date</span>
                                <strong>{{ harvestDateLabel }}</strong>
                            </article>
                            <article class="metric-card">
                                <span>Total Qty</span>
                                <strong>{{ formatWeight(totalQuantity) }}</strong>
                            </article>
                            <article class="metric-card">
                                <span>Clean Est.</span>
                                <strong>{{ formatWeight(cleanEstimate) }}</strong>
                            </article>
                            <article class="metric-card">
                                <span>Moisture</span>
                                <strong>{{ moistureLevel.toFixed(1) }}%</strong>
                            </article>
                            <article class="metric-card">
                                <span>Cup Score</span>
                                <strong>{{ cupScoreLabel }}</strong>
                            </article>
                            <article class="metric-card">
                                <span>Est. Value</span>
                                <strong>{{ formatCurrency(estimatedValue) }}</strong>
                            </article>
                        </section> -->

                        <section class="harvest-data-card">
                            <div class="section-header mb-3">
                                <h3>Harvest Details</h3>
                            </div>

                            <div class="harvest-data-grid">
                                <article v-for="item in detailItems" :key="item.label" class="data-pill-card">
                                    <div class="data-pill-card__label">
                                        <span class="detail-icon" aria-hidden="true">
                                            <svg v-if="item.key === 'date-planted' || item.key === 'harvest-date'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="3" y="5" width="18" height="16" rx="2" />
                                                <path d="M8 3v4" />
                                                <path d="M16 3v4" />
                                                <path d="M3 10h18" />
                                            </svg>
                                            <svg v-else-if="item.key === 'harvest-season'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M6 4h12" />
                                                <path d="M6 8h12" />
                                                <path d="M6 12h8" />
                                                <path d="M6 16h8" />
                                                <path d="M17 14l2 2 3-4" />
                                            </svg>
                                            <svg v-else-if="item.key === 'variety'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M12 20c4.5-2.4 7-6 7-10.1C19 6.5 16.3 4 12 4S5 6.5 5 9.9C5 14 7.5 17.6 12 20Z" />
                                                <path d="M12 7v8" />
                                                <path d="M9 11c1.2.8 2 .8 3 0 1.1-.8 1.8-.8 3 0" />
                                            </svg>
                                            <svg v-else-if="item.key === 'pick-method'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M7 20l10-10" />
                                                <path d="M14 7l3 3" />
                                                <path d="M5 14l5 5" />
                                                <path d="M4 20h6" />
                                            </svg>
                                            <svg v-else-if="item.key === 'weight'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M7 8h10l2 11H5L7 8Z" />
                                                <path d="M9.5 8a2.5 2.5 0 1 1 5 0" />
                                                <path d="M12 11v3" />
                                            </svg>
                                            <svg v-else-if="item.key === 'price'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M12 3v18" />
                                                <path d="M16 7.5c0-1.9-1.8-3-4-3s-4 1.1-4 3 1.6 2.5 4 3 4 1.1 4 3-1.8 3-4 3-4-1.1-4-3" />
                                            </svg>
                                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M12 3v10" />
                                                <path d="M8.5 9.5 12 13l3.5-3.5" />
                                                <path d="M5 19h14" />
                                            </svg>
                                        </span>
                                        <span>{{ item.label }}</span>
                                    </div>
                                    <strong>{{ item.value }}</strong>
                                </article>
                            </div>

                            <div class="quality-flags-grid">
                                <article
                                    v-for="item in qualityItems"
                                    :key="item.label"
                                    class="quality-flag-card"
                                    :class="item.tone === 'alert' ? 'is-alert' : 'is-good'"
                                >
                                    <span>{{ item.label }}</span>
                                    <strong>{{ item.value }}</strong>
                                </article>
                            </div>
                        </section>

                        <section class="source-grid">
                            <article class="source-card">
                                <div class="source-card__header">
                                    <div class="source-avatar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <rect x="4" y="4" width="16" height="16" rx="2" />
                                            <circle cx="12" cy="10" r="3" />
                                            <path d="M7.5 18c1.2-2.2 3-3.3 4.5-3.3S15.3 15.8 16.5 18" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3>{{ farmerName }}</h3>
                                        <p>Lead Producer, Mount Elgon</p>
                                    </div>
                                </div>

                                <dl class="source-details">
                                    <div>
                                        <dt>Region</dt>
                                        <dd>{{ regionLabel }}</dd>
                                    </div>
                                    <div>
                                        <dt>Altitude</dt>
                                        <dd>{{ altitudeLabel }}</dd>
                                    </div>
                                    <div>
                                        <dt>GPS Coordinates</dt>
                                        <dd>{{ coordinatesLabel }}</dd>
                                    </div>
                                </dl>
                            </article>

                            <article class="map-card">
                                <div class="map-card__image"></div>
                                <div class="map-card__caption">
                                    <el-icon><Location /></el-icon>
                                    <span>{{ farmName }}, Eastern Uganda</span>
                                </div>
                            </article>
                        </section>

                        <section class="transformation-card">
                            <div class="section-header">
                                <h3>Quantity Breakdown &amp; Transformation</h3>
                                <div class="section-header__stats">
                                    <div>
                                        <span>Ripeness Score</span>
                                        <strong>{{ Math.round(props.harvest.ripeness_percentage || 94) }}%</strong>
                                    </div>
                                    <div>
                                        <span>Method</span>
                                        <strong>{{ pickMethodLabel }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="transformation-flow">
                                <article class="flow-stage">
                                    <div class="flow-stage__icon is-red">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <circle cx="8" cy="17" r="1.5" />
                                            <circle cx="16" cy="17" r="1.5" />
                                            <path d="M5 16V9h9l3 4" />
                                            <path d="M10 9V6" />
                                            <path d="M13 6H7" />
                                        </svg>
                                    </div>
                                    <span>Fresh Cherry</span>
                                    <strong>{{ formatWeight(totalQuantity) }}</strong>
                                </article>

                                <div class="flow-connector">
                                    <span>Processing</span>
                                </div>

                                <article class="flow-stage">
                                    <div class="flow-stage__icon is-amber">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M12 4c3.8 0 6 2.7 6 6.1 0 5-4.6 8.3-6 9.1-1.4-.8-6-4.1-6-9.1C6 6.7 8.2 4 12 4Z" />
                                            <path d="M10 10c.8 1 2 1.4 4 1.2" />
                                        </svg>
                                    </div>
                                    <span>Clean Estimate</span>
                                    <strong>{{ formatWeight(cleanEstimate) }}</strong>
                                </article>

                                <div class="flow-connector">
                                    <span>{{ processLoss.toFixed(1) }}% Loss</span>
                                </div>

                                <article class="flow-stage">
                                    <div class="flow-stage__icon is-green">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M7 5h10" />
                                            <path d="M8 5v14h8V5" />
                                            <path d="M9 10h6" />
                                            <path d="M9 14h6" />
                                        </svg>
                                    </div>
                                    <span>Batch Exp.</span>
                                    <strong>{{ formatWeight(expectedBatchQuantity) }}</strong>
                                </article>
                            </div>
                        </section>

                        <section class="activity-card">
                            <div class="section-header">
                                <h3>Activities Before Harvest</h3>
                            </div>

                            <div class="activity-track">
                                <article
                                    v-for="(activity, index) in activitySteps"
                                    :key="activity.title"
                                    class="activity-step"
                                    :class="{ 'is-current': index === activitySteps.length - 1 }"
                                >
                                    <div class="activity-step__dot">
                                        <span v-if="index === activitySteps.length - 1">&#10003;</span>
                                        <span v-else></span>
                                    </div>
                                    <h4>{{ activity.title }}</h4>
                                    <p>{{ activity.date }}</p>
                                </article>
                            </div>
                        </section>
                    </div>

                    <aside class="content-rail">
                        <section class="readiness-card">
                            <div class="readiness-card__header">
                                <h3>Readiness</h3>
                                <strong>{{ readinessScore }}%</strong>
                            </div>

                            <ul class="readiness-list">
                                <li v-for="item in readinessItems" :key="item.label">
                                    <span class="readiness-check" :class="{ complete: item.complete }">
                                        <el-icon><Check /></el-icon>
                                    </span>
                                    <span>{{ item.label }}</span>
                                </li>
                            </ul>
                        </section>

                        <section class="timeline-card">
                            <p class="card-eyebrow">Verification Chain</p>

                            <div class="timeline-list">
                                <article
                                    v-for="item in verificationChain"
                                    :key="item.title"
                                    class="timeline-entry"
                                    :class="{ 'is-active': item.active }"
                                >
                                    <span class="timeline-entry__dot"></span>
                                    <div>
                                        <h4>{{ item.title }}</h4>
                                        <p>{{ item.subtitle }}</p>
                                    </div>
                                </article>
                            </div>
                        </section>

                        <section class="evidence-card">
                            <h3>Evidence &amp; Compliance</h3>

                            <component
                                v-for="item in evidenceItems"
                                :key="item.id"
                                :is="item.href ? 'a' : 'div'"
                                class="evidence-row"
                                :href="item.href || undefined"
                                :target="item.href ? '_blank' : undefined"
                                :rel="item.href ? 'noreferrer noopener' : undefined"
                            >
                                <div class="evidence-row__content">
                                    <div class="evidence-row__icon">
                                        <el-icon><Document /></el-icon>
                                    </div>
                                    <div>
                                        <strong>{{ item.title }}</strong>
                                        <span>{{ item.subtitle }}</span>
                                    </div>
                                </div>

                                <el-icon class="evidence-row__download"><Download /></el-icon>
                            </component>
                        </section>

                        <section class="insight-strip">
                            <span class="insight-strip__sparkle">+</span>
                            <p>Harvest correctly linked to active season</p>
                        </section>

                        <section class="insight-strip">
                            <span class="insight-strip__sparkle">+</span>
                            <p>Season activities support full traceability</p>
                        </section>
                    </aside>
                </div>
            </div>

            <EditHarvestQualityModel
                v-model="editHarvestModalOpen"
                :harvest="harvest"
                :pick-method-options="pickMethodOptions"
                :harvest-season-options="harvestSeasonOptions"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.harvest-profile-page {
    min-height: calc(100vh - 56px);
    background: #ffffff;
}

.harvest-profile-shell {
    max-width: 1230px;
    margin: 0 auto;
    padding: 34px 28px 48px;
}

.profile-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
}

.profile-kicker {
    margin: 0 0 4px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.12em;
    color: #6f7f7b;
}

.profile-hero h1 {
    margin: 0;
    font-size: 34px;
    line-height: 0.96;
    font-weight: 700;
    color: #121715;
}

.profile-hero__actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.hero-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 42px;
    padding: 0 18px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid #d8ddd6;
    cursor: pointer;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.hero-button--soft {
    background: #f8faf8;
    color: #121715;
}

.badge-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
}

.profile-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #12201c;
}

.profile-badge.is-green {
    background: #9bf0bb;
}

.profile-badge.is-neutral {
    background: #e4e7e6;
}

.summary-strip {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0;
    margin-top: 30px;
    background: #fff;
    border: 0.5px solid #e7ebe6;
    border-radius: 14px;
    overflow: hidden;
}

.summary-strip__item {
    padding: 16px 22px;
    border-right: 0.5px solid #edf1ec;
}

.summary-strip__item:last-child {
    border-right: none;
}

.summary-strip__item span {
    display: block;
    margin-bottom: 8px;
    font-size: 10px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #74807a;
}

.summary-strip__item strong {
    display: block;
    font-size: 24px;
    line-height: 1.15;
    font-weight: 600;
    color: #111614;
}

.summary-strip__item:not(.summary-strip__item--score) strong {
    font-size: 20px;
}

.summary-strip__item--score {
    text-align: right;
}

.summary-status {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    font-size: 17px !important;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #0a8b51;
}

.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 292px;
    gap: 24px;
    margin-top: 24px;
    align-items: start;
}

.content-main,
.content-rail {
    display: grid;
    gap: 24px;
}

.season-card,
.metric-card,
.source-card,
.map-card,
.harvest-data-card,
.transformation-card,
.activity-card,
.readiness-card,
.timeline-card,
.evidence-card,
.insight-strip {
    background: #fff;
    border: 0.5px solid #e7ebe6;
    border-radius: 14px;
}

.season-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 130px;
    min-height: 184px;
    overflow: hidden;
}

.season-card__content {
    padding: 26px 24px 22px;
}

.season-card__content h2 {
    margin: 0;
    font-size: 22px;
    line-height: 1.1;
    font-weight: 600;
    color: #121715;
}

.season-card__content > p {
    margin: 6px 0 28px;
    font-size: 14px;
    color: #6a736f;
}

.season-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.mini-metric-card {
    min-height: 74px;
    padding: 14px 16px;
    border-radius: 8px;
    background: #fbfbf9;
}

.mini-metric-card span,
.metric-card span,
.section-header__stats span,
.card-eyebrow,
.intel-metrics span {
    display: block;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: #76807d;
}

.mini-metric-card strong,
.metric-card strong {
    display: block;
    margin-top: 10px;
    font-size: 16px;
    line-height: 1.25;
    font-weight: 600;
    color: #101513;
}

.metric-active {
    color: #0d8a52 !important;
}

.health-score-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #e7efec;
    color: #a3bab4;
}

.health-score-card strong {
    font-size: 56px;
    line-height: 1;
    font-weight: 700;
}

.health-score-card span {
    margin-top: 10px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    color: #6f807b;
}

.harvest-stats {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 12px;
}

.metric-card {
    min-height: 90px;
    padding: 16px 16px 14px;
}

.source-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 24px;
}

.harvest-data-card {
    padding: 26px 24px;
}

.harvest-data-grid,
.quality-flags-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.quality-flags-grid {
    margin-top: 14px;
}

.data-pill-card,
.quality-flag-card {
    min-height: 88px;
    padding: 15px 16px 14px;
    border-radius: 10px;
    background: #fafbf9;
    border: 0.5px solid #edf1ec;
}

.data-pill-card__label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    max-width: 100%;
}

.detail-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    color: #6e7b76;
    flex-shrink: 0;
}

.detail-icon svg {
    width: 16px;
    height: 16px;
}

.data-pill-card span,
.quality-flag-card span {
    display: block;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: #76807d;
}

.data-pill-card__label > span:last-child {
    display: inline-block;
    min-width: 0;
}

.data-pill-card strong,
.quality-flag-card strong {
    display: block;
    margin-top: 10px;
    font-size: 15px;
    line-height: 1.35;
    font-weight: 600;
    color: #101513;
}

.quality-flag-card.is-good {
    background: #f6faf7;
}

.quality-flag-card.is-good strong {
    color: #0a5b38;
}

.quality-flag-card.is-alert {
    background: #fff7f5;
}

.quality-flag-card.is-alert strong {
    color: #b8572a;
}

.source-card {
    padding: 22px 24px;
}

.source-card__header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 18px;
    margin-bottom: 18px;
    border-bottom: 0.5px solid #edf0eb;
}

.source-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #f5f7f3;
    color: #11211c;
}

.source-avatar svg {
    width: 22px;
    height: 22px;
}

.source-card__header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111614;
}

.source-card__header p {
    margin: 4px 0 0;
    font-size: 13px;
    color: #68716d;
}

.source-details {
    display: grid;
    gap: 18px;
    margin: 0;
}

.source-details div {
    display: grid;
    grid-template-columns: 110px minmax(0, 1fr);
    gap: 12px;
}

.source-details dt {
    font-size: 13px;
    color: #68716d;
}

.source-details dd {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #121715;
}

.map-card {
    position: relative;
    min-height: 228px;
    overflow: hidden;
}

.map-card__image {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(0, 0, 0, 0.18)),
        radial-gradient(circle at 30% 35%, rgba(255, 255, 255, 0.42), transparent 35%),
        linear-gradient(145deg, #d7dbd6 0%, #c7ceca 32%, #97a39e 100%);
}

.map-card__image::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        repeating-linear-gradient(165deg, rgba(255, 255, 255, 0.28) 0 2px, transparent 2px 12px),
        repeating-linear-gradient(15deg, rgba(255, 255, 255, 0.1) 0 1px, transparent 1px 11px);
    opacity: 0.65;
}

.map-card__caption {
    position: absolute;
    left: 18px;
    right: 18px;
    bottom: 18px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    max-width: max-content;
    padding: 12px 16px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.92);
    color: #17211d;
    font-size: 14px;
    font-weight: 500;
}

.transformation-card,
.activity-card {
    padding: 26px 24px;
}

.section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
}

.section-header h3,
.evidence-card h3,
.readiness-card h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #121715;
}

.section-header__stats {
    display: flex;
    align-items: flex-start;
    gap: 28px;
}

.section-header__stats strong {
    display: block;
    margin-top: 6px;
    font-size: 16px;
    font-weight: 700;
    color: #111614;
}

.transformation-flow {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 86px minmax(0, 1fr) 86px minmax(0, 1fr);
    align-items: center;
    gap: 10px;
    margin-top: 30px;
}

.flow-stage {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

.flow-stage span {
    font-size: 14px;
    color: #222a27;
}

.flow-stage strong {
    font-size: 16px;
    font-weight: 700;
    color: #0f1412;
}

.flow-stage__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    border-radius: 10px;
}

.flow-stage__icon svg {
    width: 22px;
    height: 22px;
}

.flow-stage__icon.is-red {
    background: #ffe9ea;
    color: #e24a45;
}

.flow-stage__icon.is-amber {
    background: #fee0bc;
    color: #6f4f22;
}

.flow-stage__icon.is-green {
    background: #c8f5dc;
    color: #0a5b38;
}

.flow-connector {
    position: relative;
    height: 0.5px;
    background: #dbe3dd;
}

.flow-connector span {
    position: absolute;
    top: -11px;
    left: 50%;
    transform: translateX(-50%);
    padding: 0 6px;
    background: #fff;
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9aa6a1;
    white-space: nowrap;
}

.activity-track {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 18px;
    margin-top: 28px;
    position: relative;
}

.activity-track::before {
    content: '';
    position: absolute;
    top: 17px;
    left: 42px;
    right: 42px;
    height: 0.5px;
    background: #d9e1dc;
}

.activity-step {
    position: relative;
    z-index: 1;
    text-align: center;
}

.activity-step__dot {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    margin: 0 auto 12px;
    border-radius: 12px;
    border: 0.5px solid #0d3927;
    background: #fff;
    color: #0d3927;
    font-size: 14px;
    font-weight: 700;
}

.activity-step__dot span {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: currentColor;
    display: block;
}

.activity-step.is-current .activity-step__dot {
    background: #0a5b38;
    color: #fff;
    border-color: #0a5b38;
}

.activity-step.is-current .activity-step__dot span {
    width: auto;
    height: auto;
    border-radius: 0;
    background: transparent;
}

.activity-step h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    color: #121715;
}

.activity-step p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #94a09b;
}

.readiness-card {
    padding: 22px 22px 20px;
    background: #0a5b38;
    border-color: #0a5b38;
    color: #fff;
}

.readiness-card__header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 16px;
}

.readiness-card__header h3 {
    color: #fff;
}

.readiness-card__header strong {
    font-size: 44px;
    line-height: 1;
    font-weight: 700;
}

.readiness-list {
    list-style: none;
    padding: 0;
    margin: 22px 0 0;
    display: grid;
    gap: 16px;
}

.readiness-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
}

.readiness-check {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 999px;
    border: 0.5px solid rgba(255, 255, 255, 0.7);
    color: #fff;
}

.readiness-check.complete {
    background: rgba(255, 255, 255, 0.08);
}

.timeline-card,
.evidence-card {
    position: relative;
    padding: 22px 22px 20px;
    background: #f7f8f5;
}

.timeline-list {
    margin-top: 22px;
}

.timeline-entry {
    position: relative;
    display: grid;
    grid-template-columns: 16px minmax(0, 1fr);
    gap: 14px;
    padding: 0 0 22px;
}

.timeline-entry:last-child {
    padding-bottom: 0;
}

.timeline-entry::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 16px;
    bottom: 0;
    width: 1px;
    background: #d6ddd8;
}

.timeline-entry:last-child::before {
    display: none;
}

.timeline-entry__dot {
    width: 14px;
    height: 14px;
    margin-top: 2px;
    border-radius: 999px;
    background: #0a5b38;
}

.timeline-entry h4 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 500;
    color: #0f1412;
}

.timeline-entry p {
    margin: 0;
    font-size: 12px;
    color: #96a39d;
}

.timeline-entry.is-active p {
    color: #0a8b51;
    font-weight: 600;
}

.evidence-card {
    display: grid;
    gap: 12px;
}

.evidence-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 14px;
    border-radius: 8px;
    background: #fff;
    color: #15201b;
    text-decoration: none;
}

.evidence-row__content {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.evidence-row__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 7px;
    background: #f4f7f3;
    color: #7b92b7;
    flex-shrink: 0;
}

.evidence-row strong {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #232a27;
}

.evidence-row span {
    display: block;
    margin-top: 3px;
    font-size: 12px;
    color: #9ba59f;
}

.evidence-row__download {
    color: #b7c2bc;
    flex-shrink: 0;
}

.insight-strip {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 18px;
    background: #edf5f2;
}

.insight-strip__sparkle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 700;
    color: #0a5b38;
}

.insight-strip p {
    margin: 0;
    font-size: 13px;
    color: #183127;
}

@media (max-width: 1180px) {
    .summary-strip {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .summary-strip__item:nth-child(3n) {
        border-right: none;
    }

    .summary-strip__item:nth-child(n + 4) {
        border-top: 0.5px solid #ecefea;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .content-rail {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}

@media (max-width: 960px) {
    .harvest-profile-shell {
        padding: 24px 18px 38px;
    }

    .profile-hero {
        flex-direction: column;
    }

    .profile-hero h1 {
        font-size: 30px;
    }

    .season-card {
        grid-template-columns: 1fr;
    }

    .health-score-card {
        min-height: 110px;
    }

    .season-metrics,
    .harvest-stats,
    .harvest-data-grid,
    .quality-flags-grid,
    .source-grid,
    .content-rail {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .transformation-flow {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .flow-connector {
        height: 18px;
        width: 1px;
        margin: 0 auto;
    }

    .flow-connector span {
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
    }

    .activity-track {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .activity-track::before {
        left: 17px;
        top: 18px;
        bottom: 18px;
        right: auto;
        width: 1px;
        height: auto;
    }

    .activity-step {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        gap: 12px;
        text-align: left;
        align-items: center;
    }

    .activity-step__dot {
        margin: 0;
    }
}

@media (max-width: 640px) {
    .summary-strip,
    .season-metrics,
    .harvest-stats,
    .harvest-data-grid,
    .quality-flags-grid,
    .source-grid,
    .content-rail {
        grid-template-columns: 1fr;
    }

    .summary-strip__item {
        border-right: none;
        border-top: 0.5px solid #ecefea;
    }

    .summary-strip__item:first-child {
        border-top: none;
    }

    .summary-strip__item--score {
        text-align: left;
    }

    .profile-hero__actions {
        width: 100%;
    }

    .hero-button {
        flex: 1;
    }

    .section-header {
        flex-direction: column;
    }

    .section-header__stats {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
