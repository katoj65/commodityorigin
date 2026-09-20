<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import {
    Close, Promotion, Loading, LocationFilled,
    OfficeBuilding, Box, Coin, Clock, ChatDotRound, EditPen,
} from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    offer: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue', 'submitted']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({
    price: '',
    quantity: '',
    incoterm: '',
    message: '',
});

function resetForm() {
    form.reset();
    form.clearErrors();
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.post(route('exchange.offers.submit', props.offer?.recordId), {
        preserveScroll: true,
        onSuccess: () => {
            ElMessage.success(`Offer sent for ${props.offer?.id ?? 'this listing'}.`);
            emit('submitted');
            closeDialog();
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(560px, calc(100vw - 2rem))"
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        transition="dialog-fade-fast"
        class="om-modal"
        @opened="resetForm"
    >
        <template #header>
            <div class="om-head">
                <div class="om-head__icon"><el-icon :size="18"><Promotion /></el-icon></div>
                <div class="om-head__text">
                    <div class="om-eyebrow">Open Listing</div>
                    <div class="om-title">Make an Offer</div>
                </div>
                <button type="button" class="om-close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="om-body">
            <!-- Listing identity strip -->
            <div class="om-lot">
                <div class="om-lot__main">
                    <div class="om-lot__name">{{ offer?.name ?? '—' }}</div>
                    <div class="om-lot__meta">
                        <span class="om-lot__mono">{{ offer?.id ?? '' }}</span>
                        <span v-if="offer?.origin" class="om-lot__origin"><el-icon :size="11"><LocationFilled /></el-icon>{{ offer.origin }}</span>
                    </div>
                </div>
                <span class="om-tag" :class="`om-tag--${offer?.statusTone ?? 'neutral'}`">{{ offer?.status ?? '—' }}</span>
            </div>

            <!-- Deal snapshot -->
            <div class="om-stats">
                <div class="om-stat">
                    <div class="om-stat__label"><el-icon :size="11"><OfficeBuilding /></el-icon>Counterparty</div>
                    <div class="om-stat__value">{{ offer?.counterparty ?? '—' }}</div>
                    <div class="om-stat__sub">{{ offer?.counterpartyNote ?? '' }}</div>
                </div>
                <div class="om-stat">
                    <div class="om-stat__label"><el-icon :size="11"><Box /></el-icon>Quantity</div>
                    <div class="om-stat__value">{{ offer?.qty ?? '—' }}</div>
                </div>
                <div class="om-stat">
                    <div class="om-stat__label"><el-icon :size="11"><Coin /></el-icon>Listed Price</div>
                    <div class="om-stat__value">{{ offer?.price ?? '—' }}</div>
                </div>
                <div class="om-stat">
                    <div class="om-stat__label"><el-icon :size="11"><Clock /></el-icon>Validity</div>
                    <div class="om-stat__value om-stat__value--sm">{{ offer?.validUntil ?? '—' }}</div>
                </div>
            </div>

            <!-- Offer terms form -->
            <div class="om-section-label"><el-icon :size="13"><EditPen /></el-icon>Your Offer Terms</div>
            <div v-if="form.errors.status" class="om-alert">{{ form.errors.status }}</div>
            <div class="om-grid">
                <div class="om-field">
                    <label class="om-field__label"><el-icon :size="13"><Coin /></el-icon>Your Price ($/kg)</label>
                    <el-input v-model="form.price" type="number" min="0" step="0.01" placeholder="e.g. 4.10" class="om-input" :class="{ 'om-input--error': form.errors.price }">
                        <template #prefix>$</template>
                    </el-input>
                    <span v-if="form.errors.price" class="om-field__error">{{ form.errors.price }}</span>
                </div>
                <div class="om-field">
                    <label class="om-field__label"><el-icon :size="13"><Box /></el-icon>Quantity (kg)</label>
                    <el-input v-model="form.quantity" type="number" min="0" step="1" placeholder="e.g. 20000" class="om-input" :class="{ 'om-input--error': form.errors.quantity }" />
                    <span v-if="form.errors.quantity" class="om-field__error">{{ form.errors.quantity }}</span>
                </div>
                <div class="om-field om-field--span2">
                    <label class="om-field__label"><el-icon :size="13"><LocationFilled /></el-icon>Incoterms Port</label>
                    <el-input v-model="form.incoterm" placeholder="e.g. FOB Mombasa" class="om-input" :class="{ 'om-input--error': form.errors.incoterm }" />
                    <span v-if="form.errors.incoterm" class="om-field__error">{{ form.errors.incoterm }}</span>
                </div>
                <div class="om-field om-field--span2">
                    <label class="om-field__label"><el-icon :size="13"><ChatDotRound /></el-icon>Message to Counterparty <small>(optional)</small></label>
                    <el-input v-model="form.message" type="textarea" :rows="3" placeholder="Terms, Incoterms, or notes for this offer..." class="om-input" :class="{ 'om-input--error': form.errors.message }" />
                    <span v-if="form.errors.message" class="om-field__error">{{ form.errors.message }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="om-footer">
                <button type="button" class="om-btn-primary" :disabled="form.processing" @click="submit">
                    <el-icon v-if="form.processing" class="is-loading" :size="14"><Loading /></el-icon>
                    <el-icon v-else :size="14"><Promotion /></el-icon>
                    <span>{{ form.processing ? 'Sending…' : 'Send Offer' }}</span>
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose, matching EditMarketListingDialog.vue's convention:
   an <el-dialog> that ever renders with append-to-body true teleports its
   root out from under any scoped selector, so the dialog shell rules live
   here globally instead. */
.el-dialog.om-modal {
    --el-dialog-padding-primary: 0;
    border-radius: var(--dp-card-radius, 18px);
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(18, 21, 22, 0.18);
}
.el-dialog.om-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.om-modal .el-dialog__body { padding: 0; }
.el-dialog.om-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.om-head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: var(--dp-surface-container-lowest); border-bottom: 1px solid var(--dp-outline-variant); }
.om-head__icon { width: 38px; height: 38px; border-radius: 11px; background: var(--dp-primary-container); color: var(--dp-on-primary-container); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.om-head__text { flex: 1; min-width: 0; }
.om-eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--dp-on-surface-variant); margin-bottom: 1px; }
.om-title { font-size: 1.0625rem; font-weight: 800; color: var(--dp-on-surface); letter-spacing: -0.01em; }
.om-close { width: 28px; height: 28px; border-radius: 8px; border: none; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.12s; }
.om-close:hover { background: var(--dp-surface-container-highest); color: var(--dp-on-surface); }

.om-body { padding: 22px 24px 6px; max-height: 70vh; overflow-y: auto; }

/* Listing identity strip */
.om-lot { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding-bottom: 14px; margin-bottom: 14px; border-bottom: 1px solid var(--dp-outline-variant); }
.om-lot__main { min-width: 0; }
.om-lot__name { font-size: 0.9375rem; font-weight: 800; color: var(--dp-on-surface); letter-spacing: -0.01em; }
.om-lot__meta { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 3px; }
.om-lot__mono { font-family: var(--dp-font-mono); font-size: 0.75rem; color: var(--dp-on-surface-variant); }
.om-lot__origin { display: inline-flex; align-items: center; gap: 3px; font-size: 0.75rem; color: var(--dp-on-surface-variant); }

/* Deal snapshot */
.om-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding: 14px 16px; margin-bottom: 20px; background: var(--dp-surface-container-low); border-radius: 12px; }
.om-stat { display: flex; flex-direction: column; min-width: 0; }
.om-stat__label { display: flex; align-items: center; gap: 4px; font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dp-on-surface-variant); margin-bottom: 4px; white-space: nowrap; }
.om-stat__value { font-size: 0.8125rem; font-weight: 700; color: var(--dp-on-surface); line-height: 1.3; overflow-wrap: break-word; }
.om-stat__value--sm { font-size: 0.75rem; font-weight: 600; }
.om-stat__sub { font-size: 0.6875rem; color: var(--dp-on-surface-variant); margin-top: 2px; }

.om-tag { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 4px 10px; border-radius: 6px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); white-space: nowrap; flex-shrink: 0; }
.om-tag--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.om-tag--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.om-tag--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.om-tag--tertiary { background: var(--dp-tertiary-fixed); color: var(--dp-on-tertiary-fixed); }
.om-tag--accepted { background: color-mix(in srgb, var(--dp-primary) 12%, transparent); color: var(--dp-primary); }

.om-section-label { display: flex; align-items: center; gap: 5px; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-on-surface-variant); margin-bottom: 12px; }

.om-alert { font-size: 0.75rem; font-weight: 600; color: var(--dp-error); background: var(--dp-error-container); border-radius: 8px; padding: 9px 12px; margin-bottom: 14px; }

.om-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.om-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.om-field--span2 { grid-column: span 2; }
.om-field__label { display: flex; align-items: center; gap: 5px; font-size: 0.75rem; font-weight: 700; color: var(--dp-on-surface); }
.om-field__label .el-icon { color: var(--dp-on-surface-variant); }
.om-field__label small { font-weight: 500; color: var(--dp-on-surface-variant); text-transform: none; }

.om-input { width: 100%; }
.om-input :deep(.el-input__wrapper),
.om-input :deep(.el-textarea__inner) { border-radius: 8px; box-shadow: 0 0 0 1px var(--dp-outline-variant) inset; background: var(--dp-surface-container-low); }
.om-input :deep(.el-input__wrapper:hover),
.om-input :deep(.el-textarea__inner:hover) { box-shadow: 0 0 0 1px var(--dp-outline) inset; }
.om-input :deep(.el-input__wrapper.is-focus),
.om-input :deep(.el-textarea__inner:focus) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset; }
.om-input :deep(.el-input__inner),
.om-input :deep(.el-textarea__inner) { color: var(--dp-on-surface); font-family: var(--dp-font-sans); }
.om-input :deep(.el-input__prefix) { margin-right: 4px; color: var(--dp-on-surface-variant); }
.om-input--error :deep(.el-input__wrapper),
.om-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px var(--dp-error) inset; }
.om-field__error { font-size: 0.6875rem; font-weight: 600; color: var(--dp-error); }

.om-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: var(--dp-surface-container-low); border-top: 1px solid var(--dp-outline-variant); }
.om-btn-primary { display: inline-flex; align-items: center; gap: 6px; background: var(--dp-primary); border: 1px solid transparent; color: var(--dp-on-primary); border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 9px 18px; cursor: pointer; transition: opacity 0.15s ease; font-family: var(--dp-font-sans); }
.om-btn-primary:hover { opacity: 0.9; }
.om-btn-primary:disabled { opacity: 0.6; cursor: default; }
.om-btn-primary .is-loading { animation: om-spin 1s linear infinite; }
@keyframes om-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

@media (max-width: 640px) {
    .om-grid { grid-template-columns: 1fr; }
    .om-field--span2 { grid-column: span 1; }
    .om-stats { grid-template-columns: repeat(2, 1fr); }
    .om-lot { flex-direction: column; }
}
</style>
