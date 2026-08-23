<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, User } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    farmer: { type: Object, required: true },
    cooperatives: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function fieldsFromFarmer() {
    return {
        first_name: props.farmer.first_name || '',
        middle_name: props.farmer.middle_name || '',
        last_name: props.farmer.last_name || '',
        gender: props.farmer.gender || '',
        date_of_birth: props.farmer.date_of_birth || '',
        national_id: props.farmer.national_id || '',
        tel: props.farmer.tel || '',
        email: props.farmer.email || '',
        country: props.farmer.country || '',
        region: props.farmer.region || '',
        district: props.farmer.district || '',
        county: props.farmer.county || '',
        subcounty: props.farmer.subcounty || '',
        parish: props.farmer.parish || '',
        village: props.farmer.village || '',
        cooperative_id: props.farmer.cooperative_id || '',
    };
}

const form = useForm(fieldsFromFarmer());

watch(() => props.modelValue, (open) => {
    if (!open) return;
    const fields = fieldsFromFarmer();
    form.defaults(fields);
    form.reset();
    form.clearErrors();
});

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.patch(route('farmer.update', props.farmer.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({
                title: 'Farmer Updated',
                message: 'Farmer details saved successfully.',
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
        class="efd-modal"
    >
        <template #header>
            <div class="efd-modal__head">
                <div class="efd-modal__head-icon">
                    <el-icon :size="18"><User /></el-icon>
                </div>
                <div class="efd-modal__head-text">
                    <div class="efd-modal__eyebrow">Farmer Profile</div>
                    <div class="efd-modal__title">Edit Farmer</div>
                </div>
                <button type="button" class="efd-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="efd-modal__body">
            <div class="efd-grid">
                <div class="efd-field">
                    <label class="efd-field__label">First Name <span class="efd-req">*</span></label>
                    <el-input v-model="form.first_name" placeholder="e.g. Joshua" class="efd-input" :class="{ 'efd-input--error': form.errors.first_name }" />
                    <span v-if="form.errors.first_name" class="efd-field__error">{{ form.errors.first_name }}</span>
                </div>

                <div class="efd-field">
                    <label class="efd-field__label">Last Name <span class="efd-req">*</span></label>
                    <el-input v-model="form.last_name" placeholder="e.g. Kato" class="efd-input" :class="{ 'efd-input--error': form.errors.last_name }" />
                    <span v-if="form.errors.last_name" class="efd-field__error">{{ form.errors.last_name }}</span>
                </div>

                <div class="efd-field">
                    <label class="efd-field__label">Middle Name</label>
                    <el-input v-model="form.middle_name" placeholder="e.g. Wasswa" class="efd-input" :class="{ 'efd-input--error': form.errors.middle_name }" />
                    <span v-if="form.errors.middle_name" class="efd-field__error">{{ form.errors.middle_name }}</span>
                </div>

                <div class="efd-field">
                    <label class="efd-field__label">Gender</label>
                    <el-select v-model="form.gender" clearable placeholder="Select gender" class="efd-input" :class="{ 'efd-input--error': form.errors.gender }">
                        <el-option label="Male" value="male" />
                        <el-option label="Female" value="female" />
                        <el-option label="Other" value="other" />
                    </el-select>
                    <span v-if="form.errors.gender" class="efd-field__error">{{ form.errors.gender }}</span>
                </div>

                <div class="efd-field">
                    <label class="efd-field__label">Date of Birth</label>
                    <el-date-picker v-model="form.date_of_birth" type="date" value-format="YYYY-MM-DD" placeholder="Select date" class="efd-input" :class="{ 'efd-input--error': form.errors.date_of_birth }" style="width: 100%" />
                    <span v-if="form.errors.date_of_birth" class="efd-field__error">{{ form.errors.date_of_birth }}</span>
                </div>

                <div class="efd-field">
                    <label class="efd-field__label">National ID</label>
                    <el-input v-model="form.national_id" placeholder="e.g. CM12345678AB9C" class="efd-input" :class="{ 'efd-input--error': form.errors.national_id }" />
                    <span v-if="form.errors.national_id" class="efd-field__error">{{ form.errors.national_id }}</span>
                </div>

                <div class="efd-field">
                    <label class="efd-field__label">Telephone <span class="efd-req">*</span></label>
                    <el-input v-model="form.tel" type="tel" placeholder="+256 752 567 534" class="efd-input" :class="{ 'efd-input--error': form.errors.tel }" />
                    <span v-if="form.errors.tel" class="efd-field__error">{{ form.errors.tel }}</span>
                </div>

                <div class="efd-field">
                    <label class="efd-field__label">Email Address</label>
                    <el-input v-model="form.email" type="email" placeholder="farmer@example.com" class="efd-input" :class="{ 'efd-input--error': form.errors.email }" />
                    <span v-if="form.errors.email" class="efd-field__error">{{ form.errors.email }}</span>
                </div>
            </div>

            <div class="efd-section">
                <div class="efd-section__title">Location</div>

                <div class="efd-grid">
                    <div class="efd-field">
                        <label class="efd-field__label">Country</label>
                        <el-input v-model="form.country" placeholder="e.g. Uganda" class="efd-input" :class="{ 'efd-input--error': form.errors.country }" />
                        <span v-if="form.errors.country" class="efd-field__error">{{ form.errors.country }}</span>
                    </div>

                    <div class="efd-field">
                        <label class="efd-field__label">Region</label>
                        <el-input v-model="form.region" placeholder="e.g. Eastern" class="efd-input" :class="{ 'efd-input--error': form.errors.region }" />
                        <span v-if="form.errors.region" class="efd-field__error">{{ form.errors.region }}</span>
                    </div>

                    <div class="efd-field">
                        <label class="efd-field__label">District <span class="efd-req">*</span></label>
                        <el-input v-model="form.district" placeholder="e.g. Mbale" class="efd-input" :class="{ 'efd-input--error': form.errors.district }" />
                        <span v-if="form.errors.district" class="efd-field__error">{{ form.errors.district }}</span>
                    </div>

                    <div class="efd-field">
                        <label class="efd-field__label">County</label>
                        <el-input v-model="form.county" placeholder="e.g. Bungokho" class="efd-input" :class="{ 'efd-input--error': form.errors.county }" />
                        <span v-if="form.errors.county" class="efd-field__error">{{ form.errors.county }}</span>
                    </div>

                    <div class="efd-field">
                        <label class="efd-field__label">Sub-county</label>
                        <el-input v-model="form.subcounty" placeholder="e.g. Bungokho" class="efd-input" :class="{ 'efd-input--error': form.errors.subcounty }" />
                        <span v-if="form.errors.subcounty" class="efd-field__error">{{ form.errors.subcounty }}</span>
                    </div>

                    <div class="efd-field">
                        <label class="efd-field__label">Parish</label>
                        <el-input v-model="form.parish" placeholder="e.g. Bumwoni" class="efd-input" :class="{ 'efd-input--error': form.errors.parish }" />
                        <span v-if="form.errors.parish" class="efd-field__error">{{ form.errors.parish }}</span>
                    </div>

                    <div class="efd-field efd-field--span2">
                        <label class="efd-field__label">Village</label>
                        <el-input v-model="form.village" placeholder="e.g. Busamaga" class="efd-input" :class="{ 'efd-input--error': form.errors.village }" />
                        <span v-if="form.errors.village" class="efd-field__error">{{ form.errors.village }}</span>
                    </div>
                </div>
            </div>

            <div class="efd-section">
                <div class="efd-section__title">Cooperative</div>

                <div class="efd-field">
                    <el-select v-model="form.cooperative_id" clearable filterable placeholder="Assign cooperative (optional)" class="efd-input" :class="{ 'efd-input--error': form.errors.cooperative_id }">
                        <el-option v-for="c in cooperatives" :key="c.id" :label="c.name" :value="c.id" />
                    </el-select>
                    <span v-if="form.errors.cooperative_id" class="efd-field__error">{{ form.errors.cooperative_id }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="efd-modal__footer">
                <button type="button" class="efd-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="efd-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
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
.el-dialog.efd-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

.el-dialog.efd-modal .el-dialog__header {
    padding: 0;
    margin: 0;
}

.el-dialog.efd-modal .el-dialog__body {
    padding: 0;
}

.el-dialog.efd-modal .el-dialog__footer {
    padding: 0;
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.efd-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.efd-modal__head-icon {
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

.efd-modal__head-text { flex: 1; min-width: 0; }

.efd-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #271310;
    margin-bottom: 1px;
}

.efd-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.efd-modal__close {
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

.efd-modal__close:hover { background: #e5e7eb; color: #111827; }

.efd-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
}

.efd-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.efd-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.efd-field--span2 { grid-column: span 2; }

.efd-field__label {
    font-size: 0.875rem;
    font-weight: 700;
    color: #111827;
}

.efd-req { color: #dc2626; }

.efd-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.efd-input { width: 100%; }

.efd-input--error :deep(.el-input__wrapper),
.efd-input--error :deep(.el-select__wrapper) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.efd-section {
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px solid #f3f4f6;
}

.efd-section__title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 12px;
}

.efd-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.efd-btn-primary {
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

.efd-btn-primary:hover { opacity: 0.9; }
.efd-btn-primary:disabled { opacity: 0.6; cursor: default; }

.efd-btn-outline {
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

.efd-btn-outline:hover { background: #f8fafc; }

@media (max-width: 640px) {
    .efd-grid { grid-template-columns: 1fr; }
    .efd-field--span2 { grid-column: span 1; }
}
</style>
