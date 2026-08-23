<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import CreateEventModal from '@/Components/Modals/CreateEventModal.vue';
import CreateTaskModal from '@/Components/Modals/CreateTaskModal.vue';
import { Plus, Delete, Edit, WarnTriangleFilled, Clock, Calendar as CalendarIcon, CircleCheck, Check, List, Odometer, Files } from '@element-plus/icons-vue';

const props = defineProps({
    events: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
});

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

const activeDate = ref(new Date());
const selectedDay = ref(todayStr());

const eventsByDay = computed(() => {
    const map = {};
    for (const e of props.events) {
        (map[e.event_date] ??= []).push(e);
    }
    return map;
});

const dueToday = computed(() => (eventsByDay.value[todayStr()] ?? []).filter((e) => e.status === 'pending'));

/* ── Overview stats ──────────────────────────────────────────────────── */
const thisWeekCount = computed(() => {
    const start = new Date(`${todayStr()}T00:00:00`);
    const end = new Date(start);
    end.setDate(end.getDate() + 7);
    return props.events.filter((e) => {
        const d = new Date(`${e.event_date}T00:00:00`);
        return d >= start && d < end;
    }).length;
});

const overdueCount = computed(() => props.events.filter((e) => e.status === 'pending' && e.event_date < todayStr()).length);

const completedCount = computed(() => props.events.filter((e) => e.status === 'completed').length);

function selectDay(day) {
    selectedDay.value = day;
}

function jumpToDay(day) {
    selectedDay.value = day;
    activeDate.value = new Date(`${day}T00:00:00`);
}

const sortedEvents = computed(() => [...props.events].sort((a, b) => a.event_date.localeCompare(b.event_date)));

/* ── Sidebar: Events / Tasks tabs ──────────────────────────────────────── */
const sideTab = ref('events');

/* ── Tasks (sidebar widget) ──────────────────────────────────────────── */
const sortedTasks = computed(() => [...props.tasks].sort((a, b) => a.task_date.localeCompare(b.task_date)));

function taskTone(task) {
    if (task.status === 'completed') return 'green';
    return task.task_date < todayStr() ? 'red' : 'amber';
}

const completedTasksCount = computed(() => props.tasks.filter((t) => t.status === 'completed').length);

const tasksProgressPct = computed(() => (
    props.tasks.length ? Math.round((completedTasksCount.value / props.tasks.length) * 100) : 0
));

function relativeDayLabel(day) {
    const diff = Math.round((new Date(`${day}T00:00:00`) - new Date(`${todayStr()}T00:00:00`)) / 86400000);
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Tomorrow';
    if (diff === -1) return 'Yesterday';
    return diff > 0 ? `In ${diff} days` : `${Math.abs(diff)} days ago`;
}

/* ── Create / edit event dialog ───────────────────────────────────────── */
const eventDialogOpen = ref(false);
const editingEvent = ref(null);

function openCreateEventDialog() {
    editingEvent.value = null;
    eventDialogOpen.value = true;
}

function openEditEventDialog(event) {
    editingEvent.value = event;
    eventDialogOpen.value = true;
}

/* ── Create task dialog ──────────────────────────────────────────────── */
const taskDialogOpen = ref(false);

function openCreateTaskDialog() {
    taskDialogOpen.value = true;
}

const confirmOpen = ref(false);
const pendingDelete = ref(null);

function deleteEvent(event) {
    pendingDelete.value = event;
    confirmOpen.value = true;
}

function confirmDeleteEvent() {
    if (!pendingDelete.value) return;
    router.delete(route('calendar.destroy', pendingDelete.value.id), { preserveScroll: true });
    pendingDelete.value = null;
}

const typeTone = (type) => ({
    task: 'clp-dot--green',
    deadline: 'clp-dot--red',
    harvest: 'clp-dot--amber',
    market: 'clp-dot--blue',
}[type] ?? 'clp-dot--muted');

const typeChipClass = (type) => ({
    task: 'clp-chip--task',
    deadline: 'clp-chip--deadline',
    harvest: 'clp-chip--harvest',
    market: 'clp-chip--market',
}[type] ?? 'clp-chip--muted');

const typeLabel = (type) => {
    if (!type) return 'Event';
    const known = { task: 'Task', deadline: 'Deadline', harvest: 'Harvest', market: 'Market' };
    return known[type] ?? (type.charAt(0).toUpperCase() + type.slice(1));
};

</script>

<template>
    <DesignPreviewLayout title="Calendar">
        <Head title="Calendar" />

        <div class="clp-page">

            <!-- ── Page Header ───────────────────────────────────────────── -->
            <div class="clp-page-header">
                <div class="clp-page-header__row">
                    <div class="clp-page-header__left">
                        <h1 class="clp-title">Calendar Activities</h1>
                        <p class="clp-subtitle">Plan events, track deadlines, and stay ahead of every decision across your trading operations.</p>
                    </div>
                    <div class="clp-page-header__actions">
                        <button type="button" class="clp-btn-outline" @click="openCreateTaskDialog">
                            <el-icon><List /></el-icon> Add Task
                        </button>
                        <button type="button" class="clp-btn-primary" @click="openCreateEventDialog">
                            <el-icon><Plus /></el-icon> New Event
                        </button>
                    </div>
                </div>

                <div v-if="dueToday.length" class="clp-ongoing">
                    <div class="clp-ongoing__icon">
                        <el-icon :size="14"><WarnTriangleFilled /></el-icon>
                    </div>
                    <span class="clp-ongoing__label">{{ dueToday.length }} event{{ dueToday.length > 1 ? 's' : '' }} ongoing today</span>
                    <div class="clp-ongoing__chips">
                        <button
                            v-for="ev in dueToday"
                            :key="ev.id"
                            type="button"
                            class="clp-ongoing__chip"
                            @click="openEditEventDialog(ev)"
                        >
                            <span class="clp-dot" :class="typeTone(ev.type)"></span>
                            {{ ev.title }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Body: 70% calendar+events / 30% overview+tasks ─────────── -->
            <div class="clp-body">

                <div class="clp-grid">
                    <!-- 70% -->
                    <div class="clp-col-main">
                        <div class="clp-main-card">
                            <el-calendar v-model="activeDate">
                                <template #date-cell="{ data }">
                                    <div
                                        class="clp-cell"
                                        :class="{ 'clp-cell--selected': data.day === selectedDay, 'clp-cell--other': data.type !== 'current-month' }"
                                        @click="selectDay(data.day)"
                                    >
                                        <span class="clp-cell__num">{{ data.date.getDate() }}</span>
                                        <div v-if="eventsByDay[data.day]?.length" class="clp-cell__chips">
                                            <button
                                                v-for="ev in eventsByDay[data.day].slice(0, 2)"
                                                :key="ev.id"
                                                type="button"
                                                class="clp-cell-chip"
                                                :class="typeChipClass(ev.type)"
                                                :title="ev.title"
                                                @click.stop="openEditEventDialog(ev)"
                                            >{{ ev.title }}</button>
                                            <span v-if="eventsByDay[data.day].length > 2" class="clp-cell-more">
                                                +{{ eventsByDay[data.day].length - 2 }} more
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </el-calendar>
                        </div>
                    </div>

                    <!-- 30% -->
                    <div class="clp-col-side">
                        <div class="clp-side-card">
                            <div class="clp-side-card__title"><el-icon :size="15"><Odometer /></el-icon> Overview</div>
                            <div class="clp-metric-list">
                                <div class="clp-metric">
                                    <span class="clp-metric__icon"><el-icon :size="14"><Files /></el-icon></span>
                                    <span class="clp-metric__body">
                                        <span class="clp-metric__label">Total Events</span>
                                        <strong class="clp-metric__val">{{ sortedEvents.length }}</strong>
                                    </span>
                                </div>
                                <div class="clp-metric">
                                    <span class="clp-metric__icon" :class="dueToday.length ? 'clp-metric__icon--amber' : ''"><el-icon :size="14"><Clock /></el-icon></span>
                                    <span class="clp-metric__body">
                                        <span class="clp-metric__label">Due Today</span>
                                        <strong class="clp-metric__val" :class="dueToday.length ? 'clp-text-amber' : ''">{{ dueToday.length }}</strong>
                                    </span>
                                </div>
                                <div class="clp-metric">
                                    <span class="clp-metric__icon"><el-icon :size="14"><CalendarIcon /></el-icon></span>
                                    <span class="clp-metric__body">
                                        <span class="clp-metric__label">This Week</span>
                                        <strong class="clp-metric__val">{{ thisWeekCount }}</strong>
                                    </span>
                                </div>
                                <div class="clp-metric">
                                    <span class="clp-metric__icon" :class="overdueCount ? 'clp-metric__icon--red' : ''"><el-icon :size="14"><WarnTriangleFilled /></el-icon></span>
                                    <span class="clp-metric__body">
                                        <span class="clp-metric__label">Overdue</span>
                                        <strong class="clp-metric__val" :class="overdueCount ? 'clp-text-red' : ''">{{ overdueCount }}</strong>
                                    </span>
                                </div>
                                <div class="clp-metric">
                                    <span class="clp-metric__icon clp-metric__icon--green"><el-icon :size="14"><CircleCheck /></el-icon></span>
                                    <span class="clp-metric__body">
                                        <span class="clp-metric__label">Completed</span>
                                        <strong class="clp-metric__val clp-text-green">{{ completedCount }}</strong>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="clp-side-card clp-side-card--fill">
                            <div class="clp-side-card__title-row">
                                <div class="clp-tabs">
                                    <button
                                        type="button"
                                        class="clp-tab"
                                        :class="{ 'clp-tab--active': sideTab === 'events' }"
                                        @click="sideTab = 'events'"
                                    >
                                        <el-icon :size="14"><Clock /></el-icon> Events
                                        <span class="clp-tab__count">{{ sortedEvents.length }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="clp-tab"
                                        :class="{ 'clp-tab--active': sideTab === 'tasks' }"
                                        @click="sideTab = 'tasks'"
                                    >
                                        <el-icon :size="14"><List /></el-icon> Tasks
                                        <span class="clp-tab__count">{{ sortedTasks.length }}</span>
                                    </button>
                                </div>
                                <div class="clp-tasks-head__actions">
                                    <button
                                        type="button"
                                        class="clp-mini-btn"
                                        :title="sideTab === 'events' ? 'Add event' : 'Add task'"
                                        @click="sideTab === 'events' ? openCreateEventDialog() : openCreateTaskDialog()"
                                    >
                                        <el-icon :size="12"><Plus /></el-icon>
                                    </button>
                                    <Link v-if="sideTab === 'tasks'" :href="route('task.index')" class="clp-side-card__link">View All</Link>
                                </div>
                            </div>

                            <template v-if="sideTab === 'events'">
                                <div v-if="!sortedEvents.length" class="clp-empty">No events yet — create one to get started.</div>
                                <div v-else class="clp-tab-list">
                                    <div
                                        v-for="ev in sortedEvents"
                                        :key="ev.id"
                                        class="clp-event-row"
                                        @click="jumpToDay(ev.event_date)"
                                    >
                                        <span class="clp-dot" :class="typeTone(ev.type)"></span>
                                        <div class="clp-event-row__body">
                                            <div class="clp-event-row__title">{{ ev.title }}</div>
                                            <div class="clp-event-row__date">{{ relativeDayLabel(ev.event_date) }} · {{ typeLabel(ev.type) }}</div>
                                        </div>
                                        <div class="clp-row__actions" @click.stop>
                                            <button type="button" class="clp-icon-btn" aria-label="Edit event" @click="openEditEventDialog(ev)">
                                                <el-icon><Edit /></el-icon>
                                            </button>
                                            <button type="button" class="clp-icon-btn clp-icon-btn--danger" aria-label="Delete event" @click="deleteEvent(ev)">
                                                <el-icon><Delete /></el-icon>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <div v-if="sortedTasks.length" class="clp-tasks-progress">
                                    <div class="clp-tasks-progress__bar">
                                        <div class="clp-tasks-progress__fill" :style="{ width: tasksProgressPct + '%' }"></div>
                                    </div>
                                    <span class="clp-tasks-progress__label">{{ completedTasksCount }}/{{ sortedTasks.length }} done</span>
                                </div>

                                <div v-if="!sortedTasks.length" class="clp-empty">No tasks yet.</div>

                                <div v-else class="clp-tab-list">
                                    <div
                                        v-for="t in sortedTasks"
                                        :key="t.id"
                                        class="clp-task-row"
                                        :class="{ 'clp-task-row--done': t.status === 'completed' }"
                                    >
                                        <span class="clp-task-check" :class="`clp-task-check--${taskTone(t)}`">
                                            <el-icon v-if="t.status === 'completed'" :size="11"><Check /></el-icon>
                                        </span>
                                        <div class="clp-task-row__body">
                                            <div class="clp-task-row__title">{{ t.title }}</div>
                                            <div class="clp-task-row__date" :class="{ 'clp-text-red': taskTone(t) === 'red' }">{{ relativeDayLabel(t.task_date) }}</div>
                                        </div>
                                        <span v-if="taskTone(t) === 'red'" class="clp-badge clp-badge--red">Overdue</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <CreateEventModal
            v-model="eventDialogOpen"
            :event="editingEvent"
            :default-date="selectedDay"
        />

        <CreateTaskModal
            v-model="taskDialogOpen"
            :default-date="selectedDay"
        />

        <ConfirmDialog
            v-model="confirmOpen"
            title="Delete Event"
            :message="pendingDelete ? `Delete “${pendingDelete.title}”? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDeleteEvent"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
.clp-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
}

/* ── Page header ─────────────────────────────────────────────────────── */
.clp-page-header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.clp-page-header__row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

/* ── Ongoing-today banner ────────────────────────────────────────────── */
.clp-ongoing {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px 12px;
    padding: 10px 14px;
    border-radius: 12px;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border: 1px solid #fde68a;
}

.clp-ongoing__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #f59e0b;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
}

.clp-ongoing__label {
    font-size: 0.8125rem;
    font-weight: 700;
    color: #92400e;
    white-space: nowrap;
}

.clp-ongoing__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.clp-ongoing__chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    border: 1px solid #fde68a;
    background: #fff;
    color: #78350f;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.12s ease, box-shadow 0.12s ease;
}

.clp-ongoing__chip:hover {
    background: #fffbeb;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.18);
}

.clp-ongoing__chip .clp-dot { margin-top: 0; }

.clp-page-header__left {
    max-width: 560px;
}

.clp-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--dp-secondary);
    margin-bottom: 4px;
}

.clp-title {
    font-size: clamp(1.375rem, 1.05rem + 1.2vw, 1.75rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.clp-subtitle {
    font-size: 0.875rem;
    color: var(--dp-on-surface-variant);
    margin: 0;
    line-height: 1.6;
}

.clp-page-header__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 4px;
}

.clp-text-green { color: var(--dp-secondary); }
.clp-text-amber { color: #92400e; }
.clp-text-red   { color: var(--dp-error); }

/* ── Body ────────────────────────────────────────────────────────────── */
.clp-empty {
    font-size: 0.8125rem;
    color: var(--dp-on-surface-variant);
    padding: 1rem 0;
    text-align: center;
}

.clp-side-card--fill .clp-empty {
    flex: 1;
    min-height: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Two-column layout: 70% calendar+events / 30% overview+tasks ───────── */
.clp-grid {
    display: grid;
    grid-template-columns: 7fr 3fr;
    gap: 1.25rem;
    align-items: stretch;
}

.clp-main-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    overflow: hidden;
}

/* ── Side column: Overview + Tasks cards ─────────────────────────────── */
.clp-col-side {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    height: 100%;
}

.clp-side-card {
    background: var(--dp-surface-container-lowest);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    padding: 1.25rem;
}

.clp-side-card--fill {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
}

.clp-side-card__title {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.875rem;
    font-weight: 800;
    color: var(--dp-on-surface);
}

.clp-side-card__title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    row-gap: 8px;
    gap: 10px;
    margin-bottom: 4px;
}

.clp-side-card__title :deep(.el-icon) { color: var(--dp-secondary); }

/* ── Tabs (Events / Tasks) ─────────────────────────────────────────────── */
.clp-tabs {
    display: flex;
    gap: 4px;
    background: var(--dp-surface-container-low);
    border-radius: 10px;
    padding: 3px;
}

.clp-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    background: transparent;
    color: var(--dp-on-surface-variant);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 6px 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.clp-tab:hover { color: var(--dp-on-surface); }

.clp-tab--active {
    background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface);
    box-shadow: 0 1px 3px rgba(39, 19, 16, 0.08);
}

.clp-tab__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    border-radius: 999px;
    background: var(--dp-secondary-container);
    color: var(--dp-on-secondary-container);
    font-size: 0.625rem;
    font-weight: 800;
}

.clp-tab:focus-visible {
    outline: 2px solid var(--dp-primary);
    outline-offset: 2px;
}

.clp-tab-list {
    display: flex;
    flex-direction: column;
    margin-top: 0.75rem;
    flex: 1;
    min-height: 0;
    overflow-y: auto;
}

/* ── Event row (sidebar tab) ──────────────────────────────────────────── */
.clp-event-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    margin: 0 -8px;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.clp-event-row:hover { background: var(--dp-surface-container-low); }

.clp-event-row .clp-dot { margin-top: 0; flex-shrink: 0; }

.clp-event-row__body { flex: 1; min-width: 0; }

.clp-event-row__title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--dp-on-surface);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.clp-event-row__date {
    font-size: 0.6875rem;
    color: var(--dp-on-surface-variant);
    margin-top: 1px;
}

.clp-side-card__link {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--dp-secondary);
    text-decoration: none;
    white-space: nowrap;
}

.clp-side-card__link:hover { color: var(--dp-secondary); }

.clp-tasks-head__actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.clp-mini-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 7px;
    border: 1px solid var(--dp-outline-variant);
    background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface-variant);
    cursor: pointer;
    transition: all 0.15s ease;
}

.clp-mini-btn:hover {
    border-color: var(--dp-secondary);
    color: var(--dp-on-secondary-container);
    background: var(--dp-secondary-container);
}

.clp-mini-btn:focus-visible {
    outline: 2px solid var(--dp-primary);
    outline-offset: 2px;
}

.clp-tasks-progress {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 0.75rem;
}

.clp-tasks-progress__bar {
    flex: 1;
    height: 6px;
    border-radius: 999px;
    background: var(--dp-surface-container-low);
    overflow: hidden;
}

.clp-tasks-progress__fill {
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, var(--dp-secondary), #16a34a);
    transition: width 0.2s ease;
}

.clp-tasks-progress__label {
    font-size: 0.6875rem;
    font-weight: 700;
    color: var(--dp-on-surface-variant);
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}

.clp-metric-list {
    display: flex;
    flex-direction: column;
    margin-top: 0.5rem;
}

.clp-metric {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 8px;
    margin: 0 -8px;
    border-radius: 10px;
    transition: background 0.15s ease;
}

.clp-metric:hover { background: var(--dp-surface-container-low); }

.clp-metric__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 9px;
    flex-shrink: 0;
    background: var(--dp-surface-container-low);
    color: var(--dp-primary);
    transition: background 0.15s ease, color 0.15s ease;
}

.clp-metric__icon--amber { background: #fef3c7; color: #92400e; }
.clp-metric__icon--red { background: var(--dp-error-container); color: var(--dp-error); }
.clp-metric__icon--green { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }

.clp-metric__body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    min-width: 0;
}

.clp-metric__label {
    font-size: 0.8125rem;
    color: var(--dp-on-surface-variant);
    font-weight: 600;
}

.clp-metric__val {
    font-size: 1rem;
    font-weight: 800;
    color: var(--dp-on-surface);
    letter-spacing: -0.01em;
    font-variant-numeric: tabular-nums;
}

.clp-task-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    margin: 0 -8px;
    border-radius: 10px;
    transition: background 0.15s ease;
}

.clp-task-row:hover { background: var(--dp-surface-container-low); }

.clp-task-check {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1.5px solid var(--dp-outline-variant);
    color: var(--dp-on-primary);
    flex-shrink: 0;
    transition: background 0.15s ease, border-color 0.15s ease;
}

.clp-task-check--green { background: var(--dp-secondary); border-color: var(--dp-secondary); }
.clp-task-check--red { border-color: var(--dp-error); }
.clp-task-check--amber { border-color: #fcd34d; }

.clp-task-row__body {
    flex: 1;
    min-width: 0;
}

.clp-task-row__title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--dp-on-surface);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.15s ease;
}

.clp-task-row--done .clp-task-row__title {
    color: var(--dp-on-surface-variant);
    text-decoration: line-through;
    font-weight: 600;
}

.clp-task-row__date {
    font-size: 0.6875rem;
    color: var(--dp-on-surface-variant);
    margin-top: 1px;
}

.clp-row__actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

.clp-icon-btn {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    border: 1px solid var(--dp-outline-variant);
    background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface-variant);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    transition: background 0.15s ease;
}

.clp-icon-btn:hover {
    background: var(--dp-surface-container-low);
}

.clp-icon-btn:focus-visible {
    outline: 2px solid var(--dp-primary);
    outline-offset: 2px;
}

.clp-icon-btn--danger:hover {
    background: var(--dp-error-container);
    border-color: var(--dp-error-container);
    color: var(--dp-error);
}

.clp-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--dp-secondary);
    flex-shrink: 0;
    margin-top: 4px;
}

.clp-dot--green { background: var(--dp-secondary); }
.clp-dot--red { background: var(--dp-error); }
.clp-dot--amber { background: #d97706; }
.clp-dot--blue { background: #2563eb; }
.clp-dot--muted { background: var(--dp-outline); }

.clp-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.625rem;
    font-weight: 700;
    padding: 2px 8px;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
}

.clp-badge--red {
    background: var(--dp-error-container);
    color: var(--dp-on-error-container);
}

.clp-btn-primary {
    background: var(--dp-primary);
    border: none;
    color: var(--dp-on-primary);
    border-radius: 999px;
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 10px 18px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.15s ease;
}

.clp-btn-primary:hover {
    opacity: 0.88;
    color: var(--dp-on-primary);
}

.clp-btn-primary:focus-visible {
    outline: 2px solid var(--dp-primary);
    outline-offset: 2px;
}

.clp-btn-primary:disabled {
    opacity: 0.6;
    cursor: default;
}

.clp-btn-outline {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--dp-outline-variant);
    color: var(--dp-on-surface);
    border-radius: 10px;
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 10px 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease;
}

.clp-btn-outline:hover {
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface);
}

.clp-btn-outline:focus-visible {
    outline: 2px solid var(--dp-primary);
    outline-offset: 2px;
}

/* ── Calendar cell ────────────────────────────────────────────────────── */
.clp-cell {
    height: 100%;
    min-height: 96px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 10px 12px;
    cursor: pointer;
    transition: background 0.12s;
}

.clp-cell:hover {
    background: var(--dp-surface-container-low);
}

.clp-cell--selected {
    background: rgba(27, 109, 36, 0.06);
    box-shadow: inset 0 0 0 1.5px var(--dp-secondary);
}

.clp-cell--other {
    opacity: 0.4;
}

.clp-cell__num {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--dp-on-surface);
}

.clp-cell--selected .clp-cell__num {
    color: var(--dp-secondary);
    font-weight: 800;
}

.clp-cell__chips {
    display: flex;
    flex-direction: column;
    gap: 3px;
    margin-top: 6px;
    width: 100%;
}

.clp-cell-chip {
    display: block;
    width: 100%;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: left;
    border: none;
    cursor: pointer;
    font-family: inherit;
}

.clp-chip--task { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.clp-chip--deadline { background: var(--dp-error-container); color: var(--dp-on-error-container); }
.clp-chip--harvest { background: #fef3c7; color: #92400e; }
.clp-chip--market { background: #dbeafe; color: #1e40af; }
.clp-chip--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

.clp-cell-more {
    font-size: 0.625rem;
    font-weight: 700;
    color: var(--dp-on-surface-variant);
    padding: 1px 6px;
}

/* ── Element Plus calendar overrides ────────────────────────────────────
   Native Element Plus grid: bordered day cells, like a spreadsheet. The
   calendar's own border shorthand must be a full `width style color`
   value — a bare color silently drops the border. The card itself
   (.clp-main-card) stays borderless (shadow only); these are strictly
   the internal grid lines between cells, kept hairline-light so they
   don't compete with the card's own edge. */
.clp-main-card {
    --clp-hairline: color-mix(in srgb, var(--dp-outline-variant) 25%, transparent);
}

.clp-main-card :deep(.el-calendar) {
    --el-calendar-border: 0.5px solid var(--clp-hairline);
}

.clp-main-card :deep(.el-calendar__header) {
    padding: 14px 16px;
    border-bottom: 0.5px solid var(--clp-hairline);
}

.clp-main-card :deep(.el-calendar__title) {
    font-weight: 700;
    color: var(--dp-on-surface);
}

.clp-main-card :deep(.el-calendar__body) {
    padding: 0;
}

.clp-main-card :deep(.el-calendar-table) {
    border-collapse: collapse;
}

.clp-main-card :deep(.el-calendar-table .el-calendar-day) {
    height: auto;
    padding: 0;
}

.clp-main-card :deep(.el-calendar-table td) {
    border-color: var(--clp-hairline);
}

.clp-main-card :deep(.el-calendar-table thead tr) {
    background: var(--dp-surface-container-low);
}

.clp-main-card :deep(.el-calendar-table th) {
    padding: 13px 12px;
    font-size: 0.6875rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--dp-on-surface-variant);
    text-align: left;
    border-bottom: 0.5px solid var(--clp-hairline);
    border-right: 0.5px solid var(--clp-hairline);
}

.clp-main-card :deep(.el-calendar-table th:last-child) {
    border-right: none;
}

.clp-main-card :deep(.el-calendar-table th:first-child),
.clp-main-card :deep(.el-calendar-table th:last-child) {
    color: var(--dp-secondary);
}

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .clp-grid {
        grid-template-columns: 1fr;
    }
}

/* ── Reduced motion ────────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .clp-cell,
    .clp-metric,
    .clp-task-row,
    .clp-icon-btn,
    .clp-mini-btn,
    .clp-btn-primary,
    .clp-btn-outline,
    .clp-tasks-progress__fill {
        transition: none;
    }
}
</style>
