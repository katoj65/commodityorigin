<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import AddFarmModal from '@/Components/Modals/AddFarmModal.vue';
import {
    Plus, View, Edit, Delete, Box,
    House, Location, TrendCharts, Collection,
} from '@element-plus/icons-vue';

const props = defineProps({
    farms: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
    canCreateFarm: { type: Boolean, default: false },
});

function isActive(farm) {
    return (farm.status || 'Active').toLowerCase() === 'active';
}

function goToFarm(farm) {
    router.visit(route('farm.show', farm.id));
}

function farmLocation(farm) {
    return [farm.district, farm.region].filter(Boolean).join(', ') || farm.country || '—';
}

/* ── Add farm ──────────────────────────────────────────────────────── */
const addDialogOpen = ref(false);

function openAddDialog() {
    addDialogOpen.value = true;
}

/* ── Edit farm ─────────────────────────────────────────────────────── */
const editDialogOpen = ref(false);
const editingFarmId = ref(null);

const editForm = useForm({
    name: '',
    coffee_type: '',
    country: '',
    region: '',
    district: '',
    county: '',
    subcounty: '',
    parish: '',
    village: '',
    latitude: '',
    longitude: '',
    elevation: '',
    total_area: '',
    coffee_area: '',
});

function openEditDialog(farm) {
    editingFarmId.value = farm.id;
    editForm.clearErrors();
    editForm.name = farm.name || '';
    editForm.coffee_type = farm.coffee_type || '';
    editForm.country = farm.country || '';
    editForm.region = farm.region || '';
    editForm.district = farm.district || '';
    editForm.county = farm.county || '';
    editForm.subcounty = farm.subcounty || '';
    editForm.parish = farm.parish || '';
    editForm.village = farm.village || '';
    editForm.latitude = farm.latitude ?? '';
    editForm.longitude = farm.longitude ?? '';
    editForm.elevation = farm.elevation ?? '';
    editForm.total_area = farm.total_area ?? '';
    editForm.coffee_area = farm.coffee_area ?? '';
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
        <div class="mf-page">

            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="mf-header">
                <div class="mf-header__text">
                    <h1 class="mf-title">My Farms</h1>
                    <p class="mf-subtitle">Farms you've registered on Bean Origin.</p>
                </div>
                <button v-if="canCreateFarm" type="button" class="mf-btn mf-btn--primary" @click="openAddDialog">
                    <el-icon><Plus /></el-icon> Add Farm
                </button>
            </div>

            <!-- ── Toolbar ───────────────────────────────────────────────── -->
            <div class="mf-toolbar">
                <span class="mf-toolbar__title">All Farms</span>
                <span class="mf-toolbar__count">{{ farms.length }} total</span>
            </div>

            <!-- ── List (boxed card) ────────────────────────────────────── -->
            <div class="mf-card">
                <div v-if="farms.length" class="mf-list">
                    <div v-for="farm in farms" :key="farm.id" class="mf-list-row" @click="goToFarm(farm)">
                        <div class="mf-list-row__icon"><el-icon><House /></el-icon></div>
                        <div class="mf-list-row__main">
                            <div class="mf-list-row__title">{{ farm.name }}</div>
                            <div class="mf-list-row__sub"><el-icon :size="11"><Location /></el-icon> {{ farmLocation(farm) }}</div>
                        </div>
                        <div class="mf-list-row__stats">
                            <div class="mf-list-stat">
                                <span class="mf-list-stat__value">{{ farm.total_area ? `${Number(farm.total_area).toLocaleString()} ha` : '—' }}</span>
                                <span class="mf-list-stat__label">Size</span>
                            </div>
                            <div class="mf-list-stat">
                                <span class="mf-list-stat__value">{{ farm.elevation ? `${Number(farm.elevation).toLocaleString()} m` : '—' }}</span>
                                <span class="mf-list-stat__label">Altitude</span>
                            </div>
                            <div class="mf-list-stat mf-list-stat--wide">
                                <span class="mf-list-stat__value">{{ farm.coffee_type || '—' }}</span>
                                <span class="mf-list-stat__label">Variety</span>
                            </div>
                            <span class="mf-badge" :class="isActive(farm) ? 'mf-badge--good' : 'mf-badge--neutral'">{{ farm.status || 'Active' }}</span>
                        </div>
                        <div class="mf-row-actions" @click.stop>
                            <el-tooltip content="View" placement="top">
                                <Link :href="route('farm.show', farm.id)" class="mf-act-btn mf-act-btn--view">
                                    <el-icon><View /></el-icon>
                                </Link>
                            </el-tooltip>
                            <el-tooltip content="Edit" placement="top">
                                <button type="button" class="mf-act-btn mf-act-btn--edit" @click="openEditDialog(farm)">
                                    <el-icon><Edit /></el-icon>
                                </button>
                            </el-tooltip>
                            <el-tooltip content="Delete" placement="top">
                                <button type="button" class="mf-act-btn mf-act-btn--delete" @click="openDeleteDialog(farm)">
                                    <el-icon><Delete /></el-icon>
                                </button>
                            </el-tooltip>
                        </div>
                    </div>
                </div>

                <div v-else class="mf-empty">
                    <el-icon :size="24" class="mf-empty__icon"><Box /></el-icon>
                    <div class="mf-empty__title">You haven't added any farms yet</div>
                    <p class="mf-empty__text">Register your first farm to start tracking quality and traceability.</p>
                    <button v-if="canCreateFarm" type="button" class="mf-btn mf-btn--primary" @click="openAddDialog">
                        <el-icon><Plus /></el-icon> Add Your First Farm
                    </button>
                </div>
            </div>

            <!-- ── Add Farm modal ───────────────────────────────────────── -->
            <AddFarmModal v-model="addDialogOpen" />

            <!-- ── Edit Farm modal — borrows the fp-modal design language ── -->
            <el-dialog v-model="editDialogOpen" width="min(680px, calc(100vw - 2rem))" align-center class="fp-modal">
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
                            <label class="fp-field__label">Coffee Type</label>
                            <el-input v-model="editForm.coffee_type" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.coffee_type }" />
                            <InputError class="fp-field__error" :message="editForm.errors.coffee_type" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Country</label>
                            <el-input v-model="editForm.country" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.country }" />
                            <InputError class="fp-field__error" :message="editForm.errors.country" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Region</label>
                            <el-input v-model="editForm.region" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.region }" />
                            <InputError class="fp-field__error" :message="editForm.errors.region" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">District</label>
                            <el-input v-model="editForm.district" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.district }" />
                            <InputError class="fp-field__error" :message="editForm.errors.district" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Elevation (m)</label>
                            <el-input v-model="editForm.elevation" type="number" step="0.01" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.elevation }" />
                            <InputError class="fp-field__error" :message="editForm.errors.elevation" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Total Area (ha)</label>
                            <el-input v-model="editForm.total_area" type="number" min="0" step="0.01" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.total_area }" />
                            <InputError class="fp-field__error" :message="editForm.errors.total_area" />
                        </div>
                    </div>

                    <div class="fp-field">
                        <label class="fp-field__label">Coffee Area (ha) <span class="fp-field__optional">(optional)</span></label>
                        <el-input v-model="editForm.coffee_area" type="number" min="0" step="0.01" class="fp-field-input" :class="{ 'fp-field-input--error': editForm.errors.coffee_area }" />
                        <InputError class="fp-field__error" :message="editForm.errors.coffee_area" />
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="submit" form="edit-farm-form" class="mf-btn mf-btn--primary" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete Farm modal — borrows the fp-modal design language ── -->
            <ConfirmDialog
                v-model="deleteDialogOpen"
                eyebrow="Farm Workspace"
                title="Delete Farm"
                :message="farmToDelete ? `Are you sure you want to delete ${farmToDelete.name}? This action cannot be undone.` : ''"
                confirm-text="Delete Farm"
                :auto-close="false"
                :loading="deletingFarm"
                @confirm="deleteFarm"
            />

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.mf-page {
    --primary: #000000;
    --on-primary: #ffffff;
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --surface-elevated: #F1F2F3;
    --border: #E5E7EB;
    --text: #121516;
    --text-2: #4B5457;
    --text-muted: #6F7677;
    --success: #15803D;
    --success-soft: #F0FDF4;
    --error: #B91C1C;
    --error-soft: #FEF2F2;
    --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-family: var(--font-sans);
    background: var(--surface);
    color: var(--text);
    min-height: 100%;
}

/* ── Header ────────────────────────────────────────────────────────────── */
.mf-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
.mf-header__text { min-width: 0; }
.mf-title { font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0 0 6px; }
.mf-subtitle { font-size: 13.5px; line-height: 20px; color: var(--text-2); margin: 0; max-width: 60ch; }

/* ── Toolbar ───────────────────────────────────────────────────────────── */
.mf-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.mf-toolbar__title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text); }
.mf-toolbar__count { font-size: 12px; font-weight: 600; color: var(--text-muted); }

/* ── Buttons ───────────────────────────────────────────────────────────────
   NOTE: literal hex values on purpose, not var(--primary) — these classes
   are also used inside <el-dialog>, which teleports its content to <body>,
   outside .mf-page's CSS custom-property cascade. */
.mf-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    height: 36px; padding: 0 16px; border-radius: 6px;
    font-size: 13px; font-weight: 600; border: 1px solid transparent;
    text-decoration: none; cursor: pointer; transition: opacity 120ms ease;
}
.mf-btn--primary { background: #000000; color: #fff; }
.mf-btn--primary:hover:not(:disabled) { opacity: 0.88; }
.mf-btn--primary:disabled { opacity: .5; cursor: not-allowed; }

/* ── Card — flat, bordered, no shadow, matching the app's default card
   convention (Lot/Batch/Apps/Weather/Inputs). ─────────────────────────── */
.mf-card {
    border: 1px solid var(--border);
    border-radius: 6px;
    overflow: hidden;
    background: var(--surface);
}

/* ── List rows ─────────────────────────────────────────────────────────── */
.mf-list { display: flex; flex-direction: column; }
.mf-list-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: background .15s ease;
}
.mf-list-row:last-child { border-bottom: none; }
.mf-list-row:hover { background: var(--surface-muted); }
.mf-list-row__icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: var(--surface-muted);
    color: var(--text-2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 16px;
}
.mf-list-row__main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.mf-list-row__title { font-size: 14px; font-weight: 700; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mf-list-row__sub { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mf-list-row__stats { display: flex; align-items: center; gap: 20px; flex-shrink: 0; }
.mf-list-stat { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; min-width: 56px; }
.mf-list-stat--wide { min-width: 84px; }
.mf-list-stat__value { font-size: 13px; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
.mf-list-stat__label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); white-space: nowrap; }

/* ── Status badge ──────────────────────────────────────────────────────── */
.mf-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.mf-badge--good { background: var(--success-soft); color: var(--success); }
.mf-badge--neutral { background: var(--surface-elevated); color: var(--text-2); }

/* ── Row actions ───────────────────────────────────────────────────────── */
.mf-row-actions { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
.mf-act-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; text-decoration: none; border: none; background: transparent; cursor: pointer; transition: background .15s ease, color .15s ease; }
.mf-act-btn--view { color: var(--text-2); }
.mf-act-btn--view:hover { background: var(--surface-elevated); color: var(--text); }
.mf-act-btn--edit { color: var(--text-2); }
.mf-act-btn--edit:hover { background: var(--surface-elevated); color: var(--text); }
.mf-act-btn--delete { color: var(--text-2); }
.mf-act-btn--delete:hover { background: var(--error-soft); color: var(--error); }

/* ── Empty state ───────────────────────────────────────────────────────── */
.mf-empty { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 48px 20px; }
.mf-empty__icon { color: var(--text-muted); margin-bottom: 12px; }
.mf-empty__title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.mf-empty__text { font-size: 13px; color: var(--text-muted); margin: 0 0 16px; max-width: 360px; }
.mf-empty .mf-btn--primary { display: inline-flex; }

/* ── Modal — same header/body/footer structure and literal hex palette as
   every other modal in the app (AttachBatchModal, Apps' Create Agent
   dialog). NOTE: <el-dialog> teleports to <body>, outside .mf-page, so
   CSS custom properties don't cascade in — literal hex is used below. */
:deep(.el-dialog.fp-modal) {
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
:deep(.el-dialog.fp-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.fp-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.fp-modal .el-dialog__footer) { padding: 0; }

.fp-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.fp-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fp-modal__head-text { flex: 1; min-width: 0; }
.fp-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6F7677; margin-bottom: 1px; }
.fp-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }

.fp-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }

.fp-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.fp-field { display: flex; flex-direction: column; gap: 5px; }
.fp-field-input { width: 100%; }
.fp-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.fp-field__optional { font-weight: 400; color: #6F7677; }
.fp-field__error { font-size: 12px; font-weight: 500; color: #F85149; margin-top: 4px; display: block; }

:deep(.fp-field-input .el-input__wrapper),
:deep(.fp-field-input .el-textarea__inner),
:deep(.fp-field-input .el-select__wrapper) { box-shadow: 0 0 0 1px #E5E7EB inset; border-radius: 6px; background: #F5F6F7; }
.fp-field-input--error :deep(.el-input__wrapper),
.fp-field-input--error :deep(.el-textarea__inner),
.fp-field-input--error :deep(.el-select__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

/* Footer has no Cancel button — the single action sits right-aligned. */
.fp-modal__footer { display: flex; justify-content: flex-end; padding: 16px 24px; background: #F5F6F7; border-top: 1px solid #E5E7EB; }

@media (max-width: 767.98px) {
    .mf-list-row { flex-wrap: wrap; row-gap: 10px; }
    .mf-list-row__stats { order: 3; width: 100%; justify-content: space-between; }
    .mf-row-actions { order: 2; margin-left: auto; }
}

@media (max-width: 575.98px) {
    .mf-header { flex-direction: column; align-items: stretch; }
    .fp-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.fp-modal) { width: 92vw !important; }
}
</style>
