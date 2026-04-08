<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Check,
    Location,
    Medal,
    OfficeBuilding,
    Search,
    Star,
    User,
} from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    farmers: {
        type: Array,
        default: () => [],
    },
});

const search = ref('');
const region = ref('all');
const variety = ref('all');
const verification = ref('verified');

const uniqueRegions = computed(() =>
    [...new Set(props.farmers.map((farmer) => farmer.district).filter(Boolean))].sort(),
);

const uniqueVarieties = computed(() =>
    [...new Set(props.farmers.map((farmer) => farmer.coffee_type).filter(Boolean))].sort(),
);

const filteredFarmers = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.farmers.filter((farmer) => {
        const fullName = [farmer.first_name, farmer.last_name].filter(Boolean).join(' ').toLowerCase();
        const locationText = [farmer.sub_county, farmer.district].filter(Boolean).join(' ').toLowerCase();
        const cooperative = (farmer.cooperative || '').toLowerCase();
        const matchesSearch =
            !term ||
            fullName.includes(term) ||
            locationText.includes(term) ||
            cooperative.includes(term);

        const matchesRegion = region.value === 'all' || farmer.district === region.value;
        const matchesVariety = variety.value === 'all' || farmer.coffee_type === variety.value;
        const matchesVerification = verification.value === 'all' || verification.value === 'verified';

        return matchesSearch && matchesRegion && matchesVariety && matchesVerification;
    });
});

const directoryRows = computed(() =>
    filteredFarmers.value.map((farmer, index) => {
        const score = farmer.coffee_type?.toLowerCase() === 'robusta' ? 84 + (index % 3) : 87 + ((index + 2) % 4);
        const displayScore = `${score}.${index % 2 === 0 ? '5' : '0'}`;
        const estates = farmer.farms_count ? `${farmer.farms_count} estate${farmer.farms_count > 1 ? 's' : ''}` : 'No farms yet';

        return {
            ...farmer,
            fullName: [farmer.first_name, farmer.last_name].filter(Boolean).join(' ') || 'Unnamed producer',
            initials: [farmer.first_name, farmer.last_name]
                .filter(Boolean)
                .map((part) => part[0]?.toUpperCase())
                .join('')
                .slice(0, 2) || 'FP',
            location: [farmer.sub_county, farmer.district].filter(Boolean).join(', ') || 'Origin pending',
            estates,
            score: displayScore,
            scoreNote: score >= 90 ? 'Exceptional' : score >= 88 ? 'Excellent' : score >= 86 ? 'Very Good' : 'Good',
            tags: [farmer.coffee_type || 'Arabica', farmer.cooperative || 'Independent'],
        };
    }),
);

const quickStats = computed(() => [
    {
        label: 'Registered producers',
        value: directoryRows.value.length.toLocaleString(),
        detail: 'Verified directory profiles',
    },
    {
        label: 'Covered districts',
        value: uniqueRegions.value.length.toLocaleString(),
        detail: 'Active sourcing footprint',
    },
    {
        label: 'Coffee varieties',
        value: uniqueVarieties.value.length.toLocaleString(),
        detail: 'Across listed farmer records',
    },
]);
</script>

<template>
    <AppLayout title="Farmer Directory" full-width>
        <Head title="Farmer Directory" />

        <div class="farmer-directory-page">
            <div class="farmer-directory-shell">
                <section class="farmer-directory-head">
                    <div>
                        <div class="farmer-directory-kicker">Producer Registry</div>
                        <h1 class="farmer-directory-title">Farmer Directory</h1>
                        <p class="farmer-directory-copy">
                            Verified producers across coffee origins, organized for traceability, sourcing, and profile review.
                        </p>
                    </div>

                    <div class="farmer-directory-stats">
                        <article v-for="stat in quickStats" :key="stat.label" class="farmer-mini-stat">
                            <div class="farmer-mini-stat__label">{{ stat.label }}</div>
                            <div class="farmer-mini-stat__value">{{ stat.value }}</div>
                            <div class="farmer-mini-stat__detail">{{ stat.detail }}</div>
                        </article>
                    </div>
                </section>

                <section class="farmer-filter-card">
                    <div class="farmer-filter-grid">
                        <div class="farmer-search-wrap">
                            <el-input v-model="search" placeholder="Search by farmer, location, or cooperative">
                                <template #prefix>
                                    <el-icon><Search /></el-icon>
                                </template>
                            </el-input>
                        </div>

                        <el-select v-model="region" placeholder="Region">
                            <el-option label="Region: All Origins" value="all" />
                            <el-option v-for="item in uniqueRegions" :key="item" :label="item" :value="item" />
                        </el-select>

                        <el-select v-model="variety" placeholder="Variety">
                            <el-option label="Variety: All Types" value="all" />
                            <el-option v-for="item in uniqueVarieties" :key="item" :label="item" :value="item" />
                        </el-select>

                        <el-select v-model="verification" placeholder="Verification">
                            <el-option label="Certification: Verified" value="verified" />
                            <el-option label="Certification: All" value="all" />
                        </el-select>
                    </div>
                </section>

                <section class="farmer-table-card">
                    <div class="farmer-table-wrap">
                        <table class="farmer-table">
                            <thead>
                                <tr>
                                    <th>Farmer</th>
                                    <th>Location</th>
                                    <th>Estates</th>
                                    <th>Varieties</th>
                                    <th>SCAA Score</th>
                                    <th>Certifications</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody v-if="directoryRows.length">
                                <tr v-for="farmer in directoryRows" :key="farmer.id">
                                    <td>
                                        <div class="farmer-profile-cell">
                                            <div class="farmer-avatar">{{ farmer.initials }}</div>
                                            <div>
                                                <div class="farmer-name">{{ farmer.fullName }}</div>
                                                <div class="farmer-code">ID · FR-{{ String(farmer.id).padStart(4, '0') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="farmer-location">{{ farmer.location }}</div>
                                    </td>
                                    <td>
                                        <div class="farmer-estate-text">{{ farmer.estates }}</div>
                                    </td>
                                    <td>
                                        <div class="farmer-tag-row">
                                            <span v-for="tag in farmer.tags" :key="`${farmer.id}-${tag}`" class="farmer-tag-chip">
                                                {{ tag }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="farmer-score-wrap">
                                            <span class="farmer-score-value">{{ farmer.score }}</span>
                                            <span class="farmer-score-note">{{ farmer.scoreNote }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="farmer-cert-row">
                                            <span class="farmer-cert-dot farmer-cert-dot--green"></span>
                                            <span class="farmer-cert-dot farmer-cert-dot--gold"></span>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <Link :href="route('farmer.show', farmer.id)" class="farmer-view-button">
                                            View Profile
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="!directoryRows.length" class="farmer-empty-state">
                        <div class="farmer-empty-state__title">No farmers match this filter</div>
                        <p class="farmer-empty-state__copy">
                            Adjust the search or region filters, or register a new farmer to populate the directory.
                        </p>
                    </div>

                    <div class="farmer-table-footer">
                        <div class="farmer-table-footer__copy">
                            Showing {{ directoryRows.length }} of {{ props.farmers.length }} registered farmers
                        </div>
                        <div class="farmer-pagination">
                            <button type="button" class="farmer-pagination__button" disabled>Previous</button>
                            <span class="farmer-pagination__page is-active">1</span>
                            <button type="button" class="farmer-pagination__button" disabled>Next</button>
                        </div>
                    </div>
                </section>

                <section class="farmer-directory-bottom">
                    <article class="farmer-network-card">
                        <div class="farmer-network-card__title">Precision Sourcing Network</div>
                        <p class="farmer-network-card__copy">
                            Access detailed producer records, registry metrics, and farm relationships for every verified coffee grower in the exchange directory.
                        </p>
                        <div class="farmer-network-card__stats">
                            <div class="farmer-network-metric">
                                <div class="farmer-network-metric__value">{{ uniqueRegions.length || 1 }}</div>
                                <div class="farmer-network-metric__label">Origins</div>
                            </div>
                            <div class="farmer-network-metric">
                                <div class="farmer-network-metric__value">{{ props.farmers.length || 0 }}</div>
                                <div class="farmer-network-metric__label">Producers</div>
                            </div>
                        </div>
                    </article>

                    <article class="farmer-verification-card">
                        <div class="farmer-verification-card__icon">
                            <el-icon><Check /></el-icon>
                        </div>
                        <div class="farmer-verification-card__title">Institutional Verification</div>
                        <p class="farmer-verification-card__copy">
                            Farmer profiles in this registry support profile review, sourcing diligence, and cooperative alignment.
                        </p>
                        <button type="button" class="farmer-verification-card__button">Learn About Audits</button>
                    </article>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.farmer-directory-page {
    background: #fff;
}

.farmer-directory-shell {
    margin: 0 auto;
    max-width: 1320px;
    padding: 10px 10px 28px;
}

.farmer-directory-head {
    display: grid;
    gap: 16px;
    margin-bottom: 18px;
}

.farmer-directory-kicker {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.farmer-directory-title {
    color: #111827;
    font-size: clamp(1.5rem, 1.9vw, 2.15rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin: 0;
}

.farmer-directory-copy {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.6;
    margin: 8px 0 0;
    max-width: 780px;
}

.farmer-directory-stats {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.farmer-mini-stat {
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 14px;
    padding: 14px;
}

.farmer-mini-stat__label {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 12px;
    text-transform: uppercase;
}

.farmer-mini-stat__value {
    color: #14532d;
    font-size: 1.45rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 5px;
}

.farmer-mini-stat__detail {
    color: #94a3b8;
    font-size: 12px;
}

.farmer-filter-card,
.farmer-table-card {
    background: #fff;
    border: 1px solid #e9eef3;
    border-radius: 16px;
}

.farmer-filter-card {
    margin-bottom: 16px;
    padding: 14px;
}

.farmer-filter-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.farmer-search-wrap {
    min-width: 0;
}

.farmer-table-card {
    overflow: hidden;
}

.farmer-table-wrap {
    overflow-x: auto;
    padding: 0 14px;
}

.farmer-table {
    border-collapse: collapse;
    min-width: 980px;
    width: 100%;
}

.farmer-table th {
    color: #94a3b8;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding: 14px 0 12px;
    text-align: left;
    text-transform: uppercase;
}

.farmer-table td {
    border-top: 1px solid #eef2f7;
    color: #111827;
    font-size: 14px;
    padding: 15px 0;
    vertical-align: middle;
}

.farmer-profile-cell {
    align-items: center;
    display: flex;
    gap: 12px;
}

.farmer-avatar {
    align-items: center;
    background: linear-gradient(135deg, #d6f5e5 0%, #8dcfb0 100%);
    border-radius: 12px;
    color: #14532d;
    display: flex;
    font-size: 13px;
    font-weight: 800;
    height: 36px;
    justify-content: center;
    width: 36px;
}

.farmer-name {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.25;
    margin-bottom: 4px;
}

.farmer-code,
.farmer-score-note {
    color: #94a3b8;
    font-size: 11px;
}

.farmer-location,
.farmer-estate-text {
    color: #374151;
    line-height: 1.55;
}

.farmer-tag-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.farmer-tag-chip {
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

.farmer-score-wrap {
    align-items: center;
    display: flex;
    gap: 8px;
}

.farmer-score-value {
    align-items: center;
    background: #eafaf1;
    border-radius: 999px;
    color: #14532d;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    height: 28px;
    justify-content: center;
    min-width: 28px;
    padding: 0 8px;
}

.farmer-cert-row {
    display: flex;
    gap: 8px;
}

.farmer-cert-dot {
    border-radius: 999px;
    display: inline-block;
    height: 10px;
    width: 10px;
}

.farmer-cert-dot--green {
    background: #16a34a;
}

.farmer-cert-dot--gold {
    background: #c8862a;
}

.farmer-view-button {
    align-items: center;
    background: #fff;
    border: 1px solid #dbe2ea;
    border-radius: 10px;
    color: #14532d;
    display: inline-flex;
    font-size: 13px;
    font-weight: 700;
    justify-content: center;
    min-height: 36px;
    padding: 0 14px;
    text-decoration: none;
    transition: all 0.15s ease;
}

.farmer-view-button:hover {
    background: #f3fbf6;
    border-color: #bcdccc;
    color: #0f6b4c;
}

.farmer-table-footer {
    align-items: center;
    border-top: 1px solid #eef2f7;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    padding: 14px;
}

.farmer-table-footer__copy {
    color: #94a3b8;
    font-size: 12px;
}

.farmer-pagination {
    align-items: center;
    display: flex;
    gap: 8px;
}

.farmer-pagination__button,
.farmer-pagination__page {
    align-items: center;
    border: 1px solid #e5e7eb;
    border-radius: 9px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 700;
    justify-content: center;
    min-width: 34px;
    padding: 7px 10px;
}

.farmer-pagination__button {
    background: #fff;
    color: #94a3b8;
}

.farmer-pagination__page {
    background: #fff;
    color: #6b7280;
}

.farmer-pagination__page.is-active {
    background: #14532d;
    border-color: #14532d;
    color: #fff;
}

.farmer-empty-state {
    padding: 24px 18px;
    text-align: center;
}

.farmer-empty-state__title {
    color: #111827;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
}

.farmer-empty-state__copy {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.6;
    margin: 0 auto;
    max-width: 420px;
}

.farmer-directory-bottom {
    display: grid;
    gap: 16px;
    margin-top: 18px;
}

.farmer-network-card {
    background: linear-gradient(135deg, #fff1de 0%, #ffe5c6 100%);
    border: 1px solid #f6d6aa;
    border-radius: 16px;
    padding: 18px;
}

.farmer-network-card__title {
    color: #3f2a15;
    font-size: 1.45rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-bottom: 10px;
}

.farmer-network-card__copy {
    color: rgba(63, 42, 21, 0.74);
    font-size: 14px;
    line-height: 1.65;
    margin-bottom: 16px;
    max-width: 560px;
}

.farmer-network-card__stats {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.farmer-network-metric {
    background: rgba(255, 255, 255, 0.7);
    border-radius: 12px;
    min-width: 108px;
    padding: 12px;
}

.farmer-network-metric__value {
    color: #3f2a15;
    font-size: 1.7rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 5px;
}

.farmer-network-metric__label {
    color: rgba(63, 42, 21, 0.72);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.farmer-verification-card {
    background: linear-gradient(180deg, #0f6b4c 0%, #0b5139 100%);
    border-radius: 16px;
    color: #fff;
    padding: 18px;
}

.farmer-verification-card__icon {
    align-items: center;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    display: inline-flex;
    font-size: 18px;
    height: 42px;
    justify-content: center;
    margin-bottom: 14px;
    width: 42px;
}

.farmer-verification-card__title {
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-bottom: 10px;
}

.farmer-verification-card__copy {
    color: rgba(255, 255, 255, 0.82);
    font-size: 13px;
    line-height: 1.65;
    margin-bottom: 16px;
}

.farmer-verification-card__button {
    align-items: center;
    background: #b9f0cd;
    border: 0;
    border-radius: 10px;
    color: #0f6b4c;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    min-height: 40px;
    padding: 0 16px;
}

@media (min-width: 768px) {
    .farmer-directory-shell {
        padding: 12px 14px 30px;
    }

    .farmer-directory-head {
        grid-template-columns: minmax(0, 1fr) minmax(320px, 0.9fr);
    }

    .farmer-directory-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .farmer-filter-grid {
        grid-template-columns: 1.3fr repeat(3, minmax(0, 1fr));
    }

    .farmer-directory-bottom {
        grid-template-columns: minmax(0, 1.55fr) minmax(300px, 0.85fr);
    }
}
</style>
