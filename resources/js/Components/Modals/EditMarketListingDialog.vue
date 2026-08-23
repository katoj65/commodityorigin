<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Close, Edit } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    item: { type: Object, required: true },
});

const emit = defineEmits(['update:modelValue', 'updated']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function fieldsFromItem() {
    return {
        name: props.item.name || '',
        origin: props.item.origin || '',
        type: props.item.type || '',
        process: props.item.process || '',
        price_per_kg: props.item.price_per_kg ?? '',
        quantity: props.item.quantity ?? '',
        notes: props.item.notes || '',
    };
}

const form = useForm(fieldsFromItem());

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(fieldsFromItem());
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.patch(route('market.update', props.item.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            emit('updated');
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(560px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="eml-modal"
    >
        <template #header>
            <div class="eml-modal__head">
                <div class="eml-modal__head-icon"><el-icon :size="18"><Edit /></el-icon></div>
                <div class="eml-modal__head-text">
                    <div class="eml-modal__eyebrow">Your Listing</div>
                    <div class="eml-modal__title">Edit Details</div>
                </div>
                <button type="button" class="eml-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="eml-modal__body">
            <div class="eml-grid">
                <div class="eml-field eml-field--span2">
                    <label class="eml-field__label">Name</label>
                    <el-input v-model="form.name" placeholder="e.g. Premium Arabica AA" class="eml-input" :class="{ 'eml-input--error': form.errors.name }" />
                    <span v-if="form.errors.name" class="eml-field__error">{{ form.errors.name }}</span>
                </div>

                <div class="eml-field">
                    <label class="eml-field__label">Origin</label>
                    <el-input v-model="form.origin" placeholder="e.g. Kampala" class="eml-input" :class="{ 'eml-input--error': form.errors.origin }" />
                    <span v-if="form.errors.origin" class="eml-field__error">{{ form.errors.origin }}</span>
                </div>

                <div class="eml-field">
                    <label class="eml-field__label">Type</label>
                    <el-input v-model="form.type" placeholder="e.g. Arabica" class="eml-input" :class="{ 'eml-input--error': form.errors.type }" />
                    <span v-if="form.errors.type" class="eml-field__error">{{ form.errors.type }}</span>
                </div>

                <div class="eml-field">
                    <label class="eml-field__label">Process</label>
                    <el-input v-model="form.process" placeholder="e.g. Washed" class="eml-input" :class="{ 'eml-input--error': form.errors.process }" />
                    <span v-if="form.errors.process" class="eml-field__error">{{ form.errors.process }}</span>
                </div>

                <div class="eml-field">
                    <label class="eml-field__label">Price / kg</label>
                    <el-input v-model="form.price_per_kg" type="number" min="0" step="0.01" class="eml-input" :class="{ 'eml-input--error': form.errors.price_per_kg }">
                        <template #prefix>$</template>
                    </el-input>
                    <span v-if="form.errors.price_per_kg" class="eml-field__error">{{ form.errors.price_per_kg }}</span>
                </div>

                <div class="eml-field">
                    <label class="eml-field__label">Quantity (kg)</label>
                    <el-input v-model="form.quantity" type="number" min="0" step="1" class="eml-input" :class="{ 'eml-input--error': form.errors.quantity }" />
                    <span v-if="form.errors.quantity" class="eml-field__error">{{ form.errors.quantity }}</span>
                </div>

                <div class="eml-field eml-field--span2">
                    <label class="eml-field__label">Description <small>(optional)</small></label>
                    <el-input v-model="form.notes" type="textarea" :rows="3" placeholder="Describe this lot for buyers" class="eml-input" :class="{ 'eml-input--error': form.errors.notes }" />
                    <span v-if="form.errors.notes" class="eml-field__error">{{ form.errors.notes }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="eml-modal__footer">
                <button type="button" class="eml-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="eml-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so a scoped selector can never
   reach it. Literal dp-palette hex is used throughout for the same
   reason — --dp-* custom properties don't cascade into teleported content. */
.el-dialog.eml-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(39, 19, 16, 0.22);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}
.el-dialog.eml-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.eml-modal .el-dialog__body { padding: 0; }
.el-dialog.eml-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.eml-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #eeeeee; }
.eml-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(39, 19, 16, 0.08); color: #271310; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.eml-modal__head-text { flex: 1; min-width: 0; }
.eml-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #271310; margin-bottom: 1px; }
.eml-modal__title { font-size: 1.0625rem; font-weight: 800; color: #1a1c1c; letter-spacing: -0.01em; }
.eml-modal__close { width: 28px; height: 28px; border-radius: 8px; border: none; background: #f3f3f3; color: #504442; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.12s; }
.eml-modal__close:hover { background: #e8e8e8; color: #1a1c1c; }

.eml-modal__body { padding: 22px 24px 6px; max-height: 70vh; overflow-y: auto; }

.eml-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.eml-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.eml-field--span2 { grid-column: span 2; }
.eml-field__label { font-size: 0.75rem; font-weight: 700; color: #1a1c1c; }
.eml-field__label small { font-weight: 500; color: #827472; text-transform: none; }
.eml-field__error { font-size: 0.75rem; font-weight: 600; color: #ba1a1a; line-height: 1.4; }

.eml-input { width: 100%; }
.eml-input--error :deep(.el-input__wrapper),
.eml-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #ba1a1a inset !important; }
.eml-input :deep(.el-input__prefix) { margin-right: 4px; color: #504442; }

.eml-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9f9f9; border-top: 1px solid #eeeeee; }
.eml-btn-primary { background: #271310; border: 1px solid transparent; color: #fff; border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 9px 18px; cursor: pointer; transition: opacity 0.15s ease; }
.eml-btn-primary:hover { opacity: 0.9; }
.eml-btn-primary:disabled { opacity: 0.6; cursor: default; }
.eml-btn-outline { background: #fff; border: 1px solid #d3c3c0; color: #1a1c1c; border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 9px 18px; cursor: pointer; transition: background 0.15s ease; }
.eml-btn-outline:hover { background: #f9f9f9; }

@media (max-width: 640px) {
    .eml-grid { grid-template-columns: 1fr; }
    .eml-field--span2 { grid-column: span 1; }
}
</style>
