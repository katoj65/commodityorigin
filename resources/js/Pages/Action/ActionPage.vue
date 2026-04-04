<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const actionQueues = [
    {
        title: 'Pending approvals',
        value: '08',
        detail: 'lots waiting for trader review',
        tone: 'text-[#B45309]',
        bg: 'bg-[#FFF7ED]',
    },
    {
        title: 'Expiring bids',
        value: '03',
        detail: 'require action in the next 2 hours',
        tone: 'text-[#B91C1C]',
        bg: 'bg-[#FEF2F2]',
    },
    {
        title: 'Open issues',
        value: '11',
        detail: 'traceability items from partner desks',
        tone: 'text-[#1D4ED8]',
        bg: 'bg-[#EFF6FF]',
    },
];

const actionItems = [
    {
        label: 'Review pending bids',
        body: 'Confirm reserve alignment and approve the desks that are closest to closing.',
        badge: 'Priority',
        badgeClass: 'bg-[#FEF2F2] text-[#B91C1C]',
    },
    {
        label: 'Validate lot documents',
        body: 'Check grade certificates, origin details, and shipping readiness for newly submitted lots.',
        badge: 'Operations',
        badgeClass: 'bg-[#EFF6FF] text-[#1D4ED8]',
    },
    {
        label: 'Respond to farmer updates',
        body: 'Three partner cooperatives have submitted fresh onboarding and lot availability changes.',
        badge: 'Inbox',
        badgeClass: 'bg-[#ECFDF5] text-[#166534]',
    },
];
</script>

<template>
    <AppLayout title="Action Center">
        <div class="space-y-4">
            <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:p-6">
                <div class="max-w-3xl">
                    <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#C8862A]">Action</p>
                    <h1 class="mt-3 font-display text-[34px] font-bold leading-none tracking-[-0.03em] text-[#111827] sm:text-[42px]">
                        Action center
                    </h1>
                    <p class="mt-4 text-[14px] leading-relaxed text-[#6B7280] sm:text-[15px]">
                        Use this workspace to follow up on urgent approvals, review expiring bids, and keep trading operations moving.
                    </p>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="queue in actionQueues"
                    :key="queue.title"
                    class="rounded-2xl border border-[#E5E7EB] bg-white p-5"
                >
                    <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#9CA3AF]">{{ queue.title }}</div>
                    <div class="mt-3 inline-flex rounded-2xl px-4 py-3 text-[28px] font-bold leading-none" :class="[queue.bg, queue.tone]">
                        {{ queue.value }}
                    </div>
                    <p class="mt-3 text-[13px] leading-relaxed text-[#6B7280]">{{ queue.detail }}</p>
                </article>
            </section>

            <section class="rounded-2xl border border-[#E5E7EB] bg-white p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-[20px] font-bold tracking-tight text-[#111827]">
                            Current action items
                        </h2>
                        <p class="mt-1 text-[13px] text-[#6B7280]">
                            A lightweight queue for operational follow-up and trader review.
                        </p>
                    </div>
                    <span class="rounded-full bg-[#FFF8F0] px-3 py-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#9A5A10]">
                        Team queue
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    <article
                        v-for="item in actionItems"
                        :key="item.label"
                        class="rounded-2xl border border-[#E5E7EB] bg-[#FCFCFC] p-4"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-[16px] font-semibold tracking-tight text-[#111827]">{{ item.label }}</h3>
                                <p class="mt-2 max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">{{ item.body }}</p>
                            </div>
                            <span class="w-fit rounded-full px-2.5 py-1 text-[10px] font-medium uppercase tracking-[0.08em]" :class="item.badgeClass">
                                {{ item.badge }}
                            </span>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
