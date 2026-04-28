<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';

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
            target_markets: [],
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
    allocation_kg: props.defaults.allocation_kg ?? '',
    net_weight_kg: props.defaults.net_weight_kg ?? props.defaults.allocation_kg ?? '',
    quantity_bags: props.defaults.quantity_bags ?? '',
    bag_weight_kg: props.defaults.bag_weight_kg ?? '',
    grade: props.defaults.grade ?? '',
    warehouse: props.defaults.warehouse ?? '',
    packaging_type: props.defaults.packaging_type ?? props.options.packaging_types[0] ?? 'GrainPro',
    screen_size: props.defaults.screen_size ?? '',
    altitude: props.defaults.altitude ?? '',
    aroma_score: props.defaults.aroma_score ?? 8.75,
    acidity_score: props.defaults.acidity_score ?? 9.0,
    body_score: props.defaults.body_score ?? 8.25,
    target_market: props.defaults.target_market ?? props.options.target_markets[0] ?? '',
    price_per_kg: props.defaults.price_per_kg ?? '',
    tokenize: props.defaults.tokenize ?? true,
    notes: '',
    submission_intent: 'create',
});

const formatNumber = (value, digits = 0) => Number(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
});

const formatCurrency = (value) => `$${formatNumber(value, 2)}`;

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

const suggestedPriceLow = computed(() => Number(form.price_per_kg || 0) * 0.944);
const suggestedPriceHigh = computed(() => Number(form.price_per_kg || 0) * 1.056);
const canSubmitForm = computed(() => props.canSubmit && Number(form.allocation_kg || 0) > 0);

const readinessItems = computed(() => [
    { label: 'Verified Batch Origin', complete: true },
    { label: 'Quantity within limits', complete: Number(form.allocation_kg || 0) <= Number(props.sourceBatch.remaining_qty_kg || 0) },
    { label: 'Packaging specs confirmed', complete: Boolean(form.packaging_type) },
    { label: 'Warehouse release document', complete: Boolean(form.warehouse) && props.canSubmit },
]);

const submit = (intent = 'create') => {
    form.submission_intent = intent;
    form.net_weight_kg = Number(form.allocation_kg || 0);

    if (intent === 'create_and_tokenise') {
        form.tokenize = true;
    }

    form.post(route('batch.store-lot', props.batch.id));
};
</script>

<template>
    <AppLayout title="Create Lot" full-width flush :show-banner="false">
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
                        <div class="lot-section-head">
                            <h2>SOURCE BATCH SELECTION</h2>
                        </div>

                        <div class="lot-source-grid">
                            <div class="lot-source-grid__selector">
                                <label>SELECT VERIFIED BATCH</label>
                                <div class="lot-static-select">
                                    <span>{{ props.sourceBatch.label }}</span>
                                    <span class="lot-static-select__chevron">v</span>
                                </div>
                            </div>

                            <article class="lot-stat-card">
                                <span>AVAILABLE QTY</span>
                                <strong>{{ formatNumber(props.sourceBatch.available_qty_kg) }}</strong>
                                <small>kg</small>
                            </article>

                            <article class="lot-stat-card">
                                <span>QUALITY SCORE</span>
                                <strong>{{ formatNumber(props.sourceBatch.quality_score, 1) }}</strong>
                                <small>sca</small>
                            </article>
                        </div>

                        <div class="lot-source-meta">
                            <span>Season: {{ props.sourceBatch.season }}</span>
                            <span>Origin: {{ props.sourceBatch.origin }}</span>
                            <span>Type: {{ props.sourceBatch.type }}</span>
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
                                <label for="allocation_kg">NEW LOT ALLOCATION (KG)</label>
                                <input
                                    id="allocation_kg"
                                    v-model.number="form.allocation_kg"
                                    class="lot-large-input"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="e.g. 400"
                                />
                                <p>Note: Max allocation cannot exceed current batch remainder.</p>
                                <InputError class="mt-2 text-sm" :message="form.errors.allocation_kg" />
                            </div>

                            <div class="lot-allocation-card__summary">
                                <div class="lot-summary-row">
                                    <span>Batch Total</span>
                                    <strong>{{ formatNumber(props.sourceBatch.batch_total_kg) }} kg</strong>
                                </div>
                                <div class="lot-summary-row">
                                    <span>Allocated</span>
                                    <strong class="is-warning">{{ formatNumber(projectedAllocated) }} kg</strong>
                                </div>
                                <div class="lot-summary-row">
                                    <span>Remaining</span>
                                    <strong class="is-success">{{ formatNumber(remainingAfterEntry) }} kg</strong>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="lot-form-columns">
                        <div class="lot-column">
                            <div class="lot-column__title">LOT IDENTITY</div>
                            <div class="lot-field-grid">
                                <div class="lot-field lot-field--wide">
                                    <label for="lot_name">LOT NAME</label>
                                    <input id="lot_name" v-model="form.lot_name" type="text">
                                    <InputError class="mt-2 text-sm" :message="form.errors.lot_name" />
                                </div>
                                <div class="lot-field">
                                    <label for="lot_number">LOT CODE</label>
                                    <input id="lot_number" v-model="form.lot_number" type="text">
                                    <InputError class="mt-2 text-sm" :message="form.errors.lot_number" />
                                </div>
                                <div class="lot-field">
                                    <label for="warehouse">WAREHOUSE</label>
                                    <input id="warehouse" v-model="form.warehouse" type="text">
                                    <InputError class="mt-2 text-sm" :message="form.errors.warehouse" />
                                </div>
                            </div>
                        </div>

                        <div class="lot-column">
                            <div class="lot-column__title">LOGISTICS &amp; PACKING</div>
                            <div class="lot-field-grid">
                                <div class="lot-field">
                                    <label for="quantity_bags">BAG COUNT</label>
                                    <input id="quantity_bags" v-model.number="form.quantity_bags" type="number" min="1">
                                    <InputError class="mt-2 text-sm" :message="form.errors.quantity_bags" />
                                </div>
                                <div class="lot-field">
                                    <label for="bag_weight_kg">BAG WEIGHT (KG)</label>
                                    <input id="bag_weight_kg" v-model.number="form.bag_weight_kg" type="number" min="1" step="0.01">
                                    <InputError class="mt-2 text-sm" :message="form.errors.bag_weight_kg" />
                                </div>
                                <div class="lot-field lot-field--wide">
                                    <label>PACKAGING TYPE</label>
                                    <div class="lot-chip-group">
                                        <button
                                            v-for="option in props.options.packaging_types"
                                            :key="option"
                                            type="button"
                                            class="lot-choice-chip"
                                            :class="{ 'is-selected': form.packaging_type === option }"
                                            @click="form.packaging_type = option"
                                        >
                                            {{ option }}
                                        </button>
                                    </div>
                                    <InputError class="mt-2 text-sm" :message="form.errors.packaging_type" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="lot-form-columns lot-form-columns--spacious">
                        <div class="lot-column">
                            <div class="lot-column__title">TECHNICAL SPECS</div>
                            <div class="lot-field-grid">
                                <div class="lot-field">
                                    <label>VARIETY</label>
                                    <input :value="props.batch.variety || props.sourceBatch.type" type="text" readonly>
                                </div>
                                <div class="lot-field">
                                    <label for="grade">GRADE</label>
                                    <input id="grade" v-model="form.grade" type="text">
                                    <InputError class="mt-2 text-sm" :message="form.errors.grade" />
                                </div>
                                <div class="lot-field">
                                    <label for="screen_size">SCREEN SIZE</label>
                                    <input id="screen_size" v-model="form.screen_size" type="text">
                                    <InputError class="mt-2 text-sm" :message="form.errors.screen_size" />
                                </div>
                                <div class="lot-field">
                                    <label for="altitude">ALTITUDE (M)</label>
                                    <input id="altitude" v-model="form.altitude" type="text">
                                    <InputError class="mt-2 text-sm" :message="form.errors.altitude" />
                                </div>
                            </div>
                        </div>

                        <div class="lot-column">
                            <div class="lot-column__title">SENSORY PROFILE</div>
                            <div class="lot-slider-stack">
                                <div class="lot-slider-field">
                                    <div class="lot-slider-field__top">
                                        <label for="aroma_score">AROMA</label>
                                        <span>{{ formatNumber(form.aroma_score, 2) }}</span>
                                    </div>
                                    <input id="aroma_score" v-model.number="form.aroma_score" type="range" min="0" max="10" step="0.01">
                                    <InputError class="mt-2 text-sm" :message="form.errors.aroma_score" />
                                </div>
                                <div class="lot-slider-field">
                                    <div class="lot-slider-field__top">
                                        <label for="acidity_score">ACIDITY</label>
                                        <span>{{ formatNumber(form.acidity_score, 2) }}</span>
                                    </div>
                                    <input id="acidity_score" v-model.number="form.acidity_score" type="range" min="0" max="10" step="0.01">
                                    <InputError class="mt-2 text-sm" :message="form.errors.acidity_score" />
                                </div>
                                <div class="lot-slider-field">
                                    <div class="lot-slider-field__top">
                                        <label for="body_score">BODY</label>
                                        <span>{{ formatNumber(form.body_score, 2) }}</span>
                                    </div>
                                    <input id="body_score" v-model.number="form.body_score" type="range" min="0" max="10" step="0.01">
                                    <InputError class="mt-2 text-sm" :message="form.errors.body_score" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="lot-bottom-row">
                        <article class="lot-bottom-card">
                            <div class="lot-content-section__title lot-content-section__title--compact">
                                <h2>Marketplace Listing</h2>
                                <span>Buyer-facing listing setup</span>
                            </div>

                            <div class="lot-field-grid lot-field-grid--single">
                                <div class="lot-field">
                                    <label for="target_market">TARGET MARKET</label>
                                    <select id="target_market" v-model="form.target_market">
                                        <option v-for="market in props.options.target_markets" :key="market" :value="market">
                                            {{ market }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2 text-sm" :message="form.errors.target_market" />
                                </div>
                                <div class="lot-field">
                                    <label for="price_per_kg">PRICE PER KG (USD)</label>
                                    <input id="price_per_kg" v-model.number="form.price_per_kg" type="number" min="0" step="0.01">
                                    <InputError class="mt-2 text-sm" :message="form.errors.price_per_kg" />
                                </div>
                            </div>
                        </article>

                        <article class="lot-bottom-card">
                            <div class="lot-content-section__title lot-content-section__title--compact">
                                <h2>Tokenisation Settings</h2>
                                <label class="lot-switch">
                                    <input v-model="form.tokenize" type="checkbox">
                                    <span class="lot-switch__track"></span>
                                </label>
                            </div>

                            <div class="lot-token-grid">
                                <div class="lot-token-card">
                                    <span>Network</span>
                                    <strong>Ethereum L2 (Arbitrum)</strong>
                                </div>
                                <div class="lot-token-card">
                                    <span>Asset Class</span>
                                    <strong>RWA / Physical Commodity</strong>
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
                        <h3>EXPORT READINESS</h3>
                        <ul class="lot-readiness-list">
                            <li v-for="item in readinessItems" :key="item.label" :class="{ 'is-complete': item.complete }">
                                <span class="lot-readiness-dot"></span>
                                <span>{{ item.label }}</span>
                            </li>
                        </ul>
                    </section>

                    <section class="lot-sidebar-actions">
                        <button
                            type="submit"
                            class="lot-sidebar-cta lot-sidebar-cta--primary"
                            :disabled="form.processing || !canSubmitForm"
                        >
                            Create Lot
                        </button>
                        <button
                            type="button"
                            class="lot-sidebar-cta lot-sidebar-cta--secondary"
                            :disabled="form.processing || !canSubmitForm"
                            @click="submit('create_and_tokenise')"
                        >
                            Create &amp; Tokenise
                        </button>
                        <button
                            type="button"
                            class="lot-sidebar-cta lot-sidebar-cta--tertiary"
                            :disabled="form.processing || !canSubmitForm"
                            @click="submit('create_and_list')"
                        >
                            Create &amp; List
                        </button>
                    </section>
                </aside>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.create-lot-page {
    background: #f7f6f2;
    min-height: 100%;
    padding: 18px 24px 32px;
}

.lot-page-topbar {
    align-items: center;
    background: #f7f6f2;
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr) auto auto;
    margin-bottom: 18px;
}

.lot-page-topbar__copy h1 {
    color: #0f1720;
    font-size: 31px;
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
    border: 1px solid #e6eaee;
    border-radius: 18px;
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
.lot-slider-field label,
.lot-static-select,
.lot-stat-card span,
.lot-token-card span {
    color: #5d6877;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
}

.lot-static-select {
    align-items: center;
    background: #ffffff;
    border: 1px solid #d7dfe5;
    border-radius: 8px;
    color: #202a35;
    display: flex;
    font-size: 15px;
    justify-content: space-between;
    margin-top: 8px;
    padding: 16px 14px;
}

.lot-static-select__chevron {
    color: #55606d;
    font-size: 16px;
    font-weight: 700;
}

.lot-stat-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 14px 16px;
}

.lot-stat-card strong {
    color: #111a23;
    display: block;
    font-size: 31px;
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

.lot-source-meta {
    color: #5f6a77;
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
    font-size: 12px;
    margin-top: 18px;
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
    font-size: 34px;
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

.lot-large-input,
.lot-field input,
.lot-field select {
    appearance: none;
    background: #f3f5f7;
    border: 1px solid #e2e8ee;
    border-radius: 8px;
    color: #18222c;
    font-size: 15px;
    font-weight: 600;
    outline: none;
    width: 100%;
}

.lot-large-input {
    font-size: 22px;
    height: 86px;
    margin-top: 10px;
    padding: 0 18px;
}

.lot-allocation-card__left p {
    color: #89939f;
    font-size: 11px;
    font-style: italic;
    margin: 10px 0 0;
}

.lot-allocation-card__summary {
    border-left: 1px solid #e6eaee;
    display: grid;
    gap: 14px;
    padding-left: 28px;
}

.lot-summary-row {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.lot-summary-row span {
    color: #5d6877;
    font-size: 14px;
}

.lot-summary-row strong {
    color: #18232f;
    font-size: 24px;
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
    display: grid;
    gap: 28px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    padding-top: 22px;
}

.lot-form-columns--spacious {
    padding-bottom: 12px;
}

.lot-column {
    display: grid;
    gap: 16px;
}

.lot-field-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.lot-field-grid--single {
    grid-template-columns: 1fr;
}

.lot-field {
    display: grid;
    gap: 7px;
}

.lot-field--wide {
    grid-column: 1 / -1;
}

.lot-field input,
.lot-field select {
    background: #ffffff;
    height: 46px;
    padding: 0 14px;
}

.lot-field input[readonly] {
    background: #f7f8fa;
    color: #52606d;
}

.lot-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.lot-choice-chip {
    background: #f3f4f6;
    border: 1px solid #e1e6eb;
    border-radius: 8px;
    color: #3e4956;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    padding: 12px 18px;
}

.lot-choice-chip.is-selected {
    background: #f7d8bd;
    border-color: #f7d8bd;
    color: #5b3a11;
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

.lot-slider-field input[type='range'] {
    accent-color: #0f5c3a;
    width: 100%;
}

.lot-bottom-row {
    border-top: 1px solid #e1e6eb;
    display: grid;
    gap: 24px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    padding-top: 24px;
}

.lot-bottom-card {
    padding: 18px;
}

.lot-token-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 18px;
}

.lot-token-card {
    background: #f4f6f8;
    border-radius: 10px;
    display: grid;
    gap: 6px;
    padding: 14px;
}

.lot-token-card strong {
    color: #13212f;
    font-size: 14px;
}

.lot-switch {
    align-items: center;
    cursor: pointer;
    display: inline-flex;
}

.lot-switch input {
    display: none;
}

.lot-switch__track {
    background: #d6dde4;
    border-radius: 999px;
    height: 26px;
    position: relative;
    transition: 0.2s ease;
    width: 46px;
}

.lot-switch__track::after {
    background: #ffffff;
    border-radius: 50%;
    box-shadow: 0 3px 10px rgba(20, 38, 55, 0.18);
    content: '';
    height: 20px;
    left: 3px;
    position: absolute;
    top: 3px;
    transition: 0.2s ease;
    width: 20px;
}

.lot-switch input:checked + .lot-switch__track {
    background: #0f5c3a;
}

.lot-switch input:checked + .lot-switch__track::after {
    transform: translateX(20px);
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
    border-radius: 10px;
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
    .lot-allocation-card,
    .lot-form-columns,
    .lot-bottom-row,
    .lot-token-grid,
    .lot-mini-news-grid,
    .lot-recommendation__actions {
        grid-template-columns: 1fr;
    }

    .lot-allocation-card__summary {
        border-left: 0;
        border-top: 1px solid #e6eaee;
        padding-left: 0;
        padding-top: 18px;
    }

    .lot-content-section__title {
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
