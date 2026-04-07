<script setup>
defineOptions({
    inheritAttrs: false,
});

defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    nativeType: {
        type: String,
        default: 'submit',
    },
    fullWidth: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <el-button
        v-bind="$attrs"
        :native-type="nativeType"
        :loading="loading"
        :disabled="disabled"
        class="submit-button"
        :class="{ 'submit-button--full': fullWidth }"
    >
        <span class="submit-button__content">
            <slot>Submit</slot>
        </span>
    </el-button>
</template>

<style scoped>
.submit-button.el-button {
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #c8862a;
    background: #c8862a;
    color: #0e0b07;
    border-radius: 6px;
    padding: 0.95rem 1rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-weight: 600;
    transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease;
}

.submit-button--full.el-button {
    width: 100%;
}

.submit-button__content {
    width: 100%;
    display: flex;
    justify-content: center;
    text-align: center;
}

.submit-button.el-button .el-icon.is-loading {
    color: #0e0b07;
}

.submit-button.el-button.is-loading,
.submit-button.el-button.is-disabled {
    opacity: 0.6;
}

.submit-button.el-button:hover,
.submit-button.el-button:focus-visible {
    background: #e09b3a;
    border-color: #e09b3a;
    transform: translateY(-1px);
    color: #0e0b07;
}

.submit-button.el-button > span {
    width: 100%;
    display: flex;
    justify-content: center;
    text-align: center;
}
</style>
