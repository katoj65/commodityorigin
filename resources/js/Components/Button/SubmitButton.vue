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
    gap: 10px;
    border: 1px solid #000000;
    background: #000000;
    color: #ffffff;
    border-radius: 6px;
    padding: 0.95rem 10px;
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
    color: #ffffff;
}

.submit-button.el-button.is-loading,
.submit-button.el-button.is-disabled {
    opacity: 0.6;
}

.submit-button.el-button:hover,
.submit-button.el-button:focus-visible {
    background: #1c1c1c;
    border-color: #1c1c1c;
    transform: translateY(-1px);
    color: #ffffff;
}

.submit-button.el-button > span {
    width: 100%;
    display: flex;
    justify-content: center;
    text-align: center;
}
</style>
