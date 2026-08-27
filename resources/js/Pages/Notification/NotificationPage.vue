<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
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
    <DesignPreviewLayout title="Notifications">
        <Head title="Notifications" />

        <div class="ntf-page">
            <!-- ── Page header ──────────────────────────────────────────── -->
            <div class="ntf-page-header">
                <div class="ntf-page-header__left">
                    <h1 class="ntf-title">Notifications</h1>
                    <p class="ntf-subtitle">Stay on top of orders, bids, market moves, and account activity across the platform.</p>
                </div>
                <div class="ntf-page-header__actions">
                    <button type="button" class="ntf-btn ntf-btn--outline" @click="refresh">
                        <el-icon><Refresh /></el-icon> Refresh
                    </button>
                    <button type="button" class="ntf-btn ntf-btn--primary" :disabled="!unreadCount" @click="markAllRead">
                        <el-icon><CircleCheck /></el-icon> Mark all as read
                    </button>
                </div>
            </div>

            <!-- ── Notification feed card ──────────────────────────────── -->
            <div class="ntf-section">
                <div class="ntf-toolbar">
                    <div class="ntf-toolbar__left">
                        <el-checkbox
                            class="ntf-toolbar__check"
                            size="small"
                            :model-value="allSelected"
                            :indeterminate="someSelected"
                            @change="toggleSelectAll"
                        />
                        <span class="ntf-toolbar__divider" />

                        <template v-if="selected.size">
                            <button type="button" class="ntf-icon-btn" title="Mark selected as read" @click="bulkMarkRead">
                                <el-icon :size="16"><View /></el-icon>
                            </button>
                            <button type="button" class="ntf-icon-btn ntf-icon-btn--danger" title="Delete selected" @click="bulkDelete">
                                <el-icon :size="16"><Delete /></el-icon>
                            </button>
                            <span class="ntf-toolbar__selected-count">{{ selected.size }} selected</span>
                        </template>
                        <span v-else class="ntf-toolbar__hint">Select notifications to manage them in bulk</span>
                    </div>

                    <div class="ntf-toolbar__right ntf-mono">
                        {{ filteredNotifications.length }} {{ filteredNotifications.length === 1 ? 'notification' : 'notifications' }}
                    </div>
                </div>

                <!-- ── Category filters ────────────────────────────────── -->
                <div class="ntf-filters">
                    <button
                        v-for="f in filters"
                        :key="f.key"
                        type="button"
                        class="ntf-filter"
                        :class="{ 'ntf-filter--active': activeFilter === f.key }"
                        @click="activeFilter = f.key"
                    >
                        <el-icon v-if="f.icon" :size="13"><component :is="f.icon" /></el-icon>
                        {{ f.label }}
                        <span class="ntf-filter__count">{{ tabCount(f.key) }}</span>
                    </button>
                </div>

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
                        <span v-if="n.body" class="ntf-row__snippet">{{ n.body }}</span>
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
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* Notifications — app-wide theme. Tokens come from the shared
   DesignPreviewLayout --dp-* palette (defined on .dp-shell); literal hex
   fallbacks are the same values so the page reads correctly on its own.
   Uses the same icon-tile KPI + section-card language as the rest of the
   app (Calendar, Contacts, Orders). */
.ntf-page {
    --card-border: var(--dp-outline-variant, #E5E7EB);
    --surface: var(--dp-surface-container-lowest, #ffffff);
    --surface-muted: var(--dp-surface-container-low, #F5F6F7);
    --surface-elevated: var(--dp-surface-container, #F1F2F3);
    --border: var(--dp-outline-variant, #E5E7EB);
    --primary: var(--dp-primary, #000000);
    --on-primary: var(--dp-on-primary, #ffffff);
    --text: var(--dp-on-surface, #121516);
    --text-2: var(--dp-on-surface-variant, #4B5457);
    --text-muted: var(--dp-outline, #6F7677);
    --error: var(--dp-error, #F85149);
    font-family: var(--dp-font-sans, 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
    color: var(--text);
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.ntf-mono {
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-variant-numeric: tabular-nums;
}

/* ── Page header ─────────────────────────────────────────────────────── */
.ntf-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.ntf-page-header__left { max-width: 640px; }
.ntf-page-header__actions { display: flex; gap: 8px; flex-wrap: wrap; }

.ntf-title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    margin: 0 0 6px;
}
.ntf-subtitle {
    font-size: 0.9375rem;
    line-height: 1.5rem;
    color: var(--text-muted);
    margin: 0;
    max-width: 64ch;
    text-wrap: pretty;
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.ntf-btn {
    height: 36px;
    padding: 0 16px;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 120ms ease, background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ntf-btn--primary {
    background: var(--primary);
    border: 1px solid transparent;
    color: var(--on-primary);
}
.ntf-btn--primary:hover:not(:disabled) { opacity: 0.88; }
.ntf-btn--primary:disabled { opacity: 0.5; cursor: default; }
.ntf-btn--outline {
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--text);
}
.ntf-btn--outline:hover { background: var(--surface-muted); }

/* ── Feed card ───────────────────────────────────────────────────────── */
.ntf-section {
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow, none);
    overflow: hidden;
}

/* ── Toolbar ─────────────────────────────────────────────────────────── */
.ntf-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}
.ntf-toolbar__left { display: flex; align-items: center; gap: 4px; }
.ntf-toolbar__check { margin-right: 4px; }
.ntf-toolbar__divider { width: 1px; height: 18px; background: var(--border); margin: 0 6px; }
.ntf-toolbar__selected-count { font-size: 13px; font-weight: 700; color: var(--text-2); margin-left: 4px; }
.ntf-toolbar__hint { font-size: 12.5px; color: var(--text-muted); }
.ntf-toolbar__right { font-size: 12px; color: var(--text-muted); flex-shrink: 0; }

.ntf-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--text-2);
    cursor: pointer;
    flex-shrink: 0;
    transition: background 120ms ease, color 120ms ease;
}
.ntf-icon-btn:hover { background: var(--surface-muted); color: var(--text); }
.ntf-icon-btn--danger:hover { background: var(--dp-error-container, #FEEDED); color: var(--dp-error, #F85149); }

/* ── Category filters ────────────────────────────────────────────────── */
.ntf-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}
.ntf-filter {
    height: 32px;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 999px;
    color: var(--text-2);
    font-family: inherit;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.ntf-filter .el-icon { color: var(--text-muted); }
.ntf-filter:hover { background: var(--surface-muted); color: var(--text); }
.ntf-filter--active { background: var(--primary); border-color: var(--primary); color: var(--on-primary); }
.ntf-filter--active .el-icon { color: var(--on-primary); }
.ntf-filter__count {
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-size: 11px;
    line-height: 16px;
    color: var(--text-muted);
}
.ntf-filter--active .ntf-filter__count { color: var(--on-primary); opacity: 0.78; }

/* ── Empty state ─────────────────────────────────────────────────────── */
.ntf-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    text-align: center;
    padding: 56px 16px;
}
.ntf-empty-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--surface-muted);
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ntf-empty p { font-size: 14px; color: var(--text-2); margin: 0; }

/* ── Rows ────────────────────────────────────────────────────────────── */
.ntf-feed { display: flex; flex-direction: column; }
.ntf-row {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 12px 16px;
    cursor: pointer;
    transition: background 120ms ease;
}
.ntf-row:last-child { border-bottom: none; }
.ntf-row--unread { background: var(--surface-muted); }
.ntf-row:hover { background: var(--surface-muted); }
.ntf-row--selected {
    background: var(--surface-muted);
    box-shadow: inset 0 0 0 1px var(--primary, #000000);
}
.ntf-row:focus-visible { outline: 2px solid var(--primary, #000000); outline-offset: -2px; }

.ntf-row__check { display: flex; flex-shrink: 0; }
.ntf-row__avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ntf-chip--green { background: var(--dp-secondary-container, #E5FAE7); color: var(--dp-on-secondary-container, #2F6B35); }
.ntf-chip--blue { background: #dbeafe; color: #1e40af; }
.ntf-chip--violet { background: #ede9fe; color: #6d28d9; }
.ntf-chip--amber { background: #fef3c7; color: #92400e; }
.ntf-chip--slate { background: var(--dp-surface-container-high, #E5E7EB); color: var(--dp-on-surface-variant, #4B5457); }

.ntf-row__main {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.ntf-row__dot { width: 7px; height: 7px; border-radius: 50%; background: var(--primary); flex-shrink: 0; }
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
    font-size: 13.5px;
    line-height: 20px;
    font-weight: 500;
    color: var(--text-2);
}
.ntf-row--unread .ntf-row__title { font-weight: 800; color: var(--text); }
.ntf-row__snippet {
    flex: 1;
    min-width: 0;
    font-size: 13px;
    line-height: 20px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ntf-row--unread .ntf-row__snippet { color: var(--text-2); }

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
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-size: 0.75rem;
    color: var(--text-muted);
    white-space: nowrap;
    transition: opacity 0.1s;
}
.ntf-row--unread .ntf-row__time { color: var(--text-2); font-weight: 700; }
.ntf-row__actions {
    position: absolute;
    right: 0;
    display: flex;
    align-items: center;
    gap: 2px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.1s;
    background: var(--surface-muted);
    padding-left: 8px;
}
.ntf-row:hover .ntf-row__time { opacity: 0; }
.ntf-row:hover .ntf-row__actions { opacity: 1; pointer-events: auto; }
.ntf-row__action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    color: var(--text-muted);
    flex-shrink: 0;
    cursor: pointer;
    transition: background 120ms, color 120ms;
}
.ntf-row__action:hover { background: var(--surface-elevated); color: var(--text); }
.ntf-row__action--danger:hover { background: var(--dp-error-container, #FEEDED); color: var(--dp-error, #F85149); }

/* ── Priority tag ────────────────────────────────────────────────────── */
.ntf-priority {
    display: inline-flex;
    flex-shrink: 0;
    border-radius: 999px;
    font-size: 0.5938rem;
    font-weight: 800;
    padding: 2px 8px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.ntf-priority--high { background: #fef3c7; color: #92400e; }
.ntf-priority--critical { background: var(--dp-error-container, #FEEDED); color: var(--dp-error, #F85149); }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .ntf-page-header { flex-direction: column; align-items: stretch; }
    .ntf-toolbar { flex-direction: column; align-items: stretch; gap: 8px; }
    .ntf-row { flex-wrap: wrap; row-gap: 6px; }
    .ntf-row__chip { display: none; }
    .ntf-row__side { width: 60px; }
}

/* ── Reduced motion ──────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .ntf-btn,
    .ntf-icon-btn,
    .ntf-filter,
    .ntf-row,
    .ntf-row__action {
        transition: none;
    }
}
</style>
