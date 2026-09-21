<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import InputError from '@/Components/InputError.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import AddFarmModal from '@/Components/Modals/AddFarmModal.vue';
import {
    Plus, View, Edit, Delete, Box, Download,
    Location, MapLocation, OfficeBuilding, Search, List, Grid,
    ArrowRight,
} from '@element-plus/icons-vue';

const props = defineProps({
    farms: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
    canCreateFarm: { type: Boolean, default: false },
});

function isActive(farm) {
    return (farm.status || 'active').toLowerCase() === 'active';
}

function hasCoordinates(farm) {
    return farm.latitude !== null && farm.latitude !== undefined && farm.longitude !== null && farm.longitude !== undefined;
}

function goToFarm(farm) {
    router.visit(route('farm.show', farm.id));
}

function farmLocation(farm) {
    return [farm.district, farm.region].filter(Boolean).join(', ') || farm.country || '—';
}

function farmInitials(farm) {
    const parts = (farm.name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return parts.length === 1 ? parts[0].slice(0, 2).toUpperCase() : (parts[0][0] + parts[1][0]).toUpperCase();
}

function fmtVolume(kg) {
    const value = Number(kg || 0);
    if (value > 0 && value < 1000) {
        return `${value.toLocaleString(undefined, { maximumFractionDigits: 0 })} KG`;
    }
    return `${(value / 1000).toLocaleString(undefined, { maximumFractionDigits: 1 })} MT`;
}

/* ── Real custody-pipeline stage list — same 4 stages / order used on the
   Store and single Farm Profile pages (collection → batch → lot → token),
   sourced from FarmController::farmSummaries(). ────────────────────────── */
const PIPELINE_STAGES = [
    { key: 'collections', label: 'Collections', short: 'C' },
    { key: 'batches', label: 'Batches', short: 'B' },
    { key: 'lots', label: 'Lots', short: 'L' },
    { key: 'tokenised', label: 'Tokenised', short: 'T' },
];

function traceDots(farm) {
    return Array.from({ length: PIPELINE_STAGES.length }, (_, i) => i < farm.pipelineStagesDone);
}

/* ── KPI rollups — every figure here is aggregated straight from the real
   per-farm fields/pipeline already resolved server-side; nothing is an
   estimate (no fabricated tree counts or EUDR percentages). ───────────── */
const kpis = computed(() => {
    const totalFarms = props.farms.length;
    const districts = new Set(props.farms.map((f) => f.district).filter(Boolean)).size;
    const located = props.farms.filter(hasCoordinates).length;
    const totalArea = props.farms.reduce((s, f) => s + (Number(f.total_area) || 0), 0);
    const cultivatedArea = props.farms.reduce((s, f) => s + (Number(f.coffee_area) || 0), 0);
    const activeFarms = props.farms.filter((f) => f.pipeline.collections.records > 0).length;
    const totalVolumeKg = props.farms.reduce((s, f) => s + (f.pipeline.collections.volume_kg || 0), 0);
    return { totalFarms, districts, located, totalArea, cultivatedArea, activeFarms, totalVolumeKg };
});

/* ── Search / sort / pagination — all client-side over the real farms
   payload. ──────────────────────────────────────────────────────────────── */
const search = ref('');
const sortKey = ref('recent');
const viewMode = ref('table');
const page = ref(1);
const pageSize = 5;

const filteredFarms = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.farms;
    return props.farms.filter((f) => {
        const hay = [f.name, f.farm_code, f.district, f.region, f.country].filter(Boolean).join(' ').toLowerCase();
        return hay.includes(q);
    });
});

const sortedFarms = computed(() => {
    const list = [...filteredFarms.value];
    if (sortKey.value === 'name') return list.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    if (sortKey.value === 'area') return list.sort((a, b) => (Number(b.total_area) || 0) - (Number(a.total_area) || 0));
    if (sortKey.value === 'lastCollection') {
        return list.sort((a, b) => new Date(b.latestCollection?.collection_date || 0) - new Date(a.latestCollection?.collection_date || 0));
    }
    return list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
});

watch(search, () => { page.value = 1; });

const pageCount = computed(() => Math.max(1, Math.ceil(sortedFarms.value.length / pageSize)));
const pagedFarms = computed(() => sortedFarms.value.slice((page.value - 1) * pageSize, page.value * pageSize));

/* ── CSV export — exports exactly the rows currently in view (filtered +
   sorted), nothing more. ───────────────────────────────────────────────── */
function exportCsv() {
    const header = ['Farm', 'Farm Code', 'District', 'Region', 'Country', 'Latitude', 'Longitude', 'Total Area (ha)', 'Coffee Area (ha)', 'Coffee Type', 'Status'];
    const rows = sortedFarms.value.map((f) => [
        f.name, f.farm_code, f.district, f.region, f.country, f.latitude, f.longitude, f.total_area, f.coffee_area, f.coffee_type, isActive(f) ? 'Active' : 'Inactive',
    ]);
    const csv = [header, ...rows]
        .map((row) => row.map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(','))
        .join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'my-farms.csv';
    link.click();
    URL.revokeObjectURL(url);
}

/* ── Add farm ──────────────────────────────────────────────────────── */
const addDialogOpen = ref(false);

function openAddDialog() {
    addDialogOpen.value = true;
}

/* ── Edit farm ─────────────────────────────────────────────────────── */
const editDialogOpen = ref(false);
const editingFarmId = ref(null);

const editForm = useForm({
    name: '',
    coffee_type: '',
    country: '',
    region: '',
    district: '',
    county: '',
    subcounty: '',
    parish: '',
    village: '',
    latitude: '',
    longitude: '',
    elevation: '',
    total_area: '',
    coffee_area: '',
});

function openEditDialog(farm) {
    editingFarmId.value = farm.id;
    editForm.clearErrors();
    editForm.name = farm.name || '';
    editForm.coffee_type = farm.coffee_type || '';
    editForm.country = farm.country || '';
    editForm.region = farm.region || '';
    editForm.district = farm.district || '';
    editForm.county = farm.county || '';
    editForm.subcounty = farm.subcounty || '';
    editForm.parish = farm.parish || '';
    editForm.village = farm.village || '';
    editForm.latitude = farm.latitude ?? '';
    editForm.longitude = farm.longitude ?? '';
    editForm.elevation = farm.elevation ?? '';
    editForm.total_area = farm.total_area ?? '';
    editForm.coffee_area = farm.coffee_area ?? '';
    editDialogOpen.value = true;
}

function submitEditFarm() {
    editForm.patch(route('farm.update', editingFarmId.value), {
        preserveScroll: true,
        onSuccess: () => { editDialogOpen.value = false; },
    });
}

/* ── Delete farm ───────────────────────────────────────────────────── */
const deleteDialogOpen = ref(false);
const deletingFarm = ref(false);
const farmToDelete = ref(null);

function openDeleteDialog(farm) {
    farmToDelete.value = farm;
    deleteDialogOpen.value = true;
}

function deleteFarm() {
    if (!farmToDelete.value) return;
    deletingFarm.value = true;
    router.delete(route('farm.destroy', farmToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deletingFarm.value = false;
            deleteDialogOpen.value = false;
            farmToDelete.value = null;
        },
    });
}
</script>

<template>
    <MainLayout title="My Farms">
        <div class="mf-page">

            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="mf-header">
                <div class="mf-header__text">
                    <h1 class="mf-title">My Farms</h1>
                    <p class="mf-subtitle">Manage your registered farms, review real intake &amp; custody pipeline data, and keep agronomic records up to date.</p>
                </div>
                <div class="mf-header__actions">
                    <button type="button" class="mf-btn mf-btn--outline" @click="exportCsv">
                        <el-icon><Download /></el-icon> Export CSV
                    </button>
                    <button v-if="canCreateFarm" type="button" class="mf-btn mf-btn--primary" @click="openAddDialog">
                        <el-icon><Plus /></el-icon> Add Farm
                    </button>
                </div>
            </div>

            <!-- ── KPI Summary ───────────────────────────────────────────── -->
            <div class="mf-kpi-grid">
                <div class="mf-kpi">
                    <div class="mf-kpi__head">
                        <span class="mf-kpi__label">My Farms</span>
                        <div class="mf-kpi__icon"><el-icon><OfficeBuilding /></el-icon></div>
                    </div>
                    <div class="mf-kpi__value">{{ kpis.totalFarms }}</div>
                    <div class="mf-kpi__foot">
                        <span>Across {{ kpis.districts }} district{{ kpis.districts === 1 ? '' : 's' }}</span>
                    </div>
                </div>
                <div class="mf-kpi">
                    <div class="mf-kpi__head">
                        <span class="mf-kpi__label">Located Farms</span>
                        <div class="mf-kpi__icon"><el-icon><MapLocation /></el-icon></div>
                    </div>
                    <div class="mf-kpi__value">{{ kpis.located }} <span class="mf-kpi__value-of">/ {{ kpis.totalFarms }}</span></div>
                    <div class="mf-kpi__foot">
                        <span>{{ kpis.totalFarms - kpis.located }} without GPS coordinates</span>
                    </div>
                </div>
                <div class="mf-kpi">
                    <div class="mf-kpi__head">
                        <span class="mf-kpi__label">Total Farm Area</span>
                        <div class="mf-kpi__icon"><el-icon><Grid /></el-icon></div>
                    </div>
                    <div class="mf-kpi__value">{{ kpis.totalArea.toLocaleString(undefined, { maximumFractionDigits: 1 }) }} <span class="mf-kpi__unit">ha</span></div>
                    <div class="mf-kpi__foot">
                        <span>{{ kpis.cultivatedArea.toLocaleString(undefined, { maximumFractionDigits: 1 }) }} ha under coffee</span>
                    </div>
                </div>
                <div class="mf-kpi">
                    <div class="mf-kpi__head">
                        <span class="mf-kpi__label">Active In-Flow</span>
                        <div class="mf-kpi__icon"><el-icon><Box /></el-icon></div>
                    </div>
                    <div class="mf-kpi__value">{{ kpis.activeFarms }} <span class="mf-kpi__unit">farm{{ kpis.activeFarms === 1 ? '' : 's' }}</span></div>
                    <div class="mf-kpi__foot">
                        <span>{{ fmtVolume(kpis.totalVolumeKg) }} recorded in collections</span>
                    </div>
                </div>
            </div>

            <!-- ── Toolbar: search, sort, view switcher ─────────────────── -->
            <div class="mf-toolbar-card">
                <div class="mf-toolbar-row">
                    <el-input v-model="search" size="small" class="mf-search-input" placeholder="Search farm name, location, or farm code..." clearable>
                        <template #prefix><el-icon><Search /></el-icon></template>
                    </el-input>
                    <div class="mf-toolbar-controls">
                        <div class="mf-sort">
                            <span class="mf-sort__label">Sort:</span>
                            <el-select v-model="sortKey" size="small" class="mf-sort-select">
                                <el-option label="Recently Registered" value="recent" />
                                <el-option label="Farm Name (A–Z)" value="name" />
                                <el-option label="Largest Farm (Area)" value="area" />
                                <el-option label="Most Recent Collection" value="lastCollection" />
                            </el-select>
                        </div>
                        <div class="mf-view-switch">
                            <button type="button" class="mf-view-btn" :class="{ 'mf-view-btn--active': viewMode === 'table' }" @click="viewMode = 'table'">
                                <el-icon><List /></el-icon> Table
                            </button>
                            <button type="button" class="mf-view-btn" :class="{ 'mf-view-btn--active': viewMode === 'grid' }" @click="viewMode = 'grid'">
                                <el-icon><Grid /></el-icon> Grid
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Table view ────────────────────────────────────────────── -->
            <div v-if="viewMode === 'table'" class="mf-card">
                <div v-if="pagedFarms.length" class="mf-table-wrap">
                    <table class="table align-middle mb-0 mf-table">
                        <colgroup>
                            <col style="width: 16%">
                            <col style="width: 11%">
                            <col style="width: 11%">
                            <col style="width: 8%">
                            <col style="width: 10%">
                            <col style="width: 14%">
                            <col style="width: 12%">
                            <col style="width: 8%">
                            <col style="width: 10%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Farm &amp; ID</th>
                                <th>Location</th>
                                <th>Producer</th>
                                <th class="text-end">Area</th>
                                <th>Variety</th>
                                <th>Inventory Pipeline</th>
                                <th>Last Collection</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="farm in pagedFarms" :key="farm.id" class="mf-table-row" @click="goToFarm(farm)">
                                <td>
                                    <div class="mf-farm-cell">
                                        <div class="mf-farm-cell__avatar">{{ farmInitials(farm) }}</div>
                                        <div class="mf-cell-truncate">
                                            <div class="mf-farm-cell__name mf-ellipsis">{{ farm.name }}</div>
                                            <div class="mf-farm-cell__code fp-mono">{{ farm.farm_code || '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="mf-cell-truncate">
                                    <div class="mf-loc-cell__main mf-ellipsis">{{ farmLocation(farm) }}</div>
                                    <div v-if="hasCoordinates(farm)" class="mf-loc-cell__gps fp-mono" :title="`${farm.latitude}°, ${farm.longitude}°`">
                                        {{ Number(farm.latitude).toFixed(2) }}°, {{ Number(farm.longitude).toFixed(2) }}°
                                    </div>
                                </td>
                                <td class="mf-cell-truncate">
                                    <template v-if="farm.owner">
                                        <div class="mf-table-strong mf-ellipsis">{{ farm.owner.name || '—' }}</div>
                                        <div class="mf-muted mf-ellipsis">{{ farm.owner.ownership_percentage !== null ? `${farm.owner.ownership_percentage}%` : (farm.owner.is_primary ? 'Primary' : 'Owner') }}</div>
                                    </template>
                                    <span v-else class="mf-muted">No owner</span>
                                </td>
                                <td class="text-end">
                                    <div class="mf-table-strong">{{ farm.total_area !== null && farm.total_area !== undefined ? `${farm.total_area} ha` : '—' }}</div>
                                    <div class="mf-muted">{{ farm.coffee_area ? `${farm.coffee_area} ha coffee` : '—' }}</div>
                                </td>
                                <td class="mf-cell-truncate">
                                    <span v-if="farm.coffee_type" class="mf-tag">{{ farm.coffee_type }}</span>
                                    <span v-else class="mf-muted">—</span>
                                    <div v-if="farm.crop_varieties?.length" class="mf-muted mf-mt-2 mf-ellipsis" :title="farm.crop_varieties.map((v) => v.name).join(', ')">{{ farm.crop_varieties.map((v) => v.name).join(', ') }}</div>
                                </td>
                                <td class="mf-pipeline-cell">
                                    <div class="mf-pipeline-cell__top">
                                        <span class="mf-pipeline-cell__vol">{{ fmtVolume(farm.pipeline.collections.volume_kg) }}</span>
                                        <div class="mf-trace-dots">
                                            <span v-for="(done, i) in traceDots(farm)" :key="i" class="mf-trace-dot" :class="{ 'mf-trace-dot--done': done }" :title="PIPELINE_STAGES[i].label"></span>
                                        </div>
                                    </div>
                                    <span class="mf-pipeline-cell__nodes">{{ farm.pipelineStagesDone }}/4 stages</span>
                                </td>
                                <td class="mf-cell-truncate">
                                    <template v-if="farm.latestCollection">
                                        <div class="mf-table-strong">{{ farm.latestCollection.collection_date || '—' }}</div>
                                        <div class="mf-muted fp-mono mf-ellipsis">{{ farm.latestCollection.collection_code }}</div>
                                    </template>
                                    <span v-else class="mf-muted">None yet</span>
                                </td>
                                <td class="text-center">
                                    <span class="mf-badge" :class="hasCoordinates(farm) ? 'mf-badge--good' : 'mf-badge--neutral'">{{ hasCoordinates(farm) ? 'Located' : 'Not Located' }}</span>
                                </td>
                                <td class="text-end" @click.stop>
                                    <div class="mf-row-actions">
                                        <el-tooltip content="View" placement="top">
                                            <Link :href="route('farm.show', farm.id)" class="mf-act-btn mf-act-btn--view">
                                                <el-icon><View /></el-icon>
                                            </Link>
                                        </el-tooltip>
                                        <el-tooltip content="Edit" placement="top">
                                            <button type="button" class="mf-act-btn mf-act-btn--edit" @click="openEditDialog(farm)">
                                                <el-icon><Edit /></el-icon>
                                            </button>
                                        </el-tooltip>
                                        <el-tooltip content="Delete" placement="top">
                                            <button type="button" class="mf-act-btn mf-act-btn--delete" @click="openDeleteDialog(farm)">
                                                <el-icon><Delete /></el-icon>
                                            </button>
                                        </el-tooltip>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="mf-empty">
                    <el-icon :size="24" class="mf-empty__icon"><Box /></el-icon>
                    <div class="mf-empty__title">{{ farms.length ? 'No farms match your search' : "You haven't added any farms yet" }}</div>
                    <p class="mf-empty__text">{{ farms.length ? 'Try a different search term.' : 'Register your first farm to start tracking quality and traceability.' }}</p>
                    <button v-if="!farms.length && canCreateFarm" type="button" class="mf-btn mf-btn--primary" @click="openAddDialog">
                        <el-icon><Plus /></el-icon> Add Your First Farm
                    </button>
                    <button v-else-if="farms.length" type="button" class="mf-btn mf-btn--outline" @click="search = ''">Clear Search</button>
                </div>

                <div v-if="sortedFarms.length" class="mf-table-footer">
                    <span>Showing <strong>{{ pagedFarms.length }}</strong> of <strong>{{ sortedFarms.length }}</strong> registered coffee farm{{ sortedFarms.length === 1 ? '' : 's' }}</span>
                    <div class="mf-pagination">
                        <button type="button" class="mf-page-btn" :disabled="page === 1" @click="page = Math.max(1, page - 1)">Prev</button>
                        <button v-for="p in pageCount" :key="p" type="button" class="mf-page-btn" :class="{ 'mf-page-btn--active': p === page }" @click="page = p">{{ p }}</button>
                        <button type="button" class="mf-page-btn" :disabled="page === pageCount" @click="page = Math.min(pageCount, page + 1)">Next</button>
                    </div>
                </div>
            </div>

            <!-- ── Grid view ─────────────────────────────────────────────── -->
            <div v-else class="mf-grid">
                <div v-for="farm in pagedFarms" :key="farm.id" class="mf-grid-card" @click="goToFarm(farm)">
                    <div class="mf-grid-card__head">
                        <div class="min-w-0">
                            <span class="mf-grid-card__code fp-mono">{{ farm.farm_code || '—' }}</span>
                            <h3 class="mf-grid-card__name">{{ farm.name }}</h3>
                            <p class="mf-grid-card__sub">{{ farmLocation(farm) }}<template v-if="farm.owner"> · {{ farm.owner.name }}</template></p>
                        </div>
                        <span class="mf-badge" :class="hasCoordinates(farm) ? 'mf-badge--good' : 'mf-badge--neutral'">{{ hasCoordinates(farm) ? 'Located' : 'Not Located' }}</span>
                    </div>
                    <div class="mf-grid-card__stats">
                        <div><span class="mf-grid-card__stat-label">Area</span><span class="mf-grid-card__stat-value">{{ farm.total_area ? `${farm.total_area} ha` : '—' }}</span></div>
                        <div><span class="mf-grid-card__stat-label">Variety</span><span class="mf-grid-card__stat-value">{{ farm.coffee_type || '—' }}</span></div>
                        <div><span class="mf-grid-card__stat-label">In Pipeline</span><span class="mf-grid-card__stat-value">{{ fmtVolume(farm.pipeline.collections.volume_kg) }}</span></div>
                    </div>
                    <div class="mf-grid-card__row">
                        <span>Latest Collection:</span>
                        <span class="fp-mono mf-table-strong">{{ farm.latestCollection ? `${farm.latestCollection.collection_date} (${farm.latestCollection.quantity}${farm.latestCollection.unit})` : 'None yet' }}</span>
                    </div>
                    <div class="mf-grid-card__foot">
                        <span class="mf-muted">{{ farm.pipelineStagesDone }}/4 Stages Active</span>
                        <Link :href="route('farm.show', farm.id)" class="mf-btn mf-btn--primary mf-btn--sm" @click.stop>View Farm Profile <el-icon><ArrowRight /></el-icon></Link>
                    </div>
                </div>

                <div v-if="!pagedFarms.length" class="mf-empty mf-grid-empty">
                    <el-icon :size="24" class="mf-empty__icon"><Box /></el-icon>
                    <div class="mf-empty__title">{{ farms.length ? 'No farms match these filters' : "You haven't added any farms yet" }}</div>
                    <p class="mf-empty__text">{{ farms.length ? 'Try clearing a filter or search term.' : 'Register your first farm to start tracking quality and traceability.' }}</p>
                </div>
            </div>

            <!-- ── Add Farm modal ───────────────────────────────────────── -->
            <AddFarmModal v-model="addDialogOpen" />

            <!-- ── Edit Farm modal — borrows the fp-modal design language ── -->
            <el-dialog v-model="editDialogOpen" width="min(680px, calc(100vw - 2rem))" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon"><el-icon :size="18"><Edit /></el-icon></div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Workspace</div>
                            <div class="fp-modal__title">Edit Farm</div>
                        </div>
                    </div>
                </template>

                <form id="edit-farm-form" class="fp-modal__body" @submit.prevent="submitEditFarm">
                    <div class="fp-field">
                        <label class="fp-field__label">Farm Name</label>
                        <el-input v-model="editForm.name" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.name }" />
                        <InputError class="fp-field__error" :message="editForm.errors.name" />
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Coffee Type</label>
                            <el-input v-model="editForm.coffee_type" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.coffee_type }" />
                            <InputError class="fp-field__error" :message="editForm.errors.coffee_type" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Country</label>
                            <el-input v-model="editForm.country" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.country }" />
                            <InputError class="fp-field__error" :message="editForm.errors.country" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Region</label>
                            <el-input v-model="editForm.region" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.region }" />
                            <InputError class="fp-field__error" :message="editForm.errors.region" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">District</label>
                            <el-input v-model="editForm.district" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.district }" />
                            <InputError class="fp-field__error" :message="editForm.errors.district" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Elevation (m)</label>
                            <el-input v-model="editForm.elevation" type="number" step="0.01" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.elevation }" />
                            <InputError class="fp-field__error" :message="editForm.errors.elevation" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Total Area (ha)</label>
                            <el-input v-model="editForm.total_area" type="number" min="0" step="0.01" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.total_area }" />
                            <InputError class="fp-field__error" :message="editForm.errors.total_area" />
                        </div>
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Coffee Area (ha) <span class="fp-field__optional">(optional)</span></label>
                        <el-input v-model="editForm.coffee_area" type="number" min="0" step="0.01" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.coffee_area }" />
                        <InputError class="fp-field__error" :message="editForm.errors.coffee_area" />
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="submit" form="edit-farm-form" class="mf-btn mf-btn--primary" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete Farm modal — borrows the fp-modal design language ── -->
            <ConfirmDialog
                v-model="deleteDialogOpen"
                eyebrow="Farm Workspace"
                title="Delete Farm"
                :message="farmToDelete ? `Are you sure you want to delete ${farmToDelete.name}? This action cannot be undone.` : ''"
                confirm-text="Delete Farm"
                :auto-close="false"
                :loading="deletingFarm"
                @confirm="deleteFarm"
            />

        </div>
    </MainLayout>
</template>

<style scoped>
.mf-page {
    --primary: #000000;
    --on-primary: #ffffff;
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --surface-elevated: #F1F2F3;
    --border: #E5E7EB;
    --text: #121516;
    --text-2: #4B5457;
    --text-muted: #6F7677;
    --success: #15803D;
    --success-soft: #F0FDF4;
    --error: #B91C1C;
    --error-soft: #FEF2F2;
    --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-family: var(--font-sans);
    background: var(--surface);
    color: var(--text);
    min-height: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.fp-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }

/* ── Header ────────────────────────────────────────────────────────────── */
.mf-header { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: nowrap; }
.mf-header__text { min-width: 0; flex: 1 1 auto; }
.mf-title { font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0 0 6px; }
.mf-subtitle { font-size: 13.5px; line-height: 20px; color: var(--text-2); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mf-header__actions { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; flex-shrink: 0; }

/* ── Buttons ───────────────────────────────────────────────────────────────
   NOTE: literal hex values on purpose, not var(--primary) — these classes
   are also used inside <el-dialog>, which teleports its content to <body>,
   outside .mf-page's CSS custom-property cascade. */
.mf-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    height: 36px; padding: 0 16px; border-radius: 6px;
    font-size: 13px; font-weight: 600; border: 1px solid transparent;
    text-decoration: none; cursor: pointer; transition: opacity 120ms ease, background 120ms ease;
}
.mf-btn--primary { background: #000000; color: #fff; }
.mf-btn--primary:hover:not(:disabled) { opacity: 0.88; }
.mf-btn--primary:disabled { opacity: .5; cursor: not-allowed; }
.mf-btn--outline { background: #ffffff; color: #121516; border-color: #E5E7EB; }
.mf-btn--outline:hover { background: #F5F6F7; }
.mf-btn--sm { height: 30px; padding: 0 12px; font-size: 12px; }

/* ── KPI cards ─────────────────────────────────────────────────────────── */
.mf-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.mf-kpi { border: 1px solid var(--border); border-radius: 6px; padding: 12px 14px; background: var(--surface); }
.mf-kpi__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 6px; }
.mf-kpi__label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.mf-kpi__icon { width: 24px; height: 24px; border-radius: 6px; background: var(--surface-muted); color: var(--text-2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 13px; }
.mf-kpi__value { font-size: 19px; font-weight: 800; letter-spacing: -0.01em; color: var(--text); line-height: 1.1; }
.mf-kpi__value-of, .mf-kpi__unit { font-size: 11.5px; font-weight: 600; color: var(--text-muted); }
.mf-kpi__foot { margin-top: 6px; font-size: 10.5px; color: var(--text-muted); }

/* ── Toolbar ───────────────────────────────────────────────────────────── */
.mf-toolbar-card { border: 1px solid var(--border); border-radius: 6px; padding: 14px 16px; display: flex; flex-direction: column; gap: 12px; background: var(--surface); }
.mf-toolbar-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
/* NOTE: resources/css/element-overrides.css globally forces every
   .el-input__wrapper/.el-select__wrapper to min-height:48px !important —
   these fields must fight that with matching !important (our selector's
   extra classes give it the higher specificity to win the tie), to match
   the 32px-tall filter fields used elsewhere in the app (e.g. LotPage's
   .lt-search-input/.lt-select). */
.mf-search-input { flex: 1; min-width: 200px; max-width: 260px; }
.mf-search-input :deep(.el-input__wrapper) {
    min-height: 32px !important;
    height: 32px !important;
    padding: 0 10px !important;
    box-shadow: 0 0 0 1px var(--border) inset !important;
    border-radius: 6px !important;
    background: var(--surface-muted) !important;
}
.mf-search-input :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1px var(--text) inset !important; background: var(--surface) !important; }
.mf-search-input :deep(.el-input__inner) { font-size: 12.5px !important; color: var(--text); height: 30px !important; line-height: 30px !important; }
.mf-search-input :deep(.el-input__prefix) { color: var(--text-muted); font-size: 13px; }

.mf-toolbar-controls { display: flex; align-items: center; gap: 12px; flex-shrink: 0; flex-wrap: wrap; }
.mf-sort { display: flex; align-items: center; gap: 8px; }
.mf-sort__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); flex-shrink: 0; }
.mf-sort-select { width: 170px; }
.mf-sort-select :deep(.el-select__wrapper) {
    min-height: 32px !important;
    height: 32px !important;
    padding: 0 10px !important;
    box-shadow: 0 0 0 1px var(--border) inset !important;
    border-radius: 6px !important;
    background: var(--surface-muted) !important;
}
.mf-sort-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1px var(--text) inset !important; }
.mf-sort-select :deep(.el-select__selected-item) { font-size: 12.5px !important; font-weight: 600; color: var(--text); }
.mf-view-switch { display: inline-flex; background: var(--surface-muted); padding: 3px; border-radius: 8px; gap: 2px; }
.mf-view-btn { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 6px; border: none; background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 700; cursor: pointer; }
.mf-view-btn--active { background: #fff; color: var(--text); box-shadow: 0 1px 2px rgba(0,0,0,0.06); }

/* ── Card — flat, bordered, no shadow, matching the app's default card
   convention (Lot/Batch/Apps/Weather/Inputs). ─────────────────────────── */
.mf-card {
    border: 1px solid var(--border);
    border-radius: 6px;
    overflow: hidden;
    background: var(--surface);
}

/* ── Table — fixed layout so all columns fit the card width with no
   horizontal scroll; long content truncates with an ellipsis (full value
   still available via title tooltip) instead of forcing overflow. ─────── */
.mf-table-wrap { width: 100%; overflow-x: auto; }
.mf-table { table-layout: fixed; width: 100%; }
.mf-table thead th {
    background: var(--surface-muted);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    padding: 9px 10px;
    border-bottom-color: transparent;
    white-space: normal;
    line-height: 1.3;
}
.mf-table tbody td { padding: 10px 10px; font-size: 12px; border-color: var(--border); vertical-align: middle; overflow: hidden; }
.mf-table-row { cursor: pointer; transition: background .12s ease; }
.mf-table-row:hover { background: var(--surface-muted); }
.mf-table-row:last-child td { border-bottom: none; }
.mf-table-strong { font-weight: 700; color: var(--text); }
.mf-muted { font-size: 11px; color: var(--text-muted); }
.mf-mt-2 { margin-top: 3px; }
.mf-cell-truncate { min-width: 0; }
.mf-ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }

.mf-farm-cell { display: flex; align-items: center; gap: 8px; min-width: 0; }
.mf-farm-cell__avatar { width: 26px; height: 26px; border-radius: 7px; background: var(--surface-elevated); color: var(--text-2); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; flex-shrink: 0; }
.mf-farm-cell__name { font-size: 12.5px; font-weight: 700; color: var(--text); }
.mf-farm-cell__code { font-size: 10px; color: var(--text-muted); }

.mf-loc-cell__main { font-weight: 600; font-size: 12px; color: var(--text); }
.mf-loc-cell__gps { font-size: 10px; color: var(--text-muted); margin-top: 2px; }

.mf-tag { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; background: var(--surface-elevated); color: var(--text-2); max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.mf-pipeline-cell__top { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.mf-pipeline-cell__vol { font-size: 12px; font-weight: 700; color: var(--text); flex-shrink: 0; }
.mf-pipeline-cell__nodes { display: block; margin-top: 4px; font-size: 10px; font-weight: 600; color: var(--text-muted); }

.mf-trace-dots { display: inline-flex; gap: 3px; flex-shrink: 0; }
.mf-trace-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--surface-elevated); }
.mf-trace-dot--done { background: #15803d; }

.mf-badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 700; flex-shrink: 0; }
.mf-badge--good { background: var(--success-soft); color: var(--success); }
.mf-badge--neutral { background: var(--surface-elevated); color: var(--text-2); }

.mf-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 2px; }
.mf-act-btn { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 6px; text-decoration: none; border: none; background: transparent; cursor: pointer; transition: background .15s ease, color .15s ease; color: var(--text-2); flex-shrink: 0; }
.mf-act-btn :deep(svg) { width: 13px; height: 13px; }
.mf-act-btn:hover { background: var(--surface-elevated); color: var(--text); }
.mf-act-btn--delete:hover { background: var(--error-soft); color: var(--error); }

.mf-table-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 12px 16px; border-top: 1px solid var(--border); background: var(--surface-muted); font-size: 12px; color: var(--text-2); }
.mf-pagination { display: flex; align-items: center; gap: 4px; }
.mf-page-btn { min-width: 28px; height: 28px; padding: 0 6px; border-radius: 6px; border: 1px solid transparent; background: transparent; color: var(--text-2); font-size: 12px; font-weight: 700; cursor: pointer; }
.mf-page-btn:hover:not(:disabled) { background: var(--surface-elevated); }
.mf-page-btn--active { background: #000000; color: #fff; }
.mf-page-btn:disabled { opacity: .4; cursor: not-allowed; }

/* ── Grid view ─────────────────────────────────────────────────────────── */
.mf-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.mf-grid-card { border: 1px solid var(--border); border-radius: 6px; padding: 18px; background: var(--surface); cursor: pointer; transition: box-shadow .15s ease; display: flex; flex-direction: column; gap: 14px; }
.mf-grid-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
.mf-grid-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.mf-grid-card__code { font-size: 10px; color: var(--text-muted); text-transform: uppercase; }
.mf-grid-card__name { font-size: 15px; font-weight: 800; color: var(--text); margin: 2px 0 0; }
.mf-grid-card__sub { font-size: 11.5px; color: var(--text-muted); margin: 2px 0 0; }
.mf-grid-card__stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; background: var(--surface-muted); padding: 10px; border-radius: 8px; text-align: center; }
.mf-grid-card__stat-label { display: block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); }
.mf-grid-card__stat-value { display: block; font-size: 12.5px; font-weight: 700; color: var(--text); margin-top: 2px; }
.mf-grid-card__row { display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: var(--text-muted); }
.mf-grid-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding-top: 12px; border-top: 1px solid var(--border); }
.mf-grid-empty { grid-column: 1 / -1; border: 1px solid var(--border); border-radius: 6px; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.mf-empty { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 48px 20px; }
.mf-empty__icon { color: var(--text-muted); margin-bottom: 12px; }
.mf-empty__title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.mf-empty__text { font-size: 13px; color: var(--text-muted); margin: 0 0 16px; max-width: 360px; }
.mf-empty .mf-btn--primary { display: inline-flex; }

/* ── Modal — same header/body/footer structure and literal hex palette as
   every other modal in the app (AttachBatchModal, Apps' Create Agent
   dialog). NOTE: <el-dialog> teleports to <body>, outside .mf-page, so
   CSS custom properties don't cascade in — literal hex is used below. */
:deep(.el-dialog.fp-modal) {
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
:deep(.el-dialog.fp-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.fp-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.fp-modal .el-dialog__footer) { padding: 0; }

.fp-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.fp-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-modal__head-text { flex: 1; min-width: 0; }
.fp-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6F7677; margin-bottom: 1px; }
.fp-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }

.fp-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }

.fp-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.fp-field { display: flex; flex-direction: column; gap: 5px; }
.fp-field-input { width: 100%; }
.fp-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.fp-field__optional { font-weight: 400; color: #6F7677; }
.fp-field__error { font-size: 12px; font-weight: 500; color: #F85149; margin-top: 4px; display: block; }

:deep(.fp-field-input .el-input__wrapper),
:deep(.fp-field-input .el-textarea__inner),
:deep(.fp-field-input .el-select__wrapper) { box-shadow: 0 0 0 1px #E5E7EB inset; border-radius: 6px; background: #F5F6F7; }
.fp-field-input--error :deep(.el-input__wrapper),
.fp-field-input--error :deep(.el-textarea__inner),
.fp-field-input--error :deep(.el-select__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

/* Footer has no Cancel button — the single action sits right-aligned. */
.fp-modal__footer { display: flex; justify-content: flex-end; padding: 16px 24px; background: #F5F6F7; border-top: 1px solid #E5E7EB; }

@media (max-width: 1200px) {
    .mf-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 767.98px) {
    .mf-kpi-grid { grid-template-columns: 1fr; }
    .mf-toolbar-row { flex-direction: column; align-items: stretch; }
    .mf-toolbar-controls { justify-content: space-between; }
}

@media (max-width: 575.98px) {
    .mf-header { flex-direction: column; align-items: stretch; }
    .mf-header__actions { width: 100%; }
    .mf-header__actions .mf-btn { flex: 1; }
    .fp-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.fp-modal) { width: 92vw !important; }
}
</style>
