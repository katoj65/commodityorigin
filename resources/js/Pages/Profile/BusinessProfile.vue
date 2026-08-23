<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Delete, Document, Edit, Grid, Location, Message,
    Link as LinkIcon, Money, OfficeBuilding, Phone, Plus, UserFilled,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import EditBusinessProfileDialog from '@/Components/Modals/EditBusinessProfileDialog.vue';
import AddBusinessMemberDialog from '@/Components/Modals/AddBusinessMemberDialog.vue';

const props = defineProps({
    businessProfile: { type: Object, default: null },
    businessTypeOptions: { type: Array, default: () => [] },
    businessMembers: { type: Array, default: () => [] },
});

/* ── Real display data — every value below comes straight from a genuine
   BusinessProfile / BusinessMember field; nothing here is invented to
   fill a layout slot. ────────────────────────────────────────────────── */
const business = computed(() => props.businessProfile || {});
const hasBusinessProfile = computed(() => Boolean(props.businessProfile));

const businessName = computed(() => business.value.business_name || 'Business Profile');

const businessTypeLabel = computed(() => {
    if (!business.value.business_type) return '';
    return String(business.value.business_type)
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
});

const industryLine = computed(() => business.value.industry || businessTypeLabel.value || '');

const fullAddress = computed(() => [
    business.value.address_line_1, business.value.address_line_2,
    business.value.city, business.value.state, business.value.country, business.value.postal_code,
].filter(Boolean).join(', '));

/* ── Edit / delete business profile ────────────────────────────────────── */
const editBusinessOpen = ref(false);
const deleteBusinessOpen = ref(false);
const deletingBusiness = ref(false);

function deleteBusinessProfile() {
    deletingBusiness.value = true;
    router.delete(route('profile.business.destroy'), {
        preserveScroll: true,
        onFinish: () => {
            deletingBusiness.value = false;
            deleteBusinessOpen.value = false;
        },
    });
}

/* ── Add / edit business member ────────────────────────────────────────── */
const memberDialogOpen = ref(false);
const memberBeingEdited = ref(null);

function openAddMemberDialog() {
    memberBeingEdited.value = null;
    memberDialogOpen.value = true;
}

function openEditMemberDialog(member) {
    memberBeingEdited.value = member;
    memberDialogOpen.value = true;
}

const removeMemberOpen = ref(false);
const removingMemberId = ref(null);
const memberToRemove = ref(null);

function openRemoveMemberDialog(member) {
    memberToRemove.value = member;
    removeMemberOpen.value = true;
}

function removeMember() {
    if (!memberToRemove.value) return;
    removingMemberId.value = memberToRemove.value.id;
    router.delete(route('business.members.destroy', memberToRemove.value.id), {
        preserveScroll: true,
        onFinish: () => {
            removingMemberId.value = null;
            removeMemberOpen.value = false;
            memberToRemove.value = null;
        },
    });
}

/* ── Leadership — the most recently registered member is featured, since
   there's no "founder"/seniority field to rank by; everyone else is
   reachable via "Manage Team". ──────────────────────────────────────── */
const featuredMember = computed(() => props.businessMembers[0] || null);
const otherMembersCount = computed(() => Math.max(props.businessMembers.length - 1, 0));

function memberInitials(member) {
    return ((member?.first_name?.[0] || '') + (member?.last_name?.[0] || '')).toUpperCase() || '?';
}
</script>

<template>
    <DesignPreviewLayout :title="businessName">
        <Head :title="businessName" />

        <div class="bp-page">
            <div class="bp-hero">
                <div class="bp-hero__text">
                    <h1 class="dp-display-md">Business Profile</h1>
                    <p class="bp-subtitle">Manage your company's core details, contact information, and leadership structure. This information is visible to verified trade partners.</p>
                </div>
                <div v-if="hasBusinessProfile" class="bp-hero__actions">
                    <button type="button" class="bp-btn bp-btn--outline" @click="editBusinessOpen = true">
                        <el-icon :size="14"><Edit /></el-icon> Edit
                    </button>
                    <button type="button" class="bp-btn bp-btn--danger-outline" @click="deleteBusinessOpen = true">
                        <el-icon :size="14"><Delete /></el-icon> Delete
                    </button>
                </div>
            </div>

            <div v-if="!hasBusinessProfile" class="bp-card bp-empty">
                <el-icon :size="22"><OfficeBuilding /></el-icon>
                <h2>No business profile on file yet</h2>
                <p>Complete your business details from onboarding to unlock the full trading dossier.</p>
            </div>

            <div v-else class="bp-stack">
                <!-- ── Identity ───────────────────────────────────────── -->
                <div class="bp-card bp-identity">
                    <div class="bp-identity__logo">
                        <img v-if="business.logo_url" :src="business.logo_url" :alt="businessName">
                        <el-icon v-else :size="34"><OfficeBuilding /></el-icon>
                    </div>
                    <div class="bp-identity__body">
                        <h2 class="bp-identity__name">{{ businessName }}</h2>
                        <p v-if="industryLine" class="bp-identity__industry">{{ industryLine }}</p>
                        <p class="bp-identity__description">{{ business.description || 'No business description on file yet.' }}</p>
                    </div>
                </div>

                <!-- ── Registration + Contact + Quick Actions ────────── -->
                <div class="bp-trio">
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <h2 class="bp-card-title"><el-icon><Document /></el-icon> Registration</h2>
                        </div>
                        <div class="bp-info-rows">
                            <div class="bp-info-row">
                                <span class="bp-info-row__icon"><el-icon :size="14"><Document /></el-icon></span>
                                <span class="bp-info-row__label">Reg #</span>
                                <span class="bp-info-row__value">{{ business.registration_number || '—' }}</span>
                            </div>
                            <div class="bp-info-row">
                                <span class="bp-info-row__icon"><el-icon :size="14"><Money /></el-icon></span>
                                <span class="bp-info-row__label">Tax ID</span>
                                <span class="bp-info-row__value">{{ business.tax_id || '—' }}</span>
                            </div>
                            <div class="bp-info-row">
                                <span class="bp-info-row__icon"><el-icon :size="14"><OfficeBuilding /></el-icon></span>
                                <span class="bp-info-row__label">Type</span>
                                <span class="bp-info-row__value">{{ businessTypeLabel || '—' }}</span>
                            </div>
                        </div>
                        <div class="bp-stat-tiles">
                            <div class="bp-stat-tile">
                                <strong>{{ business.employee_count ?? '—' }}</strong>
                                <span>Employees</span>
                            </div>
                            <div class="bp-stat-tile">
                                <strong>{{ business.year_established ?? '—' }}</strong>
                                <span>Est. Year</span>
                            </div>
                        </div>
                    </div>

                    <div class="bp-card">
                        <div class="bp-card-head">
                            <h2 class="bp-card-title"><el-icon><Phone /></el-icon> Contact Info</h2>
                        </div>
                        <div class="bp-contact-rows">
                            <a v-if="business.website" :href="business.website" target="_blank" rel="noopener" class="bp-contact-row bp-contact-row--link">
                                <span class="bp-contact-row__icon"><el-icon :size="16"><LinkIcon /></el-icon></span>
                                <div>
                                    <span class="bp-contact-row__label">Website</span>
                                    <span class="bp-contact-row__value">{{ business.website }}</span>
                                </div>
                            </a>
                            <div v-else class="bp-contact-row">
                                <span class="bp-contact-row__icon"><el-icon :size="16"><LinkIcon /></el-icon></span>
                                <div>
                                    <span class="bp-contact-row__label">Website</span>
                                    <span class="bp-contact-row__value">—</span>
                                </div>
                            </div>
                            <a v-if="business.contact_email" :href="`mailto:${business.contact_email}`" class="bp-contact-row bp-contact-row--link">
                                <span class="bp-contact-row__icon"><el-icon :size="16"><Message /></el-icon></span>
                                <div>
                                    <span class="bp-contact-row__label">Contact Email</span>
                                    <span class="bp-contact-row__value">{{ business.contact_email }}</span>
                                </div>
                            </a>
                            <div v-else class="bp-contact-row">
                                <span class="bp-contact-row__icon"><el-icon :size="16"><Message /></el-icon></span>
                                <div>
                                    <span class="bp-contact-row__label">Contact Email</span>
                                    <span class="bp-contact-row__value">—</span>
                                </div>
                            </div>
                            <a v-if="business.contact_phone" :href="`tel:${business.contact_phone}`" class="bp-contact-row bp-contact-row--link">
                                <span class="bp-contact-row__icon"><el-icon :size="16"><Phone /></el-icon></span>
                                <div>
                                    <span class="bp-contact-row__label">Contact Phone</span>
                                    <span class="bp-contact-row__value">{{ business.contact_phone }}</span>
                                </div>
                            </a>
                            <div v-else class="bp-contact-row">
                                <span class="bp-contact-row__icon"><el-icon :size="16"><Phone /></el-icon></span>
                                <div>
                                    <span class="bp-contact-row__label">Contact Phone</span>
                                    <span class="bp-contact-row__value">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bp-card">
                        <h2 class="bp-card-title">Quick Actions</h2>
                        <div class="bp-quick-actions">
                            <button type="button" class="bp-quick-action" @click="editBusinessOpen = true">
                                <span class="bp-quick-action__icon"><el-icon :size="16"><Edit /></el-icon></span>
                                <span class="bp-quick-action__label">Edit Information</span>
                            </button>
                            <Link :href="route('business.members.index')" class="bp-quick-action">
                                <span class="bp-quick-action__icon"><el-icon :size="16"><UserFilled /></el-icon></span>
                                <span class="bp-quick-action__label">Manage Team</span>
                            </Link>
                            <Link :href="route('businesses.index')" class="bp-quick-action">
                                <span class="bp-quick-action__icon"><el-icon :size="16"><Grid /></el-icon></span>
                                <span class="bp-quick-action__label">View in Directory</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- ── Leadership + Headquarters ──────────────────────── -->
                <div class="bp-pair">
                    <div class="bp-card">
                        <div class="bp-card-head">
                            <h2 class="bp-card-title"><el-icon><UserFilled /></el-icon> Business Leadership</h2>
                            <button type="button" class="bp-btn bp-btn--outline bp-btn--sm" @click="openAddMemberDialog">
                                <el-icon :size="13"><Plus /></el-icon> Add Member
                            </button>
                        </div>

                        <div v-if="featuredMember" class="bp-leader">
                            <div class="bp-leader__avatar">
                                <img v-if="featuredMember.photo_url" :src="featuredMember.photo_url" :alt="featuredMember.name">
                                <span v-else>{{ memberInitials(featuredMember) }}</span>
                            </div>
                            <div class="bp-leader__body">
                                <h3 class="bp-leader__name">{{ featuredMember.name }}</h3>
                                <span class="bp-leader__title">{{ [featuredMember.designation, featuredMember.position].filter(Boolean).join(' · ') || '—' }}</span>
                                <p v-if="featuredMember.bio" class="bp-leader__bio">{{ featuredMember.bio }}</p>
                                <div class="bp-leader__actions">
                                    <a v-if="featuredMember.email" :href="`mailto:${featuredMember.email}`" class="bp-icon-btn" title="Email"><el-icon :size="15"><Message /></el-icon></a>
                                    <a v-if="featuredMember.telephone" :href="`tel:${featuredMember.telephone}`" class="bp-icon-btn" title="Call"><el-icon :size="15"><Phone /></el-icon></a>
                                    <button type="button" class="bp-icon-btn" title="Edit member" @click="openEditMemberDialog(featuredMember)"><el-icon :size="15"><Edit /></el-icon></button>
                                    <button type="button" class="bp-icon-btn bp-icon-btn--danger" title="Remove member" @click="openRemoveMemberDialog(featuredMember)"><el-icon :size="15"><Delete /></el-icon></button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="bp-empty bp-empty--inline">
                            <el-icon :size="20"><UserFilled /></el-icon>
                            <p>No team members registered yet.</p>
                        </div>

                        <Link v-if="otherMembersCount > 0" :href="route('business.members.index')" class="bp-leader__more">
                            +{{ otherMembersCount }} more team member{{ otherMembersCount === 1 ? '' : 's' }} — Manage Team
                        </Link>
                    </div>

                    <div class="bp-card">
                        <div class="bp-card-head">
                            <h2 class="bp-card-title"><el-icon><Location /></el-icon> Headquarters</h2>
                        </div>
                        <div class="bp-hq">
                            <div class="bp-hq__icon"><el-icon :size="26"><Location /></el-icon></div>
                            <p class="bp-hq__address">{{ fullAddress || 'No address on file yet.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <EditBusinessProfileDialog
            v-model="editBusinessOpen"
            :business="businessProfile"
            :business-type-options="businessTypeOptions"
        />

        <AddBusinessMemberDialog v-model="memberDialogOpen" :member="memberBeingEdited" />

        <!-- ── Delete Business Profile modal ──────────────────────────── -->
        <el-dialog v-model="deleteBusinessOpen" width="min(440px, calc(100vw - 2rem))" align-center class="bp-modal bp-modal--danger">
            <template #header>
                <div class="bp-modal__head">
                    <div class="bp-modal__head-icon bp-modal__head-icon--danger"><el-icon :size="18"><Delete /></el-icon></div>
                    <div class="bp-modal__head-text">
                        <div class="bp-modal__eyebrow">Business Profile</div>
                        <div class="bp-modal__title">Delete Business Profile</div>
                    </div>
                </div>
            </template>
            <div class="bp-modal__body">
                <p class="bp-modal__confirm-text">Are you sure you want to delete your business profile? This removes all registered team members too, and cannot be undone.</p>
            </div>
            <template #footer>
                <div class="bp-modal__footer">
                    <button type="button" class="bp-btn bp-btn--outline" @click="deleteBusinessOpen = false">Cancel</button>
                    <button type="button" class="bp-btn bp-btn--danger" :disabled="deletingBusiness" @click="deleteBusinessProfile">
                        {{ deletingBusiness ? 'Deleting…' : 'Delete Business Profile' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Remove Member modal ────────────────────────────────────── -->
        <el-dialog v-model="removeMemberOpen" width="min(440px, calc(100vw - 2rem))" align-center class="bp-modal bp-modal--danger">
            <template #header>
                <div class="bp-modal__head">
                    <div class="bp-modal__head-icon bp-modal__head-icon--danger"><el-icon :size="18"><Delete /></el-icon></div>
                    <div class="bp-modal__head-text">
                        <div class="bp-modal__eyebrow">Business Leadership</div>
                        <div class="bp-modal__title">Remove Member</div>
                    </div>
                </div>
            </template>
            <div v-if="memberToRemove" class="bp-modal__body">
                <p class="bp-modal__confirm-text">Remove {{ memberToRemove.name }} from your business? This can't be undone.</p>
            </div>
            <template #footer>
                <div class="bp-modal__footer">
                    <button type="button" class="bp-btn bp-btn--outline" @click="removeMemberOpen = false">Cancel</button>
                    <button type="button" class="bp-btn bp-btn--danger" :disabled="removingMemberId === memberToRemove?.id" @click="removeMember">
                        {{ removingMemberId === memberToRemove?.id ? 'Removing…' : 'Remove Member' }}
                    </button>
                </div>
            </template>
        </el-dialog>
    </DesignPreviewLayout>
</template>

<style scoped>
.bp-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
    font-family: var(--dp-font-sans);
}

/* ── Hero ────────────────────────────────────────────────────────────── */
.bp-hero { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
.bp-hero__text { max-width: 640px; }
.bp-hero__text h1 { color: var(--dp-primary); }
.bp-subtitle { font-size: 14px; line-height: 1.6; color: var(--dp-on-surface-variant); margin: 8px 0 0; }
.bp-hero__actions { display: flex; gap: 10px; flex-shrink: 0; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.bp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 18px;
    border: none;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}
.bp-btn--outline { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); box-shadow: var(--dp-card-shadow); }
.bp-btn--outline:hover { background: var(--dp-surface-container-low); }
.bp-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.bp-btn--primary:disabled { opacity: 0.6; cursor: default; }
.bp-btn--danger { background: var(--dp-error); color: var(--dp-on-error); }
.bp-btn--danger:disabled { opacity: 0.6; cursor: default; }
.bp-btn--danger-outline { background: var(--dp-surface-container-lowest); color: var(--dp-error); box-shadow: var(--dp-card-shadow); }
.bp-btn--danger-outline:hover { background: var(--dp-error-container); }
.bp-btn--sm { height: 32px; padding: 0 14px; font-size: 11.5px; }
.bp-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.bp-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    border: none;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease;
}
.bp-icon-btn:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.bp-icon-btn--danger:hover { background: var(--dp-error-container); color: var(--dp-error); }
.bp-icon-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

/* ── Layout ──────────────────────────────────────────────────────────── */
.bp-stack { display: flex; flex-direction: column; gap: 20px; }
.bp-pair { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; align-items: stretch; }
.bp-pair > .bp-card { height: 100%; display: flex; flex-direction: column; }
.bp-trio { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; align-items: stretch; }
.bp-trio > .bp-card { height: 100%; display: flex; flex-direction: column; }

.bp-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    padding: 22px;
}
.bp-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--dp-primary);
    margin: 0 0 16px;
}
.bp-card-title .el-icon { color: var(--dp-outline); font-size: 15px; }
.bp-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.bp-card-head .bp-card-title { margin-bottom: 0; }

.bp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 40px 20px;
    text-align: center;
    color: var(--dp-outline);
}
.bp-empty h2 { margin: 4px 0 0; font-size: 15px; font-weight: 700; color: var(--dp-on-surface); }
.bp-empty p { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; max-width: 44ch; }
.bp-empty--inline { padding: 24px 12px; }

/* ── Identity ────────────────────────────────────────────────────────── */
.bp-identity { display: flex; gap: 20px; align-items: flex-start; }
.bp-identity__logo {
    width: 84px;
    height: 84px;
    border-radius: 18px;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}
.bp-identity__logo img { width: 100%; height: 100%; object-fit: contain; }
.bp-identity__body { min-width: 0; }
.bp-identity__name { margin: 0; font-size: 20px; font-weight: 800; color: var(--dp-on-surface); letter-spacing: -0.01em; }
.bp-identity__industry { margin: 6px 0 0; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-outline); }
.bp-identity__description { margin: 14px 0 0; font-size: 13.5px; line-height: 1.65; color: var(--dp-on-surface-variant); }

/* ── Registration ────────────────────────────────────────────────────── */
.bp-info-rows { display: flex; flex-direction: column; gap: 4px; }
.bp-info-row { display: flex; align-items: center; gap: 10px; padding: 8px 6px; border-radius: 9px; }
.bp-info-row__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    flex-shrink: 0;
}
.bp-info-row__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dp-outline); width: 84px; flex-shrink: 0; }
.bp-info-row__value { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }

.bp-stat-tiles { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
.bp-stat-tile {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 14px 8px;
    border-radius: 12px;
    background: var(--dp-surface-container-low);
    text-align: center;
}
.bp-stat-tile strong { font-size: 20px; font-weight: 800; color: var(--dp-primary); line-height: 1; }
.bp-stat-tile span { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-outline); }

/* ── Contact ─────────────────────────────────────────────────────────── */
.bp-contact-rows { display: flex; flex-direction: column; gap: 6px; }
.bp-contact-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 6px;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
}
.bp-contact-row--link { transition: background 0.12s ease; }
.bp-contact-row--link:hover { background: var(--dp-surface-container-low); }
.bp-contact-row__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    flex-shrink: 0;
}
.bp-contact-row--link:hover .bp-contact-row__icon { background: var(--dp-primary); color: var(--dp-on-primary); }
.bp-contact-row__label { display: block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-outline); }
.bp-contact-row__value { display: block; margin-top: 2px; font-size: 13px; font-weight: 700; color: var(--dp-on-surface); word-break: break-word; }

/* ── Quick actions ───────────────────────────────────────────────────── */
.bp-quick-actions { display: flex; flex-direction: column; gap: 6px; }
.bp-quick-action {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid transparent;
    background: none;
    color: var(--dp-on-surface);
    text-decoration: none;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.bp-quick-action:hover { background: var(--dp-surface-container-low); border-color: var(--dp-outline-variant); }
.bp-quick-action__icon { color: var(--dp-outline); display: inline-flex; }
.bp-quick-action:hover .bp-quick-action__icon { color: var(--dp-primary); }
.bp-quick-action__label { font-size: 13.5px; font-weight: 700; }

/* ── Leadership ──────────────────────────────────────────────────────── */
.bp-leader { display: flex; gap: 18px; align-items: flex-start; }
.bp-leader__avatar {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    flex-shrink: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--dp-secondary-container), var(--dp-secondary-fixed));
    color: var(--dp-on-secondary-container);
    font-size: 22px;
    font-weight: 800;
}
.bp-leader__avatar img { width: 100%; height: 100%; object-fit: cover; }
.bp-leader__body { min-width: 0; flex: 1; }
.bp-leader__name { margin: 0; font-size: 16px; font-weight: 800; color: var(--dp-on-surface); }
.bp-leader__title { display: block; margin-top: 4px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dp-secondary); }
.bp-leader__bio { margin: 12px 0 0; font-size: 13px; line-height: 1.6; color: var(--dp-on-surface-variant); }
.bp-leader__actions { display: flex; gap: 8px; margin-top: 14px; }
.bp-leader__more {
    display: block;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid color-mix(in srgb, var(--dp-outline-variant) 25%, transparent);
    font-size: 12.5px;
    font-weight: 700;
    color: var(--dp-primary);
    text-decoration: none;
}
.bp-leader__more:hover { text-decoration: underline; }

/* ── Headquarters ────────────────────────────────────────────────────── */
.bp-hq { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; padding: 24px 16px; border-radius: 14px; background: var(--dp-surface-container-low); flex: 1; text-align: center; }
.bp-hq__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--dp-primary-container);
    color: var(--dp-on-primary-container);
}
.bp-hq__address { margin: 0; font-size: 14px; font-weight: 700; line-height: 1.6; color: var(--dp-on-surface); max-width: 32ch; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .bp-trio { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 900px) {
    .bp-pair,
    .bp-trio { grid-template-columns: 1fr; }
    .bp-identity { flex-direction: column; align-items: flex-start; }
    .bp-leader { flex-direction: column; align-items: center; text-align: center; }
    .bp-leader__actions { justify-content: center; }
}

/* ── Modals — el-dialog teleports to <body>, outside .dp-shell, so
   --dp-* custom properties don't cascade in; literal hex from the same
   palette is used here, matching this app's other teleported dialogs. */
</style>

<style>
.el-dialog.bp-modal { border-radius: 18px; padding: 0; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
.el-dialog.bp-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.bp-modal .el-dialog__body { padding: 0; }
.el-dialog.bp-modal .el-dialog__footer { padding: 0; }

.bp-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #f3f4f6; }
.bp-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(39, 19, 16, 0.08); color: #271310; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.bp-modal__head-icon--danger { background: #fee2e2; color: #b91c1c; }
.bp-modal__head-text { flex: 1; min-width: 0; }
.bp-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #271310; margin-bottom: 1px; }
.bp-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -0.01em; }
.bp-modal__body { padding: 22px 24px; }
.bp-modal__confirm-text { margin: 0; font-size: 0.875rem; color: #374151; line-height: 1.6; }
.bp-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

.bp-modal--danger .bp-btn--danger { background: #ba1a1a; color: #fff; border-radius: 999px; }
.bp-modal .bp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 38px; padding: 0 18px; border: none; border-radius: 999px; font-size: 12.5px; font-weight: 700; cursor: pointer; }
.bp-modal .bp-btn--outline { background: #fff; border: 1px solid #e5e7eb; color: #111827; }
.bp-modal .bp-btn--outline:hover { background: #f8fafc; }
.bp-modal .bp-btn--danger:disabled { opacity: 0.6; cursor: default; }
</style>
