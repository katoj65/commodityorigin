<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Promotion, Lock } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    currency: { type: String, default: 'USD' },
    availableBalance: { type: [Number, String], default: 0 },
});

const emit = defineEmits(['update:modelValue']);

const open = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const presets = [50, 100, 250, 500];

const form = useForm({
    amount: '',
    description: '',
});

const amountTouched = ref(false);
const amountValid = computed(() => {
    const value = Number(form.amount);
    return value > 0 && value <= Number(props.availableBalance);
});
const canSubmit = computed(() => amountValid.value);

function reset() {
    form.reset();
    form.clearErrors();
    amountTouched.value = false;
}

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) reset();
});

function pickPreset(value) {
    form.amount = value;
}

function submit() {
    amountTouched.value = true;
    if (!amountValid.value) return;

    form.post(route('wallet.transfer'), {
        preserveScroll: true,
        onSuccess: () => { open.value = false; reset(); },
    });
}
</script>

<template>
    <el-dialog v-model="open" width="460px" destroy-on-close align-center :show-close="true" class="esc-modal" style="padding: 0">
        <template #header>
            <div class="esc-modal-head">
                <div class="esc-modal-head__icon"><el-icon :size="18"><Promotion /></el-icon></div>
                <div>
                    <div class="esc-modal-eyebrow">Move Funds</div>
                    <div class="esc-modal-title">Transfer to Escrow</div>
                </div>
            </div>
        </template>

        <div class="esc-modal-body">
            <form id="esc-form" class="esc-form" @submit.prevent="submit">
                <div class="esc-field">
                    <label class="esc-field__label">Amount ({{ currency }})</label>
                    <input
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        required
                        placeholder="0.00"
                        class="esc-field__input"
                        @blur="amountTouched = true"
                    >
                    <p v-if="form.errors.amount" class="esc-error">{{ form.errors.amount }}</p>
                    <p v-else-if="amountTouched && !amountValid" class="esc-error">Enter an amount up to your available balance.</p>
                    <p v-else class="esc-hint">Available balance: {{ currency }} {{ Number(availableBalance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
                    <div class="esc-preset-grid">
                        <button
                            v-for="preset in presets"
                            :key="preset"
                            type="button"
                            class="esc-preset-chip"
                            :class="{ 'esc-preset-chip--active': Number(form.amount) === preset }"
                            @click="pickPreset(preset)"
                        >
                            {{ currency }} {{ preset }}
                        </button>
                    </div>
                </div>

                <div class="esc-field">
                    <label class="esc-field__label">Note (optional)</label>
                    <input v-model="form.description" type="text" maxlength="255" placeholder="What's this for?" class="esc-field__input">
                </div>

                <p class="esc-note"><el-icon><Lock /></el-icon> Funds you move here stay in your escrow account until you use them to fund a transaction.</p>

                <p v-if="form.errors.wallet" class="esc-error">{{ form.errors.wallet }}</p>
            </form>
        </div>

        <template #footer>
            <div class="esc-modal-footer">
                <button type="submit" form="esc-form" :disabled="form.processing || !canSubmit" class="esc-modal-btn esc-modal-btn--primary">
                    <el-icon :size="14"><Promotion /></el-icon> {{ form.processing ? 'Transferring…' : 'Transfer to Escrow' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
/* Matches .wal-modal / .dep-modal / .wdr-modal chrome so every wallet
   dialog shares the same look. !important here overrides Element Plus's
   own dialog padding, which lives on the outer .el-dialog element itself. */
:deep(.el-dialog.esc-modal) {
    border-radius: 18px !important;
    padding: 0 !important;
    overflow: hidden !important;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.esc-modal .el-dialog__header) { padding: 0 !important; margin: 0; }
:deep(.el-dialog.esc-modal .el-dialog__body) { padding: 0 !important; }
:deep(.el-dialog.esc-modal .el-dialog__footer) { padding: 0 !important; }

.esc-modal-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
    border-radius: 18px 18px 0 0;
}

.esc-modal-head__icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.esc-modal-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.esc-modal-title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.esc-modal-body { padding: 20px 24px; max-height: 70vh; overflow-y: auto; }

.esc-form { display: flex; flex-direction: column; gap: 16px; }

.esc-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.esc-field__label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; }
.esc-field__input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 0.8125rem;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #fff;
    color: #111827;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.esc-field__input:focus {
    outline: none;
    border-color: #004532;
    box-shadow: 0 0 0 3px rgba(0, 69, 50, 0.08);
}

.esc-error { font-size: 0.6875rem; color: #991b1b; margin: 0; font-weight: 600; }
.esc-hint { font-size: 0.6875rem; color: #9ca3af; margin: 0; font-family: 'IBM Plex Mono', monospace; }
.esc-note { display: flex; align-items: flex-start; gap: 5px; font-size: 0.6875rem; color: #9ca3af; margin: 0; line-height: 1.5; }
.esc-note :deep(.el-icon) { flex-shrink: 0; margin-top: 2px; color: #004532; opacity: .7; }

/* ── Amount presets ──────────────────────────────────────────────────── */
.esc-preset-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; margin-top: 2px; }
.esc-preset-chip {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    color: #374151;
    font-size: 0.75rem;
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
    padding: 8px 4px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease, transform .1s ease;
}
.esc-preset-chip:hover { border-color: #004532; }
.esc-preset-chip:active { transform: scale(.96); }
.esc-preset-chip--active { background: #004532; border-color: #004532; color: #fff; }

/* ── Footer ──────────────────────────────────────────────────────────── */
.esc-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
    border-radius: 0 0 18px 18px;
}

.esc-modal-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s ease, background 0.15s ease;
}

.esc-modal-btn--primary {
    background: linear-gradient(135deg, #004532, #065f46);
    color: #fff;
}

.esc-modal-btn--primary:hover:not(:disabled) { opacity: 0.9; }
.esc-modal-btn--primary:disabled { opacity: 0.5; cursor: not-allowed; }

@media (max-width: 480px) {
    .esc-preset-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
</style>
