<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import OuterLayout from '@/Layouts/OuterLayout.vue';

const props = defineProps({
    articles: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const pageTitle = 'Market Intelligence | Bean Origin';

const activeCategory = ref('All');
const activeArticle = ref(null);
const dialogOpen = ref(false);

const filtered = computed(() => {
    if (activeCategory.value === 'All') return props.articles;
    return props.articles.filter((a) => a.category === activeCategory.value);
});

function openArticle(item) {
    activeArticle.value = item;
    dialogOpen.value = true;
}

function formatDate(value) {
    if (!value) return '';
    return new Date(value.replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

const sentimentClass = (sentiment) => ({
    positive: 'text-[#0d631b] bg-[#0d631b]/10',
    negative: 'text-[#b91c1c] bg-[#b91c1c]/10',
    neutral: 'text-[#8b978a] bg-[#8b978a]/10',
}[sentiment] || 'text-[#8b978a] bg-[#8b978a]/10');
</script>

<template>
    <OuterLayout :title="pageTitle">
        <div class="w-full min-h-screen bg-white">
            <!-- HERO -->
            <section class="w-full bg-white pt-12 pb-8 md:pt-14 md:pb-10 px-4 md:px-8 wp-reveal">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="flex items-center justify-center gap-2 text-[#0d631b] mb-3">
                        <span class="material-symbols-outlined text-[16px]">newspaper</span>
                        <span class="text-xs font-semibold tracking-wide uppercase">Market Intelligence</span>
                    </div>
                    <h1 class="text-[26px] md:text-[32px] font-semibold text-[#181d17] leading-[1.15] tracking-tight">
                        News and updates from the exchange.
                    </h1>
                    <p class="text-sm md:text-base leading-relaxed text-[#40493d] mt-3 max-w-2xl mx-auto">
                        What's new, what's changed, and what's worth knowing before your next trade.
                    </p>
                </div>
            </section>

            <!-- FEED -->
            <section class="w-full bg-white py-16 md:py-20 px-4 md:px-8 wp-reveal">
                <div class="max-w-6xl mx-auto">
                    <div class="flex items-center gap-2 flex-wrap mb-10">
                        <button
                            type="button"
                            class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-colors"
                            :class="activeCategory === 'All' ? 'bg-[#0d631b] text-white border-[#0d631b]' : 'border-[#bfcaba]/40 text-[#40493d] hover:border-[#0d631b]/40'"
                            @click="activeCategory = 'All'"
                        >All</button>
                        <button
                            v-for="cat in categories" :key="cat"
                            type="button"
                            class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-colors"
                            :class="activeCategory === cat ? 'bg-[#0d631b] text-white border-[#0d631b]' : 'border-[#bfcaba]/40 text-[#40493d] hover:border-[#0d631b]/40'"
                            @click="activeCategory = cat"
                        >{{ cat }}</button>
                    </div>

                    <div v-if="filtered.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <button
                            v-for="item in filtered" :key="item.id"
                            type="button"
                            class="text-left bg-white border border-[#bfcaba]/25 rounded-xl p-6 flex flex-col shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all"
                            @click="openArticle(item)"
                        >
                            <div class="flex items-center gap-2 mb-3 flex-wrap">
                                <span v-if="item.category" class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full bg-[#0d631b]/10 text-[#0d631b]">{{ item.category }}</span>
                                <span v-if="item.sentiment" class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full" :class="sentimentClass(item.sentiment)">{{ item.sentiment }}</span>
                            </div>
                            <h2 class="text-base font-semibold text-[#181d17] leading-snug mb-2">{{ item.title }}</h2>
                            <p class="text-sm leading-relaxed text-[#40493d] line-clamp-3 flex-1">{{ item.excerpt }}</p>
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-[#bfcaba]/20">
                                <span class="text-xs text-[#8b978a]">{{ formatDate(item.published_at) }}</span>
                                <span class="text-[#0d631b] text-xs font-semibold flex items-center gap-1">
                                    Read more <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </span>
                            </div>
                        </button>
                    </div>
                    <p v-else class="text-sm text-[#40493d] text-center py-10">No articles in this category yet.</p>
                </div>
            </section>

            <!-- CTA -->
            <section class="w-full bg-white py-20 md:py-24 px-4 md:px-8 border-t border-[#bfcaba]/20 wp-reveal">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-[26px] md:text-[32px] font-semibold text-[#181d17] leading-tight tracking-tight">
                        Stay ahead of the <span class="text-[#0d631b]">market.</span>
                    </h2>
                    <p class="text-base text-[#40493d] mt-4 max-w-xl mx-auto">
                        Create an account for live pricing, demand signals, and full origin traceability on every trade.
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

        <el-dialog v-model="dialogOpen" width="min(640px, calc(100vw - 2rem))" align-center>
            <template v-if="activeArticle">
                <div class="flex items-center gap-2 mb-3 flex-wrap">
                    <span v-if="activeArticle.category" class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full bg-[#0d631b]/10 text-[#0d631b]">{{ activeArticle.category }}</span>
                    <span v-if="activeArticle.sentiment" class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full" :class="sentimentClass(activeArticle.sentiment)">{{ activeArticle.sentiment }}</span>
                    <span class="text-xs text-[#8b978a]">{{ formatDate(activeArticle.published_at) }}</span>
                    <span v-if="activeArticle.source" class="text-xs text-[#8b978a]">· {{ activeArticle.source }}</span>
                </div>
                <h2 class="text-xl font-semibold text-[#181d17] leading-snug mb-3">{{ activeArticle.title }}</h2>
                <p class="text-sm leading-relaxed text-[#40493d] whitespace-pre-line">{{ activeArticle.body || activeArticle.excerpt }}</p>
            </template>
        </el-dialog>
    </OuterLayout>
</template>
