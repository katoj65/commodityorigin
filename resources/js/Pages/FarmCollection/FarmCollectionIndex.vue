<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AddFarmCollectionModal from '@/Components/Modals/AddFarmCollectionModal.vue';

/* ── Structural/visual port of the uploaded "My Farm Collection" mockup
   (code.html), restyled with this app's own --dp-* theme tokens rather
   than the mockup's own Tailwind palette. The ledger table is real farm
   collection data for the authenticated user (FarmCollectionController::
   index); the KPI row and custody dossier are still illustrative dummy
   data. "Record Collection" opens the same real AddFarmCollectionModal
   already used on the Inventory page (reused, not rebuilt), wired to
   real option lists from the controller. ─────────────────────────────── */
const props = defineProps({
    coffeeTypeOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    farmCollections: { type: Array, default: () => [] },
});

const addCollectionOpen = ref(false);

const kpiCards = [
    { icon: 'receipt_long', label: 'Collections', value: '126', trailing: '+14 MoM', note: 'Total intake events' },
    { icon: 'scale', label: 'Coffee Collected', value: '482.6', unit: 'MT', note: 'Total physical intake' },
    { icon: 'verified_user', label: 'Pending Audit', value: '8', chip: 'Requires Audit', note: 'Lacking lab/scale evidence', tone: 'secondary' },
    { icon: 'conveyor_belt', label: 'In Processing', value: '31', trailing: 'Batches active', note: 'Assigned to active washing' },
    { icon: 'inventory', label: 'Ready for Batching', value: '95.4', unit: 'MT', note: 'Unassigned buffer', valueTone: true },
];

function formatCollectionDate(value) {
    if (!value) return '—';
    return new Date(value.replace(' ', 'T')).toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatMoney(amount, currency) {
    const value = Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${value}` : `$${value}`;
}

function producerName(farm) {
    return farm?.user?.full_name || farm?.user?.first_name || null;
}

const STATUS_LABELS = { pending: 'Pending', batched: 'Batched' };
const STATUS_TONE = { pending: 'secondary', batched: 'primary' };
function statusLabel(status) {
    return STATUS_LABELS[status] || (status || '—');
}
function statusTone(status) {
    return STATUS_TONE[status] || 'neutral';
}

/* The active-row dot only marks the first row by default — the dossier
   panel stays a static illustration of FC-UG-1048, deliberately decoupled
   from the real row data above. Clicking a row navigates away entirely,
   to that collection's real profile page. */
const selectedRowId = ref(props.farmCollections[0]?.id ?? null);

function goToCollection(row) {
    router.visit(route('farm-collection.show', row.id));
}
</script>

<template>
    <MainLayout title="Farm Collections">
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="fc-page">
            <!-- ── Top context bar ──────────────────────────────────────── -->
            <div class="fc-hero">
                <div class="fc-hero__text">
                    <h1 class="fc-title">My Farm Collection</h1>
                    <p class="fc-subtitle">Record and manage coffee collected from farms before it enters processing and batch aggregation.</p>
                </div>
                <div class="fc-hero__actions">
                    <button type="button" class="fc-btn fc-btn--primary" @click="addCollectionOpen = true">
                        <span class="material-symbols-outlined">add_circle</span> Record Collection
                    </button>
                    <button type="button" class="fc-btn fc-btn--muted">
                        <span class="material-symbols-outlined">file_upload</span> Import Collections
                    </button>
                </div>
            </div>

            <!-- ── Supply chain relationship stepper ────────────────────── -->
            <div class="fc-stepper">
                <div class="fc-stepper__track">
                    <div class="fc-stepper__node"><span class="fc-stepper__dot"></span><span>Farm</span></div>
                    <span class="material-symbols-outlined fc-stepper__arrow">arrow_forward</span>
                    <div class="fc-stepper__badge">
                        <span class="fc-stepper__badge-dot"></span>
                        <span>Farm Collection</span>
                        <span class="fc-stepper__badge-tag">Physical Custody Event</span>
                    </div>
                    <span class="material-symbols-outlined fc-stepper__arrow">arrow_forward</span>
                    <div class="fc-stepper__node"><span class="fc-stepper__dot"></span><span>Batch</span></div>
                    <span class="material-symbols-outlined fc-stepper__arrow">arrow_forward</span>
                    <div class="fc-stepper__node"><span class="fc-stepper__dot"></span><span>Lot</span></div>
                    <span class="material-symbols-outlined fc-stepper__arrow">arrow_forward</span>
                    <div class="fc-stepper__node"><span class="fc-stepper__dot"></span><span>Exchange</span></div>
                </div>
            
            </div>

            <!-- ── KPI summary row ───────────────────────────────────────── -->
            <div class="fc-kpi-grid">
                <div v-for="kpi in kpiCards" :key="kpi.label" class="fc-kpi">
                    <div class="fc-kpi__top">
                        <span class="fc-kpi__label">{{ kpi.label }}</span>
                        <span class="material-symbols-outlined" :class="kpi.tone === 'secondary' ? 'fc-tone-text--secondary' : 'fc-tone-text'">{{ kpi.icon }}</span>
                    </div>
                    <div class="fc-kpi__value">
                        <span class="fc-mono" :class="{ 'fc-tone-text': kpi.valueTone }">{{ kpi.value }}</span>
                        <span v-if="kpi.unit" class="fc-kpi__unit fc-mono">{{ kpi.unit }}</span>
                        <span v-if="kpi.trailing" class="fc-kpi__trailing fc-mono">{{ kpi.trailing }}</span>
                        <span v-if="kpi.chip" class="fc-chip fc-chip--tone">{{ kpi.chip }}</span>
                    </div>
                    <span class="fc-kpi__note">{{ kpi.note }}</span>
                </div>
            </div>

            <!-- ── Two-column workspace ─────────────────────────────────── -->
            <div class="fc-grid">
                <!-- ── Left column ──────────────────────────────────────── -->
                <div class="fc-col-main">
                    <!-- Ledger table -->
                    <div class="fc-table-card">
                        <div class="fc-table-wrap">
                            <table class="fc-table">
                                <colgroup>
                                    <col style="width: 16%" />
                                    <col style="width: 26%" />
                                    <col style="width: 18%" />
                                    <col style="width: 19%" />
                                    <col style="width: 21%" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>ID &amp; Date</th>
                                        <th>Farm &amp; Producer</th>
                                        <th>Qty (MT)</th>
                                        <th>Batch</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="row in farmCollections"
                                        :key="row.id"
                                        class="fc-table__row"
                                        :class="{ 'fc-table__row--active': selectedRowId === row.id }"
                                        tabindex="0"
                                        role="button"
                                        @click="goToCollection(row)"
                                        @keydown.enter="goToCollection(row)"
                                    >
                                        <td>
                                            <div class="fc-mono fc-strong fc-tone-text"><span class="fc-table__dot" v-if="selectedRowId === row.id"></span>{{ row.collection_code || `FC-${row.id}` }}</div>
                                            <div class="fc-muted fc-small">{{ formatCollectionDate(row.collection_date) }}</div>
                                        </td>
                                        <td>
                                            <div class="fc-strong">{{ row.farm?.name || `Farm #${row.farm_id}` }}</div>
                                            <div class="fc-muted fc-small">{{ producerName(row.farm) || row.coffee_type || '—' }}</div>
                                        </td>
                                        <td>
                                            <span class="fc-mono fc-strong">{{ Number(row.quantity || 0).toLocaleString() }} {{ row.unit || '' }}</span>
                                            <span v-if="row.collection_price" class="fc-mono fc-muted fc-small fc-block">{{ formatMoney(row.collection_price, row.currency) }}</span>
                                        </td>
                                        <td>
                                            <span v-if="row.batch" class="fc-chip fc-chip--fixed fc-mono">{{ row.batch.batch_number }}</span>
                                            <span v-else class="fc-muted fc-small fc-italic">Unassigned</span>
                                        </td>
                                        <td>
                                            <span class="fc-status" :class="`fc-status--${statusTone(row.status)}`">{{ statusLabel(row.status) }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!farmCollections.length">
                                        <td colspan="5" class="fc-table__empty">No farm collections recorded yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="fc-table-card__foot">
                            <span class="fc-muted fc-small">{{ farmCollections.length }} collection{{ farmCollections.length === 1 ? '' : 's' }} total</span>
                            <div class="fc-pagination">
                                <button type="button" class="fc-page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                                <button type="button" class="fc-page-btn fc-page-btn--active">1</button>
                                <button type="button" class="fc-page-btn" disabled><span class="material-symbols-outlined">chevron_right</span></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Right column: Physical Custody Dossier ───────────── -->
                <div class="fc-col-side">
                    <div class="fc-card">
                        <div class="fc-dossier__head">
                            <div>
                                <span class="fc-eyebrow-sm">Physical Custody Dossier</span>
                                <h2 class="fc-dossier__title">FC-UG-1048 <span class="fc-chip fc-chip--fixed">Verified</span></h2>
                            </div>
                            <div class="fc-dossier__head-actions">
                                <button type="button" class="fc-icon-btn" title="Edit"><span class="material-symbols-outlined">edit</span></button>
                                <button type="button" class="fc-icon-btn" title="Share / Export"><span class="material-symbols-outlined">share</span></button>
                            </div>
                        </div>

                        <div class="fc-dossier__section">
                            <span class="fc-eyebrow-sm">Custody Lineage Tree</span>
                            <div class="fc-lineage">
                                <span class="fc-tone-text fc-strong">Kawempe Farm</span>
                                <span class="fc-muted">→</span>
                                <span class="fc-chip fc-chip--fixed fc-mono">FC-1048</span>
                                <span class="fc-muted">→</span>
                                <span class="fc-mono">BAT-UG-2031</span>
                                <span class="fc-muted">→</span>
                                <span class="fc-mono">LOT-UG-001</span>
                                <span class="fc-muted">→</span>
                                <span class="fc-tone-text fc-strong">Exchange</span>
                            </div>
                        </div>

                        <div class="fc-dossier__section">
                            <div class="fc-dossier__section-head">
                                <span class="fc-card__title-plain">Physical Quantity Movement</span>
                                <span class="fc-mono fc-tone-text fc-small fc-strong">96.0% Intake Yield</span>
                            </div>
                            <div class="fc-bar">
                                <div class="fc-bar__seg fc-bar__seg--primary" style="width: 80%" title="Allocated to Batch: 2,000 kg"></div>
                                <div class="fc-bar__seg fc-bar__seg--tone" style="width: 16%" title="Unallocated Buffer: 400 kg"></div>
                                <div class="fc-bar__seg fc-bar__seg--error" style="width: 4%" title="Sorted Out / Float Defect: 100 kg"></div>
                            </div>
                            <div class="fc-stat-grid">
                                <div class="fc-stat-box"><span class="fc-stat-box__label">Gross Collected</span><span class="fc-mono fc-strong">2,500 kg</span></div>
                                <div class="fc-stat-box"><span class="fc-stat-box__label">Rejected (Floats)</span><span class="fc-mono fc-strong fc-error-text">100 kg</span></div>
                                <div class="fc-stat-box"><span class="fc-stat-box__label">Allocated (BAT-2031)</span><span class="fc-mono fc-strong fc-tone-text">2,000 kg</span></div>
                                <div class="fc-stat-box"><span class="fc-stat-box__label">Unallocated Buffer</span><span class="fc-mono fc-strong fc-tone-text--secondary">400 kg</span></div>
                            </div>
                        </div>

                        <div class="fc-dossier__section">
                            <span class="fc-card__title-plain">Point-of-Intake Condition</span>
                            <div class="fc-panel fc-panel--grid2">
                                <div><span class="fc-eyebrow-sm">Variety</span><span class="fc-field-value">Uganda Robusta (Nganda)</span></div>
                                <div><span class="fc-eyebrow-sm">Intake Form</span><span class="fc-field-value">Fresh Ripe Cherries (A1)</span></div>
                                <div><span class="fc-eyebrow-sm">Refractometer Sugar</span><span class="fc-mono fc-strong fc-tone-text">21.4° Brix</span></div>
                                <div><span class="fc-eyebrow-sm">Field Moisture</span><span class="fc-mono fc-strong">62% Wet Basis</span></div>
                                <div><span class="fc-eyebrow-sm">Packaging Form</span><span class="fc-field-value">42 Sisal Bags</span></div>
                                <div><span class="fc-eyebrow-sm">Defect Rate</span><span class="fc-field-value">4.0% sorted floaters</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── "Record Collection" — the same real modal reused from the
             Inventory page, not a rebuilt dummy multi-step form. ──────── -->
        <AddFarmCollectionModal
            v-model="addCollectionOpen"
            :coffee-type-options="coffeeTypeOptions"
            :harvest-season-options="harvestSeasonOptions"
            :currency-options="currencyOptions"
        />
    </MainLayout>
</template>

<style scoped>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

.fc-page { font-family: var(--dp-font-sans); display: flex; flex-direction: column; gap: 20px; color: var(--dp-on-surface); }
.fc-mono { font-family: var(--dp-font-mono); }
.fc-muted { color: var(--dp-on-surface-variant); }
.fc-strong { font-weight: 700; color: var(--dp-on-surface); }
.fc-small { font-size: 11px; }
.fc-block { display: block; }
.fc-nowrap { white-space: nowrap; }
.fc-italic { font-style: italic; }
.fc-tone-text { color: var(--dp-primary); }
.fc-tone-text--secondary { color: var(--dp-on-secondary-container); }
.fc-error-text { color: var(--dp-error); }

/* Hero */
.fc-hero { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.fc-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.015em; color: var(--dp-on-surface); margin: 0; }
.fc-subtitle { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; line-height: 1.5; max-width: 60ch; }
.fc-hero__actions { display: flex; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }

.fc-btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border-radius: 6px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); transition: opacity .12s ease, background .12s ease; }
.fc-btn .material-symbols-outlined { font-size: 16px; }
.fc-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.fc-btn--primary:hover { opacity: .9; }
.fc-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.fc-btn--muted:hover { background: var(--dp-surface-container-highest); }

/* Stepper */
.fc-stepper { background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px; }
.fc-stepper__track { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.fc-stepper__node { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--dp-on-surface-variant); }
.fc-stepper__dot { width: 7px; height: 7px; border-radius: 999px; background: var(--dp-outline-variant); }
.fc-stepper__arrow { font-size: 14px; color: var(--dp-outline); }
.fc-stepper__badge { display: inline-flex; align-items: center; gap: 10px; background: var(--dp-primary); color: var(--dp-on-primary); padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.fc-stepper__badge-dot { width: 7px; height: 7px; border-radius: 999px; background: var(--dp-on-primary); animation: fc-pulse 1.8s ease-in-out infinite; flex-shrink: 0; }
.fc-stepper__badge-tag { padding: 3px 7px; background: var(--dp-on-primary); color: var(--dp-primary); font-size: 9px; border-radius: 4px; text-transform: uppercase; letter-spacing: .05em; font-family: var(--dp-font-mono); font-weight: 800; white-space: nowrap; }
@keyframes fc-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
.fc-stepper__rule { font-size: 11px; color: var(--dp-on-surface-variant); line-height: 1.5; margin: 0; max-width: 70ch; }
.fc-stepper__rule strong { color: var(--dp-on-surface); font-weight: 700; }

/* KPI row */
.fc-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
.fc-kpi { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 14px; display: flex; flex-direction: column; gap: 6px; }
.fc-kpi__top { display: flex; align-items: center; justify-content: space-between; }
.fc-kpi__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.fc-kpi__top .material-symbols-outlined { font-size: 17px; }
.fc-kpi__value { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
.fc-kpi__value > .fc-mono:first-child { font-size: 1.375rem; font-weight: 800; color: var(--dp-on-surface); }
.fc-kpi__unit { font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant); }
.fc-kpi__trailing { font-size: 10.5px; font-weight: 700; color: var(--dp-primary); }
.fc-kpi__note { font-size: 10px; color: var(--dp-on-surface-variant); margin-top: 2px; }

/* Two-column grid */
.fc-grid { display: grid; grid-template-columns: minmax(0, 4fr) minmax(260px, 1fr); gap: 18px; align-items: start; }
.fc-col-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.fc-col-side { display: flex; flex-direction: column; gap: 16px; }
@media (max-width: 1180px) { .fc-grid { grid-template-columns: 1fr; } }

/* Table */
.fc-table-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); overflow: hidden; }
.fc-table-wrap { overflow-x: hidden; }
.fc-table { width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: 11.5px; }
.fc-table thead tr { background: var(--dp-surface-container-low); }
.fc-table th { padding: 9px 8px; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); overflow-wrap: break-word; }
.fc-table td { padding: 10px 8px; border-top: 1px solid var(--dp-outline-variant); vertical-align: middle; overflow-wrap: break-word; }
.fc-table__row { cursor: pointer; transition: background .12s ease; }
.fc-table__row:hover { background: var(--dp-surface-container-low); }
.fc-table__row--active { background: color-mix(in srgb, var(--dp-primary) 6%, transparent); }
.fc-table__dot { display: inline-block; width: 5px; height: 5px; border-radius: 999px; background: var(--dp-primary); margin-right: 5px; }
.fc-table__empty { text-align: center; padding: 32px 12px; color: var(--dp-on-surface-variant); font-size: 12px; }
.fc-table-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; background: var(--dp-surface-container-low); flex-wrap: wrap; }

.fc-pagination { display: flex; align-items: center; gap: 3px; }
.fc-pagination__dots { font-family: var(--dp-font-mono); font-size: 11px; color: var(--dp-on-surface-variant); padding: 0 3px; }
.fc-page-btn { min-width: 24px; height: 24px; padding: 0 4px; border-radius: 5px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 11px; font-family: var(--dp-font-mono); font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.fc-page-btn .material-symbols-outlined { font-size: 16px; }
.fc-page-btn:hover:not(:disabled) { background: var(--dp-surface-container-high); }
.fc-page-btn:disabled { opacity: .4; cursor: default; }
.fc-page-btn--active { background: var(--dp-primary); color: var(--dp-on-primary); }

/* Icon buttons */
.fc-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); cursor: pointer; transition: background .12s ease, color .12s ease; }
.fc-icon-btn:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.fc-icon-btn .material-symbols-outlined { font-size: 16px; }

/* Chips / status / tags */
.fc-chip { display: inline-flex; align-items: center; padding: 2px 6px; border-radius: 4px; font-size: 9.5px; font-family: var(--dp-font-mono); font-weight: 700; background: var(--dp-surface-container); color: var(--dp-on-surface); white-space: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
.fc-chip--fixed { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.fc-chip--tone { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.fc-status { display: inline-flex; align-items: center; padding: 3px 7px; border-radius: 999px; font-size: 9px; font-weight: 700; white-space: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
.fc-status--primary { background: var(--dp-primary-container); color: var(--dp-on-primary); }
.fc-status--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.fc-status--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }

/* Dossier card */
.fc-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 18px; display: flex; flex-direction: column; gap: 16px; }
.fc-card__title-plain { font-size: .8125rem; font-weight: 800; color: var(--dp-on-surface); }
.fc-eyebrow-sm { display: block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dp-on-surface-variant); margin-bottom: 2px; }
.fc-field-value { font-size: 12.5px; font-weight: 700; color: var(--dp-on-surface); }

.fc-dossier__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.fc-dossier__title { display: flex; align-items: center; gap: 8px; font-size: 1.0625rem; font-weight: 800; color: var(--dp-on-surface); margin: 2px 0 0; }
.fc-dossier__head-actions { display: flex; gap: 4px; flex-shrink: 0; }
.fc-dossier__section { display: flex; flex-direction: column; gap: 8px; }
.fc-dossier__section-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

.fc-lineage { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; background: var(--dp-surface-container-low); padding: 10px 12px; border-radius: 8px; font-size: 11px; }

.fc-bar { width: 100%; height: 10px; border-radius: 999px; background: var(--dp-surface-container-high); overflow: hidden; display: flex; }
.fc-bar__seg--primary { background: var(--dp-primary); }
.fc-bar__seg--tone { background: var(--dp-secondary); }
.fc-bar__seg--error { background: color-mix(in srgb, var(--dp-error) 75%, transparent); }
.fc-stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 4px; }
.fc-stat-box { display: flex; flex-direction: column; gap: 3px; padding: 8px 10px; background: var(--dp-surface-container-low); border-radius: 7px; }
.fc-stat-box__label { font-size: 9.5px; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); }

.fc-panel { background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; }
.fc-panel--grid2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px 12px; }

@media (max-width: 640px) {
    .fc-hero__actions { width: 100%; }
    .fc-hero__actions .fc-btn { flex: 1; justify-content: center; }
    .fc-panel--grid2 { grid-template-columns: 1fr; }
}
</style>
