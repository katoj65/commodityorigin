<script setup>
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const selectedType = ref('all');
const searchQuery = ref('');

const spotlightLots = [
    {
        id: 'MKT-104',
        name: 'Bugisu Washed AA',
        region: 'Mt Elgon · Arabica',
        price: '$5.24/kg',
        score: '87.6',
        status: 'Hot lot',
        statusClass: 'bg-[#FFF5EB] text-[#9A5A10]',
    },
    {
        id: 'MKT-118',
        name: 'Rwenzori Natural AB',
        region: 'Kasese · Arabica',
        price: '$4.81/kg',
        score: '85.9',
        status: 'New arrival',
        statusClass: 'bg-[#ECFDF5] text-[#1B6E4B]',
    },
    {
        id: 'MKT-096',
        name: 'West Nile Fine Robusta',
        region: 'Arua · Robusta',
        price: '$2.42/kg',
        score: '82.4',
        status: 'Ready to bid',
        statusClass: 'bg-[#EFF6FF] text-[#1D4ED8]',
    },
];

const lots = [
    {
        id: 'LOT-2201',
        name: 'Sipi Falls Premium',
        origin: 'Bugisu · Washed · 1,920m',
        type: 'arabica',
        quantity: '1,280 kg',
        price: '$5.18/kg',
        score: '87.1',
        window: 'Closes in 2h',
    },
    {
        id: 'LOT-2208',
        name: 'Kisoro Highlands AB',
        origin: 'Kisoro · Honey · 2,010m',
        type: 'arabica',
        quantity: '860 kg',
        price: '$4.92/kg',
        score: '86.3',
        window: 'Closes tomorrow',
    },
    {
        id: 'LOT-2214',
        name: 'Kibale Estate Robusta',
        origin: 'Mubende · Natural · 1,280m',
        type: 'robusta',
        quantity: '2,400 kg',
        price: '$2.36/kg',
        score: '81.9',
        window: 'Open for 6h',
    },
    {
        id: 'LOT-2219',
        name: 'Rwenzori Select Natural',
        origin: 'Kasese · Natural · 2,080m',
        type: 'arabica',
        quantity: '940 kg',
        price: '$4.75/kg',
        score: '85.4',
        window: 'Closes in 5h',
    },
    {
        id: 'LOT-2223',
        name: 'West Nile FAQ Grade 1',
        origin: 'Arua · Natural · 1,040m',
        type: 'robusta',
        quantity: '3,120 kg',
        price: '$2.04/kg',
        score: '79.8',
        window: 'Open for 1d',
    },
];

const marketStats = [
    { label: 'Active lots', value: '312', hint: 'live on exchange' },
    { label: 'Auction windows', value: '18', hint: 'closing today' },
    { label: 'Avg arabica', value: '$4.98', hint: 'per kilogram' },
    { label: 'Avg robusta', value: '$2.21', hint: 'per kilogram' },
];

const filteredLots = computed(() => {
    const normalizedQuery = searchQuery.value.trim().toLowerCase();

    return lots.filter((lot) => {
        const matchesType =
            selectedType.value === 'all' || lot.type === selectedType.value;

        if (!matchesType) {
            return false;
        }

        if (!normalizedQuery) {
            return true;
        }

        return [lot.id, lot.name, lot.origin, lot.type]
            .join(' ')
            .toLowerCase()
            .includes(normalizedQuery);
    });
});
</script>

<template>
    <AppLayout title="Marketplace">
        <div class="space-y-4">
            <section class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white">
                <div class="border-b border-[#E5E7EB] px-5 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="font-display text-[18px] font-bold tracking-tight text-[#111827]">
                                Marketplace board
                            </h2>
                            <p class="mt-1 text-[13px] text-[#6B7280]">
                                Active trading board for lots currently open in the exchange.
                            </p>
                        </div>

                        <label class="market-search-wrapper">
                            <svg class="market-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <circle cx="11" cy="11" r="7" />
                                <path d="M20 20l-3.5-3.5" />
                            </svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                class="market-search-input"
                                placeholder="Search lots, origin, type, or ID"
                            />
                        </label>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] border-collapse">
                        <thead>
                            <tr class="bg-[#F9FAFB]">
                                <th class="market-th">Lot</th>
                                <th class="market-th">Origin</th>
                                <th class="market-th">Type</th>
                                <th class="market-th">Quantity</th>
                                <th class="market-th">Cup score</th>
                                <th class="market-th">Current price</th>
                                <th class="market-th">Auction window</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="lot in filteredLots"
                                :key="lot.id"
                                class="border-b border-[#E5E7EB]/70 last:border-b-0"
                            >
                                <td class="market-td">
                                    <div class="font-display text-[15px] font-semibold text-[#111827]">
                                        {{ lot.name }}
                                    </div>
                                    <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.12em] text-[#C8862A]">
                                        {{ lot.id }}
                                    </div>
                                </td>
                                <td class="market-td text-[13px] text-[#6B7280]">{{ lot.origin }}</td>
                                <td class="market-td">
                                    <span
                                        class="rounded-full px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.12em]"
                                        :class="lot.type === 'arabica'
                                            ? 'bg-[#ECFDF5] text-[#1B6E4B]'
                                            : 'bg-[#FFF5EB] text-[#9A5A10]'"
                                    >
                                        {{ lot.type }}
                                    </span>
                                </td>
                                <td class="market-td font-medium text-[#111827]">{{ lot.quantity }}</td>
                                <td class="market-td">
                                    <span class="font-display text-[18px] font-bold text-[#111827]">{{ lot.score }}</span>
                                </td>
                                <td class="market-td">
                                    <span class="font-display text-[18px] font-bold text-[#111827]">{{ lot.price }}</span>
                                </td>
                                <td class="market-td text-[13px] text-[#6B7280]">{{ lot.window }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.market-filter-btn {
    border: 1px solid #e5e7eb;
    background: #ffffff;
    border-radius: 9999px;
    padding: 0.45rem 0.875rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #6b7280;
    transition: all 0.15s ease;
}

.market-filter-btn:hover {
    border-color: rgba(200, 134, 42, 0.35);
    background: #fff8f0;
    color: #c8862a;
}

.market-filter-btn.active {
    border-color: #c8862a;
    background: #c8862a;
    color: #ffffff;
}

.market-th {
    padding: 0.875rem 1.25rem;
    text-align: left;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #9ca3af;
}

.market-td {
    padding: 1rem 1.25rem;
}

.market-search-wrapper {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    width: min(100%, 340px);
    height: 2.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    background: #ffffff;
    padding: 0 0.875rem;
    box-shadow: 0 1px 2px rgba(17, 24, 39, 0.04);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.market-search-wrapper:focus-within {
    border-color: rgba(200, 134, 42, 0.45);
    box-shadow: 0 0 0 3px rgba(200, 134, 42, 0.12);
}

.market-search-icon {
    width: 0.95rem;
    height: 0.95rem;
    flex-shrink: 0;
    color: #9ca3af;
}

.market-search-input {
    width: 100%;
    height: 100%;
    border: 0;
    background: transparent;
    font-size: 0.875rem;
    line-height: 1.2;
    color: #111827;
    outline: 0;
}

.market-search-input::placeholder {
    color: #9ca3af;
}
</style>
