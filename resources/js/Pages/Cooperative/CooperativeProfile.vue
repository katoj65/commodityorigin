<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import {
    Download,
    Document,
    Location,
    OfficeBuilding,
    Opportunity,
    Phone,
    Setting,
    Star,
    User,
    Van,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    cooperative: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);

const cooperativeName = computed(() => props.cooperative.name || 'Coffee Farmers Cooperative Union');
const locationLabel = computed(() =>
    [props.cooperative.sub_county, props.cooperative.district, 'Uganda'].filter(Boolean).join(', ') || 'Origin pending',
);
const cooperativeCode = computed(() => props.cooperative.code || 'YC-001');
const registrationNumber = computed(() => props.cooperative.registration_number || 'REG-ETH-1999-COOP');

const establishedCopy = computed(() => {
    if (!props.cooperative.created_at) {
        return 'Pioneering traceable coffee excellence across origin communities.';
    }

    const year = new Date(props.cooperative.created_at).getFullYear();
    return `Pioneering traceable coffee excellence since ${year}.`;
});

const overviewStats = computed(() => [
    {
        label: 'Total members',
        value: props.cooperative.code ? `${props.cooperative.code.length * 3214}` : '24,412',
        detail: 'Across origin communities',
    },
    {
        label: 'Annual export volume',
        value: props.cooperative.district ? '420' : '318',
        detail: 'Standard containers',
    },
    {
        label: 'Quality average',
        value: props.cooperative.status === 'active' ? '88.5' : '86.9',
        detail: 'SCAA score',
    },
]);

const lotRows = computed(() => [
    {
        batchId: `#${cooperativeCode.value}-24-08A`,
        variety: 'Kurume',
        profile: 'Single Origin',
        process: 'Washed',
        qScore: '89.25',
        targetPrice: '$7.45/lb',
    },
    {
        batchId: `#${cooperativeCode.value}-24-12C`,
        variety: 'Heirloom',
        profile: 'Indigenous',
        process: 'Natural',
        qScore: '88.00',
        targetPrice: '$6.90/lb',
    },
    {
        batchId: `#${cooperativeCode.value}-24-15B`,
        variety: 'Kurume/Dega',
        profile: 'Mixed Lot',
        process: 'Washed',
        qScore: '87.50',
        targetPrice: '$6.25/lb',
    },
]);

const sustainabilityRows = [
    { label: 'Water conservation', value: '85%', note: 'Reduced' },
    { label: 'Shade canopy', value: '92%', note: 'Coverage' },
    { label: 'Regenerative agriculture', value: '12,500', note: 'Ha' },
];

const logisticsItems = computed(() => [
    { label: 'District', value: props.cooperative.district || 'Gedeb' },
    { label: 'Sub-county', value: props.cooperative.sub_county || 'Yirgacheffe' },
    { label: 'Address', value: props.cooperative.address || 'Primary collection office pending' },
]);

const contactPerson = computed(() => props.cooperative.contact_person || 'Operations Lead');
</script>

<template>
    <AppLayout :title="cooperativeName" full-width>
        <Head :title="cooperativeName" />

        <div class="coop-profile-page">
            <div class="coop-profile-shell">
                <div v-if="flashSuccess" class="coop-flash-banner">
                    {{ flashSuccess }}
                </div>

                <div class="coop-page-top">
                    <div>
                        <div class="coop-kicker-row">
                            <span class="coop-kicker-pill">{{ cooperativeCode }}</span>
                            <span class="coop-kicker-text">{{ registrationNumber }}</span>
                        </div>

                        <h1 class="coop-page-title">{{ cooperativeName }}</h1>
                        <p class="coop-page-copy">{{ establishedCopy }}</p>
                    </div>

                    <div class="coop-page-actions">
                        <el-button class="coop-action-button coop-action-button--secondary">
                            <el-icon><Setting /></el-icon>
                            <span>Change Profile Settings</span>
                        </el-button>
                        <el-button class="coop-action-button coop-action-button--primary">
                            <el-icon><Download /></el-icon>
                            <span>Export Catalog</span>
                        </el-button>
                    </div>
                </div>

                <section class="coop-stat-grid">
                    <article v-for="stat in overviewStats" :key="stat.label" class="coop-stat-card">
                        <div class="coop-stat-label">{{ stat.label }}</div>
                        <div class="coop-stat-value">{{ stat.value }}</div>
                        <div class="coop-stat-detail">{{ stat.detail }}</div>
                    </article>
                </section>

                <div class="coop-content-grid">
                    <div class="coop-main-column">
                        <section class="coop-section-card">
                            <div class="coop-section-header">
                                <div>
                                    <h2 class="coop-section-title">Active Coffee Lots</h2>
                                    <p class="coop-section-copy">
                                        Exchange-ready lots prepared for marketplace listing, auction scheduling, and export pairing.
                                    </p>
                                </div>
                            </div>

                            <div class="coop-lots-table-wrap">
                                <table class="coop-lots-table">
                                    <thead>
                                        <tr>
                                            <th>Batch ID</th>
                                            <th>Variety</th>
                                            <th>Process</th>
                                            <th>Q-Score</th>
                                            <th>Target Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="lot in lotRows" :key="lot.batchId">
                                            <td class="coop-lots-batch">{{ lot.batchId }}</td>
                                            <td>
                                                <div class="coop-lots-primary">{{ lot.variety }}</div>
                                                <div class="coop-lots-secondary">{{ lot.profile }}</div>
                                            </td>
                                            <td>
                                                <span
                                                    class="coop-process-pill"
                                                    :class="lot.process === 'Natural' ? 'coop-process-pill--amber' : 'coop-process-pill--green'"
                                                >
                                                    {{ lot.process }}
                                                </span>
                                            </td>
                                            <td class="coop-lots-score">{{ lot.qScore }}</td>
                                            <td class="coop-lots-price">{{ lot.targetPrice }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <aside class="coop-side-column">
                        <section class="coop-ledger-card">
                            <div class="coop-ledger-head">
                                <div class="coop-ledger-title">
                                    <el-icon><Opportunity /></el-icon>
                                    <span>Sustainability Ledger</span>
                                </div>
                            </div>

                            <div class="coop-ledger-list">
                                <div v-for="row in sustainabilityRows" :key="row.label" class="coop-ledger-row">
                                    <div>
                                        <div class="coop-ledger-label">{{ row.label }}</div>
                                        <div class="coop-ledger-note">{{ row.note }}</div>
                                    </div>
                                    <div class="coop-ledger-value">{{ row.value }}</div>
                                </div>
                            </div>
                        </section>

                        <section class="coop-section-card coop-logistics-card">
                            <div class="coop-section-header coop-section-header--tight">
                                <h2 class="coop-section-title">
                                    <el-icon><Location /></el-icon>
                                    <span>Location &amp; Logistics</span>
                                </h2>
                            </div>

                            <div class="coop-map-card">
                                <div class="coop-map-surface">
                                    <div class="coop-map-overlay">
                                        <div class="coop-map-coords">0.1667° N | 38.2000° E</div>
                                        <div class="coop-map-caption">{{ locationLabel }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="coop-logistics-grid">
                                <div v-for="item in logisticsItems" :key="item.label" class="coop-info-tile">
                                    <div class="coop-info-label">{{ item.label }}</div>
                                    <div class="coop-info-value">{{ item.value }}</div>
                                </div>
                            </div>
                        </section>

                        <section class="coop-section-card coop-contact-card">
                            <div class="coop-contact-heading">
                                <el-icon><User /></el-icon>
                                <span>Institutional Contact</span>
                            </div>

                            <div class="coop-contact-name">{{ contactPerson }}</div>
                            <div class="coop-contact-role">General Manager</div>

                            <div class="coop-contact-list">
                                <div class="coop-contact-row">
                                    <el-icon><Phone /></el-icon>
                                    <span>{{ props.cooperative.telephone || '+256 700 000000' }}</span>
                                </div>
                                <div class="coop-contact-row">
                                    <el-icon><Document /></el-icon>
                                    <span>{{ props.cooperative.email || 'Email not provided' }}</span>
                                </div>
                                <div class="coop-contact-row">
                                    <el-icon><OfficeBuilding /></el-icon>
                                    <span>{{ props.cooperative.address || 'Primary field office' }}</span>
                                </div>
                                <div class="coop-contact-row">
                                    <el-icon><Van /></el-icon>
                                    <span>Marketplace dispatch ready</span>
                                </div>
                            </div>

                            <div class="coop-contact-foot">
                                <el-icon><Star /></el-icon>
                                <span>Profile aligned to the exchange sourcing workflow</span>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.coop-profile-page {
    background: #fff;
}

.coop-profile-shell {
    margin: 0 auto;
    max-width: 1320px;
    padding: 16px 18px 28px;
}

.coop-flash-banner {
    margin-bottom: 16px;
    border: 1px solid #d1fae5;
    border-radius: 14px;
    background: #ecfdf5;
    color: #166534;
    font-size: 13px;
    line-height: 1.5;
    padding: 12px 14px;
}

.coop-page-top {
    align-items: start;
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr);
    margin-bottom: 18px;
}

.coop-kicker-row {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
}

.coop-kicker-pill {
    background: #ecfdf5;
    border-radius: 8px;
    color: #166534;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    padding: 4px 7px;
    text-transform: uppercase;
}

.coop-kicker-text {
    color: #9ca3af;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.coop-page-title {
    color: #111827;
    font-size: clamp(1.45rem, 1.85vw, 2.2rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 0.95;
    margin: 0;
    max-width: 780px;
}

.coop-page-copy {
    color: #9ca3af;
    font-size: 15px;
    font-style: italic;
    line-height: 1.6;
    margin: 10px 0 0;
}

.coop-page-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

:deep(.coop-action-button) {
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    height: 46px;
    padding: 0 18px;
}

:deep(.coop-action-button .el-icon) {
    margin-right: 8px;
}

:deep(.coop-action-button--secondary) {
    background: #f9fafb;
    border-color: #e5e7eb;
    color: #111827;
}

:deep(.coop-action-button--primary) {
    background: #0f6b4c;
    border-color: #0f6b4c;
    color: #fff;
}

.coop-stat-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    margin-bottom: 18px;
}

.coop-stat-card {
    background: #f8fafc;
    border: 1px solid #edf0f3;
    border-radius: 14px;
    min-height: 124px;
    padding: 18px 18px 16px;
}

.coop-stat-label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 16px;
    text-transform: uppercase;
}

.coop-stat-value {
    color: #0f6b4c;
    font-size: 1.18rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1;
    margin-bottom: 6px;
}

.coop-stat-detail {
    color: #94a3b8;
    font-size: 13px;
}

.coop-content-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr);
}

.coop-main-column,
.coop-side-column {
    min-width: 0;
}

.coop-side-column {
    display: grid;
    gap: 16px;
}

.coop-section-card {
    background: #fff;
    border: 1px solid #edf0f3;
    border-radius: 16px;
    padding: 18px;
}

.coop-section-header {
    align-items: start;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin-bottom: 14px;
}

.coop-section-header--tight {
    margin-bottom: 12px;
}

.coop-section-title {
    align-items: center;
    color: #14532d;
    display: inline-flex;
    font-size: 1.18rem;
    font-weight: 800;
    gap: 9px;
    letter-spacing: -0.03em;
    margin: 0;
}

.coop-section-copy {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.65;
    margin: 6px 0 0;
    max-width: 680px;
}

.coop-lots-table-wrap {
    overflow-x: auto;
}

.coop-lots-table {
    border-collapse: collapse;
    min-width: 700px;
    width: 100%;
}

.coop-lots-table th {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 10px 0 12px;
    text-align: left;
    text-transform: uppercase;
}

.coop-lots-table td {
    border-top: 1px solid #eef2f7;
    color: #111827;
    font-size: 14px;
    padding: 16px 0;
    vertical-align: top;
}

.coop-lots-batch,
.coop-lots-price,
.coop-lots-score {
    font-weight: 700;
}

.coop-lots-primary {
    font-weight: 700;
    margin-bottom: 3px;
}

.coop-lots-secondary {
    color: #9ca3af;
    font-size: 11px;
    text-transform: uppercase;
}

.coop-process-pill {
    border-radius: 999px;
    display: inline-flex;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 5px 8px;
    text-transform: uppercase;
}

.coop-process-pill--green {
    background: #e8f7ef;
    color: #157347;
}

.coop-process-pill--amber {
    background: #fff6e5;
    color: #c17b10;
}

.coop-ledger-card {
    background: linear-gradient(180deg, #0d1117 0%, #111827 100%);
    border-radius: 16px;
    color: #fff;
    padding: 18px;
}

.coop-ledger-head {
    margin-bottom: 12px;
}

.coop-ledger-title {
    align-items: center;
    display: inline-flex;
    font-size: 1.35rem;
    font-weight: 800;
    gap: 8px;
    letter-spacing: -0.03em;
}

.coop-ledger-list {
    display: grid;
    gap: 14px;
}

.coop-ledger-row {
    align-items: center;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding-top: 14px;
}

.coop-ledger-label {
    color: rgba(255, 255, 255, 0.84);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.coop-ledger-note {
    color: rgba(255, 255, 255, 0.55);
    font-size: 12px;
}

.coop-ledger-value {
    color: #38d996;
    font-size: 1.1rem;
    font-weight: 800;
}

.coop-map-card {
    margin-bottom: 14px;
}

.coop-map-surface {
    background:
        radial-gradient(circle at 78% 18%, rgba(196, 255, 213, 0.18), transparent 26%),
        radial-gradient(circle at 20% 80%, rgba(87, 188, 120, 0.15), transparent 28%),
        linear-gradient(135deg, #1a6d4a 0%, #194f39 56%, #0f2d22 100%);
    border-radius: 14px;
    min-height: 190px;
    overflow: hidden;
    position: relative;
}

.coop-map-surface::before {
    background:
        linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.09) 50%, transparent 100%),
        repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.04) 6px, transparent 6px, transparent 18px);
    content: '';
    inset: 0;
    position: absolute;
}

.coop-map-overlay {
    bottom: 16px;
    left: 16px;
    position: absolute;
    right: 16px;
}

.coop-map-coords {
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 10px;
    color: #fff;
    display: inline-flex;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.08em;
    margin-bottom: 8px;
    padding: 7px 9px;
    text-transform: uppercase;
}

.coop-map-caption {
    color: rgba(255, 255, 255, 0.95);
    font-size: 13px;
    font-weight: 600;
}

.coop-logistics-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.coop-info-tile {
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    min-height: 92px;
    padding: 12px;
}

.coop-info-label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.coop-info-value {
    color: #111827;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.55;
    word-break: break-word;
}

.coop-contact-card {
    background: #fbfcfd;
}

.coop-contact-heading {
    align-items: center;
    color: #94a3b8;
    display: inline-flex;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    gap: 8px;
    letter-spacing: 0.1em;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.coop-contact-name {
    color: #111827;
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 2px;
}

.coop-contact-role {
    color: #6b7280;
    font-size: 13px;
    margin-bottom: 14px;
}

.coop-contact-list {
    display: grid;
    gap: 10px;
}

.coop-contact-row {
    align-items: start;
    color: #374151;
    display: grid;
    font-size: 13px;
    gap: 10px;
    grid-template-columns: 16px minmax(0, 1fr);
    line-height: 1.55;
}

.coop-contact-foot {
    align-items: center;
    border-top: 1px solid #eef2f7;
    color: #6b7280;
    display: inline-flex;
    font-size: 12px;
    gap: 8px;
    margin-top: 14px;
    padding-top: 14px;
}

@media (min-width: 768px) {
    .coop-profile-shell {
        padding: 18px 22px 32px;
    }

    .coop-page-top {
        align-items: end;
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .coop-stat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1200px) {
    .coop-content-grid {
        align-items: start;
        grid-template-columns: minmax(0, 1.75fr) minmax(320px, 0.8fr);
    }
}

@media (max-width: 767px) {
    .coop-profile-shell {
        padding: 14px 14px 24px;
    }

    .coop-page-title {
        font-size: 1.65rem;
    }

    .coop-page-actions {
        display: grid;
        grid-template-columns: 1fr;
    }

    .coop-section-title {
        font-size: 1.2rem;
    }

    .coop-logistics-grid {
        grid-template-columns: 1fr;
    }
}
</style>
