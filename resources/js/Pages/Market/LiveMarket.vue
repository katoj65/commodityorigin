<script setup>
import { Head } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

defineProps({
    lots: { type: Array, default: () => [] },
    analysis: { type: Object, default: () => ({}) },
    demand: { type: Object, default: () => ({}) },
    opportunities: { type: Array, default: () => [] },
});
</script>

<template>
    <DesignPreviewLayout title="Live Market">
        <Head title="Live Market" />

        <div></div>
    </DesignPreviewLayout>
</template>
