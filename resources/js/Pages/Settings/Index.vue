<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Close, Coin, Delete, Plus, PriceTag, Van } from '@element-plus/icons-vue';

const props = defineProps({
    exchangeRates: { type: Array, default: () => [] },
    priceIndexes: { type: Array, default: () => [] },
    deliveryMethods: { type: Array, default: () => [] },
    incoterms: { type: Array, default: () => [] },
});

const fmt = (value, digits = 2) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
};

const changeClass = (value) => (value >= 0 ? 'is-up' : 'is-down');
const changeText = (value) => (value === null || value === undefined ? '—' : `${value >= 0 ? '+' : ''}${fmt(value, 2)}%`);

const addIndexOpen = ref(false);
const confirmDeleteOpen = ref(false);
const pendingDeleteIndex = ref(null);

const indexForm = useForm({
    item: '',
    current_price: '',
    percentage_fluctuation: '',
    status: 'active',
});

function openAddIndex() {
    indexForm.reset();
    indexForm.clearErrors();
    addIndexOpen.value = true;
}

function submitIndex() {
    indexForm.post(route('settings.price-indexes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addIndexOpen.value = false;
        },
    });
}

function deleteIndex(index) {
    pendingDeleteIndex.value = index;
    confirmDeleteOpen.value = true;
}

function confirmDeleteIndex() {
    if (!pendingDeleteIndex.value) return;
    router.delete(route('settings.price-indexes.destroy', pendingDeleteIndex.value.id), { preserveScroll: true });
    pendingDeleteIndex.value = null;
}
</script>

<template>
    <DesignPreviewLayout title="Settings">
        <Head title="Settings" />

        <div class="settings-page">
            <div class="settings-page__header">
                <h1 class="settings-page__title">Settings</h1>
                <p class="settings-page__subtitle">System configuration, pricing, and reference data.</p>
            </div>

            <div class="settings-dashboard">
                <section class="settings-column">
                    <h2 class="settings-column__title"><el-icon><Coin /></el-icon> Currency Exchange Rates</h2>
                    <div v-if="exchangeRates.length" class="settings-list">
                        <div v-for="rate in exchangeRates" :key="rate.id" class="settings-list__row">
                            <div class="settings-list__main">
                                <span class="settings-list__label">{{ rate.pair }}</span>
                                <span class="settings-list__sub">Rate {{ fmt(rate.rate, 4) }}</span>
                            </div>
                            <span class="settings-list__change" :class="changeClass(rate.daily_change_percent)">
                                {{ changeText(rate.daily_change_percent) }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="settings-empty">No exchange rates recorded.</p>
                </section>

                <section class="settings-column">
                    <div class="settings-column__head">
                        <h2 class="settings-column__title"><el-icon><PriceTag /></el-icon> Price Indexes</h2>
                        <button type="button" class="settings-add-btn" @click="openAddIndex">
                            <el-icon><Plus /></el-icon> Add
                        </button>
                    </div>
                    <div v-if="priceIndexes.length" class="settings-list">
                        <div v-for="index in priceIndexes" :key="index.id" class="settings-list__row">
                            <div class="settings-list__main">
                                <span class="settings-list__label">{{ index.item }}</span>
                                <span class="settings-list__sub">
                                    {{ fmt(index.current_price, 2) }}
                                    <span v-if="index.status" class="settings-list__status">{{ index.status }}</span>
                                </span>
                            </div>
                            <div class="settings-list__actions">
                                <span class="settings-list__change" :class="changeClass(index.percentage_fluctuation)">
                                    {{ changeText(index.percentage_fluctuation) }}
                                </span>
                                <button type="button" class="settings-delete-btn" title="Delete" @click="deleteIndex(index)">
                                    <el-icon><Delete /></el-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="settings-empty">No price indexes recorded.</p>
                </section>

                <section class="settings-column">
                    <h2 class="settings-column__title"><el-icon><Van /></el-icon> Others</h2>

                    <div class="settings-group">
                        <span class="settings-group__label">Delivery Methods</span>
                        <div v-if="deliveryMethods.length" class="settings-chips">
                            <span v-for="method in deliveryMethods" :key="method" class="settings-chip">{{ method }}</span>
                        </div>
                        <p v-else class="settings-empty">None recorded.</p>
                    </div>

                    <div class="settings-group">
                        <span class="settings-group__label">Incoterms</span>
                        <div v-if="incoterms.length" class="settings-chips">
                            <span v-for="incoterm in incoterms" :key="incoterm" class="settings-chip">{{ incoterm }}</span>
                        </div>
                        <p v-else class="settings-empty">None recorded.</p>
                    </div>
                </section>
            </div>
        </div>

        <el-dialog
            v-model="addIndexOpen"
            width="min(460px, calc(100vw - 2rem))"
            align-center
            :close-on-click-modal="false"
            :show-close="false"
            class="pim-modal"
        >
            <template #header>
                <div class="pim-modal__head">
                    <div class="pim-modal__head-icon">
                        <el-icon :size="18"><PriceTag /></el-icon>
                    </div>
                    <div class="pim-modal__head-text">
                        <div class="pim-modal__eyebrow">Settings</div>
                        <div class="pim-modal__title">Add Price Index</div>
                    </div>
                    <button type="button" class="pim-modal__close" aria-label="Close" @click="addIndexOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="pim-modal__body">
                <div class="pim-grid">
                    <div class="pim-field pim-field--span2">
                        <label class="pim-field__label">Item</label>
                        <el-input v-model="indexForm.item" placeholder="e.g. Uganda Robusta" class="pim-input" :class="{ 'pim-input--error': indexForm.errors.item }" />
                        <span v-if="indexForm.errors.item" class="pim-field__error">{{ indexForm.errors.item }}</span>
                    </div>
                    <div class="pim-field">
                        <label class="pim-field__label">Current Price</label>
                        <el-input-number v-model="indexForm.current_price" :min="0" :precision="2" class="pim-input w-100" :class="{ 'pim-input--error': indexForm.errors.current_price }" />
                        <span v-if="indexForm.errors.current_price" class="pim-field__error">{{ indexForm.errors.current_price }}</span>
                    </div>
                    <div class="pim-field">
                        <label class="pim-field__label">Fluctuation %</label>
                        <el-input-number v-model="indexForm.percentage_fluctuation" :precision="2" class="pim-input w-100" :class="{ 'pim-input--error': indexForm.errors.percentage_fluctuation }" />
                        <span v-if="indexForm.errors.percentage_fluctuation" class="pim-field__error">{{ indexForm.errors.percentage_fluctuation }}</span>
                    </div>
                    <div class="pim-field pim-field--span2">
                        <label class="pim-field__label">Status</label>
                        <el-select v-model="indexForm.status" class="pim-input w-100" :class="{ 'pim-input--error': indexForm.errors.status }">
                            <el-option label="Active" value="active" />
                            <el-option label="Inactive" value="inactive" />
                        </el-select>
                        <span v-if="indexForm.errors.status" class="pim-field__error">{{ indexForm.errors.status }}</span>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="pim-modal__footer">
                    <button type="button" class="pim-btn-outline" :disabled="indexForm.processing" @click="addIndexOpen = false">Cancel</button>
                    <button type="button" class="pim-btn-primary" :disabled="indexForm.processing" @click="submitIndex">
                        {{ indexForm.processing ? 'Saving…' : 'Save' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="confirmDeleteOpen"
            eyebrow="Settings"
            title="Delete Price Index"
            :message="pendingDeleteIndex ? `Delete “${pendingDeleteIndex.item}”? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDeleteIndex"
        />
    </DesignPreviewLayout>
</template>

<style>
.el-dialog.pim-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.pim-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.pim-modal .el-dialog__body { padding: 0; }
.el-dialog.pim-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.settings-page { display: flex; flex-direction: column; gap: 20px; }

.settings-page__title { font-size: 22px; font-weight: 700; color: var(--dp-on-surface); margin: 0; }
.settings-page__subtitle { font-size: 13.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; }

.settings-dashboard { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }

.settings-column {
    background: var(--dp-surface);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 8px;
    padding: 16px;
}
.settings-column__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--dp-outline);
    margin: 0 0 12px;
}
.settings-column__title .el-icon { font-size: 15px; }
.settings-column__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 0 12px;
}
.settings-column__head .settings-column__title { margin: 0; }
.settings-add-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 28px;
    padding: 0 10px;
    border: 1px solid var(--dp-outline-variant);
    border-radius: 6px;
    background: var(--dp-surface);
    color: var(--dp-on-surface-variant);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.settings-add-btn:hover { background: var(--dp-surface-container-low); }

.settings-list { display: flex; flex-direction: column; }
.settings-list__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-top: 1px solid var(--dp-outline-variant);
}
.settings-list__row:first-child { border-top: none; }
.settings-list__main { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.settings-list__label { font-size: 13.5px; font-weight: 600; color: var(--dp-on-surface); }
.settings-list__sub { font-size: 12px; color: var(--dp-on-surface-variant); display: flex; align-items: center; gap: 6px; }
.settings-list__status {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--dp-on-surface-variant);
    background: var(--dp-surface-container-high);
    padding: 1px 6px;
    border-radius: 999px;
}
.settings-list__change { font-size: 12.5px; font-weight: 700; }
.settings-list__change.is-up { color: #16A34A; }
.settings-list__change.is-down { color: var(--dp-error); }
.settings-list__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.settings-delete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: var(--dp-on-surface-variant);
    cursor: pointer;
}
.settings-delete-btn:hover { background: var(--dp-error-container); color: var(--dp-error); }

.settings-group + .settings-group { margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--dp-outline-variant); }
.settings-group__label { font-size: 12px; font-weight: 700; color: var(--dp-on-surface); }
.settings-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.settings-chip {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--dp-on-surface-variant);
    background: var(--dp-surface-container-high);
    padding: 4px 10px;
    border-radius: 999px;
}
.settings-empty { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0; }

.pim-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.pim-modal__head-icon {
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
.pim-modal__head-text { flex: 1; min-width: 0; }
.pim-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.pim-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.pim-modal__close {
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
.pim-modal__close:hover { background: #E5E7EB; color: #121516; }

.pim-modal__body { padding: 22px 24px 8px; }

.pim-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.pim-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.pim-field--span2 { grid-column: span 2; }
.pim-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.pim-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }

.pim-input { width: 100%; }
.pim-input :deep(.el-input__wrapper),
.pim-input :deep(.el-select__wrapper),
.pim-input :deep(.el-textarea__inner) { border-radius: 6px; }
.pim-input--error :deep(.el-input__wrapper),
.pim-input--error :deep(.el-select__wrapper),
.pim-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.pim-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.pim-btn-primary {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 16px;
    background: #000000;
    border: 1px solid transparent;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.pim-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.pim-btn-primary:disabled { opacity: 0.5; cursor: default; }
.pim-btn-outline {
    display: inline-flex; align-items: center; justify-content: center;
    height: 36px; padding: 0 16px;
    background: #fff;
    border: 1px solid #E5E7EB;
    color: #121516;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
}
.pim-btn-outline:hover:not(:disabled) { background: #F5F6F7; }

@media (max-width: 640px) {
    .pim-grid { grid-template-columns: 1fr; }
    .pim-field--span2 { grid-column: span 1; }
}

@media (max-width: 900px) {
    .settings-dashboard { grid-template-columns: 1fr; }
}
</style>
