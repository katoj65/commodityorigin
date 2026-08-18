<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Box, Close, Plus } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    farmOptions: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
    pickMethodOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const monthOptions = [
    { value: '01', label: 'January' }, { value: '02', label: 'February' }, { value: '03', label: 'March' },
    { value: '04', label: 'April' }, { value: '05', label: 'May' }, { value: '06', label: 'June' },
    { value: '07', label: 'July' }, { value: '08', label: 'August' }, { value: '09', label: 'September' },
    { value: '10', label: 'October' }, { value: '11', label: 'November' }, { value: '12', label: 'December' },
];

const currentYear = new Date().getFullYear();
const currentMonth = String(new Date().getMonth() + 1).padStart(2, '0');
const yearOptions = Array.from({ length: 30 }, (_, index) => String(currentYear - index));

function emptyForm() {
    return {
        farm_id: '',
        variety: '',
        date_planted_month: '',
        date_planted_year: '',
        harvest_date: '',
        harvest_season: '',
        pick_method: '',
        price: '',
        weight: '',
        ripeness_percentage: '',
        foreign_matter_present: false,
        pest_damage: false,
        disease_signs: false,
        visible_defects: false,
    };
}

const form = useForm(emptyForm());

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();

    if (props.farmOptions.length === 1) {
        form.farm_id = String(props.farmOptions[0].id);
    }
});

const availableMonthOptions = computed(() => {
    if (form.date_planted_year === String(currentYear)) {
        return monthOptions.filter((option) => option.value <= currentMonth);
    }
    return monthOptions;
});

function disableFutureDates(date) {
    const today = new Date();
    today.setHours(23, 59, 59, 999);
    return date.getTime() > today.getTime();
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (!form.farm_id) {
        form.setError('farm_id', 'Please select a farm.');
        return;
    }

    const farmId = form.farm_id;

    form
        .transform((data) => ({
            variety: data.variety,
            date_planted: data.date_planted_year && data.date_planted_month
                ? `${data.date_planted_year}-${data.date_planted_month}-01`
                : '',
            harvest_date: data.harvest_date,
            harvest_season: data.harvest_season,
            pick_method: data.pick_method,
            price: data.price,
            weight: data.weight,
            ripeness_percentage: data.ripeness_percentage,
            foreign_matter_present: data.foreign_matter_present,
            pest_damage: data.pest_damage,
            disease_signs: data.disease_signs,
            visible_defects: data.visible_defects,
        }))
        .post(route('farm.harvests.store', farmId), {
            preserveScroll: true,
            onSuccess: () => {
                closeDialog();
                ElNotification({
                    title: 'Harvest Added',
                    message: 'Harvest recorded successfully.',
                    type: 'success',
                    duration: 3200,
                    offset: 84,
                });
            },
        });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(760px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="afh-modal"
    >
        <template #header>
            <div class="afh-modal__head">
                <div class="afh-modal__head-icon">
                    <el-icon :size="18"><Box /></el-icon>
                </div>
                <div class="afh-modal__head-text">
                    <div class="afh-modal__eyebrow">Farm Workspace</div>
                    <div class="afh-modal__title">Add Harvest</div>
                </div>
                <button type="button" class="afh-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="afh-modal__body">
            <div class="afh-grid">
                <div class="afh-field afh-field--span2">
                    <label class="afh-field__label">Farm</label>
                    <el-select v-model="form.farm_id" filterable placeholder="Select farm" class="afh-input" :class="{ 'afh-input--error': form.errors.farm_id }">
                        <el-option
                            v-for="farm in farmOptions"
                            :key="farm.id"
                            :label="farm.location ? `${farm.name} — ${farm.location}` : farm.name"
                            :value="String(farm.id)"
                        />
                    </el-select>
                    <span v-if="form.errors.farm_id" class="afh-field__error">{{ form.errors.farm_id }}</span>
                </div>

                <div class="afh-field">
                    <label class="afh-field__label">Variety</label>
                    <el-select v-model="form.variety" placeholder="Select variety" class="afh-input" :class="{ 'afh-input--error': form.errors.variety }">
                        <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.variety" class="afh-field__error">{{ form.errors.variety }}</span>
                </div>

                <div class="afh-field">
                    <label class="afh-field__label">Date Planted</label>
                    <div class="afh-field-row">
                        <el-select v-model="form.date_planted_month" placeholder="Month" class="afh-input">
                            <el-option v-for="option in availableMonthOptions" :key="option.value" :label="option.label" :value="option.value" />
                        </el-select>
                        <el-select v-model="form.date_planted_year" placeholder="Year" class="afh-input">
                            <el-option v-for="option in yearOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                    </div>
                    <span v-if="form.errors.date_planted" class="afh-field__error">{{ form.errors.date_planted }}</span>
                </div>

                <div class="afh-field">
                    <label class="afh-field__label">Harvest Date</label>
                    <el-date-picker
                        v-model="form.harvest_date"
                        type="date"
                        value-format="YYYY-MM-DD"
                        placeholder="Select harvest date"
                        class="afh-input"
                        :class="{ 'afh-input--error': form.errors.harvest_date }"
                        :disabled-date="disableFutureDates"
                        style="width: 100%"
                        popper-class="afh-date-popper"
                    />
                    <span v-if="form.errors.harvest_date" class="afh-field__error mt-4">{{ form.errors.harvest_date }}</span>
                </div>

                <div class="afh-field">
                    <label class="afh-field__label">Harvest Season</label>
                    <el-select v-model="form.harvest_season" placeholder="Select season" class="afh-input" :class="{ 'afh-input--error': form.errors.harvest_season }">
                        <el-option v-for="option in harvestSeasonOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.harvest_season" class="afh-field__error">{{ form.errors.harvest_season }}</span>
                </div>

                <div class="afh-field">
                    <label class="afh-field__label">Pick Method</label>
                    <el-select v-model="form.pick_method" placeholder="Select pick method" class="afh-input" :class="{ 'afh-input--error': form.errors.pick_method }">
                        <el-option v-for="option in pickMethodOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.pick_method" class="afh-field__error">{{ form.errors.pick_method }}</span>
                </div>

                <div class="afh-field">
                    <label class="afh-field__label">Weight (kg)</label>
                    <el-input v-model="form.weight" type="number" min="0.01" step="0.01" placeholder="e.g. 1250" class="afh-input" :class="{ 'afh-input--error': form.errors.weight }" />
                    <span v-if="form.errors.weight" class="afh-field__error">{{ form.errors.weight }}</span>
                </div>

                <div class="afh-field">
                    <label class="afh-field__label">Ripeness</label>
                    <el-input v-model="form.ripeness_percentage" type="number" min="0" max="100" step="0.01" placeholder="e.g. 92.50" class="afh-input" :class="{ 'afh-input--error': form.errors.ripeness_percentage }">
                        <template #suffix>%</template>
                    </el-input>
                    <span v-if="form.errors.ripeness_percentage" class="afh-field__error">{{ form.errors.ripeness_percentage }}</span>
                </div>

                <div class="afh-field">
                    <label class="afh-field__label">Price (per kg)</label>
                    <el-input v-model="form.price" type="number" min="0.01" step="0.01" placeholder="e.g. 4.85" class="afh-input" :class="{ 'afh-input--error': form.errors.price }">
                        <template #prefix>Shs.</template>
                    </el-input>
                    <span v-if="form.errors.price" class="afh-field__error">{{ form.errors.price }}</span>
                </div>
            </div>

            <div class="afh-quality">
                <div class="afh-quality__title">Quality Indicators</div>
                <p class="afh-quality__hint">Capture visible intake issues before the batch moves into quality review.</p>

                <div class="afh-quality-grid">
                    <label class="afh-toggle-card">
                        <span>
                            <strong>Foreign matter present</strong>
                            <small>Stones, sticks, husk residue, or other unwanted material in the lot.</small>
                        </span>
                        <el-switch v-model="form.foreign_matter_present" />
                    </label>

                    <label class="afh-toggle-card">
                        <span>
                            <strong>Pest damage</strong>
                            <small>Signs of insect attack or cherry damage visible during intake inspection.</small>
                        </span>
                        <el-switch v-model="form.pest_damage" />
                    </label>

                    <label class="afh-toggle-card">
                        <span>
                            <strong>Disease signs</strong>
                            <small>Mold, rot, discoloration, or other symptoms that affect harvest quality.</small>
                        </span>
                        <el-switch v-model="form.disease_signs" />
                    </label>

                    <label class="afh-toggle-card">
                        <span>
                            <strong>Visible defects</strong>
                            <small>Broken, underripe, or visibly defective cherries observed at intake.</small>
                        </span>
                        <el-switch v-model="form.visible_defects" />
                    </label>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="afh-modal__footer">
                <button type="button" class="afh-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="afh-btn-primary" :disabled="form.processing" @click="submit">
                    <el-icon v-if="!form.processing"><Plus /></el-icon>
                    {{ form.processing ? 'Saving…' : 'Save Harvest' }}
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
.el-dialog.afh-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

.el-dialog.afh-modal .el-dialog__header {
    padding: 0;
    margin: 0;
}

.el-dialog.afh-modal .el-dialog__body {
    padding: 0;
}

.el-dialog.afh-modal .el-dialog__footer {
    padding: 0;
}

.el-popper.afh-date-popper {
    margin-top: 8px;
    box-shadow: 0 12px 28px rgba(0, 20, 15, 0.16);
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.afh-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.afh-modal__head-icon {
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

.afh-modal__head-text { flex: 1; min-width: 0; }

.afh-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.afh-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.afh-modal__close {
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

.afh-modal__close:hover { background: #e5e7eb; color: #111827; }

.afh-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
}

.afh-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.afh-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.afh-field--span2 { grid-column: span 2; }

.afh-field-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.afh-field__label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #111827;
}

.afh-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.afh-input { width: 100%; }

.afh-input--error :deep(.el-input__wrapper),
.afh-input--error :deep(.el-select__wrapper) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.afh-input :deep(.el-input__prefix) { margin-right: 4px; color: #6b7280; }
.afh-input :deep(.el-input__suffix) { margin-left: 4px; color: #6b7280; }

.afh-quality {
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px solid #f3f4f6;
}

.afh-quality__title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 4px;
}

.afh-quality__hint {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1.5;
    margin: 0 0 12px;
}

.afh-quality-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.afh-toggle-card {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 14px;
    border: 1px solid #eef2f5;
    border-radius: 10px;
    background: #fcfdfd;
}

.afh-toggle-card span {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.afh-toggle-card strong { font-size: 0.8125rem; font-weight: 700; color: #111827; }
.afh-toggle-card small { font-size: 0.75rem; color: #6b7280; line-height: 1.5; }

.afh-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.afh-btn-primary {
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

.afh-btn-primary:hover { opacity: 0.9; }
.afh-btn-primary:disabled { opacity: 0.6; cursor: default; }

.afh-btn-outline {
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

.afh-btn-outline:hover { background: #f8fafc; }

@media (max-width: 640px) {
    .afh-grid, .afh-field-row, .afh-quality-grid { grid-template-columns: 1fr; }
    .afh-field--span2 { grid-column: span 1; }
}
</style>
