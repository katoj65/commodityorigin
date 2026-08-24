<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import AddFarmCollectionModal from '@/Components/Modals/AddFarmCollectionModal.vue';
import AddBatchModal from '@/Components/Modals/AddBatchModal.vue';
import AddLotModal from '@/Components/Modals/AddLotModal.vue';
import {
    ArrowDown, Box, Check, Checked, CircleCheck, Clock, Close, Coffee, Coin, EditPen, Files, Filter,
    FolderOpened, Medal, MoreFilled, Odometer, OfficeBuilding, Operation, Plus, Promotion, RefreshRight,
    Shop, Sort, Ticket, Top, UploadFilled, Van, View, Wallet, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    store: { type: Object, default: null },
    farmCollections: { type: Array, default: () => [] },
    batches: { type: Array, default: () => [] },
    lots: { type: Array, default: () => [] },
    processOptions: { type: Array, default: () => [] },
    dryingMethodOptions: { type: Array, default: () => [] },
    millingOptions: { type: Array, default: () => [] },
    coffeeTypeOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    isAdmin: { type: Boolean, default: false },
    pendingStores: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
});

/* ── Uncommitted Inventory section tabs ──────────────────────────────── */
const inventoryTab = ref('collections');

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

const quickTransfer = reactive({ sourceWarehouse: '', destinationStore: '', lotId: '', quantity: null });

function formatMoney(amount, currency) {
    const value = Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return currency ? `${currency} ${value}` : `$${value}`;
}

/* ── Shared with StoreLayout's header buttons via v-model ─────────────── */
const storeDialogOpen = ref(false);
const statusFilter = ref('all');
const importResultVisible = ref(Boolean(props.importResult));

function formatDate(value) {
    if (!value) return '';
    return new Date(value.replace(' ', 'T')).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

/* ── Admin: verify / reject pending stores ───────────────────────────── */
const verifyingId = ref(null);

function verifyStore(pending) {
    verifyingId.value = pending.id;
    router.post(route('store.verify', pending.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            ElNotification({ title: 'Store Verified', message: `${pending.owner_name}'s store can now open.`, type: 'success', duration: 3200, offset: 84 });
        },
        onFinish: () => { verifyingId.value = null; },
    });
}

const rejectDialogOpen = ref(false);
const rejectingStore = ref(null);
const rejectForm = useForm({ reason: '' });

function requestReject(pending) {
    rejectingStore.value = pending;
    rejectForm.reset();
    rejectForm.clearErrors();
    rejectDialogOpen.value = true;
}

function submitReject() {
    rejectForm.post(route('store.reject', rejectingStore.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            rejectDialogOpen.value = false;
            ElNotification({ title: 'Store Rejected', message: `${rejectingStore.value.owner_name}'s store was rejected.`, type: 'warning', duration: 3200, offset: 84 });
        },
    });
}
</script>

<template>
    <StoreLayout
        title="My Store"
        :store="store"
        :status-options="statusOptions"
        :import-result="importResult"
        v-model:store-dialog-open="storeDialogOpen"
        v-model:status-filter="statusFilter"
        v-model:import-result-visible="importResultVisible"
    >

        <div class="st-page">
            <!-- ── Admin: pending store verifications ───────────────────── -->
            <div v-if="isAdmin && pendingStores.length">
                <div class="st-pending-card">
                    <div class="st-pending-card__head">
                        <el-icon :size="15"><WarningFilled /></el-icon>
                        Pending Store Verifications
                        <span class="st-pending-count">{{ pendingStores.length }}</span>
                    </div>
                    <div v-for="pending in pendingStores" :key="pending.id" class="st-pending-row">
                        <div class="st-pending-row__info">
                            <div class="st-pending-row__name">{{ pending.owner_name }}</div>
                            <div class="st-pending-row__meta">requested {{ formatDate(pending.created_at) }}</div>
                        </div>
                        <div class="st-pending-row__actions">
                            <button type="button" class="st-btn-outline st-btn-outline--danger" @click="requestReject(pending)">
                                <el-icon><Close /></el-icon> Reject
                            </button>
                            <button type="button" class="st-btn-primary" :disabled="verifyingId === pending.id" @click="verifyStore(pending)">
                                <el-icon><Check /></el-icon> Verify
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div>
                <!-- No store yet -->
                <div v-if="!store" class="st-empty">
                    <div class="st-empty__icon"><el-icon :size="22"><Shop /></el-icon></div>
                    <div class="st-empty__title">You haven't requested a store yet</div>
                    <p class="st-empty__text">Request your store and an admin will review it before you can start adding items.</p>
                    <button type="button" class="st-btn-primary mt-2" @click="storeDialogOpen = true">
                        <el-icon><Plus /></el-icon> Request Your Store
                    </button>
                </div>

                <!-- Pending verification -->
                <div v-else-if="store.verification_status === 'pending'" class="st-status-banner st-status-banner--pending">
                    <el-icon :size="18"><Clock /></el-icon>
                    <div>
                        <div class="st-status-banner__title">Awaiting admin verification</div>
                        <p class="st-status-banner__text">Your store request is under review. You'll be able to add items once it's verified.</p>
                    </div>
                </div>

                <!-- Rejected -->
                <div v-else-if="store.verification_status === 'rejected'" class="st-status-banner st-status-banner--rejected">
                    <el-icon :size="18"><WarningFilled /></el-icon>
                    <div>
                        <div class="st-status-banner__title">Verification rejected</div>
                        <p class="st-status-banner__text">{{ store.rejection_reason || 'No reason was given.' }}</p>
                        <button type="button" class="st-btn-primary mt-2" @click="storeDialogOpen = true">
                            <el-icon><EditPen /></el-icon> Resubmit
                        </button>
                    </div>
                </div>

                <!-- Verified: static preview layout (placeholder content — this
                     app has no warehouses/transfers/blockchain ledger backing
                     these numbers; matches the supplied mockup exactly). ──── -->
                <template v-else>
                <div class="st-verified">
                    <div class="st-hero">
                        <div class="st-hero__text">
                            <h1 class="st-title">My Store</h1>
                            <p class="st-subtitle">Manage your active inventory, process transfers, and certify lots on the blockchain before pushing to market.</p>
                        </div>
                        <div class="st-hero__actions">
                            <button type="button" class="st-btn-outline">
                                <el-icon><Sort /></el-icon> Transfer Stock
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

                    <!-- ── KPI snapshot ──────────────────────────────────── -->
                    <div class="st-kpi-grid">
                        <div class="st-kpi">
                            <div class="st-kpi__top">
                                <div class="st-kpi__icon st-kpi__icon--a"><el-icon><Box /></el-icon></div>
                                <span class="st-kpi__chip st-kpi__chip--up"><el-icon :size="12"><Top /></el-icon> +5.2%</span>
                            </div>
                            <div class="st-kpi__label">Total Stock</div>
                            <div class="st-kpi__value">12,450 <span class="st-kpi__unit">kg</span></div>
                        </div>
                        <div class="st-kpi">
                            <div class="st-kpi__top">
                                <div class="st-kpi__icon st-kpi__icon--b"><el-icon><Clock /></el-icon></div>
                            </div>
                            <div class="st-kpi__label">Uncommitted</div>
                            <div class="st-kpi__value">3,200 <span class="st-kpi__unit">kg</span></div>
                            <div class="st-kpi__sub">Ready for market prep</div>
                        </div>
                        <div class="st-kpi">
                            <div class="st-kpi__top">
                                <div class="st-kpi__icon st-kpi__icon--c"><el-icon><Van /></el-icon></div>
                                <span class="st-kpi__chip">4 active</span>
                            </div>
                            <div class="st-kpi__label">Active Transfers</div>
                            <div class="st-kpi__value">4</div>
                            <div class="st-kpi__sub"><el-icon :size="12"><Clock /></el-icon> 2 arriving today</div>
                        </div>
                        <div class="st-kpi">
                            <div class="st-kpi__top">
                                <div class="st-kpi__icon st-kpi__icon--d"><el-icon><CircleCheck /></el-icon></div>
                                <el-icon :size="16" class="st-kpi__check"><CircleCheck /></el-icon>
                            </div>
                            <div class="st-kpi__label">Certified Lots</div>
                            <div class="st-kpi__value">18</div>
                            <div class="st-kpi__sub st-kpi__sub--green">100% blockchain verified</div>
                        </div>
                    </div>

                    <div class="st-layout">
                        <div class="st-col-main">
                            <div class="st-tabs-wrap">
                                <div class="st-section-actions">
                                    <button type="button" class="st-icon-btn"><el-icon><Filter /></el-icon></button>
                                    <button type="button" class="st-icon-btn"><el-icon><MoreFilled /></el-icon></button>
                                </div>
                                <el-tabs v-model="inventoryTab" class="st-el-tabs">

                                <!-- ── Farm Collection (farm_collections) ───────────── -->
                                <el-tab-pane label="Farm Collection" name="collections">
                                    <div class="st-table-card">
                                        <div class="st-table-scroll"><el-table :data="farmCollections" class="st-el-table">
                                            <el-table-column min-width="190">
                                                <template #header><span class="st-th"><el-icon><OfficeBuilding /></el-icon> Farm</span></template>
                                                <template #default="{ row }">
                                                    <div class="st-cell">
                                                        <span class="st-thumb"><el-icon :size="15"><OfficeBuilding /></el-icon></span>
                                                        <div>
                                                            <div class="st-name">{{ row.farm?.name || `Farm #${row.farm_id}` }}</div>
                                                            <div class="st-muted st-caption">{{ row.collection_date ? formatDate(row.collection_date) : '—' }}</div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column min-width="180">
                                                <template #header><span class="st-th"><el-icon><Coffee /></el-icon> Coffee &amp; Variety</span></template>
                                                <template #default="{ row }">
                                                    <div class="st-cell">
                                                        <span class="st-thumb"><el-icon :size="15"><Coffee /></el-icon></span>
                                                        <span class="st-commodity">{{ row.coffee_type || '—' }}<span v-if="row.variety" class="st-muted"> · {{ row.variety }}</span></span>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column width="110" align="right">
                                                <template #header><span class="st-th st-th--end"><el-icon><Box /></el-icon> Quantity</span></template>
                                                <template #default="{ row }">{{ Number(row.quantity || 0).toLocaleString() }} {{ row.unit || '' }}</template>
                                            </el-table-column>
                                            <el-table-column width="110" align="right">
                                                <template #header><span class="st-th st-th--end"><el-icon><Medal /></el-icon> Grade</span></template>
                                                <template #default="{ row }"><span class="st-pill st-pill--a">{{ row.initial_grade || '—' }}</span></template>
                                            </el-table-column>
                                            <el-table-column width="130" align="right">
                                                <template #header><span class="st-th st-th--end"><el-icon><Coin /></el-icon> Price</span></template>
                                                <template #default="{ row }"><span class="st-pill st-pill--b">{{ formatMoney(row.collection_price, row.currency) }}</span></template>
                                            </el-table-column>
                                            <el-table-column width="110" align="right">
                                                <template #header><span class="st-th st-th--end">Actions</span></template>
                                                <template #default="{ row }">
                                                    <Link v-if="row.farm_id" :href="route('farm.show', row.farm_id)" class="st-link-action"><el-icon :size="13"><View /></el-icon> View Farm</Link>
                                                </template>
                                            </el-table-column>
                                            <template #empty>
                                                <div class="st-empty-cell">
                                                    <div class="st-empty-cell__icon"><el-icon :size="20"><FolderOpened /></el-icon></div>
                                                    No farm collections recorded yet.
                                                </div>
                                            </template>
                                        </el-table></div>

                                        <div class="st-pagination-foot">
                                            <span class="st-pagination-foot__text">Showing {{ farmCollections.length }} farm collection{{ farmCollections.length === 1 ? '' : 's' }}</span>
                                        </div>
                                    </div>
                                </el-tab-pane>

                                <!-- ── Batches (batches) ─────────────────────────────── -->
                                <el-tab-pane label="Batches" name="batches">
                                    <div class="st-table-card">
                                        <div class="st-table-scroll"><el-table :data="batches" class="st-el-table">
                                            <el-table-column min-width="190">
                                                <template #header><span class="st-th"><el-icon><Files /></el-icon> Batch</span></template>
                                                <template #default="{ row }">
                                                    <div class="st-cell">
                                                        <span class="st-thumb"><el-icon :size="15"><Files /></el-icon></span>
                                                        <div>
                                                            <div class="st-name">{{ row.batch_number || `Batch #${row.id}` }}</div>
                                                            <div class="st-muted st-caption">{{ row.processing_date ? formatDate(row.processing_date) : '—' }}</div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column min-width="180">
                                                <template #header><span class="st-th"><el-icon><Coffee /></el-icon> Variety</span></template>
                                                <template #default="{ row }">
                                                    <div class="st-cell">
                                                        <span class="st-thumb"><el-icon :size="15"><Coffee /></el-icon></span>
                                                        <span class="st-commodity">{{ row.variety || '—' }}</span>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column width="130" align="right">
                                                <template #header><span class="st-th st-th--end"><el-icon><Box /></el-icon> Quantity</span></template>
                                                <template #default="{ row }">
                                                    {{ row.quantity_bags ? `${Number(row.quantity_bags).toLocaleString()} bags` : '—' }}
                                                    <div v-if="row.net_weight_kg" class="st-muted st-caption">{{ Number(row.net_weight_kg).toLocaleString() }} kg</div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column width="110" align="right">
                                                <template #header><span class="st-th st-th--end"><el-icon><Checked /></el-icon> Status</span></template>
                                                <template #default="{ row }"><span class="st-pill st-pill--a">{{ row.status || '—' }}</span></template>
                                            </el-table-column>
                                            <el-table-column width="110" align="right">
                                                <template #header><span class="st-th st-th--end">Actions</span></template>
                                                <template #default="{ row }">
                                                    <Link :href="route('batch.show', row.id)" class="st-link-action"><el-icon :size="13"><View /></el-icon> View Batch</Link>
                                                </template>
                                            </el-table-column>
                                            <template #empty>
                                                <div class="st-empty-cell">
                                                    <div class="st-empty-cell__icon"><el-icon :size="20"><FolderOpened /></el-icon></div>
                                                    No batches created yet.
                                                </div>
                                            </template>
                                        </el-table></div>

                                        <div class="st-pagination-foot">
                                            <span class="st-pagination-foot__text">Showing {{ batches.length }} batch{{ batches.length === 1 ? '' : 'es' }}</span>
                                        </div>
                                    </div>
                                </el-tab-pane>

                                <!-- ── Lots (lots) ───────────────────────────────────── -->
                                <el-tab-pane label="Lots" name="lots">
                                    <div class="st-table-card">
                                        <div class="st-table-scroll"><el-table :data="lots" class="st-el-table">
                                            <el-table-column min-width="180">
                                                <template #header><span class="st-th"><el-icon><Ticket /></el-icon> Lot</span></template>
                                                <template #default="{ row }">
                                                    <div class="st-cell">
                                                        <span class="st-thumb"><el-icon :size="15"><Ticket /></el-icon></span>
                                                        <div>
                                                            <div class="st-name">{{ row.lot_name || row.lot_number || `Lot #${row.id}` }}</div>
                                                            <div v-if="row.lot_name && row.lot_number" class="st-muted st-caption">{{ row.lot_number }}</div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column min-width="180">
                                                <template #header><span class="st-th"><el-icon><Operation /></el-icon> Process &amp; Grade</span></template>
                                                <template #default="{ row }">
                                                    <div class="st-cell">
                                                        <span class="st-thumb"><el-icon :size="15"><Operation /></el-icon></span>
                                                        <span class="st-commodity">{{ row.process || '—' }}<span v-if="row.grade" class="st-muted"> · {{ row.grade }}</span></span>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                            <el-table-column width="130" align="right">
                                                <template #header><span class="st-th st-th--end"><el-icon><Odometer /></el-icon> Weight</span></template>
                                                <template #default="{ row }">{{ row.net_weight_kg ? `${Number(row.net_weight_kg).toLocaleString()} kg` : '—' }}</template>
                                            </el-table-column>
                                            <el-table-column width="130" align="right">
                                                <template #header><span class="st-th st-th--end"><el-icon><Coin /></el-icon> Price</span></template>
                                                <template #default="{ row }"><span class="st-pill st-pill--b">{{ formatMoney(row.price, null) }}</span></template>
                                            </el-table-column>
                                            <el-table-column width="110" align="right">
                                                <template #header><span class="st-th st-th--end"><el-icon><Checked /></el-icon> Status</span></template>
                                                <template #default="{ row }"><span class="st-pill st-pill--a">{{ row.status || '—' }}</span></template>
                                            </el-table-column>
                                            <el-table-column width="110" align="right">
                                                <template #header><span class="st-th st-th--end">Actions</span></template>
                                                <template #default="{ row }">
                                                    <Link :href="route('lot.show', row.id)" class="st-link-action"><el-icon :size="13"><View /></el-icon> View Lot</Link>
                                                </template>
                                            </el-table-column>
                                            <template #empty>
                                                <div class="st-empty-cell">
                                                    <div class="st-empty-cell__icon"><el-icon :size="20"><FolderOpened /></el-icon></div>
                                                    No lots created yet.
                                                </div>
                                            </template>
                                        </el-table></div>

                                        <div class="st-pagination-foot">
                                            <span class="st-pagination-foot__text">Showing {{ lots.length }} lot{{ lots.length === 1 ? '' : 's' }}</span>
                                        </div>
                                    </div>
                                </el-tab-pane>
                            </el-tabs>
                            </div>
                        </div>

                        <!-- ── Right rail ────────────────────────────────────── -->
                        <div class="st-col-side">
                            <div class="st-side-card">
                                <h3 class="st-side-card__eyebrow">Quick Transfer</h3>
                                <form class="st-qt-form" @submit.prevent>
                                    <label class="st-qt-field">
                                        <span class="st-qt-field__label">Source Warehouse</span>
                                        <el-select v-model="quickTransfer.sourceWarehouse" placeholder="Select warehouse" class="st-qt-el">
                                            <template #prefix><el-icon><OfficeBuilding /></el-icon></template>
                                            <el-option label="Rotterdam Port Facility (A-12)" value="rotterdam" />
                                            <el-option label="Bremen Storage" value="bremen" />
                                        </el-select>
                                    </label>
                                    <label class="st-qt-field">
                                        <span class="st-qt-field__label">Destination Store</span>
                                        <el-select v-model="quickTransfer.destinationStore" placeholder="Select store" class="st-qt-el">
                                            <template #prefix><el-icon><Shop /></el-icon></template>
                                            <el-option label="My Primary Store" value="primary" />
                                            <el-option label="Secondary Reserve" value="secondary" />
                                        </el-select>
                                    </label>
                                    <div class="st-qt-grid">
                                        <label class="st-qt-field">
                                            <span class="st-qt-field__label">Lot ID</span>
                                            <el-input v-model="quickTransfer.lotId" placeholder="LOT-000" class="st-qt-el" />
                                        </label>
                                        <label class="st-qt-field">
                                            <span class="st-qt-field__label">Quantity</span>
                                            <el-input-number v-model="quickTransfer.quantity" :min="0" controls-position="right" class="st-qt-el st-qt-el--number" />
                                        </label>
                                    </div>
                                    <button type="submit" class="st-btn-primary st-qt-submit">
                                        <el-icon><Promotion /></el-icon> Initiate Transfer
                                    </button>
                                </form>
                            </div>

                            <div class="st-side-card st-side-card--dark">
                                <div class="st-side-card__head">
                                    <div class="st-side-card__head-left">
                                        <span class="st-side-card__icon"><el-icon :size="18"><Wallet /></el-icon></span>
                                        <h3 class="st-side-card__eyebrow st-side-card__eyebrow--dark">Blockchain Status</h3>
                                    </div>
                                    <span class="st-pulse"><i></i><i></i></span>
                                </div>
                                <p class="st-side-card__text">2 lots require certification before they can be pushed to the public market.</p>
                                <div class="st-chain-list">
                                    <div class="st-chain-row">
                                        <span>LOT-7731-ETH</span>
                                        <button type="button" class="st-chain-commit">Commit</button>
                                    </div>
                                    <div class="st-chain-row">
                                        <span>LOT-9012-GUA</span>
                                        <span class="st-chain-syncing"><el-icon :size="14" class="st-icon-spin"><RefreshRight /></el-icon> Syncing</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </template>
            </div>
        </div>

        <!-- Admin reject reason -->
        <el-dialog v-model="rejectDialogOpen" width="min(440px, calc(100vw - 2rem))" align-center :close-on-click-modal="false" class="st-reject-modal">
            <template #header>
                <div class="st-reject-modal__title">Reject {{ rejectingStore?.owner_name }}'s Store</div>
            </template>
            <el-input v-model="rejectForm.reason" type="textarea" :rows="3" placeholder="Explain why this store is being rejected (optional)" />
            <template #footer>
                <button type="button" class="st-btn-outline" @click="rejectDialogOpen = false">Cancel</button>
                <button type="button" class="st-btn-primary st-btn-primary--danger" :disabled="rejectForm.processing" @click="submitReject">
                    {{ rejectForm.processing ? 'Rejecting…' : 'Reject Store' }}
                </button>
            </template>
        </el-dialog>

        <!-- ── Register New ▾ modals ─────────────────────────────────────── -->
        <AddFarmCollectionModal
            v-model="addCollectionOpen"
            :coffee-type-options="coffeeTypeOptions"
            :harvest-season-options="harvestSeasonOptions"
            :currency-options="currencyOptions"
        />
        <AddBatchModal v-model="addBatchOpen" :process-options="processOptions" :variety-options="coffeeTypeOptions" :drying-method-options="dryingMethodOptions" :currency-options="currencyOptions" :milling-options="millingOptions" />
        <AddLotModal v-model="addLotOpen" :batches="batches" :process-options="processOptions" />
    </StoreLayout>
</template>

<style scoped>
/* This page ports a Stitch mockup whose brand primary (#000000, "Deep
   Roast") is a different color than --dp-primary (#121611) — an old
   dark-theme value that collides under the same token name. Literal hex
   from the DESIGN.md palette is used throughout instead, matching the
   convention already established on BusinessProfile/MarketListings/
   Confirmation. Surface/neutral tokens happen to match --dp-* numerically,
   but are still hardcoded here so the whole page stays self-contained. */
.st-page {
    /* UI.md theme (2026-08-24): app-wide default, superseding the
       earlier Claude Console pass. See reference_ui_md_design_system
       memory for the full spec. */
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
    /* Standard card border for this literal-hex theme — flat, not
       alpha-blended, since --outline-variant is already the light
       target color. Reuse this exact value on other pages. */
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
.st-btn-primary--danger { background: var(--error); }
.st-btn-primary:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
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

.st-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 16px;
    border: none;
    border-radius: 6px;
    background: var(--surface-container-high);
    color: var(--on-surface);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
    white-space: nowrap;
}
.st-btn-outline:hover:not(:disabled) { background: color-mix(in srgb, var(--outline-variant) 35%, transparent); }
.st-btn-outline--danger { color: var(--error); background: transparent; border: 1px solid color-mix(in srgb, var(--error) 35%, transparent); }
.st-btn-outline--danger:hover { background: var(--error-container); }
.st-btn-outline:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

/* ── Body ──────────────────────────────────────────────────────────────── */
/* No local page padding — matching the app's native pattern (e.g.
   WalletPage.vue): DesignPreviewLayout's .dp-main already supplies the
   page margin, and sections are spaced via .st-page's own gap instead of
   a second, nested padding layer. */

/* ── Editorial hero — font, spacing, and margins copied from
   MarketListings.vue's .mktl-topbar, the app's default page-header
   pattern (2026-08-24). ─────────────────────────────────────────────── */
.st-verified { display: flex; flex-direction: column; gap: 32px; }
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

/* ── Admin pending panel ───────────────────────────────────────────────── */
.st-pending-card { background: #fffbeb; border-radius: 6px; overflow: hidden; }
.st-pending-card__head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 700;
    color: #92400e;
    border-bottom: 1px solid #fde68a;
}
.st-pending-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #f59e0b;
    color: #fff;
    font-size: 11px;
}
.st-pending-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 16px;
    border-bottom: 1px solid #fef3c7;
}
.st-pending-row:last-child { border-bottom: none; }
.st-pending-row__name { font-size: 13px; font-weight: 700; color: var(--on-surface); }
.st-pending-row__meta { font-size: 12px; color: #92400e; margin-top: 1px; }
.st-pending-row__actions { display: flex; gap: 8px; flex-shrink: 0; }

/* ── Empty state ───────────────────────────────────────────────────────── */
.st-empty { text-align: center; padding: 4rem 1rem; }
.st-empty__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--primary-container);
    color: var(--on-primary-container);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
}
.st-empty__title { font-size: 17px; font-weight: 800; letter-spacing: -0.006em; color: var(--primary); margin-bottom: 4px; }
.st-empty__text { font-size: 13px; color: var(--on-surface-variant); margin: 0 auto; max-width: 340px; line-height: 1.5; }

/* ── Status banners ────────────────────────────────────────────────────── */
.st-status-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 18px;
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    max-width: 560px;
    margin: 0 auto;
    background: var(--surface-container-lowest);
}
.st-status-banner--pending { color: var(--primary); }
.st-status-banner--pending .el-icon { color: var(--secondary); }
.st-status-banner--rejected { color: var(--on-error-container); }
.st-status-banner--rejected .el-icon { color: var(--error); }
.st-status-banner__title { font-size: 14px; font-weight: 700; color: var(--on-surface); }
.st-status-banner__text { font-size: 13px; margin: 2px 0 0; line-height: 1.5; color: var(--on-surface-variant); }

/* ── KPI snapshot ──────────────────────────────────────────────────────── */
.st-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.st-kpi {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 22px;
}
.st-kpi__top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; }
.st-kpi__icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.st-kpi__icon--a { background: color-mix(in srgb, var(--primary) 6%, var(--surface-container-lowest)); color: var(--primary); }
.st-kpi__icon--b { background: color-mix(in srgb, var(--secondary-container) 22%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.st-kpi__icon--c { background: color-mix(in srgb, var(--tertiary-container) 12%, var(--surface-container-lowest)); color: var(--tertiary); }
.st-kpi__icon--d { background: color-mix(in srgb, #88d982 22%, var(--surface-container-lowest)); color: var(--secondary); }
.st-kpi__chip {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
}
.st-kpi__chip--up { background: color-mix(in srgb, var(--secondary) 10%, transparent); color: var(--secondary); }
.st-kpi__check { color: var(--secondary); }
.st-kpi__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--on-surface-variant); margin-bottom: 4px; }
.st-kpi__value { font-size: 24px; font-weight: 800; letter-spacing: -0.012em; color: var(--on-surface); line-height: 1.2; font-variant-numeric: tabular-nums; }
.st-kpi__unit { font-family: var(--sans); font-size: 14px; font-weight: 500; color: var(--on-surface-variant); }
.st-kpi__sub { display: flex; align-items: center; gap: 4px; margin-top: 8px; font-size: 12px; color: var(--on-surface-variant); }
.st-kpi__sub--green { color: var(--secondary); font-weight: 600; }

/* ── Two-column layout ─────────────────────────────────────────────────── */
.st-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 28px; align-items: start; }
.st-col-main { min-width: 0; display: flex; flex-direction: column; gap: 16px; }
.st-col-side { min-width: 0; display: flex; flex-direction: column; gap: 20px; }

/* ── Uncommitted Inventory tabs (el-tabs) ─────────────────────────────── */
/* This Element Plus version has no #extra slot on el-tabs, so the
   filter/more icon buttons are positioned over the tab nav row instead. */
.st-tabs-wrap { position: relative; }
.st-section-actions { position: absolute; top: 0; right: 0; height: 40px; display: flex; align-items: center; gap: 4px; flex-shrink: 0; z-index: 1; }
.st-el-tabs :deep(.el-tabs__header) { margin: 0 0 16px; }
.st-el-tabs :deep(.el-tabs__nav-wrap::after) { background: var(--card-border); height: 1px; }
.st-el-tabs :deep(.el-tabs__item) {
    height: 40px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--on-surface-variant);
}
.st-el-tabs :deep(.el-tabs__item:first-child) { padding-left: 0; }
.st-el-tabs :deep(.el-tabs__item:hover) { color: var(--on-surface); }
.st-el-tabs :deep(.el-tabs__item.is-active) { color: var(--primary); font-weight: 700; }
.st-el-tabs :deep(.el-tabs__active-bar) { background-color: var(--primary); }
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

/* ── Table card ────────────────────────────────────────────────────────── */
.st-table-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    overflow: hidden;
}

/* ── Element Plus table theming ───────────────────────────────────────── */
.st-table-scroll { overflow-x: auto; }
.st-el-table {
    --el-table-bg-color: var(--surface-container-lowest);
    --el-table-tr-bg-color: var(--surface-container-lowest);
    --el-table-border-color: var(--card-border);
    --el-table-header-bg-color: var(--surface-container-low);
    --el-table-header-text-color: var(--on-surface-variant);
    --el-table-text-color: var(--on-surface);
    --el-table-row-hover-bg-color: color-mix(in srgb, var(--surface-container-low) 50%, transparent);
    font-family: var(--sans);
    width: 100%;
}
.st-el-table :deep(.el-table__inner-wrapper::before) { display: none; }
.st-el-table :deep(.el-table__header-wrapper th.el-table__cell) {
    padding: 14px 10px;
    border-bottom: 1px solid var(--card-border);
}
.st-el-table :deep(.el-table__header-wrapper th.el-table__cell:first-child) { padding-left: 16px; }
.st-el-table :deep(.el-table__header-wrapper th.el-table__cell:last-child) { padding-right: 16px; }
.st-el-table :deep(.el-table__body-wrapper td.el-table__cell) {
    padding: 18px 10px;
    border-bottom: 1px solid var(--card-border);
}
.st-el-table :deep(.el-table__body-wrapper td.el-table__cell:first-child) { padding-left: 16px; }
.st-el-table :deep(.el-table__body-wrapper td.el-table__cell:last-child) { padding-right: 16px; }
.st-el-table :deep(.el-table__row:last-child td.el-table__cell) { border-bottom: none; }
.st-el-table :deep(.el-table__row .cell) { line-height: 1.4; }
.st-el-table :deep(.el-table__row:hover .st-link-action) { opacity: 1; }
.st-el-table :deep(.el-table__empty-block) { min-height: 220px; }

.st-th { display: inline-flex; align-items: flex-start; gap: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; line-height: 1.4; }
.st-th .el-icon { margin-top: 1px; flex-shrink: 0; font-size: 13px; color: var(--on-surface-variant); }
.st-th--end { justify-content: flex-end; }

.st-cell { display: flex; align-items: center; gap: 10px; font-size: 13.5px; }

.st-empty-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 2.5rem 1rem;
    color: var(--on-surface-variant);
    font-size: 13px;
}
.st-empty-cell__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 999px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
}

.st-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--surface-container-high);
    color: var(--on-surface-variant);
    flex-shrink: 0;
}
.st-name { font-size: 13.5px; font-weight: 700; color: var(--on-surface); }
.st-caption { font-size: 11.5px; margin-top: 2px; }
.st-muted { color: var(--on-surface-variant); }
.st-commodity { font-size: 13.5px; color: var(--on-surface); }

.st-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}
.st-pill--a { background: var(--surface-container); color: var(--on-surface-variant); border: 1px solid color-mix(in srgb, var(--outline-variant) 50%, transparent); }
.st-pill--b { background: color-mix(in srgb, var(--secondary-container) 35%, transparent); color: var(--on-secondary-container); border: 1px solid color-mix(in srgb, var(--secondary-container) 60%, transparent); }

.st-link-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: none;
    background: none;
    color: var(--primary);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    padding: 0;
    opacity: 0;
    transition: opacity .15s ease;
}
.st-link-action:hover { text-decoration: underline; }
.st-link-action:focus-visible { opacity: 1; outline: 2px solid var(--primary); outline-offset: 2px; }
.st-link-action--muted { color: var(--on-surface-variant); }
.st-link-action--muted:hover { color: var(--on-surface); }

.st-pagination-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 24px;
    border-top: 1px solid color-mix(in srgb, var(--outline-variant) 15%, transparent);
    background: color-mix(in srgb, var(--surface-container-low) 25%, transparent);
}
.st-pagination-foot__text { font-size: 12px; color: var(--on-surface-variant); }
.st-pagination-foot__nav { display: flex; gap: 12px; }
.st-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: none;
    color: var(--on-surface-variant);
    cursor: pointer;
    padding: 2px;
}
.st-page-btn:hover:not(:disabled) { color: var(--primary); }
.st-page-btn:disabled { opacity: 0.3; cursor: default; }
.st-page-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
.st-icon-rotate-90 { transform: rotate(90deg); }
.st-icon-rotate-270 { transform: rotate(270deg); }

/* ── Right rail: Quick Transfer ───────────────────────────────────────── */
.st-side-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 24px;
}
.st-side-card__eyebrow {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--on-surface-variant);
    margin: 0 0 22px;
}

.st-qt-form { display: flex; flex-direction: column; gap: 20px; }
.st-qt-field { display: flex; flex-direction: column; gap: 8px; }
.st-qt-field__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-variant); }
/* Element Plus fields (el-select, el-input, el-input-number), skinned to
   match this card's original plain-HTML look — same bg/radius/shadow. */
.st-qt-el { width: 100%; }
.st-qt-el :deep(.el-select__wrapper),
.st-qt-el :deep(.el-input__wrapper) {
    background: var(--surface-container-low);
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .05) !important;
    border-radius: 12px;
    padding: 6px 14px;
    min-height: 44px;
}
.st-qt-el :deep(.el-select__wrapper.is-focused),
.st-qt-el :deep(.el-input__wrapper.is-focus) {
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .05), 0 0 0 2px color-mix(in srgb, var(--primary) 20%, transparent) !important;
}
.st-qt-el :deep(.el-select__placeholder),
.st-qt-el :deep(.el-select__selected-item),
.st-qt-el :deep(.el-input__inner) { font-size: 13.5px; color: var(--on-surface); font-family: inherit; }
.st-qt-el :deep(.el-select__prefix) { color: var(--on-surface-variant); margin-right: 6px; }
.st-qt-el--number :deep(.el-input-number) { width: 100%; }

.st-qt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.st-qt-submit { width: 100%; justify-content: center; margin-top: 4px; }

/* ── Right rail: Blockchain Status (dark) ─────────────────────────────── */
.st-side-card--dark { background: var(--primary-container); border-color: rgba(255, 255, 255, .08); color: #fff; }
.st-side-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.st-side-card__head-left { display: flex; align-items: center; gap: 10px; }
.st-side-card__icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: rgba(255, 255, 255, .1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
.st-side-card__eyebrow--dark { color: #fff; margin: 0; }
.st-pulse { display: flex; align-items: center; gap: 4px; }
.st-pulse i { width: 6px; height: 6px; border-radius: 50%; background: var(--secondary); display: block; }
.st-pulse i:first-child { animation: st-pulse-fade 1.6s ease-in-out infinite; }
.st-pulse i:last-child { opacity: .4; }
@keyframes st-pulse-fade { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

.st-side-card__text { font-size: 13.5px; line-height: 1.6; color: rgba(255, 255, 255, .8); margin: 0 0 20px; }

.st-chain-list { display: flex; flex-direction: column; gap: 12px; }
.st-chain-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .05);
    border: 1px solid rgba(255, 255, 255, .08);
    font-size: 13px;
    font-weight: 600;
}
.st-chain-commit {
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    background: var(--on-primary-container);
    color: var(--primary-container);
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    cursor: pointer;
    transition: background .15s ease;
}
.st-chain-commit:hover { background: #fff; }
.st-chain-commit:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
.st-chain-syncing { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgba(255, 255, 255, .55); }
.st-icon-spin { animation: st-spin 1.4s linear infinite; }
@keyframes st-spin { to { transform: rotate(360deg); } }

@media (prefers-reduced-motion: reduce) {
    .st-kpi,
    .st-btn-primary,
    .st-row,
    .st-pulse i,
    .st-icon-spin { transition: none; animation: none; }
}

@media (max-width: 1180px) {
    .st-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 1024px) {
    .st-layout { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .st-page { gap: 16px; }
    .st-verified { gap: 18px; }
    .st-hero { flex-direction: column; align-items: stretch; }
    .st-hero__actions { flex-direction: column; align-items: stretch; }
    .st-hero__actions .st-btn-outline,
    .st-hero__actions .st-btn-primary { justify-content: center; }
    .st-kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .st-kpi { padding: 16px; }
    .st-kpi__value { font-size: 22px; }
    .st-link-action { opacity: 1; }
    .st-qt-grid { grid-template-columns: 1fr; }
}
</style>

<style>
/* Dialog teleports to <body>, outside .dp-shell, so --dp-* custom
   properties don't cascade in — literal hex from the same palette is
   used here instead, matching DesignPreviewLayout's own teleported
   popovers/dropdowns. */
.el-dialog.st-reject-modal { border-radius: 6px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
.st-reject-modal__title { font-size: 16px; font-weight: 800; color: #000000; }
.el-dialog.st-reject-modal .el-dialog__footer { display: flex; justify-content: flex-end; gap: 10px; }
</style>
