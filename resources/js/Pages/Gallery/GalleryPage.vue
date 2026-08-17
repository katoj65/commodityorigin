<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Plus, Close, UploadFilled, Delete, Edit, Picture,
    ArrowLeft, ArrowRight, User, Calendar,
} from '@element-plus/icons-vue';

const props = defineProps({
    images: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
});

const sortedImages = computed(() => [...props.images].sort((a, b) => b.created_at.localeCompare(a.created_at)));

function formatDate(dateTime) {
    if (!dateTime) return '';

    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/* ── Lightbox ────────────────────────────────────────────────────────── */
const lightboxIndex = ref(null);
const lightboxOpen = computed(() => lightboxIndex.value !== null);
const activeImage = computed(() => (lightboxIndex.value !== null ? sortedImages.value[lightboxIndex.value] : null));

function openLightbox(index) {
    lightboxIndex.value = index;
}

function closeLightbox() {
    lightboxIndex.value = null;
}

function showPrev() {
    if (lightboxIndex.value === null) return;
    lightboxIndex.value = (lightboxIndex.value - 1 + sortedImages.value.length) % sortedImages.value.length;
}

function showNext() {
    if (lightboxIndex.value === null) return;
    lightboxIndex.value = (lightboxIndex.value + 1) % sortedImages.value.length;
}

function onKeydown(event) {
    if (!lightboxOpen.value) return;
    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowLeft') showPrev();
    if (event.key === 'ArrowRight') showNext();
}

window.addEventListener('keydown', onKeydown);
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

/* ── Upload dialog ───────────────────────────────────────────────────── */
const uploadOpen = ref(false);
const fileInput = ref(null);
const previewUrl = ref('');
const uploadForm = useForm({
    title: '',
    description: '',
    image: null,
});

function openUploadDialog() {
    uploadForm.reset();
    uploadForm.clearErrors();
    if (fileInput.value) fileInput.value.value = null;
    previewUrl.value = '';
    uploadOpen.value = true;
}

function chooseFile() {
    fileInput.value?.click();
}

function onFileChange() {
    const file = fileInput.value?.files?.[0] ?? null;
    uploadForm.image = file;

    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = file ? URL.createObjectURL(file) : '';

    if (!uploadForm.title && file) {
        uploadForm.title = file.name.replace(/\.[^.]+$/, '');
    }
}

function submitUpload() {
    uploadForm.post(route('gallery.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            uploadOpen.value = false;
            if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = '';
        },
    });
}

/* ── Edit dialog ─────────────────────────────────────────────────────── */
const editOpen = ref(false);
const editForm = useForm({
    title: '',
    description: '',
});
const editingImage = ref(null);

function openEditDialog(image) {
    editingImage.value = image;
    editForm.reset();
    editForm.clearErrors();
    editForm.title = image.title;
    editForm.description = image.description || '';
    editOpen.value = true;
}

function submitEdit() {
    if (!editingImage.value) return;

    editForm.patch(route('gallery.update', editingImage.value.id), {
        preserveScroll: true,
        onSuccess: () => { editOpen.value = false; },
    });
}

/* ── Delete ──────────────────────────────────────────────────────────── */
const deleteOpen = ref(false);
const pendingDelete = ref(null);

function requestDelete(image) {
    pendingDelete.value = image;
    deleteOpen.value = true;
    closeLightbox();
}

function confirmDelete() {
    if (!pendingDelete.value) return;

    router.delete(route('gallery.destroy', pendingDelete.value.id), { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Gallery" full-width flush :show-banner="false">
        <Head title="Gallery" />

        <div class="gal-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="gal-page-header">
                <div class="gal-page-header__left">
                    <div class="gal-kicker">Shared Media · Bean Origin</div>
                    <h1 class="gal-title">Gallery</h1>
                    <p class="gal-subtitle">Photos from farms, harvests, and the exchange floor — visible to everyone on the platform.</p>
                </div>
                <div v-if="canManage" class="gal-page-header__actions">
                    <button type="button" class="gal-btn-primary" @click="openUploadDialog">
                        <el-icon><Plus /></el-icon> Upload Image
                    </button>
                </div>
            </div>

            <!-- ── Grid ──────────────────────────────────────────────────── -->
            <div class="gal-body">
                <div v-if="sortedImages.length" class="gal-grid">
                    <div
                        v-for="(image, index) in sortedImages"
                        :key="image.id"
                        class="gal-card"
                        role="button"
                        tabindex="0"
                        :aria-label="`View ${image.title}`"
                        @click="openLightbox(index)"
                        @keydown.enter.prevent="openLightbox(index)"
                        @keydown.space.prevent="openLightbox(index)"
                    >
                        <div class="gal-card__media">
                            <img :src="image.image_url" :alt="image.title" class="gal-card__img" loading="lazy">
                        </div>
                        <div class="gal-card__content">
                            <div class="gal-card__text">
                                <span class="gal-card__title">{{ image.title }}</span>
                                <span class="gal-card__meta">{{ formatDate(image.created_at) }}</span>
                            </div>
                            <div v-if="canManage" class="gal-card__actions" @click.stop>
                                <button type="button" class="gal-card__icon-btn" aria-label="Edit image" @click="openEditDialog(image)">
                                    <el-icon :size="14"><Edit /></el-icon>
                                </button>
                                <button type="button" class="gal-card__icon-btn gal-card__icon-btn--danger" aria-label="Delete image" @click="requestDelete(image)">
                                    <el-icon :size="14"><Delete /></el-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="gal-empty">
                    <div class="gal-empty__icon"><el-icon :size="24"><Picture /></el-icon></div>
                    <div class="gal-empty__title">No images yet</div>
                    <p class="gal-empty__text">
                        {{ canManage ? 'Upload the first photo to get the gallery started.' : 'Check back soon — new photos are added regularly.' }}
                    </p>
                    <button v-if="canManage" type="button" class="gal-btn-primary" @click="openUploadDialog">
                        <el-icon><Plus /></el-icon> Upload Image
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Lightbox ──────────────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="gal-fade">
                <div v-if="lightboxOpen" class="gal-lightbox" @click.self="closeLightbox">
                    <button type="button" class="gal-lightbox__close" aria-label="Close" @click="closeLightbox">
                        <el-icon :size="20"><Close /></el-icon>
                    </button>

                    <button
                        v-if="sortedImages.length > 1"
                        type="button"
                        class="gal-lightbox__nav gal-lightbox__nav--prev"
                        aria-label="Previous image"
                        @click="showPrev"
                    >
                        <el-icon :size="22"><ArrowLeft /></el-icon>
                    </button>

                    <div class="gal-lightbox__body">
                        <img :src="activeImage?.image_url" :alt="activeImage?.title" class="gal-lightbox__img">
                        <div class="gal-lightbox__info">
                            <h2 class="gal-lightbox__title">{{ activeImage?.title }}</h2>
                            <p v-if="activeImage?.description" class="gal-lightbox__desc">{{ activeImage.description }}</p>
                            <div class="gal-lightbox__meta">
                                <span v-if="activeImage?.uploader_name" class="gal-lightbox__meta-item">
                                    <el-icon :size="13"><User /></el-icon> {{ activeImage.uploader_name }}
                                </span>
                                <span class="gal-lightbox__meta-item">
                                    <el-icon :size="13"><Calendar /></el-icon> {{ formatDate(activeImage?.created_at) }}
                                </span>
                            </div>
                            <div v-if="canManage" class="gal-lightbox__actions">
                                <button type="button" class="gal-btn-outline gal-btn-outline--light" @click="openEditDialog(activeImage)">
                                    <el-icon><Edit /></el-icon> Edit
                                </button>
                                <button type="button" class="gal-btn-outline gal-btn-outline--danger" @click="requestDelete(activeImage)">
                                    <el-icon><Delete /></el-icon> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <button
                        v-if="sortedImages.length > 1"
                        type="button"
                        class="gal-lightbox__nav gal-lightbox__nav--next"
                        aria-label="Next image"
                        @click="showNext"
                    >
                        <el-icon :size="22"><ArrowRight /></el-icon>
                    </button>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Upload modal ──────────────────────────────────────────────── -->
        <el-dialog
            v-model="uploadOpen"
            width="480px"
            destroy-on-close
            align-center
            :show-close="false"
            class="gal-modal"
        >
            <template #header>
                <div class="gal-modal__head">
                    <div class="gal-modal__head-icon">
                        <el-icon :size="18"><UploadFilled /></el-icon>
                    </div>
                    <div class="gal-modal__head-text">
                        <div class="gal-modal__eyebrow">Gallery</div>
                        <div class="gal-modal__title">Upload Image</div>
                    </div>
                    <button type="button" class="gal-modal__close" aria-label="Close" @click="uploadOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="gal-modal__body">
                <div class="gal-field">
                    <label class="gal-field__label">Title</label>
                    <el-input v-model="uploadForm.title" placeholder="e.g. Harvest Day at Bugisu" class="gal-input" :class="{ 'gal-input--error': uploadForm.errors.title }" />
                    <span v-if="uploadForm.errors.title" class="gal-field__error">{{ uploadForm.errors.title }}</span>
                </div>

                <div class="gal-field">
                    <label class="gal-field__label">Description <span class="gal-field__optional">(optional)</span></label>
                    <el-input v-model="uploadForm.description" type="textarea" :rows="2" placeholder="What's happening in this photo?" class="gal-input" />
                </div>

                <div class="gal-field">
                    <label class="gal-field__label">Image</label>
                    <input
                        ref="fileInput"
                        type="file"
                        class="gal-file-input"
                        accept=".jpg,.jpeg,.png,.gif,.webp"
                        @change="onFileChange"
                    >
                    <button type="button" class="gal-picker" :class="{ 'gal-picker--filled': previewUrl }" @click="chooseFile">
                        <img v-if="previewUrl" :src="previewUrl" alt="" class="gal-picker__preview">
                        <template v-else>
                            <span class="gal-picker__icon"><el-icon :size="16"><UploadFilled /></el-icon></span>
                            <span class="gal-picker__copy">
                                <span class="gal-picker__title">Choose an image</span>
                                <span class="gal-picker__meta">JPG, PNG, GIF, or WEBP up to 10 MB</span>
                            </span>
                        </template>
                    </button>
                    <span v-if="uploadForm.errors.image" class="gal-field__error">{{ uploadForm.errors.image }}</span>
                </div>
            </div>

            <template #footer>
                <div class="gal-modal__footer">
                    <button type="button" class="gal-btn-outline" @click="uploadOpen = false">Cancel</button>
                    <button type="button" class="gal-btn-primary" :disabled="uploadForm.processing" @click="submitUpload">
                        <el-icon v-if="!uploadForm.processing"><UploadFilled /></el-icon>
                        {{ uploadForm.processing ? 'Uploading…' : 'Upload Image' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Edit modal ────────────────────────────────────────────────── -->
        <el-dialog
            v-model="editOpen"
            width="420px"
            destroy-on-close
            align-center
            :show-close="false"
            class="gal-modal"
        >
            <template #header>
                <div class="gal-modal__head">
                    <div class="gal-modal__head-icon">
                        <el-icon :size="18"><Edit /></el-icon>
                    </div>
                    <div class="gal-modal__head-text">
                        <div class="gal-modal__eyebrow">Gallery</div>
                        <div class="gal-modal__title">Edit Image</div>
                    </div>
                    <button type="button" class="gal-modal__close" aria-label="Close" @click="editOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="gal-modal__body">
                <div class="gal-field">
                    <label class="gal-field__label">Title</label>
                    <el-input v-model="editForm.title" class="gal-input" :class="{ 'gal-input--error': editForm.errors.title }" />
                    <span v-if="editForm.errors.title" class="gal-field__error">{{ editForm.errors.title }}</span>
                </div>

                <div class="gal-field">
                    <label class="gal-field__label">Description <span class="gal-field__optional">(optional)</span></label>
                    <el-input v-model="editForm.description" type="textarea" :rows="2" class="gal-input" />
                </div>
            </div>

            <template #footer>
                <div class="gal-modal__footer">
                    <button type="button" class="gal-btn-outline" @click="editOpen = false">Cancel</button>
                    <button type="button" class="gal-btn-primary" :disabled="editForm.processing" @click="submitEdit">
                        {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="deleteOpen"
            title="Delete Image"
            message="This image will be permanently removed from the gallery. This can't be undone."
            confirm-text="Delete"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>

<style scoped>
.gal-page {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.gal-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin: 1.75rem 1.5rem 0;
    padding: 1.25rem 1.5rem;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

.gal-page-header__left { max-width: 560px; }

.gal-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.gal-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.gal-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

.gal-page-header__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 4px;
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.gal-btn-primary {
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

.gal-btn-primary:hover { opacity: 0.9; }
.gal-btn-primary:disabled { opacity: 0.6; cursor: default; }

.gal-btn-outline {
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

.gal-btn-outline:hover { background: #f8fafc; }

.gal-btn-outline--light {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.16);
    color: #fff;
}

.gal-btn-outline--light:hover { background: rgba(255, 255, 255, 0.14); }

.gal-btn-outline--danger { color: #f87171; }
.gal-btn-outline--danger:hover { background: rgba(248, 113, 113, 0.12); }

/* ── Body / grid ─────────────────────────────────────────────────────── */
.gal-body { padding: 1.5rem 1.5rem 3rem; }

.gal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
}

.gal-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    position: relative;
    padding: 0;
    border: none;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.15s ease, transform 0.15s ease;
}

.gal-card:hover,
.gal-card:focus-visible {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.gal-card:focus-visible { outline: 2px solid var(--green); outline-offset: 2px; }

.gal-card__media {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: var(--surface-low);
}

.gal-card__img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s ease;
}

.gal-card:hover .gal-card__img { transform: scale(1.06); }

.gal-card__content {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    padding: 10px 12px;
}

.gal-card__text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    text-align: left;
}

.gal-card__title {
    color: var(--on-surface);
    font-size: 0.8125rem;
    font-weight: 700;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.gal-card__meta {
    color: var(--on-surface-var);
    font-size: 0.6875rem;
}

.gal-card__actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

.gal-card__icon-btn {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    border: none;
    background: var(--surface-low);
    color: #6b7280;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.12s, color 0.12s;
}

.gal-card__icon-btn:hover { background: #e5e7eb; color: var(--on-surface); }
.gal-card__icon-btn--danger:hover { background: #fee2e2; color: #dc2626; }

/* ── Empty state ─────────────────────────────────────────────────────── */
.gal-empty { text-align: center; padding: 4rem 1rem; }

.gal-empty__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--surface-low);
    color: var(--on-surface-var);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
}

.gal-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.gal-empty__text { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0 auto 16px; max-width: 320px; line-height: 1.5; }

/* ── Lightbox ────────────────────────────────────────────────────────── */
.gal-lightbox {
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(10, 12, 10, 0.94);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
}

.gal-lightbox__close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s;
}

.gal-lightbox__close:hover { background: rgba(255, 255, 255, 0.2); }

.gal-lightbox__nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s;
}

.gal-lightbox__nav:hover { background: rgba(255, 255, 255, 0.2); }
.gal-lightbox__nav--prev { left: 16px; }
.gal-lightbox__nav--next { right: 16px; }

.gal-lightbox__body {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    max-width: 900px;
    width: 100%;
}

.gal-lightbox__img {
    max-width: 100%;
    max-height: 68vh;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    object-fit: contain;
}

.gal-lightbox__info { text-align: center; max-width: 560px; }

.gal-lightbox__title {
    color: #fff;
    font-size: 1.125rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    margin: 0 0 6px;
}

.gal-lightbox__desc {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.875rem;
    line-height: 1.6;
    margin: 0 0 10px;
}

.gal-lightbox__meta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-bottom: 14px;
}

.gal-lightbox__meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.55);
}

.gal-lightbox__actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

/* ── Modals ──────────────────────────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .gal-page's
   DOM subtree, so CSS custom properties defined on .gal-page do NOT
   cascade in. All colors below are literal hex values on purpose. */
:deep(.el-dialog.gal-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.gal-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.gal-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.gal-modal .el-dialog__footer) { padding: 0; }

.gal-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.gal-modal__head-icon {
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

.gal-modal__head-text { flex: 1; min-width: 0; }

.gal-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.gal-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.gal-modal__close {
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

.gal-modal__close:hover { background: #e5e7eb; color: #111827; }

.gal-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.gal-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.gal-field__label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #374151;
}

.gal-field__optional { font-weight: 400; color: #9ca3af; }

.gal-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.gal-input--error :deep(.el-input__wrapper),
.gal-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.gal-input :deep(.el-input__wrapper),
.gal-input :deep(.el-textarea__inner) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.gal-input :deep(.el-input__wrapper:hover),
.gal-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.gal-input :deep(.el-input__wrapper.is-focus),
.gal-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.gal-file-input { display: none; }

.gal-picker {
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

.gal-picker:hover { background: #f3f4f6; border-color: #b7c4bd; }

.gal-picker--filled { padding: 0; overflow: hidden; }

.gal-picker__preview {
    width: 100%;
    max-height: 220px;
    object-fit: cover;
    display: block;
    border-radius: 9px;
}

.gal-picker__icon {
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

.gal-picker__copy { display: flex; flex-direction: column; gap: 2px; min-width: 0; }

.gal-picker__title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: #111827;
}

.gal-picker__meta {
    font-size: 0.6875rem;
    color: #9ca3af;
}

.gal-modal__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

/* ── Lightbox transition ─────────────────────────────────────────────── */
.gal-fade-enter-active,
.gal-fade-leave-active { transition: opacity 0.18s ease; }
.gal-fade-enter-from,
.gal-fade-leave-to { opacity: 0; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .gal-page-header { margin: 1.25rem 1.25rem 0; padding: 1.25rem; border-radius: 12px; }
    .gal-body { padding: 1.25rem 1.25rem 3rem; }
    .gal-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; }
    .gal-lightbox__nav { width: 36px; height: 36px; }
}
</style>
