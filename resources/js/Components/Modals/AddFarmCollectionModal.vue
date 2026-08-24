<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Coffee } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    coffeeTypeOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

/* Fixed option sets, matching FarmCollectionService::unitOptions() /
   paymentStatusOptions() exactly — these are stable server-side enums,
   not free-text, so they're safe to mirror as literals here. */
const unitOptions = ['kg', 'lbs', 'bags'];
const paymentStatusOptions = ['pending', 'partial', 'paid', 'cancelled'];

function emptyForm() {
    return {
        farm_id: '',
        collection_date: '',
        coffee_type: '',
        variety: '',
        harvest_season: '',
        quantity: '',
        unit: 'kg',
        initial_moisture: '',
        initial_defects: '',
        initial_grade: '',
        initial_quality_score: '',
        collection_price: '',
        currency: 'USD',
        payment_status: 'pending',
        reference: '',
        notes: '',
    };
}

const form = useForm(emptyForm());

/* ── Farm-by-code lookup ────────────────────────────────────────────────
   The Farm field is a plain text input for the farm's code, not a
   picker — findFarmByCode() resolves it to a real farm id (matched
   strictly by farm_code, via GET farm.find-by-code — not scoped to
   ownership) before the id is used to build the farm.collections.store
   URL on submit. Ownership is still enforced server-side by
   FarmPolicy::update on that submit, independent of this lookup. */
const farmCode = ref('');
const farmLookupStatus = ref('idle'); // idle | loading | found | not-found
const foundFarmName = ref('');

async function findFarmByCode() {
    const code = farmCode.value.trim();
    form.farm_id = '';
    foundFarmName.value = '';

    if (!code) {
        farmLookupStatus.value = 'idle';
        return;
    }

    farmLookupStatus.value = 'loading';
    try {
        const { data } = await axios.get(route('farm.find-by-code'), { params: { farm_code: code } });
        form.farm_id = data.id;
        foundFarmName.value = data.name;
        farmLookupStatus.value = 'found';
    } catch (error) {
        farmLookupStatus.value = 'not-found';
    }
}

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();
    farmCode.value = '';
    farmLookupStatus.value = 'idle';
    foundFarmName.value = '';
});

function disableFutureDates(date) {
    return date.getTime() > Date.now();
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (!form.farm_id) return;

    const { farm_id, ...payload } = form.data();
    form.transform(() => payload).post(route('farm.collections.store', farm_id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({ title: 'Collection Recorded', message: 'The farm collection was saved.', type: 'success', duration: 3200, offset: 84 });
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
        class="afc-modal"
    >
        <template #header>
            <div class="afc-modal__head">
                <div class="afc-modal__head-icon">
                    <el-icon :size="18"><Coffee /></el-icon>
                </div>
                <div class="afc-modal__head-text">
                    <div class="afc-modal__eyebrow">Inventory</div>
                    <div class="afc-modal__title">New Farm Collection</div>
                </div>
                <button type="button" class="afc-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="afc-modal__body">
                <div class="afc-field afc-field--span2">
                    <label class="afc-field__label">Farm Code</label>
                    <el-input
                        v-model="farmCode"
                        placeholder="e.g. FARM-0042"
                        class="afc-input"
                        :class="{ 'afc-input--error': farmLookupStatus === 'not-found' || form.errors.farm_id }"
                        @blur="findFarmByCode"
                        @keyup.enter="findFarmByCode"
                    />
                    <span v-if="farmLookupStatus === 'loading'" class="afc-field__hint">Looking up farm…</span>
                    <span v-else-if="farmLookupStatus === 'found'" class="afc-field__hint afc-field__hint--ok">✓ {{ foundFarmName }}</span>
                    <span v-else-if="farmLookupStatus === 'not-found'" class="afc-field__error">No farm with that code was found.</span>
                    <span v-if="form.errors.farm_id" class="afc-field__error">{{ form.errors.farm_id }}</span>
                </div>

                <div class="afc-grid">
                    <div class="afc-field">
                        <label class="afc-field__label">Collection Date</label>
                        <el-date-picker v-model="form.collection_date" type="date" value-format="YYYY-MM-DD" placeholder="Select date" :disabled-date="disableFutureDates" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.collection_date }" />
                        <span v-if="form.errors.collection_date" class="afc-field__error">{{ form.errors.collection_date }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Coffee Type</label>
                        <el-select v-model="form.coffee_type" placeholder="Select coffee type" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.coffee_type }">
                            <el-option v-for="option in coffeeTypeOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.coffee_type" class="afc-field__error">{{ form.errors.coffee_type }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Variety <small>(optional)</small></label>
                        <el-input v-model="form.variety" placeholder="e.g. SL28" class="afc-input" :class="{ 'afc-input--error': form.errors.variety }" />
                        <span v-if="form.errors.variety" class="afc-field__error">{{ form.errors.variety }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Harvest Season <small>(optional)</small></label>
                        <el-select v-model="form.harvest_season" placeholder="Select season" clearable class="afc-input w-100" :class="{ 'afc-input--error': form.errors.harvest_season }">
                            <el-option v-for="option in harvestSeasonOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.harvest_season" class="afc-field__error">{{ form.errors.harvest_season }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Quantity</label>
                        <el-input-number v-model="form.quantity" :min="0.01" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.quantity }" />
                        <span v-if="form.errors.quantity" class="afc-field__error">{{ form.errors.quantity }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Unit</label>
                        <el-select v-model="form.unit" placeholder="Select unit" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.unit }">
                            <el-option v-for="option in unitOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.unit" class="afc-field__error">{{ form.errors.unit }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Initial Moisture % <small>(optional)</small></label>
                        <el-input-number v-model="form.initial_moisture" :min="0" :max="100" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.initial_moisture }" />
                        <span v-if="form.errors.initial_moisture" class="afc-field__error">{{ form.errors.initial_moisture }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Initial Defects <small>(optional)</small></label>
                        <el-input-number v-model="form.initial_defects" :min="0" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.initial_defects }" />
                        <span v-if="form.errors.initial_defects" class="afc-field__error">{{ form.errors.initial_defects }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Initial Grade <small>(optional)</small></label>
                        <el-input v-model="form.initial_grade" placeholder="e.g. AA" class="afc-input" :class="{ 'afc-input--error': form.errors.initial_grade }" />
                        <span v-if="form.errors.initial_grade" class="afc-field__error">{{ form.errors.initial_grade }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Initial Quality Score <small>(optional)</small></label>
                        <el-input-number v-model="form.initial_quality_score" :min="0" :max="100" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.initial_quality_score }" />
                        <span v-if="form.errors.initial_quality_score" class="afc-field__error">{{ form.errors.initial_quality_score }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Collection Price <small>(optional)</small></label>
                        <el-input-number v-model="form.collection_price" :min="0" :precision="2" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.collection_price }" />
                        <span v-if="form.errors.collection_price" class="afc-field__error">{{ form.errors.collection_price }}</span>
                    </div>
                    <div class="afc-field">
                        <label class="afc-field__label">Currency</label>
                        <el-select v-model="form.currency" placeholder="Select currency" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.currency }">
                            <el-option v-for="option in currencyOptions" :key="option" :label="option" :value="option" />
                        </el-select>
                        <span v-if="form.errors.currency" class="afc-field__error">{{ form.errors.currency }}</span>
                    </div>
                    <div class="afc-field afc-field--span2">
                        <label class="afc-field__label">Payment Status</label>
                        <el-select v-model="form.payment_status" placeholder="Select status" class="afc-input w-100" :class="{ 'afc-input--error': form.errors.payment_status }">
                            <el-option v-for="option in paymentStatusOptions" :key="option" :label="option.charAt(0).toUpperCase() + option.slice(1)" :value="option" />
                        </el-select>
                        <span v-if="form.errors.payment_status" class="afc-field__error">{{ form.errors.payment_status }}</span>
                    </div>
                    <div class="afc-field afc-field--span2">
                        <label class="afc-field__label">Reference <small>(optional)</small></label>
                        <el-input v-model="form.reference" placeholder="Payment / lot reference" class="afc-input" :class="{ 'afc-input--error': form.errors.reference }" />
                        <span v-if="form.errors.reference" class="afc-field__error">{{ form.errors.reference }}</span>
                    </div>
                    <div class="afc-field afc-field--span2">
                        <label class="afc-field__label">Notes <small>(optional)</small></label>
                        <el-input v-model="form.notes" type="textarea" :rows="2" class="afc-input" :class="{ 'afc-input--error': form.errors.notes }" />
                        <span v-if="form.errors.notes" class="afc-field__error">{{ form.errors.notes }}</span>
                    </div>
                </div>
        </div>

        <template #footer>
            <div class="afc-modal__footer">
                <button type="button" class="afc-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="afc-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Collection' }}
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

.afc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.afc-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.afc-field--span2 { grid-column: span 2; }

.afc-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.afc-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.afc-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.afc-field__hint { font-size: 12px; font-weight: 500; color: #6F7677; line-height: 1.4; }
.afc-field__hint--ok { color: #2F6B35; }

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
.afc-btn-primary:hover { opacity: 0.88; }
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
