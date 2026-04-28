<script setup>
import { computed, ref } from 'vue';
import {  Link } from '@inertiajs/vue3';
import {
    Box,
    Calendar,
    Checked,
    Connection,
    DataAnalysis,
    Document,
    List,
    TrendCharts,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import UpdateBatchModal from '@/Components/Modals/UpdateBatchModal.vue';

const props = defineProps({
    batch: {
        type: Object,
        required: true,
    },
    season: {
        type: Object,
        default: null,
    },
    harvests: {
        type: Array,
        default: () => [],
    },
});

const updateBatchModalOpen = ref(false);

const formatDate = (value) => {
    if (!value) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(value));
};

const formatMonthYear = (value) => {
    if (!value) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
};

const formatWeight = (value) => `${Number(value || 0).toLocaleString()}kg`;

const batchCode = computed(() => props.batch.batch_number || `#BTC-${String(props.batch.id).padStart(2, '0')}`);
const seasonName = computed(() => props.season?.name || 'Main Crop 2026');
const seasonCode = computed(() => props.season?.id ? `#S-${String(props.season.id).padStart(4, '0')}-MAIN` : '#S-2026-MAIN');
const totalYield = computed(() => Number(props.batch.net_weight_kg || props.season?.harvests_sum_weight || 12400));
const marketValue = computed(() => Number(props.batch.price || 58400));
const marketRate = computed(() => {
    const total = totalYield.value || 1;
    return (marketValue.value / total).toFixed(2);
});
const cupScore = computed(() => Number(props.batch.cup_score || 87.2).toFixed(1));
const moistureValue = computed(() => Number(props.batch.moisture_content || 11.2).toFixed(1));
const readinessScore = computed(() => 100);
const durationLabel = computed(() => {
    const start = props.season?.start_date ? formatMonthYear(props.season.start_date) : 'Feb 2026';
    const end = props.season?.end_date ? formatMonthYear(props.season.end_date) : 'Jun 2026';

    return `${start} - ${end}`;
});

const canManageBatch = computed(() => Boolean(props.batch.can_manage));

const badges = [
    'Season Linked',
    'Harvest Verified',
    'Quality Checked',
    'Export Ready',
];

const lifecycleSteps = [
    { key: 'season', label: 'Season', detail: 'Completed · Feb 2026', complete: true },
    { key: 'harvest', label: 'Harvests', detail: 'Verified · Oct 2026', complete: true },
    { key: 'batch', label: 'Batch', detail: 'Active · Oct 2026', current: true },
    { key: 'lot', label: 'Lot', detail: 'Pending Generation' },
    { key: 'market', label: 'Market', detail: 'Not Listed' },
];

const seasonSummaryItems = computed(() => [
    {
        label: 'Origin Season',
        primary: seasonName.value,
        secondary: `${durationLabel.value} · ${props.harvests.length || props.season?.harvests_count || 14} Harvests`,
        side: seasonCode.value,
    },
    {
        label: 'Health Score',
        primary: '92/100',
    },
    {
        label: 'Total Yield',
        primary: formatWeight(totalYield.value),
    },
]);

const harvestRows = computed(() => {
    if (props.harvests.length > 0) {
        return props.harvests.slice(0, 3).map((harvest) => ({
            id: harvest.id,
            code: `#HV-${String(harvest.id).padStart(4, '0')}`,
            date: formatDate(harvest.harvest_date),
            farm: harvest.farm?.name || 'Blue Ridge Estates',
            farmer: harvest.farm?.farmer
                ? `${harvest.farm.farmer.first_name?.[0] || ''}. ${harvest.farm.farmer.last_name || ''}`.trim()
                : 'M. Wanale',
            quantity: formatWeight(harvest.weight),
            moisture: `${Number(Math.max(10.8, Number(moistureValue.value) + ((Number(harvest.ripeness_percentage || 94) - 94) * -0.05))).toFixed(1)}%`,
        }));
    }

    return [
        { id: 4522, code: '#HV-4522', date: 'Oct 12, 2026', farm: 'Blue Ridge Estates', farmer: 'M. Wanale', quantity: '4,200kg', moisture: '11.2%' },
        { id: 4528, code: '#HV-4528', date: 'Oct 14, 2026', farm: 'Sipi Falls Central', farmer: 'J. Chemutai', quantity: '5,100kg', moisture: '11.1%' },
        { id: 4531, code: '#HV-4531', date: 'Oct 15, 2026', farm: 'Kapchorwa Heights', farmer: 'K. Sula', quantity: '3,100kg', moisture: '11.3%' },
    ];
});

const readinessChecklist = [
    'Season identity linked',
    'Individual harvests verified',
    'Aggregation qty confirmed',
    `Lab moisture checked (${moistureValue.value}%)`,
    'SCA quality report uploaded',
    'Dry mill processing complete',
];

const processingMethods = computed(() => [
    { label: 'Method', value: props.batch.processing_method || 'Washed' },
    { label: 'Fermentation', value: props.batch.drying_duration ? `${props.batch.drying_duration}h Aerobic` : '36h Aerobic' },
    { label: 'Drying', value: props.batch.drying_method || 'Raised African Beds' },
    { label: 'Duration', value: props.batch.drying_duration ? `${props.batch.drying_duration} Days` : '14 Days' },
    { label: 'Storage', value: props.batch.warehouse_location || 'Ventilated Silo' },
]);

const qualityScores = computed(() => [
    { label: 'Aroma Profile', value: 9.0 },
    { label: 'Flavor Complexity', value: 8.5 },
    { label: 'Body / Mouthfeel', value: 8.0 },
]);

const artifacts = [
    'Harvest Reports',
    'Processing Photos',
    'Quality Report',
    'Export Docs',
];

const timelineSteps = [
    { title: 'Season Created', date: 'Feb 01, 2026', complete: true },
    { title: 'Harvest Entry #HV-4531', date: 'Oct 15, 2026', complete: true },
    { title: 'Batch Aggregation Complete', date: 'Oct 18, 2026', complete: true },
    { title: 'Lot Generation Pending', date: 'Queued for action', active: true },
];
</script>

<template>
    <AppLayout title="Batch Profile" full-width flush :show-banner="false">

        <div class="batch-profile-page">
            <div class="batch-profile-shell">
                <section class="profile-hero">
                    <div class="profile-hero__copy">
                        <h1 class="title-with-icon title-with-icon--page">
                            <el-icon><Box /></el-icon>
                            <span class="ml-2">Batch Profile</span>
                        </h1>
                        <p>Verified coffee batch ready for lot creation</p>

                        <div class="profile-badges">
                            <span v-for="badge in badges" :key="badge" class="profile-badge">{{ badge }}</span>
                        </div>
                    </div>

                    <div class="profile-hero__actions">
                        <button
                            v-if="canManageBatch"
                            type="button"
                            class="profile-button profile-button--soft"
                            @click="updateBatchModalOpen = true"
                        >
                            Edit
                        </button>
                        <button type="button" class="profile-button profile-button--peach">Tokenise</button>
                        <Link :href="route('lot.create')" class="profile-button profile-button--solid">Create Lot</Link>
                    </div>
                </section>

                <section class="hero-panels">
                    <article class="hero-batch-card">
                        <div class="hero-batch-card__image"></div>

                        <div class="hero-batch-card__main">
                            <span class="hero-batch-card__code">{{ batchCode }}</span>
                            <h2>{{ batch.variety || 'Arabica SL-14' }}</h2>

                            <div class="hero-batch-card__meta">
                                <span>Mt Elgon, Uganda</span>
                                <span>YCFCU Processing</span>
                            </div>

                            <span class="active-pill">Active Batch</span>
                        </div>

                        <div class="hero-batch-card__value">
                            <span>Market Value</span>
                            <strong>Shs. {{ Number(marketValue).toLocaleString() }}</strong>
                            <p>{{ formatWeight(totalYield) }} @ ${{ marketRate }}/kg</p>
                        </div>

                        <div class="hero-batch-card__readiness">
                            <div class="readiness-circle">
                                <div>
                                    <strong>{{ readinessScore }}%</strong>
                                    <span>Ready</span>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="hero-score-card">
                        <span>Cup Quality Score</span>
                        <strong>{{ cupScore }}</strong>
                        <div class="score-pill">Excellence Grade</div>
                    </article>
                </section>

                <section class="lifecycle-strip">
                    <h2 class="title-with-icon mb-3">
                        <el-icon><Connection /></el-icon>
                        <span class="ml-2">Traceability Lifecycle</span>
                    </h2>

                    <div class="lifecycle-strip__track">
                        <div class="lifecycle-strip__line"></div>

                        <article
                            v-for="step in lifecycleSteps"
                            :key="step.key"
                            class="lifecycle-step"
                            :class="{
                                'is-complete': step.complete,
                                'is-current': step.current,
                            }"
                        >
                            <span class="lifecycle-step__icon"></span>
                            <strong>{{ step.label }}</strong>
                            <p>{{ step.detail }}</p>
                        </article>
                    </div>
                </section>

                <div class="profile-grid">
                    <main class="profile-main">
                        <section class="season-summary-card">
                            <article v-for="item in seasonSummaryItems" :key="item.label" class="season-summary-card__item">
                                <span>{{ item.label }}</span>
                                <strong>{{ item.primary }}</strong>
                                <p v-if="item.secondary">{{ item.secondary }}</p>
                                <em v-if="item.side">{{ item.side }}</em>
                            </article>
                        </section>

                        <section class="panel-card table-panel">
                            <div class="panel-card__head">
                                <h3 class="card-title">
                                    <el-icon><List /></el-icon>
                                    <span class="ml-2">Harvest Source Components</span>
                                </h3>
                                <Link :href="route('season.show', season?.id || 1)">View All Records</Link>
                            </div>

                            <div class="table-wrap">
                                <table class="source-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Date</th>
                                            <th>Source Farm</th>
                                            <th>Farmer</th>
                                            <th>Qty</th>
                                            <th>Moisture</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in harvestRows" :key="row.id">
                                            <td>
                                                <Link :href="route('harvest.show', row.id)" class="source-table__code">{{ row.code }}</Link>
                                            </td>
                                            <td>{{ row.date }}</td>
                                            <td>{{ row.farm }}</td>
                                            <td>{{ row.farmer }}</td>
                                            <td>{{ row.quantity }}</td>
                                            <td>{{ row.moisture }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="details-grid">
                            <article class="panel-card detail-panel">
                                <h3 class="card-title">
                                    <el-icon><Box /></el-icon>
                                    <span class="ml-2">Processing Methods</span>
                                </h3>

                                <div class="detail-list">
                                    <div v-for="item in processingMethods" :key="item.label" class="detail-list__row">
                                        <span>{{ item.label }}</span>
                                        <strong>{{ item.value }}</strong>
                                    </div>
                                </div>
                            </article>

                            <article class="panel-card detail-panel">
                                <h3 class="card-title">
                                    <el-icon><DataAnalysis /></el-icon>
                                    <span class="ml-2">Quality Forensics</span>
                                </h3>

                                <div class="quality-list">
                                    <div v-for="item in qualityScores" :key="item.label" class="quality-list__row">
                                        <div class="quality-list__head">
                                            <span>{{ item.label }}</span>
                                            <strong>{{ item.value.toFixed(1) }}/10</strong>
                                        </div>
                                        <div class="quality-list__bar">
                                            <div class="quality-list__fill" :style="{ width: `${item.value * 10}%` }"></div>
                                        </div>
                                    </div>

                                    <div class="quality-meta">
                                        <article>
                                            <span>Defects</span>
                                            <strong>{{ batch.defect_count ? `${batch.defect_count}/300g` : '2/300g' }}</strong>
                                        </article>
                                        <article>
                                            <span>Grade</span>
                                            <strong>Grade A</strong>
                                        </article>
                                    </div>
                                </div>
                            </article>
                        </section>

                        <section class="artifacts-panel">
                            <h3 class="title-with-icon mb-3">
                                <el-icon><Document /></el-icon>
                                <span class="ml-2">Verification Artifacts</span>
                            </h3>

                            <div class="artifacts-grid">
                                <article v-for="artifact in artifacts" :key="artifact" class="artifact-card">
                                    <span class="artifact-card__icon"></span>
                                    <strong>{{ artifact }}</strong>
                                </article>
                            </div>
                        </section>
                    </main>

                    <aside class="profile-rail">
                        <section class="rail-card rail-card--green">
                            <div class="rail-card__title-row">
                                <h3 class="card-title">
                                    <el-icon><Checked /></el-icon>
                                    <span class="ml-2">Readiness Checklist</span>
                                </h3>
                                <strong>100%</strong>
                            </div>

                            <div class="checklist-list">
                                <article v-for="item in readinessChecklist" :key="item" class="checklist-item">
                                    <span class="checklist-item__icon"></span>
                                    <span>{{ item }}</span>
                                </article>
                            </div>
                        </section>

                        <section class="rail-card">
                            <h3 class="card-title">
                                <el-icon><TrendCharts /></el-icon>
                                <span class="ml-2">Market Intelligence</span>
                            </h3>

                            <div class="market-intelligence">
                                <span>Suggested Batch Listing Price</span>
                                <strong>$5.10<span>/kg</span></strong>

                                <div class="market-intelligence__grid">
                                    <article>
                                        <span>Target Region</span>
                                        <strong>UAE / Dubai</strong>
                                    </article>
                                    <article>
                                        <span>Market Match</span>
                                        <strong>High Demand</strong>
                                    </article>
                                </div>

                                <blockquote>
                                    "High demand for Ugandan Arabica specialty profiles in the UAE market for Q4 export contracts."
                                </blockquote>
                            </div>
                        </section>

                        <section class="rail-card timeline-card">
                            <h3 class="card-title">
                                <el-icon><Calendar /></el-icon>
                                <span class="ml-2">Lifecycle Timeline</span>
                            </h3>

                            <div class="timeline-list">
                                <article
                                    v-for="step in timelineSteps"
                                    :key="step.title"
                                    class="timeline-step"
                                    :class="{
                                        'is-complete': step.complete,
                                        'is-active': step.active,
                                    }"
                                >
                                    <span class="timeline-step__dot"></span>
                                    <div>
                                        <strong>{{ step.title }}</strong>
                                        <p>{{ step.date }}</p>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </div>

        <UpdateBatchModal
            v-if="canManageBatch"
            v-model="updateBatchModalOpen"
            :batch="batch"
        />
    </AppLayout>
</template>

<style scoped>
.batch-profile-page {
    min-height: 100vh;
    background: #ffffff;
}

.batch-profile-shell {
    max-width: 1280px;
    margin: 0 auto;
    padding: 34px 28px 44px;
}

.profile-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 26px;
}

.profile-hero__copy h1 {
    margin: 0;
    font-size: 25px;
    line-height: 1.1;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: #111827;
}

.profile-hero__copy p {
    margin: 8px 0 0;
    font-size: 16px;
    color: #1f2937;
}

.title-with-icon,
.card-title {
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.title-with-icon .el-icon,
.card-title .el-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #f3f6f5;
    flex-shrink: 0;
    color: #6d7f95;
}

.title-with-icon--page .el-icon {
    width: 30px;
    height: 30px;
    font-size: 18px;
    color: #0d5b3f;
    background: #e8f6ef;
}

.title-with-icon span,
.card-title span {
    display: inline-block;
}

.profile-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 16px;
}

.profile-badge {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0 12px;
    border-radius: 999px;
    background: #a7f3d0;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #0f5132;
}

.profile-hero__actions {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-top: 12px;
}

.profile-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 20px;
    border: 1px solid #e7ecea;
    border-radius: 8px;
    background: #f4f6f5;
    font-size: 13px;
    font-weight: 600;
    color: #17202f;
    text-decoration: none;
    cursor: pointer;
}

.profile-button--peach {
    background: #ffd9b7;
    color: #754c24;
}

.profile-button--solid {
    border-color: #0d5b3f;
    background: #0d5b3f;
    color: #ffffff;
}

.hero-panels {
    display: grid;
    grid-template-columns: minmax(0, 2.2fr) minmax(250px, 1fr);
    gap: 28px;
}

.hero-batch-card,
.hero-score-card,
.season-summary-card,
.panel-card,
.rail-card {
    border: 1px solid #edf1ef;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 16px 30px rgba(15, 35, 23, 0.03);
}

.hero-batch-card {
    display: grid;
    grid-template-columns: 88px minmax(0, 1.3fr) minmax(0, 0.8fr) 118px;
    align-items: center;
    gap: 18px;
    padding: 28px;
    background: #f7f8f8;
}

.hero-batch-card__image {
    width: 88px;
    height: 120px;
    border-radius: 6px;
    background:
        radial-gradient(circle at 18% 24%, #b7d688 0 12%, transparent 12.5%),
        radial-gradient(circle at 40% 32%, #8fb05c 0 11%, transparent 11.5%),
        radial-gradient(circle at 68% 24%, #a0c76b 0 12%, transparent 12.5%),
        radial-gradient(circle at 26% 54%, #9abd63 0 12%, transparent 12.5%),
        radial-gradient(circle at 58% 52%, #7f9b4e 0 13%, transparent 13.5%),
        radial-gradient(circle at 34% 80%, #a4c56f 0 12%, transparent 12.5%),
        linear-gradient(135deg, #6b3e1d 0%, #c49173 32%, #9cc96f 33%, #6d9845 100%);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
}

.hero-batch-card__code {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #1f2937;
}

.hero-batch-card__main h2 {
    margin: 6px 0 12px;
    font-size: 20px;
    line-height: 1.1;
    font-weight: 700;
    color: #111827;
}

.hero-batch-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
    margin-bottom: 14px;
}

.hero-batch-card__meta span {
    position: relative;
    font-size: 12px;
    line-height: 1.4;
    color: #4b5563;
}

.hero-batch-card__meta span::before {
    content: '';
    display: inline-block;
    width: 12px;
    height: 12px;
    margin-right: 8px;
    border: 1.5px solid #374151;
    border-radius: 3px;
    vertical-align: -1px;
}

.active-pill {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0 12px;
    border-radius: 999px;
    background: #ffffff;
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
}

.active-pill::before {
    content: '';
    width: 8px;
    height: 8px;
    margin-right: 8px;
    border-radius: 999px;
    background: #70d7b0;
}

.hero-batch-card__value span,
.hero-score-card span,
.season-summary-card__item span,
.panel-card h3,
.rail-card h3 {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #5d6b7d;
}

.hero-batch-card__value strong {
    display: block;
    margin-top: 8px;
    font-size: 25px;
    line-height: 1;
    font-weight: 700;
    color: #0d5b3f;
}

.hero-batch-card__value p {
    margin: 8px 0 0;
    font-size: 12px;
    line-height: 1.45;
    color: #6b7280;
}

.readiness-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 90px;
    height: 90px;
    border-radius: 999px;
    background:
        conic-gradient(#0d5b3f 0deg 360deg, #d7dfdc 360deg 360deg);
}

.readiness-circle > div {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 74px;
    height: 74px;
    border-radius: inherit;
    background: #f7f8f8;
}

.readiness-circle strong {
    font-size: 21px;
    line-height: 1;
    font-weight: 700;
    color: #111827;
}

.readiness-circle span {
    margin-top: 4px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #5d6b7d;
}

.hero-score-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 28px 24px;
}

.hero-score-card strong {
    margin-top: 16px;
    font-size: 56px;
    line-height: 1;
    font-weight: 700;
    color: #0d5b3f;
}

.score-pill {
    display: inline-flex;
    align-items: center;
    min-height: 24px;
    margin-top: 12px;
    padding: 0 14px;
    border-radius: 999px;
    background: #a7f3d0;
    font-size: 12px;
    font-weight: 700;
    color: #0f5132;
}

.lifecycle-strip {
    margin-top: 34px;
}

.lifecycle-strip h2,
.artifacts-panel h3 {
    margin: 0 0 22px;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 0.01em;
    text-transform: none;
    color: #6d7f95;
}

.lifecycle-strip__track {
    position: relative;
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 18px;
}

.lifecycle-strip__line {
    position: absolute;
    top: 18px;
    left: 18px;
    right: 18px;
    height: 2px;
    background: #dbe2df;
}

.lifecycle-step {
    position: relative;
    padding-top: 0;
}

.lifecycle-step__icon {
    position: relative;
    z-index: 1;
    display: inline-flex;
    width: 38px;
    height: 38px;
    border: 1px solid #dbe2df;
    border-radius: 12px;
    background: #f7f8f8;
}

.lifecycle-step.is-complete .lifecycle-step__icon,
.lifecycle-step.is-current .lifecycle-step__icon {
    background: #0d5b3f;
    border-color: #0d5b3f;
    box-shadow: 0 0 0 5px rgba(126, 236, 192, 0.26);
}

.lifecycle-step.is-complete .lifecycle-step__icon::before,
.lifecycle-step.is-current .lifecycle-step__icon::before {
    content: '';
    width: 14px;
    height: 8px;
    margin: auto;
    border-left: 2px solid #ffffff;
    border-bottom: 2px solid #ffffff;
    transform: rotate(-45deg);
}

.lifecycle-step strong {
    display: block;
    margin-top: 16px;
    font-size: 15px;
    font-weight: 700;
    color: #111827;
}

.lifecycle-step p {
    margin: 4px 0 0;
    font-size: 12px;
    line-height: 1.4;
    color: #6b7280;
}

.profile-grid {
    display: grid;
    grid-template-columns: minmax(0, 2.15fr) minmax(270px, 1fr);
    gap: 28px;
    margin-top: 34px;
}

.profile-main,
.profile-rail {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.season-summary-card {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 0.7fr) minmax(0, 0.7fr);
    gap: 18px;
    padding: 22px 24px;
    background: #f7f8f8;
}

.season-summary-card__item strong {
    display: block;
    margin-top: 10px;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.season-summary-card__item p,
.season-summary-card__item em {
    margin: 4px 0 0;
    font-size: 13px;
    line-height: 1.4;
    font-style: normal;
    color: #6b7280;
}

.panel-card {
    padding: 0;
}

.panel-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 24px;
    border-bottom: 1px solid #edf1ef;
}

.panel-card__head h3,
.detail-panel h3,
.artifacts-panel h3,
.rail-card h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.01em;
    text-transform: none;
    color: #111827;
}

.panel-card__head a {
    font-size: 13px;
    color: #0d5b3f;
    text-decoration: none;
}

.table-wrap {
    overflow-x: auto;
}

.source-table {
    width: 100%;
    border-collapse: collapse;
}

.source-table thead th {
    padding: 16px 24px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    text-align: left;
    color: #7a8698;
    background: #fafbfb;
}

.source-table tbody td {
    padding: 18px 24px;
    border-top: 1px solid #eef2f0;
    font-size: 13px;
    line-height: 1.5;
    color: #1f2937;
}

.source-table__code {
    font-weight: 700;
    color: #0f172a;
    text-decoration: none;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 28px;
}

.detail-panel {
    padding: 24px;
    background: #f7f8f8;
}

.detail-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-top: 24px;
}

.detail-list__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.detail-list__row span {
    font-size: 13px;
    color: #374151;
}

.detail-list__row strong {
    font-size: 13px;
    font-weight: 700;
    color: #0d5b3f;
}

.quality-list {
    margin-top: 24px;
}

.quality-list__row + .quality-list__row {
    margin-top: 20px;
}

.quality-list__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 8px;
}

.quality-list__head span {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #5d6b7d;
}

.quality-list__head strong {
    font-size: 12px;
    font-weight: 700;
    color: #111827;
}

.quality-list__bar {
    height: 5px;
    border-radius: 999px;
    background: #e5ebe8;
    overflow: hidden;
}

.quality-list__fill {
    height: 100%;
    border-radius: inherit;
    background: #0d5b3f;
}

.quality-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-top: 24px;
}

.quality-meta article {
    padding: 14px 16px;
    border-radius: 8px;
    background: #ffffff;
}

.quality-meta span {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #8a97a8;
}

.quality-meta strong {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #111827;
}

.quality-meta article:first-child strong {
    color: #dc2626;
}

.artifacts-panel h3 {
    color: #111827;
}

.artifacts-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.artifact-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 18px;
    min-height: 132px;
    border: 1px solid #edf1ef;
    border-radius: 12px;
    background: #ffffff;
}

.artifact-card__icon {
    width: 24px;
    height: 30px;
    border: 2px solid #9aa9c0;
    border-radius: 4px;
    position: relative;
}

.artifact-card__icon::after {
    content: '';
    position: absolute;
    top: 5px;
    left: 5px;
    right: 5px;
    height: 2px;
    background: #9aa9c0;
    box-shadow: 0 6px 0 #9aa9c0;
}

.artifact-card strong {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.01em;
    text-transform: none;
    text-align: center;
    color: #111827;
}

.profile-rail {
    gap: 30px;
}

.rail-card {
    padding: 24px;
}

.rail-card--green {
    background: #0d5b3f;
    border-color: #0d5b3f;
    color: #ffffff;
}

.rail-card__title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.rail-card__title-row h3 {
    color: inherit;
}

.rail-card__title-row h3 .el-icon {
    color: inherit;
}

.rail-card__title-row strong {
    font-size: 25px;
    line-height: 1;
    font-weight: 700;
}

.checklist-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-top: 24px;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: inherit;
}

.checklist-item__icon {
    width: 18px;
    height: 18px;
    border: 1.5px solid currentColor;
    border-radius: 999px;
    position: relative;
    flex-shrink: 0;
}

.checklist-item__icon::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 2px;
    width: 5px;
    height: 9px;
    border-right: 1.5px solid currentColor;
    border-bottom: 1.5px solid currentColor;
    transform: rotate(40deg);
}

.market-intelligence {
    margin-top: 26px;
}

.market-intelligence > span {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #8a97a8;
}

.market-intelligence > strong {
    display: block;
    margin-top: 10px;
    font-size: 25px;
    line-height: 1;
    font-weight: 700;
    color: #7c5731;
}

.market-intelligence > strong span {
    font-size: 16px;
    font-weight: 500;
}

.market-intelligence__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 22px;
}

.market-intelligence__grid article {
    padding: 12px;
    border-radius: 8px;
    background: #f5f7f6;
}

.market-intelligence__grid span {
    display: block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #8a97a8;
}

.market-intelligence__grid strong {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    line-height: 1.4;
    font-weight: 700;
    color: #111827;
}

.market-intelligence blockquote {
    margin: 22px 0 0;
    padding: 18px;
    border-radius: 10px;
    background: #fff1e4;
    font-size: 13px;
    line-height: 1.7;
    color: #442c17;
}

.timeline-card {
    background: #f7f8f8;
}

.timeline-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-top: 22px;
}

.timeline-step {
    display: grid;
    grid-template-columns: 18px minmax(0, 1fr);
    gap: 14px;
    align-items: flex-start;
}

.timeline-step__dot {
    width: 18px;
    height: 18px;
    margin-top: 1px;
    border: 2px solid #cad4cf;
    border-radius: 999px;
    background: #ffffff;
    position: relative;
}

.timeline-step.is-complete .timeline-step__dot {
    border-color: #0d5b3f;
    background: #0d5b3f;
}

.timeline-step.is-complete .timeline-step__dot::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border-right: 1.5px solid #ffffff;
    border-bottom: 1.5px solid #ffffff;
    transform: rotate(40deg);
}

.timeline-step.is-active .timeline-step__dot {
    border-color: #0d5b3f;
}

.timeline-step.is-active .timeline-step__dot::after {
    content: '';
    position: absolute;
    inset: 3px;
    border-radius: inherit;
    background: #0d5b3f;
}

.timeline-step strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}

.timeline-step p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #6b7280;
}

@media (max-width: 1180px) {
    .hero-panels,
    .profile-grid,
    .details-grid {
        grid-template-columns: 1fr;
    }

    .hero-batch-card {
        grid-template-columns: 88px minmax(0, 1fr);
    }

    .hero-batch-card__value,
    .hero-batch-card__readiness {
        grid-column: 2;
    }

    .artifacts-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 900px) {
    .batch-profile-shell {
        padding: 24px 18px 36px;
    }

    .profile-hero {
        flex-direction: column;
    }

    .profile-hero__actions {
        width: 100%;
        flex-wrap: wrap;
        padding-top: 0;
    }

    .season-summary-card {
        grid-template-columns: 1fr;
    }

    .source-table thead {
        display: none;
    }

    .source-table,
    .source-table tbody,
    .source-table tr,
    .source-table td {
        display: block;
        width: 100%;
    }

    .source-table tbody td {
        padding: 8px 24px;
        border-top: 0;
    }

    .source-table tbody tr {
        padding: 14px 0;
        border-top: 1px solid #eef2f0;
    }
}

@media (max-width: 640px) {
    .profile-hero__copy h1 {
        font-size: 23px;
    }

    .profile-hero__copy p {
        font-size: 14px;
    }

    .hero-batch-card {
        grid-template-columns: 1fr;
        padding: 22px;
    }

    .hero-batch-card__image {
        width: 100%;
        max-width: 96px;
    }

    .hero-batch-card__value,
    .hero-batch-card__readiness {
        grid-column: auto;
    }

    .artifacts-grid {
        grid-template-columns: 1fr;
    }

    .market-intelligence__grid {
        grid-template-columns: 1fr;
    }
}
</style>
