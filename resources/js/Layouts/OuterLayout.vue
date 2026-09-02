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
const scrolled = ref(false);

const navLinks = [
    { label: 'Exchange', href: '#top', active: true },
    { label: 'Marketplace', route: 'market.live' },
    { label: 'Market Intelligence', route: 'market.news' },
    { label: 'How It Works', href: '#matchmaker' },
];

let revealObserver;
let revealTimeout;

function handleScroll() {
    scrolled.value = window.scrollY > 8;
}

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
    document.documentElement.classList.add('wp-smooth-scroll');
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
});

onBeforeUnmount(() => {
    if (revealTimeout) {
        window.clearTimeout(revealTimeout);
    }

    if (revealObserver) {
        revealObserver.disconnect();
    }

    document.documentElement.classList.remove('wp-smooth-scroll');
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head :title="props.title">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div id="top" ref="pageRef" class="wp-page min-h-screen bg-[#f7fbf0] text-[#181d17]">
        <!-- HEADER -->
        <header
            class="fixed top-0 w-full z-50 backdrop-blur-xl transition-all duration-300"
            :class="scrolled ? 'bg-[#121611]/95 shadow-[0_4px_20px_rgba(0,0,0,0.25)] border-b border-[#bfcaba]/10' : 'bg-[#121611]/80 shadow-[0_1px_8px_rgba(0,0,0,0.1)] border-b border-transparent'"
        >
            <div class="h-20 max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between">
                <a href="#top" class="flex items-center gap-2 md:gap-3 flex-shrink-0 no-underline">
                    <ApplicationMark class="h-9 w-9 md:h-10 md:w-10 flex-shrink-0 wp-invert" />
                    <span class="text-base md:text-lg font-bold text-white tracking-tight whitespace-nowrap">Bean Origin</span>
                </a>

                <nav class="hidden lg:flex items-center gap-8">
                    <template v-for="link in navLinks" :key="link.label">
                        <a
                            v-if="!link.route"
                            :href="link.href"
                            class="wp-nav-link text-sm transition-colors no-underline"
                            :class="link.active ? 'wp-nav-link--active text-[#a3f69c] font-semibold' : 'text-[#bfcaba] hover:text-[#a3f69c]'"
                        >{{ link.label }}</a>
                        <Link v-else :href="route(link.route)" class="wp-nav-link text-sm text-[#bfcaba] hover:text-[#a3f69c] transition-colors no-underline">{{ link.label }}</Link>
                    </template>
                </nav>

                <div class="flex items-center gap-3 md:gap-6">
                    <div class="hidden md:flex items-center gap-4">
                        <Link :href="route('login')" class="text-sm font-medium text-[#bfcaba] hover:text-white no-underline transition-colors">Sign in</Link>
                        <Link
                            :href="route('register')"
                            class="text-sm font-semibold text-[#002204] bg-[#a3f69c] hover:bg-[#88d982] px-4 py-2 rounded no-underline transition-all active:scale-[0.97]"
                        >Create account</Link>
                    </div>
                    <button
                        type="button"
                        aria-label="Toggle navigation menu"
                        class="relative w-9 h-9 rounded bg-[#1a2018] flex items-center justify-center border border-[#bfcaba]/20 lg:hidden transition-colors hover:border-[#a3f69c]/40"
                        @click="mobileNavOpen = !mobileNavOpen"
                    >
                        <span class="wp-burger" :class="{ 'wp-burger--open': mobileNavOpen }">
                            <span></span><span></span><span></span>
                        </span>
                    </button>
                </div>
            </div>

            <Transition name="wp-mobile-nav">
                <div v-if="mobileNavOpen" class="lg:hidden bg-[#121611] border-t border-[#bfcaba]/10 px-4 py-4 flex flex-col gap-1 origin-top">
                    <Link :href="route('market.live')" class="text-sm text-[#bfcaba] hover:text-[#a3f69c] no-underline py-2.5 transition-colors" @click="mobileNavOpen = false">Marketplace</Link>
                    <Link :href="route('market.news')" class="text-sm text-[#bfcaba] hover:text-[#a3f69c] no-underline py-2.5 transition-colors" @click="mobileNavOpen = false">Market Intelligence</Link>
                    <a href="#matchmaker" class="text-sm text-[#bfcaba] hover:text-[#a3f69c] no-underline py-2.5 transition-colors" @click="mobileNavOpen = false">How It Works</a>
                    <div class="flex items-center gap-4 pt-3 mt-2 border-t border-[#bfcaba]/10">
                        <Link :href="route('login')" class="text-sm text-[#bfcaba] no-underline">Sign in</Link>
                        <Link :href="route('register')" class="bg-[#a3f69c] text-[#002204] px-3 py-1.5 rounded text-sm font-semibold no-underline">Create account</Link>
                    </div>
                </div>
            </Transition>
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
                            <div class="flex gap-2">
                                <span class="w-9 h-9 rounded-full bg-[#e5eadf] flex items-center justify-center text-[#40493d] hover:bg-[#0d631b] hover:text-white cursor-pointer transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">public</span>
                                </span>
                                <span class="w-9 h-9 rounded-full bg-[#e5eadf] flex items-center justify-center text-[#40493d] hover:bg-[#0d631b] hover:text-white cursor-pointer transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">groups</span>
                                </span>
                                <span class="w-9 h-9 rounded-full bg-[#e5eadf] flex items-center justify-center text-[#40493d] hover:bg-[#0d631b] hover:text-white cursor-pointer transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">news</span>
                                </span>
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
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    position: relative;
}

.wp-page .wp-invert {
    filter: brightness(0) invert(1);
}

.wp-page .wp-display {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-feature-settings: 'ss01' on, 'cv01' on;
}

html.wp-smooth-scroll {
    scroll-behavior: smooth;
}

/* ── Nav underline indicator ─────────────────────────────────────────── */
.wp-nav-link {
    position: relative;
    padding-bottom: 2px;
}

.wp-nav-link::after {
    content: '';
    position: absolute;
    left: 0;
    right: 100%;
    bottom: -4px;
    height: 2px;
    background: #a3f69c;
    border-radius: 1px;
    transition: right 0.25s ease;
}

.wp-nav-link:hover::after,
.wp-nav-link--active::after {
    right: 0;
}

/* ── Mobile hamburger <-> close animation ────────────────────────────── */
.wp-burger {
    position: relative;
    width: 18px;
    height: 13px;
    display: inline-block;
}

.wp-burger span {
    position: absolute;
    left: 0;
    width: 100%;
    height: 1.5px;
    background: #fff;
    border-radius: 1px;
    transition: transform 0.25s ease, opacity 0.2s ease, top 0.25s ease;
}

.wp-burger span:nth-child(1) { top: 0; }
.wp-burger span:nth-child(2) { top: 5.5px; }
.wp-burger span:nth-child(3) { top: 11px; }

.wp-burger--open span:nth-child(1) { top: 5.5px; transform: rotate(45deg); }
.wp-burger--open span:nth-child(2) { opacity: 0; }
.wp-burger--open span:nth-child(3) { top: 5.5px; transform: rotate(-45deg); }

/* ── Mobile nav panel transition ─────────────────────────────────────── */
.wp-mobile-nav-enter-active,
.wp-mobile-nav-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.wp-mobile-nav-enter-from,
.wp-mobile-nav-leave-to {
    opacity: 0;
    transform: translateY(-6px);
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
