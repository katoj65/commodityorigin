<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AddBusinessMemberDialog from '@/Components/Modals/AddBusinessMemberDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Close, Delete, EditPen, Postcard, Location, Message, OfficeBuilding, Phone, Plus, Search, UploadFilled, UserFilled, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    business: { type: Object, required: true },
    members: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
    importResult: { type: Object, default: null },
});

/* ── Search ────────────────────────────────────────────────────────────── */
const searchQuery = ref('');

const filteredMembers = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.members;

    return props.members.filter((m) => [
        m.name, m.designation, m.position, m.telephone, m.email, m.id_number,
    ].filter(Boolean).join(' ').toLowerCase().includes(q));
});

const activeCount = computed(() => props.members.filter((m) => m.status === 'active').length);

/* ── Pagination ────────────────────────────────────────────────────────── */
const currentPage = ref(1);
const pageSize = 30;

const pagedMembers = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredMembers.value.slice(start, start + pageSize);
});

watch([searchQuery, () => props.members], () => {
    currentPage.value = 1;
});

const avatarPalette = [
    ['#d1fae5', '#065f46'], ['#dbeafe', '#1e40af'], ['#fef3c7', '#92400e'],
    ['#fce7f3', '#9d174d'], ['#e0e7ff', '#3730a3'], ['#fee2e2', '#991b1b'],
];

function initials(name) {
    return String(name || '?').split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]?.toUpperCase()).join('') || '?';
}

function avatarColors(id) {
    const [bg, fg] = avatarPalette[id % avatarPalette.length];
    return { background: bg, color: fg };
}

/* ── Add / edit member ────────────────────────────────────────────────── */
const memberDialogOpen = ref(false);
const editingMember = ref(null);

function openAddMember() {
    editingMember.value = null;
    memberDialogOpen.value = true;
}

function openEditMember(member) {
    editingMember.value = member;
    memberDialogOpen.value = true;
}

/* ── Import from Excel ────────────────────────────────────────────────── */
const fileInput = ref(null);
const importing = ref(false);
const importResultVisible = ref(Boolean(props.importResult));

function openFilePicker() {
    fileInput.value?.click();
}

function handleFileChange(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    importing.value = true;

    router.post(route('business.members.import'), { file }, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            importResultVisible.value = true;
            const imported = props.importResult?.imported ?? 0;
            const skipped = props.importResult?.errors?.length ?? 0;
            ElNotification({
                title: imported > 0 ? 'Import Complete' : 'Import Failed',
                message: imported > 0
                    ? `${imported} member${imported === 1 ? '' : 's'} imported${skipped ? `, ${skipped} row(s) skipped.` : '.'}`
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

/* ── Remove member ────────────────────────────────────────────────────── */
const removingId = ref(null);
const removeOpen = ref(false);
const pendingRemove = ref(null);

function requestRemove(member) {
    pendingRemove.value = member;
    removeOpen.value = true;
}

function confirmRemove() {
    const member = pendingRemove.value;
    if (!member) return;

    removingId.value = member.id;
    router.delete(route('business.members.destroy', member.id), {
        preserveScroll: true,
        onFinish: () => { removingId.value = null; },
    });
}
</script>

<template>
    <AppLayout title="Business Members" full-width flush :show-banner="false">
        <Head title="Business Members" />

        <div class="bm-page">
            <!-- ── Page header ───────────────────────────────────────────── -->
            <PageHeader
                :title="business.business_name"
                :subtitle="`${members.length} registered member${members.length === 1 ? '' : 's'}`"
            >
                <template #icon>
                    <img v-if="business.logo_url" :src="business.logo_url" :alt="business.business_name">
                    <el-icon v-else :size="16"><OfficeBuilding /></el-icon>
                </template>
                <template v-if="canManage" #actions>
                    <input ref="fileInput" type="file" accept=".xlsx,.xls" class="d-none" @change="handleFileChange">
                    <button type="button" class="bm-btn-outline" :disabled="importing" @click="openFilePicker">
                        <el-icon><UploadFilled /></el-icon> {{ importing ? 'Importing…' : 'Import Excel' }}
                    </button>
                    <button type="button" class="bm-btn-primary" @click="openAddMember">
                        <el-icon><Plus /></el-icon> Add Member
                    </button>
                </template>
            </PageHeader>

            <!-- ── Import results ───────────────────────────────────────── -->
            <div v-if="importResult && importResultVisible" class="bm-body bm-body--no-top-pad">
                <div class="bm-import-panel" :class="{ 'bm-import-panel--warn': importResult.errors.length }">
                    <div class="bm-import-panel__icon">
                        <el-icon :size="16"><WarningFilled v-if="importResult.errors.length" /><UploadFilled v-else /></el-icon>
                    </div>
                    <div class="bm-import-panel__body">
                        <div class="bm-import-panel__title">
                            {{ importResult.imported }} member{{ importResult.imported === 1 ? '' : 's' }} imported
                            <span v-if="importResult.errors.length">, {{ importResult.errors.length }} row{{ importResult.errors.length === 1 ? '' : 's' }} skipped</span>
                        </div>
                        <ul v-if="importResult.errors.length" class="bm-import-panel__list">
                            <li v-for="err in importResult.errors" :key="err.row">
                                Row {{ err.row }}: {{ err.errors.join(' ') }}
                            </li>
                        </ul>
                    </div>
                    <button type="button" class="bm-import-panel__close" aria-label="Dismiss" @click="importResultVisible = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </div>

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div class="bm-body">
                <div v-if="members.length" class="bm-table-card">
                    <div class="bm-toolbar">
                        <div class="bm-search">
                            <el-icon><Search /></el-icon>
                            <input v-model="searchQuery" type="text" placeholder="Search members…">
                        </div>
                        <div class="bm-toolbar__stats">
                            <span class="bm-stat"><strong>{{ filteredMembers.length }}</strong> of {{ members.length }}</span>
                            <span class="bm-stat-dot"></span>
                            <span class="bm-stat"><strong>{{ activeCount }}</strong> active</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 bm-table">
                            <thead>
                                <tr>
                                    <th><span class="bm-th"><el-icon><UserFilled /></el-icon> Member</span></th>
                                    <th><span class="bm-th"><el-icon><Postcard /></el-icon> Role</span></th>
                                    <th><span class="bm-th"><el-icon><Phone /></el-icon> Contact</span></th>
                                    <th>Status</th>
                                    <th v-if="canManage" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="member in pagedMembers" :key="member.id" class="bm-row">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bm-avatar" :style="avatarColors(member.id)">{{ initials(member.name) }}</span>
                                            <div>
                                                <div class="bm-name">{{ member.name }}</div>
                                                <div class="bm-id-chip">{{ member.id_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="bm-role">
                                            <span class="bm-role-chip">{{ member.designation }}</span>
                                            <span class="bm-role__position">{{ member.position }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="bm-contact">
                                            <span class="bm-contact__row"><el-icon :size="11"><Phone /></el-icon>{{ member.telephone }}</span>
                                            <span v-if="member.email" class="bm-contact__row"><el-icon :size="11"><Message /></el-icon>{{ member.email }}</span>
                                            <span v-if="member.address" class="bm-contact__row"><el-icon :size="11"><Location /></el-icon>{{ member.address }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="bm-status" :class="`bm-status--${member.status}`">{{ member.status }}</span>
                                    </td>
                                    <td v-if="canManage" class="text-end">
                                        <div class="bm-row-actions">
                                            <el-tooltip content="Edit member" placement="top">
                                                <button type="button" class="bm-act-btn bm-act-btn--edit" @click="openEditMember(member)">
                                                    <el-icon :size="15"><EditPen /></el-icon>
                                                </button>
                                            </el-tooltip>
                                            <el-tooltip content="Remove member" placement="top">
                                                <button
                                                    type="button"
                                                    class="bm-act-btn bm-act-btn--danger"
                                                    :disabled="removingId === member.id"
                                                    @click="requestRemove(member)"
                                                >
                                                    <el-icon :size="15"><Delete /></el-icon>
                                                </button>
                                            </el-tooltip>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="!filteredMembers.length" class="bm-no-results">
                            <el-icon :size="18"><Search /></el-icon>
                            No members match “{{ searchQuery }}”.
                        </div>
                    </div>

                    <div v-if="filteredMembers.length > pageSize" class="bm-pagination">
                        <el-pagination
                            v-model:current-page="currentPage"
                            :page-size="pageSize"
                            :total="filteredMembers.length"
                            layout="total, prev, pager, next"
                            background
                        />
                    </div>
                </div>

                <div v-else class="bm-empty">
                    <div class="bm-empty__icon"><el-icon :size="22"><UserFilled /></el-icon></div>
                    <div class="bm-empty__title">No members registered yet</div>
                    <p class="bm-empty__text">
                        {{ canManage ? 'Register your first member using the button above.' : 'This business has not registered any members yet.' }}
                    </p>
                </div>
            </div>
        </div>

        <AddBusinessMemberDialog v-model="memberDialogOpen" :member="editingMember" />

        <ConfirmDialog
            v-model="removeOpen"
            title="Remove Member"
            :message="`Remove ${pendingRemove?.name ?? 'this member'} from ${business.business_name}? This can't be undone.`"
            confirm-text="Remove"
            @confirm="confirmRemove"
        />
    </AppLayout>
</template>

<style scoped>
.bm-page {
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

.bm-btn-primary {
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
.bm-btn-primary:hover { opacity: 0.9; }

.bm-btn-outline {
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
    cursor: pointer;
    transition: background 0.15s ease;
}
.bm-btn-outline:hover { background: var(--surface-low); }
.bm-btn-outline:disabled { opacity: 0.6; cursor: default; }

/* ── Import results panel ─────────────────────────────────────────────── */
.bm-body--no-top-pad { padding-top: 0; }
.bm-import-panel {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 14px 16px;
}
.bm-import-panel--warn { background: #fffbeb; border-color: #fde68a; }
.bm-import-panel__icon {
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
.bm-import-panel--warn .bm-import-panel__icon { color: #92400e; }
.bm-import-panel__body { flex: 1; min-width: 0; }
.bm-import-panel__title { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }
.bm-import-panel__list {
    margin: 8px 0 0;
    padding-left: 18px;
    font-size: 0.75rem;
    color: var(--on-surface-var);
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.bm-import-panel__close {
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
.bm-import-panel__close:hover { background: rgba(0, 0, 0, 0.06); }

/* ── Body / table ──────────────────────────────────────────────────────── */
.bm-body { padding: 1.5rem; }

.bm-table-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

/* ── Toolbar ───────────────────────────────────────────────────────────── */
.bm-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}
.bm-search {
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
.bm-search :deep(.el-icon) { font-size: 14px; }
.bm-search input {
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.8125rem;
    color: var(--on-surface);
    width: 100%;
    font-family: inherit;
}
.bm-toolbar__stats { display: flex; align-items: center; gap: 10px; }
.bm-stat { font-size: 0.75rem; color: var(--on-surface-var); }
.bm-stat strong { color: var(--on-surface); font-weight: 700; }
.bm-stat-dot { width: 3px; height: 3px; border-radius: 50%; background: var(--border); }

/* ── Table ─────────────────────────────────────────────────────────────── */
.bm-table thead th {
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
.bm-table tbody td { padding: 13px 16px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.bm-row { transition: background 0.12s; }
.bm-row:hover { background: var(--surface-low); }
.bm-row:not(:last-child) td { border-bottom: 1px solid var(--surface-low); }

.bm-th { display: inline-flex; align-items: center; gap: 5px; }
.bm-th :deep(.el-icon) { font-size: 12px; color: #9ca3af; }

.bm-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 9px;
    font-size: 0.75rem;
    font-weight: 800;
    flex-shrink: 0;
}
.bm-name { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); line-height: 1.3; }
.bm-id-chip {
    display: inline-flex;
    margin-top: 2px;
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: var(--on-surface-var);
    background: var(--surface-low);
    border-radius: 4px;
    padding: 1px 5px;
    font-family: 'SFMono-Regular', Consolas, monospace;
}

.bm-role { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
.bm-role-chip {
    display: inline-flex;
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--green);
    background: rgba(20, 92, 66, 0.08);
    border-radius: 999px;
    padding: 2px 9px;
}
.bm-role__position { font-size: 0.75rem; color: var(--on-surface-var); }

.bm-contact { display: flex; flex-direction: column; gap: 4px; font-size: 0.8125rem; color: var(--on-surface-var); }
.bm-contact__row { display: inline-flex; align-items: center; gap: 6px; }
.bm-contact__row :deep(.el-icon) { color: #9ca3af; flex-shrink: 0; }

.bm-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: capitalize;
    padding: 3px 10px;
    border-radius: 999px;
}
.bm-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.bm-status--active { background: #dcfce7; color: #166534; }
.bm-status--inactive { background: #f1f5f9; color: #64748b; }

.bm-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; }
.bm-act-btn {
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
.bm-act-btn--edit { color: var(--green); }
.bm-act-btn--edit:hover { background: rgba(20, 92, 66, .08); }
.bm-act-btn--danger { color: #b91c1c; }
.bm-act-btn--danger:hover { background: rgba(185, 28, 28, .08); }
.bm-act-btn:disabled { opacity: 0.5; cursor: default; }

/* ── Pagination ────────────────────────────────────────────────────────── */
.bm-pagination {
    display: flex;
    justify-content: flex-end;
    padding: 12px 16px;
    border-top: 1px solid var(--border);
}
.bm-pagination :deep(.el-pagination) { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; font-family: inherit; }
.bm-pagination :deep(.el-pagination__total) { margin-right: auto; font-size: 0.75rem; font-weight: 600; color: var(--on-surface-var); }
.bm-pagination :deep(.btn-prev),
.bm-pagination :deep(.btn-next) { width: 30px; height: 30px; border-radius: 7px; background: #fff; border: 1px solid var(--border); color: var(--on-surface-var); transition: all .15s ease; }
.bm-pagination :deep(.btn-prev:hover:not(:disabled)),
.bm-pagination :deep(.btn-next:hover:not(:disabled)) { border-color: var(--green); color: var(--green); background: var(--surface-low); }
.bm-pagination :deep(.btn-prev:disabled),
.bm-pagination :deep(.btn-next:disabled) { opacity: 0.5; }
.bm-pagination :deep(.el-pager li) {
    min-width: 30px;
    height: 30px;
    border-radius: 7px;
    background: #fff;
    border: 1px solid var(--border);
    color: var(--on-surface);
    font-size: 0.75rem;
    font-weight: 700;
    margin: 0 2px;
}
.bm-pagination :deep(.el-pager li:hover) { color: var(--green); border-color: var(--green); }
.bm-pagination :deep(.el-pager li.is-active) { background: var(--green); border-color: var(--green); color: #fff; }

/* ── No search results ─────────────────────────────────────────────────── */
.bm-no-results {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 2.5rem 1rem;
    font-size: 0.8125rem;
    color: var(--on-surface-var);
}
.bm-no-results :deep(.el-icon) { color: #cbd5e1; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.bm-empty { text-align: center; padding: 4rem 1rem; }
.bm-empty__icon {
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
.bm-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.bm-empty__text { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0 auto; max-width: 320px; line-height: 1.5; }

/* ── Responsive ────────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .bm-body { padding: 1.25rem; }
}
</style>
