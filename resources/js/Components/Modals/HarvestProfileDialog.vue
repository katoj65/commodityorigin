<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Box, Calendar, TrendCharts, Coin, Collection,
    CircleCheck, WarningFilled, Close,
} from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    harvest: { type: Object, default: null },
    isAdmin: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const dialogVisible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

function close() {
    dialogVisible.value = false;
}

function viewFarm() {
    if (!props.harvest?.farm_id) return;
    close();
    router.visit(route('farm.show', props.harvest.farm_id));
}

const avatarPalette = [
    { bg: '#eef2ff', color: '#4338ca' },
    { bg: '#ecfdf5', color: '#047857' },
    { bg: '#fff7ed', color: '#c2410c' },
    { bg: '#fdf4ff', color: '#a21caf' },
    { bg: '#eff6ff', color: '#1d4ed8' },
    { bg: '#f0fdfa', color: '#0f766e' },
];

const avatarStyle = computed(() => {
    const seed = props.harvest?.farm_id ?? props.harvest?.id ?? 0;
    return avatarPalette[seed % avatarPalette.length];
});

const farmName = computed(() => props.harvest?.farm?.name || `Farm #${props.harvest?.farm_id ?? '—'}`);

function initials(name) {
    const parts = (name || '').trim().split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || 'F';
}

const harvestCode = computed(() => {
    if (!props.harvest) return '';
    const year = (props.harvest.harvest_date || '').slice(0, 4) || new Date().getFullYear();
    return `#${year}-EX${String(props.harvest.id).padStart(2, '0')}`;
});

const statusLabel = computed(() => {
    const status = props.harvest?.status;
    if (!status) return 'Pending';
    return status.charAt(0).toUpperCase() + status.slice(1);
});
const statusIsPending = computed(() => (props.harvest?.status || 'pending').toLowerCase() === 'pending');

function formatDate(value, withTime = false) {
    if (!value) return '—';
    const date = new Date(value.length === 10 ? `${value}T00:00:00` : value.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric',
        ...(withTime ? { hour: 'numeric', minute: '2-digit' } : {}),
    });
}

const weightLabel = computed(() => (props.harvest?.weight !== null && props.harvest?.weight !== undefined
    ? `${Number(props.harvest.weight).toLocaleString()} kg` : '—'));
const priceLabel = computed(() => (props.harvest?.price !== null && props.harvest?.price !== undefined
    ? `Shs. ${Number(props.harvest.price).toFixed(2)}` : '—'));
const ripenessLabel = computed(() => (props.harvest?.ripeness_percentage !== null && props.harvest?.ripeness_percentage !== undefined
    ? `${props.harvest.ripeness_percentage}%` : '—'));
const pickMethodLabel = computed(() => props.harvest?.pick_method || '—');

const recordedBy = computed(() => {
    if (!props.isAdmin) return '';
    const name = props.harvest?.creator?.name;
    return name ? ` by ${name}` : '';
});

const flags = computed(() => {
    if (!props.harvest) return [];
    return [
        { label: 'Foreign Matter', present: props.harvest.foreign_matter_present },
        { label: 'Pest Damage', present: props.harvest.pest_damage },
        { label: 'Disease Signs', present: props.harvest.disease_signs },
        { label: 'Visible Defects', present: props.harvest.visible_defects },
    ];
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        width="min(620px, calc(100vw - 2rem))"
        destroy-on-close
        align-center
        :show-close="false"
        class="hpv-modal"
    >
        <template #header>
            <div class="hpv-modal__head">
                <div class="hpv-modal__head-icon">
                    <el-icon :size="18"><Box /></el-icon>
                </div>
                <div class="hpv-modal__head-text">
                    <div class="hpv-modal__eyebrow">Harvest Record</div>
                    <div class="hpv-modal__title">Harvest {{ harvestCode }}</div>
                </div>
                <span class="hpv-status" :class="{ 'hpv-status--pending': statusIsPending }">{{ statusLabel }}</span>
                <button type="button" class="hpv-modal__close" aria-label="Close" @click="close">
                    <el-icon :size="14"><Close /></el-icon>
                </button>
            </div>
        </template>

        <div v-if="harvest" class="hpv-modal__body">
            <div class="hpv-origin">
                <div class="hpv-avatar" :style="{ background: avatarStyle.bg, color: avatarStyle.color }">{{ initials(farmName) }}</div>
                <div class="hpv-origin__text">
                    <div class="hpv-origin__name">{{ farmName }}</div>
                    <div class="hpv-origin__sub">{{ harvest.farm?.location || 'Location not set' }}</div>
                </div>
                <div class="hpv-origin__tags">
                    <el-tag v-if="harvest.variety" type="success" effect="plain" round size="small">{{ harvest.variety }}</el-tag>
                    <el-tag v-if="harvest.harvest_season" type="info" effect="plain" round size="small">{{ harvest.harvest_season }}</el-tag>
                </div>
            </div>

            <div class="hpv-section">
                <div class="hpv-section__title">Timeline</div>
                <div class="hpv-timeline">
                    <div class="hpv-timeline__node">
                        <span class="hpv-timeline__dot"></span>
                        <div class="hpv-timeline__text">
                            <div class="hpv-timeline__label">Planted</div>
                            <div class="hpv-timeline__date">{{ formatDate(harvest.date_planted) }}</div>
                        </div>
                    </div>
                    <div class="hpv-timeline__line"></div>
                    <div class="hpv-timeline__node hpv-timeline__node--end">
                        <span class="hpv-timeline__dot hpv-timeline__dot--filled"></span>
                        <div class="hpv-timeline__text">
                            <div class="hpv-timeline__label">Harvested</div>
                            <div class="hpv-timeline__date">{{ formatDate(harvest.harvest_date) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hpv-section">
                <div class="hpv-section__title">Yield &amp; Quality</div>
                <div class="hpv-stats">
                    <div class="hpv-stat">
                        <el-icon><Box /></el-icon>
                        <span class="hpv-stat__value">{{ weightLabel }}</span>
                        <span class="hpv-stat__label">Weight</span>
                    </div>
                    <div class="hpv-stat">
                        <el-icon><Coin /></el-icon>
                        <span class="hpv-stat__value">{{ priceLabel }}</span>
                        <span class="hpv-stat__label">Price / kg</span>
                    </div>
                    <div class="hpv-stat">
                        <el-icon><TrendCharts /></el-icon>
                        <span class="hpv-stat__value">{{ ripenessLabel }}</span>
                        <span class="hpv-stat__label">Ripeness</span>
                    </div>
                    <div class="hpv-stat">
                        <el-icon><Collection /></el-icon>
                        <span class="hpv-stat__value">{{ pickMethodLabel }}</span>
                        <span class="hpv-stat__label">Pick Method</span>
                    </div>
                </div>
            </div>

            <div class="hpv-section">
                <div class="hpv-section__title">Quality Indicators</div>
                <div class="hpv-flags">
                    <div v-for="flag in flags" :key="flag.label" class="hpv-flag" :class="flag.present ? 'hpv-flag--warn' : 'hpv-flag--ok'">
                        <el-icon><component :is="flag.present ? WarningFilled : CircleCheck" /></el-icon>
                        <span class="hpv-flag__label">{{ flag.label }}</span>
                        <strong class="hpv-flag__value">{{ flag.present ? 'Flagged' : 'Clean' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="hpv-modal__footer">
                <div class="hpv-modal__meta">
                    <el-icon><Calendar /></el-icon>
                    Recorded {{ formatDate(harvest?.created_at) }}{{ recordedBy }}
                </div>
                <div class="hpv-modal__actions">
                    <button type="button" class="hpv-btn-outline" :disabled="!harvest?.farm_id" @click="viewFarm">View Farm</button>
                    <button type="button" class="hpv-btn-primary" @click="close">Close</button>
                </div>
            </div>
        </template>
    </el-dialog>
</template>

<style>
/* Unscoped on purpose: <el-dialog> teleports its root to <body>, outside
   this component's own template output, so it never carries this SFC's
   scope attribute — a scoped (or :deep()) selector can never reach it.
   Class names are specific enough to avoid collisions. */
.el-dialog.hpv-modal {
    --el-dialog-padding-primary: 0;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 20, 15, 0.22);
    font-family: 'Manrope', system-ui, sans-serif;
}

.el-dialog.hpv-modal .el-dialog__header {
    padding: 0;
    margin: 0;
}

.el-dialog.hpv-modal .el-dialog__body {
    padding: 0;
}

.el-dialog.hpv-modal .el-dialog__footer {
    padding: 0;
}
</style>

<style scoped>
/* NOTE: <el-dialog> teleports its content to <body>, outside this
   component's DOM subtree, so CSS custom properties from the page do NOT
   cascade in. All colors below are literal hex values on purpose. */

.hpv-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
}

.hpv-modal__head-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 69, 50, 0.08);
    color: #004532;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.hpv-modal__head-text { flex: 1; min-width: 0; }

.hpv-modal__eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #004532;
    margin-bottom: 1px;
}

.hpv-modal__title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
}

.hpv-status {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #047857;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 999px;
    padding: 4px 10px;
    flex-shrink: 0;
    white-space: nowrap;
}
.hpv-status--pending {
    color: #6b7280;
    background: #f3f4f6;
    border-color: #e5e7eb;
}

.hpv-modal__close {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s;
}

.hpv-modal__close:hover { background: #e5e7eb; color: #111827; }

.hpv-modal__body {
    padding: 22px 24px 6px;
    max-height: 70vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 22px;
}

/* ── Origin ────────────────────────────────────────────────────────── */
.hpv-origin {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: #fafbfc;
    border: 1px solid #f3f4f6;
    border-radius: 12px;
}
.hpv-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 800;
    flex-shrink: 0;
}
.hpv-origin__text { flex: 1; min-width: 0; }
.hpv-origin__name { font-size: 0.9375rem; font-weight: 700; color: #111827; line-height: 1.3; }
.hpv-origin__sub { font-size: 0.75rem; color: #6b7280; line-height: 1.3; margin-top: 1px; }
.hpv-origin__tags { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }

/* ── Sections ──────────────────────────────────────────────────────── */
.hpv-section__title {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #9ca3af;
    margin-bottom: 10px;
}

/* ── Timeline ──────────────────────────────────────────────────────── */
.hpv-timeline { display: flex; align-items: center; }
.hpv-timeline__node { display: flex; align-items: center; gap: 10px; }
.hpv-timeline__dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #d1d5db;
    flex-shrink: 0;
}
.hpv-timeline__dot--filled { background: #004532; border-color: #004532; }
.hpv-timeline__line {
    flex: 1;
    height: 2px;
    background: repeating-linear-gradient(90deg, #d1d5db 0 6px, transparent 6px 12px);
    margin: 0 10px;
}
.hpv-timeline__label { font-size: 0.6875rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; }
.hpv-timeline__date { font-size: 0.8125rem; font-weight: 700; color: #111827; margin-top: 1px; }
.hpv-timeline__node--end .hpv-timeline__text { text-align: right; }

/* ── Stats ─────────────────────────────────────────────────────────── */
.hpv-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
}
.hpv-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 8px;
    background: #fafbfc;
    border: 1px solid #f3f4f6;
    border-radius: 12px;
    text-align: center;
}
.hpv-stat :deep(.el-icon) { font-size: 16px; color: #004532; }
.hpv-stat__value { font-size: 0.875rem; font-weight: 800; color: #111827; line-height: 1.2; }
.hpv-stat__label { font-size: 0.625rem; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.04em; }

/* ── Quality flags ─────────────────────────────────────────────────── */
.hpv-flags {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}
.hpv-flag {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid;
}
.hpv-flag--ok { background: #ecfdf5; border-color: #d1fae5; color: #047857; }
.hpv-flag--warn { background: #fef2f2; border-color: #fee2e2; color: #dc2626; }
.hpv-flag :deep(.el-icon) { font-size: 15px; flex-shrink: 0; }
.hpv-flag__label { font-size: 0.75rem; font-weight: 600; color: #374151; flex: 1; }
.hpv-flag__value { font-size: 0.6875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; }

/* ── Footer ────────────────────────────────────────────────────────── */
.hpv-modal__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
}
.hpv-modal__meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: #9ca3af;
}
.hpv-modal__meta :deep(.el-icon) { font-size: 13px; }
.hpv-modal__actions { display: flex; gap: 10px; flex-shrink: 0; }

.hpv-btn-primary {
    background: linear-gradient(135deg, #004532, #065f46);
    border: 1px solid transparent;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: opacity 0.15s ease;
}
.hpv-btn-primary:hover { opacity: 0.9; }

.hpv-btn-outline {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #111827;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    padding: 9px 18px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.hpv-btn-outline:hover { background: #f8fafc; }
.hpv-btn-outline:disabled { opacity: 0.5; cursor: default; }

@media (max-width: 560px) {
    .hpv-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .hpv-flags { grid-template-columns: 1fr; }
    .hpv-origin { flex-wrap: wrap; }
    .hpv-origin__tags { flex-direction: row; align-items: center; }
    .hpv-modal__footer { flex-direction: column; align-items: stretch; }
    .hpv-modal__actions { justify-content: flex-end; }
}
</style>
