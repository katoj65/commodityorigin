<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { List, FullScreen, Check } from '@element-plus/icons-vue';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    title: { type: String, default: 'Tasks' },
});

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

const sortedTasks = computed(() => [...props.tasks].sort((a, b) => a.task_date.localeCompare(b.task_date)));

function relativeDateLabel(date) {
    const diff = Math.round((new Date(`${date}T00:00:00`) - new Date(`${todayStr()}T00:00:00`)) / 86400000);
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

function markComplete(task) {
    router.patch(route('task.update', task.id), {
        title: task.title,
        description: task.description,
        task_date: task.task_date,
        status: 'completed',
    }, { preserveScroll: true });
}
</script>

<template>
    <div class="task-widget">
        <div class="task-widget__head">
            <div class="task-widget__title"><el-icon><List /></el-icon> {{ title }}</div>
            <Link :href="route('task.index')" class="task-widget__link" aria-label="Open all tasks" title="Open all tasks">
                <el-icon :size="13"><FullScreen /></el-icon>
            </Link>
        </div>

        <p v-if="!sortedTasks.length" class="task-widget__empty">No tasks yet — turn a calendar event into one to get started.</p>

        <div v-else class="task-widget__list">
            <div v-for="task in sortedTasks" :key="task.id" class="task-row">
                <button
                    v-if="task.status !== 'completed'"
                    type="button"
                    class="task-row__check"
                    aria-label="Mark complete"
                    title="Mark complete"
                    @click="markComplete(task)"
                ></button>
                <el-icon v-else class="task-row__done"><Check /></el-icon>

                <div class="task-row__body">
                    <div class="task-row__title" :class="{ 'task-row__title--done': task.status === 'completed' }">{{ task.title }}</div>
                </div>

                <span class="task-badge" :class="toneFor(task)">{{ relativeDateLabel(task.task_date) }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.task-widget {
    font-family: 'Manrope', system-ui, sans-serif;
}

.task-widget__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.task-widget__title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    font-weight: 700;
    color: #111827;
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
}

.task-widget__link:hover {
    background: #f8fafc;
    color: #004532;
}

.task-widget__empty {
    font-size: 0.8125rem;
    color: #6b7280;
    margin: 0;
    padding: 8px 0;
}

.task-widget__list {
    display: flex;
    flex-direction: column;
}

.task-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f8fafc;
}

.task-row:last-child {
    border-bottom: none;
}

.task-row__check {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 1.5px solid #d1d5db;
    background: #fff;
    flex-shrink: 0;
    cursor: pointer;
    padding: 0;
}

.task-row__check:hover {
    border-color: #004532;
    background: rgba(0, 69, 50, 0.08);
}

.task-row__done {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #16a34a;
    color: #fff;
    font-size: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
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
</style>
