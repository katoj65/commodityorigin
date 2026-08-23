<script setup>
import { Head } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import RequestStoreDialog from '@/Components/Modals/RequestStoreDialog.vue';

const props = defineProps({
    title: { type: String, default: 'Store' },
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
});

/**
 * These three pieces of state are consumed by whichever page is rendered
 * inside the layout (e.g. the items table's filter, or the import-results
 * panel's dismiss button). A page that cares binds them with v-model:*; a
 * page that doesn't just leaves the layout to manage its own default.
 */
const statusFilter = defineModel('statusFilter', { default: 'all' });
const storeDialogOpen = defineModel('storeDialogOpen', { default: false });
const importResultVisible = defineModel('importResultVisible', { default: false });
if (props.importResult) importResultVisible.value = true;
</script>

<template>
    <DesignPreviewLayout :title="title">
        <Head :title="title" />

        <div class="stl-page">
            <slot />
        </div>

        <RequestStoreDialog v-model="storeDialogOpen" :rejected="store?.verification_status === 'rejected'" />
    </DesignPreviewLayout>
</template>

<style scoped>
.stl-page {
    font-family: var(--dp-font-sans);
    min-height: 100%;
}
</style>
