<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const overviewStats = [
    { label: 'Active windows', value: '142', hint: 'live now', tone: 'text-[#111827]' },
    { label: 'Bid volume', value: '$1.2M', hint: '24h turnover', tone: 'text-[#004532]' },
    { label: 'Top clearing price', value: '$4.85', hint: 'per lb', tone: 'text-[#725A42]' },
    { label: 'Urgent closes', value: '09', hint: 'under 3h', tone: 'text-[#BA1A1A]' },
];

const lots = [
    {
        id: '#ETH-2024-082',
        origin: 'Yirgacheffe, Ethiopia',
        producer: 'Halo washing station',
        flag: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBc5oyZFwhdBja3znggfzewi6pZIsSgPSxGnRR9AjBnFApHnwpA0Gv3tGZEcDRqGo1fUbgvgNwHRAOdQMQe7_LVQMMDlaky7dpeO93piSSH2rdRLP0Se6rwbIGsQp75xbR339IRIx0tBS3CJBpn1MoEcdZLPVPCvrA1ZG5EgV6O14KbFcsz_0r-JRgmVpGhMlC3Qc4Zj4PtoDaT06RUZ3wXu-mGJwKtvsbR65-BYVKENpB5ha5qi-68Z69mH737UhhwVF5DgkToiBLQ',
        variety: 'Heirloom Arabica',
        type: 'arabica',
        varietyClass: 'bg-[#A6F2D1] text-[#002116]',
        score: '89.5',
        bid: '$4.85 / lb',
        bids: '12 bids',
        time: '02h 45m',
        timeClass: 'text-[#BA1A1A]',
        status: 'Lead lot',
        statusClass: 'bg-[#FFF3E0] text-[#8A4B08]',
        watching: false,
        urgent: true,
    },
    {
        id: '#COL-2024-115',
        origin: 'Huila, Colombia',
        producer: 'La Esperanza estate',
        flag: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCDLpMAFuhcvfI8NUuar9IoqaKa-eZKcUZB0GlsCAGdoSXU_GLegYkeV86BAD61y7FNRDcne-dbGT4Ac_JdL5mtbGnuGQ2Fp7FRlRCoJI5mSkrldqFmN3oOPmZUQt7c3YO7xvKDRCZRpeENzFjs999NYBgmHjjgoDS1Yv61NxDrHt08vtWDw1mQk4qadOXanWdXRP1YzMjmf1tD0P1hl4_4-vutJsmS-vEM6S3ypkmoJer_w6T1vQqQUiMk3n3eiHXT3-l8n-bOLdmY',
        variety: 'Caturra Arabica',
        type: 'arabica',
        varietyClass: 'bg-[#A6F2D1] text-[#002116]',
        score: '87.2',
        bid: '$3.12 / lb',
        bids: '8 bids',
        time: '1d 12h',
        timeClass: 'text-[#3F4944]',
        status: 'Steady',
        statusClass: 'bg-[#EEF2FF] text-[#3730A3]',
        watching: false,
        urgent: false,
    },
    {
        id: '#UGA-2024-004',
        origin: 'Mount Elgon, Uganda',
        producer: 'Sipi Falls cooperative',
        flag: 'https://lh3.googleusercontent.com/aida-public/AB6AXuD10InMCuMGy_pZ6HIIkeeEvbIKp9lTOdqUgvcJA28OU8Ldzv70dxp3Ah0weyg7pO9Bf1UbrGKe-filwCqd609GgL5yjxEvE3yMtgjaHbtz4E8cG9vK3dLxvNYidTzPgm3e4aCUT5Y3q1qE63D_dKnYz6TQaFPDm1N7tyBmS8dXQXrXZdj6EjMiWeyclHmKmcEkYjcvVfPsSWedGLBB5l14Y3PVEBKGQZ56UbqWo6Ab92-jywfGe-9wCzlwWpPxAVARRRzkwA20sjhX',
        variety: 'High Grade Robusta',
        type: 'robusta',
        varietyClass: 'bg-[#FEDCBE] text-[#291806]',
        score: '83.8',
        bid: '$2.05 / lb',
        bids: '24 bids',
        time: '00h 15m',
        timeClass: 'text-[#BA1A1A]',
        status: 'Watching',
        statusClass: 'bg-[#004532] text-white',
        watching: true,
        urgent: true,
    },
    {
        id: '#BRA-2024-991',
        origin: 'Minas Gerais, Brazil',
        producer: 'Boa Vista farm',
        flag: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBtugCMKXJAiMhzbgZSYsyDs9hLK16bydF3q1K6Yp0_emfEBZsqD4zSWfXYvNZmTsk-dRB_HwWkwewGpDmLqhzx0qbG21TzVVMKRBEeL7Xx3rPrsRdcAiCTGCgsa41TZgHjBvnnpH1cLChZzzQRpthsDnaraRFQnCcBjkX4adWHxC8bLsfhwWdR3Py1wamIyZ1HQ9cYXQ_4H_7LxyWeXZlcMC5jdkp1p2-rB92QAQHxKqCH1eNduS-Z1ZOmLMdxEGaWy6u-LvFC-W0C',
        variety: 'Bourbon Arabica',
        type: 'arabica',
        varietyClass: 'bg-[#A6F2D1] text-[#002116]',
        score: '85.9',
        bid: '$2.85 / lb',
        bids: '3 bids',
        time: '4d 08h',
        timeClass: 'text-[#3F4944]',
        status: 'New listing',
        statusClass: 'bg-[#FFF7ED] text-[#9A3412]',
        watching: false,
        urgent: false,
    },
];

const marketSummary = [
    { label: 'Active Auctions', value: '142' },
    { label: '24h Trade Volume', value: '$1.2M' },
    { label: 'Active Traders', value: '894' },
];

const bidActivity = [
    { label: 'Leading', lot: '#ETH-2024-082', detail: 'Current: $4.85 / lb', color: 'bg-emerald-500' },
    { label: 'Outbid', lot: '#COL-2024-051', detail: 'High: $3.90 / lb', color: 'bg-[#BA1A1A]' },
];

const quickNotes = [
    'Export desks are most active in the final 90 minutes.',
    'Robusta windows are drawing more bids this session.',
    'High-score arabica lots are clearing above reserve early.',
];

</script>

<template>
    <AppLayout title="Auction">
        <div class="grid grid-cols-12 gap-6 xl:gap-10">
            <div class="col-span-12 space-y-8 xl:col-span-9 xl:space-y-10">
                <section class="overflow-hidden rounded-2xl border border-[#E6E8EA] bg-white shadow-[0_18px_60px_rgba(15,23,42,0.05)]">
                    <div class="border-b border-[#EEF2F7] bg-[#F8FAFC] px-6 py-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="text-[18px] font-bold tracking-tight text-[#191C1E]">Live auction board</div>
                                <p class="mt-1 text-[13px] text-[#64748B]">
                                    Organized by lot identity, origin quality, bid depth, and time pressure.
                                </p>
                            </div>
                            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-[#94A3B8]">
                                {{ lots.length }} visible windows
                            </div>
                        </div>
                    </div>

                    <div class="hidden overflow-x-auto lg:block">
                        <table class="min-w-full border-separate border-spacing-0 text-left">
                            <thead>
                                <tr class="bg-[#F8FAFC]">
                                    <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Lot ID</th>
                                    <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Origin</th>
                                    <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Variety</th>
                                    <th class="px-6 py-4 text-center text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">SCAA Score</th>
                                    <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Current Bid</th>
                                    <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Time Left</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="lot in lots"
                                    :key="lot.id"
                                    class="group transition-colors hover:bg-slate-50/50"
                                >
                                    <td class="px-6 py-5 align-top">
                                        <div class="space-y-2">
                                            <span class="text-sm font-bold text-[#004532]">{{ lot.id }}</span>
                                            <div>
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.08em]" :class="lot.statusClass">
                                                    {{ lot.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-0.5 h-4 w-6 overflow-hidden rounded-sm bg-slate-200">
                                                <img :src="lot.flag" :alt="lot.origin" class="h-full w-full object-cover" />
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-[#191C1E]">{{ lot.origin }}</div>
                                                <div class="mt-1 text-[11px] text-[#64748B]">{{ lot.producer }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <span class="rounded px-2 py-1 text-[10px] font-bold uppercase tracking-[0.08em]" :class="lot.varietyClass">
                                            {{ lot.variety }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center align-top">
                                        <span class="text-lg font-extrabold tracking-tight text-[#004532]">{{ lot.score }}</span>
                                    </td>
                                    <td class="px-6 py-5 text-right align-top">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-[#191C1E]">{{ lot.bid }}</span>
                                            <span class="mt-1 text-[10px] text-slate-400">{{ lot.bids }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right align-top">
                                        <span class="text-sm font-bold" :class="lot.timeClass">{{ lot.time }}</span>
                                    </td>
                                    <td class="px-6 py-5 text-right align-top">
                                        <button
                                            class="rounded px-4 py-2 text-xs font-bold transition-all"
                                            :class="lot.watching ? 'bg-[#004532] text-white' : 'border border-[#004532] text-[#004532] hover:bg-[#004532] hover:text-white'"
                                        >
                                            {{ lot.watching ? 'WATCHING' : 'BID NOW' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-3 p-4 sm:p-5 lg:hidden">
                        <article
                            v-for="lot in lots"
                            :key="`mobile-${lot.id}`"
                            class="rounded-2xl border border-[#E5E7EB] bg-[#FCFCFC] p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-bold text-[#004532]">{{ lot.id }}</div>
                                    <div class="mt-2 text-[14px] font-semibold text-[#191C1E]">{{ lot.origin }}</div>
                                    <div class="mt-1 text-[12px] text-[#64748B]">{{ lot.producer }}</div>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.08em]" :class="lot.statusClass">
                                    {{ lot.status }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-white px-3 py-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Variety</div>
                                    <div class="mt-2 text-[13px] font-semibold text-[#191C1E]">{{ lot.variety }}</div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Cup score</div>
                                    <div class="mt-2 text-[16px] font-bold text-[#004532]">{{ lot.score }}</div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Current bid</div>
                                    <div class="mt-2 text-[15px] font-bold text-[#191C1E]">{{ lot.bid }}</div>
                                    <div class="mt-1 text-[10px] text-slate-400">{{ lot.bids }}</div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Time left</div>
                                    <div class="mt-2 text-[14px] font-bold" :class="lot.timeClass">{{ lot.time }}</div>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

            </div>

            <div class="col-span-12 space-y-6 xl:col-span-3">
                <section class="space-y-6 rounded-2xl border border-[#E6E8EA] bg-[#F2F4F6] p-6 xl:sticky xl:top-24">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[18px] font-bold tracking-tight text-[#191C1E]">Market Summary</h2>
                        <svg class="size-5 text-[#004532]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 18h16" />
                            <path d="M7 14l3-3 3 2 4-5" />
                        </svg>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="item in marketSummary"
                            :key="item.label"
                            class="flex items-center justify-between rounded-xl bg-white px-4 py-3"
                        >
                            <span class="text-sm font-semibold text-slate-500">{{ item.label }}</span>
                            <span class="font-bold text-[#004532]">{{ item.value }}</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <h3 class="mb-4 text-xs font-extrabold uppercase tracking-widest text-slate-400">My Bid Activity</h3>
                        <div class="space-y-4">
                            <div
                                v-for="item in bidActivity"
                                :key="item.lot"
                                class="flex items-start gap-4 rounded-xl bg-white px-4 py-3"
                            >
                                <div class="mt-1.5 h-2 w-2 rounded-full" :class="item.color"></div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-[#191C1E]">{{ item.label }}: {{ item.lot }}</p>
                                    <p class="mt-1 text-[10px] text-slate-500">{{ item.detail }}</p>
                                </div>
                                <button
                                    v-if="item.label === 'Outbid'"
                                    class="text-[10px] font-bold text-[#004532] hover:underline"
                                >
                                    REVIVE
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Market Sentiment</p>
                        <div class="mt-2 flex items-center gap-2">
                            <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="8" />
                                <path d="M8.5 14c.8 1 2 1.5 3.5 1.5s2.7-.5 3.5-1.5" />
                                <path d="M9 10h.01" />
                                <path d="M15 10h.01" />
                            </svg>
                            <span class="text-sm font-bold text-[#191C1E]">Strongly Bullish</span>
                        </div>
                        <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full w-[85%] bg-emerald-600"></div>
                        </div>
                    </div>

                    <button class="w-full rounded-md bg-[#E6E8EA] py-3 text-xs font-bold uppercase tracking-widest text-[#004532] transition-colors hover:bg-slate-200">
                        View Detailed Analytics
                    </button>
                </section>

            </div>
        </div>
    </AppLayout>
</template>
