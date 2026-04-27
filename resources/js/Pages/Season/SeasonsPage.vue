<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Filter, Location, Plus, Reading, Search, Tickets } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    seasons: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    regionOptions: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref(props.filters.search ?? '');
const visiblePages = computed(() => {
    const currentPage = props.seasons.meta.current_page || 1;
    const lastPage = props.seasons.meta.last_page || 1;
    const start = Math.max(1, currentPage - 1);
    const end = Math.min(lastPage, currentPage + 1);
    const pages = [];

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    return pages;
});

const rangeSummary = computed(() => {
    const from = props.seasons.meta.from || 0;
    const to = props.seasons.meta.to || 0;
    const total = props.seasons.meta.total || 0;

    return `Showing ${from}-${to} of ${total} seasons`;
});

const formatDate = (value) => {
    if (!value) {
        return 'Pending';
    }

    return new Intl.DateTimeFormat('en-UG', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(value));
};

let searchDebounceId = null;

const visitSeasonIndex = (page = 1) => {
    router.get(
        route('season.index'),
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
        visitSeasonIndex(1);
    }, 250);
});

onBeforeUnmount(() => {
    if (searchDebounceId) {
        window.clearTimeout(searchDebounceId);
    }
});

const statusTagType = (status) => {
    switch (String(status).toLowerCase()) {
        case 'active':
            return 'success';
        case 'completed':
            return 'info';
        case 'archived':
            return 'warning';
        default:
            return '';
    }
};
</script>

<template>
    <AppLayout title="Seasons Directory">
        <Head title="Seasons Directory" />

        <div class="season-directory-page">
            <div class="season-directory-shell">
                <section class="season-directory-hero">
                    <div>
                        <div class="season-directory-eyebrow">Season Planning</div>
                        <h1 class="season-directory-title">Seasons Directory</h1>
                        <p class="season-directory-copy">
                            Track production windows, regional cycles, and coffee season planning in one place.
                        </p>
                    </div>

                    <div class="season-directory-log-button">
                        <el-button class="season-directory-log-el-button" size="small" @click="router.visit(route('season.create'))">
                            <el-icon><Plus /></el-icon>
                            <span>Add Season</span>
                        </el-button>
                    </div>
                </section>

                <section class="season-directory-table-card">
                    <div class="season-directory-section-head">
                        <div>
                            <div class="season-directory-section-title">Season Registry</div>
                            <div class="season-directory-section-copy">
                                Review all seasonal cycles, origin windows, and harvest planning periods.
                            </div>
                        </div>
                    </div>

                    <div class="season-directory-toolbar">
                        <el-input
                            v-model="searchQuery"
                            class="season-directory-search"
                            size="small"
                            placeholder="Search seasons..."
                            :prefix-icon="Search"
                            clearable
                        />

                        <div class="season-directory-filter-wrap">
                            <el-select class="season-directory-select" size="small" placeholder="All Regions">
                                <el-option label="All Regions" value="" />
                                <el-option v-for="region in regionOptions" :key="region" :label="region" :value="region" />
                            </el-select>

                            <el-select class="season-directory-select" size="small" placeholder="All Statuses">
                                <el-option label="All Statuses" value="" />
                                <el-option v-for="status in statusOptions" :key="status" :label="status" :value="status" />
                            </el-select>

                            <el-button class="season-directory-filter-button" size="small">
                                <el-icon><Filter /></el-icon>
                            </el-button>
                        </div>
                    </div>

                    <el-table :data="seasons.data" class="season-directory-table" empty-text="No seasons have been created yet.">
                        <el-table-column label="Season" min-width="200">
                            <template #default="{ row }">
                                <span class="season-table-cell">
                                    <el-icon><Tickets /></el-icon>
                                    <Link :href="route('season.show', row.id)" class="season-name-link">
                                        <strong>{{ row.name }}</strong>
                                    </Link>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Region" min-width="180">
                            <template #default="{ row }">
                                <span class="season-table-cell">
                                    <el-icon><Location /></el-icon>
                                    <span>{{ row.region }}</span>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Coffee Type" min-width="150">
                            <template #default="{ row }">
                                <span class="season-table-cell">
                                    <el-icon><Reading /></el-icon>
                                    <span>{{ row.coffee_type }}</span>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Start Date" min-width="150">
                            <template #default="{ row }">
                                <span class="season-table-cell">
                                    <el-icon><Calendar /></el-icon>
                                    <span>{{ formatDate(row.start_date) }}</span>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="End Date" min-width="150">
                            <template #default="{ row }">
                                <span class="season-table-cell">
                                    <el-icon><Calendar /></el-icon>
                                    <span>{{ formatDate(row.end_date) }}</span>
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Status" min-width="130">
                            <template #default="{ row }">
                                <el-tag :type="statusTagType(row.status)" effect="light" round>
                                    {{ row.status }}
                                </el-tag>
                            </template>
                        </el-table-column>

                        <el-table-column label="Notes" min-width="220" show-overflow-tooltip>
                            <template #default="{ row }">
                                <span class="season-notes-cell">{{ row.notes || 'No notes added.' }}</span>
                            </template>
                        </el-table-column>
                    </el-table>

                    <div class="season-directory-footer">
                        <div class="season-directory-summary">{{ rangeSummary }}</div>

                        <div class="season-directory-pagination">
                            <Link
                                :href="route('season.index', { page: Math.max(1, seasons.meta.current_page - 1), search: filters.search || undefined })"
                                class="season-directory-page-button"
                                :class="{ disabled: seasons.meta.current_page <= 1 }"
                            >
                                Previous
                            </Link>

                            <Link
                                v-for="page in visiblePages"
                                :key="page"
                                :href="route('season.index', { page, search: filters.search || undefined })"
                                class="season-directory-page-button"
                                :class="{ active: page === seasons.meta.current_page }"
                            >
                                {{ page }}
                            </Link>

                            <Link
                                :href="route('season.index', { page: Math.min(seasons.meta.last_page, seasons.meta.current_page + 1), search: filters.search || undefined })"
                                class="season-directory-page-button"
                                :class="{ disabled: seasons.meta.current_page >= seasons.meta.last_page }"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.season-directory-page {
    background: #fff;
    padding-top: 6px;
}

.season-directory-shell {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin: 0 auto;
    max-width: 1320px;
}

.season-directory-hero,
.season-directory-toolbar,
.season-directory-footer {
    align-items: center;
    display: flex;
    gap: 16px;
    justify-content: space-between;
}

.season-directory-filter-wrap,
.season-directory-pagination {
    display: flex;
    gap: 10px;
}

.season-directory-eyebrow,
.season-directory-section-title {
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.season-directory-eyebrow {
    color: #8a9692;
    font-size: 11px;
    font-weight: 700;
}

.season-directory-title {
    color: #16312b;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -0.05em;
    margin: 10px 0 0;
}

.season-directory-copy,
.season-directory-section-copy,
.season-directory-summary,
.season-notes-cell {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.6;
}

.season-directory-log-el-button {
    border-color: #0e5b3f;
    border-radius: 999px;
    color: #0e5b3f;
    font-weight: 700;
}

.season-directory-table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    padding: 20px;
}

.season-directory-section-head {
    margin-bottom: 14px;
}

.season-directory-section-title {
    color: #253b35;
    font-size: 12px;
    font-weight: 800;
}

.season-directory-search {
    max-width: 260px;
}

.season-directory-search :deep(.el-input__wrapper),
.season-directory-select :deep(.el-select__wrapper),
.season-directory-filter-button {
    border-radius: 12px;
    min-height: 40px;
}

.season-directory-filter-button {
    border: 1px solid #dbe1e6;
}

.season-directory-table {
    margin-top: 14px;
}

.season-directory-table :deep(.el-table__header th) {
    background: #f8fafc;
    color: #7f8d9b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.season-directory-table :deep(.el-table__cell) {
    padding: 14px 0;
}

.season-directory-table :deep(.el-table__inner-wrapper::before) {
    display: none;
}

.season-table-cell {
    align-items: center;
    color: #233242;
    display: inline-flex;
    gap: 8px;
}

.season-table-cell :deep(svg) {
    color: #6f847b;
    font-size: 15px;
}

.season-name-link {
    color: #16312b;
    text-decoration: none;
}

.season-name-link:hover {
    text-decoration: underline;
}

.season-directory-page-button {
    border: 1px solid #dde5e1;
    border-radius: 999px;
    color: #51615c;
    font-size: 12px;
    font-weight: 700;
    padding: 7px 12px;
    text-decoration: none;
}

.season-directory-page-button.active {
    background: #0e5b3f;
    border-color: #0e5b3f;
    color: #fff;
}

.season-directory-page-button.disabled {
    opacity: 0.5;
    pointer-events: none;
}

@media (max-width: 960px) {
    .season-directory-toolbar,
    .season-directory-hero,
    .season-directory-footer {
        align-items: stretch;
        flex-direction: column;
    }

    .season-directory-filter-wrap,
    .season-directory-pagination {
        flex-wrap: wrap;
    }

    .season-directory-search {
        max-width: none;
        width: 100%;
    }
}
</style>
