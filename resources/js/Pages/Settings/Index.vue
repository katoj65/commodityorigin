<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    Bell, Coin, Connection, CreditCard, Delete, EditPen, Hide, InfoFilled, Key,
    Lock, Monitor, OfficeBuilding, Operation, Plus, PriceTag, QuestionFilled,
    Right, User, Van, Wallet,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EditProfileDialog from '@/Components/Modals/EditProfileDialog.vue';
import ChangePasswordModal from '@/Components/Modals/ChangePasswordModal.vue';
import SignOutOtherSessionsModal from '@/Components/Modals/SignOutOtherSessionsModal.vue';

const props = defineProps({
    sessions: { type: Array, default: () => [] },
    exchangeRates: { type: Array, default: () => [] },
    priceIndexes: { type: Array, default: () => [] },
    deliveryMethods: { type: Array, default: () => [] },
    incoterms: { type: Array, default: () => [] },
});

const page = usePage();

/* ── Real display data — every value below comes straight from a genuine
   user / profile / session field (all shared globally via Inertia's
   HandleInertiaRequests, same as PersonalProfile.vue); nothing here is
   invented to fill a layout slot. Fields with no backing column anywhere
   in the schema (notification channel preferences, weight/date/timezone
   preferences, privacy visibility flags, bank-account payout methods,
   integrations) stay visually present — per "must look exact" — but
   disabled with a "Not yet configurable" note rather than pretending to
   save, since a Settings page implies persistence and a silently-broken
   save would be actively misleading. ─────────────────────────────────── */
const user = computed(() => page.props.auth.user ?? {});
const profile = computed(() => user.value.profile ?? {});
const isBusiness = computed(() => user.value.role === 'business');
const locationLabel = computed(() => [profile.value.city, profile.value.state, profile.value.country].filter(Boolean).join(', '));

function initials(name) {
    return String(name || '').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase() || '?';
}

/* ── Categories & tab state ───────────────────────────────────────────── */
const categories = [
    { id: 'account', label: 'Account', icon: User },
    { id: 'business', label: 'Business', icon: OfficeBuilding },
    { id: 'notifications', label: 'Notifications', icon: Bell },
    { id: 'security', label: 'Security', icon: Lock },
    { id: 'payments', label: 'Payments', icon: CreditCard },
    { id: 'integrations', label: 'Integrations', icon: Connection },
    { id: 'preferences', label: 'Preferences', icon: Operation },
    { id: 'privacy', label: 'Privacy', icon: Hide },
    { id: 'market', label: 'Market Data', icon: Coin },
    { id: 'help', label: 'Help & Support', icon: QuestionFilled },
];
const activeTab = ref('account');

/* ── Account / Security shared modals ─────────────────────────────────── */
const editProfileOpen = ref(false);
const changePasswordOpen = ref(false);
const signOutOtherOpen = ref(false);
const deleteAccountInfoOpen = ref(false);

const sessionsPreview = computed(() => props.sessions.slice(0, 5));
function sessionDeviceLabel(session) {
    return [session.agent?.browser, session.agent?.platform].filter(Boolean).join(' · ') || 'Unknown device';
}

/* ── Preferences: settlement currency — the only real preference field
   (users.currency_code). Weight unit / date format / marketplace view /
   timezone have no columns anywhere and stay disabled below. ─────────── */
const currencyOptions = computed(() => page.props.currencies ?? []);
const currentCurrency = computed(() => currencyOptions.value.find((c) => c.code === user.value.currency_code) ?? null);
const currencyForm = useForm({ currency_code: user.value.currency_code || '' });

function submitCurrency(code) {
    if (!code || code === user.value.currency_code) return;
    currencyForm.currency_code = code;
    currencyForm.post(route('profile.currency'), { preserveScroll: true });
}

/* ── Notifications — no per-category channel-preference columns exist;
   this whole grid is illustrative and disabled. */
const notificationGroups = [
    {
        title: 'Trading Notifications', icon: PriceTag,
        rows: [
            { label: 'New offer received', sub: 'Direct bilateral bids and counter-proposals on active lots.', inApp: true, email: true },
            { label: 'Offer accepted or rejected', sub: 'Instant confirmations on contract approvals.', inApp: true, email: true },
            { label: 'New auction alerts & ending countdowns', sub: 'Notifications before auction floor close.', inApp: true, email: false },
            { label: 'Outbid notifications', sub: 'Alert when another buyer exceeds your ceiling bid.', inApp: true, email: true },
            { label: 'RFQ responses & trade updates', sub: 'Supplier quotes and delivery milestone status.', inApp: true, email: true },
        ],
    },
    {
        title: 'Operations & Supply Chain', icon: Van,
        rows: [
            { label: 'Farm collection & intake events', sub: 'When weighing and intake slips are logged.', inApp: true, email: false },
            { label: 'Batch & lot processing milestones', sub: 'Milling, sorting, and outturn loss reports.', inApp: true, email: false },
            { label: 'Inventory thresholds & delivery updates', sub: 'Warehouse occupancy and shipment gates.', inApp: true, email: true },
        ],
    },
    {
        title: 'Account & Compliance', icon: Lock,
        rows: [
            { label: 'Security alerts & new logins', sub: 'Immediate alerts on device auth and password resets.', inApp: true, email: true, locked: true },
            { label: 'Regulatory verification & license renewals', sub: 'Export permits and compliance audit notifications.', inApp: true, email: true },
        ],
    },
];

/* ── Privacy — no visibility/is_public columns exist; illustrative. ───── */
const privacyRows = [
    { label: 'Show Business Profile publicly', sub: 'Let verified buyers locate your entity in the directory.' },
    { label: 'Show key contact information to verified buyers', sub: 'Display corporate phone and email on public lot dossiers.' },
    { label: 'Show historical trade volumes on ledger', sub: 'Displays your completed trade count without disclosing prices.' },
];

/* ── Integrations — none of these connectors exist in this app's real
   domain; fully illustrative, buttons disabled. ──────────────────────── */
const integrations = [
    { icon: 'robot', name: 'AI Cupping & Sourcing Engine', desc: 'Automated defect analysis and yield predictions.', connected: true },
    { icon: 'link', name: 'Distributed Ledger Consensus Node', desc: 'Cryptographic lot stamping and audit hash notary.', connected: true },
    { icon: 'ship', name: 'Ocean Logistics Tracking', desc: 'Real-time container tracking to destination ports.', connected: true },
    { icon: 'geo', name: 'Satellite Deforestation Registry', desc: 'Automated polygon validation for compliance.', connected: true },
    { icon: 'calc', name: 'ERP / Accounting Connector', desc: 'Ledger export for invoices and grower payouts.', connected: false },
];

/* ── Preferences — visually present, disabled (no backing columns). ───── */
const staticPreferences = [
    { label: 'Weight & Mass Unit', value: 'Kilograms (kg) & 60kg Export Bags' },
    { label: 'Date Format', value: 'DD MMM YYYY (e.g., 18 Sep 2026)' },
    { label: 'Default Marketplace View', value: 'Compact Table (High Density Terminal)' },
    { label: 'Screen Moisture Metric System', value: 'Standard 1/64 Inch Sieve' },
];

/* ── Market Data tab — ported verbatim from the previous Settings/Index.vue
   (exchange rates, price indexes CRUD, delivery methods & incoterms
   reference lists); only the surrounding shell changed. ──────────────── */
const fmt = (value, digits = 2) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
};
const changeClass = (value) => (value >= 0 ? 'is-up' : 'is-down');
const changeText = (value) => (value === null || value === undefined ? '—' : `${value >= 0 ? '+' : ''}${fmt(value, 2)}%`);

const addIndexOpen = ref(false);
const confirmDeleteOpen = ref(false);
const pendingDeleteIndex = ref(null);

const indexForm = useForm({
    item: '',
    current_price: '',
    percentage_fluctuation: '',
    status: 'active',
});

function openAddIndex() {
    indexForm.reset();
    indexForm.clearErrors();
    addIndexOpen.value = true;
}

function submitIndex() {
    indexForm.post(route('settings.price-indexes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addIndexOpen.value = false;
        },
    });
}

function deleteIndex(index) {
    pendingDeleteIndex.value = index;
    confirmDeleteOpen.value = true;
}

function confirmDeleteIndex() {
    if (!pendingDeleteIndex.value) return;
    indexForm.delete(route('settings.price-indexes.destroy', pendingDeleteIndex.value.id), { preserveScroll: true });
    pendingDeleteIndex.value = null;
}
</script>

<template>
    <MainLayout title="Settings">
        <Head title="Settings" />

        <div class="set-page">
            <div class="set-header">
                <h1 class="dp-display-md">Settings</h1>
                <p class="set-subtitle">Manage your account, business, and platform preferences.</p>
            </div>

            <div class="set-layout">
                <!-- ── Categories sidebar ──────────────────────────────────── -->
                <nav class="set-nav" aria-label="Settings Categories">
                    <div class="set-nav__header">Settings Categories</div>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        class="set-nav__btn"
                        :class="{ 'set-nav__btn--active': activeTab === cat.id }"
                        @click="activeTab = cat.id"
                    >
                        <span class="set-nav__left"><el-icon><component :is="cat.icon" /></el-icon> {{ cat.label }}</span>
                        <el-icon class="set-nav__chevron" :size="12"><Right /></el-icon>
                    </button>
                </nav>

                <!-- ── Panels ──────────────────────────────────────────────── -->
                <div class="set-content">

                    <!-- ACCOUNT -->
                    <template v-if="activeTab === 'account'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Account Settings</h2>
                                    <p class="set-card__desc">Manage your personal credentials, contact info, and profile details.</p>
                                </div>
                                <button type="button" class="set-btn set-btn--outline" @click="changePasswordOpen = true">
                                    <el-icon :size="14"><Key /></el-icon> Change Password
                                </button>
                            </div>
                            <div class="set-card__body">
                                <div class="set-identity">
                                    <div class="set-identity__avatar">
                                        <img v-if="profile.profile_photo_url" :src="profile.profile_photo_url" :alt="user.name">
                                        <span v-else>{{ initials(user.name) }}</span>
                                    </div>
                                    <div class="set-identity__text">
                                        <span class="set-identity__name">{{ user.name || '—' }}</span>
                                        <span class="set-identity__meta">{{ user.email || '—' }} · {{ user.telephone || 'No phone on file' }}</span>
                                        <span v-if="locationLabel" class="set-identity__meta">{{ locationLabel }}</span>
                                    </div>
                                    <button type="button" class="set-btn set-btn--primary" @click="editProfileOpen = true">
                                        <el-icon :size="14"><EditPen /></el-icon> Edit Account Details
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="set-danger">
                            <div class="set-danger__head"><el-icon><InfoFilled /></el-icon> Account Danger Zone</div>
                            <div class="set-danger__body">
                                <div class="set-danger__row">
                                    <div>
                                        <div class="set-danger__label">Sign Out Across Devices</div>
                                        <div class="set-danger__sub">Terminate all other browser sessions, keeping this one signed in.</div>
                                    </div>
                                    <button type="button" class="set-btn set-btn--outline" @click="signOutOtherOpen = true">Sign Out All Devices</button>
                                </div>
                                <div class="set-danger__row">
                                    <div>
                                        <div class="set-danger__label set-danger__label--danger">Delete Account</div>
                                        <div class="set-danger__sub">Permanently deactivate your access. Requires support review.</div>
                                    </div>
                                    <button type="button" class="set-btn set-btn--danger" @click="deleteAccountInfoOpen = true">Delete Account</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- BUSINESS -->
                    <template v-else-if="activeTab === 'business'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Business Settings</h2>
                                    <p class="set-card__desc">Corporate identity, registered entities, and team seat permissions live on your Business Profile.</p>
                                </div>
                            </div>
                            <div class="set-card__body">
                                <div v-if="isBusiness" class="set-callout">
                                    <el-icon :size="20"><OfficeBuilding /></el-icon>
                                    <div class="set-callout__text">
                                        <strong>Manage your business identity, registration, and team</strong>
                                        <span>Legal name, registration/tax IDs, address, description, and business members are all edited from your Business Profile.</span>
                                    </div>
                                    <Link :href="route('profile.show')" class="set-btn set-btn--primary">Open Business Profile</Link>
                                </div>
                                <div v-else class="set-empty">
                                    <el-icon :size="22"><OfficeBuilding /></el-icon>
                                    <p>Business settings are only available on a business account. Switch your account role to register a business.</p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- NOTIFICATIONS -->
                    <template v-else-if="activeTab === 'notifications'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Notification Settings</h2>
                                    <p class="set-card__desc">Specify communication channels for trades, operational alerts, and account events.</p>
                                </div>
                                <Link :href="route('notifications.index')" class="set-btn set-btn--outline"><el-icon :size="14"><Bell /></el-icon> Open Notification Inbox</Link>
                            </div>
                            <div class="set-card__body">
                                <p class="set-note"><el-icon :size="14"><InfoFilled /></el-icon> Per-category channel preferences aren't configurable yet — shown for reference. Your notification inbox above is fully functional.</p>
                                <div v-for="group in notificationGroups" :key="group.title" class="set-notif-group">
                                    <div class="set-notif-group__head">
                                        <span class="set-notif-group__title"><el-icon :size="14"><component :is="group.icon" /></el-icon> {{ group.title }}</span>
                                        <span class="set-notif-group__cols">IN-APP &nbsp;&nbsp;&nbsp; EMAIL</span>
                                    </div>
                                    <div class="set-notif-list">
                                        <div v-for="row in group.rows" :key="row.label" class="set-notif-row">
                                            <div class="set-notif-row__text">
                                                <span>{{ row.label }}</span>
                                                <small>{{ row.sub }}</small>
                                            </div>
                                            <div class="set-notif-row__toggles">
                                                <input type="checkbox" :checked="row.inApp" disabled>
                                                <input type="checkbox" :checked="row.email" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- SECURITY -->
                    <template v-else-if="activeTab === 'security'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Security Settings</h2>
                                    <p class="set-card__desc">Protect your trading credentials with strong authentication and session controls.</p>
                                </div>
                                <span class="set-pill" :class="user.two_factor_enabled ? 'set-pill--green' : 'set-pill--muted'">{{ user.two_factor_enabled ? '2FA Active' : '2FA Off' }}</span>
                            </div>
                            <div class="set-card__body">
                                <div class="set-sec-row">
                                    <div>
                                        <div class="set-sec-row__title">Password</div>
                                        <div class="set-sec-row__sub">Change your password regularly to keep this account secure.</div>
                                    </div>
                                    <button type="button" class="set-btn set-btn--outline" @click="changePasswordOpen = true">Update Password</button>
                                </div>

                                <div class="set-sec-row">
                                    <div>
                                        <div class="set-sec-row__title">Two-Factor Authentication</div>
                                        <div class="set-sec-row__sub">Require an authenticator app for trades and settlements.</div>
                                    </div>
                                    <span class="set-pill" :class="user.two_factor_enabled ? 'set-pill--green' : 'set-pill--muted'">{{ user.two_factor_enabled ? 'Enabled' : 'Not Enabled' }}</span>
                                </div>

                                <div class="set-sec-row set-sec-row--block">
                                    <div class="set-sec-row__head">
                                        <div class="set-sec-row__title">Active Sessions</div>
                                        <button type="button" class="set-btn set-btn--outline set-btn--danger-outline set-btn--sm" @click="signOutOtherOpen = true">Sign Out of All Devices</button>
                                    </div>
                                    <div v-if="sessions.length" class="set-sessions">
                                        <div v-for="(session, index) in sessionsPreview" :key="index" class="set-session-row">
                                            <span class="set-session-row__icon"><el-icon :size="16"><Monitor /></el-icon></span>
                                            <div class="set-session-row__body">
                                                <span class="set-session-row__device">{{ sessionDeviceLabel(session) }}</span>
                                                <span class="set-session-row__meta">{{ session.ip_address || 'Unknown IP' }} · {{ session.last_active }}</span>
                                            </div>
                                            <span v-if="session.is_current_device" class="set-pill set-pill--primary">This device</span>
                                        </div>
                                    </div>
                                    <p v-else class="set-note">No active sessions recorded.</p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- PAYMENTS -->
                    <template v-else-if="activeTab === 'payments'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Payment Settings</h2>
                                    <p class="set-card__desc">Manage your platform wallet, escrow balance, and settlement currency.</p>
                                </div>
                                <Link :href="route('wallet.index')" class="set-btn set-btn--primary"><el-icon :size="14"><Wallet /></el-icon> Open Wallet</Link>
                            </div>
                            <div class="set-card__body">
                                <div class="set-callout">
                                    <el-icon :size="20"><Wallet /></el-icon>
                                    <div class="set-callout__text">
                                        <strong>Deposits, withdrawals, and escrow</strong>
                                        <span>Bank/mobile-money deposits, withdrawals, and internal transfers are all managed from your Wallet.</span>
                                    </div>
                                </div>
                                <div class="set-sec-row">
                                    <div>
                                        <div class="set-sec-row__title">Settlement Currency</div>
                                        <div class="set-sec-row__sub">{{ currentCurrency ? `${currentCurrency.symbol} · ${currentCurrency.name}` : 'No currency selected' }} — change this in Preferences.</div>
                                    </div>
                                    <button type="button" class="set-btn set-btn--outline" @click="activeTab = 'preferences'">Go to Preferences</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- INTEGRATIONS -->
                    <template v-else-if="activeTab === 'integrations'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Platform Integrations</h2>
                                    <p class="set-card__desc">External software, oracle feeds, and logistics trackers connected to the platform.</p>
                                </div>
                            </div>
                            <div class="set-card__body">
                                <p class="set-note"><el-icon :size="14"><InfoFilled /></el-icon> Integration management isn't available yet — shown for reference only.</p>
                                <div class="set-integrations">
                                    <div v-for="i in integrations" :key="i.name" class="set-integration-row">
                                        <div class="set-integration-row__icon"><el-icon :size="18"><Connection /></el-icon></div>
                                        <div class="set-integration-row__text">
                                            <span>{{ i.name }}</span>
                                            <small>{{ i.desc }}</small>
                                        </div>
                                        <span class="set-pill" :class="i.connected ? 'set-pill--green' : 'set-pill--muted'">{{ i.connected ? 'Connected' : 'Not Connected' }}</span>
                                        <button type="button" class="set-btn set-btn--outline set-btn--sm" disabled>Configure</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- PREFERENCES -->
                    <template v-else-if="activeTab === 'preferences'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Platform Preferences</h2>
                                    <p class="set-card__desc">Tailor default units, currency, and terminal view styles.</p>
                                </div>
                            </div>
                            <div class="set-card__body">
                                <div class="set-field">
                                    <label class="set-field__label">Default Settlement Currency</label>
                                    <el-select
                                        v-model="currencyForm.currency_code"
                                        filterable
                                        placeholder="Select currency"
                                        :disabled="currencyForm.processing"
                                        class="set-select"
                                        @change="submitCurrency"
                                    >
                                        <el-option v-for="option in currencyOptions" :key="option.code" :label="`${option.code} — ${option.name}`" :value="option.code" />
                                    </el-select>
                                </div>
                                <p class="set-note"><el-icon :size="14"><InfoFilled /></el-icon> The fields below aren't configurable yet — shown for reference only.</p>
                                <div class="set-pref-grid">
                                    <div v-for="p in staticPreferences" :key="p.label" class="set-field">
                                        <label class="set-field__label">{{ p.label }}</label>
                                        <el-input :model-value="p.value" disabled class="set-select" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- PRIVACY -->
                    <template v-else-if="activeTab === 'privacy'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Privacy &amp; Data Visibility</h2>
                                    <p class="set-card__desc">Control which company credentials and transaction trails are public on the exchange.</p>
                                </div>
                            </div>
                            <div class="set-card__body">
                                <p class="set-note"><el-icon :size="14"><InfoFilled /></el-icon> Visibility controls aren't configurable yet — shown for reference only.</p>
                                <div class="set-notif-list">
                                    <div v-for="row in privacyRows" :key="row.label" class="set-notif-row">
                                        <div class="set-notif-row__text">
                                            <span>{{ row.label }}</span>
                                            <small>{{ row.sub }}</small>
                                        </div>
                                        <input type="checkbox" checked disabled>
                                    </div>
                                </div>
                                <div class="set-callout set-callout--muted">
                                    <el-icon :size="18"><Lock /></el-icon>
                                    <div class="set-callout__text">
                                        <strong>Confidentiality Guarantee</strong>
                                        <span>Private financial records, escrow balances, and proprietary data are encrypted and never shown publicly.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="set-danger">
                            <div class="set-danger__head"><el-icon><InfoFilled /></el-icon> Danger Zone</div>
                            <div class="set-danger__body">
                                <div class="set-danger__row">
                                    <div>
                                        <div class="set-danger__label set-danger__label--danger">Delete Account</div>
                                        <div class="set-danger__sub">Permanent deactivation of this login and associated trading access.</div>
                                    </div>
                                    <button type="button" class="set-btn set-btn--danger" @click="deleteAccountInfoOpen = true">Delete Account</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- MARKET DATA (ported from the previous Settings page) -->
                    <template v-else-if="activeTab === 'market'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Market Data</h2>
                                    <p class="set-card__desc">System configuration, pricing, and reference data.</p>
                                </div>
                            </div>
                            <div class="set-card__body">
                                <div class="settings-dashboard">
                                    <section class="settings-column">
                                        <h2 class="settings-column__title"><el-icon><Coin /></el-icon> Currency Exchange Rates</h2>
                                        <div v-if="exchangeRates.length" class="settings-list">
                                            <div v-for="rate in exchangeRates" :key="rate.id" class="settings-list__row">
                                                <div class="settings-list__main">
                                                    <span class="settings-list__label">{{ rate.pair }}</span>
                                                    <span class="settings-list__sub">Rate {{ fmt(rate.rate, 4) }}</span>
                                                </div>
                                                <span class="settings-list__change" :class="changeClass(rate.daily_change_percent)">
                                                    {{ changeText(rate.daily_change_percent) }}
                                                </span>
                                            </div>
                                        </div>
                                        <p v-else class="settings-empty">No exchange rates recorded.</p>
                                    </section>

                                    <section class="settings-column">
                                        <div class="settings-column__head">
                                            <h2 class="settings-column__title"><el-icon><PriceTag /></el-icon> Price Indexes</h2>
                                            <button type="button" class="settings-add-btn" @click="openAddIndex">
                                                <el-icon><Plus /></el-icon> Add
                                            </button>
                                        </div>
                                        <div v-if="priceIndexes.length" class="settings-list">
                                            <div v-for="index in priceIndexes" :key="index.id" class="settings-list__row">
                                                <div class="settings-list__main">
                                                    <span class="settings-list__label">{{ index.item }}</span>
                                                    <span class="settings-list__sub">
                                                        {{ fmt(index.current_price, 2) }}
                                                        <span v-if="index.status" class="settings-list__status">{{ index.status }}</span>
                                                    </span>
                                                </div>
                                                <div class="settings-list__actions">
                                                    <span class="settings-list__change" :class="changeClass(index.percentage_fluctuation)">
                                                        {{ changeText(index.percentage_fluctuation) }}
                                                    </span>
                                                    <button type="button" class="settings-delete-btn" title="Delete" @click="deleteIndex(index)">
                                                        <el-icon><Delete /></el-icon>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-else class="settings-empty">No price indexes recorded.</p>
                                    </section>

                                    <section class="settings-column">
                                        <h2 class="settings-column__title"><el-icon><Van /></el-icon> Others</h2>

                                        <div class="settings-group">
                                            <span class="settings-group__label">Delivery Methods</span>
                                            <div v-if="deliveryMethods.length" class="settings-chips">
                                                <span v-for="method in deliveryMethods" :key="method" class="settings-chip">{{ method }}</span>
                                            </div>
                                            <p v-else class="settings-empty">None recorded.</p>
                                        </div>

                                        <div class="settings-group">
                                            <span class="settings-group__label">Incoterms</span>
                                            <div v-if="incoterms.length" class="settings-chips">
                                                <span v-for="incoterm in incoterms" :key="incoterm" class="settings-chip">{{ incoterm }}</span>
                                            </div>
                                            <p v-else class="settings-empty">None recorded.</p>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- HELP & SUPPORT -->
                    <template v-else-if="activeTab === 'help'">
                        <div class="set-card">
                            <div class="set-card__head">
                                <div>
                                    <h2 class="set-card__title">Help &amp; Support</h2>
                                    <p class="set-card__desc">Access knowledge base guides, support desks, or submit service inquiries.</p>
                                </div>
                            </div>
                            <div class="set-card__body">
                                <div class="set-help-grid">
                                    <div class="set-help-tile">
                                        <el-icon :size="18"><QuestionFilled /></el-icon>
                                        <strong>Help Center &amp; Documentation</strong>
                                        <p>Browse guides on compliance, batch aggregation, and escrow mechanics.</p>
                                    </div>
                                    <a href="mailto:support@beanorigin.example" class="set-help-tile set-help-tile--link">
                                        <el-icon :size="18"><Bell /></el-icon>
                                        <strong>Contact Support</strong>
                                        <p>Direct hotline for escrow clearance and trading floor disputes.</p>
                                    </a>
                                    <a href="mailto:support@beanorigin.example?subject=Technical%20Issue" class="set-help-tile set-help-tile--link">
                                        <el-icon :size="18"><InfoFilled /></el-icon>
                                        <strong>Report a Problem</strong>
                                        <p>Submit technical logs or report a data discrepancy.</p>
                                    </a>
                                    <div class="set-help-tile">
                                        <el-icon :size="18"><Connection /></el-icon>
                                        <strong>Platform Status</strong>
                                        <p>Real-time status of trading engines and registry syncing.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>

        <EditProfileDialog v-model="editProfileOpen" :user="user" :profile="profile" />
        <ChangePasswordModal v-model="changePasswordOpen" />
        <SignOutOtherSessionsModal v-model="signOutOtherOpen" />

        <ConfirmDialog
            v-model="deleteAccountInfoOpen"
            eyebrow="Account"
            title="Delete Account"
            message="Account deletion isn't self-service yet — email support@beanorigin.example from your registered address and our team will process the deactivation after reviewing any open escrow balances or contracts."
            confirm-text="Got it"
            :show-cancel="false"
        />

        <!-- ── Market Data: Add Price Index modal (ported as-is) ─────────── -->
        <el-dialog
            v-model="addIndexOpen"
            width="min(460px, calc(100vw - 2rem))"
            align-center
            :close-on-click-modal="false"
            :show-close="false"
            class="pim-modal"
        >
            <template #header>
                <div class="pim-modal__head">
                    <div class="pim-modal__head-icon">
                        <el-icon :size="18"><PriceTag /></el-icon>
                    </div>
                    <div class="pim-modal__head-text">
                        <div class="pim-modal__eyebrow">Settings</div>
                        <div class="pim-modal__title">Add Price Index</div>
                    </div>
                    <button type="button" class="pim-modal__close" aria-label="Close" @click="addIndexOpen = false">
                        <el-icon :size="14"><Delete /></el-icon>
                    </button>
                </div>
            </template>

            <div class="pim-modal__body">
                <div class="pim-grid">
                    <div class="pim-field pim-field--span2">
                        <label class="pim-field__label">Item</label>
                        <el-input v-model="indexForm.item" placeholder="e.g. Uganda Robusta" class="pim-input" :class="{ 'pim-input--error': indexForm.errors.item }" />
                        <span v-if="indexForm.errors.item" class="pim-field__error">{{ indexForm.errors.item }}</span>
                    </div>
                    <div class="pim-field">
                        <label class="pim-field__label">Current Price</label>
                        <el-input-number v-model="indexForm.current_price" :min="0" :precision="2" class="pim-input w-100" :class="{ 'pim-input--error': indexForm.errors.current_price }" />
                        <span v-if="indexForm.errors.current_price" class="pim-field__error">{{ indexForm.errors.current_price }}</span>
                    </div>
                    <div class="pim-field">
                        <label class="pim-field__label">Fluctuation %</label>
                        <el-input-number v-model="indexForm.percentage_fluctuation" :precision="2" class="pim-input w-100" :class="{ 'pim-input--error': indexForm.errors.percentage_fluctuation }" />
                        <span v-if="indexForm.errors.percentage_fluctuation" class="pim-field__error">{{ indexForm.errors.percentage_fluctuation }}</span>
                    </div>
                    <div class="pim-field pim-field--span2">
                        <label class="pim-field__label">Status</label>
                        <el-select v-model="indexForm.status" class="pim-input w-100" :class="{ 'pim-input--error': indexForm.errors.status }">
                            <el-option label="Active" value="active" />
                            <el-option label="Inactive" value="inactive" />
                        </el-select>
                        <span v-if="indexForm.errors.status" class="pim-field__error">{{ indexForm.errors.status }}</span>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="pim-modal__footer">
                    <button type="button" class="pim-btn-primary" :disabled="indexForm.processing" @click="submitIndex">
                        {{ indexForm.processing ? 'Saving…' : 'Save' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="confirmDeleteOpen"
            eyebrow="Settings"
            title="Delete Price Index"
            :message="pendingDeleteIndex ? `Delete “${pendingDeleteIndex.item}”? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDeleteIndex"
        />
    </MainLayout>
</template>

<style>
.el-dialog.pim-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: var(--dp-font-sans);
}
.el-dialog.pim-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.pim-modal .el-dialog__body { padding: 0; }
.el-dialog.pim-modal .el-dialog__footer { padding: 0; }
</style>

<style scoped>
.set-page { display: flex; flex-direction: column; gap: 22px; font-family: var(--dp-font-sans); }
.set-header h1 { color: var(--dp-primary); }
.set-subtitle { font-size: .9375rem; color: var(--dp-on-surface-variant); margin: 4px 0 0; }

.set-layout { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 22px; align-items: start; }

/* ── Sidebar ─────────────────────────────────────────────────────────── */
.set-nav {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 8px;
    overflow: hidden;
    position: sticky;
    top: 16px;
}
.set-nav__header {
    padding: 14px 18px;
    background: var(--dp-surface-container-low);
    border-bottom: 1px solid var(--dp-outline-variant);
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--dp-on-surface-variant);
}
.set-nav__btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 11px 16px;
    border: none;
    background: transparent;
    text-align: left;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--dp-on-surface-variant);
    border-bottom: 1px solid var(--dp-outline-variant);
    cursor: pointer;
    transition: background .15s ease, color .15s ease;
}
.set-nav__btn:last-child { border-bottom: none; }
.set-nav__left { display: inline-flex; align-items: center; gap: 10px; }
.set-nav__chevron { color: var(--dp-outline); }
.set-nav__btn:hover { background: var(--dp-surface-container-low); color: var(--dp-on-surface); }
.set-nav__btn--active { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); font-weight: 700; }
.set-nav__btn--active .set-nav__chevron { color: var(--dp-on-secondary-container); }

/* ── Card shell ──────────────────────────────────────────────────────── */
.set-content { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.set-card {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 8px;
    overflow: hidden;
}
.set-card__head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 22px; border-bottom: 1px solid var(--dp-outline-variant); }
.set-card__title { font-size: 15px; font-weight: 700; color: var(--dp-on-surface); margin: 0 0 3px; }
.set-card__desc { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0; }
.set-card__body { padding: 22px; display: flex; flex-direction: column; gap: 18px; }

.set-note { display: flex; align-items: center; gap: 7px; font-size: 12px; color: var(--dp-on-surface-variant); background: var(--dp-surface-container-low); padding: 9px 12px; border-radius: 8px; margin: 0; }

.set-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 30px 16px; text-align: center; color: var(--dp-outline); }
.set-empty p { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0; max-width: 40ch; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.set-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    height: 34px; padding: 0 14px; border: none; border-radius: 8px;
    font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none;
    transition: opacity .15s ease, background .15s ease; white-space: nowrap;
    flex-shrink: 0;
}
.set-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.set-btn--primary:hover:not(:disabled) { opacity: .88; }
.set-btn--outline { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); box-shadow: inset 0 0 0 1px var(--dp-outline-variant); }
.set-btn--outline:hover:not(:disabled) { background: var(--dp-surface-container-low); }
.set-btn--danger-outline { color: var(--dp-error); }
.set-btn--danger-outline:hover:not(:disabled) { background: var(--dp-error-container); }
.set-btn--danger { background: var(--dp-error); color: var(--dp-on-error); }
.set-btn--danger:hover:not(:disabled) { opacity: .88; }
.set-btn--sm { height: 28px; padding: 0 10px; font-size: 11px; }
.set-btn:disabled { opacity: .5; cursor: default; }

/* ── Identity row (Account) ─────────────────────────────────────────── */
.set-identity { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.set-identity__avatar {
    width: 60px; height: 60px; border-radius: 50%; overflow: hidden;
    background: linear-gradient(135deg, var(--dp-secondary-container), var(--dp-secondary-fixed));
    color: var(--dp-on-secondary-container);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 800; flex-shrink: 0;
}
.set-identity__avatar img { width: 100%; height: 100%; object-fit: cover; }
.set-identity__text { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 2px; }
.set-identity__name { font-size: 15px; font-weight: 800; color: var(--dp-on-surface); }
.set-identity__meta { font-size: 12px; color: var(--dp-on-surface-variant); }

/* ── Danger zone ─────────────────────────────────────────────────────── */
.set-danger { border: 1px solid var(--dp-error-container); border-radius: 8px; overflow: hidden; background: var(--dp-surface-container-lowest); }
.set-danger__head { display: flex; align-items: center; gap: 8px; padding: 12px 20px; background: var(--dp-error-container); color: var(--dp-error); font-size: 12.5px; font-weight: 700; }
.set-danger__body { padding: 18px 22px; display: flex; flex-direction: column; gap: 16px; }
.set-danger__row { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.set-danger__row + .set-danger__row { padding-top: 16px; border-top: 1px solid var(--dp-outline-variant); }
.set-danger__label { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.set-danger__label--danger { color: var(--dp-error); }
.set-danger__sub { font-size: 11.5px; color: var(--dp-on-surface-variant); margin-top: 2px; }

/* ── Callout ─────────────────────────────────────────────────────────── */
.set-callout { display: flex; align-items: center; gap: 14px; padding: 16px; border-radius: 8px; background: var(--dp-surface-container-low); flex-wrap: wrap; }
.set-callout--muted { background: var(--dp-surface-container-low); }
.set-callout .el-icon { color: var(--dp-primary); flex-shrink: 0; }
.set-callout__text { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 3px; }
.set-callout__text strong { font-size: 13px; color: var(--dp-on-surface); }
.set-callout__text span { font-size: 12px; color: var(--dp-on-surface-variant); }

/* ── Generic field/pill ──────────────────────────────────────────────── */
.set-field { display: flex; flex-direction: column; gap: 6px; }
.set-field__label { font-size: 11.5px; font-weight: 700; color: var(--dp-on-surface); }
.set-select { width: 100%; }
.set-select :deep(.el-select__wrapper), .set-select :deep(.el-input__wrapper) { border-radius: 8px; }
.set-pref-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }

.set-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.set-pill--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.set-pill--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.set-pill--primary { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }

/* ── Notifications / privacy rows ───────────────────────────────────── */
.set-notif-group + .set-notif-group { margin-top: 6px; }
.set-notif-group__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.set-notif-group__title { display: inline-flex; align-items: center; gap: 7px; font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.set-notif-group__cols { font-size: 10.5px; font-family: var(--dp-font-mono); color: var(--dp-on-surface-variant); }
.set-notif-list { border: 1px solid var(--dp-outline-variant); border-radius: 8px; overflow: hidden; }
.set-notif-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 11px 14px; border-bottom: 1px solid var(--dp-outline-variant); }
.set-notif-row:last-child { border-bottom: none; }
.set-notif-row__text { display: flex; flex-direction: column; gap: 2px; }
.set-notif-row__text span { font-size: 12.5px; font-weight: 600; color: var(--dp-on-surface); }
.set-notif-row__text small { font-size: 11px; color: var(--dp-on-surface-variant); }
.set-notif-row__toggles { display: flex; gap: 18px; flex-shrink: 0; }
.set-notif-row input[type="checkbox"], .set-notif-row__toggles input[type="checkbox"] { accent-color: var(--dp-primary); width: 15px; height: 15px; }

/* ── Security ────────────────────────────────────────────────────────── */
.set-sec-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding-bottom: 16px; border-bottom: 1px solid var(--dp-outline-variant); }
.set-sec-row:last-child { border-bottom: none; padding-bottom: 0; }
.set-sec-row--block { flex-direction: column; align-items: stretch; }
.set-sec-row__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.set-sec-row__title { font-size: 13.5px; font-weight: 700; color: var(--dp-on-surface); }
.set-sec-row__sub { font-size: 11.5px; color: var(--dp-on-surface-variant); margin-top: 2px; }

.set-sessions { display: flex; flex-direction: column; gap: 4px; }
.set-session-row { display: flex; align-items: center; gap: 12px; padding: 9px 4px; }
.set-session-row__icon { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: var(--dp-surface-container-low); color: var(--dp-outline); flex-shrink: 0; }
.set-session-row__body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 1px; }
.set-session-row__device { font-size: 12.5px; font-weight: 700; color: var(--dp-on-surface); }
.set-session-row__meta { font-size: 11px; color: var(--dp-on-surface-variant); }

/* ── Integrations ────────────────────────────────────────────────────── */
.set-integrations { display: flex; flex-direction: column; gap: 4px; }
.set-integration-row { display: flex; align-items: center; gap: 12px; padding: 12px 4px; border-bottom: 1px solid var(--dp-outline-variant); flex-wrap: wrap; }
.set-integration-row:last-child { border-bottom: none; }
.set-integration-row__icon { width: 38px; height: 38px; border-radius: 8px; background: var(--dp-surface-container-low); color: var(--dp-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.set-integration-row__text { flex: 1; min-width: 180px; display: flex; flex-direction: column; gap: 2px; }
.set-integration-row__text span { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.set-integration-row__text small { font-size: 11.5px; color: var(--dp-on-surface-variant); }

/* ── Help & Support ──────────────────────────────────────────────────── */
.set-help-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.set-help-tile { display: flex; flex-direction: column; gap: 6px; padding: 16px; border-radius: 8px; background: var(--dp-surface-container-low); text-decoration: none; color: inherit; }
.set-help-tile .el-icon { color: var(--dp-primary); }
.set-help-tile strong { font-size: 13.5px; color: var(--dp-on-surface); }
.set-help-tile p { font-size: 12px; color: var(--dp-on-surface-variant); margin: 0; }
.set-help-tile--link:hover { background: var(--dp-surface-container-high); }

/* ── Market Data (ported classes) ───────────────────────────────────── */
.settings-dashboard { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.settings-column { background: var(--dp-surface); border: 1px solid var(--dp-outline-variant); border-radius: 8px; padding: 16px; }
.settings-column__title { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-outline); margin: 0 0 12px; }
.settings-column__title .el-icon { font-size: 15px; }
.settings-column__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 0 0 12px; }
.settings-column__head .settings-column__title { margin: 0; }
.settings-add-btn { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 10px; border: 1px solid var(--dp-outline-variant); border-radius: 6px; background: var(--dp-surface); color: var(--dp-on-surface-variant); font-size: 12px; font-weight: 600; cursor: pointer; }
.settings-add-btn:hover { background: var(--dp-surface-container-low); }
.settings-list { display: flex; flex-direction: column; }
.settings-list__row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-top: 1px solid var(--dp-outline-variant); }
.settings-list__row:first-child { border-top: none; }
.settings-list__main { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.settings-list__label { font-size: 13.5px; font-weight: 600; color: var(--dp-on-surface); }
.settings-list__sub { font-size: 12px; color: var(--dp-on-surface-variant); display: flex; align-items: center; gap: 6px; }
.settings-list__status { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); background: var(--dp-surface-container-high); padding: 1px 6px; border-radius: 999px; }
.settings-list__change { font-size: 12.5px; font-weight: 700; }
.settings-list__change.is-up { color: #16A34A; }
.settings-list__change.is-down { color: var(--dp-error); }
.settings-list__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.settings-delete-btn { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border: none; border-radius: 6px; background: transparent; color: var(--dp-on-surface-variant); cursor: pointer; }
.settings-delete-btn:hover { background: var(--dp-error-container); color: var(--dp-error); }
.settings-group + .settings-group { margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--dp-outline-variant); }
.settings-group__label { font-size: 12px; font-weight: 700; color: var(--dp-on-surface); }
.settings-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.settings-chip { font-size: 11.5px; font-weight: 600; color: var(--dp-on-surface-variant); background: var(--dp-surface-container-high); padding: 4px 10px; border-radius: 999px; }
.settings-empty { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0; }

.pim-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.pim-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pim-modal__head-text { flex: 1; min-width: 0; }
.pim-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6F7677; margin-bottom: 1px; }
.pim-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.pim-modal__close { width: 28px; height: 28px; border-radius: 6px; border: none; background: #F1F2F3; color: #4B5457; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.12s; }
.pim-modal__close:hover { background: #E5E7EB; color: #121516; }
.pim-modal__body { padding: 22px 24px 8px; }
.pim-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.pim-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; margin-bottom: 16px; }
.pim-field--span2 { grid-column: span 2; }
.pim-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.pim-field__error { font-size: 12px; font-weight: 500; color: #F85149; line-height: 1.4; }
.pim-input { width: 100%; }
.pim-input :deep(.el-input__wrapper), .pim-input :deep(.el-select__wrapper), .pim-input :deep(.el-textarea__inner) { border-radius: 6px; }
.pim-input--error :deep(.el-input__wrapper), .pim-input--error :deep(.el-select__wrapper), .pim-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }
.pim-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #F5F6F7; border-top: 1px solid #E5E7EB; }
.pim-btn-primary { display: inline-flex; align-items: center; justify-content: center; height: 36px; padding: 0 16px; background: #000000; border: 1px solid transparent; color: #fff; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity 0.15s ease; }
.pim-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.pim-btn-primary:disabled { opacity: 0.5; cursor: default; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .set-layout { grid-template-columns: 1fr; }
    .set-nav { position: static; }
}
@media (max-width: 900px) {
    .settings-dashboard { grid-template-columns: 1fr; }
    .set-help-grid, .set-pref-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .pim-grid { grid-template-columns: 1fr; }
    .pim-field--span2 { grid-column: span 1; }
}
</style>
