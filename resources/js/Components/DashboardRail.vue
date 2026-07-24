<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

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

const railLinks = computed(() => [
    
    {
        label: 'Home',
        href: route('dashboard'),
        active: route().current('dashboard'),
        inertia: true,
        icon: 'home',
    },
    {
        label: 'Calendar',
        href: route('calendar.index'),
        active: route().current('calendar.*'),
        inertia: true,
        icon: 'calendar',
    },
    {
        label: 'Tasks',
        href: route('task.index'),
        active: route().current('task.*'),
        inertia: true,
        icon: 'tasks',
    },
    {
        label: 'Messages',
        href: route('chat.index'),
        active: route().current('chat.*'),
        inertia: true,
        icon: 'chat',
    },
    {
        label: 'Market',
        href: route('market.index'),
        active: route().current('market.*'),
        inertia: true,
        icon: 'market',
    },
    {
        label: 'Auction',
        href: route('auction.index'),
        active: route().current('auction.*'),
        inertia: true,
        icon: 'auction',
    },
    {
        label: 'Wallet',
        href: route('wallet.index'),
        active: route().current('wallet.*'),
        inertia: true,
        icon: 'wallet',
    },
]);
</script>

<template>
    <div class="dashboard-rail shell-scrollless fixed left-0 top-14 hidden h-[calc(100vh-3.5rem)] w-16 flex-shrink-0 flex-col items-center gap-1 overflow-x-hidden overflow-y-auto border-r border-white/[0.08] py-3 lg:flex">
        <!----First sidebar menu----->
        <template v-for="link in railLinks" :key="link.label">
            <div v-if="link.dividerBefore" class="my-1 h-px w-8 bg-white/10"></div>
            <Link
                v-if="link.inertia"
                :href="link.href"
                class="rail-item group relative flex h-10 w-10 items-center justify-center rounded-xl transition-colors"
                :class="link.active ? 'bg-gold text-white' : 'text-white/50 hover:bg-white/10 hover:text-white'"
            >
                <svg v-if="link.icon === 'home'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 10.5L12 3l9 7.5" />
                    <path d="M5 9.5V20a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V9.5" />
                </svg>
                <svg v-else-if="link.icon === 'calendar'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="5" width="18" height="16" rx="2" />
                    <path d="M16 3v4" />
                    <path d="M8 3v4" />
                    <path d="M3 10h18" />
                </svg>
                <svg v-else-if="link.icon === 'tasks'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                    <path d="M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                    <path d="M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    <path d="M9 13l2 2 4-4" />
                </svg>
                <svg v-else-if="link.icon === 'chat'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4h16v13H9l-5 4V4z" />
                </svg>
                <svg v-else-if="link.icon === 'market'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 9l1.2-4.5A1 1 0 016.16 3.7h11.68a1 1 0 01.96.8L20 9" />
                    <path d="M4 9v10a1 1 0 001 1h14a1 1 0 001-1V9" />
                    <path d="M4 9a2 2 0 004 0 2 2 0 004 0 2 2 0 004 0 2 2 0 004 0" />
                    <path d="M9 20v-6h6v6" />
                </svg>
                <svg v-else-if="link.icon === 'auction'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="8.5" y="6.5" width="9" height="4" rx="1" transform="rotate(45 13 8.5)" />
                    <path d="M9.5 11.5L4 17" />
                    <path d="M13.5 5.5L17 2" />
                    <path d="M3 20h7" />
                </svg>
                <svg v-else-if="link.icon === 'wallet'" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="2" y="7" width="20" height="14" rx="2" />
                    <path d="M2 12h20" />
                    <circle cx="16" cy="16" r="1.25" fill="currentColor" stroke="none" />
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
</template>

<style scoped>
.dashboard-rail {
    background: #212529;
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
</style>
