<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
    title: String,
});

const page = usePage();

const user = computed(() => page.props.auth.user);
const userInitials = computed(() => {
    const name = user.value?.name ?? '';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('') || 'CO';
});

const topNavLinks = computed(() => [
    {
        label: 'Overview',
        href: route('dashboard'),
        active: route().current('dashboard'),
        inertia: true,
    },
    {
        label: 'Market',
        href: '#',
        active: false,
        inertia: false,
    },
    {
        label: 'Reports',
        href: '#',
        active: false,
        inertia: false,
        hasChevron: true,
    },
]);

const railLinks = computed(() => [
    {
        label: 'Overview',
        href: route('dashboard'),
        active: route().current('dashboard'),
        inertia: true,
        icon: 'grid',
    },
    {
        label: 'Market',
        href: '#',
        active: false,
        inertia: false,
        icon: 'pulse',
    },
    {
        label: 'Lots',
        href: '#',
        active: false,
        inertia: false,
        icon: 'cup',
    },
    {
        label: 'My Bids',
        href: '#',
        active: false,
        inertia: false,
        icon: 'card',
    },
    {
        label: 'Origins',
        href: '#',
        active: false,
        inertia: false,
        icon: 'shield',
        dividerBefore: true,
    },
    {
        label: 'Grading',
        href: '#',
        active: false,
        inertia: false,
        icon: 'clipboard',
    },
    {
        label: 'Reports',
        href: '#',
        active: false,
        inertia: false,
        icon: 'chart',
    },
    {
        label: 'Alerts · 3',
        href: '#',
        active: false,
        inertia: false,
        icon: 'bell',
        dot: true,
    },
    {
        label: 'Profile',
        href: route('profile.show'),
        active: route().current('profile.show'),
        inertia: true,
        icon: 'user',
        dividerBefore: true,
    },
]);

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
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: null,
                icon: 'pulse',
            },
        ],
    },
    {
        title: 'Coffee Lots',
        items: [
            {
                label: 'All Lots',
                href: '#',
                active: false,
                inertia: false,
                show: true,
                badge: '312',
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

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="dashboard-shell flex h-screen flex-col overflow-hidden bg-page text-ink">
        <Head :title="title">
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link
                href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap"
                rel="stylesheet"
            />
        </Head>

        <Banner />

        <header class="z-30 flex h-14 flex-shrink-0 items-center border-b border-line bg-card">
            <div class="flex h-full items-center">
                <div class="hidden h-full w-16 flex-shrink-0 items-center justify-center border-r border-line lg:flex">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gold text-white">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 8h1a4 4 0 010 8h-1" />
                            <path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z" />
                        </svg>
                    </div>
                </div>

                <Link
                    :href="route('dashboard')"
                    class="flex h-full min-w-[170px] items-center gap-2  px-4 no-underline"
                >

                    <div>
                        <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-ink3">Commodity</div>
                        <div class="font-display text-[13px] font-bold leading-tight text-ink">Origin</div>
                    </div>
                </Link>

                <nav class="hidden h-full items-center gap-1 px-4 md:flex">
                    <template v-for="link in topNavLinks" :key="link.label">
                        <Link
                            v-if="link.inertia"
                            :href="link.href"
                            class="shell-top-link"
                            :class="{ active: link.active }"
                        >
                            {{ link.label }}
                        </Link>
                        <a
                            v-else
                            :href="link.href"
                            class="shell-top-link"
                            :class="{ active: link.active }"
                            @click.prevent
                        >
                            <span>{{ link.label }}</span>
                            <svg
                                v-if="link.hasChevron"
                                class="ml-1 size-3"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </a>
                    </template>
                </nav>
            </div>

            <div class="ml-auto flex items-center gap-2 px-4">
                <div class="hidden items-center gap-3 rounded-md border border-line bg-white px-3 py-1.5 lg:flex">
                    <span class="font-mono text-[9px] tracking-[0.1em] text-ink3">UGA-ARA-AA</span>
                    <span class="font-mono text-[11px] font-medium text-ink">$5.10</span>
                    <span class="font-mono text-[9px] text-up">▲ 1.2%</span>
                    <div class="h-3 w-px bg-line2"></div>
                    <div class="flex items-center gap-1">
                        <div class="h-1.5 w-1.5 rounded-full bg-up pulse-green"></div>
                        <span class="font-mono text-[8px] tracking-[0.08em] text-up">LIVE</span>
                    </div>
                </div>

                <button type="button" class="shell-icon-button">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                    </svg>
                </button>

                <button type="button" class="shell-icon-button relative">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 01-3.46 0" />
                    </svg>
                    <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-dn"></span>
                </button>

                <button type="button" class="shell-icon-button">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.07 4.93A10 10 0 115 19.07" />
                    </svg>
                </button>

                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gold font-display text-[11px] font-bold text-white"
                    :title="user.name"
                >
                    {{ userInitials }}
                </div>
            </div>
        </header>

        <div class="flex min-h-0 flex-1 overflow-hidden">
            <div class="dashboard-rail hidden w-16 flex-shrink-0 flex-col items-center gap-1 overflow-y-auto border-r border-white/[0.08] py-3 lg:flex">
                <template v-for="link in railLinks" :key="link.label">
                    <div v-if="link.dividerBefore" class="my-1 h-px w-8 bg-white/10"></div>
                    <Link
                        v-if="link.inertia"
                        :href="link.href"
                        class="rail-item group relative flex h-10 w-10 items-center justify-center rounded-xl transition-colors"
                        :class="link.active ? 'bg-gold text-white' : 'text-white/50 hover:bg-white/10 hover:text-white'"
                    >
                        <svg v-if="link.icon === 'grid'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                            <rect x="14" y="3" width="7" height="7" rx="1.5" />
                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        </svg>
                        <svg v-else-if="link.icon === 'user'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="7" r="4" />
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        </svg>
                        <div class="tooltip">{{ link.label }}</div>
                    </Link>
                    <a
                        v-else
                        :href="link.href"
                        class="rail-item group relative flex h-10 w-10 items-center justify-center rounded-xl text-white/50 transition-colors hover:bg-white/10 hover:text-white"
                        @click.prevent
                    >
                        <svg v-if="link.icon === 'pulse'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                        </svg>
                        <svg v-else-if="link.icon === 'cup'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 8h1a4 4 0 010 8h-1" />
                            <path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z" />
                        </svg>
                        <svg v-else-if="link.icon === 'card'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" />
                            <circle cx="9" cy="12" r="2" />
                        </svg>
                        <svg v-else-if="link.icon === 'shield'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                        <svg v-else-if="link.icon === 'clipboard'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                            <path d="M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                            <path d="M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <svg v-else-if="link.icon === 'chart'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z" />
                            <path d="M9 19V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                            <path d="M15 19a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2z" />
                        </svg>
                        <svg v-else class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 01-3.46 0" />
                        </svg>
                        <span v-if="link.dot" class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full border border-[#212529] bg-dn"></span>
                        <div class="tooltip">{{ link.label }}</div>
                    </a>
                </template>

                <div class="mb-1 flex h-9 w-9 items-center justify-center rounded-full bg-gold font-display text-[11px] font-bold text-white">
                    {{ userInitials }}
                </div>
            </div>

            <aside class="hidden w-56 flex-shrink-0 flex-col overflow-y-auto border-r border-line bg-sidebar lg:flex">
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

                <div class="mt-auto border-t border-line p-3">
                    <div class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 transition-colors hover:bg-gray-50">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gold font-display text-[11px] font-bold text-white">
                            {{ userInitials }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-[12px] font-medium text-ink">{{ user.name }}</div>
                            <div class="flex items-center gap-1 truncate font-mono text-[8px] text-up">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-up"></span>
                                Verified trader
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto">
                <div class="p-5 lg:p-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.dashboard-shell {
    font-family: 'Source Sans 3', sans-serif;
}

.dashboard-shell :deep(.font-display) {
    font-family: 'IBM Plex Sans', sans-serif;
}

.dashboard-shell :deep(.font-mono) {
    font-family: 'IBM Plex Mono', monospace;
}

.dashboard-shell :deep(::-webkit-scrollbar) {
    width: 3px;
    height: 3px;
}

.dashboard-shell :deep(::-webkit-scrollbar-track) {
    background: #f0f2f5;
}

.dashboard-shell :deep(::-webkit-scrollbar-thumb) {
    background: #d1d5db;
    border-radius: 2px;
}

.dashboard-rail {
    background: #212529;
}

.pulse-gold {
    animation: pulseGold 2.5s ease-in-out infinite;
}

.pulse-green {
    animation: pulseGreen 2s ease-in-out infinite;
}

.shell-top-link {
    display: flex;
    align-items: center;
    height: 100%;
    border-bottom: 2px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    text-decoration: none;
    transition: color 0.15s ease, border-color 0.15s ease;
}

.shell-top-link:hover {
    color: #111827;
    border-color: #d1d5db;
}

.shell-top-link.active {
    color: #c8862a;
    border-color: #c8862a;
}

.shell-icon-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #f0f2f5;
    color: #6b7280;
    transition: all 0.15s ease;
}

.shell-icon-button:hover {
    border-color: rgba(200, 134, 42, 0.3);
    background: #fff8f0;
    color: #c8862a;
}

.rail-item {
    position: relative;
}

.rail-item .tooltip {
    position: absolute;
    top: 50%;
    left: calc(100% + 8px);
    transform: translateY(-50%);
    white-space: nowrap;
    border-radius: 3px;
    background: #1a150d;
    padding: 4px 10px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #f2ede4;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s ease;
    z-index: 50;
}

.rail-item:hover .tooltip {
    opacity: 1;
}

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

.snav-label {
    flex: 1 1 auto;
    min-width: 0;
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

.page-header-card {
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(250, 250, 250, 0.95));
    padding: 1.25rem 1.5rem;
    box-shadow: 0 12px 32px rgba(17, 24, 39, 0.04);
}

.page-header-card :deep(h2) {
    margin: 0;
    font-family: 'Syne', sans-serif;
    font-size: 1.5rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
    color: #111827;
}

.page-header-card :deep(p) {
    margin-top: 0.35rem;
    color: #6b7280;
}

@keyframes pulseGold {
    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

@keyframes pulseGreen {
    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.4;
    }
}
</style>
