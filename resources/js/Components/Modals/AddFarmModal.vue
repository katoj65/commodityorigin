<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, House, Plus } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
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
        tel: '',
        email: '',
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
    form.post(route('farm.store'), {
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
        class="afm-modal"
    >
        <template #header>
            <div class="afm-modal__head">
                <div class="afm-modal__head-icon">
                    <el-icon :size="18"><House /></el-icon>
                </div>
                <div class="afm-modal__head-text">
                    <div class="afm-modal__eyebrow">Farm Workspace</div>
                    <div class="afm-modal__title">Add Farm</div>
                </div>
                <button type="button" class="afm-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="afm-modal__body">
            <div class="afm-grid">
                <div class="afm-field afm-field--span2">
                    <label class="afm-field__label">Farm Name <span class="afm-req">*</span></label>
                    <el-input v-model="form.name" placeholder="e.g. Bukit Biru Estate" class="afm-input" :class="{ 'afm-input--error': form.errors.name }" />
                    <span v-if="form.errors.name" class="afm-field__error">{{ form.errors.name }}</span>
                </div>

                <div class="afm-field">
                    <label class="afm-field__label">Coffee Type</label>
                    <el-input v-model="form.coffee_type" placeholder="e.g. Arabica" class="afm-input" :class="{ 'afm-input--error': form.errors.coffee_type }" />
                    <span v-if="form.errors.coffee_type" class="afm-field__error">{{ form.errors.coffee_type }}</span>
                </div>

                <div class="afm-field">
                    <label class="afm-field__label">Phone</label>
                    <el-input v-model="form.tel" placeholder="e.g. +256 700 000000" class="afm-input" :class="{ 'afm-input--error': form.errors.tel }" />
                    <span v-if="form.errors.tel" class="afm-field__error">{{ form.errors.tel }}</span>
                </div>

                <div class="afm-field">
                    <label class="afm-field__label">Email</label>
                    <el-input v-model="form.email" placeholder="e.g. farm@example.com" class="afm-input" :class="{ 'afm-input--error': form.errors.email }" />
                    <span v-if="form.errors.email" class="afm-field__error">{{ form.errors.email }}</span>
                </div>

                <div class="afm-field">
                    <label class="afm-field__label">Country</label>
                    <el-input v-model="form.country" placeholder="e.g. Uganda" class="afm-input" :class="{ 'afm-input--error': form.errors.country }" />
                    <span v-if="form.errors.country" class="afm-field__error">{{ form.errors.country }}</span>
                </div>

                <div class="afm-field">
                    <label class="afm-field__label">Region</label>
                    <el-input v-model="form.region" placeholder="e.g. Eastern" class="afm-input" :class="{ 'afm-input--error': form.errors.region }" />
                    <span v-if="form.errors.region" class="afm-field__error">{{ form.errors.region }}</span>
                </div>

                <div class="afm-field">
                    <label class="afm-field__label">District</label>
                    <el-input v-model="form.district" placeholder="e.g. Mbale" class="afm-input" :class="{ 'afm-input--error': form.errors.district }" />
                    <span v-if="form.errors.district" class="afm-field__error">{{ form.errors.district }}</span>
                </div>

                <div class="afm-field">
                    <label class="afm-field__label">County</label>
                    <el-input v-model="form.county" placeholder="e.g. Bungokho" class="afm-input" :class="{ 'afm-input--error': form.errors.county }" />
                    <span v-if="form.errors.county" class="afm-field__error">{{ form.errors.county }}</span>
                </div>

                <div class="afm-field">
                    <label class="afm-field__label">Sub-county</label>
                    <el-input v-model="form.subcounty" placeholder="e.g. Bungokho" class="afm-input" :class="{ 'afm-input--error': form.errors.subcounty }" />
                    <span v-if="form.errors.subcounty" class="afm-field__error">{{ form.errors.subcounty }}</span>
                </div>

                <div class="afm-field afm-field--span2">
                    <label class="afm-field__label">Parish</label>
                    <el-input v-model="form.parish" placeholder="e.g. Bumwoni" class="afm-input" :class="{ 'afm-input--error': form.errors.parish }" />
                    <span v-if="form.errors.parish" class="afm-field__error">{{ form.errors.parish }}</span>
                </div>

                <div class="afm-field afm-field--span2">
                    <label class="afm-field__label">Village</label>
                    <el-input v-model="form.village" placeholder="e.g. Busamaga" class="afm-input" :class="{ 'afm-input--error': form.errors.village }" />
                    <span v-if="form.errors.village" class="afm-field__error">{{ form.errors.village }}</span>
                </div>
            </div>

            <div class="afm-section">
                <div class="afm-section__title">Coordinates &amp; Size</div>
                <p class="afm-section__hint">Optional, but improves traceability and mapping accuracy.</p>

                <div class="afm-grid">
                    <div class="afm-field">
                        <label class="afm-field__label">Latitude</label>
                        <el-input v-model="form.latitude" type="number" step="0.0000001" placeholder="e.g. 1.0827" class="afm-input" :class="{ 'afm-input--error': form.errors.latitude }" />
                        <span v-if="form.errors.latitude" class="afm-field__error">{{ form.errors.latitude }}</span>
                    </div>

                    <div class="afm-field">
                        <label class="afm-field__label">Longitude</label>
                        <el-input v-model="form.longitude" type="number" step="0.0000001" placeholder="e.g. 34.1751" class="afm-input" :class="{ 'afm-input--error': form.errors.longitude }" />
                        <span v-if="form.errors.longitude" class="afm-field__error">{{ form.errors.longitude }}</span>
                    </div>

                    <div class="afm-field">
                        <label class="afm-field__label">Elevation (m)</label>
                        <el-input v-model="form.elevation" type="number" step="0.01" placeholder="e.g. 1650" class="afm-input" :class="{ 'afm-input--error': form.errors.elevation }" />
                        <span v-if="form.errors.elevation" class="afm-field__error">{{ form.errors.elevation }}</span>
                    </div>

                    <div class="afm-field">
                        <label class="afm-field__label">Total Area (ha)</label>
                        <el-input v-model="form.total_area" type="number" min="0" step="0.01" placeholder="e.g. 5.25" class="afm-input" :class="{ 'afm-input--error': form.errors.total_area }" />
                        <span v-if="form.errors.total_area" class="afm-field__error">{{ form.errors.total_area }}</span>
                    </div>

                    <div class="afm-field afm-field--span2">
                        <label class="afm-field__label">Coffee Area (ha)</label>
                        <el-input v-model="form.coffee_area" type="number" min="0" step="0.01" placeholder="e.g. 3.75" class="afm-input" :class="{ 'afm-input--error': form.errors.coffee_area }" />
                        <span v-if="form.errors.coffee_area" class="afm-field__error">{{ form.errors.coffee_area }}</span>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="afm-modal__footer">
                <button type="button" class="afm-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="afm-btn-primary" :disabled="form.processing" @click="submit">
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
   scope attribute. Literal hex values match the app's --primary/#000,
   --border/#E5E7EB, --text/#121516 token palette used on every other
   redesigned page this session. */
.el-dialog.afm-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.el-dialog.afm-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.afm-modal .el-dialog__body { padding: 0; }
.el-dialog.afm-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.afm-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}

.afm-modal__head-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    background: #F1F2F3;
    color: #121516;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.afm-modal__head-text { flex: 1; min-width: 0; }

.afm-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}

.afm-modal__title {
    font-size: 1.0625rem;
    font-weight: 700;
    color: #121516;
    letter-spacing: -0.01em;
}

.afm-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: #F5F6F7;
    color: #6F7677;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}

.afm-modal__close:hover { background: #E5E7EB; color: #121516; }

.afm-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
}

.afm-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.afm-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.afm-field--span2 { grid-column: span 2; }

.afm-field__label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #121516;
}

.afm-req { color: #B91C1C; }

.afm-field__error {
    font-size: 0.75rem;
    font-weight: 500;
    color: #B91C1C;
    line-height: 1.4;
}

.afm-input { width: 100%; }

.afm-input :deep(.el-input__wrapper),
.afm-input :deep(.el-select__wrapper),
.afm-input :deep(.el-textarea__inner) {
    border-radius: 6px;
}
.afm-input--error :deep(.el-input__wrapper),
.afm-input--error :deep(.el-select__wrapper),
.afm-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #B91C1C inset !important;
}

.afm-section {
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px solid #E5E7EB;
}

.afm-section__title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6F7677;
    margin-bottom: 4px;
}

.afm-section__hint {
    font-size: 0.75rem;
    color: #6F7677;
    line-height: 1.5;
    margin: 0 0 12px;
}

.afm-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}

.afm-btn-primary {
    background: #000000;
    border: 1px solid transparent;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 0 16px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.afm-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.afm-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.afm-btn-outline {
    background: #fff;
    border: 1px solid #E5E7EB;
    color: #121516;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 0 16px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.afm-btn-outline:hover { background: #F5F6F7; }

@media (max-width: 640px) {
    .afm-grid { grid-template-columns: 1fr; }
    .afm-field--span2 { grid-column: span 1; }
}
</style>
