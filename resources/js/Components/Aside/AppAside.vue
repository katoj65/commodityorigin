<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Box,
    Calendar,
    Coffee,
    CoffeeCup,
    Compass,
    Document,
    Grid,
    House,
    MagicStick,
    Picture,
    School,
    ShoppingBag,
    Sunny,
    TrendCharts,
    Trophy,
    User,
    Wallet,
} from '@element-plus/icons-vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import { resolveIcon } from '@/utils/icon';

const page = usePage();

const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');
const subscribedAgents = computed(() => page.props.subscribedAgents ?? []);

// A function's `slug` holds the URL path it links to (e.g. "farm/create"),
// entered by the admin when the function was created/edited. Built off
// Ziggy's own base URL (the global `Ziggy.url`) since the app can be
// served from a subpath (e.g. http://localhost/commodityorigin).
const functionIsRoute = (fn) => !!fn.slug;
const functionHref = (fn) => (fn.slug ? `${Ziggy.url}/${fn.slug.replace(/^\/+/, '')}` : '#');
const onFunctionClick = (fn, event) => {
    if (!functionIsRoute(fn)) {
        event.preventDefault();
    }
};

const sideSections = computed(() => [
    {
        title: 'Main',
        items: [
            { label: 'Dashboard', href: route('dashboard'), active: route().current('dashboard'), icon: Grid },
        ],
    },
    {
        title: 'Marketplace',
        items: [
            { label: 'Browse Coffee', href: route('market.index'), active: route().current('market.index'), icon: Compass },
            { label: 'Live Market', href: route('market.active'), active: route().current('market.active'), icon: TrendCharts },
            { label: 'Auctions', href: route('auction.index'), active: route().current('auction.*'), icon: Trophy },
        ],
    },
    {
        title: 'Operations',
        items: [
            { label: 'My Orders', href: route('orders.index'), active: route().current('orders.*'), icon: ShoppingBag },
            { label: 'Farmers', href: route('farmer.index'), active: route().current('farmer.index'), icon: User },
            { label: 'Coffee Farms', href: route('farm.index'), active: route().current('farm.index'), icon: House },
            { label: 'My Farms', href: route('farm.mine'), active: route().current('farm.mine'), icon: House },
            { label: 'My Harvests', href: route('farm.harvest.mine'), active: route().current('farm.harvest.mine'), icon: Coffee },
            { label: 'Weather Forecast', href: route('farm.weather'), active: route().current('farm.weather'), icon: Sunny },
            { label: 'Cooperatives', href: route('cooperative.index'), active: route().current('cooperative.*'), icon: School },
            { label: 'Season', href: route('season.index'), active: route().current('season.*'), icon: Calendar },
            { label: 'All Lots', href: route('lot.index'), active: route().current('lot.index'), icon: CoffeeCup },
            { label: 'Harvests', href: route('harvest.index'), active: route().current('harvest.*'), icon: Coffee },
            { label: 'Batches', href: route('batch.index'), active: route().current('batch.*'), icon: Box },
        ],
    },
    {
        title: 'Financials',
        items: [
            { label: 'Wallet', href: route('wallet.index'), active: route().current('wallet.*'), icon: Wallet },
        ],
    },
    {
        title: 'Analysis',
        items: [
            { label: 'Documentation', href: route('documentation.index'), active: route().current('documentation.*'), icon: Document },
            { label: 'Gallery', href: route('gallery.index'), active: route().current('gallery.*'), icon: Picture },
            { label: 'AI Assistant', href: route('chat.index'), active: route().current('chat.*'), icon: MagicStick, tertiary: true },
        ],
    },
]);

function go(item) {
    router.visit(item.href);
}
</script>

<template>
    <aside class="app-aside">
        <Link :href="route('dashboard')" class="app-aside__brand">
            <span class="app-aside__mark-wrap">
                <ApplicationMark class="app-aside__mark" />
            </span>
            <span class="app-aside__brand-text">
                <span class="app-aside__title">Bean Origin</span>
                <span class="app-aside__tagline">Coffee Trade Platform</span>
            </span>
        </Link>

        <nav class="app-aside__nav">
            <template v-if="isAdmin">
                <div v-for="section in sideSections" :key="section.title">
                    <div class="app-aside__label">{{ section.title }}</div>

                    <div
                        v-for="item in section.items"
                        :key="`${section.title}-${item.label}`"
                        class="app-nav-item"
                        :class="{ 'app-nav-item--active': item.active, 'app-nav-item--tertiary': item.tertiary }"
                        @click="go(item)"
                    >
                        <el-icon :size="18"><component :is="item.icon" /></el-icon>
                        <span class="app-nav-item__label">{{ item.label }}</span>
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="app-aside__label">Main</div>
                <div
                    class="app-nav-item"
                    :class="{ 'app-nav-item--active': route().current('dashboard') }"
                    @click="router.visit(route('dashboard'))"
                >
                    <el-icon :size="18"><Grid /></el-icon>
                    <span class="app-nav-item__label">Dashboard</span>
                </div>

                <div class="app-aside__label">Marketplace</div>
                <div
                    class="app-nav-item"
                    :class="{ 'app-nav-item--active': route().current('market.*') }"
                    @click="router.visit(route('market.index'))"
                >
                    <el-icon :size="18"><Compass /></el-icon>
                    <span class="app-nav-item__label">Market</span>
                </div>

                <div class="app-aside__label">My Agents</div>

                <div v-for="agent in subscribedAgents" :key="agent.id" class="app-aside__agent">
                    <div class="app-nav-item app-nav-item--static">
                        <el-icon :size="18"><component :is="resolveIcon(agent.icon)" /></el-icon>
                        <span class="app-nav-item__label">{{ agent.name }}</span>
                    </div>

                    <div v-if="agent.functions?.length" class="app-aside__fn-list">
                        <component
                            :is="functionIsRoute(fn) ? Link : 'a'"
                            v-for="fn in agent.functions"
                            :key="fn.id"
                            :href="functionHref(fn)"
                            class="app-fn-item"
                            @click="onFunctionClick(fn, $event)"
                        >
                            <el-icon :size="15"><component :is="resolveIcon(fn.icon)" /></el-icon>
                            <span class="app-nav-item__label">{{ fn.name }}</span>
                        </component>
                    </div>
                </div>

                <el-empty
                    v-if="!subscribedAgents.length"
                    description="No agents subscribed yet"
                    :image-size="40"
                    class="app-aside__empty"
                >
                    <el-button size="small" type="primary" text @click="router.visit(route('apps.index'))">
                        Explore Apps
                    </el-button>
                </el-empty>

                <div class="app-aside__label">Financials</div>
                <div
                    class="app-nav-item"
                    :class="{ 'app-nav-item--active': route().current('wallet.*') }"
                    @click="router.visit(route('wallet.index'))"
                >
                    <el-icon :size="18"><Wallet /></el-icon>
                    <span class="app-nav-item__label">Wallet</span>
                </div>

                <div class="app-aside__label">Analysis</div>
                <div
                    class="app-nav-item app-nav-item--tertiary"
                    :class="{ 'app-nav-item--active': route().current('chat.*') }"
                    @click="router.visit(route('chat.index'))"
                >
                    <el-icon :size="18"><MagicStick /></el-icon>
                    <span class="app-nav-item__label">AI Assistant</span>
                </div>
            </template>
        </nav>

        <div class="app-aside__footer">
            <el-avatar :size="34" class="app-aside__avatar">
                {{ (user?.name ?? '').split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]?.toUpperCase()).join('') || 'CO' }}
            </el-avatar>
            <div class="app-aside__user">
                <div class="app-aside__user-name">{{ user?.name }}</div>
                <div class="app-aside__user-role">{{ user?.role || 'Account' }}</div>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.app-aside {
    position: fixed;
    left: 0;
    top: 0;
    z-index: 50;
    display: none;
    height: 100vh;
    width: 288px;
    flex-direction: column;
    overflow-x: hidden;
    overflow-y: auto;
    border-right: 1px solid var(--border-subtle);
    background: var(--surface-container-lowest);
    scrollbar-width: none;
}

.app-aside::-webkit-scrollbar {
    display: none;
}

@media (min-width: 1024px) {
    .app-aside {
        display: flex;
    }
}

.app-aside__brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 22px;
    height: 64px;
    flex-shrink: 0;
    text-decoration: none;
    border-bottom: 1px solid var(--border-subtle);
    transition: background 0.15s ease;
}

.app-aside__brand:focus,
.app-aside__brand:focus-visible,
.app-aside__brand:active {
    outline: none;
    background: transparent;
}

.app-aside__brand:hover .app-aside__mark-wrap {
    transform: scale(1.04);
}

.app-aside__mark-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 10px;
    background: var(--surface-container-lowest);
    box-shadow: 0 0 0 1px var(--border-subtle), 0 2px 6px rgba(13, 99, 27, 0.12);
    transition: transform 0.15s ease;
}

.app-aside__mark {
    height: 28px;
    width: 28px;
    flex-shrink: 0;
}

.app-aside__brand-text {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
}

.app-aside__title {
    font-size: 18px;
    font-weight: 700;
    line-height: 1.15;
    letter-spacing: -0.01em;
    color: var(--brand-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.app-aside__tagline {
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.app-aside__nav {
    flex: 1 1 auto;
    padding: 4px 16px 16px;
}

.app-aside__label {
    margin: 16px 0 4px 16px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
}

.app-aside__agent {
    margin-bottom: 4px;
}

.app-aside__empty {
    padding: 4px 0 8px;
}

.app-aside__empty :deep(.el-empty__description) {
    margin-top: 4px;
    font-size: 12px;
}

.app-aside__fn-list {
    display: flex;
    flex-direction: column;
    gap: 1px;
    margin-top: 1px;
}

.app-fn-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 16px 6px 20px;
    border-radius: var(--radius-control);
    font-size: 12px;
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.15s ease;
}

.app-fn-item:hover {
    background: var(--surface-container);
    color: var(--text-main);
}

.app-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: var(--radius-control);
    padding: 10px 16px;
    margin-bottom: 2px;
    font-size: 14px;
    color: var(--on-surface-variant);
    cursor: pointer;
    transition: all 0.15s ease;
}

.app-nav-item--static {
    cursor: default;
}

.app-nav-item:hover {
    background: var(--surface-container);
    color: var(--text-main);
}

.app-nav-item--active {
    background: var(--brand-primary-container);
    color: var(--on-primary-container);
    font-weight: 600;
}

.app-nav-item--tertiary {
    color: var(--brand-tertiary);
}

.app-nav-item--tertiary:hover {
    background: rgba(146, 51, 87, 0.08);
    color: var(--brand-tertiary);
}

.app-nav-item--tertiary.app-nav-item--active {
    background: var(--brand-tertiary);
    color: #fff;
}

.app-nav-item__label {
    flex: 1 1 auto;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.app-aside__footer {
    display: flex;
    align-items: center;
    gap: 10px;
    border-top: 1px solid var(--border-subtle);
    padding: 12px 16px;
    flex-shrink: 0;
}

.app-aside__avatar {
    flex-shrink: 0;
    background: var(--brand-primary);
    font-size: 12px;
    font-weight: 700;
}

.app-aside__user {
    min-width: 0;
}

.app-aside__user-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-main);
}

.app-aside__user-role {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 11px;
    text-transform: capitalize;
    color: var(--text-muted);
}
</style>
