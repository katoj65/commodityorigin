<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
    title: String,
});

const page = usePage();
const mobileMenuOpen = ref(false);
const showMobileMenuButton = ref(false);
const fabOpen = ref(false);

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
        label: 'Home',
        href: route('home'),
        active: route().current('home'),
        inertia: true,
    },
    {
        label: 'Market',
        href: route('market.index'),
        active: route().current('market.index'),
        inertia: true,
    },
    {
        label: 'Auction',
        href: route('auction.index'),
        active: route().current('auction.index'),
        inertia: true,
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

const fabActions = [
    {
        label: 'Register farmer',
        icon: 'farmer',
        href: route('farmer.create'),
        inertia: true,
    },
    {
        label: 'Add lot',
        icon: 'lot',
        href: '#',
        inertia: false,
    },
    {
        label: 'Add batch',
        icon: 'batch',
        href: '#',
        inertia: false,
    },
];

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

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const toggleFabMenu = () => {
    fabOpen.value = !fabOpen.value;
};

const closeFabMenu = () => {
    fabOpen.value = false;
};

const syncMobileNavState = () => {
    showMobileMenuButton.value = window.innerWidth < 1024;

    if (!showMobileMenuButton.value) {
        closeMobileMenu();
    }
};

onMounted(() => {
    syncMobileNavState();
    window.addEventListener('resize', syncMobileNavState);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncMobileNavState);
});
</script>

<template>
    <div class="dashboard-shell flex min-h-screen flex-col overflow-hidden bg-page text-ink lg:h-screen">
        <Head :title="title">
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link
                href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap"
                rel="stylesheet"
            />
        </Head>

        <Banner />

        <header class="z-30 flex h-14 flex-shrink-0 items-stretch bg-card">
            <div class="hidden h-full w-16 flex-shrink-0 items-center justify-center bg-[#212529] lg:flex">
                <div class="flex h-9 w-9 items-center justify-center">
                    <ApplicationMark class="h-8 w-8" />
                </div>
            </div>

            <div class="flex min-w-0 flex-1 items-center border-b border-line bg-card">
                <button
                    v-if="showMobileMenuButton"
                    type="button"
                    class="shell-icon-button ml-3 lg:hidden"
                    @click="toggleMobileMenu"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h16" />
                        <path d="M4 12h16" />
                        <path d="M4 17h16" />
                    </svg>
                </button>

                <Link
                    :href="route('dashboard')"
                    class="flex h-full min-w-0 items-center gap-2 px-3 no-underline sm:min-w-[170px] sm:px-4"
                    @click="closeMobileMenu"
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

                <div class="ml-auto flex items-center gap-1 px-3 sm:gap-2 sm:px-4">
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

                    <button type="button" class="shell-icon-button">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="20" r="1.5" />
                            <circle cx="17" cy="20" r="1.5" />
                            <path d="M3 4h2l2.2 10.2a1 1 0 00.98.8h8.72a1 1 0 00.97-.76L21 8H7" />
                        </svg>
                    </button>

                    <button type="button" class="shell-icon-button relative hidden sm:inline-flex">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 01-3.46 0" />
                        </svg>
                        <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-dn"></span>
                    </button>

                    <button type="button" class="shell-icon-button hidden sm:inline-flex">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.07 4.93A10 10 0 115 19.07" />
                        </svg>
                    </button>

                    <Dropdown
                        align="right"
                        width="48"
                        :content-classes="['border border-[#E5E7EB] bg-white py-2 shadow-[0_20px_60px_rgba(17,24,39,0.12)]']"
                    >
                        <template #trigger>
                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gold font-display text-[11px] font-bold text-white transition-transform hover:scale-[1.03]"
                                :title="user.name"
                            >
                                {{ userInitials }}
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 pb-3 pt-2">
                                <div class="truncate text-[12px] font-semibold text-[#111827]">{{ user.name }}</div>
                                <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">
                                    {{ user.role || 'Account' }}
                                </div>
                            </div>

                            <div class="mx-3 h-px bg-[#E5E7EB]"></div>

                            <div class="px-2 py-2">
                                <Link :href="route('profile.show')" class="account-menu-link">
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="8" r="3.5" />
                                        <path d="M5 19a7 7 0 0114 0" />
                                    </svg>
                                    Profile settings
                                </Link>
                                <Link :href="route('dashboard')" class="account-menu-link">
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect x="3" y="3" width="7" height="7" rx="1.25" />
                                        <rect x="14" y="3" width="7" height="7" rx="1.25" />
                                        <rect x="3" y="14" width="7" height="7" rx="1.25" />
                                        <rect x="14" y="14" width="7" height="7" rx="1.25" />
                                    </svg>
                                    Dashboard
                                </Link>
                                <Link :href="route('home')" class="account-menu-link">
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M3 10.5L12 3l9 7.5" />
                                        <path d="M5 9.5V21h14V9.5" />
                                    </svg>
                                    Home
                                </Link>
                            </div>

                            <div class="mx-3 h-px bg-[#E5E7EB]"></div>

                            <div class="px-2 pb-2 pt-2">
                                <button type="button" class="account-menu-button" @click="logout">
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M15 3h3a2 2 0 012 2v14a2 2 0 01-2 2h-3" />
                                        <path d="M10 17l5-5-5-5" />
                                        <path d="M15 12H3" />
                                    </svg>
                                    Log out
                                </button>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </header>

        <div
            v-if="mobileMenuOpen"
            class="fixed inset-0 z-40 bg-[#111827]/45 backdrop-blur-[1px] lg:hidden"
            @click="closeMobileMenu"
        ></div>

        <div
            v-if="fabOpen"
            class="fixed inset-0 z-40"
            @click="closeFabMenu"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-[88vw] max-w-[320px] flex-col overflow-y-auto border-r border-line bg-sidebar shadow-2xl transition-transform duration-200 lg:hidden"
            :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center justify-between border-b border-line px-4 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center">
                        <ApplicationMark class="h-8 w-8" />
                    </div>
                    <div>
                        <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-ink3">Commodity</div>
                        <div class="font-display text-[14px] font-bold leading-tight text-ink">Origin</div>
                    </div>
                </div>

                <button type="button" class="shell-icon-button" @click="closeMobileMenu">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 6l12 12" />
                        <path d="M18 6L6 18" />
                    </svg>
                </button>
            </div>

            <div class="border-b border-line px-4 py-3">
                <div class="mb-2 font-mono text-[9px] uppercase tracking-[0.14em] text-ink4">Overview</div>
                <div class="flex flex-col gap-1">
                    <template v-for="link in topNavLinks" :key="`mobile-top-${link.label}`">
                        <Link
                            v-if="link.inertia"
                            :href="link.href"
                            class="snav"
                            :class="{ active: link.active }"
                            @click="closeMobileMenu"
                        >
                            <span class="snav-label">{{ link.label }}</span>
                        </Link>
                        <a
                            v-else
                            :href="link.href"
                            class="snav"
                            :class="{ active: link.active }"
                            @click.prevent="closeMobileMenu"
                        >
                            <span class="snav-label">{{ link.label }}</span>
                            <svg
                                v-if="link.hasChevron"
                                class="snav-chevron"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </a>
                    </template>
                </div>
            </div>

            <div
                v-for="section in sideSections"
                :key="`mobile-${section.title}`"
                class="px-4 py-3"
            >
                <div class="mb-2 px-3 font-mono text-[9px] uppercase tracking-[0.14em] text-ink4">
                    {{ section.title }}
                </div>

                <div class="flex flex-col gap-1">
                    <Link
                        v-for="item in section.items.filter((item) => item.show && item.inertia)"
                        :key="`mobile-${section.title}-${item.label}`"
                        :href="item.href"
                        class="snav"
                        :class="{ active: item.active }"
                        @click="closeMobileMenu"
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
                        :key="`mobile-${section.title}-${item.label}`"
                        :href="item.href"
                        class="snav"
                        :class="{ active: item.active }"
                        @click.prevent="closeMobileMenu"
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
            </div>

            <div class="mt-auto border-t border-line p-4">
                <div class="mb-3 flex items-center gap-2.5 rounded-lg px-2 py-1.5">
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

                <Link :href="route('profile.show')" class="snav mb-2" @click="closeMobileMenu">
                    <span class="snav-label">Profile</span>
                </Link>
                <button type="button" class="mobile-logout-btn" @click="logout">
                    Sign out
                </button>
            </div>
        </aside>

        <div class="flex min-h-0 flex-1 overflow-visible lg:overflow-hidden">
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

            <main class="flex-1 overflow-y-visible lg:overflow-y-auto">
                <div class="p-3 sm:p-5 lg:p-6">
                    <slot />
                </div>
            </main>
        </div>

        <div class="fab-shell">
            <transition-group
                name="fab-action"
                tag="div"
                class="fab-actions"
            >
                <Link
                    v-for="action in fabOpen ? fabActions.filter((action) => action.inertia) : []"
                    :key="action.label"
                    :href="action.href"
                    class="fab-action-button"
                    @click="closeFabMenu"
                >
                    <svg
                        v-if="action.icon === 'farmer'"
                        class="fab-action-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M12 3l7 4-7 4-7-4 7-4z" />
                        <path d="M7 10v3a5 5 0 0010 0v-3" />
                        <path d="M5 21a7 7 0 0114 0" />
                    </svg>
                    <svg
                        v-else-if="action.icon === 'lot'"
                        class="fab-action-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M4 7h16" />
                        <path d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                        <path d="M12 10v6" />
                        <path d="M9 13h6" />
                    </svg>
                    <svg
                        v-else
                        class="fab-action-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <rect x="4" y="5" width="7" height="7" rx="1.25" />
                        <rect x="13" y="5" width="7" height="7" rx="1.25" />
                        <rect x="4" y="14" width="7" height="7" rx="1.25" />
                        <path d="M16.5 15v5" />
                        <path d="M14 17.5h5" />
                    </svg>
                    <span>{{ action.label }}</span>
                </Link>

                <button
                    v-for="action in fabOpen ? fabActions.filter((action) => !action.inertia) : []"
                    :key="action.label"
                    type="button"
                    class="fab-action-button"
                    @click="closeFabMenu"
                >
                    <svg
                        v-if="action.icon === 'farmer'"
                        class="fab-action-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M12 3l7 4-7 4-7-4 7-4z" />
                        <path d="M7 10v3a5 5 0 0010 0v-3" />
                        <path d="M5 21a7 7 0 0114 0" />
                    </svg>
                    <svg
                        v-else-if="action.icon === 'lot'"
                        class="fab-action-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M4 7h16" />
                        <path d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                        <path d="M12 10v6" />
                        <path d="M9 13h6" />
                    </svg>
                    <svg
                        v-else
                        class="fab-action-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <rect x="4" y="5" width="7" height="7" rx="1.25" />
                        <rect x="13" y="5" width="7" height="7" rx="1.25" />
                        <rect x="4" y="14" width="7" height="7" rx="1.25" />
                        <path d="M16.5 15v5" />
                        <path d="M14 17.5h5" />
                    </svg>
                    <span>{{ action.label }}</span>
                </button>
            </transition-group>

            <button
                type="button"
                class="fab-trigger"
                :class="{ open: fabOpen }"
                @click="toggleFabMenu"
            >
                <svg class="fab-trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                </svg>
            </button>
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

.mobile-logout-btn {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #ffffff;
    padding: 0.75rem 0.875rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    text-align: left;
    transition: all 0.15s ease;
}

.mobile-logout-btn:hover {
    border-color: rgba(200, 134, 42, 0.35);
    background: #fff8f0;
    color: #c8862a;
}

.account-menu-link,
.account-menu-button {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    width: 100%;
    border-radius: 0.5rem;
    padding: 0.625rem 0.75rem;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    text-decoration: none;
    transition: all 0.15s ease;
}

.account-menu-link:hover,
.account-menu-button:hover {
    background: #fff8f0;
    color: #c8862a;
}

.account-menu-button {
    border: 0;
    background: transparent;
    cursor: pointer;
}

.account-menu-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.fab-shell {
    position: fixed;
    right: 1rem;
    bottom: 1rem;
    z-index: 50;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.75rem;
}

.fab-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.625rem;
}

.fab-action-button {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    border: 1px solid rgba(229, 231, 235, 0.95);
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.98);
    padding: 0.75rem 1rem;
    box-shadow: 0 18px 45px rgba(17, 24, 39, 0.14);
    color: #111827;
    font-size: 0.875rem;
    font-weight: 600;
    transition: transform 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
}

.fab-action-button:hover {
    transform: translateY(-1px);
    color: #c8862a;
    box-shadow: 0 22px 55px rgba(17, 24, 39, 0.18);
}

.fab-action-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.fab-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border: 0;
    border-radius: 9999px;
    background: linear-gradient(135deg, #c8862a, #e09b3a);
    box-shadow: 0 22px 55px rgba(200, 134, 42, 0.32);
    color: #ffffff;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.fab-trigger:hover {
    transform: translateY(-1px);
    box-shadow: 0 26px 62px rgba(200, 134, 42, 0.38);
}

.fab-trigger.open .fab-trigger-icon {
    transform: rotate(45deg);
}

.fab-trigger-icon {
    width: 1.25rem;
    height: 1.25rem;
    transition: transform 0.15s ease;
}

.fab-action-enter-active,
.fab-action-leave-active {
    transition: all 0.18s ease;
}

.fab-action-enter-from,
.fab-action-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.96);
}

@media (min-width: 640px) {
    .fab-shell {
        right: 1.5rem;
        bottom: 1.5rem;
    }
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
