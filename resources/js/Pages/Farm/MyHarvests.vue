<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import InputError from '@/Components/InputError.vue';
import AddFarmHarvestDialog from '@/Components/Modals/AddFarmHarvestDialog.vue';
import HarvestProfileDialog from '@/Components/Modals/HarvestProfileDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Box, House, Collection, Calendar, TrendCharts, Clock,
    View, Edit, Delete, CircleCheck, WarningFilled, Plus,
} from '@element-plus/icons-vue';

const props = defineProps({
    harvests: { type: Array, default: () => [] },
    pickMethodOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    isAdmin: { type: Boolean, default: false },
    farmOptions: { type: Array, default: () => [] },
    varietyOptions: { type: Array, default: () => [] },
});

const addHarvestOpen = ref(false);

/* ── Row presentation helpers ─────────────────────────────────────── */
const avatarPalette = [
    { bg: '#eef2ff', color: '#4338ca' },
    { bg: '#ecfdf5', color: '#047857' },
    { bg: '#fff7ed', color: '#c2410c' },
    { bg: '#fdf4ff', color: '#a21caf' },
    { bg: '#eff6ff', color: '#1d4ed8' },
    { bg: '#f0fdfa', color: '#0f766e' },
];

function initials(name) {
    const parts = (name || '').trim().split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || 'F';
}

function avatarStyle(row) {
    const swatch = avatarPalette[(row.farm_id ?? row.id) % avatarPalette.length];
    return { background: swatch.bg, color: swatch.color };
}

function farmName(row) {
    return row.farm?.name || `Farm #${row.farm_id}`;
}

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(value.length === 10 ? `${value}T00:00:00` : value.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function recordedSubtitle(row) {
    return props.isAdmin ? (row.creator?.name || 'Unknown') : 'Recorded';
}

function qualityFlagCount(row) {
    return [row.foreign_matter_present, row.pest_damage, row.disease_signs, row.visible_defects].filter(Boolean).length;
}
function qualitySummary(row) {
    const count = qualityFlagCount(row);
    return count === 0 ? 'Clean' : `${count} flag${count > 1 ? 's' : ''}`;
}
function qualityTagType(row) {
    return qualityFlagCount(row) === 0 ? 'success' : 'warning';
}
function qualityTagIcon(row) {
    return qualityFlagCount(row) === 0 ? CircleCheck : WarningFilled;
}

function disableFutureDates(date) {
    const today = new Date();
    today.setHours(23, 59, 59, 999);
    return date.getTime() > today.getTime();
}

/* ── Edit harvest ─────────────────────────────────────────────────── */
const editDialogOpen = ref(false);
const editingHarvestId = ref(null);

const editForm = useForm({
    harvest_date: '',
    harvest_season: '',
    pick_method: '',
    price: 0,
    weight: 0,
    ripeness_percentage: 0,
    foreign_matter_present: false,
    pest_damage: false,
    disease_signs: false,
    visible_defects: false,
});

function openEditDialog(row) {
    editingHarvestId.value = row.id;
    editForm.clearErrors();
    editForm.harvest_date = row.harvest_date;
    editForm.harvest_season = row.harvest_season;
    editForm.pick_method = row.pick_method;
    editForm.price = row.price !== null ? Number(row.price) : 0;
    editForm.weight = row.weight !== null ? Number(row.weight) : 0;
    editForm.ripeness_percentage = row.ripeness_percentage !== null ? Number(row.ripeness_percentage) : 0;
    editForm.foreign_matter_present = row.foreign_matter_present;
    editForm.pest_damage = row.pest_damage;
    editForm.disease_signs = row.disease_signs;
    editForm.visible_defects = row.visible_defects;
    editDialogOpen.value = true;
}

function submitEditHarvest() {
    editForm.patch(route('farm.harvest.update', editingHarvestId.value), {
        preserveScroll: true,
        onSuccess: () => { editDialogOpen.value = false; },
    });
}

/* ── Delete harvest ───────────────────────────────────────────────── */
const deleteDialogOpen = ref(false);
const deletingHarvest = ref(false);
const harvestToDelete = ref(null);

function openDeleteDialog(row) {
    harvestToDelete.value = row;
    deleteDialogOpen.value = true;
}

function deleteHarvest() {
    if (!harvestToDelete.value) return;
    deletingHarvest.value = true;
    router.delete(route('farm.harvest.destroy', harvestToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deletingHarvest.value = false;
            deleteDialogOpen.value = false;
            harvestToDelete.value = null;
        },
    });
}

/* ── View harvest ──────────────────────────────────────────────────── */
const viewHarvestDialogOpen = ref(false);
const harvestToView = ref(null);

function openViewHarvestDialog(row) {
    harvestToView.value = props.harvests.find((h) => String(h.id) === String(row.id)) || null;
    viewHarvestDialogOpen.value = true;
}
</script>

<template>
    <DesignPreviewLayout title="My Harvests">
        <Head title="My Harvests" />

        <div class="mh-page">

            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="mh-header">
                <div class="mh-header__inner">
                    <div>
                        <div class="mh-kicker">Farm Workspace</div>
                        <h1 class="mh-title mb-0">My Harvests</h1>
                        <p class="mh-subtitle mb-0">
                            {{ isAdmin ? 'Every harvest recorded across all farms.' : 'Harvests recorded across the farms you created.' }}
                        </p>
                    </div>
                    <button v-if="farmOptions.length" type="button" class="mh-btn-primary" @click="addHarvestOpen = true">
                        <el-icon><Plus /></el-icon> Add Harvest
                    </button>
                </div>
            </div>

            <!-- ── Table ─────────────────────────────────────────────────── -->
            <div class="mh-card">
            <el-table
                :data="harvests"
                class="mh-table"
                row-key="id"
                @row-click="openViewHarvestDialog"
            >
                <el-table-column min-width="220">
                    <template #header><span class="mh-th"><el-icon><House /></el-icon> Farm</span></template>
                    <template #default="{ row }">
                        <div class="mh-cell-farm">
                            <div class="mh-avatar" :style="avatarStyle(row)">{{ initials(farmName(row)) }}</div>
                            <div class="mh-cell-farm__text">
                                <span class="mh-cell-name">{{ farmName(row) }}</span>
                                <span class="mh-cell-sub">{{ row.farm?.location || 'Location not set' }}</span>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column min-width="130">
                    <template #header><span class="mh-th"><el-icon><Collection /></el-icon> Variety</span></template>
                    <template #default="{ row }">
                        <el-tag v-if="row.variety" type="success" effect="plain" size="small" round>{{ row.variety }}</el-tag>
                        <span v-else class="mh-cell-sub">—</span>
                    </template>
                </el-table-column>
                <el-table-column min-width="150">
                    <template #header><span class="mh-th"><el-icon><Calendar /></el-icon> Harvest Date</span></template>
                    <template #default="{ row }">
                        <div class="mh-cell-specs">
                            <span>{{ formatDate(row.harvest_date) }}</span>
                            <span class="mh-cell-sub">{{ row.harvest_season || 'Season not set' }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column min-width="140">
                    <template #header><span class="mh-th"><el-icon><Box /></el-icon> Yield</span></template>
                    <template #default="{ row }">
                        <div class="mh-cell-specs">
                            <span class="mh-num">{{ row.weight !== null ? `${Number(row.weight).toLocaleString()} kg` : '—' }}</span>
                            <span class="mh-cell-sub mh-num">{{ row.price !== null ? `$${Number(row.price).toFixed(2)}/kg` : 'Price not set' }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column min-width="130" align="center">
                    <template #header><span class="mh-th mh-th--center"><el-icon><TrendCharts /></el-icon> Quality</span></template>
                    <template #default="{ row }">
                        <div class="mh-cell-specs mh-cell-specs--center">
                            <span class="mh-count-pill mh-num">{{ row.ripeness_percentage !== null ? `${row.ripeness_percentage}%` : '—' }}</span>
                            <el-tag :type="qualityTagType(row)" effect="plain" size="small" round class="mh-quality-tag">
                                <el-icon><component :is="qualityTagIcon(row)" /></el-icon>
                                {{ qualitySummary(row) }}
                            </el-tag>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column min-width="160">
                    <template #header><span class="mh-th"><el-icon><Clock /></el-icon> Recorded</span></template>
                    <template #default="{ row }">
                        <div class="mh-cell-specs">
                            <span>{{ formatDate(row.created_at) }}</span>
                            <span class="mh-cell-sub">{{ recordedSubtitle(row) }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="" min-width="120" align="right">
                    <template #default="{ row }">
                        <div class="mh-row-actions">
                            <el-tooltip content="View" placement="top">
                                <button type="button" class="mh-act-btn mh-act-btn--view" @click.stop="openViewHarvestDialog(row)">
                                    <el-icon><View /></el-icon>
                                </button>
                            </el-tooltip>
                            <el-tooltip content="Edit" placement="top">
                                <button type="button" class="mh-act-btn mh-act-btn--edit" @click.stop="openEditDialog(row)">
                                    <el-icon><Edit /></el-icon>
                                </button>
                            </el-tooltip>
                            <el-tooltip content="Delete" placement="top">
                                <button type="button" class="mh-act-btn mh-act-btn--delete" @click.stop="openDeleteDialog(row)">
                                    <el-icon><Delete /></el-icon>
                                </button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-table-column>

                <template #empty>
                    <div class="mh-empty">
                        <div class="mh-empty__icon"><el-icon :size="24"><Box /></el-icon></div>
                        <div class="mh-empty__title">No harvests recorded yet</div>
                        <p class="mh-empty__text">Record a harvest from a farm profile to see it listed here.</p>
                    </div>
                </template>
            </el-table>
            </div>

            <!-- ── Edit Harvest modal — borrows the fp-modal design language ── -->
            <el-dialog v-model="editDialogOpen" width="600px" align-center class="fp-modal">
                <template #header>
                    <div class="fp-modal__head">
                        <div class="fp-modal__head-icon"><el-icon :size="18"><Edit /></el-icon></div>
                        <div class="fp-modal__head-text">
                            <div class="fp-modal__eyebrow">Farm Workspace</div>
                            <div class="fp-modal__title">Edit Harvest</div>
                        </div>
                    </div>
                </template>

                <form id="edit-harvest-form" novalidate class="fp-modal__body" @submit.prevent="submitEditHarvest">
                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Harvest Season</label>
                            <el-select v-model="editForm.harvest_season" placeholder="Select season" class="fp-field-input w-100" :class="{ 'fp-field-input--error': editForm.errors.harvest_season }">
                                <el-option v-for="option in harvestSeasonOptions" :key="option" :label="option" :value="option" />
                            </el-select>
                            <InputError class="fp-field__error" :message="editForm.errors.harvest_season" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Harvest Date</label>
                            <el-date-picker v-model="editForm.harvest_date" type="date" value-format="YYYY-MM-DD" placeholder="Select date" :disabled-date="disableFutureDates" class="fp-field-input w-100" :class="{ 'fp-field-input--error': editForm.errors.harvest_date }" />
                            <InputError class="fp-field__error fp-field__error--spaced" :message="editForm.errors.harvest_date" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Pick Method</label>
                            <el-select v-model="editForm.pick_method" placeholder="Select pick method" class="fp-field-input w-100" :class="{ 'fp-field-input--error': editForm.errors.pick_method }">
                                <el-option v-for="option in pickMethodOptions" :key="option" :label="option" :value="option" />
                            </el-select>
                            <InputError class="fp-field__error" :message="editForm.errors.pick_method" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Ripeness %</label>
                            <el-input-number v-model="editForm.ripeness_percentage" :min="0" :max="100" class="fp-field-input w-100" :class="{ 'fp-field-input--error': editForm.errors.ripeness_percentage }" />
                            <InputError class="fp-field__error" :message="editForm.errors.ripeness_percentage" />
                        </div>
                    </div>

                    <div class="fp-field-row">
                        <div class="fp-field">
                            <label class="fp-field__label">Weight (kg)</label>
                            <el-input-number v-model="editForm.weight" :min="0.01" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': editForm.errors.weight }" />
                            <InputError class="fp-field__error" :message="editForm.errors.weight" />
                        </div>
                        <div class="fp-field">
                            <label class="fp-field__label">Price (per kg)</label>
                            <el-input-number v-model="editForm.price" :min="0.01" :precision="2" class="fp-field-input w-100" :class="{ 'fp-field-input--error': editForm.errors.price }" />
                            <InputError class="fp-field__error" :message="editForm.errors.price" />
                        </div>
                    </div>

                    <div class="fp-harvest-flags">
                        <div class="fp-harvest-flag">
                            <span>Foreign matter present</span>
                            <el-switch v-model="editForm.foreign_matter_present" />
                        </div>
                        <div class="fp-harvest-flag">
                            <span>Pest damage</span>
                            <el-switch v-model="editForm.pest_damage" />
                        </div>
                        <div class="fp-harvest-flag">
                            <span>Disease signs</span>
                            <el-switch v-model="editForm.disease_signs" />
                        </div>
                        <div class="fp-harvest-flag">
                            <span>Visible defects</span>
                            <el-switch v-model="editForm.visible_defects" />
                        </div>
                    </div>
                </form>

                <template #footer>
                    <div class="fp-modal__footer">
                        <button type="submit" form="edit-harvest-form" class="mh-btn-primary" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </el-dialog>

            <!-- ── Delete Harvest modal — borrows the fp-modal design language ── -->
            <ConfirmDialog
                v-model="deleteDialogOpen"
                eyebrow="Farm Workspace"
                title="Delete Harvest"
                :message="harvestToDelete ? `Are you sure you want to delete the harvest recorded on ${formatDate(harvestToDelete.harvest_date)} for ${farmName(harvestToDelete)}? This action cannot be undone.` : ''"
                confirm-text="Delete Harvest"
                :auto-close="false"
                :loading="deletingHarvest"
                @confirm="deleteHarvest"
            />

        </div>

        <HarvestProfileDialog
            v-model="viewHarvestDialogOpen"
            :harvest="harvestToView"
            :is-admin="isAdmin"
        />

        <AddFarmHarvestDialog
            v-if="farmOptions.length"
            v-model="addHarvestOpen"
            :farm-options="farmOptions"
            :variety-options="varietyOptions"
            :pick-method-options="pickMethodOptions"
            :harvest-season-options="harvestSeasonOptions"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
.mh-page {
    --green: #004532;
    --green-dark: #002e20;
    --red: #dc2626;
    --red-dark: #b91c1c;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-white: #ffffff;
    --surface-low: #f8fafc;
    --surface-high: #eef2f0;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
    line-height: 1.5;
}

/* ── Header ────────────────────────────────────────────────────────────── */
.mh-header { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.mh-header__inner { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: 1rem clamp(1rem, 3vw, 2rem); }
.mh-kicker { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 4px; line-height: 1.4; }
.mh-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.25; }
.mh-subtitle { font-size: .8125rem; color: var(--on-surface-var); margin-top: 2px; line-height: 1.5; }

/* ── Buttons ───────────────────────────────────────────────────────────────
   NOTE: literal hex values on purpose, not var(--green)/var(--red) — these
   classes are also used inside <el-dialog>, which teleports its content to
   <body>, outside .mh-page's CSS custom-property cascade. */
.mh-btn-primary { background: #004532; border: none; color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: background .15s ease; }
.mh-btn-primary:hover { background: #002e20; }
.mh-btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.mh-btn-danger { background: #dc2626; border: none; color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 16px; cursor: pointer; transition: background .15s ease; }
.mh-btn-danger:hover { background: #b91c1c; }
.mh-btn-danger:disabled { opacity: .6; cursor: not-allowed; }

/* ── Card — boxes the table exactly like .mkt-card on the market listing
   page: floating, elevated, rounded, instead of an edge-to-edge table
   sitting flat on the page background. ───────────────────────────────── */
.mh-card {
    margin: 1.25rem clamp(1rem, 3vw, 2rem) 0;
    border: 1px solid var(--surface-high);
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03), 0 12px 28px -18px rgba(15, 23, 42, .14);
}

/* ── Table ─────────────────────────────────────────────────────────────── */
.mh-table { width: 100%; }
.mh-table :deep(.el-table__inner-wrapper::before) { display: none; }
.mh-table :deep(.el-table__row) { cursor: pointer; }
.mh-table :deep(.el-table__header th.el-table__cell) {
    background: #fafbfc;
    border-bottom: 1px solid var(--surface-high);
    padding: 10px 0;
}
.mh-table :deep(.el-table__header th.el-table__cell > .cell) {
    font-size: .8125rem;
    font-weight: 600;
    color: #374151;
    line-height: 1.3;
}
.mh-table :deep(td.el-table__cell) { font-size: .8125rem; color: var(--on-surface); padding: 7px 0; }
.mh-table :deep(.el-table__cell:first-child .cell) { padding-left: 1.25rem; }
.mh-table :deep(.el-table__cell:last-child .cell) { padding-right: 1.25rem; }
.mh-table :deep(.el-table__row:hover .el-table__cell) { background: var(--surface-low); }

/* ── Table header icons ───────────────────────────────────────────────── */
.mh-th { display: inline-flex; align-items: center; gap: 6px; }
.mh-th :deep(.el-icon) { font-size: 14px; color: #9ca3af; }
.mh-th--center { justify-content: center; }

/* ── Table cells ───────────────────────────────────────────────────────── */
.mh-cell-farm { display: flex; align-items: center; gap: 8px; }
.mh-cell-farm__text { display: flex; flex-direction: column; gap: 0; min-width: 0; }
.mh-avatar { width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .625rem; font-weight: 800; flex-shrink: 0; }
.mh-cell-name { font-weight: 700; line-height: 1.3; }
.mh-cell-sub { font-size: .6875rem; color: var(--on-surface-var); line-height: 1.3; }
.mh-cell-specs { display: flex; flex-direction: column; gap: 0; }
.mh-cell-specs--center { align-items: center; }
.mh-count-pill { display: inline-flex; min-width: 26px; justify-content: center; background: var(--surface-low); border-radius: 999px; padding: 2px 9px; font-weight: 700; font-size: .75rem; }
.mh-num { font-variant-numeric: tabular-nums; }
.mh-quality-tag { display: inline-flex; align-items: center; gap: 4px; margin-top: 2px; }
.mh-quality-tag :deep(.el-icon) { font-size: 12px; }

/* ── Row actions ───────────────────────────────────────────────────────── */
.mh-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; }
.mh-act-btn { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 7px; text-decoration: none; border: none; background: transparent; cursor: pointer; transition: background .15s ease; }
.mh-act-btn--view { color: var(--green); }
.mh-act-btn--view:hover { background: rgba(0, 69, 50, .08); }
.mh-act-btn--edit { color: var(--on-surface-var); }
.mh-act-btn--edit:hover { background: var(--surface-low); color: var(--on-surface); }
.mh-act-btn--delete { color: var(--red); }
.mh-act-btn--delete:hover { background: #fef2f2; color: var(--red-dark); }

/* ── Empty state ───────────────────────────────────────────────────────── */
.mh-empty { text-align: center; padding: 3rem 1rem; }
.mh-empty__icon { width: 52px; height: 52px; border-radius: 50%; background: var(--surface-low); color: var(--on-surface-var); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.mh-empty__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
.mh-empty__text { font-size: .8125rem; color: var(--on-surface-var); margin-bottom: 16px; max-width: 360px; margin-left: auto; margin-right: auto; line-height: 1.5; }

/* ── Modal — borrowed verbatim from FarmProfile.vue's fp-modal system ────
   NOTE: <el-dialog> teleports to <body>, outside .mh-page, so CSS custom
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
.fp-field-row--spaced { margin-bottom: 10px; }
.fp-field { display: flex; flex-direction: column; gap: 5px; }
.fp-field__label { font-size: .75rem; font-weight: 600; color: #374151; }
.fp-field__error { font-size: .75rem; font-weight: 600; color: #dc2626; margin-top: 4px; display: block; }
.fp-field__error--spaced { margin-top: 8px; }

.fp-harvest-flags { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 14px; padding: 12px; background: #f9fafb; border-radius: 10px; border: 1px solid #f3f4f6; }
.fp-harvest-flag { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: .8125rem; font-weight: 600; color: #374151; }

:deep(.fp-field-input .el-input__wrapper),
:deep(.fp-field-input .el-textarea__inner) { box-shadow: 0 0 0 1px #d1d5db inset; border-radius: 8px; }
.fp-field-input--error :deep(.el-input__wrapper),
.fp-field-input--error :deep(.el-textarea__inner),
.fp-field-input--error :deep(.el-select__wrapper),
.fp-field-input--error.el-date-editor,
.fp-field-input--error:deep(.el-date-editor) { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

/* Footer has no Cancel button — the single action sits right-aligned. */
.fp-modal__footer { display: flex; justify-content: flex-end; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

@media (max-width: 575.98px) {
    .fp-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.fp-modal) { width: 92vw !important; }
}
</style>
