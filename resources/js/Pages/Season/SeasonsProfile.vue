<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElMessageBox, ElNotification } from 'element-plus';
import {
    ArrowDown,
    Calendar,
    Checked,
    CircleCheckFilled,
    CollectionTag,
    Delete,
    Document,
    EditPen,
    Files,
    Histogram,
    Location,
    MostlyCloudy,
    Opportunity,
    Plus,
    Tickets,
    TrendCharts,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import EditSeasonModal from '@/Components/Modals/EditSeasonModal.vue';

const props = defineProps({
    season: {
        type: Object,
        required: true,
    },
    harvests: {
        type: Array,
        default: () => [],
    },
    regionOptions: {
        type: Array,
        default: () => [],
    },
    farmOptions: {
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
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const editModalOpen = ref(false);
const seasonName = computed(() => props.season.name || 'Main Crop 2026');
const seasonCode = computed(() => {
    const year = props.season.start_date ? new Date(props.season.start_date).getFullYear() : 2026;

    return `#SEA-UGA-${year}-${String(props.season.id).padStart(2, '0')}`;
});
const seasonTimeline = computed(() => {
    const start = props.season.start_date ? new Date(props.season.start_date) : new Date('2026-01-15');
    const end = props.season.end_date ? new Date(props.season.end_date) : new Date('2026-06-20');

    const format = (date) => new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        day: 'numeric',
    }).format(date);

    return `${format(start)} - ${format(end)}`;
});

const totalHarvests = computed(() => props.season.harvests_count ?? props.harvests.length);
const totalQuantity = computed(() => props.season.harvests_sum_weight ?? 0);
const totalHarvestValue = computed(() => props.season.harvests_sum_price ?? 0);

const summaryCards = computed(() => [
    { label: 'Timeline', value: seasonTimeline.value, note: '' },
    { label: 'Total Harvests', value: String(totalHarvests.value).padStart(2, '0'), note: '' },
    { label: 'Total Quantity', value: `${Number(totalQuantity.value).toLocaleString()} kg`, note: '' },
    { label: 'Total Value', value: `Shs. ${Number(totalHarvestValue.value).toLocaleString()}`, note: '' },
    { label: 'Health Score', value: '92 /100', note: '', tone: 'dark' },
]);

const insightCards = [
    { icon: Files, text: '3 harvests ready for batching' },
    { icon: MostlyCloudy, text: 'Moisture levels optimal (11.2%)' },
    { icon: TrendCharts, text: 'Yield above last season trend' },
];

const seasonHarvests = computed(() => props.harvests.map((harvest) => ({
    rawId: harvest.id,
    id: `#HAR-${String(harvest.id).padStart(3, '0')}`,
    farm: harvest.farm?.name || 'Unassigned farm',
    lot: harvest.farm?.location || harvest.variety || '—',
    quantity: harvest.weight ? `${Number(harvest.weight).toLocaleString()}kg` : '—',
    moisture: harvest.ripeness_percentage !== null ? `${Number(harvest.ripeness_percentage).toFixed(1)}%` : '—',
    score: harvest.price ? Number(harvest.price).toLocaleString() : '—',
    status: (harvest.status || 'pending').toUpperCase(),
    showUrl: route('harvest.show', harvest.id),
})));

const recentActivities = [
    { date: 'Mar 12, 2026', activity: 'Organic Weeding', note: 'Maintenance', farm: 'Moses W. / Lot A', status: 'Completed', evidence: 'View Photo' },
    { date: 'Mar 10, 2026', activity: 'Selective Pruning', note: 'Canopy Mgmt', farm: 'Mount Elgon Coop', status: 'Completed', evidence: 'View Photo' },
];

const qualityDocs = [
    { icon: Document, title: 'Field Reports', subtitle: 'Soil Analysis Q1...' },
    { icon: CollectionTag, title: 'Harvest Photos', subtitle: '12 New Files' },
    { icon: EditPen, title: 'Quality Notes', subtitle: 'SL-14 Cupping V1' },
];

const climateCards = [
    { label: 'Rainfall', value: 'Moderate', badge: 'MED', tone: 'amber' },
    { label: 'Drought', value: 'Stable', badge: 'LOW', tone: 'mint' },
    { label: 'Pest Risk', value: 'High Alert', badge: 'HIGH', tone: 'rose' },
    { label: 'Disease', value: 'Controlled', badge: 'LOW', tone: 'mint' },
];

const handleOptionsCommand = (command) => {
    if (command === 'edit') {
        editModalOpen.value = true;
        return;
    }

    if (command === 'add-harvest') {
        router.get(route('season.create-harvest', props.season.id));
        return;
    }

    if (command === 'delete') {
        ElMessageBox.confirm(
            'This will permanently delete the current season. Continue?',
            'Delete season',
            {
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                type: 'warning',
            },
        ).then(() => {
            router.delete(route('season.destroy', props.season.id), {
                preserveScroll: true,
            });
        }).catch(() => {});
    }
};

const notifySeasonUpdateSuccess = (message) => {
    ElNotification({
        title: 'Season Saved',
        message,
        type: 'success',
        duration: 3200,
        offset: 84,
    });
};

const notifyHarvestDeleteSuccess = (message) => {
    ElNotification({
        title: 'Harvest Deleted',
        message,
        type: 'success',
        duration: 3200,
        offset: 84,
    });
};

const openHarvestProfile = (row) => {
    if (!row?.showUrl) {
        return;
    }

    router.get(row.showUrl);
};

const deleteHarvest = (row) => {
    ElMessageBox.confirm(
        `This will permanently delete ${row.id}. Continue?`,
        'Delete harvest',
        {
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            type: 'warning',
        },
    ).then(() => {
        router.delete(route('season.harvest.destroy', [props.season.id, row.rawId]), {
            preserveScroll: true,
            onSuccess: (page) => {
                notifyHarvestDeleteSuccess(page.props.flash?.success || `${row.id} deleted successfully.`);
            },
        });
    }).catch(() => {});
};





</script>

<template>
    <AppLayout :title="seasonName" full-width>
        <div class="season-profile-page">
            <div class="season-profile-shell">
                <div class="season-content">
                    <section class="season-hero">
                        <div class="season-hero__crumbs">Seasons <span>›</span> {{ seasonName }}</div>
                        <div class="season-hero__head">
                            <div>
                                <h1>{{ seasonName }}</h1>
                                <p>
                                    Season ID: <strong>{{ seasonCode }}</strong> | {{ season.region }} | {{ season.start_date }} / {{ season.end_date }}
                                </p>
                            </div>

                            <div class="season-hero__actions">
                                <button
                                    type="button"
                                    class="season-btn is-primary"
                                    @click="router.get(route('batch.create-season', season.id))"
                                >
                                    <span>Create Batch</span>
                                </button>

                                <el-dropdown trigger="click" @command="handleOptionsCommand">
                                    <button type="button" class="season-btn is-soft">
                                        <!-- <el-icon><Histogram /></el-icon> -->
                                        <span>Options</span>
                                        <el-icon class="season-btn__caret"><ArrowDown /></el-icon>
                                    </button>
                                    <template #dropdown>
                                        <el-dropdown-menu>
                                            <el-dropdown-item command="edit">
                                                <el-icon><EditPen /></el-icon>
                                                <span>Edit</span>
                                            </el-dropdown-item>
                                            <el-dropdown-item command="add-harvest">
                                                <el-icon><Plus /></el-icon>
                                                <span>Add harvest</span>
                                            </el-dropdown-item>
                                            <el-dropdown-item command="delete" class="season-dropdown__danger">
                                                <el-icon><Delete /></el-icon>
                                                <span>Delete</span>
                                            </el-dropdown-item>
                                        </el-dropdown-menu>
                                    </template>
                                </el-dropdown>
                            </div>
                        </div>

                        <div class="season-pills">
                            <span class="season-pill is-green"><span class="dot"></span>Active Season</span>
                            <span class="season-pill">Traceable</span>
                            <span class="season-pill">Harvesting Phase</span>
                            <span class="season-pill">Export Potential</span>
                        </div>
                    </section>

                    <section class="season-summary-grid">
                        <article
                            v-for="card in summaryCards"
                            :key="card.label"
                            class="season-summary-card"
                            :class="{ 'is-dark': card.tone === 'dark' }"
                        >
                            <span>{{ card.label }}</span>
                            <strong>{{ card.value }}</strong>
                            <small v-if="card.note">{{ card.note }}</small>
                            <el-icon v-if="card.tone === 'dark'" class="season-summary-card__badge"><Checked /></el-icon>
                        </article>
                    </section>

                    <section class="season-grid">
                        <div class="season-left-column">
                            <article class="season-card season-insights">
                                <div class="season-card__title"><el-icon><Opportunity /></el-icon><span>AI Season Insights</span></div>
                                <div class="season-insights__list">
                                    <div v-for="item in insightCards" :key="item.text" class="season-insight-item">
                                        <span class="season-insight-item__icon"><el-icon><component :is="item.icon" /></el-icon></span>
                                        <span>{{ item.text }}</span>
                                    </div>
                                </div>
                            </article>

                            <article class="season-card season-quality">
                                <div class="season-card__title"><span>Quality Outlook</span></div>
                                <div class="season-quality__metrics">
                                    <div>
                                        <span>Avg Moisture</span>
                                        <strong>11.4%</strong>
                                    </div>
                                    <div>
                                        <span>Cupping Potential</span>
                                        <strong>86.5-88</strong>
                                    </div>
                                </div>
                                <div class="season-quality__risk">
                                    <span class="season-quality__risk-tag">LOW</span>
                                    <span>Stable drying conditions observed</span>
                                </div>
                                <div class="season-quality__pills">
                                    <span class="season-quality__pill is-peach">Premium Potential</span>
                                    <span class="season-quality__pill is-mint">Export Ready Soon</span>
                                </div>
                            </article>

                            <article class="season-card season-climate">
                                <div class="season-card__title"><span>Climate Risk</span></div>
                                <div class="season-climate__grid">
                                    <div v-for="item in climateCards" :key="item.label" class="season-climate__item">
                                        <span>{{ item.label }}</span>
                                        <strong>{{ item.value }}</strong>
                                        <em :class="`is-${item.tone}`">{{ item.badge }}</em>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div class="season-right-column">
                            <article class="season-card season-table-card">
                                <div class="season-card__title">

                                    <span>Harvests Attached to This Season</span>
                                </div>
                                <el-table
                                    :data="seasonHarvests"
                                    class="season-data-table season-harvests-table"
                                    empty-text="No harvests are attached to this season yet"
                                    @row-click="openHarvestProfile"
                                >
                                    <el-table-column label="ID" min-width="120">
                                        <template #default="{ row }">
                                            <span class="season-table-info">
                                                <el-icon><Tickets /></el-icon>
                                                <strong>{{ row.id }}</strong>
                                            </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Farm / Lot" min-width="210">
                                        <template #default="{ row }">
                                            <div class="season-table-info season-table-info--stack">
                                                <span class="season-table-info__line">
                                                    <el-icon><CollectionTag /></el-icon>
                                                    <strong>{{ row.farm }}</strong>
                                                </span>
                                                <span class="season-table-info__line is-subtle">
                                                    <el-icon><Location /></el-icon>
                                                    <small>{{ row.lot }}</small>
                                                </span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Quantity" min-width="130">
                                        <template #default="{ row }">
                                            <span class="season-table-info">
                                                <el-icon><Files /></el-icon>
                                                <span>{{ row.quantity }}</span>
                                            </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Moisture" min-width="110">
                                        <template #default="{ row }">
                                            <span class="season-table-info">
                                                <el-icon><MostlyCloudy /></el-icon>
                                                <span>{{ row.moisture }}</span>
                                            </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Price" min-width="90">
                                        <template #default="{ row }">
                                            <span class="season-table-info">
                                                <el-icon><TrendCharts /></el-icon>
                                                <span>{{ row.score }}</span>
                                            </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Action" min-width="110" align="right">
                                        <template #default="{ row }">
                                            <button
                                                type="button"
                                                class="season-delete-harvest-btn"
                                                @click.stop="deleteHarvest(row)"
                                                aria-label="Delete harvest"
                                                title="Delete harvest"
                                            >
                                                <el-icon><Delete /></el-icon>
                                            </button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </article>

                            <div class="season-docs-row">
                                <article v-for="item in qualityDocs" :key="item.title" class="season-doc-card">
                                    <span class="season-doc-card__icon"><el-icon><component :is="item.icon" /></el-icon></span>
                                    <div>
                                        <strong>{{ item.title }}</strong>
                                        <small>{{ item.subtitle }}</small>
                                    </div>
                                </article>
                            </div>

                            <article class="season-card season-table-card">
                                <div class="season-card__title">
                                  
                                    <span>Recent Activities</span>
                                </div>
                                <el-table :data="recentActivities" class="season-data-table" empty-text="No activity yet">
                                    <el-table-column label="Date" min-width="140">
                                        <template #default="{ row }">
                                            <span class="season-table-info">
                                                <el-icon><Calendar /></el-icon>
                                                <span>{{ row.date }}</span>
                                            </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Activity" min-width="180">
                                        <template #default="{ row }">
                                            <div class="season-table-info season-table-info--stack">
                                                <span class="season-table-info__line">
                                                    <el-icon><EditPen /></el-icon>
                                                    <strong>{{ row.activity }}</strong>
                                                </span>
                                                <span class="season-table-info__line is-subtle">
                                                    <el-icon><Opportunity /></el-icon>
                                                    <small>{{ row.note }}</small>
                                                </span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Farmer / Lot" min-width="180">
                                        <template #default="{ row }">
                                            <span class="season-table-info">
                                                <el-icon><CollectionTag /></el-icon>
                                                <span>{{ row.farm }}</span>
                                            </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Status" min-width="130">
                                        <template #default="{ row }">
                                            <span class="season-completed-status"><el-icon><CircleCheckFilled /></el-icon>{{ row.status }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Evidence" min-width="130">
                                        <template #default="{ row }">
                                            <span class="season-table-info">
                                                <el-icon><Document /></el-icon>
                                                <span>{{ row.evidence }}</span>
                                            </span>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </article>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <EditSeasonModal
            v-model="editModalOpen"
            :season="season"
            :region-options="regionOptions"
            :status-options="statusOptions"
            @success="notifySeasonUpdateSuccess"
        />
    </AppLayout>
</template>

<style scoped>
.season-profile-page {
    background: #ffffff;
    min-height: calc(100vh - 3.5rem);
}

.season-profile-shell {
    margin: 0 auto;
    max-width: 1180px;
    min-height: calc(100vh - 3.5rem);
}

.season-content {
    padding: 28px 12px 36px;
}

.season-hero__crumbs {
    color: #576472;
    font-size: 14px;
}

.season-hero__crumbs span {
    margin: 0 8px;
}

.season-hero__head {
    align-items: flex-start;
    display: flex;
    gap: 24px;
    justify-content: space-between;
    margin-top: 12px;
}

.season-hero h1 {
    color: #171c20;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -0.04em;
    margin: 0;
}

.season-hero p {
    color: #263238;
    font-size: 18px;
    line-height: 1.45;
    margin: 10px 0 0;
    max-width: 760px;
}

.season-hero__actions {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, auto);
    justify-items: end;
}

.season-btn {
    align-items: center;
    border-radius: 4px;
    display: inline-flex;
    font-size: 15px;
    font-weight: 600;
    gap: 8px;
    justify-content: center;
    min-height: 36px;
    min-width: 96px;
    padding: 0 16px;
}

.season-btn__caret {
    font-size: 12px;
    margin-left: 2px;
}

:deep(.season-dropdown__danger) {
    color: #dc2626;
}

.season-btn.is-soft {
    background: #f3f5f7;
    border: 1px solid #edf1f4;
    color: #1b2328;
}

.season-btn.is-primary {
    background: #0b5d46;
    border: 1px solid #0b5d46;
    color: #fff;
}

.season-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 16px;
}

.season-pill {
    align-items: center;
    background: #eff5f2;
    border-radius: 999px;
    color: #24413a;
    display: inline-flex;
    font-size: 14px;
    font-weight: 600;
    gap: 7px;
    min-height: 32px;
    padding: 0 14px;
}

.season-pill .dot {
    background: #0b5d46;
    border-radius: 50%;
    display: inline-block;
    height: 7px;
    width: 7px;
}

.season-summary-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-top: 28px;
}

.season-summary-card,
.season-card,
.season-doc-card {
    background: #fff;
    border: 1px solid #edf1f4;
    border-radius: 14px;
}

.season-summary-card {
    min-height: 104px;
    padding: 20px 18px;
    position: relative;
}

.season-summary-card span,
.season-climate__item span,
.season-quality__metrics span {
    color: #2e3943;
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: none;
}

.season-summary-card strong {
    color: #151c20;
    display: block;
    font-size: 18px;
    font-weight: 800;
    margin-top: 14px;
}

.season-summary-card small {
    color: #0b5d46;
    display: block;
    font-size: 12px;
    font-weight: 700;
    margin-top: 8px;
}

.season-summary-card.is-dark {
    background: #0b5d46;
    border-color: #0b5d46;
}

.season-summary-card.is-dark span,
.season-summary-card.is-dark strong,
.season-summary-card.is-dark small {
    color: #fff;
}

.season-summary-card__badge {
    bottom: 18px;
    color: #fff;
    font-size: 20px;
    position: absolute;
    right: 18px;
}

.season-grid {
    display: grid;
    gap: 22px;
    grid-template-columns: 290px minmax(0, 1fr);
    margin-top: 30px;
}

.season-left-column,
.season-right-column {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.season-card {
    padding: 24px;
}

.season-card__title,
.season-card__bar {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.season-card__title {
    color: #171d21;
    font-size: 14px;
    font-weight: 800;
    gap: 10px;
    letter-spacing: 0.04em;
    text-transform: none;
}

.season-card__title :deep(.el-icon) {
    color: #0b5d46;
    font-size: 18px;
}

.season-insights__list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 20px;
}

.season-insight-item {
    align-items: center;
    background: #fff;
    border: 1px solid #eef2f5;
    border-radius: 8px;
    display: flex;
    gap: 12px;
    min-height: 56px;
    padding: 0 12px;
}

.season-insight-item__icon {
    align-items: center;
    background: #aaf1cf;
    border-radius: 4px;
    color: #0b5d46;
    display: inline-flex;
    font-size: 18px;
    height: 30px;
    justify-content: center;
    width: 30px;
}

.season-quality__metrics {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 20px;
}

.season-quality__metrics strong {
    color: #0e3d30;
    display: block;
    font-size: 18px;
    margin-top: 10px;
}

.season-quality__risk {
    align-items: center;
    border-top: 1px solid #edf1f4;
    display: flex;
    gap: 10px;
    margin-top: 18px;
    padding-top: 18px;
}

.season-quality__risk-tag,
.season-quality__pill,
.season-status-pill,
.season-climate__item em {
    align-items: center;
    border-radius: 4px;
    display: inline-flex;
    font-size: 12px;
    font-style: normal;
    font-weight: 700;
    justify-content: center;
    min-height: 22px;
    padding: 0 10px;
}

.season-quality__risk-tag,
.season-quality__pill.is-mint,
.season-climate__item em.is-mint,
.season-status-pill {
    background: #cbf5de;
    color: #0b5d46;
}

.season-quality__pills {
    display: flex;
    gap: 10px;
    margin-top: 16px;
}

.season-quality__pill.is-peach,
.season-climate__item em.is-amber {
    background: #ffd7b4;
    color: #995f21;
}

.season-climate__item em.is-rose {
    background: #ffd1d9;
    color: #d14b5f;
}

.season-climate__grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 18px;
}

.season-climate__item {
    background: #f6f8fa;
    border-radius: 10px;
    min-height: 92px;
    padding: 16px;
}

.season-climate__item strong {
    color: #181d22;
    display: block;
    font-size: 16px;
    margin-top: 18px;
}

.season-climate__item em {
    margin-top: 10px;
}

.season-table-card {
    overflow: hidden;
    padding: 0;
}

.season-table-card .season-card__title {
    padding: 20px 22px;
}

:deep(.season-data-table) {
    border-top: 1px solid #edf1f4;
}

:deep(.season-data-table .el-table__header-wrapper th) {
    background: #f5f7fa;
    color: #3d4a53;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

:deep(.season-data-table .el-table__row td) {
    color: #182126;
    font-size: 14px;
}

:deep(.season-harvests-table .el-table__row) {
    cursor: pointer;
}

.season-status-pill.is-blue {
    background: #dce9ff;
    color: #2158d8;
}

.season-docs-row {
    display: grid;
    gap: 18px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.season-doc-card {
    align-items: center;
    display: flex;
    gap: 14px;
    min-height: 72px;
    padding: 0 18px;
}

.season-doc-card__icon {
    align-items: center;
    background: #aaf1cf;
    border-radius: 6px;
    color: #0b5d46;
    display: inline-flex;
    font-size: 18px;
    height: 36px;
    justify-content: center;
    width: 36px;
}

.season-doc-card strong,
.season-activity-cell strong {
    color: #172026;
    display: block;
    font-size: 14px;
    font-weight: 700;
}

.season-doc-card small,
.season-activity-cell small {
    color: #8a96a0;
    display: block;
    font-size: 12px;
    margin-top: 4px;
}

.season-completed-status {
    align-items: center;
    color: #0b9a5c;
    display: inline-flex;
    font-weight: 600;
    gap: 6px;
}

.season-table-info {
    align-items: center;
    color: #172026;
    display: inline-flex;
    gap: 8px;
}

.season-table-info :deep(.el-icon) {
    color: #0b5d46;
    font-size: 15px;
}

.season-table-info strong {
    font-size: 13px;
    font-weight: 700;
}

.season-table-info--stack {
    align-items: flex-start;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.season-table-info__line {
    align-items: center;
    display: inline-flex;
    gap: 8px;
}

.season-table-info__line.is-subtle {
    color: #8a96a0;
}

.season-delete-harvest-btn {
    align-items: center;
    background: transparent;
    border: 0;
    color: #dc2626;
    cursor: pointer;
    display: inline-flex;
    font-size: 13px;
    font-weight: 700;
    justify-content: flex-end;
    line-height: 1;
    padding: 0;
}

.season-delete-harvest-btn :deep(.el-icon) {
    font-size: 16px;
}

.season-delete-harvest-btn:hover {
    color: #b91c1c;
}

@media (max-width: 1280px) {
    .season-summary-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .season-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 980px) {
    .season-content {
        padding-inline: 0;
    }

    .season-hero__head {
        align-items: flex-start;
        flex-direction: column;
    }

    .season-docs-row,
    .season-summary-grid {
        grid-template-columns: 1fr;
    }
}
</style>
