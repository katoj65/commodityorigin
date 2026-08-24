<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Box, Close } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    processOptions: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
    dryingMethodOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    millingOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
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
    };
}

const form = useForm(emptyForm());

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();
});

function disableFutureDates(date) {
    return date.getTime() > Date.now();
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.post(route('batch.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({ title: 'Batch Created', message: 'The new batch was added.', type: 'success', duration: 3200, offset: 84 });
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(640px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="abm-modal"
    >
        <template #header>
            <div class="abm-modal__head">
                <div class="abm-modal__head-icon">
                    <el-icon :size="18"><Box /></el-icon>
                </div>
                <div class="abm-modal__head-text">
                    <div class="abm-modal__eyebrow">Inventory</div>
                    <div class="abm-modal__title">New Batch</div>
                </div>
                <button type="button" class="abm-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="abm-modal__body">
            <div class="abm-grid">
                <div class="abm-field abm-field--span2">
                    <label class="abm-field__label">Variety</label>
                    <el-select v-model="form.variety" placeholder="Select variety" filterable class="abm-input w-100" :class="{ 'abm-input--error': form.errors.variety }">
                        <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.variety" class="abm-field__error">{{ form.errors.variety }}</span>
                </div>
                <div class="abm-field abm-field--span2">
                    <label class="abm-field__label">Warehouse Location</label>
                    <el-input v-model="form.warehouse_location" placeholder="e.g. Kampala Central Warehouse" class="abm-input" :class="{ 'abm-input--error': form.errors.warehouse_location }" />
                    <span v-if="form.errors.warehouse_location" class="abm-field__error">{{ form.errors.warehouse_location }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Quantity (bags)</label>
                    <el-input-number v-model="form.quantity_bags" :min="1" class="abm-input w-100" :class="{ 'abm-input--error': form.errors.quantity_bags }" />
                    <span v-if="form.errors.quantity_bags" class="abm-field__error">{{ form.errors.quantity_bags }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Net Weight (kg)</label>
                    <el-input-number v-model="form.net_weight_kg" :min="1" :precision="2" class="abm-input w-100" :class="{ 'abm-input--error': form.errors.net_weight_kg }" />
                    <span v-if="form.errors.net_weight_kg" class="abm-field__error">{{ form.errors.net_weight_kg }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Price</label>
                    <el-input-number v-model="form.price" :min="0" :precision="2" class="abm-input w-100" :class="{ 'abm-input--error': form.errors.price }" />
                    <span v-if="form.errors.price" class="abm-field__error">{{ form.errors.price }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Currency</label>
                    <el-select v-model="form.currency" placeholder="Select currency" filterable class="abm-input w-100" :class="{ 'abm-input--error': form.errors.currency }">
                        <el-option v-for="option in currencyOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.currency" class="abm-field__error">{{ form.errors.currency }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Moisture Content % <small>(optional)</small></label>
                    <el-input-number v-model="form.moisture_content" :min="0" :max="100" :precision="2" class="abm-input w-100" :class="{ 'abm-input--error': form.errors.moisture_content }" />
                    <span v-if="form.errors.moisture_content" class="abm-field__error">{{ form.errors.moisture_content }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Processing Date</label>
                    <el-date-picker v-model="form.processing_date" type="date" value-format="YYYY-MM-DD" placeholder="Select date" :disabled-date="disableFutureDates" class="abm-input w-100" :class="{ 'abm-input--error': form.errors.processing_date }" />
                    <span v-if="form.errors.processing_date" class="abm-field__error">{{ form.errors.processing_date }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Processing Method</label>
                    <el-select v-model="form.processing_method" placeholder="Select processing method" filterable class="abm-input w-100" :class="{ 'abm-input--error': form.errors.processing_method }">
                        <el-option v-for="option in processOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.processing_method" class="abm-field__error">{{ form.errors.processing_method }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Drying Method</label>
                    <el-select v-model="form.drying_method" placeholder="Select drying method" filterable class="abm-input w-100" :class="{ 'abm-input--error': form.errors.drying_method }">
                        <el-option v-for="option in dryingMethodOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.drying_method" class="abm-field__error">{{ form.errors.drying_method }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Drying Duration (days) <small>(optional)</small></label>
                    <el-input-number v-model="form.drying_duration" :min="0" class="abm-input w-100" :class="{ 'abm-input--error': form.errors.drying_duration }" />
                    <span v-if="form.errors.drying_duration" class="abm-field__error">{{ form.errors.drying_duration }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Milling Status <small>(optional)</small></label>
                    <el-select v-model="form.milling_status" placeholder="Select milling status" clearable filterable class="abm-input w-100" :class="{ 'abm-input--error': form.errors.milling_status }">
                        <el-option v-for="option in millingOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.milling_status" class="abm-field__error">{{ form.errors.milling_status }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Screen Size <small>(optional)</small></label>
                    <el-input v-model="form.screen_size" placeholder="e.g. 18" class="abm-input" :class="{ 'abm-input--error': form.errors.screen_size }" />
                    <span v-if="form.errors.screen_size" class="abm-field__error">{{ form.errors.screen_size }}</span>
                </div>
                <div class="abm-field">
                    <label class="abm-field__label">Defect Count <small>(optional)</small></label>
                    <el-input-number v-model="form.defect_count" :min="0" class="abm-input w-100" :class="{ 'abm-input--error': form.errors.defect_count }" />
                    <span v-if="form.errors.defect_count" class="abm-field__error">{{ form.errors.defect_count }}</span>
                </div>
                <div class="abm-field abm-field--span2">
                    <label class="abm-field__label">Cup Score <small>(optional)</small></label>
                    <el-input-number v-model="form.cup_score" :min="0" :max="100" :precision="2" class="abm-input w-100" :class="{ 'abm-input--error': form.errors.cup_score }" />
                    <span v-if="form.errors.cup_score" class="abm-field__error">{{ form.errors.cup_score }}</span>
                </div>
                <div class="abm-field abm-field--span2">
                    <label class="abm-field__label">Notes <small>(optional)</small></label>
                    <el-input v-model="form.notes" type="textarea" :rows="2" class="abm-input" :class="{ 'abm-input--error': form.errors.notes }" />
                    <span v-if="form.errors.notes" class="abm-field__error">{{ form.errors.notes }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="abm-modal__footer">
                <button type="button" class="abm-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="abm-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Batch' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.abm-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.abm-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.abm-modal .el-dialog__body { padding: 0; }
.el-dialog.abm-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.abm-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.abm-modal__head-icon {
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
.abm-modal__head-text { flex: 1; min-width: 0; }
.abm-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.abm-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.abm-modal__close {
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
.abm-modal__close:hover { background: #E5E7EB; color: #121516; }

.abm-modal__body { padding: 22px 24px 8px; max-height: 72vh; overflow-y: auto; }

.abm-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.abm-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.abm-field--span2 { grid-column: span 2; }

.abm-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.abm-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.abm-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.abm-input { width: 100%; }
.abm-input :deep(.el-input__wrapper),
.abm-input :deep(.el-select__wrapper),
.abm-input :deep(.el-textarea__inner) { border-radius: 6px; }
.abm-input--error :deep(.el-input__wrapper),
.abm-input--error :deep(.el-select__wrapper),
.abm-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.abm-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.abm-btn-primary {
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
.abm-btn-primary:hover { opacity: 0.88; }
.abm-btn-primary:disabled { opacity: 0.5; cursor: default; }
.abm-btn-outline {
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
.abm-btn-outline:hover { background: #F5F6F7; }

@media (max-width: 640px) {
    .abm-grid { grid-template-columns: 1fr; }
}
</style>
