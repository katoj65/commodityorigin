<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Briefcase, Check, Collection, Document, Files, Location, Opportunity, Tickets, Calendar } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    season: {
        type: Object,
        required: true,
    },
    harvests: {
        type: Array,
        default: () => [],
    },
});

const processingMethods = ['Washed', 'Natural', 'Honey', 'Anaerobic', 'Semi-washed'];
const dryingMethods = ['Raised beds', 'Patio', 'Mechanical dryer', 'Greenhouse'];
const millingStatuses = ['Pending', 'In milling', 'Milled', 'Ready for grading'];
const selectedHarvestIds = ref(props.harvests.map((h) => h.id));

const formatDate = (value) => {
    if (!value) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(value));
};

const deriveMoisture = (harvest) => {
    const ripeness = Number(harvest.ripeness_percentage || 94);

    return Math.max(10.4, Math.min(12.6, 12.9 - (ripeness * 0.018)));
};

const harvestRows = computed(() => props.harvests.map((harvest, index) => ({
    id: harvest.id,
    code: `#H-2024-${String(index + 1).padStart(2, '0')}`,
    date: formatDate(harvest.harvest_date),
    farm: harvest.farm?.name || 'Blue Ridge Unit',
    type: harvest.variety || 'SL28/34',
    quantity: Number(harvest.weight || 0),
    moisture: deriveMoisture(harvest),
    status: (harvest.status || 'ready').charAt(0).toUpperCase() + (harvest.status || 'ready').slice(1).toLowerCase(),
})));

const selectedHarvests = computed(() => harvestRows.value.filter((harvest) => selectedHarvestIds.value.includes(harvest.id)));
const selectedCount = computed(() => selectedHarvests.value.length);
const totalInputWeight = computed(() => selectedHarvests.value.reduce((sum, harvest) => sum + harvest.quantity, 0));
const averageMoisture = computed(() => {
    if (selectedHarvests.value.length === 0) {
        return 0;
    }

    return selectedHarvests.value.reduce((sum, harvest) => sum + harvest.moisture, 0) / selectedHarvests.value.length;
});
const cleanCoffeeEstimate = computed(() => totalInputWeight.value * 0.8611);
const defectCount = computed(() => `${Math.max(2, selectedCount.value)} / 300g`);
const projectedCupScore = computed(() => {
    const score = 86.5 + Math.max(0, selectedCount.value - 2) * 0.2;
    return `${score.toFixed(1)} SCAA`;
});
const seasonBackUrl = computed(() => route('season.show', props.season.id));
const firstSelectedHarvest = computed(() => selectedHarvests.value[0] ?? harvestRows.value[0] ?? null);

const form = useForm({
    batch_number: '',
    variety: '',
    warehouse_location: '',
    quantity_bags: '',
    net_weight_kg: '',
    price: '',
    moisture_content: '',
    processing_date: '',
    processing_method: '',
    drying_method: '',
    drying_duration: '',
    milling_status: '',
    screen_size: '',
    defect_count: '',
    cup_score: '',
    notes: '',
});

const seasonBadges = [
    'Season Linked',
    'Harvest Selected',
    'Traceable Batch',
];

const checklistItems = computed(() => [
    { label: 'Season Context Linked', done: true },
    { label: 'Harvest Inputs Selected', done: selectedCount.value > 0 },
    { label: 'Origin Mapping Validated', done: selectedCount.value > 1 },
    { label: 'Batch Details Ready', done: Boolean(form.batch_number && form.variety) },
]);

const toggleHarvest = (harvestId) => {
    if (selectedHarvestIds.value.includes(harvestId)) {
        selectedHarvestIds.value = selectedHarvestIds.value.filter((id) => id !== harvestId);
        return;
    }

    selectedHarvestIds.value = [...selectedHarvestIds.value, harvestId];
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        season_id: props.season.id,
        harvest_ids: selectedHarvestIds.value,
    }));

    form.post(route('batch.store'), {
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
    });
};
</script>

<template>
    <AppLayout title="Create Batch" full-width flush :show-banner="false">


        <div class="create-batch-page">
            <div class="create-batch-shell">
                <section class="batch-hero">
                    <div>
                        <h1 class="mb-2">Create Batch</h1>
                        <p>Create a batch from harvests under <strong>{{ season.name }}</strong></p>

                   
                    </div>

                    <div class="batch-hero__actions">
                        <Link :href="seasonBackUrl" class="batch-hero-button is-soft">Back to Season</Link>
                    </div>
                </section>

                <section class="batch-steps">
                    <article class="batch-step is-active">
                        <span>1</span>
                        <strong>Select Harvests</strong>
                    </article>
                    <article class="batch-step">
                        <span>2</span>
                        <strong>Batch Details</strong>
                    </article>
                    <article class="batch-step">
                        <span>3</span>
                        <strong>Processing</strong>
                    </article>
                    <article class="batch-step">
                        <span>4</span>
                        <strong>Quality</strong>
                    </article>
                    <article class="batch-step">
                        <span>5</span>
                        <strong>Review</strong>
                    </article>
                </section>

                <form class="batch-grid" @submit.prevent="submit">
                    <div class="batch-main">
                        <section class="batch-context-card">
                            <div class="batch-section-title">
                                <span>Season Context</span>
                                <Link :href="seasonBackUrl">View Season Profile</Link>
                            </div>

                            <div class="batch-context-grid">
                                <article>
                                    <span class="batch-context-label"><el-icon><Collection /></el-icon><span>Season Name</span></span>
                                    <strong>{{ season.name }}</strong>
                                </article>
                                <article>
                                    <span class="batch-context-label"><el-icon><Tickets /></el-icon><span>Season ID</span></span>
                                    <strong>UG-2026-MAIN</strong>
                                </article>
                                <article>
                                    <span class="batch-context-label"><el-icon><Document /></el-icon><span>Type</span></span>
                                    <strong>Main Crop</strong>
                                </article>
                                <article>
                                    <span class="batch-context-label"><el-icon><Briefcase /></el-icon><span>Farm / Coop</span></span>
                                    <strong>Atelier Coop</strong>
                                </article>
                                <article>
                                    <span class="batch-context-label"><el-icon><Location /></el-icon><span>Region</span></span>
                                    <strong>{{ season.region }}</strong>
                                </article>
                                <article>
                                    <span class="batch-context-label"><el-icon><Calendar /></el-icon><span>Total Harvests</span></span>
                                    <strong>{{ season.harvests_count ?? harvests.length }}</strong>
                                </article>
                                <article>
                                    <span class="batch-context-label"><el-icon><Files /></el-icon><span>Total Quantity</span></span>
                                    <strong>{{ Number(season.harvests_sum_weight ?? totalInputWeight).toLocaleString() }}kg</strong>
                                </article>
                            </div>
                        </section>

                        <section class="batch-table-card">
                            <div class="batch-table-card__header">
                                <h2>Select Harvests for Batching</h2>
                            </div>

                            <div class="batch-table-wrap">
                                <table class="batch-harvest-table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Harvest ID</th>
                                            <th>Date</th>
                                            <th>Farm</th>
                                            <th>Type</th>
                                            <th>Qty (kg)</th>
                                            <th>Moisture (%)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="harvest in harvestRows"
                                            :key="harvest.id"
                                            :class="{ 'is-selected': selectedHarvestIds.includes(harvest.id) }"
                                            @click="toggleHarvest(harvest.id)"
                                        >
                                            <td>
                                                <span class="batch-checkbox" :class="{ 'is-checked': selectedHarvestIds.includes(harvest.id) }">
                                                    <el-icon v-if="selectedHarvestIds.includes(harvest.id)"><Check /></el-icon>
                                                </span>
                                            </td>
                                            <td>{{ harvest.code }}</td>
                                            <td>{{ harvest.date }}</td>
                                            <td>{{ harvest.farm }}</td>
                                            <td>{{ harvest.type }}</td>
                                            <td>{{ harvest.quantity.toLocaleString() }}</td>
                                            <td>{{ harvest.moisture.toFixed(1) }}%</td>
                                            <td><span class="batch-status-pill">{{ harvest.status }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="batch-selection-summary">
                                <article>
                                    <span>Selected</span>
                                    <strong>{{ selectedCount }} Harvests</strong>
                                </article>
                                <article>
                                    <span>Total Input</span>
                                    <strong>{{ totalInputWeight.toLocaleString() }}kg</strong>
                                </article>
                                <article>
                                    <span>Est. Clean Coffee</span>
                                    <strong>{{ cleanCoffeeEstimate.toFixed(0) }}kg</strong>
                                </article>
                            </div>
                        </section>

                        <div class="batch-lower-grid">
                            <section class="batch-details-card">
                                <div class="batch-card-heading">
                                    <el-icon><Tickets /></el-icon>
                                    <span>Batch Details</span>
                                </div>

                                <div class="batch-form-grid">
                                    <div class="batch-form-field">
                                        <label>Batch number</label>
                                        <el-input v-model="form.batch_number" class="batch-form-control" placeholder="e.g. BATCH-2026-001" />
                                        <InputError :message="form.errors.batch_number" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Variety <span class="text-red-500">*</span></label>
                                        <el-input v-model="form.variety" class="batch-form-control" placeholder="e.g. Bourbon, Geisha, SL-28" />
                                        <InputError :message="form.errors.variety" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field batch-form-field--full">
                                        <label>Warehouse location</label>
                                        <el-input v-model="form.warehouse_location" class="batch-form-control" placeholder="Warehouse or collection point" />
                                        <InputError :message="form.errors.warehouse_location" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Quantity (bags)</label>
                                        <el-input v-model="form.quantity_bags" class="batch-form-control" type="number" min="1" placeholder="e.g. 12" />
                                        <InputError :message="form.errors.quantity_bags" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Net weight (kg)</label>
                                        <el-input v-model="form.net_weight_kg" class="batch-form-control" type="number" min="1" step="0.01" placeholder="e.g. 720" />
                                        <InputError :message="form.errors.net_weight_kg" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Moisture content</label>
                                        <el-input v-model="form.moisture_content" class="batch-form-control" type="number" min="0" max="100" step="0.01" placeholder="Optional moisture percentage" />
                                        <InputError :message="form.errors.moisture_content" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Price <span class="text-red-500">*</span></label>
                                        <el-input v-model="form.price" class="batch-form-control" type="number" min="0" step="0.01" placeholder="e.g. 2450" />
                                        <InputError :message="form.errors.price" class="batch-input-error" />
                                    </div>
                                </div>
                            </section>

                            <section class="batch-process-card">
                                <div class="batch-card-heading">
                                    <el-icon><Opportunity /></el-icon>
                                    <span>Processing Configuration</span>
                                </div>

                                <div class="batch-form-grid">
                                    <div class="batch-form-field batch-form-field--full">
                                        <label>Processing date <span class="text-red-500">*</span></label>
                                        <div class="batch-date-row">
                                            <el-date-picker
                                                v-model="form.processing_date"
                                                type="date"
                                                value-format="YYYY-MM-DD"
                                                placeholder="Select date"
                                                class="batch-form-control batch-date-picker !w-full"
                                            />
                                        </div>
                                        <div v-if="form.errors.processing_date" class="batch-date-error-slot mt-4">
                                            <InputError :message="form.errors.processing_date" />
                                        </div>
                                    </div>

                                    <div class="batch-form-field mt-3">
                                        <label>Processing method <span class="text-red-500">*</span></label>
                                        <el-select v-model="form.processing_method" clearable filterable placeholder="Select method" class="batch-form-control !w-full">
                                            <el-option
                                                v-for="method in processingMethods"
                                                :key="method"
                                                :label="method"
                                                :value="method"
                                            />
                                        </el-select>
                                        <InputError :message="form.errors.processing_method" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field mt-3">
                                        <label>Drying method <span class="text-red-500">*</span></label>
                                        <el-select v-model="form.drying_method" clearable filterable placeholder="Select drying method" class="batch-form-control !w-full">
                                            <el-option
                                                v-for="method in dryingMethods"
                                                :key="method"
                                                :label="method"
                                                :value="method"
                                            />
                                        </el-select>
                                        <InputError :message="form.errors.drying_method" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Drying duration (days)</label>
                                        <el-input v-model="form.drying_duration" class="batch-form-control" type="number" min="0" placeholder="e.g. 14" />
                                        <InputError :message="form.errors.drying_duration" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Milling status</label>
                                        <el-select v-model="form.milling_status" clearable filterable placeholder="Select status" class="batch-form-control !w-full">
                                            <el-option
                                                v-for="status in millingStatuses"
                                                :key="status"
                                                :label="status"
                                                :value="status"
                                            />
                                        </el-select>
                                        <InputError :message="form.errors.milling_status" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Screen size</label>
                                        <el-input v-model="form.screen_size" class="batch-form-control" placeholder="e.g. 16/18" />
                                        <InputError :message="form.errors.screen_size" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Defect count</label>
                                        <el-input v-model="form.defect_count" class="batch-form-control" type="number" min="0" placeholder="e.g. 8" />
                                        <InputError :message="form.errors.defect_count" class="batch-input-error" />
                                    </div>

                                    <div class="batch-form-field">
                                        <label>Cup score</label>
                                        <el-input v-model="form.cup_score" class="batch-form-control" type="number" min="0" max="100" step="0.01" placeholder="e.g. 86.75" />
                                        <InputError :message="form.errors.cup_score" class="batch-input-error" />
                                    </div>
                                </div>
                            </section>
                        </div>

                        <section class="batch-notes-card">
                            <div class="batch-card-heading">
                                <el-icon><Document /></el-icon>
                                <span>Notes</span>
                            </div>

                            <div class="batch-form-field batch-form-field--full">
                                <label>Notes</label>
                                <el-input
                                    v-model="form.notes"
                                    class="batch-form-control"
                                    type="textarea"
                                    resize="vertical"
                                    placeholder="Add warehouse, quality, or traceability notes"
                                />
                                <InputError :message="form.errors.notes" class="batch-input-error" />
                            </div>
                        </section>

                        <section class="batch-footer-card">
                            <article>
                                <span>Linked Season</span>
                                <strong>{{ season.name }}</strong>
                            </article>
                            <article>
                                <span>Projected Batch Qty</span>
                                <strong>{{ cleanCoffeeEstimate.toFixed(0) }} kg Clean</strong>
                            </article>
                            <article>
                                <span>Processing</span>
                                <strong>{{ form.processing_method || 'Not set' }}<template v-if="form.drying_duration"> · {{ form.drying_duration }}h Fermentation</template></strong>
                            </article>
                            <SubmitButton
                                native-type="submit"
                                class="batch-footer-submit"
                                :loading="form.processing"
                                :disabled="form.processing"
                            >
                                Create Batch
                            </SubmitButton>
                        </section>
                    </div>

                    <aside class="batch-rail">
                        <section class="batch-guide-card">
                            <div class="batch-card-heading is-light">
                                <el-icon><Briefcase /></el-icon>
                                <span>AI Batch Guidance</span>
                            </div>
                            <p>
                                “Moisture verification recommended before tokenisation. Harvest
                                {{ firstSelectedHarvest?.code || '#H-2024-03' }} is at the upper limit
                                ({{ averageMoisture.toFixed(1) || '11.5' }}%). Blending might be necessary.”
                            </p>
                            <button type="button" class="guide-cta">Apply Optimization</button>
                        </section>

                        <section class="batch-rail-card">
                            <div class="rail-section-heading">Quality Indicators <span>Score: 88%</span></div>
                            <div class="rail-metric-list">
                                <article>
                                    <span>Avg. Moisture</span>
                                    <strong>{{ averageMoisture.toFixed(2) }}%</strong>
                                </article>
                                <article>
                                    <span>Defect Count</span>
                                    <strong>{{ defectCount }}</strong>
                                </article>
                                <article>
                                    <span>Expected Cup Score</span>
                                    <strong>{{ projectedCupScore }}</strong>
                                </article>
                            </div>
                        </section>

                        <section class="batch-rail-card">
                            <div class="rail-section-heading">Readiness Checklist</div>
                            <ul class="batch-checklist">
                                <li v-for="item in checklistItems" :key="item.label">
                                    <span class="check-icon" :class="{ 'is-done': item.done }">
                                        <el-icon><Check /></el-icon>
                                    </span>
                                    <span>{{ item.label }}</span>
                                </li>
                            </ul>
                        </section>

                        <section class="batch-rail-card">
                            <div class="rail-section-heading">Documents &amp; Evidence</div>
                            <div class="dropzone-card">
                                <el-icon><Document /></el-icon>
                                <strong>Drop harvest photos / reports</strong>
                                <span>PDF, JPG up to 10MB</span>
                            </div>
                        </section>

                        <section class="traceability-card">
                            <div class="traceability-card__icon">
                                <el-icon><Collection /></el-icon>
                            </div>
                            <div>
                                <span>Traceability Status</span>
                                <strong>100% Secure Blockchain Link</strong>
                            </div>
                            <em>Level 4</em>
                        </section>
                    </aside>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.create-batch-page {
    --primary:            #004532;
    --primary-grad:       #065f46;
    --on-primary:         #ffffff;
    --on-surface:         #191c1e;
    --on-surface-var:     #74777a;
    --surface:            #f7f9fb;
    --surface-low:        #f2f4f6;
    --surface-high:       #e6e8ea;
    --surface-white:      #ffffff;
    --primary-fixed:      #a6f2d1;
    --on-primary-fixed:   #002116;
    --secondary-fixed:    #fedcbe;
    --on-secondary-fixed: #291806;
    font-family: 'Manrope', system-ui, sans-serif;
    min-height: calc(100vh - 56px);
    background: var(--surface-white);
}

.create-batch-shell {
    max-width: 1180px;
    margin: 0 auto;
    padding: 28px 18px 40px;
}

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.batch-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
}

.batch-hero h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--on-surface);
    line-height: 1.15;
}

.batch-hero p {
    margin: 6px 0 0;
    font-size: 13px;
    color: var(--on-surface-var);
    line-height: 1.5;
}

.batch-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 16px;
}

.batch-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 24px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: capitalize;
}

.batch-badge.is-mint {
    background: var(--primary-fixed);
    color: var(--on-primary-fixed);
}

.batch-badge.is-peach {
    background: var(--secondary-fixed);
    color: var(--on-secondary-fixed);
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: currentColor;
    opacity: 0.7;
}

.batch-hero__actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.batch-hero-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid var(--surface-high);
    color: var(--on-surface);
    background: var(--surface-white);
    transition: background 0.15s ease;
}

.batch-hero-button:hover { background: var(--surface-low); }

.batch-hero-button.is-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    border-color: transparent;
    color: var(--on-primary);
}

.batch-hero-button.is-primary:hover { opacity: 0.9; }

/* ── Steps ─────────────────────────────────────────────────────────────────── */
.batch-steps {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
    margin-top: 24px;
}

.batch-step {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--on-surface-var);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: capitalize;
}

.batch-step::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--surface-high);
}

.batch-step:last-child::after { display: none; }

.batch-step span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--surface-low);
    color: var(--on-surface-var);
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0;
    flex-shrink: 0;
}

.batch-step.is-active span {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary);
}

.batch-step.is-active { color: var(--on-surface); }

/* ── Grid ──────────────────────────────────────────────────────────────────── */
.batch-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 280px;
    gap: 20px;
    margin-top: 24px;
    align-items: start;
}

.batch-main,
.batch-rail {
    display: grid;
    gap: 20px;
}

/* ── Card base ─────────────────────────────────────────────────────────────── */
.batch-context-card,
.batch-table-card,
.batch-details-card,
.batch-process-card,
.batch-guide-card,
.batch-rail-card,
.batch-notes-card,
.batch-footer-card,
.traceability-card {
    background: var(--surface-white);
    border: 1px solid var(--surface-high);
    border-radius: 10px;
}

/* ── Season context card ───────────────────────────────────────────────────── */
.batch-context-card { padding: 20px; }

.batch-section-title,
.batch-card-heading,
.rail-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.batch-section-title span,
.batch-card-heading span {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: capitalize;
    color: var(--on-surface-var);
}

.batch-section-title a {
    color: var(--primary);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
}
.batch-section-title a:hover { text-decoration: underline; }

.batch-context-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px 16px;
    margin-top: 20px;
}

.batch-context-grid span,
.batch-form-field label,
.rail-metric-list span {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: var(--on-surface-var);
}

.batch-context-label {
    display: inline-flex !important;
    align-items: center;
    gap: 6px;
}

.batch-context-label .el-icon {
    font-size: 13px;
    color: var(--primary);
    opacity: 0.7;
    flex-shrink: 0;
}

.batch-context-label > span { display: inline-block; }

.batch-context-grid strong,
.rail-metric-list strong {
    display: block;
    margin-top: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--on-surface);
}

/* ── Harvest table card ────────────────────────────────────────────────────── */
.batch-table-card { overflow: hidden; }

.batch-table-card__header {
    padding: 14px 20px;
    border-bottom: 1px solid var(--surface-high);
}

.batch-table-card__header h2 {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: var(--on-surface);
}

.batch-harvest-table {
    width: 100%;
    border-collapse: collapse;
}

.batch-harvest-table th,
.batch-harvest-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid var(--surface-low);
    font-size: 13px;
    color: var(--on-surface);
}

.batch-harvest-table th {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: capitalize;
    color: var(--on-surface-var);
    background: var(--surface-low);
}

.batch-harvest-table tbody tr { cursor: pointer; transition: background 0.1s ease; }
.batch-harvest-table tbody tr:hover { background: var(--surface-low); }
.batch-harvest-table tbody tr.is-selected { background: rgba(0,69,50,0.05); }
.batch-harvest-table tbody tr:last-child td { border-bottom: none; }

.batch-checkbox {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border: 1px solid var(--surface-high);
    border-radius: 4px;
    color: transparent;
}

.batch-checkbox.is-checked {
    background: var(--primary);
    border-color: var(--primary);
    color: var(--on-primary);
}

.batch-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 20px;
    padding: 0 8px;
    border-radius: 999px;
    background: var(--primary-fixed);
    color: var(--on-primary-fixed);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.08em;
}

/* ── Selection summary bar ─────────────────────────────────────────────────── */
.batch-selection-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    padding: 14px 20px;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
}

.batch-selection-summary span,
.batch-footer-card span,
.traceability-card span {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: capitalize;
    color: rgba(255,255,255,0.65);
}

.batch-selection-summary strong {
    display: block;
    margin-top: 6px;
    font-size: 15px;
    font-weight: 800;
    color: var(--on-primary);
}

/* ── Lower two-column grid ─────────────────────────────────────────────────── */
.batch-lower-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.batch-details-card,
.batch-process-card,
.batch-notes-card {
    padding: 20px;
}

.batch-card-heading {
    justify-content: flex-start;
    gap: 8px;
}

.batch-card-heading .el-icon {
    font-size: 15px;
    color: var(--primary);
}

/* ── Form fields ───────────────────────────────────────────────────────────── */
.batch-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
    margin-top: 18px;
}

.batch-form-field { display: flex; flex-direction: column; }
.batch-form-field--full { grid-column: 1 / -1; }
.batch-form-field label { margin-bottom: 6px; }

.batch-date-row,
.batch-form-control,
.batch-form-control.el-date-editor { width: 100%; }

.batch-form-control :deep(.el-textarea__inner) { min-height: 120px !important; }
.batch-input-error { margin-top: 6px; }
.batch-input-error :deep(p) { margin: 0; }
.batch-date-error-slot { margin-top: 6px; }
.batch-date-error-slot :deep(p) { margin: 0; }

/* ── Rail ──────────────────────────────────────────────────────────────────── */
.batch-guide-card {
    padding: 18px;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    border-color: transparent;
    color: var(--on-primary);
}

.batch-card-heading.is-light span,
.batch-card-heading.is-light .el-icon { color: rgba(255,255,255,0.85); }

.batch-guide-card p {
    margin: 18px 0 0;
    font-size: 13px;
    line-height: 1.65;
    color: rgba(255,255,255,0.85);
}

.guide-cta {
    width: 100%;
    min-height: 36px;
    margin-top: 14px;
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 6px;
    background: rgba(255,255,255,0.08);
    color: var(--on-primary);
    font-size: 12px;
    font-weight: 700;
    font-family: 'Manrope', system-ui, sans-serif;
    cursor: pointer;
    transition: background 0.15s ease;
}
.guide-cta:hover { background: rgba(255,255,255,0.14); }

.batch-rail-card { padding: 18px; }

.rail-section-heading {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: capitalize;
    color: var(--on-surface-var);
}

.rail-section-heading span {
    font-size: 11px;
    font-weight: 700;
    color: var(--primary);
}

.rail-metric-list {
    display: grid;
    gap: 0;
    margin-top: 16px;
}

.rail-metric-list article {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--surface-low);
}

.rail-metric-list article:last-child { border-bottom: none; }
.rail-metric-list strong { margin-top: 0; text-align: right; font-size: 13px; }

/* ── Checklist ─────────────────────────────────────────────────────────────── */
.batch-checklist {
    list-style: none;
    padding: 0;
    margin: 16px 0 0;
    display: grid;
    gap: 12px;
}

.batch-checklist li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: var(--on-surface);
}

.check-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 999px;
    border: 1px solid var(--surface-high);
    color: transparent;
    flex-shrink: 0;
}

.check-icon.is-done {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(0,69,50,0.06);
}

/* ── Dropzone ──────────────────────────────────────────────────────────────── */
.dropzone-card {
    display: grid;
    place-items: center;
    min-height: 120px;
    margin-top: 16px;
    border: 1px dashed var(--surface-high);
    border-radius: 8px;
    background: var(--surface-low);
    text-align: center;
    color: var(--on-surface-var);
    gap: 4px;
}

.dropzone-card .el-icon { font-size: 28px; color: var(--surface-high); }

.dropzone-card strong {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--on-surface);
}

.dropzone-card span { font-size: 11px; color: var(--on-surface-var); }

/* ── Traceability card ─────────────────────────────────────────────────────── */
.traceability-card {
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr) auto;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: var(--primary-fixed);
    border-color: var(--primary-fixed);
}

.traceability-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: rgba(0,33,22,0.12);
    color: var(--on-primary-fixed);
    font-size: 16px;
}

.traceability-card span { color: rgba(0,33,22,0.6); font-size: 10px; letter-spacing: 0.06em; text-transform: capitalize; }

.traceability-card strong {
    display: block;
    margin-top: 3px;
    font-size: 12px;
    font-weight: 800;
    color: var(--on-primary-fixed);
}

.traceability-card em {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    min-height: 36px;
    border-radius: 6px;
    background: rgba(0,33,22,0.12);
    color: var(--on-primary-fixed);
    font-size: 12px;
    font-style: normal;
    font-weight: 800;
}

/* ── Footer card ───────────────────────────────────────────────────────────── */
.batch-footer-card {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr)) 220px;
    gap: 16px;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    border-color: transparent;
}

.batch-footer-card span {
    color: rgba(255,255,255,0.65);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: capitalize;
}

.batch-footer-card strong {
    display: block;
    margin-top: 6px;
    font-size: 14px;
    font-weight: 700;
    color: var(--on-primary);
    line-height: 1.4;
}

.batch-footer-submit {
    min-height: 48px;
    width: 100%;
    justify-self: stretch;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.04em;
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1180px) {
    .batch-grid { grid-template-columns: 1fr; }
    .batch-context-grid,
    .batch-footer-card { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 960px) {
    .batch-hero,
    .batch-form-grid,
    .batch-lower-grid,
    .batch-context-grid,
    .batch-selection-summary,
    .batch-footer-card { grid-template-columns: 1fr; }
    .batch-hero { display: grid; }
    .batch-hero__actions,
    .batch-steps { grid-template-columns: 1fr; }
    .batch-harvest-table { min-width: 760px; }
}

@media (max-width: 640px) {
    .create-batch-shell { padding: 18px 14px 28px; }
}
</style>
