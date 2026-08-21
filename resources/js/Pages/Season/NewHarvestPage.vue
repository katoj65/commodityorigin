<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Calendar, Crop, Location, User } from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    season: {
        type: Object,
        required: true,
    },
    harvests: {
        type: Array,
        default: () => [],
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
const previewUrls = ref([]);

const form = useForm({
    farm_id: props.farmOptions[0] ? String(props.farmOptions[0].id) : '',
    variety: '',
    date_planted_month: '',
    date_planted_year: '',
    harvest_date: '',
    harvest_season: props.harvestSeasonOptions[0] ?? '',
    pick_method: props.pickMethodOptions[0] ?? '',
    price: '',
    weight: '',
    ripeness_percentage: '',
    foreign_matter_present: false,
    pest_damage: false,
    disease_signs: false,
    visible_defects: false,
});

const selectedFarm = computed(() => {
    const farmId = String(form.farm_id ?? '').trim();

    return props.farmOptions.find((farm) => String(farm.id) === farmId) ?? null;
});

const seasonTitle = computed(() => props.season.name || 'Main Crop 2026');
const seasonStatus = computed(() => (props.season.status || 'active').toUpperCase());
const seasonRegion = computed(() => (props.season.region || 'Uganda').toUpperCase());
const recordedHarvests = computed(() => props.harvests.length);
const estimatedHarvests = computed(() => Math.max(props.harvests.length, 12));
const seasonCode = computed(() => {
    const year = props.season.start_date ? new Date(props.season.start_date).getFullYear() : new Date().getFullYear();
    const nameToken = seasonTitle.value
        .replace(/[^a-z0-9]+/gi, '-')
        .replace(/^-+|-+$/g, '')
        .split('-')
        .slice(0, 2)
        .join('-')
        .toUpperCase() || 'MAIN';

    return `UG-${year}-${nameToken}-${String(props.season.id).padStart(3, '0')}`;
});
const seasonDuration = computed(() => {
    if (!props.season.start_date || !props.season.end_date) {
        return 'Not set';
    }

    const formatDate = (value) => new Intl.DateTimeFormat('en-US', {
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));

    return `${formatDate(props.season.start_date)} - ${formatDate(props.season.end_date)}`;
});
const availablePlantedMonthOptions = computed(() => {
    if (form.date_planted_year === String(currentYear)) {
        return plantedMonthOptions.filter((option) => option.value <= currentMonth);
    }

    return plantedMonthOptions;
});
const datePlantedLabel = computed(() => {
    if (!form.date_planted_month || !form.date_planted_year) {
        return 'Not set';
    }

    const monthLabel = plantedMonthOptions.find((option) => option.value === form.date_planted_month)?.label ?? '';

    return `${monthLabel} ${form.date_planted_year}`;
});
const gpsCoordinates = computed(() => {
    if (selectedFarm.value?.latitude === null || selectedFarm.value?.latitude === undefined || selectedFarm.value?.longitude === null || selectedFarm.value?.longitude === undefined) {
        return '—';
    }

    const latitude = Number(selectedFarm.value.latitude);
    const longitude = Number(selectedFarm.value.longitude);
    const latDirection = latitude >= 0 ? 'N' : 'S';
    const lngDirection = longitude >= 0 ? 'E' : 'W';

    return `${Math.abs(latitude).toFixed(4)}° ${latDirection}, ${Math.abs(longitude).toFixed(4)}° ${lngDirection}`;
});
const disableFutureDates = (date) => {
    const today = new Date();
    today.setHours(23, 59, 59, 999);

    return date.getTime() > today.getTime();
};

const revokePreviewUrls = () => {
    previewUrls.value.forEach((url) => URL.revokeObjectURL(url));
    previewUrls.value = [];
};

const onEvidenceChange = (event) => {
    revokePreviewUrls();

    const files = Array.from(event.target.files ?? []);
    previewUrls.value = files.map((file) => URL.createObjectURL(file));
};

const submit = () => {
    form
        .transform((data) => ({
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
        .post(route('season.store-harvest', props.season.id), {
            preserveScroll: true,
        });
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

onBeforeUnmount(() => {
    revokePreviewUrls();
});
</script>

<template>
    <DesignPreviewLayout :title="`Add Harvest to ${seasonTitle}`">
        <Head :title="`Add Harvest to ${seasonTitle}`" />

        <div class="new-harvest-page">
            <div class="new-harvest-shell">
                <section class="new-harvest-hero app-card">
                    <div class="new-harvest-hero__copy">
                        <h1>Add Harvest to Season</h1>
                        <p>Record a new harvest under {{ seasonTitle }}</p>
                        <div class="new-harvest-hero__pills">
                            <span class="hero-pill is-green">SEASON: {{ seasonTitle.toUpperCase() }}</span>
                            <span class="hero-pill is-peach">STATUS: {{ seasonStatus }}</span>
                            <span class="hero-pill is-gray">REGION: {{ seasonRegion }}</span>
                        </div>
                    </div>

                    <div class="new-harvest-hero__actions">
                        <Link :href="route('season.show', season.id)" class="hero-action is-soft">Back to Season</Link>
                    </div>
                </section>

                <section class="new-harvest-grid">
                    <div class="new-harvest-main">
                        <article class="season-context-card app-card">
                            <div class="context-stat">
                                <span>Season ID</span>
                                <strong>{{ seasonCode }}</strong>
                            </div>
                            <div class="context-stat">
                                <span>Duration</span>
                                <strong>{{ seasonDuration }}</strong>
                            </div>
                            <div class="context-stat">
                                <span>Coffee Type</span>
                                <strong>{{ form.variety || 'Arabica SL28' }}</strong>
                            </div>
                            <div class="context-stat">
                                <span>Recorded Harvests</span>
                                <strong>{{ String(recordedHarvests).padStart(2, '0') }} / {{ String(estimatedHarvests).padStart(2, '0') }} Estimated</strong>
                            </div>
                        </article>

                        <article class="stepper-card app-card">
                            <div class="stepper-step is-active">
                                <div class="stepper-step__badge">1</div>
                                <span>Source</span>
                            </div>
                            <div class="stepper-line"></div>
                            <div class="stepper-step">
                                <div class="stepper-step__badge">2</div>
                                <span>Details</span>
                            </div>
                            <div class="stepper-line"></div>
                            <div class="stepper-step">
                                <div class="stepper-step__badge">3</div>
                                <span>Quantity</span>
                            </div>
                            <div class="stepper-line"></div>
                            <div class="stepper-step">
                                <div class="stepper-step__badge">4</div>
                                <span>Quality</span>
                            </div>
                            <div class="stepper-line"></div>
                            <div class="stepper-step">
                                <div class="stepper-step__badge">5</div>
                                <span>Review</span>
                            </div>
                        </article>

                        <form class="new-harvest-form" @submit.prevent="submit">
                            <div class="dual-card-grid">
                                <article class="panel-card app-card">
                                    <div class="panel-card__header">
                                        <h2>Source Info</h2>
                                        <div class="panel-badges">
                                            <span class="mini-pill is-green">Verified</span>
                                            <span class="mini-pill is-blue">Traceable</span>
                                        </div>
                                    </div>

                                    <div class="divider"></div>

                                    <div class="app-form-field">
                                        <label class="app-form-label">
                                            Farm ID <span class="app-form-required">*</span>
                                        </label>
                                        <el-select v-model="form.farm_id" class="app-form-control" placeholder="Select farm">
                                            <el-option
                                                v-for="farm in props.farmOptions"
                                                :key="farm.id"
                                                :label="`#${farm.id} · ${farm.name}`"
                                                :value="String(farm.id)"
                                            />
                                        </el-select>
                                        <InputError class="mt-2 text-sm" :message="form.errors.farm_id" />
                                    </div>

                                    <div class="app-form-field">
                                        <label class="app-form-label">
                                            Variety <span class="app-form-required">*</span>
                                        </label>
                                        <el-input v-model="form.variety" class="app-form-control" placeholder="Coffee variety" />
                                        <InputError class="mt-2 text-sm" :message="form.errors.variety" />
                                    </div>

                                    <div class="app-form-field">
                                        <label class="app-form-label">
                                            Date Planted <span class="app-form-required">*</span>
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

                                    <div class="info-grid">
                                        <div class="info-field">
                                            <span>Farmer Name</span>
                                            <strong>{{ selectedFarm?.farmer_name || '—' }}</strong>
                                        </div>
                                        <div class="info-field">
                                            <span>Region</span>
                                            <strong>{{ selectedFarm?.region || season.region || '—' }}</strong>
                                        </div>
                                        <div class="info-field">
                                            <span>GPS Coordinates</span>
                                            <strong>{{ gpsCoordinates }}</strong>
                                        </div>
                                        <div class="info-field">
                                            <span>Altitude (M)</span>
                                            <strong>{{ selectedFarm?.altitude || '—' }}</strong>
                                        </div>
                                    </div>
                                </article>

                                <article class="panel-card app-card">
                                    <div class="panel-card__header">
                                        <h2>Harvest Profile</h2>
                                    </div>

                                    <div class="divider"></div>

                                    <div class="app-form-field harvest-date-field">
                                        <label class="app-form-label">
                                            Harvest Date <span class="app-form-required">*</span>
                                        </label>
                                        <el-date-picker
                                            v-model="form.harvest_date"
                                            class="app-form-control harvest-date-control"
                                            type="date"
                                            value-format="YYYY-MM-DD"
                                            placeholder="Select harvest date"
                                            :disabled-date="disableFutureDates"
                                        />
                                        <InputError class="mt-4 text-sm" :message="form.errors.harvest_date" />
                                    </div>

                                    <div class="app-form-field">
                                        <label class="app-form-label">
                                            Harvest Season <span class="app-form-required">*</span>
                                        </label>
                                        <el-select v-model="form.harvest_season" class="app-form-control" placeholder="Select harvest season">
                                            <el-option
                                                v-for="option in props.harvestSeasonOptions"
                                                :key="option"
                                                :label="option"
                                                :value="option"
                                            />
                                        </el-select>
                                        <InputError class="mt-2 text-sm" :message="form.errors.harvest_season" />
                                    </div>

                                    <div class="app-form-field">
                                        <label class="app-form-label">
                                            Pick Method <span class="app-form-required">*</span>
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
                                            Price <span class="app-form-required">*</span>
                                        </label>
                                        <el-input v-model="form.price" class="app-form-control" type="number" min="0.01" step="0.01" placeholder="e.g. 4.85">
                                            <template #prefix>Shs.</template>
                                        </el-input>
                                        <InputError class="mt-2 text-sm" :message="form.errors.price" />
                                    </div>
                                </article>
                            </div>

                            <div class="metrics-grid">
                                <article class="panel-card app-card">
                                    <div class="panel-card__header">
                                        <h2>Quantity &amp; Quality Metrics</h2>
                                    </div>

                                    <div class="divider"></div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="app-form-field">
                                            <label class="app-form-label">
                                                Weight <span class="app-form-required">*</span>
                                            </label>
                                            <el-input
                                                v-model="form.weight"
                                                class="app-form-control"
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                placeholder="e.g. 1250"
                                            />
                                            <InputError class="mt-2 text-sm" :message="form.errors.weight" />
                                        </div>

                                        <div class="app-form-field">
                                            <label class="app-form-label">
                                                Ripeness Percentage <span class="app-form-required">*</span>
                                            </label>
                                            <el-input
                                                v-model="form.ripeness_percentage"
                                                class="app-form-control"
                                                type="number"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                placeholder="e.g. 92.50"
                                            >
                                                <template #suffix>%</template>
                                            </el-input>
                                            <InputError class="mt-2 text-sm" :message="form.errors.ripeness_percentage" />
                                        </div>
                                    </div>

                                    <div class="quality-grid">
                                        <label class="quality-item">
                                            <span class="quality-item__copy">
                                                <strong>Foreign Matter Present</strong>
                                                <small>Stones, sticks, husk residue, or other unwanted material found in the lot.</small>
                                                <InputError class="pt-1 text-sm" :message="form.errors.foreign_matter_present" />
                                            </span>
                                            <el-switch v-model="form.foreign_matter_present" />
                                        </label>

                                        <label class="quality-item">
                                            <span class="quality-item__copy">
                                                <strong>Pest Damage</strong>
                                                <small>Signs of insect attack or cherry damage visible during intake inspection.</small>
                                                <InputError class="pt-1 text-sm" :message="form.errors.pest_damage" />
                                            </span>
                                            <el-switch v-model="form.pest_damage" />
                                        </label>

                                        <label class="quality-item">
                                            <span class="quality-item__copy">
                                                <strong>Disease Signs</strong>
                                                <small>Mold, rot, discoloration, or other symptoms that affect harvest quality.</small>
                                                <InputError class="pt-1 text-sm" :message="form.errors.disease_signs" />
                                            </span>
                                            <el-switch v-model="form.disease_signs" />
                                        </label>

                                        <label class="quality-item">
                                            <span class="quality-item__copy">
                                                <strong>Visible Defects</strong>
                                                <small>Broken, immature, or clearly inconsistent cherries noticed during handling.</small>
                                                <InputError class="pt-1 text-sm" :message="form.errors.visible_defects" />
                                            </span>
                                            <el-switch v-model="form.visible_defects" />
                                        </label>
                                    </div>
                                </article>

                                <article class="panel-card app-card evidence-card">
                                    <div class="panel-card__header">
                                        <h2>Evidence</h2>
                                    </div>

                                    <div class="divider"></div>

                                    <label class="evidence-dropzone">
                                        <input type="file" accept=".jpg,.jpeg,.png,.pdf" multiple class="sr-only" @change="onEvidenceChange" />
                                        <div class="evidence-dropzone__icon">☁</div>
                                        <strong>Drag &amp; drop harvest photos or field logs</strong>
                                        <span>JPEG, PNG, PDF up to 10MB</span>
                                    </label>

                                    <div class="evidence-thumbs">
                                        <template v-if="previewUrls.length">
                                            <div v-for="thumb in previewUrls.slice(0, 2)" :key="thumb" class="evidence-thumb">
                                                <img :src="thumb" alt="Harvest evidence preview" />
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="evidence-thumb evidence-thumb--sample"></div>
                                            <div class="evidence-thumb evidence-thumb--doc">▤</div>
                                        </template>
                                    </div>
                                </article>
                            </div>

                            <div class="footer-grid">
                                <article class="panel-card app-card">
                                    <div class="checklist-header">
                                        <h2>Readiness Checklist</h2>
                                        <div>
                                            <strong>85%</strong>
                                            <span>Score</span>
                                        </div>
                                    </div>

                                    <div class="checklist-list">
                                        <div class="checklist-item is-complete">Fermentation Tanks Ready</div>
                                        <div class="checklist-item is-complete">Sorting Tables Clean</div>
                                        <div class="checklist-item is-complete">Labor Scheduled</div>
                                        <div class="checklist-item is-pending">Dryer Capacity Checked</div>
                                        <div class="checklist-item is-complete">Waste Plan Active</div>
                                        <div class="checklist-item is-complete">Logistics Confirmed</div>
                                    </div>
                                </article>

                                <article class="activities-card">
                                    <h2>Farming Activities Link</h2>
                                    <div class="activities-divider"></div>
                                    <div class="activities-grid">
                                        <div>
                                            <span>Total Logged</span>
                                            <strong>24 Operations</strong>
                                        </div>
                                        <div>
                                            <span>Crop Health</span>
                                            <strong>Optimal</strong>
                                        </div>
                                        <div>
                                            <span>Last Intervention</span>
                                            <strong>2 days ago (Mulching)</strong>
                                        </div>
                                        <div>
                                            <span>Evidence Meta</span>
                                            <strong>92% Match</strong>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <div class="submit-row mt-2 border-top pt-4">
                                <SubmitButton :loading="form.processing" :full-width="false">
                                    Save Harvest
                                </SubmitButton>
                            </div>
                        </form>
                    </div>

                    <aside class="new-harvest-sidebar">
                        <article class="summary-card app-card">
                            <div class="summary-card__head">
                                <div>
                                    <p class="summary-eyebrow">Harvest Intake</p>
                                    <h2>Harvest Summary</h2>
                                </div>

                                <div class="summary-badge">
                                    <el-icon :size="18"><Crop /></el-icon>
                                </div>
                            </div>

                            <div class="summary-farm">
                                <div class="summary-farm__row">
                                    <el-icon class="text-[#0F5D3B]"><Location /></el-icon>
                                    <span>{{ selectedFarm?.name || 'Select a farm' }}</span>
                                </div>
                                <div class="summary-farm__row">
                                    <el-icon class="text-[#0F5D3B]"><User /></el-icon>
                                    <span>{{ selectedFarm?.location || 'Farm location pending' }}</span>
                                </div>
                            </div>

                            <div class="summary-list">
                                <div>
                                    <span>Variety</span>
                                    <strong>{{ form.variety || 'Not set' }}</strong>
                                </div>
                                <div>
                                    <span>Date Planted</span>
                                    <strong>{{ datePlantedLabel }}</strong>
                                </div>
                                <div>
                                    <span>Harvest Date</span>
                                    <strong>{{ form.harvest_date || 'Not set' }}</strong>
                                </div>
                                <div>
                                    <span>Harvest Season</span>
                                    <strong>{{ form.harvest_season || 'Not set' }}</strong>
                                </div>
                                <div>
                                    <span>Pick Method</span>
                                    <strong>{{ form.pick_method || 'Not set' }}</strong>
                                </div>
                                <div>
                                    <span>Weight</span>
                                    <strong>{{ form.weight ? `${form.weight} kg` : 'Not set' }}</strong>
                                </div>
                                <div>
                                    <span>Price</span>
                                    <strong>{{ form.price ? `Shs. ${form.price}` : 'Not set' }}</strong>
                                </div>
                                <div>
                                    <span>Ripeness</span>
                                    <strong>{{ form.ripeness_percentage ? `${form.ripeness_percentage}%` : 'Not set' }}</strong>
                                </div>
                            </div>

                            <div class="summary-guide">
                                <p class="summary-eyebrow">Harvest Intake Guide</p>
                                <ul>
                                    <li>Select the correct farm so the harvest stays traceable back to origin.</li>
                                    <li>Confirm the ripeness grade and pick method before the lot moves into processing.</li>
                                    <li>Capture the harvested cherry weight as received in the field or collection center.</li>
                                </ul>
                            </div>
                        </article>
                    </aside>
                </section>
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.new-harvest-page {
    background: var(--surface, #f7f9fb);
    min-height: calc(100vh - 72px);
}

.new-harvest-shell {
    margin: 0 auto;
    max-width: 1240px;
    padding: 0;
}

.app-card {
    background: #ffffff;
    border: 1px solid #eef2f0;
    border-radius: 16px;
    box-shadow: none;
}

.new-harvest-hero {
    align-items: flex-start;
    display: flex;
    gap: 24px;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 20px;
}

.new-harvest-hero h1 {
    color: #0d3f33;
    font-size: 27px;
    font-weight: 700;
    letter-spacing: -0.04em;
    line-height: 1.05;
    margin: 0;
}

.new-harvest-hero p {
    color: #2e3537;
    font-size: 15px;
    margin: 10px 0 0;
}

.new-harvest-hero__pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 16px;
}

.hero-pill,
.mini-pill {
    border-radius: 999px;
    display: inline-flex;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    line-height: 1;
    padding: 7px 12px;
}

.hero-pill.is-green,
.mini-pill.is-green {
    background: #b9f0d1;
    color: #0a4737;
}

.hero-pill.is-peach {
    background: #f7d2b1;
    color: #8c5622;
}

.hero-pill.is-gray {
    background: #dadddf;
    color: #252a2d;
}

.mini-pill.is-blue {
    background: #cfd7f0;
    color: #3d4967;
}

.new-harvest-hero__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: flex-end;
}

.hero-action {
    align-items: center;
    border-radius: 0;
    cursor: pointer;
    display: inline-flex;
    font-size: 16px;
    font-weight: 500;
    justify-content: center;
    min-height: 36px;
    min-width: 140px;
    padding: 0 18px;
    text-decoration: none;
}

.hero-action.is-soft {
    background: rgba(255, 255, 255, 0.74);
    border: 1px solid #edf0f2;
    color: #0e1817;
}

.hero-action.is-primary {
    background: #0a503d;
    border: 1px solid #083e2f;
    color: #fff;
}

.new-harvest-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr) 320px;
}

.new-harvest-main,
.new-harvest-sidebar,
.new-harvest-form {
    display: grid;
    gap: 16px;
}

.season-context-card {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    padding: 20px 24px;
}

.context-stat span,
.info-field span,
.summary-list span,
.summary-eyebrow,
.activities-grid span,
.checklist-header span {
    color: #6b7280;
    display: block;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.context-stat strong,
.info-field strong,
.summary-list strong,
.activities-grid strong,
.checklist-header strong {
    color: #111827;
    display: block;
    font-size: 16px;
    font-weight: 600;
    margin-top: 8px;
}

.stepper-card {
    align-items: center;
    display: grid;
    gap: 14px;
    grid-template-columns: auto minmax(20px, 1fr) auto minmax(20px, 1fr) auto minmax(20px, 1fr) auto minmax(20px, 1fr) auto;
    padding: 18px 16px;
}

.stepper-step {
    align-items: center;
    color: #1f2a2e;
    display: flex;
    flex-direction: column;
    font-size: 12px;
    gap: 10px;
    text-transform: uppercase;
}

.stepper-step__badge {
    align-items: center;
    background: #dce1e5;
    border-radius: 12px;
    color: #20262a;
    display: flex;
    font-size: 24px;
    font-weight: 500;
    height: 40px;
    justify-content: center;
    width: 40px;
}

.stepper-step.is-active .stepper-step__badge {
    background: #0a503d;
    color: #fff;
}

.stepper-line {
    border-top: 2px solid #d1d5db;
}

.dual-card-grid,
.metrics-grid,
.footer-grid {
    display: grid;
    gap: 16px;
}

.dual-card-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.metrics-grid {
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 0.8fr);
}

.footer-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.panel-card,
.summary-card {
    padding: 20px;
}

.panel-card__header,
.checklist-header,
.summary-card__head {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.panel-card__header h2,
.summary-card h2,
.activities-card h2,
.checklist-header h2 {
    color: #111827;
    font-size: 17px;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0;
}

.panel-badges {
    display: flex;
    gap: 8px;
}

.app-form-field {
    margin-bottom: 18px;
}

.app-form-field:last-child {
    margin-bottom: 0;
}

.app-form-field + .app-form-field {
    margin-top: 4px;
}

.divider,
.activities-divider {
    border-top: 1px solid #eef2f0;
    margin: 16px 0;
}

.harvest-date-field {
    margin-bottom: 28px;
}

.info-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 18px;
}

.info-field {
    background: #f9fafb;
    border: 1px solid #eef2f0;
    border-radius: 12px;
    padding: 12px 14px;
}

.quality-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 18px;
}

.quality-item {
    align-items: flex-start;
    background: #fcfdfd;
    border: 1px solid #eef2f0;
    border-radius: 12px;
    display: flex;
    gap: 16px;
    justify-content: space-between;
    min-height: 88px;
    padding: 14px;
}

.quality-item__copy strong {
    color: #111827;
    display: block;
    font-size: 13px;
    font-weight: 600;
}

.quality-item__copy small {
    color: #6b7280;
    display: block;
    font-size: 12px;
    line-height: 1.5;
    margin-top: 4px;
}

.evidence-dropzone {
    align-items: center;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    color: #1d2528;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 10px;
    justify-content: center;
    min-height: 196px;
    padding: 24px;
    text-align: center;
}

.evidence-dropzone__icon {
    color: #627073;
    font-size: 36px;
}

.evidence-dropzone span {
    color: #697579;
    font-size: 12px;
}

.evidence-thumbs {
    display: flex;
    gap: 10px;
    margin-top: 18px;
}

.evidence-thumb {
    align-items: center;
    background: #e6eaec;
    border-radius: 12px;
    display: flex;
    height: 48px;
    justify-content: center;
    overflow: hidden;
    width: 48px;
}

.evidence-thumb img {
    height: 100%;
    object-fit: cover;
    width: 100%;
}

.evidence-thumb--sample {
    background: linear-gradient(145deg, #521818, #cc4343);
}

.evidence-thumb--doc {
    color: #5d676a;
    font-size: 22px;
}

.checklist-header div {
    text-align: right;
}

.checklist-header strong {
    color: #093c30;
    font-size: 22px;
    margin-top: 0;
}

.checklist-list {
    display: grid;
    gap: 16px 24px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 24px;
}

.checklist-item {
    color: #252a2d;
    font-size: 14px;
    padding-left: 30px;
    position: relative;
}

.checklist-item::before {
    border: 1.5px solid #163d36;
    border-radius: 999px;
    content: '';
    height: 16px;
    left: 0;
    position: absolute;
    top: 2px;
    width: 16px;
}

.checklist-item.is-complete::after {
    color: #0a503d;
    content: '✓';
    font-size: 12px;
    left: 3px;
    position: absolute;
    top: 1px;
}

.activities-card {
    background: #084b39;
    border: 1px solid #084b39;
    border-radius: 16px;
    color: #fff;
    padding: 20px;
}

.activities-card h2,
.activities-card strong,
.activities-card span {
    color: #fff;
}

.activities-grid {
    display: grid;
    gap: 24px 16px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.summary-eyebrow {
    margin: 0 0 6px;
}

.summary-badge {
    align-items: center;
    background: #0f5d3b;
    border-radius: 999px;
    color: #fff;
    display: flex;
    height: 44px;
    justify-content: center;
    width: 44px;
}

.summary-farm {
    background: #f8fbf9;
    border: 1px solid #e8f0eb;
    border-radius: 16px;
    margin-top: 16px;
    padding: 16px;
}

.summary-farm__row {
    align-items: center;
    color: #4b5563;
    display: flex;
    font-size: 13px;
    gap: 8px;
}

.summary-farm__row + .summary-farm__row {
    margin-top: 10px;
}

.summary-list {
    display: grid;
    gap: 14px;
    margin-top: 16px;
}

.summary-list > div {
    border-bottom: 1px solid #f3f4f6;
    padding-bottom: 14px;
}

.summary-list > div:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.summary-guide {
    background: #fafafa;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    margin-top: 18px;
    padding: 14px;
}

.summary-guide ul {
    color: #4b5563;
    font-size: 13px;
    line-height: 1.6;
    margin: 10px 0 0;
    padding-left: 18px;
}

.submit-row {
    display: flex;
    justify-content: flex-end;
    padding-top: 4px;
}

:deep(.app-form-label) {
    color: #374151;
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

:deep(.app-form-required) {
    color: #b42318;
}

:deep(.app-form-control),
:deep(.app-form-control.el-date-editor),
:deep(.app-form-control .el-input__wrapper),
:deep(.app-form-control .el-select__wrapper),
:deep(.app-form-control.el-date-editor .el-input__wrapper) {
    width: 100%;
}

:deep(.app-form-control .el-input__wrapper),
:deep(.app-form-control .el-select__wrapper),
:deep(.app-form-control.el-date-editor .el-input__wrapper) {
    background: #ffffff;
    border: none;
    border-radius: 12px;
    box-shadow: 0 0 0 1px #eef2f0 inset;
    min-height: 42px;
    padding-left: 12px;
    padding-right: 12px;
}

:deep(.harvest-date-control.el-date-editor) {
    display: flex;
}

:deep(.harvest-date-control .el-input__wrapper) {
    min-height: 48px;
}

:deep(.app-form-control .el-input__wrapper.is-focus),
:deep(.app-form-control .el-select__wrapper.is-focused),
:deep(.app-form-control .el-select__wrapper:focus-within),
:deep(.app-form-control.el-date-editor.is-focus .el-input__wrapper),
:deep(.app-form-control.el-date-editor.is-active .el-input__wrapper) {
    border-color: #0f5d3b;
    box-shadow: 0 0 0 1px #0f5d3b inset;
}

@media (max-width: 1180px) {
    .new-harvest-grid,
    .dual-card-grid,
    .metrics-grid,
    .footer-grid,
    .season-context-card,
    .quality-grid,
    .checklist-list,
    .activities-grid,
    .info-grid {
        grid-template-columns: 1fr;
    }

    .new-harvest-hero {
        flex-direction: column;
    }

    .new-harvest-hero__actions {
        justify-content: flex-start;
    }
}

@media (max-width: 780px) {
    .stepper-card {
        gap: 10px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .stepper-line {
        display: none;
    }

    .stepper-step {
        font-size: 11px;
    }

    .new-harvest-hero h1 {
        font-size: 24px;
    }

    .hero-action {
        min-width: 0;
        width: 100%;
    }
}
</style>
