<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

/* ── Structural/visual port of the uploaded "Tokenised Coffee" mockup
   (code.html), restyled with this app's own --dp-* theme tokens rather
   than the mockup's own Tailwind palette. Everything below is
   illustrative dummy data — no Blockchain-tokenisation feature exists
   in this app yet (no route/model wiring beyond the Blockchain model
   itself), so unlike Batches/Lots, "Transfer Token" opens a purely
   visual dummy dialog (local toggle state only, no submit), mirroring
   the mockup's own open/close-only JS behaviour. ─────────────────────── */
const transferOpen = ref(false);

const kpiCards = [
    { icon: 'token', label: 'Tokenised Lots', value: '24 Lots', note: 'Across 6 verified origin clusters', progress: 78 },
    { icon: 'scale', label: 'Physical Mass Minted', value: '428.0 MT', note: '100% physically bonded & certified', progress: 92 },
    { icon: 'account_balance_wallet', label: 'Active Token Supply', value: '428,000', unit: 'TOK', note: 'Exact 1 Token : 1 kg Physical Ratio', progress: 100 },
    { icon: 'security', label: 'Total Custody Value', value: '$1,768,400', unit: 'USD', footRow: true },
];

const tabs = [
    { key: 'all', label: 'All Tokenised', count: 24 },
    { key: 'active', label: 'Active', count: 18 },
    { key: 'available', label: 'Available', count: 14 },
    { key: 'transferred', label: 'Transferred', count: 4 },
    { key: 'locked', label: 'Locked', count: 2 },
    { key: 'pending', label: 'Pending', count: 1 },
];
const activeTab = ref('all');

const tokens = [
    { id: 'TOK-UG-00421', chainRef: 'Arbitrum RWA #421', coffee: 'Uganda Robusta Sc. 18', lot: 'LOT-UG-001', lotSub: 'Mukono Central', mass: '20.0 MT', massKg: '20,000 kg', supply: '20,000 TOK', backed: '100% Backed', custody: 'Bean Origin Exporters', custodySub: 'Stanbic Silo B-14', status: 'active', statusLabel: 'Active', action: 'Inspect', actionTone: 'primary', selected: true },
    { id: 'TOK-UG-00418', chainRef: 'Arbitrum RWA #418', coffee: 'Bugisu Arabica AA Washed', lot: 'LOT-UG-003', lotSub: 'Mt. Elgon', mass: '12.0 MT', massKg: '12,000 kg', supply: '12,000 TOK', backed: '100% Backed', custody: 'Stanbic Custody', custodySub: 'Escrow Reserved', status: 'available', statusLabel: 'Available', action: 'View', actionTone: 'muted' },
    { id: 'TOK-UG-00412', chainRef: 'Arbitrum RWA #412', coffee: 'Rwenzori Natural Drugar', lot: 'LOT-UG-004', lotSub: 'Kasese Slopes', mass: '15.0 MT', massKg: '15,000 kg', supply: '15,000 TOK', backed: '100% Backed', custody: 'Dubai Coffee Trad.', custodySub: 'Settled Trade', status: 'transferred', statusLabel: 'Transferred', action: 'View', actionTone: 'muted' },
    { id: 'TOK-UG-00405', chainRef: 'Arbitrum RWA #405', coffee: 'West Nile FAQ Robusta', lot: 'LOT-UG-005', lotSub: 'Nebbi Basin', mass: '25.0 MT', massKg: '25,000 kg', supply: '25,000 TOK', backed: '100% Backed', custody: 'Bean Origin Escrow', custodySub: 'Smart Escrow Lock', status: 'locked', statusLabel: 'Locked', action: 'View', actionTone: 'muted' },
    { id: 'TOK-UG-00426', chainRef: 'Awaiting Auditor Sig', coffee: 'Masaka Highland Robusta', lot: 'LOT-UG-002', lotSub: 'Masaka Basin', mass: '35.0 MT', massKg: '35,000 kg', supply: '35,000 TOK', backed: null, backedNote: 'Validating deed', custody: 'Verification Stage', custodySub: 'Stanbic Escrow Lead', status: 'pending', statusLabel: 'Pending', action: 'Audit', actionTone: 'muted', pending: true },
];

const filteredTokens = computed(() => (activeTab.value === 'all' ? tokens : tokens.filter((t) => t.status === activeTab.value)));

const selectedTokenId = ref('TOK-UG-00421');
const selectedToken = computed(() => tokens.find((t) => t.id === selectedTokenId.value) ?? tokens[0]);

function selectToken(token) {
    selectedTokenId.value = token.id;
}

const provenance = [
    { icon: 'agriculture', label: 'FARM-1048 (Kawempe Organic)', tag: 'Origin Farm' },
    { icon: 'inventory_2', label: 'FC-UG-1048 (Mukono Station)', tag: 'Farm Coll.' },
    { icon: 'filter_vintage', label: 'BAT-UG-2021 (Wet Mill Process)', tag: 'Batch' },
    { icon: 'grid_view', label: 'LOT-UG-001 (Commercial Grade 18)', tag: 'Physical Source', tone: 'text' },
];

const milestones = [
    { label: 'Lot Verified', date: '18 Sep 2026' },
    { label: 'Stanbic Escrow Lodged', date: '19 Sep 2026' },
    { label: 'Tokens Minted & Audited', date: '21 Sep 2026' },
];
</script>

<template>
    <MainLayout title="Tokenised Coffee">
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
        </Head>

        <div class="tkc-page">
            <!-- ── Top context bar ──────────────────────────────────────── -->
            <div class="tkc-hero">
                <div class="tkc-hero__text">
                    <nav class="tkc-breadcrumb">
                        <span>My Coffee</span>
                        <span class="material-symbols-outlined">chevron_right</span>
                        <span class="tkc-breadcrumb__current">Tokenised Coffee</span>
                    </nav>
                    <h1 class="tkc-title">Tokenised Coffee <span class="tkc-badge">RWA Regulated</span></h1>
                    <p class="tkc-subtitle">Manage digital representations of verified coffee Lots, cryptographic custody, and immutable provenance deeds.</p>
                </div>
                <div class="tkc-hero__actions">
                    <button type="button" class="tkc-btn tkc-btn--muted">
                        <span class="material-symbols-outlined">neurology</span> Ask Bean Origin AI
                    </button>
                    <Link :href="route('inventory.lots')" class="tkc-btn tkc-btn--container">
                        <span class="material-symbols-outlined">grid_view</span> View Lots
                    </Link>
                    <button type="button" class="tkc-btn tkc-btn--primary">
                        <span class="material-symbols-outlined">add_circle</span> Tokenise Coffee
                    </button>
                </div>
            </div>

            <!-- ── Governance banner ─────────────────────────────────────── -->
            <div class="tkc-banner">
                <div class="tkc-banner__left">
                    <span class="material-symbols-outlined tkc-banner__icon">verified_user</span>
                    <div>
                        <div class="tkc-banner__head">
                            <span class="tkc-banner__eyebrow">Digital Asset Governance Rule</span>
                            <span class="tkc-chip">1 Token = 1 kg Physical Escrow</span>
                        </div>
                        <p class="tkc-banner__text"><strong>The Lot remains the physical coffee source of truth.</strong> Tokenisation creates an immutable digital title of ownership registered on-chain; it does not duplicate mass or create independent inventory. Title deeds are collateralized by physical coffee lodged in <strong>Stanbic Bank Bonded Silos</strong> under Swiss SPS Inspection oversight.</p>
                    </div>
                </div>
                <div class="tkc-flow">
                    <span>Farm</span><span class="material-symbols-outlined">trending_flat</span>
                    <span>Collection</span><span class="material-symbols-outlined">trending_flat</span>
                    <span>Batch</span><span class="material-symbols-outlined">trending_flat</span>
                    <span>Lot</span><span class="material-symbols-outlined">trending_flat</span>
                    <span class="tkc-flow__badge">Tokenised Coffee</span>
                    <span class="material-symbols-outlined">trending_flat</span>
                    <span class="tkc-muted">Exchange</span>
                </div>
            </div>

            <!-- ── KPI row ───────────────────────────────────────────────── -->
            <div class="tkc-kpi-grid">
                <div v-for="kpi in kpiCards" :key="kpi.label" class="tkc-kpi">
                    <div class="tkc-kpi__top">
                        <span class="tkc-kpi__label">{{ kpi.label }}</span>
                        <span class="material-symbols-outlined tkc-tone-text">{{ kpi.icon }}</span>
                    </div>
                    <div>
                        <div class="tkc-kpi__value">{{ kpi.value }}<span v-if="kpi.unit" class="tkc-kpi__unit">{{ kpi.unit }}</span></div>
                        <div v-if="kpi.note" class="tkc-kpi__note">{{ kpi.note }}</div>
                    </div>
                    <div v-if="kpi.progress" class="tkc-kpi__track"><div class="tkc-kpi__fill" :style="{ width: kpi.progress + '%' }"></div></div>
                    <div v-else-if="kpi.footRow" class="tkc-kpi__foot"><span>Escrow Coverage</span><span class="tkc-strong tkc-tone-text">100.0% Audited</span></div>
                </div>
            </div>

            <!-- ── Tabs & export ─────────────────────────────────────────── -->
            <div class="tkc-tabsbar">
                <div class="tkc-tabs">
                    <button v-for="tab in tabs" :key="tab.key" type="button" class="tkc-tab" :class="{ 'tkc-tab--active': activeTab === tab.key }" @click="activeTab = tab.key">
                        {{ tab.label }} <span class="tkc-tab__count">{{ tab.count }}</span>
                    </button>
                </div>
                <div class="tkc-export">
                    <span class="tkc-muted">Export Ledger:</span>
                    <button type="button" class="tkc-mini-btn">CSV</button>
                    <button type="button" class="tkc-mini-btn">PDF Audit</button>
                </div>
            </div>

            <!-- ── Filters ───────────────────────────────────────────────── -->
            <div class="tkc-filters">
                <div class="tkc-search">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" placeholder="Search by Token ID, Lot ID, Coffee, Farm or Owner..." readonly />
                </div>
                <select class="tkc-select"><option>All Origins (Uganda)</option><option>Mukono &amp; Central Basin</option><option>Bugisu (Mt. Elgon)</option><option>Rwenzori Mountains</option></select>
                <select class="tkc-select"><option>All Varieties</option><option>Robusta Screen 18 Washed</option><option>Bugisu Arabica AA</option></select>
                <select class="tkc-select"><option>Arbitrum One (L2 RWA)</option><option>Ethereum Mainnet Escrow</option><option>Bean Origin Enterprise Ledger</option></select>
                <select class="tkc-select"><option>All Statuses</option><option>Active &amp; Backed</option><option>Available on Desk</option><option>Transferred</option><option>Locked in Escrow</option></select>
                <button type="button" class="tkc-btn tkc-btn--muted">
                    <span class="material-symbols-outlined">tune</span> Save Preset
                </button>
            </div>

            <!-- ── Two-column workspace ─────────────────────────────────── -->
            <div class="tkc-grid">
                <!-- ── Left column ──────────────────────────────────────── -->
                <div class="tkc-col-main">
                    <div class="tkc-table-card">
                        <div class="tkc-table-card__head">
                            <div>
                                <div class="tkc-table-card__title">
                                    <span>Physical Lot Tokenised Registry</span>
                                    <span class="tkc-table__dot"></span>
                                </div>
                                <p class="tkc-muted tkc-small">Live decentralized ledger synchronised with bonded physical warehouse slips</p>
                            </div>
                            <span class="tkc-mono tkc-muted tkc-small">Sync: Block #18,492,084</span>
                        </div>
                        <div class="tkc-table-wrap">
                            <table class="tkc-table">
                                <colgroup>
                                    <col style="width: 14%" />
                                    <col style="width: 21%" />
                                    <col style="width: 11%" />
                                    <col style="width: 12%" />
                                    <col style="width: 15%" />
                                    <col style="width: 14%" />
                                    <col style="width: 13%" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Token ID</th>
                                        <th>Coffee &amp; Lot Ref</th>
                                        <th>Physical Mass</th>
                                        <th>Token Supply</th>
                                        <th>Legal Custody</th>
                                        <th>Status</th>
                                        <th class="tkc-table__end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="row in filteredTokens"
                                        :key="row.id"
                                        class="tkc-table__row"
                                        :class="{ 'tkc-table__row--active': row.id === selectedTokenId }"
                                        @click="selectToken(row)"
                                    >
                                        <td>
                                            <div class="tkc-mono tkc-strong" :class="row.pending ? 'tkc-muted' : 'tkc-tone-text'">
                                                <span class="material-symbols-outlined">{{ row.pending ? 'pending' : 'toll' }}</span>{{ row.id }}
                                            </div>
                                            <div class="tkc-muted tkc-small tkc-mono">{{ row.chainRef }}</div>
                                        </td>
                                        <td>
                                            <div class="tkc-strong">{{ row.coffee }}</div>
                                            <div class="tkc-muted tkc-small"><span class="tkc-tone-text tkc-strong">{{ row.lot }}</span> • {{ row.lotSub }}</div>
                                        </td>
                                        <td class="tkc-mono tkc-strong">
                                            {{ row.mass }}
                                            <div class="tkc-muted tkc-small">{{ row.massKg }}</div>
                                        </td>
                                        <td class="tkc-mono">
                                            <div class="tkc-strong">{{ row.supply }}</div>
                                            <div class="tkc-small" :class="row.backed ? 'tkc-tone-text' : 'tkc-muted'">{{ row.backed || row.backedNote }}</div>
                                        </td>
                                        <td>
                                            <div class="tkc-small tkc-strong">{{ row.custody }}</div>
                                            <div class="tkc-muted tkc-small">{{ row.custodySub }}</div>
                                        </td>
                                        <td><span class="tkc-status" :class="`tkc-status--${row.status}`">{{ row.statusLabel }}</span></td>
                                        <td class="tkc-table__end">
                                            <button type="button" class="tkc-action-btn" :class="`tkc-action-btn--${row.actionTone}`">{{ row.action }}</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tkc-table-card__foot">
                            <span class="tkc-muted tkc-small">Showing 1 to 5 of 24 collateralized tokens</span>
                            <div class="tkc-pagination">
                                <button type="button" class="tkc-page-btn" disabled>Previous</button>
                                <button type="button" class="tkc-page-btn tkc-page-btn--active">1</button>
                                <button type="button" class="tkc-page-btn">2</button>
                                <button type="button" class="tkc-page-btn">3</button>
                                <button type="button" class="tkc-page-btn">Next</button>
                            </div>
                        </div>
                    </div>

                    <!-- Collateral reserve proof -->
                    <div class="tkc-reserve">
                        <div class="tkc-reserve__left">
                            <span class="material-symbols-outlined">account_balance</span>
                            <div>
                                <div class="tkc-strong tkc-small">Independent Custody Escrow: Stanbic Bank Uganda</div>
                                <div class="tkc-muted tkc-small">All 428,000 TOK correspond 1:1 with audited warehouse warrants registered with the Uganda Capital Markets Authority.</div>
                            </div>
                        </div>
                        <button type="button" class="tkc-link-btn">Verify Auditor Ledger</button>
                    </div>
                </div>

                <!-- ── Right column: Token dossier ────────────────────────── -->
                <div class="tkc-col-side">
                    <div class="tkc-card">
                        <div class="tkc-dossier__head">
                            <div>
                                <div class="tkc-dossier__title-row">
                                    <span class="tkc-mono tkc-tone-text tkc-strong tkc-small">{{ selectedToken.id }}</span>
                                    <span class="tkc-status" :class="`tkc-status--${selectedToken.status}`">{{ selectedToken.statusLabel }}</span>
                                </div>
                                <h3 class="tkc-title-lg">{{ selectedToken.coffee }}</h3>
                                <p class="tkc-muted tkc-small">Cryptographic Title Deed • 100% Backed Physical Reserve</p>
                            </div>
                            <span class="material-symbols-outlined tkc-dossier__icon">token</span>
                        </div>

                        <div class="tkc-actions-grid">
                            <button type="button" class="tkc-btn tkc-btn--primary" @click="transferOpen = true">
                                <span class="material-symbols-outlined">swap_horiz</span> Transfer Token
                            </button>
                            <Link :href="route('inventory.lots')" class="tkc-btn tkc-btn--container">
                                <span class="material-symbols-outlined">grid_view</span> View Full Lot
                            </Link>
                            <button type="button" class="tkc-btn tkc-btn--muted tkc-btn--sm">
                                <span class="material-symbols-outlined">open_in_new</span> Blockchain Deed
                            </button>
                            <button type="button" class="tkc-btn tkc-btn--muted tkc-btn--sm">
                                <span class="material-symbols-outlined">download</span> Certificate (PDF)
                            </button>
                        </div>

                        <div class="tkc-panel tkc-anchor">
                            <div class="tkc-anchor__head">
                                <span class="tkc-eyebrow-sm"><span class="material-symbols-outlined">anchor</span> Physical Coffee Anchor</span>
                                <span class="tkc-mono tkc-tone-text tkc-strong tkc-small">{{ selectedToken.lot }}</span>
                            </div>
                            <div class="tkc-panel--grid2">
                                <div class="tkc-field-box">
                                    <span class="tkc-eyebrow-sm">Total Physical Mass</span>
                                    <span class="tkc-mono tkc-strong">20,000 kg <span class="tkc-muted tkc-small">(20 MT)</span></span>
                                    <span class="tkc-tone-text tkc-small tkc-strong">Tokenised: 20,000 TOK (100%)</span>
                                </div>
                                <div class="tkc-field-box">
                                    <span class="tkc-eyebrow-sm">Stanbic Warehouse Deed</span>
                                    <span class="tkc-mono tkc-strong tkc-small">WH-KLA-2026-0421</span>
                                    <span class="tkc-muted tkc-small">Silo Bay B-14, Kampala</span>
                                </div>
                            </div>
                            <div class="tkc-anchor__specs">
                                <div><span class="tkc-muted">Quality Grade:</span><span class="tkc-strong">Screen 18+ (92.4% uniform)</span></div>
                                <div><span class="tkc-muted">Moisture &amp; CQI:</span><span class="tkc-strong">11.2% Moisture • 84.50 CQI Washed</span></div>
                                <div><span class="tkc-muted">Harvest Crop:</span><span class="tkc-strong">Main Crop 2025/2026</span></div>
                            </div>
                        </div>

                        <div class="tkc-dossier__section">
                            <span class="tkc-card__title-plain">Provenance Lineage (Verified Chain)</span>
                            <div class="tkc-chain">
                                <div v-for="step in provenance" :key="step.label" class="tkc-chain__row">
                                    <span class="tkc-chain__left"><span class="material-symbols-outlined tkc-tone-text">{{ step.icon }}</span><span class="tkc-mono tkc-small">{{ step.label }}</span></span>
                                    <span class="tkc-muted tkc-small" :class="step.tone === 'text' ? 'tkc-tone-text tkc-strong' : ''">{{ step.tag }}</span>
                                </div>
                                <div class="tkc-chain__row tkc-chain__row--active">
                                    <span class="tkc-chain__left"><span class="material-symbols-outlined">token</span><span class="tkc-mono tkc-strong tkc-small">{{ selectedToken.id }} (20,000 TOK)</span></span>
                                    <span class="tkc-small tkc-strong">Active Title</span>
                                </div>
                            </div>
                        </div>

                        <div class="tkc-dossier__section">
                            <span class="tkc-card__title-plain">Smart Contract Specifications</span>
                            <div class="tkc-panel--grid2">
                                <div class="tkc-field-box tkc-field-box--plain"><span class="tkc-eyebrow-sm">Token Standard</span><span class="tkc-strong tkc-small">ERC-3643 (RWA Compliant)</span></div>
                                <div class="tkc-field-box tkc-field-box--plain"><span class="tkc-eyebrow-sm">Decimals / Unit</span><span class="tkc-mono tkc-strong tkc-small">0 Decimals (1 = 1 kg)</span></div>
                                <div class="tkc-field-box tkc-field-box--plain tkc-field-box--span2"><span class="tkc-eyebrow-sm">Contract Address</span><span class="tkc-mono tkc-tone-text tkc-strong tkc-small tkc-truncate">0x82f4c9794ba219a9e33dc4b391740921e9044b7a</span></div>
                                <div class="tkc-field-box tkc-field-box--plain tkc-field-box--span2"><span class="tkc-eyebrow-sm">Custody Wallet (Stanbic Multi-sig)</span><span class="tkc-mono tkc-small tkc-truncate">0x3b89dc12948ff98012ba47a947194ccbb8140411</span></div>
                            </div>
                        </div>

                        <div class="tkc-dossier__section">
                            <span class="tkc-card__title-plain">Milestone Verification Proof</span>
                            <div class="tkc-milestones">
                                <div v-for="m in milestones" :key="m.label" class="tkc-milestone">
                                    <span class="tkc-milestone__left"><span class="material-symbols-outlined tkc-tone-text">check_circle</span><span class="tkc-strong tkc-small">{{ m.label }}</span></span>
                                    <span class="tkc-muted tkc-small">{{ m.date }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="tkc-dossier__section">
                            <span class="tkc-card__title-plain">Commercial &amp; Trade Records</span>
                            <div class="tkc-panel tkc-commercial">
                                <div class="tkc-commercial__row">
                                    <div><span class="tkc-strong tkc-small">Exchange Desk Listing</span><div class="tkc-muted tkc-small">Active @ $4.15 / kg FOB Mombasa</div></div>
                                    <button type="button" class="tkc-mini-btn">View Listing</button>
                                </div>
                                <div class="tkc-commercial__row">
                                    <div><span class="tkc-strong tkc-small">Active Counter-Offer</span><div class="tkc-muted tkc-small">OFF-1048: Dubai Coffee Trading ($4.10)</div></div>
                                    <button type="button" class="tkc-mini-btn">View Offer</button>
                                </div>
                            </div>
                        </div>

                        <div class="tkc-ai-box">
                            <div class="tkc-ai-box__head">
                                <span class="tkc-ai-box__head-left"><span class="material-symbols-outlined tkc-tone-text">neurology</span><span class="tkc-strong tkc-small">Bean Origin AI Copilot</span></span>
                                <span class="tkc-ai-box__mode">Auditor Mode</span>
                            </div>
                            <p class="tkc-ai-box__text">"{{ selectedToken.id }} possesses 100% complete chain verification. Moisture certificate is within export threshold (11.2%). No double-pledging detected across global registries."</p>
                            <div class="tkc-ai-box__actions">
                                <button type="button" class="tkc-ai-chip">Audit Physical vs Digital</button>
                                <button type="button" class="tkc-ai-chip">Verify Warehouse Deed</button>
                                <button type="button" class="tkc-ai-chip">Simulate Fractional Transfer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── "Transfer Token" — purely visual dummy dialog; no
             Blockchain-tokenisation backend exists in this app yet. ────── -->
        <Teleport to="body">
            <div v-if="transferOpen" class="tkc-modal-overlay" @click.self="transferOpen = false">
                <div class="tkc-modal tkc-modal--sm">
                    <div class="tkc-modal__head">
                        <div>
                            <h3 class="tkc-modal__title">Transfer Digital Coffee Title <span class="tkc-badge tkc-badge--sm tkc-badge--tertiary">ERC-3643</span></h3>
                            <p class="tkc-muted tkc-small">Transfer legal warehouse entitlement to an accredited institutional buyer</p>
                        </div>
                        <button type="button" class="tkc-icon-btn" @click="transferOpen = false"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <div class="tkc-modal__body">
                        <div class="tkc-panel tkc-transfer-summary">
                            <div><span class="tkc-muted">Active Token Asset:</span><span class="tkc-mono tkc-tone-text tkc-strong">{{ selectedToken.id }} (Robusta 18)</span></div>
                            <div><span class="tkc-muted">Available Balance:</span><span class="tkc-mono tkc-strong">20,000 TOK (20 MT)</span></div>
                            <div><span class="tkc-muted">Stanbic Escrow Holding:</span><span>Silo B-14 Kampala</span></div>
                        </div>
                        <div class="tkc-form-field">
                            <label>Recipient Accredited Institution</label>
                            <select class="tkc-select tkc-select--full">
                                <option>Dubai Coffee Trading FZCO (Wallet 0x7c92...d912) • Verified</option>
                                <option>Rotterdam Green Coffee Merchants (Wallet 0x11ab...6041) • Verified</option>
                                <option>Hamburg Specialty Beans GmbH (Wallet 0x99fe...a423) • Verified</option>
                            </select>
                        </div>
                        <div class="tkc-form-row">
                            <div class="tkc-form-field">
                                <label>Quantity in Tokens (kg)</label>
                                <input class="tkc-input tkc-mono" type="text" value="10000" readonly />
                                <span class="tkc-muted tkc-small">= 10.0 Metric Tons</span>
                            </div>
                            <div class="tkc-form-field">
                                <label>Settlement Trade ID</label>
                                <input class="tkc-input tkc-mono" type="text" value="TRD-2026-DXB-1048" readonly />
                                <span class="tkc-muted tkc-small">Contract reference</span>
                            </div>
                        </div>
                        <div class="tkc-form-field">
                            <label>Transfer Purpose &amp; Regulatory Escrow</label>
                            <div class="tkc-network">Physical possession stays at Stanbic Silo B-14 until buyer requests out-of-warehouse dispatch or export container stuffing.</div>
                        </div>
                    </div>
                    <div class="tkc-modal__foot">
                        <button type="button" class="tkc-btn tkc-btn--muted" @click="transferOpen = false">Cancel</button>
                        <button type="button" class="tkc-btn tkc-btn--primary" @click="transferOpen = false">
                            <span class="material-symbols-outlined">send</span> Execute Title Transfer
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </MainLayout>
</template>

<style scoped>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }

.tkc-page { font-family: var(--dp-font-sans); display: flex; flex-direction: column; gap: 20px; color: var(--dp-on-surface); }
.tkc-mono { font-family: var(--dp-font-mono); }
.tkc-muted { color: var(--dp-on-surface-variant); }
.tkc-strong { font-weight: 700; color: var(--dp-on-surface); }
.tkc-small { font-size: 11px; }
.tkc-tone-text { color: var(--dp-primary); }
.tkc-truncate { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Hero */
.tkc-hero { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.tkc-breadcrumb { display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); margin-bottom: 4px; }
.tkc-breadcrumb .material-symbols-outlined { font-size: 14px; }
.tkc-breadcrumb__current { color: var(--dp-primary); font-weight: 700; }
.tkc-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.015em; color: var(--dp-on-surface); margin: 0; display: flex; align-items: center; gap: 10px; }
.tkc-subtitle { font-size: 12.5px; color: var(--dp-on-surface-variant); margin: 4px 0 0; line-height: 1.5; max-width: 64ch; }
.tkc-hero__actions { display: flex; gap: 8px; flex-wrap: wrap; flex-shrink: 0; }
.tkc-badge { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; padding: 3px 10px; border-radius: 999px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.tkc-badge--sm { font-size: 10px; padding: 2px 8px; }
.tkc-badge--tertiary { background: #DAE2FD; color: #333B54; }

.tkc-btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border-radius: 6px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); text-decoration: none; transition: opacity .12s ease, background .12s ease; }
.tkc-btn .material-symbols-outlined { font-size: 16px; }
.tkc-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.tkc-btn--primary:hover { opacity: .9; }
.tkc-btn--muted { background: var(--dp-surface-container-high); color: var(--dp-on-surface); }
.tkc-btn--muted:hover { background: var(--dp-surface-container-highest); }
.tkc-btn--container { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.tkc-btn--container:hover { background: var(--dp-surface-container-high); }
.tkc-btn--sm { height: 30px; font-size: 11px; }

/* Governance banner */
.tkc-banner { background: var(--dp-surface-container-low); border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.tkc-banner__left { display: flex; align-items: flex-start; gap: 12px; max-width: 640px; }
.tkc-banner__icon { width: 36px; height: 36px; border-radius: 8px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.tkc-banner__head { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap; }
.tkc-banner__eyebrow { font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-primary); }
.tkc-banner__text { font-size: 12px; color: var(--dp-on-surface); line-height: 1.55; margin: 0; }
.tkc-banner__text strong { font-weight: 700; }
.tkc-chip { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); white-space: nowrap; }
.tkc-chip--fixed { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.tkc-flow { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: var(--dp-on-surface-variant); background: var(--dp-surface-container-lowest); padding: 8px 12px; border-radius: 8px; flex-wrap: wrap; }
.tkc-flow .material-symbols-outlined { font-size: 13px; color: var(--dp-outline); }
.tkc-flow__badge { padding: 3px 9px; border-radius: 5px; background: var(--dp-primary); color: var(--dp-on-primary); font-weight: 700; }

/* KPI row */
.tkc-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
.tkc-kpi { background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 8px; }
.tkc-kpi__top { display: flex; align-items: center; justify-content: space-between; }
.tkc-kpi__label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.tkc-kpi__top .material-symbols-outlined { font-size: 17px; }
.tkc-kpi__value { font-size: 1.375rem; font-weight: 800; color: var(--dp-on-surface); }
.tkc-kpi__unit { font-size: 11px; font-weight: 700; color: var(--dp-on-surface-variant); margin-left: 4px; }
.tkc-kpi__note { font-size: 10.5px; color: var(--dp-on-surface-variant); margin-top: 2px; }
.tkc-kpi__track { width: 100%; height: 4px; border-radius: 999px; background: var(--dp-surface-container-highest); overflow: hidden; }
.tkc-kpi__fill { height: 100%; background: var(--dp-primary); }
.tkc-kpi__foot { display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: var(--dp-on-surface-variant); font-weight: 600; }

/* Tabs / export */
.tkc-tabsbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.tkc-tabs { display: flex; align-items: center; gap: 4px; padding: 4px; background: var(--dp-surface-container-low); border-radius: 8px; overflow-x: auto; }
.tkc-tab { display: inline-flex; align-items: center; gap: 5px; padding: 6px 11px; border-radius: 6px; border: none; background: transparent; color: var(--dp-on-surface-variant); font-size: 11.5px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); }
.tkc-tab--active { background: var(--dp-surface-container-lowest); color: var(--dp-primary); }
.tkc-tab__count { font-size: 10px; color: inherit; opacity: .75; }
.tkc-export { display: flex; align-items: center; gap: 8px; font-size: 11.5px; }
.tkc-mini-btn { padding: 6px 10px; border-radius: 6px; border: none; background: var(--dp-surface-container-low); color: var(--dp-on-surface); font-size: 11px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.tkc-mini-btn:hover { background: var(--dp-surface-container-high); }

/* Filters */
.tkc-filters { background: var(--dp-surface-container-low); border-radius: 10px; padding: 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.tkc-search { position: relative; display: flex; align-items: center; flex: 1; min-width: 220px; }
.tkc-search .material-symbols-outlined { position: absolute; left: 10px; font-size: 16px; color: var(--dp-on-surface-variant); }
.tkc-search input { width: 100%; padding: 8px 10px 8px 32px; background: var(--dp-surface-container-lowest); border: none; border-radius: 6px; font-size: 12px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); outline: none; }
.tkc-select { padding: 7px 10px; background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); font-size: 11.5px; font-weight: 600; border: none; border-radius: 6px; font-family: var(--dp-font-sans); cursor: pointer; }
.tkc-select--full { width: 100%; padding: 9px 10px; }

/* Two-column grid */
.tkc-grid { display: grid; grid-template-columns: minmax(0, 7fr) minmax(320px, 5fr); gap: 18px; align-items: start; }
.tkc-col-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.tkc-col-side { display: flex; flex-direction: column; gap: 16px; }
@media (max-width: 1180px) { .tkc-grid { grid-template-columns: 1fr; } }

/* Table */
.tkc-table-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); overflow: hidden; }
.tkc-table-card__head { display: flex; align-items: flex-start; justify-content: space-between; padding: 12px 16px; background: var(--dp-surface-container-low); gap: 10px; flex-wrap: wrap; }
.tkc-table-card__title { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: .8125rem; color: var(--dp-on-surface); }
.tkc-table__dot { width: 7px; height: 7px; border-radius: 999px; background: var(--dp-primary); }
.tkc-table-wrap { overflow-x: hidden; }
.tkc-table { width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: 11.5px; }
.tkc-table thead tr { background: var(--dp-surface-container); }
.tkc-table th { padding: 9px 8px; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: var(--dp-on-surface-variant); overflow-wrap: break-word; }
.tkc-table td { padding: 10px 8px; border-top: 1px solid var(--dp-outline-variant); vertical-align: middle; overflow-wrap: break-word; }
.tkc-table__row { cursor: pointer; transition: background .12s ease; }
.tkc-table__row:hover { background: var(--dp-surface-container); }
.tkc-table__row--active { background: var(--dp-surface-container-low); }
.tkc-table__row--active:hover { background: var(--dp-surface-container-low); }
.tkc-table__end { text-align: right; }
.tkc-table-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; background: var(--dp-surface-container-low); flex-wrap: wrap; }

.tkc-table td .material-symbols-outlined { font-size: 13px; vertical-align: -2px; margin-right: 3px; }

.tkc-pagination { display: flex; align-items: center; gap: 3px; }
.tkc-page-btn { min-width: 24px; height: 24px; padding: 0 8px; border-radius: 5px; border: none; background: var(--dp-surface-container); color: var(--dp-on-surface-variant); font-size: 11px; font-family: var(--dp-font-sans); font-weight: 700; cursor: pointer; }
.tkc-page-btn:hover:not(:disabled) { background: var(--dp-surface-container-high); }
.tkc-page-btn:disabled { opacity: .4; cursor: default; }
.tkc-page-btn--active { background: var(--dp-primary); color: var(--dp-on-primary); }

.tkc-action-btn { padding: 5px 9px; border-radius: 5px; border: none; font-size: 10.5px; font-weight: 700; cursor: pointer; white-space: nowrap; font-family: var(--dp-font-sans); width: 100%; }
.tkc-action-btn--primary { background: var(--dp-primary); color: var(--dp-on-primary); }
.tkc-action-btn--muted { background: var(--dp-surface-container); color: var(--dp-on-surface); }
.tkc-action-btn--muted:hover { background: var(--dp-surface-container-high); }

/* Status pills */
.tkc-status { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 4px; font-size: 9.5px; font-weight: 700; white-space: nowrap; }
.tkc-status--active { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.tkc-status--available { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }
.tkc-status--transferred { background: #DAE2FD; color: #333B54; }
.tkc-status--locked { background: var(--dp-secondary-fixed); color: var(--dp-on-secondary-fixed); }
.tkc-status--pending { background: var(--dp-surface-container-high); color: var(--dp-on-surface-variant); }

/* Reserve proof banner */
.tkc-reserve { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: var(--dp-surface-container-low); border-radius: 10px; padding: 14px 16px; }
.tkc-reserve__left { display: flex; align-items: center; gap: 12px; }
.tkc-reserve__left .material-symbols-outlined { width: 32px; height: 32px; border-radius: 8px; background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.tkc-link-btn { flex-shrink: 0; padding: 7px 12px; border-radius: 8px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-primary); font-size: 11.5px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.tkc-link-btn:hover { background: var(--dp-surface-container); }

/* Dossier card */
.tkc-card { background: var(--dp-surface-container-lowest); border: 1px solid var(--dp-outline-variant); border-radius: var(--dp-card-radius); padding: 18px; display: flex; flex-direction: column; gap: 16px; }
.tkc-card__title-plain { font-size: .8125rem; font-weight: 800; color: var(--dp-on-surface); }
.tkc-eyebrow-sm { display: flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dp-on-surface); }
.tkc-eyebrow-sm .material-symbols-outlined { font-size: 15px; color: var(--dp-primary); }

.tkc-dossier__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.tkc-dossier__title-row { display: flex; align-items: center; gap: 8px; margin-bottom: 2px; }
.tkc-title-lg { font-size: 1.0625rem; font-weight: 800; letter-spacing: -.01em; margin: 2px 0; }
.tkc-dossier__icon { width: 36px; height: 36px; border-radius: 8px; background: var(--dp-surface-container); color: var(--dp-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.tkc-dossier__section { display: flex; flex-direction: column; gap: 8px; }

.tkc-actions-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }

.tkc-panel { background: var(--dp-surface-container-low); border-radius: 8px; padding: 12px; display: flex; flex-direction: column; gap: 10px; }
.tkc-anchor__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.tkc-panel--grid2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.tkc-field-box { background: var(--dp-surface-container-lowest); border-radius: 6px; padding: 8px; display: flex; flex-direction: column; gap: 2px; }
.tkc-field-box--plain { background: var(--dp-surface-container); }
.tkc-field-box--span2 { grid-column: span 2; }
.tkc-anchor__specs { background: var(--dp-surface-container-lowest); border-radius: 6px; padding: 10px; display: flex; flex-direction: column; gap: 6px; font-size: 11px; }
.tkc-anchor__specs > div { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

.tkc-chain { display: flex; flex-direction: column; gap: 5px; }
.tkc-chain__row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px; border-radius: 6px; background: var(--dp-surface-container-low); }
.tkc-chain__row--active { background: var(--dp-primary-fixed); color: var(--dp-on-primary-fixed); }
.tkc-chain__row--active .tkc-mono, .tkc-chain__row--active .material-symbols-outlined { color: var(--dp-on-primary-fixed); }
.tkc-chain__left { display: flex; align-items: center; gap: 7px; }
.tkc-chain__left .material-symbols-outlined { font-size: 15px; }

.tkc-milestones { display: flex; flex-direction: column; gap: 6px; }
.tkc-milestone { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 10px; border-radius: 6px; background: var(--dp-surface-container-low); }
.tkc-milestone__left { display: flex; align-items: center; gap: 7px; }
.tkc-milestone__left .material-symbols-outlined { font-size: 16px; }

.tkc-commercial { gap: 8px; }
.tkc-commercial__row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.tkc-commercial__row + .tkc-commercial__row { padding-top: 8px; border-top: 1px solid var(--dp-outline-variant); }

.tkc-ai-box { background: var(--dp-surface-container-high); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.tkc-ai-box__head { display: flex; align-items: center; justify-content: space-between; }
.tkc-ai-box__head-left { display: flex; align-items: center; gap: 6px; }
.tkc-ai-box__mode { font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--dp-primary); }
.tkc-ai-box__text { font-size: 12px; color: var(--dp-on-surface); line-height: 1.55; margin: 0; }
.tkc-ai-box__actions { display: flex; flex-wrap: wrap; gap: 6px; }
.tkc-ai-chip { padding: 6px 10px; border-radius: 6px; border: none; background: var(--dp-surface-container-lowest); color: var(--dp-on-surface); font-size: 10.5px; font-weight: 700; cursor: pointer; font-family: var(--dp-font-sans); }
.tkc-ai-chip:hover { color: var(--dp-primary); }

/* Modals */
.tkc-modal-overlay { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(0, 0, 0, .4); backdrop-filter: blur(4px); }
.tkc-modal { width: 100%; max-width: 640px; max-height: 88vh; overflow-y: auto; background: var(--dp-surface-container-lowest); border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,.18); font-family: var(--dp-font-sans); }
.tkc-modal--sm { max-width: 520px; }
.tkc-modal__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 18px 20px; background: var(--dp-surface-container-low); position: sticky; top: 0; }
.tkc-modal__title { font-size: 1rem; font-weight: 800; color: var(--dp-on-surface); margin: 0 0 3px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.tkc-modal__body { padding: 18px 20px; display: flex; flex-direction: column; gap: 14px; }
.tkc-modal__foot { padding: 16px 20px; background: var(--dp-surface-container-low); display: flex; align-items: center; justify-content: flex-end; gap: 10px; position: sticky; bottom: 0; }

.tkc-form-field { display: flex; flex-direction: column; gap: 4px; }
.tkc-form-field label { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--dp-on-surface-variant); }
.tkc-form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.tkc-input { width: 100%; padding: 9px 10px; background: var(--dp-surface-container-low); border: none; border-radius: 6px; font-size: 12px; color: var(--dp-on-surface); font-family: var(--dp-font-sans); font-weight: 700; outline: none; }

.tkc-network { padding: 9px 12px; border-radius: 8px; background: var(--dp-surface-container); font-size: 11.5px; color: var(--dp-on-surface); font-weight: 600; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }

.tkc-transfer-summary { gap: 6px; }
.tkc-transfer-summary > div { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 11.5px; }

@media (max-width: 640px) {
    .tkc-hero__actions { width: 100%; }
    .tkc-hero__actions .tkc-btn { flex: 1; justify-content: center; }
    .tkc-panel--grid2 { grid-template-columns: 1fr; }
    .tkc-field-box--span2 { grid-column: span 1; }
    .tkc-actions-grid { grid-template-columns: 1fr 1fr; }
    .tkc-form-row { grid-template-columns: 1fr; }
}
</style>
