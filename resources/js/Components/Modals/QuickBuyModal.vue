<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import { Promotion } from '@element-plus/icons-vue';
import InputError from '@/Components/InputError.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    cropGrades: { type: Array,   default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'success']);


const form = useForm({
    crop_type: '',
    variety:     '',
    grade:       '',
    amount:      '',
    quantity:    '',
    notes:       '',
});

const bidTotal = computed(() => {
    const qty = Number(form.quantity) || 0;
    const amt = Number(form.amount) || 0;
    return (qty * amt).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

const close = () => {
    form.reset();
    form.clearErrors();
    emit('update:modelValue', false);
};

const submit = () => {
    form.post(route('lot.request.store'), {
        preserveScroll: true,
        preserveState:  true,
        onSuccess: () => {
            ElMessage.success('Saved successfully.');
            emit('success');
            close();
        },
        onError: () => {
            emit('update:modelValue', true);
        },
    });
};
</script>

<template>
    <el-dialog
        :model-value="modelValue"
        width="min(520px, calc(100vw - 2rem))"
        class="quick-buy-dialog"
        modal-class="quick-buy-overlay"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="true"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #header>
            <div class="pr-8">
                <div class="qb-eyebrow">Exchange · Quick Buy</div>
                <div class="qb-title">Request Lot</div>
                <p class="qb-sub">Submit your bid for a coffee lot on the open market.</p>
            </div>
        </template>

        <form class="qb-body" @submit.prevent="submit">

            <div class="qb-scroll-wrap">
                <el-form label-position="top" class="qb-section">

                    <el-form-item label="Coffee Type">
                        <el-select v-model="form.crop_type" placeholder="Select type" class="!w-full">
                            <el-option label="Arabica"  value="arabica" />
                            <el-option label="Robusta"  value="robusta" />
                            <el-option label="Liberica" value="liberica" />
                        </el-select>
                        <InputError :message="form.errors.crop_type" class="qb-error-field mt-1" />
                    </el-form-item>

                    <el-form-item label="Variety">
                        <el-input v-model="form.variety" placeholder="e.g. Bourbon, SL28, Typica" />
                        <InputError :message="form.errors.variety" class="qb-error-field mt-1" />
                    </el-form-item>

                    <el-form-item label="Grade">
                        <el-select v-model="form.grade" placeholder="Select grade" class="!w-full">
                            <el-option
                                v-for="grade in props.cropGrades"
                                :key="grade.slug"
                                :label="grade.name"
                                :value="grade.slug"
                            />
                        </el-select>
                        <InputError :message="form.errors.grade" class="qb-error-field mt-1" />
                    </el-form-item>

                    <el-form-item label="Bid Price per kg (Shs.)">
                        <el-input v-model="form.amount" type="number" step="0.01" placeholder="e.g. 12.00" />
                        <InputError :message="form.errors.bid_amount" class="qb-error-field mt-1" />
                    </el-form-item>

                    <el-form-item label="Quantity (kg)">
                        <el-input v-model="form.quantity" type="number" step="1" placeholder="Min. 60 kg" />
                        <InputError :message="form.errors.quantity" class="qb-error-field mt-1" />
                    </el-form-item>

                    <el-form-item label="Notes">
                        <el-input v-model="form.notes" type="textarea" :rows="3" resize="none" placeholder="Delivery preferences, questions for the seller…" />
                        <InputError :message="form.errors.notes" class="qb-error-field mt-1" />
                    </el-form-item>

                </el-form>
            </div>


        </form>

        <template #footer>
            <div class="qb-footer">

                <button
                    type="button"
                    class="qb-btn qb-btn--primary"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <el-icon><Promotion /></el-icon>
                    {{ form.processing ? 'Submitting…' : 'Submit' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
/* ── Dialog shell ── */
:global(.quick-buy-overlay.el-overlay) { overflow: hidden !important; }
:deep(.quick-buy-dialog) { border-radius:20px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column; }
:deep(.quick-buy-dialog .el-dialog__header) { margin-right:0;padding:20px 24px 8px;flex-shrink:0; }
:deep(.quick-buy-dialog .el-dialog__body)   { padding:0;flex:1 1 0;min-height:0;overflow-y:auto; }
:deep(.quick-buy-dialog .el-dialog__footer) { padding:0;flex-shrink:0; }

/* ── Header ── */
.qb-eyebrow { font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#6b7280; }
.qb-title   { margin-top:4px;font-size:20px;font-weight:800;letter-spacing:-.02em;color:#111827; }
.qb-sub     { margin-top:4px;font-size:13px;color:#6b7280;line-height:1.5; }

/* ── Form body ── */
.qb-body { color:#191c1e; }
.qb-scroll-wrap { max-height:450px;overflow-y:auto; }
.qb-section { padding:1.25rem 1.5rem; }

:deep(.quick-buy-dialog .el-form-item) { margin-bottom:.875rem;overflow:visible !important; }
:deep(.quick-buy-dialog .el-form-item__label) { padding-bottom:.3rem;color:#374151;font-size:13px;font-weight:600;line-height:1.4; }
:deep(.quick-buy-dialog .el-form-item__content) { align-items:flex-start;flex-wrap:wrap;overflow:visible !important; }
:deep(.quick-buy-dialog .el-input__wrapper),
:deep(.quick-buy-dialog .el-select__wrapper) { border-radius:8px;box-shadow:0 0 0 1px #dbe1e6 inset !important;min-height:40px; }
:deep(.quick-buy-dialog .el-textarea__inner) { border-radius:8px;box-shadow:0 0 0 1px #dbe1e6 inset !important;min-height:72px !important;padding:.5rem .7rem;font-size:14px;color:#111827; }
:deep(.quick-buy-dialog .el-input__inner),
:deep(.quick-buy-dialog .el-select__selected-item) { font-size:14px;color:#111827; }
:deep(.quick-buy-dialog .el-input__wrapper:hover),
:deep(.quick-buy-dialog .el-select__wrapper:hover),
:deep(.quick-buy-dialog .el-textarea__inner:hover) { box-shadow:0 0 0 1px #cdd5dc inset !important; }
:deep(.quick-buy-dialog .el-input__wrapper.is-focus),
:deep(.quick-buy-dialog .el-select__wrapper.is-focused),
:deep(.quick-buy-dialog .el-textarea__inner:focus),
:deep(.quick-buy-dialog .el-input__wrapper:focus-within),
:deep(.quick-buy-dialog .el-select__wrapper:focus-within) { box-shadow:0 0 0 1px #004532 inset !important;outline:none !important; }
:deep(.quick-buy-dialog .el-select),
:deep(.quick-buy-dialog .el-input) { width:100%; }

.qb-error-field { width:100%;flex-basis:100%; }
.qb-error-field :deep(p) { font-size:.75rem !important;line-height:1.3;font-weight:600; }

/* ── Summary ── */
.qb-summary {
    border-top:1px solid #e6e8ea;padding:1rem 1.5rem;display:grid;gap:8px;background:#f7f9fb;
}
.qb-summary__row { display:flex;justify-content:space-between;align-items:center;font-size:13px; }
.qb-summary__row span   { color:#6b7280; }
.qb-summary__row strong { color:#111827;font-weight:700; }
.qb-summary__divider    { height:1px;background:#e6e8ea;margin:2px 0; }
.qb-summary__row--total { font-size:14px; }
.qb-summary__row--total span   { font-weight:700;color:#111827; }
.qb-summary__row--total strong { font-size:16px;font-weight:800; }
.qb-accent { color:#004532 !important; }

/* ── Footer ── */
.qb-footer {
    display:flex;justify-content:flex-end;gap:10px;
    padding:12px 24px 20px;background:#fff;border-top:1px solid #e6e8ea;
}
.qb-btn {
    font-family:'Manrope',system-ui,sans-serif;font-size:14px;font-weight:700;
    border-radius:.5rem;padding:10px 22px;cursor:pointer;
    display:inline-flex;align-items:center;gap:6px;
    text-decoration:none;transition:opacity .15s,background .15s;border:none;
}
.qb-btn--primary { background:linear-gradient(135deg,#004532,#065f46);color:#fff; }
.qb-btn--primary:hover    { opacity:.9; }
.qb-btn--primary:disabled { opacity:.55;cursor:not-allowed; }
.qb-btn--ghost { background:transparent;color:#6b7280;border:1px solid #d1d5db; }
.qb-btn--ghost:hover { background:#f7f9fb;color:#111827; }
</style>
