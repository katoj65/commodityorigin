<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { CircleClose, Promotion } from '@element-plus/icons-vue';
import PublishLotModal from '@/Components/Modals/PublishLotModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    lot: { type: Object, required: true },
    currencyOptions: { type: Array, default: () => [] },
    currencyCountries: { type: Object, default: () => ({}) },
    deliveryMethodOptions: { type: Array, default: () => [] },
    incotermOptions: { type: Array, default: () => [] },
    paymentOptions: { type: Array, default: () => [] },
    deliveryTermsOptions: { type: Array, default: () => [] },
});

const modalOpen = ref(false);
const unpublishDialogOpen = ref(false);
const unpublishLoading = ref(false);
const isPublished = computed(() => Boolean(props.lot.is_published));

function confirmUnpublish() {
    unpublishLoading.value = true;
    router.delete(route('lot.unpublish', props.lot.id), {
        preserveScroll: true,
        onSuccess: () => {
            ElNotification({
                title: 'Unpublished',
                message: `Lot ${props.lot.lot_number || `#${props.lot.id}`} was removed from the market.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
            // Same reasoning as PublishLotModal's submit() — force a hard
            // reload so the page's derived display state (Edit/Delete
            // gating, the button itself, etc.) picks up the change.
            window.location.reload();
        },
        onError: () => {
            ElNotification({
                title: 'Unpublish Failed',
                message: 'This lot could not be removed from the market.',
                type: 'error',
                duration: 3200,
                offset: 84,
            });
        },
        onFinish: () => {
            unpublishLoading.value = false;
            unpublishDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <button
        v-if="!isPublished"
        type="button"
        class="ptm-btn ptm-btn--primary"
        @click="modalOpen = true"
    >
        <el-icon><Promotion /></el-icon> Publish to Market
    </button>
    <button
        v-else
        type="button"
        class="ptm-btn ptm-btn--unpublish"
        @click="unpublishDialogOpen = true"
    >
        <el-icon><CircleClose /></el-icon> Unpublish
    </button>

    <PublishLotModal
        v-model="modalOpen"
        :lot="lot"
        :currency-options="currencyOptions"
        :currency-countries="currencyCountries"
        :delivery-method-options="deliveryMethodOptions"
        :incoterm-options="incotermOptions"
        :payment-options="paymentOptions"
        :delivery-terms-options="deliveryTermsOptions"
    />

    <ConfirmDialog
        v-model="unpublishDialogOpen"
        eyebrow="Lot Profile"
        title="Remove this listing from the market?"
        :message="`Lot ${lot.lot_number || `#${lot.id}`} will be taken off the market immediately — buyers won't be able to see or purchase it. You can publish it again later.`"
        confirm-text="Unpublish"
        icon="warning"
        :danger="false"
        :loading="unpublishLoading"
        :auto-close="false"
        @confirm="confirmUnpublish"
    />
</template>

<style scoped>
.ptm-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 14px;
    border-radius: var(--dp-card-radius, 6px);
    font-size: .75rem;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid transparent;
    font-family: inherit;
    white-space: nowrap;
    transition: opacity .15s ease, background .15s ease;
}
.ptm-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ptm-btn--primary:hover { opacity: .9; }
.ptm-btn--unpublish {
    background: var(--dp-surface-container-lowest);
    border-color: var(--card-border);
    color: var(--dp-error);
}
.ptm-btn--unpublish:hover {
    background: var(--dp-error-container);
    border-color: var(--dp-error);
}
</style>
