<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Document, Star, Ticket, WarningFilled } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    lot: { type: Object, required: true },
    processOptions: { type: Array, default: () => [] },
    coffeeGradeOptions: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
    originOptions: { type: Array, default: () => [] },
    packagingTypeOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    currencyCountries: { type: Object, default: () => ({}) },
    flavorOptions: { type: Array, default: () => [] },
});

function currencyLabel(code) {
    const country = props.currencyCountries[code];
    return country ? `${code} — ${country}` : code;
}

const emit = defineEmits(['update:modelValue', 'success']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({
    lot_name: '',
    description: '',
    image: null,
    process: '',
    grade: '',
    variety: '',
    origin: '',
    region: '',
    altitude: '',
    year_of_harvest: '',
    moisture: '',
    defects_percentage: '',
    screen: '',
    packaging_type: '',
    quantity_bags: '',
    bag_weight_kg: '',
    price: '',
    currency: 'USD',
    quality_score: '',
    acidity: '',
    body: '',
    flavor: '',
    aroma: '',
    balance: '',
    aftertaste: '',
    notes: '',
});

const hydrateForm = () => {
    form.defaults({
        lot_name: props.lot.lot_name ?? '',
        description: props.lot.description ?? '',
        image: null,
        process: props.lot.process ?? '',
        grade: props.lot.grade ?? '',
        variety: props.lot.variety ?? '',
        origin: props.lot.origin ?? '',
        region: props.lot.region ?? '',
        altitude: props.lot.altitude ?? '',
        year_of_harvest: props.lot.year_of_harvest ?? '',
        moisture: props.lot.moisture ?? '',
        defects_percentage: props.lot.defects_percentage ?? '',
        screen: props.lot.screen ?? '',
        packaging_type: props.lot.packaging_type ?? '',
        quantity_bags: props.lot.quantity_bags ?? '',
        bag_weight_kg: props.lot.bag_weight_kg ?? '',
        price: props.lot.price ?? '',
        currency: props.lot.currency ?? 'USD',
        quality_score: props.lot.quality_score ?? '',
        acidity: props.lot.acidity ?? '',
        body: props.lot.body ?? '',
        flavor: props.lot.flavor ?? '',
        aroma: props.lot.aroma ?? '',
        balance: props.lot.balance ?? '',
        aftertaste: props.lot.aftertaste ?? '',
        notes: props.lot.notes ?? '',
    });

    form.reset();
    form.clearErrors();
};

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) hydrateForm();
});

function closeDialog() {
    dialogVisible.value = false;
}

function onImageChange(event) {
    const [file] = event.target.files ?? [];
    form.image = file ?? null;
}

function submit() {
    form.patch(route('lot.update', props.lot.id), {
        forceFormData: true,
        preserveScroll: 'errors',
        onSuccess: () => {
            ElNotification({
                title: 'Lot Updated',
                message: `Lot ${props.lot.lot_number || `#${props.lot.id}`} was updated successfully.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
            emit('success');
            closeDialog();
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(680px, calc(100vw - 2rem))"
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="elm-modal"
    >
        <template #header>
            <div class="elm-modal__head">
                <div class="elm-modal__head-icon">
                    <el-icon :size="18"><Ticket /></el-icon>
                </div>
                <div class="elm-modal__head-text">
                    <div class="elm-modal__eyebrow">Lot Profile</div>
                    <div class="elm-modal__title">Edit Lot</div>
                </div>
                <button type="button" class="elm-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="elm-modal__body">
            <div class="elm-section">
                <h3 class="elm-section__title"><el-icon><Ticket /></el-icon> Lot Identity</h3>
                <div class="elm-grid">
                    <div class="elm-field elm-field--span2">
                        <label class="elm-field__label">Lot Name <small>(optional)</small></label>
                        <el-input v-model="form.lot_name" placeholder="e.g. Yirgacheffe Reserve" class="elm-input" :class="{ 'elm-input--error': form.errors.lot_name }" />
                        <span v-if="form.errors.lot_name" class="elm-field__error">{{ form.errors.lot_name }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Variety</label>
                        <el-select v-model="form.variety" placeholder="Select variety" filterable class="elm-input w-100" :class="{ 'elm-input--error': form.errors.variety }">
                            <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.variety" class="elm-field__error">{{ form.errors.variety }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Grade</label>
                        <el-select v-model="form.grade" placeholder="Select grade" filterable class="elm-input w-100" :class="{ 'elm-input--error': form.errors.grade }">
                            <el-option v-for="option in coffeeGradeOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.grade" class="elm-field__error">{{ form.errors.grade }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Screen</label>
                        <el-input v-model="form.screen" placeholder="e.g. 16/18" class="elm-input" :class="{ 'elm-input--error': form.errors.screen }" />
                        <span v-if="form.errors.screen" class="elm-field__error">{{ form.errors.screen }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Origin</label>
                        <el-select v-model="form.origin" placeholder="Select origin country" filterable class="elm-input w-100" :class="{ 'elm-input--error': form.errors.origin }">
                            <el-option v-for="option in originOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.origin" class="elm-field__error">{{ form.errors.origin }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Region</label>
                        <el-input v-model="form.region" placeholder="e.g. Sidama" class="elm-input" :class="{ 'elm-input--error': form.errors.region }" />
                        <span v-if="form.errors.region" class="elm-field__error">{{ form.errors.region }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Altitude (m) <small>(optional)</small></label>
                        <el-input-number v-model="form.altitude" :min="0" :max="5000" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.altitude }" />
                        <span v-if="form.errors.altitude" class="elm-field__error">{{ form.errors.altitude }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Year of Harvest</label>
                        <el-input-number v-model="form.year_of_harvest" :min="2000" :max="2100" :controls="false" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.year_of_harvest }" />
                        <span v-if="form.errors.year_of_harvest" class="elm-field__error">{{ form.errors.year_of_harvest }}</span>
                    </div>
                </div>
            </div>

            <div class="elm-section">
                <h3 class="elm-section__title"><el-icon><WarningFilled /></el-icon> Processing &amp; Quality</h3>
                <div class="elm-grid">
                    <div class="elm-field">
                        <label class="elm-field__label">Process</label>
                        <el-select v-model="form.process" placeholder="Select process" filterable class="elm-input w-100" :class="{ 'elm-input--error': form.errors.process }">
                            <el-option v-for="option in processOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.process" class="elm-field__error">{{ form.errors.process }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Moisture %</label>
                        <el-input-number v-model="form.moisture" :min="0" :max="100" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.moisture }" />
                        <span v-if="form.errors.moisture" class="elm-field__error">{{ form.errors.moisture }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Defects % <small>(optional)</small></label>
                        <el-input-number v-model="form.defects_percentage" :min="0" :max="100" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.defects_percentage }" />
                        <span v-if="form.errors.defects_percentage" class="elm-field__error">{{ form.errors.defects_percentage }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Quality Score <small>(optional)</small></label>
                        <el-input-number v-model="form.quality_score" :min="0" :max="100" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.quality_score }" />
                        <span v-if="form.errors.quality_score" class="elm-field__error">{{ form.errors.quality_score }}</span>
                    </div>
                </div>
            </div>

            <div class="elm-section">
                <h3 class="elm-section__title"><el-icon><Star /></el-icon> Cupping Profile</h3>
                <div class="elm-grid">
                    <div class="elm-field">
                        <label class="elm-field__label">Acidity <small>(optional)</small></label>
                        <el-input-number v-model="form.acidity" :min="0" :max="10" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.acidity }" />
                        <span v-if="form.errors.acidity" class="elm-field__error">{{ form.errors.acidity }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Body <small>(optional)</small></label>
                        <el-input-number v-model="form.body" :min="0" :max="10" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.body }" />
                        <span v-if="form.errors.body" class="elm-field__error">{{ form.errors.body }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Flavor <small>(optional)</small></label>
                        <el-select v-model="form.flavor" placeholder="Select a flavor" filterable clearable class="elm-input w-100" :class="{ 'elm-input--error': form.errors.flavor }">
                            <el-option v-for="option in flavorOptions" :key="option.slug" :label="option.name" :value="option.slug" />
                        </el-select>
                        <span v-if="form.errors.flavor" class="elm-field__error">{{ form.errors.flavor }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Aroma <small>(optional)</small></label>
                        <el-input-number v-model="form.aroma" :min="0" :max="10" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.aroma }" />
                        <span v-if="form.errors.aroma" class="elm-field__error">{{ form.errors.aroma }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Balance <small>(optional)</small></label>
                        <el-input-number v-model="form.balance" :min="0" :max="10" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.balance }" />
                        <span v-if="form.errors.balance" class="elm-field__error">{{ form.errors.balance }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Aftertaste <small>(optional)</small></label>
                        <el-input-number v-model="form.aftertaste" :min="0" :max="10" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.aftertaste }" />
                        <span v-if="form.errors.aftertaste" class="elm-field__error">{{ form.errors.aftertaste }}</span>
                    </div>
                </div>
            </div>

            <div class="elm-section">
                <h3 class="elm-section__title"><el-icon><Ticket /></el-icon> Packaging &amp; Quantity</h3>
                <div class="elm-grid">
                    <div class="elm-field">
                        <label class="elm-field__label">Packaging Type <small>(optional)</small></label>
                        <el-select v-model="form.packaging_type" placeholder="Select packaging type" clearable class="elm-input w-100" :class="{ 'elm-input--error': form.errors.packaging_type }">
                            <el-option v-for="option in packagingTypeOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.packaging_type" class="elm-field__error">{{ form.errors.packaging_type }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Quantity (bags)</label>
                        <el-input-number v-model="form.quantity_bags" :min="1" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.quantity_bags }" />
                        <span v-if="form.errors.quantity_bags" class="elm-field__error">{{ form.errors.quantity_bags }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Bag Weight (kg)</label>
                        <el-input-number v-model="form.bag_weight_kg" :min="1" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.bag_weight_kg }" />
                        <span v-if="form.errors.bag_weight_kg" class="elm-field__error">{{ form.errors.bag_weight_kg }}</span>
                    </div>
                    <div class="elm-field">
                        <label class="elm-field__label">Price <small>(optional)</small></label>
                        <el-input-number v-model="form.price" :min="0" :precision="2" class="elm-input w-100" :class="{ 'elm-input--error': form.errors.price }" />
                        <span v-if="form.errors.price" class="elm-field__error">{{ form.errors.price }}</span>
                    </div>
                    <div class="elm-field elm-field--span2">
                        <label class="elm-field__label">Currency</label>
                        <el-select v-model="form.currency" placeholder="Select currency" filterable class="elm-input w-100" :class="{ 'elm-input--error': form.errors.currency }">
                            <el-option v-for="option in currencyOptions" :key="option" :label="currencyLabel(option)" :value="option" />
                        </el-select>
                        <span v-if="form.errors.currency" class="elm-field__error">{{ form.errors.currency }}</span>
                    </div>
                </div>
            </div>

            <div class="elm-section">
                <h3 class="elm-section__title"><el-icon><Document /></el-icon> Description &amp; Notes</h3>
                <div class="elm-grid">
                    <div class="elm-field elm-field--span2">
                        <label class="elm-field__label">Description <small>(optional)</small></label>
                        <el-input v-model="form.description" type="textarea" :rows="2" class="elm-input" :class="{ 'elm-input--error': form.errors.description }" />
                        <span v-if="form.errors.description" class="elm-field__error">{{ form.errors.description }}</span>
                    </div>
                    <div class="elm-field elm-field--span2">
                        <label class="elm-field__label">Notes <small>(optional)</small></label>
                        <el-input v-model="form.notes" type="textarea" :rows="2" class="elm-input" :class="{ 'elm-input--error': form.errors.notes }" />
                        <span v-if="form.errors.notes" class="elm-field__error">{{ form.errors.notes }}</span>
                    </div>
                    <div class="elm-field elm-field--span2">
                        <label class="elm-field__label">Image <small>(optional — leave blank to keep current)</small></label>
                        <input type="file" accept="image/*" class="elm-file-input" @change="onImageChange">
                        <small v-if="form.image" class="elm-file-name">{{ form.image.name }}</small>
                        <span v-if="form.errors.image" class="elm-field__error">{{ form.errors.image }}</span>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="elm-modal__footer">
                <button type="button" class="elm-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="elm-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.elm-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.elm-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.elm-modal .el-dialog__body { padding: 0; }
.el-dialog.elm-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.elm-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.elm-modal__head-icon {
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
.elm-modal__head-text { flex: 1; min-width: 0; }
.elm-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.elm-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.elm-modal__close {
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
.elm-modal__close:hover { background: #E5E7EB; color: #121516; }

.elm-modal__body { padding: 22px 24px 8px; max-height: 72vh; overflow-y: auto; }

.elm-section { margin-bottom: 22px; }
.elm-section:last-child { margin-bottom: 0; }
.elm-section__title {
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
.elm-section:first-child .elm-section__title { padding-top: 0; border-top: none; }
.elm-section__title .el-icon { font-size: 13px; color: #6F7677; }

.elm-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.elm-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.elm-field--span2 { grid-column: span 2; }

.elm-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.elm-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.elm-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.elm-input { width: 100%; }
.elm-input :deep(.el-input__wrapper),
.elm-input :deep(.el-select__wrapper),
.elm-input :deep(.el-textarea__inner) { border-radius: 6px; }
.elm-input--error :deep(.el-input__wrapper),
.elm-input--error :deep(.el-select__wrapper),
.elm-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.elm-file-input {
    width: 100%;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 13px;
    color: #121516;
    background: #fff;
    cursor: pointer;
}
.elm-file-name { font-size: 12px; color: #6F7677; }

.elm-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.elm-btn-primary {
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
.elm-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.elm-btn-primary:disabled { opacity: 0.5; cursor: default; }
.elm-btn-outline {
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
.elm-btn-outline:hover { background: #F5F6F7; }

@media (max-width: 640px) {
    .elm-grid { grid-template-columns: 1fr; }
}
</style>
