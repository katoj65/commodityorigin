<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import AttachBatchModal from '@/Components/Modals/AttachBatchModal.vue';
import EditLotModal from '@/Components/Modals/EditLotModal.vue';
import PublishLotModal from '@/Components/Modals/PublishLotModal.vue';
import AddLotImagesDialog from '@/Components/Modals/AddLotImagesDialog.vue';
import AddLotActivityModal from '@/Components/Modals/AddLotActivityModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import ImageViewer from '@/Components/ImageViewer.vue';
import {
    ArrowDown,
    Box,
    CameraFilled,
    Clock,
    Coffee,
    Coin,
    Connection,
    Delete,
    EditPen,
    Files,
    FullScreen,
    HotWater,
    Location,
    Odometer,
    OfficeBuilding,
    Operation,
    Plus,
    Position,
    Promotion,
    SoldOut,
    Star,
    Ticket,
    Trophy,
    Van,
} from '@element-plus/icons-vue';

const props = defineProps({
    lot: { type: Object, default: () => ({}) },
    processOptions: { type: Array, default: () => [] },
    coffeeGradeOptions: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
    originOptions: { type: Array, default: () => [] },
    packagingTypeOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    currencyCountries: { type: Object, default: () => ({}) },
    flavorOptions: { type: Array, default: () => [] },
    bodyOptions: { type: Array, default: () => [] },
    acidityOptions: { type: Array, default: () => [] },
    aftertasteOptions: { type: Array, default: () => [] },
    aromaOptions: { type: Array, default: () => [] },
    deliveryMethodOptions: { type: Array, default: () => [] },
    incotermOptions: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    activityOptions: { type: Array, default: () => [] },
});

const showAttachBatch = ref(false);
const showEditLot = ref(false);
const showPublishLot = ref(false);
const deleteDialogOpen = ref(false);
const deleting = ref(false);
const unpublishDialogOpen = ref(false);
const unpublishing = ref(false);
const showAddImages = ref(false);
const showAddActivity = ref(false);
const deleteActivityDialogOpen = ref(false);
const pendingActivity = ref(null);
const deletingActivity = ref(false);
const MAX_LOT_IMAGES = 3;
const remainingImageSlots = computed(() => Math.max(0, MAX_LOT_IMAGES - (props.lot.images?.length || 0)));

/* ── Image viewer: main photo + gallery form one browsable sequence ─────── */
const viewerOpen = ref(false);
const viewerIndex = ref(0);
const viewerImages = computed(() => {
    const list = [];
    if (props.lot.image) {
        list.push({ url: `/storage/${props.lot.image}`, alt: props.lot.lot_name || props.lot.lot_number });
    }
    for (const img of props.lot.images || []) {
        list.push({ url: img.image_url, alt: props.lot.lot_name || props.lot.lot_number || 'Lot photo' });
    }
    return list;
});

function openViewer(index) {
    viewerIndex.value = index;
    viewerOpen.value = true;
}

function handleOptionsCommand(command) {
    if (command === 'traceability') {
        router.visit(route('lot.traceability', props.lot.id));
        return;
    }

    if (command === 'edit') {
        showEditLot.value = true;
        return;
    }

    if (command === 'publish') {
        showPublishLot.value = true;
        return;
    }

    if (command === 'unpublish') {
        unpublishDialogOpen.value = true;
        return;
    }

    if (command === 'add-activity') {
        showAddActivity.value = true;
        return;
    }

    if (command === 'delete') {
        deleteDialogOpen.value = true;
    }
}

/* ── Lot Activity — event slugs are resolved to their metadata display
   name; anything not found (a retired slug) falls back to a titleized
   version of the slug itself rather than disappearing. ────────────────── */
function eventLabel(slug) {
    const match = props.activityOptions.find((option) => option.slug === slug);
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
    router.delete(route('lot.activities.destroy', [props.lot.id, pendingActivity.value.id]), {
        preserveScroll: true,
        onFinish: () => {
            deletingActivity.value = false;
            deleteActivityDialogOpen.value = false;
            pendingActivity.value = null;
        },
    });
}

const deleteActivityMessage = computed(() => `Remove the "${pendingActivity.value ? eventLabel(pendingActivity.value.event) : ''}" activity from this lot's log? This action cannot be undone.`);

function confirmDeleteLot() {
    deleting.value = true;
    router.delete(route('lot.destroy', props.lot.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

function confirmUnpublishLot() {
    unpublishing.value = true;
    router.delete(route('lot.unpublish', props.lot.id), {
        preserveScroll: true,
        onFinish: () => {
            unpublishing.value = false;
            unpublishDialogOpen.value = false;
        },
    });
}

const linkedBatches = computed(() => props.lot.lot_batches || []);

/* ── Traceability QR code ─────────────────────────────────────────────── */
// The QR encodes the traceability URL; keep a visible copy next to the code.
const traceabilityUrl = computed(() => (props.lot.id ? route('lot.traceability', props.lot.id) : ''));

const statusMap = {
    draft: { label: 'Draft', tone: 'warning' },
    ready: { label: 'Ready', tone: 'info' },
    listing_ready: { label: 'Listing Ready', tone: 'success' },
    tokenisation_ready: { label: 'Tokenised', tone: 'success' },
};
const statusInfo = computed(() => statusMap[props.lot.status] || { label: props.lot.status || 'Unknown', tone: 'info' });

/* ── KPI sub-values ───────────────────────────────────────────────────── */
const lotValueTotal = computed(() => {
    if (!props.lot.price || !props.lot.net_weight_kg) return null;
    return Number(props.lot.price) * Number(props.lot.net_weight_kg);
});

/* ── Cupping Profile tile only renders once at least one SCA attribute
   or flavor note has been recorded, rather than showing six "Not
   recorded" rows on every lot. ─────────────────────────────────────── */
const hasCuppingProfile = computed(() => ['acidity', 'body', 'flavor', 'aroma', 'balance', 'aftertaste']
    .some((key) => props.lot[key] !== null && props.lot[key] !== undefined) || (props.lot.flavors?.length > 0));

const market = computed(() => props.lot.market ?? null);

const hasDeliveryProfile = computed(() => {
    if (!market.value) return false;

    return [
        'available_from', 'delivery_method', 'incoterm', 'dispatch',
        'transport_arrangement', 'insurance_arrangement',
    ].some((key) => market.value[key] !== null && market.value[key] !== undefined && market.value[key] !== '');
});

function flavorLabel(slug) {
    const match = props.flavorOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function bodyLabel(slug) {
    const match = props.bodyOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function acidityLabel(slug) {
    const match = props.acidityOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function aftertasteLabel(slug) {
    const match = props.aftertasteOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function aromaLabel(slug) {
    const match = props.aromaOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function deliveryMethodLabel(slug) {
    const match = props.deliveryMethodOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function incotermLabel(slug) {
    const match = props.incotermOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function batchStatusTone(status) {
    const s = (status || '').toLowerCase();
    if (['received', 'ready', 'completed', 'delivered', 'approved', 'batched'].includes(s)) return 'good';
    if (['processing', 'pending', 'draft'].includes(s)) return 'warn';
    if (['cancelled', 'rejected', 'expired'].includes(s)) return 'bad';
    return 'neutral';
}

/* ── Origin trace: farms and farm collections behind the linked batches ── */
const sourcedCollections = computed(() => {
    const rows = [];
    for (const lb of linkedBatches.value) {
        const batch = lb.batch;
        if (!batch) continue;
        for (const link of batch.farm_collection_links || []) {
            rows.push({ link, batch });
        }
    }
    return rows;
});

/* ── Origin trace: batches + farm collections + farms combined ─────────── */
const originTrace = computed(() => {
    return linkedBatches.value.map((lb) => {
        const batch = lb.batch;
        const collections = sourcedCollections.value.filter((row) => row.batch?.id === batch?.id);
        const farmMap = new Map();
        for (const row of collections) {
            const farm = row.link.farm_collection?.farm;
            if (farm && !farmMap.has(farm.id)) {
                farmMap.set(farm.id, farm);
            }
        }
        return { lb, batch, collections, farms: Array.from(farmMap.values()) };
    });
});

function farmLocation(farm) {
    if (!farm) return '';
    return [farm.district, farm.region, farm.country].filter(Boolean).join(', ');
}

const recorderInitials = computed(() => {
    const name = (props.lot.user?.name || '').trim();
    if (!name) return '?';
    return name.split(/\s+/).filter(Boolean).slice(0, 2).map((p) => p[0].toUpperCase()).join('');
});

const fmtNumber = (value, digits = 2) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
};

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value.replace(' ', 'T')).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const fmtDateTime = (value) => {
    if (!value) return '—';
    return new Date(value.replace(' ', 'T')).toLocaleString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
    });
};
</script>

<template>
    <DesignPreviewLayout title="Lot Profile">
        <div class="lp-page">
            <!-- ── Page header ──────────────────────────────────────────── -->
            <div class="lp-page-header">
                <div class="lp-page-header__text">
                    <h1 class="lp-page-title">Lot Profile</h1>
                    <p class="lp-page-description">Full specifications, linked batches, and activity history for this coffee lot.</p>
                </div>
                <el-dropdown trigger="click" @command="handleOptionsCommand">
                    <button type="button" class="lp-btn lp-btn--primary">
                        Options <el-icon class="lp-caret"><ArrowDown /></el-icon>
                    </button>
                    <template #dropdown>
                        <el-dropdown-menu class="lp-options-menu">
                            <el-dropdown-item command="traceability"><el-icon><Connection /></el-icon> View Traceability</el-dropdown-item>
                            <el-dropdown-item v-if="lot.can_manage" command="edit"><el-icon><EditPen /></el-icon> Edit Lot</el-dropdown-item>
                            <el-dropdown-item v-if="lot.can_manage && !lot.is_published" command="publish"><el-icon><Promotion /></el-icon> Publish to Market</el-dropdown-item>
                            <el-dropdown-item v-if="lot.can_manage && lot.is_published" command="unpublish"><el-icon><SoldOut /></el-icon> Unpublish from Market</el-dropdown-item>
                            <el-dropdown-item v-if="lot.can_manage" command="add-activity"><el-icon><Clock /></el-icon> Add Activity</el-dropdown-item>
                            <el-dropdown-item v-if="lot.can_manage" command="delete" divided class="lp-options-menu__item--danger"><el-icon><Delete /></el-icon> Delete Lot</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>

            <!-- ── Bento mosaic ─────────────────────────────────────────── -->
            <div class="lp-bento">
                <!-- Hero tile -->
                <div class="lp-tile lp-tile--hero">
                    <div class="lp-hero__top">
                        <div class="lp-hero__top-main">
                            <div class="lp-hero__photo-col">
                                <div class="lp-hero__photo" :class="{ 'lp-hero__photo--clickable': lot.image }" @click="lot.image && openViewer(0)">
                                    <img v-if="lot.image" :src="`/storage/${lot.image}`" :alt="lot.lot_name || lot.lot_number" />
                                    <el-icon v-else :size="46"><Ticket /></el-icon>
                                </div>
                            </div>
                            <div class="lp-hero__intro">
                                <span class="lp-badge" :class="`lp-badge--${statusInfo.tone}`"><span class="lp-badge__dot"></span>{{ statusInfo.label }}</span>
                                <h2 class="lp-hero__title">{{ lot.lot_name || lot.lot_number }}</h2>
                                <p v-if="lot.description" class="lp-hero__description">{{ lot.description }}</p>
                            </div>
                        </div>
                        <el-tooltip
                            v-if="lot.can_manage"
                            :content="remainingImageSlots > 0 ? 'Upload photos' : 'Photo limit reached'"
                            placement="top"
                        >
                            <button
                                type="button"
                                class="lp-btn lp-btn--outline lp-btn--icon lp-hero__upload"
                                :disabled="remainingImageSlots <= 0"
                                @click="showAddImages = true"
                            >
                                <el-icon><CameraFilled /></el-icon>
                            </button>
                        </el-tooltip>
                    </div>
                    <div v-if="lot.images?.length" class="lp-hero__gallery-section">
                        <div class="lp-hero__gallery">
                            <button
                                v-for="(img, idx) in lot.images"
                                :key="img.id"
                                type="button"
                                class="lp-hero__gallery-item"
                                @click="openViewer((lot.image ? 1 : 0) + idx)"
                            >
                                <img :src="img.image_url" alt="Lot photo" />
                            </button>
                        </div>
                    </div>
                    <div class="lp-hero__facts">
                        <span class="lp-hero__fact"><el-icon><Ticket /></el-icon><span class="lp-mono">{{ lot.lot_number }}</span></span>
                        <span class="lp-hero__fact"><el-icon><HotWater /></el-icon>{{ lot.process || 'Process pending' }}</span>
                        <span class="lp-hero__fact"><el-icon><Clock /></el-icon>{{ fmtDate(lot.created_at) }}</span>
                    </div>
                </div>

                <!-- Stat tiles -->
                <div class="lp-tile lp-tile--stat">
                    <div class="lp-kpi__group">
                        <div class="lp-kpi__top">
                            <span class="lp-kpi__icon lp-kpi__icon--weight"><el-icon><Odometer /></el-icon></span>
                            <span class="lp-tile__label">Net Weight</span>
                        </div>
                        <span class="lp-tile__value lp-mono">{{ fmtNumber(lot.net_weight_kg) }}<small>kg</small></span>
                        <span class="lp-tile__sub">{{ lot.quantity_bags ? `${fmtNumber(lot.quantity_bags, 0)} bags total` : 'Bag count not recorded' }}</span>
                    </div>

                    <div class="lp-kpi__group">
                        <div class="lp-kpi__top">
                            <span class="lp-kpi__icon lp-kpi__icon--batches"><el-icon><Files /></el-icon></span>
                            <span class="lp-tile__label">Batches Linked</span>
                        </div>
                        <span class="lp-tile__value lp-mono">{{ linkedBatches.length }}</span>
                        <span class="lp-tile__sub">{{ linkedBatches.length ? 'Linked to this lot' : 'No batches linked yet' }}</span>
                    </div>
                </div>
                <div class="lp-tile lp-tile--stat lp-tile--bags">
                    <div class="lp-kpi__top">
                        <span class="lp-kpi__icon lp-kpi__icon--bags"><el-icon><Box /></el-icon></span>
                        <span class="lp-tile__label">Bags</span>
                    </div>
                    <span class="lp-tile__value lp-mono">{{ fmtNumber(lot.quantity_bags, 0) }}</span>
                    <span class="lp-tile__sub">{{ lot.bag_weight_kg ? `${fmtNumber(lot.bag_weight_kg)} kg each` : 'Bag weight not recorded' }}</span>

                    <Link :href="traceabilityUrl" class="lp-bag-qr" :title="`Scan to trace ${lot.lot_number || 'this lot'}`">
                        <span v-if="lot.qr_code" class="lp-bag-qr__code" v-html="lot.qr_code"></span>
                        <span v-else class="lp-bag-qr__code"><el-icon :size="22"><FullScreen /></el-icon></span>
                        <span class="lp-bag-qr__label">Scan to trace</span>
                    </Link>
                </div>
                <div class="lp-tile lp-tile--stat lp-tile--accent lp-tile--wide">
                    <div class="lp-kpi__top">
                        <span class="lp-kpi__icon lp-kpi__icon--price"><el-icon><Coin /></el-icon></span>
                        <span class="lp-tile__label">Price / kg</span>
                    </div>
                    <span class="lp-tile__value lp-mono">{{ lot.price ? `${lot.currency || 'USD'} ${fmtNumber(lot.price)}` : '—' }}</span>
                    <span class="lp-tile__sub">{{ lotValueTotal !== null ? `${lot.currency || 'USD'} ${fmtNumber(lotValueTotal)} total value` : 'Total value pending' }}</span>
                </div>
                <!-- Specifications -->
                <div class="lp-tile lp-tile--specs">
                    <h2 class="lp-tile__title"><el-icon><Operation /></el-icon> Specifications</h2>
                    <div class="lp-spec-grid">
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><HotWater /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Process</span>
                                <strong class="lp-spec__value">{{ lot.process || 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Coffee /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Grade</span>
                                <strong class="lp-spec__value">{{ lot.grade || 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Box /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Packaging Type</span>
                                <strong class="lp-spec__value">{{ lot.packaging_type || 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Odometer /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Bag Weight</span>
                                <strong class="lp-spec__value lp-mono">{{ fmtNumber(lot.bag_weight_kg) }} kg</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Trophy /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Quality Score</span>
                                <strong class="lp-spec__value lp-mono">{{ lot.quality_score ? `${fmtNumber(lot.quality_score)} /100` : 'Not recorded' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cupping Profile -->
                <div v-if="hasCuppingProfile" class="lp-tile lp-tile--specs">
                    <h2 class="lp-tile__title"><el-icon><Star /></el-icon> Cupping Profile</h2>
                    <div class="lp-spec-grid">
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Star /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Acidity</span>
                                <strong class="lp-spec__value">{{ lot.acidity ? acidityLabel(lot.acidity) : 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Star /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Body</span>
                                <strong class="lp-spec__value">{{ lot.body ? bodyLabel(lot.body) : 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Star /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Flavor</span>
                                <strong class="lp-spec__value">{{ lot.flavor ? flavorLabel(lot.flavor) : 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Star /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Aroma</span>
                                <strong class="lp-spec__value">{{ lot.aroma ? aromaLabel(lot.aroma) : 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Star /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Balance</span>
                                <strong class="lp-spec__value lp-mono">{{ lot.balance !== null && lot.balance !== undefined ? fmtNumber(lot.balance) : 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Star /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Aftertaste</span>
                                <strong class="lp-spec__value">{{ lot.aftertaste ? aftertasteLabel(lot.aftertaste) : 'Not recorded' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div v-if="lot.flavors?.length" class="lp-flavor-notes">
                        <span class="lp-spec__label">Flavor Notes</span>
                        <div class="lp-flavor-notes__chips">
                            <span v-for="flavor in lot.flavors" :key="flavor.id" class="lp-event-pill">{{ flavor.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Delivery -->
                <div v-if="hasDeliveryProfile" class="lp-tile lp-tile--specs">
                    <h2 class="lp-tile__title"><el-icon><Van /></el-icon> Delivery</h2>
                    <div class="lp-spec-grid">
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Location /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Available From (Location)</span>
                                <strong class="lp-spec__value">{{ market.available_from || 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Van /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Delivery Method</span>
                                <strong class="lp-spec__value">{{ market.delivery_method ? deliveryMethodLabel(market.delivery_method) : 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Operation /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Incoterm</span>
                                <strong class="lp-spec__value">{{ market.incoterm ? incotermLabel(market.incoterm) : 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Location /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Dispatch</span>
                                <strong class="lp-spec__value">{{ market.dispatch || 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Van /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Transport Arrangement</span>
                                <strong class="lp-spec__value">{{ market.transport_arrangement || 'Not recorded' }}</strong>
                            </div>
                        </div>
                        <div class="lp-spec">
                            <span class="lp-spec__icon"><el-icon><Promotion /></el-icon></span>
                            <div class="lp-spec__body">
                                <span class="lp-spec__label">Insurance Arrangement</span>
                                <strong class="lp-spec__value">{{ market.insurance_arrangement || 'Not recorded' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="lot.notes" class="lp-tile lp-tile--specs">
                    <h2 class="lp-tile__title"><el-icon><EditPen /></el-icon> Notes</h2>
                    <p class="lp-prose">{{ lot.notes }}</p>
                </div>

                <!-- Origin & Provenance: batches, collections and farms combined -->
                <div class="lp-tile lp-tile--full">
                    <div class="lp-tile__head">
                        <div class="lp-tile__head-left">
                            <h2 class="lp-tile__title"><el-icon><Connection /></el-icon> Origin &amp; Provenance</h2>
                            <span v-if="linkedBatches.length" class="lp-tile__count">{{ linkedBatches.length }}</span>
                        </div>
                        <button v-if="lot.can_manage" type="button" class="lp-btn lp-btn--primary" @click="showAttachBatch = true">
                            <el-icon><Plus /></el-icon> Attach Batch
                        </button>
                    </div>

                    <div v-if="originTrace.length" class="lp-origin">
                        <div v-for="entry in originTrace" :key="entry.lb.id" class="lp-origin__batch">
                            <div class="lp-origin__batch-head">
                                <span class="lp-batch-row__icon"><el-icon><Box /></el-icon></span>
                                <div class="lp-origin__batch-info">
                                    <div class="lp-origin__batch-line">
                                        <span class="lp-origin__batch-number lp-mono">{{ entry.batch?.batch_number || entry.lb.batch_number }}</span>
                                        <span v-if="entry.batch?.processing_method" class="lp-inline-code">{{ entry.batch.processing_method }}</span>
                                        <span v-if="entry.batch?.status" class="lp-batch-row__status" :class="`lp-batch-row__status--${batchStatusTone(entry.batch.status)}`">{{ entry.batch.status }}</span>
                                    </div>
                                    <div class="lp-origin__batch-meta">
                                        {{ entry.batch?.variety || '—' }}
                                        <span v-if="entry.batch?.warehouse_location"> &middot; <el-icon class="lp-batch-row__meta-icon"><Location /></el-icon>{{ entry.batch.warehouse_location }}</span>
                                    </div>
                                </div>
                                <div class="lp-origin__batch-stats">
                                    <span v-if="entry.batch?.cup_score" class="lp-origin__stat">
                                        <span class="lp-origin__stat-value lp-mono">{{ fmtNumber(entry.batch.cup_score) }}</span>
                                        <span class="lp-origin__stat-label">Cup Score</span>
                                    </span>
                                    <span v-if="entry.lb.allocation_kg" class="lp-origin__stat">
                                        <span class="lp-origin__stat-value lp-mono">{{ fmtNumber(entry.lb.allocation_kg) }} kg</span>
                                        <span class="lp-origin__stat-label">Drawn</span>
                                    </span>
                                </div>
                            </div>

                            <div v-if="entry.collections.length" class="lp-origin__sources">
                                <div v-for="row in entry.collections" :key="row.link.id" class="lp-origin__source">
                                    <el-icon class="lp-origin__source-icon"><Coffee /></el-icon>
                                    <span class="lp-origin__source-code lp-mono">{{ row.link.farm_collection_code }}</span>
                                    <span v-if="row.link.farm_collection?.initial_grade" class="lp-grade-pill">{{ row.link.farm_collection.initial_grade }}</span>
                                    <span v-if="row.link.farm_collection?.quantity" class="lp-origin__source-qty lp-mono">{{ Number(row.link.farm_collection.quantity).toLocaleString() }} {{ row.link.farm_collection.unit || '' }}</span>
                                    <span class="lp-origin__source-farm">
                                        {{ row.link.farm_collection?.farm?.name || 'Unknown farm' }}
                                        <span v-if="farmLocation(row.link.farm_collection?.farm)" class="lp-origin__source-loc">&middot; {{ farmLocation(row.link.farm_collection?.farm) }}</span>
                                    </span>
                                </div>
                            </div>

                            <div v-if="entry.farms.length" class="lp-origin__farms">
                                <div v-for="farm in entry.farms" :key="farm.id" class="lp-origin__farm">
                                    <el-icon class="lp-origin__farm-icon"><OfficeBuilding /></el-icon>
                                    <span class="lp-origin__farm-name">{{ farm.name || `Farm #${farm.id}` }}</span>
                                    <span v-if="farmLocation(farm)" class="lp-origin__farm-loc">{{ farmLocation(farm) }}</span>
                                    <span class="lp-farm-facts">
                                        <span v-if="farm.elevation !== null && farm.elevation !== undefined" class="lp-farm-fact"><el-icon><Position /></el-icon>{{ Math.round(farm.elevation) }}m</span>
                                        <span v-if="farm.coffee_type" class="lp-farm-fact"><el-icon><Coffee /></el-icon>{{ farm.coffee_type }}</span>
                                        <span v-if="farm.coffee_area !== null && farm.coffee_area !== undefined" class="lp-farm-fact"><el-icon><Odometer /></el-icon>{{ farm.coffee_area }} ha</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="lp-empty">No batches linked to this lot yet. Attach one by its batch number to record where this lot's coffee came from.</p>

                    <div class="lp-origin__recorder">
                        <div class="lp-recorder">
                            <div class="lp-recorder__avatar">{{ recorderInitials }}</div>
                            <div class="lp-recorder__body">
                                <div class="lp-recorder__name">{{ lot.user?.name || 'Unknown' }}</div>
                                <div class="lp-recorder__role">Recorded By · Lot Creator</div>
                            </div>
                        </div>
                        <div class="lp-origin__recorder-date">
                            <el-icon><Clock /></el-icon>
                            <span class="lp-mono">{{ fmtDateTime(lot.created_at) }}</span>
                        </div>
                    </div>

                    <!-- Activity log — integrated beneath the origin trace -->
                    <div class="lp-activity">
                        <div class="lp-activity__head">
                            <div class="lp-activity__heading">
                                <h3 class="lp-activity__title"><el-icon><Clock /></el-icon> Lot Activity</h3>
                                <span v-if="activities.length" class="lp-tile__count">{{ activities.length }}</span>
                            </div>
                            <button v-if="lot.can_manage" type="button" class="lp-btn lp-btn--primary lp-btn--sm" @click="showAddActivity = true">
                                <el-icon><Plus /></el-icon> Add Activity
                            </button>
                        </div>

                        <div v-if="activities.length" class="lp-activity-table-wrap">
                            <table class="lp-activity-table">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Description</th>
                                        <th>Recorded By</th>
                                        <th>Date</th>
                                        <th v-if="lot.can_manage" class="lp-activity-table__actions-head" />
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="activity in activities" :key="activity.id">
                                        <td><span class="lp-event-pill">{{ eventLabel(activity.event) }}</span></td>
                                        <td class="lp-activity-table__desc">{{ activity.description || '—' }}</td>
                                        <td>{{ activity.recorded_by?.name || 'System' }}</td>
                                        <td class="lp-mono lp-activity-table__date">{{ fmtDateTime(activity.created_at) }}</td>
                                        <td v-if="lot.can_manage" class="lp-activity-table__actions">
                                            <button type="button" class="lp-activity-delete" aria-label="Delete activity" @click="requestDeleteActivity(activity)">
                                                <el-icon :size="14"><Delete /></el-icon>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="lp-empty">No activity recorded for this lot yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <AttachBatchModal v-if="lot.can_manage" v-model="showAttachBatch" :lot-id="lot.id" />
        <EditLotModal
            v-if="lot.can_manage"
            v-model="showEditLot"
            :lot="lot"
            :process-options="processOptions"
            :coffee-grade-options="coffeeGradeOptions"
            :variety-options="varietyOptions"
            :origin-options="originOptions"
            :packaging-type-options="packagingTypeOptions"
            :currency-options="currencyOptions"
            :currency-countries="currencyCountries"
            :flavor-options="flavorOptions"
            :body-options="bodyOptions"
            :acidity-options="acidityOptions"
            :aftertaste-options="aftertasteOptions"
            :aroma-options="aromaOptions"
        />
        <PublishLotModal
            v-if="lot.can_manage && !lot.is_published"
            v-model="showPublishLot"
            :lot="lot"
            :currency-options="currencyOptions"
            :currency-countries="currencyCountries"
            :delivery-method-options="deliveryMethodOptions"
            :incoterm-options="incotermOptions"
        />
        <ConfirmDialog
            v-model="deleteDialogOpen"
            eyebrow="Lot"
            title="Delete Lot"
            :message="`Are you sure you want to delete lot ${lot.lot_number}? This action cannot be undone.`"
            confirm-text="Delete Lot"
            :auto-close="false"
            :loading="deleting"
            @confirm="confirmDeleteLot"
        />
        <AddLotImagesDialog
            v-if="lot.can_manage"
            v-model="showAddImages"
            :lot-id="lot.id"
            :remaining-slots="remainingImageSlots"
        />
        <ConfirmDialog
            v-model="unpublishDialogOpen"
            eyebrow="Lot"
            title="Unpublish from Market"
            :message="`Remove lot ${lot.lot_number} from the market? Buyers will no longer be able to find or order it.`"
            confirm-text="Unpublish"
            :auto-close="false"
            :loading="unpublishing"
            @confirm="confirmUnpublishLot"
        />
        <ImageViewer v-model="viewerOpen" :images="viewerImages" :index="viewerIndex" />

        <AddLotActivityModal
            v-if="lot.can_manage"
            v-model="showAddActivity"
            :lot-id="lot.id"
            :activity-options="activityOptions"
        />
        <ConfirmDialog
            v-model="deleteActivityDialogOpen"
            eyebrow="Lot Activity"
            title="Delete Activity"
            :message="deleteActivityMessage"
            confirm-text="Delete Activity"
            :auto-close="false"
            :loading="deletingActivity"
            @confirm="confirmDeleteActivity"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Tokens ───────────────────────────────────────────────────────────── */
.lp-page {
    --bg: #FFFFFF;
    --surface: #FFFFFF;
    --surface-muted: #FAFAFA;
    --surface-elevated: #F4F4F5;
    --border: #E4E4E7;
    --primary: #000000;
    --text: #18181B;
    --text-2: #52525B;
    --text-muted: #A1A1AA;
    --accent: #EA580C;
    --accent-soft: #FFF1E8;
    --success: #15803D;
    --success-soft: #F0FDF4;
    --warning: #B45309;
    --warning-soft: #FEF3E2;
    --error: #B91C1C;
    --info: #1D4ED8;
    --font-sans: Inter, system-ui, sans-serif;
    --font-mono: ui-monospace, 'SF Mono', 'JetBrains Mono', Consolas, monospace;

    background: var(--bg);
    color: var(--text);
    font-family: var(--font-sans);
    min-height: 100%;
}
.lp-mono { font-family: var(--font-mono); }

/* ── Page header ──────────────────────────────────────────────────────── */
.lp-page-header {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 20px;
    margin-bottom: 24px;
}
.lp-page-header__text { min-width: 0; }
.lp-page-title {
    font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0 0 6px;
    display: flex; align-items: center; gap: 9px;
}
.lp-page-title .el-icon { font-size: 20px; color: currentColor; }

.lp-options-menu.el-dropdown-menu { border-radius: 6px; border: 1px solid var(--border); padding: 4px; }
.lp-options-menu :deep(.el-dropdown-menu__item) {
    display: flex; align-items: center; gap: 8px;
    border-radius: 6px; font-size: 13px; color: var(--text);
}
.lp-options-menu :deep(.el-dropdown-menu__item) .el-icon { font-size: 14px; color: currentColor; }
.lp-options-menu :deep(.el-dropdown-menu__item:hover) { background: var(--surface-elevated); color: var(--text); }
.lp-options-menu :deep(.lp-options-menu__item--danger) { color: var(--error); }
.lp-options-menu :deep(.lp-options-menu__item--danger:hover) { background: #FEF2F2; color: var(--error); }
.lp-page-description { font-size: 13.5px; line-height: 20px; color: var(--text-2); margin: 0; max-width: 60ch; }

.lp-btn--outline {
    background: var(--surface-muted); color: var(--text); border-color: var(--border);
}
.lp-btn--outline:hover { background: var(--surface-elevated); opacity: 1; }
.lp-caret { font-size: 11px; margin-left: 2px; }

/* ── Bento grid ───────────────────────────────────────────────────────── */
.lp-bento {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    grid-auto-rows: minmax(96px, auto);
    grid-auto-flow: dense;
    gap: 14px;
}

.lp-tile {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.lp-tile--hero { grid-column: span 2; grid-row: span 2; background: var(--surface-muted); justify-content: space-between; gap: 16px; }
.lp-tile--stat { justify-content: center; gap: 6px; }
.lp-tile--accent { background: var(--accent-soft); border-color: transparent; }
.lp-tile--specs { grid-column: span 2; }
.lp-tile--wide { grid-column: span 2; }
.lp-tile--full { grid-column: span 4; }

/* ── Origin trace row: Farms / Farm Collections columns ─────────────────── */
.lp-tile-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    align-items: stretch;
}
.lp-tile-row--2col { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.lp-tile-row .lp-tile__head { flex-wrap: wrap; row-gap: 10px; }
/* Columns are narrow, so stack each row's stat/status below its icon+text
   instead of squeezing everything onto one line. */
.lp-tile-row .lp-batch-row { flex-wrap: wrap; row-gap: 8px; }
.lp-tile-row .lp-batch-row__main { flex: 1 1 100%; }
.lp-tile-row .lp-batch-row__stat { align-items: flex-start; }
.lp-tile-row .lp-batch-row__chevron { display: none; }

/* ── Hero tile ────────────────────────────────────────────────────────── */
.lp-hero__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.lp-hero__top-main { display: flex; align-items: flex-start; gap: 16px; min-width: 0; }
.lp-btn--icon { width: 36px; height: 36px; padding: 0; justify-content: center; }
.lp-btn--icon .el-icon { font-size: 19px; }
.lp-hero__upload { flex-shrink: 0; }
.lp-hero__upload:disabled { opacity: 0.5; cursor: default; }
.lp-hero__upload:disabled:hover { opacity: 0.5; }

/* Main lot photo sits above its own uploaded-gallery strip, both in one
   column, so the gallery reads as "below the main image" and stays
   visibly smaller than it. */
.lp-hero__photo-col { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }

.lp-hero__gallery-section {
    padding-top: 14px;
    border-top: 1px solid var(--border);
}
.lp-hero__gallery {
    display: flex;
    gap: 10px;
}
.lp-hero__gallery-item {
    width: 76px;
    height: 76px;
    padding: 0;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
    background: var(--surface-elevated);
    cursor: pointer;
    flex-shrink: 0;
    transition: transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease;
}
.lp-hero__gallery-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(24, 24, 27, 0.18);
    border-color: var(--accent);
}
.lp-hero__gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.lp-hero__photo {
    width: 148px; height: 148px; border-radius: 12px; flex-shrink: 0;
    background: var(--surface-elevated); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); overflow: hidden;
}
.lp-hero__photo img { width: 100%; height: 100%; object-fit: cover; }
.lp-hero__photo--clickable { cursor: pointer; transition: opacity 120ms ease; }
.lp-hero__photo--clickable:hover { opacity: 0.9; }
.lp-hero__intro { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.lp-hero__title { font-size: 25px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lp-hero__description { font-size: 13px; line-height: 20px; color: var(--text-2); margin: 0; white-space: pre-line; }
.lp-hero__facts { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; padding-top: 16px; border-top: 1px solid var(--border); }
.lp-hero__fact {
    display: inline-flex; align-items: center; gap: 6px;
    height: 26px; padding: 0 10px; border-radius: 999px;
    background: var(--surface-elevated); border: 1px solid var(--border);
    font-size: 12px; font-weight: 600; color: var(--text-2);
}
.lp-hero__fact .el-icon { font-size: 12px; color: var(--text-muted); }

/* ── Badges ───────────────────────────────────────────────────────────── */
.lp-badge {
    display: inline-flex; align-items: center; gap: 6px; align-self: flex-start;
    height: 22px; padding: 0 9px; border-radius: 999px;
    font-size: 11px; font-weight: 600;
    background: var(--surface-elevated); border: 1px solid var(--border); color: var(--text-2);
}
.lp-badge__dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.lp-badge--success { color: var(--success); background: var(--success-soft); border-color: transparent; }
.lp-badge--warning { color: var(--warning); background: var(--warning-soft); border-color: transparent; }
.lp-badge--error { color: var(--error); background: #FEF2F2; border-color: transparent; }
.lp-badge--info { color: var(--info); background: #EFF6FF; border-color: transparent; }

/* ── Stat tiles ───────────────────────────────────────────────────────── */
.lp-tile__label { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-muted); }
.lp-tile__label .el-icon { font-size: 12px; }
.lp-tile__value { font-size: 22px; line-height: 28px; font-weight: 700; color: var(--text); display: flex; align-items: baseline; gap: 4px; }
.lp-tile__value small { font-size: 11px; font-weight: 500; color: var(--text-muted); }
.lp-tile__sub { font-size: 11px; color: var(--text-muted); }
.lp-tile--accent .lp-tile__label { color: #C2410C; }
.lp-tile--accent .lp-tile__value { color: #9A3412; }
.lp-tile--accent .lp-tile__sub { color: #C2410C; }

.lp-kpi__top { display: flex; align-items: center; gap: 8px; margin-bottom: 2px; }
.lp-kpi__icon {
    width: 26px; height: 26px; border-radius: 7px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--surface-elevated); color: var(--text-muted); font-size: 13px;
}
.lp-kpi__icon--weight { background: #EFF6FF; color: var(--info); }
.lp-kpi__icon--bags { background: var(--surface-elevated); color: var(--text-2); }
.lp-kpi__icon--price { background: var(--accent-soft); color: #C2410C; }
.lp-kpi__icon--batches { background: var(--surface-elevated); color: var(--text-2); }

.lp-kpi__group + .lp-kpi__group { margin-top: 18px; }

.lp-bag-qr {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
    margin-top: 12px;
    text-decoration: none;
}
.lp-bag-qr__code {
    width: 68px;
    height: 68px;
    padding: 6px;
    border-radius: 8px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    color: var(--text-muted);
}
.lp-bag-qr__code :deep(svg) { width: 100%; height: 100%; display: block; }
.lp-bag-qr__label { font-size: 10.5px; font-weight: 600; color: var(--text-muted); }

/* ── Tile headers ─────────────────────────────────────────────────────── */
.lp-tile__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.lp-tile__head .lp-tile__title { margin: 0; }
.lp-tile__head-left { display: flex; align-items: center; gap: 8px; min-width: 0; }
.lp-tile__head-left .lp-tile__title { margin: 0; }
.lp-tile__title {
    font-size: 13px; font-weight: 700; color: var(--text);
    margin: 0 0 14px; display: flex; align-items: center; gap: 6px;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.lp-tile__title .el-icon { font-size: 13px; color: currentColor; }
.lp-tile__count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 20px; padding: 0 6px; margin-top: -1px;
    border-radius: 999px; background: var(--surface-elevated); color: var(--text-2);
    font-size: 11px; font-weight: 700; font-variant-numeric: tabular-nums;
}

/* ── Specifications ───────────────────────────────────────────────────── */
.lp-spec-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 20px; flex: 1; align-content: start; }
.lp-spec { display: flex; align-items: flex-start; gap: 10px; min-width: 0; }
.lp-spec__icon {
    width: 30px; height: 30px; border-radius: 6px; flex-shrink: 0;
    background: var(--surface-elevated); color: var(--text-2);
    display: flex; align-items: center; justify-content: center; font-size: 14px;
}
.lp-spec__body { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.lp-spec__label { font-size: 10.5px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-muted); }
.lp-spec__value { font-size: 13.5px; font-weight: 600; color: var(--text); }

.lp-prose { font-size: 13.5px; line-height: 21px; color: var(--text-2); margin: 0; white-space: pre-line; flex: 1; }

/* ── Recorded by ──────────────────────────────────────────────────────── */
.lp-recorder { display: flex; align-items: center; gap: 12px; }
.lp-recorder__avatar {
    width: 40px; height: 40px; border-radius: 999px; flex-shrink: 0;
    background: var(--accent-soft); color: #C2410C; border: 1px solid transparent;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700;
}
.lp-recorder__body { min-width: 0; }
.lp-recorder__name { font-size: 14px; font-weight: 700; color: var(--text); line-height: 1.3; }
.lp-recorder__role { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

/* ── Buttons ──────────────────────────────────────────────────────────── */
.lp-btn {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    height: 32px; padding: 0 13px; border-radius: 6px;
    font-size: 12.5px; font-weight: 600; border: 1px solid transparent;
    cursor: pointer; transition: opacity 120ms ease;
}
.lp-btn--sm { height: 28px; padding: 0 10px; font-size: 12px; }
.lp-btn--sm .el-icon { font-size: 13px; }
.lp-btn--primary { background: var(--primary); color: #fff; }
.lp-btn--primary:hover { opacity: 0.88; }

/* ── Linked batches ───────────────────────────────────────────────────── */
.lp-batch-list { display: flex; flex-direction: column; }
.lp-batch-row {
    display: flex; align-items: center; gap: 16px;
    padding: 14px 4px; border-bottom: 1px solid var(--border);
    text-decoration: none; color: inherit;
    transition: background 120ms ease;
}
.lp-batch-row:last-child { border-bottom: none; }
.lp-batch-row:hover { background: var(--surface-muted); margin: 0 -12px; padding: 14px 16px; border-radius: 8px; }
.lp-batch-row:hover .lp-batch-row__icon { background: var(--accent-soft); color: #C2410C; }
.lp-batch-row:hover .lp-batch-row__chevron { opacity: 1; transform: translateX(0); }
.lp-batch-row--static { cursor: default; align-items: flex-start; }
.lp-batch-row--static:hover { background: none; margin: 0; padding: 13px 4px; border-radius: 0; }
.lp-batch-row--static:hover .lp-batch-row__icon { background: var(--surface-elevated); color: var(--text-2); }
.lp-batch-row--static .lp-batch-row__main { align-items: flex-start; }
.lp-batch-row--static .lp-batch-row__icon { margin-top: 1px; }
.lp-batch-row--static .lp-batch-row__stat { padding-top: 1px; }
.lp-farm-facts { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 7px; }
.lp-farm-fact {
    display: inline-flex; align-items: center; gap: 5px;
    height: 22px; padding: 0 8px; border-radius: 999px;
    background: var(--surface-elevated); color: var(--text-2);
    font-size: 11px; font-weight: 600;
}
.lp-farm-fact .el-icon { font-size: 11px; color: var(--text-muted); }
.lp-batch-row__main { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }
.lp-batch-row__icon {
    width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
    background: var(--surface-elevated); color: var(--text-2);
    display: flex; align-items: center; justify-content: center; font-size: 15px;
    transition: background 120ms ease, color 120ms ease;
}
.lp-batch-row__icon--farm { background: var(--success-soft); color: var(--success); }
.lp-batch-row--static:hover .lp-batch-row__icon--farm { background: var(--success-soft); color: var(--success); }
.lp-batch-row__icon--collection { background: var(--warning-soft); color: var(--warning); }
.lp-batch-row:hover .lp-batch-row__icon--collection { background: #FDE68A; color: #92400E; }
.lp-batch-row__body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.lp-batch-row__number { font-size: 13.5px; font-weight: 700; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lp-inline-code {
    display: inline-flex; align-items: center; vertical-align: middle; margin-left: 6px;
    padding: 1px 6px; border-radius: 4px;
    background: var(--surface-elevated); color: var(--text-muted);
    font-family: var(--font-mono); font-size: 10px; font-weight: 600;
}
.lp-grade-pill {
    display: inline-flex; align-items: center; vertical-align: middle; margin-left: 6px;
    padding: 1px 7px; border-radius: 999px;
    background: var(--accent-soft); color: #9A3412;
    font-family: var(--font-sans); font-size: 10px; font-weight: 700;
}
.lp-batch-row__meta { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lp-batch-row__meta-icon { font-size: 11px; margin-left: 2px; }
.lp-batch-row__stats { display: flex; align-items: center; gap: 18px; flex-shrink: 0; }
.lp-batch-row__stat { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; flex-shrink: 0; }
.lp-batch-row__stat-value { font-size: 12.5px; font-weight: 700; color: var(--text); white-space: nowrap; }
.lp-batch-row__stat-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); }
.lp-batch-row__status {
    flex-shrink: 0; text-transform: capitalize;
    padding: 3px 9px; border-radius: 999px;
    background: var(--surface-elevated); color: var(--text-2);
    font-size: 11px; font-weight: 600;
}
.lp-batch-row__status--good { background: var(--success-soft); color: var(--success); }
.lp-batch-row__status--warn { background: var(--warning-soft); color: var(--warning); }
.lp-batch-row__status--bad { background: #FEF2F2; color: var(--error); }
.lp-batch-row__chevron {
    flex-shrink: 0; font-size: 13px; color: var(--text-muted);
    opacity: 0; transform: translateX(-3px);
    transition: opacity 120ms ease, transform 120ms ease;
}

/* ── Origin & Provenance (batches + collections + farms combined) ─────── */
.lp-origin { display: flex; flex-direction: column; }
.lp-origin__batch { padding: 16px 4px; border-bottom: 1px solid var(--border); }
.lp-origin__batch:last-child { border-bottom: none; }
.lp-origin__batch-head { display: flex; align-items: flex-start; gap: 14px; }
.lp-origin__batch-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.lp-origin__batch-line { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.lp-origin__batch-number { font-size: 13.5px; font-weight: 700; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lp-origin__batch-meta { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-muted); }
.lp-origin__batch-stats { display: flex; align-items: center; gap: 18px; flex-shrink: 0; }
.lp-origin__stat { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; flex-shrink: 0; }
.lp-origin__stat-value { font-size: 12.5px; font-weight: 700; color: var(--text); white-space: nowrap; }
.lp-origin__stat-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); }

.lp-origin__sources { margin: 10px 0 0 0; padding-left: 18px; border-left: 2px solid var(--border); display: flex; flex-direction: column; gap: 8px; }
.lp-origin__source { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 12.5px; }
.lp-origin__source-icon { color: var(--text-muted); font-size: 13px; }
.lp-origin__source-code { font-weight: 600; color: var(--text); }
.lp-origin__source-qty { color: var(--text-2); font-weight: 600; }
.lp-origin__source-farm { color: var(--text-muted); }
.lp-origin__source-loc { color: var(--text-muted); }

.lp-origin__farms { margin: 10px 0 0 0; padding-left: 18px; border-left: 2px solid var(--success-soft); display: flex; flex-direction: column; gap: 8px; }
.lp-origin__farm { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 12.5px; }
.lp-origin__farm-icon { color: var(--success); font-size: 13px; flex-shrink: 0; }
.lp-origin__farm-name { font-weight: 600; color: var(--text); }
.lp-origin__farm-loc { color: var(--text-muted); }
.lp-origin__farm .lp-farm-facts { margin-top: 0; }

.lp-origin__recorder {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.lp-origin__recorder-date {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-2);
}
.lp-origin__recorder-date .el-icon { font-size: 13px; color: var(--text-muted); }

.lp-empty { font-size: 13px; color: var(--text-muted); margin: 0; }

/* ── Integrated activity log (inside Origin & Provenance) ──────────────── */
.lp-activity {
    margin-top: 18px;
    padding-top: 18px;
    border-top: 1px solid var(--border);
}
.lp-activity__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}
.lp-activity__heading {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}
.lp-activity__title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.lp-activity__title .el-icon { font-size: 13px; color: currentColor; }

/* ── Lot Activity ─────────────────────────────────────────────────────── */
.lp-activity-table-wrap { overflow-x: auto; }
.lp-activity-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.lp-activity-table thead th {
    text-align: left;
    padding: 0 0 10px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.lp-activity-table thead th:not(:first-child) { padding-left: 20px; }
.lp-activity-table tbody td {
    padding: 13px 0;
    border-bottom: 1px dashed var(--border);
    color: var(--text);
    vertical-align: top;
}
.lp-activity-table tbody td:not(:first-child) { padding-left: 20px; }
.lp-activity-table tbody tr:last-child td { border-bottom: none; padding-bottom: 0; }
.lp-activity-table__desc { color: var(--text-2); max-width: 360px; }
.lp-activity-table__date { color: var(--text-2); white-space: nowrap; }
.lp-activity-table__actions-head { width: 1%; }
.lp-activity-table__actions { width: 1%; text-align: right; }

.lp-event-pill {
    display: inline-flex; align-items: center;
    padding: 4px 11px; border-radius: 999px;
    background: var(--surface-elevated); color: var(--text-2);
    border: 1px solid var(--border);
    font-size: 11.5px; font-weight: 700;
    white-space: nowrap;
}

.lp-flavor-notes { margin-top: 18px; padding-top: 16px; border-top: 1px dashed var(--border); }
.lp-flavor-notes__chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }

.lp-activity-delete {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border: none; border-radius: 6px;
    background: transparent; color: var(--text-muted); cursor: pointer;
    transition: background 120ms ease, color 120ms ease;
}
.lp-activity-delete:hover { background: #FEF2F2; color: var(--error); }
.lp-activity-delete:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .lp-bento { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .lp-tile--hero { grid-column: span 2; }
    .lp-tile--specs, .lp-tile--wide { grid-column: span 2; }
    .lp-tile--full { grid-column: span 2; }
    .lp-tile-row { grid-template-columns: 1fr; }
}

@media (max-width: 639.98px) {
    .lp-bento { grid-template-columns: 1fr; }
    .lp-tile--hero, .lp-tile--specs, .lp-tile--wide, .lp-tile--full { grid-column: span 1; }
    .lp-tile--hero { grid-row: span 1; }
    .lp-hero__title { font-size: 20px; line-height: 26px; }
    .lp-spec-grid { grid-template-columns: 1fr; }
    .lp-batch-row { flex-wrap: wrap; }
    .lp-batch-row__chevron { display: none; }
    .lp-tile__head { flex-direction: column; align-items: stretch; }
}
</style>
