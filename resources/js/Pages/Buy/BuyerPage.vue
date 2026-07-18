<script setup>
import { reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ShoppingCart, Plus, Tickets, Box, Check, Close as CloseIcon,
} from '@element-plus/icons-vue';

const props = defineProps({
    myRequests: { type: Array, default: () => [] },
    openRequests: { type: Array, default: () => [] },
});

/* ── Submit a coffee request ─────────────────────────────────────────── */
const form = reactive({
    crop_type: '',
    variety: '',
    grade: '',
    quantity: null,
    amount: null,
    notes: '',
});
const submitting = ref(false);

function submitRequest() {
    if (!form.crop_type.trim() || !form.quantity) {
        ElMessage.warning('Coffee type and quantity are required.');
        return;
    }

    submitting.value = true;
    router.post(route('buy.store'), form, {
        preserveScroll: true,
        onSuccess: () => {
            form.crop_type = '';
            form.variety = '';
            form.grade = '';
            form.quantity = null;
            form.amount = null;
            form.notes = '';
        },
        onFinish: () => { submitting.value = false; },
    });
}

/* ── Respond to an open request ──────────────────────────────────────── */
const respondingId = ref(null);

function respond(lotRequest, status) {
    respondingId.value = lotRequest.id;
    router.post(route('buy.respond', lotRequest.id), { status }, {
        preserveScroll: true,
        onFinish: () => { respondingId.value = null; },
    });
}

/* ── Display helpers ──────────────────────────────────────────────────── */
const statusLabel = (s) => ({
    pending: 'Pending',
    approved: 'Approved',
    rejected: 'Rejected',
    fulfilled: 'Fulfilled',
}[s] ?? s ?? '—');

const statusCls = (s) => ({
    pending: 'byr-badge--amber',
    approved: 'byr-badge--green',
    rejected: 'byr-badge--red',
    fulfilled: 'byr-badge--blue',
}[s] ?? 'byr-badge--muted');

const fmtDate = (d) => (d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—');
const fmtQty = (q) => (q != null ? `${Number(q).toLocaleString()} kg` : '—');
const fmtAmt = (a) => (a != null ? `$${Number(a).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '—');
const cap = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '—');
</script>

<template>
    <AppLayout title="Buy Coffee" full-width flush :show-banner="false">
        <Head title="Buy Coffee" />

        <div class="byr-page">
            <div class="byr-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="py-3">
                        <div class="byr-kicker">Coffee Requests</div>
                        <h1 class="byr-title mb-0">Buy Coffee</h1>
                        <p class="byr-subtitle mb-0">Submit a coffee request, or browse and respond to open requests from other buyers.</p>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">
                <div class="row g-3">

                    <!-- ══════════════════════════════════════════════════
                         Left (col-md-8) — open requests table
                         ══════════════════════════════════════════════════ -->
                    <div class="col-12 col-md-8">
                        <div class="byr-section">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="background:#f8fafc;border-radius:8px 8px 0 0;">
                                <span class="byr-count-label"><span class="byr-count-num">{{ openRequests.length }}</span> open requests</span>
                            </div>

                            <div v-if="!openRequests.length" class="text-center py-5">
                                <el-icon style="font-size:2rem;color:#d1d5db;"><Box /></el-icon>
                                <p class="byr-muted mt-2 mb-0" style="font-size:.875rem;">No open requests right now — check back soon.</p>
                            </div>

                            <div v-else class="table-responsive">
                                <table class="table byr-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="min-width:190px;">Coffee</th>
                                            <th>Grade</th>
                                            <th class="text-end">Quantity</th>
                                            <th class="text-end">Budget</th>
                                            <th style="min-width:140px;">Requester</th>
                                            <th>Submitted</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="r in openRequests" :key="r.id" class="byr-table-row">
                                            <!-- Coffee picture + identity -->
                                            <td>
                                                <div class="byr-tbl-crop">
                                                    <div class="byr-tbl-crop__avatar">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="15" height="15" style="opacity:.55">
                                                            <path d="M3 9h13a3 3 0 0 1 3 3v1a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9Z" />
                                                            <path d="M19 10h1a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2h-1" />
                                                            <path d="M6 3c-.5.7-.5 1.3 0 2s.5 1.3 0 2" />
                                                            <path d="M10 3c-.5.7-.5 1.3 0 2s.5 1.3 0 2" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="byr-item-name">{{ cap(r.crop_type) }}</div>
                                                        <div class="byr-tbl-crop__sub">{{ r.variety ? cap(r.variety) : 'Any variety' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Grade -->
                                            <td>
                                                <span v-if="r.grade" class="byr-grade-pill">{{ cap(r.grade) }}</span>
                                                <span v-else class="byr-muted">—</span>
                                            </td>
                                            <!-- Quantity -->
                                            <td class="text-end">
                                                <span style="font-size:.8125rem;font-weight:700;color:#111827;">{{ fmtQty(r.quantity) }}</span>
                                            </td>
                                            <!-- Budget -->
                                            <td class="text-end">
                                                <span v-if="r.amount" style="font-size:.8125rem;font-weight:800;color:#004532;">{{ fmtAmt(r.amount) }}</span>
                                                <span v-else class="byr-muted">—</span>
                                            </td>
                                            <!-- Requester -->
                                            <td>
                                                <div v-if="r.user" class="byr-requester">
                                                    <div class="byr-requester__avatar">{{ (r.user.name ?? '?').charAt(0).toUpperCase() }}</div>
                                                    <div style="font-size:.8125rem;font-weight:600;color:#111827;">{{ r.user.name }}</div>
                                                </div>
                                                <span v-else class="byr-muted">—</span>
                                            </td>
                                            <!-- Date -->
                                            <td><span style="font-size:.8125rem;color:#6b7280;white-space:nowrap;">{{ fmtDate(r.created_at) }}</span></td>
                                            <!-- Status -->
                                            <td><span class="byr-badge" :class="statusCls(r.status)">{{ statusLabel(r.status) }}</span></td>
                                            <!-- Actions -->
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button
                                                        type="button"
                                                        class="btn byr-act-btn byr-act-btn--outline btn-sm"
                                                        :disabled="respondingId === r.id"
                                                        @click="respond(r, 'rejected')"
                                                    >
                                                        <el-icon><CloseIcon /></el-icon>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn byr-act-btn byr-act-btn--primary btn-sm"
                                                        :disabled="respondingId === r.id"
                                                        @click="respond(r, 'approved')"
                                                    >
                                                        <el-icon><Check /></el-icon> Approve
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ══════════════════════════════════════════════════
                         Right (col-md-4) — submit request + my requests
                         ══════════════════════════════════════════════════ -->
                    <div class="col-12 col-md-4">
                        <div class="byr-card">
                            <div class="byr-card-title mb-3"><el-icon class="byr-card-icon"><Plus /></el-icon> Submit a Coffee Request</div>

                            <div class="byr-field">
                                <label>Coffee Type</label>
                                <el-input v-model="form.crop_type" placeholder="e.g. Arabica" />
                            </div>
                            <div class="byr-field">
                                <label>Variety</label>
                                <el-input v-model="form.variety" placeholder="e.g. SL28 (optional)" />
                            </div>
                            <div class="byr-field">
                                <label>Grade</label>
                                <el-input v-model="form.grade" placeholder="e.g. AA (optional)" />
                            </div>
                            <div class="byr-field">
                                <label>Quantity (kg)</label>
                                <el-input-number v-model="form.quantity" :min="0.01" :precision="2" controls-position="right" style="width:100%" />
                            </div>
                            <div class="byr-field">
                                <label>Budget ($)</label>
                                <el-input-number v-model="form.amount" :min="0" :precision="2" controls-position="right" style="width:100%" />
                            </div>
                            <div class="byr-field">
                                <label>Notes</label>
                                <el-input v-model="form.notes" type="textarea" :rows="3" placeholder="Any additional detail for sellers…" />
                            </div>

                            <button type="button" class="btn byr-btn-primary w-100 mt-2" :disabled="submitting" @click="submitRequest">
                                <el-icon><ShoppingCart /></el-icon> {{ submitting ? 'Submitting…' : 'Submit Request' }}
                            </button>
                        </div>

                        <div class="byr-card mt-3">
                            <div class="byr-card-title mb-3"><el-icon class="byr-card-icon"><Tickets /></el-icon> My Requests</div>

                            <div v-if="!myRequests.length" class="byr-empty">You haven't submitted any coffee requests yet.</div>
                            <div v-for="r in myRequests" :key="r.id" class="byr-my-row">
                                <div>
                                    <div class="byr-item-name">{{ cap(r.crop_type) }}<span v-if="r.variety"> · {{ cap(r.variety) }}</span></div>
                                    <div class="byr-muted">{{ fmtQty(r.quantity) }}<span v-if="r.amount"> · {{ fmtAmt(r.amount) }}</span></div>
                                </div>
                                <span class="byr-badge" :class="statusCls(r.status)">{{ statusLabel(r.status) }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.byr-page {
    --green: #004532;
    --green-dark: #002e20;
    --border: #e5e7eb;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    font-family: 'Manrope', system-ui, sans-serif;
    background: #fff;
    color: var(--on-surface);
    min-height: 100%;
}
.byr-muted { color: var(--on-surface-var); font-size: .8125rem; }
.byr-item-name { font-size: .8125rem; font-weight: 700; color: var(--on-surface); }

.byr-header { background: #fff; border-bottom: 1px solid var(--border); }
.byr-kicker { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--green); margin-bottom: 2px; }
.byr-title { font-size: 1.125rem; font-weight: 800; letter-spacing: -.02em; }
.byr-subtitle { font-size: .8125rem; color: var(--on-surface-var); }

.byr-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.byr-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: .875rem; font-weight: 700; color: var(--on-surface); }
.byr-card-icon { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

.byr-field { margin-bottom: 12px; }
.byr-field label { display: block; font-size: .75rem; font-weight: 600; color: var(--on-surface-var); margin-bottom: 4px; }

.byr-btn-primary { background: var(--green); border-color: var(--green); color: #fff; border-radius: 8px; font-size: .8125rem; font-weight: 700; padding: 9px 16px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
.byr-btn-primary:hover { background: var(--green-dark); }
.byr-btn-primary:disabled { opacity: .6; }

.byr-empty { font-size: .8125rem; color: var(--on-surface-var); text-align: center; padding: .75rem 0; }

.byr-my-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--surface-low); }
.byr-my-row:last-child { border-bottom: none; }

/* ── Section / table ─────────────────────────────────────────────────── */
.byr-section { background: #fff; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
.byr-count-label { font-size: .8125rem; color: var(--on-surface-var); }
.byr-count-num { font-weight: 700; color: var(--on-surface); }

.byr-table thead th { background: var(--surface-low); font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom: 1px solid var(--border); white-space: nowrap; }
.byr-table tbody td { padding: 9px 12px; font-size: .8125rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.byr-table-row:last-child td { border-bottom: none; }
.byr-table-row:hover { background: var(--surface-low); }

.byr-tbl-crop { display: flex; align-items: center; gap: 10px; }
.byr-tbl-crop__avatar { width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface-low); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--green); }
.byr-tbl-crop__sub { font-size: .6875rem; color: var(--on-surface-var); margin-top: 1px; }

.byr-grade-pill { display: inline-flex; border-radius: 4px; font-size: .6875rem; font-weight: 700; padding: 2px 8px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

.byr-requester { display: flex; align-items: center; gap: 8px; }
.byr-requester__avatar { width: 26px; height: 26px; border-radius: 50%; background: var(--green); color: #fff; font-size: .6875rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

.byr-act-btn { font-size: .75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; border-radius: 6px; padding: 5px 10px; }
.byr-act-btn--outline { background: #fff; border: 1px solid var(--border); color: var(--on-surface); }
.byr-act-btn--outline:hover { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
.byr-act-btn--primary { background: var(--green); border: 1px solid var(--green); color: #fff; }
.byr-act-btn--primary:hover { background: var(--green-dark); }
.byr-act-btn:disabled { opacity: .6; }

.byr-badge { display: inline-flex; border-radius: 999px; font-size: .625rem; font-weight: 700; padding: 3px 9px; flex-shrink: 0; }
.byr-badge--green { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.byr-badge--amber { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.byr-badge--red { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.byr-badge--blue { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.byr-badge--muted { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }
</style>
