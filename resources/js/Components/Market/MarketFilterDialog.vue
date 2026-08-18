<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Filter, Close, Search, RefreshLeft } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
        type: '',
        origin: '',
        process: '',
        min_price: null,
        max_price: null,
        min_quality: null,
    };
}

const form = ref(emptyForm());

const optionsLoaded = ref(false);
const loadingOptions = ref(false);
const options = ref({ types: [], origins: [], processes: [] });

function loadOptions() {
    if (optionsLoaded.value || loadingOptions.value) return;

    loadingOptions.value = true;
    window.axios.get(route('market.filter.options'))
        .then(({ data }) => {
            options.value = data;
            optionsLoaded.value = true;
        })
        .finally(() => { loadingOptions.value = false; });
}

watch(() => props.modelValue, (open) => {
    if (open) loadOptions();
});

function resetForm() {
    form.value = emptyForm();
}

const submitting = ref(false);

function applyFilters() {
    submitting.value = true;

    const payload = Object.fromEntries(
        Object.entries(form.value).filter(([, value]) => value !== '' && value !== null),
    );

    router.get(route('market.filter'), payload, {
        onFinish: () => {
            submitting.value = false;
            dialogVisible.value = false;
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="440px"
        destroy-on-close
        align-center
        :show-close="false"
        class="mfd-modal"
    >
        <template #header>
            <div class="mfd-modal__head">
                <div class="mfd-modal__head-icon">
                    <el-icon :size="18"><Filter /></el-icon>
                </div>
                <div class="mfd-modal__head-text">
                    <div class="mfd-modal__eyebrow">Market</div>
                    <div class="mfd-modal__title">Filter Coffee</div>
                </div>
                <button type="button" class="mfd-modal__close" aria-label="Close" @click="dialogVisible = false">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="mfd-modal__body">
            <div class="mfd-field">
                <label class="mfd-field__label">Coffee Type</label>
                <el-select v-model="form.type" placeholder="Any type" clearable class="mfd-input" :loading="loadingOptions">
                    <el-option v-for="t in options.types" :key="t" :label="t" :value="t" />
                </el-select>
            </div>

            <div class="mfd-field">
                <label class="mfd-field__label">Origin</label>
                <el-select v-model="form.origin" placeholder="Any origin" clearable class="mfd-input" :loading="loadingOptions">
                    <el-option v-for="o in options.origins" :key="o" :label="o" :value="o" />
                </el-select>
            </div>

            <div class="mfd-field">
                <label class="mfd-field__label">Process</label>
                <el-select v-model="form.process" placeholder="Any process" clearable class="mfd-input" :loading="loadingOptions">
                    <el-option v-for="p in options.processes" :key="p" :label="p" :value="p" />
                </el-select>
            </div>

            <div class="mfd-field-row">
                <div class="mfd-field">
                    <label class="mfd-field__label">Min Price ($/kg)</label>
                    <el-input-number v-model="form.min_price" :min="0" :controls="false" class="mfd-input" placeholder="0" />
                </div>
                <div class="mfd-field">
                    <label class="mfd-field__label">Max Price ($/kg)</label>
                    <el-input-number v-model="form.max_price" :min="0" :controls="false" class="mfd-input" placeholder="Any" />
                </div>
            </div>

            <div class="mfd-field">
                <label class="mfd-field__label">Min Quality Score</label>
                <el-input-number v-model="form.min_quality" :min="0" :max="100" :controls="false" class="mfd-input" placeholder="Any" />
            </div>
        </div>

        <template #footer>
            <div class="mfd-modal__footer">
                <button type="button" class="mfd-btn-outline" @click="resetForm">
                    <el-icon><RefreshLeft /></el-icon> Reset
                </button>
                <button type="button" class="mfd-btn-primary" :disabled="submitting" @click="applyFilters">
                    <el-icon v-if="!submitting"><Search /></el-icon>
                    {{ submitting ? 'Filtering…' : 'Apply Filters' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so it never carries this SFC's
   scope attribute — a scoped (or :deep()) selector can never reach it.
   Class names are specific enough to avoid collisions. */
.el-dialog.mfd-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

.el-dialog.mfd-modal .el-dialog__header {
    padding: 0;
    margin: 0;
}

.el-dialog.mfd-modal .el-dialog__body {
    padding: 0;
}

.el-dialog.mfd-modal .el-dialog__footer {
    padding: 0;
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.mfd-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.mfd-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.mfd-modal__head-text {
    flex: 1;
    min-width: 0;
}

.mfd-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.mfd-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.mfd-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}

.mfd-modal__close:hover {
    background: #e5e7eb;
    color: #111827;
}

.mfd-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.mfd-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
    min-width: 0;
}

.mfd-field-row {
    display: flex;
    gap: 14px;
}

.mfd-field__label {
    font-size: 0.9375rem;
    font-weight: 400;
    color: #374151;
}

.mfd-input {
    width: 100%;
}

.mfd-input :deep(.el-select__wrapper),
.mfd-input :deep(.el-input__wrapper) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.mfd-input :deep(.el-select__wrapper:hover),
.mfd-input :deep(.el-input__wrapper:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.mfd-input :deep(.el-select__wrapper.is-focused),
.mfd-input :deep(.el-input__wrapper.is-focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.mfd-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.mfd-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.mfd-btn-primary:hover { opacity: 0.9; }
.mfd-btn-primary:disabled { opacity: 0.6; cursor: default; }

.mfd-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.mfd-btn-outline:hover { background: #f8fafc; }
</style>
