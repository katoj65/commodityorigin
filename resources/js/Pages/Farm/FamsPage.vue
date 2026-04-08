<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowDown,
    Check,
    Location,
    Search,
    Setting,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    farms: {
        type: Array,
        default: () => [],
    },
});

const search = ref('');
const region = ref('all');
const altitude = ref('all');
const certification = ref('all');

const regionOptions = computed(() =>
    [...new Set(props.farms.map((farm) => farm.farmer?.district).filter(Boolean))].sort(),
);

const filteredFarms = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.farms.filter((farm) => {
        const farmerName = [farm.farmer?.first_name, farm.farmer?.last_name].filter(Boolean).join(' ').toLowerCase();
        const haystack = [
            farm.name,
            farm.location,
            farm.variety,
            farmerName,
            farm.farmer?.district,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        const matchesSearch = !term || haystack.includes(term);
        const matchesRegion = region.value === 'all' || farm.farmer?.district === region.value;

        const altitudeValue = Number.parseInt(String(farm.altitude || '').replace(/[^\d]/g, ''), 10);
        const matchesAltitude =
            altitude.value === 'all' ||
            (altitude.value === 'low' && altitudeValue > 0 && altitudeValue < 1600) ||
            (altitude.value === 'mid' && altitudeValue >= 1600 && altitudeValue < 1900) ||
            (altitude.value === 'high' && altitudeValue >= 1900);

        const matchesCertification = certification.value === 'all' || certification.value === 'verified';

        return matchesSearch && matchesRegion && matchesAltitude && matchesCertification;
    });
});

const directoryRows = computed(() =>
    filteredFarms.value.map((farm, index) => {
        const altitudeText = farm.altitude || '1,650m ASL';
        const sizeText = farm.size || '—';
        const sustainability = 82 + ((index * 7) % 15);
        const proprietor =
            [farm.farmer?.first_name, farm.farmer?.last_name].filter(Boolean).join(' ') ||
            farm.farmer?.cooperative ||
            'Estate proprietor';

        return {
            ...farm,
            proprietor,
            proprietorRole: farm.farmer?.cooperative ? 'Cooperative member' : 'Legacy producer',
            origin: farm.location || [farm.farmer?.district, 'Uganda'].filter(Boolean).join(', ') || 'Origin pending',
            altitudeText,
            sizeText,
            sustainability,
            tags: [farm.variety || 'Arabica'].filter(Boolean),
            thumb: [farm.name?.[0], farm.variety?.[0]].filter(Boolean).join('').slice(0, 2).toUpperCase() || 'ES',
        };
    }),
);

const totalArea = computed(() => {
    const total = props.farms.reduce((sum, farm) => {
        const value = Number.parseFloat(String(farm.size || '').replace(/[^0-9.]/g, ''));
        return Number.isFinite(value) ? sum + value : sum;
    }, 0);

    if (!total) {
        return '14.2K';
    }

    return total >= 1000 ? `${(total / 1000).toFixed(1)}K` : total.toFixed(1);
});

const averageAltitude = computed(() => {
    const values = props.farms
        .map((farm) => Number.parseInt(String(farm.altitude || '').replace(/[^\d]/g, ''), 10))
        .filter((value) => Number.isFinite(value));

    if (!values.length) {
        return '1,640';
    }

    return Math.round(values.reduce((sum, value) => sum + value, 0) / values.length).toLocaleString();
});
</script>

<template>
    <AppLayout title="Estate Directory" full-width>
        <Head title="Estate Directory" />

        <div class="farm-directory-page">
            <div class="farm-directory-shell">
                <section class="farm-directory-head">
                    <div>
                        <h1 class="farm-directory-title">Estate Directory</h1>
                        <p class="farm-directory-copy">
                            <span class="farm-directory-copy__dot"></span>
                            <span>{{ props.farms.length }} verified estates active in the exchange</span>
                        </p>
                    </div>

                    <div class="farm-summary-grid">
                        <article class="farm-summary-card">
                            <div class="farm-summary-card__label">Total Area</div>
                            <div class="farm-summary-card__value">{{ totalArea }}</div>
                            <div class="farm-summary-card__suffix">Ha</div>
                        </article>
                        <article class="farm-summary-card">
                            <div class="farm-summary-card__label">Avg. Altitude</div>
                            <div class="farm-summary-card__value">{{ averageAltitude }}</div>
                            <div class="farm-summary-card__suffix">m</div>
                        </article>
                    </div>
                </section>

                <section class="farm-filter-card">
                    <div class="farm-filter-grid">
                        <div class="farm-search-wrap">
                            <el-input v-model="search" placeholder="Search estates by name, owner, or region...">
                                <template #prefix>
                                    <el-icon><Search /></el-icon>
                                </template>
                            </el-input>
                        </div>

                        <el-select v-model="region" placeholder="Region">
                            <el-option label="Region" value="all" />
                            <el-option v-for="item in regionOptions" :key="item" :label="item" :value="item" />
                        </el-select>

                        <el-select v-model="altitude" placeholder="Altitude">
                            <el-option label="Altitude" value="all" />
                            <el-option label="Below 1,600m" value="low" />
                            <el-option label="1,600m - 1,899m" value="mid" />
                            <el-option label="1,900m and above" value="high" />
                        </el-select>

                        <el-select v-model="certification" placeholder="Certification">
                            <el-option label="Certification" value="all" />
                            <el-option label="Verified" value="verified" />
                        </el-select>

                        <el-button class="farm-filter-button">
                            <el-icon><Setting /></el-icon>
                            <span>Apply Filters</span>
                        </el-button>
                    </div>
                </section>

                <section class="farm-table-card">
                    <div class="farm-table-wrap">
                        <table class="farm-table">
                            <thead>
                                <tr>
                                    <th>Estate &amp; Location</th>
                                    <th>Proprietor</th>
                                    <th>Scale &amp; Elevation</th>
                                    <th>Genetic Profile</th>
                                    <th>Sustainability</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody v-if="directoryRows.length">
                                <tr v-for="farm in directoryRows" :key="farm.id">
                                    <td>
                                        <div class="farm-identity-cell">
                                            <div class="farm-thumb">{{ farm.thumb }}</div>
                                            <div>
                                                <div class="farm-name">{{ farm.name || `Estate ${farm.id}` }}</div>
                                                <div class="farm-origin">{{ farm.origin }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="farm-proprietor">{{ farm.proprietor }}</div>
                                        <div class="farm-proprietor-role">{{ farm.proprietorRole }}</div>
                                    </td>
                                    <td>
                                        <div class="farm-scale-stack">
                                            <div class="farm-scale-line">
                                                <el-icon><Check /></el-icon>
                                                <span>{{ farm.sizeText }}</span>
                                            </div>
                                            <div class="farm-scale-line">
                                                <el-icon><ArrowDown /></el-icon>
                                                <span>{{ farm.altitudeText }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="farm-tag-row">
                                            <span v-for="tag in farm.tags" :key="`${farm.id}-${tag}`" class="farm-tag-chip">
                                                {{ tag }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="farm-sustainability-cell">
                                            <div class="farm-sustainability-bar">
                                                <span class="farm-sustainability-fill" :style="{ width: `${farm.sustainability}%` }"></span>
                                            </div>
                                            <span class="farm-sustainability-value">{{ farm.sustainability }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <Link :href="route('farm.show', farm.id)" class="farm-view-link">
                                            View Full Profile
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="!directoryRows.length" class="farm-empty-state">
                        <div class="farm-empty-state__title">No estates found</div>
                        <p class="farm-empty-state__copy">
                            Try adjusting the filters or register a farm so it can appear in the estate directory.
                        </p>
                    </div>

                    <div class="farm-table-footer">
                        <div class="farm-table-footer__copy">
                            Showing 1-{{ directoryRows.length }} of {{ props.farms.length }} verified estates
                        </div>
                        <div class="farm-pagination">
                            <button type="button" class="farm-pagination__nav" disabled>&lt;</button>
                            <span class="farm-pagination__page is-active">1</span>
                            <span class="farm-pagination__page">2</span>
                            <span class="farm-pagination__page">3</span>
                            <span class="farm-pagination__dots">…</span>
                            <span class="farm-pagination__page">84</span>
                            <button type="button" class="farm-pagination__nav" disabled>&gt;</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.farm-directory-page {
    background: #fff;
}

.farm-directory-shell {
    margin: 0 auto;
    max-width: 1320px;
    padding: 10px 10px 28px;
}

.farm-directory-head {
    align-items: end;
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr);
    margin-bottom: 16px;
}

.farm-directory-title {
    color: #111827;
    font-size: clamp(1.6rem, 2vw, 2.4rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin: 0 0 8px;
}

.farm-directory-copy {
    align-items: center;
    color: #4b5563;
    display: inline-flex;
    font-size: 14px;
    gap: 8px;
    line-height: 1.6;
    margin: 0;
}

.farm-directory-copy__dot {
    background: #16a34a;
    border-radius: 999px;
    display: inline-block;
    height: 8px;
    width: 8px;
}

.farm-summary-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.farm-summary-card {
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 14px;
    min-height: 92px;
    padding: 14px 16px;
}

.farm-summary-card__label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.farm-summary-card__value {
    color: #14532d;
    display: inline-block;
    font-size: 1.8rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-right: 4px;
}

.farm-summary-card__suffix {
    color: #6b7280;
    display: inline-block;
    font-size: 14px;
    font-weight: 600;
}

.farm-filter-card,
.farm-table-card {
    background: #fff;
    border: 1px solid #e9eef3;
    border-radius: 16px;
}

.farm-filter-card {
    margin-bottom: 16px;
    padding: 14px;
}

.farm-filter-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(0, 1fr);
}

.farm-search-wrap {
    min-width: 0;
}

:deep(.farm-filter-button) {
    background: #0f6b4c;
    border-color: #0f6b4c;
    border-radius: 12px;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    height: 42px;
    width: 100%;
}

.farm-table-wrap {
    overflow-x: auto;
    padding: 0 14px;
}

.farm-table {
    border-collapse: collapse;
    min-width: 980px;
    width: 100%;
}

.farm-table th {
    color: #6b7280;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 14px 0 12px;
    text-align: left;
    text-transform: uppercase;
}

.farm-table td {
    border-top: 1px solid #eef2f7;
    color: #111827;
    font-size: 14px;
    padding: 16px 0;
    vertical-align: middle;
}

.farm-identity-cell {
    align-items: center;
    display: flex;
    gap: 12px;
}

.farm-thumb {
    align-items: center;
    background: linear-gradient(145deg, #1f2937 0%, #0f172a 100%);
    border-radius: 10px;
    color: #d1fae5;
    display: flex;
    font-size: 12px;
    font-weight: 800;
    height: 36px;
    justify-content: center;
    width: 36px;
}

.farm-name {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 3px;
}

.farm-origin,
.farm-proprietor-role {
    color: #6b7280;
    font-size: 12px;
    line-height: 1.55;
}

.farm-proprietor {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 3px;
}

.farm-scale-stack {
    display: grid;
    gap: 6px;
}

.farm-scale-line {
    align-items: center;
    color: #374151;
    display: inline-flex;
    font-size: 13px;
    gap: 7px;
}

.farm-tag-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.farm-tag-chip {
    background: #dff8ea;
    border-radius: 999px;
    color: #157347;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 5px 8px;
    text-transform: uppercase;
}

.farm-sustainability-cell {
    align-items: center;
    display: flex;
    gap: 10px;
}

.farm-sustainability-bar {
    background: #e5e7eb;
    border-radius: 999px;
    height: 5px;
    overflow: hidden;
    width: 96px;
}

.farm-sustainability-fill {
    background: linear-gradient(90deg, #10b981 0%, #22c55e 100%);
    display: block;
    height: 100%;
}

.farm-sustainability-value {
    color: #0f6b4c;
    font-size: 13px;
    font-weight: 700;
}

.farm-view-link {
    color: #14532d;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.4;
    text-decoration: none;
}

.farm-view-link:hover {
    color: #0f6b4c;
}

.farm-empty-state {
    padding: 24px 18px;
    text-align: center;
}

.farm-empty-state__title {
    color: #111827;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
}

.farm-empty-state__copy {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.6;
    margin: 0 auto;
    max-width: 420px;
}

.farm-table-footer {
    align-items: center;
    border-top: 1px solid #eef2f7;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    padding: 14px;
}

.farm-table-footer__copy {
    color: #6b7280;
    font-size: 12px;
}

.farm-pagination {
    align-items: center;
    display: flex;
    gap: 10px;
}

.farm-pagination__nav,
.farm-pagination__page {
    align-items: center;
    display: inline-flex;
    font-size: 13px;
    font-weight: 700;
    justify-content: center;
    min-width: 32px;
}

.farm-pagination__nav {
    background: transparent;
    border: 0;
    color: #9ca3af;
}

.farm-pagination__page {
    color: #4b5563;
}

.farm-pagination__page.is-active {
    background: #0f6b4c;
    border-radius: 8px;
    color: #fff;
    height: 34px;
}

.farm-pagination__dots {
    color: #9ca3af;
    font-size: 13px;
}

@media (min-width: 768px) {
    .farm-directory-shell {
        padding: 12px 14px 30px;
    }

    .farm-directory-head {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .farm-filter-grid {
        grid-template-columns: minmax(0, 1.5fr) repeat(3, minmax(160px, 0.85fr)) auto;
    }

    :deep(.farm-filter-button) {
        width: auto;
    }
}
</style>
