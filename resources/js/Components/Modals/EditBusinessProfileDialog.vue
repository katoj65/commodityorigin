<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, OfficeBuilding, Upload } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    business: { type: Object, default: () => ({}) },
    businessTypeOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
        business_name: props.business?.business_name || '',
        business_type: props.business?.business_type || '',
        industry: props.business?.industry || '',
        registration_number: props.business?.registration_number || '',
        tax_id: props.business?.tax_id || '',
        website: props.business?.website || '',
        contact_email: props.business?.contact_email || '',
        contact_phone: props.business?.contact_phone || '',
        employee_count: props.business?.employee_count ?? '',
        year_established: props.business?.year_established ?? '',
        description: props.business?.description || '',
        address_line_1: props.business?.address_line_1 || '',
        address_line_2: props.business?.address_line_2 || '',
        city: props.business?.city || '',
        state: props.business?.state || '',
        country: props.business?.country || '',
        postal_code: props.business?.postal_code || '',
        logo: null,
    };
}

const form = useForm(emptyForm());

/* ── Logo preview — shows the newly picked file once chosen, falling back
   to the already-saved logo, and to nothing (upload prompt) otherwise. ── */
const localPreviewUrl = ref('');
const displayLogoUrl = computed(() => localPreviewUrl.value || props.business?.logo_url || null);

function revokeLocalPreview() {
    if (localPreviewUrl.value) URL.revokeObjectURL(localPreviewUrl.value);
    localPreviewUrl.value = '';
}

watch(() => props.modelValue, (open) => {
    if (!open) return;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();
    revokeLocalPreview();
});

onBeforeUnmount(revokeLocalPreview);

function onLogoChange(event) {
    const file = event.target.files?.[0] || null;
    form.logo = file;

    revokeLocalPreview();
    if (file) localPreviewUrl.value = URL.createObjectURL(file);
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.patch(route('profile.business.update'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({
                title: 'Business Profile Updated',
                message: 'Your business details were saved successfully.',
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
        class="ebp-modal"
    >
        <template #header>
            <div class="ebp-modal__head">
                <div class="ebp-modal__head-icon">
                    <el-icon :size="18"><OfficeBuilding /></el-icon>
                </div>
                <div class="ebp-modal__head-text">
                    <div class="ebp-modal__eyebrow">Business Profile</div>
                    <div class="ebp-modal__title">Edit Business Profile</div>
                </div>
                <button type="button" class="ebp-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="ebp-modal__body">
            <div class="ebp-logo">
                <label class="ebp-logo__upload" :class="{ 'ebp-logo__upload--has-image': displayLogoUrl }">
                    <input type="file" accept="image/*" class="ebp-logo__input" @change="onLogoChange">
                    <img v-if="displayLogoUrl" :src="displayLogoUrl" alt="Business logo preview" class="ebp-logo__preview">
                    <template v-if="displayLogoUrl">
                        <div class="ebp-logo__overlay">
                            <el-icon><Upload /></el-icon>
                            <span>Change logo</span>
                        </div>
                    </template>
                    <template v-else>
                        <el-icon><Upload /></el-icon>
                        <span>Upload a business logo (optional)</span>
                    </template>
                </label>
                <span v-if="form.errors.logo" class="ebp-field__error">{{ form.errors.logo }}</span>
            </div>

            <div class="ebp-grid">
                <div class="ebp-field ebp-field--span2">
                    <label class="ebp-field__label">Business Name</label>
                    <el-input v-model="form.business_name" placeholder="e.g. Bugisu Highland Coffee Co." class="ebp-input" :class="{ 'ebp-input--error': form.errors.business_name }" />
                    <span v-if="form.errors.business_name" class="ebp-field__error">{{ form.errors.business_name }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Business Type</label>
                    <el-select v-model="form.business_type" placeholder="Select business type" class="ebp-input" :class="{ 'ebp-input--error': form.errors.business_type }">
                        <el-option v-for="opt in businessTypeOptions" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                    <span v-if="form.errors.business_type" class="ebp-field__error">{{ form.errors.business_type }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Industry <small>(optional)</small></label>
                    <el-input v-model="form.industry" placeholder="e.g. Coffee Export" class="ebp-input" :class="{ 'ebp-input--error': form.errors.industry }" />
                    <span v-if="form.errors.industry" class="ebp-field__error">{{ form.errors.industry }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Registration Number <small>(optional)</small></label>
                    <el-input v-model="form.registration_number" placeholder="Business registration #" class="ebp-input" :class="{ 'ebp-input--error': form.errors.registration_number }" />
                    <span v-if="form.errors.registration_number" class="ebp-field__error">{{ form.errors.registration_number }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Tax ID <small>(optional)</small></label>
                    <el-input v-model="form.tax_id" placeholder="Tax identification number" class="ebp-input" :class="{ 'ebp-input--error': form.errors.tax_id }" />
                    <span v-if="form.errors.tax_id" class="ebp-field__error">{{ form.errors.tax_id }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Website <small>(optional)</small></label>
                    <el-input v-model="form.website" placeholder="https://…" class="ebp-input" :class="{ 'ebp-input--error': form.errors.website }" />
                    <span v-if="form.errors.website" class="ebp-field__error">{{ form.errors.website }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Employees <small>(optional)</small></label>
                    <el-input v-model="form.employee_count" type="number" min="0" placeholder="e.g. 25" class="ebp-input" :class="{ 'ebp-input--error': form.errors.employee_count }" />
                    <span v-if="form.errors.employee_count" class="ebp-field__error">{{ form.errors.employee_count }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Contact Email <small>(optional)</small></label>
                    <el-input v-model="form.contact_email" placeholder="business@example.com" class="ebp-input" :class="{ 'ebp-input--error': form.errors.contact_email }" />
                    <span v-if="form.errors.contact_email" class="ebp-field__error">{{ form.errors.contact_email }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Contact Phone <small>(optional)</small></label>
                    <el-input v-model="form.contact_phone" placeholder="+256…" class="ebp-input" :class="{ 'ebp-input--error': form.errors.contact_phone }" />
                    <span v-if="form.errors.contact_phone" class="ebp-field__error">{{ form.errors.contact_phone }}</span>
                </div>

                <div class="ebp-field ebp-field--span2">
                    <label class="ebp-field__label">Year Established <small>(optional)</small></label>
                    <el-input v-model="form.year_established" type="number" min="1800" :max="new Date().getFullYear()" placeholder="e.g. 2014" class="ebp-input" :class="{ 'ebp-input--error': form.errors.year_established }" />
                    <span v-if="form.errors.year_established" class="ebp-field__error">{{ form.errors.year_established }}</span>
                </div>

                <div class="ebp-field ebp-field--span2">
                    <label class="ebp-field__label">Description <small>(optional)</small></label>
                    <el-input v-model="form.description" type="textarea" :rows="3" placeholder="A short description of the business" class="ebp-input" :class="{ 'ebp-input--error': form.errors.description }" />
                    <span v-if="form.errors.description" class="ebp-field__error">{{ form.errors.description }}</span>
                </div>

                <div class="ebp-field ebp-field--span2">
                    <label class="ebp-field__label">Address Line 1</label>
                    <el-input v-model="form.address_line_1" placeholder="Street address" class="ebp-input" :class="{ 'ebp-input--error': form.errors.address_line_1 }" />
                    <span v-if="form.errors.address_line_1" class="ebp-field__error">{{ form.errors.address_line_1 }}</span>
                </div>

                <div class="ebp-field ebp-field--span2">
                    <label class="ebp-field__label">Address Line 2 <small>(optional)</small></label>
                    <el-input v-model="form.address_line_2" placeholder="Suite, floor, etc." class="ebp-input" :class="{ 'ebp-input--error': form.errors.address_line_2 }" />
                    <span v-if="form.errors.address_line_2" class="ebp-field__error">{{ form.errors.address_line_2 }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">City</label>
                    <el-input v-model="form.city" placeholder="City" class="ebp-input" :class="{ 'ebp-input--error': form.errors.city }" />
                    <span v-if="form.errors.city" class="ebp-field__error">{{ form.errors.city }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">State / Province</label>
                    <el-input v-model="form.state" placeholder="State or province" class="ebp-input" :class="{ 'ebp-input--error': form.errors.state }" />
                    <span v-if="form.errors.state" class="ebp-field__error">{{ form.errors.state }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Country</label>
                    <el-input v-model="form.country" placeholder="Country" class="ebp-input" :class="{ 'ebp-input--error': form.errors.country }" />
                    <span v-if="form.errors.country" class="ebp-field__error">{{ form.errors.country }}</span>
                </div>

                <div class="ebp-field">
                    <label class="ebp-field__label">Postal Code <small>(optional)</small></label>
                    <el-input v-model="form.postal_code" placeholder="Postal code" class="ebp-input" :class="{ 'ebp-input--error': form.errors.postal_code }" />
                    <span v-if="form.errors.postal_code" class="ebp-field__error">{{ form.errors.postal_code }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="ebp-modal__footer">
                <button type="button" class="ebp-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="ebp-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so it never carries this SFC's
   scope attribute — a scoped (or :deep()) selector can never reach it. */
.el-dialog.ebp-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}
.el-dialog.ebp-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.ebp-modal .el-dialog__body { padding: 0; }
.el-dialog.ebp-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.ebp-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}
.ebp-modal__head-icon {
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
.ebp-modal__head-text { flex: 1; min-width: 0; }
.ebp-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #271310;
    margin-bottom: 1px;
}
.ebp-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}
.ebp-modal__close {
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
.ebp-modal__close:hover { background: #e5e7eb; color: #111827; }

.ebp-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
}

.ebp-logo {
    margin-bottom: 18px;
    display: flex;
    justify-content: center;
}
.ebp-logo__upload {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border: 1px dashed #d1d5db;
    border-radius: 10px;
    background: #fafbfc;
    color: #374151;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.12s ease;
    width: 100%;
}
.ebp-logo__upload:hover { background: #f3f4f6; }
.ebp-logo__upload .el-icon { color: #271310; font-size: 16px; flex-shrink: 0; }
.ebp-logo__input { display: none; }

.ebp-logo__upload--has-image {
    position: relative;
    width: 104px;
    height: 104px;
    padding: 0;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
    background: #f1f5f3;
}
.ebp-logo__upload--has-image:hover { background: #f1f5f3; }
.ebp-logo__preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.ebp-logo__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    background: rgba(15, 23, 42, 0.55);
    color: #fff;
    font-size: 0.6875rem;
    font-weight: 700;
    opacity: 0;
    transition: opacity 0.15s ease;
}
.ebp-logo__overlay .el-icon { color: #fff; font-size: 16px; }
.ebp-logo__upload--has-image:hover .ebp-logo__overlay { opacity: 1; }

.ebp-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.ebp-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
    margin-bottom: 16px;
}
.ebp-field--span2 { grid-column: span 2; }

.ebp-field__label { font-size: 0.75rem; font-weight: 700; color: #111827; }
.ebp-field__label small { font-weight: 500; color: #9ca3af; text-transform: none; }

.ebp-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.ebp-input { width: 100%; }
.ebp-input--error :deep(.el-input__wrapper),
.ebp-input--error :deep(.el-select__wrapper),
.ebp-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.ebp-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.ebp-btn-primary {
    background: #271310;
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.ebp-btn-primary:hover { opacity: 0.9; }
.ebp-btn-primary:disabled { opacity: 0.6; cursor: default; }

.ebp-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.ebp-btn-outline:hover { background: #f8fafc; }

@media (max-width: 640px) {
    .ebp-grid { grid-template-columns: 1fr; }
    .ebp-field--span2 { grid-column: span 1; }
}
</style>
