<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
    Check, Setting, ArrowRight, CircleCheck, CircleCheckFilled, Bell, Box, Wallet,
} from '@element-plus/icons-vue';

const props = defineProps({
    notifications: { type: Array, default: () => [] },
});

/* ── Category presentation — labels/icons/colors for categories the backend
   actually emits today (orders, wallet); anything else falls back to a
   neutral, title-cased presentation rather than being invented. ────────── */
const CATEGORY_META = {
    orders: { label: 'Orders', icon: Box, bg: 'var(--dp-primary-container)', fg: 'var(--dp-on-primary-container)' },
    wallet: { label: 'Wallet', icon: Wallet, bg: 'var(--dp-secondary-container)', fg: 'var(--dp-on-secondary-container)' },
};

function categoryMeta(category) {
    if (CATEGORY_META[category]) return CATEGORY_META[category];
    const label = (category || 'general').replace(/[_.]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    return { label, icon: Bell, bg: 'var(--dp-surface-container-high)', fg: 'var(--dp-on-surface-variant)' };
}

function timeAgo(dateTime) {
    if (!dateTime) return '';
    const diffMs = Date.now() - new Date(dateTime.replace(' ', 'T')).getTime();
    const diffMin = Math.round(diffMs / 60000);
    if (diffMin < 1) return 'Just now';
    if (diffMin < 60) return `${diffMin}m ago`;
    const diffHr = Math.round(diffMin / 60);
    if (diffHr < 24) return `${diffHr}h ago`;
    const diffDay = Math.round(diffHr / 24);
    if (diffDay < 7) return `${diffDay}d ago`;
    return new Date(dateTime.replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function dateGroupOf(dateTime) {
    if (!dateTime) return 'earlier';
    const date = new Date(dateTime.replace(' ', 'T'));
    const now = new Date();
    const startOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    const diffDays = Math.round((startOfDay(now) - startOfDay(date)) / 86400000);
    if (diffDays <= 0) return 'today';
    if (diffDays === 1) return 'yesterday';
    return 'earlier';
}

const GROUP_ORDER = ['today', 'yesterday', 'earlier'];
const GROUP_LABEL = { today: 'Today', yesterday: 'Yesterday', earlier: 'Earlier This Week' };
const GROUP_META = { today: 'Live Feed', yesterday: 'Historical Ledger', earlier: 'Archival Status' };

const unreadCount = computed(() => props.notifications.filter((n) => !n.is_read).length);

const groupedNotifications = computed(() => {
    const groups = new Map();
    props.notifications.forEach((n) => {
        const key = dateGroupOf(n.created_at);
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(n);
    });
    return GROUP_ORDER
        .filter((key) => groups.has(key))
        .map((key) => ({ key, label: GROUP_LABEL[key], meta: GROUP_META[key], items: groups.get(key) }));
});

function markRead(notification) {
    if (notification.is_read) return;
    router.patch(route('notifications.read', notification.id), {}, { preserveScroll: true, preserveState: true });
}

function markAllRead() {
    if (unreadCount.value === 0) return;
    router.post(route('notifications.read-all'), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => ElNotification({ title: 'All Caught Up', message: 'Every notification has been marked as read.', type: 'success', duration: 3000, offset: 84 }),
    });
}

function openAction(notification) {
    if (!notification.is_read) {
        router.patch(route('notifications.read', notification.id), {}, {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                if (notification.action_url) router.visit(notification.action_url);
            },
        });
        return;
    }
    if (notification.action_url) router.visit(notification.action_url);
}
</script>

<template>
    <MainLayout title="Notifications">
        <div class="np-page">
            <div class="np-header">
                <div class="np-header__text">
                    <h1 class="np-title">Notifications</h1>
                    <p class="np-subtitle">Every update on your orders, wallet, and account activity, in one place.</p>
                </div>
                <div class="np-header__actions">
                    <button type="button" class="np-btn" :disabled="unreadCount === 0" @click="markAllRead">
                        <el-icon :size="16" color="var(--dp-primary)"><Check /></el-icon> Mark all as read
                    </button>
                    <Link :href="route('settings.index')" class="np-btn np-btn--ghost">
                        <el-icon :size="16"><Setting /></el-icon> Notification Settings
                    </Link>
                </div>
            </div>

            <div class="np-feed">
                <section v-for="group in groupedNotifications" :key="group.key" class="np-group">
                    <div class="np-group__head">
                        <span class="np-group__label">{{ group.label }}</span>
                        <span class="np-group__badge">{{ group.items.length }}</span>
                        <span class="np-group__meta">{{ group.meta }}</span>
                    </div>
                    <div class="np-list">
                        <article
                            v-for="notification in group.items"
                            :key="notification.id"
                            class="np-item"
                            :class="{ 'np-item--read': notification.is_read }"
                        >
                            <div class="np-item__marker">
                                <span class="np-item__dot" :class="{ 'np-item__dot--on': !notification.is_read }" />
                                <span
                                    class="np-item__icon"
                                    :style="{ background: categoryMeta(notification.category).bg, color: categoryMeta(notification.category).fg }"
                                >
                                    <el-icon :size="18"><component :is="categoryMeta(notification.category).icon" /></el-icon>
                                </span>
                            </div>
                            <div class="np-item__body">
                                <div class="np-item__top">
                                    <div class="np-item__titlewrap">
                                        <h2 class="np-item__title" :class="{ 'np-item__title--unread': !notification.is_read }">{{ notification.title }}</h2>
                                        <span v-if="!notification.is_read && notification.priority === 'high'" class="np-badge np-badge--urgent">Action Required</span>
                                        <span class="np-badge np-badge--category">{{ categoryMeta(notification.category).label }}</span>
                                    </div>
                                    <time class="np-item__time" :title="notification.created_at">{{ timeAgo(notification.created_at) }}</time>
                                </div>
                                <p v-if="notification.body" class="np-item__text">{{ notification.body }}</p>
                                <div class="np-item__actions">
                                    <button v-if="notification.action_url" type="button" class="np-cta" @click="openAction(notification)">
                                        View Details <el-icon :size="13"><ArrowRight /></el-icon>
                                    </button>
                                    <button
                                        type="button"
                                        class="np-item__toggle"
                                        :disabled="notification.is_read"
                                        :title="notification.is_read ? 'Read' : 'Mark as read'"
                                        @click="markRead(notification)"
                                    >
                                        <el-icon :size="18"><component :is="notification.is_read ? CircleCheck : CircleCheckFilled" /></el-icon>
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <div v-if="groupedNotifications.length === 0" class="np-empty">
                    <div class="np-empty__icon"><el-icon :size="30"><CircleCheck /></el-icon></div>
                    <h3 class="np-empty__title">You're all caught up!</h3>
                    <p class="np-empty__text">You don't have any notifications yet. New activity on your orders and wallet will show up here.</p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.np-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding-bottom: 40px;
}

/* Header */
.np-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--dp-outline-variant);
}
.np-title {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--dp-on-surface);
    letter-spacing: -0.01em;
}
.np-subtitle {
    margin: 4px 0 0;
    font-size: 13px;
    color: var(--dp-on-surface-variant);
    max-width: 40rem;
}
.np-header__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

.np-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 36px;
    padding: 0 14px;
    border-radius: var(--dp-card-radius);
    background: var(--dp-surface-container-low);
    border: 1px solid transparent;
    color: var(--dp-on-surface);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    text-decoration: none;
    transition: background 0.12s ease, opacity 0.12s ease;
}
.np-btn:hover:not(:disabled) { background: var(--dp-surface-container-high); }
.np-btn:disabled { opacity: 0.5; cursor: default; }
.np-btn--ghost { background: var(--dp-surface-container-lowest); border-color: var(--dp-outline-variant); }
.np-btn--ghost:hover { background: var(--dp-surface-container-low); }

/* Feed */
.np-feed { display: flex; flex-direction: column; gap: 32px; }
.np-group__head { display: flex; align-items: center; gap: 8px; padding: 0 4px 10px; }
.np-group__label { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dp-on-surface-variant); }
.np-group__badge { font-size: 10px; font-weight: 700; color: var(--dp-on-surface); background: var(--dp-surface-container-high); border-radius: 4px; padding: 1px 7px; }
.np-group__meta { margin-left: auto; font-size: 11px; color: var(--dp-outline); }

.np-list { display: flex; flex-direction: column; gap: 10px; }
.np-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    background: var(--dp-surface-container-lowest);
    border-radius: 10px;
}
.np-item--read { opacity: 0.9; }
.np-item__marker { display: flex; align-items: center; gap: 8px; padding-top: 4px; flex-shrink: 0; }
.np-item__dot { width: 8px; height: 8px; border-radius: 50%; background: transparent; flex-shrink: 0; }
.np-item__dot--on { background: var(--dp-primary); }
.np-item__icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.np-item__body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
.np-item__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
.np-item__titlewrap { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.np-item__title { margin: 0; font-size: 13.5px; font-weight: 500; color: var(--dp-on-surface); }
.np-item__title--unread { font-weight: 700; }
.np-item__time { font-size: 12px; color: var(--dp-on-surface-variant); white-space: nowrap; flex-shrink: 0; }
.np-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
.np-badge--urgent { background: var(--dp-error-container); color: var(--dp-on-error-container); }
.np-badge--category { background: var(--dp-surface-container); color: var(--dp-on-surface-variant); font-weight: 600; }
.np-item__text { margin: 0; font-size: 12.5px; line-height: 1.6; color: var(--dp-on-surface-variant); }
.np-item__actions { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 4px; flex-wrap: wrap; }
.np-cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 30px;
    padding: 0 12px;
    border-radius: 8px;
    border: none;
    background: var(--dp-surface-container-high);
    color: var(--dp-on-surface);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.np-cta:hover { background: var(--dp-surface-container); }
.np-item__toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: var(--dp-outline);
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}
.np-item__toggle:hover:not(:disabled) { background: var(--dp-surface-container); color: var(--dp-primary); }
.np-item__toggle:disabled { cursor: default; opacity: 0.5; }

/* Empty state */
.np-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 64px 24px;
    text-align: center;
    background: var(--dp-surface-container-lowest);
    border-radius: 14px;
}
.np-empty__icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--dp-surface-container-low);
    color: var(--dp-outline);
    margin-bottom: 6px;
}
.np-empty__title { margin: 0; font-size: 15px; font-weight: 700; color: var(--dp-on-surface); }
.np-empty__text { margin: 0 0 10px; font-size: 12.5px; color: var(--dp-on-surface-variant); max-width: 26rem; }

@media (max-width: 640px) {
    .np-item { flex-wrap: wrap; }
    .np-item__actions { width: 100%; }
}
</style>
