<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const auctionWindows = [
    {
        id: 'AUC-301',
        lot: 'Bugisu Reserve AA',
        origin: 'Mt Elgon · Washed · 1,960m',
        currentBid: '$5.42/kg',
        reserve: '$5.20/kg',
        closes: '01h 24m',
        bidders: 12,
        tone: 'text-[#1B6E4B]',
        badge: 'Live',
        badgeClass: 'bg-[#ECFDF5] text-[#1B6E4B]',
    },
    {
        id: 'AUC-304',
        lot: 'Rwenzori Natural Select',
        origin: 'Kasese · Natural · 2,040m',
        currentBid: '$4.88/kg',
        reserve: '$4.75/kg',
        closes: '03h 08m',
        bidders: 8,
        tone: 'text-[#C8862A]',
        badge: 'Competitive',
        badgeClass: 'bg-[#FFF8F0] text-[#9A5A10]',
    },
    {
        id: 'AUC-309',
        lot: 'West Nile Robusta Grade 1',
        origin: 'Arua · Natural · 1,020m',
        currentBid: '$2.31/kg',
        reserve: '$2.20/kg',
        closes: '05h 41m',
        bidders: 5,
        tone: 'text-[#1D4ED8]',
        badge: 'Open',
        badgeClass: 'bg-[#EFF6FF] text-[#1D4ED8]',
    },
];

const biddingSteps = [
    'Review the live bid ladder and reserve threshold before entering.',
    'Confirm quality details, process method, and origin traceability for the lot.',
    'Place your bid before the timer closes to remain in the active round.',
];

const bidLadder = [
    { time: '14:02', bidder: 'Export House A', amount: '$5.42/kg', status: 'Leading' },
    { time: '13:58', bidder: 'Roaster Union', amount: '$5.38/kg', status: 'Outbid' },
    { time: '13:47', bidder: 'Kampala Trader Co.', amount: '$5.31/kg', status: 'Outbid' },
    { time: '13:39', bidder: 'Origin Buyer Desk', amount: '$5.24/kg', status: 'Reserve met' },
];
</script>

<template>
    <AppLayout title="Auction">
        <div class="space-y-4">
            <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:p-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div class="max-w-3xl">
                        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#C8862A]">
                            Auction
                        </p>
                        <h1 class="mt-3 font-display text-[34px] font-bold leading-none tracking-[-0.03em] text-[#111827] sm:text-[42px]">
                            Live auction board
                        </h1>
                        <p class="mt-4 max-w-2xl text-[14px] leading-relaxed text-[#6B7280] sm:text-[15px]">
                            Follow active bidding windows, compare reserve levels, and place auction bids on verified Ugandan coffee lots with full traceability context.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:w-[460px]">
                        <div class="rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] px-4 py-3">
                            <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">Live windows</div>
                            <div class="mt-2 font-display text-[22px] font-bold leading-none text-[#111827]">18</div>
                            <div class="mt-1 text-[11px] text-[#6B7280]">currently open</div>
                        </div>
                        <div class="rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] px-4 py-3">
                            <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">Bidders online</div>
                            <div class="mt-2 font-display text-[22px] font-bold leading-none text-[#111827]">76</div>
                            <div class="mt-1 text-[11px] text-[#6B7280]">active this hour</div>
                        </div>
                        <div class="rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] px-4 py-3">
                            <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">Top arabica</div>
                            <div class="mt-2 font-display text-[22px] font-bold leading-none text-[#111827]">$5.42</div>
                            <div class="mt-1 text-[11px] text-[#6B7280]">highest live bid</div>
                        </div>
                        <div class="rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] px-4 py-3">
                            <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">Reserve met</div>
                            <div class="mt-2 font-display text-[22px] font-bold leading-none text-[#111827]">11</div>
                            <div class="mt-1 text-[11px] text-[#6B7280]">lots this session</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:p-6">
                    <div class="mb-4">
                        <h2 class="font-display text-[20px] font-bold tracking-tight text-[#111827]">
                            Active auction windows
                        </h2>
                        <p class="mt-1 text-[13px] text-[#6B7280]">
                            Lots with live bidding activity and reserve visibility.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <article
                            v-for="window in auctionWindows"
                            :key="window.id"
                            class="rounded-2xl border border-[#E5E7EB] bg-[#FCFCFC] p-4"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">
                                        {{ window.id }}
                                    </div>
                                    <h3 class="mt-2 font-display text-[18px] font-bold tracking-tight text-[#111827]">
                                        {{ window.lot }}
                                    </h3>
                                    <p class="mt-1 text-[13px] text-[#6B7280]">{{ window.origin }}</p>
                                </div>

                                <span class="w-fit rounded-full px-2.5 py-1 text-[10px] font-medium" :class="window.badgeClass">
                                    {{ window.badge }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-4">
                                <div class="rounded-xl bg-white px-3 py-3 border border-[#E5E7EB]">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">Current bid</div>
                                    <div class="mt-2 font-display text-[22px] font-bold leading-none" :class="window.tone">
                                        {{ window.currentBid }}
                                    </div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3 border border-[#E5E7EB]">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">Reserve</div>
                                    <div class="mt-2 font-display text-[22px] font-bold leading-none text-[#111827]">
                                        {{ window.reserve }}
                                    </div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3 border border-[#E5E7EB]">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">Time left</div>
                                    <div class="mt-2 font-display text-[22px] font-bold leading-none text-[#C0392B]">
                                        {{ window.closes }}
                                    </div>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-3 border border-[#E5E7EB]">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">Bidders</div>
                                    <div class="mt-2 font-display text-[22px] font-bold leading-none text-[#111827]">
                                        {{ window.bidders }}
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="space-y-4">
                    <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:p-6">
                        <h2 class="font-display text-[20px] font-bold tracking-tight text-[#111827]">
                            How to bid
                        </h2>
                        <div class="mt-4 space-y-3">
                            <div
                                v-for="(step, index) in biddingSteps"
                                :key="step"
                                class="flex items-start gap-3 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] p-4"
                            >
                                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-[#FFF8F0] font-mono text-[10px] font-medium text-[#C8862A]">
                                    0{{ index + 1 }}
                                </div>
                                <p class="text-[14px] leading-relaxed text-[#4B5563]">{{ step }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="font-display text-[20px] font-bold tracking-tight text-[#111827]">
                                    Bid ladder
                                </h2>
                                <p class="mt-1 text-[13px] text-[#6B7280]">
                                    Latest movement on the lead auction window.
                                </p>
                            </div>
                            <span class="rounded-full bg-[#ECFDF5] px-3 py-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#1B6E4B]">
                                Live
                            </span>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div
                                v-for="bid in bidLadder"
                                :key="`${bid.time}-${bid.bidder}`"
                                class="flex items-center justify-between gap-3 rounded-xl border border-[#E5E7EB] bg-[#FCFCFC] px-4 py-3"
                            >
                                <div>
                                    <div class="text-[14px] font-medium text-[#111827]">{{ bid.bidder }}</div>
                                    <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#9CA3AF]">
                                        {{ bid.time }}
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div class="font-display text-[20px] font-bold leading-none text-[#111827]">
                                        {{ bid.amount }}
                                    </div>
                                    <div class="mt-1 text-[11px] text-[#6B7280]">{{ bid.status }}</div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
