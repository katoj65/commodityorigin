<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Location, User, OfficeBuilding, Crop, MapLocation } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    farmer: {
        type: Object,
        required: true,
    },
    varietyOptions: {
        type: Array,
        default: () => [],
    },
});

const farmerName = computed(() =>
    [props.farmer.first_name, props.farmer.last_name].filter(Boolean).join(' ') || 'Selected Farmer',
);

const farmerLocation = computed(() => [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', ') || 'Location pending');

const farmerMeta = computed(() => [
    {
        label: 'Coffee Type',
        value: props.farmer.coffee_type || 'Not set',
    },
    {
        label: 'Cooperative',
        value: props.farmer.cooperative || 'Independent producer',
    },
    {
        label: 'Origin',
        value: farmerLocation.value,
    },
]);

const form = useForm({
    farmer_id: props.farmer.id,
    name: '',
    location: [props.farmer.sub_county, props.farmer.district].filter(Boolean).join(', '),
    size: '',
    altitude: '',
    variety: '',
    notes: '',
});

const submit = () => {
    form.post(route('farm.store'));
};
</script>

<template>
    <AppLayout title="Add Farm">
        <Head title="Add Farm" />

        <div class="space-y-4 farm-create-page">
            <section class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-1.5">
                    <h1 class="font-display text-[20px] font-bold leading-tight text-[#111827]">Add Farm</h1>
                    <p class="max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                        Register a new farm location for {{ farmerName }} and capture its traceability details.
                    </p>
                </div>
            </section>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_320px]">
                <div class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-4 sm:px-5 sm:py-5">
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Farmer</label>
                                <el-input :model-value="farmerName" readonly />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Farm name</label>
                                <el-input v-model="form.name" placeholder="Enter farm name" />
                                <InputError class="mt-2 text-sm" :message="form.errors.name" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">
                                    Location
                                   
                                </label>
                                <el-input v-model="form.location" placeholder="Village, parish, district" />
                                <InputError class="mt-2 text-sm" :message="form.errors.location" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Farm size</label>
                                <el-input v-model="form.size" placeholder="e.g. 12 hectares" />
                                <InputError class="mt-2 text-sm" :message="form.errors.size" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Altitude</label>
                                <el-input v-model="form.altitude" placeholder="e.g. 1,850 masl" />
                                <InputError class="mt-2 text-sm" :message="form.errors.altitude" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Variety</label>
                                <el-select v-model="form.variety" class="w-full" placeholder="Select variety" clearable>
                                    <el-option
                                        v-for="option in props.varietyOptions"
                                        :key="option"
                                        :label="option"
                                        :value="option"
                                    />
                                </el-select>
                                <InputError class="mt-2 text-sm" :message="form.errors.variety" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-[12px] font-semibold text-[#374151]">Notes</label>
                            <el-input
                                v-model="form.notes"
                                type="textarea"
                                resize="vertical"
                                placeholder="Add farm notes, access details, or agronomy context"
                            />
                            <InputError class="mt-2 text-sm" :message="form.errors.notes" />
                        </div>

                        <div class="flex justify-end pt-1">
                            <SubmitButton :loading="form.processing" :full-width="false">
                                Save Farm
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
                                        Farmer Profile
                                    </p>
                                    <h2 class="mt-1 text-[18px] font-semibold leading-tight text-[#111827]">
                                        {{ farmerName }}
                                    </h2>
                                </div>

                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#0F5D3B] text-white">
                                    <el-icon :size="18"><User /></el-icon>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-2 text-[13px] text-[#4B5563]">
                                <el-icon class="text-[#0F5D3B]"><Location /></el-icon>
                                <span>{{ farmerLocation }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="item in farmerMeta"
                                :key="item.label"
                                class="rounded-lg border border-[#E5E7EB] px-3.5 py-3"
                            >
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                                    <el-icon class="text-[#0F5D3B]">
                                        <Crop v-if="item.label === 'Coffee Type'" />
                                        <OfficeBuilding v-else-if="item.label === 'Cooperative'" />
                                        <MapLocation v-else />
                                    </el-icon>
                                    <span>{{ item.label }}</span>
                                </div>
                                <p class="mt-1.5 text-[14px] font-semibold leading-6 text-[#111827]">
                                    {{ item.value }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-dashed border-[#D1D5DB] bg-[#FAFAFA] px-3.5 py-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#6B7280]">
                                Farm Intake Guide
                            </p>
                            <ul class="mt-2.5 space-y-2 text-[13px] leading-5 text-[#4B5563]">
                                <li>Use the exact farm name recognized by the farmer or cooperative.</li>
                                <li>Select the registered crop variety so farm records stay standardized.</li>
                                <li>Include altitude and notes that help future lot registration.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </AppLayout>
</template>
