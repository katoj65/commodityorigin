<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ title: String });

// ── Wallet data ────────────────────────────────────────────────
const overview = [
    { label: 'Balance',        value: '$24,850.00', trend: '▲ 8.4%',  helper: 'vs last month', badgeClass: 'bg-[#ECFDF5] text-[#1B6E4B]', icon: 'balance',  accent: 'border-l-[#1B6E4B]' },
    { label: 'Total Received', value: '$18,340.00', trend: '▲ 11.2%', helper: 'vs last month', badgeClass: 'bg-[#ECFDF5] text-[#2D7A55]', icon: 'received', accent: 'border-l-[#2D7A55]' },
    { label: 'Total Spent',    value: '$9,720.00',  trend: '▼ 3.1%',  helper: 'vs last month', badgeClass: 'bg-[#FEF2F2] text-[#C0392B]', icon: 'spent',    accent: 'border-l-[#C0392B]' },
    { label: 'Transactions',   value: '47',         trend: '+6',       helper: 'this month',    badgeClass: 'bg-[#EFF6FF] text-[#2563EB]', icon: 'count',    accent: 'border-l-[#2563EB]' },
];

const balanceSummary = [
    { label: 'Cash Balance',        value: '$24,850', raw: 24850, change: '+$1,240', pos: true  },
    { label: 'Marketplace Credits', value: '$1,480',  raw: 1480,  change: '+$320',   pos: true  },
    { label: 'Escrow Balance',      value: '$6,400',  raw: 6400,  change: '−$800',   pos: false },
    { label: 'Pending Settlements', value: '$3,200',  raw: 3200,  change: '2 items', pos: null  },
];

const totalRaw = balanceSummary.reduce((s, i) => s + i.raw, 0);
const balanceBars = [
    { color: 'bg-[#1B6E4B]', label: 'Cash' },
    { color: 'bg-[#C8862A]', label: 'Credits' },
    { color: 'bg-[#C0392B]', label: 'Escrow' },
    { color: 'bg-[#2563EB]', label: 'Pending' },
];
const barWidths = computed(() => balanceSummary.map(i => Math.round((i.raw / totalRaw) * 100)));

const transactions = [
    { date: 'Jun 2, 2026',  ref: 'TXN-8841', type: 'Deposit',    desc: 'Bank transfer deposit',       amount: '+$5,000.00', pos: true,  status: 'completed'  },
    { date: 'Jun 1, 2026',  ref: 'TXN-8839', type: 'Purchase',   desc: 'Sipi Falls AA — BOE-0441',    amount: '−$1,224.00', pos: false, status: 'completed'  },
    { date: 'May 30, 2026', ref: 'TXN-8820', type: 'Bid',        desc: 'Bid placed — BOE-0438',       amount: '−$837.00',   pos: false, status: 'processing' },
    { date: 'May 28, 2026', ref: 'TXN-8801', type: 'Sale',       desc: 'Lot sale — BOE-0412',         amount: '+$2,100.00', pos: true,  status: 'completed'  },
    { date: 'May 25, 2026', ref: 'TXN-8790', type: 'Withdrawal', desc: 'Withdrawal to bank',          amount: '−$2,000.00', pos: false, status: 'completed'  },
    { date: 'May 22, 2026', ref: 'TXN-8775', type: 'Settlement', desc: 'Trade settlement — BOE-0409', amount: '+$3,450.00', pos: true,  status: 'pending'    },
];

const txnStatusClass = {
    completed:  'bg-[#ECFDF5] text-[#2D7A55]',
    pending:    'bg-[#FFF8F0] text-[#C8862A]',
    processing: 'bg-[#EFF6FF] text-[#2563EB]',
    failed:     'bg-[#FEF2F2] text-[#C0392B]',
};

const txnTypeStyle = {
    Deposit:    { bg: 'bg-[#ECFDF5]', color: 'text-[#2D7A55]',  icon: 'in'     },
    Purchase:   { bg: 'bg-[#FFF8F0]', color: 'text-[#C8862A]',  icon: 'coffee' },
    Bid:        { bg: 'bg-[#EFF6FF]', color: 'text-[#2563EB]',  icon: 'bid'    },
    Sale:       { bg: 'bg-[#ECFDF5]', color: 'text-[#1B6E4B]',  icon: 'out'    },
    Withdrawal: { bg: 'bg-[#FEF2F2]', color: 'text-[#C0392B]',  icon: 'out'    },
    Settlement: { bg: 'bg-[#F0FBF5]', color: 'text-[#2D7A55]',  icon: 'settle' },
};

const paymentMethods = [
    { name: 'Stanbic Bank',     detail: '•••• 4821',       type: 'bank',   primary: true,  accent: 'border-l-[#1B6E4B]', iconBg: 'bg-[#F0FBF5]', iconColor: 'text-[#1B6E4B]' },
    { name: 'MTN Mobile Money', detail: '+256 77••• 4120', type: 'mobile', primary: false, accent: 'border-l-[#C8862A]', iconBg: 'bg-[#FFF8F0]', iconColor: 'text-[#C8862A]' },
    { name: 'Visa Card',        detail: '•••• 9034',       type: 'card',   primary: false, accent: 'border-l-[#2563EB]', iconBg: 'bg-[#EFF6FF]', iconColor: 'text-[#2563EB]' },
];

const statements = [
    { title: 'Account Statement',  desc: 'Monthly activity summary',       period: 'May 2026', icon: 'doc'   },
    { title: 'Transaction Report', desc: 'Full transaction history export', period: 'May 2026', icon: 'list'  },
    { title: 'Revenue Report',     desc: 'Earnings and sales breakdown',    period: 'Q1 2026',  icon: 'chart' },
    { title: 'Marketplace Report', desc: 'Buying and bidding activity',     period: 'Q1 2026',  icon: 'store' },
];
</script>

<template>
    <AppLayout :title="title || 'Wallet'">

        <!-- ── Page hero ───────────────────────── -->
        <div class="mb-4 overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
            <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <!-- Left: balance -->
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-[#E5E7EB] bg-[#F9FAFB] text-[#C8862A]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="2" y="6" width="20" height="14" rx="2.5"/>
                            <path d="M2 11h20"/>
                            <path d="M6 15.5h2"/>
                            <circle cx="17" cy="15.5" r="1.2" fill="currentColor" stroke="none"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-[#9CA3AF]">Available Balance</div>
                        <div class="flex items-baseline gap-2">
                            <span class="font-display text-[24px] font-bold leading-tight tracking-tight text-[#111827]">$24,850.00</span>
                            <span class="rounded bg-[#ECFDF5] px-1.5 py-0.5 font-mono text-[9px] font-medium text-[#1B6E4B]">▲ 8.4%</span>
                        </div>
                    </div>
                    <div class="hidden h-8 w-px bg-[#E5E7EB] sm:block mx-1"></div>
                    <div class="hidden sm:flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#2D7A55] pulse-green"></span>
                        <span class="font-mono text-[10px] text-[#6B7280]">Active</span>
                    </div>
                    <div class="hidden lg:flex items-center gap-1.5 ml-1">
                        <span class="font-mono text-[9px] text-[#9CA3AF]">1 USD =</span>
                        <span class="font-mono text-[10px] font-medium text-[#374151]">3,720 UGX</span>
                    </div>
                </div>
                <!-- Right: actions -->
                <div class="flex items-center gap-1.5">
                    <button class="hero-action-btn">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
                        Deposit
                    </button>
                    <button class="hero-action-btn">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12l7 7 7-7"/></svg>
                        Withdraw
                    </button>
                    <button class="hero-action-btn">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16V4"/><path d="M3 8l4-4 4 4"/><path d="M17 8v12"/><path d="M13 16l4 4 4-4"/></svg>
                        Transfer
                    </button>
                </div>
            </div>
        </div>

        <!-- ── KPI overview ────────────────────── -->
        <div class="mb-4 grid grid-cols-2 gap-2 xl:grid-cols-4">
            <div v-for="kpi in overview" :key="kpi.label"
                 class="flex items-center gap-3 rounded-xl border border-[#E5E7EB] border-l-2 bg-white px-3.5 py-3 transition-colors hover:bg-[#FDFAF5]"
                 :class="kpi.accent">
                <!-- Icon -->
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl" :class="kpi.badgeClass">
                    <!-- Balance: wallet -->
                    <svg v-if="kpi.icon === 'balance'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <rect x="2" y="6" width="20" height="14" rx="2.5"/>
                        <path d="M2 11h20"/>
                        <path d="M6 15.5h2.5"/>
                        <circle cx="17" cy="15.5" r="1.3" fill="currentColor" stroke="none"/>
                    </svg>
                    <!-- Received: arrow down into tray -->
                    <svg v-else-if="kpi.icon === 'received'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M12 3v13"/>
                        <path d="M6 11l6 6 6-6"/>
                        <path d="M3 20h18"/>
                    </svg>
                    <!-- Spent: arrow up from tray -->
                    <svg v-else-if="kpi.icon === 'spent'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M12 21V8"/>
                        <path d="M6 13l6-6 6 6"/>
                        <path d="M3 4h18"/>
                    </svg>
                    <!-- Count: stacked lines with count badge -->
                    <svg v-else class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M3 6h18"/>
                        <path d="M3 10h12"/>
                        <path d="M3 14h8"/>
                        <circle cx="19" cy="15" r="4" fill="currentColor" stroke="none" class="opacity-20"/>
                        <path d="M19 13v4M17 15h4" stroke-width="1.5"/>
                    </svg>
                </div>
                <!-- Text -->
                <div class="min-w-0">
                    <div class="font-mono text-[9px] uppercase tracking-[0.1em] text-[#9CA3AF]">{{ kpi.label }}</div>
                    <div class="font-display text-[18px] font-bold leading-tight tracking-tight text-[#111827]">{{ kpi.value }}</div>
                    <div class="flex items-center gap-1">
                        <span class="rounded px-1 py-0.5 font-mono text-[8px] font-medium" :class="kpi.badgeClass">{{ kpi.trend }}</span>
                        <span class="font-mono text-[9px] text-[#9CA3AF]">{{ kpi.helper }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Two-column layout ───────────────── -->
        <div class="flex flex-col gap-3 xl:flex-row xl:gap-4">

            <!-- Left column -->
            <div class="flex min-w-0 flex-1 flex-col gap-3">

                <!-- Recent Transactions -->
                <div class="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                    <div class="flex items-center justify-between border-b border-[#F3F4F6] px-4 py-3.5">
                        <div>
                            <h3 class="font-display text-[14px] font-bold tracking-tight text-[#111827]">Recent Transactions</h3>
                            <p class="mt-0.5 text-[11px] text-[#9CA3AF]">Last 30 days of account activity</p>
                        </div>
                        <button class="flex items-center gap-1 rounded-lg border border-[#E5E7EB] bg-[#F9FAFB] px-3 py-1.5 font-mono text-[10px] text-[#6B7280] transition-colors hover:border-[#C8862A]/40 hover:bg-[#FFF8F0] hover:text-[#C8862A]">
                            View all
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <!-- Transaction rows -->
                    <div class="divide-y divide-[#F3F4F6]">
                        <div v-for="t in transactions" :key="t.ref"
                             class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-[#FDFAF5]">
                            <!-- Type icon -->
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl"
                                 :class="txnTypeStyle[t.type]?.bg ?? 'bg-[#F3F4F6]'">
                                <svg v-if="txnTypeStyle[t.type]?.icon === 'in'" class="h-4 w-4" :class="txnTypeStyle[t.type]?.color" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5"/><path d="M5 12l7 7 7-7"/></svg>
                                <svg v-else-if="txnTypeStyle[t.type]?.icon === 'out'" class="h-4 w-4" :class="txnTypeStyle[t.type]?.color" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12l7-7 7 7"/></svg>
                                <svg v-else-if="txnTypeStyle[t.type]?.icon === 'coffee'" class="h-4 w-4" :class="txnTypeStyle[t.type]?.color" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 8h1a4 4 0 010 8h-1"/><path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/><path d="M6 2v2"/><path d="M10 2v2"/><path d="M14 2v2"/></svg>
                                <svg v-else-if="txnTypeStyle[t.type]?.icon === 'bid'" class="h-4 w-4" :class="txnTypeStyle[t.type]?.color" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.5 2.5l7 7-10 10-7-7 10-10z"/><path d="M2 22l5-5"/></svg>
                                <svg v-else class="h-4 w-4" :class="txnTypeStyle[t.type]?.color" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
                            </div>
                            <!-- Description -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="truncate text-[13px] font-medium text-[#111827]">{{ t.desc }}</span>
                                </div>
                                <div class="mt-0.5 flex items-center gap-2 font-mono text-[10px] text-[#9CA3AF]">
                                    <span>{{ t.ref }}</span>
                                    <span class="text-[#D1D5DB]">·</span>
                                    <span>{{ t.date }}</span>
                                </div>
                            </div>
                            <!-- Type badge -->
                            <span class="hidden flex-shrink-0 rounded-md px-1.5 py-0.5 font-mono text-[9px] sm:inline-block"
                                  :class="(txnTypeStyle[t.type]?.bg ?? 'bg-[#F3F4F6]') + ' ' + (txnTypeStyle[t.type]?.color ?? 'text-[#6B7280]')">
                                {{ t.type }}
                            </span>
                            <!-- Amount + status -->
                            <div class="flex-shrink-0 text-right">
                                <div class="font-display text-[14px] font-semibold leading-tight"
                                     :class="t.pos ? 'text-[#1B6E4B]' : 'text-[#C0392B]'">
                                    {{ t.amount }}
                                </div>
                                <span class="mt-0.5 inline-block rounded px-1.5 py-0.5 font-mono text-[8px] uppercase tracking-[0.06em]"
                                      :class="txnStatusClass[t.status]">
                                    {{ t.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Summary -->
                <div class="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                    <div class="flex items-center justify-between border-b border-[#F3F4F6] px-4 py-3">
                        <div>
                            <h3 class="font-display text-[14px] font-bold tracking-tight text-[#111827]">Balance Summary</h3>
                            <p class="font-mono text-[9px] text-[#9CA3AF]">Portfolio breakdown · changes vs last month</p>
                        </div>
                        <span class="font-display text-[15px] font-bold text-[#111827]">$35,930</span>
                    </div>
                    <!-- Stacked bar -->
                    <div class="px-4 pt-3">
                        <div class="flex h-1.5 overflow-hidden rounded-full">
                            <div v-for="(bar, i) in balanceBars" :key="bar.label"
                                 class="transition-all" :class="bar.color"
                                 :style="{ width: barWidths[i] + '%' }">
                            </div>
                        </div>
                        <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-0.5">
                            <div v-for="(bar, i) in balanceBars" :key="bar.label" class="flex items-center gap-1">
                                <span class="h-1.5 w-1.5 flex-shrink-0 rounded-sm" :class="bar.color"></span>
                                <span class="font-mono text-[9px] text-[#6B7280]">{{ bar.label }} {{ barWidths[i] }}%</span>
                            </div>
                        </div>
                    </div>
                    <!-- Items list -->
                    <div class="divide-y divide-[#F3F4F6] px-4 pb-2 pt-3">
                        <div v-for="(item, i) in balanceSummary" :key="item.label"
                             class="flex items-center gap-3 border-l-2 py-2 pl-3 transition-colors hover:bg-[#FDFAF5]"
                             :class="balanceBars[i].color.replace('bg-', 'border-l-')">
                            <div class="min-w-0 flex-1">
                                <div class="font-mono text-[9px] uppercase tracking-[0.08em] text-[#9CA3AF]">{{ item.label }}</div>
                            </div>
                            <div class="font-display text-[15px] font-bold leading-none tracking-tight text-[#111827]">{{ item.value }}</div>
                            <span class="w-[64px] flex-shrink-0 text-right font-mono text-[10px]"
                                  :class="item.pos === true ? 'text-[#1B6E4B]' : item.pos === false ? 'text-[#C0392B]' : 'text-[#C8862A]'">
                                <span v-if="item.pos === true">▲ </span>
                                <span v-else-if="item.pos === false">▼ </span>{{ item.change }}
                            </span>
                        </div>
                    </div>
                </div>

            </div><!-- /left column -->

            <!-- Right sidebar -->
            <div class="flex w-full flex-shrink-0 flex-col gap-3 xl:w-72">

                <!-- Statements & Reports -->
                <div class="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                    <div class="border-b border-[#F3F4F6] px-4 py-3.5">
                        <h3 class="font-display text-[14px] font-bold tracking-tight text-[#111827]">Statements & Reports</h3>
                        <p class="mt-0.5 text-[11px] text-[#9CA3AF]">Download your account records</p>
                    </div>
                    <div class="divide-y divide-[#F3F4F6]">
                        <div v-for="s in statements" :key="s.title"
                             class="flex items-center gap-3 px-3.5 py-3 transition-colors hover:bg-[#FDFAF5]">
                            <!-- Icon -->
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-[#F3F4F6] text-[#6B7280]">
                                <svg v-if="s.icon === 'doc'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <svg v-else-if="s.icon === 'list'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                                <svg v-else-if="s.icon === 'chart'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-5 9 5v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                            <!-- Text -->
                            <div class="min-w-0 flex-1">
                                <div class="text-[12px] font-semibold text-[#111827]">{{ s.title }}</div>
                                <div class="font-mono text-[9px] text-[#9CA3AF]">{{ s.desc }}</div>
                            </div>
                            <!-- Period + download -->
                            <div class="flex flex-shrink-0 flex-col items-end gap-1.5">
                                <span class="rounded bg-[#F3F4F6] px-1.5 py-0.5 font-mono text-[8px] text-[#6B7280]">{{ s.period }}</span>
                                <button class="flex items-center gap-1 rounded bg-[#C8862A] px-2 py-0.5 font-mono text-[8px] text-white transition-colors hover:bg-[#E09B3A]">
                                    <svg class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12l7 7 7-7"/></svg>
                                    Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                    <div class="flex items-center justify-between border-b border-[#F3F4F6] px-4 py-3.5">
                        <div>
                            <h3 class="font-display text-[14px] font-bold tracking-tight text-[#111827]">Payment Methods</h3>
                            <p class="mt-0.5 text-[11px] text-[#9CA3AF]">Linked accounts &amp; cards</p>
                        </div>
                        <button class="flex items-center gap-1 rounded-lg border border-dashed border-[#C8862A]/50 px-2.5 py-1 font-mono text-[9px] text-[#C8862A] transition-colors hover:bg-[#FFF8F0]">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                            Add
                        </button>
                    </div>
                    <div class="divide-y divide-[#F3F4F6]">
                        <div v-for="m in paymentMethods" :key="m.name"
                             class="flex items-center gap-3 border-l-2 px-3.5 py-3 transition-colors hover:bg-[#FDFAF5]"
                             :class="m.accent">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl" :class="m.iconBg">
                                <svg v-if="m.type === 'bank'" class="h-4 w-4" :class="m.iconColor" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-5 9 5"/><path d="M3 9v11h18V9"/><path d="M9 22V12H15V22"/></svg>
                                <svg v-else-if="m.type === 'mobile'" class="h-4 w-4" :class="m.iconColor" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                                <svg v-else class="h-4 w-4" :class="m.iconColor" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[12px] font-semibold text-[#111827]">{{ m.name }}</div>
                                <div class="font-mono text-[10px] text-[#9CA3AF]">{{ m.detail }}</div>
                            </div>
                            <div class="flex flex-shrink-0 flex-col items-end gap-1">
                                <span v-if="m.primary" class="rounded-full bg-[#ECFDF5] px-1.5 py-0.5 font-mono text-[8px] font-medium text-[#1B6E4B]">Primary</span>
                                <button class="font-mono text-[9px] text-[#9CA3AF] transition-colors hover:text-[#C8862A]">Manage</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /right sidebar -->
        </div><!-- /two columns -->

    </AppLayout>
</template>

<style scoped>
.pulse-green { animation: pulseGreen 2s ease-in-out infinite; }
@keyframes pulseGreen { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }

.hero-action-btn { display:inline-flex;align-items:center;gap:.375rem;border:1px solid #E5E7EB;border-radius:.5rem;background:#fff;color:#374151;padding:.375rem .7rem;font-size:12px;font-weight:500;cursor:pointer;transition:all .15s;font-family:'IBM Plex Sans',sans-serif; }
.hero-action-btn:hover { background:#FFF8F0;border-color:rgba(200,134,42,.35);color:#C8862A; }
</style>
