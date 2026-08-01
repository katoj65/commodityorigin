<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import WalletNavLink from '@/Components/WalletNavLink.vue';

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
const userInitials = computed(() => {
    const name = user.value?.name ?? '';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('') || 'CO';
});

const sideSections = computed(() => [
    {
        title: 'Workspace',
        items: [
            {
                label: 'Trader Overview',
                href: route('dashboard'),
                active: route().current('dashboard'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'grid',
            },
            {
                label: 'Live Market',
                href: route('market.active'),
                active: route().current('market.active'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'pulse',
            },
            {
                label: 'Farmers',
                href: route('farmer.index'),
                active: route().current('farmer.index'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'farmer',
            },
            {
                label: 'Coffee Farms',
                href: route('farm.index'),
                active: route().current('farm.*'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'farm',
            },
            {
                label: 'Cooperatives',
                href: route('cooperative.index'),
                active: route().current('cooperative.*'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'cooperative',
            },
        ],
    },
    {
        title: 'Coffee Lots',
        items: [
            {
                label: 'Season',
                href: route('season.index'),
                active: route().current('season.*'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'season',
            },
            {
                label: 'All Lots',
                href: route('lot.index'),
                active: route().current('lot.index'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'cup',
            },
            {
                label: 'Arabica',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'arabica',
                chevron: true,
            },
            {
                label: 'Robusta',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'robusta',
                chevron: true,
            },
            {
                label: 'My Bids',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: '14',
                icon: 'card',
            },
            {
                label: 'Harvests',
                href: route('harvest.index'),
                active: route().current('harvest.*'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'harvest',
            },
            {
                label: 'Batches',
                href: route('batch.index'),
                active: route().current('batch.*'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'batch',
            },
            {
                label: 'Grade Guide',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'clipboard',
                chevron: true,
            },
        ],
    },
    {
        title: 'Regions',
        items: [
            {
                label: 'Bugisu · Mt Elgon',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'shield',
                chevron: true,
            },
            {
                label: 'Rwenzori Mts.',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'shield',
                chevron: true,
            },
            {
                label: 'West Nile',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'shield',
                chevron: true,
            },
        ],
    },
    {
        title: 'Account',
        items: [
            {
                label: 'Wallet',
                href: route('wallet.index'),
                active: route().current('wallet.*'),
                inertia: true,
                show: true,
                badge: null,
                icon: 'wallet',
            },
            {
                label: 'Alerts',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: '3',
                icon: 'bell',
            },
            {
                label: 'Reports',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'chart',
                chevron: true,
            },
            {
                label: 'Settings',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'settings',
            },
        ],
    },
]);
</script>

<template>

            <aside class="shell-scrollless fixed left-16 top-14 hidden h-[calc(100vh-3.5rem)] w-56 flex-shrink-0 flex-col overflow-x-hidden overflow-y-auto border-r border-line bg-sidebar lg:flex">
                <div class="px-4 pb-2 pt-4">
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-md border border-line bg-golddim">
                            <svg class="size-3.5 text-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="3" width="7" height="7" rx="1" />
                                <rect x="14" y="3" width="7" height="7" rx="1" />
                                <rect x="3" y="14" width="7" height="7" rx="1" />
                                <rect x="14" y="14" width="7" height="7" rx="1" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-mono text-[8px] uppercase tracking-[0.1em] text-ink3">Exchange</div>
                            <div class="font-display text-[12px] font-bold leading-none text-ink">Dashboard</div>
                        </div>
                    </div>
                </div>

                <div v-if="!isAdmin" class="px-4 py-2">
                    <div class="mb-2 mt-1 px-3 font-mono text-[9px] uppercase tracking-[0.14em] text-ink4">
                        My Agents
                    </div>

                    <div v-for="agent in subscribedAgents" :key="agent.id" class="mb-1.5">
                        <div class="snav" style="cursor: default;">
                            <el-icon><component :is="agent.icon || 'Setting'" /></el-icon>
                            <span class="snav-label">{{ agent.name }}</span>
                        </div>

                        <div v-if="agent.functions?.length" class="agent-fn-list">
                            <component
                                :is="functionIsRoute(fn) ? Link : 'a'"
                                v-for="fn in agent.functions"
                                :key="fn.id"
                                :href="functionHref(fn)"
                                class="agent-fn-link"
                                @click="onFunctionClick(fn, $event)"
                            >
                                <el-icon><component :is="fn.icon || 'Setting'" /></el-icon>
                                <span class="snav-label">{{ fn.name }}</span>
                            </component>
                        </div>
                    </div>

                    <div v-if="!subscribedAgents.length" class="px-3 py-2 text-[12px] leading-relaxed text-ink4">
                        No agents subscribed yet.
                        <Link :href="route('apps.index')" class="font-medium text-gold hover:underline">
                            Explore Apps
                        </Link>
                    </div>
                </div>

                <template v-if="isAdmin">
                <div
                    v-for="section in sideSections"
                    :key="section.title"
                    class="px-4 py-2"
                >
                    <div class="mb-2 mt-1 px-3 font-mono text-[9px] uppercase tracking-[0.14em] text-ink4">
                        {{ section.title }}
                    </div>

                    <Link
                        v-for="item in section.items.filter((item) => item.show && item.inertia)"
                        :key="`${section.title}-${item.label}`"
                        :href="item.href"
                        class="snav mb-0.5"
                        :class="{ active: item.active }"
                    >
                        <svg v-if="item.icon === 'grid'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>
                        <svg v-else-if="item.icon === 'pulse'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                        </svg>
                        <svg v-else-if="item.icon === 'farmer'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="8" r="3" />
                            <path d="M4 19a5 5 0 0110 0" />
                            <path d="M16 7h4" />
                            <path d="M16 11h4" />
                            <path d="M16 15h4" />
                        </svg>
                        <svg v-else-if="item.icon === 'farm'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M3 20h18" />
                            <path d="M5 20v-6l4-2 4 2v6" />
                            <path d="M13 20V9l3-2 3 2v11" />
                            <path d="M8 10V6h2v3" />
                        </svg>
                        <svg v-else-if="item.icon === 'cooperative'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 20h16" />
                            <path d="M6 20v-7" />
                            <path d="M12 20V9" />
                            <path d="M18 20v-5" />
                            <path d="M3 9l9-5 9 5" />
                            <path d="M8.5 12h1" />
                            <path d="M14.5 12h1" />
                        </svg>
                        <svg v-else-if="item.icon === 'harvest'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18.5 3C13 3 8.8 4.8 6.4 7.3A8.8 8.8 0 004 13.7c0 2.2.7 4.3 2.1 6.1" />
                            <path d="M7.1 19.8c1.3-1.9 3.2-3.1 5.3-3.1 4 0 7.3-3.3 7.3-7.3V3.9" />
                            <path d="M8 20h8" />
                        </svg>
                        <svg v-else-if="item.icon === 'season'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="5" width="18" height="16" rx="2" />
                            <path d="M16 3v4" />
                            <path d="M8 3v4" />
                            <path d="M3 10h18" />
                            <path d="M8 14h3" />
                            <path d="M13 14h3" />
                            <path d="M8 18h3" />
                        </svg>
                        <svg v-else-if="item.icon === 'batch'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 7.5l8-4 8 4-8 4-8-4z" />
                            <path d="M4 12l8 4 8-4" />
                            <path d="M4 16.5l8 4 8-4" />
                        </svg>
                        <svg v-else-if="item.icon === 'cup'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 8h1a4 4 0 010 8h-1" />
                            <path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z" />
                        </svg>
                        <svg v-else-if="item.icon === 'wallet'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="7" width="20" height="14" rx="2" />
                            <path d="M2 12h20" />
                            <circle cx="16" cy="16" r="1.25" fill="currentColor" stroke="none" />
                        </svg>
                        <span class="snav-label">{{ item.label }}</span>
                        <span v-if="item.badge" class="snav-trailing">
                            <span class="snav-badge">{{ item.badge }}</span>
                        </span>
                    </Link>

                    <a
                        v-for="item in section.items.filter((item) => item.show && !item.inertia)"
                        :key="`${section.title}-${item.label}`"
                        :href="item.href"
                        class="snav mb-0.5"
                        :class="{ active: item.active }"
                        @click.prevent
                    >
                        <svg v-if="item.icon === 'cup'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 8h1a4 4 0 010 8h-1" />
                            <path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z" />
                        </svg>
                        <div v-else-if="item.icon === 'arabica'" class="h-2 w-2 flex-shrink-0 rounded-full bg-ara"></div>
                        <div v-else-if="item.icon === 'robusta'" class="h-2 w-2 flex-shrink-0 rounded-full bg-rob"></div>
                        <svg v-else-if="item.icon === 'card'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" />
                            <circle cx="9" cy="12" r="2" />
                        </svg>
                        <svg v-else-if="item.icon === 'clipboard'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                            <path d="M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                            <path d="M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <svg v-else-if="item.icon === 'shield'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                        <svg v-else-if="item.icon === 'bell'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 01-3.46 0" />
                        </svg>
                        <svg v-else-if="item.icon === 'chart'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z" />
                            <path d="M9 19V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                            <path d="M15 19a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2z" />
                        </svg>
                        <svg v-else-if="item.icon === 'settings'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.07 4.93A10 10 0 115 19.07" />
                        </svg>
                        <span class="snav-label">{{ item.label }}</span>
                        <span v-if="item.badge || item.chevron" class="snav-trailing">
                            <span v-if="item.badge" class="snav-badge">{{ item.badge }}</span>
                            <svg
                                v-if="item.chevron"
                                class="snav-chevron"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </span>
                    </a>
                </div>
                </template>

                <div class="mt-auto border-t border-line p-3">
                    <div class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 transition-colors hover:bg-gray-50">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gold font-display text-[11px] font-bold text-white">
                            {{ userInitials }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-[12px] font-medium text-ink">{{ user?.name }}</div>
                            <div class="flex items-center gap-1 truncate font-mono text-[8px] text-up">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-up"></span>
                                Verified trader
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

</template>

<style scoped>
/* ── Scrollbar hiding on the root aside ────────────────────────────────────── */
aside {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
aside::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}

/* ── Side-nav items ────────────────────────────────────────────────────────── */
.snav {
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 6px;
    padding: 7px 12px;
    font-size: 13px;
    color: #374151;
    text-decoration: none;
    transition: all 0.15s ease;
}

.snav svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
}

.snav .el-icon {
    width: 15px;
    height: 15px;
    font-size: 15px;
    flex-shrink: 0;
}

.snav-label {
    flex: 1 1 auto;
    min-width: 0;
}

.agent-fn-list {
    display: flex;
    flex-direction: column;
    gap: 1px;
    margin-top: 1px;
}

.agent-fn-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    border-radius: 5px;
    font-size: 11px;
    color: #9ca3af;
    text-decoration: none;
    transition: all 0.15s ease;
}

.agent-fn-link .el-icon {
    width: 15px;
    height: 15px;
    font-size: 15px;
    flex-shrink: 0;
}

.agent-fn-link:hover {
    background: #fff8f0;
    color: #c8862a;
}

.snav-trailing {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    flex-shrink: 0;
}

.snav-badge {
    border-radius: 0.25rem;
    background: #fff8f0;
    padding: 0.125rem 0.375rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 8px;
    line-height: 1;
    color: #c8862a;
}

.snav-chevron {
    width: 14px;
    height: 14px;
    color: #9ca3af;
}

.snav:hover {
    background: #fff8f0;
    color: #c8862a;
}

.snav.active {
    background: #fff8f0;
    color: #c8862a;
    font-weight: 500;
}
</style>
