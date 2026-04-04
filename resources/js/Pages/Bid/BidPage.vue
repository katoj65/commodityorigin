<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const filters = [
    { label: 'Arabica', active: true },
    { label: 'Robusta', active: false },
    { label: 'Micro-lots', active: false },
    { label: 'Ending Soon', active: false },
];

const featuredLot = {
    id: 'UG-SIPI-2024-08',
    title: 'Mount Elgon Sipi Falls',
    origin: 'Kapchorwa District, Uganda',
    process: 'Washed Arabica',
    score: '87.5',
    currentBid: '$4.85/lb',
    reserve: '$4.70/lb',
    bidders: '14 desks',
    closes: '02h 45m',
};

const tableMetrics = [
    { label: 'Board depth', value: '5 live lots', tone: 'text-[#111827]' },
    { label: 'Fastest close', value: '42m 10s', tone: 'text-[#B91C1C]' },
    { label: 'Top score', value: '89.2', tone: 'text-[#065F46]' },
];

const auctionLots = [
    {
        id: 'UG-SIPI-2024-08',
        estate: 'Mount Elgon Sipi Falls',
        location: 'Kapchorwa District, Uganda',
        type: 'Washed Arabica',
        badgeClass: 'bg-[#FFF3E0] text-[#8A4B08]',
        score: '87.5',
        bid: '$4.85/lb',
        movement: '+0.15',
        movementClass: 'text-[#047857]',
        reserve: 'Reserve met',
        reserveClass: 'bg-[#ECFDF5] text-[#166534]',
        timeLeft: '02h 45m',
        timeClass: 'text-[#B91C1C]',
    },
    {
        id: 'ET-YRG-2024-12',
        estate: 'Yirgacheffe Chelelektu',
        location: 'Gedeo Zone, Ethiopia',
        type: 'Natural Process',
        badgeClass: 'bg-[#E8FFF5] text-[#065F46]',
        score: '89.2',
        bid: '$6.12/lb',
        movement: 'No recent bids',
        movementClass: 'text-[#94A3B8]',
        reserve: 'Premium lot',
        reserveClass: 'bg-[#EEF2FF] text-[#3730A3]',
        timeLeft: '14h 12m',
        timeClass: 'text-[#475569]',
    },
    {
        id: 'CO-HUL-2024-03',
        estate: 'Huila Finca La Reserva',
        location: 'Pitalito, Colombia',
        type: 'Honey Process',
        badgeClass: 'bg-[#FFF3E0] text-[#8A4B08]',
        score: '88.0',
        bid: '$5.20/lb',
        movement: '+0.05',
        movementClass: 'text-[#047857]',
        reserve: 'Reserve met',
        reserveClass: 'bg-[#ECFDF5] text-[#166534]',
        timeLeft: '01d 04h',
        timeClass: 'text-[#475569]',
    },
    {
        id: 'CR-TAR-2024-21',
        estate: 'Tarrazu Don Mayo',
        location: 'San Marcos, Costa Rica',
        type: 'Washed Arabica',
        badgeClass: 'bg-[#FFF3E0] text-[#8A4B08]',
        score: '86.8',
        bid: '$4.40/lb',
        movement: 'Stable',
        movementClass: 'text-[#94A3B8]',
        reserve: 'Closing fast',
        reserveClass: 'bg-[#FEF2F2] text-[#B91C1C]',
        timeLeft: '42m 10s',
        timeClass: 'text-[#B91C1C]',
    },
    {
        id: 'BR-MIN-2024-55',
        estate: 'Minas Gerais Fazenda',
        location: 'Sul de Minas, Brazil',
        type: 'Specialty Robusta',
        badgeClass: 'bg-[#EEF2FF] text-[#3730A3]',
        score: '84.2',
        bid: '$2.95/lb',
        movement: '+0.02',
        movementClass: 'text-[#047857]',
        reserve: 'Open bid',
        reserveClass: 'bg-[#F8FAFC] text-[#475569]',
        timeLeft: '03d 19h',
        timeClass: 'text-[#475569]',
    },
];

const bidLadder = [
    { time: '14:02', bidder: 'Export House A', amount: '$4.85/lb', state: 'Leading', stateClass: 'text-[#166534]' },
    { time: '13:58', bidder: 'Roaster Union', amount: '$4.81/lb', state: 'Outbid', stateClass: 'text-[#64748B]' },
    { time: '13:47', bidder: 'Kampala Trader Co.', amount: '$4.76/lb', state: 'Reserve met', stateClass: 'text-[#8A4B08]' },
    { time: '13:39', bidder: 'Origin Buyer Desk', amount: '$4.70/lb', state: 'Opening mark', stateClass: 'text-[#64748B]' },
];

const watchlist = [
    { lot: 'Rwenzori Select', note: 'Aggressive bidding in the last 20 minutes', tone: 'bg-[#ECFDF5] text-[#166534]' },
    { lot: 'Bugisu AA Reserve', note: 'High score lot with reserve already cleared', tone: 'bg-[#FFF7ED] text-[#9A3412]' },
    { lot: 'Kisoro Honey Lot', note: 'Thin ladder, likely to move on next bid', tone: 'bg-[#EEF2FF] text-[#3730A3]' },
];

const footerStats = [
    { label: 'Market liquidity', value: '$12.4M' },
    { label: 'Average cup score', value: '86.4' },
    { label: 'Bidders online', value: '1,402' },
    { label: 'Avg close cycle', value: '4.2 Days' },
];
</script>

<template>
    <AppLayout title="Live Auctions">
        <div class="space-y-4">
            <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:p-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h2 class="text-[24px] font-bold tracking-[-0.03em] text-[#111827]">Bid board</h2>
                        <p class="mt-1 text-[13px] text-[#6B7280]">
                            Review active lots, bid velocity, and reserve status before committing capital.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-for="filter in filters"
                            :key="filter.label"
                            type="button"
                            class="rounded-full px-4 py-2 text-[13px] font-medium transition-colors"
                            :class="
                                filter.active
                                    ? 'bg-[#0F2A24] text-white'
                                    : 'bg-[#F8FAFC] text-[#475569] hover:bg-[#EEF2F7]'
                            "
                        >
                            {{ filter.label }}
                        </button>

                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-full border border-[#D0D7DE] px-4 py-2 text-[13px] font-medium text-[#475569]"
                        >
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 6h16M7 12h10M10 18h4" />
                            </svg>
                            More filters
                        </button>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
                <div class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_18px_60px_rgba(15,23,42,0.05)]">
                    <div class="border-b border-[#EEF2F7] bg-[linear-gradient(180deg,#FCFDFC_0%,#F8FBF8_100%)] px-5 py-5 sm:px-6">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0">
                                <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#94A3B8]">Trading table</div>
                                <div class="mt-1 text-[18px] font-semibold tracking-tight text-[#111827]">Live auction windows</div>
                                <p class="mt-1 max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                                    Monitor lead bids, reserve thresholds, and close pressure in one desk-ready board.
                                </p>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-3 xl:min-w-[420px]">
                                <div
                                    v-for="metric in tableMetrics"
                                    :key="metric.label"
                                    class="rounded-2xl border border-[#E5E7EB] bg-white px-4 py-3"
                                >
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">{{ metric.label }}</div>
                                    <div class="mt-2 text-[16px] font-semibold tracking-tight" :class="metric.tone">
                                        {{ metric.value }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 rounded-[20px] border border-[#E5E7EB] bg-white px-4 py-4 sm:px-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-[#0F2A24] px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.14em] text-white">
                                            Lead window
                                        </span>
                                        <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-[#94A3B8]">
                                            {{ featuredLot.id }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-[18px] font-semibold tracking-tight text-[#111827]">
                                        {{ featuredLot.title }}
                                    </div>
                                    <div class="mt-1 text-[13px] text-[#6B7280]">
                                        {{ featuredLot.origin }} · {{ featuredLot.process }}
                                    </div>
                                </div>

                                <div class="grid gap-2 sm:grid-cols-4 lg:min-w-[520px]">
                                    <div class="rounded-2xl bg-[#F8FAFC] px-3 py-3">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Current bid</div>
                                        <div class="mt-2 text-[18px] font-bold leading-none text-[#111827]">{{ featuredLot.currentBid }}</div>
                                    </div>
                                    <div class="rounded-2xl bg-[#F8FAFC] px-3 py-3">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Reserve</div>
                                        <div class="mt-2 text-[18px] font-bold leading-none text-[#111827]">{{ featuredLot.reserve }}</div>
                                    </div>
                                    <div class="rounded-2xl bg-[#F8FAFC] px-3 py-3">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Cup score</div>
                                        <div class="mt-2 text-[18px] font-bold leading-none text-[#065F46]">{{ featuredLot.score }}</div>
                                    </div>
                                    <div class="rounded-2xl bg-[#F8FAFC] px-3 py-3">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Closes</div>
                                        <div class="mt-2 text-[18px] font-bold leading-none text-[#B91C1C]">{{ featuredLot.closes }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 p-4 sm:p-5 lg:hidden">
                        <article
                            v-for="lot in auctionLots"
                            :key="`mobile-${lot.id}`"
                            class="rounded-2xl border border-[#E5E7EB] bg-[#FCFCFC] p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="h-2.5 w-2.5 flex-shrink-0 rounded-full"
                                            :class="lot.timeClass === 'text-[#B91C1C]' ? 'bg-[#FCA5A5]' : 'bg-[#86EFAC]'"
                                        ></span>
                                        <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-[#94A3B8]">{{ lot.id }}</span>
                                    </div>
                                    <div class="mt-2 text-[15px] font-semibold text-[#111827]">{{ lot.estate }}</div>
                                    <div class="mt-1 text-[12px] leading-relaxed text-[#64748B]">{{ lot.location }}</div>
                                </div>

                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em]" :class="lot.badgeClass">
                                    {{ lot.type }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-white px-3 py-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Score</div>
                                    <div class="mt-2 text-[16px] font-bold text-[#065F46]">{{ lot.score }}</div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Current bid</div>
                                    <div class="mt-2 text-[16px] font-bold text-[#111827]">{{ lot.bid }}</div>
                                    <div
                                        class="mt-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium"
                                        :class="lot.movementClass === 'text-[#047857]' ? 'bg-[#ECFDF5] text-[#166534]' : 'bg-[#F8FAFC] text-[#64748B]'"
                                    >
                                        {{ lot.movement }}
                                    </div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Reserve</div>
                                    <div class="mt-2">
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-medium uppercase tracking-[0.08em]" :class="lot.reserveClass">
                                            {{ lot.reserve }}
                                        </span>
                                    </div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">Time left</div>
                                    <div class="mt-2 flex items-center gap-1.5 text-[13px] font-semibold" :class="lot.timeClass">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <circle cx="12" cy="12" r="8" />
                                            <path d="M12 8v4.5l3 2" />
                                        </svg>
                                        {{ lot.timeLeft }}
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="hidden overflow-x-auto lg:block">
                        <table class="min-w-full table-fixed border-collapse text-left">
                            <colgroup>
                                <col class="w-[34%]" />
                                <col class="w-[16%]" />
                                <col class="w-[10%]" />
                                <col class="w-[16%]" />
                                <col class="w-[12%]" />
                                <col class="w-[12%]" />
                            </colgroup>
                            <thead>
                                <tr class="border-b border-[#EEF2F7] bg-[#F8FAFC] text-[11px] uppercase tracking-[0.18em] text-[#64748B]">
                                    <th class="px-6 py-4 font-bold">Lot</th>
                                    <th class="px-4 py-4 font-bold">Profile</th>
                                    <th class="px-4 py-4 text-center font-bold">Score</th>
                                    <th class="px-4 py-4 text-right font-bold">Current bid</th>
                                    <th class="px-4 py-4 text-center font-bold">Reserve</th>
                                    <th class="px-4 py-4 text-center font-bold">Time left</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F5F9]">
                                <tr
                                    v-for="lot in auctionLots"
                                    :key="lot.id"
                                    class="group align-top transition-colors hover:bg-[#FBFDFC]"
                                >
                                    <td class="px-6 py-5">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="mt-1.5 h-2.5 w-2.5 flex-shrink-0 rounded-full"
                                                :class="lot.timeClass === 'text-[#B91C1C]' ? 'bg-[#FCA5A5]' : 'bg-[#86EFAC]'"
                                            ></div>
                                            <div class="min-w-0">
                                                <div class="font-mono text-[10px] uppercase tracking-[0.12em] text-[#94A3B8]">{{ lot.id }}</div>
                                                <div class="mt-2 text-[14px] font-semibold leading-snug text-[#111827]">{{ lot.estate }}</div>
                                                <div class="mt-1 text-[12px] leading-relaxed text-[#64748B]">{{ lot.location }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5">
                                        <div class="space-y-2">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em]" :class="lot.badgeClass">
                                                {{ lot.type }}
                                            </span>
                                            <div class="text-[11px] text-[#94A3B8]">Lot profile</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 text-center">
                                        <div class="text-[16px] font-bold leading-none text-[#065F46]">{{ lot.score }}</div>
                                        <div class="mt-2 text-[11px] text-[#94A3B8]">cup score</div>
                                    </td>
                                    <td class="px-4 py-5 text-right">
                                        <div class="text-[16px] font-bold leading-none text-[#111827]">{{ lot.bid }}</div>
                                        <div
                                            class="mt-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium"
                                            :class="lot.movementClass === 'text-[#047857]' ? 'bg-[#ECFDF5] text-[#166534]' : 'bg-[#F8FAFC] text-[#64748B]'"
                                        >
                                            {{ lot.movement }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 text-center">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-medium uppercase tracking-[0.08em]" :class="lot.reserveClass">
                                            {{ lot.reserve }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold" :class="lot.timeClass">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <circle cx="12" cy="12" r="8" />
                                                <path d="M12 8v4.5l3 2" />
                                            </svg>
                                            {{ lot.timeLeft }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <aside class="space-y-4">
                    <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_18px_60px_rgba(15,23,42,0.05)]">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#94A3B8]">Desk summary</div>
                                <h3 class="mt-1 text-[18px] font-bold tracking-tight text-[#111827]">Quick portfolio</h3>
                            </div>
                            <div class="rounded-full bg-[#F8FAFC] p-2 text-[#64748B]">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 19V5M10 19v-8M16 19v-4M22 19V9" />
                                </svg>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-2xl bg-[#F8FAFC] p-4">
                                <div class="font-mono text-[9px] uppercase tracking-[0.16em] text-[#94A3B8]">Active bids</div>
                                <div class="mt-2 text-[28px] font-bold leading-none text-[#111827]">12 <span class="text-[13px] font-normal text-[#64748B]">lots</span></div>
                            </div>
                            <div class="rounded-2xl bg-[#F8FAFC] p-4">
                                <div class="font-mono text-[9px] uppercase tracking-[0.16em] text-[#94A3B8]">Winning</div>
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <div class="text-[28px] font-bold leading-none text-[#166534]">08</div>
                                    <span class="rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[10px] font-bold uppercase text-[#166534]">Top bid</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-[#F8FAFC] p-4">
                                <div class="font-mono text-[9px] uppercase tracking-[0.16em] text-[#94A3B8]">Total exposure</div>
                                <div class="mt-2 text-[28px] font-bold leading-none text-[#111827]">$248,500</div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-5 w-full rounded-full bg-[#0F2A24] px-4 py-3 text-[13px] font-semibold text-white transition-opacity hover:opacity-92"
                        >
                            Manage all bids
                        </button>
                    </section>

                    <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-[0_18px_60px_rgba(15,23,42,0.05)]">
                        <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#94A3B8]">Lead ladder</div>
                        <h3 class="mt-1 text-[18px] font-bold tracking-tight text-[#111827]">Bid movement</h3>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="entry in bidLadder"
                                :key="`${entry.time}-${entry.bidder}`"
                                class="rounded-2xl border border-[#EEF2F7] bg-[#FCFDFC] px-4 py-3"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-[14px] font-semibold text-[#111827]">{{ entry.bidder }}</div>
                                        <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.14em] text-[#94A3B8]">{{ entry.time }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[16px] font-bold text-[#111827]">{{ entry.amount }}</div>
                                        <div class="mt-1 text-[11px] font-medium" :class="entry.stateClass">{{ entry.state }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-[#E5E7EB] bg-[linear-gradient(180deg,#FFF9F0_0%,#FFFFFF_100%)] p-5 shadow-[0_18px_60px_rgba(15,23,42,0.05)]">
                        <div class="font-mono text-[10px] uppercase tracking-[0.16em] text-[#9A5A10]">Watchlist pulse</div>
                        <h3 class="mt-1 text-[18px] font-bold tracking-tight text-[#111827]">What needs attention</h3>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="item in watchlist"
                                :key="item.lot"
                                class="rounded-2xl border border-[#F1E7D3] bg-white/90 px-4 py-4"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-[14px] font-semibold text-[#111827]">{{ item.lot }}</div>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase" :class="item.tone">Watch</span>
                                </div>
                                <div class="mt-2 text-[12px] leading-relaxed text-[#6B7280]">{{ item.note }}</div>
                            </div>
                        </div>
                    </section>
                </aside>
            </section>

            <section class="grid gap-4 rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">
                <div v-for="stat in footerStats" :key="stat.label" class="space-y-1">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-[#94A3B8]">{{ stat.label }}</div>
                    <div class="text-[24px] font-bold tracking-tight text-[#111827]">{{ stat.value }}</div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
