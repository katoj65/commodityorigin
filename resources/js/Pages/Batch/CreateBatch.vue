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
const selectedHarvestIds = ref([]);

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
    status: (harvest.status || 'ready').toUpperCase(),
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
    'SEASON LINKED',
    'HARVEST SELECTED',
    'TRACEABLE BATCH',
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
        <Head title="Create Batch" />

        <div class="create-batch-page">
            <div class="create-batch-shell">
                <section class="batch-hero">
                    <div>
                        <h1>Create Batch</h1>
                        <p>Create a batch from harvests under <strong>{{ season.name }}</strong></p>

                        <div class="batch-badges">
                            <span
                                v-for="badge in seasonBadges"
                                :key="badge"
                                class="batch-badge"
                                :class="badge === 'HARVEST SELECTED' ? 'is-peach' : 'is-mint'"
                            >
                                <span class="badge-dot"></span>
                                {{ badge }}
                            </span>
                        </div>
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
                                            <td><span class="batch-status-pill">READY</span></td>
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

                                    <div class="batch-form-field">
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

                                    <div class="batch-form-field">
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
.create-batch-page {
    min-height: calc(100vh - 56px);
    background: #ffffff;
}

.create-batch-shell {
    max-width: 1180px;
    margin: 0 auto;
    padding: 30px 18px 40px;
}

.batch-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
}

.batch-hero h1 {
    margin: 0;
    font-size: 34px;
    line-height: 1;
    font-weight: 700;
    letter-spacing: -0.04em;
    color: #111615;
}

.batch-hero p {
    margin: 10px 0 0;
    font-size: 18px;
    color: #2b3632;
}

.batch-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
}

.batch-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 30px;
    padding: 0 13px;
    border-radius: 999px;
    border: 1px solid #d8e1db;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.14em;
    color: #23443b;
}

.batch-badge.is-mint {
    background: #d4fae8;
}

.batch-badge.is-peach {
    background: #f8d3ac;
}

.badge-dot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #0a5d3c;
}

.batch-hero__actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.batch-hero-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 22px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid #d7e0db;
    color: #17201d;
    background: #ffffff;
}

.batch-hero-button.is-primary {
    background: #0a5d3c;
    border-color: #0a5d3c;
    color: #ffffff;
    box-shadow: 0 12px 30px rgba(10, 93, 60, 0.16);
}

.batch-steps {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 18px;
    margin-top: 28px;
}

.batch-step {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #8b9aa4;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.batch-step::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #dfe6e2;
}

.batch-step:last-child::after {
    display: none;
}

.batch-step span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: #edf0ee;
    color: #18231e;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 0;
}

.batch-step.is-active span {
    background: #0a5d3c;
    color: #ffffff;
}

.batch-step.is-active {
    color: #18231e;
}

.batch-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 292px;
    gap: 24px;
    margin-top: 28px;
    align-items: start;
}

.batch-main,
.batch-rail {
    display: grid;
    gap: 24px;
}

.batch-context-card,
.batch-table-card,
.batch-details-card,
.batch-process-card,
.batch-guide-card,
.batch-rail-card,
.batch-notes-card,
.batch-footer-card,
.traceability-card {
    background: #ffffff;
    border: 0.5px solid #e1e7e3;
    border-radius: 14px;
}

.batch-context-card {
    padding: 24px 24px 22px;
}

.batch-section-title,
.batch-card-heading,
.rail-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.batch-section-title span,
.batch-card-heading span,
.rail-section-heading {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: none;
    color: #65817a;
}

.batch-section-title a {
    color: #0d3928;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.batch-context-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 28px 20px;
    margin-top: 28px;
}

.batch-context-grid span,
.batch-form-field label,
.rail-metric-list span {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: none;
    color: #5d6965;
}

.batch-context-label {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
}

.batch-context-label .el-icon {
    font-size: 14px;
    color: #6b8178;
    flex-shrink: 0;
}

.batch-context-label > span {
    display: inline-block;
}

.batch-context-grid strong,
.rail-metric-list strong {
    display: block;
    margin-top: 10px;
    font-size: 16px;
    font-weight: 700;
    color: #111615;
}

.batch-table-card {
    overflow: hidden;
}

.batch-table-card__header {
    padding: 18px 24px;
    border-bottom: 0.5px solid #edf2ee;
}

.batch-table-card__header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #161d1a;
}

.batch-harvest-table {
    width: 100%;
    border-collapse: collapse;
}

.batch-harvest-table th,
.batch-harvest-table td {
    padding: 16px 18px;
    text-align: left;
    border-bottom: 0.5px solid #edf2ee;
    font-size: 14px;
    color: #27312d;
}

.batch-harvest-table th {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #52625c;
}

.batch-harvest-table tbody tr {
    cursor: pointer;
}

.batch-harvest-table tbody tr.is-selected {
    background: #edf4ef;
}

.batch-checkbox {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border: 0.5px solid #d1dad4;
    border-radius: 3px;
    color: transparent;
}

.batch-checkbox.is-checked {
    background: #0a5d3c;
    border-color: #0a5d3c;
    color: #ffffff;
}

.batch-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 22px;
    padding: 0 10px;
    border-radius: 999px;
    background: #e6f1eb;
    color: #0a5d3c;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
}

.batch-selection-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    padding: 18px 24px;
    background: #0b5d46;
}

.batch-selection-summary span,
.batch-footer-card span,
.traceability-card span {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.72);
}

.batch-selection-summary strong {
    display: block;
    margin-top: 8px;
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
}

.batch-lower-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
}

.batch-details-card,
.batch-process-card,
.batch-notes-card {
    padding: 24px;
}

.batch-card-heading {
    justify-content: flex-start;
    gap: 10px;
}

.batch-card-heading .el-icon {
    color: #10271f;
}

.batch-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-top: 24px;
}

.batch-form-field {
    display: flex;
    flex-direction: column;
}

.batch-form-field--full {
    grid-column: 1 / -1;
}

.batch-form-field label {
    margin-bottom: 8px;
}

.batch-date-row,
.batch-form-control,
.batch-form-control.el-date-editor {
    width: 100%;
}

.batch-form-control :deep(.el-textarea__inner) {
    min-height: 120px !important;
}

.batch-input-error {
    margin-top: 6px;
}

.batch-input-error :deep(p) {
    margin: 0;
}

.batch-date-error-slot {
    margin-top: 6px;
}

.batch-date-error-slot :deep(p) {
    margin: 0;
}

.batch-guide-card {
    padding: 20px;
    background: #0b5d46;
    border-color: #0b5d46;
    color: #ffffff;
}

.batch-card-heading.is-light span,
.batch-card-heading.is-light .el-icon {
    color: #ffffff;
}

.batch-guide-card p {
    margin: 26px 0 0;
    font-size: 16px;
    line-height: 1.65;
}

.guide-cta {
    width: 100%;
    min-height: 40px;
    margin-top: 18px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.04);
    color: #ffffff;
    font-size: 15px;
    font-weight: 500;
}

.batch-rail-card {
    padding: 20px;
}

.rail-section-heading {
    color: #18211f;
}

.rail-section-heading span {
    color: #18211f;
}

.rail-metric-list {
    display: grid;
    gap: 16px;
    margin-top: 24px;
}

.rail-metric-list article {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 16px;
}

.rail-metric-list article + article {
    padding-top: 16px;
    border-top: 1px solid #eef2ef;
}

.rail-metric-list strong {
    margin-top: 0;
    text-align: right;
}

.batch-checklist {
    list-style: none;
    padding: 0;
    margin: 22px 0 0;
    display: grid;
    gap: 16px;
}

.batch-checklist li {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    color: #2d3834;
}

.check-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 999px;
    border: 1px solid #cad5cf;
    color: transparent;
}

.check-icon.is-done {
    border-color: #0a5d3c;
    color: #0a5d3c;
}

.dropzone-card {
    display: grid;
    place-items: center;
    min-height: 138px;
    margin-top: 22px;
    border: 1px dashed #dbe3dd;
    border-radius: 12px;
    background: #fbfcfb;
    text-align: center;
    color: #8d9a95;
}

.dropzone-card .el-icon {
    font-size: 34px;
    color: #b7c2bc;
}

.dropzone-card strong {
    display: block;
    margin-top: 10px;
    font-size: 16px;
    color: #3e4a45;
}

.dropzone-card span {
    margin-top: 6px;
    font-size: 13px;
}

.traceability-card {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr) auto;
    align-items: center;
    gap: 14px;
    padding: 18px 16px;
    background: #9eeec6;
    border-color: #9eeec6;
}

.traceability-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.32);
    color: #0d3d29;
}

.traceability-card span {
    color: #356555;
}

.traceability-card strong {
    display: block;
    margin-top: 6px;
    font-size: 18px;
    color: #0d2319;
}

.traceability-card em {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 46px;
    min-height: 42px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.3);
    color: #214d3b;
    font-size: 13px;
    font-style: normal;
    font-weight: 700;
}

.batch-footer-card {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr)) 252px;
    gap: 20px;
    align-items: center;
    padding: 30px;
}

.batch-footer-card span {
    color: #66746f;
}

.batch-footer-card strong {
    display: block;
    margin-top: 8px;
    font-size: 18px;
    line-height: 1.45;
    color: #18211f;
}

.batch-footer-submit {
    min-height: 58px;
    width: 100%;
    justify-self: stretch;
    font-size: 15px;
    letter-spacing: 0.08em;
}

@media (max-width: 1180px) {
    .batch-grid {
        grid-template-columns: 1fr;
    }

    .batch-context-grid,
    .batch-footer-card {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 960px) {
    .batch-hero,
    .batch-form-grid,
    .batch-lower-grid,
    .batch-context-grid,
    .batch-selection-summary,
    .batch-footer-card {
        grid-template-columns: 1fr;
    }

    .batch-hero {
        display: grid;
    }

    .batch-hero__actions,
    .batch-steps {
        grid-template-columns: 1fr;
    }

    .batch-harvest-table {
        min-width: 760px;
    }
}

@media (max-width: 640px) {
    .create-batch-shell {
        padding: 22px 14px 32px;
    }

    .batch-hero h1 {
        font-size: 30px;
    }

    .batch-hero p {
        font-size: 15px;
    }
}
</style>
