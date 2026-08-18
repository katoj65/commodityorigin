<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import {
    Plus, Search, Box, FirstAidKit, Goods, Coin, Edit, Delete,
    WarningFilled, CircleCheck, User, ShoppingCart,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import AddAgriculturalInputDialog from '@/Components/Modals/AddAgriculturalInputDialog.vue';

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
    <AppLayout title="Agricultural Inputs" full-width flush :show-banner="false">
        <Head title="Agricultural Inputs" />

        <div class="ain-page">
            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="ain-header">
                <div class="ain-header__inner">
                    <div>
                        <div class="ain-kicker">Farm Workspace</div>
                        <h1 class="ain-title mb-0">Agricultural Inputs</h1>
                        <p class="ain-subtitle mb-0">Medicine and fertilizer products available for your farm.</p>
                    </div>
                    <div class="ain-header__actions">
                        <div class="ain-search">
                            <el-icon><Search /></el-icon>
                            <input v-model="searchTerm" type="text" placeholder="Search by name, tag, or manufacturer…">
                        </div>
                        <button v-if="canManage" type="button" class="ain-btn-primary" @click="addOpen = true">
                            <el-icon><Plus /></el-icon> Add Input
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Toolbar ───────────────────────────────────────────────── -->
            <div class="ain-toolbar">
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

                    <div class="row g-2">
                        <div class="col-6"><div class="ain-spec-cell"><span>Price</span><strong>{{ formatPrice(viewingInput.price) }} / {{ viewingInput.unit }}</strong></div></div>
                        <div class="col-6"><div class="ain-spec-cell"><span>Stock</span><strong>{{ viewingInput.stock_quantity }} {{ viewingInput.unit }}</strong></div></div>
                        <div class="col-6"><div class="ain-spec-cell"><span>Manufacturer</span><strong>{{ viewingInput.manufacturer || '—' }}</strong></div></div>
                        <div class="col-6"><div class="ain-spec-cell"><span>SKU</span><strong>{{ viewingInput.sku }}</strong></div></div>
                    </div>

                    <div v-if="viewingInput.creator_name" class="ain-view__meta">
                        <el-icon><User /></el-icon> Added by {{ viewingInput.creator_name }}
                    </div>
                </div>

                <template v-if="viewingInput" #footer>
                    <div class="ain-modal__footer">
                        <button
                            type="button"
                            class="ain-btn-primary"
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
                            <el-input v-model="editForm.name" class="ain-field-input w-100" :class="{ 'ain-field-input--error': editForm.errors.name }" />
                            <InputError class="ain-field__error" :message="editForm.errors.name" />
                        </div>
                        <div class="ain-field">
                            <label class="ain-field__label">Category</label>
                            <el-select v-model="editForm.category" class="ain-field-input w-100" :class="{ 'ain-field-input--error': editForm.errors.category }">
                                <el-option label="Medicine" value="medicine" />
                                <el-option label="Fertilizer" value="fertilizer" />
                            </el-select>
                            <InputError class="ain-field__error" :message="editForm.errors.category" />
                        </div>
                    </div>

                    <div class="ain-field-row">
                        <div class="ain-field">
                            <label class="ain-field__label">Tag</label>
                            <el-input v-model="editForm.tag" class="ain-field-input w-100" :class="{ 'ain-field-input--error': editForm.errors.tag }" />
                            <InputError class="ain-field__error" :message="editForm.errors.tag" />
                        </div>
                        <div class="ain-field">
                            <label class="ain-field__label">Manufacturer</label>
                            <el-input v-model="editForm.manufacturer" class="ain-field-input w-100" />
                        </div>
                    </div>

                    <div class="ain-field-row">
                        <div class="ain-field">
                            <label class="ain-field__label">Price</label>
                            <el-input-number v-model="editForm.price" :min="0.01" :precision="2" class="ain-field-input w-100" :class="{ 'ain-field-input--error': editForm.errors.price }" />
                            <InputError class="ain-field__error" :message="editForm.errors.price" />
                        </div>
                        <div class="ain-field">
                            <label class="ain-field__label">Stock Quantity</label>
                            <el-input-number v-model="editForm.stock_quantity" :min="0" class="ain-field-input w-100" :class="{ 'ain-field-input--error': editForm.errors.stock_quantity }" />
                            <InputError class="ain-field__error" :message="editForm.errors.stock_quantity" />
                        </div>
                    </div>

                    <div class="ain-field-row">
                        <div class="ain-field">
                            <label class="ain-field__label">Unit</label>
                            <el-input v-model="editForm.unit" class="ain-field-input w-100" :class="{ 'ain-field-input--error': editForm.errors.unit }" />
                            <InputError class="ain-field__error" :message="editForm.errors.unit" />
                        </div>
                        <div class="ain-field">
                            <label class="ain-field__label">Status</label>
                            <el-select v-model="editForm.status" class="ain-field-input w-100">
                                <el-option label="Active" value="active" />
                                <el-option label="Inactive" value="inactive" />
                            </el-select>
                        </div>
                    </div>

                    <div class="ain-field">
                        <label class="ain-field__label">Description</label>
                        <el-input v-model="editForm.description" type="textarea" :rows="3" class="ain-field-input w-100" />
                    </div>
                </form>

                <template #footer>
                    <div class="ain-modal__footer">
                        <button type="submit" form="edit-input-form" class="ain-btn-primary" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete dialog ─────────────────────────────────────────── -->
            <el-dialog v-model="deleteOpen" width="420px" align-center class="ain-modal ain-modal--danger">
                <template #header>
                    <div class="ain-modal__head">
                        <div class="ain-modal__head-icon ain-modal__head-icon--danger"><el-icon :size="18"><Delete /></el-icon></div>
                        <div class="ain-modal__head-text">
                            <div class="ain-modal__eyebrow">Agricultural Inputs</div>
                            <div class="ain-modal__title">Remove Input</div>
                        </div>
                    </div>
                </template>

                <div v-if="deletingInput" class="ain-modal__body">
                    <p class="ain-modal__confirm-text">
                        Are you sure you want to remove <strong>{{ deletingInput.name }}</strong> from the store? This action cannot be undone.
                    </p>
                </div>

                <template #footer>
                    <div class="ain-modal__footer">
                        <button type="button" class="ain-btn-outline" @click="deleteOpen = false">Cancel</button>
                        <button type="button" class="ain-btn-danger" :disabled="deleting" @click="confirmDelete">
                            {{ deleting ? 'Removing…' : 'Remove Input' }}
                        </button>
                    </div>
                </template>
            </el-dialog>
        </div>

        <AddAgriculturalInputDialog v-if="canManage" v-model="addOpen" @created="onCreated" />
    </AppLayout>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>. */
.el-dialog.ain-modal {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}
.el-dialog.ain-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.ain-modal .el-dialog__body { padding: 0; }
.el-dialog.ain-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.ain-page {
    --green: #004532;
    --green-dark: #002e20;
    --red: #dc2626;
    --amber: #b45309;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-white: #ffffff;
    --surface-low: #f8fafc;
    --surface-high: #eef2f0;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
    line-height: 1.5;
}

/* ── Header ────────────────────────────────────────────────────────────── */
.ain-header { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.ain-header__inner { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: 1rem clamp(1rem, 3vw, 2rem); }
.ain-header__actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.ain-kicker { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 4px; }
.ain-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -.02em; }
.ain-subtitle { font-size: .8125rem; color: var(--on-surface-var); margin-top: 2px; }

.ain-btn-primary { background: #004532; border: none; color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: background .15s ease; }
.ain-btn-primary:hover { background: #002e20; }
.ain-btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.ain-btn-outline { background: #fff; border: 1px solid #e5e7eb; color: #111827; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 18px; cursor: pointer; transition: background .15s ease; }
.ain-btn-outline:hover { background: #f8fafc; }
.ain-btn-danger { background: #dc2626; border: none; color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 16px; cursor: pointer; transition: background .15s ease; }
.ain-btn-danger:hover { background: #b91c1c; }
.ain-btn-danger:disabled { opacity: .6; cursor: not-allowed; }

/* ── Toolbar ───────────────────────────────────────────────────────────── */
.ain-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 1rem clamp(1rem, 3vw, 2rem) 0; }
.ain-search { display: flex; align-items: center; gap: 8px; padding: 0 12px; height: 40px; border: 1px solid var(--surface-high); border-radius: 8px; background: #fff; min-width: 280px; flex: 1; max-width: 420px; }
.ain-search .el-icon { color: #9ca3af; font-size: 15px; }
.ain-search input { border: none; outline: none; font-size: .8125rem; flex: 1; background: transparent; }
.ain-tabs { display: inline-flex; gap: 4px; padding: 4px; background: var(--surface-low); border-radius: 8px; }
.ain-tab { border: none; background: transparent; padding: 7px 14px; font-size: .8125rem; font-weight: 700; color: var(--on-surface-var); border-radius: 6px; cursor: pointer; transition: all .12s ease; }
.ain-tab.is-active { background: #fff; color: var(--green); box-shadow: 0 1px 2px rgba(15,23,42,.08); }

/* ── Grid ──────────────────────────────────────────────────────────────── */
.ain-body { padding: 1.25rem clamp(1rem, 3vw, 2rem) 2rem; }
.ain-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
.ain-card { background: #fff; border: 1px solid var(--surface-high); border-radius: 14px; overflow: hidden; cursor: pointer; box-shadow: 0 1px 2px rgba(15,23,42,.03); transition: box-shadow .15s ease, transform .15s ease; }
.ain-card:hover { box-shadow: 0 12px 28px -14px rgba(15,23,42,.22); transform: translateY(-2px); }
.ain-card__media { position: relative; height: 140px; background: var(--surface-low); display: flex; align-items: center; justify-content: center; }
.ain-card__img { width: 100%; height: 100%; object-fit: cover; }
.ain-card__placeholder { color: #c3ccd3; }
.ain-card__category { position: absolute; top: 10px; left: 10px; display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: .625rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; background: #fff; }
.ain-card__category.is-medicine { color: #1d4ed8; }
.ain-card__category.is-fertilizer { color: #047857; }
.ain-card__actions { position: absolute; top: 10px; right: 10px; display: flex; gap: 4px; }
.ain-card__icon-btn { width: 26px; height: 26px; border-radius: 7px; border: none; background: #fff; color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 1px 3px rgba(15,23,42,.15); }
.ain-card__icon-btn:hover { background: var(--surface-low); color: var(--on-surface); }
.ain-card__icon-btn--danger:hover { background: #fef2f2; color: var(--red); }
.ain-card__body { padding: 12px 14px 14px; display: flex; flex-direction: column; gap: 4px; }
.ain-card__tag { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); }
.ain-card__name { font-size: .875rem; font-weight: 700; margin: 0; line-height: 1.3; }
.ain-card__price { display: flex; align-items: center; gap: 5px; font-size: .8125rem; font-weight: 800; color: var(--green); margin-top: 4px; }
.ain-card__price .el-icon { font-size: 13px; }
.ain-card__price small { color: var(--on-surface-var); font-weight: 600; }
.ain-card__stock { display: flex; align-items: center; gap: 5px; font-size: .6875rem; font-weight: 600; margin-top: 2px; }
.ain-card__stock .el-icon { font-size: 13px; }
.ain-card__stock.is-ok { color: #047857; }
.ain-card__stock.is-low { color: var(--amber); }
.ain-card__stock.is-out { color: var(--red); }

.ain-card__buy {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    margin-top: 10px;
    padding: 8px 0;
    border: none;
    border-radius: 8px;
    background: #004532;
    color: #fff;
    font-size: .75rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s ease;
}
.ain-card__buy:hover:not(:disabled) { background: #002e20; }
.ain-card__buy:disabled { background: var(--surface-high); color: var(--on-surface-var); cursor: default; }
.ain-card__buy .el-icon { font-size: 13px; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.ain-empty { text-align: center; padding: 3rem 1rem; }
.ain-empty__icon { width: 52px; height: 52px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.ain-empty__title { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
.ain-empty__text { font-size: .8125rem; color: var(--on-surface-var); }

/* ── Pagination ────────────────────────────────────────────────────────── */
.ain-pagination { display: flex; align-items: center; justify-content: center; gap: 14px; margin-top: 24px; }
.ain-page-btn { padding: 8px 14px; border-radius: 8px; border: 1px solid var(--surface-high); background: #fff; color: var(--on-surface); font-size: .8125rem; font-weight: 700; text-decoration: none; }
.ain-page-btn.is-disabled { opacity: .4; pointer-events: none; }
.ain-page-info { font-size: .8125rem; color: var(--on-surface-var); }

/* ── Modal — literal hex values (teleported to <body>, no CSS vars) ──────── */
.ain-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #f3f4f6; }
.ain-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(0,69,50,.08); color: #004532; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ain-modal__head-icon--danger { background: #fee2e2; color: #dc2626; }
.ain-modal__head-text { flex: 1; min-width: 0; }
.ain-modal__eyebrow { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #004532; margin-bottom: 1px; }
.ain-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -.01em; }

.ain-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }
.ain-modal__confirm-text { font-size: 0.875rem; color: #374151; line-height: 1.5; margin: 0; }

.ain-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ain-field { display: flex; flex-direction: column; gap: 5px; }
.ain-field__label { font-size: .75rem; font-weight: 600; color: #374151; }
.ain-field__error { font-size: .75rem; font-weight: 600; color: #dc2626; margin-top: 4px; display: block; }

.ain-view__img { width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; }
.ain-view__desc { font-size: .8125rem; color: #374151; line-height: 1.6; margin: 0; }
.ain-view__meta { font-size: .75rem; color: #9ca3af; display: flex; align-items: center; gap: 6px; padding-top: 8px; border-top: 1px solid #f3f4f6; }

.ain-spec-cell { background: #f8fafc; border-radius: 6px; padding: 7px 9px; }
.ain-spec-cell span { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; display: block; margin-bottom: 3px; }
.ain-spec-cell strong { font-size: 0.8125rem; font-weight: 700; color: #111827; display: block; }

:deep(.ain-field-input .el-input__wrapper),
:deep(.ain-field-input .el-textarea__inner) { box-shadow: 0 0 0 1px #d1d5db inset; border-radius: 8px; }
.ain-field-input--error :deep(.el-input__wrapper),
.ain-field-input--error :deep(.el-textarea__inner),
.ain-field-input--error :deep(.el-select__wrapper) { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

.ain-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

@media (max-width: 640px) {
    .ain-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.ain-modal) { width: 92vw !important; }
}
</style>
