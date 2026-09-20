<script setup>
import { computed, ref, onBeforeUnmount } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { User, OfficeBuilding, Check, Loading, Picture, CircleCheck } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'success']);

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);

const initials = computed(() => {
    const name = authUser.value?.name ?? '';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('') || '—';
});

const form = useForm({
    profile_type: 'personal',
    photo: null,
    date_of_birth: '',
    gender: '',
    bio: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    country: 'Uganda',
    postal_code: '',
});

const accountTypeDescription = computed(() => (
    form.profile_type === 'business'
        ? "You're signing up on behalf of a company, cooperative, or exporter."
        : "You're trading on your own, maybe as a farmer, trader, or independent buyer."
));

// ── Photo upload ─────────────────────────────────────────────────────────
const fileInput = ref(null);
const photoPreview = ref(null);

function pickPhoto() {
    fileInput.value?.click();
}

function onPhotoSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    if (photoPreview.value) URL.revokeObjectURL(photoPreview.value);
    form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
}

function removePhoto() {
    if (photoPreview.value) URL.revokeObjectURL(photoPreview.value);
    photoPreview.value = null;
    form.photo = null;
    if (fileInput.value) fileInput.value.value = '';
}

onBeforeUnmount(() => {
    if (photoPreview.value) URL.revokeObjectURL(photoPreview.value);
});

// ── Sidebar navigation ───────────────────────────────────────────────────
const activeSection = ref('overview');
const contentEl = ref(null);

function goToSection(id) {
    activeSection.value = id;
    const target = document.getElementById(`pt-sec-${id}`);
    target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function closeDialog() {
    emit('update:modelValue', false);
}

function submit() {
    form.post(route('profile.store'), {
        preserveScroll: true,
        onSuccess: () => {
            ElNotification({
                title: 'Profile Created',
                message: 'Your profile has been saved successfully.',
                type: 'success',
                duration: 3200,
                offset: 84,
            });
            emit('success');
            closeDialog();
        },
    });
}
</script>

<template>
    <el-dialog
        :model-value="modelValue"
        width="min(900px, calc(100vw - 2rem))"
        align-center
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        :show-close="false"
        class="pt-modal"
        @update:model-value="emit('update:modelValue', $event)"
    >
        <template #header>
            <div class="pt-head">
                <h1 class="pt-head__title">My Profile</h1>
                <p class="pt-head__subtitle">Complete your profile to start trading on Bean Origin.</p>
            </div>
        </template>

        <div class="pt-shell">
            <!-- Left navigation -->
            <aside class="pt-nav">
                <nav class="pt-nav__list">
                    <button
                        type="button"
                        class="pt-nav__item"
                        :class="{ 'pt-nav__item--active': activeSection === 'overview' }"
                        @click="goToSection('overview')"
                    >
                        <el-icon :size="17"><User /></el-icon>
                        <span>Profile Overview</span>
                    </button>
                    <button
                        type="button"
                        class="pt-nav__item"
                        :class="{ 'pt-nav__item--active': activeSection === 'personal' }"
                        @click="goToSection('personal')"
                    >
                        <el-icon :size="17"><OfficeBuilding /></el-icon>
                        <span>Personal Information</span>
                    </button>
                </nav>
            </aside>

            <!-- Right content -->
            <div ref="contentEl" class="pt-content">
                <!-- Profile Overview -->
                <section id="pt-sec-overview" class="pt-card pt-card--tinted">
                    <div class="pt-overview">
                        <div class="pt-overview__identity">
                            <div class="pt-avatar">
                                <img v-if="photoPreview" :src="photoPreview" alt="" class="pt-avatar__img" />
                                <span v-else>{{ initials }}</span>
                            </div>
                            <div class="pt-overview__text">
                                <div class="pt-overview__name-row">
                                    <span class="pt-overview__name">{{ authUser?.name ?? '—' }}</span>
                                    <span v-if="authUser?.email_verified_at" class="pt-pill">
                                        <el-icon :size="12"><CircleCheck /></el-icon>
                                        Email Verified
                                    </span>
                                </div>
                                <div class="pt-overview__email">{{ authUser?.email ?? '—' }}</div>
                                <div class="pt-overview__hint">JPG, PNG or WEBP. Maximum 5&nbsp;MB</div>
                            </div>
                        </div>
                        <div class="pt-overview__actions">
                            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" hidden @change="onPhotoSelected" />
                            <button type="button" class="pt-btn-primary pt-btn-primary--sm" @click="pickPhoto">
                                <el-icon :size="14"><Picture /></el-icon>
                                <span>Upload Photo</span>
                            </button>
                            <button v-if="photoPreview" type="button" class="pt-btn-outline" @click="removePhoto">
                                Remove
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Personal Information -->
                <section id="pt-sec-personal" class="pt-card">
                    <div class="pt-card__head">
                        <h3 class="pt-card__title">Personal Information</h3>
                        <p class="pt-card__desc">Used to set up your trading credentials on Bean Origin.</p>
                    </div>

                    <!-- Account type -->
                    <div class="pt-type-card">
                        <span class="pt-field__label">Account Type</span>
                        <div class="pt-segmented">
                            <button type="button" class="pt-segmented__opt" :class="{ 'pt-segmented__opt--active': form.profile_type === 'personal' }" @click="form.profile_type = 'personal'">
                                <el-icon :size="15"><User /></el-icon>
                                <span>Individual</span>
                            </button>
                            <button type="button" class="pt-segmented__opt" :class="{ 'pt-segmented__opt--active': form.profile_type === 'business' }" @click="form.profile_type = 'business'">
                                <el-icon :size="15"><OfficeBuilding /></el-icon>
                                <span>Business</span>
                            </button>
                        </div>
                        <p class="pt-type-card__desc">{{ accountTypeDescription }}</p>
                    </div>

                    <div class="pt-grid">
                        <div class="pt-field">
                            <label class="pt-field__label">Date of Birth</label>
                            <el-date-picker v-model="form.date_of_birth" type="date" placeholder="Select date" value-format="YYYY-MM-DD" style="width: 100%" class="pt-input" :class="{ 'pt-input--error': form.errors.date_of_birth }" />
                            <span v-if="form.errors.date_of_birth" class="pt-field__error">{{ form.errors.date_of_birth }}</span>
                        </div>
                        <div class="pt-field">
                            <label class="pt-field__label">Gender</label>
                            <el-select v-model="form.gender" placeholder="Select gender" class="pt-input" :class="{ 'pt-input--error': form.errors.gender }">
                                <el-option label="Male" value="male" />
                                <el-option label="Female" value="female" />
                                <el-option label="Prefer not to say" value="prefer_not_to_say" />
                            </el-select>
                            <span v-if="form.errors.gender" class="pt-field__error">{{ form.errors.gender }}</span>
                        </div>
                        <div class="pt-field pt-field--span2">
                            <label class="pt-field__label">Address Line 1</label>
                            <el-input v-model="form.address_line_1" placeholder="Street, village, or plot" class="pt-input" :class="{ 'pt-input--error': form.errors.address_line_1 }" />
                            <span v-if="form.errors.address_line_1" class="pt-field__error">{{ form.errors.address_line_1 }}</span>
                        </div>
                        <div class="pt-field pt-field--span2">
                            <label class="pt-field__label">Address Line 2 <small>(optional)</small></label>
                            <el-input v-model="form.address_line_2" placeholder="Apartment, landmark, or extra details" class="pt-input" />
                        </div>
                        <div class="pt-field">
                            <label class="pt-field__label">City</label>
                            <el-input v-model="form.city" class="pt-input" :class="{ 'pt-input--error': form.errors.city }" />
                            <span v-if="form.errors.city" class="pt-field__error">{{ form.errors.city }}</span>
                        </div>
                        <div class="pt-field">
                            <label class="pt-field__label">State / District</label>
                            <el-input v-model="form.state" class="pt-input" :class="{ 'pt-input--error': form.errors.state }" />
                            <span v-if="form.errors.state" class="pt-field__error">{{ form.errors.state }}</span>
                        </div>
                        <div class="pt-field">
                            <label class="pt-field__label">Country</label>
                            <el-input v-model="form.country" class="pt-input" :class="{ 'pt-input--error': form.errors.country }" />
                            <span v-if="form.errors.country" class="pt-field__error">{{ form.errors.country }}</span>
                        </div>
                        <div class="pt-field">
                            <label class="pt-field__label">Postal Code <small>(optional)</small></label>
                            <el-input v-model="form.postal_code" class="pt-input" />
                        </div>
                        <div class="pt-field pt-field--span2">
                            <label class="pt-field__label">Short Bio <small>(optional)</small></label>
                            <el-input v-model="form.bio" type="textarea" :rows="3" placeholder="Tell us about yourself" class="pt-input" />
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <template #footer>
            <div class="pt-footer">
                <div class="pt-footer__status">
                    <template v-if="form.isDirty">
                        <span class="pt-footer__dot"></span>
                        <span>Unsaved changes</span>
                    </template>
                </div>
                <button type="button" class="pt-btn-primary" :disabled="form.processing" @click="submit">
                    <el-icon v-if="form.processing" class="is-loading" :size="14"><Loading /></el-icon>
                    <el-icon v-else :size="14"><Check /></el-icon>
                    <span>{{ form.processing ? 'Saving…' : 'Create Profile' }}</span>
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose — see EditMarketListingDialog.vue / OfferModal.vue for
   why the dialog shell rules live outside <style scoped>. */
.el-dialog.pt-modal {
    --el-dialog-padding-primary: 0;
    border-radius: var(--dp-card-radius, 18px);
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(18, 21, 22, 0.18);
}
.el-dialog.pt-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.pt-modal .el-dialog__body { padding: 0; }
.el-dialog.pt-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.pt-head { padding: 20px 28px; background: var(--dp-surface-container-lowest); border-bottom: 1px solid var(--dp-outline-variant); }
.pt-head__title { margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--dp-on-surface); letter-spacing: -0.01em; }
.pt-head__subtitle { margin: 2px 0 0; font-size: 0.75rem; color: var(--dp-on-surface-variant); }

.pt-shell { display: flex; height: min(560px, 66vh); overflow: hidden; }

.pt-nav { width: 220px; flex-shrink: 0; background: var(--dp-surface-container-low); border-right: 1px solid var(--dp-outline-variant); padding: 16px 12px; overflow-y: auto; }
.pt-nav__list { display: flex; flex-direction: column; gap: 4px; }
.pt-nav__item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: none; border-radius: 8px; background: transparent; color: var(--dp-on-surface-variant); font-size: 0.8125rem; font-weight: 700; font-family: var(--dp-font-sans); cursor: pointer; text-align: left; transition: background 0.15s ease, color 0.15s ease; }
.pt-nav__item:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.pt-nav__item--active { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.pt-nav__item--active:hover { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }

.pt-content { flex: 1; min-width: 0; overflow-y: auto; padding: 24px 28px; display: flex; flex-direction: column; gap: 20px; background: var(--dp-surface-container-low); }

.pt-card { padding: 20px 22px; border-radius: var(--dp-card-radius, 14px); background: var(--dp-surface-container-lowest); box-shadow: 0 1px 3px rgba(25, 28, 30, 0.05); }
.pt-card--tinted { background: var(--dp-surface-container-high); box-shadow: none; }
.pt-card__head { margin-bottom: 16px; }
.pt-card__title { margin: 0; font-size: 0.9375rem; font-weight: 800; color: var(--dp-on-surface); }
.pt-card__desc { margin: 2px 0 0; font-size: 0.75rem; color: var(--dp-on-surface-variant); }

.pt-overview { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.pt-overview__identity { display: flex; align-items: center; gap: 14px; min-width: 0; }
.pt-avatar { width: 64px; height: 64px; border-radius: 999px; background: var(--dp-primary); color: var(--dp-on-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.25rem; flex-shrink: 0; overflow: hidden; }
.pt-avatar__img { width: 100%; height: 100%; object-fit: cover; }
.pt-overview__text { min-width: 0; }
.pt-overview__name-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pt-overview__name { font-size: 1rem; font-weight: 800; color: var(--dp-on-surface); }
.pt-overview__email { font-size: 0.8125rem; color: var(--dp-on-surface-variant); margin-top: 1px; }
.pt-overview__hint { font-size: 0.6875rem; color: var(--dp-on-surface-variant); margin-top: 3px; }
.pt-overview__actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.pt-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; background: var(--dp-primary-container); color: var(--dp-on-primary-container); }

.pt-section-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-on-surface-variant); margin-bottom: 10px; }

.pt-type-card { padding: 0 0 18px; margin-bottom: 18px; border-bottom: 1px solid var(--dp-outline-variant); display: flex; flex-direction: column; gap: 10px; }
.pt-type-card__desc { margin: 0; font-size: 0.8125rem; color: var(--dp-on-surface-variant); line-height: 1.5; }

.pt-segmented { display: flex; padding: 3px; background: var(--dp-surface-container-low); border-radius: 10px; }
.pt-segmented__opt { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 12px; border: none; border-radius: 8px; background: transparent; color: var(--dp-on-surface-variant); cursor: pointer; font-size: 0.8125rem; font-weight: 700; font-family: var(--dp-font-sans); transition: background 0.15s ease, color 0.15s ease; }
.pt-segmented__opt:hover { color: var(--dp-on-surface); }
.pt-segmented__opt--active { background: var(--dp-primary); color: var(--dp-on-primary); }
.pt-segmented__opt--active:hover { color: var(--dp-on-primary); }

.pt-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 14px; }
.pt-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.pt-field--span2 { grid-column: span 2; }
.pt-field__label { font-size: 0.875rem; font-weight: 700; color: var(--dp-on-surface); }
.pt-field__label small { font-weight: 500; color: var(--dp-on-surface-variant); text-transform: none; }
.pt-field__error { font-size: 0.8125rem; font-weight: 600; color: var(--dp-error); }

.pt-input { width: 100%; }
.pt-input :deep(.el-input__wrapper),
.pt-input :deep(.el-select__wrapper),
.pt-input :deep(.el-textarea__inner) { border-radius: 8px; box-shadow: 0 0 0 1px var(--dp-outline-variant) inset; background: var(--dp-surface-container-low); }
.pt-input :deep(.el-input__wrapper:hover),
.pt-input :deep(.el-select__wrapper:hover),
.pt-input :deep(.el-textarea__inner:hover) { box-shadow: 0 0 0 1px var(--dp-outline) inset; }
.pt-input :deep(.el-input__wrapper.is-focus),
.pt-input :deep(.el-select__wrapper.is-focused),
.pt-input :deep(.el-textarea__inner:focus) { box-shadow: 0 0 0 1.5px var(--dp-primary) inset; }
.pt-input :deep(.el-input__inner),
.pt-input :deep(.el-select__selected-item),
.pt-input :deep(.el-textarea__inner) { color: var(--dp-on-surface); font-family: var(--dp-font-sans); }
.pt-input--error :deep(.el-input__wrapper),
.pt-input--error :deep(.el-select__wrapper),
.pt-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px var(--dp-error) inset; }

.pt-footer { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 16px 24px; background: var(--dp-surface-container-low); border-top: 1px solid var(--dp-outline-variant); }
.pt-footer__status { display: flex; align-items: center; gap: 8px; font-size: 0.75rem; font-weight: 600; color: #b45309; }
.pt-footer__dot { width: 7px; height: 7px; border-radius: 999px; background: #f59e0b; }

.pt-btn-primary { display: inline-flex; align-items: center; gap: 6px; background: var(--dp-primary); border: 1px solid transparent; color: var(--dp-on-primary); border-radius: 8px; font-size: 0.8125rem; font-weight: 700; padding: 9px 18px; cursor: pointer; transition: opacity 0.15s ease; font-family: var(--dp-font-sans); }
.pt-btn-primary:hover { opacity: 0.9; }
.pt-btn-primary:disabled { opacity: 0.6; cursor: default; }
.pt-btn-primary--sm { padding: 7px 14px; font-size: 0.75rem; }
.pt-btn-primary .is-loading { animation: pt-spin 1s linear infinite; }
@keyframes pt-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.pt-btn-outline { display: inline-flex; align-items: center; gap: 6px; background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface); border-radius: 8px; font-size: 0.75rem; font-weight: 700; padding: 7px 14px; cursor: pointer; transition: background 0.15s ease; font-family: var(--dp-font-sans); }
.pt-btn-outline:hover { background: var(--dp-surface-container-high); }

@media (max-width: 720px) {
    .pt-shell { flex-direction: column; height: auto; max-height: 72vh; }
    .pt-nav { width: 100%; border-right: none; border-bottom: 1px solid var(--dp-outline-variant); }
    .pt-nav__list { flex-direction: row; overflow-x: auto; }
}

@media (max-width: 640px) {
    .pt-grid { grid-template-columns: 1fr; }
    .pt-field--span2 { grid-column: span 1; }
    .pt-overview { flex-direction: column; align-items: flex-start; }
}
</style>
