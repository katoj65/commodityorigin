<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';

defineProps({
    title: {
        type: String,
        default: 'Market Intelligence',
    },
});

const page = usePage();

const isHomeRoute = computed(() => page.url === '/');
const isLiveMarketRoute = computed(() => page.url.startsWith('/live-market'));
const isNewsRoute = computed(() => page.url.startsWith('/news'));
const isOriginsRoute = computed(() => page.url.startsWith('/origins'));
</script>

<template>
    <Head :title="title">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;500;600;700;800&family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="welcome-page min-h-screen bg-bg text-cream overflow-x-hidden scroll-smooth">
        <div class="outer-fixed-shell">
            <div class="bg-surface border-b border-white/[0.06] h-9 flex items-center overflow-hidden outer-ticker">
                <div class="flex-shrink-0 bg-gold text-bg font-mono text-[9px] tracking-[0.14em] uppercase px-4 h-full flex items-center font-medium">Live Prices</div>
                <div class="overflow-hidden flex-1 flex">
                    <div class="ticker-wrap">
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">UGA-ARA-AA</span><span class="font-mono text-[10px] font-medium">$4.82/kg</span><span class="font-mono text-[9px] text-up">▲ +2.3%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">UGA-ROB-G1</span><span class="font-mono text-[10px] font-medium">$2.14/kg</span><span class="font-mono text-[9px] text-up">▲ +0.8%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">BUGISU-AA</span><span class="font-mono text-[10px] font-medium">$5.10/kg</span><span class="font-mono text-[9px] text-red">▼ -0.4%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">RWENZORI-AB</span><span class="font-mono text-[10px] font-medium">$4.65/kg</span><span class="font-mono text-[9px] text-up">▲ +1.1%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">W-NILE-FAQ</span><span class="font-mono text-[10px] font-medium">$1.98/kg</span><span class="font-mono text-[9px] text-up">▲ +0.3%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">KIBALE-FIN</span><span class="font-mono text-[10px] font-medium">$2.40/kg</span><span class="font-mono text-[9px] text-up">▲ +3.1%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">ICE-COFFEE</span><span class="font-mono text-[10px] font-medium">$192.40/lb</span><span class="font-mono text-[9px] text-red">▼ -0.7%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">KISORO-AA</span><span class="font-mono text-[10px] font-medium">$5.34/kg</span><span class="font-mono text-[9px] text-up">▲ +1.8%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">UGA-ARA-AA</span><span class="font-mono text-[10px] font-medium">$4.82/kg</span><span class="font-mono text-[9px] text-up">▲ +2.3%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">UGA-ROB-G1</span><span class="font-mono text-[10px] font-medium">$2.14/kg</span><span class="font-mono text-[9px] text-up">▲ +0.8%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">BUGISU-AA</span><span class="font-mono text-[10px] font-medium">$5.10/kg</span><span class="font-mono text-[9px] text-red">▼ -0.4%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">RWENZORI-AB</span><span class="font-mono text-[10px] font-medium">$4.65/kg</span><span class="font-mono text-[9px] text-up">▲ +1.1%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">W-NILE-FAQ</span><span class="font-mono text-[10px] font-medium">$1.98/kg</span><span class="font-mono text-[9px] text-up">▲ +0.3%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">KIBALE-FIN</span><span class="font-mono text-[10px] font-medium">$2.40/kg</span><span class="font-mono text-[9px] text-up">▲ +3.1%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">ICE-COFFEE</span><span class="font-mono text-[10px] font-medium">$192.40/lb</span><span class="font-mono text-[9px] text-red">▼ -0.7%</span></div>
                        <div class="inline-flex items-center gap-2 px-7 border-r border-white/[0.06]"><span class="font-mono text-[10px] text-cream/80 tracking-[0.1em]">KISORO-AA</span><span class="font-mono text-[10px] font-medium">$5.34/kg</span><span class="font-mono text-[9px] text-up">▲ +1.8%</span></div>
                    </div>
                </div>
            </div>

            <nav class="outer-nav-bar bg-bg/92 backdrop-blur-xl border-b border-white/[0.06] flex flex-col items-start justify-between gap-4 px-4 py-3 sm:px-6 md:h-[60px] md:flex-row md:items-center md:gap-6 md:px-10 md:py-0">
                <div class="flex w-full flex-col items-start gap-4 md:w-auto md:flex-row md:items-center md:gap-10">
                    <Link :href="route('home')" class="flex items-center gap-3 font-display font-bold text-base text-cream no-underline tracking-tight outer-brand-link">
                        <ApplicationMark class="h-8 w-8 flex-shrink-0 outer-brand-mark" />
                        Commodity <span class="text-gold">Origin</span>
                    </Link>
                    <ul class="flex flex-wrap gap-1 list-none">
                        <li>
                            <Link
                                :href="route('home')"
                                class="outer-nav-link font-mono text-[10px] tracking-[0.12em] uppercase px-3 py-1.5 rounded no-underline"
                                :class="isHomeRoute ? 'is-active' : ''"
                            >
                                Home
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="route('market.news')"
                                class="outer-nav-link font-mono text-[10px] tracking-[0.12em] uppercase px-3 py-1.5 rounded no-underline"
                                :class="isNewsRoute ? 'is-active' : ''"
                            >
                                News
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="route('market.live')"
                                class="outer-nav-link font-mono text-[10px] tracking-[0.12em] uppercase px-3 py-1.5 rounded no-underline"
                                :class="isLiveMarketRoute ? 'is-active' : ''"
                            >
                                Live Market
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="route('origin.index')"
                                class="outer-nav-link font-mono text-[10px] tracking-[0.12em] uppercase px-3 py-1.5 rounded no-underline"
                                :class="isOriginsRoute ? 'is-active' : ''"
                            >
                                Coffee Origins
                            </Link>
                        </li>
                    </ul>
                </div>
                <div class="flex w-full flex-wrap items-center gap-3 md:w-auto md:justify-end">
                    <div class="flex items-center gap-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-up pulse-green"></div>
                        <span class="font-mono text-[9px] text-up tracking-[0.1em]">Market open</span>
                    </div>
                    <Link :href="route('login')" class="font-mono text-[9px] tracking-[0.12em] uppercase px-3.5 py-1.5 border border-white/[0.14] text-cream/70 rounded-sm hover:border-gold hover:text-gold transition-all no-underline">Sign in</Link>
                    <Link :href="route('register')" class="font-mono text-[9px] tracking-[0.12em] uppercase px-3.5 py-1.5 bg-gold border border-gold text-bg font-medium rounded-sm hover:bg-gold2 transition-all no-underline">Open account</Link>
                </div>
            </nav>
        </div>

        <div class="bg-white outer-page-content">
            <slot />
        </div>

        <footer class="bg-[#0D0804] border-t border-white/[0.06] px-4 pt-10 sm:px-6 md:px-10">
            <div class="grid grid-cols-1 gap-8 mb-8 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1fr_1fr] lg:gap-10">
                <div>
                    <a href="#" class="flex items-center gap-2 mb-3 no-underline">
                        <div class="w-2 h-2 rounded-full bg-gold"></div>
                        <span class="font-display font-bold text-[15px] text-cream">Commodity <span class="text-gold">Origin</span></span>
                    </a>
                    <p class="text-[13px] text-cream/65 leading-relaxed max-w-[220px]">Uganda's blockchain-verified coffee commodity exchange. From farm to cup, transparently.</p>
                </div>
                <div>
                    <div class="font-mono text-[9px] tracking-[0.18em] uppercase text-cream/40 mb-4">Exchange</div>
                    <ul class="list-none flex flex-col gap-2.5">
                        <li><Link :href="route('market.live')" class="foot-link text-[13px] no-underline">Live market</Link></li>
                        <li><Link :href="route('origin.index')" class="foot-link text-[13px] no-underline">Origins</Link></li>
                        <li><a href="#quality" class="foot-link text-[13px] no-underline">Grading</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Price history</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">API access</a></li>
                    </ul>
                </div>
                <div>
                    <div class="font-mono text-[9px] tracking-[0.18em] uppercase text-cream/40 mb-4">Coffee</div>
                    <ul class="list-none flex flex-col gap-2.5">
                        <li><a href="#" class="foot-link text-[13px] no-underline">Arabica lots</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Robusta lots</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Fine Robusta</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Seasonal lots</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Brew guides</a></li>
                    </ul>
                </div>
                <div>
                    <div class="font-mono text-[9px] tracking-[0.18em] uppercase text-cream/40 mb-4">Company</div>
                    <ul class="list-none flex flex-col gap-2.5">
                        <li><a href="#" class="foot-link text-[13px] no-underline">About us</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">UCDA partnership</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Farmer partners</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Sustainability</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <div class="font-mono text-[9px] tracking-[0.18em] uppercase text-cream/40 mb-4">Legal</div>
                    <ul class="list-none flex flex-col gap-2.5">
                        <li><a href="#" class="foot-link text-[13px] no-underline">Terms of trade</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Privacy policy</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Cookie policy</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Compliance</a></li>
                        <li><a href="#" class="foot-link text-[13px] no-underline">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/[0.06] py-5 flex flex-col gap-3 justify-between items-start md:flex-row md:items-center">
                <span class="font-mono text-[9px] tracking-[0.08em] text-cream/35">© 2024 Commodity Origin Ltd · Kampala, Uganda · hello@commodityorigin.ug</span>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-[9px] tracking-[0.08em] text-cream/35">Pearl of Africa</span>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.welcome-page {
    font-family: 'Manrope', sans-serif;
    position: relative;
}

.outer-fixed-shell {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 60;
    background:
        linear-gradient(180deg, rgba(14, 11, 7, 0.98) 0%, rgba(14, 11, 7, 0.93) 100%);
    box-shadow: 0 14px 40px rgba(0, 0, 0, 0.28);
}

.outer-ticker {
    position: relative;
    z-index: 61;
}

.outer-nav-bar {
    position: relative;
    z-index: 60;
    box-shadow: inset 0 1px 0 rgba(242, 237, 228, 0.04);
}

.outer-page-content {
    padding-top: 96px;
}

.outer-brand-link {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.outer-brand-link:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}

.outer-brand-mark {
    filter: drop-shadow(0 8px 18px rgba(200, 134, 42, 0.18));
}

.outer-nav-link {
    color: rgba(242, 237, 228, 0.4);
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        box-shadow 0.2s ease;
}

.outer-nav-link:hover {
    color: #f2ede4;
    background: rgba(24, 18, 12, 0.88);
}

.outer-nav-link.is-active {
    color: #f2ede4;
    background: rgba(24, 18, 12, 0.92);
    box-shadow: inset 0 0 0 1px rgba(200, 134, 42, 0.16);
}

.welcome-page .font-display {
    font-family: 'Bricolage Grotesque', sans-serif;
}

.welcome-page .font-mono {
    font-family: 'IBM Plex Mono', monospace;
}

.welcome-page [class*='border-white/'] {
    border-color: transparent !important;
}

.welcome-page::before {
    content: '';
    position: fixed;
    inset: 0;
    z-index: 999;
    pointer-events: none;
    background: repeating-linear-gradient(
        0deg,
        rgba(0, 0, 0, 0.025) 0,
        rgba(0, 0, 0, 0.025) 1px,
        transparent 1px,
        transparent 2px
    );
}

::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: #0e0b07;
}

::-webkit-scrollbar-thumb {
    background: #c8862a;
    border-radius: 2px;
}

.welcome-page .ticker-wrap {
    display: flex;
    animation: ticker 30s linear infinite;
    white-space: nowrap;
}

@keyframes ticker {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-50%);
    }
}

@keyframes pulse-green {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(82, 183, 136, 0.4);
    }

    50% {
        box-shadow: 0 0 0 5px rgba(82, 183, 136, 0);
    }
}

.welcome-page .pulse-green {
    animation: pulse-green 2s ease-in-out infinite;
}

.welcome-page .foot-link {
    color: rgba(242, 237, 228, 0.65);
    transition: color 0.2s;
}

.welcome-page .foot-link:hover {
    color: #f2ede4;
}

@media (max-width: 767px) {
    .outer-page-content {
        padding-top: 116px;
    }

    .welcome-page .ticker-wrap {
        animation-duration: 42s;
    }
}
</style>
