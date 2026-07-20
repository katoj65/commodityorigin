<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { ElMessage, ElNotification } from 'element-plus';
import { User, Crop, ShoppingCart, Van, TrendCharts, Setting } from '@element-plus/icons-vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Dropdown from '@/Components/Dropdown.vue';
import AppAside from '@/Components/Aside/AppAside.vue';
import InputError from '@/Components/InputError.vue';
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
const showMobileMenuButton = ref(false);

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
        active: route().current('market.*'),
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

// ── Role dialog ────────────────────────────────────────────────
const roles        = computed(() => page.props.roles ?? []);
const roleIconMap  = { user: User, farmer: Crop, buyer: ShoppingCart, exporter: Van, investor: TrendCharts, admin: Setting };
const showRoleDialog = ref(false);
const selectedRole   = ref(user.value?.role ?? null);
const roleForm       = useForm({ role: '' });

watch(showRoleDialog, (open) => { if (open) selectedRole.value = user.value?.role ?? null; });

const submitRoleForm = () => {
    roleForm.role = selectedRole.value ?? '';
    roleForm.post(route('profile.role'), {
        preserveScroll: true,
        preserveState:  true,
        onSuccess: () => {
            ElMessage.success('Role selected successfully.');
            showRoleDialog.value = false;
            selectedRole.value   = null;
        },
        onError: () => {
            showRoleDialog.value = true;
        },
    });
};

const logout = () => {
    router.post(route('logout'));
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};


const syncMobileNavState = () => {
    showMobileMenuButton.value = window.innerWidth < 1024;

    if (!showMobileMenuButton.value) {
        closeMobileMenu();
    }
};

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
    syncMobileNavState();
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
    <div class="dashboard-shell flex min-h-screen flex-col overflow-x-hidden bg-page text-ink">
        <Head :title="title">
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link
                href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap"
                rel="stylesheet"
            />
        </Head>



        <header class="fixed inset-x-0 top-0 z-30 flex h-14 flex-shrink-0 items-stretch bg-white">
            <div class="hidden h-full w-16 flex-shrink-0 items-center justify-center bg-[#212529] lg:flex">
                <div class="flex h-9 w-9 items-center justify-center">
                    <ApplicationMark class="h-8 w-8" />
                </div>
            </div>

            <div class="flex min-w-0 flex-1 items-center border-b border-line bg-white">
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
                    class="flex h-full min-w-0 items-center gap-2 px-3 no-underline text-[#111827] sm:min-w-[170px] sm:px-4"
                    @click="closeMobileMenu"
                >
                    <div>
                        <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#111827]">Commodity</div>
                        <div class="font-display text-[13px] font-bold leading-tight text-[#111827]">Origin</div>
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

                    <Link :href="route('checkout.index')" class="shell-icon-button">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="20" r="1.5" />
                            <circle cx="17" cy="20" r="1.5" />
                            <path d="M3 4h2l2.2 10.2a1 1 0 00.98.8h8.72a1 1 0 00.97-.76L21 8H7" />
                        </svg>
                    </Link>

                    <Link :href="route('apps.index')" class="shell-icon-button hidden sm:inline-flex" title="Apps">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                            <rect x="14" y="3" width="7" height="7" rx="1.5" />
                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        </svg>
                    </Link>

                    <button type="button" class="shell-icon-button relative hidden sm:inline-flex">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 01-3.46 0" />
                        </svg>
                        <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-dn"></span>
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
                                :title="user?.name"
                            >
                                {{ userInitials }}
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 pb-3 pt-2">
                                <div class="truncate text-[12px] font-semibold text-[#111827]">{{ user?.name }}</div>
                                <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">
                                    {{ user?.role || 'Account' }}
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
                                <button type="button" class="account-menu-button" @click="showRoleDialog = true">
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                    Switch Role
                                </button>
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
                        <div class="truncate text-[12px] font-medium text-ink">{{ user?.name }}</div>
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





        <div class="flex flex-1 overflow-visible pt-14">
            <div class="dashboard-rail shell-scrollless fixed left-0 top-14 hidden h-[calc(100vh-3.5rem)] w-16 flex-shrink-0 flex-col items-center gap-1 overflow-x-hidden overflow-y-auto border-r border-white/[0.08] py-3 lg:flex">
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
                        <svg v-else-if="link.icon === 'pulse'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
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





            <AppAside />







            <main class="min-w-0 flex-1 overflow-visible lg:ml-72">
                <div
                    class="min-w-0"
                    :class="props.flush ? 'w-full max-w-none' : (props.fullWidth ? 'w-full max-w-none p-3 sm:p-5 lg:p-6' : 'p-3 sm:p-5 lg:p-6')"
                >
                    <slot />
                </div>
            </main>
        </div>

        <!-- ── Select Role Dialog ──────────────────────── -->
        <el-dialog
            v-model="showRoleDialog"
            width="min(640px, calc(100vw - 2rem))"
            class="role-select-dialog"
            destroy-on-close
            align-center
            :close-on-click-modal="false"
            :show-close="true"
        >
            <template #header>
                <div class="pr-8">
                    <div class="mt-0.5 text-[17px] font-bold tracking-tight text-[#111827]">Select Your Role</div>
                    <p class="mt-1 text-[13px] text-[#6B7280]">Choose the role that best describes how you participate on the exchange.</p>
                    <InputError :message="roleForm.errors.role" class="mt-1" />
                </div>
            </template>

            <div class="grid grid-cols-2 gap-3 px-4 py-4 sm:grid-cols-3">
                <button
                    v-for="r in roles"
                    :key="r.slug"
                    type="button"
                    class="role-card text-left"
                    :class="{ selected: selectedRole === r.slug }"
                    @click="selectedRole = r.slug"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <el-icon :size="22" class="role-card-icon" :class="{ active: selectedRole === r.slug }">
                            <component :is="roleIconMap[r.slug] ?? User" />
                        </el-icon>
                        <span v-if="selectedRole === r.slug" class="role-card-check">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12l5 5L20 7"/></svg>
                        </span>
                    </div>
                    <span class="role-card-name">{{ r.name }}</span>
                    <p v-if="r.description" class="role-card-desc mt-1">{{ r.description }}</p>
                </button>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 px-4 pb-1">
                    <button
                        type="button"
                        class="layout-primary-btn min-w-[130px]"
                        :disabled="roleForm.processing"
                        @click="submitRoleForm"
                    >
                        <svg v-if="roleForm.processing" class="layout-btn-icon animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                        {{ roleForm.processing ? 'Saving…' : 'Confirm Role' }}
                    </button>
                </div>
            </template>
        </el-dialog>

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

.dashboard-shell :deep(.shell-scrollless) {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.dashboard-shell :deep(.shell-scrollless::-webkit-scrollbar) {
    display: none;
    width: 0;
    height: 0;
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

/* ── Role select dialog ── */
:deep(.role-select-dialog),
:deep(.role-select-dialog .el-dialog) { border-radius:16px;overflow:hidden; }
:deep(.role-select-dialog .el-dialog__header) { margin-right:0;padding:14px 18px 10px; }
:deep(.role-select-dialog .el-dialog__body)   { padding:0;max-height:500px;overflow-y:auto; }
:deep(.role-select-dialog .el-dialog__footer) { padding:8px 18px 14px;border-top:1px solid #f3f4f6; }

.role-card {
    border:1.5px solid #e5e7eb;border-radius:12px;padding:.875rem 1rem;
    background:#fff;cursor:pointer;width:100%;
    transition:border-color .15s,background .15s,box-shadow .15s;
}
.role-card:hover { border-color:#c8862a;background:#fffaf4; }
.role-card.selected { border-color:#c8862a;background:#fff8f0;box-shadow:0 0 0 3px rgba(200,134,42,.12); }
.role-card-name { display:block;font-size:14px;font-weight:600;color:#111827;line-height:1.3; }
.role-card-desc { font-size:11px;color:#6b7280;line-height:1.5; }
.role-card-check { flex-shrink:0;color:#c8862a; }
.role-card-icon { color:#9ca3af;transition:color .15s; }
.role-card-icon.active { color:#c8862a; }
.role-card:hover .role-card-icon { color:#c8862a; }

.layout-primary-btn { display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border:1px solid #c8862a;background:#c8862a;color:#fff;border-radius:.5rem;padding:.425rem .875rem;font-size:13px;font-weight:500;transition:all .15s; }
.layout-primary-btn:hover { background:#e09b3a;border-color:#e09b3a; }
.layout-primary-btn:disabled { opacity:.6;cursor:not-allowed; }
.layout-btn-icon { width:1rem;height:1rem;flex-shrink:0; }
</style>
