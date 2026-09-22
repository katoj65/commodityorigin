<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Edit, Setting, User, Message, Phone, Calendar,
    OfficeBuilding, ArrowRight, CircleCheck, Tickets, Files, Refresh, Box,
    InfoFilled, Lock, Clock, Download, Warning, Close, Plus,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import EditProfileDialog from '@/Components/Modals/EditProfileDialog.vue';
import ChangePasswordModal from '@/Components/Modals/ChangePasswordModal.vue';

/* ── Structural/visual port of the uploaded "My Profile" mockup
   (code.html), restyled with this app's own --dp-* theme tokens rather
   than the mockup's own Tailwind palette. Header identity, verification
   status, and Active Sessions use real account/session data (same
   fields the previous PersonalProfile.vue read); "Edit Profile" and
   "Change Password" open the same real EditProfileDialog/
   ChangePasswordModal already used elsewhere (reused, not rebuilt).
   Platform persona, My Businesses, Coffee Preferences, Active
   Operations counts, Recent Activity, and the Danger Zone have no
   backing feature in this app yet, so they're illustrative dummy
   content — their buttons stay inert rather than faking a result. ── */
const props = defineProps({
    sessions: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth.user ?? {});
const profile = computed(() => user.value.profile ?? {});

const fullName = computed(() => user.value.name || 'Profile Owner');
const initials = computed(() => {
    const name = fullName.value;
    return name.split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]?.toUpperCase()).join('') || '—';
});
const emailVerified = computed(() => Boolean(user.value.email_verified_at));
const twoFactorEnabled = computed(() => Boolean(user.value.two_factor_enabled));
const locationLabel = computed(() => [profile.value.city, profile.value.state, profile.value.country].filter(Boolean).join(', ') || 'Location not set');
const memberSinceYear = computed(() => {
    const source = user.value.created_at ? new Date(user.value.created_at) : null;
    return source && !Number.isNaN(source.getTime()) ? source.getFullYear() : '—';
});
const memberSinceFull = computed(() => {
    const source = user.value.created_at ? new Date(user.value.created_at) : null;
    return source && !Number.isNaN(source.getTime())
        ? source.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
        : '—';
});

function sessionDeviceLabel(session) {
    const platform = session.agent?.platform;
    const browser = session.agent?.browser;
    return [browser, platform].filter(Boolean).join(' · ') || 'Unknown device';
}
const latestSession = computed(() => props.sessions[0] ?? null);

const editProfileOpen = ref(false);
const changePasswordOpen = ref(false);

const personaPills = [
    { label: 'Green Coffee Buyer', active: true },
    { label: 'Origin Specialist', active: true },
    { label: 'Cooperative Liaison', active: false },
    { label: 'Quality Q-Grader (Cupper)', active: false },
];

const businesses = [
    { initials: 'UCE', name: 'Uganda Coffee Exporters Ltd', badge: 'Verified Tier 1', badgeTone: 'primary', role: 'Business Administrator', stat: '24 Active Lots', reg: 'Reg #UCDA-EXP-2024' },
    { initials: 'BOT', name: 'Bean Origin Trading Desk Ltd', badge: 'Pending Verification', badgeTone: 'secondary', role: 'Trade Specialist', stat: 'Desk Pilot' },
];

const varieties = ['Robusta Screen 18', 'Uganda Bugisu Arabica AA', 'Rwenzori Natural Drugar'];
const origins = [
    { label: 'Uganda (Primary Focus)', tone: 'high' },
    { label: 'Ethiopia (Yirgacheffe & Sidama)', tone: 'muted' },
    { label: 'Rwanda (Western Lake Kivu)', tone: 'muted' },
    { label: 'Kenya (Nyeri High-Grown)', tone: 'muted' },
];

const activeOperations = [
    { value: '8', label: 'Offers', note: 'Sent & received', icon: Tickets, href: route('exchange.offers') },
    { value: '4', label: 'RFQs', note: 'Sourcing tenders', icon: Files, href: route('rfq.index') },
    { value: '12', label: 'Trades', note: 'Executed contracts', icon: Refresh, tone: 'primary' },
    { value: '9', label: 'Orders', note: 'Active / delivered', icon: Box, href: route('orders.index') },
];

const activityTimeline = [
    { icon: CircleCheck, tone: 'primary', title: 'Counter-Offer Accepted', time: '10:42 EAT', note: 'Buyer accepted counter-offer for 20 MT Uganda Robusta Screen 18 at $4,115/MT FOB Mombasa.' },
    { icon: Message, tone: 'neutral', title: 'RFQ Response Received', time: '09:18 EAT', note: 'Kyagalanyi Coffee Ltd lodged documentation for Tender RFQ-1048 (Bugisu AA).' },
    { icon: Box, tone: 'secondary', title: 'Delivery Note Cleared', time: 'Yesterday', note: 'ORD-1044 cleared Kilindini Mombasa Terminal gate. Escrow milestones updated.' },
    { icon: Setting, tone: 'neutral', title: 'Market Appetite Tuned', time: 'Yesterday', note: 'Updated default CIF Jebel Ali routing preferences in coffee matching engine.' },
];

function scrollToSection(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<template>
    <MainLayout title="Profile">
        <Head title="Profile" />

        <div class="pp-page">
            <!-- ── Page header ────────────────────────────────────────── -->
            <div class="pp-topbar">
                <div>
                    <h1 class="pp-title">My Profile</h1>
                    <p class="pp-subtitle">Manage your personal information, security, and Bean Origin account credentials.</p>
                </div>
                <div class="pp-topbar__actions">
                    <button type="button" class="pp-btn pp-btn--primary" @click="editProfileOpen = true">
                        <el-icon :size="15"><Edit /></el-icon> Edit Profile
                    </button>
                    <Link :href="route('settings.index')" class="pp-btn pp-btn--muted">
                        <el-icon :size="15"><Setting /></el-icon> Account Settings
                    </Link>
                </div>
            </div>

            <!-- ── Profile header card ───────────────────────────────── -->
            <div class="pp-card pp-header-card">
                <div class="pp-identity">
                    <div class="pp-avatar">
                        <img v-if="profile.profile_photo_url" :src="profile.profile_photo_url" :alt="fullName" />
                        <span v-else>{{ initials }}</span>
                        <span v-if="emailVerified" class="pp-avatar__badge"><el-icon :size="12"><CircleCheck /></el-icon></span>
                    </div>
                    <div class="pp-identity__body">
                        <div class="pp-identity__row">
                            <h2 class="pp-identity__name">{{ fullName }}</h2>
                            <span v-if="emailVerified" class="pp-tag pp-tag--fixed"><el-icon :size="11"><CircleCheck /></el-icon> Verified Member</span>
                            <span class="pp-tag pp-tag--mono">Personal Account</span>
                        </div>
                        <p class="pp-identity__role">{{ profile.bio || 'Coffee Market Participant' }}<span v-if="locationLabel"> · {{ locationLabel }}</span></p>
                        <div class="pp-identity__meta">
                            <span class="pp-identity__meta-item">
                                <el-icon :size="14"><Message /></el-icon>
                                <span class="pp-strong">{{ user.email || '—' }}</span>
                                <span v-if="emailVerified" class="pp-dot" title="Verified"></span>
                            </span>
                            <span class="pp-identity__meta-item">
                                <el-icon :size="14"><Phone /></el-icon>
                                <span class="pp-mono pp-strong">{{ user.telephone || '—' }}</span>
                            </span>
                            <span class="pp-identity__meta-item">
                                <el-icon :size="14"><Calendar /></el-icon>
                                <span>Member since {{ memberSinceYear }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Business workspace context — illustrative; no real "active business" concept exists for a personal account yet. -->
                <div class="pp-context-card">
                    <div>
                        <div class="pp-context-card__head">
                            <span class="pp-eyebrow-xs">Current Active Context</span>
                            <span class="pp-tag pp-tag--fixed">Corporate Member</span>
                        </div>
                        <p class="pp-context-card__title"><el-icon :size="16"><OfficeBuilding /></el-icon> Uganda Coffee Exporters Ltd</p>
                        <p class="pp-context-card__note">You are currently authenticated as an individual with administrative rights to this corporate entity.</p>
                    </div>
                    <Link :href="route('business.members.index')" class="pp-context-card__link">
                        <span>Open Business Workspace</span>
                        <el-icon :size="14"><ArrowRight /></el-icon>
                    </Link>
                </div>
            </div>

            <!-- ── Sub-navigation tabs ───────────────────────────────── -->
            <nav class="pp-tabs" aria-label="Profile Tabs">
                <button type="button" class="pp-tab pp-tab--active">Overview</button>
                <button type="button" class="pp-tab" @click="scrollToSection('personal-info')">Personal Information</button>
                <button type="button" class="pp-tab" @click="scrollToSection('preferences')">Coffee Preferences</button>
                <button type="button" class="pp-tab" @click="scrollToSection('businesses')">My Businesses</button>
                <button type="button" class="pp-tab" @click="scrollToSection('security')">Security &amp; Access</button>
                <button type="button" class="pp-tab" @click="scrollToSection('activity')">Activity History</button>
            </nav>

            <!-- ── Two-column workbench ─────────────────────────────── -->
            <div class="pp-grid">
                <!-- ── Column A ──────────────────────────────────────── -->
                <div class="pp-col-main">
                    <!-- Personal Information -->
                    <section class="pp-card" id="personal-info">
                        <div class="pp-card-head">
                            <div>
                                <h3 class="pp-card-title">Personal Information</h3>
                                <p class="pp-card-desc">Primary legal identity details for cross-border trade authorization.</p>
                            </div>
                            <button type="button" class="pp-btn pp-btn--tint pp-btn--sm" @click="editProfileOpen = true">
                                <el-icon :size="14"><Edit /></el-icon> Edit Details
                            </button>
                        </div>
                        <div class="pp-field-grid">
                            <div class="pp-field"><span class="pp-eyebrow-xs">Legal Full Name</span><p class="pp-field__value">{{ fullName }}</p></div>
                            <div class="pp-field">
                                <span class="pp-eyebrow-xs">Primary Email</span>
                                <div class="pp-field__row"><p class="pp-field__value">{{ user.email || '—' }}</p><el-icon v-if="emailVerified" :size="16" class="pp-tone-text"><CircleCheck /></el-icon></div>
                            </div>
                            <div class="pp-field"><span class="pp-eyebrow-xs">Verified Telephone</span><p class="pp-field__value pp-mono">{{ user.telephone || '—' }}</p></div>
                            <div class="pp-field"><span class="pp-eyebrow-xs">Jurisdiction &amp; Location</span><p class="pp-field__value">{{ locationLabel }}</p></div>
                            <div class="pp-field"><span class="pp-eyebrow-xs">System Timezone</span><p class="pp-field__value pp-mono">East Africa Time (UTC+3)</p></div>
                            <div class="pp-field"><span class="pp-eyebrow-xs">Origin Registry Enrollment</span><p class="pp-field__value pp-mono">{{ memberSinceFull }}</p></div>
                        </div>
                        <div class="pp-notice">
                            <el-icon :size="17"><InfoFilled /></el-icon>
                            <span>All identity updates must match your official Uganda Coffee Development Authority (UCDA) or National Identification registry documentation.</span>
                        </div>
                    </section>

                    <!-- Platform Role & Appetite -->
                    <section class="pp-card">
                        <div class="pp-card-head">
                            <div>
                                <h3 class="pp-card-title">Platform Role &amp; Appetite</h3>
                                <p class="pp-card-desc">Configures how AI brokers and institutional order books categorize your appetite.</p>
                            </div>
                            <button type="button" class="pp-btn pp-btn--muted pp-btn--sm">
                                <el-icon :size="14"><Setting /></el-icon> Change Usage
                            </button>
                        </div>
                        <div class="pp-panel">
                            <div class="pp-panel__head">
                                <span class="pp-eyebrow-xs">Active Trading Persona</span>
                                <span class="pp-mono pp-tone-text pp-small pp-strong">ALIGNED WITH ORIGIN BUYER DESK</span>
                            </div>
                            <h4 class="pp-panel__title">Buyer &amp; Origin Trade Specialist</h4>
                            <p class="pp-panel__text">You use Bean Origin to discover, source, bid on export-ready green coffee lots, negotiate forward delivery contracts, and manage direct trade clearing for East African origins.</p>
                            <div class="pp-chip-row">
                                <span v-for="pill in personaPills" :key="pill.label" class="pp-persona-pill" :class="{ 'pp-persona-pill--active': pill.active }">
                                    <el-icon v-if="pill.active" :size="13"><CircleCheck /></el-icon>{{ pill.label }}
                                </span>
                            </div>
                        </div>
                        <div class="pp-notice pp-notice--plain">
                            <el-icon :size="16"><InfoFilled /></el-icon>
                            <span><strong>Notice:</strong> These preferences calibrate market feed algorithms, algorithmic price alert spreads, and matchmaking bots. They do not alter your legal trade limits or escrow authorization rights.</span>
                        </div>
                    </section>

                    <!-- My Businesses -->
                    <section class="pp-card" id="businesses">
                        <div class="pp-card-head">
                            <div>
                                <div class="pp-card-title-row">
                                    <h3 class="pp-card-title">My Businesses</h3>
                                    <span class="pp-tag pp-tag--mono">{{ businesses.length }} Organizations</span>
                                </div>
                                <p class="pp-card-desc">Corporate and cooperative accounts registered under your trading credentials.</p>
                            </div>
                            <button type="button" class="pp-btn pp-btn--primary pp-btn--sm">
                                <el-icon :size="14"><OfficeBuilding /></el-icon> Join or Create Business
                            </button>
                        </div>
                        <div class="pp-biz-list">
                            <div v-for="biz in businesses" :key="biz.name" class="pp-biz-row">
                                <div class="pp-biz-row__left">
                                    <div class="pp-biz-row__badge">{{ biz.initials }}</div>
                                    <div>
                                        <div class="pp-biz-row__name-row">
                                            <span class="pp-strong">{{ biz.name }}</span>
                                            <span class="pp-tag" :class="biz.badgeTone === 'primary' ? 'pp-tag--fixed' : 'pp-tag--secondary'">{{ biz.badge }}</span>
                                        </div>
                                        <div class="pp-biz-row__meta">
                                            <span>Role: <strong class="pp-strong">{{ biz.role }}</strong></span>
                                            <span>•</span>
                                            <span class="pp-mono pp-tone-text pp-strong">{{ biz.stat }}</span>
                                            <template v-if="biz.reg"><span>•</span><span>{{ biz.reg }}</span></template>
                                        </div>
                                    </div>
                                </div>
                                <Link v-if="biz.badgeTone === 'primary'" :href="route('business.members.index')" class="pp-btn pp-btn--primary pp-btn--sm">
                                    <span>Open Business</span>
                                    <el-icon :size="13"><ArrowRight /></el-icon>
                                </Link>
                                <button v-else type="button" class="pp-btn pp-btn--muted pp-btn--sm">View Status</button>
                            </div>
                        </div>
                    </section>

                    <!-- Coffee & Sourcing Preferences -->
                    <section class="pp-card" id="preferences">
                        <div class="pp-card-head">
                            <div>
                                <h3 class="pp-card-title">Coffee &amp; Sourcing Preferences</h3>
                                <p class="pp-card-desc">Tailors spot recommendations, container matching, and direct farm offers.</p>
                            </div>
                            <span class="pp-mono pp-muted pp-tiny">Updated: Today, 08:30 EAT</span>
                        </div>
                        <div class="pp-pref-block">
                            <span class="pp-eyebrow-xs">Preferred Green Coffee Varieties</span>
                            <div class="pp-chip-row">
                                <span v-for="v in varieties" :key="v" class="pp-persona-pill pp-persona-pill--active">{{ v }} <el-icon :size="12"><Close /></el-icon></span>
                                <button type="button" class="pp-mini-btn"><el-icon :size="13"><Plus /></el-icon> Add Variety</button>
                            </div>
                        </div>
                        <div class="pp-pref-block">
                            <span class="pp-eyebrow-xs">Monitored Harvest Origins</span>
                            <div class="pp-chip-row">
                                <span v-for="o in origins" :key="o.label" class="pp-origin-chip" :class="{ 'pp-origin-chip--high': o.tone === 'high' }">{{ o.label }}</span>
                            </div>
                        </div>
                        <div class="pp-field-grid pp-field-grid--2">
                            <div class="pp-field"><span class="pp-eyebrow-xs">Default Contract Volume</span><p class="pp-field__value pp-mono">10 – 50 MT (Commercial Containers)</p><span class="pp-field__note">Full Container Load (FCL) standardized</span></div>
                            <div class="pp-field"><span class="pp-eyebrow-xs">Preferred Delivery Terms &amp; Hubs</span><p class="pp-field__value">FOB Mombasa · CIF Jebel Ali · Ex-Whs Kampala</p><span class="pp-field__note">Incoterms 2020 Compliant</span></div>
                        </div>
                        <div class="pp-pref-foot">
                            <button type="button" class="pp-btn pp-btn--primary pp-btn--sm">Save Preferences</button>
                            <span class="pp-mono pp-muted pp-tiny">Last saved 4 hours ago</span>
                        </div>
                    </section>
                </div>

                <!-- ── Column B ──────────────────────────────────────── -->
                <div class="pp-col-side">
                    <!-- Verification Status -->
                    <section class="pp-card">
                        <div class="pp-card-head">
                            <h3 class="pp-card-title">Verification Status</h3>
                            <span class="pp-tag pp-tag--fixed">Tier 2 Approved</span>
                        </div>
                        <div class="pp-verify-list">
                            <div class="pp-verify-row">
                                <span class="pp-verify-row__left"><el-icon :size="17" class="pp-tone-text"><User /></el-icon><span><p class="pp-strong pp-small">Identity &amp; Biometrics</p><p class="pp-muted pp-tiny">National ID registry cross-check</p></span></span>
                                <span class="pp-mono pp-tone-text pp-strong pp-tiny">VERIFIED</span>
                            </div>
                            <div class="pp-verify-row">
                                <span class="pp-verify-row__left"><el-icon :size="17" class="pp-tone-text"><Message /></el-icon><span><p class="pp-strong pp-small">Email Verification</p><p class="pp-muted pp-tiny">Primary institutional inbox bound</p></span></span>
                                <span class="pp-mono pp-strong pp-tiny" :class="emailVerified ? 'pp-tone-text' : 'pp-muted'">{{ emailVerified ? 'VERIFIED' : 'PENDING' }}</span>
                            </div>
                            <div class="pp-verify-row">
                                <span class="pp-verify-row__left"><el-icon :size="17" class="pp-tone-text"><Lock /></el-icon><span><p class="pp-strong pp-small">Two-Factor Authentication</p><p class="pp-muted pp-tiny">{{ twoFactorEnabled ? 'Authenticator active' : 'Not yet enabled' }}</p></span></span>
                                <span class="pp-mono pp-strong pp-tiny" :class="twoFactorEnabled ? 'pp-tone-text' : 'pp-muted'">{{ twoFactorEnabled ? 'ACTIVE' : 'OFF' }}</span>
                            </div>
                        </div>
                        <div class="pp-field" v-if="latestSession">
                            <span class="pp-eyebrow-xs">Most Recent Authentication</span>
                            <p class="pp-field__value pp-mono pp-small">{{ latestSession.last_active || '—' }}</p>
                            <span class="pp-field__note">{{ sessionDeviceLabel(latestSession) }} · {{ latestSession.ip_address || 'Unknown IP' }}</span>
                        </div>
                    </section>

                    <!-- Active Operations -->
                    <section class="pp-card">
                        <div class="pp-card-head">
                            <h3 class="pp-card-title">Active Operations</h3>
                            <span class="pp-muted pp-tiny">Personal Queue</span>
                        </div>
                        <div class="pp-ops-grid">
                            <component
                                :is="op.href ? Link : 'div'"
                                v-for="op in activeOperations"
                                :key="op.label"
                                :href="op.href"
                                class="pp-ops-card"
                            >
                                <div class="pp-ops-card__top">
                                    <span class="pp-mono pp-strong pp-ops-card__value" :class="op.tone === 'primary' ? 'pp-tone-text' : ''">{{ op.value }}</span>
                                    <el-icon :size="17" class="pp-muted"><component :is="op.icon" /></el-icon>
                                </div>
                                <p class="pp-strong pp-small">{{ op.label }}</p>
                                <p class="pp-muted pp-tiny">{{ op.note }}</p>
                            </component>
                        </div>
                    </section>

                    <!-- Recent Personal Activity -->
                    <section class="pp-card" id="activity">
                        <div class="pp-card-head">
                            <h3 class="pp-card-title">Recent Personal Activity</h3>
                        </div>
                        <div class="pp-timeline">
                            <div v-for="(item, i) in activityTimeline" :key="i" class="pp-timeline__row">
                                <span class="pp-timeline__icon" :class="`pp-timeline__icon--${item.tone}`"><el-icon :size="14"><component :is="item.icon" /></el-icon></span>
                                <div class="pp-timeline__body">
                                    <div class="pp-timeline__top"><span class="pp-strong pp-small">{{ item.title }}</span><span class="pp-mono pp-muted pp-tiny">{{ item.time }}</span></div>
                                    <p class="pp-muted pp-tiny">{{ item.note }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Security & Credentials -->
                    <section class="pp-card" id="security">
                        <div class="pp-card-head">
                            <h3 class="pp-card-title">Security &amp; Credentials</h3>
                        </div>
                        <div class="pp-security-list">
                            <div class="pp-security-row">
                                <div><p class="pp-strong pp-small">Account Password</p><p class="pp-muted pp-tiny">Change your account password</p></div>
                                <button type="button" class="pp-mini-btn" @click="changePasswordOpen = true">Change</button>
                            </div>
                            <div class="pp-security-row">
                                <div><p class="pp-strong pp-small">Two-Factor Authentication</p><p class="pp-muted pp-tiny" :class="twoFactorEnabled ? 'pp-tone-text' : ''">{{ twoFactorEnabled ? 'Enabled on this account' : 'Not enabled yet' }}</p></div>
                                <button type="button" class="pp-mini-btn">Manage</button>
                            </div>
                            <div class="pp-security-row">
                                <div><p class="pp-strong pp-small">Active Sessions ({{ sessions.length }})</p><p class="pp-muted pp-tiny">{{ latestSession ? sessionDeviceLabel(latestSession) : 'No active sessions recorded' }}</p></div>
                                <button type="button" class="pp-mini-btn" @click="scrollToSection('security')">Sessions</button>
                            </div>
                        </div>
                        <button type="button" class="pp-btn pp-btn--muted pp-btn--block">
                            <el-icon :size="15"><Clock /></el-icon> View Full Security &amp; Audit Log
                        </button>
                    </section>

                    <!-- Danger Zone -->
                    <section class="pp-card pp-danger-card">
                        <div class="pp-danger-head"><el-icon :size="17"><Warning /></el-icon><h3 class="pp-danger-title">Account Lifecycle &amp; Portability</h3></div>
                        <div class="pp-panel">
                            <span class="pp-eyebrow-xs">Data Portability</span>
                            <p class="pp-panel__text">Download a cryptographically signed JSON/CSV bundle of your personal login audits, profile details, and communications archive under GDPR/Uganda DPPA mandates.</p>
                            <button type="button" class="pp-mini-btn"><el-icon :size="14"><Download /></el-icon> Download Personal Data Archive</button>
                        </div>
                        <div class="pp-panel pp-panel--danger">
                            <span class="pp-eyebrow-xs pp-tone-error">Deactivate Credentials</span>
                            <p class="pp-panel__text">Permanently revoke {{ fullName }}'s personal authentication keys. <strong class="pp-strong">Notice:</strong> Executed forward trade contracts, escrow releases, and Uganda Coffee Development Authority trade certificates remain permanently archived for statutory compliance.</p>
                            <button type="button" class="pp-mini-btn pp-mini-btn--danger">Deactivate Personal Account</button>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <EditProfileDialog v-model="editProfileOpen" :user="user" :profile="profile" />
        <ChangePasswordModal v-model="changePasswordOpen" />
    </MainLayout>
</template>

<style scoped>
.pp-page { font-family: var(--dp-font-sans); display: flex; flex-direction: column; gap: 24px; color: var(--dp-on-surface); }
.pp-mono { font-family: var(--dp-font-mono); }
.pp-muted { color: var(--dp-on-surface-variant); }
.pp-strong { font-weight: 700; color: var(--dp-on-surface); }
.pp-small { font-size: 12px; margin: 0; }
.pp-tiny { font-size: 10.5px; }
.pp-tone-text { color: var(--dp-primary); }
.pp-tone-error { color: var(--dp-error); }
.pp-eyebrow-xs { display: block; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface-variant); margin-bottom: 4px; }

/* Top bar */
.pp-topbar { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.pp-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; color: var(--dp-on-surface); margin: 0; }
.pp-subtitle { font-size: 13px; color: var(--dp-on-surface-variant); margin: 4px 0 0; }
.pp-topbar__actions { display: flex; align-items: center; gap: 10px; }

.pp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 16px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); text-decoration: none; transition: opacity .12s ease, background .12s ease; }
.pp-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.pp-btn--primary:hover { opacity: .9; }
.pp-btn--muted { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.pp-btn--muted:hover { background: var(--dp-surface-container-high); }
.pp-btn--tint { background: color-mix(in srgb, var(--dp-primary-fixed) 45%, transparent); color: var(--dp-primary); }
.pp-btn--tint:hover { background: var(--dp-primary-fixed); }
.pp-btn--sm { height: 30px; padding: 0 12px; font-size: 11.5px; }
.pp-btn--block { width: 100%; }

/* Cards */
.pp-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 16px; }
.pp-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.pp-card-title { font-size: 1rem; font-weight: 800; color: var(--dp-on-surface); margin: 0; }
.pp-card-title-row { display: flex; align-items: center; gap: 8px; }
.pp-card-desc { font-size: 11.5px; color: var(--dp-on-surface-variant); margin: 3px 0 0; }

.pp-tag { display: inline-flex; align-items: center; gap: 3px; padding: 2px 9px; border-radius: 999px; font-size: 10.5px; font-weight: 800; white-space: nowrap; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.pp-tag--fixed { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.pp-tag--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.pp-tag--mono { font-family: var(--dp-font-mono); background: var(--dp-surface-container); }

/* Header card */
.pp-header-card { flex-direction: row; align-items: stretch; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
.pp-identity { display: flex; align-items: flex-start; gap: 18px; flex: 1; min-width: 260px; }
.pp-avatar { position: relative; flex-shrink: 0; width: 84px; height: 84px; border-radius: 18px; background: var(--dp-surface-container-high); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; font-weight: 800; color: var(--dp-primary); overflow: hidden; }
.pp-avatar img { width: 100%; height: 100%; object-fit: cover; }
.pp-avatar__badge { position: absolute; bottom: -3px; right: -3px; width: 22px; height: 22px; border-radius: 999px; background: var(--dp-primary); color: var(--dp-on-primary); display: flex; align-items: center; justify-content: center; }
.pp-identity__row { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
.pp-identity__name { font-size: 1.375rem; font-weight: 800; letter-spacing: -.01em; color: var(--dp-on-surface); margin: 0; }
.pp-identity__role { font-size: 13px; color: var(--dp-on-surface-variant); margin: 4px 0 0; }
.pp-identity__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 16px 20px; margin-top: 10px; font-size: 12px; }
.pp-identity__meta-item { display: inline-flex; align-items: center; gap: 6px; color: var(--dp-on-surface-variant); }
.pp-identity__meta-item .el-icon { color: var(--dp-primary); }
.pp-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--dp-primary); }

.pp-context-card { width: 320px; flex-shrink: 0; background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 10px; }
.pp-context-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px; }
.pp-context-card__title { display: flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 700; color: var(--dp-on-surface); margin: 3px 0; }
.pp-context-card__title .el-icon { color: var(--dp-secondary); }
.pp-context-card__note { font-size: 11px; color: var(--dp-on-surface-variant); line-height: 1.5; margin: 0; }
.pp-context-card__link { display: flex; align-items: center; justify-content: space-between; padding: 7px 11px; background: var(--dp-surface-container-lowest); border-radius: 6px; font-size: 11.5px; font-weight: 700; color: var(--dp-primary); text-decoration: none; }
.pp-context-card__link:hover { background: var(--dp-surface-container); }

/* Tabs */
.pp-tabs { display: flex; align-items: center; gap: 4px; background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 10px; padding: 6px; overflow-x: auto; }
.pp-tab { padding: 8px 14px; border-radius: 7px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 11.5px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); }
.pp-tab:hover { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.pp-tab--active { background: var(--dp-primary); color: var(--dp-on-primary); }

/* Grid */
.pp-grid { display: grid; grid-template-columns: minmax(0, 7fr) minmax(300px, 5fr); gap: 24px; align-items: start; }
.pp-col-main { display: flex; flex-direction: column; gap: 24px; min-width: 0; }
.pp-col-side { display: flex; flex-direction: column; gap: 20px; }
@media (max-width: 1100px) { .pp-grid { grid-template-columns: 1fr; } .pp-header-card { flex-direction: column; } .pp-context-card { width: 100%; } }

/* Field grid */
.pp-field-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.pp-field-grid--2 { grid-template-columns: repeat(2, 1fr); }
.pp-field { background: var(--dp-surface-container-low); border-radius: 8px; padding: 11px 13px; }
.pp-field__value { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); margin: 0; }
.pp-field__row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.pp-field__note { display: block; font-size: 10.5px; color: var(--dp-on-surface-variant); margin-top: 3px; }

.pp-notice { display: flex; align-items: flex-start; gap: 9px; background: var(--dp-surface-container-low); border-radius: 8px; padding: 11px 13px; font-size: 11.5px; color: var(--dp-on-surface-variant); line-height: 1.5; }
.pp-notice .el-icon { color: var(--dp-secondary); flex-shrink: 0; margin-top: 1px; }
.pp-notice--plain .el-icon { color: var(--dp-on-surface-variant); }
.pp-notice strong { color: var(--dp-on-surface); font-weight: 700; }

/* Panel */
.pp-panel { background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 8px; }
.pp-panel__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
.pp-panel__title { font-size: 13.5px; font-weight: 800; color: var(--dp-on-surface); margin: 0; }
.pp-panel__text { font-size: 11.5px; color: var(--dp-on-surface-variant); line-height: 1.55; margin: 0; }
.pp-panel--danger { background: color-mix(in srgb, var(--dp-error-container) 45%, transparent); }

.pp-chip-row { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 2px; }
.pp-persona-pill { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: var(--dp-surface-container); color: var(--dp-on-surface-variant); }
.pp-persona-pill--active { background: var(--dp-primary); color: var(--dp-on-primary); }
.pp-origin-chip { padding: 5px 11px; border-radius: 999px; font-size: 11px; font-weight: 600; background: var(--dp-surface-container); color: var(--dp-on-surface-variant); }
.pp-origin-chip--high { background: var(--dp-surface-container-high); color: var(--dp-on-surface); font-weight: 700; }
.pp-mini-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 11px; border-radius: 6px; border: none; background: var(--dp-surface-container); color: var(--dp-on-surface); font-size: 11px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); white-space: nowrap; }
.pp-mini-btn:hover { background: var(--dp-surface-container-high); }
.pp-mini-btn--danger { background: var(--dp-surface-container-lowest); color: var(--dp-error); }
.pp-mini-btn--danger:hover { background: var(--dp-error-container); }

.pp-pref-block { display: flex; flex-direction: column; gap: 4px; }
.pp-pref-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding-top: 4px; }

/* My Businesses */
.pp-biz-list { display: flex; flex-direction: column; gap: 10px; }
.pp-biz-row { background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.pp-biz-row__left { display: flex; align-items: flex-start; gap: 12px; }
.pp-biz-row__badge { width: 38px; height: 38px; border-radius: 8px; background: var(--dp-surface-container-highest); color: var(--dp-primary); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; }
.pp-biz-row__name-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pp-biz-row__meta { display: flex; align-items: center; gap: 7px; font-size: 11px; color: var(--dp-on-surface-variant); margin-top: 3px; flex-wrap: wrap; }

/* Verification */
.pp-verify-list { display: flex; flex-direction: column; gap: 8px; }
.pp-verify-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--dp-surface-container-low); border-radius: 8px; padding: 10px 12px; }
.pp-verify-row__left { display: flex; align-items: center; gap: 10px; }
.pp-verify-row__left p { margin: 0; }

/* Active operations */
.pp-ops-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.pp-ops-card { display: block; background: var(--dp-surface-container-low); border-radius: 10px; padding: 12px; text-decoration: none; color: inherit; transition: background .12s ease; }
a.pp-ops-card:hover { background: var(--dp-surface-container); }
.pp-ops-card__top { display: flex; align-items: center; justify-content: space-between; }
.pp-ops-card__value { font-size: 1.125rem; }
.pp-ops-card p { margin: 4px 0 0; }

/* Timeline */
.pp-timeline { display: flex; flex-direction: column; gap: 12px; }
.pp-timeline__row { display: flex; align-items: flex-start; gap: 10px; }
.pp-timeline__icon { width: 26px; height: 26px; border-radius: 999px; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.pp-timeline__icon--primary { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.pp-timeline__icon--secondary { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.pp-timeline__body { flex: 1; min-width: 0; }
.pp-timeline__top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.pp-timeline__body p { margin: 2px 0 0; line-height: 1.5; }

/* Security */
.pp-security-list { display: flex; flex-direction: column; gap: 8px; }
.pp-security-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--dp-surface-container-low); border-radius: 8px; padding: 10px 12px; }
.pp-security-row p { margin: 0; }

/* Danger zone */
.pp-danger-card { border-color: color-mix(in srgb, var(--dp-error) 25%, var(--dp-outline-variant)); }
.pp-danger-head { display: flex; align-items: center; gap: 8px; }
.pp-danger-head .el-icon { color: var(--dp-error); }
.pp-danger-title { font-size: 1rem; font-weight: 800; color: var(--dp-error); margin: 0; }

@media (max-width: 640px) {
    .pp-field-grid, .pp-field-grid--2, .pp-ops-grid { grid-template-columns: 1fr; }
    .pp-topbar__actions { width: 100%; }
    .pp-topbar__actions .pp-btn { flex: 1; justify-content: center; }
}
</style>
