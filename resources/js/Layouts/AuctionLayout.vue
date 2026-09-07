<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import { Coin, Grid, Trophy, User } from '@element-plus/icons-vue';

/* ── The shell every auction-section page (overview, Live, My Bids,
   Active Buyers) renders inside — title, subtitle, and the KPI strip
   stay identical everywhere; only the `<slot />` content (each page's
   own tables) differs. The first tile is the way back to the overview
   page rather than a page of its own; the rest double as links to their
   own dedicated page, the same "nav card" pattern the Store section
   uses for its tab strip. ─────────────────────────────────────────────── */
const props = defineProps({
    title: { type: String, default: 'Auctions' },
    subtitle: { type: String, default: 'Discover and bid on verified coffee lots available through competitive price discovery.' },
    overview: { type: Object, default: () => ({}) },
});

const kpis = computed(() => [
    {
        key: 'overview',
        label: 'All Auctions',
        value: (props.overview.live_auctions ?? 0) + (props.overview.upcoming_lots ?? 0),
        icon: Grid,
        route: 'auction.index',
    },
    {
        key: 'live',
        label: 'Live Auctions',
        value: props.overview.live_auctions ?? 0,
        icon: Trophy,
        route: 'auction.live',
    },
    {
        key: 'buyers',
        label: 'Active Buyers',
        value: props.overview.active_buyers ?? 0,
        icon: User,
        route: 'auction.buyers',
    },
    {
        key: 'mybids',
        label: 'My Bids',
        value: props.overview.my_bids_count ?? 0,
        icon: Coin,
        route: 'auction.mybids',
    },
]);
</script>

<template>
    <DesignPreviewLayout :title="title">
        <Head :title="title" />

        <div class="al-page">
            <header class="al-header">
                <h1 class="al-header__title">{{ title }}</h1>
                <p class="al-header__subtitle">{{ subtitle }}</p>
            </header>

            <nav class="al-kpis">
                <Link
                    v-for="kpi in kpis"
                    :key="kpi.key"
                    :href="route(kpi.route)"
                    class="al-kpi"
                    :class="{ 'al-kpi--active': route().current(kpi.route) }"
                >
                    <span class="al-kpi__icon"><el-icon><component :is="kpi.icon" /></el-icon></span>
                    <span class="al-kpi__body">
                        <span class="al-kpi__value">{{ kpi.value }}</span>
                        <span class="al-kpi__label">{{ kpi.label }}</span>
                    </span>
                </Link>
            </nav>

            <div class="al-body">
                <slot />
            </div>
        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
.al-page { display: flex; flex-direction: column; gap: 18px; }

.al-header__title { font-size: 1.5rem; line-height: 1.2; font-weight: 800; letter-spacing: -0.015em; color: var(--dp-on-surface); margin: 0; }
.al-header__subtitle { font-size: 13.5px; line-height: 1.5; color: var(--dp-on-surface-variant); margin: 6px 0 0; max-width: 620px; }

/* ── KPI nav cards — flat, no border, tinted surface; each is also a link
   to its own page. The active page's tile gets a subtle ring and an
   accent-colored icon — no color block — so the section always shows
   where you are without shouting about it. ────────────────────────────── */
.al-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.al-kpi {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--dp-surface-container-low);
    border: 1px solid var(--dp-outline-variant);
    border-radius: var(--dp-card-radius, 6px);
    text-decoration: none;
    color: inherit;
    transition: box-shadow .15s ease, background-color .15s ease;
}
.al-kpi:hover { box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06); }
.al-kpi--active { box-shadow: inset 0 0 0 1px var(--dp-outline-variant); }
.al-kpi__icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    background: var(--dp-surface-container-high);
    color: var(--dp-on-surface-variant);
}
.al-kpi--active .al-kpi__icon { background: var(--dp-surface); color: var(--dp-primary); }
.al-kpi__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.al-kpi__value { font-size: 1.125rem; font-weight: 800; color: var(--dp-on-surface); line-height: 1.2; font-variant-numeric: tabular-nums; }
.al-kpi__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.al-body { display: flex; flex-direction: column; gap: 18px; }

@media (max-width: 1100px) {
    .al-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .al-kpis { grid-template-columns: 1fr 1fr; }
}
</style>
