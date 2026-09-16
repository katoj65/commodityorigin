<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Box } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    batchId: { type: [Number, String], required: true },
    warehouse: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
        storage_bay: props.warehouse?.storage_bay ?? '',
        date_stored: props.warehouse?.date_stored ?? '',
        quantity_stored_kg: props.warehouse?.quantity_stored_kg ?? '',
        climate_ambient: props.warehouse?.climate_ambient ?? '',
        physical_pallet: props.warehouse?.physical_pallet ?? '',
        packaging_spec: props.warehouse?.packaging_spec ?? '',
    };
}

const form = useForm(emptyForm());

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function isFutureDate(date) {
    return date.getTime() > Date.now();
}

function submit() {
    if (!form.storage_bay || !form.date_stored || !form.quantity_stored_kg) {
        form.setError({
            storage_bay: form.storage_bay ? undefined : 'Storage bay is required.',
            date_stored: form.date_stored ? undefined : 'Date stored is required.',
            quantity_stored_kg: form.quantity_stored_kg ? undefined : 'Quantity stored is required.',
        });
        return;
    }

    form.post(route('batch.warehouse.store', props.batchId), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({ title: 'Storage Record Saved', message: "The batch's warehousing detail was saved.", type: 'success', duration: 3200, offset: 84 });
            // The host page's storage detail is computed once from props at
            // setup — Inertia reuses the same mounted instance on redirect,
            // so a hard reload is needed for it to reflect the new record.
            window.location.reload();
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(520px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="asr-modal"
    >
        <template #header>
            <div class="asr-modal__head">
                <div class="asr-modal__head-icon">
                    <el-icon :size="18"><Box /></el-icon>
                </div>
                <div class="asr-modal__head-text">
                    <div class="asr-modal__eyebrow">Batch</div>
                    <div class="asr-modal__title">Add Storage Record</div>
                </div>
                <button type="button" class="asr-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="asr-modal__body">
            <div class="asr-field">
                <label class="asr-field__label">Storage Bay</label>
                <el-input v-model="form.storage_bay" placeholder="e.g. Depot #Kampala-04, Bay 3B" class="asr-input" :class="{ 'asr-input--error': form.errors.storage_bay }" />
                <span v-if="form.errors.storage_bay" class="asr-field__error">{{ form.errors.storage_bay }}</span>
            </div>

            <div class="asr-row">
                <div class="asr-field">
                    <label class="asr-field__label">Date Stored</label>
                    <el-date-picker v-model="form.date_stored" type="date" value-format="YYYY-MM-DD" placeholder="Select a date" :disabled-date="isFutureDate" class="asr-input w-100" :class="{ 'asr-input--error': form.errors.date_stored }" />
                    <span v-if="form.errors.date_stored" class="asr-field__error">{{ form.errors.date_stored }}</span>
                </div>
                <div class="asr-field">
                    <label class="asr-field__label">Quantity Stored <small>(kg)</small></label>
                    <el-input-number v-model="form.quantity_stored_kg" :min="0.01" :precision="2" controls-position="right" class="asr-input w-100" :class="{ 'asr-input--error': form.errors.quantity_stored_kg }" />
                    <span v-if="form.errors.quantity_stored_kg" class="asr-field__error">{{ form.errors.quantity_stored_kg }}</span>
                </div>
            </div>

            <div class="asr-field">
                <label class="asr-field__label">Climate Ambient <small>(optional)</small></label>
                <el-input v-model="form.climate_ambient" placeholder="e.g. 18°C - 21°C · 58% Relative Humidity" class="asr-input" :class="{ 'asr-input--error': form.errors.climate_ambient }" />
                <span v-if="form.errors.climate_ambient" class="asr-field__error">{{ form.errors.climate_ambient }}</span>
            </div>

            <div class="asr-field">
                <label class="asr-field__label">Physical Pallet <small>(optional)</small></label>
                <el-input v-model="form.physical_pallet" placeholder="e.g. Palletized & Raised (15cm off deck)" class="asr-input" :class="{ 'asr-input--error': form.errors.physical_pallet }" />
                <span v-if="form.errors.physical_pallet" class="asr-field__error">{{ form.errors.physical_pallet }}</span>
            </div>

            <div class="asr-field">
                <label class="asr-field__label">Packaging Spec <small>(optional)</small></label>
                <el-input v-model="form.packaging_spec" placeholder="e.g. GrainPro Hermetic + Food-Grade Jute" class="asr-input" :class="{ 'asr-input--error': form.errors.packaging_spec }" />
                <span v-if="form.errors.packaging_spec" class="asr-field__error">{{ form.errors.packaging_spec }}</span>
            </div>
        </div>

        <template #footer>
            <div class="asr-modal__footer">
                <button type="button" class="asr-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Storage Record' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* ── App theme (see reference_ui_md_design_system memory) ─────────────── */
.el-dialog.asr-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.asr-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.asr-modal .el-dialog__body { padding: 0; }
.el-dialog.asr-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.asr-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.asr-modal__head-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    background: #F1F2F3;
    color: #121516;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.asr-modal__head-text { flex: 1; min-width: 0; }
.asr-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.asr-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.asr-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: #F1F2F3;
    color: #4B5457;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}
.asr-modal__close:hover { background: #E5E7EB; color: #121516; }

.asr-modal__body { padding: 22px 24px; display: flex; flex-direction: column; gap: 16px; }
.asr-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.asr-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.asr-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.asr-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.asr-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.asr-input { width: 100%; }
.w-100 { width: 100%; }
.asr-input :deep(.el-input__wrapper),
.asr-input :deep(.el-input-number),
.asr-input :deep(.el-textarea__inner) { border-radius: 6px; }
.asr-input--error :deep(.el-input__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.asr-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.asr-btn-primary {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 16px;
    background: #000000;
    border: 1px solid transparent;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.asr-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.asr-btn-primary:disabled { opacity: 0.5; cursor: default; }

@media (max-width: 520px) {
    .asr-row { grid-template-columns: 1fr; }
}
</style>
