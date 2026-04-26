<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@element-plus/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    batches: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({
            total_batches: 0,
            received_batches: 0,
            total_weight: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
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

const formatWeight = (value) => Number(value || 0).toLocaleString();
const formatMoisture = (value) => (value ? `${Number(value).toFixed(1)}%` : 'Pending');
const processLabel = (batch) => {
    const moisture = Number(batch.moisture_content || 0);

    if (!moisture) {
        return 'Pending';
    }

    if (moisture <= 11.5) {
        return 'Stable';
    }

    if (moisture <= 13) {
        return 'Review';
    }

    return 'Hold';
};
const scoreLabel = (batch) => {
    const moisture = Number(batch.moisture_content || 0);

    if (!moisture) {
        return 'Pending';
    }

    return Math.max(82, Math.min(94, 96 - moisture)).toFixed(1);
};
const statusClass = (status) => String(status || 'received').toLowerCase().replace(/\s+/g, '-');
const statusLabel = (status) => String(status || 'received').replace(/_/g, ' ');

const visiblePages = computed(() => {
    const currentPage = props.batches.meta.current_page || 1;
    const lastPage = props.batches.meta.last_page || 1;
    const start = Math.max(1, currentPage - 1);
    const end = Math.min(lastPage, currentPage + 1);
    const pages = [];

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    return pages;
});
const rangeSummary = computed(() => {
    const from = props.batches.meta.from || 0;
    const to = props.batches.meta.to || 0;
    const total = props.batches.meta.total || 0;

    return total > 0
        ? `Showing ${from} to ${to} of ${total} entries`
        : 'No entries recorded';
});
</script>

<template>
    <AppLayout title="Batch Directory" full-width>
        <div class="batch-registry-page">
            <section class="batch-registry-heading">
                <div>
                    <div class="batch-registry-kicker">Batch Directory</div>
                    <h1>Precision Coffee Asset Registry</h1>
                    <p>
                        A specialized view of certified coffee batches currently active within the Commodity Origin exchange system.
                    </p>
                </div>

                <div class="batch-registry-heading-action">
                    <el-button class="batch-registry-action-button" @click="router.visit(route('batch.create'))">
                        <el-icon><Plus /></el-icon>
                        <span>Create Batch</span>
                    </el-button>
                </div>
            </section>

            <section class="batch-registry-table-card">
                <el-table
                    :data="props.batches.data"
                    class="batch-registry-table"
                    empty-text="No batches have been recorded yet."
                    size="small"
                    stripe
                >
                    <el-table-column label="Batch ID" min-width="150" show-overflow-tooltip>
                        <template #default="{ row }">
                            <div class="batch-registry-code">{{ row.batch_number }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="Warehouse" min-width="220" show-overflow-tooltip>
                        <template #default="{ row }">
                            <div class="batch-registry-primary">{{ row.warehouse_location }}</div>
                            <div class="batch-registry-muted">{{ formatDate(row.created_at) }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="Bags" width="110">
                        <template #default="{ row }">
                            <div class="batch-registry-primary">{{ Number(row.quantity_bags || 0).toLocaleString() }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="Weight" min-width="150" align="right">
                        <template #default="{ row }">
                            <div class="batch-registry-primary">{{ formatWeight(row.net_weight_kg) }} kg</div>
                            <div class="batch-registry-muted">{{ formatMoisture(row.moisture_content) }} moisture</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="Quality" min-width="150">
                        <template #default="{ row }">
                            <el-tag class="batch-registry-process" effect="plain" size="small">
                                {{ processLabel(row) }}
                            </el-tag>
                            <div class="batch-registry-muted">Score {{ scoreLabel(row) }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="Status" min-width="140">
                        <template #default="{ row }">
                            <span class="batch-registry-status" :class="statusClass(row.status)">
                                {{ statusLabel(row.status) }}
                            </span>
                        </template>
                    </el-table-column>

                    <el-table-column label="Actions" width="110" align="right">
                        <template #default="{ row }">
                            <Link :href="row.show_url" class="batch-registry-action">View</Link>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="batch-registry-footer">
                    <div class="batch-registry-summary">{{ rangeSummary }}</div>

                    <div class="batch-registry-pagination">
                        <Link
                            :href="route('batch.index', { page: Math.max(1, batches.meta.current_page - 1), search: filters.search || undefined })"
                            class="batch-registry-page-button"
                            :class="{ disabled: batches.meta.current_page <= 1 }"
                        >
                            Previous
                        </Link>

                        <Link
                            v-for="page in visiblePages"
                            :key="page"
                            :href="route('batch.index', { page, search: filters.search || undefined })"
                            class="batch-registry-page-button"
                            :class="{ active: page === batches.meta.current_page }"
                        >
                            {{ page }}
                        </Link>

                        <Link
                            :href="route('batch.index', { page: Math.min(batches.meta.last_page, batches.meta.current_page + 1), search: filters.search || undefined })"
                            class="batch-registry-page-button"
                            :class="{ disabled: batches.meta.current_page >= batches.meta.last_page }"
                        >
                            Next
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.batch-registry-page {
    min-height: calc(100vh - 48px);
    background: #fbfcfc;
    color: #1f2a2a;
    padding: 8px 18px 32px;
}

.batch-registry-heading,
.batch-registry-table-card {
    max-width: 1180px;
    margin-inline: auto;
}

.batch-registry-kicker,
.batch-registry-summary {
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.batch-registry-kicker {
    color: #6b7280;
    font-size: 10px;
    font-weight: 800;
}

.batch-registry-heading h1 {
    margin: 8px 0 0;
    color: #123d2d;
    font-size: 20px;
    font-weight: 800;
    line-height: 1.2;
}

.batch-registry-heading p {
    max-width: 760px;
    margin: 8px 0 0;
    color: #657386;
    font-size: 14px;
    line-height: 1.6;
}

.batch-registry-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.batch-registry-action-button {
    min-height: 36px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.batch-registry-table-card {
    margin-top: 24px;
    overflow: hidden;
    border: 1px solid #e4e7e8;
    border-radius: 8px;
    background: #fff;
}

.batch-registry-table {
    width: 100%;
    --el-table-border-color: #edf0f1;
    --el-table-header-bg-color: #f6f8f8;
    --el-table-row-hover-bg-color: #f8fbf9;
}

.batch-registry-table :deep(.el-table__header th) {
    background: #f6f8f8;
    color: #7b8796;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.batch-registry-table :deep(.el-table__cell) {
    padding: 9px 0;
}

.batch-registry-table :deep(.cell) {
    line-height: 1.35;
}

.batch-registry-table :deep(.el-table__row) {
    height: 54px;
}

.batch-registry-table :deep(.el-table__row--striped .el-table__cell) {
    background: #fcfdfd;
}

.batch-registry-code {
    color: #003f2c;
    display: inline-flex;
    align-items: center;
    border-radius: 6px;
    background: #eef5f1;
    padding: 4px 8px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.03em;
}

.batch-registry-primary {
    color: #202828;
    font-size: 13px;
    font-weight: 700;
}

.batch-registry-muted {
    margin-top: 2px;
    color: #94a1b2;
    font-size: 11px;
}

.batch-registry-process {
    border-color: #d8dfdd;
    background: #f6f8f8;
    color: #283231;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.batch-registry-status {
    display: inline-flex;
    align-items: center;
    color: #647386;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.batch-registry-status::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    margin-right: 7px;
    border-radius: 999px;
    background: #cbd5df;
}

.batch-registry-status.received::before {
    background: #16a05d;
}

.batch-registry-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: #f0f7f3;
    color: #0e5b3f;
    font-size: 12px;
    font-weight: 800;
    padding: 6px 10px;
    text-decoration: none;
}

.batch-registry-action:hover {
    background: #e0efe7;
}

.batch-registry-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border-top: 1px solid #edf0f1;
    padding: 12px 16px;
}

.batch-registry-summary {
    color: #7b8796;
    font-size: 11px;
    font-weight: 800;
}

.batch-registry-pagination {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.batch-registry-page-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    border: 1px solid #e3e7e8;
    border-radius: 6px;
    background: #fff;
    color: #263232;
    font-size: 12px;
    font-weight: 700;
    padding: 0 12px;
    text-decoration: none;
}

.batch-registry-page-button.active {
    border-color: #00462f;
    background: #00462f;
    color: #fff;
}

.batch-registry-page-button.disabled {
    pointer-events: none;
    opacity: 0.45;
}

@media (max-width: 1180px) {
    .batch-registry-heading,
    .batch-registry-footer {
        flex-direction: column;
        align-items: stretch;
    }
}

@media (max-width: 720px) {
    .batch-registry-page {
        padding-inline: 0;
    }
}
</style>
