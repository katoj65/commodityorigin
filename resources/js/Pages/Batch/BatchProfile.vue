<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import UpdateBatchModal from '@/Components/Modals/UpdateBatchModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import AttachFarmCollectionModal from '@/Components/Modals/AttachFarmCollectionModal.vue';
import {
    ArrowDown, Box, Calendar, Checked, CircleCheck, Coffee, Coin, Delete, Document, EditPen,
    Files, FolderOpened, HotWater, Link as LinkIcon, Location, Medal, OfficeBuilding, Operation, PriceTag, Plus, Ticket, User, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    batch: { type: Object, required: true },
    season: { type: Object, default: null },
    harvests: { type: Array, default: () => [] },
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
    if (['approved', 'paid', 'active'].includes(status)) return 'good';
    if (['rejected', 'cancelled', 'expired'].includes(status)) return 'bad';
    return 'pending';
}

const deleteMessage = computed(() => `Are you sure you want to delete batch ${props.batch.batch_number}? This action cannot be undone.`);

const seasonLocation = computed(() => props.season?.region || '');
</script>

<template>
    <DesignPreviewLayout title="Batch">
        <div class="btp-page">
            <div class="btp-page-head">
                <div class="btp-page-head__text">
                    <h1 class="btp-page-title">Batch</h1>
                    <p class="btp-page-subtitle">Everything recorded about this batch, from processing and quality grading to the harvests and lots linked to it.</p>
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

            <div class="btp-hero">
                <div class="btp-hero__icon"><el-icon :size="22"><Files /></el-icon></div>
                <div class="btp-hero__text">
                    <div class="btp-hero__title-row">
                        <h1 class="btp-title">{{ batchTitle }}</h1>
                        <span class="btp-hero__code"><el-icon :size="11"><Ticket /></el-icon>{{ batch.batch_number }}</span>
                    </div>
                    <p class="btp-subtitle">
                        Processed {{ formatDate(batch.processing_date) }}
                        <span v-if="batch.warehouse_location"> · {{ batch.warehouse_location }}</span>
                    </p>
                </div>
                <div class="btp-hero__badges">
                    <span v-if="batch.status" class="btp-pill btp-pill--status">{{ batch.status }}</span>
                </div>
            </div>

            <div class="btp-stat-grid">
                <div class="btp-stat">
                    <div class="btp-stat__icon btp-stat__icon--a"><el-icon><Box /></el-icon></div>
                    <div class="btp-stat__label">Quantity</div>
                    <div class="btp-stat__value">{{ Number(batch.quantity_bags || 0).toLocaleString() }} <span class="btp-stat__unit">bags</span></div>
                    <div class="btp-stat__sub">{{ Number(batch.net_weight_kg || 0).toLocaleString() }} kg total</div>
                </div>
                <div class="btp-stat">
                    <div class="btp-stat__icon btp-stat__icon--b"><el-icon><Coin /></el-icon></div>
                    <div class="btp-stat__label">Price</div>
                    <div class="btp-stat__value">{{ formatMoney(batch.price, batch.currency) }}</div>
                </div>
                <div class="btp-stat">
                    <div class="btp-stat__icon btp-stat__icon--c"><el-icon><Medal /></el-icon></div>
                    <div class="btp-stat__label">Cup Score</div>
                    <div class="btp-stat__value">{{ batch.cup_score ?? '—' }} <span v-if="cupScoreKnown" class="btp-stat__unit">/100</span></div>
                </div>
                <div class="btp-stat">
                    <div class="btp-stat__icon" :class="hasDefects ? 'btp-stat__icon--warn' : 'btp-stat__icon--good'"><el-icon><WarningFilled /></el-icon></div>
                    <div class="btp-stat__label">Defects</div>
                    <div class="btp-stat__value">{{ batch.defect_count ?? '—' }}</div>
                    <span v-if="defectsKnown" class="btp-stat__pill" :class="hasDefects ? 'btp-stat__pill--warn' : 'btp-stat__pill--good'">
                        {{ hasDefects ? 'Flagged' : 'Clean' }}
                    </span>
                </div>
            </div>

            <div class="btp-layout">
                <div class="btp-col-main">
                    <div class="btp-card">
                        <h2 class="btp-card__title"><el-icon><Files /></el-icon> Batch Details</h2>
                        <dl class="btp-dl">
                            <div class="btp-dl__row"><dt>Batch Number</dt><dd>{{ batch.batch_number || '—' }}</dd></div>
                            <div class="btp-dl__row"><dt>Variety</dt><dd>{{ batch.variety || '—' }}</dd></div>
                            <div class="btp-dl__row"><dt><el-icon :size="13"><Location /></el-icon> Warehouse Location</dt><dd>{{ batch.warehouse_location || '—' }}</dd></div>
                            <div class="btp-dl__row"><dt>Processing Date</dt><dd>{{ formatDate(batch.processing_date) }}</dd></div>
                            <div class="btp-dl__row"><dt>Status</dt><dd class="btp-dl__capitalize">{{ batch.status || '—' }}</dd></div>
                        </dl>
                    </div>

                    <div class="btp-card">
                        <h2 class="btp-card__title"><el-icon><Operation /></el-icon> Processing</h2>
                        <dl class="btp-dl">
                            <div class="btp-dl__row"><dt>Processing Method</dt><dd>{{ batch.processing_method || '—' }}</dd></div>
                            <div class="btp-dl__row"><dt><el-icon :size="13"><HotWater /></el-icon> Drying Method</dt><dd>{{ batch.drying_method || '—' }}</dd></div>
                            <div class="btp-dl__row"><dt>Drying Duration</dt><dd>{{ batch.drying_duration !== null && batch.drying_duration !== undefined ? `${batch.drying_duration} days` : '—' }}</dd></div>
                            <div class="btp-dl__row"><dt>Milling Status</dt><dd>{{ batch.milling_status || '—' }}</dd></div>
                        </dl>
                    </div>

                    <div class="btp-card">
                        <h2 class="btp-card__title"><el-icon><WarningFilled /></el-icon> Quality Assessment</h2>
                        <dl class="btp-dl">
                            <div class="btp-dl__row"><dt><el-icon :size="13"><HotWater /></el-icon> Moisture Content</dt><dd>{{ batch.moisture_content !== null && batch.moisture_content !== undefined ? `${batch.moisture_content}%` : '—' }}</dd></div>
                            <div class="btp-dl__row"><dt>Cup Score</dt><dd>{{ batch.cup_score ?? '—' }}</dd></div>
                            <div class="btp-dl__row"><dt>Defect Count</dt><dd>{{ batch.defect_count ?? '—' }}</dd></div>
                            <div class="btp-dl__row"><dt>Screen Size</dt><dd>{{ batch.screen_size || '—' }}</dd></div>
                        </dl>
                    </div>

                    <div v-if="batch.notes" class="btp-card">
                        <h2 class="btp-card__title"><el-icon><Document /></el-icon> Notes</h2>
                        <p class="btp-notes">{{ batch.notes }}</p>
                    </div>

                    <div v-if="harvests.length" class="btp-card">
                        <h2 class="btp-card__title"><el-icon><Coffee /></el-icon> Harvests in this Batch</h2>
                        <div class="btp-list">
                            <Link v-for="h in harvests" :key="h.id" :href="route('harvest.show', h.id)" class="btp-list-row">
                                <div class="btp-list-row__icon"><el-icon><Coffee /></el-icon></div>
                                <div class="btp-list-row__main">
                                    <div class="btp-list-row__title">{{ h.farm?.name || `Farm #${h.farm_id}` }}</div>
                                    <div class="btp-list-row__sub">{{ h.variety || '—' }} · {{ formatDate(h.harvest_date) }}</div>
                                </div>
                                <div class="btp-list-row__stats">
                                    <div class="btp-list-stat">
                                        <span class="btp-list-stat__value">{{ h.weight ? `${Number(h.weight).toLocaleString()} kg` : '—' }}</span>
                                        <span class="btp-list-stat__label">Weight</span>
                                    </div>
                                    <span class="btp-pill btp-pill--grade">{{ h.status || '—' }}</span>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <div v-if="batch.compliances && batch.compliances.length" class="btp-card">
                        <h2 class="btp-card__title"><el-icon><CircleCheck /></el-icon> Compliance Records</h2>
                        <div class="btp-list">
                            <div v-for="c in batch.compliances" :key="c.id" class="btp-list-row btp-list-row--static">
                                <div class="btp-list-row__icon"><el-icon><CircleCheck /></el-icon></div>
                                <div class="btp-list-row__main">
                                    <div class="btp-list-row__title">{{ c.compliance_type }}</div>
                                    <div class="btp-list-row__sub">
                                        <span v-if="c.certificate_number">{{ c.certificate_number }} · </span>{{ c.issued_by || 'Unknown issuer' }}
                                    </div>
                                </div>
                                <div class="btp-list-row__stats">
                                    <div class="btp-list-stat">
                                        <span class="btp-list-stat__value">{{ formatDate(c.expires_at) }}</span>
                                        <span class="btp-list-stat__label">Expires</span>
                                    </div>
                                    <span class="btp-pill btp-pill--grade">{{ c.status }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="batch.lots && batch.lots.length" class="btp-card">
                        <h2 class="btp-card__title"><el-icon><Ticket /></el-icon> Lots from this Batch</h2>
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
                    </div>
                </div>

                <div class="btp-col-side">
                    <div class="btp-side-card">
                        <div class="btp-side-card__head">
                            <span class="btp-side-card__head-icon"><el-icon><User /></el-icon></span>
                            <h3 class="btp-side-card__eyebrow">Recorded By</h3>
                        </div>
                        <div class="btp-recorder">
                            <div class="btp-recorder__avatar">{{ recorderInitials }}</div>
                            <div class="btp-recorder__body">
                                <div class="btp-recorder__name">{{ batch.user?.name || 'Unknown' }}</div>
                                <div class="btp-recorder__meta"><el-icon :size="12"><Checked /></el-icon> {{ formatDateTime(batch.created_at) }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="season" class="btp-side-card">
                        <div class="btp-side-card__head">
                            <span class="btp-side-card__head-icon"><el-icon><Calendar /></el-icon></span>
                            <h3 class="btp-side-card__eyebrow">Season</h3>
                        </div>
                        <div class="btp-season-summary">
                            <div class="btp-season-summary__name">{{ season.name }}</div>
                            <div v-if="seasonLocation" class="btp-season-loc"><el-icon :size="12"><Location /></el-icon> {{ seasonLocation }}</div>
                            <div class="btp-season-stats">
                                <span v-if="season.harvests_count !== null">{{ season.harvests_count }} harvest{{ season.harvests_count === 1 ? '' : 's' }}</span>
                                <span v-if="season.harvests_sum_weight">{{ Number(season.harvests_sum_weight).toLocaleString() }} kg</span>
                            </div>
                        </div>
                        <Link :href="route('season.show', season.id)" class="btp-season-link">
                            View Season <el-icon class="btp-season-link__arrow"><ArrowDown class="btp-season-link__rotate" /></el-icon>
                        </Link>
                    </div>

                    <div class="btp-side-card">
                        <div class="btp-side-card__head">
                            <span class="btp-side-card__head-icon"><el-icon><OfficeBuilding /></el-icon></span>
                            <h3 class="btp-side-card__eyebrow btp-side-card__eyebrow--grow">Farm Collections</h3>
                            <button v-if="batch.can_manage" type="button" class="btp-side-card__add" title="Attach a farm collection" @click="attachModalOpen = true">
                                <el-icon :size="14"><Plus /></el-icon>
                            </button>
                        </div>

                        <div v-if="batch.farm_collection_links && batch.farm_collection_links.length" class="btp-collection-list">
                            <Link
                                v-for="link in batch.farm_collection_links"
                                :key="link.id"
                                :href="route('farm-collection.show', link.farm_collection_id)"
                                class="btp-collection-card"
                            >
                                <div class="btp-collection-card__icon"><el-icon><OfficeBuilding /></el-icon></div>
                                <div class="btp-collection-card__body">
                                    <div class="btp-collection-card__top">
                                        <span class="btp-collection-card__farm">{{ link.farm_collection?.farm?.name || `Collection ${link.farm_collection_code}` }}</span>
                                        <span class="btp-collection-card__status" :class="`btp-collection-card__status--${collectionStatusTone(link.status)}`">{{ link.status }}</span>
                                    </div>
                                    <div class="btp-collection-card__sub">
                                        {{ link.farm_collection?.coffee_type || '—' }}<span v-if="link.farm_collection?.variety"> · {{ link.farm_collection.variety }}</span>
                                    </div>
                                    <div class="btp-collection-card__meta">
                                        <span class="btp-collection-card__code"><el-icon :size="10"><PriceTag /></el-icon>{{ link.farm_collection_code }}</span>
                                        <span v-if="link.farm_collection?.quantity" class="btp-collection-card__qty">{{ Number(link.farm_collection.quantity).toLocaleString() }} {{ link.farm_collection.unit || '' }}</span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="btp-collection-empty">
                            <div class="btp-collection-empty__icon"><el-icon :size="18"><FolderOpened /></el-icon></div>
                            <p class="btp-collection-empty__text">No farm collections linked yet.</p>
                        </div>
                    </div>
                </div>
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
.btp-hero__badges { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

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

.btp-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.btp-stat {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.btp-stat:hover { border-color: color-mix(in srgb, var(--on-surface) 18%, var(--card-border)); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04); }
.btp-stat__icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    font-size: 16px;
}
.btp-stat__icon--a { background: color-mix(in srgb, var(--primary) 7%, var(--surface-container-lowest)); color: var(--primary); }
.btp-stat__icon--b { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.btp-stat__icon--c { background: #EEF2FF; color: #4338CA; }
.btp-stat__icon--good { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.btp-stat__icon--warn { background: #fef3c7; color: #92400e; }
.btp-stat__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-variant); margin-bottom: 6px; }
.btp-stat__value { font-size: 24px; font-weight: 800; letter-spacing: -0.01em; color: var(--on-surface); font-variant-numeric: tabular-nums; line-height: 1.2; }
.btp-stat__unit { font-size: 13px; font-weight: 600; color: var(--on-surface-variant); }
.btp-stat__sub { font-size: 11.5px; color: var(--on-surface-variant); margin-top: 7px; }
.btp-stat__pill {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    margin-top: 9px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.btp-stat__pill--good { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.btp-stat__pill--warn { background: #fef3c7; color: #92400e; }

.btp-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 20px; align-items: start; }
.btp-col-main { min-width: 0; display: flex; flex-direction: column; gap: 16px; }
.btp-col-side { min-width: 0; display: flex; flex-direction: column; gap: 16px; }

.btp-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px 24px;
}
.btp-card__title {
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

/* ── Related-record list rows (Harvests/Compliance/Lots) ────────────────── */
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
.btp-list-row__stats { display: flex; align-items: center; gap: 16px; flex-shrink: 0; }
.btp-list-stat { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; min-width: 70px; }
.btp-list-stat__value { font-size: 13px; font-weight: 700; color: var(--on-surface); font-variant-numeric: tabular-nums; white-space: nowrap; }
.btp-list-stat__label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-variant); white-space: nowrap; }

.btp-side-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px;
}
.btp-side-card__head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.btp-side-card__head-icon {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: color-mix(in srgb, var(--primary) 6%, var(--surface-container-lowest));
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 13px;
}
.btp-side-card__eyebrow { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--on-surface-variant); margin: 0; }
.btp-side-card__eyebrow--grow { flex: 1; }
.btp-side-card__add {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: none;
    background: var(--surface-container);
    color: var(--on-surface);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background .15s ease;
}
.btp-side-card__add:hover { background: color-mix(in srgb, var(--card-border) 60%, transparent); }

.btp-recorder { display: flex; align-items: center; gap: 12px; }
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

.btp-season-summary { margin-bottom: 16px; }
.btp-season-summary__name { font-size: 15px; font-weight: 800; letter-spacing: -0.005em; color: var(--on-surface); }
.btp-season-loc { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--on-surface-variant); margin-top: 6px; }
.btp-season-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 9px; font-size: 12px; color: var(--on-surface-variant); font-weight: 600; }

.btp-season-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 16px;
    border-radius: 6px;
    background: var(--primary);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: opacity .15s ease;
}
.btp-season-link:hover { opacity: .88; }
.btp-season-link__rotate { transform: rotate(-90deg); font-size: 13px; }

/* ── Farm Collections linked via batch_farm_collection ───────────────────── */
.btp-collection-list { display: flex; flex-direction: column; gap: 10px; }
.btp-collection-card {
    display: flex;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--card-border);
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
}
.btp-collection-card:hover { border-color: color-mix(in srgb, var(--on-surface) 18%, var(--card-border)); box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05); transform: translateY(-1px); }
.btp-collection-card__icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    flex-shrink: 0;
    background: color-mix(in srgb, var(--primary) 7%, var(--surface-container-lowest));
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.btp-collection-card__body { flex: 1; min-width: 0; }
.btp-collection-card__top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.btp-collection-card__farm { font-size: 13px; font-weight: 700; color: var(--on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.btp-collection-card__status {
    flex-shrink: 0;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 9.5px;
    font-weight: 700;
}
.btp-collection-card__status--pending { background: #fef3c7; color: #92400e; }
.btp-collection-card__status--good { background: var(--secondary-container); color: var(--on-secondary-container); }
.btp-collection-card__status--bad { background: var(--error-container); color: var(--on-error-container); }
.btp-collection-card__sub { font-size: 11.5px; color: var(--on-surface-variant); margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.btp-collection-card__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 9px;
    padding-top: 9px;
    border-top: 1px dashed var(--card-border);
}
.btp-collection-card__code {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 10.5px;
    font-weight: 600;
    color: var(--on-surface-variant);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.btp-collection-card__qty { flex-shrink: 0; font-size: 11px; font-weight: 700; color: var(--on-surface); font-variant-numeric: tabular-nums; }

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

@media (max-width: 1024px) {
    .btp-layout { grid-template-columns: 1fr; }
    .btp-stat-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .btp-page-title { font-size: 1.25rem; line-height: 1.6rem; }
    .btp-hero { flex-direction: column; align-items: flex-start; }
    .btp-hero__badges { flex-wrap: wrap; }
    .btp-stat-grid { grid-template-columns: 1fr; }
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
