<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const props = defineProps({
    analysis: { type: Object, default: () => ({}) },
    demand: { type: Object, default: () => ({}) },
});

const pageTitle = 'Exchange | Bean Origin';

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');

const tierLabels = { high: 'High Demand', medium: 'Medium Demand', low: 'Low Demand' };

const sentimentClass = computed(() => {
    const score = props.demand.sentiment_score;
    if (score == null) return '';
    if (score >= 70) return 'text-[#0d631b]';
    if (score >= 40) return 'text-[#b45309]';
    return 'text-[#b91c1c]';
});
</script>

<template>
    <OuterLayout :title="pageTitle">
        <div class="w-full min-h-screen bg-white">
            <!-- HERO -->
            <section class="w-full bg-white pt-12 pb-8 md:pt-14 md:pb-10 px-4 md:px-8 wp-reveal">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="flex items-center justify-center gap-2 text-[#0d631b] mb-3">
                        <span class="material-symbols-outlined text-[16px]">trending_up</span>
                        <span class="text-xs font-semibold tracking-wide uppercase">Exchange</span>
                    </div>
                    <h1 class="text-[26px] md:text-[32px] font-semibold text-[#181d17] leading-[1.15] tracking-tight">
                        A live look at what's trading right now.
                    </h1>
                    <p class="text-sm md:text-base leading-relaxed text-[#40493d] mt-3 max-w-2xl mx-auto">
                        Real listings, real pricing, real demand — no account needed to take a look.
                    </p>
                </div>
            </section>

            <!-- HEADLINE STATS -->
            <section class="w-full bg-white py-10 px-4 md:px-8 border-t border-[#bfcaba]/20 wp-reveal">
                <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white border border-[#bfcaba]/25 rounded-xl p-5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#8b978a] mb-1">Live Listings</div>
                        <div class="text-2xl font-bold text-[#181d17] tabular-nums">{{ fmtNum(analysis.total_listings) }}</div>
                    </div>
                    <div class="bg-white border border-[#bfcaba]/25 rounded-xl p-5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#8b978a] mb-1">Total Volume</div>
                        <div class="text-2xl font-bold text-[#181d17] tabular-nums">{{ fmtNum(analysis.total_volume_kg) }}<span class="text-sm font-semibold text-[#8b978a]"> kg</span></div>
                    </div>
                    <div class="bg-white border border-[#bfcaba]/25 rounded-xl p-5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#8b978a] mb-1">Average Price</div>
                        <div class="text-2xl font-bold text-[#181d17] tabular-nums">{{ fmtMoney(analysis.average_price_per_kg) }}<span class="text-sm font-semibold text-[#8b978a]"> /kg</span></div>
                    </div>
                    <div class="bg-white border border-[#bfcaba]/25 rounded-xl p-5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#8b978a] mb-1">Market Sentiment</div>
                        <div class="text-2xl font-bold tabular-nums" :class="sentimentClass">
                            {{ demand.sentiment_score ?? '—' }}<span v-if="demand.sentiment_score != null" class="text-sm font-semibold text-[#8b978a]"> /100</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- INSIGHTS + DEMAND -->
            <section class="w-full bg-white pb-16 md:pb-20 px-4 md:px-8 wp-reveal">
                <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border border-[#bfcaba]/25 rounded-xl p-6">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#8b978a] mb-4">What's happening</h2>
                        <ul v-if="analysis.insights?.length" class="flex flex-col gap-3">
                            <li v-for="(insight, i) in analysis.insights" :key="i" class="text-sm leading-relaxed text-[#40493d]">{{ insight }}</li>
                        </ul>
                        <p v-else class="text-sm text-[#40493d]">No activity yet — check back once listings go live.</p>
                    </div>

                    <div class="bg-white border border-[#bfcaba]/25 rounded-xl p-6">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#8b978a] mb-4">Demand breakdown</h2>
                        <div v-if="demand.tiers?.length" class="flex flex-col gap-4">
                            <div v-for="tier in demand.tiers" :key="tier.tier">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm font-semibold text-[#181d17]">{{ tierLabels[tier.tier] || tier.tier }}</span>
                                    <span class="text-xs text-[#8b978a] tabular-nums">{{ tier.count }} · {{ tier.percentage }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-[#f1f5eb] overflow-hidden">
                                    <div
                                        class="h-full rounded-full"
                                        :class="{
                                            'bg-[#0d631b]': tier.tier === 'high',
                                            'bg-[#d97706]': tier.tier === 'medium',
                                            'bg-[#dc2626]': tier.tier === 'low',
                                        }"
                                        :style="{ width: tier.percentage + '%' }"
                                    />
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-[#40493d]">No demand data yet.</p>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="w-full bg-white py-20 md:py-24 px-4 md:px-8 border-t border-[#bfcaba]/20 wp-reveal">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-[26px] md:text-[32px] font-semibold text-[#181d17] leading-tight tracking-tight">
                        Ready to place a <span class="text-[#0d631b]">trade?</span>
                    </h2>
                    <p class="text-base text-[#40493d] mt-4 max-w-xl mx-auto">
                        Create an account to see full listings, live pricing, and trade directly on the exchange.
                    </p>
                    <div class="flex items-center justify-center gap-4 mt-8 flex-wrap">
                        <Link :href="route('register')" class="inline-flex items-center gap-2 bg-[#0d631b] text-white text-sm font-semibold px-6 py-3 rounded-lg no-underline hover:opacity-90 transition-opacity">
                            Create Account <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </Link>
                        <Link :href="route('how-it-works.index')" class="inline-flex items-center gap-2 border border-[#bfcaba]/40 text-[#181d17] text-sm font-semibold px-6 py-3 rounded-lg no-underline hover:bg-[#f7fbf0] transition-colors">
                            How It Works
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </OuterLayout>
</template>
