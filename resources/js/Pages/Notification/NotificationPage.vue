<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, TrendCharts, Coin, User, CircleCheck, Refresh, Delete, View,
} from '@element-plus/icons-vue';

const props = defineProps({
    notifications: { type: Array, default: () => [] },
});

/* ── Filters (rendered as Element UI tabs) ──────────────────────────────── */
const activeFilter = ref('all');
const filters = [
    { key: 'all', label: 'All' },
    { key: 'unread', label: 'Unread' },
    { key: 'orders', label: 'Orders', icon: Box },
    { key: 'bids', label: 'Bids', icon: TrendCharts },
    { key: 'market', label: 'Market', icon: Coin },
    { key: 'account', label: 'Account', icon: User },
    { key: 'system', label: 'System', icon: Bell },
];

const sortedNotifications = computed(() => [...props.notifications].sort((a, b) => b.created_at.localeCompare(a.created_at)));

function matchesFilter(key, n) {
    switch (key) {
        case 'unread': return !n.is_read;
        case 'orders': return n.category === 'orders';
        case 'bids': return n.category === 'bids';
        case 'market': return n.category === 'market';
        case 'account': return n.category === 'account';
        case 'system': return n.category === 'system';
        default: return true;
    }
}

const filteredNotifications = computed(() => sortedNotifications.value.filter((n) => matchesFilter(activeFilter.value, n)));

function tabCount(key) {
    return sortedNotifications.value.filter((n) => matchesFilter(key, n)).length;
}

const unreadCount = computed(() => sortedNotifications.value.filter((n) => !n.is_read).length);

/* ── Selection (Gmail-style bulk actions) ───────────────────────────────── */
const selected = ref(new Set());

watch(activeFilter, () => { selected.value = new Set(); });

const allSelected = computed(() => filteredNotifications.value.length > 0
    && filteredNotifications.value.every((n) => selected.value.has(n.id)));
const someSelected = computed(() => selected.value.size > 0 && !allSelected.value);

function toggleSelectAll(checked) {
    selected.value = checked ? new Set(filteredNotifications.value.map((n) => n.id)) : new Set();
}

function toggleSelect(id, checked) {
    const next = new Set(selected.value);
    if (checked) next.add(id); else next.delete(id);
    selected.value = next;
}

/* ── Display helpers ─────────────────────────────────────────────────────── */
const categoryStyle = {
    orders: { icon: Box, chip: 'ntf-chip--green' },
    bids: { icon: TrendCharts, chip: 'ntf-chip--blue' },
    market: { icon: Coin, chip: 'ntf-chip--violet' },
    account: { icon: User, chip: 'ntf-chip--amber' },
    system: { icon: Bell, chip: 'ntf-chip--slate' },
};

function iconFor(n) {
    return categoryStyle[n.category]?.icon ?? Bell;
}

function chipClass(n) {
    return categoryStyle[n.category]?.chip ?? 'ntf-chip--slate';
}

function categoryLabel(category) {
    return category.charAt(0).toUpperCase() + category.slice(1);
}

function displayTime(dateTime) {
    const date = new Date(dateTime.replace(' ', 'T'));
    const now = new Date();

    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }

    const options = { month: 'short', day: 'numeric' };
    if (date.getFullYear() !== now.getFullYear()) options.year = 'numeric';

    return date.toLocaleDateString('en-US', options);
}

/* ── Actions ─────────────────────────────────────────────────────────────── */
function openNotification(n) {
    if (!n.is_read) {
        router.patch(route('notifications.read', n.id), {}, { preserveScroll: true, preserveState: true });
    }

    if (n.action_url) {
        router.visit(n.action_url);
    }
}

function markRead(n) {
    router.patch(route('notifications.read', n.id), {}, { preserveScroll: true, preserveState: true });
}

function markAllRead() {
    router.post(route('notifications.read-all'), {}, { preserveScroll: true });
}

function deleteNotification(n) {
    router.delete(route('notifications.destroy', n.id), { preserveScroll: true });
}

function refresh() {
    router.reload({ only: ['notifications'], preserveScroll: true });
}

function visit(url, method) {
    return new Promise((resolve) => {
        router.visit(url, {
            method, preserveScroll: true, preserveState: true, onFinish: resolve,
        });
    });
}

async function bulkMarkRead() {
    const ids = [...selected.value];
    for (const id of ids) {
        const n = props.notifications.find((x) => x.id === id);
        if (n && !n.is_read) await visit(route('notifications.read', id), 'patch');
    }
    selected.value = new Set();
}

async function bulkDelete() {
    const ids = [...selected.value];
    for (const id of ids) {
        await visit(route('notifications.destroy', id), 'delete');
    }
    selected.value = new Set();
}
</script>

<template>
    <AppLayout title="Notifications" full-width flush :show-banner="false">
        <Head title="Notifications" />

        <div class="ntf-page">
            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="ntf-header">
                <h1 class="ntf-title">
                    Notifications
                    <span v-if="unreadCount" class="ntf-unread-pill">{{ unreadCount }} new</span>
                </h1>
            </div>

            <!-- ── Toolbar ───────────────────────────────────────────────── -->
            <div class="ntf-toolbar">
                <div class="ntf-toolbar__left">
                    <el-checkbox
                        class="ntf-toolbar__check"
                        size="small"
                        :model-value="allSelected"
                        :indeterminate="someSelected"
                        @change="toggleSelectAll"
                    />
                    <button type="button" class="ntf-icon-btn" title="Refresh" @click="refresh">
                        <el-icon :size="16"><Refresh /></el-icon>
                    </button>

                    <span class="ntf-toolbar__divider" />

                    <template v-if="selected.size">
                        <button type="button" class="ntf-icon-btn" title="Mark as read" @click="bulkMarkRead">
                            <el-icon :size="16"><View /></el-icon>
                        </button>
                        <button type="button" class="ntf-icon-btn" title="Delete" @click="bulkDelete">
                            <el-icon :size="16"><Delete /></el-icon>
                        </button>
                        <span class="ntf-toolbar__selected-count">{{ selected.size }} selected</span>
                    </template>
                    <template v-else>
                        <button type="button" class="ntf-toolbar__text-btn" :disabled="!unreadCount" @click="markAllRead">
                            <el-icon :size="13"><CircleCheck /></el-icon> Mark all as read
                        </button>
                    </template>
                </div>

                <div class="ntf-toolbar__right">
                    {{ filteredNotifications.length }} {{ filteredNotifications.length === 1 ? 'notification' : 'notifications' }}
                </div>
            </div>

            <!-- ── Category tabs ─────────────────────────────────────────── -->
            <el-tabs v-model="activeFilter" class="ntf-tabs">
                <el-tab-pane v-for="f in filters" :key="f.key" :name="f.key">
                    <template #label>
                        <span class="ntf-tab-label">
                            <el-icon v-if="f.icon" :size="14"><component :is="f.icon" /></el-icon>
                            {{ f.label }}
                            <span v-if="tabCount(f.key)" class="ntf-tab-count">{{ tabCount(f.key) }}</span>
                        </span>
                    </template>
                </el-tab-pane>
            </el-tabs>

            <!-- ── List ──────────────────────────────────────────────────── -->
            <div class="ntf-feed">
                <div v-if="!filteredNotifications.length" class="ntf-empty">
                    <span class="ntf-empty-icon"><el-icon :size="20"><Bell /></el-icon></span>
                    <p>Nothing here yet.</p>
                </div>

                <div
                    v-for="n in filteredNotifications"
                    :key="n.id"
                    class="ntf-row"
                    :class="{ 'ntf-row--unread': !n.is_read, 'ntf-row--selected': selected.has(n.id) }"
                    tabindex="0"
                    role="button"
                    @click="openNotification(n)"
                    @keydown.enter.prevent="openNotification(n)"
                >
                    <span class="ntf-row__check" @click.stop>
                        <el-checkbox
                            size="small"
                            :model-value="selected.has(n.id)"
                            @change="(val) => toggleSelect(n.id, val)"
                        />
                    </span>

                    <span class="ntf-row__avatar" :class="chipClass(n)">
                        <el-icon :size="14"><component :is="iconFor(n)" /></el-icon>
                    </span>

                    <div class="ntf-row__main">
                        <span v-if="!n.is_read" class="ntf-row__dot" />
                        <span class="ntf-row__chip" :class="chipClass(n)">{{ categoryLabel(n.category) }}</span>
                        <span class="ntf-row__title">{{ n.title }}</span>
                        <span v-if="n.body" class="ntf-row__snippet">— {{ n.body }}</span>
                        <span
                            v-if="n.priority === 'critical' || n.priority === 'high'"
                            class="ntf-priority"
                            :class="`ntf-priority--${n.priority}`"
                        >{{ n.priority }}</span>
                    </div>

                    <div class="ntf-row__side">
                        <span class="ntf-row__time">{{ displayTime(n.created_at) }}</span>
                        <span class="ntf-row__actions">
                            <span
                                v-if="!n.is_read"
                                class="ntf-row__action"
                                role="button"
                                aria-label="Mark as read"
                                @click.stop="markRead(n)"
                            >
                                <el-icon :size="14"><View /></el-icon>
                            </span>
                            <span
                                class="ntf-row__action ntf-row__action--danger"
                                role="button"
                                aria-label="Delete notification"
                                @click.stop="deleteNotification(n)"
                            >
                                <el-icon :size="14"><Delete /></el-icon>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.ntf-page {
    --green: #004532;
    --border: #eef2f0;
    --on-surface: #0b0d0f;
    --on-surface-var: #6b7280;
    --surface-low: #f7f7f8;
    --surface-selected: rgba(0, 69, 50, 0.06);
    font-family: 'Manrope', system-ui, sans-serif;
    background: #ffffff;
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Header ──────────────────────────────────────────────────────────── */
.ntf-header {
    background: #ffffff;
    padding: 1.75rem 1.5rem 1.25rem;
    border-bottom: 1px solid var(--border);
}

.ntf-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.025em;
    margin: 0;
}

.ntf-unread-pill {
    display: inline-flex;
    align-items: center;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    padding: 3px 9px;
    border-radius: 999px;
    background: rgba(0, 69, 50, 0.08);
    color: var(--green);
}

/* ── Toolbar ─────────────────────────────────────────────────────────── */
.ntf-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.5rem 0.75rem;
}

.ntf-toolbar__left {
    display: flex;
    align-items: center;
    gap: 4px;
}

.ntf-toolbar__check {
    margin-right: 4px;
}

.ntf-toolbar__divider {
    width: 1px;
    height: 18px;
    background: var(--border);
    margin: 0 4px;
}

.ntf-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: none;
    background: none;
    color: var(--on-surface-var);
    cursor: pointer;
    transition: background 0.12s, color 0.12s;
}

.ntf-icon-btn:hover {
    background: var(--surface-low);
    color: var(--on-surface);
}

.ntf-toolbar__selected-count {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--on-surface-var);
    margin-left: 4px;
}

.ntf-toolbar__text-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: none;
    border: none;
    color: var(--green);
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 6px;
    transition: opacity 0.12s;
}

.ntf-toolbar__text-btn:hover:not(:disabled) {
    opacity: 0.7;
}

.ntf-toolbar__text-btn:disabled {
    color: #b8bcc4;
    cursor: default;
}

.ntf-toolbar__right {
    font-size: 0.75rem;
    color: var(--on-surface-var);
    flex-shrink: 0;
}

/* ── Tabs (Element UI) ───────────────────────────────────────────────── */
.ntf-tabs {
    padding: 0 1.5rem;
}

.ntf-tabs :deep(.el-tabs__header) {
    margin: 0;
}

.ntf-tabs :deep(.el-tabs__nav-wrap::after) {
    background-color: var(--border);
    height: 1px;
}

.ntf-tabs :deep(.el-tabs__item) {
    height: 42px;
    line-height: 42px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--on-surface-var);
    padding: 0 16px;
}

.ntf-tabs :deep(.el-tabs__item.is-active) {
    color: var(--on-surface);
    font-weight: 800;
}

.ntf-tabs :deep(.el-tabs__active-bar) {
    background-color: var(--green);
    height: 2.5px;
}

.ntf-tab-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.ntf-tab-count {
    font-size: 0.6875rem;
    font-weight: 800;
    opacity: 0.55;
}

/* ── Empty state ─────────────────────────────────────────────────────── */
.ntf-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-align: center;
    padding: 4.5rem 1rem;
}

.ntf-empty-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--surface-low);
    color: #b8bcc4;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.ntf-empty p {
    font-size: 0.8437rem;
    color: var(--on-surface-var);
    margin: 0;
}

/* ── Rows (Gmail-style flat list) ────────────────────────────────────── */
.ntf-feed {
    padding-bottom: 4rem;
}

.ntf-row {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    background: var(--surface-low);
    border-bottom: 1px solid #f0f0f1;
    padding: 9px 1.5rem;
    cursor: pointer;
    transition: background 0.1s, box-shadow 0.1s;
}

.ntf-row--unread {
    background: #ffffff;
}

.ntf-row--selected {
    background: var(--surface-selected);
}

.ntf-row:hover {
    background: #ffffff;
    box-shadow: inset 0 0 0 1px var(--border), 0 1px 4px rgba(0, 0, 0, 0.08);
    position: relative;
    z-index: 1;
}

.ntf-row:focus-visible {
    outline: 2px solid var(--green);
    outline-offset: -2px;
}

.ntf-row__check {
    display: flex;
    flex-shrink: 0;
}

.ntf-row__avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ntf-chip--green { background: rgba(0, 69, 50, 0.09); color: #004532; }
.ntf-chip--blue { background: rgba(29, 78, 216, 0.09); color: #1d4ed8; }
.ntf-chip--violet { background: rgba(124, 58, 237, 0.09); color: #7c3aed; }
.ntf-chip--amber { background: rgba(194, 65, 12, 0.09); color: #c2410c; }
.ntf-chip--slate { background: rgba(71, 85, 105, 0.09); color: #475569; }

.ntf-row__main {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ntf-row__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--green);
    flex-shrink: 0;
}

.ntf-row__chip {
    flex-shrink: 0;
    display: inline-block;
    font-size: 0.625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 2px 7px;
    border-radius: 5px;
}

.ntf-row__title {
    flex-shrink: 0;
    font-size: 0.8437rem;
    font-weight: 500;
    color: var(--on-surface-var);
}

.ntf-row--unread .ntf-row__title {
    font-weight: 800;
    color: var(--on-surface);
}

.ntf-row__snippet {
    flex: 1;
    min-width: 0;
    font-size: 0.8437rem;
    color: var(--on-surface-var);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ntf-row--unread .ntf-row__snippet {
    color: var(--on-surface);
}

.ntf-row__side {
    position: relative;
    width: 92px;
    height: 20px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.ntf-row__time {
    position: absolute;
    right: 0;
    font-size: 0.75rem;
    color: #9aa0a6;
    white-space: nowrap;
    transition: opacity 0.1s;
}

.ntf-row--unread .ntf-row__time {
    color: var(--on-surface);
    font-weight: 700;
}

.ntf-row__actions {
    position: absolute;
    right: 0;
    display: flex;
    align-items: center;
    gap: 2px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.1s;
}

.ntf-row:hover .ntf-row__time {
    opacity: 0;
}

.ntf-row:hover .ntf-row__actions {
    opacity: 1;
    pointer-events: auto;
}

.ntf-row__action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    color: #6b7280;
    flex-shrink: 0;
    transition: background 0.12s, color 0.12s;
}

.ntf-row__action:hover {
    background: var(--surface-low);
    color: var(--on-surface);
}

.ntf-row__action--danger:hover {
    background: #fee2e2;
    color: #991b1b;
}

/* ── Priority tag ────────────────────────────────────────────────────── */
.ntf-priority {
    display: inline-flex;
    flex-shrink: 0;
    border-radius: 999px;
    font-size: 0.5938rem;
    font-weight: 800;
    padding: 1px 7px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.ntf-priority--high { background: #fef3c7; color: #92400e; }
.ntf-priority--critical { background: #fee2e2; color: #991b1b; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .ntf-header { padding: 1.25rem 1.25rem 0; }
    .ntf-toolbar { padding: 0.75rem 1.25rem; }
    .ntf-tabs { padding: 0 1.25rem; }
    .ntf-row { padding: 9px 1.25rem; }
    .ntf-row__chip { display: none; }
    .ntf-row__side { width: 60px; }
}
</style>
