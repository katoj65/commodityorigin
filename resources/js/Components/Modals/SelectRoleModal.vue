<script setup>
import { computed } from 'vue';
import { ElMessage } from 'element-plus';
import { useForm } from '@inertiajs/vue3';
import SubmitButton from '@/Components/Button/SubmitButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    currentRole: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const form = useForm({
    role: props.currentRole || '',
});

const selectRole = (roleSlug) => {
    form.role = roleSlug;
    form.clearErrors('role');
};

const submitRole = () => {
    form.post(route('profile.role'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            ElMessage.success('Role selected successfully.');
            dialogVisible.value = false;
        },
        onError: () => {
            dialogVisible.value = true;
        },
    });
};
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(860px, calc(100vw - 2rem))"
        class="select-role-dialog"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
    >
        <template #header>
            <div class="pr-8">
                <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#6B7280]">Select role</div>
                <div class="mt-1 text-[22px] font-bold tracking-tight text-[#111827]">Choose Your Trading Role</div>
                <p class="mt-2 text-[13px] leading-relaxed text-[#6B7280]">
                    Select the role that best matches how you will participate in the exchange.
                </p>
            </div>
        </template>

        <div class="p-5">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <button
                    v-for="role in roles"
                    :key="role.slug"
                    type="button"
                    class="role-card text-left"
                    :class="{ 'role-card--active': form.role === role.slug }"
                    @click="selectRole(role.slug)"
                >
                    <div class="role-card__header">
                        <div class="role-card__title">{{ role.name }}</div>
                        <div class="role-card__check">
                            <svg v-if="form.role === role.slug" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12l5 5L20 7" />
                            </svg>
                        </div>
                    </div>
                    <p class="role-card__body">
                        {{ role.description || 'Role available for marketplace participation.' }}
                    </p>
                    <div class="role-card__slug">{{ role.slug }}</div>
                </button>
            </div>

            <InputError :message="form.errors.role" class="role-input-error mt-4" />

        </div>

        <template #footer>


            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <SubmitButton
                    native-type="button"
                    class="sm:!w-auto sm:min-w-[240px]"
                    :loading="form.processing"
                    :disabled="form.processing || !form.role"
                    @click="submitRole"
                >
                    Save Role
                </SubmitButton>
            </div>

        </template>
    </el-dialog>
</template>

<style scoped>
:deep(.select-role-dialog),
:deep(.select-role-dialog .el-dialog) {
    border-radius: 20px;
    overflow: hidden;
}

:deep(.select-role-dialog .el-dialog__header) {
    margin-right: 0;
    padding: 20px 24px 8px;
}

:deep(.select-role-dialog .el-dialog__body) {
    padding: 12px 24px 8px;
}

:deep(.select-role-dialog .el-dialog__footer) {
    padding: 8px 24px 24px;
}

.role-card {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    background: #ffffff;
    padding: 1rem;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}

.role-card:hover {
    border-color: rgba(200, 134, 42, 0.42);
    box-shadow: 0 14px 30px rgba(17, 24, 39, 0.06);
    transform: translateY(-1px);
}

.role-card--active {
    border-color: #c8862a;
    box-shadow: 0 0 0 1px rgba(200, 134, 42, 0.18) inset;
    background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
}

.role-card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.role-card__title {
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: #111827;
}

.role-card__check {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 999px;
    border: 1px solid #d1d5db;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #c8862a;
    flex-shrink: 0;
}

.role-card--active .role-card__check {
    border-color: rgba(200, 134, 42, 0.55);
    background: rgba(200, 134, 42, 0.1);
}

.role-card__check svg {
    width: 0.875rem;
    height: 0.875rem;
}

.role-card__body {
    margin-top: 0.7rem;
    font-size: 0.82rem;
    line-height: 1.6;
    color: #6b7280;
}

.role-card__slug {
    margin-top: 0.9rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #9ca3af;
}

.role-input-error :deep(p) {
    font-size: 0.875rem !important;
    line-height: 1.4;
    font-weight: 600;
}
</style>
