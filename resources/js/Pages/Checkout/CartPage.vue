<script setup>
import { computed, ref } from 'vue';
import { Delete, Document, Van } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const cartItems = ref([
    {
        id: 'LOT-ET-001',
        name: 'Ethiopia Yirgacheffe G1',
        producer: 'Aramo Washing Station',
        profile: 'Special Reserve',
        score: '91.5',
        variety: 'Heirloom',
        process: 'Washed',
        unitWeight: '60kg Bag',
        quantity: 12,
        pricePerLb: 4.85,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVNPRKcnvtgsayf1-HlE1xA92LWW1C56Io3VMreh4aujnZTgd7RVNEZOyEqFGcffC6O3JdFFEczJbLDdWYhY3SPZ_97Ep-mSdEA6EpSHOYxQ4YC-9rWllkkDGEgrkRhX8fdY9yD34FR8UBs42K4RgVHEi6OXDt4QvP-hJgG1uWAZlyFMQ7HCYg9NcS7oQW5HysDvCK3FiXBDRpfkupmdW5tIy7o5GV8ZL8feaXnYtU6ZpDEAJvS_XKRffdezzJJCSUQeF2AHlDDapn',
    },
    {
        id: 'LOT-CO-014',
        name: 'Colombia Huila Pitalito',
        producer: 'El Diviso Estate',
        profile: 'Sidra Variety',
        score: '89.2',
        variety: 'Sidra',
        process: 'Anaerobic',
        unitWeight: '60kg Bag',
        quantity: 8,
        pricePerLb: 5.2,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDv_vhmIorxO5TXHfY4x3mvxWbuX9RQy83ltILS7_Znxq5fvxI-Af9qAPgN5wgaIFsW3SpILtw5fP4_4RHBnrAN1hoGJ6K_oJPDsrhpRlJPQrHsyJqVXi7bRF1VJONSOLAiG2hff_cRXPZx30imH9z7qDIOPwif1o6pzkCvQVLbJMvIcJO6YoRd1gm0kWJd-m8xEmRAUHSdv-XePrjlP-jNVr3X7AggNZkF5_rFaGloIp9BhynvWLg4uk1mj71GfuBx5Hmi1FzxFK-_',
    },
]);

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(value);

const subtotal = computed(() =>
    cartItems.value.reduce((sum, item) => sum + item.quantity * item.pricePerLb * 154, 0),
);
const logistics = computed(() => (cartItems.value.length ? 642 : 0));
const complianceFees = computed(() => 112.5);
const total = computed(() => subtotal.value + logistics.value + complianceFees.value);
const selectedVolume = computed(() => cartItems.value.reduce((sum, item) => sum + item.quantity * 60, 0));
const totalBags = computed(() => cartItems.value.reduce((sum, item) => sum + item.quantity, 0));

const increaseQuantity = (id) => {
    const item = cartItems.value.find((entry) => entry.id === id);

    if (item) {
        item.quantity += 1;
    }
};

const decreaseQuantity = (id) => {
    const item = cartItems.value.find((entry) => entry.id === id);

    if (item && item.quantity > 1) {
        item.quantity -= 1;
    }
};

const removeItem = (id) => {
    cartItems.value = cartItems.value.filter((item) => item.id !== id);
};
</script>

<template>
    <AppLayout title="Acquisition Basket" full-width>
        <div class="basket-page">
            <div class="basket-shell">
                <section class="basket-header">
                    <div>
                        <div class="basket-kicker">Exchange Pipeline</div>
                        <h1 class="basket-title">Acquisition Basket</h1>
                        <p class="basket-copy">
                            Review institutional coffee lots currently staged for trade execution. All pricing reflects
                            direct-trade transparency standards.
                        </p>
                    </div>
                </section>

                <div class="basket-grid">
                    <section class="basket-items-column">
                        <article
                            v-for="item in cartItems"
                            :key="item.id"
                            class="basket-item-card"
                        >
                            <div class="basket-item-media">
                                <img :src="item.image" :alt="item.name" class="basket-item-image" />
                            </div>

                            <div class="basket-item-content">
                                <div class="basket-item-top">
                                    <div class="basket-item-main">
                                        <h2 class="basket-item-title">{{ item.name }}</h2>
                                        <p class="basket-item-subtitle">
                                            {{ item.producer }} <span>•</span> {{ item.profile }}
                                        </p>
                                    </div>

                                    <div class="basket-score-card">
                                        <div class="basket-score-card__label">SCAA Score</div>
                                        <div class="basket-score-card__value">{{ item.score }}</div>
                                    </div>
                                </div>

                                <div class="basket-spec-grid">
                                    <div class="basket-spec">
                                        <div class="basket-spec__label">Variety</div>
                                        <div class="basket-spec__value">{{ item.variety }}</div>
                                    </div>
                                    <div class="basket-spec">
                                        <div class="basket-spec__label">Process</div>
                                        <div class="basket-spec__value">{{ item.process }}</div>
                                    </div>
                                    <div class="basket-spec">
                                        <div class="basket-spec__label">Unit weight</div>
                                        <div class="basket-spec__value">{{ item.unitWeight }}</div>
                                    </div>
                                    <div class="basket-spec basket-spec--quantity">
                                        <div class="basket-spec__label">Quantity</div>
                                        <div class="basket-quantity-picker">
                                            <button type="button" @click="decreaseQuantity(item.id)">-</button>
                                            <span>{{ item.quantity }}</span>
                                            <button type="button" @click="increaseQuantity(item.id)">+</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="basket-item-footer">
                                    <div class="basket-item-actions">
                                        <el-button class="basket-secondary-action" @click="removeItem(item.id)">
                                            <el-icon><Delete /></el-icon>
                                            <span>Remove</span>
                                        </el-button>
                                        <button type="button" class="basket-link-action">Save For Later</button>
                                    </div>

                                    <div class="basket-price-block">
                                        <div class="basket-price-block__label">Price per lb</div>
                                        <div class="basket-price-block__value">${{ item.pricePerLb.toFixed(2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <div class="basket-return-row">
                            <button type="button" class="basket-return-link">← Return to Marketplace</button>
                            <div class="basket-volume-meta">
                                <span>Selected Volume</span>
                                <strong>{{ selectedVolume.toLocaleString() }}kg ({{ totalBags }} Bags)</strong>
                            </div>
                        </div>
                    </section>

                    <aside class="basket-summary-column">
                        <section class="basket-summary-card">
                            <div class="basket-summary-title">
                                <span>Order Summary</span>
                                <span class="basket-summary-badge"></span>
                            </div>

                            <div class="basket-summary-rows">
                                <div class="basket-summary-row">
                                    <span>Gross Commodity Total</span>
                                    <strong>{{ formatCurrency(subtotal) }}</strong>
                                </div>
                                <div class="basket-summary-row">
                                    <span>Logistics &amp; Insurance (Est.)</span>
                                    <strong>{{ formatCurrency(logistics) }}</strong>
                                </div>
                                <div class="basket-summary-row">
                                    <span>Compliance Fees &amp; Tax</span>
                                    <strong>{{ formatCurrency(complianceFees) }}</strong>
                                </div>
                            </div>

                            <div class="basket-total-block">
                                <div class="basket-total-block__label">Estimated Total</div>
                                <div class="basket-total-block__value">{{ formatCurrency(total) }}</div>
                                <div class="basket-total-block__currency">USD</div>
                            </div>

                            <el-button class="basket-finalize-button">
                                Finalize Order
                                <span class="basket-finalize-arrow">→</span>
                            </el-button>

                            <p class="basket-summary-note">
                                By finalizing, you agree to the institutional trade protocols and standard shipping terms
                                defined in the Master Service Agreement.
                            </p>
                        </section>

                        <section class="basket-side-card">
                            <div class="basket-side-card__title">
                                <el-icon><Van /></el-icon>
                                <span>Shipping Estimate</span>
                            </div>
                            <p class="basket-side-card__copy">
                                Standard ocean freight ex-works. Estimated departure window: 7-10 business days.
                            </p>
                        </section>

                        <section class="basket-side-card">
                            <div class="basket-side-card__title">
                                <el-icon><Document /></el-icon>
                                <span>Line of Credit</span>
                            </div>
                            <p class="basket-side-card__copy">
                                Tier 1 credit status active. Net-30 terms applied to this transaction.
                            </p>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.basket-page {
    background: #fff;
}

.basket-shell {
    margin: 0 auto;
    max-width: 1320px;
    padding: 10px 10px 30px;
}

.basket-header {
    margin-bottom: 18px;
}

.basket-kicker {
    color: #c28a3d;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.basket-title {
    color: #111827;
    font-size: clamp(1.6rem, 2vw, 2.35rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin: 0 0 10px;
}

.basket-copy {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.65;
    margin: 0;
    max-width: 640px;
}

.basket-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr);
}

.basket-items-column {
    min-width: 0;
}

.basket-item-card {
    background: #fff;
    border: 1px solid #e9eef3;
    border-radius: 16px;
    display: grid;
    gap: 16px;
    grid-template-columns: 120px minmax(0, 1fr);
    margin-bottom: 16px;
    padding: 14px;
}

.basket-item-media {
    overflow: hidden;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
    height: 154px;
}

.basket-item-image {
    height: 100%;
    object-fit: cover;
    width: 100%;
}

.basket-item-content {
    min-width: 0;
}

.basket-item-top {
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(0, 1fr);
    margin-bottom: 14px;
}

.basket-item-title {
    color: #1f2937;
    font-size: 1.12rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.05;
    margin: 0 0 8px;
}

.basket-item-subtitle {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.55;
    margin: 0;
}

.basket-item-subtitle span {
    margin: 0 5px;
}

.basket-score-card {
    align-items: center;
    background: #bdf3d4;
    border-radius: 10px;
    display: inline-flex;
    gap: 10px;
    justify-self: start;
    padding: 9px 12px;
}

.basket-score-card__label {
    color: #0f6b4c;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.basket-score-card__value {
    color: #0f6b4c;
    font-size: 1.15rem;
    font-weight: 800;
    line-height: 1;
}

.basket-spec-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 16px;
}

.basket-spec__label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.basket-spec__value {
    color: #374151;
    font-size: 13px;
    font-weight: 700;
}

.basket-quantity-picker {
    align-items: center;
    display: inline-flex;
    gap: 10px;
}

.basket-quantity-picker button {
    background: transparent;
    border: 0;
    color: #64748b;
    font-size: 18px;
    height: 24px;
    line-height: 1;
    padding: 0;
    width: 18px;
}

.basket-quantity-picker span {
    color: #1f2937;
    font-size: 14px;
    font-weight: 800;
    min-width: 16px;
    text-align: center;
}

.basket-item-footer {
    align-items: end;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    justify-content: space-between;
}

.basket-item-actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

:deep(.basket-secondary-action) {
    --el-button-bg-color: #fff;
    --el-button-border-color: transparent;
    --el-button-text-color: #94a3b8;
    --el-button-hover-bg-color: #f8fafc;
    --el-button-hover-border-color: transparent;
    --el-button-hover-text-color: #64748b;
    --el-button-active-bg-color: #f8fafc;
    --el-button-active-border-color: transparent;
    --el-button-active-text-color: #64748b;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    height: 28px;
    letter-spacing: 0.08em;
    padding: 0 2px;
    text-transform: uppercase;
}

.basket-link-action {
    background: transparent;
    border: 0;
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    padding: 0;
    text-transform: uppercase;
}

.basket-price-block {
    text-align: right;
}

.basket-price-block__label {
    color: #94a3b8;
    font-size: 10px;
    margin-bottom: 4px;
}

.basket-price-block__value {
    color: #0f6b4c;
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
}

.basket-summary-column {
    display: grid;
    gap: 14px;
}

.basket-summary-card {
    background: #f8fafc;
    border: 1px solid #e9eef3;
    border-radius: 16px;
    padding: 18px;
}

.basket-summary-title {
    align-items: center;
    color: #1f2937;
    display: flex;
    font-size: 1.35rem;
    font-weight: 800;
    justify-content: space-between;
    letter-spacing: -0.03em;
    margin-bottom: 18px;
}

.basket-summary-badge {
    background: #0f6b4c;
    border-radius: 999px;
    display: inline-block;
    height: 10px;
    width: 10px;
}

.basket-summary-rows {
    display: grid;
    gap: 14px;
}

.basket-summary-row {
    align-items: start;
    color: #6b7280;
    display: flex;
    font-size: 13px;
    justify-content: space-between;
    gap: 16px;
    line-height: 1.5;
}

.basket-summary-row strong {
    color: #374151;
    font-size: 13px;
    white-space: nowrap;
}

.basket-total-block {
    border-top: 1px solid #e5e7eb;
    margin-top: 16px;
    padding-top: 16px;
}

.basket-total-block__label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.basket-total-block__value {
    color: #1f2937;
    font-size: 2.35rem;
    font-weight: 800;
    letter-spacing: -0.05em;
    line-height: 1;
}

.basket-total-block__currency {
    color: #94a3b8;
    font-size: 11px;
    font-weight: 700;
    margin-top: 4px;
    text-transform: uppercase;
}

:deep(.basket-finalize-button) {
    --el-button-bg-color: #0f6b4c;
    --el-button-border-color: #0f6b4c;
    --el-button-text-color: #ffffff;
    --el-button-hover-bg-color: #0b5139;
    --el-button-hover-border-color: #0b5139;
    --el-button-active-bg-color: #0b5139;
    --el-button-active-border-color: #0b5139;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 800;
    height: 46px;
    margin-top: 18px;
    text-transform: uppercase;
    width: 100%;
}

.basket-finalize-arrow {
    margin-left: 8px;
}

.basket-summary-note {
    color: #9ca3af;
    font-size: 11px;
    line-height: 1.6;
    margin: 14px 0 0;
}

.basket-side-card {
    background: #fff;
    border: 1px solid #e9eef3;
    border-radius: 14px;
    padding: 14px 16px;
}

.basket-side-card__title {
    align-items: center;
    color: #374151;
    display: inline-flex;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    gap: 8px;
    letter-spacing: 0.1em;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.basket-side-card__copy {
    color: #6b7280;
    font-size: 12px;
    line-height: 1.65;
    margin: 0;
}

.basket-return-row {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    justify-content: space-between;
    padding: 10px 2px 0;
}

.basket-return-link {
    background: transparent;
    border: 0;
    color: #94a3b8;
    font-size: 13px;
    font-weight: 600;
    padding: 0;
}

.basket-volume-meta {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-align: right;
    text-transform: uppercase;
}

.basket-volume-meta strong {
    color: #374151;
    display: block;
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0;
    margin-top: 4px;
    text-transform: none;
}

@media (min-width: 1024px) {
    .basket-grid {
        align-items: start;
        grid-template-columns: minmax(0, 1.55fr) 330px;
    }

    .basket-summary-column {
        position: sticky;
        top: 92px;
    }

    .basket-item-top {
        align-items: start;
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .basket-spec-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (max-width: 767px) {
    .basket-shell {
        padding: 10px 8px 24px;
    }

    .basket-item-card {
        grid-template-columns: 1fr;
    }

    .basket-item-media {
        height: 220px;
    }

    .basket-title {
        font-size: 1.85rem;
    }

    .basket-total-block__value {
        font-size: 2rem;
    }
}
</style>
