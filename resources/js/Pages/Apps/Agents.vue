<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import {
    CircleCheck, Close, Connection, Download, Plus, Setting, StarFilled,
} from '@element-plus/icons-vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { resolveIcon } from '@/utils/icon';

const props = defineProps({
    agents: { type: Array, default: () => [] },
    subscribedAgentIds: { type: Array, default: () => [] },
    canCreateAgent: { type: Boolean, default: false },
});

function notify(message) {
    ElNotification({ message, type: 'success', duration: 2800, offset: 84 });
}

const isSubscribed = (agentId) => props.subscribedAgentIds.includes(agentId);

/* Icon "tone" is purely a display accent with no backing field on Agent —
   rotated deterministically by id so tiles stay visually varied, same as
   the mockup's own arbitrary per-card tones. */
const tones = ['emerald', 'blue', 'green', 'amber', 'indigo'];
const toneFor = (agentId) => tones[(agentId ?? 0) % tones.length];

function formatType(type) {
    if (!type) return 'General';
    return String(type).replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/* ── My Apps table (real subscriptions) ───────────────────────────────── */
const myApps = computed(() => props.agents.filter((a) => isSubscribed(a.id)));

/* ── Subscribe / unsubscribe (real) ────────────────────────────────────── */
const subscribing = ref(null);

function toggleSubscription(agent) {
    subscribing.value = agent.id;
    const options = { preserveScroll: true, onFinish: () => { subscribing.value = null; } };

    if (isSubscribed(agent.id)) {
        router.delete(route('agent.unsubscribe', agent.id), options);
    } else {
        router.post(route('agent.subscribe', agent.id), {}, options);
    }
}

/* ── Details modal (real agent + real functions) ──────────────────────── */
const detailsOpen = ref(false);
const detailsApp = ref(null);

function openDetails(app) {
    detailsApp.value = app;
    detailsOpen.value = true;
}

/* Admins have a real per-agent management page (agent.show — Gate-checked
   admin-only, since it's an edit/delete/functions-CRUD screen, not a
   general "app" view). Clicking an app tile takes them straight there.
   Everyone else has no such page, so the details modal remains their
   closest real equivalent (description + real functions + subscribe). */
function handleAppClick(app) {
    if (props.canCreateAgent) {
        router.visit(route('agent.show', app.id));
        return;
    }
    openDetails(app);
}

/* ── Install (subscribe) confirmation modal ───────────────────────────── */
const installOpen = ref(false);
const installApp = ref(null);

function openInstall(app) {
    installApp.value = app;
    installOpen.value = true;
}

function confirmInstall() {
    toggleSubscription(installApp.value);
    installOpen.value = false;
    notify(`${installApp.value?.name} added to your workspace.`);
}

/* ── Create Agent modal (admin only, real) ────────────────────────────── */
const createDialogOpen = ref(false);

const createForm = useForm({
    name: '', icon: '', agent_type: '', action: '', status: 'pending', description: '',
});

function openCreateDialog() {
    createForm.reset();
    createForm.clearErrors();
    createDialogOpen.value = true;
}

function submitCreateAgent() {
    createForm.post(route('apps.store'), {
        preserveScroll: true,
        onSuccess: () => { createDialogOpen.value = false; },
    });
}
</script>

<template>
    <MainLayout title="Apps">
        <Head title="Apps" />

        <div class="ap-page">
            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="ap-header">
                <div class="ap-header__text">
                    <h1 class="ap-title">Apps</h1>
                    <p class="ap-subtitle">Extend your Bean Origin workspace with specialized tools for coffee trading, operations, intelligence, and business management.</p>
                </div>
                <div class="ap-header__actions">
                    <a href="#my-apps" class="ap-btn ap-btn--outline"><el-icon><CircleCheck /></el-icon> My Apps ({{ myApps.length }})</a>
                    <button v-if="canCreateAgent" type="button" class="ap-btn ap-btn--primary" @click="openCreateDialog">
                        <el-icon><Plus /></el-icon> New Agent
                    </button>
                </div>
            </div>

            <!-- ── All Apps & Modules ──────────────────────────────────── -->
            <section class="ap-section">
                <div class="ap-section__head">
                    <h2 class="ap-section__title">All Apps &amp; Modules <span class="ap-section__badge">{{ agents.length }} APPS</span></h2>
                </div>
                <div v-if="agents.length" class="ap-grid">
                    <div v-for="app in agents" :key="app.id" class="ap-tile" @click="handleAppClick(app)">
                        <div class="ap-tile__top">
                            <div class="ap-app-icon" :class="`ap-app-icon--${toneFor(app.id)}`"><el-icon><component :is="resolveIcon(app.icon)" /></el-icon></div>
                            <div class="ap-tile__id">
                                <h4 class="ap-tile__name">{{ app.name }}</h4>
                                <p class="ap-tile__cat">{{ formatType(app.agent_type) }}</p>
                            </div>
                        </div>
                        <p class="ap-tile__desc">{{ app.description || 'No description provided yet.' }}</p>
                        <div class="ap-tile__footer">
                            <span class="ap-badge" :class="isSubscribed(app.id) ? 'ap-badge--active' : 'ap-badge--available'">{{ isSubscribed(app.id) ? 'Active' : 'Available' }}</span>
                            <button v-if="isSubscribed(app.id)" type="button" class="ap-btn ap-btn--outline ap-btn--sm" :disabled="subscribing === app.id" @click.stop="toggleSubscription(app)">
                                {{ subscribing === app.id ? 'Removing…' : 'Unsubscribe' }}
                            </button>
                            <button v-else type="button" class="ap-btn ap-btn--primary ap-btn--sm" @click.stop="openInstall(app)">Install</button>
                        </div>
                    </div>
                </div>
                <p v-else class="ap-no-results">No apps available yet.</p>
            </section>

            <!-- ── My Apps ─────────────────────────────────────────────── -->
            <section id="my-apps" class="ap-section">
                <div class="ap-section__head">
                    <h2 class="ap-section__title">My Apps <span class="ap-section__badge">{{ myApps.length }} ACTIVE IN WORKSPACE</span></h2>
                </div>
                <div v-if="myApps.length" class="ap-table-card">
                    <table class="ap-table">
                        <thead>
                            <tr>
                                <th>Application</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Functions</th>
                                <th class="ap-table__end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="app in myApps" :key="app.id">
                                <td>
                                    <div class="ap-table__app">
                                        <div class="ap-app-icon ap-app-icon--sm" :class="`ap-app-icon--${toneFor(app.id)}`"><el-icon><component :is="resolveIcon(app.icon)" /></el-icon></div>
                                        <div>
                                            <div class="ap-table__app-name">{{ app.name }}</div>
                                            <div class="ap-table__app-desc">{{ app.description || 'No description provided yet.' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="ap-mono">{{ formatType(app.agent_type) }}</td>
                                <td><span class="ap-badge ap-badge--active">Active</span></td>
                                <td class="ap-mono">{{ app.functions?.length || 0 }} function{{ app.functions?.length === 1 ? '' : 's' }}</td>
                                <td class="ap-table__end">
                                    <button type="button" class="ap-btn ap-btn--outline ap-btn--sm" @click="openDetails(app)">Details</button>
                                    <button type="button" class="ap-btn ap-btn--outline ap-btn--sm ap-btn--icon-only" title="Unsubscribe" :disabled="subscribing === app.id" @click="toggleSubscription(app)"><el-icon><Setting /></el-icon></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="ap-empty">
                    <el-icon :size="22"><StarFilled /></el-icon>
                    <p>You haven't added any apps to your workspace yet.</p>
                </div>
            </section>

        </div>

        <!-- ── App Details Modal ───────────────────────────────────────── -->
        <el-dialog v-model="detailsOpen" width="min(600px, calc(100vw - 2rem))" align-center destroy-on-close :close-on-click-modal="false" :show-close="false" class="ap-modal">
            <template v-if="detailsApp" #header>
                <div class="ap-details-head">
                    <div class="ap-details-head__icon" :class="`ap-app-icon--${toneFor(detailsApp.id)}`">
                        <el-icon :size="22"><component :is="resolveIcon(detailsApp.icon)" /></el-icon>
                    </div>
                    <div class="ap-details-head__text">
                        <div class="ap-details-head__eyebrow">{{ formatType(detailsApp.agent_type) }}</div>
                        <div class="ap-details-head__title-row">
                            <h3 class="ap-details-head__title">{{ detailsApp.name }}</h3>
                            <span class="ap-badge" :class="isSubscribed(detailsApp.id) ? 'ap-badge--active' : 'ap-badge--available'">{{ isSubscribed(detailsApp.id) ? 'Active' : 'Available' }}</span>
                        </div>
                    </div>
                    <button type="button" class="ap-details-head__close" aria-label="Close" @click="detailsOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div v-if="detailsApp" class="ap-details">
                <section class="ap-details__section">
                    <h4 class="ap-details__label">What this app does</h4>
                    <p class="ap-details__desc">{{ detailsApp.description || 'No description provided yet.' }}</p>
                </section>

                <section class="ap-details__section">
                    <h4 class="ap-details__label">
                        Key Features
                        <span v-if="detailsApp.functions?.length" class="ap-details__count">{{ detailsApp.functions.length }}</span>
                    </h4>
                    <div v-if="detailsApp.functions?.length" class="ap-feature-list">
                        <div v-for="fn in detailsApp.functions" :key="fn.id" class="ap-feature-row">
                            <div class="ap-feature-row__icon"><el-icon :size="15"><component :is="resolveIcon(fn.icon)" /></el-icon></div>
                            <div class="ap-feature-row__text">
                                <span>{{ fn.name }}</span>
                                <small v-if="fn.description">{{ fn.description }}</small>
                            </div>
                        </div>
                    </div>
                    <p v-else class="ap-details__empty">No functions configured for this app yet.</p>
                </section>

                <div class="ap-details__foot">
                    <Link v-if="canCreateAgent" :href="route('agent.show', detailsApp.id)" class="ap-btn ap-btn--outline"><el-icon><Setting /></el-icon> Manage Agent</Link>
                    <button
                        type="button"
                        class="ap-btn"
                        :class="isSubscribed(detailsApp.id) ? 'ap-btn--outline' : 'ap-btn--primary'"
                        :disabled="subscribing === detailsApp.id"
                        @click="toggleSubscription(detailsApp)"
                    >
                        {{ isSubscribed(detailsApp.id) ? 'Unsubscribe' : 'Add to Workspace' }}
                    </button>
                </div>
            </div>
        </el-dialog>

        <!-- ── Install (subscribe) Modal ───────────────────────────────── -->
        <el-dialog v-model="installOpen" width="min(460px, calc(100vw - 2rem))" align-center destroy-on-close class="ap-modal">
            <template #header>
                <div class="ap-modal__head">
                    <div class="ap-modal__head-icon"><el-icon :size="18"><Connection /></el-icon></div>
                    <div class="ap-modal__head-text">
                        <div class="ap-modal__title">Add Tool to Workspace?</div>
                    </div>
                </div>
            </template>

            <div v-if="installApp" class="ap-install">
                <div class="ap-install__id">
                    <div class="ap-app-icon" :class="`ap-app-icon--${toneFor(installApp.id)}`"><el-icon :size="20"><component :is="resolveIcon(installApp.icon)" /></el-icon></div>
                    <div>
                        <div class="ap-install__name">{{ installApp.name }}</div>
                        <div class="ap-install__cat">{{ formatType(installApp.agent_type).toUpperCase() }}</div>
                    </div>
                </div>
                <p class="ap-install__summary">Adding this tool grants it permission to process relevant commercial data in your workspace.</p>

                <template v-if="installApp.functions?.length">
                    <div class="ap-details__label">This app will be able to:</div>
                    <div class="ap-details__perms">
                        <div v-for="fn in installApp.functions" :key="fn.id" class="ap-details__perm"><el-icon><CircleCheck /></el-icon> {{ fn.name }}</div>
                    </div>
                </template>

                <div class="ap-install__note"><el-icon><CircleCheck /></el-icon> You can remove this app from your workspace anytime.</div>
            </div>

            <template #footer>
                <div class="ap-modal__footer">
                    <button type="button" class="ap-btn ap-btn--outline" @click="installOpen = false">Cancel</button>
                    <button type="button" class="ap-btn ap-btn--primary" :disabled="subscribing === installApp?.id" @click="confirmInstall">
                        <el-icon><Download /></el-icon> Add to Workspace
                    </button>
                </div>
            </template>
        </el-dialog>

        <!-- ── Create Agent modal (admin only) ─────────────────────────── -->
        <el-dialog v-model="createDialogOpen" width="50%" align-center class="ap-modal">
            <template #header>
                <div class="ap-modal__head">
                    <div class="ap-modal__head-icon"><el-icon :size="18"><Plus /></el-icon></div>
                    <div class="ap-modal__head-text">
                        <div class="ap-modal__eyebrow">Automation</div>
                        <div class="ap-modal__title">Create Agent</div>
                    </div>
                </div>
            </template>

            <form class="ap-create-form" @submit.prevent="submitCreateAgent">
                <div class="ap-field-row">
                    <div class="ap-field">
                        <label class="ap-field__label">Name</label>
                        <el-input v-model="createForm.name" placeholder="e.g. Farmer Agent" class="ap-input" :class="{ 'ap-input--error': createForm.errors.name }" />
                        <span v-if="createForm.errors.name" class="ap-field__error">{{ createForm.errors.name }}</span>
                    </div>
                    <div class="ap-field">
                        <label class="ap-field__label">Icon <span class="ap-field__optional">(Element Plus icon name)</span></label>
                        <el-input v-model="createForm.icon" placeholder="e.g. Cherry" class="ap-input" :class="{ 'ap-input--error': createForm.errors.icon }" />
                        <span v-if="createForm.errors.icon" class="ap-field__error">{{ createForm.errors.icon }}</span>
                    </div>
                </div>

                <div class="ap-field-row">
                    <div class="ap-field">
                        <label class="ap-field__label">Agent Type <span class="ap-field__optional">(unique slug)</span></label>
                        <el-input v-model="createForm.agent_type" placeholder="e.g. roaster_agent" class="ap-input" :class="{ 'ap-input--error': createForm.errors.agent_type }" />
                        <span v-if="createForm.errors.agent_type" class="ap-field__error">{{ createForm.errors.agent_type }}</span>
                    </div>
                    <div class="ap-field">
                        <label class="ap-field__label">Action</label>
                        <el-input v-model="createForm.action" placeholder="e.g. match_roast_profile" class="ap-input" :class="{ 'ap-input--error': createForm.errors.action }" />
                        <span v-if="createForm.errors.action" class="ap-field__error">{{ createForm.errors.action }}</span>
                    </div>
                </div>

                <div class="ap-field">
                    <label class="ap-field__label">Status</label>
                    <el-select v-model="createForm.status" placeholder="Select" style="width:100%" class="ap-input">
                        <el-option label="Pending" value="pending" />
                        <el-option label="Active" value="active" />
                        <el-option label="Success" value="success" />
                        <el-option label="Failed" value="failed" />
                    </el-select>
                </div>

                <div class="ap-field">
                    <label class="ap-field__label">Description <span class="ap-field__optional">(optional)</span></label>
                    <el-input v-model="createForm.description" type="textarea" :rows="3" placeholder="What does this agent do?" class="ap-input" />
                    <span v-if="createForm.errors.description" class="ap-field__error">{{ createForm.errors.description }}</span>
                </div>

                <div class="ap-modal__footer">
                    <button type="button" class="ap-btn ap-btn--outline" @click="createDialogOpen = false">Cancel</button>
                    <button type="submit" class="ap-btn ap-btn--primary" :disabled="createForm.processing">
                        <el-icon v-if="!createForm.processing"><Plus /></el-icon>
                        {{ createForm.processing ? 'Creating…' : 'Create Agent' }}
                    </button>
                </div>
            </form>
        </el-dialog>
    </MainLayout>
</template>

<style scoped>
.ap-page { font-family: var(--dp-font-sans); display: flex; flex-direction: column; gap: 24px; }

/* ── Header ──────────────────────────────────────────────────────────── */
.ap-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.ap-header__text { min-width: 0; }
.ap-title { font-size: 24px; line-height: 30px; font-weight: 800; letter-spacing: -0.015em; color: var(--dp-on-surface); margin: 0 0 6px; }
.ap-subtitle { font-size: 13.5px; line-height: 20px; color: var(--dp-on-surface-variant); margin: 0; max-width: 62ch; }
.ap-header__actions { display: flex; gap: 10px; flex-shrink: 0; }

/* ── Sections ────────────────────────────────────────────────────────── */
.ap-section { display: flex; flex-direction: column; gap: 16px; }
.ap-section__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.ap-section__title { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; color: var(--dp-on-surface); letter-spacing: -0.2px; margin: 0; }
.ap-section__badge { font-family: var(--dp-font-mono); font-size: 10px; font-weight: 600; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); padding: 2px 7px; border-radius: 12px; }
.ap-section__desc { font-size: 13.5px; color: var(--dp-on-surface-variant); margin: -8px 0 0; }
.ap-section__note { font-size: 12px; color: var(--dp-on-surface-variant); }
.ap-no-results { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; }
.ap-mono { font-family: var(--dp-font-mono); font-size: 11.5px; color: var(--dp-on-surface-variant); }

/* ── App icon tones ──────────────────────────────────────────────────── */
.ap-app-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.ap-app-icon--sm { width: 34px; height: 34px; font-size: 16px; border-radius: 9px; }
.ap-app-icon--emerald { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.ap-app-icon--blue { background: #EFF6FF; color: #2563EB; }
.ap-app-icon--green { background: #F0FDF4; color: #16A34A; }
.ap-app-icon--amber { background: #FFFBEB; color: #D97706; }
.ap-app-icon--indigo { background: #EEF2FF; color: #4F46E5; }
.ap-app-icon--slate { background: var(--dp-surface-container-low); color: var(--dp-on-surface-variant); border: 1px solid var(--dp-outline-variant); }

/* ── Badges ──────────────────────────────────────────────────────────── */
.ap-badge { display: inline-flex; align-items: center; gap: 6px; font-family: var(--dp-font-mono); font-size: 10.5px; font-weight: 600; padding: 3px 8px; border-radius: 4px; }
.ap-badge--active { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.ap-badge--available { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

/* ── Grid tiles ──────────────────────────────────────────────────────── */
.ap-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
.ap-tile { display: flex; flex-direction: column; background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 10px; padding: 16px; cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease; }
.ap-tile:hover { border-color: var(--dp-outline); }
.ap-tile__top { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px; }
.ap-tile__id { flex: 1; min-width: 0; }
.ap-tile__name { font-size: 14px; font-weight: 700; color: var(--dp-on-surface); margin: 0; }
.ap-tile__cat { font-family: var(--dp-font-mono); font-size: 10.5px; color: var(--dp-on-surface-variant); margin: 2px 0 0; }
.ap-tile__desc { font-size: 12.5px; color: var(--dp-on-surface-variant); line-height: 1.45; margin-bottom: 14px; min-height: 36px; }
.ap-tile__footer { display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--dp-outline-variant); padding-top: 10px; margin-top: auto; }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.ap-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    height: 34px; padding: 0 14px; border-radius: 8px;
    font-size: 12.5px; font-weight: 600; border: 1px solid transparent;
    cursor: pointer; transition: opacity .12s ease, background .12s ease, border-color .12s ease, color .12s ease;
    text-decoration: none; white-space: nowrap;
}
.ap-btn--block { width: 100%; }
.ap-btn--sm { height: 28px; padding: 0 10px; font-size: 11.5px; }
.ap-btn--icon-only { width: 28px; padding: 0; }
.ap-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.ap-btn--primary:hover:not(:disabled) { opacity: .88; }
.ap-btn--outline { background: var(--dp-surface-container-lowest); border-color: var(--dp-outline-variant); color: var(--dp-on-surface); }
.ap-btn--outline:hover:not(:disabled) { background: var(--dp-surface-container-low); }

/* Browser default focus rings render blue — replace with the app's own
   accent so keyboard focus stays visible without the mismatched color. */
.ap-btn:focus-visible,
.ap-details-head__close:focus-visible,
.ap-tile:focus-visible {
    outline: 2px solid var(--dp-primary);
    outline-offset: 2px;
}

/* ── My Apps table ───────────────────────────────────────────────────── */
.ap-table-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 10px; overflow: hidden; }
.ap-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.ap-table th { background: var(--dp-surface-container-low); border-bottom: 1px solid var(--dp-outline-variant); font-family: var(--dp-font-mono); font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); text-transform: uppercase; letter-spacing: .5px; padding: 10px 18px; text-align: left; }
.ap-table td { padding: 12px 18px; border-bottom: 1px solid var(--dp-outline-variant); vertical-align: middle; }
.ap-table tr:last-child td { border-bottom: none; }
.ap-table__end { text-align: right; }
.ap-table__end .ap-btn { margin-left: 6px; }
.ap-table__app { display: flex; align-items: center; gap: 12px; }
.ap-table__app-name { font-weight: 700; color: var(--dp-on-surface); font-size: 13px; }
.ap-table__app-desc { font-size: 11px; color: var(--dp-on-surface-variant); margin-top: 1px; }
.ap-table__muted { font-size: 12px; color: var(--dp-on-surface-variant); }
.ap-table__scopes { font-size: 11.5px; color: var(--dp-primary); font-family: var(--dp-font-mono); }

/* ── Empty state ─────────────────────────────────────────────────────── */
.ap-empty {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 40px 20px; background: var(--dp-surface-container-low); border: 1px solid var(--dp-outline-variant);
    border-radius: 10px; text-align: center; color: var(--dp-on-surface-variant);
}
.ap-empty p { font-size: 12.5px; margin: 0; }

/* ── Modals — el-dialog teleports its content to <body>, outside this
   component's DOM subtree (and outside .dp-shell, where --dp-* custom
   properties are defined), so those tokens don't cascade in here.
   Literal hex from the app's default palette is used instead, matching
   every other teleported dialog in this app. */
:deep(.el-dialog.ap-modal) { border-radius: 6px; padding: 0; overflow: hidden; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18); font-family: 'Inter', system-ui, sans-serif; }
:deep(.el-dialog.ap-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.ap-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.ap-modal .el-dialog__footer) { padding: 0; }

.ap-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.ap-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ap-modal__head-text { flex: 1; min-width: 0; }
.ap-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6F7677; margin-bottom: 1px; }
.ap-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }
.ap-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #F5F6F7; border-top: 1px solid #E5E7EB; }

/* ── Details modal — redesigned: hero-ish header (icon, title, live
   status pill, real close button), tonal feature cards instead of a
   plain bullet list. No gradients/decorative bars per standing feedback
   — spacing and tonal layering carry the "modern" feel instead. ──────── */
.ap-details-head { display: flex; align-items: flex-start; gap: 14px; padding: 22px 24px; background: #fff; border-bottom: 1px solid #F1F2F3; }
.ap-details-head__icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ap-details-head__text { flex: 1; min-width: 0; }
.ap-details-head__eyebrow { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #6F7677; margin-bottom: 4px; }
.ap-details-head__title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.ap-details-head__title { font-size: 1.125rem; font-weight: 800; color: #121516; letter-spacing: -0.01em; margin: 0; }
.ap-details-head__close { width: 30px; height: 30px; border-radius: 8px; border: none; background: #F5F6F7; color: #6F7677; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background .12s ease, color .12s ease; }
.ap-details-head__close:hover { background: #EDEFF0; color: #121516; }

.ap-details { padding: 22px 24px 24px; display: flex; flex-direction: column; gap: 22px; max-height: 68vh; overflow-y: auto; }
.ap-details__section { display: flex; flex-direction: column; gap: 10px; }
.ap-details__label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6F7677; margin: 0; }
.ap-details__count { font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; background: #F1F2F3; color: #6F7677; padding: 1px 7px; border-radius: 999px; }
.ap-details__desc { font-size: 13.5px; color: #4B5457; line-height: 1.6; margin: 0; }
.ap-details__empty { font-size: 12.5px; color: #6F7677; background: #F5F6F7; border-radius: 10px; padding: 14px; text-align: center; margin: 0; }

.ap-feature-list { display: flex; flex-direction: column; gap: 6px; }
.ap-feature-row { display: flex; align-items: flex-start; gap: 12px; padding: 12px; border-radius: 10px; background: #F5F6F7; transition: background .12s ease; }
.ap-feature-row:hover { background: #F1F2F3; }
.ap-feature-row__icon { width: 30px; height: 30px; border-radius: 8px; background: #ECFDF5; color: #065F46; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ap-feature-row__text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.ap-feature-row__text span { font-size: 13px; font-weight: 700; color: #121516; }
.ap-feature-row__text small { font-size: 11.5px; color: #6F7677; line-height: 1.4; }

.ap-details__foot { display: flex; gap: 10px; border-top: 1px solid #E5E7EB; padding-top: 18px; }
.ap-details__foot .ap-btn { flex: 1; }

.ap-install { padding: 22px 24px; display: flex; flex-direction: column; gap: 4px; }
.ap-install__id { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
.ap-install__name { font-weight: 800; font-size: 15px; color: #121516; }
.ap-install__cat { font-size: 11.5px; color: #6F7677; font-family: 'JetBrains Mono', monospace; }
.ap-install__summary { font-size: 13px; color: #4B5457; line-height: 1.45; margin: 0 0 4px; }
.ap-install__note { display: flex; align-items: center; gap: 8px; font-size: 11.5px; color: #4B5457; background: #F5F6F7; border: 1px solid #E5E7EB; border-radius: 8px; padding: 9px 12px; margin-top: 10px; }
.ap-install__note .el-icon { color: #065F46; }

.ap-create-form { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }
.ap-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ap-field { display: flex; flex-direction: column; gap: 5px; }
.ap-field__label { font-size: 12px; font-weight: 600; color: #121516; }
.ap-field__optional { font-weight: 400; color: #6F7677; }
.ap-field__error { font-size: 12px; font-weight: 500; color: #F85149; margin-top: 4px; display: block; }
.ap-input--error :deep(.el-input__wrapper),
.ap-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #F85149 inset !important; }
.ap-input :deep(.el-input__wrapper),
.ap-input :deep(.el-textarea__inner),
.ap-input :deep(.el-select__wrapper) { border-radius: 6px; box-shadow: 0 0 0 1px #E5E7EB inset; background: #F5F6F7; transition: box-shadow 120ms ease, background 120ms ease; }
.ap-input :deep(.el-input__wrapper:hover),
.ap-input :deep(.el-textarea__inner:hover),
.ap-input :deep(.el-select__wrapper:hover) { background: #fff; box-shadow: 0 0 0 1px #E5E7EB inset; }
.ap-input :deep(.el-input__wrapper.is-focus),
.ap-input :deep(.el-textarea__inner:focus),
.ap-input :deep(.el-select__wrapper.is-focused) { background: #fff; box-shadow: 0 0 0 1.5px #000000 inset; }

@media (max-width: 767.98px) {
    .ap-field-row { grid-template-columns: 1fr; }
    .ap-header { flex-direction: column; align-items: stretch; }
    .ap-details__foot { flex-direction: column; }
    :deep(.el-dialog.ap-modal) { width: 92vw !important; }
}
</style>
