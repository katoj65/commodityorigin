<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    farmer: { type: Object, required: true },
    cooperatives: { type: Array, default: () => [] },
    canCreateFarm: { type: Boolean, default: false },
});
</script>

<template>
    <MainLayout title="Farmer Profile">
        <Head title="Farmer Profile" />
        <div></div>
    </MainLayout>
</template>
