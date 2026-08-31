<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import {
    Check, Clock, Close, EditPen, Plus, Shop, UploadFilled, WarningFilled,
} from '@element-plus/icons-vue';

/* ── This page is now purely the onboarding gate: request/pending/
   rejected states, plus the admin's pending-verification queue. A
   verified store is redirected straight to /store/collections by
   StoreController::show() — the actual inventory (Farm Collections,
   Batches, Lots, Tokenised Lots) now lives on its own four pages under
   StoreInventoryLayout, so nothing verified-state-only stays here. ──── */
const props = defineProps({
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    isAdmin: { type: Boolean, default: false },
    pendingStores: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
});

const storeDialogOpen = ref(false);
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

@media (prefers-reduced-motion: reduce) {
    .st-btn-primary { transition: none; }
}

@media (max-width: 640px) {
    .st-page { gap: 16px; }
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
