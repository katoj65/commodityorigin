<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import {
    Aim,
    Calendar,
    Checked,
    CircleCheckFilled,
    CollectionTag,
    Document,
    GoodsFilled,
    Histogram,
    Location,
    Opportunity,
    PriceTag,
    Reading,
    SetUp,
    Tickets,
    TrendCharts,
    User,
    WarningFilled,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AddHarvestDocumentModal from '@/Components/Modals/AddHarvestDocumentModal.vue';
import AddHarvestSustainabilityModal from '@/Components/Modals/AddHarvestSustainabilityModal.vue';
import EditHarvestQualityModel from '@/Components/Modals/EditHarvestQualityModel.vue';

const props = defineProps({
    harvest: {
        type: Object,
        required: true,
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

const page = usePage();
const qualityModalOpen = ref(false);
const documentModalOpen = ref(false);
const sustainabilityModalOpen = ref(false);
const flashSuccess = computed(() => page.props.flash?.success ?? '');

const harvestDocuments = computed(() => (
    Array.isArray(props.harvest.harvest_documents) ? props.harvest.harvest_documents : []
));

const farmName = computed(() => props.harvest.farm?.name || 'Mount Elgon Heights');
const farmerName = computed(() => {
    const fullName = [
        props.harvest.farm?.farmer?.first_name,
        props.harvest.farm?.farmer?.last_name,
    ].filter(Boolean).join(' ');

    return fullName || props.harvest.farmer_name || 'Abebe Bikila';
});
const varietyLabel = computed(() => props.harvest.variety || props.harvest.farm?.variety || 'Arabica');
const harvestDateLabel = computed(() => {
    if (!props.harvest.harvest_date) {
        return 'Oct 12, 2024';
    }

    return new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(props.harvest.harvest_date));
});
const plantedDateLabel = computed(() => {
    if (!props.harvest.date_planted) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(props.harvest.date_planted));
});
const harvestCode = computed(() => {
    const year = props.harvest.harvest_date
        ? new Date(props.harvest.harvest_date).getFullYear()
        : 2024;

    return `#H-${year}-${String(props.harvest.id).padStart(2, '0')}`;
});
const volumeKg = computed(() => Number(props.harvest.weight || 1200));
const moistureContent = computed(() => Number(props.harvest.moisture_content || 11.2));
const qualityScore = computed(() => Number(props.harvest.ripeness_percentage || 87.4));
const formatBooleanLabel = (value) => (value ? 'Yes' : 'No');
const currentPrice = computed(() => {
    if (props.harvest.price) {
        return Number(props.harvest.price);
    }

    if (qualityScore.value >= 90) {
        return 4.5;
    }

    if (qualityScore.value >= 86) {
        return 4.25;
    }

    return 3.8;
});
const estimatedMarketValue = computed(() => Math.round(volumeKg.value * currentPrice.value));
const pickMethodLabel = computed(() => props.harvest.pick_method || 'Selective');
const harvestSeasonLabel = computed(() => props.harvest.harvest_season || 'Main Season');
const qualityBreakdownRows = computed(() => [
    {
        label: 'Pick Method',
        value: pickMethodLabel.value,
        icon: SetUp,
        tone: 'feature',
    },
    {
        label: 'Ripeness Percentage',
        value: `${qualityScore.value.toFixed(1)}%`,
        icon: Aim,
        tone: 'feature',
    },
    {
        label: 'Foreign Matter Present',
        value: formatBooleanLabel(props.harvest.foreign_matter_present),
        icon: WarningFilled,
        tone: props.harvest.foreign_matter_present ? 'risk' : 'clear',
    },
    {
        label: 'Pest Damage',
        value: formatBooleanLabel(props.harvest.pest_damage),
        icon: WarningFilled,
        tone: props.harvest.pest_damage ? 'risk' : 'clear',
    },
    {
        label: 'Disease Signs',
        value: formatBooleanLabel(props.harvest.disease_signs),
        icon: WarningFilled,
        tone: props.harvest.disease_signs ? 'risk' : 'clear',
    },
    {
        label: 'Visible Defects',
        value: formatBooleanLabel(props.harvest.visible_defects),
        icon: WarningFilled,
        tone: props.harvest.visible_defects ? 'risk' : 'clear',
    },
]);
const processChecklist = computed(() => [
    'Cherry collection verified',
    'Moisture benchmark passed',
    'Defect sorting completed',
    'Farm traceability confirmed',
]);
const processDetails = computed(() => [
    { label: 'Date Planted', value: plantedDateLabel.value, icon: Calendar },
    { label: 'Harvest Date', value: harvestDateLabel.value, icon: Tickets },
    { label: 'Harvest Season', value: harvestSeasonLabel.value, icon: Reading },
    { label: 'Weight', value: `${volumeKg.value.toLocaleString()} kg`, icon: GoodsFilled },
    { label: 'Price', value: `$${currentPrice.value.toFixed(2)}`, icon: PriceTag },
]);
const qualityPotential = computed(() => [
    { label: 'Ripeness Score', value: `${Math.min(95, Math.round(qualityScore.value + 8))}/100` },
    { label: 'Cupping Range', value: qualityScore.value >= 86 ? '86-88' : '82-84' },
    { label: 'Defect Risk', value: 'Low', tone: 'positive' },
]);
const timelineItems = computed(() => [
    {
        date: 'Oct 14, 2024',
        title: 'Ready for Batching',
        note: 'Quality verified by Warehouse B',
        active: true,
    },
    {
        date: harvestDateLabel.value,
        title: 'Harvest Registration',
        note: 'Initial cherry count logged',
        active: false,
    },
]);
const attachedFarms = computed(() => {
    if (!props.harvest.farm) {
        return [];
    }

    const farm = props.harvest.farm;
    const responsible = [
        farm.farmer?.first_name,
        farm.farmer?.last_name,
    ].filter(Boolean).join(' ') || farmerName.value;

    return [
        {
            id: `#FARM-${String(farm.id || props.harvest.farm_id || props.harvest.id).padStart(3, '0')}`,
            name: farm.name || farmName.value,
            location: farm.location || 'Origin pending',
            variety: farm.variety || varietyLabel.value,
            farmer: responsible,
            status: String(farm.status || 'Attached'),
        },
    ];
});
const sustainabilityDetails = ref({
    organicCertified: props.harvest.sustainability?.organicCertified ?? true,
    climateSmart: props.harvest.sustainability?.climateSmart ?? true,
    shadeGrown: props.harvest.sustainability?.shadeGrown ?? true,
    waterManagement: props.harvest.sustainability?.waterManagement ?? true,
    soilConservation: props.harvest.sustainability?.soilConservation ?? true,
    lowCarbon: props.harvest.sustainability?.lowCarbon ?? true,
    fairWages: props.harvest.sustainability?.fairWages ?? true,
    notes: props.harvest.sustainability?.notes ?? '',
});
const sustainabilityPillars = computed(() => [
    { label: 'Shade Grown', icon: CollectionTag, active: sustainabilityDetails.value.shadeGrown },
    { label: 'Water Management', icon: GoodsFilled, active: sustainabilityDetails.value.waterManagement },
    { label: 'Soil Conservation', icon: Opportunity, active: sustainabilityDetails.value.soilConservation },
    { label: 'Low Carbon', icon: TrendCharts, active: sustainabilityDetails.value.lowCarbon },
    { label: 'Fair Wages', icon: User, active: sustainabilityDetails.value.fairWages },
]);
const documentItems = computed(() => {
    if (harvestDocuments.value.length) {
        return harvestDocuments.value.map((document) => ({
            title: document.title || document.original_name || 'Harvest document',
            fileName: document.original_name || 'File attached',
            href: document.file_url,
        }));
    }

    return [
        { title: 'Harvest photos (12)', fileName: 'Field image bundle', href: '#' },
        { title: 'Farmer Confirmation.pdf', fileName: 'Signed confirmation', href: '#' },
        { title: 'Field Quality Report', fileName: 'Inspection report', href: '#' },
    ];
});

let lastShownSuccess = '';

watch(
    flashSuccess,
    (message) => {
        if (!message || message === lastShownSuccess) {
            return;
        }

        lastShownSuccess = message;

        ElNotification({
            title: 'Harvest Updated',
            message,
            type: 'success',
            duration: 3200,
            offset: 84,
        });
    },
    { immediate: true },
);

const saveSustainabilityDetails = (payload) => {
    sustainabilityDetails.value = { ...payload };
};

watch(
    () => props.harvest.sustainability,
    (value) => {
        if (!value) {
            return;
        }

        sustainabilityDetails.value = {
            organicCertified: value.organicCertified ?? true,
            climateSmart: value.climateSmart ?? true,
            shadeGrown: value.shadeGrown ?? true,
            waterManagement: value.waterManagement ?? true,
            soilConservation: value.soilConservation ?? true,
            lowCarbon: value.lowCarbon ?? true,
            fairWages: value.fairWages ?? true,
            notes: value.notes ?? '',
        };
    },
    { deep: true },
);
</script>

<template>
    <AppLayout :title="harvestCode" full-width>
        <div class="harvest-profile-page">
            <div class="harvest-profile-shell">
                <section class="harvest-hero">
                    <div class="harvest-hero__meta">
                        <span class="harvest-chip is-green">Verified Farm</span>
                        <span class="harvest-chip is-peach">Fresh Harvest</span>
                        <span class="harvest-chip is-slate">Ready for Batching</span>
                        <span class="harvest-chip is-slate">Traceable</span>
                    </div>

                    <div class="harvest-hero__row">
                        <div class="harvest-hero__copy">
                            <h1>{{ harvestCode }}</h1>
                            <p>
                                Farmer: {{ farmerName }}
                                <span>•</span>
                                {{ varietyLabel }} SL-14
                            </p>
                        </div>

                        <div class="harvest-hero__actions">
                            <button type="button" class="harvest-btn harvest-btn--soft" @click="qualityModalOpen = true">Edit Harvest</button>
                            <button type="button" class="harvest-btn harvest-btn--green" @click="documentModalOpen = true">Upload Document</button>
                        </div>
                    </div>
                </section>

                <section class="harvest-layout">
                    <main class="harvest-main">
                        <section class="harvest-stats-grid">
                            <article class="harvest-stat-card">
                                <span>Total Quantity</span>
                                <strong>{{ volumeKg.toLocaleString() }}kg</strong>
                            </article>
                            <article class="harvest-stat-card">
                                <span>Harvest Date</span>
                                <strong>{{ harvestDateLabel }}</strong>
                            </article>
                            <article class="harvest-stat-card">
                                <span>Moisture Content</span>
                                <strong>{{ moistureContent.toFixed(1) }}%</strong>
                            </article>
                            <article class="harvest-stat-card">
                                <span>Picking Method</span>
                                <strong>{{ pickMethodLabel }}</strong>
                            </article>
                            <article class="harvest-stat-card">
                                <span>Bean Variety</span>
                                <strong>{{ varietyLabel }}</strong>
                            </article>
                            <article class="harvest-stat-card">
                                <span>Est. Market Value</span>
                                <strong>${{ estimatedMarketValue.toLocaleString() }}</strong>
                            </article>
                        </section>

                        <section class="harvest-card harvest-breakdown-card h-100">
                            <div class="harvest-section-title">
                                <el-icon><Histogram /></el-icon>
                                <span>Quantity Breakdown</span>
                            </div>

                            <div class="harvest-breakdown-layout">
                                <div class="harvest-breakdown-featured">
                                    <div
                                        v-for="item in qualityBreakdownRows.slice(0, 2)"
                                        :key="item.label"
                                        class="harvest-breakdown-highlight"
                                    >
                                        <div class="harvest-breakdown-highlight__icon">
                                            <el-icon><component :is="item.icon" /></el-icon>
                                        </div>
                                        <span>{{ item.label }}</span>
                                        <strong>{{ item.value }}</strong>
                                    </div>
                                </div>

                                <div class="harvest-breakdown-flags">
                                    <div
                                        v-for="item in qualityBreakdownRows.slice(2)"
                                        :key="item.label"
                                        class="harvest-breakdown-flag"
                                        :class="`is-${item.tone}`"
                                    >
                                        <div class="harvest-breakdown-flag__meta">
                                            <div class="harvest-breakdown-flag__icon" :class="`is-${item.tone}`">
                                                <el-icon>
                                                    <component :is="item.value === 'Yes' ? item.icon : CircleCheckFilled" />
                                                </el-icon>
                                            </div>
                                            <span>{{ item.label }}</span>
                                        </div>
                                        <strong>{{ item.value }}</strong>
                                    </div>
                                </div>
                            </div>
                        </section>


                    </main>

                    <aside class="harvest-sidebar">
                        <section class="harvest-card harvest-checklist-card h-100">


                            <div class="harvest-process-subsection border-0 mt-0 pt-0">
                                <div class="harvest-table-title">Harvest Details</div>
                                <div class="harvest-process-details">
                                    <div v-for="item in processDetails" :key="item.label" class="harvest-process-details__row">
                                        <span class="harvest-process-details__label">
                                            <el-icon><component :is="item.icon" /></el-icon>
                                            <span>{{ item.label }}</span>
                                        </span>
                                        <strong>{{ item.value }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="harvest-process-subsection">
                                <div class="harvest-table-title">Documents</div>
                                <div class="harvest-documents-card">
                                    <a
                                        v-for="item in documentItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="harvest-document-link"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <span class="harvest-document-link__icon">
                                            <el-icon><Document /></el-icon>
                                        </span>
                                        <div>
                                            <strong>{{ item.title }}</strong>
                                            <small>{{ item.fileName }}</small>
                                        </div>
                                        <span class="harvest-document-link__action">↓</span>
                                    </a>
                                </div>
                            </div>
                            <button type="button" class="harvest-btn harvest-btn--green harvest-btn--block">Move to Batch</button>
                        </section>

                    </aside>
                </section>

                <section class="harvest-card harvest-lots-card">
                    <div class="harvest-lots-card__head">
                        <h2 class="harvest-lots-card__title">
                            <el-icon><CollectionTag /></el-icon>
                            <span>Farms Attached to This Harvest</span>
                        </h2>
                        <div class="harvest-lots-card__chips">
                            <span class="harvest-badge is-bright">Attached</span>
                            <span class="harvest-badge is-muted">Traceable</span>
                        </div>
                    </div>

                    <el-table
                        :data="attachedFarms"
                        empty-text="No farm attached to this harvest"
                        class="harvest-farms-table mt-3"
                    >
                        <el-table-column label="Farm ID" min-width="140">
                            <template #default="{ row }">
                                <span class="harvest-table-cell">
                                    <el-icon><Tickets /></el-icon>
                                    <strong>{{ row.id }}</strong>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Name" min-width="180">
                            <template #default="{ row }">
                                <span class="harvest-table-cell">
                                    <el-icon><CollectionTag /></el-icon>
                                    <span>{{ row.name }}</span>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Location" min-width="180">
                            <template #default="{ row }">
                                <span class="harvest-table-cell">
                                    <el-icon><Location /></el-icon>
                                    <span>{{ row.location }}</span>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Variety" min-width="140">
                            <template #default="{ row }">
                                <span class="harvest-table-cell">
                                    <el-icon><GoodsFilled /></el-icon>
                                    <span>{{ row.variety }}</span>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Farmer" min-width="180">
                            <template #default="{ row }">
                                <span class="harvest-table-cell">
                                    <el-icon><User /></el-icon>
                                    <span>{{ row.farmer }}</span>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Status" min-width="120">
                            <template #default="{ row }">
                                <span class="harvest-status-pill active">{{ row.status }}</span>
                            </template>
                        </el-table-column>
                    </el-table>
                </section>

                <section class="harvest-card harvest-sustainability-card">
                    <div class="harvest-sustainability-card__head">
                        <h2>Sustainability Profile</h2>
                        <div class="harvest-sustainability-card__tags">
                            <span v-if="sustainabilityDetails.organicCertified"><el-icon><CollectionTag /></el-icon> Organic Certified</span>
                            <span v-if="sustainabilityDetails.climateSmart"><el-icon><GoodsFilled /></el-icon> Climate Smart</span>
                            <button type="button" class="harvest-btn harvest-btn--soft harvest-btn--sm" @click="sustainabilityModalOpen = true">
                                Add Sustainability Details
                            </button>
                        </div>
                    </div>

                    <div class="harvest-sustainability-grid">
                        <article
                            v-for="item in sustainabilityPillars"
                            :key="item.label"
                            class="harvest-sustainability-tile"
                            :class="{ 'is-inactive': !item.active }"
                        >
                            <el-icon><component :is="item.icon" /></el-icon>
                            <span>{{ item.label }}</span>
                        </article>
                    </div>
                </section>
            </div>
        </div>

        <EditHarvestQualityModel
            v-model="qualityModalOpen"
            :harvest="props.harvest"
            :pick-method-options="props.pickMethodOptions"
            :harvest-season-options="props.harvestSeasonOptions"
        />
        <AddHarvestDocumentModal
            v-model="documentModalOpen"
            :harvest="props.harvest"
            :document-type-options="props.documentTypeOptions"
        />
        <AddHarvestSustainabilityModal
            v-model="sustainabilityModalOpen"
            :harvest="props.harvest"
            :sustainability="sustainabilityDetails"
            @save="saveSustainabilityDetails"
        />
    </AppLayout>
</template>

<style scoped>
.harvest-profile-page {
    background: #fff;
    min-height: 100vh;
    padding: 0.75rem 1rem 1.5rem;
}

.harvest-profile-shell {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
    margin: 0 auto;
    max-width: 1440px;
}

.harvest-hero__meta,
.harvest-hero__row,
.harvest-section-title,
.harvest-stat-card span,
.harvest-table-title,
.harvest-mini-table__head,
.harvest-lots-table__head {
    letter-spacing: 0.08em;
}

.harvest-chip,
.harvest-badge,
.harvest-status-pill,
.harvest-score-chip,
.harvest-btn {
    border-radius: 999px;
}

.harvest-hero {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.harvest-hero__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    text-transform: uppercase;
}

.harvest-chip {
    display: inline-flex;
    align-items: center;
    font-size: 0.66rem;
    font-weight: 800;
    padding: 0.38rem 0.62rem;
}

.harvest-chip.is-green {
    background: #c9f3df;
    color: #0b5a3e;
}

.harvest-chip.is-peach {
    background: #ffe3cf;
    color: #8a5218;
}

.harvest-chip.is-slate {
    background: #edf1f6;
    color: #5f6977;
}

.harvest-hero__row {
    align-items: center;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
}

.harvest-hero__copy h1 {
    color: #0f412d;
    font-size: 2.1rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    margin: 0;
}

.harvest-hero__copy p {
    color: #43515c;
    font-size: 1rem;
    margin: 0.45rem 0 0;
}

.harvest-hero__copy p span {
    color: #97a2ad;
    margin: 0 0.4rem;
}

.harvest-hero__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: flex-end;
}

.harvest-btn {
    border: 1px solid #d6dde6;
    font-size: 0.94rem;
    font-weight: 700;
    padding: 0.8rem 1.2rem;
}

.harvest-btn--soft {
    background: #fff;
    color: #1c2a39;
}

.harvest-btn--peach {
    background: #ffe2c7;
    border-color: #ffd0aa;
    color: #5d3812;
}

.harvest-btn--green {
    background: #055336;
    border-color: #055336;
    color: #fff;
}

.harvest-btn--block {
    border-radius: 0.85rem;
    margin-top: 1rem;
    width: 100%;
}

.harvest-btn--sm {
    font-size: 0.8rem;
    padding: 0.55rem 0.9rem;
}

.harvest-layout {
    display: grid;
    gap: 1.4rem;
    grid-template-columns: minmax(0, 1fr) 320px;
    align-items: stretch;
}

.harvest-main,
.harvest-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.harvest-sidebar > .h-100 {
    flex: 1 1 auto;
}

.harvest-info-row {
    display: grid;
    gap: 1rem;
    grid-template-columns: minmax(0, 1fr);
}

.harvest-card,
.harvest-stat-card {
    background: #fff;
    border: 1px solid #e8edf3;
    border-radius: 1.1rem;
    box-shadow: 0 10px 28px rgba(20, 32, 56, 0.04);
}

.harvest-stats-grid {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.harvest-stat-card {
    padding: 1.1rem 1.25rem;
}

.harvest-stat-card span {
    color: #70808e;
    display: block;
    font-size: 0.68rem;
    font-weight: 700;
    margin-bottom: 0.8rem;
    text-transform: uppercase;
}

.harvest-stat-card strong {
    color: #0f412d;
    font-size: 1.05rem;
    font-weight: 800;
}

.harvest-breakdown-card,
.harvest-lots-card,
.harvest-sustainability-card,
.harvest-card {
    padding: 1.2rem 1.25rem;
}

.harvest-section-title {
    align-items: center;
    color: #122232;
    display: inline-flex;
    font-size: 0.78rem;
    font-weight: 800;
    gap: 0.55rem;
    text-transform: uppercase;
}

.harvest-section-title.is-light {
    color: #fff;
}

.harvest-breakdown-layout {
    display: flex;
    flex-direction: column;
    gap: 0.95rem;
    margin-top: 1.25rem;
}

.harvest-breakdown-featured {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.harvest-breakdown-highlight,
.harvest-breakdown-flag {
    border-radius: 0.95rem;
    padding: 0.8rem 0.9rem;
}

.harvest-breakdown-highlight {
    background: linear-gradient(180deg, #fbfcfe 0%, #f4f7fb 100%);
    border: 1px solid #e5ebf2;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    min-height: 5.1rem;
}

.harvest-breakdown-highlight__icon {
    align-items: center;
    background: #e9f5ef;
    border-radius: 0.85rem;
    color: #0f6a47;
    display: inline-flex;
    height: 1.8rem;
    justify-content: center;
    width: 1.8rem;
}

.harvest-breakdown-flags {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.harvest-breakdown-flag {
    background: #f8fafc;
    border: 1px solid #edf1f5;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-height: 4.35rem;
}

.harvest-breakdown-flag__meta {
    align-items: center;
    display: flex;
    gap: 0.55rem;
}

.harvest-breakdown-flag__icon {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    flex-shrink: 0;
    height: 1.55rem;
    justify-content: center;
    width: 1.55rem;
}

.harvest-breakdown-flag__icon.is-clear {
    background: #e8f6ee;
    color: #0d6b47;
}

.harvest-breakdown-flag__icon.is-risk {
    background: #fff0ef;
    color: #cf3b2d;
}

.harvest-breakdown-highlight span,
.harvest-breakdown-flag span {
    color: #758494;
    display: block;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    margin-bottom: 0.45rem;
    text-transform: uppercase;
}

.harvest-breakdown-highlight strong,
.harvest-breakdown-flag strong {
    color: #162333;
    display: block;
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.4;
}

.harvest-breakdown-highlight strong {
    color: #0f412d;
    font-size: 1rem;
}

.harvest-breakdown-flag strong {
    font-size: 0.85rem;
    padding-left: 2.1rem;
}

.harvest-breakdown-flag.is-clear strong {
    color: #0f6a47;
}

.harvest-breakdown-flag.is-risk strong {
    color: #b53a2e;
}

.harvest-table-title {
    color: #161f2e;
    font-size: 1rem;
    font-weight: 700;
}

.harvest-lots-table__head,
.harvest-lots-table__row {
    align-items: center;
    display: grid;
    gap: 0.9rem;
}

.harvest-status-pill {
    display: inline-flex;
    font-size: 0.65rem;
    font-weight: 800;
    justify-content: center;
    padding: 0.35rem 0.6rem;
    text-transform: uppercase;
}

.harvest-status-pill.active {
    background: #dff7e7;
    color: #0c6a45;
}

.harvest-status-pill.queued {
    background: #eef2f6;
    color: #7a8794;
}

.harvest-farm-card {
    overflow: hidden;
    padding: 0;
}

.harvest-farm-card__image {
    align-items: end;
    background:
        linear-gradient(180deg, rgba(4, 34, 22, 0.12) 0%, rgba(4, 34, 22, 0.72) 100%),
        radial-gradient(circle at 20% 30%, rgba(112, 163, 92, 0.95), rgba(12, 64, 39, 0.95) 52%, rgba(7, 34, 20, 1) 100%);
    display: flex;
    height: 7.2rem;
    padding: 1rem;
}

.harvest-farm-card__image-copy strong {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 700;
}

.harvest-farm-card__identity,
.harvest-farm-card__meta {
    display: flex;
    gap: 0.85rem;
    padding: 1rem 1.1rem 0;
}

.harvest-avatar {
    align-items: center;
    background: linear-gradient(135deg, #ffd9a9, #9fe0c1);
    border-radius: 999px;
    color: #0b3a28;
    display: flex;
    font-weight: 800;
    height: 2.65rem;
    justify-content: center;
    width: 2.65rem;
}

.harvest-farm-card__identity strong,
.harvest-farm-card__meta strong {
    color: #19283a;
    display: block;
}

.harvest-farm-card__identity span,
.harvest-farm-card__meta span {
    color: #6c7a88;
    display: block;
    font-size: 0.84rem;
}

.harvest-farm-card__meta {
    justify-content: space-between;
    padding-bottom: 1rem;
}

.harvest-checklist-card,
.harvest-quality-card,
.harvest-timeline-card,
.harvest-documents-card {
    padding: 1rem;
}

.harvest-checklist {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin-top: 1rem;
}

.harvest-process-subsection {
    border-top: 1px solid #edf1f5;
    margin-top: 1rem;
    padding-top: 1rem;
}

.harvest-process-details {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
    margin-top: 0.85rem;
}

.harvest-process-details__row {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

.harvest-process-details__label {
    align-items: center;
    display: inline-flex;
    gap: 0.45rem;
}

.harvest-process-details__label :deep(svg) {
    color: #69806f;
    font-size: 0.95rem;
}

.harvest-process-details__row span {
    color: #71808f;
    font-size: 0.85rem;
}

.harvest-process-details__row strong {
    color: #162333;
    font-size: 0.88rem;
    font-weight: 700;
    text-align: right;
}

.harvest-checklist__row {
    align-items: center;
    color: #304051;
    display: flex;
    font-size: 0.91rem;
    gap: 0.65rem;
}

.harvest-checklist__row :deep(svg) {
    color: #0a6a46;
}

.harvest-quality-list,
.harvest-timeline {
    display: flex;
    flex-direction: column;
    gap: 0.95rem;
    margin-top: 1rem;
}

.harvest-quality-list__row {
    align-items: center;
    display: flex;
    justify-content: space-between;
    color: #4d5c6b;
    font-size: 0.92rem;
}

.harvest-quality-list__row strong {
    color: #0f1e2e;
}

.harvest-quality-list__row strong.is-positive {
    color: #0b6a47;
}

.harvest-badge {
    background: #cbf2dc;
    color: #0a5d3f;
    display: inline-flex;
    font-size: 0.65rem;
    font-weight: 800;
    margin-top: 1rem;
    padding: 0.36rem 0.62rem;
    text-transform: uppercase;
}

.harvest-badge.is-bright {
    background: #aff0c9;
}

.harvest-badge.is-muted {
    background: #eef2f6;
    color: #5f6c78;
}

.harvest-timeline__row {
    display: grid;
    gap: 0.85rem;
    grid-template-columns: 0.7rem minmax(0, 1fr);
}

.harvest-timeline__dot {
    background: #d7dfe7;
    border-radius: 999px;
    height: 0.75rem;
    margin-top: 0.25rem;
    position: relative;
    width: 0.75rem;
}

.harvest-timeline__dot::after {
    background: #dde4ec;
    content: '';
    height: 2rem;
    left: 50%;
    position: absolute;
    top: 0.8rem;
    transform: translateX(-50%);
    width: 1px;
}

.harvest-timeline__row:last-child .harvest-timeline__dot::after {
    display: none;
}

.harvest-timeline__dot.is-active {
    background: #04573a;
}

.harvest-timeline__row strong,
.harvest-document-link strong {
    color: #162333;
    display: block;
}

.harvest-timeline__row span,
.harvest-document-link small {
    color: #4d5a69;
    display: block;
    font-size: 0.9rem;
    margin-top: 0.2rem;
}

.harvest-timeline__row small {
    color: #8a95a1;
    display: block;
    font-size: 0.78rem;
    margin-top: 0.18rem;
}

.harvest-documents-card {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.harvest-document-link {
    align-items: center;
    background: #f5f7fb;
    border: 1px solid #eef2f6;
    border-radius: 0.85rem;
    color: inherit;
    display: grid;
    gap: 0.75rem;
    grid-template-columns: 2.15rem minmax(0, 1fr) auto;
    padding: 0.85rem;
    text-decoration: none;
}

.harvest-document-link__icon {
    align-items: center;
    background: #fff;
    border-radius: 0.65rem;
    color: #667685;
    display: flex;
    height: 2.15rem;
    justify-content: center;
    width: 2.15rem;
}

.harvest-document-link__action {
    color: #384656;
    font-size: 1rem;
    font-weight: 700;
}

.harvest-lots-card__head,
.harvest-sustainability-card__head {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

.harvest-lots-card__head h2,
.harvest-sustainability-card__head h2 {
    color: #151f2f;
    font-size: 1.15rem;
    margin: 0;
}

.harvest-lots-card__title {
    align-items: center;
    display: inline-flex;
    gap: 0.55rem;
}

.harvest-lots-card__title :deep(svg) {
    color: #0a5f40;
}

.harvest-lots-card__chips,
.harvest-sustainability-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.harvest-sustainability-card__tags span {
    align-items: center;
    color: #103323;
    display: inline-flex;
    font-size: 0.9rem;
    gap: 0.35rem;
}

.harvest-lots-table {
    margin-top: 1rem;
}

.harvest-farms-table :deep(.el-table__header th) {
    background: #f8fafc;
    color: #7f8d9b;
    font-size: 0.67rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.harvest-farms-table :deep(.el-table__cell) {
    padding: 0.9rem 0;
}

.harvest-farms-table :deep(.el-table__row td) {
    background: #fff;
}

.harvest-farms-table :deep(.el-table__inner-wrapper::before) {
    display: none;
}

.harvest-table-cell {
    align-items: center;
    color: #233242;
    display: inline-flex;
    gap: 0.5rem;
}

.harvest-table-cell :deep(svg) {
    color: #69806f;
    font-size: 0.95rem;
}

.harvest-lots-table__head {
    border-bottom: 1px solid #edf1f5;
    color: #7f8d9b;
    font-size: 0.67rem;
    font-weight: 700;
    grid-template-columns: 1.1fr 1.2fr 0.9fr 1.25fr 1.1fr 0.9fr;
    padding-bottom: 0.8rem;
    text-transform: uppercase;
}

.harvest-lots-table__row {
    color: #233242;
    font-size: 0.92rem;
    grid-template-columns: 1.1fr 1.2fr 0.9fr 1.25fr 1.1fr 0.9fr;
    padding: 1rem 0;
}

.harvest-lots-table__row + .harvest-lots-table__row {
    border-top: 1px solid #f0f3f7;
}

.harvest-score-chip {
    align-items: center;
    background: #dff7e7;
    border-radius: 0.45rem;
    color: #0a6644;
    display: inline-flex;
    font-weight: 700;
    justify-content: center;
    padding: 0.35rem 0.55rem;
    width: fit-content;
}

.harvest-table-action {
    background: transparent;
    border: 0;
    color: #12472f;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0;
    text-transform: uppercase;
}

.harvest-sustainability-grid {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-top: 1.2rem;
}

.harvest-sustainability-tile {
    align-items: center;
    background: #f7f9fc;
    border: 1px solid #edf1f5;
    border-radius: 0.95rem;
    color: #263648;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    justify-content: center;
    min-height: 6rem;
    padding: 1rem;
    text-align: center;
}

.harvest-sustainability-tile.is-inactive {
    opacity: 0.45;
}

.harvest-sustainability-tile :deep(svg) {
    color: #0a5f40;
    font-size: 1.1rem;
}

@media (max-width: 1200px) {
    .harvest-layout {
        grid-template-columns: 1fr;
    }

    .harvest-sidebar {
        order: -1;
    }
}

@media (max-width: 900px) {
    .harvest-profile-page {
        padding: 0.6rem 0.75rem 1rem;
    }

    .harvest-hero__row,
    .harvest-lots-card__head,
    .harvest-sustainability-card__head {
        align-items: start;
        flex-direction: column;
    }

    .harvest-stats-grid,
    .harvest-breakdown-featured,
    .harvest-breakdown-flags,
    .harvest-sustainability-grid {
        grid-template-columns: 1fr;
    }

    .harvest-lots-table__head,
    .harvest-lots-table__row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
