<script setup>
import AuctionLayout from '@/Layouts/AuctionLayout.vue';
import AuctionLotTable from '@/Components/Auction/AuctionLotTable.vue';
import { Clock, Trophy } from '@element-plus/icons-vue';

defineProps({
    overview: { type: Object, default: () => ({}) },
    featuredLots: { type: Array, default: () => [] },
    endingSoon: { type: Array, default: () => [] },
});
</script>

<template>
    <AuctionLayout title="Live Auctions" subtitle="Every lot open for bidding right now." :overview="overview">
        <section class="auc-section">
            <div class="auc-section__head">
                <h2 class="auc-section__title"><el-icon><Trophy /></el-icon> Live Auctions</h2>
                <span class="auc-section__count">{{ featuredLots.length }}</span>
            </div>
            <AuctionLotTable :lots="featuredLots" mode="live" empty-text="No live auctions right now." />
        </section>

        <section class="auc-section">
            <div class="auc-section__head">
                <h2 class="auc-section__title"><el-icon><Clock /></el-icon> Ending Soon</h2>
                <span class="auc-section__count">{{ endingSoon.length }}</span>
            </div>
            <AuctionLotTable :lots="endingSoon" mode="soon" empty-text="No lots are ending soon." />
        </section>
    </AuctionLayout>
</template>

<style scoped>
.auc-section {
    background: var(--dp-surface);
    border: 1px solid var(--dp-outline-variant);
    border-radius: var(--dp-card-radius, 6px);
    padding: 18px;
}
.auc-section__head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.auc-section__title {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    color: var(--dp-outline); margin: 0;
}
.auc-section__count {
    font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant);
    background: var(--dp-surface-container-high); padding: 1px 8px; border-radius: 999px;
}
</style>
