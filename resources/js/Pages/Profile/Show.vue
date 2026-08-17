<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    Check,
    DataAnalysis,
    Edit,
    Location,
    Lock,
    Message,
    Monitor,
    Phone,
    Setting,
    User,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    sessions: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const user = computed(() => page.props.auth.user ?? {});
const profile = computed(() => user.value.profile ?? {});

/* ── Settlement currency ─────────────────────────────────────────────── */
const currencyOptions = computed(() => page.props.currencies ?? []);
const currentCurrency = computed(() => currencyOptions.value.find((c) => c.code === user.value.currency_code) ?? null);

const currencyForm = useForm({ currency_code: user.value.currency_code || '' });

function submitCurrency(code) {
    if (!code || code === user.value.currency_code) return;

    currencyForm.currency_code = code;
    currencyForm.post(route('profile.currency'), { preserveScroll: true });
}

const fullName = computed(() => user.value.name || 'Profile Owner');
const roleLabel = computed(() =>
    String(user.value.role || 'Lead Procurement Officer')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase()),
);
const emailVerified = computed(() => Boolean(user.value.email_verified_at));
const twoFactorEnabled = computed(() => Boolean(user.value.two_factor_enabled));
const locationLabel = computed(() =>
    [profile.value.city, profile.value.state, profile.value.country].filter(Boolean).join(', ') || 'Kampala, Uganda',
);
const birthLabel = computed(() => {
    if (!profile.value.date_of_birth) {
        return 'Not set';
    }

    return new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(profile.value.date_of_birth));
});
const bioText = computed(
    () => profile.value.bio || 'Lead profile for origin operations, account verification, and market-facing identity.',
);
const memberSince = computed(() => {
    const source = user.value.created_at ? new Date(user.value.created_at) : new Date();

    return Number.isNaN(source.getTime()) ? '2024' : String(source.getFullYear());
});
const accountTag = computed(() => (emailVerified.value ? 'Institutional Grade' : 'Pending Verification'));
const profileTier = computed(() => (completionPercentage.value >= 85 ? 'Gold Tier Member' : 'Profile In Progress'));
const strategicLabel = computed(() => (twoFactorEnabled.value ? 'Trusted Access' : 'Security Review'));

const completionPercentage = computed(() => {
    const fields = [
        user.value.first_name,
        user.value.last_name,
        user.value.email,
        user.value.telephone,
        user.value.role,
        profile.value.bio,
        profile.value.date_of_birth,
        profile.value.gender,
        profile.value.address_line_1,
        profile.value.city,
        profile.value.state,
        profile.value.country,
    ];
    const completed = fields.filter((value) => String(value || '').trim() !== '').length;

    return Math.round((completed / fields.length) * 100);
});

const trustIndex = computed(() => {
    let score = 52;

    if (emailVerified.value) {
        score += 18;
    }

    if (twoFactorEnabled.value) {
        score += 18;
    }

    score += Math.round(completionPercentage.value * 0.12);

    return Math.min(score, 100).toFixed(1);
});

const activeSessionsCount = computed(() => Math.max(props.sessions.length, 1));

const statCards = computed(() => [
    {
        label: 'Profile Completion',
        value: `${completionPercentage.value}%`,
        note: completionPercentage.value >= 85 ? 'Ready for market workflows' : 'Needs more profile detail',
        icon: User,
        accent: false,
    },
    {
        label: 'Trust Score Index',
        value: trustIndex.value,
        suffix: '/100',
        note: emailVerified.value ? 'Verified account signal' : 'Verification in progress',
        icon: Lock,
        accent: true,
    },
    {
        label: 'Session Register',
        value: String(activeSessionsCount.value),
        note: activeSessionsCount.value > 1 ? 'Recent browser access recorded' : 'Single secure device',
        icon: Monitor,
        accent: false,
    },
    {
        label: 'Market Exposure',
        value: emailVerified.value ? 'Active' : 'Pending',
        note: twoFactorEnabled.value ? 'Protected access enabled' : '2FA still inactive',
        icon: DataAnalysis,
        accent: false,
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
        label: 'Profile Quality',
        value: `${completionPercentage.value}%`,
        note: birthLabel.value === 'Not set' ? 'Profile details still missing' : `Date of birth on file · ${birthLabel.value}`,
        tone: completionPercentage.value >= 85 ? 'good' : 'neutral',
        icon: Check,
    },
]);

const configCards = computed(() => [
    {
        label: 'Notification Frequency',
        value: 'Real-time Alerts',
        note: activeSessionsCount.value > 1 ? 'Monitoring multiple recent devices' : 'Monitoring this secure device',
        kind: 'toggle',
        enabled: true,
    },
    {
        label: 'Base Settlement Currency',
        value: currentCurrency.value ? currentCurrency.value.code : 'Not set',
        note: currentCurrency.value
            ? `${currentCurrency.value.symbol} · ${currentCurrency.value.name}`
            : `Choose from ${currencyOptions.value.length} available currencies`,
        kind: 'currency',
    },
    {
        label: 'Market Reports',
        value: emailVerified.value ? 'Subscribed' : 'Limited Access',
        note: emailVerified.value ? 'Weekly reporting enabled' : 'Complete verification to unlock',
        kind: 'status',
        enabled: emailVerified.value,
    },
]);

const formatLedgerStamp = (input, fallbackHour) => {
    const date = input ? new Date(input) : new Date();

    if (Number.isNaN(date.getTime())) {
        return `Apr 16, 2026 · ${fallbackHour}`;
    }

    return new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).format(date);
};

const ledgerRows = computed(() => [
    {
        ref: `PRF-${String(user.value.id || 24).padStart(3, '0')}-MAIL`,
        domain: 'Identity Audit',
        timestamp: formatLedgerStamp(user.value.email_verified_at, '09:15'),
        outcome: emailVerified.value ? 'Verified' : 'Pending',
        amount: user.value.email || 'Primary email',
        tone: emailVerified.value ? 'good' : 'warn',
    },
    {
        ref: `SEC-${String(user.value.id || 24).padStart(3, '0')}-2FA`,
        domain: 'Security Protocol',
        timestamp: formatLedgerStamp(user.value.updated_at, '11:30'),
        outcome: twoFactorEnabled.value ? 'Finalized' : 'Action Needed',
        amount: twoFactorEnabled.value ? 'Authenticator linked' : 'Enable 2FA',
        tone: twoFactorEnabled.value ? 'good' : 'warn',
    },
    {
        ref: `SES-${String(user.value.id || 24).padStart(3, '0')}-WEB`,
        domain: 'Session Monitor',
        timestamp: formatLedgerStamp(user.value.updated_at || user.value.created_at, '14:22'),
        outcome: activeSessionsCount.value > 1 ? 'Tracked' : 'Stable',
        amount: `${activeSessionsCount.value} browser session${activeSessionsCount.value === 1 ? '' : 's'}`,
        tone: activeSessionsCount.value > 1 ? 'neutral' : 'good',
    },
    {
        ref: `CFG-${String(user.value.id || 24).padStart(3, '0')}-BIO`,
        domain: 'Profile Configuration',
        timestamp: formatLedgerStamp(user.value.created_at, '17:45'),
        outcome: completionPercentage.value >= 85 ? 'Finalized' : 'In Review',
        amount: `${completionPercentage.value}% completion`,
        tone: completionPercentage.value >= 85 ? 'good' : 'neutral',
    },
]);

const goToLedger = () => {
    document.getElementById('profile-ledger')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};
</script>

<template>
    <AppLayout title="Profile" full-width flush>
        <Head title="Profile" />

        <div class="profile-page">
            <section class="profile-topbar">
                <div class="profile-topbar__inner">
                    <div class="profile-topbar__copy">
                        <h1 class="profile-page__title">User Profile</h1>
                        <p class="profile-page__subtitle"> Member Since {{ memberSince }}</p>
                    </div>

                    <div class="profile-topbar__actions">
                        <el-button-group class="profile-action-group">
                            <el-button class="profile-group-button" type="default">
                                <el-icon><Setting /></el-icon>
                                <span>Settings</span>
                            </el-button>
                            <el-button class="profile-group-button" type="default" @click="goToLedger">
                                <el-icon><Edit /></el-icon>
                                <span>Edit Profile</span>
                            </el-button>
                        </el-button-group>
                    </div>
                </div>
            </section>

            <div class="profile-shell">
                <section class="profile-main-grid">
                    <div class="profile-column">
                        <section id="profile-identity" class="profile-card profile-card--identity">
                            <div class="profile-identity__media">
                                <div class="profile-avatar-frame">
                                    <el-avatar
                                        class="profile-avatar"
                                        shape="square"
                                    >
                                        <el-icon><User /></el-icon>
                                    </el-avatar>
                                </div>
                            </div>

                            <div class="profile-identity__body">
                                <h2 class="profile-identity__name">{{ fullName }}</h2>
                                <div class="profile-identity__role">{{ roleLabel }}</div>
                                <p class="profile-identity__bio">{{ bioText }}</p>

                                <div class="profile-contact-list">
                                    <div class="profile-contact-row">
                                        <el-icon><Message /></el-icon>
                                        <span>{{ user.email || 'Email pending' }}</span>
                                    </div>
                                    <div class="profile-contact-row">
                                        <el-icon><Phone /></el-icon>
                                        <span>{{ user.telephone || 'Telephone pending' }}</span>
                                    </div>
                                    <div class="profile-contact-row">
                                        <el-icon><Location /></el-icon>
                                        <span>{{ locationLabel }}</span>
                                    </div>
                                </div>

                                <div class="profile-badge-list">
                                    <span class="profile-badge profile-badge--good">{{ profileTier }}</span>
                                    <span class="profile-badge profile-badge--soft">{{ strategicLabel }}</span>
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
                        <section class="profile-stat-grid">
                            <article
                                v-for="item in statCards"
                                :key="item.label"
                                class="profile-card profile-stat-card"
                            >
                                <div class="profile-stat-card__eyebrow">
                                    <el-icon><component :is="item.icon" /></el-icon>
                                    <span>{{ item.label }}</span>
                                </div>
                                <div class="profile-stat-card__value">
                                    {{ item.value }}<small v-if="item.suffix">{{ item.suffix }}</small>
                                </div>
                                <div v-if="item.accent" class="profile-stat-card__meter">
                                    <span :style="{ width: `${Math.min(Number(trustIndex), 100)}%` }"></span>
                                </div>
                                <div class="profile-stat-card__note">{{ item.note }}</div>
                            </article>
                        </section>

                        <section class="profile-card profile-card--config">
                            <div class="profile-card__head">
                                <div>
                                    <h2 class="profile-card__title">Environment Configuration</h2>
                                </div>
                            </div>

                            <div class="profile-config-grid">
                                <article v-for="item in configCards" :key="item.label" class="profile-config-card">
                                    <div class="profile-config-card__label">{{ item.label }}</div>

                                    <div v-if="item.kind === 'toggle'" class="profile-config-card__toggle">
                                        <span>{{ item.value }}</span>
                                        <span class="profile-toggle" :class="{ 'is-on': item.enabled }"></span>
                                    </div>

                                    <div v-else-if="item.kind === 'currency'" class="profile-config-card__currency">
                                        <el-select
                                            v-model="currencyForm.currency_code"
                                            filterable
                                            placeholder="Select currency"
                                            :disabled="currencyForm.processing"
                                            class="profile-currency-select"
                                            @change="submitCurrency"
                                        >
                                            <el-option
                                                v-for="option in currencyOptions"
                                                :key="option.code"
                                                :label="`${option.code} — ${option.name}`"
                                                :value="option.code"
                                            >
                                                <span class="profile-currency-option">
                                                    <span class="profile-currency-option__code">{{ option.code }}</span>
                                                    <span class="profile-currency-option__name">{{ option.name }}</span>
                                                    <span class="profile-currency-option__symbol">{{ option.symbol }}</span>
                                                </span>
                                            </el-option>
                                        </el-select>
                                    </div>

                                    <div v-else class="profile-config-card__status">
                                        <span>{{ item.value }}</span>
                                        <el-icon v-if="item.enabled"><Check /></el-icon>
                                    </div>

                                    <div class="profile-config-card__note">{{ item.note }}</div>
                                </article>
                            </div>
                        </section>

                        <section id="profile-ledger" class="profile-card profile-card--ledger">
                            <div class="profile-card__head">
                                <div>
                                    <h2 class="profile-card__title">Audit Activity Ledger</h2>
                                </div>
                                <span class="profile-card__action">View Detailed Logs</span>
                            </div>

                            <div class="profile-ledger-wrap">
                                <table class="profile-ledger-table">
                                    <thead>
                                        <tr>
                                            <th>Event Reference</th>
                                            <th>Domain</th>
                                            <th>Timestamp</th>
                                            <th>Outcome</th>
                                            <th>Magnitude</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in ledgerRows" :key="row.ref">
                                            <td>{{ row.ref }}</td>
                                            <td><span class="profile-domain-pill">{{ row.domain }}</span></td>
                                            <td>{{ row.timestamp }}</td>
                                            <td>
                                                <span class="profile-outcome" :class="`is-${row.tone}`">
                                                    <span class="profile-outcome__dot"></span>
                                                    {{ row.outcome }}
                                                </span>
                                            </td>
                                            <td>{{ row.amount }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
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
}

/* Edge-to-edge: flush with the top, left, and right of the page — no
   shell padding, no border-radius, just a bottom border like a bar. */
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

.profile-kicker {
    color: #1f6e50;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

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

.profile-topbar__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.profile-action-group {
    display: inline-flex;
}

.profile-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.profile-button--ghost {
    border: 1px solid #e8eeeb;
    background: #ffffff;
    color: #27443a;
}

.profile-button--primary {
    border: 1px solid #145c42;
    background: #145c42;
    color: #ffffff;
}

.profile-group-button {
    min-height: 40px;
    padding: 0 16px;
    border-radius: 0;
    font-size: 12px;
    font-weight: 700;
}

.profile-group-button :deep(.el-icon) {
    margin-right: 8px;
}

.profile-group-button:first-child {
    border-top-left-radius: 6px;
    border-bottom-left-radius: 6px;
}

.profile-group-button:last-child {
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
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
.profile-card--ledger,
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

.profile-identity__bio {
    max-width: 24ch;
    margin: 10px auto 0;
    color: #73837c;
    font-size: 12px;
    line-height: 1.6;
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
    margin: 0;
    color: #233a31;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.profile-card__action {
    color: #45695c;
    font-size: 12px;
    font-weight: 700;
}

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

.profile-stat-card__value {
    margin-top: 18px;
    color: #182f26;
    font-size: clamp(1.3rem, 1.6vw, 1.6rem);
    font-weight: 800;
    line-height: 1;
}

.profile-stat-card__value small {
    color: #80908a;
    font-size: 1rem;
    font-weight: 700;
}

.profile-stat-card__meter {
    width: 100%;
    height: 5px;
    margin-top: 16px;
    border-radius: 999px;
    background: #e5ece8;
    overflow: hidden;
}

.profile-stat-card__meter span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: #179264;
}

.profile-stat-card__note {
    margin-top: 10px;
    color: #71807a;
    font-size: 12px;
    line-height: 1.5;
}

.profile-config-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 18px;
}

.profile-config-card {
    min-height: 120px;
    padding: 16px;
    border: 1px solid var(--card-border);
    border-radius: 8px;
    background: #fafcfb;
}

.profile-config-card__label {
    color: #586c63;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.profile-config-card__toggle,
.profile-config-card__status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 16px;
    color: #213730;
    font-size: 14px;
    font-weight: 700;
}

.profile-toggle {
    position: relative;
    flex-shrink: 0;
    width: 42px;
    height: 24px;
    border-radius: 999px;
    background: #ced8d3;
}

.profile-toggle::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ffffff;
    transition: transform 0.2s ease;
}

.profile-toggle.is-on {
    background: #145c42;
}

.profile-toggle.is-on::after {
    transform: translateX(18px);
}

.profile-config-card__currency {
    margin-top: 16px;
}

.profile-currency-select {
    width: 100%;
}

.profile-currency-select :deep(.el-select__wrapper) {
    border-radius: 8px;
    box-shadow: 0 0 0 1px #d8e2dc inset;
    background: #ffffff;
    min-height: 36px;
}

.profile-currency-select :deep(.el-select__wrapper.is-focused) {
    box-shadow: 0 0 0 1.5px #145c42 inset;
}

.profile-currency-option {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.profile-currency-option__code {
    font-weight: 700;
    color: #20362e;
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    flex-shrink: 0;
}

.profile-currency-option__name {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #586c63;
}

.profile-currency-option__symbol {
    flex-shrink: 0;
    color: #9ca8a2;
    font-size: 11px;
}

.profile-config-card__note {
    margin-top: 12px;
    color: #7d8c86;
    font-size: 11px;
    line-height: 1.5;
}

.profile-ledger-wrap {
    margin-top: 18px;
    overflow-x: auto;
}

.profile-ledger-table {
    width: 100%;
    min-width: 760px;
    border-collapse: collapse;
}

.profile-ledger-table th,
.profile-ledger-table td {
    padding: 14px 12px;
    border-bottom: 1px solid #edf1ee;
    text-align: left;
}

.profile-ledger-table th {
    color: #61756c;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}

.profile-ledger-table td {
    color: #27443a;
    font-size: 12px;
}

.profile-ledger-table tbody tr:last-child td {
    border-bottom: 0;
}

.profile-domain-pill {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0 10px;
    border-radius: 4px;
    background: #eef3f0;
    color: #4a6258;
    font-size: 12px;
    font-weight: 700;
}

.profile-outcome {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
}

.profile-outcome__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #7f9188;
}

.profile-outcome.is-good {
    color: #145c42;
}

.profile-outcome.is-good .profile-outcome__dot {
    background: #19a46f;
}

.profile-outcome.is-warn {
    color: #b67729;
}

.profile-outcome.is-warn .profile-outcome__dot {
    background: #df962f;
}

.profile-outcome.is-neutral {
    color: #48675a;
}

.profile-outcome.is-neutral .profile-outcome__dot {
    background: #5f8575;
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
    .profile-config-grid,
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

    .profile-topbar__actions {
        width: 100%;
    }

    .profile-action-group {
        width: 100%;
    }

    .profile-button {
        flex: 1 1 180px;
    }

    .profile-group-button {
        flex: 1 1 0;
    }

    .profile-stat-grid,
    .profile-config-grid,
    .profile-column {
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
