<script setup>
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const cartItems = ref([
    {
        id: 'LOT-UG-001',
        name: 'Mount Elgon Sipi Falls',
        score: 'SCAA 88.5',
        region: 'UGANDA',
        description: 'Notes of dark chocolate, brown sugar, and lemon zest. Harvested at 1,800m elevation. Grade A Micro-lot.',
        units: 2,
        unitPrice: 620,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDv_vhmIorxO5TXHfY4x3mvxWbuX9RQy83ltILS7_Znxq5fvxI-Af9qAPgN5wgaIFsW3SpILtw5fP4_4RHBnrAN1hoGJ6K_oJPDsrhpRlJPQrHsyJqVXi7bRF1VJONSOLAiG2hff_cRXPZx30imH9z7qDIOPwif1o6pzkCvQVLbJMvIcJO6YoRd1gm0kWJd-m8xEmRAUHSdv-XePrjlP-jNVr3X7AggNZkF5_rFaGloIp9BhynvWLg4uk1mj71GfuBx5Hmi1FzxFK-_',
    },
    {
        id: 'LOT-ET-002',
        name: 'Yirgacheffe G1 Chelchele',
        score: 'SCAA 91.0',
        region: 'ETHIOPIA',
        description: 'Exceptional clarity with floral jasmine aroma and a vibrant berry acidity. High-altitude heirloom varieties.',
        units: 1,
        unitPrice: 890,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVNPRKcnvtgsayf1-HlE1xA92LWW1C56Io3VMreh4aujnZTgd7RVNEZOyEqFGcffC6O3JdFFEczJbLDdWYhY3SPZ_97Ep-mSdEA6EpSHOYxQ4YC-9rWllkkDGEgrkRhX8fdY9yD34FR8UBs42K4RgVHEi6OXDt4QvP-hJgG1uWAZlyFMQ7HCYg9NcS7oQW5HysDvCK3FiXBDRpfkupmdW5tIy7o5GV8ZL8feaXnYtU6ZpDEAJvS_XKRffdezzJJCSUQeF2AHlDDapn',
    },
]);

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(value);

const lineTotal = (item) => item.units * item.unitPrice;
const itemCount = computed(() => cartItems.value.length);
const subtotal = computed(() => cartItems.value.reduce((sum, item) => sum + lineTotal(item), 0));
const shipping = computed(() => (cartItems.value.length ? 45 : 0));
const tax = computed(() => subtotal.value * 0.06);
const total = computed(() => subtotal.value + shipping.value + tax.value);

const increaseUnits = (id) => {
    const item = cartItems.value.find((entry) => entry.id === id);

    if (item) {
        item.units += 1;
    }
};

const decreaseUnits = (id) => {
    const item = cartItems.value.find((entry) => entry.id === id);

    if (item && item.units > 1) {
        item.units -= 1;
    }
};

const removeItem = (id) => {
    cartItems.value = cartItems.value.filter((item) => item.id !== id);
};
</script>

<template>
    <AppLayout title="Shopping Cart">
        <div class="cart-page bg-[linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] py-1 font-['Manrope',sans-serif] text-[#1f2937]">
            <section class="mb-3 rounded-lg border border-[#E5E7EB] bg-white px-4 py-3 shadow-[0_10px_28px_-26px_rgba(15,23,42,0.22)] sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="font-display text-[20px] font-bold leading-none tracking-tight text-[#111827]">
                            Shopping Cart
                        </h1>
                        <p class="mt-2 max-w-2xl text-[13px] leading-relaxed text-[#6B7280]">
                            Review selected coffee lots, adjust unit quantities, and finalize the transaction from the settlement summary.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <div class="rounded-md border border-[#E5E7EB] bg-white px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6B7280]">
                            {{ itemCount }} Lots
                        </div>
                        <div class="rounded-md border border-[#D7EEE4] bg-[#ECF8F1] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#2F6F58]">
                            Trader Ready
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-5 xl:grid-cols-[minmax(0,1fr)_360px] xl:gap-6">
                <section class="rounded-[18px] border border-[#e8edf3] bg-white px-4 py-2.5 shadow-[0_16px_36px_-30px_rgba(15,23,42,0.24)] sm:px-6 sm:py-4">
                    <div
                        v-for="(item, index) in cartItems"
                        :key="item.id"
                        class="grid gap-4 py-4 transition-all duration-200 hover:translate-y-[-1px] lg:grid-cols-[150px_minmax(0,1fr)_118px] lg:gap-5"
                        :class="index !== cartItems.length - 1 ? 'border-b border-[#f3f4f6]' : ''"
                    >
                        <div class="relative h-[154px] overflow-hidden rounded-[10px] border border-[#edf2f7] bg-[#f8fafc] shadow-[0_12px_24px_-20px_rgba(15,23,42,0.24)]">
                            <img
                                :src="item.image"
                                :alt="item.name"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-[1.03]"
                            />
                            <div class="absolute left-3 top-3 rounded-md bg-white/92 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-[#667085] shadow-sm">
                                {{ item.id }}
                            </div>
                        </div>

                        <div class="min-w-0 lg:col-span-2 lg:grid lg:grid-cols-[minmax(0,1fr)_118px] lg:gap-5">
                            <div>
                                <div class="flex flex-col gap-3">
                                    <div class="min-w-0">
                                        <h2 class="text-[18px] font-semibold tracking-[-0.03em] text-[#1f2937]">
                                            {{ item.name }}
                                        </h2>

                                        <div class="mt-2.5 flex flex-wrap gap-2">
                                            <span class="rounded-md bg-[#e8f7ef] px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-[#2f6f58]">
                                                {{ item.score }}
                                            </span>
                                            <span class="rounded-md bg-[#f3f4f6] px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-[#8c919b]">
                                                {{ item.region }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <p class="mt-4 max-w-2xl text-[13px] leading-[1.75] text-[#6b7280]">
                                    {{ item.description }}
                                </p>

                                <div class="mt-3.5 flex flex-wrap gap-2">
                                    <span class="rounded-md bg-[#F8FAFC] px-2.5 py-1 text-[10px] font-medium text-[#64748B]">Institutional Lot</span>
                                    <span class="rounded-md bg-[#F8FAFC] px-2.5 py-1 text-[10px] font-medium text-[#64748B]">Export Verified</span>
                                </div>

                                <div class="mt-5 inline-flex items-center rounded-[10px] border border-[#e5e7eb] bg-white px-2 py-1 shadow-[0_8px_18px_-16px_rgba(15,23,42,0.2)]">
                                    <button
                                        type="button"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-[#6b7280] transition hover:bg-[#f8fafc] hover:text-[#1f2937]"
                                        @click="decreaseUnits(item.id)"
                                    >
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14" />
                                        </svg>
                                    </button>
                                    <span class="min-w-9 px-2.5 text-center text-[19px] font-semibold tracking-[-0.04em] text-[#1f2937]">
                                        {{ String(item.units).padStart(2, '0') }}
                                    </span>
                                    <button
                                        type="button"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-[#6b7280] transition hover:bg-[#f8fafc] hover:text-[#1f2937]"
                                        @click="increaseUnits(item.id)"
                                    >
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14" />
                                            <path d="M5 12h14" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex flex-col justify-between gap-4 lg:items-end">
                                <div class="rounded-[12px] border border-[#edf2f7] bg-[#F8FAFC] px-3 py-2.5 text-left lg:min-w-[112px]">
                                    <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#98A2B3]">Line Total</div>
                                    <div class="mt-1 text-[18px] font-medium tracking-[-0.02em] text-[#1f2937]">
                                    {{ formatCurrency(lineTotal(item)) }}
                                    </div>
                                </div>
                                <el-button
                                    class="cart-remove-button lg:mb-1"
                                    @click="removeItem(item.id)"
                                >
                                    <el-icon><Delete /></el-icon>
                                    <span>Remove</span>
                                </el-button>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="xl:pt-0">
                    <div class="sticky top-24 rounded-[18px] border border-[#e8edf3] bg-[#fcfcfb] px-4 py-4 shadow-[0_18px_42px_-32px_rgba(15,23,42,0.24)]">
                        <p class="text-[15px] font-bold uppercase tracking-[0.24em] text-[#a0a7b3]">Summary</p>

                        <div class="mt-4 rounded-[14px] border border-[#e3efe8] bg-[linear-gradient(135deg,#F8FBF9_0%,#EEF7F2_100%)] px-3.5 py-3">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#7A8A85]">Order Overview</div>
                            <div class="mt-2 flex items-end justify-between gap-3">
                                <span class="text-[14px] text-[#6B7280]">{{ itemCount }} selected lots</span>
                                <span class="text-[18px] font-semibold text-[#2f6f58]">{{ formatCurrency(subtotal) }}</span>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div class="flex items-center justify-between text-[15px] text-[#6b7280]">
                                <span>Subtotal</span>
                                <span class="font-semibold text-[#1f2937]">{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[15px] text-[#6b7280]">
                                <span>Shipping</span>
                                <span class="font-semibold text-[#1f2937]">{{ formatCurrency(shipping) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[15px] text-[#6b7280]">
                                <span>Tax</span>
                                <span class="font-semibold text-[#1f2937]">{{ formatCurrency(tax) }}</span>
                            </div>
                        </div>

                        <div class="my-5 border-t border-[#edf2f7]"></div>

                        <div class="flex items-end justify-between gap-4">
                            <span class="text-[16px] font-bold uppercase tracking-[0.14em] text-[#1f2937]">Total</span>
                            <span class="text-[30px] font-extrabold leading-none tracking-[-0.05em] text-[#2f6f58]">
                                {{ formatCurrency(total) }}
                            </span>
                        </div>

                        <div class="pt-6">
                            <el-button class="cart-finalize-button !inline-flex !w-full !items-center !justify-center !rounded-lg !border-[#2f6f58] !bg-[#2f6f58] !px-6 !py-3 !text-center !text-[13px] !font-bold !uppercase !tracking-[0.16em] !text-white hover:!border-[#285d4b] hover:!bg-[#285d4b]">
                                Finalize Transaction
                            </el-button>
                        </div>

                        <div class="mt-4 flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.18em] text-[#a0a7b3]">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="11" width="14" height="9" rx="2" />
                                <path d="M8 11V8a4 4 0 118 0v3" />
                            </svg>
                            <span>Secure Institutional Protocol</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.cart-page :where(h1, h2, p) {
    margin: 0;
}

.cart-page :where(button) {
    font: inherit;
}

.cart-finalize-button :deep(.el-button__text) {
    width: 100%;
    text-align: center;
}

.cart-remove-button {
    --el-button-bg-color: #ffffff;
    --el-button-border-color: #eef2f7;
    --el-button-text-color: #6b7280;
    --el-button-hover-bg-color: #f8fafc;
    --el-button-hover-border-color: #e2e8f0;
    --el-button-hover-text-color: #475569;
    --el-button-active-bg-color: #f8fafc;
    --el-button-active-border-color: #e2e8f0;
    --el-button-active-text-color: #475569;
    height: 34px;
    padding: 0 12px;
    border-radius: 0.5rem;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.01em;
}

.cart-remove-button :deep(.el-icon) {
    margin-right: 0.15rem;
    font-size: 0.9rem;
}
</style>
