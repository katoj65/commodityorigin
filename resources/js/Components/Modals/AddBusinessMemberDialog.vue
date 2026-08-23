<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { Close, Upload, UserFilled } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    member: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const isEdit = computed(() => Boolean(props.member));

function emptyForm() {
    return {
        first_name: '',
        last_name: '',
        gender: '',
        date_of_birth: '',
        id_number: '',
        designation: '',
        position: '',
        telephone: '',
        email: '',
        address: '',
        status: 'active',
        bio: '',
        notes: '',
        photo: null,
    };
}

function formFromMember(member) {
    return {
        first_name: member.first_name ?? '',
        last_name: member.last_name ?? '',
        gender: member.gender ?? '',
        date_of_birth: member.date_of_birth ?? '',
        id_number: member.id_number ?? '',
        designation: member.designation ?? '',
        position: member.position ?? '',
        telephone: member.telephone ?? '',
        email: member.email ?? '',
        address: member.address ?? '',
        status: member.status || 'active',
        bio: member.bio ?? '',
        notes: member.notes ?? '',
        photo: null,
    };
}

const form = useForm(emptyForm());

/* ── Photo preview — shows the newly picked file once chosen, falling
   back to the already-saved photo, and to nothing (upload prompt)
   otherwise. ──────────────────────────────────────────────────────── */
const localPreviewUrl = ref('');
const displayPhotoUrl = computed(() => localPreviewUrl.value || props.member?.photo_url || null);

function revokeLocalPreview() {
    if (localPreviewUrl.value) URL.revokeObjectURL(localPreviewUrl.value);
    localPreviewUrl.value = '';
}

function onPhotoChange(event) {
    const file = event.target.files?.[0] || null;
    form.photo = file;

    revokeLocalPreview();
    if (file) localPreviewUrl.value = URL.createObjectURL(file);
}

onBeforeUnmount(revokeLocalPreview);

watch(() => props.modelValue, (open) => {
    if (!open) return;
    const defaults = isEdit.value ? formFromMember(props.member) : emptyForm();
    form.defaults(defaults);
    form.reset();
    form.clearErrors();
    revokeLocalPreview();
});

function disableFutureDates(date) {
    const today = new Date();
    today.setHours(23, 59, 59, 999);
    return date.getTime() > today.getTime();
}

function closeDialog() {
    dialogVisible.value = false;
}

function submit() {
    if (isEdit.value) {
        form.patch(route('business.members.update', props.member.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeDialog();
                ElNotification({
                    title: 'Member Updated',
                    message: 'The member\'s details were saved.',
                    type: 'success',
                    duration: 3200,
                    offset: 84,
                });
            },
        });
        return;
    }

    form.post(route('business.members.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            ElNotification({
                title: 'Member Registered',
                message: 'The new member was added to your business.',
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
        width="min(640px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :close-on-click-modal="false"
        :show-close="false"
        class="abm-modal"
    >
        <template #header>
            <div class="abm-modal__head">
                <div class="abm-modal__head-icon">
                    <el-icon :size="18"><UserFilled /></el-icon>
                </div>
                <div class="abm-modal__head-text">
                    <div class="abm-modal__eyebrow">Business Members</div>
                    <div class="abm-modal__title">{{ isEdit ? 'Edit Member' : 'Register Member' }}</div>
                </div>
                <button type="button" class="abm-modal__close" aria-label="Close" @click="closeDialog">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div class="abm-modal__body">
            <div class="abm-photo">
                <label class="abm-photo__upload" :class="{ 'abm-photo__upload--has-image': displayPhotoUrl }">
                    <input type="file" accept="image/*" class="abm-photo__input" @change="onPhotoChange">
                    <img v-if="displayPhotoUrl" :src="displayPhotoUrl" alt="Member photo preview" class="abm-photo__preview">
                    <template v-if="displayPhotoUrl">
                        <div class="abm-photo__overlay">
                            <el-icon><Upload /></el-icon>
                            <span>Change photo</span>
                        </div>
                    </template>
                    <template v-else>
                        <el-icon><Upload /></el-icon>
                        <span>Upload a photo (optional)</span>
                    </template>
                </label>
                <span v-if="form.errors.photo" class="abm-field__error">{{ form.errors.photo }}</span>
            </div>

            <div class="abm-grid">
                <div class="abm-field">
                    <label class="abm-field__label">First Name</label>
                    <el-input v-model="form.first_name" placeholder="First name" class="abm-input" :class="{ 'abm-input--error': form.errors.first_name }" />
                    <span v-if="form.errors.first_name" class="abm-field__error">{{ form.errors.first_name }}</span>
                </div>

                <div class="abm-field">
                    <label class="abm-field__label">Last Name</label>
                    <el-input v-model="form.last_name" placeholder="Last name" class="abm-input" :class="{ 'abm-input--error': form.errors.last_name }" />
                    <span v-if="form.errors.last_name" class="abm-field__error">{{ form.errors.last_name }}</span>
                </div>

                <div class="abm-field">
                    <label class="abm-field__label">Gender</label>
                    <el-select v-model="form.gender" placeholder="Select gender" class="abm-input" :class="{ 'abm-input--error': form.errors.gender }">
                        <el-option label="Male" value="male" />
                        <el-option label="Female" value="female" />
                        <el-option label="Prefer not to say" value="prefer_not_to_say" />
                    </el-select>
                    <span v-if="form.errors.gender" class="abm-field__error">{{ form.errors.gender }}</span>
                </div>

                <div class="abm-field">
                    <label class="abm-field__label">Date of Birth</label>
                    <el-date-picker
                        v-model="form.date_of_birth"
                        type="date"
                        value-format="YYYY-MM-DD"
                        placeholder="Select date of birth"
                        class="abm-input"
                        :class="{ 'abm-input--error': form.errors.date_of_birth }"
                        :disabled-date="disableFutureDates"
                        style="width: 100%"
                        popper-class="abm-date-popper"
                    />
                    <span v-if="form.errors.date_of_birth" class="abm-field__error">{{ form.errors.date_of_birth }}</span>
                </div>

                <div class="abm-field">
                    <label class="abm-field__label">ID Number</label>
                    <el-input v-model="form.id_number" placeholder="National ID number" class="abm-input" :class="{ 'abm-input--error': form.errors.id_number }" />
                    <span v-if="form.errors.id_number" class="abm-field__error">{{ form.errors.id_number }}</span>
                </div>

                <div class="abm-field">
                    <label class="abm-field__label">Designation</label>
                    <el-input v-model="form.designation" placeholder="e.g. Operations Lead" class="abm-input" :class="{ 'abm-input--error': form.errors.designation }" />
                    <span v-if="form.errors.designation" class="abm-field__error">{{ form.errors.designation }}</span>
                </div>

                <div class="abm-field">
                    <label class="abm-field__label">Position</label>
                    <el-input v-model="form.position" placeholder="e.g. Export Coordinator" class="abm-input" :class="{ 'abm-input--error': form.errors.position }" />
                    <span v-if="form.errors.position" class="abm-field__error">{{ form.errors.position }}</span>
                </div>

                <div class="abm-field">
                    <label class="abm-field__label">Telephone</label>
                    <el-input v-model="form.telephone" placeholder="+256…" class="abm-input" :class="{ 'abm-input--error': form.errors.telephone }" />
                    <span v-if="form.errors.telephone" class="abm-field__error">{{ form.errors.telephone }}</span>
                </div>

                <div class="abm-field">
                    <label class="abm-field__label">Status</label>
                    <el-select v-model="form.status" placeholder="Select status" class="abm-input" :class="{ 'abm-input--error': form.errors.status }">
                        <el-option label="Active" value="active" />
                        <el-option label="Inactive" value="inactive" />
                    </el-select>
                    <span v-if="form.errors.status" class="abm-field__error">{{ form.errors.status }}</span>
                </div>

                <div class="abm-field abm-field--span2">
                    <label class="abm-field__label">Email <small>(optional)</small></label>
                    <el-input v-model="form.email" placeholder="member@example.com" class="abm-input" :class="{ 'abm-input--error': form.errors.email }" />
                    <span v-if="form.errors.email" class="abm-field__error">{{ form.errors.email }}</span>
                </div>

                <div class="abm-field abm-field--span2">
                    <label class="abm-field__label">Address <small>(optional)</small></label>
                    <el-input v-model="form.address" placeholder="Street, city, district" class="abm-input" :class="{ 'abm-input--error': form.errors.address }" />
                    <span v-if="form.errors.address" class="abm-field__error">{{ form.errors.address }}</span>
                </div>

                <div class="abm-field abm-field--span2">
                    <label class="abm-field__label">Bio <small>(optional)</small></label>
                    <el-input v-model="form.bio" type="textarea" :rows="3" placeholder="A short bio — role history, expertise, background" class="abm-input" :class="{ 'abm-input--error': form.errors.bio }" />
                    <span v-if="form.errors.bio" class="abm-field__error">{{ form.errors.bio }}</span>
                </div>

                <div class="abm-field abm-field--span2">
                    <label class="abm-field__label">Notes <small>(optional)</small></label>
                    <el-input v-model="form.notes" type="textarea" :rows="2" placeholder="Any additional detail about this member" class="abm-input" :class="{ 'abm-input--error': form.errors.notes }" />
                    <span v-if="form.errors.notes" class="abm-field__error">{{ form.errors.notes }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="abm-modal__footer">
                <button type="button" class="abm-btn-outline" @click="closeDialog">Cancel</button>
                <button type="button" class="abm-btn-primary" :disabled="form.processing" @click="submit">
                    {{ form.processing ? (isEdit ? 'Saving…' : 'Registering…') : (isEdit ? 'Save Changes' : 'Register Member') }}
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so it never carries this SFC's
   scope attribute — a scoped (or :deep()) selector can never reach it. */
.el-dialog.abm-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}
.el-dialog.abm-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.abm-modal .el-dialog__body { padding: 0; }
.el-dialog.abm-modal .el-dialog__footer { padding: 0; }

.el-popper.abm-date-popper {
    margin-top: 8px;
    box-shadow: 0 12px 28px rgba(0, 20, 15, 0.16);
}

.abm-input.el-date-editor {
    height: 40px;
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.abm-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}
.abm-modal__head-icon {
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
.abm-modal__head-text { flex: 1; min-width: 0; }
.abm-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #271310;
    margin-bottom: 1px;
}
.abm-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}
.abm-modal__close {
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
.abm-modal__close:hover { background: #e5e7eb; color: #111827; }

.abm-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
}

.abm-photo {
    margin-bottom: 18px;
    display: flex;
    justify-content: center;
}
.abm-photo__upload {
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
.abm-photo__upload:hover { background: #f3f4f6; }
.abm-photo__upload .el-icon { color: #271310; font-size: 16px; flex-shrink: 0; }
.abm-photo__input { display: none; }

.abm-photo__upload--has-image {
    position: relative;
    width: 104px;
    height: 104px;
    padding: 0;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
    background: #f1f5f3;
}
.abm-photo__upload--has-image:hover { background: #f1f5f3; }
.abm-photo__preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.abm-photo__overlay {
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
.abm-photo__overlay .el-icon { color: #fff; font-size: 16px; }
.abm-photo__upload--has-image:hover .abm-photo__overlay { opacity: 1; }

.abm-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.abm-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
    margin-bottom: 16px;
}
.abm-field--span2 { grid-column: span 2; }

.abm-field__label { font-size: 0.75rem; font-weight: 700; color: #111827; }
.abm-field__label small { font-weight: 500; color: #9ca3af; text-transform: none; }

.abm-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.abm-input { width: 100%; }
.abm-input--error :deep(.el-input__wrapper),
.abm-input--error :deep(.el-select__wrapper),
.abm-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

.abm-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.abm-btn-primary {
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
.abm-btn-primary:hover { opacity: 0.9; }
.abm-btn-primary:disabled { opacity: 0.6; cursor: default; }

.abm-btn-outline {
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
.abm-btn-outline:hover { background: #f8fafc; }

@media (max-width: 640px) {
    .abm-grid { grid-template-columns: 1fr; }
    .abm-field--span2 { grid-column: span 1; }
}
</style>
