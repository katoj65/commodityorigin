<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeft, Delete, Edit, Plus, Setting } from '@element-plus/icons-vue';
import { resolveIcon } from '@/utils/icon';

const props = defineProps({
    agent: { type: Object, required: true },
});

// Icon components are registered globally by name (see app.js). Falls
// back to a default icon if the stored name isn't a real registered icon.
const iconOrFallback = resolveIcon;

const statusLabel = (s) => ({
    pending: 'Pending',
    active: 'Active',
    success: 'Success',
    failed: 'Failed',
}[s] ?? s ?? '—');

const statusCls = (s) => ({
    pending: 'agd-badge--yellow',
    active: 'agd-badge--green',
    success: 'agd-badge--green',
    failed: 'agd-badge--red',
}[s] ?? 'agd-badge--muted');

/* ── Edit agent dialog ────────────────────────────────────────────────── */
const editDialogOpen = ref(false);

const editForm = useForm({
    name: props.agent.name,
    icon: props.agent.icon,
    agent_type: props.agent.agent_type,
    action: props.agent.action,
    status: props.agent.status,
    description: props.agent.description,
});

function openEditDialog() {
    editForm.reset();
    editForm.clearErrors();
    editDialogOpen.value = true;
}

function submitEdit() {
    editForm.patch(route('agent.update', props.agent.id), {
        preserveScroll: true,
        onSuccess: () => { editDialogOpen.value = false; },
    });
}

/* ── Delete agent ─────────────────────────────────────────────────────── */
const deleting = ref(false);

function deleteAgent() {
    deleting.value = true;
    router.delete(route('agent.destroy', props.agent.id), {
        onFinish: () => { deleting.value = false; },
    });
}

/* ── Add function dialog ──────────────────────────────────────────────── */
const addFnDialogOpen = ref(false);

const fnForm = useForm({
    name: '',
    icon: '',
    order: 0,
    slug: '',
    description: '',
});

function openAddFnDialog() {
    fnForm.reset();
    fnForm.clearErrors();
    addFnDialogOpen.value = true;
}

function submitAddFn() {
    fnForm.post(route('agent.functions.store', props.agent.id), {
        preserveScroll: true,
        onSuccess: () => { addFnDialogOpen.value = false; },
    });
}

/* ── Edit / delete function ───────────────────────────────────────────── */
const editFnDialogOpen = ref(false);
const editingFn = ref(null);

const editFnForm = useForm({
    name: '',
    icon: '',
    order: 0,
    slug: '',
    description: '',
});

function openEditFnDialog(fn) {
    editingFn.value = fn;
    editFnForm.reset();
    editFnForm.clearErrors();
    editFnForm.name = fn.name;
    editFnForm.icon = fn.icon;
    editFnForm.order = fn.order ?? 0;
    editFnForm.slug = fn.slug;
    editFnForm.description = fn.description;
    editFnDialogOpen.value = true;
}

function submitEditFn() {
    editFnForm.patch(route('agent.functions.update', [props.agent.id, editingFn.value.id]), {
        preserveScroll: true,
        onSuccess: () => { editFnDialogOpen.value = false; },
    });
}

const deletingFnId = ref(null);

function deleteFunction(fn) {
    deletingFnId.value = fn.id;
    router.delete(route('agent.functions.destroy', [props.agent.id, fn.id]), {
        preserveScroll: true,
        onFinish: () => { deletingFnId.value = null; },
    });
}
</script>

<template>
    <AppLayout :title="agent.name" full-width flush :show-banner="false">
        <Head :title="agent.name" />

        <div class="agd-page">

            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="agd-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="py-3">
                        <Link :href="route('apps.index')" class="agd-back-link">
                            <el-icon><ArrowLeft /></el-icon> Back to Apps
                        </Link>

                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mt-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="agd-icon">
                                    <el-icon :size="22"><component :is="iconOrFallback(agent.icon)" /></el-icon>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h1 class="agd-title mb-0">{{ agent.name }}</h1>
                                        <span class="agd-badge" :class="statusCls(agent.status)">{{ statusLabel(agent.status) }}</span>
                                    </div>
                                    <p class="agd-subtitle mb-0">{{ agent.agent_type }} · {{ agent.action }}</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm agd-btn-outline" @click="openEditDialog">
                                    <el-icon><Edit /></el-icon> Edit
                                </button>
                                <el-popconfirm
                                    title="Delete this agent? This cannot be undone."
                                    confirm-button-text="Delete"
                                    cancel-button-text="Cancel"
                                    confirm-button-type="danger"
                                    @confirm="deleteAgent"
                                >
                                    <template #reference>
                                        <button type="button" class="btn btn-sm agd-btn-danger" :disabled="deleting">
                                            <el-icon><Delete /></el-icon> {{ deleting ? 'Deleting…' : 'Delete' }}
                                        </button>
                                    </template>
                                </el-popconfirm>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">
                <div class="row g-3">

                    <!-- ── Details ─────────────────────────────────────── -->
                    <div class="col-12 col-lg-4">
                        <div class="agd-card">
                            <div class="agd-card__title">Details</div>
                            <div class="agd-detail-row"><span>Agent Type</span><strong>{{ agent.agent_type }}</strong></div>
                            <div class="agd-detail-row"><span>Action</span><strong>{{ agent.action }}</strong></div>
                            <div class="agd-detail-row"><span>Status</span><strong>{{ statusLabel(agent.status) }}</strong></div>
                            <div class="agd-detail-row"><span>Created</span><strong>{{ agent.created_at }}</strong></div>
                            <div class="agd-detail-row"><span>Updated</span><strong>{{ agent.updated_at }}</strong></div>
                        </div>
                        <div class="agd-card mt-3">
                            <div class="agd-card__title">Description</div>
                            <p class="agd-desc mb-0">{{ agent.description || 'No description provided.' }}</p>
                        </div>
                    </div>

                    <!-- ── Functions ───────────────────────────────────── -->
                    <div class="col-12 col-lg-8">
                        <div class="agd-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="agd-card__title mb-0">Functions</div>
                                <button type="button" class="btn btn-sm agd-btn-primary" @click="openAddFnDialog">
                                    <el-icon><Plus /></el-icon> Add Function
                                </button>
                            </div>

                            <div v-if="!agent.functions?.length" class="agd-empty">
                                <el-icon style="font-size:1.75rem;color:#d1d5db;"><Setting /></el-icon>
                                <p class="agd-muted mt-2 mb-0">No functions added yet.</p>
                            </div>

                            <div v-else class="agd-fn-list">
                                <div v-for="fn in agent.functions" :key="fn.id" class="agd-fn-row">
                                    <div class="agd-fn-row__order" title="Display order">{{ fn.order }}</div>
                                    <div class="agd-fn-row__icon">
                                        <el-icon><component :is="iconOrFallback(fn.icon)" /></el-icon>
                                    </div>
                                    <div class="agd-fn-row__text">
                                        <div class="agd-fn-row__name">{{ fn.name }}</div>
                                        <div class="agd-fn-row__desc">{{ fn.description || '—' }}</div>
                                    </div>
                                    <div class="agd-fn-row__slug">{{ fn.slug }}</div>
                                    <div class="agd-fn-row__actions">
                                        <button type="button" class="agd-fn-icon-btn" title="Edit function" @click="openEditFnDialog(fn)">
                                            <el-icon><Edit /></el-icon>
                                        </button>
                                        <el-popconfirm
                                            title="Delete this function? This cannot be undone."
                                            confirm-button-text="Delete"
                                            cancel-button-text="Cancel"
                                            confirm-button-type="danger"
                                            @confirm="deleteFunction(fn)"
                                        >
                                            <template #reference>
                                                <button type="button" class="agd-fn-icon-btn agd-fn-icon-btn--danger" title="Delete function" :disabled="deletingFnId === fn.id">
                                                    <el-icon><Delete /></el-icon>
                                                </button>
                                            </template>
                                        </el-popconfirm>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ── Edit Agent modal ────────────────────────────────────── -->
            <el-dialog v-model="editDialogOpen" width="50%" align-center class="agd-modal">
                <template #header>
                    <div class="agd-modal__head">
                        <div class="agd-modal__head-icon">
                            <el-icon :size="18"><Edit /></el-icon>
                        </div>
                        <div class="agd-modal__head-text">
                            <div class="agd-modal__eyebrow">Automation</div>
                            <div class="agd-modal__title">Edit Agent</div>
                        </div>
                    </div>
                </template>

                <form class="agd-modal__body" @submit.prevent="submitEdit">
                    <div class="agd-field-row">
                        <div class="agd-field">
                            <label class="agd-field__label">Name</label>
                            <el-input v-model="editForm.name" class="agd-input" :class="{ 'agd-input--error': editForm.errors.name }" />
                            <span v-if="editForm.errors.name" class="agd-field__error">{{ editForm.errors.name }}</span>
                        </div>
                        <div class="agd-field">
                            <label class="agd-field__label">Icon <span class="agd-field__optional">(Element Plus icon name)</span></label>
                            <el-input v-model="editForm.icon" class="agd-input" :class="{ 'agd-input--error': editForm.errors.icon }" />
                            <span v-if="editForm.errors.icon" class="agd-field__error">{{ editForm.errors.icon }}</span>
                        </div>
                    </div>

                    <div class="agd-field-row">
                        <div class="agd-field">
                            <label class="agd-field__label">Agent Type <span class="agd-field__optional">(unique slug)</span></label>
                            <el-input v-model="editForm.agent_type" class="agd-input" :class="{ 'agd-input--error': editForm.errors.agent_type }" />
                            <span v-if="editForm.errors.agent_type" class="agd-field__error">{{ editForm.errors.agent_type }}</span>
                        </div>
                        <div class="agd-field">
                            <label class="agd-field__label">Action</label>
                            <el-input v-model="editForm.action" class="agd-input" :class="{ 'agd-input--error': editForm.errors.action }" />
                            <span v-if="editForm.errors.action" class="agd-field__error">{{ editForm.errors.action }}</span>
                        </div>
                    </div>

                    <div class="agd-field">
                        <label class="agd-field__label">Status</label>
                        <el-select v-model="editForm.status" placeholder="Select" style="width:100%" class="agd-input">
                            <el-option label="Pending" value="pending" />
                            <el-option label="Active" value="active" />
                            <el-option label="Success" value="success" />
                            <el-option label="Failed" value="failed" />
                        </el-select>
                    </div>

                    <div class="agd-field">
                        <label class="agd-field__label">Description <span class="agd-field__optional">(optional)</span></label>
                        <el-input v-model="editForm.description" type="textarea" :rows="3" class="agd-input" />
                        <span v-if="editForm.errors.description" class="agd-field__error">{{ editForm.errors.description }}</span>
                    </div>

                    <div class="agd-modal__footer">
                        <button type="button" class="btn btn-sm agd-btn-outline" @click="editDialogOpen = false">Cancel</button>
                        <button type="submit" class="btn btn-sm agd-btn-primary" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </el-dialog>

            <!-- ── Add Function modal ──────────────────────────────────── -->
            <el-dialog v-model="addFnDialogOpen" width="50%" align-center class="agd-modal">
                <template #header>
                    <div class="agd-modal__head">
                        <div class="agd-modal__head-icon">
                            <el-icon :size="18"><Plus /></el-icon>
                        </div>
                        <div class="agd-modal__head-text">
                            <div class="agd-modal__eyebrow">{{ agent.name }}</div>
                            <div class="agd-modal__title">Add Function</div>
                        </div>
                    </div>
                </template>

                <form class="agd-modal__body" @submit.prevent="submitAddFn">
                    <div class="agd-field-row">
                        <div class="agd-field">
                            <label class="agd-field__label">Name</label>
                            <el-input v-model="fnForm.name" placeholder="e.g. Log Harvest" class="agd-input" :class="{ 'agd-input--error': fnForm.errors.name }" />
                            <span v-if="fnForm.errors.name" class="agd-field__error">{{ fnForm.errors.name }}</span>
                        </div>
                        <div class="agd-field">
                            <label class="agd-field__label">Icon <span class="agd-field__optional">(Element Plus icon name)</span></label>
                            <el-input v-model="fnForm.icon" placeholder="e.g. Notebook" class="agd-input" :class="{ 'agd-input--error': fnForm.errors.icon }" />
                            <span v-if="fnForm.errors.icon" class="agd-field__error">{{ fnForm.errors.icon }}</span>
                        </div>
                    </div>

                    <div class="agd-field-row">
                        <div class="agd-field">
                            <label class="agd-field__label">Slug <span class="agd-field__optional">(unique per agent)</span></label>
                            <el-input v-model="fnForm.slug" placeholder="e.g. log_harvest" class="agd-input" :class="{ 'agd-input--error': fnForm.errors.slug }" />
                            <span v-if="fnForm.errors.slug" class="agd-field__error">{{ fnForm.errors.slug }}</span>
                        </div>
                        <div class="agd-field">
                            <label class="agd-field__label">Order <span class="agd-field__optional">(lower shows first)</span></label>
                            <el-input-number v-model="fnForm.order" :min="0" class="agd-input w-100" :class="{ 'agd-input--error': fnForm.errors.order }" />
                            <span v-if="fnForm.errors.order" class="agd-field__error">{{ fnForm.errors.order }}</span>
                        </div>
                    </div>

                    <div class="agd-field">
                        <label class="agd-field__label">Description <span class="agd-field__optional">(optional)</span></label>
                        <el-input v-model="fnForm.description" type="textarea" :rows="3" placeholder="What does this function do?" class="agd-input" />
                        <span v-if="fnForm.errors.description" class="agd-field__error">{{ fnForm.errors.description }}</span>
                    </div>

                    <div class="agd-modal__footer">
                        <button type="button" class="btn btn-sm agd-btn-outline" @click="addFnDialogOpen = false">Cancel</button>
                        <button type="submit" class="btn btn-sm agd-btn-primary" :disabled="fnForm.processing">
                            {{ fnForm.processing ? 'Adding…' : 'Add Function' }}
                        </button>
                    </div>
                </form>
            </el-dialog>

            <!-- ── Edit Function modal ─────────────────────────────────── -->
            <el-dialog v-model="editFnDialogOpen" width="50%" align-center class="agd-modal">
                <template #header>
                    <div class="agd-modal__head">
                        <div class="agd-modal__head-icon">
                            <el-icon :size="18"><Edit /></el-icon>
                        </div>
                        <div class="agd-modal__head-text">
                            <div class="agd-modal__eyebrow">{{ agent.name }}</div>
                            <div class="agd-modal__title">Edit Function</div>
                        </div>
                    </div>
                </template>

                <form class="agd-modal__body" @submit.prevent="submitEditFn">
                    <div class="agd-field-row">
                        <div class="agd-field">
                            <label class="agd-field__label">Name</label>
                            <el-input v-model="editFnForm.name" class="agd-input" :class="{ 'agd-input--error': editFnForm.errors.name }" />
                            <span v-if="editFnForm.errors.name" class="agd-field__error">{{ editFnForm.errors.name }}</span>
                        </div>
                        <div class="agd-field">
                            <label class="agd-field__label">Icon <span class="agd-field__optional">(Element Plus icon name)</span></label>
                            <el-input v-model="editFnForm.icon" class="agd-input" :class="{ 'agd-input--error': editFnForm.errors.icon }" />
                            <span v-if="editFnForm.errors.icon" class="agd-field__error">{{ editFnForm.errors.icon }}</span>
                        </div>
                    </div>

                    <div class="agd-field-row">
                        <div class="agd-field">
                            <label class="agd-field__label">Slug <span class="agd-field__optional">(unique per agent)</span></label>
                            <el-input v-model="editFnForm.slug" class="agd-input" :class="{ 'agd-input--error': editFnForm.errors.slug }" />
                            <span v-if="editFnForm.errors.slug" class="agd-field__error">{{ editFnForm.errors.slug }}</span>
                        </div>
                        <div class="agd-field">
                            <label class="agd-field__label">Order <span class="agd-field__optional">(lower shows first)</span></label>
                            <el-input-number v-model="editFnForm.order" :min="0" class="agd-input w-100" :class="{ 'agd-input--error': editFnForm.errors.order }" />
                            <span v-if="editFnForm.errors.order" class="agd-field__error">{{ editFnForm.errors.order }}</span>
                        </div>
                    </div>

                    <div class="agd-field">
                        <label class="agd-field__label">Description <span class="agd-field__optional">(optional)</span></label>
                        <el-input v-model="editFnForm.description" type="textarea" :rows="3" class="agd-input" />
                        <span v-if="editFnForm.errors.description" class="agd-field__error">{{ editFnForm.errors.description }}</span>
                    </div>

                    <div class="agd-modal__footer">
                        <button type="button" class="btn btn-sm agd-btn-outline" @click="editFnDialogOpen = false">Cancel</button>
                        <button type="submit" class="btn btn-sm agd-btn-primary" :disabled="editFnForm.processing">
                            {{ editFnForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </el-dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
.agd-page {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}
.agd-muted { color: var(--on-surface-var); }

.agd-header { background: #fff; border-bottom: 1px solid var(--border); }
.agd-back-link { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; color: var(--on-surface-var); text-decoration: none; }
.agd-back-link:hover { color: var(--green); }
.agd-icon { width: 44px; height: 44px; border-radius: 10px; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.agd-title { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.agd-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

.agd-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; }
.agd-card__title { font-size: .8125rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--on-surface-var); margin-bottom: 12px; }
.agd-desc { font-size: .8125rem; color: var(--on-surface); line-height: 1.6; }

.agd-detail-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: .8125rem; }
.agd-detail-row:last-child { border-bottom: none; }
.agd-detail-row span { color: var(--on-surface-var); }
.agd-detail-row strong { font-weight: 700; color: var(--on-surface); }

.agd-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 40px 20px; background: var(--surface-low); border-radius: 10px; text-align: center; }

.agd-fn-list { display: flex; flex-direction: column; gap: 2px; }
.agd-fn-row { display: flex; align-items: center; gap: 12px; padding: 10px 8px; border-bottom: 1px solid var(--surface-low); }
.agd-fn-row:last-child { border-bottom: none; }
.agd-fn-row__order {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    background: var(--surface-low);
    color: var(--on-surface-var);
    font-family: 'IBM Plex Mono', monospace;
    font-size: .6875rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.agd-fn-row__icon { width: 32px; height: 32px; border-radius: 8px; background: rgba(0,69,50,0.08); color: var(--green); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.agd-fn-row__text { flex: 1; min-width: 0; }
.agd-fn-row__name { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }
.agd-fn-row__desc { font-size: .75rem; color: var(--on-surface-var); }
.agd-fn-row__slug { font-family: 'IBM Plex Mono', monospace; font-size: .6875rem; color: var(--on-surface-var); background: var(--surface-low); border-radius: 5px; padding: 3px 8px; flex-shrink: 0; }
.agd-fn-row__actions { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
.agd-fn-icon-btn { display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; border: 1px solid transparent; background: transparent; color: var(--on-surface-var); transition: all .12s ease; }
.agd-fn-icon-btn:hover { background: var(--surface-low); color: var(--on-surface); }
.agd-fn-icon-btn--danger:hover { background: #fee2e2; color: #991b1b; }
.agd-fn-icon-btn:disabled { opacity: .5; cursor: not-allowed; }

.agd-badge { display: inline-flex; border-radius: 999px; font-size: .6rem; font-weight: 700; padding: 2px 8px; }
.agd-badge--green  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.agd-badge--yellow { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.agd-badge--red    { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.agd-badge--muted  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

.agd-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 6px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.agd-btn-primary:hover { background: #065f46; color: #fff; }
.agd-btn-outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); border-radius: 6px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.agd-btn-outline:hover { background: var(--surface-low); }
.agd-btn-danger { background: #fff; border: 1px solid #fca5a5; color: #991b1b; border-radius: 6px; font-size: .75rem; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.agd-btn-danger:hover { background: #fee2e2; }
.agd-btn-danger:disabled { opacity: .6; cursor: not-allowed; }

/* ── Modals — same design language as other dialogs in the app ───────
   NOTE: <el-dialog> teleports to <body>, outside .agd-page, so CSS
   custom properties like var(--green) do not cascade in — literal hex
   values are used below on purpose. */
:deep(.el-dialog.agd-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, .22);
    font-family: 'Manrope', system-ui, sans-serif;
}
:deep(.el-dialog.agd-modal .el-dialog__header) { padding: 0; margin: 0; }
:deep(.el-dialog.agd-modal .el-dialog__body) { padding: 0; }
:deep(.el-dialog.agd-modal .el-dialog__footer) { padding: 0; }

.agd-modal__head { display: flex; align-items: center; gap: 12px; padding: 20px 24px; background: #fff; border-bottom: 1px solid #f3f4f6; }
.agd-modal__head-icon { width: 38px; height: 38px; border-radius: 11px; background: rgba(0,69,50,.08); color: #004532; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.agd-modal__head-text { flex: 1; min-width: 0; }
.agd-modal__eyebrow { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #004532; margin-bottom: 1px; }
.agd-modal__title { font-size: 1.0625rem; font-weight: 800; color: #111827; letter-spacing: -.01em; }

.agd-modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 65vh; overflow-y: auto; }

.agd-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.agd-field { display: flex; flex-direction: column; gap: 5px; }
.agd-field__label { font-size: .75rem; font-weight: 600; color: #374151; }
.agd-field__optional { font-weight: 400; color: #9ca3af; }
.agd-field__error { font-size: .75rem; font-weight: 600; color: #dc2626; margin-top: 4px; display: block; }

.agd-input--error :deep(.el-input__wrapper),
.agd-input--error :deep(.el-textarea__inner) { box-shadow: 0 0 0 1.5px #dc2626 inset !important; }

.agd-input :deep(.el-input__wrapper),
.agd-input :deep(.el-textarea__inner),
.agd-input :deep(.el-select__wrapper) { border-radius: 10px; box-shadow: 0 0 0 1px #e5e7eb inset; background: #f9fafb; transition: box-shadow .12s, background .12s; }
.agd-input :deep(.el-input__wrapper:hover),
.agd-input :deep(.el-textarea__inner:hover),
.agd-input :deep(.el-select__wrapper:hover) { background: #fff; box-shadow: 0 0 0 1px #d1d5db inset; }
.agd-input :deep(.el-input__wrapper.is-focus),
.agd-input :deep(.el-textarea__inner:focus),
.agd-input :deep(.el-select__wrapper.is-focused) { background: #fff; box-shadow: 0 0 0 1.5px #004532 inset; }

.agd-modal__footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; }

@media (max-width: 767.98px) {
    .agd-field-row { grid-template-columns: 1fr; }
    :deep(.el-dialog.agd-modal) { width: 92vw !important; }
}
</style>
