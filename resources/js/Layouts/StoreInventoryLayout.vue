<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import AddFarmCollectionModal from '@/Components/Modals/AddFarmCollectionModal.vue';
import AddBatchModal from '@/Components/Modals/AddBatchModal.vue';
import AddLotModal from '@/Components/Modals/AddLotModal.vue';
import {
    ArrowDown, ArrowRight, Close, Files, Filter, MoreFilled, OfficeBuilding,
    Plus, Ticket, UploadFilled, Wallet, WarningFilled,
} from '@element-plus/icons-vue';

/* ── The shell every inventory tab page (Farm Collections, Batches, Lots,
   Tokenised Lots) renders inside — hero, nav cards, and the blockchain
   status banner all stay identical across tabs; only the `<slot />`
   content (each tab's own list) differs. The old el-tabs bar + separate
   KPI grid have been folded into one row of clickable nav cards (each
   both a link to its page and a live count), and Quick Transfer has been
   dropped entirely — this is meant to be the simple, inviting front door
   to the store, not a dashboard. ──────────────────────────────────────── */
const props = defineProps({
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
    activeTab: { type: String, required: true }, // 'collections' | 'batches' | 'lots' | 'tokenised'
    farmCollections: { type: Array, default: () => [] },
    batches: { type: Array, default: () => [] },
    lots: { type: Array, default: () => [] },
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

const tokenisedLots = computed(() => props.lots.filter((lot) => lot.blockchain));
const tokenisedPct = computed(() => props.lots.length ? Math.round((tokenisedLots.value.length / props.lots.length) * 100) : 0);
const sourcedFarmsCount = computed(() => new Set(props.farmCollections.map((c) => c.farm_id).filter(Boolean)).size);
const totalBatchWeightKg = computed(() => props.batches.reduce((sum, b) => sum + Number(b.net_weight_kg || 0), 0));
const totalLotWeightKg = computed(() => props.lots.reduce((sum, l) => sum + Number(l.net_weight_kg || 0), 0));

/* ── Nav cards — the page's primary navigation, one per tab. Doubles as
   the KPI snapshot the old separate grid used to show, so there's one
   thing to look at instead of two. Every tab page is always linked here,
   regardless of which one is active. ──────────────────────────────────── */
const tabs = computed(() => [
    {
        key: 'collections',
        label: 'Farm Collections',
        icon: OfficeBuilding,
        route: 'store.collections',
        value: props.farmCollections.length,
        sub: `From ${sourcedFarmsCount.value} farm${sourcedFarmsCount.value === 1 ? '' : 's'}`,
    },
    {
        key: 'batches',
        label: 'Batches',
        icon: Files,
        route: 'store.batches',
        value: props.batches.length,
        sub: `${totalBatchWeightKg.value.toLocaleString()} kg total`,
    },
    {
        key: 'lots',
        label: 'Lots',
        icon: Ticket,
        route: 'store.lots',
        value: props.lots.length,
        sub: `${totalLotWeightKg.value.toLocaleString()} kg total`,
    },
    {
        key: 'tokenised',
        label: 'Tokenised Lots',
        icon: Wallet,
        route: 'store.tokenised',
        value: tokenisedLots.value.length,
        sub: `${tokenisedPct.value}% of all lots`,
    },
]);

const currentTab = computed(() => tabs.value.find((tab) => tab.key === props.activeTab));

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
        title="My Store"
        :store="store"
        :status-options="statusOptions"
        :import-result="importResult"
        v-model:store-dialog-open="storeDialogOpen"
        v-model:import-result-visible="importResultVisible"
    >
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
                <div class="st-hero">
                    <div class="st-hero__text">
                        <h1 class="st-title">My Store</h1>
                        <p class="st-subtitle">Manage your active inventory and certify lots on the blockchain before pushing to market.</p>
                    </div>
                    <div class="st-hero__actions">
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

                <!-- ── Nav cards — every tab page is always linked here. ──── -->
                <nav class="st-nav-cards">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.key"
                        :href="route(tab.route)"
                        class="st-nav-card"
                    >
                        <div class="st-nav-card__head">
                            <div class="st-nav-card__top">
                                <div class="st-nav-card__icon">
                                    <el-icon><component :is="tab.icon" /></el-icon>
                                </div>
                                <span class="st-nav-card__label">{{ tab.label }}</span>
                            </div>
                            <el-icon class="st-nav-card__chevron"><ArrowRight /></el-icon>
                        </div>
                        <div class="st-nav-card__stat">
                            <span class="st-nav-card__value">{{ tab.value }}</span>
                            <span class="st-nav-card__sub">{{ tab.sub }}</span>
                        </div>
                    </Link>
                </nav>

                <!-- ── Active tab's content ─────────────────────────────── -->
                <div class="st-body">
                    <div class="st-list-toolbar">
                        <h2 class="st-list-toolbar__title">{{ currentTab?.label }}</h2>
                        <div class="st-list-toolbar__actions">
                            <button type="button" class="st-icon-btn"><el-icon><Filter /></el-icon></button>
                            <button type="button" class="st-icon-btn"><el-icon><MoreFilled /></el-icon></button>
                        </div>
                    </div>

                    <slot />
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
.st-verified { display: flex; flex-direction: column; gap: 28px; }
.st-hero { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.st-hero__text { display: flex; flex-direction: column; gap: 8px; max-width: 640px; }
.st-title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    color: var(--primary);
    margin: 0 0 6px;
}
.st-subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 400; color: var(--on-surface-variant); margin: 0; max-width: 620px; }
.st-hero__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

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

/* ── Nav cards — primary navigation + KPI snapshot, one row, one look.
   Flat, modern tiles: no border, no resting shadow, one consistent
   neutral color treatment (no per-tab hue coding) — the tinted surface
   and typography carry the tile, not chrome. Value and caption sit on
   one baseline-aligned row so the caption never breaks onto its own
   line, truncating with an ellipsis instead if the tile gets narrow.
   Hover adds only a small, soft shadow — no border or lift. ─────────── */
.st-nav-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.st-nav-card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 18px 20px 16px;
    background: var(--surface-container-low);
    border-radius: var(--card-radius);
    text-decoration: none;
    color: inherit;
    overflow: hidden;
    transition: box-shadow .15s ease;
}
.st-nav-card:hover { box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06); }
.st-nav-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; min-width: 0; }
.st-nav-card__top { display: flex; align-items: center; gap: 10px; min-width: 0; }
.st-nav-card__icon {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    background: var(--surface-container-high);
    color: var(--on-surface-variant);
}
.st-nav-card__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.st-nav-card__stat { display: flex; align-items: baseline; gap: 7px; min-width: 0; }
.st-nav-card__value { flex-shrink: 0; font-size: 1.625rem; font-weight: 800; letter-spacing: -.01em; color: var(--on-surface); line-height: 1.2; font-variant-numeric: tabular-nums; }
.st-nav-card__sub { flex: 1; min-width: 0; font-size: .6875rem; font-weight: 600; color: var(--on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.st-nav-card__chevron { flex-shrink: 0; font-size: 14px; color: var(--on-surface-variant); opacity: 0; transform: translateX(-4px); transition: opacity .15s ease, transform .15s ease; }
.st-nav-card:hover .st-nav-card__chevron { opacity: 1; transform: translateX(0); color: var(--primary); }

/* ── Active tab content ───────────────────────────────────────────────── */
.st-body { display: flex; flex-direction: column; gap: 14px; padding-top: 12px; border-top: 1px solid var(--card-border); }
.st-list-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.st-list-toolbar__title { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.005em; color: var(--on-surface); margin: 0; }
.st-list-toolbar__actions { display: flex; align-items: center; gap: 2px; flex-shrink: 0; }
.st-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--on-surface-variant);
    cursor: pointer;
    transition: background .15s ease;
}
.st-icon-btn:hover { background: var(--surface-container); }
.st-icon-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

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
    .st-hero__actions .st-btn-primary { justify-content: center; }
    .st-nav-cards { grid-template-columns: 1fr; gap: 10px; }
}
</style>
