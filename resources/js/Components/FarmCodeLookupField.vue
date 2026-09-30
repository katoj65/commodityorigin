<script setup>
/* Resolves a farm by its public farm code (GET farm.find-by-code, not
   scoped to ownership — server-side authorization on whatever the
   resolved farm_id is then used for still happens on that later
   request). Shared by every form that needs "type a farm code, get a
   farm_id" instead of a picker — originally duplicated between
   AddFarmCollectionModal.vue and ImportFarmCollectionsModal.vue; pulled
   out here so there's exactly one copy of the lookup. */
import { ref } from 'vue';

const props = defineProps({
    modelValue: { type: [Number, String], default: '' },
    label: { type: String, default: 'Farm Code' },
    placeholder: { type: String, default: 'e.g. FARM-0042' },
    externalError: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'resolved']);

const code = ref('');
const status = ref('idle'); // idle | loading | found | not-found
const foundName = ref('');

async function lookup() {
    const value = code.value.trim();
    emit('update:modelValue', '');
    foundName.value = '';

    if (!value) {
        status.value = 'idle';
        return;
    }

    status.value = 'loading';
    try {
        const { data } = await axios.get(route('farm.find-by-code'), { params: { farm_code: value } });
        emit('update:modelValue', data.id);
        foundName.value = data.name;
        status.value = 'found';
        emit('resolved', data);
    } catch (error) {
        status.value = 'not-found';
    }
}

function reset() {
    code.value = '';
    status.value = 'idle';
    foundName.value = '';
    emit('update:modelValue', '');
}

defineExpose({ lookup, reset });
</script>

<template>
    <div class="fcl-field">
        <label class="fcl-field__label">{{ label }}</label>
        <el-input
            v-model="code"
            :placeholder="placeholder"
            class="fcl-input"
            :class="{ 'fcl-input--error': status === 'not-found' || externalError }"
            @blur="lookup"
            @keyup.enter="lookup"
        />
        <span v-if="status === 'loading'" class="fcl-field__hint">Looking up farm…</span>
        <span v-else-if="status === 'found'" class="fcl-field__hint fcl-field__hint--ok">✓ {{ foundName }}</span>
        <span v-else-if="status === 'not-found'" class="fcl-field__error">No farm with that code was found.</span>
        <span v-if="externalError" class="fcl-field__error">{{ externalError }}</span>
    </div>
</template>

<style scoped>
.fcl-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.fcl-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.fcl-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.fcl-field__hint { font-size: 12px; font-weight: 500; color: #6F7677; line-height: 1.4; }
.fcl-field__hint--ok { color: #2F6B35; }

.fcl-input { width: 100%; }
.fcl-input :deep(.el-input__wrapper) { border-radius: 6px; }
.fcl-input--error :deep(.el-input__wrapper) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }
</style>
