<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const selectedRange = ref('1W');
const selectedType = ref('all');

const stats = [
    {
        label: 'Portfolio',
        value: '$284,720',
        accent: 'text-[#C8862A]',
        icon: 'layers',
        helper: 'this month',
        trend: '▲ 4.2%',
        badgeClass: 'bg-[#ECFDF5] text-[#2D7A55]',
    },
    {
        label: 'Active bids',
        value: '14',
        accent: 'text-[#2D7A55]',
        icon: 'pulse',
        helper: 'today',
        trend: '● 3 expiring',
        badgeClass: 'bg-[#FEF2F2] text-[#C0392B]',
    },
    {
        label: 'Lots won YTD',
        value: '47',
        accent: 'text-[#1B6E4B]',
        icon: 'check',
        helper: 'vs last year',
        trend: '▲ 12',
        badgeClass: 'bg-[#ECFDF5] text-[#2D7A55]',
    },
    {
        label: 'Avg cup score',
        value: '85.4',
        accent: 'text-[#C8862A]',
        icon: 'star',
        helper: 'SCAA protocol',
        trend: '▲ 1.2pts',
        badgeClass: 'bg-[#ECFDF5] text-[#2D7A55]',
    },
];

const rangeData = {
    '1W': {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        arabica: [4.92, 4.88, 4.95, 5.01, 4.97, 5.05, 5.1],
        robusta: [2.2, 2.18, 2.25, 2.28, 2.22, 2.35, 2.4],
    },
    '1M': {
        labels: ['W1', 'W2', 'W3', 'W4'],
        arabica: [4.61, 4.74, 4.92, 5.04],
        robusta: [2.08, 2.16, 2.24, 2.31],
    },
    '3M': {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        arabica: [4.28, 4.41, 4.5, 4.72, 4.89, 5.03],
        robusta: [1.92, 2.01, 2.08, 2.15, 2.24, 2.33],
    },
    '1Y': {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        arabica: [4.02, 4.28, 4.64, 5.02],
        robusta: [1.81, 1.94, 2.12, 2.29],
    },
};

const summaryCards = [
    {
        title: 'Arabica Lots Won',
        amount: '$47,280',
        lastMonth: '$39,485 USD',
        accentClass: 'text-[#1B6E4B]',
        iconBg: 'bg-[#F0FBF5]',
        icon: 'cup',
    },
    {
        title: 'Robusta Lots Won',
        amount: '$31,440',
        lastMonth: '$28,210 USD',
        accentClass: 'text-[#C8862A]',
        iconBg: 'bg-[#FFF8F0]',
        icon: 'cup',
    },
];

const lots = [
    {
        id: 'BOE-0441',
        name: 'Sipi Falls AA',
        origin: 'Bugisu · Washed · 1,900m',
        type: 'arabica',
        score: '87.2',
        price: '$5.10',
        delta: '▲ 1.2%',
        deltaClass: 'text-[#2D7A55]',
        availability: 42,
        sparkline: '0,20 12,17 24,13 36,15 48,9 60,7 72,5',
    },
    {
        id: 'BOE-0438',
        name: 'Kasese Natural',
        origin: 'Rwenzori · Natural · 2,100m',
        type: 'arabica',
        score: '85.8',
        price: '$4.65',
        delta: '▲ 0.8%',
        deltaClass: 'text-[#2D7A55]',
        availability: 18,
        sparkline: '0,13 12,15 24,11 36,9 48,12 60,8 72,6',
    },
    {
        id: 'BOE-0429',
        name: 'Kibale Fine Robusta',
        origin: 'Mubende · Natural · 1,300m',
        type: 'robusta',
        score: '82.0',
        price: '$2.40',
        delta: '▲ 3.1%',
        deltaClass: 'text-[#2D7A55]',
        availability: 78,
        sparkline: '0,21 12,19 24,21 36,17 48,15 60,13 72,10',
    },
    {
        id: 'BOE-0435',
        name: 'Kisoro Highlands AB',
        origin: 'Kisoro · Honey · 2,000m',
        type: 'arabica',
        score: '86.4',
        price: '$4.82',
        delta: '▼ 0.4%',
        deltaClass: 'text-[#C0392B]',
        availability: 65,
        sparkline: '0,17 12,15 24,13 36,16 48,11 60,13 72,9',
    },
    {
        id: 'BOE-0421',
        name: 'West Nile FAQ Grade 1',
        origin: 'Arua · Natural · 1,000m',
        type: 'robusta',
        score: '79.4',
        price: '$1.98',
        delta: '▲ 0.3%',
        deltaClass: 'text-[#2D7A55]',
        availability: 90,
        sparkline: '0,17 12,19 24,16 36,17 48,15 60,16 72,14',
    },
];

const actionItems = [
    {
        title: 'Pending Bids to Review',
        body: 'You have 14 active bids and 3 expiring today. Review and take action.',
        accent: 'text-[#C0392B]',
        bg: 'bg-[#FEF2F2]',
        icon: 'wallet',
    },
    {
        title: 'UCDA Lot Verifications',
        body: 'There are 8 new verified lots matching your watchlist filters.',
        accent: 'text-[#1B6E4B]',
        bg: 'bg-[#F0FBF5]',
        icon: 'shield',
    },
    {
        title: 'Farm Messages',
        body: 'There are 5 new messages from partner farms and cooperatives.',
        accent: 'text-[#C8862A]',
        bg: 'bg-[#FFF8F0]',
        icon: 'message',
    },
    {
        title: 'Incoming Shipments',
        body: 'You have 3 shipments in transit — track on-chain provenance.',
        accent: 'text-[#2D7A55]',
        bg: 'bg-[#ECFDF5]',
        icon: 'pin',
    },
];

const regionVolumes = [
    { label: 'Bugisu', value: 38, color: '#C8862A' },
    { label: 'Rwenzori', value: 22, color: '#1B6E4B' },
    { label: 'West Nile', value: 19, color: '#52B788' },
    { label: 'Mubende', value: 12, color: '#8B5CF6' },
    { label: 'Other', value: 9, color: '#60A5FA' },
];

const displayedRange = computed(() => rangeData[selectedRange.value]);
const maxChartValue = computed(() => Math.max(...displayedRange.value.arabica, ...displayedRange.value.robusta));

const chartBars = computed(() =>
    displayedRange.value.labels.map((label, index) => ({
        label,
        arabica: displayedRange.value.arabica[index],
        robusta: displayedRange.value.robusta[index],
    })),
);

const filteredLots = computed(() => {
    if (selectedType.value === 'all') {
        return lots;
    }

    return lots.filter((lot) => lot.type === selectedType.value);
});
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="flex flex-col gap-4 xl:flex-row xl:gap-5">
            <div class="flex min-w-0 flex-1 flex-col gap-4">
                <div class="flex flex-col gap-4 rounded-xl border border-[#E5E7EB] bg-white px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="font-display text-[20px] font-bold leading-none tracking-tight text-[#111827]">
                            Trader Dashboard
                        </h1>
                        <p class="mt-1 max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                            Uganda Coffee Commodity Exchange · From Farm to Cup, Transparently
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        <button class="dashboard-secondary-btn">
                            <svg class="dashboard-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                <path d="M7 10l5 5 5-5" />
                                <path d="M12 15V3" />
                            </svg>
                            Export
                        </button>
                        <button class="dashboard-secondary-btn">
                            <svg class="dashboard-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z" />
                                <path d="M9 19V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                                <path d="M15 19a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2z" />
                            </svg>
                            Reports
                        </button>
                        <Link :href="route('bid.index')" class="dashboard-primary-btn no-underline">
                            <svg class="dashboard-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14" />
                                <path d="M5 12l7 7 7-7" />
                            </svg>
                            Place Bid
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="stat-card rounded-xl border border-[#E5E7EB] bg-white p-4"
                    >
                        <div class="mb-2 flex items-start justify-between">
                            <span class="font-mono text-[9px] uppercase tracking-[0.12em] text-[#6B7280]">
                                {{ stat.label }}
                            </span>
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF8F0] text-[#C8862A]">
                                <svg v-if="stat.icon === 'layers'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z" />
                                    <path d="M2 17l10 5 10-5" />
                                    <path d="M2 12l10 5 10-5" />
                                </svg>
                                <svg v-else-if="stat.icon === 'pulse'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                                </svg>
                                <svg v-else-if="stat.icon === 'check'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4" />
                                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <svg v-else class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                            </div>
                        </div>
                        <div class="font-display text-[22px] font-bold leading-none tracking-tight text-[#111827]">
                            {{ stat.value }}
                        </div>
                        <div class="mt-2 flex items-center gap-1.5">
                            <span class="rounded px-1.5 py-0.5 font-mono text-[9px]" :class="stat.badgeClass">
                                {{ stat.trend }}
                            </span>
                            <span class="text-[11px] text-[#6B7280]">{{ stat.helper }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-[#E5E7EB] bg-white p-4 sm:p-5">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <h2 class="font-display text-[16px] font-bold tracking-tight text-[#111827]">
                                Lot Price Overview
                            </h2>
                            <p class="text-[12px] leading-relaxed text-[#6B7280]">
                                Last 7 days buy and bid overview.
                                <a href="#" class="font-medium text-[#C8862A] no-underline hover:underline" @click.prevent>
                                    Detailed Stats
                                </a>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex flex-wrap gap-1">
                                <button
                                    v-for="range in Object.keys(rangeData)"
                                    :key="range"
                                    class="range-btn"
                                    :class="{ active: selectedRange === range }"
                                    @click="selectedRange = range"
                                >
                                    {{ range }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="grid h-[220px] min-w-[470px] grid-cols-7 items-end gap-3 rounded-xl bg-[#FAFAFA] px-4 pb-6 pt-4 sm:min-w-0">
                            <div
                                v-for="bar in chartBars"
                                :key="bar.label"
                                class="flex h-full flex-col justify-end gap-1"
                            >
                                <div class="flex h-full items-end gap-1">
                                    <div
                                        class="w-1/2 rounded-t-[3px] bg-[#1B6E4B]/75"
                                        :style="{ height: `${(bar.arabica / maxChartValue) * 100}%` }"
                                    ></div>
                                    <div
                                        class="w-1/2 rounded-t-[3px] bg-[#C8862A]/70"
                                        :style="{ height: `${(bar.robusta / maxChartValue) * 100}%` }"
                                    ></div>
                                </div>
                                <div class="text-center font-mono text-[8px] text-[#8A7A6A]">
                                    {{ bar.label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 xl:grid-cols-2">
                        <div
                            v-for="summary in summaryCards"
                            :key="summary.title"
                            class="flex flex-col items-start gap-4 rounded-xl border border-[#E5E7EB] bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <div class="font-display text-[22px] font-bold leading-none tracking-tight" :class="summary.accentClass">
                                    {{ summary.amount }} <span class="text-[13px] font-normal">USD</span>
                                </div>
                                <div class="mt-1 text-[11px] text-[#6B7280]">
                                    Last month <span class="font-medium text-[#111827]">{{ summary.lastMonth }}</span>
                                </div>
                                <div class="mt-2 text-[13px] font-medium" :class="summary.accentClass">
                                    {{ summary.title }}
                                </div>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-[#E5E7EB]" :class="summary.iconBg">
                                <svg class="h-6 w-6" :class="summary.accentClass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M17 8h1a4 4 0 010 8h-1" />
                                    <path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                    <div class="flex flex-col gap-3 border-b border-[#E5E7EB] px-4 py-3.5 sm:px-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <h3 class="font-display text-[14px] font-bold tracking-tight text-[#111827]">
                                Active Market Lots
                            </h3>
                            <p class="text-[11px] text-[#6B7280]">
                                Live listings on the exchange · 312 total
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <button class="table-filter-btn" :class="{ active: selectedType === 'all' }" @click="selectedType = 'all'">
                                All
                            </button>
                            <button class="table-filter-btn" :class="{ active: selectedType === 'arabica' }" @click="selectedType = 'arabica'">
                                Arabica
                            </button>
                            <button class="table-filter-btn" :class="{ active: selectedType === 'robusta' }" @click="selectedType = 'robusta'">
                                Robusta
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] border-collapse">
                            <thead>
                                <tr class="bg-[#F9FAFB]">
                                    <th class="dashboard-th">Lot ID</th>
                                    <th class="dashboard-th">Coffee &amp; Origin</th>
                                    <th class="dashboard-th">Type</th>
                                    <th class="dashboard-th">Score</th>
                                    <th class="dashboard-th">Trend</th>
                                    <th class="dashboard-th">Price</th>
                                    <th class="dashboard-th">Avail.</th>
                                    <th class="border-b border-[#E5E7EB]"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="lot in filteredLots" :key="lot.id" class="lot-row cursor-pointer">
                                    <td class="dashboard-td">
                                        <div class="font-mono text-[9px] font-medium text-[#C8862A]">{{ lot.id }}</div>
                                    </td>
                                    <td class="dashboard-td">
                                        <div class="text-[12px] font-medium text-[#111827]">{{ lot.name }}</div>
                                        <div class="font-mono text-[9px] text-[#6B7280]">{{ lot.origin }}</div>
                                    </td>
                                    <td class="dashboard-td">
                                        <span
                                            class="rounded-md border px-2 py-0.5 font-mono text-[8px] uppercase tracking-[0.08em]"
                                            :class="lot.type === 'arabica'
                                                ? 'border-[#E5E7EB] bg-[#F0FBF5] text-[#1B6E4B]'
                                                : 'border-[#E5E7EB] bg-[#FFF5EB] text-[#7A4F1A]'"
                                        >
                                            {{ lot.type }}
                                        </span>
                                    </td>
                                    <td class="dashboard-td">
                                        <div class="font-mono text-[11px] font-bold" :class="lot.type === 'arabica' ? 'text-[#2D7A55]' : 'text-[#C8862A]'">
                                            {{ lot.score }}
                                        </div>
                                    </td>
                                    <td class="dashboard-td">
                                        <svg class="h-[26px] w-[72px]" viewBox="0 0 72 26">
                                            <polyline
                                                :points="lot.sparkline"
                                                fill="none"
                                                :stroke="lot.type === 'arabica' ? '#2D7A55' : '#C8862A'"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </td>
                                    <td class="dashboard-td">
                                        <div class="font-mono text-[12px] font-bold text-[#111827]">{{ lot.price }}</div>
                                        <div class="font-mono text-[9px]" :class="lot.deltaClass">{{ lot.delta }}</div>
                                    </td>
                                    <td class="dashboard-td">
                                        <div class="avail-bar">
                                            <div class="avail-fill" :class="{ low: lot.availability < 20 }" :style="{ width: `${lot.availability}%` }"></div>
                                        </div>
                                    </td>
                                    <td class="dashboard-td">
                                        <button class="bid-btn">Bid now</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="flex w-full flex-shrink-0 flex-col gap-4 xl:w-72">
                <div class="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                    <div class="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3.5">
                        <h3 class="font-display text-[14px] font-bold tracking-tight text-[#111827]">
                            Action Center
                        </h3>
                    </div>
                    <a
                        v-for="item in actionItems"
                        :key="item.title"
                        href="#"
                        class="action-row flex items-start gap-3 border-b border-[#E5E7EB]/60 px-4 py-3.5 no-underline last:border-b-0"
                        @click.prevent
                    >
                        <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl border border-[#E5E7EB]" :class="item.bg">
                            <svg v-if="item.icon === 'wallet'" class="h-4 w-4" :class="item.accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" />
                                <circle cx="9" cy="12" r="2" />
                            </svg>
                            <svg v-else-if="item.icon === 'shield'" class="h-4 w-4" :class="item.accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M9 12l2 2 4-4" />
                                <path d="M21 12c0 5.591-3.824 10.29-9 11.622C6.824 22.29 3 17.591 3 12c0-1.042.133-2.052.382-3.016A11.955 11.955 0 0112 5.944a11.955 11.955 0 018.618 3.04A12.02 12.02 0 0121 12z" />
                            </svg>
                            <svg v-else-if="item.icon === 'message'" class="h-4 w-4" :class="item.accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M8 10h.01" />
                                <path d="M12 10h.01" />
                                <path d="M16 10h.01" />
                                <path d="M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <svg v-else class="h-4 w-4" :class="item.accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-0.5 text-[12px] font-medium text-[#111827]">{{ item.title }}</div>
                            <div class="text-[11px] leading-relaxed text-[#6B7280]">{{ item.body }}</div>
                        </div>
                        <svg class="mt-1 h-4 w-4 flex-shrink-0 text-[#6B7280]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </a>
                </div>

                <div class="rounded-xl border border-[#E5E7EB] bg-white p-4">
                    <div class="mb-3">
                        <h3 class="font-display text-[13px] font-bold tracking-tight text-[#111827]">
                            Volume by Region
                        </h3>
                        <p class="font-mono text-[9px] text-[#6B7280]">47,200 kg traded this month</p>
                    </div>
                    <div class="mx-auto mb-4 flex h-[112px] w-[112px] items-center justify-center rounded-full border-[14px] border-[#F3F4F6] sm:h-[130px] sm:w-[130px] sm:border-[16px]">
                        <div class="text-center">
                            <div class="font-display text-[22px] font-bold text-[#111827]">47.2K</div>
                            <div class="font-mono text-[9px] text-[#6B7280]">kg</div>
                        </div>
                    </div>
                    <div class="grid gap-1.5">
                        <div
                            v-for="region in regionVolumes"
                            :key="region.label"
                            class="flex items-center gap-2 border-b border-[#E5E7EB]/50 py-1 last:border-b-0"
                        >
                            <div class="h-2.5 w-2.5 flex-shrink-0 rounded-sm" :style="{ backgroundColor: region.color }"></div>
                            <span class="flex-1 text-[11px] text-[#111827]">{{ region.label }}</span>
                            <div class="prog-bar w-20 flex-shrink-0">
                                <div class="prog-fill" :style="{ width: `${region.value}%`, backgroundColor: region.color }"></div>
                            </div>
                            <span class="w-7 text-right font-mono text-[10px] text-[#6B7280]">{{ region.value }}%</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-[#E5E7EB] bg-white p-4">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[#C8862A] text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 12l2 2 4-4" />
                            <path d="M21 12c0 5.591-3.824 10.29-9 11.622C6.824 22.29 3 17.591 3 12c0-1.042.133-2.052.382-3.016A11.955 11.955 0 0112 5.944a11.955 11.955 0 018.618 3.04A12.02 12.02 0 0121 12z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-[12px] font-bold text-[#111827]">UCDA Verified Trader</div>
                        <div class="mt-0.5 font-mono text-[9px] text-[#6B7280]">ID: TRD-UGA-20240188</div>
                        <div class="mt-1 font-mono text-[9px] text-[#C8862A]">● Active · Pearl of Africa</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-display {
    font-family: 'IBM Plex Sans', sans-serif;
}

.font-mono {
    font-family: 'IBM Plex Mono', monospace;
}

.dashboard-secondary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    border-radius: 0.5rem;
    padding: 0.5rem 0.875rem;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.15s ease;
}

.dashboard-secondary-btn:hover {
    background: #fff8f0;
    border-color: rgba(200, 134, 42, 0.3);
    color: #c8862a;
}

.dashboard-primary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border: 1px solid #c8862a;
    background: #c8862a;
    color: #fff;
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.15s ease;
}

.dashboard-primary-btn:hover {
    background: #e09b3a;
    border-color: #e09b3a;
}

.dashboard-btn-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.range-btn,
.table-filter-btn {
    border: 1px solid #e5e7eb;
    color: #6b7280;
    background: #fff;
    border-radius: 0.5rem;
    padding: 0.375rem 0.75rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition: all 0.15s ease;
}

.range-btn:hover,
.table-filter-btn:hover {
    border-color: rgba(200, 134, 42, 0.4);
    background: #fff8f0;
    color: #c8862a;
}

.range-btn.active,
.table-filter-btn.active {
    border-color: #c8862a;
    background: #c8862a;
    color: #fff;
}

.stat-card {
    transition: border-color 0.2s ease;
}

.stat-card:hover {
    border-color: #c8862a;
}

.dashboard-th {
    border-bottom: 1px solid #e5e7eb;
    padding: 0.625rem 1.25rem;
    text-align: left;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 8px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #6b7280;
}

.dashboard-td {
    border-bottom: 1px solid rgba(229, 231, 235, 0.6);
    padding: 0.75rem 1.25rem;
}

.lot-row:hover td {
    background: #fdfaf5;
}

.bid-btn {
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    border-radius: 0.5rem;
    padding: 0.375rem 0.75rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    transition: all 0.2s ease;
}

.bid-btn:hover {
    background: #c8862a;
    border-color: #c8862a;
    color: #fff;
}

.avail-bar {
    width: 60px;
    height: 3px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
}

.avail-fill {
    height: 100%;
    background: #c8862a;
    border-radius: 2px;
}

.avail-fill.low {
    background: #c0392b;
}

.action-row {
    transition: background 0.15s ease;
}

.action-row:hover {
    background: #fdfaf5;
}

.prog-bar {
    height: 3px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
}

.prog-fill {
    height: 100%;
    border-radius: 2px;
}

@media (max-width: 639px) {
    .dashboard-secondary-btn,
    .dashboard-primary-btn {
        flex: 1 1 calc(50% - 0.5rem);
        min-width: 140px;
    }

    .dashboard-th,
    .dashboard-td {
        padding-left: 0.875rem;
        padding-right: 0.875rem;
    }
}
</style>
