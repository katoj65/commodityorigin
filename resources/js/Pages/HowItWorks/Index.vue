<script setup>
import { Link } from '@inertiajs/vue3';
import OuterLayout from '@/Layouts/OuterLayout.vue';

defineProps({
    steps: { type: Array, default: () => [] },
});

const pageTitle = 'How It Works | Bean Origin';
</script>

<template>
    <OuterLayout :title="pageTitle">
        <div class="w-full min-h-screen bg-white">
            <!-- HERO -->
            <section class="w-full bg-white pt-12 pb-8 md:pt-14 md:pb-10 px-4 md:px-8 wp-reveal">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="flex items-center justify-center gap-2 text-[#0d631b] mb-3">
                        <span class="material-symbols-outlined text-[16px]">auto_awesome</span>
                        <span class="text-xs font-semibold tracking-wide uppercase">How it works</span>
                    </div>
                    <h1 class="text-[26px] md:text-[32px] font-semibold text-[#181d17] leading-[1.15] tracking-tight">
                        From farm to cup, fully traceable.
                    </h1>
                    <p class="text-sm md:text-base leading-relaxed text-[#40493d] mt-3 max-w-2xl mx-auto">
                        Nothing about this trade should feel like a black box — here's what actually happens at each step.
                    </p>
                </div>
            </section>

            <!-- STEPS -->
            <section class="w-full bg-white py-16 md:py-20 px-4 md:px-8 border-t border-[#bfcaba]/20 wp-reveal">
                <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="(step, i) in steps" :key="step.id"
                        class="bg-white border border-[#bfcaba]/25 rounded-xl p-6 flex flex-col shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <img v-if="step.image" :src="step.image" :alt="step.title" class="w-10 h-10 rounded-lg object-cover flex-shrink-0" />
                            <div v-else class="w-10 h-10 rounded-lg bg-[#0d631b]/10 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[#0d631b] text-[20px]">{{ step.icon || 'check_circle' }}</span>
                            </div>
                            <span class="text-[11px] font-bold text-[#8b978a] tabular-nums">{{ String(i + 1).padStart(2, '0') }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-[#181d17] leading-snug mb-2">{{ step.title }}</h3>
                        <p class="text-sm leading-relaxed text-[#40493d]">{{ step.description }}</p>
                    </div>

                    <p v-if="!steps.length" class="text-sm text-[#40493d] col-span-full text-center py-10">
                        Steps will appear here once they're published.
                    </p>
                </div>
            </section>

            <!-- CTA -->
            <section class="w-full bg-white py-20 md:py-24 px-4 md:px-8 border-t border-[#bfcaba]/20 wp-reveal">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-[26px] md:text-[32px] font-semibold text-[#181d17] leading-tight tracking-tight">
                        Ready to trade on <span class="text-[#0d631b]">Bean Origin?</span>
                    </h2>
                    <p class="text-base text-[#40493d] mt-4 max-w-xl mx-auto">
                        Create an account and get matched with verified lots, live pricing, and full origin traceability.
                    </p>
                    <div class="flex items-center justify-center gap-4 mt-8 flex-wrap">
                        <Link :href="route('register')" class="inline-flex items-center gap-2 bg-[#0d631b] text-white text-sm font-semibold px-6 py-3 rounded-lg no-underline hover:opacity-90 transition-opacity">
                            Create Account <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </Link>
                        <Link :href="route('market.live')" class="inline-flex items-center gap-2 border border-[#bfcaba]/40 text-[#181d17] text-sm font-semibold px-6 py-3 rounded-lg no-underline hover:bg-[#f7fbf0] transition-colors">
                            Browse Marketplace
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </OuterLayout>
</template>
