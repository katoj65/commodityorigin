<script setup>
import AuctionLayout from '@/Layouts/AuctionLayout.vue';
import { Coin, Files, Trophy, User } from '@element-plus/icons-vue';

const props = defineProps({
    overview: { type: Object, default: () => ({}) },
    buyers: { type: Array, default: () => [] },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
</script>

<template>
    <AuctionLayout title="Active Buyers" subtitle="Every buyer who has placed a bid, ranked by total bid value." :overview="overview">
        <section class="auc-section">
            <div class="auc-section__head">
                <h2 class="auc-section__title"><el-icon><User /></el-icon> Active Buyers</h2>
                <span class="auc-section__count">{{ buyers.length }}</span>
            </div>

            <div v-if="buyers.length" class="auc-table-wrap">
                <table class="auc-table">
                    <thead>
                        <tr>
                            <th><span class="auc-th"><el-icon><User /></el-icon> Buyer</span></th>
                            <th><span class="auc-th"><el-icon><Trophy /></el-icon> Bids Placed</span></th>
                            <th><span class="auc-th"><el-icon><Files /></el-icon> Lots Bid On</span></th>
                            <th><span class="auc-th"><el-icon><Coin /></el-icon> Total Bid Value</span></th>
                            <th><span class="auc-th"><el-icon><Coin /></el-icon> Highest Bid</span></th>
                            <th>Last Bid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="buyer in buyers" :key="buyer.id">
                            <td>
                                <div class="auc-buyer-cell">
                                    <strong class="auc-buyer-cell__name">{{ buyer.name }}</strong>
                                    <span v-if="buyer.role" class="auc-buyer-cell__role">{{ buyer.role }}</span>
                                </div>
                            </td>
                            <td>{{ buyer.bids_placed }}</td>
                            <td>{{ buyer.lots_bid_on }}</td>
                            <td class="auc-mono auc-mono--strong">{{ fmtMoney(buyer.total_bid_value) }}</td>
                            <td class="auc-mono">{{ fmtMoney(buyer.highest_bid) }}</td>
                            <td>{{ buyer.last_bid_ago || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="auc-empty">No buyers have placed a bid yet.</p>
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

.auc-table-wrap { overflow-x: auto; }
.auc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.auc-table th {
    text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    color: var(--dp-on-surface-variant); padding: 8px 12px; border-bottom: 1px solid var(--dp-outline-variant);
    white-space: nowrap;
}
.auc-th { display: inline-flex; align-items: center; gap: 5px; }
.auc-th .el-icon { font-size: 13px; }
.auc-table td { padding: 11px 12px; border-bottom: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface); }
.auc-table tr:last-child td { border-bottom: none; }
.auc-mono { font-variant-numeric: tabular-nums; }
.auc-mono--strong { font-weight: 800; color: var(--dp-on-surface); }

.auc-buyer-cell { display: flex; flex-direction: column; gap: 2px; }
.auc-buyer-cell__name { font-size: 13.5px; font-weight: 700; color: var(--dp-on-surface); }
.auc-buyer-cell__role { font-size: 11px; color: var(--dp-on-surface-variant); text-transform: capitalize; }

.auc-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; padding: 8px 0; }
</style>
