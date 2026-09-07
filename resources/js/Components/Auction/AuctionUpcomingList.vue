<script setup>
import { Link } from '@inertiajs/vue3';
import { Goods } from '@element-plus/icons-vue';

defineProps({
    lots: { type: Array, default: () => [] },
    emptyText: { type: String, default: 'No upcoming auctions.' },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
</script>

<template>
    <div v-if="lots.length" class="auc-list">
        <div v-for="lot in lots" :key="lot.id" class="auc-list__row">
            <div class="auc-list__main">
                <span class="auc-list__name">{{ lot.lot_name || lot.lot_number }}</span>
                <span class="auc-list__meta">{{ lot.origin_country }} · {{ lot.variety || '—' }} · {{ lot.grade || '—' }}</span>
            </div>
            <strong class="auc-list__value">{{ fmtMoney(lot.starting_price) }}</strong>
            <Link :href="route('auction.show', lot.id)" class="auc-view-link">
                View Auction <el-icon><Goods /></el-icon>
            </Link>
        </div>
    </div>
    <p v-else class="auc-empty">{{ emptyText }}</p>
</template>

<style scoped>
.auc-list { display: flex; flex-direction: column; }
.auc-list__row { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-top: 1px solid var(--dp-outline-variant); }
.auc-list__row:first-child { border-top: none; }
.auc-list__main { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
.auc-list__name { font-size: 14px; font-weight: 600; color: var(--dp-on-surface); }
.auc-list__meta { font-size: 12px; color: var(--dp-on-surface-variant); }
.auc-list__value { font-size: 14px; font-weight: 700; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }
.auc-view-link {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    font-size: 12.5px; font-weight: 700; color: var(--dp-primary); text-decoration: none;
}
.auc-view-link:hover { text-decoration: underline; }

.auc-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; padding: 8px 0; }
</style>
