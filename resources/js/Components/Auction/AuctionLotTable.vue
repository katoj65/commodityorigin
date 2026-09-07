<script setup>
import { Link } from '@inertiajs/vue3';
import { Box, CircleCheck, Coin, Files, Goods, Location, Medal, Trophy } from '@element-plus/icons-vue';

/* ── The lot table shared by every auction page that lists lots (Live
   Auctions + Ending Soon on the overview/Live pages, My Auctions on the
   overview page) — only the status badge and empty-state copy differ,
   both controlled by `mode`. ──────────────────────────────────────────── */
const props = defineProps({
    lots: { type: Array, default: () => [] },
    mode: { type: String, default: 'live' }, // 'live' | 'soon' | 'mine'
    emptyText: { type: String, default: 'No lots to show.' },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');

const STATUS_LABELS = { live: 'Live', draft: 'Upcoming', ready: 'Live', listing_ready: 'Live', tokenisation_ready: 'Live', ended: 'Ended', awarded: 'Awarded' };
const statusLabel = (s) => STATUS_LABELS[s] || (s || '—').replace(/_/g, ' ');

function badge(lot) {
    if (props.mode === 'soon') return { label: 'Ending Soon', cls: 'auc-badge--soon' };
    if (props.mode === 'mine') return { label: statusLabel(lot.status), cls: lot.status === 'draft' ? '' : 'auc-badge--live' };
    return { label: statusLabel(lot.status), cls: 'auc-badge--live' };
}
</script>

<template>
    <div v-if="lots.length" class="auc-table-wrap">
        <table class="auc-table">
            <thead>
                <tr>
                    <th><span class="auc-th"><el-icon><Files /></el-icon> Lot</span></th>
                    <th><span class="auc-th"><el-icon><Location /></el-icon> Origin</span></th>
                    <th><span class="auc-th"><el-icon><Goods /></el-icon> Variety</span></th>
                    <th><span class="auc-th"><el-icon><Medal /></el-icon> Grade</span></th>
                    <th><span class="auc-th"><el-icon><Box /></el-icon> Quantity</span></th>
                    <th><span class="auc-th"><el-icon><Coin /></el-icon> Starting Bid</span></th>
                    <th><span class="auc-th"><el-icon><Coin /></el-icon> Current Bid</span></th>
                    <th><span class="auc-th"><el-icon><Trophy /></el-icon> Bids</span></th>
                    <th><span class="auc-th"><el-icon><CircleCheck /></el-icon> Status</span></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="lot in lots" :key="lot.id">
                    <td>
                        <div class="auc-lot-cell">
                            <strong class="auc-lot-cell__name">{{ lot.lot_name || lot.lot_number }}</strong>
                            <span class="auc-lot-cell__num">{{ lot.lot_number || '—' }}</span>
                        </div>
                    </td>
                    <td>{{ lot.origin_country || '—' }}</td>
                    <td>{{ lot.variety || '—' }}</td>
                    <td>{{ lot.grade || '—' }}</td>
                    <td class="auc-mono">{{ fmtNum(lot.net_weight_kg) }} kg</td>
                    <td class="auc-mono">{{ fmtMoney(lot.starting_price) }}</td>
                    <td class="auc-mono auc-mono--strong">{{ fmtMoney(lot.current_bid ?? lot.starting_price) }}</td>
                    <td>{{ lot.bid_count ?? 0 }}</td>
                    <td><span class="auc-badge" :class="badge(lot).cls">{{ badge(lot).label }}</span></td>
                    <td><Link :href="route('auction.show', lot.id)" class="auc-view-link">View <el-icon><Goods /></el-icon></Link></td>
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
.auc-mono--strong { font-weight: 800; color: var(--dp-on-surface); }

.auc-lot-cell { display: flex; flex-direction: column; gap: 2px; }
.auc-lot-cell__name { font-size: 13.5px; font-weight: 700; color: var(--dp-on-surface); }
.auc-lot-cell__num { font-size: 11.5px; color: var(--dp-on-surface-variant); }

.auc-badge {
    display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    color: var(--dp-on-surface-variant); background: var(--dp-surface-container-high);
    padding: 2px 8px; border-radius: 999px; white-space: nowrap;
}
.auc-badge--live { color: #16A34A; background: #E9F9EE; }
.auc-badge--soon { color: #92400E; background: #fef3c7; }

.auc-view-link {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    font-size: 12.5px; font-weight: 700; color: var(--dp-primary); text-decoration: none;
}
.auc-view-link:hover { text-decoration: underline; }

.auc-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; padding: 8px 0; }
</style>
