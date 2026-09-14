<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import CreateEventModal from '@/Components/Modals/CreateEventModal.vue';
import CreateTaskModal from '@/Components/Modals/CreateTaskModal.vue';
import {
    Plus, Delete, Check,
    Download, ArrowLeft, ArrowRight, Calendar as CalendarIcon,
} from '@element-plus/icons-vue';

const props = defineProps({
    events: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
});

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

const activeDate = ref(new Date());
const selectedDay = ref(todayStr());
const calendarRef = ref(null);

function shortDate(dateStr) {
    return new Date(`${dateStr}T00:00:00`).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

const eventsByDay = computed(() => {
    const map = {};
    for (const e of props.events) {
        (map[e.event_date] ??= []).push(e);
    }
    return map;
});

function selectDay(day) {
    selectedDay.value = day;
}

function jumpToDay(day) {
    selectedDay.value = day;
    activeDate.value = new Date(`${day}T00:00:00`);
}

const sortedEvents = computed(() => [...props.events].sort((a, b) => a.event_date.localeCompare(b.event_date)));

/* ── Selected-day inspector — real events recorded on whichever day was
   last clicked in the calendar grid (defaults to today). ──────────────── */
const selectedDayEvents = computed(() => (eventsByDay.value[selectedDay.value] ?? []).sort((a, b) => a.title.localeCompare(b.title)));

function selectedDayLabel() {
    const date = new Date(`${selectedDay.value}T00:00:00`);
    return date.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' });
}

/* ── Real/overdue tone for a single event row — reused by the Selected
   Day list and the Upcoming list so an overdue item is flagged inline
   instead of needing its own dedicated banner card. ────────────────────── */
function eventTone(ev) {
    if (ev.status === 'completed') return 'green';
    return ev.event_date < todayStr() ? 'red' : 'neutral';
}

/* ── Upcoming — the next real events over the coming week, sorted by
   date; capped so the sidebar stays short rather than scrolling. ───────── */
const upcomingEvents = computed(() => {
    const today = todayStr();
    const weekOut = new Date(`${today}T00:00:00`);
    weekOut.setDate(weekOut.getDate() + 7);
    const weekOutStr = weekOut.toISOString().slice(0, 10);
    return sortedEvents.value
        .filter((e) => e.event_date >= today && e.event_date <= weekOutStr)
        .slice(0, 6);
});

const activeMonthLabel = computed(() => activeDate.value.toLocaleDateString(undefined, { month: 'long', year: 'numeric' }));

/* ── Export — a genuine .ics file built straight from the real events
   array (no placeholder calendar entries). ─────────────────────────────── */
function exportEvents() {
    const escape = (s) => String(s ?? '').replace(/[\\;,]/g, (m) => `\\${m}`).replace(/\n/g, '\\n');
    const lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Bean Origin//Calendar//EN'];
    for (const e of sortedEvents.value) {
        const dt = e.event_date.replace(/-/g, '');
        lines.push(
            'BEGIN:VEVENT',
            `UID:calendar-event-${e.id}@beanorigin`,
            `DTSTART;VALUE=DATE:${dt}`,
            `SUMMARY:${escape(e.title)}`,
            `STATUS:${e.status === 'completed' ? 'CONFIRMED' : 'TENTATIVE'}`,
            ...(e.description ? [`DESCRIPTION:${escape(e.description)}`] : []),
            'END:VEVENT',
        );
    }
    lines.push('END:VCALENDAR');
    const blob = new Blob([lines.join('\r\n')], { type: 'text/calendar;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'bean-origin-calendar.ics';
    link.click();
    URL.revokeObjectURL(url);
}

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

/* ── Toggle a task's completion straight from the calendar sidebar —
   task.update requires the full record, so the current title/date ride
   along unchanged with only `status` flipped. ──────────────────────────── */
const togglingTaskId = ref(null);
function toggleTask(task) {
    togglingTaskId.value = task.id;
    router.patch(route('task.update', task.id), {
        title: task.title,
        description: task.description,
        task_date: task.task_date,
        status: task.status === 'completed' ? 'pending' : 'completed',
    }, {
        preserveScroll: true,
        onFinish: () => { togglingTaskId.value = null; },
    });
}

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
    <DesignPreviewLayout title="Calendar" flush-header>
        <Head title="Calendar" />

        <div class="clp-page">

            <!-- ── Page Header + Month Nav ───────────────────────────────── -->
            <div class="clp-page-header">
                <div class="clp-page-header__row">
                    <div class="clp-page-header__left">
                        <h1 class="clp-title">Calendar</h1>
                        <p class="clp-subtitle">Manage intakes, deadlines, and quality sessions in one place.</p>
                    </div>
                    <div class="clp-page-header__actions">
                        <div class="clp-nav">
                            <button type="button" class="clp-nav__btn" title="Previous month" @click="calendarRef?.selectDate('prev-month')">
                                <el-icon :size="15"><ArrowLeft /></el-icon>
                            </button>
                            <span class="clp-nav__label"><el-icon :size="13"><CalendarIcon /></el-icon> {{ activeMonthLabel }}</span>
                            <button type="button" class="clp-nav__btn" title="Next month" @click="calendarRef?.selectDate('next-month')">
                                <el-icon :size="15"><ArrowRight /></el-icon>
                            </button>
                        </div>
                        <button type="button" class="clp-btn-outline" @click="calendarRef?.selectDate('today')">Today</button>
                        <button type="button" class="clp-icon-btn" title="Export calendar as iCal" @click="exportEvents">
                            <el-icon><Download /></el-icon>
                        </button>
                        <button type="button" class="clp-btn-primary" @click="openCreateEventDialog">
                            <el-icon><Plus /></el-icon> New Event
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Body: calendar + sidebar ──────────────────────────────── -->
            <div class="clp-body">

                <div class="clp-grid">
                    <!-- Calendar -->
                    <div class="clp-col-main">
                        <div class="clp-main-card">
                            <el-calendar ref="calendarRef" v-model="activeDate">
                                <template #date-cell="{ data }">
                                    <div
                                        class="clp-cell"
                                        :class="{ 'clp-cell--selected': data.day === selectedDay, 'clp-cell--today': data.day === todayStr(), 'clp-cell--other': data.type !== 'current-month' }"
                                        @click="selectDay(data.day)"
                                    >
                                        <div class="clp-cell__head">
                                            <span v-if="data.day === todayStr()" class="clp-cell__today-badge">{{ data.date.getDate() }}</span>
                                            <span v-else class="clp-cell__num">{{ data.date.getDate() }}</span>
                                        </div>
                                        <div v-if="eventsByDay[data.day]?.length" class="clp-cell__chips">
                                            <button
                                                v-for="ev in eventsByDay[data.day].slice(0, 2)"
                                                :key="ev.id"
                                                type="button"
                                                class="clp-cell-chip"
                                                :class="typeChipClass(ev.type)"
                                                :title="ev.title"
                                                @click.stop="openEditEventDialog(ev)"
                                            >
                                                <span class="clp-cell-chip__dot" :class="typeTone(ev.type)"></span>
                                                <span class="clp-cell-chip__label">{{ ev.title }}</span>
                                            </button>
                                            <span v-if="eventsByDay[data.day].length > 2" class="clp-cell-more">
                                                +{{ eventsByDay[data.day].length - 2 }} more
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </el-calendar>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="clp-col-side">
                        <!-- Selected Day -->
                        <div class="clp-side-card clp-inspector">
                            <div class="clp-inspector__head">
                                <div>
                                    <div class="clp-inspector__eyebrow">Selected Day</div>
                                    <h3 class="clp-inspector__date">{{ selectedDayLabel() }}</h3>
                                </div>
                                <span class="clp-badge">{{ selectedDayEvents.length }} Event{{ selectedDayEvents.length === 1 ? '' : 's' }}</span>
                            </div>

                            <div v-if="selectedDayEvents.length" class="clp-inspector__list">
                                <div
                                    v-for="ev in selectedDayEvents"
                                    :key="ev.id"
                                    class="clp-inspector__item"
                                    @click="openEditEventDialog(ev)"
                                >
                                    <div class="clp-inspector__item-top">
                                        <span class="clp-dot" :class="typeTone(ev.type)"></span>
                                        <span class="clp-inspector__item-title">{{ ev.title }}</span>
                                        <button type="button" class="clp-inspector__item-del" aria-label="Delete event" @click.stop="deleteEvent(ev)">
                                            <el-icon :size="12"><Delete /></el-icon>
                                        </button>
                                    </div>
                                    <p v-if="ev.description" class="clp-inspector__item-desc">{{ ev.description }}</p>
                                    <span class="clp-badge" :class="`clp-badge--${eventTone(ev)}`">
                                        {{ ev.status === 'completed' ? 'Done' : (eventTone(ev) === 'red' ? 'Overdue' : typeLabel(ev.type)) }}
                                    </span>
                                </div>
                            </div>
                            <p v-else class="clp-inspector__empty">No events recorded for this day.</p>

                            <button type="button" class="clp-add-btn" @click="openCreateEventDialog">
                                <el-icon><Plus /></el-icon> Add Event for {{ shortDate(selectedDay) }}
                            </button>
                        </div>

                        <!-- Upcoming This Week -->
                        <div class="clp-side-card">
                            <div class="clp-side-card__head">
                                <h3 class="clp-side-card__title">Upcoming This Week</h3>
                                <span class="clp-badge">{{ upcomingEvents.length }} Scheduled</span>
                            </div>
                            <div v-if="upcomingEvents.length" class="clp-simple-list">
                                <div v-for="ev in upcomingEvents" :key="ev.id" class="clp-simple-row" @click="jumpToDay(ev.event_date)">
                                    <div class="clp-simple-row__main">
                                        <span class="clp-simple-row__title">{{ ev.title }}</span>
                                        <span class="clp-simple-row__sub">{{ typeLabel(ev.type) }}</span>
                                    </div>
                                    <div class="clp-simple-row__side">
                                        <span class="clp-simple-row__date">{{ relativeDayLabel(ev.event_date) }}</span>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="clp-empty">Nothing scheduled this week.</p>
                        </div>

                        <!-- Tasks -->
                        <div class="clp-side-card">
                            <div class="clp-side-card__head">
                                <h3 class="clp-side-card__title">Tasks</h3>
                                <div class="clp-side-card__head-actions">
                                    <span class="clp-badge">{{ completedTasksCount }} of {{ sortedTasks.length }} Done</span>
                                    <button type="button" class="clp-mini-btn" title="Add task" @click="openCreateTaskDialog">
                                        <el-icon :size="12"><Plus /></el-icon>
                                    </button>
                                </div>
                            </div>
                            <div v-if="sortedTasks.length" class="clp-simple-list">
                                <div
                                    v-for="t in sortedTasks"
                                    :key="t.id"
                                    class="clp-task-row"
                                    :class="{ 'clp-task-row--done': t.status === 'completed' }"
                                >
                                    <button
                                        type="button"
                                        class="clp-task-check"
                                        :class="`clp-task-check--${taskTone(t)}`"
                                        :disabled="togglingTaskId === t.id"
                                        aria-label="Toggle task complete"
                                        @click="toggleTask(t)"
                                    >
                                        <el-icon v-if="t.status === 'completed'" :size="11"><Check /></el-icon>
                                    </button>
                                    <div class="clp-task-row__body">
                                        <div class="clp-task-row__title">{{ t.title }}</div>
                                        <div class="clp-task-row__date" :class="{ 'clp-text-red': taskTone(t) === 'red' }">{{ relativeDayLabel(t.task_date) }}</div>
                                    </div>
                                    <span v-if="taskTone(t) === 'red'" class="clp-badge clp-badge--red">Overdue</span>
                                </div>
                            </div>
                            <p v-else class="clp-empty">No tasks yet.</p>
                            <Link :href="route('task.index')" class="clp-side-card__link">View All Tasks</Link>
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
    --card-border: var(--dp-outline-variant);
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

.clp-page-header__left {
    max-width: 560px;
}

.clp-title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    margin: 0 0 6px;
}

.clp-subtitle {
    font-size: .9375rem;
    line-height: 1.5rem;
    font-weight: 400;
    color: var(--dp-on-surface-variant);
    margin: 0;
}

.clp-page-header__actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.clp-text-red { color: var(--dp-error); }

/* ── Month navigation pill ─────────────────────────────────────────────── */
.clp-nav {
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 3px;
    background: var(--dp-surface-container-low);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 10px;
}
.clp-nav__btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 7px; border: none;
    background: transparent; color: var(--dp-on-surface-variant); cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}
.clp-nav__btn:hover { background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); }
.clp-nav__label {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 0 8px; font-size: 0.8125rem; font-weight: 800; color: var(--dp-on-surface);
    white-space: nowrap;
}
.clp-nav__label .el-icon { color: var(--dp-on-surface-variant); }

/* ── Category filter / export toolbar ─────────────────────────────────── */
.clp-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 18px;
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
}
.clp-toolbar__chips { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
.clp-toolbar__label { font-size: 0.625rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em; color: var(--dp-on-surface-variant); margin-right: 2px; }
.clp-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: 999px; border: 1px solid var(--dp-outline-variant);
    background: var(--dp-surface-container-lowest); color: var(--dp-on-surface-variant);
    font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.clp-chip:hover { background: var(--dp-surface-container-low); color: var(--dp-on-surface); border-color: var(--dp-outline); }
.clp-chip--active { background: var(--dp-primary); color: var(--dp-on-primary); border-color: var(--dp-primary); }
.clp-chip--active .clp-dot { background: var(--dp-on-primary); }
.clp-chip__count { font-family: ui-monospace, monospace; font-size: 0.6875rem; opacity: 0.75; }

/* ── Body ────────────────────────────────────────────────────────────── */
.clp-empty {
    font-size: 0.8125rem;
    color: var(--dp-on-surface-variant);
    padding: 1rem 0;
    text-align: center;
}

/* ── Two-column layout: calendar / sidebar ─────────────────────────────── */
.clp-grid {
    display: grid;
    grid-template-columns: 7fr 3fr;
    gap: 1.25rem;
    align-items: stretch;
}

.clp-main-card {
    background: var(--dp-surface-container-lowest);
    border: 1px solid var(--card-border);
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
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius);
    box-shadow: var(--dp-card-shadow);
    padding: 1.25rem;
}

/* ── Side card shared header (title + trailing badge/actions) ─────────── */
.clp-side-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
}
.clp-side-card__head-actions { display: flex; align-items: center; gap: 8px; }
.clp-side-card__title {
    font-size: 0.8125rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--dp-on-surface);
    margin: 0;
}
.clp-side-card__link {
    display: block;
    margin-top: 10px;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--dp-on-surface-variant);
    text-decoration: none;
    text-align: center;
}
.clp-side-card__link:hover { text-decoration: underline; }

/* ── Selected-day inspector ────────────────────────────────────────────── */
.clp-inspector__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--card-border); }
.clp-inspector__eyebrow { font-size: 0.625rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--dp-on-surface-variant); margin-bottom: 2px; }
.clp-inspector__date { font-size: 1rem; font-weight: 800; color: var(--dp-on-surface); margin: 0; }
.clp-inspector__list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.clp-inspector__item {
    padding: 10px 12px;
    border-radius: 10px;
    background: var(--dp-surface-container-low);
    border: 1px solid var(--card-border);
    cursor: pointer;
    transition: border-color 0.15s ease;
}
.clp-inspector__item:hover { border-color: var(--dp-outline); }
.clp-inspector__item-top { display: flex; align-items: center; gap: 8px; }
.clp-inspector__item-top .clp-dot { margin-top: 0; flex-shrink: 0; }
.clp-inspector__item-title { flex: 1; min-width: 0; font-size: 0.8125rem; font-weight: 700; color: var(--dp-on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.clp-inspector__item-del {
    flex-shrink: 0; width: 22px; height: 22px; border-radius: 6px; border: none;
    background: transparent; color: var(--dp-on-surface-variant); display: flex; align-items: center; justify-content: center;
    cursor: pointer; opacity: 0; transition: opacity 0.15s ease, background 0.15s ease, color 0.15s ease;
}
.clp-inspector__item:hover .clp-inspector__item-del { opacity: 1; }
.clp-inspector__item-del:hover { background: var(--dp-error-container); color: var(--dp-error); }
.clp-inspector__item-desc { font-size: 0.75rem; color: var(--dp-on-surface-variant); margin: 6px 0 8px; }
.clp-inspector__empty { font-size: 0.8125rem; color: var(--dp-on-surface-variant); margin: 0 0 12px; }

.clp-add-btn {
    width: 100%;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 14px; border-radius: 10px; border: 1px solid var(--dp-outline-variant);
    background: var(--dp-surface-container-low); color: var(--dp-on-surface);
    font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: background 0.15s ease;
}
.clp-add-btn:hover { background: var(--dp-surface-container-high); }

/* ── Simple divided lists (Upcoming This Week) ─────────────────────────── */
.clp-simple-list { display: flex; flex-direction: column; }
.clp-simple-row {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 9px 6px; margin: 0 -6px; border-bottom: 1px solid var(--card-border);
    cursor: pointer; border-radius: 8px; transition: background 0.15s ease;
}
.clp-simple-row:last-child { border-bottom: none; }
.clp-simple-row:hover { background: var(--dp-surface-container-low); }
.clp-simple-row__main { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.clp-simple-row__title { font-size: 0.8125rem; font-weight: 700; color: var(--dp-on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.clp-simple-row__sub { font-size: 0.6875rem; color: var(--dp-on-surface-variant); }
.clp-simple-row__side { flex-shrink: 0; text-align: right; }
.clp-simple-row__date { font-size: 0.75rem; font-weight: 600; color: var(--dp-on-surface-variant); white-space: nowrap; }

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
    border-color: var(--dp-outline);
    color: var(--dp-on-surface);
    background: var(--dp-surface-container-high);
}

.clp-mini-btn:focus-visible {
    outline: 2px solid var(--dp-primary);
    outline-offset: 2px;
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
    padding: 0;
    border-radius: 50%;
    border: 1.5px solid var(--dp-outline-variant);
    background: transparent;
    color: var(--dp-on-primary);
    flex-shrink: 0;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}

.clp-task-check:disabled { opacity: 0.6; cursor: default; }
.clp-task-check:focus-visible { outline: 2px solid var(--dp-primary); outline-offset: 2px; }

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

.clp-icon-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--dp-outline-variant);
    background: var(--dp-surface-container-lowest);
    color: var(--dp-on-surface-variant);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    flex-shrink: 0;
    transition: background 0.15s ease;
}

.clp-icon-btn:hover {
    background: var(--dp-surface-container-low);
}

.clp-icon-btn:focus-visible {
    outline: 2px solid var(--dp-primary);
    outline-offset: 2px;
}

.clp-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--dp-outline);
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

.clp-badge--green {
    background: var(--dp-secondary-container);
    color: var(--dp-on-secondary-container);
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
    min-height: 112px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 12px 14px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.clp-cell:hover {
    background: var(--dp-surface-container-low);
}

.clp-cell--selected {
    background: var(--dp-surface-container-low);
    box-shadow: inset 0 0 0 1.5px var(--dp-outline-variant);
}

.clp-cell--other {
    opacity: 0.4;
}

.clp-cell--today {
    box-shadow: inset 0 0 0 1.5px var(--dp-outline-variant);
    background: var(--dp-surface-container-low);
}

.clp-cell--today.clp-cell--selected {
    box-shadow: inset 0 0 0 1.5px var(--dp-outline-variant);
}

.clp-cell__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 6px;
}

.clp-cell__today-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--dp-on-surface);
    background: var(--dp-surface-container-high);
}

.clp-cell__num {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--dp-on-surface);
}

.clp-cell--selected .clp-cell__num {
    color: var(--dp-on-surface);
    font-weight: 800;
}

.clp-cell__chips {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 8px;
    width: 100%;
}

.clp-cell-chip {
    display: flex;
    align-items: center;
    gap: 5px;
    width: 100%;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 3px 7px;
    border-radius: 6px;
    text-align: left;
    border: 1px solid transparent;
    cursor: pointer;
    font-family: inherit;
    transition: filter 0.12s ease;
}

.clp-cell-chip:hover { filter: brightness(0.97); }

.clp-cell-chip__dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.clp-cell-chip__label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.clp-chip--task { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); border-color: color-mix(in srgb, var(--dp-secondary) 30%, transparent); }
.clp-chip--task .clp-cell-chip__dot { background: var(--dp-secondary); }
.clp-chip--deadline { background: var(--dp-error-container); color: var(--dp-on-error-container); border-color: color-mix(in srgb, var(--dp-error) 30%, transparent); }
.clp-chip--deadline .clp-cell-chip__dot { background: var(--dp-error); }
.clp-chip--harvest { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.clp-chip--harvest .clp-cell-chip__dot { background: #d97706; }
.clp-chip--market { background: #dbeafe; color: #1e40af; border-color: #bfdbfe; }
.clp-chip--market .clp-cell-chip__dot { background: #2563eb; }
.clp-chip--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); border-color: var(--dp-outline-variant); }
.clp-chip--muted .clp-cell-chip__dot { background: var(--dp-outline); }

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
    padding: 16px 18px;
    border-bottom: 0.5px solid var(--clp-hairline);
}

.clp-main-card :deep(.el-calendar__title) {
    font-size: 0.9375rem;
    font-weight: 800;
    color: var(--dp-on-surface);
}

.clp-main-card :deep(.el-calendar__button-group .el-button) {
    border-radius: 8px !important;
    font-size: 0.75rem;
    font-weight: 600;
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


/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .clp-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .clp-toolbar { flex-direction: column; align-items: stretch; }
    .clp-page-header__row { flex-direction: column; align-items: stretch; }
    .clp-page-header__actions { justify-content: space-between; }
}

@media (max-width: 575.98px) {
    .clp-title { font-size: 1.25rem; line-height: 1.6rem; }
}

/* ── Reduced motion ────────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .clp-cell,
    .clp-task-row,
    .clp-simple-row,
    .clp-inspector__item,
    .clp-icon-btn,
    .clp-mini-btn,
    .clp-btn-primary,
    .clp-btn-outline {
        transition: none;
    }
}
</style>
