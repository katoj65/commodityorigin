<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm font-medium text-red-400" style="color:red; margin-top:8px; margin-bottom:0;">
            {{ message }}
        </p>
    </div>
</template>
