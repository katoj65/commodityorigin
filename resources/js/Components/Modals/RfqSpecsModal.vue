<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import { Close, Loading } from '@element-plus/icons-vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    specification: { type: Object, default: null },
    cropTypes: { type: Array, default: () => [] },
    grades: { type: Array, default: () => [] },
    incoterms: { type: Array, default: () => [] },
    paymentTerms: { type: Array, default: () => [] },
    destinations: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'saved']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const isEdit = computed(() => Boolean(props.specification?.id));

// Deferred hydration: the dialog shell + "Preparing form…" loader mount
// immediately on open, then the real fields hydrate via @opened.
const contentReady = ref(false);

const form = useForm({
    type: '',
    grade: '',
    target_price: '',
    destination: '',
    payment_terms: '',
    min_weight: '',
    max_weight: '',
    incoterms: '',
});

function populate() {
    form.reset();
    form.clearErrors();

    if (props.specification) {
        form.type = props.specification.type ?? '';
        form.grade = props.specification.grade ?? '';
        form.target_price = props.specification.target_price ?? '';
        form.destination = props.specification.destination ?? '';
        form.payment_terms = props.specification.payment_terms ?? '';
        form.min_weight = props.specification.min_weight ?? '';
        form.max_weight = props.specification.max_weight ?? '';
        form.incoterms = props.specification.incoterms ?? '';
    }
}

function onDialogOpened() {
    populate();
    contentReady.value = true;
}

function submit() {
    form.clearErrors();

    // Every field on this form is now required — mirrors
    // RfqController::validateSpecification(), which was updated to
    // require all settings_rfq_specifications columns rather than
    // treating most of them as optional.
    if (!form.type) form.setError('type', 'Select a coffee type.');
    if (!form.grade) form.setError('grade', 'Select a grade.');
    if (!form.destination) form.setError('destination', 'Select a destination.');
    if (!form.payment_terms) form.setError('payment_terms', 'Select payment terms.');
    if (!form.incoterms) form.setError('incoterms', 'Select an Incoterm.');

    const targetPrice = form.target_price === '' ? null : Number(form.target_price);
    if (targetPrice === null) {
        form.setError('target_price', 'Enter a target price.');
    } else if (Number.isNaN(targetPrice) || targetPrice < 0) {
        form.setError('target_price', 'Enter a valid price of 0 or more.');
    }

    const minWeight = form.min_weight === '' ? null : Number(form.min_weight);
    if (minWeight === null) {
        form.setError('min_weight', 'Enter a minimum weight.');
    } else if (Number.isNaN(minWeight) || minWeight < 0) {
        form.setError('min_weight', 'Enter a valid weight of 0 or more.');
    }

    const maxWeight = form.max_weight === '' ? null : Number(form.max_weight);
    if (maxWeight === null) {
        form.setError('max_weight', 'Enter a maximum weight.');
    } else if (Number.isNaN(maxWeight) || maxWeight < 0) {
        form.setError('max_weight', 'Enter a valid weight of 0 or more.');
    }

    if (minWeight !== null && maxWeight !== null && !form.errors.min_weight && !form.errors.max_weight && minWeight > maxWeight) {
        form.setError('max_weight', 'Max weight must be equal to or greater than min weight.');
    }

    if (Object.keys(form.errors).length) return;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            ElMessage.success(isEdit.value ? 'Specification updated.' : 'Specification saved.');
            emit('saved');
            dialogVisible.value = false;
        },
    };

    if (isEdit.value) {
        form.put(route('rfq.specifications.update', props.specification.id), options);
    } else {
        form.post(route('rfq.specifications.store'), options);
    }
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(600px, calc(100vw - 2rem))"
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="rsm-modal"
        @opened="onDialogOpened"
    >
        <template #header>
            <div class="rsm-head">
                <div class="rsm-head__text">
                    <div class="rsm-eyebrow">Active Sourcing Requisition</div>
                    <div class="rsm-title">{{ isEdit ? 'Edit RFQ Specification' : 'Add RFQ Specification' }}</div>
                </div>
                <button type="button" class="rsm-close" aria-label="Close" @click="dialogVisible = false">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="rsm-body">
            <div v-if="!contentReady" class="rsm-loading">
                <el-icon class="is-loading" :size="22"><Loading /></el-icon>
                <span>Preparing form…</span>
            </div>
            <div v-else class="rsm-grid">
                <label class="rsm-field" :class="{ 'rsm-field--error': form.errors.type }">
                    <span class="rsm-label">Coffee Type</span>
                    <el-select v-model="form.type" placeholder="Select type" class="!w-full" :class="{ 'rsm-input--error': form.errors.type }">
                        <el-option v-for="o in cropTypes" :key="o" :label="o" :value="o" />
                    </el-select>
                    <span v-if="form.errors.type" class="rsm-field__error">{{ form.errors.type }}</span>
                </label>

                <label class="rsm-field" :class="{ 'rsm-field--error': form.errors.grade }">
                    <span class="rsm-label">Grade</span>
                    <el-select v-model="form.grade" placeholder="Select grade" class="!w-full" :class="{ 'rsm-input--error': form.errors.grade }">
                        <el-option v-for="o in grades" :key="o" :label="o" :value="o" />
                    </el-select>
                    <span v-if="form.errors.grade" class="rsm-field__error">{{ form.errors.grade }}</span>
                </label>

                <label class="rsm-field" :class="{ 'rsm-field--error': form.errors.target_price }">
                    <span class="rsm-label">Target Price ($/kg)</span>
                    <el-input v-model="form.target_price" type="number" min="0" step="0.01" placeholder="e.g. 4.10" :class="{ 'rsm-input--error': form.errors.target_price }" />
                    <span v-if="form.errors.target_price" class="rsm-field__error">{{ form.errors.target_price }}</span>
                </label>

                <label class="rsm-field" :class="{ 'rsm-field--error': form.errors.destination }">
                    <span class="rsm-label">Destination</span>
                    <el-select v-model="form.destination" placeholder="Select destination" class="!w-full" clearable :class="{ 'rsm-input--error': form.errors.destination }">
                        <el-option v-for="o in destinations" :key="o" :label="o" :value="o" />
                    </el-select>
                    <span v-if="form.errors.destination" class="rsm-field__error">{{ form.errors.destination }}</span>
                </label>

                <label class="rsm-field rsm-field--span2" :class="{ 'rsm-field--error': form.errors.payment_terms }">
                    <span class="rsm-label">Payment Terms</span>
                    <el-select v-model="form.payment_terms" placeholder="Select payment terms" class="!w-full" clearable :class="{ 'rsm-input--error': form.errors.payment_terms }">
                        <el-option v-for="o in paymentTerms" :key="o" :label="o" :value="o" />
                    </el-select>
                    <span v-if="form.errors.payment_terms" class="rsm-field__error">{{ form.errors.payment_terms }}</span>
                </label>

                <label class="rsm-field" :class="{ 'rsm-field--error': form.errors.min_weight }">
                    <span class="rsm-label">Min Weight (kg)</span>
                    <el-input v-model="form.min_weight" type="number" min="0" step="0.01" placeholder="e.g. 1000" :class="{ 'rsm-input--error': form.errors.min_weight }" />
                    <span v-if="form.errors.min_weight" class="rsm-field__error">{{ form.errors.min_weight }}</span>
                </label>

                <label class="rsm-field" :class="{ 'rsm-field--error': form.errors.max_weight }">
                    <span class="rsm-label">Max Weight (kg)</span>
                    <el-input v-model="form.max_weight" type="number" min="0" step="0.01" placeholder="e.g. 5000" :class="{ 'rsm-input--error': form.errors.max_weight }" />
                    <span v-if="form.errors.max_weight" class="rsm-field__error">{{ form.errors.max_weight }}</span>
                </label>

                <label class="rsm-field rsm-field--span2" :class="{ 'rsm-field--error': form.errors.incoterms }">
                    <span class="rsm-label">Incoterms</span>
                    <el-select v-model="form.incoterms" placeholder="Select Incoterm" class="!w-full" clearable :class="{ 'rsm-input--error': form.errors.incoterms }">
                        <el-option v-for="o in incoterms" :key="o" :label="o" :value="o" />
                    </el-select>
                    <span v-if="form.errors.incoterms" class="rsm-field__error">{{ form.errors.incoterms }}</span>
                </label>
            </div>
        </div>

        <template #footer>
            <div class="rsm-footer">
                <SubmitButton native-type="button" :loading="form.processing" :full-width="false" class="min-w-[170px]" @click="submit">
                    {{ form.processing ? 'Saving…' : (isEdit ? 'Save Changes' : 'Save Specification') }}
                </SubmitButton>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
.rsm-modal {
    border-radius: 16px;
}
.rsm-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}
.rsm-head__text { flex: 1; min-width: 0; }
.rsm-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--dp-on-surface-variant, #6b7280);
    margin-bottom: 2px;
}
.rsm-title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: var(--dp-on-surface, #111827);
    letter-spacing: -0.01em;
}
.rsm-close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: var(--dp-surface-container-high, #f3f4f6);
    color: var(--dp-on-surface-variant, #6b7280);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
}
.rsm-close:hover { background: var(--dp-surface-container-highest, #e5e7eb); color: var(--dp-on-surface, #111827); }

.rsm-body { padding: 8px 4px 0; }
.rsm-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 48px 20px;
    color: #6b7280;
    font-size: 13px;
    font-weight: 600;
}
.rsm-loading .is-loading { animation: rsm-spin 1s linear infinite; color: #111827; }
@keyframes rsm-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.rsm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 16px; }
.rsm-field { display: flex; flex-direction: column; gap: 5px; }
.rsm-field--span2 { grid-column: span 2; }
.rsm-label { font-size: 12px; font-weight: 700; color: var(--dp-on-surface, #111827); }
.rsm-field__error { font-size: 12px; font-weight: 500; color: var(--dp-error, #F85149); line-height: 1.4; }
.rsm-input--error :deep(.el-input__wrapper),
.rsm-input--error :deep(.el-select__wrapper) { box-shadow: 0 0 0 1.5px var(--dp-error, #F85149) inset !important; }
.rsm-footer { display: flex; justify-content: flex-end; padding-top: 4px; }

@media (max-width: 560px) {
    .rsm-grid { grid-template-columns: 1fr; }
    .rsm-field--span2 { grid-column: span 1; }
}
</style>


