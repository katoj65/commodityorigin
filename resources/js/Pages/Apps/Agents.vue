<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Plus, Setting } from '@element-plus/icons-vue';

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

// Icon components are registered globally by name (see app.js), so the
// `icon` string stored on agents/agent_functions resolves directly.
const iconOrFallback = (icon) => icon || 'Setting';

const statusLabel = (s) => ({
    pending: 'Pending',
    active: 'Active',
    success: 'Success',
    failed: 'Failed',
}[s] ?? s ?? '—');

const statusCls = (s) => ({
    pending: 'ap-badge--yellow',
    active: 'ap-badge--green',
    success: 'ap-badge--green',
    failed: 'ap-badge--red',
}[s] ?? 'ap-badge--muted');

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
    <AppLayout title="Apps" full-width flush :show-banner="false">
        <Head title="Apps" />

        <div class="ap-page">

            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="ap-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="py-3 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                        <div>
                            <div class="ap-kicker">Automation</div>
                            <h1 class="ap-title mb-0">Apps</h1>
                            <p class="ap-subtitle mb-0">Browse available AI agents and add the ones you want to your subscription.</p>
                        </div>
                        <button v-if="props.canCreateAgent" type="button" class="btn btn-sm ap-btn-primary" @click="openCreateDialog">
                            <el-icon><Plus /></el-icon> New Agent
                        </button>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── Empty state ─────────────────────────────────────── -->
                <div v-if="!props.agents.length" class="ap-empty">
                    <el-icon style="font-size:2rem;color:#d1d5db;"><Setting /></el-icon>
                    <p class="ap-muted mt-2 mb-0">No agents are available yet.</p>
                </div>

                <!-- ── Tile grid ───────────────────────────────────────── -->
                <div v-else class="row g-3">
                    <div v-for="agent in props.agents" :key="agent.id" class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="ap-tile h-100">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="ap-tile__icon">
                                    <el-icon><component :is="iconOrFallback(agent.icon)" /></el-icon>
                                </div>
                                <Link v-if="props.canCreateAgent" :href="route('agent.show', agent.id)" class="ap-manage-link" title="Manage agent">
                                    <el-icon><Setting /></el-icon> Manage
                                </Link>
                            </div>
                            <Link
                                :href="route('agent.show', agent.id)"
                                class="ap-tile__name ap-tile__name--link"
                            >
                                {{ agent.name }}
                            </Link>

                            <div v-if="agent.functions?.length" class="ap-tile__functions">
                                <a v-for="fn in agent.functions" :key="fn.id" href="#" class="ap-fn-link" @click.prevent>
                                    <el-icon><component :is="iconOrFallback(fn.icon)" /></el-icon>
                                    {{ fn.name }}
                                </a>
                            </div>

                            <p class="ap-tile__desc">{{ agent.description }}</p>
                            <div class="ap-tile__footer mt-3">
                                <!-- <span class="ap-badge" :class="statusCls(agent.status)">{{ statusLabel(agent.status) }}</span> -->
                                <button
                                    type="button"
                                    class="btn btn-sm w-100"
                                    :class="isSubscribed(agent.id) ? 'ap-btn-outline' : 'ap-btn-primary'"
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
                        <button type="button" class="btn btn-sm ap-btn-outline" @click="createDialogOpen = false">Cancel</button>
                        <button type="submit" class="btn btn-sm ap-btn-primary" :disabled="createForm.processing">
                            <el-icon v-if="!createForm.processing"><Plus /></el-icon>
                            {{ createForm.processing ? 'Creating…' : 'Create Agent' }}
                        </button>
                    </div>
                </form>
            </el-dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
.ap-page {
    --green: #004532;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}
.ap-muted { color: var(--on-surface-var); }

.ap-header   { background: #fff; border-bottom: 1px solid var(--border); }
.ap-kicker   { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.ap-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.ap-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

.ap-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 48px 20px; background: var(--surface-low); border: 1px solid var(--border); border-radius: 10px; text-align: center; }

.ap-tile { display: flex; flex-direction: column; background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: box-shadow .15s, transform .15s; }
.ap-tile:hover { box-shadow: 0 8px 24px rgba(0,0,0,.10); transform: translateY(-2px); border-color: rgba(0,69,50,0.2); }
.ap-tile__icon { width: 44px; height: 44px; border-radius: 10px; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
.ap-manage-link { display: inline-flex; align-items: center; gap: 4px; font-size: .6875rem; font-weight: 600; color: var(--on-surface-var); text-decoration: none; padding: 4px 8px; border-radius: 6px; }
.ap-manage-link:hover { background: var(--surface-low); color: var(--green); }
.ap-tile__name { font-size: .9375rem; font-weight: 700; color: var(--on-surface); margin-bottom: 6px; }
.ap-tile__name--link { display: inline-block; text-decoration: none; cursor: pointer; }
.ap-tile__name--link:hover { color: var(--green); }
.ap-tile__functions { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.ap-fn-link { display: inline-flex; align-items: center; gap: 4px; font-size: .6875rem; font-weight: 600; color: var(--green); text-decoration: underline; text-decoration-color: rgba(0,69,50,0.35); text-underline-offset: 2px; }
.ap-fn-link:hover { text-decoration-color: var(--green); }
.ap-fn-link .el-icon { font-size: 11px; }
.ap-tile__desc { font-size: .8125rem; color: var(--on-surface-var); line-height: 1.55; flex: 1; margin-bottom: 20px; }
.ap-tile__footer { display: flex; flex-direction: column; gap: 10px; margin-top: auto; padding-top: 14px; border-top: 1px solid var(--border); }

.ap-badge { display: inline-flex; border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 8px; }
.ap-badge--green  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.ap-badge--yellow { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.ap-badge--red    { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.ap-badge--muted  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

.ap-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.ap-btn-primary:hover { background: #065f46; }
.ap-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.ap-btn-outline:hover { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }

/* ── Create Agent modal — same design language as other onboarding
   dialogs in the app (rounded card, icon header, footer bar). ────────
   NOTE: <el-dialog> teleports to <body>, outside .ap-page, so CSS
   custom properties like var(--green) do not cascade in — literal
   hex values are used below on purpose. */
:deep(.el-dialog.ap-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, .22);
    font-family: 'Manrope', system-ui, sans-serif;
}
:deep(.el-dialog.ap-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.ap-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.ap-modal .el-dialog__footer) { padding: 0; }

.ap-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #f3f4f6; }
.ap-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(0,69,50,.08); color: #004532; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ap-modal__head-text { flex: 1; min-width: 0; }
.ap-modal__eyebrow { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #004532; margin-bottom: 1px; }
.ap-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -.01em; }

.ap-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }

.ap-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ap-field { display: flex; flex-direction: column; gap: 5px; }
.ap-field__label { font-size: .75rem; font-weight: 600; color: #374151; }
.ap-field__optional { font-weight: 400; color: #9ca3af; }
.ap-field__error { font-size: .75rem; font-weight: 600; color: #dc2626; margin-top: 4px; display: block; }

.ap-input--error :deep(.el-input__wrapper),
.ap-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

.ap-input :deep(.el-input__wrapper),
.ap-input :deep(.el-textarea__inner),
.ap-input :deep(.el-select__wrapper) { border-radius: 10px; box-shadow: 0 0 0 1px #e5e7eb inset; background: #f9fafb; transition: box-shadow .12s, background .12s; }
.ap-input :deep(.el-input__wrapper:hover),
.ap-input :deep(.el-textarea__inner:hover),
.ap-input :deep(.el-select__wrapper:hover) { background: #fff; box-shadow: 0 0 0 1px #d1d5db inset; }
.ap-input :deep(.el-input__wrapper.is-focus),
.ap-input :deep(.el-textarea__inner:focus),
.ap-input :deep(.el-select__wrapper.is-focused) { background: #fff; box-shadow: 0 0 0 1.5px #004532 inset; }

.ap-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

@media (max-width: 767.98px) {
    .ap-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.ap-modal) { width: 92vw !important; }
}
</style>
