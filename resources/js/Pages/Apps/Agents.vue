<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Plus, Setting } from '@element-plus/icons-vue';
import { resolveIcon } from '@/utils/icon';

const props = defineProps({
    agents: { type: Array, default: () => [] },
    subscribedAgentIds: { type: Array, default: () => [] },
    canCreateAgent: { type: Boolean, default: false },
});

/* ── Create agent dialog (admin only) ────────────────────────────────── */
const createDialogOpen = ref(false);

const createForm = useForm({
    name: '',
    icon: '',
    agent_type: '',
    action: '',
    status: 'pending',
    description: '',
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

// Icon components are registered globally by name (see app.js). Falls
// back to a default icon if the stored name isn't a real registered icon.
const iconOrFallback = resolveIcon;

// A function's `slug` holds the URL path it links to (e.g. "farm/create"),
// entered by the admin when the function was created/edited. Built off
// Ziggy's own base URL (the global `Ziggy.url`) since the app can be
// served from a subpath (e.g. http://localhost/commodityorigin).
const functionIsRoute = (fn) => !!fn.slug;
const functionHref = (fn) => (fn.slug ? `${Ziggy.url}/${fn.slug.replace(/^\/+/, '')}` : '#');
const onFunctionClick = (fn, event) => {
    if (!functionIsRoute(fn)) {
        event.preventDefault();
    }
};

const subscribing = ref(null);

const isSubscribed = (agentId) => props.subscribedAgentIds.includes(agentId);

function toggleSubscription(agent) {
    subscribing.value = agent.id;
    const options = {
        preserveScroll: true,
        onFinish: () => { subscribing.value = null; },
    };

    if (isSubscribed(agent.id)) {
        router.delete(route('agent.unsubscribe', agent.id), options);
    } else {
        router.post(route('agent.subscribe', agent.id), {}, options);
    }
}
</script>

<template>
    <DesignPreviewLayout title="Apps">
        <div class="ap-page">

            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="ap-header">
                <div class="ap-header__text">
                    <h1 class="ap-title">Apps</h1>
                    <p class="ap-subtitle">Browse available AI agents and add the ones you want to your subscription.</p>
                </div>
                <button v-if="props.canCreateAgent" type="button" class="ap-btn ap-btn--primary" @click="openCreateDialog">
                    <el-icon><Plus /></el-icon> New Agent
                </button>
            </div>

            <!-- ── Empty state ─────────────────────────────────────────── -->
            <div v-if="!props.agents.length" class="ap-empty">
                <el-icon :size="28" class="ap-empty__icon"><Setting /></el-icon>
                <p class="ap-empty__text">No agents are available yet.</p>
            </div>

            <!-- ── Tile grid ───────────────────────────────────────────── -->
            <div v-else class="ap-grid">
                <div v-for="agent in props.agents" :key="agent.id" class="ap-tile">
                    <div class="ap-tile__top">
                        <div class="ap-tile__icon">
                            <el-icon><component :is="iconOrFallback(agent.icon)" /></el-icon>
                        </div>
                        <Link v-if="props.canCreateAgent" :href="route('agent.show', agent.id)" class="ap-manage-link" title="Manage agent">
                            <el-icon><Setting /></el-icon> Manage
                        </Link>
                    </div>
                    <Link :href="route('agent.show', agent.id)" class="ap-tile__name">{{ agent.name }}</Link>

                    <div v-if="agent.functions?.length" class="ap-tile__functions">
                        <component
                            :is="functionIsRoute(fn) ? Link : 'a'"
                            v-for="fn in agent.functions"
                            :key="fn.id"
                            :href="functionHref(fn)"
                            class="ap-fn-link"
                            @click="onFunctionClick(fn, $event)"
                        >
                            <el-icon><component :is="iconOrFallback(fn.icon)" /></el-icon>
                            {{ fn.name }}
                        </component>
                    </div>

                    <p class="ap-tile__desc">{{ agent.description }}</p>
                    <div class="ap-tile__footer">
                        <button
                            type="button"
                            class="ap-btn ap-btn--full"
                            :class="isSubscribed(agent.id) ? 'ap-btn--outline-danger' : 'ap-btn--primary'"
                            :disabled="subscribing === agent.id"
                            @click="toggleSubscription(agent)"
                        >
                            {{
                                subscribing === agent.id
                                    ? (isSubscribed(agent.id) ? 'Unsubscribing…' : 'Subscribing…')
                                    : (isSubscribed(agent.id) ? 'Unsubscribe' : 'Add to Subscription')
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Create Agent modal (admin only) ────────────────────── -->
            <el-dialog v-model="createDialogOpen" width="50%" align-center class="ap-modal">
                <template #header>
                    <div class="ap-modal__head">
                        <div class="ap-modal__head-icon">
                            <el-icon :size="18"><Plus /></el-icon>
                        </div>
                        <div class="ap-modal__head-text">
                            <div class="ap-modal__eyebrow">Automation</div>
                            <div class="ap-modal__title">Create Agent</div>
                        </div>
                    </div>
                </template>

                <form class="ap-modal__body" @submit.prevent="submitCreateAgent">
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
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.ap-page {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --surface-elevated: #F1F2F3;
    --border: #E5E7EB;
    --primary: #000000;
    --on-primary: #ffffff;
    --text: #121516;
    --text-2: #4B5457;
    --text-muted: #6F7677;
    --error: #B91C1C;
    --error-soft: #FEF2F2;
    --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-family: var(--font-sans);
    background: var(--surface);
    color: var(--text);
    min-height: 100%;
}

/* ── Header ──────────────────────────────────────────────────────────── */
.ap-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
.ap-header__text { min-width: 0; }
.ap-title { font-size: 24px; line-height: 30px; font-weight: 700; letter-spacing: -0.015em; color: var(--text); margin: 0 0 6px; }
.ap-subtitle { font-size: 13.5px; line-height: 20px; color: var(--text-2); margin: 0; max-width: 60ch; }

/* ── Empty state ─────────────────────────────────────────────────────── */
.ap-empty {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 48px 20px; background: var(--surface-muted); border: 1px solid var(--border);
    border-radius: 6px; text-align: center;
}
.ap-empty__icon { color: var(--text-muted); }
.ap-empty__text { font-size: 13px; color: var(--text-muted); margin: 0; }

/* ── Tile grid ───────────────────────────────────────────────────────── */
.ap-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
.ap-tile {
    display: flex; flex-direction: column;
    background: var(--surface); border: 1px solid var(--border); border-radius: 6px;
    padding: 20px; transition: border-color 120ms ease;
}
.ap-tile:hover { border-color: var(--text-muted); }
.ap-tile__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.ap-tile__icon {
    width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0;
    background: var(--surface-elevated); color: var(--text-2);
    display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 14px;
}
.ap-manage-link {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600; color: var(--text-muted);
    text-decoration: none; padding: 4px 8px; border-radius: 6px;
    transition: background 120ms ease, color 120ms ease;
}
.ap-manage-link:hover { background: var(--surface-muted); color: var(--text); }
.ap-tile__name {
    font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px;
    display: inline-block; text-decoration: none;
}
.ap-tile__name:hover { text-decoration: underline; }
.ap-tile__functions { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.ap-fn-link {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 9px; border-radius: 999px;
    background: var(--surface-muted); border: 1px solid var(--border);
    font-size: 11px; font-weight: 600; color: var(--text-2);
    text-decoration: none; transition: background 120ms ease, color 120ms ease;
}
.ap-fn-link:hover { background: var(--surface-elevated); color: var(--text); }
.ap-fn-link .el-icon { font-size: 11px; color: var(--text-muted); }
.ap-tile__desc { font-size: 13px; color: var(--text-2); line-height: 1.55; flex: 1; margin-bottom: 18px; }
.ap-tile__footer { margin-top: auto; padding-top: 14px; border-top: 1px solid var(--border); }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.ap-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    height: 36px; padding: 0 16px; border-radius: 6px;
    font-size: 13px; font-weight: 600; border: 1px solid transparent;
    cursor: pointer; transition: opacity 120ms ease, background 120ms ease, border-color 120ms ease, color 120ms ease;
}
.ap-btn--full { width: 100%; }
.ap-btn--primary { background: var(--primary); color: var(--on-primary); }
.ap-btn--primary:hover:not(:disabled) { opacity: 0.88; }
.ap-btn--primary:disabled { opacity: 0.5; cursor: default; }
.ap-btn--outline { background: var(--surface); border-color: var(--border); color: var(--text); }
.ap-btn--outline:hover:not(:disabled) { background: var(--surface-muted); }
.ap-btn--outline-danger { background: var(--surface); border-color: var(--border); color: var(--text); }
.ap-btn--outline-danger:hover:not(:disabled) { background: var(--error-soft); border-color: #FCA5A5; color: var(--error); }
.ap-btn--outline-danger:disabled { opacity: 0.6; cursor: default; }

/* ── Create Agent modal — same design language as the app's other
   onboarding dialogs (AttachBatchModal, AttachFarmCollectionModal):
   icon header, footer bar, literal hex from the app's default palette.
   NOTE: <el-dialog> teleports to <body>, outside .ap-page, so CSS
   custom properties defined above do not cascade in. */
:deep(.el-dialog.ap-modal) {
    border-radius: 6px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    font-family: 'Inter', system-ui, sans-serif;
}
:deep(.el-dialog.ap-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.ap-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.ap-modal .el-dialog__footer) { padding: 0; }

.ap-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #E5E7EB; }
.ap-modal__head-icon { width: 36px; height: 36px; border-radius: 6px; background: #F1F2F3; color: #121516; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ap-modal__head-text { flex: 1; min-width: 0; }
.ap-modal__eyebrow { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6F7677; margin-bottom: 1px; }
.ap-modal__title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; }

.ap-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }

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

.ap-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #F5F6F7; border-top: 1px solid #E5E7EB; }

@media (max-width: 767.98px) {
    .ap-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.ap-modal) { width: 92vw !important; }
    .ap-header { flex-direction: column; align-items: stretch; }
}
</style>
