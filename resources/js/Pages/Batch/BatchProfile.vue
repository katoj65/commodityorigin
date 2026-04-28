<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    Box,
    Calendar,
    Checked,
    Connection,
    DataAnalysis,
    Document,
    Download,
    Files,
    List,
    Location,
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

const formatNumber = (value, digits = 0) => Number(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
});

const formatMonthYear = (value) => {
    if (!value) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
};

const batchCode = computed(() => props.batch.batch_number || `ETH-YR-2023-${String(props.batch.id).padStart(4, '0')}`);
const gradeLabel = computed(() => props.batch.grade || 'G1');
const batchTitle = computed(() => props.batch.variety || 'Yirgacheffe');
const titleSuffix = computed(() => `${gradeLabel.value}-Specialty`);
const totalQuantity = computed(() => Number(props.batch.net_weight_kg || props.season?.harvests_sum_weight || 4200));
const commercialValue = computed(() => Number(props.batch.price || 32540));
const cupScore = computed(() => Number(props.batch.cup_score || 87.2));
const moistureScore = computed(() => Number(props.batch.moisture_content || 11.2));
const densityValue = computed(() => Math.max(720, Math.round(cupScore.value * 8.1)));
const waterActivity = computed(() => 0.58);
const seasonTitle = computed(() => props.season?.name || 'Main Harvest 2023/24');
const seasonRegion = computed(() => props.season?.region || 'Central Highlands Region');
const seasonHealth = computed(() => Number(props.batch.cup_score || 88.4));
const activeFarms = computed(() => props.harvests.length || props.season?.harvests_count || 12);
const readinessScore = computed(() => {
    let score = 88;

    if (props.season) score += 1;
    if (props.harvests.length > 0) score += 1;
    if (cupScore.value >= 87) score += 1;
    if (moistureScore.value <= 12) score += 1;

    return Math.min(score, 92);
});
const seasonHref = computed(() => (props.season?.id ? route('season.show', props.season.id) : route('batch.index')));

const journeySteps = [
    { label: 'Harvest', complete: true },
    { label: 'Milling', complete: true },
    { label: 'Export', complete: false },
];

const processingItems = computed(() => [
    {
        title: props.batch.processing_method || 'Washed Process',
        subtitle: 'Processing Method',
        icon: Checked,
    },
    {
        title: props.batch.drying_duration ? `${props.batch.drying_duration}h Fermentation` : '36h Fermentation',
        subtitle: 'Controlled Anaerobic',
        icon: TrendCharts,
    },
    {
        title: props.batch.warehouse_location || 'Silo B-12 (Temp Controlled)',
        subtitle: 'Current Location',
        icon: Box,
    },
]);

const readinessItems = computed(() => [
    { label: 'Season Linkage Complete', complete: true },
    { label: 'Milling Report Verified', complete: true },
    { label: 'Cupping Score Approved', complete: true },
    { label: 'Export Logistics Quote', complete: false },
]);

const harvestRows = computed(() => {
    if (props.harvests.length > 0) {
        return props.harvests.slice(0, 3).map((harvest, index) => ({
            id: harvest.id,
            harvestCode: `HV-24-${String(index + 1).padStart(3, '0')}`,
            farmIdentity: harvest.farm?.name || `Source Estate ${index + 1}`,
            quantity: `${formatNumber(harvest.weight || 0)} KG`,
            grade: props.batch.grade || 'G1',
            status: 'Verified',
        }));
    }

    return [
        { id: 1, harvestCode: 'HV-24-001', farmIdentity: 'Aricha Cooperative', quantity: '1,200 KG', grade: 'G1', status: 'Verified' },
        { id: 2, harvestCode: 'HV-24-005', farmIdentity: 'Misty Mountain Farm', quantity: '850 KG', grade: 'G1', status: 'Verified' },
        { id: 3, harvestCode: 'HV-24-012', farmIdentity: 'Blue Nile Estate', quantity: '1,150 KG', grade: 'G1', status: 'Verified' },
    ];
});

const intelligenceBars = computed(() => [
    { label: 'Moisture Content', value: `${formatNumber(moistureScore.value, 1)}%`, width: 81 },
    { label: 'Water Activity', value: `${waterActivity.value.toFixed(2)}AW`, width: 73 },
    { label: 'Density', value: `${densityValue.value}G/L`, width: 89 },
]);

const sensoryNotes = [
    { title: 'Aroma', description: 'Floral, Jasmine, Peach Blossom' },
    { title: 'Body', description: 'Silky, Medium, Tea-like' },
    { title: 'Flavor', description: 'Lemon, Honey, Berries' },
    { title: 'Acidity', description: 'Bright, Citric, Complex' },
];

const documentTiles = [
    { title: 'Quality Report', meta: 'PDF • 2.4 MB', icon: Document },
    { title: 'Certification', meta: 'PNG • 1.1 MB', icon: Checked },
    { title: 'Export Docs', meta: 'DOC • 0.8 MB', icon: Files },
    { title: 'Add Document', meta: '', icon: null, isAdd: true },
];

const marketBars = [28, 42, 56, 68, 82];
const canManageBatch = computed(() => Boolean(props.batch.can_manage));
</script>

<template>
    <AppLayout title="Batch Profile" full-width flush :show-banner="false">
        <div class="batch-profile-page">
            <div class="batch-profile-shell">
                <section class="top-summary-card">
                    <div class="top-summary-grid">
                        <article class="summary-block summary-block--identity">
                            <span class="block-label">Batch Identity</span>
                            <h1>{{ batchTitle }}<br>{{ titleSuffix }}</h1>
                            <p>ID : {{ batchCode }}</p>
                        </article>

                        <article class="summary-block summary-block--journey">
                            <span class="block-label">Traceability Journey</span>

                            <div class="journey-track">
                                <div
                                    v-for="(step, index) in journeySteps"
                                    :key="step.label"
                                    class="journey-step"
                                    :class="{ 'is-complete': step.complete }"
                                >
                                    <span class="journey-step__dot"></span>
                                    <span v-if="index < journeySteps.length - 1" class="journey-step__line"></span>
                                    <strong>{{ step.label }}</strong>
                                </div>
                            </div>
                        </article>

                        <article class="summary-block summary-block--readiness">
                            <div class="mini-ring" :style="{ '--score': `${readinessScore}` }">
                                <div class="mini-ring__inner">{{ readinessScore }}%</div>
                            </div>

                            <div class="summary-block__readiness-copy">
                                <span class="block-label">Readiness</span>
                                <strong>Market<br>Ready</strong>
                            </div>
                        </article>

                        <article class="summary-block summary-block--value">
                            <span class="block-label">Commercial Value</span>

                            <div class="commercial-grid">
                                <div>
                                    <span>Qty</span>
                                    <strong>{{ formatNumber(totalQuantity) }} KG</strong>
                                </div>

                                <div>
                                    <span>Value</span>
                                    <strong class="value-accent">Shs. {{ formatNumber(commercialValue) }}</strong>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="summary-actions">
                        <div class="summary-actions__group">
                            <Link :href="route('batch.create-lot', batch.id)" class="action-button action-button--green">
                                <el-icon><Files /></el-icon>
                                <span>Create Lot</span>
                            </Link>

                            <button
                                v-if="canManageBatch"
                                type="button"
                                class="action-button action-button--peach"
                                @click="updateBatchModalOpen = true"
                            >
                                <el-icon><List /></el-icon>
                                <span>Edit</span>
                            </button>

                            <Link :href="seasonHref" class="action-button action-button--neutral">
                                <el-icon><Connection /></el-icon>
                                <span>View Harvests</span>
                            </Link>
                        </div>

                        <button type="button" class="download-report">
                            <el-icon><Download /></el-icon>
                            <span>Download Report</span>
                        </button>
                    </div>

                    <div class="insight-banner">
                        <span class="insight-banner__icon">✦</span>
                        <p><strong>AI Insight:</strong> This batch is strongly traceable and ready for lot creation. Optimization suggested for UAE market placement.</p>
                    </div>
                </section>

                <div class="profile-grid">
                    <main class="profile-main">
                        <section class="feature-row">
                            <article class="surface-card season-card">
                                <div class="card-head">
                                    <span class="card-label">Season Context</span>
                                    <span class="status-pill">Active</span>
                                </div>

                                <h2>{{ seasonTitle }}</h2>
                                <p>{{ seasonRegion }}</p>

                                <div class="season-stats">
                                    <div>
                                        <strong>{{ formatNumber(seasonHealth, 1) }}</strong>
                                        <span>Health Score</span>
                                    </div>
                                    <div>
                                        <strong>{{ activeFarms }}</strong>
                                        <span>Active Farms</span>
                                    </div>
                                </div>

                                <Link :href="seasonHref" class="season-link">View Season Details</Link>
                            </article>

                            <article class="surface-card processing-card">
                                <span class="card-label">Processing &amp; Storage</span>

                                <div class="processing-list">
                                    <div v-for="item in processingItems" :key="item.title" class="processing-item">
                                        <div class="processing-item__icon">
                                            <el-icon><component :is="item.icon" /></el-icon>
                                        </div>

                                        <div>
                                            <strong>{{ item.title }}</strong>
                                            <span>{{ item.subtitle }}</span>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            <article class="surface-card readiness-card">
                                <span class="card-label">Lot Readiness Checklist</span>

                                <div class="readiness-progress">
                                    <strong>{{ readinessScore }}%</strong>
                                    <div class="readiness-progress__bar">
                                        <span :style="{ width: `${readinessScore}%` }"></span>
                                    </div>
                                </div>

                                <div class="readiness-list">
                                    <div
                                        v-for="item in readinessItems"
                                        :key="item.label"
                                        class="readiness-item"
                                        :class="{ 'is-complete': item.complete }"
                                    >
                                        <span class="readiness-item__dot"></span>
                                        <span>{{ item.label }}</span>
                                    </div>
                                </div>
                            </article>
                        </section>

                        <section class="surface-card harvest-card">
                            <div class="section-head">
                                <span class="card-label">Harvest Sources</span>
                                <span class="section-meta">{{ harvestRows.length }} Selected Sources</span>
                            </div>

                            <div class="harvest-table">
                                <div class="harvest-table__head">
                                    <span>Harvest ID</span>
                                    <span>Farm Identity</span>
                                    <span>Quantity</span>
                                    <span>Grade</span>
                                    <span>Status</span>
                                </div>

                                <div v-for="row in harvestRows" :key="row.id" class="harvest-table__row">
                                    <span>{{ row.harvestCode }}</span>
                                    <span>{{ row.farmIdentity }}</span>
                                    <span>{{ row.quantity }}</span>
                                    <span>{{ row.grade }}</span>
                                    <span class="table-status">{{ row.status }}</span>
                                </div>
                            </div>
                        </section>

                        <section class="quality-panel">
                            <div class="quality-panel__top">
                                <div>
                                    <span class="quality-panel__label">Quality Intelligence</span>
                                    <h2>Institutional Grade Analysis</h2>
                                </div>

                                <div class="quality-tags">
                                    <span>Specialty</span>
                                    <span>Premium</span>
                                </div>
                            </div>

                            <div class="quality-panel__body">
                                <div class="score-tile">
                                    <strong>{{ formatNumber(cupScore, 1) }}</strong>
                                    <span>SCA Cupping Score</span>
                                    <div class="score-tile__stars">* * * * *</div>
                                </div>

                                <div class="intelligence-bars">
                                    <div v-for="item in intelligenceBars" :key="item.label" class="intelligence-bar">
                                        <div class="intelligence-bar__head">
                                            <span>{{ item.label }}</span>
                                            <strong>{{ item.value }}</strong>
                                        </div>
                                        <div class="intelligence-bar__track">
                                            <span :style="{ width: `${item.width}%` }"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="sensory-breakdown">
                                    <span class="sensory-breakdown__label">Sensory Breakdown</span>

                                    <div class="sensory-grid">
                                        <div v-for="item in sensoryNotes" :key="item.title" class="sensory-item">
                                            <strong>{{ item.title }}</strong>
                                            <p>{{ item.description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </main>

                    <aside class="profile-side">
                        <section class="surface-card market-card">
                            <span class="card-label">Market Intelligence</span>

                            <div class="market-meta">
                                <div>
                                    <span>Target Market</span>
                                    <strong>UAE / Dubai</strong>
                                </div>
                                <div>
                                    <span>Demand Level</span>
                                    <strong>Critical High</strong>
                                </div>
                            </div>

                            <div class="market-chart">
                                <span class="market-chart__label">Price Trend (30D)</span>

                                <div class="market-chart__bars">
                                    <span
                                        v-for="height in marketBars"
                                        :key="height"
                                        :style="{ height: `${height}px` }"
                                    ></span>
                                </div>
                            </div>
                        </section>

                        <section class="surface-card documents-card">
                            <div class="section-head section-head--tight">
                                <span class="card-label">Documents &amp; Evidence</span>
                                <span class="section-meta">...</span>
                            </div>

                            <div class="documents-grid">
                                <article
                                    v-for="item in documentTiles"
                                    :key="item.title"
                                    class="document-tile"
                                    :class="{ 'is-add': item.isAdd }"
                                >
                                    <template v-if="item.isAdd">
                                        <span class="document-tile__plus">+</span>
                                    </template>

                                    <template v-else>
                                        <div class="document-tile__icon">
                                            <el-icon><component :is="item.icon" /></el-icon>
                                        </div>
                                        <strong>{{ item.title }}</strong>
                                        <span>{{ item.meta }}</span>
                                    </template>
                                </article>
                            </div>
                        </section>

                        <section class="advisor-card">
                            <div class="advisor-card__title">
                                <div class="advisor-card__icon">
                                    <el-icon><DataAnalysis /></el-icon>
                                </div>
                                <strong>AI Batch Advisor</strong>
                            </div>

                            <p>
                                “Based on current market trends in the Middle East, this Yirgacheffe profile is fetching a 12% premium. I recommend initiating the UAE Export Permit immediately.”
                            </p>

                            <div class="advisor-card__footer">
                                <div>
                                    <span>Confidence</span>
                                    <strong>94.8%</strong>
                                </div>

                                <button type="button">Apply Recommendation</button>
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
    max-width: 1240px;
    margin: 0 auto;
    padding: 18px 18px 42px;
}

.top-summary-card,
.surface-card,
.advisor-card {
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #ebf0f3;
    box-shadow: 0 12px 26px rgba(17, 24, 39, 0.04);
}

.top-summary-card {
    padding: 26px 28px 30px;
}

.top-summary-grid {
    display: grid;
    grid-template-columns: 1.1fr 1fr 0.9fr 0.9fr;
    gap: 0;
}

.summary-block {
    min-height: 128px;
    padding: 0 28px;
    border-right: 1px solid #edf1f4;
}

.summary-block:first-child {
    padding-left: 0;
}

.summary-block:last-child {
    padding-right: 0;
    border-right: 0;
}

.block-label,
.card-label {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    color: #7d6950;
}

.block-label {
    font-size: 13px;
    letter-spacing: 0.12em;
}

.card-label {
    font-size: 13px;
    letter-spacing: 0.1em;
    color: #6f573a;
}

.summary-block--identity h1 {
    margin: 16px 0 14px;
    font-size: 24px;
    line-height: 1.18;
    font-weight: 700;
    letter-spacing: -0.04em;
    color: #12161c;
}

.summary-block--identity p {
    margin: 0;
    font-size: 14px;
    color: #585f6c;
}

.journey-track {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    margin-top: 24px;
}

.journey-step {
    position: relative;
    display: grid;
    justify-items: center;
    gap: 18px;
}

.journey-step__dot {
    position: relative;
    z-index: 1;
    width: 12px;
    height: 12px;
    border-radius: 999px;
    background: #c9d6df;
}

.journey-step.is-complete .journey-step__dot {
    background: #17b47a;
}

.journey-step__line {
    position: absolute;
    top: 5px;
    left: calc(50% + 6px);
    width: calc(100% - 12px);
    height: 2px;
    background: #dce7e3;
}

.journey-step strong {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #1f2630;
}

.summary-block--readiness {
    display: flex;
    align-items: center;
    gap: 18px;
}

.mini-ring {
    --score: 92;
    width: 86px;
    height: 86px;
    border-radius: 50%;
    background: conic-gradient(#08a06c calc(var(--score) * 1%), #dce8e4 0);
    display: grid;
    place-items: center;
}

.mini-ring__inner {
    width: 74px;
    height: 74px;
    border-radius: inherit;
    background: #ffffff;
    display: grid;
    place-items: center;
    font-size: 28px;
    font-weight: 700;
    color: #182029;
}

.summary-block__readiness-copy {
    display: grid;
    gap: 8px;
}

.summary-block__readiness-copy strong {
    font-size: 16px;
    line-height: 1.2;
    color: #15202c;
}

.commercial-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    margin-top: 16px;
}

.commercial-grid span,
.market-meta span,
.season-stats span,
.processing-item span,
.intelligence-bar__head span,
.document-tile span,
.advisor-card__footer span {
    display: block;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #7a818c;
}

.commercial-grid strong {
    display: block;
    margin-top: 6px;
    font-size: 28px;
    font-weight: 700;
    color: #141922;
}

.commercial-grid .value-accent {
    color: #0a8c57;
}

.summary-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding-top: 28px;
    margin-top: 20px;
    border-top: 1px solid #edf1f4;
}

.summary-actions__group {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.action-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-height: 46px;
    padding: 0 22px;
    border-radius: 6px;
    border: 1px solid transparent;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    color: #161d25;
    cursor: pointer;
}

.action-button :deep(svg),
.download-report :deep(svg) {
    font-size: 16px;
}

.action-button--green {
    background: #0d5a3d;
    color: #ffffff;
}

.action-button--peach {
    background: #ffd6b0;
    color: #37210d;
}

.action-button--neutral {
    background: #eef2f5;
    color: #101822;
}

.download-report {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 0;
    border: 0;
    background: transparent;
    font-size: 15px;
    font-weight: 500;
    color: #16212c;
    cursor: pointer;
}

.insight-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 30px;
    padding: 16px 18px;
    border-radius: 10px;
    border: 1px solid #c9f0df;
    background: #f4fff9;
}

.insight-banner__icon {
    font-size: 20px;
    color: #0a9960;
}

.insight-banner p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: #295244;
}

.profile-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.8fr);
    gap: 28px;
    margin-top: 28px;
}

.profile-main,
.profile-side {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.feature-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 28px;
}

.surface-card {
    padding: 22px 22px 24px;
}

.card-head,
.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    padding: 0 10px;
    border-radius: 6px;
    background: #b8f0cf;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: #0d563a;
}

.season-card h2 {
    margin: 34px 0 6px;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: #111720;
}

.season-card p {
    margin: 0;
    font-size: 14px;
    color: #66707f;
}

.season-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    margin: 28px 0 24px;
}

.season-stats strong {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #0e6646;
}

.season-stats div:last-child strong {
    color: #131922;
}

.season-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    border-radius: 4px;
    border: 1px solid #d3dde2;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    color: #16212b;
}

.processing-list {
    display: grid;
    gap: 14px;
    margin-top: 24px;
}

.processing-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.processing-item__icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: #f1f4f6;
    display: grid;
    place-items: center;
    color: #0b6642;
    flex-shrink: 0;
}

.processing-item strong {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #101720;
}

.processing-item span {
    margin-top: 3px;
}

.readiness-progress {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 26px;
}

.readiness-progress strong {
    font-size: 40px;
    line-height: 1;
    font-weight: 700;
    color: #0b8458;
}

.readiness-progress__bar {
    flex: 1;
    height: 10px;
    border-radius: 999px;
    background: #edf3ef;
    overflow: hidden;
}

.readiness-progress__bar span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: #0b9b67;
}

.readiness-list {
    display: grid;
    gap: 18px;
    margin-top: 30px;
}

.readiness-item {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 15px;
    color: #1b232d;
}

.readiness-item__dot {
    width: 14px;
    height: 14px;
    border-radius: 999px;
    border: 1.5px solid #c4d0dc;
    flex-shrink: 0;
}

.readiness-item.is-complete .readiness-item__dot {
    border-color: #11a36a;
    position: relative;
}

.readiness-item.is-complete .readiness-item__dot::after {
    content: '';
    position: absolute;
    inset: 3px;
    border-radius: inherit;
    background: #11a36a;
}

.section-meta {
    font-size: 13px;
    color: #7b8490;
}

.harvest-table {
    margin-top: 18px;
}

.harvest-table__head,
.harvest-table__row {
    display: grid;
    grid-template-columns: 1.1fr 1.8fr 0.9fr 0.6fr 0.7fr;
    gap: 18px;
}

.harvest-table__head {
    padding: 8px 0 14px;
    border-bottom: 1px solid #edf1f4;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6a727d;
}

.harvest-table__row {
    padding: 18px 0;
    border-bottom: 1px solid #f0f3f6;
    font-size: 15px;
    color: #121b25;
}

.harvest-table__row:last-child {
    border-bottom: 0;
}

.table-status {
    color: #0c8f5a;
    font-weight: 600;
}

.quality-panel {
    padding: 28px 30px 30px;
    border-radius: 8px;
    background: #0f5f43;
    color: #ffffff;
}

.quality-panel__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
}

.quality-panel__label {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #3fd09a;
}

.quality-panel h2 {
    margin: 14px 0 0;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -0.03em;
}

.quality-tags {
    display: flex;
    gap: 10px;
}

.quality-tags span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1px solid rgba(148, 220, 188, 0.35);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #f0fff7;
}

.quality-panel__body {
    display: grid;
    grid-template-columns: 160px minmax(0, 1fr) minmax(220px, 0.9fr);
    gap: 36px;
    margin-top: 30px;
    align-items: start;
}

.score-tile {
    padding: 22px 22px 20px;
    border-radius: 14px;
    background: rgba(0, 0, 0, 0.12);
}

.score-tile strong {
    display: block;
    font-size: 48px;
    line-height: 1;
    font-weight: 700;
    color: #38dd9d;
}

.score-tile span {
    display: block;
    margin-top: 10px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    color: #effff7;
}

.score-tile__stars {
    margin-top: 18px;
    font-size: 14px;
    letter-spacing: 0.22em;
    color: #3de1a0;
}

.intelligence-bars {
    display: grid;
    gap: 20px;
}

.intelligence-bar__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 8px;
}

.intelligence-bar__head span,
.intelligence-bar__head strong {
    color: #f4fff9;
}

.intelligence-bar__track {
    height: 6px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    overflow: hidden;
}

.intelligence-bar__track span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: #47dba2;
}

.sensory-breakdown__label {
    display: block;
    margin-bottom: 14px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #3fd09a;
}

.sensory-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px 22px;
}

.sensory-item strong {
    display: block;
    font-size: 22px;
    margin-bottom: 6px;
}

.sensory-item p {
    margin: 0;
    font-size: 14px;
    line-height: 1.45;
    color: rgba(255, 255, 255, 0.72);
}

.market-card,
.documents-card {
    padding: 20px 20px 22px;
}

.market-meta {
    display: grid;
    gap: 18px;
    margin-top: 20px;
}

.market-meta strong {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 8px;
    min-height: 32px;
    padding: 0 16px;
    border-radius: 4px;
    background: #0d5b3d;
    font-size: 15px;
    color: #ffffff;
}

.market-meta div:last-child strong {
    justify-content: flex-start;
    padding: 0;
    min-height: auto;
    border-radius: 0;
    background: transparent;
    color: #0b8b57;
}

.market-chart {
    margin-top: 26px;
    padding: 18px 18px 16px;
    border-radius: 8px;
    background: #f2f5f7;
}

.market-chart__label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #646c78;
}

.market-chart__bars {
    display: flex;
    align-items: flex-end;
    gap: 5px;
    height: 86px;
    margin-top: 18px;
}

.market-chart__bars span {
    flex: 1;
    border-radius: 2px 2px 0 0;
    background: #7acbb0;
}

.market-chart__bars span:last-child {
    background: #119764;
}

.section-head--tight {
    align-items: center;
}

.documents-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
    margin-top: 22px;
}

.document-tile {
    min-height: 90px;
    padding: 18px 16px;
    border-radius: 10px;
    background: #f5f7f9;
    display: grid;
    align-content: center;
    gap: 8px;
}

.document-tile__icon {
    width: 24px;
    height: 24px;
    display: grid;
    place-items: center;
    color: #0c6242;
}

.document-tile strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #151c24;
    text-transform: uppercase;
}

.document-tile.is-add {
    place-items: center;
    border: 1px dashed #ced7dd;
    background: #fafbfc;
}

.document-tile__plus {
    font-size: 34px;
    line-height: 1;
    color: #161d24;
}

.advisor-card {
    padding: 22px 20px 20px;
    background: #0b5b3c;
    color: #ffffff;
    box-shadow: 0 18px 30px rgba(6, 36, 22, 0.2);
}

.advisor-card__title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.advisor-card__icon {
    width: 24px;
    height: 24px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, 0.35);
}

.advisor-card__title strong {
    font-size: 25px;
    font-weight: 700;
}

.advisor-card p {
    margin: 24px 0 22px;
    font-size: 14px;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.88);
}

.advisor-card__footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
}

.advisor-card__footer span {
    color: rgba(226, 255, 243, 0.72);
}

.advisor-card__footer strong {
    display: block;
    margin-top: 6px;
    font-size: 34px;
    line-height: 1;
    font-weight: 700;
}

.advisor-card__footer button {
    min-height: 44px;
    padding: 0 16px;
    border-radius: 6px;
    border: 0;
    background: #ffffff;
    font-size: 14px;
    font-weight: 600;
    color: #0d593d;
    cursor: pointer;
}

@media (max-width: 1180px) {
    .top-summary-grid,
    .feature-row,
    .profile-grid,
    .quality-panel__body {
        grid-template-columns: 1fr;
    }

    .summary-block {
        padding: 0 0 24px;
        border-right: 0;
        border-bottom: 1px solid #edf1f4;
        margin-bottom: 24px;
    }

    .summary-block:last-child {
        border-bottom: 0;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .summary-actions,
    .advisor-card__footer {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 820px) {
    .batch-profile-shell {
        padding: 14px 14px 34px;
    }

    .summary-actions__group,
    .documents-grid,
    .sensory-grid,
    .commercial-grid,
    .season-stats,
    .harvest-table__head,
    .harvest-table__row {
        grid-template-columns: 1fr;
    }

    .summary-actions__group {
        display: grid;
        width: 100%;
    }

    .action-button,
    .download-report,
    .season-link,
    .advisor-card__footer button {
        width: 100%;
        justify-content: center;
    }

    .harvest-table__head {
        display: none;
    }

    .harvest-table__row {
        padding: 16px 0;
        gap: 8px;
    }
}
</style>
