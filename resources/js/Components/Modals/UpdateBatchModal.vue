<script setup>
import { computed, watch } from 'vue';
import { ElNotification } from 'element-plus';
import { Close, Document, Files, Operation, WarningFilled } from '@element-plus/icons-vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    batch: {
        type: Object,
        required: true,
    },
    currencyOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'success']);

const processingMethods = ['Washed', 'Natural', 'Honey', 'Anaerobic', 'Semi-washed'];
const dryingMethods = ['Raised beds', 'Patio', 'Mechanical dryer', 'Greenhouse'];
const millingStatuses = ['Pending', 'In milling', 'Milled', 'Ready for grading'];

const form = useForm({
    batch_number: '',
    variety: '',
    warehouse_location: '',
    quantity_bags: '',
    net_weight_kg: '',
    price: '',
    currency: 'USD',
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

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const hydrateForm = () => {
    form.defaults({
        batch_number: props.batch.batch_number ?? '',
        variety: props.batch.variety ?? '',
        warehouse_location: props.batch.warehouse_location ?? '',
        quantity_bags: props.batch.quantity_bags ?? '',
        net_weight_kg: props.batch.net_weight_kg ?? '',
        price: props.batch.price ?? '',
        currency: props.batch.currency ?? 'USD',
        moisture_content: props.batch.moisture_content ?? '',
        processing_date: props.batch.processing_date ?? '',
        processing_method: props.batch.processing_method ?? '',
        drying_method: props.batch.drying_method ?? '',
        drying_duration: props.batch.drying_duration ?? '',
        milling_status: props.batch.milling_status ?? '',
        screen_size: props.batch.screen_size ?? '',
        defect_count: props.batch.defect_count ?? '',
        cup_score: props.batch.cup_score ?? '',
        notes: props.batch.notes ?? '',
    });

    form.reset();
    form.clearErrors();
};

watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            hydrateForm();
        }
    },
);

watch(
    () => props.batch,
    () => {
        if (props.modelValue) {
            hydrateForm();
        }
    },
);

const closeDialog = () => {
    dialogVisible.value = false;
};

const submit = () => {
    form.patch(route('batch.update', props.batch.id), {
        preserveScroll: 'errors',
        onSuccess: () => {
            ElNotification({
                title: 'Batch Updated',
                message: `Batch ${props.batch.batch_number || `#${props.batch.id}`} was updated successfully.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
            emit('success');
            closeDialog();
        },
        onError: () => {
            dialogVisible.value = true;
        },
    });
};
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(640px, calc(100vw - 2rem))"
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="afc-modal"
    >
        <template #header>
            <div class="afc-modal__head">
                <div class="afc-modal__head-icon">
                    <el-icon :size="18"><Files /></el-icon>
                </div>
                <div class="afc-modal__head-text">
                    <div class="afc-modal__eyebrow">Batch Profile</div>
                    <div class="afc-modal__title">Edit Batch Data</div>
                </div>
                <button type="button" class="afc-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="afc-modal__body">
            <div class="afc-section">
                <h3 class="afc-section__title"><el-icon><Files /></el-icon> Batch Details</h3>
                <div class="afc-grid">
                    <div class="afc-field afc-field--span2">
                        <label class="afc-field__label">Variety</label>
                        <el-input v-model="form.variety" placeholder="e.g. Bourbon, Geisha, SL-28" class="afc-input" :class="{ 'afc-input--error': form.errors.variety }" />
                        <span v-if="form.errors.variety" class="afc-field__error">{{ form.errors.variety }}</span>
                    </div>

                    <div class="afc-field afc-field--span2">
                        <label class="afc-field__label">Warehouse Location</label>
                        <el-input v-model="form.warehouse_location" placeholder="Warehouse or collection point" class="afc-input" :class="{ 'afc-input--error': form.errors.warehouse_location }" />
                        <span v-if="form.errors.warehouse_location" class="afc-field__error">{{ form.errors.warehouse_location }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Processing Date</label>
                        <el-date-picker v-model="form.processing_date" type="date" value-format="YYYY-MM-DD" placeholder="Select date" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.processing_date }" />
                        <span v-if="form.errors.processing_date" class="afc-field__error">{{ form.errors.processing_date }}</span>
                    </div>
                </div>
            </div>

            <div class="afc-section">
                <h3 class="afc-section__title"><el-icon><Operation /></el-icon> Processing</h3>
                <div class="afc-grid">
                    <div class="afc-field">
                        <label class="afc-field__label">Quantity (bags)</label>
                        <el-input-number v-model="form.quantity_bags" :min="1" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.quantity_bags }" />
                        <span v-if="form.errors.quantity_bags" class="afc-field__error">{{ form.errors.quantity_bags }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Net Weight (kg)</label>
                        <el-input-number v-model="form.net_weight_kg" :min="0" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.net_weight_kg }" />
                        <span v-if="form.errors.net_weight_kg" class="afc-field__error">{{ form.errors.net_weight_kg }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Price</label>
                        <el-input-number v-model="form.price" :min="0" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.price }" />
                        <span v-if="form.errors.price" class="afc-field__error">{{ form.errors.price }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Currency</label>
                        <el-select v-model="form.currency" filterable placeholder="Select currency" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.currency }">
                            <el-option v-for="option in currencyOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.currency" class="afc-field__error">{{ form.errors.currency }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Processing Method</label>
                        <el-select v-model="form.processing_method" clearable filterable placeholder="Select method" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.processing_method }">
                            <el-option v-for="method in processingMethods" :key="method" :label="method" :value="method" />
                        </el-select>
                        <span v-if="form.errors.processing_method" class="afc-field__error">{{ form.errors.processing_method }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Drying Method</label>
                        <el-select v-model="form.drying_method" clearable filterable placeholder="Select drying method" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.drying_method }">
                            <el-option v-for="method in dryingMethods" :key="method" :label="method" :value="method" />
                        </el-select>
                        <span v-if="form.errors.drying_method" class="afc-field__error">{{ form.errors.drying_method }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Drying Duration (days)</label>
                        <el-input-number v-model="form.drying_duration" :min="0" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.drying_duration }" />
                        <span v-if="form.errors.drying_duration" class="afc-field__error">{{ form.errors.drying_duration }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Milling Status</label>
                        <el-select v-model="form.milling_status" clearable filterable placeholder="Select status" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.milling_status }">
                            <el-option v-for="status in millingStatuses" :key="status" :label="status" :value="status" />
                        </el-select>
                        <span v-if="form.errors.milling_status" class="afc-field__error">{{ form.errors.milling_status }}</span>
                    </div>
                </div>
            </div>

            <div class="afc-section">
                <h3 class="afc-section__title"><el-icon><WarningFilled /></el-icon> Quality Assessment</h3>
                <div class="afc-grid">
                    <div class="afc-field">
                        <label class="afc-field__label">Moisture Content <small>(optional)</small></label>
                        <el-input-number v-model="form.moisture_content" :min="0" :max="100" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.moisture_content }" />
                        <span v-if="form.errors.moisture_content" class="afc-field__error">{{ form.errors.moisture_content }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Screen Size <small>(optional)</small></label>
                        <el-input v-model="form.screen_size" placeholder="e.g. 16/18" class="afc-input" :class="{ 'afc-input--error': form.errors.screen_size }" />
                        <span v-if="form.errors.screen_size" class="afc-field__error">{{ form.errors.screen_size }}</span>
                    </div>

                    <div class="afc-field">
                        <label class="afc-field__label">Defect Count</label>
                        <el-input-number v-model="form.defect_count" :min="0" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.defect_count }" />
                        <span v-if="form.errors.defect_count" class="afc-field__error">{{ form.errors.defect_count }}</span>
                    </div>

                    <div class="afc-field afc-field--span2">
                        <label class="afc-field__label">Cup Score</label>
                        <el-input-number v-model="form.cup_score" :min="0" :max="100" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.cup_score }" />
                        <span v-if="form.errors.cup_score" class="afc-field__error">{{ form.errors.cup_score }}</span>
                    </div>
                </div>
            </div>

            <div class="afc-section">
                <h3 class="afc-section__title"><el-icon><Document /></el-icon> Notes</h3>
                <div class="afc-grid">
                    <div class="afc-field afc-field--span2">
                        <label class="afc-field__label">Notes <small>(optional)</small></label>
                        <el-input v-model="form.notes" type="textarea" :rows="2" placeholder="Add warehouse, quality, or traceability notes" class="afc-input" :class="{ 'afc-input--error': form.errors.notes }" />
                        <span v-if="form.errors.notes" class="afc-field__error">{{ form.errors.notes }}</span>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="afc-modal__footer">
                <button type="button" class="afc-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="afc-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* ── App theme (see reference_ui_md_design_system memory) ─────────────── */
.el-dialog.afc-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.afc-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.afc-modal .el-dialog__body { padding: 0; }
.el-dialog.afc-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.afc-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.afc-modal__head-icon {
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
.afc-modal__head-text { flex: 1; min-width: 0; }
.afc-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.afc-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.afc-modal__close {
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
.afc-modal__close:hover { background: #E5E7EB; color: #121516; }

.afc-modal__body { padding: 22px 24px 8px; max-height: 72vh; overflow-y: auto; }

.afc-section { margin-bottom: 22px; }
.afc-section:last-child { margin-bottom: 0; }
.afc-section__title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6F7677;
    margin: 0 0 14px;
    padding-top: 18px;
    border-top: 1px solid #E5E7EB;
}
.afc-section:first-child .afc-section__title { padding-top: 0; border-top: none; }
.afc-section__title .el-icon { font-size: 13px; color: #6F7677; }

.afc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.afc-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.afc-field--span2 { grid-column: span 2; }

.afc-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.afc-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.afc-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.afc-input { width: 100%; }
.afc-input :deep(.el-input__wrapper),
.afc-input :deep(.el-select__wrapper),
.afc-input :deep(.el-textarea__inner) { border-radius: 6px; }
.afc-input--error :deep(.el-input__wrapper),
.afc-input--error :deep(.el-select__wrapper),
.afc-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.afc-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.afc-btn-primary {
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
.afc-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.afc-btn-primary:disabled { opacity: 0.5; cursor: default; }
.afc-btn-outline {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 16px;
    background: #fff;
    border: 1px solid #E5E7EB;
    color: #121516;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
}
.afc-btn-outline:hover { background: #F5F6F7; }

@media (max-width: 640px) {
    .afc-grid { grid-template-columns: 1fr; }
}
</style>
