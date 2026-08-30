<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Close, Picture, Upload } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    lotId: { type: [Number, String], required: true },
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
    form.post(route('lot.images.store', props.lotId), {
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
        class="ali-modal"
    >
        <template #header>
            <div class="ali-modal__head">
                <div class="ali-modal__head-icon"><el-icon :size="18"><Picture /></el-icon></div>
                <div class="ali-modal__head-text">
                    <div class="ali-modal__eyebrow">Lot Photos</div>
                    <div class="ali-modal__title">Add Images</div>
                </div>
                <button type="button" class="ali-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="ali-modal__body">
            <p class="ali-hint">Up to {{ remainingSlots }} more photo{{ remainingSlots === 1 ? '' : 's' }} can be added to this lot.</p>

            <div class="ali-drop" @click="chooseFiles">
                <input ref="fileInput" type="file" accept="image/*" multiple class="ali-drop__input" @change="onFilesChange" @click.stop>
                <el-icon :size="22"><Upload /></el-icon>
                <span>Click to choose photos</span>
            </div>
            <span v-if="form.errors.images" class="ali-field__error">{{ form.errors.images }}</span>
            <span v-if="form.errors['images.0']" class="ali-field__error">{{ form.errors['images.0'] }}</span>

            <div v-if="previews.length" class="ali-previews">
                <div v-for="(p, i) in previews" :key="p.url" class="ali-preview">
                    <img :src="p.url" alt="Selected photo">
                    <button type="button" class="ali-preview__remove" @click="removePreview(i)">
                        <el-icon :size="12"><Close /></el-icon>
                    </button>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="ali-modal__footer">
                <button type="button" class="ali-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="ali-btn-primary" :disabled="form.processing || !previews.length" @click="submit">
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
   reach it. */
.el-dialog.ali-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 10px;
    padding: 0;
    overflow: hidden;
    box-shadow: none;
    border: 1px solid #E4E4E7;
    font-family: Inter, system-ui, sans-serif;
}
.el-dialog.ali-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.ali-modal .el-dialog__body { padding: 0; }
.el-dialog.ali-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.ali-modal__head { display: flex; align-items: center; gap: 12px; padding: 18px 22px; background: #fff; border-bottom: 1px solid #E4E4E7; }
.ali-modal__head-icon { width: 36px; height: 36px; border-radius: 8px; background: #F4F4F5; color: #18181B; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ali-modal__head-text { flex: 1; min-width: 0; }
.ali-modal__eyebrow { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #A1A1AA; margin-bottom: 1px; }
.ali-modal__title { font-size: 16px; font-weight: 700; color: #18181B; letter-spacing: -0.01em; }
.ali-modal__close { width: 26px; height: 26px; border-radius: 6px; border: none; background: #F4F4F5; color: #52525B; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.12s; }
.ali-modal__close:hover { background: #E4E4E7; color: #18181B; }

.ali-modal__body { padding: 18px 22px 6px; max-height: 70vh; overflow-y: auto; }
.ali-hint { font-size: 12.5px; color: #52525B; margin: 0 0 14px; }

.ali-drop {
    height: 104px; border: 1.5px dashed #D4D4D8; border-radius: 8px; background: #FAFAFA;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
    color: #52525B; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: background 0.12s ease;
}
.ali-drop:hover { background: #F4F4F5; }
.ali-drop :deep(.el-icon) { color: #18181B; }
.ali-drop__input { display: none; }

.ali-field__error { display: block; font-size: 11.5px; font-weight: 600; color: #B91C1C; margin-top: 6px; }

.ali-previews { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 14px; }
.ali-preview { position: relative; aspect-ratio: 1 / 1; border-radius: 8px; overflow: hidden; background: #E4E4E7; }
.ali-preview img { width: 100%; height: 100%; object-fit: cover; }
.ali-preview__remove {
    position: absolute; top: 5px; right: 5px; width: 20px; height: 20px; border-radius: 50%;
    border: none; background: rgba(24, 24, 27, 0.85); color: #fff; display: flex; align-items: center; justify-content: center;
    cursor: pointer;
}

.ali-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px; background: #FAFAFA; border-top: 1px solid #E4E4E7; }
.ali-btn-primary { background: #000; border: 1px solid transparent; color: #fff; border-radius: 6px; font-size: 12.5px; font-weight: 600; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: opacity 0.15s ease; }
.ali-btn-primary:hover { opacity: 0.88; }
.ali-btn-primary:disabled { opacity: 0.5; cursor: default; }
.ali-btn-outline { background: #fff; border: 1px solid #D4D4D8; color: #18181B; border-radius: 6px; font-size: 12.5px; font-weight: 600; padding: 8px 16px; cursor: pointer; transition: background 0.15s ease; }
.ali-btn-outline:hover { background: #FAFAFA; }
</style>
