<script setup>
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const commodityType = ref('arabica');

const tickerItems = [
    { label: 'Arabica C-Future', value: '$2.4540', delta: '+1.24%', deltaClass: 'text-emerald-600' },
    { label: 'Robusta London', value: '$4,812.00', delta: '-0.42%', deltaClass: 'text-[#BA1A1A]' },
    { label: 'Ethiopian Yirgacheffe Index', value: '$3.1200', delta: '+0.85%', deltaClass: 'text-emerald-600' },
    { label: 'Colombian Excelso', value: '$2.9450', delta: '0.00%', deltaClass: 'text-slate-400' },
];

const marketRows = [
    {
        id: 'ETH-2024-081',
        icon: '🇪🇹',
        name: 'Yirgacheffe G1',
        meta: 'Heirloom / Washed',
        variety: 'Heirloom',
        score: '89.5',
        bid: '$4.12',
        bids: '12 bids',
        time: '02h 14m 10s',
        timeClass: 'text-[#BA1A1A]',
        type: 'arabica',
    },
    {
        id: 'COL-2024-114',
        icon: '🇨🇴',
        name: 'Huila Pink Bourbon',
        meta: 'Anaerobic / Finca El Mirador',
        variety: 'Pink Bourbon',
        score: '91.2',
        bid: '$7.45',
        bids: '24 bids',
        time: '14h 45m 02s',
        timeClass: 'text-slate-600',
        type: 'arabica',
    },
    {
        id: 'BRA-2024-002',
        icon: '🇧🇷',
        name: 'Mogiana NY 2/3',
        meta: 'Natural / Cooperative',
        variety: 'Catuai',
        score: '82.5',
        bid: '$2.18',
        bids: '0 bids',
        time: '01d 08h 12s',
        timeClass: 'text-slate-600',
        type: 'robusta',
    },
    {
        id: 'PAN-2024-055',
        icon: '🇵🇦',
        name: 'Esmeralda Geisha',
        meta: 'Washed / Private Collection',
        variety: 'Geisha',
        score: '94.8',
        bid: '$42.00',
        bids: '102 bids',
        time: '00h 12m 45s',
        timeClass: 'text-[#BA1A1A]',
        type: 'arabica',
    },
];

const filteredRows = computed(() =>
    marketRows.filter((row) => commodityType.value === 'all' || row.type === commodityType.value),
);

const alerts = [
    { title: 'Large Transaction: Arabica', detail: '50,000 lbs exchanged at $2.44', dot: 'bg-emerald-500' },
    { title: 'Auction Closed: Lot G-12', detail: 'Final bid: $3.95/lb (Panama)', dot: 'bg-emerald-500' },
    { title: 'Supply Alert: Vietnam', detail: 'Heavy rainfall impacting Robusta drying', dot: 'bg-[#FEDCBE]' },
];
</script>

<template>
    <AppLayout title="Market" full-width>
        <div class="market-page space-y-3">
            <div class="flex flex-col gap-6 xl:flex-row">
                <div class="min-w-0 flex-1 space-y-6">
                    <section class="flex flex-col gap-3 rounded-2xl border border-[#E5E7EB] bg-white p-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-[#004532]">Market Status: Open</p>
                            <h1 class="text-[24px] font-bold tracking-tight text-[#191C1E] sm:text-[28px]">Coffee Futures &amp; Live Auctions</h1>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            <button class="rounded-md border border-[#D7DDE4] bg-white px-3 py-1.5 text-[12px] font-medium tracking-[0.01em] text-slate-600 transition-colors hover:bg-[#F8FAFC]">
                                Export CSV
                            </button>
                            <button class="flex items-center gap-1.5 rounded-md border border-[#004532] bg-[#004532] px-3 py-1.5 text-[12px] font-medium tracking-[0.01em] text-white transition-colors hover:bg-[#03513c]">
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M21 12a9 9 0 11-2.64-6.36" />
                                    <path d="M21 3v6h-6" />
                                </svg>
                                Refresh Live
                            </button>
                        </div>
                    </section>

                    <section class="grid gap-3 rounded-2xl border border-[#E5E7EB] bg-[#F2F4F6] p-3 md:grid-cols-2 xl:grid-cols-[max-content,max-content,max-content,minmax(0,1fr)] xl:items-end">
                        <div class="min-w-0 flex flex-col gap-1">
                            <label class="ml-1 text-[10px] font-bold uppercase text-slate-400">Commodity Type</label>
                            <div class="flex w-full rounded bg-white p-1 sm:w-max">
                                <button
                                    type="button"
                                    class="rounded px-4 py-1 text-xs font-bold"
                                    :class="commodityType === 'arabica' ? 'bg-[#004532] text-white' : 'text-slate-600'"
                                    @click="commodityType = 'arabica'"
                                >
                                    Arabica
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-4 py-1 text-xs font-bold"
                                    :class="commodityType === 'robusta' ? 'bg-[#004532] text-white' : 'text-slate-600'"
                                    @click="commodityType = 'robusta'"
                                >
                                    Robusta
                                </button>
                            </div>
                        </div>

                        <div class="min-w-0 flex flex-col gap-1">
                            <label class="ml-1 text-[10px] font-bold uppercase text-slate-400">Origin Region</label>
                            <select class="w-full rounded border-none bg-white py-2 pr-10 text-xs font-medium focus:ring-1 focus:ring-[#004532]">
                                <option>All Global Origins</option>
                                <option>East Africa</option>
                                <option>Central America</option>
                                <option>South America</option>
                                <option>Southeast Asia</option>
                            </select>
                        </div>

                        <div class="min-w-0 flex flex-col gap-1">
                            <label class="ml-1 text-[10px] font-bold uppercase text-slate-400">SCAA Minimum</label>
                            <select class="w-full rounded border-none bg-white py-2 pr-10 text-xs font-medium focus:ring-1 focus:ring-[#004532]">
                                <option>80+ Specialty</option>
                                <option>84+ Premium</option>
                                <option>88+ Rare</option>
                            </select>
                        </div>

                        <div class="min-w-0 flex flex-col gap-1 xl:justify-self-end">
                            <label class="ml-1 text-[10px] font-bold uppercase text-slate-400">Sort By</label>
                            <select class="w-full rounded border-none bg-white py-2 pr-10 text-xs font-medium focus:ring-1 focus:ring-[#004532] xl:min-w-[180px]">
                                <option>Highest Score</option>
                                <option>Ending Soon</option>
                                <option>Price: Low to High</option>
                            </select>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_18px_60px_rgba(15,23,42,0.05)]">
                        <div class="border-b border-[#EEF2F7] bg-[#F8FAFC] px-4 py-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <div class="text-[18px] font-bold tracking-tight text-[#191C1E]">Live market board</div>
                                    <p class="mt-1 text-[13px] text-[#64748B]">
                                        Structured by lot identity, producer profile, cup quality, bid level, and time pressure.
                                    </p>
                                </div>
                                <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-[#94A3B8]">
                                    {{ filteredRows.length }} active listings
                                </div>
                            </div>
                        </div>

                        <div v-if="filteredRows.length" class="space-y-3 p-3.5 sm:p-4 lg:hidden">
                            <article
                                v-for="row in filteredRows"
                                :key="`mobile-${row.id}`"
                                class="rounded-2xl border border-[#E5E7EB] bg-[#FCFCFC] p-3.5"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-mono text-[10px] uppercase tracking-[0.12em] text-slate-400">{{ row.id }}</div>
                                        <div class="mt-2 flex items-center gap-3">
                                            <span class="text-xl">{{ row.icon }}</span>
                                            <div class="min-w-0">
                                                <p class="text-[14px] font-bold text-[#191C1E]">{{ row.name }}</p>
                                                <p class="text-[11px] text-slate-400">{{ row.meta }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="rounded bg-[#A6F2D1] px-2 py-1 text-[10px] font-medium text-[#002116]">
                                        {{ row.variety }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-xl bg-white px-3 py-2.5">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-slate-400">Cup score</div>
                                        <div class="mt-2 text-[16px] font-black text-emerald-900">{{ row.score }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2.5">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-slate-400">Current bid</div>
                                        <div class="mt-2 text-[15px] font-bold text-[#191C1E]">{{ row.bid }} <span class="text-[10px] text-slate-400">/lb</span></div>
                                        <div class="mt-1 text-[10px]" :class="row.bids === '0 bids' ? 'text-slate-400' : 'text-emerald-600'">{{ row.bids }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2.5">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-slate-400">Time remaining</div>
                                        <div class="mt-2 flex items-center gap-2 text-[12px] font-bold" :class="row.timeClass">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <circle cx="12" cy="12" r="8" />
                                                <path d="M12 8v4.5l3 2" />
                                            </svg>
                                            <span>{{ row.time }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-end">
                                        <button class="w-full rounded bg-[#004532] px-4 py-2.5 text-xs font-bold text-white">
                                            Place Bid
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-else class="p-8 text-center">
                            <div class="text-[15px] font-semibold text-[#191C1E]">No market listings available</div>
                            <p class="mt-1 text-[13px] text-[#64748B]">Change the commodity filter to see more lots.</p>
                        </div>

                        <div v-if="filteredRows.length" class="hidden overflow-x-auto lg:block">
                            <table class="min-w-full table-fixed border-collapse text-left">
                                <colgroup>
                                    <col class="w-[12%]" />
                                    <col class="w-[32%]" />
                                    <col class="w-[15%]" />
                                    <col class="w-[10%]" />
                                    <col class="w-[14%]" />
                                    <col class="w-[10%]" />
                                    <col class="w-[7%]" />
                                </colgroup>
                                <thead>
                                    <tr class="bg-[#F8FAFC] text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                        <th class="px-4 py-3">Lot ID</th>
                                        <th class="px-4 py-3">Origin / Farm</th>
                                        <th class="px-4 py-3">Variety</th>
                                        <th class="px-3 py-3 text-center">SCAA Score</th>
                                        <th class="px-4 py-3">Current Bid</th>
                                        <th class="px-3 py-3">Time Remaining</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="row in filteredRows"
                                        :key="row.id"
                                        class="align-top transition-colors hover:bg-slate-50"
                                    >
                                        <td class="px-4 py-3.5">
                                            <div class="font-mono text-[11px] font-medium text-slate-500">{{ row.id }}</div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-start gap-3">
                                                <span class="mt-0.5 text-xl">{{ row.icon }}</span>
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-bold text-[#191C1E]">{{ row.name }}</p>
                                                    <p class="mt-1 text-[11px] leading-relaxed text-slate-400">{{ row.meta }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span class="inline-flex rounded bg-[#A6F2D1] px-2 py-1 text-[11px] font-medium text-[#002116]">
                                                {{ row.variety }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            <span class="text-[15px] font-black text-emerald-900">{{ row.score }}</span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <p class="text-sm font-bold text-[#191C1E]">{{ row.bid }} <span class="text-[10px] text-slate-400">/lb</span></p>
                                            <p class="mt-1 text-[10px]" :class="row.bids === '0 bids' ? 'text-slate-400' : 'text-emerald-600'">{{ row.bids }}</p>
                                        </td>
                                        <td class="px-3 py-3.5">
                                            <div class="flex items-center gap-2 text-[12px] font-bold" :class="row.timeClass">
                                                <svg class="size-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <circle cx="12" cy="12" r="8" />
                                                    <path d="M12 8v4.5l3 2" />
                                                </svg>
                                                <span>{{ row.time }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            <button class="rounded bg-[#004532] px-3.5 py-2 text-[11px] font-bold text-white">
                                                Place Bid
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <aside class="w-full space-y-5 xl:w-80 xl:flex-shrink-0">
                    <section class="rounded-xl border border-[#E5E7EB] bg-[#F2F4F6] p-4">
                        <h4 class="mb-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Market Sentiment</h4>
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-xl font-bold text-emerald-900">Strongly Bullish</span>
                            <svg class="size-6 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 18h16" />
                                <path d="M7 14l3-3 3 2 4-5" />
                            </svg>
                        </div>
                        <div class="mb-6 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full w-4/5 bg-emerald-600"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase text-slate-400">24h Vol</p>
                                <p class="text-sm font-bold text-[#191C1E]">1.2M Lbs</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-slate-400">Volatility</p>
                                <p class="text-sm font-bold text-[#191C1E]">Low (4.2%)</p>
                            </div>
                        </div>
                    </section>

                    <section class="relative min-h-[240px] overflow-hidden rounded-xl bg-[#004532] p-4 text-white">
                        <img
                            class="absolute inset-0 h-full w-full object-cover opacity-20 mix-blend-overlay"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuBS887NIN4be9-_oSo9zD1pYSeYic8J9IZcxbMKXpnvX2XBpApYH70HQl7wY_SUvqFQQFMI9Z9Q3ebJQRDDwTwR6a9vb7hpCm9PuRV3vhUVqzGlKlUKKQRYqj0Q5cc7dS6T-1Gelgi1bE8X4Rf84MMUhmBirxU5Czyifh_RgOFRktXsnQ_i8hpVgGeQpveP-7NQwk2YjCAnKcRwR-gGH8llWqyZsom13EG8Ch1cBi3w2_K4NPOB_RHoprvnaYlyaZQDju5X-_ubTrgq"
                            alt="Coffee beans background"
                        />
                        <div class="relative z-10 flex h-full flex-col">
                            <h4 class="mb-2 text-[10px] font-bold uppercase tracking-widest text-emerald-200">Regional Insight</h4>
                            <p class="mb-4 text-lg font-bold leading-tight">East African Harvest Yields Peak Quality</p>
                            <p class="mb-6 text-xs leading-relaxed text-emerald-100">
                                Early samples from Rwanda and Kenya show a 15% increase in Specialty Grade (85+) compared to previous season.
                            </p>
                            <button class="mt-auto rounded border border-white/20 bg-white/20 py-2 text-xs font-bold uppercase tracking-widest text-white backdrop-blur-md transition-all hover:bg-white/30">
                                Read Report
                            </button>
                        </div>
                    </section>

                    <section class="rounded-xl border border-[#E5E7EB] bg-white p-4">
                        <h4 class="mb-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Live Alerts</h4>
                        <div class="flex flex-col gap-4">
                            <div
                                v-for="alert in alerts"
                                :key="alert.title"
                                class="flex gap-3"
                            >
                                <div class="mt-1 h-2 w-2 rounded-full" :class="alert.dot"></div>
                                <div>
                                    <p class="text-xs font-bold leading-tight text-[#191C1E]">{{ alert.title }}</p>
                                    <p class="mt-0.5 text-[10px] text-slate-400">{{ alert.detail }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="group flex cursor-pointer items-center justify-between rounded-xl bg-[#F2F4F6] p-4 transition-all hover:bg-[#E6E8EA]">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded bg-white">
                                <svg class="size-5 text-[#004532]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z" />
                                    <path d="M9.5 12.5l1.5 1.5 3.5-3.5" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold uppercase text-[#191C1E]">Live Desk Support</span>
                        </div>
                        <svg class="size-5 text-slate-300 transition-colors group-hover:text-[#004532]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </section>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.market-page :where(h1, h2, h3, h4, p) {
    margin: 0;
}

.market-page :where(button, select, table) {
    font: inherit;
}

.market-page table {
    margin-bottom: 0;
}
</style>
