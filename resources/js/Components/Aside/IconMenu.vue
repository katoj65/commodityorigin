<script setup>
import { Link } from '@inertiajs/vue3';
import { useRailNavigation } from '@/Composables/useRailNavigation';

const navigation   = useRailNavigation();
const userInitials = navigation.userInitials;
const railLinks    = navigation.railLinks;

function onRailClick(link, event) {
    if (!link.inertia) {
        event.preventDefault();
    }
}
</script>

<template>
    <!-- Icon rail -->
    <div class="dashboard-rail shell-scrollless fixed left-0 top-14 hidden h-[calc(100vh-3.5rem)] w-16 flex-shrink-0 flex-col items-center gap-1 overflow-x-hidden overflow-y-auto border-r border-white/[0.08] py-4 lg:flex">

        <template v-for="link in railLinks" :key="link.label">
            <div v-if="link.dividerBefore" class="my-1.5 h-px w-8 shrink-0 bg-white/10"></div>

             
            <component :is="link.inertia ? Link : 'a'"
                       :href="link.href"
                       class="rail-item group relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl outline-none transition-all duration-150 focus-visible:ring-2 focus-visible:ring-gold/50"
                       :class="link.active ? 'bg-gold text-white shadow-[0_2px_10px_rgba(200,134,42,0.35)]' : 'text-white/45 hover:bg-white/10 hover:text-white'"
                       @click="onRailClick(link, $event)">

                <svg v-if="link.icon === 'grid'"      class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                <svg v-else-if="link.icon === 'cup'"       class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 010 8h-1"/><path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/></svg>
                <svg v-else-if="link.icon === 'card'"      class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><circle cx="9" cy="12" r="2"/></svg>
                <svg v-else-if="link.icon === 'shield'"    class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <svg v-else-if="link.icon === 'clipboard'" class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><path d="M9 5a2 2 0 002 2h2a2 2 0 002-2"/><path d="M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <svg v-else-if="link.icon === 'chart'"     class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z"/><path d="M9 19V9a2 2 0 012-2h2a2 2 0 012 2v10"/><path d="M15 19a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/></svg>
                <svg v-else-if="link.icon === 'bell'"      class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                <svg v-else-if="link.icon === 'user'"      class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/></svg>
                <svg v-else                                class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1.5"/></svg>

                <span v-if="link.dot" class="dot-badge"></span>

                <div class="tooltip">
                    <span class="tooltip-arrow"></span>
                    {{ link.label }}
                </div>
            </component>
        </template>

        <div class="mt-auto flex flex-col items-center gap-2 pt-2">
            <div class="h-px w-8 bg-white/10"></div>
            <div class="avatar-badge flex h-9 w-9 items-center justify-center rounded-full bg-gold font-display text-[11px] font-bold text-white">
                {{ userInitials }}
            </div>
        </div>
    </div>
</template>

<style scoped>
.dashboard-rail { background:#212529; }

.shell-scrollless                    { scrollbar-width:none;-ms-overflow-style:none; }
.shell-scrollless::-webkit-scrollbar { display:none;width:0;height:0; }

.rail-item { position:relative; }

.rail-item .dot-badge {
    position: absolute;
    right: 5px;
    top: 5px;
    height: 8px;
    width: 8px;
    border-radius: 999px;
    border: 2px solid #212529;
    background: #E07070;
}

.rail-item .tooltip {
    position: absolute;
    top: 50%;
    left: calc(100% + 10px);
    transform: translate(-4px, -50%);
    white-space: nowrap;
    border-radius: 5px;
    background: #1a150d;
    padding: 5px 10px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #f2ede4;
    box-shadow: 0 6px 16px rgba(0,0,0,.35);
    opacity: 0;
    pointer-events: none;
    transition: opacity .15s ease, transform .15s ease;
    z-index: 50;
}
.rail-item .tooltip-arrow {
    position: absolute;
    left: -4px;
    top: 50%;
    height: 8px;
    width: 8px;
    background: #1a150d;
    transform: translateY(-50%) rotate(45deg);
}
.rail-item:hover .tooltip,
.rail-item:focus-visible .tooltip {
    opacity: 1;
    transform: translate(0, -50%);
}

.avatar-badge {
    box-shadow: 0 0 0 2px rgba(255,255,255,.08);
    transition: box-shadow .15s ease;
}
.avatar-badge:hover { box-shadow: 0 0 0 2px rgba(200,134,42,.45); }
</style>
