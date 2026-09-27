<script setup>
/* ── Structural/visual port of the uploaded "Bean Origin — Farmer
   Dashboard" mockup (code.html), restyled with a page-scoped token
   block (--fd-*) mapped 1:1 from the mockup's own "Scientific Atelier"
   palette (Deep Emerald / Roasted Umber) rather than the app's own
   --dp-* theme — same convention already used on the Documentation and
   RFQ page ports. Sits on MainLayout, which already supplies the
   sidebar/topbar chrome the mockup's own aside/header duplicated, so
   only the content area below is ported. Per explicit instruction this
   pass is pure dummy content — nothing here posts to the backend; the
   AI copilot box is a local-only canned-response demo, same pattern as
   the Documentation page's AI-ask box. ── */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AddFarmModal from '@/Components/Modals/AddFarmModal.vue';

defineProps({
    title: { type: String, default: 'Farmer Dashboard' },
    hasProfile: { type: Boolean, default: false },
    currentRole: { type: String, default: null },
    roles: { type: Array, default: () => [] },
    showSelectRoleModal: { type: Boolean, default: false },
    myFarms: {
        type: Object,
        default: () => ({ data: [], meta: { current_page: 1, last_page: 1, per_page: 5, total: 0 } }),
    },
    farmKpis: {
        type: Object,
        default: () => ({
            farm_count: 0,
            pending_farm_count: 0,
            cultivated_area_ha: 0,
            available_collection_count: 0,
            available_quantity_kg: 0,
            portfolio_value: 0,
            avg_price_per_kg: null,
        }),
    },
    pipeline: {
        type: Object,
        default: () => ({ nodes: [], escrow: { value: 0, sub: 'Smart Escrow Hold', tag: 'No Active Holds' } }),
    },
});

/* ── My Registered Farms — real Farm rows owned by the current user
   (see FarmerController::index()). Pagination is a real server
   round-trip, same pattern as Batch/BatchesPage.vue. ────────────────── */
function goToPage(page) {
    router.get(route('farmer.index'), { page }, { preserveState: true, preserveScroll: true, replace: true });
}
const FARM_STATUS_LABELS = { active: 'Active', inactive: 'Inactive', pending: 'Pending' };
const farmStatusLabel = (status) => FARM_STATUS_LABELS[status] || status || 'Active';
const farmStatusTone = (status) => (status === 'inactive' ? 'muted' : status === 'pending' ? 'secondary' : 'primary');
const farmLocation = (farm) => [farm.district, farm.region].filter(Boolean).join(', ') || '—';
const addFarmDialogOpen = ref(false);

/* ── KPI strip — real, derived from Farm + FarmCollection data for the
   current user (see FarmerController::index()'s $farmKpis block). ──── */
const numberFmt = new Intl.NumberFormat('en-US');
const moneyFmt = new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
function formatWeight(kg) {
    const n = Number(kg) || 0;
    return n >= 1000 ? `${numberFmt.format(Math.round((n / 1000) * 10) / 10)} MT` : `${numberFmt.format(Math.round(n))} kg`;
}

const forecastDays = [
    { day: 'MON', icon: 'rainy', temp: '21°', rain: '24mm', tone: 'default' },
    { day: 'TUE', icon: 'thunderstorm', temp: '19°', rain: '42mm', tone: 'alert' },
    { day: 'WED', icon: 'grain', temp: '20°', rain: '12mm', tone: 'default' },
    { day: 'THU', icon: 'partly_cloudy_day', temp: '23°', rain: '2mm', tone: 'default' },
    { day: 'FRI', icon: 'wb_sunny', temp: '25°', rain: '0mm', tone: 'default' },
    { day: 'SAT', icon: 'wb_sunny', temp: '26°', rain: '0mm', tone: 'default' },
    { day: 'SUN', icon: 'cloud', temp: '24°', rain: '1mm', tone: 'default' },
];

/* ── Traceability pipeline node value display — nodes carry a raw kg
   number plus value_unit: 'kg' for weight-based steps (see
   FarmerController::traceabilityPipeline()); other steps (Farm counts,
   the Exchange "Listed"/"Not Listed" state) already arrive pre-formatted
   as strings. ─────────────────────────────────────────────────────── */
function pipelineNodeValue(node) {
    return node.value_unit === 'kg' ? formatWeight(node.value) : node.value;
}

/* ── My Farms portfolio (dummy) ──────────────────────────────────────── */
const farms = [
    { name: 'Kisoro Coffee Farm', altitude: '2,000m ASL', area: '6.5 ha • Arabica', stage: 'Fruit Ripening (72%)', stageTone: 'primary', note: 'Rain Expected', noteTone: 'error', eudr: '2901-UG', hue: '150' },
    { name: 'Mubende Robusta', altitude: '1,300m ASL', area: '8.0 ha • Fine Robusta', stage: 'Harvest Peak (88%)', stageTone: 'secondary', note: 'Partly Cloudy 24°C', noteTone: 'muted', eudr: '1142-UG', hue: '18' },
    { name: 'Mukono Agroforest', altitude: '1,150m ASL', area: '4.0 ha • Shade Robusta', stage: 'Flowering (35%)', stageTone: 'muted', note: 'Sunny 26°C', noteTone: 'muted', eudr: '8840-UG', hue: '95' },
];

/* ── Farm inputs & IPM stock (dummy) ─────────────────────────────────── */
const inputs = [
    { item: 'Bio-Organic Compost', farm: 'Mubende Estate', total: '1,000 kg / 800 kg', remaining: '200 kg', date: '30 Sep 2026', status: 'Low Stock', tone: 'error' },
    { item: 'Foliar Micronutrients (Zinc/Boron)', farm: 'Kisoro Highland', total: '50 L / 35 L', remaining: '15 L', date: '28 Sep 2026', status: 'Available', tone: 'primary' },
    { item: 'Organic Neem Pest Barrier', farm: 'Kisoro Highland', total: '20 L / 16 L', remaining: '4 L', date: '25 Sep 2026', status: 'Reorder Needed', tone: 'secondary' },
];

/* ── Farmgate collection receipts (dummy) ────────────────────────────── */
const receipts = [
    { code: 'COL-2026-00124', status: 'Verified at Wet Mill', tone: 'primary', icon: 'receipt_long', origin: 'Mubende Estate', volume: '4,500 kg Cherry', extraLabel: 'Brix', extraValue: '22.4°', batch: 'BTH-2026-048', action: 'Inspect Traceability' },
    { code: 'COL-2026-00121', status: 'Dry Mill Stored', tone: 'secondary', icon: 'inventory', origin: 'Kisoro Highland Estate', volume: '1,900 kg Parchment', extraLabel: 'Moisture', extraValue: '11.8%', batch: 'BTH-2026-039', action: 'View Batch' },
];

/* ── Today's field tasks (dummy) ─────────────────────────────────────── */
const tasks = ref([
    { title: 'Kisoro: Parabolic Dryer Moisture Check', note: 'Target moisture threshold 11.5% before rain onset.', due: 'Due 11:00 AM', priority: 'HIGH', done: false },
    { title: 'Mubende: Coffee Berry Borer (CBB) Trap Log', note: 'Inspect Block C pheromone traps & enter count.', due: 'Due 02:00 PM', priority: 'MEDIUM', done: false },
    { title: 'Kisoro: Distribute 100 GrainPro Liners', note: "Staging bags ready for Thursday's dry picking window.", due: 'Due 04:30 PM', priority: 'HIGH', done: false },
    { title: 'Mukono: Soil Sensor Moisture Log', note: 'Volumetric moisture logged at 28% saturation.', due: 'Completed 07:30 AM', priority: null, done: true },
]);

/* ── Farm health telemetry (dummy) ───────────────────────────────────── */
const healthMetrics = [
    { label: 'Crop Vigor Index', value: 'Good (NDVI 0.78)', tone: 'primary' },
    { label: 'Pest Pressure Risk', value: 'Low (Normal)', tone: 'primary' },
    { label: 'Fungal/Rust Vulnerability', value: 'Moderate (High RH)', tone: 'secondary' },
    { label: 'Soil Moisture (Root Zone)', value: '26–30% Optimal', tone: 'default' },
    { label: 'Water Canopy Reserve', value: 'Abundant', tone: 'primary' },
];

/* ── Market benchmarks & RFQs (dummy) ────────────────────────────────── */
const marketRefs = [
    { label: 'Arabica Ref', value: '$5.10 / kg', change: '+2.1% this week' },
    { label: 'Robusta Ref', value: '$4.15 / kg', change: '+1.2% this week' },
];
const rfqs = [
    { buyer: 'Dubai Specialty Importer', price: '$4.10/kg CIF', seeking: 'Seeking: 15 MT Robusta Screen 18 Washed', action: 'View RFQ Details', primary: false },
    { buyer: 'Hamburg Organic Roaster', price: '$5.30/kg FOB', seeking: 'Seeking: 5 MT Bugisu Washed AA Grade', action: 'Submit Lot Allocation', primary: true },
];

/* ── Bean Origin Copilot (local canned-response demo, mirrors the
   Documentation page's AI-ask pattern — illustrative only) ──────────── */
const copilotPrompts = [
    "How should I adjust drying for tomorrow's rain?",
    'Calculate expected cherry-to-green yield ratio.',
    'Prepare 4.5 MT Mubende batch for collection.',
];
const copilotQuery = ref('');
const copilotAsked = ref(false);
const copilotResponse = ref('');
function askCopilot(prompt) {
    const q = (prompt ?? copilotQuery.value).trim();
    if (!q) return;
    copilotQuery.value = q;
    copilotAsked.value = true;
    copilotResponse.value = 'Based on current soil-moisture telemetry and the 42mm rain forecast for Tuesday, hold patio drying at Kisoro and move wet parchment under cover tonight — resuming outdoor drying Thursday keeps moisture loss on schedule for the 11.5% target.';
}
</script>

<template>
    <MainLayout title="Farmer Dashboard">
        <Head title="Farmer Dashboard">
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
        </Head>

        <div class="fd-page">
            <!-- ── Greeting banner ──────────────────────────────────────── -->
            <div class="fd-banner">
                <div class="fd-banner__top">
                    <div class="fd-banner__copy">
                        <h1 class="fd-title">Farmer Dashboard</h1>
                        <p class="fd-subtitle">Here is your institutional agronomic overview, collection schedules, and EUDR traceability log today.</p>
                    </div>
                    <div class="fd-banner__actions">
                        <button type="button" class="fd-btn fd-btn--muted" @click="addFarmDialogOpen = true"><span class="material-symbols-outlined">add</span> Add Farm</button>
                        <button type="button" class="fd-btn fd-btn--muted"><span class="material-symbols-outlined">local_shipping</span> Record Collection</button>
                        <button type="button" class="fd-btn fd-btn--primary"><span class="material-symbols-outlined">agriculture</span> Record Harvest</button>
                        <button type="button" class="fd-btn fd-btn--secondary"><span class="material-symbols-outlined">auto_awesome</span> Ask Bean Origin AI</button>
                    </div>
                </div>
            </div>

            <div class="fd-body">
             

                <!-- ── 2. KPI strip — real Farm + FarmCollection data ────── -->
                <div class="fd-kpi-grid">
                    <div class="fd-kpi">
                        <div class="fd-kpi__head"><span class="fd-kpi__label">My Managed Farms</span><span class="material-symbols-outlined fd-tone-primary">terrain</span></div>
                        <div class="fd-kpi__value">{{ farmKpis.farm_count }} <span class="fd-kpi__unit">{{ farmKpis.farm_count === 1 ? 'Unit' : 'Units' }}</span></div>
                        <p class="fd-kpi__sub"><span class="fd-strong">{{ farmKpis.cultivated_area_ha }} ha</span> cultivated canopy</p>
                    </div>
                    <div class="fd-kpi">
                        <div class="fd-kpi__head"><span class="fd-kpi__label">Farm Verification</span><span class="material-symbols-outlined fd-tone-secondary">verified</span></div>
                        <div class="fd-kpi__value fd-mono">{{ farmKpis.farm_count - farmKpis.pending_farm_count }}<span class="fd-kpi__unit">/{{ farmKpis.farm_count }}</span></div>
                        <p class="fd-kpi__sub" :class="farmKpis.pending_farm_count ? 'fd-tone-secondary' : 'fd-tone-primary'">
                            {{ farmKpis.pending_farm_count ? `${farmKpis.pending_farm_count} pending review` : 'All farms verified' }}
                        </p>
                    </div>
                    <div class="fd-kpi">
                        <div class="fd-kpi__head"><span class="fd-kpi__label">Coffee Available</span><span class="material-symbols-outlined fd-tone-primary">inventory_2</span></div>
                        <div class="fd-kpi__value fd-mono">{{ formatWeight(farmKpis.available_quantity_kg) }}</div>
                        <p class="fd-kpi__sub">Unbatched collection stock</p>
                    </div>
                    <div class="fd-kpi">
                        <div class="fd-kpi__head"><span class="fd-kpi__label">Active Collections</span><span class="material-symbols-outlined fd-tone-tertiary">local_shipping</span></div>
                        <div class="fd-kpi__value fd-mono">{{ farmKpis.available_collection_count }} <span class="fd-kpi__unit">{{ farmKpis.available_collection_count === 1 ? 'Load' : 'Loads' }}</span></div>
                        <p class="fd-kpi__sub"><span class="fd-strong">{{ formatWeight(farmKpis.available_quantity_kg) }}</span> awaiting batch assignment</p>
                    </div>
                    <div class="fd-kpi fd-kpi--solid">
                        <div class="fd-kpi__head"><span class="fd-kpi__label">Est. Portfolio Value</span><span class="material-symbols-outlined">payments</span></div>
                        <div class="fd-kpi__value fd-mono">${{ moneyFmt.format(farmKpis.portfolio_value) }}</div>
                        <p class="fd-kpi__sub">{{ farmKpis.avg_price_per_kg !== null ? `Avg $${farmKpis.avg_price_per_kg.toFixed(2)} / kg` : 'No priced collections yet' }}</p>
                    </div>
                </div>

                <!-- ── 3. Weather & agronomic advisory ──────────────────── -->
                <div class="fd-card">
                    <div class="fd-weather-head">
                        <div class="fd-weather-head__title">
                            <div class="fd-icon-box"><span class="material-symbols-outlined">cloud_sync</span></div>
                            <div>
                                <h2 class="fd-h2">Microclimate Telemetry &amp; Field Weather</h2>
                                <p class="fd-muted-text">Hyperlocal precipitation, drying indices, and parabolic moisture risk advisory</p>
                            </div>
                        </div>
                    </div>

                    <div class="fd-weather-grid">
                        <div class="fd-weather-current">
                            <div class="fd-weather-current__head">
                                <span class="fd-eyebrow">Current Conditions</span>
                                <span class="fd-pill fd-pill--error">Rain Alert</span>
                            </div>
                            <div class="fd-weather-current__temp">
                                <span class="fd-mono">21°C</span>
                                <span class="fd-muted-text">Overcast • Showers expected</span>
                            </div>
                            <div class="fd-weather-metrics">
                                <div class="fd-weather-metric"><span>Precipitation</span><strong class="fd-mono">78% (24mm)</strong></div>
                                <div class="fd-weather-metric"><span>Humidity</span><strong class="fd-mono">84% RH</strong></div>
                                <div class="fd-weather-metric"><span>Wind Velocity</span><strong class="fd-mono">12 km/h NE</strong></div>
                                <div class="fd-weather-metric"><span>Solar Radiance</span><strong class="fd-mono">3.8 kWh/m²</strong></div>
                            </div>
                        </div>

                        <div class="fd-weather-forecast">
                            <div class="fd-forecast-row">
                                <div v-for="d in forecastDays" :key="d.day" class="fd-forecast-pill" :class="{ 'fd-forecast-pill--alert': d.tone === 'alert' }">
                                    <span class="fd-forecast-pill__day">{{ d.day }}</span>
                                    <span class="material-symbols-outlined">{{ d.icon }}</span>
                                    <span class="fd-mono fd-forecast-pill__temp">{{ d.temp }}</span>
                                    <span class="fd-forecast-pill__rain">{{ d.rain }}</span>
                                </div>
                            </div>
                            <div class="fd-advisory">
                                <span class="material-symbols-outlined fd-tone-secondary">warning</span>
                                <div>
                                    <h3 class="fd-advisory__title">Agronomic Weather Directive: Heavy Rain Alert</h3>
                                    <p class="fd-muted-text">Heavy rain expected tomorrow across Southwestern highlands. Pause outdoor patio drying; transfer wet parchment into covered parabolic solar dryers immediately. Cherry picking recommended to resume Thursday dry window.</p>
                                </div>
                                <div class="fd-advisory__actions">
                                    <button type="button" class="fd-btn fd-btn--sm fd-btn--muted">14-Day View</button>
                                    <button type="button" class="fd-btn fd-btn--sm fd-btn--primary">Plan Operations</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── 4. Traceability pipeline ──────────────────────────── -->
                <div class="fd-card">
                    <div class="fd-section-head">
                        <div class="fd-section-head__title">
                            <span class="material-symbols-outlined fd-tone-primary">account_tree</span>
                            <h2 class="fd-h2">Physical Coffee Traceability Flow</h2>
                            <span class="fd-mono-note fd-pipeline-chip">EUDR Compliant Chain of Custody</span>
                        </div>
                        <button type="button" class="fd-link-btn">Export Full Manifest (CSV)</button>
                    </div>
                    <div class="fd-pipeline">
                        <div v-for="node in pipeline.nodes" :key="node.step" class="fd-pipeline__node" :class="`fd-pipeline__node--${node.tone || 'primary'}`">
                            <span class="fd-pipeline__step">{{ node.step }}</span>
                            <div class="fd-pipeline__value fd-mono">{{ pipelineNodeValue(node) }}</div>
                            <p class="fd-pipeline__sub">{{ node.sub }}</p>
                            <span class="fd-pipeline__tag">{{ node.tag }}</span>
                        </div>
                        <div class="fd-pipeline__node fd-pipeline__node--solid">
                            <span class="fd-pipeline__step">6. Escrow <span class="material-symbols-outlined">lock</span></span>
                            <div class="fd-pipeline__value fd-mono">${{ moneyFmt.format(pipeline.escrow.value) }}</div>
                            <p class="fd-pipeline__sub">{{ pipeline.escrow.sub }}</p>
                            <span class="fd-pipeline__tag">{{ pipeline.escrow.tag }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── 5. Two-column operational grid ────────────────────── -->
                <div class="fd-grid-columns">
                    <!-- LEFT -->
                    <div class="fd-grid-main">
                        <!-- My Registered Farms — real Farm rows owned by this user -->
                        <div class="fd-card">
                            <div class="fd-section-head">
                                <div>
                                    <div class="fd-card-title-row"><span class="material-symbols-outlined fd-tone-primary">landscape</span><h2 class="fd-h2">My Registered Farms</h2></div>
                                    <p class="fd-muted-text">All farms registered to your account, most recently added first</p>
                                </div>
                                <span class="fd-mono-note">{{ myFarms.meta.total }} total</span>
                            </div>
                            <el-table :data="myFarms.data" class="fd-el-table" table-layout="fixed">
                                <el-table-column label="Farm &amp; Location">
                                    <template #default="{ row }">
                                        <div class="fd-strong">{{ row.name }}</div>
                                        <div class="fd-table-sub">{{ farmLocation(row) }}</div>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Coffee Type" width="120"><template #default="{ row }"><span class="fd-muted-text">{{ row.coffee_type || '—' }}</span></template></el-table-column>
                                <el-table-column label="Area" width="110"><template #default="{ row }"><span class="fd-mono fd-strong">{{ row.coffee_area ?? row.total_area ?? '—' }} ha</span></template></el-table-column>
                                <el-table-column label="Status" width="100">
                                    <template #default="{ row }"><span class="fd-pill" :class="`fd-pill--${farmStatusTone(row.status)}`">{{ farmStatusLabel(row.status) }}</span></template>
                                </el-table-column>
                                <el-table-column label="" width="70" align="right">
                                    <template #default="{ row }"><Link :href="route('farm.show', row.id)" class="fd-link-btn">View</Link></template>
                                </el-table-column>
                                <template #empty>
                                    <span class="fd-muted-text">You haven't registered any farms yet.</span>
                                </template>
                            </el-table>
                            <div v-if="myFarms.meta.last_page > 1" class="fd-card__foot fd-card__foot--center">
                                <el-pagination
                                    :current-page="myFarms.meta.current_page"
                                    :page-size="myFarms.meta.per_page"
                                    :total="myFarms.meta.total"
                                    layout="prev, pager, next"
                                    @current-change="goToPage"
                                />
                            </div>
                        </div>

                        <!-- My Farms Portfolio -->
                        <div class="fd-card">
                            <div class="fd-section-head">
                                <div>
                                    <div class="fd-card-title-row"><span class="material-symbols-outlined fd-tone-primary">grid_view</span><h2 class="fd-h2">My Farms Portfolio</h2></div>
                                    <p class="fd-muted-text">Registered cadastral plots with satellite NDVI and micro-climate tags</p>
                                </div>
                                <div class="fd-toggle-group">
                                    <button type="button" class="fd-toggle fd-toggle--active">Cards</button>
                                    <button type="button" class="fd-toggle">Map View</button>
                                </div>
                            </div>
                            <div class="fd-farm-grid">
                                <div v-for="farm in farms" :key="farm.name" class="fd-farm-card">
                                    <div class="fd-farm-card__image" :style="{ background: `linear-gradient(135deg, hsl(${farm.hue} 45% 30%), hsl(${farm.hue} 55% 18%))` }">
                                        <span class="fd-farm-card__name">{{ farm.name }}</span>
                                        <span class="fd-farm-card__alt">{{ farm.altitude }}</span>
                                    </div>
                                    <div class="fd-farm-card__body">
                                        <div class="fd-farm-card__row"><span>Area / Crop</span><strong>{{ farm.area }}</strong></div>
                                        <div class="fd-farm-card__row"><span>Stage</span><strong :class="`fd-tone-${farm.stageTone}`">{{ farm.stage }}</strong></div>
                                        <div class="fd-farm-card__row"><span>Weather</span><strong :class="farm.noteTone === 'error' ? 'fd-tone-error fd-mono' : ''">{{ farm.note }}</strong></div>
                                        <div class="fd-farm-card__foot">
                                            <span class="fd-mono-note">EUDR ID: {{ farm.eudr }}</span>
                                            <button type="button" class="fd-link-btn">Manage Block</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Farm Inputs -->
                        <div class="fd-card">
                            <div class="fd-section-head">
                                <div>
                                    <div class="fd-card-title-row"><span class="material-symbols-outlined fd-tone-primary">eco</span><h2 class="fd-h2">Farm Inputs, Nutrition &amp; IPM Stock</h2></div>
                                    <p class="fd-muted-text">Organic certified soil conditioners, organic fungicides, and regenerative inputs</p>
                                </div>
                                <button type="button" class="fd-btn fd-btn--muted">Log Input Usage</button>
                            </div>
                            <el-table :data="inputs" class="fd-el-table" table-layout="fixed">
                                <el-table-column label="Input Item"><template #default="{ row }"><span class="fd-strong">{{ row.item }}</span></template></el-table-column>
                                <el-table-column label="Assigned Farm" width="120"><template #default="{ row }"><span class="fd-muted-text">{{ row.farm }}</span></template></el-table-column>
                                <el-table-column label="Total / Used" width="110"><template #default="{ row }"><span class="fd-mono">{{ row.total }}</span></template></el-table-column>
                                <el-table-column label="Remaining" width="90"><template #default="{ row }"><span class="fd-mono fd-strong" :class="`fd-tone-${row.tone}`">{{ row.remaining }}</span></template></el-table-column>
                                <el-table-column label="Target Date" width="100"><template #default="{ row }"><span class="fd-mono fd-muted-text">{{ row.date }}</span></template></el-table-column>
                                <el-table-column label="Status" width="110" align="right"><template #default="{ row }"><span class="fd-pill" :class="`fd-pill--${row.tone}`">{{ row.status }}</span></template></el-table-column>
                            </el-table>
                            <div class="fd-need-banner">
                                <div class="fd-need-banner__text">
                                    <span class="material-symbols-outlined fd-tone-secondary">local_mall</span>
                                    <span><strong class="fd-strong">Immediate Input Needs:</strong> <span class="fd-muted-text">300 kg NPK required by 28 Sep • 400 kg Compost required by 30 Sep</span></span>
                                </div>
                                <button type="button" class="fd-btn fd-btn--primary">Find Verified Local Suppliers</button>
                            </div>
                        </div>

                        <!-- Farmgate collection receipts -->
                        <div class="fd-card">
                            <div class="fd-section-head">
                                <div>
                                    <div class="fd-card-title-row"><span class="material-symbols-outlined fd-tone-primary">receipt_long</span><h2 class="fd-h2">Farmgate Collection Receipts &amp; Milling Handover</h2></div>
                                    <p class="fd-muted-text">Digital weighbridge certificates linked directly to downstream lot batches</p>
                                </div>
                                <button type="button" class="fd-link-btn">All Collections (48)</button>
                            </div>
                            <div class="fd-receipt-list">
                                <div v-for="r in receipts" :key="r.code" class="fd-receipt">
                                    <div class="fd-receipt__left">
                                        <div class="fd-receipt__icon"><span class="material-symbols-outlined">{{ r.icon }}</span></div>
                                        <div>
                                            <div class="fd-receipt__head">
                                                <span class="fd-mono fd-strong">{{ r.code }}</span>
                                                <span class="fd-pill" :class="`fd-pill--${r.tone}`">{{ r.status }}</span>
                                            </div>
                                            <div class="fd-receipt__meta">
                                                <span>Origin: <strong class="fd-strong">{{ r.origin }}</strong></span>
                                                <span>Volume: <strong class="fd-strong fd-mono">{{ r.volume }}</strong></span>
                                                <span>{{ r.extraLabel }}: <strong class="fd-strong fd-mono">{{ r.extraValue }}</strong></span>
                                                <span>Assigned Batch: <strong class="fd-tone-primary fd-mono">{{ r.batch }}</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="fd-btn fd-btn--sm fd-btn--muted">{{ r.action }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="fd-grid-aside">
                        <!-- Today's tasks -->
                        <div class="fd-card">
                            <div class="fd-section-head fd-section-head--tight">
                                <div class="fd-card-title-row"><span class="material-symbols-outlined fd-tone-primary">checklist</span><h2 class="fd-h3">Today's Field Tasks</h2></div>
                                <span class="fd-mono fd-tone-primary fd-strong">{{ tasks.filter(t => !t.done).length }} Remaining</span>
                            </div>
                            <div class="fd-task-list">
                                <label v-for="(task, idx) in tasks" :key="task.title" class="fd-task" :class="{ 'fd-task--done': task.done }">
                                    <input v-model="tasks[idx].done" type="checkbox" />
                                    <div>
                                        <span class="fd-task__title">{{ task.title }}</span>
                                        <span class="fd-task__note">{{ task.note }}</span>
                                        <div v-if="!task.done" class="fd-task__foot">
                                            <span class="fd-mono-note fd-tone-error">{{ task.due }}</span>
                                            <span class="fd-priority" :class="`fd-priority--${task.priority?.toLowerCase()}`">{{ task.priority }}</span>
                                        </div>
                                        <span v-else class="fd-mono-note fd-tone-primary">{{ task.due }}</span>
                                    </div>
                                </label>
                            </div>
                            <button type="button" class="fd-link-btn fd-link-btn--block"><span class="material-symbols-outlined">add</span> Add Custom Field Task</button>
                        </div>

                        <!-- Farm health telemetry -->
                        <div class="fd-card">
                            <div class="fd-section-head fd-section-head--tight">
                                <h2 class="fd-h3">Farm Health Telemetry</h2>
                                <span class="material-symbols-outlined fd-tone-primary">satellite_alt</span>
                            </div>
                            <p class="fd-italic-note">AI assessment based on Sentinel-2 satellite imagery &amp; IoT sensors</p>
                            <div class="fd-metric-list">
                                <div v-for="m in healthMetrics" :key="m.label" class="fd-metric-row">
                                    <div class="fd-metric-row__left"><span class="fd-dot" :class="`fd-dot--${m.tone}`"></span><span>{{ m.label }}</span></div>
                                    <span class="fd-mono fd-strong" :class="`fd-tone-${m.tone}`">{{ m.value }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Market benchmarks -->
                        <div class="fd-card">
                            <div class="fd-section-head fd-section-head--tight">
                                <h2 class="fd-h3">Live Coffee Market Benchmarks</h2>
                                <span class="material-symbols-outlined fd-tone-secondary">query_stats</span>
                            </div>
                            <div class="fd-market-grid">
                                <div v-for="ref_ in marketRefs" :key="ref_.label" class="fd-market-ref">
                                    <span class="fd-eyebrow">{{ ref_.label }}</span>
                                    <div class="fd-market-ref__value fd-mono">{{ ref_.value }}</div>
                                    <span class="fd-tone-primary fd-market-ref__change">{{ ref_.change }}</span>
                                </div>
                            </div>
                            <div class="fd-inventory-banner">
                                <div class="fd-inventory-banner__row"><span class="fd-muted-text">Your Ready Inventory Value:</span><span class="fd-mono fd-strong fd-tone-primary">$7,470 est.</span></div>
                                <span class="fd-mono-note">1.8 MT dry parchment available for lot assignment</span>
                            </div>
                            <span class="fd-eyebrow" style="display:block;margin-bottom:6px;">Live Buyer RFQs Matching Your Profile</span>
                            <div class="fd-rfq-list">
                                <div v-for="rfq in rfqs" :key="rfq.buyer" class="fd-rfq">
                                    <div class="fd-rfq__row"><span class="fd-strong">{{ rfq.buyer }}</span><span class="fd-mono fd-strong fd-tone-primary">{{ rfq.price }}</span></div>
                                    <span class="fd-muted-text">{{ rfq.seeking }}</span>
                                    <button type="button" class="fd-btn fd-btn--block" :class="rfq.primary ? 'fd-btn--primary' : 'fd-btn--muted'">{{ rfq.action }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Bean Origin Copilot -->
                        <div class="fd-card">
                            <div class="fd-copilot-head">
                                <span class="material-symbols-outlined fd-tone-secondary">smart_toy</span>
                                <div>
                                    <h2 class="fd-h3">Bean Origin Copilot</h2>
                                    <span class="fd-mono-note">Agronomic &amp; Market Advisory</span>
                                </div>
                            </div>
                            <div class="fd-prompt-list">
                                <button v-for="p in copilotPrompts" :key="p" type="button" class="fd-prompt" @click="askCopilot(p)">
                                    <span>"{{ p }}"</span>
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </button>
                            </div>
                            <div v-if="copilotAsked" class="fd-copilot-response">{{ copilotResponse }}</div>
                            <div class="fd-ai-input">
                                <input v-model="copilotQuery" type="text" placeholder="Ask anything about crops, pests, market..." @keydown.enter="askCopilot()" />
                                <button type="button" class="fd-ai-send" @click="askCopilot()"><span class="material-symbols-outlined">send</span></button>
                            </div>
                        </div>

                        <!-- EUDR & Sustainability ledger -->
                        <div class="fd-card">
                            <div class="fd-section-head fd-section-head--tight">
                                <h2 class="fd-h3">EUDR &amp; Sustainability Ledger</h2>
                                <span class="material-symbols-outlined fd-tone-primary">verified_user</span>
                            </div>
                            <div class="fd-eudr-box">
                                <div class="fd-eudr-box__row"><span class="fd-strong">EUDR Polygon Verification</span><span class="fd-mono fd-strong fd-tone-primary">4 / 4 Farms</span></div>
                                <p class="fd-muted-text">Zero deforestation confirmed via Landsat 2020 baseline comparison.</p>
                            </div>
                            <div class="fd-chip-row">
                                <span class="fd-chip">Organic Certified (UCDA)</span>
                                <span class="fd-chip">Rainforest Alliance</span>
                                <span class="fd-chip">Fairtrade Africa</span>
                            </div>
                            <div class="fd-completeness">
                                <div class="fd-completeness__row"><span class="fd-muted-text">Sustainability Profile Completeness</span><span class="fd-mono fd-strong fd-tone-primary">88%</span></div>
                                <div class="fd-progress-track"><div class="fd-progress-fill" style="width:88%;"></div></div>
                            </div>
                            <button type="button" class="fd-btn fd-btn--muted fd-btn--block">Update Sustainability Records</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Add Farm modal — same component/behavior as /farm/farm-list ── -->
        <AddFarmModal v-model="addFarmDialogOpen" />
    </MainLayout>
</template>

<style scoped>
.fd-page {
    /* Page-scoped token block mapped 1:1 from the mockup's own
       "Scientific Atelier" palette — kept separate from the app's
       --dp-* theme so this page renders exactly as designed. */
    --fd-primary: #004532;
    --fd-primary-container: #065f46;
    --fd-on-primary: #ffffff;
    --fd-primary-fixed: #a6f2d1;
    --fd-on-primary-fixed: #002116;
    --fd-secondary: #725a42;
    --fd-secondary-fixed: #fedcbe;
    --fd-on-secondary-fixed: #291806;
    --fd-tertiary: #343c51;
    --fd-tertiary-fixed: #dae2fd;
    --fd-on-tertiary-fixed: #131b2e;
    --fd-error: #ba1a1a;
    --fd-error-container: #ffdad6;
    --fd-on-error-container: #93000a;
    --fd-surface: #f7f9fb;
    --fd-surface-container-lowest: #ffffff;
    --fd-surface-container-low: #f2f4f6;
    --fd-surface-container: #eceef0;
    --fd-surface-container-high: #e6e8ea;
    --fd-surface-container-highest: #e0e3e5;
    --fd-on-surface: #191c1e;
    --fd-on-surface-variant: #3f4944;
    --fd-outline-variant: #bec9c2;

    font-family: var(--dp-font-sans);
    color: var(--fd-on-surface);
    display: flex;
    flex-direction: column;
}
.fd-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; font-size: 18px; }
.fd-tone-primary { color: var(--fd-primary) !important; }
.fd-tone-secondary { color: var(--fd-secondary) !important; }
.fd-tone-tertiary { color: var(--fd-tertiary) !important; }
.fd-tone-error { color: var(--fd-error) !important; }
.fd-strong { font-weight: 700; color: var(--fd-on-surface); }
.fd-muted-text { font-size: var(--dp-content-font-size); color: var(--fd-on-surface-variant); line-height: 1.6; margin: 0; }
.fd-mono { font-family: var(--dp-font-mono); }
.fd-mono-note { font-size: 10.5px; font-family: var(--dp-font-mono); color: var(--fd-on-surface-variant); }
.fd-eyebrow { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--fd-on-surface-variant); }
.fd-h2 { font-size: 1rem; font-weight: 800; margin: 0; color: var(--fd-on-surface); }
.fd-h3 { font-size: 13px; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: .03em; color: var(--fd-on-surface); }
.fd-italic-note { font-size: 11px; color: var(--fd-on-surface-variant); font-style: italic; margin: 0 0 12px; }

.fd-card { background: var(--fd-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 16px; padding: 20px; }
.fd-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding-top: 14px; margin-top: 6px; }
.fd-section-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding-bottom: 14px; }
.fd-section-head--tight { align-items: center; padding-bottom: 10px; }
.fd-section-head__title { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.fd-card-title-row { display: flex; align-items: center; gap: 8px; margin-bottom: 2px; }
.fd-link-btn { border: none; background: none; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--fd-primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 0; }
.fd-link-btn .material-symbols-outlined { font-size: 15px; }
.fd-link-btn--block { width: 100%; justify-content: center; padding: 8px; border-radius: 8px; }
.fd-link-btn--block:hover { background: var(--fd-surface-container-low); }

/* Buttons */
.fd-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; border-radius: 8px; border: none; cursor: pointer; font-size: var(--dp-content-font-size); font-weight: 700; white-space: nowrap; }
.fd-btn .material-symbols-outlined { font-size: 16px; }
.fd-btn--sm { padding: 6px 10px; font-size: 11px; }
.fd-btn--block { width: 100%; justify-content: center; margin-top: 6px; }
.fd-btn--muted { background: var(--fd-surface-container-low); color: var(--fd-on-surface); }
.fd-btn--muted:hover { background: var(--fd-surface-container-high); }
.fd-btn--primary { background: var(--fd-primary); color: var(--fd-on-primary); }
.fd-btn--primary:hover { background: var(--fd-primary-container); }
.fd-btn--secondary { background: var(--fd-secondary-fixed); color: var(--fd-on-secondary-fixed); }
.fd-btn--secondary:hover { opacity: .85; }
.fd-btn--error { background: var(--fd-error); color: #ffffff; }
.fd-btn--error:hover { opacity: .9; }

/* Banner */
.fd-banner { background: var(--fd-surface-container-lowest); padding: 0 0 20px; border-bottom: 1px solid var(--dp-outline-variant); }
.fd-banner__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.fd-title { font-size: 1.75rem; font-weight: 800; letter-spacing: -.01em; margin: 4px 0 4px; color: var(--fd-on-surface); }
.fd-subtitle { font-size: var(--dp-content-font-size); color: var(--fd-on-surface-variant); margin: 0; max-width: 640px; }
.fd-banner__actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.fd-body { padding: 20px 0 0; display: flex; flex-direction: column; gap: 20px; }

/* Alert hub */
.fd-alert-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; }
.fd-alert-card { background: var(--fd-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 14px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
.fd-alert-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; }
.fd-alert-card--error::before { background: var(--fd-error); }
.fd-alert-card--secondary::before { background: var(--fd-secondary); }
.fd-alert-card--tertiary::before { background: var(--fd-tertiary); }
.fd-alert-card--primary::before { background: var(--fd-primary); }
.fd-alert-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
.fd-alert-tag { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; padding: 2px 7px; border-radius: 5px; }
.fd-alert-tag .material-symbols-outlined { font-size: 13px; }
.fd-alert-tag--error { color: var(--fd-error); background: rgba(186,26,26,.08); }
.fd-alert-tag--secondary { color: var(--fd-secondary); background: var(--fd-secondary-fixed); }
.fd-alert-tag--tertiary { color: var(--fd-tertiary); background: var(--fd-tertiary-fixed); }
.fd-alert-tag--primary { color: var(--fd-primary); background: var(--fd-primary-fixed); }
.fd-alert-card__title { font-size: 13.5px; font-weight: 800; margin: 0; color: var(--fd-on-surface); }
.fd-alert-card__desc { font-size: 12px; color: var(--fd-on-surface-variant); margin: 4px 0 0; line-height: 1.5; }
.fd-alert-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 12px; margin-top: 12px; }
.fd-alert-card__cta { font-size: 11px; font-weight: 700; }

/* KPI strip */
.fd-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; }
.fd-kpi { background: var(--fd-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 14px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; }
.fd-kpi--solid { background: var(--fd-primary); border-color: rgba(255, 255, 255, .18); color: var(--fd-on-primary); }
.fd-kpi--solid .fd-kpi__label { color: var(--fd-primary-fixed); }
.fd-kpi--solid .fd-kpi__value { color: var(--fd-on-primary); }
.fd-kpi--solid .fd-kpi__sub { color: var(--fd-primary-fixed); }
.fd-kpi__head { display: flex; align-items: center; justify-content: space-between; }
.fd-kpi__label { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--fd-on-surface-variant); }
.fd-kpi__value { font-size: 22px; font-weight: 800; margin-top: 10px; color: var(--fd-on-surface); }
.fd-kpi__unit { font-size: 13px; font-weight: 500; color: var(--fd-on-surface-variant); }
.fd-kpi__sub { font-size: 11px; color: var(--fd-on-surface-variant); margin: 3px 0 0; }

/* Weather */
.fd-weather-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding-bottom: 16px; }
.fd-weather-head__title { display: flex; align-items: center; gap: 12px; }
.fd-icon-box { width: 40px; height: 40px; border-radius: 10px; background: var(--fd-surface-container-low); color: var(--fd-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fd-icon-box .material-symbols-outlined { font-size: 22px; }
.fd-weather-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); gap: 20px; }
.fd-weather-current { background: var(--fd-surface-container-low); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; }
.fd-weather-current__head { display: flex; align-items: center; justify-content: space-between; }
.fd-pill { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 5px; font-size: 10px; font-weight: 800; }
.fd-pill--error { background: var(--fd-error-container); color: var(--fd-on-error-container); }
.fd-pill--primary { background: var(--fd-primary-fixed); color: var(--fd-on-primary-fixed); }
.fd-pill--secondary { background: var(--fd-secondary-fixed); color: var(--fd-on-secondary-fixed); }
.fd-pill--muted { background: var(--fd-surface-container-high); color: var(--fd-on-surface); }
.fd-weather-current__temp { display: flex; align-items: baseline; gap: 10px; margin-top: 14px; }
.fd-weather-current__temp .fd-mono { font-size: 34px; font-weight: 800; color: var(--fd-on-surface); }
.fd-weather-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 18px; }
.fd-weather-metric { background: var(--fd-surface-container-lowest); border-radius: 8px; padding: 9px; display: flex; flex-direction: column; gap: 3px; }
.fd-weather-metric span { font-size: 9.5px; color: var(--fd-on-surface-variant); text-transform: uppercase; font-weight: 700; letter-spacing: .03em; }
.fd-weather-metric strong { font-size: 13px; color: var(--fd-on-surface); }
.fd-weather-forecast { display: flex; flex-direction: column; justify-content: space-between; gap: 14px; }
.fd-forecast-row { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
.fd-forecast-pill { padding: 8px 4px; border-radius: 8px; background: var(--fd-surface-container-low); text-align: center; display: flex; flex-direction: column; align-items: center; gap: 3px; }
.fd-forecast-pill--alert { background: var(--fd-error-container); }
.fd-forecast-pill--alert .fd-forecast-pill__day,
.fd-forecast-pill--alert .fd-forecast-pill__temp { color: var(--fd-error); }
.fd-forecast-pill--alert .material-symbols-outlined { color: var(--fd-error); }
.fd-forecast-pill--alert .fd-forecast-pill__rain { color: var(--fd-error); font-weight: 700; }
.fd-forecast-pill__day { font-size: 10px; font-weight: 700; color: var(--fd-on-surface-variant); }
.fd-forecast-pill .material-symbols-outlined { color: var(--fd-primary); font-size: 20px; }
.fd-forecast-pill__temp { font-size: 12px; font-weight: 800; color: var(--fd-on-surface); }
.fd-forecast-pill__rain { font-size: 9px; color: var(--fd-on-surface-variant); }
.fd-advisory { background: var(--fd-surface-container-low); border-radius: 12px; padding: 14px; display: flex; align-items: flex-start; gap: 10px; flex-wrap: wrap; }
.fd-advisory__title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .02em; margin: 0 0 3px; color: var(--fd-on-surface); }
.fd-advisory > div { flex: 1; min-width: 200px; }
.fd-advisory__actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

/* Pipeline */
.fd-pipeline-chip { background: var(--fd-surface-container-low); padding: 2px 8px; border-radius: 5px; }
.fd-pipeline { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px; }
.fd-pipeline__node { background: var(--fd-surface-container-low); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 6px; }
.fd-pipeline__node--solid { background: var(--fd-primary); color: var(--fd-on-primary); }
.fd-pipeline__node--solid .fd-pipeline__step,
.fd-pipeline__node--solid .fd-pipeline__sub { color: var(--fd-primary-fixed); }
.fd-pipeline__node--solid .fd-pipeline__tag { color: var(--fd-primary-fixed); font-weight: 800; }
.fd-pipeline__node--solid .fd-pipeline__step .material-symbols-outlined { color: var(--fd-primary-fixed); font-size: 15px; }
.fd-pipeline__step { font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--fd-on-surface-variant); display: flex; align-items: center; gap: 4px; justify-content: space-between; }
.fd-pipeline__value { font-size: 15px; font-weight: 800; color: var(--fd-on-surface); }
.fd-pipeline__sub { font-size: 10px; color: var(--fd-on-surface-variant); margin: 0; font-family: var(--dp-font-mono); }
.fd-pipeline__tag { font-size: 10px; font-weight: 700; color: var(--fd-primary); }
.fd-pipeline__node--secondary .fd-pipeline__tag { color: var(--fd-secondary); }
.fd-pipeline__node--tertiary .fd-pipeline__tag { color: var(--fd-tertiary); }

/* Two-column grid */
.fd-grid-columns { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 20px; align-items: start; }
.fd-grid-main { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.fd-grid-aside { display: flex; flex-direction: column; gap: 20px; }

/* Harvest table */
.fd-table-sub { font-size: 11px; color: var(--fd-on-surface-variant); font-weight: 400; margin-top: 2px; }
.fd-el-table { width: 100%; font-size: var(--dp-content-font-size); --el-table-border-color: transparent; --el-table-header-bg-color: var(--fd-surface-container-low); --el-table-header-text-color: var(--fd-on-surface-variant); --el-table-row-hover-bg-color: var(--fd-surface-container-low); --el-table-text-color: var(--fd-on-surface); }
.fd-el-table :deep(.el-table__header th.el-table__cell) { padding: 9px 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; }
.fd-el-table :deep(.el-table__body td.el-table__cell) { padding: 12px; border-bottom: 1px solid var(--fd-surface-container-low); }
.fd-el-table :deep(.el-table__row:last-child td.el-table__cell) { border-bottom: none; }
.fd-progress-track { width: 50px; height: 6px; border-radius: 999px; background: var(--fd-surface-container-high); overflow: hidden; flex-shrink: 0; }
.fd-progress-fill { height: 100%; background: var(--fd-primary); border-radius: 999px; }
.fd-card__foot--center { justify-content: center; }

/* Farm cards */
.fd-toggle-group { display: flex; align-items: center; gap: 2px; background: var(--fd-surface-container-low); padding: 3px; border-radius: 8px; }
.fd-toggle { border: none; background: none; cursor: pointer; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; color: var(--fd-on-surface-variant); }
.fd-toggle--active { background: var(--fd-surface-container-lowest); color: var(--fd-on-surface); }
.fd-farm-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
.fd-farm-card { background: var(--fd-surface-container-low); border-radius: 12px; overflow: hidden; }
.fd-farm-card__image { height: 100px; padding: 10px; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-start; }
.fd-farm-card__name { background: var(--fd-primary); color: var(--fd-on-primary); font-size: 10px; font-weight: 800; padding: 3px 7px; border-radius: 5px; }
.fd-farm-card__alt { align-self: flex-end; background: rgba(255,255,255,.9); color: var(--fd-on-surface); font-size: 10px; font-weight: 800; font-family: var(--dp-font-mono); padding: 2px 6px; border-radius: 5px; }
.fd-farm-card__body { padding: 12px; display: flex; flex-direction: column; gap: 7px; }
.fd-farm-card__row { display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; }
.fd-farm-card__row span { color: var(--fd-on-surface-variant); font-weight: 500; }
.fd-farm-card__row strong { font-weight: 700; color: var(--fd-on-surface); }
.fd-farm-card__foot { display: flex; align-items: center; justify-content: space-between; padding-top: 6px; }

/* Inputs banner */
.fd-need-banner { margin-top: 12px; padding: 12px; border-radius: 12px; background: var(--fd-surface-container-low); display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.fd-need-banner__text { display: flex; align-items: center; gap: 10px; font-size: 12px; }

/* Receipts */
.fd-receipt-list { display: flex; flex-direction: column; gap: 10px; }
.fd-receipt { background: var(--fd-surface-container-low); border-radius: 12px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.fd-receipt__left { display: flex; align-items: flex-start; gap: 10px; }
.fd-receipt__icon { width: 34px; height: 34px; border-radius: 8px; background: var(--fd-surface-container-highest); color: var(--fd-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fd-receipt__head { display: flex; align-items: center; gap: 8px; }
.fd-receipt__meta { display: flex; flex-wrap: wrap; gap: 4px 16px; font-size: 11px; color: var(--fd-on-surface-variant); margin-top: 4px; }

/* Tasks */
.fd-task-list { display: flex; flex-direction: column; gap: 8px; }
.fd-task { display: flex; align-items: flex-start; gap: 10px; padding: 10px; border-radius: 10px; background: var(--fd-surface-container-low); cursor: pointer; }
.fd-task--done { opacity: .55; }
.fd-task input { margin-top: 3px; width: 15px; height: 15px; accent-color: var(--fd-primary); }
.fd-task__title { display: block; font-size: 12px; font-weight: 700; color: var(--fd-on-surface); }
.fd-task--done .fd-task__title { text-decoration: line-through; }
.fd-task__note { display: block; font-size: 11px; color: var(--fd-on-surface-variant); margin-top: 2px; }
.fd-task__foot { display: flex; align-items: center; gap: 8px; margin-top: 5px; }
.fd-priority { font-size: 9px; font-weight: 800; padding: 1px 6px; border-radius: 4px; background: var(--fd-surface-container-highest); color: var(--fd-on-surface); }
.fd-priority--high { background: var(--fd-error-container); color: var(--fd-on-error-container); }

/* Health telemetry */
.fd-metric-list { display: flex; flex-direction: column; gap: 6px; }
.fd-metric-row { display: flex; align-items: center; justify-content: space-between; background: var(--fd-surface-container-low); border-radius: 10px; padding: 9px 11px; font-size: 12px; }
.fd-metric-row__left { display: flex; align-items: center; gap: 8px; color: var(--fd-on-surface); font-weight: 500; }
.fd-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--fd-outline-variant); flex-shrink: 0; }
.fd-dot--primary { background: var(--fd-primary); }
.fd-dot--secondary { background: var(--fd-secondary); }

/* Market */
.fd-market-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
.fd-market-ref { background: var(--fd-surface-container-low); border-radius: 10px; padding: 10px; }
.fd-market-ref__value { font-size: 14px; font-weight: 800; color: var(--fd-on-surface); margin-top: 3px; }
.fd-market-ref__change { display: block; font-size: 10px; font-weight: 700; margin-top: 2px; }
.fd-inventory-banner { background: var(--fd-surface-container-low); border-radius: 10px; padding: 12px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 3px; }
.fd-inventory-banner__row { display: flex; align-items: center; justify-content: space-between; font-size: 12px; }
.fd-rfq-list { display: flex; flex-direction: column; gap: 8px; }
.fd-rfq { background: var(--fd-surface-container-low); border-radius: 10px; padding: 10px; display: flex; flex-direction: column; gap: 4px; }
.fd-rfq__row { display: flex; align-items: center; justify-content: space-between; font-size: 12px; }
.fd-rfq .fd-muted-text { font-size: 11px; }

/* Copilot */
.fd-copilot-head { display: flex; align-items: center; gap: 8px; padding-bottom: 12px; }
.fd-prompt-list { display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px; }
.fd-prompt { display: flex; align-items: center; justify-content: space-between; gap: 8px; border: none; cursor: pointer; background: var(--fd-surface-container-low); padding: 9px 10px; border-radius: 10px; font-size: 11.5px; color: var(--fd-on-surface); text-align: left; }
.fd-prompt:hover { background: var(--fd-surface-container-high); }
.fd-prompt .material-symbols-outlined { font-size: 15px; color: var(--fd-on-surface-variant); flex-shrink: 0; }
.fd-copilot-response { background: var(--fd-surface-container-low); border-radius: 10px; padding: 11px; font-size: 11.5px; line-height: 1.6; color: var(--fd-on-surface-variant); margin-bottom: 10px; }
.fd-ai-input { position: relative; display: flex; align-items: center; }
.fd-ai-input input { width: 100%; background: var(--fd-surface-container-low); border: none; border-radius: 10px; padding: 9px 36px 9px 12px; font-size: var(--dp-content-font-size); color: var(--fd-on-surface); outline: none; }
.fd-ai-send { position: absolute; right: 8px; border: none; background: none; cursor: pointer; color: var(--fd-primary); display: flex; }

/* EUDR ledger */
.fd-eudr-box { background: var(--fd-surface-container-low); border-radius: 10px; padding: 11px; margin-bottom: 10px; }
.fd-eudr-box__row { display: flex; align-items: center; justify-content: space-between; font-size: 12px; margin-bottom: 3px; }
.fd-chip-row { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.fd-chip { background: var(--fd-surface-container-low); color: var(--fd-on-surface); font-size: 10.5px; font-weight: 600; padding: 5px 9px; border-radius: 6px; }
.fd-completeness { margin-bottom: 10px; }
.fd-completeness__row { display: flex; align-items: center; justify-content: space-between; font-size: 12px; margin-bottom: 5px; }
.fd-completeness .fd-progress-track { width: 100%; }

@media (max-width: 1200px) {
    .fd-grid-columns { grid-template-columns: 1fr; }
    .fd-weather-grid { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
    .fd-forecast-row { grid-template-columns: repeat(4, 1fr); }
}
</style>
