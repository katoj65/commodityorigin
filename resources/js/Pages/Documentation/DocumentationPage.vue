<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Plus, Close, UploadFilled, Download, Delete, Document, User, Calendar,
} from '@element-plus/icons-vue';

const props = defineProps({
    documents: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    authUserId: { type: Number, default: null },
});

/* ── Filters ─────────────────────────────────────────────────────────── */
const activeFilter = ref('all');
const filters = computed(() => [
    { key: 'all', label: 'All' },
    { key: 'mine', label: 'Mine' },
    ...props.categories.map((category) => ({ key: category, label: category })),
]);

const sortedDocuments = computed(() => [...props.documents].sort((a, b) => b.created_at.localeCompare(a.created_at)));

function matchesFilter(key, doc) {
    if (key === 'mine') return doc.user_id === props.authUserId;
    if (key === 'all') return true;
    return doc.category === key;
}

const filteredDocuments = computed(() => sortedDocuments.value.filter((d) => matchesFilter(activeFilter.value, d)));

function tabCount(key) {
    return sortedDocuments.value.filter((d) => matchesFilter(key, d)).length;
}

/* ── KPIs ────────────────────────────────────────────────────────────── */
const kpis = computed(() => ({
    total: sortedDocuments.value.length,
    mine: tabCount('mine'),
    categories: new Set(sortedDocuments.value.map((d) => d.category)).size,
}));

/* ── Display helpers ─────────────────────────────────────────────────── */
function categoryTone(category) {
    const tones = ['doc-badge--green', 'doc-badge--blue', 'doc-badge--amber', 'doc-badge--violet', 'doc-badge--slate'];
    const idx = props.categories.indexOf(category);

    return tones[idx % tones.length] ?? 'doc-badge--slate';
}

function formatBytes(bytes) {
    if (!bytes) return '—';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function formatDate(dateTime) {
    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/* ── Upload dialog ───────────────────────────────────────────────────── */
const uploadOpen = ref(false);
const fileInput = ref(null);
const form = useForm({
    title: '',
    category: '',
    description: '',
    attachment: null,
});

function openUploadDialog() {
    form.reset();
    form.clearErrors();
    if (fileInput.value) fileInput.value.value = null;
    uploadOpen.value = true;
}

function chooseFile() {
    fileInput.value?.click();
}

function onFileChange() {
    form.attachment = fileInput.value?.files?.[0] ?? null;
    if (!form.title && form.attachment) {
        form.title = form.attachment.name.replace(/\.[^.]+$/, '');
    }
}

function submitUpload() {
    form.post(route('documentation.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { uploadOpen.value = false; },
    });
}

/* ── Delete ──────────────────────────────────────────────────────────── */
const deleteOpen = ref(false);
const pendingDeleteId = ref(null);

function requestDelete(doc) {
    pendingDeleteId.value = doc.id;
    deleteOpen.value = true;
}

function confirmDelete() {
    if (!pendingDeleteId.value) return;
    router.delete(route('documentation.destroy', pendingDeleteId.value), { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Documentation" full-width flush :show-banner="false">
        <Head title="Documentation" />

        <div class="doc-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="doc-page-header">
                <div class="doc-page-header__left">
                    <div class="doc-kicker">Knowledge Base · Bean Origin</div>
                    <h1 class="doc-title">Documentation</h1>
                    <p class="doc-subtitle">Shared guides, policies, and reference files for everyone on the platform.</p>
                </div>
                <div class="doc-page-header__actions">
                    <button type="button" class="doc-btn-primary" @click="openUploadDialog">
                        <el-icon><Plus /></el-icon> Upload Document
                    </button>
                </div>
            </div>

            <!-- ── Overview strip ────────────────────────────────────────── -->
            <div class="doc-kpi-strip">
                <div class="doc-kpi">
                    <span class="doc-kpi__label">Total Documents</span>
                    <strong class="doc-kpi__val">{{ kpis.total }}</strong>
                </div>
                <div class="doc-kpi">
                    <span class="doc-kpi__label">My Uploads</span>
                    <strong class="doc-kpi__val">{{ kpis.mine }}</strong>
                </div>
                <div class="doc-kpi">
                    <span class="doc-kpi__label">Categories</span>
                    <strong class="doc-kpi__val">{{ kpis.categories }}</strong>
                </div>
            </div>

            <div class="doc-body">
                <div class="doc-section">
                    <el-tabs v-model="activeFilter" class="doc-tabs">
                        <el-tab-pane v-for="f in filters" :key="f.key" :name="f.key">
                            <template #label>
                                <span class="doc-tab-label">
                                    {{ f.label }}
                                    <span v-if="tabCount(f.key)" class="doc-tab-count">{{ tabCount(f.key) }}</span>
                                </span>
                            </template>
                        </el-tab-pane>
                    </el-tabs>

                    <el-table :data="filteredDocuments" class="doc-table" empty-text="No documents in this view.">
                        <el-table-column label="Document" min-width="260">
                            <template #default="{ row }">
                                <div class="doc-cell-title">
                                    <span class="doc-cell-title__icon"><el-icon :size="14"><Document /></el-icon></span>
                                    <div class="doc-cell-title__text">
                                        <span class="doc-cell-title__name">{{ row.title }}</span>
                                        <span v-if="row.description" class="doc-cell-title__desc">{{ row.description }}</span>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="Category" width="130">
                            <template #default="{ row }">
                                <span class="doc-badge" :class="categoryTone(row.category)">{{ row.category }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Uploaded By" width="170">
                            <template #default="{ row }">
                                <span class="doc-uploader"><el-icon :size="12"><User /></el-icon> {{ row.uploader_name }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="Size" width="90">
                            <template #default="{ row }">{{ formatBytes(row.file_size) }}</template>
                        </el-table-column>
                        <el-table-column label="Uploaded" width="120">
                            <template #default="{ row }">
                                <span class="doc-uploaded"><el-icon :size="12"><Calendar /></el-icon> {{ formatDate(row.created_at) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="" width="90" align="right">
                            <template #default="{ row }">
                                <div class="doc-row-actions">
                                    <a
                                        :href="row.file_url"
                                        :download="row.original_name"
                                        target="_blank"
                                        rel="noopener"
                                        class="doc-icon-btn"
                                        aria-label="Download document"
                                        title="Download"
                                    >
                                        <el-icon :size="15"><Download /></el-icon>
                                    </a>
                                    <button
                                        v-if="row.user_id === authUserId"
                                        type="button"
                                        class="doc-icon-btn doc-icon-btn--danger"
                                        aria-label="Delete document"
                                        title="Delete"
                                        @click="requestDelete(row)"
                                    >
                                        <el-icon :size="15"><Delete /></el-icon>
                                    </button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </div>

        <!-- ── Upload modal ─────────────────────────────────────────────── -->
        <el-dialog
            v-model="uploadOpen"
            width="480px"
            destroy-on-close
            align-center
            :show-close="false"
            class="doc-modal"
        >
            <template #header>
                <div class="doc-modal__head">
                    <div class="doc-modal__head-icon">
                        <el-icon :size="18"><UploadFilled /></el-icon>
                    </div>
                    <div class="doc-modal__head-text">
                        <div class="doc-modal__eyebrow">Knowledge Base</div>
                        <div class="doc-modal__title">Upload Document</div>
                    </div>
                    <button type="button" class="doc-modal__close" aria-label="Close" @click="uploadOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="doc-modal__body">
                <div class="doc-field">
                    <label class="doc-field__label">Title</label>
                    <el-input v-model="form.title" placeholder="e.g. Export Compliance Guide" class="doc-input" :class="{ 'doc-input--error': form.errors.title }" />
                    <span v-if="form.errors.title" class="doc-field__error">{{ form.errors.title }}</span>
                </div>

                <div class="doc-field">
                    <label class="doc-field__label">Category</label>
                    <el-select v-model="form.category" placeholder="Select category…" style="width:100%" class="doc-input" :class="{ 'doc-input--error': form.errors.category }">
                        <el-option v-for="opt in categories" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                    <span v-if="form.errors.category" class="doc-field__error">{{ form.errors.category }}</span>
                </div>

                <div class="doc-field">
                    <label class="doc-field__label">Description</label>
                    <el-input v-model="form.description" type="textarea" :rows="2" placeholder="Optional summary of what this document covers" class="doc-input" />
                </div>

                <div class="doc-field">
                    <label class="doc-field__label">File</label>
                    <input
                        ref="fileInput"
                        type="file"
                        class="doc-file-input"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                        @change="onFileChange"
                    >
                    <button type="button" class="doc-picker" @click="chooseFile">
                        <span class="doc-picker__icon"><el-icon :size="16"><UploadFilled /></el-icon></span>
                        <span class="doc-picker__copy">
                            <span class="doc-picker__title">{{ form.attachment?.name || 'Choose a file' }}</span>
                            <span class="doc-picker__meta">PDF, image, Word, or Excel up to 10 MB</span>
                        </span>
                    </button>
                    <span v-if="form.errors.attachment" class="doc-field__error">{{ form.errors.attachment }}</span>
                </div>
            </div>

            <template #footer>
                <div class="doc-modal__footer">
                    <button type="button" class="doc-btn-outline" @click="uploadOpen = false">Cancel</button>
                    <button type="button" class="doc-btn-primary" :disabled="form.processing" @click="submitUpload">
                        <el-icon v-if="!form.processing"><UploadFilled /></el-icon>
                        {{ form.processing ? 'Uploading…' : 'Upload Document' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="deleteOpen"
            title="Delete Document"
            message="This document will be permanently removed. This can't be undone."
            confirm-text="Delete"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>

<style scoped>
.doc-page {
    --green: #004532;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.doc-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.75rem 1.5rem 0;
}

.doc-page-header__left {
    max-width: 560px;
}

.doc-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.doc-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.doc-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

.doc-page-header__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 4px;
}

/* ── Overview strip ──────────────────────────────────────────────────── */
.doc-kpi-strip {
    display: flex;
    overflow-x: auto;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-top: 1.5rem;
    scrollbar-width: none;
}

.doc-kpi-strip::-webkit-scrollbar { display: none; }

.doc-kpi {
    flex: 1;
    min-width: 130px;
    padding: 1rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
    border-right: 1px solid var(--border);
}

.doc-kpi:last-child { border-right: none; }

.doc-kpi__label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
}

.doc-kpi__val {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--on-surface);
    letter-spacing: -0.01em;
}

/* ── Body ────────────────────────────────────────────────────────────── */
.doc-body {
    padding: 1.5rem 0 3rem;
}

.doc-section {
    background: transparent;
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.doc-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.doc-btn-primary:hover { opacity: 0.9; }
.doc-btn-primary:disabled { opacity: 0.6; cursor: default; }

.doc-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.doc-btn-outline:hover { background: #f8fafc; }

/* ── Category tabs ───────────────────────────────────────────────────── */
.doc-tabs { padding: 0 1.5rem; }
.doc-tabs :deep(.el-tabs__header) { margin: 0; }
.doc-tabs :deep(.el-tabs__nav-wrap::after) { background-color: var(--border); }
.doc-tabs :deep(.el-tabs__item) {
    font-weight: 700;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    height: 44px;
}
.doc-tabs :deep(.el-tabs__item.is-active) { color: var(--green); }
.doc-tabs :deep(.el-tabs__active-bar) { background-color: var(--green); }

.doc-tab-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.doc-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    font-size: 0.625rem;
    font-weight: 800;
}

/* ── Table ───────────────────────────────────────────────────────────── */
.doc-table {
    --el-table-border-color: var(--surface-low);
    --el-table-header-bg-color: var(--surface-low);
    --el-table-header-text-color: var(--on-surface-var);
    font-family: 'Manrope', system-ui, sans-serif;
}

.doc-table :deep(.el-table__header) th {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.doc-table :deep(.el-table__inner-wrapper::before) { display: none; }
.doc-table :deep(.el-table__header-wrapper th:first-child .cell),
.doc-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.5rem; }
.doc-table :deep(.el-table__header-wrapper th:last-child .cell),
.doc-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.5rem; }

.doc-cell-title {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.doc-cell-title__icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.doc-cell-title__text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.doc-cell-title__name {
    font-size: 0.8437rem;
    font-weight: 600;
    color: var(--on-surface);
}

.doc-cell-title__desc {
    font-size: 0.75rem;
    color: var(--on-surface-var);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 360px;
}

.doc-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 4px 10px;
    white-space: nowrap;
}

.doc-badge--green { background: #dcfce7; color: #166534; }
.doc-badge--blue { background: #dbeafe; color: #1e40af; }
.doc-badge--amber { background: #fef3c7; color: #92400e; }
.doc-badge--violet { background: #ede9fe; color: #6d28d9; }
.doc-badge--slate { background: #f3f4f6; color: #6b7280; }

.doc-uploader,
.doc-uploaded {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
}

.doc-row-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
}

.doc-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    background: none;
    color: #6b7280;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.12s, color 0.12s;
}

.doc-icon-btn:hover { background: var(--surface-low); color: var(--on-surface); }
.doc-icon-btn--danger:hover { background: #fee2e2; color: #991b1b; }

/* ── Upload modal ──────────────────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .doc-page's
   DOM subtree, so CSS custom properties defined on .doc-page do NOT
   cascade in. All colors below are literal hex values on purpose. */
:deep(.el-dialog.doc-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.doc-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.doc-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.doc-modal .el-dialog__footer) { padding: 0; }

.doc-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.doc-modal__head-icon {
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

.doc-modal__head-text { flex: 1; min-width: 0; }

.doc-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.doc-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.doc-modal__close {
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

.doc-modal__close:hover { background: #e5e7eb; color: #111827; }

.doc-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.doc-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.doc-field__label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #374151;
}

.doc-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.doc-input--error :deep(.el-input__wrapper),
.doc-input--error :deep(.el-textarea__inner),
.doc-input--error :deep(.el-select__wrapper) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.doc-input :deep(.el-input__wrapper),
.doc-input :deep(.el-textarea__inner) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.doc-input :deep(.el-input__wrapper:hover),
.doc-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.doc-input :deep(.el-input__wrapper.is-focus),
.doc-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.doc-file-input { display: none; }

.doc-picker {
    width: 100%;
    min-height: 76px;
    border: 1px dashed #cfd8d4;
    border-radius: 10px;
    background: #f9fafb;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
    cursor: pointer;
    transition: background 0.12s, border-color 0.12s;
}

.doc-picker:hover { background: #f3f4f6; border-color: #b7c4bd; }

.doc-picker__icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.doc-picker__copy { display: flex; flex-direction: column; gap: 2px; min-width: 0; }

.doc-picker__title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.doc-picker__meta {
    font-size: 0.6875rem;
    color: #9ca3af;
}

.doc-modal__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .doc-page-header { padding: 1.25rem 1.25rem 0; }
    .doc-body { padding: 1.25rem 0 3rem; }
    .doc-tabs { padding: 0 1.25rem; }
    .doc-table :deep(.el-table__header-wrapper th:first-child .cell),
    .doc-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 1.25rem; }
    .doc-table :deep(.el-table__header-wrapper th:last-child .cell),
    .doc-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 1.25rem; }
}
</style>
