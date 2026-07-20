<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Delete, MagicStick, CircleCheck, Warning, Clock, List, Calendar as CalendarIcon, Close, Files } from '@element-plus/icons-vue';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
});

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

/* ── Filters ─────────────────────────────────────────────────────────── */
const activeFilter = ref('all');
const filters = [
    { key: 'all', label: 'All' },
    { key: 'today', label: 'Due Today' },
    { key: 'overdue', label: 'Overdue' },
    { key: 'upcoming', label: 'Upcoming' },
    { key: 'completed', label: 'Completed' },
];

const sortedTasks = computed(() => [...props.tasks].sort((a, b) => a.task_date.localeCompare(b.task_date)));

const filteredTasks = computed(() => sortedTasks.value.filter((t) => {
    switch (activeFilter.value) {
        case 'today': return t.status === 'pending' && t.task_date === todayStr();
        case 'overdue': return t.status === 'pending' && t.task_date < todayStr();
        case 'upcoming': return t.status === 'pending' && t.task_date > todayStr();
        case 'completed': return t.status === 'completed';
        default: return true;
    }
}));

/* ── KPIs ────────────────────────────────────────────────────────────── */
const kpis = computed(() => ({
    total: props.tasks.length,
    dueToday: props.tasks.filter((t) => t.status === 'pending' && t.task_date === todayStr()).length,
    overdue: props.tasks.filter((t) => t.status === 'pending' && t.task_date < todayStr()).length,
    upcoming: props.tasks.filter((t) => t.status === 'pending' && t.task_date > todayStr()).length,
    completed: props.tasks.filter((t) => t.status === 'completed').length,
}));

/* ── Display helpers ─────────────────────────────────────────────────── */
function relativeDateLabel(date) {
    const diff = Math.round((new Date(`${date}T00:00:00`) - new Date(`${todayStr()}T00:00:00`)) / 86400000);
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Tomorrow';
    if (diff === -1) return 'Yesterday';
    return diff > 0 ? `In ${diff} days` : `${Math.abs(diff)} days overdue`;
}

function toneFor(task) {
    if (task.status === 'completed') return 'tp-badge--green';
    if (task.task_date < todayStr()) return 'tp-badge--red';
    if (task.task_date === todayStr()) return 'tp-badge--amber';
    return 'tp-badge--muted';
}

/* ── Create dialog ───────────────────────────────────────────────────── */
const dialogOpen = ref(false);
const saving = ref(false);
const form = reactive({
    title: '',
    description: '',
    task_date: todayStr(),
    add_to_calendar: false,
});

function openCreateDialog() {
    form.title = '';
    form.description = '';
    form.task_date = todayStr();
    form.add_to_calendar = false;
    dialogOpen.value = true;
}

function saveTask() {
    if (!form.title.trim() || !form.task_date) {
        ElMessage.warning('Title and date are required.');
        return;
    }

    saving.value = true;
    router.post(route('task.store'), { ...form }, {
        preserveScroll: true,
        onSuccess: () => { dialogOpen.value = false; },
        onFinish: () => { saving.value = false; },
    });
}

/* ── Complete / delete ───────────────────────────────────────────────── */
function markComplete(task) {
    router.patch(route('task.update', task.id), {
        title: task.title,
        description: task.description,
        task_date: task.task_date,
        status: 'completed',
    }, { preserveScroll: true });
}

function reopenTask(task) {
    router.patch(route('task.update', task.id), {
        title: task.title,
        description: task.description,
        task_date: task.task_date,
        status: 'pending',
    }, { preserveScroll: true });
}

const confirmOpen = ref(false);
const pendingDelete = ref(null);

function deleteTask(task) {
    pendingDelete.value = task;
    confirmOpen.value = true;
}

function confirmDeleteTask() {
    if (!pendingDelete.value) return;
    router.delete(route('task.destroy', pendingDelete.value.id), { preserveScroll: true });
    pendingDelete.value = null;
}
</script>

<template>
    <AppLayout title="Tasks" full-width flush :show-banner="false">
        <Head title="Tasks" />

        <div class="tp-page">
            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="tp-page-header">
                <div class="tp-page-header__left">

                    <h1 class="tp-title">Tasks</h1>
                    <p class="tp-subtitle">The decisions and actions Bean Origin AI thinks need your attention — created directly or from calendar events.</p>
                </div>
                <div class="tp-page-header__actions">
                    <Link :href="route('calendar.index')" class="tp-btn-outline">
                        <el-icon><CalendarIcon /></el-icon> View Calendar
                    </Link>
                    <button type="button" class="tp-btn-primary" @click="openCreateDialog">
                        <el-icon><Plus /></el-icon> New Task
                    </button>
                </div>
            </div>

            <!-- ── Overview strip ────────────────────────────────────────── -->
            <div class="tp-kpi-strip">
                <div class="tp-kpi">
                    <span class="tp-kpi__label">Total</span>
                    <strong class="tp-kpi__value">{{ kpis.total }}</strong>
                </div>
                <div class="tp-kpi">
                    <span class="tp-kpi__label">Due Today</span>
                    <strong class="tp-kpi__value" :class="kpis.dueToday ? 'tp-kpi__value--amber' : ''">{{ kpis.dueToday }}</strong>
                </div>
                <div class="tp-kpi">
                    <span class="tp-kpi__label">Overdue</span>
                    <strong class="tp-kpi__value" :class="kpis.overdue ? 'tp-kpi__value--red' : ''">{{ kpis.overdue }}</strong>
                </div>
                <div class="tp-kpi">
                    <span class="tp-kpi__label">Upcoming</span>
                    <strong class="tp-kpi__value">{{ kpis.upcoming }}</strong>
                </div>
                <div class="tp-kpi">
                    <span class="tp-kpi__label">Completed</span>
                    <strong class="tp-kpi__value tp-kpi__value--green">{{ kpis.completed }}</strong>
                </div>
            </div>

            <div class="tp-body">

                <!-- ── Filters ──────────────────────────────────────────── -->
                <div class="tp-filters mb-3">
                    <button
                        v-for="f in filters"
                        :key="f.key"
                        type="button"
                        class="tp-filter-tab"
                        :class="{ 'tp-filter-tab--active': activeFilter === f.key }"
                        @click="activeFilter = f.key"
                    >{{ f.label }}</button>
                </div>

                <!-- ── Task list ────────────────────────────────────────── -->
                <div class="tp-section">
                    <div class="tp-section-head">
                        <div class="tp-section-title"><el-icon><List /></el-icon> {{ filters.find(f => f.key === activeFilter)?.label }}</div>
                        <span class="tp-count-badge">{{ filteredTasks.length }}</span>
                    </div>

                    <div v-if="!filteredTasks.length" class="text-center py-5">
                        <el-icon style="font-size:2rem;color:#d1d5db;"><List /></el-icon>
                        <p class="tp-muted mt-2 mb-0" style="font-size:.875rem;">No tasks in this view.</p>
                    </div>

                    <div v-else class="tp-list">
                        <div v-for="task in filteredTasks" :key="task.id" class="tp-row">
                            <button
                                v-if="task.status !== 'completed'"
                                type="button"
                                class="tp-row__check"
                                aria-label="Mark complete"
                                title="Mark complete"
                                @click="markComplete(task)"
                            ></button>
                            <el-icon v-else class="tp-row__done" @click="reopenTask(task)" title="Reopen task"><CircleCheck /></el-icon>

                            <div class="tp-row__body">
                                <div class="tp-row__title" :class="{ 'tp-row__title--done': task.status === 'completed' }">{{ task.title }}</div>
                                <div v-if="task.description" class="tp-row__desc">{{ task.description }}</div>
                            </div>

                            <span class="tp-badge" :class="toneFor(task)">
                                <el-icon v-if="task.status === 'pending' && task.task_date < todayStr()"><Warning /></el-icon>
                                <el-icon v-else><Clock /></el-icon>
                                {{ relativeDateLabel(task.task_date) }}
                            </span>

                            <button type="button" class="tp-row__delete" aria-label="Delete task" title="Delete task" @click="deleteTask(task)">
                                <el-icon><Delete /></el-icon>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <el-dialog
            v-model="dialogOpen"
            width="480px"
            destroy-on-close
            align-center
            :show-close="false"
            class="tp-modal"
        >
            <template #header>
                <div class="tp-modal__head">
                    <div class="tp-modal__head-icon">
                        <el-icon :size="18"><CircleCheck /></el-icon>
                    </div>
                    <div class="tp-modal__head-text">
                        <div class="tp-modal__eyebrow">Create</div>
                        <div class="tp-modal__title">New Task</div>
                    </div>
                    <button type="button" class="tp-modal__close" aria-label="Close" @click="dialogOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="tp-modal__body">
                <div class="tp-field">
                    <label class="tp-field__label">Title</label>
                    <el-input v-model="form.title" placeholder="What needs a decision?" class="tp-input" />
                </div>

                <div class="tp-field">
                    <label class="tp-field__label"><el-icon :size="12"><Files /></el-icon> Description</label>
                    <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Optional notes" class="tp-input" />
                </div>

                <div class="tp-field">
                    <label class="tp-field__label">Task Date</label>
                    <el-date-picker v-model="form.task_date" type="date" value-format="YYYY-MM-DD" style="width:100%" class="tp-input" />
                </div>

                <div class="tp-field tp-field--switch">
                    <div class="tp-switch-row">
                        <div class="tp-switch-row__text">
                            <label class="tp-field__label"><el-icon :size="12"><CalendarIcon /></el-icon> Add to Calendar</label>
                            <span class="tp-field__hint">Also show this task as an event on your calendar, kept in sync automatically.</span>
                        </div>
                        <el-switch v-model="form.add_to_calendar" class="tp-switch" />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="tp-modal__footer">
                    <button type="button" class="tp-btn-outline" @click="dialogOpen = false">Cancel</button>
                    <button type="button" class="tp-btn-primary" :disabled="saving" @click="saveTask">
                        <el-icon v-if="!saving"><Plus /></el-icon>
                        {{ saving ? 'Saving…' : 'Create Task' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Delete Task"
            :message="pendingDelete ? `Delete “${pendingDelete.title}”? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDeleteTask"
        />
    </AppLayout>
</template>

<style scoped>
.tp-page {
    --green: #004532;
    --green-dark: #002e20;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #fff;
    color: var(--on-surface);
    min-height: 100%;
}
.tp-muted { color: var(--on-surface-var); }

/* ── Page header ─────────────────────────────────────────────────────── */
.tp-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.75rem 1.5rem 0;
}
.tp-page-header__left { max-width: 560px; }
.tp-page-header__actions { display: flex; gap: 8px; flex-wrap: wrap; padding-top: 4px; }

.tp-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 4px; }
.tp-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; margin: 0 0 .25rem; }
.tp-subtitle { font-size: .875rem; color: var(--on-surface-var); margin: 0; line-height: 1.6; }

.tp-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: .8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity .15s ease;
}
.tp-btn-primary:hover { opacity: .9; }
.tp-btn-primary:disabled { opacity: .6; }
.tp-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: .8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s ease;
}
.tp-btn-outline:hover { background: #f8fafc; }

/* ── Overview strip ──────────────────────────────────────────────────── */
.tp-kpi-strip {
    display: flex;
    gap: 0;
    overflow-x: auto;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-top: 1.5rem;
    scrollbar-width: none;
}
.tp-kpi-strip::-webkit-scrollbar { display: none; }
.tp-kpi {
    flex: 1;
    min-width: 110px;
    padding: 1rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
    border-right: 1px solid var(--border);
}
.tp-kpi:last-child { border-right: none; }
.tp-kpi__label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); }
.tp-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; letter-spacing: -.01em; }
.tp-kpi__value--amber { color: #92400e; }
.tp-kpi__value--red { color: #991b1b; }
.tp-kpi__value--green { color: #166534; }

/* ── Body ────────────────────────────────────────────────────────────── */
.tp-body { padding: 1.25rem 1.5rem 3rem; }

/* ── Filters ─────────────────────────────────────────────────────────── */
.tp-filters { display: flex; gap: 6px; flex-wrap: wrap; }
.tp-filter-tab { border: 1px solid var(--border); background: #fff; color: var(--on-surface-var); font-size: .75rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; cursor: pointer; transition: all .15s; }
.tp-filter-tab:hover { border-color: var(--green); color: var(--green); }
.tp-filter-tab--active { background: var(--green); border-color: var(--green); color: #fff; }

/* ── Section / list ──────────────────────────────────────────────────── */
.tp-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
.tp-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: .875rem 1rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface-low);
}
.tp-section-title { display: inline-flex; align-items: center; gap: 7px; font-size: .875rem; font-weight: 800; color: var(--on-surface); }
.tp-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 20px;
    padding: 0 7px;
    border-radius: 999px;
    background: rgba(0, 69, 50, .08);
    color: var(--green);
    font-size: .6875rem;
    font-weight: 800;
}
.tp-list { display: flex; flex-direction: column; padding: 4px 16px; }

.tp-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--surface-low); }
.tp-row:last-child { border-bottom: none; }

.tp-row__check { width: 20px; height: 20px; border-radius: 50%; border: 1.5px solid #d1d5db; background: #fff; flex-shrink: 0; cursor: pointer; padding: 0; }
.tp-row__check:hover { border-color: var(--green); background: rgba(0,69,50,0.08); }
.tp-row__done { width: 20px; height: 20px; border-radius: 50%; background: #16a34a; color: #fff; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer; }

.tp-row__body { flex: 1; min-width: 0; }
.tp-row__title { font-size: .875rem; font-weight: 600; color: var(--on-surface); }
.tp-row__title--done { color: #9ca3af; text-decoration: line-through; }
.tp-row__desc { font-size: .75rem; color: var(--on-surface-var); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.tp-row__delete { width: 28px; height: 28px; border-radius: 6px; border: none; background: transparent; color: var(--on-surface-var); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer; }
.tp-row__delete:hover { background: #fee2e2; color: #991b1b; }

.tp-badge { display: inline-flex; align-items: center; gap: 4px; border-radius: 999px; font-size: .6875rem; font-weight: 700; padding: 4px 10px; flex-shrink: 0; white-space: nowrap; }
.tp-badge--green { background: #dcfce7; color: #166534; }
.tp-badge--amber { background: #fef3c7; color: #92400e; }
.tp-badge--red { background: #fee2e2; color: #991b1b; }
.tp-badge--muted { background: #f3f4f6; color: #6b7280; }

/* ── New Task modal ──────────────────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .tp-page's DOM
   subtree, so CSS custom properties (var(--green) etc.) defined on .tp-page
   do NOT cascade in. All colors below are literal hex values on purpose. */
:deep(.el-dialog.tp-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
}

:deep(.el-dialog.tp-modal .el-dialog__header) {
    padding: 0;
    margin: 0;
}

:deep(.el-dialog.tp-modal .el-dialog__body) {
    padding: 0;
}

:deep(.el-dialog.tp-modal .el-dialog__footer) {
    padding: 0;
}

.tp-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.tp-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tp-modal__head-text {
    flex: 1;
    min-width: 0;
}

.tp-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.tp-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.tp-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}

.tp-modal__close:hover {
    background: #e5e7eb;
    color: #111827;
}

.tp-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.tp-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tp-field__label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9375rem;
    font-weight: 400;
    color: #374151;
}

.tp-field__hint {
    font-size: 0.75rem;
    font-weight: 400;
    color: #6b7280;
    line-height: 1.4;
}

.tp-input :deep(.el-input__wrapper),
.tp-input :deep(.el-textarea__inner) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.tp-input :deep(.el-input__wrapper:hover),
.tp-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.tp-input :deep(.el-input__wrapper.is-focus),
.tp-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

/* ── Add-to-calendar switch ──────────────────────────────────────────── */
.tp-field--switch {
    margin-top: 4px;
    padding-top: 14px;
    border-top: 1px solid #f3f4f6;
}

.tp-switch-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.tp-switch-row__text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.tp-switch-row .tp-field__label {
    font-weight: 600;
    color: #111827;
}

.tp-switch :deep(.el-switch__core) {
    background: #d1d5db;
    border-color: #d1d5db;
}

.tp-switch.is-checked :deep(.el-switch__core) {
    background: #004532;
    border-color: #004532;
}

.tp-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.tp-modal__footer .tp-btn-primary,
.tp-modal__footer .tp-btn-outline {
    padding: 9px 18px;
}
</style>
