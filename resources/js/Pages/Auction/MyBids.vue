<script setup>
import AuctionLayout from '@/Layouts/AuctionLayout.vue';
import AuctionBidsTable from '@/Components/Auction/AuctionBidsTable.vue';
import { Trophy } from '@element-plus/icons-vue';

defineProps({
    overview: { type: Object, default: () => ({}) },
    myBids: { type: Array, default: () => [] },
});
</script>

<template>
    <AuctionLayout title="My Bids" subtitle="Every bid you've placed across the exchange." :overview="overview">
        <section class="auc-section">
            <div class="auc-section__head">
                <h2 class="auc-section__title"><el-icon><Trophy /></el-icon> My Bids</h2>
                <span class="auc-section__count">{{ myBids.length }}</span>
            </div>
            <AuctionBidsTable :bids="myBids" />
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
