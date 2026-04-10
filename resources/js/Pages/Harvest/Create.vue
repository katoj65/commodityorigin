<script setup>
import { computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Calendar, Crop, Location, User } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    farmOptions: {
        type: Array,
        default: () => [],
    },
    pickMethodOptions: {
        type: Array,
        default: () => [],
    },
    ripenessGradeOptions: {
        type: Array,
        default: () => [],
    },
});

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
    farm_id: '',
    variety: '',
    date_planted_month: '',
    date_planted_year: '',
    harvest_date: '',
    pick_method: '',
    price: '',
    weight: '',
    ripeness_grade: '',
});

const selectedFarm = computed(() => {
    const farmId = String(form.farm_id ?? '').trim();

    return props.farmOptions.find((farm) => String(farm.id) === farmId) ?? null;
});

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

const submit = () => {
    form
        .transform((data) => ({
            farm_id: data.farm_id,
            variety: data.variety,
            date_planted: data.date_planted_year && data.date_planted_month
                ? `${data.date_planted_year}-${data.date_planted_month}-01`
                : '',
            harvest_date: data.harvest_date,
            pick_method: data.pick_method,
            price: data.price,
            weight: data.weight,
            ripeness_grade: data.ripeness_grade,
        }))
        .post(route('harvest.store'));
};
</script>

<template>
    <AppLayout title="Add Harvest">
        <Head title="Add Harvest" />

        <div class="space-y-4">
            <section class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-1.5">
                    <h1 class="font-display text-[20px] font-bold leading-tight text-[#111827]">Add Harvest</h1>
                    <p class="max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                        Record a harvest intake against a registered farm so batch preparation and traceability can begin from the field.
                    </p>
                </div>
            </section>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_320px]">
                <div class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-4 sm:px-5 sm:py-5">
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="app-form-field">
                                <label class="app-form-label">
                                    Variety <span class="app-form-required">*</span>
                                </label>
                                <el-input v-model="form.variety" class="app-form-control" placeholder="Coffee variety" />
                                <InputError class="mt-2 text-sm" :message="form.errors.variety" />
                            </div>

                            <div class="app-form-field">
                                <label class="app-form-label">
                                    Farm ID <span class="app-form-required">*</span>
                                </label>
                                <el-input v-model="form.farm_id" class="app-form-control" placeholder="Enter farm ID manually" />
                                <InputError class="mt-2 text-sm" :message="form.errors.farm_id" />
                            </div>

                            <div class="app-form-field">
                                <label class="app-form-label">
                                    Date planted <span class="app-form-required">*</span>
                                </label>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <el-select v-model="form.date_planted_month" class="app-form-control" placeholder="Select month">
                                        <el-option
                                            v-for="option in availablePlantedMonthOptions"
                                            :key="option.value"
                                            :label="option.label"
                                            :value="option.value"
                                        />
                                    </el-select>

                                    <el-select v-model="form.date_planted_year" class="app-form-control" placeholder="Select year">
                                        <el-option
                                            v-for="option in plantedYearOptions"
                                            :key="option"
                                            :label="option"
                                            :value="option"
                                        />
                                    </el-select>
                                </div>
                                <InputError class="mt-2 text-sm" :message="form.errors.date_planted" />
                            </div>

                            <div class="app-form-field">
                                <label class="app-form-label">
                                    Harvest date <span class="app-form-required">*</span>
                                </label>
                                <div class="app-form-control app-form-control--spaced">
                                    <el-date-picker
                                        v-model="form.harvest_date"
                                        class="app-form-control"
                                        type="date"
                                        value-format="YYYY-MM-DD"
                                        placeholder="Select harvest date"
                                        :disabled-date="disableFutureDates"
                                    />
                                </div>
                                <InputError class="mt-2 text-sm" :message="form.errors.harvest_date" />
                            </div>

                            <div class="app-form-field">
                                <label class="app-form-label">
                                    Pick method <span class="app-form-required">*</span>
                                </label>
                                <el-select v-model="form.pick_method" class="app-form-control" placeholder="Select pick method">
                                    <el-option
                                        v-for="option in props.pickMethodOptions"
                                        :key="option"
                                        :label="option"
                                        :value="option"
                                    />
                                </el-select>
                                <InputError class="mt-2 text-sm" :message="form.errors.pick_method" />
                            </div>

                            <div class="app-form-field">
                                <label class="app-form-label">
                                    Weight <span class="app-form-required">*</span>
                                </label>
                                <el-input v-model="form.weight" class="app-form-control" type="number" min="0.01" step="0.01" placeholder="e.g. 1250" />
                                <InputError class="mt-2 text-sm" :message="form.errors.weight" />
                            </div>



                            <div class="app-form-field ">
                                <label class="app-form-label">
                                    Ripeness grade <span class="app-form-required">*</span>
                                </label>
                                <el-select v-model="form.ripeness_grade" class="app-form-control" placeholder="Select ripeness grade">
                                    <el-option
                                        v-for="option in props.ripenessGradeOptions"
                                        :key="option"
                                        :label="option"
                                        :value="option"
                                    />
                                </el-select>
                                <InputError class="mt-2 text-sm" :message="form.errors.ripeness_grade" />
                            </div>


                               <div class="app-form-field">
                                <label class="app-form-label">
                                    Price <span class="app-form-required">*</span>
                                </label>
                                <el-input v-model="form.price" class="app-form-control" type="number" min="0.01" step="0.01" placeholder="e.g. 4.85">
                                    <template #prefix>$</template>
                                </el-input>
                                <InputError class="mt-2 text-sm" :message="form.errors.price" />
                            </div>
                        </div>

                        <div class="flex justify-end pt-1">
                            <SubmitButton :loading="form.processing" :full-width="false">
                                Save Harvest
                            </SubmitButton>
                        </div>
                    </form>
                </div>

                <aside class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-4 sm:px-5">
                    <div class="space-y-4">
                        <div class="rounded-xl border border-[#E8F0EB] bg-[#F8FBF9] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#6B7280]">
                                        Harvest Intake
                                    </p>
                                    <h2 class="mt-1 text-[18px] font-semibold leading-tight text-[#111827]">
                                        {{ selectedFarm?.name || 'Select a farm' }}
                                    </h2>
                                </div>

                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#0F5D3B] text-white">
                                    <el-icon :size="18"><Crop /></el-icon>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-2 text-[13px] text-[#4B5563]">
                                <el-icon class="text-[#0F5D3B]"><Location /></el-icon>
                                <span>{{ selectedFarm?.location || 'Farm location pending' }}</span>
                            </div>

                            <div class="mt-2 flex items-center gap-2 text-[13px] text-[#4B5563]">
                                <el-icon class="text-[#0F5D3B]"><User /></el-icon>
                                <span>{{ selectedFarm?.id ? `Farm ID #${selectedFarm.id}` : 'Farm ID pending' }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="rounded-lg border border-[#E5E7EB] px-3.5 py-3">
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]"><Crop /></el-icon>
                                    <span>Variety</span>
                                </div>
                                <p class="mt-1.5 text-[14px] font-semibold leading-6 text-[#111827]">
                                    {{ form.variety || 'Not set' }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-[#E5E7EB] px-3.5 py-3">
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]"><Calendar /></el-icon>
                                    <span>Date planted</span>
                                </div>
                                <p class="mt-1.5 text-[14px] font-semibold leading-6 text-[#111827]">
                                    {{
                                        form.date_planted_month && form.date_planted_year
                                            ? `${plantedMonthOptions.find((option) => option.value === form.date_planted_month)?.label || ''} ${form.date_planted_year}`
                                            : 'Not set'
                                    }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-[#E5E7EB] px-3.5 py-3">
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]"><User /></el-icon>
                                    <span>Price</span>
                                </div>
                                <p class="mt-1.5 text-[14px] font-semibold leading-6 text-[#111827]">
                                    {{ form.price ? `$${form.price}` : 'Not set' }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-[#E5E7EB] px-3.5 py-3">
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]"><Calendar /></el-icon>
                                    <span>Harvest date</span>
                                </div>
                                <p class="mt-1.5 text-[14px] font-semibold leading-6 text-[#111827]">
                                    {{ form.harvest_date || 'Not set' }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-[#E5E7EB] px-3.5 py-3">
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]"><Location /></el-icon>
                                    <span>Weight</span>
                                </div>
                                <p class="mt-1.5 text-[14px] font-semibold leading-6 text-[#111827]">
                                    {{ form.weight ? `${form.weight} kg` : 'Not set' }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-dashed border-[#D1D5DB] bg-[#FAFAFA] px-3.5 py-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#6B7280]">
                                Harvest Intake Guide
                            </p>
                            <ul class="mt-2.5 space-y-2 text-[13px] leading-5 text-[#4B5563]">
                                <li>Select the correct farm so the harvest stays traceable back to origin.</li>
                                <li>Confirm the ripeness grade and pick method before the lot moves into processing.</li>
                                <li>Capture the harvested cherry weight as received in the field or collection center.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </AppLayout>
</template>
