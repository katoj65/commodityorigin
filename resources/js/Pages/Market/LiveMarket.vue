<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    lots: { type: Array, default: () => [] },
    analysis: { type: Object, default: () => ({}) },
    demand: { type: Object, default: () => ({}) },
    opportunities: { type: Array, default: () => [] },
});
</script>

<template>
    <MainLayout title="Live Market">
        <Head title="Live Market" />

        <div></div>
    </MainLayout>
</template>
