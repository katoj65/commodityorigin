<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const pageTitle = 'Bean Origin: The Digital Exchange for Coffee';

// Deterministic ascending price curve for the hero visual — ties the flagship
// image to the exchange/pricing narrative instead of decorative bean clip art.
function seededHeroCurve(points) {
    const coords = [];
    let seed = 11;
    let level = 92;
    const rand = () => {
        seed = (seed * 9301 + 49297) % 233280;
        return seed / 233280;
    };

    for (let i = 0; i < points; i += 1) {
        level -= 6.5 + rand() * 3.5;
        const noise = (rand() - 0.5) * 6;
        coords.push({
            x: (400 / (points - 1)) * i,
            y: Math.min(96, Math.max(6, level + noise)),
        });
    }

    return coords;
}

const heroCurvePoints = seededHeroCurve(9);
const heroCurvePath = heroCurvePoints
    .map((point, i) => `${i === 0 ? 'M' : 'L'}${point.x.toFixed(1)},${(point.y * 5.5).toFixed(1)}`)
    .join(' ');
const heroCurveFillPath = `${heroCurvePath} L400,550 L0,550 Z`;

const tickerItems = [
    { label: 'Arabica (NY)', value: '184.25', change: '1.32%', tone: 'up' },
    { label: 'Robusta (LON)', value: '3245.00', change: '0.45%', tone: 'down' },
    { label: 'Uganda Bugisu', value: '192.50', change: '0.80%', tone: 'up' },
    { label: 'Ethiopia Yirgacheffe', value: '215.10', change: '2.10%', tone: 'up' },
    { label: 'Brazil Santos', value: '178.90', change: '0.15%', tone: 'down' },
];

const originRegions = [
    { name: 'Bugisu · Mt Elgon', altitude: '1,500-2,200m', variety: 'Arabica', notes: 'SL14, SL28', lots: 18 },
    { name: 'Rwenzori', altitude: '1,400-2,000m', variety: 'Arabica', notes: 'SL14', lots: 9 },
    { name: 'Kisoro', altitude: '1,900-2,300m', variety: 'Arabica', notes: 'Bourbon', lots: 6 },
    { name: 'Mubende', altitude: '1,200-1,500m', variety: 'Robusta', notes: 'Nganda', lots: 12 },
    { name: 'West Nile', altitude: '1,100-1,400m', variety: 'Robusta', notes: 'Erecta', lots: 7 },
    { name: 'Masaka', altitude: '1,100-1,300m', variety: 'Robusta', notes: 'Nganda', lots: 10 },
];

const matchmakerPoints = [
    { icon: 'route', title: 'Predictive Logistics Routing', copy: 'Calculates fastest and cheapest maritime routes automatically.' },
    { icon: 'tune', title: 'Quality Profile Matching', copy: 'Aligns SCA scores and flavor notes with specific buyer demands.' },
];

const esgMetrics = [
    {
        icon: 'co2',
        color: '#0d631b',
        title: 'Carbon Footprint',
        sub: 'Avg. per kg',
        value: '-1.2',
        unit: 'kg CO2e',
        bar: 85,
        copy: '85% of featured lots are carbon negative or neutral at the farm gate.',
    },
    {
        icon: 'monetization_on',
        color: '#b7791f',
        title: 'Farmer Value',
        sub: 'FOB Price Share',
        value: '72',
        unit: '%',
        bar: 72,
        copy: 'Average percentage of final FOB price returned directly to the producing cooperative.',
    },
    {
        icon: 'water_drop',
        color: '#79573f',
        title: 'Water Efficiency',
        sub: 'Processing Usage',
        value: '4.5',
        unit: 'L/kg',
        bar: 90,
        copy: 'One of the most water-efficient washing processes among the lots we verify.',
    },
];

const auctionFilters = [
    { key: 'all', label: 'All' },
    { key: 'east-africa', label: 'East Africa' },
    { key: 'south-america', label: 'South America' },
    { key: 'central-america', label: 'Central America' },
    { key: 'asia-pacific', label: 'Asia Pacific' },
];

const auctionFilter = ref('all');

const auctionRows = [
    { id: 'UG-BUGI-021', origin: 'Uganda Bugisu AA', region: 'east-africa', variety: 'SL14/SL28, Washed', price: '7.35', delta: '1.8%', deltaTone: 'up', volume: '30 Bags', time: '01:05:30', timeTone: 'amber', status: 'Active Auction', statusTone: 'green', action: 'PLACE BID', style: 'solid', iconColor: '#0d631b' },
    { id: 'ET-YIRG-042', origin: 'Ethiopia Yirgacheffe G1', region: 'east-africa', variety: 'Arabica SL28, AA', price: '6.45', delta: '0.9%', deltaTone: 'up', volume: '45 Bags', time: '02:14:45', timeTone: 'amber', status: 'Active Auction', statusTone: 'green', action: 'PLACE BID', style: 'solid', iconColor: '#0d631b' },
    { id: 'CO-HUIL-088', origin: 'Colombia Huila Supremo', region: 'south-america', variety: 'Caturra, Screen 18', price: '5.12', delta: '0.4%', deltaTone: 'down', volume: '12.5 MT', time: null, status: 'Spot Available', statusTone: 'primary', action: 'BUY NOW', style: 'outline', iconColor: '#79573f' },
    { id: 'KE-NYER-015', origin: 'Kenya Nyeri AA Plus', region: 'east-africa', variety: 'SL34, Washed', price: '8.20', delta: '2.3%', deltaTone: 'up', volume: '20 Bags', time: '00:12:05', timeTone: 'rose', status: 'Closing Soon', statusTone: 'rose', action: 'PLACE BID', style: 'solid', iconColor: '#735c00' },
];

const filteredAuctions = computed(() => {
    if (auctionFilter.value === 'all') {
        return auctionRows;
    }

    return auctionRows.filter((row) => row.region === auctionFilter.value);
});

const askLadder = [
    { price: '184.50', size: '12,400', depth: 85 },
    { price: '184.45', size: '4,200', depth: 40 },
    { price: '184.40', size: '8,100', depth: 65 },
    { price: '184.35', size: '1,500', depth: 20 },
];

const bidLadder = [
    { price: '184.20', size: '6,100', depth: 55 },
    { price: '184.15', size: '2,500', depth: 30 },
    { price: '184.10', size: '9,200', depth: 75 },
    { price: '184.05', size: '4,800', depth: 45 },
];

// Deterministic OHLC series so the "Candles" tab actually renders candlesticks.
function seededCandles(count) {
    const candles = [];
    let seed = 7;
    const rand = () => {
        seed = (seed * 9301 + 49297) % 233280;
        return seed / 233280;
    };

    let level = 84;
    for (let i = 0; i < count; i += 1) {
        const drift = 4.5 + rand() * 2.5;
        level -= drift;
        const noise = (rand() - 0.5) * 5;
        const open = Math.min(94, Math.max(8, level + noise + drift * 0.35));
        const close = Math.min(94, Math.max(8, level + noise - drift * 0.35));
        const bodyTop = Math.min(open, close);
        const bodyBottom = Math.max(open, close);

        candles.push({
            open,
            close,
            high: Math.max(4, bodyTop - (1.5 + rand() * 3)),
            low: Math.min(97, bodyBottom + (1.5 + rand() * 3)),
            volume: 25 + rand() * 75,
            bullish: close < open,
        });
    }

    return candles;
}

const candleSlotWidth = 100 / 14;
const candleBodyWidth = candleSlotWidth * 0.5;

const chartCandles = seededCandles(14).map((candle, i) => ({
    ...candle,
    cx: candleSlotWidth * (i + 0.5),
}));

const chartPriceLabels = [
    { y: 20, price: '184.55' },
    { y: 40, price: '184.35' },
    { y: 60, price: '184.15' },
    { y: 80, price: '183.95' },
];
</script>

<template>
    <OuterLayout :title="pageTitle">
        <!-- HERO -->
        <section class="relative w-full overflow-hidden bg-[#121611] pt-12 pb-24 md:pt-24 md:pb-32 px-4 md:px-8 text-[#eef2e8]">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-5 flex flex-col gap-8 relative z-10 wp-fade-1">
                    <!-- <div class="inline-flex items-center gap-3 px-4 py-1.5 bg-[#1a2018] rounded-full w-fit shadow-inner border border-[#0d631b]/30">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#a3f69c] wp-pulse"></span>
                        <span class="text-xs text-[#a3f69c] uppercase tracking-[0.2em]">Global Markets Live</span>
                    </div> -->

                    <h1 class="text-[44px] md:text-[64px] leading-[1.05] text-white tracking-[-0.03em] font-semibold">
                        The Digital Exchange <br class="hidden sm:block" />
                        for <span class="text-[#a3f69c]">Coffee.</span>
                    </h1>

                    <p class="text-lg leading-relaxed text-[#bfcaba] max-w-lg">
                        Buy and sell coffee directly with producers and buyers around the world. Live pricing, verified origins, and settlement that doesn't take weeks.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mt-2">
                        <Link
                            :href="route('login')"
                            class="bg-[#a3f69c] text-[#002204] px-8 py-4 rounded text-xs font-bold tracking-widest uppercase hover:bg-[#88d982] hover:shadow-[0_0_20px_rgba(163,246,156,0.2)] hover:-translate-y-0.5 transition-all text-center no-underline"
                        >Enter the Exchange</Link>
                        <a
                            href="#auctions"
                            class="bg-[#1a2018] text-white px-8 py-4 rounded text-xs font-bold tracking-widest uppercase hover:bg-[#20281e] transition-all border border-[#707a6c]/30 text-center no-underline"
                        >Explore Marketplace</a>
                    </div>

                    <div class="mt-8 grid grid-cols-3 gap-3 sm:flex sm:gap-10 border-t border-[#707a6c]/20 pt-8 bg-[#1a2018]/50 p-4 sm:p-6 rounded-lg backdrop-blur-md border border-[#707a6c]/10">
                        <div class="min-w-0">
                            <div class="wp-display text-lg sm:text-3xl font-bold tracking-tight text-white tabular-nums">$2.4B+</div>
                            <div class="text-[9px] sm:text-[10px] text-[#bfcaba] uppercase mt-2 tracking-[0.05em] sm:tracking-[0.15em] leading-tight">Traded Volume</div>
                        </div>
                        <div class="min-w-0">
                            <div class="wp-display text-lg sm:text-3xl font-bold tracking-tight text-white tabular-nums">45+</div>
                            <div class="text-[9px] sm:text-[10px] text-[#bfcaba] uppercase mt-2 tracking-[0.05em] sm:tracking-[0.15em] leading-tight">Origin Countries</div>
                        </div>
                        <div class="min-w-0">
                            <div class="wp-display text-lg sm:text-3xl font-bold tracking-tight text-[#a3f69c] tabular-nums flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px] sm:text-[24px]">trending_up</span>99.9%
                            </div>
                            <div class="text-[9px] sm:text-[10px] text-[#bfcaba] uppercase mt-2 tracking-[0.05em] sm:tracking-[0.15em] leading-tight">Platform Uptime</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 relative wp-fade-2">
                    <div class="absolute inset-0 bg-gradient-to-tr from-[#a3f69c]/20 via-[#0d631b]/5 to-transparent rounded-2xl blur-3xl -z-10 transform scale-110"></div>
                    <div class="relative rounded-xl overflow-hidden shadow-2xl bg-[#121611] p-1 group">
                        <svg
                            class="w-full h-[380px] md:h-[600px] rounded-lg transition-transform duration-1000 group-hover:scale-105"
                            viewBox="0 0 400 550"
                            preserveAspectRatio="xMidYMid slice"
                            role="img"
                            aria-label="Ascending price chart representing coffee market growth"
                        >
                            <defs>
                                <linearGradient id="heroBg" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#1c2a18" />
                                    <stop offset="55%" stop-color="#141d11" />
                                    <stop offset="100%" stop-color="#0d1309" />
                                </linearGradient>
                                <linearGradient id="heroCurveFill" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#a3f69c" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#a3f69c" stop-opacity="0" />
                                </linearGradient>
                                <radialGradient id="heroVignette" cx="50%" cy="35%" r="75%">
                                    <stop offset="0%" stop-color="#000000" stop-opacity="0" />
                                    <stop offset="100%" stop-color="#000000" stop-opacity="0.55" />
                                </radialGradient>
                            </defs>
                            <rect width="400" height="550" fill="url(#heroBg)" />
                            <line v-for="row in 5" :key="row" x1="0" :y1="row * 90" x2="400" :y2="row * 90" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
                            <path :d="heroCurveFillPath" fill="url(#heroCurveFill)" />
                            <path :d="heroCurvePath" fill="none" stroke="#a3f69c" stroke-width="2.5" stroke-linejoin="round" opacity="0.85" />
                            <circle
                                v-for="(point, i) in heroCurvePoints" :key="i"
                                :cx="point.x" :cy="point.y * 5.5" r="3"
                                fill="#a3f69c" :opacity="i === heroCurvePoints.length - 1 ? 1 : 0.35"
                            />
                            <rect width="400" height="550" fill="url(#heroVignette)" />
                        </svg>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#121611] via-transparent to-[#121611]/50 rounded-lg pointer-events-none"></div>

                        <!-- Floating price card -->
                        <div class="absolute top-8 right-8 bg-[#1a2018]/80 backdrop-blur-xl p-5 rounded-lg shadow-2xl w-64 hover:bg-[#1a2018]/95 transition-all">
                            <div class="flex justify-between items-start mb-3">
                                <div class="text-xs text-[#bfcaba] uppercase tracking-wider">Arabica (KC)</div>
                                <span class="material-symbols-outlined text-[18px] text-[#bfcaba]">show_chart</span>
                            </div>
                            <div class="wp-display text-3xl font-bold tracking-tight text-white tabular-nums">184.25</div>
                            <div class="text-xs text-[#a3f69c] flex items-center gap-1 mt-2 bg-[#a3f69c]/10 w-fit px-2 py-1 rounded">
                                <span class="material-symbols-outlined text-[14px]">arrow_upward</span> +2.40 (1.32%)
                            </div>
                            <div class="mt-4 pt-4 border-t border-[#707a6c]/20">
                                <div class="text-[10px] text-[#bfcaba] mb-2 uppercase tracking-[0.2em]">24h Volume</div>
                                <div class="h-8 w-full flex items-end gap-1 opacity-80">
                                    <div class="w-full bg-[#0d631b]/30 rounded-t-sm h-[30%]"></div>
                                    <div class="w-full bg-[#0d631b]/40 rounded-t-sm h-[60%]"></div>
                                    <div class="w-full bg-[#0d631b]/50 rounded-t-sm h-[40%]"></div>
                                    <div class="w-full bg-[#0d631b]/70 rounded-t-sm h-[90%]"></div>
                                    <div class="w-full bg-[#a3f69c] rounded-t-sm h-full shadow-[0_0_10px_rgba(163,246,156,0.5)]"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating live match card -->
                        <div class="absolute bottom-8 left-8 bg-[#1a2018]/90 backdrop-blur-xl p-5 rounded-lg shadow-2xl w-72 hover:-translate-y-1 transition-transform">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-[#a3f69c] wp-pulse"></div>
                                    <span class="text-xs text-white uppercase tracking-wider">Live Match</span>
                                </div>
                                <span class="text-[10px] text-[#a3f69c] border border-[#a3f69c]/30 px-2 py-0.5 rounded bg-[#a3f69c]/10 tracking-wider">FILLED</span>
                            </div>
                            <div class="flex justify-between items-end border-b border-[#707a6c]/20 pb-4 mb-4">
                                <div>
                                    <div class="text-[10px] text-[#bfcaba] mb-1 uppercase tracking-wider">Asset</div>
                                    <div class="text-sm font-semibold text-white">Bugisu AA (UG)</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] text-[#bfcaba] mb-1 uppercase tracking-wider">Size</div>
                                    <div class="text-sm text-white tabular-nums">500 MT</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-[#bfcaba] uppercase tracking-wider">
                                <span class="material-symbols-outlined text-[16px] text-[#a3f69c]">verified_user</span> Smart Contract Settled
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ORIGIN STRIP (real growing regions, not fabricated partners) -->
        <section class="w-full bg-[#ebefe5] py-16 md:py-20 border-t border-[#bfcaba]/20 wp-reveal">
            <div class="max-w-7xl mx-auto px-4 md:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#0d631b]/10 text-[#0d631b] rounded-full w-fit mb-4">
                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                            <span class="text-[11px] font-bold tracking-widest uppercase">Origin Network</span>
                        </div>
                        <h2 class="text-[28px] md:text-[32px] font-semibold text-[#181d17] leading-tight">Sourced from Uganda's growing regions.</h2>
                        <p class="text-base leading-6 text-[#40493d] mt-4">
                            Six verified growing regions, each with its own altitude, variety, and processing profile. Browse lots by origin instead of guessing at a label.
                        </p>
                    </div>
                    <Link :href="route('origin.index')" class="text-[#0d631b] text-sm font-semibold flex items-center gap-2 hover:gap-3 transition-all uppercase tracking-widest w-fit no-underline flex-shrink-0">
                        View Origin Directory <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </Link>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                    <Link
                        v-for="region in originRegions" :key="region.name"
                        :href="route('origin.index')"
                        class="group bg-[#f7fbf0] rounded-xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all no-underline flex flex-col"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-8 h-8 rounded-lg bg-[#0d631b]/10 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[#0d631b] text-[16px]">terrain</span>
                            </div>
                            <span
                                class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-full flex-shrink-0"
                                :class="region.variety === 'Arabica' ? 'bg-[#0d631b]/10 text-[#0d631b]' : 'bg-[#79573f]/10 text-[#79573f]'"
                            >{{ region.variety }}</span>
                        </div>
                        <h3 class="text-sm font-semibold text-[#181d17] group-hover:text-[#0d631b] transition-colors mb-1 leading-snug">{{ region.name }}</h3>
                        <p class="text-xs text-[#40493d] leading-snug">{{ region.altitude }} · {{ region.notes }}</p>
                        <div class="mt-3 pt-3 border-t border-[#bfcaba]/20 flex items-center justify-between">
                            <span class="text-[10px] text-[#8b978a]">{{ region.lots }} Lots</span>
                            <span class="material-symbols-outlined text-[14px] text-[#0d631b] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- MARKET STRIP -->
        <div class="relative w-full bg-[#121611] border-y border-[#707a6c]/20 py-4 overflow-hidden flex items-center shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)] wp-reveal">
            <div class="px-4 md:px-8 flex items-center gap-3 border-r border-[#707a6c]/20 pr-6 flex-shrink-0 bg-[#121611] z-20 relative">
                <span class="w-2 h-2 rounded-full bg-[#a3f69c] wp-pulse"></span>
                <span class="text-[10px] text-white uppercase tracking-[0.2em] font-semibold">Market Open</span>
            </div>
            <div class="overflow-hidden flex-1 relative">
                <div class="wp-ticker flex items-center pl-12">
                    <template v-for="(item, i) in [...tickerItems, ...tickerItems]" :key="i">
                        <span class="flex items-center gap-2.5 flex-shrink-0 px-7">
                            <span class="text-[11px] text-[#8b978a] uppercase tracking-wider">{{ item.label }}</span>
                            <span class="text-sm text-white font-semibold tabular-nums">{{ item.value }}</span>
                            <span
                                class="text-[11px] font-semibold tabular-nums flex items-center gap-0.5"
                                :class="item.tone === 'up' ? 'text-[#a3f69c]' : 'text-[#F43F5E]'"
                            >
                                <span class="material-symbols-outlined text-[15px]">{{ item.tone === 'up' ? 'arrow_drop_up' : 'arrow_drop_down' }}</span>{{ item.change }}
                            </span>
                        </span>
                        <span class="w-px h-3 bg-[#707a6c]/20 flex-shrink-0"></span>
                    </template>
                </div>
            </div>
            <div class="pointer-events-none absolute inset-y-0 right-0 w-16 md:w-28 bg-gradient-to-l from-[#121611] to-transparent z-10"></div>
        </div>

        <!-- AI MATCHMAKER -->
        <section id="matchmaker" class="py-28 md:py-32 px-4 md:px-8 bg-[#121611] text-white relative overflow-hidden wp-reveal">
            <div class="absolute right-0 top-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#0d631b]/10 rounded-full blur-[120px] -z-10 pointer-events-none"></div>
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-20 items-center">
                <div class="relative z-10">
                    <h2 class="text-4xl md:text-5xl text-white max-w-xl mb-6 font-semibold tracking-tight leading-[1.1]">
                        The right buyer, matched to every <span class="text-[#a3f69c]">lot.</span>
                    </h2>
                    <p class="text-lg leading-relaxed text-[#bfcaba] max-w-xl mb-10">
                        We match sellers with buyers by comparing available inventory, shipping costs, and what each buyer is actually looking for. Both sides get a better deal, faster.
                    </p>
                    <ul class="mb-10 border-t border-[#707a6c]/15">
                        <li v-for="point in matchmakerPoints" :key="point.title" class="flex items-start gap-4 py-5 border-b border-[#707a6c]/15">
                            <div class="w-9 h-9 rounded-lg bg-[#1a2018] border border-[#707a6c]/20 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[#a3f69c] text-[18px]">{{ point.icon }}</span>
                            </div>
                            <div>
                                <div class="text-sm text-white font-semibold mb-1">{{ point.title }}</div>
                                <div class="text-sm text-[#8b978a] leading-relaxed">{{ point.copy }}</div>
                            </div>
                        </li>
                    </ul>
                    <Link :href="route('market.news')" class="text-[#a3f69c] text-sm font-semibold flex items-center gap-2 hover:gap-3 transition-all uppercase tracking-widest w-fit no-underline">
                        Explore the Algorithm <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </Link>
                </div>

                <div class="relative z-10">
                    <div class="bg-[#181a17] rounded-2xl shadow-2xl p-7 md:p-8">
                        <div class="flex items-center justify-between mb-7">
                            <span class="text-[10px] text-[#8b978a] uppercase tracking-[0.2em] font-semibold">Live Match Preview</span>
                            <span class="inline-flex items-center gap-1.5 bg-[#a3f69c]/10 text-[#a3f69c] text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#a3f69c] wp-pulse"></span>98% Match
                            </span>
                        </div>

                        <div class="flex items-start gap-4 pb-6 border-b border-[#707a6c]/10">
                            <div class="w-11 h-11 rounded-lg bg-[#1a2018] border border-[#707a6c]/20 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[#bfcaba] text-[20px]">sell</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-[#8b978a] uppercase tracking-[0.15em] mb-1">Selling Order</div>
                                <div class="text-base text-white font-medium">Kisoro Highlands Co-op</div>
                                <div class="text-[13px] text-[#8b978a] mt-0.5">Uganda · Kisoro · 18 MT · Fully Washed</div>
                            </div>
                        </div>

                        <div class="flex items-center py-5">
                            <div class="w-2 h-2 rounded-full bg-[#a3f69c] flex-shrink-0"></div>
                            <div class="flex-1 border-t border-dashed border-[#a3f69c]/30 mx-3 relative">
                                <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-[#181a17] px-3 flex items-center gap-1.5 whitespace-nowrap">
                                    <span class="material-symbols-outlined text-[#a3f69c] wp-spin text-[14px]">sync</span>
                                    <span class="text-[#a3f69c] text-[10px] tracking-[0.1em] uppercase font-semibold">Matching</span>
                                </span>
                            </div>
                            <div class="w-2 h-2 rounded-full bg-[#a3f69c] flex-shrink-0"></div>
                        </div>

                        <div class="flex items-start gap-4 pt-6 border-t border-[#707a6c]/10">
                            <div class="w-11 h-11 rounded-lg bg-[#1a2018] border border-[#707a6c]/20 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[#bfcaba] text-[20px]">shopping_cart</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-[#8b978a] uppercase tracking-[0.15em] mb-1">Buying Intent</div>
                                <div class="text-base text-white font-medium">Nordic Roasters Ltd.</div>
                                <div class="text-[13px] text-[#8b978a] mt-0.5">Norway · Requires 15-20 MT · Ugandan Washed Arabica</div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 divide-x divide-[#707a6c]/15 border-t border-[#707a6c]/10 pt-6">
                            <div class="pr-4">
                                <div class="text-[10px] text-[#8b978a] uppercase tracking-[0.15em] mb-1.5">Est. Shipping</div>
                                <div class="text-sm text-white font-semibold tabular-nums">$1,240 · 14 Days</div>
                            </div>
                            <div class="pl-4">
                                <div class="text-[10px] text-[#a3f69c]/70 uppercase tracking-[0.15em] mb-1.5">Margin Boost</div>
                                <div class="text-sm text-[#a3f69c] font-semibold tabular-nums">+4.2% vs Spot</div>
                            </div>
                        </div>

                        <Link :href="route('login')" class="block text-center w-full mt-7 bg-[#a3f69c] text-[#002204] py-3.5 rounded-lg text-sm font-bold hover:bg-[#88d982] transition-colors uppercase tracking-widest no-underline">Initiate Smart Contract</Link>
                    </div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-[#0d631b]/10 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                </div>
            </div>
        </section>

        <!-- EXCHANGE TERMINAL PREVIEW -->
        <section class="py-24 px-4 md:px-8 bg-[#ebefe5] relative overflow-hidden wp-reveal">
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-[#a3f69c]/10 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
            <div class="max-w-7xl mx-auto relative z-10 flex flex-col gap-12">
                <div class="max-w-2xl">
                    <h2 class="wp-display text-[32px] md:text-[48px] leading-[1.15] font-semibold text-[#181d17] mb-6">Coffee, traded with <span class="text-[#0d631b]">intelligence.</span></h2>
                    <p class="text-base leading-6 text-[#40493d] max-w-lg mb-8">
                        The same matching engine and trading interface our traders use every day. Fast, reliable, and deep enough for serious volume.
                    </p>
                    <Link
                        :href="route('login')"
                        class="inline-block bg-[#a3f69c] text-[#002204] px-8 py-4 rounded-lg text-xs font-semibold tracking-[0.02em] uppercase hover:bg-[#88d982] hover:shadow-[0_0_15px_rgba(163,246,156,0.3)] transition-all no-underline"
                    >Launch Terminal Preview</Link>
                </div>

                <div class="w-full bg-[#1a1d19] rounded-xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-[#20241f] border-b border-[#707a6c]/20">
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#a3f69c] text-[18px]">candlestick_chart</span>
                                <span class="text-sm font-medium text-[#eef2e8]">KCH4 <span class="text-[#bfcaba] font-normal">(Arabica Mar '24)</span></span>
                            </div>
                            <div class="hidden sm:block h-4 w-px bg-[#707a6c]/30"></div>
                            <div class="hidden sm:flex items-baseline gap-2">
                                <span class="text-lg text-[#eef2e8] font-semibold tabular-nums">184.25</span>
                                <span class="text-sm text-[#10B981] flex items-center tabular-nums"><span class="material-symbols-outlined text-[16px]">arrow_upward</span>1.32%</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 bg-[#121611]/60 px-2.5 py-1 rounded-full border border-[#707a6c]/20 flex-shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] wp-pulse"></span>
                            <span class="text-[10px] text-[#8b978a] uppercase tracking-wider font-semibold">Live</span>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row">
                        <!-- Chart area -->
                        <div class="flex-1 lg:border-r border-[#707a6c]/20 flex flex-col relative bg-[#181a17]">
                            <div class="h-12 border-b border-[#707a6c]/10 flex items-center justify-between px-4 gap-4 overflow-x-auto">
                                <div class="flex items-center gap-1 bg-[#121611] rounded-full p-1 flex-shrink-0">
                                    <button type="button" class="px-3 py-1 rounded-full text-[11px] font-semibold bg-[#a3f69c] text-[#002204]">1H</button>
                                    <button type="button" class="px-3 py-1 rounded-full text-[11px] font-medium text-[#8b978a] hover:text-white transition-colors">4H</button>
                                    <button type="button" class="px-3 py-1 rounded-full text-[11px] font-medium text-[#8b978a] hover:text-white transition-colors">1D</button>
                                    <button type="button" class="px-3 py-1 rounded-full text-[11px] font-medium text-[#8b978a] hover:text-white transition-colors">1W</button>
                                </div>
                                <span class="hidden sm:flex items-center gap-1.5 text-[11px] text-[#8b978a] flex-shrink-0">
                                    <span class="material-symbols-outlined text-[14px]">candlestick_chart</span>Candles
                                </span>
                            </div>
                            <div class="flex-1 p-4 relative h-72 lg:h-96">
                                <svg class="absolute inset-4 w-[calc(100%-2rem)] h-[calc(100%-2rem)]" preserveAspectRatio="none" viewBox="0 0 100 100">
                                    <line v-for="row in 4" :key="row" x1="0" :y1="row * 20" x2="100" :y2="row * 20" stroke="rgba(255,255,255,0.06)" stroke-width="0.5" />
                                    <g v-for="(candle, i) in chartCandles" :key="i">
                                        <line
                                            :x1="candle.cx" :x2="candle.cx" :y1="candle.high" :y2="candle.low"
                                            :stroke="candle.bullish ? '#10B981' : '#F43F5E'" stroke-width="0.6"
                                        />
                                        <rect
                                            :x="candle.cx - candleBodyWidth / 2"
                                            :y="Math.min(candle.open, candle.close)"
                                            :width="candleBodyWidth"
                                            :height="Math.max(Math.abs(candle.open - candle.close), 1.4)"
                                            :fill="candle.bullish ? '#10B981' : '#F43F5E'"
                                        />
                                    </g>
                                </svg>
                                <div class="absolute inset-4 pointer-events-none">
                                    <span
                                        v-for="label in chartPriceLabels"
                                        :key="label.price"
                                        class="absolute right-0 text-[9px] text-[#8b978a] tabular-nums -translate-y-1/2 bg-[#181a17] pl-1.5"
                                        :style="{ top: label.y + '%' }"
                                    >{{ label.price }}</span>
                                </div>
                                <div class="absolute bottom-4 left-4 right-4 h-14 flex items-end gap-1">
                                    <div
                                        v-for="(candle, i) in chartCandles" :key="i"
                                        class="flex-1 rounded-t-sm opacity-30"
                                        :class="candle.bullish ? 'bg-[#10B981]' : 'bg-[#F43F5E]'"
                                        :style="{ height: candle.volume + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Order book -->
                        <div class="w-full lg:w-80 bg-[#1e221d] flex flex-col">
                            <div class="p-4 border-b border-[#707a6c]/20">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-[11px] font-bold tracking-widest text-[#bfcaba] uppercase">Market Sentiment</span>
                                    <span class="text-[10px] text-[#10B981] font-bold">BULLISH</span>
                                </div>
                                <div class="h-2 w-full bg-[#181a17] rounded-full overflow-hidden flex">
                                    <div class="h-full bg-[#10B981]/80 w-[68%]"></div>
                                    <div class="h-full bg-[#F43F5E]/80 w-[32%]"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-[#bfcaba] tabular-nums mt-1">
                                    <span>68% Buy</span>
                                    <span>32% Sell</span>
                                </div>
                            </div>

                            <div class="p-4 flex-1 flex flex-col gap-1">
                                <div class="flex justify-between text-[10px] text-[#bfcaba] uppercase mb-1">
                                    <span>Price (USD)</span>
                                    <span>Size (MT)</span>
                                </div>
                                <div v-for="ask in askLadder" :key="ask.price" class="flex justify-between text-xs text-[#F43F5E] relative py-0.5 tabular-nums">
                                    <div class="absolute right-0 top-0 h-full bg-[#F43F5E]/15 rounded-l-sm" :style="{ width: ask.depth + '%' }"></div>
                                    <span class="relative z-10 pl-1">{{ ask.price }}</span><span class="relative z-10 pr-1">{{ ask.size }}</span>
                                </div>
                                <div class="py-2 flex items-center justify-between border-y border-[#707a6c]/10 my-1 bg-[#20241f] px-2 rounded">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#eef2e8] text-base font-bold tabular-nums">184.25</span>
                                        <span class="material-symbols-outlined text-[16px] text-[#10B981]">arrow_upward</span>
                                    </div>
                                    <span class="text-[10px] text-[#bfcaba]">Spread 0.05</span>
                                </div>
                                <div v-for="bid in bidLadder" :key="bid.price" class="flex justify-between text-xs text-[#10B981] relative py-0.5 tabular-nums">
                                    <div class="absolute left-0 top-0 h-full bg-[#10B981]/15 rounded-r-sm" :style="{ width: bid.depth + '%' }"></div>
                                    <span class="relative z-10 pl-1">{{ bid.price }}</span><span class="relative z-10 pr-1">{{ bid.size }}</span>
                                </div>
                            </div>

                            <div class="p-4 border-t border-[#707a6c]/20 bg-[#181a17]">
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="button" class="bg-[#10B981] text-[#0f1f18] py-3 rounded text-xs font-bold hover:bg-[#10B981]/90 transition-colors">BUY</button>
                                    <button type="button" class="bg-[#F43F5E] text-[#2a0e14] py-3 rounded text-xs font-bold hover:bg-[#F43F5E]/90 transition-colors">SELL</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ESG SCORECARDS -->
        <section class="py-24 px-4 md:px-8 bg-[#f1f5eb] border-b border-[#bfcaba]/30 wp-reveal">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14">
                    <div class="max-w-2xl">
                        <h2 class="text-[28px] md:text-[32px] font-semibold text-[#181d17] leading-tight">Transparent ESG metrics, verified per lot.</h2>
                        <p class="text-base leading-6 text-[#40493d] mt-4">
                            Every lot traded on Bean Origin comes with verified impact data tracked on-chain, so buyers can price in sustainability instead of guessing at it.
                        </p>
                    </div>
                    <Link :href="route('market.news')" class="text-[#0d631b] text-sm font-semibold flex items-center gap-2 hover:gap-3 transition-all uppercase tracking-widest w-fit no-underline flex-shrink-0">
                        View Methodology <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="metric in esgMetrics" :key="metric.title" class="wp-esg-card bg-[#f7fbf0] rounded-xl p-6 shadow-sm transition-shadow">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h3 class="text-base font-semibold text-[#181d17]">{{ metric.title }}</h3>
                                <p class="text-[10px] font-bold tracking-wider text-[#8b978a] uppercase mt-1">{{ metric.sub }}</p>
                            </div>
                            <span class="material-symbols-outlined text-[20px]" :style="{ color: metric.color }" title="Blockchain-verified">verified</span>
                        </div>

                        <div class="flex items-center gap-5 mb-5">
                            <div class="relative w-20 h-20 flex-shrink-0">
                                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                    <circle cx="50" cy="50" r="42" fill="none" stroke="#e5eadf" stroke-width="9" />
                                    <circle
                                        cx="50" cy="50" r="42" fill="none" :stroke="metric.color" stroke-width="9" stroke-linecap="round"
                                        stroke-dasharray="264"
                                        :stroke-dashoffset="264 - (264 * metric.bar) / 100"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[22px]" :style="{ color: metric.color }">{{ metric.icon }}</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-3xl font-bold tabular-nums leading-none" :style="{ color: metric.color }">{{ metric.value }}</div>
                                <div class="text-sm text-[#8b978a] mt-1.5">{{ metric.unit }}</div>
                            </div>
                        </div>

                        <p class="text-sm text-[#40493d] leading-relaxed">{{ metric.copy }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- LIVE AUCTIONS -->
        <section id="auctions" class="py-24 px-4 md:px-8 bg-[#f1f5eb] wp-reveal">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div class="flex flex-col gap-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#F43F5E]/10 rounded-full w-fit">
                            <span class="w-2 h-2 rounded-full bg-[#F43F5E] wp-pulse"></span>
                            <span class="text-[11px] font-bold tracking-widest text-[#F43F5E] uppercase">Live Auctions</span>
                        </div>
                        <h2 class="text-[28px] md:text-[32px] font-semibold text-[#181d17]">Live Auctions &amp; Spot Pricing</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="text-[11px] font-bold tracking-widest text-[#40493d] uppercase mb-2 w-full">Filter by Origin</span>
                        <button
                            v-for="filter in auctionFilters"
                            :key="filter.key"
                            type="button"
                            class="px-4 py-2 rounded-full text-xs font-semibold transition-colors"
                            :class="auctionFilter === filter.key ? 'bg-[#0d631b] text-white' : 'bg-[#e5eadf] text-[#40493d] hover:bg-[#bfcaba]/30'"
                            @click="auctionFilter = filter.key"
                        >{{ filter.label }}</button>
                    </div>

                </div>

                <div class="w-full overflow-x-auto rounded-xl bg-[#f7fbf0] shadow-sm">
                    <table class="w-full text-left border-collapse min-w-[880px]">
                        <thead>
                            <tr class="bg-[#e5eadf] border-b border-[#bfcaba]/30">
                                <th class="px-6 py-4 text-xs font-semibold text-[#40493d] uppercase tracking-wider">Lot ID &amp; Origin</th>
                                <th class="px-6 py-4 text-xs font-semibold text-[#40493d] uppercase tracking-wider">Variety &amp; Grade</th>
                                <th class="px-6 py-4 text-xs font-semibold text-[#40493d] uppercase tracking-wider">Current Bid / Price</th>
                                <th class="px-6 py-4 text-xs font-semibold text-[#40493d] uppercase tracking-wider">Volume</th>
                                <th class="px-6 py-4 text-xs font-semibold text-[#40493d] uppercase tracking-wider">Time Remaining</th>
                                <th class="px-6 py-4 text-xs font-semibold text-[#40493d] uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold text-[#40493d] uppercase tracking-wider text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#bfcaba]/20">
                            <tr v-if="filteredAuctions.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <p class="text-sm text-[#40493d]">No live lots in this region right now.</p>
                                    <Link :href="route('market.live')" class="inline-block mt-2 text-sm font-semibold text-[#0d631b] hover:text-[#2e7d32] no-underline">Browse all listings</Link>
                                </td>
                            </tr>
                            <tr v-for="row in filteredAuctions" :key="row.id" class="hover:bg-[#f1f5eb] transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded bg-[#e0e4da] flex items-center justify-center flex-shrink-0" :style="{ color: row.iconColor }">
                                            <span class="material-symbols-outlined text-[20px]">public</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-[#181d17] group-hover:text-[#0d631b] transition-colors">{{ row.id }}</div>
                                            <div class="text-sm text-[#40493d]">{{ row.origin }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-[#40493d]">{{ row.variety }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-[#181d17] tabular-nums font-medium">${{ row.price }} <span class="text-[10px] font-normal text-[#40493d]">/kg</span></span>
                                        <span
                                            class="inline-flex items-center text-[11px] font-semibold tabular-nums"
                                            :class="row.deltaTone === 'up' ? 'text-[#10B981]' : 'text-[#F43F5E]'"
                                        >
                                            <span class="material-symbols-outlined text-[14px]">{{ row.deltaTone === 'up' ? 'arrow_drop_up' : 'arrow_drop_down' }}</span>{{ row.delta }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-[#181d17] tabular-nums">{{ row.volume }}</td>
                                <td class="px-6 py-4">
                                    <div v-if="row.time" class="flex items-center gap-2 text-sm tabular-nums" :class="row.timeTone === 'rose' ? 'text-[#F43F5E]' : 'text-[#F59E0B]'">
                                        <span v-if="row.timeTone === 'rose'" class="w-1.5 h-1.5 rounded-full bg-[#F43F5E] wp-pulse flex-shrink-0"></span>
                                        <span v-else class="material-symbols-outlined text-[16px]">schedule</span>{{ row.time }}
                                    </div>
                                    <span v-else class="text-sm text-[#40493d]">N/A</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 rounded-sm text-[10px] font-semibold uppercase"
                                        :class="{
                                            'bg-[#10B981]/10 text-[#10B981]': row.statusTone === 'green',
                                            'bg-[#0d631b]/10 text-[#0d631b]': row.statusTone === 'primary',
                                            'bg-[#F43F5E]/10 text-[#F43F5E]': row.statusTone === 'rose',
                                        }"
                                    >{{ row.status }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('login')"
                                        class="inline-block px-4 py-2 rounded text-xs font-semibold transition-all no-underline"
                                        :class="row.style === 'solid' ? 'bg-[#0d631b] text-white hover:bg-[#2e7d32]' : 'border border-[#0d631b] text-[#0d631b] hover:bg-[#0d631b] hover:text-white'"
                                    >{{ row.action }}</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex justify-center">
                    <Link :href="route('market.live')" class="text-sm font-semibold text-[#0d631b] hover:text-[#2e7d32] flex items-center gap-2 transition-colors uppercase tracking-wider no-underline">
                        View All Market Listings <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </Link>
                </div>
            </div>
        </section>
    </OuterLayout>
</template>

<style>
@keyframes wpPulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
    }
}

.wp-page .wp-pulse {
    animation: wpPulse 2s ease-in-out infinite;
}

@keyframes wpFadeInUp {
    from {
        opacity: 0;
        transform: translateY(1rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.wp-page .wp-fade-1 {
    animation: wpFadeInUp 0.8s ease-out both;
}

.wp-page .wp-fade-2 {
    animation: wpFadeInUp 1s ease-out 0.15s both;
}

.wp-page .wp-esg-card:hover {
    
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}

@keyframes wpTicker {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.wp-page .wp-ticker {
    width: max-content;
    animation: wpTicker 40s linear infinite;
}

.wp-page .wp-ticker:hover {
    animation-play-state: paused;
}

@keyframes wpSpin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.wp-page .wp-spin {
    display: inline-block;
    animation: wpSpin 3s linear infinite;
}

@media (prefers-reduced-motion: reduce) {
    .wp-page .wp-ticker,
    .wp-page .wp-spin,
    .wp-page .wp-pulse {
        animation: none;
    }
}
</style>
