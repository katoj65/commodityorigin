<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';
import {
    Box, Checked, Clock, Collection, DataAnalysis,
    Document, Edit, Files, GoodsFilled, Histogram, Medal,
    Money, MoreFilled, Star, TrendCharts, User, Delete,
} from '@element-plus/icons-vue';

const props = defineProps({
    lotRequest: { type: Object, required: true },
    canEdit:    { type: Boolean, default: false },
    canDelete:  { type: Boolean, default: false },
});

const editOpen   = ref(false);
const deleteOpen = ref(false);

const editForm = useForm({
    crop_type: '',
    variety:   '',
    grade:     '',
    quantity:  '',
    amount:    '',
    notes:     '',
});

function openEdit() {
    editForm.crop_type = req.value.crop_type  ?? '';
    editForm.variety   = req.value.variety    ?? '';
    editForm.grade     = req.value.grade      ?? '';
    editForm.quantity  = req.value.quantity   ?? '';
    editForm.amount    = req.value.amount     ?? '';
    editForm.notes     = req.value.notes      ?? '';
    editOpen.value = true;
}

function submitEdit() {
    editForm.put(route('lot.request.update', req.value.id), {
        onSuccess: () => { editOpen.value = false; },
    });
}

function confirmDelete() {
    useForm({}).delete(route('lot.request.destroy', req.value.id), {
        onSuccess: () => { deleteOpen.value = false; },
    });
}

function handleCommand(cmd) {
    if (cmd === 'edit')   openEdit();
    if (cmd === 'delete') deleteOpen.value = true;
}

const req = computed(() => props.lotRequest);

const statusConfig = {
    pending:    { label: 'Pending',    bg: '#FFF8F0', text: '#C8862A', border: 'rgba(200,134,42,0.4)' },
    processing: { label: 'Processing', bg: '#EFF6FF', text: '#2563EB', border: 'rgba(37,99,235,0.4)'  },
    approved:   { label: 'Approved',   bg: '#ECFDF5', text: '#1B6E4B', border: 'rgba(27,110,75,0.4)'  },
    rejected:   { label: 'Rejected',   bg: '#FEF2F2', text: '#C0392B', border: 'rgba(192,57,43,0.4)'  },
    fulfilled:  { label: 'Fulfilled',  bg: '#ECFDF5', text: '#2D7A55', border: 'rgba(45,122,85,0.4)'  },
};

const status = computed(() => statusConfig[req.value.status] ?? statusConfig.pending);

const statusPillStyle = computed(() => ({
    background:   status.value.bg,
    color:        status.value.text,
    border:       `1px solid ${status.value.border}`,
    borderRadius: '999px',
    fontSize:     '10px',
    fontWeight:   '700',
    letterSpacing:'0.05em',
    padding:      '2px 10px',
    textTransform:'uppercase',
    display:      'inline-flex',
    alignItems:   'center',
    gap:          '5px',
}));

const fmt = (val, decimals = 2) =>
    val != null ? Number(val).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : '—';

const fmtDate = (val) => val
    ? new Date(val).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
    : '—';

const cap = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : '—';

const daysSince = computed(() => {
    if (!req.value.created_at) return null;
    return Math.floor((Date.now() - new Date(req.value.created_at).getTime()) / 86400000);
});

const pricePerKg = computed(() => {
    const a = Number(req.value.amount), q = Number(req.value.quantity);
    return a && q ? '$' + fmt(a / q, 4) + '/kg' : null;
});

const timelineOrder = ['pending', 'processing', 'approved', 'fulfilled'];
const timelineStages = computed(() => {
    const s = req.value.status;
    const currentIdx = timelineOrder.indexOf(s === 'rejected' ? 'processing' : s);
    return [
        { key: 'pending',    label: 'Request Submitted',  date: fmtDate(req.value.created_at) },
        { key: 'processing', label: 'Under Review',        date: null },
        { key: 'approved',   label: 'Request Approved',   date: null },
        { key: 'fulfilled',  label: 'Order Fulfilled',    date: null },
    ].map((stage, i) => ({
        ...stage,
        done:     i < currentIdx,
        current:  timelineOrder[currentIdx] === stage.key && s !== 'rejected',
        rejected: s === 'rejected' && stage.key === 'processing',
    }));
});

const userInitials = computed(() => {
    const name = req.value.user?.name ?? '';
    return name.split(' ').filter(Boolean).slice(0, 2).map(p => p[0]?.toUpperCase()).join('') || '??';
});


</script>

<template>
    <AppLayout :title="`Lot Request #${req.id}`" :full-width="true" :flush="true" :show-banner="false">

        <div class="lr-root">

            <!-- ── Hero ─────────────────────────────────────────────────────── -->
            <section class="lp-hero">

                <div class="lp-hero__inner">

                    <!-- Left -->
                    <div class="lp-hero__left">
                        <div class="lp-tag-row">
                            <span class="lp-hero-tag">Buyer Request</span>
                            <span class="lp-hero-tag lp-hero-tag--warm">{{ cap(req.crop_type) }}</span>
                            <!-- Owner dropdown — same row as tags, pinned right -->
                            <div v-if="canEdit || canDelete" class="lr-hero-menu">
                                <el-dropdown trigger="click" @command="handleCommand">
                                    <button class="lr-menu-btn" aria-label="Request options">
                                        <el-icon><MoreFilled /></el-icon>
                                    </button>
                                    <template #dropdown>
                                        <el-dropdown-menu>
                                            <el-dropdown-item v-if="canEdit" command="edit">
                                                <el-icon><Edit /></el-icon> Edit Request
                                            </el-dropdown-item>
                                            <el-dropdown-item v-if="canDelete" command="delete" class="lr-dropdown-danger">
                                                <el-icon><Delete /></el-icon> Delete Request
                                            </el-dropdown-item>
                                        </el-dropdown-menu>
                                    </template>
                                </el-dropdown>
                            </div>
                        </div>
                        <h1 class="lp-hero__title">
                            Lot Request
                            <span class="lr-ref">#{{ req.id }}</span>
                        </h1>
                        <p class="lp-hero__sub">
                            <el-icon class="lp-sub-icon"><GoodsFilled /></el-icon>{{ cap(req.crop_type) }}
                            <span class="lp-sub-sep">&middot;</span>
                            <el-icon class="lp-sub-icon"><Box /></el-icon>{{ fmt(req.quantity, 0) }} kg
                            <span class="lp-sub-sep">&middot;</span>
                            <el-icon class="lp-sub-icon"><Clock /></el-icon>Submitted {{ fmtDate(req.created_at) }}
                            <span class="lp-sub-sep">&middot;</span>
                            {{ daysSince }}d ago
                        </p>
                    </div>

                    <!-- Right: Requester card -->
                    <!-- <div class="lp-hero__right">
                        <div class="lr-requester-hero">
                            <p class="lp-qr-label">Requester</p>
                            <div class="lr-hero-avatar">{{ userInitials }}</div>
                            <p class="lr-hero-name">{{ req.user?.name ?? '—' }}</p>
                            <p class="lr-hero-email">{{ req.user?.email ?? '—' }}</p>
                            <span :style="statusPillStyle" class="lr-hero-status-pill">{{ status.label }}</span>
                        </div>
                    </div> -->

                </div>

                <!-- Hero zones -->
                <div class="lp-hero__zones">
                    <!-- Zone 1: Request Identity -->
                    <div class="lp-hero-zone">
                        <div class="lp-zone-eyebrow"><el-icon><Files /></el-icon> REQUEST IDENTITY</div>
                        <div class="lp-zone-kv-list">
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Document /></el-icon> Reference</span>
                                <strong class="lr-mono">#{{ req.id }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><GoodsFilled /></el-icon> Crop Type</span>
                                <strong>{{ cap(req.crop_type) }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Clock /></el-icon> Submitted</span>
                                <strong>{{ fmtDate(req.created_at) }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Checked /></el-icon> Status</span>
                                <span :style="statusPillStyle">{{ status.label }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Zone 2: Crop Specification -->
                    <div class="lp-hero-zone">
                        <div class="lp-zone-eyebrow"><el-icon><Histogram /></el-icon> CROP SPECIFICATION</div>
                        <div class="lp-zone-kv-list">
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Star /></el-icon> Variety</span>
                                <strong>{{ cap(req.variety) || '—' }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Medal /></el-icon> Grade</span>
                                <strong>{{ req.grade ? req.grade.toUpperCase() : '—' }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Box /></el-icon> Quantity</span>
                                <strong>{{ fmt(req.quantity, 0) }} kg</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><TrendCharts /></el-icon> Price / kg</span>
                                <strong>{{ pricePerKg || '—' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Zone 3: Budget -->
                    <div class="lp-hero-zone">
                        <div class="lp-zone-eyebrow"><el-icon><Money /></el-icon> BUDGET</div>
                        <div class="lp-zone-kv-list">
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Money /></el-icon> Max Budget</span>
                                <strong class="lp-accent">{{ req.amount ? '$' + fmt(req.amount) : 'Open' }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><TrendCharts /></el-icon> Unit Price</span>
                                <strong>{{ pricePerKg || '—' }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Box /></el-icon> Volume</span>
                                <strong>{{ fmt(req.quantity, 0) }} kg</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><DataAnalysis /></el-icon> Flexibility</span>
                                <span class="lp-demand-chip">{{ req.amount ? 'FIXED' : 'OPEN' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Zone 4: Requester -->
                    <div class="lp-hero-zone">
                        <div class="lp-zone-eyebrow"><el-icon><User /></el-icon> REQUESTER</div>
                        <div class="lp-zone-kv-list">
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><User /></el-icon> Name</span>
                                <strong>{{ req.user?.name ?? '—' }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Collection /></el-icon> Role</span>
                                <strong>{{ cap(req.user?.role) }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Files /></el-icon> User ID</span>
                                <strong class="lr-mono">#{{ req.user_id }}</strong>
                            </div>
                            <div class="lp-zone-kv">
                                <span class="lp-zone-kv__label"><el-icon><Clock /></el-icon> Active Since</span>
                                <strong>{{ daysSince }}d</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Main Grid ─────────────────────────────────────────────────── -->
            <section class="lp-section lp-section--base p-0">
                <div class="lp-section__inner p-3">
                    <div class="lp-main-grid p-1">

                        <!-- LEFT COLUMN -->
                        <div class="lp-main-col">

                            <!-- Buyer Notes -->
                            <div class="lp-card">
                                <h2 class="lp-card__title mb-2"><el-icon><Document /></el-icon> Buyer Notes</h2>
                                <p v-if="req.notes" class="lr-notes">&ldquo;{{ req.notes }}&rdquo;</p>
                                <p v-else class="lr-notes-empty">No additional notes provided by the buyer.</p>
                            </div>

                            <!-- Procurement Timeline -->
                            <div class="lp-card">
                                <h2 class="lp-card__title mb-2"><el-icon><Clock /></el-icon> Procurement Timeline</h2>
                                <div class="lp-timeline">
                                    <div v-for="(stage, i) in timelineStages" :key="i"
                                         class="lp-tl-item"
                                         :class="[
                                             stage.rejected ? 'lp-tl-item--rejected' :
                                             stage.done     ? 'lp-tl-item--done'     :
                                             stage.current  ? 'lp-tl-item--current'  : 'lp-tl-item--pending'
                                         ]">
                                        <div v-if="i < timelineStages.length - 1" class="lp-tl-spine"></div>
                                        <div class="lp-tl-dot">
                                            <svg v-if="stage.done && !stage.rejected" class="lp-tl-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg>
                                            <svg v-else-if="stage.rejected" class="lp-tl-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                        </div>
                                        <div class="lp-tl-body">
                                            <strong>{{ stage.rejected ? 'Request Rejected' : stage.label }}</strong>
                                            <span>{{ stage.date || 'Pending' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /left -->

                        <!-- RIGHT COLUMN -->
                        <div class="lp-side-col">

                            <!-- Actions -->
                            <div class="lp-card lr-actions-card h-100">

                                <!-- Pending / Processing -->
                                <template v-if="req.status === 'pending' || req.status === 'processing'">
                                    <div class="lr-action-header">
                                        <div class="lr-action-header__icon lr-action-header__icon--review">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                        </div>
                                        <div>
                                            <div class="lr-action-header__title">Awaiting Decision</div>
                                            <div class="lr-action-header__sub">Review the request and take action below.</div>
                                        </div>
                                    </div>
                                    <div class="lr-action-divider"></div>
                                    <div class="lr-action-row lr-action-row--approve">
                                        <div class="lr-action-row__info">
                                            <div class="lr-action-row__label">Approve Request</div>
                                            <div class="lr-action-row__desc">Accept and proceed with procurement</div>
                                        </div>
                                        <button class="lp-btn lp-btn--primary lr-action-btn">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12l5 5L20 7"/></svg>
                                            Approve
                                        </button>
                                    </div>
                                    <div class="lr-action-row lr-action-row--reject">
                                        <div class="lr-action-row__info">
                                            <div class="lr-action-row__label lr-action-row__label--danger">Reject Request</div>
                                            <div class="lr-action-row__desc">Decline this procurement request</div>
                                        </div>
                                        <button class="lp-btn lp-btn--reject lr-action-btn" style="margin:10px;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                            Reject
                                        </button>
                                    </div>
                                </template>

                                <!-- Approved -->
                                <template v-else-if="req.status === 'approved'">
                                    <div class="lr-action-header">
                                        <div class="lr-action-header__icon lr-action-header__icon--approved">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12l5 5L20 7"/></svg>
                                        </div>
                                        <div>
                                            <div class="lr-action-header__title">Request Approved</div>
                                            <div class="lr-action-header__sub">Mark fulfilled once the order is completed.</div>
                                        </div>
                                    </div>
                                    <div class="lr-action-divider"></div>
                                    <div class="lr-action-row lr-action-row--approve">
                                        <div class="lr-action-row__info">
                                            <div class="lr-action-row__label">Mark as Fulfilled</div>
                                            <div class="lr-action-row__desc">Confirm order delivery is complete</div>
                                        </div>
                                        <button class="lp-btn lp-btn--primary lr-action-btn">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                            Fulfill
                                        </button>
                                    </div>
                                </template>

                                <!-- Fulfilled / Rejected / other -->
                                <template v-else>
                                    <div class="lr-action-header">
                                        <div class="lr-action-header__icon"
                                             :class="req.status === 'fulfilled' ? 'lr-action-header__icon--approved' : 'lr-action-header__icon--rejected'">
                                            <svg v-if="req.status === 'fulfilled'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12l5 5L20 7"/></svg>
                                            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                        </div>
                                        <div>
                                            <div class="lr-action-header__title">{{ status.label }}</div>
                                            <div class="lr-action-header__sub">No further actions available.</div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Always: Export -->
                                <div class="lr-action-divider"></div>
                                <div class="lr-export-wrap">
                                    <button class="lp-btn lp-btn--tertiary lr-export-btn">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        Export PDF
                                    </button>
                                </div>
                            </div>

                        </div><!-- /right -->
                    </div>
                </div>
            </section>

        </div>

        <!-- ── Edit Dialog ──────────────────────────────────────────────── -->
        <el-dialog v-model="editOpen" width="560px" :close-on-click-modal="false" :show-close="false" class="lr-edit-dialog">
            <!-- Custom header -->
            <template #header>
                <div class="lr-dialog-head">
                    <div class="lr-dialog-head__icon">
                        <el-icon><Edit /></el-icon>
                    </div>
                    <div class="lr-dialog-head__text">
                        <div class="lr-dialog-head__title">Edit Lot Request</div>
                        <div class="lr-dialog-head__sub">Ref <span class="lr-mono">#{{ req.id }}</span> · changes saved immediately</div>
                    </div>
                    <button class="lr-dialog-close" @click="editOpen = false" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>

            <el-form label-position="top" class="lr-dialog-form">

                <!-- Section: Crop -->
                <div class="lr-form-section-label">
                    <el-icon><GoodsFilled /></el-icon> Crop Details
                </div>
                <div class="lr-form-row">
                    <el-form-item label="Crop Type" required>
                        <el-input v-model="editForm.crop_type" placeholder="e.g. Coffee">
                            <template #prefix><el-icon><GoodsFilled /></el-icon></template>
                        </el-input>
                        <div v-if="editForm.errors.crop_type" class="lr-field-error">{{ editForm.errors.crop_type }}</div>
                    </el-form-item>
                    <el-form-item label="Variety">
                        <el-input v-model="editForm.variety" placeholder="e.g. Arabica">
                            <template #prefix><el-icon><Star /></el-icon></template>
                        </el-input>
                        <div v-if="editForm.errors.variety" class="lr-field-error">{{ editForm.errors.variety }}</div>
                    </el-form-item>
                </div>
                <div class="lr-form-row">
                    <el-form-item label="Grade">
                        <el-input v-model="editForm.grade" placeholder="e.g. AA">
                            <template #prefix><el-icon><Medal /></el-icon></template>
                        </el-input>
                        <div v-if="editForm.errors.grade" class="lr-field-error">{{ editForm.errors.grade }}</div>
                    </el-form-item>
                    <el-form-item label="Quantity (kg)" required>
                        <el-input v-model="editForm.quantity" type="number" placeholder="e.g. 500">
                            <template #prefix><el-icon><Box /></el-icon></template>
                            <template #suffix><span class="lr-input-unit">kg</span></template>
                        </el-input>
                        <div v-if="editForm.errors.quantity" class="lr-field-error">{{ editForm.errors.quantity }}</div>
                    </el-form-item>
                </div>

                <!-- Section: Budget -->
                <div class="lr-form-section-label">
                    <el-icon><Money /></el-icon> Budget
                </div>
                <el-form-item label="Max Budget">
                    <el-input v-model="editForm.amount" type="number" placeholder="Leave blank for open budget">
                        <template #prefix><span class="lr-input-unit lr-input-unit--prefix">$</span></template>
                        <template #suffix><span class="lr-input-unit">USD</span></template>
                    </el-input>
                    <div v-if="editForm.errors.amount" class="lr-field-error">{{ editForm.errors.amount }}</div>
                </el-form-item>

                <!-- Section: Notes -->
                <div class="lr-form-section-label">
                    <el-icon><Document /></el-icon> Additional Notes
                </div>
                <el-form-item label="">
                    <el-input v-model="editForm.notes" type="textarea" :rows="3"
                              placeholder="Describe any specific requirements, processing preferences, or delivery conditions…" />
                    <div v-if="editForm.errors.notes" class="lr-field-error">{{ editForm.errors.notes }}</div>
                </el-form-item>

            </el-form>

            <template #footer>
                <div class="lr-dialog-footer">
                    <SubmitButton :loading="editForm.processing" :full-width="false" native-type="button" @click="submitEdit">
                        Save Changes
                    </SubmitButton>
                </div>
            </template>
        </el-dialog>

        <!-- ── Delete Dialog ─────────────────────────────────────────────── -->
        <el-dialog v-model="deleteOpen" title="Delete Lot Request" width="420px" :close-on-click-modal="false">
            <div class="lr-delete-body">
                <div class="lr-delete-icon">
                    <el-icon><Delete /></el-icon>
                </div>
                <p class="lr-delete-title">Delete Request <span class="lr-mono">#{{ req.id }}</span>?</p>
                <p class="lr-delete-sub">This will permanently remove the lot request. This action cannot be undone.</p>
            </div>
            <template #footer>
                <div class="lr-dialog-footer">
                    <button class="lr-btn lr-btn--delete" @click="confirmDelete">
                        <el-icon><Delete /></el-icon> Delete Request
                    </button>
                </div>
            </template>
        </el-dialog>

    </AppLayout>
</template>

<style scoped>
/* ── Design tokens ──────────────────────────────────────────────────────────── */
.lr-root {
    --primary:            #004532;
    --primary-grad:       #065f46;
    --secondary:          #725a42;
    --on-primary:         #ffffff;
    --on-surface:         #191c1e;
    --on-surface-var:     #74777a;
    --surface:            #f7f9fb;
    --surface-low:        #f2f4f6;
    --surface-high:       #e6e8ea;
    --surface-highest:    #e0e3e5;
    --surface-white:      #ffffff;
    --primary-fixed:      #a6f2d1;
    --on-primary-fixed:   #002116;
    --secondary-fixed:    #fedcbe;
    --on-secondary-fixed: #291806;
    --outline-variant:    #bec9c2;
    --inner-pad:          2rem;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Owner menu ──────────────────────────────────────────────────────────── */
.lr-hero-menu { margin-left: auto; }
.lr-menu-btn {
    display: flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 8px;
    border: 1px solid var(--surface-high); background: var(--surface-white);
    color: var(--on-surface-var); cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
    font-size: 16px;
}
.lr-menu-btn:hover { background: var(--surface-low); border-color: var(--outline-variant); color: var(--on-surface); }

/* ── Hero ─────────────────────────────────────────────────────────────────── */
.lp-hero {
    background: #ffffff;
    border-bottom: 1px solid var(--surface-high);
    padding: 1.25rem 0 0;
}
.lp-hero__inner {
    padding: 0 var(--inner-pad) 2.25rem;
    display: flex;
    align-items: flex-start;
    gap: 2.5rem;
}
.lp-hero__left  { flex: 1; min-width: 0; }
.lp-hero__right { flex-shrink: 0; display: flex; flex-direction: column; align-items: center; }

.lp-tag-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1.25rem; }

.lp-hero-tag {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--surface-low); color: var(--on-surface);
    border: 1px solid var(--surface-high); border-radius: 999px;
    font-size: 10px; font-weight: 700; letter-spacing: 0.06em;
    padding: 4px 12px; text-transform: uppercase;
}
.lp-hero-tag--warm {
    background: var(--secondary-fixed);
    color: var(--on-secondary-fixed);
    border-color: transparent;
}

.lp-hero__title {
    font-size: 1.5rem; font-weight: 800;
    letter-spacing: -0.02em; line-height: 1.2;
    color: var(--on-surface); margin: 0 0 0.75rem;
}
.lr-ref {
    font-size: 1.25rem; font-weight: 400;
    color: var(--on-surface-var);
    font-family: 'IBM Plex Mono', monospace;
    margin-left: 4px;
}
.lr-mono {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.8125rem;
    color: var(--on-surface);
}

.lp-hero__sub {
    font-size: 0.9rem; color: var(--on-surface-var);
    margin: 0 0 1.75rem; line-height: 1.6;
    display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
}
.lp-sub-sep { color: var(--surface-highest); }

.lp-hero__actions { display: flex; flex-wrap: wrap; gap: 10px; }

/* Requester card in hero right */
.lr-requester-hero {
    background: var(--surface-low); border: 1px solid var(--surface-high);
    border-radius: 0.75rem; padding: 1.25rem;
    text-align: center; display: flex; flex-direction: column;
    align-items: center; gap: 6px; min-width: 160px;
}
.lr-hero-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary); font-size: 18px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin-top: 4px;
}
.lr-hero-name {
    font-size: 0.875rem; font-weight: 700; color: var(--on-surface);
    margin: 0; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.lr-hero-email {
    font-size: 0.6875rem; color: var(--on-surface-var);
    font-family: 'IBM Plex Mono', monospace; margin: 0;
    max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.lr-hero-status-pill { margin-top: 4px; }

/* QR label reuse */
.lp-qr-label {
    font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em;
    color: var(--on-surface-var); text-transform: uppercase; margin: 0;
}

/* Hero zones strip */
.lp-hero__zones {
    padding: 0 var(--inner-pad);
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    background: var(--surface-white);
    border-top: 1px solid var(--surface-high);
}
.lp-hero-zone {
    padding: 1.5rem; background: var(--surface-white);
}
.lp-hero-zone + .lp-hero-zone { border-left: 1px solid var(--surface-high); }

.lp-zone-eyebrow {
    font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.14em;
    color: var(--primary); text-transform: uppercase;
    margin-bottom: 1rem; display: flex; align-items: center; gap: 5px;
}
.lp-zone-eyebrow .el-icon { font-size: 13px; }
.lp-zone-kv__label .el-icon { font-size: 12px; opacity: 0.65; }
.lp-card__title .el-icon { font-size: 15px; color: var(--primary); }
.lp-zone-kv-list { display: grid; gap: 0; }
.lp-zone-kv {
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; font-size: 0.8125rem;
    padding: 7px 0; border-bottom: 1px solid var(--surface-low);
}
.lp-zone-kv:last-child { border-bottom: none; padding-bottom: 0; }
.lp-zone-kv:first-child { padding-top: 0; }
.lp-zone-kv__label {
    color: var(--on-surface-var); white-space: nowrap; flex-shrink: 0;
}
.lp-zone-kv strong { color: var(--on-surface); font-weight: 600; text-align: right; }

/* Origin tags */
.lp-origin-tag {
    background: var(--primary-fixed); color: var(--on-primary-fixed);
    border-radius: 999px; font-size: 10px; font-weight: 700;
    letter-spacing: 0.05em; padding: 4px 12px; text-transform: uppercase;
    display: inline-flex; align-items: center; gap: 4px;
}
.lp-accent { color: var(--primary) !important; }

.lp-demand-chip {
    background: var(--secondary-fixed); color: var(--on-secondary-fixed);
    font-size: 10px; font-weight: 800; letter-spacing: 0.06em;
    border-radius: 4px; padding: 2px 8px;
}

/* ── Buttons ─────────────────────────────────────────────────────────────── */
.lp-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem; font-weight: 700; border-radius: 0.375rem;
    padding: 9px 18px; border: none; cursor: pointer;
    transition: background 0.15s ease, opacity 0.15s ease;
    display: inline-flex; align-items: center; justify-content: center;
    gap: 6px; text-decoration: none;
}
.lp-btn--primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-grad) 100%);
    color: var(--on-primary);
}
.lp-btn--primary:hover { opacity: 0.9; }
.lp-btn--secondary {
    background: var(--secondary-fixed); color: var(--on-secondary-fixed);
}
.lp-btn--secondary:hover { opacity: 0.88; }
.lp-btn--tertiary {
    background: transparent; color: var(--primary);
}
.lp-btn--tertiary:hover { background: var(--surface-high); }
.lp-btn--reject {
    background: #FEF2F2; color: #C0392B; border: 1px solid #FECACA;
}
.lp-btn--reject:hover { background: #FEE2E2; }
.lp-btn--hero-ghost {
    background: transparent; color: var(--on-surface-var);
    border: 1px solid var(--outline-variant); border-radius: 0.375rem;
    font-size: 0.8125rem; font-weight: 700; padding: 9px 18px;
    cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    transition: color 0.15s, background 0.15s;
}
.lp-btn--hero-ghost:hover { color: var(--on-surface); background: var(--surface-low); }
.lp-btn--full { width: 100%; margin-top: 1rem; justify-content: center; }
.lp-btn--mt   { margin-top: 1.25rem; }

/* ── Sections ─────────────────────────────────────────────────────────────── */
.lp-section { padding: 2.5rem 0; }
.lp-section--base { background: var(--surface-white); border-top: 1px solid var(--surface-high); }
.lp-section__inner { padding: 0 var(--inner-pad); }

/* ── Main grid ────────────────────────────────────────────────────────────── */
.lp-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.85fr);
    gap: 2rem;
    align-items: stretch;
}
.lp-main-col { display: flex; flex-direction: column; gap: 2rem; }
.lp-side-col { display: flex; flex-direction: column; gap: 2rem; }

/* ── Cards ────────────────────────────────────────────────────────────────── */
.lp-card {
    background: #ffffff;
    border: 1px solid var(--surface-high);
    border-radius: 0.5rem;
    padding: 1.75rem;
}
.lp-card__title {
    font-size: 0.8125rem; font-weight: 700;
    color: var(--on-surface); margin: 0 0 1.5rem;
    display: flex; align-items: center; gap: 6px;
}
.lp-card__head-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem;
}
.lp-card__head-row .lp-card__title { margin-bottom: 0; }

/* ── KV rows ─────────────────────────────────────────────────────────────── */
.lp-kv-stack { display: grid; gap: 0; }
.lp-kv-stack--mt { margin-top: 1.25rem; }
.lp-kv-row {
    display: flex; justify-content: space-between; align-items: center;
    gap: 8px; font-size: 0.8125rem; padding: 10px 0;
    background: linear-gradient(var(--surface-low) 0, var(--surface-low) 100%) no-repeat left bottom / 100% 1px;
}
.lp-kv-row:last-child { background: none; }
.lp-kv-row span   { color: var(--on-surface-var); }
.lp-kv-row strong { color: var(--on-surface); font-weight: 600; text-align: right; }

/* ── Grade badge ─────────────────────────────────────────────────────────── */
.lr-grade-badge {
    display: inline-block;
    background: var(--surface-low); border: 1px solid var(--surface-high);
    border-radius: 4px; padding: 1px 8px;
    font-size: 11px; font-weight: 800;
    font-family: 'IBM Plex Mono', monospace;
    color: var(--on-surface);
}

/* ── Notes ────────────────────────────────────────────────────────────────── */
.lr-notes {
    font-size: 0.875rem; line-height: 1.75;
    color: var(--on-surface); font-style: italic; margin: 0;
    background: var(--surface-low); border-left: 3px solid var(--primary-fixed);
    padding: 12px 16px; border-radius: 0 6px 6px 0;
}
.lr-notes-empty {
    font-size: 0.8125rem; color: var(--on-surface-var); margin: 0;
}

/* ── Timeline ─────────────────────────────────────────────────────────────── */
.lp-timeline { display: grid; gap: 0; position: relative; }
.lp-tl-item {
    display: flex; align-items: flex-start; gap: 16px;
    padding-bottom: 1.5rem; position: relative;
}
.lp-tl-item:last-child { padding-bottom: 0; }
.lp-tl-dot {
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--surface-high); flex-shrink: 0;
    z-index: 1; margin-top: 1px;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--surface-high);
}
.lp-tl-item--done .lp-tl-dot {
    background: var(--primary-fixed);
    border-color: var(--primary);
}
.lp-tl-item--current .lp-tl-dot {
    background: #FFF8F0; border-color: #C8862A;
}
.lp-tl-item--rejected .lp-tl-dot {
    background: #FEF2F2; border-color: #C0392B;
}
.lp-tl-check { width: 9px; height: 9px; }
.lp-tl-item--done .lp-tl-check { stroke: var(--primary); }
.lp-tl-item--rejected .lp-tl-check { stroke: #C0392B; }
.lp-tl-spine {
    position: absolute; left: 9px; top: 22px; bottom: 0;
    width: 2px; background: var(--surface-high);
}
.lp-tl-item--done .lp-tl-spine { background: var(--primary-fixed); }
.lp-tl-body strong {
    display: block; font-size: 0.8125rem; font-weight: 700; color: var(--on-surface);
}
.lp-tl-body span { font-size: 0.75rem; color: var(--on-surface-var); }
.lp-tl-item--pending .lp-tl-body strong { color: var(--on-surface-var); }
.lp-tl-item--rejected .lp-tl-body strong { color: #C0392B; }

/* ── Seller / Requester ───────────────────────────────────────────────────── */
.lp-seller { display: flex; align-items: center; gap: 14px; margin-bottom: 1rem; }
.lp-seller-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: var(--on-primary); font-size: 18px; font-weight: 800;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.lp-seller-name { display: block; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.lp-seller-loc  { display: block; font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }

/* ── Action cards inside Actions panel ───────────────────────────────────── */
.lr-action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.lr-action-cell {
    border-radius: 0.5rem; padding: 1rem; text-align: left;
}
.lr-action-cell--approve { background: #f0faf5; }
.lr-action-cell--reject  { background: #fff9f5; }
.lr-action-desc { font-size: 0.75rem; color: var(--on-surface-var); margin: 0.375rem 0 0; }
.lp-trade-card__eyebrow {
    font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.12em;
    color: var(--on-surface-var); text-transform: uppercase;
}
.lr-no-action {
    background: var(--surface-low); color: var(--on-surface-var);
    border-radius: 6px; font-size: 0.8125rem; font-weight: 500;
    line-height: 1.6; padding: 12px 14px;
}
.lp-ai-callout {
    background: var(--primary-fixed); color: var(--on-primary-fixed);
    border-radius: 6px; font-size: 0.8125rem; font-weight: 600;
    line-height: 1.6; padding: 12px 14px;
}

/* ── Actions card ────────────────────────────────────────────────────────── */
.lr-actions-card { padding: 0; overflow: hidden; flex: 1; }

.lr-action-header {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 1.25rem 1.5rem;
}
.lr-action-header__icon {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.lr-action-header__icon--review   { background: #FFF8F0; color: #C8862A; }
.lr-action-header__icon--approved { background: #ECFDF5; color: #1B6E4B; }
.lr-action-header__icon--rejected { background: #FEF2F2; color: #C0392B; }
.lr-action-header__title {
    font-size: 0.875rem; font-weight: 700; color: var(--on-surface); line-height: 1.3;
}
.lr-action-header__sub {
    font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; line-height: 1.4;
}

.lr-action-divider { height: 1px; background: var(--surface-low); }

.lr-action-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; padding: 1rem 1.5rem;
}
.lr-action-row--approve { background: #fafffe; }
.lr-action-row--reject  { background: #fffafa; }
.lr-action-row__info    { min-width: 0; }
.lr-action-row__label {
    font-size: 0.8125rem; font-weight: 600; color: var(--on-surface);
}
.lr-action-row__label--danger { color: #C0392B; }
.lr-action-row__desc {
    font-size: 0.6875rem; color: var(--on-surface-var); margin-top: 2px;
}
.lr-action-btn {
    flex-shrink: 0; white-space: nowrap; margin-top: 0 !important; width: auto !important;
}
.lr-export-wrap { padding: 1rem 1.5rem; }
.lr-export-btn  { width: 100%; justify-content: center; }

/* ── Edit dialog header ───────────────────────────────────────────────────── */
.lr-dialog-head {
    display: flex; align-items: center; gap: 12px;
    width: 100%; padding: 0;
}
.lr-dialog-head__icon {
    width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
    background: #ECFDF5; color: #1B6E4B;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.lr-dialog-head__text  { flex: 1; min-width: 0; }
.lr-dialog-head__title { font-size: 1rem; font-weight: 700; color: var(--on-surface); line-height: 1.2; }
.lr-dialog-head__sub   { font-size: 0.75rem; color: var(--on-surface-var); margin-top: 2px; }
.lr-dialog-close {
    flex-shrink: 0; background: none; border: none; cursor: pointer;
    color: var(--on-surface-var); padding: 6px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s, color 0.15s;
}
.lr-dialog-close:hover { background: var(--surface-low); color: var(--on-surface); }

/* ── Edit dialog form ─────────────────────────────────────────────────────── */
.lr-dialog-form { display: flex; flex-direction: column; gap: 0; }
.lr-form-section-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--primary);
    margin: 1rem 0 0.5rem; padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--surface-low);
}
.lr-form-section-label:first-child { margin-top: 0; }
.lr-form-section-label .el-icon { font-size: 13px; }
.lr-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
.lr-field-error { font-size: 0.6875rem; color: #C0392B; margin-top: 4px; }
.lr-input-unit { font-size: 0.75rem; color: var(--on-surface-var); font-weight: 600; }
.lr-input-unit--prefix { font-weight: 700; color: var(--primary); }

/* ── Dialog footer buttons ────────────────────────────────────────────────── */
.lr-dialog-footer { display: flex; justify-content: flex-end; gap: 0.625rem; padding-top: 0.5rem; }
.lr-btn {
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600;
    padding: 0.5rem 1.125rem; cursor: pointer; border: none;
    font-family: 'Manrope', system-ui, sans-serif;
    transition: background 0.15s, opacity 0.15s;
}
.lr-btn--ghost {
    background: var(--surface-low); color: var(--on-surface-var);
    border: 1px solid var(--surface-high);
}
.lr-btn--ghost:hover { background: var(--surface-high); color: var(--on-surface); }
.lr-btn--save {
    background: linear-gradient(135deg, var(--primary), var(--primary-grad));
    color: #fff;
}
.lr-btn--save:hover:not(:disabled) { opacity: 0.9; }
.lr-btn--save:disabled { opacity: 0.55; cursor: not-allowed; }
.lr-btn--delete {
    background: #FEF2F2; color: #C0392B; border: 1px solid #FECACA;
}
.lr-btn--delete:hover { background: #FEE2E2; border-color: #C0392B; }

/* ── Spinner ─────────────────────────────────────────────────────────────── */
.lr-spinner { width: 13px; height: 13px; animation: lr-spin 0.7s linear infinite; }
@keyframes lr-spin { to { transform: rotate(360deg); } }

/* ── Delete dialog ───────────────────────────────────────────────────────── */
.lr-delete-body { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; padding: 1rem 0 0.5rem; text-align: center; }
.lr-delete-icon {
    width: 52px; height: 52px; border-radius: 50%;
    background: #FEF2F2; display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #C0392B;
}
.lr-delete-title { font-size: 1rem; font-weight: 700; color: var(--on-surface); margin: 0; }
.lr-delete-sub   { font-size: 0.8125rem; color: var(--on-surface-var); margin: 0; line-height: 1.6; max-width: 300px; }

/* ── Element Plus dialog overrides (teleported — use :global) ────────────── */
:global(.lr-edit-dialog .el-dialog__header) { padding: 1.25rem 1.5rem 1rem; border-bottom: 1px solid #f0f2f4; }
:global(.lr-edit-dialog .el-dialog__body)   { padding: 1.25rem 1.5rem; height: 450px; overflow-y: auto; overscroll-behavior: contain; }
:global(.lr-edit-dialog .el-dialog__body::-webkit-scrollbar)       { width: 5px; }
:global(.lr-edit-dialog .el-dialog__body::-webkit-scrollbar-track) { background: transparent; }
:global(.lr-edit-dialog .el-dialog__body::-webkit-scrollbar-thumb) { background: #e0e3e5; border-radius: 999px; }
:global(.lr-edit-dialog .el-dialog__body::-webkit-scrollbar-thumb:hover) { background: #bec9c2; }
:global(.lr-edit-dialog .el-dialog__footer) { padding: 0.75rem 1.5rem 1.25rem; border-top: 1px solid #f0f2f4; }
:global(.lr-edit-dialog .el-form-item__label) { font-size: 0.75rem; font-weight: 600; color: #374151; padding-bottom: 4px; }

/* ── Dropdown danger item (teleported outside scoped root — use :global) ── */
:global(.lr-dropdown-danger) { color: #C0392B !important; }
:global(.lr-dropdown-danger:hover) { background: #FEF2F2 !important; }
:global(.lr-dropdown-danger .el-icon) { color: #C0392B !important; }

/* ── Responsive ───────────────────────────────────────────────────────────── */
@media (max-width: 1200px) {
    .lp-hero__zones { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .lr-root { --inner-pad: 1.25rem; }
    .lp-hero { padding: 1rem 0 0; }
    .lp-hero__inner { gap: 1.25rem; padding-bottom: 1.5rem; }
    .lp-hero__title { font-size: 1.25rem; }
    .lp-main-grid { grid-template-columns: 1fr; }
    .lr-action-grid { grid-template-columns: 1fr; }
    .lp-section { padding: 2rem 0; }
}
@media (max-width: 640px) {
    .lr-root { --inner-pad: 1rem; }
    .lp-hero { padding: 1.25rem 0 0; }
    .lp-hero__inner { flex-direction: column; gap: 1rem; padding-bottom: 1.25rem; }
    .lp-hero__right { display: none; }
    .lp-hero__title { font-size: 1.25rem; margin-bottom: 0.5rem; }
    .lp-hero__actions { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 4px; scrollbar-width: none; }
    .lp-hero__actions::-webkit-scrollbar { display: none; }
    .lp-hero__zones { grid-template-columns: 1fr; }
    .lp-hero-zone + .lp-hero-zone { border-left: none; border-top: 1px solid var(--surface-high); }
    .lp-hero-zone { padding: 1rem; }
    .lp-tag-row { flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; padding-bottom: 2px; }
    .lp-tag-row::-webkit-scrollbar { display: none; }
    .lp-card { padding: 1.25rem; }
    .lp-card__title { margin-bottom: 1rem; }
    .lp-section { padding: 1.5rem 0; }
}
@media (max-width: 420px) {
    .lr-root { --inner-pad: 0.875rem; }
    .lp-hero__title { font-size: 1.125rem; }
    .lp-card { padding: 1rem; }
    .lp-main-col { gap: 1rem; }
    .lp-side-col { gap: 1rem; }
    .lp-main-grid { gap: 1rem; }
}
</style>
