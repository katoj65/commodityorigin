<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Bean Origin',
    },
});

const pageRef = ref(null);
const mobileNavOpen = ref(false);

const navLinks = [
    { label: 'Exchange', href: '#top', active: true },
    { label: 'Marketplace', route: 'market.live' },
    { label: 'Market Intelligence', route: 'market.news' },
    { label: 'How It Works', href: '#matchmaker' },
];

let revealObserver;
let revealTimeout;

function initializeRevealAnimation() {
    if (!pageRef.value) {
        return;
    }

    revealTimeout = window.setTimeout(() => {
        const elements = pageRef.value?.querySelectorAll('.wp-reveal') || [];

        elements.forEach((element) => element.classList.add('wp-reveal--armed'));

        revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('wp-reveal--visible');
                        revealObserver?.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.1 },
        );

        elements.forEach((element) => revealObserver?.observe(element));
    }, 60);
}

onMounted(async () => {
    await nextTick();
    initializeRevealAnimation();
});

onBeforeUnmount(() => {
    if (revealTimeout) {
        window.clearTimeout(revealTimeout);
    }

    if (revealObserver) {
        revealObserver.disconnect();
    }
});
</script>

<template>
    <Head :title="props.title">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div id="top" ref="pageRef" class="wp-page min-h-screen bg-[#f7fbf0] text-[#181d17]">
        <!-- HEADER -->
        <header class="fixed top-0 w-full z-50 bg-[#121611]/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.1)]">
            <div class="h-20 max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between">
                <div class="flex items-center gap-2 md:gap-3 flex-shrink-0">
                    <ApplicationMark class="h-9 w-9 md:h-10 md:w-10 flex-shrink-0 wp-invert" />
                    <span class="text-base md:text-lg font-bold text-white tracking-tight whitespace-nowrap">Bean Origin</span>
                </div>

                <nav class="hidden lg:flex items-center gap-8">
                    <template v-for="link in navLinks" :key="link.label">
                        <a
                            v-if="!link.route"
                            :href="link.href"
                            class="text-sm transition-colors no-underline"
                            :class="link.active ? 'text-[#a3f69c] font-semibold' : 'text-[#bfcaba] hover:text-[#a3f69c]'"
                        >{{ link.label }}</a>
                        <Link v-else :href="route(link.route)" class="text-sm text-[#bfcaba] hover:text-[#a3f69c] transition-colors no-underline">{{ link.label }}</Link>
                    </template>
                </nav>

                <div class="flex items-center gap-3 md:gap-6">
                    <div class="hidden md:flex items-center gap-4">
                        <Link :href="route('login')" class="text-xs font-semibold tracking-[0.02em] text-[#bfcaba] hover:text-white uppercase no-underline">Sign In</Link>
                        <Link :href="route('register')" class="text-xs font-semibold tracking-[0.02em] text-[#bfcaba] hover:text-white uppercase no-underline">Create Account</Link>
                    </div>
                    <button
                        type="button"
                        class="w-9 h-9 rounded bg-[#1a2018] flex items-center justify-center border border-[#bfcaba]/20 lg:hidden"
                        @click="mobileNavOpen = !mobileNavOpen"
                    >
                        <span class="material-symbols-outlined text-white text-[18px]">menu</span>
                    </button>
                </div>
            </div>

            <div v-if="mobileNavOpen" class="lg:hidden bg-[#121611] border-t border-[#bfcaba]/10 px-4 py-4 flex flex-col gap-3">
                <Link :href="route('market.live')" class="text-sm text-[#bfcaba] no-underline" @click="mobileNavOpen = false">Marketplace</Link>
                <Link :href="route('market.news')" class="text-sm text-[#bfcaba] no-underline" @click="mobileNavOpen = false">Market Intelligence</Link>
                <a href="#matchmaker" class="text-sm text-[#bfcaba] no-underline" @click="mobileNavOpen = false">How It Works</a>
                <div class="flex items-center gap-4 pt-2 border-t border-[#bfcaba]/10">
                    <Link :href="route('login')" class="text-xs uppercase tracking-[0.1em] text-[#bfcaba] no-underline">Sign In</Link>
                    <Link :href="route('register')" class="bg-[#0d631b] text-white px-3 py-1.5 rounded text-xs uppercase tracking-[0.1em] no-underline">Create Account</Link>
                </div>
            </div>
        </header>

        <main class="w-full pt-20">
            <slot></slot>
        </main>

        <!-- FOOTER -->
        <footer class="w-full bg-[#f1f5eb] mt-12 py-16">
            <div class="max-w-7xl mx-auto px-4 md:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <div class="col-span-1">
                        <div class="flex items-center gap-2 mb-6">
                            <ApplicationMark class="h-8 w-8 flex-shrink-0 opacity-80" />
                            <span class="text-base font-bold text-[#181d17]">Bean Origin</span>
                        </div>
                        <p class="text-sm text-[#40493d]">The premier destination for specialty bean exchange and agricultural market intelligence.</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-[#181d17] mb-6 uppercase tracking-wider">Platform</h4>
                        <ul class="space-y-4 list-none p-0 m-0">
                            <li><Link :href="route('login')" class="text-sm text-[#40493d] hover:text-[#0d631b] transition-colors no-underline">Trading Terminal</Link></li>
                            <li><Link :href="route('origin.index')" class="text-sm text-[#40493d] hover:text-[#0d631b] transition-colors no-underline">Origin Directory</Link></li>
                            <li><Link :href="route('market.live')" class="text-sm text-[#40493d] hover:text-[#0d631b] transition-colors no-underline">Live Pricing</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-[#181d17] mb-6 uppercase tracking-wider">Company</h4>
                        <ul class="space-y-4 list-none p-0 m-0">
                            <li><span class="text-sm text-[#40493d]">Our Mission</span></li>
                            <li><span class="text-sm text-[#40493d]">Compliance</span></li>
                            <li><span class="text-sm text-[#40493d]">Contact Us</span></li>
                        </ul>
                    </div>
                    <div class="flex flex-col gap-6">
                        <div>
                            <h4 class="text-xs font-semibold text-[#181d17] mb-6 uppercase tracking-wider">Connect</h4>
                            <div class="flex gap-4">
                                <span class="material-symbols-outlined text-[#40493d] hover:text-[#0d631b] cursor-pointer transition-colors">public</span>
                                <span class="material-symbols-outlined text-[#40493d] hover:text-[#0d631b] cursor-pointer transition-colors">groups</span>
                                <span class="material-symbols-outlined text-[#40493d] hover:text-[#0d631b] cursor-pointer transition-colors">news</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-t border-[#bfcaba]/40 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-[#40493d]">
                    <p class="m-0">© 2026 Bean Origin Global Exchange. All rights reserved.</p>
                    <div class="flex gap-8">
                        <span class="hover:text-[#0d631b] transition-colors cursor-pointer">Terms of Service</span>
                        <span class="hover:text-[#0d631b] transition-colors cursor-pointer">Privacy Policy</span>
                        <span class="hover:text-[#0d631b] transition-colors cursor-pointer">Cookie Policy</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.wp-page {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    position: relative;
}

.wp-page .wp-invert {
    filter: brightness(0) invert(1);
}

.wp-page .wp-display {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.wp-page .material-symbols-outlined {
    font-family: 'Material Symbols Outlined';
    font-weight: normal;
    font-style: normal;
    font-size: 24px;
    line-height: 1;
    letter-spacing: normal;
    text-transform: none;
    display: inline-block;
    white-space: nowrap;
    word-wrap: normal;
    direction: ltr;
    -webkit-font-smoothing: antialiased;
}

.wp-page .wp-reveal {
    opacity: 0;
    transform: translateY(10px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.wp-page .wp-reveal.wp-reveal--visible {
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    .wp-page .wp-reveal {
        transition: none;
    }
}
</style>
