<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import AttachBatchModal from '@/Components/Modals/AttachBatchModal.vue';
import {
    ArrowDown,
    ArrowRight,
    Box,
    Clock,
    Coffee,
    Coin,
    Connection,
    Document,
    Download,
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
    Ticket,
    Trophy,
    User,
} from '@element-plus/icons-vue';

const props = defineProps({
    lot: { type: Object, default: () => ({}) },
});

const showAttachBatch = ref(false);

function handleOptionsCommand(command) {
    if (command === 'traceability') {
        router.visit(route('lot.traceability', props.lot.id));
        return;
    }

    if (command === 'publish') {
        router.post(route('lot.publish', props.lot.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                const flash = usePage().props.flash || {};
                if (flash.error) {
                    ElNotification({ title: 'Already Published', message: flash.error, type: 'warning', duration: 3600, offset: 84 });
                } else {
                    ElNotification({ title: 'Published', message: flash.success || 'Lot published to market successfully.', type: 'success', duration: 3200, offset: 84 });
                }
            },
        });
    }
}

const linkedBatches = computed(() => props.lot.lot_batches || []);

/* ── Traceability QR code ─────────────────────────────────────────────── */
// The QR encodes the traceability URL; keep a visible copy next to the code.
const traceabilityUrl = computed(() => (props.lot.id ? route('lot.traceability', props.lot.id) : ''));

function downloadQrCode() {
    if (!props.lot.qr_code) return;

    const blob = new Blob([props.lot.qr_code], { type: 'image/svg+xml' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${props.lot.lot_number || 'lot'}-qr.svg`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

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

const qualityKnown = computed(() => props.lot.quality_score !== null && props.lot.quality_score !== undefined);
const qualityTone = computed(() => {
    if (!qualityKnown.value) return 'neutral';
    return Number(props.lot.quality_score) >= 80 ? 'good' : 'warn';
});
const qualityLabel = computed(() => {
    if (!qualityKnown.value) return 'Not yet graded';
    return Number(props.lot.quality_score) >= 80 ? 'Specialty grade' : 'Below specialty';
});

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

const sourcedFarms = computed(() => {
    const byFarm = new Map();
    for (const row of sourcedCollections.value) {
        const farm = row.link.farm_collection?.farm;
        if (!farm) continue;
        if (!byFarm.has(farm.id)) {
            byFarm.set(farm.id, { farm, batchNumbers: new Set(), collectionCount: 0 });
        }
        const entry = byFarm.get(farm.id);
        entry.batchNumbers.add(row.batch.batch_number);
        entry.collectionCount += 1;
    }
    return Array.from(byFarm.values()).map((entry) => ({
        farm: entry.farm,
        collectionCount: entry.collectionCount,
        batchLabel: Array.from(entry.batchNumbers).join(', '),
    }));
});

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
                    <button type="button" class="lp-btn lp-btn--outline">
                        Options <el-icon class="lp-caret"><ArrowDown /></el-icon>
                    </button>
                    <template #dropdown>
                        <el-dropdown-menu class="lp-options-menu">
                            <el-dropdown-item command="traceability"><el-icon><Connection /></el-icon> View Traceability</el-dropdown-item>
                            <el-dropdown-item v-if="lot.can_manage" command="publish"><el-icon><Promotion /></el-icon> Publish to Market</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>

            <!-- ── Bento mosaic ─────────────────────────────────────────── -->
            <div class="lp-bento">
                <!-- Hero tile -->
                <div class="lp-tile lp-tile--hero">
                    <div class="lp-hero__top">
                        <div class="lp-hero__photo">
                            <img v-if="lot.image" :src="`/storage/${lot.image}`" :alt="lot.lot_name || lot.lot_number" />
                            <el-icon v-else :size="30"><Ticket /></el-icon>
                        </div>
                        <div class="lp-hero__intro">
                            <span class="lp-badge" :class="`lp-badge--${statusInfo.tone}`"><span class="lp-badge__dot"></span>{{ statusInfo.label }}</span>
                            <h2 class="lp-hero__title">{{ lot.lot_name || lot.lot_number }}</h2>
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
                    <div class="lp-kpi__top">
                        <span class="lp-kpi__icon lp-kpi__icon--weight"><el-icon><Odometer /></el-icon></span>
                        <span class="lp-tile__label">Net Weight</span>
                    </div>
                    <span class="lp-tile__value lp-mono">{{ fmtNumber(lot.net_weight_kg) }}<small>kg</small></span>
                    <span class="lp-tile__sub">{{ lot.quantity_bags ? `${fmtNumber(lot.quantity_bags, 0)} bags total` : 'Bag count not recorded' }}</span>
                </div>
                <div class="lp-tile lp-tile--stat">
                    <div class="lp-kpi__top">
                        <span class="lp-kpi__icon lp-kpi__icon--bags"><el-icon><Box /></el-icon></span>
                        <span class="lp-tile__label">Bags</span>
                    </div>
                    <span class="lp-tile__value lp-mono">{{ fmtNumber(lot.quantity_bags, 0) }}</span>
                    <span class="lp-tile__sub">{{ lot.bag_weight_kg ? `${fmtNumber(lot.bag_weight_kg)} kg each` : 'Bag weight not recorded' }}</span>
                </div>
                <div class="lp-tile lp-tile--stat lp-tile--accent">
                    <div class="lp-kpi__top">
                        <span class="lp-kpi__icon lp-kpi__icon--price"><el-icon><Coin /></el-icon></span>
                        <span class="lp-tile__label">Price / kg</span>
                    </div>
                    <span class="lp-tile__value lp-mono">{{ lot.price ? `$${fmtNumber(lot.price)}` : '—' }}</span>
                    <span class="lp-tile__sub">{{ lotValueTotal !== null ? `$${fmtNumber(lotValueTotal)} total value` : 'Total value pending' }}</span>
                </div>
                <div class="lp-tile lp-tile--stat">
                    <div class="lp-kpi__top">
                        <span class="lp-kpi__icon" :class="`lp-kpi__icon--quality-${qualityTone}`"><el-icon><Trophy /></el-icon></span>
                        <span class="lp-tile__label">Quality Score</span>
                    </div>
                    <span class="lp-tile__value lp-mono">{{ lot.quality_score ? fmtNumber(lot.quality_score) : '—' }}<small v-if="lot.quality_score">/100</small></span>
                    <span class="lp-tile__sub">{{ qualityLabel }}</span>
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
                    </div>
                </div>

                <!-- Recorded by -->
                <div class="lp-tile lp-tile--recorder">
                    <h2 class="lp-tile__title"><el-icon><User /></el-icon> Recorded By</h2>
                    <div class="lp-recorder">
                        <div class="lp-recorder__avatar">{{ recorderInitials }}</div>
                        <div class="lp-recorder__body">
                            <div class="lp-recorder__name">{{ lot.user?.name || 'Unknown' }}</div>
                            <div class="lp-recorder__role">Lot Creator</div>
                        </div>
                    </div>
                    <div class="lp-recorder__meta-row">
                        <el-icon><Clock /></el-icon>
                        <span class="lp-mono">{{ fmtDateTime(lot.created_at) }}</span>
                    </div>
                </div>

                <!-- Batches count -->
                <div class="lp-tile lp-tile--stat">
                    <span class="lp-tile__label"><el-icon><Files /></el-icon> Batches Linked</span>
                    <span class="lp-tile__value lp-mono">{{ linkedBatches.length }}</span>
                </div>

                <!-- Description -->
                <div v-if="lot.description" class="lp-tile lp-tile--wide">
                    <h2 class="lp-tile__title"><el-icon><Document /></el-icon> Description</h2>
                    <p class="lp-prose">{{ lot.description }}</p>
                </div>

                <!-- Notes -->
                <div v-if="lot.notes" class="lp-tile lp-tile--wide">
                    <h2 class="lp-tile__title"><el-icon><EditPen /></el-icon> Notes</h2>
                    <p class="lp-prose">{{ lot.notes }}</p>
                </div>

                <!-- Traceability row: QR code card + Linked Batches share one even row -->
                <div class="lp-tile-row lp-tile-row--2col lp-tile--full">
                <!-- Traceability QR code -->
                <div class="lp-tile lp-tile--qr">
                    <div class="lp-qr-card">
                        <div class="lp-qr-card__code-wrap">
                            <div v-if="lot.qr_code" class="lp-qr-card__code" v-html="lot.qr_code"></div>
                            <div v-else class="lp-qr-card__code lp-qr-card__code--empty"><el-icon :size="30"><FullScreen /></el-icon></div>
                            <span class="lp-qr-card__scan-ring" aria-hidden="true"></span>
                        </div>
                        <div class="lp-qr-card__info">
                            <span class="lp-qr-card__eyebrow"><el-icon><FullScreen /></el-icon> Traceability QR</span>
                            <h3 class="lp-qr-card__title">Scan to trace this lot</h3>
                            <p class="lp-qr-card__hint">
                                Point a phone camera at the code to open this lot's traceability timeline — origin, batches, and processing history.
                            </p>
                            <a :href="traceabilityUrl" class="lp-qr-card__url lp-mono">{{ traceabilityUrl }}</a>
                            <div class="lp-qr-card__actions">
                                <button v-if="lot.qr_code" type="button" class="lp-btn lp-btn--primary lp-btn--sm" @click="downloadQrCode">
                                    <el-icon><Download /></el-icon> Download SVG
                                </button>
                                <Link :href="traceabilityUrl" class="lp-btn lp-btn--outline lp-btn--sm">
                                    <el-icon><Connection /></el-icon> View Timeline
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Linked batches -->
                <div class="lp-tile">
                    <div class="lp-tile__head">
                        <h2 class="lp-tile__title"><el-icon><Files /></el-icon> Linked Batches</h2>
                        <button v-if="lot.can_manage" type="button" class="lp-btn lp-btn--primary" @click="showAttachBatch = true">
                            <el-icon><Plus /></el-icon> Attach Batch
                        </button>
                    </div>
                    <div v-if="linkedBatches.length" class="lp-batch-list">
                        <Link
                            v-for="lb in linkedBatches"
                            :key="lb.id"
                            :href="lb.batch ? route('batch.show', lb.batch.id) : '#'"
                            class="lp-batch-row"
                        >
                            <span class="lp-batch-row__main">
                                <span class="lp-batch-row__icon"><el-icon><Box /></el-icon></span>
                                <span class="lp-batch-row__body">
                                    <span class="lp-batch-row__number lp-mono">{{ lb.batch?.batch_number || lb.batch_number }}</span>
                                    <span class="lp-batch-row__meta">
                                        {{ lb.batch?.variety || '—' }}
                                        <span v-if="lb.batch?.warehouse_location"> &middot; <el-icon class="lp-batch-row__meta-icon"><Location /></el-icon>{{ lb.batch.warehouse_location }}</span>
                                    </span>
                                </span>
                            </span>
                            <span v-if="lb.allocation_kg" class="lp-batch-row__stat">
                                <span class="lp-batch-row__stat-value lp-mono">{{ fmtNumber(lb.allocation_kg) }} kg</span>
                                <span class="lp-batch-row__stat-label">Drawn</span>
                            </span>
                            <span v-if="lb.batch?.status" class="lp-batch-row__status" :class="`lp-batch-row__status--${batchStatusTone(lb.batch.status)}`">{{ lb.batch.status }}</span>
                            <el-icon class="lp-batch-row__chevron"><ArrowRight /></el-icon>
                        </Link>
                    </div>
                    <p v-else class="lp-empty">No batches linked to this lot yet. Attach one by its batch number to record where this lot's coffee came from.</p>
                </div>
                </div>

                <!-- Origin trace row: Farms and Farm Collections as columns -->
                <div class="lp-tile-row lp-tile-row--2col lp-tile--full">
                <!-- Farms: origin of the coffee behind this lot's batches -->
                <div class="lp-tile">
                    <div class="lp-tile__head">
                        <h2 class="lp-tile__title"><el-icon><OfficeBuilding /></el-icon> Farms</h2>
                        <span v-if="sourcedFarms.length" class="lp-tile__count">{{ sourcedFarms.length }}</span>
                    </div>
                    <div v-if="sourcedFarms.length" class="lp-batch-list">
                        <div v-for="entry in sourcedFarms" :key="entry.farm.id" class="lp-batch-row lp-batch-row--static">
                            <span class="lp-batch-row__main">
                                <span class="lp-batch-row__icon lp-batch-row__icon--farm"><el-icon><OfficeBuilding /></el-icon></span>
                                <span class="lp-batch-row__body">
                                    <span class="lp-batch-row__number">
                                        {{ entry.farm.name || `Farm #${entry.farm.id}` }}
                                        <span v-if="entry.farm.farm_code" class="lp-inline-code">{{ entry.farm.farm_code }}</span>
                                    </span>
                                    <span class="lp-batch-row__meta">
                                        {{ [entry.farm.district, entry.farm.region, entry.farm.country].filter(Boolean).join(', ') || 'Location not recorded' }}
                                        <span> &middot; via {{ entry.batchLabel }}</span>
                                    </span>
                                    <span class="lp-farm-facts">
                                        <span v-if="entry.farm.elevation !== null && entry.farm.elevation !== undefined" class="lp-farm-fact">
                                            <el-icon><Position /></el-icon>{{ Math.round(entry.farm.elevation) }}m
                                        </span>
                                        <span v-if="entry.farm.coffee_type" class="lp-farm-fact">
                                            <el-icon><Coffee /></el-icon>{{ entry.farm.coffee_type }}
                                        </span>
                                        <span v-if="entry.farm.coffee_area !== null && entry.farm.coffee_area !== undefined" class="lp-farm-fact">
                                            <el-icon><Odometer /></el-icon>{{ entry.farm.coffee_area }} ha
                                        </span>
                                    </span>
                                </span>
                            </span>
                            <span class="lp-batch-row__stat">
                                <span class="lp-batch-row__stat-value lp-mono">{{ entry.collectionCount }}</span>
                                <span class="lp-batch-row__stat-label">{{ entry.collectionCount === 1 ? 'Collection' : 'Collections' }}</span>
                            </span>
                        </div>
                    </div>
                    <p v-else class="lp-empty">No farm origin data available for the batches linked to this lot.</p>
                </div>

                <!-- Farm collections: the sourcing events behind this lot's batches -->
                <div class="lp-tile">
                    <div class="lp-tile__head">
                        <h2 class="lp-tile__title"><el-icon><Coffee /></el-icon> Farm Collections</h2>
                        <span v-if="sourcedCollections.length" class="lp-tile__count">{{ sourcedCollections.length }}</span>
                    </div>
                    <div v-if="sourcedCollections.length" class="lp-batch-list">
                        <Link
                            v-for="row in sourcedCollections"
                            :key="row.link.id"
                            :href="route('farm-collection.show', row.link.farm_collection_id)"
                            class="lp-batch-row"
                        >
                            <span class="lp-batch-row__main">
                                <span class="lp-batch-row__icon lp-batch-row__icon--collection"><el-icon><Coffee /></el-icon></span>
                                <span class="lp-batch-row__body">
                                    <span class="lp-batch-row__number lp-mono">
                                        {{ row.link.farm_collection_code }}
                                        <span v-if="row.link.farm_collection?.initial_grade" class="lp-grade-pill">{{ row.link.farm_collection.initial_grade }}</span>
                                    </span>
                                    <span class="lp-batch-row__meta">
                                        {{ row.link.farm_collection?.farm?.name || 'Unknown farm' }}
                                        <span> &middot; via {{ row.batch.batch_number }}</span>
                                    </span>
                                </span>
                            </span>
                            <span v-if="row.link.farm_collection?.quantity" class="lp-batch-row__stat">
                                <span class="lp-batch-row__stat-value lp-mono">{{ Number(row.link.farm_collection.quantity).toLocaleString() }} {{ row.link.farm_collection.unit || '' }}</span>
                                <span class="lp-batch-row__stat-label">Qty</span>
                            </span>
                            <span class="lp-batch-row__status" :class="`lp-batch-row__status--${batchStatusTone(row.link.farm_collection?.status)}`">{{ row.link.farm_collection?.status || row.link.status }}</span>
                            <el-icon class="lp-batch-row__chevron"><ArrowRight /></el-icon>
                        </Link>
                    </div>
                    <p v-else class="lp-empty">No farm collections linked via the batches attached to this lot.</p>
                </div>
                </div>
            </div>
        </div>

        <AttachBatchModal v-if="lot.can_manage" v-model="showAttachBatch" :lot-id="lot.id" />
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

    max-width: 1100px;
    margin: 0 auto;
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
.lp-hero__top { display: flex; align-items: center; gap: 16px; }
.lp-hero__photo {
    width: 72px; height: 72px; border-radius: 10px; flex-shrink: 0;
    background: var(--surface-elevated); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); overflow: hidden;
}
.lp-hero__photo img { width: 100%; height: 100%; object-fit: cover; }
.lp-hero__intro { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.lp-hero__title { font-size: 25px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
.lp-kpi__icon--quality-good { background: var(--success-soft); color: var(--success); }
.lp-kpi__icon--quality-warn { background: var(--warning-soft); color: var(--warning); }
.lp-kpi__icon--quality-neutral { background: var(--surface-elevated); color: var(--text-muted); }

/* ── Tile headers ─────────────────────────────────────────────────────── */
.lp-tile__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.lp-tile__head .lp-tile__title { margin: 0; }
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
.lp-recorder__meta-row {
    display: flex; align-items: center; gap: 6px;
    margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border);
    font-size: 11.5px; color: var(--text-2);
}
.lp-recorder__meta-row .el-icon { font-size: 12px; color: var(--text-muted); }

/* ── Traceability QR card ─────────────────────────────────────────────── */
/* A polished, gradient-panelled card: code on the left on a soft accent
   backdrop, description + actions on the right. */
.lp-tile--qr {
    background: linear-gradient(135deg, var(--surface-muted) 0%, var(--surface) 55%);
    overflow: hidden;
}
.lp-qr-card { display: flex; align-items: center; gap: 22px; flex: 1; }
.lp-qr-card__code-wrap {
    position: relative; flex-shrink: 0;
    width: 168px; height: 168px; border-radius: 14px;
    background: var(--accent-soft);
    display: flex; align-items: center; justify-content: center;
}
.lp-qr-card__scan-ring {
    position: absolute; inset: -1px; border-radius: inherit;
    border: 1.5px dashed rgba(194, 65, 12, 0.35);
    pointer-events: none;
}
.lp-qr-card__code {
    width: 132px; height: 132px;
    padding: 10px; border-radius: 10px;
    background: #fff; border: 1px solid var(--border);
    box-shadow: 0 8px 20px rgba(17, 24, 39, 0.10);
    display: flex; align-items: center; justify-content: center;
}
.lp-qr-card__code :deep(svg) { width: 100%; height: 100%; display: block; }
.lp-qr-card__code--empty { color: var(--text-muted); background: var(--surface-elevated); box-shadow: none; }
.lp-qr-card__info { min-width: 0; display: flex; flex-direction: column; gap: 9px; }
.lp-qr-card__eyebrow {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
    color: #C2410C;
}
.lp-qr-card__eyebrow .el-icon { font-size: 12px; }
.lp-qr-card__title { font-size: 17px; line-height: 22px; font-weight: 700; letter-spacing: -0.01em; color: var(--text); margin: 0; }
.lp-qr-card__hint { font-size: 12.5px; line-height: 18px; color: var(--text-2); margin: 0; }
.lp-qr-card__url { font-size: 11px; color: var(--text-muted); word-break: break-all; text-decoration: none; }
.lp-qr-card__url:hover { color: var(--text); text-decoration: underline; }
.lp-qr-card__actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }

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
    display: flex; align-items: center; gap: 14px;
    padding: 13px 4px; border-bottom: 1px solid var(--border);
    text-decoration: none; color: inherit;
    transition: background 120ms ease;
}
.lp-batch-row:last-child { border-bottom: none; }
.lp-batch-row:hover { background: var(--surface-muted); margin: 0 -12px; padding: 13px 16px; border-radius: 8px; }
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
    width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
    background: var(--surface-elevated); color: var(--text-2);
    display: flex; align-items: center; justify-content: center; font-size: 14px;
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

.lp-empty { font-size: 13px; color: var(--text-muted); margin: 0; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 1100px) { .lp-page { padding: 0 24px; } }

@media (max-width: 900px) {
    .lp-bento { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .lp-tile--hero { grid-column: span 2; }
    .lp-tile--specs, .lp-tile--wide { grid-column: span 2; }
    .lp-tile--full { grid-column: span 2; }
    .lp-tile-row { grid-template-columns: 1fr; }
}

@media (max-width: 639.98px) {
    .lp-page { padding: 0 16px; }
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
