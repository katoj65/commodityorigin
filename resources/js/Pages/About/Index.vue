<script setup>
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight, VideoPlay, CircleCheckFilled, Connection, Close, LocationFilled, Medal,
    TrendCharts, Box, Coordinate, Cherry, Ship, DataLine, View, Share, MagicStick, Search,
    ShoppingBag, Shop, DocumentAdd, Timer, Tickets, Histogram, Cpu, Van, Refresh, Postcard,
    DocumentChecked, CircleCheck, Lock, House, Management, Suitcase, CoffeeCup, OfficeBuilding,
    PriceTag, Wallet, ScaleToOriginal, MapLocation, Opportunity, Document, Cloudy, Stamp,
    Calendar, Aim, GoldMedal, Notebook, DataBoard, Monitor, Coin, Link as LinkIcon, Platform,
    Compass,
} from '@element-plus/icons-vue';
import OuterLayout from '@/Layouts/OuterLayout.vue';

/* ── Dummy about-page content, illustrative only ───────────────────── */
const microStats = [
    { label: 'FOB Reference', value: '$4.15', unit: '/kg avg' },
    { label: 'Live Trade Volume', value: '1,284', unit: 'MT physical' },
    { label: 'EUDR Verified', value: '100%', unit: 'polygon mapped' },
];

const legacyPoints = [
    { title: 'Data locked in spreadsheets and email', body: 'Paper waybills, manual grading sheets, and scattered email threads add weeks of delay to every shipment.' },
    { title: 'Unpredictable middleman margins', body: 'Intermediaries take an unpredictable cut while smallholder farmers are left with the baseline farmgate price.' },
    { title: 'EUDR exposure', body: 'Without solid farm geolocation records, shipments risk sudden holds or rejection under deforestation rules.' },
    { title: 'Sensory verification you have to take on faith', body: 'Cup scores are often self-reported, and moisture can shift during transit with no way to check it in transit.' },
];

const solutionPoints = [
    { title: 'One digital coffee record', body: 'Elevation, variety, GPS polygon, cupping score, and batch data all live in a single source of truth.' },
    { title: 'Transparent pricing', body: 'Direct FOB benchmarks and live auctions put more of the value back where it belongs, at origin.' },
    { title: 'Compliance built in', body: 'Zero-deforestation checks run automatically, cross-referenced against satellite imagery.' },
    { title: 'Escrow-backed settlement', body: 'Funds release once the bill of lading and destination lab re-grading are both confirmed.' },
];

const dimensions = [
    { icon: LocationFilled, title: 'Origin', body: 'Farm-gate GPS polygons and producer identity.' },
    { icon: Medal, title: 'Quality', body: 'Certified Q-grader sensory protocols.' },
    { icon: TrendCharts, title: 'Price', body: 'Transparent FOB and farmgate parity.' },
    { icon: Box, title: 'Availability', body: 'Real-time inventory at port and origin warehouses.' },
    { icon: Coordinate, title: 'Traceability', body: 'An unbroken chain of custody from cherry to green bean.' },
    { icon: Cherry, title: 'Sustainability', body: 'EUDR, organic, and regenerative soil audit trails.' },
    { icon: Ship, title: 'Logistics', body: 'Container tracking and in-transit climate logging.' },
    { icon: DataLine, title: 'Market Demand', body: 'Purchasing trend signals for roasters and importers.' },
];

const pillars = [
    { icon: View, tone: 'primary', tag: 'Pillar 01', title: 'Transparency', body: 'We publish audited price indices, moisture levels, cupping breakdowns, and exporter certifications in the open, not behind a sales call.', tagline: 'Direct data access' },
    { icon: Share, tone: 'secondary', tag: 'Pillar 02', title: 'Direct connections', body: 'We pair origin cooperatives directly with buyers and roasters, cutting out the speculative middle layers that add cost without adding value.', tagline: 'Less friction, more trade' },
    { icon: MagicStick, tone: 'tertiary', tag: 'Pillar 03', title: 'Smarter decisions', body: 'From weather-yield hedging to automated contract drafting, our AI tools give both sides better information before they commit capital.', tagline: 'AI-assisted trading' },
];

const capabilities = [
    { icon: Search, title: 'Discover coffee', body: 'Search verified green coffee lots by SCA score, process, screen size, and harvest date.' },
    { icon: ShoppingBag, title: 'Buy and source', body: 'Purchase spot inventory in bonded warehouses or negotiate forward FOB contracts direct from origin.' },
    { icon: Shop, title: 'Sell and manage stock', body: 'Cooperatives and exporters can list harvest yields, publish cupping sheets, and track reserved stock.' },
    { icon: DocumentAdd, title: 'Create RFQs and offers', body: 'Post custom sourcing requests and let qualified exporters respond with competitive bids.' },
    { icon: Timer, title: 'Micro-lot auctions', body: 'Bid on award-winning micro-lots in scheduled, transparent auction sessions.' },
    { icon: Tickets, title: 'Track trades and orders', body: 'Follow contract approvals, quality sign-offs, and multi-currency escrow releases end to end.' },
    { icon: Histogram, title: 'Market intelligence', body: 'ICE benchmark correlations, freight congestion indices, and tariff updates in one place.' },
    { icon: Cpu, title: 'AI commercial support', body: 'Model inventory costs, match roast profiles to available supply, and draft compliant paperwork with plain language prompts.' },
];

const lifecycleStages = [
    { n: '01', icon: LocationFilled, title: 'Farm', body: 'Farmer identity, GPS polygon boundaries, and micro-climate elevation on record from day one.', tag: 'Identity' },
    { n: '02', icon: Van, title: 'Farm Collection', body: 'Cherry weight, harvest date, and ripeness recorded with a digital receipt at the wet mill.', tag: 'Harvest' },
    { n: '03', icon: Refresh, title: 'Batch Processing', body: 'Cherries move through washed, natural, anaerobic, or honey fermentation under controlled conditions.', tag: 'Processing' },
    { n: '04', icon: Box, title: 'Lot Creation', body: 'A defined export quantity with standardized moisture, screen sorting, and water activity.', tag: 'Standardization' },
    { n: '05', icon: Postcard, title: 'Product Packaging', body: 'Sensory profiles, cupping charts, moisture proofs, and bag IDs, ready for the catalog.', tag: 'Catalog ready' },
    { n: '06', icon: Connection, title: 'The Exchange', body: 'Coffee reaches institutional traders, roasters, and buyers through public boards and private deal rooms.', tag: 'Discovery' },
    { n: '07', icon: DocumentChecked, title: 'Trade Agreement', body: 'Binding digital sales contracts under standard Incoterms, signed and timestamped.', tag: 'Commercial' },
    { n: '08', icon: CircleCheck, title: 'Order Execution', body: 'Pre-shipment sampling, dry-mill dispatch checks, and container seal assignment.', tag: 'Compliance' },
    { n: '09', icon: Lock, title: 'Escrow Settlement', body: 'Funds sit in a secured account with milestone-based release and currency conversion handled automatically.', tag: 'Security' },
    { n: '10', icon: Ship, title: 'Freight Fulfilment', body: 'Ocean freight tracking, humidity sensors, and customs clearance coordinated in one view.', tag: 'Movement' },
    { n: '11', icon: House, title: 'Physical Delivery', body: 'Warehouse intake, quality sign-off, and payout clearance for the producer.', tag: 'Closed' },
];

const ecosystem = [
    { icon: Cherry, tag: 'Supply Origin', tone: 'primary', title: 'Producers', body: 'Register micro-farms, digitize harvest logs, and connect directly with buyers willing to pay for quality.', foot: 'Direct market access' },
    { icon: Management, tag: 'Aggregation', tone: 'secondary', title: 'Cooperatives', body: 'Aggregate smallholder harvests, automate member payouts, and generate EUDR trace reports.', foot: 'Member sync' },
    { icon: Suitcase, tag: 'Origin Logistics', tone: 'tertiary', title: 'Exporters', body: 'Manage dry-mill output, export paperwork, and customs, with financing support for currency exposure.', foot: 'Export compliance' },
    { icon: TrendCharts, tag: 'Market Makers', tone: 'primary', title: 'Commodity Traders', body: 'Spot arbitrage spreads, execute forward agreements, and hedge across multiple origins.', foot: 'Deal execution' },
    { icon: CoffeeCup, tag: 'Sensory & Craft', tone: 'secondary', title: 'Specialty Roasters', body: 'Source to a roast profile, order sample packs quickly, and share the farm story with a QR tag.', foot: 'Sensory discovery' },
    { icon: OfficeBuilding, tag: 'Procurement', tone: 'tertiary', title: 'Commercial Buyers', body: 'Pre-vetted agreements, verified quality audits, and delivery timelines you can plan around.', foot: 'Institutional procurement' },
    { icon: Van, tag: 'Freight Corridors', tone: 'primary', title: 'Logistics Carriers', body: 'Coordinate drayage, container allocation, and bonded warehouse transfers with digital documents.', foot: 'Container telemetry' },
    { icon: DataLine, tag: 'Analytics', tone: 'secondary', title: 'Coffee Enterprises', body: 'Use price projections and compliance tools to run cafe networks and packaging operations.', foot: 'Strategic intelligence' },
];

const exchangeMechanisms = [
    { icon: Shop, title: 'Market Listings', body: 'Buy-now inventory in bonded warehouses at fixed, transparent FOB or CIF pricing.' },
    { icon: PriceTag, title: 'Counter-Offers & Private Bids', body: 'Negotiate volume, payment terms, and delivery port directly with the other side.' },
    { icon: DocumentAdd, title: 'Request for Quotes', body: 'Broadcast procurement needs and let qualified suppliers respond with sealed proposals.' },
    { icon: Timer, title: 'Dynamic Auctions', body: 'Scheduled bidding for prized micro-lots, Cup of Excellence entries, and single-estate rarities.' },
    { icon: Wallet, title: 'My Trades & Settlement', body: 'One dashboard for live shipments, escrow status, and digital bills of lading.' },
];

const orderbookRows = [
    { lot: 'Yirgacheffe G1', process: 'Washed', score: '88.5', volume: '18,000 kg', price: '$4.85/kg' },
    { lot: 'Huila Pitalito', process: 'Castillo / Honey', score: '86.7', volume: '24,500 kg', price: '$4.20/kg' },
    { lot: 'Antigua Pastores', process: 'Bourbon Washed', score: '87.0', volume: '12,000 kg', price: '$4.60/kg' },
    { lot: 'Sul de Minas', process: 'Natural Arabica', score: '84.2', volume: '48,000 kg', price: '$3.45/kg', muted: true },
    { lot: 'Sidama Aleta', process: 'Natural Organic', score: '89.0', volume: '9,600 kg', price: '$5.40/kg' },
];

const intelligenceModules = [
    { icon: TrendCharts, title: 'Coffee Prices', body: "ICE 'C' contract and differentials" },
    { icon: ScaleToOriginal, title: 'Farmgate vs Export', body: 'FOB margin distribution' },
    { icon: MapLocation, title: 'Producing Countries', body: 'Harvest calendar and yield' },
    { icon: DataLine, title: 'Supply & Demand', body: 'Global inventory deficits' },
    { icon: Opportunity, title: 'Market Opportunities', body: 'AI-flagged price gaps' },
    { icon: Document, title: 'Coffee News Radar', body: 'Curated trade dispatches' },
    { icon: Cloudy, title: 'Weather Focus', body: 'Rainfall and frost models' },
    { icon: Ship, title: 'Port Congestion', body: 'Berth delays at key ports' },
    { icon: Timer, title: 'Transit Times', body: 'Corridor and demurrage risk' },
    { icon: Stamp, title: 'EUDR & Regulations', body: 'Due diligence tools' },
    { icon: Calendar, title: 'Coffee Schedule', body: 'Auction dates and deliveries' },
];

const traceabilityItems = [
    { icon: LocationFilled, title: 'Farm-Gate Provenance', body: 'Registered farmer profiles, farm boundaries, and elevation benchmarks.' },
    { icon: DocumentChecked, title: 'Collection Records', body: 'Digital intake receipts timestamped at regional buying centers.' },
    { icon: Aim, title: 'Processing Specifications', body: 'Fermentation hours, patio drying times, and water pH logs.' },
    { icon: CircleCheckFilled, title: 'Sensory Certifications', body: 'Licensed Q-grader cupping forms, SCA notes, and defect tallies.' },
];

const trustCards = [
    { icon: Stamp, title: 'Business Verification', body: 'KYC and KYB checks, business registry validation, and tax compliance audits across exporting and importing countries.' },
    { icon: Aim, title: 'Coffee Verification', body: 'Physical lot sampling, dual-lab sensory confirmation, and moisture checks before the container seals.' },
    { icon: Document, title: 'Document Authenticity', body: 'Phytosanitary certificates, certificates of origin, and bills of lading, signed with cryptographic signatures.' },
    { icon: GoldMedal, title: 'Certification Sync', body: 'Direct integrations with Fair Trade, Rainforest Alliance, Organic, and Bird Friendly registries.' },
    { icon: Lock, title: 'Escrow Protection', body: 'Buyer funds stay in segregated custody until the bill of lading is verified.' },
    { icon: Notebook, title: 'Commercial Records', body: 'Permanent trade records that build credit history for cooperatives seeking working capital.' },
];

const aiFlow = [
    { n: '01', title: 'Understand', body: 'Reads intent' },
    { n: '02', title: 'Search', body: 'Scans the catalog' },
    { n: '03', title: 'Compare', body: 'Sensory & price' },
    { n: '04', title: 'Analyze', body: 'Freight rates' },
    { n: '05', title: 'Recommend', body: 'Risk-adjusted picks' },
    { n: '06', title: 'Prepare', body: 'Drafts a contract' },
    { n: '07', title: 'Confirm', body: 'Human sign-off', highlight: true },
    { n: '08', title: 'Execute', body: 'Locks escrow' },
    { n: '09', title: 'Track', body: 'Watches the vessel' },
];

const aiTags = ['Product Discovery', 'Market Intelligence', 'Automated RFQ', 'Negotiation Modeling', 'Logistics Routing', 'EUDR Auto-Check'];

const entities = [
    { title: 'Farm', body: 'Sets the geographic origin and farmer identity.' },
    { title: 'Collection', body: 'Records physical aggregation and farm-gate weights.' },
    { title: 'Batch', body: 'Captures wet processing and drying parameters.' },
    { title: 'Lot', body: 'Defines commercial grade, screening, and moisture.' },
    { title: 'Product', body: 'Presents sensory profiles and certifications.' },
    { title: 'Exchange', body: 'Makes inventory discoverable to buyers.' },
    { title: 'Trade', body: 'Binds both parties and triggers escrow.' },
];

const techCards = [
    { icon: DataBoard, tone: 'primary', title: 'Digital Coffee Records', body: 'Standardized schemas covering farm provenance, moisture, water activity, screen separation, and cup scores.' },
    { icon: Cpu, tone: 'secondary', title: 'Agritech AI', body: 'Models trained on decades of harvest yields, price history, and cupping competition results.' },
    { icon: Monitor, tone: 'tertiary', title: 'Telemetry & Sensors', body: 'IoT readings for humidity and temperature inside shipping containers during ocean transit.' },
    { icon: Coin, tone: 'primary', title: 'Digital Provenance', body: 'Sealed asset records so a certificate of origin or harvest allocation can never be sold twice.' },
    { icon: LinkIcon, tone: 'secondary', title: 'Enterprise Integrations', body: 'Connectors for SAP, Microsoft Dynamics, risk systems, and major customs clearing networks.' },
];

const missionMini = [
    { icon: Connection, title: 'Connect', body: 'Bridge remote origin regions directly with commercial roasters.' },
    { icon: Compass, title: 'Discover', body: 'Surface exceptional micro-lots and verified direct-trade sources.' },
    { icon: Histogram, title: 'Understand', body: 'Put real market intelligence and farmgate economics in reach.' },
    { icon: Share, title: 'Trade', body: 'Settle contracts quickly, with institutional-grade safeguards.' },
];

const principles = [
    { tag: '01 / ROOTS', title: 'Origin matters', body: 'Every bean has a source, a terroir, and a family behind it. Technology should highlight that, not flatten it into a faceless code.' },
    { tag: '02 / CLARITY', title: 'Transparency matters', body: 'Hidden pricing and broker spreads slow everyone down. Open benchmarks build the trust that keeps trade moving.' },
    { tag: '03 / COMMUNITY', title: 'People matter', body: 'We build bridges between roasters, farmers, and cuppers who care about the craft, not a replacement for those relationships.' },
    { tag: '04 / RIGOR', title: 'Better decisions matter', body: 'When farmers can see real demand, they plant smarter. When roasters know transit times, they do not run out of stock.' },
];

const networkHubs = [
    { tag: 'Latin America Hub', tone: 'primary', ports: 'Santos, Buenaventura', title: 'Colombia, Brazil & Central America', body: 'High-grown Arabica varietals, direct integration with 48 registered producer cooperatives.', stat: 'Annual volume: 420,000 MT' },
    { tag: 'East Africa Hub', tone: 'primary', ports: 'Mombasa, Djibouti', title: 'Ethiopia, Kenya, Uganda & Rwanda', body: 'Heirloom arabicas and high-elevation washed lots, with automated auction links to regional wet mills.', stat: 'Annual volume: 280,000 MT' },
    { tag: 'Asia-Pacific Hub', tone: 'primary', ports: 'HCMC, Jakarta', title: 'Vietnam, Indonesia & PNG', body: 'Commercial-grade Robusta alongside rare Indonesian wet-hulled Mandheling micro-lots.', stat: 'Annual volume: 310,000 MT' },
    { tag: 'Import Hub', tone: 'secondary', ports: 'Rotterdam, Hamburg', title: 'European Union Gateway', body: 'Bonded warehouse distribution and customs clearing serving roasters across 24 European countries.', stat: 'Spot stock: 18,500 MT' },
    { tag: 'Import Hub', tone: 'secondary', ports: 'Oakland, New York', title: 'North American Logistics', body: 'East and West coast intake hubs with multi-temperature storage and rail dispatch.', stat: 'Spot stock: 22,000 MT' },
    { tag: 'Emerging Hub', tone: 'secondary', ports: 'Jebel Ali (Dubai)', title: 'MENA Roasting Corridor', body: 'A fast-growing specialty market across the GCC, linked to East African lots through simplified customs.', stat: 'Spot stock: 7,400 MT' },
];

const timeline = [
    { stage: 'Stage 1: Foundation', title: 'Where we started', body: 'An agritech project focused on building farm-gate tracking and origin infrastructure for smallholder cooperatives shut out by paperwork.', foot: 'Farm registries & GPS polygons' },
    { stage: 'Stage 2: Present day', title: 'Where we are now', highlight: true, body: 'A multi-sided exchange supporting spot trading, forward contracts, escrow, auctions, live intelligence, and Bean Origin AI.', foot: 'The exchange is live' },
    { stage: 'Stage 3: The future', title: 'Where we are going', body: 'Universal digital liquidity for coffee: instant micro-lot settlement, satellite yield hedging, and automated regulatory compliance worldwide.', foot: 'Built for what is next' },
];
</script>

<template>
    <OuterLayout title="About Bean Origin">
        <div class="wp-about bg-white">
            <!-- 1. HERO -->
            <section class="bg-[#f7fbf0] pb-20 pt-10">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        <div class="lg:col-span-7 flex flex-col gap-6">
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-[#181d17] leading-[1.12]">
                                Building a more <span class="text-[#0d631b]">connected</span> coffee market.
                            </h1>
                            <p class="text-base sm:text-lg text-[#40493d] max-w-2xl leading-relaxed">
                                Bean Origin is a digital platform connecting producers, cooperatives, exporters, buyers, traders, and roasters through a more transparent and better-informed coffee trade.
                            </p>
                            <div class="flex flex-wrap items-center gap-4 pt-2">
                                <Link :href="route('exchange-snapshot.index')" class="px-7 py-3.5 rounded-xl bg-[#0d631b] text-white text-sm font-bold hover:bg-[#0a4f15] transition-all flex items-center gap-2 no-underline">
                                    <span>Explore the Exchange</span>
                                    <el-icon :size="18"><ArrowRight /></el-icon>
                                </Link>
                                <Link :href="route('how-it-works.index')" class="px-7 py-3.5 rounded-xl bg-white text-[#181d17] text-sm font-semibold hover:bg-[#f0f4ee] transition-all flex items-center gap-2 no-underline shadow-sm">
                                    <el-icon :size="18"><VideoPlay /></el-icon>
                                    <span>How Bean Origin Works</span>
                                </Link>
                            </div>
                            <div class="grid grid-cols-3 gap-6 pt-6 mt-4 bg-white/60 rounded-xl p-5">
                                <div v-for="m in microStats" :key="m.label">
                                    <div class="text-xs uppercase text-[#6b7568] tracking-wider font-semibold">{{ m.label }}</div>
                                    <div class="text-xl font-bold text-[#0d631b] mt-1">{{ m.value }}<span class="text-xs font-normal text-[#6b7568]"> {{ m.unit }}</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-5 relative">
                            <div class="relative rounded-2xl overflow-hidden shadow-xl bg-[#181d17]">
                                <div class="w-full h-[380px] flex flex-col items-center justify-center gap-3 text-[#a7d9b0]">
                                    <el-icon :size="56"><CoffeeCup /></el-icon>
                                    <span class="text-xs uppercase tracking-widest font-semibold">Q-Grade Sensory Lab</span>
                                </div>
                                <div class="absolute bottom-4 left-4 right-4 p-5 rounded-xl bg-white/95 backdrop-blur-md">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0d631b] uppercase tracking-wider">
                                            <el-icon :size="15"><CircleCheckFilled /></el-icon> Real-time Corridor
                                        </span>
                                        <span class="text-xs font-semibold text-[#6b7568]">Lot #BO-2024-HN</span>
                                    </div>
                                    <div class="text-sm font-bold text-[#181d17]">Finca El Mirador &middot; SHG EP Micro-lot</div>
                                    <div class="text-xs text-[#6b7568] mt-1 flex items-center justify-between">
                                        <span>San Pedro Sula to Hamburg</span>
                                        <span class="text-[#0d631b] font-bold">In transit (ETA 8d)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- 2. THE PROBLEM -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="max-w-3xl mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Market Friction</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Coffee is a global market, but the journey is fragmented.</h2>
                        <p class="text-base text-[#6b7568] mt-4 leading-relaxed">Between the coffee cherries on a mountain slope and the espresso cup in a city center sits a long chain of intermediaries. Every handoff without a shared digital record leaks margin, adds delay, and blurs where the coffee actually came from.</p>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
                        <div class="p-8 rounded-2xl bg-white shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between pb-6 mb-6 bg-[#fef2f2] -mx-8 -mt-8 p-8 rounded-t-2xl">
                                    <div>
                                        <span class="text-xs uppercase tracking-widest text-[#b91c1c] font-bold">Legacy Structure</span>
                                        <h3 class="text-xl font-bold text-[#181d17] mt-1">Fragmented physical sourcing</h3>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-[#fee2e2] text-[#b91c1c] flex items-center justify-center flex-shrink-0">
                                        <el-icon :size="20"><Close /></el-icon>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div v-for="p in legacyPoints" :key="p.title" class="flex items-start gap-3.5">
                                        <el-icon :size="16" class="text-[#b91c1c] mt-0.5"><Close /></el-icon>
                                        <div>
                                            <div class="text-sm font-bold text-[#181d17]">{{ p.title }}</div>
                                            <div class="text-xs text-[#6b7568] mt-0.5">{{ p.body }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8 pt-4 text-xs font-mono text-[#6b7568] bg-[#f8faf7] p-3 rounded-lg">
                                Farmer &rarr; Local Broker &rarr; Mill &rarr; Exporter &rarr; Speculator &rarr; Importer &rarr; Roaster
                            </div>
                        </div>
                        <div class="p-8 rounded-2xl bg-white shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between pb-6 mb-6 bg-[#f7fbf0] -mx-8 -mt-8 p-8 rounded-t-2xl">
                                    <div>
                                        <span class="text-xs uppercase tracking-widest text-[#0d631b] font-bold">The Solution</span>
                                        <h3 class="text-xl font-bold text-[#181d17] mt-1">Connected digital infrastructure</h3>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-[#0d631b] text-white flex items-center justify-center flex-shrink-0">
                                        <el-icon :size="20"><CircleCheckFilled /></el-icon>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div v-for="p in solutionPoints" :key="p.title" class="flex items-start gap-3.5">
                                        <el-icon :size="16" class="text-[#0d631b] mt-0.5"><CircleCheckFilled /></el-icon>
                                        <div>
                                            <div class="text-sm font-bold text-[#181d17]">{{ p.title }}</div>
                                            <div class="text-xs text-[#6b7568] mt-0.5">{{ p.body }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8 pt-4 text-xs font-mono text-[#0d631b] font-bold bg-[#f7fbf0] p-3 rounded-lg">
                                Producer Collective &harr; Institutional Exchange &harr; Direct Buyer
                            </div>
                        </div>
                    </div>
                    <div class="pt-6">
                        <h3 class="text-lg font-bold text-[#181d17] mb-6">Eight things we rebuild from the ground up</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="d in dimensions" :key="d.title" class="p-4 rounded-xl bg-white shadow-sm">
                                <el-icon :size="22" class="text-[#0d631b]"><component :is="d.icon" /></el-icon>
                                <div class="text-sm font-bold text-[#181d17] mt-2">{{ d.title }}</div>
                                <div class="text-xs text-[#6b7568] mt-1">{{ d.body }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. OUR PURPOSE -->
            <section class="w-full bg-[#f7fbf0] py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Our Purpose</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">To make coffee easier to discover, understand, verify, and trade.</h2>
                        <p class="text-base text-[#6b7568] mt-4 leading-relaxed">The platform brings coffee supply, market intelligence, trading tools, traceability, and AI-assisted decisions into one place.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div v-for="p in pillars" :key="p.title" class="p-8 rounded-2xl bg-white shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6" :class="p.tone === 'primary' ? 'bg-[#e6f4ee] text-[#0d631b]' : p.tone === 'secondary' ? 'bg-[#fdecd3] text-[#b45309]' : 'bg-[#e0e7ff] text-[#1e40af]'">
                                    <el-icon :size="24"><component :is="p.icon" /></el-icon>
                                </div>
                                <span class="text-xs uppercase tracking-wider font-bold" :class="p.tone === 'primary' ? 'text-[#0d631b]' : p.tone === 'secondary' ? 'text-[#b45309]' : 'text-[#1e40af]'">{{ p.tag }}</span>
                                <h3 class="text-xl font-bold text-[#181d17] mt-2 mb-3">{{ p.title }}</h3>
                                <p class="text-sm text-[#6b7568] leading-relaxed">{{ p.body }}</p>
                            </div>
                            <div class="mt-8 pt-4 flex items-center gap-2 text-xs font-bold" :class="p.tone === 'primary' ? 'text-[#0d631b]' : p.tone === 'secondary' ? 'text-[#b45309]' : 'text-[#1e40af]'">
                                <span>{{ p.tagline }}</span>
                                <el-icon :size="16"><ArrowRight /></el-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4. WHAT IS BEAN ORIGIN -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8 mb-16">
                        <div class="max-w-2xl">
                            <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Platform Capabilities</div>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">A digital exchange designed for institutional coffee.</h2>
                            <p class="text-base text-[#6b7568] mt-3 leading-relaxed">From single-origin micro-lots in Huila to full container loads out of Santos, every transaction runs with financial-grade precision.</p>
                        </div>
                        <Link :href="route('market.live')" class="px-6 py-3 rounded-xl bg-[#f7fbf0] text-[#181d17] font-semibold text-sm hover:bg-[#e6f4ee] transition-all self-start lg:self-auto flex items-center gap-2 no-underline">
                            <span>View Live Catalog</span>
                            <el-icon :size="18"><ArrowRight /></el-icon>
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="c in capabilities" :key="c.title" class="p-6 rounded-2xl bg-[#f7fbf0]">
                            <el-icon :size="26" class="text-[#0d631b] mb-4"><component :is="c.icon" /></el-icon>
                            <h4 class="text-base font-bold text-[#181d17] mb-2">{{ c.title }}</h4>
                            <p class="text-xs text-[#6b7568] leading-relaxed">{{ c.body }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. FROM ORIGIN TO MARKET -->
            <section class="w-full bg-[#f8faf7] py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="max-w-3xl mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Digital Provenance Pipeline</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">From origin to market: the 11-stage connected lifecycle.</h2>
                        <p class="text-base text-[#6b7568] mt-4 leading-relaxed">Every gram of coffee traded through Bean Origin is tied to one digital thread, so there is no offline paperwork and no gap in custody.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <div v-for="s in lifecycleStages" :key="s.n" class="p-6 rounded-2xl bg-white shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded bg-[#f2f4f2] text-[#181d17]">STAGE {{ s.n }}</span>
                                    <el-icon :size="20" class="text-[#0d631b]"><component :is="s.icon" /></el-icon>
                                </div>
                                <h3 class="text-base font-bold text-[#181d17] mb-2">{{ s.title }}</h3>
                                <p class="text-xs text-[#6b7568] leading-relaxed">{{ s.body }}</p>
                            </div>
                            <div class="mt-4 pt-3 text-[11px] font-semibold text-[#0d631b]">{{ s.tag }}</div>
                        </div>
                        <div class="p-6 rounded-2xl bg-[#0d631b] text-white flex flex-col justify-between">
                            <div>
                                <el-icon :size="32" class="text-[#a7d9b0] mb-4"><Monitor /></el-icon>
                                <h3 class="text-lg font-bold">One unified record</h3>
                                <p class="text-xs text-[#c9e8d3] mt-2 leading-relaxed">Every step from cherry picking to roaster intake writes to the same ledger entry.</p>
                            </div>
                            <div class="mt-6 pt-4 text-xs font-bold text-[#a7d9b0] flex items-center gap-1">
                                <span>Enterprise protocol active</span>
                                <el-icon :size="16"><CircleCheckFilled /></el-icon>
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 p-6 rounded-xl bg-white shadow-sm flex items-center justify-between gap-6 flex-wrap">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-[#fdecd3] text-[#b45309] flex items-center justify-center font-bold flex-shrink-0">
                                <el-icon :size="20"><Notebook /></el-icon>
                            </div>
                            <p class="text-sm font-semibold text-[#181d17]">"One connected digital record replaces the offline paperwork we used to juggle across a dozen jurisdictions."</p>
                        </div>
                        <Link :href="route('how-it-works.index')" class="text-xs font-bold text-[#0d631b] hover:underline flex items-center gap-1 no-underline flex-shrink-0">
                            <span>Read the technical spec</span>
                            <el-icon :size="16"><ArrowRight /></el-icon>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- 6. BUILT FOR THE ECOSYSTEM -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Who Uses Bean Origin</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Built for every actor in the coffee ecosystem.</h2>
                        <p class="text-base text-[#6b7568] mt-3 leading-relaxed">From independent single-estate farms to multinational coffee merchants, everyone gets tools built for their role.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="e in ecosystem" :key="e.title" class="p-6 rounded-2xl bg-[#f7fbf0] flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center mb-5 shadow-sm" :class="e.tone === 'primary' ? 'text-[#0d631b]' : e.tone === 'secondary' ? 'text-[#b45309]' : 'text-[#1e40af]'">
                                    <el-icon :size="24"><component :is="e.icon" /></el-icon>
                                </div>
                                <span class="text-[11px] font-bold uppercase tracking-wider" :class="e.tone === 'primary' ? 'text-[#0d631b]' : e.tone === 'secondary' ? 'text-[#b45309]' : 'text-[#1e40af]'">{{ e.tag }}</span>
                                <h3 class="text-base font-bold text-[#181d17] mt-1 mb-2">{{ e.title }}</h3>
                                <p class="text-xs text-[#6b7568] leading-relaxed">{{ e.body }}</p>
                            </div>
                            <div class="mt-6 pt-4 text-xs font-semibold text-[#6b7568]">{{ e.foot }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 7. THE EXCHANGE -->
            <section class="w-full bg-[#f8faf7] py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        <div class="lg:col-span-5 flex flex-col gap-6">
                            <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b]">Core Commercial Rails</div>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Where coffee meets the global market.</h2>
                            <p class="text-sm sm:text-base text-[#6b7568] leading-relaxed">The Bean Origin Exchange runs five trading mechanisms built around how physical green coffee actually moves.</p>
                            <div class="space-y-3 pt-2">
                                <div v-for="(m, i) in exchangeMechanisms" :key="m.title" class="p-4 rounded-xl bg-white flex items-start gap-3 shadow-sm">
                                    <el-icon :size="20" class="text-[#0d631b] mt-0.5"><component :is="m.icon" /></el-icon>
                                    <div>
                                        <div class="text-sm font-bold text-[#181d17]">{{ i + 1 }}. {{ m.title }}</div>
                                        <div class="text-xs text-[#6b7568]">{{ m.body }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4">
                                <Link :href="route('exchange-snapshot.index')" class="px-7 py-3 rounded-xl bg-[#0d631b] text-white text-sm font-bold hover:bg-[#0a4f15] transition-all inline-flex items-center gap-2 no-underline">
                                    <span>Explore the Exchange</span>
                                    <el-icon :size="18"><ArrowRight /></el-icon>
                                </Link>
                            </div>
                        </div>
                        <div class="lg:col-span-7">
                            <div class="rounded-2xl bg-white p-6 shadow-xl">
                                <div class="flex items-center justify-between pb-6 mb-6 bg-[#f7fbf0] -mx-6 -mt-6 p-6 rounded-t-2xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-3 h-3 rounded-full bg-[#0d631b] animate-pulse"></div>
                                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-[#181d17]">Exchange Spot Orderbook</span>
                                    </div>
                                    <span class="text-xs uppercase px-2.5 py-1 rounded bg-[#e6f4ee] text-[#0d631b] font-bold">Live</span>
                                </div>
                                <div class="space-y-3 font-mono text-xs">
                                    <div class="grid grid-cols-5 text-[#6b7568] uppercase tracking-wider font-bold text-[10px] pb-2">
                                        <span>Lot / Origin</span><span>Process</span><span>Score</span><span>Volume</span><span class="text-right">Price (FOB)</span>
                                    </div>
                                    <div v-for="row in orderbookRows" :key="row.lot" class="grid grid-cols-5 items-center p-3 rounded-xl bg-[#f7fbf0]">
                                        <div class="font-sans font-bold text-[#181d17]">{{ row.lot }}</div>
                                        <div>{{ row.process }}</div>
                                        <div :class="row.muted ? 'text-[#6b7568]' : 'text-[#0d631b] font-bold'">{{ row.score }} SCA</div>
                                        <div>{{ row.volume }}</div>
                                        <div class="text-right font-bold" :class="row.muted ? 'text-[#181d17]' : 'text-[#0d631b]'">{{ row.price }}</div>
                                    </div>
                                </div>
                                <div class="mt-6 pt-4 flex items-center justify-between text-[11px] text-[#6b7568] font-mono">
                                    <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#0d631b]"></span> Matching engine: 12ms latency</span>
                                    <span>Escrow provider: Tier-1 custody</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 8. MARKET INTELLIGENCE -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="max-w-3xl mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Predictive Market Analytics</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Understand the market before you trade.</h2>
                        <p class="text-base text-[#6b7568] mt-4 leading-relaxed">Bean Origin pairs real-time physical market data with available inventory and trading activity, so you can weigh the risk before committing capital.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-12">
                        <div v-for="mod in intelligenceModules" :key="mod.title" class="p-4 rounded-xl bg-[#f7fbf0] shadow-sm flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-white text-[#0d631b] flex items-center justify-center flex-shrink-0">
                                <el-icon :size="20"><component :is="mod.icon" /></el-icon>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-[#181d17]">{{ mod.title }}</div>
                                <div class="text-[11px] text-[#6b7568]">{{ mod.body }}</div>
                            </div>
                        </div>
                        <Link :href="route('market-intelligence.index')" class="p-4 rounded-xl bg-[#0d631b] text-white shadow-sm flex items-center justify-between no-underline">
                            <div>
                                <div class="text-xs uppercase tracking-widest font-bold text-[#a7d9b0]">Intelligence Hub</div>
                                <div class="text-sm font-bold">Live Feed</div>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-[#0a4f15] flex items-center justify-center flex-shrink-0">
                                <el-icon :size="18"><ArrowRight /></el-icon>
                            </div>
                        </Link>
                    </div>
                    <div class="flex justify-center">
                        <Link :href="route('market-intelligence.index')" class="px-8 py-3.5 rounded-xl bg-[#0d631b] text-white text-sm font-bold hover:bg-[#0a4f15] transition-all flex items-center gap-2 no-underline">
                            <span>Explore Coffee Market Intelligence</span>
                            <el-icon :size="18"><DataLine /></el-icon>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- 9. TRACEABILITY -->
            <section class="w-full bg-[#f7fbf0] py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        <div class="lg:col-span-5 order-2 lg:order-1">
                            <div class="rounded-2xl overflow-hidden shadow-xl bg-[#181d17]">
                                <div class="w-full h-[340px] flex flex-col items-center justify-center gap-3 text-[#a7d9b0]">
                                    <el-icon :size="52"><MapLocation /></el-icon>
                                    <span class="text-xs uppercase tracking-widest font-semibold">Farm Polygon Registry</span>
                                </div>
                                <div class="p-6 bg-white">
                                    <div class="flex items-center justify-between flex-wrap gap-1">
                                        <span class="text-xs font-mono font-bold text-[#0d631b]">EUDR Polygon: Confirmed</span>
                                        <span class="text-xs font-bold text-[#6b7568]">14.3412&deg; N, 88.1245&deg; W</span>
                                    </div>
                                    <div class="text-xs text-[#6b7568] mt-2 leading-relaxed">Zero-deforestation checked against satellite imagery from the 2020 regulatory baseline.</div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-7 order-1 lg:order-2 flex flex-col gap-6">
                            <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b]">Provenance</div>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Know exactly where your coffee comes from.</h2>
                            <p class="text-base text-[#6b7568] leading-relaxed">The Bean Origin provenance ledger captures each physical milestone as it happens, turning what used to be scattered paper handoffs into one unbroken chain of custody.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                <div v-for="t in traceabilityItems" :key="t.title" class="flex items-start gap-3">
                                    <el-icon :size="20" class="text-[#0d631b] mt-0.5"><component :is="t.icon" /></el-icon>
                                    <div>
                                        <div class="text-sm font-bold text-[#181d17]">{{ t.title }}</div>
                                        <div class="text-xs text-[#6b7568]">{{ t.body }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4">
                                <a href="#" class="px-7 py-3 rounded-xl bg-white text-[#181d17] text-sm font-semibold hover:bg-[#e6f4ee] transition-all inline-flex items-center gap-2 no-underline shadow-sm">
                                    <span>Explore Traceability Rails</span>
                                    <el-icon :size="18"><ArrowRight /></el-icon>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 10. TRUST & VERIFICATION -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Institutional Integrity</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Trust built into every step of the journey.</h2>
                        <p class="text-base text-[#6b7568] mt-3 leading-relaxed">Cross-border agricultural trade needs verification that actually holds up. We run multi-layer credential checks before any transaction executes.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="t in trustCards" :key="t.title" class="p-6 rounded-2xl bg-[#f7fbf0]">
                            <div class="w-10 h-10 rounded-xl bg-white text-[#0d631b] flex items-center justify-center mb-4">
                                <el-icon :size="20"><component :is="t.icon" /></el-icon>
                            </div>
                            <h3 class="text-base font-bold text-[#181d17] mb-2">{{ t.title }}</h3>
                            <p class="text-xs text-[#6b7568] leading-relaxed">{{ t.body }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 11. MEET BEAN ORIGIN AI -->
            <section class="w-full bg-[#f8faf7] py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        <div class="lg:col-span-6 flex flex-col gap-6">
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Meet Bean Origin AI.</h2>
                            <p class="text-base text-[#6b7568] leading-relaxed">Built specifically for commodity trading. Bean Origin AI helps buyers and producers query market depth, put contracts together, and plan logistics.</p>
                            <div class="p-5 rounded-2xl bg-white shadow-sm">
                                <div class="text-[11px] uppercase tracking-widest text-[#6b7568] font-bold mb-2">A sample sourcing query</div>
                                <div class="p-4 rounded-xl bg-[#f7fbf0] text-sm font-mono text-[#181d17] flex items-start gap-3">
                                    <el-icon :size="18" class="text-[#0d631b] mt-0.5"><Search /></el-icon>
                                    <span>"Find me 20 tonnes of Ugandan Robusta Screen 18 under $4.00/kg FOB Mombasa, compare shipping to Rotterdam, and flag any EUDR issues."</span>
                                </div>
                                <div class="mt-4 flex items-center justify-between text-xs text-[#6b7568] flex-wrap gap-2">
                                    <span class="flex items-center gap-1 font-bold text-[#0d631b]"><el-icon :size="16"><CircleCheck /></el-icon> 4 lots evaluated, match found</span>
                                    <span class="font-mono">Processing: 0.8s</span>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-[#fdecd3] flex items-start gap-3">
                                <el-icon :size="20" class="text-[#b45309] mt-0.5"><Lock /></el-icon>
                                <div class="text-xs text-[#181d17] leading-relaxed">
                                    <strong class="font-bold">Human in the loop:</strong> anything consequential, binding bids, signed contracts, or fund transfers, still needs two-factor human sign-off.
                                </div>
                            </div>
                            <div>
                                <Link :href="route('market-intelligence.index')" class="px-7 py-3 rounded-xl bg-[#0d631b] text-white text-sm font-bold hover:bg-[#0a4f15] transition-all inline-flex items-center gap-2 no-underline">
                                    <span>Explore AI Commerce Capabilities</span>
                                    <el-icon :size="18"><ArrowRight /></el-icon>
                                </Link>
                            </div>
                        </div>
                        <div class="lg:col-span-6">
                            <div class="p-8 rounded-2xl bg-white shadow-lg">
                                <h3 class="text-base font-bold text-[#181d17] mb-6">A 9-step autonomous commerce flow</h3>
                                <div class="grid grid-cols-3 gap-3 text-center">
                                    <div v-for="step in aiFlow" :key="step.n" class="p-3 rounded-xl" :class="step.highlight ? 'bg-[#e6f4ee]' : 'bg-[#f7fbf0]'">
                                        <div class="text-[10px] font-mono text-[#0d631b] font-bold">STEP {{ step.n }}</div>
                                        <div class="text-xs font-bold text-[#181d17] mt-1">{{ step.title }}</div>
                                        <div class="text-[10px] text-[#6b7568] mt-0.5">{{ step.body }}</div>
                                    </div>
                                </div>
                                <div class="mt-8 pt-6 bg-white -mx-8 -mb-8 p-8 rounded-b-2xl">
                                    <div class="text-xs uppercase font-bold text-[#181d17] mb-3">Enterprise AI Integrations</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="tag in aiTags" :key="tag" class="px-2.5 py-1 rounded bg-[#f7fbf0] text-xs font-medium text-[#181d17]">{{ tag }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 12. ONE CONNECTED RECORD -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Data Topology</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">One connected record. Many possibilities.</h2>
                        <p class="text-base text-[#6b7568] mt-3 leading-relaxed">Instead of scattering data across spreadsheets, disconnected systems, and paper notes, Bean Origin keeps one schema that grows as the coffee moves through each physical stage.</p>
                    </div>
                    <div class="p-8 rounded-2xl bg-[#f7fbf0]">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4">
                            <div v-for="(e, i) in entities" :key="e.title" class="p-4 rounded-xl bg-white">
                                <span class="text-xs font-mono font-bold text-[#0d631b]">ENTITY {{ String(i + 1).padStart(2, '0') }}</span>
                                <div class="text-sm font-bold text-[#181d17] mt-2">{{ e.title }}</div>
                                <p class="text-xs text-[#6b7568] mt-1">{{ e.body }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 13. TECHNOLOGY ARCHITECTURE -->
            <section class="w-full bg-[#f8faf7] py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="max-w-3xl mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Enterprise Engineering</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Technology built around the coffee industry.</h2>
                        <p class="text-base text-[#6b7568] mt-4 leading-relaxed">Commodity systems tend to fail when they ignore physical reality. Ours is built around seasonality, cupping quality that changes over time, and the realities of ocean transit.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="t in techCards" :key="t.title" class="p-8 rounded-2xl bg-white shadow-sm">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6" :class="t.tone === 'primary' ? 'bg-[#e6f4ee] text-[#0d631b]' : t.tone === 'secondary' ? 'bg-[#fdecd3] text-[#b45309]' : 'bg-[#e0e7ff] text-[#1e40af]'">
                                <el-icon :size="24"><component :is="t.icon" /></el-icon>
                            </div>
                            <h3 class="text-lg font-bold text-[#181d17] mb-2">{{ t.title }}</h3>
                            <p class="text-xs text-[#6b7568] leading-relaxed">{{ t.body }}</p>
                        </div>
                        <div class="p-8 rounded-2xl bg-[#0d631b] text-white shadow-sm flex flex-col justify-between">
                            <div>
                                <el-icon :size="28" class="text-[#a7d9b0] mb-4"><Platform /></el-icon>
                                <h3 class="text-lg font-bold">Built for scale</h3>
                                <p class="text-xs text-[#c9e8d3] mt-2 leading-relaxed">Sub-second settlement checks and data residency that meets ISO 27001 standards.</p>
                            </div>
                            <div class="text-xs font-mono font-bold text-[#a7d9b0] pt-4">SOC 2 Type II certified</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 14. VISION & MISSION -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="p-10 lg:p-16 rounded-3xl bg-[#f7fbf0]">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                            <div>
                                <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Long-Term Ambition</div>
                                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight mb-6">Our Vision & Mission</h2>
                                <div class="mb-6">
                                    <span class="text-xs uppercase font-bold text-[#6b7568] tracking-wider">Vision</span>
                                    <p class="text-lg font-bold text-[#0d631b] mt-1">"A digital infrastructure for a more connected global coffee market."</p>
                                </div>
                                <div>
                                    <span class="text-xs uppercase font-bold text-[#6b7568] tracking-wider">Mission</span>
                                    <p class="text-base text-[#6b7568] mt-1 leading-relaxed">Connect coffee, people, information, and markets through technology that respects producers and gives businesses better tools to work with.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="m in missionMini" :key="m.title" class="p-6 rounded-2xl bg-white shadow-sm">
                                    <el-icon :size="28" class="text-[#0d631b] mb-3"><component :is="m.icon" /></el-icon>
                                    <div class="text-base font-bold text-[#181d17]">{{ m.title }}</div>
                                    <p class="text-xs text-[#6b7568] mt-1">{{ m.body }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 15. OUR PRINCIPLES -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Guiding Ethos</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">The principles that shape our platform.</h2>
                        <p class="text-base text-[#6b7568] mt-3 leading-relaxed">Technology should serve people, protect origin identities, and bring some daylight to commodity markets.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="p in principles" :key="p.title" class="p-6 rounded-2xl bg-[#f7fbf0]">
                            <span class="text-xs font-mono font-bold text-[#0d631b]">{{ p.tag }}</span>
                            <h3 class="text-lg font-bold text-[#181d17] mt-2 mb-2">{{ p.title }}</h3>
                            <p class="text-xs text-[#6b7568] leading-relaxed">{{ p.body }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 16. GLOBAL COFFEE NETWORK -->
            <section class="w-full bg-[#f8faf7] py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8 mb-16">
                        <div class="max-w-2xl">
                            <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Global Footprint</div>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">Connecting coffee across borders.</h2>
                            <p class="text-base text-[#6b7568] mt-3 leading-relaxed">Bean Origin operates in the coffee industry's busiest corridors, linking producing regions near the equator directly to the cities that drink the most coffee.</p>
                        </div>
                        <div class="text-xs font-mono font-bold px-3 py-1.5 rounded-lg bg-white text-[#0d631b] shadow-sm">Active hubs in 28 jurisdictions</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="hub in networkHubs" :key="hub.title" class="p-6 rounded-2xl bg-white shadow-sm">
                            <div class="flex items-center justify-between mb-4 flex-wrap gap-1">
                                <span class="text-xs uppercase font-bold tracking-wider" :class="hub.tone === 'primary' ? 'text-[#0d631b]' : 'text-[#b45309]'">{{ hub.tag }}</span>
                                <span class="text-xs font-mono text-[#6b7568]">{{ hub.ports }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-[#181d17] mb-2">{{ hub.title }}</h3>
                            <p class="text-xs text-[#6b7568] leading-relaxed mb-4">{{ hub.body }}</p>
                            <div class="pt-3 flex items-center justify-between text-xs font-bold" :class="hub.tone === 'primary' ? 'text-[#0d631b]' : 'text-[#b45309]'">
                                <span>{{ hub.stat }}</span>
                                <el-icon :size="16"><ArrowRight /></el-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 17. THE BEAN ORIGIN JOURNEY -->
            <section class="w-full bg-white py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <div class="text-xs uppercase tracking-widest font-extrabold text-[#0d631b] mb-3">Evolution Timeline</div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#181d17] tracking-tight">The Bean Origin journey.</h2>
                        <p class="text-base text-[#6b7568] mt-3 leading-relaxed">From digitizing single farm lots in Central America to running high-velocity institutional trade today.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div v-for="t in timeline" :key="t.stage" class="p-8 rounded-2xl flex flex-col justify-between" :class="t.highlight ? 'bg-[#e6f4ee]' : 'bg-[#f7fbf0]'">
                            <div>
                                <span class="text-xs font-mono font-bold" :class="t.highlight ? 'text-[#0d631b]' : 'text-[#6b7568]'">{{ t.stage.toUpperCase() }}</span>
                                <h3 class="text-xl font-bold text-[#181d17] mt-2 mb-3">{{ t.title }}</h3>
                                <p class="text-sm text-[#6b7568] leading-relaxed">{{ t.body }}</p>
                            </div>
                            <div class="mt-8 pt-4 text-xs font-semibold" :class="t.highlight ? 'text-[#0d631b]' : 'text-[#b45309]'">{{ t.foot }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 18. FINAL CTA -->
            <section class="w-full bg-[#0d631b] py-24 text-white relative overflow-hidden">
                <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10 text-center">
                    <div class="max-w-3xl mx-auto">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0a4f15] text-[#a7d9b0] text-xs font-bold uppercase tracking-wider mb-6">
                            <span>The Next Chapter for Coffee Trade</span>
                        </div>
                        <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-6">The coffee market is going digital.</h2>
                        <p class="text-base sm:text-lg text-[#c9e8d3] max-w-2xl mx-auto leading-relaxed mb-10">Discover coffee. Understand the market. Connect with the right people. Trade with confidence.</p>
                        <div class="flex flex-wrap items-center justify-center gap-4">
                            <Link :href="route('market.live')" class="px-8 py-4 rounded-xl bg-white text-[#0d631b] text-sm font-extrabold shadow-xl hover:bg-[#f0f4ee] transition-all flex items-center gap-2 no-underline">
                                <span>Explore Coffee Catalog</span>
                                <el-icon :size="18"><ArrowRight /></el-icon>
                            </Link>
                            <Link :href="route('exchange-snapshot.index')" class="px-8 py-4 rounded-xl bg-[#0a4f15] text-white text-sm font-bold hover:bg-[#083f10] transition-all no-underline">
                                <span>Explore the Exchange</span>
                            </Link>
                            <Link :href="route('register')" class="px-8 py-4 rounded-xl bg-[#fedcbe] text-[#291806] text-sm font-bold hover:bg-[#fbcb9a] transition-all no-underline">
                                <span>Create an Account</span>
                            </Link>
                        </div>
                        <div class="mt-12 flex flex-wrap items-center justify-center gap-8 text-xs text-[#c9e8d3] font-mono">
                            <span class="flex items-center gap-1.5"><el-icon :size="16" class="text-[#a7d9b0]"><CircleCheckFilled /></el-icon> Direct Farm Provenance</span>
                            <span class="flex items-center gap-1.5"><el-icon :size="16" class="text-[#a7d9b0]"><CircleCheckFilled /></el-icon> Q-Grade Sensory Audited</span>
                            <span class="flex items-center gap-1.5"><el-icon :size="16" class="text-[#a7d9b0]"><CircleCheckFilled /></el-icon> EUDR Polygon Compliant</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </OuterLayout>
</template>
