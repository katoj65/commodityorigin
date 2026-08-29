<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import UpdateBatchModal from '@/Components/Modals/UpdateBatchModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import AttachFarmCollectionModal from '@/Components/Modals/AttachFarmCollectionModal.vue';
import {
    ArrowDown, Box, Checked, Coffee, Coin, Delete, EditPen, Files,
    FolderOpened, HotWater, Link as LinkIcon, Location, Medal, OfficeBuilding, Operation, PriceTag, Plus, Ticket, User, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    batch: { type: Object, required: true },
    currencyOptions: { type: Array, default: () => [] },
});

/* ── Actions dropdown ──────────────────────────────────────────────────── */
const editDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const deleting = ref(false);
const attachModalOpen = ref(false);

function handleActionCommand(command) {
    if (command === 'edit') editDialogOpen.value = true;
    else if (command === 'delete') deleteDialogOpen.value = true;
}

function deleteBatch() {
    deleting.value = true;
    router.delete(route('batch.destroy', props.batch.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

/* ── Formatters ────────────────────────────────────────────────────────── */
function formatDate(value) {
    if (!value) return '—';
    return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });
}

function formatDateTime(value) {
    if (!value) return '—';
    return new Date(value.replace(' ', 'T')).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function formatMoney(amount, currency) {
    if (amount === null || amount === undefined) return '—';
    const value = Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${value}` : `$${value}`;
}

/* ── Computed ──────────────────────────────────────────────────────────── */
const batchTitle = computed(() => props.batch.variety || 'Batch');
const hasDefects = computed(() => Number(props.batch.defect_count || 0) > 0);
const defectsKnown = computed(() => props.batch.defect_count !== null && props.batch.defect_count !== undefined);
const cupScoreKnown = computed(() => props.batch.cup_score !== null && props.batch.cup_score !== undefined);

const recorderInitials = computed(() => {
    const parts = (props.batch.user?.name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return parts.length === 1 ? parts[0][0].toUpperCase() : (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

function collectionStatusTone(status) {
    if (['approved', 'paid', 'active', 'batched'].includes(status)) return 'good';
    if (['rejected', 'cancelled', 'expired'].includes(status)) return 'bad';
    return 'pending';
}

const deleteMessage = computed(() => `Are you sure you want to delete batch ${props.batch.batch_number}? This action cannot be undone.`);
</script>

<template>
    <DesignPreviewLayout title="Batch">
        <div class="btp-page">
            <div class="btp-page-head">
                <div class="btp-page-head__text">
                    <h1 class="btp-page-title">Batch</h1>
                    <p class="btp-page-subtitle">Everything recorded about this batch, from processing and quality grading to the lots linked to it.</p>
                </div>
                <el-dropdown v-if="batch.can_manage" trigger="click" @command="handleActionCommand">
                    <button type="button" class="btp-btn-outline btp-actions-btn" :disabled="deleting">
                        Actions <el-icon class="btp-caret"><ArrowDown /></el-icon>
                    </button>
                    <template #dropdown>
                        <el-dropdown-menu class="btp-actions-menu">
                            <el-dropdown-item command="edit"><el-icon><EditPen /></el-icon> Edit</el-dropdown-item>
                            <el-dropdown-item command="delete" class="btp-actions-menu__danger"><el-icon><Delete /></el-icon> Delete</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>

            <!-- ── Identity strip ───────────────────────────────────────────── -->
            <div class="btp-hero">
                <div class="btp-hero__icon"><el-icon :size="22"><Coffee /></el-icon></div>
                <div class="btp-hero__text">
                    <div class="btp-hero__title-row">
                        <h1 class="btp-title">{{ batchTitle }}</h1>
                        <span class="btp-hero__code"><el-icon :size="11"><Ticket /></el-icon>{{ batch.batch_number }}</span>
                        <span v-if="batch.status" class="btp-pill btp-pill--status">{{ batch.status }}</span>
                    </div>
                    <p class="btp-subtitle">
                        Processed {{ formatDate(batch.processing_date) }}
                        <span v-if="batch.warehouse_location"> · {{ batch.warehouse_location }}</span>
                    </p>
                </div>
            </div>

            <!-- ── KPI section ───────────────────────────────────────────────── -->
            <div class="btp-stat-row">
                <div class="btp-stat-chip">
                    <div class="btp-stat-chip__icon btp-stat-chip__icon--a"><el-icon><Box /></el-icon></div>
                    <div class="btp-stat-chip__body">
                        <span class="btp-stat-chip__label">Net Weight</span>
                        <div class="btp-stat-chip__value">{{ Number(batch.net_weight_kg || 0).toLocaleString() }} <span class="btp-stat-chip__unit">kg</span></div>
                        <div class="btp-stat-chip__sub">{{ Number(batch.quantity_bags || 0).toLocaleString() }} bags</div>
                    </div>
                </div>
                <div class="btp-stat-divider" />
                <div class="btp-stat-chip">
                    <div class="btp-stat-chip__icon btp-stat-chip__icon--b"><el-icon><Coin /></el-icon></div>
                    <div class="btp-stat-chip__body">
                        <span class="btp-stat-chip__label">Price</span>
                        <div class="btp-stat-chip__value">{{ formatMoney(batch.price, batch.currency) }}</div>
                        <div class="btp-stat-chip__sub">Per unit</div>
                    </div>
                </div>
                <div class="btp-stat-divider" />
                <div class="btp-stat-chip">
                    <div class="btp-stat-chip__icon btp-stat-chip__icon--c"><el-icon><Medal /></el-icon></div>
                    <div class="btp-stat-chip__body">
                        <span class="btp-stat-chip__label">Cup Score</span>
                        <div class="btp-stat-chip__value">{{ batch.cup_score ?? '—' }} <span v-if="cupScoreKnown" class="btp-stat-chip__unit">/100</span></div>
                        <div class="btp-stat-chip__sub">{{ cupScoreKnown ? (batch.cup_score >= 80 ? 'Specialty grade' : 'Below specialty') : 'Not yet graded' }}</div>
                    </div>
                </div>
                <div class="btp-stat-divider" />
                <div class="btp-stat-chip">
                    <div class="btp-stat-chip__icon" :class="hasDefects ? 'btp-stat-chip__icon--warn' : 'btp-stat-chip__icon--good'"><el-icon><WarningFilled /></el-icon></div>
                    <div class="btp-stat-chip__body">
                        <span class="btp-stat-chip__label">Defects</span>
                        <div class="btp-stat-chip__value">{{ batch.defect_count ?? '—' }}</div>
                        <div class="btp-stat-chip__sub" :class="{ 'btp-stat-chip__sub--warn': hasDefects }">{{ defectsKnown ? (hasDefects ? 'Flagged for review' : 'Clean sample') : 'Not yet inspected' }}</div>
                    </div>
                </div>
            </div>

            <!-- ── Section tile grid ─────────────────────────────────────────── -->
            <div class="btp-grid">
                <section class="btp-tile">
                    <h2 class="btp-tile__title"><el-icon><Files /></el-icon> Overview</h2>
                    <dl class="btp-dl">
                        <div class="btp-dl__row"><dt>Batch Number</dt><dd>{{ batch.batch_number || '—' }}</dd></div>
                        <div class="btp-dl__row"><dt>Variety</dt><dd>{{ batch.variety || '—' }}</dd></div>
                        <div class="btp-dl__row"><dt><el-icon :size="13"><Location /></el-icon> Warehouse Location</dt><dd>{{ batch.warehouse_location || '—' }}</dd></div>
                        <div class="btp-dl__row"><dt>Processing Date</dt><dd>{{ formatDate(batch.processing_date) }}</dd></div>
                        <div class="btp-dl__row"><dt>Status</dt><dd class="btp-dl__capitalize">{{ batch.status || '—' }}</dd></div>
                    </dl>
                </section>

                <section class="btp-tile">
                    <h2 class="btp-tile__title"><el-icon><Operation /></el-icon> Processing</h2>
                    <dl class="btp-dl">
                        <div class="btp-dl__row"><dt>Processing Method</dt><dd>{{ batch.processing_method || '—' }}</dd></div>
                        <div class="btp-dl__row"><dt><el-icon :size="13"><HotWater /></el-icon> Drying Method</dt><dd>{{ batch.drying_method || '—' }}</dd></div>
                        <div class="btp-dl__row"><dt>Drying Duration</dt><dd>{{ batch.drying_duration !== null && batch.drying_duration !== undefined ? `${batch.drying_duration} days` : '—' }}</dd></div>
                        <div class="btp-dl__row"><dt>Milling Status</dt><dd>{{ batch.milling_status || '—' }}</dd></div>
                    </dl>
                    <template v-if="batch.notes">
                        <h3 class="btp-tile__subtitle btp-tile__subtitle--mt">Notes</h3>
                        <p class="btp-notes">{{ batch.notes }}</p>
                    </template>
                </section>

                <section class="btp-tile">
                    <h2 class="btp-tile__title"><el-icon><WarningFilled /></el-icon> Quality Graded</h2>
                    <dl class="btp-dl">
                        <div class="btp-dl__row"><dt><el-icon :size="13"><HotWater /></el-icon> Moisture Content</dt><dd>{{ batch.moisture_content !== null && batch.moisture_content !== undefined ? `${batch.moisture_content}%` : '—' }}</dd></div>
                        <div class="btp-dl__row"><dt>Cup Score</dt><dd>{{ batch.cup_score ?? '—' }}</dd></div>
                        <div class="btp-dl__row"><dt>Defect Count</dt><dd>{{ batch.defect_count ?? '—' }}</dd></div>
                        <div class="btp-dl__row"><dt>Screen Size</dt><dd>{{ batch.screen_size || '—' }}</dd></div>
                    </dl>
                </section>

                <section class="btp-tile btp-tile--half">
                    <div class="btp-tile__head">
                        <h2 class="btp-tile__title"><el-icon><OfficeBuilding /></el-icon> Sourced</h2>
                        <button v-if="batch.can_manage" type="button" class="btp-btn-outline" @click="attachModalOpen = true">
                            <el-icon :size="14"><Plus /></el-icon> Attach
                        </button>
                    </div>

                    <h3 class="btp-tile__subtitle">Farm Collections</h3>
                    <div v-if="batch.farm_collection_links && batch.farm_collection_links.length" class="btp-list">
                        <Link
                            v-for="link in batch.farm_collection_links"
                            :key="link.id"
                            :href="route('farm-collection.show', link.farm_collection_id)"
                            class="btp-list-row"
                        >
                            <div class="btp-list-row__icon"><el-icon><OfficeBuilding /></el-icon></div>
                            <div class="btp-list-row__main">
                                <div class="btp-list-row__title">{{ link.farm_collection?.farm?.name || `Collection ${link.farm_collection_code}` }}</div>
                                <div class="btp-list-row__sub">
                                    {{ link.farm_collection?.coffee_type || '—' }}<span v-if="link.farm_collection?.variety"> · {{ link.farm_collection.variety }}</span>
                                    <span> · <span class="btp-list-row__code"><el-icon :size="10"><PriceTag /></el-icon>{{ link.farm_collection_code }}</span></span>
                                </div>
                            </div>
                            <div class="btp-list-row__stats">
                                <div class="btp-list-stat">
                                    <span class="btp-list-stat__value">{{ link.farm_collection?.quantity ? `${Number(link.farm_collection.quantity).toLocaleString()} ${link.farm_collection.unit || ''}` : '—' }}</span>
                                    <span class="btp-list-stat__label">Qty</span>
                                </div>
                                <span class="btp-pill" :class="`btp-pill--${collectionStatusTone(link.farm_collection?.status)}`">{{ link.farm_collection?.status || link.status }}</span>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="btp-collection-empty">
                        <div class="btp-collection-empty__icon"><el-icon :size="18"><FolderOpened /></el-icon></div>
                        <p class="btp-collection-empty__text">No farm collections linked yet.</p>
                    </div>
                </section>

                <section v-if="batch.lots && batch.lots.length" class="btp-tile btp-tile--half">
                    <h2 class="btp-tile__title"><el-icon><Ticket /></el-icon> Lots Produced</h2>
                    <div class="btp-list">
                        <Link v-for="lot in batch.lots" :key="lot.id" :href="route('lot.show', lot.id)" class="btp-list-row">
                            <div class="btp-list-row__icon"><el-icon><Ticket /></el-icon></div>
                            <div class="btp-list-row__main">
                                <div class="btp-list-row__title">{{ lot.lot_name || lot.lot_number || `Lot #${lot.id}` }}</div>
                                <div class="btp-list-row__sub">{{ lot.process || '—' }}<span v-if="lot.grade"> · {{ lot.grade }}</span></div>
                            </div>
                            <div class="btp-list-row__stats">
                                <div class="btp-list-stat">
                                    <span class="btp-list-stat__value">{{ lot.net_weight_kg ? `${Number(lot.net_weight_kg).toLocaleString()} kg` : '—' }}</span>
                                    <span class="btp-list-stat__label">Weight</span>
                                </div>
                                <span class="btp-pill btp-pill--grade">{{ lot.status || '—' }}</span>
                            </div>
                        </Link>
                    </div>
                </section>

                <section class="btp-tile btp-tile--wide btp-tile--footer">
                    <div class="btp-recorder">
                        <h2 class="btp-tile__title"><el-icon><User /></el-icon> Recorded By</h2>
                        <div class="btp-recorder__row">
                            <div class="btp-recorder__avatar">{{ recorderInitials }}</div>
                            <div class="btp-recorder__body">
                                <div class="btp-recorder__name">{{ batch.user?.name || 'Unknown' }}</div>
                                <div class="btp-recorder__meta"><el-icon :size="12"><Checked /></el-icon> {{ formatDateTime(batch.created_at) }}</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <UpdateBatchModal
            v-if="batch.can_manage"
            v-model="editDialogOpen"
            :batch="batch"
            :currency-options="currencyOptions"
        />

        <ConfirmDialog
            v-model="deleteDialogOpen"
            eyebrow="Batch"
            title="Delete Batch"
            :message="deleteMessage"
            confirm-text="Delete Batch"
            :auto-close="false"
            :loading="deleting"
            @confirm="deleteBatch"
        />

        <AttachFarmCollectionModal
            v-if="batch.can_manage"
            v-model="attachModalOpen"
            :batch-id="batch.id"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
.btp-page {
    --primary: #000000;
    --primary-container: #262626;
    --on-primary-container: #F1F2F3;
    --secondary-container: #E5FAE7;
    --on-secondary-container: #2F6B35;
    --error: #F85149;
    --error-container: #FEEDED;
    --on-error-container: #C6413A;
    --surface-container-lowest: #ffffff;
    --surface-container-low: #F5F6F7;
    --surface-container: #F1F2F3;
    --on-surface: #121516;
    --on-surface-variant: #4B5457;
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-family: var(--sans);
    color: var(--on-surface);
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.btp-page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }
.btp-page-head__text { display: flex; flex-direction: column; gap: 6px; }
.btp-page-title { font-size: 1.5rem; line-height: 1.9rem; letter-spacing: -0.015em; font-weight: 800; color: var(--primary); margin: 0; }
.btp-page-subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 400; color: var(--on-surface-variant); margin: 0; max-width: 640px; }

.btp-actions-btn { flex-shrink: 0; }
.btp-actions-btn:disabled { opacity: .6; cursor: default; }
.btp-caret { font-size: 11px; margin-left: -2px; }

.btp-hero {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 24px;
}
.btp-hero__icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: var(--surface-container-low);
    color: var(--on-surface-variant);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.btp-hero__text { flex: 1; min-width: 0; }
.btp-hero__title-row { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin: 0 0 4px; }
.btp-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -0.012em; color: var(--primary); margin: 0; }
.btp-hero__code {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 999px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
    font-size: 11px;
    font-weight: 700;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    letter-spacing: .01em;
}
.btp-subtitle { font-size: .9375rem; color: var(--on-surface-variant); margin: 0; }

.btp-pill {
    display: inline-flex;
    align-items: center;
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    text-transform: capitalize;
}
.btp-pill--status { background: var(--secondary-container); color: var(--on-secondary-container); }
.btp-pill--grade { background: var(--surface-container); color: var(--on-surface-variant); border: 1px solid color-mix(in srgb, var(--card-border) 80%, transparent); }
.btp-pill--good { background: var(--secondary-container); color: var(--on-secondary-container); }
.btp-pill--bad { background: var(--error-container); color: var(--on-error-container); }
.btp-pill--pending { background: #fef3c7; color: #92400e; }

/* ── KPI section ─────────────────────────────────────────────────────── */
.btp-stat-row {
    display: flex;
    align-items: stretch;
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 18px 24px;
}
.btp-stat-chip { display: flex; align-items: center; gap: 14px; flex: 1 1 0; min-width: 0; }
.btp-stat-divider { width: 1px; align-self: stretch; margin: 0 20px; background: var(--card-border); flex-shrink: 0; }
.btp-stat-chip__body { min-width: 0; display: flex; flex-direction: column; }
.btp-stat-chip__icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 16px;
}
.btp-stat-chip__icon--a { background: color-mix(in srgb, var(--primary) 7%, var(--surface-container-lowest)); color: var(--primary); }
.btp-stat-chip__icon--b { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.btp-stat-chip__icon--c { background: #EEF2FF; color: #4338CA; }
.btp-stat-chip__icon--good { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.btp-stat-chip__icon--warn { background: #fef3c7; color: #92400e; }
.btp-stat-chip__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-variant); margin-bottom: 2px; }
.btp-stat-chip__value { font-size: 18px; font-weight: 800; letter-spacing: -0.01em; color: var(--on-surface); font-variant-numeric: tabular-nums; line-height: 1.25; }
.btp-stat-chip__unit { font-size: 12px; font-weight: 600; color: var(--on-surface-variant); }
.btp-stat-chip__sub { font-size: 11.5px; color: var(--on-surface-variant); margin-top: 1px; }
.btp-stat-chip__sub--warn { color: #92400e; font-weight: 600; }

/* ── Section tile grid ─────────────────────────────────────────────────── */
.btp-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 16px;
    align-items: stretch;
}
.btp-tile {
    grid-column: span 2;
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px 24px;
}
.btp-tile--wide { grid-column: 1 / -1; }
.btp-tile--half { grid-column: span 3; }
.btp-tile--footer { display: flex; }
.btp-tile__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 4px; }
.btp-tile__head .btp-tile__title { margin: 0; }
.btp-tile__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--on-surface-variant);
    margin: 0 0 14px;
}
.btp-tile__subtitle {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--on-surface-variant);
    margin: 0 0 10px;
}
.btp-tile__subtitle--mt { margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--card-border); }

.btp-dl { margin: 0; display: flex; flex-direction: column; }
.btp-dl__row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px dashed var(--card-border);
    font-size: 13.5px;
}
.btp-dl__row:last-child { border-bottom: none; padding-bottom: 0; }
.btp-dl__row dt { display: inline-flex; align-items: center; gap: 5px; color: var(--on-surface-variant); }
.btp-dl__row dt .el-icon { color: var(--on-surface-variant); }
.btp-dl__row dd { margin: 0; font-weight: 600; color: var(--on-surface); text-align: right; }
.btp-dl__capitalize { text-transform: capitalize; }

.btp-notes { font-size: 13.5px; line-height: 1.6; color: var(--on-surface); margin: 0; white-space: pre-wrap; }

/* ── Related-record list rows (Farm Collections/Lots) ─────────────────── */
.btp-list { display: flex; flex-direction: column; }
.btp-list-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 4px;
    border-bottom: 1px solid var(--card-border);
    text-decoration: none;
    color: inherit;
    transition: background .15s ease;
}
.btp-list-row:last-child { border-bottom: none; }
a.btp-list-row:hover { background: color-mix(in srgb, var(--surface-container-low) 60%, transparent); margin: 0 -12px; padding: 14px 16px; border-radius: 10px; cursor: pointer; }
.btp-list-row--static { cursor: default; }
.btp-list-row__icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--surface-container-low);
    color: var(--on-surface-variant);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
}
.btp-list-row__main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.btp-list-row__title { font-size: 13.5px; font-weight: 700; color: var(--on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.btp-list-row__sub { font-size: 12px; color: var(--on-surface-variant); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.btp-list-row__code { display: inline-flex; align-items: center; gap: 3px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 11px; }
.btp-list-row__stats { display: flex; align-items: center; gap: 16px; flex-shrink: 0; }
.btp-list-stat { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; min-width: 70px; }
.btp-list-stat__value { font-size: 13px; font-weight: 700; color: var(--on-surface); font-variant-numeric: tabular-nums; white-space: nowrap; }
.btp-list-stat__label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-variant); white-space: nowrap; }

/* ── Footer tile: recorder ─────────────────────────────────────────────── */
.btp-recorder { flex: 1; min-width: 0; }
.btp-recorder__row { display: flex; align-items: center; gap: 12px; }
.btp-recorder__avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #3a3a3a);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .01em;
}
.btp-recorder__body { min-width: 0; }
.btp-recorder__name { font-size: 14px; font-weight: 700; color: var(--on-surface); }
.btp-recorder__meta { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--on-surface-variant); margin-top: 3px; }

.btp-collection-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 20px 0 4px; }
.btp-collection-empty__icon {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
    display: flex;
    align-items: center;
    justify-content: center;
}
.btp-collection-empty__text { font-size: 12.5px; color: var(--on-surface-variant); margin: 0; text-align: center; }

.btp-btn-outline {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 36px;
    padding: 0 16px;
    border: none;
    border-radius: 6px;
    background: var(--surface-container);
    color: var(--on-surface);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s ease;
}
.btp-btn-outline:hover:not(:disabled) { background: color-mix(in srgb, var(--card-border) 60%, transparent); }

@media (max-width: 900px) {
    .btp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .btp-tile { grid-column: span 1; }
    .btp-tile--half { grid-column: span 1; }
    .btp-stat-row { flex-wrap: wrap; row-gap: 18px; }
    .btp-stat-chip { flex: 1 1 45%; }
    .btp-stat-divider { display: none; }
}

@media (max-width: 640px) {
    .btp-page-title { font-size: 1.25rem; line-height: 1.6rem; }
    .btp-hero { flex-direction: column; align-items: flex-start; }
    .btp-stat-row { flex-direction: column; }
    .btp-stat-chip { flex: 1 1 auto; }
    .btp-grid { grid-template-columns: 1fr; }
    .btp-tile__head { flex-direction: column; align-items: stretch; }
}

@media (max-width: 575.98px) {
    .btp-page-head { flex-direction: column; align-items: stretch; }
}
</style>

<style>
/* Dropdown teleports to <body>, outside scoped styles — literal hex
   from the same UI.md palette, matching every other page's actions menu. */
.btp-actions-menu.el-dropdown-menu { border-radius: 6px; border: 1px solid #E5E7EB; padding: 4px; }
.btp-actions-menu .el-dropdown-menu__item {
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #121516;
    padding: 8px 12px;
}
.btp-actions-menu .el-dropdown-menu__item:hover { background: #F5F6F7; color: #121516; }
.btp-actions-menu .btp-actions-menu__danger { color: #F85149; }
.btp-actions-menu .btp-actions-menu__danger:hover { background: #FEEDED; color: #C6413A; }
</style>
