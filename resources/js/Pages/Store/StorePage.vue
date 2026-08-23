<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import AddEditStoreItemDialog from '@/Components/Modals/AddEditStoreItemDialog.vue';
import StoreItemStatusDialog from '@/Components/Modals/StoreItemStatusDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Check, Close, Clock, Delete, EditPen, Goods, Plus, RefreshLeft, Search, Shop, UploadFilled, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    store: { type: Object, default: null },
    items: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    isAdmin: { type: Boolean, default: false },
    pendingStores: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
});

/* ── Shared with StoreLayout's header buttons via v-model ─────────────── */
const storeDialogOpen = ref(false);
const statusFilter = ref('all');
const importResultVisible = ref(Boolean(props.importResult));

/* ── Search + filter + pagination ─────────────────────────────────────── */
const searchQuery = ref('');
const categoryFilter = ref('all');
const sortBy = ref('newest');
const currentPage = ref(1);
const pageSize = 30;

const categories = computed(() => {
    const seen = new Set();
    props.items.forEach((i) => { if (i.category) seen.add(i.category); });
    return [...seen].sort();
});

function resetFilters() {
    searchQuery.value = '';
    categoryFilter.value = 'all';
    sortBy.value = 'newest';
}

const filteredItems = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();

    const filtered = props.items.filter((i) => {
        const matchesStatus = statusFilter.value === 'all' || i.status === statusFilter.value;
        const matchesCategory = categoryFilter.value === 'all' || i.category === categoryFilter.value;
        const matchesSearch = !q || [i.name, i.category, i.status].filter(Boolean).join(' ').toLowerCase().includes(q);
        return matchesStatus && matchesCategory && matchesSearch;
    });

    const sorted = [...filtered];
    if (sortBy.value === 'price_asc') sorted.sort((a, b) => a.price - b.price);
    else if (sortBy.value === 'price_desc') sorted.sort((a, b) => b.price - a.price);
    else sorted.sort((a, b) => (b.created_at ?? '').localeCompare(a.created_at ?? ''));

    return sorted;
});

const pagedItems = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredItems.value.slice(start, start + pageSize);
});

watch([searchQuery, statusFilter, categoryFilter, sortBy, () => props.items], () => {
    currentPage.value = 1;
});

/* ── Category pill tone — hashed so the same category always renders the
   same tone, matching Store/Market.vue's browse-table treatment. ─────── */
const TONES = ['st-pill--a', 'st-pill--b', 'st-pill--c', 'st-pill--d'];

function pillTone(value) {
    if (!value) return TONES[0];
    let hash = 0;
    for (let i = 0; i < value.length; i++) hash = (hash * 31 + value.charCodeAt(i)) % TONES.length;
    return TONES[hash];
}

function formatMoney(value, currency) {
    const amount = Number(value ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${amount}` : amount;
}

function statusLabel(status) {
    if (!status) return '—';
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function formatDate(value) {
    if (!value) return '';
    return new Date(value.replace(' ', 'T')).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

/* ── Edit item (row-level; header's "Add Item" button owns creation) ──── */
const itemDialogOpen = ref(false);
const editingItem = ref(null);

function openEditItem(item) {
    editingItem.value = item;
    itemDialogOpen.value = true;
}

function openItemPage(item, event) {
    if (event.target.closest('button, a')) return;
    router.visit(route('store.items.show', item.id));
}

/* ── Status / trace ───────────────────────────────────────────────────── */
const statusDialogOpen = ref(false);
const activeItem = ref(null);

function openStatusDialog(item) {
    activeItem.value = item;
    statusDialogOpen.value = true;
}

/* ── Remove item ──────────────────────────────────────────────────────── */
const removingId = ref(null);
const removeOpen = ref(false);
const pendingRemove = ref(null);

function requestRemove(item) {
    pendingRemove.value = item;
    removeOpen.value = true;
}

function confirmRemove() {
    const item = pendingRemove.value;
    if (!item) return;

    removingId.value = item.id;
    router.delete(route('store.items.destroy', item.id), {
        preserveScroll: true,
        onFinish: () => { removingId.value = null; },
    });
}

/* ── Admin: verify / reject pending stores ───────────────────────────── */
const verifyingId = ref(null);

function verifyStore(pending) {
    verifyingId.value = pending.id;
    router.post(route('store.verify', pending.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            ElNotification({ title: 'Store Verified', message: `${pending.owner_name}'s store can now open.`, type: 'success', duration: 3200, offset: 84 });
        },
        onFinish: () => { verifyingId.value = null; },
    });
}

const rejectDialogOpen = ref(false);
const rejectingStore = ref(null);
const rejectForm = useForm({ reason: '' });

function requestReject(pending) {
    rejectingStore.value = pending;
    rejectForm.reset();
    rejectForm.clearErrors();
    rejectDialogOpen.value = true;
}

function submitReject() {
    rejectForm.post(route('store.reject', rejectingStore.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            rejectDialogOpen.value = false;
            ElNotification({ title: 'Store Rejected', message: `${rejectingStore.value.owner_name}'s store was rejected.`, type: 'warning', duration: 3200, offset: 84 });
        },
    });
}
</script>

<template>
    <StoreLayout
        title="My Store"
        :store="store"
        :status-options="statusOptions"
        :import-result="importResult"
        v-model:store-dialog-open="storeDialogOpen"
        v-model:status-filter="statusFilter"
        v-model:import-result-visible="importResultVisible"
    >

        <div class="st-page">
            <!-- ── Editorial intro — always shown, regardless of store state ── -->
            <div class="st-body st-body--no-bottom-pad">
                <div class="st-hero">
                    <span class="st-kicker">The Store · Bean Origin</span>
                    <h1 class="dp-display-md">My Store</h1>
                    <p class="st-subtitle">Every item you've listed, its live status, and its full trace history — in one place.</p>
                </div>
            </div>

            <!-- ── Admin: pending store verifications ───────────────────── -->
            <div v-if="isAdmin && pendingStores.length" class="st-body st-body--no-bottom-pad">
                <div class="st-pending-card">
                    <div class="st-pending-card__head">
                        <el-icon :size="15"><WarningFilled /></el-icon>
                        Pending Store Verifications
                        <span class="st-pending-count">{{ pendingStores.length }}</span>
                    </div>
                    <div v-for="pending in pendingStores" :key="pending.id" class="st-pending-row">
                        <div class="st-pending-row__info">
                            <div class="st-pending-row__name">{{ pending.owner_name }}</div>
                            <div class="st-pending-row__meta">requested {{ formatDate(pending.created_at) }}</div>
                        </div>
                        <div class="st-pending-row__actions">
                            <button type="button" class="st-btn-outline st-btn-outline--danger" @click="requestReject(pending)">
                                <el-icon><Close /></el-icon> Reject
                            </button>
                            <button type="button" class="st-btn-primary" :disabled="verifyingId === pending.id" @click="verifyStore(pending)">
                                <el-icon><Check /></el-icon> Verify
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Import results ───────────────────────────────────────── -->
            <div v-if="importResult && importResultVisible" class="st-body st-body--no-bottom-pad">
                <div class="st-import-panel" :class="{ 'st-import-panel--warn': importResult.errors.length }">
                    <div class="st-import-panel__icon">
                        <el-icon :size="16"><WarningFilled v-if="importResult.errors.length" /><UploadFilled v-else /></el-icon>
                    </div>
                    <div class="st-import-panel__body">
                        <div class="st-import-panel__title">
                            {{ importResult.imported }} item{{ importResult.imported === 1 ? '' : 's' }} imported
                            <span v-if="importResult.errors.length">, {{ importResult.errors.length }} row{{ importResult.errors.length === 1 ? '' : 's' }} skipped</span>
                        </div>
                        <ul v-if="importResult.errors.length" class="st-import-panel__list">
                            <li v-for="err in importResult.errors" :key="err.row">
                                Row {{ err.row }}: {{ err.errors.join(' ') }}
                            </li>
                        </ul>
                    </div>
                    <button type="button" class="st-import-panel__close" aria-label="Dismiss" @click="importResultVisible = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </div>

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div class="st-body">
                <!-- No store yet -->
                <div v-if="!store" class="st-empty">
                    <div class="st-empty__icon"><el-icon :size="22"><Shop /></el-icon></div>
                    <div class="st-empty__title">You haven't requested a store yet</div>
                    <p class="st-empty__text">Request your store and an admin will review it before you can start adding items.</p>
                    <button type="button" class="st-btn-primary mt-2" @click="storeDialogOpen = true">
                        <el-icon><Plus /></el-icon> Request Your Store
                    </button>
                </div>

                <!-- Pending verification -->
                <div v-else-if="store.verification_status === 'pending'" class="st-status-banner st-status-banner--pending">
                    <el-icon :size="18"><Clock /></el-icon>
                    <div>
                        <div class="st-status-banner__title">Awaiting admin verification</div>
                        <p class="st-status-banner__text">Your store request is under review. You'll be able to add items once it's verified.</p>
                    </div>
                </div>

                <!-- Rejected -->
                <div v-else-if="store.verification_status === 'rejected'" class="st-status-banner st-status-banner--rejected">
                    <el-icon :size="18"><WarningFilled /></el-icon>
                    <div>
                        <div class="st-status-banner__title">Verification rejected</div>
                        <p class="st-status-banner__text">{{ store.rejection_reason || 'No reason was given.' }}</p>
                        <button type="button" class="st-btn-primary mt-2" @click="storeDialogOpen = true">
                            <el-icon><EditPen /></el-icon> Resubmit
                        </button>
                    </div>
                </div>

                <!-- Verified: items inventory -->
                <template v-else>
                    <div v-if="items.length" class="st-inventory">
                        <!-- ── Search + filters ─────────────────────────────── -->
                        <div class="st-controls">
                            <div class="st-search-row">
                                <div class="st-search">
                                    <el-icon><Search /></el-icon>
                                    <input v-model="searchQuery" type="text" placeholder="Search items…">
                                </div>
                                <span class="st-stat"><strong>{{ filteredItems.length }}</strong> of {{ items.length }}</span>
                            </div>

                            <div class="st-filter-bar">
                                <el-select v-model="categoryFilter" class="st-filter-pill" placeholder="Category">
                                    <el-option label="Category: All" value="all" />
                                    <el-option v-for="c in categories" :key="c" :label="c" :value="c" />
                                </el-select>
                                <el-select v-model="sortBy" class="st-filter-pill" placeholder="Sort">
                                    <el-option label="Newest First" value="newest" />
                                    <el-option label="Price: Low to High" value="price_asc" />
                                    <el-option label="Price: High to Low" value="price_desc" />
                                </el-select>
                                <button type="button" class="st-reset" @click="resetFilters">
                                    <el-icon :size="14"><RefreshLeft /></el-icon> Reset Filters
                                </button>
                            </div>
                        </div>

                    <div class="st-table-card">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 st-table">
                                <thead>
                                    <tr>
                                        <th><span class="st-th"><el-icon><Goods /></el-icon> Item</span></th>
                                        <th>Category</th>
                                        <th>Price / Qty</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in pagedItems" :key="item.id" class="st-row" @click="openItemPage(item, $event)">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="st-thumb">
                                                    <img v-if="item.image_url" :src="item.image_url" :alt="item.name">
                                                    <el-icon v-else :size="15"><Goods /></el-icon>
                                                </span>
                                                <div>
                                                    <Link :href="route('store.items.show', item.id)" class="st-name st-name--link">{{ item.name }}</Link>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span v-if="item.category" class="st-pill" :class="pillTone(item.category)">{{ item.category }}</span>
                                            <span v-else class="st-muted">—</span>
                                        </td>
                                        <td>
                                            <div class="st-price">{{ formatMoney(item.price, item.currency_code) }}</div>
                                            <div class="st-muted" style="font-size:.75rem;">{{ item.quantity }} {{ item.unit || '' }}</div>
                                        </td>
                                        <td>
                                            <button type="button" class="st-status" :class="`st-status--${item.status}`" @click="openStatusDialog(item)">
                                                {{ statusLabel(item.status) }}
                                            </button>
                                        </td>
                                        <td class="text-end">
                                            <div class="st-row-actions">
                                                <el-tooltip content="Status & trace history" placement="top">
                                                    <button type="button" class="st-act-btn st-act-btn--trace" @click="openStatusDialog(item)">
                                                        <el-icon :size="15"><Clock /></el-icon>
                                                    </button>
                                                </el-tooltip>
                                                <el-tooltip content="Edit item" placement="top">
                                                    <button type="button" class="st-act-btn st-act-btn--edit" @click="openEditItem(item)">
                                                        <el-icon :size="15"><EditPen /></el-icon>
                                                    </button>
                                                </el-tooltip>
                                                <el-tooltip content="Remove item" placement="top">
                                                    <button
                                                        type="button"
                                                        class="st-act-btn st-act-btn--danger"
                                                        :disabled="removingId === item.id"
                                                        @click="requestRemove(item)"
                                                    >
                                                        <el-icon :size="15"><Delete /></el-icon>
                                                    </button>
                                                </el-tooltip>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-if="!filteredItems.length" class="st-no-results">
                                <el-icon :size="18"><Search /></el-icon>
                                <span v-if="searchQuery">No items match “{{ searchQuery }}”.</span>
                                <span v-else>No items match your filters.</span>
                            </div>
                        </div>

                        <div v-if="pagedItems.length" class="st-cards">
                            <div v-for="item in pagedItems" :key="item.id" class="st-card">
                                <div class="st-card__top">
                                    <span class="st-thumb">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name">
                                        <el-icon v-else :size="15"><Goods /></el-icon>
                                    </span>
                                    <div class="st-card__title">
                                        <Link :href="route('store.items.show', item.id)" class="st-name st-name--link">{{ item.name }}</Link>
                                        <div class="st-muted" style="font-size:.75rem;">{{ item.category || '—' }}</div>
                                    </div>
                                    <button type="button" class="st-status" :class="`st-status--${item.status}`" @click="openStatusDialog(item)">
                                        {{ statusLabel(item.status) }}
                                    </button>
                                </div>
                                <div class="st-card__body">
                                    <div class="st-price">{{ formatMoney(item.price, item.currency_code) }}</div>
                                    <div class="st-muted" style="font-size:.75rem;">{{ item.quantity }} {{ item.unit || '' }}</div>
                                </div>
                                <div class="st-card__actions">
                                    <button type="button" class="st-act-btn st-act-btn--trace" @click="openStatusDialog(item)">
                                        <el-icon :size="15"><Clock /></el-icon> Trace
                                    </button>
                                    <button type="button" class="st-act-btn st-act-btn--edit" @click="openEditItem(item)">
                                        <el-icon :size="15"><EditPen /></el-icon> Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="st-act-btn st-act-btn--danger"
                                        :disabled="removingId === item.id"
                                        @click="requestRemove(item)"
                                    >
                                        <el-icon :size="15"><Delete /></el-icon> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="st-no-results st-cards">
                            <el-icon :size="18"><Search /></el-icon>
                            <span v-if="searchQuery">No items match “{{ searchQuery }}”.</span>
                            <span v-else>No items match your filters.</span>
                        </div>

                        <div v-if="filteredItems.length > pageSize" class="st-pagination">
                            <el-pagination
                                v-model:current-page="currentPage"
                                :page-size="pageSize"
                                :total="filteredItems.length"
                                layout="total, prev, pager, next"
                                background
                            />
                        </div>
                    </div>
                    </div>

                    <div v-else class="st-empty">
                        <div class="st-empty__icon"><el-icon :size="22"><Goods /></el-icon></div>
                        <div class="st-empty__title">No items yet</div>
                        <p class="st-empty__text">Add your first item using the button above.</p>
                    </div>
                </template>
            </div>
        </div>

        <AddEditStoreItemDialog v-model="itemDialogOpen" :item="editingItem" />
        <StoreItemStatusDialog v-model="statusDialogOpen" :item="activeItem" :status-options="statusOptions" />

        <ConfirmDialog
            v-model="removeOpen"
            title="Remove Item"
            :message="`Remove ${pendingRemove?.name ?? 'this item'} from your store? This can't be undone.`"
            confirm-text="Remove"
            @confirm="confirmRemove"
        />

        <!-- Admin reject reason -->
        <el-dialog v-model="rejectDialogOpen" width="min(440px, calc(100vw - 2rem))" align-center :close-on-click-modal="false" class="st-reject-modal">
            <template #header>
                <div class="st-reject-modal__title">Reject {{ rejectingStore?.owner_name }}'s Store</div>
            </template>
            <el-input v-model="rejectForm.reason" type="textarea" :rows="3" placeholder="Explain why this store is being rejected (optional)" />
            <template #footer>
                <button type="button" class="st-btn-outline" @click="rejectDialogOpen = false">Cancel</button>
                <button type="button" class="st-btn-primary st-btn-primary--danger" :disabled="rejectForm.processing" @click="submitReject">
                    {{ rejectForm.processing ? 'Rejecting…' : 'Reject Store' }}
                </button>
            </template>
        </el-dialog>
    </StoreLayout>
</template>

<style scoped>
.st-page {
    font-family: var(--dp-font-sans);
    min-height: 100%;
}

/* ── Buttons ───────────────────────────────────────────────────────────── */
.st-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 34px;
    padding: 0 16px;
    border: none;
    border-radius: 999px;
    background: var(--dp-primary);
    color: var(--dp-on-primary);
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.st-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(39, 19, 16, 0.2); }
.st-btn-primary:disabled { opacity: 0.6; cursor: default; transform: none; box-shadow: none; }
.st-btn-primary--danger { background: var(--dp-error); }
.st-btn-primary:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.st-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 34px;
    padding: 0 16px;
    border: 1px solid var(--dp-outline-variant);
    border-radius: 999px;
    background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface);
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.15s ease;
}
.st-btn-outline:hover { background: var(--dp-surface-container-low); }
.st-btn-outline--danger { color: var(--dp-error); border-color: color-mix(in srgb, var(--dp-error) 35%, transparent); }
.st-btn-outline--danger:hover { background: var(--dp-error-container); }
.st-btn-outline:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

/* ── Body ──────────────────────────────────────────────────────────────── */
.st-body { padding: 1.5rem; }
.st-body--no-bottom-pad { padding-bottom: 0; }

/* ── Import results panel ─────────────────────────────────────────────── */
.st-import-panel {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: var(--dp-secondary-container);
    border-radius: var(--dp-card-radius);
    padding: 14px 16px;
}
.st-import-panel--warn { background: #fef3c7; }
.st-import-panel__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    color: var(--dp-on-secondary-container);
    flex-shrink: 0;
}
.st-import-panel--warn .st-import-panel__icon { color: #92400e; }
.st-import-panel__body { flex: 1; min-width: 0; }
.st-import-panel__title { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.st-import-panel__list {
    margin: 8px 0 0;
    padding-left: 18px;
    font-size: 12px;
    color: var(--dp-on-surface-variant);
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.st-import-panel__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    flex-shrink: 0;
}
.st-import-panel__close:hover { background: rgba(0, 0, 0, 0.06); }

/* ── Admin pending panel ───────────────────────────────────────────────── */
.st-pending-card {
    background: #fffbeb;
    border-radius: var(--dp-card-radius);
    overflow: hidden;
}
.st-pending-card__head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 700;
    color: #92400e;
    border-bottom: 1px solid #fde68a;
}
.st-pending-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #f59e0b;
    color: #fff;
    font-size: 11px;
}
.st-pending-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 16px;
    border-bottom: 1px solid #fef3c7;
}
.st-pending-row:last-child { border-bottom: none; }
.st-pending-row__name { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.st-pending-row__meta { font-size: 12px; color: #92400e; margin-top: 1px; }
.st-pending-row__actions { display: flex; gap: 8px; flex-shrink: 0; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.st-empty { text-align: center; padding: 4rem 1rem; }
.st-empty__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
}
.st-empty__title { font-size: 16px; font-weight: 700; color: var(--dp-primary); margin-bottom: 4px; }
.st-empty__text { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0 auto; max-width: 340px; line-height: 1.5; }

/* ── Status banners ────────────────────────────────────────────────────── */
.st-status-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 18px;
    border-radius: var(--dp-card-radius);
    max-width: 560px;
    margin: 0 auto;
    background: var(--dp-surface-container-lowest);
    box-shadow: var(--dp-card-shadow);
}
.st-status-banner--pending { color: var(--dp-primary); }
.st-status-banner--pending .el-icon { color: var(--dp-secondary); }
.st-status-banner--rejected { color: var(--dp-on-error-container); }
.st-status-banner--rejected .el-icon { color: var(--dp-error); }
.st-status-banner__title { font-size: 14px; font-weight: 700; color: var(--dp-on-surface); }
.st-status-banner__text { font-size: 13px; margin: 2px 0 0; line-height: 1.5; color: var(--dp-on-surface-variant); }

/* ── Inventory: editorial intro ───────────────────────────────────────── */
.st-inventory { display: flex; flex-direction: column; gap: 20px; }
.st-hero { display: flex; flex-direction: column; gap: 8px; max-width: 640px; }
.st-hero h1 { color: var(--dp-primary); }
.st-kicker {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: var(--dp-primary);
}
.st-subtitle { font-size: 14px; line-height: 1.6; color: var(--dp-on-surface-variant); margin: 0; }

/* ── Search + filters ────────────────────────────────────────────────── */
.st-controls { display: flex; flex-direction: column; gap: 12px; }
.st-search-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.st-search {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 14px;
    height: 40px;
    width: 260px;
    border-radius: 999px;
    background: var(--dp-surface-container-lowest);
    box-shadow: var(--dp-card-shadow);
    color: var(--dp-on-surface-variant);
    flex-shrink: 0;
}
.st-search :deep(.el-icon) { font-size: 14px; }
.st-search input { border: none; outline: none; background: transparent; font-size: 13px; color: var(--dp-on-surface); width: 100%; font-family: inherit; }
.st-stat { font-size: 12.5px; color: var(--dp-on-surface-variant); margin-left: auto; }
.st-stat strong { color: var(--dp-on-surface); font-weight: 700; }

.st-filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    background: var(--dp-surface-container-lowest);
    border-radius: 14px;
    box-shadow: var(--dp-card-shadow);
    padding: 14px 16px;
}
.st-filter-pill { width: 168px; }
.st-filter-pill :deep(.el-select__wrapper) {
    min-height: 38px !important;
    height: 38px !important;
    border-radius: 10px;
    background: var(--dp-surface-container-low);
    box-shadow: none !important;
    font-size: 13px;
    font-weight: 600;
    color: var(--dp-on-surface-variant);
    padding-left: 14px;
}
.st-filter-pill :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset !important; }
.st-filter-pill :deep(.el-select__placeholder) { color: var(--dp-on-surface-variant); }

.st-reset {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-left: auto;
    padding: 0;
    border: none;
    background: none;
    color: var(--dp-primary);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}
.st-reset:hover { text-decoration: underline; }
.st-reset:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 3px; }

/* Category pill — tone hashed per value, matching Store/Market.vue's
   browse-table treatment for a consistent look across both pages. */
.st-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}
.st-pill--a { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.st-pill--b { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.st-pill--c { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.st-pill--d { background: var(--dp-tertiary-fixed); color: var(--dp-on-tertiary-fixed); }

/* ── Table card ────────────────────────────────────────────────────────── */
.st-table-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    overflow: hidden;
}

.st-table thead th {
    background: var(--dp-surface-container-low);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--dp-on-surface-variant);
    padding: 12px 16px;
    border-bottom-color: transparent;
    white-space: nowrap;
}
.st-table tbody td { padding: 13px 16px; font-size: 13px; border-color: color-mix(in srgb, var(--dp-outline-variant) 25%, transparent); vertical-align: middle; }
.st-row { transition: background 0.12s; cursor: pointer; }
.st-row:hover { background: var(--dp-surface-container-low); }
.st-row:last-child td { border-bottom: none; }

.st-th { display: inline-flex; align-items: center; gap: 5px; }
.st-th :deep(.el-icon) { font-size: 12px; color: var(--dp-outline); }

.st-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
    flex-shrink: 0;
    overflow: hidden;
}
.st-thumb img { width: 100%; height: 100%; object-fit: cover; }
.st-name { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.st-name--link { text-decoration: none; }
.st-name--link:hover { color: var(--dp-primary); text-decoration: underline; }
.st-muted { color: var(--dp-on-surface-variant); }
.st-price { font-size: 13px; font-weight: 700; color: var(--dp-primary); font-family: var(--dp-font-mono); }

.st-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    text-transform: capitalize;
    padding: 4px 11px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
}
.st-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.st-status:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }
.st-status--available { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.st-status--reserved { background: #fef3c7; color: #92400e; }
.st-status--sold { background: #dbeafe; color: #1e40af; }
.st-status--shipped { background: #ede9fe; color: #6d28d9; }
.st-status--delivered { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.st-status--archived { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.st-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; }
.st-act-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: background .15s ease;
}
.st-act-btn--trace { color: #6d28d9; }
.st-act-btn--trace:hover { background: rgba(109, 40, 217, .08); }
.st-act-btn--edit { color: var(--dp-secondary); }
.st-act-btn--edit:hover { background: color-mix(in srgb, var(--dp-secondary) 10%, transparent); }
.st-act-btn--danger { color: var(--dp-error); }
.st-act-btn--danger:hover { background: var(--dp-error-container); }
.st-act-btn:disabled { opacity: 0.5; cursor: default; }
.st-act-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.st-no-results {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 2.5rem 1rem;
    font-size: 13px;
    color: var(--dp-on-surface-variant);
}
.st-no-results :deep(.el-icon) { color: var(--dp-outline); }

/* ── Mobile card list (replaces table below 640px) ────────────────────── */
.st-cards { display: none; }
.st-card {
    padding: 14px 16px;
    border-bottom: 1px solid color-mix(in srgb, var(--dp-outline-variant) 25%, transparent);
}
.st-card:last-child { border-bottom: none; }
.st-card__top { display: flex; align-items: center; gap: 10px; }
.st-card__title { flex: 1; min-width: 0; }
.st-card__body { display: flex; align-items: baseline; justify-content: space-between; margin: 8px 0 10px; padding-left: 42px; }
.st-card__actions { display: flex; align-items: center; gap: 8px; }
.st-card__actions .st-act-btn {
    flex: 1;
    width: auto;
    height: 32px;
    gap: 5px;
    font-size: 12px;
    font-weight: 700;
    background: var(--dp-surface-container-low);
}

.st-pagination { display: flex; justify-content: flex-end; padding: 12px 16px; border-top: 1px solid color-mix(in srgb, var(--dp-outline-variant) 25%, transparent); }
.st-pagination :deep(.el-pagination) { display: flex; align-items: center; gap: 6px; font-family: inherit; }
.st-pagination :deep(.el-pagination__total) { margin-right: auto; font-size: 12px; font-weight: 600; color: var(--dp-on-surface-variant); }
.st-pagination :deep(.btn-prev),
.st-pagination :deep(.btn-next) { width: 30px; height: 30px; border-radius: 8px; background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface-variant); }
.st-pagination :deep(.el-pager li) { min-width: 30px; height: 30px; border-radius: 8px; background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface); font-size: 12px; font-weight: 700; margin: 0 2px; }
.st-pagination :deep(.el-pager li.is-active) { background: var(--dp-primary); border-color: var(--dp-primary); color: var(--dp-on-primary); }

@media (prefers-reduced-motion: reduce) {
    .st-btn-primary,
    .st-row,
    .st-act-btn { transition: none; }
}

@media (max-width: 900px) {
    .st-filter-pill { width: 150px; }
}

@media (max-width: 640px) {
    .st-body { padding: 1.25rem; }
    .table-responsive { display: none; }
    .st-cards { display: block; }
    .st-search { width: 100%; }
    .st-stat { margin-left: 0; }
    .st-filter-pill { width: 100%; }
    .st-filter-bar { flex-direction: column; align-items: stretch; }
    .st-reset { margin-left: 0; justify-content: center; padding: 8px 0 0; }
}
</style>

<style>
/* Dialog teleports to <body>, outside .dp-shell, so --dp-* custom
   properties don't cascade in — literal hex from the same palette is
   used here instead, matching DesignPreviewLayout's own teleported
   popovers/dropdowns. */
.el-dialog.st-reject-modal { border-radius: 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
.st-reject-modal__title { font-size: 16px; font-weight: 800; color: #271310; }
.el-dialog.st-reject-modal .el-dialog__footer { display: flex; justify-content: flex-end; gap: 10px; }
</style>
