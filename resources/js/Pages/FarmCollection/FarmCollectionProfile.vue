<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import MainLayout from '@/Layouts/MainLayout.vue';
import EditFarmCollectionModal from '@/Components/Modals/EditFarmCollectionModal.vue';
import AddFarmCollectionActivityModal from '@/Components/Modals/AddFarmCollectionActivityModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { isGoogleMapsConfigured, renderMap } from '@/services/googleMaps';
import {
    Aim, ArrowDown, ArrowRight, Box, CircleCheck, CircleCheckFilled, Clock, Cloudy,
    Coffee, Coin, Delete, Document, EditPen, Files, Grid, Location, LocationFilled, MapLocation,
    Medal, Message, OfficeBuilding, Phone, PriceTag, Ticket, Umbrella, User, Warning, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    collection: { type: Object, required: true },
    custodyChain: { type: Object, default: () => ({ batch: null, lot: null, tokenised: null }) },
    farmOwner: { type: Object, default: null },
    coffeeTypeOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    activityOptions: { type: Array, default: () => [] },
    sustainabilityPractices: { type: Array, default: () => [] },
    sustainabilityPracticeOptions: { type: Array, default: () => [] },
});

const editDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const deleting = ref(false);
const addActivityOpen = ref(false);
const deleteActivityDialogOpen = ref(false);
const pendingActivity = ref(null);
const deletingActivity = ref(false);

function handleActionCommand(command) {
    if (command === 'add-activity') addActivityOpen.value = true;
    else if (command === 'delete') deleteDialogOpen.value = true;
}

/* ── Farm Collection Activity — event slugs are resolved to their
   metadata display name; anything not found (a retired slug) falls back
   to a titleized version of the slug itself rather than disappearing. ── */
function eventLabel(slug) {
    const match = props.activityOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/* ── Same resolution pattern for a sustainability practice's slug. ────── */
function practiceLabel(slug) {
    const match = props.sustainabilityPracticeOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function requestDeleteActivity(activity) {
    pendingActivity.value = activity;
    deleteActivityDialogOpen.value = true;
}

function confirmDeleteActivity() {
    if (!pendingActivity.value) return;
    deletingActivity.value = true;
    router.delete(route('farm-collection.activities.destroy', [props.collection.id, pendingActivity.value.id]), {
        preserveScroll: true,
        onFinish: () => {
            deletingActivity.value = false;
            deleteActivityDialogOpen.value = false;
            pendingActivity.value = null;
        },
    });
}

const deleteActivityMessage = computed(() => `Remove the "${pendingActivity.value ? eventLabel(pendingActivity.value.event) : ''}" activity from this collection's log? This action cannot be undone.`);

function readCookie(name) {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
    return match ? decodeURIComponent(match[1]) : null;
}

function deleteCollection() {
    deleting.value = true;
    // Deleting the very record this page shows means the backend's
    // back()-redirect target (this page's own URL) would 404 after the
    // row is gone. Both Inertia's router.delete() and a plain
    // axios.delete() transparently FOLLOW that redirect (the browser's
    // XHR layer can't be told not to — confirmed live: Chromium replays
    // it as DELETE against the 404 target, which itself then 405s since
    // that route is GET-only), so either would misreport a successful
    // delete as a failure. `fetch` with redirect:'manual' is the only
    // browser API that can see "the server redirected" (response.type
    // === 'opaqueredirect') without following it, so that's used here
    // instead — treat any redirect as success, navigate to the Store
    // page ourselves.
    fetch(route('farm.collections.destroy', [props.collection.farm_id, props.collection.id]), {
        method: 'DELETE',
        redirect: 'manual',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': readCookie('XSRF-TOKEN') || '',
        },
    })
        .then((response) => {
            if (response.type !== 'opaqueredirect' && !response.ok) {
                throw new Error(`Unexpected response: ${response.status}`);
            }
            router.visit(route('farm-collection.index'));
            ElNotification({ title: 'Collection Deleted', message: 'The farm collection was removed.', type: 'success', duration: 3200, offset: 84 });
        })
        .catch(() => {
            ElNotification({ title: 'Delete Failed', message: 'Could not delete this collection.', type: 'error', duration: 3200, offset: 84 });
        })
        .finally(() => {
            deleting.value = false;
            deleteDialogOpen.value = false;
        });
}

function formatDate(value) {
    if (!value) return '—';
    return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });
}

function formatDateTime(value) {
    if (!value) return '—';
    return new Date(value.replace(' ', 'T')).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function formatMoney(amount, currency) {
    if (amount === null || amount === undefined) return '—';
    const value = Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${value}` : `$${value}`;
}

const farmName = computed(() => props.collection.farm?.name || `Farm #${props.collection.farm_id}`);

const hasDefects = computed(() => Number(props.collection.initial_defects || 0) > 0);
const defectsKnown = computed(() => props.collection.initial_defects !== null && props.collection.initial_defects !== undefined);
const qualityScoreKnown = computed(() => props.collection.initial_quality_score !== null && props.collection.initial_quality_score !== undefined);

const farmLocation = computed(() => {
    const farm = props.collection.farm;
    if (!farm) return '';
    return [farm.district, farm.region, farm.country].filter(Boolean).join(', ');
});

const recorderInitials = computed(() => {
    const parts = (props.collection.user?.name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return parts.length === 1 ? parts[0][0].toUpperCase() : (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const deleteMessage = computed(() => `Are you sure you want to delete this collection recorded against ${farmName.value}? This action cannot be undone.`);

/* ── Traceability Lineage & Custody Journey — real chain traced from
   this exact collection, not a generic "most recent lot" heuristic:
   Farm (always) → Collection (this record, always) → Batch (via
   batch_farm_collection, if linked) → Certified Lot (via lot_batch, if
   the batch was carried into one) → Tokenised (only once a real
   blockchains row exists for that lot). A stage that hasn't happened
   yet renders as "Not yet" — never a placeholder code. ─────────────── */
const chainSteps = computed(() => {
    const { batch, lot, tokenised } = props.custodyChain;
    return [
        {
            key: 'farm', icon: OfficeBuilding, label: 'Origin Farm', done: true,
            title: farmName.value, sub: props.collection.farm?.farm_code || '—',
        },
        {
            key: 'collection', icon: Box, label: 'Farm Collection', done: true, current: true,
            title: props.collection.collection_code || `#${props.collection.id}`,
            sub: `${Number(props.collection.quantity || 0).toLocaleString()} ${props.collection.unit || ''}`,
        },
        {
            key: 'batch', icon: Files, label: 'Batch Assembly', done: Boolean(batch),
            title: batch?.batch_number || 'Not yet batched',
            sub: batch ? `${Number(batch.weight).toLocaleString()} kg batch` : 'Awaiting assembly',
        },
        {
            key: 'lot', icon: Ticket, label: 'Certified Lot', done: Boolean(lot),
            title: lot?.lot_name || lot?.lot_number || 'Not yet certified',
            sub: lot ? [lot.grade, lot.net_weight_kg ? `${Number(lot.net_weight_kg).toLocaleString()} kg` : null].filter(Boolean).join(' · ') || '—' : 'Awaiting certification',
        },
        {
            key: 'token', icon: Coin, label: 'Tokenised RWA', done: Boolean(tokenised),
            title: tokenised ? 'On-chain' : 'Not yet tokenised',
            sub: tokenised ? (tokenised.network || 'Committed') : 'Awaiting tokenisation',
        },
    ];
});
const chainDoneCount = computed(() => chainSteps.value.filter((s) => s.done).length);
const chainProgressPct = computed(() => ((chainDoneCount.value - 1) / (chainSteps.value.length - 1)) * 100);

const custodyStage = computed(() => {
    const { batch, lot, tokenised } = props.custodyChain;
    if (tokenised) return { label: 'Tokenised', detail: 'Committed on-chain' };
    if (lot) return { label: 'Certified Lot', detail: lot.lot_number };
    if (batch) return { label: 'Aggregated to Batch', detail: batch.batch_number };
    return { label: 'Awaiting Batch', detail: 'Not yet assembled' };
});

/* ── Producer Profile — the farm's real registered owner, from the
   user_farm_ownership pivot (not the sparsely-populated Farmer
   registry), so this reflects whoever actually holds the farm. ─────── */
const farmerName = computed(() => {
    const o = props.farmOwner;
    if (!o) return '';
    return o.name || [o.first_name, o.last_name].filter(Boolean).join(' ');
});
const farmerInitials = computed(() => {
    const parts = farmerName.value.trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return parts.length === 1 ? parts[0][0].toUpperCase() : (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const hasSustainabilityContent = computed(() => (props.collection.farm?.certifications?.length || 0) > 0
    || props.sustainabilityPractices.length > 0
    || [props.collection.farm?.water_conservation_percentage, props.collection.farm?.carbon_sequestration, props.collection.farm?.soil_health_index, props.collection.farm?.soil_type]
        .some((v) => v !== null && v !== undefined));

/* ── Sustainability gauges — same normalization scale FarmProfile.vue
   uses for these same three farm columns (20 tCO2e/ha, 5.0 soil index),
   kept consistent so the ring reads the same way across both pages. ─── */
const waterConservationPercent = computed(() => Math.min(100, props.collection.farm?.water_conservation_percentage || 0));
const carbonSequestrationPercent = computed(() => Math.min(100, ((props.collection.farm?.carbon_sequestration || 0) / 20) * 100));
const soilHealthPercent = computed(() => Math.min(100, ((props.collection.farm?.soil_health_index || 0) / 5) * 100));
function gaugeStyle(percent, color) {
    return { background: `conic-gradient(${color} ${percent}%, var(--surface-container) ${percent}% 100%)` };
}

const visiblePractices = computed(() => props.sustainabilityPractices.slice(0, 3));
const morePracticesCount = computed(() => Math.max(0, props.sustainabilityPractices.length - visiblePractices.value.length));

/* ── Farm Location & Map — real, driven by the farm's own lat/long,
   same Google Maps integration as FarmProfile.vue. ────────────────── */
const hasCoordinates = computed(() => {
    const farm = props.collection.farm;
    return farm && farm.latitude !== null && farm.latitude !== undefined && farm.longitude !== null && farm.longitude !== undefined;
});
const latitudeLabel = computed(() => (hasCoordinates.value ? `${props.collection.farm.latitude}°` : '—'));
const longitudeLabel = computed(() => (hasCoordinates.value ? `${props.collection.farm.longitude}°` : '—'));

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
            { lat: Number(props.collection.farm.latitude), lng: Number(props.collection.farm.longitude) },
            { markerTitle: farmName.value },
        );
        mapReady.value = true;
    } catch {
        mapFailed.value = true;
    }
}
onMounted(mountMap);
watch(() => [props.collection.farm?.latitude, props.collection.farm?.longitude], mountMap);
</script>

<template>
    <MainLayout title="Farm Collection">
        <div class="fcp-page">


            <div class="fcp-page-head">
                <div class="fcp-page-head__text">
                    <div class="fcp-hero__title-row">
                        <h1 class="fcp-page-title">Farm Collection</h1>
                        <span v-if="collection.collection_code" class="fcp-hero__code">
                            <el-icon :size="11"><PriceTag /></el-icon>{{ collection.collection_code }}
                        </span>
                        <span v-if="collection.status" class="fcp-pill fcp-pill--status" :class="`fcp-pill--${collection.status}`">{{ collection.status }}</span>
                       
                    </div>
                    <p class="fcp-page-subtitle">
                        Intake record of physical coffee collection from {{ farmName }}
                        <span v-if="collection.user?.name"> · recorded by {{ collection.user.name }}</span>
                    </p>
                </div>
                <div class="fcp-page-head__actions">
                    <button v-if="collection.can_manage" type="button" class="fcp-btn-outline" @click="editDialogOpen = true">
                        <el-icon><EditPen /></el-icon> Edit Collection
                    </button>
                    <Link v-if="custodyChain.batch" :href="route('batch.show', custodyChain.batch.id)" class="fcp-btn-primary">
                        <el-icon><Files /></el-icon> View Batch
                    </Link>
                    <el-dropdown v-if="collection.can_manage" trigger="click" @command="handleActionCommand">
                        <button type="button" class="fcp-btn-outline fcp-actions-btn" :disabled="deleting" aria-label="More actions">
                            <el-icon><ArrowDown /></el-icon>
                        </button>
                        <template #dropdown>
                            <el-dropdown-menu class="fcp-actions-menu">
                                <el-dropdown-item command="add-activity"><el-icon><Clock /></el-icon> Add Activity</el-dropdown-item>
                                <el-dropdown-item command="delete" class="fcp-actions-menu__danger"><el-icon><Delete /></el-icon> Delete</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </div>

            <!-- ── KPI Summary Strip ─────────────────────────────────────── -->
            <div class="fcp-stat-grid fcp-stat-grid--5">
                <div class="fcp-stat">
                    <div class="fcp-stat__head">
                        <div class="fcp-stat__icon fcp-stat__icon--a"><el-icon><Box /></el-icon></div>
                        <div class="fcp-stat__label">Quantity</div>
                    </div>
                    <div class="fcp-stat__row">
                        <div class="fcp-stat__value">{{ Number(collection.quantity || 0).toLocaleString() }} <span class="fcp-stat__unit">{{ collection.unit || '' }}</span></div>
                        <div class="fcp-stat__caption">Recorded at intake</div>
                    </div>
                </div>
                <div class="fcp-stat">
                    <div class="fcp-stat__head">
                        <div class="fcp-stat__icon fcp-stat__icon--b"><el-icon><Coin /></el-icon></div>
                        <div class="fcp-stat__label">Price / Unit</div>
                    </div>
                    <div class="fcp-stat__row">
                        <div class="fcp-stat__value">{{ formatMoney(collection.collection_price, collection.currency) }}</div>
                        <div v-if="collection.unit" class="fcp-stat__caption">per {{ collection.unit }}</div>
                    </div>
                </div>
                <div class="fcp-stat">
                    <div class="fcp-stat__head">
                        <div class="fcp-stat__icon" :class="hasDefects ? 'fcp-stat__icon--warn' : 'fcp-stat__icon--good'"><el-icon><WarningFilled /></el-icon></div>
                        <div class="fcp-stat__label">Defects</div>
                    </div>
                    <div class="fcp-stat__row">
                        <div class="fcp-stat__value">{{ collection.initial_defects ?? '—' }}</div>
                        <span v-if="defectsKnown" class="fcp-stat__pill" :class="hasDefects ? 'fcp-stat__pill--warn' : 'fcp-stat__pill--good'">
                            {{ hasDefects ? 'Flagged' : 'Clean' }}
                        </span>
                    </div>
                </div>
                <div class="fcp-stat">
                    <div class="fcp-stat__head">
                        <div class="fcp-stat__icon fcp-stat__icon--c"><el-icon><Medal /></el-icon></div>
                        <div class="fcp-stat__label">Quality Score</div>
                    </div>
                    <div class="fcp-stat__row">
                        <div class="fcp-stat__value">{{ collection.initial_quality_score ?? '—' }} <span v-if="qualityScoreKnown" class="fcp-stat__unit">/100</span></div>
                    </div>
                </div>
                <div class="fcp-stat">
                    <div class="fcp-stat__head">
                        <div class="fcp-stat__icon fcp-stat__icon--d"><el-icon><Files /></el-icon></div>
                        <div class="fcp-stat__label">Custody Stage</div>
                    </div>
                    <div class="fcp-stat__row">
                        <div class="fcp-stat__value fcp-stat__value--sm">{{ custodyStage.label }}</div>
                        <div class="fcp-stat__caption">{{ custodyStage.detail }}</div>
                    </div>
                </div>
            </div>

            <!-- ── Traceability Lineage & Custody Journey ────────────────── -->
            <div class="fcp-card">
                <div class="fcp-card__head-row">
                    <h2 class="fcp-card__title fcp-card__title--lg"><el-icon><ArrowRight /></el-icon> Traceability Lineage &amp; Custody Journey</h2>
                </div>
                <p class="fcp-card__desc">Real custody chain traced from this collection through batch assembly, certification, and tokenisation.</p>

                <div class="fcp-chain">
                    <div class="fcp-chain__track"><div class="fcp-chain__fill" :style="{ width: chainProgressPct + '%' }" /></div>
                    <div v-for="step in chainSteps" :key="step.key" class="fcp-chain__step" :class="{ 'fcp-chain__step--done': step.done, 'fcp-chain__step--current': step.current }">
                        <div class="fcp-chain__dot"><el-icon :size="step.current ? 18 : 15"><component :is="step.icon" /></el-icon></div>
                        <div class="fcp-chain__label">{{ step.label }}</div>
                        <div class="fcp-chain__step-title">{{ step.title }}</div>
                        <div class="fcp-chain__step-sub">{{ step.sub }}</div>
                    </div>
                </div>

                <div v-if="custodyChain.batch" class="fcp-chain-banner">
                    <div class="fcp-chain-banner__icon"><el-icon :size="18"><Files /></el-icon></div>
                    <div class="fcp-chain-banner__body">
                        <div class="fcp-chain-banner__title">Assigned Batch: {{ custodyChain.batch.batch_number }}</div>
                        <div class="fcp-chain-banner__meta">
                            {{ Number(custodyChain.batch.weight).toLocaleString() }} kg batch total
                            <span v-if="custodyChain.batch.contribution_pct"> · this collection contributed {{ custodyChain.batch.contribution_pct }}%</span>
                        </div>
                    </div>
                    <Link :href="route('batch.show', custodyChain.batch.id)" class="fcp-chain-banner__link">
                        View Batch <el-icon class="fcp-farm-link__arrow"><ArrowRight /></el-icon>
                    </Link>
                </div>
            </div>

            <!-- ── Source Farm & Farmer Dossier ──────────────────────────── -->
            <div class="fcp-trio">
                <div class="fcp-card">
                    <h2 class="fcp-card__title"><el-icon><OfficeBuilding /></el-icon> Source Farm Dossier</h2>
                    <div class="fcp-farm-summary">
                        <div class="fcp-farm-summary__name">{{ farmName }}</div>
                        <div class="fcp-farm-summary__meta">
                            <span v-if="collection.farm?.farm_code" class="fcp-farm-code">
                                <el-icon :size="11"><PriceTag /></el-icon>{{ collection.farm.farm_code }}
                            </span>
                            <span v-if="farmLocation" class="fcp-farm-loc">
                                <el-icon :size="12"><Location /></el-icon>{{ farmLocation }}
                            </span>
                        </div>
                    </div>
                    <dl class="fcp-dl">
                        <div v-if="collection.farm?.total_area !== null && collection.farm?.total_area !== undefined" class="fcp-dl__row"><dt>Total Area</dt><dd>{{ collection.farm.total_area }} ha</dd></div>
                        <div v-if="collection.farm?.coffee_type" class="fcp-dl__row"><dt>Primary Crop</dt><dd>{{ collection.farm.coffee_type }}</dd></div>
                        <div v-if="collection.farm?.tel" class="fcp-dl__row"><dt><el-icon :size="12"><Phone /></el-icon> Phone</dt><dd>{{ collection.farm.tel }}</dd></div>
                        <div v-if="collection.farm?.email" class="fcp-dl__row"><dt><el-icon :size="12"><Message /></el-icon> Email</dt><dd>{{ collection.farm.email }}</dd></div>
                    </dl>
                    <Link v-if="collection.farm_id" :href="route('farm.show', collection.farm_id)" class="fcp-card-link-btn">
                        View Farm Profile <el-icon :size="13"><ArrowRight /></el-icon>
                    </Link>
                </div>

                <div class="fcp-card">
                    <h2 class="fcp-card__title"><el-icon><User /></el-icon> Producer Profile</h2>
                    <template v-if="farmOwner">
                        <div class="fcp-producer">
                            <div class="fcp-producer__avatar">{{ farmerInitials }}</div>
                            <div class="fcp-producer__body">
                                <div class="fcp-producer__name">{{ farmerName }}</div>
                                <div class="fcp-producer__meta">
                                    {{ farmOwner.is_primary ? 'Primary Owner' : 'Owner' }}
                                    <span v-if="farmOwner.ownership_percentage !== null"> · {{ farmOwner.ownership_percentage }}%</span>
                                </div>
                            </div>
                        </div>
                        <dl class="fcp-dl">
                            <div v-if="farmOwner.tel" class="fcp-dl__row"><dt><el-icon :size="12"><Phone /></el-icon> Mobile</dt><dd>{{ farmOwner.tel }}</dd></div>
                            <div v-if="farmOwner.email" class="fcp-dl__row"><dt><el-icon :size="12"><Message /></el-icon> Email</dt><dd>{{ farmOwner.email }}</dd></div>
                            <div v-if="farmOwner.national_id" class="fcp-dl__row"><dt>National ID</dt><dd>{{ farmOwner.national_id }}</dd></div>
                            <div v-if="farmOwner.created_at" class="fcp-dl__row"><dt>Ownership Since</dt><dd>{{ formatDate(farmOwner.created_at?.slice(0, 10)) }}</dd></div>
                        </dl>
                    </template>
                    <p v-else class="fcp-empty">No owner linked to this farm yet.</p>
                </div>

                <div class="fcp-card">
                    <h2 class="fcp-card__title"><el-icon><CircleCheckFilled /></el-icon> Sustainability Snapshot</h2>
                    <template v-if="hasSustainabilityContent">
                        <div class="fcp-eco-gauges">
                            <div class="fcp-eco-gauge">
                                <div class="fcp-eco-gauge__ring" :style="gaugeStyle(waterConservationPercent, 'var(--primary)')">
                                    <div class="fcp-eco-gauge__hole"><el-icon :size="14"><Umbrella /></el-icon></div>
                                </div>
                                <div class="fcp-eco-gauge__value">{{ collection.farm?.water_conservation_percentage !== null && collection.farm?.water_conservation_percentage !== undefined ? `${collection.farm.water_conservation_percentage}%` : '—' }}</div>
                                <div class="fcp-eco-gauge__label">Water</div>
                            </div>
                            <div class="fcp-eco-gauge">
                                <div class="fcp-eco-gauge__ring" :style="gaugeStyle(carbonSequestrationPercent, 'var(--primary)')">
                                    <div class="fcp-eco-gauge__hole"><el-icon :size="14"><Cloudy /></el-icon></div>
                                </div>
                                <div class="fcp-eco-gauge__value">{{ collection.farm?.carbon_sequestration !== null && collection.farm?.carbon_sequestration !== undefined ? collection.farm.carbon_sequestration : '—' }}</div>
                                <div class="fcp-eco-gauge__label">Carbon</div>
                            </div>
                            <div class="fcp-eco-gauge">
                                <div class="fcp-eco-gauge__ring" :style="gaugeStyle(soilHealthPercent, 'var(--primary)')">
                                    <div class="fcp-eco-gauge__hole"><el-icon :size="14"><Grid /></el-icon></div>
                                </div>
                                <div class="fcp-eco-gauge__value">{{ collection.farm?.soil_health_index !== null && collection.farm?.soil_health_index !== undefined ? `${collection.farm.soil_health_index}/5` : '—' }}</div>
                                <div class="fcp-eco-gauge__label">Soil Health</div>
                            </div>
                        </div>

                        <div v-if="collection.farm?.soil_type" class="fcp-eco-soil">Soil type: <strong>{{ collection.farm.soil_type }}</strong></div>

                        <div v-if="collection.farm?.certifications?.length" class="fcp-eco-block">
                            <span class="fcp-eco-block__label">Certifications</span>
                            <div class="fcp-cert-row">
                                <span v-for="cert in collection.farm.certifications" :key="cert.id" class="fcp-pill fcp-pill--grade" :title="cert.description || ''">{{ cert.name }}</span>
                            </div>
                        </div>

                        <div v-if="sustainabilityPractices.length" class="fcp-eco-block">
                            <span class="fcp-eco-block__label">Practices</span>
                            <ul class="fcp-practice-list">
                                <li v-for="practice in visiblePractices" :key="practice.id" :title="practice.description || ''">
                                    <el-icon :size="12"><CircleCheck /></el-icon> {{ practiceLabel(practice.practice) }}
                                </li>
                            </ul>
                            <span v-if="morePracticesCount > 0" class="fcp-eco-more">+{{ morePracticesCount }} more recorded</span>
                        </div>
                    </template>
                    <p v-else class="fcp-empty">No sustainability data recorded for this farm yet.</p>
                </div>
            </div>

            <div class="fcp-layout">
                <div class="fcp-col-main">
                    <div class="fcp-card">
                        <h2 class="fcp-card__title"><el-icon><WarningFilled /></el-icon> Quality &amp; Intake Condition</h2>
                        <div class="fcp-metric-grid">
                            <div class="fcp-metric-box">
                                <span class="fcp-metric-box__label">Moisture</span>
                                <div class="fcp-metric-box__value">{{ collection.initial_moisture !== null && collection.initial_moisture !== undefined ? `${collection.initial_moisture}%` : '—' }}</div>
                            </div>
                            <div class="fcp-metric-box">
                                <span class="fcp-metric-box__label">Defects</span>
                                <div class="fcp-metric-box__value">{{ collection.initial_defects ?? '—' }}</div>
                            </div>
                            <div class="fcp-metric-box">
                                <span class="fcp-metric-box__label">Grade</span>
                                <div class="fcp-metric-box__value">{{ collection.initial_grade || '—' }}</div>
                            </div>
                            <div class="fcp-metric-box">
                                <span class="fcp-metric-box__label">Quality Score</span>
                                <div class="fcp-metric-box__value">{{ collection.initial_quality_score ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="fcp-card">
                        <h2 class="fcp-card__title"><el-icon><Coffee /></el-icon> Collection Details</h2>
                        <dl class="fcp-dl">
                            <div class="fcp-dl__row"><dt>Coffee Type</dt><dd>{{ collection.coffee_type || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Variety</dt><dd>{{ collection.variety || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Harvest Season</dt><dd>{{ collection.harvest_season || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Collection Date</dt><dd>{{ formatDate(collection.collection_date) }}</dd></div>
                            <div class="fcp-dl__row" v-if="collection.reference"><dt>Reference</dt><dd>{{ collection.reference }}</dd></div>
                        </dl>
                    </div>

                    <div class="fcp-card" v-if="collection.notes">
                        <h2 class="fcp-card__title"><el-icon><Document /></el-icon> Notes</h2>
                        <p class="fcp-notes">{{ collection.notes }}</p>
                    </div>
                </div>

                <div class="fcp-col-side">
                    <div class="fcp-side-card">
                        <div class="fcp-side-card__head">
                            <span class="fcp-side-card__head-icon"><el-icon><User /></el-icon></span>
                            <h3 class="fcp-side-card__eyebrow">Recorded By</h3>
                        </div>
                        <div class="fcp-recorder">
                            <div class="fcp-recorder__avatar">{{ recorderInitials }}</div>
                            <div class="fcp-recorder__body">
                                <div class="fcp-recorder__name">{{ collection.user?.name || 'Unknown' }}</div>
                                <div class="fcp-recorder__meta"><el-icon :size="12"><Clock /></el-icon> {{ formatDateTime(collection.created_at) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="fcp-card">
                        <h2 class="fcp-card__title"><el-icon><Ticket /></el-icon> Payment</h2>
                        <dl class="fcp-dl">
                            <div class="fcp-dl__row"><dt>Price / Unit</dt><dd>{{ formatMoney(collection.collection_price, collection.currency) }}</dd></div>
                            <div class="fcp-dl__row"><dt>Currency</dt><dd>{{ collection.currency || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Payment Status</dt><dd class="fcp-dl__capitalize">{{ collection.payment_status || '—' }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- ── Farm Location & Map ───────────────────────────────────── -->
            <div class="fcp-card">
                <div class="fcp-card__head-row">
                    <h2 class="fcp-card__title fcp-card__title--lg"><el-icon><MapLocation /></el-icon> Collection Point Geolocation</h2>
                </div>
                <p class="fcp-card__desc">The source farm's registered coordinates — this collection's real point of origin.</p>
                <div class="fcp-map-tile">
                    <template v-if="hasCoordinates && mapConfigured">
                        <div ref="mapEl" class="fcp-map-canvas"></div>
                        <div v-if="!mapReady && !mapFailed" class="fcp-map-empty"><span class="fcp-empty">Loading map…</span></div>
                        <div v-if="mapFailed" class="fcp-map-empty">
                            <el-icon :size="20"><Warning /></el-icon>
                            <span class="fcp-empty">Map failed to load.</span>
                        </div>
                    </template>
                    <div v-else class="fcp-map-empty">
                        <el-icon :size="20"><LocationFilled /></el-icon>
                        <span class="fcp-empty">{{ hasCoordinates ? 'Map unavailable — Google Maps is not configured.' : 'No coordinates set for this farm.' }}</span>
                    </div>
                </div>
                <div class="fcp-map-facts">
                    <div class="fcp-map-fact"><el-icon :size="13"><Aim /></el-icon> {{ latitudeLabel }}, {{ longitudeLabel }}</div>
                    <div v-if="collection.farm?.elevation !== null && collection.farm?.elevation !== undefined" class="fcp-map-fact">Elevation: {{ collection.farm.elevation }}m</div>
                    <div v-if="farmLocation" class="fcp-map-fact">{{ farmLocation }}</div>
                </div>
            </div>

            <!-- ── Chronological Audit & Activity Timeline ───────────────── -->
            <div class="fcp-card">
                <div class="fcp-card__head-row">
                    <h2 class="fcp-card__title fcp-card__title--lg"><el-icon><Clock /></el-icon> Chronological Intake &amp; Audit Trail</h2>
                    <span v-if="activities.length" class="fcp-hero__code">{{ activities.length }} Event{{ activities.length === 1 ? '' : 's' }} Logged</span>
                </div>
                <div v-if="activities.length" class="fcp-timeline">
                    <div v-for="activity in activities" :key="activity.id" class="fcp-timeline__item">
                        <div class="fcp-timeline__head">
                            <span class="fcp-event-pill">{{ eventLabel(activity.event) }}</span>
                            <span class="fcp-timeline__date">{{ formatDateTime(activity.created_at) }}</span>
                        </div>
                        <p v-if="activity.description" class="fcp-timeline__desc">{{ activity.description }}</p>
                        <div class="fcp-timeline__foot">
                            <span>{{ activity.recorded_by?.name || 'System' }}</span>
                            <button v-if="collection.can_manage" type="button" class="fcp-activity-delete" aria-label="Delete activity" @click="requestDeleteActivity(activity)">
                                <el-icon :size="13"><Delete /></el-icon>
                            </button>
                        </div>
                    </div>
                </div>
                <p v-else class="fcp-empty">No activity recorded for this collection yet.</p>
            </div>
        </div>

        <EditFarmCollectionModal
            v-if="collection.can_manage"
            v-model="editDialogOpen"
            :collection="collection"
            :coffee-type-options="coffeeTypeOptions"
            :harvest-season-options="harvestSeasonOptions"
            :currency-options="currencyOptions"
        />

        <ConfirmDialog
            v-model="deleteDialogOpen"
            eyebrow="Farm Collection"
            title="Delete Collection"
            :message="deleteMessage"
            confirm-text="Delete Collection"
            :auto-close="false"
            :loading="deleting"
            @confirm="deleteCollection"
        />

        <AddFarmCollectionActivityModal
            v-if="collection.can_manage"
            v-model="addActivityOpen"
            :collection-id="collection.id"
            :activity-options="activityOptions"
        />

        <ConfirmDialog
            v-model="deleteActivityDialogOpen"
            eyebrow="Farm Collection Activity"
            title="Delete Activity"
            :message="deleteActivityMessage"
            confirm-text="Delete Activity"
            :auto-close="false"
            :loading="deletingActivity"
            @confirm="confirmDeleteActivity"
        />
    </MainLayout>
</template>

<style scoped>
.fcp-page {
    --primary: #000000;
    --primary-container: #262626;
    --on-primary-container: #F1F2F3;
    --secondary-container: #E5FAE7;
    --on-secondary-container: #2F6B35;
    --error: #F85149;
    --error-container: #FEEDED;
    --on-error-container: #C6413A;
    --surface-container-lowest: #ffffff;
    --surface-container-low: #F5F6F7;
    --surface-container: #F1F2F3;
    --on-surface: #121516;
    --on-surface-variant: #4B5457;
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-family: var(--sans);
    color: var(--on-surface);
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.fcp-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-variant); }
.fcp-breadcrumb a { color: var(--on-surface-variant); text-decoration: none; }
.fcp-breadcrumb a:hover { color: var(--primary); }
.fcp-breadcrumb__sep { color: var(--card-border); }
.fcp-breadcrumb__current { color: var(--on-surface); }

.fcp-page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.fcp-page-head__text { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.fcp-page-title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    color: var(--primary);
    margin: 0;
}
.fcp-page-subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 400; color: var(--on-surface-variant); margin: 0; max-width: 640px; }
.fcp-page-head__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; flex-wrap: wrap; }

.fcp-actions-btn { flex-shrink: 0; padding: 0 12px; }
.fcp-actions-btn:disabled { opacity: .6; cursor: default; }
.fcp-caret { font-size: 11px; margin-left: -2px; }

.fcp-hero__title-row { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin: 0 0 4px; }
.fcp-hero__code {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 999px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
    font-size: 11px;
    font-weight: 700;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    letter-spacing: .01em;
}

.fcp-pill {
    display: inline-flex;
    align-items: center;
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    text-transform: capitalize;
}
.fcp-pill--grade { background: var(--surface-container); color: var(--on-surface-variant); border: 1px solid color-mix(in srgb, var(--card-border) 80%, transparent); }
.fcp-pill--status { background: var(--secondary-container); color: var(--on-secondary-container); }
.fcp-pill--pending { background: #fef3c7; color: #92400e; }
.fcp-pill--cancelled { background: var(--error-container); color: var(--on-error-container); }

.fcp-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.fcp-stat-grid--5 { grid-template-columns: repeat(5, 1fr); }
.fcp-stat {
    background: var(--surface-container-low);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px;
    transition: box-shadow .15s ease;
}
.fcp-stat:hover { box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06); }
.fcp-stat__head { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.fcp-stat__row { display: flex; align-items: baseline; gap: 8px; min-width: 0; }
.fcp-stat__row .fcp-stat__value { flex-shrink: 0; }
.fcp-stat__icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 13px;
}
.fcp-stat__icon--a { background: color-mix(in srgb, var(--primary) 7%, var(--surface-container-lowest)); color: var(--primary); }
.fcp-stat__icon--b { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.fcp-stat__icon--c { background: #EEF2FF; color: #4338CA; }
.fcp-stat__icon--d { background: #FDF2F8; color: #9D174D; }
.fcp-stat__icon--good { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.fcp-stat__icon--warn { background: #fef3c7; color: #92400e; }
.fcp-stat__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-variant); }
.fcp-stat__value { font-size: 24px; font-weight: 800; letter-spacing: -0.01em; color: var(--on-surface); font-variant-numeric: tabular-nums; line-height: 1.2; }
.fcp-stat__value--sm { font-size: 16px; }
.fcp-stat__unit { font-size: 13px; font-weight: 600; color: var(--on-surface-variant); }
.fcp-stat__caption { flex: 1; min-width: 0; font-size: 11.5px; color: var(--on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.fcp-stat__pill {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    flex-shrink: 0;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.fcp-stat__pill--good { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.fcp-stat__pill--warn { background: #fef3c7; color: #92400e; }

.fcp-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 20px; align-items: start; }
.fcp-col-main { min-width: 0; display: flex; flex-direction: column; gap: 16px; }
.fcp-col-side { min-width: 0; display: flex; flex-direction: column; gap: 16px; }

.fcp-trio { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

.fcp-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px 24px;
}
.fcp-card__head-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.fcp-card__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--on-surface-variant);
    margin: 0 0 14px;
}
.fcp-card__title--lg { font-size: 14px; color: var(--on-surface); margin: 0; }
.fcp-card__title--lg .el-icon { color: var(--primary); }
.fcp-card__desc { font-size: 12.5px; color: var(--on-surface-variant); margin: 6px 0 18px; }

.fcp-dl { margin: 0; display: flex; flex-direction: column; }
.fcp-dl__row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px dashed var(--card-border);
    font-size: 13.5px;
}
.fcp-dl__row:last-child { border-bottom: none; padding-bottom: 0; }
.fcp-dl__row dt { display: inline-flex; align-items: center; gap: 5px; color: var(--on-surface-variant); }
.fcp-dl__row dt .el-icon { color: var(--on-surface-variant); }
.fcp-dl__row dd { margin: 0; font-weight: 600; color: var(--on-surface); text-align: right; }
.fcp-dl__capitalize { text-transform: capitalize; }

.fcp-notes { font-size: 13.5px; line-height: 1.6; color: var(--on-surface); margin: 0; white-space: pre-wrap; }

.fcp-empty { font-size: 13px; color: var(--on-surface-variant); margin: 0; }

/* ── Traceability Lineage & Custody Journey ───────────────────────────── */
.fcp-chain { position: relative; display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; padding: 8px 0 4px; }
.fcp-chain__track { position: absolute; left: 10%; right: 10%; top: 24px; height: 3px; background: var(--card-border); border-radius: 999px; z-index: 0; }
.fcp-chain__fill { height: 100%; background: var(--primary); border-radius: 999px; transition: width .25s ease; }
.fcp-chain__step { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 4px; opacity: .55; }
.fcp-chain__step--done { opacity: 1; }
.fcp-chain__dot {
    width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    background: var(--surface-container); color: var(--on-surface-variant); border: 3px solid var(--surface-container-lowest);
}
.fcp-chain__step--done .fcp-chain__dot { background: var(--primary); color: #fff; }
.fcp-chain__step--current .fcp-chain__dot { width: 50px; height: 50px; box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary) 15%, transparent); }
.fcp-chain__label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--primary); margin-top: 4px; }
.fcp-chain__step:not(.fcp-chain__step--done) .fcp-chain__label { color: var(--on-surface-variant); }
.fcp-chain__step-title { font-size: 12.5px; font-weight: 700; color: var(--on-surface); max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fcp-chain__step-sub { font-size: 11px; color: var(--on-surface-variant); font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }

.fcp-chain-banner {
    display: flex; align-items: center; gap: 12px; margin-top: 20px; padding: 14px 16px;
    border-radius: 8px; background: var(--surface-container-low);
}
.fcp-chain-banner__icon {
    width: 34px; height: 34px; border-radius: 8px; background: var(--surface-container-lowest); color: var(--primary);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.fcp-chain-banner__body { flex: 1; min-width: 0; }
.fcp-chain-banner__title { font-size: 13px; font-weight: 700; color: var(--on-surface); }
.fcp-chain-banner__meta { font-size: 11.5px; color: var(--on-surface-variant); margin-top: 2px; }
.fcp-chain-banner__link { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: var(--primary); text-decoration: none; flex-shrink: 0; }
.fcp-chain-banner__link:hover .fcp-farm-link__arrow { transform: translateX(2px); }

/* ── Metric grid (Quality & Intake) ───────────────────────────────────── */
.fcp-metric-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.fcp-metric-box { padding: 14px; border-radius: 8px; background: var(--surface-container-low); display: flex; flex-direction: column; gap: 6px; }
.fcp-metric-box__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-variant); }
.fcp-metric-box__value { font-size: 18px; font-weight: 800; color: var(--on-surface); }

/* ── Producer profile ──────────────────────────────────────────────────── */
.fcp-producer { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.fcp-producer__avatar {
    width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #3a3a3a);
    color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 13px; font-weight: 700;
}
.fcp-producer__body { min-width: 0; }
.fcp-producer__name { font-size: 14px; font-weight: 700; color: var(--on-surface); }
.fcp-producer__meta { font-size: 12px; color: var(--on-surface-variant); margin-top: 2px; }

.fcp-eco-gauges { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; }
.fcp-eco-gauge { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 6px; }
.fcp-eco-gauge__ring {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.fcp-eco-gauge__hole {
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--surface-container-lowest); color: var(--on-surface-variant);
    display: flex; align-items: center; justify-content: center;
}
.fcp-eco-gauge__value { font-size: 12.5px; font-weight: 800; color: var(--on-surface); line-height: 1.2; }
.fcp-eco-gauge__label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-variant); }

.fcp-eco-soil { font-size: 12.5px; color: var(--on-surface-variant); margin-bottom: 14px; }
.fcp-eco-soil strong { color: var(--on-surface); font-weight: 600; }

.fcp-eco-block { padding-top: 14px; border-top: 1px dashed var(--card-border); }
.fcp-eco-block:first-of-type { padding-top: 0; border-top: none; }
.fcp-eco-block + .fcp-eco-block { margin-top: 14px; }
.fcp-eco-block__label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-variant); margin-bottom: 8px; }

.fcp-eco-more { display: block; margin-top: 8px; font-size: 11.5px; color: var(--on-surface-variant); }

.fcp-cert-row { display: flex; flex-wrap: wrap; gap: 6px; }
.fcp-practice-list { margin: 0; padding: 0; list-style: none; font-size: 12.5px; color: var(--on-surface); display: flex; flex-direction: column; gap: 6px; }
.fcp-practice-list li { display: flex; align-items: flex-start; gap: 6px; }
.fcp-practice-list li .el-icon { color: var(--on-secondary-container); margin-top: 1px; flex-shrink: 0; }

.fcp-card-link-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px; width: 100%; margin-top: 16px;
    height: 36px; border-radius: 6px; background: var(--surface-container-low); color: var(--primary);
    font-size: 12.5px; font-weight: 700; text-decoration: none; transition: background .15s ease;
}
.fcp-card-link-btn:hover { background: var(--surface-container); }

.fcp-empty { font-size: 13px; color: var(--on-surface-variant); margin: 0; }

/* ── Farm summary (Source Farm Dossier) ───────────────────────────────── */
.fcp-farm-summary { margin-bottom: 16px; }
.fcp-farm-summary__name { font-size: 15px; font-weight: 800; letter-spacing: -0.005em; color: var(--on-surface); }
.fcp-farm-summary__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 9px; }
.fcp-farm-code {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 4px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
    font-size: 11px;
    font-weight: 700;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    letter-spacing: .01em;
}
.fcp-farm-loc { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: var(--on-surface-variant); }

.fcp-farm-link__arrow { font-size: 13px; transition: transform .15s ease; }

/* ── Map ───────────────────────────────────────────────────────────────── */
.fcp-map-tile { position: relative; border-radius: 10px; overflow: hidden; background: var(--surface-container-low); min-height: 260px; }
.fcp-map-canvas { width: 100%; height: 260px; }
.fcp-map-empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: var(--on-surface-variant); }
.fcp-map-facts { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 14px; font-size: 12.5px; color: var(--on-surface-variant); }
.fcp-map-fact { display: inline-flex; align-items: center; gap: 5px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }

/* ── Buttons ───────────────────────────────────────────────────────────── */
.fcp-btn-outline {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 36px;
    padding: 0 16px;
    border: none;
    border-radius: 6px;
    background: var(--surface-container);
    color: var(--on-surface);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s ease;
}
.fcp-btn-outline:hover { background: color-mix(in srgb, var(--card-border) 60%, transparent); }
.fcp-btn-primary {
    display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; border: none; border-radius: 6px;
    background: var(--primary); color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; transition: opacity .15s ease;
}
.fcp-btn-primary:hover { opacity: .88; }

/* ── Activity timeline ─────────────────────────────────────────────────── */
.fcp-timeline { display: flex; flex-direction: column; gap: 0; position: relative; padding-left: 16px; border-left: 2px solid var(--card-border); }
.fcp-timeline__item { position: relative; padding: 0 0 20px 16px; }
.fcp-timeline__item::before { content: ''; position: absolute; left: -25px; top: 4px; width: 9px; height: 9px; border-radius: 50%; background: var(--primary); border: 2px solid var(--surface-container-lowest); }
.fcp-timeline__item:last-child { padding-bottom: 0; }
.fcp-timeline__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.fcp-timeline__date { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 11.5px; color: var(--on-surface-variant); white-space: nowrap; }
.fcp-timeline__desc { font-size: 12.5px; color: var(--on-surface-variant); margin: 6px 0 0; line-height: 1.5; }
.fcp-timeline__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 8px; font-size: 11.5px; color: var(--on-surface-variant); }

.fcp-event-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 11px;
    border-radius: 999px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
    border: 1px solid color-mix(in srgb, var(--card-border) 80%, transparent);
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}

.fcp-activity-delete {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: var(--on-surface-variant);
    cursor: pointer;
    transition: background .15s ease, color .15s ease;
}
.fcp-activity-delete:hover { background: var(--error-container); color: var(--error); }
.fcp-activity-delete:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

.fcp-side-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px;
}
.fcp-side-card__head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.fcp-side-card__head-icon {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: color-mix(in srgb, var(--primary) 6%, var(--surface-container-lowest));
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 13px;
}
.fcp-side-card__eyebrow {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--on-surface-variant);
    margin: 0;
}

.fcp-recorder { display: flex; align-items: center; gap: 12px; }
.fcp-recorder__avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #3a3a3a);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .01em;
}
.fcp-recorder__body { min-width: 0; }
.fcp-recorder__name { font-size: 14px; font-weight: 700; color: var(--on-surface); }
.fcp-recorder__meta { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--on-surface-variant); margin-top: 3px; }
.fcp-recorder__meta .el-icon { flex-shrink: 0; }

@media (max-width: 1180px) {
    .fcp-trio { grid-template-columns: 1fr; }
    .fcp-stat-grid--5 { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 1024px) {
    .fcp-layout { grid-template-columns: 1fr; }
    .fcp-stat-grid { grid-template-columns: repeat(2, 1fr); }
    .fcp-chain { grid-template-columns: repeat(5, minmax(90px, 1fr)); overflow-x: auto; }
}

@media (max-width: 640px) {
    .fcp-page-title { font-size: 1.25rem; line-height: 1.6rem; }
    .fcp-stat-grid, .fcp-stat-grid--5 { grid-template-columns: 1fr; }
    .fcp-metric-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 575.98px) {
    .fcp-page-head { flex-direction: column; align-items: stretch; }
}
</style>

<style>
/* Dropdown teleports to <body>, outside scoped styles — literal hex
   from the same UI.md palette, matching StorePage.vue's .st-register-menu. */
.fcp-actions-menu.el-dropdown-menu { border-radius: 6px; border: 1px solid #E5E7EB; padding: 4px; }
.fcp-actions-menu .el-dropdown-menu__item {
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #121516;
    padding: 8px 12px;
}
.fcp-actions-menu .el-dropdown-menu__item:hover { background: #F5F6F7; color: #121516; }
.fcp-actions-menu .fcp-actions-menu__danger { color: #F85149; }
.fcp-actions-menu .fcp-actions-menu__danger:hover { background: #FEEDED; color: #C6413A; }
</style>
