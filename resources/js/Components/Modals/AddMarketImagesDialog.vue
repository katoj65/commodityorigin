<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Close, Picture, Upload } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    marketId: { type: [Number, String], required: true },
    remainingSlots: { type: Number, default: 3 },
});

const emit = defineEmits(['update:modelValue', 'uploaded']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({ images: [] });
const fileInput = ref(null);
const previews = ref([]);

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
    if (fileInput.value) fileInput.value.value = null;
    previews.value.forEach((p) => URL.revokeObjectURL(p.url));
    previews.value = [];
});

function chooseFiles() {
    fileInput.value?.click();
}

function onFilesChange() {
    const files = Array.from(fileInput.value?.files || []).slice(0, props.remainingSlots);
    previews.value.forEach((p) => URL.revokeObjectURL(p.url));
    previews.value = files.map((file) => ({ file, url: URL.createObjectURL(file) }));
    form.images = files;
}

function removePreview(index) {
    URL.revokeObjectURL(previews.value[index].url);
    previews.value.splice(index, 1);
    form.images = previews.value.map((p) => p.file);
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (!form.images.length) return;
    form.post(route('market.images.store', props.marketId), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            emit('uploaded');
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
        class="ami-modal"
    >
        <template #header>
            <div class="ami-modal__head">
                <div class="ami-modal__head-icon"><el-icon :size="18"><Picture /></el-icon></div>
                <div class="ami-modal__head-text">
                    <div class="ami-modal__eyebrow">Listing Photos</div>
                    <div class="ami-modal__title">Add Images</div>
                </div>
                <button type="button" class="ami-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="ami-modal__body">
            <p class="ami-hint">Up to {{ remainingSlots }} more photo{{ remainingSlots === 1 ? '' : 's' }} can be added to this listing.</p>

            <div class="ami-drop" @click="chooseFiles">
                <input ref="fileInput" type="file" accept="image/*" multiple class="ami-drop__input" @change="onFilesChange" @click.stop>
                <el-icon :size="22"><Upload /></el-icon>
                <span>Click to choose photos</span>
            </div>
            <span v-if="form.errors.images" class="ami-field__error">{{ form.errors.images }}</span>
            <span v-if="form.errors['images.0']" class="ami-field__error">{{ form.errors['images.0'] }}</span>

            <div v-if="previews.length" class="ami-previews">
                <div v-for="(p, i) in previews" :key="p.url" class="ami-preview">
                    <img :src="p.url" alt="Selected photo">
                    <button type="button" class="ami-preview__remove" @click="removePreview(i)">
                        <el-icon :size="12"><Close /></el-icon>
                    </button>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="ami-modal__footer">
                <button type="button" class="ami-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="ami-btn-primary" :disabled="form.processing || !previews.length" @click="submit">
                    <el-icon v-if="!form.processing"><Upload /></el-icon>
                    {{ form.processing ? 'Uploading…' : 'Upload' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so a scoped selector can never
   reach it. Literal dp-palette hex is used throughout this file for the
   same reason — --dp-* custom properties live on .dp-shell and don't
   cascade into teleported content. */
.el-dialog.ami-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(39, 19, 16, 0.22);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}
.el-dialog.ami-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.ami-modal .el-dialog__body { padding: 0; }
.el-dialog.ami-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.ami-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #eeeeee; }
.ami-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(39, 19, 16, 0.08); color: #271310; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ami-modal__head-text { flex: 1; min-width: 0; }
.ami-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #271310; margin-bottom: 1px; }
.ami-modal__title { font-size: 1.0625rem; font-weight: 800; color: #1a1c1c; letter-spacing: -0.01em; }
.ami-modal__close { width: 28px; height: 28px; border-radius: 8px; border: none; background: #f3f3f3; color: #504442; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.12s; }
.ami-modal__close:hover { background: #e8e8e8; color: #1a1c1c; }

.ami-modal__body { padding: 20px 24px 6px; max-height: 70vh; overflow-y: auto; }
.ami-hint { font-size: 0.8125rem; color: #504442; margin: 0 0 14px; }

.ami-drop {
    height: 110px; border: 1.5px dashed #d3c3c0; border-radius: 12px; background: #f9f9f9;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
    color: #504442; font-size: 0.8125rem; font-weight: 600; cursor: pointer; transition: background 0.12s ease;
}
.ami-drop:hover { background: #f3f3f3; }
.ami-drop :deep(.el-icon) { color: #271310; }
.ami-drop__input { display: none; }

.ami-field__error { display: block; font-size: 0.75rem; font-weight: 600; color: #ba1a1a; margin-top: 6px; }

.ami-previews { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 14px; }
.ami-preview { position: relative; aspect-ratio: 1 / 1; border-radius: 10px; overflow: hidden; background: #eeeeee; }
.ami-preview img { width: 100%; height: 100%; object-fit: cover; }
.ami-preview__remove {
    position: absolute; top: 5px; right: 5px; width: 20px; height: 20px; border-radius: 50%;
    border: none; background: rgba(39, 19, 16, 0.85); color: #fff; display: flex; align-items: center; justify-content: center;
    cursor: pointer;
}

.ami-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9f9f9; border-top: 1px solid #eeeeee; }
.ami-btn-primary { background: #271310; border: 1px solid transparent; color: #fff; border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 9px 18px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: opacity 0.15s ease; }
.ami-btn-primary:hover { opacity: 0.9; }
.ami-btn-primary:disabled { opacity: 0.5; cursor: default; }
.ami-btn-outline { background: #fff; border: 1px solid #d3c3c0; color: #1a1c1c; border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 9px 18px; cursor: pointer; transition: background 0.15s ease; }
.ami-btn-outline:hover { background: #f9f9f9; }
</style>
