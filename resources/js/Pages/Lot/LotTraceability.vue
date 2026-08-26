<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';

const props = defineProps({
    lot:    { type: Object, default: () => ({}) },
    batch:  { type: Object, default: () => ({}) },
});

const fmt = (v, d = 0) => Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });

const lotNumber     = computed(() => props.lot.lot_number    || 'LOT-2026-001');
const lotName       = computed(() => props.lot.lot_name      || 'Bugisu Premium Export Lot');
const lotStatus     = computed(() => props.lot.status        || 'Active');
const coffeeType    = computed(() => props.batch?.variety    || 'Arabica – Bourbon');
const process       = computed(() => props.lot.process       || 'Washed');
const grade         = computed(() => props.lot.grade         || 'AA');
const netWeight     = computed(() => fmt(props.lot.net_weight_kg, 2));
const qualityScore  = computed(() => Number(props.lot.quality_score || 87.5));
const batchNumber   = computed(() => props.batch?.batch_number || 'BCH-2025-042');
const warehouse     = computed(() => props.batch?.warehouse_location || 'Kampala Dry Mill');

const timeline = computed(() => [
    {
        stage: 'Batch',
        icon: '⚙️',
        color: 'blue',
        done: true,
        title: batchNumber.value,
        code: batchNumber.value,
        fields: [
            { label: 'Batch ID',           value: batchNumber.value },
            { label: 'Processing Method',  value: props.batch?.processing_method || 'Washed' },
            { label: 'Drying Method',      value: 'Raised Bed' },
            { label: 'Moisture Content',   value: (props.batch?.moisture_content || 11.2) + '%' },
            { label: 'Batch Quantity',     value: fmt(props.batch?.weight, 2) + ' kg' },
        ],
    },
    {
        stage: 'Lot',
        icon: '📦',
        color: 'primary',
        done: true,
        current: true,
        title: lotNumber.value,
        code: lotNumber.value,
        fields: [
            { label: 'Lot ID',           value: lotNumber.value },
            { label: 'Lot Quantity',     value: netWeight.value + ' kg' },
            { label: 'Quality Score',    value: qualityScore.value.toFixed(1) + ' SCA' },
            { label: 'Packaging Type',   value: props.lot.packaging_type || 'GrainPro' },
            { label: 'Export Readiness', value: '83%' },
        ],
    },
    {
        stage: 'Market',
        icon: '🏪',
        color: 'teal',
        done: false,
        title: 'Market Listing',
        code: 'MKT-2026-ACTIVE',
        fields: [
            { label: 'Listing Date',       value: props.lot.created_at || '01 Mar 2026' },
            { label: 'Current Status',     value: lotStatus.value },
            { label: 'Buyer Interest',     value: 'High' },
            { label: 'Market Value',       value: 'Shs. ' + fmt(props.lot.price_per_kg, 2) + '/kg' },
        ],
    },
]);

const ownershipHistory = [
    { role: 'Farmer',      org: 'John Wamala',         date: '01 Mar 2025', done: true  },
    { role: 'Cooperative', org: 'Bugisu Cooperative',  date: '15 Aug 2025', done: true  },
    { role: 'Processor',   org: 'Uganda Coffee Mill',  date: '20 Sep 2025', done: true  },
    { role: 'Exporter',    org: 'Origin Exports Ltd',  date: '01 Feb 2026', done: true  },
    { role: 'Buyer',       org: 'Pending',             date: '—',           done: false },
];

const documents = [
    { name: 'Collection Report',    icon: '📋', available: true  },
    { name: 'Batch Report',         icon: '📄', available: true  },
    { name: 'Lot Report',           icon: '📑', available: true  },
    { name: 'Sustainability Report', icon: '🌿', available: true  },
    { name: 'Quality Certificate',  icon: '🏅', available: true  },
    { name: 'Export Permit',        icon: '📜', available: false },
];

const sustainabilityScore = ref(78);
const fabOpen = ref(false);

const stageColor = (c) => ({
    green:   'lt-stage--green',
    amber:   'lt-stage--amber',
    blue:    'lt-stage--blue',
    primary: 'lt-stage--primary',
    teal:    'lt-stage--teal',
}[c] || '');
</script>

<template>
    <DesignPreviewLayout title="Traceability Record">
        <div class="lt-root">

            <!-- ── Page Header ─────────────────────────────────────────────── -->
            <div class="lt-page-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 py-3">
                        <div>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="lt-badge lt-badge--green">✓ Verified</span>
                                <span class="lt-badge lt-badge--green">🔗 Traceable</span>
                                <span class="lt-badge lt-badge--amber">⛓ Blockchain Verified</span>
                                <span class="lt-badge lt-badge--blue">📦 Export Ready</span>
                            </div>
                            <h1 class="lt-page-title mb-1">Traceability Record</h1>
                            <p class="lt-page-sub mb-0">View the complete journey of this coffee from production to market.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                            <Link :href="route('lot.show', lot.id)" class="lt-btn lt-btn--outline">← Lot Profile</Link>
                            <button class="lt-btn lt-btn--outline">⬇ Download Report</button>
                            <button class="lt-btn lt-btn--outline">⬇ Download QR</button>
                            <button class="lt-btn lt-btn--primary">↗ Share Record</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 pb-5 mt-4">
                <div class="row g-4">

                    <!-- ── LEFT: Timeline + Main content ──────────────────── -->
                    <div class="col-12 col-lg-8">

                        <!-- Overview Card -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Lot Overview</p>
                                <span class="lt-badge lt-badge--green" style="font-size:0.5625rem;">{{ lotStatus }}</span>
                            </div>
                            <div class="lt-card-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <p class="lt-section-label mb-2">Lot Information</p>
                                        <div class="lt-kv-list">
                                            <div class="lt-kv"><span>Lot Name</span><strong>{{ lotName }}</strong></div>
                                            <div class="lt-kv"><span>Lot ID</span><strong class="lt-mono">{{ lotNumber }}</strong></div>
                                            <div class="lt-kv"><span>QR Reference</span><strong class="lt-mono">{{ lotNumber }}</strong></div>
                                            <div class="lt-kv"><span>Coffee Type</span><strong>{{ coffeeType }}</strong></div>
                                            <div class="lt-kv"><span>Current Owner</span><strong>Origin Exports Ltd</strong></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <p class="lt-section-label mb-2">Quick Statistics</p>
                                        <div class="row g-2">
                                            <div v-for="kpi in [
                                                { label: 'Farmers',       value: '24' },
                                                { label: 'Farms',         value: '8'  },
                                                { label: 'Collections',   value: '4'  },
                                                { label: 'Batches',       value: '1'  },
                                                { label: 'Quantity',      value: netWeight + ' kg' },
                                                { label: 'Quality Score', value: qualityScore.toFixed(1) + ' SCA' },
                                            ]" :key="kpi.label" class="col-6">
                                                <div class="lt-kpi-card">
                                                    <div class="lt-kpi-val">{{ kpi.value }}</div>
                                                    <div class="lt-kpi-label">{{ kpi.label }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Traceability Timeline -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Main Traceability Timeline</p>
                            </div>
                            <div class="lt-card-body">
                                <div class="lt-timeline">
                                    <div v-for="(step, i) in timeline" :key="step.stage" class="lt-tl-item" :class="{ 'lt-tl-item--current': step.current }">
                                        <!-- Spine -->
                                        <div class="lt-tl-left">
                                            <div class="lt-tl-node" :class="[stageColor(step.color), step.done ? 'lt-tl-node--done' : '']">
                                                <span>{{ step.icon }}</span>
                                            </div>
                                            <div v-if="i < timeline.length - 1" class="lt-tl-spine" :class="step.done ? 'lt-tl-spine--done' : ''"></div>
                                        </div>
                                        <!-- Content -->
                                        <div class="lt-tl-body">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div>
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <span class="lt-stage-tag" :class="stageColor(step.color)">{{ step.stage }}</span>
                                                        <span v-if="step.current" class="lt-current-tag">Current</span>
                                                        <span v-if="step.done" class="lt-verified-tag">✓ Verified</span>
                                                    </div>
                                                    <h6 class="lt-tl-title mb-0">{{ step.title }}</h6>
                                                    <p class="lt-tl-code mb-0">{{ step.code }}</p>
                                                </div>
                                                <button class="lt-btn lt-btn--ghost lt-btn--sm flex-shrink-0">View Details</button>
                                            </div>
                                            <div class="row g-2 mt-1">
                                                <div v-for="field in step.fields" :key="field.label" class="col-6 col-sm-4">
                                                    <div class="lt-field-cell">
                                                        <span class="lt-field-cell__label">{{ field.label }}</span>
                                                        <strong class="lt-field-cell__val">{{ field.value }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Origin Information -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Origin &amp; Production Area</p>
                            </div>
                            <div class="lt-card-body">
                                <div class="row g-4">
                                    <div class="col-12 col-md-5">
                                        <p class="lt-section-label mb-2">Origin Information</p>
                                        <div class="lt-kv-list">
                                            <div class="lt-kv"><span>Country</span><strong>Uganda</strong></div>
                                            <div class="lt-kv"><span>Region</span><strong>Eastern Uganda</strong></div>
                                            <div class="lt-kv"><span>District</span><strong>Mbale</strong></div>
                                            <div class="lt-kv"><span>Cooperative</span><strong>Bugisu Cooperative Union</strong></div>
                                            <div class="lt-kv"><span>Farm Count</span><strong>8 Farms</strong></div>
                                            <div class="lt-kv"><span>Farmer Count</span><strong>24 Farmers</strong></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <p class="lt-section-label mb-2">Production Area</p>
                                        <div class="lt-map-placeholder">
                                            <div class="lt-map-inner">
                                                <div class="lt-map-pin">📍</div>
                                                <p class="lt-map-text">Mt. Elgon, Mbale District</p>
                                                <p class="lt-map-sub">1,800 – 2,100 m altitude</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quality Journey -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Quality Journey</p>
                            </div>
                            <div class="lt-card-body">
                                <div class="lt-quality-chain">
                                    <div v-for="(stage, idx) in [
                                        { label: 'Collection Score', value: 85.8, sub: 'Raw cherry' },
                                        { label: 'Batch Score',   value: 86.5, sub: 'Post-processing' },
                                        { label: 'Lot Score',     value: qualityScore, sub: 'Final grade' },
                                    ]" :key="stage.label" class="lt-qj-step">
                                        <div class="lt-qj-card" :class="idx === 2 ? 'lt-qj-card--active' : ''">
                                            <div class="lt-qj-score">{{ stage.value.toFixed(1) }}</div>
                                            <div class="lt-qj-label">{{ stage.label }}</div>
                                            <div class="lt-qj-sub">{{ stage.sub }}</div>
                                        </div>
                                        <div v-if="idx < 2" class="lt-qj-arrow">↓</div>
                                    </div>
                                </div>
                                <!-- Sparkline -->
                                <div class="lt-chart-wrap mt-3">
                                    <svg viewBox="0 0 300 48" style="width:100%;display:block;">
                                        <defs>
                                            <linearGradient id="ltGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#004532" stop-opacity="0.14"/>
                                                <stop offset="100%" stop-color="#004532" stop-opacity="0"/>
                                            </linearGradient>
                                        </defs>
                                        <polyline points="0,38 100,28 200,22 300,12"
                                            fill="none" stroke="#004532" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                                        <polygon points="0,38 100,28 200,22 300,12 300,48 0,48" fill="url(#ltGrad)"/>
                                    </svg>
                                    <div class="d-flex justify-content-between px-1 mt-1" style="font-size:0.6875rem;color:#6b7280;">
                                        <span>Collection</span><span>Batch</span><span>Lot</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ownership History -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Ownership History</p>
                            </div>
                            <div class="lt-card-body">
                                <div class="lt-ownership">
                                    <div v-for="(owner, idx) in ownershipHistory" :key="owner.role" class="lt-own-item">
                                        <div class="lt-own-left">
                                            <div class="lt-own-node" :class="owner.done ? 'lt-own-node--done' : ''">
                                                {{ owner.role.charAt(0) }}
                                            </div>
                                            <div v-if="idx < ownershipHistory.length - 1" class="lt-own-spine" :class="owner.done ? 'lt-own-spine--done' : ''"></div>
                                        </div>
                                        <div class="lt-own-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <div class="lt-own-role">{{ owner.role }}</div>
                                                    <div class="lt-own-org">{{ owner.org }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="lt-own-date">{{ owner.date }}</div>
                                                    <span class="lt-badge" :class="owner.done ? 'lt-badge--green' : 'lt-badge--muted'" style="font-size:0.5625rem;">
                                                        {{ owner.done ? 'Transferred' : 'Pending' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Media Gallery -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Media Gallery</p>
                            </div>
                            <div class="lt-card-body">
                                <div class="row g-3">
                                    <div v-for="label in ['Farm', 'Collection', 'Processing', 'Lot']" :key="label" class="col-6 col-md-3">
                                        <div class="lt-gallery-card">
                                            <div class="lt-gallery-thumb">☕</div>
                                            <div class="lt-gallery-label">{{ label }} Images</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Related Records -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Related Records</p>
                            </div>
                            <div class="lt-card-body">
                                <div class="row g-3">
                                    <div v-for="rec in [
                                        { label: 'Related Collection', code: 'COL-2025-011', icon: '🌾' },
                                        { label: 'Related Batch',   code: batchNumber,    icon: '⚙️' },
                                        { label: 'Related Lot',     code: lotNumber,      icon: '📦' },
                                    ]" :key="rec.label" class="col-12 col-md-4">
                                        <div class="lt-related-card">
                                            <div class="lt-related-icon">{{ rec.icon }}</div>
                                            <div class="lt-related-info">
                                                <div class="lt-related-label">{{ rec.label }}</div>
                                                <div class="lt-related-code lt-mono">{{ rec.code }}</div>
                                            </div>
                                            <button class="lt-btn lt-btn--ghost lt-btn--sm">View</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /col-lg-8 -->

                    <!-- ── RIGHT: Sidebar ──────────────────────────────────── -->
                    <div class="col-12 col-lg-4 lt-sidebar">

                        <!-- QR Verification -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">QR Verification</p>
                            </div>
                            <div class="lt-card-body text-center">
                                <svg class="lt-qr-svg mb-3" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" fill="#004532">
                                    <rect x="2" y="2" width="22" height="22" rx="2" fill="none" stroke="#004532" stroke-width="2.5"/>
                                    <rect x="7" y="7" width="12" height="12" rx="1"/>
                                    <rect x="56" y="2" width="22" height="22" rx="2" fill="none" stroke="#004532" stroke-width="2.5"/>
                                    <rect x="61" y="7" width="12" height="12" rx="1"/>
                                    <rect x="2" y="56" width="22" height="22" rx="2" fill="none" stroke="#004532" stroke-width="2.5"/>
                                    <rect x="7" y="61" width="12" height="12" rx="1"/>
                                    <rect x="28" y="2" width="4" height="4"/><rect x="34" y="2" width="4" height="4"/>
                                    <rect x="46" y="2" width="4" height="4"/><rect x="28" y="8" width="4" height="4"/>
                                    <rect x="40" y="8" width="4" height="4"/><rect x="52" y="8" width="4" height="4"/>
                                    <rect x="34" y="14" width="4" height="4"/><rect x="46" y="14" width="4" height="4"/>
                                    <rect x="28" y="20" width="4" height="4"/><rect x="40" y="20" width="4" height="4"/>
                                    <rect x="28" y="30" width="4" height="4"/><rect x="34" y="30" width="4" height="4"/>
                                    <rect x="46" y="30" width="4" height="4"/><rect x="52" y="30" width="4" height="4"/>
                                    <rect x="28" y="36" width="4" height="4"/><rect x="40" y="36" width="4" height="4"/>
                                    <rect x="52" y="36" width="4" height="4"/><rect x="34" y="42" width="4" height="4"/>
                                    <rect x="40" y="42" width="4" height="4"/><rect x="46" y="42" width="4" height="4"/>
                                    <rect x="28" y="48" width="4" height="4"/><rect x="52" y="48" width="4" height="4"/>
                                    <rect x="34" y="56" width="4" height="4"/><rect x="46" y="56" width="4" height="4"/>
                                    <rect x="52" y="56" width="4" height="4"/><rect x="28" y="62" width="4" height="4"/>
                                    <rect x="40" y="62" width="4" height="4"/><rect x="34" y="68" width="4" height="4"/>
                                    <rect x="46" y="68" width="4" height="4"/><rect x="52" y="68" width="4" height="4"/>
                                </svg>
                                <p class="lt-qr-hint mb-3">Scan to verify authenticity and traceability information.</p>
                                <div class="lt-kv-list mb-3 text-start">
                                    <div class="lt-kv"><span>QR Reference</span><strong class="lt-mono">{{ lotNumber }}</strong></div>
                                    <div class="lt-kv"><span>Verification</span><span class="lt-badge lt-badge--green" style="font-size:0.5625rem;">Verified</span></div>
                                    <div class="lt-kv"><span>Last Verified</span><strong>01 Mar 2026</strong></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="lt-btn lt-btn--outline flex-fill lt-btn--sm">⬇ Download QR</button>
                                    <button class="lt-btn lt-btn--ghost flex-fill lt-btn--sm">🖨 Print QR</button>
                                </div>
                            </div>
                        </div>

                        <!-- Blockchain Verification -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Blockchain Record</p>
                                <span class="lt-badge lt-badge--amber" style="font-size:0.5625rem;">⛓ Immutable</span>
                            </div>
                            <div class="lt-card-body">
                                <div class="lt-kv-list mb-3">
                                    <div class="lt-kv"><span>Transaction ID</span><strong class="lt-mono" style="font-size:0.6875rem;">0x4a2b…9f3c</strong></div>
                                    <div class="lt-kv"><span>Record Date</span><strong>22 Feb 2026</strong></div>
                                    <div class="lt-kv"><span>Network</span><strong>Arbitrum L2</strong></div>
                                    <div class="lt-kv"><span>Status</span><span class="lt-badge lt-badge--green" style="font-size:0.5625rem;">✓ Verified</span></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="lt-btn lt-btn--primary flex-fill lt-btn--sm">View Record</button>
                                    <button class="lt-btn lt-btn--outline flex-fill lt-btn--sm">Verify TX</button>
                                </div>
                            </div>
                        </div>

                        <!-- Sustainability -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Sustainability</p>
                                <div class="lt-sus-score">
                                    <span class="lt-sus-score__num">{{ sustainabilityScore }}</span>
                                    <span class="lt-sus-score__label">/100</span>
                                </div>
                            </div>
                            <div class="lt-card-body">
                                <p class="lt-sub-section mb-2">Environmental</p>
                                <div class="lt-sus-list mb-3">
                                    <div class="lt-sus-row" v-for="item in ['Water Conservation','Soil Management','Climate Smart Practices']" :key="item">
                                        <span>{{ item }}</span><span class="lt-badge lt-badge--green" style="font-size:0.5rem;">Active</span>
                                    </div>
                                </div>
                                <p class="lt-sub-section mb-2">Social</p>
                                <div class="lt-sus-list mb-3">
                                    <div class="lt-sus-row"><span>Farmer Participation</span><strong>24 farmers</strong></div>
                                    <div class="lt-sus-row"><span>Women Participation</span><strong>38%</strong></div>
                                    <div class="lt-sus-row"><span>Community Programs</span><strong>3 active</strong></div>
                                </div>
                                <p class="lt-sub-section mb-2">Compliance</p>
                                <div class="lt-sus-list">
                                    <div class="lt-sus-row"><span>Organic Status</span><span class="lt-badge lt-badge--green" style="font-size:0.5rem;">Certified</span></div>
                                    <div class="lt-sus-row"><span>Fairtrade Status</span><span class="lt-badge lt-badge--green" style="font-size:0.5rem;">Certified</span></div>
                                    <div class="lt-sus-row"><span>Certifications</span><strong>3 active</strong></div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents & Evidence -->
                        <div class="lt-card mb-4">
                            <div class="lt-card-head">
                                <p class="lt-eyebrow mb-0">Documents &amp; Evidence</p>
                            </div>
                            <div class="lt-card-body">
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="doc in documents" :key="doc.name"
                                        class="lt-doc-row"
                                        :class="doc.available ? '' : 'lt-doc-row--missing'">
                                        <span class="lt-doc-icon">{{ doc.icon }}</span>
                                        <span class="lt-doc-name">{{ doc.name }}</span>
                                        <div class="d-flex gap-1 ms-auto">
                                            <button v-if="doc.available" class="lt-btn lt-btn--ghost lt-btn--sm">View</button>
                                            <button v-if="doc.available" class="lt-btn lt-btn--ghost lt-btn--sm">⬇</button>
                                            <span v-else class="lt-badge lt-badge--muted" style="font-size:0.5rem;">Missing</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AI Insights -->
                        <div class="lt-card lt-card--ai mb-4">
                            <div class="lt-card-head lt-card-head--ai">
                                <p class="lt-eyebrow mb-0">AI Traceability Analysis</p>
                            </div>
                            <div class="lt-card-body">
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="insight in [
                                        { text: 'Full traceability available from farm to market.',        tone: 'ok'   },
                                        { text: 'Export-ready verification completed successfully.',       tone: 'ok'   },
                                        { text: 'Sustainability metrics above platform average.',          tone: 'ok'   },
                                        { text: 'Export permit not yet uploaded — required for shipping.', tone: 'warn' },
                                    ]" :key="insight.text" class="lt-insight-item" :class="`lt-insight-item--${insight.tone}`">
                                        {{ insight.text }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /sidebar -->
                </div><!-- /row -->
            </div><!-- /container -->

            <!-- ── Floating Actions ─────────────────────────────────────────── -->
            <div class="lt-fab-group">
                <transition name="lt-fab-pop">
                    <div v-if="fabOpen" class="lt-fab-menu">
                        <button class="lt-fab-item">📷 Scan QR</button>
                        <button class="lt-fab-item">⬇ Download Report</button>
                        <button class="lt-fab-item">✉ Contact Supplier</button>
                    </div>
                </transition>
                <button class="lt-fab" @click="fabOpen = !fabOpen">
                    <span style="font-size:1.25rem;">{{ fabOpen ? '×' : '+' }}</span>
                </button>
            </div>

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.lt-root {
    --primary:          #004532;
    --primary-grad:     #065f46;
    --primary-fixed:    #a6f2d1;
    --on-primary:       #ffffff;
    --on-primary-fixed: #002116;
    --secondary-fixed:  #fedcbe;
    --surface:          #f7f9fb;
    --surface-low:      #f2f4f6;
    --surface-high:     #eef2f0;
    --on-surface:       #111827;
    --on-surface-var:   #6b7280;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Page header ───────────────────────────────────────────────────────────── */
.lt-page-header   { background: #fff; border-bottom: 1px solid var(--surface-high); }
.lt-page-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; color: var(--on-surface); }
.lt-page-sub      { font-size: 0.8125rem; color: var(--on-surface-var); }

/* ── Card system ───────────────────────────────────────────────────────────── */
.lt-card {
    background: #ffffff;
    border: 1px solid var(--surface-high);
    border-radius: 0.75rem;
    overflow: hidden;
}
.lt-card-head {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--surface-high);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.lt-card-body { padding: 1.5rem; }

.lt-card--ai            { background: #f6fdf9; border-color: #c8e6d4; }
.lt-card-head--ai       { border-bottom-color: #c8e6d4; }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.lt-btn {
    font-family: 'Manrope', system-ui, sans-serif;
    font-size: 0.8125rem; font-weight: 700;
    border-radius: 6px; padding: 8px 16px; border: none;
    cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none; transition: opacity 0.15s, background 0.15s;
}
.lt-btn--primary  { background: linear-gradient(135deg, var(--primary), var(--primary-grad)); color: var(--on-primary); }
.lt-btn--primary:hover { opacity: 0.88; }
.lt-btn--outline  { background: #fff; border: 1px solid var(--surface-high); color: var(--on-surface); }
.lt-btn--outline:hover { background: var(--surface-low); }
.lt-btn--ghost    { background: transparent; color: var(--on-surface-var); border: 1px solid transparent; }
.lt-btn--ghost:hover { background: var(--surface-low); color: var(--on-surface); }
.lt-btn--sm       { font-size: 0.75rem; padding: 5px 12px; }

/* ── Badges ────────────────────────────────────────────────────────────────── */
.lt-badge         { display: inline-flex; align-items: center; gap: 4px; font-size: 0.6875rem; font-weight: 700; border-radius: 999px; padding: 4px 10px; letter-spacing: 0.04em; }
.lt-badge--green  { background: var(--primary-fixed); color: var(--on-primary-fixed); }
.lt-badge--amber  { background: #fef3c7; color: #92400e; }
.lt-badge--blue   { background: #dbeafe; color: #1e40af; }
.lt-badge--muted  { background: var(--surface-high); color: var(--on-surface-var); }

/* ── Labels ────────────────────────────────────────────────────────────────── */
.lt-eyebrow       { font-size: 0.625rem; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--on-surface-var); margin: 0; }
.lt-section-label { font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--on-surface-var); margin: 0; }
.lt-sub-section   { font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.10em; text-transform: uppercase; color: var(--primary); margin: 0; }
.lt-mono          { font-family: 'IBM Plex Mono', monospace; }

/* ── KV list ───────────────────────────────────────────────────────────────── */
.lt-kv-list       { display: flex; flex-direction: column; gap: 0; }
.lt-kv            { display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 0.8125rem; padding: 8px 0; border-bottom: 1px solid var(--surface-high); }
.lt-kv:last-child { border-bottom: none; }
.lt-kv span:first-child { color: var(--on-surface-var); flex-shrink: 0; }
.lt-kv strong     { color: var(--on-surface); font-weight: 600; text-align: right; }

/* ── KPI cards ─────────────────────────────────────────────────────────────── */
.lt-kpi-card      { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 6px; padding: 10px 12px; text-align: center; }
.lt-kpi-val       { font-size: 1rem; font-weight: 800; color: var(--primary); letter-spacing: -0.02em; }
.lt-kpi-label     { font-size: 0.6875rem; color: var(--on-surface-var); font-weight: 600; margin-top: 2px; }

/* ── Timeline ──────────────────────────────────────────────────────────────── */
.lt-timeline      { display: flex; flex-direction: column; gap: 0; }
.lt-tl-item       { display: flex; gap: 16px; padding-bottom: 2rem; }
.lt-tl-item:last-child { padding-bottom: 0; }
.lt-tl-item--current .lt-tl-body { background: #f0faf5; border: 1px solid #c8e6d4; border-radius: 12px; padding: 16px; margin: -4px; }
.lt-tl-left       { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; width: 40px; }
.lt-tl-node       { width: 40px; height: 40px; border-radius: 50%; background: var(--surface-high); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; border: 2px solid transparent; }
.lt-tl-node--done { border-color: var(--primary); background: var(--primary-fixed); }
.lt-tl-spine      { flex: 1; width: 2px; background: var(--surface-high); margin-top: 4px; min-height: 24px; }
.lt-tl-spine--done { background: var(--primary-fixed); }
.lt-tl-body       { flex: 1; min-width: 0; }
.lt-tl-title      { font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.lt-tl-code       { font-size: 0.75rem; color: var(--on-surface-var); font-family: 'IBM Plex Mono', monospace; margin-top: 2px; }

.lt-stage-tag     { font-size: 0.5625rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; border-radius: 4px; padding: 2px 8px; }
.lt-stage--green  .lt-tl-node, .lt-stage-tag.lt-stage--green  { background: #dcfce7; color: #166534; }
.lt-stage--amber  .lt-tl-node, .lt-stage-tag.lt-stage--amber  { background: #fef3c7; color: #92400e; }
.lt-stage--blue   .lt-tl-node, .lt-stage-tag.lt-stage--blue   { background: #dbeafe; color: #1e40af; }
.lt-stage--primary .lt-tl-node, .lt-stage-tag.lt-stage--primary { background: var(--primary-fixed); color: var(--on-primary-fixed); }
.lt-stage--teal   .lt-tl-node, .lt-stage-tag.lt-stage--teal   { background: #ccfbf1; color: #134e4a; }

.lt-current-tag   { font-size: 0.5625rem; font-weight: 800; background: var(--primary); color: var(--on-primary); border-radius: 4px; padding: 2px 8px; letter-spacing: 0.06em; text-transform: uppercase; }
.lt-verified-tag  { font-size: 0.5625rem; font-weight: 700; background: #dcfce7; color: #166534; border-radius: 4px; padding: 2px 8px; }

.lt-field-cell    { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 6px; padding: 8px 10px; }
.lt-field-cell__label { display: block; font-size: 0.625rem; font-weight: 700; color: var(--on-surface-var); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px; }
.lt-field-cell__val   { display: block; font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }

/* ── Origin map placeholder ────────────────────────────────────────────────── */
.lt-map-placeholder { background: #f8fdf9; border-radius: 12px; border: 1px solid var(--surface-high); min-height: 140px; display: flex; align-items: center; justify-content: center; }
.lt-map-inner       { text-align: center; }
.lt-map-pin         { font-size: 2rem; margin-bottom: 6px; }
.lt-map-text        { font-size: 0.875rem; font-weight: 700; color: var(--primary); margin: 0; }
.lt-map-sub         { font-size: 0.75rem; color: var(--on-surface-var); margin: 0; }

/* ── Quality journey ───────────────────────────────────────────────────────── */
.lt-quality-chain   { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.lt-qj-step         { display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 240px; }
.lt-qj-card         { background: #fff; border: 1px solid var(--surface-high); border-radius: 6px; padding: 12px 20px; text-align: center; width: 100%; }
.lt-qj-card--active { background: #f0faf5; border-color: #a6f2d1; }
.lt-qj-score        { font-size: 1.375rem; font-weight: 900; color: var(--primary); letter-spacing: -0.02em; }
.lt-qj-label        { font-size: 0.75rem; font-weight: 700; color: var(--on-surface); }
.lt-qj-sub          { font-size: 0.6875rem; color: var(--on-surface-var); }
.lt-qj-arrow        { font-size: 1.25rem; color: var(--surface-high); line-height: 1.2; }
.lt-chart-wrap      { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 8px; padding: 12px 8px 6px; }

/* ── Ownership ─────────────────────────────────────────────────────────────── */
.lt-ownership       { display: flex; flex-direction: column; gap: 0; }
.lt-own-item        { display: flex; gap: 14px; padding-bottom: 1.25rem; }
.lt-own-item:last-child { padding-bottom: 0; }
.lt-own-left        { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; width: 36px; }
.lt-own-node        { width: 36px; height: 36px; border-radius: 50%; background: var(--surface-high); color: var(--on-surface-var); font-size: 0.8125rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--surface-high); }
.lt-own-node--done  { background: var(--primary-fixed); color: var(--on-primary-fixed); border-color: #a6f2d1; }
.lt-own-spine       { flex: 1; width: 2px; background: var(--surface-high); min-height: 16px; margin-top: 4px; }
.lt-own-spine--done { background: var(--primary-fixed); }
.lt-own-body        { flex: 1; }
.lt-own-role        { font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--on-surface-var); }
.lt-own-org         { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); margin-top: 1px; }
.lt-own-date        { font-size: 0.75rem; color: var(--on-surface-var); }

/* ── Gallery ───────────────────────────────────────────────────────────────── */
.lt-gallery-card    { border: 1px solid var(--surface-high); border-radius: 6px; overflow: hidden; }
.lt-gallery-thumb   { background: #f8fdf9; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem; border-bottom: 1px solid var(--surface-high); }
.lt-gallery-label   { font-size: 0.75rem; font-weight: 700; color: var(--on-surface-var); padding: 8px 10px; text-align: center; }

/* ── Related records ───────────────────────────────────────────────────────── */
.lt-related-card    { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 6px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; }
.lt-related-icon    { font-size: 1.5rem; flex-shrink: 0; }
.lt-related-info    { flex: 1; min-width: 0; }
.lt-related-label   { font-size: 0.75rem; font-weight: 700; color: var(--on-surface-var); }
.lt-related-code    { font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); }

/* ── QR sidebar ────────────────────────────────────────────────────────────── */
.lt-qr-svg          { width: 100px; height: 100px; display: block; margin: 0 auto; }
.lt-qr-hint         { font-size: 0.8125rem; color: var(--on-surface-var); line-height: 1.5; }

/* ── Sustainability ────────────────────────────────────────────────────────── */
.lt-sus-score       { display: flex; align-items: baseline; gap: 2px; }
.lt-sus-score__num  { font-size: 1.375rem; font-weight: 900; color: var(--primary); }
.lt-sus-score__label{ font-size: 0.75rem; color: var(--on-surface-var); }
.lt-sus-list        { display: flex; flex-direction: column; gap: 0; }
.lt-sus-row         { display: flex; justify-content: space-between; align-items: center; font-size: 0.8125rem; padding: 7px 0; border-bottom: 1px solid var(--surface-high); }
.lt-sus-row:last-child { border-bottom: none; }
.lt-sus-row span:first-child { color: var(--on-surface-var); }
.lt-sus-row strong  { color: var(--on-surface); font-weight: 600; }

/* ── Documents ─────────────────────────────────────────────────────────────── */
.lt-doc-row         { display: flex; align-items: center; gap: 10px; background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 8px; padding: 10px 12px; font-size: 0.8125rem; }
.lt-doc-row--missing { background: #fffbf5; border-color: #fde8c8; }
.lt-doc-icon        { font-size: 1rem; flex-shrink: 0; }
.lt-doc-name        { flex: 1; font-weight: 600; color: var(--on-surface); }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.lt-insight-item    { font-size: 0.8125rem; line-height: 1.6; padding: 9px 12px 9px 32px; border-radius: 6px; position: relative; color: var(--on-surface); border: 1px solid transparent; }
.lt-insight-item::before { content: ''; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 8px; height: 8px; border-radius: 50%; }
.lt-insight-item--ok   { background: #f6fdf9; border-color: #c8e6d4; }
.lt-insight-item--ok::before { background: var(--primary); }
.lt-insight-item--warn { background: #fffbf0; border-color: #fde68a; }
.lt-insight-item--warn::before { background: #f59e0b; }

/* ── Sidebar sticky ────────────────────────────────────────────────────────── */
.lt-sidebar         { position: sticky; top: 1rem; align-self: flex-start; }

/* ── FAB ───────────────────────────────────────────────────────────────────── */
.lt-fab-group       { position: fixed; bottom: 28px; right: 28px; z-index: 400; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
.lt-fab             { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-grad)); color: var(--on-primary); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: opacity 0.15s; }
.lt-fab:hover       { opacity: 0.9; }
.lt-fab-menu        { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
.lt-fab-item        { background: #fff; border: 1px solid var(--surface-high); border-radius: 999px; font-family: 'Manrope', sans-serif; font-size: 0.8125rem; font-weight: 700; color: var(--on-surface); padding: 8px 16px; cursor: pointer; white-space: nowrap; }
.lt-fab-item:hover  { background: var(--surface-low); }

.lt-fab-pop-enter-active, .lt-fab-pop-leave-active { transition: opacity 0.15s, transform 0.15s; }
.lt-fab-pop-enter-from, .lt-fab-pop-leave-to       { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 992px) {
    .lt-sidebar { position: static; }
}
@media (max-width: 640px) {
    .lt-tl-item--current .lt-tl-body { margin: 0; }
    .lt-fab-group { bottom: 16px; right: 16px; }
}
</style>
