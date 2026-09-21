<script setup>
import { reactive, ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
    MagicStick, Search, DataAnalysis, Finished, Checked,
    Download, Service, Lock, Coin, Link, Tickets, ShoppingCart, Sell,
    Document, Clock, TrendCharts, Paperclip, Memo, Microphone, Promotion,
    User, OfficeBuilding, Van, CoffeeCup, Operation, TopRight, ArrowRight,
} from '@element-plus/icons-vue';

/* ── Illustrative content — static, dummy-data mockup ported 1:1 from the
   approved "AI Commerce" design file to demonstrate the agentic e-commerce
   coffee-trading workspace. No backend calls. ──────────────────────────── */
const QUICK_PILLS = reactive([
    { key: 'find', label: 'Find Coffee', icon: Search, active: false },
    { key: 'buy', label: 'Buy Coffee', icon: ShoppingCart, active: true },
    { key: 'sell', label: 'Sell Coffee', icon: Sell, active: false },
    { key: 'rfq', label: 'Draft RFQ', icon: Document, active: false },
    { key: 'trades', label: 'Check My Trades', icon: Clock, active: false },
    { key: 'trends', label: 'Market Trends', icon: TrendCharts, active: false },
]);

function selectPill(pill) {
    QUICK_PILLS.forEach((p) => { p.active = p.key === pill.key; });
}

const AGENTS = [
    { icon: Search, label: 'Sourcing Agent', status: '✓ Done (4 lots)', tone: 'positive' },
    { icon: Coin, label: 'Negotiation & Pricing', status: '✓ -7.1% Vol Disc', tone: 'positive' },
    { icon: Checked, label: 'Compliance & EUDR', status: '✓ 100% Passed', tone: 'positive' },
    { icon: OfficeBuilding, label: 'Escrow & Settlement', status: 'Standby (Step 4)', tone: 'pending' },
    { icon: Van, label: 'Logistics Routing', status: 'Mombasa → DXB 14d', tone: 'neutral' },
];

const chatInput = ref('Offer $3.80/kg for 5 tonnes of Lot #LOT-00124 on CIF Jebel Ali terms');
</script>

<template>
    <MainLayout title="AI Assistant">
        <div class="chat-page">
            <div class="chat-workspace">
                <!-- CENTER PANEL: chat workspace -->
                <main class="chat-panel-col chat-panel-col--center">
                    <div class="chat-canvas__head">
                        <div class="chat-canvas__identity">
                            <div class="chat-canvas__avatar"><el-icon :size="20"><CoffeeCup /></el-icon></div>
                            <div>
                                <div class="chat-canvas__name-row">
                                    <span class="chat-canvas__name">Bean Origin AI</span>
                                    <span class="chat-canvas__agent-badge"><span class="chat-left__dot" />AI Commerce Agent</span>
                                </div>
                                <span class="chat-canvas__desc">Discover · Compare · Trade Commodities</span>
                            </div>
                        </div>
                        <div class="chat-canvas__actions">
                            <button type="button" class="chat-text-btn"><el-icon :size="14"><Download /></el-icon> Export Deal</button>
                            <button type="button" class="chat-specialist-btn"><el-icon :size="14"><Service /></el-icon> Connect Specialist</button>
                        </div>
                    </div>

                    <div class="chat-stream">
                        <!-- Turn 1: user -->
                        <div class="chat-row chat-row--user">
                            <div class="chat-bubble chat-bubble--user">
                                <p>I need 20 tonnes of Ugandan Robusta, Screen 18 or higher, for delivery to Dubai Jebel Ali next month.</p>
                                <div class="chat-bubble__meta"><span>12:41 PM</span><span>·</span><span>RFQ Query</span></div>
                            </div>
                            <div class="chat-avatar chat-avatar--user"><el-icon :size="15"><User /></el-icon></div>
                        </div>

                        <!-- Turn 1: AI -->
                        <div class="chat-row chat-row--ai">
                            <div class="chat-avatar chat-avatar--ai"><el-icon :size="17"><MagicStick /></el-icon></div>
                            <div class="chat-turn">
                                <div class="chat-reasoning-pill">
                                    <el-icon :size="14" color="var(--dp-primary)"><Finished /></el-icon>
                                    <span>Searched 42 active inventory records · 4 matched Screen 18+ · Cross-referenced Dubai logistics corridor ✓</span>
                                </div>
                                <p class="chat-narrative">
                                    I found 4 export-ready Robusta Lots matching your <strong>Screen 18</strong> specification and <strong>Dubai Jebel Ali</strong> delivery requirement. Here are the two top recommended options ready for immediate contract or offer:
                                </p>

                                <div class="chat-lot-grid">
                                    <div class="chat-lot-card">
                                        <div class="chat-lot-card__top">
                                            <div>
                                                <div class="chat-lot-card__badges">
                                                    <span class="chat-tag chat-tag--positive chat-tag--sm">Top Match 98%</span>
                                                    <span class="chat-mono chat-lot-card__id">LOT-00124</span>
                                                </div>
                                                <h3 class="chat-lot-card__title">Uganda Robusta — Mukono Basin</h3>
                                            </div>
                                            <div class="chat-lot-card__price">
                                                <span class="chat-mono">$4.20<small>/kg</small></span>
                                                <p>FOB Mombasa</p>
                                            </div>
                                        </div>
                                        <div class="chat-lot-card__detail">
                                            <div class="chat-lot-thumb"><el-icon :size="18"><CoffeeCup /></el-icon></div>
                                            <div class="chat-lot-card__detail-text">
                                                <span class="chat-lot-card__spec">Grade 1 · Screen 18 · Natural</span>
                                                <span class="chat-lot-card__coop">Mukono High Ridge Co-op</span>
                                                <div class="chat-lot-card__stats">
                                                    <span class="chat-mono chat-lot-card__score">83.5 SCAA</span>
                                                    <span>· 10,000 kg Avail</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chat-lot-card__seller">
                                            <span>Uganda Coffee Traders</span>
                                            <span class="chat-lot-card__trust"><el-icon :size="12"><Checked /></el-icon>Escrow 5.0★</span>
                                        </div>
                                        <div class="chat-lot-card__actions">
                                            <button type="button" class="chat-btn-plain">View Lot</button>
                                            <button type="button" class="chat-btn-primary">Buy Now</button>
                                            <button type="button" class="chat-btn-plain">Offer</button>
                                        </div>
                                    </div>

                                    <div class="chat-lot-card">
                                        <div class="chat-lot-card__top">
                                            <div>
                                                <div class="chat-lot-card__badges">
                                                    <span class="chat-tag chat-tag--neutral chat-tag--sm">Alt Match 91%</span>
                                                    <span class="chat-mono chat-lot-card__id">LOT-00131</span>
                                                </div>
                                                <h3 class="chat-lot-card__title">Uganda Robusta — Mubende Ridge</h3>
                                            </div>
                                            <div class="chat-lot-card__price chat-lot-card__price--muted">
                                                <span class="chat-mono">$4.10<small>/kg</small></span>
                                                <p>FOB Mombasa</p>
                                            </div>
                                        </div>
                                        <div class="chat-lot-card__detail">
                                            <div class="chat-lot-thumb"><el-icon :size="18"><CoffeeCup /></el-icon></div>
                                            <div class="chat-lot-card__detail-text">
                                                <span class="chat-lot-card__spec">Grade 1 · Screen 18+ · Natural</span>
                                                <span class="chat-lot-card__coop">Mubende Exporters Union</span>
                                                <div class="chat-lot-card__stats">
                                                    <span class="chat-mono chat-lot-card__score">82.0 SCAA</span>
                                                    <span>· 15,000 kg Avail</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chat-lot-card__seller">
                                            <span>Kampala Coffee Exporters</span>
                                            <span class="chat-lot-card__trust chat-lot-card__trust--muted"><el-icon :size="12"><Checked /></el-icon>Verified Exporter</span>
                                        </div>
                                        <div class="chat-lot-card__actions">
                                            <button type="button" class="chat-btn-plain">View Lot</button>
                                            <button type="button" class="chat-btn-primary">Buy Now</button>
                                            <button type="button" class="chat-btn-plain">Offer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Turn 2: user -->
                        <div class="chat-row chat-row--user">
                            <div class="chat-bubble chat-bubble--user">
                                <p>Compare the two lots and show me the price breakdown if I take 5 tonnes of Lot #LOT-00124.</p>
                                <div class="chat-bubble__meta"><span>12:43 PM</span><span>·</span><span>Comparison &amp; Quote</span></div>
                            </div>
                            <div class="chat-avatar chat-avatar--user"><el-icon :size="15"><User /></el-icon></div>
                        </div>

                        <!-- Turn 2: AI -->
                        <div class="chat-row chat-row--ai">
                            <div class="chat-avatar chat-avatar--ai"><el-icon :size="17"><DataAnalysis /></el-icon></div>
                            <div class="chat-turn">
                                <p class="chat-narrative">
                                    Here is the side-by-side technical grading specification comparison. Note that <strong>Lot #LOT-00124</strong> scores higher in cup uniformity and carries a lower moisture profile suited for transit across the Indian Ocean:
                                </p>

                                <div class="chat-table-wrap">
                                    <table class="chat-table">
                                        <thead>
                                            <tr><th>Metric</th><th class="chat-table__selected-head">Lot #LOT-00124 (Selected)</th><th>Lot #LOT-00131</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>Available Volume</td><td>10,000 kg</td><td>15,000 kg</td></tr>
                                            <tr><td>Screen Spec</td><td>Screen 18 Exact (94% retention)</td><td>Screen 18+ (89% retention)</td></tr>
                                            <tr><td>Moisture Content</td><td class="chat-table__accent">11.8% (Export Optimal)</td><td>12.2%</td></tr>
                                            <tr><td>SCAA Sensory Score</td><td class="chat-table__accent chat-table__accent--bold">83.5 pts</td><td>82.0 pts</td></tr>
                                            <tr><td>Spot Unit Price</td><td>$4.20 / kg</td><td>$4.10 / kg</td></tr>
                                            <tr><td>EUDR Deforestation Check</td><td class="chat-table__accent"><el-icon :size="13"><Checked /></el-icon>100% Geofenced GPS</td><td>Verified Exporter Plot</td></tr>
                                            <tr><td>Transit to Dubai (Jebel Ali)</td><td>14 Days (Mombasa Direct)</td><td>14 Days</td></tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="chat-po-card">
                                    <div class="chat-po-card__head">
                                        <span class="chat-po-card__title"><el-icon :size="16" color="var(--dp-primary)"><Tickets /></el-icon>Purchase Order Summary (Draft Contract)</span>
                                        <span class="chat-tag chat-tag--emphasis chat-mono">Action Confirmation Required</span>
                                    </div>
                                    <div class="chat-po-breakdown">
                                        <div class="chat-po-row"><span>Product &amp; Lot:</span><strong>Uganda Robusta (Lot #LOT-00124)</strong></div>
                                        <div class="chat-po-row"><span>Contract Quantity:</span><strong>5,000 kg (Hermetic Big Bags)</strong></div>
                                        <div class="chat-po-row">
                                            <span>Tier Negotiated Unit Price:</span>
                                            <span class="chat-po-row__price">
                                                <span class="chat-po-row__strike">$4.20</span>
                                                <strong class="chat-mono">$3.90 / kg</strong>
                                                <span class="chat-tag chat-tag--positive chat-tag--sm">-7.1% Vol Discount</span>
                                            </span>
                                        </div>
                                        <div class="chat-po-row"><span>Merchandise Subtotal:</span><strong class="chat-mono">$19,500.00 USD</strong></div>
                                        <div class="chat-po-row"><span>Est. Freight (Mombasa → Jebel Ali):</span><strong class="chat-mono">$1,450.00 USD</strong></div>
                                        <div class="chat-po-row chat-po-row--total"><span>Total Contract Invoiced to Escrow:</span><strong class="chat-mono">$20,950.00 USD</strong></div>
                                        <div class="chat-po-row chat-po-row--footnote">
                                            <span>Designated Custody: <strong>Stanbic Bank Escrow</strong></span>
                                            <span>Seller: <strong>Uganda Coffee Traders</strong></span>
                                        </div>
                                    </div>
                                    <p class="chat-narrative">Would you like me to reserve this 5,000 kg allocation and initiate the institutional escrow contract?</p>
                                    <div class="chat-po-card__buttons">
                                        <button type="button" class="chat-btn-primary"><el-icon :size="14"><Lock /></el-icon> Confirm Purchase &amp; Open Escrow</button>
                                        <button type="button" class="chat-btn-plain"><el-icon :size="14"><Coin /></el-icon> Counter-Offer ($3.70/kg)</button>
                                        <button type="button" class="chat-btn-text">Edit Parameters</button>
                                        <button type="button" class="chat-btn-danger">Cancel</button>
                                    </div>
                                    <div class="chat-sources">
                                        <span class="chat-label">Sources:</span>
                                        <span class="chat-source-link"><el-icon :size="11"><Link /></el-icon>Lot #LOT-00124 Master Record</span>
                                        <span>·</span>
                                        <span class="chat-source-link"><el-icon :size="11"><Link /></el-icon>Mombasa-Dubai Freight Feed</span>
                                        <span>·</span>
                                        <span class="chat-source-link"><el-icon :size="11"><Link /></el-icon>Stanbic Escrow Schedule</span>
                                    </div>
                                </div>

                                <div class="chat-workflow-status">
                                    <span class="chat-workflow-status__pulse" />
                                    <span class="chat-workflow-status__step">Step 3 of 5:</span>
                                    <span>Awaiting Buyer Confirmation · Funds held in smart escrow upon execution.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="chat-quick-pills">
                        <button
                            v-for="pill in QUICK_PILLS"
                            :key="pill.key"
                            type="button"
                            class="chat-quick-pill"
                            :class="{ 'chat-quick-pill--active': pill.active }"
                            @click="selectPill(pill)"
                        >
                            <el-icon :size="13"><component :is="pill.icon" /></el-icon> {{ pill.label }}
                        </button>
                    </div>

                </main>

                <!-- RIGHT PANEL: commerce context drawer -->
                <aside class="chat-panel-col chat-panel-col--right">
                    <div class="chat-search">
                        <el-icon :size="14" class="chat-search__icon"><Search /></el-icon>
                        <input type="text" class="chat-search__input" placeholder="Search lots, contracts, RFQs..." />
                    </div>

                    <div class="chat-right__section">
                        <span class="chat-label">Current Specification</span>
                        <div class="chat-spec-box">
                            <div class="chat-spec-row"><span>Target Volume:</span><strong class="chat-mono">20,000 kg</strong></div>
                            <div class="chat-spec-row"><span>Variety / Grade:</span><strong>Uganda Robusta, Scr 18+</strong></div>
                            <div class="chat-spec-row"><span>Discharge Port:</span><strong>Jebel Ali (AEJEA)</strong></div>
                            <div class="chat-spec-row"><span>Incoterm Target:</span><strong class="chat-mono chat-spec-row__accent">CIF / FOB Mombasa</strong></div>
                            <div class="chat-spec-row"><span>Target Execution:</span><strong>Within 30 days</strong></div>
                        </div>
                    </div>

                    <div class="chat-right__section">
                        <div class="chat-right__section-head">
                            <span class="chat-label">Active Selection</span>
                            <span class="chat-tag chat-tag--positive chat-tag--sm">Primary</span>
                        </div>
                        <div class="chat-active-lot">
                            <div class="chat-active-lot__top">
                                <div class="chat-lot-thumb"><el-icon :size="18"><CoffeeCup /></el-icon></div>
                                <div>
                                    <span class="chat-active-lot__title">Lot #LOT-00124</span>
                                    <span class="chat-active-lot__sub">Mukono Basin Cooperative</span>
                                </div>
                            </div>
                            <div class="chat-active-lot__stats">
                                <div class="chat-active-lot__stat"><span class="chat-label">Cup Score</span><strong class="chat-mono chat-spec-row__accent">83.50 pts</strong></div>
                                <div class="chat-active-lot__stat"><span class="chat-label">Inventory</span><strong class="chat-mono">10,000 kg</strong></div>
                            </div>
                            <div class="chat-sparkline">
                                <div class="chat-sparkline__head"><span>ICE Robusta Diff (30d)</span><span class="chat-mono chat-spec-row__accent">+0.18 $/lb</span></div>
                                <svg class="chat-sparkline__svg" viewBox="0 0 160 30">
                                    <path d="M0,22 Q20,18 40,24 T80,12 T120,16 T160,6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" fill="none" />
                                    <circle cx="160" cy="6" r="3" />
                                </svg>
                            </div>
                            <div class="chat-active-lot__ready"><el-icon :size="14"><Checked /></el-icon>Ready for Purchase Order / Offer</div>
                        </div>
                    </div>

                    <div class="chat-right__section">
                        <span class="chat-label">Agent Orchestration</span>
                        <div class="chat-agent-list">
                            <div v-for="agent in AGENTS" :key="agent.label" class="chat-agent-row">
                                <span class="chat-agent-row__left"><el-icon :size="15" color="var(--dp-primary)"><component :is="agent.icon" /></el-icon>{{ agent.label }}</span>
                                <span class="chat-mono chat-agent-row__status" :class="`chat-agent-row__status--${agent.tone}`">{{ agent.status }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="chat-right__section">
                        <span class="chat-label">Quick Actions</span>
                        <div class="chat-quick-actions">
                            <button type="button" class="chat-quick-action">
                                <span><el-icon :size="16" color="var(--dp-primary)"><Document /></el-icon>View Quality Certificate PDF</span>
                                <el-icon :size="15"><TopRight /></el-icon>
                            </button>
                            <button type="button" class="chat-quick-action">
                                <span><el-icon :size="16" color="var(--dp-primary)"><Operation /></el-icon>Escrow Vault Calculator</span>
                                <el-icon :size="15"><ArrowRight /></el-icon>
                            </button>
                            <button type="button" class="chat-quick-action chat-quick-action--specialist">
                                <span><el-icon :size="16"><Service /></el-icon>Connect with Trade Specialist</span>
                            </button>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="chat-composer">
                <div class="chat-composer__grid">
                    <div class="chat-composer__slot">
                        <div class="chat-input-wrap">
                            <div class="chat-input-wrap__left">
                                <button type="button" class="chat-icon-btn" title="Attach Document"><el-icon :size="16"><Paperclip /></el-icon></button>
                                <button type="button" class="chat-icon-btn" title="Attach Reference Lot / Contract"><el-icon :size="16"><Memo /></el-icon></button>
                            </div>
                            <input v-model="chatInput" type="text" class="chat-input" />
                            <div class="chat-input-wrap__right">
                                <button type="button" class="chat-icon-btn" title="Voice Input"><el-icon :size="16"><Microphone /></el-icon></button>
                                <button type="button" class="chat-send-btn"><el-icon :size="15"><Promotion /></el-icon></button>
                            </div>
                        </div>
                        <div class="chat-composer__footer">
                            <span>Powered by Bean Origin Sovereign Multi-Agent Protocol · ICE Futures linked</span>
                            <span>Press <kbd>Return ↵</kbd> to submit command</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.chat-page { display: flex; flex-direction: column; gap: 16px; padding-bottom: 100px; margin-top: -28px; }
.chat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--dp-on-surface-variant); }
.chat-mono { font-family: var(--dp-font-mono); }

@keyframes chat-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }

.chat-tag { display: inline-flex; align-items: center; gap: 4px; padding: 2px 9px; border-radius: 5px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
.chat-tag--sm { padding: 1px 7px; font-size: 9px; }
.chat-tag--emphasis { background: var(--dp-primary-container); color: var(--dp-on-primary-container); }
.chat-tag--positive { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }
.chat-tag--neutral { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

/* Workspace grid */
.chat-workspace { display: grid; grid-template-columns: 1fr 300px; gap: 16px; align-items: start; }
.chat-panel-col { background: var(--dp-surface-container-lowest); border-radius: 12px; }
.chat-left__dot { width: 6px; height: 6px; border-radius: 50%; background: var(--dp-primary); flex-shrink: 0; }

/* Center panel */
.chat-panel-col--center { display: flex; flex-direction: column; overflow: hidden; }
.chat-canvas__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; flex-wrap: wrap; }
.chat-canvas__identity { display: flex; align-items: center; gap: 10px; }
.chat-canvas__avatar { width: 36px; height: 36px; border-radius: 9px; background: var(--dp-primary-container); color: var(--dp-on-primary-container); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.chat-canvas__name-row { display: flex; align-items: center; gap: 8px; }
.chat-canvas__name { font-size: 13px; font-weight: 800; color: var(--dp-on-surface); }
.chat-canvas__agent-badge { display: flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 700; color: var(--dp-on-secondary-container); background: var(--dp-secondary-container); padding: 2px 9px; border-radius: 999px; }
.chat-canvas__desc { font-size: 11px; color: var(--dp-on-surface-variant); font-weight: 600; }
.chat-canvas__actions { display: flex; align-items: center; gap: 8px; }
.chat-text-btn { display: flex; align-items: center; gap: 6px; height: 32px; padding: 0 10px; border-radius: 8px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 12px; font-weight: 600; cursor: pointer; }
.chat-text-btn:hover { background: var(--dp-surface-container-low); }
.chat-specialist-btn { display: flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px; border-radius: 8px; border: none; background: var(--dp-primary-container); color: var(--dp-on-primary-container); font-size: 12px; font-weight: 700; cursor: pointer; }
.chat-specialist-btn:hover { opacity: 0.9; }

.chat-stream { padding: 4px 20px 20px; display: flex; flex-direction: column; gap: 22px; }
.chat-row { display: flex; gap: 10px; }
.chat-row--user { justify-content: flex-end; }
.chat-row--ai { justify-content: flex-start; align-items: flex-start; }
.chat-avatar { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
.chat-avatar--user { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.chat-avatar--ai { width: 32px; height: 32px; border-radius: 9px; background: var(--dp-primary); color: var(--dp-on-primary); }

.chat-bubble { max-width: 34rem; }
.chat-bubble--user { background: var(--dp-primary); color: var(--dp-on-primary); border-radius: 12px 12px 0 12px; padding: 11px 14px; }
.chat-bubble--user p { margin: 0; font-size: 12.5px; line-height: 1.55; font-weight: 500; }
.chat-bubble__meta { display: flex; align-items: center; justify-content: flex-end; gap: 6px; margin-top: 6px; font-size: 10px; font-family: var(--dp-font-mono); opacity: 0.75; }

.chat-turn { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 14px; }
.chat-reasoning-pill { display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border-radius: 999px; background: var(--dp-surface-container-low); font-size: 11px; font-weight: 500; color: var(--dp-on-surface-variant); width: fit-content; }
.chat-narrative { margin: 0; font-size: 12px; line-height: 1.6; color: var(--dp-on-surface); }
.chat-narrative strong { color: var(--dp-primary); }

.chat-lot-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.chat-lot-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.chat-lot-card__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.chat-lot-card__badges { display: flex; align-items: center; gap: 6px; margin-bottom: 5px; }
.chat-lot-card__id { font-size: 10px; color: var(--dp-on-surface-variant); }
.chat-lot-card__title { margin: 0; font-size: 12px; font-weight: 800; color: var(--dp-on-surface); line-height: 1.3; }
.chat-lot-card__price { text-align: right; }
.chat-lot-card__price span { font-size: 14px; font-weight: 800; color: var(--dp-primary); }
.chat-lot-card__price small { font-size: 10px; font-weight: 500; color: var(--dp-on-surface-variant); }
.chat-lot-card__price p { margin: 1px 0 0; font-size: 9px; text-transform: uppercase; font-weight: 700; color: var(--dp-on-surface-variant); }
.chat-lot-card__price--muted span { color: var(--dp-on-surface); }
.chat-lot-card__detail { display: flex; align-items: center; gap: 10px; background: var(--dp-surface-container-low); padding: 8px; border-radius: 8px; }
.chat-lot-thumb { width: 44px; height: 44px; border-radius: 8px; background: var(--dp-surface-container-high); color: var(--dp-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.chat-lot-card__detail-text { display: flex; flex-direction: column; font-size: 11px; line-height: 1.35; }
.chat-lot-card__spec { font-weight: 700; color: var(--dp-on-surface); }
.chat-lot-card__coop { color: var(--dp-on-surface-variant); font-size: 10px; }
.chat-lot-card__stats { display: flex; align-items: center; gap: 6px; margin-top: 3px; font-size: 10px; color: var(--dp-on-surface-variant); }
.chat-lot-card__score { color: var(--dp-primary); font-weight: 700; }
.chat-lot-card__seller { display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: var(--dp-on-surface-variant); font-weight: 600; }
.chat-lot-card__trust { display: flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; color: var(--dp-primary); }
.chat-lot-card__trust--muted { color: var(--dp-on-surface-variant); }
.chat-lot-card__actions { display: flex; align-items: center; gap: 6px; }

.chat-btn-primary { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 10px; border-radius: 8px; border: none; background: var(--dp-primary); color: var(--dp-on-primary); font-size: 11px; font-weight: 700; cursor: pointer; }
.chat-btn-primary:hover { opacity: 0.9; }
.chat-btn-plain { flex: 1; padding: 7px 10px; border-radius: 8px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface); font-size: 11px; font-weight: 700; cursor: pointer; }
.chat-btn-plain:hover { background: var(--dp-surface-container-high); }
.chat-btn-text { padding: 7px 10px; border-radius: 8px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 11.5px; font-weight: 600; cursor: pointer; }
.chat-btn-text:hover { color: var(--dp-on-surface); }
.chat-btn-danger { margin-left: auto; padding: 7px 10px; border-radius: 8px; border: none; background: transparent; color: var(--dp-error); font-size: 11.5px; font-weight: 600; cursor: pointer; }
.chat-btn-danger:hover { text-decoration: underline; }

.chat-table-wrap { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: 12px; overflow: hidden; }
.chat-table { width: 100%; border-collapse: collapse; font-size: 11px; }
.chat-table thead { background: var(--dp-surface-container-low); }
.chat-table th { text-align: left; padding: 9px 12px; font-size: 11px; font-weight: 700; color: var(--dp-on-surface); }
.chat-table__selected-head { color: var(--dp-primary); }
.chat-table td { padding: 8px 12px; color: var(--dp-on-surface-variant); font-weight: 500; }
.chat-table td:first-child { color: var(--dp-on-surface-variant); }
.chat-table td:nth-child(2) { color: var(--dp-on-surface); font-weight: 600; }
.chat-table__accent { color: var(--dp-primary) !important; font-weight: 700 !important; display: flex; align-items: center; gap: 4px; }
.chat-table__accent--bold { font-weight: 800 !important; }
.chat-table tbody tr:nth-child(even) { background: var(--dp-surface-container-low); }

.chat-po-card { background: var(--dp-surface-container-low); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.chat-po-card__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.chat-po-card__title { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; color: var(--dp-on-surface); }
.chat-po-breakdown { background: var(--dp-surface-container-lowest); padding: 13px; border-radius: 10px; display: flex; flex-direction: column; gap: 7px; }
.chat-po-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 11.5px; }
.chat-po-row span:first-child { color: var(--dp-on-surface-variant); }
.chat-po-row strong { color: var(--dp-on-surface); font-weight: 700; }
.chat-po-row__price { display: flex; align-items: center; gap: 7px; }
.chat-po-row__strike { font-size: 11px; text-decoration: line-through; color: var(--dp-on-surface-variant); }
.chat-po-row__price strong { color: var(--dp-primary); font-size: 13px; }
.chat-po-row--total { padding-top: 8px; margin-top: 2px; border-top: 1px solid var(--dp-outline-variant); }
.chat-po-row--total strong { font-size: 14px; color: var(--dp-primary); font-weight: 800; }
.chat-po-row--footnote { font-size: 10.5px; }
.chat-po-row--footnote strong { font-weight: 600; }
.chat-po-card__buttons { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.chat-po-card__buttons .chat-btn-primary, .chat-po-card__buttons .chat-btn-plain { flex: none; }

.chat-sources { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: 10.5px; color: var(--dp-on-surface-variant); }
.chat-source-link { display: inline-flex; align-items: center; gap: 4px; color: var(--dp-on-surface-variant); cursor: pointer; text-decoration: underline; }
.chat-source-link:hover { color: var(--dp-primary); }

.chat-workflow-status { display: flex; align-items: center; gap: 8px; padding: 9px 12px; border-radius: 8px; background: var(--dp-surface-container-low); font-size: 11px; color: var(--dp-on-surface-variant); }
.chat-workflow-status__pulse { width: 7px; height: 7px; border-radius: 50%; background: var(--dp-primary); animation: chat-pulse 1.6s ease-in-out infinite; flex-shrink: 0; }
.chat-workflow-status__step { font-weight: 700; color: var(--dp-on-surface); }

.chat-quick-pills { display: flex; align-items: center; gap: 6px; padding: 10px 18px 16px; overflow-x: auto; }
.chat-quick-pill { display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0; padding: 5px 11px; border-radius: 999px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface); font-size: 11px; font-weight: 600; cursor: pointer; }
.chat-quick-pill:hover { background: var(--dp-surface-container-high); }
.chat-quick-pill--active { background: var(--dp-secondary-container); color: var(--dp-on-secondary-container); }

/* Composer — a true fixed footer spanning the full page edge-to-edge (past
   the app sidebar); its inner content is constrained to the same grid
   tracks as .chat-workspace so it lines up with the center column only. */
.chat-composer {
    position: fixed;
    left: 288px;
    right: 0;
    bottom: 0;
    z-index: 30;
    background: var(--dp-surface-container-lowest);
}
.chat-composer__grid { display: grid; grid-template-columns: 1fr 300px; gap: 16px; padding: 12px 64px 14px; }
.chat-composer__slot { grid-column: 1; display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.chat-input-wrap { display: flex; align-items: center; gap: 4px; background: var(--dp-surface-container-low); border-radius: 12px; padding: 6px; }
.chat-input-wrap:focus-within { background: var(--dp-surface-container-high); }
.chat-input-wrap__left, .chat-input-wrap__right { display: flex; align-items: center; gap: 2px; }
.chat-icon-btn { width: 30px; height: 30px; border-radius: 8px; border: none; background: transparent; color: var(--dp-on-surface-variant); display: flex; align-items: center; justify-content: center; cursor: pointer; }
.chat-icon-btn:hover { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.chat-input { flex: 1; min-width: 0; background: transparent; border: none !important; padding: 0 8px; font-size: 12px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); }
.chat-input:focus, .chat-input:focus-visible { outline: none !important; box-shadow: none !important; border: none !important; }
.chat-send-btn { width: 34px; height: 34px; border-radius: 9px; border: none; background: var(--dp-primary); color: var(--dp-on-primary); display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
.chat-send-btn:hover { opacity: 0.9; }
.chat-composer__footer { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 10px; color: var(--dp-on-surface-variant); flex-wrap: wrap; }
.chat-composer__footer kbd { padding: 1px 5px; background: var(--dp-surface-container); border-radius: 4px; font-family: var(--dp-font-mono); font-size: 9px; }

/* Right panel */
.chat-panel-col--right { display: flex; flex-direction: column; gap: 16px; padding: 16px; }

.chat-search { position: relative; display: flex; align-items: center; }
.chat-search__icon { position: absolute; left: 10px; color: var(--dp-outline); }
.chat-search__input { width: 100%; height: 34px; padding: 0 10px 0 30px; border-radius: 8px; border: none; background: var(--dp-surface-container-low); font-size: 12px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); }
.chat-search__input:focus { outline: none; background: var(--dp-surface-container-high); }

.chat-right__section { display: flex; flex-direction: column; gap: 8px; }
.chat-right__section-head { display: flex; align-items: center; justify-content: space-between; }
.chat-spec-box { background: var(--dp-surface-container-low); padding: 11px; border-radius: 10px; display: flex; flex-direction: column; gap: 6px; }
.chat-spec-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; font-size: 11px; }
.chat-spec-row span { color: var(--dp-on-surface-variant); }
.chat-spec-row strong { text-align: right; color: var(--dp-on-surface); font-weight: 600; }
.chat-spec-row__accent { color: var(--dp-primary) !important; }

.chat-active-lot { background: var(--dp-surface-container-low); padding: 11px; border-radius: 10px; display: flex; flex-direction: column; gap: 10px; }
.chat-active-lot__top { display: flex; align-items: center; gap: 9px; }
.chat-active-lot__title { display: block; font-size: 12px; font-weight: 800; color: var(--dp-on-surface); }
.chat-active-lot__sub { display: block; font-size: 10px; color: var(--dp-on-surface-variant); }
.chat-active-lot__stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.chat-active-lot__stat { background: var(--dp-surface-container-lowest); padding: 7px 9px; border-radius: 8px; display: flex; flex-direction: column; gap: 2px; }
.chat-active-lot__stat strong { font-size: 12px; }
.chat-sparkline { background: var(--dp-surface-container-lowest); padding: 8px 9px; border-radius: 8px; display: flex; flex-direction: column; gap: 4px; }
.chat-sparkline__head { display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: var(--dp-on-surface-variant); font-weight: 600; }
.chat-sparkline__svg { width: 100%; height: 28px; stroke: var(--dp-primary); }
.chat-sparkline__svg circle { fill: var(--dp-primary); }
.chat-active-lot__ready { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: var(--dp-primary); }

.chat-agent-list { display: flex; flex-direction: column; gap: 6px; }
.chat-agent-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 7px 10px; border-radius: 7px; background: var(--dp-surface-container-low); }
.chat-agent-row__left { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; color: var(--dp-on-surface); }
.chat-agent-row__status { font-size: 10px; font-weight: 700; }
.chat-agent-row__status--positive { color: var(--dp-primary); }
.chat-agent-row__status--pending { color: var(--dp-on-surface-variant); }
.chat-agent-row__status--neutral { color: var(--dp-on-surface-variant); font-weight: 500; }

.chat-quick-actions { display: flex; flex-direction: column; gap: 7px; }
.chat-quick-action { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 9px 11px; border-radius: 8px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface); font-size: 11.5px; font-weight: 700; cursor: pointer; }
.chat-quick-action:hover { background: var(--dp-surface-container-high); }
.chat-quick-action span { display: flex; align-items: center; gap: 8px; }
.chat-quick-action--specialist { background: var(--dp-primary-container); color: var(--dp-on-primary-container); justify-content: center; }
.chat-quick-action--specialist:hover { opacity: 0.9; }

@media (max-width: 1279.98px) {
    .chat-composer { left: 0; }
    .chat-composer__grid { padding-left: 24px; padding-right: 24px; }
}
@media (max-width: 1024px) {
    .chat-workspace { grid-template-columns: 1fr; }
    .chat-panel-col--right { order: -1; }
    .chat-composer__grid { grid-template-columns: 1fr; }
    .chat-composer__slot { grid-column: 1; }
}
@media (max-width: 767.98px) {
    .chat-composer__grid { padding-left: 16px; padding-right: 16px; }
}
@media (max-width: 479.98px) {
    .chat-composer__grid { padding-left: 12px; padding-right: 12px; }
}
</style>
