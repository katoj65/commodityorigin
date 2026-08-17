<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Bell, Box, ChatDotRound, Check, Checked, Clock,
    CollectionTag, Connection, DataLine, Download,
    Medal, Message, Opportunity, Promotion,
    ShoppingCart, Star, TrendCharts, Van, View,
    Warning, WarningFilled,
} from '@element-plus/icons-vue';

/* ── Portfolio metrics ─────────────────────────────────────────── */
const portfolio = [
    { label: 'Total Invested',      value: '$142,500', sub: 'Capital deployed',       tone: 'primary', icon: CollectionTag, change: '' },
    { label: 'Current Value',       value: '$168,240', sub: 'Live portfolio value',    tone: 'success', icon: TrendCharts,   change: '+$25,740' },
    { label: 'Profit / Loss',       value: '+$25,740', sub: 'Unrealised gain',         tone: 'success', icon: DataLine,      change: '' },
    { label: 'ROI',                 value: '18.1%',    sub: 'Return on investment',    tone: 'success', icon: Star,          change: '↑ vs 12% avg' },
    { label: 'Active Investments',  value: '7',        sub: 'Open positions',          tone: 'info',    icon: Box,           change: '' },
    { label: 'Token Holdings',      value: '4,280',    sub: 'Coffee-backed tokens',    tone: 'warning', icon: Connection,    change: '' },
];

/* ── Chart ─────────────────────────────────────────────────────── */
const chartRange = ref('30D');
const chartBars = [
    { label: 'W1', value: 88, roi: 55 }, { label: 'W2', value: 74, roi: 48 },
    { label: 'W3', value: 92, roi: 62 }, { label: 'W4', value: 68, roi: 44 },
    { label: 'W5', value: 96, roi: 70 }, { label: 'W6', value: 80, roi: 52 },
    { label: 'W7', value: 100,roi: 75 }, { label: 'W8', value: 85, roi: 60 },
];

/* ── Allocation ────────────────────────────────────────────────── */
const allocType   = [
    { label: 'Arabica',   pct: 52, color: '#004532' },
    { label: 'Robusta',   pct: 31, color: '#059669' },
    { label: 'Mixed',     pct: 17, color: '#a7f3d0' },
];
const allocRegion = [
    { label: 'Uganda',    pct: 58, color: '#004532' },
    { label: 'Ethiopia',  pct: 22, color: '#065f46' },
    { label: 'Brazil',    pct: 12, color: '#10b981' },
    { label: 'Other',     pct: 8,  color: '#d1fae5' },
];
const allocCategory = [
    { label: 'Lots',      pct: 48, color: '#004532' },
    { label: 'Tokens',    pct: 38, color: '#059669' },
    { label: 'Contracts', pct: 14, color: '#a7f3d0' },
];

/* ── Tokenised assets ──────────────────────────────────────────── */
const assets = ref([
    { id: 'TKN-001', name: 'Mount Elgon AA',      origin: 'Uganda',   type: 'Arabica', tokens: '1,200', value: '$6,240', roi: '+22.4%', roiUp: true,  status: 'Active'   },
    { id: 'TKN-002', name: 'Kyoga Robusta R1',    origin: 'Uganda',   type: 'Robusta', tokens: '2,500', value: '$4,700', roi: '+14.2%', roiUp: true,  status: 'Active'   },
    { id: 'TKN-003', name: 'Sipi Falls Natural',  origin: 'Uganda',   type: 'Arabica', tokens: '580',   value: '$3,538', roi: '+31.0%', roiUp: true,  status: 'Matured'  },
    { id: 'TKN-004', name: 'Yirgacheffe G1',      origin: 'Ethiopia', type: 'Arabica', tokens: 'None',  value: '$12,800',roi: '+8.5%',  roiUp: true,  status: 'Active'   },
    { id: 'TKN-005', name: 'Minas Gerais Fazenda',origin: 'Brazil',   type: 'Arabica', tokens: 'None',  value: '$9,600', roi: '-2.1%',  roiUp: false, status: 'Pending'  },
]);

/* ── Opportunities ─────────────────────────────────────────────── */
const opportunities = [
    { name: 'Uganda Robusta Tokens',       roi: '14–18%', risk: 'Low',    duration: '6 mo',  min: '$500',   demand: 'Extreme', tone: 'success' },
    { name: 'Rwenzori Arabica Premium',    roi: '18–24%', risk: 'Medium', duration: '9 mo',  min: '$1,000', demand: 'High',    tone: 'warning' },
    { name: 'Mount Elgon Specialty Lot',   roi: '22–30%', risk: 'Medium', duration: '12 mo', min: '$2,000', demand: 'High',    tone: 'warning' },
];

/* ── Returns & payouts ─────────────────────────────────────────── */
const returns = [
    { investment: 'Mount Elgon AA',     amount: '$12,000', ret: '+$2,688', status: 'Paid',     date: 'Apr 30', tone: 'success'  },
    { investment: 'Kyoga Robusta R1',   amount: '$8,500',  ret: '+$1,207', status: 'Upcoming', date: 'Jun 15', tone: 'primary'  },
    { investment: 'Sipi Falls Natural', amount: '$5,000',  ret: '+$1,550', status: 'Pending',  date: 'Jul 01', tone: 'secondary'},
    { investment: 'Yirgacheffe G1',     amount: '$14,000', ret: '+$1,190', status: 'Paid',     date: 'Mar 31', tone: 'success'  },
];

/* ── Risk analysis ─────────────────────────────────────────────── */
const risks = [
    { label: 'Market Risk',    level: 'Low',    pct: 22, tone: 'success' },
    { label: 'Price Volatility',level: 'Medium', pct: 55, tone: 'warning' },
    { label: 'Supply Risk',    level: 'Low',    pct: 18, tone: 'success' },
    { label: 'Export Risk',    level: 'Medium', pct: 42, tone: 'warning' },
];

/* ── Recent transactions ───────────────────────────────────────── */
const transactions = [
    { asset: 'Mount Elgon AA',      type: 'Invest', amount: '$12,000', date: 'May 02', tone: 'primary'  },
    { asset: 'Kyoga Robusta R1',    type: 'Buy',    amount: '$8,500',  date: 'Apr 28', tone: 'success'  },
    { asset: 'Sipi Falls Natural',  type: 'Invest', amount: '$5,000',  date: 'Apr 20', tone: 'primary'  },
    { asset: 'Yirgacheffe G1',      type: 'Sell',   amount: '$3,200',  date: 'Apr 15', tone: 'danger'   },
    { asset: 'Minas Gerais Fazenda',type: 'Buy',    amount: '$9,600',  date: 'Apr 10', tone: 'success'  },
];

/* ── Market intelligence ───────────────────────────────────────── */
const marketBars = [34, 52, 44, 68, 58, 72, 80, 66, 88, 76, 92, 84];

/* ── Alerts ────────────────────────────────────────────────────── */
const alerts = reactive({ priceMove: true, roiChange: true, newOpportunity: false, marketRisk: true });

/* ── AI insights ───────────────────────────────────────────────── */
const insights = [
    { text: 'Robusta investments showing strong 14–18% annual growth — consider increasing allocation.', tone: 'success' },
    { text: 'High demand in UAE and Germany markets increasing ROI potential for Arabica lots.',          tone: 'primary' },
    { text: 'Diversification across Uganda, Ethiopia, and Brazil regions recommended to reduce risk.',   tone: 'warning' },
];

/* ── Chatbot ───────────────────────────────────────────────────── */
const chatOpen  = ref(false);
const chatInput = ref('');
const chatMsgs  = ref([
    { role: 'bot', text: 'Hi! I\'m your Bean Origin Investment Advisor. I can help you identify high-ROI coffee assets and manage portfolio risk. What would you like to know?' },
]);
const prompts = ['Which coffee should I invest in?', 'What is the ROI potential?', 'Is this a good investment?', 'What are the risks?'];
const sendChat = () => {
    const t = chatInput.value.trim();
    if (!t) return;
    chatMsgs.value.push({ role: 'user', text: t });
    chatInput.value = '';
    setTimeout(() => chatMsgs.value.push({ role: 'bot', text: 'Uganda Robusta tokens offer the best risk-adjusted returns right now, with 14–18% ROI and low market risk. Mount Elgon Arabica is your highest performer at 22.4% ROI.' }), 700);
};
const usePrompt = (p) => { chatInput.value = p; sendChat(); };
</script>

<template>
    <AppLayout title="Investor Dashboard" full-width flush :show-banner="false">
        <Head title="Investor Dashboard" />

        <div class="iv-page">

            <!-- ── Header ────────────────────────────────────────────── -->
            <div class="iv-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 py-3 flex-wrap">
                        <div>
                            <h1 class="iv-title mb-0">Investor Dashboard</h1>
                            <p class="iv-subtitle mb-0">Track and grow your coffee-backed investments</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn iv-btn-primary btn-sm"><el-icon><ShoppingCart /></el-icon> Invest Now</button>
                            <button class="btn iv-btn-outline btn-sm"><el-icon><CollectionTag /></el-icon> My Portfolio</button>
                            <button class="btn iv-btn-outline btn-sm"><el-icon><Download /></el-icon> Reports</button>
                            <button class="btn iv-btn-ghost btn-sm"><el-icon><ChatDotRound /></el-icon> Ask Advisor</button>
                        </div>
                    </div>
                    <!-- Badge strip -->
                    <div class="d-flex flex-wrap gap-2 pb-2">
                        <span class="iv-hbadge"><el-icon><Connection /></el-icon> Tokenised Assets</span>
                        <span class="iv-hbadge iv-hbadge--soft"><el-icon><Check /></el-icon> Verified Investments</span>
                        <span class="iv-hbadge iv-hbadge--outline"><el-icon><Van /></el-icon> Traceable Commodities</span>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3 px-lg-4 py-3">

                <!-- ── 2. Portfolio Overview ──────────────────────────── -->
                <div class="row g-2 mb-3">
                    <div v-for="metric in portfolio" :key="metric.label" class="col-6 col-sm-4 col-lg-2">
                        <div class="iv-kpi h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="iv-kpi__label">{{ metric.label }}</span>
                                <span class="iv-kpi__icon" :class="`iv-kpi__icon--${metric.tone}`">
                                    <el-icon><component :is="metric.icon" /></el-icon>
                                </span>
                            </div>
                            <div class="iv-kpi__value" :class="`iv-kpi__value--${metric.tone}`">{{ metric.value }}</div>
                            <div class="iv-kpi__sub">{{ metric.sub }}</div>
                            <div v-if="metric.change" class="iv-kpi__change">{{ metric.change }}</div>
                        </div>
                    </div>
                </div>

                <!-- ── Main 2-column grid ────────────────────────────── -->
                <div class="row g-3">

                    <!-- Left column -->
                    <div class="col-12 col-xxl-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- 3. Performance Chart -->
                            <div class="iv-card">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div class="iv-card-title">
                                        <el-icon class="iv-card-icon"><TrendCharts /></el-icon>
                                        Portfolio Performance
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button v-for="r in ['7D','30D','90D','1Y']" :key="r"
                                            class="btn" :class="chartRange === r ? 'iv-btn-primary' : 'iv-btn-outline'"
                                            @click="chartRange = r">{{ r }}</button>
                                    </div>
                                </div>
                                <div class="iv-chart">
                                    <div class="iv-chart-bars">
                                        <div v-for="bar in chartBars" :key="bar.label" class="iv-chart-col">
                                            <div class="iv-chart-pair">
                                                <div class="iv-chart-bar iv-chart-bar--value"  :style="{ height: `${bar.value}%` }"></div>
                                                <div class="iv-chart-bar iv-chart-bar--roi" :style="{ height: `${bar.roi}%` }"></div>
                                            </div>
                                            <span class="iv-chart-label">{{ bar.label }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-2">
                                    <div class="d-flex gap-3">
                                        <span class="iv-legend"><span class="iv-legend-dot iv-legend-dot--value"></span>Portfolio Value</span>
                                        <span class="iv-legend"><span class="iv-legend-dot iv-legend-dot--roi"></span>ROI Trend</span>
                                    </div>
                                    <div class="iv-chart-note">
                                        <el-icon><Opportunity /></el-icon>
                                        Consistent growth since Q1 — portfolio up 18.1% YTD.
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Asset Allocation -->
                            <div class="iv-card">
                                <div class="iv-card-title mb-3">
                                    <el-icon class="iv-card-icon"><CollectionTag /></el-icon>
                                    Asset Allocation
                                </div>
                                <div class="row g-3">
                                    <div v-for="(alloc, key) in { 'By Coffee Type': allocType, 'By Region': allocRegion, 'By Category': allocCategory }" :key="key" class="col-12 col-md-4">
                                        <div class="iv-alloc-block">
                                            <div class="iv-alloc-title">{{ key }}</div>
                                            <div class="d-flex flex-column gap-1 mt-2">
                                                <div v-for="item in alloc" :key="item.label" class="iv-alloc-row">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="iv-alloc-label">{{ item.label }}</span>
                                                        <span class="iv-alloc-pct">{{ item.pct }}%</span>
                                                    </div>
                                                    <div class="iv-bar-track">
                                                        <div class="iv-bar-fill" :style="{ width: `${item.pct}%`, background: item.color }"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Tokenised Assets -->
                            <div class="iv-card p-0 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
                                    <div class="iv-card-title">
                                        <el-icon class="iv-card-icon"><Connection /></el-icon>
                                        Tokenised Coffee Assets
                                    </div>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:.65rem;">{{ assets.length }} assets</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 iv-table">
                                        <thead>
                                            <tr>
                                                <th>Lot / Asset</th>
                                                <th>Origin</th>
                                                <th>Type</th>
                                                <th>Tokens</th>
                                                <th>Value</th>
                                                <th>ROI</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="asset in assets" :key="asset.id" class="iv-table-row">
                                                <td>
                                                    <div class="iv-asset-name">{{ asset.name }}</div>
                                                    <div class="iv-asset-id">{{ asset.id }}</div>
                                                </td>
                                                <td class="iv-td-muted">{{ asset.origin }}</td>
                                                <td>
                                                    <span class="iv-type-badge" :class="asset.type === 'Arabica' ? 'iv-type-badge--amber' : 'iv-type-badge--blue'">{{ asset.type }}</span>
                                                </td>
                                                <td class="iv-td-muted">{{ asset.tokens }}</td>
                                                <td class="fw-semibold">{{ asset.value }}</td>
                                                <td>
                                                    <span :class="asset.roiUp ? 'iv-up' : 'iv-down'" style="font-size:.8125rem;font-weight:700;">{{ asset.roi }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="asset.status === 'Active'  ? 'bg-success-subtle text-success-emphasis border border-success-subtle' :
                                                                asset.status === 'Matured' ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle' :
                                                                'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'">
                                                        {{ asset.status }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-sm iv-btn-primary px-2"><el-icon><View /></el-icon></button>
                                                        <button class="btn btn-sm iv-btn-outline px-2"><el-icon><ShoppingCart /></el-icon></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 6. Investment Opportunities -->
                            <div class="iv-card">
                                <div class="iv-card-title mb-3">
                                    <el-icon class="iv-card-icon"><Medal /></el-icon>
                                    Investment Opportunities
                                </div>
                                <div class="row g-3">
                                    <div v-for="opp in opportunities" :key="opp.name" class="col-12 col-md-4">
                                        <div class="iv-opp-card h-100">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div class="iv-opp-name">{{ opp.name }}</div>
                                                <span class="badge" style="font-size:.6rem;"
                                                    :class="opp.risk === 'Low' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'">
                                                    {{ opp.risk }} Risk
                                                </span>
                                            </div>
                                            <div class="row g-1 mb-3">
                                                <div class="col-6"><div class="iv-detail-cell"><span>Exp. ROI</span><strong class="iv-up">{{ opp.roi }}</strong></div></div>
                                                <div class="col-6"><div class="iv-detail-cell"><span>Duration</span><strong>{{ opp.duration }}</strong></div></div>
                                                <div class="col-6"><div class="iv-detail-cell"><span>Min. Invest</span><strong>{{ opp.min }}</strong></div></div>
                                                <div class="col-6"><div class="iv-detail-cell"><span>Demand</span><strong>{{ opp.demand }}</strong></div></div>
                                            </div>
                                            <button class="btn iv-btn-primary btn-sm w-100"><el-icon><ShoppingCart /></el-icon> Invest</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Returns & Payouts -->
                            <div class="iv-card p-0 overflow-hidden">
                                <div class="px-3 py-2 border-bottom">
                                    <div class="iv-card-title">
                                        <el-icon class="iv-card-icon"><CollectionTag /></el-icon>
                                        Returns &amp; Payouts
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 iv-table">
                                        <thead>
                                            <tr>
                                                <th>Investment</th>
                                                <th>Amount</th>
                                                <th>Return</th>
                                                <th>Status</th>
                                                <th>Payout Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="r in returns" :key="r.investment + r.date">
                                                <td class="iv-asset-name">{{ r.investment }}</td>
                                                <td class="iv-td-muted">{{ r.amount }}</td>
                                                <td class="iv-up fw-semibold">{{ r.ret }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="`bg-${r.tone}-subtle text-${r.tone}-emphasis border border-${r.tone}-subtle`">
                                                        {{ r.status }}
                                                    </span>
                                                </td>
                                                <td class="iv-td-muted">{{ r.date }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 10. Recent Transactions -->
                            <div class="iv-card p-0 overflow-hidden">
                                <div class="px-3 py-2 border-bottom">
                                    <div class="iv-card-title">
                                        <el-icon class="iv-card-icon"><Clock /></el-icon>
                                        Recent Transactions
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 iv-table">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="tx in transactions" :key="tx.asset + tx.date">
                                                <td class="iv-asset-name">{{ tx.asset }}</td>
                                                <td>
                                                    <span class="badge rounded-pill" style="font-size:.65rem;"
                                                        :class="`bg-${tx.tone}-subtle text-${tx.tone}-emphasis border border-${tx.tone}-subtle`">
                                                        {{ tx.type }}
                                                    </span>
                                                </td>
                                                <td class="fw-semibold">{{ tx.amount }}</td>
                                                <td class="iv-td-muted">{{ tx.date }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right column -->
                    <div class="col-12 col-xxl-4">
                        <div class="iv-rail">

                            <!-- 7. Market Intelligence -->
                            <div class="iv-card">
                                <div class="iv-card-title mb-3">
                                    <el-icon class="iv-card-icon"><TrendCharts /></el-icon>
                                    Market Intelligence
                                </div>
                                <div class="iv-mini-chart mb-3">
                                    <div v-for="(v, i) in marketBars" :key="i" class="iv-mini-bar" :style="{ height: `${v}%` }"></div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div class="iv-intel-row">
                                        <span>Coffee Price Trend</span>
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">Rising ↑</span>
                                    </div>
                                    <div class="iv-intel-row">
                                        <span>Demand Trend</span>
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">Strong ↑</span>
                                    </div>
                                    <div class="iv-intel-row">
                                        <span>Supply Risk</span>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">Medium</span>
                                    </div>
                                    <div class="iv-intel-row">
                                        <span>Market Volatility</span>
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.65rem;">Low</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Risk Analysis -->
                            <div class="iv-card">
                                <div class="iv-card-title mb-3">
                                    <el-icon class="iv-card-icon"><Warning /></el-icon>
                                    Risk Analysis
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div v-for="risk in risks" :key="risk.label">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="iv-risk-label">{{ risk.label }}</span>
                                            <span class="iv-risk-level" :class="`iv-risk-level--${risk.tone}`">{{ risk.level }}</span>
                                        </div>
                                        <div class="iv-bar-track">
                                            <div class="iv-bar-fill"
                                                :style="{ width: `${risk.pct}%`,
                                                          background: risk.tone === 'success' ? '#004532' : '#f59e0b' }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Smart Alerts -->
                            <div class="iv-card">
                                <div class="iv-card-title mb-3">
                                    <el-icon class="iv-card-icon"><Bell /></el-icon>
                                    Smart Alerts
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="(val, key) in alerts" :key="key" class="iv-alert-row">
                                        <span>{{ { priceMove: 'Price Movement', roiChange: 'ROI Changes', newOpportunity: 'New Opportunities', marketRisk: 'Market Risks' }[key] }}</span>
                                        <button class="iv-toggle" :class="{ 'iv-toggle--on': alerts[key] }" @click="alerts[key] = !alerts[key]"><i></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. AI Insights -->
                            <div>
                                <div class="iv-card-title mb-2">
                                    <el-icon class="iv-card-icon"><Opportunity /></el-icon>
                                    AI Investment Insights
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="ins in insights" :key="ins.text" class="iv-insight-card" :class="`iv-insight-card--${ins.tone}`">
                                        <el-icon class="iv-insight-icon"><Opportunity /></el-icon>
                                        <p class="iv-insight-text">{{ ins.text }}</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /main grid -->
                <div class="pb-5"></div>
            </div>

            <!-- ── Floating Chatbot ──────────────────────────────────── -->
            <div class="iv-fab-wrap">
                <Transition name="iv-chat">
                    <div v-if="chatOpen" class="iv-chatbot">
                        <div class="iv-chatbot__head">
                            <div class="iv-chatbot__identity">
                                <div class="iv-chatbot__avatar"><el-icon><ChatDotRound /></el-icon></div>
                                <div>
                                    <div class="iv-chatbot__name">Investment Advisor</div>
                                    <div class="iv-chatbot__status"><i></i> Online</div>
                                </div>
                            </div>
                            <button class="iv-chatbot__close" @click="chatOpen = false">×</button>
                        </div>
                        <div class="iv-chatbot__body">
                            <div v-for="(msg, i) in chatMsgs" :key="i" class="iv-chat-msg" :class="`iv-chat-msg--${msg.role}`">{{ msg.text }}</div>
                        </div>
                        <div class="iv-chatbot__prompts">
                            <button v-for="p in prompts" :key="p" class="iv-prompt-chip" @click="usePrompt(p)">{{ p }}</button>
                        </div>
                        <div class="iv-chatbot__input">
                            <input v-model="chatInput" placeholder="Ask your advisor…" @keydown.enter="sendChat">
                            <button @click="sendChat"><el-icon><Promotion /></el-icon></button>
                        </div>
                    </div>
                </Transition>
                <button class="iv-fab" @click="chatOpen = !chatOpen">
                    <el-icon><ChatDotRound /></el-icon>
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Tokens ────────────────────────────────────────────────────────────────── */
.iv-page {
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
.iv-header { background: var(--surface-white); border-bottom: 1px solid var(--surface-high); }
.iv-title    { font-size: 1.125rem; font-weight: 800; letter-spacing: -0.02em; }
.iv-subtitle { font-size: 0.8125rem; color: var(--on-surface-var); }
.iv-hbadge   { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,69,50,0.08); color: var(--green); border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 3px 10px; }
.iv-hbadge--soft    { background: #dcfce7; color: #166534; }
.iv-hbadge--outline { background: transparent; border: 1px solid var(--surface-high); color: var(--on-surface-var); }

/* ── Buttons ───────────────────────────────────────────────────────────────── */
.iv-btn-primary { background: var(--green); border-color: var(--green); color: var(--on-green); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.iv-btn-primary:hover { background: var(--green-grad); border-color: var(--green-grad); color: #fff; }
.iv-btn-outline { background: var(--surface-white); border-color: var(--surface-high); color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.iv-btn-outline:hover { background: var(--surface-low); }
.iv-btn-ghost   { background: var(--surface-mid); border-color: transparent; color: var(--on-surface); border-radius: 6px; font-size: 0.8125rem; font-weight: 600; padding: 6px 14px; display: inline-flex; align-items: center; gap: 5px; }
.iv-btn-ghost:hover { background: var(--surface-high); }

/* ── KPI Cards ─────────────────────────────────────────────────────────────── */
.iv-kpi { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 10px; padding: 0.875rem; }
.iv-kpi__label { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); }
.iv-kpi__icon  { width: 22px; height: 22px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
.iv-kpi__icon--primary { background: #e0f2fe; color: #0369a1; }
.iv-kpi__icon--success { background: #dcfce7; color: #166534; }
.iv-kpi__icon--warning { background: #fef3c7; color: #92400e; }
.iv-kpi__icon--info    { background: #dbeafe; color: #1d4ed8; }
.iv-kpi__value { font-size: 1.0625rem; font-weight: 800; color: var(--on-surface); line-height: 1; margin: 4px 0 2px; }
.iv-kpi__value--primary { color: #0369a1; }
.iv-kpi__value--success { color: var(--green); }
.iv-kpi__value--warning { color: #92400e; }
.iv-kpi__value--info    { color: #1d4ed8; }
.iv-kpi__sub    { font-size: 0.625rem; color: var(--on-surface-var); }
.iv-kpi__change { font-size: 0.6875rem; font-weight: 700; color: var(--green); margin-top: 3px; }

/* ── Cards ─────────────────────────────────────────────────────────────────── */
.iv-card { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.iv-card-title { display: inline-flex; align-items: center; gap: 7px; font-size: 0.9375rem; font-weight: 700; color: var(--on-surface); }
.iv-card-icon  { width: 24px; height: 24px; border-radius: 6px; background: rgba(0,69,50,0.08); color: var(--green); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

/* ── Chart ─────────────────────────────────────────────────────────────────── */
.iv-chart { height: 130px; display: flex; align-items: flex-end; background: var(--surface-low); border-radius: 8px; padding: 8px; gap: 4px; }
.iv-chart-bars { display: flex; align-items: flex-end; gap: 4px; width: 100%; height: 100%; }
.iv-chart-col  { display: flex; flex-direction: column; align-items: center; gap: 3px; flex: 1; height: 100%; justify-content: flex-end; }
.iv-chart-pair { display: flex; gap: 2px; align-items: flex-end; height: 85%; }
.iv-chart-bar  { width: 10px; border-radius: 3px 3px 0 0; }
.iv-chart-bar--value { background: var(--green); }
.iv-chart-bar--roi   { background: #a7f3d0; }
.iv-chart-label { font-size: 0.625rem; color: var(--on-surface-var); font-weight: 600; }
.iv-legend { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; color: var(--on-surface-var); }
.iv-legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.iv-legend-dot--value { background: var(--green); }
.iv-legend-dot--roi   { background: #a7f3d0; }
.iv-chart-note { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--green); font-weight: 600; }

/* ── Allocation ────────────────────────────────────────────────────────────── */
.iv-alloc-block { background: var(--surface-low); border-radius: 8px; padding: 0.875rem; height: 100%; }
.iv-alloc-title { font-size: 0.75rem; font-weight: 700; color: var(--on-surface); }
.iv-alloc-label { font-size: 0.75rem; color: var(--on-surface-var); }
.iv-alloc-pct   { font-size: 0.75rem; font-weight: 700; color: var(--green); }
.iv-bar-track   { height: 6px; background: var(--surface-high); border-radius: 999px; overflow: hidden; }
.iv-bar-fill    { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

/* ── Table ─────────────────────────────────────────────────────────────────── */
.iv-table thead th { background: var(--surface-low); font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); padding: 8px 12px; border-bottom-color: var(--surface-high); white-space: nowrap; }
.iv-table tbody td { padding: 9px 12px; font-size: 0.8125rem; border-color: var(--surface-low); vertical-align: middle; }
.iv-table-row { transition: background 0.1s; }
.iv-table-row:hover { background: var(--surface-low); }
.iv-asset-name { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); }
.iv-asset-id   { font-size: 0.6875rem; color: var(--on-surface-var); }
.iv-td-muted   { color: var(--on-surface-var); font-size: 0.8125rem; }
.iv-type-badge { display: inline-flex; border-radius: 999px; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; }
.iv-type-badge--amber { background: #fef3c7; color: #92400e; }
.iv-type-badge--blue  { background: #dbeafe; color: #1e40af; }
.iv-up   { color: #166534; font-weight: 700; }
.iv-down { color: #b91c1c; font-weight: 700; }

/* ── Opportunities ─────────────────────────────────────────────────────────── */
.iv-opp-card { background: var(--surface-low); border: 1px solid var(--surface-high); border-radius: 10px; padding: 1rem; display: flex; flex-direction: column; }
.iv-opp-name { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); }
.iv-detail-cell { background: var(--surface-white); border: 1px solid var(--surface-high); border-radius: 6px; padding: 5px 8px; }
.iv-detail-cell span   { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--on-surface-var); display: block; }
.iv-detail-cell strong { font-size: 0.875rem; font-weight: 700; color: var(--on-surface); display: block; }

/* ── Right rail ────────────────────────────────────────────────────────────── */
.iv-rail { display: flex; flex-direction: column; gap: 1rem; position: sticky; top: 60px; }

/* Market intelligence */
.iv-mini-chart { display: flex; align-items: flex-end; gap: 3px; height: 60px; background: var(--surface-low); border-radius: 8px; padding: 6px; }
.iv-mini-bar   { flex: 1; background: var(--green); border-radius: 2px 2px 0 0; opacity: 0.7; }
.iv-intel-row  { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 5px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.iv-intel-row:last-child { border-bottom: none; }

/* Risk */
.iv-risk-label { font-size: 0.8125rem; color: var(--on-surface); font-weight: 600; }
.iv-risk-level { font-size: 0.6875rem; font-weight: 700; border-radius: 999px; padding: 2px 8px; }
.iv-risk-level--success { background: #dcfce7; color: #166534; }
.iv-risk-level--warning { background: #fef3c7; color: #92400e; }

/* Alerts */
.iv-alert-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 7px 0; border-bottom: 1px solid var(--surface-low); font-size: 0.8125rem; }
.iv-alert-row:last-child { border-bottom: none; }
.iv-toggle { width: 32px; height: 18px; border-radius: 999px; border: none; padding: 2px; background: var(--surface-high); cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
.iv-toggle i { display: block; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
.iv-toggle--on { background: var(--green); }
.iv-toggle--on i { transform: translateX(14px); }

/* AI Insights */
.iv-insight-card { display: flex; align-items: flex-start; gap: 9px; padding: 0.875rem; border-radius: 10px; border: 1px solid; }
.iv-insight-card--success { background: #f0fdf4; border-color: #bbf7d0; }
.iv-insight-card--primary { background: #f0f9ff; border-color: #bae6fd; }
.iv-insight-card--warning { background: #fffbeb; border-color: #fde68a; }
.iv-insight-icon { font-size: 14px; color: var(--green); flex-shrink: 0; margin-top: 1px; }
.iv-insight-text { font-size: 0.8125rem; font-weight: 600; color: var(--on-surface); line-height: 1.5; margin: 0; }

/* ── Chatbot ───────────────────────────────────────────────────────────────── */
.iv-fab-wrap { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 300; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
.iv-fab { width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--green); color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,69,50,0.35); cursor: pointer; }
.iv-fab:hover { background: var(--green-grad); }
.iv-chatbot { width: 310px; border-radius: 14px; overflow: hidden; background: #fff; border: 1px solid var(--surface-high); box-shadow: 0 8px 30px rgba(0,0,0,0.14); display: flex; flex-direction: column; }
.iv-chatbot__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--green); color: #fff; }
.iv-chatbot__identity { display: flex; align-items: center; gap: 10px; }
.iv-chatbot__avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.iv-chatbot__name { font-size: 0.875rem; font-weight: 700; }
.iv-chatbot__status { display: flex; align-items: center; gap: 5px; font-size: 0.625rem; opacity: 0.8; }
.iv-chatbot__status i { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.iv-chatbot__close { border: none; background: none; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1; cursor: pointer; }
.iv-chatbot__body { padding: 10px; background: var(--surface-low); max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.iv-chat-msg { font-size: 0.8125rem; padding: 8px 10px; border-radius: 10px; line-height: 1.5; max-width: 90%; }
.iv-chat-msg--bot  { background: #fff; color: var(--on-surface); border-radius: 10px 10px 10px 2px; }
.iv-chat-msg--user { background: var(--green); color: #fff; align-self: flex-end; border-radius: 10px 10px 2px 10px; }
.iv-chatbot__prompts { display: flex; flex-wrap: wrap; gap: 5px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.iv-prompt-chip { font-size: 0.6875rem; padding: 3px 9px; border-radius: 999px; background: var(--surface-low); border: 1px solid var(--surface-high); color: var(--on-surface); cursor: pointer; white-space: nowrap; }
.iv-prompt-chip:hover { background: var(--surface-mid); }
.iv-chatbot__input { display: flex; gap: 6px; padding: 8px 10px; border-top: 1px solid var(--surface-high); }
.iv-chatbot__input input { flex: 1; border: 1px solid var(--surface-high); border-radius: 7px; padding: 6px 9px; font-size: 0.8125rem; outline: none; }
.iv-chatbot__input input:focus { border-color: var(--green); }
.iv-chatbot__input button { border: none; background: var(--green); color: #fff; border-radius: 7px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
.iv-chat-enter-active, .iv-chat-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.iv-chat-enter-from, .iv-chat-leave-to { opacity: 0; transform: translateY(8px); }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1399.98px) { .iv-rail { position: static; } }
@media (max-width: 767.98px)  { .iv-chatbot { width: calc(100vw - 3rem); max-width: 310px; } }
</style>
