<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import { isGoogleMapsConfigured, renderMap } from '@/services/googleMaps';
import {
    Box, CaretRight, ChatDotRound, CircleCheckFilled, Coffee, Delete, Document, Download, Edit, Files,
    Location, MapLocation, Plus, Promotion, User,
    Sunny, PartlyCloudy, Cloudy, Umbrella, Lightning, Grid,
    Upload, Warning,
    InfoFilled, LocationFilled, HomeFilled, Aim, Top,
    Calendar, Medal, Money, Ticket, Memo,
} from '@element-plus/icons-vue';

const props = defineProps({
    farm: { type: Object, required: true },
    canEdit: { type: Boolean, default: false },
    varietyOptions: { type: Array, default: () => [] },
    cropVarietyOptions: { type: Array, default: () => [] },
    certificationOptions: { type: Array, default: () => [] },
    soilTypeOptions: { type: Array, default: () => [] },
    climaticZoneOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    weatherRegion: { type: String, default: null },
    weatherOutlook: { type: Array, default: () => [] },
    documents: { type: Array, default: () => [] },
    documentTypeOptions: { type: Array, default: () => [] },
    collections: { type: Array, default: () => [] },
    collectionUnitOptions: { type: Array, default: () => [] },
    collectionPaymentStatusOptions: { type: Array, default: () => [] },
});

/* ── Real display computed — every value below comes straight from a
   genuine Farm column; nothing here is invented to fill a layout slot. */
const farmName = computed(() => props.farm.name || 'Farm Profile');

const addressParts = computed(() => [
    props.farm.village, props.farm.parish, props.farm.subcounty,
    props.farm.county, props.farm.district, props.farm.region, props.farm.country,
].filter(Boolean));

const subtitle = computed(() => {
    const place = [props.farm.district, props.farm.country].filter(Boolean).join(', ');
    const type = props.farm.coffee_type ? `${props.farm.coffee_type} coffee` : 'Coffee';
    return place ? `A ${type} farm in ${place}.` : `A ${type} farm.`;
});

const hasCoordinates = computed(() =>
    props.farm.latitude !== null && props.farm.latitude !== undefined &&
    props.farm.longitude !== null && props.farm.longitude !== undefined,
);
const latitudeLabel = computed(() => hasCoordinates.value ? `${props.farm.latitude}°` : '—');
const longitudeLabel = computed(() => hasCoordinates.value ? `${props.farm.longitude}°` : '—');
const elevationLabel = computed(() => (props.farm.elevation !== null && props.farm.elevation !== undefined) ? `${props.farm.elevation}m` : '—');

const locationTrail = computed(() => [props.farm.country, props.farm.region, props.farm.district, props.farm.county].filter(Boolean).join(' › '));

const farmerName = computed(() => [props.farm.farmer?.first_name, props.farm.farmer?.last_name].filter(Boolean).join(' '));

/* ── Agronomy — real, linked via soil_metadata_id / climate_zone_metadata_id
   / the farm_crop_varieties & farm_certifications pivots. Composed into
   readable copy from the linked records' own real fields; nothing here
   is invented per-farm. ───────────────────────────────────────────────── */
const certificationList = computed(() => props.farm.certifications || []);

const waterConservationPercent = computed(() => Math.min(100, props.farm.water_conservation_percentage || 0));
const carbonSequestrationPercent = computed(() => Math.min(100, ((props.farm.carbon_sequestration || 0) / 20) * 100));
const soilHealthPercent = computed(() => Math.min(100, ((props.farm.soil_health_index || 0) / 5) * 100));

function gaugeStyle(percent, color) {
    return { background: `conic-gradient(${color} ${percent}%, var(--dp-surface-container-high) ${percent}% 100%)` };
}

/* ── Edit farm dialog (creator only) — every field here is a real Farm
   column, matching AddFarmDialog.vue's field set exactly. ────────────── */
const editDialogOpen = ref(false);

function emptyEditForm() {
    return {
        name: props.farm.name || '',
        coffee_type: props.farm.coffee_type || '',
        tel: props.farm.tel || '',
        email: props.farm.email || '',
        status: props.farm.status || 'active',
        country: props.farm.country || '',
        region: props.farm.region || '',
        district: props.farm.district || '',
        county: props.farm.county || '',
        subcounty: props.farm.subcounty || '',
        parish: props.farm.parish || '',
        village: props.farm.village || '',
        latitude: props.farm.latitude ?? '',
        longitude: props.farm.longitude ?? '',
        elevation: props.farm.elevation ?? '',
        total_area: props.farm.total_area ?? '',
        coffee_area: props.farm.coffee_area ?? '',
        soil_metadata_id: props.farm.soil_metadata_id ?? null,
        climate_zone_metadata_id: props.farm.climate_zone_metadata_id ?? null,
        water_conservation_percentage: props.farm.water_conservation_percentage ?? '',
        carbon_sequestration: props.farm.carbon_sequestration ?? '',
        soil_health_index: props.farm.soil_health_index ?? '',
        soil_type: props.farm.soil_type || '',
        crop_variety_ids: (props.farm.crop_varieties || []).map((v) => v.id),
        certification_ids: (props.farm.certifications || []).map((c) => c.id),
    };
}

const editForm = useForm(emptyEditForm());

function openEditDialog() {
    editForm.defaults(emptyEditForm());
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

/* ── Geocode from address — composed from the farm's own real address
   fields (the legacy single `location` string this used to read never
   existed as a Farm column). ─────────────────────────────────────────── */
const geocoding = ref(false);
const geocodeError = ref('');

async function locateFromAddress() {
    geocodeError.value = '';
    const address = addressParts.value.join(', ');

    if (!address) {
        geocodeError.value = 'This farm has no address fields on file to look up.';
        return;
    }

    geocoding.value = true;
    try {
        const { data } = await axios.post(route('farm.geocode'), { address });
        editForm.latitude = data.lat;
        editForm.longitude = data.lng;
    } catch (error) {
        geocodeError.value = error.response?.data?.message || 'Could not resolve coordinates for that address.';
    } finally {
        geocoding.value = false;
    }
}

/* ── Delete farm ───────────────────────────────────────────────────────── */
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

function disableFutureDates(date) {
    const today = new Date();
    today.setHours(23, 59, 59, 999);
    return date.getTime() > today.getTime();
}

/* ── Add / edit collection dialog (admin or creator) ───────────────────── */
const collectionDialogOpen = ref(false);
const editingCollectionId = ref(null);

const collectionForm = useForm({
    collection_date: '',
    coffee_type: '',
    variety: '',
    harvest_season: '',
    quantity: '',
    unit: 'kg',
    initial_moisture: '',
    initial_defects: '',
    initial_grade: '',
    initial_quality_score: '',
    collection_price: '',
    currency: 'USD',
    payment_status: 'pending',
    reference: '',
    notes: '',
});

const collectionDialogTitle = computed(() => editingCollectionId.value ? 'Edit Collection' : 'Add Collection');
const collectionSubmitLabel = computed(() => {
    if (collectionForm.processing) return 'Saving…';
    return editingCollectionId.value ? 'Update Collection' : 'Save Collection';
});

function openCollectionDialog() {
    collectionForm.reset();
    collectionForm.clearErrors();
    editingCollectionId.value = null;
    collectionForm.coffee_type = props.farm.coffee_type || '';
    collectionDialogOpen.value = true;
}

function openEditCollectionDialog(row) {
    const collection = props.collections.find((c) => String(c.id) === String(row.id)) || row;
    collectionForm.clearErrors();
    editingCollectionId.value = collection.id;
    collectionForm.collection_date = collection.collection_date || '';
    collectionForm.coffee_type = collection.coffee_type || '';
    collectionForm.variety = collection.variety || '';
    collectionForm.harvest_season = collection.harvest_season || '';
    collectionForm.quantity = collection.quantity ?? '';
    collectionForm.unit = collection.unit || 'kg';
    collectionForm.initial_moisture = collection.initial_moisture ?? '';
    collectionForm.initial_defects = collection.initial_defects ?? '';
    collectionForm.initial_grade = collection.initial_grade || '';
    collectionForm.initial_quality_score = collection.initial_quality_score ?? '';
    collectionForm.collection_price = collection.collection_price ?? '';
    collectionForm.currency = collection.currency || 'USD';
    collectionForm.payment_status = collection.payment_status || 'pending';
    collectionForm.reference = collection.reference || '';
    collectionForm.notes = collection.notes || '';
    viewCollectionDialogOpen.value = false;
    collectionDialogOpen.value = true;
}

function editCollectionFromView() {
    if (collectionToView.value) openEditCollectionDialog(collectionToView.value);
}

function submitCollectionForm() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            collectionDialogOpen.value = false;
            collectionForm.reset();
            editingCollectionId.value = null;
        },
    };

    if (editingCollectionId.value) {
        collectionForm.patch(route('farm.collections.update', [props.farm.id, editingCollectionId.value]), options);
    } else {
        collectionForm.post(route('farm.collections.store', props.farm.id), options);
    }
}

/* ── Delete collection dialog (admin or creator) ───────────────────────── */
const deleteCollectionDialogOpen = ref(false);
const deletingCollection = ref(false);
const collectionToDelete = ref(null);

function openDeleteCollectionDialog(row) {
    collectionToDelete.value = props.collections.find((c) => String(c.id) === String(row.id)) || row;
    viewCollectionDialogOpen.value = false;
    deleteCollectionDialogOpen.value = true;
}

function deleteCollection() {
    if (!collectionToDelete.value) return;
    deletingCollection.value = true;
    router.delete(route('farm.collections.destroy', [props.farm.id, collectionToDelete.value.id]), {
        preserveScroll: true,
        onFinish: () => {
            deletingCollection.value = false;
            deleteCollectionDialogOpen.value = false;
            collectionToDelete.value = null;
        },
    });
}

const collectionPaymentStatusTone = { pending: 'amber', partial: 'amber', paid: 'green', cancelled: 'muted' };

const collectionRows = computed(() => props.collections.map((c) => ({
    id: c.id,
    date: c.collection_date || '—',
    coffeeType: c.coffee_type || '—',
    variety: c.variety || '—',
    qty: (c.quantity !== null && c.quantity !== undefined) ? `${Number(c.quantity).toLocaleString()} ${c.unit || 'kg'}` : '—',
    grade: c.initial_grade || '—',
    paymentStatus: c.payment_status ? c.payment_status.charAt(0).toUpperCase() + c.payment_status.slice(1) : 'Pending',
    tone: collectionPaymentStatusTone[(c.payment_status || 'pending').toLowerCase()] || 'primary',
})));

/* ── View collection dialog ────────────────────────────────────────────── */
const viewCollectionDialogOpen = ref(false);
const collectionToView = ref(null);

function openViewCollectionDialog(row) {
    collectionToView.value = props.collections.find((c) => String(c.id) === String(row.id)) || null;
    viewCollectionDialogOpen.value = true;
}

const collectionToViewPaymentStatus = computed(() => {
    if (!collectionToView.value?.payment_status) return 'Pending';
    const s = collectionToView.value.payment_status;
    return s.charAt(0).toUpperCase() + s.slice(1);
});

const collectionToViewTone = computed(() => collectionPaymentStatusTone[(collectionToView.value?.payment_status || 'pending').toLowerCase()] || 'primary');

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

/* ── Google Map — real, driven by the farm's own lat/long. ───────────── */
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

/* ── Farm Weather — real, planting-season outlook for the matched
   region (see FarmController::show(), matched against farm.district). */
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
</script>

<template>
    <DesignPreviewLayout :title="farmName">
        <Head :title="farmName" />

        <div class="fp-page">
            <!-- ── Hero ──────────────────────────────────────────────────── -->
            <div class="fp-hero">
                <div class="fp-hero__text">
                    <h1 class="dp-display-md">{{ farmName }}</h1>
                    <p class="fp-subtitle">{{ subtitle }}</p>
                </div>
                <div v-if="canEdit" class="fp-hero__actions">
                    <button type="button" class="fp-btn fp-btn--outline" @click="openEditDialog">
                        <el-icon :size="15"><Edit /></el-icon> Edit Farm
                    </button>
                    <button type="button" class="fp-btn fp-btn--danger-outline" @click="deleteDialogOpen = true">
                        <el-icon :size="15"><Delete /></el-icon> Delete
                    </button>
                </div>
            </div>

            <div class="fp-stack">
                <!-- ── General Information + Location ───────────────────── -->
                <div class="fp-pair">
                    <div class="fp-card">
                        <div class="fp-card-head">
                            <h2 class="fp-card-title"><el-icon><InfoFilled /></el-icon> General Information</h2>
                        </div>

                        <div class="fp-info-header">
                            <div class="fp-info-avatar"><el-icon :size="22"><Coffee /></el-icon></div>
                            <div class="fp-info-header__body">
                                <div class="fp-info-header__name">{{ farm.name }}</div>
                                <div class="fp-info-header__meta">
                                    <span class="fp-mono">{{ farm.farm_code || '—' }}</span>
                                    <span v-if="farm.coffee_type" class="fp-info-header__dot">•</span>
                                    <span v-if="farm.coffee_type">{{ farm.coffee_type }}</span>
                                </div>
                            </div>
                            <strong class="fp-status-pill" :class="`fp-status-pill--${farm.status === 'inactive' ? 'muted' : 'green'}`">
                                {{ farm.status === 'inactive' ? 'Inactive' : 'Active' }}
                            </strong>
                        </div>

                        <div class="fp-info-rows">
                            <div class="fp-info-row">
                                <span class="fp-info-row__icon"><el-icon :size="14"><ChatDotRound /></el-icon></span>
                                <span class="fp-info-row__label">Phone</span>
                                <span class="fp-info-row__value">{{ farm.tel || '—' }}</span>
                            </div>
                            <div class="fp-info-row">
                                <span class="fp-info-row__icon"><el-icon :size="14"><Promotion /></el-icon></span>
                                <span class="fp-info-row__label">Email</span>
                                <span class="fp-info-row__value fp-truncate">{{ farm.email || '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="fp-card">
                        <div class="fp-card-head">
                            <h2 class="fp-card-title"><el-icon><Location /></el-icon> Location</h2>
                        </div>

                        <div class="fp-loc-header">
                            <div class="fp-loc-avatar"><el-icon :size="20"><LocationFilled /></el-icon></div>
                            <div class="fp-loc-header__body">
                                <div class="fp-loc-header__trail">{{ locationTrail || '—' }}</div>
                                <div class="fp-loc-header__sub">Administrative origin trail</div>
                            </div>
                        </div>

                        <div class="fp-loc-coords">
                            <span class="fp-loc-coords__icon"><el-icon :size="14"><Aim /></el-icon></span>
                            <span class="fp-loc-coords__label">Coordinates</span>
                            <span class="fp-loc-coords__value fp-mono">{{ latitudeLabel }}, {{ longitudeLabel }}</span>
                        </div>

                        <div class="fp-grid-2 fp-loc-grid">
                            <div class="fp-stat-cell"><span><el-icon :size="12"><Location /></el-icon> Subcounty</span><strong>{{ farm.subcounty || '—' }}</strong></div>
                            <div class="fp-stat-cell"><span><el-icon :size="12"><Location /></el-icon> Parish</span><strong>{{ farm.parish || '—' }}</strong></div>
                            <div class="fp-stat-cell fp-field--span2"><span><el-icon :size="12"><HomeFilled /></el-icon> Village</span><strong>{{ farm.village || '—' }}</strong></div>
                        </div>
                    </div>
                </div>

                <!-- ── Map + Technical Specs + Sustainability Metrics ────── -->
                <div class="fp-trio">
                    <div class="fp-card">
                        <div class="fp-card-head">
                            <h2 class="fp-card-title">Location &amp; Origin Map</h2>
                            <el-icon :size="18" class="fp-card-head-icon"><MapLocation /></el-icon>
                        </div>
                        <div class="fp-map-tile">
                            <template v-if="hasCoordinates && mapConfigured">
                                <div ref="mapEl" class="fp-map-canvas"></div>
                                <div v-if="!mapReady && !mapFailed" class="fp-map-empty">
                                    <span class="fp-muted">Loading map…</span>
                                </div>
                                <div v-if="mapFailed" class="fp-map-empty">
                                    <el-icon :size="20"><Warning /></el-icon>
                                    <span class="fp-muted">Map failed to load.</span>
                                </div>
                            </template>
                            <div v-else class="fp-map-empty">
                                <el-icon :size="20"><Location /></el-icon>
                                <span class="fp-muted fp-muted--center">
                                    {{ hasCoordinates ? 'Map unavailable — Google Maps is not configured.' : 'No coordinates set for this farm.' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="fp-card">
                        <div class="fp-card-head">
                            <h2 class="fp-card-title"><el-icon><Grid /></el-icon> Technical Specs</h2>
                        </div>

                        <div class="fp-spec-tiles">
                            <div class="fp-spec-tile">
                                <span class="fp-spec-tile__label">Total Area</span>
                                <div class="fp-spec-tile__value">
                                    <template v-if="farm.total_area !== null && farm.total_area !== undefined">{{ farm.total_area }}<span class="fp-spec-tile__unit">ha</span></template>
                                    <template v-else>—</template>
                                </div>
                            </div>
                            <div class="fp-spec-tile">
                                <span class="fp-spec-tile__label">Coffee Area</span>
                                <div class="fp-spec-tile__value">
                                    <template v-if="farm.coffee_area !== null && farm.coffee_area !== undefined">{{ farm.coffee_area }}<span class="fp-spec-tile__unit">ha</span></template>
                                    <template v-else>—</template>
                                </div>
                            </div>
                        </div>

                        <div class="fp-info-rows">
                            <div class="fp-info-row">
                                <span class="fp-info-row__icon"><el-icon :size="14"><Top /></el-icon></span>
                                <span class="fp-info-row__label">Elevation</span>
                                <span class="fp-info-row__value">{{ elevationLabel }}</span>
                            </div>
                            <div class="fp-info-row">
                                <span class="fp-info-row__icon"><el-icon :size="14"><Grid /></el-icon></span>
                                <span class="fp-info-row__label">Soil Type</span>
                                <span class="fp-info-row__value">{{ farm.soil?.name || '—' }}</span>
                            </div>
                            <div class="fp-info-row">
                                <span class="fp-info-row__icon"><el-icon :size="14"><PartlyCloudy /></el-icon></span>
                                <span class="fp-info-row__label">Climate</span>
                                <span class="fp-info-row__value">{{ farm.climate_zone?.name || '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="fp-card">
                        <div class="fp-card-head">
                            <h2 class="fp-card-title"><el-icon><CircleCheckFilled /></el-icon> Sustainability Metrics</h2>
                        </div>
                        <div class="fp-gauge-row">
                            <div class="fp-gauge">
                                <div class="fp-gauge__ring" :style="gaugeStyle(waterConservationPercent, 'var(--dp-secondary)')">
                                    <div class="fp-gauge__hole"><el-icon :size="16"><Umbrella /></el-icon></div>
                                </div>
                                <div class="fp-gauge__value">{{ farm.water_conservation_percentage !== null && farm.water_conservation_percentage !== undefined ? `${farm.water_conservation_percentage}%` : '—' }}</div>
                                <div class="fp-gauge__label">Water Conservation</div>
                            </div>
                            <div class="fp-gauge">
                                <div class="fp-gauge__ring" :style="gaugeStyle(carbonSequestrationPercent, 'var(--dp-surface-tint)')">
                                    <div class="fp-gauge__hole"><el-icon :size="16"><Cloudy /></el-icon></div>
                                </div>
                                <div class="fp-gauge__value">{{ farm.carbon_sequestration !== null && farm.carbon_sequestration !== undefined ? `${farm.carbon_sequestration}` : '—' }}<span v-if="farm.carbon_sequestration !== null && farm.carbon_sequestration !== undefined" class="fp-gauge__unit">tCO2e/ha</span></div>
                                <div class="fp-gauge__label">Carbon Sequestration</div>
                            </div>
                            <div class="fp-gauge">
                                <div class="fp-gauge__ring" :style="gaugeStyle(soilHealthPercent, 'var(--dp-secondary-fixed)')">
                                    <div class="fp-gauge__hole"><el-icon :size="16"><Grid /></el-icon></div>
                                </div>
                                <div class="fp-gauge__value">{{ farm.soil_health_index !== null && farm.soil_health_index !== undefined ? `${farm.soil_health_index}/5.0` : '—' }}</div>
                                <div class="fp-gauge__label">Soil Health</div>
                            </div>
                        </div>

                        <div class="fp-metric-footer">
                            <div>
                                <span class="fp-stat-cell__label">Certifications</span>
                                <div v-if="certificationList.length" class="fp-chip-row">
                                    <span v-for="cert in certificationList" :key="cert.id" class="fp-chip" :title="cert.description || ''">{{ cert.name }}</span>
                                </div>
                                <span v-else class="fp-muted">None recorded</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Farm Collections ──────────────────────────────────── -->
                <div class="fp-card fp-card--flush">
                    <div class="fp-card-head fp-card-head--padded">
                        <h2 class="fp-card-title"><el-icon><Coffee /></el-icon> Farm Collections</h2>
                        <button v-if="canEdit" type="button" class="fp-btn fp-btn--outline" @click="openCollectionDialog">
                            <el-icon :size="14"><Plus /></el-icon> Add Collection
                        </button>
                    </div>

                    <div v-if="collectionRows.length" class="table-responsive">
                        <table class="table align-middle mb-0 fp-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Coffee Type</th>
                                    <th>Variety</th>
                                    <th>Grade</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Payment</th>
                                    <th v-if="canEdit" class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in collectionRows" :key="c.id" class="fp-table-row" @click="openViewCollectionDialog(c)">
                                    <td class="fp-mono fp-table-strong">{{ c.date }}</td>
                                    <td class="fp-muted">{{ c.coffeeType }}</td>
                                    <td class="fp-muted">{{ c.variety }}</td>
                                    <td class="fp-muted">{{ c.grade }}</td>
                                    <td class="text-end fp-table-strong">{{ c.qty }}</td>
                                    <td class="text-end">
                                        <span class="fp-status-pill" :class="`fp-status-pill--${c.tone}`">{{ c.paymentStatus }}</span>
                                    </td>
                                    <td v-if="canEdit" class="text-end fp-table-actions">
                                        <button type="button" class="fp-icon-btn" title="Edit collection" @click.stop="openEditCollectionDialog(c)">
                                            <el-icon :size="14"><Edit /></el-icon>
                                        </button>
                                        <button type="button" class="fp-icon-btn fp-icon-btn--danger" title="Delete collection" @click.stop="openDeleteCollectionDialog(c)">
                                            <el-icon :size="14"><Delete /></el-icon>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="fp-empty">
                        <el-icon :size="20"><Coffee /></el-icon>
                        <p>No collections recorded yet for this farm.</p>
                    </div>
                </div>

                <!-- ── Farm Owner + Weather ──────────────────────────────── -->
                <div class="fp-pair">
                    <div v-if="farm.farmer" class="fp-card">
                        <div class="fp-card-head">
                            <h2 class="fp-card-title"><el-icon><User /></el-icon> Farm Owner</h2>
                        </div>

                        <div class="fp-owner">
                            <div class="fp-owner__avatar-wrap">
                                <div class="fp-owner__avatar">
                                    {{ (farm.farmer.first_name?.[0] || '') + (farm.farmer.last_name?.[0] || '') || '?' }}
                                </div>
                                <span v-if="farm.farmer.verification_status === 'verified'" class="fp-owner__badge">
                                    <el-icon :size="11"><CircleCheckFilled /></el-icon>
                                </span>
                            </div>
                            <div class="fp-owner__body">
                                <div class="fp-owner__name">{{ farmerName }}</div>
                                <span v-if="farm.farmer.verification_status" class="fp-status-pill" :class="`fp-status-pill--${farm.farmer.verification_status === 'verified' ? 'green' : 'amber'}`">
                                    {{ farm.farmer.verification_status }}
                                </span>
                            </div>
                        </div>

                        <div class="fp-owner__contacts">
                            <div v-if="farm.farmer.district || farm.farmer.subcounty" class="fp-owner__contact">
                                <span class="fp-owner__contact-icon"><el-icon :size="14"><Location /></el-icon></span>
                                <span>{{ [farm.farmer.subcounty, farm.farmer.district].filter(Boolean).join(', ') }}</span>
                            </div>
                            <a v-if="farm.farmer.tel" :href="`tel:${farm.farmer.tel}`" class="fp-owner__contact fp-owner__contact--link">
                                <span class="fp-owner__contact-icon"><el-icon :size="14"><ChatDotRound /></el-icon></span>
                                <span>{{ farm.farmer.tel }}</span>
                            </a>
                            <a v-if="farm.farmer.email" :href="`mailto:${farm.farmer.email}`" class="fp-owner__contact fp-owner__contact--link">
                                <span class="fp-owner__contact-icon"><el-icon :size="14"><Promotion /></el-icon></span>
                                <span class="fp-truncate">{{ farm.farmer.email }}</span>
                            </a>
                            <div v-if="farm.farmer.cooperative" class="fp-owner__contact">
                                <span class="fp-owner__contact-icon"><el-icon :size="14"><MapLocation /></el-icon></span>
                                <span>{{ farm.farmer.cooperative.name }}</span>
                            </div>
                        </div>

                        <Link :href="route('farmer.show', farm.farmer.id)" class="fp-owner__cta">
                            View Farmer Profile <el-icon :size="14"><CaretRight /></el-icon>
                        </Link>
                    </div>

                    <div class="fp-card">
                        <div class="fp-card-head">
                            <h2 class="fp-card-title">Farm Weather</h2>
                            <el-icon :size="18" class="fp-card-head-icon"><Sunny /></el-icon>
                        </div>
                        <template v-if="weatherOutlook.length">
                            <p class="fp-weather-intro">
                                Planting-season outlook<template v-if="weatherRegion"> for {{ weatherRegion }}</template>.
                            </p>
                            <div class="fp-weather-grid">
                                <div v-for="month in weatherPreview" :key="month.id" class="fp-weather-card">
                                    <div class="fp-weather-card__icon" :style="weatherIconStyle(month)">
                                        <el-icon :size="20"><component :is="weatherIcon(month)" /></el-icon>
                                    </div>
                                    <div class="fp-weather-card__month">{{ weatherMonthLabel(month) }}</div>
                                    <div class="fp-weather-card__temp">{{ month.temperature_min }}°<span>–</span>{{ month.temperature_max }}°</div>
                                    <div class="fp-weather-card__condition">{{ month.condition }}</div>
                                    <div v-if="month.rainfall_mm !== null || month.humidity_percentage !== null" class="fp-weather-card__meta">
                                        <span v-if="month.rainfall_mm !== null">{{ month.rainfall_mm }}mm</span>
                                        <span v-if="month.humidity_percentage !== null">{{ month.humidity_percentage }}% RH</span>
                                    </div>
                                    <p v-if="month.advisory" class="fp-weather-card__tip">{{ month.advisory }}</p>
                                </div>
                            </div>
                            <Link :href="route('farm.weather')" class="fp-btn fp-btn--outline fp-btn--block fp-mt">
                                <el-icon><Sunny /></el-icon>
                                {{ hasMoreWeather ? `View ${weatherOutlook.length - weatherPreviewCount} More Months` : 'View Full Forecast' }}
                            </Link>
                        </template>
                        <p v-else class="fp-muted">No seasonal outlook available yet for this farm's region.</p>
                    </div>
                </div>

                <!-- ── Documents ─────────────────────────────────────────── -->
                <div class="fp-card">
                    <div class="fp-card-head">
                        <h2 class="fp-card-title"><el-icon><Files /></el-icon> Documents</h2>
                        <button v-if="canEdit" type="button" class="fp-btn fp-btn--outline" @click="openDocumentDialog">
                            <el-icon :size="14"><Upload /></el-icon> Upload
                        </button>
                    </div>

                    <div v-if="documents.length" class="fp-doc-list">
                        <div v-for="doc in documents" :key="doc.id" class="fp-doc-item">
                            <div class="fp-doc-item__icon"><el-icon :size="16"><Document /></el-icon></div>
                            <div class="fp-doc-item__body">
                                <div class="fp-doc-item__title">{{ doc.title }}</div>
                                <div class="fp-muted">
                                    <span v-if="doc.document_type">{{ doc.document_type }} · </span>{{ formatFileSize(doc.file_size) }} · {{ formatDocDate(doc.created_at) }}
                                </div>
                            </div>
                            <div class="fp-doc-item__actions">
                                <a :href="doc.file_url" target="_blank" rel="noopener" class="fp-icon-btn" title="Download">
                                    <el-icon :size="14"><Download /></el-icon>
                                </a>
                                <button v-if="canEdit" type="button" class="fp-icon-btn fp-icon-btn--danger" title="Delete" @click="openDeleteDocumentDialog(doc)">
                                    <el-icon :size="14"><Delete /></el-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="fp-empty">
                        <el-icon :size="20"><Files /></el-icon>
                        <p>No documents uploaded yet.</p>
                        <button v-if="canEdit" type="button" class="fp-btn fp-btn--outline" @click="openDocumentDialog">
                            <el-icon :size="14"><Upload /></el-icon> Upload Document
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Edit Farm modal (creator only) ─────────────────────────── -->
        <el-dialog v-model="editDialogOpen" width="min(720px, calc(100vw - 2rem))" align-center destroy-on-close :close-on-click-modal="false" class="fp-modal">
            <template #header>
                <div class="fp-modal__head">
                    <div class="fp-modal__head-icon"><el-icon :size="18"><Edit /></el-icon></div>
                    <div class="fp-modal__head-text">
                        <div class="fp-modal__eyebrow">Farm Profile</div>
                        <div class="fp-modal__title">Edit Farm</div>
                    </div>
                </div>
            </template>

            <form id="edit-farm-form" class="fp-modal__body" @submit.prevent="submitEditFarm">
                <div class="fp-grid-2">
                    <div class="fp-field fp-field--span2">
                        <label class="fp-field__label">Farm Name <span class="fp-req">*</span></label>
                        <el-input v-model="editForm.name" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.name }" />
                        <InputError class="fp-field__error" :message="editForm.errors.name" />
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Coffee Type</label>
                        <el-input v-model="editForm.coffee_type" placeholder="e.g. Arabica" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.coffee_type" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Status</label>
                        <el-select v-model="editForm.status" class="fp-field-input w-100">
                            <el-option label="Active" value="active" />
                            <el-option label="Inactive" value="inactive" />
                        </el-select>
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Phone</label>
                        <el-input v-model="editForm.tel" placeholder="e.g. +256 700 000000" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.tel }" />
                        <InputError class="fp-field__error" :message="editForm.errors.tel" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Email</label>
                        <el-input v-model="editForm.email" placeholder="e.g. farm@example.com" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.email }" />
                        <InputError class="fp-field__error" :message="editForm.errors.email" />
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Country</label>
                        <el-input v-model="editForm.country" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.country" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Region</label>
                        <el-input v-model="editForm.region" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.region" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">District</label>
                        <el-input v-model="editForm.district" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.district" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">County</label>
                        <el-input v-model="editForm.county" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.county" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Sub-county</label>
                        <el-input v-model="editForm.subcounty" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.subcounty" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Parish</label>
                        <el-input v-model="editForm.parish" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.parish" />
                    </div>
                    <div class="fp-field fp-field--span2">
                        <label class="fp-field__label">Village</label>
                        <el-input v-model="editForm.village" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.village" />
                    </div>
                </div>

                <div class="fp-modal__section">
                    <div class="fp-modal__section-head">
                        <div class="fp-modal__section-title">Coordinates &amp; Size</div>
                        <button type="button" class="fp-btn fp-btn--outline" :disabled="geocoding" @click="locateFromAddress">
                            <el-icon :size="14"><Location /></el-icon> {{ geocoding ? 'Locating…' : 'Locate from address' }}
                        </button>
                    </div>
                    <span v-if="geocodeError" class="fp-field__error d-block mb-2">{{ geocodeError }}</span>

                    <div class="fp-grid-2">
                        <div class="fp-field">
                            <label class="fp-field__label">Latitude</label>
                            <el-input v-model="editForm.latitude" type="number" step="0.0000001" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.latitude }" />
                            <InputError class="fp-field__error" :message="editForm.errors.latitude" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Longitude</label>
                            <el-input v-model="editForm.longitude" type="number" step="0.0000001" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.longitude }" />
                            <InputError class="fp-field__error" :message="editForm.errors.longitude" />
                        </div>
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
                        <div class="fp-field fp-field--span2">
                            <label class="fp-field__label">Coffee Area (ha)</label>
                            <el-input v-model="editForm.coffee_area" type="number" min="0" step="0.01" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.coffee_area }" />
                            <InputError class="fp-field__error" :message="editForm.errors.coffee_area" />
                        </div>
                    </div>
                </div>

                <div class="fp-modal__section">
                    <div class="fp-modal__section-title">Agronomy</div>
                    <div class="fp-grid-2">
                        <div class="fp-field">
                            <label class="fp-field__label">Soil Type <span class="fp-field__optional">(from list)</span></label>
                            <el-select v-model="editForm.soil_metadata_id" placeholder="Select soil type" clearable class="fp-field-input w-100">
                                <el-option v-for="opt in soilTypeOptions" :key="opt.id" :label="opt.name" :value="opt.id" />
                            </el-select>
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Soil Type <span class="fp-field__optional">(free text)</span></label>
                            <el-input v-model="editForm.soil_type" placeholder="e.g. Volcanic Loam" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.soil_type }" />
                            <InputError class="fp-field__error" :message="editForm.errors.soil_type" />
                        </div>
                        <div class="fp-field fp-field--span2">
                            <label class="fp-field__label">Climate Zone</label>
                            <el-select v-model="editForm.climate_zone_metadata_id" placeholder="Select climate zone" clearable class="fp-field-input w-100">
                                <el-option v-for="opt in climaticZoneOptions" :key="opt.id" :label="opt.name" :value="opt.id" />
                            </el-select>
                        </div>
                        <div class="fp-field fp-field--span2">
                            <label class="fp-field__label">Varietals Grown</label>
                            <el-select v-model="editForm.crop_variety_ids" multiple placeholder="Select varieties" class="fp-field-input w-100">
                                <el-option v-for="opt in cropVarietyOptions" :key="opt.id" :label="opt.name" :value="opt.id" />
                            </el-select>
                        </div>
                        <div class="fp-field fp-field--span2">
                            <label class="fp-field__label">Certifications</label>
                            <el-select v-model="editForm.certification_ids" multiple placeholder="Select certifications" class="fp-field-input w-100">
                                <el-option v-for="opt in certificationOptions" :key="opt.id" :label="opt.name" :value="opt.id" />
                            </el-select>
                        </div>
                    </div>
                </div>

                <div class="fp-modal__section">
                    <div class="fp-modal__section-title">Sustainability &amp; Traceability</div>
                    <div class="fp-grid-3">
                        <div class="fp-field">
                            <label class="fp-field__label">Water Conservation %</label>
                            <el-input v-model="editForm.water_conservation_percentage" type="number" min="0" max="100" step="0.1" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.water_conservation_percentage }" />
                            <InputError class="fp-field__error" :message="editForm.errors.water_conservation_percentage" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Carbon Sequestration <span class="fp-field__optional">(tCO2e/ha)</span></label>
                            <el-input v-model="editForm.carbon_sequestration" type="number" min="0" step="0.1" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.carbon_sequestration }" />
                            <InputError class="fp-field__error" :message="editForm.errors.carbon_sequestration" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Soil Health Index <span class="fp-field__optional">(0–5)</span></label>
                            <el-input v-model="editForm.soil_health_index" type="number" min="0" max="5" step="0.1" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.soil_health_index }" />
                            <InputError class="fp-field__error" :message="editForm.errors.soil_health_index" />
                        </div>
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="fp-modal__footer">
                    <button type="button" class="fp-btn fp-btn--outline" @click="editDialogOpen = false">Cancel</button>
                    <button type="submit" form="edit-farm-form" class="fp-btn fp-btn--primary" :disabled="editForm.processing">
                        {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Delete Farm modal ────────────────────────────────────────── -->
        <el-dialog v-model="deleteDialogOpen" width="min(420px, calc(100vw - 2rem))" align-center class="fp-modal fp-modal--danger">
            <template #header>
                <div class="fp-modal__head">
                    <div class="fp-modal__head-icon fp-modal__head-icon--danger"><el-icon :size="18"><Delete /></el-icon></div>
                    <div class="fp-modal__head-text">
                        <div class="fp-modal__eyebrow">Farm Profile</div>
                        <div class="fp-modal__title">Delete Farm</div>
                    </div>
                </div>
            </template>
            <div class="fp-modal__body">
                <p class="fp-modal__confirm-text">Are you sure you want to delete <strong>{{ farmName }}</strong>? This action cannot be undone.</p>
            </div>
            <template #footer>
                <div class="fp-modal__footer">
                    <button type="button" class="fp-btn fp-btn--outline" @click="deleteDialogOpen = false">Cancel</button>
                    <button type="button" class="fp-btn fp-btn--danger" :disabled="deletingFarm" @click="deleteFarm">
                        {{ deletingFarm ? 'Deleting…' : 'Delete Farm' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Add Collection modal ─────────────────────────────────────── -->
        <el-dialog v-model="collectionDialogOpen" width="min(600px, calc(100vw - 2rem))" align-center destroy-on-close :close-on-click-modal="false" class="fp-modal">
            <template #header>
                <div class="fp-modal__head">
                    <div class="fp-modal__head-icon"><el-icon :size="18"><Coffee /></el-icon></div>
                    <div class="fp-modal__head-text">
                        <div class="fp-modal__eyebrow">Farm Profile</div>
                        <div class="fp-modal__title">{{ collectionDialogTitle }}</div>
                    </div>
                </div>
            </template>

            <form id="collection-form" novalidate class="fp-modal__body" @submit.prevent="submitCollectionForm">
                <div class="fp-grid-2">
                    <div class="fp-field">
                        <label class="fp-field__label">Collection Date</label>
                        <el-date-picker v-model="collectionForm.collection_date" type="date" value-format="YYYY-MM-DD" placeholder="Select date" :disabled-date="disableFutureDates" class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.collection_date }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.collection_date" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Coffee Type</label>
                        <el-input v-model="collectionForm.coffee_type" placeholder="e.g. Arabica" class="fp-field-input" :class="{ 'fp-field-input--error': collectionForm.errors.coffee_type }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.coffee_type" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Variety</label>
                        <el-select v-model="collectionForm.variety" placeholder="Select crop variety" clearable class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.variety }">
                            <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <InputError class="fp-field__error" :message="collectionForm.errors.variety" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Harvest Season</label>
                        <el-select v-model="collectionForm.harvest_season" placeholder="Select season" clearable class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.harvest_season }">
                            <el-option v-for="option in harvestSeasonOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <InputError class="fp-field__error" :message="collectionForm.errors.harvest_season" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Quantity</label>
                        <el-input-number v-model="collectionForm.quantity" :min="0.01" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.quantity }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.quantity" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Unit</label>
                        <el-select v-model="collectionForm.unit" placeholder="Select unit" class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.unit }">
                            <el-option v-for="option in collectionUnitOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <InputError class="fp-field__error" :message="collectionForm.errors.unit" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Initial Moisture %</label>
                        <el-input-number v-model="collectionForm.initial_moisture" :min="0" :max="100" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.initial_moisture }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.initial_moisture" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Initial Defects</label>
                        <el-input-number v-model="collectionForm.initial_defects" :min="0" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.initial_defects }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.initial_defects" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Initial Grade</label>
                        <el-input v-model="collectionForm.initial_grade" placeholder="e.g. Grade A" class="fp-field-input" :class="{ 'fp-field-input--error': collectionForm.errors.initial_grade }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.initial_grade" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Initial Quality Score</label>
                        <el-input-number v-model="collectionForm.initial_quality_score" :min="0" :max="100" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.initial_quality_score }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.initial_quality_score" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Collection Price</label>
                        <el-input-number v-model="collectionForm.collection_price" :min="0" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.collection_price }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.collection_price" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Currency</label>
                        <el-input v-model="collectionForm.currency" placeholder="USD" maxlength="3" class="fp-field-input" :class="{ 'fp-field-input--error': collectionForm.errors.currency }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.currency" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Payment Status</label>
                        <el-select v-model="collectionForm.payment_status" placeholder="Select status" class="fp-field-input w-100" :class="{ 'fp-field-input--error': collectionForm.errors.payment_status }">
                            <el-option v-for="option in collectionPaymentStatusOptions" :key="option" :label="option.charAt(0).toUpperCase() + option.slice(1)" :value="option" />
                        </el-select>
                        <InputError class="fp-field__error" :message="collectionForm.errors.payment_status" />
                    </div>
                    <div class="fp-field">
                        <label class="fp-field__label">Reference</label>
                        <el-input v-model="collectionForm.reference" placeholder="Payment / lot reference" class="fp-field-input" :class="{ 'fp-field-input--error': collectionForm.errors.reference }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.reference" />
                    </div>
                    <div class="fp-field fp-field--span2">
                        <label class="fp-field__label">Notes</label>
                        <el-input v-model="collectionForm.notes" type="textarea" :rows="3" class="fp-field-input" :class="{ 'fp-field-input--error': collectionForm.errors.notes }" />
                        <InputError class="fp-field__error" :message="collectionForm.errors.notes" />
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="fp-modal__footer">
                    <button type="button" class="fp-btn fp-btn--outline" @click="collectionDialogOpen = false">Cancel</button>
                    <button type="submit" form="collection-form" class="fp-btn fp-btn--primary" :disabled="collectionForm.processing">
                        {{ collectionSubmitLabel }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── View Collection modal ────────────────────────────────────── -->
        <el-dialog v-model="viewCollectionDialogOpen" width="min(560px, calc(100vw - 2rem))" align-center class="fp-modal">
            <template #header>
                <div class="fp-modal__head">
                    <div class="fp-modal__head-icon"><el-icon :size="18"><Coffee /></el-icon></div>
                    <div class="fp-modal__head-text">
                        <div class="fp-modal__eyebrow">Farm Collections</div>
                        <div class="fp-modal__title">{{ collectionToView?.coffee_type || 'Collection' }} · {{ collectionToView?.collection_date || '—' }}</div>
                    </div>
                </div>
            </template>

            <div v-if="collectionToView" class="fp-modal__body">
                <div class="fp-collection-hero">
                    <div class="fp-collection-hero__qty">
                        <span class="fp-collection-hero__label"><el-icon :size="12"><Box /></el-icon> Quantity Collected</span>
                        <div class="fp-collection-hero__value">
                            {{ collectionToView.quantity !== null && collectionToView.quantity !== undefined ? Number(collectionToView.quantity).toLocaleString() : '—' }}
                            <span class="fp-collection-hero__unit">{{ collectionToView.unit || 'kg' }}</span>
                        </div>
                    </div>
                    <span class="fp-status-pill" :class="`fp-status-pill--${collectionToViewTone}`">{{ collectionToViewPaymentStatus }}</span>
                </div>

                <div class="fp-view-section">
                    <div class="fp-view-section__title"><el-icon :size="12"><InfoFilled /></el-icon> Collection Details</div>
                    <div class="fp-grid-2">
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Coffee /></el-icon> Coffee Type</span><strong>{{ collectionToView.coffee_type || '—' }}</strong></div>
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Grid /></el-icon> Variety</span><strong>{{ collectionToView.variety || '—' }}</strong></div>
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Sunny /></el-icon> Harvest Season</span><strong>{{ collectionToView.harvest_season || '—' }}</strong></div>
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Calendar /></el-icon> Collection Date</span><strong>{{ collectionToView.collection_date || '—' }}</strong></div>
                    </div>
                </div>

                <div class="fp-view-section">
                    <div class="fp-view-section__title"><el-icon :size="12"><CircleCheckFilled /></el-icon> Quality Assessment</div>
                    <div class="fp-grid-3">
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Umbrella /></el-icon> Moisture</span><strong>{{ collectionToView.initial_moisture !== null && collectionToView.initial_moisture !== undefined ? `${collectionToView.initial_moisture}%` : '—' }}</strong></div>
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Warning /></el-icon> Defects</span><strong>{{ collectionToView.initial_defects !== null && collectionToView.initial_defects !== undefined ? collectionToView.initial_defects : '—' }}</strong></div>
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Medal /></el-icon> Grade</span><strong>{{ collectionToView.initial_grade || '—' }}</strong></div>
                        <div class="fp-stat-cell fp-field--span2"><span><el-icon :size="12"><CircleCheckFilled /></el-icon> Quality Score</span><strong>{{ collectionToView.initial_quality_score !== null && collectionToView.initial_quality_score !== undefined ? `${collectionToView.initial_quality_score} / 100` : '—' }}</strong></div>
                    </div>
                </div>

                <div class="fp-view-section">
                    <div class="fp-view-section__title"><el-icon :size="12"><Money /></el-icon> Payment</div>
                    <div class="fp-grid-2">
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Money /></el-icon> Collection Price</span><strong>{{ collectionToView.collection_price !== null && collectionToView.collection_price !== undefined ? `${collectionToView.currency || 'USD'} ${Number(collectionToView.collection_price).toLocaleString()}` : '—' }}</strong></div>
                        <div class="fp-stat-cell"><span><el-icon :size="12"><Ticket /></el-icon> Reference</span><strong>{{ collectionToView.reference || '—' }}</strong></div>
                    </div>
                </div>

                <div v-if="collectionToView.notes" class="fp-view-section">
                    <div class="fp-view-section__title"><el-icon :size="12"><Memo /></el-icon> Notes</div>
                    <p class="fp-collection-notes">{{ collectionToView.notes }}</p>
                </div>

                <p class="fp-muted">Recorded {{ formatDocDate(collectionToView.created_at) }}</p>
            </div>

            <template #footer>
                <div class="fp-modal__footer">
                    <button type="button" class="fp-btn fp-btn--outline" @click="viewCollectionDialogOpen = false">Close</button>
                    <button v-if="canEdit" type="button" class="fp-btn fp-btn--danger" @click="openDeleteCollectionDialog(collectionToView)">
                        <el-icon :size="14"><Delete /></el-icon> Delete
                    </button>
                    <button v-if="canEdit" type="button" class="fp-btn fp-btn--primary" @click="editCollectionFromView">
                        <el-icon :size="14"><Edit /></el-icon> Edit
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Delete Collection modal ──────────────────────────────────── -->
        <el-dialog v-model="deleteCollectionDialogOpen" width="min(440px, calc(100vw - 2rem))" align-center class="fp-modal fp-modal--danger">
            <template #header>
                <div class="fp-modal__head">
                    <div class="fp-modal__head-icon fp-modal__head-icon--danger"><el-icon :size="18"><Delete /></el-icon></div>
                    <div class="fp-modal__head-text">
                        <div class="fp-modal__eyebrow">Farm Collections</div>
                        <div class="fp-modal__title">Delete Collection</div>
                    </div>
                </div>
            </template>
            <div v-if="collectionToDelete" class="fp-modal__body">
                <p class="fp-modal__confirm-text">Are you sure you want to delete this collection record? This action cannot be undone.</p>
                <dl class="fp-preview">
                    <div class="fp-preview__row"><dt>Coffee Type</dt><dd>{{ collectionToDelete.coffee_type || '—' }}</dd></div>
                    <div class="fp-preview__row"><dt>Date</dt><dd>{{ collectionToDelete.collection_date || '—' }}</dd></div>
                    <div class="fp-preview__row"><dt>Quantity</dt><dd>{{ collectionToDelete.quantity !== null && collectionToDelete.quantity !== undefined ? `${Number(collectionToDelete.quantity).toLocaleString()} ${collectionToDelete.unit || 'kg'}` : '—' }}</dd></div>
                </dl>
            </div>
            <template #footer>
                <div class="fp-modal__footer">
                    <button type="button" class="fp-btn fp-btn--outline" @click="deleteCollectionDialogOpen = false">Cancel</button>
                    <button type="button" class="fp-btn fp-btn--danger" :disabled="deletingCollection" @click="deleteCollection">
                        {{ deletingCollection ? 'Deleting…' : 'Delete Collection' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Upload Document modal ────────────────────────────────────── -->
        <el-dialog v-model="documentDialogOpen" width="min(480px, calc(100vw - 2rem))" align-center destroy-on-close :close-on-click-modal="false" class="fp-modal">
            <template #header>
                <div class="fp-modal__head">
                    <div class="fp-modal__head-icon"><el-icon :size="18"><Upload /></el-icon></div>
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
                    <label class="fp-dropzone" :class="{ 'fp-dropzone--error': documentForm.errors.document }">
                        <input type="file" class="fp-dropzone__input" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx" @change="onDocumentFileChange">
                        <el-icon :size="20"><Upload /></el-icon>
                        <span v-if="documentFileName" class="fp-dropzone__filename">{{ documentFileName }}</span>
                        <span v-else class="fp-dropzone__hint">Click to choose a file — image, PDF, or Word document (max 10MB)</span>
                    </label>
                    <InputError class="fp-field__error" :message="documentForm.errors.document" />
                </div>
            </form>

            <template #footer>
                <div class="fp-modal__footer">
                    <button type="button" class="fp-btn fp-btn--outline" @click="documentDialogOpen = false">Cancel</button>
                    <button type="submit" form="document-form" class="fp-btn fp-btn--primary" :disabled="documentForm.processing">
                        {{ documentForm.processing ? 'Uploading…' : 'Upload Document' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Delete Document modal ────────────────────────────────────── -->
        <el-dialog v-model="deleteDocumentDialogOpen" width="min(420px, calc(100vw - 2rem))" align-center class="fp-modal fp-modal--danger">
            <template #header>
                <div class="fp-modal__head">
                    <div class="fp-modal__head-icon fp-modal__head-icon--danger"><el-icon :size="18"><Delete /></el-icon></div>
                    <div class="fp-modal__head-text">
                        <div class="fp-modal__eyebrow">Farm Profile</div>
                        <div class="fp-modal__title">Delete Document</div>
                    </div>
                </div>
            </template>
            <div v-if="documentToDelete" class="fp-modal__body">
                <p class="fp-modal__confirm-text">Are you sure you want to delete <strong>{{ documentToDelete.title }}</strong>? This action cannot be undone.</p>
            </div>
            <template #footer>
                <div class="fp-modal__footer">
                    <button type="button" class="fp-btn fp-btn--outline" @click="deleteDocumentDialogOpen = false">Cancel</button>
                    <button type="button" class="fp-btn fp-btn--danger" :disabled="deletingDocument" @click="deleteDocument">
                        {{ deletingDocument ? 'Deleting…' : 'Delete Document' }}
                    </button>
                </div>
            </template>
        </el-dialog>
    </DesignPreviewLayout>
</template>

<style scoped>
.fp-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
    font-family: var(--dp-font-sans);
}

/* ── Hero ────────────────────────────────────────────────────────────── */
.fp-hero { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
.fp-hero__text { max-width: 640px; }
.fp-hero__text h1 { color: var(--dp-primary); }
.fp-subtitle { font-size: 14px; line-height: 1.6; color: var(--dp-on-surface-variant); margin: 8px 0 0; }
.fp-hero__actions { display: flex; gap: 10px; flex-shrink: 0; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.fp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 36px;
    padding: 0 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}
.fp-btn--outline { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); box-shadow: var(--dp-card-shadow); }
.fp-btn--outline:hover { background: var(--dp-surface-container-low); }
.fp-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.fp-btn--primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); }
.fp-btn--primary:disabled { opacity: 0.6; cursor: default; transform: none; box-shadow: none; }
.fp-btn--danger { background: var(--dp-error); color: var(--dp-on-error); }
.fp-btn--danger:disabled { opacity: 0.6; cursor: default; }
.fp-btn--danger-outline { background: var(--dp-surface-container-lowest); color: var(--dp-error); box-shadow: var(--dp-card-shadow); }
.fp-btn--danger-outline:hover { background: var(--dp-error-container); }
.fp-btn--block { width: 100%; }
.fp-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }
.fp-mt { margin-top: 14px; }

.fp-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: none;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease;
}
.fp-icon-btn:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.fp-icon-btn--danger:hover { background: var(--dp-error-container); color: var(--dp-error); }
.fp-icon-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

/* ── Layout ──────────────────────────────────────────────────────────── */
.fp-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    padding: 22px;
}
.fp-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--dp-primary);
    margin: 0 0 16px;
}
.fp-card-title .el-icon { color: var(--dp-outline); font-size: 15px; }
.fp-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.fp-card-head .fp-card-title { margin-bottom: 0; }

.fp-muted { color: var(--dp-on-surface-variant); font-size: 12.5px; }
.fp-muted--center { text-align: center; }

/* ── Page layout ───────────────────────────────────────────────────────── */
.fp-stack { display: flex; flex-direction: column; gap: 20px; }
.fp-pair { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; align-items: stretch; }
.fp-pair > .fp-card { height: 100%; display: flex; flex-direction: column; }
.fp-trio { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; align-items: stretch; }
.fp-trio > .fp-card { height: 100%; display: flex; flex-direction: column; }
.fp-card-head-icon { color: var(--dp-outline); flex-shrink: 0; }
.fp-card-head--padded { padding: 22px 22px 0; margin-bottom: 8px; }
.fp-mono { font-family: var(--dp-font-mono); }
.fp-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; max-width: 100%; }
.fp-stat-cell__label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-outline); margin-bottom: 6px; }

/* ── Sustainability metrics ───────────────────────────────────────────── */
.fp-gauge-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.fp-gauge { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px; }
.fp-gauge__ring {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.fp-gauge__hole {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--dp-surface-container-lowest);
    color: var(--dp-outline);
    display: flex;
    align-items: center;
    justify-content: center;
}
.fp-gauge__value { font-size: 14px; font-weight: 800; color: var(--dp-on-surface); line-height: 1.2; }
.fp-gauge__unit { display: block; font-size: 9.5px; font-weight: 700; color: var(--dp-outline); text-transform: uppercase; letter-spacing: 0.04em; }
.fp-gauge__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--dp-outline); line-height: 1.3; }

.fp-metric-footer {
    margin-top: 18px;
    padding-top: 18px;
    border-top: 1px solid color-mix(in srgb, var(--dp-outline-variant) 25%, transparent);
}
.fp-chip-row { display: flex; flex-wrap: wrap; gap: 6px; }
.fp-chip {
    display: inline-flex;
    align-items: center;
    padding: 4px 11px;
    border-radius: 999px;
    background: var(--dp-surface-container-high);
    color: var(--dp-on-surface-variant);
    font-size: 11.5px;
    font-weight: 700;
}

/* ── Flush-padding table card (Farm Collections) ──────────────────────── */
.fp-card--flush { padding: 0; overflow: hidden; }
.fp-table thead th {
    background: var(--dp-surface-container-low);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--dp-on-surface-variant);
    padding: 12px 22px;
    border-bottom-color: transparent;
    white-space: nowrap;
}
.fp-table tbody td { padding: 14px 22px; font-size: 13px; border-color: color-mix(in srgb, var(--dp-outline-variant) 25%, transparent); vertical-align: middle; }
.fp-table-row { cursor: pointer; transition: background 0.12s ease; }
.fp-table-row:hover { background: var(--dp-surface-container-low); }
.fp-table-row:last-child td { border-bottom: none; }
.fp-table-strong { font-weight: 700; color: var(--dp-on-surface); }

/* ── Farm owner card ─────────────────────────────────────────────────── */
.fp-owner { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
.fp-owner__avatar-wrap { position: relative; flex-shrink: 0; }
.fp-owner__avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--dp-secondary-container), var(--dp-secondary-fixed));
    color: var(--dp-on-secondary-container);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 800;
}
.fp-owner__badge {
    position: absolute;
    right: -2px;
    bottom: -2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--dp-secondary);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--dp-surface-container-lowest);
}
.fp-owner__name { font-size: 15px; font-weight: 700; color: var(--dp-on-surface); margin-bottom: 5px; }

.fp-owner__contacts { display: flex; flex-direction: column; gap: 4px; margin-bottom: 18px; }
.fp-owner__contact {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 6px;
    border-radius: 9px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--dp-on-surface-variant);
    text-decoration: none;
    transition: background 0.12s ease;
}
.fp-owner__contact span:last-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fp-owner__contact--link:hover { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.fp-owner__contact-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    flex-shrink: 0;
}

.fp-owner__cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    height: 38px;
    margin-top: auto;
    border-radius: 999px;
    background: var(--dp-surface-container-low);
    color: var(--dp-primary);
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s ease, gap 0.15s ease;
}
.fp-owner__cta:hover { background: var(--dp-surface-container-high); gap: 9px; }
.fp-owner__cta:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

/* ── General information card ────────────────────────────────────────── */
.fp-info-header { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
.fp-info-avatar {
    width: 46px;
    height: 46px;
    border-radius: 13px;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.fp-info-header__body { min-width: 0; flex: 1; }
.fp-info-header__name { font-size: 16px; font-weight: 800; color: var(--dp-on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fp-info-header__meta { display: flex; align-items: center; gap: 6px; margin-top: 3px; font-size: 12px; font-weight: 600; color: var(--dp-on-surface-variant); }
.fp-info-header__meta .fp-mono { color: var(--dp-outline); }
.fp-info-header__dot { color: var(--dp-outline); }

.fp-info-rows { display: flex; flex-direction: column; gap: 4px; }
.fp-info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 6px;
    border-radius: 9px;
    transition: background 0.12s ease;
}
.fp-info-row:hover { background: var(--dp-surface-container-low); }
.fp-info-row__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    flex-shrink: 0;
}
.fp-info-row__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dp-outline); width: 64px; flex-shrink: 0; }
.fp-info-row__value { font-size: 13.5px; font-weight: 700; color: var(--dp-on-surface); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }

/* ── Location card ───────────────────────────────────────────────────── */
.fp-loc-header { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
.fp-loc-avatar {
    width: 46px;
    height: 46px;
    border-radius: 13px;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.fp-loc-header__body { min-width: 0; flex: 1; }
.fp-loc-header__trail { font-size: 14px; font-weight: 800; color: var(--dp-on-surface); line-height: 1.35; overflow-wrap: break-word; }
.fp-loc-header__sub { font-size: 11px; font-weight: 600; color: var(--dp-outline); margin-top: 2px; }

.fp-loc-coords {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 12px;
    margin-bottom: 16px;
    border-radius: 10px;
    background: var(--dp-surface-container-low);
}
.fp-loc-coords__icon { display: inline-flex; color: var(--dp-outline); flex-shrink: 0; }
.fp-loc-coords__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dp-outline); }
.fp-loc-coords__value { font-size: 12.5px; font-weight: 700; color: var(--dp-on-surface); margin-left: auto; }

.fp-loc-grid { margin-top: auto; }

/* ── Technical specs card ────────────────────────────────────────────── */
.fp-spec-tiles { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
.fp-spec-tile { padding: 14px 16px; border-radius: 6px; background: var(--dp-surface-container-low); }
.fp-spec-tile__label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-outline); margin-bottom: 6px; }
.fp-spec-tile__value { font-size: 22px; font-weight: 800; color: var(--dp-on-surface); line-height: 1; }
.fp-spec-tile__unit { font-size: 12px; font-weight: 700; color: var(--dp-outline); margin-left: 3px; }

/* ── Status pill ─────────────────────────────────────────────────────── */
.fp-status-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: capitalize;
    white-space: nowrap;
}
.fp-status-pill--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.fp-status-pill--amber { background: #fef3c7; color: #92400e; }
.fp-status-pill--primary { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.fp-status-pill--danger { background: var(--dp-error-container); color: var(--dp-on-error-container); }
.fp-status-pill--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

/* ── Stat row / cells ────────────────────────────────────────────────── */
.fp-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 16px; }
.fp-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px 16px; }
.fp-field--span2 { grid-column: span 2; }
.fp-stat-cell { display: flex; flex-direction: column; gap: 4px; }
.fp-stat-cell span { display: inline-flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-outline); }
.fp-stat-cell span .el-icon { color: var(--dp-primary); opacity: 0.6; flex-shrink: 0; }
.fp-stat-cell strong { font-size: 13.5px; font-weight: 700; color: var(--dp-on-surface); }

/* ── Map ─────────────────────────────────────────────────────────────── */
.fp-map-tile {
    position: relative;
    width: 100%;
    height: 220px;
    border-radius: 6px;
    overflow: hidden;
    background: var(--dp-surface-container-low);
}
.fp-map-canvas { width: 100%; height: 100%; }
.fp-map-empty {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px;
    color: var(--dp-outline);
}
.fp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 30px 16px;
    text-align: center;
    color: var(--dp-outline);
}
.fp-empty p { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; }

/* ── Weather ─────────────────────────────────────────────────────────── */
.fp-weather-intro { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0 0 14px; }
.fp-weather-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; align-items: stretch; }
.fp-weather-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 5px;
    padding: 16px 10px;
    border-radius: 6px;
    background: var(--dp-surface-container-low);
    transition: background 0.15s ease;
}
.fp-weather-card:hover { background: var(--dp-surface-container-high); }
.fp-weather-card__icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 2px;
}
.fp-weather-card__month { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--dp-on-surface-variant); }
.fp-weather-card__temp { font-size: 19px; font-weight: 800; color: var(--dp-primary); font-family: var(--dp-font-mono); }
.fp-weather-card__temp span { color: var(--dp-outline); font-weight: 600; font-size: 13px; margin: 0 1px; }
.fp-weather-card__condition { font-size: 12px; font-weight: 600; color: var(--dp-on-surface); }
.fp-weather-card__meta { display: flex; gap: 8px; font-size: 10.5px; color: var(--dp-outline); font-family: var(--dp-font-mono); }
.fp-weather-card__tip { font-size: 11px; color: var(--dp-on-surface-variant); font-style: italic; line-height: 1.4; margin: 2px 0 0; }

/* ── Documents ───────────────────────────────────────────────────────── */
.fp-doc-list { display: flex; flex-direction: column; }
.fp-doc-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid color-mix(in srgb, var(--dp-outline-variant) 25%, transparent); }
.fp-doc-item:last-child { border-bottom: none; }
.fp-doc-item__icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.fp-doc-item__body { flex: 1; min-width: 0; }
.fp-doc-item__title { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fp-doc-item__actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

.fp-table-actions { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

/* ── Reduced motion ──────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .fp-btn,
    .fp-icon-btn,
    .fp-table-row { transition: none; }
}

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1200px) {
    .fp-trio { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 900px) {
    .fp-pair { grid-template-columns: 1fr; }
    .fp-trio { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .fp-card { padding: 18px; }
    .fp-card-head--padded { padding: 18px 18px 0; }
    .fp-table thead th,
    .fp-table tbody td { padding: 12px 18px; }
    .fp-grid-2 { grid-template-columns: 1fr; }
    .fp-field--span2 { grid-column: span 1; }
    .fp-hero { align-items: flex-start; }
    .fp-hero__actions { width: 100%; }
    .fp-hero__actions .fp-btn { flex: 1; }
    .fp-weather-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .fp-weather-card { padding: 12px 6px; }
}

/* ── Modals — el-dialog teleports to <body>, outside .dp-shell, so
   --dp-* custom properties don't cascade in; literal hex from the same
   palette is used here, matching this app's other teleported dialogs. */
</style>

<style>
.el-dialog.fp-modal { border-radius: 18px; padding: 0; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
.el-dialog.fp-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.fp-modal .el-dialog__body { padding: 0; }
.el-dialog.fp-modal .el-dialog__footer { padding: 0; }

.fp-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #f3f4f6; }
.fp-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(39, 19, 16, 0.08); color: #271310; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-modal__head-icon--danger { background: #fee2e2; color: #b91c1c; }
.fp-modal__head-text { flex: 1; min-width: 0; }
.fp-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #271310; margin-bottom: 1px; }
.fp-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -0.01em; }

.fp-modal__body { padding: 22px 24px 6px; max-height: 70vh; overflow-y: auto; }
.fp-modal__confirm-text { font-size: 0.875rem; color: #374151; line-height: 1.6; margin: 0 0 4px; }
.fp-modal__section { margin-top: 20px; padding-top: 18px; border-top: 1px solid #f3f4f6; }
.fp-modal__section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.fp-modal__section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; }

.fp-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.fp-field__label { font-size: 0.8125rem; font-weight: 700; color: #111827; }
.fp-field__optional { color: #9ca3af; font-weight: 500; }
.fp-req { color: #dc2626; }
.fp-field__error { font-size: 0.75rem; font-weight: 600; color: #dc2626; line-height: 1.4; }
.fp-field-input { width: 100%; }
.fp-field-input--error :deep(.el-input__wrapper),
.fp-field-input--error :deep(.el-select__wrapper),
.fp-field-input--error.el-input .el-input__wrapper { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

.fp-preview { margin: 14px 0 0; display: flex; flex-direction: column; gap: 8px; padding: 14px; border-radius: 10px; background: #f9fafb; }
.fp-preview__row { display: flex; align-items: center; justify-content: space-between; font-size: 0.8125rem; }
.fp-preview__row dt { color: #6b7280; margin: 0; }
.fp-preview__row dd { color: #111827; font-weight: 700; margin: 0; }

/* ── View Collection modal ──────────────────────────────────────────────── */
.fp-view-section { margin-bottom: 18px; }
.fp-view-section:last-of-type { margin-bottom: 8px; }
.fp-view-section__title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6b7280;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f3f4f6;
}
.fp-view-section__title .el-icon { color: #9ca3af; }

.fp-collection-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 18px;
    margin-bottom: 20px;
    border-radius: 12px;
    background: #f9fafb;
}
.fp-collection-hero__label { display: inline-flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; margin-bottom: 4px; }
.fp-collection-hero__label .el-icon { color: #9ca3af; }
.fp-collection-hero__value { font-size: 26px; font-weight: 800; color: #111827; line-height: 1; }
.fp-collection-hero__unit { font-size: 13px; font-weight: 700; color: #6b7280; margin-left: 4px; }
.fp-collection-notes { margin: 0; padding: 12px 14px; border-radius: 10px; background: #f9fafb; font-size: 13px; line-height: 1.55; color: #374151; white-space: pre-wrap; }


.fp-dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 24px 16px;
    border: 1.5px dashed #d1d5db;
    border-radius: 12px;
    background: #f9fafb;
    color: #6b7280;
    cursor: pointer;
    text-align: center;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.fp-dropzone:hover { border-color: #271310; background: #f3f4f6; }
.fp-dropzone--error { border-color: #dc2626; }
.fp-dropzone__input { display: none; }
.fp-dropzone__hint { font-size: 0.75rem; }
.fp-dropzone__filename { font-size: 0.8125rem; font-weight: 700; color: #111827; }

.fp-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

@media (max-width: 640px) {
    .fp-grid-2,
    .fp-grid-3 { grid-template-columns: 1fr; }
    .fp-field--span2 { grid-column: span 1; }
    .fp-info-row { flex-wrap: wrap; }
    .fp-info-row__label { width: auto; }
    .fp-info-row__value { flex-basis: 100%; }
}
</style>
