<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Calendar,
    Check,
    Close,
    DataAnalysis,
    Document,
    Edit,
    Grid,
    Link as LinkIcon,
    Location,
    Lock,
    MagicStick,
    Message,
    Money,
    OfficeBuilding,
    Phone,
    Plus,
    UserFilled,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import EditBusinessProfileDialog from '@/Components/Modals/EditBusinessProfileDialog.vue';
import AddBusinessMemberDialog from '@/Components/Modals/AddBusinessMemberDialog.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { resolveIcon } from '@/utils/icon';

const props = defineProps({
    sessions: {
        type: Array,
        default: () => [],
    },
    businessProfile: {
        type: Object,
        default: null,
    },
    businessTypeOptions: {
        type: Array,
        default: () => [],
    },
    businessMembers: {
        type: Array,
        default: () => [],
    },
});

const pageProps = usePage();

const editBusinessOpen = ref(false);
const addMemberOpen = ref(false);

const user = computed(() => pageProps.props.auth.user ?? {});
const business = computed(() => props.businessProfile ?? {});
const hasBusinessProfile = computed(() => Boolean(props.businessProfile));
const subscribedAgents = computed(() => pageProps.props.subscribedAgents ?? []);

/* ── Remove a registered member ──────────────────────────────────────── */
const removingMemberId = ref(null);
const removeMemberOpen = ref(false);
const pendingRemoveMember = ref(null);

function requestRemoveMember(member) {
    pendingRemoveMember.value = member;
    removeMemberOpen.value = true;
}

function confirmRemoveMember() {
    const member = pendingRemoveMember.value;
    if (!member) return;

    removingMemberId.value = member.id;
    router.delete(route('business.members.destroy', member.id), {
        preserveScroll: true,
        onFinish: () => { removingMemberId.value = null; },
    });
}

/* ── Unsubscribe from an agent ───────────────────────────────────────── */
const unsubscribingId = ref(null);
const unsubscribeOpen = ref(false);
const pendingUnsubscribe = ref(null);

function requestUnsubscribe(agent) {
    pendingUnsubscribe.value = agent;
    unsubscribeOpen.value = true;
}

function confirmUnsubscribe() {
    const agent = pendingUnsubscribe.value;
    if (!agent) return;

    unsubscribingId.value = agent.id;
    router.delete(route('agent.unsubscribe', agent.id), {
        preserveScroll: true,
        onFinish: () => { unsubscribingId.value = null; },
    });
}

const fullName = computed(() => business.value.business_name || user.value.name || 'Business Account');
const businessTypeLabel = computed(() =>
    String(business.value.business_type || 'Business')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase()),
);
const industryLabel = computed(() => business.value.industry || 'Coffee');
const locationLabel = computed(
    () => [business.value.city, business.value.state, business.value.country].filter(Boolean).join(', ') || 'Not set',
);
const fullAddress = computed(() =>
    [business.value.address_line_1, business.value.address_line_2, business.value.city, business.value.state, business.value.country, business.value.postal_code]
        .filter(Boolean)
        .join(', '),
);
const emailVerified = computed(() => Boolean(user.value.email_verified_at));
const twoFactorEnabled = computed(() => Boolean(user.value.two_factor_enabled));
const memberSince = computed(() => {
    const source = user.value.created_at ? new Date(user.value.created_at) : new Date();

    return Number.isNaN(source.getTime()) ? '2024' : String(source.getFullYear());
});

const ownerJoinedDate = computed(() => {
    if (!user.value.created_at) return 'Unknown';

    const source = new Date(user.value.created_at);
    if (Number.isNaN(source.getTime())) return 'Unknown';

    return source.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
});

const ownerInitials = computed(() => {
    const name = user.value.name || '';
    const parts = name.trim().split(/\s+/).filter(Boolean);

    return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || '?';
});

const activeSessionsCount = computed(() => Math.max(props.sessions.length, 1));

const completionPercentage = computed(() => {
    const fields = [
        business.value.business_name,
        business.value.business_type,
        business.value.industry,
        business.value.registration_number,
        business.value.tax_id,
        business.value.contact_email,
        business.value.contact_phone,
        business.value.address_line_1,
        business.value.city,
        business.value.state,
        business.value.country,
        business.value.description,
    ];
    const completed = fields.filter((value) => String(value || '').trim() !== '').length;

    return Math.round((completed / fields.length) * 100);
});

const detailCards = computed(() => [
    {
        label: 'Registration Number',
        value: business.value.registration_number || 'Not set',
        icon: OfficeBuilding,
    },
    {
        label: 'Tax ID',
        value: business.value.tax_id || 'Not set',
        icon: Money,
    },
    {
        label: 'Employees',
        value: business.value.employee_count ? String(business.value.employee_count) : 'Not set',
        icon: UserFilled,
    },
    {
        label: 'Year Established',
        value: business.value.year_established ? String(business.value.year_established) : 'Not set',
        icon: Calendar,
    },
]);

const securityItems = computed(() => [
    {
        label: 'Email Verification',
        value: emailVerified.value ? 'Verified' : 'Pending',
        note: user.value.email || 'Primary email missing',
        tone: emailVerified.value ? 'good' : 'warn',
        icon: Message,
    },
    {
        label: '2FA Status',
        value: twoFactorEnabled.value ? 'Enabled' : 'Recommended',
        note: twoFactorEnabled.value ? 'Authenticator linked to account' : 'Turn on stronger sign-in security',
        tone: twoFactorEnabled.value ? 'good' : 'warn',
        icon: Lock,
    },
    {
        label: 'Business Profile Quality',
        value: `${completionPercentage.value}%`,
        note: hasBusinessProfile.value ? 'Business details on file' : 'Business profile not yet completed',
        tone: completionPercentage.value >= 85 ? 'good' : 'neutral',
        icon: Check,
    },
]);

const statCards = computed(() => [
    {
        label: 'Profile Completion',
        value: `${completionPercentage.value}%`,
        note: completionPercentage.value >= 85 ? 'Ready for market workflows' : 'Needs more business detail',
        icon: OfficeBuilding,
        accent: false,
    },
    {
        label: 'Session Register',
        value: String(activeSessionsCount.value),
        note: activeSessionsCount.value > 1 ? 'Recent browser access recorded' : 'Single secure device',
        icon: DataAnalysis,
        accent: false,
    },
]);
</script>

<template>
    <DesignPreviewLayout title="Business Profile">
        <Head title="Business Profile" />

        <div class="profile-page">
            <section class="profile-topbar">
                <div class="profile-topbar__inner">
                    <div class="profile-topbar__copy">
                        <h1 class="profile-page__title">Business Profile</h1>
                        <p class="profile-page__subtitle">Member Since {{ memberSince }}</p>
                    </div>

                    <div class="profile-topbar__actions">
                        <button type="button" class="profile-edit-btn" @click="editBusinessOpen = true">
                            <el-icon><Edit /></el-icon>
                            <span>Edit Business Profile</span>
                        </button>
                    </div>
                </div>
            </section>

            <div class="profile-shell">
                <div v-if="!hasBusinessProfile" class="profile-card profile-empty-state">
                    <div class="profile-empty-state__icon"><el-icon><OfficeBuilding /></el-icon></div>
                    <h2>No business profile on file yet</h2>
                    <p>Complete your business details from onboarding to unlock the full trading dossier.</p>
                </div>

                <section class="profile-main-grid">
                    <div class="profile-column">
                        <section class="profile-card profile-card--identity">
                            <div class="profile-identity__media">
                                <div class="profile-avatar-frame">
                                    <img v-if="business.logo_url" :src="business.logo_url" :alt="fullName" class="profile-avatar profile-avatar--photo">
                                    <el-avatar v-else class="profile-avatar" shape="square">
                                        <el-icon><OfficeBuilding /></el-icon>
                                    </el-avatar>
                                </div>
                            </div>

                            <div class="profile-identity__body">
                                <h2 class="profile-identity__name">{{ fullName }}</h2>
                                <div class="profile-identity__role">{{ businessTypeLabel }}</div>

                                <div class="profile-contact-list">
                                    <div class="profile-contact-row">
                                        <el-icon><Message /></el-icon>
                                        <span>{{ business.contact_email || user.email || 'Email pending' }}</span>
                                    </div>
                                    <div class="profile-contact-row">
                                        <el-icon><Phone /></el-icon>
                                        <span>{{ business.contact_phone || 'Telephone pending' }}</span>
                                    </div>
                                    <div class="profile-contact-row">
                                        <el-icon><Location /></el-icon>
                                        <span>{{ locationLabel }}</span>
                                    </div>
                                    <div v-if="business.website" class="profile-contact-row">
                                        <el-icon><LinkIcon /></el-icon>
                                        <span>{{ business.website }}</span>
                                    </div>
                                </div>

                                <div class="profile-badge-list">
                                    <span class="profile-badge profile-badge--good">{{ industryLabel }}</span>
                                    <span class="profile-badge profile-badge--soft">{{ businessTypeLabel }}</span>
                                </div>
                            </div>
                        </section>

                        <section class="profile-card profile-card--security">
                            <div class="profile-card__head">
                                <div>
                                    <h2 class="profile-card__title">Security Protocol</h2>
                                </div>
                            </div>

                            <div class="profile-security-list">
                                <article v-for="item in securityItems" :key="item.label" class="profile-security-row">
                                    <div class="profile-security-row__icon" :class="`is-${item.tone}`">
                                        <el-icon><component :is="item.icon" /></el-icon>
                                    </div>
                                    <div class="profile-security-row__body">
                                        <div class="profile-security-row__label">{{ item.label }}</div>
                                        <div class="profile-security-row__note">{{ item.note }}</div>
                                    </div>
                                    <span class="profile-security-row__status" :class="`is-${item.tone}`">{{ item.value }}</span>
                                </article>
                            </div>
                        </section>
                    </div>

                    <div class="profile-content">
                        <section class="profile-stat-grid profile-stat-grid--business">
                            <article v-for="item in statCards" :key="item.label" class="profile-card profile-stat-card">
                                <div class="profile-stat-card__eyebrow">
                                    <el-icon><component :is="item.icon" /></el-icon>
                                    <span>{{ item.label }}</span>
                                </div>
                                <div class="profile-stat-card__value">{{ item.value }}</div>
                                <div class="profile-stat-card__note">{{ item.note }}</div>
                            </article>

                            <article v-for="item in detailCards" :key="item.label" class="profile-card profile-stat-card">
                                <div class="profile-stat-card__eyebrow">
                                    <el-icon><component :is="item.icon" /></el-icon>
                                    <span>{{ item.label }}</span>
                                </div>
                                <div class="profile-stat-card__value profile-stat-card__value--text">{{ item.value }}</div>
                            </article>

                            <article class="profile-card profile-stat-card profile-owner-card">
                                <div class="profile-stat-card__eyebrow">
                                    <el-icon><UserFilled /></el-icon>
                                    <span>Account Owner</span>
                                </div>
                                <div class="profile-owner-card__body">
                                    <span class="profile-owner-card__avatar">{{ ownerInitials }}</span>
                                    <div class="profile-owner-card__info">
                                        <div class="profile-owner-card__name">{{ user.name || 'Unknown' }}</div>
                                        <div class="profile-owner-card__meta">
                                            <span><el-icon :size="14"><Message /></el-icon> {{ user.email || 'Email pending' }}</span>
                                            <span v-if="user.telephone"><el-icon :size="14"><Phone /></el-icon> {{ user.telephone }}</span>
                                            <span><el-icon :size="14"><Calendar /></el-icon> Joined {{ ownerJoinedDate }}</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </section>

                        <div class="profile-row-pair">
                            <section class="profile-card profile-card--config">
                                <div class="profile-card__head">
                                    <div>
                                        <h2 class="profile-card__title"><el-icon><Document /></el-icon> About</h2>
                                    </div>
                                </div>

                                <p class="profile-about-text">{{ business.description || 'No business description on file yet.' }}</p>
                            </section>

                            <section class="profile-card profile-card--config">
                                <div class="profile-card__head">
                                    <div>
                                        <h2 class="profile-card__title"><el-icon><Location /></el-icon> Registered Address</h2>
                                    </div>
                                </div>

                                <div class="profile-address-block">
                                    {{ fullAddress || 'No address on file yet.' }}
                                </div>
                            </section>
                        </div>

                        <section class="profile-card profile-card--config">
                            <div class="profile-card__head">
                                <div>
                                    <h2 class="profile-card__title">Subscribed Agents</h2>
                                </div>
                            </div>

                            <div v-if="subscribedAgents.length" class="profile-agents-table-wrap">
                                <el-table :data="subscribedAgents" class="profile-agents-table" row-key="id">
                                    <el-table-column min-width="200">
                                        <template #header><span class="profile-agents-th"><el-icon><MagicStick /></el-icon> Agent</span></template>
                                        <template #default="{ row }">
                                            <div class="profile-agents-cell">
                                                <span class="profile-agents-cell__icon"><el-icon :size="17"><component :is="resolveIcon(row.icon)" /></el-icon></span>
                                                <span class="profile-agents-cell__name">{{ row.name }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>

                                    <el-table-column min-width="300">
                                        <template #header><span class="profile-agents-th"><el-icon><Grid /></el-icon> Functions</span></template>
                                        <template #default="{ row }">
                                            <div v-if="row.functions?.length" class="profile-agents-functions">
                                                <span v-for="fn in row.functions" :key="fn.id" class="profile-agents-chip">
                                                    <el-icon :size="14"><component :is="resolveIcon(fn.icon)" /></el-icon>
                                                    {{ fn.name }}
                                                </span>
                                            </div>
                                            <span v-else class="profile-agents-muted">No functions configured</span>
                                        </template>
                                    </el-table-column>

                                    <el-table-column width="70" align="right">
                                        <template #default="{ row }">
                                            <button
                                                type="button"
                                                class="profile-agents-unsub"
                                                :disabled="unsubscribingId === row.id"
                                                title="Unsubscribe"
                                                aria-label="Unsubscribe from agent"
                                                @click="requestUnsubscribe(row)"
                                            >
                                                <el-icon :size="16"><Close /></el-icon>
                                            </button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>

                            <div v-else class="profile-agents-empty">
                                <p>Not subscribed to any agents yet.</p>
                            </div>
                        </section>

                        <section class="profile-card profile-card--config">
                            <div class="profile-card__head">
                                <div>
                                    <h2 class="profile-card__title"><el-icon><UserFilled /></el-icon> Business Members</h2>
                                </div>
                                <div class="d-flex gap-2">
                                    <Link :href="route('business.members.index')" class="profile-edit-btn profile-edit-btn--sm">
                                        <el-icon><UserFilled /></el-icon>
                                        <span>Manage</span>
                                    </Link>
                                    <button type="button" class="profile-edit-btn profile-edit-btn--sm" @click="addMemberOpen = true">
                                        <el-icon><Plus /></el-icon>
                                        <span>Add Member</span>
                                    </button>
                                </div>
                            </div>

                            <div v-if="businessMembers.length" class="profile-agents-table-wrap">
                                <el-table :data="businessMembers" class="profile-agents-table" row-key="id">
                                    <el-table-column min-width="180">
                                        <template #header><span class="profile-agents-th"><el-icon><UserFilled /></el-icon> Member</span></template>
                                        <template #default="{ row }">
                                            <div class="profile-agents-cell">
                                                <span class="profile-agents-cell__icon"><el-icon :size="17"><UserFilled /></el-icon></span>
                                                <span class="profile-agents-cell__name">{{ row.name }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>

                                    <el-table-column min-width="160">
                                        <template #header><span class="profile-agents-th"><el-icon><Grid /></el-icon> Role</span></template>
                                        <template #default="{ row }">
                                            <div class="profile-member-role">
                                                <span class="profile-member-role__designation">{{ row.designation }}</span>
                                                <span class="profile-member-role__position">{{ row.position }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>

                                    <el-table-column min-width="200">
                                        <template #header><span class="profile-agents-th"><el-icon><Phone /></el-icon> Contact</span></template>
                                        <template #default="{ row }">
                                            <div class="profile-member-contact">
                                                <span>{{ row.telephone }}</span>
                                                <span v-if="row.email">{{ row.email }}</span>
                                                <span v-if="row.address">{{ row.address }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>

                                    <el-table-column width="70" align="right">
                                        <template #default="{ row }">
                                            <button
                                                type="button"
                                                class="profile-agents-unsub"
                                                :disabled="removingMemberId === row.id"
                                                title="Remove member"
                                                aria-label="Remove member"
                                                @click="requestRemoveMember(row)"
                                            >
                                                <el-icon :size="16"><Close /></el-icon>
                                            </button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>

                            <div v-else class="profile-agents-empty">
                                <p>No members registered yet.</p>
                            </div>
                        </section>
                    </div>
                </section>
            </div>
        </div>

        <EditBusinessProfileDialog
            v-model="editBusinessOpen"
            :business="businessProfile"
            :business-type-options="businessTypeOptions"
        />

        <ConfirmDialog
            v-model="unsubscribeOpen"
            title="Unsubscribe Agent"
            :message="`Unsubscribe from ${pendingUnsubscribe?.name ?? 'this agent'}? You'll lose access to its functions until you subscribe again.`"
            confirm-text="Unsubscribe"
            @confirm="confirmUnsubscribe"
        />

        <AddBusinessMemberDialog v-model="addMemberOpen" />

        <ConfirmDialog
            v-model="removeMemberOpen"
            title="Remove Member"
            :message="`Remove ${pendingRemoveMember?.name ?? 'this member'} from your business? This can't be undone.`"
            confirm-text="Remove"
            @confirm="confirmRemoveMember"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
.profile-page {
    min-height: 100%;
    background: var(--surface, #f7f9fb);
}

.profile-shell {
    max-width: 1280px;
    margin: 0 auto;
    padding: 24px 18px 28px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.profile-topbar {
    border-bottom: 1px solid var(--card-border);
    background: #ffffff;
    box-shadow: var(--card-shadow);
}

.profile-topbar__inner {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    max-width: 1280px;
    margin: 0 auto;
    padding: 18px;
}

.profile-topbar__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.profile-edit-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 16px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: linear-gradient(135deg, #145c42, #0d3d2c);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.profile-edit-btn:hover { opacity: 0.9; }
.profile-edit-btn :deep(.el-icon) { font-size: 14px; }

.profile-edit-btn--sm {
    min-height: 32px;
    padding: 0 12px;
    font-size: 11px;
    background: #fff;
    color: #145c42;
    border: 1px solid var(--card-border);
}
.profile-edit-btn--sm:hover { opacity: 1; background: #f0f5f3; }
.profile-edit-btn--sm :deep(.el-icon) { font-size: 13px; }

.profile-page__title {
    margin: 8px 0 0;
    color: #192f27;
    font-size: clamp(1.2rem, 1.5vw, 1.4rem);
    font-weight: 800;
    line-height: 1.1;
}

.profile-page__subtitle {
    margin: 8px 0 0;
    color: #71817a;
    font-size: 13px;
}

.profile-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    padding: 32px 18px;
}

.profile-empty-state__icon {
    display: grid;
    place-items: center;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #eef3f0;
    color: #145c42;
    font-size: 22px;
    margin-bottom: 6px;
}

.profile-empty-state h2 {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    color: #20362e;
}

.profile-empty-state p {
    margin: 0;
    color: #7b8a84;
    font-size: 13px;
    max-width: 48ch;
}

.profile-main-grid {
    display: grid;
    grid-template-columns: 300px minmax(0, 1fr);
    gap: 18px;
    align-items: start;
}

.profile-column,
.profile-content {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.profile-card {
    border: 1px solid var(--card-border);
    border-radius: 8px;
    background: #ffffff;
    box-shadow: var(--card-shadow);
}

.profile-card--identity,
.profile-card--security,
.profile-card--config,
.profile-stat-card {
    padding: 18px;
}

.profile-card--identity {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.profile-identity__media {
    display: flex;
    justify-content: center;
    width: 100%;
    margin-bottom: 18px;
}

.profile-avatar-frame {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 156px;
    height: 156px;
    padding: 0;
    border-radius: 999px;
    background: transparent;
}

.profile-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    border-radius: 999px;
    border: 0;
    background: #f1f5f3;
    color: #90a49b;
    font-size: 38px;
}

.profile-avatar--photo {
    display: block;
    object-fit: cover;
}

.profile-identity__body {
    width: 100%;
}

.profile-identity__name {
    margin: 0;
    color: #20362e;
    font-size: 1.15rem;
    font-weight: 800;
    line-height: 1.15;
}

.profile-identity__role {
    margin-top: 8px;
    color: #406256;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.profile-row-pair {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    align-items: stretch;
}

.profile-row-pair .profile-card {
    display: flex;
    flex-direction: column;
}

.profile-about-text {
    margin: 16px 0 0;
    color: #425a51;
    font-size: 13px;
    line-height: 1.7;
    white-space: pre-line;
}

.profile-contact-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 18px;
    align-items: center;
}

.profile-contact-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    min-height: 38px;
    padding: 0 12px;
    border: 1px solid #eff3f1;
    border-radius: 8px;
    background: #fbfcfb;
    color: #425a51;
    font-size: 12px;
    line-height: 1.5;
}

.profile-contact-row :deep(svg) {
    color: #145c42;
    font-size: 15px;
}

.profile-badge-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-top: 18px;
}

.profile-badge {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0 10px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}

.profile-badge--good {
    background: #dbf0e5;
    color: #145c42;
}

.profile-badge--soft {
    background: #f3efe2;
    color: #8c6a2e;
}

.profile-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.profile-card__title {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin: 0;
    color: #233a31;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.profile-card__title :deep(.el-icon) { color: #145c42; font-size: 14px; }

.profile-security-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-top: 16px;
}

.profile-security-row {
    display: grid;
    grid-template-columns: 40px minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
}

.profile-security-row__icon {
    display: grid;
    place-items: center;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #eef3f0;
    color: #61776d;
    font-size: 18px;
}

.profile-security-row__icon.is-good {
    background: #def2e8;
    color: #145c42;
}

.profile-security-row__icon.is-warn {
    background: #fff0dd;
    color: #b67729;
}

.profile-security-row__label {
    color: #223932;
    font-size: 12px;
    font-weight: 700;
}

.profile-security-row__note {
    margin-top: 4px;
    color: #7b8a84;
    font-size: 11px;
    line-height: 1.45;
}

.profile-security-row__status {
    color: #637771;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.profile-security-row__status.is-good {
    color: #145c42;
}

.profile-security-row__status.is-warn {
    color: #b67729;
}

.profile-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.profile-stat-card__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #7d8c86;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.profile-stat-card__eyebrow :deep(.el-icon) { font-size: 13px; }

.profile-stat-card__value {
    margin-top: 18px;
    color: #182f26;
    font-size: clamp(1.3rem, 1.6vw, 1.6rem);
    font-weight: 800;
    line-height: 1;
}

.profile-stat-card__value--text {
    font-size: 1rem;
    word-break: break-word;
}

.profile-stat-card__note {
    margin-top: 10px;
    color: #71807a;
    font-size: 12px;
    line-height: 1.5;
}

.profile-owner-card {
    grid-column: span 2;
}

.profile-owner-card__body {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 16px;
}

.profile-owner-card__avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #eef3f0;
    color: #145c42;
    font-size: 14px;
    font-weight: 800;
    flex-shrink: 0;
}

.profile-owner-card__info { min-width: 0; }

.profile-owner-card__name {
    color: #182f26;
    font-size: 0.9375rem;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.profile-owner-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 14px;
    margin-top: 6px;
}

.profile-owner-card__meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #71807a;
    font-size: 11.5px;
}

.profile-owner-card__meta :deep(.el-icon) { color: #9ca8a2; }

.profile-address-block {
    margin-top: 16px;
    padding: 14px 16px;
    border: 1px solid var(--card-border);
    border-radius: 8px;
    background: #fafcfb;
    color: #27443a;
    font-size: 13px;
    line-height: 1.6;
}

/* ── Subscribed Agents table ─────────────────────────────────────────── */
.profile-agents-table-wrap {
    margin-top: 16px;
    border: 1px solid var(--card-border);
    border-radius: 8px;
    overflow: hidden;
}

.profile-agents-table {
    --el-table-border-color: var(--card-border);
    --el-table-header-bg-color: #fafcfb;
    --el-table-header-text-color: #71807a;
    font-family: 'Manrope', system-ui, sans-serif;
}

.profile-agents-table :deep(.el-table__header) th {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.profile-agents-table :deep(.el-table__cell) { padding: 12px 0; }
.profile-agents-table :deep(.el-table__inner-wrapper::before) { display: none; }
.profile-agents-table :deep(.el-table__header-wrapper th:first-child .cell),
.profile-agents-table :deep(.el-table__body-wrapper td:first-child .cell) { padding-left: 16px; }
.profile-agents-table :deep(.el-table__header-wrapper th:last-child .cell),
.profile-agents-table :deep(.el-table__body-wrapper td:last-child .cell) { padding-right: 16px; }

.profile-agents-th { display: inline-flex; align-items: center; gap: 6px; }
.profile-agents-th :deep(.el-icon) { font-size: 15px; color: #9ca8a2; }

.profile-agents-cell { display: flex; align-items: center; gap: 10px; }
.profile-agents-cell__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #eef3f0;
    color: #145c42;
    flex-shrink: 0;
}
.profile-agents-cell__name { font-size: 13px; font-weight: 700; color: #20362e; }

.profile-agents-functions { display: flex; flex-wrap: wrap; gap: 6px; }
.profile-agents-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    border-radius: 999px;
    background: #f3efe2;
    color: #8c6a2e;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.profile-agents-muted { color: #a1aca5; font-size: 12px; font-style: italic; }

.profile-member-role {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.profile-member-role__designation { font-size: 13px; font-weight: 700; color: #20362e; }
.profile-member-role__position { font-size: 11.5px; color: #7b8a84; }

.profile-member-contact {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 12px;
    color: #425a51;
}

.profile-agents-unsub {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: 1px solid var(--card-border);
    background: #fff;
    color: #b45252;
    cursor: pointer;
    transition: background 0.12s ease, border-color 0.12s ease;
}
.profile-agents-unsub:hover:not(:disabled) { background: #fef2f2; border-color: #fecaca; }
.profile-agents-unsub:disabled { opacity: 0.5; cursor: default; }

.profile-agents-empty {
    margin-top: 16px;
    padding: 24px 16px;
    border: 1px dashed var(--card-border);
    border-radius: 8px;
    text-align: center;
}
.profile-agents-empty p {
    margin: 0;
    color: #7b8a84;
    font-size: 13px;
}

@media (max-width: 1200px) {
    .profile-main-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .profile-column {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 18px;
    }
}

@media (max-width: 980px) {
    .profile-stat-grid,
    .profile-column {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .profile-shell {
        padding: 16px 12px 24px;
    }

    .profile-topbar__inner {
        flex-direction: column;
        padding: 14px 12px;
    }

    .profile-stat-grid,
    .profile-column,
    .profile-row-pair {
        grid-template-columns: minmax(0, 1fr);
    }

    .profile-security-row {
        grid-template-columns: 40px minmax(0, 1fr);
    }

    .profile-security-row__status {
        grid-column: 2;
    }
}
</style>
