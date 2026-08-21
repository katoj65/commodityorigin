<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import { User, Crop, ShoppingCart, Van, TrendCharts, Setting } from '@element-plus/icons-vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Dropdown from '@/Components/Dropdown.vue';
import AppAside from '@/Components/Aside/AppAside.vue';
import IconMenu from '@/Components/Aside/IconMenu.vue';
import InputError from '@/Components/InputError.vue';
import QuickBuyModal from '@/Components/Modals/QuickBuyModal.vue';

const props = defineProps({
    title:       String,
    fullWidth:   { type: Boolean, default: false },
    flush:       { type: Boolean, default: false },
    hasProfile:  { type: Boolean, default: false },
    hasRole:     { type: Boolean, default: false },
    roles:       { type: Array,   default: () => [] },
    cropGrades:  { type: Array,   default: () => [] },
    lotRequests: { type: Array,   default: () => [] },
});

// ── Shell state ────────────────────────────────────────────────
const page = usePage();
const mobileMenuOpen       = ref(false);
const showMobileMenuButton = ref(false);

const user = computed(() => page.props.auth.user);
const userInitials = computed(() => {
    const name = user.value?.name ?? '';
    return name.split(' ').filter(Boolean).slice(0, 2).map(p => p[0]?.toUpperCase()).join('') || 'CO';
});

const topNavLinks = computed(() => [
    { label: 'Home',    href: route('home'),         active: route().current('home'),        inertia: true },
    { label: 'Market',  href: route('market.index'), active: route().current('market.*'),    inertia: true },
    { label: 'Auction', href: route('auction.index'), active: route().current('auction.index'), inertia: true },
]);


// ── Dashboard state ────────────────────────────────────────────
const selectedRange = ref('1W');
const selectedType  = ref('all');

// ── Dashboard data ─────────────────────────────────────────────
const stats = [
    { label: 'Portfolio',     value: '$284,720', icon: 'layers', helper: 'this month',    trend: '▲ 4.2%',      badgeClass: 'bg-[#ECFDF5] text-[#2D7A55]' },
    { label: 'Active bids',   value: '14',       icon: 'pulse',  helper: 'today',          trend: '● 3 expiring', badgeClass: 'bg-[#FEF2F2] text-[#C0392B]' },
    { label: 'Lots won YTD',  value: '47',       icon: 'check',  helper: 'vs last year',   trend: '▲ 12',        badgeClass: 'bg-[#ECFDF5] text-[#2D7A55]' },
    { label: 'Avg cup score', value: '85.4',     icon: 'star',   helper: 'SCAA protocol',  trend: '▲ 1.2pts',    badgeClass: 'bg-[#ECFDF5] text-[#2D7A55]' },
];

const rangeData = {
    '1W': { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], arabica: [4.92,4.88,4.95,5.01,4.97,5.05,5.1],  robusta: [2.2,2.18,2.25,2.28,2.22,2.35,2.4]  },
    '1M': { labels: ['W1','W2','W3','W4'],                       arabica: [4.61,4.74,4.92,5.04],                 robusta: [2.08,2.16,2.24,2.31]                },
    '3M': { labels: ['Jan','Feb','Mar','Apr','May','Jun'],        arabica: [4.28,4.41,4.5,4.72,4.89,5.03],       robusta: [1.92,2.01,2.08,2.15,2.24,2.33]     },
    '1Y': { labels: ['Q1','Q2','Q3','Q4'],                        arabica: [4.02,4.28,4.64,5.02],                 robusta: [1.81,1.94,2.12,2.29]               },
};

const summaryCards = [
    { title: 'Arabica Lots Won', amount: '$47,280', lastMonth: '$39,485 USD', accentClass: 'text-[#1B6E4B]', iconBg: 'bg-[#F0FBF5]' },
    { title: 'Robusta Lots Won', amount: '$31,440', lastMonth: '$28,210 USD', accentClass: 'text-[#C8862A]', iconBg: 'bg-[#FFF8F0]' },
];

const lots = [
    { id: 'BOE-0441', name: 'Sipi Falls AA',        origin: 'Bugisu · Washed · 1,900m',    type: 'arabica', score: '87.2', price: '$5.10', delta: '▲ 1.2%', deltaClass: 'text-[#2D7A55]', availability: 42, sparkline: '0,20 12,17 24,13 36,15 48,9 60,7 72,5'   },
    { id: 'BOE-0438', name: 'Kasese Natural',        origin: 'Rwenzori · Natural · 2,100m', type: 'arabica', score: '85.8', price: '$4.65', delta: '▲ 0.8%', deltaClass: 'text-[#2D7A55]', availability: 18, sparkline: '0,13 12,15 24,11 36,9 48,12 60,8 72,6'   },
    { id: 'BOE-0429', name: 'Kibale Fine Robusta',   origin: 'Mubende · Natural · 1,300m',  type: 'robusta', score: '82.0', price: '$2.40', delta: '▲ 3.1%', deltaClass: 'text-[#2D7A55]', availability: 78, sparkline: '0,21 12,19 24,21 36,17 48,15 60,13 72,10' },
    { id: 'BOE-0435', name: 'Kisoro Highlands AB',   origin: 'Kisoro · Honey · 2,000m',     type: 'arabica', score: '86.4', price: '$4.82', delta: '▼ 0.4%', deltaClass: 'text-[#C0392B]', availability: 65, sparkline: '0,17 12,15 24,13 36,16 48,11 60,13 72,9'  },
    { id: 'BOE-0421', name: 'West Nile FAQ Grade 1', origin: 'Arua · Natural · 1,000m',     type: 'robusta', score: '79.4', price: '$1.98', delta: '▲ 0.3%', deltaClass: 'text-[#2D7A55]', availability: 90, sparkline: '0,17 12,19 24,16 36,17 48,15 60,16 72,14' },
];

const statusStyle = {
    pending:    { accent: 'text-[#C0392B]', bg: 'bg-[#FEF2F2]', icon: 'wallet'  },
    processing: { accent: 'text-[#C8862A]', bg: 'bg-[#FFF8F0]', icon: 'shield'  },
    approved:   { accent: 'text-[#1B6E4B]', bg: 'bg-[#F0FBF5]', icon: 'shield'  },
    completed:  { accent: 'text-[#2D7A55]', bg: 'bg-[#ECFDF5]', icon: 'pin'     },
    rejected:   { accent: 'text-[#C0392B]', bg: 'bg-[#FEF2F2]', icon: 'message' },
};

const cap = s => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';

const actionItems = computed(() => props.lotRequests.map(req => {
    const style = statusStyle[req.status] ?? { accent: 'text-[#6B7280]', bg: 'bg-[#F9FAFB]', icon: 'wallet' };
    const crop = [req.crop_type, req.variety].filter(Boolean).map(cap).join(' · ');
    return {
        id:       req.id,
        title:    crop || 'Lot Request',
        status:   cap(req.status),
        notes:    req.notes || null,
        grade:    req.grade    ? cap(req.grade)                            : null,
        quantity: req.quantity ? `${req.quantity} kg`                      : null,
        amount:   req.amount   ? `$${Number(req.amount).toLocaleString()}` : null,
        ...style,
    };
}));

const regionVolumes = [
    { label: 'Bugisu',    value: 38, color: '#C8862A' },
    { label: 'Rwenzori',  value: 22, color: '#1B6E4B' },
    { label: 'West Nile', value: 19, color: '#52B788' },
    { label: 'Mubende',   value: 12, color: '#8B5CF6' },
    { label: 'Other',     value: 9,  color: '#60A5FA' },
];

const displayedRange = computed(() => rangeData[selectedRange.value]);
const maxChartValue  = computed(() => Math.max(...displayedRange.value.arabica, ...displayedRange.value.robusta));
const chartBars      = computed(() => displayedRange.value.labels.map((label, i) => ({ label, arabica: displayedRange.value.arabica[i], robusta: displayedRange.value.robusta[i] })));
const filteredLots   = computed(() => selectedType.value === 'all' ? lots : lots.filter(l => l.type === selectedType.value));

// ── Quick Buy modal ────────────────────────────────────────────
const showQuickBuy = ref(false);

// ── Role dialog ────────────────────────────────────────────────
const roleIconMap = { user: User, farmer: Crop, buyer: ShoppingCart, exporter: Van, investor: TrendCharts, admin: Setting };

const showRoleDialog  = ref(props.hasProfile && !props.hasRole);
const selectedRole    = ref(user.value?.role ?? null);

watch(showRoleDialog, (open) => { if (open) selectedRole.value = user.value?.role ?? null; });

const roleForm = useForm({ role: '' });

const submitRoleForm = () => {
    roleForm.role = selectedRole.value ?? '';
    roleForm.post(route('profile.role'), {
        preserveScroll: true,
        preserveState:  true,
        onSuccess: () => {
            ElMessage.success('Role selected successfully.');
            showRoleDialog.value = false;
        },
        onError: () => {
            showRoleDialog.value = true;
        },
    });
};

// ── Profile dialog ─────────────────────────────────────────────
const showProfileDialog  = ref(!props.hasProfile);
const existingProfile    = computed(() => page.props.auth?.user?.profile ?? null);

const profileForm = useForm({
    bio:            '',
    date_of_birth:  '',
    gender:         '',
    address_line_1: '',
    address_line_2: '',
    city:           '',
    state:          '',
    country:        '',
    postal_code:    '',
});

const hydrateProfileForm = () => {
    profileForm.defaults({
        bio:            existingProfile.value?.bio            ?? '',
        date_of_birth:  existingProfile.value?.date_of_birth  ?? '',
        gender:         existingProfile.value?.gender          ?? '',
        address_line_1: existingProfile.value?.address_line_1 ?? '',
        address_line_2: existingProfile.value?.address_line_2 ?? '',
        city:           existingProfile.value?.city            ?? '',
        state:          existingProfile.value?.state           ?? '',
        country:        existingProfile.value?.country         ?? 'Uganda',
        postal_code:    existingProfile.value?.postal_code     ?? '',
    });
    profileForm.reset();
    profileForm.clearErrors();
};

const submitProfileForm = () => {
    profileForm.post(route('profile.store'), {
        preserveScroll: true,
        preserveState:  true,
        onSuccess: () => {
            ElMessage.success('Profile saved successfully.');
            showProfileDialog.value = false;
            hydrateProfileForm();
            if (!props.hasRole) showRoleDialog.value = true;
        },
        onError: () => {
            showProfileDialog.value = true;
        },
    });
};

watch(showProfileDialog, (open) => { if (open) hydrateProfileForm(); });

// ── Shell helpers ──────────────────────────────────────────────
const logout           = () => router.post(route('logout'));
const toggleMobileMenu = () => { mobileMenuOpen.value = !mobileMenuOpen.value; };
const closeMobileMenu  = () => { mobileMenuOpen.value = false; };

const syncMobileNavState = () => {
    showMobileMenuButton.value = window.innerWidth < 1024;
    if (!showMobileMenuButton.value) closeMobileMenu();
};

onMounted(() => {
    document.documentElement.classList.add('app-layout-scrollless');
    document.body.classList.add('app-layout-scrollless');
    syncMobileNavState();
    window.addEventListener('resize', syncMobileNavState);
});

onBeforeUnmount(() => {
    document.documentElement.classList.remove('app-layout-scrollless');
    document.body.classList.remove('app-layout-scrollless');
    window.removeEventListener('resize', syncMobileNavState);
});
</script>

<template>
    <div class="dashboard-shell flex min-h-screen flex-col overflow-x-hidden bg-page text-ink">
        <Head :title="title" />

        <!-- ── Top header ─────────────────────────────────────── -->
        <header class="fixed inset-x-0 top-0 z-30 flex h-14 flex-shrink-0 items-stretch bg-white">
            <div class="hidden h-full w-16 flex-shrink-0 items-center justify-center bg-[#212529] lg:flex">
                <div class="flex h-9 w-9 items-center justify-center">
                    <ApplicationMark class="h-8 w-8" />
                </div>
            </div>

            <div class="flex min-w-0 flex-1 items-center border-b border-line bg-white">
                <button v-if="showMobileMenuButton" type="button" class="shell-icon-button ml-3 lg:hidden" @click="toggleMobileMenu">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>
                    </svg>
                </button>

                <Link :href="route('dashboard')" class="flex h-full min-w-0 items-center gap-2 px-3 no-underline text-[#111827] sm:min-w-[170px] sm:px-4" @click="closeMobileMenu">
                    <div>
                        <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#111827]">Commodity</div>
                        <div class="font-display text-[13px] font-bold leading-tight text-[#111827]">Origin</div>
                    </div>
                </Link>

                <nav class="hidden h-full items-center gap-1 px-4 md:flex">
                    <template v-for="link in topNavLinks" :key="link.label">
                        <Link v-if="link.inertia" :href="link.href" class="shell-top-link" :class="{ active: link.active }">
                            {{ link.label }}
                        </Link>
                        <a v-else :href="link.href" class="shell-top-link" :class="{ active: link.active }">
                            {{ link.label }}
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
                            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>

                    <Link :href="route('checkout.index')" class="shell-icon-button">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/>
                            <path d="M3 4h2l2.2 10.2a1 1 0 00.98.8h8.72a1 1 0 00.97-.76L21 8H7"/>
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
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 01-3.46 0"/>
                        </svg>
                        <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-dn"></span>
                    </button>

                    <Dropdown align="right" width="48" :content-classes="['border border-[#E5E7EB] bg-white py-2 shadow-[0_20px_60px_rgba(17,24,39,0.12)]']">
                        <template #trigger>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-gold font-display text-[11px] font-bold text-white transition-transform hover:scale-[1.03]" :title="user?.name">
                                {{ userInitials }}
                            </button>
                        </template>
                        <template #content>
                            <div class="px-4 pb-3 pt-2">
                                <div class="truncate text-[12px] font-semibold text-[#111827]">{{ user?.name }}</div>
                                <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">{{ user?.role || 'Account' }}</div>
                            </div>
                            <div class="mx-3 h-px bg-[#E5E7EB]"></div>
                            <div class="px-2 py-2">
                                <Link :href="route('profile.show')" class="account-menu-link">
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19a7 7 0 0114 0"/></svg>
                                    Profile settings
                                </Link>
                                <Link :href="route('dashboard')" class="account-menu-link">
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.25"/><rect x="14" y="3" width="7" height="7" rx="1.25"/><rect x="3" y="14" width="7" height="7" rx="1.25"/><rect x="14" y="14" width="7" height="7" rx="1.25"/></svg>
                                    Dashboard
                                </Link>
                                <Link :href="route('home')" class="account-menu-link">
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
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
                                    <svg class="account-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 3h3a2 2 0 012 2v14a2 2 0 01-2 2h-3"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
                                    Log out
                                </button>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </header>

        <div v-if="mobileMenuOpen" class="fixed inset-0 z-40 bg-[#111827]/45 backdrop-blur-[1px] lg:hidden" @click="closeMobileMenu"></div>

        <div class="flex flex-1 overflow-visible pt-14" id="icon-menu">

            <IconMenu />

            <AppAside />

            <!-- ── Main content ──────────────────────────────── -->
            <main class="min-w-0 flex-1 overflow-visible lg:ml-72">
                <div class="min-w-0" :class="props.flush ? 'w-full max-w-none' : (props.fullWidth ? 'w-full max-w-none p-3 sm:p-5 lg:p-6' : 'p-3 sm:p-5 lg:p-6')">

                    <div class="flex flex-col gap-3 xl:flex-row xl:gap-4">

                        <!-- Left column -->
                        <div class="flex min-w-0 flex-1 flex-col gap-3">

                            <!-- Page header -->
                            <div class="flex flex-col gap-3 rounded-xl border border-[#E5E7EB] bg-white px-4 py-3 sm:px-5 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <h1 class="font-display text-[20px] font-bold leading-none tracking-tight text-[#111827]">
                                        {{ user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'My' }} Dashboard
                                    </h1>
                                    <p class="mt-1 max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                                        Uganda Coffee Commodity Exchange · From Farm to Cup, Transparently
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                    <button class="dashboard-secondary-btn" @click="showQuickBuy = true">
                                        <svg class="dashboard-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                                            <path d="M3 6h18"/>
                                            <path d="M16 10a4 4 0 01-8 0"/>
                                        </svg>
                                        Request Lot
                                    </button>
                                    <button class="dashboard-secondary-btn">
                                        <svg class="dashboard-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z"/>
                                            <path d="M9 19V9a2 2 0 012-2h2a2 2 0 012 2v10"/>
                                            <path d="M15 19a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/>
                                        </svg>
                                        Reports
                                    </button>
                                    <Link :href="route('bid.index')" class="dashboard-primary-btn no-underline">
                                        <svg class="dashboard-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14"/>
                                            <path d="M5 12l7 7 7-7"/>
                                        </svg>
                                        Place Bid
                                    </Link>
                                </div>
                            </div>

                            <!-- Stats row -->
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
                                <div v-for="stat in stats" :key="stat.label" class="stat-card rounded-xl border border-[#E5E7EB] bg-white p-3.5">
                                    <div class="mb-2 flex items-start justify-between">
                                        <span class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#6B7280]">{{ stat.label }}</span>
                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF8F0] text-[#C8862A]">
                                            <svg v-if="stat.icon === 'layers'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                            <svg v-else-if="stat.icon === 'pulse'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                            <svg v-else-if="stat.icon === 'check'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <svg v-else                            class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        </div>
                                    </div>
                                    <div class="font-display text-[22px] font-bold leading-none tracking-tight text-[#111827]">{{ stat.value }}</div>
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <span class="rounded px-1.5 py-0.5 font-mono text-[9px]" :class="stat.badgeClass">{{ stat.trend }}</span>
                                        <span class="text-[11px] text-[#6B7280]">{{ stat.helper }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Price chart -->
                            <div class="rounded-xl border border-[#E5E7EB] bg-white p-3.5 sm:p-4">
                                <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <h2 class="font-display text-[16px] font-bold tracking-tight text-[#111827]">Lot Price Overview</h2>
                                        <p class="text-[12px] leading-relaxed text-[#6B7280]">
                                            Last 7 days buy and bid overview.
                                            <a href="#" class="font-medium text-[#C8862A] no-underline hover:underline" @click.prevent>Detailed Stats</a>
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <button v-for="range in Object.keys(rangeData)" :key="range" class="range-btn" :class="{ active: selectedRange === range }" @click="selectedRange = range">
                                            {{ range }}
                                        </button>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <div class="grid h-[208px] min-w-[470px] grid-cols-7 items-end gap-2.5 rounded-xl bg-[#FAFAFA] px-3.5 pb-5 pt-3.5 sm:min-w-0">
                                        <div v-for="bar in chartBars" :key="bar.label" class="flex h-full flex-col justify-end gap-1">
                                            <div class="flex h-full items-end gap-1">
                                                <div class="w-1/2 rounded-t-[3px] bg-[#1B6E4B]/75" :style="{ height: `${(bar.arabica / maxChartValue) * 100}%` }"></div>
                                                <div class="w-1/2 rounded-t-[3px] bg-[#C8862A]/70" :style="{ height: `${(bar.robusta / maxChartValue) * 100}%` }"></div>
                                            </div>
                                            <div class="text-center font-mono text-[8px] text-[#8A7A6A]">{{ bar.label }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 grid grid-cols-1 gap-2.5 xl:grid-cols-2">
                                    <div v-for="summary in summaryCards" :key="summary.title" class="flex flex-col items-start gap-3 rounded-xl border border-[#E5E7EB] bg-white p-3.5 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <div class="font-display text-[22px] font-bold leading-none tracking-tight" :class="summary.accentClass">
                                                {{ summary.amount }} <span class="text-[13px] font-normal">USD</span>
                                            </div>
                                            <div class="mt-1 text-[11px] text-[#6B7280]">
                                                Last month <span class="font-medium text-[#111827]">{{ summary.lastMonth }}</span>
                                            </div>
                                            <div class="mt-2 text-[13px] font-medium" :class="summary.accentClass">{{ summary.title }}</div>
                                        </div>
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-[#E5E7EB]" :class="summary.iconBg">
                                            <svg class="h-6 w-6" :class="summary.accentClass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M17 8h1a4 4 0 010 8h-1"/><path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Lots table -->
                            <div class="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                                <div class="flex flex-col gap-3 border-b border-[#E5E7EB] px-3.5 py-3 sm:px-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="font-display text-[14px] font-bold tracking-tight text-[#111827]">Active Market Lots</h3>
                                        <p class="text-[11px] text-[#6B7280]">Live listings on the exchange · 312 total</p>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button class="table-filter-btn" :class="{ active: selectedType === 'all' }"     @click="selectedType = 'all'">All</button>
                                        <button class="table-filter-btn" :class="{ active: selectedType === 'arabica' }" @click="selectedType = 'arabica'">Arabica</button>
                                        <button class="table-filter-btn" :class="{ active: selectedType === 'robusta' }" @click="selectedType = 'robusta'">Robusta</button>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[760px] border-collapse">
                                        <thead>
                                            <tr class="bg-[#F9FAFB]">
                                                <th class="dashboard-th">Lot ID</th>
                                                <th class="dashboard-th">Coffee &amp; Origin</th>
                                                <th class="dashboard-th">Type</th>
                                                <th class="dashboard-th">Score</th>
                                                <th class="dashboard-th">Trend</th>
                                                <th class="dashboard-th">Price</th>
                                                <th class="dashboard-th">Avail.</th>
                                                <th class="border-b border-[#E5E7EB]"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in filteredLots" :key="lot.id" class="lot-row cursor-pointer">
                                                <td class="dashboard-td">
                                                    <div class="font-mono text-[9px] font-medium text-[#C8862A]">{{ lot.id }}</div>
                                                </td>
                                                <td class="dashboard-td">
                                                    <div class="text-[12px] font-medium text-[#111827]">{{ lot.name }}</div>
                                                    <div class="font-mono text-[9px] text-[#6B7280]">{{ lot.origin }}</div>
                                                </td>
                                                <td class="dashboard-td">
                                                    <span class="rounded-md border px-2 py-0.5 font-mono text-[8px] uppercase tracking-[0.08em]" :class="lot.type === 'arabica' ? 'border-[#E5E7EB] bg-[#F0FBF5] text-[#1B6E4B]' : 'border-[#E5E7EB] bg-[#FFF5EB] text-[#7A4F1A]'">
                                                        {{ lot.type }}
                                                    </span>
                                                </td>
                                                <td class="dashboard-td">
                                                    <div class="font-mono text-[11px] font-bold" :class="lot.type === 'arabica' ? 'text-[#2D7A55]' : 'text-[#C8862A]'">{{ lot.score }}</div>
                                                </td>
                                                <td class="dashboard-td">
                                                    <svg class="h-[26px] w-[72px]" viewBox="0 0 72 26">
                                                        <polyline :points="lot.sparkline" fill="none" :stroke="lot.type === 'arabica' ? '#2D7A55' : '#C8862A'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </td>
                                                <td class="dashboard-td">
                                                    <div class="font-mono text-[12px] font-bold text-[#111827]">{{ lot.price }}</div>
                                                    <div class="font-mono text-[9px]" :class="lot.deltaClass">{{ lot.delta }}</div>
                                                </td>
                                                <td class="dashboard-td">
                                                    <div class="avail-bar">
                                                        <div class="avail-fill" :class="{ low: lot.availability < 20 }" :style="{ width: `${lot.availability}%` }"></div>
                                                    </div>
                                                </td>
                                                <td class="dashboard-td">
                                                    <button class="bid-btn">Bid now</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div><!-- /left column -->

                        <!-- Right sidebar -->
                        <div class="flex w-full flex-shrink-0 flex-col gap-3 xl:w-72">

                            <!-- Lot Requests -->
                            <div class="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                                <div class="flex items-center justify-between border-b border-[#E5E7EB] px-3.5 py-2.5">
                                    <h3 class="font-display text-[14px] font-bold tracking-tight text-[#111827]">Lot Requests</h3>
                                    <div class="flex items-center gap-2">
                                        <span v-if="actionItems.length" class="rounded-full bg-[#F3F4F6] px-1.5 py-0.5 font-mono text-[10px] text-[#6B7280]">{{ actionItems.length }}</span>
                                        <Link :href="route('lot.requests.index')" class="font-mono text-[10px] font-semibold text-[#004532] no-underline hover:underline">View all →</Link>
                                    </div>
                                </div>

                                <div v-if="!actionItems.length" class="px-3.5 py-5 text-center font-mono text-[12px] text-[#9CA3AF]">No lot requests yet.</div>

                                <Link v-for="item in actionItems" :key="item.id"
                                   :href="route('lot.request.show', item.id)"
                                   class="action-row flex items-center gap-2.5 border-b border-[#E5E7EB]/60 px-3.5 py-3 no-underline last:border-b-0">
                                    <!-- Status dot -->
                                    <div class="h-2 w-2 flex-shrink-0 rounded-full" :class="item.accent.replace('text-', 'bg-')"></div>

                                    <!-- Main info -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="truncate text-[13px] font-semibold text-[#111827]">{{ item.title }}</span>
                                            <span class="flex-shrink-0 font-mono text-[10px]" :class="item.accent">{{ item.status }}</span>
                                        </div>
                                        <div class="mt-0.5 flex items-center gap-1.5 font-mono text-[11px] text-[#9CA3AF]">
                                            <span v-if="item.grade">{{ item.grade }}</span>
                                            <span v-if="item.grade && (item.quantity || item.amount)" class="text-[#D1D5DB]">·</span>
                                            <span v-if="item.quantity">{{ item.quantity }}</span>
                                            <span v-if="item.quantity && item.amount" class="text-[#D1D5DB]">·</span>
                                            <span v-if="item.amount" class="font-medium text-[#374151]">{{ item.amount }}</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <!-- Volume by Region -->
                            <div class="rounded-xl border border-[#E5E7EB] bg-white p-3.5">
                                <div class="mb-3">
                                    <h3 class="font-display text-[13px] font-bold tracking-tight text-[#111827]">Volume by Region</h3>
                                    <p class="font-mono text-[9px] text-[#6B7280]">47,200 kg traded this month</p>
                                </div>
                                <div class="mx-auto mb-4 flex h-[112px] w-[112px] items-center justify-center rounded-full border-[14px] border-[#F3F4F6] sm:h-[130px] sm:w-[130px] sm:border-[16px]">
                                    <div class="text-center">
                                        <div class="font-display text-[22px] font-bold text-[#111827]">47.2K</div>
                                        <div class="font-mono text-[9px] text-[#6B7280]">kg</div>
                                    </div>
                                </div>
                                <div class="grid gap-1.5">
                                    <div v-for="region in regionVolumes" :key="region.label" class="flex items-center gap-2 border-b border-[#E5E7EB]/50 py-1 last:border-b-0">
                                        <div class="h-2.5 w-2.5 flex-shrink-0 rounded-sm" :style="{ backgroundColor: region.color }"></div>
                                        <span class="flex-1 text-[11px] text-[#111827]">{{ region.label }}</span>
                                        <div class="prog-bar w-20 flex-shrink-0">
                                            <div class="prog-fill" :style="{ width: `${region.value}%`, backgroundColor: region.color }"></div>
                                        </div>
                                        <span class="w-7 text-right font-mono text-[10px] text-[#6B7280]">{{ region.value }}%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- UCDA badge -->
                            <div class="flex items-center gap-3 rounded-xl border border-[#E5E7EB] bg-white p-3.5">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[#C8862A] text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M9 12l2 2 4-4"/>
                                        <path d="M21 12c0 5.591-3.824 10.29-9 11.622C6.824 22.29 3 17.591 3 12c0-1.042.133-2.052.382-3.016A11.955 11.955 0 0112 5.944a11.955 11.955 0 018.618 3.04A12.02 12.02 0 0121 12z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-display text-[12px] font-bold text-[#111827]">UCDA Verified Trader</div>
                                    <div class="mt-0.5 font-mono text-[9px] text-[#6B7280]">ID: TRD-UGA-20240188</div>
                                    <div class="mt-1 font-mono text-[9px] text-[#C8862A]">● Active · Pearl of Africa</div>
                                </div>
                            </div>

                        </div><!-- /right sidebar -->

                    </div><!-- /xl:flex-row -->








                    <QuickBuyModal v-model="showQuickBuy" :crop-grades="props.cropGrades" />

                    <!-- ── Edit Profile Dialog ───────────────────────── -->
                    <el-dialog
                        v-model="showProfileDialog"
                        width="min(680px, calc(100vw - 2rem))"
                        class="profile-edit-dialog"
                        destroy-on-close
                        align-center
                        :close-on-click-modal="false"
                        :show-close="true"
                    >
                        <template #header>
                            <div class="pr-8">

                                <div class="mt-0.5 text-[17px] font-bold tracking-tight text-[#111827]">Add Profile</div>
                            </div>
                        </template>

                        <el-form label-position="top" class="grid gap-x-5 gap-y-2 sm:grid-cols-2 px-4 py-3">

                            <el-form-item label="Date of birth" class="mr-2">
                                <el-date-picker v-model="profileForm.date_of_birth" type="date" placeholder="Select date" class="!w-full" value-format="YYYY-MM-DD" />

                                <InputError :message="profileForm.errors.date_of_birth" class="profile-input-error mt-4" />

                            </el-form-item>

                            <el-form-item label="Gender">
                                <el-select v-model="profileForm.gender" placeholder="Select gender" class="!w-full">
                                    <el-option label="Male"              value="male" />
                                    <el-option label="Female"            value="female" />
                                    <el-option label="Prefer not to say" value="prefer_not_to_say" />
                                </el-select>
                                <InputError :message="profileForm.errors.gender" class="profile-input-error mt-2" />
                            </el-form-item>

                            <el-form-item label="Address line 1" class="mr-2">
                                <el-input v-model="profileForm.address_line_1" placeholder="Street, village, or plot" />
                                <InputError :message="profileForm.errors.address_line_1" class="profile-input-error mt-2" />
                            </el-form-item>

                            <el-form-item label="Address line 2">
                                <el-input v-model="profileForm.address_line_2" placeholder="Apartment or landmark" />
                                <InputError :message="profileForm.errors.address_line_2" class="profile-input-error mt-2" />
                            </el-form-item>

                            <el-form-item label="City" class="mr-2">
                                <el-input v-model="profileForm.city" placeholder="City or town" />
                                <InputError :message="profileForm.errors.city" class="profile-input-error mt-2" />
                            </el-form-item>

                            <el-form-item label="State / District">
                                <el-input v-model="profileForm.state" placeholder="District or region" />
                                <InputError :message="profileForm.errors.state" class="profile-input-error mt-2" />
                            </el-form-item>

                            <el-form-item label="Country" class="mr-2">
                                <el-input v-model="profileForm.country" placeholder="Country" />
                                <InputError :message="profileForm.errors.country" class="profile-input-error mt-2" />
                            </el-form-item>

                            <el-form-item label="Postal code">
                                <el-input v-model="profileForm.postal_code" placeholder="Postal code" />
                                <InputError :message="profileForm.errors.postal_code" class="profile-input-error mt-2" />
                            </el-form-item>

                            <el-form-item label="Short bio" class="sm:col-span-2">
                                <el-input
                                    v-model="profileForm.bio"
                                    type="textarea"
                                    :rows="2"
                                    resize="none"
                                    placeholder="Tell us about yourself or your trading desk"
                                />
                                <InputError :message="profileForm.errors.bio" class="profile-input-error mt-1" />
                            </el-form-item>

                        </el-form>

                        <template #footer>
                            <div class="flex justify-end gap-2 px-4 pb-1">

                                <button
                                    type="button"
                                    class="dashboard-primary-btn min-w-[130px]"
                                    :disabled="profileForm.processing"
                                    @click="submitProfileForm"
                                >
                                    <svg v-if="profileForm.processing" class="dashboard-btn-icon animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                                    {{ profileForm.processing ? 'Saving…' : 'Save Profile' }}
                                </button>
                            </div>
                        </template>
                    </el-dialog>

                    <!-- ── Select Role Dialog ──────────────────────── -->
                    <el-dialog
                        v-model="showRoleDialog"
                        width="min(640px, calc(100vw - 2rem))"
                        class="role-select-dialog"
                        destroy-on-close
                        align-center
                        :close-on-click-modal="false"
                        :show-close="false"
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
                                    class="dashboard-primary-btn min-w-[130px]"
                                    :disabled="roleForm.processing"
                                    @click="submitRoleForm"
                                >
                                    <svg v-if="roleForm.processing" class="dashboard-btn-icon animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                                    {{ roleForm.processing ? 'Saving…' : 'Confirm Role' }}
                                </button>
                            </div>
                        </template>
                    </el-dialog>










                </div>
            </main>

        </div>

    </div>
</template>

<style scoped>
:global(html.app-layout-scrollless),
:global(body.app-layout-scrollless) { scrollbar-width:none;-ms-overflow-style:none; }
:global(html.app-layout-scrollless::-webkit-scrollbar),
:global(body.app-layout-scrollless::-webkit-scrollbar) { display:none; }

.dashboard-shell { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; }
.dashboard-shell :deep(.font-display) { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; }
.dashboard-shell :deep(.font-mono)    { font-family:ui-monospace,'SF Mono','Cascadia Code','Segoe UI Mono',Consolas,'Liberation Mono',monospace; }
.dashboard-shell :deep(::-webkit-scrollbar)       { width:3px;height:3px; }
.dashboard-shell :deep(::-webkit-scrollbar-track) { background:#f0f2f5; }
.dashboard-shell :deep(::-webkit-scrollbar-thumb) { background:#d1d5db;border-radius:2px; }
.pulse-green { animation:pulseGreen 2s ease-in-out infinite; }

.shell-icon-button { display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border:1px solid #e5e7eb;border-radius:.5rem;background:#f0f2f5;color:#6b7280;transition:all .15s; }
.shell-icon-button:hover { border-color:rgba(200,134,42,.3);background:#fff8f0;color:#c8862a; }

.account-menu-link,.account-menu-button { display:flex;align-items:center;gap:.625rem;width:100%;border-radius:.5rem;padding:.625rem .75rem;text-align:left;font-size:.875rem;font-weight:500;color:#374151;text-decoration:none;transition:all .15s; }
.account-menu-link:hover,.account-menu-button:hover { background:#fff8f0;color:#c8862a; }
.account-menu-button { border:0;background:transparent;cursor:pointer; }
.account-menu-icon { width:1rem;height:1rem;flex-shrink:0; }


.font-display { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; }
.font-mono    { font-family:ui-monospace,'SF Mono','Cascadia Code','Segoe UI Mono',Consolas,'Liberation Mono',monospace; }

.dashboard-secondary-btn { display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border:1px solid #e5e7eb;background:#fff;color:#6b7280;border-radius:.5rem;padding:.425rem .75rem;font-size:13px;font-weight:500;transition:all .15s; }
.dashboard-secondary-btn:hover { background:#fff8f0;border-color:rgba(200,134,42,.3);color:#c8862a; }
.dashboard-primary-btn { display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border:1px solid #c8862a;background:#c8862a;color:#fff;border-radius:.5rem;padding:.425rem .875rem;font-size:13px;font-weight:500;transition:all .15s; }
.dashboard-primary-btn:hover { background:#e09b3a;border-color:#e09b3a; }
.dashboard-btn-icon { width:1rem;height:1rem;flex-shrink:0; }

.range-btn,.table-filter-btn { border:1px solid #e5e7eb;color:#6b7280;background:#fff;border-radius:.5rem;padding:.3rem .625rem;font-family:ui-monospace,'SF Mono','Cascadia Code','Segoe UI Mono',Consolas,'Liberation Mono',monospace;font-size:9px;letter-spacing:.08em;text-transform:uppercase;transition:all .15s; }
.range-btn:hover,.table-filter-btn:hover { border-color:rgba(200,134,42,.4);background:#fff8f0;color:#c8862a; }
.range-btn.active,.table-filter-btn.active { border-color:#c8862a;background:#c8862a;color:#fff; }

.stat-card { transition:border-color .2s; }
.stat-card:hover { border-color:#c8862a; }

.dashboard-th { border-bottom:1px solid #e5e7eb;padding:.55rem 1rem;text-align:left;font-family:ui-monospace,'SF Mono','Cascadia Code','Segoe UI Mono',Consolas,'Liberation Mono',monospace;font-size:8px;font-weight:400;text-transform:uppercase;letter-spacing:.12em;color:#6b7280; }
.dashboard-td { border-bottom:1px solid rgba(229,231,235,.6);padding:.625rem 1rem; }
.lot-row:hover td { background:#fdfaf5; }

.bid-btn { border:1px solid #e5e7eb;background:#fff;color:#6b7280;border-radius:.5rem;padding:.3rem .625rem;font-family:ui-monospace,'SF Mono','Cascadia Code','Segoe UI Mono',Consolas,'Liberation Mono',monospace;font-size:9px;text-transform:uppercase;letter-spacing:.08em;transition:all .2s; }
.bid-btn:hover { background:#c8862a;border-color:#c8862a;color:#fff; }

.avail-bar  { width:60px;height:3px;background:#e5e7eb;border-radius:2px;overflow:hidden; }
.avail-fill { height:100%;background:#c8862a;border-radius:2px; }
.avail-fill.low { background:#c0392b; }

.action-row { transition:background .15s; }
.action-row:hover { background:#fdfaf5; }

.prog-bar  { height:3px;background:#e5e7eb;border-radius:2px;overflow:hidden; }
.prog-fill { height:100%;border-radius:2px; }

@media (max-width:639px) {
    .dashboard-secondary-btn,.dashboard-primary-btn { flex:1 1 calc(50% - .5rem);min-width:140px; }
    .dashboard-th,.dashboard-td { padding-left:.875rem;padding-right:.875rem; }
}

.shell-top-link {
    display:flex;align-items:center;height:100%;
    border-bottom:2px solid transparent;
    padding:.375rem .75rem;font-size:13px;font-weight:500;
    color:#6b7280;text-decoration:none;
    transition:color .15s ease,border-color .15s ease;
}
.shell-top-link:hover { color:#111827;border-color:#d1d5db; }
.shell-top-link.active { color:#c8862a;border-color:#c8862a; }

@keyframes pulseGreen {
    0%, 100% { opacity:1; }
    50%       { opacity:0.4; }
}

/* ── Profile edit dialog ── */
:deep(.profile-edit-dialog),
:deep(.profile-edit-dialog .el-dialog) {
    border-radius: 16px;
    overflow: hidden;
    --el-color-primary: #6b7280;
    --el-color-primary-light-3: #dbe1e6;
    --el-color-primary-light-5: #dbe1e6;
    --el-input-focus-border-color: #dbe1e6;
    --el-border-color-hover: #cdd5dc;
    --el-fill-color-blank: #ffffff;
}
:deep(.profile-edit-dialog .el-dialog__header) { margin-right:0;padding:14px 18px 10px; }
:deep(.profile-edit-dialog .el-dialog__body)   { padding:0; }
:deep(.profile-edit-dialog .el-dialog__footer) { padding:8px 18px 14px; }

:deep(.profile-edit-dialog .el-input__wrapper),
:deep(.profile-edit-dialog .el-select__wrapper) {
    border-radius: 8px;
    box-shadow: 0 0 0 1px #dbe1e6 inset !important;
    min-height: 36px;
    padding-top: .2rem;
    padding-bottom: .2rem;
}
:deep(.profile-edit-dialog .el-textarea__inner) {
    border-radius: 8px;
    box-shadow: 0 0 0 1px #dbe1e6 inset !important;
    min-height: 64px !important;
    padding: .5rem .7rem;
    line-height: 1.5;
    font-size: 14px;
    color: #111827;
}
:deep(.profile-edit-dialog .el-date-editor.el-input) { border:none !important;box-shadow:none !important;background:transparent !important;padding:0 !important; }
:deep(.profile-edit-dialog .el-date-editor.el-input),
:deep(.profile-edit-dialog .el-select),
:deep(.profile-edit-dialog .el-input) { width:100%; }
:deep(.profile-edit-dialog .el-input__inner),
:deep(.profile-edit-dialog .el-select__selected-item) { font-size:14px;color:#111827; }
:deep(.profile-edit-dialog .el-form-item) { margin-bottom: 0; overflow: visible !important; }
:deep(.profile-edit-dialog .el-form-item__label) { padding-bottom:.3rem;color:#374151;font-size:14px;font-weight:600;line-height:1.4; }
:deep(.profile-edit-dialog .el-form-item__content) { align-items:flex-start; flex-wrap:wrap; overflow:visible !important; }

:deep(.profile-edit-dialog .el-input__wrapper:hover),
:deep(.profile-edit-dialog .el-select__wrapper:hover),
:deep(.profile-edit-dialog .el-textarea__inner:hover) { box-shadow:0 0 0 1px #cdd5dc inset !important; }

:deep(.profile-edit-dialog .el-input__wrapper.is-focus),
:deep(.profile-edit-dialog .el-select__wrapper.is-focused),
:deep(.profile-edit-dialog .el-textarea__inner:focus),
:deep(.profile-edit-dialog .el-date-editor.is-focus .el-input__wrapper),
:deep(.profile-edit-dialog .el-date-editor:focus-within .el-input__wrapper),
:deep(.profile-edit-dialog .el-input__wrapper:focus-within),
:deep(.profile-edit-dialog .el-select__wrapper:focus-within) {
    border-color:#dbe1e6 !important;
    box-shadow:0 0 0 1px #dbe1e6 inset !important;
    outline:none !important;
}

.profile-input-error { width:100%;flex-basis:100%; }
.profile-input-error :deep(p) { font-size:.75rem !important;line-height:1.3;font-weight:600; }

/* ── Role select dialog ── */
:deep(.role-select-dialog),
:deep(.role-select-dialog .el-dialog) { border-radius:16px;overflow:hidden; }
:deep(.role-select-dialog .el-dialog__header) { margin-right:0;padding:14px 18px 10px; }
:deep(.role-select-dialog .el-dialog__body)   { padding:0;max-height:500px;overflow-y:auto; }
:deep(.role-select-dialog .el-dialog__footer) { padding:8px 18px 14px;border-top:1px solid #f3f4f6; }

.role-card {
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    padding: .875rem 1rem;
    background: #fff;
    cursor: pointer;
    transition: border-color .15s, background .15s, box-shadow .15s;
    width: 100%;
}
.role-card:hover { border-color: #c8862a; background: #fffaf4; }
.role-card.selected { border-color: #c8862a; background: #fff8f0; box-shadow: 0 0 0 3px rgba(200,134,42,.12); }
.role-card-name { display:block; font-size: 14px; font-weight: 600; color: #111827; line-height: 1.3; }
.role-card-desc { font-size: 11px; color: #6b7280; line-height: 1.5; }
.role-card-check { flex-shrink:0; color: #c8862a; }
.role-card-icon { color: #9ca3af; transition: color .15s; }
.role-card-icon.active { color: #c8862a; }
.role-card:hover .role-card-icon { color: #c8862a; }
</style>
