<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import RequestStoreDialog from '@/Components/Modals/RequestStoreDialog.vue';
import AddEditStoreItemDialog from '@/Components/Modals/AddEditStoreItemDialog.vue';
import { Compass, EditPen, Plus, Shop, UploadFilled } from '@element-plus/icons-vue';

const props = defineProps({
    title: { type: String, default: 'Store' },
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
});

/**
 * These three pieces of state are shown in the header but consumed by
 * whichever page is rendered inside the layout (e.g. the items table's
 * filter, or the import-results panel's dismiss button). A page that
 * cares binds them with v-model:*; a page that doesn't (like Market,
 * which has no table) just leaves the layout to manage its own default.
 */
const statusFilter = defineModel('statusFilter', { default: 'all' });
const storeDialogOpen = defineModel('storeDialogOpen', { default: false });
const importResultVisible = defineModel('importResultVisible', { default: false });
if (props.importResult) importResultVisible.value = true;

/**
 * The store is composed of several sections that all share this layout —
 * add a new store-related page here and it appears as a button in the
 * header's action row everywhere the layout is used.
 */
const sections = [
    { label: 'Inventory', icon: Shop, href: route('store.show'), match: 'store.show' },
    { label: 'Market', icon: Compass, href: route('store.market'), match: 'store.market' },
];

function statusLabel(status) {
    if (!status) return '—';
    return status.charAt(0).toUpperCase() + status.slice(1);
}

/* ── Add item (header button — always creates new; row-level edit stays
   local to whichever page renders the items table) ─────────────────── */
const itemDialogOpen = ref(false);

/* ── Import from Excel ────────────────────────────────────────────────── */
const fileInput = ref(null);
const importing = ref(false);

function openFilePicker() {
    fileInput.value?.click();
}

function handleFileChange(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    importing.value = true;

    router.post(route('store.items.import'), { file }, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            importResultVisible.value = true;
            const imported = props.importResult?.imported ?? 0;
            const skipped = props.importResult?.errors?.length ?? 0;
            ElNotification({
                title: imported > 0 ? 'Import Complete' : 'Import Failed',
                message: imported > 0
                    ? `${imported} item${imported === 1 ? '' : 's'} imported${skipped ? `, ${skipped} row(s) skipped.` : '.'}`
                    : 'No rows were imported. See the details below the table.',
                type: imported > 0 ? (skipped ? 'warning' : 'success') : 'error',
                duration: 4000,
                offset: 84,
            });
        },
        onError: (errors) => {
            ElNotification({
                title: 'Import Failed',
                message: errors.file || 'Please check the file and try again.',
                type: 'error',
                duration: 4000,
                offset: 84,
            });
        },
        onFinish: () => {
            importing.value = false;
            event.target.value = '';
        },
    });
}

const storeVerified = computed(() => props.store?.verification_status === 'verified');
</script>

<template>
    <AppLayout :title="title" full-width flush :show-banner="false">
        <Head :title="title" />

        <div class="stl-page">
            <PageHeader :title="title">
                <template v-if="$slots.icon" #icon>
                    <slot name="icon" />
                </template>
                <template #actions>
                    <nav class="stl-tabs">
                        <Link
                            v-for="section in sections"
                            :key="section.label"
                            :href="section.href"
                            class="stl-tab"
                            :class="{ 'stl-tab--active': route().current(section.match) }"
                        >
                            <el-icon :size="14"><component :is="section.icon" /></el-icon>
                            {{ section.label }}
                        </Link>
                    </nav>

                    <button v-if="store?.verification_status === 'rejected'" type="button" class="stl-btn" @click="storeDialogOpen = true">
                        <el-icon><EditPen /></el-icon> Resubmit
                    </button>
                    <button v-if="!store" type="button" class="stl-btn" @click="storeDialogOpen = true">
                        <el-icon><Plus /></el-icon> Request Store
                    </button>

                    <el-select v-if="storeVerified" v-model="statusFilter" class="stl-status-filter" placeholder="Status">
                        <el-option label="All Statuses" value="all" />
                        <el-option v-for="opt in statusOptions" :key="opt" :label="statusLabel(opt)" :value="opt" />
                    </el-select>
                    <input v-if="storeVerified" ref="fileInput" type="file" accept=".xlsx,.xls" class="d-none" @change="handleFileChange">
                    <button v-if="storeVerified" type="button" class="stl-btn" :disabled="importing" @click="openFilePicker">
                        <el-icon><UploadFilled /></el-icon> {{ importing ? 'Importing…' : 'Import Excel' }}
                    </button>
                    <button v-if="storeVerified" type="button" class="stl-btn" @click="itemDialogOpen = true">
                        <el-icon><Plus /></el-icon> Add Item
                    </button>
                </template>
            </PageHeader>

            <slot />
        </div>

        <RequestStoreDialog v-model="storeDialogOpen" :rejected="store?.verification_status === 'rejected'" />
        <AddEditStoreItemDialog v-model="itemDialogOpen" :item="null" />
    </AppLayout>
</template>

<style scoped>
.stl-page {
    /* Shared with every store-related page's own scoped styles — defined
       here (rather than only on each page's own wrapper) because content
       rendered into the header's #actions slot lives inside PageHeader,
       a sibling of the page body, not a descendant of it. */
    --green: #145c42;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    min-height: 100%;
}

/* ── Section tabs — matches the real Market page's own tab styling
   (resources/js/Pages/Market/MarketPage.vue: .mkt-tabs/.mkt-tab) so the
   two headers read as one consistent design language. ─────────────── */
.stl-tabs { display: flex; align-items: center; gap: 2px; }
.stl-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface-var);
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s ease, color 0.15s ease;
}
.stl-tab :deep(.el-icon) { font-size: 14px; }
.stl-tab:hover { background: var(--surface-low); color: var(--on-surface); }
.stl-tab--active { background: rgba(0, 69, 50, 0.08); color: var(--green); }

/* ── Action buttons — kept as plain, low-contrast controls (not bold
   CTAs) to match that same restrained header language. ─────────────── */
.stl-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 32px;
    padding: 0 13px;
    border: 1px solid var(--border);
    border-radius: 7px;
    background: #fff;
    color: var(--on-surface-var);
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.stl-btn:hover { background: var(--surface-low); color: var(--on-surface); }
.stl-btn:disabled { opacity: 0.6; cursor: default; }

.stl-status-filter { width: 150px; }
.stl-status-filter :deep(.el-select__wrapper) {
    min-height: 32px !important;
    height: 32px !important;
    border-radius: 7px;
    box-shadow: 0 0 0 1px var(--border) inset;
    font-size: 0.75rem;
    font-weight: 700;
}
.stl-status-filter :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1px var(--green) inset; }
</style>
