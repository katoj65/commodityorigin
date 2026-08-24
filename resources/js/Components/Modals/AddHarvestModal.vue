<script setup>
import { computed, watch } from 'vue';
import { Close } from '@element-plus/icons-vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    season: {
        type: Object,
        required: true,
    },
    farmOptions: {
        type: Array,
        default: () => [],
    },
    pickMethodOptions: {
        type: Array,
        default: () => [],
    },
    harvestSeasonOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'success']);

const plantedMonthOptions = [
    { value: '01', label: 'January' },
    { value: '02', label: 'February' },
    { value: '03', label: 'March' },
    { value: '04', label: 'April' },
    { value: '05', label: 'May' },
    { value: '06', label: 'June' },
    { value: '07', label: 'July' },
    { value: '08', label: 'August' },
    { value: '09', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' },
];

const currentYear = new Date().getFullYear();
const currentMonth = String(new Date().getMonth() + 1).padStart(2, '0');
const plantedYearOptions = Array.from({ length: 30 }, (_, index) => String(currentYear - index));

const form = useForm({
    season_id: '',
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
});

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const seasonHarvestLabel = computed(() => props.season?.name || 'Current season');

const availablePlantedMonthOptions = computed(() => {
    if (form.date_planted_year === String(currentYear)) {
        return plantedMonthOptions.filter((option) => option.value <= currentMonth);
    }

    return plantedMonthOptions;
});

const disableFutureDates = (date) => {
    const today = new Date();
    today.setHours(23, 59, 59, 999);

    return date.getTime() > today.getTime();
};

const hydrateForm = () => {
    form.defaults({
        season_id: props.season?.id ?? '',
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
    });

    form.reset();
    form.clearErrors();
};

watch(
    () => props.modelValue,
    (open) => {
        if (open) {
            hydrateForm();
        }
    },
);

watch(
    () => form.farm_id,
    (farmId) => {
        const farm = props.farmOptions.find((option) => String(option.id) === String(farmId ?? '').trim());

        if (!farm) {
            return;
        }

        form.variety = farm.variety ?? '';
    },
    { immediate: true },
);

watch(
    () => form.date_planted_year,
    (year) => {
        if (year !== String(currentYear)) {
            return;
        }

        if (form.date_planted_month && form.date_planted_month > currentMonth) {
            form.date_planted_month = '';
        }
    },
);

const closeDialog = () => {
    dialogVisible.value = false;
};

const submit = () => {
    form
        .transform((data) => ({
            season_id: data.season_id,
            farm_id: data.farm_id,
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
        .post(route('harvest.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('success', 'Harvest added successfully.');
                closeDialog();
                ElNotification({
                    title: 'Harvest Added',
                    message: `Harvest saved to ${seasonHarvestLabel.value}.`,
                    type: 'success',
                    duration: 3200,
                    offset: 84,
                });
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
        width="min(900px, calc(100vw - 2rem))"
        class="add-harvest-dialog"
        destroy-on-close
        append-to-body
        align-center
        :close-on-click-modal="false"
        :show-close="false"
    >
        <template #header>
            <div class="modal-header">
                <div class="pr-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#6B7280]">Add harvest</div>
                    <div class="mt-1 text-[22px] font-bold tracking-tight text-[#111827]">Create Harvest</div>
                    <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                        Capture a new harvest and attach it directly to {{ seasonHarvestLabel }}.
                    </p>
                </div>
                <button type="button" class="modal-close-button" @click="closeDialog" aria-label="Close dialog">
                    <el-icon :size="18"><Close /></el-icon>
                </button>
            </div>
        </template>

        <el-form label-position="top" class="grid gap-4 p-5 pb-2 pt-2 sm:grid-cols-2">
            <el-form-item label="Farm">
                <el-select v-model="form.farm_id" filterable placeholder="Select farm" class="!w-full">
                    <el-option
                        v-for="farm in farmOptions"
                        :key="farm.id"
                        :label="`${farm.name} ${farm.location ? `- ${farm.location}` : ''}`"
                        :value="String(farm.id)"
                    />
                </el-select>
                <InputError :message="form.errors.farm_id" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Variety">
                <el-input v-model="form.variety" placeholder="Coffee variety" />
                <InputError :message="form.errors.variety" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Date planted">
                <div class="add-harvest-modal__inline-grid">
                    <el-select v-model="form.date_planted_month" placeholder="Month" class="!w-full">
                        <el-option
                            v-for="option in availablePlantedMonthOptions"
                            :key="option.value"
                            :label="option.label"
                            :value="option.value"
                        />
                    </el-select>

                    <el-select v-model="form.date_planted_year" placeholder="Year" class="!w-full">
                        <el-option
                            v-for="option in plantedYearOptions"
                            :key="option"
                            :label="option"
                            :value="option"
                        />
                    </el-select>
                </div>
                <InputError :message="form.errors.date_planted" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Harvest date">
                <el-date-picker
                    v-model="form.harvest_date"
                    type="date"
                    value-format="YYYY-MM-DD"
                    placeholder="Select harvest date"
                    class="!w-full"
                    :disabled-date="disableFutureDates"
                />
                <InputError :message="form.errors.harvest_date" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Harvest season">
                <el-select v-model="form.harvest_season" placeholder="Select harvest season" class="!w-full">
                    <el-option
                        v-for="option in harvestSeasonOptions"
                        :key="option"
                        :label="option"
                        :value="option"
                    />
                </el-select>
                <InputError :message="form.errors.harvest_season" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Pick method">
                <el-select v-model="form.pick_method" placeholder="Select pick method" class="!w-full">
                    <el-option
                        v-for="option in pickMethodOptions"
                        :key="option"
                        :label="option"
                        :value="option"
                    />
                </el-select>
                <InputError :message="form.errors.pick_method" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Weight">
                <el-input v-model="form.weight" type="number" min="0.01" step="0.01" placeholder="e.g. 1250" />
                <InputError :message="form.errors.weight" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Ripeness percentage">
                <el-input
                    v-model="form.ripeness_percentage"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    placeholder="e.g. 92.50"
                >
                    <template #suffix>%</template>
                </el-input>
                <InputError :message="form.errors.ripeness_percentage" class="modal-input-error mt-2" />
            </el-form-item>

            <el-form-item label="Price" class="sm:col-span-2">
                <el-input v-model="form.price" type="number" min="0.01" step="0.01" placeholder="e.g. 4.85">
                    <template #prefix>Shs.</template>
                </el-input>
                <InputError :message="form.errors.price" class="modal-input-error mt-2" />
            </el-form-item>

            <div class="sm:col-span-2 add-harvest-modal__quality-wrap">
                <div class="app-form-label mb-2">Quality indicators</div>
                <p class="add-harvest-modal__quality-copy">
                    Capture visible intake issues before the batch moves into quality review.
                </p>

                <div class="add-harvest-modal__quality-grid">
                    <label class="add-harvest-modal__toggle-card">
                        <span>
                            <strong>Foreign matter present</strong>
                            <small>Stones, sticks, husk residue, or other unwanted material in the lot.</small>
                            <InputError :message="form.errors.foreign_matter_present" class="modal-input-error mt-2" />
                        </span>
                        <el-switch v-model="form.foreign_matter_present" />
                    </label>

                    <label class="add-harvest-modal__toggle-card">
                        <span>
                            <strong>Pest damage</strong>
                            <small>Signs of insect attack or cherry damage visible during intake inspection.</small>
                            <InputError :message="form.errors.pest_damage" class="modal-input-error mt-2" />
                        </span>
                        <el-switch v-model="form.pest_damage" />
                    </label>

                    <label class="add-harvest-modal__toggle-card">
                        <span>
                            <strong>Disease signs</strong>
                            <small>Mold, rot, discoloration, or other symptoms that affect harvest quality.</small>
                            <InputError :message="form.errors.disease_signs" class="modal-input-error mt-2" />
                        </span>
                        <el-switch v-model="form.disease_signs" />
                    </label>

                    <label class="add-harvest-modal__toggle-card">
                        <span>
                            <strong>Visible defects</strong>
                            <small>Broken, underripe, or visibly defective cherries observed at intake.</small>
                            <InputError :message="form.errors.visible_defects" class="modal-input-error mt-2" />
                        </span>
                        <el-switch v-model="form.visible_defects" />
                    </label>
                </div>
            </div>
        </el-form>

        <template #footer>
            <div class="flex flex-col-reverse gap-3 px-5 pb-2 sm:flex-row sm:justify-end">
                <el-button @click="closeDialog">Cancel</el-button>
                <SubmitButton :loading="form.processing" :full-width="false" class="min-w-[180px]" @click="submit">
                    Save Harvest
                </SubmitButton>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
:deep(.add-harvest-dialog .el-dialog) {
    border-radius: 18px;
    overflow: hidden;
}

.modal-header {
    align-items: flex-start;
    display: flex;
    justify-content: space-between;
}

.modal-close-button {
    align-items: center;
    background: #f3f4f6;
    border: 0;
    border-radius: 999px;
    color: #4b5563;
    display: inline-flex;
    height: 34px;
    justify-content: center;
    width: 34px;
}

.add-harvest-modal__inline-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    width: 100%;
}

.add-harvest-modal__quality-wrap {
    padding-top: 4px;
}

.add-harvest-modal__quality-copy {
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
    margin: 0 0 12px;
}

.add-harvest-modal__quality-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.add-harvest-modal__toggle-card {
    align-items: flex-start;
    background: #fcfdfd;
    border: 1px solid #eef2f5;
    border-radius: 6px;
    display: flex;
    gap: 16px;
    justify-content: space-between;
    min-height: 96px;
    padding: 14px;
}

.add-harvest-modal__toggle-card span {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 4px;
}

.add-harvest-modal__toggle-card strong {
    color: #111827;
    font-size: 13px;
    font-weight: 600;
}

.add-harvest-modal__toggle-card small {
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
}

.modal-input-error {
    color: #dc2626;
    font-size: 12px;
}

:deep(.add-harvest-dialog .el-form-item__label) {
    color: #111827;
    font-size: 12px;
    font-weight: 700;
    padding-bottom: 8px;
}

@media (max-width: 900px) {
    .add-harvest-modal__inline-grid,
    .add-harvest-modal__quality-grid {
        grid-template-columns: 1fr;
    }
}
</style>
