<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    ArrowLeft,
    ArrowRight,
    Box,
    CircleCheck,
    Clock,
    Coffee,
    Coin,
    Connection,
    Download,
    Files,
    FullScreen,
    Histogram,
    MapLocation,
    OfficeBuilding,
    Odometer,
    Position,
    Printer,
    Share,
    Ticket,
    Trophy,
    User,
} from '@element-plus/icons-vue';

const props = defineProps({
    lot: { type: Object, default: () => ({}) },
    batches: { type: Array, default: () => [] },
    timeline: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ batches: 0, collections: 0, farms: 0, farmers: 0 }) },
});

/* ── Formatters ─────────────────────────────────────────────────────────── */
const fmt = (v, d = 2) => {
    if (v === null || v === undefined || v === '' || Number.isNaN(Number(v))) return '—';
    return Number(v).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
};
const fmtQty = (v, unit = 'kg', d = 2) => (v === null || v === undefined || v === '') ? '—' : `${fmt(v, d)} ${unit}`;
const pct = (v) => (v === null || v === undefined || v === '') ? '—' : `${fmt(v, 1)}%`;
const score = (v) => (v === null || v === undefined || v === '') ? '—' : fmt(v, 1);

/* ── Lot display ────────────────────────────────────────────────────────── */
const lotTitle = computed(() => props.lot.lot_name || props.lot.lot_number || 'Lot');
const statusMap = {
    draft: { label: 'Draft', tone: 'warning' },
    ready: { label: 'Ready', tone: 'info' },
    listing_ready: { label: 'Listing Ready', tone: 'success' },
    tokenisation_ready: { label: 'Tokenised', tone: 'success' },
};
const statusInfo = computed(() => statusMap[props.lot.status] || { label: props.lot.status || 'Lot', tone: 'info' });

/* ── Journey timeline ───────────────────────────────────────────────────── */
const stageMeta = {
    collection: { label: 'Collected', tone: 'success' },
    batching: { label: 'Batched', tone: 'accent' },
    lotting: { label: 'Lot', tone: 'info' },
};
const timeline = computed(() => (props.timeline || []).map((e) => ({
    ...e,
    stageLabel: stageMeta[e.stage]?.label || e.stage,
    tone: stageMeta[e.stage]?.tone || 'neutral',
})));
const lastIdx = computed(() => timeline.value.length - 1);

/* ── Chain rollup (for the "origin" sidebar) ────────────────────────────── */
const allFarms = computed(() => {
    const seen = new Map();
    for (const b of props.batches) for (const f of b.farms || []) if (f && !seen.has(f.id)) seen.set(f.id, f);
    return [...seen.values()];
});
const allFarmers = computed(() => {
    const seen = new Map();
    for (const b of props.batches) for (const fm of b.farmers || []) if (fm && !seen.has(fm.id)) seen.set(fm.id, fm);
    return [...seen.values()];
});

function printPage() {
    window.print();
}
</script>

<template>
    <DesignPreviewLayout title="Lot Traceability">
        <div class="lt-page">
            <!-- ── Page header ──────────────────────────────────────────── -->
            <div class="lt-page-header">
                <div class="lt-page-header__text">
                    <Link :href="route('lot.show', lot.id)" class="lt-back">
                        <el-icon><ArrowLeft /></el-icon> Lot Profile
                    </Link>
                    <h1 class="lt-page-title">Traceability Record</h1>
                    <p class="lt-page-description">
                        The complete origin of this lot, traced from farm collection through batching to the final lot.
                    </p>
                </div>
                <div class="lt-page-header__actions">
                    <button type="button" class="lt-btn lt-btn--outline" @click="printPage">
                        <el-icon><Printer /></el-icon> Print
                    </button>
                    <a :href="route('lot.qr.download', lot.id)" class="lt-btn lt-btn--outline">
                        <el-icon><Download /></el-icon> QR Code
                    </a>
                    <button type="button" class="lt-btn lt-btn--primary">
                        <el-icon><Share /></el-icon> Share
                    </button>
                </div>
            </div>

            <!-- ── Hero: lot identity + chain stats ────────────────────────── -->
            <div class="lt-hero">
                <div class="lt-hero__main">
                    <div class="lt-hero__photo">
                        <img v-if="lot.image" :src="`/storage/${lot.image}`" :alt="lotTitle" />
                        <el-icon v-else :size="30"><Ticket /></el-icon>
                    </div>
                    <div class="lt-hero__intro">
                        <span class="lt-badge" :class="`lt-badge--${statusInfo.tone}`"><span class="lt-badge__dot"></span>{{ statusInfo.label }}</span>
                        <h2 class="lt-hero__title">{{ lotTitle }}</h2>
                        <span class="lt-hero__code lt-mono">{{ lot.lot_number }}</span>
                    </div>
                </div>
                <div class="lt-hero__chips">
                    <span class="lt-chip"><el-icon><Connection /></el-icon> Traceable</span>
                    <span class="lt-chip"><el-icon><OfficeBuilding /></el-icon> {{ stats.farms }} {{ stats.farms === 1 ? 'Farm' : 'Farms' }}</span>
                    <span class="lt-chip"><el-icon><User /></el-icon> {{ stats.farmers }} {{ stats.farmers === 1 ? 'Farmer' : 'Farmers' }}</span>
                    <span class="lt-chip"><el-icon><Coffee /></el-icon> {{ stats.collections }} Collections</span>
                    <span class="lt-chip"><el-icon><Box /></el-icon> {{ stats.batches }} {{ stats.batches === 1 ? 'Batch' : 'Batches' }}</span>
                </div>
            </div>
        </div>
    </DesignPreviewLayout>
</template>
