<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm font-medium text-red-400" style="color:red;">
            {{ message }}
        </p>
    </div>
</template>
