<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, Connection, DataLine, Download,
    Location, Lock, Medal, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning, WarningFilled,
} from '@element-plus/icons-vue';

/* ── KPIs ──────────────────────────────────────────────────────── */
const kpis = [
    { label: 'Total Users',        value: '1,284', sub: '+12 this week',      tone: 'primary', icon: Star,          trend: '↑' },
    { label: 'Active Farmers',     value: '342',   sub: '89% verified',       tone: 'success', icon: Check,         trend: '↑' },
    { label: 'Active Buyers',      value: '518',   sub: 'Across 14 countries',tone: 'info',    icon: ShoppingCart,  trend: '↑' },
    { label: 'Exporters',          value: '94',    sub: '76 export-ready',    tone: 'warning', icon: Van,           trend: '↑' },
    { label: 'Total Lots',         value: '2,471', sub: '188 pending review', tone: 'primary', icon: Box,           trend: '↑' },
    { label: 'Active Auctions',    value: '23',    sub: '4 closing today',    tone: 'danger',  icon: TrendCharts,   trend: '↑' },
    { label: 'Tokenised Assets',   value: '8,640', sub: '$2.1M total value',  tone: 'success', icon: Connection,    trend: '↑' },
    { label: 'Daily Transactions', value: '412',   sub: 'Last 24 hours',      tone: 'info',    icon: DataLine,      trend: '↑' },
];

/* ── Platform Activity Chart ───────────────────────────────────── */
const chartRange = ref('7D');
const chartData = {
    '7D': [
        { label: 'Mon', tx: 72, lots: 18, auctions: 8  },
        { label: 'Tue', tx: 85, lots: 22, auctions: 11 },
        { label: 'Wed', tx: 68, lots: 14, auctions: 9  },
        { label: 'Thu', tx: 94, lots: 28, auctions: 14 },
        { label: 'Fri', tx: 100, lots: 32, auctions: 18},
        { label: 'Sat', tx: 78, lots: 20, auctions: 12 },
        { label: 'Sun', tx: 62, lots: 12, auctions: 7  },
    ],
    '30D': [
        { label: 'W1', tx: 65, lots: 44, auctions: 30 },
        { label: 'W2', tx: 78, lots: 58, auctions: 42 },
        { label: 'W3', tx: 88, lots: 70, auctions: 55 },
        { label: 'W4', tx: 100, lots: 84, auctions: 68},
    ],
    '90D': [
        { label: 'Jan', tx: 55, lots: 38, auctions: 22 },
        { label: 'Feb', tx: 72, lots: 55, auctions: 38 },
        { label: 'Mar', tx: 100, lots: 78, auctions: 60},
    ],
    '1Y': [
        { label: 'Q1', tx: 55, lots: 40, auctions: 28 },
        { label: 'Q2', tx: 74, lots: 60, auctions: 44 },
        { label: 'Q3', tx: 88, lots: 74, auctions: 56 },
        { label: 'Q4', tx: 100, lots: 88, auctions: 70},
    ],
};
const chartBars = computed(() => chartData[chartRange.value]);

/* ── User Management ───────────────────────────────────────────── */
const userSearch  = ref('');
const userRoleFilter = ref('All');
const users = ref([
    { name: 'James Okwi',     email: 'james@farm.ug',   role: 'Farmer',   location: 'Mbale, Uganda',     status: 'Active',    verified: true,  activity: 88 },
    { name: 'Sara Nielsen',   email: 'sara@nordic.dk',  role: 'Buyer',    location: 'Copenhagen, Denmark',status: 'Active',   verified: true,  activity: 74 },
    { name: 'Ahmed Hassan',   email: 'ahmed@dubai.ae',  role: 'Exporter', location: 'Dubai, UAE',         status: 'Active',   verified: true,  activity: 92 },
    { name: 'Grace Atim',     email: 'grace@farm.ug',   role: 'Farmer',   location: 'Kapchorwa, Uganda',  status: 'Pending',  verified: false, activity: 20 },
    { name: 'Klaus Wagner',   email: 'klaus@kaffee.de', role: 'Buyer',    location: 'Berlin, Germany',    status: 'Active',   verified: true,  activity: 65 },
    { name: 'Priya Sharma',   email: 'priya@exp.in',    role: 'Exporter', location: 'Mumbai, India',      status: 'Suspended',verified: false, activity: 0  },
    { name: 'David Muwanga',  email: 'david@farm.ug',   role: 'Farmer',   location: 'Kasese, Uganda',     status: 'Active',   verified: true,  activity: 55 },
]);
const filteredUsers = computed(() => {
    let r = users.value;
    if (userRoleFilter.value !== 'All') r = r.filter(u => u.role === userRoleFilter.value);
    if (userSearch.value.trim()) {
        const q = userSearch.value.toLowerCase();
        r = r.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q) || u.location.toLowerCase().includes(q));
    }
    return r;
});

/* ── Lot Approval Queue ────────────────────────────────────────── */
const approvalFilter = ref('All');
const lotQueue = ref([
    { id: 'LT-2401', farm: 'Sipi Plot A',    origin: 'Uganda',   score: 89.2, ready: 'Yes',     status: 'Pending Review', date: 'May 06', tone: 'warning' },
    { id: 'LT-2402', farm: 'Elgon Block B',  origin: 'Uganda',   score: 84.6, ready: 'Partial', status: 'Pending Review', date: 'May 05', tone: 'warning' },
    { id: 'LT-2403', farm: 'Yirgacheffe Co', origin: 'Ethiopia', score: 91.0, ready: 'Yes',     status: 'Approved',       date: 'May 04', tone: 'success' },
    { id: 'LT-2404', farm: 'Kivu Growers',   origin: 'Rwanda',   score: 76.5, ready: 'No',      status: 'Rejected',       date: 'May 03', tone: 'danger'  },
    { id: 'LT-2405', farm: 'Rwenzori Farm',  origin: 'Uganda',   score: 87.8, ready: 'Yes',     status: 'Pending Review', date: 'May 02', tone: 'warning' },
]);
const filteredQueue = computed(() => {
    if (approvalFilter.value === 'All') return lotQueue.value;
    return lotQueue.value.filter(l => l.status === approvalFilter.value);
});

/* ── Auction Monitoring ────────────────────────────────────────── */
const auctions = ref([
    { id: 'AUC-001', lot: 'Mount Elgon AA',    bid: '$5,460',  participants: 8,  closing: '2h 14m', status: 'Active',  alert: false },
    { id: 'AUC-002', lot: 'Kyoga Robusta R1',  bid: '$23,400', participants: 14, closing: '0h 22m', status: 'Closing', alert: true  },
    { id: 'AUC-003', lot: 'Yirgacheffe G1',    bid: '$18,200', participants: 6,  closing: '5h 08m', status: 'Active',  alert: false },
    { id: 'AUC-004', lot: 'Sipi Falls Natural',bid: '$7,800',  participants: 11, closing: '1h 40m', status: 'Active',  alert: true  },
]);

/* ── Transactions ──────────────────────────────────────────────── */
const transactions = ref([
    { id: 'TXN-7841', user: 'Sara Nielsen',  type: 'Buy',    amount: '$5,200', status: 'Completed', date: 'May 07', tone: 'success' },
    { id: 'TXN-7842', user: 'Ahmed Hassan',  type: 'Sell',   amount: '$14,800',status: 'Completed', date: 'May 07', tone: 'success' },
    { id: 'TXN-7843', user: 'Klaus Wagner',  type: 'Invest', amount: '$3,000', status: 'Pending',   date: 'May 07', tone: 'warning' },
    { id: 'TXN-7844', user: 'James Okwi',    type: 'Sell',   amount: '$2,100', status: 'Completed', date: 'May 06', tone: 'success' },
    { id: 'TXN-7845', user: 'Priya Sharma',  type: 'Buy',    amount: '$8,640', status: 'Failed',    date: 'May 06', tone: 'danger'  },
    { id: 'TXN-7846', user: 'David Muwanga', type: 'Sell',   amount: '$1,880', status: 'Completed', date: 'May 06', tone: 'success' },
]);

/* ── Compliance checks ─────────────────────────────────────────── */
const compliance = [
    { label: 'Farmer Verification',    done: 284, total: 342, pct: 83 },
    { label: 'Export Documents',       done: 72,  total: 94,  pct: 77 },
    { label: 'Quality Validation',     done: 2108,total: 2471,pct: 85 },
    { label: 'Blockchain Verification',done: 8200, total: 8640,pct: 95 },
    { label: 'KYC / AML Checks',       done: 1100, total: 1284,pct: 86 },
];

/* ── Disputes ──────────────────────────────────────────────────── */
const disputes = ref([
    { id: 'DSP-041', user: 'Klaus Wagner',  issue: 'Quality dispute',    severity: 'Medium', status: 'Investigating', admin: 'Admin A' },
    { id: 'DSP-042', user: 'Grace Atim',    issue: 'Payment delay',      severity: 'Low',    status: 'Open',          admin: 'Unassigned' },
    { id: 'DSP-043', user: 'Ahmed Hassan',  issue: 'Shipment missing',   severity: 'High',   status: 'Investigating', admin: 'Admin B' },
    { id: 'DSP-044', user: 'Priya Sharma',  issue: 'Fraud suspicion',    severity: 'Critical',status: 'Open',         admin: 'Admin A' },
]);

/* ── Market oversight ──────────────────────────────────────────── */
const overviewBars = [48, 62, 55, 74, 68, 82, 90, 76, 94, 80, 88, 84];
const anomalies = [
    { label: 'AUC-002 bid spike',     type: 'Price Anomaly',   tone: 'danger'  },
    { label: 'UAE Robusta demand ↑',  type: 'Demand Spike',    tone: 'warning' },
    { label: 'Rwenzori supply low',   type: 'Supply Shortage', tone: 'warning' },
];

/* ── Tokenised assets ──────────────────────────────────────────── */
const tokens = ref([
    { id: 'TKN-001', lot: 'Mount Elgon AA',     owner: 'Sara Nielsen',  value: '$6,240', status: 'Active',  tone: 'success' },
    { id: 'TKN-002', lot: 'Kyoga Robusta R1',   owner: 'Klaus Wagner',  value: '$4,700', status: 'Active',  tone: 'success' },
    { id: 'TKN-003', lot: 'Sipi Falls Natural', owner: 'Ahmed Hassan',  value: '$3,538', status: 'Frozen',  tone: 'danger'  },
    { id: 'TKN-004', lot: 'Yirgacheffe G1',     owner: 'Sara Nielsen',  value: '$12,800',status: 'Active',  tone: 'success' },
]);

/* ── System health ─────────────────────────────────────────────── */
const health = [
    { label: 'API Gateway',       status: 'Healthy',  uptime: '99.98%', tone: 'success' },
    { label: 'Database',          status: 'Healthy',  uptime: '99.99%', tone: 'success' },
    { label: 'Blockchain Sync',   status: 'Warning',  uptime: '98.12%', tone: 'warning' },
    { label: 'Payment Gateway',   status: 'Healthy',  uptime: '99.95%', tone: 'success' },
];

/* ── AI Insights ───────────────────────────────────────────────── */
const insights = [
    { text: 'Unusual bidding activity detected in AUC-002 — possible bid manipulation.',      tone: 'danger'  },
    { text: 'Export approvals up 22% this week — increased farmer compliance activity.',      tone: 'success' },
    { text: 'Robusta demand spike in UAE — consider prioritising lot approvals from Uganda.', tone: 'warning' },
];

/* ── Alerts ────────────────────────────────────────────────────── */
const alerts = reactive({ fraud: true, priceAnomaly: true, newRegistrations: false, auctionSpikes: true, exportCompliance: true });

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: "Hi! I'm your Bean Origin Admin Assistant. I can help you monitor platform health, manage users, and detect anomalies. What do you need?" },
]);
const prompts = ['Which users are suspicious?', 'What lots need approval?', 'Are there pricing anomalies?', 'Which auctions need attention?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'AUC-002 shows abnormal bidding — 14 participants with rapid consecutive bids. Priya Sharma\'s account has been flagged for suspicious transaction patterns. Recommend review.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <DesignPreviewLayout title="Admin Dashboard">
        <Head title="Admin Dashboard" />

        <div class="ad-page">

            <!-- ── 1. Header ──────────────────────────────────────────── -->
            <div class="ad-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <h1 class="ad-title mb-0">Admin Dashboard</h1>
                            <p class="ad-subtitle mb-0">Manage Bean Origin platform operations and ecosystem health</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn ad-btn-primary btn-sm"><el-icon><Star /></el-icon> Create User</button>
                            <button class="btn ad-btn-outline btn-sm"><el-icon><Check /></el-icon> Approve Lots</button>
                            <button class="btn ad-btn-outline btn-sm"><el-icon><Download /></el-icon> System Logs</button>
                            <button class="btn ad-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask AI Assistant</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 pb-2">
                        <span class="ad-hbadge"><el-icon><Lock /></el-icon> System Admin</span>
                        <span class="ad-hbadge ad-hbadge--soft"><el-icon><Checked /></el-icon> Platform Control</span>
                        <span class="ad-hbadge ad-hbadge--outline"><el-icon><Check /></el-icon> Verified Operations</span>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. KPI Cards ────────────────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="kpi in kpis" :key="kpi.label" class="col-6 col-sm-4 col-lg-3 col-xl">
                        <div class="ad-kpi h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="ad-kpi__label">{{ kpi.label }}</span>
                                <span class="ad-kpi__icon" :class="`ad-kpi__icon--${kpi.tone}`">
                                    <el-icon><component :is="kpi.icon" /></el-icon>
                                </span>
                            </div>
                            <div class="ad-kpi__value" :class="`ad-kpi__value--${kpi.tone}`">{{ kpi.value }}</div>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <span class="ad-kpi__trend">{{ kpi.trend }}</span>
                                <span class="ad-kpi__sub">{{ kpi.sub }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ──────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 3. Platform Activity Chart -->
                            <div class="ad-card">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div class="ad-card-title">
                                        <el-icon class="ad-card-icon"><TrendCharts /></el-icon>
                                        Platform Activity
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button v-for="r in ['7D','30D','90D','1Y']" :key="r"
                                            class="btn" :class="chartRange === r ? 'ad-btn-primary' : 'ad-btn-outline'"
                                            @click="chartRange = r">{{ r }}</button>
                                    </div>
                                </div>
                                <div class="ad-chart">
                                    <div class="ad-chart-bars">
                                        <div v-for="bar in chartBars" :key="bar.label" class="ad-chart-col">
                                            <div class="ad-chart-triple">
                                                <div class="ad-chart-bar ad-chart-bar--tx"      :style="{ height: `${bar.tx}%` }"></div>
                                                <div class="ad-chart-bar ad-chart-bar--lots"    :style="{ height: `${bar.lots}%` }"></div>
                                                <div class="ad-chart-bar ad-chart-bar--auctions":style="{ height: `${bar.auctions}%` }"></div>
                                            </div>
                                            <span class="ad-chart-label">{{ bar.label }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-2">
                                    <div class="d-flex gap-3 flex-wrap">
                                        <span class="ad-legend"><span class="ad-legend-dot" style="background:#004532;"></span>Transactions</span>
                                        <span class="ad-legend"><span class="ad-legend-dot" style="background:#059669;"></span>Lots Created</span>
                                        <span class="ad-legend"><span class="ad-legend-dot" style="background:#a7f3d0;"></span>Auctions</span>
                                    </div>
                                    <div class="ad-chart-note"><el-icon><Opportunity /></el-icon> Platform activity up 18% this period.</div>
                                </div>
                            </div>

                            <!-- 4. User Management Table -->
                            <div class="ad-card p-0 overflow-hidden">
                                <div class="px-3 pt-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                                        <div class="ad-card-title">
                                            <el-icon class="ad-card-icon"><Star /></el-icon>
                                            User Management
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ filteredUsers.length }} users</span>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">{{ users.filter(u => !u.verified).length }} pending</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="ad-filter-group">
                                            <button v-for="f in ['All','Farmer','Buyer','Exporter']" :key="f"
                                                class="ad-filter-btn" :class="{ 'ad-filter-btn--active': userRoleFilter === f }"
                                                @click="userRoleFilter = f">{{ f }}</button>
                                        </div>
                                        <div class="ad-search-wrap ms-auto">
                                            <el-icon class="ad-search-icon"><View /></el-icon>
                                            <input v-model="userSearch" class="ad-search-input" placeholder="Search users…">
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ad-table">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Role</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Verified</th>
                                                <th>Activity</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!filteredUsers.length">
                                                <td colspan="7" class="text-center py-4 ad-td-muted">No users match your filter.</td>
                                            </tr>
                                            <tr v-for="u in filteredUsers" :key="u.email" class="ad-table-row">
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="ad-user-avatar" :class="`ad-user-avatar--${u.role.toLowerCase()}`">{{ u.name.charAt(0) }}</div>
                                                        <div>
                                                            <div class="ad-item-name">{{ u.name }}</div>
                                                            <div class="ad-td-muted" style="font-size:.7rem;">{{ u.email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="ad-role-badge" :class="`ad-role-badge--${u.role.toLowerCase()}`">{{ u.role }}</span>
                                                </td>
                                                <td class="ad-td-muted">{{ u.location }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="u.status === 'Active'    ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : u.status === 'Pending'   ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                        {{ u.status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <div class="ad-verify-dot" :class="u.verified ? 'ad-verify-dot--yes' : 'ad-verify-dot--no'"></div>
                                                        <span style="font-size:.8rem;" :class="u.verified ? 'ad-up' : 'ad-down'">{{ u.verified ? 'Verified' : 'Pending' }}</span>
                                                    </div>
                                                </td>
                                                <td style="min-width:100px;">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="ad-bar-track" style="width:56px;">
                                                            <div class="ad-bar-fill"
                                                                :style="{ width: `${u.activity}%`,
                                                                          background: u.activity > 60 ? '#004532' : u.activity > 20 ? '#f59e0b' : '#ef4444' }"></div>
                                                        </div>
                                                        <span style="font-size:.75rem; font-weight:700;"
                                                            :class="u.activity > 60 ? 'ad-up' : u.activity > 20 ? 'ad-warn' : 'ad-down'">{{ u.activity }}%</span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm ad-btn-outline ad-act-btn"><el-icon><View /></el-icon></button>
                                                        <button v-if="!u.verified" class="btn btn-sm ad-btn-primary ad-act-btn"><el-icon><Checked /></el-icon> Verify</button>
                                                        <button v-if="u.status !== 'Suspended'" class="btn btn-sm ad-btn-danger ad-act-btn"><el-icon><Lock /></el-icon></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 5. Lot Approval Queue -->
                            <div class="ad-card p-0 overflow-hidden">
                                <div class="px-3 pt-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                                        <div class="ad-card-title">
                                            <el-icon class="ad-card-icon"><Box /></el-icon>
                                            Lot &amp; Product Approval Queue
                                        </div>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">{{ lotQueue.filter(l => l.status === 'Pending Review').length }} pending</span>
                                    </div>
                                    <div class="ad-filter-group">
                                        <button v-for="f in ['All','Pending Review','Approved','Rejected']" :key="f"
                                            class="ad-filter-btn" :class="{ 'ad-filter-btn--active': approvalFilter === f }"
                                            @click="approvalFilter = f">{{ f }}</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ad-table">
                                        <thead>
                                            <tr>
                                                <th>Lot ID</th>
                                                <th>Farm / Origin</th>
                                                <th>Quality</th>
                                                <th>Export Readiness</th>
                                                <th>Status</th>
                                                <th>Submitted</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lot in filteredQueue" :key="lot.id" class="ad-table-row">
                                                <td class="ad-item-name">{{ lot.id }}</td>
                                                <td>
                                                    <div class="ad-item-name">{{ lot.farm }}</div>
                                                    <div class="ad-td-muted" style="font-size:.7rem;">{{ lot.origin }}</div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="ad-score-dot" :class="lot.score >= 88 ? 'ad-score-dot--high' : 'ad-score-dot--mid'">{{ lot.score }}</div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="lot.ready === 'Yes'     ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : lot.ready === 'Partial' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                        {{ lot.ready }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="lot.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : lot.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                        {{ lot.status }}
                                                    </span>
                                                </td>
                                                <td class="ad-td-muted">{{ lot.date }}</td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm ad-btn-outline ad-act-btn"><el-icon><View /></el-icon></button>
                                                        <button v-if="lot.status === 'Pending Review'" class="btn btn-sm ad-btn-primary ad-act-btn"><el-icon><Check /></el-icon> Approve</button>
                                                        <button v-if="lot.status === 'Pending Review'" class="btn btn-sm ad-btn-danger ad-act-btn"><el-icon><Warning /></el-icon> Reject</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 6. Auction Monitoring -->
                            <div class="ad-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="ad-card-title">
                                        <el-icon class="ad-card-icon"><TrendCharts /></el-icon>
                                        Auction Monitoring
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">{{ auctions.length }} active</span>
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle" style="font-size:.65rem;">{{ auctions.filter(a => a.alert).length }} alerts</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ad-table">
                                        <thead>
                                            <tr>
                                                <th>Auction ID</th>
                                                <th>Lot</th>
                                                <th>Current Bid</th>
                                                <th>Participants</th>
                                                <th>Closing In</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="auc in auctions" :key="auc.id" class="ad-table-row" :class="{ 'ad-table-row--alert': auc.alert }">
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div v-if="auc.alert" class="ad-alert-pulse"></div>
                                                        <span class="ad-item-name">{{ auc.id }}</span>
                                                    </div>
                                                </td>
                                                <td class="ad-item-name">{{ auc.lot }}</td>
                                                <td class="fw-semibold">{{ auc.bid }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="ad-participants">{{ auc.participants }}</span>
                                                        <span class="ad-td-muted" style="font-size:.75rem;">bidders</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span :class="auc.status === 'Closing' ? 'ad-closing-badge' : 'ad-td-muted'">{{ auc.closing }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="auc.status === 'Active' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                        {{ auc.status }}
                                                    </span>
                                                    <span v-if="auc.alert" class="badge bg-danger ms-1" style="font-size:.6rem;">⚠ Alert</span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm ad-btn-outline ad-act-btn"><el-icon><View /></el-icon> Monitor</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 7. Transactions Overview -->
                            <div class="ad-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="ad-card-title">
                                        <el-icon class="ad-card-icon"><DataLine /></el-icon>
                                        Transactions Overview
                                    </div>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ transactions.length }} recent</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ad-table">
                                        <thead>
                                            <tr>
                                                <th>Transaction ID</th>
                                                <th>User</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="tx in transactions" :key="tx.id" class="ad-table-row">
                                                <td class="ad-item-name">{{ tx.id }}</td>
                                                <td class="ad-td-muted">{{ tx.user }}</td>
                                                <td>
                                                    <span class="ad-tx-type" :class="`ad-tx-type--${tx.type.toLowerCase()}`">{{ tx.type }}</span>
                                                </td>
                                                <td class="fw-semibold">{{ tx.amount }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="tx.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : tx.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                        {{ tx.status }}
                                                    </span>
                                                </td>
                                                <td class="ad-td-muted">{{ tx.date }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 9. Dispute Management -->
                            <div class="ad-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="ad-card-title">
                                        <el-icon class="ad-card-icon"><Warning /></el-icon>
                                        Dispute Management
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle" style="font-size:.65rem;">{{ disputes.filter(d => d.status === 'Open').length }} open</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ad-table">
                                        <thead>
                                            <tr>
                                                <th>Case ID</th>
                                                <th>User</th>
                                                <th>Issue</th>
                                                <th>Severity</th>
                                                <th>Status</th>
                                                <th>Assigned</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="d in disputes" :key="d.id" class="ad-table-row" :class="{ 'ad-table-row--critical': d.severity === 'Critical' }">
                                                <td class="ad-item-name">{{ d.id }}</td>
                                                <td class="ad-td-muted">{{ d.user }}</td>
                                                <td class="ad-td-muted">{{ d.issue }}</td>
                                                <td>
                                                    <span class="ad-severity-badge" :class="`ad-severity-badge--${d.severity.toLowerCase()}`">{{ d.severity }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="d.status === 'Resolved'      ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                              : d.status === 'Investigating' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                              : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                        {{ d.status }}
                                                    </span>
                                                </td>
                                                <td class="ad-td-muted">{{ d.admin }}</td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm ad-btn-outline ad-act-btn"><el-icon><View /></el-icon></button>
                                                        <button v-if="d.status !== 'Resolved'" class="btn btn-sm ad-btn-primary ad-act-btn"><el-icon><Check /></el-icon> Resolve</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 11. Tokenised Assets Control -->
                            <div class="ad-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="ad-card-title">
                                        <el-icon class="ad-card-icon"><Connection /></el-icon>
                                        Tokenised Assets Control
                                    </div>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ tokens.length }} tokens</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 ad-table">
                                        <thead>
                                            <tr>
                                                <th>Token ID</th>
                                                <th>Lot</th>
                                                <th>Owner</th>
                                                <th>Value</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="token in tokens" :key="token.id" class="ad-table-row">
                                                <td class="ad-item-name">{{ token.id }}</td>
                                                <td class="ad-td-muted">{{ token.lot }}</td>
                                                <td class="ad-td-muted">{{ token.owner }}</td>
                                                <td class="fw-semibold">{{ token.value }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="token.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                                        {{ token.status }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm ad-btn-outline ad-act-btn"><el-icon><View /></el-icon></button>
                                                        <button class="btn btn-sm ad-btn-outline ad-act-btn"><el-icon><Van /></el-icon> Transfer</button>
                                                        <button v-if="token.status === 'Active'" class="btn btn-sm ad-btn-danger ad-act-btn"><el-icon><Lock /></el-icon> Freeze</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right rail -->
                    <div class="col-12 col-xxl-4">
                        <div class="ad-rail">

                            <!-- 8. Compliance & Verification -->
                            <div class="ad-card">
                                <div class="ad-card-title mb-3">
                                    <el-icon class="ad-card-icon"><Checked /></el-icon>
                                    Compliance &amp; Verification
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="item in compliance" :key="item.label">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="ad-compliance-label">{{ item.label }}</span>
                                            <span class="ad-compliance-pct" :class="item.pct >= 90 ? 'ad-up' : item.pct >= 75 ? 'ad-warn' : 'ad-down'">{{ item.pct }}%</span>
                                        </div>
                                        <div class="ad-bar-track">
                                            <div class="ad-bar-fill"
                                                :style="{ width: `${item.pct}%`,
                                                          background: item.pct >= 90 ? '#004532' : item.pct >= 75 ? '#f59e0b' : '#ef4444' }">
                                            </div>
                                        </div>
                                        <div class="ad-td-muted mt-1" style="font-size:.7rem;">{{ item.done.toLocaleString() }} / {{ item.total.toLocaleString() }} complete</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Market Oversight -->
                            <div class="ad-card">
                                <div class="ad-card-title mb-3">
                                    <el-icon class="ad-card-icon"><TrendCharts /></el-icon>
                                    Market Oversight
                                </div>
                                <div class="ad-mini-chart mb-3">
                                    <div v-for="(v, i) in overviewBars" :key="i" class="ad-mini-bar" :style="{ height: `${v}%` }"></div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="a in anomalies" :key="a.label" class="ad-anomaly-row">
                                        <div class="ad-anomaly-dot" :class="`ad-anomaly-dot--${a.tone}`"></div>
                                        <div class="flex-fill">
                                            <div style="font-size:.8125rem; font-weight:600; color:var(--on-surface);">{{ a.label }}</div>
                                            <div class="ad-td-muted" style="font-size:.7rem;">{{ a.type }}</div>
                                        </div>
                                        <button class="btn btn-sm ad-btn-outline" style="font-size:.7rem; padding:3px 8px;">View</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. System Health -->
                            <div class="ad-card">
                                <div class="ad-card-title mb-3">
                                    <el-icon class="ad-card-icon"><Connection /></el-icon>
                                    System Health
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="h in health" :key="h.label" class="ad-health-row">
                                        <div class="ad-health-indicator" :class="`ad-health-indicator--${h.tone}`"></div>
                                        <div class="flex-fill">
                                            <div class="ad-health-label">{{ h.label }}</div>
                                            <div class="ad-td-muted" style="font-size:.7rem;">Uptime {{ h.uptime }}</div>
                                        </div>
                                        <span class="badge rounded-pill" style="font-size:.65rem;"
                                            :class="h.tone === 'success' ? 'bg-success-subtle text-success-emphasis border border-success-subtle'
                                                  : h.tone === 'warning' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                                  : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle'">
                                            {{ h.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. AI Admin Insights -->
                            <div>
                                <div class="ad-card-title mb-2">
                                    <el-icon class="ad-card-icon"><Opportunity /></el-icon>
                                    AI Admin Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="ad-insight-card" :class="`ad-insight-card--${ins.tone}`">
                                        <el-icon class="ad-insight-icon"><Opportunity /></el-icon>
                                        <p class="ad-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. Smart Alerts -->
                            <div class="ad-card">
                                <div class="ad-card-title mb-3">
                                    <el-icon class="ad-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="ad-alert-row">
                                        <span>{{ { fraud: 'Fraud Detection', priceAnomaly: 'Price Anomalies', newRegistrations: 'New Registrations', auctionSpikes: 'Auction Spikes', exportCompliance: 'Export Compliance' }[key] }}</span>
                                        <button class="ad-toggle" :class="{ 'ad-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── 15. Floating Admin AI ───────────────────────────────── -->
            <div class="ad-fab-wrap">
                <Transition name="ad-chat">
                    <div v-if="chatOpen" class="ad-chatbot">
                        <div class="ad-chatbot__head">
                            <div class="ad-chatbot__identity">
                                <div class="ad-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="ad-chatbot__name">Admin Assistant</div>
                                    <div class="ad-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="ad-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="ad-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="ad-chat-msg" :class="`ad-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="ad-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="ad-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="ad-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask the admin AI…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="ad-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </DesignPreviewLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.ad-page {
    --green:          #004532;
    --green-grad:     #065f46;
    --on-green:       #ffffff;
    --on-surface:     #111827;
    --on-surface-var: #6b7280;
    --surface-white:  #ffffff;
    --surface-low:    #f8fafc;
    --surface-mid:    #f1f5f9;
    --surface-high:   #eef2f0;
    font-family: 'Manrope', system-ui, sans-serif;
    background: var(--surface, #f7f9fb);
    color: var(--on-surface);
    min-height: 100%;
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.ad-header   { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.ad-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; }
.ad-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); }
.ad-hbadge   { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.ad-hbadge--soft    { background: #dcfce7; color: #166534; }
.ad-hbadge--outline { background: transparent; border: 1px solid var(--surface-high); color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.ad-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.ad-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.ad-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.ad-btn-outline:hover { background: var(--surface-low); }
.ad-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.ad-btn-ghost:hover { background: var(--surface-high); }
.ad-btn-danger  { background: #fee2e2; border-color: #fecaca; color: #b91c1c; border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.ad-btn-danger:hover { background: #fecaca; }

/* ── KPI Cards ─────────────────────────────────────────────────────────────── */
.ad-kpi { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.ad-kpi__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.ad-kpi__icon  { width: 22px; height: 22px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
.ad-kpi__icon--primary { background: #e0f2fe; color: #0369a1; }
.ad-kpi__icon--success { background: #dcfce7; color: #166534; }
.ad-kpi__icon--warning { background: #fef3c7; color: #92400e; }
.ad-kpi__icon--info    { background: #dbeafe; color: #1d4ed8; }
.ad-kpi__icon--danger  { background: #fee2e2; color: #b91c1c; }
.ad-kpi__value { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 0; }
.ad-kpi__value--primary { color: #0369a1; }
.ad-kpi__value--success { color: var(--green); }
.ad-kpi__value--warning { color: #92400e; }
.ad-kpi__value--info    { color: #1d4ed8; }
.ad-kpi__value--danger  { color: #b91c1c; }
.ad-kpi__trend { font-size: 0.75rem; font-weight: 800; color: var(--green); }
.ad-kpi__sub   { font-size: 0.625rem; color: var(--on-surface-var); }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.ad-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 6px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.ad-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.ad-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Chart ─────────────────────────────────────────────────────────────────── */
.ad-chart { height: 130px; display: flex; align-items: flex-end; background: var(--surface-low); border-radius: 8px; padding: 8px; }
.ad-chart-bars  { display: flex; align-items: flex-end; gap: 6px; width: 100%; height: 100%; }
.ad-chart-col   { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; height: 100%; justify-content: flex-end; }
.ad-chart-triple{ display: flex; gap: 2px; align-items: flex-end; height: 85%; }
.ad-chart-bar   { width: 8px; border-radius: 3px 3px 0 0; }
.ad-chart-bar--tx       { background: var(--green); }
.ad-chart-bar--lots     { background: #059669; }
.ad-chart-bar--auctions { background: #a7f3d0; }
.ad-chart-label { font-size: 0.625rem; color: var(--on-surface-var); font-weight: 600; }
.ad-legend      { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; color: var(--on-surface-var); }
.ad-legend-dot  { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.ad-chart-note  { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--green); font-weight: 600; }

/* ── Filter + Search ───────────────────────────────────────────────────────── */
.ad-filter-group { display: flex; gap: 3px; background: var(--surface-low); border-radius: 8px; padding: 3px; flex-wrap: wrap; }
.ad-filter-btn { font-size: 0.6875rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; border: none; background: transparent; color: var(--on-surface-var); cursor: pointer; white-space: nowrap; transition: all 0.12s; }
.ad-filter-btn:hover { background: var(--surface-mid); color: var(--on-surface); }
.ad-filter-btn--active { background: var(--surface-white); color: var(--green); box-shadow: 0 1px 3px rgba(0,0,0,.08); font-weight: 700; }
.ad-search-wrap  { position: relative; display: flex; align-items: center; }
.ad-search-icon  { position: absolute; left: 8px; font-size: 13px; color: var(--on-surface-var); pointer-events: none; }
.ad-search-input { height: 30px; border: 1px solid var(--surface-high); border-radius: 7px; padding: 0 10px 0 28px; font-size: 0.8125rem; outline: none; color: var(--on-surface); width: 160px; }
.ad-search-input:focus { border-color: var(--green); }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.ad-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.ad-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.ad-table-row { transition: background 0.1s; }
.ad-table-row:hover { background: var(--surface-low); }
.ad-table-row--alert    { background: #fff7f7; }
.ad-table-row--critical { background: #fff1f1; }
.ad-item-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.ad-td-muted  { color: var(--on-surface-var); font-size: 0.8125rem; }
.ad-act-btn   { font-size: 0.75rem !important; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.ad-up   { color: #166534; font-weight: 700; }
.ad-warn { color: #92400e; font-weight: 700; }
.ad-down { color: #b91c1c; font-weight: 700; }

/* ── User avatars & role badges ────────────────────────────────────────────── */
.ad-user-avatar { width: 32px; height: 32px; border-radius: 8px; font-weight: 800; font-size: 0.875rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ad-user-avatar--farmer   { background: #dcfce7; color: #166534; }
.ad-user-avatar--buyer    { background: #dbeafe; color: #1e40af; }
.ad-user-avatar--exporter { background: #fef3c7; color: #92400e; }
.ad-role-badge { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.ad-role-badge--farmer   { background: #dcfce7; color: #166534; }
.ad-role-badge--buyer    { background: #dbeafe; color: #1e40af; }
.ad-role-badge--exporter { background: #fef3c7; color: #92400e; }
.ad-verify-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ad-verify-dot--yes { background: #16a34a; }
.ad-verify-dot--no  { background: #dc2626; }

/* ── Progress bars ─────────────────────────────────────────────────────────── */
.ad-bar-track { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.ad-bar-fill  { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* ── Score bubble ──────────────────────────────────────────────────────────── */
.ad-score-dot { display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-size: 0.75rem; font-weight: 800; padding: 3px 8px; }
.ad-score-dot--high { background: #dcfce7; color: #166534; }
.ad-score-dot--mid  { background: #fef3c7; color: #92400e; }

/* ── Auction ───────────────────────────────────────────────────────────────── */
.ad-alert-pulse { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; animation: adPulse 1.4s ease-in-out infinite; flex-shrink: 0; }
@keyframes adPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }
.ad-participants { font-size: 0.9375rem; font-weight: 800; color: var(--on-surface); }
.ad-closing-badge { font-size: 0.8125rem; font-weight: 700; color: #b91c1c; }

/* ── Transactions ──────────────────────────────────────────────────────────── */
.ad-tx-type { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.ad-tx-type--buy    { background: #dbeafe; color: #1e40af; }
.ad-tx-type--sell   { background: #dcfce7; color: #166534; }
.ad-tx-type--invest { background: #fef3c7; color: #92400e; }

/* ── Severity badges ───────────────────────────────────────────────────────── */
.ad-severity-badge { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.ad-severity-badge--low      { background: #dbeafe; color: #1e40af; }
.ad-severity-badge--medium   { background: #fef3c7; color: #92400e; }
.ad-severity-badge--high     { background: #fee2e2; color: #b91c1c; }
.ad-severity-badge--critical { background: #b91c1c; color: #fff; }

/* ── Compliance ────────────────────────────────────────────────────────────── */
.ad-compliance-label { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.ad-compliance-pct   { font-size: 0.8125rem; font-weight: 800; }

/* ── Market overview ───────────────────────────────────────────────────────── */
.ad-mini-chart { display: flex; align-items: flex-end; gap: 3px; height: 52px; background: var(--surface-low); border-radius: 8px; padding: 6px; }
.ad-mini-bar   { flex: 1; background: var(--green); border-radius: 2px 2px 0 0; opacity: 0.75; }
.ad-anomaly-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.ad-anomaly-row:last-child { border-bottom: none; }
.ad-anomaly-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.ad-anomaly-dot--danger  { background: #ef4444; }
.ad-anomaly-dot--warning { background: #f59e0b; }

/* ── System health ─────────────────────────────────────────────────────────── */
.ad-health-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--surface-low); }
.ad-health-row:last-child { border-bottom: none; }
.ad-health-indicator { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.ad-health-indicator--success { background: #16a34a; }
.ad-health-indicator--warning { background: #f59e0b; }
.ad-health-indicator--danger  { background: #ef4444; }
.ad-health-label { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }

/* ── AI Insights ───────────────────────────────────────────────────────────── */
.ad-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 6px; border: 1px solid; }
.ad-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.ad-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.ad-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.ad-insight-card--danger  { background: #fff1f2; border-color: #fecdd3; }
.ad-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.ad-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Smart alerts ──────────────────────────────────────────────────────────── */
.ad-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.ad-alert-row:last-child { border-bottom: none; }
.ad-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.ad-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.ad-toggle--on { background: var(--green); }
.ad-toggle--on i { transform: translateX(14px); }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.ad-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.ad-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.ad-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.ad-fab:hover { background: var(--green-grad); }
.ad-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.ad-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.ad-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.ad-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.ad-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.ad-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.ad-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.ad-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.ad-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.ad-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.ad-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.ad-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.ad-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.ad-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.ad-prompt-chip:hover { background: var(--surface-mid); }
.ad-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.ad-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.ad-chatbot__input input:focus { border-color: var(--green); }
.ad-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.ad-chat-enter-active, .ad-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.ad-chat-enter-from, .ad-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .ad-rail { position: static; } }
@media (max-width: 767.98px)  { .ad-chatbot { width: calc(100vw - 3rem); max-width: 310px; } }
</style>
