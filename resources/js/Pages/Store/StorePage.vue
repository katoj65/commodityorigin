<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import AddEditStoreItemDialog from '@/Components/Modals/AddEditStoreItemDialog.vue';
import StoreItemStatusDialog from '@/Components/Modals/StoreItemStatusDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Check, Close, Clock, Delete, EditPen, Goods, Plus, Search, Shop, UploadFilled, WarningFilled,
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
const currentPage = ref(1);
const pageSize = 30;

const filteredItems = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();

    return props.items.filter((i) => {
        const matchesStatus = statusFilter.value === 'all' || i.status === statusFilter.value;
        const matchesSearch = !q || [i.name, i.category, i.status].filter(Boolean).join(' ').toLowerCase().includes(q);
        return matchesStatus && matchesSearch;
    });
});

const pagedItems = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredItems.value.slice(start, start + pageSize);
});

watch([searchQuery, statusFilter, () => props.items], () => {
    currentPage.value = 1;
});

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
        <template #icon>
            <el-icon :size="16"><Shop /></el-icon>
        </template>

        <div class="st-page">
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
                        <p class="st-status-banner__text">{{ store.rejection_reason || 'No reason was given.' }} Resubmit to request another review.</p>
                    </div>
                </div>

                <!-- Verified: items inventory -->
                <template v-else>
                    <div v-if="items.length" class="st-table-card">
                        <div class="st-toolbar">
                            <div class="st-search">
                                <el-icon><Search /></el-icon>
                                <input v-model="searchQuery" type="text" placeholder="Search items…">
                            </div>
                            <div class="st-toolbar__stats">
                                <span class="st-stat"><strong>{{ filteredItems.length }}</strong> of {{ items.length }}</span>
                            </div>
                        </div>

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
                                        <td class="st-muted">{{ item.category || '—' }}</td>
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
                                <span v-else>No items match the selected status.</span>
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
                            <span v-else>No items match the selected status.</span>
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
    --green: #145c42;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Buttons ───────────────────────────────────────────────────────────── */
.st-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 32px;
    padding: 0 13px;
    border: none;
    border-radius: 7px;
    background: linear-gradient(135deg, #145c42, #0d3d2c);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.st-btn-primary:hover { opacity: 0.9; }
.st-btn-primary:disabled { opacity: 0.6; cursor: default; }
.st-btn-primary--danger { background: linear-gradient(135deg, #b91c1c, #7f1d1d); }

.st-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 32px;
    padding: 0 13px;
    border: 1px solid var(--border);
    border-radius: 7px;
    background: #fff;
    color: var(--on-surface);
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.15s ease;
}
.st-btn-outline:hover { background: var(--surface-low); }
.st-btn-outline--danger { color: #b91c1c; border-color: #fecaca; }
.st-btn-outline--danger:hover { background: #fef2f2; }

/* ── Body ──────────────────────────────────────────────────────────────── */
.st-body { padding: 1.5rem; }
.st-body--no-bottom-pad { padding-bottom: 0; }

/* ── Import results panel ─────────────────────────────────────────────── */
.st-import-panel {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 14px 16px;
}
.st-import-panel--warn { background: #fffbeb; border-color: #fde68a; }
.st-import-panel__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.7);
    color: #166534;
    flex-shrink: 0;
}
.st-import-panel--warn .st-import-panel__icon { color: #92400e; }
.st-import-panel__body { flex: 1; min-width: 0; }
.st-import-panel__title { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.st-import-panel__list {
    margin: 8px 0 0;
    padding-left: 18px;
    font-size: 0.75rem;
    color: var(--on-surface-var);
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
    color: var(--on-surface-var);
    cursor: pointer;
    flex-shrink: 0;
}
.st-import-panel__close:hover { background: rgba(0, 0, 0, 0.06); }

/* ── Admin pending panel ───────────────────────────────────────────────── */
.st-pending-card {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 14px;
    overflow: hidden;
}
.st-pending-card__head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    font-size: 0.8125rem;
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
    font-size: 0.6875rem;
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
.st-pending-row__name { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.st-pending-row__meta { font-size: 0.75rem; color: #92400e; margin-top: 1px; }
.st-pending-row__actions { display: flex; gap: 8px; flex-shrink: 0; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.st-empty { text-align: center; padding: 4rem 1rem; }
.st-empty__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid var(--border);
    color: var(--on-surface-var);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
}
.st-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.st-empty__text { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0 auto; max-width: 340px; line-height: 1.5; }

/* ── Status banners ────────────────────────────────────────────────────── */
.st-status-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border-radius: 12px;
    max-width: 560px;
    margin: 0 auto;
}
.st-status-banner--pending { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.st-status-banner--rejected { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
.st-status-banner__title { font-size: 0.875rem; font-weight: 700; }
.st-status-banner__text { font-size: 0.8125rem; margin: 2px 0 0; line-height: 1.5; opacity: 0.9; }

/* ── Table card ────────────────────────────────────────────────────────── */
.st-table-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

.st-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}
.st-search {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
    height: 36px;
    width: 240px;
    border: 1px solid var(--border);
    border-radius: 9px;
    background: var(--surface-low);
    color: var(--on-surface-var);
    flex-shrink: 0;
}
.st-search :deep(.el-icon) { font-size: 14px; }
.st-search input { border: none; outline: none; background: transparent; font-size: 0.8125rem; color: var(--on-surface); width: 100%; font-family: inherit; }
.st-toolbar__stats { display: flex; align-items: center; gap: 10px; }
.st-stat { font-size: 0.75rem; color: var(--on-surface-var); }
.st-stat strong { color: var(--on-surface); font-weight: 700; }

.st-table thead th {
    background: var(--surface-low);
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
    padding: 11px 16px;
    border-bottom-color: var(--border);
    white-space: nowrap;
}
.st-table tbody td { padding: 13px 16px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.st-row { transition: background 0.12s; cursor: pointer; }
.st-row:hover { background: var(--surface-low); }
.st-row:not(:last-child) td { border-bottom: 1px solid var(--surface-low); }

.st-th { display: inline-flex; align-items: center; gap: 5px; }
.st-th :deep(.el-icon) { font-size: 12px; color: #9ca3af; }

.st-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(20, 92, 66, 0.08);
    color: var(--green);
    flex-shrink: 0;
    overflow: hidden;
}
.st-thumb img { width: 100%; height: 100%; object-fit: cover; }
.st-name { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.st-name--link { text-decoration: none; }
.st-name--link:hover { color: var(--green); text-decoration: underline; }
.st-muted { color: var(--on-surface-var); }
.st-price { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }

.st-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: capitalize;
    padding: 3px 10px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
}
.st-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.st-status--available { background: #dcfce7; color: #166534; }
.st-status--reserved { background: #fef3c7; color: #92400e; }
.st-status--sold { background: #dbeafe; color: #1e40af; }
.st-status--shipped { background: #ede9fe; color: #6d28d9; }
.st-status--delivered { background: #dcfce7; color: #166534; }
.st-status--archived { background: #f1f5f9; color: #64748b; }

.st-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; }
.st-act-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 7px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: background .15s ease;
}
.st-act-btn--trace { color: #6d28d9; }
.st-act-btn--trace:hover { background: rgba(109, 40, 217, .08); }
.st-act-btn--edit { color: var(--green); }
.st-act-btn--edit:hover { background: rgba(20, 92, 66, .08); }
.st-act-btn--danger { color: #b91c1c; }
.st-act-btn--danger:hover { background: rgba(185, 28, 28, .08); }
.st-act-btn:disabled { opacity: 0.5; cursor: default; }

.st-no-results {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 2.5rem 1rem;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
}
.st-no-results :deep(.el-icon) { color: #cbd5e1; }

/* ── Mobile card list (replaces table below 640px) ────────────────────── */
.st-cards { display: none; }
.st-card {
    padding: 14px 16px;
    border-bottom: 1px solid var(--surface-low);
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
    font-size: 0.75rem;
    font-weight: 700;
    background: var(--surface-low);
}

.st-pagination { display: flex; justify-content: flex-end; padding: 12px 16px; border-top: 1px solid var(--border); }
.st-pagination :deep(.el-pagination) { display: flex; align-items: center; gap: 6px; font-family: inherit; }
.st-pagination :deep(.el-pagination__total) { margin-right: auto; font-size: 0.75rem; font-weight: 600; color: var(--on-surface-var); }
.st-pagination :deep(.btn-prev),
.st-pagination :deep(.btn-next) { width: 30px; height: 30px; border-radius: 7px; background: #fff; border: 1px solid var(--border); color: var(--on-surface-var); }
.st-pagination :deep(.el-pager li) { min-width: 30px; height: 30px; border-radius: 7px; background: #fff; border: 1px solid var(--border); color: var(--on-surface); font-size: 0.75rem; font-weight: 700; margin: 0 2px; }
.st-pagination :deep(.el-pager li.is-active) { background: var(--green); border-color: var(--green); color: #fff; }

@media (max-width: 640px) {
    .st-body { padding: 1.25rem; }
    .table-responsive { display: none; }
    .st-cards { display: block; }
    .st-toolbar { padding: 12px 14px; }
    .st-search { width: 100%; }
}
</style>

<style>
.el-dialog.st-reject-modal { border-radius: 16px; font-family: 'Manrope', system-ui, sans-serif; }
.st-reject-modal__title { font-size: 1rem; font-weight: 800; color: #111827; }
.el-dialog.st-reject-modal .el-dialog__footer { display: flex; justify-content: flex-end; gap: 10px; }
</style>
