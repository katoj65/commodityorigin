<script setup>
import { Link } from '@inertiajs/vue3';
import { Box, Clock, CircleCheck, Coin, Files } from '@element-plus/icons-vue';

defineProps({
    bids: { type: Array, default: () => [] },
    emptyText: { type: String, default: "You haven't placed any bids yet." },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');

const STATUS_LABELS = { live: 'Live', draft: 'Upcoming', ready: 'Live', listing_ready: 'Live', tokenisation_ready: 'Live', ended: 'Ended', awarded: 'Awarded' };
const statusLabel = (s) => STATUS_LABELS[s] || (s || '—').replace(/_/g, ' ');
</script>

<template>
    <div v-if="bids.length" class="auc-table-wrap">
        <table class="auc-table">
            <thead>
                <tr>
                    <th><span class="auc-th"><el-icon><Files /></el-icon> Lot</span></th>
                    <th><span class="auc-th"><el-icon><Coin /></el-icon> Amount</span></th>
                    <th><span class="auc-th"><el-icon><Box /></el-icon> Quantity</span></th>
                    <th><span class="auc-th"><el-icon><CircleCheck /></el-icon> Status</span></th>
                    <th><span class="auc-th"><el-icon><Clock /></el-icon> Placed</span></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="bid in bids" :key="bid.id">
                    <td><Link :href="route('auction.show', bid.lot_id)" class="auc-table-link">{{ bid.lot_number }}</Link></td>
                    <td class="auc-mono">{{ fmtMoney(bid.amount) }}</td>
                    <td>{{ fmtNum(bid.quantity) }} kg</td>
                    <td><span class="auc-badge" :class="bid.status === 'pending' ? 'auc-badge--pending' : ''">{{ statusLabel(bid.status) }}</span></td>
                    <td>{{ bid.placed_ago || '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p v-else class="auc-empty">{{ emptyText }}</p>
</template>

<style scoped>
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
.auc-table-link { color: var(--dp-on-surface); font-weight: 600; text-decoration: none; }
.auc-table-link:hover { color: var(--dp-primary); text-decoration: underline; }

.auc-badge {
    display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    color: var(--dp-on-surface-variant); background: var(--dp-surface-container-high);
    padding: 2px 8px; border-radius: 999px; white-space: nowrap;
}
.auc-badge--pending { color: #92400E; background: #fef3c7; }

.auc-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; padding: 8px 0; }
</style>
