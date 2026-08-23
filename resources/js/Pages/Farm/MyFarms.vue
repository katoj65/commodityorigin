<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import {
    Plus, View, Edit, Delete, Box,
    House, Location, TrendCharts, Collection, DataLine, Checked,
} from '@element-plus/icons-vue';

const props = defineProps({
    farms: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
});

function isActive(farm) {
    return (farm.status || 'Active').toLowerCase() === 'active';
}

function statusTagType(farm) {
    return isActive(farm) ? 'success' : 'info';
}

function goToFarm(farm) {
    router.visit(route('farm.show', farm.id));
}

/* ── Edit farm ─────────────────────────────────────────────────────── */
const editDialogOpen = ref(false);
const editingFarmId = ref(null);

const editForm = useForm({
    name: '',
    location: '',
    size: '',
    altitude: '',
    variety: '',
    notes: '',
});

function openEditDialog(farm) {
    editingFarmId.value = farm.id;
    editForm.clearErrors();
    editForm.name = farm.name;
    editForm.location = farm.location;
    editForm.size = farm.size;
    editForm.altitude = farm.altitude;
    editForm.variety = farm.variety;
    editForm.notes = farm.notes;
    editDialogOpen.value = true;
}

function submitEditFarm() {
    editForm.patch(route('farm.update', editingFarmId.value), {
        preserveScroll: true,
        onSuccess: () => { editDialogOpen.value = false; },
    });
}

/* ── Delete farm ───────────────────────────────────────────────────── */
const deleteDialogOpen = ref(false);
const deletingFarm = ref(false);
const farmToDelete = ref(null);

function openDeleteDialog(farm) {
    farmToDelete.value = farm;
    deleteDialogOpen.value = true;
}

function deleteFarm() {
    if (!farmToDelete.value) return;
    deletingFarm.value = true;
    router.delete(route('farm.destroy', farmToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deletingFarm.value = false;
            deleteDialogOpen.value = false;
            farmToDelete.value = null;
        },
    });
}
</script>

<template>
    <DesignPreviewLayout title="My Farms">
        <Head title="My Farms" />

        <div class="mf-page">

            
            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="mf-header">
                <div class="mf-header__inner">
                    <div>
                        <div class="mf-kicker">Farm Workspace</div>
                        <h1 class="mf-title mb-0">My Farms</h1>
                        <p class="mf-subtitle mb-0">Farms you've registered on Bean Origin</p>
                    </div>
                    <Link :href="route('farmer.index')" class="mf-btn-primary">
                        <el-icon><Plus /></el-icon> Add Farm
                    </Link>
                </div>
            </div>

            <!-- ── Toolbar ───────────────────────────────────────────────── -->
            <div class="mf-toolbar">
                <span class="mf-toolbar__title">All Farms</span>
                <span class="mf-toolbar__count">{{ farms.length }} total</span>
            </div>

            <!-- ── Table (boxed card) ───────────────────────────────────── -->
            <div class="mf-card">
            <el-table
                :data="farms"
                class="mf-table"
                row-key="id"
                @row-click="goToFarm"
            >
                <el-table-column prop="name" min-width="200">
                    <template #header><span class="mf-th"><el-icon><House /></el-icon> Farm</span></template>
                    <template #default="{ row }">
                        <span class="mf-cell-name">{{ row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="location" min-width="170">
                    <template #header><span class="mf-th"><el-icon><Location /></el-icon> Location</span></template>
                    <template #default="{ row }">{{ row.location || '—' }}</template>
                </el-table-column>
                <el-table-column prop="size" min-width="110">
                    <template #header><span class="mf-th"><el-icon><Box /></el-icon> Size</span></template>
                    <template #default="{ row }">{{ row.size || '—' }}</template>
                </el-table-column>
                <el-table-column prop="altitude" min-width="110">
                    <template #header><span class="mf-th"><el-icon><TrendCharts /></el-icon> Altitude</span></template>
                    <template #default="{ row }">{{ row.altitude || '—' }}</template>
                </el-table-column>
                <el-table-column prop="variety" min-width="150">
                    <template #header><span class="mf-th"><el-icon><Collection /></el-icon> Variety</span></template>
                    <template #default="{ row }">{{ row.variety || '—' }}</template>
                </el-table-column>
                <el-table-column prop="harvests_count" min-width="100" align="center">
                    <template #header><span class="mf-th mf-th--center"><el-icon><DataLine /></el-icon> Harvests</span></template>
                    <template #default="{ row }">{{ row.harvests_count ?? 0 }}</template>
                </el-table-column>
                <el-table-column min-width="110">
                    <template #header><span class="mf-th"><el-icon><Checked /></el-icon> Status</span></template>
                    <template #default="{ row }">
                        <el-tag :type="statusTagType(row)" size="small" effect="light" round>
                            {{ row.status || 'Active' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="" min-width="120" align="right">
                    <template #default="{ row }">
                        <div class="mf-row-actions">
                            <el-tooltip content="View" placement="top">
                                <Link :href="route('farm.show', row.id)" class="mf-act-btn mf-act-btn--view" @click.stop>
                                    <el-icon><View /></el-icon>
                                </Link>
                            </el-tooltip>
                            <el-tooltip content="Edit" placement="top">
                                <button type="button" class="mf-act-btn mf-act-btn--edit" @click.stop="openEditDialog(row)">
                                    <el-icon><Edit /></el-icon>
                                </button>
                            </el-tooltip>
                            <el-tooltip content="Delete" placement="top">
                                <button type="button" class="mf-act-btn mf-act-btn--delete" @click.stop="openDeleteDialog(row)">
                                    <el-icon><Delete /></el-icon>
                                </button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-table-column>

                <template #empty>
                    <div class="mf-empty">
                        <div class="mf-empty__icon"><el-icon :size="24"><Box /></el-icon></div>
                        <div class="mf-empty__title">You haven't added any farms yet</div>
                        <p class="mf-empty__text">Register your first farm to start tracking harvests, quality, and traceability.</p>
                        <Link :href="route('farmer.index')" class="mf-btn-primary">
                            <el-icon><Plus /></el-icon> Add Your First Farm
                        </Link>
                    </div>
                </template>
            </el-table>
            </div>

            <!-- ── Edit Farm modal — borrows the fp-modal design language ── -->
            <el-dialog v-model="editDialogOpen" width="560px" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon"><el-icon :size="18"><Edit /></el-icon></div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Workspace</div>
                            <div class="fp-modal__title">Edit Farm</div>
                        </div>
                    </div>
                </template>

                <form id="edit-farm-form" class="fp-modal__body" @submit.prevent="submitEditFarm">
                    <div class="fp-field">
                        <label class="fp-field__label">Farm Name</label>
                        <el-input v-model="editForm.name" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.name }" />
                        <InputError class="fp-field__error" :message="editForm.errors.name" />
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Location</label>
                            <el-input v-model="editForm.location" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.location }" />
                            <InputError class="fp-field__error" :message="editForm.errors.location" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Altitude</label>
                            <el-input v-model="editForm.altitude" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.altitude }" />
                            <InputError class="fp-field__error" :message="editForm.errors.altitude" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Farm Size</label>
                            <el-input v-model="editForm.size" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.size }" />
                            <InputError class="fp-field__error" :message="editForm.errors.size" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Variety</label>
                            <el-select v-model="editForm.variety" placeholder="Select crop variety" clearable class="fp-field-input w-100" :class="{ 'fp-field-input--error': editForm.errors.variety }">
                                <el-option v-for="option in varietyOptions" :key="option" :label="option" :value="option" />
                            </el-select>
                            <InputError class="fp-field__error" :message="editForm.errors.variety" />
                        </div>
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Notes <span class="fp-field__optional">(optional)</span></label>
                        <el-input v-model="editForm.notes" type="textarea" :rows="3" class="fp-field-input" />
                        <InputError class="fp-field__error" :message="editForm.errors.notes" />
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="submit" form="edit-farm-form" class="mf-btn-primary" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete Farm modal — borrows the fp-modal design language ── -->
            <el-dialog v-model="deleteDialogOpen" width="420px" align-center class="fp-modal fp-modal--danger">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon fp-modal__head-icon--danger"><el-icon :size="18"><Delete /></el-icon></div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Workspace</div>
                            <div class="fp-modal__title">Delete Farm</div>
                        </div>
                    </div>
                </template>

                <div v-if="farmToDelete" class="fp-modal__body">
                    <p class="fp-modal__confirm-text">
                        Are you sure you want to delete <strong>{{ farmToDelete.name }}</strong>? This action cannot be undone.
                    </p>
                </div>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="button" class="mf-btn-danger" :disabled="deletingFarm" @click="deleteFarm">
                            {{ deletingFarm ? 'Deleting…' : 'Delete Farm' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.mf-page {
    --green: #004532;
    --green-dark: #002e20;
    --red: #dc2626;
    --red-dark: #b91c1c;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-white: #ffffff;
    --surface-low: #f8fafc;
    --surface-high: #eef2f0;
    --shadow-sm: 0 1px 2px rgba(15, 23, 42, .05);
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
    line-height: 1.5;
}

/* ── Header ────────────────────────────────────────────────────────────── */
.mf-header { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.mf-header__inner { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: 1rem clamp(1rem, 3vw, 2rem); }
.mf-kicker { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 4px; line-height: 1.4; }
.mf-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.25; }
.mf-subtitle { font-size: .8125rem; color: var(--on-surface-var); margin-top: 2px; line-height: 1.5; }

/* ── Toolbar ───────────────────────────────────────────────────────────── */
.mf-toolbar { display: flex; align-items: center; justify-content: space-between; padding: .875rem clamp(1rem, 3vw, 2rem); }
.mf-toolbar__title { font-size: .875rem; font-weight: 800; color: var(--on-surface); }
.mf-toolbar__count { font-size: .75rem; font-weight: 600; color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────
   NOTE: literal hex values on purpose, not var(--green)/var(--red) — these
   classes are also used inside <el-dialog>, which teleports its content to
   <body>, outside .mf-page's CSS custom-property cascade. */
.mf-btn-primary { background: #004532; border: none; color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 16px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); transition: background .15s ease; cursor: pointer; }
.mf-btn-primary:hover { background: #002e20; color: #fff; }
.mf-btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.mf-btn-danger { background: #dc2626; border: none; color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 16px; cursor: pointer; transition: background .15s ease; }
.mf-btn-danger:hover { background: #b91c1c; }
.mf-btn-danger:disabled { opacity: .6; cursor: not-allowed; }

/* ── Card — boxes the table exactly like .mkt-card on the market listing
   page: floating, elevated, rounded, instead of an edge-to-edge table
   sitting flat on the page background. ───────────────────────────────── */
.mf-card {
    margin: 0 clamp(1rem, 3vw, 2rem);
    border: 1px solid var(--surface-high);
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03), 0 12px 28px -18px rgba(15, 23, 42, .14);
}

/* ── Table (boxed) ─────────────────────────────────────────────────────── */
.mf-table { width: 100%; }
.mf-table :deep(.el-table__inner-wrapper::before) { display: none; }
.mf-table :deep(.el-table__row) { cursor: pointer; }
.mf-table :deep(th.el-table__cell) { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-var); padding: 12px 0; }
.mf-table :deep(td.el-table__cell) { font-size: .8125rem; color: var(--on-surface); padding: 12px 0; }
.mf-table :deep(.el-table__cell:first-child .cell) { padding-left: 1.25rem; }
.mf-table :deep(.el-table__cell:last-child .cell) { padding-right: 1.25rem; }
.mf-cell-name { font-weight: 700; }

/* ── Table header icons ───────────────────────────────────────────────── */
.mf-th { display: inline-flex; align-items: center; gap: 5px; }
.mf-th--center { justify-content: center; }

/* ── Row actions ───────────────────────────────────────────────────────── */
.mf-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; }
.mf-act-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 7px; text-decoration: none; border: none; background: transparent; cursor: pointer; transition: background .15s ease; }
.mf-act-btn--view { color: var(--green); }
.mf-act-btn--view:hover { background: rgba(0, 69, 50, .08); }
.mf-act-btn--edit { color: var(--on-surface-var); }
.mf-act-btn--edit:hover { background: var(--surface-low); color: var(--on-surface); }
.mf-act-btn--delete { color: var(--red); }
.mf-act-btn--delete:hover { background: #fef2f2; color: var(--red-dark); }

/* ── Empty state ───────────────────────────────────────────────────────── */
.mf-empty { text-align: center; padding: 3rem 1rem; }
.mf-empty__icon { width: 52px; height: 52px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.mf-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.mf-empty__text { font-size: .8125rem; color: var(--on-surface-var); margin-bottom: 16px; max-width: 360px; margin-left: auto; margin-right: auto; line-height: 1.5; }
.mf-empty .mf-btn-primary { display: inline-flex; }

/* ── Modal — borrowed verbatim from FarmProfile.vue's fp-modal system ────
   NOTE: <el-dialog> teleports to <body>, outside .mf-page, so CSS custom
   properties like var(--green) do not cascade in — literal hex values
   are used below on purpose. */
:deep(.el-dialog.fp-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, .22);
    font-family: 'Manrope', system-ui, sans-serif;
}
:deep(.el-dialog.fp-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.fp-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.fp-modal .el-dialog__footer) { padding: 0; }

.fp-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #f3f4f6; }
.fp-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(0,69,50,.08); color: #004532; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-modal__head-icon--danger { background: #fee2e2; color: #dc2626; }
.fp-modal__head-text { flex: 1; min-width: 0; }
.fp-modal__eyebrow { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #004532; margin-bottom: 1px; }
.fp-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -.01em; }

.fp-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }
.fp-modal__confirm-text { font-size: 0.875rem; color: #374151; line-height: 1.5; margin: 0; }

.fp-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.fp-field { display: flex; flex-direction: column; gap: 5px; }
.fp-field__label { font-size: .75rem; font-weight: 600; color: #374151; }
.fp-field__optional { font-weight: 400; color: #9ca3af; }
.fp-field__error { font-size: .75rem; font-weight: 600; color: #dc2626; margin-top: 4px; display: block; }

:deep(.fp-field-input .el-input__wrapper),
:deep(.fp-field-input .el-textarea__inner) { box-shadow: 0 0 0 1px #d1d5db inset; border-radius: 8px; }
.fp-field-input--error :deep(.el-input__wrapper),
.fp-field-input--error :deep(.el-textarea__inner),
.fp-field-input--error :deep(.el-select__wrapper) { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

/* Footer has no Cancel button — the single action sits right-aligned. */
.fp-modal__footer { display: flex; justify-content: flex-end; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

@media (max-width: 575.98px) {
    .fp-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.fp-modal) { width: 92vw !important; }
}
</style>
