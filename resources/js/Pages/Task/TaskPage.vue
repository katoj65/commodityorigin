<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Delete, MagicStick, CircleCheck, Warning, Clock, List } from '@element-plus/icons-vue';

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
});

function openCreateDialog() {
    form.title = '';
    form.description = '';
    form.task_date = todayStr();
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
            <div class="tp-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <div class="tp-kicker"><el-icon><MagicStick /></el-icon> Decision Support</div>
                            <h1 class="tp-title mb-0">Tasks</h1>
                            <p class="tp-subtitle mb-0">The decisions and actions Coffee Pulse thinks need your attention.</p>
                        </div>
                        <button type="button" class="btn tp-btn-primary btn-sm" @click="openCreateDialog">
                            <el-icon><Plus /></el-icon> New Task
                        </button>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── KPI strip ────────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-3">
                        <div class="tp-kpi h-100"><span class="tp-kpi__label">Total</span><div class="tp-kpi__value">{{ kpis.total }}</div></div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="tp-kpi h-100"><span class="tp-kpi__label">Due Today</span><div class="tp-kpi__value tp-kpi__value--amber">{{ kpis.dueToday }}</div></div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="tp-kpi h-100"><span class="tp-kpi__label">Overdue</span><div class="tp-kpi__value tp-kpi__value--red">{{ kpis.overdue }}</div></div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="tp-kpi h-100"><span class="tp-kpi__label">Completed</span><div class="tp-kpi__value tp-kpi__value--green">{{ kpis.completed }}</div></div>
                    </div>
                </div>

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

        <el-dialog v-model="dialogOpen" title="New Task" width="420px" destroy-on-close align-center>
            <el-form label-position="top">
                <el-form-item label="Title">
                    <el-input v-model="form.title" placeholder="What needs a decision?" />
                </el-form-item>
                <el-form-item label="Task Date">
                    <el-date-picker v-model="form.task_date" type="date" value-format="YYYY-MM-DD" style="width:100%" />
                </el-form-item>
                <el-form-item label="Description">
                    <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Optional notes" />
                </el-form-item>
            </el-form>
            <template #footer>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="tp-btn-outline" @click="dialogOpen = false">Cancel</button>
                    <button type="button" class="tp-btn-primary" :disabled="saving" @click="saveTask">{{ saving ? 'Saving…' : 'Save Task' }}</button>
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

/* ── Header ──────────────────────────────────────────────────────────── */
.tp-header { background: #fff; border-bottom: 1px solid var(--border); }
.tp-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.tp-title { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.tp-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

.tp-btn-primary { background: #004532; border-color: #004532; color: #fff; border-radius: 6px; font-size: .8125rem; font-weight: 700; padding: 8px 14px; display: inline-flex; align-items: center; gap: 5px; }
.tp-btn-primary:hover { background: #002e20; }
.tp-btn-primary:disabled { opacity: .6; }
.tp-btn-outline { background: #fff; border: 1px solid #e5e7eb; color: #111827; border-radius: 6px; font-size: .8125rem; font-weight: 700; padding: 8px 14px; display: inline-flex; align-items: center; gap: 5px; }
.tp-btn-outline:hover { background: #f8fafc; }

/* ── KPI ─────────────────────────────────────────────────────────────── */
.tp-kpi { border: 1px solid var(--border); border-radius: 8px; padding: .875rem; background: #fff; }
.tp-kpi__label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); display: block; }
.tp-kpi__value { font-size: 1.25rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin-top: 4px; }
.tp-kpi__value--amber { color: #92400e; }
.tp-kpi__value--red { color: #991b1b; }
.tp-kpi__value--green { color: #166534; }

/* ── Filters ─────────────────────────────────────────────────────────── */
.tp-filters { display: flex; gap: 6px; flex-wrap: wrap; }
.tp-filter-tab { border: 1px solid var(--border); background: #fff; color: var(--on-surface-var); font-size: .75rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; cursor: pointer; transition: all .15s; }
.tp-filter-tab:hover { border-color: var(--green); color: var(--green); }
.tp-filter-tab--active { background: var(--green); border-color: var(--green); color: #fff; }

/* ── Section / list ──────────────────────────────────────────────────── */
.tp-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
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
</style>
