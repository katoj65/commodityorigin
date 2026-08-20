<script setup>
import { computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Goods } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    item: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue']);

const page = usePage();
const currencyOptions = computed(() => page.props.currencies ?? []);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const isEdit = computed(() => Boolean(props.item));

function emptyForm() {
    return {
        name: '',
        category: '',
        description: '',
        price: '',
        currency_code: '',
        quantity: 0,
        unit: '',
        notes: '',
    };
}

function formFromItem(item) {
    return {
        name: item.name ?? '',
        category: item.category ?? '',
        description: item.description ?? '',
        price: item.price ?? '',
        currency_code: item.currency_code ?? '',
        quantity: item.quantity ?? 0,
        unit: item.unit ?? '',
        notes: item.notes ?? '',
    };
}

const form = useForm(emptyForm());

const selectedCurrencySymbol = computed(() => currencyOptions.value.find((c) => c.code === form.currency_code)?.symbol ?? '');

watch(() => props.modelValue, (open) => {
    if (!open) return;
    const defaults = isEdit.value ? formFromItem(props.item) : emptyForm();
    form.defaults(defaults);
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (isEdit.value) {
        form.patch(route('store.items.update', props.item.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeDialog();
                ElNotification({ title: 'Item Updated', message: "The item's details were saved.", type: 'success', duration: 3200, offset: 84 });
            },
        });
        return;
    }

    form.post(route('store.items.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({ title: 'Item Added', message: 'The new item was added to your store.', type: 'success', duration: 3200, offset: 84 });
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(600px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="asi-modal"
    >
        <template #header>
            <div class="asi-modal__head">
                <div class="asi-modal__head-icon">
                    <el-icon :size="18"><Goods /></el-icon>
                </div>
                <div class="asi-modal__head-text">
                    <div class="asi-modal__eyebrow">Store Inventory</div>
                    <div class="asi-modal__title">{{ isEdit ? 'Edit Item' : 'Add Item' }}</div>
                </div>
                <button type="button" class="asi-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="asi-modal__body">
            <div class="asi-field asi-field--span2">
                <label class="asi-field__label">Item Name</label>
                <el-input v-model="form.name" placeholder="e.g. Washed Arabica AA" class="asi-input" :class="{ 'asi-input--error': form.errors.name }" />
                <span v-if="form.errors.name" class="asi-field__error">{{ form.errors.name }}</span>
            </div>

            <div class="asi-field asi-field--span2">
                <label class="asi-field__label">Category <small>(optional)</small></label>
                <el-input v-model="form.category" placeholder="e.g. Green Coffee" class="asi-input" :class="{ 'asi-input--error': form.errors.category }" />
                <span v-if="form.errors.category" class="asi-field__error">{{ form.errors.category }}</span>
            </div>

            <div class="asi-divider"><span>Pricing &amp; Stock</span></div>

            <div class="asi-grid">
                <div class="asi-field">
                    <label class="asi-field__label">Currency <small>(optional)</small></label>
                    <el-select v-model="form.currency_code" filterable clearable placeholder="Select currency" class="asi-input" :class="{ 'asi-input--error': form.errors.currency_code }">
                        <el-option v-for="option in currencyOptions" :key="option.code" :label="`${option.code} — ${option.name}`" :value="option.code">
                            <span class="asi-currency-option">
                                <span class="asi-currency-option__code">{{ option.code }}</span>
                                <span class="asi-currency-option__name">{{ option.name }}</span>
                                <span class="asi-currency-option__symbol">{{ option.symbol }}</span>
                            </span>
                        </el-option>
                    </el-select>
                    <span v-if="form.errors.currency_code" class="asi-field__error">{{ form.errors.currency_code }}</span>
                </div>

                <div class="asi-field">
                    <label class="asi-field__label">Price</label>
                    <el-input v-model="form.price" placeholder="0.00" class="asi-input" :class="{ 'asi-input--error': form.errors.price }">
                        <template v-if="selectedCurrencySymbol" #prefix>{{ selectedCurrencySymbol }}</template>
                    </el-input>
                    <span v-if="form.errors.price" class="asi-field__error">{{ form.errors.price }}</span>
                </div>

                <div class="asi-field">
                    <label class="asi-field__label">Quantity</label>
                    <el-input-number v-model="form.quantity" :min="0" controls-position="right" class="asi-input asi-input--number" :class="{ 'asi-input--error': form.errors.quantity }" />
                    <span v-if="form.errors.quantity" class="asi-field__error">{{ form.errors.quantity }}</span>
                </div>

                <div class="asi-field">
                    <label class="asi-field__label">Unit <small>(optional)</small></label>
                    <el-input v-model="form.unit" placeholder="e.g. kg, bags" class="asi-input" :class="{ 'asi-input--error': form.errors.unit }" />
                    <span v-if="form.errors.unit" class="asi-field__error">{{ form.errors.unit }}</span>
                </div>
            </div>

            <div class="asi-divider"><span>Details</span></div>

            <div class="asi-field">
                <label class="asi-field__label">Description <small>(optional)</small></label>
                <el-input v-model="form.description" type="textarea" :rows="2" placeholder="Describe this item" class="asi-input" :class="{ 'asi-input--error': form.errors.description }" />
                <span v-if="form.errors.description" class="asi-field__error">{{ form.errors.description }}</span>
            </div>

            <div class="asi-field">
                <label class="asi-field__label">Notes <small>(optional)</small></label>
                <el-input v-model="form.notes" type="textarea" :rows="2" placeholder="Any additional detail" class="asi-input" :class="{ 'asi-input--error': form.errors.notes }" />
                <span v-if="form.errors.notes" class="asi-field__error">{{ form.errors.notes }}</span>
            </div>
        </div>

        <template #footer>
            <div class="asi-modal__footer">
                <button type="button" class="asi-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="asi-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : (isEdit ? 'Save Changes' : 'Add Item') }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.asi-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}
.el-dialog.asi-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.asi-modal .el-dialog__body { padding: 0; }
.el-dialog.asi-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.asi-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}
.asi-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(20, 92, 66, 0.08);
    color: #145c42;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.asi-modal__head-text { flex: 1; min-width: 0; }
.asi-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #145c42;
    margin-bottom: 1px;
}
.asi-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -0.01em; }
.asi-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}
.asi-modal__close:hover { background: #e5e7eb; color: #111827; }

.asi-modal__body { padding: 22px 24px 8px; max-height: 72vh; overflow-y: auto; }

.asi-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 4px 0 14px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
}
.asi-divider::before,
.asi-divider::after { content: ''; flex: 1; height: 1px; background: #f3f4f6; }

.asi-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.asi-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.asi-field--span2 { grid-column: span 2; }

.asi-field__label { font-size: 0.75rem; font-weight: 700; color: #111827; }
.asi-field__label small { font-weight: 500; color: #9ca3af; text-transform: none; }
.asi-field__error { font-size: 0.75rem; font-weight: 600; color: #dc2626; line-height: 1.4; }

.asi-input { width: 100%; }
.asi-input--number :deep(.el-input__wrapper) { padding-left: 12px; }
.asi-input--error :deep(.el-input__wrapper),
.asi-input--error :deep(.el-select__wrapper),
.asi-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

.asi-currency-option { display: flex; align-items: center; gap: 8px; }
.asi-currency-option__code { font-weight: 700; color: #111827; }
.asi-currency-option__name { flex: 1; color: #6b7280; font-size: 0.8125rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.asi-currency-option__symbol { color: #9ca3af; font-size: 0.75rem; }

.asi-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}
.asi-btn-primary {
    background: linear-gradient(135deg, #145c42, #0d3d2c);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.asi-btn-primary:hover { opacity: 0.9; }
.asi-btn-primary:disabled { opacity: 0.6; cursor: default; }
.asi-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.asi-btn-outline:hover { background: #f8fafc; }

@media (max-width: 640px) {
    .asi-grid { grid-template-columns: 1fr; }
}
</style>
