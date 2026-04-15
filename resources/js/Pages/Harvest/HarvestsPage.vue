<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Filter, Plus, Search } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    harvests: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
    estateOptions: {
        type: Array,
        default: () => [],
    },
});

const visiblePages = computed(() => {
    const currentPage = props.harvests.meta.current_page || 1;
    const lastPage = props.harvests.meta.last_page || 1;
    const start = Math.max(1, currentPage - 1);
    const end = Math.min(lastPage, currentPage + 1);
    const pages = [];

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    return pages;
});

const hasHarvests = computed(() => props.harvests.data.length > 0);
const searchQuery = ref(props.filters.search ?? '');
const rangeSummary = computed(() => {
    const from = props.harvests.meta.from || 0;
    const to = props.harvests.meta.to || 0;
    const total = props.harvests.meta.total || 0;

    return `Showing ${from}-${to} of ${total} harvests`;
});

let searchDebounceId = null;

const visitHarvestIndex = (page = 1) => {
    router.get(
        route('harvest.index'),
        {
            page,
            search: searchQuery.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(searchQuery, () => {
    if (searchDebounceId) {
        window.clearTimeout(searchDebounceId);
    }

    searchDebounceId = window.setTimeout(() => {
        visitHarvestIndex(1);
    }, 250);
});

onBeforeUnmount(() => {
    if (searchDebounceId) {
        window.clearTimeout(searchDebounceId);
    }
});
</script>

<template>
    <AppLayout title="Harvests Directory">
        <Head title="Harvests Directory" />

        <div class="harvest-directory-page">
            <div class="harvest-directory-shell">
                <section class="harvest-directory-hero">
                    <div>
                        <div class="harvest-directory-eyebrow">Harvest Exchange</div>
                        <h1 class="harvest-directory-title">Harvests Directory</h1>
                        <p class="harvest-directory-copy">
                            Centralized oversight for all micro-lot processing, estate management, and seasonal quality tracking across your active harvest pipeline.
                        </p>
                    </div>

                    <div class="harvest-directory-log-button">
                        <el-button class="harvest-directory-log-el-button" size="small" @click="router.visit(route('harvest.create'))">
                            <el-icon><Plus /></el-icon>
                            <span>Log New Harvest</span>
                        </el-button>
                    </div>
                </section>

                <section class="harvest-directory-table-card">
                    <div class="harvest-directory-section-head">
                        <div>
                            <div class="harvest-directory-section-title">Harvest Registry</div>
                            <div class="harvest-directory-section-copy">
                                Review estate harvest records, quality movement, and processing readiness in one place.
                            </div>
                        </div>
                    </div>

                    <div class="harvest-directory-toolbar">
                        <el-input
                            v-model="searchQuery"
                            class="harvest-directory-search"
                            size="small"
                            placeholder="Search by harvest ID..."
                            :prefix-icon="Search"
                            clearable
                        />

                        <div class="harvest-directory-filter-wrap">
                            <el-select class="harvest-directory-select" size="small" placeholder="All Estates">
                                <el-option label="All Estates" value="" />
                                <el-option v-for="estate in estateOptions" :key="estate" :label="estate" :value="estate" />
                            </el-select>

                            <el-select class="harvest-directory-select" size="small" placeholder="Variety: All">
                                <el-option label="Variety: All" value="" />
                                <el-option label="Arabica" value="Arabica" />
                                <el-option label="Robusta" value="Robusta" />
                            </el-select>

                            <el-select class="harvest-directory-select" size="small" placeholder="Method: All">
                                <el-option label="Method: All" value="" />
                                <el-option label="Washed" value="Washed" />
                                <el-option label="Natural" value="Natural" />
                                <el-option label="Honey" value="Honey" />
                                <el-option label="Anaerobic" value="Anaerobic" />
                            </el-select>

                            <el-button class="harvest-directory-filter-button" size="small">
                                <el-icon><Filter /></el-icon>
                            </el-button>
                        </div>
                    </div>

                    <div class="harvest-directory-table-wrap">
                        <table class="harvest-directory-table">
                            <thead>
                                <tr>
                                    <th>Harvest ID</th>
                                    <th>Estate Name</th>
                                    <th>Variety</th>
                                    <th>Processing Method</th>
                                    <th>Yield (kg)</th>
                                    <th>SCAA Score</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody v-if="hasHarvests">
                                <tr v-for="harvest in harvests.data" :key="harvest.id">
                                    <td class="harvest-directory-code">H-{{ harvest.id }}</td>
                                    <td class="harvest-directory-estate">{{ harvest.estate_name }}</td>
                                    <td>
                                        <span class="harvest-directory-variety">{{ harvest.variety }}</span>
                                    </td>
                                    <td>{{ harvest.processing_method }}</td>
                                    <td>{{ Number(harvest.yield_kg).toLocaleString() }}</td>
                                    <td>{{ Number(harvest.scaa_score).toFixed(1) }}</td>
                                    <td>
                                        <span class="harvest-directory-status" :class="harvest.status_tone">{{ harvest.status }}</span>
                                    </td>
                                    <td>
                                        <Link :href="harvest.show_url" class="harvest-directory-action-link">Open</Link>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="8" class="harvest-directory-empty">
                                        No harvests have been recorded yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="harvest-directory-footer">
                        <div class="harvest-directory-summary">{{ rangeSummary }}</div>

                        <div class="harvest-directory-pagination">
                            <Link
                                :href="route('harvest.index', { page: Math.max(1, harvests.meta.current_page - 1), search: filters.search || undefined })"
                                class="harvest-directory-page-button"
                                :class="{ disabled: harvests.meta.current_page <= 1 }"
                            >
                                Previous
                            </Link>

                            <Link
                                v-for="page in visiblePages"
                                :key="page"
                                :href="route('harvest.index', { page, search: filters.search || undefined })"
                                class="harvest-directory-page-button"
                                :class="{ active: page === harvests.meta.current_page }"
                            >
                                {{ page }}
                            </Link>

                            <Link
                                :href="route('harvest.index', { page: Math.min(harvests.meta.last_page, harvests.meta.current_page + 1), search: filters.search || undefined })"
                                class="harvest-directory-page-button"
                                :class="{ disabled: harvests.meta.current_page >= harvests.meta.last_page }"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </section>

                <section class="harvest-directory-insights">
                    <article class="harvest-directory-insight-card dark">
                        <div class="harvest-directory-insight-eyebrow">Regional Quality Trends</div>
                        <h2>Regional Quality Trends</h2>
                        <p>
                            The harvest portfolio is showing stronger SCAA stability this season, with washed lots leading consistency across estate groups.
                        </p>
                        <button type="button">View Full Analysis</button>
                    </article>

                    <article class="harvest-directory-insight-card warm">
                        <div class="harvest-directory-insight-eyebrow">Efficiency Tip</div>
                        <h2>Optimizing Natural Lots</h2>
                        <p>
                            Natural method lots can reduce holding time when early drying interventions are scheduled within the first export prep window.
                        </p>
                        <a href="#" @click.prevent>Learn More</a>
                    </article>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.harvest-directory-page {
    background: #fff;
    padding-top: 6px;
}

.harvest-directory-shell {
    max-width: 1180px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.harvest-directory-hero,
.harvest-directory-toolbar,
.harvest-directory-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.harvest-directory-filter-wrap,
.harvest-directory-pagination,
.harvest-directory-insights {
    display: flex;
    gap: 14px;
}

.harvest-directory-eyebrow,
.harvest-directory-table th,
.harvest-directory-insight-eyebrow,
.harvest-directory-section-title {
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.harvest-directory-log-button,
.harvest-directory-insight-card button {
    text-decoration: none;
}

.harvest-directory-eyebrow {
    color: #9aa6a2;
    font-size: 10px;
    font-weight: 700;
}

.harvest-directory-title {
    margin-top: 8px;
    color: #192f28;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.1;
}

.harvest-directory-copy {
    margin-top: 8px;
    max-width: 620px;
    color: #6f7c78;
    font-size: 14px;
    line-height: 1.6;
}

.harvest-directory-log-button {
    display: inline-flex;
}

.harvest-directory-log-el-button {
    min-height: 32px;
    border-radius: 10px;
    padding: 0 12px;
    font-weight: 700;
}

.harvest-directory-table-card {
    border: 1px solid #e7ecea;
    border-radius: 18px;
    background: #fff;
    padding: 18px;
    box-shadow: 0 8px 26px rgba(15, 23, 42, 0.04);
}

.harvest-directory-section-head {
    margin-bottom: 14px;
}

.harvest-directory-section-title {
    color: #7f8b87;
    font-size: 10px;
    font-weight: 700;
}

.harvest-directory-section-copy {
    margin-top: 6px;
    color: #5f6e69;
    font-size: 13px;
    line-height: 1.6;
}

.harvest-directory-search {
    flex: 1;
    min-width: 240px;
}

.harvest-directory-search :deep(.el-input__wrapper),
.harvest-directory-select :deep(.el-select__wrapper),
.harvest-directory-filter-button {
    min-height: 38px;
    border-radius: 10px;
    background: #f6f8f7;
    box-shadow: none;
}

.harvest-directory-search :deep(.el-input__wrapper) {
    padding-left: 12px;
    padding-right: 12px;
}

.harvest-directory-select {
    min-width: 140px;
}

.harvest-directory-filter-button {
    border: 1px solid #e2e8e5;
    color: #60716b;
}

.harvest-directory-table-wrap {
    margin-top: 16px;
    overflow-x: auto;
}

.harvest-directory-table {
    width: 100%;
    border-collapse: collapse;
}

.harvest-directory-table th {
    padding: 12px 10px;
    color: #9aa6a2;
    font-size: 10px;
    font-weight: 700;
    text-align: left;
}

.harvest-directory-table td {
    padding: 16px 10px;
    border-top: 1px solid #edf2f0;
    color: #243934;
    font-size: 13px;
    vertical-align: middle;
}

.harvest-directory-code {
    color: #17322c;
    font-weight: 800;
}

.harvest-directory-estate {
    font-weight: 600;
}

.harvest-directory-variety {
    display: inline-flex;
    border-radius: 999px;
    background: #dcfce7;
    padding: 5px 9px;
    color: #0d7a4f;
    font-size: 11px;
    font-weight: 700;
}

.harvest-directory-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
}

.harvest-directory-status::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: currentColor;
}

.harvest-directory-status.processing { color: #c26b19; }
.harvest-directory-status.drying { color: #3b82f6; }
.harvest-directory-status.ready { color: #0f8a5f; }

.harvest-directory-action-link {
    color: #0d5b3f;
    font-weight: 700;
    text-decoration: none;
}

.harvest-directory-empty,
.harvest-directory-summary {
    color: #7d8985;
    font-size: 12px;
}

.harvest-directory-page-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    border: 1px solid #e6ece9;
    border-radius: 8px;
    padding: 8px 10px;
    color: #30403b;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    background: #fff;
}

.harvest-directory-page-button.active {
    background: #0d5b3f;
    border-color: #0d5b3f;
    color: #fff;
}

.harvest-directory-page-button.disabled {
    pointer-events: none;
    opacity: 0.5;
}

.harvest-directory-insight-card {
    flex: 1;
    border-radius: 18px;
    padding: 24px;
}

.harvest-directory-insight-card.dark {
    background:
        radial-gradient(circle at top right, rgba(18, 160, 121, 0.22), transparent 36%),
        linear-gradient(135deg, #0e6949 0%, #065f46 100%);
    color: #eafff7;
}

.harvest-directory-insight-card.warm {
    background: #ffd9b3;
    color: #4f3115;
}

.harvest-directory-insight-eyebrow {
    font-size: 10px;
    font-weight: 700;
    opacity: 0.75;
}

.harvest-directory-insight-card h2 {
    margin-top: 10px;
    font-size: 32px;
    font-weight: 800;
    line-height: 1.05;
}

.harvest-directory-insight-card p {
    margin-top: 10px;
    max-width: 460px;
    font-size: 13px;
    line-height: 1.7;
}

.harvest-directory-insight-card button {
    margin-top: 18px;
    border: 0;
    padding: 10px 14px;
}

.harvest-directory-insight-card a {
    display: inline-flex;
    margin-top: 18px;
    color: inherit;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    text-transform: uppercase;
}

@media (max-width: 1080px) {
    .harvest-directory-insights {
        grid-template-columns: 1fr;
        display: grid;
    }

    .harvest-directory-toolbar,
    .harvest-directory-hero,
    .harvest-directory-footer {
        flex-direction: column;
        align-items: stretch;
    }
}

@media (max-width: 800px) {
    .harvest-directory-filter-wrap {
        flex-wrap: wrap;
    }

    .harvest-directory-title {
        font-size: 20px;
    }
}
</style>
