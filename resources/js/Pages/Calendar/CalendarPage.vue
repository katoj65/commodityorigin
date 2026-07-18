<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Plus, Delete, Edit, WarnTriangleFilled, Clock } from '@element-plus/icons-vue';

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

const selectedDayEvents = computed(() => eventsByDay.value[selectedDay.value] ?? []);

const dueToday = computed(() => (eventsByDay.value[todayStr()] ?? []).filter((e) => e.status === 'pending'));

function selectDay(day) {
    selectedDay.value = day;
}

function jumpToDay(day) {
    selectedDay.value = day;
    activeDate.value = new Date(`${day}T00:00:00`);
}

const sortedEvents = computed(() => [...props.events].sort((a, b) => a.event_date.localeCompare(b.event_date)));

const upcomingEvents = computed(() => sortedEvents.value
    .filter((e) => e.event_date >= todayStr())
    .slice(0, 6));

const previousEvents = computed(() => [...sortedEvents.value]
    .filter((e) => e.event_date < todayStr())
    .reverse()
    .slice(0, 6));

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
const saving = ref(false);
const form = reactive({
    title: '',
    description: '',
    event_date: todayStr(),
    type: '',
    status: 'pending',
});

function openCreateDialog() {
    editingId.value = null;
    form.title = '';
    form.description = '';
    form.event_date = selectedDay.value;
    form.type = '';
    form.status = 'pending';
    dialogOpen.value = true;
}

function openEditDialog(event) {
    editingId.value = event.id;
    form.title = event.title;
    form.description = event.description ?? '';
    form.event_date = event.event_date;
    form.type = event.type ?? '';
    form.status = event.status;
    dialogOpen.value = true;
}

function saveEvent() {
    if (!form.title.trim() || !form.event_date) {
        ElMessage.warning('Title and date are required.');
        return;
    }

    saving.value = true;
    const payload = { ...form };
    const onFinish = () => { saving.value = false; };
    const onSuccess = () => { dialogOpen.value = false; };

    if (editingId.value) {
        router.patch(route('calendar.update', editingId.value), payload, { preserveScroll: true, onSuccess, onFinish });
    } else {
        router.post(route('calendar.store'), payload, { preserveScroll: true, onSuccess, onFinish });
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

const typeLabel = (type) => ({
    task: 'Task',
    deadline: 'Deadline',
    harvest: 'Harvest',
    market: 'Market',
}[type] ?? 'Event');
</script>

<template>
    <AppLayout title="Calendar" full-width flush :show-banner="false">
        <Head title="Calendar" />

        <div class="clp-page">
            <div class="clp-header">
                <div>
                    <div class="clp-kicker">Workspace</div>
                    <h1 class="clp-title">Calendar</h1>
                </div>
                <button type="button" class="clp-btn-primary" @click="openCreateDialog">
                    <el-icon><Plus /></el-icon> New Event
                </button>
            </div>

            <div v-if="dueToday.length" class="clp-due-banner">
                <el-icon><WarnTriangleFilled /></el-icon>
                <span>{{ dueToday.length }} event{{ dueToday.length > 1 ? 's' : '' }} due today: {{ dueToday.map((e) => e.title).join(', ') }}</span>
            </div>

            <div class="clp-body">
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

                <div class="clp-sidebar">
                    <div class="clp-sidebar-card">
                        <div class="clp-sidebar-title">{{ selectedDay }}</div>

                        <div v-if="!selectedDayEvents.length" class="clp-empty">No events on this day.</div>

                        <div v-for="ev in selectedDayEvents" :key="ev.id" class="clp-event-row">
                            <div class="clp-dot" :class="typeTone(ev.type)"></div>
                            <div class="clp-event-row__body">
                                <div class="clp-event-row__title">{{ ev.title }}</div>
                                <p v-if="ev.description" class="clp-event-row__desc">{{ ev.description }}</p>
                                <div class="clp-event-row__meta">
                                    <span class="clp-badge">{{ typeLabel(ev.type) }}</span>
                                    <span class="clp-badge" :class="ev.status === 'completed' ? 'clp-badge--green' : 'clp-badge--amber'">{{ ev.status }}</span>
                                </div>
                            </div>
                            <div class="clp-event-row__actions">
                                <button type="button" class="clp-icon-btn" aria-label="Edit event" @click="openEditDialog(ev)">
                                    <el-icon><Edit /></el-icon>
                                </button>
                                <button type="button" class="clp-icon-btn clp-icon-btn--danger" aria-label="Delete event" @click="deleteEvent(ev)">
                                    <el-icon><Delete /></el-icon>
                                </button>
                            </div>
                        </div>

                        <button type="button" class="clp-btn-outline w-100 mt-3" @click="openCreateDialog">
                            <el-icon><Plus /></el-icon> Add Event for This Day
                        </button>
                    </div>
                </div>
            </div>

            <div class="clp-timeline-row">
                <div class="clp-timeline-card">
                    <div class="clp-timeline-title"><el-icon><Clock /></el-icon> Upcoming Events</div>
                    <div v-if="!upcomingEvents.length" class="clp-empty">No upcoming events.</div>
                    <button
                        v-for="ev in upcomingEvents"
                        :key="ev.id"
                        type="button"
                        class="clp-timeline-item"
                        @click="jumpToDay(ev.event_date)"
                    >
                        <div class="clp-timeline-date">
                            <span class="clp-timeline-date__month">{{ dateBadge(ev.event_date).month }}</span>
                            <span class="clp-timeline-date__day">{{ dateBadge(ev.event_date).day }}</span>
                        </div>
                        <div class="clp-timeline-item__body">
                            <div class="clp-timeline-item__title">{{ ev.title }}</div>
                            <div class="clp-timeline-item__meta">
                                <span class="clp-dot" :class="typeTone(ev.type)"></span>
                                <span>{{ typeLabel(ev.type) }}</span>
                                <span class="clp-timeline-item__relative">{{ relativeDayLabel(ev.event_date) }}</span>
                            </div>
                        </div>
                        <span class="clp-badge" :class="ev.status === 'completed' ? 'clp-badge--green' : 'clp-badge--amber'">{{ ev.status }}</span>
                    </button>
                </div>

                <div class="clp-timeline-card">
                    <div class="clp-timeline-title"><el-icon><Clock /></el-icon> Previous Events</div>
                    <div v-if="!previousEvents.length" class="clp-empty">No previous events.</div>
                    <button
                        v-for="ev in previousEvents"
                        :key="ev.id"
                        type="button"
                        class="clp-timeline-item clp-timeline-item--past"
                        @click="jumpToDay(ev.event_date)"
                    >
                        <div class="clp-timeline-date clp-timeline-date--past">
                            <span class="clp-timeline-date__month">{{ dateBadge(ev.event_date).month }}</span>
                            <span class="clp-timeline-date__day">{{ dateBadge(ev.event_date).day }}</span>
                        </div>
                        <div class="clp-timeline-item__body">
                            <div class="clp-timeline-item__title">{{ ev.title }}</div>
                            <div class="clp-timeline-item__meta">
                                <span class="clp-dot" :class="typeTone(ev.type)"></span>
                                <span>{{ typeLabel(ev.type) }}</span>
                                <span class="clp-timeline-item__relative">{{ relativeDayLabel(ev.event_date) }}</span>
                            </div>
                        </div>
                        <span class="clp-badge" :class="ev.status === 'completed' ? 'clp-badge--green' : 'clp-badge--amber'">{{ ev.status }}</span>
                    </button>
                </div>
            </div>
        </div>

        <el-dialog v-model="dialogOpen" :title="editingId ? 'Edit Event' : 'New Event'" width="420px" destroy-on-close align-center>
            <el-form label-position="top">
                <el-form-item label="Title">
                    <el-input v-model="form.title" placeholder="Event title" />
                </el-form-item>
                <el-form-item label="Date">
                    <el-date-picker v-model="form.event_date" type="date" value-format="YYYY-MM-DD" style="width:100%" />
                </el-form-item>
                <el-form-item label="Type">
                    <el-select v-model="form.type" placeholder="Select type" style="width:100%" clearable>
                        <el-option label="Task" value="task" />
                        <el-option label="Deadline" value="deadline" />
                        <el-option label="Harvest" value="harvest" />
                        <el-option label="Market" value="market" />
                    </el-select>
                </el-form-item>
                <el-form-item label="Status">
                    <el-select v-model="form.status" style="width:100%">
                        <el-option label="Pending" value="pending" />
                        <el-option label="Completed" value="completed" />
                    </el-select>
                </el-form-item>
                <el-form-item label="Description">
                    <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Optional notes" />
                </el-form-item>
            </el-form>
            <template #footer>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="clp-btn-outline" @click="dialogOpen = false">Cancel</button>
                    <button type="button" class="clp-btn-primary" :disabled="saving" @click="saveEvent">{{ saving ? 'Saving…' : 'Save Event' }}</button>
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
    padding: 1.25rem 1.5rem 3rem;
}

.clp-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.clp-kicker {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--green);
    margin-bottom: 2px;
}

.clp-title {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0;
}

.clp-due-banner {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.8125rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.clp-body {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.25rem;
    align-items: start;
}

.clp-calendar-card,
.clp-sidebar-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.clp-sidebar-card {
    padding: 1rem;
}

.clp-sidebar-title {
    font-size: 0.9375rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.clp-empty {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    padding: 1rem 0;
    text-align: center;
}

.clp-event-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid var(--surface-low);
}

.clp-event-row:last-of-type {
    border-bottom: none;
}

.clp-event-row__body {
    flex: 1;
    min-width: 0;
}

.clp-event-row__title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface);
}

.clp-event-row__desc {
    font-size: 0.75rem;
    color: var(--on-surface-var);
    margin: 2px 0 6px;
    line-height: 1.4;
}

.clp-event-row__meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.clp-event-row__actions {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex-shrink: 0;
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
    background: #004532;
    border: 1px solid #004532;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.clp-btn-primary:hover {
    background: #002e20;
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
}

.clp-btn-outline:hover {
    background: #f8fafc;
}

/* ── Calendar cell ────────────────────────────────────────────────────── */
.clp-cell {
    height: 100%;
    min-height: 64px;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 6px 0 4px;
    cursor: pointer;
    border-radius: 8px;
    transition: background 0.12s;
}

.clp-cell:hover {
    background: var(--surface-low);
}

.clp-cell--selected {
    background: rgba(0, 69, 50, 0.08);
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
    margin-top: 4px;
}

/* ── Element Plus calendar overrides ─────────────────────────────────── */
.clp-calendar-card :deep(.el-calendar) {
    --el-calendar-border: #e5e7eb;
}

.clp-calendar-card :deep(.el-calendar__header) {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}

.clp-calendar-card :deep(.el-calendar__title) {
    font-weight: 700;
    color: var(--on-surface);
}

.clp-calendar-card :deep(.el-calendar-table .el-calendar-day) {
    height: auto;
    padding: 2px;
}

.clp-calendar-card :deep(.el-calendar-table th) {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--on-surface-var);
}

/* ── Upcoming / Previous timeline ─────────────────────────────────────── */
.clp-timeline-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-top: 1.25rem;
}

.clp-timeline-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    padding: 1rem;
}

.clp-timeline-title {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--on-surface);
    margin-bottom: 0.75rem;
}

.clp-timeline-item {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    border-bottom: 1px solid var(--surface-low);
    padding: 10px 4px;
    cursor: pointer;
    border-radius: 8px;
    transition: background 0.12s;
}

.clp-timeline-item:hover {
    background: var(--surface-low);
}

.clp-timeline-item:last-of-type {
    border-bottom: none;
}

.clp-timeline-date {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 10px;
    background: rgba(0, 69, 50, 0.08);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    line-height: 1.1;
}

.clp-timeline-date--past {
    background: var(--surface-low);
}

.clp-timeline-date__month {
    font-size: 0.5625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--green);
}

.clp-timeline-date--past .clp-timeline-date__month {
    color: var(--on-surface-var);
}

.clp-timeline-date__day {
    font-size: 1rem;
    font-weight: 800;
    color: var(--on-surface);
}

.clp-timeline-item__body {
    flex: 1;
    min-width: 0;
}

.clp-timeline-item__title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.clp-timeline-item--past .clp-timeline-item__title {
    color: var(--on-surface-var);
}

.clp-timeline-item__meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.6875rem;
    color: var(--on-surface-var);
    margin-top: 2px;
}

.clp-timeline-item__meta .clp-dot {
    margin-top: 0;
}

.clp-timeline-item__relative {
    margin-left: 2px;
    padding-left: 6px;
    border-left: 1px solid var(--border);
}

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .clp-body {
        grid-template-columns: 1fr;
    }

    .clp-timeline-row {
        grid-template-columns: 1fr;
    }
}
</style>
