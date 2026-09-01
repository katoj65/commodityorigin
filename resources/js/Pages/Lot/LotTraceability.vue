<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    ArrowRight,
    Box,
    Check,
    Clock,
    Coffee,
    Connection,
    CopyDocument,
    Download,
    Files,
    FullScreen,
    HotWater,
    Link as LinkIcon,
    Location,
    Lock,
    Medal,
    Odometer,
    OfficeBuilding,
    Operation,
    Position,
    Printer,
    Share,
    Star,
    Ticket,
    Trophy,
    User,
    WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    lot: { type: Object, default: () => ({}) },
    blockchain: { type: Object, default: null },
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

/* ── Lot display ────────────────────────────────────────────────────────── */
const lotTitle = computed(() => props.lot.lot_name || props.lot.lot_number || 'Lot');
const statusMap = {
    draft: { label: 'Draft', tone: 'warning' },
    ready: { label: 'Ready', tone: 'info' },
    listing_ready: { label: 'Listing Ready', tone: 'success' },
    tokenisation_ready: { label: 'Tokenised', tone: 'success' },
    live: { label: 'Live', tone: 'success' },
};
const statusInfo = computed(() => statusMap[props.lot.status] || { label: props.lot.status || 'Lot', tone: 'info' });

const originFacts = computed(() => [
    { icon: Coffee, label: props.lot.variety },
    { icon: Location, label: props.lot.origin },
    { icon: Position, label: props.lot.region },
    { icon: Odometer, label: props.lot.altitude ? `${fmt(props.lot.altitude, 0)} m` : null },
    { icon: Clock, label: props.lot.year_of_harvest ? `${props.lot.year_of_harvest} harvest` : null },
].filter((f) => f.label));

const specs = computed(() => [
    { label: 'Variety', value: props.lot.variety, icon: Coffee },
    { label: 'Origin', value: props.lot.origin, icon: Location },
    { label: 'Region', value: props.lot.region, icon: Position },
    { label: 'Altitude', value: props.lot.altitude ? `${fmt(props.lot.altitude, 0)} m` : null, icon: Odometer },
    { label: 'Year of Harvest', value: props.lot.year_of_harvest, icon: Clock },
    { label: 'Process', value: props.lot.process, icon: HotWater },
    { label: 'Grade', value: props.lot.grade, icon: Medal },
    { label: 'Screen', value: props.lot.screen, icon: Operation },
    { label: 'Moisture', value: pct(props.lot.moisture), icon: WarningFilled },
    { label: 'Defects', value: pct(props.lot.defects_percentage), icon: WarningFilled },
    { label: 'Packaging', value: props.lot.packaging_type, icon: Box },
    { label: 'Acidity', value: props.lot.acidity !== null && props.lot.acidity !== undefined ? fmt(props.lot.acidity) : null, icon: Star },
    { label: 'Body', value: props.lot.body !== null && props.lot.body !== undefined ? fmt(props.lot.body) : null, icon: Star },
    { label: 'Flavor', value: props.lot.flavor !== null && props.lot.flavor !== undefined ? fmt(props.lot.flavor) : null, icon: Star },
    { label: 'Aroma', value: props.lot.aroma !== null && props.lot.aroma !== undefined ? fmt(props.lot.aroma) : null, icon: Star },
    { label: 'Balance', value: props.lot.balance !== null && props.lot.balance !== undefined ? fmt(props.lot.balance) : null, icon: Star },
    { label: 'Aftertaste', value: props.lot.aftertaste !== null && props.lot.aftertaste !== undefined ? fmt(props.lot.aftertaste) : null, icon: Star },
].filter((s) => s.value !== null && s.value !== undefined && s.value !== '—'));

/* ── Journey timeline ───────────────────────────────────────────────────── */
const stageMeta = {
    collection: { label: 'Collected', tone: 'success', icon: Coffee },
    batching: { label: 'Batched', tone: 'accent', icon: Box },
    lotting: { label: 'Lot Created', tone: 'info', icon: Ticket },
    blockchain: { label: 'Blockchain', tone: 'blockchain', icon: Lock },
};
const timeline = computed(() => (props.timeline || []).map((e) => ({
    ...e,
    stageLabel: stageMeta[e.stage]?.label || e.stage,
    tone: stageMeta[e.stage]?.tone || 'neutral',
    icon: stageMeta[e.stage]?.icon || Files,
})));

/* ── Blockchain hash copy ─────────────────────────────────────────────── */
const hashCopied = ref(false);
let copyResetTimer = null;
function copyHash() {
    if (!props.blockchain?.hash || !navigator.clipboard) return;
    navigator.clipboard.writeText(props.blockchain.hash).then(() => {
        hashCopied.value = true;
        clearTimeout(copyResetTimer);
        copyResetTimer = setTimeout(() => { hashCopied.value = false; }, 1500);
    });
}
const shortHash = (hash) => (hash ? `${hash.slice(0, 10)}…${hash.slice(-8)}` : '—');

/* ── Share / copy link ────────────────────────────────────────────────── */
const linkCopied = ref(false);
let linkResetTimer = null;
function shareLink() {
    if (navigator.share) {
        navigator.share({ title: lotTitle.value, url: props.lot.qr_url }).catch(() => {});
        return;
    }
    if (!navigator.clipboard || !props.lot.qr_url) return;
    navigator.clipboard.writeText(props.lot.qr_url).then(() => {
        linkCopied.value = true;
        clearTimeout(linkResetTimer);
        linkResetTimer = setTimeout(() => { linkCopied.value = false; }, 1500);
    });
}

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
                    <h1 class="lt-page-title">Traceability Record</h1>
                    <p class="lt-page-description">
                        The complete, verifiable origin of this lot — traced from farm collection through batching to the final lot.
                    </p>
                </div>
                <div class="lt-page-header__actions">
                    <button type="button" class="lt-btn lt-btn--outline" @click="printPage">
                        <el-icon><Printer /></el-icon> Print
                    </button>
                    <a :href="route('lot.qr.download', lot.id)" class="lt-btn lt-btn--outline">
                        <el-icon><Download /></el-icon> QR Code
                    </a>
                    <button type="button" class="lt-btn lt-btn--primary" @click="shareLink">
                        <el-icon><component :is="linkCopied ? Check : Share" /></el-icon> {{ linkCopied ? 'Copied!' : 'Share' }}
                    </button>
                </div>
            </div>

            <!-- ── Hero: lot identity + origin facts ───────────────────────── -->
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
                <div v-if="originFacts.length" class="lt-hero__facts">
                    <span v-for="(fact, i) in originFacts" :key="i" class="lt-fact">
                        <el-icon><component :is="fact.icon" /></el-icon>{{ fact.label }}
                    </span>
                </div>
                <div class="lt-hero__chips">
                    <span class="lt-chip"><el-icon><Connection /></el-icon> Traceable</span>
                    <span class="lt-chip"><el-icon><OfficeBuilding /></el-icon> {{ stats.farms }} {{ stats.farms === 1 ? 'Farm' : 'Farms' }}</span>
                    <span class="lt-chip"><el-icon><User /></el-icon> {{ stats.farmers }} {{ stats.farmers === 1 ? 'Farmer' : 'Farmers' }}</span>
                    <span class="lt-chip"><el-icon><Coffee /></el-icon> {{ stats.collections }} Collections</span>
                    <span class="lt-chip"><el-icon><Box /></el-icon> {{ stats.batches }} {{ stats.batches === 1 ? 'Batch' : 'Batches' }}</span>
                </div>
            </div>

            <!-- ── Specifications + Verification row ───────────────────────── -->
            <div class="lt-row lt-row--2col">
                <div class="lt-tile">
                    <h2 class="lt-tile__title"><el-icon><Operation /></el-icon> Specifications</h2>
                    <div v-if="specs.length" class="lt-spec-grid">
                        <div v-for="spec in specs" :key="spec.label" class="lt-spec">
                            <span class="lt-spec__icon"><el-icon><component :is="spec.icon" /></el-icon></span>
                            <div class="lt-spec__body">
                                <span class="lt-spec__label">{{ spec.label }}</span>
                                <strong class="lt-spec__value">{{ spec.value }}</strong>
                            </div>
                        </div>
                    </div>
                    <p v-else class="lt-empty">No specification data recorded for this lot yet.</p>
                    <div v-if="props.lot.flavors?.length" class="lt-flavor-notes">
                        <span class="lt-spec__label">Flavor Notes</span>
                        <div class="lt-flavor-notes__chips">
                            <span v-for="name in props.lot.flavors" :key="name" class="lt-chip">{{ name }}</span>
                        </div>
                    </div>
                </div>

                <div class="lt-tile">
                    <h2 class="lt-tile__title"><el-icon><Lock /></el-icon> Verify This Lot</h2>
                    <div class="lt-verify">
                        <div class="lt-verify__qr">
                            <div v-if="lot.qr_code" class="lt-verify__qr-code" v-html="lot.qr_code"></div>
                            <div v-else class="lt-verify__qr-code lt-verify__qr-code--empty"><el-icon :size="24"><FullScreen /></el-icon></div>
                            <span class="lt-verify__qr-caption"><el-icon><FullScreen /></el-icon>Scan to reopen this record</span>
                        </div>

                        <div v-if="blockchain" class="lt-chain">
                            <div class="lt-chain__status">
                                <span class="lt-badge lt-badge--success"><span class="lt-badge__dot"></span>{{ blockchain.status }}</span>
                                <span class="lt-chain__network">{{ blockchain.network }}</span>
                            </div>
                            <div class="lt-chain__row">
                                <span class="lt-chain__label">Block</span>
                                <span class="lt-chain__value lt-mono">#{{ blockchain.block_number }}</span>
                            </div>
                            <div class="lt-chain__row">
                                <span class="lt-chain__label">Hash</span>
                                <button type="button" class="lt-chain__hash lt-mono" :title="blockchain.hash" @click="copyHash">
                                    {{ shortHash(blockchain.hash) }}
                                    <el-icon :size="12"><component :is="hashCopied ? Check : CopyDocument" /></el-icon>
                                </button>
                            </div>
                            <div class="lt-chain__row">
                                <span class="lt-chain__label">Confirmations</span>
                                <span class="lt-chain__value lt-mono">{{ blockchain.confirmations }}</span>
                            </div>
                            <div class="lt-chain__row">
                                <span class="lt-chain__label">Committed</span>
                                <span class="lt-chain__value">{{ blockchain.committed_by?.name ? `by ${blockchain.committed_by.name}` : '—' }}</span>
                            </div>
                        </div>
                        <div v-else class="lt-chain lt-chain--pending">
                            <p>This lot has not been committed to the traceability blockchain yet.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Journey timeline ─────────────────────────────────────────── -->
            <div class="lt-tile lt-tile--full">
                <h2 class="lt-tile__title"><el-icon><Connection /></el-icon> Traceability Timeline</h2>
                <div v-if="timeline.length" class="lt-timeline">
                    <component
                        :is="event.href ? Link : 'div'"
                        v-for="event in timeline"
                        :key="`${event.stage}-${event.date}-${event.title}`"
                        :href="event.href || undefined"
                        class="lt-timeline__node"
                        :class="[`lt-timeline__node--${event.tone}`, { 'lt-timeline__node--linked': event.href }]"
                    >
                        <span class="lt-timeline__dot"><el-icon :size="11"><component :is="event.icon" /></el-icon></span>
                        <span class="lt-timeline__line"></span>
                        <div class="lt-timeline__body">
                            <div class="lt-timeline__meta">
                                <span class="lt-timeline__stage">{{ event.stageLabel }}</span>
                                <span class="lt-timeline__date lt-mono">{{ event.date }}</span>
                            </div>
                            <div class="lt-timeline__title">{{ event.title }}</div>
                            <div v-if="event.subtitle" class="lt-timeline__subtitle lt-mono">{{ event.subtitle }}</div>
                        </div>
                        <el-icon v-if="event.href" class="lt-timeline__chevron"><ArrowRight /></el-icon>
                    </component>
                </div>
                <p v-else class="lt-empty">No traceability events recorded yet.</p>
            </div>

            <!-- ── Origin chain: per-batch breakdown ────────────────────────── -->
            <div class="lt-tile lt-tile--full">
                <h2 class="lt-tile__title"><el-icon><Files /></el-icon> Origin Chain</h2>
                <p class="lt-tile__hint">Every batch this lot draws from, and the farm collections and farms behind each one.</p>

                <div v-if="batches.length" class="lt-batch-stack">
                    <article v-for="batch in batches" :key="batch.id" class="lt-batch-card">
                        <div class="lt-batch-card__head">
                            <div class="lt-batch-card__icon"><el-icon><Box /></el-icon></div>
                            <div class="lt-batch-card__intro">
                                <Link :href="route('batch.show', batch.id)" class="lt-batch-card__number lt-mono">{{ batch.batch_number }}</Link>
                                <span class="lt-batch-card__meta">
                                    {{ batch.variety || 'Variety pending' }}
                                    <span v-if="batch.processing_method"> · {{ batch.processing_method }}</span>
                                    <span v-if="batch.warehouse_location"> · <el-icon class="lt-inline-icon"><Location /></el-icon>{{ batch.warehouse_location }}</span>
                                </span>
                            </div>
                            <span v-if="batch.allocation_kg" class="lt-batch-card__stat">
                                <strong class="lt-mono">{{ fmtQty(batch.allocation_kg) }}</strong>
                                <span>Drawn</span>
                            </span>
                        </div>

                        <div class="lt-batch-card__facts">
                            <span v-if="batch.cup_score" class="lt-fact lt-fact--sm"><el-icon><Trophy /></el-icon>{{ fmt(batch.cup_score, 1) }} cup score</span>
                            <span v-if="batch.moisture_content" class="lt-fact lt-fact--sm"><el-icon><WarningFilled /></el-icon>{{ pct(batch.moisture_content) }} moisture</span>
                            <span v-if="batch.screen_size" class="lt-fact lt-fact--sm"><el-icon><Operation /></el-icon>Screen {{ batch.screen_size }}</span>
                            <span v-if="batch.drying_method" class="lt-fact lt-fact--sm"><el-icon><HotWater /></el-icon>{{ batch.drying_method }}</span>
                            <span v-if="batch.processing_date" class="lt-fact lt-fact--sm"><el-icon><Clock /></el-icon>{{ batch.processing_date }}</span>
                        </div>

                        <div v-if="(batch.collections || []).length" class="lt-collection-list">
                            <Link
                                v-for="collection in batch.collections"
                                :key="collection.id"
                                :href="route('farm-collection.show', collection.id)"
                                class="lt-collection-row"
                            >
                                <span class="lt-collection-row__icon"><el-icon><Coffee /></el-icon></span>
                                <span class="lt-collection-row__body">
                                    <span class="lt-collection-row__code lt-mono">
                                        {{ collection.collection_code }}
                                        <span v-if="collection.initial_grade" class="lt-grade-pill">{{ collection.initial_grade }}</span>
                                    </span>
                                    <span class="lt-collection-row__meta">
                                        {{ collection.farm?.name || 'Unknown farm' }}
                                        <span v-if="collection.farm?.location"> · {{ collection.farm.location }}</span>
                                        <span v-if="collection.farm?.elevation_m"> · {{ collection.farm.elevation_m }}m</span>
                                    </span>
                                </span>
                                <span v-if="collection.quantity" class="lt-collection-row__stat">
                                    <strong class="lt-mono">{{ fmtQty(collection.quantity, collection.unit || 'kg') }}</strong>
                                </span>
                                <el-icon class="lt-collection-row__chevron"><ArrowRight /></el-icon>
                            </Link>
                        </div>
                        <p v-else class="lt-empty lt-empty--sm">No farm collections linked to this batch.</p>
                    </article>
                </div>
                <p v-else class="lt-empty">No batches are linked to this lot yet — traceability begins once a batch is attached.</p>
            </div>

            <!-- ── Recorded by ──────────────────────────────────────────────── -->
            <div class="lt-tile lt-tile--full lt-tile--footer">
                <div class="lt-recorder">
                    <div class="lt-recorder__avatar"><el-icon><User /></el-icon></div>
                    <div class="lt-recorder__body">
                        <span class="lt-recorder__label">Recorded by</span>
                        <span class="lt-recorder__name">{{ lot.recorded_by?.name || 'Unknown' }}</span>
                    </div>
                </div>
                <div class="lt-recorder__meta">
                    <el-icon><Clock /></el-icon>
                    <span class="lt-mono">{{ lot.created_at }}</span>
                </div>
                <a :href="lot.qr_url" class="lt-recorder__link lt-mono">
                    <el-icon><LinkIcon /></el-icon>{{ lot.qr_url }}
                </a>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Tokens (mirrors LotProfile.vue's .lp-page palette) ─────────────────── */
.lt-page {
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
    --blockchain: #6D28D9;
    --blockchain-soft: #F3EEFF;
    --font-sans: Inter, system-ui, sans-serif;
    --font-mono: ui-monospace, 'SF Mono', 'JetBrains Mono', Consolas, monospace;

    background: var(--bg);
    color: var(--text);
    font-family: var(--font-sans);
    min-height: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.lt-mono { font-family: var(--font-mono); }

/* ── Page header ──────────────────────────────────────────────────────── */
.lt-page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 8px; }
.lt-page-header__text { min-width: 0; }
.lt-page-title { font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0 0 6px; }
.lt-page-description { font-size: 13.5px; line-height: 20px; color: var(--text-2); margin: 0; max-width: 58ch; }
.lt-page-header__actions { display: flex; gap: 8px; flex-shrink: 0; }

.lt-btn {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; white-space: nowrap;
    height: 32px; padding: 0 13px; border-radius: 6px;
    font-size: 12.5px; font-weight: 600; border: 1px solid transparent;
    cursor: pointer; transition: opacity 120ms ease; text-decoration: none;
}
.lt-btn--outline { background: var(--surface-muted); color: var(--text); border-color: var(--border); }
.lt-btn--outline:hover { background: var(--surface-elevated); }
.lt-btn--primary { background: var(--primary); color: #fff; }
.lt-btn--primary:hover { opacity: 0.88; }

/* ── Hero ─────────────────────────────────────────────────────────────── */
.lt-hero {
    background: var(--surface-muted); border: 1px solid var(--border); border-radius: 6px;
    padding: 20px; display: flex; flex-direction: column; gap: 16px;
}
.lt-hero__main { display: flex; align-items: center; gap: 16px; }
.lt-hero__photo {
    width: 64px; height: 64px; border-radius: 10px; flex-shrink: 0;
    background: var(--surface-elevated); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); overflow: hidden;
}
.lt-hero__photo img { width: 100%; height: 100%; object-fit: cover; }
.lt-hero__intro { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
.lt-hero__title { font-size: 22px; line-height: 27px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lt-hero__code { font-size: 12px; color: var(--text-muted); }

.lt-badge {
    display: inline-flex; align-items: center; gap: 6px; align-self: flex-start;
    height: 22px; padding: 0 9px; border-radius: 999px;
    font-size: 11px; font-weight: 600;
    background: var(--surface-elevated); border: 1px solid var(--border); color: var(--text-2);
}
.lt-badge__dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.lt-badge--success { color: var(--success); background: var(--success-soft); border-color: transparent; }
.lt-badge--warning { color: var(--warning); background: var(--warning-soft); border-color: transparent; }
.lt-badge--info { color: var(--info); background: #EFF6FF; border-color: transparent; }

.lt-hero__facts, .lt-hero__chips { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
.lt-hero__facts { padding-top: 14px; border-top: 1px solid var(--border); }
.lt-hero__chips { padding-top: 14px; border-top: 1px solid var(--border); }

.lt-fact, .lt-chip {
    display: inline-flex; align-items: center; gap: 6px;
    height: 26px; padding: 0 10px; border-radius: 999px;
    background: var(--surface-elevated); border: 1px solid var(--border);
    font-size: 12px; font-weight: 600; color: var(--text-2);
}
.lt-fact .el-icon, .lt-chip .el-icon { font-size: 12px; color: var(--text-muted); }
.lt-fact--sm { height: 24px; padding: 0 9px; font-size: 11.5px; }

/* ── Row / tile layout ────────────────────────────────────────────────── */
.lt-row { display: grid; gap: 14px; }
.lt-row--2col { grid-template-columns: repeat(2, minmax(0, 1fr)); }

.lt-tile { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: 18px; }
.lt-tile--full { width: 100%; }
.lt-tile--footer { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.lt-tile__title {
    font-size: 13px; font-weight: 700; color: var(--text);
    margin: 0 0 14px; display: flex; align-items: center; gap: 6px;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.lt-tile__title .el-icon { font-size: 13px; }
.lt-tile__hint { font-size: 12.5px; color: var(--text-muted); margin: -8px 0 14px; }
.lt-empty { font-size: 13px; color: var(--text-muted); margin: 0; }
.lt-empty--sm { font-size: 12px; margin-top: 8px; }

/* ── Specifications ───────────────────────────────────────────────────── */
.lt-spec-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 18px; }
.lt-spec { display: flex; align-items: flex-start; gap: 10px; min-width: 0; }
.lt-spec__icon {
    width: 28px; height: 28px; border-radius: 6px; flex-shrink: 0;
    background: var(--surface-elevated); color: var(--text-2);
    display: flex; align-items: center; justify-content: center; font-size: 13px;
}
.lt-spec__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.lt-spec__label { font-size: 10.5px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-muted); }
.lt-flavor-notes { margin-top: 18px; padding-top: 16px; border-top: 1px dashed var(--border); }
.lt-flavor-notes__chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.lt-spec__value { font-size: 13px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Verify (QR + blockchain) ─────────────────────────────────────────── */
.lt-verify { display: flex; flex-direction: column; gap: 16px; }
.lt-verify__qr { display: flex; flex-direction: column; align-items: center; gap: 8px; padding-bottom: 4px; }
.lt-verify__qr-code {
    width: 92px; height: 92px; padding: 8px; border-radius: 10px;
    background: #fff; border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
}
.lt-verify__qr-code :deep(svg) { width: 100%; height: 100%; display: block; }
.lt-verify__qr-code--empty { color: var(--text-muted); background: var(--surface-elevated); }
.lt-verify__qr-caption { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: var(--text-muted); }
.lt-verify__qr-caption .el-icon { font-size: 11px; }

.lt-chain { display: flex; flex-direction: column; gap: 12px; }
.lt-chain__status { display: flex; align-items: center; justify-content: space-between; }
.lt-chain__network { font-size: 11.5px; color: var(--text-muted); }
.lt-chain__row { display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid var(--border); }
.lt-chain__label { font-size: 11.5px; font-weight: 600; color: var(--text-muted); }
.lt-chain__value { font-size: 12.5px; font-weight: 600; color: var(--text); }
.lt-chain__hash {
    display: inline-flex; align-items: center; gap: 6px;
    background: none; border: none; padding: 0; cursor: pointer;
    font-size: 12px; font-weight: 600; color: var(--text);
}
.lt-chain__hash:hover { color: var(--blockchain); }
.lt-chain__hash .el-icon { color: var(--text-muted); }
.lt-chain--pending {
    text-align: center; padding: 8px 0 0;
    border-top: 1px solid var(--border);
    color: var(--text-muted);
}
.lt-chain--pending p { font-size: 12.5px; margin: 0; }

/* ── Traceability timeline ────────────────────────────────────────────── */
.lt-timeline { display: flex; flex-direction: column; }
.lt-timeline__node {
    position: relative;
    display: flex; align-items: flex-start; gap: 14px;
    padding: 0 4px 32px;
    text-decoration: none; color: inherit;
}
.lt-timeline__node:last-child { padding-bottom: 0; }
.lt-timeline__node:last-child .lt-timeline__line { display: none; }
.lt-timeline__dot {
    position: relative; z-index: 1; flex-shrink: 0;
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: var(--surface-elevated); color: var(--text-2);
    border: 2px solid var(--surface);
    box-shadow: 0 0 0 1px var(--border);
}
.lt-timeline__node--success .lt-timeline__dot { background: var(--success-soft); color: var(--success); }
.lt-timeline__node--accent .lt-timeline__dot { background: var(--accent-soft); color: #C2410C; }
.lt-timeline__node--info .lt-timeline__dot { background: #EFF6FF; color: var(--info); }
.lt-timeline__node--blockchain .lt-timeline__dot { background: var(--blockchain-soft); color: var(--blockchain); }
.lt-timeline__line {
    position: absolute; left: 15.5px; top: 24px; bottom: 0;
    width: 1px; background: var(--border);
}
.lt-timeline__body { flex: 1; min-width: 0; padding-top: 1px; }
.lt-timeline__meta { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; }
.lt-timeline__stage {
    font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    color: var(--text-muted);
}
.lt-timeline__date { font-size: 11px; color: var(--text-muted); }
.lt-timeline__title { font-size: 13.5px; font-weight: 600; color: var(--text); }
.lt-timeline__subtitle { font-size: 12px; color: var(--text-2); margin-top: 2px; }
.lt-timeline__chevron {
    flex-shrink: 0; margin-top: 3px; font-size: 12px; color: var(--text-muted);
    opacity: 0; transform: translateX(-3px); transition: opacity 120ms ease, transform 120ms ease;
}
.lt-timeline__node--linked { cursor: pointer; border-radius: 8px; transition: background 120ms ease; }
.lt-timeline__node--linked:hover { background: var(--surface-muted); }
.lt-timeline__node--linked:hover .lt-timeline__chevron { opacity: 1; transform: translateX(0); }

/* ── Origin chain: batch cards ─────────────────────────────────────────── */
.lt-batch-stack { display: flex; flex-direction: column; gap: 14px; }
.lt-batch-card { border: 1px solid var(--border); border-radius: 6px; padding: 16px; background: var(--surface); }
.lt-batch-card__head { display: flex; align-items: center; gap: 12px; }
.lt-batch-card__icon {
    width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0;
    background: var(--surface-elevated); color: var(--text-2);
    display: flex; align-items: center; justify-content: center; font-size: 15px;
}
.lt-batch-card__intro { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.lt-batch-card__number { font-size: 14px; font-weight: 700; color: var(--text); text-decoration: none; }
.lt-batch-card__number:hover { text-decoration: underline; }
.lt-batch-card__meta { display: flex; align-items: center; gap: 3px; font-size: 12px; color: var(--text-muted); }
.lt-inline-icon { font-size: 11px; margin-left: 2px; }
.lt-batch-card__stat { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; flex-shrink: 0; font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; }
.lt-batch-card__stat strong { font-size: 13px; color: var(--text); text-transform: none; letter-spacing: 0; }

.lt-batch-card__facts { display: flex; flex-wrap: wrap; gap: 6px; margin: 12px 0 0; padding-top: 12px; border-top: 1px solid var(--border); }

.lt-collection-list { display: flex; flex-direction: column; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); }
.lt-collection-row {
    display: flex; align-items: center; gap: 12px;
    padding: 9px 4px; border-radius: 6px;
    text-decoration: none; color: inherit;
    transition: background 120ms ease;
}
.lt-collection-row:hover { background: var(--surface-muted); }
.lt-collection-row:hover .lt-collection-row__chevron { opacity: 1; transform: translateX(0); }
.lt-collection-row__icon {
    width: 28px; height: 28px; border-radius: 7px; flex-shrink: 0;
    background: var(--warning-soft); color: var(--warning);
    display: flex; align-items: center; justify-content: center; font-size: 12px;
}
.lt-collection-row__body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.lt-collection-row__code { font-size: 12.5px; font-weight: 700; color: var(--text); }
.lt-grade-pill {
    display: inline-flex; align-items: center; vertical-align: middle; margin-left: 6px;
    padding: 1px 7px; border-radius: 999px;
    background: var(--accent-soft); color: #9A3412;
    font-family: var(--font-sans); font-size: 10px; font-weight: 700;
}
.lt-collection-row__meta { font-size: 11.5px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lt-collection-row__stat { flex-shrink: 0; font-size: 12px; }
.lt-collection-row__chevron {
    flex-shrink: 0; font-size: 12px; color: var(--text-muted);
    opacity: 0; transform: translateX(-3px); transition: opacity 120ms ease, transform 120ms ease;
}

/* ── Recorded by footer ───────────────────────────────────────────────── */
.lt-recorder { display: flex; align-items: center; gap: 10px; }
.lt-recorder__avatar {
    width: 32px; height: 32px; border-radius: 999px; flex-shrink: 0;
    background: var(--accent-soft); color: #C2410C;
    display: flex; align-items: center; justify-content: center; font-size: 14px;
}
.lt-recorder__body { display: flex; flex-direction: column; gap: 1px; }
.lt-recorder__label { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); }
.lt-recorder__name { font-size: 13px; font-weight: 700; color: var(--text); }
.lt-recorder__meta { display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: var(--text-2); }
.lt-recorder__meta .el-icon { font-size: 12px; color: var(--text-muted); }
.lt-recorder__link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; color: var(--text-muted); text-decoration: none;
    max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.lt-recorder__link:hover { color: var(--text); text-decoration: underline; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .lt-row--2col { grid-template-columns: 1fr; }
}

@media (max-width: 639.98px) {
    .lt-page-header { flex-direction: column; align-items: stretch; }
    .lt-page-header__actions { flex-wrap: wrap; }
    .lt-spec-grid { grid-template-columns: 1fr; }
    .lt-tile--footer { flex-direction: column; align-items: flex-start; }
}

/* ── Print ────────────────────────────────────────────────────────────── */
@media print {
    .lt-page-header__actions { display: none; }
}
</style>
