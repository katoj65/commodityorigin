<script setup>
import { ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import {
    Plus, Search, Box, FirstAidKit, Goods, Coin, Edit, Delete,
    WarningFilled, CircleCheck, User, ShoppingCart,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import AddAgriculturalInputDialog from '@/Components/Modals/AddAgriculturalInputDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    inputs: {
        type: Object,
        default: () => ({ data: [], meta: { current_page: 1, last_page: 1, per_page: 12, total: 0 } }),
    },
    tagOptions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ search: '', category: null }) },
    canManage: { type: Boolean, default: false },
});

/* ── Search + category filter ─────────────────────────────────────────── */
const searchTerm = ref(props.filters.search || '');
const activeCategory = ref(props.filters.category || '');

function applyFilters() {
    router.get(route('farm.inputs.index'), {
        search: searchTerm.value || undefined,
        category: activeCategory.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

let searchDebounce = null;
watch(searchTerm, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(applyFilters, 400);
});

function selectCategory(category) {
    activeCategory.value = category;
    applyFilters();
}

/* ── Presentation helpers ─────────────────────────────────────────────── */
function categoryLabel(category) {
    return category === 'medicine' ? 'Medicine' : 'Fertilizer';
}
function categoryIcon(category) {
    return category === 'medicine' ? FirstAidKit : Goods;
}

/* Items without an uploaded photo get a stable placeholder image (seeded
   by SKU, so the same item always shows the same picture instead of a
   different random one on every reload) rather than sitting empty. If
   that placeholder itself fails to load (e.g. offline), fall back to the
   category icon. */
const brokenImages = ref({});
function placeholderImageUrl(item) {
    return `https://picsum.photos/seed/${item.sku}/480/360`;
}
function displayImageUrl(item) {
    if (brokenImages.value[item.id]) return null;
    return item.image_url || placeholderImageUrl(item);
}
function onImageError(item) {
    brokenImages.value = { ...brokenImages.value, [item.id]: true };
}
function formatPrice(value) {
    return `Shs. ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
function stockTone(item) {
    if (item.stock_quantity <= 0) return 'out';
    if (item.stock_quantity < 20) return 'low';
    return 'ok';
}
function stockLabel(item) {
    if (item.stock_quantity <= 0) return 'Out of stock';
    if (item.stock_quantity < 20) return `Low stock · ${item.stock_quantity} ${item.unit}`;
    return `${item.stock_quantity} ${item.unit} in stock`;
}

/* ── Add input ─────────────────────────────────────────────────────────── */
const addOpen = ref(false);

/* ── View input ────────────────────────────────────────────────────────── */
const viewOpen = ref(false);
const viewingInput = ref(null);
function openView(item) {
    viewingInput.value = item;
    viewOpen.value = true;
}

/* ── Buy ───────────────────────────────────────────────────────────────── */
const buyingId = ref(null);

function addToCart(item) {
    if (buyingId.value || item.stock_quantity <= 0) return;

    buyingId.value = item.id;
    router.post(route('checkout.items.store'), {
        cartable_type: 'agricultural_input',
        cartable_id: item.id,
        quantity: 1,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            ElNotification({
                title: 'Added to Cart',
                message: `${item.name} was added to your cart.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
        },
        onFinish: () => { buyingId.value = null; },
    });
}

/* ── Edit input ────────────────────────────────────────────────────────── */
const editOpen = ref(false);
const editingInput = ref(null);
const editForm = useForm({
    name: '', category: '', tag: '', description: '', price: 0,
    stock_quantity: 0, unit: '', manufacturer: '', status: 'active',
});

function openEdit(item) {
    editingInput.value = item;
    editForm.clearErrors();
    editForm.name = item.name;
    editForm.category = item.category;
    editForm.tag = item.tag || '';
    editForm.description = item.description || '';
    editForm.price = Number(item.price);
    editForm.stock_quantity = item.stock_quantity;
    editForm.unit = item.unit;
    editForm.manufacturer = item.manufacturer || '';
    editForm.status = item.status;
    editOpen.value = true;
}

function submitEdit() {
    if (!editingInput.value) return;
    editForm.patch(route('farm.inputs.update', editingInput.value.id), {
        preserveScroll: true,
        onSuccess: () => { editOpen.value = false; },
    });
}

/* ── Delete input ──────────────────────────────────────────────────────── */
const deleteOpen = ref(false);
const deletingInput = ref(null);
const deleting = ref(false);

function openDelete(item) {
    deletingInput.value = item;
    deleteOpen.value = true;
}

function confirmDelete() {
    if (!deletingInput.value) return;
    deleting.value = true;
    router.delete(route('farm.inputs.destroy', deletingInput.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteOpen.value = false;
            deletingInput.value = null;
        },
    });
}

function onCreated() {
    ElNotification({
        title: 'Input Added',
        message: 'The new input is now live in the store.',
        type: 'success',
        duration: 3200,
        offset: 84,
    });
}
</script>

<template>
    <DesignPreviewLayout title="Agricultural Inputs">
        <div class="ain-page">
            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="ain-header">
                <div class="ain-header__text">
                    <h1 class="ain-title">Agricultural Inputs</h1>
                    <p class="ain-subtitle">Medicine and fertilizer products available for your farm.</p>
                </div>
                <button v-if="canManage" type="button" class="ain-btn ain-btn--primary" @click="addOpen = true">
                    <el-icon><Plus /></el-icon> Add Input
                </button>
            </div>

            <!-- ── Toolbar ───────────────────────────────────────────────── -->
            <div class="ain-toolbar">
                <div class="ain-search">
                    <el-icon><Search /></el-icon>
                    <input v-model="searchTerm" type="text" placeholder="Search by name, tag, or manufacturer…">
                </div>
                <div class="ain-tabs">
                    <button type="button" class="ain-tab" :class="{ 'is-active': !activeCategory }" @click="selectCategory('')">All</button>
                    <button type="button" class="ain-tab" :class="{ 'is-active': activeCategory === 'medicine' }" @click="selectCategory('medicine')">Medicine</button>
                    <button type="button" class="ain-tab" :class="{ 'is-active': activeCategory === 'fertilizer' }" @click="selectCategory('fertilizer')">Fertilizer</button>
                </div>
            </div>

            <!-- ── Grid ──────────────────────────────────────────────────── -->
            <div class="ain-body">
                <div v-if="inputs.data.length" class="ain-grid">
                    <article v-for="item in inputs.data" :key="item.id" class="ain-card" @click="openView(item)">
                        <div class="ain-card__media">
                            <img
                                v-if="displayImageUrl(item)"
                                :src="displayImageUrl(item)"
                                :alt="item.name"
                                class="ain-card__img"
                                loading="lazy"
                                @error="onImageError(item)"
                            >
                            <div v-else class="ain-card__placeholder">
                                <el-icon :size="28"><component :is="categoryIcon(item.category)" /></el-icon>
                            </div>
                            <span class="ain-card__category" :class="`is-${item.category}`">
                                <el-icon><component :is="categoryIcon(item.category)" /></el-icon>
                                {{ categoryLabel(item.category) }}
                            </span>
                            <div v-if="canManage" class="ain-card__actions" @click.stop>
                                <button type="button" class="ain-card__icon-btn" aria-label="Edit input" @click="openEdit(item)">
                                    <el-icon><Edit /></el-icon>
                                </button>
                                <button type="button" class="ain-card__icon-btn ain-card__icon-btn--danger" aria-label="Delete input" @click="openDelete(item)">
                                    <el-icon><Delete /></el-icon>
                                </button>
                            </div>
                        </div>
                        <div class="ain-card__body">
                            <span v-if="item.tag" class="ain-card__tag">{{ item.tag }}</span>
                            <h3 class="ain-card__name">{{ item.name }}</h3>
                            <div class="ain-card__price"><el-icon><Coin /></el-icon> {{ formatPrice(item.price) }} <small>/ {{ item.unit }}</small></div>
                            <div class="ain-card__stock" :class="`is-${stockTone(item)}`">
                                <el-icon><component :is="stockTone(item) === 'ok' ? CircleCheck : WarningFilled" /></el-icon>
                                {{ stockLabel(item) }}
                            </div>
                            <button
                                type="button"
                                class="ain-card__buy"
                                :disabled="item.stock_quantity <= 0 || buyingId === item.id"
                                @click.stop="addToCart(item)"
                            >
                                <el-icon><ShoppingCart /></el-icon>
                                {{ item.stock_quantity <= 0 ? 'Out of Stock' : (buyingId === item.id ? 'Adding…' : 'Buy') }}
                            </button>
                        </div>
                    </article>
                </div>

                <div v-else class="ain-empty">
                    <div class="ain-empty__icon"><el-icon :size="24"><Box /></el-icon></div>
                    <div class="ain-empty__title">No inputs found</div>
                    <p class="ain-empty__text">Try a different search term or category filter.</p>
                </div>

                <div v-if="inputs.meta.last_page > 1" class="ain-pagination">
                    <Link
                        :href="route('farm.inputs.index', { page: Math.max(1, inputs.meta.current_page - 1), search: filters.search || undefined, category: filters.category || undefined })"
                        class="ain-page-btn" :class="{ 'is-disabled': inputs.meta.current_page <= 1 }"
                    >← Prev</Link>
                    <span class="ain-page-info">Page {{ inputs.meta.current_page }} of {{ inputs.meta.last_page }}</span>
                    <Link
                        :href="route('farm.inputs.index', { page: Math.min(inputs.meta.last_page, inputs.meta.current_page + 1), search: filters.search || undefined, category: filters.category || undefined })"
                        class="ain-page-btn" :class="{ 'is-disabled': inputs.meta.current_page >= inputs.meta.last_page }"
                    >Next →</Link>
                </div>
            </div>

            <!-- ── View dialog ───────────────────────────────────────────── -->
            <el-dialog v-model="viewOpen" width="min(560px, calc(100vw - 2rem))" align-center class="ain-modal">
                <template #header>
                    <div class="ain-modal__head">
                        <div class="ain-modal__head-icon"><el-icon :size="18"><component :is="viewingInput ? categoryIcon(viewingInput.category) : Box" /></el-icon></div>
                        <div class="ain-modal__head-text">
                            <div class="ain-modal__eyebrow">{{ viewingInput ? categoryLabel(viewingInput.category) : '' }}</div>
                            <div class="ain-modal__title">{{ viewingInput?.name }}</div>
                        </div>
                    </div>
                </template>

                <div v-if="viewingInput" class="ain-modal__body">
                    <img
                        v-if="displayImageUrl(viewingInput)"
                        :src="displayImageUrl(viewingInput)"
                        :alt="viewingInput.name"
                        class="ain-view__img"
                        @error="onImageError(viewingInput)"
                    >
                    <p v-if="viewingInput.description" class="ain-view__desc">{{ viewingInput.description }}</p>

                    <div class="ain-spec-grid">
                        <div class="ain-spec-cell"><span>Price</span><strong>{{ formatPrice(viewingInput.price) }} / {{ viewingInput.unit }}</strong></div>
                        <div class="ain-spec-cell"><span>Stock</span><strong>{{ viewingInput.stock_quantity }} {{ viewingInput.unit }}</strong></div>
                        <div class="ain-spec-cell"><span>Manufacturer</span><strong>{{ viewingInput.manufacturer || '—' }}</strong></div>
                        <div class="ain-spec-cell"><span>SKU</span><strong>{{ viewingInput.sku }}</strong></div>
                    </div>

                    <div v-if="viewingInput.creator_name" class="ain-view__meta">
                        <el-icon><User /></el-icon> Added by {{ viewingInput.creator_name }}
                    </div>
                </div>

                <template v-if="viewingInput" #footer>
                    <div class="ain-modal__footer">
                        <button
                            type="button"
                            class="ain-btn ain-btn--primary"
                            :disabled="viewingInput.stock_quantity <= 0 || buyingId === viewingInput.id"
                            @click="addToCart(viewingInput)"
                        >
                            <el-icon><ShoppingCart /></el-icon>
                            {{ viewingInput.stock_quantity <= 0 ? 'Out of Stock' : (buyingId === viewingInput.id ? 'Adding…' : 'Buy Now') }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Edit dialog ───────────────────────────────────────────── -->
            <el-dialog v-model="editOpen" width="min(640px, calc(100vw - 2rem))" align-center class="ain-modal">
                <template #header>
                    <div class="ain-modal__head">
                        <div class="ain-modal__head-icon"><el-icon :size="18"><Edit /></el-icon></div>
                        <div class="ain-modal__head-text">
                            <div class="ain-modal__eyebrow">Agricultural Inputs</div>
                            <div class="ain-modal__title">Edit Input</div>
                        </div>
                    </div>
                </template>

                <form id="edit-input-form" novalidate class="ain-modal__body" @submit.prevent="submitEdit">
                    <div class="ain-field-row">
                        <div class="ain-field">
                            <label class="ain-field__label">Name</label>
                            <el-input v-model="editForm.name" class="ain-field-input" :class="{ 'ain-field-input--error': editForm.errors.name }" />
                            <InputError class="ain-field__error" :message="editForm.errors.name" />
                        </div>
                        <div class="ain-field">
                            <label class="ain-field__label">Category</label>
                            <el-select v-model="editForm.category" class="ain-field-input" :class="{ 'ain-field-input--error': editForm.errors.category }">
                                <el-option label="Medicine" value="medicine" />
                                <el-option label="Fertilizer" value="fertilizer" />
                            </el-select>
                            <InputError class="ain-field__error" :message="editForm.errors.category" />
                        </div>
                    </div>

                    <div class="ain-field-row">
                        <div class="ain-field">
                            <label class="ain-field__label">Tag</label>
                            <el-input v-model="editForm.tag" class="ain-field-input" :class="{ 'ain-field-input--error': editForm.errors.tag }" />
                            <InputError class="ain-field__error" :message="editForm.errors.tag" />
                        </div>
                        <div class="ain-field">
                            <label class="ain-field__label">Manufacturer</label>
                            <el-input v-model="editForm.manufacturer" class="ain-field-input" />
                        </div>
                    </div>

                    <div class="ain-field-row">
                        <div class="ain-field">
                            <label class="ain-field__label">Price</label>
                            <el-input-number v-model="editForm.price" :min="0.01" :precision="2" class="ain-field-input" :class="{ 'ain-field-input--error': editForm.errors.price }" />
                            <InputError class="ain-field__error" :message="editForm.errors.price" />
                        </div>
                        <div class="ain-field">
                            <label class="ain-field__label">Stock Quantity</label>
                            <el-input-number v-model="editForm.stock_quantity" :min="0" class="ain-field-input" :class="{ 'ain-field-input--error': editForm.errors.stock_quantity }" />
                            <InputError class="ain-field__error" :message="editForm.errors.stock_quantity" />
                        </div>
                    </div>

                    <div class="ain-field-row">
                        <div class="ain-field">
                            <label class="ain-field__label">Unit</label>
                            <el-input v-model="editForm.unit" class="ain-field-input" :class="{ 'ain-field-input--error': editForm.errors.unit }" />
                            <InputError class="ain-field__error" :message="editForm.errors.unit" />
                        </div>
                        <div class="ain-field">
                            <label class="ain-field__label">Status</label>
                            <el-select v-model="editForm.status" class="ain-field-input">
                                <el-option label="Active" value="active" />
                                <el-option label="Inactive" value="inactive" />
                            </el-select>
                        </div>
                    </div>

                    <div class="ain-field">
                        <label class="ain-field__label">Description</label>
                        <el-input v-model="editForm.description" type="textarea" :rows="3" class="ain-field-input" />
                    </div>
                </form>

                <template #footer>
                    <div class="ain-modal__footer">
                        <button type="submit" form="edit-input-form" class="ain-btn ain-btn--primary" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete dialog ─────────────────────────────────────────── -->
            <ConfirmDialog
                v-model="deleteOpen"
                eyebrow="Agricultural Inputs"
                title="Remove Input"
                :message="deletingInput ? `Are you sure you want to remove ${deletingInput.name} from the store? This action cannot be undone.` : ''"
                confirm-text="Remove Input"
                loading-text="Removing…"
                :auto-close="false"
                :loading="deleting"
                @confirm="confirmDelete"
            />
        </div>

        <AddAgriculturalInputDialog v-if="canManage" v-model="addOpen" @created="onCreated" />
    </DesignPreviewLayout>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, so it
   can't see .ain-page's scoped CSS vars — same convention as every
   other modal in the app (AttachBatchModal, Apps' Create Agent dialog). */
.el-dialog.ain-modal {
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.ain-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.ain-modal .el-dialog__body { padding: 0; }
.el-dialog.ain-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.ain-page {
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --surface-elevated: #F1F2F3;
    --border: #E5E7EB;
    --primary: #000000;
    --on-primary: #ffffff;
    --text: #121516;
    --text-2: #4B5457;
    --text-muted: #6F7677;
    --success: #15803D;
    --success-soft: #F0FDF4;
    --warning: #B45309;
    --warning-soft: #FEF3E2;
    --error: #B91C1C;
    --error-soft: #FEF2F2;
    --info: #1D4ED8;
    --info-soft: #EFF6FF;
    --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-family: var(--font-sans);
    background: var(--surface);
    color: var(--text);
    min-height: 100%;
}

/* ── Header ────────────────────────────────────────────────────────────── */
.ain-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
.ain-header__text { min-width: 0; }
.ain-title { font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0 0 6px; }
.ain-subtitle { font-size: 13.5px; line-height: 20px; color: var(--text-2); margin: 0; max-width: 60ch; }

.ain-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    height: 36px; padding: 0 16px; border-radius: 6px;
    font-size: 13px; font-weight: 600; border: 1px solid transparent;
    cursor: pointer; transition: opacity 120ms ease, background 120ms ease;
}
.ain-btn--primary { background: var(--primary); color: var(--on-primary); }
.ain-btn--primary:hover:not(:disabled) { opacity: 0.88; }
.ain-btn--primary:disabled { opacity: 0.5; cursor: default; }

/* ── Toolbar ───────────────────────────────────────────────────────────── */
.ain-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.ain-search {
    display: flex; align-items: center; gap: 8px; padding: 0 12px; height: 38px;
    border: 1px solid var(--border); border-radius: 6px; background: var(--surface);
    min-width: 260px; flex: 1; max-width: 420px;
}
.ain-search .el-icon { color: var(--text-muted); font-size: 15px; }
.ain-search input { border: none; outline: none; font-size: 13px; flex: 1; background: transparent; color: var(--text); font-family: inherit; }
.ain-search input::placeholder { color: var(--text-muted); }
.ain-tabs { display: inline-flex; gap: 2px; padding: 3px; background: var(--surface-muted); border-radius: 8px; }
.ain-tab { border: none; background: transparent; padding: 7px 14px; font-size: 13px; font-weight: 600; color: var(--text-2); border-radius: 6px; cursor: pointer; transition: background 120ms ease, color 120ms ease; }
.ain-tab.is-active { background: var(--surface); color: var(--text); }

/* ── Grid ──────────────────────────────────────────────────────────────── */
.ain-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
.ain-card { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: 10px; cursor: pointer; transition: border-color 120ms ease; }
.ain-card:hover { border-color: var(--text-muted); }
.ain-card__media { position: relative; height: 132px; background: var(--surface-muted); border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.ain-card__img { width: 100%; height: 100%; object-fit: cover; }
.ain-card__placeholder { color: var(--text-muted); }
.ain-card__category { position: absolute; top: 10px; left: 10px; display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; background: var(--surface); }
.ain-card__category.is-medicine { color: var(--info); }
.ain-card__category.is-fertilizer { color: var(--success); }
.ain-card__actions { position: absolute; top: 10px; right: 10px; display: flex; gap: 4px; }
.ain-card__icon-btn { width: 26px; height: 26px; border-radius: 6px; border: none; background: var(--surface); color: var(--text-2); display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
.ain-card__icon-btn:hover { background: var(--surface-muted); color: var(--text); }
.ain-card__icon-btn--danger:hover { background: var(--error-soft); color: var(--error); }
.ain-card__body { padding: 0; display: flex; flex-direction: column; gap: 4px; }
.ain-card__tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); }
.ain-card__name { font-size: 14px; font-weight: 700; margin: 0; line-height: 1.3; color: var(--text); }
.ain-card__price { display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 700; color: var(--text); margin-top: 4px; }
.ain-card__price .el-icon { font-size: 13px; color: var(--text-muted); }
.ain-card__price small { color: var(--text-muted); font-weight: 600; }
.ain-card__stock { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; margin-top: 2px; }
.ain-card__stock .el-icon { font-size: 13px; }
.ain-card__stock.is-ok { color: var(--success); }
.ain-card__stock.is-low { color: var(--warning); }
.ain-card__stock.is-out { color: var(--error); }

.ain-card__buy {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; margin-top: 10px; padding: 8px 0;
    border: none; border-radius: 6px;
    background: var(--primary); color: var(--on-primary);
    font-size: 12px; font-weight: 600; cursor: pointer;
    transition: opacity 120ms ease;
}
.ain-card__buy:hover:not(:disabled) { opacity: 0.88; }
.ain-card__buy:disabled { background: var(--surface-elevated); color: var(--text-muted); cursor: default; }
.ain-card__buy .el-icon { font-size: 13px; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.ain-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center; padding: 48px 20px; background: var(--surface-muted); border: 1px solid var(--border); border-radius: 6px; }
.ain-empty__icon { color: var(--text-muted); }
.ain-empty__title { font-size: 14px; font-weight: 700; color: var(--text); }
.ain-empty__text { font-size: 13px; color: var(--text-muted); margin: 0; }

/* ── Pagination ────────────────────────────────────────────────────────── */
.ain-pagination { display: flex; align-items: center; justify-content: center; gap: 14px; margin-top: 24px; }
.ain-page-btn { padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface); color: var(--text); font-size: 13px; font-weight: 600; text-decoration: none; transition: background 120ms ease; }
.ain-page-btn:hover:not(.is-disabled) { background: var(--surface-muted); }
.ain-page-btn.is-disabled { opacity: .4; pointer-events: none; }
.ain-page-info { font-size: 13px; color: var(--text-muted); }

/* ── Modal — literal hex values (teleported to <body>, no CSS vars); same
   palette as AttachBatchModal.vue / the Apps page's Create Agent dialog. ── */
.ain-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.ain-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ain-modal__head-text { flex: 1; min-width: 0; }
.ain-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6F7677; margin-bottom: 1px; }
.ain-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }

.ain-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }

.ain-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ain-field { display: flex; flex-direction: column; gap: 5px; }
.ain-field-input { width: 100%; }
.ain-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.ain-field__error { font-size: 12px; font-weight: 500; color: #F85149; margin-top: 4px; display: block; }

.ain-view__img { width: 100%; max-height: 220px; object-fit: cover; border-radius: 6px; }
.ain-view__desc { font-size: 13px; color: #4B5457; line-height: 1.6; margin: 0; }
.ain-view__meta { font-size: 12px; color: #6F7677; display: flex; align-items: center; gap: 6px; padding-top: 8px; border-top: 1px solid #F1F2F3; }

.ain-spec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.ain-spec-cell { background: #F5F6F7; border-radius: 6px; padding: 7px 9px; }
.ain-spec-cell span { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6F7677; display: block; margin-bottom: 3px; }
.ain-spec-cell strong { font-size: 13px; font-weight: 700; color: #121516; display: block; }

:deep(.ain-field-input .el-input__wrapper),
:deep(.ain-field-input .el-textarea__inner),
:deep(.ain-field-input .el-select__wrapper) { box-shadow: 0 0 0 1px #E5E7EB inset; border-radius: 6px; background: #F5F6F7; }
.ain-field-input--error :deep(.el-input__wrapper),
.ain-field-input--error :deep(.el-textarea__inner),
.ain-field-input--error :deep(.el-select__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.ain-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #F5F6F7; border-top: 1px solid #E5E7EB; }

@media (max-width: 640px) {
    .ain-header { flex-direction: column; align-items: stretch; }
    .ain-field-row, .ain-spec-grid { grid-template-columns: 1fr; }
    :deep(.el-dialog.ain-modal) { width: 92vw !important; }
}
</style>
