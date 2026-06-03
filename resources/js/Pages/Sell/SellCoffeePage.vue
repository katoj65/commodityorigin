<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    // Coffee details
    coffeeName: '',
    coffeeType: '',
    origin: '',
    region: '',
    process: '',
    harvestSeason: '',
    variety: '',
    grade: '',
    moisture: '',
    cuppingScore: '',
    // Listing
    saleType: 'fixed',
    quantity: '',
    unit: 'kg',
    price: '',
    minPrice: '',
    currency: 'USD',
    // Visibility
    markets: [],
    buyerTypes: [],
    // Certs
    certifications: [],
    traceability: false,
    exportReady: false,
    // Notes
    description: '',
    images: [],
});

// ── Computed summary ──────────────────────────────────────────────────────────
const estimatedValue = computed(() => {
    const qty = Number(form.quantity || 0);
    const price = Number(form.price || 0);
    if (!qty || !price) return '—';
    return (qty * price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

const saleTypeLabel = computed(() => ({
    fixed: 'Fixed Price', auction: 'Auction', rfo: 'Request for Offers',
}[form.saleType] ?? '—'));

const formComplete = computed(() =>
    form.coffeeName && form.coffeeType && form.origin && form.quantity && form.price,
);

// ── Market data ───────────────────────────────────────────────────────────────
const marketPrices = [
    { type: 'Robusta', price: '$2.34', change: '+1.2%', up: true, weekly: '+3.4%' },
    { type: 'Arabica', price: '$3.87', change: '+0.8%', up: true, weekly: '+2.1%' },
];
const demandRegions = [
    { region: 'UAE',  level: 92, tone: 'success' },
    { region: 'EU',   level: 78, tone: 'primary' },
    { region: 'USA',  level: 85, tone: 'info' },
    { region: 'Asia', level: 70, tone: 'warning' },
];

// ── Image upload ──────────────────────────────────────────────────────────────
const MAX_IMAGES = 5;
const fileInput = ref(null);
const imageSlots = ref([]); // [{ file, url }]

const triggerUpload = () => fileInput.value?.click();

const onFilesSelected = (e) => {
    const files = Array.from(e.target.files || []);
    files.forEach(file => {
        if (imageSlots.value.length >= MAX_IMAGES) return;
        if (!file.type.startsWith('image/')) return;
        imageSlots.value.push({ file, url: URL.createObjectURL(file) });
    });
    form.images = imageSlots.value.map(s => s.file);
    e.target.value = '';
};

const removeImage = (index) => {
    URL.revokeObjectURL(imageSlots.value[index].url);
    imageSlots.value.splice(index, 1);
    form.images = imageSlots.value.map(s => s.file);
};

// ── Actions ───────────────────────────────────────────────────────────────────
const saving = ref(false);

const publish = () => form.post(route('seller.sell-coffee.store'), { forceFormData: true });
const saveDraft = () => { saving.value = true; setTimeout(() => { saving.value = false; }, 800); };
</script>

<template>
    <AppLayout title="Sell Coffee" full-width flush :show-banner="false">

        <div class="sc-page">

            <!-- ── Header ──────────────────────────────────────────────────── -->
            <div class="sc-header border-bottom">
                <div class="container-fluid px-4 px-lg-5">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 py-4">
                        <div>
                            <h1 class="sc-title mb-1">Sell Coffee</h1>
                            <p class="sc-subtitle mb-0">Fill in your coffee details and publish your listing to connect with buyers globally.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn sc-btn-outline btn-sm"><el-icon><List /></el-icon> My Listings</button>
                            <button class="btn sc-btn-outline btn-sm"><el-icon><TrendCharts /></el-icon> Market Prices</button>
                            <button class="btn sc-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Body ────────────────────────────────────────────────────── -->
            <div class="container-fluid px-4 px-lg-5 py-4">
                <div class="row g-4 align-items-start">

                    <!-- ── Left col ─────────────────────────────────────────── -->
                    <div class="col-12 col-lg-8">
                        <form @submit.prevent="publish">

                        <!-- Coffee Details -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-4">
                                    <span class="sc-card-icon sc-card-icon--green"><el-icon><Coffee /></el-icon></span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Coffee Details</h5>
                                        <p class="sc-card-sub mb-0">Describe your coffee so buyers know exactly what they're getting</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-sm-8">
                                        <label class="sc-label">Coffee Name / Lot Name <span class="sc-required">*</span></label>
                                        <el-input v-model="form.coffeeName" size="small" placeholder="e.g. Mount Elgon Arabica AA" style="width:100%" />
                                        <InputError :message="form.errors.coffeeName" />
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="sc-label">Coffee Type <span class="sc-required">*</span></label>
                                        <el-select v-model="form.coffeeType" size="small" placeholder="Select" style="width:100%">
                                            <el-option value="Arabica" label="Arabica" />
                                            <el-option value="Robusta" label="Robusta" />
                                            <el-option value="Liberica" label="Liberica" />
                                            <el-option value="Specialty" label="Specialty" />
                                        </el-select>
                                        <InputError :message="form.errors.coffeeType" />
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="sc-label">Country of Origin <span class="sc-required">*</span></label>
                                        <el-select v-model="form.origin" size="small" filterable placeholder="Select country" style="width:100%">
                                            <el-option value="Uganda" label="Uganda" />
                                            <el-option value="Ethiopia" label="Ethiopia" />
                                            <el-option value="Kenya" label="Kenya" />
                                            <el-option value="Rwanda" label="Rwanda" />
                                            <el-option value="Tanzania" label="Tanzania" />
                                            <el-option value="Colombia" label="Colombia" />
                                            <el-option value="Brazil" label="Brazil" />
                                            <el-option value="Vietnam" label="Vietnam" />
                                            <el-option value="Indonesia" label="Indonesia" />
                                            <el-option value="Other" label="Other" />
                                        </el-select>
                                        <InputError :message="form.errors.origin" />
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="sc-label">Region / District</label>
                                        <el-input v-model="form.region" size="small" placeholder="e.g. Bugisu, Mount Elgon" style="width:100%" />
                                        <InputError :message="form.errors.region" />
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="sc-label">Processing Method</label>
                                        <el-select v-model="form.process" size="small" placeholder="Select" style="width:100%">
                                            <el-option value="Washed" label="Washed" />
                                            <el-option value="Natural" label="Natural" />
                                            <el-option value="Honey" label="Honey" />
                                            <el-option value="Wet-Hulled" label="Wet-Hulled" />
                                            <el-option value="Pulped Natural" label="Pulped Natural" />
                                        </el-select>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="sc-label">Harvest Season</label>
                                        <el-select v-model="form.harvestSeason" size="small" placeholder="Select" style="width:100%">
                                            <el-option value="2024 Main Crop" label="2024 Main Crop" />
                                            <el-option value="2024 Fly Crop"  label="2024 Fly Crop" />
                                            <el-option value="2023 Main Crop" label="2023 Main Crop" />
                                            <el-option value="2023 Fly Crop"  label="2023 Fly Crop" />
                                        </el-select>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="sc-label">Variety</label>
                                        <el-input v-model="form.variety" size="small" placeholder="e.g. SL28, Bourbon" style="width:100%" />
                                        <InputError :message="form.errors.variety" />
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="sc-label">Grade / Screen Size</label>
                                        <el-select v-model="form.grade" size="small" placeholder="Select" style="width:100%">
                                            <el-option value="AA" label="AA" />
                                            <el-option value="AB" label="AB" />
                                            <el-option value="C"  label="C" />
                                            <el-option value="PB" label="PB (Peaberry)" />
                                            <el-option value="Screen 18" label="Screen 18" />
                                            <el-option value="Screen 15" label="Screen 15" />
                                        </el-select>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="sc-label">Moisture Content (%)</label>
                                        <el-input v-model="form.moisture" size="small" type="number" placeholder="e.g. 11.5" style="width:100%">
                                            <template #append>%</template>
                                        </el-input>
                                        <InputError :message="form.errors.moisture" />
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="sc-label">Cupping Score</label>
                                        <el-input v-model="form.cuppingScore" size="small" type="number" placeholder="e.g. 87.5" style="width:100%">
                                            <template #append>/ 100</template>
                                        </el-input>
                                        <InputError :message="form.errors.cuppingScore" />
                                    </div>
                                    <div class="col-12">
                                        <label class="sc-label">Description</label>
                                        <el-input
                                            v-model="form.description"
                                            type="textarea"
                                            :rows="3"
                                            size="small"
                                            placeholder="Describe your coffee — flavour notes, farm story, processing details..."
                                            style="width:100%"
                                        />
                                        <InputError :message="form.errors.description" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing & Quantity -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-4">
                                    <span class="sc-card-icon sc-card-icon--amber"><el-icon><CollectionTag /></el-icon></span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Pricing & Quantity</h5>
                                        <p class="sc-card-sub mb-0">Set how much you're selling and at what price</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="sc-label">Sale Type</label>
                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                            <label v-for="opt in [{v:'fixed',l:'Fixed Price'},{v:'auction',l:'Auction'},{v:'rfo',l:'Request for Offers'}]"
                                                :key="opt.v" class="sc-radio-pill" :class="{ 'sc-radio-pill--active': form.saleType === opt.v }">
                                                <input v-model="form.saleType" type="radio" :value="opt.v" class="d-none">
                                                {{ opt.l }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label class="sc-label">Quantity Available <span class="sc-required">*</span></label>
                                        <el-input v-model="form.quantity" size="small" type="number" placeholder="e.g. 2000" style="width:100%" />
                                        <InputError :message="form.errors.quantity" />
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label class="sc-label">Unit</label>
                                        <el-select v-model="form.unit" size="small" style="width:100%">
                                            <el-option value="kg" label="Kg" />
                                            <el-option value="bag" label="Bag (60kg)" />
                                            <el-option value="container" label="Container" />
                                        </el-select>
                                        <InputError :message="form.errors.unit" />
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label class="sc-label">Currency</label>
                                        <el-select v-model="form.currency" size="small" style="width:100%">
                                            <el-option value="USD" label="USD" />
                                            <el-option value="EUR" label="EUR" />
                                            <el-option value="UGX" label="UGX" />
                                        </el-select>
                                        <InputError :message="form.errors.currency" />
                                    </div>
                                    <div class="col-6 col-sm-6">
                                        <label class="sc-label">Asking Price (per {{ form.unit }}) <span class="sc-required">*</span></label>
                                        <el-input v-model="form.price" size="small" type="number" placeholder="0.00" style="width:100%">
                                            <template #prepend>{{ form.currency }}</template>
                                        </el-input>
                                        <InputError :message="form.errors.price" />
                                    </div>
                                    <div class="col-6 col-sm-6">
                                        <label class="sc-label">Minimum Acceptable Price</label>
                                        <el-input v-model="form.minPrice" size="small" type="number" placeholder="0.00" style="width:100%">
                                            <template #prepend>{{ form.currency }}</template>
                                        </el-input>
                                        <InputError :message="form.errors.minPrice" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Market Reach -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-4">
                                    <span class="sc-card-icon sc-card-icon--blue"><el-icon><Connection /></el-icon></span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Market Reach</h5>
                                        <p class="sc-card-sub mb-0">Choose which markets and buyers can see your listing</p>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="sc-label mb-2">Market Access</label>
                                        <el-checkbox-group v-model="form.markets" class="d-flex flex-column gap-2">
                                            <el-checkbox value="Local Market">Local Market</el-checkbox>
                                            <el-checkbox value="Regional Market">Regional Market</el-checkbox>
                                            <el-checkbox value="Global Market">Global Market</el-checkbox>
                                        </el-checkbox-group>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="sc-label mb-2">Target Buyers</label>
                                        <el-checkbox-group v-model="form.buyerTypes" class="d-flex flex-column gap-2">
                                            <el-checkbox value="Roasters">Roasters</el-checkbox>
                                            <el-checkbox value="Exporters">Exporters</el-checkbox>
                                            <el-checkbox value="Importers">Importers</el-checkbox>
                                            <el-checkbox value="Investors">Investors</el-checkbox>
                                        </el-checkbox-group>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certifications -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-4">
                                    <span class="sc-card-icon sc-card-icon--teal"><el-icon><Medal /></el-icon></span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Certifications & Standards</h5>
                                        <p class="sc-card-sub mb-0">Select all that apply — buyers filter by these</p>
                                    </div>
                                </div>
                                <el-checkbox-group v-model="form.certifications" class="d-flex flex-wrap gap-2">
                                    <el-checkbox value="Organic">Organic</el-checkbox>
                                    <el-checkbox value="Fairtrade">Fairtrade</el-checkbox>
                                    <el-checkbox value="Rainforest Alliance">Rainforest Alliance</el-checkbox>
                                    <el-checkbox value="UTZ">UTZ</el-checkbox>
                                    <el-checkbox value="4C">4C</el-checkbox>
                                    <el-checkbox value="EUDR Compliant">EUDR Compliant</el-checkbox>
                                </el-checkbox-group>
                                <div class="d-flex flex-wrap gap-4 mt-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <el-switch v-model="form.traceability" />
                                        <span class="sc-switch-label">Full Traceability Available</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <el-switch v-model="form.exportReady" />
                                        <span class="sc-switch-label">Export Ready</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Photos -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-4">
                                    <span class="sc-card-icon sc-card-icon--amber"><el-icon><Picture /></el-icon></span>
                                    <div>
                                        <h5 class="sc-card-title mb-0">Coffee Photos</h5>
                                        <p class="sc-card-sub mb-0">Listings with photos get 3× more buyer attention · up to {{ MAX_IMAGES }} images</p>
                                    </div>
                                </div>

                                <!-- Hidden file input -->
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    style="display:none"
                                    @change="onFilesSelected"
                                />

                                <!-- Drop zone (shown when no images) -->
                                <div v-if="imageSlots.length === 0" class="sc-dropzone rounded-3 mb-3" @click="triggerUpload">
                                    <el-icon style="font-size:2rem; color:#adb5bd;"><Picture /></el-icon>
                                    <p class="mb-1 mt-2" style="font-size:0.85rem; font-weight:600; color:#495057;">Click to upload photos</p>
                                    <p class="mb-0" style="font-size:0.73rem; color:#adb5bd;">PNG, JPG, WEBP — max 5 images</p>
                                </div>

                                <!-- Thumbnails grid -->
                                <div v-if="imageSlots.length > 0" class="row g-2 mb-3">
                                    <div v-for="(img, i) in imageSlots" :key="i" class="col-4 col-sm-3">
                                        <div class="sc-image-slot rounded-3">
                                            <img :src="img.url" class="sc-image-slot__img" :alt="`Photo ${i+1}`" />
                                            <button type="button" class="sc-image-slot__remove" @click="removeImage(i)" title="Remove">
                                                <el-icon><Close /></el-icon>
                                            </button>
                                            <span v-if="i === 0" class="sc-image-slot__badge">Primary</span>
                                        </div>
                                    </div>
                                    <!-- Add more slot -->
                                    <div v-if="imageSlots.length < MAX_IMAGES" class="col-4 col-sm-3">
                                        <div class="sc-image-slot sc-image-slot--add rounded-3" @click="triggerUpload">
                                            <el-icon style="font-size:1.4rem; color:#adb5bd;"><Plus /></el-icon>
                                            <span style="font-size:0.68rem; color:#adb5bd; margin-top:4px;">Add Photo</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <button type="button" class="btn sc-btn-outline btn-sm" @click="triggerUpload">
                                        <el-icon><Upload /></el-icon> {{ imageSlots.length ? 'Add More Photos' : 'Upload Photos' }}
                                    </button>
                                    <span v-if="imageSlots.length" class="text-muted" style="font-size:0.75rem;">
                                        {{ imageSlots.length }} / {{ MAX_IMAGES }} photos selected
                                    </span>
                                </div>
                                <InputError :message="form.errors.images" class="mt-2" />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="sc-card">
                            <div class="sc-card-body">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <SubmitButton :loading="form.processing" :full-width="false">
                                        <el-icon class="mr-2"><Van /></el-icon>
                                         Publish to Market
                                    </SubmitButton>
                                    <button type="button" class="btn sc-btn-outline btn-sm" :disabled="saving" @click="saveDraft">
                                        {{ saving ? 'Saving…' : 'Save Draft' }}
                                    </button>
                                    <button type="button" class="btn sc-btn-ghost btn-sm">Preview</button>
                                    <button type="button" class="btn btn-link btn-sm text-danger ms-auto">Cancel</button>
                                </div>
                                <p v-if="!formComplete" class="sc-hint mt-2 mb-0">
                                    <el-icon><Warning /></el-icon> Fill in Coffee Name, Type, Origin, Quantity and Price to publish.
                                </p>
                            </div>
                        </div>
                        </form>

                    </div><!-- /col-lg-8 -->

                    <!-- ── Right Sidebar ─────────────────────────────────────── -->
                    <div class="col-12 col-lg-4">

                        <!-- Live Summary -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-3">
                                    <span class="sc-card-icon sc-card-icon--green"><el-icon><ShoppingCart /></el-icon></span>
                                    <h6 class="sc-card-title mb-0">Listing Summary</h6>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Coffee</span>
                                    <span class="sc-summary-value">{{ form.coffeeName || '—' }}</span>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Type</span>
                                    <span class="sc-summary-value">{{ form.coffeeType || '—' }}</span>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Origin</span>
                                    <span class="sc-summary-value">{{ form.origin ? form.origin + (form.region ? ', ' + form.region : '') : '—' }}</span>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Quantity</span>
                                    <span class="sc-summary-value">{{ form.quantity ? form.quantity + ' ' + form.unit : '—' }}</span>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Price</span>
                                    <span class="sc-summary-value fw-semibold text-success">{{ form.price ? form.currency + ' ' + form.price + '/' + form.unit : '—' }}</span>
                                </div>
                                <div class="sc-summary-row">
                                    <span class="sc-summary-label">Est. Value</span>
                                    <span class="sc-summary-value fw-bold" :class="estimatedValue !== '—' ? 'text-success' : ''">
                                        {{ estimatedValue !== '—' ? form.currency + ' ' + estimatedValue : '—' }}
                                    </span>
                                </div>
                                <div class="sc-summary-row border-0">
                                    <span class="sc-summary-label">Sale Type</span>
                                    <span class="sc-summary-value">{{ saleTypeLabel }}</span>
                                </div>
                                <div v-if="form.certifications.length" class="d-flex flex-wrap gap-1 mt-3">
                                    <span v-for="c in form.certifications" :key="c"
                                        class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle"
                                        style="font-size:0.65rem;">{{ c }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Buyer Preview -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <h6 class="sc-card-title mb-3">How Buyers See It</h6>
                                <div class="sc-preview rounded-3 p-3">
                                    <div class="sc-preview__img rounded-2 mb-3">
                                        <el-icon style="font-size:1.8rem; color:#adb5bd;"><Picture /></el-icon>
                                    </div>
                                    <div class="sc-preview__name">{{ form.coffeeName || 'Your Coffee Name' }}</div>
                                    <div class="sc-preview__origin text-muted">
                                        {{ form.origin || 'Country' }}{{ form.region ? ', ' + form.region : '' }}
                                        {{ form.coffeeType ? ' · ' + form.coffeeType : '' }}
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-2 mb-2">
                                        <div class="sc-preview__price">
                                            {{ form.price ? form.currency + ' ' + form.price : '—' }}
                                            <span class="text-muted" style="font-size:0.72rem;">/{{ form.unit }}</span>
                                        </div>
                                        <div v-if="form.cuppingScore" class="sc-quality-pill">
                                            <el-icon><Star /></el-icon> {{ form.cuppingScore }}
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span v-if="form.exportReady" class="badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle" style="font-size:0.62rem;">Export Ready</span>
                                        <span v-if="form.traceability" class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:0.62rem;">Traceable</span>
                                        <span v-for="c in form.certifications.slice(0,2)" :key="c" class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:0.62rem;">{{ c }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Market Prices -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-3">
                                    <span class="sc-card-icon sc-card-icon--amber"><el-icon><TrendCharts /></el-icon></span>
                                    <h6 class="sc-card-title mb-0">Live Market Prices</h6>
                                </div>
                                <div v-for="mp in marketPrices" :key="mp.type" class="sc-price-row">
                                    <div class="sc-price-type">{{ mp.type }}</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="sc-price-value">{{ mp.price }}</div>
                                        <span class="badge rounded-pill" :class="mp.up ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'" style="font-size:0.65rem;">
                                            {{ mp.change }}
                                        </span>
                                    </div>
                                    <div class="text-muted" style="font-size:0.68rem;">Weekly: {{ mp.weekly }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Demand -->
                        <div class="sc-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-3">
                                    <span class="sc-card-icon sc-card-icon--blue"><el-icon><Location /></el-icon></span>
                                    <h6 class="sc-card-title mb-0">Buyer Demand</h6>
                                </div>
                                <div v-for="d in demandRegions" :key="d.region" class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span style="font-size:0.78rem; color:#495057;">{{ d.region }}</span>
                                        <span style="font-size:0.78rem; font-weight:600; color:#1a1a2e;">{{ d.level }}%</span>
                                    </div>
                                    <div class="progress sc-progress">
                                        <div class="progress-bar" :class="`bg-${d.tone}`" :style="{ width: d.level + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AI Pricing -->
                        <div class="sc-card sc-ai-card mb-4">
                            <div class="sc-card-body">
                                <div class="sc-card-head mb-2">
                                    <span class="sc-card-icon sc-card-icon--green"><el-icon><Opportunity /></el-icon></span>
                                    <h6 class="sc-card-title mb-0">AI Pricing Suggestion</h6>
                                </div>
                                <div class="sc-ai-range mb-2">$3.60 – $3.95 <span class="text-muted">/kg</span></div>
                                <div class="d-flex gap-2 mb-3">
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill" style="font-size:0.65rem;">Competitive</span>
                                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill" style="font-size:0.65rem;">High Demand</span>
                                </div>
                                <p style="font-size:0.78rem; color:#495057; line-height:1.6; margin:0;">
                                    Current market conditions suggest pricing between <strong>$3.60</strong> and <strong>$3.95</strong> per kg. UAE and EU buyers are actively seeking Arabica.
                                </p>
                            </div>
                        </div>

                    </div><!-- /col-lg-4 -->
                </div><!-- /row -->


            </div><!-- /container-fluid -->
        </div><!-- /sc-page -->

        <!-- FABs -->
        <div class="sc-fab-group">
            <button class="btn sc-fab" title="Contact Support"><el-icon><ChatDotRound /></el-icon></button>
            <button class="btn sc-fab sc-fab--primary" title="Ask Market Advisor"><el-icon><Opportunity /></el-icon></button>
        </div>

    </AppLayout>
</template>

<style scoped>
.sc-page { background: #fff; min-height: 100vh; padding-bottom: 5rem; }

/* Header */
.sc-header { background: #fff; }
.sc-title   { font-size: 1.25rem; font-weight: 700; color: #1a1a2e; }
.sc-subtitle { font-size: 0.8rem; color: #6c757d; }

/* Card */
.sc-card { background: #fff; border: 1px solid #e4e8e5; border-radius: 12px; }
.sc-card-body { padding: 1.5rem; }
.sc-card-head { display: flex; align-items: center; gap: 0.75rem; }

/* Card icon */
.sc-card-icon {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem; flex-shrink: 0;
}
.sc-card-icon--green { background: #d8f3dc; color: #2d6a4f; }
.sc-card-icon--amber { background: #fff3cd; color: #856404; }
.sc-card-icon--blue  { background: #cfe2ff; color: #0a58ca; }
.sc-card-icon--teal  { background: #d1ecf1; color: #0c525d; }

/* Card text */
.sc-card-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; }
.sc-card-sub   { font-size: 0.73rem; color: #6c757d; }

/* Form label */
.sc-label    { font-size: 0.73rem; font-weight: 600; color: #495057; margin-bottom: 0.3rem; display: block; }
.sc-required { color: #dc3545; }
.sc-hint     { font-size: 0.73rem; color: #856404; display: flex; align-items: center; gap: 5px; }

/* Buttons */
.sc-btn-primary {
    background: #2d6a4f; border-color: #2d6a4f; color: #fff;
    font-size: 0.82rem; font-weight: 600; border-radius: 8px;
    padding: 0.45rem 1.1rem;
    display: inline-flex; align-items: center; gap: 6px;
}
.sc-btn-primary:hover    { background: #1b4332; border-color: #1b4332; color: #fff; }
.sc-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.sc-btn-outline {
    background: transparent; border: 1px solid #dee2e6; color: #495057;
    font-size: 0.78rem; border-radius: 8px;
    display: inline-flex; align-items: center; gap: 5px;
}
.sc-btn-outline:hover { background: #f8f9fa; border-color: #adb5bd; }
.sc-btn-ghost {
    background: transparent; border: none; color: #6c757d;
    font-size: 0.78rem;
}
.sc-btn-ghost:hover { background: #f0f2f1; }

/* Radio pills */
.sc-radio-pill {
    cursor: pointer; padding: 0.35rem 0.9rem; font-size: 0.78rem;
    border-radius: 20px; border: 1px solid #dee2e6; color: #495057;
    background: #fff; user-select: none; transition: all .15s;
}
.sc-radio-pill--active { background: #2d6a4f; border-color: #2d6a4f; color: #fff; }

/* Switch label */
.sc-switch-label { font-size: 0.82rem; color: #495057; }

/* Summary */
.sc-summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.55rem 0; border-bottom: 1px solid #f0f0f0; font-size: 0.8rem;
}
.sc-summary-label { color: #6c757d; }
.sc-summary-value { color: #1a1a2e; font-weight: 500; }

/* Preview */
.sc-preview { background: #f8f9fa; border: 1px solid #e4e8e5; }
.sc-preview__img {
    height: 88px; background: #e9ecef;
    display: flex; align-items: center; justify-content: center;
}
.sc-preview__name   { font-size: 0.88rem; font-weight: 700; color: #1a1a2e; }
.sc-preview__origin { font-size: 0.73rem; }
.sc-preview__price  { font-size: 0.9rem; font-weight: 700; color: #2d6a4f; }

/* Quality pill */
.sc-quality-pill {
    display: inline-flex; align-items: center; gap: 4px;
    background: #fff3cd; color: #856404;
    border-radius: 20px; padding: 3px 10px;
    font-size: 0.75rem; font-weight: 700;
}

/* Market prices */
.sc-price-row { padding: 0.65rem 0; border-bottom: 1px solid #f0f0f0; }
.sc-price-row:last-child { border-bottom: none; }
.sc-price-type  { font-size: 0.72rem; font-weight: 700; color: #495057; text-transform: uppercase; letter-spacing: 0.04em; }
.sc-price-value { font-size: 1rem; font-weight: 800; color: #1a1a2e; }

/* Progress */
.sc-progress { height: 6px; border-radius: 3px; background: #e9ecef; }

/* AI card */
.sc-ai-card { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #b7e4c7; }
.sc-ai-range { font-size: 1.3rem; font-weight: 800; color: #1a1a2e; }

/* Drop zone */
.sc-dropzone {
    border: 2px dashed #c8d4cc; background: #f8faf9;
    padding: 2.5rem 1rem; text-align: center;
    cursor: pointer; transition: border-color 0.18s, background 0.18s;
}
.sc-dropzone:hover { border-color: #2d6a4f; background: #f0f7f4; }

/* Image slots */
.sc-image-slot {
    aspect-ratio: 1 / 1; background: #f8f9fa;
    border: 1.5px solid #e4e8e5; overflow: hidden;
    position: relative;
}
.sc-image-slot--add {
    border: 1.5px dashed #c8d4cc; cursor: pointer;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    transition: border-color 0.18s, background 0.18s;
}
.sc-image-slot--add:hover { border-color: #2d6a4f; background: #f0f7f4; }
.sc-image-slot__img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.sc-image-slot__remove {
    position: absolute; top: 4px; right: 4px;
    width: 20px; height: 20px; border-radius: 50%;
    background: rgba(0,0,0,0.55); border: none; color: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.65rem; padding: 0;
    opacity: 0; transition: opacity 0.15s;
}
.sc-image-slot:hover .sc-image-slot__remove { opacity: 1; }
.sc-image-slot__badge {
    position: absolute; bottom: 4px; left: 4px;
    background: rgba(0,0,0,0.55); color: #fff;
    font-size: 0.6rem; font-weight: 600; padding: 1px 6px;
    border-radius: 3px; text-transform: uppercase; letter-spacing: 0.05em;
}

/* Table */
.sc-table { font-size: 0.8rem; }
.sc-table th {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: #6c757d; padding: 0.5rem 0.6rem;
    background: #f8f9fa;
}
.sc-table td { padding: 0.55rem 0.6rem; vertical-align: middle; color: #495057; }
.sc-table-name { color: #1a1a2e; font-weight: 600; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* FABs */
.sc-fab-group {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    display: flex; flex-direction: column; gap: 0.5rem; z-index: 1000;
}
.sc-fab {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: #fff; border: 1px solid #dee2e6; color: #495057;
    font-size: 1rem; transition: background .15s;
}
.sc-fab:hover { background: #f0f2f1; }
.sc-fab--primary { background: #2d6a4f; border-color: #2d6a4f; color: #fff; }
.sc-fab--primary:hover { background: #1b4332; }
</style>
