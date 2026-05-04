<script setup>
import { watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Promotion } from '@element-plus/icons-vue';

const props = defineProps({
    lot: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const form = useForm({});

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            ElNotification({
                title: 'Published',
                message: flash.success,
                type: 'success',
                duration: 4000,
                position: 'top-right',
            });
        }
        if (flash?.error) {
            ElNotification({
                title: 'Already Published',
                message: flash.error,
                type: 'error',
                duration: 4000,
                position: 'top-right',
            });
        }
    },
    { deep: true },
);

const publish = () => {
    form.post(route('lot.publish', props.lot.id));
};
</script>

<template>
    <button
        type="button"
        class="pb-btn"
        :disabled="form.processing"
        @click="publish"
    >
        <el-icon class="pb-btn__icon" :class="{ 'is-loading': form.processing }">
            <Promotion />
        </el-icon>
        {{ form.processing ? 'Publishing…' : 'Publish to Market' }}
    </button>
</template>

<style scoped>
.pb-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0 18px;
    height: 38px;
    border-radius: 6px;
    border: none;
    background: linear-gradient(135deg, #004532, #065f46);
    color: #ffffff;
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: opacity 0.15s ease;
}
.pb-btn:hover:not(:disabled) { opacity: 0.88; }
.pb-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pb-btn__icon { font-size: 14px; }
.pb-btn__icon.is-loading { animation: spin 0.8s linear infinite; }

@keyframes spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
</style>
