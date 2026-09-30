<script setup>
/* Bulk "Import Collections" for the farm-agnostic "My Farm Collection"
   ledger (FarmCollectionIndex.vue). Reuses functionality that already
   exists rather than rebuilding it:
     - App\Helpers\ExcelImportHelper (already used by FarmController /
       Business/StoreController) does the actual spreadsheet parsing.
     - App\Services\FarmCollectionService::importRows() does the
       row-by-row validation + creation — same service backing "Record
       Collection" on this page and the Farm Profile page's own
       "Import Excel" button.
     - POST farm.collections.import (FarmController::importCollections)
       is the same endpoint the Farm Profile page's importer already
       posts to — nothing new on the backend beyond passing its flashed
       session result through FarmCollectionController::index() too.
   Every row in the sheet must belong to one farm (FarmCollectionService
   defaults each row's coffee_type from that farm's own coffee_type), so
   this modal resolves a single target farm by code first — via the same
   shared FarmCodeLookupField AddFarmCollectionModal.vue uses, not a
   second copy of that lookup. */
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, UploadFilled, WarningFilled } from '@element-plus/icons-vue';
import FarmCodeLookupField from '@/Components/FarmCodeLookupField.vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    collectionImportResult: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

/* ── Farm-by-code lookup ───────────────────────────────────────────── */
const farmCodeField = ref(null);
const farmId = ref('');

/* ── File selection ───────────────────────────────────────────────────
   Same accepted types as FarmController::importCollections()'s
   'mimes:xlsx,xls' validation rule. */
const fileInput = ref(null);
const selectedFile = ref(null);
const fileError = ref('');

function handleFileChange(event) {
    selectedFile.value = event.target.files?.[0] || null;
    fileError.value = '';
}

const contentReady = ref(false);

watch(() => props.modelValue, (open) => {
    if (!open) return;
    farmCodeField.value?.reset();
    selectedFile.value = null;
    fileError.value = '';
    if (fileInput.value) fileInput.value.value = '';
    resultVisible.value = false;

    contentReady.value = false;
    requestAnimationFrame(() => requestAnimationFrame(() => {
        contentReady.value = true;
    }));
});

/* Result panel reacts to the flashed session result the backend redirect
   brings back through FarmCollectionController::index()'s
   `collectionImportResult` prop — the same convention Farm Profile's
   own importer already relies on. */
const resultVisible = ref(false);
watch(() => props.collectionImportResult, (result) => {
    if (!result || !dialogVisible.value) return;
    resultVisible.value = true;
});

const importing = ref(false);

async function submit() {
    if (!farmId.value) {
        await farmCodeField.value?.lookup();
    }

    if (!farmId.value) {
        return;
    }

    if (!selectedFile.value) {
        fileError.value = 'Choose an Excel file (.xlsx or .xls) to import.';
        return;
    }

    importing.value = true;
    resultVisible.value = false;

    router.post(route('farm.collections.import', farmId.value), { file: selectedFile.value }, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            const imported = props.collectionImportResult?.imported ?? 0;
            const skipped = props.collectionImportResult?.errors?.length ?? 0;
            resultVisible.value = true;
            ElNotification({
                title: imported > 0 ? 'Import Complete' : 'Import Failed',
                message: imported > 0
                    ? `${imported} collection${imported === 1 ? '' : 's'} imported${skipped ? `, ${skipped} row(s) skipped.` : '.'}`
                    : 'No rows were imported. See the details below.',
                type: imported > 0 ? (skipped ? 'warning' : 'success') : 'error',
                duration: 4000,
                offset: 84,
            });
            if (skipped === 0 && imported > 0) {
                dialogVisible.value = false;
            }
        },
        onError: (errors) => {
            fileError.value = errors.file || 'Please check the file and try again.';
        },
        onFinish: () => {
            importing.value = false;
        },
    });
}

function closeDialog() {
    dialogVisible.value = false;
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
        class="ifc-modal"
    >
        <template #header>
            <div class="ifc-modal__head">
                <div class="ifc-modal__head-icon">
                    <el-icon :size="18"><UploadFilled /></el-icon>
                </div>
                <div class="ifc-modal__head-text">
                    <div class="ifc-modal__eyebrow">Inventory</div>
                    <div class="ifc-modal__title">Import Collections</div>
                </div>
                <button type="button" class="ifc-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="ifc-modal__body">
            <div v-if="!contentReady" class="ifc-loading">
                <span class="ifc-loading__spinner"></span>
                <span>Preparing…</span>
            </div>
            <template v-else>
                <p class="ifc-intro">Bulk-record coffee collections for one farm from a spreadsheet. Every row is added to the farm below — resolve it by its farm code first, then upload the file.</p>

                <FarmCodeLookupField ref="farmCodeField" v-model="farmId" />

                <div class="ifc-field">
                    <label class="ifc-field__label">Excel File <small>(.xlsx or .xls)</small></label>
                    <input ref="fileInput" type="file" accept=".xlsx,.xls" class="ifc-file-input" @change="handleFileChange">
                    <button type="button" class="ifc-dropzone" :class="{ 'ifc-dropzone--error': fileError }" @click="fileInput?.click()">
                        <el-icon :size="16"><UploadFilled /></el-icon>
                        <span>{{ selectedFile ? selectedFile.name : 'Click to choose a file' }}</span>
                    </button>
                    <span v-if="fileError" class="ifc-field__error">{{ fileError }}</span>
                </div>

                <div v-if="resultVisible && collectionImportResult" class="ifc-result" :class="{ 'ifc-result--warn': collectionImportResult.errors.length }">
                    <div class="ifc-result__icon">
                        <el-icon :size="16"><WarningFilled v-if="collectionImportResult.errors.length" /><UploadFilled v-else /></el-icon>
                    </div>
                    <div class="ifc-result__body">
                        <div class="ifc-result__title">
                            {{ collectionImportResult.imported }} collection{{ collectionImportResult.imported === 1 ? '' : 's' }} imported
                            <span v-if="collectionImportResult.errors.length">, {{ collectionImportResult.errors.length }} row{{ collectionImportResult.errors.length === 1 ? '' : 's' }} skipped</span>
                        </div>
                        <ul v-if="collectionImportResult.errors.length" class="ifc-result__list">
                            <li v-for="err in collectionImportResult.errors" :key="err.row">Row {{ err.row }}: {{ err.errors.join(' ') }}</li>
                        </ul>
                    </div>
                </div>
            </template>
        </div>

        <template #footer>
            <div class="ifc-modal__footer">
                <button type="button" class="ifc-btn-primary" :disabled="importing || !contentReady" @click="submit">
                    {{ importing ? 'Importing…' : 'Import' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
.el-dialog.ifc-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.ifc-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.ifc-modal .el-dialog__body { padding: 0; }
.el-dialog.ifc-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.ifc-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.ifc-modal__head-icon {
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
.ifc-modal__head-text { flex: 1; min-width: 0; }
.ifc-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.ifc-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.ifc-modal__close {
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
.ifc-modal__close:hover { background: #E5E7EB; color: #121516; }

.ifc-modal__body { padding: 22px 24px 8px; max-height: 72vh; overflow-y: auto; }

.ifc-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 56px 24px; color: #6F7677; font-size: 13px; font-weight: 500; }
.ifc-loading__spinner { width: 26px; height: 26px; border-radius: 50%; border: 2.5px solid #E5E7EB; border-top-color: #121516; animation: ifc-spin 0.7s linear infinite; }
@keyframes ifc-spin { to { transform: rotate(360deg); } }

.ifc-intro { font-size: 12.5px; color: #6F7677; line-height: 1.5; margin: 0 0 18px; }

.ifc-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.ifc-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.ifc-field__label small { font-weight: 500; color: #6F7677; text-transform: none; }
.ifc-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.ifc-file-input { display: none; }
.ifc-dropzone {
    display: flex; align-items: center; gap: 8px;
    width: 100%; height: 40px; padding: 0 14px;
    border-radius: 6px; border: 1px dashed #D0D5D8;
    background: #FAFBFB; color: #4B5457;
    font-size: 13px; font-weight: 500;
    cursor: pointer; text-align: left;
    transition: border-color 0.12s ease, background 0.12s ease;
}
.ifc-dropzone:hover { border-color: #9CA3AF; background: #F5F6F7; }
.ifc-dropzone--error { border-color: #F85149; }

.ifc-result {
    display: flex; gap: 10px;
    padding: 12px 14px; margin-bottom: 16px;
    border-radius: 6px;
    background: #EFF8F1; border: 1px solid #CDE9D3;
    color: #1E4620;
}
.ifc-result--warn { background: #FFF8E8; border-color: #F3DFA6; color: #6B4E00; }
.ifc-result__icon { flex-shrink: 0; margin-top: 1px; }
.ifc-result__title { font-size: 12.5px; font-weight: 700; }
.ifc-result__list { margin: 6px 0 0; padding-left: 16px; font-size: 11.5px; line-height: 1.6; }

.ifc-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.ifc-btn-primary {
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
.ifc-btn-primary:hover { opacity: 0.88; }
.ifc-btn-primary:disabled { opacity: 0.5; cursor: default; }
</style>
