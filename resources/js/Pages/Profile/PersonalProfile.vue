<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CircleCheckFilled, Close, Edit, Location, Lock, MagicStick,
    Message, Monitor, Phone, User,
} from '@element-plus/icons-vue';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import EditProfileDialog from '@/Components/Modals/EditProfileDialog.vue';
import { resolveIcon } from '@/utils/icon';

const props = defineProps({
    sessions: { type: Array, default: () => [] },
});

const page = usePage();

/* ── Real display data — every value below comes straight from a genuine
   user / profile / session field; nothing here is invented to fill a
   layout slot. ─────────────────────────────────────────────────────── */
const user = computed(() => page.props.auth.user ?? {});
const profile = computed(() => user.value.profile ?? {});

const fullName = computed(() => user.value.name || 'Profile Owner');
const roleLabel = computed(() => {
    if (!user.value.role) return '';
    return String(user.value.role).replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
});
const emailVerified = computed(() => Boolean(user.value.email_verified_at));
const twoFactorEnabled = computed(() => Boolean(user.value.two_factor_enabled));
const locationLabel = computed(() => [profile.value.city, profile.value.state, profile.value.country].filter(Boolean).join(', '));
const memberSince = computed(() => {
    const source = user.value.created_at ? new Date(user.value.created_at) : null;
    return source && !Number.isNaN(source.getTime()) ? String(source.getFullYear()) : '—';
});

const completionPercentage = computed(() => {
    const fields = [
        user.value.first_name, user.value.last_name, user.value.email, user.value.telephone, user.value.role,
        profile.value.bio, profile.value.date_of_birth, profile.value.gender,
        profile.value.address_line_1, profile.value.city, profile.value.state, profile.value.country,
    ];
    const completed = fields.filter((value) => String(value || '').trim() !== '').length;

    return Math.round((completed / fields.length) * 100);
});

/* ── Settlement currency ─────────────────────────────────────────────── */
const currencyOptions = computed(() => page.props.currencies ?? []);
const currentCurrency = computed(() => currencyOptions.value.find((c) => c.code === user.value.currency_code) ?? null);
const currencyForm = useForm({ currency_code: user.value.currency_code || '' });

function submitCurrency(code) {
    if (!code || code === user.value.currency_code) return;
    currencyForm.currency_code = code;
    currencyForm.post(route('profile.currency'), { preserveScroll: true });
}

/* ── Edit profile ────────────────────────────────────────────────────── */
const editProfileOpen = ref(false);

/* ── Subscribed agents — shared on every Inertia request; admin accounts
   always receive an empty list here since they manage agents from the
   Apps page instead of subscribing. ─────────────────────────────────── */
const isAdmin = computed(() => user.value?.role === 'admin');
const subscribedAgents = computed(() => page.props.subscribedAgents ?? []);

const unsubscribingId = ref(null);
const unsubscribeOpen = ref(false);
const agentToUnsubscribe = ref(null);

function openUnsubscribeDialog(agent) {
    agentToUnsubscribe.value = agent;
    unsubscribeOpen.value = true;
}

function confirmUnsubscribe() {
    const agent = agentToUnsubscribe.value;
    if (!agent || unsubscribingId.value) return;

    unsubscribingId.value = agent.id;
    router.delete(route('agent.unsubscribe', agent.id), {
        preserveScroll: true,
        onFinish: () => {
            unsubscribingId.value = null;
            unsubscribeOpen.value = false;
            agentToUnsubscribe.value = null;
        },
    });
}

function sessionDeviceLabel(session) {
    const platform = session.agent?.platform;
    const browser = session.agent?.browser;

    return [browser, platform].filter(Boolean).join(' · ') || 'Unknown device';
}

const sessionsPreview = computed(() => props.sessions.slice(0, 5));
const extraSessionsCount = computed(() => Math.max(props.sessions.length - sessionsPreview.value.length, 0));
</script>

<template>
    <DesignPreviewLayout title="Profile">
        <Head title="Profile" />

        <div class="pp-page">
            <div class="pp-hero">
                <div class="pp-hero__text">
                    <h1 class="dp-display-md">User Profile</h1>
                    <p class="pp-subtitle">Member since {{ memberSince }}</p>
                </div>
                <div class="pp-hero__actions">
                    <button type="button" class="pp-btn pp-btn--outline" @click="editProfileOpen = true">
                        <el-icon :size="14"><Edit /></el-icon> Edit Profile
                    </button>
                </div>
            </div>

            <div class="pp-stack">
                <!-- ── Identity ───────────────────────────────────────── -->
                <div class="pp-card pp-identity">
                    <div class="pp-identity__avatar">
                        <img v-if="profile.profile_photo_url" :src="profile.profile_photo_url" :alt="fullName">
                        <el-icon v-else :size="30"><User /></el-icon>
                    </div>
                    <div class="pp-identity__body">
                        <h2 class="pp-identity__name">{{ fullName }}</h2>
                        <p v-if="roleLabel" class="pp-identity__role">{{ roleLabel }}</p>
                        <p class="pp-identity__bio">{{ profile.bio || 'No bio added yet.' }}</p>
                        <div class="pp-identity__contacts">
                            <span class="pp-identity__contact"><el-icon :size="14"><Message /></el-icon> {{ user.email || '—' }}</span>
                            <span class="pp-identity__contact"><el-icon :size="14"><Phone /></el-icon> {{ user.telephone || '—' }}</span>
                            <span v-if="locationLabel" class="pp-identity__contact"><el-icon :size="14"><Location /></el-icon> {{ locationLabel }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── Account Status + Currency + Quick Actions ──────── -->
                <div class="pp-trio">
                    <div class="pp-card">
                        <div class="pp-card-head">
                            <h2 class="pp-card-title"><el-icon><Lock /></el-icon> Account Status</h2>
                        </div>
                        <div class="pp-status-rows">
                            <div class="pp-status-row">
                                <span class="pp-status-row__label"><el-icon :size="14"><Message /></el-icon> Email Verification</span>
                                <span class="pp-status-pill" :class="`pp-status-pill--${emailVerified ? 'green' : 'amber'}`">{{ emailVerified ? 'Verified' : 'Pending' }}</span>
                            </div>
                            <div class="pp-status-row">
                                <span class="pp-status-row__label"><el-icon :size="14"><Lock /></el-icon> 2FA Status</span>
                                <span class="pp-status-pill" :class="`pp-status-pill--${twoFactorEnabled ? 'green' : 'amber'}`">{{ twoFactorEnabled ? 'Enabled' : 'Off' }}</span>
                            </div>
                            <div class="pp-status-row">
                                <span class="pp-status-row__label"><el-icon :size="14"><CircleCheckFilled /></el-icon> Profile Completion</span>
                                <span class="pp-status-pill" :class="`pp-status-pill--${completionPercentage >= 85 ? 'green' : 'muted'}`">{{ completionPercentage }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="pp-card">
                        <div class="pp-card-head">
                            <h2 class="pp-card-title">Settlement Currency</h2>
                        </div>
                        <el-select
                            v-model="currencyForm.currency_code"
                            filterable
                            placeholder="Select currency"
                            :disabled="currencyForm.processing"
                            class="pp-currency-select"
                            @change="submitCurrency"
                        >
                            <el-option
                                v-for="option in currencyOptions"
                                :key="option.code"
                                :label="`${option.code} — ${option.name}`"
                                :value="option.code"
                            />
                        </el-select>
                        <p class="pp-card-note">{{ currentCurrency ? `${currentCurrency.symbol} · ${currentCurrency.name}` : `Choose from ${currencyOptions.length} available currencies` }}</p>
                    </div>

                    <div class="pp-card">
                        <h2 class="pp-card-title">Quick Actions</h2>
                        <div class="pp-quick-actions">
                            <button type="button" class="pp-quick-action" @click="editProfileOpen = true">
                                <span class="pp-quick-action__icon"><el-icon :size="16"><Edit /></el-icon></span>
                                <span class="pp-quick-action__label">Edit Information</span>
                            </button>
                            <Link :href="route('apps.index')" class="pp-quick-action">
                                <span class="pp-quick-action__icon"><el-icon :size="16"><MagicStick /></el-icon></span>
                                <span class="pp-quick-action__label">Browse Agents</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- ── Active Sessions + Subscribed Agents ────────────── -->
                <div class="pp-pair">
                    <div class="pp-card">
                        <div class="pp-card-head">
                            <h2 class="pp-card-title"><el-icon><Monitor /></el-icon> Active Sessions</h2>
                        </div>
                        <div v-if="sessions.length" class="pp-sessions">
                            <div v-for="(session, index) in sessionsPreview" :key="index" class="pp-session-row">
                                <span class="pp-session-row__icon"><el-icon :size="16"><Monitor /></el-icon></span>
                                <div class="pp-session-row__body">
                                    <span class="pp-session-row__device">{{ sessionDeviceLabel(session) }}</span>
                                    <span class="pp-session-row__meta">{{ session.ip_address || 'Unknown IP' }} · {{ session.last_active }}</span>
                                </div>
                                <span v-if="session.is_current_device" class="pp-status-pill pp-status-pill--primary">This device</span>
                            </div>
                            <p v-if="extraSessionsCount > 0" class="pp-card-note pp-sessions__more">+{{ extraSessionsCount }} more session{{ extraSessionsCount === 1 ? '' : 's' }}</p>
                        </div>
                        <div v-else class="pp-empty">
                            <el-icon :size="20"><Monitor /></el-icon>
                            <p>No active sessions recorded.</p>
                        </div>
                    </div>

                    <div class="pp-card">
                        <div class="pp-card-head">
                            <h2 class="pp-card-title"><el-icon><MagicStick /></el-icon> Subscribed Agents</h2>
                            <Link :href="route('apps.index')" class="pp-card-action">Browse Agents</Link>
                        </div>

                        <div v-if="isAdmin" class="pp-empty">
                            <el-icon :size="20"><MagicStick /></el-icon>
                            <p>Admin accounts manage agents directly from the Apps page.</p>
                        </div>
                        <div v-else-if="!subscribedAgents.length" class="pp-empty">
                            <el-icon :size="20"><MagicStick /></el-icon>
                            <p>You haven't subscribed to any agents yet.</p>
                        </div>
                        <div v-else class="pp-agents">
                            <div v-for="agent in subscribedAgents" :key="agent.id" class="pp-agent-row">
                                <span class="pp-agent-row__icon"><el-icon :size="16"><component :is="resolveIcon(agent.icon)" /></el-icon></span>
                                <div class="pp-agent-row__body">
                                    <span class="pp-agent-row__name">{{ agent.name }}</span>
                                    <div v-if="agent.functions?.length" class="pp-agent-row__functions">
                                        <span v-for="fn in agent.functions.slice(0, 3)" :key="fn.id" class="pp-chip">{{ fn.name }}</span>
                                        <span v-if="agent.functions.length > 3" class="pp-chip pp-chip--muted">+{{ agent.functions.length - 3 }} more</span>
                                    </div>
                                </div>
                                <button type="button" class="pp-icon-btn pp-icon-btn--danger" title="Unsubscribe" :disabled="unsubscribingId === agent.id" @click="openUnsubscribeDialog(agent)">
                                    <el-icon :size="14"><Close /></el-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <EditProfileDialog v-model="editProfileOpen" :user="user" :profile="profile" />

        <!-- ── Unsubscribe Agent modal ─────────────────────────────────── -->
        <el-dialog v-model="unsubscribeOpen" width="min(440px, calc(100vw - 2rem))" align-center class="pp-modal pp-modal--danger">
            <template #header>
                <div class="pp-modal__head">
                    <div class="pp-modal__head-icon pp-modal__head-icon--danger"><el-icon :size="18"><Close /></el-icon></div>
                    <div class="pp-modal__head-text">
                        <div class="pp-modal__eyebrow">Subscribed Agents</div>
                        <div class="pp-modal__title">Unsubscribe Agent</div>
                    </div>
                </div>
            </template>
            <div v-if="agentToUnsubscribe" class="pp-modal__body">
                <p class="pp-modal__confirm-text">Unsubscribe from <strong>{{ agentToUnsubscribe.name }}</strong>? You'll lose access to its functions until you subscribe again.</p>
            </div>
            <template #footer>
                <div class="pp-modal__footer">
                    <button type="button" class="pp-btn pp-btn--outline" @click="unsubscribeOpen = false">Cancel</button>
                    <button type="button" class="pp-btn pp-btn--danger" :disabled="unsubscribingId === agentToUnsubscribe?.id" @click="confirmUnsubscribe">
                        {{ unsubscribingId === agentToUnsubscribe?.id ? 'Unsubscribing…' : 'Unsubscribe' }}
                    </button>
                </div>
            </template>
        </el-dialog>
    </DesignPreviewLayout>
</template>

<style scoped>
.pp-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
    font-family: var(--dp-font-sans);
}

/* ── Hero ────────────────────────────────────────────────────────────── */
.pp-hero { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
.pp-hero__text h1 { color: var(--dp-primary); }
.pp-subtitle { font-size: 14px; line-height: 1.6; color: var(--dp-on-surface-variant); margin: 8px 0 0; }
.pp-hero__actions { display: flex; gap: 10px; flex-shrink: 0; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.pp-btn {
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
    transition: background 0.15s ease;
}
.pp-btn--outline { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); box-shadow: var(--dp-card-shadow); }
.pp-btn--outline:hover { background: var(--dp-surface-container-low); }
.pp-btn--danger { background: var(--dp-error); color: var(--dp-on-error); }
.pp-btn--danger:disabled { opacity: 0.6; cursor: default; }
.pp-btn:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

.pp-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 9px;
    border: none;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
    flex-shrink: 0;
}
.pp-icon-btn:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.pp-icon-btn--danger:hover { background: var(--dp-error-container); color: var(--dp-error); }
.pp-icon-btn:disabled { opacity: 0.5; cursor: default; }

/* ── Layout ──────────────────────────────────────────────────────────── */
.pp-stack { display: flex; flex-direction: column; gap: 20px; }
.pp-pair { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; align-items: stretch; }
.pp-pair > .pp-card { height: 100%; display: flex; flex-direction: column; }
.pp-trio { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; align-items: stretch; }
.pp-trio > .pp-card { height: 100%; display: flex; flex-direction: column; }

.pp-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    padding: 22px;
}
.pp-card-title {
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
.pp-card-title .el-icon { color: var(--dp-outline); font-size: 15px; }
.pp-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.pp-card-head .pp-card-title { margin-bottom: 0; }
.pp-card-action { font-size: 12px; font-weight: 700; color: var(--dp-primary); text-decoration: none; }
.pp-card-action:hover { text-decoration: underline; }
.pp-card-note { margin: 10px 0 0; font-size: 12px; color: var(--dp-on-surface-variant); }
.pp-sessions__more { padding: 4px 6px 0; }

.pp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 28px 16px;
    text-align: center;
    color: var(--dp-outline);
    flex: 1;
}
.pp-empty p { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0; max-width: 34ch; }

/* ── Identity ────────────────────────────────────────────────────────── */
.pp-identity { display: flex; gap: 20px; align-items: flex-start; }
.pp-identity__avatar {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--dp-secondary-container), var(--dp-secondary-fixed));
    color: var(--dp-on-secondary-container);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}
.pp-identity__avatar img { width: 100%; height: 100%; object-fit: cover; }
.pp-identity__body { min-width: 0; }
.pp-identity__name { margin: 0; font-size: 19px; font-weight: 800; color: var(--dp-on-surface); letter-spacing: -0.01em; }
.pp-identity__role { margin: 5px 0 0; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-secondary); }
.pp-identity__bio { margin: 12px 0 0; font-size: 13.5px; line-height: 1.6; color: var(--dp-on-surface-variant); max-width: 60ch; }
.pp-identity__contacts { display: flex; flex-wrap: wrap; gap: 8px 18px; margin-top: 14px; }
.pp-identity__contact { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; color: var(--dp-on-surface-variant); }
.pp-identity__contact .el-icon { color: var(--dp-outline); }

/* ── Account status ──────────────────────────────────────────────────── */
.pp-status-rows { display: flex; flex-direction: column; gap: 10px; }
.pp-status-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 2px; }
.pp-status-row__label { display: inline-flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 600; color: var(--dp-on-surface); }
.pp-status-row__label .el-icon { color: var(--dp-outline); }

.pp-status-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 700;
    white-space: nowrap;
}
.pp-status-pill--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.pp-status-pill--amber { background: #fef3c7; color: #92400e; }
.pp-status-pill--primary { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.pp-status-pill--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.pp-currency-select { width: 100%; }
.pp-currency-select :deep(.el-select__wrapper) { border-radius: 10px; min-height: 40px; }

/* ── Quick actions ───────────────────────────────────────────────────── */
.pp-quick-actions { display: flex; flex-direction: column; gap: 6px; }
.pp-quick-action {
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
.pp-quick-action:hover { background: var(--dp-surface-container-low); border-color: var(--dp-outline-variant); }
.pp-quick-action__icon { color: var(--dp-outline); display: inline-flex; }
.pp-quick-action:hover .pp-quick-action__icon { color: var(--dp-primary); }
.pp-quick-action__label { font-size: 13.5px; font-weight: 700; }

/* ── Sessions ────────────────────────────────────────────────────────── */
.pp-sessions { display: flex; flex-direction: column; gap: 4px; }
.pp-session-row { display: flex; align-items: center; gap: 12px; padding: 10px 6px; border-radius: 10px; }
.pp-session-row__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    flex-shrink: 0;
}
.pp-session-row__body { min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 2px; }
.pp-session-row__device { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.pp-session-row__meta { font-size: 11.5px; color: var(--dp-on-surface-variant); }

/* ── Agents ──────────────────────────────────────────────────────────── */
.pp-agents { display: flex; flex-direction: column; gap: 4px; }
.pp-agent-row { display: flex; align-items: center; gap: 12px; padding: 10px 6px; border-radius: 10px; }
.pp-agent-row__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--dp-surface-container-low);
    color: var(--dp-primary);
    flex-shrink: 0;
}
.pp-agent-row__body { min-width: 0; flex: 1; }
.pp-agent-row__name { display: block; font-size: 13px; font-weight: 700; color: var(--dp-on-surface); }
.pp-agent-row__functions { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; }
.pp-chip {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 999px;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
    font-size: 10.5px;
    font-weight: 700;
}
.pp-chip--muted { background: transparent; color: var(--dp-outline); }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .pp-trio { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 900px) {
    .pp-pair,
    .pp-trio { grid-template-columns: 1fr; }
    .pp-identity { flex-direction: column; align-items: flex-start; }
}

/* ── Modals — el-dialog teleports to <body>, outside .dp-shell, so
   --dp-* custom properties don't cascade in; literal hex from the same
   palette is used here, matching this app's other teleported dialogs. */
</style>

<style>
.el-dialog.pp-modal { border-radius: 18px; padding: 0; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
.el-dialog.pp-modal .el-dialog__header { padding: 0; margin: 0; }
.el-dialog.pp-modal .el-dialog__body { padding: 0; }
.el-dialog.pp-modal .el-dialog__footer { padding: 0; }

.pp-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #f3f4f6; }
.pp-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(39, 19, 16, 0.08); color: #271310; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pp-modal__head-icon--danger { background: #fee2e2; color: #b91c1c; }
.pp-modal__head-text { flex: 1; min-width: 0; }
.pp-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #271310; margin-bottom: 1px; }
.pp-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -0.01em; }
.pp-modal__body { padding: 22px 24px; }
.pp-modal__confirm-text { margin: 0; font-size: 0.875rem; color: #374151; line-height: 1.6; }
.pp-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

.pp-modal .pp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 38px; padding: 0 18px; border: none; border-radius: 999px; font-size: 12.5px; font-weight: 700; cursor: pointer; }
.pp-modal .pp-btn--outline { background: #fff; border: 1px solid #e5e7eb; color: #111827; }
.pp-modal .pp-btn--outline:hover { background: #f8fafc; }
.pp-modal .pp-btn--danger { background: #ba1a1a; color: #fff; }
.pp-modal .pp-btn--danger:disabled { opacity: 0.6; cursor: default; }
</style>
