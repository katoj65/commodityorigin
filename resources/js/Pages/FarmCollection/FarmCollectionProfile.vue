<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import EditFarmCollectionModal from '@/Components/Modals/EditFarmCollectionModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    ArrowDown, ArrowRight, Box, Clock, Close, Coffee, Coin, Delete, Document, EditPen, Location, Medal, Message, OfficeBuilding, Phone, PriceTag, Ticket, User, WarningFilled,
} from '@element-plus/icons-vue';

const props = defineProps({
    collection: { type: Object, required: true },
    coffeeTypeOptions: { type: Array, default: () => [] },
    harvestSeasonOptions: { type: Array, default: () => [] },
    currencyOptions: { type: Array, default: () => [] },
});

const editDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const deleting = ref(false);
const farmModalOpen = ref(false);

function handleActionCommand(command) {
    if (command === 'edit') editDialogOpen.value = true;
    else if (command === 'delete') deleteDialogOpen.value = true;
}

function readCookie(name) {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
    return match ? decodeURIComponent(match[1]) : null;
}

function deleteCollection() {
    deleting.value = true;
    // Deleting the very record this page shows means the backend's
    // back()-redirect target (this page's own URL) would 404 after the
    // row is gone. Both Inertia's router.delete() and a plain
    // axios.delete() transparently FOLLOW that redirect (the browser's
    // XHR layer can't be told not to — confirmed live: Chromium replays
    // it as DELETE against the 404 target, which itself then 405s since
    // that route is GET-only), so either would misreport a successful
    // delete as a failure. `fetch` with redirect:'manual' is the only
    // browser API that can see "the server redirected" (response.type
    // === 'opaqueredirect') without following it, so that's used here
    // instead — treat any redirect as success, navigate to the Store
    // page ourselves.
    fetch(route('farm.collections.destroy', [props.collection.farm_id, props.collection.id]), {
        method: 'DELETE',
        redirect: 'manual',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': readCookie('XSRF-TOKEN') || '',
        },
    })
        .then((response) => {
            if (response.type !== 'opaqueredirect' && !response.ok) {
                throw new Error(`Unexpected response: ${response.status}`);
            }
            router.visit(route('store.show'));
            ElNotification({ title: 'Collection Deleted', message: 'The farm collection was removed.', type: 'success', duration: 3200, offset: 84 });
        })
        .catch(() => {
            ElNotification({ title: 'Delete Failed', message: 'Could not delete this collection.', type: 'error', duration: 3200, offset: 84 });
        })
        .finally(() => {
            deleting.value = false;
            deleteDialogOpen.value = false;
        });
}

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

const farmName = computed(() => props.collection.farm?.name || `Farm #${props.collection.farm_id}`);

const hasDefects = computed(() => Number(props.collection.initial_defects || 0) > 0);
const defectsKnown = computed(() => props.collection.initial_defects !== null && props.collection.initial_defects !== undefined);
const qualityScoreKnown = computed(() => props.collection.initial_quality_score !== null && props.collection.initial_quality_score !== undefined);

const farmLocation = computed(() => {
    const farm = props.collection.farm;
    if (!farm) return '';
    return [farm.district, farm.region, farm.country].filter(Boolean).join(', ');
});

const recorderInitials = computed(() => {
    const parts = (props.collection.user?.name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return parts.length === 1 ? parts[0][0].toUpperCase() : (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const deleteMessage = computed(() => `Are you sure you want to delete this collection recorded against ${farmName.value}? This action cannot be undone.`);
</script>

<template>
    <DesignPreviewLayout title="Farm Collection">
        <div class="fcp-page">
            <div class="fcp-page-head">
                <div class="fcp-page-head__text">
                    <h1 class="fcp-page-title">Farm Collection</h1>
                    <p class="fcp-page-subtitle">Everything recorded about this coffee collection, including where it came from, its quality at intake, and payment status.</p>
                </div>
                <el-dropdown v-if="collection.can_manage" trigger="click" @command="handleActionCommand">
                    <button type="button" class="fcp-btn-outline fcp-actions-btn" :disabled="deleting">
                        Actions <el-icon class="fcp-caret"><ArrowDown /></el-icon>
                    </button>
                    <template #dropdown>
                        <el-dropdown-menu class="fcp-actions-menu">
                            <el-dropdown-item command="edit"><el-icon><EditPen /></el-icon> Edit</el-dropdown-item>
                            <el-dropdown-item command="delete" class="fcp-actions-menu__danger"><el-icon><Delete /></el-icon> Delete</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>

            <div class="fcp-hero">
                <div class="fcp-hero__icon"><el-icon :size="22"><OfficeBuilding /></el-icon></div>
                <div class="fcp-hero__text">
                    <div class="fcp-hero__title-row">
                        <h1 class="fcp-title">{{ farmName }}</h1>
                        <span v-if="collection.collection_code" class="fcp-hero__code">
                            <el-icon :size="11"><PriceTag /></el-icon>{{ collection.collection_code }}
                        </span>
                    </div>
                    <p class="fcp-subtitle">
                        Collected {{ formatDate(collection.collection_date) }}
                        <span v-if="collection.harvest_season"> · {{ collection.harvest_season }} season</span>
                    </p>
                </div>
                <div class="fcp-hero__badges">
                    <span v-if="collection.status" class="fcp-pill fcp-pill--status" :class="`fcp-pill--${collection.status}`">{{ collection.status }}</span>
                    <span v-if="collection.initial_grade" class="fcp-pill fcp-pill--grade">Grade {{ collection.initial_grade }}</span>
                    <span v-if="collection.payment_status" class="fcp-pill fcp-pill--status" :class="`fcp-pill--${collection.payment_status}`">{{ collection.payment_status }}</span>
                </div>
            </div>

            <div class="fcp-stat-grid">
                <div class="fcp-stat">
                    <div class="fcp-stat__icon fcp-stat__icon--a"><el-icon><Box /></el-icon></div>
                    <div class="fcp-stat__label">Quantity</div>
                    <div class="fcp-stat__value">{{ Number(collection.quantity || 0).toLocaleString() }} <span class="fcp-stat__unit">{{ collection.unit || '' }}</span></div>
                    <div class="fcp-stat__caption">Recorded at intake</div>
                </div>
                <div class="fcp-stat">
                    <div class="fcp-stat__icon fcp-stat__icon--b"><el-icon><Coin /></el-icon></div>
                    <div class="fcp-stat__label">Price / Unit</div>
                    <div class="fcp-stat__value">{{ formatMoney(collection.collection_price, collection.currency) }}</div>
                    <div v-if="collection.unit" class="fcp-stat__caption">per {{ collection.unit }}</div>
                </div>
                <div class="fcp-stat">
                    <div class="fcp-stat__icon" :class="hasDefects ? 'fcp-stat__icon--warn' : 'fcp-stat__icon--good'"><el-icon><WarningFilled /></el-icon></div>
                    <div class="fcp-stat__label">Defects</div>
                    <div class="fcp-stat__value">{{ collection.initial_defects ?? '—' }}</div>
                    <span v-if="defectsKnown" class="fcp-stat__pill" :class="hasDefects ? 'fcp-stat__pill--warn' : 'fcp-stat__pill--good'">
                        {{ hasDefects ? 'Flagged' : 'Clean' }}
                    </span>
                </div>
                <div class="fcp-stat">
                    <div class="fcp-stat__icon fcp-stat__icon--c"><el-icon><Medal /></el-icon></div>
                    <div class="fcp-stat__label">Quality Score</div>
                    <div class="fcp-stat__value">{{ collection.initial_quality_score ?? '—' }} <span v-if="qualityScoreKnown" class="fcp-stat__unit">/100</span></div>
                </div>
            </div>

            <div class="fcp-layout">
                <div class="fcp-col-main">
                    <div class="fcp-card">
                        <h2 class="fcp-card__title"><el-icon><Coffee /></el-icon> Collection Details</h2>
                        <dl class="fcp-dl">
                            <div class="fcp-dl__row"><dt>Coffee Type</dt><dd>{{ collection.coffee_type || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Variety</dt><dd>{{ collection.variety || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Harvest Season</dt><dd>{{ collection.harvest_season || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Collection Date</dt><dd>{{ formatDate(collection.collection_date) }}</dd></div>
                            <div class="fcp-dl__row" v-if="collection.reference"><dt>Reference</dt><dd>{{ collection.reference }}</dd></div>
                        </dl>
                    </div>

                    <div class="fcp-card">
                        <h2 class="fcp-card__title"><el-icon><WarningFilled /></el-icon> Quality Assessment</h2>
                        <dl class="fcp-dl">
                            <div class="fcp-dl__row"><dt>Initial Moisture</dt><dd>{{ collection.initial_moisture !== null && collection.initial_moisture !== undefined ? `${collection.initial_moisture}%` : '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Initial Defects</dt><dd>{{ collection.initial_defects ?? '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Initial Grade</dt><dd>{{ collection.initial_grade || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Initial Quality Score</dt><dd>{{ collection.initial_quality_score ?? '—' }}</dd></div>
                        </dl>
                    </div>

                    <div class="fcp-card">
                        <h2 class="fcp-card__title"><el-icon><Ticket /></el-icon> Payment</h2>
                        <dl class="fcp-dl">
                            <div class="fcp-dl__row"><dt>Price / Unit</dt><dd>{{ formatMoney(collection.collection_price, collection.currency) }}</dd></div>
                            <div class="fcp-dl__row"><dt>Currency</dt><dd>{{ collection.currency || '—' }}</dd></div>
                            <div class="fcp-dl__row"><dt>Payment Status</dt><dd class="fcp-dl__capitalize">{{ collection.payment_status || '—' }}</dd></div>
                        </dl>
                    </div>

                    <div class="fcp-card" v-if="collection.notes">
                        <h2 class="fcp-card__title"><el-icon><Document /></el-icon> Notes</h2>
                        <p class="fcp-notes">{{ collection.notes }}</p>
                    </div>
                </div>

                <div class="fcp-col-side">
                    <div class="fcp-side-card">
                        <div class="fcp-side-card__head">
                            <span class="fcp-side-card__head-icon"><el-icon><User /></el-icon></span>
                            <h3 class="fcp-side-card__eyebrow">Recorded By</h3>
                        </div>
                        <div class="fcp-recorder">
                            <div class="fcp-recorder__avatar">{{ recorderInitials }}</div>
                            <div class="fcp-recorder__body">
                                <div class="fcp-recorder__name">{{ collection.user?.name || 'Unknown' }}</div>
                                <div class="fcp-recorder__meta"><el-icon :size="12"><Clock /></el-icon> {{ formatDateTime(collection.created_at) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="fcp-side-card">
                        <div class="fcp-side-card__head">
                            <span class="fcp-side-card__head-icon"><el-icon><OfficeBuilding /></el-icon></span>
                            <h3 class="fcp-side-card__eyebrow">Farm</h3>
                        </div>
                        <div class="fcp-farm-summary">
                            <div class="fcp-farm-summary__name">{{ farmName }}</div>
                            <div class="fcp-farm-summary__meta">
                                <span v-if="collection.farm?.farm_code" class="fcp-farm-code">
                                    <el-icon :size="11"><PriceTag /></el-icon>
                                    {{ collection.farm.farm_code }}
                                </span>
                                <span v-if="collection.farm?.district || collection.farm?.country" class="fcp-farm-loc">
                                    <el-icon :size="12"><Location /></el-icon>
                                    {{ [collection.farm?.district, collection.farm?.country].filter(Boolean).join(', ') }}
                                </span>
                            </div>
                        </div>
                        <button v-if="collection.farm_id" type="button" class="fcp-farm-link" @click="farmModalOpen = true">
                            View Farm <el-icon class="fcp-farm-link__arrow"><ArrowRight /></el-icon>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <EditFarmCollectionModal
            v-if="collection.can_manage"
            v-model="editDialogOpen"
            :collection="collection"
            :coffee-type-options="coffeeTypeOptions"
            :harvest-season-options="harvestSeasonOptions"
            :currency-options="currencyOptions"
        />

        <ConfirmDialog
            v-model="deleteDialogOpen"
            eyebrow="Farm Collection"
            title="Delete Collection"
            :message="deleteMessage"
            confirm-text="Delete Collection"
            :auto-close="false"
            :loading="deleting"
            @confirm="deleteCollection"
        />

        <el-dialog v-model="farmModalOpen" width="min(460px, calc(100vw - 2rem))" align-center :show-close="false" class="fcp-farm-modal">
            <template #header>
                <div class="fcp-farm-modal__head">
                    <div class="fcp-farm-modal__head-icon"><el-icon :size="18"><OfficeBuilding /></el-icon></div>
                    <div class="fcp-farm-modal__head-text">
                        <div class="fcp-farm-modal__eyebrow">Farm</div>
                        <div class="fcp-farm-modal__title">{{ farmName }}</div>
                    </div>
                    <button type="button" class="fcp-farm-modal__close" aria-label="Close" @click="farmModalOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="fcp-farm-modal__body">
                <div v-if="collection.farm?.status || collection.farm?.coffee_type" class="fcp-farm-modal__badges">
                    <span v-if="collection.farm?.status" class="fcp-pill fcp-pill--grade">{{ collection.farm.status }}</span>
                    <span v-if="collection.farm?.coffee_type" class="fcp-pill fcp-pill--grade">{{ collection.farm.coffee_type }}</span>
                </div>
                <dl class="fcp-dl">
                    <div v-if="collection.farm?.farm_code" class="fcp-dl__row"><dt><el-icon :size="13"><PriceTag /></el-icon> Farm Code</dt><dd>{{ collection.farm.farm_code }}</dd></div>
                    <div v-if="farmLocation" class="fcp-dl__row"><dt><el-icon :size="13"><Location /></el-icon> Location</dt><dd>{{ farmLocation }}</dd></div>
                    <div v-if="collection.farm?.total_area !== null && collection.farm?.total_area !== undefined" class="fcp-dl__row"><dt>Total Area</dt><dd>{{ collection.farm.total_area }} ha</dd></div>
                    <div v-if="collection.farm?.coffee_area !== null && collection.farm?.coffee_area !== undefined" class="fcp-dl__row"><dt>Coffee Area</dt><dd>{{ collection.farm.coffee_area }} ha</dd></div>
                    <div v-if="collection.farm?.elevation !== null && collection.farm?.elevation !== undefined" class="fcp-dl__row"><dt>Elevation</dt><dd>{{ collection.farm.elevation }} m</dd></div>
                    <div v-if="collection.farm?.tel" class="fcp-dl__row"><dt><el-icon :size="13"><Phone /></el-icon> Phone</dt><dd>{{ collection.farm.tel }}</dd></div>
                    <div v-if="collection.farm?.email" class="fcp-dl__row"><dt><el-icon :size="13"><Message /></el-icon> Email</dt><dd>{{ collection.farm.email }}</dd></div>
                </dl>
            </div>
        </el-dialog>
    </DesignPreviewLayout>
</template>

<style scoped>
.fcp-page {
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

.fcp-page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }
.fcp-page-head__text { display: flex; flex-direction: column; gap: 6px; }
.fcp-page-title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    color: var(--primary);
    margin: 0;
}
.fcp-page-subtitle { font-size: .9375rem; line-height: 1.5rem; font-weight: 400; color: var(--on-surface-variant); margin: 0; max-width: 640px; }

.fcp-actions-btn { flex-shrink: 0; }
.fcp-actions-btn:disabled { opacity: .6; cursor: default; }
.fcp-caret { font-size: 11px; margin-left: -2px; }

.fcp-hero {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 24px;
}
.fcp-hero__icon {
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
.fcp-hero__text { flex: 1; min-width: 0; }
.fcp-hero__title-row { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin: 0 0 4px; }
.fcp-title { font-size: 1.375rem; font-weight: 800; letter-spacing: -0.012em; color: var(--primary); margin: 0; }
.fcp-hero__code {
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
.fcp-subtitle { font-size: .9375rem; color: var(--on-surface-variant); margin: 0; }
.fcp-hero__badges { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.fcp-pill {
    display: inline-flex;
    align-items: center;
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    text-transform: capitalize;
}
.fcp-pill--grade { background: var(--surface-container); color: var(--on-surface-variant); border: 1px solid color-mix(in srgb, var(--card-border) 80%, transparent); }
.fcp-pill--status { background: var(--secondary-container); color: var(--on-secondary-container); }
.fcp-pill--pending { background: #fef3c7; color: #92400e; }
.fcp-pill--cancelled { background: var(--error-container); color: var(--on-error-container); }

.fcp-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.fcp-stat {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.fcp-stat:hover { border-color: color-mix(in srgb, var(--on-surface) 18%, var(--card-border)); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04); }
.fcp-stat__icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    font-size: 16px;
}
.fcp-stat__icon--a { background: color-mix(in srgb, var(--primary) 7%, var(--surface-container-lowest)); color: var(--primary); }
.fcp-stat__icon--b { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.fcp-stat__icon--c { background: #EEF2FF; color: #4338CA; }
.fcp-stat__icon--good { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.fcp-stat__icon--warn { background: #fef3c7; color: #92400e; }
.fcp-stat__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-variant); margin-bottom: 6px; }
.fcp-stat__value { font-size: 24px; font-weight: 800; letter-spacing: -0.01em; color: var(--on-surface); font-variant-numeric: tabular-nums; line-height: 1.2; }
.fcp-stat__unit { font-size: 13px; font-weight: 600; color: var(--on-surface-variant); }
.fcp-stat__caption { font-size: 11.5px; color: var(--on-surface-variant); margin-top: 7px; }

.fcp-stat__pill {
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
.fcp-stat__pill--good { background: color-mix(in srgb, var(--secondary-container) 55%, var(--surface-container-lowest)); color: var(--on-secondary-container); }
.fcp-stat__pill--warn { background: #fef3c7; color: #92400e; }

.fcp-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 20px; align-items: start; }
.fcp-col-main { min-width: 0; display: flex; flex-direction: column; gap: 16px; }
.fcp-col-side { min-width: 0; display: flex; flex-direction: column; gap: 16px; }

.fcp-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px 24px;
}
.fcp-card__title {
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

.fcp-dl { margin: 0; display: flex; flex-direction: column; }
.fcp-dl__row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px dashed var(--card-border);
    font-size: 13.5px;
}
.fcp-dl__row:last-child { border-bottom: none; padding-bottom: 0; }
.fcp-dl__row dt { display: inline-flex; align-items: center; gap: 5px; color: var(--on-surface-variant); }
.fcp-dl__row dt .el-icon { color: var(--on-surface-variant); }
.fcp-dl__row dd { margin: 0; font-weight: 600; color: var(--on-surface); text-align: right; }
.fcp-dl__capitalize { text-transform: capitalize; }

.fcp-notes { font-size: 13.5px; line-height: 1.6; color: var(--on-surface); margin: 0; white-space: pre-wrap; }

.fcp-side-card {
    background: var(--surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--card-radius);
    padding: 20px;
}
.fcp-side-card__head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.fcp-side-card__head-icon {
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
.fcp-side-card__eyebrow {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--on-surface-variant);
    margin: 0;
}

.fcp-recorder { display: flex; align-items: center; gap: 12px; }
.fcp-recorder__avatar {
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
.fcp-recorder__body { min-width: 0; }
.fcp-recorder__name { font-size: 14px; font-weight: 700; color: var(--on-surface); }
.fcp-recorder__meta { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--on-surface-variant); margin-top: 3px; }
.fcp-recorder__meta .el-icon { flex-shrink: 0; }

.fcp-farm-summary { margin-bottom: 16px; }
.fcp-farm-summary__name { font-size: 15px; font-weight: 800; letter-spacing: -0.005em; color: var(--on-surface); }
.fcp-farm-summary__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 9px; }
.fcp-farm-code {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 4px;
    background: var(--surface-container);
    color: var(--on-surface-variant);
    font-size: 11px;
    font-weight: 700;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    letter-spacing: .01em;
}
.fcp-farm-loc { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: var(--on-surface-variant); }

.fcp-farm-link {
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
.fcp-farm-link:hover { opacity: .88; }
.fcp-farm-link__arrow { font-size: 13px; transition: transform .15s ease; }
.fcp-farm-link:hover .fcp-farm-link__arrow { transform: translateX(2px); }

.fcp-btn-outline {
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
.fcp-btn-outline:hover { background: color-mix(in srgb, var(--card-border) 60%, transparent); }

@media (max-width: 1024px) {
    .fcp-layout { grid-template-columns: 1fr; }
    .fcp-stat-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .fcp-page-title { font-size: 1.25rem; line-height: 1.6rem; }
    .fcp-hero { flex-direction: column; align-items: flex-start; }
    .fcp-hero__badges { flex-wrap: wrap; }
    .fcp-stat-grid { grid-template-columns: 1fr; }
}

@media (max-width: 575.98px) {
    .fcp-page-head { flex-direction: column; align-items: stretch; }
}
</style>

<style>
/* Dropdown teleports to <body>, outside scoped styles — literal hex
   from the same UI.md palette, matching StorePage.vue's .st-register-menu. */
.fcp-actions-menu.el-dropdown-menu { border-radius: 6px; border: 1px solid #E5E7EB; padding: 4px; }
.fcp-actions-menu .el-dropdown-menu__item {
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #121516;
    padding: 8px 12px;
}
.fcp-actions-menu .el-dropdown-menu__item:hover { background: #F5F6F7; color: #121516; }
.fcp-actions-menu .fcp-actions-menu__danger { color: #F85149; }
.fcp-actions-menu .fcp-actions-menu__danger:hover { background: #FEEDED; color: #C6413A; }

/* "View Farm" brief-details dialog — same header/body/footer chrome
   convention as every other modal on this page, literal UI.md hex since
   el-dialog teleports outside scoped styles. */
.el-dialog.fcp-farm-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}
.el-dialog.fcp-farm-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.fcp-farm-modal .el-dialog__body { padding: 0; }
.el-dialog.fcp-farm-modal .el-dialog__footer { padding: 0; }

.fcp-farm-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.fcp-farm-modal__head-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    background: #F1F2F3;
    color: #121516;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.fcp-farm-modal__head-text { flex: 1; min-width: 0; }
.fcp-farm-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.fcp-farm-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.fcp-farm-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: #F1F2F3;
    color: #4B5457;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}
.fcp-farm-modal__close:hover { background: #E5E7EB; color: #121516; }

.fcp-farm-modal__body { padding: 22px 24px 24px; max-height: 60vh; overflow-y: auto; }
.fcp-farm-modal__badges { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
</style>
