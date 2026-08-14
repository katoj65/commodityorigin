<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Calendar as CalendarIcon, FullScreen } from '@element-plus/icons-vue';

const props = defineProps({
    events: { type: Array, default: () => [] },
    title: { type: String, default: 'Calendar' },
    showHeader: { type: Boolean, default: true },
});

const activeDate = ref(new Date());

const eventsByDay = computed(() => {
    const map = {};
    for (const e of props.events) {
        (map[e.event_date] ??= []).push(e);
    }
    return map;
});

const typeTone = (type) => ({
    task: 'cal-dot--green',
    deadline: 'cal-dot--red',
    harvest: 'cal-dot--amber',
    market: 'cal-dot--blue',
}[type] ?? 'cal-dot--muted');

const eventTitles = (day) => (eventsByDay.value[day] ?? []).map((e) => e.title).join(', ');
</script>

<template>
    <div class="cal-widget">
        <div v-if="showHeader" class="cal-widget__head">
            <div class="cal-widget__title"><el-icon><CalendarIcon /></el-icon> {{ title }}</div>
            <Link :href="route('calendar.index')" class="cal-widget__link" aria-label="Open full calendar" title="Open full calendar">
                <el-icon :size="13"><FullScreen /></el-icon>
            </Link>
        </div>

        <el-calendar v-model="activeDate" class="cal-widget__calendar">
            <template #date-cell="{ data }">
                <div
                    class="cal-cell"
                    :class="{ 'cal-cell--other': data.type !== 'current-month' }"
                    :title="eventTitles(data.day)"
                >
                    <span class="cal-cell__num">{{ data.date.getDate() }}</span>
                    <div v-if="eventsByDay[data.day]?.length" class="cal-cell__dots">
                        <span
                            v-for="ev in eventsByDay[data.day].slice(0, 3)"
                            :key="ev.id"
                            class="cal-dot"
                            :class="typeTone(ev.type)"
                        ></span>
                    </div>
                </div>
            </template>
        </el-calendar>
    </div>
</template>

<style scoped>
.cal-widget {
    font-family: 'Manrope', system-ui, sans-serif;
}

.cal-widget__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.cal-widget__title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    font-weight: 700;
    color: #111827;
}

.cal-widget__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    color: #6b7280;
    text-decoration: none;
}

.cal-widget__link:hover {
    background: #f8fafc;
    color: #004532;
}

/* ── Cell ─────────────────────────────────────────────────────────────── */
.cal-cell {
    height: 100%;
    min-height: 44px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2px 0;
    border-radius: 6px;
}

.cal-cell--other {
    opacity: 0.4;
}

.cal-cell__num {
    font-size: 0.75rem;
    font-weight: 600;
    color: #111827;
}

.cal-cell__dots {
    display: flex;
    gap: 2px;
    margin-top: 3px;
}

.cal-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #004532;
    flex-shrink: 0;
}

.cal-dot--green { background: #16a34a; }
.cal-dot--red { background: #dc2626; }
.cal-dot--amber { background: #d97706; }
.cal-dot--blue { background: #2563eb; }
.cal-dot--muted { background: #9ca3af; }

/* ── Element Plus calendar overrides ─────────────────────────────────── */
.cal-widget__calendar :deep(.el-calendar__header) {
    padding: 8px 4px;
}

.cal-widget__calendar :deep(.el-calendar__title) {
    font-size: 0.8125rem;
    font-weight: 700;
    color: #111827;
}

.cal-widget__calendar :deep(.el-calendar__body) {
    padding: 4px;
}

.cal-widget__calendar :deep(.el-calendar-table .el-calendar-day) {
    height: auto;
    padding: 1px;
}

.cal-widget__calendar :deep(.el-calendar-table th) {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    padding: 4px 0;
}

.cal-widget__calendar :deep(.el-calendar-table td.is-selected .cal-cell) {
    background: rgba(0, 69, 50, 0.08);
    box-shadow: inset 0 0 0 1.5px #004532;
}

.cal-widget__calendar :deep(.el-calendar-table td.is-selected .cal-cell__num) {
    color: #004532;
    font-weight: 800;
}
</style>
