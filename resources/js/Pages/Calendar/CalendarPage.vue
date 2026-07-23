<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Delete, Edit, WarnTriangleFilled, Clock, Close, Calendar as CalendarIcon, Files, CircleCheck, Loading, Star, PriceTag, Sunny, TrendCharts, List, MoreFilled } from '@element-plus/icons-vue';

const props = defineProps({
    events: { type: Array, default: () => [] },
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

const upcomingEvents = computed(() => sortedEvents.value.filter((e) => e.event_date >= todayStr()));

const previousEvents = computed(() => [...sortedEvents.value]
    .filter((e) => e.event_date < todayStr())
    .reverse());

/* ── Past events tabs ────────────────────────────────────────────────── */
const pastFilter = ref('all');
const pastTabs = [
    { name: 'all', label: 'All' },
    { name: 'task', label: 'Task' },
    { name: 'deadline', label: 'Deadline' },
    { name: 'harvest', label: 'Harvest' },
    { name: 'market', label: 'Market' },
];

const filteredPastEvents = computed(() => (pastFilter.value === 'all'
    ? previousEvents.value
    : previousEvents.value.filter((e) => e.type === pastFilter.value)));

function relativeDayLabel(day) {
    const diff = Math.round((new Date(`${day}T00:00:00`) - new Date(`${todayStr()}T00:00:00`)) / 86400000);
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Tomorrow';
    if (diff === -1) return 'Yesterday';
    return diff > 0 ? `In ${diff} days` : `${Math.abs(diff)} days ago`;
}

function dateBadge(day) {
    const d = new Date(`${day}T00:00:00`);
    return {
        month: d.toLocaleDateString('en-US', { month: 'short' }),
        day: d.getDate(),
    };
}

/* ── Create / edit dialog ─────────────────────────────────────────────── */
const dialogOpen = ref(false);
const editingId = ref(null);
const form = useForm({
    title: '',
    description: '',
    event_date: todayStr(),
    type: '',
    status: 'pending',
    make_task: false,
});

const knownTypeValues = ['', 'task', 'deadline', 'harvest', 'market'];
const otherTypeMode = ref(false);

function selectType(value) {
    otherTypeMode.value = false;
    form.type = value;
}

function selectOtherType() {
    otherTypeMode.value = true;
    if (knownTypeValues.includes(form.type)) {
        form.type = '';
    }
}

function openCreateDialog() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.event_date = selectedDay.value;
    otherTypeMode.value = false;
    dialogOpen.value = true;
}

function openEditDialog(event) {
    editingId.value = event.id;
    form.clearErrors();
    form.title = event.title;
    form.description = event.description ?? '';
    form.event_date = event.event_date;
    form.type = event.type ?? '';
    form.status = event.status;
    form.make_task = false;
    otherTypeMode.value = form.type !== '' && !knownTypeValues.includes(form.type);
    dialogOpen.value = true;
}

function saveEvent() {
    form.clearErrors();

    if (!form.title.trim()) form.setError('title', 'Title is required.');
    if (!form.event_date) form.setError('event_date', 'Date is required.');
    if (form.errors.title || form.errors.event_date) return;

    const onSuccess = () => { dialogOpen.value = false; };

    if (editingId.value) {
        form.patch(route('calendar.update', editingId.value), { preserveScroll: true, onSuccess });
    } else {
        form.post(route('calendar.store'), { preserveScroll: true, onSuccess });
    }
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

const typeLabel = (type) => {
    if (!type) return 'Event';
    const known = { task: 'Task', deadline: 'Deadline', harvest: 'Harvest', market: 'Market' };
    return known[type] ?? (type.charAt(0).toUpperCase() + type.slice(1));
};

/* ── Dialog type picker ───────────────────────────────────────────────── */
const typeOptions = [
    { value: '', label: 'General', icon: Star, tone: 'muted' },
    { value: 'task', label: 'Task', icon: CircleCheck, tone: 'green' },
    { value: 'deadline', label: 'Deadline', icon: PriceTag, tone: 'red' },
    { value: 'harvest', label: 'Harvest', icon: Sunny, tone: 'amber' },
    { value: 'market', label: 'Market', icon: TrendCharts, tone: 'blue' },
];
</script>

<template>
    <AppLayout title="Calendar" full-width flush :show-banner="false">
        <Head title="Calendar" />

        <div class="clp-page">

            <!-- ── Page Header ───────────────────────────────────────────── -->
            <div class="clp-page-header">
                <div class="clp-page-header__left">

                    <h1 class="clp-title">Calendar Activities</h1>
                    <p class="clp-subtitle">Plan events, track deadlines, and stay ahead of every decision across your trading operations.</p>
                </div>
                <div class="clp-page-header__actions">
                    <Link :href="route('task.index')" class="clp-btn-outline">
                        <el-icon><List /></el-icon> View Tasks
                    </Link>
                    <button type="button" class="clp-btn-primary" @click="openCreateDialog">
                        <el-icon><Plus /></el-icon> New Event
                    </button>
                </div>
            </div>

            <!-- ── Overview Strip ────────────────────────────────────────── -->
            <div class="clp-kpi-strip">
                <div class="clp-kpi">
                    <span class="clp-kpi__label">Total Events</span>
                    <strong class="clp-kpi__val">{{ sortedEvents.length }}</strong>
                </div>
                <div class="clp-kpi">
                    <span class="clp-kpi__label">Due Today</span>
                    <strong class="clp-kpi__val" :class="dueToday.length ? 'clp-text-amber' : ''">{{ dueToday.length }}</strong>
                </div>
                <div class="clp-kpi">
                    <span class="clp-kpi__label">This Week</span>
                    <strong class="clp-kpi__val">{{ thisWeekCount }}</strong>
                </div>
                <div class="clp-kpi">
                    <span class="clp-kpi__label">Overdue</span>
                    <strong class="clp-kpi__val" :class="overdueCount ? 'clp-text-red' : ''">{{ overdueCount }}</strong>
                </div>
                <div class="clp-kpi">
                    <span class="clp-kpi__label">Completed</span>
                    <strong class="clp-kpi__val clp-text-green">{{ completedCount }}</strong>
                </div>
            </div>

            <div class="clp-calendar-card">
                <el-calendar v-model="activeDate">
                    <template #date-cell="{ data }">
                        <div
                            class="clp-cell"
                            :class="{ 'clp-cell--selected': data.day === selectedDay, 'clp-cell--other': data.type !== 'current-month' }"
                            @click="selectDay(data.day)"
                        >
                            <span class="clp-cell__num">{{ data.date.getDate() }}</span>
                            <div v-if="eventsByDay[data.day]?.length" class="clp-cell__dots">
                                <span
                                    v-for="ev in eventsByDay[data.day].slice(0, 3)"
                                    :key="ev.id"
                                    class="clp-dot"
                                    :class="typeTone(ev.type)"
                                ></span>
                            </div>
                        </div>
                    </template>
                </el-calendar>
            </div>

            <div class="clp-body">

                <div v-if="dueToday.length" class="clp-due-banner">
                    <el-icon><WarnTriangleFilled /></el-icon>
                    <span>{{ dueToday.length }} event{{ dueToday.length > 1 ? 's' : '' }} due today: {{ dueToday.map((e) => e.title).join(', ') }}</span>
                </div>

                <div class="clp-columns">
                    <div class="clp-panel clp-panel--upcoming">
                        <div class="clp-panel-head">
                            <div class="clp-panel-title"><el-icon><Clock /></el-icon> Current &amp; Upcoming Events</div>
                            <span class="clp-count-badge">{{ upcomingEvents.length }}</span>
                        </div>

                        <div v-if="!upcomingEvents.length" class="clp-empty">No current or upcoming events.</div>

                        <div v-else class="clp-list">
                            <div v-for="ev in upcomingEvents" :key="ev.id" class="clp-row">
                                <button type="button" class="clp-row__date" @click="jumpToDay(ev.event_date)">
                                    <span class="clp-row__date-month">{{ dateBadge(ev.event_date).month }}</span>
                                    <span class="clp-row__date-day">{{ dateBadge(ev.event_date).day }}</span>
                                </button>
                                <div class="clp-row__body">
                                    <div class="clp-row__title">{{ ev.title }}</div>
                                    <div class="clp-row__meta">
                                        <span class="clp-dot" :class="typeTone(ev.type)"></span>
                                        <span>{{ typeLabel(ev.type) }}</span>
                                        <span class="clp-row__relative">{{ relativeDayLabel(ev.event_date) }}</span>
                                    </div>
                                </div>
                                <span class="clp-badge" :class="ev.status === 'completed' ? 'clp-badge--green' : 'clp-badge--amber'">{{ ev.status }}</span>
                                <div class="clp-row__actions">
                                    <button type="button" class="clp-icon-btn" aria-label="Edit event" @click="openEditDialog(ev)">
                                        <el-icon><Edit /></el-icon>
                                    </button>
                                    <button type="button" class="clp-icon-btn clp-icon-btn--danger" aria-label="Delete event" @click="deleteEvent(ev)">
                                        <el-icon><Delete /></el-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="clp-panel clp-panel--past">
                        <div class="clp-panel-head">
                            <div class="clp-panel-title"><el-icon><Clock /></el-icon> Past Events</div>
                            <span class="clp-count-badge">{{ previousEvents.length }}</span>
                        </div>

                        <el-tabs v-model="pastFilter" class="clp-past-tabs">
                            <el-tab-pane v-for="tab in pastTabs" :key="tab.name" :label="tab.label" :name="tab.name">
                                <div v-if="!filteredPastEvents.length" class="clp-empty">No past events.</div>

                                <div v-else class="clp-list clp-list--compact">
                                    <button
                                        v-for="ev in filteredPastEvents"
                                        :key="ev.id"
                                        type="button"
                                        class="clp-row clp-row--compact"
                                        @click="openEditDialog(ev)"
                                    >
                                        <span class="clp-dot" :class="typeTone(ev.type)"></span>
                                        <div class="clp-row__body">
                                            <div class="clp-row__title">{{ ev.title }}</div>
                                            <div class="clp-row__relative">{{ relativeDayLabel(ev.event_date) }}</div>
                                        </div>
                                    </button>
                                </div>
                            </el-tab-pane>
                        </el-tabs>
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
            class="clp-modal"
        >
            <template #header>
                <div class="clp-modal__head">
                    <div class="clp-modal__head-icon">
                        <el-icon :size="18"><CalendarIcon /></el-icon>
                    </div>
                    <div class="clp-modal__head-text">
                        <div class="clp-modal__eyebrow">{{ editingId ? 'Edit' : 'Create' }}</div>
                        <div class="clp-modal__title">{{ editingId ? 'Edit Event' : 'New Event' }}</div>
                    </div>
                    <button type="button" class="clp-modal__close" aria-label="Close" @click="dialogOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="clp-modal__body">
                <div class="clp-field">
                    <label class="clp-field__label">Title</label>
                    <el-input v-model="form.title" placeholder="e.g. Export deadline for Lot #42" class="clp-input" :class="{ 'clp-input--error': form.errors.title }" />
                    <span v-if="form.errors.title" class="clp-field__error">{{ form.errors.title }}</span>
                </div>

                <div class="clp-field">
                    <label class="clp-field__label"><el-icon :size="12"><Files /></el-icon> Description</label>
                    <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Optional notes" class="clp-input" />
                </div>

                <div class="clp-field">
                    <label class="clp-field__label">Date</label>
                    <el-date-picker v-model="form.event_date" type="date" value-format="YYYY-MM-DD" style="width:100%" class="clp-input" :class="{ 'clp-input--error': form.errors.event_date }" />
                    <span v-if="form.errors.event_date" class="clp-field__error">{{ form.errors.event_date }}</span>
                </div>

                <div class="clp-field mt-3">
                    <label class="clp-field__label">Type</label>
                    <div class="clp-type-grid">
                        <button
                            v-for="opt in typeOptions"
                            :key="opt.value || 'general'"
                            type="button"
                            class="clp-type-pill"
                            :class="[`clp-type-pill--${opt.tone}`, { 'clp-type-pill--active': !otherTypeMode && form.type === opt.value }]"
                            @click="selectType(opt.value)"
                        >
                            <el-icon :size="15"><component :is="opt.icon" /></el-icon>
                            <span>{{ opt.label }}</span>
                        </button>
                        <button
                            type="button"
                            class="clp-type-pill clp-type-pill--muted"
                            :class="{ 'clp-type-pill--active': otherTypeMode }"
                            @click="selectOtherType"
                        >
                            <el-icon :size="15"><MoreFilled /></el-icon>
                            <span>Other</span>
                        </button>
                    </div>

                    <el-input
                        v-if="otherTypeMode"
                        v-model="form.type"
                        placeholder="e.g. Compliance, Inspection, Shipping"
                        class="clp-input mt-2"
                        maxlength="255"
                    />
                </div>

                <div v-if="editingId" class="clp-field">
                    <label class="clp-field__label">Status</label>
                    <div class="clp-status-toggle">
                        <button
                            type="button"
                            class="clp-status-toggle__btn"
                            :class="{ 'clp-status-toggle__btn--active': form.status === 'pending' }"
                            @click="form.status = 'pending'"
                        >
                            <el-icon :size="13"><Loading /></el-icon> Pending
                        </button>
                        <button
                            type="button"
                            class="clp-status-toggle__btn clp-status-toggle__btn--green"
                            :class="{ 'clp-status-toggle__btn--active': form.status === 'completed' }"
                            @click="form.status = 'completed'"
                        >
                            <el-icon :size="13"><CircleCheck /></el-icon> Completed
                        </button>
                    </div>
                </div>

                <div v-if="!editingId" class="clp-field clp-field--task">
                    <div class="clp-switch-row">
                        <div class="clp-switch-row__text">
                            <label class="clp-field__label"><el-icon :size="12"><CircleCheck /></el-icon> Task Reminder</label>
                            <span class="clp-field__hint">Also add this to your tasks. You'll get a decision-support notification when it's due.</span>
                        </div>
                        <el-switch v-model="form.make_task" class="clp-switch" />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="clp-modal__footer">
                    <button type="button" class="clp-btn-outline" @click="dialogOpen = false">Cancel</button>
                    <button type="button" class="clp-btn-primary" :disabled="form.processing" @click="saveEvent">
                        <el-icon v-if="!form.processing"><Plus /></el-icon>
                        {{ form.processing ? 'Saving…' : editingId ? 'Save Changes' : 'Create Event' }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Delete Event"
            :message="pendingDelete ? `Delete “${pendingDelete.title}”? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDeleteEvent"
        />
    </AppLayout>
</template>

<style scoped>
.clp-page {
    --green: #004532;
    --green-dark: #002e20;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.clp-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.75rem 1.5rem 0;
}

.clp-page-header__left {
    max-width: 560px;
}

.clp-kicker {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 4px;
}

.clp-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.clp-subtitle {
    font-size: 0.875rem;
    color: var(--on-surface-var);
    margin: 0;
    line-height: 1.6;
}

.clp-page-header__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 4px;
}

/* ── Overview strip ──────────────────────────────────────────────────── */
.clp-kpi-strip {
    display: flex;
    gap: 0;
    overflow-x: auto;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-top: 1.5rem;
    scrollbar-width: none;
}

.clp-kpi-strip::-webkit-scrollbar {
    display: none;
}

.clp-kpi {
    flex: 1;
    min-width: 130px;
    padding: 1rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
    border-right: 1px solid var(--border);
}

.clp-kpi:last-child {
    border-right: none;
}

.clp-kpi__label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
}

.clp-kpi__val {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--on-surface);
    letter-spacing: -0.01em;
}

.clp-text-green { color: #166534; }
.clp-text-amber { color: #92400e; }
.clp-text-red   { color: #b91c1c; }

/* ── Body ────────────────────────────────────────────────────────────── */
.clp-body {
    padding: 1.25rem 1.5rem 3rem;
}

.clp-due-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border: 1px solid var(--border);
    border-left: 3px solid #d97706;
    color: #92400e;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.8125rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.clp-calendar-card {
    background: #fff;
    border: none;
    border-radius: 0;
    overflow: hidden;
    width: 100%;
    margin-top: 1.5rem;
}

.clp-empty {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    padding: 1rem 0;
    text-align: center;
}

/* ── Columns below calendar (80 / 20) ───────────────────────────────────── */
.clp-columns {
    display: grid;
    grid-template-columns: 4fr 1fr;
    gap: 1.25rem;
    align-items: start;
    margin-top: 1.25rem;
}

.clp-panel {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
}

.clp-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 0.875rem 1rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface-low);
}

.clp-panel--past .clp-panel-head {
    background: #fff;
    border-bottom: none;
}

.clp-panel--upcoming .clp-list {
    padding: 0.25rem 1rem 0.5rem;
}

.clp-panel--past .clp-past-tabs {
    padding: 0.5rem 1rem 1rem;
}

.clp-panel--upcoming .clp-empty {
    padding: 1rem;
}

.clp-panel-title {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.875rem;
    font-weight: 800;
    color: var(--on-surface);
}

.clp-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 20px;
    padding: 0 7px;
    border-radius: 999px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
    font-size: 0.6875rem;
    font-weight: 800;
}

.clp-list {
    display: flex;
    flex-direction: column;
    max-height: 520px;
    overflow-y: auto;
}

.clp-row {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    border-bottom: 1px solid var(--surface-low);
    padding: 10px 4px;
}

.clp-row:last-of-type {
    border-bottom: none;
}

.clp-row__date {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 10px;
    background: rgba(0, 69, 50, 0.08);
    border: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    line-height: 1.1;
    cursor: pointer;
}

.clp-row__date-month {
    font-size: 0.5625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--green);
}

.clp-row__date-day {
    font-size: 1rem;
    font-weight: 800;
    color: var(--on-surface);
}

.clp-row__body {
    flex: 1;
    min-width: 0;
}

.clp-row__title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.clp-row__meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.6875rem;
    color: var(--on-surface-var);
    margin-top: 2px;
}

.clp-row__meta .clp-dot {
    margin-top: 0;
}

.clp-row__relative {
    margin-left: 2px;
    padding-left: 6px;
    border-left: 1px solid var(--border);
}

.clp-row__actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

/* ── Past events panel (compact rows inside tabs) ───────────────────────── */
.clp-list--compact {
    max-height: 440px;
}

.clp-row--compact {
    gap: 8px;
    padding: 8px 4px;
    cursor: pointer;
    border-radius: 8px;
}

.clp-row--compact:hover {
    background: var(--surface-low);
}

.clp-row--compact .clp-dot {
    margin-top: 5px;
}

.clp-row--compact .clp-row__relative {
    margin-left: 0;
    padding-left: 0;
    border-left: none;
    display: block;
    margin-top: 1px;
    font-size: 0.6875rem;
    color: var(--on-surface-var);
}

.clp-past-tabs :deep(.el-tabs__header) {
    margin-bottom: 8px;
}

.clp-past-tabs :deep(.el-tabs__nav-wrap::after) {
    background-color: var(--border);
}

.clp-past-tabs :deep(.el-tabs__item) {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--on-surface-var);
    padding: 0 10px;
    height: 34px;
}

.clp-past-tabs :deep(.el-tabs__item.is-active) {
    color: var(--green);
}

.clp-past-tabs :deep(.el-tabs__active-bar) {
    background-color: var(--green);
}

.clp-icon-btn {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: #fff;
    color: var(--on-surface-var);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
}

.clp-icon-btn:hover {
    background: var(--surface-low);
}

.clp-icon-btn--danger:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #991b1b;
}

.clp-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--green);
    flex-shrink: 0;
    margin-top: 4px;
}

.clp-dot--green { background: #16a34a; }
.clp-dot--red { background: #dc2626; }
.clp-dot--amber { background: #d97706; }
.clp-dot--blue { background: #2563eb; }
.clp-dot--muted { background: #9ca3af; }

.clp-badge {
    display: inline-flex;
    border-radius: 999px;
    font-size: 0.625rem;
    font-weight: 700;
    padding: 2px 8px;
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.clp-badge--green {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.clp-badge--amber {
    background: #fef3c7;
    color: #92400e;
    border-color: #fcd34d;
}

.clp-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.15s ease;
}

.clp-btn-primary:hover {
    opacity: 0.9;
}

.clp-btn-primary:disabled {
    opacity: 0.6;
    cursor: default;
}

.clp-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease;
}

.clp-btn-outline:hover {
    background: #f8fafc;
}

/* ── Calendar cell ────────────────────────────────────────────────────── */
.clp-cell {
    height: 100%;
    min-height: 88px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 10px 12px;
    cursor: pointer;
    transition: background 0.12s;
}

.clp-cell:hover {
    background: var(--surface-low);
}

.clp-cell--selected {
    background: rgba(0, 69, 50, 0.06);
    box-shadow: inset 0 0 0 1.5px var(--green);
}

.clp-cell--other {
    opacity: 0.4;
}

.clp-cell__num {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface);
}

.clp-cell--selected .clp-cell__num {
    color: var(--green);
    font-weight: 800;
}

.clp-cell__dots {
    display: flex;
    gap: 3px;
    margin-top: 6px;
}

/* ── Element Plus calendar overrides ────────────────────────────────────
   Native Element Plus grid: bordered day cells, like a spreadsheet. The
   calendar's own border shorthand must be a full `width style color`
   value — a bare color silently drops the border. */
.clp-calendar-card :deep(.el-calendar) {
    --el-calendar-border: 1px solid var(--border);
}

.clp-calendar-card :deep(.el-calendar__header) {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}

.clp-calendar-card :deep(.el-calendar__title) {
    font-weight: 700;
    color: var(--on-surface);
}

.clp-calendar-card :deep(.el-calendar__body) {
    padding: 0;
}

.clp-calendar-card :deep(.el-calendar-table) {
    border-collapse: collapse;
}

.clp-calendar-card :deep(.el-calendar-table .el-calendar-day) {
    height: auto;
    padding: 0;
}

.clp-calendar-card :deep(.el-calendar-table td) {
    border-color: var(--border);
}

.clp-calendar-card :deep(.el-calendar-table thead tr) {
    background: var(--surface-low);
}

.clp-calendar-card :deep(.el-calendar-table th) {
    padding: 13px 12px;
    font-size: 0.6875rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--on-surface-var);
    text-align: left;
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
}

.clp-calendar-card :deep(.el-calendar-table th:last-child) {
    border-right: none;
}

.clp-calendar-card :deep(.el-calendar-table th:first-child),
.clp-calendar-card :deep(.el-calendar-table th:last-child) {
    color: var(--green);
}

/* ── New/Edit Event modal ────────────────────────────────────────────────
   NOTE: <el-dialog> teleports its content to <body>, outside .clp-page's DOM
   subtree, so CSS custom properties (var(--green) etc.) defined on .clp-page
   do NOT cascade in. All colors below are literal hex values on purpose. */
:deep(.el-dialog.clp-modal) {
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

:deep(.el-dialog.clp-modal .el-dialog__header) {
    padding: 0;
    margin: 0;
}

:deep(.el-dialog.clp-modal .el-dialog__body) {
    padding: 0;
}

:deep(.el-dialog.clp-modal .el-dialog__footer) {
    padding: 0;
}

.clp-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.clp-modal__head-icon {
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

.clp-modal__head-text {
    flex: 1;
    min-width: 0;
}

.clp-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.clp-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.clp-modal__close {
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

.clp-modal__close:hover {
    background: #e5e7eb;
    color: #111827;
}

.clp-modal__body {
    padding: 22px 24px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 65vh;
    overflow-y: auto;
}

.clp-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.clp-field__label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9375rem;
    font-weight: 400;
    color: #374151;
}

.clp-field--task {
    margin-top: 4px;
    padding-top: 14px;
    border-top: 1px solid #f3f4f6;
}

.clp-field__hint {
    font-size: 0.75rem;
    font-weight: 400;
    color: #6b7280;
    line-height: 1.4;
}

.clp-field__error {
    font-size: 0.75rem;
    font-weight: 600;
    color: #dc2626;
    line-height: 1.4;
}

.clp-input--error :deep(.el-input__wrapper),
.clp-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #dc2626 inset !important;
}

/* ── Task-reminder switch ────────────────────────────────────────────── */
.clp-switch-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.clp-switch-row__text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.clp-switch-row .clp-field__label {
    font-weight: 600;
    color: #111827;
}

.clp-switch :deep(.el-switch__core) {
    background: #d1d5db;
    border-color: #d1d5db;
}

.clp-switch.is-checked :deep(.el-switch__core) {
    background: #004532;
    border-color: #004532;
}

.clp-input :deep(.el-input__wrapper),
.clp-input :deep(.el-textarea__inner) {
    border-radius: 10px;
    box-shadow: 0 0 0 1px #e5e7eb inset;
    background: #f9fafb;
    transition: box-shadow 0.12s, background 0.12s;
}

.clp-input :deep(.el-input__wrapper:hover),
.clp-input :deep(.el-textarea__inner:hover) {
    background: #fff;
    box-shadow: 0 0 0 1px #d1d5db inset;
}

.clp-input :deep(.el-input__wrapper.is-focus),
.clp-input :deep(.el-textarea__inner:focus) {
    background: #fff;
    box-shadow: 0 0 0 1.5px #004532 inset;
}

.clp-type-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.clp-type-pill {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 8px;
    border-radius: 10px;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s;
}

.clp-type-pill:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.clp-type-pill--active.clp-type-pill--muted { background: #f3f4f6; border-color: #9ca3af; color: #374151; }
.clp-type-pill--active.clp-type-pill--green { background: #dcfce7; border-color: #16a34a; color: #166534; }
.clp-type-pill--active.clp-type-pill--red { background: #fee2e2; border-color: #dc2626; color: #991b1b; }
.clp-type-pill--active.clp-type-pill--amber { background: #fef3c7; border-color: #d97706; color: #92400e; }
.clp-type-pill--active.clp-type-pill--blue { background: #dbeafe; border-color: #2563eb; color: #1e40af; }

.clp-status-toggle {
    display: flex;
    gap: 8px;
}

.clp-status-toggle__btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 12px;
    border-radius: 10px;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s;
}

.clp-status-toggle__btn:hover {
    background: #f9fafb;
}

.clp-status-toggle__btn--active {
    background: #fef3c7;
    border-color: #d97706;
    color: #92400e;
}

.clp-status-toggle__btn--green.clp-status-toggle__btn--active {
    background: #dcfce7;
    border-color: #16a34a;
    color: #166534;
}

.clp-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}

.clp-modal__footer .clp-btn-primary,
.clp-modal__footer .clp-btn-outline {
    padding: 9px 18px;
}

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .clp-columns {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .clp-page-header {
        padding: 1.25rem 1.25rem 0;
    }

    .clp-body {
        padding: 1.25rem 1.25rem 3rem;
    }
}
</style>
