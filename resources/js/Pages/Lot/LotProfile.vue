<script setup>
import { ref } from 'vue';
import { router, Link as InertiaLink } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import {
    PriceTag, Sell, Goods, EditPen, InfoFilled, PieChart, Lock,
    Connection, Check, Collection, Box, Document, Trophy, CircleCheck,
    LocationFilled, MapLocation, Shop, Link, Files, Clock, Cpu, WarningFilled,
    Right, TopRight, Download, Delete, Plus,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import EditLotModal from '@/Components/Modals/EditLotModal.vue';
import AttachBatchModal from '@/Components/Modals/AttachBatchModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import PublishToMarketButton from '@/Components/Button/PublishToMarketButton.vue';

/* ── Real Lot data, field by field, with dummy fallbacks ported from the
   "Lot #LOT-000124 — Institutional Master Record" reference mockup
   wherever the database has nothing to say. `lot` is LotResource for this
   record (real lot + batches + farm collections + farms + blockchain +
   activity log). The layout/markup below is unchanged — only the data
   feeding it. ───────────────────────────────────────────────────────── */
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
    paymentOptions: { type: Array, default: () => [] },
    deliveryTermsOptions: { type: Array, default: () => [] },
});
const l = props.lot ?? {};

/* A published lot is live on the market — editing/deleting it here could
   silently invalidate that listing, so both actions are disabled while
   published; use Unpublish first. */
const isPublished = Boolean(l.is_published);

/* ── Edit / delete — real actions against the real lot.update / lot.destroy
   routes (the backend for these already existed; only this page's wiring
   was missing). ─────────────────────────────────────────────────────── */
const editModalOpen = ref(false);
const deleteDialogOpen = ref(false);
const deleteLoading = ref(false);
const attachBatchModalOpen = ref(false);
const detachBatchDialogOpen = ref(false);
const detachBatchLoading = ref(false);

function confirmDelete() {
    if (!has(l.id)) return;
    deleteLoading.value = true;
    router.delete(route('lot.destroy', l.id), {
        onSuccess: () => {
            ElNotification({
                title: 'Lot Deleted',
                message: `Lot ${l.lot_number || `#${l.id}`} was deleted successfully.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
        },
        onError: () => {
            ElNotification({
                title: 'Delete Failed',
                message: 'This lot could not be deleted.',
                type: 'error',
                duration: 3200,
                offset: 84,
            });
        },
        onFinish: () => {
            deleteLoading.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

const has = (v) => v !== null && v !== undefined && v !== '';
const titleCase = (s) => String(s).replace(/[_-]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
const fmtLongDate = (s) => {
    if (!has(s)) return null;
    const d = new Date(String(s).replace(' ', 'T'));
    return isNaN(d) ? null : d.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });
};
const fmtActivityDate = (s) => {
    if (!has(s)) return null;
    const d = new Date(String(s).replace(' ', 'T'));
    return isNaN(d) ? null : d.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' }) + ' · ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};
const truncateHash = (h) => (has(h) ? `${String(h).slice(0, 6)}...${String(h).slice(-4)}` : null);

const lotBatches = l.lot_batches ?? [];
const primaryLotBatch = lotBatches[0] ?? null;
const primaryBatch = primaryLotBatch?.batch ?? null;
const collectionLinks = lotBatches.flatMap((lb) => lb.batch?.farm_collection_links ?? []);
const collectionUnits = [...new Set(collectionLinks.map((link) => link.farm_collection?.unit).filter(has))];
const collectionUnit = collectionUnits[0] || 'kg';
const totalCollectionQty = collectionUnits.length === 1
    ? collectionLinks.reduce((sum, link) => sum + (parseFloat(link.farm_collection?.quantity) || 0), 0)
    : null;
const farmsById = new Map();
collectionLinks.forEach((link) => {
    const farm = link.farm_collection?.farm;
    if (farm?.id) farmsById.set(farm.id, farm);
});
const uniqueFarms = [...farmsById.values()];
const primaryFarm = uniqueFarms[0] ?? null;

const lot = {
    code: l.lot_number || 'LOT-000124',
    name: l.lot_name || 'Uganda Robusta Reserve',
    status: has(l.status) ? titleCase(l.status) : 'Ready for Trading',
    origin: l.origin || 'Uganda',
    coffeeType: l.variety || 'Robusta (Fine Canephora)',
    createdDate: fmtLongDate(l.created_at) || '15 September 2026',
    hash: truncateHash(l.blockchain?.hash) || '0x8f2d...471c',
};

const marketQty = has(l.market?.quantity) ? parseFloat(l.market.quantity) : null;
const marketQtySub = marketQty !== null
    ? (has(l.net_weight_kg) ? `${marketQty.toLocaleString()} of ${parseFloat(l.net_weight_kg).toLocaleString()} kg total` : 'Listed on market')
    : (l.market ? 'Listed on market' : 'Not yet published to market');

const kpis = [
    { label: 'Available Quantity', value: marketQty !== null ? marketQty.toLocaleString() : '500', unit: l.market?.unit || 'kg', sub: marketQtySub, accent: 'success' },
    { label: 'Coffee Type', value: l.variety || 'Robusta', sub: l.process || 'Fine Canephora' },
    { label: 'Physical Grade', value: has(l.screen) ? `Screen ${l.screen}` : 'Screen 18', sub: has(l.grade) ? l.grade : '7.14mm (92.4% Ret.)', mono: true },
    { label: 'Processing', value: l.process || 'Natural', sub: primaryBatch?.drying_method || 'Raised Drying Beds' },
    { label: 'Origin Region', value: l.origin || 'Uganda', sub: primaryFarm?.district || l.region || 'Mukono District' },
    { label: 'Lot Status', value: has(l.status) ? titleCase(l.status) : 'Ready for Trading', sub: 'Escrow Ready', accent: 'dark' },
];

const aboutText = l.description || l.notes || '500 kg of Uganda Robusta coffee aggregated from verified farm collections and prepared for commercial trading. Sourced specifically from verified smallholder farmers along the Mukono basin under supervised zero-water natural sun-drying protocols.';
const checklist = [
    'Defect count verified by certified Q-Grader',
    'Hermetic GrainPro lining sealed in Kampala bonded depot',
    'Zero deforestation polygon match (EUDR Art. 9)',
];

/* Physical Quantity Allocation — sourced from this lot's market listing
   (markets.quantity / available_quantity / reserved_quantity). Allocated is
   derived (total minus available minus reserved) since markets has no
   dedicated "allocated" column. No market listing yet → all zero, not
   illustrative dummy data. */
const marketTotal = has(l.market?.quantity) ? parseFloat(l.market.quantity) : 0;
const marketAvailable = has(l.market?.available_quantity) ? parseFloat(l.market.available_quantity) : 0;
const marketReserved = has(l.market?.reserved_quantity) ? parseFloat(l.market.reserved_quantity) : 0;
const marketAllocated = Math.max(marketTotal - marketAvailable - marketReserved, 0);
const allocation = {
    total: marketTotal,
    allocated: marketAllocated,
    reserved: marketReserved,
    available: marketAvailable,
    depot: l.storage_profile?.warehouse || 'None',
};

const pipeline = [
    { num: 1, title: 'Farm Origin', sub: uniqueFarms.length ? `${uniqueFarms.length} Smallholder${uniqueFarms.length === 1 ? '' : 's'}` : '3 Smallholders', state: 'completed' },
    { num: 2, title: 'Farm Collections', sub: collectionLinks.length ? `${collectionLinks.length} Recorded${totalCollectionQty !== null ? ` (${totalCollectionQty.toLocaleString()} ${collectionUnit})` : ''}` : '3 Recorded (1,000 kg)', state: 'completed' },
    { num: 3, title: 'Milling Batch', sub: primaryBatch?.batch_number || 'BAT-2026-00082', state: 'completed' },
    { num: 4, title: 'Active Lot', sub: `${lot.code} (Current)`, state: 'active' },
];
const dummyCollections = [
    { id: null, code: 'FC-001', qty: '300 kg' },
    { id: null, code: 'FC-002', qty: '400 kg' },
    { id: null, code: 'FC-003', qty: '300 kg' },
];
/* Sourced from the lot_batch_farm_collection pivot directly (lot -> farm
   collection, one join) rather than the deeper lot_batches -> batch ->
   batch_farm_collection -> farm_collection chain used elsewhere on this
   page. */
const lotBatchFarmCollections = l.lot_batch_farm_collections ?? [];
const collections = lotBatchFarmCollections.length
    ? lotBatchFarmCollections.map((link) => ({
        id: link.farm_collection_id,
        code: link.farm_collection?.collection_code,
        qty: has(link.farm_collection?.quantity) ? `${link.farm_collection.quantity} ${link.farm_collection?.unit ?? 'kg'}` : '—',
    }))
    : dummyCollections;
/* A lot can have more than one linked batch (via lot_batch) — show every
   one of them, not just the first. */
const parentBatches = lotBatches
    .filter((lb) => lb.batch)
    .map((lb) => ({
        id: lb.batch.id,
        code: lb.batch.batch_number,
        qty: has(lb.batch.net_weight_kg) ? `${lb.batch.net_weight_kg} kg` : null,
    }));

const batchToDetach = ref(null);

function requestDetachBatch(batch) {
    batchToDetach.value = batch;
    detachBatchDialogOpen.value = true;
}

function confirmDetachBatch() {
    if (!batchToDetach.value) return;
    const batch = batchToDetach.value;
    detachBatchLoading.value = true;
    router.delete(route('lot.batches.destroy', { lot: l.id, batch: batch.id }), {
        preserveScroll: true,
        onSuccess: () => {
            ElNotification({
                title: 'Batch Removed',
                message: `Batch #${batch.code} was removed from this lot.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
            window.location.reload();
        },
        onError: () => {
            ElNotification({
                title: 'Remove Failed',
                message: 'This batch could not be removed from the lot.',
                type: 'error',
                duration: 3200,
                offset: 84,
            });
        },
        onFinish: () => {
            detachBatchLoading.value = false;
            detachBatchDialogOpen.value = false;
        },
    });
}

/* Trade names don't imply their species epithet (Robusta -> Coffea
   canephora, not "Coffea robusta") — look it up rather than guess. */
const speciesByVariety = { robusta: 'Coffea canephora', arabica: 'Coffea arabica', liberica: 'Coffea liberica', excelsa: 'Coffea excelsa' };
const coffeeSpecies = speciesByVariety[l.variety?.toLowerCase()] ?? null;

const specs = [
    { label: 'Lot Identifier', value: lot.code, mono: true },
    { label: 'Lot Name', value: lot.name },
    { label: 'Coffee Type / Species', value: l.variety ? `${l.variety}${coffeeSpecies ? ` (${coffeeSpecies})` : ''}` : 'Robusta (Coffea canephora)' },
    { label: 'Genetic Variety', value: 'NARO-Kituza KR Clones' },
    { label: 'Origin Country & Region', value: [l.origin, l.region].filter(has).join(', ') || 'Uganda, Central Mukono Basin' },
    { label: 'Harvest Period', value: has(l.year_of_harvest) ? `${l.year_of_harvest} Harvest` : 'Main Crop Nov 2025 – Jan 2026' },
    { label: 'Processing Method', value: [l.process, primaryBatch?.drying_method].filter(has).join(' — ') || 'Natural Sun-Dried (Raised Beds)' },
    { label: 'Physical Grade', value: l.grade || 'Uganda Fine Robusta Grade 1' },
    { label: 'Screen Size', value: has(l.screen) ? `Screen ${l.screen} standard` : 'Screen 18 (7.14mm standard)', mono: true },
    { label: 'Current Physical Quantity', value: has(l.net_weight_kg) ? `${l.net_weight_kg} kg` : '500 kg Remaining (700 kg Free)', mono: true, accent: true },
    { label: 'Standard Unit of Measure', value: has(l.bag_weight_kg) ? `Kilograms (kg) / ${l.bag_weight_kg}kg Bags` : 'Kilograms (kg) / 60kg Hermetic Bags', mono: true },
];

const dummyQualityFlavors = ['Dark Cocoa Nibs', 'Black Molasses', 'Toasted Walnut', 'Dried Black Cherry', 'Sweet Cedar', 'Raw Cane Sugar'];
const realFlavors = (l.flavors?.length ? l.flavors.map((f) => f.name) : [l.flavor, l.aroma, l.body, l.acidity, l.aftertaste].filter(has).map(titleCase));
const quality = {
    score: has(l.quality_score) ? Number(l.quality_score).toFixed(2) : '82.50',
    gradeLabel: 'Specialty Fine Robusta Grade',
    assessedDate: '14 Feb 2026',
    lab: 'Mukono Cupping Lab #3',
    metrics: [
        { label: 'Moisture', value: has(l.moisture) ? `${l.moisture}%` : '11.2%', sub: 'Optimal 10-12%' },
        { label: has(l.screen) ? `Screen ${l.screen}` : 'Screen 18', value: '92.4%', sub: 'Retention' },
        { label: 'Primary Defects', value: has(l.defects_percentage) ? `${l.defects_percentage}% / lot` : '0 / 350g', sub: 'Export Zero', accent: true },
        { label: 'Water Act.', value: '0.54 aw', sub: 'Safe <0.65' },
    ],
    flavors: realFlavors.length ? realFlavors : dummyQualityFlavors,
};

const originFacts = {
    countryDistrict: [primaryFarm?.country || l.origin, primaryFarm?.district].filter(has).join(', ') || 'Uganda, Mukono District',
    microclimate: [primaryFarm?.region ? `${primaryFarm.region} Region` : null, has(primaryFarm?.elevation) ? `${primaryFarm.elevation}m ASL` : null].filter(has).join(' · ') || 'Central Basin Microclimate · 1,220m ASL',
    coop: l.user?.name ? titleCase(l.user.name) : 'Mukono Smallholder Agro-Coop',
    leadProducer: primaryFarm ? `Lead Producer: ${primaryFarm.name}` : 'Lead Producer: John Kato (Kato Family Farm)',
    gps: has(primaryFarm?.latitude) && has(primaryFarm?.longitude) ? `${primaryFarm.latitude}° N, ${primaryFarm.longitude}° E` : '0.3542° N, 32.7481° E',
};
const mapSector = primaryFarm?.district ? `${primaryFarm.district} District (Farm ${primaryFarm.farm_code || primaryFarm.name})` : 'Mukono Cadastral Sector 4 (Plot 4B, 5A, 6C)';

/* No boolean verification flags exist on the Lot record to source these
   from honestly — this stays fully illustrative. */
const sustainBadges = ['Verified Origin', 'Traceable', 'Quality Verified', 'Certification Available'];
const dummySustainItems = [
    { title: 'EUDR Deforestation-Free Pass', sub: 'Satellite verified post-Dec 2020 zero cut', status: 'VERIFIED' },
    { title: 'Zero-Water Dry Footprint', sub: 'Raised African beds drying process', status: 'PASSED' },
    { title: 'Fair Producer Living Wage', sub: '+38% disbursed over market baseline', status: 'AUDITED' },
];
const sustainItems = primaryFarm?.certifications?.length
    ? primaryFarm.certifications.slice(0, 4).map((c) => ({ title: c.name, sub: c.description || `Certified for ${primaryFarm.name}`, status: 'VERIFIED' }))
    : dummySustainItems;

const dummyListings = [
    { channel: 'Product Profile', detail: 'Uganda Fine Robusta Screen 18', price: '$4.20 / kg', status: 'Active', style: 'success', action: 'View' },
    { channel: 'Bilateral Offer', detail: 'Direct Roaster Offer #OFF-0042', price: '$4.20 / kg (200kg)', status: 'Active', style: 'primary', action: 'View' },
    { channel: 'Live Auction', detail: 'Auction Terminal Lot #AUC-18', price: 'Starting $3.80 / kg', status: 'Not Active', style: 'neutral', action: 'Setup' },
    { channel: 'B2B RFQ', detail: '2 Institutional Requests pending', price: 'Custom CIF', status: '2 Pending', style: 'warning', action: 'Review' },
];
const listings = [
    l.market
        ? { channel: 'Product Profile', detail: l.market.title, price: has(l.price) ? `$${l.price} / kg` : dummyListings[0].price, status: titleCase(l.market.status), style: l.market.status === 'live' ? 'success' : 'neutral', action: 'View' }
        : dummyListings[0],
    ...dummyListings.slice(1),
];

const token = l.blockchain
    ? {
        id: `TKN-UG-${lot.code}`,
        chain: l.blockchain.network,
        volume: has(l.net_weight_kg) ? `${l.net_weight_kg} kg (1:1 Pegged)` : '500 kg (1:1 Pegged)',
        mintTimestamp: fmtActivityDate(l.blockchain.committed_at) || '15 Sep 2026 14:22 UTC',
        contractRef: truncateHash(l.blockchain.hash) || '0x39a04...8821f',
    }
    : {
        id: 'TKN-UG-LOT-000124',
        chain: 'Hedera Hashgraph (HCS)',
        volume: '500 kg (1:1 Pegged)',
        mintTimestamp: '15 Sep 2026 14:22 UTC',
        contractRef: '0x39a04...8821f',
    };

/* No document/certificate attachment schema exists for lots — this stays
   fully illustrative. */
const documents = [
    { name: 'Quality & Cupping Lab Report', meta: 'CQI-Q-Robusta · 82.50 pts · 1.2 MB' },
    { name: 'Certificate of Origin (Form O)', meta: 'UCDA-UG-2026-9092 · 840 KB' },
    { name: 'EUDR Geolocation Statement', meta: 'EU-REG-2023/1115 · 2.4 MB' },
    { name: 'Phytosanitary Inspection Pass', meta: 'MAAIF-PHYTO-UG · 620 KB' },
];

const dummyActivity = [
    { title: 'Lot Created & Allocated', text: '500 kg aggregated from Batch #BAT-001', date: '15 Sep 2026 · 10:14 AM' },
    { title: 'Quality & Defect Audit Recorded', text: '82.50 CQI Score and zero primary defects verified', date: '16 Sep 2026 · 02:40 PM' },
    { title: 'EUDR & Regulatory Verification', text: 'Satellite farm polygons certified deforestation-free', date: '18 Sep 2026 · 09:12 AM' },
    { title: 'Commercial Product Profile Published', text: 'Listed on marketplace at $4.20/kg FOB', date: '20 Sep 2026 · 11:30 AM' },
    { title: '200 kg Allocated to Offer', text: 'Bilateral order #OFF-0042 placed into escrow', date: 'Today · 08:15 AM' },
];
const activity = l.activities?.length
    ? l.activities.map((a) => ({ title: titleCase(a.event), text: a.description || '', date: fmtActivityDate(a.created_at) || '—' }))
    : dummyActivity;

/* Advisory copy — no AI scoring pipeline runs against real lots yet, so
   this stays fully illustrative. */
const aiInsight = {
    tag: 'Market Opportunity',
    tagSub: 'High Demand Match',
    text: 'Trading Readiness: 100%. Screen 18 retention (92.4%) combined with 82.50 Q-grade positions this lot in the top 5% of East African Fine Robustas. Priced at $4.20/kg, it offers a strong +12% margin premium over standard commercial FAQ grades.',
    buyerRelevance: 'Targeted at premium European espresso roasters seeking consistent single-origin crema density and low-astringency chocolate profiles.',
    disclaimer: 'AI provides analytical market intelligence only. Official physical quantities, provenance, and certifications remain immutable.',
};
</script>

<template>
    <DesignPreviewLayout title="Lot Profile">
        <div class="lp-page">
            <!-- ── Page header ───────────────────────────────────────────────────── -->
            <div class="lp-header">
                <div class="lp-header__text">
                    <div class="lp-header__title-row">
                        <h1 class="lp-header__title">Lot #{{ lot.code }}</h1>
                        <span class="lp-status-pill"><span class="lp-status-pill__dot"></span>{{ lot.status }}</span>
                        <span class="lp-check-pill"><el-icon><CircleCheck /></el-icon> Verified Inventory</span>
                    </div>
                    <div class="lp-header__meta">
                        <span><strong>Lot Name:</strong> {{ lot.name }}</span>
                        <span class="lp-dot">•</span>
                        <span><strong>Origin:</strong> {{ lot.origin }}</span>
                        <span class="lp-dot">•</span>
                        <span><strong>Coffee:</strong> {{ lot.coffeeType }}</span>
                        <span class="lp-dot">•</span>
                        <span><strong>Created:</strong> {{ lot.createdDate }}</span>
                        <span class="lp-dot">•</span>
                        <span class="lp-mono">Hash: {{ lot.hash }}</span>
                    </div>
                </div>
                <div class="lp-header__actions">
                    <PublishToMarketButton
                        :lot="l"
                        :currency-options="currencyOptions"
                        :currency-countries="currencyCountries"
                        :delivery-method-options="deliveryMethodOptions"
                        :incoterm-options="incotermOptions"
                        :payment-options="paymentOptions"
                        :delivery-terms-options="deliveryTermsOptions"
                    />
                    <button type="button" class="lp-btn lp-btn--outline"><el-icon><PriceTag /></el-icon> Create Offer</button>
                    <button type="button" class="lp-btn lp-btn--outline"><el-icon><Sell /></el-icon> Create Auction</button>
                    <button type="button" class="lp-btn lp-btn--outline"><el-icon><Goods /></el-icon> Create Product</button>
                    <button
                        type="button"
                        class="lp-btn lp-btn--outline"
                        :disabled="isPublished"
                        :title="isPublished ? 'Unpublish this lot from the market before editing it' : undefined"
                        @click="editModalOpen = true"
                    ><el-icon><EditPen /></el-icon> Edit Lot</button>
                    <button
                        type="button"
                        class="lp-btn lp-btn--outline lp-btn--danger"
                        :disabled="isPublished"
                        :title="isPublished ? 'Unpublish this lot from the market before deleting it' : undefined"
                        @click="deleteDialogOpen = true"
                    ><el-icon><Delete /></el-icon> Delete Lot</button>
                </div>
            </div>

            <!-- ── KPI strip ─────────────────────────────────────────────────────── -->
            <div class="lp-kpi-grid">
                <div v-for="k in kpis" :key="k.label" class="lp-kpi">
                    <span class="lp-eyebrow">{{ k.label }}</span>
                    <div class="lp-kpi__value" :class="{ 'lp-mono': k.mono, 'lp-accent-text': k.accent === 'success' }">{{ k.value }} <span v-if="k.unit">{{ k.unit }}</span></div>
                    <span class="lp-kpi__sub" :class="{ 'lp-accent-text': k.accent }">{{ k.sub }}</span>
                </div>
            </div>

            <!-- ── About this lot + allocation ──────────────────────────────────── -->
            <div class="lp-grid-5-7">
                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><InfoFilled /></el-icon> About This Lot</h2>
                        <span class="lp-tag-mono">Master Record</span>
                    </div>
                    <p class="lp-body-text">{{ aboutText }}</p>
                    <div class="lp-checklist-box">
                        <div class="lp-checklist-box__head">
                            <span>Commercial Readiness Checklist</span>
                            <span class="lp-check-badge">100% Compliant</span>
                        </div>
                        <div class="lp-checklist-box__item" v-for="c in checklist" :key="c">
                            <el-icon><Check /></el-icon>{{ c }}
                        </div>
                    </div>
                </div>

                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><PieChart /></el-icon> Physical Quantity Allocation</h2>
                        <span class="lp-tag-mono lp-tag-mono--success"><el-icon><Lock /></el-icon> Double-Sale Protection Active</span>
                    </div>
                    <div class="lp-alloc-head">
                        <span>Total Master Quantity: <strong>{{ allocation.total.toLocaleString() }} kg</strong></span>
                        <span class="lp-mono"><strong>{{ allocation.available.toLocaleString() }} kg</strong> Available for spot allocation</span>
                    </div>
                    <div class="lp-alloc-track">
                        <div class="lp-alloc-track__fill lp-alloc-track__fill--available" :style="{ width: (allocation.total ? allocation.available / allocation.total * 100 : 0) + '%' }"></div>
                        <div class="lp-alloc-track__fill lp-alloc-track__fill--allocated" :style="{ width: (allocation.total ? allocation.allocated / allocation.total * 100 : 0) + '%' }"></div>
                        <div class="lp-alloc-track__fill lp-alloc-track__fill--reserved" :style="{ width: (allocation.total ? allocation.reserved / allocation.total * 100 : 0) + '%' }"></div>
                    </div>
                    <div class="lp-alloc-stats">
                        <div class="lp-alloc-stat"><el-icon><Box /></el-icon><span>Original</span><strong>{{ allocation.total.toLocaleString() }} kg</strong></div>
                        <div class="lp-alloc-stat"><el-icon class="lp-alloc-stat--allocated"><TopRight /></el-icon><span>Allocated</span><strong class="lp-alloc-stat--allocated">{{ allocation.allocated.toLocaleString() }} kg</strong><small class="lp-alloc-stat__sub">of {{ allocation.total.toLocaleString() }} kg original</small></div>
                        <div class="lp-alloc-stat"><el-icon class="lp-alloc-stat--reserved"><Lock /></el-icon><span>Reserved</span><strong class="lp-alloc-stat--reserved">{{ allocation.reserved.toLocaleString() }} kg</strong></div>
                        <div class="lp-alloc-stat"><el-icon class="lp-alloc-stat--available"><CircleCheck /></el-icon><span>Available</span><strong class="lp-alloc-stat--available">{{ allocation.available.toLocaleString() }} kg</strong></div>
                    </div>
                    <div class="lp-info-strip">
                        <span><el-icon><InfoFilled /></el-icon> Allocated inventory prevents duplicate commercial listings.</span>
                        <span class="lp-strong">Depot #{{ allocation.depot }}</span>
                    </div>
                </div>
            </div>

            <!-- ── Traceability custody journey ─────────────────────────────────── -->
            <div class="lp-card">
                <div class="lp-card__head">
                    <div>
                        <h2 class="lp-card__title"><el-icon><Connection /></el-icon> Traceability Custody Journey</h2>
                        <span class="lp-muted">Upstream records contributing directly to this Lot's custody chain</span>
                    </div>
                    <button type="button" class="lp-btn lp-btn--outline lp-btn--sm">View Full Traceability <el-icon><Right /></el-icon></button>
                </div>
                <div class="lp-pipeline">
                    <div class="lp-pipeline__line"></div>
                    <div v-for="p in pipeline" :key="p.num" class="lp-pipeline__step" :class="`lp-pipeline__step--${p.state}`">
                        <div class="lp-pipeline__circle"><el-icon><component :is="p.state === 'active' ? Collection : Check" /></el-icon></div>
                        <div class="lp-pipeline__title">{{ p.num }}. {{ p.title }}</div>
                        <div class="lp-pipeline__sub lp-mono">{{ p.sub }}</div>
                    </div>
                </div>
                <div class="lp-grid-8-4">
                    <div class="lp-subpanel">
                        <span class="lp-strong">Contributing Farm Collections:</span>
                        <div class="lp-chip-row">
                            <component
                                :is="c.id ? InertiaLink : 'span'"
                                v-for="c in collections"
                                :key="c.code"
                                :href="c.id ? route('farm-collection.show', c.id) : undefined"
                                class="lp-chip"
                                :class="{ 'lp-chip--linked': c.id }"
                            >
                                <el-icon><Box /></el-icon> Collection <strong>#{{ c.code }}</strong>
                                <span class="lp-chip__qty lp-mono">{{ c.qty }}</span>
                                <el-icon><Right /></el-icon>
                            </component>
                        </div>
                    </div>
                    <div class="lp-subpanel">
                        <div class="lp-subpanel__head">
                            <span class="lp-strong">Parent Milling Batch:</span>
                            <button type="button" class="lp-btn lp-btn--outline lp-btn--sm" @click="attachBatchModalOpen = true">
                                <el-icon><Plus /></el-icon> Add Batch
                            </button>
                        </div>
                        <div v-if="!parentBatches.length" class="lp-batch-row">
                            <span><el-icon><Box /></el-icon> <strong>None</strong></span>
                        </div>
                        <div v-else class="lp-batch-list">
                            <component
                                :is="InertiaLink"
                                v-for="batch in parentBatches"
                                :key="batch.id"
                                :href="route('batch.show', batch.id)"
                                class="lp-batch-row lp-batch-row--linked"
                            >
                                <span :title="`Batch #${batch.code}`"><el-icon><Box /></el-icon> <strong>{{ `Batch #${batch.code}` }}</strong></span>
                                <span class="lp-batch-row__right">
                                    <span v-if="batch.qty" class="lp-tag-mono">{{ batch.qty }}</span>
                                    <button
                                        type="button"
                                        class="lp-batch-row__remove"
                                        title="Remove this batch from the lot"
                                        @click.stop.prevent="requestDetachBatch(batch)"
                                    >
                                        <el-icon><Delete /></el-icon>
                                    </button>
                                </span>
                            </component>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Specifications + quality ──────────────────────────────────────── -->
            <div class="lp-grid-6-6">
                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><Document /></el-icon> Physical &amp; Botanical Specifications</h2>
                        <span class="lp-tag-mono">Two-Column Master</span>
                    </div>
                    <div class="lp-spec-table">
                        <div v-for="s in specs" :key="s.label" class="lp-spec-row">
                            <span>{{ s.label }}</span>
                            <strong :class="[{ 'lp-mono': s.mono }, { 'lp-accent-text': s.accent }]">{{ s.value }}</strong>
                        </div>
                    </div>
                </div>

                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><Trophy /></el-icon> Certified Quality Assessment</h2>
                        <button type="button" class="lp-btn lp-btn--outline lp-btn--sm">View Quality Report <el-icon><TopRight /></el-icon></button>
                    </div>
                    <div class="lp-quality-callout">
                        <div>
                            <span class="lp-eyebrow">CQI Certified Score</span>
                            <div class="lp-quality-callout__score lp-mono">{{ quality.score }} <span>/ 100</span></div>
                            <span class="lp-quality-callout__grade"><el-icon><CircleCheck /></el-icon> {{ quality.gradeLabel }}</span>
                        </div>
                        <div class="lp-quality-callout__meta">
                            <span class="lp-mono">Assessed: <strong>{{ quality.assessedDate }}</strong></span>
                            <span>{{ quality.lab }}</span>
                        </div>
                    </div>
                    <div class="lp-metric4-grid">
                        <div v-for="m in quality.metrics" :key="m.label" class="lp-metric4">
                            <span>{{ m.label }}</span>
                            <strong class="lp-mono" :class="{ 'lp-accent-text': m.accent }">{{ m.value }}</strong>
                            <em :class="{ 'lp-accent-text': m.accent }">{{ m.sub }}</em>
                        </div>
                    </div>
                    <div>
                        <span class="lp-label-row">Validated Flavor Descriptors</span>
                        <div class="lp-flavor-tags">
                            <span v-for="f in quality.flavors" :key="f" class="lp-flavor-tag">{{ f }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Origin/farms + sustainability ────────────────────────────────── -->
            <div class="lp-grid-7-5">
                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><LocationFilled /></el-icon> Geographical Origin &amp; Contributing Farms</h2>
                        <button type="button" class="lp-btn lp-btn--outline lp-btn--sm">View Farms (3) <el-icon><Right /></el-icon></button>
                    </div>
                    <div class="lp-origin-layout">
                        <div class="lp-origin-facts">
                            <div class="lp-origin-fact">
                                <span>Country &amp; District</span>
                                <strong>{{ originFacts.countryDistrict }}</strong>
                                <em>{{ originFacts.microclimate }}</em>
                            </div>
                            <div class="lp-origin-fact">
                                <span>Participating Producer Cooperative</span>
                                <strong>{{ originFacts.coop }}</strong>
                                <em>{{ originFacts.leadProducer }}</em>
                            </div>
                            <div class="lp-origin-fact">
                                <span>Geospatial Telemetry</span>
                                <strong class="lp-mono">{{ originFacts.gps }}</strong>
                                <span class="lp-tag-mono lp-tag-mono--success lp-mt6"><el-icon><CircleCheck /></el-icon> Sentinel-2 Polygon Geofenced</span>
                            </div>
                        </div>
                        <div class="lp-map-card">
                            <el-icon><MapLocation /></el-icon>
                            <span class="lp-strong">Cadastral Polygon Map</span>
                            <span class="lp-mono lp-muted">{{ mapSector }}</span>
                            <button type="button" class="lp-btn lp-btn--outline lp-btn--sm">Inspect Cadastral Plot <el-icon><TopRight /></el-icon></button>
                        </div>
                    </div>
                </div>

                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><CircleCheck /></el-icon> Verified Sustainability</h2>
                        <span class="lp-check-badge">4 Audited Proofs</span>
                    </div>
                    <div class="lp-chip-row">
                        <span v-for="b in sustainBadges" :key="b" class="lp-check-pill lp-check-pill--outline"><el-icon><CircleCheck /></el-icon>{{ b }}</span>
                    </div>
                    <div class="lp-sustain-list">
                        <div v-for="s in sustainItems" :key="s.title" class="lp-sustain-row">
                            <div>
                                <span class="lp-strong">{{ s.title }}</span>
                                <em>{{ s.sub }}</em>
                            </div>
                            <span class="lp-status-tag">{{ s.status }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Commercial status + tokenisation ─────────────────────────────── -->
            <div class="lp-grid-7-5">
                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><Shop /></el-icon> Commercial Status &amp; Active Listings</h2>
                        <span class="lp-tag-mono lp-tag-mono--success">Master Inventory Synced</span>
                    </div>
                    <p class="lp-body-text lp-body-text--sm">All commercial channels below reference this unified Lot inventory. Allocating quantity in one channel instantly secures the underlying physical volume.</p>
                    <div class="lp-listing-table">
                        <div class="lp-listing-row lp-listing-row--head">
                            <span>Channel</span><span>Reference / Details</span><span>Price / Term</span><span>Status</span><span>Action</span>
                        </div>
                        <div v-for="l in listings" :key="l.channel" class="lp-listing-row">
                            <span class="lp-strong">{{ l.channel }}</span>
                            <span class="lp-muted-inline">{{ l.detail }}</span>
                            <span class="lp-mono lp-strong">{{ l.price }}</span>
                            <span class="lp-status-chip" :class="`lp-status-chip--${l.style}`">{{ l.status }}</span>
                            <button type="button" class="lp-btn lp-btn--outline lp-btn--sm">{{ l.action }}</button>
                        </div>
                    </div>
                </div>

                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><Link /></el-icon> Blockchain Tokenisation</h2>
                        <span class="lp-check-badge">Tokenised</span>
                    </div>
                    <div class="lp-token-box">
                        <div class="lp-token-row"><span>Token Identifier</span><strong class="lp-mono">{{ token.id }}</strong></div>
                        <div class="lp-token-row"><span>Underlying Chain</span><strong><el-icon><Connection /></el-icon> {{ token.chain }}</strong></div>
                        <div class="lp-token-row"><span>Tokenised Volume</span><strong class="lp-mono lp-accent-text">{{ token.volume }}</strong></div>
                        <div class="lp-token-row"><span>Mint Timestamp</span><strong class="lp-mono">{{ token.mintTimestamp }}</strong></div>
                        <div class="lp-token-row"><span>Contract Reference</span><strong class="lp-mono">{{ token.contractRef }}</strong></div>
                    </div>
                    <div class="lp-token-foot">
                        <span><el-icon><CircleCheck /></el-icon> Immutable digital twin</span>
                        <button type="button" class="lp-btn lp-btn--outline lp-btn--sm">View Blockchain Record <el-icon><TopRight /></el-icon></button>
                    </div>
                </div>
            </div>

            <!-- ── Documents + activity + AI insight ────────────────────────────── -->
            <div class="lp-grid-3">
                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><Files /></el-icon> Attached Documents</h2>
                        <span class="lp-tag-mono">4 Verified</span>
                    </div>
                    <div class="lp-doc-list">
                        <div v-for="d in documents" :key="d.name" class="lp-doc-row">
                            <div class="lp-doc-row__icon"><el-icon><Document /></el-icon></div>
                            <div class="lp-doc-row__text">
                                <span class="lp-doc-row__name">{{ d.name }}</span>
                                <span class="lp-mono lp-muted">{{ d.meta }}</span>
                            </div>
                            <div class="lp-doc-row__actions">
                                <button type="button" class="lp-btn lp-btn--outline lp-btn--sm">View</button>
                                <button type="button" class="lp-btn lp-btn--outline lp-btn--sm lp-btn--icon"><el-icon><Download /></el-icon></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><Clock /></el-icon> Lifecycle Audit History</h2>
                        <span class="lp-tag-mono">Chronological</span>
                    </div>
                    <div class="lp-activity">
                        <div v-for="a in activity" :key="a.title" class="lp-activity-row">
                            <span class="lp-activity-dot"><el-icon><Check /></el-icon></span>
                            <div>
                                <span class="lp-strong">{{ a.title }}</span>
                                <em>{{ a.text }}</em>
                                <span class="lp-mono lp-activity-date">{{ a.date }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lp-card">
                    <div class="lp-card__head">
                        <h2 class="lp-card__title"><el-icon><Cpu /></el-icon> AI Lot Intelligence</h2>
                        <span class="lp-check-badge">Advisory Model</span>
                    </div>
                    <div class="lp-ai-box">
                        <div class="lp-ai-box__head">
                            <span class="lp-status-chip lp-status-chip--success">{{ aiInsight.tag }}</span>
                            <span class="lp-mono lp-muted">{{ aiInsight.tagSub }}</span>
                        </div>
                        <p>{{ aiInsight.text }}</p>
                    </div>
                    <div class="lp-ai-buyer">
                        <span class="lp-strong">Potential Buyer Relevance</span>
                        <p>{{ aiInsight.buyerRelevance }}</p>
                    </div>
                    <div class="lp-ai-disclaimer"><el-icon><WarningFilled /></el-icon> {{ aiInsight.disclaimer }}</div>
                </div>
            </div>
        </div>

        <EditLotModal
            v-model="editModalOpen"
            :lot="l"
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
        <ConfirmDialog
            v-model="deleteDialogOpen"
            eyebrow="Lot Profile"
            title="Delete this lot?"
            :message="`Lot ${l.lot_number || `#${l.id}`} and its records will be permanently removed. This cannot be undone.`"
            confirm-text="Delete Lot"
            :loading="deleteLoading"
            :auto-close="false"
            :show-cancel="false"
            @confirm="confirmDelete"
        />
        <AttachBatchModal v-model="attachBatchModalOpen" :lot-id="l.id" />
        <ConfirmDialog
            v-model="detachBatchDialogOpen"
            eyebrow="Lot Profile"
            title="Remove this batch from the lot?"
            :message="`Batch #${batchToDetach?.code} will be unlinked from this lot. The batch record itself is not deleted.`"
            confirm-text="Remove Batch"
            :loading="detachBatchLoading"
            :auto-close="false"
            :show-cancel="false"
            @confirm="confirmDetachBatch"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Ported from the "Lot #LOT-000124 — Institutional Master Record"
   reference mockup (code.html) + DESIGN.md, mapped onto the app's
   persistent --dp-* tokens. All content on this page is illustrative
   sample data — see the script's opening comment. ─────────────────────── */
.lp-page {
    --card-border: var(--dp-outline-variant);
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.lp-mono { font-family: var(--dp-font-mono); }
.lp-muted { color: var(--dp-on-surface-variant); }
.lp-muted-inline { color: var(--dp-on-surface-variant); font-size: .8125rem; }
.lp-accent-text { color: var(--dp-primary) !important; }
.lp-strong { display: block; font-weight: 700; color: var(--dp-on-surface); font-size: .8125rem; }
.lp-dot { color: var(--dp-outline); }
.lp-mt6 { margin-top: 6px; }
.lp-eyebrow { display: block; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface-variant); margin-bottom: 3px; }
.lp-label-row { display: block; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); margin-bottom: 8px; }
.lp-body-text { font-size: .8125rem; line-height: 1.65; color: var(--dp-on-surface-variant); margin: 0 !important; }
.lp-body-text--sm { font-size: .75rem; }

/* ── Cards & buttons ─────────────────────────────────────────────────── */
.lp-card { padding: 24px; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); display: flex; flex-direction: column; gap: 16px; }
.lp-card__head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.lp-card__title { display: flex; align-items: center; gap: 8px; font-size: 1rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); margin: 0 !important; }
.lp-card__title :deep(.el-icon) { color: var(--dp-primary); }

.lp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: var(--dp-card-radius); font-size: .75rem; font-weight: 700; cursor: pointer; border: 1px solid transparent; font-family: inherit; white-space: nowrap; }
.lp-btn:disabled { opacity: .45; cursor: not-allowed; }
.lp-btn:disabled:hover { background: var(--dp-surface-container-lowest); border-color: var(--card-border); }
.lp-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.lp-btn--primary:hover { opacity: .9; }
.lp-btn--outline { background: var(--dp-surface-container-lowest); border-color: var(--card-border); color: var(--dp-on-surface); }
.lp-btn--outline:hover { background: var(--dp-surface-container); }
.lp-btn--danger { color: var(--dp-error); }
.lp-btn--danger:hover { background: var(--dp-error-container); border-color: var(--dp-error); }
.lp-btn--sm { height: 32px; padding: 0 10px; font-size: .6875rem; flex-shrink: 0; }
.lp-btn--icon { width: 32px; padding: 0; }

/* ── Header ──────────────────────────────────────────────────────────── */
.lp-header { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid var(--card-border); }
.lp-header__text { display: flex; flex-direction: column; gap: 8px; }
.lp-header__title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.lp-header__title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); margin: 0 !important; }
.lp-status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: var(--dp-card-radius); background: var(--dp-primary-container); color: var(--dp-on-primary-container); font-size: .75rem; font-weight: 700; }
.lp-status-pill__dot { width: 6px; height: 6px; border-radius: 50%; background: var(--dp-primary); }
.lp-check-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container); color: var(--dp-on-surface); font-size: .75rem; font-weight: 600; }
.lp-check-pill :deep(.el-icon) { color: var(--dp-primary); }
.lp-check-pill--outline { background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); }
.lp-header__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: .8125rem; color: var(--dp-on-surface-variant); }
.lp-header__meta strong { color: var(--dp-on-surface); font-weight: 700; }
.lp-header__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }

/* ── KPI grid ────────────────────────────────────────────────────────── */
.lp-kpi-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; }
.lp-kpi { padding: 14px 16px; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); display: flex; flex-direction: column; gap: 4px; }
.lp-kpi__value { font-size: 1.1875rem; font-weight: 800; color: var(--dp-on-surface); }
.lp-kpi__value span { font-size: .8125rem; font-weight: 500; color: var(--dp-on-surface-variant); }
.lp-kpi__sub { font-size: .6875rem; color: var(--dp-on-surface-variant); }

/* ── Layout helpers ──────────────────────────────────────────────────── */
.lp-grid-5-7 { display: grid; grid-template-columns: 5fr 7fr; gap: 24px; align-items: stretch; }
.lp-grid-6-6 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: stretch; }
.lp-grid-7-5 { display: grid; grid-template-columns: 7fr 5fr; gap: 24px; align-items: stretch; }
.lp-grid-8-4 { display: grid; grid-template-columns: 8fr 4fr; gap: 14px; }
.lp-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; align-items: stretch; }

/* ── About / checklist ───────────────────────────────────────────────── */
.lp-tag-mono { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container); color: var(--dp-on-surface-variant); font-family: var(--dp-font-mono); font-size: .6875rem; font-weight: 600; flex-shrink: 0; }
.lp-tag-mono--success { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.lp-checklist-box { padding: 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); display: flex; flex-direction: column; gap: 8px; }
.lp-checklist-box__head { display: flex; align-items: center; justify-content: space-between; }
.lp-checklist-box__head span:first-child { font-size: .75rem; font-weight: 700; color: var(--dp-on-surface); }
.lp-check-badge { padding: 3px 9px; border-radius: 999px; background: var(--dp-primary-container); color: var(--dp-on-primary-container); font-size: .625rem; font-weight: 700; flex-shrink: 0; }
.lp-checklist-box__item { display: flex; align-items: center; gap: 6px; font-size: .75rem; color: var(--dp-on-surface-variant); }
.lp-checklist-box__item :deep(.el-icon) { color: var(--dp-primary); flex-shrink: 0; }

/* ── Allocation ──────────────────────────────────────────────────────── */
.lp-alloc-head { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; font-size: .75rem; color: var(--dp-on-surface-variant); flex-wrap: wrap; }
.lp-alloc-head strong { color: var(--dp-on-surface); }
.lp-alloc-track { display: flex; width: 100%; height: 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-high); overflow: hidden; }
.lp-alloc-track__fill--available { background: var(--dp-primary); }
.lp-alloc-track__fill--allocated { background: #3B82F6; }
.lp-alloc-track__fill--reserved { background: #F59E0B; }
.lp-alloc-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; text-align: center; }
.lp-alloc-stat { padding: 8px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); display: flex; flex-direction: column; align-items: center; gap: 2px; }
.lp-alloc-stat .el-icon { font-size: 16px; color: var(--dp-on-surface-variant); margin-bottom: 2px; }
.lp-alloc-stat span { display: block; font-family: var(--dp-font-mono); font-size: .625rem; color: var(--dp-on-surface-variant); text-transform: uppercase; }
.lp-alloc-stat strong { font-family: var(--dp-font-mono); font-size: .875rem; color: var(--dp-on-surface); }
.lp-alloc-stat__sub { font-size: .625rem; color: var(--dp-on-surface-variant); text-transform: none; }
.lp-alloc-stat .lp-alloc-stat--allocated { color: #3B82F6; }
.lp-alloc-stat .lp-alloc-stat--reserved { color: #B45309; }
.lp-alloc-stat .lp-alloc-stat--available { color: var(--dp-primary); }
.lp-info-strip { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); font-size: .75rem; color: var(--dp-on-surface-variant); font-family: var(--dp-font-mono); flex-wrap: wrap; }
.lp-info-strip :deep(.el-icon) { color: var(--dp-outline); }

/* ── Traceability pipeline ───────────────────────────────────────────── */
.lp-pipeline { position: relative; display: flex; align-items: flex-start; justify-content: space-between; padding: 8px 0; }
.lp-pipeline__line { position: absolute; top: 27px; left: 30px; right: 30px; height: 2px; background: var(--dp-surface-container-high); }
.lp-pipeline__step { position: relative; flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center; background: var(--dp-surface-container-lowest); }
.lp-pipeline__circle { width: 38px; height: 38px; border-radius: 50%; background: var(--dp-surface-container-low); border: 2px solid var(--card-border); display: flex; align-items: center; justify-content: center; margin-bottom: 4px; }
.lp-pipeline__circle :deep(.el-icon) { color: var(--dp-on-surface-variant); }
.lp-pipeline__step--completed .lp-pipeline__circle { background: var(--dp-primary-container); border-color: var(--dp-primary); }
.lp-pipeline__step--completed .lp-pipeline__circle :deep(.el-icon) { color: #fff; }
.lp-pipeline__step--active .lp-pipeline__circle { background: var(--dp-primary); border-color: var(--dp-primary); }
.lp-pipeline__step--active .lp-pipeline__circle :deep(.el-icon) { color: #fff; }
.lp-pipeline__title { font-size: .75rem; font-weight: 700; color: var(--dp-on-surface); }
.lp-pipeline__sub { font-size: .625rem; color: var(--dp-on-surface-variant); }
.lp-subpanel { padding: 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); display: flex; flex-direction: column; gap: 10px; }
.lp-subpanel__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.lp-chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
.lp-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 10px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); font-size: .75rem; color: var(--dp-on-surface); text-decoration: none; }
.lp-chip :deep(.el-icon) { color: var(--dp-on-surface-variant); font-size: 13px; }
.lp-chip__qty { padding: 1px 6px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container); font-size: .625rem; color: var(--dp-on-surface-variant); }
.lp-chip--linked { cursor: pointer; transition: border-color .12s ease, background .12s ease, color .12s ease; }
.lp-chip--linked:hover { border-color: var(--dp-primary); background: var(--dp-primary-container); color: #fff; }
.lp-chip--linked:hover :deep(.el-icon) { color: #fff; }
.lp-chip--linked:hover .lp-chip__qty { background: rgba(255, 255, 255, 0.16); color: #fff; }
.lp-batch-list { display: flex; flex-direction: column; gap: 8px; }
.lp-batch-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); font-size: .8125rem; text-decoration: none; color: var(--dp-on-surface); }
.lp-batch-row > span:first-child { display: inline-flex; align-items: center; gap: 6px; min-width: 0; overflow: hidden; }
.lp-batch-row > span:first-child strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lp-batch-row :deep(.el-icon) { color: var(--dp-primary); }
.lp-batch-row--linked { cursor: pointer; transition: border-color .12s ease, background .12s ease, color .12s ease; }
.lp-batch-row--linked:hover { border-color: var(--dp-primary); background: var(--dp-primary-container); color: #fff; }
.lp-batch-row--linked:hover :deep(.el-icon) { color: #fff; }
.lp-batch-row--linked:hover .lp-tag-mono { background: rgba(255, 255, 255, 0.16); color: #fff; }
.lp-batch-row__right { display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0; }
.lp-batch-row__remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: none;
    background: transparent;
    cursor: pointer;
    flex-shrink: 0;
    transition: background .12s ease;
}
.lp-batch-row__remove:hover { background: rgba(248, 81, 73, 0.16); }

/* ── Specs table ─────────────────────────────────────────────────────── */
.lp-spec-table { display: flex; flex-direction: column; }
.lp-spec-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--dp-surface-container-high); font-size: .8125rem; }
.lp-spec-row:last-child { border-bottom: none; }
.lp-spec-row span { color: var(--dp-on-surface-variant); font-weight: 500; }
.lp-spec-row strong { color: var(--dp-on-surface); font-weight: 700; text-align: right; }

/* ── Quality ─────────────────────────────────────────────────────────── */
.lp-quality-callout { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; padding: 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); }
.lp-quality-callout__score { font-size: 1.5rem; font-weight: 800; color: var(--dp-on-surface); }
.lp-quality-callout__score span { font-size: .8125rem; font-weight: 500; color: var(--dp-on-surface-variant); }
.lp-quality-callout__grade { display: flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; color: var(--dp-primary); }
.lp-quality-callout__meta { display: flex; flex-direction: column; gap: 2px; text-align: right; font-size: .6875rem; color: var(--dp-on-surface-variant); flex-shrink: 0; }
.lp-quality-callout__meta strong { color: var(--dp-on-surface); }
.lp-metric4-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.lp-metric4 { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 10px 6px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); text-align: center; }
.lp-metric4 span { font-size: .5625rem; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); }
.lp-metric4 strong { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.lp-metric4 em { font-style: normal; font-size: .625rem; color: var(--dp-on-surface-variant); }
.lp-flavor-tags { display: flex; flex-wrap: wrap; gap: 6px; }
.lp-flavor-tag { padding: 4px 10px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); color: var(--dp-on-surface); font-size: .6875rem; font-weight: 600; }

/* ── Origin & sustainability ─────────────────────────────────────────── */
.lp-origin-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; align-items: stretch; }
.lp-origin-facts { display: flex; flex-direction: column; gap: 14px; }
.lp-origin-fact { display: flex; flex-direction: column; gap: 2px; }
.lp-origin-fact span { font-size: .75rem; color: var(--dp-on-surface-variant); }
.lp-origin-fact strong { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.lp-origin-fact em { font-style: normal; font-size: .75rem; color: var(--dp-on-surface-variant); }
.lp-map-card { border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 4px; padding: 20px; min-height: 160px; }
.lp-map-card :deep(.el-icon) { color: var(--dp-on-surface-variant); font-size: 26px; margin-bottom: 4px; }
.lp-map-card .lp-btn { margin-top: 8px; }
.lp-sustain-list { display: flex; flex-direction: column; }
.lp-sustain-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 0; border-top: 1px solid var(--dp-surface-container-high); }
.lp-sustain-row em { display: block; font-style: normal; font-size: .6875rem; color: var(--dp-on-surface-variant); margin-top: 1px; }
.lp-status-tag { padding: 3px 9px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container); color: var(--dp-primary); font-family: var(--dp-font-mono); font-size: .625rem; font-weight: 700; flex-shrink: 0; }

/* ── Commercial listings table ───────────────────────────────────────── */
.lp-listing-table { display: flex; flex-direction: column; }
.lp-listing-row { display: grid; grid-template-columns: 1.1fr 1.6fr 1.1fr .9fr .7fr; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--dp-surface-container-high); }
.lp-listing-row:last-child { border-bottom: none; }
.lp-listing-row--head { font-size: .625rem; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); font-weight: 700; padding-bottom: 8px; }
.lp-status-chip { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 999px; font-size: .6875rem; font-weight: 700; background: var(--dp-surface-container); color: var(--dp-on-surface-variant); width: fit-content; }
.lp-status-chip--success { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.lp-status-chip--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.lp-status-chip--warning { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.lp-status-chip--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

/* ── Tokenisation ────────────────────────────────────────────────────── */
.lp-token-box { padding: 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); display: flex; flex-direction: column; gap: 8px; }
.lp-token-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: .75rem; }
.lp-token-row span { color: var(--dp-on-surface-variant); }
.lp-token-row strong { color: var(--dp-on-surface); font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
.lp-token-row strong :deep(.el-icon) { color: var(--dp-primary); }
.lp-token-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.lp-token-foot > span { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; color: var(--dp-on-surface-variant); font-family: var(--dp-font-mono); }
.lp-token-foot :deep(.el-icon) { color: var(--dp-primary); }

/* ── Documents ───────────────────────────────────────────────────────── */
.lp-doc-list { display: flex; flex-direction: column; gap: 8px; }
.lp-doc-row { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); }
.lp-doc-row__icon { width: 30px; height: 30px; border-radius: var(--dp-card-radius); background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lp-doc-row__text { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 1px; }
.lp-doc-row__name { font-size: .75rem; font-weight: 700; color: var(--dp-on-surface); }
.lp-doc-row__actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

/* ── Activity ────────────────────────────────────────────────────────── */
.lp-activity { display: flex; flex-direction: column; }
.lp-activity-row { position: relative; display: flex; gap: 10px; padding-bottom: 14px; }
.lp-activity-row:last-child { padding-bottom: 0; }
.lp-activity-row::before { content: ''; position: absolute; left: 9px; top: 20px; bottom: 0; width: 2px; background: var(--dp-surface-container-high); }
.lp-activity-row:last-child::before { display: none; }
.lp-activity-dot { width: 20px; height: 20px; border-radius: 50%; background: var(--dp-primary-container); color: var(--dp-on-primary-container); display: flex; align-items: center; justify-content: center; flex-shrink: 0; z-index: 1; }
.lp-activity-dot :deep(.el-icon) { font-size: 11px; }
.lp-activity-row em { display: block; font-style: normal; font-size: .75rem; color: var(--dp-on-surface-variant); margin-top: 1px; }
.lp-activity-date { display: block; font-size: .625rem; color: var(--dp-on-surface-variant); margin-top: 3px; }

/* ── AI insight ──────────────────────────────────────────────────────── */
.lp-ai-box { padding: 12px 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); }
.lp-ai-box__head { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
.lp-ai-box p { font-size: .75rem; line-height: 1.55; color: var(--dp-on-surface); margin: 0 !important; }
.lp-ai-buyer p { font-size: .75rem; line-height: 1.5; color: var(--dp-on-surface-variant); margin: 4px 0 0 !important; }
.lp-ai-disclaimer { display: flex; align-items: flex-start; gap: 6px; padding: 8px 10px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); font-family: var(--dp-font-mono); font-size: .625rem; color: var(--dp-on-surface-variant); line-height: 1.5; }
.lp-ai-disclaimer :deep(.el-icon) { color: var(--dp-outline); flex-shrink: 0; margin-top: 1px; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1200px) {
    .lp-kpi-grid { grid-template-columns: repeat(3, 1fr); }
    .lp-grid-3 { grid-template-columns: 1fr; }
}
@media (max-width: 1024px) {
    .lp-grid-5-7, .lp-grid-6-6, .lp-grid-7-5 { grid-template-columns: 1fr; }
    .lp-grid-8-4 { grid-template-columns: 1fr; }
    .lp-origin-layout { grid-template-columns: 1fr; }
    .lp-listing-row { grid-template-columns: 1fr 1fr; row-gap: 4px; }
    .lp-listing-row--head { display: none; }
}
@media (max-width: 640px) {
    .lp-kpi-grid { grid-template-columns: 1fr 1fr; }
    .lp-metric4-grid { grid-template-columns: 1fr 1fr; }
    .lp-alloc-stats { grid-template-columns: 1fr 1fr; }
    .lp-header__actions { width: 100%; }
    .lp-header__actions .lp-btn { flex: 1; }
}
</style>
