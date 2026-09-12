<script setup>
/* Trade hub — structural/visual port of the uploaded "Institutional
   Commodity Exchange" Trade mockup (code.html / DESIGN.md), restyled
   with this app's own --dp-* design tokens instead of the mockup's own
   literal emerald/slate palette + Manrope font (see DesignPreviewLayout
   .vue's token-block comment for why literal hex/tailwind.config.js
   extension is avoided app-wide). The real dark sidebar/header shell is
   unchanged — only this page's content area is rebuilt.

   Shared shell for the whole Trade hub (Market/RFQs/Offers/Auctions/My
   Trades) — title/badge/subtitle and the tab counts are real, passed in
   by whichever controller renders the page. Offers/My Trades tab counts
   are still static since no controller wires those yet. */
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Close, Tickets } from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

const props = defineProps({
    title: { type: String, default: 'Trade' },
    badge: { type: String, default: 'B2B Commodity Exchange' },
    subtitle: { type: String, default: 'Discover, evaluate and trade verified coffee from trusted origins with instant cryptographic provenance.' },
    marketCount: { type: Number, default: 0 },
    auctionCount: { type: Number, default: 0 },
    requestCount: { type: Number, default: 0 },
    cropTypeOptions: { type: Array, default: () => [] },
    gradeOptions: { type: Array, default: () => [] },
});

// Offers/My Trades have no real count wired through yet, so those two tabs
// stay static — Market/RFQs/Auctions already receive real data via props.
const tabs = computed(() => [
    { key: 'market', label: 'Market', icon: 'storefront', count: props.marketCount, route: 'trade.index' },
    { key: 'rfqs', label: 'RFQs', icon: 'description', count: props.requestCount, route: 'rfq.index' },
    { key: 'offers', label: 'Offers', icon: 'forum', count: 8, route: 'trade.offer' },
    { key: 'auctions', label: 'Auctions', icon: 'gavel', count: props.auctionCount, route: 'auction.index' },
    { key: 'my-trades', label: 'My Trades', icon: 'receipt_long', count: 16, route: 'orders.index' },
]);

/* ── Create RFQ — reachable from the header CTA on every Trade-section
   page, not just the RFQs page itself. Submitting posts back to
   rfq.store, which redirects back to whichever page opened it, so the
   tab bar's RFQ count refreshes wherever you are. ─────────────────────── */
const createRfqOpen = ref(false);
const rfqForm = useForm({
    crop_type: '',
    variety: '',
    grade: '',
    amount: '',
    quantity: '',
    notes: '',
});

function openCreateRfq() {
    rfqForm.reset();
    rfqForm.clearErrors();
    createRfqOpen.value = true;
}

function submitRfq() {
    rfqForm.post(route('rfq.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createRfqOpen.value = false;
        },
    });
}
</script>

<template>
    <DesignPreviewLayout :title="title">
        <Head :title="title">
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
                rel="stylesheet"
            />
        </Head>

        <div class="trade-page">
            <!-- ── Page header ──────────────────────────────────────────── -->
            <div class="trade-head">
                <div>
                    <div class="trade-head__title-row">
                        <h1 class="trade-head__title">{{ title }}</h1>
                        <span class="trade-head__badge">{{ badge }}</span>
                    </div>
                    <p class="trade-head__subtitle">{{ subtitle }}</p>
                </div>
                <button type="button" class="trade-head__cta" @click="openCreateRfq">
                    <span class="material-symbols-outlined">add_circle</span>
                    <span>Create RFQ</span>
                </button>
            </div>

            <!-- ── Trading mechanism tabs ───────────────────────────────── -->
            <div class="trade-tabs">
                <div class="trade-tabs__list">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.key"
                        :href="route(tab.route)"
                        class="trade-tab"
                        :class="{ 'trade-tab--active': route().current(tab.route) }"
                    >
                        <span class="material-symbols-outlined">{{ tab.icon }}</span>
                        <span>{{ tab.label }}</span>
                        <span class="trade-tab__count">{{ tab.count }}</span>
                    </Link>
                </div>
                <div class="trade-tabs__trust">
                    <span><span class="material-symbols-outlined">check_circle</span> 100% Origin Audited</span>
                </div>
            </div>

            <slot></slot>
        </div>

        <el-dialog
            v-model="createRfqOpen"
            width="min(520px, calc(100vw - 2rem))"
            align-center
            :close-on-click-modal="false"
            :show-close="false"
            class="rfq-modal"
        >
            <template #header>
                <div class="rfq-modal__head">
                    <div class="rfq-modal__head-icon">
                        <el-icon :size="18"><Tickets /></el-icon>
                    </div>
                    <div class="rfq-modal__head-text">
                        <div class="rfq-modal__eyebrow">Trade</div>
                        <div class="rfq-modal__title">New Request for Quote</div>
                    </div>
                    <button type="button" class="rfq-modal__close" aria-label="Close" @click="createRfqOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="rfq-modal__body">
                <div class="rfq-grid">
                    <div class="rfq-field rfq-field--span2">
                        <label class="rfq-field__label">Crop Type</label>
                        <el-select v-model="rfqForm.crop_type" placeholder="Select crop type" filterable class="rfq-input" :class="{ 'rfq-input--error': rfqForm.errors.crop_type }">
                            <el-option v-for="crop in cropTypeOptions" :key="crop" :label="crop" :value="crop" />
                        </el-select>
                        <span v-if="rfqForm.errors.crop_type" class="rfq-field__error">{{ rfqForm.errors.crop_type }}</span>
                    </div>
                    <div class="rfq-field">
                        <label class="rfq-field__label">Variety <small>(optional)</small></label>
                        <el-input v-model="rfqForm.variety" placeholder="e.g. SL14" class="rfq-input" :class="{ 'rfq-input--error': rfqForm.errors.variety }" />
                        <span v-if="rfqForm.errors.variety" class="rfq-field__error">{{ rfqForm.errors.variety }}</span>
                    </div>
                    <div class="rfq-field">
                        <label class="rfq-field__label">Grade</label>
                        <el-select v-model="rfqForm.grade" placeholder="Select grade" filterable class="rfq-input" :class="{ 'rfq-input--error': rfqForm.errors.grade }">
                            <el-option v-for="grade in gradeOptions" :key="grade" :label="grade" :value="grade" />
                        </el-select>
                        <span v-if="rfqForm.errors.grade" class="rfq-field__error">{{ rfqForm.errors.grade }}</span>
                    </div>
                    <div class="rfq-field">
                        <label class="rfq-field__label">Quantity</label>
                        <el-input-number v-model="rfqForm.quantity" :min="0.01" :precision="2" class="rfq-input w-100" :class="{ 'rfq-input--error': rfqForm.errors.quantity }" />
                        <span v-if="rfqForm.errors.quantity" class="rfq-field__error">{{ rfqForm.errors.quantity }}</span>
                    </div>
                    <div class="rfq-field">
                        <label class="rfq-field__label">Amount <small>(optional)</small></label>
                        <el-input-number v-model="rfqForm.amount" :min="0" :precision="2" class="rfq-input w-100" :class="{ 'rfq-input--error': rfqForm.errors.amount }" />
                        <span v-if="rfqForm.errors.amount" class="rfq-field__error">{{ rfqForm.errors.amount }}</span>
                    </div>
                    <div class="rfq-field rfq-field--span2">
                        <label class="rfq-field__label">Notes <small>(optional)</small></label>
                        <el-input v-model="rfqForm.notes" type="textarea" :rows="3" class="rfq-input" :class="{ 'rfq-input--error': rfqForm.errors.notes }" />
                        <span v-if="rfqForm.errors.notes" class="rfq-field__error">{{ rfqForm.errors.notes }}</span>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="rfq-modal__footer">
                    <button type="button" class="rfq-btn-outline" :disabled="rfqForm.processing" @click="createRfqOpen = false">Cancel</button>
                    <button type="button" class="rfq-btn-primary" :disabled="rfqForm.processing" @click="submitRfq">
                        {{ rfqForm.processing ? 'Submitting…' : 'Submit' }}
                    </button>
                </div>
            </template>
        </el-dialog>
    </DesignPreviewLayout>
</template>

<style>
.el-dialog.rfq-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
.el-dialog.rfq-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.rfq-modal .el-dialog__body { padding: 0; }
.el-dialog.rfq-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.trade-page { display: flex; flex-direction: column; gap: 24px; }
.trade-page :deep(.material-symbols-outlined) { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

/* ── Page header ──────────────────────────────────────────────────────── */
.trade-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; }
.trade-head__title-row { display: flex; align-items: center; gap: 12px; }
.trade-head__title { font-size: 22px; font-weight: 700; color: var(--dp-on-surface); margin: 0; }
.trade-head__badge { display: inline-flex; align-items: center; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.trade-head__subtitle { font-size: 13.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; max-width: 640px; }

.trade-head__cta {
    display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 16px; border-radius: 8px; border: none; cursor: pointer;
    background: var(--dp-primary); color: var(--dp-on-primary); font-family: inherit; font-size: 13px; font-weight: 700;
    white-space: nowrap; transition: opacity .15s ease;
}
.trade-head__cta:hover { opacity: .88; }
.trade-head__cta .material-symbols-outlined { font-size: 18px; }

/* ── Trading mechanism tabs ───────────────────────────────────────────── */
.trade-tabs { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--dp-outline-variant); padding-bottom: 10px; overflow-x: auto; }
.trade-tabs__list { display: flex; align-items: center; gap: 8px; }
.trade-tab {
    display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 8px; border: none; background: transparent;
    font-family: inherit; font-size: 13px; font-weight: 700; color: var(--dp-on-surface-variant); cursor: pointer; white-space: nowrap;
    text-decoration: none; transition: background .15s ease, color .15s ease;
}
.trade-tab .material-symbols-outlined { font-size: 18px; }
.trade-tab:hover:not(.trade-tab--active) { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.trade-tab--active { background: var(--dp-primary); color: var(--dp-on-primary); cursor: default; }
.trade-tab__count { font-size: 11px; padding: 1px 7px; border-radius: 999px; background: rgba(255,255,255,.2); }
.trade-tab:not(.trade-tab--active) .trade-tab__count { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.trade-tabs__trust { display: flex; align-items: center; gap: 16px; font-size: 12px; color: var(--dp-on-surface-variant); font-weight: 600; flex-shrink: 0; }
.trade-tabs__trust span { display: inline-flex; align-items: center; gap: 5px; }
.trade-tabs__trust .material-symbols-outlined { font-size: 15px; color: var(--dp-primary); }

@media (max-width: 1100px) {
    .trade-tabs__trust { display: none; }
}

/* ── Create RFQ modal ───────────────────────────────────────────────────── */
.rfq-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.rfq-modal__head-icon {
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
.rfq-modal__head-text { flex: 1; min-width: 0; }
.rfq-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.rfq-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.rfq-modal__close {
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
}
.rfq-modal__close:hover { background: #E5E7EB; color: #121516; }

.rfq-modal__body { padding: 22px 24px 8px; }
.rfq-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.rfq-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.rfq-field--span2 { grid-column: span 2; }
.rfq-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.rfq-field__label small { font-weight: 500; color: #6F7677; }
.rfq-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.rfq-input { width: 100%; }
.rfq-input :deep(.el-input__wrapper),
.rfq-input :deep(.el-select__wrapper),
.rfq-input :deep(.el-textarea__inner) { border-radius: 6px; }
.rfq-input--error :deep(.el-input__wrapper),
.rfq-input--error :deep(.el-select__wrapper),
.rfq-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }

.rfq-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.rfq-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    background: #000000;
    border: 1px solid transparent;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.rfq-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.rfq-btn-primary:disabled { opacity: 0.5; cursor: default; }
.rfq-btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    background: #fff;
    border: 1px solid #E5E7EB;
    color: #121516;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.rfq-btn-outline:hover:not(:disabled) { background: #F5F6F7; }

@media (max-width: 640px) {
    .rfq-grid { grid-template-columns: 1fr; }
    .rfq-field--span2 { grid-column: span 1; }
}
</style>
