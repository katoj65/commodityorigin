<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Box, Close, Promotion } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    offer: { type: Object, default: null },
    authUserId: { type: Number, default: null },
});

const emit = defineEmits(['update:modelValue', 'edit', 'delete']);

const isCreator = computed(() => props.offer?.seller_id === props.authUserId);

const responding = ref(false);
const form = useForm({ message: '' });

function close() {
    responding.value = false;
    emit('update:modelValue', false);
}

function startRespond() {
    form.reset();
    form.clearErrors();
    responding.value = true;
}

function cancelRespond() {
    responding.value = false;
}

function submitResponse() {
    form.post(route('trade.offer.respond', props.offer.id), {
        preserveScroll: true,
        onSuccess: () => {
            responding.value = false;
            emit('update:modelValue', false);
        },
    });
}

function statusLabel(status) {
    return (status || '').charAt(0).toUpperCase() + (status || '').slice(1);
}

function statusTone(status) {
    return {
        open: 'ofr-badge--muted',
        pending: 'ofr-badge--amber',
        confirmed: 'ofr-badge--blue',
        processing: 'ofr-badge--blue',
        shipped: 'ofr-badge--blue',
        delivered: 'ofr-badge--green',
        cancelled: 'ofr-badge--red',
        withdrawn: 'ofr-badge--muted',
    }[status] ?? 'ofr-badge--muted';
}

function formatMoney(amount, currency) {
    return `${currency} ${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
</script>

<template>
    <el-dialog
        :model-value="modelValue"
        width="min(520px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :show-close="false"
        class="ofr-modal"
        @update:model-value="close"
    >
        <template #header>
            <div class="ofr-modal__head">
                <div class="ofr-modal__head-icon">
                    <el-icon :size="18"><Box /></el-icon>
                </div>
                <div class="ofr-modal__head-text">
                    <div class="ofr-modal__eyebrow">Offer</div>
                    <div class="ofr-modal__title">{{ offer?.offer_number || '—' }}</div>
                </div>
                <button type="button" class="ofr-modal__close" aria-label="Close" @click="close">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div v-if="offer" class="ofr-modal__body">
            <div class="ofr-preview__top">
                <span class="ofr-badge" :class="statusTone(offer.status)">{{ statusLabel(offer.status) }}</span>
                <span class="ofr-preview__seller">by {{ offer.seller_name || '—' }}</span>
            </div>

            <div class="ofr-preview__grid">
                <div class="ofr-preview__item"><span>Crop Type</span><strong>{{ offer.crop_type }}</strong></div>
                <div class="ofr-preview__item"><span>Variety</span><strong>{{ offer.variety || '—' }}</strong></div>
                <div class="ofr-preview__item"><span>Grade</span><strong>{{ offer.grade || '—' }}</strong></div>
                <div class="ofr-preview__item"><span>Quantity</span><strong>{{ Number(offer.quantity || 0).toLocaleString() }} kg</strong></div>
                <div class="ofr-preview__item"><span>Unit Price</span><strong>{{ formatMoney(offer.unit_price, offer.currency) }}</strong></div>
                <div class="ofr-preview__item"><span>Total Amount</span><strong>{{ formatMoney(offer.total_amount, offer.currency) }}</strong></div>
            </div>

            <p v-if="offer.notes" class="ofr-preview__notes">{{ offer.notes }}</p>

            <div v-if="responding" class="ofr-respond">
                <label class="ofr-field__label">Your response</label>
                <el-input
                    v-model="form.message"
                    type="textarea"
                    :rows="3"
                    placeholder="Introduce yourself and state your interest…"
                    class="ofr-input"
                />
                <span v-if="form.errors.message" class="ofr-field__error">{{ form.errors.message }}</span>
            </div>
        </div>

        <template #footer>
            <div v-if="offer" class="ofr-modal__footer">
                <template v-if="isCreator">
                    <button type="button" class="ofr-btn ofr-btn--outline" @click="emit('edit', offer)">Edit</button>
                    <button type="button" class="ofr-btn ofr-btn--danger" @click="emit('delete', offer)">Delete</button>
                </template>
                <template v-else-if="responding">
                    <button type="button" class="ofr-btn ofr-btn--outline" @click="cancelRespond">Cancel</button>
                    <button type="button" class="ofr-btn ofr-btn--primary" :disabled="form.processing" @click="submitResponse">
                        {{ form.processing ? 'Sending…' : 'Send Response' }}
                    </button>
                </template>
                <template v-else>
                    <button type="button" class="ofr-btn ofr-btn--primary" @click="startRespond">
                        <el-icon :size="14"><Promotion /></el-icon> Respond to Offer
                    </button>
                </template>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
/* <el-dialog> teleports to <body>, so literal hex values are used here
   (same convention as the orders page modals). */
:deep(.el-dialog.ofr-modal) {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
}
:deep(.el-dialog.ofr-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.ofr-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.ofr-modal .el-dialog__footer) { padding: 0; }

.ofr-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.ofr-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; border: 1px solid #E5E7EB; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ofr-modal__head-text { flex: 1; min-width: 0; }
.ofr-modal__eyebrow { font-size: 11px; line-height: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6F7677; margin-bottom: 2px; }
.ofr-modal__title { font-size: 15px; line-height: 20px; font-weight: 700; color: #121516; }
.ofr-modal__close { width: 32px; height: 32px; border-radius: 6px; border: none; background: transparent; color: #6F7677; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 120ms, color 120ms; }
.ofr-modal__close:hover { background: #F1F2F3; color: #121516; }

.ofr-modal__body { padding: 22px 24px 6px; display: flex; flex-direction: column; gap: 16px; max-height: 65vh; overflow-y: auto; }

.ofr-preview__top { display: flex; align-items: center; gap: 10px; }
.ofr-preview__seller { font-size: 12.5px; color: #6F7677; }
.ofr-preview__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ofr-preview__item { display: flex; flex-direction: column; gap: 3px; background: #F5F6F7; border: 1px solid #E5E7EB; border-radius: 6px; padding: 10px 12px; min-width: 0; }
.ofr-preview__item span { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6F7677; }
.ofr-preview__item strong { font-size: 13.5px; font-weight: 700; color: #121516; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ofr-preview__notes { font-size: 13px; line-height: 1.6; color: #4B5457; background: #F5F6F7; border-radius: 6px; padding: 10px 12px; margin: 0; white-space: pre-wrap; }
.ofr-badge { display: inline-flex; border-radius: 999px; font-size: 11px; font-weight: 600; padding: 4px 10px; flex-shrink: 0; white-space: nowrap; }
.ofr-badge--green { background: var(--dp-secondary-container, #E5FAE7); color: var(--dp-on-secondary-container, #2F6B35); }
.ofr-badge--amber { background: #fef3c7; color: #92400e; }
.ofr-badge--red { background: var(--dp-error-container, #FEEDED); color: #991b1b; }
.ofr-badge--blue { background: #dbeafe; color: #1e40af; }
.ofr-badge--muted { background: var(--dp-surface-container-high, #E5E7EB); color: var(--dp-on-surface-variant, #4B5457); }

.ofr-btn {
    height: 36px;
    padding: 0 16px;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 120ms ease, background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ofr-btn--primary { background: var(--primary); border: 1px solid transparent; color: var(--on-primary); }
.ofr-btn--primary:hover:not(:disabled) { opacity: 0.88; }
.ofr-btn--primary:disabled { opacity: 0.5; cursor: default; }
.ofr-btn--outline { background: var(--surface); border: 1px solid var(--border); color: var(--text); }
.ofr-btn--outline:hover { background: var(--surface-muted); }
.ofr-btn--danger { background: #F85149; border: 1px solid transparent; color: #ffffff; }
.ofr-btn--danger:hover:not(:disabled) { opacity: 0.88; }

.ofr-field__label { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: #121516; }
.ofr-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.ofr-respond { display: flex; flex-direction: column; gap: 6px; }
.ofr-input :deep(.el-textarea__inner) { border-radius: 6px; box-shadow: 0 0 0 1px #E5E7EB inset; background: #F5F6F7; transition: box-shadow 120ms, background 120ms; }
.ofr-input :deep(.el-textarea__inner:hover) { background: #fff; }
.ofr-input :deep(.el-textarea__inner:focus) { background: #fff; box-shadow: 0 0 0 1.5px #000000 inset; }

.ofr-modal__footer {
    --primary: #000000;
    --on-primary: #ffffff;
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --border: #E5E7EB;
    --text: #121516;
    --text-muted: #6F7677;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}

@media (max-width: 479.98px) {
    .ofr-modal__head { padding: 16px 18px; }
    .ofr-modal__body { padding: 18px 18px 6px; }
    .ofr-modal__footer { padding: 14px 18px; flex-wrap: wrap; }
    .ofr-preview__grid { grid-template-columns: 1fr; }
}
</style>