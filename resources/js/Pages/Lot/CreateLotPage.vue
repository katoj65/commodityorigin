<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Box, CollectionTag, Files, Tickets, Van } from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    batch: {
        type: Object,
        required: true,
    },
    sourceBatch: {
        type: Object,
        required: true,
    },
    defaults: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({
            packaging_types: [],
            processing_methods: [],
        }),
    },
    canSubmit: {
        type: Boolean,
        default: false,
    },
    submissionBlockedMessage: {
        type: String,
        default: null,
    },
});

const form = useForm({
    lot_number: props.defaults.lot_number ?? '',
    lot_name: props.defaults.lot_name ?? '',
    description: props.defaults.description ?? '',
    image: null,
    allocation_kg: props.defaults.allocation_kg ?? '',
    net_weight_kg: props.defaults.net_weight_kg ?? props.defaults.allocation_kg ?? '',
    quantity_bags: props.defaults.quantity_bags ?? '',
    bag_weight_kg: props.defaults.bag_weight_kg ?? '',
    grade: props.defaults.grade ?? '',
    process: props.defaults.process ?? '',
    packaging_type: props.defaults.packaging_type ?? props.options.packaging_types[0] ?? 'GrainPro',
    quality_score: props.defaults.quality_score ?? '',
    acidity: props.defaults.acidity ?? '',
    body: props.defaults.body ?? '',
    flavor: props.defaults.flavor ?? '',
    aroma: props.defaults.aroma ?? '',
    balance: props.defaults.balance ?? '',
    aftertaste: props.defaults.aftertaste ?? '',
    price: props.defaults.price ?? '',
    notes: '',
    submission_intent: 'create',
});

const formatNumber = (value, digits = 0) => Number(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
});

const formatCurrency = (value) => `Shs. ${formatNumber(value, 2)}`;

const overviewBadges = [
    'BATCH REQUIRED',
    'TRACEABLE',
    'EXPORT PREPARATION',
    'TOKENISATION READY',
];

const lifecycleSteps = [
    { label: 'BATCH', number: '1', active: true },
    { label: 'LOT DETAILS', number: '2', current: true },
    { label: 'SPECIFICATIONS', number: '3' },
    { label: 'EXPORT', number: '4' },
    { label: 'REVIEW', number: '5' },
];

const remainingAfterEntry = computed(() => Math.max(
    Number(props.sourceBatch.remaining_qty_kg || 0) - Number(form.allocation_kg || 0),
    0,
));

const projectedAllocated = computed(() => (
    Number(props.sourceBatch.allocated_qty_kg || 0) + Number(form.allocation_kg || 0)
));

const suggestedPriceLow = computed(() => Number(form.price || 0) * 0.944);
const suggestedPriceHigh = computed(() => Number(form.price || 0) * 1.056);
const hasValue = (value) => String(value ?? '').trim().length > 0;
const sourceMetaItems = computed(() => [
    { label: 'Season', value: props.sourceBatch.season || 'Pending' },
    { label: 'Origin', value: props.sourceBatch.origin || 'Pending' },
    { label: 'Type', value: props.sourceBatch.type || 'Pending' },
    { label: 'Warehouse', value: props.sourceBatch.warehouse || 'Pending' },
]);

const requiredForSubmit = computed(() => ({
    lot_number: hasValue(form.lot_number),
    allocation_kg: Number(form.allocation_kg || 0) > 0,
    quantity_bags: Number(form.quantity_bags || 0) >= 1,
    bag_weight_kg: Number(form.bag_weight_kg || 0) >= 1,
    grade: hasValue(form.grade),
    process: hasValue(form.process),
    packaging_type: hasValue(form.packaging_type),
    price: Number(form.price || 0) > 0,
    within_batch_total: Number(form.allocation_kg || 0) <= Number(props.sourceBatch.batch_total_kg || 0),
}));

const readinessItems = computed(() => [
    { label: 'Verified Batch Origin', complete: true },
    { label: 'Quantity within limits', complete: Number(form.allocation_kg || 0) > 0 },
    { label: 'Packaging specs confirmed', complete: Boolean(form.packaging_type) },
]);

const validateForm = () => {
    form.clearErrors();

    const errors = {};

    if (!requiredForSubmit.value.lot_number) errors.lot_number = 'Lot code is required.';
    if (!requiredForSubmit.value.allocation_kg) errors.allocation_kg = 'Allocation must be greater than 0.';
    if (requiredForSubmit.value.allocation_kg && !requiredForSubmit.value.within_batch_total) {
        errors.allocation_kg = `Allocation cannot exceed the batch total of ${formatNumber(props.sourceBatch.batch_total_kg || 0, 2)} kg.`;
    }
    if (!requiredForSubmit.value.quantity_bags) errors.quantity_bags = 'Bag count must be at least 1.';
    if (!requiredForSubmit.value.bag_weight_kg) errors.bag_weight_kg = 'Bag weight must be at least 1 kg.';
    if (!requiredForSubmit.value.grade) errors.grade = 'Grade is required.';
    if (!requiredForSubmit.value.process) errors.process = 'Processing method is required.';
    if (!requiredForSubmit.value.packaging_type) errors.packaging_type = 'Packaging type is required.';
    if (!requiredForSubmit.value.price) errors.price = 'Price per kg is required.';

    if (!props.canSubmit) {
        errors.batch = props.submissionBlockedMessage || 'Batch must be linked before creating a lot.';
    }

    if (Object.keys(errors).length > 0) {
        form.setError(errors);
        return false;
    }

    return true;
};

const onImageChange = (event) => {
    const [file] = event.target.files ?? [];
    form.image = file ?? null;
};

const submit = (intent = 'create') => {
    if (!validateForm()) {
        return;
    }

    form.submission_intent = intent;
    form.net_weight_kg = Number(form.allocation_kg || 0);
    form.price = Number(form.price || 0);

    form.post(route('batch.store-lot', props.batch.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <DesignPreviewLayout title="Create Lot">
        <Head title="Create Lot" />

        <div class="create-lot-page">
            <section class="lot-page-topbar">
                <div class="lot-page-topbar__copy">
                    <h1>Create Lot</h1>
                    <p>Create an export-ready lot from a verified batch</p>
                </div>

                <nav class="lot-page-tabs" aria-label="Lot creation sections">
                    <button type="button" class="lot-page-tab lot-page-tab--active">Lot Creation</button>
                    <button type="button" class="lot-page-tab">Inventory</button>
                    <button type="button" class="lot-page-tab">History</button>
                </nav>

                <div class="lot-page-topbar__actions">
                    <button
                        type="button"
                        class="lot-page-action lot-page-action--ghost"
                        :disabled="form.processing || !props.canSubmit"
                        @click="submit('draft')"
                    >
                        Save Draft
                    </button>
                    <button type="button" class="lot-page-action lot-page-action--ghost">Preview Lot</button>
                    <button type="button" class="lot-page-action lot-page-action--solid">Ask Advisor</button>
                </div>
            </section>

            <div class="lot-pill-row">
                <span v-for="badge in overviewBadges" :key="badge" class="lot-pill">{{ badge }}</span>
            </div>

            <section class="lot-stepper">
                <article
                    v-for="(step, index) in lifecycleSteps"
                    :key="step.label"
                    class="lot-stepper__step"
                >
                    <div class="lot-stepper__node" :class="{ 'is-active': step.active, 'is-current': step.current }">
                        {{ step.number }}
                    </div>
                    <span>{{ step.label }}</span>
                    <div v-if="index < lifecycleSteps.length - 1" class="lot-stepper__line"></div>
                </article>
            </section>

            <form class="lot-creation-grid" @submit.prevent="submit('create')">
                <main class="lot-creation-main">
                    <section class="lot-surface lot-source-card">
                        <div class="lot-section-head lot-section-head--source">
                            <div class="lot-section-head__copy">
                                <h2>Source Batch Selection</h2>
                                <p>Use a verified batch profile as the traceable baseline for this lot.</p>
                            </div>
                            <span class="lot-source-status">Verified Source</span>
                        </div>

                        <div class="lot-source-grid">
                            <div class="lot-source-grid__selector">
                                <label>Select verified batch</label>
                                <div class="lot-static-select">
                                    <span>{{ props.sourceBatch.label }}</span>
                                    <span class="lot-static-select__chevron">Batch locked</span>
                                </div>
                            </div>

                            <article class="lot-stat-card">
                                <div class="lot-stat-card__head">
                                    <span>AVAILABLE QTY</span>
                                    <el-icon><Box /></el-icon>
                                </div>
                                <strong>{{ formatNumber(props.sourceBatch.available_qty_kg) }}</strong>
                                <small>kg</small>
                            </article>

                            <article class="lot-stat-card">
                                <div class="lot-stat-card__head">
                                    <span>BATCH PRICE</span>
                                    <el-icon><CollectionTag /></el-icon>
                                </div>
                                <strong>{{ props.batch.price ? formatCurrency(props.batch.price) : 'Not set' }}</strong>
                                <small>usd total</small>
                            </article>
                        </div>

                        <div class="lot-source-meta-grid">
                            <article v-for="item in sourceMetaItems" :key="item.label" class="lot-source-meta-item">
                                <span>{{ item.label }}</span>
                                <strong>{{ item.value }}</strong>
                            </article>
                        </div>

                        <p v-if="props.submissionBlockedMessage" class="lot-source-warning">
                            {{ props.submissionBlockedMessage }}
                        </p>
                        <InputError class="mt-2 text-sm" :message="form.errors.batch" />
                    </section>

                    <section class="lot-content-section">
                        <div class="lot-content-section__title">
                            <h2>Quantity Allocation</h2>
                            <span>Batch-aware allocation control</span>
                        </div>

                        <div class="lot-surface lot-allocation-card">
                            <div class="lot-allocation-card__left">
                                <label for="allocation_kg">New lot allocation (kg)</label>
                                <el-input
                                    id="allocation_kg"
                                    v-model="form.allocation_kg"
                                    class="lot-form-control"
                                    type="number"
                                    placeholder="e.g. 400"
                                />
                                <p>Note: Max allocation cannot exceed current batch remainder.</p>
                                <InputError class="mt-2 text-sm" :message="form.errors.allocation_kg" />
                            </div>

                            <div class="lot-allocation-card__summary">
                                <div class="lot-summary-row">
                                    <span class="lot-summary-row__label"><el-icon><Files /></el-icon><span>Batch Total</span></span>
                                    <strong>{{ formatNumber(props.sourceBatch.batch_total_kg) }} kg</strong>
                                </div>
                                <div class="lot-summary-row">
                                    <span class="lot-summary-row__label"><el-icon><Tickets /></el-icon><span>Allocated</span></span>
                                    <strong class="is-warning">{{ formatNumber(Number(form.allocation_kg || 0)) }} kg</strong>
                                </div>
                                <div class="lot-summary-row">
                                    <span class="lot-summary-row__label"><el-icon><Van /></el-icon><span>Remaining</span></span>
                                    <strong class="is-success">{{ formatNumber(remainingAfterEntry) }} kg</strong>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="lot-form-columns">
                        <div class="lot-column">
                            <div class="lot-column__title">Lot Identity</div>
                            <div class="lot-field-grid">
                                <div class="lot-field lot-field--wide">
                                    <label for="lot_name">Lot name</label>
                                    <el-input id="lot_name" v-model="form.lot_name" class="lot-form-control" placeholder="e.g. Bugisu Premium Lot" />
                                    <InputError class="mt-2 text-sm" :message="form.errors.lot_name" />
                                </div>
                                <div class="lot-field lot-field--wide">
                                    <label for="lot_number">Lot code</label>
                                    <el-input id="lot_number" v-model="form.lot_number" class="lot-form-control" placeholder="e.g. LOT-2026-001" />
                                    <InputError class="mt-2 text-sm" :message="form.errors.lot_number" />
                                </div>
                            </div>
                        </div>

                        <div class="lot-column">
                            <div class="lot-column__title">LOGISTICS &amp; PACKING</div>
                            <div class="lot-field-grid">
                                <div class="lot-field">
                                    <label for="quantity_bags">Bag count</label>
                                    <el-input id="quantity_bags" v-model="form.quantity_bags" class="lot-form-control" type="number" placeholder="e.g. 18" />
                                    <InputError class="mt-2 text-sm" :message="form.errors.quantity_bags" />
                                </div>
                                <div class="lot-field">
                                    <label for="bag_weight_kg">Bag weight (kg)</label>
                                    <el-input id="bag_weight_kg" v-model="form.bag_weight_kg" class="lot-form-control" type="number" placeholder="e.g. 60" />
                                    <InputError class="mt-2 text-sm" :message="form.errors.bag_weight_kg" />
                                </div>
                                <div class="lot-field lot-field--wide">
                                    <label>Packaging type</label>
                                    <el-select v-model="form.packaging_type" class="lot-form-control" placeholder="Select packaging type">
                                        <el-option
                                            v-for="option in props.options.packaging_types"
                                            :key="option"
                                            :label="option"
                                            :value="option"
                                        />
                                    </el-select>
                                    <InputError class="mt-2 text-sm" :message="form.errors.packaging_type" />
                                </div>
                            </div>
                        </div>

                        <div class="lot-field lot-field--span-all">
                            <label for="description">Lot description</label>
                            <el-input
                                id="description"
                                v-model="form.description"
                                class="lot-form-control lot-form-control--textarea"
                                type="textarea"
                                :autosize="{ minRows: 3, maxRows: 5 }"
                                placeholder="Describe this lot for buyers and traceability records."
                            />
                            <InputError class="mt-2 text-sm" :message="form.errors.description" />
                        </div>

                        <div class="lot-field lot-field--span-all">
                            <label for="image">Upload image</label>
                            <input
                                id="image"
                                class="lot-upload-input"
                                type="file"
                                accept="image/*"
                                @change="onImageChange"
                            >
                            <small v-if="form.image" class="lot-upload-name">{{ form.image.name }}</small>
                            <InputError class="mt-2 text-sm" :message="form.errors.image" />
                        </div>
                    </section>

                    <section class="lot-form-columns lot-form-columns--spacious">
                        <div class="lot-column">
                            <div class="lot-column__title">Technical Specs</div>
                            <div class="lot-field-grid">
                                <div class="lot-field">
                                    <label>Variety</label>
                                    <el-input :model-value="props.batch.variety || props.sourceBatch.type" class="lot-form-control lot-form-control--readonly" readonly />
                                </div>
                                <div class="lot-field">
                                    <label for="grade">Grade</label>
                                    <el-input id="grade" v-model="form.grade" class="lot-form-control" placeholder="e.g. AA, Screen 18, Specialty" />
                                    <InputError class="mt-2 text-sm" :message="form.errors.grade" />
                                </div>
                                <div class="lot-field">
                                    <label for="process">Processing</label>
                                    <el-select id="process" v-model="form.process" class="lot-form-control" placeholder="Select lot processing method">
                                        <el-option
                                            v-for="option in props.options.processing_methods"
                                            :key="option.id"
                                            :label="option.name"
                                            :value="option.name"
                                        />
                                    </el-select>
                                    <InputError class="mt-2 text-sm" :message="form.errors.process" />
                                </div>
                                <div class="lot-field">
                                    <label for="quality_score">Quality score <small>(optional)</small></label>
                                    <el-input id="quality_score" v-model="form.quality_score" class="lot-form-control" type="number" min="0" max="100" step="0.01" placeholder="e.g. 87.5" />
                                    <InputError class="mt-2 text-sm" :message="form.errors.quality_score" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="lot-form-columns lot-form-columns--spacious">
                        <div class="lot-column">
                            <div class="lot-column__title">Cupping Profile <small>(optional)</small></div>
                            <div class="lot-field-grid">
                                <div class="lot-field">
                                    <label for="acidity">Acidity</label>
                                    <el-select id="acidity" v-model="form.acidity" class="lot-form-control" filterable clearable placeholder="Select an acidity">
                                        <el-option v-for="option in props.options.acidities" :key="option.slug" :label="option.name" :value="option.slug" />
                                    </el-select>
                                    <InputError class="mt-2 text-sm" :message="form.errors.acidity" />
                                </div>
                                <div class="lot-field">
                                    <label for="body">Body</label>
                                    <el-select id="body" v-model="form.body" class="lot-form-control" filterable clearable placeholder="Select a body">
                                        <el-option v-for="option in props.options.bodies" :key="option.slug" :label="option.name" :value="option.slug" />
                                    </el-select>
                                    <InputError class="mt-2 text-sm" :message="form.errors.body" />
                                </div>
                                <div class="lot-field">
                                    <label for="flavor">Flavor</label>
                                    <el-select id="flavor" v-model="form.flavor" class="lot-form-control" filterable clearable placeholder="Select a flavor">
                                        <el-option v-for="option in props.options.flavors" :key="option.slug" :label="option.name" :value="option.slug" />
                                    </el-select>
                                    <InputError class="mt-2 text-sm" :message="form.errors.flavor" />
                                </div>
                                <div class="lot-field">
                                    <label for="aroma">Aroma</label>
                                    <el-select id="aroma" v-model="form.aroma" class="lot-form-control" filterable clearable placeholder="Select an aroma">
                                        <el-option v-for="option in props.options.aromas" :key="option.slug" :label="option.name" :value="option.slug" />
                                    </el-select>
                                    <InputError class="mt-2 text-sm" :message="form.errors.aroma" />
                                </div>
                                <div class="lot-field">
                                    <label for="balance">Balance</label>
                                    <el-input id="balance" v-model="form.balance" class="lot-form-control" type="number" min="0" max="10" step="0.01" placeholder="e.g. 8.0" />
                                    <InputError class="mt-2 text-sm" :message="form.errors.balance" />
                                </div>
                                <div class="lot-field">
                                    <label for="aftertaste">Aftertaste</label>
                                    <el-select id="aftertaste" v-model="form.aftertaste" class="lot-form-control" filterable clearable placeholder="Select an aftertaste">
                                        <el-option v-for="option in props.options.aftertastes" :key="option.slug" :label="option.name" :value="option.slug" />
                                    </el-select>
                                    <InputError class="mt-2 text-sm" :message="form.errors.aftertaste" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="lot-bottom-row">
                        <article class="lot-bottom-card">
                            <div class="lot-content-section__title lot-content-section__title--compact">
                                <h2 class="mb-2">Marketplace Listing</h2>
                                <span>Buyer-facing listing setup</span>
                            </div>

                            <div class="lot-field-grid lot-field-grid--single">
                                <div class="lot-field">
                                    <label for="price">Price per kg <span style="color:#c0392b">*</span></label>
                                    <el-input
                                        id="price"
                                        v-model="form.price"
                                        class="lot-form-control"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="e.g. 4.85"
                                    >
                                        <template #prefix>Shs.</template>
                                    </el-input>
                                    <InputError class="mt-2 text-sm" :message="form.errors.price" />
                                </div>
                            </div>
                        </article>
                    </section>
                </main>

                <aside class="lot-sidebar">
                    <section class="lot-sidebar-card lot-sidebar-card--guidance">
                        <h3>AI MARKET GUIDANCE</h3>
                        <p>
                            Based on your SCA score of {{ formatNumber(props.sourceBatch.quality_score, 1) }} and current harvest
                            trends, this lot is highly competitive in the UAE and Scandinavian specialty market.
                        </p>

                        <div class="lot-metric-box">
                            <span>Suggested Price</span>
                            <strong>{{ formatCurrency(suggestedPriceLow) }} - {{ formatCurrency(suggestedPriceHigh) }}</strong>
                        </div>
                        <div class="lot-metric-box">
                            <span>Projected ROI</span>
                            <strong class="is-success">+18.4%</strong>
                        </div>

                        <button type="button" class="lot-sidebar-button lot-sidebar-button--outline">View Market Report</button>
                    </section>

                    <section class="lot-sidebar-card">
                        <h3>MARKET NEWS FOR THIS LOT</h3>
                        <small>Updates affecting pricing, export readiness, and buyer demand</small>

                        <div class="lot-news-highlight">
                            <p>
                                UAE demand for high-scoring specialty micro-lots may attract faster buyer interest.
                            </p>
                            <div class="lot-tag-row">
                                <span class="lot-news-tag is-positive">POSITIVE</span>
                                <span class="lot-news-tag is-blue">HIGH DEMAND</span>
                                <span class="lot-news-tag is-orange">PRICE IMPACT: MEDIUM</span>
                            </div>
                        </div>

                        <div class="lot-mini-news-grid">
                            <article class="lot-mini-news-card">
                                <strong>Robusta prices rise in key export markets</strong>
                                <span>Higher demand may improve listing prices.</span>
                            </article>
                            <article class="lot-mini-news-card">
                                <strong>New export documentation reminder</strong>
                                <span>Ensure certificate of origin is attached before listing.</span>
                            </article>
                        </div>

                        <div class="lot-chip-cloud">
                            <span>Price Trend</span>
                            <span>Demand Level</span>
                            <span>Export Risk</span>
                            <span>Buyer Interest</span>
                            <span>Quality Req.</span>
                        </div>

                        <div class="lot-recommendation">
                            <p>
                                Recommended action: create and tokenise this lot, then list for UAE buyers.
                            </p>
                            <div class="lot-recommendation__actions">
                                <button type="button" class="lot-sidebar-button lot-sidebar-button--fill">Apply Suggested Price</button>
                                <button type="button" class="lot-sidebar-button lot-sidebar-button--subtle">View Market</button>
                                <button type="button" class="lot-sidebar-button lot-sidebar-button--subtle">Set Alert</button>
                            </div>
                        </div>
                    </section>

                    <section class="lot-sidebar-card">
                        <h3 class="mb-2">Export Readiness</h3>
                        <ul class="lot-readiness-list">
                            <li v-for="item in readinessItems" :key="item.label" :class="{ 'is-complete': item.complete }">
                                <span class="lot-readiness-dot"></span>
                                <span>{{ item.label }}</span>
                            </li>
                        </ul>
                    </section>

                    <section class="lot-sidebar-actions">
                        <SubmitButton
                            class="lot-sidebar-cta lot-sidebar-cta--primary"
                            :loading="form.processing"
                        >
                            Create Lot
                        </SubmitButton>
                    </section>
                </aside>
            </form>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.create-lot-page {
    background: var(--surface, #f7f9fb);
    min-height: 100%;
    padding: 18px 24px 32px;
}

.lot-page-topbar {
    align-items: center;
    background: #ffffff;
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr) auto auto;
    margin-bottom: 18px;
}

.lot-page-topbar__copy h1 {
    color: #0f1720;
    font-size: 25px;
    font-weight: 700;
    letter-spacing: -0.04em;
    line-height: 1;
    margin: 0;
}

.lot-page-topbar__copy p {
    color: #75808f;
    font-size: 13px;
    margin: 6px 0 0;
}

.lot-page-tabs {
    align-items: center;
    display: inline-flex;
    gap: 10px;
}

.lot-page-tab {
    background: transparent;
    border: 0;
    border-bottom: 2px solid transparent;
    color: #65707e;
    cursor: pointer;
    font-size: 14px;
    padding: 8px 2px;
}

.lot-page-tab--active {
    border-bottom-color: #18232f;
    color: #17212b;
}

.lot-page-topbar__actions {
    display: inline-flex;
    gap: 10px;
}

.lot-page-action {
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    padding: 11px 16px;
    transition: 0.2s ease;
}

.lot-page-action:disabled,
.lot-sidebar-cta:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.lot-page-action--ghost {
    background: #ffffff;
    border: 1px solid #d8dfe5;
    color: #23303d;
}

.lot-page-action--solid {
    background: #0f5c3a;
    border: 1px solid #0f5c3a;
    color: #ffffff;
}

.lot-pill-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 18px;
}

.lot-pill {
    align-items: center;
    background: #edf0ec;
    border-radius: 999px;
    color: #4b5968;
    display: inline-flex;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 7px 12px;
}

.lot-stepper {
    align-items: flex-start;
    display: grid;
    gap: 0;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-bottom: 28px;
}

.lot-stepper__step {
    align-items: center;
    display: flex;
    gap: 12px;
    position: relative;
}

.lot-stepper__node {
    align-items: center;
    background: #eaedf1;
    border: 1px solid #eaedf1;
    border-radius: 14px;
    color: #525f6d;
    display: inline-flex;
    font-size: 14px;
    font-weight: 700;
    height: 34px;
    justify-content: center;
    min-width: 34px;
}

.lot-stepper__node.is-active {
    background: #0f5c3a;
    border-color: #0f5c3a;
    color: #ffffff;
}

.lot-stepper__node.is-current {
    background: #ffffff;
    border-color: #17212b;
    color: #17212b;
}

.lot-stepper__step span {
    color: #17212b;
    font-size: 10px;
    letter-spacing: 0.06em;
}

.lot-stepper__line {
    background: #d9dee3;
    flex: 1;
    height: 2px;
    margin-left: 12px;
}

.lot-creation-grid {
    display: grid;
    gap: 24px;
    grid-template-columns: minmax(0, 1.85fr) minmax(320px, 0.85fr);
}

.lot-creation-main {
    display: grid;
    gap: 24px;
}

.lot-surface,
.lot-sidebar-card,
.lot-bottom-card {
    background: #ffffff;
    border: 1px solid #eef2f0;
    border-radius: 6px;
    box-shadow: 0 10px 22px rgba(18, 34, 49, 0.03);
}

.lot-section-head h2,
.lot-column__title,
.lot-sidebar-card h3 {
    color: #18232f;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0.16em;
    margin: 0;
}

.lot-section-head--source {
    align-items: flex-start;
    display: flex;
    justify-content: space-between;
    gap: 14px;
}

.lot-section-head__copy {
    display: grid;
    gap: 6px;
}

.lot-section-head__copy p {
    color: #5f6d79;
    font-size: 13px;
    line-height: 1.6;
    margin: 0;
}

.lot-source-status {
    background: #e8f7ee;
    border: 1px solid #b4dfc4;
    border-radius: 999px;
    color: #0d6a3f;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 8px 10px;
    text-transform: uppercase;
}

.lot-source-card {
    background: #eef2f2;
    padding: 22px 18px 18px;
}

.lot-source-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: minmax(0, 1.7fr) repeat(2, minmax(0, 0.75fr));
    margin-top: 18px;
}

.lot-source-grid__selector label,
.lot-field label,
.lot-allocation-card__left label,
.lot-slider-field label {
    color: #334155;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    letter-spacing: 0.02em;
    line-height: 1.35;
    text-transform: none;
}

.lot-static-select,
.lot-stat-card span {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.lot-static-select {
    align-items: center;
    background: #ffffff;
    border: 1px solid #edf1f5;
    border-radius: 14px;
    color: #111827;
    display: flex;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 15px;
    font-weight: 600;
    justify-content: space-between;
    margin-top: 8px;
    min-height: 48px;
    padding: 12px 14px;
}

.lot-static-select__chevron {
    color: #64748b;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.lot-stat-card {
    background: #ffffff;
    border-radius: 6px;
    padding: 14px 16px;
}

.lot-stat-card__head {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.lot-stat-card__head .el-icon {
    color: #2d6a4f;
    font-size: 15px;
}

.lot-stat-card strong {
    color: #111a23;
    display: block;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.1;
    margin-top: 8px;
}

.lot-stat-card small {
    color: #6e7a87;
    display: block;
    font-size: 12px;
    margin-top: 2px;
}

.lot-source-meta-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-top: 14px;
}

.lot-source-meta-item {
    background: #ffffff;
    border: 1px solid #e0e8ef;
    border-radius: 10px;
    display: grid;
    gap: 4px;
    padding: 10px 12px;
}

.lot-source-meta-item span {
    color: #8090a0;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.lot-source-meta-item strong {
    color: #152432;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.35;
}

.lot-source-warning {
    color: #9b5d27;
    font-size: 13px;
    margin: 14px 0 0;
}

.lot-content-section {
    display: grid;
    gap: 14px;
}

.lot-content-section__title {
    align-items: baseline;
    display: flex;
    gap: 12px;
    justify-content: space-between;
}

.lot-content-section__title h2 {
    color: #111822;
    font-size: 25px;
    font-weight: 700;
    letter-spacing: -0.04em;
    margin: 0;
}

.lot-content-section__title span {
    color: #6d7786;
    font-size: 13px;
}

.lot-content-section__title--compact h2 {
    font-size: 18px;
}

.lot-allocation-card {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1.2fr) minmax(220px, 0.8fr);
    padding: 22px;
}

.lot-form-control {
    width: 100%;
}

.lot-form-control :deep(.el-input__wrapper),
.lot-form-control :deep(.el-select__wrapper) {
    align-items: center;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 0 0 1px #e8edf3 inset;
    color: #111827;
    font-size: 14px;
    font-weight: 600;
    height: 48px;
    min-height: 48px;
    padding-left: 14px;
    padding-right: 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.lot-allocation-card__left p {
    color: #89939f;
    font-size: 11px;
    font-style: italic;
    margin: 10px 0 0;
}

.lot-allocation-card__summary {
    border-left: 1px solid #eef2f0;
    display: grid;
    gap: 14px;
    padding-left: 28px;
}

.lot-summary-row {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.lot-summary-row__label {
    align-items: center;
    color: #5d6877;
    display: inline-flex;
    gap: 8px;
    font-size: 14px;
}

.lot-summary-row__label .el-icon {
    color: #2d6a4f;
    font-size: 14px;
}

.lot-summary-row span {
    color: #5d6877;
    font-size: 14px;
}

.lot-summary-row strong {
    color: #18232f;
    font-size: 20px;
    font-weight: 700;
}

.lot-summary-row strong.is-warning {
    color: #d46f0a;
}

.lot-summary-row strong.is-success,
.lot-metric-box strong.is-success {
    color: #0f8e57;
}

.lot-form-columns {
    border-top: 1px solid #e1e6eb;
    align-items: start;
    display: grid;
    gap: 28px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    padding-top: 22px;
}

.lot-form-columns--spacious {
    padding-bottom: 12px;
}

.lot-column {
    align-content: start;
    display: grid;
    gap: 10px;
}

.lot-field-grid {
    align-items: start;
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.lot-field-grid--single {
    grid-template-columns: 1fr;
}

.lot-field {
    display: grid;
    gap: 8px;
}

.lot-field--wide {
    grid-column: 1 / -1;
}

.lot-field--span-all {
    grid-column: 1 / -1;
}

.lot-field :deep(.el-input),
.lot-field :deep(.el-select) {
    width: 100%;
}

.lot-field :deep(.el-input__inner),
.lot-field :deep(.el-select__selected-item) {
    color: #111827;
}

.lot-field :deep(.el-input__inner::placeholder) {
    color: #a3aab5;
}

.lot-form-control :deep(.el-input__wrapper.is-focus),
.lot-form-control :deep(.el-select__wrapper.is-focused),
.lot-form-control :deep(.el-select__wrapper:focus-within),
.lot-static-select:focus-within {
    box-shadow: 0 0 0 1px #198754 inset, 0 0 0 0.2rem rgba(25, 135, 84, 0.12);
}

.lot-form-control--readonly :deep(.el-input__wrapper) {
    background: #f8fafc;
}

.lot-form-control--readonly :deep(.el-input__inner) {
    color: #475569;
}

.lot-form-control--textarea :deep(.el-textarea__inner) {
    background: #ffffff;
    border: 1px solid #e8edf3;
    border-radius: 14px;
    box-shadow: none;
    color: #111827;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.5;
    min-height: 96px;
    padding: 12px 14px;
}

.lot-form-control--textarea :deep(.el-textarea__inner:focus) {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.12);
    outline: 0;
}

.lot-upload-input {
    background: #ffffff;
    border: 1px solid #e8edf3;
    border-radius: 14px;
    color: #111827;
    cursor: pointer;
    font-size: 13px;
    min-height: 48px;
    padding: 12px 14px;
    width: 100%;
}

.lot-upload-name {
    color: #64748b;
    font-size: 12px;
}

.lot-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.lot-choice-chip {
    background: #ffffff;
    border: 1px solid #eef2f0;
    border-radius: 999px;
    color: #475569;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.04em;
    padding: 10px 14px;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.lot-choice-chip.is-selected {
    background: #f5fbf8;
    border-color: #b7dfc6;
    color: #166534;
}

.lot-slider-stack {
    display: grid;
    gap: 18px;
}

.lot-slider-field {
    display: grid;
    gap: 10px;
}

.lot-slider-field__top {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.lot-slider-field__top span {
    color: #1a2430;
    font-size: 13px;
    font-weight: 700;
}

.lot-bottom-row {
    border-top: 1px solid #e1e6eb;
    display: grid;
    gap: 24px;
    grid-template-columns: minmax(0, 1fr);
    padding-top: 24px;
}

.lot-bottom-row .lot-field-grid--single {
    max-width: 320px;
}

.lot-bottom-card {
    padding: 18px;
}

.lot-sidebar {
    display: grid;
    gap: 20px;
}

.lot-sidebar-card {
    padding: 20px;
}

.lot-sidebar-card--guidance {
    background: #eaf4ee;
    border-color: #c6ddd1;
}

.lot-sidebar-card h3 {
    font-size: 13px;
    margin-bottom: 12px;
}

.lot-sidebar-card p,
.lot-sidebar-card small {
    color: #586372;
    display: block;
    font-size: 13px;
    line-height: 1.7;
}

.lot-metric-box {
    align-items: center;
    background: rgba(255, 255, 255, 0.74);
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    margin-top: 12px;
    padding: 12px 14px;
}

.lot-metric-box span {
    color: #5d6877;
    font-size: 12px;
}

.lot-metric-box strong {
    color: #13212f;
    font-size: 18px;
}

.lot-sidebar-button {
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    margin-top: 14px;
    padding: 11px 14px;
    width: 100%;
}

.lot-sidebar-button--outline {
    background: transparent;
    border: 1px solid #9cb7aa;
    color: #184732;
}

.lot-news-highlight {
    border-left: 3px solid #0eb16a;
    margin-top: 16px;
    padding-left: 14px;
}

.lot-tag-row,
.lot-chip-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}

.lot-news-tag,
.lot-chip-cloud span {
    background: #eef2f5;
    border-radius: 4px;
    color: #5b6673;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 5px 7px;
}

.lot-news-tag.is-positive {
    background: #e4f8eb;
    color: #13824f;
}

.lot-news-tag.is-blue {
    background: #e4edff;
    color: #3062d4;
}

.lot-news-tag.is-orange {
    background: #fff1de;
    color: #c27517;
}

.lot-mini-news-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 16px;
}

.lot-mini-news-card {
    background: #fbfcfd;
    border: 1px solid #ebeff3;
    border-radius: 6px;
    display: grid;
    gap: 6px;
    padding: 14px;
}

.lot-mini-news-card strong {
    color: #16212c;
    font-size: 13px;
    line-height: 1.5;
}

.lot-mini-news-card span {
    color: #6c7684;
    font-size: 11px;
    line-height: 1.6;
}

.lot-recommendation {
    background: #fff3e8;
    border: 1px solid #efd6bc;
    border-radius: 12px;
    margin-top: 16px;
    padding: 14px;
}

.lot-recommendation__actions {
    display: grid;
    gap: 8px;
    grid-template-columns: 1fr 1fr 1fr;
    margin-top: 12px;
}

.lot-sidebar-button--fill {
    background: #8a673f;
    border: 1px solid #8a673f;
    color: #ffffff;
    margin-top: 0;
}

.lot-sidebar-button--subtle {
    background: #fff9f3;
    border: 1px solid #e4d0b8;
    color: #805a30;
    margin-top: 0;
}

.lot-readiness-list {
    display: grid;
    gap: 16px;
    list-style: none;
    margin: 18px 0 0;
    padding: 0;
}

.lot-readiness-list li {
    align-items: center;
    color: #576270;
    display: flex;
    font-size: 14px;
    gap: 12px;
}

.lot-readiness-dot {
    background: transparent;
    border: 1px solid #c7d0d9;
    border-radius: 50%;
    display: inline-flex;
    height: 12px;
    width: 12px;
}

.lot-readiness-list li.is-complete .lot-readiness-dot {
    background: #e2f7ea;
    border-color: #18a75f;
    box-shadow: inset 0 0 0 3px #ffffff;
}

.lot-sidebar-actions {
    display: grid;
    gap: 10px;
}

.lot-sidebar-cta {
    border: 0;
    border-radius: 10px;
    color: #ffffff;
    cursor: pointer;
    font-size: 15px;
    font-weight: 700;
    min-height: 46px;
    padding: 14px 16px;
}

.lot-sidebar-cta--primary {
    background: #0f5c3a;
}

.lot-sidebar-cta--secondary {
    background: linear-gradient(180deg, #3978ff 0%, #255de0 100%);
}

.lot-sidebar-cta--tertiary {
    background: #8b6946;
}

@media (max-width: 1240px) {
    .lot-page-topbar {
        grid-template-columns: 1fr;
    }

    .lot-creation-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 980px) {
    .create-lot-page {
        padding: 16px;
    }

    .lot-source-grid,
    .lot-source-meta-grid,
    .lot-allocation-card,
    .lot-form-columns,
    .lot-mini-news-grid,
    .lot-recommendation__actions {
        grid-template-columns: 1fr;
    }

    .lot-allocation-card__summary {
        border-left: 0;
        border-top: 1px solid #eef2f0;
        padding-left: 0;
        padding-top: 18px;
    }

    .lot-content-section__title {
        align-items: flex-start;
        flex-direction: column;
    }

    .lot-section-head--source {
        align-items: flex-start;
        flex-direction: column;
    }
}

@media (max-width: 640px) {
    .lot-page-tabs,
    .lot-page-topbar__actions,
    .lot-stepper {
        overflow-x: auto;
    }

    .lot-field-grid {
        grid-template-columns: 1fr;
    }

    .lot-page-topbar__copy h1 {
        font-size: 28px;
    }

    .lot-content-section__title h2 {
        font-size: 28px;
    }
}
</style>
