<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import AddFarmCollectionModal from '@/Components/Modals/AddFarmCollectionModal.vue';
import AddBatchModal from '@/Components/Modals/AddBatchModal.vue';
import AddLotModal from '@/Components/Modals/AddLotModal.vue';
import {
    ArrowDown, Close, Plus, UploadFilled, WarningFilled,
} from '@element-plus/icons-vue';

/* ── The shell every inventory tab page (Farm Collections, Batches, Lots,
   Tokenised Lots) renders inside — structural/visual port of the uploaded
   "Inventory" mockup (code.html / DESIGN.md), restyled with this page's
   own literal-hex UI.md tokens (see .st-page below) rather than the
   mockup's own palette.

   Everything in this file is real data passed in from
   StoreController::stageSummary() — no fabricated SLA/yield percentages:
   a farm collection's own `status` column tracks batched-vs-pending;
   batches/lots don't have an equivalent status, so "moved to next stage"
   is derived from their real pivot links (batchFarmCollections/
   lotBatches) and blockchain relation instead. ────────────────────────── */
const props = defineProps({
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
    activeTab: { type: String, required: true }, // 'collections' | 'batches' | 'lots' | 'tokenised'
    farmCollections: { type: Array, default: () => [] },
    batches: { type: Array, default: () => [] },
    lots: { type: Array, default: () => [] },
    stageProgress: { type: Array, default: () => [] },
    movementLedger: { type: Array, default: () => [] },
    chainLineage: { type: Array, default: null },
    inventoryHealth: { type: Object, default: () => ({}) },
    processOptions: { type: Array, default: () => [] },
    dryingMethodOptions: { type: Array, default: () => [] },
    millingOptions: { type: Array, default: () => [] },
    coffeeTypeOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    coffeeGradeOptions: { type: Array, default: () => [] },
    packagingTypeOptions: { type: Array, default: () => [] },
    originOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    currencyCountries: { type: Object, default: () => ({}) },
    flavorOptions: { type: Array, default: () => [] },
    bodyOptions: { type: Array, default: () => [] },
    acidityOptions: { type: Array, default: () => [] },
    aftertasteOptions: { type: Array, default: () => [] },
    aromaOptions: { type: Array, default: () => [] },
});

/* ── A stage's real weight is shown in whichever unit keeps it legible —
   a small real value (e.g. a single 40kg lot) rounds down to "0.0" in
   metric tons and reads as if nothing were there, so anything under
   1 MT is shown in KG instead of being silently truncated to zero. ── */
function fmtVolume(kg) {
    const value = Number(kg || 0);
    if (value > 0 && value < 1000) {
        return { value: value.toLocaleString(undefined, { maximumFractionDigits: 0 }), unit: 'KG' };
    }
    return { value: (value / 1000).toLocaleString(undefined, { maximumFractionDigits: 1 }), unit: 'MT' };
}

const currentTab = computed(() => props.stageProgress.find((tab) => tab.key === props.activeTab));

/* ── Header action — a real export, not the mockup's decorative button. ── */
function exportLedgerCsv() {
    const header = ['Type', 'ID', 'Coffee', 'Quantity (kg)', 'Status', 'Created'];
    const rows = [
        ...props.farmCollections.map((c) => ['Farm Collection', c.collection_code, [c.coffee_type, c.variety].filter(Boolean).join(' '), c.quantity, c.status, c.created_at]),
        ...props.batches.map((b) => ['Batch', b.batch_number, b.variety, b.net_weight_kg, b.status, b.created_at]),
        ...props.lots.map((l) => ['Lot', l.lot_number, [l.variety, l.grade].filter(Boolean).join(' '), l.net_weight_kg, l.blockchain ? 'tokenised' : l.status, l.created_at]),
    ];

    const csv = [header, ...rows]
        .map((row) => row.map((value) => `"${String(value ?? '').replace(/"/g, '""')}"`).join(','))
        .join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `bean-origin-inventory-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}

/* ── Hero "Register New ▾" dropdown — opens the matching independent
   modal component instead of navigating away. ────────────────────────── */
const addCollectionOpen = ref(false);
const addBatchOpen = ref(false);
const addLotOpen = ref(false);

function handleRegisterCommand(command) {
    if (command === 'collection') addCollectionOpen.value = true;
    else if (command === 'batch') addBatchOpen.value = true;
    else if (command === 'lot') addLotOpen.value = true;
}

/* ── Shared with StoreLayout's header buttons via v-model ─────────────── */
const storeDialogOpen = ref(false);
const importResultVisible = ref(Boolean(props.importResult));
</script>

<template>
    <StoreLayout
        title="Inventory"
        :store="store"
        :status-options="statusOptions"
        :import-result="importResult"
        v-model:store-dialog-open="storeDialogOpen"
        v-model:import-result-visible="importResultVisible"
    >
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="st-page">
            <!-- ── Import results ───────────────────────────────────────── -->
            <div v-if="importResult && importResultVisible">
                <div class="st-import-panel" :class="{ 'st-import-panel--warn': importResult.errors.length }">
                    <div class="st-import-panel__icon">
                        <el-icon :size="16"><WarningFilled v-if="importResult.errors.length" /><UploadFilled v-else /></el-icon>
                    </div>
                    <div class="st-import-panel__body">
                        <div class="st-import-panel__title">
                            {{ importResult.imported }} item{{ importResult.imported === 1 ? '' : 's' }} imported
                            <span v-if="importResult.errors.length">, {{ importResult.errors.length }} row{{ importResult.errors.length === 1 ? '' : 's' }} skipped</span>
                        </div>
                        <ul v-if="importResult.errors.length" class="st-import-panel__list">
                            <li v-for="err in importResult.errors" :key="err.row">
                                Row {{ err.row }}: {{ err.errors.join(' ') }}
                            </li>
                        </ul>
                    </div>
                    <button type="button" class="st-import-panel__close" aria-label="Dismiss" @click="importResultVisible = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </div>

            <div class="st-verified">
                <!-- ── Page header ───────────────────────────────────────── -->
                <div class="st-hero">
                    <div class="st-hero__text">
                        <div class="st-hero__title-row">
                            <h1 class="st-title">Inventory</h1>
                            <span class="st-hero__badge">Physical &amp; Tokenised</span>
                        </div>
                        <p class="st-subtitle">Farm-to-token custody tracking</p>
                    </div>
                    <div class="st-hero__actions">
                        <button type="button" class="st-btn-outline" @click="exportLedgerCsv">
                            <span class="material-symbols-outlined">download</span> Export Ledger (CSV)
                        </button>
                        <el-dropdown trigger="click" @command="handleRegisterCommand">
                            <button type="button" class="st-btn-primary">
                                <el-icon><Plus /></el-icon> Register New <el-icon class="st-caret"><ArrowDown /></el-icon>
                            </button>
                            <template #dropdown>
                                <el-dropdown-menu class="st-register-menu">
                                    <el-dropdown-item command="collection">Farm Collection</el-dropdown-item>
                                    <el-dropdown-item command="batch">Batch</el-dropdown-item>
                                    <el-dropdown-item command="lot">Lot</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </div>
                </div>

                <!-- ── Stage cards — every tab page is always linked here. ── -->
                <nav class="st-nav-cards">
                    <Link
                        v-for="stage in stageProgress"
                        :key="stage.key"
                        :href="route(stage.route)"
                        class="st-nav-card"
                    >
                        <div class="st-nav-card__top">
                            <div class="st-nav-card__icon"><span class="material-symbols-outlined">{{ stage.icon }}</span></div>
                            <span class="st-nav-card__records">{{ stage.records }} Record{{ stage.records === 1 ? '' : 's' }}</span>
                        </div>
                        <div class="st-nav-card__value">{{ fmtVolume(stage.volume_kg).value }} <span class="st-nav-card__unit">{{ fmtVolume(stage.volume_kg).unit }}</span></div>
                        <div class="st-nav-card__label">{{ stage.label }}</div>
                        <div v-if="stage.ready !== null" class="st-nav-card__bar"><div class="st-nav-card__bar-fill" :style="{ width: stage.progress + '%' }" /></div>
                        <div v-if="stage.ready !== null" class="st-nav-card__ready">
                            <span>{{ stage.ready_label }}:</span>
                            <strong>{{ stage.ready }}</strong>
                        </div>
                        <p v-if="stage.note" class="st-nav-card__note">{{ stage.note }}</p>
                        <span class="st-nav-card__link">View {{ stage.label }} <span class="material-symbols-outlined">arrow_forward</span></span>
                    </Link>
                </nav>

                <!-- ── Active tab's content ─────────────────────────────── -->
                <div class="st-body">
                    <div class="st-list-toolbar">
                        <h2 class="st-list-toolbar__title">{{ currentTab?.label }}</h2>
                    </div>

                    <slot />
                </div>

                <!-- ── Bottom triptych ───────────────────────────────────── -->
                <div class="st-triptych">
                    <div class="st-panel">
                        <div class="st-panel__head">
                            <span class="material-symbols-outlined">sync_alt</span>
                            <h3>Recent Movement Ledger</h3>
                        </div>
                        <div v-if="movementLedger.length" class="st-timeline">
                            <div v-for="(event, i) in movementLedger" :key="i" class="st-timeline__item">
                                <div class="st-timeline__head">
                                    <span class="st-timeline__label">{{ event.label }}</span>
                                    <span class="st-timeline__ago">{{ event.ago }}</span>
                                </div>
                                <p class="st-timeline__detail">{{ event.detail }}</p>
                            </div>
                        </div>
                        <p v-else class="st-panel__empty">No inventory movement recorded yet.</p>
                    </div>

                    <div class="st-panel">
                        <div class="st-panel__head">
                            <span class="material-symbols-outlined">share_location</span>
                            <h3>Chain Lineage Inspector</h3>
                        </div>
                        <div v-if="chainLineage && chainLineage.length" class="st-chain">
                            <div v-for="(link, i) in chainLineage" :key="i" class="st-chain__item">
                                <span class="st-chain__icon material-symbols-outlined">{{ link.icon }}</span>
                                <div class="st-chain__body">
                                    <div class="st-chain__title">{{ link.title }}</div>
                                    <div class="st-chain__sub">{{ link.sub }}</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="st-panel__empty">No traceable chain yet — register a farm collection to begin one.</p>
                    </div>

                    <div class="st-panel">
                        <div class="st-panel__head">
                            <span class="material-symbols-outlined">speed</span>
                            <h3>Inventory Health</h3>
                        </div>
                        <div class="st-health-grid">
                            <div class="st-health-box">
                                <div class="st-health-box__label">Avg Quality Score</div>
                                <div class="st-health-box__value">{{ inventoryHealth.avg_quality_score ?? '—' }}</div>
                            </div>
                            <div class="st-health-box">
                                <div class="st-health-box__label">Avg Moisture</div>
                                <div class="st-health-box__value">{{ inventoryHealth.avg_moisture_content != null ? inventoryHealth.avg_moisture_content + '%' : '—' }}</div>
                            </div>
                            <div class="st-health-box">
                                <div class="st-health-box__label">Total Active Value</div>
                                <div class="st-health-box__value">${{ Number(inventoryHealth.total_value || 0).toLocaleString() }}</div>
                            </div>
                            <div class="st-health-box">
                                <div class="st-health-box__label">Tokenised Value</div>
                                <div class="st-health-box__value">${{ Number(inventoryHealth.tokenised_value || 0).toLocaleString() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Register New ▾ modals ─────────────────────────────────────── -->
        <AddFarmCollectionModal
            v-model="addCollectionOpen"
            :coffee-type-options="coffeeTypeOptions"
            :harvest-season-options="harvestSeasonOptions"
            :currency-options="currencyOptions"
        />
        <AddBatchModal v-model="addBatchOpen" :process-options="processOptions" :variety-options="coffeeTypeOptions" :drying-method-options="dryingMethodOptions" :currency-options="currencyOptions" :milling-options="millingOptions" />
        <AddLotModal
            v-model="addLotOpen"
            :process-options="processOptions"
            :coffee-grade-options="coffeeGradeOptions"
            :packaging-type-options="packagingTypeOptions"
            :variety-options="coffeeTypeOptions"
            :origin-options="originOptions"
            :currency-options="currencyOptions"
            :currency-countries="currencyCountries"
            :flavor-options="flavorOptions"
            :body-options="bodyOptions"
            :acidity-options="acidityOptions"
            :aftertaste-options="aftertasteOptions"
            :aroma-options="aromaOptions"
        />
    </StoreLayout>
</template>

<style scoped>
/* Same literal-hex UI.md theme as before (see reference_ui_md_design_system
   memory) — this block is duplicated onto every page that renders inside
   .st-page (the four tab pages' own scoped styles rely on these custom
   properties cascading through the real DOM). */
.st-page {
    --primary: #000000;
    --primary-container: #262626;
    --on-primary-container: #F1F2F3;
    --secondary: #7EE787;
    --secondary-container: #E5FAE7;
    --on-secondary-container: #2F6B35;
    --tertiary: #191818;
    --tertiary-container: #2e2c2c;
    --on-tertiary-container: #979393;
    --error: #F85149;
    --error-container: #FEEDED;
    --on-error-container: #C6413A;
    --surface: #ffffff;
    --surface-container-lowest: #ffffff;
    --surface-container-low: #F5F6F7;
    --surface-container: #F1F2F3;
    --surface-container-high: #E5E7EB;
    --on-surface: #121516;
    --on-surface-variant: #4B5457;
    --outline: #6F7677;
    --outline-variant: #E5E7EB;
    --card-border: #E5E7EB;
    --card-radius: 6px;
    --sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-family: var(--sans);
    color: var(--on-surface);
    min-height: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Buttons ───────────────────────────────────────────────────────────── */
.st-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 16px;
    border: none;
    border-radius: 6px;
    background: var(--primary);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, opacity .15s ease;
    white-space: nowrap;
}
.st-btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); }
.st-btn-primary:disabled { opacity: 0.6; cursor: default; transform: none; box-shadow: none; }
.st-caret { font-size: 11px; margin-left: -2px; }

/* ── "Register New ▾" dropdown menu ───────────────────────────────────── */
.st-register-menu.el-dropdown-menu { border-radius: 6px; border: 1px solid var(--card-border); padding: 4px; }
.st-register-menu :deep(.el-dropdown-menu__item) {
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: var(--on-surface);
    padding: 8px 12px;
}
.st-register-menu :deep(.el-dropdown-menu__item:hover) { background: var(--surface-container-low); color: var(--on-surface); }

/* ── Editorial hero ────────────────────────────────────────────────────── */
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }
.st-verified { display: flex; flex-direction: column; gap: 28px; }
.st-hero { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.st-hero__text { display: flex; flex-direction: column; gap: 8px; max-width: 640px; }
.st-hero__title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.st-hero__badge {
    display: inline-flex; align-items: center; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px;
    background: var(--secondary-container); color: var(--on-secondary-container);
}
.st-title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    color: var(--primary);
    margin: 0;
}
.st-subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 400; color: var(--on-surface-variant); margin: 0; max-width: 620px; }
.st-hero__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; flex-wrap: wrap; }
.st-btn-outline {
    display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 6px;
    background: var(--surface-container-high); color: var(--on-surface); border: none; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: background .15s ease; white-space: nowrap;
}
.st-btn-outline:hover { background: var(--surface-container); }
.st-btn-outline .material-symbols-outlined { font-size: 17px; }

@media (max-width: 575.98px) {
    .st-title { font-size: 1.25rem; line-height: 1.6rem; }
}

/* ── Import results panel ─────────────────────────────────────────────── */
.st-import-panel {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: var(--secondary-container);
    border-radius: 6px;
    padding: 14px 16px;
}
.st-import-panel--warn { background: #fef3c7; }
.st-import-panel__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    color: var(--on-secondary-container);
    flex-shrink: 0;
}
.st-import-panel--warn .st-import-panel__icon { color: #92400e; }
.st-import-panel__body { flex: 1; min-width: 0; }
.st-import-panel__title { font-size: 13px; font-weight: 700; color: var(--on-surface); }
.st-import-panel__list {
    margin: 8px 0 0;
    padding-left: 18px;
    font-size: 12px;
    color: var(--on-surface-variant);
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.st-import-panel__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--on-surface-variant);
    cursor: pointer;
    flex-shrink: 0;
}
.st-import-panel__close:hover { background: rgba(0, 0, 0, 0.06); }

/* ── Stage cards — real per-stage volume/records/progress, each also the
   nav link to that tab. ────────────────────────────────────────────────── */
.st-nav-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.st-nav-card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 18px 20px;
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    text-decoration: none;
    color: inherit;
    overflow: hidden;
    transition: box-shadow .15s ease, border-color .15s ease;
}
.st-nav-card:hover { box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06); }
.st-nav-card__top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.st-nav-card__icon {
    width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center;
    background: var(--surface-container-high); color: var(--on-surface-variant); flex-shrink: 0;
}
.st-nav-card__icon .material-symbols-outlined { font-size: 17px; }
.st-nav-card__records { font-family: monospace; font-size: 11px; font-weight: 600; color: var(--on-surface-variant); background: var(--surface-container); padding: 3px 8px; border-radius: 999px; white-space: nowrap; }
.st-nav-card__value { font-size: 1.375rem; font-weight: 800; letter-spacing: -.01em; color: var(--on-surface); line-height: 1.2; font-variant-numeric: tabular-nums; }
.st-nav-card__unit { font-size: .75rem; font-weight: 700; color: var(--on-surface-variant); }
.st-nav-card__label { font-size: .8125rem; font-weight: 700; color: var(--on-surface-variant); }
.st-nav-card__bar { width: 100%; height: 5px; border-radius: 999px; background: var(--surface-container-high); overflow: hidden; margin-top: 4px; }
.st-nav-card__bar-fill { height: 100%; border-radius: 999px; background: var(--primary); }
.st-nav-card__ready { display: flex; align-items: center; justify-content: space-between; gap: 6px; font-size: .75rem; color: var(--on-surface-variant); }
.st-nav-card__ready strong { color: var(--on-surface); font-weight: 700; }
.st-nav-card__note { font-size: .6875rem; color: var(--on-surface-variant); margin: 0; line-height: 1.4; }
.st-nav-card__link {
    display: inline-flex; align-items: center; gap: 4px; margin-top: auto; padding-top: 10px; border-top: 1px solid var(--card-border);
    font-size: .75rem; font-weight: 700; color: var(--primary);
}
.st-nav-card__link .material-symbols-outlined { font-size: 14px; }

/* ── Active tab content ───────────────────────────────────────────────── */
.st-body { display: flex; flex-direction: column; gap: 14px; padding-top: 12px; border-top: 1px solid var(--card-border); }
.st-list-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.st-list-toolbar__title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.005em; color: var(--on-surface); margin: 0; }

/* ── Bottom triptych ──────────────────────────────────────────────────── */
.st-triptych { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
.st-panel { display: flex; flex-direction: column; gap: 14px; background: var(--surface-container-lowest); border: 1px solid var(--card-border); border-radius: var(--card-radius); padding: 20px; }
.st-panel__head { display: flex; align-items: center; gap: 8px; }
.st-panel__head .material-symbols-outlined { font-size: 19px; color: var(--primary); }
.st-panel__head h3 { font-size: .8125rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface); margin: 0; }
.st-panel__empty { font-size: .8125rem; color: var(--on-surface-variant); margin: 0; }

.st-timeline { display: flex; flex-direction: column; gap: 12px; position: relative; padding-left: 14px; border-left: 2px solid var(--card-border); }
.st-timeline__item { position: relative; }
.st-timeline__item::before { content: ''; position: absolute; left: -18px; top: 4px; width: 8px; height: 8px; border-radius: 50%; background: var(--primary); }
.st-timeline__head { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; }
.st-timeline__label { font-family: monospace; font-size: .75rem; font-weight: 700; color: var(--on-surface); }
.st-timeline__ago { font-family: monospace; font-size: .6875rem; color: var(--on-surface-variant); white-space: nowrap; }
.st-timeline__detail { font-size: .75rem; color: var(--on-surface-variant); margin: 3px 0 0; line-height: 1.4; }

.st-chain { display: flex; flex-direction: column; gap: 10px; }
.st-chain__item { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; background: var(--surface-container-low); }
.st-chain__icon { width: 26px; height: 26px; border-radius: 50%; background: var(--surface-container-high); color: var(--on-surface); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.st-chain__body { min-width: 0; }
.st-chain__title { font-family: monospace; font-size: .75rem; font-weight: 700; color: var(--on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.st-chain__sub { font-size: .6875rem; color: var(--on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.st-health-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.st-health-box { padding: 12px; border-radius: 8px; background: var(--surface-container-low); }
.st-health-box__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--on-surface-variant); }
.st-health-box__value { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); margin-top: 3px; font-variant-numeric: tabular-nums; }

@media (prefers-reduced-motion: reduce) {
    .st-nav-card,
    .st-btn-primary { transition: none; animation: none; }
}

@media (max-width: 1180px) {
    .st-nav-cards { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .st-page { gap: 16px; }
    .st-verified { gap: 18px; }
    .st-hero { flex-direction: column; align-items: stretch; }
    .st-hero__actions .st-btn-primary,
    .st-hero__actions .st-btn-outline { justify-content: center; }
    .st-nav-cards { grid-template-columns: 1fr; gap: 10px; }
    .st-health-grid { grid-template-columns: 1fr; }
}
</style>
