<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import {
    Box, CircleCheck, Coffee, Sunny, Checked, User, InfoFilled, PieChart,
    EditPen, Trophy, Operation, Plus, Ticket, Check, Collection,
    OfficeBuilding, LocationFilled, Download, Document, Clock, Delete,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import UpdateBatchModal from '@/Components/Modals/UpdateBatchModal.vue';
import AttachFarmCollectionModal from '@/Components/Modals/AttachFarmCollectionModal.vue';
import AddBatchActivityModal from '@/Components/Modals/AddBatchActivityModal.vue';
import AddStorageRecordModal from '@/Components/Modals/AddStorageRecordModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

/* ── Real Batch data, field by field, with dummy fallbacks ported from the
   "Batch #BAT-000124" reference mockup (code.html) + DESIGN.md wherever the
   database has nothing to say. `batch` is BatchResource for this record
   (real batch + farm collections + farms + lots + activity log). Several
   mockup concepts have no schema support at all (per-stage mass log,
   genetic variety, warehouse bay/pallet/packaging detail, cupping flavor
   notes, a traceability hash) — those stay fully illustrative, noted
   inline. The layout/markup below is unchanged — only the data feeding
   it. ──────────────────────────────────────────────────────────────── */
const props = defineProps({
    batch: { type: Object, default: () => ({}) },
    currencyOptions: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    activityOptions: { type: Array, default: () => [] },
});
const b = props.batch ?? {};

/* ── Edit / delete / attach — real actions against the real batch.update,
   batch.destroy, and batch.farm-collections.store routes (the backend for
   these already existed; only this redesigned page's wiring was missing).
   Editing and attaching a collection keep the user on this same route, so
   the page reloads on success to pick up the fresh data — this page's
   derived display state is computed once from props at setup and Inertia
   reuses the same mounted instance on a same-route redirect. ──────────── */
const editModalOpen = ref(false);
const deleteDialogOpen = ref(false);
const deleting = ref(false);
const attachModalOpen = ref(false);
const addActivityModalOpen = ref(false);
const addStorageModalOpen = ref(false);

const deleteMessage = computed(() => `Are you sure you want to delete batch ${b.batch_number || `#${b.id}`}? This action cannot be undone.`);

function deleteBatch() {
    deleting.value = true;
    router.delete(route('batch.destroy', b.id), {
        onError: () => {
            ElNotification({
                title: 'Delete Failed',
                message: 'This batch could not be deleted.',
                type: 'error',
                duration: 3200,
                offset: 84,
            });
        },
        onFinish: () => {
            deleting.value = false;
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
const fmtShortDate = (s) => {
    if (!has(s)) return null;
    const d = new Date(String(s).replace(' ', 'T'));
    return isNaN(d) ? null : d.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
};

/* Trade names don't imply their species epithet (Robusta -> Coffea
   canephora, not "Coffea robusta") — look it up rather than guess. */
const speciesByVariety = { robusta: 'Coffea canephora', arabica: 'Coffea arabica', liberica: 'Coffea liberica', excelsa: 'Coffea excelsa' };
const coffeeSpecies = speciesByVariety[b.variety?.toLowerCase()] ?? null;

const collectionLinks = b.farm_collection_links ?? [];
const farmsById = new Map();
collectionLinks.forEach((link) => {
    const farm = link.farm_collection?.farm;
    if (farm?.id) farmsById.set(farm.id, farm);
});
const uniqueFarms = [...farmsById.values()];
const primaryFarm = uniqueFarms[0] ?? null;

/* Farm collections record their own quantity in whatever unit was entered
   (kg, bags, ...) — only safe to sum and compare against net_weight_kg
   (always kg) when every linked collection shares that same "kg" unit.
   Mixed or non-kg units fall back to netWeight for both sides rather than
   silently comparing incompatible units. */
const collectionUnits = [...new Set(collectionLinks.map((l) => l.farm_collection?.unit).filter(has))];
const collectionsAreAllKg = collectionUnits.length === 0 || (collectionUnits.length === 1 && collectionUnits[0].toLowerCase() === 'kg');
const totalCollectionQty = collectionLinks.length && collectionsAreAllKg
    ? collectionLinks.reduce((sum, l) => sum + (parseFloat(l.farm_collection?.quantity) || 0), 0)
    : null;

/* Only a single recorded weight exists on a batch (net_weight_kg) — there is
   no separate "raw intake" vs "clean/graded" weight column. The intake side
   of the yield waterfall is instead read from the sum of this batch's farm
   collections (its real upstream input), while net_weight_kg stands in for
   the "clean" output — an honest real comparison, not a fabricated one. */
const netWeight = has(b.net_weight_kg) ? parseFloat(b.net_weight_kg) : null;
const inputWeight = totalCollectionQty !== null ? totalCollectionQty : netWeight;
const hasYieldPair = inputWeight !== null && netWeight !== null && inputWeight > netWeight;

const header = {
    code: b.batch_number || 'BAT-000124',
    status: has(b.status) ? titleCase(b.status) : 'Ready for Lot Creation',
    processTag: b.processing_method || b.drying_method || 'Natural Sun-Dried',
    coffee: b.variety ? `${primaryFarm?.country || 'Uganda'} ${b.variety}` : 'Uganda Robusta (KR Clones)',
    created: fmtLongDate(b.created_at) || '15 September 2026',
    location: [primaryFarm?.region, primaryFarm?.district].filter(has).join(', ') || b.warehouse_location || 'Central Uganda, Mukono District',
    // No traceability/blockchain schema exists for batches — stays illustrative.
    hash: '0xb7e2...94a1',
};

const kpis = [
    { icon: Box, label: 'Input Quantity', value: has(inputWeight) ? inputWeight.toLocaleString() : '1,000', unit: 'kg', sub: collectionLinks.length ? `Aggregated from ${collectionLinks.length} collection${collectionLinks.length === 1 ? '' : 's'}` : 'Aggregated from 3 collections', bar: 100, barColor: 'outline' },
    {
        icon: CircleCheck, label: 'Current Clean', value: has(netWeight) ? netWeight.toLocaleString() : '850', unit: 'kg',
        sub: hasYieldPair ? `-${(inputWeight - netWeight).toLocaleString()} kg outturn loss (${((inputWeight - netWeight) / inputWeight * 100).toFixed(1)}%)` : '-150 kg outturn loss (15.0%)',
        subColor: 'error', bar: hasYieldPair ? Math.round(netWeight / inputWeight * 100) : 85, barColor: 'primary', accent: true,
    },
    { icon: Coffee, label: 'Coffee Species', value: b.variety || 'Robusta', sub: 'NARO-Kituza KR Clones', tag: 'High-yielding botanical', tagColor: 'secondary' },
    { icon: Sunny, label: 'Processing Method', value: b.processing_method || 'Natural Sun-Dried', sub: b.drying_method || 'Raised African Beds', tag: has(b.drying_duration) ? `${b.drying_duration} Day Dry Cycle` : 'Controlled Aeration', tagColor: 'primary' },
    { icon: Checked, label: 'Lot Readiness', value: has(b.status) ? titleCase(b.status) : 'Ready for Lot', sub: has(b.milling_status) ? `Milling: ${titleCase(b.milling_status)}` : 'CQI & Physical checks 100%', tag: b.lots?.length ? `${b.lots.length} Lot${b.lots.length === 1 ? '' : 's'} Created` : '0 Pending Checks', tagColor: 'primary', accent: true },
    { icon: User, label: 'Source Intake', value: uniqueFarms.length ? String(uniqueFarms.length) : '3', unit: 'Farms', sub: '100% smallholder verified', tag: 'Full Farm Traceability', tagColor: 'primary' },
];

const specs = [
    { label: 'Batch ID', value: header.code, chip: true },
    { label: 'Botanical Species', value: b.variety ? `${b.variety} (${coffeeSpecies || 'Coffea sp.'})` : 'Robusta (Coffea canephora)' },
    // No genetic-variety/clone column exists on the batch record — stays illustrative.
    { label: 'Genetic Variety', value: 'NARO-Kituza KR Clones (KR1-KR7)', accent: true },
    { label: 'Origin / Country', value: primaryFarm?.country ? `${primaryFarm.country} (East Africa)` : 'Uganda (East Africa)' },
    { label: 'Region / District', value: [primaryFarm?.region, primaryFarm?.district].filter(has).join(', ') || 'Central Uganda, Mukono District' },
    { label: 'Harvest Period', value: 'Main Crop 2025 / Jan 2026' },
    { label: 'Primary Processing', value: [b.processing_method, b.drying_method].filter(has).join(' — ') || 'Natural Sun-Dried (Raised Beds)', secondary: true },
    { label: 'Dry Mill Station', value: b.warehouse_location || 'Mukono Central Station & Dry Mill' },
    { label: 'Batch Creation Date', value: fmtLongDate(b.created_at) || '15 September 2026' },
    { label: 'Operational Status', value: has(b.status) ? titleCase(b.status) : 'Ready for Lot Creation', pill: true },
];

/* Processing Yield & Outturn Waterfall — the batch schema has no per-stage
   mass log (only the two real endpoints above), so the two middle stages
   are a proportional interpolation between them, not independently
   measured figures. Falls back to the mockup's illustrative numbers when
   there's no real intake/clean weight pair to anchor to. */
const wfInput = hasYieldPair ? inputWeight : 1000;
const wfClean = hasYieldPair ? netWeight : 850;
const wfLoss = Math.max(wfInput - wfClean, 0);
const wfMid1 = Math.round(wfInput - wfLoss / 3);
const wfMid2 = Math.round(wfInput - wfLoss * 2 / 3);
const wfNetPct = wfInput ? (wfClean / wfInput * 100) : 85;
const wfLossPct = wfInput ? (wfLoss / wfInput * 100) : 15;
const wfLossThird = Math.round(wfLoss / 3);

const waterfall = [
    { step: '01. INTAKE', pct: '100%', value: Math.round(wfInput).toLocaleString(), label: 'Cherry Intake', note: 'Aggregated Farm Harvest' },
    { step: '02. HULLING', pct: `-${wfLossThird.toLocaleString()} kg`, pctColor: 'error', value: wfMid1.toLocaleString(), label: 'Dry Process De-hull', note: 'Parchment eliminated' },
    { step: '03. DRYING', pct: `-${wfLossThird.toLocaleString()} kg`, pctColor: 'error', value: wfMid2.toLocaleString(), label: 'Raised Bed Solar', note: has(b.moisture_content) ? `Target: ${b.moisture_content}% Moisture` : 'Target: 11.2% Moisture' },
    { step: '04. CLEAN BATCH', pct: `${wfNetPct.toFixed(0)}% Net`, value: Math.round(wfClean).toLocaleString(), label: has(b.screen_size) ? `Screen ${b.screen_size} Graded` : 'Screen 18 Graded', note: 'Ready for Lot Allocation', highlight: true },
];
const yieldLegend = [
    { label: `${Math.round(wfClean).toLocaleString()} kg Commercial Export Grade`, color: 'primary' },
    { label: `${wfLossThird.toLocaleString()} kg Husk/Parchment`, color: 'secondary' },
    { label: `${wfLossThird.toLocaleString()} kg Evap Moisture`, color: 'secondary-dim' },
    { label: `${wfLossThird.toLocaleString()} kg Screen 15/17 Offgrade`, color: 'error' },
];

const dummyCollections = [
    { code: 'FC-001', farm: 'Kato Family Farm', plot: 'Plot #MK-01 · Verified Smallholder', date: '10 Sep 2026', qty: '300 kg', moisture: '12.0%', status: 'Accepted' },
    { code: 'FC-002', farm: 'Namaganda Estate', plot: 'Plot #MK-04 · Verified Smallholder', date: '11 Sep 2026', qty: '400 kg', moisture: '11.8%', status: 'Accepted' },
    { code: 'FC-003', farm: 'Ssemwogerere Plot', plot: 'Plot #MK-09 · Verified Smallholder', date: '12 Sep 2026', qty: '300 kg', moisture: '11.9%', status: 'Accepted' },
];
const collections = collectionLinks.length
    ? collectionLinks.map((link) => {
        const fc = link.farm_collection;
        const farm = fc?.farm;
        return {
            id: link.farm_collection_id ?? fc?.id ?? null,
            code: link.farm_collection_code || fc?.collection_code,
            farm: farm?.name || `Collection ${link.farm_collection_code}`,
            plot: [farm?.farm_code ? `Plot #${farm.farm_code}` : null, 'Verified Smallholder'].filter(has).join(' · '),
            date: fmtShortDate(fc?.collection_date) || '—',
            qty: has(fc?.quantity) ? `${Number(fc.quantity).toLocaleString()} ${fc?.unit || 'kg'}` : '—',
            moisture: has(fc?.initial_moisture) ? `${fc.initial_moisture}%` : '—',
            status: fc?.status ? titleCase(fc.status) : 'Pending',
        };
    })
    : dummyCollections;
const avgMoisture = collectionLinks.length
    ? (collectionLinks.reduce((sum, l) => sum + (parseFloat(l.farm_collection?.initial_moisture) || 0), 0) / collectionLinks.length)
    : null;

/* Batch Activity — event slugs are resolved to their metadata display name;
   anything not found (a retired slug) falls back to a titleized version of
   the slug itself rather than disappearing. No per-activity mass-in/out or
   pass/fail schema exists, so real rows show a generic "Recorded" status
   rather than a fabricated one. */
const eventIcons = { drying: Sunny, processing: Operation, storage: OfficeBuilding, milling: Collection, harvest: Box, quality: Trophy };
function eventIcon(slug) {
    return eventIcons[slug] || Clock;
}
function eventLabel(slug) {
    const match = props.activityOptions.find((option) => option.slug === slug);
    if (match) return match.name;
    return titleCase(slug);
}
const dummyProcessingRecords = [
    { icon: Sunny, title: 'Sun Drying Stabilization', date: '12 Sep 2026', desc: 'Raised Sun Beds #4, Mukono Station · Moisture dropped from 14.5% to 12.1%', supervisor: 'Sarah Nabatanzi', massInOut: '1,000 kg → 950 kg', status: 'Passed' },
    { icon: Operation, title: 'Hulling & Mechanical Cleaning', date: '13 Sep 2026', desc: 'Cimbria Huller Line 2 · Parchment and husks evacuated; hermetic density separator used', supervisor: 'David Ochieng', massInOut: '950 kg → 900 kg', status: 'Passed' },
    { icon: Collection, title: 'Optical Sorting & Screen 18 Grading', date: '14 Sep 2026', desc: 'Satake Optical Sorter · Screen 18 selection; 50 kg screen 15/17 off-grade separated', supervisor: 'Emmanuel Kato', massInOut: '900 kg → 850 kg', status: 'Screen 18 Q-Grade', statusAccent: true },
    { icon: OfficeBuilding, title: 'Storage Staging & Sealed Packaging', date: '15 Sep 2026', desc: 'Kampala Bonded Depot Bay 3 · Sealed in GrainPro hermetic liners inside jute bags', supervisor: 'Grace Akello', massInOut: '850 kg (Net Export)', massLabel: 'Quantity Sealed', status: 'Vault Staged', statusAccent: true },
];
const processingRecords = props.activities?.length
    ? props.activities.map((a) => ({
        id: a.id,
        icon: eventIcon(a.event),
        title: eventLabel(a.event),
        date: fmtShortDate(a.created_at) || '—',
        desc: a.description || 'No description recorded.',
        supervisor: a.recorded_by?.name || 'Unassigned',
        massInOut: '—',
        status: 'Recorded',
        statusAccent: true,
    }))
    : dummyProcessingRecords;

const activityToDelete = ref(null);
const deleteActivityDialogOpen = ref(false);
const deletingActivity = ref(false);
const deleteActivityMessage = computed(() => `Remove the "${activityToDelete.value?.title ?? ''}" record from this batch's log? This action cannot be undone.`);

function requestDeleteActivity(record) {
    activityToDelete.value = record;
    deleteActivityDialogOpen.value = true;
}

function confirmDeleteActivity() {
    if (!activityToDelete.value) return;
    const record = activityToDelete.value;
    deletingActivity.value = true;
    router.delete(route('batch.activities.destroy', { batch: b.id, activity: record.id }), {
        preserveScroll: true,
        onSuccess: () => {
            ElNotification({
                title: 'Record Removed',
                message: `"${record.title}" was removed from this batch's log.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
            window.location.reload();
        },
        onError: () => {
            ElNotification({
                title: 'Remove Failed',
                message: 'This record could not be removed.',
                type: 'error',
                duration: 3200,
                offset: 84,
            });
        },
        onFinish: () => {
            deletingActivity.value = false;
            deleteActivityDialogOpen.value = false;
        },
    });
}

const warehouse = b.warehouse ?? null;
const storage = [
    { label: 'Warehouse Facility', value: b.warehouse_location || 'Kampala Coffee Bonded Warehouse (Depot #4)' },
    { label: 'Storage Bay', value: warehouse?.storage_bay || 'Depot #Kampala-04, Bay 3B', chip: true },
    { label: 'Date Stored', value: fmtLongDate(warehouse?.date_stored) || fmtLongDate(b.created_at) || '15 September 2026' },
    {
        label: 'Quantity Stored',
        value: has(warehouse?.quantity_stored_kg)
            ? `${Number(warehouse.quantity_stored_kg).toLocaleString()} kg${has(b.quantity_bags) ? ` (${b.quantity_bags} bags)` : ''}`
            : has(netWeight) ? `${netWeight.toLocaleString()} kg${has(b.quantity_bags) ? ` (${b.quantity_bags} bags)` : ''}` : '850 kg (14.2 × 60kg Sacks)',
        accent: true,
    },
    { label: 'Climate Ambient', value: warehouse?.climate_ambient || '18°C - 21°C · 58% Relative Humidity' },
    { label: 'Physical Pallet', value: warehouse?.physical_pallet || 'Palletized & Raised (15cm off deck)' },
    { label: 'Packaging Spec', value: warehouse?.packaging_spec || 'GrainPro Hermetic + Food-Grade Jute' },
];

const qualityMetrics = [
    { label: 'CQI Score', value: has(b.cup_score) ? Number(b.cup_score).toFixed(2) : '82.50', sub: 'Fine Robusta G1', accent: true },
    { label: 'Screen Size', value: has(b.screen_size) ? `Scr ${b.screen_size}` : 'Scr 18', sub: '92.4% Retention' },
    { label: 'Moisture', value: has(b.moisture_content) ? `${b.moisture_content}%` : '11.2%', sub: 'Optimal (10-12%)', subAccent: true },
    // No water-activity column exists on the batch record — stays illustrative.
    { label: 'Water Activity', value: '0.54 aw', sub: 'Safe (<0.65 aw)', subAccent: true },
];
const defects = {
    primary: has(b.defect_count) ? `${b.defect_count} Defect${Number(b.defect_count) === 1 ? '' : 's'}` : '0 Defects',
};
// No cupping/flavor relation exists for batches (only lots have one) — stays illustrative.
const flavorTags = ['Dark Cocoa Nibs', 'Molasses', 'Toasted Hazelnut', 'Sweet Cedar'];

const stepper = [
    { num: 1, title: 'Farm Collections', sub: `${uniqueFarms.length || 3} Farms · ${Math.round(inputWeight ?? 1000).toLocaleString()} kg`, state: 'completed' },
    { num: 2, title: 'This Batch', sub: `${header.code} · ${Math.round(netWeight ?? 850).toLocaleString()} kg`, state: 'active' },
    { num: 3, title: 'Commercial Lot', sub: b.lots?.length ? `${b.lots.length} Lot${b.lots.length === 1 ? '' : 's'} Created` : 'Action Required', state: b.lots?.length ? 'completed' : 'next' },
    { num: 4, title: 'Marketplace / Auction', sub: 'Terminal Trading', state: 'faded' },
];

const ctaText = `${has(netWeight) ? netWeight.toLocaleString() : '850'} kg clean ${b.variety || 'Robusta'} is staged in ${b.warehouse_location || 'Kampala Depot Bay 3B'}, fully verified and ready to allocate into export lot records or bilateral contract specifications.`;

const aboutText = b.notes || `${Math.round(inputWeight ?? 1000).toLocaleString()} kg of ${b.variety || 'Robusta'} coffee aggregated from ${collectionLinks.length || 3} verified farm collection${(collectionLinks.length || 3) === 1 ? '' : 's'}${primaryFarm?.district ? ` in the ${primaryFarm.district} basin` : ' in the Mukono basin'} and processed together at the Central Milling Station for commercial lot creation and export grading.`;
// The 3-way loss breakdown (husk/moisture/offgrade) has no per-cause schema — stays illustrative even when the total loss above is real.
const reconciliationText = hasYieldPair
    ? `${wfLoss.toLocaleString()} kg reduction (${wfLossPct.toFixed(1)}%) accounted for moisture loss, husk and parchment separation, and screen sorting removal.`
    : '150 kg reduction accounted for moisture loss (-40kg to 11.2%), husk and parchment separation (-60kg), and screen 15/17 sorting removal (-50kg).';
</script>

<template>
    <DesignPreviewLayout title="Batch Profile">
        <div class="btp-page">
            <!-- ── Page header ───────────────────────────────────────────────────── -->
            <div class="btp-header">
                <div class="btp-header__text">
                    <div class="btp-header__title-row">
                        <h1 class="btp-header__title">Batch #{{ header.code }}</h1>
                        <span class="btp-status-pill"><el-icon><CircleCheck /></el-icon>{{ header.status }}</span>
                        <span class="btp-check-pill"><el-icon><Sunny /></el-icon>{{ header.processTag }}</span>
                    </div>
                    <div class="btp-header__meta">
                        <span><strong>Coffee:</strong> {{ header.coffee }}</span>
                        <span class="btp-dot">•</span>
                        <span><strong>Created:</strong> {{ header.created }}</span>
                        <span class="btp-dot">•</span>
                        <span class="btp-header__meta-icon"><el-icon><LocationFilled /></el-icon>{{ header.location }}</span>
                        <span class="btp-dot">•</span>
                        <span class="btp-mono">Hash: {{ header.hash }}</span>
                    </div>
                </div>
                <div class="btp-header__actions">
                    <template v-if="b.can_manage">
                        <button type="button" class="btp-btn btp-btn--outline" @click="editModalOpen = true"><el-icon><EditPen /></el-icon> Edit Batch</button>
                        <button type="button" class="btp-btn btp-btn--outline btp-btn--danger" @click="deleteDialogOpen = true"><el-icon><Delete /></el-icon> Delete Batch</button>
                    </template>
                    <button v-if="b.can_manage" type="button" class="btp-btn btp-btn--outline" @click="addActivityModalOpen = true"><el-icon><Operation /></el-icon> Record Processing</button>
                    <button v-if="b.can_manage" type="button" class="btp-btn btp-btn--secondary" @click="attachModalOpen = true"><el-icon><Plus /></el-icon> Add Collection</button>
                    <button v-if="b.can_manage" type="button" class="btp-btn btp-btn--primary" @click="addStorageModalOpen = true"><el-icon><Box /></el-icon> Add Storage Record</button>
                </div>
            </div>

            <!-- ── KPI strip ─────────────────────────────────────────────────────── -->
            <div class="btp-kpi-grid">
                <div v-for="k in kpis" :key="k.label" class="btp-kpi">
                    <div class="btp-kpi__head">
                        <span class="btp-eyebrow" :class="{ 'btp-accent-text': k.accent }">{{ k.label }}</span>
                        <el-icon :class="{ 'btp-accent-text': k.accent }"><component :is="k.icon" /></el-icon>
                    </div>
                    <div>
                        <div class="btp-kpi__value" :class="{ 'btp-accent-text': k.accent }">{{ k.value }} <span v-if="k.unit">{{ k.unit }}</span></div>
                        <div class="btp-kpi__sub" :class="{ 'btp-kpi__sub--error': k.subColor === 'error' }">{{ k.sub }}</div>
                    </div>
                    <div v-if="k.bar" class="btp-kpi__bar"><div class="btp-kpi__bar-fill" :class="`btp-kpi__bar-fill--${k.barColor}`" :style="{ width: k.bar + '%' }"></div></div>
                    <span v-if="k.tag" class="btp-kpi__tag" :class="{ 'btp-accent-text': k.tagColor === 'primary' }">{{ k.tag }}</span>
                </div>
            </div>

            <!-- ── About this batch + reconciliation callout ────────────────────── -->
            <div class="btp-about-row">
                <div class="btp-about-text">
                    <div class="btp-about-text__head"><el-icon><InfoFilled /></el-icon> Operational Scope &amp; Aggregation Context</div>
                    <p class="btp-body-text">{{ aboutText }}</p>
                </div>
                <div class="btp-about-highlight">
                    <div class="btp-about-highlight__icon"><el-icon><PieChart /></el-icon></div>
                    <div>
                        <div class="btp-strong">{{ wfLossPct.toFixed(0) }}% Yield Reconciliation Cleared</div>
                        <p class="btp-body-text btp-body-text--sm">{{ reconciliationText }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Institutional specifications + yield waterfall ───────────────── -->
            <div class="btp-grid-5-7">
                <div class="btp-card">
                    <div class="btp-card__head">
                        <h2 class="btp-card__title"><el-icon><Document /></el-icon> Institutional Specifications</h2>
                        <span class="btp-tag-mono">SPEC-V2.4</span>
                    </div>
                    <div class="btp-spec-table">
                        <div v-for="s in specs" :key="s.label" class="btp-spec-row">
                            <span>{{ s.label }}</span>
                            <strong v-if="s.chip" class="btp-mono btp-spec-chip">{{ s.value }}</strong>
                            <span v-else-if="s.pill" class="btp-status-tag-solid">{{ s.value }}</span>
                            <strong v-else :class="{ 'btp-accent-text': s.accent, 'btp-secondary-text': s.secondary }">{{ s.value }}</strong>
                        </div>
                    </div>
                    <div class="btp-info-strip">
                        <span><el-icon><CircleCheck /></el-icon> UCDA Export Compliance</span>
                        <span class="btp-mono btp-accent-text btp-strong">Pre-Approved #UG-2026-88</span>
                    </div>
                </div>

                <div class="btp-card">
                    <div class="btp-card__head">
                        <div>
                            <h2 class="btp-card__title"><el-icon><Operation /></el-icon> Processing Yield &amp; Outturn Waterfall</h2>
                            <span class="btp-muted">Physical mass balance tracking across mechanical and dehydration phases</span>
                        </div>
                        <button v-if="b.can_manage" type="button" class="btp-btn btp-btn--outline btp-btn--sm" @click="addActivityModalOpen = true"><el-icon><Operation /></el-icon> Record Processing</button>
                    </div>
                    <div class="btp-waterfall-grid">
                        <div v-for="w in waterfall" :key="w.step" class="btp-waterfall-step" :class="{ 'btp-waterfall-step--highlight': w.highlight }">
                            <div class="btp-waterfall-step__head">
                                <span class="btp-waterfall-step__num">{{ w.step }}</span>
                                <span class="btp-waterfall-step__pct" :class="{ 'btp-waterfall-step__pct--error': w.pctColor === 'error' }">{{ w.pct }}</span>
                            </div>
                            <div class="btp-waterfall-step__value">{{ w.value }} <span>kg</span></div>
                            <div class="btp-waterfall-step__label">{{ w.label }}</div>
                            <div class="btp-waterfall-step__note btp-mono">{{ w.note }}</div>
                        </div>
                    </div>
                    <div class="btp-yield-strip">
                        <div class="btp-yield-strip__head">
                            <span class="btp-strong">Yield Efficiency Mass Balance</span>
                            <span class="btp-mono btp-accent-text btp-strong">850 kg Net (85.0%) / 150 kg Loss (15.0%)</span>
                        </div>
                        <div class="btp-yield-track">
                            <div class="btp-yield-track__fill btp-yield-track__fill--primary" style="width: 85%"></div>
                            <div class="btp-yield-track__fill btp-yield-track__fill--secondary" style="width: 5%"></div>
                            <div class="btp-yield-track__fill btp-yield-track__fill--secondary-dim" style="width: 5%"></div>
                            <div class="btp-yield-track__fill btp-yield-track__fill--error" style="width: 5%"></div>
                        </div>
                        <div class="btp-yield-legend">
                            <span v-for="l in yieldLegend" :key="l.label"><i :class="`btp-yield-legend__dot btp-yield-legend__dot--${l.color}`"></i>{{ l.label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Contributing farm collections ─────────────────────────────────── -->
            <div class="btp-card">
                <div class="btp-card__head">
                    <div>
                        <h2 class="btp-card__title"><el-icon><OfficeBuilding /></el-icon> Contributing Farm Collections</h2>
                        <span class="btp-muted">{{ collections.length }} smallholder intake batch{{ collections.length === 1 ? '' : 'es' }} verified with farmgate biometric IDs and moisture log</span>
                    </div>
                    <span class="btp-tag-mono btp-tag-mono--success">Total Intake: {{ Math.round(inputWeight ?? 1000).toLocaleString() }} kg</span>
                </div>
                <div class="btp-collections-table-wrap">
                    <table class="btp-collections-table">
                        <thead>
                            <tr><th>Collection ID</th><th>Farm / Smallholder</th><th>Intake Date</th><th>Intake Qty</th><th>Moisture</th><th>QA Status</th><th class="btp-collections-table__action-head" /></tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in collections" :key="c.code">
                                <td class="btp-mono btp-accent-text btp-collections-table__id">{{ c.code }}</td>
                                <td>
                                    <div class="btp-strong">{{ c.farm }}</div>
                                    <div class="btp-muted-inline">{{ c.plot }}</div>
                                </td>
                                <td>{{ c.date }}</td>
                                <td class="btp-mono btp-collections-table__qty">{{ c.qty }}</td>
                                <td class="btp-mono btp-muted-inline">{{ c.moisture }}</td>
                                <td><span class="btp-status-tag-solid">{{ c.status }}</span></td>
                                <td class="btp-collections-table__action">
                                    <Link v-if="c.id" :href="route('farm-collection.show', c.id)" class="btp-btn btp-btn--outline btp-btn--sm">View Collection</Link>
                                    <button v-else type="button" class="btp-btn btp-btn--outline btp-btn--sm" disabled>View Collection</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="btp-info-strip">
                    <span><el-icon><CircleCheck /></el-icon> Aggregate Intake Summary: <span class="btp-muted">{{ Math.round(inputWeight ?? 1000).toLocaleString() }} kg across {{ uniqueFarms.length || 3 }} cooperative partner farm{{ (uniqueFarms.length || 3) === 1 ? '' : 's' }}.</span></span>
                    <span class="btp-mono">Avg. Intake Moisture: <strong>{{ avgMoisture !== null ? avgMoisture.toFixed(1) + '%' : '11.9%' }}</strong></span>
                </div>
            </div>

            <!-- ── Processing records / audit trail ─────────────────────────────── -->
            <div class="btp-card">
                <div class="btp-card__head">
                    <div>
                        <h2 class="btp-card__title"><el-icon><Clock /></el-icon> Processing Records &amp; Station Audit Trail</h2>
                        <span class="btp-muted">Chronological station transformations verified by certified station masters</span>
                    </div>
                    <button v-if="b.can_manage" type="button" class="btp-btn btp-btn--primary btp-btn--sm" @click="addActivityModalOpen = true"><el-icon><Plus /></el-icon> Add Processing Record</button>
                </div>
                <div class="btp-audit-list">
                    <div v-for="r in processingRecords" :key="r.id ?? r.title" class="btp-audit-row">
                        <div class="btp-audit-row__left">
                            <div class="btp-audit-row__icon"><el-icon><component :is="r.icon" /></el-icon></div>
                            <div>
                                <div class="btp-audit-row__title-line">
                                    <span class="btp-strong">{{ r.title }}</span>
                                    <span class="btp-tag-mono">{{ r.date }}</span>
                                </div>
                                <p class="btp-body-text btp-body-text--sm">{{ r.desc }}</p>
                                <div class="btp-audit-row__supervisor"><el-icon><User /></el-icon> Supervisor: {{ r.supervisor }}</div>
                            </div>
                        </div>
                        <div class="btp-audit-row__right">
                            <div class="btp-audit-row__mass">
                                <span>{{ r.massLabel || 'Mass In / Out' }}</span>
                                <strong class="btp-mono">{{ r.massInOut }}</strong>
                            </div>
                            <span class="btp-tag-mono" :class="{ 'btp-tag-mono--success': r.statusAccent }">{{ r.status }}</span>
                            <button
                                v-if="r.id && b.can_manage"
                                type="button"
                                class="btp-audit-row__remove"
                                title="Remove this record"
                                @click="requestDeleteActivity(r)"
                            >
                                <el-icon><Delete /></el-icon>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Storage + quality assessment ─────────────────────────────────── -->
            <div class="btp-grid-5-7">
                <div class="btp-card">
                    <div class="btp-card__head">
                        <h2 class="btp-card__title"><el-icon><OfficeBuilding /></el-icon> Bonded Warehousing &amp; Storage</h2>
                        <span class="btp-status-tag-solid">Stored &amp; Ready</span>
                    </div>
                    <div class="btp-spec-table">
                        <div v-for="s in storage" :key="s.label" class="btp-spec-row">
                            <span>{{ s.label }}</span>
                            <strong v-if="s.chip" class="btp-mono btp-spec-chip">{{ s.value }}</strong>
                            <strong v-else :class="{ 'btp-accent-text': s.accent }">{{ s.value }}</strong>
                        </div>
                    </div>
                    <button type="button" class="btp-btn btp-btn--outline btp-btn--block"><el-icon><Box /></el-icon> View Storage Details &amp; Logistics Slip</button>
                </div>

                <div class="btp-card">
                    <div class="btp-card__head">
                        <div>
                            <h2 class="btp-card__title"><el-icon><Trophy /></el-icon> Physical Assessment &amp; CQI Cupping</h2>
                            <span class="btp-muted">Certified Q-Robusta verification executed at Mukono Cupping Lab #3</span>
                        </div>
                        <button type="button" class="btp-btn btp-btn--secondary btp-btn--sm"><el-icon><CircleCheck /></el-icon> View Quality Certificate</button>
                    </div>
                    <div class="btp-metric4-grid">
                        <div v-for="m in qualityMetrics" :key="m.label" class="btp-metric4">
                            <span>{{ m.label }}</span>
                            <strong class="btp-mono" :class="{ 'btp-accent-text': m.accent }">{{ m.value }}</strong>
                            <em :class="{ 'btp-accent-text': m.subAccent }">{{ m.sub }}</em>
                        </div>
                    </div>
                    <div class="btp-grid-6-6">
                        <div class="btp-defect-box">
                            <div class="btp-spec-row"><span>Primary Defects / 350g</span><strong class="btp-mono btp-accent-text">{{ defects.primary }}</strong></div>
                            <div class="btp-spec-row"><span>Secondary Defects / 350g</span><strong class="btp-mono">2 minor quakers</strong></div>
                            <div class="btp-spec-row"><span>Assessment Date</span><strong class="btp-mono">14 Sep 2026</strong></div>
                            <div class="btp-mono btp-muted-inline btp-mt6">Certified Inspector: Q-Robusta #4819</div>
                        </div>
                        <div class="btp-defect-box">
                            <span class="btp-strong">Sensory &amp; Flavor Notes</span>
                            <div class="btp-flavor-tags btp-mt6">
                                <span v-for="(f, i) in flavorTags" :key="f" class="btp-flavor-tag" :class="{ 'btp-flavor-tag--accent': i === 2 }">{{ f }}</span>
                            </div>
                            <p class="btp-body-text btp-body-text--sm btp-mt6">Heavy body, clean finish with cane syrup sweetness.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Traceability custody flow + CTA ──────────────────────────────── -->
            <div class="btp-card btp-card--custody">
                <div class="btp-card__head">
                    <div>
                        <span class="btp-eyebrow btp-accent-text">Traceability Custody Flow</span>
                        <h2 class="btp-card__title btp-mt6">Custodial Transformation Stage</h2>
                    </div>
                    <span class="btp-mono btp-muted">Step 2 of 4 Active</span>
                </div>
                <div class="btp-stepper">
                    <div v-for="s in stepper" :key="s.num" class="btp-stepper__node" :class="`btp-stepper__node--${s.state}`">
                        <div class="btp-stepper__circle"><el-icon v-if="s.state === 'completed'"><Check /></el-icon><span v-else>{{ s.num }}</span></div>
                        <div>
                            <div class="btp-stepper__title">{{ s.num }}. {{ s.title }}</div>
                            <div class="btp-stepper__sub" :class="{ 'btp-mono': s.state !== 'next' }">{{ s.sub }}</div>
                        </div>
                    </div>
                </div>
                <div class="btp-cta-banner">
                    <div>
                        <div class="btp-cta-banner__head"><el-icon><CircleCheck /></el-icon> {{ header.status }}</div>
                        <p>{{ ctaText }}</p>
                    </div>
                    <div class="btp-cta-banner__actions">
                        <button type="button" class="btp-btn btp-btn--light"><el-icon><Download /></el-icon> Download Dossier (PDF)</button>
                        <button type="button" class="btp-btn btp-btn--secondary"><el-icon><Ticket /></el-icon> Create Lot Now</button>
                    </div>
                </div>
            </div>
        </div>

        <UpdateBatchModal
            v-if="b.can_manage"
            v-model="editModalOpen"
            :batch="b"
            :currency-options="currencyOptions"
            @success="() => window.location.reload()"
        />
        <ConfirmDialog
            v-model="deleteDialogOpen"
            eyebrow="Batch"
            title="Delete Batch"
            :message="deleteMessage"
            confirm-text="Delete Batch"
            :auto-close="false"
            :loading="deleting"
            :show-cancel="false"
            @confirm="deleteBatch"
        />
        <AttachFarmCollectionModal v-if="b.can_manage" v-model="attachModalOpen" :batch-id="b.id" />
        <AddBatchActivityModal v-if="b.can_manage" v-model="addActivityModalOpen" :batch-id="b.id" :activity-options="activityOptions" />
        <AddStorageRecordModal v-if="b.can_manage" v-model="addStorageModalOpen" :batch-id="b.id" :warehouse="warehouse" />
        <ConfirmDialog
            v-model="deleteActivityDialogOpen"
            eyebrow="Batch Processing Records"
            title="Remove this record?"
            :message="deleteActivityMessage"
            confirm-text="Remove Record"
            :loading="deletingActivity"
            :auto-close="false"
            :show-cancel="false"
            @confirm="confirmDeleteActivity"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Ported from the "Batch #BAT-000124" reference mockup (code.html) +
   DESIGN.md, mapped onto the app's persistent --dp-* tokens — identical
   porting pattern to LotProfile.vue. All content on this page is
   illustrative sample data — see the script's opening comment. ──────── */
.btp-page {
    --card-border: var(--dp-outline-variant);
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.btp-mono { font-family: var(--dp-font-mono); }
.btp-muted { color: var(--dp-on-surface-variant); }
.btp-muted-inline { color: var(--dp-on-surface-variant); font-size: .8125rem; }
.btp-accent-text { color: var(--dp-primary) !important; }
.btp-secondary-text { color: var(--dp-on-surface-variant) !important; font-weight: 700 !important; }
.btp-strong { display: block; font-weight: 700; color: var(--dp-on-surface); font-size: .8125rem; }
.btp-dot { color: var(--dp-outline); }
.btp-mt6 { margin-top: 6px; }
.btp-eyebrow { display: block; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface-variant); margin-bottom: 3px; }
.btp-body-text { font-size: .8125rem; line-height: 1.65; color: var(--dp-on-surface-variant); margin: 0 !important; }
.btp-body-text--sm { font-size: .75rem; }

/* ── Cards & buttons ─────────────────────────────────────────────────── */
.btp-card { padding: 24px; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); display: flex; flex-direction: column; gap: 16px; }
.btp-card__head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.btp-card__title { display: flex; align-items: center; gap: 8px; font-size: 1rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); margin: 0 !important; }
.btp-card__title :deep(.el-icon) { color: var(--dp-primary); }

.btp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: var(--dp-card-radius); font-size: .75rem; font-weight: 700; cursor: pointer; border: 1px solid transparent; font-family: inherit; white-space: nowrap; text-decoration: none; }
.btp-btn:disabled, a.btp-btn[disabled] { opacity: .5; cursor: default; pointer-events: none; }
.btp-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.btp-btn--primary:hover { opacity: .9; }
.btp-btn--outline { background: var(--dp-surface-container-lowest); border-color: var(--card-border); color: var(--dp-on-surface); }
.btp-btn--outline:hover { background: var(--dp-surface-container); }
.btp-btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.btp-btn--secondary:hover { opacity: .88; }
.btp-btn--light { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); }
.btp-btn--light:hover { background: var(--dp-surface-container-high); }
.btp-btn--danger { color: var(--dp-error); }
.btp-btn--danger:hover { background: var(--dp-error-container); border-color: var(--dp-error); }
.btp-btn--sm { height: 32px; padding: 0 10px; font-size: .6875rem; flex-shrink: 0; }
.btp-btn--block { width: 100%; }

/* ── Header ──────────────────────────────────────────────────────────── */
.btp-header { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid var(--card-border); }
.btp-header__text { display: flex; flex-direction: column; gap: 8px; }
.btp-header__title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.btp-header__title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); margin: 0 !important; }
.btp-status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: var(--dp-card-radius); background: var(--dp-primary); color: var(--dp-on-primary); font-size: .75rem; font-weight: 700; }
.btp-check-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: var(--dp-card-radius); background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); font-size: .75rem; font-weight: 600; }
.btp-header__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: .8125rem; color: var(--dp-on-surface-variant); }
.btp-header__meta strong { color: var(--dp-on-surface); font-weight: 700; }
.btp-header__meta-icon { display: inline-flex; align-items: center; gap: 4px; }
.btp-header__meta-icon :deep(.el-icon) { color: var(--dp-outline); font-size: 13px; }
.btp-header__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }

/* ── KPI grid ────────────────────────────────────────────────────────── */
.btp-kpi-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; }
.btp-kpi { padding: 14px 16px; background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--dp-card-radius); display: flex; flex-direction: column; gap: 10px; }
.btp-kpi__head { display: flex; align-items: center; justify-content: space-between; }
.btp-kpi__head :deep(.el-icon) { color: var(--dp-outline); font-size: 16px; }
.btp-kpi__value { font-size: 1.1875rem; font-weight: 800; color: var(--dp-on-surface); }
.btp-kpi__value span { font-size: .8125rem; font-weight: 500; color: var(--dp-on-surface-variant); }
.btp-kpi__sub { font-size: .6875rem; color: var(--dp-on-surface-variant); margin-top: 2px; }
.btp-kpi__sub--error { color: var(--dp-error); font-weight: 600; }
.btp-kpi__bar { width: 100%; height: 5px; border-radius: 3px; background: var(--dp-surface-container-high); overflow: hidden; }
.btp-kpi__bar-fill { height: 100%; }
.btp-kpi__bar-fill--primary { background: var(--dp-primary); }
.btp-kpi__bar-fill--outline { background: var(--dp-outline); }
.btp-kpi__tag { font-size: .625rem; font-weight: 700; color: var(--dp-on-surface-variant); }

/* ── Layout helpers ──────────────────────────────────────────────────── */
.btp-grid-5-7 { display: grid; grid-template-columns: 5fr 7fr; gap: 24px; align-items: stretch; }
.btp-grid-6-6 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start; }

/* ── About + reconciliation ──────────────────────────────────────────── */
.btp-about-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; padding: 20px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); }
.btp-about-text { flex: 1 1 420px; min-width: 0; display: flex; flex-direction: column; gap: 8px; }
.btp-about-text__head { display: flex; align-items: center; gap: 8px; font-size: .8125rem; font-weight: 700; color: var(--dp-primary); }
.btp-about-highlight { flex: 0 1 420px; display: flex; align-items: flex-start; gap: 12px; padding: 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-lowest); border: 1px solid var(--card-border); }
.btp-about-highlight__icon { width: 34px; height: 34px; border-radius: var(--dp-card-radius); background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Specs table ─────────────────────────────────────────────────────── */
.btp-spec-table { display: flex; flex-direction: column; }
.btp-spec-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 0; font-size: .8125rem; }
.btp-spec-row span { color: var(--dp-on-surface-variant); font-weight: 500; }
.btp-spec-row strong { color: var(--dp-on-surface); font-weight: 700; text-align: right; }
.btp-spec-chip { padding: 2px 8px; border-radius: 4px; background: var(--dp-surface-container-high); }
.btp-status-tag-solid { padding: 3px 9px; border-radius: 999px; font-size: .6875rem; font-weight: 700; background: var(--dp-primary); color: var(--dp-on-primary); }
.btp-info-strip { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); font-size: .75rem; color: var(--dp-on-surface-variant); flex-wrap: wrap; }
.btp-info-strip :deep(.el-icon) { color: var(--dp-primary); }
.btp-info-strip > span:first-child { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: var(--dp-on-surface); }

/* ── Yield waterfall ─────────────────────────────────────────────────── */
.btp-waterfall-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.btp-waterfall-step { padding: 12px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); display: flex; flex-direction: column; gap: 6px; }
.btp-waterfall-step--highlight { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.btp-waterfall-step__head { display: flex; align-items: center; justify-content: space-between; }
.btp-waterfall-step__num { font-family: var(--dp-font-mono); font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.btp-waterfall-step--highlight .btp-waterfall-step__num { color: var(--dp-on-primary-container); }
.btp-waterfall-step__pct { font-size: .625rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.btp-waterfall-step__pct--error { background: var(--dp-error-container); color: var(--dp-error); }
.btp-waterfall-step--highlight .btp-waterfall-step__pct { background: var(--dp-primary); color: var(--dp-on-primary); }
.btp-waterfall-step__value { font-size: 1.125rem; font-weight: 800; color: var(--dp-on-surface); }
.btp-waterfall-step--highlight .btp-waterfall-step__value { color: var(--dp-on-primary-container); }
.btp-waterfall-step__value span { font-size: .6875rem; font-weight: 500; color: var(--dp-on-surface-variant); }
.btp-waterfall-step__label { font-size: .6875rem; font-weight: 600; color: var(--dp-on-surface-variant); }
.btp-waterfall-step--highlight .btp-waterfall-step__label { color: var(--dp-on-primary-container); }
.btp-waterfall-step__note { font-size: .625rem; color: var(--dp-on-surface-variant); }
.btp-waterfall-step--highlight .btp-waterfall-step__note { color: var(--dp-on-primary-container); }

.btp-yield-strip { padding: 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); display: flex; flex-direction: column; gap: 8px; }
.btp-yield-strip__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; font-size: .75rem; }
.btp-yield-track { display: flex; width: 100%; height: 10px; border-radius: 6px; background: var(--dp-surface-container-high); overflow: hidden; }
.btp-yield-track__fill--primary { background: var(--dp-primary); }
.btp-yield-track__fill--secondary { background: var(--dp-secondary-fixed); }
.btp-yield-track__fill--secondary-dim { background: var(--dp-secondary-fixed-dim, var(--dp-secondary-fixed)); }
.btp-yield-track__fill--error { background: var(--dp-error); opacity: .7; }
.btp-yield-legend { display: flex; flex-wrap: wrap; gap: 12px; font-size: .6875rem; color: var(--dp-on-surface-variant); font-family: var(--dp-font-mono); }
.btp-yield-legend span { display: inline-flex; align-items: center; gap: 6px; }
.btp-yield-legend__dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; }
.btp-yield-legend__dot--primary { background: var(--dp-primary); }
.btp-yield-legend__dot--secondary { background: var(--dp-secondary-fixed); }
.btp-yield-legend__dot--secondary-dim { background: var(--dp-secondary-fixed-dim, var(--dp-secondary-fixed)); }
.btp-yield-legend__dot--error { background: var(--dp-error); opacity: .7; }

/* ── Farm collections table ──────────────────────────────────────────── */
.btp-tag-mono { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container); color: var(--dp-on-surface-variant); font-family: var(--dp-font-mono); font-size: .6875rem; font-weight: 600; flex-shrink: 0; }
.btp-tag-mono--success { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.btp-collections-table-wrap { overflow-x: auto; }
.btp-collections-table { width: 100%; border-collapse: collapse; font-size: .75rem; }
.btp-collections-table thead th { text-align: left; padding: 8px 10px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); background: var(--dp-surface-container-low); white-space: nowrap; }
.btp-collections-table thead th:first-child { border-radius: var(--dp-card-radius) 0 0 var(--dp-card-radius); }
.btp-collections-table thead th:last-child { border-radius: 0 var(--dp-card-radius) var(--dp-card-radius) 0; }
.btp-collections-table tbody td { padding: 10px; vertical-align: middle; color: var(--dp-on-surface); border-bottom: 1px solid var(--dp-surface-container-high); }
.btp-collections-table tbody tr:last-child td { border-bottom: none; }
.btp-collections-table__id { font-weight: 700; }
.btp-collections-table__qty { font-weight: 700; }
.btp-collections-table__action, .btp-collections-table__action-head { text-align: right; width: 1%; }

/* ── Processing records ──────────────────────────────────────────────── */
.btp-audit-list { display: flex; flex-direction: column; gap: 10px; }
.btp-audit-row { padding: 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; }
.btp-audit-row__left { display: flex; align-items: flex-start; gap: 12px; flex: 1 1 320px; min-width: 0; }
.btp-audit-row__icon { width: 36px; height: 36px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-high); color: var(--dp-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.btp-audit-row__title-line { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 3px; }
.btp-audit-row__supervisor { display: inline-flex; align-items: center; gap: 5px; font-size: .6875rem; color: var(--dp-on-surface-variant); margin-top: 4px; }
.btp-audit-row__supervisor :deep(.el-icon) { font-size: 12px; }
.btp-audit-row__right { display: flex; align-items: center; gap: 16px; flex-shrink: 0; font-size: .75rem; }
.btp-audit-row__mass { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; }
.btp-audit-row__mass span { font-size: .625rem; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); }
.btp-audit-row__mass strong { font-weight: 700; color: var(--dp-on-surface); }
.btp-audit-row__remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    flex-shrink: 0;
    transition: background .12s ease, color .12s ease;
}
.btp-audit-row__remove:hover { background: var(--dp-error-container); color: var(--dp-error); }

/* ── Quality metrics ─────────────────────────────────────────────────── */
.btp-metric4-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.btp-metric4 { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 10px 6px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); text-align: center; }
.btp-metric4 span { font-size: .5625rem; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); }
.btp-metric4 strong { font-size: .8125rem; font-weight: 700; color: var(--dp-on-surface); }
.btp-metric4 em { font-style: normal; font-size: .625rem; color: var(--dp-on-surface-variant); }
.btp-defect-box { padding: 12px 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-low); }
.btp-defect-box .btp-spec-row { padding: 5px 0; font-size: .75rem; }
.btp-flavor-tags { display: flex; flex-wrap: wrap; gap: 6px; }
.btp-flavor-tag { padding: 4px 10px; border-radius: 999px; background: var(--dp-surface-container-high); color: var(--dp-on-surface); font-size: .6875rem; font-weight: 600; }
.btp-flavor-tag--accent { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }

/* ── Traceability stepper + CTA ──────────────────────────────────────── */
.btp-card--custody { background: linear-gradient(135deg, var(--dp-surface-container-low), var(--dp-surface-container)); border: none; }
.btp-stepper { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.btp-stepper__node { padding: 12px 14px; border-radius: var(--dp-card-radius); background: var(--dp-surface-container-lowest); display: flex; align-items: center; gap: 10px; box-shadow: none; }
.btp-stepper__node--active { background: var(--dp-primary); color: var(--dp-on-primary); }
.btp-stepper__node--next { background: transparent; border: 1px dashed var(--dp-outline-variant); }
.btp-stepper__node--faded { opacity: .55; }
.btp-stepper__circle { width: 30px; height: 30px; border-radius: 50%; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 700; font-size: .75rem; }
.btp-stepper__node--completed .btp-stepper__circle { background: var(--dp-primary); color: var(--dp-on-primary); }
.btp-stepper__node--active .btp-stepper__circle { background: var(--dp-surface-container-lowest); color: var(--dp-primary); }
.btp-stepper__title { font-size: .75rem; font-weight: 700; }
.btp-stepper__sub { font-size: .6875rem; opacity: .85; }
.btp-stepper__node--next .btp-stepper__sub { color: var(--dp-primary); font-weight: 700; }

.btp-cta-banner { padding: 18px 20px; border-radius: var(--dp-card-radius); background: var(--dp-primary); color: var(--dp-on-primary); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; }
.btp-cta-banner__head { display: flex; align-items: center; gap: 8px; font-size: .875rem; font-weight: 700; margin-bottom: 4px; }
.btp-cta-banner p { font-size: .75rem; line-height: 1.5; margin: 0; opacity: .9; max-width: 640px; }
.btp-cta-banner__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1200px) {
    .btp-kpi-grid { grid-template-columns: repeat(3, 1fr); }
    .btp-waterfall-grid { grid-template-columns: repeat(2, 1fr); }
    .btp-stepper { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 1024px) {
    .btp-grid-5-7, .btp-grid-6-6 { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .btp-kpi-grid { grid-template-columns: 1fr 1fr; }
    .btp-metric4-grid { grid-template-columns: 1fr 1fr; }
    .btp-waterfall-grid { grid-template-columns: 1fr; }
    .btp-stepper { grid-template-columns: 1fr; }
    .btp-header__actions { width: 100%; }
    .btp-header__actions .btp-btn { flex: 1; }
    .btp-collections-table { min-width: 640px; }
}
</style>
