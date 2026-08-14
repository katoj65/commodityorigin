<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import {
    Bell,
    Box,
    Close,
    Document,
    Expand,
    HomeFilled,
    MagicStick,
    Message,
    Odometer,
    Search,
    ShoppingCart,
    SwitchButton,
    User,
} from '@element-plus/icons-vue';
import AppAside from '@/Components/Aside/AppAside.vue';
import AiChatWidget from '@/Components/AiChatWidget.vue';

const props = defineProps({
    title: String,
    fullWidth: {
        type: Boolean,
        default: false,
    },
    flush: {
        type: Boolean,
        default: false,
    },
    showBanner: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();
const mobileMenuOpen = ref(false);

const user = computed(() => page.props.auth.user);
const unreadNotificationsCount = computed(() => page.props.unreadNotificationsCount ?? 0);
const recentNotifications = computed(() => page.props.recentNotifications ?? []);
const cartActiveCount = computed(() => page.props.cartActiveCount ?? 0);
const userInitials = computed(() => {
    const name = user.value?.name ?? '';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('') || 'CO';
});

const mobileNavSections = computed(() => [
    {
        title: 'Main',
        items: [
            { label: 'Dashboard', href: route('dashboard'), active: route().current('dashboard') },
        ],
    },
    {
        title: 'Marketplace',
        items: [
            { label: 'Browse Coffee', href: route('market.index'), active: route().current('market.index') },
            { label: 'Live Market', href: route('market.active'), active: route().current('market.active') },
            { label: 'Auctions', href: route('auction.index'), active: route().current('auction.*') },
        ],
    },
    {
        title: 'Operations',
        items: [
            { label: 'My Orders', href: route('orders.index'), active: route().current('orders.*') },
            { label: 'Farmers', href: route('farmer.index'), active: route().current('farmer.index') },
            { label: 'Coffee Farms', href: route('farm.index'), active: route().current('farm.*') },
            { label: 'Cooperatives', href: route('cooperative.index'), active: route().current('cooperative.*') },
            { label: 'Season', href: route('season.index'), active: route().current('season.*') },
            { label: 'Harvests', href: route('harvest.index'), active: route().current('harvest.*') },
            { label: 'Batches', href: route('batch.index'), active: route().current('batch.*') },
        ],
    },
    {
        title: 'Financials',
        items: [
            { label: 'Wallet', href: route('wallet.index'), active: route().current('wallet.*') },
        ],
    },
    {
        title: 'Analysis',
        items: [
            { label: 'Documentation', href: route('documentation.index'), active: route().current('documentation.*') },
            { label: 'AI Assistant', href: route('chat.index'), active: route().current('chat.*') },
        ],
    },
]);

const searchQuery = ref('');
const suggestions = ref([]);
const suggestOpen = ref(false);
const suggestLoading = ref(false);
const activeSuggestIndex = ref(-1);
let suggestTimer = null;
let suggestRequestId = 0;

function fetchSuggestions(q) {
    const requestId = ++suggestRequestId;
    suggestLoading.value = true;

    window.axios.get(route('search.suggest'), { params: { q } })
        .then(({ data }) => {
            if (requestId !== suggestRequestId) return;
            suggestions.value = data.results ?? [];
            suggestOpen.value = true;
        })
        .catch(() => {
            if (requestId !== suggestRequestId) return;
            suggestions.value = [];
        })
        .finally(() => {
            if (requestId === suggestRequestId) suggestLoading.value = false;
        });
}

watch(searchQuery, (value) => {
    clearTimeout(suggestTimer);
    activeSuggestIndex.value = -1;

    const q = value.trim();
    if (!q) {
        suggestions.value = [];
        suggestOpen.value = false;
        return;
    }

    suggestTimer = setTimeout(() => fetchSuggestions(q), 200);
});

function closeSuggestions() {
    suggestOpen.value = false;
    activeSuggestIndex.value = -1;
}

function onSearchFocus() {
    if (searchQuery.value.trim() && suggestions.value.length) {
        suggestOpen.value = true;
    }
}

function onSearchBlur() {
    setTimeout(closeSuggestions, 120);
}

function goToSuggestion(item) {
    closeSuggestions();
    searchQuery.value = '';
    router.visit(route('market.show', item.id));
}

function submitSearch() {
    closeSuggestions();
    const q = searchQuery.value.trim();
    router.visit(route('search.index'), q ? { data: { q }, method: 'get' } : undefined);
}

function handleSearchKeydown(e) {
    if (!suggestOpen.value || !suggestions.value.length) {
        if (e.key === 'Enter') submitSearch();
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeSuggestIndex.value = (activeSuggestIndex.value + 1) % suggestions.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeSuggestIndex.value = activeSuggestIndex.value <= 0
            ? suggestions.value.length - 1
            : activeSuggestIndex.value - 1;
    } else if (e.key === 'Enter') {
        if (activeSuggestIndex.value >= 0) {
            goToSuggestion(suggestions.value[activeSuggestIndex.value]);
        } else {
            submitSearch();
        }
    } else if (e.key === 'Escape') {
        closeSuggestions();
    }
}

function openAiAssistant() {
    window.dispatchEvent(new Event('open-ai-chat'));
}

const accountMenuItems = [
    { label: 'Profile settings', href: () => route('profile.show'), icon: User },
    { label: 'Dashboard', href: () => route('dashboard'), icon: Odometer },
    { label: 'Home', href: () => route('home'), icon: HomeFilled },
    { label: 'Documentation', href: () => route('documentation.index'), icon: Document },
];

function logout() {
    router.post(route('logout'));
}

function handleAccountCommand(command) {
    if (command === 'logout') {
        logout();
        return;
    }

    router.visit(command);
}

function notificationTimeAgo(dateTime) {
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

function openNotification(notification) {
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

    if (notification.action_url) {
        router.visit(notification.action_url);
    }
}

function markAllNotificationsRead() {
    router.post(route('notifications.read-all'), {}, { preserveScroll: true, preserveState: true });
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}

function syncMobileNavState() {
    if (window.innerWidth >= 1024) {
        closeMobileMenu();
    }
}

const CALENDAR_NOTIFIED_STORAGE_KEY = 'calendarDueNotifiedDate';

function notifyDueCalendarEvents() {
    const dueEvents = page.props.dueCalendarEvents ?? [];
    if (!dueEvents.length) return;

    const today = new Date().toISOString().slice(0, 10);
    if (localStorage.getItem(CALENDAR_NOTIFIED_STORAGE_KEY) === today) return;

    localStorage.setItem(CALENDAR_NOTIFIED_STORAGE_KEY, today);

    ElNotification({
        title: `${dueEvents.length} event${dueEvents.length > 1 ? 's' : ''} due today`,
        message: dueEvents.map((e) => e.title).join(', '),
        type: 'warning',
        duration: 8000,
        position: 'bottom-right',
    });
}

const TASK_NOTIFIED_STORAGE_KEY = 'taskDueNotifiedDate';

function notifyDueTasks() {
    const dueTasks = page.props.dueTasksToday ?? [];
    if (!dueTasks.length) return;

    const today = new Date().toISOString().slice(0, 10);
    if (localStorage.getItem(TASK_NOTIFIED_STORAGE_KEY) === today) return;

    localStorage.setItem(TASK_NOTIFIED_STORAGE_KEY, today);

    ElNotification({
        title: `${dueTasks.length} task${dueTasks.length > 1 ? 's' : ''} need${dueTasks.length > 1 ? '' : 's'} a decision today`,
        message: dueTasks.map((t) => t.title).join(', '),
        type: 'error',
        duration: 8000,
        position: 'bottom-right',
        onClick: () => router.visit(route('calendar.index')),
    });
}

onMounted(() => {
    document.documentElement.classList.add('app-layout-scrollless');
    document.body.classList.add('app-layout-scrollless');
    window.addEventListener('resize', syncMobileNavState);
    notifyDueCalendarEvents();
    notifyDueTasks();
});

onBeforeUnmount(() => {
    document.documentElement.classList.remove('app-layout-scrollless');
    document.body.classList.remove('app-layout-scrollless');
    window.removeEventListener('resize', syncMobileNavState);
});
</script>

<template>
    <div class="dashboard-shell">
        <Head :title="title" />

        <el-header class="shell-header" height="64px">
            <el-button
                text
                circle
                class="shell-icon-button shell-icon-button--mobile"
                @click="mobileMenuOpen = true"
            >
                <el-icon :size="16"><Expand /></el-icon>
            </el-button>

            <div class="shell-search">
                <el-input
                    v-model="searchQuery"
                    placeholder="Search origin, variety, or supplier…"
                    :prefix-icon="Search"
                    class="shell-search__input"
                    @focus="onSearchFocus"
                    @blur="onSearchBlur"
                    @keydown="handleSearchKeydown"
                />

                <transition name="shell-search-fade">
                    <div v-if="suggestOpen" class="shell-search__panel">
                        <div v-if="suggestLoading" class="shell-search__loading">Searching…</div>
                        <template v-else-if="suggestions.length">
                            <button
                                v-for="(item, idx) in suggestions"
                                :key="item.id"
                                type="button"
                                class="shell-search__item"
                                :class="{ 'shell-search__item--active': idx === activeSuggestIndex }"
                                @mousedown.prevent="goToSuggestion(item)"
                                @mouseenter="activeSuggestIndex = idx"
                            >
                                <span class="shell-search__item-icon"><el-icon :size="14"><Box /></el-icon></span>
                                <span class="shell-search__item-body">
                                    <span class="shell-search__item-name">{{ item.name }}</span>
                                    <span class="shell-search__item-meta">
                                        {{ item.origin }}<template v-if="item.type"> · {{ item.type }}</template><template v-if="item.lot_code"> · {{ item.lot_code }}</template>
                                    </span>
                                </span>
                                <span class="shell-search__item-price">${{ Number(item.price_per_kg).toFixed(2) }}<small>/kg</small></span>
                            </button>
                            <button type="button" class="shell-search__footer" @mousedown.prevent="submitSearch">
                                See all results for "{{ searchQuery }}"
                            </button>
                        </template>
                        <div v-else class="shell-search__empty">
                            No market items match "{{ searchQuery }}"
                        </div>
                    </div>
                </transition>
            </div>

            <div class="shell-header__actions">
                <el-button class="shell-ai-btn" round @click="openAiAssistant">
                    <el-icon :size="16"><MagicStick /></el-icon>
                    <span class="shell-ai-btn__label">AI Assistant</span>
                </el-button>

                <div class="shell-header__icons">
                    <el-popover placement="bottom-end" :width="320" trigger="click" popper-class="shell-notif-popover">
                        <template #reference>
                            <el-badge :value="unreadNotificationsCount" :hidden="unreadNotificationsCount === 0" :max="99">
                                <el-button text circle class="shell-icon-button">
                                    <el-icon :size="18"><Bell /></el-icon>
                                </el-button>
                            </el-badge>
                        </template>

                        <div class="notif-menu-head">
                            <span>Notifications</span>
                            <el-button
                                v-if="unreadNotificationsCount > 0"
                                text
                                size="small"
                                type="primary"
                                @click="markAllNotificationsRead"
                            >
                                Mark all read
                            </el-button>
                        </div>

                        <el-scrollbar max-height="352px" class="notif-menu-list">
                            <button
                                v-for="notification in recentNotifications"
                                :key="notification.id"
                                type="button"
                                class="notif-menu-item"
                                :class="{ 'notif-menu-item--unread': !notification.is_read }"
                                @click="openNotification(notification)"
                            >
                                <span class="notif-menu-item__dot" :class="{ 'notif-menu-item__dot--on': !notification.is_read }" />
                                <span class="notif-menu-item__body">
                                    <span class="notif-menu-item__title">{{ notification.title }}</span>
                                    <span v-if="notification.body" class="notif-menu-item__text">{{ notification.body }}</span>
                                    <span class="notif-menu-item__time">{{ notificationTimeAgo(notification.created_at) }}</span>
                                </span>
                            </button>

                            <el-empty
                                v-if="recentNotifications.length === 0"
                                description="You're all caught up"
                                :image-size="44"
                            />
                        </el-scrollbar>

                        <Link :href="route('notifications.index')" class="notif-menu-footer">
                            View all notifications
                        </Link>
                    </el-popover>

                    <el-tooltip content="Messages" placement="bottom" :show-after="200">
                        <el-button text circle class="shell-icon-button" @click="router.visit(route('chat.index'))">
                            <el-icon :size="18"><Message /></el-icon>
                        </el-button>
                    </el-tooltip>

                    <el-tooltip content="Cart" placement="bottom" :show-after="200">
                        <el-badge :value="cartActiveCount" :hidden="cartActiveCount === 0" :max="99">
                            <el-button text circle class="shell-icon-button" @click="router.visit(route('checkout.index'))">
                                <el-icon :size="18"><ShoppingCart /></el-icon>
                            </el-button>
                        </el-badge>
                    </el-tooltip>
                </div>

                <div class="shell-header__divider" />

                <el-dropdown trigger="click" placement="bottom-end" @command="handleAccountCommand">
                    <div class="shell-header__account">
                        <el-avatar :size="32" class="shell-header__avatar">{{ userInitials }}</el-avatar>
                        <div class="shell-header__account-text">
                            <div class="shell-header__account-name">{{ user?.name }}</div>
                            <div class="shell-header__account-role">{{ user?.role || 'Account' }}</div>
                        </div>
                    </div>

                    <template #dropdown>
                        <div class="account-menu-head">
                            <div class="account-menu-name">{{ user?.name }}</div>
                            <div class="account-menu-role">{{ user?.role || 'Account' }}</div>
                        </div>

                        <el-dropdown-menu>
                            <el-dropdown-item
                                v-for="item in accountMenuItems"
                                :key="item.label"
                                :command="item.href()"
                                :icon="item.icon"
                            >
                                {{ item.label }}
                            </el-dropdown-item>
                            <el-dropdown-item divided command="logout" :icon="SwitchButton">
                                Log out
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
        </el-header>

        <el-drawer
            v-model="mobileMenuOpen"
            direction="ltr"
            size="85%"
            :with-header="false"
            class="shell-drawer"
        >
            <div class="shell-drawer__head">
                <span class="shell-drawer__brand-title">Bean Origin</span>
                <el-button text circle @click="closeMobileMenu">
                    <el-icon :size="16"><Close /></el-icon>
                </el-button>
            </div>

            <div v-for="section in mobileNavSections" :key="section.title" class="shell-drawer__section">
                <div class="shell-drawer__label">{{ section.title }}</div>
                <Link
                    v-for="item in section.items"
                    :key="`mobile-${section.title}-${item.label}`"
                    :href="item.href"
                    class="shell-drawer__link"
                    :class="{ active: item.active }"
                    @click="closeMobileMenu"
                >
                    {{ item.label }}
                </Link>
            </div>

            <div class="shell-drawer__footer">
                <div class="shell-drawer__user">
                    <el-avatar :size="32" class="shell-header__avatar">{{ userInitials }}</el-avatar>
                    <div class="shell-drawer__user-name">{{ user?.name }}</div>
                </div>
                <el-button class="shell-drawer__logout" @click="logout">Sign out</el-button>
            </div>
        </el-drawer>

        <div class="shell-body">
            <AppAside />

            <main class="shell-main" :class="{ 'shell-main--flush': props.flush }">
                <div class="shell-main__inner" :class="{ 'shell-main__inner--full': props.fullWidth && !props.flush }">
                    <slot />
                </div>
            </main>
        </div>

        <AiChatWidget />
    </div>
</template>

<style scoped>
:global(html.app-layout-scrollless),
:global(body.app-layout-scrollless) {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

:global(html.app-layout-scrollless::-webkit-scrollbar),
:global(body.app-layout-scrollless::-webkit-scrollbar) {
    display: none;
}

.dashboard-shell {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    overflow-x: hidden;
    background: var(--surface);
    font-family: var(--font-sans);
}

.shell-header {
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    z-index: 30;
    display: flex;
    align-items: center;
    height: 64px !important;
    padding: 0 24px;
    gap: 16px;
    background: #fff;
    border-bottom: 1px solid var(--border-subtle, rgba(15, 23, 42, 0.08));
    box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
}

@media (min-width: 1024px) {
    .shell-header {
        left: 288px;
    }
}

.shell-icon-button--mobile {
    display: inline-flex;
    flex-shrink: 0;
}

@media (min-width: 1024px) {
    .shell-icon-button--mobile {
        display: none;
    }
}

.shell-search {
    position: relative;
    flex: 1 1 auto;
    max-width: 520px;
    min-width: 0;
}

.shell-search__panel {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.14);
    padding: 6px;
    z-index: 40;
    max-height: 400px;
    overflow-y: auto;
}

.shell-search__loading,
.shell-search__empty {
    padding: 16px 12px;
    font-size: 13px;
    color: var(--text-muted);
    text-align: center;
}

.shell-search__item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    border: none;
    background: none;
    padding: 9px 10px;
    border-radius: 9px;
    text-align: left;
    cursor: pointer;
    transition: background 0.1s ease;
}

.shell-search__item--active,
.shell-search__item:hover {
    background: var(--el-color-primary-light-9);
}

.shell-search__item-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: var(--surface-container-low);
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.shell-search__item-body {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.shell-search__item-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.shell-search__item-meta {
    font-size: 11px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.shell-search__item-price {
    font-size: 13px;
    font-weight: 700;
    color: var(--brand-primary);
    flex-shrink: 0;
    white-space: nowrap;
}

.shell-search__item-price small {
    font-size: 10px;
    font-weight: 600;
    color: var(--text-muted);
}

.shell-search__footer {
    display: block;
    width: 100%;
    border: none;
    border-top: 1px solid var(--border-subtle);
    background: none;
    margin-top: 4px;
    padding: 10px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--brand-primary);
    text-align: center;
    cursor: pointer;
    border-radius: 0 0 9px 9px;
}

.shell-search__footer:hover {
    background: var(--surface-container-low);
}

.shell-search-fade-enter-active,
.shell-search-fade-leave-active {
    transition: opacity 0.12s ease, transform 0.12s ease;
}

.shell-search-fade-enter-from,
.shell-search-fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

.shell-search__input :deep(.el-input__wrapper) {
    border-radius: var(--radius-pill);
    background: var(--surface-container-low);
    border: 1px solid var(--border-subtle);
    box-shadow: none;
    padding: 0 16px;
}

.shell-search__input :deep(.el-input__wrapper.is-focus) {
    border-color: var(--el-color-primary);
    box-shadow: 0 0 0 3px var(--el-color-primary-light-9);
}

.shell-header__actions {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 12px;
}

.shell-ai-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 38px;
    padding: 0 18px;
    border: none;
    background: var(--brand-primary-container);
    color: #fff;
    font-weight: 600;
    font-size: 13px;
}

.shell-ai-btn:hover,
.shell-ai-btn:focus {
    background: var(--brand-primary);
    color: #fff;
}

.shell-ai-btn__label {
    display: none;
}

@media (min-width: 640px) {
    .shell-ai-btn__label {
        display: inline;
    }
}

.shell-header__icons {
    display: none;
    align-items: center;
    gap: 2px;
}

@media (min-width: 640px) {
    .shell-header__icons {
        display: flex;
    }
}

.shell-icon-button {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    color: var(--on-surface-variant);
}

.shell-icon-button:hover {
    background: var(--surface-container);
    color: var(--text-main);
}

.shell-header__divider {
    display: none;
    height: 28px;
    width: 1px;
    background: var(--border-subtle);
}

@media (min-width: 640px) {
    .shell-header__divider {
        display: block;
    }
}

.shell-header__account {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px;
    padding-right: 12px;
    border-radius: var(--radius-pill);
    cursor: pointer;
    transition: background 0.15s ease;
}

.shell-header__account:hover {
    background: var(--surface-container);
}

.shell-header__avatar {
    flex-shrink: 0;
    background: var(--brand-primary);
    font-size: 12px;
    font-weight: 700;
}

.shell-header__account-text {
    display: none;
    line-height: 1.3;
}

@media (min-width: 1024px) {
    .shell-header__account-text {
        display: block;
    }
}

.shell-header__account-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
}

.shell-header__account-role {
    font-size: 11px;
    color: var(--text-muted);
}

.notif-menu-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding-bottom: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--el-text-color-primary);
}

.notif-menu-list {
    border-top: 1px solid var(--el-border-color-extra-light);
}

.notif-menu-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    width: 100%;
    border: 0;
    border-bottom: 1px solid var(--el-border-color-extra-light);
    background: transparent;
    padding: 0.625rem 0;
    text-align: left;
    cursor: pointer;
    transition: background 0.15s ease;
}

.notif-menu-item:hover {
    background: var(--el-fill-color-light);
}

.notif-menu-item--unread {
    background: var(--el-color-primary-light-9);
}

.notif-menu-item--unread:hover {
    background: var(--el-color-primary-light-8);
}

.notif-menu-item__dot {
    flex-shrink: 0;
    width: 6px;
    height: 6px;
    margin-top: 6px;
    border-radius: 999px;
    background: transparent;
}

.notif-menu-item__dot--on {
    background: var(--el-color-primary);
}

.notif-menu-item__body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.notif-menu-item__title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--el-text-color-primary);
    line-height: 1.4;
}

.notif-menu-item__text {
    font-size: 0.75rem;
    color: var(--el-text-color-secondary);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notif-menu-item__time {
    font-size: 0.6875rem;
    color: var(--el-text-color-placeholder);
    margin-top: 1px;
}

.notif-menu-footer {
    display: block;
    margin-top: 4px;
    padding-top: 0.625rem;
    border-top: 1px solid var(--el-border-color-extra-light);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--el-color-primary);
    text-align: center;
    text-decoration: none;
}

.notif-menu-footer:hover {
    text-decoration: underline;
}

.account-menu-head {
    padding: 4px 12px 8px;
}

.account-menu-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--el-text-color-primary);
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.account-menu-role {
    margin-top: 2px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
}

.shell-drawer :deep(.el-drawer__body) {
    display: flex;
    flex-direction: column;
    padding: 0;
}

.shell-drawer__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--el-border-color-lighter);
    padding: 16px;
}

.shell-drawer__brand-title {
    font-size: 18px;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: var(--brand-primary);
}

.shell-drawer__section {
    border-bottom: 1px solid var(--el-border-color-lighter);
    padding: 12px 16px;
}

.shell-drawer__label {
    margin-bottom: 8px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--text-muted);
}

.shell-drawer__link {
    display: block;
    border-radius: var(--radius-control);
    padding: 9px 12px;
    font-size: 14px;
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.15s ease;
}

.shell-drawer__link:hover {
    background: var(--el-color-primary-light-9);
    color: var(--el-color-primary);
}

.shell-drawer__link.active {
    background: var(--el-color-primary-light-9);
    color: var(--el-color-primary);
    font-weight: 500;
}

.shell-drawer__footer {
    margin-top: auto;
    padding: 16px;
}

.shell-drawer__user {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.shell-drawer__user-name {
    font-size: 13px;
    font-weight: 500;
    color: var(--el-text-color-primary);
}

.shell-drawer__logout {
    width: 100%;
}

.shell-body {
    display: flex;
    flex: 1 1 auto;
    padding-top: 64px;
}

.shell-main {
    min-width: 0;
    flex: 1 1 auto;
    padding: 0.75rem;
}

@media (min-width: 640px) {
    .shell-main {
        padding: 1.25rem;
    }
}

@media (min-width: 1024px) {
    .shell-main {
        margin-left: 288px;
        padding: 1.5rem;
    }
}

.shell-main--flush {
    padding: 0 !important;
}

.shell-main__inner {
    min-width: 0;
}

.shell-main__inner--full {
    width: 100%;
    max-width: none;
}
</style>
