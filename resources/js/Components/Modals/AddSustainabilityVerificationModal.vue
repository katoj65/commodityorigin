<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, CircleCheck } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    lotId: { type: [Number, String], required: true },
    options: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const statusOptions = ['VERIFIED', 'PASSED', 'AUDITED', 'PENDING', 'FLAGGED'];

const form = useForm({ slug: '', item: '', description: '', status: 'PENDING' });

function applyMetadata(slug) {
    const option = props.options.find((o) => o.slug === slug);
    if (!option) return;
    form.item = option.name;
    form.description = option.description || '';
}

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (!form.item) return;

    form.post(route('lot.sustainability-verifications.store', props.lotId), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            form.reset();
            form.clearErrors();
            ElNotification({ title: 'Verification Added', message: 'The sustainability verification was added to this lot.', type: 'success', duration: 3200, offset: 84 });
            window.location.reload();
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
        class="asvm-modal"
    >
        <template #header>
            <div class="asvm-modal__head">
                <div class="asvm-modal__head-icon">
                    <el-icon :size="18"><CircleCheck /></el-icon>
                </div>
                <div class="asvm-modal__head-text">
                    <div class="asvm-modal__eyebrow">Lot</div>
                    <div class="asvm-modal__title">Add Sustainability Verification</div>
                </div>
                <button type="button" class="asvm-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="asvm-modal__body">
            <div class="asvm-field">
                <label class="asvm-field__label">Standard / Requirement</label>
                <el-select
                    v-model="form.slug"
                    placeholder="Select a standard or regulation"
                    filterable
                    class="asvm-select"
                    :class="{ 'asvm-input--error': form.errors.item }"
                    @change="applyMetadata"
                >
                    <el-option v-for="o in options" :key="o.slug" :label="o.name" :value="o.slug" />
                </el-select>
                <span v-if="form.errors.item" class="asvm-field__error">{{ form.errors.item }}</span>
            </div>

            <div class="asvm-field">
                <label class="asvm-field__label">Description</label>
                <el-input v-model="form.description" type="textarea" :rows="3" placeholder="What was checked and how it was verified" />
                <span v-if="form.errors.description" class="asvm-field__error">{{ form.errors.description }}</span>
            </div>

            <div class="asvm-field">
                <label class="asvm-field__label">Status</label>
                <el-select v-model="form.status" class="asvm-select">
                    <el-option v-for="s in statusOptions" :key="s" :label="s" :value="s" />
                </el-select>
            </div>
        </div>

        <template #footer>
            <div class="asvm-modal__footer">
                <button type="button" class="asvm-btn-primary" :disabled="form.processing || !form.item" @click="submit">
                    {{ form.processing ? 'Adding…' : 'Add Verification' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* ── App theme (see reference_ui_md_design_system memory) ─────────────── */
.el-dialog.asvm-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.asvm-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.asvm-modal .el-dialog__body { padding: 0; }
.el-dialog.asvm-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.asvm-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.asvm-modal__head-icon {
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
.asvm-modal__head-text { flex: 1; min-width: 0; }
.asvm-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.asvm-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.asvm-modal__close {
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
.asvm-modal__close:hover { background: #E5E7EB; color: #121516; }

.asvm-modal__body { padding: 22px 24px; display: flex; flex-direction: column; gap: 16px; }

.asvm-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.asvm-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.asvm-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.asvm-select { width: 100%; }
.asvm-select :deep(.el-select__wrapper) { border-radius: 6px; }
.asvm-input--error :deep(.el-select__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.asvm-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.asvm-btn-primary {
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
.asvm-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.asvm-btn-primary:disabled { opacity: 0.5; cursor: default; }
</style>
