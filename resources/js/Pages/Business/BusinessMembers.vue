<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import AddBusinessMemberDialog from '@/Components/Modals/AddBusinessMemberDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Close, Delete, Message, Phone, Plus, Search, UploadFilled, UserFilled, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    business: { type: Object, required: true },
    members: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
    importResult: { type: Object, default: null },
});

/* ── Search ──────────────────────────────────────────────────────────── */
const searchQuery = ref('');

const filteredMembers = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.members;

    return props.members.filter((m) => [
        m.name, m.designation, m.position, m.telephone, m.email, m.id_number,
    ].filter(Boolean).join(' ').toLowerCase().includes(q));
});

/* ── Pagination ──────────────────────────────────────────────────────── */
const currentPage = ref(1);
const pageSize = 9;

const pagedMembers = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredMembers.value.slice(start, start + pageSize);
});

watch([searchQuery, () => props.members], () => {
    currentPage.value = 1;
});

/* ── Add / edit member ───────────────────────────────────────────────── */
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

/* ── Import from Excel ───────────────────────────────────────────────── */
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
                    : 'No rows were imported. See the details below.',
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

/* ── Remove member ───────────────────────────────────────────────────── */
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
    <DesignPreviewLayout title="Business Membership">
        <Head title="Business Membership" />

        <div class="bm-page">
            <div class="bm-hero">
                <div class="bm-hero__text">
                    <h1 class="dp-display-md">Business Membership</h1>
                    <p class="bm-subtitle">Your team, all in one place — keep memberships active and the right people connected so nothing lapses.</p>
                </div>
            </div>

            <!-- ── Import results ───────────────────────────────────────── -->
            <div v-if="importResult && importResultVisible" class="bm-card bm-import-panel" :class="{ 'bm-import-panel--warn': importResult.errors.length }">
                <div class="bm-import-panel__icon">
                    <el-icon :size="16"><WarningFilled v-if="importResult.errors.length" /><UploadFilled v-else /></el-icon>
                </div>
                <div class="bm-import-panel__body">
                    <div class="bm-import-panel__title">
                        {{ importResult.imported }} member{{ importResult.imported === 1 ? '' : 's' }} imported
                        <span v-if="importResult.errors.length">, {{ importResult.errors.length }} row{{ importResult.errors.length === 1 ? '' : 's' }} skipped</span>
                    </div>
                    <ul v-if="importResult.errors.length" class="bm-import-panel__list">
                        <li v-for="err in importResult.errors" :key="err.row">Row {{ err.row }}: {{ err.errors.join(' ') }}</li>
                    </ul>
                </div>
                <button type="button" class="bm-import-panel__close" aria-label="Dismiss" @click="importResultVisible = false">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>

            <!-- ── Section header ────────────────────────────────────────── -->
            <div class="bm-section-head">
                <h2 class="bm-section-title">Organization Members</h2>
                <div class="bm-toolbar">
                    <div class="bm-search">
                        <el-icon :size="14"><Search /></el-icon>
                        <input v-model="searchQuery" type="text" placeholder="Search members…">
                    </div>
                    <template v-if="canManage">
                        <input ref="fileInput" type="file" accept=".xlsx,.xls" class="d-none" @change="handleFileChange">
                        <button type="button" class="bm-btn bm-btn--outline" :disabled="importing" @click="openFilePicker">
                            <el-icon :size="14"><UploadFilled /></el-icon> {{ importing ? 'Importing…' : 'Import Excel' }}
                        </button>
                        <button type="button" class="bm-btn bm-btn--primary" @click="openAddMember">
                            <el-icon :size="14"><Plus /></el-icon> Invite Member
                        </button>
                    </template>
                </div>
            </div>

            <!-- ── Member cards ──────────────────────────────────────────── -->
            <div v-if="members.length">
                <div v-if="pagedMembers.length" class="bm-grid">
                    <div v-for="member in pagedMembers" :key="member.id" class="bm-card bm-member" :class="{ 'bm-member--inactive': member.status !== 'active' }">
                        <div class="bm-member__head">
                            <div class="bm-member__identity">
                                <div class="bm-member__avatar">
                                    <img v-if="member.photo_url" :src="member.photo_url" :alt="member.name">
                                    <el-icon v-else :size="20"><UserFilled /></el-icon>
                                </div>
                                <div>
                                    <h3 class="bm-member__name">{{ member.name }}</h3>
                                    <p class="bm-member__title">{{ member.designation || '—' }}</p>
                                </div>
                            </div>
                            <span class="bm-status-pill" :class="`bm-status-pill--${member.status === 'active' ? 'green' : 'muted'}`">{{ member.status === 'active' ? 'Active' : 'Inactive' }}</span>
                        </div>

                        <div class="bm-member__details">
                            <span class="bm-member__detail"><el-icon :size="14"><Message /></el-icon> {{ member.email || '—' }}</span>
                            <span class="bm-member__detail"><el-icon :size="14"><Phone /></el-icon> {{ member.telephone || '—' }}</span>
                            <span v-if="member.position" class="bm-member__detail"><el-icon :size="14"><UserFilled /></el-icon> {{ member.position }}</span>
                        </div>

                        <div v-if="canManage" class="bm-member__actions">
                            <button type="button" class="bm-btn bm-btn--outline bm-btn--block" @click="openEditMember(member)">
                                {{ member.status === 'active' ? 'Manage Access' : 'Reactivate' }}
                            </button>
                            <button type="button" class="bm-icon-btn bm-icon-btn--danger" title="Remove member" :disabled="removingId === member.id" @click="requestRemove(member)">
                                <el-icon :size="15"><Delete /></el-icon>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="bm-card bm-no-results">
                    <el-icon :size="18"><Search /></el-icon>
                    No members match "{{ searchQuery }}".
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

            <div v-else class="bm-card bm-empty">
                <div class="bm-empty__icon"><el-icon :size="22"><UserFilled /></el-icon></div>
                <div class="bm-empty__title">No members registered yet</div>
                <p class="bm-empty__text">{{ canManage ? 'Invite your first member using the button above.' : 'This business has not registered any members yet.' }}</p>
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
    </DesignPreviewLayout>
</template>

<style scoped>
.bm-page {
    --card-border: var(--dp-outline-variant);
    --card-radius: 6px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    font-family: var(--dp-font-sans);
}

/* ── Hero ────────────────────────────────────────────────────────────── */
.bm-hero__text h1 { color: var(--dp-primary); }
.bm-subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 400; color: var(--dp-on-surface-variant); margin: 0; max-width: 720px; }

/* ── Cards ───────────────────────────────────────────────────────────── */
.bm-card {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    box-shadow: var(--dp-card-shadow);
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.bm-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 16px;
    border: none;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
}
.bm-btn--outline { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); box-shadow: var(--dp-card-shadow); }
.bm-btn--outline:hover { background: var(--dp-surface-container-low); }
.bm-btn--outline:disabled { opacity: 0.6; cursor: default; }
.bm-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.bm-btn--primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(39, 19, 16, 0.2); }
.bm-btn--block { width: 100%; }
.bm-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.bm-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 12px;
    border: none;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.15s ease, color 0.15s ease;
}
.bm-icon-btn--danger:hover { background: var(--dp-error-container); color: var(--dp-error); }
.bm-icon-btn:disabled { opacity: 0.5; cursor: default; }

/* ── Import panel ────────────────────────────────────────────────────── */
.bm-import-panel { display: flex; align-items: flex-start; gap: 12px; padding: 16px 18px; }
.bm-import-panel--warn { background: #fffbeb; }
.bm-import-panel__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--dp-secondary-container);
    color: var(--dp-on-secondary-container);
    flex-shrink: 0;
}
.bm-import-panel--warn .bm-import-panel__icon { background: #fde68a; color: #92400e; }
.bm-import-panel__body { flex: 1; min-width: 0; }
.bm-import-panel__title { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.bm-import-panel__list { margin: 8px 0 0; padding-left: 18px; font-size: 12px; color: var(--dp-on-surface-variant); display: flex; flex-direction: column; gap: 3px; }
.bm-import-panel__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    flex-shrink: 0;
}
.bm-import-panel__close:hover { background: var(--dp-surface-container-low); }

/* ── Section head / toolbar ──────────────────────────────────────────── */
.bm-section-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; }
.bm-section-title { margin: 0; font-size: 20px; font-weight: 800; color: var(--dp-primary); letter-spacing: -0.01em; font-family: var(--dp-font-sans); }
.bm-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.bm-search {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 14px;
    height: 38px;
    width: 220px;
    border-radius: 999px;
    background: var(--dp-surface-container-lowest);
    box-shadow: var(--dp-card-shadow);
    color: var(--dp-outline);
}
.bm-search input { border: none; outline: none; background: transparent; font-size: 12.5px; color: var(--dp-on-surface); width: 100%; font-family: inherit; }

/* ── Member grid ─────────────────────────────────────────────────────── */
.bm-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
.bm-member { padding: 22px; display: flex; flex-direction: column; gap: 18px; }
.bm-member--inactive { opacity: 0.75; }

.bm-member__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.bm-member__identity { display: flex; align-items: center; gap: 14px; min-width: 0; }
.bm-member__avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}
.bm-member__avatar img { width: 100%; height: 100%; object-fit: cover; }
.bm-member__name { margin: 0; font-size: 14.5px; font-weight: 700; color: var(--dp-on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bm-member__title { margin: 3px 0 0; font-size: 12px; color: var(--dp-on-surface-variant); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.bm-status-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
}
.bm-status-pill--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.bm-status-pill--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.bm-member__details { display: flex; flex-direction: column; gap: 8px; }
.bm-member__detail { display: inline-flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--dp-on-surface-variant); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bm-member__detail .el-icon { color: var(--dp-outline); flex-shrink: 0; }

.bm-member__actions { display: flex; align-items: center; gap: 8px; margin-top: auto; }

/* ── States ──────────────────────────────────────────────────────────── */
.bm-no-results, .bm-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 48px 20px;
    text-align: center;
    color: var(--dp-outline);
}
.bm-no-results { flex-direction: row; padding: 40px 20px; font-size: 13px; color: var(--dp-on-surface-variant); }
.bm-empty__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
}
.bm-empty__title { font-size: 15px; font-weight: 700; color: var(--dp-on-surface); }
.bm-empty__text { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; max-width: 40ch; }

/* ── Pagination ──────────────────────────────────────────────────────── */
.bm-pagination { display: flex; justify-content: flex-end; margin-top: 18px; }
.bm-pagination :deep(.el-pagination) { font-family: inherit; }
.bm-pagination :deep(.el-pagination__total) { color: var(--dp-on-surface-variant); font-size: 12.5px; font-weight: 600; }
.bm-pagination :deep(.el-pager li.is-active) { background: var(--dp-primary); color: var(--dp-on-primary); }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .bm-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 700px) {
    .bm-grid { grid-template-columns: 1fr; }
    .bm-search { width: 100%; }
    .bm-toolbar { width: 100%; }
    .bm-toolbar .bm-btn { flex: 1; }
}
</style>
