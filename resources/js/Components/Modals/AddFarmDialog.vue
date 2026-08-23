<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, MapLocation, Plus } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    farmerId: { type: [Number, String], required: true },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
        name: '',
        coffee_type: '',
        country: 'Uganda',
        region: '',
        district: '',
        county: '',
        subcounty: '',
        parish: '',
        village: '',
        latitude: '',
        longitude: '',
        elevation: '',
        total_area: '',
        coffee_area: '',
    };
}

const form = useForm(emptyForm());

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form
        .transform((data) => ({ ...data, farmer_id: props.farmerId }))
        .post(route('farm.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeDialog();
                ElNotification({
                    title: 'Farm Added',
                    message: 'Farm registered successfully.',
                    type: 'success',
                    duration: 3200,
                    offset: 84,
                });
            },
        });
}
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(720px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="afd-modal"
    >
        <template #header>
            <div class="afd-modal__head">
                <div class="afd-modal__head-icon">
                    <el-icon :size="18"><MapLocation /></el-icon>
                </div>
                <div class="afd-modal__head-text">
                    <div class="afd-modal__eyebrow">Farm Workspace</div>
                    <div class="afd-modal__title">Add Farm</div>
                </div>
                <button type="button" class="afd-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="afd-modal__body">
            <div class="afd-grid">
                <div class="afd-field afd-field--span2">
                    <label class="afd-field__label">Farm Name <span class="afd-req">*</span></label>
                    <el-input v-model="form.name" placeholder="e.g. Bukit Biru Estate" class="afd-input" :class="{ 'afd-input--error': form.errors.name }" />
                    <span v-if="form.errors.name" class="afd-field__error">{{ form.errors.name }}</span>
                </div>

                <div class="afd-field">
                    <label class="afd-field__label">Coffee Type</label>
                    <el-input v-model="form.coffee_type" placeholder="e.g. Arabica" class="afd-input" :class="{ 'afd-input--error': form.errors.coffee_type }" />
                    <span v-if="form.errors.coffee_type" class="afd-field__error">{{ form.errors.coffee_type }}</span>
                </div>

                <div class="afd-field">
                    <label class="afd-field__label">Country</label>
                    <el-input v-model="form.country" placeholder="e.g. Uganda" class="afd-input" :class="{ 'afd-input--error': form.errors.country }" />
                    <span v-if="form.errors.country" class="afd-field__error">{{ form.errors.country }}</span>
                </div>

                <div class="afd-field">
                    <label class="afd-field__label">Region</label>
                    <el-input v-model="form.region" placeholder="e.g. Eastern" class="afd-input" :class="{ 'afd-input--error': form.errors.region }" />
                    <span v-if="form.errors.region" class="afd-field__error">{{ form.errors.region }}</span>
                </div>

                <div class="afd-field">
                    <label class="afd-field__label">District</label>
                    <el-input v-model="form.district" placeholder="e.g. Mbale" class="afd-input" :class="{ 'afd-input--error': form.errors.district }" />
                    <span v-if="form.errors.district" class="afd-field__error">{{ form.errors.district }}</span>
                </div>

                <div class="afd-field">
                    <label class="afd-field__label">County</label>
                    <el-input v-model="form.county" placeholder="e.g. Bungokho" class="afd-input" :class="{ 'afd-input--error': form.errors.county }" />
                    <span v-if="form.errors.county" class="afd-field__error">{{ form.errors.county }}</span>
                </div>

                <div class="afd-field">
                    <label class="afd-field__label">Sub-county</label>
                    <el-input v-model="form.subcounty" placeholder="e.g. Bungokho" class="afd-input" :class="{ 'afd-input--error': form.errors.subcounty }" />
                    <span v-if="form.errors.subcounty" class="afd-field__error">{{ form.errors.subcounty }}</span>
                </div>

                <div class="afd-field afd-field--span2">
                    <label class="afd-field__label">Parish</label>
                    <el-input v-model="form.parish" placeholder="e.g. Bumwoni" class="afd-input" :class="{ 'afd-input--error': form.errors.parish }" />
                    <span v-if="form.errors.parish" class="afd-field__error">{{ form.errors.parish }}</span>
                </div>

                <div class="afd-field afd-field--span2">
                    <label class="afd-field__label">Village</label>
                    <el-input v-model="form.village" placeholder="e.g. Busamaga" class="afd-input" :class="{ 'afd-input--error': form.errors.village }" />
                    <span v-if="form.errors.village" class="afd-field__error">{{ form.errors.village }}</span>
                </div>
            </div>

            <div class="afd-section">
                <div class="afd-section__title">Coordinates &amp; Size</div>
                <p class="afd-section__hint">Optional, but improves traceability and mapping accuracy.</p>

                <div class="afd-grid">
                    <div class="afd-field">
                        <label class="afd-field__label">Latitude</label>
                        <el-input v-model="form.latitude" type="number" step="0.0000001" placeholder="e.g. 1.0827" class="afd-input" :class="{ 'afd-input--error': form.errors.latitude }" />
                        <span v-if="form.errors.latitude" class="afd-field__error">{{ form.errors.latitude }}</span>
                    </div>

                    <div class="afd-field">
                        <label class="afd-field__label">Longitude</label>
                        <el-input v-model="form.longitude" type="number" step="0.0000001" placeholder="e.g. 34.1751" class="afd-input" :class="{ 'afd-input--error': form.errors.longitude }" />
                        <span v-if="form.errors.longitude" class="afd-field__error">{{ form.errors.longitude }}</span>
                    </div>

                    <div class="afd-field">
                        <label class="afd-field__label">Elevation (m)</label>
                        <el-input v-model="form.elevation" type="number" step="0.01" placeholder="e.g. 1650" class="afd-input" :class="{ 'afd-input--error': form.errors.elevation }" />
                        <span v-if="form.errors.elevation" class="afd-field__error">{{ form.errors.elevation }}</span>
                    </div>

                    <div class="afd-field">
                        <label class="afd-field__label">Total Area (ha)</label>
                        <el-input v-model="form.total_area" type="number" min="0" step="0.01" placeholder="e.g. 5.25" class="afd-input" :class="{ 'afd-input--error': form.errors.total_area }" />
                        <span v-if="form.errors.total_area" class="afd-field__error">{{ form.errors.total_area }}</span>
                    </div>

                    <div class="afd-field afd-field--span2">
                        <label class="afd-field__label">Coffee Area (ha)</label>
                        <el-input v-model="form.coffee_area" type="number" min="0" step="0.01" placeholder="e.g. 3.75" class="afd-input" :class="{ 'afd-input--error': form.errors.coffee_area }" />
                        <span v-if="form.errors.coffee_area" class="afd-field__error">{{ form.errors.coffee_area }}</span>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="afd-modal__footer">
                <button type="button" class="afd-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="afd-btn-primary" :disabled="form.processing" @click="submit">
                    <el-icon v-if="!form.processing"><Plus /></el-icon>
                    {{ form.processing ? 'Saving…' : 'Save Farm' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so it never carries this SFC's
   scope attribute — a scoped (or :deep()) selector can never reach it.
   Class names are specific enough to avoid collisions. */
.el-dialog.afd-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

.el-dialog.afd-modal .el-dialog__header {
    padding: 0;
    margin: 0;
}

.el-dialog.afd-modal .el-dialog__body {
    padding: 0;
}

.el-dialog.afd-modal .el-dialog__footer {
    padding: 0;
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.afd-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.afd-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(39, 19, 16, 0.08);
    color: #271310;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.afd-modal__head-text { flex: 1; min-width: 0; }

.afd-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #271310;
    margin-bottom: 1px;
}

.afd-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.afd-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}

.afd-modal__close:hover { background: #e5e7eb; color: #111827; }

.afd-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
}

.afd-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.afd-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.afd-field--span2 { grid-column: span 2; }

.afd-field__label {
    font-size: 0.875rem;
    font-weight: 700;
    color: #111827;
}

.afd-req { color: #dc2626; }

.afd-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.afd-input { width: 100%; }

.afd-input--error :deep(.el-input__wrapper),
.afd-input--error :deep(.el-select__wrapper) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.afd-section {
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px solid #f3f4f6;
}

.afd-section__title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 4px;
}

.afd-section__hint {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1.5;
    margin: 0 0 12px;
}

.afd-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.afd-btn-primary {
    background: linear-gradient(135deg, #271310, #3e2723);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.afd-btn-primary:hover { opacity: 0.9; }
.afd-btn-primary:disabled { opacity: 0.6; cursor: default; }

.afd-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.afd-btn-outline:hover { background: #f8fafc; }

@media (max-width: 640px) {
    .afd-grid { grid-template-columns: 1fr; }
    .afd-field--span2 { grid-column: span 1; }
}
</style>
