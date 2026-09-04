<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Bell, Box, Calendar, Close, CoffeeCup, Coin, Compass, Document, FirstAidKit, Grid,
    House, MagicStick, Menu, Message, Odometer, Picture, Postcard, School, Search, Sell, Setting, Shop,
    ShoppingBag, ShoppingCart, Sunny, SwitchButton, Tickets, TrendCharts, Trophy, User, Wallet,
} from '@element-plus/icons-vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import { resolveIcon } from '@/utils/icon';

defineProps({
    title: { type: String, default: 'Bean Origin' },
});

const mobileNavOpen = ref(false);

// Real shared-prop data (same source AppLayout.vue reads), not mock counts —
// injected into every Inertia response by HandleInertiaRequests.php.
const page = usePage();
const unreadNotificationsCount = computed(() => page.props.unreadNotificationsCount ?? 0);
const recentNotifications = computed(() => page.props.recentNotifications ?? []);
const cartActiveCount = computed(() => page.props.cartActiveCount ?? 0);

// Mirrors AppAside.vue's role-gated nav exactly (same section labels,
// link labels, icons, and admin/non-admin branching) so this preview
// reads as the same product — wired to the same real routes and the
// same real `auth.user.role` / `subscribedAgents` shared props.
const user = computed(() => page.props.auth?.user ?? null);
const isAdmin = computed(() => user.value?.role === 'admin');
const subscribedAgents = computed(() => page.props.subscribedAgents ?? []);

function functionIsRoute(fn) {
    return !!fn.slug;
}

// Built off Ziggy's own base URL (not a relative path) since the app can
// be served from a subpath — matches AppAside.vue's exact helper.
function functionHref(fn) {
    return fn.slug ? `${Ziggy.url}/${fn.slug.replace(/^\/+/, '')}` : '#';
}

const adminNavSections = computed(() => [
    {
        label: 'Main',
        items: [
            { index: 'dashboard', label: 'Dashboard', icon: Grid, href: route('dashboard'), active: route().current('dashboard') },
        ],
    },
    {
        label: 'Marketplace',
        items: [
            { index: 'market-browse', label: 'Browse Coffee', icon: Compass, href: route('market.index'), active: route().current('market.index') },
            { index: 'market-live', label: 'Live Market', icon: TrendCharts, href: route('market.active'), active: route().current('market.active') },
            { index: 'trade', label: 'Trade', icon: Sell, href: route('trade.index'), active: route().current('trade.*') },
            { index: 'auctions', label: 'Auctions', icon: Trophy, href: route('auction.index'), active: route().current('auction.*') },
            { index: 'store', label: 'My Store', icon: Shop, href: route('store.show'), active: route().current('store.*') },
        ],
    },
    {
        label: 'Operations',
        items: [
            { index: 'calendar', label: 'Calendar', icon: Calendar, href: route('calendar.index'), active: route().current('calendar.*') },
            { index: 'contacts', label: 'Contacts', icon: Postcard, href: route('contact.index'), active: route().current('contact.*') },
            { index: 'orders', label: 'My Orders', icon: ShoppingBag, href: route('orders.index'), active: route().current('orders.*') },
            { index: 'farmers', label: 'Farmers', icon: User, href: route('farmer.index'), active: route().current('farmer.index') },
            { index: 'farms', label: 'Coffee Farms', icon: House, href: route('farm.index'), active: route().current('farm.index') },
            { index: 'my-farms', label: 'My Farms', icon: House, href: route('farm.mine'), active: route().current('farm.mine') },
            { index: 'inputs', label: 'Agricultural Inputs', icon: FirstAidKit, href: route('farm.inputs.index'), active: route().current('farm.inputs.*') },
            { index: 'weather', label: 'Weather Forecast', icon: Sunny, href: route('farm.weather'), active: route().current('farm.weather') },
            { index: 'cooperatives', label: 'Cooperatives', icon: School, href: route('cooperative.index'), active: route().current('cooperative.*') },
            { index: 'season', label: 'Season', icon: Calendar, href: route('season.index'), active: route().current('season.*') },
            { index: 'lots', label: 'All Lots', icon: CoffeeCup, href: route('lot.index'), active: route().current('lot.index') },
            { index: 'batches', label: 'Batches', icon: Box, href: route('batch.index'), active: route().current('batch.*') },
        ],
    },
    {
        label: 'Financials',
        items: [
            { index: 'purchases', label: 'Purchases', icon: Tickets, href: route('purchases.index'), active: route().current('purchases.*') },
            { index: 'wallet', label: 'Wallet', icon: Wallet, href: route('wallet.index'), active: route().current('wallet.*') },
            { index: 'currencies', label: 'Currencies', icon: Coin, href: route('currencies.index'), active: route().current('currencies.*') },
        ],
    },
    {
        label: 'Analysis',
        items: [
            { index: 'documentation', label: 'Documentation', icon: Document, href: route('documentation.index'), active: route().current('documentation.*') },
            { index: 'gallery', label: 'Gallery', icon: Picture, href: route('gallery.index'), active: route().current('gallery.*') },
            { index: 'chat', label: 'AI Assistant', icon: MagicStick, tertiary: true, href: route('chat.index'), active: route().current('chat.*') },
        ],
    },
]);

const nonAdminNavSections = computed(() => {
    const sections = [
        {
            label: 'Main',
            items: [
                { index: 'dashboard', label: 'Dashboard', icon: Grid, href: route('dashboard'), active: route().current('dashboard') },
            ],
        },
        {
            label: 'Marketplace',
            items: [
                { index: 'market', label: 'Market', icon: Compass, href: route('market.index'), active: route().current('market.*') },
                { index: 'trade', label: 'Trade', icon: Sell, href: route('trade.index'), active: route().current('trade.*') },
                { index: 'store', label: 'My Store', icon: Shop, href: route('store.show'), active: route().current('store.*') },
                { index: 'calendar', label: 'Calendar', icon: Calendar, href: route('calendar.index'), active: route().current('calendar.*') },
                { index: 'contacts', label: 'Contacts', icon: Postcard, href: route('contact.index'), active: route().current('contact.*') },
            ],
        },
    ];

    if (subscribedAgents.value.length) {
        sections.push({ label: 'My Agents', agents: subscribedAgents.value });
    }

    sections.push({
        label: 'Financials',
        items: [
            { index: 'purchases', label: 'Purchases', icon: Tickets, href: route('purchases.index'), active: route().current('purchases.*') },
            { index: 'wallet', label: 'Wallet', icon: Wallet, href: route('wallet.index'), active: route().current('wallet.*') },
        ],
    });

    sections.push({
        label: 'Analysis',
        items: [
            { index: 'chat', label: 'AI Assistant', icon: MagicStick, tertiary: true, href: route('chat.index'), active: route().current('chat.*') },
        ],
    });

    return sections;
});

const navSections = computed(() => (isAdmin.value ? adminNavSections.value : nonAdminNavSections.value));

const activeNavIndex = computed(() => {
    for (const section of navSections.value) {
        if (!section.items) continue;
        const match = section.items.find((item) => item.active);
        if (match) return match.index;
    }
    return '';
});

const accountMenuItems = [
    { label: 'Profile Settings', icon: User, href: route('profile.show') },
    { label: 'Overview', icon: Odometer, href: route('dashboard') },
    { label: 'Apps', icon: Grid, href: route('apps.index') },
    { label: 'Settings', icon: Setting, href: route('settings.index') },
    { label: 'Documentation', icon: Document, href: route('documentation.index') },
];

function signOut() {
    router.post(route('logout'));
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

// ── Header search — ported from AppLayout.vue's global search exactly
// (same debounced /search/suggest endpoint, same keyboard nav, same
// submit-to-results-page behavior) so this preview's search behaves
// identically to the real app shell, just skinned in dp-* classes.
const searchQuery = ref('');
const suggestions = ref([]);
const suggestOpen = ref(false);
const suggestLoading = ref(false);
const activeSuggestIndex = ref(-1);
let suggestTimer = null;
let suggestRequestId = 0;

function closeSuggestions() {
    suggestOpen.value = false;
    activeSuggestIndex.value = -1;
}

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

function onSearchFocus() {
    if (searchQuery.value.trim() && suggestions.value.length) {
        suggestOpen.value = true;
    }
}

function onSearchBlur() {
    setTimeout(closeSuggestions, 120);
}

function onSearchKeydown(e) {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!suggestions.value.length) return;
        activeSuggestIndex.value = (activeSuggestIndex.value + 1) % suggestions.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!suggestions.value.length) return;
        activeSuggestIndex.value = (activeSuggestIndex.value - 1 + suggestions.value.length) % suggestions.value.length;
    } else if (e.key === 'Enter') {
        if (activeSuggestIndex.value >= 0 && suggestions.value[activeSuggestIndex.value]) {
            goToSuggestion(suggestions.value[activeSuggestIndex.value]);
        } else {
            submitSearch();
        }
    } else if (e.key === 'Escape') {
        closeSuggestions();
    }
}
</script>

<template>
    <div class="dp-shell">
        <Head :title="title" />

        <!-- ── Sidebar ──────────────────────────────────────────────────── -->
        <div v-if="mobileNavOpen" class="dp-scrim" @click="mobileNavOpen = false" />

        <aside class="dp-aside" :class="{ 'dp-aside--open': mobileNavOpen }">
            <div class="dp-aside__brand">
                <span class="dp-aside__mark-wrap">
                    <ApplicationMark class="dp-aside__mark" />
                </span>
                <span class="dp-aside__brand-text">
                    <span class="dp-aside__title">Bean Origin</span>
                    <span class="dp-aside__tagline">Coffee Trade Platform</span>
                </span>
                <el-button text circle class="dp-aside__close" @click="mobileNavOpen = false">
                    <el-icon :size="18"><Close /></el-icon>
                </el-button>
            </div>

            <el-menu :default-active="activeNavIndex" class="dp-nav" @select="mobileNavOpen = false">
                <template v-for="section in navSections" :key="section.label">
                    <div class="dp-nav__label">{{ section.label }}</div>

                    <template v-if="section.items">
                        <el-menu-item
                            v-for="item in section.items"
                            :key="item.index"
                            :index="item.index"
                            class="dp-nav__item"
                            :class="{ 'dp-nav__item--tertiary': item.tertiary }"
                        >
                            <Link :href="item.href" class="dp-nav__link">
                                <span class="dp-nav__icon"><el-icon :size="16"><component :is="item.icon" /></el-icon></span>
                                <span>{{ item.label }}</span>
                            </Link>
                        </el-menu-item>
                    </template>

                    <template v-else-if="section.agents">
                        <div v-for="agent in section.agents" :key="agent.id" class="dp-nav__agent">
                            <div class="dp-nav__item dp-nav__item--static">
                                <span class="dp-nav__icon"><el-icon :size="16"><component :is="resolveIcon(agent.icon)" /></el-icon></span>
                                <span>{{ agent.name }}</span>
                            </div>
                            <div v-if="agent.functions?.length" class="dp-nav__fn-list">
                                <component
                                    :is="functionIsRoute(fn) ? Link : 'span'"
                                    v-for="fn in agent.functions"
                                    :key="fn.id"
                                    :href="functionIsRoute(fn) ? functionHref(fn) : undefined"
                                    class="dp-nav__fn-item"
                                >
                                    <el-icon :size="14"><component :is="resolveIcon(fn.icon)" /></el-icon>
                                    <span>{{ fn.name }}</span>
                                </component>
                            </div>
                        </div>
                    </template>
                </template>
            </el-menu>

            <div class="dp-aside__footer">
                <span class="dp-aside__avatar-wrap">
                    <el-avatar :size="34" class="dp-avatar">{{ (user?.name ?? '').split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]?.toUpperCase()).join('') || 'CO' }}</el-avatar>
                    <span class="dp-aside__status" />
                </span>
                <div class="dp-aside__user">
                    <p class="dp-aside__user-name">{{ user?.name || 'Account' }}</p>
                    <p class="dp-aside__user-role">{{ user?.role || 'Bean Origin' }}</p>
                </div>
            </div>
        </aside>

        <div class="dp-content">
            <!-- ── Header ───────────────────────────────────────────────── -->
            <header class="dp-header">
                <el-button text circle class="dp-menu-btn" @click="mobileNavOpen = true">
                    <el-icon :size="20"><Menu /></el-icon>
                </el-button>

                <div class="dp-header__search">
                    <div class="dp-search">
                        <el-input
                            v-model="searchQuery"
                            placeholder="Search subscribers, products..."
                            :prefix-icon="Search"
                            class="dp-search-input"
                            @focus="onSearchFocus"
                            @blur="onSearchBlur"
                            @keydown="onSearchKeydown"
                        />

                        <transition name="dp-search-fade">
                            <div v-if="suggestOpen" class="dp-search__panel">
                                <div v-if="suggestLoading" class="dp-search__loading">Searching…</div>
                                <template v-else>
                                    <button
                                        v-for="(item, index) in suggestions"
                                        :key="item.id"
                                        type="button"
                                        class="dp-search__item"
                                        :class="{ 'dp-search__item--active': index === activeSuggestIndex }"
                                        @mousedown.prevent="goToSuggestion(item)"
                                        @mouseenter="activeSuggestIndex = index"
                                    >
                                        <span class="dp-search__item-icon"><el-icon :size="15"><Box /></el-icon></span>
                                        <span class="dp-search__item-body">
                                            <span class="dp-search__item-name">{{ item.name }}</span>
                                            <span class="dp-search__item-meta">{{ [item.origin, item.type, item.lot_code].filter(Boolean).join(' · ') }}</span>
                                        </span>
                                        <span class="dp-search__item-price">${{ Number(item.price_per_kg).toFixed(2) }}<small>/kg</small></span>
                                    </button>

                                    <div v-if="!suggestions.length" class="dp-search__empty">No market items match "{{ searchQuery }}"</div>

                                    <button type="button" class="dp-search__footer" @mousedown.prevent="submitSearch">
                                        See all results for "{{ searchQuery }}"
                                    </button>
                                </template>
                            </div>
                        </transition>
                    </div>
                </div>

                <div class="dp-header__actions">
                    <el-button class="dp-ask-ai-btn" round @click="router.visit(route('chat.index'))">
                        <el-icon :size="16"><MagicStick /></el-icon>
                        <span class="dp-ask-ai-btn__label">Ask AI</span>
                    </el-button>
                    <el-popover placement="bottom-end" :width="320" trigger="click" popper-class="dp-notif-popover">
                        <template #reference>
                            <el-badge :value="unreadNotificationsCount" :hidden="unreadNotificationsCount === 0" :max="99" class="dp-icon-badge">
                                <el-button text circle class="dp-icon-btn">
                                    <el-icon :size="20"><Bell /></el-icon>
                                </el-button>
                            </el-badge>
                        </template>

                        <div class="dp-notif-menu__head">
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

                        <el-scrollbar max-height="352px" class="dp-notif-menu__list">
                            <button
                                v-for="notification in recentNotifications"
                                :key="notification.id"
                                type="button"
                                class="dp-notif-menu__item"
                                :class="{ 'dp-notif-menu__item--unread': !notification.is_read }"
                                @click="openNotification(notification)"
                            >
                                <span class="dp-notif-menu__dot" :class="{ 'dp-notif-menu__dot--on': !notification.is_read }" />
                                <span class="dp-notif-menu__body">
                                    <span class="dp-notif-menu__title">{{ notification.title }}</span>
                                    <span v-if="notification.body" class="dp-notif-menu__text">{{ notification.body }}</span>
                                    <span class="dp-notif-menu__time">{{ notificationTimeAgo(notification.created_at) }}</span>
                                </span>
                            </button>

                            <el-empty
                                v-if="recentNotifications.length === 0"
                                description="You're all caught up"
                                :image-size="44"
                            />
                        </el-scrollbar>

                        <Link :href="route('notifications.index')" class="dp-notif-menu__footer">
                            View all notifications
                        </Link>
                    </el-popover>

                    <el-tooltip content="Messages" placement="bottom" :show-after="200">
                        <el-button text circle class="dp-icon-btn" @click="router.visit(route('chat.index'))">
                            <el-icon :size="20"><Message /></el-icon>
                        </el-button>
                    </el-tooltip>

                    <el-tooltip content="Cart" placement="bottom" :show-after="200">
                        <el-badge :value="cartActiveCount" :hidden="cartActiveCount === 0" :max="99" class="dp-icon-badge">
                            <el-button text circle class="dp-icon-btn" @click="router.visit(route('checkout.index'))">
                                <el-icon :size="20"><ShoppingCart /></el-icon>
                            </el-button>
                        </el-badge>
                    </el-tooltip>
                    <el-dropdown trigger="click" placement="bottom-end" popper-class="dp-account-menu">
                        <div class="dp-account">
                            <el-avatar :size="32" class="dp-avatar"><el-icon><User /></el-icon></el-avatar>
                        </div>

                        <template #dropdown>
                            <div class="dp-account-menu__head">
                                <p class="dp-label-md">Roaster Admin</p>
                                <p class="dp-caption">Bean Origin</p>
                            </div>
                            <el-dropdown-menu>
                                <el-dropdown-item
                                    v-for="item in accountMenuItems"
                                    :key="item.label"
                                    :icon="item.icon"
                                    @click="router.visit(item.href)"
                                >
                                    {{ item.label }}
                                </el-dropdown-item>
                                <el-dropdown-item divided :icon="SwitchButton" @click="signOut">Sign Out</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </header>

            <main class="dp-main">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
/* ── Design tokens — scoped to this preview only via CSS custom
   properties, not tailwind.config.js: this app's shared config
   already defines an old dark-theme palette (bg, surface, green,
   gold, cream…) still consumed by Login/Register/OuterLayout, and
   several of this mockup's token NAMES collide (e.g. "surface") with
   different hex values there. Plain scoped CSS also sidesteps a real
   bug found while building this page: the local Vite dev server
   double-escapes parentheses inside Tailwind arbitrary values (e.g.
   bg-[var(--x)]), silently producing invalid selectors — using real
   CSS var() in a normal stylesheet has no such issue. ─────────────── */
.dp-shell {
    /* UI.md theme (2026-08-24): app-wide default, superseding the
       earlier Claude Console pass. See reference_ui_md_design_system
       memory for the full spec. */
    --dp-surface: #ffffff;
    --dp-surface-container-lowest: #ffffff;
    --dp-surface-container-low: #F5F6F7;
    --dp-surface-container: #F1F2F3;
    --dp-surface-container-high: #E5E7EB;
    --dp-surface-container-highest: #D9DCDD;
    --dp-on-surface: #121516;
    --dp-on-surface-variant: #4B5457;
    --dp-outline: #6F7677;
    --dp-outline-variant: #E5E7EB;
    --dp-surface-tint: #000000;
    --dp-primary: #000000;
    --dp-on-primary: #ffffff;
    --dp-primary-container: #262626;
    --dp-on-primary-container: #F1F2F3;
    --dp-primary-fixed: #F1F2F3;
    --dp-on-primary-fixed: #121516;
    --dp-secondary: #7EE787;
    --dp-secondary-container: #E5FAE7;
    --dp-on-secondary-container: #2F6B35;
    --dp-secondary-fixed: #A4EEAA;
    --dp-on-secondary-fixed: #12310F;
    --dp-tertiary-fixed: #F1F2F3;
    --dp-on-tertiary-fixed: #121516;
    --dp-error: #F85149;
    --dp-on-error: #ffffff;
    --dp-on-error-container: #C6413A;
    --dp-error-container: #FEEDED;
    --dp-border-subtle: #E5E7EB;

    --dp-font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    --dp-font-mono: 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace;

    /* Shared "content card" tokens — one fixed radius/shadow for every
       bordered white card across pages built on this layout. Reference
       these instead of hardcoding new values. */
    --dp-card-radius: 6px;
    --dp-card-shadow: none;

    display: flex;
    min-height: 100vh;
    background: var(--dp-surface);
    font-family: var(--dp-font-sans);
    color: var(--dp-on-surface);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}

/* ── Typography tokens — one professional system sans-serif stack
   (dp-font-sans) instead of a display serif or a Google Fonts webfont,
   which reads as editorial/magazine rather than business software and
   carries a network-reliability risk. Numeric values use dp-mono
   separately, matching this app's convention of monospacing
   prices/stats for tabular alignment. ───────────────────────────────── */
.dp-shell .dp-display-lg { font-size: 26px; line-height: 33px; letter-spacing: -0.012em; font-weight: 800; margin: 0; }
/* Page-title size synced to MarketListings.vue's .mktl-topbar__title, the
   app's default page-header pattern (2026-08-24). */
.dp-shell .dp-display-md { font-size: 1.5rem; line-height: 1.9rem; letter-spacing: -0.015em; font-weight: 800; margin: 0 0 6px; }
.dp-shell .dp-headline-md { font-size: 17px; line-height: 25px; letter-spacing: -0.006em; font-weight: 700; margin: 0; }
.dp-shell .dp-headline-sm { font-size: 14px; line-height: 21px; letter-spacing: -0.003em; font-weight: 700; margin: 0; }
.dp-shell .dp-body-lg { font-size: 13.5px; line-height: 21px; font-weight: 500; letter-spacing: 0.001em; margin: 0; }
.dp-shell .dp-body-md { font-size: 13px; line-height: 20px; font-weight: 500; letter-spacing: 0.002em; margin: 0; }
.dp-shell .dp-label-md { font-size: 11.5px; line-height: 16px; letter-spacing: 0.05em; font-weight: 700; margin: 0; }
.dp-shell .dp-caption { font-size: 11px; line-height: 16px; font-weight: 600; letter-spacing: 0.008em; margin: 0; }
.dp-shell .dp-mono { font-family: var(--dp-font-mono); font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }

/* ── Sidebar ──────────────────────────────────────────────────────────── */
.dp-aside {
    width: 288px;
    flex-shrink: 0;
    background: #121611;
    border-right: 1px solid rgba(0, 0, 0, 0.12);
    display: flex;
    flex-direction: column;
    padding-top: 28px;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

/* ── Brand — mirrors the real AppAside.vue brand block (same logo mark,
   title, and tagline) so this preview reads as the same product. ──── */
.dp-aside__brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 20px;
    margin-bottom: 36px;
}
.dp-aside__close.el-button { display: none; margin-left: auto; flex-shrink: 0; color: rgba(255, 255, 255, 0.75); }
.dp-aside__mark-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 10px;
    background: var(--dp-surface-container-lowest);
    box-shadow: 0 2px 6px rgba(39, 19, 16, 0.1);
    overflow: hidden;
}
.dp-aside__mark { height: 30px; width: 30px; flex-shrink: 0; }
.dp-aside__brand-text { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
.dp-aside__title {
    font-size: 19px;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.01em;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dp-aside__tagline {
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.65);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dp-nav__label {
    padding: 0 12px;
    margin: 20px 0 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.55);
}
.dp-nav__label:first-child { margin-top: 0; }

.dp-nav.el-menu {
    flex: 1;
    border-right: none;
    background: transparent;
    padding: 0 16px;
}
.dp-nav .dp-nav__item.el-menu-item {
    height: auto;
    line-height: normal;
    padding: 0 !important;
    margin-bottom: 4px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: rgba(255, 255, 255, 0.75);
    transition: all 0.2s ease;
}
.dp-nav__link {
    display: flex;
    align-items: center;
    width: 100%;
    height: 100%;
    padding: 10px 12px;
    gap: 12px;
    color: inherit;
    text-decoration: none;
}
.dp-nav .dp-nav__item.el-menu-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}
.dp-nav .dp-nav__item.el-menu-item.is-active {
    background: #ffffff;
    color: #121611;
    font-weight: 700;
}
.dp-nav .dp-nav__item .el-icon { color: inherit; }

/* Agent header row + its function links — plain divs (not el-menu-item),
   since a dynamic function list doesn't fit el-menu's fixed-index model.
   Mirrors AppAside.vue's .app-nav-item--static / .app-fn-item. */
.dp-nav__agent { margin-bottom: 2px; }
.dp-nav__item--static {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    margin-bottom: 2px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: rgba(255, 255, 255, 0.75);
    cursor: default;
}
.dp-nav__fn-list {
    display: flex;
    flex-direction: column;
    gap: 1px;
    margin: 1px 0 4px;
}
.dp-nav__fn-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px 6px 18px;
    border-radius: 8px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.55);
    text-decoration: none;
    transition: all 0.15s ease;
}
.dp-nav__fn-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.dp-nav__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border-radius: 8px;
    background: transparent;
    color: inherit;
    transition: background 0.2s ease, color 0.2s ease;
}
.dp-nav .dp-nav__item.el-menu-item:hover .dp-nav__icon {
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
}
.dp-nav .dp-nav__item.el-menu-item.is-active .dp-nav__icon {
    background: rgba(13, 99, 27, 0.1);
    color: #121611;
}

/* AI Assistant — matches AppAside.vue's tertiary nav-item treatment
   (literal hex: this brand-tertiary plum isn't among the dp-* tokens). */
.dp-nav .dp-nav__item--tertiary.el-menu-item { color: #923357; }
.dp-nav .dp-nav__item--tertiary.el-menu-item:hover { background: rgba(146, 51, 87, 0.08); color: #923357; }
.dp-nav .dp-nav__item--tertiary.el-menu-item:hover .dp-nav__icon { background: rgba(146, 51, 87, 0.14); color: #923357; }
.dp-nav .dp-nav__item--tertiary.el-menu-item.is-active { background: #923357; color: #ffffff; }
.dp-nav .dp-nav__item--tertiary.el-menu-item.is-active .dp-nav__icon { background: rgba(255, 255, 255, 0.2); color: #ffffff; }

.dp-aside__footer {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 24px 20px 0;
    padding: 12px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.08);
}
.dp-aside__avatar-wrap { position: relative; flex-shrink: 0; }
.dp-aside__status {
    position: absolute;
    right: -1px;
    bottom: -1px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #6fe08a;
    border: 2px solid #121611;
}
.dp-aside__user { min-width: 0; overflow: hidden; }
.dp-aside__user-name { font-size: 14px; font-weight: 600; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }
.dp-aside__user-role { font-size: 12px; color: rgba(255, 255, 255, 0.65); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }

/* ── Content column ───────────────────────────────────────────────────── */
.dp-content { flex: 1; min-width: 0; }

.dp-header {
    position: sticky;
    top: 0;
    z-index: 10;
    height: 80px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 24px;
    background: var(--dp-surface-container-lowest);
    box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
}
.dp-menu-btn.el-button { display: none; flex-shrink: 0; color: var(--dp-on-surface-variant); font-size: 20px; }
.dp-header__search { flex: 1; display: flex; justify-content: center; min-width: 0; }
.dp-search { position: relative; max-width: 576px; width: 100%; }
.dp-search-input { width: 100%; }
.dp-search-input .el-input__wrapper {
    background: var(--dp-surface-container-low);
    border-radius: 10px;
    box-shadow: none;
    height: 44px;
    padding-left: 16px;
}
.dp-search-input .el-input__wrapper.is-focus { box-shadow: 0 0 0 2px var(--dp-primary-container) inset; }
.dp-search-input :deep(.el-input__inner) { font-size: 14px; }

/* ── Search suggestions dropdown — functionally identical to
   AppLayout.vue's global search (same /search/suggest endpoint, same
   keyboard nav and submit behavior), skinned with dp-* tokens since
   this panel isn't teleported and can use them directly. ──────────── */
.dp-search__panel {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: var(--dp-surface-container-lowest);
    border-radius: 6px;
    box-shadow: 0 16px 40px rgba(39, 19, 16, 0.16);
    border: 1px solid var(--dp-border-subtle);
    max-height: 400px;
    overflow-y: auto;
    z-index: 40;
    padding: 6px;
}
.dp-search__loading,
.dp-search__empty {
    padding: 16px 12px;
    text-align: center;
    font-size: 13px;
    color: var(--dp-on-surface-variant);
}
.dp-search__item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 8px 10px;
    border: none;
    background: none;
    border-radius: 9px;
    cursor: pointer;
    text-align: left;
    transition: background 0.12s ease;
}
.dp-search__item:hover,
.dp-search__item--active {
    background: var(--dp-surface-container-high);
}
.dp-search__item-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border-radius: 8px;
    background: var(--dp-surface-container-low);
    color: var(--dp-on-surface-variant);
}
.dp-search__item-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.dp-search__item-name { font-size: 13px; font-weight: 700; color: var(--dp-on-surface); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-search__item-meta { font-size: 11.5px; color: var(--dp-on-surface-variant); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-search__item-price { flex-shrink: 0; font-family: var(--dp-font-mono); font-size: 13px; font-weight: 700; color: var(--dp-primary); }
.dp-search__item-price small { font-family: var(--dp-font-sans); font-size: 10px; font-weight: 600; color: var(--dp-on-surface-variant); margin-left: 2px; }
.dp-search__footer {
    display: block;
    width: 100%;
    padding: 10px;
    margin-top: 4px;
    border: none;
    border-top: 1px solid var(--dp-border-subtle);
    background: none;
    border-radius: 0 0 9px 9px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--dp-primary);
    cursor: pointer;
    text-align: center;
    transition: background 0.12s ease;
}
.dp-search__footer:hover { background: var(--dp-surface-container-low); }

.dp-search-fade-enter-active,
.dp-search-fade-leave-active { transition: opacity 0.12s ease, transform 0.12s ease; }
.dp-search-fade-enter-from,
.dp-search-fade-leave-to { opacity: 0; transform: translateY(-4px); }

.dp-header__actions { display: flex; align-items: center; gap: 20px; margin-left: auto; padding-left: 24px; }

.dp-ask-ai-btn.el-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 40px;
    padding: 0 18px;
    margin-right: 4px;
    border: none;
    background: var(--dp-primary);
    color: var(--dp-on-primary);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.02em;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.dp-ask-ai-btn.el-button:hover {
    background: var(--dp-primary);
    color: var(--dp-on-primary);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(39, 19, 16, 0.24);
}
.dp-ask-ai-btn.el-button .dp-ask-ai-btn__label { font-family: var(--dp-font-sans); }
.dp-icon-btn { color: var(--dp-on-surface-variant); }
.dp-icon-btn:hover { color: var(--dp-on-surface); background: var(--dp-surface-container); }
.dp-icon-badge :deep(.el-badge__content) { background: var(--dp-error); border: 1.5px solid var(--dp-surface); font-family: var(--dp-font-mono); font-size: 10px; height: 16px; line-height: 16px; padding: 0 4px; }

.dp-avatar { background: var(--dp-primary); color: var(--dp-on-primary); flex-shrink: 0; }

.dp-account { display: flex; align-items: center; cursor: pointer; border-radius: 999px; transition: box-shadow 0.15s ease; }
.dp-account:hover { box-shadow: 0 0 0 3px #eeeeee; }

/* The dropdown popper teleports to <body>, outside .dp-shell, so its
   --dp-* custom properties (defined on .dp-shell) don't cascade in —
   literal hex from the same palette is used here instead. */
.dp-account-menu.el-dropdown__popper {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    border-radius: 10px;
    border: 1px solid #e2e2e2;
    box-shadow: 0 12px 32px rgba(39, 19, 16, 0.16);
    min-width: 220px;
}
.dp-account-menu .el-dropdown-menu { padding: 6px; }
.dp-account-menu__head { padding: 12px 14px 10px; border-bottom: 1px solid #eeeeee; margin-bottom: 4px; }
.dp-account-menu__head p:first-child { color: #1a1c1c; margin: 0 0 2px; }
.dp-account-menu__head p:last-child { color: #827472; margin: 0; }
.dp-account-menu .el-dropdown-menu__item {
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #504442;
    padding: 9px 10px;
    gap: 10px;
}
.dp-account-menu .el-dropdown-menu__item:hover { background: #f3f3f3; color: #1a1c1c; }
.dp-account-menu .el-dropdown-menu__item.is-divided { border-top-color: #eeeeee; }
.dp-account-menu .el-dropdown-menu__item.is-divided:hover { color: #ba1a1a; background: #ffdad6; }

/* Notifications popover — same teleport-to-<body> caveat as the account
   menu above, so literal hex from the dp palette is used instead of
   var(--dp-*). Structure/behavior mirrors AppLayout.vue's notif-menu
   exactly (real unread state, mark-all-read, per-item read+navigate). */
.dp-notif-popover.el-popover.el-popper {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    border-radius: 10px;
    border: 1px solid #e2e2e2;
    box-shadow: 0 12px 32px rgba(39, 19, 16, 0.16);
    padding: 12px;
}
.dp-notif-menu__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding-bottom: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #1a1c1c;
}
.dp-notif-menu__list { border-top: 1px solid #eeeeee; }
.dp-notif-menu__item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
    border: 0;
    border-bottom: 1px solid #eeeeee;
    background: transparent;
    padding: 10px 0;
    text-align: left;
    cursor: pointer;
    transition: background 0.15s ease;
}
.dp-notif-menu__item:hover { background: #f3f3f3; }
.dp-notif-menu__item--unread { background: rgba(39, 19, 16, 0.05); }
.dp-notif-menu__item--unread:hover { background: rgba(39, 19, 16, 0.09); }
.dp-notif-menu__dot {
    flex-shrink: 0;
    width: 6px;
    height: 6px;
    margin-top: 6px;
    border-radius: 999px;
    background: transparent;
}
.dp-notif-menu__dot--on { background: #121611; }
.dp-notif-menu__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.dp-notif-menu__title { font-size: 13px; font-weight: 600; color: #1a1c1c; line-height: 1.4; }
.dp-notif-menu__text {
    font-size: 12px;
    color: #504442;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.dp-notif-menu__time { font-size: 11px; color: #827472; margin-top: 1px; }
.dp-notif-menu__footer {
    display: block;
    margin-top: 4px;
    padding-top: 10px;
    border-top: 1px solid #eeeeee;
    font-size: 12px;
    font-weight: 600;
    color: #121611;
    text-align: center;
    text-decoration: none;
}
.dp-notif-menu__footer:hover { text-decoration: underline; }

.dp-main { padding: 48px 64px; display: flex; flex-direction: column; gap: 32px; }

.dp-scrim {
    position: fixed;
    inset: 0;
    z-index: 35;
    background: rgba(26, 20, 18, 0.45);
    animation: dp-scrim-in 0.2s ease;
}
@keyframes dp-scrim-in { from { opacity: 0; } to { opacity: 1; } }

/* ── Tablet & mobile — sidebar becomes an off-canvas drawer instead of
   disappearing entirely, so nav stays reachable below the desktop
   breakpoint. Opened via the header's hamburger button. ────────────── */
@media (max-width: 1279.98px) {
    .dp-aside {
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 40;
        height: 100dvh;
        transform: translateX(-100%);
        box-shadow: 0 16px 40px rgba(39, 19, 16, 0.24);
        transition: transform 0.25s ease;
    }
    .dp-aside--open { transform: translateX(0); }
    .dp-aside__close.el-button { display: inline-flex; }
    .dp-menu-btn.el-button { display: inline-flex; }
    .dp-header { padding: 0 24px; }
    .dp-main { padding: 32px 24px; gap: 24px; }
}

@media (max-width: 767.98px) {
    .dp-header { height: 68px; padding: 0 16px; gap: 8px; }
    .dp-header__search { justify-content: flex-start; }
    .dp-search-input .el-input__wrapper { height: 40px; padding-left: 12px; }
    .dp-ask-ai-btn.el-button .dp-ask-ai-btn__label { display: none; }
    .dp-ask-ai-btn.el-button { padding: 0; width: 40px; justify-content: center; gap: 0; }
    .dp-header__actions { gap: 10px; padding-left: 0; }
    .dp-main { padding: 24px 16px; gap: 20px; }
    .dp-aside { width: 84vw; max-width: 320px; }
}

@media (max-width: 479.98px) {
    .dp-header { padding: 0 12px; }
    .dp-header__search { display: none; }
    .dp-main { padding: 20px 12px; gap: 16px; }
}
</style>
