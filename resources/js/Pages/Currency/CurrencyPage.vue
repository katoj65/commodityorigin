<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Plus, Close, Coin, Edit, Delete, Search, CircleCheck, CircleClose,
} from '@element-plus/icons-vue';

const props = defineProps({
    currencies: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
});

const searchQuery = ref('');

const sortedCurrencies = computed(() => [...props.currencies].sort((a, b) => a.sort_order - b.sort_order || a.code.localeCompare(b.code)));

const filteredCurrencies = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return sortedCurrencies.value;

    return sortedCurrencies.value.filter((c) => `${c.code} ${c.name} ${c.symbol}`.toLowerCase().includes(q));
});

const activeCount = computed(() => props.currencies.filter((c) => c.is_active).length);

/* ── Create / edit dialog ────────────────────────────────────────────── */
const dialogOpen = ref(false);
const dialogMode = ref('create');
const editingCurrency = ref(null);

const form = useForm({
    code: '',
    name: '',
    symbol: '',
    sort_order: 0,
    is_active: true,
});

function openCreateDialog() {
    dialogMode.value = 'create';
    editingCurrency.value = null;
    form.reset();
    form.clearErrors();
    form.sort_order = props.currencies.length + 1;
    dialogOpen.value = true;
}

function openEditDialog(currency) {
    dialogMode.value = 'edit';
    editingCurrency.value = currency;
    form.clearErrors();
    form.code = currency.code;
    form.name = currency.name;
    form.symbol = currency.symbol;
    form.sort_order = currency.sort_order;
    form.is_active = currency.is_active;
    dialogOpen.value = true;
}

function submitDialog() {
    form.code = form.code.toUpperCase();

    if (dialogMode.value === 'create') {
        form.post(route('currencies.store'), {
            preserveScroll: true,
            onSuccess: () => { dialogOpen.value = false; },
        });
    } else {
        form.patch(route('currencies.update', editingCurrency.value.id), {
            preserveScroll: true,
            onSuccess: () => { dialogOpen.value = false; },
        });
    }
}

/* ── Quick active/inactive toggle ────────────────────────────────────── */
function toggleActive(currency) {
    router.patch(route('currencies.update', currency.id), {
        code: currency.code,
        name: currency.name,
        symbol: currency.symbol,
        sort_order: currency.sort_order,
        is_active: !currency.is_active,
    }, { preserveScroll: true });
}

/* ── Delete ───────────────────────────────────────────────────────────── */
const deleteOpen = ref(false);
const pendingDelete = ref(null);

function requestDelete(currency) {
    pendingDelete.value = currency;
    deleteOpen.value = true;
}

function confirmDelete() {
    if (!pendingDelete.value) return;
    router.delete(route('currencies.destroy', pendingDelete.value.id), { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Currencies" full-width flush :show-banner="false">
        <Head title="Currencies" />

        <div class="cur-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <div class="cur-page-header">
                <div class="cur-page-header__left">
                    <div class="cur-kicker">Settlement · Bean Origin</div>
                    <h1 class="cur-title">Currencies</h1>
                    <p class="cur-subtitle">Every currency traders can settle in. {{ activeCount }} of {{ currencies.length }} are active right now.</p>
                </div>
                <div v-if="canManage" class="cur-page-header__actions">
                    <button type="button" class="cur-btn-primary" @click="openCreateDialog">
                        <el-icon><Plus /></el-icon> Add Currency
                    </button>
                </div>
            </div>

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div class="cur-body">
                <div class="cur-card">
                    <div class="cur-toolbar">
                        <div class="cur-search">
                            <el-icon><Search /></el-icon>
                            <input v-model="searchQuery" type="text" placeholder="Search by code or name…">
                        </div>
                        <span class="cur-count">{{ filteredCurrencies.length }} currenc{{ filteredCurrencies.length === 1 ? 'y' : 'ies' }}</span>
                    </div>

                    <div v-if="filteredCurrencies.length" class="cur-table-wrap">
                        <table class="cur-table">
                            <thead>
                                <tr>
                                    <th>Currency</th>
                                    <th>Symbol</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th v-if="canManage" class="cur-th-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="currency in filteredCurrencies" :key="currency.id">
                                    <td>
                                        <div class="cur-identity">
                                            <span class="cur-chip">{{ currency.code }}</span>
                                            <span class="cur-identity__name">{{ currency.name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="cur-symbol">{{ currency.symbol }}</span></td>
                                    <td><span class="cur-sort">{{ currency.sort_order }}</span></td>
                                    <td>
                                        <button
                                            v-if="canManage"
                                            type="button"
                                            class="cur-status cur-status--btn"
                                            :class="currency.is_active ? 'is-active' : 'is-inactive'"
                                            @click="toggleActive(currency)"
                                        >
                                            <el-icon :size="12"><component :is="currency.is_active ? CircleCheck : CircleClose" /></el-icon>
                                            {{ currency.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                        <span v-else class="cur-status" :class="currency.is_active ? 'is-active' : 'is-inactive'">
                                            <el-icon :size="12"><component :is="currency.is_active ? CircleCheck : CircleClose" /></el-icon>
                                            {{ currency.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td v-if="canManage" class="cur-td-actions">
                                        <div class="cur-row-actions">
                                            <button type="button" class="cur-icon-btn" aria-label="Edit currency" @click="openEditDialog(currency)">
                                                <el-icon :size="14"><Edit /></el-icon>
                                            </button>
                                            <button type="button" class="cur-icon-btn cur-icon-btn--danger" aria-label="Delete currency" @click="requestDelete(currency)">
                                                <el-icon :size="14"><Delete /></el-icon>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="cur-empty">
                        <div class="cur-empty__icon"><el-icon :size="24"><Coin /></el-icon></div>
                        <div class="cur-empty__title">No currencies found</div>
                        <p class="cur-empty__text">
                            {{ searchQuery ? 'Try a different search term.' : 'Add the first currency to get started.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Create / edit modal ──────────────────────────────────────── -->
        <el-dialog
            v-model="dialogOpen"
            width="420px"
            destroy-on-close
            align-center
            :show-close="false"
            class="cur-modal"
        >
            <template #header>
                <div class="cur-modal__head">
                    <div class="cur-modal__head-icon">
                        <el-icon :size="18"><Coin /></el-icon>
                    </div>
                    <div class="cur-modal__head-text">
                        <div class="cur-modal__eyebrow">Currencies</div>
                        <div class="cur-modal__title">{{ dialogMode === 'create' ? 'Add Currency' : 'Edit Currency' }}</div>
                    </div>
                    <button type="button" class="cur-modal__close" aria-label="Close" @click="dialogOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="cur-modal__body">
                <div class="cur-field-row">
                    <div class="cur-field cur-field--code">
                        <label class="cur-field__label">Code</label>
                        <el-input
                            v-model="form.code"
                            placeholder="USD"
                            maxlength="3"
                            class="cur-input"
                            :class="{ 'cur-input--error': form.errors.code }"
                            style="text-transform: uppercase;"
                        />
                        <span v-if="form.errors.code" class="cur-field__error">{{ form.errors.code }}</span>
                    </div>
                    <div class="cur-field cur-field--symbol">
                        <label class="cur-field__label">Symbol</label>
                        <el-input v-model="form.symbol" placeholder="$" maxlength="8" class="cur-input" :class="{ 'cur-input--error': form.errors.symbol }" />
                        <span v-if="form.errors.symbol" class="cur-field__error">{{ form.errors.symbol }}</span>
                    </div>
                </div>

                <div class="cur-field">
                    <label class="cur-field__label">Name</label>
                    <el-input v-model="form.name" placeholder="e.g. US Dollar" class="cur-input" :class="{ 'cur-input--error': form.errors.name }" />
                    <span v-if="form.errors.name" class="cur-field__error">{{ form.errors.name }}</span>
                </div>

                <div class="cur-field-row">
                    <div class="cur-field">
                        <label class="cur-field__label">Sort Order</label>
                        <el-input v-model.number="form.sort_order" type="number" :min="0" class="cur-input" />
                    </div>
                    <div class="cur-field cur-field--switch">
                        <label class="cur-field__label">Active</label>
                        <el-switch v-model="form.is_active" style="--el-switch-on-color: #004532;" />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="cur-modal__footer">
                    <button type="button" class="cur-btn-outline" @click="dialogOpen = false">Cancel</button>
                    <button type="button" class="cur-btn-primary" :disabled="form.processing" @click="submitDialog">
                        {{ form.processing ? 'Saving…' : (dialogMode === 'create' ? 'Add Currency' : 'Save Changes') }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="deleteOpen"
            title="Delete Currency"
            :message="`${pendingDelete?.code ?? 'This currency'} will be permanently removed. Any user set to it will revert to no preferred currency.`"
            confirm-text="Delete"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>

<style scoped>
.cur-page {
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
.cur-page-header {
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

.cur-page-header__left { max-width: 560px; }

.cur-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.cur-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.cur-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

.cur-page-header__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 4px;
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.cur-btn-primary {
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

.cur-btn-primary:hover { opacity: 0.9; }
.cur-btn-primary:disabled { opacity: 0.6; cursor: default; }

.cur-btn-outline {
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

.cur-btn-outline:hover { background: #f8fafc; }

/* ── Body / card ─────────────────────────────────────────────────────── */
.cur-body { padding: 1.5rem 1.5rem 3rem; }

.cur-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

.cur-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
}

.cur-search {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1 1 auto;
    max-width: 320px;
    padding: 0 12px;
    height: 36px;
    border: 1px solid var(--border);
    border-radius: 9px;
    background: var(--surface-low);
    color: var(--on-surface-var);
}

.cur-search input {
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.8125rem;
    color: var(--on-surface);
    width: 100%;
    font-family: inherit;
}

.cur-count {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--on-surface-var);
    white-space: nowrap;
}

/* ── Table ───────────────────────────────────────────────────────────── */
.cur-table-wrap { overflow-x: auto; }

.cur-table {
    width: 100%;
    min-width: 620px;
    border-collapse: collapse;
}

.cur-table th {
    text-align: left;
    padding: 10px 20px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--on-surface-var);
    background: var(--surface-low);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

.cur-th-actions { text-align: right; }

.cur-table td {
    padding: 12px 20px;
    font-size: 0.8125rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.cur-table tbody tr:last-child td { border-bottom: none; }
.cur-table tbody tr:hover { background: var(--surface-low); }

.cur-identity { display: flex; align-items: center; gap: 10px; }

.cur-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    padding: 3px 8px;
    border-radius: 7px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
}

.cur-identity__name { font-weight: 600; color: var(--on-surface); }

.cur-symbol {
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--on-surface);
}

.cur-sort {
    font-variant-numeric: tabular-nums;
    color: var(--on-surface-var);
}

.cur-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.6875rem;
    font-weight: 700;
    border: none;
}

.cur-status--btn { cursor: pointer; transition: opacity 0.12s ease; }
.cur-status--btn:hover { opacity: 0.8; }

.cur-status.is-active { background: #dcfce7; color: #166534; }
.cur-status.is-inactive { background: #f3f4f6; color: #6b7280; }

.cur-td-actions { text-align: right; }

.cur-row-actions {
    display: inline-flex;
    gap: 4px;
}

.cur-icon-btn {
    width: 28px;
    height: 28px;
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

.cur-icon-btn:hover { background: #e5e7eb; color: var(--on-surface); }
.cur-icon-btn--danger:hover { background: #fee2e2; color: #dc2626; }

/* ── Empty state ─────────────────────────────────────────────────────── */
.cur-empty { text-align: center; padding: 4rem 1rem; }

.cur-empty__icon {
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

.cur-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.cur-empty__text { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0 auto; max-width: 320px; line-height: 1.5; }

/* ── Modal ────────────────────────────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .cur-page's
   DOM subtree, so CSS custom properties defined on .cur-page do NOT
   cascade in. All colors below are literal hex values on purpose. */
:deep(.el-dialog.cur-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.cur-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.cur-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.cur-modal .el-dialog__footer) { padding: 0; }

.cur-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.cur-modal__head-icon {
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

.cur-modal__head-text { flex: 1; min-width: 0; }

.cur-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.cur-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.cur-modal__close {
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

.cur-modal__close:hover { background: #e5e7eb; color: #111827; }

.cur-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.cur-field-row { display: flex; gap: 14px; }
.cur-field-row .cur-field { flex: 1; min-width: 0; }
.cur-field--code { flex: 0 0 100px; }
.cur-field--switch { display: flex; flex-direction: column; justify-content: center; }
.cur-field--switch .cur-field__label { margin-bottom: 10px; }

.cur-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.cur-field__label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #374151;
}

.cur-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.cur-input--error :deep(.el-input__wrapper) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.cur-input :deep(.el-input__wrapper) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.cur-input :deep(.el-input__wrapper:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.cur-input :deep(.el-input__wrapper.is-focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.cur-modal__footer {
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
    .cur-page-header { margin: 1.25rem 1.25rem 0; padding: 1.25rem; border-radius: 12px; }
    .cur-body { padding: 1.25rem 1.25rem 3rem; }
    .cur-toolbar { flex-direction: column; align-items: stretch; }
    .cur-search { max-width: none; }
}
</style>
