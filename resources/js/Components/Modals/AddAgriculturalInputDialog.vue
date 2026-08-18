<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Box, Close, Plus, Upload } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'created']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
        name: '',
        category: '',
        tag: '',
        description: '',
        price: '',
        stock_quantity: '',
        unit: '',
        manufacturer: '',
        status: 'active',
        image: null,
    };
}

const form = useForm(emptyForm());
const fileInput = ref(null);
const previewUrl = ref('');

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();
    if (fileInput.value) fileInput.value.value = null;
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = '';
});

function chooseFile() {
    fileInput.value?.click();
}

function onFileChange() {
    const file = fileInput.value?.files?.[0] || null;
    form.image = file;

    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = file ? URL.createObjectURL(file) : '';
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.post(route('farm.inputs.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            emit('created');
        },
    });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(680px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="aai-modal"
    >
        <template #header>
            <div class="aai-modal__head">
                <div class="aai-modal__head-icon">
                    <el-icon :size="18"><Box /></el-icon>
                </div>
                <div class="aai-modal__head-text">
                    <div class="aai-modal__eyebrow">Farm Workspace</div>
                    <div class="aai-modal__title">Add Input</div>
                </div>
                <button type="button" class="aai-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="aai-modal__body">
            <div class="aai-photo" @click="chooseFile">
                <input ref="fileInput" type="file" accept="image/*" class="aai-photo__input" @change="onFileChange" @click.stop>
                <img v-if="previewUrl" :src="previewUrl" alt="Preview" class="aai-photo__preview">
                <div v-else class="aai-photo__placeholder">
                    <el-icon><Upload /></el-icon>
                    <span>Upload a product photo (optional)</span>
                </div>
            </div>
            <span v-if="form.errors.image" class="aai-field__error">{{ form.errors.image }}</span>

            <div class="aai-grid">
                <div class="aai-field aai-field--span2">
                    <label class="aai-field__label">Name</label>
                    <el-input v-model="form.name" placeholder="e.g. NPK 17-17-17 Compound Fertilizer" class="aai-input" :class="{ 'aai-input--error': form.errors.name }" />
                    <span v-if="form.errors.name" class="aai-field__error">{{ form.errors.name }}</span>
                </div>

                <div class="aai-field">
                    <label class="aai-field__label">Category</label>
                    <el-select v-model="form.category" placeholder="Select category" class="aai-input" :class="{ 'aai-input--error': form.errors.category }">
                        <el-option label="Medicine" value="medicine" />
                        <el-option label="Fertilizer" value="fertilizer" />
                    </el-select>
                    <span v-if="form.errors.category" class="aai-field__error">{{ form.errors.category }}</span>
                </div>

                <div class="aai-field">
                    <label class="aai-field__label">Tag</label>
                    <el-input v-model="form.tag" placeholder="e.g. Fungicide, NPK Blend" class="aai-input" :class="{ 'aai-input--error': form.errors.tag }" />
                    <span v-if="form.errors.tag" class="aai-field__error">{{ form.errors.tag }}</span>
                </div>

                <div class="aai-field">
                    <label class="aai-field__label">Price</label>
                    <el-input v-model="form.price" type="number" min="0.01" step="0.01" placeholder="e.g. 45.00" class="aai-input" :class="{ 'aai-input--error': form.errors.price }">
                        <template #prefix>Shs.</template>
                    </el-input>
                    <span v-if="form.errors.price" class="aai-field__error">{{ form.errors.price }}</span>
                </div>

                <div class="aai-field">
                    <label class="aai-field__label">Unit</label>
                    <el-input v-model="form.unit" placeholder="e.g. kg, litre, bag (50kg)" class="aai-input" :class="{ 'aai-input--error': form.errors.unit }" />
                    <span v-if="form.errors.unit" class="aai-field__error">{{ form.errors.unit }}</span>
                </div>

                <div class="aai-field">
                    <label class="aai-field__label">Stock Quantity</label>
                    <el-input v-model="form.stock_quantity" type="number" min="0" placeholder="e.g. 120" class="aai-input" :class="{ 'aai-input--error': form.errors.stock_quantity }" />
                    <span v-if="form.errors.stock_quantity" class="aai-field__error">{{ form.errors.stock_quantity }}</span>
                </div>

                <div class="aai-field">
                    <label class="aai-field__label">Manufacturer <small>(optional)</small></label>
                    <el-input v-model="form.manufacturer" placeholder="e.g. Yara Uganda" class="aai-input" :class="{ 'aai-input--error': form.errors.manufacturer }" />
                    <span v-if="form.errors.manufacturer" class="aai-field__error">{{ form.errors.manufacturer }}</span>
                </div>

                <div class="aai-field aai-field--span2">
                    <label class="aai-field__label">Description <small>(optional)</small></label>
                    <el-input v-model="form.description" type="textarea" :rows="3" placeholder="What this input is used for" class="aai-input" :class="{ 'aai-input--error': form.errors.description }" />
                    <span v-if="form.errors.description" class="aai-field__error">{{ form.errors.description }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="aai-modal__footer">
                <button type="button" class="aai-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="aai-btn-primary" :disabled="form.processing" @click="submit">
                    <el-icon v-if="!form.processing"><Plus /></el-icon>
                    {{ form.processing ? 'Saving…' : 'Add Input' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so it never carries this SFC's
   scope attribute — a scoped (or :deep()) selector can never reach it. */
.el-dialog.aai-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}
.el-dialog.aai-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.aai-modal .el-dialog__body { padding: 0; }
.el-dialog.aai-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.aai-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}
.aai-modal__head-icon {
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
.aai-modal__head-text { flex: 1; min-width: 0; }
.aai-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}
.aai-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}
.aai-modal__close {
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
.aai-modal__close:hover { background: #e5e7eb; color: #111827; }

.aai-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
}

.aai-photo {
    height: 140px;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    background: #fafbfc;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
    margin-bottom: 6px;
    transition: background 0.12s ease;
}
.aai-photo:hover { background: #f3f4f6; }
.aai-photo__input { display: none; }
.aai-photo__preview { width: 100%; height: 100%; object-fit: cover; }
.aai-photo__placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: #6b7280;
    font-size: 0.8125rem;
    font-weight: 600;
}
.aai-photo__placeholder .el-icon { color: #145c42; font-size: 20px; }

.aai-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-top: 16px;
}
.aai-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.aai-field--span2 { grid-column: span 2; }
.aai-field__label { font-size: 0.75rem; font-weight: 700; color: #111827; }
.aai-field__label small { font-weight: 500; color: #9ca3af; text-transform: none; }
.aai-field__error { font-size: 0.75rem; font-weight: 600; color: #dc2626; line-height: 1.4; }

.aai-input { width: 100%; }
.aai-input--error :deep(.el-input__wrapper),
.aai-input--error :deep(.el-select__wrapper),
.aai-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}
.aai-input :deep(.el-input__prefix) { margin-right: 4px; color: #6b7280; }

.aai-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}
.aai-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.aai-btn-primary:hover { opacity: 0.9; }
.aai-btn-primary:disabled { opacity: 0.6; cursor: default; }
.aai-btn-outline {
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
.aai-btn-outline:hover { background: #f8fafc; }

@media (max-width: 640px) {
    .aai-grid { grid-template-columns: 1fr; }
    .aai-field--span2 { grid-column: span 1; }
}
</style>
