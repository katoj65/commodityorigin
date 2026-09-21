<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AddBatchModal from '@/Components/Modals/AddBatchModal.vue';

/* ── Structural/visual port of the uploaded "My Batches" mockup
   (code.html), restyled with this app's own --dp-* theme tokens rather
   than the mockup's own Tailwind palette. Everything below is
   illustrative dummy data, not real batches — but "Create Batch" opens
   the same real AddBatchModal already used on the Inventory page
   (reused, not rebuilt), wired to real option lists already provided by
   StoreController::inventoryContext(). ───────────────────────────────── */
const props = defineProps({
    processOptions: { type: Array, default: () => [] },
    dryingMethodOptions: { type: Array, default: () => [] },
    millingOptions: { type: Array, default: () => [] },
    coffeeTypeOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
});

const addBatchOpen = ref(false);

const kpiCards = [
    { icon: 'folder_open', label: 'Active Batches', value: '24', trailing: '+3 this mo', trailingIcon: 'arrow_upward', note: 'Across 4 processing stations' },
    { icon: 'autorenew', label: 'Processing', value: '8', trailing: 'Active operations', note: 'Drying, Fermenting, Hulling' },
    { icon: 'verified', label: 'Ready for Lot', value: '10', trailing: 'Passed Specs', tone: 'primary', note: 'Awaiting allocation to export lots' },
    { icon: 'scale', label: 'Total Quantity', value: '186.4', unit: 'MT', note: 'Aggregated in 5 origin regions' },
    { icon: 'assignment_late', label: 'Quality Review', value: '4', trailing: 'Pending Audit', tone: 'error', note: 'Moisture & CQI cupping check' },
];

const batches = [
    { id: 'BAT-UG-2048', coffee: 'Uganda Robusta', origin: 'Nganda • Mukono Mill', coll: '4 Coll.', collNote: 'FC-1048, 1049...', net: '9.55 MT', netNote: '10.0 MT input', stage: 'Drying (Day 4)', stageNote: '11.4% H₂O', stageTone: 'secondary', status: 'Processing', statusTone: 'secondary', action: 'Inspect', actionTone: 'primary', active: true },
    { id: 'BAT-UG-2047', coffee: 'Fine Robusta Screen 18', origin: 'Masaka Washing Station', coll: '7 Coll.', net: '24.0 MT', netNote: 'Graded & Polished', stage: '84.5 CQI', stageIcon: 'verified', stageNote: '11.2% H₂O', stageTone: 'primary', status: 'Ready for Lot', statusTone: 'primary', action: 'Create Lot', actionTone: 'container' },
    { id: 'BAT-UG-2045', coffee: 'Bugisu Arabica AA Washed', origin: 'Mt. Elgon Wet Mill • Mbale', coll: '3 Coll.', net: '11.2 MT', netNote: '6 MT in LOT-UG-014', netTone: 'tertiary', stage: '86.8 SCAA', stageIcon: 'verified', stageNote: '10.8% H₂O', stageTone: 'primary', status: 'Allocated (6MT)', statusTone: 'tertiary', action: 'Allocate', actionTone: 'muted' },
    { id: 'BAT-UG-2042', coffee: 'Rwenzori Natural Drugar', origin: 'Kasese Solar Dryers', coll: '5 Coll.', net: '15.8 MT', netNote: 'Hulling & Sizing', stage: 'Lab Pending', stageIcon: 'biotech', stageNote: 'Moisture audit req.', stageTone: 'error', status: 'Quality Review', statusTone: 'error', action: 'Review', actionTone: 'muted' },
    { id: 'BAT-UG-2038', coffee: 'West Nile FAQ Robusta', origin: 'Nebbi Regional Mill', coll: '6 Coll.', net: '32.0 MT', netNote: 'Fully Milled', stage: '81.0 CQI', stageIcon: 'done_all', stageNote: '11.6% H₂O', stageTone: 'primary', status: 'Completed', statusTone: 'neutral', action: 'View Lots', actionTone: 'muted' },
];

const collections = [
    { code: 'FC-UG-1048', farm: 'Kawempe Coffee Farm', weight: '2,500 kg' },
    { code: 'FC-UG-1049', farm: 'Luwero Triangle Estate', weight: '4,000 kg' },
    { code: 'FC-UG-1051', farm: 'Nalubale Estate Block B', weight: '2,000 kg' },
    { code: 'FC-UG-1054', farm: 'Kasenge Valley Farm', weight: '1,500 kg' },
];

const timeline = [
    { date: '15 Sep', title: 'Batch Aggregated & Weighed', note: 'Verified by David K. at Mukono Intake Depot', tone: 'primary' },
    { date: '16 Sep', title: 'Wet Fermentation Complete', note: '36h Controlled tank fermentation; pH monitored 4.3', tone: 'primary' },
    { date: '18 Sep', title: 'Raised Bed Drying (Current)', note: 'Moisture calibrated at 11.4% (Target: 11.0%)', tone: 'secondary' },
    { date: '20 Sep (Scheduled)', title: 'Mechanical Hulling', note: 'Density separation, destoning & Screen 18 sorting', tone: 'muted' },
];
</script>

<template>
    <MainLayout title="Batches">
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="btc-page">
            <!-- ── Top context bar ──────────────────────────────────────── -->
            <div class="btc-hero">
                <div class="btc-hero__text">
                    <nav class="btc-breadcrumb">
                        <span>My Coffee</span>
                        <span class="material-symbols-outlined">chevron_right</span>
                        <span class="btc-breadcrumb__current">Batches</span>
                    </nav>
                    <h1 class="btc-title">My Batches</h1>
                    <p class="btc-subtitle">Manage coffee aggregation, processing, quality, quantity transformations, and preparation for commercial Lots.</p>
                </div>
                <div class="btc-hero__actions">
                    <button type="button" class="btc-btn btc-btn--primary" @click="addBatchOpen = true">
                        <span class="material-symbols-outlined">add_circle</span> Create Batch
                    </button>
                    <Link :href="route('farm-collection.index')" class="btc-btn btc-btn--muted">
                        <span class="material-symbols-outlined">inventory_2</span> View Farm Collections
                    </Link>
                    <button type="button" class="btc-btn btc-btn--secondary">
                        <span class="material-symbols-outlined">auto_awesome</span> Ask Bean Origin AI
                    </button>
                </div>
            </div>

            <!-- ── Batch lifecycle banner ────────────────────────────────── -->
            <div class="btc-stepper">
                <div class="btc-stepper__track">
                    <div class="btc-stepper__node"><span class="btc-stepper__dot"></span><span>Farm</span></div>
                    <span class="material-symbols-outlined btc-stepper__arrow">arrow_forward</span>
                    <div class="btc-stepper__node"><span class="btc-stepper__dot"></span><span>Farm Collection</span></div>
                    <span class="material-symbols-outlined btc-stepper__arrow">arrow_forward</span>
                    <div class="btc-stepper__badge">
                        <span class="btc-stepper__badge-dot"></span>
                        <span>Batch: Processing &amp; Transformation</span>
                    </div>
                    <span class="material-symbols-outlined btc-stepper__arrow">arrow_forward</span>
                    <div class="btc-stepper__node"><span class="btc-stepper__dot"></span><span>Lot</span></div>
                    <span class="material-symbols-outlined btc-stepper__arrow">arrow_forward</span>
                    <div class="btc-stepper__node"><span class="btc-stepper__dot"></span><span>Exchange</span></div>
                    <span class="material-symbols-outlined btc-stepper__arrow">arrow_forward</span>
                    <div class="btc-stepper__node"><span class="btc-stepper__dot"></span><span>Trade</span></div>
                </div>
                <p class="btc-stepper__rule"><strong>Institutional Rule:</strong> A Batch groups coffee from one or more Farm Collections for processing, quality grading, and preparation into commercial Lots. Milling outturns and physical losses are recorded to eliminate double-counting.</p>
            </div>

            <!-- ── KPI summary row ───────────────────────────────────────── -->
            <div class="btc-kpi-grid">
                <div v-for="kpi in kpiCards" :key="kpi.label" class="btc-kpi">
                    <div class="btc-kpi__top">
                        <span class="btc-kpi__label">{{ kpi.label }}</span>
                        <span class="material-symbols-outlined" :class="kpi.tone === 'error' ? 'btc-tone-error' : 'btc-tone-text'">{{ kpi.icon }}</span>
                    </div>
                    <div class="btc-kpi__value">
                        <span class="btc-mono">{{ kpi.value }}</span>
                        <span v-if="kpi.unit" class="btc-kpi__unit btc-mono">{{ kpi.unit }}</span>
                        <span v-if="kpi.trailing" class="btc-kpi__trailing" :class="{ 'btc-tone-error': kpi.tone === 'error', 'btc-tone-text': kpi.tone === 'primary' }">
                            <span v-if="kpi.trailingIcon" class="material-symbols-outlined">{{ kpi.trailingIcon }}</span>{{ kpi.trailing }}
                        </span>
                    </div>
                    <span class="btc-kpi__note">{{ kpi.note }}</span>
                </div>
            </div>

            <!-- ── Search & filters ──────────────────────────────────────── -->
            <div class="btc-filters">
                <div class="btc-search">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" placeholder="Search batch ID, coffee, farm, origin, or collection..." readonly />
                    <span class="btc-search__badge">⌘K</span>
                </div>
                <div class="btc-filters__row">
                    <select class="btc-select"><option>Status: All Statuses</option><option>Active</option><option selected>Processing</option><option>Quality Review</option><option>Ready for Lot</option><option>Partially Allocated</option><option>Completed</option></select>
                    <select class="btc-select"><option>Coffee: All Types</option><option>Robusta Screen 18</option><option>Bugisu Arabica AA</option><option>Rwenzori Natural</option></select>
                    <select class="btc-select"><option>Origin: All Regions</option><option>Central Uganda</option><option>Mt. Elgon</option><option>Rwenzori</option><option>West Nile</option></select>
                    <select class="btc-select"><option>Method: All</option><option>Washed</option><option>Natural</option><option>Honey</option><option>Wet Mill Anaerobic</option></select>
                    <select class="btc-select"><option>Hub: All Locations</option><option>Kampala Central Dry Mill</option><option>Mbale Processing Hub</option><option>Kasese Depot</option></select>
                    <div class="btc-view-toggle">
                        <button type="button" class="btc-view-toggle__opt btc-view-toggle__opt--active"><span class="material-symbols-outlined">table_rows</span></button>
                        <button type="button" class="btc-view-toggle__opt"><span class="material-symbols-outlined">grid_view</span></button>
                    </div>
                </div>
            </div>

            <!-- ── Two-column workspace ─────────────────────────────────── -->
            <div class="btc-grid">
                <!-- ── Left column ──────────────────────────────────────── -->
                <div class="btc-col-main">
                    <div class="btc-table-card">
                        <div class="btc-table-card__head">
                            <div class="btc-table-card__title">
                                <span>Registered Batches</span>
                                <span class="btc-chip">24 Active</span>
                            </div>
                            <div class="btc-sort"><span>Sort:</span><span class="btc-sort__value">Newest First</span></div>
                        </div>
                        <div class="btc-table-wrap">
                            <table class="btc-table">
                                <colgroup>
                                    <col style="width: 11%" />
                                    <col style="width: 20%" />
                                    <col style="width: 11%" />
                                    <col style="width: 12%" />
                                    <col style="width: 15%" />
                                    <col style="width: 14%" />
                                    <col style="width: 17%" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Batch ID</th>
                                        <th>Coffee &amp; Variety</th>
                                        <th>Collections</th>
                                        <th>Net / Input</th>
                                        <th>Stage &amp; Moisture</th>
                                        <th>Status</th>
                                        <th class="btc-table__end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in batches" :key="row.id" class="btc-table__row" :class="{ 'btc-table__row--active': row.active }">
                                        <td class="btc-mono btc-strong btc-tone-text">
                                            <span class="btc-table__dot" v-if="row.active"></span>{{ row.id }}
                                        </td>
                                        <td>
                                            <div class="btc-strong">{{ row.coffee }}</div>
                                            <div class="btc-muted btc-small">{{ row.origin }}</div>
                                        </td>
                                        <td>
                                            <span class="btc-chip">{{ row.coll }}</span>
                                            <div v-if="row.collNote" class="btc-muted btc-small btc-mono">{{ row.collNote }}</div>
                                        </td>
                                        <td>
                                            <div class="btc-strong">{{ row.net }}</div>
                                            <div class="btc-small" :class="row.netTone === 'tertiary' ? 'btc-tone-tertiary' : 'btc-muted'">{{ row.netNote }}</div>
                                        </td>
                                        <td>
                                            <div class="btc-strong" :class="row.stageTone === 'error' ? 'btc-tone-error' : (row.stageTone === 'primary' ? 'btc-tone-text' : '')">
                                                <span v-if="row.stageIcon" class="material-symbols-outlined">{{ row.stageIcon }}</span>{{ row.stage }}
                                            </div>
                                            <div class="btc-small" :class="row.stageTone === 'secondary' ? 'btc-tone-secondary' : 'btc-muted'">{{ row.stageNote }}</div>
                                        </td>
                                        <td>
                                            <span class="btc-status" :class="`btc-status--${row.statusTone}`">{{ row.status }}</span>
                                        </td>
                                        <td class="btc-table__end">
                                            <button type="button" class="btc-action-btn" :class="`btc-action-btn--${row.actionTone}`">{{ row.action }}</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="btc-table-card__foot">
                            <span class="btc-muted btc-small">Showing <strong class="btc-strong">1–5</strong> of <strong class="btc-strong">24</strong> batches</span>
                            <div class="btc-pagination">
                                <button type="button" class="btc-page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                                <button type="button" class="btc-page-btn btc-page-btn--active">1</button>
                                <button type="button" class="btc-page-btn">2</button>
                                <button type="button" class="btc-page-btn">3</button>
                                <button type="button" class="btc-page-btn">4</button>
                                <button type="button" class="btc-page-btn">5</button>
                                <button type="button" class="btc-page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                            </div>
                        </div>
                    </div>

                    <!-- Insight banner -->
                    <div class="btc-insight">
                        <div class="btc-insight__left">
                            <span class="material-symbols-outlined">hub</span>
                            <div>
                                <div class="btc-strong btc-small">Decoupled Aggregation Architecture</div>
                                <div class="btc-muted btc-small">Individual farmer payments remain anchored to intake weights while batch outturn governs commercial sales.</div>
                            </div>
                        </div>
                        <span class="btc-mono btc-strong btc-tone-text btc-small">Avg Regional Yield: 94.2%</span>
                    </div>
                </div>

                <!-- ── Right column: Batch Inspection Dossier ───────────── -->
                <div class="btc-col-side">
                    <div class="btc-card">
                        <div class="btc-dossier__head">
                            <div>
                                <div class="btc-dossier__title-row">
                                    <span class="btc-mono btc-tone-text btc-title-lg">BAT-UG-2048</span>
                                    <span class="btc-status btc-status--secondary">Processing</span>
                                </div>
                                <p class="btc-field-value">Uganda Robusta (Nganda Traditional Selection)</p>
                            </div>
                            <div class="btc-dossier__head-actions">
                                <button type="button" class="btc-icon-btn" title="Export Dossier PDF"><span class="material-symbols-outlined">picture_as_pdf</span></button>
                                <button type="button" class="btc-icon-btn" title="Batch Settings"><span class="material-symbols-outlined">more_vert</span></button>
                            </div>
                        </div>

                        <div class="btc-quick-actions">
                            <button type="button" class="btc-quick-btn btc-quick-btn--primary"><span class="material-symbols-outlined">update</span><span>Next Stage</span></button>
                            <button type="button" class="btc-quick-btn btc-quick-btn--secondary"><span class="material-symbols-outlined">biotech</span><span>Record Lab</span></button>
                            <button type="button" class="btc-quick-btn btc-quick-btn--fixed"><span class="material-symbols-outlined">post_add</span><span>Create Lot</span></button>
                        </div>

                        <div class="btc-dossier__section">
                            <span class="btc-eyebrow-sm">Traceability Chain Lineage</span>
                            <div class="btc-lineage">
                                <span class="btc-strong">4 Collections</span>
                                <span class="btc-muted">→</span>
                                <span class="btc-chip btc-chip--fixed btc-mono">BAT-UG-2048</span>
                                <span class="btc-muted">→</span>
                                <span class="btc-muted btc-italic">LOT-UG-2048 (Pending)</span>
                            </div>
                        </div>

                        <div class="btc-dossier__section">
                            <div class="btc-dossier__section-head">
                                <span class="btc-card__title-plain">Mass Balance &amp; Milling Outturn</span>
                                <span class="btc-mono btc-tone-text btc-small btc-strong">95.5% Yield</span>
                            </div>
                            <div class="btc-bar">
                                <div class="btc-bar__seg btc-bar__seg--primary" style="width: 95.5%" title="Usable Outturn: 9,550 kg"></div>
                                <div class="btc-bar__seg btc-bar__seg--secondary" style="width: 3.5%" title="Hulling/Moisture Loss: 350 kg"></div>
                                <div class="btc-bar__seg btc-bar__seg--error" style="width: 1%" title="Rejected Defects: 100 kg"></div>
                            </div>
                            <div class="btc-stat-grid">
                                <div class="btc-stat-box"><span class="btc-stat-box__label">Intake Gross Input</span><span class="btc-mono btc-strong">10,000 kg</span><span class="btc-muted btc-small">10.00 MT aggregated</span></div>
                                <div class="btc-stat-box"><span class="btc-stat-box__label">Current Net Usable</span><span class="btc-mono btc-strong btc-tone-text">9,550 kg</span><span class="btc-tone-text btc-small btc-strong">9.55 MT commercial</span></div>
                            </div>
                            <div class="btc-loss-row">
                                <span>Milling &amp; Moisture Loss: <strong>-350 kg (3.5%)</strong></span>
                                <span>Defects Removed: <strong>-100 kg (1.0%)</strong></span>
                            </div>
                        </div>

                        <div class="btc-dossier__section">
                            <div class="btc-dossier__section-head">
                                <span class="btc-card__title-plain">Aggregated Collections (4)</span>
                                <span class="btc-link">View Waybills</span>
                            </div>
                            <div class="btc-panel">
                                <div v-for="c in collections" :key="c.code" class="btc-coll-row">
                                    <div>
                                        <div class="btc-mono btc-strong btc-small">{{ c.code }}</div>
                                        <div class="btc-muted btc-small">{{ c.farm }}</div>
                                    </div>
                                    <span class="btc-mono btc-strong btc-small">{{ c.weight }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="btc-dossier__section">
                            <span class="btc-card__title-plain">Processing Timeline &amp; Milestones</span>
                            <span class="btc-tone-secondary btc-small btc-strong">Method: Natural &amp; Raised Sun-Bed Solar Drying</span>
                            <div class="btc-timeline">
                                <div v-for="(entry, i) in timeline" :key="i" class="btc-timeline__item" :class="{ 'btc-timeline__item--muted': entry.tone === 'muted' }">
                                    <span class="btc-timeline__dot" :class="`btc-timeline__dot--${entry.tone}`"></span>
                                    <div class="btc-strong btc-small">{{ entry.date }} • {{ entry.title }}</div>
                                    <div class="btc-small" :class="entry.tone === 'secondary' ? 'btc-tone-secondary' : 'btc-muted'">{{ entry.note }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="btc-panel btc-quality-panel">
                            <div class="btc-dossier__section-head">
                                <span class="btc-card__title-plain">Quality Audit Parameters</span>
                                <span class="btc-chip btc-chip--fixed">Grade 1 Clean</span>
                            </div>
                            <div class="btc-panel--grid2">
                                <div class="btc-field-box"><span class="btc-eyebrow-sm">Screen Size</span><span class="btc-strong btc-small">Screen 18+ (92.4%)</span></div>
                                <div class="btc-field-box"><span class="btc-eyebrow-sm">Moisture Spec</span><span class="btc-tone-text btc-strong btc-small">11.4% (Compliance)</span></div>
                                <div class="btc-field-box"><span class="btc-eyebrow-sm">Defect Count</span><span class="btc-strong btc-small">2 per 350g sample</span></div>
                                <div class="btc-field-box"><span class="btc-eyebrow-sm">Preliminary Cup</span><span class="btc-tone-secondary btc-strong btc-small">84.5 CQI Score</span></div>
                            </div>
                            <div class="btc-quality-actions">
                                <button type="button" class="btc-mini-btn">+ Add Assessment</button>
                                <button type="button" class="btc-mini-btn">Upload Certificate</button>
                            </div>
                        </div>

                        <div class="btc-storage">
                            <span class="material-symbols-outlined btc-tone-text">warehouse</span>
                            <div>
                                <div class="btc-strong btc-small">Kampala Central Dry Mill • Silo B-14</div>
                                <div class="btc-muted btc-small">Ambient: 21°C • Humidity: 58% • Inspected: 19 Sep 2026</div>
                            </div>
                        </div>

                        <div class="btc-allocation-box">
                            <div class="btc-allocation-box__head"><span class="material-symbols-outlined">verified_user</span><span class="btc-strong btc-small">Lot Allocation Protocol</span></div>
                            <p class="btc-allocation-box__text">9.55 MT net commercial grade verified. Ready to allocate into export-ready Lots with digital phytosanitary ledger linkage.</p>
                            <button type="button" class="btc-btn btc-btn--primary btc-btn--block"><span class="material-symbols-outlined">check_circle</span> Create Commercial Lot from Batch</button>
                        </div>

                        <div class="btc-ai-box">
                            <div class="btc-ai-box__head">
                                <span class="material-symbols-outlined btc-tone-text">auto_awesome</span>
                                <span class="btc-strong btc-small">Bean Origin AI Insights</span>
                            </div>
                            <p class="btc-ai-box__text">Batch <strong class="btc-mono btc-strong">BAT-UG-2048</strong> has achieved <strong class="btc-tone-text">95.5% milling yield</strong>, outperforming the regional average (93.8%). Moisture level of 11.4% is within safe export transport window. Recommend completing destoning before final lot certification.</p>
                            <div class="btc-ai-box__actions">
                                <button type="button" class="btc-ai-chip">Check Moisture Compliance</button>
                                <button type="button" class="btc-ai-chip">Estimate Commercial Lot Price</button>
                                <button type="button" class="btc-ai-chip">Simulate Lot Split</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── "Create Batch" — the same real modal reused from the
             Inventory page, not a rebuilt dummy multi-select form. ────── -->
        <AddBatchModal
            v-model="addBatchOpen"
            :process-options="processOptions"
            :variety-options="coffeeTypeOptions"
            :drying-method-options="dryingMethodOptions"
            :currency-options="currencyOptions"
            :milling-options="millingOptions"
        />
    </MainLayout>
</template>

<style scoped>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

.btc-page { font-family: var(--dp-font-sans); display: flex; flex-direction: column; gap: 20px; color: var(--dp-on-surface); }
.btc-mono { font-family: var(--dp-font-mono); }
.btc-muted { color: var(--dp-on-surface-variant); }
.btc-strong { font-weight: 700; color: var(--dp-on-surface); }
.btc-small { font-size: 11px; }
.btc-italic { font-style: italic; }
.btc-tone-text { color: var(--dp-primary); }
.btc-tone-secondary { color: var(--dp-on-secondary-container); }
.btc-tone-tertiary { color: #4B5578; }
.btc-tone-error { color: var(--dp-error); }

/* Hero */
.btc-hero { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.btc-breadcrumb { display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); margin-bottom: 4px; }
.btc-breadcrumb .material-symbols-outlined { font-size: 14px; }
.btc-breadcrumb__current { color: var(--dp-primary); font-weight: 700; }
.btc-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.015em; color: var(--dp-on-surface); margin: 0; }
.btc-subtitle { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; line-height: 1.5; max-width: 62ch; }
.btc-hero__actions { display: flex; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }

.btc-btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border-radius: 6px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); text-decoration: none; transition: opacity .12s ease, background .12s ease; }
.btc-btn .material-symbols-outlined { font-size: 16px; }
.btc-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.btc-btn--primary:hover { opacity: .9; }
.btc-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.btc-btn--muted:hover { background: var(--dp-surface-container-highest); }
.btc-btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.btc-btn--secondary:hover { opacity: .9; }
.btc-btn--block { width: 100%; justify-content: center; }

/* Stepper */
.btc-stepper { background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px; }
.btc-stepper__track { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.btc-stepper__node { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--dp-on-surface-variant); }
.btc-stepper__dot { width: 7px; height: 7px; border-radius: 999px; background: var(--dp-outline-variant); }
.btc-stepper__arrow { font-size: 14px; color: var(--dp-outline); }
.btc-stepper__badge { display: inline-flex; align-items: center; gap: 10px; background: var(--dp-primary); color: var(--dp-on-primary); padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.btc-stepper__badge-dot { width: 7px; height: 7px; border-radius: 999px; background: var(--dp-on-primary); animation: btc-pulse 1.8s ease-in-out infinite; flex-shrink: 0; }
@keyframes btc-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
.btc-stepper__rule { font-size: 11px; color: var(--dp-on-surface-variant); line-height: 1.5; margin: 0; max-width: 75ch; }
.btc-stepper__rule strong { color: var(--dp-on-surface); font-weight: 700; }

/* KPI row */
.btc-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
.btc-kpi { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 14px; display: flex; flex-direction: column; gap: 6px; }
.btc-kpi__top { display: flex; align-items: center; justify-content: space-between; }
.btc-kpi__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.btc-kpi__top .material-symbols-outlined { font-size: 17px; }
.btc-kpi__value { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
.btc-kpi__value > .btc-mono:first-child { font-size: 1.375rem; font-weight: 800; color: var(--dp-on-surface); }
.btc-kpi__unit { font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant); }
.btc-kpi__trailing { font-size: 10.5px; font-weight: 700; color: var(--dp-on-surface-variant); display: inline-flex; align-items: center; gap: 2px; }
.btc-kpi__trailing .material-symbols-outlined { font-size: 13px; }
.btc-kpi__note { font-size: 10px; color: var(--dp-on-surface-variant); margin-top: 2px; }

/* Filters */
.btc-filters { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.btc-search { position: relative; display: flex; align-items: center; max-width: 420px; }
.btc-search .material-symbols-outlined { position: absolute; left: 10px; font-size: 17px; color: var(--dp-on-surface-variant); }
.btc-search input { width: 100%; padding: 8px 60px 8px 34px; background: var(--dp-surface-container-low); border: none; border-radius: 6px; font-size: 12px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); outline: none; }
.btc-search__badge { position: absolute; right: 10px; font-family: var(--dp-font-mono); font-size: 10px; padding: 2px 6px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); border-radius: 4px; }
.btc-filters__row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.btc-select { padding: 7px 10px; background: var(--dp-surface-container-low); color: var(--dp-on-surface); font-size: 11.5px; font-weight: 600; border: none; border-radius: 6px; font-family: var(--dp-font-sans); cursor: pointer; }
.btc-view-toggle { display: flex; gap: 4px; background: var(--dp-surface-container-low); padding: 3px; border-radius: 6px; margin-left: auto; }
.btc-view-toggle__opt { display: inline-flex; align-items: center; padding: 6px 8px; border-radius: 5px; border: none; background: transparent; color: var(--dp-on-surface-variant); cursor: pointer; }
.btc-view-toggle__opt .material-symbols-outlined { font-size: 16px; }
.btc-view-toggle__opt--active { background: var(--dp-surface-container-lowest); color: var(--dp-primary); box-shadow: 0 1px 2px rgba(18, 21, 22, 0.08); }

/* Two-column grid */
.btc-grid { display: grid; grid-template-columns: minmax(0, 7fr) minmax(320px, 5fr); gap: 18px; align-items: start; }
.btc-col-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.btc-col-side { display: flex; flex-direction: column; gap: 16px; }
@media (max-width: 1180px) { .btc-grid { grid-template-columns: 1fr; } }

/* Table */
.btc-table-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); overflow: hidden; }
.btc-table-card__head { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: var(--dp-surface-container-low); gap: 10px; flex-wrap: wrap; }
.btc-table-card__title { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: .8125rem; color: var(--dp-on-surface); }
.btc-sort { display: flex; align-items: center; gap: 6px; font-family: var(--dp-font-mono); font-size: 11px; color: var(--dp-on-surface-variant); }
.btc-sort__value { font-weight: 700; color: var(--dp-on-surface); }
.btc-table-wrap { overflow-x: hidden; }
.btc-table { width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: 11.5px; }
.btc-table thead tr { background: var(--dp-surface-container-low); }
.btc-table th { padding: 9px 8px; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); overflow-wrap: break-word; }
.btc-table td { padding: 10px 8px; border-top: 1px solid var(--dp-outline-variant); vertical-align: middle; overflow-wrap: break-word; }
.btc-table__row { transition: background .12s ease; }
.btc-table__row:hover { background: var(--dp-surface-container-low); }
.btc-table__row--active { background: color-mix(in srgb, var(--dp-primary) 6%, transparent); }
.btc-table__dot { display: inline-block; width: 5px; height: 5px; border-radius: 999px; background: var(--dp-primary); margin-right: 5px; }
.btc-table__end { text-align: right; }
.btc-table-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; background: var(--dp-surface-container-low); flex-wrap: wrap; }

.btc-strong .material-symbols-outlined { font-size: 13px; vertical-align: -2px; margin-right: 2px; }

.btc-pagination { display: flex; align-items: center; gap: 3px; }
.btc-page-btn { min-width: 24px; height: 24px; padding: 0 4px; border-radius: 5px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 11px; font-family: var(--dp-font-mono); font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.btc-page-btn .material-symbols-outlined { font-size: 16px; }
.btc-page-btn:hover:not(:disabled) { background: var(--dp-surface-container-high); }
.btc-page-btn:disabled { opacity: .4; cursor: default; }
.btc-page-btn--active { background: var(--dp-primary); color: var(--dp-on-primary); }

.btc-action-btn { padding: 5px 8px; border-radius: 5px; border: none; font-size: 10px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); width: 100%; }
.btc-action-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.btc-action-btn--container { background: var(--dp-primary-container); color: var(--dp-on-primary); }
.btc-action-btn--muted { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.btc-action-btn--muted:hover { background: var(--dp-surface-container-high); }

/* Icon buttons */
.btc-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); cursor: pointer; transition: background .12s ease, color .12s ease; }
.btc-icon-btn:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.btc-icon-btn .material-symbols-outlined { font-size: 16px; }

/* Chips / status */
.btc-chip { display: inline-flex; align-items: center; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-family: var(--dp-font-mono); font-weight: 700; background: var(--dp-surface-container); color: var(--dp-on-surface); white-space: nowrap; }
.btc-chip--fixed { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.btc-status { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 999px; font-size: 9.5px; font-weight: 700; white-space: nowrap; }
.btc-status--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.btc-status--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.btc-status--tertiary { background: #DAE2FD; color: #333B54; }
.btc-status--error { background: var(--dp-error-container); color: var(--dp-error); }
.btc-status--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

/* Insight banner */
.btc-insight { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px 16px; }
.btc-insight__left { display: flex; align-items: center; gap: 12px; }
.btc-insight__left .material-symbols-outlined { width: 38px; height: 38px; border-radius: 8px; background: var(--dp-surface-container-lowest); color: var(--dp-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }

/* Dossier card */
.btc-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 18px; display: flex; flex-direction: column; gap: 16px; }
.btc-card__title-plain { font-size: .8125rem; font-weight: 800; color: var(--dp-on-surface); }
.btc-eyebrow-sm { display: block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dp-on-surface-variant); margin-bottom: 2px; }
.btc-field-value { font-size: 12.5px; font-weight: 600; color: var(--dp-on-surface); margin: 2px 0 0; }

.btc-dossier__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.btc-dossier__title-row { display: flex; align-items: center; gap: 8px; }
.btc-title-lg { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.01em; }
.btc-dossier__head-actions { display: flex; gap: 4px; flex-shrink: 0; }
.btc-dossier__section { display: flex; flex-direction: column; gap: 8px; }
.btc-dossier__section-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

.btc-quick-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.btc-quick-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; padding: 10px 4px; border-radius: 8px; border: none; font-size: 10.5px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.btc-quick-btn .material-symbols-outlined { font-size: 17px; }
.btc-quick-btn--primary { background: var(--dp-surface-container-low); color: var(--dp-primary); }
.btc-quick-btn--primary:hover { background: var(--dp-surface-container-high); }
.btc-quick-btn--secondary { background: var(--dp-surface-container-low); color: var(--dp-on-secondary-container); }
.btc-quick-btn--secondary:hover { background: var(--dp-surface-container-high); }
.btc-quick-btn--fixed { background: var(--dp-primary-container); color: var(--dp-on-primary); }
.btc-quick-btn--fixed:hover { opacity: .9; }

.btc-lineage { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; background: var(--dp-surface-container-low); padding: 10px 12px; border-radius: 8px; font-size: 11px; }

.btc-bar { width: 100%; height: 10px; border-radius: 999px; background: var(--dp-surface-container-high); overflow: hidden; display: flex; }
.btc-bar__seg--primary { background: var(--dp-primary); }
.btc-bar__seg--secondary { background: var(--dp-secondary); }
.btc-bar__seg--error { background: color-mix(in srgb, var(--dp-error) 75%, transparent); }
.btc-stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 4px; }
.btc-stat-box { display: flex; flex-direction: column; gap: 2px; padding: 8px 10px; background: var(--dp-surface-container-low); border-radius: 7px; }
.btc-stat-box__label { font-size: 9.5px; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); }
.btc-loss-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 10px; background: var(--dp-surface-container); border-radius: 7px; font-size: 10.5px; color: var(--dp-on-surface-variant); flex-wrap: wrap; }
.btc-loss-row strong { color: var(--dp-on-surface); font-weight: 700; }

.btc-link { font-size: 11px; font-weight: 700; color: var(--dp-primary); cursor: pointer; }

.btc-panel { background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; }
.btc-coll-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 6px 4px; }
.btc-coll-row + .btc-coll-row { border-top: 1px solid var(--dp-outline-variant); }

.btc-timeline { position: relative; display: flex; flex-direction: column; gap: 12px; padding-left: 14px; border-left: 2px solid var(--dp-outline-variant); margin-top: 4px; }
.btc-timeline__item--muted { opacity: .55; }
.btc-timeline__item { position: relative; }
.btc-timeline__dot { position: absolute; left: -19px; top: 3px; width: 8px; height: 8px; border-radius: 999px; background: var(--dp-primary); }
.btc-timeline__dot--secondary { background: var(--dp-secondary); }
.btc-timeline__dot--muted { background: var(--dp-outline); }

.btc-quality-panel { display: flex; flex-direction: column; gap: 10px; }
.btc-panel--grid2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.btc-field-box { background: var(--dp-surface-container-lowest); border-radius: 6px; padding: 8px; display: flex; flex-direction: column; gap: 2px; }
.btc-quality-actions { display: flex; gap: 8px; }
.btc-mini-btn { flex: 1; padding: 7px; border-radius: 6px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-primary); font-size: 10.5px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.btc-mini-btn:hover { background: var(--dp-surface-container-highest); }

.btc-storage { display: flex; align-items: flex-start; gap: 10px; background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; }
.btc-storage .material-symbols-outlined { font-size: 20px; margin-top: 1px; }

.btc-allocation-box { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 8px; }
.btc-allocation-box__head { display: flex; align-items: center; gap: 6px; }
.btc-allocation-box__text { font-size: 11.5px; line-height: 1.5; margin: 0; }
.btc-allocation-box .btc-btn--primary { margin-top: 2px; }

.btc-ai-box { background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.btc-ai-box__head { display: flex; align-items: center; gap: 6px; }
.btc-ai-box__text { font-size: 12px; color: var(--dp-on-surface); line-height: 1.55; margin: 0; }
.btc-ai-box__actions { display: flex; flex-wrap: wrap; gap: 6px; }
.btc-ai-chip { padding: 6px 10px; border-radius: 6px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-primary); font-size: 10px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.btc-ai-chip:hover { background: var(--dp-surface-container-highest); }

@media (max-width: 640px) {
    .btc-hero__actions { width: 100%; }
    .btc-hero__actions .btc-btn { flex: 1; justify-content: center; }
    .btc-panel--grid2 { grid-template-columns: 1fr; }
    .btc-quick-actions { grid-template-columns: 1fr 1fr 1fr; }
}
</style>
