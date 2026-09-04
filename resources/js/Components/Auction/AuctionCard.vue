<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Box, Coin, Goods, Location, Medal } from '@element-plus/icons-vue';

const props = defineProps({
    lot: { type: Object, required: true },
    soon: { type: Boolean, default: false },
});

const fmtMoney = (n) => (n != null ? Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—');
const fmtNum = (n) => (n != null ? Number(n).toLocaleString() : '—');

const status = computed(() => {
    const s = props.lot.status;
    if (props.soon) return { label: 'Ending Soon', tone: 'soon' };
    if (s === 'draft') return { label: 'Upcoming', tone: 'neutral' };
    if (['ready', 'listing_ready', 'tokenisation_ready'].includes(s)) return { label: 'Live', tone: 'live' };
    return { label: (s || '—').replace(/_/g, ' '), tone: 'neutral' };
});

const currentPrice = computed(() => props.lot.current_bid ?? props.lot.starting_price ?? 0);
const meta = computed(() => [props.lot.variety, props.lot.grade, props.lot.process].filter(Boolean));
</script>

<template>
    <Link :href="route('auction.show', lot.id)" class="ac">
        <div class="ac__media">
            <img v-if="lot.image" :src="lot.image" :alt="lot.lot_name || lot.lot_number" class="ac__img" />
            <div v-else class="ac__img ac__img--empty"><el-icon><Goods /></el-icon></div>
            <span class="ac__badge" :class="`ac__badge--${status.tone}`">{{ status.label }}</span>
        </div>

        <div class="ac__body">
            <h3 class="ac__name">{{ lot.lot_name || lot.lot_number }}</h3>
            <p class="ac__lot"><el-icon><Box /></el-icon> {{ lot.lot_number || '—' }}</p>

            <p class="ac__origin">
                <el-icon><Location /></el-icon> {{ lot.origin_country || '—' }}<template v-if="lot.region"> · {{ lot.region }}</template>
            </p>

            <div v-if="meta.length" class="ac__chips">
                <span v-for="m in meta" :key="m" class="ac__chip">{{ m }}</span>
                <span v-if="lot.quality_score" class="ac__chip ac__chip--score"><el-icon><Medal /></el-icon> {{ lot.quality_score }}</span>
            </div>

            <div class="ac__stats">
                <div class="ac__stat">
                    <span class="ac__stat-label">{{ lot.current_bid != null ? 'Current Bid' : 'Starting Bid' }}</span>
                    <strong class="ac__stat-value"><el-icon><Coin /></el-icon> {{ fmtMoney(currentPrice) }}</strong>
                </div>
                <div class="ac__stat ac__stat--right">
                    <span class="ac__stat-label">Bids</span>
                    <strong class="ac__stat-value">{{ lot.bid_count ?? 0 }}</strong>
                </div>
            </div>

            <div class="ac__foot">
                <span class="ac__qty"><el-icon><Box /></el-icon> {{ fmtNum(lot.net_weight_kg) }} kg</span>
                <span class="ac__cta">{{ status.tone === 'live' ? 'Bid now' : 'View auction' }} <el-icon><Goods /></el-icon></span>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.ac {
    display: flex; flex-direction: column; overflow: hidden;
    background: var(--dp-surface); border: 1px solid var(--dp-outline-variant);
    border-radius: var(--dp-card-radius, 6px); text-decoration: none;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.ac:hover { border-color: var(--dp-outline); box-shadow: 0 2px 10px rgba(0, 0, 0, .06); }

.ac__media { position: relative; height: 148px; background: var(--dp-surface-container-low); }
.ac__img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ac__img--empty { display: flex; align-items: center; justify-content: center; font-size: 30px; color: var(--dp-outline); }
.ac__badge {
    position: absolute; top: 10px; left: 10px;
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    padding: 3px 9px; border-radius: 999px; background: var(--dp-surface);
}
.ac__badge--live { color: #16A34A; }
.ac__badge--soon { color: #92400E; }
.ac__badge--neutral { color: var(--dp-on-surface-variant); }

.ac__body { display: flex; flex-direction: column; gap: 8px; padding: 14px; }
.ac__name { font-size: 14.5px; font-weight: 700; color: var(--dp-on-surface); margin: 0; line-height: 1.3; }
.ac__lot { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; color: var(--dp-on-surface-variant); margin: 0; }
.ac__lot .el-icon { font-size: 12px; }
.ac__origin { display: flex; align-items: center; gap: 5px; font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 0; }
.ac__origin .el-icon { font-size: 13px; }

.ac__chips { display: flex; flex-wrap: wrap; gap: 6px; }
.ac__chip {
    font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant);
    background: var(--dp-surface-container-low); padding: 2px 8px; border-radius: 4px;
}
.ac__chip--score { display: inline-flex; align-items: center; gap: 4px; color: #2F6B35; background: #E5FAE7; }

.ac__stats { display: flex; justify-content: space-between; gap: 12px; padding-top: 10px; border-top: 1px solid var(--dp-outline-variant); }
.ac__stat { display: flex; flex-direction: column; gap: 2px; }
.ac__stat--right { text-align: right; }
.ac__stat-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.ac__stat-value { display: inline-flex; align-items: center; gap: 4px; font-size: 15px; font-weight: 800; color: var(--dp-on-surface); font-variant-numeric: tabular-nums; }
.ac__stat-value .el-icon { font-size: 14px; color: var(--dp-outline); }

.ac__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.ac__qty { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: var(--dp-on-surface-variant); }
.ac__cta { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: var(--dp-primary); }
</style>
