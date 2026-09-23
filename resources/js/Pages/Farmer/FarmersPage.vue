<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    farmers: { type: Array, default: () => [] },
});
</script>

<template>
    <MainLayout title="Farmers">
        <Head title="Farmers" />
        <div></div>
    </MainLayout>
</template>
