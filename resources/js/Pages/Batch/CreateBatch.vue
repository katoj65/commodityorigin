<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    Box, Calendar, ChatDotRound, Check,
    CircleCheck, Collection, CollectionTag, DataLine,
    Location, Opportunity, Promotion, Star, Tickets, TrendCharts,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    season:   { type: Object, required: true },
    harvests: { type: Array,  default: () => [] },
});

/* ── Options ─────────────────────────────────────────────────── */
const processingMethods  = ['Washed', 'Natural', 'Honey', 'Anaerobic', 'Semi-washed'];
const fermentationMethods = ['None', 'Standard', 'Anaerobic', 'Carbonic Maceration', 'Extended'];
const dryingMethods      = ['Raised beds', 'Sun-dried', 'Mechanical dryer', 'Greenhouse'];
const millingStatuses    = ['Pending', 'In milling', 'Milled', 'Ready for grading'];
const batchTypes         = ['Processing', 'Export', 'Experimental'];
const coffeeTypes        = ['Arabica', 'Robusta', 'Mixed'];
const storageTypes       = ['Ambient', 'Climate Controlled', 'Cold Storage', 'Vacuum Sealed'];
const packagingTypes     = ['Jute Bags', 'GrainPro', 'Poly Bags', 'Vacuum Packs', 'Boxes'];
const beanGrades         = ['AA', 'AB', 'C', 'PB (Peaberry)', 'E (Elephant)', 'TT'];
const inspectionStatuses = ['Pending', 'In Progress', 'Passed', 'Failed'];
const certifications     = ['None', 'Organic', 'Fair Trade', 'Rainforest Alliance', 'UTZ', 'Direct Trade'];
const flavorNoteOptions  = ['Chocolate', 'Fruity', 'Citrus', 'Nutty', 'Floral', 'Caramel'];

/* ── Local state ─────────────────────────────────────────────── */
const selectedHarvestIds  = ref(props.harvests.map(h => h.id));
const chatOpen            = ref(false);
const chatInput           = ref('');
const blockchainEnabled   = ref(false);
const selectedFlavorNotes = ref([]);

/* ── Form ────────────────────────────────────────────────────── */
const form = useForm({
    batch_number:       '',
    variety:            '',
    warehouse_location: '',
    quantity_bags:      '',
    net_weight_kg:      '',
    price:              '',
    moisture_content:   '',
    processing_date:    '',
    processing_method:  '',
    drying_method:      '',
    drying_duration:    '',
    milling_status:     '',
    screen_size:        '',
    defect_count:       '',
    cup_score:          '',
    notes:              '',
    /* extra optional fields */
    batch_type:         '',
    coffee_type:        '',
    cooperative:        '',
    fermentation_method:'',
    storage_type:       '',
    packaging_type:     '',
    bag_weight_kg:      '',
    inspection_status:  '',
    certification:      '',
});

/* ── Helpers ─────────────────────────────────────────────────── */
const formatDate = v =>
    v ? new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(v)) : 'Pending';

const deriveMoisture = h =>
    Math.max(10.4, Math.min(12.6, 12.9 - (Number(h.ripeness_percentage || 94) * 0.018)));

const harvestRows = computed(() =>
    props.harvests.map((h, i) => ({
        id:       h.id,
        code:     h.harvest_number || `HV-${String(i + 1).padStart(3, '0')}`,
        date:     formatDate(h.harvest_date),
        farm:     h.farm?.name || 'Source Farm',
        qty:      Number(h.weight || 0),
        score:    h.cup_score ? Number(h.cup_score).toFixed(1) : '87.2',
        moisture: deriveMoisture(h),
        status:   h.status || 'Verified',
    }))
);

const toggleHarvest = id => {
    selectedHarvestIds.value = selectedHarvestIds.value.includes(id)
        ? selectedHarvestIds.value.filter(x => x !== id)
        : [...selectedHarvestIds.value, id];
};

const toggleFlavor = note => {
    selectedFlavorNotes.value = selectedFlavorNotes.value.includes(note)
        ? selectedFlavorNotes.value.filter(n => n !== note)
        : [...selectedFlavorNotes.value, note];
};

/* ── Computed summary ────────────────────────────────────────── */
const selectedHarvests  = computed(() => harvestRows.value.filter(h => selectedHarvestIds.value.includes(h.id)));
const selectedCount     = computed(() => selectedHarvests.value.length);
const totalInputWeight  = computed(() => selectedHarvests.value.reduce((s, h) => s + h.qty, 0));
const avgMoisture       = computed(() => selectedHarvests.value.length ? selectedHarvests.value.reduce((s, h) => s + h.moisture, 0) / selectedHarvests.value.length : 0);
const avgQualityScore   = computed(() => selectedHarvests.value.length ? selectedHarvests.value.reduce((s, h) => s + Number(h.score), 0) / selectedHarvests.value.length : 0);
const estimatedYield    = computed(() => totalInputWeight.value * 0.8611);
const yieldEfficiency   = computed(() => '86.1');
const bagWeight         = computed(() => Number(form.bag_weight_kg || 60));
const estimatedBags     = computed(() => bagWeight.value > 0 ? Math.ceil(estimatedYield.value / bagWeight.value) : 0);
const batchWeight       = computed(() => Number(form.net_weight_kg) || Math.round(estimatedYield.value));

const exportReadiness = computed(() => {
    let s = 60;
    if (selectedCount.value > 0) s += 10;
    if (form.processing_method) s += 10;
    if (form.cup_score && Number(form.cup_score) >= 80) s += 10;
    if (form.moisture_content && Number(form.moisture_content) <= 12) s += 5;
    if (form.inspection_status === 'Passed') s += 5;
    return Math.min(s, 100);
});

const formCompletion = computed(() => {
    const fields = [form.batch_number, form.variety, form.processing_method, form.drying_method, form.net_weight_kg, form.cup_score, selectedCount.value > 0 ? 'ok' : ''];
    return Math.round(fields.filter(Boolean).length / fields.length * 100);
});

const pipelineStages = computed(() => [
    { label: 'Season',  count: 1,                                                sub: 'Active',   verify: 'Linked',        done: true,  current: false },
    { label: 'Harvest', count: props.season.harvests_count || harvestRows.value.length, sub: 'Verified', verify: 'Quality Graded', done: true,  current: false },
    { label: 'Batch',   count: selectedCount.value,                              sub: 'Forming',  verify: 'In Progress',   done: false, current: true  },
    { label: 'Lot',     count: 0,                                                sub: 'Pending',  verify: 'Awaiting',      done: false, current: false },
]);

const aiInsights = computed(() => [
    selectedCount.value > 1
        ? 'Multiple harvests selected — blend consistency looks strong for specialty export.'
        : 'Select more harvests to improve batch quality and consistency.',
    form.processing_method === 'Washed'
        ? 'Washed process selected — optimal clean cup profile for EU & specialty buyers.'
        : form.processing_method
            ? `${form.processing_method} process selected — verify moisture targets for this method.`
            : 'Processing method not set — required for export certification.',
    avgQualityScore.value >= 86
        ? 'Quality scores indicate specialty-grade potential. Recommend premium lot creation.'
        : 'Consider selecting higher-scoring harvests for improved export positioning.',
]);

const seasonBackUrl = computed(() => route('season.show', props.season.id));

const chatPrompts = ['How do I create a good batch?', 'Which harvests should I select?', 'Is this batch export ready?', 'What processing method is best?'];

const fillPrompt = p => { chatInput.value = p; };

/* ── Submit ──────────────────────────────────────────────────── */
const submit = () => {
    form.transform(data => ({ ...data, season_id: props.season.id, harvest_ids: selectedHarvestIds.value }));
    form.post(route('batch.store'), {
        preserveScroll: true,
        onFinish: () => form.transform(data => data),
    });
};
</script>

<template>
    <AppLayout title="Create Batch" full-width flush :show-banner="false">

        <div class="cb-page">

            <!-- Header ────────────────────────────────────────────── -->
            <div class="cb-header">
                <div class="cb-header-left">
                    <div class="cb-kicker">Batch Creation · Bean Origin</div>
                    <h1 class="cb-title mt-2 mb-2">Create Batch</h1>
                    <p class="cb-subtitle mb-2">Convert harvests into traceable coffee batches for processing and export</p>
                    <div class="cb-badge-row">
                        <span class="cb-badge cb-badge--green">Traceable</span>
                        <span class="cb-badge cb-badge--blue">Processing Stage</span>
                        <span class="cb-badge cb-badge--amber">Lot Ready Pipeline</span>
                        <span class="cb-badge cb-badge--purple">Harvest Linked</span>
                    </div>
                </div>
                <div class="cb-header-actions">
                    <Link :href="seasonBackUrl" class="cb-btn cb-btn--outline">
                        <el-icon><Collection /></el-icon> View Batches
                    </Link>
                    <button type="button" class="cb-btn cb-btn--outline">
                        <el-icon><CollectionTag /></el-icon> Link Harvests
                    </button>
                    <button type="button" class="cb-btn cb-btn--ghost">
                        <el-icon><ChatDotRound /></el-icon> Ask Advisor
                    </button>
                </div>
            </div>

            <!-- ② Context Panel ─────────────────────────────────────── -->
            <div class="cb-context">
                <div class="cb-ctx-block">
                    <div class="cb-ctx-label">Selected Season</div>
                    <div class="cb-ctx-val">{{ season.name }}</div>
                    <div class="cb-ctx-sub">ID: {{ season.id }} · {{ season.region || 'Uganda' }}</div>
                </div>
                <div class="cb-ctx-block">
                    <div class="cb-ctx-label">Available Harvests</div>
                    <div class="cb-ctx-val">{{ season.harvests_count ?? harvests.length }}</div>
                    <div class="cb-ctx-sub">Total in this season</div>
                </div>
                <div class="cb-ctx-block">
                    <div class="cb-ctx-label">Selected Harvests</div>
                    <div class="cb-ctx-val cb-ctx-val--green">{{ selectedCount }}</div>
                    <div class="cb-ctx-sub">{{ Number(season.harvests_sum_weight ?? totalInputWeight).toLocaleString() }} kg total</div>
                </div>
                <div class="cb-ctx-block">
                    <div class="cb-ctx-label">Estimated Yield</div>
                    <div class="cb-ctx-val">{{ Math.round(estimatedYield).toLocaleString() }} kg</div>
                    <div class="cb-ctx-sub">After processing loss</div>
                </div>
                <div class="cb-ctx-actions">
                    <button type="button" class="cb-btn cb-btn--primary">
                        <el-icon><CollectionTag /></el-icon> Select Harvests
                    </button>
                    <Link :href="seasonBackUrl" class="cb-btn cb-btn--outline">View Season</Link>
                </div>
            </div>

            <!-- ③ Main form + summary rail ──────────────────────────── -->
            <form class="cb-body" @submit.prevent="submit">

                <!-- Left: form sections -->
                <div class="cb-main">

                    <!-- A. Basic Batch Information ─────────────────── -->
                    <div class="cb-card">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><Tickets /></el-icon> Basic Batch Information</div>
                            <div class="d-flex gap-1">
                                <span class="cb-badge cb-badge--blue" style="font-size:9px;">Section A</span>
                            </div>
                        </div>
                        <div class="cb-card-body">
                            <div class="cb-form-grid">
                                <div class="cb-field cb-field--full">
                                    <label>Batch Name / Number</label>
                                    <el-input v-model="form.batch_number" placeholder="e.g. BATCH-2026-001" />
                                    <InputError :message="form.errors.batch_number" class="cb-error" />
                                </div>
                                <div class="cb-field">
                                    <label>Batch Type</label>
                                    <el-select v-model="form.batch_type" clearable placeholder="Select type" class="!w-full">
                                        <el-option v-for="t in batchTypes" :key="t" :label="t" :value="t" />
                                    </el-select>
                                </div>
                                <div class="cb-field">
                                    <label>Coffee Type <span class="cb-req">*</span></label>
                                    <el-select v-model="form.coffee_type" clearable placeholder="Select coffee type" class="!w-full">
                                        <el-option v-for="t in coffeeTypes" :key="t" :label="t" :value="t" />
                                    </el-select>
                                </div>
                                <div class="cb-field">
                                    <label>Variety <span class="cb-req">*</span></label>
                                    <el-input v-model="form.variety" placeholder="e.g. Bourbon, SL-28, Geisha" />
                                    <InputError :message="form.errors.variety" class="cb-error" />
                                </div>
                                <div class="cb-field">
                                    <label>Origin Region</label>
                                    <el-input :model-value="season.region || 'Central Highlands'" readonly placeholder="Auto from harvests" />
                                </div>
                                <div class="cb-field cb-field--full">
                                    <label>Cooperative / Farmer Group</label>
                                    <el-input v-model="form.cooperative" placeholder="e.g. Sipi Falls Cooperative" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- B. Harvest Linking ─────────────────────────── -->
                    <div class="cb-card">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><CollectionTag /></el-icon> Harvest Linking</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="cb-muted" style="font-size:11px;">{{ selectedCount }} / {{ harvestRows.length }} selected</span>
                                <span class="cb-badge cb-badge--green" style="font-size:9px;">Section B</span>
                            </div>
                        </div>
                        <div class="cb-table-wrap">
                            <table class="cb-table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Harvest ID</th>
                                        <th>Farm</th>
                                        <th>Date</th>
                                        <th class="text-end">Qty (kg)</th>
                                        <th class="text-end">Score</th>
                                        <th class="text-end">Moisture</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="h in harvestRows"
                                        :key="h.id"
                                        :class="{ 'cb-tr--selected': selectedHarvestIds.includes(h.id) }"
                                        @click="toggleHarvest(h.id)"
                                    >
                                        <td style="width:36px;">
                                            <span class="cb-checkbox" :class="{ 'cb-checkbox--on': selectedHarvestIds.includes(h.id) }">
                                                <el-icon v-if="selectedHarvestIds.includes(h.id)"><Check /></el-icon>
                                            </span>
                                        </td>
                                        <td><span class="cb-code">{{ h.code }}</span></td>
                                        <td><span style="font-size:12px; font-weight:600;">{{ h.farm }}</span></td>
                                        <td><span class="cb-muted">{{ h.date }}</span></td>
                                        <td class="text-end"><strong style="font-size:12px;">{{ h.qty.toLocaleString() }}</strong></td>
                                        <td class="text-end"><span class="cb-score">{{ h.score }}</span></td>
                                        <td class="text-end"><span class="cb-muted">{{ h.moisture.toFixed(1) }}%</span></td>
                                        <td>
                                            <span class="cb-pill" :class="h.status === 'Verified' ? 'cb-pill--green' : 'cb-pill--blue'">{{ h.status }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!harvestRows.length">
                                        <td colspan="8" class="text-center cb-muted" style="padding:20px;">No harvests available in this season.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="cb-table-foot">
                            <div class="cb-summary-strip">
                                <div class="cb-strip-item">
                                    <span>Selected</span>
                                    <strong>{{ selectedCount }} Harvests</strong>
                                </div>
                                <div class="cb-strip-item">
                                    <span>Total Input</span>
                                    <strong>{{ totalInputWeight.toLocaleString() }} kg</strong>
                                </div>
                                <div class="cb-strip-item">
                                    <span>Est. Yield</span>
                                    <strong>{{ Math.round(estimatedYield).toLocaleString() }} kg</strong>
                                </div>
                                <div class="cb-strip-item">
                                    <span>Avg Quality</span>
                                    <strong>{{ avgQualityScore.toFixed(1) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- C & D row: Processing + Quality ───────────────── -->
                    <div class="cb-two-col">

                        <!-- C. Processing Details -->
                        <div class="cb-card">
                            <div class="cb-card-head">
                                <div class="cb-card-title"><el-icon><TrendCharts /></el-icon> Processing Details</div>
                                <div class="d-flex gap-1">
                                    <span class="cb-badge cb-badge--green" style="font-size:9px;">Specialty</span>
                                    <span class="cb-badge cb-badge--amber" style="font-size:9px;">Section C</span>
                                </div>
                            </div>
                            <div class="cb-card-body">
                                <div class="cb-form-grid">
                                    <div class="cb-field cb-field--full">
                                        <label>Processing Date <span class="cb-req">*</span></label>
                                        <el-date-picker
                                            v-model="form.processing_date"
                                            type="date"
                                            value-format="YYYY-MM-DD"
                                            placeholder="Select date"
                                            class="!w-full"
                                        />
                                        <InputError :message="form.errors.processing_date" class="cb-error" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Processing Method <span class="cb-req">*</span></label>
                                        <el-select v-model="form.processing_method" clearable filterable placeholder="Select" class="!w-full">
                                            <el-option v-for="m in processingMethods" :key="m" :label="m" :value="m" />
                                        </el-select>
                                        <InputError :message="form.errors.processing_method" class="cb-error" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Fermentation Method</label>
                                        <el-select v-model="form.fermentation_method" clearable placeholder="Select" class="!w-full">
                                            <el-option v-for="m in fermentationMethods" :key="m" :label="m" :value="m" />
                                        </el-select>
                                    </div>
                                    <div class="cb-field">
                                        <label>Drying Method <span class="cb-req">*</span></label>
                                        <el-select v-model="form.drying_method" clearable filterable placeholder="Select" class="!w-full">
                                            <el-option v-for="m in dryingMethods" :key="m" :label="m" :value="m" />
                                        </el-select>
                                        <InputError :message="form.errors.drying_method" class="cb-error" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Drying Duration (days)</label>
                                        <el-input v-model="form.drying_duration" type="number" min="0" placeholder="e.g. 14" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Milling Type</label>
                                        <el-select v-model="form.milling_status" clearable placeholder="Select" class="!w-full">
                                            <el-option v-for="s in millingStatuses" :key="s" :label="s" :value="s" />
                                        </el-select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- D. Quality Attributes -->
                        <div class="cb-card">
                            <div class="cb-card-head">
                                <div class="cb-card-title"><el-icon><Star /></el-icon> Quality Attributes</div>
                                <span class="cb-badge cb-badge--blue" style="font-size:9px;">Section D</span>
                            </div>
                            <div class="cb-card-body">
                                <div class="cb-form-grid">
                                    <div class="cb-field">
                                        <label>Expected Cupping Score</label>
                                        <el-input v-model="form.cup_score" type="number" min="0" max="100" step="0.01" placeholder="e.g. 86.75" />
                                        <InputError :message="form.errors.cup_score" class="cb-error" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Moisture Level (%)</label>
                                        <el-input v-model="form.moisture_content" type="number" min="0" max="100" step="0.01" :placeholder="avgMoisture > 0 ? avgMoisture.toFixed(1) : '11.2'" />
                                        <InputError :message="form.errors.moisture_content" class="cb-error" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Defect Count</label>
                                        <el-input v-model="form.defect_count" type="number" min="0" placeholder="e.g. 8" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Bean Size Grade</label>
                                        <el-select v-model="form.screen_size" clearable placeholder="Select grade" class="!w-full">
                                            <el-option v-for="g in beanGrades" :key="g" :label="g" :value="g" />
                                        </el-select>
                                    </div>
                                </div>
                                <!-- Flavor notes -->
                                <div style="margin-top:14px;">
                                    <div class="cb-field-label">Optional Flavor Notes</div>
                                    <div class="cb-flavor-grid">
                                        <button
                                            v-for="note in flavorNoteOptions"
                                            :key="note"
                                            type="button"
                                            class="cb-flavor-chip"
                                            :class="{ 'cb-flavor-chip--on': selectedFlavorNotes.includes(note) }"
                                            @click="toggleFlavor(note)"
                                        >{{ note }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /cb-two-col -->

                    <!-- E & F row: Production Output + Storage ─────────── -->
                    <div class="cb-two-col">

                        <!-- E. Production Output -->
                        <div class="cb-card">
                            <div class="cb-card-head">
                                <div class="cb-card-title"><el-icon><Box /></el-icon> Production Output</div>
                                <span class="cb-badge cb-badge--green" style="font-size:9px;">Section E</span>
                            </div>
                            <div class="cb-card-body">
                                <div class="cb-form-grid">
                                    <div class="cb-field">
                                        <label>Estimated Batch Weight (kg)</label>
                                        <el-input v-model="form.net_weight_kg" type="number" min="1" step="0.01" :placeholder="String(Math.round(estimatedYield) || 0)" />
                                        <InputError :message="form.errors.net_weight_kg" class="cb-error" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Bag Weight (kg)</label>
                                        <el-input v-model="form.bag_weight_kg" type="number" min="1" placeholder="e.g. 60" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Number of Bags</label>
                                        <el-input v-model="form.quantity_bags" type="number" min="1" :placeholder="String(estimatedBags || 0)" />
                                        <InputError :message="form.errors.quantity_bags" class="cb-error" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Yield Efficiency</label>
                                        <el-input :model-value="yieldEfficiency + '%'" readonly />
                                    </div>
                                    <div class="cb-field cb-field--full">
                                        <label>Price (per kg)</label>
                                        <el-input v-model="form.price" type="number" min="0" step="0.01" placeholder="e.g. 4.80" />
                                        <InputError :message="form.errors.price" class="cb-error" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- F. Storage & Handling -->
                        <div class="cb-card">
                            <div class="cb-card-head">
                                <div class="cb-card-title"><el-icon><Location /></el-icon> Storage &amp; Handling</div>
                                <div class="d-flex gap-1">
                                    <span class="cb-badge cb-badge--green" style="font-size:9px;">Proper Storage</span>
                                    <span class="cb-badge cb-badge--blue" style="font-size:9px;">Section F</span>
                                </div>
                            </div>
                            <div class="cb-card-body">
                                <div class="cb-form-grid">
                                    <div class="cb-field cb-field--full">
                                        <label>Storage Location</label>
                                        <el-input v-model="form.warehouse_location" placeholder="e.g. Warehouse B — Silo 12" />
                                        <InputError :message="form.errors.warehouse_location" class="cb-error" />
                                    </div>
                                    <div class="cb-field">
                                        <label>Storage Type</label>
                                        <el-select v-model="form.storage_type" clearable placeholder="Select" class="!w-full">
                                            <el-option v-for="t in storageTypes" :key="t" :label="t" :value="t" />
                                        </el-select>
                                    </div>
                                    <div class="cb-field">
                                        <label>Packaging Type</label>
                                        <el-select v-model="form.packaging_type" clearable placeholder="Select" class="!w-full">
                                            <el-option v-for="t in packagingTypes" :key="t" :label="t" :value="t" />
                                        </el-select>
                                    </div>
                                    <div class="cb-field cb-field--full">
                                        <label>Handling Notes</label>
                                        <el-input v-model="form.notes" type="textarea" :rows="3" placeholder="Handling, quality, or storage observations…" />
                                        <InputError :message="form.errors.notes" class="cb-error" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- G. Compliance & Traceability ──────────────────── -->
                    <div class="cb-card">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><CircleCheck /></el-icon> Compliance &amp; Traceability</div>
                            <div class="d-flex gap-1">
                                <span class="cb-badge cb-badge--green" style="font-size:9px;">Verified</span>
                                <span class="cb-badge cb-badge--purple" style="font-size:9px;">Export Ready</span>
                                <span class="cb-badge cb-badge--blue" style="font-size:9px;">Section G</span>
                            </div>
                        </div>
                        <div class="cb-card-body">
                            <div class="cb-form-grid">
                                <div class="cb-field">
                                    <label>Quality Inspection Status</label>
                                    <el-select v-model="form.inspection_status" clearable placeholder="Select" class="!w-full">
                                        <el-option v-for="s in inspectionStatuses" :key="s" :label="s" :value="s" />
                                    </el-select>
                                </div>
                                <div class="cb-field">
                                    <label>Certification Readiness</label>
                                    <el-select v-model="form.certification" clearable placeholder="Select" class="!w-full">
                                        <el-option v-for="c in certifications" :key="c" :label="c" :value="c" />
                                    </el-select>
                                </div>
                                <div class="cb-field">
                                    <label>Traceability ID</label>
                                    <el-input :model-value="form.batch_number ? `TRC-${form.batch_number}` : 'Auto-generated on save'" readonly />
                                </div>
                                <div class="cb-field">
                                    <label>Blockchain Readiness</label>
                                    <div class="cb-toggle-row">
                                        <span class="cb-muted" style="font-size:12px;">{{ blockchainEnabled ? 'Enabled — Immutable record' : 'Disabled' }}</span>
                                        <button type="button" class="cb-toggle" :class="{ 'cb-toggle--on': blockchainEnabled }" @click="blockchainEnabled = !blockchainEnabled">
                                            <i></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions ────────────────────────────────────────── -->
                    <div class="cb-form-actions">
                        <SubmitButton native-type="submit" :loading="form.processing" :disabled="form.processing" style="width:150px;">
                            Create Batch
                        </SubmitButton>
                        <button type="button" class="cb-btn cb-btn--outline">Save Draft</button>
                        <button type="button" class="cb-btn cb-btn--ghost" @click="form.reset()">Reset Form</button>
                        <Link :href="seasonBackUrl" class="cb-btn cb-btn--ghost">Link to Lot</Link>
                    </div>

                    <!-- Traceability Preview ───────────────────────────── -->
                    <div class="cb-card">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><Promotion /></el-icon> Traceability Pipeline</div>
                            <span class="cb-badge cb-badge--amber">Current: Batch Formation</span>
                        </div>
                        <div class="cb-card-body--pipe">
                            <div class="cb-pipe">
                                <div v-for="(stage, idx) in pipelineStages" :key="stage.label" class="cb-pipe-item">
                                    <div class="cb-pipe-item__top">
                                        <div class="cb-pipe-item__count" :class="{ 'cb-pipe-item__count--current': stage.current }">{{ stage.count }}</div>
                                        <div class="cb-pipe-item__sub">{{ stage.sub }}</div>
                                    </div>
                                    <div class="cb-pipe-item__mid">
                                        <div class="cb-pipe-item__seg" :class="{ 'cb-pipe-item__seg--done': idx > 0 && pipelineStages[idx-1].done, 'cb-pipe-item__seg--hidden': idx === 0 }" />
                                        <div class="cb-pipe-item__dot" :class="{ 'cb-pipe-item__dot--done': stage.done, 'cb-pipe-item__dot--current': stage.current }">
                                            <el-icon v-if="stage.done" style="font-size:12px;"><Check /></el-icon>
                                        </div>
                                        <div class="cb-pipe-item__seg" :class="{ 'cb-pipe-item__seg--done': stage.done, 'cb-pipe-item__seg--hidden': idx === pipelineStages.length - 1 }" />
                                    </div>
                                    <div class="cb-pipe-item__bot">
                                        <div class="cb-pipe-item__label" :class="{ 'cb-pipe-item__label--current': stage.current }">{{ stage.label }}</div>
                                        <div class="cb-pipe-item__verify" :class="{ 'cb-pipe-item__verify--done': stage.done, 'cb-pipe-item__verify--current': stage.current }">{{ stage.verify }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Batch Insights ──────────────────────────────── -->
                    <div class="cb-card">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><Opportunity /></el-icon> AI Batch Insights</div>
                            <span class="cb-muted" style="font-size:10px;">Powered by Bean Origin AI</span>
                        </div>
                        <div class="cb-card-body">
                            <div class="cb-insights-grid">
                                <div v-for="(insight, i) in aiInsights" :key="i" class="cb-insight-card" :class="i === 0 ? 'cb-insight-card--green' : i === 1 ? 'cb-insight-card--blue' : 'cb-insight-card--amber'">
                                    <el-icon class="cb-insight-icon"><Opportunity /></el-icon>
                                    <p class="cb-insight-text">{{ insight }}</p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div><!-- /cb-main -->

                <!-- Right rail: Live Summary ─────────────────────────── -->
                <aside class="cb-rail">

                    <!-- Live Summary Panel -->
                    <div class="cb-card cb-card--sticky">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><DataLine /></el-icon> Batch Summary</div>
                            <span class="cb-completion-pill">{{ formCompletion }}% Complete</span>
                        </div>
                        <div class="cb-card-body">
                            <!-- Progress bar -->
                            <div class="cb-progress-label">Form completion</div>
                            <div class="cb-progress-track mb-3"><div class="cb-progress-fill" :style="{ width: formCompletion + '%' }" /></div>

                            <!-- Key metrics -->
                            <div class="cb-summary-grid">
                                <div class="cb-summary-stat">
                                    <div class="cb-summary-val">{{ selectedCount }}</div>
                                    <div class="cb-muted">Harvests</div>
                                </div>
                                <div class="cb-summary-stat">
                                    <div class="cb-summary-val">{{ Math.round(estimatedYield).toLocaleString() }}</div>
                                    <div class="cb-muted">Est. kg</div>
                                </div>
                                <div class="cb-summary-stat">
                                    <div class="cb-summary-val cb-summary-val--green">{{ avgQualityScore > 0 ? avgQualityScore.toFixed(1) : '—' }}</div>
                                    <div class="cb-muted">Avg Score</div>
                                </div>
                                <div class="cb-summary-stat">
                                    <div class="cb-summary-val">{{ exportReadiness }}%</div>
                                    <div class="cb-muted">Export Ready</div>
                                </div>
                            </div>

                            <!-- Details list -->
                            <div class="cb-summary-list">
                                <div class="cb-summary-row">
                                    <span class="cb-muted">Processing Method</span>
                                    <strong>{{ form.processing_method || '—' }}</strong>
                                </div>
                                <div class="cb-summary-row">
                                    <span class="cb-muted">Drying Method</span>
                                    <strong>{{ form.drying_method || '—' }}</strong>
                                </div>
                                <div class="cb-summary-row">
                                    <span class="cb-muted">Coffee Type</span>
                                    <strong>{{ form.coffee_type || '—' }}</strong>
                                </div>
                                <div class="cb-summary-row">
                                    <span class="cb-muted">Avg Moisture</span>
                                    <strong>{{ avgMoisture > 0 ? avgMoisture.toFixed(1) + '%' : '—' }}</strong>
                                </div>
                                <div class="cb-summary-row">
                                    <span class="cb-muted">Storage Location</span>
                                    <strong>{{ form.warehouse_location || '—' }}</strong>
                                </div>
                                <div class="cb-summary-row">
                                    <span class="cb-muted">Blockchain</span>
                                    <span class="cb-badge" :class="blockchainEnabled ? 'cb-badge--green' : 'cb-badge--gray'" style="font-size:8px;">{{ blockchainEnabled ? 'Enabled' : 'Disabled' }}</span>
                                </div>
                            </div>

                            <!-- Export readiness bar -->
                            <div style="margin-top:12px;">
                                <div class="cb-progress-label">Export Readiness — {{ exportReadiness }}%</div>
                                <div class="cb-progress-track"><div class="cb-progress-fill cb-progress-fill--amber" :style="{ width: exportReadiness + '%' }" /></div>
                            </div>

                            <!-- Flavor notes preview -->
                            <div v-if="selectedFlavorNotes.length" style="margin-top:12px;">
                                <div class="cb-progress-label">Flavor Profile</div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <span v-for="n in selectedFlavorNotes" :key="n" class="cb-badge cb-badge--green" style="font-size:9px;">{{ n }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Season context -->
                    <div class="cb-card">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><Calendar /></el-icon> Season</div>
                            <span class="cb-badge cb-badge--green" style="font-size:9px;">Active</span>
                        </div>
                        <div class="cb-card-body">
                            <div class="cb-bold" style="font-size:13px; color:#003f2c;">{{ season.name }}</div>
                            <div class="cb-muted" style="margin-top:2px; font-size:11px;">{{ season.region || 'Uganda' }}</div>
                            <div class="cb-season-stats">
                                <div class="cb-season-stat">
                                    <div class="cb-bold" style="font-size:16px;">{{ season.harvests_count ?? harvests.length }}</div>
                                    <div class="cb-muted">Harvests</div>
                                </div>
                                <div class="cb-season-stat">
                                    <div class="cb-bold" style="font-size:16px; color:#004532;">{{ selectedCount }}</div>
                                    <div class="cb-muted">Selected</div>
                                </div>
                            </div>
                            <Link :href="seasonBackUrl" class="cb-link-btn">View Season Details</Link>
                        </div>
                    </div>

                    <!-- Quality indicators -->
                    <div class="cb-card">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><Star /></el-icon> Quality Indicators</div>
                            <span class="cb-bold" style="font-size:11px; color:#004532;">{{ avgQualityScore > 0 ? avgQualityScore.toFixed(0) + '/100' : '—' }}</span>
                        </div>
                        <div class="cb-card-body">
                            <div class="cb-qual-row">
                                <span class="cb-muted" style="font-size:11px;">Avg Cup Score</span>
                                <span class="cb-bold" style="font-size:11px;">{{ avgQualityScore > 0 ? avgQualityScore.toFixed(1) : '—' }}</span>
                            </div>
                            <div class="cb-bar-track mt-1 mb-2"><div class="cb-bar-fill" :style="{ width: avgQualityScore > 0 ? avgQualityScore + '%' : '0%' }" /></div>
                            <div class="cb-qual-row">
                                <span class="cb-muted" style="font-size:11px;">Avg Moisture</span>
                                <span class="cb-bold" style="font-size:11px;">{{ avgMoisture > 0 ? avgMoisture.toFixed(1) + '%' : '—' }}</span>
                            </div>
                            <div class="cb-bar-track mt-1 mb-2"><div class="cb-bar-fill cb-bar-fill--blue" :style="{ width: avgMoisture > 0 ? (avgMoisture * 8) + '%' : '0%' }" /></div>
                            <div class="cb-qual-row">
                                <span class="cb-muted" style="font-size:11px;">Yield Efficiency</span>
                                <span class="cb-bold" style="font-size:11px;">{{ yieldEfficiency }}%</span>
                            </div>
                            <div class="cb-bar-track mt-1"><div class="cb-bar-fill" style="width:86.1%;" /></div>
                        </div>
                    </div>

                    <!-- Readiness checklist -->
                    <div class="cb-card">
                        <div class="cb-card-head">
                            <div class="cb-card-title"><el-icon><CircleCheck /></el-icon> Readiness</div>
                        </div>
                        <div class="cb-card-body">
                            <div class="cb-checklist">
                                <div v-for="item in [
                                    { label: 'Season Context Linked',    done: true },
                                    { label: 'Harvest Inputs Selected',  done: selectedCount > 0 },
                                    { label: 'Batch Details Filled',     done: Boolean(form.batch_number && form.variety) },
                                    { label: 'Processing Method Set',    done: Boolean(form.processing_method) },
                                    { label: 'Quality Data Entered',     done: Boolean(form.cup_score) },
                                    { label: 'Storage Location Set',     done: Boolean(form.warehouse_location) },
                                ]" :key="item.label" class="cb-check-item">
                                    <span class="cb-check-dot" :class="item.done ? 'cb-check-dot--done' : ''">
                                        <el-icon v-if="item.done"><Check /></el-icon>
                                    </span>
                                    <span :class="item.done ? 'cb-bold' : 'cb-muted'" style="font-size:11px;">{{ item.label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </aside>

            </form><!-- /cb-body -->

        </div><!-- /cb-page -->

        <!-- ④ Floating Chatbot ──────────────────────────────────────── -->
        <div class="cb-float">
            <div v-if="chatOpen" class="cb-chat-panel">
                <div class="cb-chat-head">
                    <div>
                        <div class="cb-bold" style="font-size:12px;">Bean Origin Batch Advisor</div>
                        <div class="cb-muted" style="font-size:10px;">Batch creation assistant</div>
                    </div>
                    <button class="cb-chat-close" @click="chatOpen = false">×</button>
                </div>
                <div class="cb-chat-prompts">
                    <button v-for="p in chatPrompts" :key="p" class="cb-chat-prompt" type="button" @click="fillPrompt(p)">{{ p }}</button>
                </div>
                <div class="cb-chat-input-row">
                    <input v-model="chatInput" class="cb-chat-input" placeholder="Ask about batch creation…" />
                    <button class="cb-chat-send" type="button"><el-icon><Promotion /></el-icon></button>
                </div>
            </div>
            <button class="cb-float-btn" type="button" @click="chatOpen = !chatOpen">
                <el-icon><ChatDotRound /></el-icon>
            </button>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ── Base ───────────────────────────────────────────────────────── */
.cb-page {
    min-height: calc(100vh - 48px);
    background: var(--surface, #f7f9fb);
    color: #1f2a2a;
    padding-bottom: 60px;
    font-family: 'Manrope', sans-serif;
}

/* ── Header ─────────────────────────────────────────────────────── */
.cb-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 24px;
    border-bottom: 1px solid #e8ecec;
    background: #fff;
    flex-wrap: wrap;
}
.cb-kicker {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: #94a1b2;
    margin-bottom: 4px;
}
.cb-title {
    font-size: 18px;
    font-weight: 800;
    color: #003f2c;
    margin: 0 0 3px;
    line-height: 1.15;
}
.cb-subtitle {
    font-size: 12px;
    color: #657386;
    margin: 0 0 8px;
    line-height: 1.5;
}
.cb-badge-row { display: flex; gap: 5px; flex-wrap: wrap; }
.cb-header-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.cb-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    padding: 0 12px; height: 30px; border-radius: 5px;
    font-size: 11px; font-weight: 700; letter-spacing: .03em;
    cursor: pointer; border: 1px solid transparent;
    text-decoration: none; white-space: nowrap;
    transition: background .14s, color .14s, border-color .14s;
}
.cb-btn--primary { background: #003f2c; color: #fff; border-color: #003f2c; }
.cb-btn--primary:hover { background: #004532; }
.cb-btn--outline { background: #fff; color: #263232; border-color: #d4d8d8; }
.cb-btn--outline:hover { border-color: #003f2c; color: #003f2c; }
.cb-btn--ghost { background: transparent; color: #657386; border-color: transparent; }
.cb-btn--ghost:hover { background: #f3f4f4; }

/* ── Badges ──────────────────────────────────────────────────────── */
.cb-badge {
    display: inline-flex; align-items: center;
    padding: 3px 7px; border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 700;
    letter-spacing: .1em; text-transform: uppercase;
}
.cb-badge--green  { background: #eef5f1; color: #004532; border: 1px solid #c3ddd2; }
.cb-badge--blue   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.cb-badge--amber  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.cb-badge--purple { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
.cb-badge--gray   { background: #f0f2f2; color: #657386; border: 1px solid #d4d8d8; }

/* ── Context panel ───────────────────────────────────────────────── */
.cb-context {
    display: flex;
    align-items: center;
    gap: 0;
    border-bottom: 1px solid #e8ecec;
    background: #fafbfb;
    flex-wrap: wrap;
}
.cb-ctx-block {
    padding: 10px 20px;
    border-right: 1px solid #e8ecec;
    flex: 1;
    min-width: 140px;
}
.cb-ctx-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .12em; text-transform: uppercase;
    color: #94a1b2; margin-bottom: 3px;
}
.cb-ctx-val {
    font-size: 16px; font-weight: 800; color: #1f2a2a; line-height: 1.1;
}
.cb-ctx-val--green { color: #004532; }
.cb-ctx-sub { font-size: 10px; color: #94a1b2; margin-top: 1px; }
.cb-ctx-actions {
    padding: 10px 20px;
    display: flex; align-items: center; gap: 6px; flex-shrink: 0;
}

/* ── Body layout ─────────────────────────────────────────────────── */
.cb-body {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(0, 0.8fr);
    gap: 16px;
    padding: 16px 24px;
    align-items: start;
}
@media (max-width: 1100px) { .cb-body { grid-template-columns: 1fr; } }

.cb-main { display: flex; flex-direction: column; gap: 12px; }
.cb-rail { display: flex; flex-direction: column; gap: 12px; }

/* ── Card ────────────────────────────────────────────────────────── */
.cb-card {
    border: 1px solid #e4e7e8;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
}
.cb-card--sticky { position: sticky; top: 12px; }
.cb-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 8px 14px;
    border-bottom: 1px solid #e8ecec;
    background: #f8f9f9;
    border-radius: 7px 7px 0 0;
    flex-wrap: wrap;
}
.cb-card-title {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 800; color: #1f2a2a;
    letter-spacing: .04em; text-transform: uppercase;
    font-family: 'IBM Plex Mono', monospace;
}
.cb-card-body { padding: 14px; }
.cb-card-body--pipe { padding: 16px 14px; }

/* ── Form layout ─────────────────────────────────────────────────── */
.cb-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
@media (max-width: 640px) { .cb-form-grid { grid-template-columns: 1fr; } }

.cb-field { display: flex; flex-direction: column; gap: 5px; }
.cb-field--full { grid-column: 1 / -1; }
.cb-field label {
    font-size: 11px; font-weight: 700;
    color: #4b5563; letter-spacing: .03em;
}
.cb-req { color: #ef4444; }
.cb-field-label {
    font-size: 11px; font-weight: 700;
    color: #4b5563; letter-spacing: .03em; margin-bottom: 6px;
}
.cb-error { margin-top: 4px; }
.cb-error :deep(p) { margin: 0; font-size: 11px; }

/* ── Two-col section row ─────────────────────────────────────────── */
.cb-two-col {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
@media (max-width: 900px) { .cb-two-col { grid-template-columns: 1fr; } }

/* ── Harvest table ───────────────────────────────────────────────── */
.cb-table-wrap { overflow-x: auto; }
.cb-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.cb-table thead th {
    padding: 7px 10px;
    background: #f6f8f8;
    border-bottom: 1px solid #e4e7e8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800;
    letter-spacing: .12em; text-transform: uppercase;
    color: #7b8796; white-space: nowrap;
}
.cb-table tbody td { padding: 8px 10px; border-bottom: 1px solid #f0f2f2; vertical-align: middle; }
.cb-table tbody tr:last-child td { border-bottom: none; }
.cb-table tbody tr { cursor: pointer; transition: background .12s; }
.cb-table tbody tr:hover td { background: #f8fbf9; }
.cb-tr--selected td { background: rgba(0,69,50,.04) !important; }

.cb-checkbox {
    display: inline-flex; align-items: center; justify-content: center;
    width: 16px; height: 16px;
    border: 1px solid #d4d8d8; border-radius: 4px;
    color: transparent;
}
.cb-checkbox--on { background: #003f2c; border-color: #003f2c; color: #fff; }

.cb-code {
    display: inline-flex; padding: 2px 6px; border-radius: 4px;
    background: #eef5f1; color: #003f2c;
    font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 800;
}
.cb-score {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 36px; padding: 2px 6px; border-radius: 4px;
    border: 1px solid #c3ddd2; background: #eef5f1;
    color: #004532; font-size: 11px; font-weight: 800;
}
.cb-pill {
    display: inline-flex; align-items: center;
    padding: 2px 7px; border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
}
.cb-pill--green { background: #eef5f1; color: #004532; border: 1px solid #c3ddd2; }
.cb-pill--blue  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

/* ── Table footer / summary strip ───────────────────────────────── */
.cb-table-foot { padding: 0; border-top: 1px solid #e8ecec; }
.cb-summary-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    background: linear-gradient(135deg, #003f2c, #004532);
}
.cb-strip-item { padding: 10px 14px; text-align: center; border-right: 1px solid rgba(255,255,255,.1); }
.cb-strip-item:last-child { border-right: none; }
.cb-strip-item span { display: block; font-size: 9px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: rgba(255,255,255,.6); margin-bottom: 3px; }
.cb-strip-item strong { display: block; font-size: 14px; font-weight: 800; color: #fff; }

/* ── Flavor chips ────────────────────────────────────────────────── */
.cb-flavor-grid { display: flex; flex-wrap: wrap; gap: 6px; }
.cb-flavor-chip {
    padding: 4px 10px; border-radius: 5px;
    border: 1px solid #d4d8d8; background: #f8f9f9;
    font-size: 11px; font-weight: 600; color: #4b5563;
    cursor: pointer; transition: background .12s, border-color .12s, color .12s;
}
.cb-flavor-chip:hover { border-color: #004532; color: #004532; background: #eef5f1; }
.cb-flavor-chip--on { background: #eef5f1; border-color: #004532; color: #004532; }

/* ── Toggle ──────────────────────────────────────────────────────── */
.cb-toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 10px; border: 1px solid #e4e7e8; border-radius: 6px; background: #fafbfb; }
.cb-toggle { width: 36px; height: 20px; border-radius: 999px; border: 1px solid #d4d8d8; padding: 2px; background: #fff; cursor: pointer; transition: background .2s; flex-shrink: 0; }
.cb-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #d4d8d8; transition: transform .2s; }
.cb-toggle--on { background: #003f2c; border-color: #003f2c; }
.cb-toggle--on i { background: #fff; transform: translateX(16px); }

/* ── Pipeline ────────────────────────────────────────────────────── */
.cb-pipe { display: flex; align-items: stretch; overflow-x: auto; }
.cb-pipe-item { flex: 1; min-width: 70px; display: flex; flex-direction: column; align-items: center; }
.cb-pipe-item__top { text-align: center; padding-bottom: 8px; min-height: 36px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; }
.cb-pipe-item__count { font-size: 16px; font-weight: 800; color: #1f2a2a; line-height: 1; }
.cb-pipe-item__count--current { color: #d97706; }
.cb-pipe-item__sub { font-size: 9px; color: #94a1b2; margin-top: 2px; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; }
.cb-pipe-item__mid { display: flex; align-items: center; width: 100%; }
.cb-pipe-item__seg { flex: 1; height: 2px; background: #e4e7e8; transition: background .2s; }
.cb-pipe-item__seg--done   { background: #004532; }
.cb-pipe-item__seg--hidden { background: transparent; }
.cb-pipe-item__dot { width: 30px; height: 30px; border-radius: 50%; border: 2px solid #d4d8d8; background: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff; position: relative; z-index: 1; transition: border-color .2s, background .2s; }
.cb-pipe-item__dot--done    { border-color: #004532; background: #004532; }
.cb-pipe-item__dot--current { border-color: #d97706; background: #d97706; outline: 4px solid rgba(217,119,6,.18); outline-offset: 1px; }
.cb-pipe-item__bot { text-align: center; padding-top: 8px; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.cb-pipe-item__label { font-size: 10px; font-weight: 800; color: #1f2a2a; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; }
.cb-pipe-item__label--current { color: #d97706; }
.cb-pipe-item__verify { font-size: 9px; color: #b8c0cc; font-family: 'IBM Plex Mono', monospace; letter-spacing: .04em; padding: 2px 6px; border-radius: 3px; border: 1px solid #e8ecec; background: #fafbfb; white-space: nowrap; }
.cb-pipe-item__verify--done    { color: #004532; border-color: #c3ddd2; background: #eef5f1; }
.cb-pipe-item__verify--current { color: #92400e; border-color: #fde68a; background: #fffbeb; }

/* ── AI Insights ─────────────────────────────────────────────────── */
.cb-insights-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
@media (max-width: 768px) { .cb-insights-grid { grid-template-columns: 1fr; } }
.cb-insight-card { display: flex; align-items: flex-start; gap: 8px; padding: 12px; border-radius: 7px; border: 1px solid; }
.cb-insight-card--green { background: #eef5f1; border-color: #c3ddd2; }
.cb-insight-card--blue  { background: #eff6ff; border-color: #bfdbfe; }
.cb-insight-card--amber { background: #fffbeb; border-color: #fde68a; }
.cb-insight-icon { font-size: 13px; color: #004532; flex-shrink: 0; margin-top: 1px; }
.cb-insight-text { font-size: 12px; font-weight: 600; color: #1f2a2a; line-height: 1.55; margin: 0; }

/* ── Form actions row ────────────────────────────────────────────── */
.cb-form-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 14px 0 2px;
    border-top: 1px solid #e8ecec;
}

/* ── Rail cards ──────────────────────────────────────────────────── */
.cb-completion-pill {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px; font-weight: 800;
    color: #004532; background: #eef5f1;
    border: 1px solid #c3ddd2;
    padding: 3px 8px; border-radius: 5px;
}
.cb-summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 12px; }
.cb-summary-stat { text-align: center; padding: 8px; border: 1px solid #e8ecec; border-radius: 5px; background: #fafbfb; }
.cb-summary-val { font-size: 17px; font-weight: 800; color: #1f2a2a; line-height: 1.1; }
.cb-summary-val--green { color: #004532; }
.cb-summary-list { display: flex; flex-direction: column; gap: 0; }
.cb-summary-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f0f2f2; }
.cb-summary-row:last-child { border-bottom: none; }

.cb-progress-label { font-size: 10px; color: #94a1b2; margin-bottom: 4px; }
.cb-progress-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; border: 1px solid #e4e7e8; }
.cb-progress-fill { height: 100%; background: #004532; border-radius: 3px; transition: width .4s; }
.cb-progress-fill--amber { background: #d97706; }

.cb-bar-track { height: 5px; background: #f0f2f2; border-radius: 3px; overflow: hidden; }
.cb-bar-fill { height: 100%; background: #004532; border-radius: 3px; }
.cb-bar-fill--blue { background: #2563eb; }

.cb-qual-row { display: flex; justify-content: space-between; gap: 8px; }

.cb-season-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; margin: 10px 0; }
.cb-season-stat { text-align: center; padding: 8px 4px; }
.cb-link-btn {
    display: flex; align-items: center; justify-content: center;
    height: 30px; border: 1px solid #d4d8d8; border-radius: 5px;
    text-decoration: none; font-size: 11px; font-weight: 700; color: #263232;
    margin-top: 8px; transition: border-color .14s, color .14s;
}
.cb-link-btn:hover { border-color: #003f2c; color: #003f2c; }

.cb-checklist { display: flex; flex-direction: column; gap: 8px; }
.cb-check-item { display: flex; align-items: center; gap: 8px; }
.cb-check-dot {
    width: 17px; height: 17px; border-radius: 50%;
    border: 2px solid #d4d8d8;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 10px; color: #fff; background: #fff;
}
.cb-check-dot--done { border-color: #004532; background: #004532; }

/* ── Helpers ─────────────────────────────────────────────────────── */
.cb-muted  { font-size: 11px; color: #94a1b2; }
.cb-bold   { font-weight: 700; color: #1f2a2a; }

/* ── Floating Chat ───────────────────────────────────────────────── */
.cb-float { position: fixed; bottom: 24px; right: 24px; z-index: 200; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.cb-float-btn { width: 44px; height: 44px; border-radius: 50%; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: background .14s; }
.cb-float-btn:hover { background: #004532; }
.cb-chat-panel { width: 290px; border: 1px solid #e4e7e8; border-radius: 10px; background: #fff; overflow: hidden; }
.cb-chat-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 13px; border-bottom: 1px solid #e8ecec; background: #f8f9f9; }
.cb-chat-close { font-size: 18px; line-height: 1; background: none; border: none; color: #94a1b2; cursor: pointer; padding: 0; }
.cb-chat-prompts { padding: 8px 12px; display: flex; flex-direction: column; gap: 5px; border-bottom: 1px solid #f0f2f2; }
.cb-chat-prompt { text-align: left; background: #f8f9f9; border: 1px solid #e4e7e8; border-radius: 5px; padding: 6px 9px; font-size: 11px; color: #263232; cursor: pointer; transition: background .14s; }
.cb-chat-prompt:hover { background: #eef5f1; border-color: #c3ddd2; color: #004532; }
.cb-chat-input-row { display: flex; align-items: center; padding: 8px 12px; gap: 6px; }
.cb-chat-input { flex: 1; height: 30px; padding: 0 9px; border: 1px solid #d4d8d8; border-radius: 5px; font-size: 11px; outline: none; }
.cb-chat-input:focus { border-color: #003f2c; }
.cb-chat-send { width: 30px; height: 30px; border-radius: 5px; background: #003f2c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; }
</style>
