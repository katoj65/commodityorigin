<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import {
    Histogram, Search, RefreshLeft, CircleCheck, ChatDotRound,
    Coin, Promotion,
    Files, Refresh,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import OfferModal from '@/Components/Modals/OfferModal.vue';

const props = defineProps({
    originOptions: { type: Array, default: () => [] },
    coffeeTypeOptions: { type: Array, default: () => [] },
    fobOptions: { type: Array, default: () => [] },
    offers: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    offerStats: { type: Object, default: () => ({}) },
});


/* ── Dummy Offers hub content — illustrative only ─────────────────── */
const statusTabs = [
    { key: 'all', label: 'All Offers', count: 13 },
    { key: 'received', label: 'Received', count: 8 },
    { key: 'sent', label: 'Sent', count: 5 },
    { key: 'negotiating', label: 'Negotiating', count: 3, dot: true },
    { key: 'accepted', label: 'Accepted', count: 12 },
    { key: 'declined', label: 'Declined', count: 4 },
    { key: 'expired', label: 'Expired', count: 2 },
];
const activeTab = ref('all');
const searchQuery = ref(props.filters.search ?? '');
const originFilter = ref(props.filters.origin ?? 'all');
const typeFilter = ref(props.filters.coffee_type ?? 'all');
const statusFilter = ref(props.filters.status ?? 'active-first');

const originOptions = computed(() => [
    { value: 'all', label: 'All Origins' },
    ...props.originOptions.map((name) => ({ value: name, label: name })),
]);
const typeOptions = computed(() => [
    { value: 'all', label: 'All Coffee Types' },
    ...props.coffeeTypeOptions.map((name) => ({ value: name, label: name })),
]);
const statusOptions = [
    { value: 'active-first', label: 'Active First' },
    { value: 'negotiating', label: 'Negotiating' },
    { value: 'received', label: 'Received' },
    { value: 'accepted', label: 'Accepted' },
    { value: 'expired', label: 'Expired' },
];

function applyFilters() {
    router.get(route('exchange.offers'), {
        search: searchQuery.value || undefined,
        origin: originFilter.value !== 'all' ? originFilter.value : undefined,
        coffee_type: typeFilter.value !== 'all' ? typeFilter.value : undefined,
        status: statusFilter.value !== 'active-first' ? statusFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const kpis = computed(() => [
    { icon: Files, label: 'Offers Received', value: String(props.offerStats.offersReceived ?? 0), note: 'Available offers', tone: 'primary' },
    { icon: Promotion, label: 'Offers Sent', value: String(props.offerStats.offersSent ?? 0), note: '2 in active counter-turn' },
    { icon: ChatDotRound, label: 'Active Negotiating', value: String(props.offerStats.activeNegotiating ?? 0), note: 'Avg spread $0.09/kg' },
    { icon: CircleCheck, label: 'Accepted (YTD)', value: '12', note: '100% converted to escrow', tone: 'primary' },
    { icon: Coin, label: 'Pipeline Offer Value', value: `$${Number(props.offerStats.pipelineValue ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`, note: 'Volume: 58 MT (966 bags)', mono: true },
]);

const offers = computed(() => props.offers);

/* ── Offer modal — triggered by the "Offer" button on open-status rows ── */
const offerModalOpen = ref(false);
const selectedOffer = ref(null);

function openOfferModal(offer) {
    selectedOffer.value = offer;
    offerModalOpen.value = true;
}

function isOpenStatus(offer) {
    return (offer.status ?? '').toLowerCase() === 'open';
}

function placeholderAction(label) {
    ElMessage.info(`${label} (dummy preview).`);
}
</script>

<template>
    <MainLayout title="Offers">
        <div class="ex-page">
            <!-- 1. HEADER -->
            <section class="ex-card ex-hero">
                <div class="ex-hero__top">
                    <div>
                        <h1 class="dp-display-md ex-strong">Offers</h1>
                        <p class="dp-body-md ex-muted" style="max-width: 640px;">Negotiate physical green coffee spot prices, Incoterms, and Stanbic institutional escrow terms prior to legally binding order execution.</p>
                    </div>
                    <div class="ex-hero__actions">
                        <Link :href="route('exchange.index')" class="ex-btn ex-btn--muted">
                            <el-icon :size="16"><Histogram /></el-icon><span>View Exchange</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- 2. STATUS FILTER TABS -->
            <section class="ex-filter-tabs">
                <div class="ex-filter-tabs__list">
                    <button v-for="tab in statusTabs" :key="tab.key" type="button" class="ex-filter-tab" :class="{ 'ex-filter-tab--active': activeTab === tab.key }" @click="activeTab = tab.key">
                        <span v-if="tab.dot" class="ex-dot" style="background: var(--dp-secondary);"></span>
                        <span>{{ tab.label }}</span>
                        <span class="ex-filter-tab__count">{{ tab.count }}</span>
                    </button>
                </div>
                <div class="dp-caption ex-muted ex-flex-icon"><el-icon :size="14" class="ex-icon--primary"><Refresh /></el-icon>Automated clearing window: 24h</div>
            </section>

            <!-- 3. KPI ROW -->
            <section class="ex-kpi-grid ex-kpi-grid--5">
                <div v-for="kpi in kpis" :key="kpi.label" class="ex-kpi">
                    <div class="ex-kpi__head">
                        <span class="dp-label-md ex-strong">{{ kpi.label }}</span>
                        <el-icon :size="18" :class="kpi.tone === 'primary' ? 'ex-icon--primary' : 'ex-muted'"><component :is="kpi.icon" /></el-icon>
                    </div>
                    <div class="ex-kpi__value"><span class="ex-kpi__num" :class="{ 'dp-mono': kpi.mono }">{{ kpi.value }}</span></div>
                    <p class="dp-caption" :class="kpi.tone === 'primary' ? 'ex-icon--primary ex-strong' : 'ex-muted'">{{ kpi.note }}</p>
                </div>
            </section>

            <!-- 4. OFFERS TABLE -->
            <section class="ex-card">
                <div class="ex-filters-bar">
                    <div class="ex-ft-field">
                        <label class="ex-ft-label">Search</label>
                        <el-input v-model="searchQuery" placeholder="Offer ID, coffee, lot, counterparty..." clearable>
                            <template #prefix><el-icon><Search /></el-icon></template>
                        </el-input>
                    </div>
                    <div class="ex-ft-field">
                        <label class="ex-ft-label">Origin</label>
                        <el-select v-model="originFilter" class="ex-ft-control" aria-label="Filter by origin">
                            <el-option v-for="opt in originOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
                        </el-select>
                    </div>
                    <div class="ex-ft-field">
                        <label class="ex-ft-label">Coffee Type</label>
                        <el-select v-model="typeFilter" class="ex-ft-control" aria-label="Filter by coffee type">
                            <el-option v-for="opt in typeOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
                        </el-select>
                    </div>
                    <div class="ex-ft-field">
                        <label class="ex-ft-label">Status</label>
                        <el-select v-model="statusFilter" class="ex-ft-control" aria-label="Filter by status">
                            <el-option v-for="opt in statusOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
                        </el-select>
                    </div>
                    <div class="ex-ft-field ex-ft-field--clear">
                        <label class="ex-ft-label">&nbsp;</label>
                        <el-button class="ex-ft-clear" title="Apply filters" :icon="RefreshLeft" @click="applyFilters">Filter</el-button>
                    </div>
                </div>

                <div class="ex-table-wrap">
                    <table class="ex-table">
                        <thead>
                            <tr><th>Offer &amp; Lot</th><th>Counterparty</th><th>Quantity &amp; Price</th><th>Status &amp; Validity</th><th class="ex-right">Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="!offers.length">
                                <td colspan="5" class="ex-center dp-caption ex-muted">No offers yet.</td>
                            </tr>
                            <tr v-for="offer in offers" :key="offer.id" :class="{ 'ex-row--selected': offer.focus, 'ex-row--faded': offer.faded }">
                                <td>
                                    <div class="ex-strong">{{ offer.name }}</div>
                                    <div class="dp-mono ex-icon--primary ex-caption-sm">{{ offer.id }}</div>
                                </td>
                                <td>
                                    <div>{{ offer.counterparty }}</div>
                                    <div v-if="offer.verified" class="dp-caption ex-icon--primary ex-flex-icon"><el-icon :size="12"><CircleCheck /></el-icon>{{ offer.counterpartyNote }}</div>
                                    <div v-else class="dp-caption ex-muted">{{ offer.counterpartyNote }}</div>
                                </td>
                                <td>
                                    <div class="dp-mono">{{ offer.qty }}</div>
                                    <div class="dp-caption ex-icon--primary dp-mono">{{ offer.price }}</div>
                                </td>
                                <td>
                                    <span class="ex-tag-mini" :class="`ex-tag-mini--${offer.statusTone}`">{{ offer.status }}</span>
                                    <div class="dp-caption ex-muted dp-mono">{{ offer.validUntil }}</div>
                                </td>
                                <td class="ex-right">
                                    <button v-if="isOpenStatus(offer)" type="button" class="ex-btn ex-btn--sm" :class="`ex-btn--${offer.actionTone}`" @click="openOfferModal(offer)">Offer</button>
                                    <Link v-else :href="route('exchange.offers.show', offer.recordId)" class="ex-btn ex-btn--sm" :class="`ex-btn--${offer.actionTone}`">{{ offer.action }}</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="ex-footline">
                    <span class="dp-caption ex-muted">Showing 5 of 13 active physical contracts</span>
                    <div class="ex-actions-inline">
                        <button v-for="p in [1, 2, 3]" :key="p" type="button" class="ex-btn ex-btn--sm" :class="p === 1 ? 'ex-btn--primary' : 'ex-btn--muted'" @click="placeholderAction(`Page ${p}`)">{{ p }}</button>
                    </div>
                </div>
            </section>
        </div>

        <OfferModal v-model="offerModalOpen" :offer="selectedOffer" :fob-options="fobOptions" />
    </MainLayout>
</template>

<style scoped>
.ex-page { display: flex; flex-direction: column; gap: 16px; }

.ex-card {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--dp-outline-variant);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.ex-panel { background: var(--dp-surface-container-low); border-radius: 8px; padding: 14px; }

.ex-muted { color: var(--dp-on-surface-variant); }
.ex-strong { color: var(--dp-on-surface); font-weight: 700; }
.ex-on { color: var(--dp-on-surface); }
.ex-mb-sm { margin-bottom: 8px; }
.ex-caption-sm { font-size: 11px; }
.ex-eyebrow { text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; font-size: 11px; }
.ex-flex-icon { display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ex-flex-icon-between { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }

.ex-icon--primary { color: var(--dp-primary); }
.ex-icon--secondary { color: var(--dp-secondary); }
.ex-icon--error { color: var(--dp-error); }

.ex-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--dp-primary); flex-shrink: 0; }
.ex-dot--pulse { animation: ex-pulse 1.6s ease-in-out infinite; }
@keyframes ex-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }

.ex-tag-mini { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 3px 9px; border-radius: 6px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); white-space: nowrap; }
.ex-tag-mini--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.ex-tag-mini--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ex-tag-mini--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.ex-tag-mini--tertiary { background: var(--dp-tertiary-fixed); color: var(--dp-on-tertiary-fixed); }
.ex-tag-mini--accepted { background: color-mix(in srgb, var(--dp-primary) 12%, transparent); color: var(--dp-primary); }

.ex-hero { border: none; border-bottom: 1px solid var(--dp-outline-variant); border-radius: 0; margin-top: -32px; padding-bottom: 24px; box-shadow: none; }
.ex-hero__top { display: flex; flex-direction: column; gap: 16px; }
@media (min-width: 1024px) { .ex-hero__top { flex-direction: row; align-items: center; justify-content: space-between; } }
.ex-hero__actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }

.ex-filter-tabs { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 6px; background: var(--dp-surface-container-low); border-radius: 12px; }
.ex-filter-tabs__list { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; }
.ex-filter-tab { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--dp-font-sans); }
.ex-filter-tab--active { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); box-shadow: var(--dp-card-shadow); font-weight: 700; }
.ex-filter-tab__count { padding: 1px 7px; border-radius: 999px; font-size: 10px; font-family: var(--dp-font-mono); background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.ex-kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
@media (min-width: 1024px) { .ex-kpi-grid--5 { grid-template-columns: repeat(5, 1fr); } }
.ex-kpi { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 16px 18px; display: flex; flex-direction: column; gap: 8px; }
.ex-kpi__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.ex-kpi__num { font-size: 24px; font-weight: 800; color: var(--dp-on-surface); font-family: var(--dp-font-sans); line-height: 1; }

.ex-filters-bar {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 12px;
    align-items: end;
    width: 100%;
}
@media (max-width: 900px) { .ex-filters-bar { grid-template-columns: 1fr 1fr; } }
@media (max-width: 560px) { .ex-filters-bar { grid-template-columns: 1fr; } }

.ex-ft-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.ex-ft-field--clear { flex-shrink: 0; }

.ex-ft-label { font-size: 10px; font-weight: 700; color: var(--dp-on-surface-variant); text-transform: uppercase; letter-spacing: 0.04em; }

.ex-ft-field :deep(.el-input__wrapper) { background: var(--dp-surface-container-low); border-radius: 8px; box-shadow: none; }
.ex-ft-field :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset; background: var(--dp-surface-container-lowest); }
.ex-ft-field :deep(.el-input__inner) { font-family: var(--dp-font-sans); font-size: var(--dp-content-font-size) !important; color: var(--dp-on-surface); }
.ex-ft-field :deep(.el-input__prefix) { color: var(--dp-on-surface-variant); }

.ex-ft-control { width: 100%; }
.ex-ft-control :deep(.el-select__wrapper) { background: var(--dp-surface-container-low); border-radius: 8px; box-shadow: none; }
.ex-ft-control :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset; background: var(--dp-surface-container-lowest); }
.ex-ft-control :deep(.el-select__selected-item) { font-family: var(--dp-font-sans); font-size: var(--dp-content-font-size) !important; color: var(--dp-on-surface); }
.ex-ft-control :deep(.el-select__placeholder) { font-size: var(--dp-content-font-size) !important; }

.ex-ft-clear.el-button {
    height: 48px;
    min-height: 48px;
    margin: 0;
    padding: 0 16px;
    border: none;
    border-radius: 8px;
    background: var(--dp-surface-container-high);
    color: var(--dp-on-surface);
    font-family: var(--dp-font-sans);
    font-size: 11px;
    font-weight: 600;
}
.ex-ft-clear.el-button:hover { background: var(--dp-surface-dim); }

.ex-table-wrap { overflow-x: auto; }
.ex-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
.ex-table thead tr { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; font-weight: 700; }
.ex-table th { padding: 10px 12px; white-space: nowrap; }
.ex-table th:first-child { border-radius: 6px 0 0 6px; }
.ex-table th:last-child { border-radius: 0 6px 6px 0; }
.ex-table tbody tr { border-bottom: 1px solid var(--dp-outline-variant); transition: background 0.15s ease; }
.ex-table tbody tr:last-child { border-bottom: none; }
.ex-table tbody tr:hover { background: var(--dp-surface-container-low); }
.ex-table td { padding: 12px; vertical-align: middle; }
.ex-row--selected { background: var(--dp-surface-container-low); }
.ex-row--faded { opacity: 0.6; }
.ex-center { text-align: center; }
.ex-right { text-align: right; }

.ex-footline { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding-top: 12px; border-top: 1px solid var(--dp-outline-variant); }
.ex-actions-inline { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ex-actions-inline--end { justify-content: flex-end; }

.ex-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 14px; border-radius: 8px; border: none; font-size: var(--dp-content-font-size); font-weight: 700; cursor: pointer; transition: background 0.15s ease, color 0.15s ease; font-family: var(--dp-font-sans); white-space: nowrap; text-decoration: none; }
.ex-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ex-btn--primary:hover { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.ex-btn--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.ex-btn--secondary:hover { background: var(--dp-secondary); color: var(--dp-on-secondary-container); }
.ex-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.ex-btn--muted:hover { background: var(--dp-surface-dim); }
.ex-btn--sm { padding: 6px 10px; }

.ex-link { display: inline-flex; align-items: center; justify-content: space-between; gap: 6px; font-weight: 700; color: var(--dp-primary); text-decoration: none; background: none; border: none; cursor: pointer; font-size: 12px; font-family: var(--dp-font-sans); padding: 6px 4px; }
.ex-link:hover { text-decoration: underline; }
</style>
