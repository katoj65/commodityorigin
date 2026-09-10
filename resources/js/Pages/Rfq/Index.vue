<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TradeLayout from '@/Layouts/TradeLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Delete } from '@element-plus/icons-vue';

const props = defineProps({
    requests: { type: Array, default: () => [] },
    cropTypes: { type: Array, default: () => [] },
    grades: { type: Array, default: () => [] },
    marketCount: { type: Number, default: 0 },
    auctionCount: { type: Number, default: 0 },
    requestCount: { type: Number, default: 0 },
});

const fmt = (value, digits = 2) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
};

const statusTone = (status) => {
    switch (status) {
        case 'approved': return 'is-good';
        case 'fulfilled': return 'is-info';
        case 'rejected': return 'is-bad';
        default: return 'is-warn';
    }
};

const deleteOpen = ref(false);
const pendingDelete = ref(null);

function requestDelete(item) {
    pendingDelete.value = item;
    deleteOpen.value = true;
}

function confirmDelete() {
    if (!pendingDelete.value) return;
    router.delete(route('rfq.destroy', pendingDelete.value.id), { preserveScroll: true });
    pendingDelete.value = null;
}
</script>

<template>
    <TradeLayout
        title="RFQs"
        subtitle="Submit and track requests for coffee quotes."
        :market-count="marketCount"
        :auction-count="auctionCount"
        :request-count="requestCount"
        :crop-type-options="cropTypes"
        :grade-options="grades"
    >
        <div class="rfq-page">
            <section class="rfq-card">
                <div v-if="requests.length" class="rfq-table-wrap">
                    <table class="rfq-table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Grade</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in requests" :key="item.id">
                                <td>
                                    <span class="rfq-table__main">{{ item.crop_type }}</span>
                                    <span v-if="item.variety" class="rfq-table__sub">{{ item.variety }}</span>
                                </td>
                                <td>{{ item.grade || '—' }}</td>
                                <td>{{ fmt(item.quantity) }}</td>
                                <td>{{ item.amount ? fmt(item.amount) : '—' }}</td>
                                <td><span class="rfq-status" :class="statusTone(item.status)">{{ item.status }}</span></td>
                                <td>{{ item.user?.name || '—' }}</td>
                                <td class="rfq-table__actions">
                                    <button type="button" class="rfq-delete-btn" title="Delete" @click="requestDelete(item)">
                                        <el-icon><Delete /></el-icon>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="rfq-empty">No requests for quote yet.</p>
            </section>
        </div>

        <ConfirmDialog
            v-model="deleteOpen"
            eyebrow="Trade"
            title="Delete Request for Quote"
            :message="pendingDelete ? `Delete this ${pendingDelete.crop_type} request? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDelete"
        />
    </TradeLayout>
</template>

<style scoped>
.rfq-page { display: flex; flex-direction: column; gap: 20px; }

.rfq-card {
    background: var(--dp-surface);
    border: 1px solid var(--dp-outline-variant);
    border-radius: 8px;
    padding: 16px;
}
.rfq-table-wrap { overflow-x: auto; }
.rfq-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rfq-table th {
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--dp-on-surface-variant);
    padding: 8px 12px;
    border-bottom: 1px solid var(--dp-outline-variant);
}
.rfq-table td { padding: 11px 12px; border-bottom: 1px solid var(--dp-outline-variant); color: var(--dp-on-surface); }
.rfq-table tr:last-child td { border-bottom: none; }
.rfq-table__main { display: block; font-weight: 600; }
.rfq-table__sub { display: block; font-size: 11.5px; color: var(--dp-on-surface-variant); margin-top: 2px; }
.rfq-status {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 2px 8px;
    border-radius: 999px;
}
.rfq-status.is-good { color: #16A34A; background: #E9F9EE; }
.rfq-status.is-info { color: #1D4ED8; background: #EFF6FF; }
.rfq-status.is-bad { color: var(--dp-error); background: var(--dp-error-container); }
.rfq-status.is-warn { color: #92400E; background: #fef3c7; }
.rfq-table__actions { text-align: right; }
.rfq-delete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: var(--dp-on-surface-variant);
    cursor: pointer;
}
.rfq-delete-btn:hover { background: var(--dp-error-container); color: var(--dp-error); }
.rfq-empty { font-size: 13px; color: var(--dp-on-surface-variant); margin: 0; }
</style>

