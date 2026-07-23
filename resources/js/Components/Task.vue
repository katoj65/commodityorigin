<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    List, FullScreen, Check, MagicStick, Close, Files, Clock,
    CircleCheck, RefreshLeft, Delete,
} from '@element-plus/icons-vue';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    title: { type: String, default: 'Tasks' },
});

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

const sortedTasks = computed(() => [...props.tasks].sort((a, b) => a.task_date.localeCompare(b.task_date)));

const pendingCount = computed(() => props.tasks.filter((t) => t.status === 'pending').length);

function relativeDateLabel(task) {
    if (task.status === 'completed') return 'Completed';

    const diff = Math.round((new Date(`${task.task_date}T00:00:00`) - new Date(`${todayStr()}T00:00:00`)) / 86400000);
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Tomorrow';
    if (diff === -1) return 'Yesterday';
    return diff > 0 ? `In ${diff} days` : `${Math.abs(diff)} days overdue`;
}

function toneFor(task) {
    if (task.status === 'completed') return 'task-badge--green';
    if (task.task_date < todayStr()) return 'task-badge--red';
    if (task.task_date === todayStr()) return 'task-badge--amber';
    return 'task-badge--muted';
}

function formattedDate(date) {
    return new Date(`${date}T00:00:00`).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
}

/* ── Task detail dialog ─────────────────────────────────────────────── */
const dialogOpen = ref(false);
const activeTaskId = ref(null);
const activeTask = computed(() => props.tasks.find((t) => t.id === activeTaskId.value) ?? null);

function openTask(task) {
    activeTaskId.value = task.id;
    dialogOpen.value = true;
}

function toggleStatus() {
    if (!activeTask.value) return;

    const { id, title, description, task_date: taskDate, status } = activeTask.value;
    dialogOpen.value = false;

    router.patch(route('task.update', id), {
        title,
        description,
        task_date: taskDate,
        status: status === 'completed' ? 'pending' : 'completed',
    }, { preserveScroll: true });
}

const confirmDeleteOpen = ref(false);

function requestDelete() {
    dialogOpen.value = false;
    confirmDeleteOpen.value = true;
}

function confirmDelete() {
    if (!activeTask.value) return;

    router.delete(route('task.destroy', activeTask.value.id), { preserveScroll: true });
}
</script>

<template>
    <div class="task-widget">
        <div class="task-widget__head">
            <div class="task-widget__title-group">
                <span class="task-widget__icon"><el-icon :size="14"><List /></el-icon></span>
                <div class="task-widget__title">{{ title }}</div>
                <span v-if="pendingCount" class="task-widget__count">{{ pendingCount }}</span>
            </div>
            <Link :href="route('task.index')" class="task-widget__link" aria-label="Open all tasks" title="Open all tasks">
                <el-icon :size="13"><FullScreen /></el-icon>
            </Link>
        </div>

        <div v-if="!sortedTasks.length" class="task-widget__empty">
            <span class="task-widget__empty-icon"><el-icon :size="18"><MagicStick /></el-icon></span>
            <p>No tasks yet — turn a calendar event into one to get started.</p>
        </div>

        <div v-else class="task-widget__list">
            <button
                v-for="task in sortedTasks"
                :key="task.id"
                type="button"
                class="task-row"
                @click="openTask(task)"
            >
                <span class="task-row__status" :class="{ 'task-row__status--done': task.status === 'completed' }">
                    <el-icon v-if="task.status === 'completed'" :size="10"><Check /></el-icon>
                </span>

                <div class="task-row__body">
                    <div class="task-row__title" :class="{ 'task-row__title--done': task.status === 'completed' }">{{ task.title }}</div>
                </div>

                <span class="task-badge" :class="toneFor(task)">{{ relativeDateLabel(task) }}</span>
            </button>
        </div>

        <el-dialog
            v-model="dialogOpen"
            width="420px"
            destroy-on-close
            align-center
            :show-close="false"
            class="task-modal"
        >
            <template v-if="activeTask" #header>
                <div class="task-modal__head">
                    <span class="task-modal__head-icon" :class="{ 'task-modal__head-icon--done': activeTask.status === 'completed' }">
                        <el-icon :size="18"><CircleCheck /></el-icon>
                    </span>
                    <div class="task-modal__head-text">
                        <div class="task-modal__eyebrow">Task</div>
                        <div class="task-modal__title">{{ activeTask.title }}</div>
                    </div>
                    <button type="button" class="task-modal__close" aria-label="Close" @click="dialogOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div v-if="activeTask" class="task-modal__body">
                <div class="task-modal__field">
                    <span class="task-modal__label"><el-icon :size="12"><Clock /></el-icon> Due</span>
                    <div class="task-modal__due">
                        <span>{{ formattedDate(activeTask.task_date) }}</span>
                        <span class="task-badge" :class="toneFor(activeTask)">{{ relativeDateLabel(activeTask) }}</span>
                    </div>
                </div>

                <div v-if="activeTask.description" class="task-modal__field">
                    <span class="task-modal__label"><el-icon :size="12"><Files /></el-icon> Notes</span>
                    <p class="task-modal__desc">{{ activeTask.description }}</p>
                </div>
            </div>

            <template #footer>
                <div class="task-modal__footer">
                    <button type="button" class="task-btn-outline task-btn-outline--danger" @click="requestDelete">
                        <el-icon :size="13"><Delete /></el-icon> Delete
                    </button>
                    <button type="button" class="task-btn-primary" @click="toggleStatus">
                        <el-icon :size="13"><component :is="activeTask?.status === 'completed' ? RefreshLeft : CircleCheck" /></el-icon>
                        {{ activeTask?.status === 'completed' ? 'Reopen Task' : 'Mark Complete' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="confirmDeleteOpen"
            title="Delete Task"
            :message="activeTask ? `Delete “${activeTask.title}”? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDelete"
        />
    </div>
</template>

<style scoped>
.task-widget {
    font-family: 'Manrope', system-ui, sans-serif;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.task-widget__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.task-widget__title-group {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.task-widget__icon {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.task-widget__title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #111827;
}

.task-widget__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #fef3c7;
    color: #92400e;
    font-size: 0.625rem;
    font-weight: 800;
}

.task-widget__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    color: #6b7280;
    text-decoration: none;
    transition: background 0.12s, color 0.12s;
}

.task-widget__link:hover {
    background: #f8fafc;
    color: #004532;
}

.task-widget__empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-align: center;
    padding: 1.5rem 0.5rem;
}

.task-widget__empty-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #f8fafc;
    color: #9ca3af;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.task-widget__empty p {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

.task-widget__list {
    display: flex;
    flex-direction: column;
}

.task-row {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 8px 6px;
    margin: 0 -6px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.12s;
    font-family: inherit;
}

.task-row:hover {
    background: #f8fafc;
}

.task-row__status {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 1.5px solid #d1d5db;
    background: #fff;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.task-row__status--done {
    border-color: #16a34a;
    background: #16a34a;
    color: #fff;
}

.task-row__body {
    flex: 1;
    min-width: 0;
}

.task-row__title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.task-row__title--done {
    color: #9ca3af;
    text-decoration: line-through;
}

.task-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.625rem;
    font-weight: 700;
    padding: 3px 9px;
    flex-shrink: 0;
    white-space: nowrap;
}

.task-badge--green { background: #dcfce7; color: #166534; }
.task-badge--amber { background: #fef3c7; color: #92400e; }
.task-badge--red { background: #fee2e2; color: #991b1b; }
.task-badge--muted { background: #f3f4f6; color: #6b7280; }

/* ── Task detail dialog ────────────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .task-widget's
   DOM subtree, so scoped styles here still apply (Vue keeps the scope
   attribute on teleported content), but colors are literal hex on purpose
   for consistency with the Calendar/Task page modals. */
:deep(.el-dialog.task-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.task-modal .el-dialog__header) {
    padding: 0;
    margin: 0;
}

:deep(.el-dialog.task-modal .el-dialog__body) {
    padding: 0;
}

:deep(.el-dialog.task-modal .el-dialog__footer) {
    padding: 0;
}

.task-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.task-modal__head-icon {
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

.task-modal__head-icon--done {
    background: #dcfce7;
    color: #16a34a;
}

.task-modal__head-text {
    flex: 1;
    min-width: 0;
}

.task-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.task-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
    line-height: 1.3;
}

.task-modal__close {
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

.task-modal__close:hover {
    background: #e5e7eb;
    color: #111827;
}

.task-modal__body {
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    max-height: 60vh;
    overflow-y: auto;
}

.task-modal__field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.task-modal__label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.task-modal__due {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
}

.task-modal__desc {
    font-size: 0.8125rem;
    color: #374151;
    line-height: 1.55;
    margin: 0;
    background: #f9fafb;
    border: 1px solid #f3f4f6;
    border-radius: 10px;
    padding: 10px 12px;
}

.task-modal__footer {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.task-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}

.task-btn-primary:hover {
    opacity: 0.9;
}

.task-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.task-btn-outline:hover {
    background: #f8fafc;
}

.task-btn-outline--danger:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #991b1b;
}
</style>
