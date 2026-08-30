<script setup>
import { computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Hide, Promotion, Star, View } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    lot: { type: Object, required: true },
    currencyOptions: { type: Array, default: () => [] },
    currencyCountries: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue']);

const unitOptions = ['kg', 'lbs', 'bags'];
const pricingTypeOptions = [
    { value: 'fixed', label: 'Set price' },
    { value: 'negotiable', label: 'Open to offers' },
    { value: 'auction', label: 'Auction' },
];

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function currencyLabel(code) {
    const country = props.currencyCountries[code];
    return country ? `${code} — ${country}` : code;
}

/* ── Pre-fill from the lot's own recorded data — the seller can still
   adjust anything (e.g. list less than the lot's full net weight) before
   publishing. Delivery location defaults from the lot's first linked
   batch's warehouse, when one is available. ────────────────────────── */
function fieldsFromLot() {
    const batch = props.lot.lot_batches?.[0]?.batch ?? null;

    return {
        title: props.lot.lot_name || props.lot.lot_number || '',
        description: props.lot.description || '',
        quantity: props.lot.net_weight_kg ?? '',
        available_quantity: props.lot.net_weight_kg ?? '',
        unit: 'kg',
        currency: props.lot.currency || 'USD',
        price_per_unit: props.lot.price ?? '',
        pricing_type: 'fixed',
        minimum_order_quantity: 0,
        payment_terms: '',
        delivery_terms: '',
        delivery_location: batch?.warehouse_location || '',
        is_featured: false,
        is_public: true,
    };
}

const form = useForm(fieldsFromLot());

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        form.defaults(fieldsFromLot());
        form.reset();
        form.clearErrors();
    }
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.post(route('lot.publish', props.lot.id), {
        preserveScroll: true,
        onSuccess: () => {
            const flash = usePage().props.flash || {};
            if (flash.error) {
                ElNotification({ title: 'Already Published', message: flash.error, type: 'warning', duration: 3600, offset: 84 });
            } else {
                ElNotification({ title: 'Published', message: flash.success || 'Your lot is now live on the market.', type: 'success', duration: 3200, offset: 84 });
            }
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
        class="plm-modal"
    >
        <template #header>
            <div class="plm-modal__head">
                <div class="plm-modal__head-icon">
                    <el-icon :size="18"><Promotion /></el-icon>
                </div>
                <div class="plm-modal__head-text">
                    <div class="plm-modal__eyebrow">Lot Profile</div>
                    <div class="plm-modal__title">Publish to Market</div>
                </div>
                <button type="button" class="plm-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="plm-modal__body">
            <p class="plm-message">
                We've filled this in using <strong>{{ lot.lot_name || lot.lot_number }}</strong>'s details — have a look, tweak anything you like, then send it live for buyers to see.
            </p>

            <p v-if="form.errors.lot" class="plm-banner">{{ form.errors.lot }}</p>

            <div class="plm-grid">
                <div class="plm-field plm-field--span2">
                    <label class="plm-field__label">What should buyers see this as?</label>
                    <el-input v-model="form.title" placeholder="e.g. Yirgacheffe Reserve" class="plm-input" :class="{ 'plm-input--error': form.errors.title }" />
                    <span v-if="form.errors.title" class="plm-field__error">{{ form.errors.title }}</span>
                </div>

                <div class="plm-field">
                    <label class="plm-field__label">How much are you listing?</label>
                    <el-input-number v-model="form.quantity" :min="0.01" :precision="2" class="plm-input w-100" :class="{ 'plm-input--error': form.errors.quantity }" />
                    <span v-if="form.errors.quantity" class="plm-field__error">{{ form.errors.quantity }}</span>
                </div>
                <div class="plm-field">
                    <label class="plm-field__label">How much is available right now? <small>(optional)</small></label>
                    <el-input-number v-model="form.available_quantity" :min="0" :max="form.quantity || undefined" :precision="2" class="plm-input w-100" :class="{ 'plm-input--error': form.errors.available_quantity }" />
                    <span v-if="form.errors.available_quantity" class="plm-field__error">{{ form.errors.available_quantity }}</span>
                </div>

                <div class="plm-field">
                    <label class="plm-field__label">Unit</label>
                    <el-select v-model="form.unit" placeholder="Select unit" class="plm-input w-100" :class="{ 'plm-input--error': form.errors.unit }">
                        <el-option v-for="option in unitOptions" :key="option" :label="option" :value="option" />
                    </el-select>
                    <span v-if="form.errors.unit" class="plm-field__error">{{ form.errors.unit }}</span>
                </div>
                <div class="plm-field">
                    <label class="plm-field__label">Currency</label>
                    <el-select v-model="form.currency" placeholder="Select currency" filterable class="plm-input w-100" :class="{ 'plm-input--error': form.errors.currency }">
                        <el-option v-for="option in currencyOptions" :key="option" :label="currencyLabel(option)" :value="option" />
                    </el-select>
                    <span v-if="form.errors.currency" class="plm-field__error">{{ form.errors.currency }}</span>
                </div>

                <div class="plm-field">
                    <label class="plm-field__label">Price per {{ form.unit || 'unit' }}</label>
                    <el-input-number v-model="form.price_per_unit" :min="0" :precision="2" class="plm-input w-100" :class="{ 'plm-input--error': form.errors.price_per_unit }" />
                    <span v-if="form.errors.price_per_unit" class="plm-field__error">{{ form.errors.price_per_unit }}</span>
                </div>
                <div class="plm-field">
                    <label class="plm-field__label">How is it priced?</label>
                    <el-select v-model="form.pricing_type" placeholder="Select pricing" class="plm-input w-100" :class="{ 'plm-input--error': form.errors.pricing_type }">
                        <el-option v-for="option in pricingTypeOptions" :key="option.value" :label="option.label" :value="option.value" />
                    </el-select>
                    <span v-if="form.errors.pricing_type" class="plm-field__error">{{ form.errors.pricing_type }}</span>
                </div>

                <div class="plm-field">
                    <label class="plm-field__label">Smallest order you'll accept <small>(optional)</small></label>
                    <el-input-number v-model="form.minimum_order_quantity" :min="0" :precision="2" class="plm-input w-100" :class="{ 'plm-input--error': form.errors.minimum_order_quantity }" />
                    <span v-if="form.errors.minimum_order_quantity" class="plm-field__error">{{ form.errors.minimum_order_quantity }}</span>
                </div>
                <div class="plm-field">
                    <label class="plm-field__label">Where will it ship from? <small>(optional)</small></label>
                    <el-input v-model="form.delivery_location" placeholder="e.g. Kampala Warehouse" class="plm-input" :class="{ 'plm-input--error': form.errors.delivery_location }" />
                    <span v-if="form.errors.delivery_location" class="plm-field__error">{{ form.errors.delivery_location }}</span>
                </div>

                <div class="plm-field">
                    <label class="plm-field__label">How should buyers pay? <small>(optional)</small></label>
                    <el-input v-model="form.payment_terms" placeholder="e.g. 50% deposit, balance on delivery" class="plm-input" :class="{ 'plm-input--error': form.errors.payment_terms }" />
                    <span v-if="form.errors.payment_terms" class="plm-field__error">{{ form.errors.payment_terms }}</span>
                </div>
                <div class="plm-field">
                    <label class="plm-field__label">How will it be delivered? <small>(optional)</small></label>
                    <el-input v-model="form.delivery_terms" placeholder="e.g. FOB, Ex-works" class="plm-input" :class="{ 'plm-input--error': form.errors.delivery_terms }" />
                    <span v-if="form.errors.delivery_terms" class="plm-field__error">{{ form.errors.delivery_terms }}</span>
                </div>

                <div class="plm-field plm-field--span2">
                    <label class="plm-field__label">Anything else buyers should know? <small>(optional)</small></label>
                    <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Describe this lot for buyers" class="plm-input" :class="{ 'plm-input--error': form.errors.description }" />
                    <span v-if="form.errors.description" class="plm-field__error">{{ form.errors.description }}</span>
                </div>
            </div>

            <div class="plm-visibility">
                <div class="plm-toggle">
                    <div class="plm-toggle__icon plm-toggle__icon--featured"><el-icon><Star /></el-icon></div>
                    <div class="plm-toggle__text">
                        <span class="plm-toggle__title">Feature this listing</span>
                        <span class="plm-toggle__desc">Featured listings are highlighted at the top of the market so more buyers notice them.</span>
                    </div>
                    <el-switch v-model="form.is_featured" />
                </div>
                <div class="plm-toggle">
                    <div class="plm-toggle__icon" :class="form.is_public ? 'plm-toggle__icon--public' : 'plm-toggle__icon--private'"><el-icon><component :is="form.is_public ? View : Hide" /></el-icon></div>
                    <div class="plm-toggle__text">
                        <span class="plm-toggle__title">{{ form.is_public ? 'Public' : 'Private' }}</span>
                        <span class="plm-toggle__desc">
                            {{ form.is_public
                                ? 'Anyone browsing the market can see this listing.'
                                : "Only you can see this listing — it won't appear anywhere on the market until you make it public." }}
                        </span>
                    </div>
                    <el-switch v-model="form.is_public" active-text="Public" inactive-text="Private" inline-prompt />
                </div>
            </div>
        </div>

        <template #footer>
            <div class="plm-modal__footer">
                <button type="button" class="plm-btn-outline" :disabled="form.processing" @click="closeDialog">Cancel</button>
                <button type="button" class="plm-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Publishing…' : 'Publish Lot' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.plm-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.plm-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.plm-modal .el-dialog__body { padding: 0; }
.el-dialog.plm-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.plm-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.plm-modal__head-icon {
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
.plm-modal__head-text { flex: 1; min-width: 0; }
.plm-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.plm-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.plm-modal__close {
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
.plm-modal__close:hover { background: #E5E7EB; color: #121516; }

.plm-modal__body { padding: 22px 24px 8px; max-height: 72vh; overflow-y: auto; }
.plm-message { font-size: 13.5px; line-height: 1.6; color: #4B5457; margin: 0 0 18px; }
.plm-message strong { color: #121516; }

.plm-banner {
    font-size: 12.5px;
    font-weight: 500;
    color: #B91C1C;
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: 6px;
    padding: 10px 12px;
    margin: -6px 0 18px;
}

.plm-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.plm-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.plm-field--span2 { grid-column: span 2; }

.plm-field__label {
    display: flex; align-items: center; gap: 6px;
    font-size: 13.5px; font-weight: 600; color: #121516;
}
.plm-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.plm-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.plm-input { width: 100%; }
.plm-input :deep(.el-input__wrapper),
.plm-input :deep(.el-select__wrapper),
.plm-input :deep(.el-textarea__inner) { border-radius: 6px; }
.plm-input--error :deep(.el-input__wrapper),
.plm-input--error :deep(.el-select__wrapper),
.plm-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

/* ── Visibility settings — icon + title + description rows, matching a
   standard settings-panel pattern rather than two bare labeled switches. */
.plm-visibility {
    display: flex; flex-direction: column; gap: 10px;
    margin-top: 4px; padding-top: 18px; border-top: 1px solid #E5E7EB;
}
.plm-toggle {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px; border-radius: 8px; background: #F5F6F7;
}
.plm-toggle__icon {
    width: 32px; height: 32px; border-radius: 7px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
}
.plm-toggle__icon--featured { background: #FFF1E8; color: #C2410C; }
.plm-toggle__icon--public { background: #EFF6FF; color: #1D4ED8; }
.plm-toggle__icon--private { background: #F1F2F3; color: #4B5457; }
.plm-toggle__text { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; padding-top: 2px; }
.plm-toggle__title { font-size: 13px; font-weight: 700; color: #121516; }
.plm-toggle__desc { font-size: 12px; line-height: 1.5; color: #6F7677; }
.plm-toggle .el-switch { flex-shrink: 0; margin-top: 4px; }

.plm-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.plm-btn-primary {
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
.plm-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.plm-btn-primary:disabled { opacity: 0.5; cursor: default; }
.plm-btn-outline {
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
.plm-btn-outline:hover:not(:disabled) { background: #F5F6F7; }
.plm-btn-outline:disabled { opacity: 0.5; cursor: default; }

@media (max-width: 640px) {
    .plm-grid { grid-template-columns: 1fr; }
    .plm-field--span2 { grid-column: span 1; }
}
</style>
