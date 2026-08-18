<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Edit, Upload } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    user: { type: Object, default: () => ({}) },
    profile: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function emptyForm() {
    return {
        first_name: props.user?.first_name || '',
        last_name: props.user?.last_name || '',
        date_of_birth: props.profile?.date_of_birth || '',
        gender: props.profile?.gender || '',
        address_line_1: props.profile?.address_line_1 || '',
        address_line_2: props.profile?.address_line_2 || '',
        city: props.profile?.city || '',
        state: props.profile?.state || '',
        country: props.profile?.country || '',
        postal_code: props.profile?.postal_code || '',
        bio: props.profile?.bio || '',
        photo: null,
    };
}

const form = useForm(emptyForm());

/* ── Photo preview — shows the newly picked file once chosen, falling
   back to the profile's already-saved photo, and to nothing (upload
   prompt) if neither exists. ────────────────────────────────────────── */
const localPreviewUrl = ref('');
const displayPhotoUrl = computed(() => localPreviewUrl.value || props.profile?.profile_photo_url || null);

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

function disableFutureDates(date) {
    const today = new Date();
    today.setHours(23, 59, 59, 999);
    return date.getTime() > today.getTime();
}

function onPhotoChange(event) {
    const file = event.target.files?.[0] || null;
    form.photo = file;

    revokeLocalPreview();
    if (file) localPreviewUrl.value = URL.createObjectURL(file);
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({
                title: 'Profile Updated',
                message: 'Your profile details were saved successfully.',
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
        width="min(680px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="epd-modal"
    >
        <template #header>
            <div class="epd-modal__head">
                <div class="epd-modal__head-icon">
                    <el-icon :size="18"><Edit /></el-icon>
                </div>
                <div class="epd-modal__head-text">
                    <div class="epd-modal__eyebrow">User Profile</div>
                    <div class="epd-modal__title">Edit Profile</div>
                </div>
                <button type="button" class="epd-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="epd-modal__body">
            <div class="epd-photo">
                <label class="epd-photo__upload" :class="{ 'epd-photo__upload--has-image': displayPhotoUrl }">
                    <input type="file" accept="image/*" class="epd-photo__input" @change="onPhotoChange">
                    <img v-if="displayPhotoUrl" :src="displayPhotoUrl" alt="Profile photo preview" class="epd-photo__preview">
                    <template v-if="displayPhotoUrl">
                        <div class="epd-photo__overlay">
                            <el-icon><Upload /></el-icon>
                            <span>Change photo</span>
                        </div>
                    </template>
                    <template v-else>
                        <el-icon><Upload /></el-icon>
                        <span>Upload a profile photo (optional)</span>
                    </template>
                </label>
                <span v-if="form.errors.photo" class="epd-field__error">{{ form.errors.photo }}</span>
            </div>

            <div class="epd-grid">
                <div class="epd-field">
                    <label class="epd-field__label">First Name</label>
                    <el-input v-model="form.first_name" placeholder="First name" class="epd-input" :class="{ 'epd-input--error': form.errors.first_name }" />
                    <span v-if="form.errors.first_name" class="epd-field__error">{{ form.errors.first_name }}</span>
                </div>

                <div class="epd-field">
                    <label class="epd-field__label">Last Name</label>
                    <el-input v-model="form.last_name" placeholder="Last name" class="epd-input" :class="{ 'epd-input--error': form.errors.last_name }" />
                    <span v-if="form.errors.last_name" class="epd-field__error">{{ form.errors.last_name }}</span>
                </div>

                <div class="epd-field">
                    <label class="epd-field__label">Date of Birth</label>
                    <el-date-picker
                        v-model="form.date_of_birth"
                        type="date"
                        value-format="YYYY-MM-DD"
                        placeholder="Select date of birth"
                        class="epd-input"
                        :class="{ 'epd-input--error': form.errors.date_of_birth }"
                        :disabled-date="disableFutureDates"
                        style="width: 100%"
                        popper-class="epd-date-popper"
                    />
                    <span v-if="form.errors.date_of_birth" class="epd-field__error">{{ form.errors.date_of_birth }}</span>
                </div>

                <div class="epd-field">
                    <label class="epd-field__label">Gender</label>
                    <el-select v-model="form.gender" placeholder="Select gender" class="epd-input" :class="{ 'epd-input--error': form.errors.gender }">
                        <el-option label="Male" value="male" />
                        <el-option label="Female" value="female" />
                        <el-option label="Prefer not to say" value="prefer_not_to_say" />
                    </el-select>
                    <span v-if="form.errors.gender" class="epd-field__error">{{ form.errors.gender }}</span>
                </div>

                <div class="epd-field epd-field--span2">
                    <label class="epd-field__label">Address Line 1</label>
                    <el-input v-model="form.address_line_1" placeholder="Street address" class="epd-input" :class="{ 'epd-input--error': form.errors.address_line_1 }" />
                    <span v-if="form.errors.address_line_1" class="epd-field__error">{{ form.errors.address_line_1 }}</span>
                </div>

                <div class="epd-field epd-field--span2">
                    <label class="epd-field__label">Address Line 2 <small>(optional)</small></label>
                    <el-input v-model="form.address_line_2" placeholder="Apartment, suite, etc." class="epd-input" :class="{ 'epd-input--error': form.errors.address_line_2 }" />
                    <span v-if="form.errors.address_line_2" class="epd-field__error">{{ form.errors.address_line_2 }}</span>
                </div>

                <div class="epd-field">
                    <label class="epd-field__label">City</label>
                    <el-input v-model="form.city" placeholder="City" class="epd-input" :class="{ 'epd-input--error': form.errors.city }" />
                    <span v-if="form.errors.city" class="epd-field__error">{{ form.errors.city }}</span>
                </div>

                <div class="epd-field">
                    <label class="epd-field__label">State / Province</label>
                    <el-input v-model="form.state" placeholder="State or province" class="epd-input" :class="{ 'epd-input--error': form.errors.state }" />
                    <span v-if="form.errors.state" class="epd-field__error">{{ form.errors.state }}</span>
                </div>

                <div class="epd-field">
                    <label class="epd-field__label">Country</label>
                    <el-input v-model="form.country" placeholder="Country" class="epd-input" :class="{ 'epd-input--error': form.errors.country }" />
                    <span v-if="form.errors.country" class="epd-field__error">{{ form.errors.country }}</span>
                </div>

                <div class="epd-field">
                    <label class="epd-field__label">Postal Code <small>(optional)</small></label>
                    <el-input v-model="form.postal_code" placeholder="Postal code" class="epd-input" :class="{ 'epd-input--error': form.errors.postal_code }" />
                    <span v-if="form.errors.postal_code" class="epd-field__error">{{ form.errors.postal_code }}</span>
                </div>

                <div class="epd-field epd-field--span2">
                    <label class="epd-field__label">Bio <small>(optional)</small></label>
                    <el-input v-model="form.bio" type="textarea" :rows="3" placeholder="A short bio for your profile" class="epd-input" :class="{ 'epd-input--error': form.errors.bio }" />
                    <span v-if="form.errors.bio" class="epd-field__error">{{ form.errors.bio }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="epd-modal__footer">
                <button type="button" class="epd-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="epd-btn-primary" :disabled="form.processing" @click="submit">
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
.el-dialog.epd-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}
.el-dialog.epd-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.epd-modal .el-dialog__body { padding: 0; }
.el-dialog.epd-modal .el-dialog__footer { padding: 0; }

.el-popper.epd-date-popper {
    margin-top: 8px;
    box-shadow: 0 12px 28px rgba(0, 20, 15, 0.16);
}

/* <el-date-picker>'s root element doesn't reliably receive this SFC's
   scoped data-v attribute, so the height-matching fix below (see
   .epd-input--error scoped rule for context) has to live here instead —
   otherwise its inner .el-input__wrapper renders taller than the outer
   .el-date-editor box that flex layout reserves space for, and the extra
   height paints over whatever sits below it (e.g. a validation error). */
.epd-input.el-date-editor {
    height: 48px;
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.epd-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}
.epd-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(20, 92, 66, 0.08);
    color: #145c42;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.epd-modal__head-text { flex: 1; min-width: 0; }
.epd-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #145c42;
    margin-bottom: 1px;
}
.epd-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}
.epd-modal__close {
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
.epd-modal__close:hover { background: #e5e7eb; color: #111827; }

.epd-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
}

.epd-photo {
    margin-bottom: 18px;
    display: flex;
    justify-content: center;
}
.epd-photo__upload {
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
.epd-photo__upload:hover { background: #f3f4f6; }
.epd-photo__upload .el-icon { color: #145c42; font-size: 16px; flex-shrink: 0; }
.epd-photo__input { display: none; }

.epd-photo__upload--has-image {
    position: relative;
    width: 104px;
    height: 104px;
    padding: 0;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
    background: #f1f5f3;
}
.epd-photo__upload--has-image:hover { background: #f1f5f3; }
.epd-photo__preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.epd-photo__overlay {
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
.epd-photo__overlay .el-icon { color: #fff; font-size: 16px; }
.epd-photo__upload--has-image:hover .epd-photo__overlay { opacity: 1; }

.epd-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.epd-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
    margin-bottom: 16px;
}
.epd-field--span2 { grid-column: span 2; }

.epd-field__label { font-size: 0.75rem; font-weight: 700; color: #111827; }
.epd-field__label small { font-weight: 500; color: #9ca3af; text-transform: none; }

.epd-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.epd-input { width: 100%; }
.epd-input--error :deep(.el-input__wrapper),
.epd-input--error :deep(.el-select__wrapper),
.epd-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.epd-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.epd-btn-primary {
    background: linear-gradient(135deg, #145c42, #0d3d2c);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.epd-btn-primary:hover { opacity: 0.9; }
.epd-btn-primary:disabled { opacity: 0.6; cursor: default; }

.epd-btn-outline {
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
.epd-btn-outline:hover { background: #f8fafc; }

@media (max-width: 640px) {
    .epd-grid { grid-template-columns: 1fr; }
    .epd-field--span2 { grid-column: span 1; }
}
</style>
