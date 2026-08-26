<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Link as LinkIcon } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    lotId: { type: [Number, String], required: true },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({ batch_number: '' });

/* ── Batch-by-number lookup ────────────────────────────────────────────
   Mirrors AttachFarmCollectionModal.vue's collection-by-code lookup — a
   plain text input resolved via GET batch.find-by-number (open, not
   scoped to ownership) before submit. The actual link is authorized
   server-side by LotPolicy::update on the lot, independent of this
   lookup. */
const lookupStatus = ref('idle'); // idle | loading | found | not-found
const foundBatch = ref(null);

async function findByNumber() {
    const number = form.batch_number.trim();
    foundBatch.value = null;

    if (!number) {
        lookupStatus.value = 'idle';
        return;
    }

    lookupStatus.value = 'loading';
    try {
        const { data } = await axios.get(route('batch.find-by-number'), { params: { batch_number: number } });
        foundBatch.value = data;
        lookupStatus.value = 'found';
        // Normalize the field to the trimmed value that was actually
        // matched, so submit() posts the same string the lookup verified.
        form.batch_number = number;
    } catch (error) {
        lookupStatus.value = 'not-found';
    }
}

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
    lookupStatus.value = 'idle';
    foundBatch.value = null;
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (!foundBatch.value) return;

    form.post(route('lot.batches.store', props.lotId), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            form.reset();
            form.clearErrors();
            lookupStatus.value = 'idle';
            foundBatch.value = null;
            ElNotification({ title: 'Batch Linked', message: 'The batch was linked to this lot.', type: 'success', duration: 3200, offset: 84 });
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
        class="abtm-modal"
    >
        <template #header>
            <div class="abtm-modal__head">
                <div class="abtm-modal__head-icon">
                    <el-icon :size="18"><LinkIcon /></el-icon>
                </div>
                <div class="abtm-modal__head-text">
                    <div class="abtm-modal__eyebrow">Lot</div>
                    <div class="abtm-modal__title">Attach Batch</div>
                </div>
                <button type="button" class="abtm-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="abtm-modal__body">
            <div class="abtm-field">
                <label class="abtm-field__label">Batch Number</label>
                <el-input
                    v-model="form.batch_number"
                    placeholder="e.g. BATCH-2026-AB12CD"
                    class="abtm-input"
                    :class="{ 'abtm-input--error': lookupStatus === 'not-found' || form.errors.batch_number }"
                    @blur="findByNumber"
                    @keyup.enter="findByNumber"
                />
                <span v-if="lookupStatus === 'loading'" class="abtm-field__hint">Looking up batch…</span>
                <span v-else-if="lookupStatus === 'not-found'" class="abtm-field__error">No batch with that number was found.</span>
                <span v-if="form.errors.batch_number" class="abtm-field__error">{{ form.errors.batch_number }}</span>
            </div>

            <div v-if="lookupStatus === 'found' && foundBatch" class="abtm-preview">
                <div class="abtm-preview__row abtm-preview__row--head">
                    <span class="abtm-preview__variety">{{ foundBatch.variety || `Batch #${foundBatch.id}` }}</span>
                    <span v-if="foundBatch.status" class="abtm-preview__pill">{{ foundBatch.status }}</span>
                </div>
                <div class="abtm-preview__row">
                    <span class="abtm-preview__label">Warehouse</span>
                    <span class="abtm-preview__value">{{ foundBatch.warehouse_location || '—' }}</span>
                </div>
                <div class="abtm-preview__row">
                    <span class="abtm-preview__label">Weight</span>
                    <span class="abtm-preview__value">{{ Number(foundBatch.net_weight_kg || 0).toLocaleString() }} kg</span>
                </div>
                <div class="abtm-preview__row">
                    <span class="abtm-preview__label">Cup Score</span>
                    <span class="abtm-preview__value">{{ foundBatch.cup_score ? `${foundBatch.cup_score}/100` : '—' }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="abtm-modal__footer">
                <button type="button" class="abtm-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="abtm-btn-primary" :disabled="form.processing || !foundBatch" @click="submit">
                    {{ form.processing ? 'Linking…' : 'Link Batch' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* ── App theme (see reference_ui_md_design_system memory) ─────────────── */
.el-dialog.abtm-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.abtm-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.abtm-modal .el-dialog__body { padding: 0; }
.el-dialog.abtm-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.abtm-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.abtm-modal__head-icon {
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
.abtm-modal__head-text { flex: 1; min-width: 0; }
.abtm-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.abtm-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.abtm-modal__close {
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
.abtm-modal__close:hover { background: #E5E7EB; color: #121516; }

.abtm-modal__body { padding: 22px 24px; }

.abtm-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.abtm-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.abtm-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.abtm-field__hint { font-size: 12px; font-weight: 500; color: #6F7677; line-height: 1.4; }

.abtm-input { width: 100%; }
.abtm-input :deep(.el-input__wrapper) { border-radius: 6px; }
.abtm-input--error :deep(.el-input__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

/* ── Resolved batch preview ───────────────────────────────────────────── */
.abtm-preview {
    margin-top: 16px;
    padding: 14px 16px;
    border-radius: 6px;
    background: #F5F6F7;
    border: 1px solid #E5E7EB;
    display: flex;
    flex-direction: column;
    gap: 9px;
}
.abtm-preview__row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.abtm-preview__row--head { padding-bottom: 9px; border-bottom: 1px dashed #E5E7EB; }
.abtm-preview__variety { font-size: 14px; font-weight: 700; color: #121516; }
.abtm-preview__pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #E5E7EB;
    color: #4B5457;
    font-size: 11px;
    font-weight: 700;
    text-transform: capitalize;
}
.abtm-preview__label { font-size: 12px; color: #6F7677; }
.abtm-preview__value { font-size: 12.5px; font-weight: 600; color: #121516; }

.abtm-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.abtm-btn-primary {
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
.abtm-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.abtm-btn-primary:disabled { opacity: 0.5; cursor: default; }
.abtm-btn-outline {
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
.abtm-btn-outline:hover { background: #F5F6F7; }
</style>
