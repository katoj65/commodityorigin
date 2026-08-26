<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Link as LinkIcon } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    batchId: { type: [Number, String], required: true },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({ collection_code: '' });

/* ── Collection-by-code lookup ────────────────────────────────────────────
   Mirrors AddFarmCollectionModal.vue's farm-by-code lookup exactly — a
   plain text input resolved via GET farm-collection.find-by-code (open,
   not scoped to ownership) before submit. The actual link is authorized
   server-side by BatchPolicy::update on the batch, independent of this
   lookup. */
const lookupStatus = ref('idle'); // idle | loading | found | not-found
const foundCollection = ref(null);

async function findByCode() {
    const code = form.collection_code.trim();
    foundCollection.value = null;

    if (!code) {
        lookupStatus.value = 'idle';
        return;
    }

    lookupStatus.value = 'loading';
    try {
        const { data } = await axios.get(route('farm-collection.find-by-code'), { params: { collection_code: code } });
        foundCollection.value = data;
        lookupStatus.value = 'found';
        // Normalize the field to the trimmed value that was actually
        // matched, so submit() posts the same string the lookup verified.
        form.collection_code = code;
    } catch (error) {
        lookupStatus.value = 'not-found';
    }
}

const collectionAvailable = computed(() => foundCollection.value?.status === 'pending');

function formatMoney(amount, currency) {
    if (amount === null || amount === undefined) return '—';
    const value = Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${value}` : `$${value}`;
}

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
    lookupStatus.value = 'idle';
    foundCollection.value = null;
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (!foundCollection.value || !collectionAvailable.value) return;

    form.post(route('batch.farm-collections.store', props.batchId), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            form.reset();
            form.clearErrors();
            lookupStatus.value = 'idle';
            foundCollection.value = null;
            ElNotification({ title: 'Collection Linked', message: 'The farm collection was linked to this batch.', type: 'success', duration: 3200, offset: 84 });
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(480px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="afc-modal"
    >
        <template #header>
            <div class="afc-modal__head">
                <div class="afc-modal__head-icon">
                    <el-icon :size="18"><LinkIcon /></el-icon>
                </div>
                <div class="afc-modal__head-text">
                    <div class="afc-modal__eyebrow">Batch</div>
                    <div class="afc-modal__title">Attach Farm Collection</div>
                </div>
                <button type="button" class="afc-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="afc-modal__body">
            <div class="afc-field">
                <label class="afc-field__label">Collection Code</label>
                <el-input
                    v-model="form.collection_code"
                    placeholder="e.g. COL-2026-AB12CD"
                    class="afc-input"
                    :class="{ 'afc-input--error': lookupStatus === 'not-found' || form.errors.collection_code }"
                    @blur="findByCode"
                    @keyup.enter="findByCode"
                />
                <span v-if="lookupStatus === 'loading'" class="afc-field__hint">Looking up collection…</span>
                <span v-else-if="lookupStatus === 'not-found'" class="afc-field__error">No farm collection with that code was found.</span>
                <span v-if="form.errors.collection_code" class="afc-field__error">{{ form.errors.collection_code }}</span>
            </div>

            <div v-if="lookupStatus === 'found' && foundCollection" class="acf-preview">
                <div class="acf-preview__row acf-preview__row--head">
                    <span class="acf-preview__farm">{{ foundCollection.farm?.name || `Farm #${foundCollection.farm_id}` }}</span>
                    <span v-if="foundCollection.initial_grade" class="acf-preview__pill">Grade {{ foundCollection.initial_grade }}</span>
                </div>
                <div class="acf-preview__row">
                    <span class="acf-preview__label">Coffee</span>
                    <span class="acf-preview__value">{{ foundCollection.coffee_type || '—' }}<span v-if="foundCollection.variety"> · {{ foundCollection.variety }}</span></span>
                </div>
                <div class="acf-preview__row">
                    <span class="acf-preview__label">Quantity</span>
                    <span class="acf-preview__value">{{ Number(foundCollection.quantity || 0).toLocaleString() }} {{ foundCollection.unit || '' }}</span>
                </div>
                <div class="acf-preview__row">
                    <span class="acf-preview__label">Price</span>
                    <span class="acf-preview__value">{{ formatMoney(foundCollection.collection_price, foundCollection.currency) }}</span>
                </div>
            </div>

            <p v-if="foundCollection && !collectionAvailable" class="afc-field__error acf-unavailable">
                This collection has already been {{ foundCollection.status }} and can't be used again.
            </p>
        </div>

        <template #footer>
            <div class="afc-modal__footer">
                <button type="button" class="afc-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="afc-btn-primary" :disabled="form.processing || !foundCollection || !collectionAvailable" @click="submit">
                    {{ form.processing ? 'Linking…' : 'Link Collection' }}
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

.afc-modal__body { padding: 22px 24px; }

.afc-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.afc-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.afc-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.afc-field__hint { font-size: 12px; font-weight: 500; color: #6F7677; line-height: 1.4; }

.afc-input { width: 100%; }
.afc-input :deep(.el-input__wrapper) { border-radius: 6px; }
.afc-input--error :deep(.el-input__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

/* ── Resolved collection preview ──────────────────────────────────────── */
.acf-preview {
    margin-top: 16px;
    padding: 14px 16px;
    border-radius: 6px;
    background: #F5F6F7;
    border: 1px solid #E5E7EB;
    display: flex;
    flex-direction: column;
    gap: 9px;
}
.acf-unavailable { margin-top: 10px; }
.acf-preview__row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.acf-preview__row--head { padding-bottom: 9px; border-bottom: 1px dashed #E5E7EB; }
.acf-preview__farm { font-size: 14px; font-weight: 700; color: #121516; }
.acf-preview__pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #E5E7EB;
    color: #4B5457;
    font-size: 11px;
    font-weight: 700;
}
.acf-preview__label { font-size: 12px; color: #6F7677; }
.acf-preview__value { font-size: 12.5px; font-weight: 600; color: #121516; }

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
.afc-btn-primary:hover:not(:disabled) { opacity: 0.88; }
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
</style>
