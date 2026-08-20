<script setup>
import { ref } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { Compass, Goods, Location, Shop } from '@element-plus/icons-vue';

defineProps({
    store: { type: Object, default: null },
    statusOptions: { type: Array, default: () => [] },
    importResult: { type: Object, default: null },
});

/**
 * Dummy preview data — the market is not backed by real store items yet,
 * this is a placeholder of what browsing across stores could look like.
 */
const dummyItems = ref([
    { id: 1, name: 'Washed Arabica AA', seller: 'Kapchorwa Highland Traders', category: 'Green Coffee', price: 5.4, currency: 'USD', unit: 'kg', location: 'Kapchorwa, Uganda' },
    { id: 2, name: 'Natural Robusta Screen 18', seller: 'Mbale Export Co.', category: 'Green Coffee', price: 3.1, currency: 'USD', unit: 'kg', location: 'Mbale, Uganda' },
    { id: 3, name: 'Honey Bourbon Grade 1', seller: 'Rwenzori Specialty Roasters', category: 'Specialty Coffee', price: 6.9, currency: 'USD', unit: 'kg', location: 'Rwenzori, Uganda' },
    { id: 4, name: 'Fully Washed SL28', seller: 'Sipi Falls Cooperative', category: 'Specialty Coffee', price: 7.25, currency: 'USD', unit: 'kg', location: 'Sipi Falls, Uganda' },
    { id: 5, name: 'Sun-Dried Typica AB', seller: 'Kigezi Growers Union', category: 'Green Coffee', price: 4.6, currency: 'USD', unit: 'kg', location: 'Kigezi, Uganda' },
    { id: 6, name: 'Roasted Espresso Blend', seller: 'Fort Portal Roastery', category: 'Roasted Coffee', price: 9.8, currency: 'USD', unit: 'kg', location: 'Fort Portal, Uganda' },
]);

function formatMoney(value, currency) {
    return `${currency} ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}
</script>

<template>
    <StoreLayout title="Market" :store="store" :status-options="statusOptions" :import-result="importResult">
        <template #icon>
            <el-icon :size="16"><Compass /></el-icon>
        </template>

        <div class="mkt-body">
            <div class="mkt-grid">
                <article v-for="item in dummyItems" :key="item.id" class="mkt-card">
                    <div class="mkt-card__thumb"><el-icon :size="20"><Goods /></el-icon></div>
                    <div class="mkt-card__body">
                        <div class="mkt-card__category">{{ item.category }}</div>
                        <div class="mkt-card__name">{{ item.name }}</div>
                        <div class="mkt-card__seller"><el-icon :size="12"><Shop /></el-icon> {{ item.seller }}</div>
                        <div class="mkt-card__location"><el-icon :size="12"><Location /></el-icon> {{ item.location }}</div>
                        <div class="mkt-card__footer">
                            <span class="mkt-card__price">{{ formatMoney(item.price, item.currency) }}<small>/{{ item.unit }}</small></span>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </StoreLayout>
</template>

<style scoped>
.mkt-body { padding: 1.5rem; }

.mkt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
}

.mkt-card {
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #eef2f0;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 1px 2px rgba(17, 24, 39, .03), 0 12px 28px -18px rgba(17, 24, 39, .14);
}

.mkt-card__thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(20, 92, 66, 0.08);
    color: #145c42;
    margin-bottom: 12px;
}

.mkt-card__category {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #145c42;
    margin-bottom: 4px;
}
.mkt-card__name { font-size: 0.9375rem; font-weight: 700; color: #111827; line-height: 1.3; margin-bottom: 8px; }
.mkt-card__seller,
.mkt-card__location {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 3px;
}
.mkt-card__seller :deep(.el-icon),
.mkt-card__location :deep(.el-icon) { color: #9ca3af; flex-shrink: 0; }

.mkt-card__footer {
    display: flex;
    align-items: center;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f3f4f6;
}
.mkt-card__price { font-size: 1rem; font-weight: 800; color: #111827; }
.mkt-card__price small { font-size: 0.75rem; font-weight: 600; color: #9ca3af; }

@media (max-width: 640px) {
    .mkt-body { padding: 1.25rem; }
}
</style>
