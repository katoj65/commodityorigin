<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import { isGoogleMapsConfigured, renderMap } from '@/services/googleMaps';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, DataLine, Delete, Download, Edit,
    Location, Medal, Opportunity, Plus, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning,
    Sunny, PartlyCloudy, Cloudy, Umbrella, Lightning,
    Files, Upload, Document,
} from '@element-plus/icons-vue';

const props = defineProps({
    farm: { type: Object, required: true },
    canEdit: { type: Boolean, default: false },
    varietyOptions: { type: Array, default: () => [] },
    climaticZoneOptions: { type: Array, default: () => [] },
    soilTypeOptions: { type: Array, default: () => [] },
    harvests: { type: Array, default: () => [] },
    pickMethodOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    weatherRegion: { type: String, default: null },
    weatherOutlook: { type: Array, default: () => [] },
    documents: { type: Array, default: () => [] },
    documentTypeOptions: { type: Array, default: () => [] },
});

/* ── Edit farm dialog (creator only) ──────────────────────────────── */
const editDialogOpen = ref(false);

const editForm = useForm({
    name: props.farm.name,
    location: props.farm.location,
    size: props.farm.size,
    altitude: props.farm.altitude,
    variety: props.farm.variety,
    notes: props.farm.notes,
});

function openEditDialog() {
    editForm.reset();
    editForm.clearErrors();
    editDialogOpen.value = true;
}

function submitEditFarm() {
    editForm.patch(route('farm.update', props.farm.id), {
        preserveScroll: true,
        onSuccess: () => { editDialogOpen.value = false; },
    });
}

/* ── Delete farm ───────────────────────────────────────────────────── */
const deleteDialogOpen = ref(false);
const deletingFarm = ref(false);

function deleteFarm() {
    deletingFarm.value = true;
    router.delete(route('farm.destroy', props.farm.id), {
        onFinish: () => {
            deletingFarm.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

/* ── Edit environmental specs dialog (creator only) ──────────────────── */
const specsDialogOpen = ref(false);

const specsForm = useForm({
    rainfall: props.farm.rainfall,
    temperature: props.farm.temperature,
    humidity: props.farm.humidity,
    soil_type: props.farm.soil_type,
    climatic_zone: props.farm.climatic_zone,
});

function openSpecsDialog() {
    specsForm.reset();
    specsForm.clearErrors();
    specsDialogOpen.value = true;
}

function submitSpecsForm() {
    specsForm.patch(route('farm.specs.update', props.farm.id), {
        preserveScroll: true,
        onSuccess: () => { specsDialogOpen.value = false; },
    });
}

/* ── Edit farm location dialog (creator only) ─────────────────────────── */
const locationDialogOpen = ref(false);

const locationForm = useForm({
    latitude: props.farm.latitude,
    longitude: props.farm.longitude,
    altitude: props.farm.altitude,
});

function openLocationDialog() {
    locationForm.reset();
    locationForm.clearErrors();
    locationDialogOpen.value = true;
}

function submitLocationForm() {
    locationForm.patch(route('farm.location.update', props.farm.id), {
        preserveScroll: true,
        onSuccess: () => { locationDialogOpen.value = false; },
    });
}

const geocoding = ref(false);
const geocodeError = ref('');

async function locateFromAddress() {
    geocodeError.value = '';

    if (!props.farm.location) {
        geocodeError.value = 'This farm has no address on file to look up.';
        return;
    }

    geocoding.value = true;
    try {
        const { data } = await axios.post(route('farm.geocode'), { address: props.farm.location });
        locationForm.latitude = data.lat;
        locationForm.longitude = data.lng;
    } catch (error) {
        geocodeError.value = error.response?.data?.message || 'Could not resolve coordinates for that address.';
    } finally {
        geocoding.value = false;
    }
}

/* ── Add harvest dialog (creator only) ────────────────────────────────── */
const harvestDialogOpen = ref(false);

const harvestForm = useForm({
    variety: '',
    date_planted: '',
    harvest_date: '',
    harvest_season: '',
    pick_method: '',
    price: '',
    weight: '',
    ripeness_percentage: '',
    foreign_matter_present: false,
    pest_damage: false,
    disease_signs: false,
    visible_defects: false,
});

function disableFutureDates(date) {
    const today = new Date();
    today.setHours(23, 59, 59, 999);
    return date.getTime() > today.getTime();
}

function openHarvestDialog() {
    harvestForm.reset();
    harvestForm.clearErrors();
    harvestForm.variety = props.farm.variety || '';
    harvestDialogOpen.value = true;
}

function submitHarvestForm() {
    harvestForm.post(route('farm.harvests.store', props.farm.id), {
        preserveScroll: true,
        onSuccess: () => {
            harvestDialogOpen.value = false;
            harvestForm.reset();
        },
    });
}

/* ── Delete harvest dialog (creator only) ─────────────────────────────── */
const deleteHarvestDialogOpen = ref(false);
const deletingHarvest = ref(false);
const harvestToDelete = ref(null);

function openDeleteHarvestDialog(harvest) {
    harvestToDelete.value = harvest;
    deleteHarvestDialogOpen.value = true;
}

function deleteHarvest() {
    if (!harvestToDelete.value) return;
    deletingHarvest.value = true;
    router.delete(route('farm.harvests.destroy', [props.farm.id, harvestToDelete.value.id]), {
        preserveScroll: true,
        onFinish: () => {
            deletingHarvest.value = false;
            deleteHarvestDialogOpen.value = false;
            harvestToDelete.value = null;
        },
    });
}

/* ── Upload document dialog (creator only) ────────────────────────────── */
const documentDialogOpen = ref(false);

const documentForm = useForm({
    title: '',
    document_type: '',
    document: null,
});

const documentFileName = ref('');

function openDocumentDialog() {
    documentForm.reset();
    documentForm.clearErrors();
    documentFileName.value = '';
    documentDialogOpen.value = true;
}

function onDocumentFileChange(event) {
    const file = event.target.files?.[0] || null;
    documentForm.document = file;
    documentFileName.value = file?.name || '';
}

function submitDocumentForm() {
    documentForm.post(route('farm.documents.store', props.farm.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            documentDialogOpen.value = false;
            documentForm.reset();
            documentFileName.value = '';
        },
    });
}

/* ── Delete document dialog (creator only) ────────────────────────────── */
const deleteDocumentDialogOpen = ref(false);
const deletingDocument = ref(false);
const documentToDelete = ref(null);

function openDeleteDocumentDialog(doc) {
    documentToDelete.value = doc;
    deleteDocumentDialogOpen.value = true;
}

function deleteDocument() {
    if (!documentToDelete.value) return;
    deletingDocument.value = true;
    router.delete(route('farm.documents.destroy', [props.farm.id, documentToDelete.value.id]), {
        preserveScroll: true,
        onFinish: () => {
            deletingDocument.value = false;
            deleteDocumentDialogOpen.value = false;
            documentToDelete.value = null;
        },
    });
}

function formatFileSize(bytes) {
    if (bytes === null || bytes === undefined) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function formatDocDate(value) {
    if (!value) return '—';
    const date = new Date(value.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

/* ── Real computed from prop ───────────────────────────────────── */
const farmName    = computed(() => props.farm.name || 'Farm Profile');
const farmerName  = computed(() => [props.farm.farmer?.first_name, props.farm.farmer?.last_name].filter(Boolean).join(' ') || 'Assigned Producer');
const locationLabel = computed(() =>
    props.farm.location ||
    [props.farm.farmer?.sub_county, props.farm.farmer?.district, 'Uganda'].filter(Boolean).join(', ') ||
    'Origin pending',
);
const estateBrief = computed(() =>
    props.farm.notes ||
    `${farmName.value} is managed for traceable coffee production with structured harvesting, post-harvest discipline, and field-level quality monitoring.`,
);
const altitudeRange = computed(() => props.farm.altitude || '—');
const latitudeLabel  = computed(() => (props.farm.latitude !== null && props.farm.latitude !== undefined) ? `${props.farm.latitude}°N` : '—');
const longitudeLabel = computed(() => (props.farm.longitude !== null && props.farm.longitude !== undefined) ? `${props.farm.longitude}°E` : '—');

/* ── Google Map ────────────────────────────────────────────────────── */
const hasCoordinates = computed(() =>
    props.farm.latitude !== null && props.farm.latitude !== undefined &&
    props.farm.longitude !== null && props.farm.longitude !== undefined,
);
const mapConfigured = isGoogleMapsConfigured();
const mapEl = ref(null);
const mapReady = ref(false);
const mapFailed = ref(false);

async function mountMap() {
    if (!hasCoordinates.value || !mapEl.value || !mapConfigured) return;
    mapFailed.value = false;
    try {
        await renderMap(
            mapEl.value,
            { lat: Number(props.farm.latitude), lng: Number(props.farm.longitude) },
            { markerTitle: farmName.value },
        );
        mapReady.value = true;
    } catch {
        mapFailed.value = true;
    }
}

onMounted(mountMap);
watch(() => [props.farm.latitude, props.farm.longitude], mountMap);
const farmSize      = computed(() => props.farm.size || '—');
const variety       = computed(() => props.farm.variety || props.farm.farmer?.coffee_type || 'Arabica');
const rainfall      = computed(() => props.farm.rainfall    || '—');
const temperature   = computed(() => props.farm.temperature || '—');
const humidityIndex = computed(() => props.farm.humidity    || '—');
const soilType      = computed(() => props.farm.soil_type     || '—');
const climaticZone  = computed(() => props.farm.climatic_zone || '—');
const bagsEstimate  = computed(() => {
    if (props.farm.total_bags_produced) return `${props.farm.total_bags_produced.toLocaleString()} Bags`;
    const d = String(props.farm.size || '').match(/\d+/);
    return d?.[0] ? `${d[0]} Bags` : '—';
});
const thumb = computed(() =>
    [props.farm.name?.[0], props.farm.variety?.[0]].filter(Boolean).join('').slice(0, 2).toUpperCase() || 'ES',
);
const isRobusta = computed(() => variety.value.toLowerCase().includes('robusta'));
const qualityScore = computed(() => isRobusta.value ? 84.3 : 88.5);

const harvestStatusTone = { pending: 'warning', processing: 'warning', processed: 'success', ready: 'success', sold: 'primary' };
function harvestCode(h) {
    return `#${(h.harvest_date || '').slice(0, 4) || new Date().getFullYear()}-EX${String(h.id).padStart(2, '0')}`;
}
const harvestRows = computed(() => props.harvests.map((h) => ({
    id: h.id,
    code: harvestCode(h),
    season: h.harvest_season || '—',
    date: h.harvest_date || '—',
    qty: h.weight !== null && h.weight !== undefined ? `${Number(h.weight).toLocaleString()} kg` : '—',
    score: h.ripeness_percentage !== null && h.ripeness_percentage !== undefined ? Number(h.ripeness_percentage) : null,
    status: h.status ? h.status.charAt(0).toUpperCase() + h.status.slice(1) : 'Pending',
    tone: harvestStatusTone[(h.status || 'pending').toLowerCase()] || 'primary',
})));

/* ── View harvest dialog ───────────────────────────────────────────── */
const viewHarvestDialogOpen = ref(false);
const harvestToView = ref(null);
const harvestToViewCode = computed(() => harvestToView.value ? harvestCode(harvestToView.value) : '');
const harvestToViewStatus = computed(() => {
    if (!harvestToView.value?.status) return 'Pending';
    const s = harvestToView.value.status;
    return s.charAt(0).toUpperCase() + s.slice(1);
});

function openViewHarvestDialog(row) {
    harvestToView.value = props.harvests.find((h) => String(h.id) === String(row.id)) || null;
    viewHarvestDialogOpen.value = true;
}

/* ── Static / mock data for new sections ───────────────────────── */
const productionBars = [52, 64, 68, 78, 74, 82, 88, 84, 90, 86, 92, 88];
const qualityBars    = [72, 78, 76, 82, 80, 86, 84, 88, 86, 90, 89, 92];

/* ── Farm Weather — planting-season outlook ───────────────────────── */
const weatherConditionMeta = {
    'Sunny': { icon: Sunny, bg: '#fffbeb', color: '#b45309' },
    'Partly Cloudy': { icon: PartlyCloudy, bg: '#eff6ff', color: '#1d4ed8' },
    'Cloudy': { icon: Cloudy, bg: '#f3f4f6', color: '#4b5563' },
    'Rainy': { icon: Umbrella, bg: '#eef2ff', color: '#4338ca' },
    'Thunderstorms': { icon: Lightning, bg: '#fef2f2', color: '#b91c1c' },
};

function weatherIcon(row) {
    return weatherConditionMeta[row.condition]?.icon || Cloudy;
}

function weatherIconStyle(row) {
    const meta = weatherConditionMeta[row.condition];
    return { background: meta?.bg || '#f3f4f6', color: meta?.color || '#4b5563' };
}

function weatherMonthLabel(row) {
    if (!row.forecast_date) return '—';
    const date = new Date(`${row.forecast_date}T00:00:00`);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
}

const weatherPreviewCount = 3;
const weatherPreview = computed(() => props.weatherOutlook.slice(0, weatherPreviewCount));
const hasMoreWeather = computed(() => props.weatherOutlook.length > weatherPreviewCount);

const traceStages = [
    { label: 'Farm',    icon: Location,      status: 'Verified',   date: 'Oct 2024', complete: true  },
    { label: 'Harvest', icon: Box,           status: 'Recorded',   date: 'Mar 2025', complete: true  },
    { label: 'Batch',   icon: CollectionTag, status: 'Processing', date: 'Apr 2025', complete: false },
    { label: 'Lot',     icon: Star,          status: 'Pending',    date: '—',        complete: false },
    { label: 'Market',  icon: ShoppingCart,  status: 'Ready',      date: '—',        complete: false },
];

const sustainability = [
    { label: 'Shade Farming',          value: '94%',       icon: Star   },
    { label: 'Water Usage',            value: 'Low Impact', icon: Check  },
    { label: 'Carbon Footprint',       value: '<0.62kg/kg', icon: Check  },
    { label: 'Soil Management',        value: 'Organic',    icon: Medal  },
    { label: 'Climate-Smart Practices',value: 'Active',     icon: Checked},
];

const lots = [
    { name: `${farmName.value} Natural Lot 1`, type: variety.value, score: qualityScore.value, price: '$4.80–5.20', qty: '1,200 kg', demand: 'High',   badges: ['Verified','Export Ready'], tone: 'success' },
    { name: `${farmName.value} Washed Lot 2`,  type: variety.value, score: qualityScore.value - 1, price: '$4.40–4.80', qty: '900 kg',   demand: 'Medium', badges: ['Verified'],               tone: 'warning' },
];

const buyerMarkets = [
    { region: 'Germany / EU', demand: 'High',   price: '$5.00–5.40', pct: 85, tone: 'success' },
    { region: 'UAE',          demand: 'Extreme', price: '$1.80–2.10', pct: 96, tone: 'danger'  },
    { region: 'USA',          demand: 'High',    price: '$4.80–5.20', pct: 78, tone: 'success' },
    { region: 'Japan',        demand: 'Medium',  price: '$5.20–5.80', pct: 62, tone: 'warning' },
];

const timeline = [
    { icon: Location,      text: 'Farm registered on Bean Origin',              date: 'Jan 2024',  tone: 'success' },
    { icon: Star,          text: 'Season 2024/25 created and activated',        date: 'Oct 2024',  tone: 'primary' },
    { icon: Box,           text: 'Harvest HV-001 recorded — 2,400 kg',          date: 'Mar 2025',  tone: 'success' },
    { icon: CollectionTag, text: 'Batch created — processing stage',            date: 'Apr 2025',  tone: 'warning' },
    { icon: ShoppingCart,  text: 'First lot submitted to market',               date: 'Apr 2025',  tone: 'info'   },
    { icon: Van,           text: 'Lot 1 sold — Nordic Roasters, Sweden',        date: 'May 2025',  tone: 'primary' },
];

const insights = [
    { text: 'This farm shows strong specialty Arabica potential with consistent 88+ cupping scores.', tone: 'success' },
    { text: 'Harvest consistency is improving season over season — up 12% vs 2023/24.',               tone: 'primary' },
    { text: 'Export demand is increasing for this farm\'s coffee profile in EU and UAE markets.',     tone: 'warning' },
];

const alerts = reactive({ harvestUpdates: true, qualityAlerts: true, buyerInterest: false, exportUpdates: true });

const data=computed(()=>props.farm);



</script>

<template>
    <DesignPreviewLayout :title="farmName">
        <div class="fp-page">

            <!-- ── 1. Sticky Header ───────────────────────────────────── -->
            <div class="fp-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="fp-header-kicker">Farm Profile</div>
                            <h1 class="fp-title mb-0">{{ farmName }}</h1>
                            <p class="fp-subtitle mb-0">Verified coffee farm with traceable production data</p>
                        </div>
                        <el-button-group v-if="props.canEdit" class="fp-btn-group">
                            <el-button :icon="Edit" @click="openEditDialog">Edit</el-button>
                            <el-button :icon="Delete" type="danger" @click="deleteDialogOpen = true">Delete</el-button>
                        </el-button-group>
                    </div>
                    <!-- <div class="d-flex flex-wrap gap-2 pb-2">
                        <span class="fp-hbadge"><el-icon><Checked /></el-icon> Verified Farm</span>
                        <span class="fp-hbadge fp-hbadge--soft"><el-icon><Check /></el-icon> Traceable Production</span>
                        <span class="fp-hbadge fp-hbadge--blue"><el-icon><Van /></el-icon> Export Ready</span>
                        <span class="fp-hbadge fp-hbadge--amber"><el-icon><Star /></el-icon> Sustainable</span>
                    </div> -->
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Hero Overview Card ──────────────────────────── -->
                <div class="fp-hero-card mb-3">
                    <div class="row g-0 align-items-stretch">
                        <!-- Left: Identity -->
                        <div class="col-12 col-md-4 fp-hero-identity">
                            <div class="fp-farm-avatar-lg">{{ thumb }}</div>
                            <div class="fp-hero-name">{{ farmName }}</div>

                            <div class="d-flex align-items-center gap-1 mb-2">
                                <el-icon style="font-size:12px; color:var(--on-surface-var);"><Location /></el-icon>
                                <span class="fp-td-muted" style="font-size:.8125rem;">{{ locationLabel }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">Verified</span>
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle" style="font-size:.65rem;">Export Ready</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">Organic</span>
                            </div>
                            <!-- <div class="fp-contact-row">
                                <span class="fp-td-muted" style="font-size:.75rem;">{{ props.farm.farmer?.email || 'producer@exchange.ug' }}</span>
                            </div> -->
                            <!-- <div class="fp-contact-row">
                                <span class="fp-td-muted" style="font-size:.75rem;">{{ props.farm.farmer?.telephone || '+256 700 000000' }}</span>
                            </div> -->
                        </div>

                        <!-- Center: Specs -->
                        <div class="col-12 col-md-4 fp-hero-specs">
                            <div class="fp-specs-head">
                                <div class="fp-specs-title" style="margin-bottom:0;">Farm Specifications</div>
                                <button v-if="props.canEdit" type="button" class="fp-specs-edit-btn" title="Edit environmental specifications" @click="openSpecsDialog">
                                    <el-icon><Edit /></el-icon>
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-6"><div class="fp-spec-cell"><span>Farm Size</span><strong>{{ farmSize }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Altitude</span><strong>{{ altitudeRange }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Coffee Type</span><strong>{{ variety }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Total Bags</span><strong>{{ bagsEstimate }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Rainfall</span><strong>{{ rainfall }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Temperature</span><strong>{{ temperature }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Humidity</span><strong>{{ humidityIndex }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Status</span><strong class="fp-up">{{ props.farm.status || 'Active' }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Soil Type</span><strong>{{ soilType }}</strong></div></div>
                                <div class="col-6"><div class="fp-spec-cell"><span>Climatic Zone</span><strong>{{ climaticZone }}</strong></div></div>
                            </div>
                        </div>

                        <!-- Right: Performance -->
                        <div class="col-12 col-md-4 fp-hero-perf">
                            <div class="fp-specs-title">Performance &amp; Trust</div>
                            <div class="fp-quality-ring-wrap mb-3">
                                <div class="fp-quality-ring">
                                    <svg viewBox="0 0 80 80" width="100" height="100">
                                        <circle cx="40" cy="40" r="34" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                        <circle cx="40" cy="40" r="34" fill="none" stroke="#004532" stroke-width="8"
                                            stroke-dasharray="213.6"
                                            :stroke-dashoffset="213.6 - (213.6 * ((qualityScore - 80) / 20))"
                                            stroke-linecap="round"
                                            transform="rotate(-90 40 40)"/>
                                    </svg>
                                    <div class="fp-quality-ring-inner">
                                        <div class="fp-quality-score">{{ qualityScore }}</div>
                                        <div class="fp-quality-label">SCA Score</div>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <span class="badge bg-success text-white mb-1 d-block" style="font-size:.65rem;">Specialty Grade</span>
                                    <span class="badge bg-primary text-white mb-1 d-block" style="font-size:.65rem;">Export Quality</span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle d-block" style="font-size:.65rem;">Premium</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="fp-perf-row"><span>Yield Efficiency</span><strong class="fp-up">88%</strong></div>
                                <div class="fp-perf-row"><span>Export Readiness</span><strong class="fp-up">92%</strong></div>
                                <div class="fp-perf-row"><span>Verification</span><strong class="fp-up">Verified ✓</strong></div>
                                <div class="fp-perf-row"><span>Defect Rate</span><strong>0.8%</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 3. Farm Location & Map -->
                            <div class="fp-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fp-card-title mb-0">
                                        <el-icon class="fp-card-icon"><Location /></el-icon>
                                        Farm Location &amp; Environment
                                    </div>
                                    <button v-if="props.canEdit" type="button" class="fp-specs-edit-btn" title="Edit farm location" @click="openLocationDialog">
                                        <el-icon><Edit /></el-icon>
                                    </button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-7">
                                        <div class="fp-map-tile">
                                            <template v-if="hasCoordinates && mapConfigured">
                                                <div ref="mapEl" class="fp-map-canvas"></div>
                                                <div v-if="!mapReady && !mapFailed" class="fp-map-empty">
                                                    <span class="fp-td-muted" style="font-size:.75rem;">Loading map…</span>
                                                </div>
                                                <div v-if="mapFailed" class="fp-map-empty">
                                                    <el-icon style="font-size:20px; color:var(--on-surface-var);"><Warning /></el-icon>
                                                    <span class="fp-td-muted" style="font-size:.75rem;">Map failed to load.</span>
                                                </div>
                                            </template>
                                            <div v-else class="fp-map-empty">
                                                <el-icon style="font-size:20px; color:var(--on-surface-var);"><Location /></el-icon>
                                                <span class="fp-td-muted" style="font-size:.75rem; text-align:center;">
                                                    {{ hasCoordinates ? 'Map unavailable — Google Maps is not configured.' : 'No coordinates set for this farm.' }}
                                                </span>
                                            </div>
                                            <div class="fp-map-coords">
                                                <div class="fw-semibold" style="font-size:.8125rem;">{{ latitudeLabel }}, {{ longitudeLabel }}</div>
                                                <div class="fp-td-muted" style="font-size:.7rem;">GPS coordinates — {{ locationLabel }}</div>
                                            </div>
                                            <div class="fp-map-badges">
                                                <span class="fp-map-badge">{{ altitudeRange }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <div class="d-flex flex-column gap-2 h-100 justify-content-center">
                                            <div class="fp-env-row"><span>Annual Rainfall</span><strong>{{ rainfall }}</strong></div>
                                            <div class="fp-env-row"><span>Temperature</span><strong>{{ temperature }}</strong></div>
                                            <div class="fp-env-row"><span>Humidity Index</span><strong>{{ humidityIndex }}</strong></div>
                                            <div class="fp-env-row"><span>Soil Type</span><strong>{{ soilType }}</strong></div>
                                            <div class="fp-env-row"><span>Climate Zone</span><strong>{{ climaticZone }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Harvest History -->
                            <div class="fp-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="fp-card-title">
                                        <el-icon class="fp-card-icon"><Box /></el-icon>
                                        Harvest History
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ harvestRows.length }} harvests</span>
                                        <button v-if="props.canEdit" type="button" class="fp-specs-edit-btn" title="Add harvest" @click="openHarvestDialog">
                                            <el-icon><Plus /></el-icon>
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 fp-table">
                                        <thead>
                                            <tr>
                                                <th><span class="fp-table-th"><el-icon><Box /></el-icon> Harvest ID</span></th>
                                                <th><span class="fp-table-th"><el-icon><CollectionTag /></el-icon> Season</span></th>
                                                <th><span class="fp-table-th"><el-icon><Clock /></el-icon> Date</span></th>
                                                <th><span class="fp-table-th"><el-icon><DataLine /></el-icon> Quantity</span></th>
                                                <th><span class="fp-table-th"><el-icon><Star /></el-icon> Quality Score</span></th>
                                                <th><span class="fp-table-th"><el-icon><Checked /></el-icon> Status</span></th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="h in harvestRows" :key="h.id" class="fp-table-row">
                                                <td class="fp-item-name">{{ h.code }}</td>
                                                <td class="fp-td-muted">{{ h.season }}</td>
                                                <td class="fp-td-muted">{{ h.date }}</td>
                                                <td class="fw-semibold">{{ h.qty }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span v-if="h.score !== null" class="fp-score-pill" :class="h.score >= 88 ? 'fp-score-pill--high' : 'fp-score-pill--mid'">{{ h.score }}</span>
                                                        <span v-else class="fp-td-muted">—</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="h.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : h.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'">
                                                        {{ h.status }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button type="button" class="btn btn-sm fp-btn-outline fp-act-btn" @click="openViewHarvestDialog(h)"><el-icon><View /></el-icon> View</button>
                                                        <button v-if="props.canEdit" type="button" class="btn btn-sm fp-btn-danger-ghost fp-act-btn" @click="openDeleteHarvestDialog(h)"><el-icon><Delete /></el-icon> Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="harvestRows.length === 0">
                                                <td colspan="7">
                                                    <div class="fp-table-empty">
                                                        <div class="fp-table-empty__icon"><el-icon :size="18"><Box /></el-icon></div>
                                                        <div class="fp-table-empty__title">No harvests recorded yet</div>
                                                        <p class="fp-table-empty__text">Add a harvest to start tracking yield, quality, and season history for this farm.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 5. Production Overview -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><TrendCharts /></el-icon>
                                    Production Overview
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="fp-td-muted mb-1" style="font-size:.75rem;">Annual Production Trend</div>
                                        <div class="fp-chart">
                                            <div class="fp-chart-bars">
                                                <div v-for="(v, i) in productionBars" :key="i" class="fp-chart-col">
                                                    <div class="fp-chart-bar fp-chart-bar--prod" :style="{ height: `${v}%` }"></div>
                                                    <span class="fp-chart-lbl">{{ ['J','F','M','A','M','J','J','A','S','O','N','D'][i] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="fp-td-muted mb-1" style="font-size:.75rem;">Quality Trend</div>
                                        <div class="fp-chart">
                                            <div class="fp-chart-bars">
                                                <div v-for="(v, i) in qualityBars" :key="i" class="fp-chart-col">
                                                    <div class="fp-chart-bar fp-chart-bar--qual" :style="{ height: `${v}%` }"></div>
                                                    <span class="fp-chart-lbl">{{ ['J','F','M','A','M','J','J','A','S','O','N','D'][i] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-6 col-md-3"><div class="fp-spec-cell"><span>Avg Production</span><strong>5,400 kg/yr</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fp-spec-cell"><span>Yield / ha</span><strong>2.4 t</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fp-spec-cell"><span>Active Season</span><strong>2024/25</strong></div></div>
                                    <div class="col-6 col-md-3"><div class="fp-spec-cell"><span>Coffee Type Split</span><strong>{{ variety }} 100%</strong></div></div>
                                </div>
                            </div>

                            <!-- 6. Farm-to-Market Traceability -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Van /></el-icon>
                                    Farm-to-Market Traceability
                                </div>
                                <div class="fp-trace-flow">
                                    <div v-for="(stage, i) in traceStages" :key="stage.label" class="fp-trace-stage">
                                        <div class="fp-trace-dot" :class="stage.complete ? 'fp-trace-dot--done' : 'fp-trace-dot--pending'">
                                            <el-icon><component :is="stage.icon" /></el-icon>
                                        </div>
                                        <div class="fp-trace-label">{{ stage.label }}</div>
                                        <div class="fp-trace-status" :class="stage.complete ? 'fp-up' : 'fp-td-muted'">{{ stage.status }}</div>
                                        <div class="fp-trace-date fp-td-muted">{{ stage.date }}</div>
                                        <div v-if="i < traceStages.length - 1" class="fp-trace-line" :class="stage.complete ? 'fp-trace-line--done' : ''"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Available Lots -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><ShoppingCart /></el-icon>
                                    Available Coffee Lots
                                </div>
                                <div class="row g-3">
                                    <div v-for="lot in lots" :key="lot.name" class="col-12 col-md-6">
                                        <div class="fp-lot-card h-100">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div class="fp-item-name">{{ lot.name }}</div>
                                                <div class="fp-score-pill" :class="lot.score >= 88 ? 'fp-score-pill--high' : 'fp-score-pill--mid'" style="flex-shrink:0;">{{ lot.score }}</div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                <span v-for="b in lot.badges" :key="b" class="badge rounded-pill" style="font-size:.6rem; padding:2px 7px;"
                                                    :class="b === 'Verified' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                          : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'">{{ b }}</span>
                                            </div>
                                            <div class="row g-1 mb-3">
                                                <div class="col-6"><div class="fp-spec-cell"><span>Type</span><strong>{{ lot.type }}</strong></div></div>
                                                <div class="col-6"><div class="fp-spec-cell"><span>Qty</span><strong>{{ lot.qty }}</strong></div></div>
                                                <div class="col-6"><div class="fp-spec-cell"><span>Price/kg</span><strong>{{ lot.price }}</strong></div></div>
                                                <div class="col-6"><div class="fp-spec-cell"><span>Demand</span>
                                                    <strong :class="lot.tone === 'success' ? 'fp-up' : 'fp-warn'">{{ lot.demand }}</strong>
                                                </div></div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn fp-btn-outline btn-sm flex-fill fp-act-btn"><el-icon><View /></el-icon> View Lot</button>
                                                <button class="btn fp-btn-primary btn-sm flex-fill fp-act-btn"><el-icon><ShoppingCart /></el-icon> Buy</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Buyer Interest & Demand -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><TrendCharts /></el-icon>
                                    Buyer Interest &amp; Demand
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="m in buyerMarkets" :key="m.region">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-semibold" style="font-size:.8125rem;">{{ m.region }}</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fp-td-muted" style="font-size:.75rem;">{{ m.price }}</span>
                                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                                    :class="m.tone === 'danger'  ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                                          : m.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                          : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                    {{ m.demand }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="fp-bar-track">
                                            <div class="fp-bar-fill"
                                                :style="{ width: `${m.pct}%`,
                                                          background: m.tone === 'danger' ? '#ef4444' : m.tone === 'success' ? '#004532' : '#f59e0b' }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. Activity Timeline -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Clock /></el-icon>
                                    Activity Timeline
                                </div>
                                <div class="fp-timeline">
                                    <div v-for="(act, i) in timeline" :key="i" class="fp-timeline-item">
                                        <div class="fp-timeline-dot" :class="`fp-timeline-dot--${act.tone}`">
                                            <el-icon><component :is="act.icon" /></el-icon>
                                        </div>
                                        <div class="fp-timeline-body">
                                            <div class="fp-timeline-text">{{ act.text }}</div>
                                            <div class="fp-timeline-time">{{ act.date }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="fp-rail">

                            <!-- Farmer attached to farm -->
                            <div class="fp-card" v-if="props.farm.farmer">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Star /></el-icon>
                                    Farm Owner
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="fp-farmer-avatar">
                                        {{ (props.farm.farmer.first_name?.[0] || '') + (props.farm.farmer.last_name?.[0] || '') || '?' }}
                                    </div>
                                    <div>
                                        <div class="fp-item-name">{{ farmerName }}</div>
                                        <div class="fp-td-muted" style="font-size:.75rem;">Single origin producer</div>
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mt-1 d-inline-block" style="font-size:.6rem;">Verified</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-1 mb-3">
                                    <div class="fp-farmer-row" v-if="props.farm.farmer.district || props.farm.farmer.sub_county">
                                        <el-icon style="font-size:12px; color:var(--on-surface-var); flex-shrink:0;"><Location /></el-icon>
                                        <span>{{ [props.farm.farmer.sub_county, props.farm.farmer.district].filter(Boolean).join(', ') }}</span>
                                    </div>
                                    <div class="fp-farmer-row" v-if="props.farm.farmer.telephone">
                                        <el-icon style="font-size:12px; color:var(--on-surface-var); flex-shrink:0;"><ChatDotRound /></el-icon>
                                        <span>{{ props.farm.farmer.telephone }}</span>
                                    </div>
                                    <div class="fp-farmer-row" v-if="props.farm.farmer.email">
                                        <el-icon style="font-size:12px; color:var(--on-surface-var); flex-shrink:0;"><Promotion /></el-icon>
                                        <span>{{ props.farm.farmer.email }}</span>
                                    </div>
                                </div>
                                <div class="row g-1 mb-3">
                                    <div class="col-6" v-if="props.farm.farmer.coffee_type">
                                        <div class="fp-spec-cell"><span>Coffee Type</span><strong>{{ props.farm.farmer.coffee_type }}</strong></div>
                                    </div>
                                    <div class="col-6" v-if="props.farm.farmer.farm_size">
                                        <div class="fp-spec-cell"><span>Farm Size</span><strong>{{ props.farm.farmer.farm_size }}</strong></div>
                                    </div>
                                    <div class="col-12" v-if="props.farm.farmer.cooperative">
                                        <div class="fp-spec-cell"><span>Cooperative</span><strong>{{ props.farm.farmer.cooperative }}</strong></div>
                                    </div>
                                </div>
                                <button class="btn fp-btn-outline btn-sm w-100"><el-icon><ShoppingCart /></el-icon> Contact Farmer</button>
                            </div>

                            <!-- 7. Farm Weather -->
                            <div class="fp-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fp-card-title mb-0">
                                        <el-icon class="fp-card-icon"><Sunny /></el-icon>
                                        Farm Weather
                                    </div>
                                    <span class="fp-weather-region"><el-icon><Location /></el-icon>{{ locationLabel }}</span>
                                </div>

                                <template v-if="weatherOutlook.length">
                                    <p class="fp-weather-intro">
                                        Planting-season outlook for {{ locationLabel }}.
                                        <template v-if="weatherRegion && !locationLabel.toLowerCase().includes(weatherRegion.split('(')[0].trim().toLowerCase())">
                                            Nearest regional data: {{ weatherRegion }}.
                                        </template>
                                    </p>
                                    <div class="fp-weather-list">
                                        <div v-for="month in weatherPreview" :key="month.id" class="fp-weather-row">
                                            <div class="fp-weather-row__icon" :style="weatherIconStyle(month)">
                                                <el-icon :size="16"><component :is="weatherIcon(month)" /></el-icon>
                                            </div>
                                            <div class="fp-weather-row__body">
                                                <div class="fp-weather-row__head">
                                                    <span class="fp-weather-row__month">{{ weatherMonthLabel(month) }}</span>
                                                    <span class="fp-weather-row__temp">{{ month.temperature_min }}°–{{ month.temperature_max }}°C</span>
                                                </div>
                                                <div class="fp-weather-row__meta">
                                                    {{ month.condition }}<template v-if="month.rainfall_mm !== null"> · {{ month.rainfall_mm }}mm rain</template><template v-if="month.humidity_percentage !== null"> · {{ month.humidity_percentage }}% humidity</template>
                                                </div>
                                                <p v-if="month.advisory" class="fp-weather-row__tip">{{ month.advisory }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <Link :href="route('farm.weather')" class="btn fp-btn-outline btn-sm w-100 mt-3">
                                        <el-icon><Sunny /></el-icon>
                                        {{ hasMoreWeather ? `View ${weatherOutlook.length - weatherPreviewCount} More Months` : 'View Full Forecast' }}
                                    </Link>
                                </template>
                                <p v-else class="fp-td-muted" style="font-size:.8125rem;">No seasonal outlook available yet for this farm's region.</p>
                            </div>

                            <!-- 8. Documents -->
                            <div class="fp-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fp-card-title mb-0">
                                        <el-icon class="fp-card-icon"><Files /></el-icon>
                                        Documents
                                    </div>
                                    <button v-if="props.canEdit" type="button" class="fp-doc-upload-btn" @click="openDocumentDialog">
                                        <el-icon><Upload /></el-icon> Upload
                                    </button>
                                </div>

                                <div v-if="documents.length" class="fp-doc-list">
                                    <div v-for="doc in documents" :key="doc.id" class="fp-doc-item">
                                        <div class="fp-doc-item__icon"><el-icon :size="16"><Document /></el-icon></div>
                                        <div class="fp-doc-item__body">
                                            <div class="fp-doc-item__title">{{ doc.title }}</div>
                                            <div class="fp-doc-item__meta">
                                                <span v-if="doc.document_type">{{ doc.document_type }} · </span>{{ formatFileSize(doc.file_size) }} · {{ formatDocDate(doc.created_at) }}
                                            </div>
                                        </div>
                                        <div class="fp-doc-item__actions">
                                            <a :href="doc.file_url" target="_blank" rel="noopener" class="fp-doc-action fp-doc-action--download" title="Download">
                                                <el-icon><Download /></el-icon>
                                            </a>
                                            <button v-if="props.canEdit" type="button" class="fp-doc-action fp-doc-action--delete" title="Delete" @click="openDeleteDocumentDialog(doc)">
                                                <el-icon><Delete /></el-icon>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="fp-doc-empty">
                                    <div class="fp-doc-empty__icon"><el-icon :size="20"><Files /></el-icon></div>
                                    <p>No documents uploaded yet.</p>
                                    <button v-if="props.canEdit" type="button" class="btn fp-btn-outline btn-sm" @click="openDocumentDialog">
                                        <el-icon><Upload /></el-icon> Upload Document
                                    </button>
                                </div>
                            </div>

                            <!-- 9. Sustainability & Practices -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Star /></el-icon>
                                    Sustainability &amp; Practices
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="fp-sus-badge">Sustainable</span>
                                    <span class="fp-sus-badge fp-sus-badge--amber">Organic Practices</span>
                                    <span class="fp-sus-badge fp-sus-badge--blue">Eco Certified</span>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="s in sustainability" :key="s.label" class="fp-sus-row">
                                        <el-icon style="font-size:13px; color:var(--green);"><component :is="s.icon" /></el-icon>
                                        <span class="fp-cert-label flex-fill">{{ s.label }}</span>
                                        <strong class="fp-up" style="font-size:.8125rem;">{{ s.value }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Market Readiness -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Van /></el-icon>
                                    Market Readiness
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><div class="fp-spec-cell"><span>Export Lots</span><strong class="fp-up">{{ lots.length }}</strong></div></div>
                                    <div class="col-6"><div class="fp-spec-cell"><span>Avg Price</span><strong>$4.60–5.20</strong></div></div>
                                    <div class="col-6"><div class="fp-spec-cell"><span>Buyer Interest</span><strong class="fp-up">High</strong></div></div>
                                    <div class="col-6"><div class="fp-spec-cell"><span>Active Contracts</span><strong>1</strong></div></div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn fp-btn-primary btn-sm"><el-icon><ShoppingCart /></el-icon> View Lots</button>
                                    <button class="btn fp-btn-outline btn-sm"><el-icon><DataLine /></el-icon> Sell Coffee</button>
                                </div>
                            </div>

                            <!-- 15. AI Farm Insights -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Opportunity /></el-icon>
                                    AI Farm Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="fp-insight-card" :class="`fp-insight-card--${ins.tone}`">
                                        <el-icon class="fp-insight-icon"><Opportunity /></el-icon>
                                        <p class="fp-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 16. Smart Alerts -->
                            <div class="fp-card">
                                <div class="fp-card-title mb-3">
                                    <el-icon class="fp-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="fp-alert-row">
                                        <span>{{ { harvestUpdates: 'Harvest Updates', qualityAlerts: 'Quality Alerts', buyerInterest: 'Buyer Interest Alerts', exportUpdates: 'Export Readiness Updates' }[key] }}</span>
                                        <button class="fp-toggle" :class="{ 'fp-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── Edit Farm modal (creator only) ─────────────────────── -->
            <el-dialog v-model="editDialogOpen" width="50%" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon">
                            <el-icon :size="18"><Edit /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Profile</div>
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
                            <label class="fp-field__label">Location</label>
                            <el-input v-model="editForm.location" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.location }" />
                            <InputError class="fp-field__error" :message="editForm.errors.location" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Altitude</label>
                            <el-input v-model="editForm.altitude" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.altitude }" />
                            <InputError class="fp-field__error" :message="editForm.errors.altitude" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Farm Size</label>
                            <el-input v-model="editForm.size" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.size }" />
                            <InputError class="fp-field__error" :message="editForm.errors.size" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Variety</label>
                            <el-select v-model="editForm.variety" placeholder="Select crop variety" clearable class="fp-field-input w-100" :class="{ 'fp-field-input--error': editForm.errors.variety }">
                                <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                            </el-select>
                            <InputError class="fp-field__error" :message="editForm.errors.variety" />
                        </div>
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Notes <span class="fp-field__optional">(optional)</span></label>
                        <el-input v-model="editForm.notes" type="textarea" :rows="3" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.notes" />
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="submit" form="edit-farm-form" class="btn fp-btn-primary btn-sm" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete Farm modal (creator only) ────────────────────── -->
            <el-dialog v-model="deleteDialogOpen" width="420px" align-center class="fp-modal fp-modal--danger">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon fp-modal__head-icon--danger">
                            <el-icon :size="18"><Delete /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Profile</div>
                            <div class="fp-modal__title">Delete Farm</div>
                        </div>
                    </div>
                </template>

                <div class="fp-modal__body">
                    <p class="fp-modal__confirm-text">
                        Are you sure you want to delete <strong>{{ farmName }}</strong>? This action cannot be undone.
                    </p>
                </div>

                <div class="fp-modal__footer">
                    <button type="button" class="btn fp-btn-outline btn-sm" @click="deleteDialogOpen = false">Cancel</button>
                    <button type="button" class="btn fp-btn-danger btn-sm" :disabled="deletingFarm" @click="deleteFarm">
                        {{ deletingFarm ? 'Deleting…' : 'Delete Farm' }}
                    </button>
                </div>
            </el-dialog>

            <!-- ── Edit Environmental Specs modal (creator only) ──────── -->
            <el-dialog v-model="specsDialogOpen" width="480px" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon">
                            <el-icon :size="18"><Edit /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Profile</div>
                            <div class="fp-modal__title">Edit Farm Specifications</div>
                        </div>
                    </div>
                </template>

                <form id="specs-form" class="fp-modal__body" @submit.prevent="submitSpecsForm">
                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Rainfall</label>
                            <el-input v-model="specsForm.rainfall" placeholder="e.g. 1,850 mm" class="fp-field-input" :class="{ 'fp-field-input--error': specsForm.errors.rainfall }" />
                            <InputError class="fp-field__error" :message="specsForm.errors.rainfall" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Temperature</label>
                            <el-input v-model="specsForm.temperature" placeholder="e.g. 18.5°C – 24.2°C" class="fp-field-input" :class="{ 'fp-field-input--error': specsForm.errors.temperature }" />
                            <InputError class="fp-field__error" :message="specsForm.errors.temperature" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Humidity</label>
                            <el-input v-model="specsForm.humidity" placeholder="e.g. 62%" class="fp-field-input" :class="{ 'fp-field-input--error': specsForm.errors.humidity }" />
                            <InputError class="fp-field__error" :message="specsForm.errors.humidity" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Soil Type</label>
                            <el-select v-model="specsForm.soil_type" placeholder="Select soil type" clearable class="fp-field-input w-100" :class="{ 'fp-field-input--error': specsForm.errors.soil_type }">
                                <el-option v-for="option in soilTypeOptions" :key="option.id" :label="option.name" :value="option.name" />
                            </el-select>
                            <InputError class="fp-field__error" :message="specsForm.errors.soil_type" />
                        </div>
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Climatic Zone</label>
                        <el-select v-model="specsForm.climatic_zone" placeholder="Select climatic zone" clearable class="fp-field-input w-100" :class="{ 'fp-field-input--error': specsForm.errors.climatic_zone }">
                            <el-option v-for="option in climaticZoneOptions" :key="option.id" :label="option.name" :value="option.name" />
                        </el-select>
                        <InputError class="fp-field__error" :message="specsForm.errors.climatic_zone" />
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="button" class="btn fp-btn-outline btn-sm" @click="specsDialogOpen = false">Cancel</button>
                        <button type="submit" form="specs-form" class="btn fp-btn-primary btn-sm" :disabled="specsForm.processing">
                            {{ specsForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Edit Farm Location modal (creator only) ─────────────── -->
            <el-dialog v-model="locationDialogOpen" width="480px" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon">
                            <el-icon :size="18"><Location /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Profile</div>
                            <div class="fp-modal__title">Edit Farm Location</div>
                        </div>
                    </div>
                </template>

                <form id="location-form" class="fp-modal__body" @submit.prevent="submitLocationForm">
                    <div class="fp-locate-row">
                        <button type="button" class="btn fp-btn-outline btn-sm" :disabled="geocoding" @click="locateFromAddress">
                            <el-icon><Location /></el-icon>
                            {{ geocoding ? 'Locating…' : 'Locate from farm address' }}
                        </button>
                        <span v-if="geocodeError" class="fp-locate-error">{{ geocodeError }}</span>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Latitude</label>
                            <el-input v-model="locationForm.latitude" placeholder="e.g. 0.3476" class="fp-field-input" :class="{ 'fp-field-input--error': locationForm.errors.latitude }" />
                            <InputError class="fp-field__error" :message="locationForm.errors.latitude" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Longitude</label>
                            <el-input v-model="locationForm.longitude" placeholder="e.g. 34.1162" class="fp-field-input" :class="{ 'fp-field-input--error': locationForm.errors.longitude }" />
                            <InputError class="fp-field__error" :message="locationForm.errors.longitude" />
                        </div>
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Altitude</label>
                        <el-input v-model="locationForm.altitude" placeholder="e.g. 1,950m" class="fp-field-input" :class="{ 'fp-field-input--error': locationForm.errors.altitude }" />
                        <InputError class="fp-field__error" :message="locationForm.errors.altitude" />
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="submit" form="location-form" class="btn fp-btn-primary btn-sm" :disabled="locationForm.processing">
                            {{ locationForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Add Harvest modal (creator only) ──────────────────── -->
            <el-dialog v-model="harvestDialogOpen" width="560px" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon">
                            <el-icon :size="18"><Box /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Profile</div>
                            <div class="fp-modal__title">Add Harvest</div>
                        </div>
                    </div>
                </template>

                <form id="harvest-form" novalidate class="fp-modal__body" @submit.prevent="submitHarvestForm">
                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Variety</label>
                            <el-select v-model="harvestForm.variety" placeholder="Select crop variety" clearable class="fp-field-input w-100" :class="{ 'fp-field-input--error': harvestForm.errors.variety }">
                                <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                            </el-select>
                            <InputError class="fp-field__error" :message="harvestForm.errors.variety" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Harvest Season</label>
                            <el-select v-model="harvestForm.harvest_season" placeholder="Select season" class="fp-field-input w-100" :class="{ 'fp-field-input--error': harvestForm.errors.harvest_season }">
                                <el-option v-for="option in harvestSeasonOptions" :key="option" :label="option" :value="option" />
                            </el-select>
                            <InputError class="fp-field__error" :message="harvestForm.errors.harvest_season" />
                        </div>
                    </div>

                    <div class="fp-field-row fp-field-row--spaced">
                        <div class="fp-field">
                            <label class="fp-field__label">Date Planted</label>
                            <el-date-picker v-model="harvestForm.date_planted" type="date" value-format="YYYY-MM-DD" placeholder="Select date" :disabled-date="disableFutureDates" class="fp-field-input w-100" :class="{ 'fp-field-input--error': harvestForm.errors.date_planted }" />
                            <InputError class="fp-field__error fp-field__error--spaced" :message="harvestForm.errors.date_planted" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Harvest Date</label>
                            <el-date-picker v-model="harvestForm.harvest_date" type="date" value-format="YYYY-MM-DD" placeholder="Select date" :disabled-date="disableFutureDates" class="fp-field-input w-100" :class="{ 'fp-field-input--error': harvestForm.errors.harvest_date }" />
                            <InputError class="fp-field__error fp-field__error--spaced" :message="harvestForm.errors.harvest_date" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Pick Method</label>
                            <el-select v-model="harvestForm.pick_method" placeholder="Select pick method" class="fp-field-input w-100" :class="{ 'fp-field-input--error': harvestForm.errors.pick_method }">
                                <el-option v-for="option in pickMethodOptions" :key="option" :label="option" :value="option" />
                            </el-select>
                            <InputError class="fp-field__error" :message="harvestForm.errors.pick_method" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Ripeness %</label>
                            <el-input-number v-model="harvestForm.ripeness_percentage" :min="0" :max="100" class="fp-field-input w-100" :class="{ 'fp-field-input--error': harvestForm.errors.ripeness_percentage }" />
                            <InputError class="fp-field__error" :message="harvestForm.errors.ripeness_percentage" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Weight (kg)</label>
                            <el-input-number v-model="harvestForm.weight" :min="0.01" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': harvestForm.errors.weight }" />
                            <InputError class="fp-field__error" :message="harvestForm.errors.weight" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Price (per kg)</label>
                            <el-input-number v-model="harvestForm.price" :min="0.01" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': harvestForm.errors.price }" />
                            <InputError class="fp-field__error" :message="harvestForm.errors.price" />
                        </div>
                    </div>

                    <div class="fp-harvest-flags">
                        <div class="fp-harvest-flag">
                            <span>Foreign matter present</span>
                            <el-switch v-model="harvestForm.foreign_matter_present" />
                        </div>
                        <div class="fp-harvest-flag">
                            <span>Pest damage</span>
                            <el-switch v-model="harvestForm.pest_damage" />
                        </div>
                        <div class="fp-harvest-flag">
                            <span>Disease signs</span>
                            <el-switch v-model="harvestForm.disease_signs" />
                        </div>
                        <div class="fp-harvest-flag">
                            <span>Visible defects</span>
                            <el-switch v-model="harvestForm.visible_defects" />
                        </div>
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="submit" form="harvest-form" class="btn fp-btn-primary btn-sm" :disabled="harvestForm.processing">
                            {{ harvestForm.processing ? 'Saving…' : 'Save Harvest' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete Harvest modal (creator only) ─────────────────── -->
            <el-dialog v-model="deleteHarvestDialogOpen" width="440px" align-center class="fp-modal fp-modal--danger">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon fp-modal__head-icon--danger">
                            <el-icon :size="18"><Delete /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Harvest History</div>
                            <div class="fp-modal__title">Delete Harvest</div>
                        </div>
                    </div>
                </template>

                <div v-if="harvestToDelete" class="fp-modal__body">
                    <p class="fp-modal__confirm-text">
                        Are you sure you want to delete this harvest record? This action cannot be undone.
                    </p>
                    <div class="fp-harvest-preview">
                        <div class="fp-harvest-preview__row"><span>Harvest ID</span><strong>{{ harvestToDelete.code }}</strong></div>
                        <div class="fp-harvest-preview__row"><span>Season</span><strong>{{ harvestToDelete.season }}</strong></div>
                        <div class="fp-harvest-preview__row"><span>Date</span><strong>{{ harvestToDelete.date }}</strong></div>
                        <div class="fp-harvest-preview__row"><span>Quantity</span><strong>{{ harvestToDelete.qty }}</strong></div>
                    </div>
                </div>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="button" class="btn fp-btn-outline btn-sm" @click="deleteHarvestDialogOpen = false">Cancel</button>
                        <button type="button" class="btn fp-btn-danger btn-sm" :disabled="deletingHarvest" @click="deleteHarvest">
                            {{ deletingHarvest ? 'Deleting…' : 'Delete Harvest' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── View Harvest modal ───────────────────────────────────── -->
            <el-dialog v-model="viewHarvestDialogOpen" width="560px" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon">
                            <el-icon :size="18"><Box /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Harvest History</div>
                            <div class="fp-modal__title">Harvest {{ harvestToViewCode }}</div>
                        </div>
                    </div>
                </template>

                <div v-if="harvestToView" class="fp-modal__body">
                    <div class="fp-view-section">
                        <div class="fp-view-section__title">Overview</div>
                        <div class="row g-2">
                            <div class="col-6"><div class="fp-spec-cell"><span>Variety</span><strong>{{ harvestToView.variety || '—' }}</strong></div></div>
                            <div class="col-6"><div class="fp-spec-cell"><span>Harvest Season</span><strong>{{ harvestToView.harvest_season || '—' }}</strong></div></div>
                            <div class="col-6"><div class="fp-spec-cell"><span>Pick Method</span><strong>{{ harvestToView.pick_method || '—' }}</strong></div></div>
                            <div class="col-6"><div class="fp-spec-cell"><span>Status</span><strong>{{ harvestToViewStatus }}</strong></div></div>
                        </div>
                    </div>

                    <div class="fp-view-section">
                        <div class="fp-view-section__title">Timeline</div>
                        <div class="row g-2">
                            <div class="col-6"><div class="fp-spec-cell"><span>Date Planted</span><strong>{{ harvestToView.date_planted || '—' }}</strong></div></div>
                            <div class="col-6"><div class="fp-spec-cell"><span>Harvest Date</span><strong>{{ harvestToView.harvest_date || '—' }}</strong></div></div>
                        </div>
                    </div>

                    <div class="fp-view-section">
                        <div class="fp-view-section__title">Yield &amp; Pricing</div>
                        <div class="row g-2">
                            <div class="col-4"><div class="fp-spec-cell"><span>Weight</span><strong>{{ harvestToView.weight !== null && harvestToView.weight !== undefined ? `${Number(harvestToView.weight).toLocaleString()} kg` : '—' }}</strong></div></div>
                            <div class="col-4"><div class="fp-spec-cell"><span>Price / kg</span><strong>{{ harvestToView.price !== null && harvestToView.price !== undefined ? `$${Number(harvestToView.price).toFixed(2)}` : '—' }}</strong></div></div>
                            <div class="col-4"><div class="fp-spec-cell"><span>Ripeness</span><strong>{{ harvestToView.ripeness_percentage !== null && harvestToView.ripeness_percentage !== undefined ? `${harvestToView.ripeness_percentage}%` : '—' }}</strong></div></div>
                        </div>
                    </div>

                    <div class="fp-view-section">
                        <div class="fp-view-section__title">Quality Flags</div>
                        <div class="fp-harvest-flags">
                            <div class="fp-harvest-flag">
                                <span>Foreign Matter</span>
                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                    :class="harvestToView.foreign_matter_present ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-success-subtle text-success-emphasis border border-success-subtle'">
                                    {{ harvestToView.foreign_matter_present ? 'Present' : 'None' }}
                                </span>
                            </div>
                            <div class="fp-harvest-flag">
                                <span>Pest Damage</span>
                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                    :class="harvestToView.pest_damage ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-success-subtle text-success-emphasis border border-success-subtle'">
                                    {{ harvestToView.pest_damage ? 'Present' : 'None' }}
                                </span>
                            </div>
                            <div class="fp-harvest-flag">
                                <span>Disease Signs</span>
                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                    :class="harvestToView.disease_signs ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-success-subtle text-success-emphasis border border-success-subtle'">
                                    {{ harvestToView.disease_signs ? 'Present' : 'None' }}
                                </span>
                            </div>
                            <div class="fp-harvest-flag">
                                <span>Visible Defects</span>
                                <span class="badge rounded-pill" style="font-size:.65rem;"
                                    :class="harvestToView.visible_defects ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : 'bg-success-subtle text-success-emphasis border border-success-subtle'">
                                    {{ harvestToView.visible_defects ? 'Present' : 'None' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="fp-view-meta">Recorded {{ harvestToView.created_at || '—' }}</div>
                </div>
            </el-dialog>

            <!-- ── Upload Document modal (creator only) ─────────────────── -->
            <el-dialog v-model="documentDialogOpen" width="480px" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon">
                            <el-icon :size="18"><Upload /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Profile</div>
                            <div class="fp-modal__title">Upload Document</div>
                        </div>
                    </div>
                </template>

                <form id="document-form" novalidate class="fp-modal__body" @submit.prevent="submitDocumentForm">
                    <div class="fp-field">
                        <label class="fp-field__label">Document Title</label>
                        <el-input v-model="documentForm.title" placeholder="e.g. Organic Certification 2026" class="fp-field-input" :class="{ 'fp-field-input--error': documentForm.errors.title }" />
                        <InputError class="fp-field__error" :message="documentForm.errors.title" />
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Document Type <span class="fp-field__optional">(optional)</span></label>
                        <el-select v-model="documentForm.document_type" placeholder="Select a type" clearable class="fp-field-input w-100" :class="{ 'fp-field-input--error': documentForm.errors.document_type }">
                            <el-option v-for="option in documentTypeOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <InputError class="fp-field__error" :message="documentForm.errors.document_type" />
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">File</label>
                        <label class="fp-doc-dropzone" :class="{ 'fp-doc-dropzone--error': documentForm.errors.document }">
                            <input type="file" class="fp-doc-dropzone__input" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx" @change="onDocumentFileChange">
                            <el-icon :size="20"><Upload /></el-icon>
                            <span v-if="documentFileName" class="fp-doc-dropzone__filename">{{ documentFileName }}</span>
                            <span v-else class="fp-doc-dropzone__hint">Click to choose a file — image, PDF, or Word document (max 10MB)</span>
                        </label>
                        <InputError class="fp-field__error" :message="documentForm.errors.document" />
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="submit" form="document-form" class="btn fp-btn-primary btn-sm" :disabled="documentForm.processing">
                            {{ documentForm.processing ? 'Uploading…' : 'Upload Document' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete Document modal (creator only) ──────────────────── -->
            <el-dialog v-model="deleteDocumentDialogOpen" width="420px" align-center class="fp-modal fp-modal--danger">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon fp-modal__head-icon--danger">
                            <el-icon :size="18"><Delete /></el-icon>
                        </div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Profile</div>
                            <div class="fp-modal__title">Delete Document</div>
                        </div>
                    </div>
                </template>

                <div v-if="documentToDelete" class="fp-modal__body">
                    <p class="fp-modal__confirm-text">
                        Are you sure you want to delete <strong>{{ documentToDelete.title }}</strong>? This action cannot be undone.
                    </p>
                </div>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="button" class="btn fp-btn-outline btn-sm" @click="deleteDocumentDialogOpen = false">Cancel</button>
                        <button type="button" class="btn fp-btn-danger btn-sm" :disabled="deletingDocument" @click="deleteDocument">
                            {{ deletingDocument ? 'Deleting…' : 'Delete Document' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.fp-page {
    --green:          #004532;
    --green-grad:     #065f46;
    --on-green:       #ffffff;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-white:  #ffffff;
    --surface-low:    #f8fafc;
    --surface-mid:    #f1f5f9;
    --surface-high:   #eef2f0;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
    line-height: 1.5;
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.fp-header      { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.fp-header-kicker { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--green); margin-bottom: 4px; line-height: 1; }
.fp-title       { font-size: 1.1875rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.25; }
.fp-subtitle    { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.5; margin-top: 2px; }
.fp-hbadge      { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fp-hbadge--soft { background: #dcfce7; color: #166534; }
.fp-hbadge--blue { background: #dbeafe; color: #1e40af; }
.fp-hbadge--amber{ background: #fef3c7; color: #92400e; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.fp-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.fp-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.fp-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fp-btn-outline:hover { background: var(--surface-low); }
.fp-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fp-btn-danger  { background: #dc2626; border-color: #dc2626; color: #fff; border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fp-btn-danger:hover { background: #b91c1c; border-color: #b91c1c; color: #fff; }
.fp-btn-danger:disabled { opacity: .6; cursor: not-allowed; }
.fp-btn-danger-ghost { background: #fef2f2; border-color: transparent; color: #dc2626; border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.fp-btn-danger-ghost:hover { background: #fee2e2; }
.fp-btn-group { border: 1px solid var(--surface-high); border-radius: 8px; overflow: hidden; }
.fp-btn-group :deep(.el-button) { border: none; border-radius: 0; }
.fp-btn-group :deep(.el-button + .el-button) { border-left: 1px solid var(--surface-high); }

/* ── Hero card ─────────────────────────────────────────────────────────────── */
.fp-hero-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.fp-hero-identity { padding: 1.25rem; border-right: 1px solid var(--surface-high); background: linear-gradient(180deg, #f0fdf4, #ffffff); display: flex; flex-direction: column; }
.fp-hero-specs, .fp-hero-perf { padding: 1.25rem; }
.fp-hero-specs { border-right: 1px solid var(--surface-high); }
.fp-farm-avatar-lg { width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(145deg, #1f2937, #0f172a); color: #d1fae5; font-weight: 800; font-size: 1.125rem; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
.fp-hero-name   { font-size: 1.125rem; font-weight: 800; color: var(--on-surface); line-height: 1.3; margin-bottom: 4px; }
.fp-hero-farmer { font-size: 0.8125rem; color: var(--on-surface-var); margin-bottom: 6px; }
.fp-contact-row { font-size: 0.75rem; color: var(--on-surface-var); margin-bottom: 3px; }
.fp-specs-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--on-surface-var); margin-bottom: 12px; }
.fp-specs-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
.fp-specs-edit-btn { width: 24px; height: 24px; border-radius: 6px; border: 1px solid var(--surface-high); background: var(--surface-white); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; flex-shrink: 0; transition: background 0.15s ease, color 0.15s ease; }
.fp-specs-edit-btn:hover { background: var(--surface-low); color: var(--green); }

/* ── Quality ring ──────────────────────────────────────────────────────────── */
.fp-quality-ring-wrap { display: flex; align-items: center; }
.fp-quality-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-quality-ring-inner { position: absolute; text-align: center; }
.fp-quality-score { font-size: 1.1875rem; font-weight: 800; color: var(--green); line-height: 1; font-variant-numeric: tabular-nums; }
.fp-quality-label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); margin-top: 2px; }
.fp-perf-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.4; }
.fp-perf-row:last-child { border-bottom: none; }
.fp-perf-row strong { color: var(--on-surface); font-weight: 700; font-variant-numeric: tabular-nums; }

/* ── Spec cell ─────────────────────────────────────────────────────────────── */
.fp-spec-cell { background: var(--surface-low); border-radius: 6px; padding: 7px 9px; }
.fp-spec-cell span   { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-var); display: block; margin-bottom: 3px; line-height: 1.3; }
.fp-spec-cell strong { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); display: block; line-height: 1.35; font-variant-numeric: tabular-nums; }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.fp-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 14px; padding: 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,.03), 0 1px 8px rgba(0,0,0,.03); transition: box-shadow 0.15s ease, border-color 0.15s ease; }
.fp-card:hover { border-color: #d1d5db; box-shadow: 0 2px 4px rgba(0,0,0,.03), 0 6px 18px rgba(0,0,0,.05); }
.fp-card-title { display: inline-flex; align-items: center; gap: 8px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); letter-spacing: -0.01em; line-height: 1.3; }
.fp-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Map ───────────────────────────────────────────────────────────────────── */
.fp-map-tile { position: relative; min-height: 180px; border-radius: 10px; overflow: hidden; background: linear-gradient(135deg, rgba(0,0,0,.04) 25%, transparent 25%) -12px 0/24px 24px, linear-gradient(225deg, rgba(0,0,0,.04) 25%, transparent 25%) -12px 0/24px 24px, linear-gradient(315deg, rgba(0,0,0,.04) 25%, transparent 25%) 0 0/24px 24px, linear-gradient(45deg, rgba(0,0,0,.04) 25%, transparent 25%) 0 0/24px 24px, #f6f7f7; border: 1px solid var(--surface-high); }
.fp-map-canvas { position: absolute; inset: 0; }
.fp-map-empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 1rem; text-align: center; }
.fp-map-coords { position: absolute; bottom: 0.75rem; left: 0.75rem; right: 0.75rem; background: rgba(255,255,255,.92); border-radius: 8px; padding: 8px 10px; }
.fp-map-badges { position: absolute; top: 0.75rem; right: 0.75rem; display: flex; flex-direction: column; gap: 4px; align-items: flex-end; }
.fp-map-badge { background: rgba(0,69,50,0.85); color: #fff; border-radius: 999px; font-size: 0.625rem; font-weight: 700; padding: 3px 8px; }
.fp-env-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.4; }
.fp-env-row:last-child { border-bottom: none; }
.fp-env-row strong { color: var(--on-surface); font-weight: 700; font-variant-numeric: tabular-nums; }

/* ── Production chart ──────────────────────────────────────────────────────── */
.fp-chart { height: 100px; display: flex; align-items: flex-end; background: var(--surface-low); border-radius: 8px; padding: 6px; }
.fp-chart-bars { display: flex; align-items: flex-end; gap: 2px; width: 100%; height: 100%; }
.fp-chart-col  { display: flex; flex-direction: column; align-items: center; gap: 2px; flex: 1; height: 100%; justify-content: flex-end; }
.fp-chart-bar  { width: 100%; border-radius: 2px 2px 0 0; }
.fp-chart-bar--prod { background: var(--green); }
.fp-chart-bar--qual { background: #059669; }
.fp-chart-lbl  { font-size: 0.5625rem; color: var(--on-surface-var); font-weight: 600; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.fp-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 10px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.fp-table tbody td { padding: 11px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; font-variant-numeric: tabular-nums; }
.fp-table-row { transition: background 0.1s; }
.fp-table-row:hover { background: var(--surface-low); }
.fp-item-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.4; }
.fp-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; line-height: 1.4; }
.fp-act-btn   { font-size: 0.75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.fp-score-pill { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 800; padding: 2px 8px; font-variant-numeric: tabular-nums; }
.fp-score-pill--high { background: #dcfce7; color: #166534; }
.fp-score-pill--mid  { background: #fef3c7; color: #92400e; }
.fp-up   { color: #166534; font-weight: 700; }
.fp-warn { color: #92400e; font-weight: 700; }

/* ── Table header icons (matches sibling el-table pattern) ───────────────── */
.fp-table-th { display: inline-flex; align-items: center; gap: 6px; }
.fp-table-th :deep(.el-icon) { font-size: 12px; color: #9ca3af; }

/* ── In-table empty state ─────────────────────────────────────────────────── */
.fp-table-empty { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 6px; padding: 2rem 1rem; }
.fp-table-empty__icon { width: 40px; height: 40px; border-radius: 12px; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; }
.fp-table-empty__title { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.fp-table-empty__text { font-size: 0.75rem; color: var(--on-surface-var); margin: 0; max-width: 320px; line-height: 1.5; }

/* ── Traceability ──────────────────────────────────────────────────────────── */
.fp-trace-flow { display: flex; justify-content: space-between; align-items: flex-start; position: relative; }
.fp-trace-stage { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; position: relative; z-index: 1; }
.fp-trace-dot { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.fp-trace-dot--done    { background: #dcfce7; color: #166534; }
.fp-trace-dot--pending { background: var(--surface-high); color: var(--on-surface-var); }
.fp-trace-label  { font-size: 0.6875rem; font-weight: 700; color: var(--on-surface); text-align: center; margin-top: 2px; }
.fp-trace-status { font-size: 0.6875rem; font-weight: 600; text-align: center; }
.fp-trace-date   { font-size: 0.625rem; color: var(--on-surface-var); text-align: center; }
.fp-trace-line   { position: absolute; top: 19px; left: 50%; width: 100%; height: 2px; background: var(--surface-high); z-index: 0; }
.fp-trace-line--done { background: #16a34a; }

/* ── Lot card ──────────────────────────────────────────────────────────────── */
.fp-lot-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; display: flex; flex-direction: column; }

/* ── Bars ──────────────────────────────────────────────────────────────────── */
.fp-bar-track { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.fp-bar-fill  { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

.fp-cert-label { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.4; }

/* ── Farm Weather card ─────────────────────────────────────────────────────── */
.fp-weather-region { display: inline-flex; align-items: center; gap: 4px; font-size: .6875rem; font-weight: 700; color: var(--on-surface-var); background: var(--surface-low); border-radius: 999px; padding: 3px 9px; flex-shrink: 0; }
.fp-weather-intro { font-size: .75rem; color: var(--on-surface-var); margin: 0 0 10px; line-height: 1.4; }
.fp-weather-list { display: flex; flex-direction: column; gap: 10px; }
.fp-weather-row { display: flex; gap: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--surface-low); }
.fp-weather-row:last-child { padding-bottom: 0; border-bottom: none; }
.fp-weather-row__icon { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-weather-row__body { flex: 1; min-width: 0; }
.fp-weather-row__head { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; }
.fp-weather-row__month { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.fp-weather-row__temp { font-size: .75rem; font-weight: 700; color: var(--on-surface); flex-shrink: 0; font-variant-numeric: tabular-nums; }
.fp-weather-row__meta { font-size: .6875rem; color: var(--on-surface-var); margin-top: 1px; }
.fp-weather-row__tip { font-size: .75rem; color: #374151; line-height: 1.4; margin: 4px 0 0; }

/* ── Farmer card ───────────────────────────────────────────────────────────── */
.fp-farmer-avatar { width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-farmer-row { display: flex; align-items: center; gap: 7px; font-size: 0.8125rem; color: var(--on-surface-var); padding: 4px 0; line-height: 1.4; }

/* ── Sustainability ────────────────────────────────────────────────────────── */
.fp-sus-badge { display: inline-flex; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.fp-sus-badge--amber { background: #fef3c7; color: #92400e; }
.fp-sus-badge--blue  { background: #dbeafe; color: #1e40af; }
.fp-sus-row { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.fp-sus-row:last-child { border-bottom: none; }

/* ── Timeline ──────────────────────────────────────────────────────────────── */
.fp-timeline { display: flex; flex-direction: column; }
.fp-timeline-item { display: flex; gap: 10px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--surface-low); }
.fp-timeline-item:last-child { border-bottom: none; }
.fp-timeline-dot { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
.fp-timeline-dot--success { background: #dcfce7; color: #166534; }
.fp-timeline-dot--primary { background: #dbeafe; color: #1d4ed8; }
.fp-timeline-dot--warning { background: #fef3c7; color: #92400e; }
.fp-timeline-dot--info    { background: #e0f2fe; color: #0369a1; }
.fp-timeline-dot--danger  { background: #fee2e2; color: #b91c1c; }
.fp-timeline-body { flex: 1; min-width: 0; }
.fp-timeline-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.4; }
.fp-timeline-time { font-size: 0.6875rem; color: var(--on-surface-var); margin-top: 2px; }

/* ── Documents ─────────────────────────────────────────────────────────────── */
.fp-doc-upload-btn { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border: none; border-radius: 7px; font-size: .75rem; font-weight: 700; padding: 5px 11px; cursor: pointer; transition: background .15s ease; flex-shrink: 0; }
.fp-doc-upload-btn:hover { background: rgba(0,69,50,0.15); }

.fp-doc-list { display: flex; flex-direction: column; }
.fp-doc-item { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--surface-low); }
.fp-doc-item:last-child { border-bottom: none; }
.fp-doc-item__icon { width: 32px; height: 32px; border-radius: 9px; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-doc-item__body { flex: 1; min-width: 0; }
.fp-doc-item__title { font-size: .8125rem; font-weight: 700; color: var(--on-surface); line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fp-doc-item__meta { font-size: .6875rem; color: var(--on-surface-var); margin-top: 1px; }
.fp-doc-item__actions { display: flex; align-items: center; gap: 2px; flex-shrink: 0; }
.fp-doc-action { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 7px; border: none; background: transparent; color: var(--on-surface-var); cursor: pointer; text-decoration: none; transition: background .15s ease, color .15s ease; }
.fp-doc-action--download:hover { background: rgba(0,69,50,0.08); color: var(--green); }
.fp-doc-action--delete:hover { background: #fef2f2; color: #dc2626; }

.fp-doc-empty { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px; padding: 1.5rem 0.5rem; color: var(--on-surface-var); }
.fp-doc-empty__icon { width: 44px; height: 44px; border-radius: 12px; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; margin-bottom: 2px; }
.fp-doc-empty p { font-size: .8125rem; margin: 0; }

.fp-doc-dropzone { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center; border: 1.5px dashed var(--surface-high); border-radius: 10px; padding: 1.25rem 1rem; cursor: pointer; color: var(--on-surface-var); transition: border-color .15s ease, background .15s ease; position: relative; }
.fp-doc-dropzone:hover { border-color: var(--green); background: rgba(0,69,50,0.03); }
.fp-doc-dropzone--error { border-color: #dc2626; }
.fp-doc-dropzone__input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.fp-doc-dropzone__hint { font-size: .75rem; line-height: 1.4; max-width: 260px; }
.fp-doc-dropzone__filename { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.fp-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.fp-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.fp-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.fp-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.fp-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.fp-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Alerts ────────────────────────────────────────────────────────────────── */
.fp-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 8px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; line-height: 1.4; }
.fp-alert-row:last-child { border-bottom: none; }
.fp-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.fp-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.fp-toggle--on { background: var(--green); }
.fp-toggle--on i { transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.fp-rail { display: flex; flex-direction: column; gap: 1.125rem; position: sticky; top: 100px; }

/* ── Edit modal — same design language as other dialogs in the app ───
   NOTE: <el-dialog> teleports to <body>, outside .fp-page, so CSS
   custom properties like var(--green) do not cascade in — literal hex
   values are used below on purpose. */
:deep(.el-dialog.fp-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, .22);
    font-family: 'Manrope', system-ui, sans-serif;
}
:deep(.el-dialog.fp-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.fp-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.fp-modal .el-dialog__footer) { padding: 0; }

.fp-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #f3f4f6; }
.fp-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(0,69,50,.08); color: #004532; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-modal__head-icon--danger { background: #fee2e2; color: #dc2626; }
.fp-modal__head-text { flex: 1; min-width: 0; }
.fp-modal__eyebrow { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #004532; margin-bottom: 1px; }
.fp-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -.01em; }

.fp-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }
.fp-modal__confirm-text { font-size: 0.875rem; color: #374151; line-height: 1.5; margin: 0; }

.fp-harvest-preview { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; background: #f3f4f6; border: 1px solid #f3f4f6; border-radius: 10px; overflow: hidden; margin-top: 14px; }
.fp-harvest-preview__row { background: #fff; padding: 9px 12px; display: flex; flex-direction: column; gap: 2px; }
.fp-harvest-preview__row span { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; }
.fp-harvest-preview__row strong { font-size: 0.8125rem; font-weight: 700; color: #111827; font-variant-numeric: tabular-nums; }

.fp-view-section { display: flex; flex-direction: column; gap: 8px; }
.fp-view-section__title { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; }
.fp-view-meta { font-size: 0.75rem; color: #9ca3af; text-align: center; padding-top: 4px; border-top: 1px solid #f3f4f6; }

.fp-locate-row { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; }
.fp-locate-error { font-size: .75rem; font-weight: 600; color: #dc2626; }
.fp-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.fp-field-row--spaced { margin-bottom: 10px; }
.fp-field { display: flex; flex-direction: column; gap: 5px; }
.fp-field__label { font-size: .75rem; font-weight: 600; color: #374151; }
.fp-field__optional { font-weight: 400; color: #9ca3af; }
.fp-field__error { font-size: .75rem; font-weight: 600; color: #dc2626; margin-top: 4px; display: block; }
.fp-field__error--spaced { margin-top: 8px; }
.fp-field__error--spaced :deep(p) { margin-top: 8px !important; }

.fp-harvest-flags { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 14px; padding: 12px; background: #f9fafb; border-radius: 10px; border: 1px solid #f3f4f6; }
.fp-harvest-flag { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: .8125rem; font-weight: 600; color: #374151; }

.fp-field-input--error :deep(.el-input__wrapper),
.fp-field-input--error :deep(.el-textarea__inner),
.fp-field-input--error :deep(.el-select__wrapper),
.fp-field-input--error.el-date-editor,
.fp-field-input--error:deep(.el-date-editor) { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

.fp-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .fp-rail { position: static; } }
@media (max-width: 991.98px)  { .fp-hero-identity, .fp-hero-specs { border-right: none; border-bottom: 1px solid var(--surface-high); } }
@media (max-width: 767.98px)  {
    .fp-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.fp-modal) { width: 92vw !important; }
}
</style>
