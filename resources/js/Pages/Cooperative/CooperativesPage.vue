<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Search, Setting } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    cooperatives: {
        type: Array,
        default: () => [],
    },
});

const search = ref('');
const region = ref('all');
const certification = ref('all');

const regionOptions = computed(() =>
    [...new Set(props.cooperatives.map((item) => item.district).filter(Boolean))].sort(),
);

const filteredCooperatives = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.cooperatives.filter((cooperative) => {
        const haystack = [
            cooperative.name,
            cooperative.code,
            cooperative.registration_number,
            cooperative.district,
            cooperative.sub_county,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        const matchesSearch = !term || haystack.includes(term);
        const matchesRegion = region.value === 'all' || cooperative.district === region.value;
        const matchesCertification = certification.value === 'all' || cooperative.status === 'active';

        return matchesSearch && matchesRegion && matchesCertification;
    });
});

const directoryRows = computed(() =>
    filteredCooperatives.value.map((cooperative, index) => {
        const memberEstimate = cooperative.code
            ? cooperative.code.replace(/[^A-Za-z0-9]/g, '').length * 4000
            : 8000 + index * 2500;

        return {
            ...cooperative,
            initials: (cooperative.name || 'Coop')
                .split(' ')
                .filter(Boolean)
                .slice(0, 2)
                .map((part) => part[0]?.toUpperCase())
                .join('')
                .slice(0, 2) || 'CP',
            territory: [cooperative.district, cooperative.sub_county].filter(Boolean).join(', ') || 'Origin pending',
            networkSize: `${memberEstimate.toLocaleString()} members`,
            varietalFocus: [
                cooperative.district?.toLowerCase().includes('mbale') ? 'Arabica' : 'Washed Arabica',
                cooperative.status === 'active' ? 'Organic' : 'Conventional',
            ],
        };
    }),
);

const totalMemberNetwork = computed(() =>
    directoryRows.value
        .reduce((sum, item) => sum + Number.parseInt(item.networkSize.replace(/[^\d]/g, ''), 10), 0)
        .toLocaleString(),
);

const totalAnnualExport = computed(() => (directoryRows.value.length ? directoryRows.value.length * 16.7 : 0).toFixed(0));
</script>

<template>
    <AppLayout title="Cooperative Directory" full-width>
        <Head title="Cooperative Directory" />

        <div class="coops-directory-page">
            <div class="coops-directory-shell">
                <section class="coops-directory-head">
                    <div>
                        <div class="coops-directory-kicker">Institutional Network</div>
                        <h1 class="coops-directory-title">Cooperative Directory</h1>
                        <p class="coops-directory-copy">
                            Verified institutional cooperatives across coffee origins. Real-time access to traceability,
                            member data, and annual export capacities.
                        </p>
                    </div>
                </section>

                <section class="coops-stat-grid">
                    <article class="coops-stat-card">
                        <div class="coops-stat-label">Total cooperatives</div>
                        <div class="coops-stat-value">{{ props.cooperatives.length }}</div>
                        <div class="coops-stat-note">+4%</div>
                    </article>
                    <article class="coops-stat-card">
                        <div class="coops-stat-label">Total member network</div>
                        <div class="coops-stat-value">{{ totalMemberNetwork }}</div>
                        <div class="coops-stat-note">+12%</div>
                    </article>
                    <article class="coops-stat-card">
                        <div class="coops-stat-label">Total annual export</div>
                        <div class="coops-stat-value">{{ totalAnnualExport }}</div>
                        <div class="coops-stat-note">Containers</div>
                    </article>
                </section>

                <section class="coops-filter-card">
                    <div class="coops-filter-grid">
                        <div class="coops-search-wrap">
                            <el-input v-model="search" placeholder="Search cooperatives by name or code...">
                                <template #prefix>
                                    <el-icon><Search /></el-icon>
                                </template>
                            </el-input>
                        </div>

                        <el-select v-model="region" placeholder="Region">
                            <el-option label="Region: All" value="all" />
                            <el-option v-for="item in regionOptions" :key="item" :label="item" :value="item" />
                        </el-select>

                        <el-select v-model="certification" placeholder="Certification">
                            <el-option label="Certification: All" value="all" />
                            <el-option label="Certification: Verified" value="verified" />
                        </el-select>

                        <button type="button" class="coops-filter-button" aria-label="Advanced filters">
                            <el-icon><Setting /></el-icon>
                        </button>
                    </div>
                </section>

                <section class="coops-table-card">
                    <div class="coops-table-wrap">
                        <table class="coops-table">
                            <thead>
                                <tr>
                                    <th>Cooperative Identity</th>
                                    <th>System Code</th>
                                    <th>Territory</th>
                                    <th>Network Size</th>
                                    <th>Varietal Focus</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody v-if="directoryRows.length">
                                <tr v-for="cooperative in directoryRows" :key="cooperative.id">
                                    <td>
                                        <div class="coops-identity-cell">
                                            <div class="coops-avatar">{{ cooperative.initials }}</div>
                                            <div>
                                                <div class="coops-name">{{ cooperative.name }}</div>
                                                <div class="coops-subcopy">
                                                    {{ cooperative.code || 'COOP' }} · {{ cooperative.registration_number || 'Registry pending' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="coops-code-primary">{{ cooperative.code || '—' }}</div>
                                        <div class="coops-code-secondary">{{ cooperative.status || 'active' }}</div>
                                    </td>
                                    <td>
                                        <div class="coops-territory">{{ cooperative.territory }}</div>
                                    </td>
                                    <td>
                                        <div class="coops-network">{{ cooperative.networkSize }}</div>
                                    </td>
                                    <td>
                                        <div class="coops-tag-row">
                                            <span
                                                v-for="tag in cooperative.varietalFocus"
                                                :key="`${cooperative.id}-${tag}`"
                                                class="coops-tag-chip"
                                                :class="{ 'coops-tag-chip--amber': tag.toLowerCase() === 'organic' }"
                                            >
                                                {{ tag }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <Link :href="route('cooperative.show', cooperative.id)" class="coops-view-link">
                                            View Profile
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="!directoryRows.length" class="coops-empty-state">
                        <div class="coops-empty-state__title">No cooperatives match this filter</div>
                        <p class="coops-empty-state__copy">
                            Try a different search term or region filter, or register a new cooperative.
                        </p>
                    </div>

                    <div class="coops-table-footer">
                        <div class="coops-table-footer__copy">
                            Showing 1 to {{ directoryRows.length }} of {{ props.cooperatives.length }} entries
                        </div>
                        <div class="coops-pagination">
                            <button type="button" class="coops-pagination__button" disabled>Previous</button>
                            <button type="button" class="coops-pagination__button is-active">Next</button>
                        </div>
                    </div>
                </section>

                <section class="coops-bottom-grid">
                    <article class="coops-corridor-card">
                        <div class="coops-corridor-map"></div>
                        <div class="coops-corridor-title">East African Corridor</div>
                        <p class="coops-corridor-copy">
                            Our highest density of certified cooperatives is concentrated in the high-altitude volcanic soils of Ethiopia and Uganda.
                        </p>
                    </article>

                    <article class="coops-compliance-card">
                        <div class="coops-compliance-kicker">Institutional Compliance Standards</div>
                        <p class="coops-compliance-copy">
                            Each cooperative listing has passed a multi-stage audit process covering financial transparency,
                            environmental stewardship, and social labor conditions.
                        </p>
                        <div class="coops-compliance-metrics">
                            <div>
                                <div class="coops-compliance-value">100%</div>
                                <div class="coops-compliance-label">Audit trails</div>
                            </div>
                            <div>
                                <div class="coops-compliance-value">No-Risk</div>
                                <div class="coops-compliance-label">KYC policy</div>
                            </div>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.coops-directory-page {
    background: #fff;
}

.coops-directory-shell {
    margin: 0 auto;
    max-width: 1320px;
    padding: 10px 10px 28px;
}

.coops-directory-head {
    margin-bottom: 16px;
}

.coops-directory-kicker {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.coops-directory-title {
    color: #111827;
    font-size: clamp(1.5rem, 1.9vw, 2.2rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin: 0 0 8px;
}

.coops-directory-copy {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.65;
    margin: 0;
    max-width: 760px;
}

.coops-stat-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    margin-bottom: 16px;
}

.coops-stat-card {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 14px;
    min-height: 112px;
    padding: 14px 16px;
}

.coops-stat-label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.coops-stat-value {
    color: #111827;
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 6px;
}

.coops-stat-note {
    color: #0f9f5d;
    font-size: 12px;
    font-weight: 700;
}

.coops-filter-card,
.coops-table-card {
    background: #fff;
    border: 1px solid #e9eef3;
    border-radius: 16px;
}

.coops-filter-card {
    margin-bottom: 16px;
    padding: 14px;
}

.coops-filter-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(0, 1fr);
}

.coops-search-wrap {
    min-width: 0;
}

.coops-filter-button {
    align-items: center;
    background: #111827;
    border: 0;
    border-radius: 12px;
    color: #fff;
    display: inline-flex;
    font-size: 16px;
    height: 42px;
    justify-content: center;
    width: 100%;
}

.coops-table-wrap {
    overflow-x: auto;
    padding: 0 14px;
}

.coops-table {
    border-collapse: collapse;
    min-width: 980px;
    width: 100%;
}

.coops-table th {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 14px 0 12px;
    text-align: left;
    text-transform: uppercase;
}

.coops-table td {
    border-top: 1px solid #eef2f7;
    color: #111827;
    font-size: 14px;
    padding: 16px 0;
    vertical-align: middle;
}

.coops-identity-cell {
    align-items: center;
    display: flex;
    gap: 12px;
}

.coops-avatar {
    align-items: center;
    background: linear-gradient(135deg, #d6f5e5 0%, #8dcfb0 100%);
    border-radius: 12px;
    color: #14532d;
    display: flex;
    font-size: 12px;
    font-weight: 800;
    height: 38px;
    justify-content: center;
    width: 38px;
}

.coops-name {
    color: #14532d;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 4px;
}

.coops-subcopy,
.coops-code-secondary {
    color: #94a3b8;
    font-size: 11px;
    line-height: 1.5;
}

.coops-code-primary,
.coops-network,
.coops-territory {
    color: #374151;
    font-size: 14px;
    font-weight: 700;
}

.coops-tag-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.coops-tag-chip {
    background: #dff8ea;
    border-radius: 8px;
    color: #157347;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 4px 7px;
    text-transform: uppercase;
}

.coops-tag-chip--amber {
    background: #fff1db;
    color: #b66b0f;
}

.coops-view-link {
    color: #14532d;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.4;
    text-decoration: none;
}

.coops-view-link:hover {
    color: #0f6b4c;
}

.coops-table-footer {
    align-items: center;
    border-top: 1px solid #eef2f7;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    padding: 14px;
}

.coops-table-footer__copy {
    color: #94a3b8;
    font-size: 12px;
}

.coops-pagination {
    display: flex;
    gap: 8px;
}

.coops-pagination__button {
    align-items: center;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    color: #94a3b8;
    display: inline-flex;
    font-size: 12px;
    font-weight: 700;
    height: 34px;
    justify-content: center;
    min-width: 72px;
    padding: 0 12px;
}

.coops-pagination__button.is-active {
    background: #0f6b4c;
    border-color: #0f6b4c;
    color: #fff;
}

.coops-empty-state {
    padding: 24px 18px;
    text-align: center;
}

.coops-empty-state__title {
    color: #111827;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
}

.coops-empty-state__copy {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.6;
    margin: 0 auto;
    max-width: 420px;
}

.coops-bottom-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr);
    margin-top: 18px;
}

.coops-corridor-card {
    background: linear-gradient(180deg, #0b3f36 0%, #0d4c42 100%);
    border-radius: 16px;
    color: #fff;
    overflow: hidden;
    padding: 18px;
}

.coops-corridor-map {
    background:
        radial-gradient(circle at 42% 45%, rgba(255, 199, 122, 0.12), transparent 22%),
        radial-gradient(circle at 68% 58%, rgba(255, 255, 255, 0.08), transparent 18%),
        repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.04) 7px, transparent 7px, transparent 16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    height: 180px;
    margin-bottom: 14px;
}

.coops-corridor-title {
    font-size: 1.45rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-bottom: 8px;
}

.coops-corridor-copy {
    color: rgba(255, 255, 255, 0.82);
    font-size: 13px;
    line-height: 1.65;
    margin: 0;
}

.coops-compliance-card {
    padding: 18px;
}

.coops-compliance-kicker {
    color: #111827;
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.05;
    margin-bottom: 12px;
    max-width: 360px;
}

.coops-compliance-copy {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.7;
    margin: 0 0 16px;
}

.coops-compliance-metrics {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
}

.coops-compliance-value {
    color: #14532d;
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}

.coops-compliance-label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

@media (min-width: 768px) {
    .coops-directory-shell {
        padding: 12px 14px 30px;
    }

    .coops-stat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .coops-filter-grid {
        grid-template-columns: minmax(0, 1.5fr) minmax(160px, 0.75fr) minmax(180px, 0.75fr) 48px;
    }

    .coops-filter-button {
        width: 48px;
    }

    .coops-bottom-grid {
        align-items: stretch;
        grid-template-columns: minmax(0, 1fr) minmax(320px, 0.85fr);
    }
}
</style>
